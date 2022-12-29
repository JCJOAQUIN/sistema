<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\URL;
use App;
use Alert;
use Auth;
use Ilovepdf\CompressTask;
use PDF;
use Lang;
use Carbon\Carbon;
use App\Functions\Files;

class ConfiguracionTdcController extends Controller
{
   private $module_id = 202;

	public function index()
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$data 	= App\Module::find($this->module_id);
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
			return abort(404);
		}
	}

	public function create()
	{
		if(Auth::user()->module->where('id',204)->count()>0)
		{
			$data			= App\Module::find($this->module_id);
			$enterprises	= App\Enterprise::where('status','ACTIVE')->get();
			return view('configuracion.tdc.alta',
				[
					'id'			=> $data['father'],
					'title'			=> $data['name'],
					'details'		=> $data['details'],
					'child_id'		=> $this->module_id,
					'option_id'		=> 204,
					'enterprises'	=> $enterprises,
				]);
		}
		else
		{
			return abort(404);
		}
	}

	public function store(Request $request)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$tdc						= new App\CreditCards();
			$tdc->idEnterprise			= $request->enterprise_id;
			$tdc->idAccAcc				= $request->account_id;
			$tdc->idBanks				= $request->bank_id;
			$tdc->name_credit_card		= $request->name_credit_card;
			$tdc->alias					= $request->alias;
			$tdc->assignment			= $request->assignment;
			$tdc->credit_card			= $request->credit_card;
			$tdc->status				= $request->status;
			$tdc->type_credit			= $request->type_credit;
			$tdc->type_credit_other		= $request->type_credit_other;
			$tdc->cutoff_date			= $request->cutoff_date != "" ? Carbon::createFromFormat('d-m-Y',$request->cutoff_date)->format('Y-m-d') : null;
			$tdc->payment_date			= $request->payment_date != "" ? Carbon::createFromFormat('d-m-Y',$request->payment_date)->format('Y-m-d') : null;
			$tdc->limit_credit			= $request->limit_credit;
			$tdc->type_currency			= $request->type_currency;
			$tdc->principal_aditional	= $request->principal_aditional;
			$tdc->principal_card		= $request->principal_card;
			$tdc->principal_card_id 	= $request->principal_card_id;
			$tdc->save();
			$alert  = "swal('', '".Lang::get("messages.record_created")."', 'success');";
			return redirect('configuration/credit-card/'.$tdc->idcreditCard.'/edit')->with('alert',$alert);
		}
		else
		{
			return redirect('/error');
		}
	}

	public function search(Request $request)
	{
		if(Auth::user()->module->where('id',205)->count()>0)
		{
			$data			= App\Module::find($this->module_id);
			$account_id		= $request->account_id;
			$bank_id		= $request->bank_id;
			$enterprise_id	= $request->enterprise_id;

			$banksAccounts = App\CreditCards::where(function($query) use($account_id,$enterprise_id,$bank_id)
			{
				if ($account_id != '') 
				{
					$query->where('idAccAcc',$account_id);
				}
				if ($enterprise_id != '') 
				{
					$query->where('idEnterprise',$enterprise_id);
				}
				if ($bank_id != '') 
				{
					$query->where('idBanks',$bank_id);
				}
			})
			->orderBy('idcreditCard','DESC')
			->paginate(10);
			return response (
				view('configuracion.tdc.busqueda',
				[
					'id'			=> $data['father'],
					'title'			=> $data['name'],
					'details'		=> $data['details'],
					'child_id'		=> $this->module_id,
					'option_id'		=> 205,
					'bank_id'		=> $bank_id,
					'account_id'	=> $account_id, 
					'enterprise_id'	=> $enterprise_id,
					'banksAccounts'	=> $banksAccounts
				])
			)->cookie(
				"urlSearch", storeUrlCookie(205), 2880
			);
		}
		else
		{
			return abort(404);
		}
	}

	public function edit($id)
	{
		if(Auth::user()->module->where('id',205)->count()>0)
		{
			$data			= App\Module::find($this->module_id);
			$enterprises	= App\Enterprise::where('status','ACTIVE')->get();
			$tdc			= App\CreditCards::find($id);
			return view('configuracion.tdc.cambio',
				[
					'id'			=> $data['father'],
					'title'			=> $data['name'],
					'details'		=> $data['details'],
					'child_id'		=> $this->module_id,
					'option_id'		=> 205,
					'enterprises'	=> $enterprises,
					'tdc'			=> $tdc
				]);
		}
		else
		{
			return abort(404);
		}
	}

	public function update(Request $request, $id)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$banks						= App\CreditCards::find($id);
			$banks->idEnterprise		= $request->enterprise_id;
			$banks->idAccAcc			= $request->account_id;
			$banks->idBanks				= $request->bank_id;
			$banks->name_credit_card	= $request->name_credit_card;
			$banks->alias				= $request->alias;
			$banks->assignment			= $request->assignment;
			$banks->credit_card			= $request->credit_card;
			$banks->status				= $request->status;
			$banks->type_credit			= $request->type_credit;
			$banks->type_credit_other	= $request->type_credit_other;
			$banks->cutoff_date			= $request->cutoff_date != "" ? Carbon::createFromFormat('d-m-Y',$request->cutoff_date)->format('Y-m-d') : null;
			$banks->payment_date		= $request->payment_date != "" ? Carbon::createFromFormat('d-m-Y',$request->payment_date)->format('Y-m-d') : null;
			$banks->limit_credit		= $request->limit_credit;
			$banks->type_currency		= $request->type_currency;
			$banks->principal_aditional	= $request->principal_aditional;
			$banks->principal_card		= $request->principal_card;
			$banks->principal_card_id 	= $request->principal_card_id;
			$banks->save();

			if (isset($request->realPath) && count($request->realPath)>0) 
			{
				for ($i=0; $i < count($request->realPath); $i++) 
				{ 
					$documents 					= new App\CreditCardDocuments();
					$new_file_name = Files::rename($request->realPath[$i],$banks->credit_card);
					$documents->path 			= $new_file_name;
					$documents->idcreditCard 	= $id;
					$documents->save();
				}
			}

			$alert  = "swal('', '".Lang::get("messages.record_updated")."', 'success');";
			return redirect('configuration/credit-card/'.$id.'/edit')->with('alert',$alert);
		}
		else
		{
			return redirect('/error');
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
				for ($i=0; $i < count($request->realPath); $i++) 
				{ 
					\Storage::disk('public')->delete('/docs/credit_card/'.$request->realPath[$i]);
				}
				
			}
			if($request->file('path'))
			{
				$extention				= strtolower($request->path->getClientOriginalExtension());
				$nameWithoutExtention	= 'Estado_de_cuenta_'.round(microtime(true) * 1000).'.';
				$name					= $nameWithoutExtention.$extention;
				$destinity				= '/docs/credit_card/'.$name;
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

	public function accountStatus (Request $request,$id)
	{
		if (Auth::user()->module->where('id',205)->count()>0) 
		{
			$tdc 		= App\CreditCards::find($id);
			if($request->month != NULL || $request->year != NULL)
			{
				$payments 	= App\Payment::where('account',$tdc->idAccAcc)
				->whereMonth('paymentDate',$request->month)
				->whereYear('paymentDate',$request->year)
				->orderBy('paymentDate','ASC')
				->get();

				$monthlyAverageExpense = $payments->avg('amount');
			}
			else{
				$payments = NULL;
				$alert  = "swal('', 'No hay mes o año seleccionado.', 'error');";
				return redirect('configuration/credit-card/'.$id.'/edit')->with('alert',$alert);
			}

			if (count($payments)>0) 
			{
				//return view('configuracion.tdc.estado_cuenta',['payments'	=> $payments,'tdc'		=> $tdc,'month'		=> $request->month,'year'		=> $request->year,'monthlyAverageExpense' => $monthlyAverageExpense]);
				$pdf = PDF::loadView('configuracion.tdc.estado_cuenta',[
					'payments'	=> $payments,
					'tdc'		=> $tdc,
					'month'		=> $request->month,
					'year'		=> $request->year,
					'monthlyAverageExpense' => $monthlyAverageExpense
				]);
				return $pdf->download('Estado_de_cuenta_'.$request->month.'_'.$request->year.'.pdf');
			}
			else
			{
				$alert  = "swal('', 'No hay registros.', 'info');";
				return redirect('configuration/credit-card/'.$id.'/edit')->with('alert',$alert);
			}
		}
		else
		{
			return redirect('/home');
		}
	}

	public function validation(Request $request)
	{
		$response = array(
			'valid'		=> false,
			'message'	=> 'Error, por favor seleccione un Banco y el tipo de Tarjeta'
		);
		
		if(preg_match("/^(\d{16}){0,1}$/i", $request->credit_card))
		{
			if (isset($request->principal_id) && $request->principal_id == '1' || isset($request->principal_id) && $request->principal_id == '2')
			{
				$valid_tdc = App\CreditCards::where('credit_card',$request->credit_card)->get();
				if(count($valid_tdc) > 0)
				{
					$response = array(
						'valid'		=>false,
						'message'	=>'Error, el número de tarjeta ingresado ya existe, favor de verificar'
					);
				}
				if (isset($request->bank_id) && $request->bank_id != "" && isset($request->credit_card) && $request->credit_card != "") 
				{
					$validate = App\CreditCards::whereRaw('REPLACE(credit_card," ","") = "'.$request->credit_card.'"')->where('idBanks',$request->bank_id)->where('credit_card','!=',"")->get();
					if (count($validate)>0)
					{
	
						if(isset($request->oldCreditCard) && $request->oldCreditCard===$request->credit_card)
						{
							$response = array('valid' => true);
						}
						else
						{
							$response = array(
								'valid'		=>false,
								'message'	=>'Error, el número de tarjeta ingresado ya existe, favor de verificar'
							);
						}
					}
					else
					{
						$response = array('valid' => true, 'message' => count($validate));
					}
				}
				elseif (isset($request->bank_id) && $request->bank_id != "" && !isset($request->credit_card)) 
				{
					$response = array(
						'valid'		=> false,
						'message'	=> 'Este campo es obligatorio'
					);
				}
			}
		}
		else
		{
			$response = array(
				'valid'		=> false,
				'message'	=> 'El número de tarjeta debe ser de 16 dígitos.'
			);
			
		}
		return Response($response);
	}
}
