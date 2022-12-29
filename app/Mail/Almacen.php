<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class Almacen extends Mailable
{
	use Queueable, SerializesModels;
	public $folio;
	

	public function __construct($folio)
	{
		$this->folio = $folio;
		
	}

	public function build()
	{

		return $this->view('emails.almacen')->subject('Nuevo articulo en el almacén.');
	}
}