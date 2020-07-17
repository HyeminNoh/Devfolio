<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Contribution extends Model
{
    protected $fillable = ['data'];

    public function user(){
        return $this->belongsTo(User::class);
    }
}
