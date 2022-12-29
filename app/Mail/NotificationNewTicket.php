<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotificationNewTicket extends Mailable
{
	use Queueable, SerializesModels;
	public $requestUser;
	public $url;
	public $subject;

	public function __construct($requestUser,$url,$subject)
	{
		$this->requestUser 	= $requestUser;
		$this->url      	= $url;
		$this->subject  	= $subject;
	}

	public function build()
	{
		$subject = $this->subject;
		return $this->view('emails.newticket')->subject($subject);
	}
}
