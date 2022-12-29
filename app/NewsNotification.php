<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NewsNotification extends Model
{
    protected $fillable = 
    [
        'description',
        'status',
        'user_id',
    ];

    public function results()
    {
        return $this->hasMany(NewsResult::class,'news_notification_id','id');
    }

    public function userData()
    {
        return $this->hasOne(User::class,'id','user_id');
    }

    public function statusData()
    {
        switch ($this->status) 
        {
            case '1':
                return 'Activo';
                break;

            case '2':
                return 'Inactivo';
                break;

            default:
                break;
        }
    }
}
