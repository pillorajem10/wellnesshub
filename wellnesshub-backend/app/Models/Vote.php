<?php

namespace App\Models;

use Database\Factories\VoteFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Vote extends Model
{
    /** @use HasFactory<VoteFactory> */
    use HasFactory;

    protected $table = 'tbl_votes';

    protected $primaryKey = 'tbl_vote_id';

    public const CREATED_AT = 'tbl_vote_created_at';

    public const UPDATED_AT = 'tbl_vote_updated_at';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'tbl_vote_user_id',
        'tbl_vote_votable_id',
        'tbl_vote_votable_type',
        'tbl_vote_value',
    ];

    protected function casts(): array
    {
        return [
            'tbl_vote_value' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saved(function (Vote $vote): void {
            self::recalculateVotableTotals($vote);
        });

        static::deleted(function (Vote $vote): void {
            self::recalculateVotableTotals($vote);
        });
    }

    /**
     * Sum vote values (+1 / -1) for the related thread or comment and persist to the votable row.
     */
    public static function recalculateVotableTotals(Vote $vote): void
    {
        $type = $vote->tbl_vote_votable_type;
        $id = (int) $vote->tbl_vote_votable_id;

        $total = (int) self::query()
            ->where('tbl_vote_votable_type', $type)
            ->where('tbl_vote_votable_id', $id)
            ->sum('tbl_vote_value');

        if ($type === Thread::class) {
            Thread::query()->where('tbl_thread_id', $id)->update([
                'tbl_thread_votes_count' => $total,
            ]);
        } elseif ($type === Comment::class) {
            Comment::query()->where('tbl_comment_id', $id)->update([
                'tbl_comment_votes_count' => $total,
            ]);
        }
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tbl_vote_user_id', 'tbl_user_id');
    }

    public function votable(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'tbl_vote_votable_type', 'tbl_vote_votable_id');
    }
}
