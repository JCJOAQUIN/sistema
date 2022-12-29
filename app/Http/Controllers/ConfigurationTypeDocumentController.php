<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\URL;
use Carbon\Carbon;
use Auth;
use App;
use CatTypeDocument;
use Lang;


class ConfigurationTypeDocumentController extends Controller
{
    private $module_id = 340;
    public function index()
    {
        if(Auth::user()->module->where('id',$this->module_id)->count()>0)
        {
            $data = App\Module::find($this->module_id);
			return view('layouts.child_module',
            [
                'id'		=> $data['father'],
                'title'		=> $data['name'],
                'details'	=> $data['details'],
                'child_id'	=> $this->module_id
            ]);
        }
        else
		{
			return redirect('/');
		}
    }

    public function create()
    {
       if(Auth::user()->module->where('id',341)->count()>0)
       {
            $data = App\Module::find($this->module_id);
            return view('configuracion.type_document.alta',
            [
                'id'		=> $data['father'],
                'title'		=> $data['name'],
                'details'	=> $data['details'],
                'child_id'	=> $this->module_id,
                'option_id'	=> 341
            ]);
       }
       else
       {
           return redirect('/');
       }
    }

    public function search()
    {
        if(Auth::user()->module->where('id',342)->count()>0)
        {
            $data = App\Module::find($this->module_id);
            return view('configuracion.type_document.busqueda',
            [
                'id'		=> $data['father'],
                'title'		=> $data['name'],
                'details'	=> $data['details'],
                'child_id'	=> $this->module_id,
                'option_id'	=> 342
            ]);
        }
    }

    public function store(Request $request)
    {
        if(Auth::user()->module->where('id',$this->module_id)->count()>0)
        {
            $typeDocument               = new App\CatTypeDocument();
            $typeDocument->name         = $request->nameDocument;
            $typeDocument->description  = $request->descriptionDocument;
            $typeDocument->save();

            $alert = "swal('', '".Lang::get("messages.record_created")."', 'success');";
            return redirect('configuration/type-document')->with('alert',$alert);
        }
        else 
        {
            return redirect('/');
        }
    }

    public function follow(Request $request)
    {
        if(Auth::user()->module->where('id',342)->count()>0)
        {
            $data                   = App\Module::find($this->module_id);
            $documentName           = $request->nameDocument;
            $documentDescription    = $request->descriptionDocument;

            $documents              = App\CatTypeDocument::where(function($query) use ($documentName)
            {
                if($documentName != "")
                {
                    $query->where('name','like','%'.$documentName.'%');
                }
            })
            ->orderBy('id','desc')
            ->paginate(10);

            return response(
                view('configuracion.type_document.busqueda',
                [
                    'id'				    =>	$data['father'],
                    'title'				    =>	$data['name'],
                    'details'			    =>	$data['details'],
                    'child_id'			    =>	$this->module_id,
                    'option_id'			    =>	342,
                    'documents'             =>  $documents,
                    'documentName'          =>  $documentName,
                ])
            )
            ->cookie(
                'urlSearch', storeUrlCookie(342), 2880
            );
        }
        else
		{
			return redirect('/');
		}
    }

    public function edit(Request $request, $id)
    {
        if(Auth::user()->module->where('id',342))
        {
            $data       = App\Module::find($this->module_id);
            $documents  = App\CatTypeDocument::find($id);
            return view('configuracion.type_document.alta',
            [
                'id'		=>	$data['father'],
				'title'		=>	$data['name'],
				'details'	=>	$data['details'],
				'child_id'	=>	$this->module_id,
				'option_id'	=>	342,
				'documents'	=>	$documents
            ]);
        }
        else
		{
			return redirect('/');
		}
    }

    public function update(Request $request, $id)
    {
        if(Auth::user()->module->where('id',$this->module_id)->count()>0)
        {
            $exists = App\CatTypeDocument::where('name',$request->nameDocument)->first();
            if(!empty($exists) && $exists->id != $request->id)
            {
                $alert = "swal('','Por favor ingrese siglas diferentes.','error');";
                return back()->with('alert',$alert);
            }
            else
            {
                $documentUpdate                 = App\CatTypeDocument::find($id);
                $documentUpdate->name           = $request->nameDocument;
                $documentUpdate->description    = $request->descriptionDocument;
                $documentUpdate->save();

                $alert = "swal('', '".Lang::get("messages.record_updated")."', 'success');";
                return redirect()->route('type.document.edit',$id)->with('alert',$alert);
            }
        }
        else
        {
            redirect('/');
        }
    }

    public function validateNameDoc(Request $request)
    {
        if($request->ajax())
        {
            if($request->nameDocument == '')
            {
                $response = array(
                    'valid'		=> false,
                    'message'	=> 'El campo es requerido.'
                );
            }
            else 
            {
                $exist = App\CatTypeDocument::where('name',$request->nameDocument)
                    ->where(function($q) use($request)
                    {
                        if(isset($request->oldName))
                        {
                            $q->where('id','!=',$request->oldName);
                        }
                    })
                    ->get();
                if(count($exist) > 0)
                {
                    $response = array(
                        'valid'     => false,
                        'class'     => 'error',
                        'message'   => 'Las siglas ingresadas ya existen.'
                    );   
                }
                else
                {
                    $response = array('valid' => true);
                }
            }
            return Response($response);
        }
    }
}
