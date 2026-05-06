<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVoteRequest;
use App\Models\Comment;
use App\Models\Thread;
use App\Models\Vote;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class VoteController extends Controller
{
    use ApiResponse;

    /**
     * Voting is treated as a toggle: voting again on the same item removes the user's previous vote.
     */
    public function store(StoreVoteRequest $request): JsonResponse
    {
        $data = $request->validated();
        $userId = (int) $request->user()->getAuthIdentifier();

        $class = match ($data['votable_type']) {
            'thread' => Thread::class,
            'comment' => Comment::class,
        };

        /** @var Thread|Comment|null $votable */
        $votable = $class::query()->find($data['votable_id']);

        if (! $votable) {
            return $this->errorResponse('Votable item not found.', [
                'votable_id' => ['No matching thread or comment for this id.'],
            ], 422);
        }

        $attributes = [
            'tbl_vote_user_id' => $userId,
            'tbl_vote_votable_id' => (int) $votable->getKey(),
            'tbl_vote_votable_type' => $class,
        ];
        $existingVote = Vote::query()->where($attributes)->first();

        if ($existingVote) {
            $existingVote->delete();
            $votesCount = $this->syncVotableVotesCount($class, (int) $votable->getKey());

            return $this->successResponse([
                'votable_type' => $data['votable_type'],
                'votable_id' => $votable->getKey(),
                'votes_count' => $votesCount,
                'user_vote' => null,
            ], 'Vote removed successfully.');
        }

        Vote::query()->create($attributes + [
            'tbl_vote_value' => (int) $data['value'],
        ]);

        $votesCount = $this->syncVotableVotesCount($class, (int) $votable->getKey());

        return $this->successResponse([
            'votable_type' => $data['votable_type'],
            'votable_id' => $votable->getKey(),
            'votes_count' => $votesCount,
            'user_vote' => (int) $data['value'],
        ], 'Vote recorded successfully.');
    }

    private function syncVotableVotesCount(string $votableClass, int $votableId): int
    {
        $votesCount = (int) Vote::query()
            ->where('tbl_vote_votable_type', $votableClass)
            ->where('tbl_vote_votable_id', $votableId)
            ->sum('tbl_vote_value');

        if ($votableClass === Thread::class) {
            Thread::query()->where('tbl_thread_id', $votableId)->update([
                'tbl_thread_votes_count' => $votesCount,
            ]);
        } else {
            Comment::query()->where('tbl_comment_id', $votableId)->update([
                'tbl_comment_votes_count' => $votesCount,
            ]);
        }

        return $votesCount;
    }
}
