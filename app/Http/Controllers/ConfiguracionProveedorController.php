<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\URL;
use App\http\Requests\GeneralRequest;
use App;
use Alert;
use Auth;
use Lang;
use Carbon\Carbon;
use Ilovepdf\CompressTask;

class ConfiguracionProveedorController extends Controller
{
	private $module_id = 44;
	public function index()
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$data  = App\Module::find($this->module_id);
			return view('layouts.child_module',
				[
					'id' 		=> $data['father'],
					'title' 	=> $data['name'],
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
		if(Auth::user()->module->where('id',45)->count()>0)
		{
			$data	= App\Module::find($this->module_id);
			return view('configuracion.proveedor.alta',
				[
					'id' 		=> $data['father'],
					'title' 	=> $data['name'],
					'details'  	=> $data['details'],
					'child_id'	=> $this->module_id,
					'option_id' => 45
				]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function store(Request $request)
	{
		$data							= App\Module::find($this->module_id);

		$t_provider_data 				= new App\ProviderData();
		$t_provider_data->users_id 		= Auth::user()->id;
		$t_provider_data->save();

		$t_provider						= new App\Provider();
		$t_provider->businessName		= $request->reason;
		$t_provider->beneficiary		= $request->beneficiary;
		$t_provider->phone				= $request->phone;
		$t_provider->rfc				= $request->rfc;
		$t_provider->contact			= $request->contact;
		$t_provider->commentaries		= $request->other;
		$t_provider->status				= 2;
		$t_provider->users_id			= Auth::user()->id;
		$t_provider->address			= $request->address;
		$t_provider->number				= $request->number;
		$t_provider->colony				= $request->colony;
		$t_provider->postalCode			= $request->cp;
		$t_provider->city				= $request->city;
		$t_provider->state_idstate		= $request->state;
		$t_provider->provider_data_id	= $t_provider_data->id;
		$t_provider->save();

		if(isset($request->clasificationData))
		{
			$dataClass						= json_decode($request->clasificationData);
			$providerClass					= new App\ProviderClassification;
			$providerClass->provider_id		= $t_provider->idProvider;
			$providerClass->classification	= $dataClass->clasif;
			$providerClass->commentary		= $dataClass->comm;
			$providerClass->created_by		= Auth::user()->id;
			$providerClass->provider_data_id= $t_provider_data->id;
			$providerClass->save();
			foreach ($dataClass->doc as $doc)
			{
				$document								= new App\ProviderClassificationDocs;
				$document->path							= $doc;
				$document->providerClassification_id	= $providerClass->id;
				$document->save();
			}
		}

		if(isset($request->providerBank))
		{
			for ($i=0; $i < count($request->providerBank); $i++)
			{
				$t_providerBank							= new App\ProviderBanks;
				$t_providerBank->provider_idProvider	= $t_provider->idProvider;
				$t_providerBank->banks_idBanks			= $request->bank[$i];
				$t_providerBank->alias					= $request->alias[$i];
				$t_providerBank->account				= $request->account[$i];
				$t_providerBank->branch					= $request->branch_office[$i];
				$t_providerBank->reference				= $request->reference[$i];
				$t_providerBank->clabe					= $request->clabe[$i];
				$t_providerBank->currency				= $request->currency[$i];
				$t_providerBank->agreement				= $request->agreement[$i];
				$t_providerBank->iban 					= $request->iban[$i];
				$t_providerBank->bic_swift 				= $request->bic_swift[$i];
				$t_providerBank->provider_data_id 		= $t_provider_data->id;
				$t_providerBank->save();
			}
		}

		$alert = "swal('', '".Lang::get("messages.record_created")."', 'success');";
		return redirect('configuration/provider')->with('alert',$alert);
	}

	public function search(Request $request)
	{
		if(Auth::user()->module->where('id',46)->count()>0)
		{
			$data		= App\Module::find($this->module_id);
			$providers	= App\Provider::where(function($query) use ($request)
							{
								$query->where('rfc','LIKE','%'.$request->search.'%')
									->orWhere('businessName','LIKE','%'.$request->search.'%');
							})
							->where('status',2)
							->orderBy('idProvider', 'desc')
							->paginate(10);
			return response (
				view('configuracion.proveedor.busqueda',
				[
					'id'		=> $data['father'],
					'title'		=> $data['name'],
					'details'	=> $data['details'],
					'child_id'	=> $this->module_id,
					'option_id'	=> 46,
					'providers'	=> $providers,
					'search'	=> $request->search,
				])
			)
			->cookie(
				'urlSearch', storeUrlCookie(46), 2880
			);
		}
		else
		{
			return redirect('/');
		}
	}

	public function show($id)
	{
		return redirect('/');
	}

	public function edit($id)
	{
		if(Auth::user()->module->where('id',46)->count()>0)
		{
			$data		= App\Module::find($this->module_id);
			$provider	= App\Provider::where('status',2)->find($id);
			if($provider != "")
			{
				return view('configuracion.proveedor.cambio',
					[
						'id' 		=> $data['father'],
						'title'		=> $data['name'],
						'details'	=> $data['details'],
						'child_id'	=> $this->module_id,
						'option_id'	=> 46,
						'provider'	=> $provider
					]);
			}
			else
			{
				$alert = "swal('', '".Lang::get("messages.record_previously_deleted")."', 'error');";
				return back()->with('alert',$alert);
			}
		}
		else
		{
			return redirect('/');
		}
	}

	public function validation(Request $request)
	{
		if ($request->ajax()) 
		{
			$response = array(
				'valid'		=> false,
				'message'	=> 'Este campo es obligatorio'
			);
			if(isset($request->reason))
			{
				$exist = App\Provider::where('businessName','LIKE',$request->reason)->where('status',2)->count();
				if($exist>0)
				{
					if(isset($request->oldReason) && $request->oldReason == $request->reason)
					{
						$response = array('valid' => true,'message' => '');
					}
					else
					{
						$response = array(
							'valid'		=> false,
							'message'	=> 'La razón social ya se encuentra registrada.'
						);
					}
				}
				else
				{
					$response = array('valid' => true,'message' => '');
				}
			}

			if(isset($request->rfc))
			{
				if(preg_match("/^([A-Z,Ñ,&]{3,4}([0-9]{2})(0[1-9]|1[0-2])(0[1-9]|1[0-9]|2[0-9]|3[0-1])[A-Z|\d]{3}){0,1}$/i", $request->rfc) || preg_match("/^XAXX1[0-9]{8}$/i", $request->rfc))
				{
					$rfc = App\Provider::where('rfc','LIKE',$request->rfc)
						->where('status',2)
                        ->where(function($q) use($request)
                        {
                            if(isset($request->oldRfc))
                            {
                                $q->where('idProvider','!=',$request->oldRfc);
                            }
                        })
                        ->count();

					if($rfc > 0)
					{
						$response = array(
                            'valid'     => false,
                            'class'     => 'error',
                            'message'   => 'El RFC ya se encuentra registrado.'
                        );
					}
					else
					{
						$response = array('valid' => true,'message' => '');
					}				
				}
				else
				{
					$response = array(
						'valid'		=> false,
						'message'	=> 'El RFC debe ser válido.'
					);
				}
			}
			return Response($response);
		}

	}

	public function validateAccount(Request $request)
	{
		if ($request->ajax())
		{
			$response = array('exists'=>'false', 'message'=>'');
			if(isset($request->account) && $request->account != "")
			{
				$accounts	= App\ProviderBanks::where('banks_idBanks', $request->bank)
							->where('account', $request->account)			
							->get();

				if (count($accounts) > 0)
				{
					$response = array('exists'=>'true', 'message'=>'El número de cuenta ya se encuentra registrado');
					return Response($response);
				}
			}

			if(isset($request->clabe) && $request->clabe != "")
			{
				$clabes	= App\ProviderBanks::where('clabe', $request->clabe)
						->get();
				if (count($clabes) > 0)
				{
					$response = array('exists'=>'true', 'message'=>'La CLABE ya se encuentra registrada');
					return Response($response);
				} 
			}
			return Response($response);
		}
	}

	public function update(Request $request, $id)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$data							= App\Module::find($this->module_id);
			$provider						= App\Provider::find($id);
			$provider->status				= 1;
			$provider->save();


			$provider_data_id 				= $provider->provider_data_id;

			$t_provider						= new App\Provider();
			$t_provider->businessName		= $request->reason;
			$t_provider->beneficiary		= $request->beneficiary;
			$t_provider->phone				= $request->phone;
			$t_provider->rfc				= $request->rfc;
			$t_provider->contact			= $request->contact;
			$t_provider->commentaries		= $request->other;
			$t_provider->status				= 2;
			$t_provider->users_id			= Auth::user()->id;
			$t_provider->address			= $request->address;
			$t_provider->number				= $request->number;
			$t_provider->colony				= $request->colony;
			$t_provider->postalCode			= $request->cp;
			$t_provider->city				= $request->city;
			$t_provider->state_idstate		= $request->state;
			$t_provider->provider_data_id	= $provider_data_id;
			$t_provider->save();

			if(isset($request->clasificationDataRemove))
			{
				for ($i=0; $i < count($request->clasificationDataRemove); $i++)
				{
					$documentRemove = App\ProviderClassificationDocs::where('id', $request->clasificationDataRemove[$i])->delete();
				}
			}

			if(isset($request->clasificationData))
			{
				$dataClass							= json_decode($request->clasificationData);

				if ($provider->providerData->providerClassification != "") 
				{
					$providerClass					= App\ProviderClassification::find($provider->providerData->providerClassification->id);
				}
				else
				{
					$providerClass					= new App\ProviderClassification;
				}

				$providerClass->provider_id			= $t_provider->idProvider;
				$providerClass->classification		= $dataClass->clasif;
				$providerClass->commentary			= $dataClass->comm;
				$providerClass->created_by			= Auth::user()->id;
				$providerClass->provider_data_id	= $provider_data_id;
				$providerClass->save();

				foreach ($dataClass->doc as $doc)
				{
					$document								= new App\ProviderClassificationDocs;
					$document->path							= $doc;
					$document->providerClassification_id	= $providerClass->id;
					$document->save();
				}
			}
			elseif($provider->providerData->providerClassification != "" && $request->status == 'Sin validar')
			{
				$providerClass	= App\ProviderClassification::where('id',$provider->providerData->providerClassification->id)->delete();
			}

			if (isset($request->delete) && count($request->delete)>0) 
			{
				App\ProviderBanks::whereIn('id',$request->delete)->update(['visible'=>0]);
			}

			if(isset($request->providerBank))
			{
				for ($i=0; $i < count($request->providerBank); $i++)
				{ 
					if($request->providerBank[$i] == "x")
					{
						$t_providerBank							= new App\ProviderBanks;
						$t_providerBank->banks_idBanks			= $request->bank[$i];
						$t_providerBank->alias					= $request->alias[$i];
						$t_providerBank->account				= $request->account[$i];
						$t_providerBank->branch					= $request->branch_office[$i];
						$t_providerBank->reference				= $request->reference[$i];
						$t_providerBank->clabe					= $request->clabe[$i];
						$t_providerBank->currency				= $request->currency[$i];
						$t_providerBank->agreement				= $request->agreement[$i];
						$t_providerBank->iban 					= $request->iban[$i];
						$t_providerBank->bic_swift 				= $request->bic_swift[$i];
						$t_providerBank->provider_data_id 		= $provider_data_id;
						$t_providerBank->save();
					}
				}
			}
			$alert = "swal('', '".Lang::get("messages.record_updated")."', 'success');";
			return redirect()->route('provider.edit',$t_provider->idProvider)->with('alert',$alert);
		}
		else
		{
			return redirect('/');
		}
	}

	public function destroy($id)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$data = App\Module::find($this->module_id);
			$provider = App\Provider::find($id);
			if($provider != "")
			{
				$provider
						->update([
							'status' => "1",
						]);
						$alert = "swal('', '".Lang::get("messages.record_deleted")."', 'success');";
				return redirect('configuration/provider')->with('alert',$alert);
			}
			else
			{
				$alert = "swal('', '".Lang::get("messages.record_previously_deleted")."', 'error');";
				return back()->with('alert',$alert);
			}
		}
		else
		{
			return redirect('/');
		}
	}

	public function uploader(Request $request)
	{
		\Tinify\setKey("DDPii23RhemZFX8YXES5OVhEP7UmdXMt");
		$response = array(
			'error'		=> 'ERROR',
			'message'	=> 'Error, por favor intente nuevamente'
		);
		if ($request->ajax()) 
		{
			if($request->realPath!='')
			{
				\Storage::disk('public')->delete('/docs/providers/'.$request->realPath);
			}
			if($request->file('path'))
			{
				$extention				= strtolower($request->path->getClientOriginalExtension());
				$nameWithoutExtention	= 'AdG'.round(microtime(true) * 1000).'_providerDoc.';
				$name					= $nameWithoutExtention.$extention;
				$destinity				= '/docs/providers/'.$name;

				if($extention=='png' || $extention=='jpg' || $extention=='jpeg')
				{
					try
					{
						$sourceData	= file_get_contents($request->path);
						$resultData	= \Tinify\fromBuffer($sourceData)->toBuffer();
						\Storage::disk('public')->put($destinity,$resultData);
						$response['error']		= 'DONE';
						$response['path']		= $name;
						$response['message']	= '';
						$response['extention']	= strtolower($extention);
					}
					catch(\Tinify\AccountException $e)
					{
						$response['message']	= $e->getMessage();
					}
					catch(\Tinify\ClientException $e)
					{
						$response['message']	= 'Por favor, verifique su archivo.';
					}
					catch(\Tinify\ServerException $e)
					{
						$response['message']	= 'Ocurrió un error al momento de comprimir su archivo. Por favor, intente después de unos minutos. Si ve este mensaje por un periodo de tiempo más larga, por favor contacte a soporte con el código: SAPIT2.';
					}
					catch(\Tinify\ConnectionException $e)
					{
						$response['message']	= 'Ocurrió un problema de conexión, por favor verifique su red e intente nuevamente.';
					}
					catch(Exception $e)
					{
						
					}
				}
				else
				{
					try
					{
						$myTask = new CompressTask('project_public_3366528f2ee24af6a83e7cb142128e1c__nwaXf03e5ca1e49cb9f1d272dda7e327c6df','secret_key_09de0b6ac33ca88293b6dd69b35c8564_CZyihbc2f9c54892e685d558169cc933a4dfd');
						\Storage::disk('public')->put('/docs/uncompressed_pdf/'.$name,\File::get($request->path));
						$file = $myTask->addFile(public_path().'/docs/uncompressed_pdf/'.$name);
						$myTask->setCompressionLevel('recommended');
						$myTask->execute();
						$myTask->setOutputFilename($nameWithoutExtention);
						$myTask->download(public_path().'/docs/compressed_pdf');
						\Storage::disk('public')->move('/docs/compressed_pdf/'.$name,$destinity);
						\Storage::disk('public')->delete(['/docs/uncompressed_pdf/'.$name,'/docs/compressed_pdf/'.$name]);
						$response['error']		= 'DONE';
						$response['path']		= $name;
						$response['message']	= '';
						$response['extention']	= $extention;
					}
					catch (\Ilovepdf\Exceptions\StartException $e)
					{
						$response['message']	= 'Ocurrió un problema, por favor intente nuevamente.';
					}
					catch (\Ilovepdf\Exceptions\AuthException $e)
					{
						$response['message']	= 'Ocurrió un problema, por favor intente nuevamente.';
					}
					catch (\Ilovepdf\Exceptions\UploadException $e)
					{
						$response['message']	= 'Ocurrió un error al momento de comprimir su archivo. Por favor, intente después de unos minutos.';
					}
					catch (\Ilovepdf\Exceptions\ProcessException $e)
					{
						$response['message']	= 'Ocurrió un error al momento de comprimir su archivo. Por favor, intente después de unos minutos.';
					}
					catch (\Exception $e)
					{
						$response['message_console']	= $e->getMessage();
					}
				}
			}
			return Response($response);
		}
	}
	
	public function zipCode(Request $request)
	{
		if ($request->ajax())
		{
			if($request->search!= '')
			{
				$result = array();
				$clave = App\CatZipCode::where('zip_code','LIKE','%'.$request->search.'%')
				->get();
				foreach ($clave as $c)
				{
					$tempArray['id']	= $c->zip_code;
					$tempArray['text']	= $c->zip_code;
					$result['results'][]	= $tempArray;
				}
				if(count($clave)==0)
				{
					$result['results'] = [];
				}
				return Response($result);
			}
		}
	}
}
