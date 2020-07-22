<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Contribution extends Model
{
    public $timestamps = false;
    protected $primaryKey = 'idx';
    protected $fillable = ['data'];

    public function user(){
        return $this->belongsTo(User::class);
    }
}
