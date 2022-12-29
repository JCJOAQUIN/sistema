<?php

namespace App\Mail;

use App\BoardroomReservations;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotificacionSalaJuntasUpdateReservation extends Mailable
{
    use Queueable, SerializesModels;
    public $subject;
    public $oldValues;
    public $newValues;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($subject,$oldValues,BoardroomReservations $newValues)
    {
        $this->subject   = $subject;
        $this->oldValues = $oldValues;
        $this->newValues = $newValues;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = $this->subject;
        return $this->view('emails.sala_juntas.actualizacion')->subject($subject);
    }
}
