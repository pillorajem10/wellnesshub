<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommentRequest;
use App\Http\Requests\UpdateCommentRequest;
use App\Models\Comment;
use App\Models\Thread;
use App\Models\Vote;
use App\Services\TypesenseService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()?->getAuthIdentifier();
        $perPage = min(max((int) $request->query('per_page', 15), 1), 100);
        $threadId = $request->query('thread_id');

        $query = Comment::query()->with(['author', 'thread']);

        if ($threadId !== null && $threadId !== '') {
            $query->where('tbl_comment_thread_id', (int) $threadId);
        }

        $query->orderByDesc('tbl_comment_created_at');

        $paginator = $query->paginate($perPage);
        $items = $paginator->items();

        $this->attachCommentUserVotes($items, $userId ? (int) $userId : null);

        return $this->successResponse([
            'items' => $items,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    public function store(StoreCommentRequest $request, TypesenseService $typesense): JsonResponse
    {
        $data = $request->validated();

        $comment = Comment::query()->create([
            'tbl_comment_thread_id' => $data['thread_id'],
            'tbl_comment_author_id' => $request->user()->getAuthIdentifier(),
            'tbl_comment_parent_id' => $data['parent_id'] ?? null,
            'tbl_comment_body' => $data['body'],
        ]);

        $comment->load('author');

        // Ensure thread comment counts are reflected in Typesense search results.
        if ($typesense->clientConfigured()) {
            $thread = Thread::query()->with('author')->find((int) $data['thread_id']);
            if ($thread) {
                $thread->refresh();
                $typesense->indexThread($thread);
            }
        }

        return $this->successResponse($comment, 'Comment created successfully.', 201);
    }

    public function show(Comment $comment): JsonResponse
    {
        $userId = request()->user()?->getAuthIdentifier();
        $comment->load(['author', 'thread', 'parent']);

        if ($userId) {
            $value = Vote::query()
                ->where('tbl_vote_user_id', (int) $userId)
                ->where('tbl_vote_votable_type', Comment::class)
                ->where('tbl_vote_votable_id', (int) $comment->getKey())
                ->value('tbl_vote_value');
            $comment->setAttribute('user_vote', $value !== null ? (int) $value : null);
            $comment->setAttribute('current_user_vote', $value !== null ? (int) $value : null);
        } else {
            $comment->setAttribute('user_vote', null);
            $comment->setAttribute('current_user_vote', null);
        }

        return $this->successResponse($comment);
    }

    public function update(UpdateCommentRequest $request, Comment $comment): JsonResponse
    {
        if ((int) $comment->tbl_comment_author_id !== (int) $request->user()->getAuthIdentifier()) {
            return $this->errorResponse('Unauthorized action.', [], 403);
        }

        $data = $request->validated();

        if ((int) $data['thread_id'] !== (int) $comment->tbl_comment_thread_id) {
            return $this->errorResponse('Validation failed.', [
                'thread_id' => ['Comments cannot be moved to another thread.'],
            ], 422);
        }

        $comment->update([
            'tbl_comment_parent_id' => $data['parent_id'] ?? null,
            'tbl_comment_body' => $data['body'],
        ]);

        $comment->load('author');

        return $this->successResponse($comment, 'Comment updated successfully.');
    }

    public function destroy(Request $request, Comment $comment, TypesenseService $typesense): JsonResponse
    {
        if ((int) $comment->tbl_comment_author_id !== (int) $request->user()->getAuthIdentifier()) {
            return $this->errorResponse('Unauthorized action.', [], 403);
        }

        $threadId = (int) $comment->tbl_comment_thread_id;
        $comment->delete();

        // Ensure thread comment counts are reflected in Typesense search results.
        if ($typesense->clientConfigured()) {
            $thread = Thread::query()->with('author')->find($threadId);
            if ($thread) {
                $thread->refresh();
                $typesense->indexThread($thread);
            }
        }

        return $this->successResponse(null, 'Comment deleted successfully.');
    }

    /**
     * @param  array<int, Comment>  $comments
     */
    private function attachCommentUserVotes(array $comments, ?int $userId): void
    {
        if (! $userId || ! $comments) {
            foreach ($comments as $comment) {
                $comment->setAttribute('user_vote', null);
                $comment->setAttribute('current_user_vote', null);
            }

            return;
        }

        $ids = collect($comments)->map(fn (Comment $c) => (int) $c->getKey())->filter()->values();
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
