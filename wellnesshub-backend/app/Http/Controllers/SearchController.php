<?php

namespace App\Http\Controllers;

use App\Models\Protocol;
use App\Models\Thread;
use App\Services\TypesenseService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    use ApiResponse;

    public function protocols(Request $request, TypesenseService $typesense): JsonResponse
    {
        $query = (string) $request->query('q', '');
        $sort = $request->query('sort');

        if ($typesense->clientConfigured()) {
            $result = $typesense->searchProtocols($query, is_string($sort) ? $sort : null);
            if ($result !== null) {
                $documents = collect($result['hits'] ?? [])
                    ->map(fn (array $hit) => $hit['document'] ?? $hit)
                    ->values()
                    ->all();

                return $this->successResponse([
                    'found' => $result['found'] ?? 0,
                    'hits' => $documents,
                    'source' => 'typesense',
                ]);
            }
        }

        return $this->successResponse($this->fallbackProtocolSearch($query, is_string($sort) ? $sort : null));
    }

    public function threads(Request $request, TypesenseService $typesense): JsonResponse
    {
        $query = (string) $request->query('q', '');
        $sort = $request->query('sort');

        if ($typesense->clientConfigured()) {
            $result = $typesense->searchThreads($query, is_string($sort) ? $sort : null);
            if ($result !== null) {
                $documents = collect($result['hits'] ?? [])
                    ->map(fn (array $hit) => $hit['document'] ?? $hit)
                    ->values()
                    ->all();

                return $this->successResponse([
                    'found' => $result['found'] ?? 0,
                    'hits' => $documents,
                    'source' => 'typesense',
                ]);
            }
        }

        return $this->successResponse($this->fallbackThreadSearch($query, is_string($sort) ? $sort : null));
    }

    /**
     * @return array{found: int, hits: list<array<string, mixed>>, source: string}
     */
    private function fallbackProtocolSearch(string $query, ?string $sort): array
    {
        $q = Protocol::query()->with('author');

        if ($query !== '') {
            $needle = '%'.$query.'%';
            $q->where(function ($sub) use ($needle): void {
                $sub->where('tbl_protocol_title', 'like', $needle)
                    ->orWhere('tbl_protocol_content', 'like', $needle)
                    ->orWhere('tbl_protocol_tags', 'like', $needle);
            });
        }

        match ($sort) {
            'most_reviewed' => $q->orderByDesc('tbl_protocol_reviews_count'),
            'highest_rated' => $q->orderByDesc('tbl_protocol_avg_rating'),
            'most_upvoted' => $q->orderByDesc('tbl_protocol_votes_count'),
            default => $q->orderByDesc('tbl_protocol_created_at'),
        };

        $rows = $q->limit(50)->get();

        $hits = $rows->map(function (Protocol $protocol) {
            return [
                'id' => (string) $protocol->getKey(),
                'title' => $protocol->tbl_protocol_title,
                'content' => $protocol->tbl_protocol_content,
                'tags' => $protocol->tbl_protocol_tags ?? [],
                'author' => $protocol->author?->displayName() ?? '',
                'avg_rating' => (float) $protocol->tbl_protocol_avg_rating,
                'reviews_count' => (int) $protocol->tbl_protocol_reviews_count,
                'votes_count' => (int) $protocol->tbl_protocol_votes_count,
                'created_at' => $protocol->tbl_protocol_created_at?->getTimestamp() ?? 0,
            ];
        })->all();

        return [
            'found' => count($hits),
            'hits' => $hits,
            'source' => 'database',
        ];
    }

    /**
     * @return array{found: int, hits: list<array<string, mixed>>, source: string}
     */
    private function fallbackThreadSearch(string $query, ?string $sort): array
    {
        $q = Thread::query()->with('author');

        if ($query !== '') {
            $needle = '%'.$query.'%';
            $q->where(function ($sub) use ($needle): void {
                $sub->where('tbl_thread_title', 'like', $needle)
                    ->orWhere('tbl_thread_body', 'like', $needle)
                    ->orWhere('tbl_thread_tags', 'like', $needle);
            });
        }

        match ($sort) {
            'most_reviewed', 'most_commented' => $q->orderByDesc('tbl_thread_comments_count'),
            'highest_rated', 'most_upvoted' => $q->orderByDesc('tbl_thread_votes_count'),
            default => $q->orderByDesc('tbl_thread_created_at'),
        };

        $rows = $q->limit(50)->get();

        $hits = $rows->map(function (Thread $thread) {
            return [
                'id' => (string) $thread->getKey(),
                'protocol_id' => (string) $thread->tbl_thread_protocol_id,
                'title' => $thread->tbl_thread_title,
                'body' => $thread->tbl_thread_body,
                'tags' => $thread->tbl_thread_tags ?? [],
                'author' => $thread->author?->displayName() ?? '',
                'votes_count' => (int) $thread->tbl_thread_votes_count,
                'comments_count' => (int) $thread->tbl_thread_comments_count,
                'created_at' => $thread->tbl_thread_created_at?->getTimestamp() ?? 0,
            ];
        })->all();

        return [
            'found' => count($hits),
            'hits' => $hits,
            'source' => 'database',
        ];
    }
}
