<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class PasswordChange extends Mailable
{
	use Queueable, SerializesModels;
	public $password;
	public $name;
	public $url;
	public $subject;

	public function __construct($password,$name,$url,$subject)
	{
		$this->password = $password;
		$this->name     = $name;
		$this->url      = $url;
		$this->subject  = $subject;
	}

	public function build()
	{
		$subject = $this->subject;
		return $this->view('emails.password')->subject($subject);
	}
}
