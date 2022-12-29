<?php

namespace App\Observers;

use App\Boardroom;
use App\BoardroomReservations;
use App\Mail\NotificacionSalaJuntasCancelReservation;
use App\Mail\NotificacionSalaJuntasNewReservation;
use App\Mail\NotificacionSalaJuntasUpdateReservation;
use App\User;
use Illuminate\Support\Facades\Mail;

class BoardroomReservationsObserver
{
    /**
     * Handle to the BoardroomReservations "created" event.
     *
     * @param  \App\BoardroomReservations  $BoardroomReservations
     * @return void
     */
    public function created(BoardroomReservations $BoardroomReservations)
    {

        $br = Boardroom::where('id',$BoardroomReservations->boardroom_id)->first();

        $usersToNotify = User::whereHas('module',function($q)
            {
                $q->where('id', 269);
            })
            ->whereHas('inChargeEntGet',function($q) use ($br)
            {
                $q->where('enterprise_id', $br->enterprise_id)
                    ->where('module_id',269);
            })
            ->where('id','!=',auth()->user()->id)
            ->where('active',1)
            ->where('notification',1)
		->get();
        try
        {
            foreach ($usersToNotify as $user)
            {
                $name    = $user->fullName();
                $to      = $user->email;
                $subject = "Nueva reservación";
                Mail::to($to)->send(new NotificacionSalaJuntasNewReservation($name,$subject,$BoardroomReservations));
            }
            
        }
        catch(\Exception $e){ }
    }

    
    public function updating(BoardroomReservations $BoardroomReservations)
    {
        
        
        $olValues  = $BoardroomReservations->getOriginal();
        $newValues = $BoardroomReservations;
        
        $room = Boardroom::where('id',$BoardroomReservations->boardroom_id)->first();

        $notifyIds = [];

        $notifyIds[] = $BoardroomReservations->id_elaborate;
        
        if($BoardroomReservations->id_elaborate != $olValues["id_elaborate"])
        {
            $notifyIds[] = $olValues["id_elaborate"];
        }

        $adminUsers = User::whereHas('module',function($q)
            {
                $q->where('id', 269);
            })
            ->whereHas('inChargeEntGet',function($q) use ($room)
            {
                $q->where('enterprise_id', $room->enterprise_id)
                    ->where('module_id',269);
            })
		->get()->pluck('id')->toArray();

        $notifyIds = array_unique(array_merge($notifyIds,$adminUsers));

        if(count($notifyIds)>0)
        {
            try
            {
                foreach ($notifyIds as $id)
                {
                    if($id != auth()->user()->id)
                    {
                        $user    = User::find($id);
                        $to      = $user->email;
                        if($BoardroomReservations->status == 1)
                        {
                            $subject = "Cambio en reservación";
                            Mail::to($to)->send(new NotificacionSalaJuntasUpdateReservation($subject,$olValues,$newValues));
                        }
                        else if($BoardroomReservations->status == 0)
                        {
                            $subject = "Reservación cancelada";
                            Mail::to($to)->send(new NotificacionSalaJuntasCancelReservation($subject,$olValues,$newValues));
                        }
                    }
                }
                
            }
            catch(\Exception $e){ }
        }

    }
    /**
     * Handle the BoardroomReservations "updated" event.
     *
     * @param  \App\BoardroomReservations  $BoardroomReservations
     * @return void
     */
    public function updated(BoardroomReservations $BoardroomReservations)
    {
        //
    }

    /**
     * Handle the BoardroomReservations "deleted" event.
     *
     * @param  \App\BoardroomReservations  $BoardroomReservations
     * @return void
     */
    public function deleted(BoardroomReservations $BoardroomReservations)
    {
        //
    }
}