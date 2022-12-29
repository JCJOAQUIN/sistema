<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CatTypeDocument extends Model
{
    protected $table = 'cat_type_documents';
    protected $fillable = 
    [
        'id',
        'name',
        'description'
    ];

    public $timestamps = false;
}
