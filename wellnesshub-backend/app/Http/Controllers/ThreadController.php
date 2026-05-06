<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreThreadRequest;
use App\Http\Requests\UpdateThreadRequest;
use App\Models\Comment;
use App\Models\Thread;
use App\Models\Vote;
use App\Services\TypesenseService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ThreadController extends Controller
{
    use ApiResponse;

    public function index(Request $request, TypesenseService $typesense): JsonResponse
    {
        $userId = $request->user()?->getAuthIdentifier();
        $perPage = min(max((int) $request->query('per_page', 15), 1), 100);
        $page = max((int) $request->query('page', 1), 1);
        $search = $request->query('search');
        $sort = $request->query('sort', 'recent');
        $protocolId = $request->query('protocol_id');

        $protocolIdInt = null;
        if ($protocolId !== null && $protocolId !== '') {
            $protocolIdInt = (int) $protocolId;
        }

        // If Typesense is configured and a search term is provided, use it for search + sort.
        if (is_string($search) && $search !== '' && $typesense->clientConfigured()) {
            $result = $typesense->searchThreads($search, is_string($sort) ? $sort : null, $protocolIdInt);
            if ($result !== null) {
                $ids = collect($result['hits'] ?? [])
                    ->map(fn (array $hit) => (int) (($hit['document']['id'] ?? $hit['id'] ?? null)))
                    ->filter()
                    ->values();

                $total = (int) ($result['found'] ?? $ids->count());
                $lastPage = (int) max(1, (int) ceil($total / $perPage));
                $slice = $ids->slice(($page - 1) * $perPage, $perPage)->all();

                $rows = Thread::query()
                    ->with('author')
                    ->whereIn('tbl_thread_id', $slice)
                    ->get()
                    ->keyBy('tbl_thread_id');

                $items = collect($slice)
                    ->map(fn (int $id) => $rows->get($id))
                    ->filter()
                    ->values()
                    ->all();

                $this->attachThreadUserVotes($items, $userId ? (int) $userId : null);

                return $this->successResponse([
                    'items' => $items,
                    'meta' => [
                        'current_page' => $page,
                        'last_page' => $lastPage,
                        'per_page' => $perPage,
                        'total' => $total,
                    ],
                    'source' => 'typesense',
                ]);
            }
        }

        $query = Thread::query()->with('author');

        if ($protocolIdInt !== null) {
            $query->where('tbl_thread_protocol_id', $protocolIdInt);
        }

        if (is_string($search) && $search !== '') {
            $query->where('tbl_thread_title', 'like', '%'.$search.'%');
        }

        match ($sort) {
            'most_upvoted' => $query->orderByDesc('tbl_thread_votes_count'),
            'most_commented' => $query->orderByDesc('tbl_thread_comments_count'),
            default => $query->orderByDesc('tbl_thread_created_at'),
        };

        $paginator = $query->paginate($perPage);
        $items = $paginator->items();

        $this->attachThreadUserVotes($items, $userId ? (int) $userId : null);

        return $this->successResponse([
            'items' => $items,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
            'source' => 'database',
        ]);
    }

    public function store(StoreThreadRequest $request): JsonResponse
    {
        $data = $request->validated();

        $thread = Thread::query()->create([
            'tbl_thread_protocol_id' => $data['protocol_id'],
            'tbl_thread_author_id' => $request->user()->getAuthIdentifier(),
            'tbl_thread_title' => $data['title'],
            'tbl_thread_body' => $data['body'],
            'tbl_thread_tags' => $data['tags'] ?? [],
        ]);

        $thread->load('author');

        return $this->successResponse($thread, 'Thread created successfully.', 201);
    }

    public function show(Thread $thread): JsonResponse
    {
        $userId = request()->user()?->getAuthIdentifier();
        $thread->load(['protocol', 'author']);

        if ($userId) {
            $value = Vote::query()
                ->where('tbl_vote_user_id', (int) $userId)
                ->where('tbl_vote_votable_type', Thread::class)
                ->where('tbl_vote_votable_id', (int) $thread->getKey())
                ->value('tbl_vote_value');

            $thread->setAttribute('user_vote', $value !== null ? (int) $value : null);
            $thread->setAttribute('current_user_vote', $value !== null ? (int) $value : null);
        } else {
            $thread->setAttribute('user_vote', null);
            $thread->setAttribute('current_user_vote', null);
        }

        $flat = Comment::query()
            ->where('tbl_comment_thread_id', $thread->getKey())
            ->with('author')
            ->orderByDesc('tbl_comment_created_at')
            ->get();

        $this->attachCommentUserVotes($flat, $userId ? (int) $userId : null);

        $tree = $this->buildCommentTree($flat);

        return $this->successResponse([
            'thread' => $thread,
            'comments' => $tree,
        ]);
    }

    public function update(UpdateThreadRequest $request, Thread $thread): JsonResponse
    {
        if ((int) $thread->tbl_thread_author_id !== (int) $request->user()->getAuthIdentifier()) {
            return $this->errorResponse('Unauthorized action.', [], 403);
        }

        $data = $request->validated();

        $thread->update([
            'tbl_thread_protocol_id' => $data['protocol_id'],
            'tbl_thread_title' => $data['title'],
            'tbl_thread_body' => $data['body'],
            'tbl_thread_tags' => $data['tags'] ?? [],
        ]);

        $thread->load('author');

        return $this->successResponse($thread, 'Thread updated successfully.');
    }

    public function destroy(Request $request, Thread $thread): JsonResponse
    {
        if ((int) $thread->tbl_thread_author_id !== (int) $request->user()->getAuthIdentifier()) {
            return $this->errorResponse('Unauthorized action.', [], 403);
        }

        $thread->delete();

        return $this->successResponse(null, 'Thread deleted successfully.');
    }

    /**
     * Group comments by parent id (0 = root), then recurse so each node carries a `replies` array.
     *
     * @param  Collection<int, Comment>  $comments
     * @return list<array<string, mixed>>
     */
    private function buildCommentTree(Collection $comments): array
    {
        /** @var array<int, list<Comment>> $byParent */
        $byParent = [];
        foreach ($comments as $comment) {
            $pid = (int) ($comment->tbl_comment_parent_id ?? 0);
            $byParent[$pid][] = $comment;
        }

        foreach ($byParent as $pid => $rows) {
            usort($rows, function (Comment $a, Comment $b): int {
                $at = strtotime((string) ($a->tbl_comment_created_at ?? '')) ?: 0;
                $bt = strtotime((string) ($b->tbl_comment_created_at ?? '')) ?: 0;

                // Newest-first so fresh replies stay visible.
                return $bt <=> $at;
            });
            $byParent[$pid] = $rows;
        }

        $build = function (int $parentId) use (&$build, &$byParent): array {
            $rows = $byParent[$parentId] ?? [];
            $out = [];
            foreach ($rows as $comment) {
                $out[] = array_merge($comment->toArray(), [
                    'replies' => $build((int) $comment->tbl_comment_id),
                ]);
            }

            return $out;
        };

        return $build(0);
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

    /**
     * @param  Collection<int, Comment>  $comments
     */
    private function attachCommentUserVotes(Collection $comments, ?int $userId): void
    {
        if (! $userId || $comments->isEmpty()) {
            foreach ($comments as $comment) {
                $comment->setAttribute('user_vote', null);
                $comment->setAttribute('current_user_vote', null);
            }

            return;
        }

        $ids = $comments->map(fn (Comment $c) => (int) $c->getKey())->filter()->values();
        if ($ids->isEmpty()) {
            return;
        }

        $votes = Vote::query()
            ->where('tbl_vote_user_id', $userId)
            ->where('tbl_vote_votable_type', Comment::class)
            ->whereIn('tbl_vote_votable_id', $ids->all())
            ->pluck('tbl_vote_value', 'tbl_vote_votable_id');

        foreach ($comments as $comment) {
            $value = $votes->get((int) $comment->getKey());
            $normalized = $value !== null ? (int) $value : null;
            $comment->setAttribute('user_vote', $normalized);
            $comment->setAttribute('current_user_vote', $normalized);
        }
    }
}
