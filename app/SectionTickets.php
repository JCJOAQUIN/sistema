<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SectionTickets extends Model
{
	public $timestamps    = false;
	protected $primaryKey = 'idsectionTickets';
	protected $fillable   = 
	[
		'section',
		'details',
	];

	public function inReview()
	{
		return $this->belongsToMany(User::class,'user_review_ticket');
	}

	public function scopeOrderName($query)
	{
		return $query->orderBy('section','asc');
	}
}
