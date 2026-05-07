<?php

namespace App\Http\Controllers;

use App\Models\Protocol;
use App\Models\Thread;
use App\Models\Vote;
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
        $userId = $request->user()?->getAuthIdentifier();
        $query = (string) $request->query('q', '');
        $sort = $request->query('sort');

        if ($typesense->clientConfigured()) {
            $result = $typesense->searchThreads($query, is_string($sort) ? $sort : null);
            if ($result !== null) {
                $ids = collect($result['hits'] ?? [])
                    ->map(fn (array $hit) => (int) (($hit['document']['id'] ?? $hit['id'] ?? null)))
                    ->filter()
                    ->values();

                $rows = Thread::query()
                    ->with('author')
                    ->whereIn('tbl_thread_id', $ids->all())
                    ->get()
                    ->keyBy('tbl_thread_id');

                $items = $ids
                    ->map(fn (int $id) => $rows->get($id))
                    ->filter()
                    ->values()
                    ->all();

                $this->attachThreadUserVotes($items, $userId ? (int) $userId : null);

                return $this->successResponse([
                    'found' => $result['found'] ?? 0,
                    'hits' => $items,
                    'source' => 'typesense',
                ]);
            }
        }

        return $this->successResponse(
            $this->fallbackThreadSearch($query, is_string($sort) ? $sort : null, $userId ? (int) $userId : null)
        );
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
    private function fallbackThreadSearch(string $query, ?string $sort, ?int $userId): array
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

        $rows = $q->limit(50)->get()->all();

        $this->attachThreadUserVotes($rows, $userId);

        return [
            'found' => count($rows),
            'hits' => $rows,
            'source' => 'database',
        ];
    }

    /**
     * @param  array<int, Thread>  $threads
     */
    private function attachThreadUserVotes(array $threads, ?int $userId): void
    {
        if (! $userId || ! $threads) {
            foreach ($threads as $thread) {
                $thread->setAttribute('user_vote', null);
                $thread->setAttribute('current_user_vote', null);
            }

            return;
        }

        $ids = collect($threads)->map(fn (Thread $t) => (int) $t->getKey())->filter()->values();
        if ($ids->isEmpty()) {
            return;
        }

        $votes = Vote::query()
            ->where('tbl_vote_user_id', $userId)
            ->where('tbl_vote_votable_type', Thread::class)
            ->whereIn('tbl_vote_votable_id', $ids->all())
            ->pluck('tbl_vote_value', 'tbl_vote_votable_id');

        foreach ($threads as $thread) {
            $value = $votes->get((int) $thread->getKey());
            $normalized = $value !== null ? (int) $value : null;
            $thread->setAttribute('user_vote', $normalized);
            $thread->setAttribute('current_user_vote', $normalized);
        }
    }
}
