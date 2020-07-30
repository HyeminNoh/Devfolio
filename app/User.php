<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Class User
 *
 * @mixin Builder
 * @package App
 */
class User extends Authenticatable
{
    use Notifiable;

    public $timestamps = false;
    protected $primaryKey = 'idx';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'github_id',
        'access_token',
        'avatar',
        'github_url',
        'blog_url',
    ];

    public function report()
    {
        return $this->hasMany(Report::class);
    }
}
