<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendNonConformityNotification extends Mailable
{
	use Queueable, SerializesModels;
	public $nc_report_number;
	public $status;
	public $close_date;
	public $name;
	public $subject;

	public function __construct($nc_report_number,$status,$close_date,$name,$subject)
	{
		$this->nc_report_number	= $nc_report_number;
		$this->status			= $status;
		$this->close_date		= $close_date;
		$this->name				= $name;
		$this->subject			= $subject;
	}

	public function build()
	{
		$subject = $this->subject;
		return $this->view('emails.non_conformity_notification')->subject($subject);
	}
}
