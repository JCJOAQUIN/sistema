<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\Module;
use App\Property;
use App\PropertyPayments;
use App\PropertyLegalDocuments;
use Ilovepdf\CompressTask;
use Excel;
use Lang;
use Illuminate\Support\Facades\DB;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Common\Entity\Row;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Box\Spout\Common\Entity\Style\Color;
use Box\Spout\Common\Entity\Style\CellAlignment;
use Box\Spout\Common\Entity\Style\Border;
use Box\Spout\Writer\Common\Creator\Style\BorderBuilder;

class AdministrationPropertyController extends Controller
{
	protected $module_id = 289;
	public function index()
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{   
			$data = Module::find($this->module_id);
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
		if (Auth::user()->module->where('id',290)->count()>0) 
		{
			$data = Module::find($this->module_id);
			return view('administracion.inmuebles.alta',
			[
				'id'		=> $data['father'],
				'title'		=> $data['name'],
				'details'	=> $data['details'],
				'child_id'	=> $this->module_id,
				'option_id'	=> 290
			]);
		}
	}

	public function search(Request $request)
	{
		if (Auth::user()->module->where('id',291)->count()>0) 
		{
			$data = Module::find($this->module_id);

			$properties = Property::where(function($query) use ($request)
			{
				if ($request->property != "") 
				{
					$query->where('property','like','%'.$request->property.'%');
				}

				if ($request->location != "") 
				{
					$query->where('location','like','%'.$request->location.'%');
				}

				if ($request->type_property != "") 
				{
					$query->where('type_property',$request->type_property);
				}

				if ($request->use_property != "") 
				{
					$query->where('use_property',$request->use_property);
				}
			})
			->orderBy('created_at', 'DESC')
			->paginate(10);

			return view('administracion.inmuebles.busqueda',
			[
				'id'			=> $data['father'],
				'title'			=> $data['name'],
				'details'		=> $data['details'],
				'child_id'		=> $this->module_id,
				'option_id'		=> 291,
				'properties'	=> $properties,
				'property'		=> $request->property,
				'location'		=> $request->location,
				'type_property'	=> $request->type_property,
				'use_property'	=> $request->use_property,
			]);
		}
		else
		{
			return redirect('error');
		}
	}

	public function edit(Request $request,Property $property)
	{
		if (Auth::user()->module->where('id',291)->count()>0) 
		{
			$data = Module::find($this->module_id);

			return view('administracion.inmuebles.alta',
			[
				'id'		=> $data['father'],
				'title'		=> $data['name'],
				'details'	=> $data['details'],
				'child_id' 	=> $this->module_id,
				'option_id' => 291,
				'property'	=> $property,
			]);
		}
		else
		{
			return redirect('error');
		}
	}

	public function store(Request $request)
	{
		if (Auth::user()->module->where('id',290)->count()>0) 
		{
			$property						= new Property();
			$property->property				= $request->property;
			$property->location				= $request->location;
			$property->type_property		= $request->type_property;
			$property->use_property			= $request->use_property;
			$property->number_of_rooms		= $request->number_of_rooms;
			$property->number_of_bathrooms	= $request->number_of_bathrooms;
			$property->parking_lot			= $request->parking_lot;
			$property->kitchen_room			= $request->kitchen_room;
			$property->garden				= $request->garden;
			$property->boardroom			= $request->boardroom;
			$property->furnished			= $request->furnished;
			$property->measurements			= $request->measurements;
			$property->users_id				= Auth::user()->id;
			$property->save();

			if (isset($request->t_payment_type) && count($request->t_payment_type)>0) 
			{
				for ($i=0; $i < count($request->t_payment_type); $i++) 
				{ 
					$payment				= new PropertyPayments;
					$payment->payment_type	= $request->t_payment_type[$i];
					$payment->periodicity	= $request->t_periodicity[$i];
					$payment->date_range	= $request->t_date_range[$i];
					$payment->amount		= $request->t_amount[$i];
					$payment->path			= $request->t_path[$i];
					$payment->property_id 	= $property->id;
					$payment->user_id 		= Auth::user()->id;
					$payment->save();
				}
			}

			if (isset($request->t_legal_document) && count($request->t_legal_document)>0) 
			{
				for ($i=0; $i < count($request->t_legal_document); $i++) 
				{ 
					$legal_document					= new PropertyLegalDocuments;
					$legal_document->legal_document	= $request->t_legal_document[$i];
					$legal_document->description	= $request->t_description[$i];
					$legal_document->path			= $request->t_path_legal_document[$i];
					$legal_document->property_id	= $property->id;
					$legal_document->user_id		= Auth::user()->id;
					$legal_document->save();
				}
			}

			$alert = "swal('', '".Lang::get("messages.record_created")."', 'success');";
			return redirect()->route('property.edit',['property'=>$property->id])->with('alert',$alert);

		}
		else
		{
			return redirect('error');
		}
	}

	public function update(Request $request, Property $property)
	{
		if (Auth::user()->module->where('id',291)->count() > 0) 
		{
			$property->property					= $request->property;
			$property->location				= $request->location;
			$property->type_property			= $request->type_property;
			$property->use_property				= $request->use_property;
			$property->number_of_rooms		= $request->number_of_rooms;
			$property->number_of_bathrooms	= $request->number_of_bathrooms;
			$property->parking_lot			= $request->parking_lot;
			$property->kitchen_room			= $request->kitchen_room;
			$property->garden					= $request->garden;
			$property->boardroom				= $request->boardroom;
			$property->furnished				= $request->furnished;
			$property->measurements			= $request->measurements;
			$property->save();


			if (isset($request->delete) && count($request->delete)) 
			{
				PropertyPayments::whereIn('id',$request->delete)->delete();
			}

			if (isset($request->deleteLegalDocument) && count($request->deleteLegalDocument)) 
			{
				PropertyLegalDocuments::whereIn('id',$request->deleteLegalDocument)->delete();
			}

			if (isset($request->t_payment_type) && count($request->t_payment_type)>0) 
			{
				for ($i=0; $i < count($request->t_payment_type); $i++) 
				{ 
					if ($request->payment_id[$i] == "x") 
					{
						$payment = new PropertyPayments;
					}
					else
					{
						$payment = PropertyPayments::find($request->payment_id[$i]);
					}

					$payment->payment_type	= $request->t_payment_type[$i];
					$payment->periodicity	= $request->t_periodicity[$i];
					$payment->date_range	= $request->t_date_range[$i];
					$payment->amount		= $request->t_amount[$i];
					$payment->path			= $request->t_path[$i];
					$payment->property_id 	= $property->id;
					$payment->user_id 		= Auth::user()->id;
					$payment->save();
				}
			}

			if (isset($request->t_legal_document) && count($request->t_legal_document)>0) 
			{
				for ($i=0; $i < count($request->t_legal_document); $i++) 
				{ 
					if ($request->legal_document_id[$i] == "x") 
					{
						$legal_document = new PropertyLegalDocuments;
					}
					else
					{
						$legal_document = PropertyLegalDocuments::find($request->legal_document_id[$i]);
					}
					$legal_document->legal_document	= $request->t_legal_document[$i];
					$legal_document->description	= $request->t_description[$i];
					$legal_document->path			= $request->t_path_legal_document[$i];
					$legal_document->property_id	= $property->id;
					$legal_document->user_id		= Auth::user()->id;
					$legal_document->save();
				}
			}

			$alert = "swal('', '".Lang::get("messages.record_updated")."', 'success');";
			return redirect()->route('property.edit',['property'=>$property->id])->with('alert',$alert);
		}
		else
		{
			return redirect('error');
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
					\Storage::disk('public')->delete('/docs/properties/'.$request->realPath[$i]);
				}
				
			}
			if($request->file('path'))
			{
				$extention				= strtolower($request->path->getClientOriginalExtension());
				$nameWithoutExtention	= 'AdG'.round(microtime(true) * 1000).'_property_doc.';
				$name					= $nameWithoutExtention.$extention;
				$destinity				= '/docs/properties/'.$name;
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

	public function export(Request $request)
	{
		if (Auth::user()->module->where('id',291)->count()>0) 
		{
			$properties = DB::table('properties')->selectRaw('
							properties.id as id,
							properties.property as property,
							properties.location as location,
							properties.type_property as type_property,
							properties.use_property as use_property,
							properties.number_of_rooms as number_of_rooms,
							properties.number_of_bathrooms as number_of_bathrooms,
							properties.parking_lot as parking_lot,
							properties.kitchen_room as kitchen_room,
							properties.garden as garden,
							properties.boardroom as boardroom,
							properties.furnished as furnished,
							properties.measurements as measurements,
							property_payments.payment_type as payment_type,
							property_payments.periodicity as periodicity,
							property_payments.date_range as date_range,
							property_payments.amount as amount
						')
						->leftJoin('property_payments','property_payments.property_id','properties.id')
						->where(function($query) use ($request)
						{
							if ($request->property != "") 
							{
								$query->where('property','like','%'.$request->property.'%');
							}

							if ($request->location != "") 
							{
								$query->where('location','like','%'.$request->location.'%');
							}

							if ($request->type_property != "") 
							{
								$query->where('type_property',$request->type_property);
							}

							if ($request->use_property != "") 
							{
								$query->where('use_property',$request->use_property);
							}
						})
						->orderby('properties.id', 'ASC')
						->get();

			if(count($properties)==0 || $properties==null)
			{
				return redirect()->back()->with('alert',"swal('', '".Lang::get("messages.result_not_found")."', 'error');");
			}
			$defaultStyle   = (new StyleBuilder())->setFontName('Calibri')->setFontSize(12)->build();
			$currencyFormat = (new StyleBuilder())->setFormat('_("$"* #,##0.00_),_("$"* \(#,##0.00\),_("$"* "-"??_),_(@_)')->build();
			$rowDark        = (new StyleBuilder())->setBackgroundColor('F0F0F0')->setCellAlignment(CellAlignment::LEFT)->build();
			$mhStyleCol1    = (new StyleBuilder())->setBackgroundColor('70A03F')->setFontColor(Color::WHITE)->build();
			$mhStyleCol2    = (new StyleBuilder())->setBackgroundColor('ED704D')->setFontColor(Color::WHITE)->build();
			$alignment		= (new StyleBuilder())->setCellAlignment(CellAlignment::LEFT)->build();
			$writer         = WriterEntityFactory::createXLSXWriter();
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('Inmuebles.xlsx');
			$mainHeaderArr = ['Información General','','','','','','','','','','','','','Información de Pagos','','',''];
			$tmpMHArr      = [];
			foreach($mainHeaderArr as $k => $mh)
			{
				if($k <= 12)
				{
					$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol1);
				}
				else
				{
					$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol2);
				}
			}
			$rowFromValues = WriterEntityFactory::createRow($tmpMHArr);
			$writer->addRow($rowFromValues);
			$headerArr    = ['ID','Inmueble','Ubicación','Tipo de Inmueble','Uso de Inmueble','Número de Habitaciones','Número de Baños','Estacionamiento','Cocina','Jardín','Sala de Juntas','Amueblado','Medidas', 'Tipo de Pago','Periodicidad','Periodo Pagado','Monto'];
			$tmpHeaderArr = [];
			foreach($headerArr as $k => $sh)
			{
				if($k <= 12)
				{
					$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$mhStyleCol1);
				}
				else
				{
					$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$mhStyleCol2);
				}
			}
			$rowFromValues = WriterEntityFactory::createRow($tmpHeaderArr);
			$writer->addRow($rowFromValues);
			$tempFolio     = '';
			$kindRow       = true;
			foreach($properties as $property)
			{
				if($tempFolio != $property->id)
				{
					$tempFolio = $property->id;
					$kindRow = !$kindRow;
				}
				else
				{
					$property->id					= null;
					$property->property				= '';
					$property->location				= '';
					$property->type_property		= '';
					$property->use_property			= '';
					$property->number_of_rooms		= '';
					$property->number_of_bathrooms	= '';
					$property->parking_lot			= '';
					$property->kitchen_room			= '';
					$property->garden				= '';
					$property->boardroom			= '';
					$property->furnished			= '';
					$property->measurements			= '';
				}
				$tmpArr = [];
				foreach($property as $k => $p)
				{
					if(in_array($k,['amount']))
					{
						if($p != '')
						{
							$tmpArr[] = WriterEntityFactory::createCell((double)$p, $currencyFormat);
						}
						else
						{
							$tmpArr[] = WriterEntityFactory::createCell($p);
						}
					}
					else
					{
						$tmpArr[] = WriterEntityFactory::createCell($p);
					}
				}
				if($kindRow)
				{
					$rowFromValues = WriterEntityFactory::createRow($tmpArr,$rowDark);
				}
				else
				{
					$rowFromValues = WriterEntityFactory::createRow($tmpArr, $alignment);
				}
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
