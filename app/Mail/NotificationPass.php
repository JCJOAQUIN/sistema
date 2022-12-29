<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotificationPass extends Mailable
{
	use Queueable, SerializesModels;
	public $name;
	public $url;
	public $subject;
	/**
	 * Create a new message instance.
	 *
	 * @return void
	 */
	public function __construct($name,$url,$subject)
	{
		$this->name		= $name;
		$this->url		= $url;
		$this->subject	= $subject;
	}

	/**
	 * Build the message.
	 *
	 * @return $this
	 */
	public function build()
	{
		$subject	= $this->subject;
		return $this->view('emails.notificationpass')->subject($subject);
	}
}
