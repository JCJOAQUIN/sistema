<?php

namespace App\Mail;

use App\BoardroomReservations;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotificacionSalaJuntasNewReservation extends Mailable
{
    use Queueable, SerializesModels;
    public $name;
    public $subject;
    public $reservation;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($name,$subject, BoardroomReservations $reservation)
    {
        $this->name        = $name;
        $this->subject     = $subject;
        $this->reservation = $reservation;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = $this->subject;
        return $this->view('emails.sala_juntas.nueva_reservacion')->subject($subject);
    }
}
