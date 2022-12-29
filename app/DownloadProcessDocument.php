<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DownloadProcessDocument extends Model
{
    protected $fillable = 
    [
        'file_name',
        'real_name',
        'file_extension',
        'user_id'
    ];
}
