<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Report
 *
 * @mixin Builder
 * @package App
 */
class Report extends Model
{
    public $timestamps = false;
    protected $primaryKey = 'idx';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_idx', 'type', 'data'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_idx', 'idx');
    }
}
