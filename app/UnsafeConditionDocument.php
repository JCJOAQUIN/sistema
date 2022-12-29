<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UnsafeConditionDocument extends Model
{
    protected $fillable =
    [
        'path',
        'unsafe_condition_id',
        'type',
    ];
}
