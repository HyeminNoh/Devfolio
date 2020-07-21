<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'github_id', 'access_token', 'avatar', 'github_url', 'blog_url',
    ];

    public function contribution(){
        return $this->belongsTo(Contribution::class);
    }

    public function repository(){
        return $this->belongsTo(Repository::class);
    }

    public function skill(){
        return $this->belongsTo(Skill::class);
    }
}
