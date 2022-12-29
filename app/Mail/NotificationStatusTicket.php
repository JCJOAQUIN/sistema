<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotificationStatusTicket extends Mailable
{
	use Queueable, SerializesModels;
	public $requestUser;
	public $url;
	public $subject;
	public $num;
	public $status;

	public function __construct($requestUser,$url,$subject,$num,$status)
	{
		$this->requestUser 	= $requestUser;
		$this->url      	= $url;
		$this->subject  	= $subject;
		$this->num 			= $num;
		$this->status 		= $status;
	}

	public function build()
	{
		$subject = $this->subject;
		return $this->view('emails.status')->subject($subject);
	}
}