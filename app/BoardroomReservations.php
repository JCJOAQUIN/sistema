<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BoardroomReservations extends Model
{
	protected $fillable = 
	[
		'start',
		'end',
		'cancel_description',
		'boardroom_id',
		'reason',
		'observations',
		'id_request',
		'id_elaborate',
		'status'
	];
	protected $casts = 
	[
		'start' => 'datetime:Y-m-d H:i',
		'end'   => 'datetime:Y-m-d H:i',
	];

	public function boardroom()
	{
		return $this->belongsTo(Boardroom::class);
	}

	public function requestUser()
	{
		return $this->hasOne(User::class,'id','id_request');
	}
}
