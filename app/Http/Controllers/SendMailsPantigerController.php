<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App;
use Alert;
use Illuminate\Support\Facades\Mail;

class SendMailsPantigerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $emails = App\User::where('active',1)
                        ->where('notification',1)
                        ->get();
        if ($emails != "") 
        {
            foreach ($emails as $email)
            {
                $name           = $email->name.' '.$email->last_name.' '.$email->scnd_last_name;
                $to             = $email->email;
                $url            = route('home');
                $subject        = "Acceso al Sistema";
                Mail::to($to)->send(new App\Mail\NotificationPass($name,$url,$subject));
            }
        }
        $alert  = "swal('', 'Contraseñas enviadas...', 'success');";
        return redirect('home')->with('alert',$alert);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
