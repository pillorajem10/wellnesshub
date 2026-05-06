<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVoteRequest;
use App\Models\Comment;
use App\Models\Thread;
use App\Models\Vote;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class VoteController extends Controller
{
    use ApiResponse;

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
        // Vote value convention: 1 = upvote, -1 = downvote.
        $requestedValue = (int) $data['value'];

        [$currentUserVote, $stats, $message] = DB::transaction(function () use (
            $attributes,
            $requestedValue,
            $class,
            $votable
        ): array {
            $existingVote = Vote::query()
                ->where($attributes)
                ->lockForUpdate()
                ->first();

            $currentUserVote = null;
            $message = 'Vote recorded successfully.';

            if (! $existingVote) {
                Vote::query()->create($attributes + [
                    'tbl_vote_value' => $requestedValue,
                ]);
                $currentUserVote = $requestedValue;
                $message = 'Vote recorded successfully.';
            } else {
                $existingValue = (int) $existingVote->tbl_vote_value;

                if ($existingValue === $requestedValue) {
                    // Clicking the same vote twice toggles it off.
                    $existingVote->delete();
                    $currentUserVote = null;
                    $message = 'Vote removed successfully.';
                } else {
                    // Clicking the opposite vote switches immediately.
                    $existingVote->update([
                        'tbl_vote_value' => $requestedValue,
                    ]);
                    $currentUserVote = $requestedValue;
                    $message = 'Vote updated successfully.';
                }
            }

            $stats = $this->syncAndGetVotableVoteStats($class, (int) $votable->getKey());

            return [$currentUserVote, $stats, $message];
        });

        return $this->successResponse([
            'votable_type' => $data['votable_type'],
            'votable_id' => $votable->getKey(),
            'votes_count' => $stats['votes_count'],
            'upvotes_count' => $stats['upvotes_count'],
            'downvotes_count' => $stats['downvotes_count'],
            'user_vote' => $currentUserVote,
            'current_user_vote' => $currentUserVote,
        ], $message);
    }

    /**
     * Recalculate vote stats and persist the net score to the votable row.
     *
     * @return array{votes_count:int, upvotes_count:int, downvotes_count:int}
     */
    private function syncAndGetVotableVoteStats(string $votableClass, int $votableId): array
    {
        $row = Vote::query()
            ->where('tbl_vote_votable_type', $votableClass)
            ->where('tbl_vote_votable_id', $votableId)
            ->selectRaw('COALESCE(SUM(tbl_vote_value), 0) as score')
            ->selectRaw('COALESCE(SUM(tbl_vote_value = 1), 0) as upvotes')
            ->selectRaw('COALESCE(SUM(tbl_vote_value = -1), 0) as downvotes')
            ->first();

        $votesCount = (int) ($row->score ?? 0);
        $upvotesCount = (int) ($row->upvotes ?? 0);
        $downvotesCount = (int) ($row->downvotes ?? 0);

        if ($votableClass === Thread::class) {
            Thread::query()->where('tbl_thread_id', $votableId)->update([
                'tbl_thread_votes_count' => $votesCount,
            ]);
        } else {
            Comment::query()->where('tbl_comment_id', $votableId)->update([
                'tbl_comment_votes_count' => $votesCount,
            ]);
        }

        return [
            'votes_count' => $votesCount,
            'upvotes_count' => $upvotesCount,
            'downvotes_count' => $downvotesCount,
        ];
    }
}
