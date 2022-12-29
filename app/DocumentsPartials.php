<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DocumentsPartials extends Model
{
    const CREATED_AT        = 'date';
    protected $primaryKey   = 'iddocumentsPartial';
    protected $fillable     =
    [
        'partial_id',
        'path',
        'name'
    ];

    public function setPathAttribute($path)
    {
        if(is_string($path))
        {
            $this->attributes['path'] = $path;
        }
        else
        {
            if(!empty($path))
            {
                $this->attributes['path'] = 'AdG'.round(microtime(true) * 1000).'_purchaseDoc.'.$path->getClientOriginalExtension();
				$name = '/docs/purchase/AdG'.round(microtime(true) * 1000).'_purchaseDoc.'.$path->getClientOriginalExtension();
				\Storage::disk('public')->put($name,\File::get($path));
            }
        }
    }

}
