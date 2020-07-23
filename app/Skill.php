<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Skill extends Model
{
    public $timestamps = false;
    protected $primaryKey = 'idx';

    protected $fillable = ['user_idx', 'data'];

    public function user(){
        return $this->belongsTo(User::class);
    }
}
