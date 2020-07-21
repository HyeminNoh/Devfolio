<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Skill extends Model
{
    protected $fillable = ['data'];

    public function user(){
        return $this->belongsTo(User::class);
    }
}
