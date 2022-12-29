<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CatAuditor extends Model
{
    protected $fillable = 
    [
        'name'
    ];

    public function scopeOrderName($query)
    {
        return $query->orderBy('name','asc');
    }
}
