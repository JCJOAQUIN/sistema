<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendNewsNotification extends Mailable
{
	use Queueable, SerializesModels;
	public $resultNews;
	public $subject;
	public $name;
	public $description;
	public function __construct($resultNews,$subject,$name,$description)
	{
		$this->resultNews	= $resultNews;
		$this->subject		= $subject;
		$this->name			= $name;
		$this->description	= $description;
	}

	public function build()
	{
		$subject = $this->subject;
		return $this->view('administracion.news.partial.new_container')->subject($subject);
	}
}
