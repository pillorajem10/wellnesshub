<?php

namespace App\Models;

use Database\Factories\ReviewFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class Review extends Model
{
    /** @use HasFactory<ReviewFactory> */
    use HasFactory;

    protected $table = 'tbl_reviews';

    protected $primaryKey = 'tbl_review_id';

    public const CREATED_AT = 'tbl_review_created_at';

    public const UPDATED_AT = 'tbl_review_updated_at';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'tbl_review_protocol_id',
        'tbl_review_author_id',
        'tbl_review_rating',
        'tbl_review_feedback',
    ];

    protected function casts(): array
    {
        return [
            'tbl_review_rating' => 'integer',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'tbl_review_id';
    }

    protected static function booted(): void
    {
        static::saved(function (Review $review): void {
            self::updateProtocolRating((int) $review->tbl_review_protocol_id);
        });

        static::deleted(function (Review $review): void {
            self::updateProtocolRating((int) $review->tbl_review_protocol_id);
        });
    }

    /**
     * Recalculate average rating and review count for a protocol after review changes.
     */
    public static function updateProtocolRating(int $protocolId): void
    {
        $row = DB::table('tbl_reviews')
            ->where('tbl_review_protocol_id', $protocolId)
            ->selectRaw('COUNT(*) as c, COALESCE(AVG(tbl_review_rating), 0) as avg_rating')
            ->first();

        $count = (int) ($row->c ?? 0);
        $avg = $count > 0 ? round((float) $row->avg_rating, 2) : 0.0;

        Protocol::query()->where('tbl_protocol_id', $protocolId)->update([
            'tbl_protocol_reviews_count' => $count,
            'tbl_protocol_avg_rating' => $avg,
        ]);
    }

    public function protocol(): BelongsTo
    {
        return $this->belongsTo(Protocol::class, 'tbl_review_protocol_id', 'tbl_protocol_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tbl_review_author_id', 'tbl_user_id');
    }
}
