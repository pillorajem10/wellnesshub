<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * @var string
     */
    protected $rememberTokenName = '';

    protected $table = 'tbl_users';

    protected $primaryKey = 'tbl_user_id';

    public const CREATED_AT = 'tbl_user_created_at';

    public const UPDATED_AT = 'tbl_user_updated_at';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'tbl_user_fname',
        'tbl_user_lname',
        'tbl_user_email',
        'tbl_user_password',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'tbl_user_password',
    ];

    /**
     * Passwords are hashed explicitly in AuthController, UserFactory, and seeders.
     *
     * @return string
     */
    public function getAuthPassword()
    {
        return $this->tbl_user_password;
    }

    public function authoredProtocols(): HasMany
    {
        return $this->hasMany(Protocol::class, 'tbl_protocol_author_id', 'tbl_user_id');
    }

    public function authoredThreads(): HasMany
    {
        return $this->hasMany(Thread::class, 'tbl_thread_author_id', 'tbl_user_id');
    }

    public function authoredComments(): HasMany
    {
        return $this->hasMany(Comment::class, 'tbl_comment_author_id', 'tbl_user_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'tbl_review_author_id', 'tbl_user_id');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class, 'tbl_vote_user_id', 'tbl_user_id');
    }

    public function displayName(): string
    {
        return trim($this->tbl_user_fname.' '.$this->tbl_user_lname);
    }
}
