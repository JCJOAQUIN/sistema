<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UnsafeActDocument extends Model
{
    protected $table = 'unsafe_act_documents';

    protected $fillable =
    [
        'path',
        'unsafe_act_id',
        'type',
    ];
}
