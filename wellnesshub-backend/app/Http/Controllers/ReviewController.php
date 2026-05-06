<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReviewRequest;
use App\Http\Requests\UpdateReviewRequest;
use App\Models\Review;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $perPage = min(max((int) $request->query('per_page', 15), 1), 100);
        $protocolId = $request->query('protocol_id');

        $query = Review::query()->with(['author', 'protocol']);

        if ($protocolId !== null && $protocolId !== '') {
            $query->where('tbl_review_protocol_id', (int) $protocolId);
        }

        $query->orderByDesc('tbl_review_created_at');

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

    public function store(StoreReviewRequest $request): JsonResponse
    {
        $data = $request->validated();

        $review = Review::query()->create([
            'tbl_review_protocol_id' => $data['protocol_id'],
            'tbl_review_author_id' => $request->user()->getAuthIdentifier(),
            'tbl_review_rating' => $data['rating'],
            'tbl_review_feedback' => $data['feedback'] ?? null,
        ]);

        $review->load('author');

        return $this->successResponse($review, 'Review created successfully.', 201);
    }

    public function show(Review $review): JsonResponse
    {
        $review->load(['author', 'protocol']);

        return $this->successResponse($review);
    }

    public function update(UpdateReviewRequest $request, Review $review): JsonResponse
    {
        if ((int) $review->tbl_review_author_id !== (int) $request->user()->getAuthIdentifier()) {
            return $this->errorResponse('Unauthorized action.', [], 403);
        }

        $data = $request->validated();

        $review->update([
            'tbl_review_protocol_id' => $data['protocol_id'],
            'tbl_review_rating' => $data['rating'],
            'tbl_review_feedback' => $data['feedback'] ?? null,
        ]);

        $review->load('author');

        return $this->successResponse($review, 'Review updated successfully.');
    }

    public function destroy(Request $request, Review $review): JsonResponse
    {
        if ((int) $review->tbl_review_author_id !== (int) $request->user()->getAuthIdentifier()) {
            return $this->errorResponse('Unauthorized action.', [], 403);
        }

        $review->delete();

        return $this->successResponse(null, 'Review deleted successfully.');
    }
}
