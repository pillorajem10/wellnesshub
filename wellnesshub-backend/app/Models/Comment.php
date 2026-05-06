<?php

namespace App\Models;

use Database\Factories\CommentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Comment extends Model
{
    /** @use HasFactory<CommentFactory> */
    use HasFactory;

    protected $table = 'tbl_comments';

    protected $primaryKey = 'tbl_comment_id';

    public const CREATED_AT = 'tbl_comment_created_at';

    public const UPDATED_AT = 'tbl_comment_updated_at';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'tbl_comment_thread_id',
        'tbl_comment_author_id',
        'tbl_comment_parent_id',
        'tbl_comment_body',
        'tbl_comment_votes_count',
    ];

    protected function casts(): array
    {
        return [
            'tbl_comment_votes_count' => 'integer',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'tbl_comment_id';
    }

    protected static function booted(): void
    {
        static::deleting(function (Comment $comment): void {
            Vote::query()
                ->where('tbl_vote_votable_type', self::class)
                ->where('tbl_vote_votable_id', $comment->getKey())
                ->delete();
        });

        static::saved(function (Comment $comment): void {
            self::syncThreadCommentsCount((int) $comment->tbl_comment_thread_id);
        });

        static::deleted(function (Comment $comment): void {
            self::syncThreadCommentsCount((int) $comment->tbl_comment_thread_id);
        });
    }

    public static function syncThreadCommentsCount(int $threadId): void
    {
        $count = self::query()->where('tbl_comment_thread_id', $threadId)->count();
        Thread::query()->where('tbl_thread_id', $threadId)->update([
            'tbl_thread_comments_count' => $count,
        ]);
    }

    public function thread(): BelongsTo
    {
        return $this->belongsTo(Thread::class, 'tbl_comment_thread_id', 'tbl_thread_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tbl_comment_author_id', 'tbl_user_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'tbl_comment_parent_id', 'tbl_comment_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(self::class, 'tbl_comment_parent_id', 'tbl_comment_id');
    }

    public function votes(): MorphMany
    {
        return $this->morphMany(Vote::class, 'votable', 'tbl_vote_votable_type', 'tbl_vote_votable_id');
    }
}
