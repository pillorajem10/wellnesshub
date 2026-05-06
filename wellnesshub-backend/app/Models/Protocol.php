<?php

namespace App\Models;

use Database\Factories\ProtocolFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Protocol extends Model
{
    /** @use HasFactory<ProtocolFactory> */
    use HasFactory;

    protected $table = 'tbl_protocols';

    protected $primaryKey = 'tbl_protocol_id';

    public const CREATED_AT = 'tbl_protocol_created_at';

    public const UPDATED_AT = 'tbl_protocol_updated_at';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'tbl_protocol_title',
        'tbl_protocol_slug',
        'tbl_protocol_content',
        'tbl_protocol_tags',
        'tbl_protocol_author_id',
        'tbl_protocol_avg_rating',
        'tbl_protocol_reviews_count',
        'tbl_protocol_votes_count',
    ];

    protected function casts(): array
    {
        return [
            'tbl_protocol_tags' => 'array',
            'tbl_protocol_avg_rating' => 'float',
            'tbl_protocol_reviews_count' => 'integer',
            'tbl_protocol_votes_count' => 'integer',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'tbl_protocol_id';
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tbl_protocol_author_id', 'tbl_user_id');
    }

    public function threads(): HasMany
    {
        return $this->hasMany(Thread::class, 'tbl_thread_protocol_id', 'tbl_protocol_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'tbl_review_protocol_id', 'tbl_protocol_id');
    }
}
