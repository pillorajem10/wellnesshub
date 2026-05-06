<?php

namespace App\Services;

use App\Models\Protocol;
use App\Models\Thread;
use Illuminate\Support\Facades\Log;
use Typesense\Client;
use Typesense\Exceptions\ObjectNotFound;
use Typesense\Exceptions\TypesenseClientError;

class TypesenseService
{
    public function __construct(
        private ?Client $client = null,
    ) {
        $this->client = $client ?? $this->makeClient();
    }

    protected function makeClient(): ?Client
    {
        $apiKey = config('typesense.api_key');
        if (empty($apiKey)) {
            return null;
        }

        $host = config('typesense.host');
        $port = (int) config('typesense.port');
        $protocol = config('typesense.protocol');

        return new Client([
            'api_key' => $apiKey,
            'nodes' => [
                [
                    'host' => $host,
                    'port' => $port,
                    'protocol' => $protocol,
                ],
            ],
            'connection_timeout_seconds' => 5,
        ]);
    }

    public function clientConfigured(): bool
    {
        return $this->client !== null;
    }

    /**
     * Observers and HTTP handlers call into Typesense through here so a down cluster only produces logs.
     */
    protected function safeRun(callable $callback, string $context): mixed
    {
        if (! $this->clientConfigured()) {
            return null;
        }

        try {
            return $callback($this->client);
        } catch (TypesenseClientError|\Throwable $e) {
            Log::warning("Typesense {$context} failed: ".$e->getMessage(), ['exception' => $e]);

            return null;
        }
    }

    public function ensureProtocolCollection(): void
    {
        $name = config('typesense.collections.protocols');

        $this->safeRun(function (Client $client) use ($name): void {
            try {
                $client->collections[$name]->retrieve();
            } catch (ObjectNotFound) {
                $client->collections->create([
                    'name' => $name,
                    'fields' => [
                        ['name' => 'id', 'type' => 'string'],
                        ['name' => 'title', 'type' => 'string'],
                        ['name' => 'content', 'type' => 'string'],
                        ['name' => 'tags', 'type' => 'string[]', 'optional' => true],
                        ['name' => 'author', 'type' => 'string', 'optional' => true],
                        ['name' => 'avg_rating', 'type' => 'float'],
                        ['name' => 'reviews_count', 'type' => 'int32'],
                        ['name' => 'votes_count', 'type' => 'int32'],
                        ['name' => 'created_at', 'type' => 'int64'],
                    ],
                    'default_sorting_field' => 'created_at',
                ]);
            }
        }, 'ensureProtocolCollection');
    }

    public function ensureThreadCollection(): void
    {
        $name = config('typesense.collections.threads');

        $this->safeRun(function (Client $client) use ($name): void {
            try {
                $client->collections[$name]->retrieve();
            } catch (ObjectNotFound) {
                $client->collections->create([
                    'name' => $name,
                    'fields' => [
                        ['name' => 'id', 'type' => 'string'],
                        ['name' => 'protocol_id', 'type' => 'string'],
                        ['name' => 'title', 'type' => 'string'],
                        ['name' => 'body', 'type' => 'string'],
                        ['name' => 'tags', 'type' => 'string[]', 'optional' => true],
                        ['name' => 'author', 'type' => 'string', 'optional' => true],
                        ['name' => 'votes_count', 'type' => 'int32'],
                        ['name' => 'comments_count', 'type' => 'int32'],
                        ['name' => 'created_at', 'type' => 'int64'],
                    ],
                    'default_sorting_field' => 'created_at',
                ]);
            }
        }, 'ensureThreadCollection');
    }

    public function indexProtocol(Protocol $protocol): void
    {
        $this->ensureProtocolCollection();
        $collection = config('typesense.collections.protocols');
        $protocol->loadMissing('author');

        $this->safeRun(function (Client $client) use ($protocol, $collection): void {
            $doc = [
                'id' => (string) $protocol->getKey(),
                'title' => $protocol->tbl_protocol_title,
                'content' => $protocol->tbl_protocol_content,
                'tags' => $protocol->tbl_protocol_tags ?? [],
                'author' => $protocol->author?->displayName() ?? '',
                'avg_rating' => (float) $protocol->tbl_protocol_avg_rating,
                'reviews_count' => (int) $protocol->tbl_protocol_reviews_count,
                'votes_count' => (int) $protocol->tbl_protocol_votes_count,
                'created_at' => $protocol->tbl_protocol_created_at?->getTimestamp() ?? time(),
            ];

            $client->collections[$collection]->documents->upsert($doc);
        }, 'indexProtocol');
    }

    public function deleteProtocolDocument(int|string $protocolId): void
    {
        $collection = config('typesense.collections.protocols');

        $this->safeRun(function (Client $client) use ($protocolId, $collection): void {
            try {
                $client->collections[$collection]->documents[(string) $protocolId]->delete();
            } catch (ObjectNotFound) {
                // Already removed or never indexed.
            }
        }, 'deleteProtocolDocument');
    }

    public function indexThread(Thread $thread): void
    {
        $this->ensureThreadCollection();
        $collection = config('typesense.collections.threads');
        $thread->loadMissing('author');

        $this->safeRun(function (Client $client) use ($thread, $collection): void {
            $doc = [
                'id' => (string) $thread->getKey(),
                'protocol_id' => (string) $thread->tbl_thread_protocol_id,
                'title' => $thread->tbl_thread_title,
                'body' => $thread->tbl_thread_body,
                'tags' => $thread->tbl_thread_tags ?? [],
                'author' => $thread->author?->displayName() ?? '',
                'votes_count' => (int) $thread->tbl_thread_votes_count,
                'comments_count' => (int) $thread->tbl_thread_comments_count,
                'created_at' => $thread->tbl_thread_created_at?->getTimestamp() ?? time(),
            ];

            $client->collections[$collection]->documents->upsert($doc);
        }, 'indexThread');
    }

    public function deleteThreadDocument(int|string $threadId): void
    {
        $collection = config('typesense.collections.threads');

        $this->safeRun(function (Client $client) use ($threadId, $collection): void {
            try {
                $client->collections[$collection]->documents[(string) $threadId]->delete();
            } catch (ObjectNotFound) {
            }
        }, 'deleteThreadDocument');
    }

    /**
     * @return array<string, mixed>|null
     */
    public function searchProtocols(string $query, ?string $sort = null): ?array
    {
        $this->ensureProtocolCollection();
        $collection = config('typesense.collections.protocols');
        $q = trim($query) === '' ? '*' : $query;

        return $this->safeRun(function (Client $client) use ($collection, $q, $sort): array {
            $params = [
                'q' => $q,
                'query_by' => 'title,content,tags',
            ];
            $sortBy = $this->protocolSortBy($sort);
            if ($sortBy !== null) {
                $params['sort_by'] = $sortBy;
            }

            return $client->collections[$collection]->documents->search($params);
        }, 'searchProtocols');
    }

    /**
     * @return array<string, mixed>|null
     */
    public function searchThreads(string $query, ?string $sort = null, ?int $protocolId = null): ?array
    {
        $this->ensureThreadCollection();
        $collection = config('typesense.collections.threads');
        $q = trim($query) === '' ? '*' : $query;

        return $this->safeRun(function (Client $client) use ($collection, $q, $sort, $protocolId): array {
            $params = [
                'q' => $q,
                'query_by' => 'title,body,tags',
            ];
            $sortBy = $this->threadSortBy($sort);
            if ($sortBy !== null) {
                $params['sort_by'] = $sortBy;
            }
            if ($protocolId !== null) {
                $params['filter_by'] = 'protocol_id:='.$protocolId;
            }

            return $client->collections[$collection]->documents->search($params);
        }, 'searchThreads');
    }

    public function reindexAll(): void
    {
        $this->ensureProtocolCollection();
        $this->ensureThreadCollection();

        if (! $this->clientConfigured()) {
            return;
        }

        foreach (Protocol::query()->with('author')->cursor() as $protocol) {
            $this->indexProtocol($protocol);
        }

        foreach (Thread::query()->with('author')->cursor() as $thread) {
            $this->indexThread($thread);
        }
    }

    protected function protocolSortBy(?string $sort): ?string
    {
        return match ($sort) {
            'highest_rated' => 'avg_rating:desc',
            'most_reviewed' => 'reviews_count:desc',
            'most_upvoted' => 'votes_count:desc',
            'recent' => 'created_at:desc',
            default => 'created_at:desc',
        };
    }

    protected function threadSortBy(?string $sort): ?string
    {
        return match ($sort) {
            // Threads are not "rated"; use engagement proxies for search sorting.
            'highest_rated' => 'votes_count:desc',
            'most_reviewed' => 'comments_count:desc',
            'most_upvoted' => 'votes_count:desc',
            'recent' => 'created_at:desc',
            default => 'created_at:desc',
        };
    }
}
