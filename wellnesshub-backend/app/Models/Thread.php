<?php

namespace App\Models;

use Database\Factories\ThreadFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Thread extends Model
{
    /** @use HasFactory<ThreadFactory> */
    use HasFactory;

    protected $table = 'tbl_threads';

    protected $primaryKey = 'tbl_thread_id';

    public const CREATED_AT = 'tbl_thread_created_at';

    public const UPDATED_AT = 'tbl_thread_updated_at';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'tbl_thread_protocol_id',
        'tbl_thread_author_id',
        'tbl_thread_title',
        'tbl_thread_body',
        'tbl_thread_tags',
        'tbl_thread_votes_count',
        'tbl_thread_comments_count',
    ];

    protected function casts(): array
    {
        return [
            'tbl_thread_tags' => 'array',
            'tbl_thread_votes_count' => 'integer',
            'tbl_thread_comments_count' => 'integer',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'tbl_thread_id';
    }

    protected static function booted(): void
    {
        static::created(function (Thread $thread): void {
            self::syncProtocolVotesCount((int) $thread->tbl_thread_protocol_id);
        });

        static::updated(function (Thread $thread): void {
            if ($thread->wasChanged('tbl_thread_votes_count')) {
                self::syncProtocolVotesCount((int) $thread->tbl_thread_protocol_id);
            }
            if ($thread->wasChanged('tbl_thread_protocol_id')) {
                self::syncProtocolVotesCount((int) $thread->getOriginal('tbl_thread_protocol_id'));
                self::syncProtocolVotesCount((int) $thread->tbl_thread_protocol_id);
            }
        });

        static::deleting(function (Thread $thread): void {
            Vote::query()
                ->where('tbl_vote_votable_type', self::class)
                ->where('tbl_vote_votable_id', $thread->getKey())
                ->delete();
        });

        static::deleted(function (Thread $thread): void {
            self::syncProtocolVotesCount((int) $thread->tbl_thread_protocol_id);
        });
    }

    /**
     * tbl_protocol_votes_count is the sum of thread-level net vote scores under this protocol.
     */
    public static function syncProtocolVotesCount(int $protocolId): void
    {
        $sum = (int) self::query()
            ->where('tbl_thread_protocol_id', $protocolId)
            ->sum('tbl_thread_votes_count');

        Protocol::query()->where('tbl_protocol_id', $protocolId)->update([
            'tbl_protocol_votes_count' => $sum,
        ]);
    }

    public function protocol(): BelongsTo
    {
        return $this->belongsTo(Protocol::class, 'tbl_thread_protocol_id', 'tbl_protocol_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tbl_thread_author_id', 'tbl_user_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class, 'tbl_comment_thread_id', 'tbl_thread_id');
    }

    public function votes(): MorphMany
    {
        return $this->morphMany(Vote::class, 'votable', 'tbl_vote_votable_type', 'tbl_vote_votable_id');
    }
}
