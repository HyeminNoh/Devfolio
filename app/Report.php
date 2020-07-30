<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    public $timestamps = false;
    protected $primaryKey = 'idx';

    protected $fillable = ['user_idx', 'type', 'data'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_idx', 'idx');
    }
}
