<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UnsafePracticeDocument extends Model
{
    protected $fillable =
    [
        'path',
        'unsafe_practice_id',
        'type',
    ];
}
