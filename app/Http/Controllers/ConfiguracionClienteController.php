<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\URL;
use App\http\Requests\GeneralRequest;
use App;
use Alert;
use Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Excel;
use Lang;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Common\Entity\Row;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Box\Spout\Common\Entity\Style\Color;
use Box\Spout\Common\Entity\Style\CellAlignment;
use Box\Spout\Common\Entity\Style\Border;
use Box\Spout\Writer\Common\Creator\Style\BorderBuilder;

class ConfiguracionClienteController extends Controller
{
	private $module_id = 182;
	public function index()
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$data  = App\Module::find($this->module_id);
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
		if(Auth::user()->module->where('id',183)->count()>0)
		{
			$data   = App\Module::find($this->module_id);
			return view('configuracion.cliente.alta',
				[
					'id'		=> $data['father'],
					'title'		=> $data['name'],
					'details'	=> $data['details'],
					'child_id'	=> $this->module_id,
					'option_id'	=> 183
				]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function store(Request $request)
	{
		$data						= App\Module::find($this->module_id);
		$t_client					= new App\Clients();
		$t_client->businessName		= $request->reason;
		$t_client->email			= $request->email;
		$t_client->phone			= $request->phone;
		$t_client->rfc				= $request->rfc;
		$t_client->contact			= $request->contact;
		$t_client->commentaries		= $request->other;
		$t_client->status			= 2;
		$t_client->users_id			= Auth::user()->id;
		$t_client->address			= $request->address;
		$t_client->number			= $request->number;
		$t_client->colony			= $request->colony;
		$t_client->postalCode		= $request->cp;
		$t_client->city				= $request->city;
		$t_client->state_idstate	= $request->state;
		$t_client->save();

		$alert = "swal('', '".Lang::get("messages.record_created")."', 'success');";
		return redirect('configuration/client')->with('alert',$alert);
	}

	public function search(Request $request)
	{
		if(Auth::user()->module->where('id',184)->count()>0)
		{
			$search  = $request->search;
			$clients = App\Clients::where(function($query) use ($request)
							{
								$query->where('rfc','LIKE','%'.$request->search.'%')
									->orWhere('businessName','LIKE','%'.$request->search.'%');
							})
							->where('status',2)
							->orderBy('businessName','desc')
							->paginate(10);
			$data           = App\Module::find($this->module_id);
			$countClients =count($clients);
			return response(
				view('configuracion.cliente.busqueda',
				[
					'id'        => $data['father'],
					'title'     => $data['name'],
					'details'   => $data['details'],
					'child_id'  => $this->module_id,
					'option_id' => 184,
					'clients'	=> $clients,
					'search'	=> $search,
					'countClients' => $countClients
				])
			)->cookie(
				"urlSearch", storeUrlCookie(184),  2880
			);
		}
		else
		{
			return redirect('/');
		}
	}

/*	public function getClients(Request $request)
	{
		if($request->ajax())
		{
			$tableStr   = "";
			$clients  = App\Clients::where(function($query) use ($request)
							{
								$query->where('rfc','LIKE','%'.$request->search.'%')
									->orWhere('businessName','LIKE','%'.$request->search.'%');
							})
							->where('status',2)
							->get();
			if (count($clients) >= 1) 
			{
				$tableStr = "<table id='table' class='table table-striped'><thead class='thead-dark'><tr><th>ID</th><th>RFC</th><th>Nombre</th><th>Acci&oacute;n</th></tr></thead><tbody>";
				foreach ($clients as $client)
				{
					$tableStr .="<tr>
								<td>".$client->idClient."</td>
								<td>".$client->rfc."</td>
								<td>".$client->businessName."</td>
								<td class='text-nowrap'><a title='Editar Cliente' href="."'".url::route('client.edit',$client->idClient)."'"."class='btn btn-green'><span class='icon-pencil'></span></a> 
									<a title='Eliminar Cliente' href="."'".url::route('client.destroy2',$client->idClient)."'"." class='btn btn-red client-delete'><span class='icon-bin'></span></a></td>
								</tr>";
				}
				$tableStr .= "</tbody></table>";
				return Response($tableStr);
			}
			else
			{
				$notfound = '<div id="not-found" style="display:block;">RESULTADO NO ENCONTRADO</div>';
				return Response($notfound);
			}
		}
	}
*/
	
	public function edit($id)
	{
		if(Auth::user()->module->where('id',184)->count()>0)
		{
			$data       = App\Module::find($this->module_id);
			$client   = App\Clients::find($id);
			if($client != "")
			{
				return view('configuracion.cliente.cambio',
					[
						'id'		=> $data['father'],
						'title'		=> $data['name'],
						'details'	=> $data['details'],
						'child_id'	=> $this->module_id,
						'option_id'	=> 184,
						'client'	=> $client
					]);
			}
			else
			{
				return redirect('/error');
			}
		}
		else
		{
			return redirect('/');
		}
	}

	public function validationrfc(Request $request)
	{
		if ($request->ajax())
		{
			$response = array(
				'valid'		=> false,
				'message'	=> 'El campo es requerido.'
			);
			if(isset($request->rfc) && $request->rfc !== null)
			{
				if(preg_match("/^([A-Z,Ñ,&]{3,4}([0-9]{2})(0[1-9]|1[0-2])(0[1-9]|1[0-9]|2[0-9]|3[0-1])[A-Z|\d]{3}){0,1}$/i", $request->rfc) || preg_match("/^XAXX1[0-9]{8}$/i", $request->rfc))
				{
					$rfc	= App\Clients::where('rfc',$request->rfc)->where('status',2)->count();
					if ($rfc>0 && $request->oldRfc != $request->rfc)
					{
						$response = array(
							'valid'		=> false,
							'message'	=> 'El RFC ya se encuentra registrado.'
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
			return response($response);
		}
	}
	public function validationReason(Request $request)
	{
		if ($request->ajax())
		{
			$response = array(
				'valid'		=> false,
				'message'	=> 'El campo es requerido.'
			);
			if(isset($request->reason) && $request->reason !== null)
			{
				$reason	= App\Clients::where('businessName',$request->reason)->where('status',2)->count();
				if ($reason>0 && $request->oldReason != $request->reason)
				{
					$response = array(
						'valid'		=> false,
						'message'	=> 'La Razón Social ya se encuentra registrada.'
					);
				}
				else
				{
					$response = array('valid' => true,'message' => '');
				}
			}
			return response($response);
		}
	}

	public function update(Request $request, $id)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$data						= App\Module::find($this->module_id);
			$client						= App\Clients::find($id);
			$client->status				= 1;
			$client->save();
			$t_client					= new App\Clients();
			$t_client->businessName		= $request->reason;
			$t_client->email			= $request->email;
			$t_client->phone			= $request->phone;
			$t_client->rfc				= $request->rfc;
			$t_client->contact			= $request->contact;
			$t_client->commentaries		= $request->other;
			$t_client->status			= 2;
			$t_client->users_id			= Auth::user()->id;
			$t_client->address			= $request->address;
			$t_client->number			= $request->number;
			$t_client->colony			= $request->colony;
			$t_client->postalCode		= $request->cp;
			$t_client->city				= $request->city;
			$t_client->state_idstate	= $request->state;
			$t_client->save();

		   
			$alert = "swal('', '".Lang::get("messages.record_updated")."', 'success');";
			return redirect('configuration/client/'.$t_client->idClient.'/edit')->with('alert',$alert);
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
			$client = App\Clients::find($id)
					->update([
						'status' => "1",
					]);
			$alert = "swal('', '".Lang::get("messages.record_deleted")."', 'success');";
			return redirect('configuration/client')->with('alert',$alert);
		}
		else
		{
			return redirect('/');
		}
	}

	public function export(Request $request)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{	
			$data			= App\Module::find($this->module_id);
			$search  = $request->search;
			$clients = DB::table('clients')->selectRaw(
						'
							clients.idClient as clientId,
							clients.businessName as clientBusinessName,
							clients.rfc as clientRFC,
							clients.contact as clientContact,
							clients.email as clientEmail,
							clients.phone as clientPhone,
							IF(clients.status=1, "Activo", "Inactivo") as clientStatus,
							clients.address as clientAddress,
							clients.number as clientNumber,
							clients.colony as clientColony,
							clients.postalcode as clientPostalCode,
							clients.city as clientCity,
							states.description as stateName
						')
						->leftJoin('states', 'states.idstate', 'clients.state_idstate')
						->where(function($query) use ($request)
						{
							$query->where('clients.rfc','LIKE','%'.$request->search.'%')
								->orWhere('clients.businessName','LIKE','%'.$request->search.'%');
						})
						->where('clients.status',2)
						->orderBy('clients.businessName','ASC')
						->get();
					
			if(count($clients)==0 || $clients==null)
			{
				return redirect()->back()->with('alert',"swal('', '".Lang::get("messages.result_not_found")."', 'error');");
			}
			$defaultStyle   = (new StyleBuilder())->setFontName('Calibri')->setFontSize(12)->build();
			$rowDark        = (new StyleBuilder())->setBackgroundColor('F0F0F0')->setCellAlignment(CellAlignment::LEFT)->build();
			$mhStyleCol1    = (new StyleBuilder())->setBackgroundColor('000000')->setFontColor(Color::WHITE)->build();
			$mhStyleCol2    = (new StyleBuilder())->setBackgroundColor('104f64')->setFontColor(Color::WHITE)->build();
			$alignment		= (new StyleBuilder())->setCellAlignment(CellAlignment::LEFT)->build();
			$writer         = WriterEntityFactory::createXLSXWriter();
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('Reporte de clientes.xlsx');
			$writer->getCurrentSheet()->setName('Clientes');

			$headers = ['Reporte de Clientes','','','','','','','','','','','',''];
			$tempHeaders      = [];
			foreach($headers as $k => $mh)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($mh,$mhStyleCol1);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);

			$subHeader    = ['ID','Razón Social','RFC', 'Contacto','Email','Telefono','Estado','Dirección','Número','Colonia','Código Postal','Ciudad','Estado'];
			$tempSubHeader = [];
			foreach($subHeader as $k => $sh)
			{
				$tempSubHeader[] = WriterEntityFactory::createCell($sh,$mhStyleCol2);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
			$writer->addRow($rowFromValues);

			foreach($clients as $data)
			{
				$tmpArr = [];
				foreach($data as $k => $r)
				{
					$tmpArr[] = WriterEntityFactory::createCell($r);
				}
				$rowFromValues = WriterEntityFactory::createRow($tmpArr, $alignment);
				$writer->addRow($rowFromValues);
			}
			return $writer->close();
		}
		else
		{
			return redirect('error');
		}

	}
}
