<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AuditCategory extends Model
{
    protected $table    = 'audit_categories';

    protected $fillable = 
    [
        'id','name'
    ];

    public function subcategories()
    {
        return $this->hasMany(AuditSubcategory::class);
    }
}
