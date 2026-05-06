<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommentRequest;
use App\Http\Requests\UpdateCommentRequest;
use App\Models\Comment;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $perPage = min(max((int) $request->query('per_page', 15), 1), 100);
        $threadId = $request->query('thread_id');

        $query = Comment::query()->with(['author', 'thread']);

        if ($threadId !== null && $threadId !== '') {
            $query->where('tbl_comment_thread_id', (int) $threadId);
        }

        $query->orderByDesc('tbl_comment_created_at');

        $paginator = $query->paginate($perPage);

        return $this->successResponse([
            'items' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    public function store(StoreCommentRequest $request): JsonResponse
    {
        $data = $request->validated();

        $comment = Comment::query()->create([
            'tbl_comment_thread_id' => $data['thread_id'],
            'tbl_comment_author_id' => $request->user()->getAuthIdentifier(),
            'tbl_comment_parent_id' => $data['parent_id'] ?? null,
            'tbl_comment_body' => $data['body'],
        ]);

        $comment->load('author');

        return $this->successResponse($comment, 'Comment created successfully.', 201);
    }

    public function show(Comment $comment): JsonResponse
    {
        $comment->load(['author', 'thread', 'parent']);

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

    public function destroy(Request $request, Comment $comment): JsonResponse
    {
        if ((int) $comment->tbl_comment_author_id !== (int) $request->user()->getAuthIdentifier()) {
            return $this->errorResponse('Unauthorized action.', [], 403);
        }

        $comment->delete();

        return $this->successResponse(null, 'Comment deleted successfully.');
    }
}
