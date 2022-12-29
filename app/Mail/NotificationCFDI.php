<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotificationCFDI extends Mailable
{
	use Queueable, SerializesModels;
	public $subject;
	public $xmlFile;
	public $pdfFile;

	/**
	 * Create a new message instance.
	 *
	 * @return void
	 */
	public function __construct($subject,$xmlFile,$pdfFile)
	{
		$this->subject	= $subject;
		$this->xmlFile	= $xmlFile;
		$this->pdfFile	= $pdfFile;
	}

	/**
	 * Build the message.
	 *
	 * @return $this
	 */
	public function build()
	{
		$subject	= $this->subject;
		$xmlFile	= $this->xmlFile;
		$pdfFile	= $this->pdfFile;
		return $this->view('emails.cfdi')
		->attach(\Storage::disk('reserved')->getDriver()->getAdapter()->getPathPrefix().$xmlFile)
		->attach(\Storage::disk('reserved')->getDriver()->getAdapter()->getPathPrefix().$pdfFile)
		->subject($subject);
	}
}
