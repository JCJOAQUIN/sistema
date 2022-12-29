<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class Notificacion extends Mailable
{
    use Queueable, SerializesModels;
    public $name;
    public $kind;
    public $status;
    public $date;
    public $url;
    public $subject;
    public $requestUser;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($name,$kind,$status,$date,$url,$subject,$requestUser)
    {
        $this->name         = $name;
        $this->kind         = $kind;
        $this->status       = $status;
        $this->date         = $date;
        $this->url          = $url;
        $this->subject      = $subject;
        $this->requestUser  = $requestUser;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = $this->subject;
        return $this->view('emails.notification')->subject($subject);
    }
}
