<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class repository extends Model
{
    public $timestamps = false;
    protected $fillable = ['data'];

    public function user(){
        return $this->belongsTo(User::class);
    }
}
