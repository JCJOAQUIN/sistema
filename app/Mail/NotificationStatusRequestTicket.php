<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotificationStatusRequestTicket extends Mailable
{
	use Queueable, SerializesModels;
	public $requestUser;
	public $subject;
	public $num;

	public function __construct($requestUser,$subject,$num)
	{
		$this->requestUser 	= $requestUser;
		$this->subject  	= $subject;
		$this->num 			= $num;
	}

	public function build()
	{
		$subject = $this->subject;
		return $this->view('emails.statusrequest')->subject($subject);
	}
}