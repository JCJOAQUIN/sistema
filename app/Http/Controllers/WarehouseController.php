<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\URL;
use App;
use Alert;
use Auth;
use Excel;
use Lang;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Ilovepdf\CompressTask;
use PDF;
use App\Functions\Files;
use App\WarehouseRemove;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Common\Entity\Row;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Box\Spout\Common\Entity\Style\Color;
use Box\Spout\Common\Entity\Style\CellAlignment;
use Box\Spout\Common\Entity\Style\Border;
use Box\Spout\Writer\Common\Creator\Style\BorderBuilder;

class WarehouseController extends Controller
{
	private $module_id = 110;
	public function index()
	{
		if(Auth::user()->module->where('id',$this->module_id)->count() > 0)
		{
			$data  = App\Module::find($this->module_id);
			return view('layouts.child_module',
				[
					'id'        => $data['id'],
					'title'     => $data['name'],
					'details'   => $data['details'],
					'child_id'  => 41
				]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function search(Request $request)
	{
		if(Auth::user()->module->where('id',213)->count()>0)
		{
			$data	= App\Module::find($this->module_id);
			$category	= $request->cat;
			$place_id	= $request->place_id;
			$enterprise	= $request->enterpriseid;
			$account_id	= $request->account_id;
			$concept	= $request->concept;
			$mindate	= $request->mindate !="" ? Carbon::createFromFormat('d-m-Y',$request->mindate) : null;
			$maxdate	= $request->maxdate !="" ? Carbon::createFromFormat('d-m-Y',$request->maxdate) : null;

			$results = App\Warehouse::where(function($query) use ($category, $place_id, $enterprise, $account_id, $concept, $mindate, $maxdate)
				{
					if($category)
					{
						$query->where('warehouses.warehouseType',$category);
					}
					if ($place_id != "")
					{
						$query->where('place_location',$place_id);
					}
					if ($enterprise != "")
					{
						$query->whereHas('lot',function($q) use($enterprise)
						{
							$q->where('lots.idEnterprise',$enterprise);
						});
					}
					if ($account_id != "")
					{
						$query->whereHas('lot',function($q) use($account_id)
						{
							$q->where('lots.account',$account_id);
						});
					}
					if($concept)
					{
						$query->whereHas('cat_c', function($query) use($concept)
						{
							$query->where('description','LIKE','%'.$concept.'%');
						});
					}
					if ($mindate != "" && $maxdate != "")
					{
						$query->whereHas('lot',function($q) use($mindate,$maxdate)
						{
							$q->whereBetween('lots.date',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 23:59:59')]);
						});
					}
				})
				->whereHas('lot', function($q)
				{
					$q->where('lots.status',2);
				})
				->leftJoin('cat_warehouse_concepts','warehouses.concept','cat_warehouse_concepts.id')
				->leftJoin('places','warehouses.place_location','places.id')
				->groupBy('concept','place_location')
				->selectRaw('
					warehouses.warehouseType warehouse_type,
					places.place as place_location,
					SUM(warehouses.quantity) as quantity,
					cat_warehouse_concepts.description as concept')
				->where('warehouses.status',1)
				->where('quantity','>',0)
				->orderBy('cat_warehouse_concepts.id','DESC')
				->paginate(15);
			return view('almacen.busqueda.index',
				[
					'id'         => 110,
					'title'      => $data['name'],
					'details'    => $data['details'],
					'child_id'   => 41,
					'option_id'  => 213,
					'results'    => $results,
					'category'   => $category,
					'place_id'   => $place_id,
					'enterprise' => $enterprise,
					'account_id' => $account_id,
					'concept'    => $concept,
					'mindate'    => $request->mindate,
					'maxdate'    => $request->maxdate
				]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function stationery()
	{
		if(Auth::user()->module->where('id',111)->count()>0)
		{
			$data   = App\Module::find($this->module_id);
			return view('almacen.alta.index',
				[
					'id'            => $data['id'],
					'title'         => $data['name'],
					'details'       => $data['details'],
					'option_id'     => 111,
					'child_id'      => $this->module_id,
					'category_id'   => [1],
				]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function tool(Request $request)
	{
		if(Auth::user()->module->where('id',113)->count()>0)
		{
			$data   = App\Module::find($this->module_id);
			return view('almacen.alta.alta',
				[
					'id'            => 110,
					'title'         => $data['name'],
					'details'       => $data['details'],
					'child_id'      => 41,
					'option_id'     => 113,
					'selected_item' => 1
				]);
		}
		else
		{
			return abort(404);
		}
	}

	public function toolMassive(Request $request)
	{
		if(Auth::user()->module->where('id',113)->count()>0)
		{
			$data   = App\Module::find($this->module_id);
			return view('almacen.alta.alta',
				[
					'id'            => 110,
					'title'         => $data['name'],
					'details'       => $data['details'],
					'option_id'     => 113,
					'child_id'      => 41,
					'selected_item' => 2
				]);
		}
		else
		{
			return abort(404);
		}
	}

	public function uploadCsv(Request $request)
	{
		$response = array(
			'error'     => 'ERROR',
			'message'   => 'Error, por favor intente nuevamente'
		);
		if ($request->ajax()) 
		{
			if($request->realPathPurchase!='')
			{
				\Storage::disk('public')->delete('/docs/warehouse_xml/'.$request->realPathPurchase);
			}
			if($request->file('csv_file'))
			{
				$extention              = strtolower($request->csv_file->getClientOriginalExtension());
				$nameWithoutExtention   = 'AdG'.round(microtime(true) * 1000).'_warehouse_doc.';
				$name                   = $nameWithoutExtention.$extention;

				$destinity              = '/docs/warehouse_xml/'.$name;

				\Storage::disk('public')->put($destinity,\File::get($request->csv_file));

				$response['error']      = 'DONE';
				$response['path']       = $name;
				$response['message']    = '';
				$response['extention']  = strtolower($extention);

			}
			return Response($response);
		}
	}

	public function checkMassive(Request $request)
	{
		if(Auth::user()->module->where('id',113)->count()>0)
		{
			if($request->file('csv_file')->isValid())
			{
				$delimiters = [";" => 0, "," => 0];
				$handle     = fopen($request->file('csv_file'), "r");
				$firstLine  = fgets($handle);
				fclose($handle); 
				foreach ($delimiters as $delimiter => &$count)
				{
					$count = count(str_getcsv($firstLine, $delimiter));
				}
				$separator = array_search(max($delimiters), $delimiters);
				if($separator == $request->separator)
				{
					$name		= '/docs/warehouse_xml/'.$request->realPathPurchase;
					$path		= \Storage::disk('public')->path($name);
					$records	= array();

					if (($handle = fopen($path, "r")) !== FALSE)
					{
						$first  = true;
						while (($data = fgetcsv($handle, 1000, $request->separator)) !== FALSE)
						{
							if($first)
							{
								$data[0]	= preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $data[0]);
								$first		= false;
							}
							$records[]	= $data;
						}
						fclose($handle);
					}
					array_walk($records, function(&$a) use ($records)
					{
						$a = array_combine($records[1], $a);
					});
					array_shift($records);
					array_shift($records);

					$headers = [
						'folio',
						'titulo',
						'id',
						'ubicacion_sede',
						'categoria',
						'cuenta',
						'codigo',
						'cantidad',
						'danados',
						'unidad',
						'descripcion',
						'precio_unitario',
						'subtotal',
						'iva',
						'impuesto_adicional',
						'retenciones',
						'total'
					];
					if(empty($records) || empty($records[0]['folio']) || empty($records[0]['id']) || empty($records[0]['categoria']) || array_diff($headers, array_keys($records[0])))
					{
						$alert	= "swal('','".Lang::get("messages.file_upload_error")."', 'error');";
						return back()->with('alert',$alert);	
					}

					$data   = App\Module::find($this->module_id);
					return view('almacen.alta.verificar_compras',
					[
						'id'			=> 110,
						'title'			=> $data['name'],
						'details'		=> $data['details'],
						'option_id'		=> 113,
						'child_id'		=> 41,
						'selected_item'	=> 2,
						'records'		=> $records
					]);
				}
				else
				{
					$alert	= "swal('','".Lang::get("messages.separator_error")."', 'error');";
					return back()->with('alert',$alert);
				}
			}
			else
			{
				$alert	= "swal('','".Lang::get("messages.file_upload_error")."', 'error');";
				return back()->with('alert',$alert);
			}
		}
		else
		{
			return redirect('error');
		}
	}

	public function toolPurchase(Request $request)
	{
		if(Auth::user()->module->where('id',113)->count()>0)
		{
			$data   = App\Module::find($this->module_id);
			$result = App\RequestModel::where('kind',1)
				->whereHas('budget',function($q)
				{
					$q->where('status',1);
				})
				->whereIn('status',[5,10,11,12])
				->where('statusWarehouse',0)
				->where('goToWarehouse',1)
				->whereIn('idEnterpriseR',Auth::user()->inChargeEnt(113)->pluck('enterprise_id'))
				->where(function($query) use ($request)
				{
					if($request->search != '')
					{
						$query->where('folio','like','%'.$request->search.'%')
							->orWhereHas('purchases',function($q) use ($request)
							{
								$q->where('title','like','%'.$request->search.'%');
							})
							->orWhereHas('elaborateUser',function($q) use ($request)
							{
								$q->where('name','like','%'.$request->search.'%');
							})
							->orWhereHas('requestEnterprise',function($q) use ($request)
							{
								$q->where('name','like','%'.$request->search.'%');
							});
					}
				})
				->has('purchases.documents')
				->orderby('folio','desc')
				->paginate(10);
			return view('almacen.alta.alta',
				[
					'id'            => 110,
					'title'         => $data['name'],
					'details'       => $data['details'],
					'option_id'     => 113,
					'child_id'      => 41,
					'selected_item' => 3,
					'search'        => $request->search,
					'result'        => $result
				]);
		}
		else
		{
			return abort(404);
		}
	}

	public function computer(Request $request)
	{
		if(Auth::user()->module->where('id',112)->count()>0)
		{
			$data   = App\Module::find($this->module_id);
			return view('almacen.computo',
				[
					'id'		=> 110,
					'title'		=> $data['name'],
					'details'	=> $data['details'],
					'child_id'	=> 41,
					'option_id'	=> 112
				]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function inventory(Request $request)
	{
		if ($request->ajax())
		{
			return response()->json(App\Warehouse::where('warehouseType',$request->type)->orderName()->groupBy('concept')->get());
		}
	}

	public function stationeryStore(Request $request)
	{
		$alert	= "swal('','".Lang::get("messages.record_created")."', 'success');";
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$data                   = App\Module::find($this->module_id);
			$t_lot                  = new App\Lot();
			$t_lot->subtotal        = $request->sub_total_articles;
			$t_lot->iva             = $request->iva_articles;
			$t_lot->total           = $request->total;
			$t_lot->articles        = $request->total_articles;
			$t_lot->date            = Carbon::createFromFormat('d-m-Y',$request->date)->format('Y-m-d');
			$t_lot->idEnterprise    = $request->enterprise_id;
			$t_lot->idElaborate     = Auth::user()->id;
			$t_lot->idKind          = 7;
			$t_lot->save();
			$idlot  = $t_lot->idlot;

			if (isset($request->realPath) && count($request->realPath)>0) 
			{
				for ($i=0; $i < count($request->realPath); $i++) 
				{ 
					$t_file         = new App\DocumentsWarehouse();
					$new_file_name  = Files::rename($request->realPath[$i],$idlot);
					$t_file->path   = $new_file_name;
					$t_file->idlot  = $idlot;
					$t_file->save();
				}
			}

			for ($i=0; $i < count($request->tconcept_id); $i++) 
			{ 
				if (is_numeric($request->tconcept_id[$i])) 
				{
					$t_warehouse = App\Warehouse::where('idwarehouse',$request->tconcept_id[$i])->first();
					$cat         = App\CatWarehouseConcept::where('id',$t_warehouse->concept)->count();
					if($cat == 0)
					{
						$cat = App\CatWarehouseConcept::create([
							'description' => $t_warehouse->concept,
							'warehouseType' => $request->tcategory_id[$i],
							]);
					}
					else
					{
						$cat = App\CatWarehouseConcept::where('id',$t_warehouse->concept)->first();
					}
					$t_warehouse_n               = new App\Warehouse();
					$t_warehouse_n->concept      = $cat->id;
					$quantity                    = $request->tquanty[$i] - $request->tdamaged[$i];
					$t_warehouse_n->quantity     = $quantity;
					$t_warehouse_n->quantityReal = $request->tquanty[$i];
					$t_warehouse_n->damaged      = $request->tdamaged[$i];
					if($quantity == 0)
					{
						$t_warehouse_n->status = 0;
					}

					$t_warehouse_n->short_code = $request->tshort_code[$i];
					$t_warehouse_n->long_code  = $request->tlong_code[$i];
					if($request->tmeasurement_id[$i])
					{
						$t_warehouse_n->measurement = $request->tmeasurement_id[$i];
					}
					$t_warehouse_n->commentaries   = $request->tcommentaries[$i];
					$t_warehouse_n->amountUnit     = $request->tamount[$i]/$request->tquanty[$i];
					$t_warehouse_n->iva            = $request->tiva[$i];
					$t_warehouse_n->typeTax        = $request->tiva_kind[$i];
					$t_warehouse_n->subtotal       = $request->tsub_total[$i];
					$t_warehouse_n->amount         = $request->tamount[$i];
					$t_warehouse_n->idLot          = $t_lot->idlot;
					$t_warehouse_n->warehouseType  = $request->tcategory_id[$i];
					$t_warehouse_n->place_location = $request->tplace_id[$i];
					$t_warehouse_n->account        = $request->taccount_id[$i];
					if($request->tcategory_id[$i] == 4)
					{
						$t_warehouse_n->type      = $request->ttype[$i];
						$t_warehouse_n->brand     = $request->tbrand[$i];
						$t_warehouse_n->storage   = $request->tstorage[$i];
						$t_warehouse_n->processor = $request->tprocessor[$i];
						$t_warehouse_n->ram       = $request->tram[$i];
						$t_warehouse_n->sku       = $request->tsku[$i];
					}
					$t_warehouse_n->save();
				}
				else
				{
					$t_warehouse    = new App\Warehouse();
					$cat            = App\CatWarehouseConcept::where('description',$request->tconcept_name[$i])->count();
					if($cat == 0)
					{
						$cat = App\CatWarehouseConcept::create([
							'description' => $request->tconcept_name[$i],
							'warehouseType' => $request->tcategory_id[$i],
							]);
					}
					else
					{
						$cat = App\CatWarehouseConcept::where('description',$request->tconcept_name[$i])->first();
					}
					$quantity                  = $request->tquanty[$i] - $request->tdamaged[$i];
					$t_warehouse->quantity     = $quantity;
					$t_warehouse->quantityReal = $request->tquanty[$i];
					$t_warehouse->damaged      = $request->tdamaged[$i];
					if($quantity == 0)
					{
						$t_warehouse->status = 0;
					}
					$t_warehouse->concept    = $cat->id;
					$t_warehouse->short_code = $request->tshort_code[$i];
					$t_warehouse->long_code  = $request->tlong_code[$i];
					if($request->tmeasurement_id[$i])
					{
						$t_warehouse->measurement   = $request->tmeasurement_id[$i];
					}
					$t_warehouse->commentaries   = $request->tcommentaries[$i];
					$t_warehouse->amountUnit     = $request->tamount[$i]/$request->tquanty[$i];
					$t_warehouse->iva            = $request->tiva[$i];
					$t_warehouse->typeTax        = $request->tiva_kind[$i];
					$t_warehouse->subtotal       = $request->tsub_total[$i];
					$t_warehouse->amount         = $request->tamount[$i];
					$t_warehouse->idLot          = $t_lot->idlot;
					$t_warehouse->warehouseType  = $request->tcategory_id[$i];
					$t_warehouse->place_location = $request->tplace_id[$i];
					$t_warehouse->account        = $request->taccount_id[$i];
					if($request->tcategory_id[$i] == 4)
					{
						$t_warehouse->type      = $request->ttype[$i];
						$t_warehouse->brand     = $request->tbrand[$i];
						$t_warehouse->storage   = $request->tstorage[$i];
						$t_warehouse->processor = $request->tprocessor[$i];
						$t_warehouse->ram       = $request->tram[$i];
						$t_warehouse->sku       = $request->tsku[$i];
					}
					$t_warehouse->save();
				}
			}
			return redirect('/warehouse')->with('alert',$alert);
		}
		else
		{
			return redirect('/');
		}
	}

	public function stationeryStoreCompras(Request $request)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$rq					 = App\RequestModel::where('folio',$request->folio)->first();
			$alert	= "swal('','".Lang::get("messages.record_created")."', 'success');";
			$data                = App\Module::find($this->module_id);
			$t_lot               = new App\Lot();
			$t_lot->subtotal     = $request->sub_total_articles_compras;
			$t_lot->iva          = $request->iva_articles_compras;
			$t_lot->total        = $request->total;
			$t_lot->articles     = $request->total_articles_compras;
			$t_lot->date         = Carbon::createFromFormat('d-m-Y',$request->date)->format('Y-m-d');
			$t_lot->idEnterprise = $request->enterprise_id_compras;
			$t_lot->idElaborate  = Auth::user()->id;
			$t_lot->idKind       = 7;
			$t_lot->save();
			
			$rq      = App\RequestModel::where('folio',$request->folio)->first();
			$rq_user = $rq->requestUser;

			try
			{
				$this->createNotificationAlmacen($rq_user->id,$request->folio,'almacen');
				Mail::to($rq_user->email)->send(new App\Mail\Almacen($request->folio));
			}
			catch(\Exception $e)
			{
				$alert	= "swal('','".Lang::get("messages.record_delivered_no_mail")."', 'success');";
			}

			$users_delivery = App\User::whereHas('module',function($q)
					{
						$q->where('id', 95);
					})
					->where('active',1)
					->where('notification',1)
					->get();
			foreach ($users_delivery as $user)
			{
				try
				{
					$this->createNotificationAlmacen($user->id,$request->folio,'almacen');
					Mail::to($user->email)->send(new App\Mail\Almacen($request->folio));
				}
				catch(\Exception $e)
				{
					$alert	= "swal('','".Lang::get("messages.record_delivered_no_mail")."', 'success');";
				}
			}
			App\LotIdFolios::create([
				'idLot'     => $t_lot->idlot,
				'idFolio'   => $request->folio,
			]);

			$idlot  = $t_lot->idlot;

			for ($i=0; $i < count($request->tconcept_name); $i++) 
			{
				$t_warehouse    = new App\Warehouse();
				$cat            = App\CatWarehouseConcept::where('description',$request->tconcept_name[$i])->count();
				
				if($cat == 0)
				{
					$cat    = App\CatWarehouseConcept::create([
							'description'   => $request->tconcept_name[$i],
							'warehouseType' => $request->tcategory_id[$i],
						]);
				}
				else
				{
					$cat    = App\CatWarehouseConcept::where('description',$request->tconcept_name[$i])->first();
				}

				$quantity                  = $request->tquanty[$i] - $request->tdamaged[$i];
				$t_warehouse->concept      = $cat->id;
				$t_warehouse->quantity     = $quantity;
				$t_warehouse->quantityReal = $request->tquanty[$i];
				$t_warehouse->damaged      = $request->tdamaged[$i];

				if($quantity == 0)
				{
					$t_warehouse->status = 0;
				}

				if($request->tmeasurement_id[$i])
				{
					$t_warehouse->measurement   = $request->tmeasurement_id[$i];
				}

				
				$t_warehouse->amountUnit     = $request->tamount[$i]/$request->tquanty[$i];
				$t_warehouse->iva            = $request->tiva[$i];
				$t_warehouse->typeTax        = $request->tiva_kind[$i];
				$t_warehouse->subtotal       = $request->tsub_total[$i];
				$t_warehouse->amount         = $request->tamount[$i];
				$t_warehouse->idLot          = $t_lot->idlot;
				$t_warehouse->warehouseType  = $request->tcategory_id[$i];
				$t_warehouse->short_code     = $request->tcode[$i];
				$t_warehouse->place_location = $request->place_id_compras;
				$t_warehouse->account        = $request->taccount_id[$i];

				$t_warehouse->commentaries      = $request->tcommentaries[$i] != "" ? $request->tcommentaries[$i] : null;

				if($request->tcategory_id[$i] == 4)
				{
					$t_warehouse->type      = $request->ttype[$i];
					$t_warehouse->brand     = $request->tbrand[$i];
					$t_warehouse->storage   = $request->tstorage[$i];
					$t_warehouse->processor = $request->tprocessor[$i];
					$t_warehouse->ram       = $request->tram[$i];
					$t_warehouse->sku       = $request->tsku[$i];
				}

				$t_warehouse->save();

				$dp = App\DetailPurchase::where('idDetailPurchase',$request->tidPurchase[$i])->first();
				
				$dp->statusWarehouse = 1;
				$dp->save();
			}

			$total_altas =  App\DetailPurchase::where('idPurchase',$rq->purchases()->first()->idPurchase)
				->where('statusWarehouse',1)
				->count();

			if($total_altas == $rq->purchases()->first()->detailPurchase()->count())
			{
				$rq->statusWarehouse = 1;
				$rq->save();
			}
			
			return redirect('/warehouse')->with('alert',$alert);
		}
		else
		{
			return redirect('/');
		}
	}

	public function computerStore(Request $request)
	{
		$alert	= "swal('','".Lang::get("messages.record_created")."', 'success');";
		if(Auth::user()->module->where('id',112)->count()>0)
		{
			for ($i=0; $i < count($request->ttype); $i++) 
			{ 
				$computer                   = new App\ComputerEquipment();
				$computer->idElaborate      = Auth::user()->id;
				$computer->idEnterprise     = $request->enterprise_id;
				$computer->account          = $request->account_id;
				$computer->place_location   = $request->place_id;
				$computer->quantity         = $request->tquanty[$i];
				$computer->type             = $request->ttype[$i];
				$computer->brand            = $request->tbrand[$i];
				$computer->storage          = $request->tstorage[$i];
				$computer->processor        = $request->tprocessor[$i];
				$computer->ram              = $request->tram[$i];
				$computer->sku              = $request->tsku[$i];
				$computer->typeTax          = $request->tiva_kind[$i];
				$computer->subtotal         = $request->tsub_total[$i];
				$computer->iva              = $request->tiva[$i];
				$computer->amountUnit       = $request->tamountunit[$i];
				$computer->commentaries     = $request->tcommentaries[$i];
				$computer->amountTotal      = $request->tamount[$i];
				$computer->date             = Carbon::createFromFormat('d-m-Y',$request->date)->format('Y-m-d');
				$computer->save();
			}
			return redirect('/warehouse')->with('alert',$alert);
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
			'error'     => 'ERROR',
			'message'   => 'Error, por favor intente nuevamente'
		);
		if ($request->ajax()) 
		{
			if($request->realPath!='')
			{
				for ($i=0; $i < count($request->realPath); $i++) 
				{ 
					\Storage::disk('public')->delete('/docs/warehouse/'.$request->realPath[$i]);
				}
			}
			if($request->file('path'))
			{
				$extention              = strtolower($request->path->getClientOriginalExtension());
				$nameWithoutExtention   = 'AdG'.round(microtime(true) * 1000).'_warehouse.';
				$name                   = $nameWithoutExtention.$extention;
				$destinity              = '/docs/warehouse/'.$name;
				if($extention=='png' || $extention=='jpg' || $extention=='jpeg')
				{
					try
					{
						$sourceData             = file_get_contents($request->path);
						$resultData             = \Tinify\fromBuffer($sourceData)->toBuffer();
						\Storage::disk('public')->put($destinity,$resultData);
						$response['error']      = 'DONE';
						$response['path']       = $name;
						$response['message']    = '';
						$response['extention']  = strtolower($extention);
					}
					catch(\Tinify\AccountException $e)
					{
						$response['message']    = $e->getMessage();
					}
					catch(\Tinify\ClientException $e)
					{
						$response['message']    = 'Por favor, verifique su archivo.';
					}
					catch(\Tinify\ServerException $e)
					{
						$response['message']    = 'Ocurrió un error al momento de comprimir su archivo. Por favor, intente después de unos minutos. Si ve este mensaje por un periodo de tiempo más larga, por favor contacte a soporte con el código: SAPIT2.';
					}
					catch(\Tinify\ConnectionException $e)
					{
						$response['message']    = 'Ocurrió un problema de conexión, por favor verifique su red e intente nuevamente.';
					}
					catch(Exception $e)
					{
						
					}
				}
				else
				{
					try
					{
						$myTask                 = new CompressTask('project_public_3366528f2ee24af6a83e7cb142128e1c__nwaXf03e5ca1e49cb9f1d272dda7e327c6df','secret_key_09de0b6ac33ca88293b6dd69b35c8564_CZyihbc2f9c54892e685d558169cc933a4dfd');
						\Storage::disk('public')->put('/docs/uncompressed_pdf/'.$name,\File::get($request->path));
						$file                   = $myTask->addFile(public_path().'/docs/uncompressed_pdf/'.$name);
						$myTask->setCompressionLevel('recommended');
						$myTask->execute();
						$myTask->setOutputFilename($nameWithoutExtention);
						$myTask->download(public_path().'/docs/compressed_pdf');
						\Storage::disk('public')->move('/docs/compressed_pdf/'.$name,$destinity);
						\Storage::disk('public')->delete(['/docs/uncompressed_pdf/'.$name,'/docs/compressed_pdf/'.$name]);
						$response['error']      = 'DONE';
						$response['path']       = $name;
						$response['message']    = '';
						$response['extention']  = $extention;
					}
					catch (\Ilovepdf\Exceptions\StartException $e)
					{
						$response['message']    = 'Ocurrió un problema, por favor intente nuevamente.';
					}
					catch (\Ilovepdf\Exceptions\AuthException $e)
					{
						$response['message']    = 'Ocurrió un problema, por favor intente nuevamente.';
					}
					catch (\Ilovepdf\Exceptions\UploadException $e)
					{
						$response['message']    = 'Ocurrió un error al momento de comprimir su archivo. Por favor, intente después de unos minutos.';
					}
					catch (\Ilovepdf\Exceptions\ProcessException $e)
					{
						$response['message']    = 'Ocurrió un error al momento de comprimir su archivo. Por favor, intente después de unos minutos.';
					}
					catch (\Exception $e)
					{
						$response['message_console']    = $e->getMessage();
					}
				}
			}
			return Response($response);
		}
	}

	public function warehouseTable(Request $request)
	{
		$table      = "";
		$enterprise = $request->idEnterprise;
		$concept    = $request->concept;
		$category   = $request->category;
		$place_id   = $request->place_id;
		$max        = null;
		$min        = null;
		$account_id = $request->account_id;

		if ($request->mindate != null)
		{
			$date1      = strtotime($request->mindate);
			$mindate    = date('Y-m-d',$date1);
			$date2      = strtotime($request->maxdate);
			$maxdate    = date('Y-m-d',$date2);
			$min        = $mindate;
			$max        = $maxdate;
		}

		$data = App\Warehouse::where(function($query) use ($enterprise, $min, $max, $place_id, $account_id, $category)
		{
			if ($enterprise != "")
			{
				if ($enterprise != "todas")
				{
					$query->whereHas('lot',function($q) use($enterprise)
					{
						$q->where('lots.idEnterprise',$enterprise);
					});
				}
			}
			if ($account_id != "")
			{
				if ($account_id != "todas")
				{
					$query->whereHas('lot',function($q) use($account_id)
					{
						$q->where('lots.account',$account_id);
					});
				}
			}
			if ($min != "" && $max != "")
			{
				$query->whereHas('lot',function($q) use($min,$max)
				{
					$q->whereBetween('lots.date',[''.$min.' '.date('00:00:00').'',''.$max.' '.date('23:59:59').'']);
				});
			}
			if ($place_id != null )
			{
				$query->where('place_location',$place_id);
			}
			$query->whereHas('lot',function($q) use($min,$max)
			{
				$q->where('lots.status',2);
			});
			if($category)
			{
				$query->where('warehouseType',$category);
			}
		})
		->whereHas('cat_c', function($query) use($concept)
		{
			if($concept)
			{
				$query->where('description','LIKE','%'.$concept.'%');
			}
		})
		->groupBy('concept','place_location')
		->selectRaw('
			idwarehouse,
			warehouseType,
			place_location,
			quantity,
			warehouses.quantity,
			sum(warehouses.quantity) as quantity,
			concept')
		->where('status',1)
		->where('quantity','>',0)
		->paginate(10);

		return \Response::JSON(array(
			'table'         => $data,
			'pagination'    => (string) $data->links()
		));
	}

	public function warehouseExcel(Request $request)
	{
		$category	= $request->cat;
		$place_id	= $request->place_id;
		$enterprise	= $request->idEnterprise;
		$account_id	= $request->account_id;
		$concept	= $request->concept;
		$max		= null;
		$min		= null;
		if ($request->mindate != null)
		{
			$date1		= strtotime($request->mindate);
			$mindate	= date('Y-m-d',$date1);
			$date2		= strtotime($request->maxdate);
			$maxdate	= date('Y-m-d',$date2);
			$min		= $mindate;
			$max		= $maxdate;
		}
		$results = App\Lot::select('lots.*')
			->where(function($query) use ($category, $place_id, $enterprise, $account_id, $concept, $max, $min)
			{
				if($category)
				{
					$query->whereHas('warehouseStationary',function($q) use($category)
					{
						$q->where('warehouseType',$category);
					});
				}
				if ($place_id != "")
				{
					$query->whereHas('warehouseStationary',function($q) use($place_id)
					{
						$q->where('place_location',$place_id);
					});
				}
				if ($enterprise != "")
				{
					$query->where('idEnterprise',$enterprise);
				}
				if ($account_id != "")
				{
					$q->where('account',$account_id);
				}
				if($concept)
				{
					$query->whereHas('warehouseStationary',function($q) use($concept)
					{
						$q->whereHas('cat_c', function($q) use($concept)
						{
							$q->where('description','LIKE','%'.$concept.'%');
						});
					});
				}
				if ($min != "" && $max != "")
				{
					$query->whereBetween('lots.date',[''.$min.' '.date('00:00:00').'',''.$max.' '.date('23:59:59').'']);
				}
			})
			->where('status',2)
			->get();
		
		Excel::create('Reporte-Inventario', function($excel) use ($results)
		{
			$excel->sheet('Datos',function($sheet) use ($results)
			{
				$sheet->setStyle([
					'font' => [
						'name'	=> 'Calibri',
						'size'	=> 12
					],
					'alignment' => [
						'vertical' => 'center',
					]
				]);
				$sheet->setColumnFormat(array(
					'C'	=> '"$"#,##0.00_-',
					'D'	=> '"$"#,##0.00_-',
					'J'	=> '"$"#,##0.00_-',
				));
				$sheet->mergeCells('A1:J1');

				$sheet->cell('A1:J1', function($cells)
				{
					$cells->setBackground('#000000');
					$cells->setFontColor('#ffffff');
				});
				$sheet->cell('A2:J2', function($cells)
				{
					$cells->setBackground('#1d353d');
					$cells->setFontColor('#ffffff');
				});
				$sheet->cell('A1:J2', function($cells)
				{
					$cells->setFontWeight('bold');
					$cells->setAlignment('center');
					$cells->setFont(array('family' => 'Calibri','size' => '16','bold' => true));
				});

				$sheet->row(1,['Reporte de inventario']);
				$sheet->row(2,['Lote','Empresa','Inversión Total ($)','Inversión en Artículos ($)','Cantidad','Artículo','Categoría','Cuenta','Ubicación/sede','Importe por Artículos ($)']);
				$beginMerge	= 2;
				foreach($results as $lot)
				{
					$tempCount = 0;
					$row       = [];
					$row[]     = $lot->idlot;
					$row[]     = $lot->enterprise->name;
					$row[]     = $lot->total;
					$row[]     = $lot->articles;
					$first     = true;
					$merge     = false;
					foreach($lot->warehouseStationary as $art)
					{
						if ($art->status != 0)
						{
							if (!$first)
							{
								$row	= array();
								$row[]	= '';
								$row[]	= '';
								$row[]	= '';
								$row[]	= '';
							}
							else
							{
								$first = false;
								$beginMerge++;
								$merge = true;
							}
							$row[] = $art->quantity;
							$row[] = $art->cat_c->description;
							$row[] = $art->warehouse->description;
							$row[] = $art->accounts ? $art->accounts->account.' '.$art->accounts->description.'('.$art->accounts->content.')' : '';
							$row[] = $art->place_location ? $art->location->place : "" ;
							$row[] = $art->amount;
							$tempCount++;
							$sheet->appendRow($row);
						}
					}
					if($merge)
					{
						$endMerge = $beginMerge+$tempCount-1;
						$sheet->mergeCells('A'.$beginMerge.':A'.$endMerge);
						$sheet->mergeCells('B'.$beginMerge.':B'.$endMerge);
						$sheet->mergeCells('C'.$beginMerge.':C'.$endMerge);
						$sheet->mergeCells('D'.$beginMerge.':D'.$endMerge);
						$beginMerge = $endMerge;
					}
				}
			});
		})->export('xls');
	}

	public function computerTable(Request $request)
	{
		$concept    = $request->concept;
		$type       = $request->type;
		$enterprise = $request->enterprise_id;
		$account    = $request->account_id;
		$place      = $request->place_id;
		$max        = null;
		$min        = null;
		$account_id = $request->account_id;

		if ($request->mindate != null)
		{
			$date1      = strtotime($request->mindate);
			$mindate    = date('Y-m-d',$date1);
			$date2      = strtotime($request->maxdate);
			$maxdate    = date('Y-m-d',$date2);
			$min        = $mindate;
			$max        = $maxdate;
		}

		$data = App\ComputerEquipment::where(function($query) use ($type, $enterprise, $account, $place, $min, $max, $concept)
		{
			if($concept != "")
			{
				$query->where('brand',$concept);
			}
			if($type != "")
			{
				if ($type != "todos") 
				{
					$query->where('type',$type);
				}
			}
			if($enterprise != "")
			{
				if ($type != "todos") 
				{
					$query->where('idEnterprise',$enterprise);
				}
			}
			if($account != "")
			{
				if ($account != "todos") 
				{
					$query->where('account',$account);
				}
			}
			if($place != "")
			{
				if ($place != "todos") 
				{
					$query->where('place_location',$place);
				}
			}
			if ($min != "" && $max != "")
			{
				$query->whereBetween('date',[''.$min.' '.date('00:00:00').'',''.$max.' '.date('23:59:59').'']);
			}
		})
		->where('status',1)
		->where('quantity','>',0)
		->with('enterprise','accounts','location')
		->paginate(10);
		return \Response::JSON(array(
			'table'         => $data, 
			'pagination'    => (string) $data->links()
		));
	}

	public function computerExcel(Request $request)
	{
		$table      = "";
		$type       = $request->type_export;
		$enterprise = $request->enterprise_id;
		$account    = $request->account_id;
		$place      = $request->place_id;
		$max        = null;
		$min        = null;
		$account_id = $request->account_id;
		$concept    = $request->concept_export;

		if ($request->mindate_export != null)
		{
			$date1      = strtotime($request->mindate_export);
			$mindate    = date('Y-m-d',$date1);
			$date2      = strtotime($request->maxdate_export);
			$maxdate    = date('Y-m-d',$date2);
			$min        = $mindate;
			$max        = $maxdate;
		}

		$equipments = App\ComputerEquipment::where(function($query) use ($type, $enterprise, $account, $place, $min, $max, $concept)
		{
			if($concept != "")
			{
				$query->where('brand',$concept);
			}
			if($type != "")
			{
				if ($type != "todos") 
				{
					$query->where('type',$type);
				}
			}
			if($enterprise != "")
			{
				if ($type != "todos") 
				{
					$query->where('idEnterprise',$enterprise);
				}
			}
			if($account != "")
			{
				if ($account != "todos") 
				{
					$query->where('account',$account);
				}
			}
			if($place != "")
			{
				if ($place != "todos") 
				{
					$query->where('place_location',$place);
				}
			}
			if ($min != "" && $max != "")
			{
				
				$query->whereBetween('date',[''.$min.' '.date('00:00:00').'',''.$max.' '.date('23:59:59').'']);
			}
		})
		->where('status',1)
		->get();

		Excel::create('Reporte-Inventario-Computo', function($excel) use ($type,$equipments)
		{
			$excel->sheet('Datos',function($sheet) use ($type,$equipments)
			{
				$sheet->setStyle([
						'font' => [
							'name'  => 'Calibri',
							'size'  => 12
						],
						'alignment' => [
							'vertical' => 'center',
						]
				]);
				$sheet->mergeCells('A1:N1');
				$sheet->cell('A1:N1', function($cells)
				{
					$cells->setBackground('#000000');
					$cells->setFontColor('#ffffff');
					$cells->setFontWeight('bold');
					$cells->setAlignment('center');
					$cells->setFont(array('family'=>'Calibri','size'=>16,'bold'=>true));
				});
				$sheet->cell('A2:N2', function($cells)
				{
					$cells->setBackground('#1d353d');
					$cells->setFontColor('#ffffff');
					$cells->setFontWeight('bold');
					$cells->setAlignment('center');
					$cells->setFont(array('family'  => 'Calibri','size' => 14,'bold' => true));
				});
				$sheet->row(1,['Repoorte de inventario de equipo de cómputo']);
				$sheet->row(2,['ID','Empresa','Cuenta','Ubicación/sede','Cantidad','Tipo','Marca','Almacenamiento','Procesador','Memoria RAM','SKU','Comentarios','Importe Unitario','Importe Total']);

				foreach ($equipments as $equipment)
				{
					switch ($equipment->type) 
					{
						case "1":
							$equip  = "Smartphone";
							break;

						case "2":
							$equip  = "Tablet";
							break;

						case "3":
							$equip  = "Laptop";
							break;

						case "4":
							$equip  = "Desktop";
							break;
						
						default:
							break;
					}
					$row    = [];
					$row[]  = $equipment->id;
					$row[]  = $equipment->idEnterprise ? $equipment->enterprise->name : '';
					$row[]  = $equipment->account ? $equipment->accounts->account.' '.$equipment->accounts->description.' ('.$equipment->accounts->content.')' : '';
					$row[]  = $equipment->place_location ? $equipment->location->place : '';
					$row[]  = $equipment->quantity;
					$row[]  = $equip;
					$row[]  = $equipment->brand;
					$row[]  = $equipment->storage;
					$row[]  = $equipment->processor;
					$row[]  = $equipment->ram;
					$row[]  = $equipment->sku;
					$row[]  = $equipment->commentaries;
					$row[]  = $equipment->amountUnit;
					$row[]  = $equipment->amountTotal;
					$sheet->appendRow($row);
				}
			});
		})->export('xls');
	}

	public function search_w(Request $request)
	{
		if ($request->ajax())
		{
			$concept	= $request->concept;
			$concepts	= App\Warehouse::whereHas('cat_c', function($query) use($concept)
				{
					if($concept)
					{
						$query->where('description','LIKE','%'.$concept.'%');
					}
				})
				->where('status',1)
				->where('quantity','>',0)
				->orderBy('warehouseType','DESC')
				->with('measurementD')
				->with('cat_c','location','wareHouse')
				->paginate(10);

			if(count($concepts)>0)
			{
				$html		= '';
				$body		= [];
				$modelBody	= [];
				$modelHead	= [
					[
						["value" => "Cantidad"],
						["value" => "Concepto"],
						["value" => "Código corto"],
						["value" => "Código largo"],
						["value" => "Ubicación/sede"],
						["value" => "Categoría"],
						["value" => "Acción"]
					]
				];
				foreach($concepts as $c)
				{
					$measurement	= $c->measurementD;
					$measurementId	= "";
					$measurementDes	= "";
					if($measurement != null)
					{
						$measurementId	= $c->measurementD->id;
						$measurementDes = $c->measurementD->description;
					}
					$amount = $c->amount/$c->quantityReal;
					$body = [ "classEx"	=> "search_lot_".$c->idwarehouse,
						[							
							"content"	=>
							[
								"label" => $c->quantity
							]
						],
						[
							"content"	=>
							[
								[
									"label" 	=> $c->cat_c->description
								],
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" value=\"".$c->cat_c->description."\"",
									"classEx"		=> "search_concept"
								]

							]
						],
						[
							"content"	=>
							[
								"label"		=> $c->short_code != null ? $c->short_code : '---'
							]
						],
						[
							"content"	=>
							[
								"label"		=> $c->long_code != null ? $c->long_code : '---'
							]
						],
						[
							"classEx"	=> "place_location_text",
							"content"	=>
							[
								"label"		=> $c->place_location != null ?  $c->location->place : "---"
							]
						],
						[
							"classEx"	=> "search_type",
							"content"	=>
							[
								"label"		=> $c->wareHouse->description
							]
						],
						[
							"content"	=>
							[
								[
									"kind"			=> "components.buttons.button",
									"variant"		=> "success",
									"attributeEx"	=> "title=\"Agregar\" type=\"button\" value=\"".$c->idwarehouse."\"",
									"classEx"		=> "edit",
									"label"			=> "Seleccionar",
								],
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" value=\"".$c->warehouseType."\"",
									"classEx"		=> "search_warehouseType"
								],
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" value=\"".$measurementId."\"",
									"classEx"		=> "search_measurement_id"
								],
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" value=\"".$measurementDes."\"",
									"classEx"		=> "search_measurement"
								],
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" value=\"".$c->short_code."\"",
									"classEx"		=> "search_short_code"
								],
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" value=\"".$c->long_code."\"",
									"classEx"		=> "search_long_code"
								],
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" value=\"".$c->place_location."\"",
									"classEx"		=> "search_place_id"
								],
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" value=\"".$amount."\"",
									"classEx"		=> "search_price"
								]
							]
						]
					];
					$modelBody[] = $body;
				}
				$html .= html_entity_decode(preg_replace("/(\r)*(\n)*/", "",view("components.tables.table", [
					"modelBody"			=> $modelBody,
					"modelHead"			=> $modelHead,
					"attributeEx"		=> "id=\"table-warehouse\""
				])));
				$html .= html_entity_decode(preg_replace("/(\r)*(\n)*/", "", "<div class='flex flex-row justify-center paginateSearch'>".$concepts->appends($_GET)->links()."</div>"));
				return Response($html);
			}
			else
			{
				$notfound = html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.labels.not-found")));
				return Response($notfound);
			}
		}
	}

	public function fileName(Request $request)
	{
		$lot = App\Lot::where([
			['fileName',$request->fileName],
			['idElaborate',Auth::user()->id]
		])->count();
		if($lot > 0)
		{
			$lot = App\Lot::where([
				['fileName',$request->fileName],
				['idElaborate',Auth::user()->id]
			])->first();
			return \Response::JSON(array(
					'exist'          => true,
					'lot'            => $lot,
					'articles_count' => $lot->warehouseStationary->count(),
					'finish'         => $lot->status == 2 ? true : false
				));
		}
		else
		{
			return \Response::JSON(array(
				'exist' => false,
			));
		}
	}

	public function create_lot_file(Request $request)
	{
		$name = '/docs/warehouse_xml/AdG'.time().'_'.Auth::user()->id.'.'.$request->file('file')->getClientOriginalExtension();
		\Storage::disk('public')->put($name,\File::get($request->file('file')));
		$new_date = Carbon::createFromFormat('d-m-Y',$request->fecha)->format('Y-m-d');
		$lot = App\Lot::create([
			'total'         => $request->total,
			'articles'      => $request->total,
			'date'          => $new_date,
			'subtotal'      => $request->sub_total,
			'iva'           => $request->total - $request->sub_total,
			'idKind'        => 7,
			'idElaborate'   => Auth::user()->id,
			'idEnterprise'  => $request->idEnterprise,
			'fileName'      => $request->fileName,
			'filePath'      => $request->$name,
			'status'        => 1,
		]);
		$idlot  = $lot->idlot;

		if (isset($request->realPath) && count($request->realPath)>0) 
		{
			for ($i=0; $i < count($request->realPath); $i++) 
			{ 
				$t_file         = new App\DocumentsWarehouse();
				$new_file_name  = Files::rename($request->realPath[$i],$idlot);
				$t_file->path   = $new_file_name;
				$t_file->idlot  = $idlot;
				$t_file->save();
			}
		}
		return \Response::JSON(array(
			'lot'   => $lot
		));
	}

	public function create_warehouse(Request $request)
	{
		$t_warehouse = new App\Warehouse();
		$finish      = $request->finish;
		$cat         = App\CatWarehouseConcept::where('description',$request->concept)->count();
		if($cat == 0)
		{
			$cat = App\CatWarehouseConcept::create([
				'description'   => $request->concept,
				'warehouseType' => $request->category_id,
			]);
		}
		else
		{
			$cat    = App\CatWarehouseConcept::where('description',$request->concept)->first();
		}
		$quantity                    = $request->quanty - $request->damaged;
		$t_warehouse->quantity       = $quantity;
		$t_warehouse->quantityReal   = $request->quanty;
		$t_warehouse->damaged        = $request->damaged;
		$amountUnit                  = $request->amount/$request->quanty;
		$sub_total                   = $request->quanty * $request->uamount;
		$t_warehouse->concept        = $cat->id;
		$t_warehouse->short_code     = $request->short_code;
		$t_warehouse->long_code      = $request->long_code;
		$t_warehouse->measurement    = $request->measurement_id;
		$t_warehouse->commentaries   = $request->commentaries;
		$t_warehouse->amountUnit     = $amountUnit;
		$t_warehouse->iva            = $request->amount - $sub_total;
		$t_warehouse->typeTax        = $request->iva_kind;
		$t_warehouse->subtotal       = $sub_total;
		$t_warehouse->amount         = $request->amount;
		$t_warehouse->idLot          = $request->idlot;
		$t_warehouse->warehouseType  = $request->category_id;
		$t_warehouse->place_location = $request->place_id;
		$t_warehouse->status         = 0;
		$t_warehouse->account        = $request->account_id;
		if($request->category_id == 4)
		{
			$t_warehouse->type      = $request->type;
			$t_warehouse->brand     = $request->brand;
			$t_warehouse->storage   = $request->storage;
			$t_warehouse->processor = $request->processor;
			$t_warehouse->ram       = $request->ram;
			$t_warehouse->sku       = $request->sku;
		}
		$t_warehouse->save();
		if($finish == "true")
		{
			$lot = App\Lot::where('idlot',$request->idlot)->first();
			foreach ($lot->warehouseStationary as $w)
			{
				if($w->quantity > 0)
				{
					$w->status = 1; #disponible
					$w->save();
				}
			}
			$lot->status = 2; #finalizado
			$lot->save();
			$folios	= App\LotIdFolios::where('idLot',$lot->idlot)->get()->pluck('idFolio');
			foreach ($folios as $folio)
			{
				$rq      = App\RequestModel::where('folio',$folio)->first();
				$rq_user = $rq->requestUser;
				try
				{
					$this->createNotificationAlmacen($rq_user->id,$folio,'almacen');
					Mail::to($rq_user->email)->send(new App\Mail\Almacen($folio));
				}
				catch(\Exception $e){}
				$users_delivery = App\User::whereHas('module',function($q)
					{
						$q->where('id', 95);
					})
					->where('active',1)
					->where('notification',1)
					->get();
				foreach ($users_delivery as $user)
				{
					try
					{
						$this->createNotificationAlmacen($user->id,$folio,'almacen');
						Mail::to($user->email)->send(new App\Mail\Almacen($folio));
					}
					catch(\Exception $e){}
				}
			}
		}
		return \Response::JSON(array(
			'status' => true,
		));

	}

	public function search_concept(Request $request)
	{
		$search = $request->search;
		$r      = App\CatWarehouseConcept::where(function($q) use ($search)
		{
			foreach ($search as $value)
			{
				$q->where('description', 'like', "%{$value}%");
			}
		})->first();

		return \Response::JSON(array(
			'concept'   => $r,
		));
	}

	public function edit($id)
	{
		$warehouse  = App\Warehouse::where('idwarehouse',$id)->first();
		if(Auth::user()->module->where('id',$this->module_id)->count()>0 || $warehouse->quantity > 0)
		{
			$data   = App\Module::find($this->module_id);
			return view('almacen.warehouse_edit',
				[
					'id'          => $data['id'],
					'title'       => $data['name'],
					'details'     => $data['details'],
					'warehouse'   => $warehouse,
					'category_id' => [$warehouse->warehouseType],
					'child_id'   => 41,
					'option_id'  => 213,
				]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function computer_edit($id)
	{
		$equipment = App\ComputerEquipment::where('id',$id)->first();
		if(Auth::user()->module->where('id',$this->module_id)->count()>0 || $equipment->quantity > 0)
		{
			$data   = App\Module::find($this->module_id);
			return view('almacen.computer_edit',
				[
					'id'        =>$data['id'],
					'title'     =>$data['name'],
					'details'   =>$data['details'],
					'equipment' => $equipment,
				]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function edit_send(Request $request)
	{
		$new_date                  = $request->date!="" ? Carbon::createFromFormat('d-m-Y',$request->date)->format('Y-m-d') : null;
		$t_warehouse_n             = App\Warehouse::where('idwarehouse',$request->idwarehouse)->first();
		$t_lot                     = $t_warehouse_n->lot;
		$version_lot               = new App\VersionLot();
		$version_lot->idlot        = $t_lot->idlot;
		$version_lot->total        = $t_lot->total;
		$version_lot->subtotal     = $t_lot->subtotal;
		$version_lot->iva          = $t_lot->iva;
		$version_lot->articles     = $t_lot->articles;
		$version_lot->date         = $t_lot->date;
		$version_lot->idEnterprise = $t_lot->idEnterprise;
		$version_lot->idElaborate  = $t_lot->idElaborate;
		$version_lot->save();
		
		$t_lot->total         = $request->total;
		$t_lot->articles      = $request->total;
		$t_lot->subtotal      = $request->sub_total_articles;
		$t_lot->iva           = $request->iva_articles;
		$t_lot->date          = $new_date;
		$t_lot->idEnterprise  = $request->enterprise_id;
		$t_lot->idElaborate   = Auth::user()->id;
		$t_lot->save();

		$idlot              = $t_lot->idlot;
		$version_doc    = $t_lot->versions->count() - 1;

		foreach ($t_lot->documents as $doc)
		{
			$n_doc                                              = new App\VersionDocumentsWarehouse();
			$n_doc->iddocumentsWarehouse    = $doc->iddocumentsWarehouse;
			$n_doc->path                                    = $doc->path;
			$n_doc->idlot                                   = $doc->idlot;
			$n_doc->version                             = $version_doc;
			$n_doc->save();
		}

		if(isset($request->delete_documents) && count($request->delete_documents))
		{
			App\DocumentsWarehouse::whereIn('iddocumentsWarehouse',$request->delete_documents)->update(['status'=>0]);
		}

		if (isset($request->realPath) && count($request->realPath)>0) 
		{
			for ($i=0; $i < count($request->realPath); $i++) 
			{ 
				$t_file                 = new App\DocumentsWarehouse();
				$new_file_name  = Files::rename($request->realPath[$i],$idlot);
				$t_file->path       = $new_file_name;
				$t_file->idlot  = $idlot;
				$t_file->save();
			}
		}

		$t_warehouse                                    = App\Warehouse::where('idwarehouse',$request->idwarehouse)->first();
		$n_warehouse                                    = new App\VersionWarehouse();
		$n_warehouse->concept                   = $t_warehouse->concept;
		$n_warehouse->quantity              = $t_warehouse->quantity;
		$n_warehouse->quantityReal      = $t_warehouse->quantityReal;
		$n_warehouse->short_code            = $t_warehouse->short_code;
		$n_warehouse->long_code             = $t_warehouse->long_code;
		$n_warehouse->measurement           = $t_warehouse->measurement;
		$n_warehouse->commentaries      = $t_warehouse->commentaries;
		$n_warehouse->amountUnit            = $t_warehouse->amountUnit;
		$n_warehouse->iva                           = $t_warehouse->iva;
		$n_warehouse->typeTax                   = $t_warehouse->typeTax;
		$n_warehouse->subtotal              = $t_warehouse->subtotal;
		$n_warehouse->amount                    = $t_warehouse->amount;
		$n_warehouse->idLot                     = $t_warehouse->idLot;
		$n_warehouse->warehouseType     = $t_warehouse->warehouseType;
		$n_warehouse->place_location    = $t_warehouse->place_location;
		$n_warehouse->idWarehouse           = $t_warehouse->idwarehouse;
		$n_warehouse->save();

		$cat    = App\CatWarehouseConcept::where('description',$request->concept_name)->count();
		
		if($cat == 0)
		{
			$cat = App\CatWarehouseConcept::create([
				'description' => $request->concept_name,
				'warehouseType' => $request->category_id,
				]);
		}
		else
		{
			$cat    = App\CatWarehouseConcept::where('description',$request->concept_name)->first();
		}
		#update warehosue
		$sub_total                                      = $request->quantity * $request->uamount;
		$t_warehouse_n->concept             = $cat->id;
		$t_warehouse_n->quantity            = $request->quantity;
		$t_warehouse_n->quantityReal    = $request->quantity;
		$t_warehouse_n->short_code      = $request->short_code;
		$t_warehouse_n->long_code           = $request->long_code;
		$t_warehouse_n->account             = $request->account_id;
		
		if($request->measurement_id)
		{
			$t_warehouse_n->measurement = $request->measurement_id;
		}
		
		$t_warehouse_n->commentaries        = $request->commentaries;
		$t_warehouse_n->amountUnit          = $request->uamount;
		$t_warehouse_n->amount                  = $request->amount;
		$t_warehouse_n->iva                         = $request->amount - $sub_total;
		$t_warehouse_n->typeTax                 = $request->iva_kind;
		$t_warehouse_n->subtotal                = $sub_total;
		$t_warehouse_n->idLot                       = $t_lot->idlot;
		$t_warehouse_n->warehouseType       = $request->category_id;
		$t_warehouse_n->place_location  = $request->place_id;

		if($request->category_id == 4)
		{
			$t_warehouse_n->type            = $request->type;
			$t_warehouse_n->brand       = $request->brand;
			$t_warehouse_n->storage     = $request->storage;
			$t_warehouse_n->processor = $request->processor;
			$t_warehouse_n->ram             = $request->ram;
			$t_warehouse_n->sku             = $request->sku;
		}
		else
		{
			$t_warehouse_n->type            = null;
			$t_warehouse_n->brand       = null;
			$t_warehouse_n->storage     = null;
			$t_warehouse_n->processor = null;
			$t_warehouse_n->ram             = null;
			$t_warehouse_n->sku             = null;
		}
		$t_warehouse_n->save();
		$alert	= "swal('','".Lang::get("messages.record_updated")."', 'success');";
		return redirect('/warehouse')->with('alert',$alert);
	}

	public function computer_edit_send(Request $request)
	{

		$old_date                   = strtotime($request->date);
		$new_date                   = date('Y-m-d',$old_date);
		$computer                   = App\ComputerEquipment::where('id',$request->computer_id)->first();
		$computer_v                 = new App\VersionComputerEquipment();
		$computer_v->idElaborate    = $computer->idElaborate;
		$computer_v->idEnterprise   = $computer->idEnterprise;
		$computer_v->account        = $computer->account;
		$computer_v->place_location = $computer->place_location;
		$computer_v->type           = $computer->type;
		$computer_v->quantity       = $computer->quantity;
		$computer_v->amountUnit     = $computer->amountUnit;
		$computer_v->brand          = $computer->brand;
		$computer_v->storage        = $computer->storage;
		$computer_v->processor      = $computer->processor;
		$computer_v->ram            = $computer->ram;
		$computer_v->sku            = $computer->sku;
		$computer_v->typeTax        = $computer->typeTax;
		$computer_v->subtotal       = $computer->subtotal;
		$computer_v->iva            = $computer->iva;
		$computer_v->amountTotal    = $computer->amountTotal;
		$computer_v->commentaries   = $computer->commentaries;
		$computer_v->idComputer     = $computer->id;
		$computer_v->date           = $computer->date;
		$computer_v->save();
		$sub_total                  = $request->amountunit * $request->quantity;
		$computer->idElaborate      = Auth::user()->id;
		$computer->idEnterprise     = $request->enterprise_id;
		$computer->account          = $request->account_id;
		$computer->place_location   = $request->place_id;
		$computer->type             = $request->type;
		$computer->quantity         = $request->quantity;
		$computer->amountUnit       = $request->amountunit;
		$computer->brand            = $request->brand;
		$computer->storage          = $request->storage;
		$computer->processor        = $request->processor;
		$computer->ram              = $request->ram;
		$computer->sku              = $request->sku;
		$computer->typeTax          = $request->iva_kind;
		$computer->subtotal         = $sub_total;
		$computer->iva              = $request->amount - $sub_total;
		$computer->amountTotal      = $request->amount;
		$computer->commentaries     = $request->commentaries;
		$computer->date             = $new_date;
		$computer->save();
		$alert	= "swal('','".Lang::get("messages.record_updated")."', 'success');";
		return redirect('/warehouse')->with('alert',$alert);
	}

	public function getAccount(Request $request)
	{
		if($request->ajax())
		{
			$acc = [];
			switch ($request->warehouseType)
			{
				case 1:#papeleria
					$acc    = App\Account::where('idEnterprise',$request->enterpriseid)
						->where('account','1108002')
						->where('selectable',1)
						->orderBy('account','asc')
						->get();
					break;
				
				case 2:#herramienta
					$accounts = App\Account::where('idEnterprise',$request->enterpriseid)
						->where(function($query)
							{
								$query->where('account','like','1202%')->orWhere('account','like','1204%');
							})
						->where('selectable',1)
						->orderBy('account','asc')
						->get();
					foreach ($accounts as $account)
					{
						if(
								App\Account::
									where('account',$account->father)
									->where('idEnterprise',$request->enterpriseid)
									->where(function($query)
										{
											$query->where('account','like','1202%')->orWhere('account','like','1204%');
										})
									->where('selectable',0)
									->where(function($query)
										{
											$query->where('description','Mobiliario y equipo')->orWhere('description','Maquinaria y equipo');
										})
									->exists()
						)
						{
							array_push($acc,$account);
						}
					}
					break;
				case 3:#Insumo
					$acc    = App\Account::where('idEnterprise',$request->enterpriseid)
						->where('account','1108001')
						->where('selectable',1)
						->orderBy('account','asc')
						->get();
					break;
				case 4:
				case 'computo':
					$acc    = App\Account::where('idEnterprise',$request->enterpriseid)
						->whereIn('account',[1202001,1202004,1202005,1202006,1202007,1202008,1202009])
						->where('selectable',1)
						->orderBy('account','asc')
						->get();
				break;
			}
			return Response($acc);
		}
	}

	public function update(Request $request, $id)
	{
		//
	}

	public function destroy($id)
	{
		//
	}

	public function search_compras(Request $request)
	{
		if ($request->ajax())
		{
			$bugdets = App\Budget::where('status',1)->get()->pluck('request_id');
			$folios = App\RequestModel::where('kind',1)->whereNotIn('folio',$bugdets)->get()->pluck('folio');
			$data = App\RequestModel::where('kind',1)
				->whereHas('budget',function($q)
				{
					$q->where('status',1);
				})
				->whereIn('status',[5,10,11,12])
				->where('statusWarehouse',0)
				->where('goToWarehouse',1)
				->whereIn('idEnterpriseR',Auth::user()->inChargeEnt($request->option_id)->pluck('enterprise_id'))
				->where(function($query) use ($request)
				{
					if($request->search != '')
					{
						$query->where('folio','like','%'.$request->search.'%')
							->orWhereHas('purchases',function($q) use ($request)
							{
								$q->where('title','like','%'.$request->search.'%');
							})
							->orWhereHas('elaborateUser',function($q) use ($request)
							{
								$q->where('name','like','%'.$request->search.'%');
							})
							->orWhereHas('requestEnterprise',function($q) use ($request)
							{
								$q->where('name','like','%'.$request->search.'%');
							});
					}
				})
				->with('purchases','elaborateUser','requestEnterprise')
				->has('purchases.documents')
				->orderby('folio','desc')
				->paginate(10);
			
			foreach ($data as $d)
			{
				$time       = new \DateTime($d->fDate);
				$d->fDate   = $time->format('d-m-Y H:i');
			}
			return \Response::JSON(array(
					'table'         => $data, 
					'pagination'    => (string) $data->links()
				));
		}
	}

	public function search_compras_request(Request $request)
	{

		if ($request->ajax())
		{
			$data = App\RequestModel::
				where('folio',$request->folio)
				->with('purchases.detailPurchase','purchases.documents','accounts','enterprise')
				->first();
			$date			= strtotime($data->fDate);
			$data->fDate	= $data->fDate;
			$account_id         = $data->accountR;
			$account_name       = $data->accountsReview->account.' '.$data->accountsReview->description;
			$detailPurchases    = $data->purchases()->first()->detailPurchase()->where('statusWarehouse',0)->get();


			$data           = $data->toArray();
			
			$data["purchases"][0]["detail_purchase"] = $detailPurchases;
			$data["account_id"]     = $account_id;
			$data["account_name"]   = $account_name;

			return Response($data);
		}
	}

	public function createNotificationAlmacen($user_id,$folio,$type)
	{
		App\Notifications::create([
			'title'   => "Almacén",
			'body'    => $folio,
			'end'     => Carbon::now()->addDays(7),
			'route'   => 'administration/stationery',
			'user_id' => $user_id,
		]);
	}

	public function reportRequisition(Request $request)
	{
		if(Auth::user()->module->where('id',237)->count()>0)
		{
			$data   = App\Module::find($this->module_id);

			$title_request      = $request->title_request;
			$mindate_request    = $request->mindate_request;
			$maxdate_request    = $request->maxdate_request;
			$mindate_obra       = $request->mindate_obra;
			$maxdate_obra       = $request->maxdate_obra;
			$status             = $request->status;
			$folio              = $request->folio;
			$user_request       = $request->user_request;

			$requests = App\RequestModel::leftJoin('requisitions', 'request_models.folio', 'requisitions.idFolio')
				->where('request_models.kind', 19)
				->where(function ($query) 
				{
					if (Auth::user()->id != 43) 
					{
						$query->where('request_models.idElaborate', Auth::user()->id)->orWhere('request_models.idRequest', Auth::user()->id);
					}
				})
				->where(function ($query) use ($title_request, $user_request, $mindate_request, $maxdate_request, $mindate_obra, $maxdate_obra, $folio, $status) 
				{
					if ($user_request != "") 
					{
						$query->whereIn('request_models.idRequest', $user_request);
					}
					if ($title_request != "") 
					{
						$query->where('requisitions.title', 'LIKE', '%' . $title_request . '%');
					}
					if ($mindate_request != "") 
					{
						$query->whereBetween('requisitions.date_request', ['' . $mindate_request . ' ' . date('00:00:00') . '', '' . $maxdate_request . ' ' . date('23:59:59') . '']);
					}
					if ($mindate_obra != "") 
					{
						$query->whereBetween('requisitions.date_obra', ['' . $mindate_obra . ' ' . date('00:00:00') . '', '' . $maxdate_obra . ' ' . date('23:59:59') . '']);
					}
					if ($folio != "") 
					{
						$query->where('request_models.folio', $folio);
					}
					if ($status != "") 
					{
						$query->whereIn('request_models.status', $status);
					}
				})
				->orderBy('request_models.fDate', 'DESC')
				->orderBy('request_models.folio', 'DESC')
				->paginate(10);
			return view('almacen.report_requisition',
				[
					'id'				=> $data['id'],
					'title'				=> $data['name'],
					'details'			=> $data['details'],
					'child_id'			=> 41,
					'option_id'			=> 237,
					'requests'			=> $requests,
					'mindate_obra'		=> $mindate_obra,
					'maxdate_obra'		=> $maxdate_obra,
					'mindate_request'	=> $mindate_request,
					'maxdate_request'	=> $maxdate_request,
					'folio'				=> $folio,
					'status'			=> $status,
					'title_request'		=> $title_request,
					'user_request'		=> $user_request,
				]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function requisitionExcel(Request $request)
	{
		if (Auth::user()->module->where('id', 237)->count() > 0) 
		{

			$title_request      = $request->title_request;
			$mindate_request    = $request->mindate_request;
			$maxdate_request    = $request->maxdate_request;
			$mindate_obra       = $request->mindate_obra;
			$maxdate_obra       = $request->maxdate_obra;
			$status             = $request->status;
			$folio              = $request->folio;
			$user_request       = $request->user_request;

			$requests = App\RequestModel::leftJoin('requisitions', 'request_models.folio', 'requisitions.idFolio')
				->where('request_models.kind', 19)
				->where(function ($query) 
				{
					if (Auth::user()->id != 43) 
					{
						$query->where('request_models.idElaborate', Auth::user()->id)->orWhere('request_models.idRequest', Auth::user()->id);
					}
				})
				->where(function ($query) use ($title_request, $user_request, $mindate_request, $maxdate_request, $mindate_obra, $maxdate_obra, $folio, $status) 
				{
					if ($user_request != "") 
					{
						$query->whereIn('request_models.idRequest', $user_request);
					}
					if ($title_request != "") 
					{
						$query->where('requisitions.title', 'LIKE', '%' . $title_request . '%');
					}
					if ($mindate_request != "") 
					{
						$query->whereBetween('requisitions.date_request', ['' . $mindate_request . ' ' . date('00:00:00') . '', '' . $maxdate_request . ' ' . date('23:59:59') . '']);
					}
					if ($mindate_obra != "") 
					{
						$query->whereBetween('requisitions.date_obra', ['' . $mindate_obra . ' ' . date('00:00:00') . '', '' . $maxdate_obra . ' ' . date('23:59:59') . '']);
					}
					if ($folio != "") 
					{
						$query->where('request_models.folio', $folio);
					}
					if ($status != "") 
					{
						$query->whereIn('request_models.status', $status);
					}
				})
				->orderBy('request_models.fDate', 'DESC')
				->orderBy('request_models.folio', 'DESC')
				->get();


			Excel::create('Reporte-Simple-Requisición', function ($excel) use ($requests) 
			{
				$excel->sheet('Datos', function ($sheet) use ($requests) 
				{
					$row = ['Reporte'];
					$sheet->appendRow($row);
					$row = [
						'Código','Medida','Unidad','Nombre','Categoría', 'Existencia', 'Almacén',
					];
					$sheet->appendRow($row);

					$sheet->mergeCells('A1:G1');
					$sheet->cell('A1:G1', function ($cells) 
					{
						$cells->setFontWeight('bold');
						$cells->setAlignment('center');
						$cells->setFont(array('family' => 'Calibri', 'size' => 16, 'bold' => true));
					});

					
					$sheet->cell('A1:G1', function ($cells) 
					{
						$cells->setBackground('#000000');
						$cells->setFontColor('#ffffff');
					});

					$sheet->cell('A2:G2', function ($cells) 
					{
						$cells->setFontWeight('bold');
						$cells->setAlignment('center');
						$cells->setFont(array('family' => 'Calibri', 'size' => 14, 'bold' => true));
						$cells->setBackground('#104f64');
						$cells->setFontColor('#ffffff');
					});

					
					$sheet->setAutoSize(array(
						'A',
						'B',
						'C',
						'E',
						'F',
						'G',
					));
					$sheet->setWidth(array(
						'D'     => 50,
						'E'     => 25,
						'F'     => 25,
						'G'     => 25,
					));
					$sheet->setColumnFormat(array(
					'Z'     => 'A',
				));

					foreach ($requests as $request) 
					{
						$tempCount = 0;

						if($request->status == 17)
						{

							foreach ($request->requisition->purchases as $purchase) 
							{
								foreach ($purchase->detailPurchase as $detail) 
								{
									$row = [];
									$row[] = $detail->code.' ';
									$row[] = $detail->measurement;
									$row[] = $detail->unit;
									$row[] = $detail->description;
									$row[] = $detail->categoria;
									$row[] = $detail->quantity;
									$row[] = $request->requisition->requisition_type == 1 ? $detail->estatusAlmacen : 'No Aplica';
									
									$sheet->appendRow($row);
									$tempCount++;
								}
							}
							foreach ($request->requisition->refunds as $refund) 
							{
								foreach ($refund->refundDetail as $detail) 
								{
									$row = [];
									$row[] = $detail->code.' ';
									$row[] = $detail->measurement;
									$row[] = $detail->unit;
									$row[] = $detail->concept;
									$row[] = $detail->categoria; //categoria
									$row[] = $detail->quantity; //cantidad;
									$row[] = 'N/A';
									$sheet->appendRow($row);
									$tempCount++;
								}
							}

						}
						else
						{
							foreach ($request->requisition->details as $dt) 
							{
								$row = [];
								$row[] = $dt->code.' ';
								$row[] = $dt->measurement;
								$row[] = $dt->unit;
								$row[] = $dt->description;
								$row[] = $dt->categoria;
								$row[] = $dt->quantity;
								$row[] = 'Pendiente';
								$sheet->appendRow($row);
								$tempCount++;
							}
						}

					}
					$sheet->getStyle("A3:A".$sheet->getHighestRow())->getAlignment()->setWrapText(true);
				});
			})->export('xlsx');
		} 
		else 
		{
			return redirect('/error');
		}
	}

	public function requisitionPdf(Request $request)
	{
		$title_request      = $request->title_request;
		$mindate_request    = $request->mindate_request;
		$maxdate_request    = $request->maxdate_request;
		$mindate_obra       = $request->mindate_obra;
		$maxdate_obra       = $request->maxdate_obra;
		$status             = $request->status;
		$folio              = $request->folio;
		$user_request       = $request->user_request;

		$requests = App\RequestModel::leftJoin('requisitions', 'request_models.folio', 'requisitions.idFolio')
			->where('request_models.kind', 19)
			->where(function ($query) 
			{
				if (Auth::user()->id != 43) 
				{
					$query->where('request_models.idElaborate', Auth::user()->id)->orWhere('request_models.idRequest', Auth::user()->id);
				}
			})
			->where(function ($query) use ($title_request, $user_request, $mindate_request, $maxdate_request, $mindate_obra, $maxdate_obra, $folio, $status) 
			{
				if ($user_request != "") 
				{
					$query->whereIn('request_models.idRequest', $user_request);
				}
				if ($title_request != "") 
				{
					$query->where('requisitions.title', 'LIKE', '%' . $title_request . '%');
				}
				if ($mindate_request != "") 
				{
					$query->whereBetween('requisitions.date_request', ['' . $mindate_request . ' ' . date('00:00:00') . '', '' . $maxdate_request . ' ' . date('23:59:59') . '']);
				}
				if ($mindate_obra != "") 
				{
					$query->whereBetween('requisitions.date_obra', ['' . $mindate_obra . ' ' . date('00:00:00') . '', '' . $maxdate_obra . ' ' . date('23:59:59') . '']);
				}
				if ($folio != "") 
				{
					$query->where('request_models.folio', $folio);
				}
				if ($status != "") 
				{
					$query->whereIn('request_models.status', $status);
				}
			})
			->orderBy('request_models.fDate', 'DESC')
			->orderBy('request_models.folio', 'DESC')
			->get();

		if ($requests != "")
		{
			$pdf = PDF::loadView('almacen.report_requisition_pdf',['requests'=>$requests]);
			return $pdf->download('Reporte-Almacén-Requisición.pdf');
		}
		else
		{
			return redirect('/error');
		}
	}

	public function inputsOutputs(Request $request)
	{
		if(Auth::user()->module->where('id',238)->count()>0)
		{
			$data       = App\Module::find($this->module_id);
			$enterprise = $request->idEnterprise;
			$name       = $request->name;
			$concept    = $request->concept;
			$category   = $request->category;
			$place_id   = $request->place_id;
			$account_id = $request->account_id;
			$mindate   	= $request->mindate != "" ? Carbon::createFromFormat('d-m-Y',$request->mindate)->format('Y-m-d') : null;
			$maxdate   	= $request->maxdate != "" ? Carbon::createFromFormat('d-m-Y',$request->maxdate)->format('Y-m-d') : null;
			$warehouse	= App\Warehouse::where(function($query) use ($enterprise, $mindate, $maxdate, $place_id, $account_id,$category)
			{
				if ($enterprise != "")
				{
					if ($enterprise != "todas")
					{
						$query->whereHas('lot',function($q) use($enterprise){
							$q->where('lots.idEnterprise',$enterprise);
						});
					}
				}
				if ($account_id != "")
				{
					if ($account_id != "todas")
					{
						$query->whereHas('lot',function($q) use($account_id){
							$q->where('lots.account',$account_id);
						});
					}
				}
				if ($mindate != "" && $maxdate != "")
				{
					$query->whereHas('lot',function($q) use($mindate,$maxdate){
						$q->whereBetween('lots.date',[''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').'']);
					});
				}
	
				if ($place_id != null )
				{
					$query->where('place_location',$place_id);
				}
	
				$query->whereHas('lot',function($q){
					$q->where('lots.status',2);
				});
				if($category)
				{
					$query->where('warehouseType',$category);
				}
			})
			->whereHas('cat_c', function($query) use($concept,$category)
			{
				if($concept)
				{
					$query->where('description','LIKE','%'.$concept.'%');
				}
			})
			->orderBy('idwarehouse','DESC')
			->paginate(10);
			return view('almacen.inputs_outputs',
				[
					'id'           => 110,
					'title'        => $data['name'],
					'details'      => $data['details'],
					'child_id'     => 41,
					'option_id'    => 238,
					'data'         => $warehouse,
					'mindate'      => $request->mindate,
					'maxdate'      => $request->maxdate,
					'idEnterprise' => $enterprise,
					'category'     => $category,
					'place_id'     => $place_id,
					'account_id'   => $account_id,
					'concept'      => $concept,
				]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function inputsOutputsExcel(Request $request)
	{
		if (Auth::user()->module->where('id', 230)->count() > 0)
		{
			$enterprise = $request->idEnterprise;
			$name       = $request->name;
			$concept    = $request->concept;
			$category   = $request->category;
			$place_id   = $request->place_id;
			$account_id = $request->account_id;
			$mindate   	= $request->mindate != "" ? Carbon::createFromFormat('d-m-Y',$request->mindate)->format('Y-m-d') : null;
			$maxdate   	= $request->maxdate != "" ? Carbon::createFromFormat('d-m-Y',$request->maxdate)->format('Y-m-d') : null;		
			$requests = DB::table('warehouses')->selectRaw(
				'
					lots.idlot,
					cat_warehouse_concepts.description,
					cat_warehouse_types.description as warehousestype,
					warehouses.short_code,
					warehouses.quantityReal,					
					IFNULL(warehouses.damaged,0) as damaged,
					warehouses.quantity,
					((warehouses.quantityReal - IFNULL(warehouses.damaged, 0)) - warehouses.quantity) as total
				')
				->join('lots', 'lots.idLot', 'warehouses.idLot')
				->join('cat_warehouse_concepts', 'cat_warehouse_concepts.id', 'warehouses.concept')
				->join('cat_warehouse_types', 'cat_warehouse_types.id', 'cat_warehouse_concepts.warehouseType')
				->where(function($query) use ($enterprise, $mindate, $maxdate, $place_id, $account_id,$category,$concept)
				{
					if ($enterprise != "")
					{
						if ($enterprise != "todas")
						{
							$query->where('lots.idEnterprise',$enterprise);
						}
					}
					if ($account_id != "")
					{
						if ($account_id != "todas")
						{
							$query->where('lots.account',$account_id);
						}
					}
					if ($mindate != "" && $maxdate != "")
					{
						$query->whereBetween('lots.date',[''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').'']);
					}
					if ($place_id != null )
					{
						$query->where('place_location',$place_id);
					}
					if($category)
					{
						$query->where('warehouses.warehouseType',$category);
					}
					if($concept)
					{
						$query->where('cat_warehouse_concepts.description','LIKE','%'.preg_replace("/\s+/", "%", $concept).'%');
					}
					$query->where('lots.status',2);
				})
				->orderBy('idwarehouse','DESC')
				->get();
			if(count($requests)==0 || $requests==null)
			{
				return redirect()->back()->with('alert',"swal('', '".Lang::get("messages.result_not_found")."', 'error');");
			}

			$defaultStyle   = (new StyleBuilder())->setFontName('Calibri')->setFontSize(12)->build();
			$currencyFormat = (new StyleBuilder())->setFormat('_("$"* #,##0.00_),_("$"* \(#,##0.00\),_("$"* "-"??_),_(@_)')->build();
			$rowDark        = (new StyleBuilder())->setBackgroundColor('F0F0F0')->setCellAlignment(CellAlignment::LEFT)->build();
			$dateFormat   	= (new StyleBuilder())->setFormat('d-m-yy');
			$mhStyleCol1    = (new StyleBuilder())->setBackgroundColor('000000')->setFontColor(Color::WHITE)->build();
			$mhStyleCol2    = (new StyleBuilder())->setBackgroundColor('104f64')->setFontColor(Color::WHITE)->build();
			$alignment		= (new StyleBuilder())->setCellAlignment(CellAlignment::LEFT)->build();
			$writer         = WriterEntityFactory::createXLSXWriter();
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('Reporte-Entradas-Salidas.xlsx');
			$writer->getCurrentSheet()->setName('DATOS');

			$headers		= ['Reporte-Entradas-Salidas','','','','','','',''];
			$tempHeaders    = [];
			foreach($headers as $k => $mh)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($mh,$mhStyleCol1);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);

			$subHeader    = ['Lote', 'Artículo', 'Categoría', 'Clave', 'Recibidos', 'Dañados', 'Existencia', 'Entregados'];
			$tempSubHeader = [];
			foreach($subHeader as $k => $sh)
			{
				$tempSubHeader[] = WriterEntityFactory::createCell($sh,$mhStyleCol2);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
			$writer->addRow($rowFromValues);
			//asd

			$tempId     = '';
			$kindRow       = true;
			foreach($requests as $request)
			{
				if($tempId != $request->idlot)
				{
					$tempId = $request->idlot;
					$kindRow = !$kindRow;
				}
				else
				{
					$request->idlot				= null;
					$request->warehousestype	= '';
				}
				$tmpArr = [];
				foreach($request as $k => $r)
				{
					$tmpArr[] = WriterEntityFactory::createCell($r);
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

	public function requisitionModal(Request $request)
	{
		$request = App\RequestModel::find($request->id);
		return view('almacen.modal_requisition',['request' => $request]);
	}

	public function inputsOutputsModal(Request $request)
	{
		$warehouse = App\Warehouse::find($request->id);
		return view('almacen.modal_inputs_outputs',['warehouse' => $warehouse]);
	}

	public function remove(Request $request)
	{
		$data	    = App\Module::find($this->module_id);
		$enterprise = $request->enterpriseid;
		$place_id   = $request->place_id;
		$mindate    = $request->mindate != "" ? Carbon::createFromFormat('d-m-Y',$request->mindate) : null;
		$maxdate    = $request->maxdate != "" ? Carbon::createFromFormat('d-m-Y',$request->maxdate) : null;
		$account_id = $request->account_id;
		$concept    = $request->concept;
		$category   = $request->cat;
			
		$warehouse = App\Warehouse::where(function($query) use ($request)
		{
			if ($request->idEnterprise != "")
			{
				if ($request->idEnterprise != "todas")
				{
					$enterprise = $request->idEnterprise;
					$query->whereHas('lot',function($q) use($enterprise){
						$q->where('lots.idEnterprise',$enterprise);
					});
				}
			}
			if ($request->account_id != "")
			{
				if ($request->account_id != "todas")
				{
					$account_id = $request->account_id;
					$query->whereHas('lot',function($q) use($account_id){
						$q->where('lots.account',$account_id);
					});
				}
			}
			if ($request->mindate != "" && $request->maxdate != "")
			{
				$mindate = Carbon::createFromFormat('d-m-Y',$request->mindate);
				$maxdate = Carbon::createFromFormat('d-m-Y',$request->maxdate);
				$query->whereHas('lot',function($q) use($mindate,$maxdate){
					$q->whereBetween('lots.date',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 23:59:59')]);
				});
			}

			if ($request->place_id != null )
			{
				$query->where('place_location',$request->place_id);
			}

			if ($request->mindate != "" && $request->maxdate != "")
			{
				$min = $request->mindate;
				$max = $request->maxdate;
				$query->whereHas('lot',function($q) use($min,$max){
					$q->where('lots.status',2);
				});
			}
			if($request->cat != "")
			{
				$query->where('warehouseType',$request->cat);
			}
		})
		->whereHas('cat_c', function($query) use($concept,$category)
		{
			if($concept)
			{
				$query->where('description','LIKE','%'.$concept.'%');
			}
		})
		->groupBy('concept','place_location')
		->selectRaw('
			idwarehouse,
			warehouseType,
			place_location,
			quantity,
			warehouses.quantity, sum(warehouses.quantity) as quantity,
			concept')
		->with('cat_c','location','wareHouse','lot')
		->where('status',1)
		->where('quantity','>',0)
		->orderBy('idwarehouse','DESC')
		->paginate(10);

		return view('almacen.baja.index',
		[
			"warehouses"   => $warehouse,
			"title"        => "Baja almacén",
			"details"      => "En este módulo podrá ejecutar la entrega de solicitudes de almacén",
			'id'           => $data['id'],
			'child_id'     => 41,
			'option_id'    => 304,
			'mindate'		=> $request->mindate,
			'maxdate'		=> $request->maxdate,
			'enterpriseid' => $enterprise,
			'category'     => $category,
			'place_id'     => $place_id,
			'account_id'   => $account_id,
			'concept'      => $concept,
		]);
	}

	public function removeDetail(Request $request)
	{
		$modal		  = "";
		$details      = "";
		$concept      = $request->warehouseConcept;
		$place_id     = $request->place_id;
		$account_id   = $request->account_id;
		$enterpriseid = $request->idEnterprise;
		$max 		  = null;
		$min 		  = null;

		if ($request->mindate != null)
		{
			$min	=	Carbon::createFromFormat('d-m-Y',$request->mindate);
		}
		if ($request->maxdate != null)
		{
			$max	=	Carbon::createFromFormat('d-m-Y',$request->maxdate);
		}
		$warehouses = App\Warehouse::where('concept',$concept)
			->where(function($query) use ($enterpriseid,$min,$max,$place_id,$account_id)
			{
				if ($enterpriseid != "")
				{
					if ($enterpriseid != "todas")
					{
						$query->whereHas('lot',function($q) use($enterpriseid){
							$q->where('lots.idEnterprise',$enterpriseid);
						});
					}
				}
				if ($account_id != "")
				{
					if ($account_id != "todas")
					{
						$query->whereHas('lot',function($q) use($account_id){
							$q->where('lots.account',$account_id);
						});
					}
				}
				if ($min != "" && $max != "")
				{
					$query->whereHas('lot',function($q) use($min,$max){
						$q->whereBetween('lots.date',[$min->format('Y-m-d 00:00:00'),$max->format('Y-m-d 23:59:59')]);
					});
				}
				$query->whereHas('lot',function($q)
				{
					$q->where('lots.status',2);
				});
				if ($place_id != "")
				{
					$query->where('place_location',$place_id);
				}
			})
			->get();

		$index = 0;

		foreach ($warehouses as $warehouse)
		{
			if ($warehouse->status == 1)
			{
				$entregados = $warehouse->quantityReal - $warehouse->damaged;
				$entregados = $entregados - $warehouse->quantity;
				$warehousesRemoved = App\WarehouseRemove::where('warehouse_id', $warehouse->idwarehouse)->sum('quantity');
				$modal		=	"<div class='row'>";
				$modelHead	=	[];
				$body		=	[];
				$modelBody	=	[];
				$modelHead	=
				[
					[
						["value"	=>	"Producto/Material"],
						["value"	=>	"Categoría"],
						["value"	=>	"Clave"],
						["value"	=>	"Ubicación"],
						["value"	=>	"Recibidos"],
						["value"	=>	"Dañados"],
						["value"	=>	"Existencia"],
						["value"	=>	"Entregados"],
						["value"	=>	"Dados de baja"],
						["value"	=>	"Cantidad de artículos a quitar"],
					]
				];
				$body	=
				[
					[
						"content"	=>	["label"	=>	$warehouse->cat_c->description !="" ? $warehouse->cat_c->description : "---"]
					],
					[
						"content"	=>	["label"	=>	$warehouse->wareHouse->description !="" ? $warehouse->wareHouse->description : "---"]
					],
					[
						"content"	=>	["label"	=>	$warehouse->short_code !="" ? $warehouse->short_code : "---"]
					],
					[
						"content"	=>	["label"	=>	$warehouse->place_location !="" ? $warehouse->place_location : "---"]
					],
					[
						"content"	=>	["label"	=>	$warehouse->quantityReal !="" ? $warehouse->quantityReal : "---"]
					],
					[
						"content"	=>	["label"	=>	$warehouse->damaged !="" ? $warehouse->damaged : "---"]
					],
					[
						"content"	=>
						[
							["kind"	=>	"components.labels.label",		"label"	=>	$warehouse->quantity !="" ? $warehouse->quantity : "---"],
							["kind"	=>	"components.inputs.input-text",	"attributeEx"	=>	"type=\"hidden\" value=\"".$warehouse->quantity."\"",	"classEx"	=>	"quantity"]
						]
					],
					[
						"content"	=>	["label"	=>	$entregados !="" ? $entregados : "---"]
					],
					[
						"content"	=>	["label"	=>	$warehousesRemoved !="" ? $warehousesRemoved : "---"]
					],
					[
						"content"	=>
						[
							["kind"	=>	"components.inputs.input-text",	"attributeEx"	=>	"type=\"text\" name=\"quantityRemove[".$index."]\" value=\"0\" data-validation=\"required\"", "classEx"	=>	"quantityRemove"],
							["kind"	=>	"components.inputs.input-text",	"attributeEx"	=>	"type=\"hidden\"name=\"idWarehouse\" value=\"".$warehouse->idwarehouse."\""],
							["kind"	=>	"components.inputs.input-text",	"attributeEx"	=>	"type=\"hidden\"name=\"idLot[".$index."]\" value=\"".$warehouse->lot->idlot."\""],
							["kind"	=>	"components.inputs.input-text",	"attributeEx"	=>	"type=\"hidden\"name=\"idEnterprise\" value=\"".$warehouse->lot->enterprise->id."\""],
						]
					],
				];
				$modelBody[]	=	$body;
				$modal .=	 html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view('components.tables.table', ["modelHead" => $modelHead, "modelBody" => $modelBody, "classExBody" => "tr-remove"])));
				$index++;
			}
		}
		$modal .= "<div class='text-center mt-8'>";
		$modal .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view('components.labels.label', ["label" => "Causas de la baja"])));
		$modal .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view('components.inputs.text-area', ["attributeEx" => "name=\"reasons\" data-validation=\"required\" placeholder=\"Ingrese las causas\" title=\"Entregar\"", "classEx" => "input-text", "slot" => ""])));
		$modal .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view('components.buttons.button', ["attributeEx" => "type=\"submit\" title=\"Entregar\"", "classEx" => "mt-8 set-warehouse", "label" => "Dar de baja productos", "variant" => "red"])));
		$modal .=	 "</div></div>";
		return Response($modal);
	}

	public function delete(Request $request)
	{
		$removedQunatity = 0;
		foreach($request->idLot as $arrayKey => $idLot) //Array key and idLot aren´t the same data
		{
			if($request->quantityRemove[$arrayKey] > 0)
			{
				$warehouseDeleted = new warehouseRemove();
				$warehouseDeleted->warehouse_id = $request->idWarehouse;
				$warehouseDeleted->lot_id = $idLot;
				$warehouseDeleted->user_id = Auth::user()->id;
				$warehouseDeleted->quantity = $request->quantityRemove[$arrayKey];
				$warehouseDeleted->reasons = $request->reasons;
				$warehouseDeleted->save();

				$removedQunatity = $removedQunatity + $request->quantityRemove[$arrayKey];
			}
		}
		$warehouse           = App\Warehouse::find($request->idWarehouse);
		$warehouse->quantity = $warehouse->quantity - $removedQunatity;
		$warehouse->save();
		$alert	= "swal('','".Lang::get("messages.record_deleted")."', 'success');";
		return back()->with('alert',$alert);
	}

	public function deliveryReportIndex(Request $request)
	{
		$data	    = App\Module::find($this->module_id);
		$mindate   	= $request->mindate != "" ? Carbon::createFromFormat('d-m-Y',$request->mindate)->format('Y-m-d') : null;
		$maxdate   	= $request->maxdate != "" ? Carbon::createFromFormat('d-m-Y',$request->maxdate)->format('Y-m-d') : null;
		$deliveries = App\DetailStationery::whereHas('productDelivery', function($query) use ($request)
		{
			if ($request->place_id != "")
			{
				$query->where('place_location',$request->place_id);
			}
			if($request->cat != "")
			{
				$query->where('warehouseType',$request->cat);
			}
			if($request->concept != "")
			{
				$query->whereHas('cat_c', function($query) use($request)
				{
					$query->where('description','LIKE','%'.$request->concept.'%');
				});
			}
			if ($request->idEnterprise != "")
			{
				if ($request->idEnterprise != "todas")
				{
					$query->whereHas('lot',function($q) use($request){
						$q->where('lots.idEnterprise',$request->idEnterprise);
					});
				}
			}
			if ($request->account_id != "")
			{
				if ($request->account_id != "todas")
				{
					$query->whereHas('lot',function($q) use($request){
						$q->where('lots.account', $request->account_id);
					});
				}
			}
		})
		->whereHas('stat', function($query) use ($mindate, $maxdate)
		{
			if ($mindate != "" && $maxdate != "")
			{
				$query->whereHas('requestModel', function($q) use ($mindate, $maxdate)
				{
					$q->whereBetween('request_models.deliveryDate', [$mindate.' 00:00:00', $maxdate.' 23:59:59']);
				});
			}
		})
		->groupBy('idStat')
		->orderBy('idStatDetail', 'DESC')
		->paginate(10);
		return view('almacen.reportDelivery', 
		[
			"deliveries"   => $deliveries, 
			"title"        => "Reporte de entrega", 
			"details"      => "En este módulo podrá descargar los reportes de las entregas",
			'id'           => $data['id'],
			'child_id'     => 41,
			'option_id'    => 305,
			'mindate'      => $request->mindate != null ? $request->mindate : "",
			'maxdate'      => $request->maxdate != null ? $request->maxdate : "",
			'enterpriseid' => $request->idEnterprise,
			'category'     => $request->cat,
			'place_id'     => $request->place_id,
			'account_id'   => $request->account_id,
			'concept'      => $request->concept,
		]);
	}
	public function downloadDeliveryReportPDF($id)
	{
		$Stationery	= App\Stationery::find($id);
		if ($Stationery != null)
		{
			$pdf = PDF::loadView('almacen.downloadPDF_deliveryReport',['Stationery'=>$Stationery]);
			return $pdf->download('Reporte_Entregas_almacén'.$Stationery->requestModel->folio.'.pdf');
		}
		else
		{
			return redirect('/error');
		}
	}

	// public function exportDeliveryReport(Request $request)
	// {
	// 	$data	    = App\Module::find($this->module_id);
	// 	$enterprise = $request->idEnterprise;
	// 	$place_id   = $request->place_id;
	// 	$mindate   	= $request->mindate != "" ? Carbon::createFromFormat('d-m-Y',$request->mindate)->format('Y-m-d') : null;
	// 	$maxdate   	= $request->maxdate != "" ? Carbon::createFromFormat('d-m-Y',$request->maxdate)->format('Y-m-d') : null;
	// 	$account_id = $request->account_id;
	// 	$concept    = $request->concept;
	// 	$category   = $request->cat;
	// 	$deliveries = DB::table('detail_stationeries')
	// 		->leftJoin('warehouses', 'detail_stationeries.idwarehouse', '=', 'warehouses.idwarehouse')
	// 		->leftJoin('cat_warehouse_concepts', 'warehouses.concept', 'cat_warehouse_concepts.id')
	// 		->leftJoin('cat_warehouse_types', 'warehouses.warehouseType', 'cat_warehouse_types.id')
	// 		->leftJoin('lots', 'warehouses.idLot', 'lots.idlot')
	// 		->leftJoin('stationeries', 'detail_stationeries.idStat', 'stationeries.idStationery')
	// 		->leftJoin('request_models', 'stationeries.idFolio', 'request_models.folio')
	// 		->leftJoin('users as user_request', 'request_models.idRequest', 'user_request.id')
	// 		->leftJoin('users as user_elaborate', 'request_models.idElaborate', 'user_elaborate.id')
	// 		->leftJoin('users as user_check', 'request_models.idCheck', 'user_check.id')
	// 		->leftJoin('users as user_authorize', 'request_models.idAuthorize', 'user_authorize.id')
	// 		->leftJoin('places', 'warehouses.place_location', 'places.id')
	// 		->leftJoin('enterprises as enterprise_elaborate', 'request_models.idEnterprise', 'enterprise_elaborate.id')
	// 		->leftJoin('enterprises as enterprise_review', 'request_models.idEnterpriseR', 'enterprise_review.id')
	// 		->leftJoin('areas as area_elaborate', 'request_models.idArea', 'area_elaborate.id')
	// 		->leftJoin('areas as area_review', 'request_models.idAreaR', 'area_review.id')
	// 		->leftJoin('departments as department_elaborate', 'request_models.idDepartment', 'department_elaborate.id')
	// 		->leftJoin('departments as department_review', 'request_models.idDepartamentR', 'department_review.id')
	// 		->leftJoin('projects as project_elaborate', 'request_models.idProject', 'project_elaborate.idproyect')
	// 		->leftJoin('projects as project_review', 'request_models.idProjectR', 'project_review.idproyect')
	// 		->where(function($q) use($place_id, $category, $enterprise, $account_id, $maxdate, $mindate, $concept)
	// 		{
	// 			if($place_id != '')
	// 			{
	// 				$q->where('warehouses.place_location', $place_id);
	// 			}
	// 			if ($category != "")
	// 			{
	// 				$q->where('cat_warehouse_types.id',$category);
	// 			}
	// 			if ($enterprise != "" && $enterprise != "todas")
	// 			{
	// 				$q->where('lots.idEnterprise', $enterprise);
	// 			}
	// 			if ($account_id != "" && $account_id != "todas")
	// 			{
	// 				$q->where('lots.account', $account_id);
	// 			}
	// 			if($concept != "")
	// 			{
	// 				$q->where('cat_warehouse_concepts.description','LIKE','%'.$concept.'%');
	// 			}
	// 			if ($mindate != "" && $maxdate != "")
	// 			{
	// 				$q->whereBetween('request_models.deliveryDate',[$mindate.' 00:00:00', $maxdate.' 23:59:59']);
	// 			}
	// 		})
	// 		->orderBy('request_models.folio')
	// 		->selectRaw(
	// 			'
	// 				request_models.folio, 
	// 				stationeries.title, 
	// 				stationeries.datetitle, 
	// 				stationeries.subtotal, 
	// 				stationeries.iva, 
	// 				stationeries.total, 
	// 				CONCAT_WS(" ",user_request.name, user_request.last_name, user_request.scnd_last_name) as userRequest_name, 
	// 				CONCAT_WS(" ",user_elaborate.name, user_elaborate.last_name, user_elaborate.scnd_last_name) as userElaborate_name, 
	// 				enterprise_elaborate.name as enterpriseElaborate, 
	// 				area_elaborate.name as areaElaborate, 
	// 				department_elaborate.name as departmentElaborate, 
	// 				project_elaborate.proyectName as projectElaborate, 
	// 				CONCAT_WS(" ",user_check.name, user_check.last_name, user_check.scnd_last_name) as userCheck_name, 
	// 				request_models.reviewDate, 
	// 				enterprise_review.name as enterpriseReview, 
	// 				area_review.name as areaReview, 
	// 				department_review.name as departmentReview, 
	// 				project_review.proyectName as projectReview,
	// 				CONCAT_WS(" ",user_authorize.name, user_authorize.last_name, user_authorize.scnd_last_name) as userAuthorize_name, 
	// 				request_models.authorizeDate, 
	// 				detail_stationeries.product, 
	// 				detail_stationeries.quantity, 
	// 				detail_stationeries.subtotal as ds_subtotal, 
	// 				detail_stationeries.iva as ds_iva, 
	// 				detail_stationeries.total as ds_total, 
	// 				cat_warehouse_concepts.description as productDescription, 
	// 				places.place
	// 			')
	// 		->get();

	// 	if(count($deliveries)==0 || $deliveries==null)
	// 	{
	// 		return redirect()->back()->with('alert',"swal('', '".Lang::get("messages.result_not_found")."', 'error');");
	// 	}

	// 	$defaultStyle   = (new StyleBuilder())->setFontName('Calibri')->setFontSize(12)->build();
	// 	$currencyFormat = (new StyleBuilder())->setFormat('_("$"* #,##0.00_),_("$"* \(#,##0.00\),_("$"* "-"??_),_(@_)')->build();
	// 	$rowDark        = (new StyleBuilder())->setBackgroundColor('F0F0F0')->setCellAlignment(CellAlignment::LEFT)->build();
	// 	$dateFormat   	= (new StyleBuilder())->setFormat('d-m-yy');
	// 	$mhStyleCol1    = (new StyleBuilder())->setBackgroundColor('000000')->setFontColor(Color::WHITE)->build();
	// 	$mhStyleCol2    = (new StyleBuilder())->setBackgroundColor('104f64')->setFontColor(Color::WHITE)->build();
	// 	$alignment		= (new StyleBuilder())->setCellAlignment(CellAlignment::LEFT)->build();
	// 	$writer         = WriterEntityFactory::createXLSXWriter();
	// 	$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('Reporte-Entradas-Salidas.xlsx');
	// 	$writer->getCurrentSheet()->setName('DATOS');
	
	// 	$headers		= ['Reporte-Entradas-Salidas', '','','','','','Datos de solicitante','','','','','','Datos de revisión', '','','','','', 'Datos de autorización', '', 'Datos de artículos entregados', '', '', '', '', '', ''];
	// 	$tempHeaders    = [];
	// 	foreach($headers as $k => $mh)
	// 	{
	// 		$tempHeaders[] = WriterEntityFactory::createCell($mh,$mhStyleCol1);
	// 	}
	// 	$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
	// 	$writer->addRow($rowFromValues);

	// 	$subHeader    = ['Folio', 'Título', 'Fecha', 'Subtotal', 'IVA', 'Total', 'Solicitante', 'Elaborado por', 'Empresa', 'Dirección', 'Departamento', 'Proyecto', 'Revisada por', 'Fecha de revisión', 'Empresa', 'Dirección', 'Departamento', 'Proyecto', 'Autorizada por', 'Fecha de autorización', 'Artículo solicitado', 'Cantidad de artículos solicitados', 'Artículo entregado (Del inventario)', 'Ubicación del artículo entregado', 'Subtotal', 'IVA', 'Total'];
	// 	$tempSubHeader = [];
	// 	foreach($subHeader as $sh)
	// 	{
	// 		$tempSubHeader[] = WriterEntityFactory::createCell($sh,$mhStyleCol2);
	// 	}
	// 	$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
	// 	$writer->addRow($rowFromValues);

	// 	$tempFolio     = "";
	// 	$kindRow       = true;
		
	// 	foreach($deliveries as $delivery)
	// 	{
	// 		if($tempFolio != $delivery->folio)
	// 		{
	// 			$tempFolio = $delivery->folio;
	// 			$kindRow = !$kindRow;
	// 		}
	// 		else
	// 		{
	// 			$delivery->folio					= null;
	// 			$delivery->title					= "";
	// 			$delivery->datetitle				= "";
	// 			$delivery->subtotal					= null;
	// 			$delivery->iva						= null;
	// 			$delivery->total					= null;
	// 			$delivery->userRequest_name			= "";
	// 			$delivery->userElaborate_name		= "";
	// 			$delivery->enterpriseElaborate		= "";
	// 			$delivery->areaElaborate			= "";
	// 			$delivery->departmentElaborate		= "";
	// 			$delivery->projectElaborate			= "";
	// 			$delivery->userCheck_name			= "";
	// 			$delivery->reviewDate				= "";
	// 			$delivery->enterpriseReview			= "";
	// 			$delivery->areaReview				= "";
	// 			$delivery->departmentReview			= "";
	// 			$delivery->projectReview			= "";
	// 			$delivery->userAuthorize_name		= "";
	// 			$delivery->authorizeDate			= "";
	// 			$delivery->product					= "";
	// 			$delivery->quantity					= "";
	// 			$delivery->ds_subtotal				= "";
	// 			$delivery->ds_iva					= "";
	// 			$delivery->ds_total					= "";
	// 			$delivery->productDescription		= "";
	// 			$delivery->place					= "";
	// 		}
	// 		$tmpArr = [];
	// 		foreach($delivery->toArray() as $k => $r)
	// 		{
	// 			$tmpArr[] = WriterEntityFactory::createCell($r,$alignment);
	// 		}
	// 		if($kindRow)
	// 		{
	// 			$rowFromValues = WriterEntityFactory::createRow($tmpArr,$rowDark);
	// 		}
	// 		else
	// 		{
	// 			$rowFromValues = WriterEntityFactory::createRow($tmpArr, $alignment);
	// 		}
	// 		$writer->addRow($rowFromValues);
	// 	}
	// 	return $writer->close();
	// }
	public function exportDeliveryReport(Request $request)
	{
		$data	    = App\Module::find($this->module_id);
		$enterprise = $request->idEnterprise;
		$place_id   = $request->place_id;
		$mindate   	= $request->mindate != "" ? Carbon::createFromFormat('d-m-Y',$request->mindate)->format('Y-m-d') : null;
		$maxdate   	= $request->maxdate != "" ? Carbon::createFromFormat('d-m-Y',$request->maxdate)->format('Y-m-d') : null;
		$account_id = $request->account_id;
		$concept    = $request->concept;
		$category   = $request->cat;
		$deliveries = DB::table('detail_stationeries')
			->leftJoin('warehouses', 'detail_stationeries.idwarehouse', '=', 'warehouses.idwarehouse')
			->leftJoin('cat_warehouse_concepts', 'warehouses.concept', 'cat_warehouse_concepts.id')
			->leftJoin('cat_warehouse_types', 'warehouses.warehouseType', 'cat_warehouse_types.id')
			->leftJoin('lots', 'warehouses.idLot', 'lots.idlot')
			->leftJoin('stationeries', 'detail_stationeries.idStat', 'stationeries.idStationery')
			->leftJoin('request_models', 'stationeries.idFolio', 'request_models.folio')
			->leftJoin('users as user_request', 'request_models.idRequest', 'user_request.id')
			->leftJoin('users as user_elaborate', 'request_models.idElaborate', 'user_elaborate.id')
			->leftJoin('users as user_check', 'request_models.idCheck', 'user_check.id')
			->leftJoin('users as user_authorize', 'request_models.idAuthorize', 'user_authorize.id')
			->leftJoin('places', 'warehouses.place_location', 'places.id')
			->leftJoin('enterprises as enterprise_elaborate', 'request_models.idEnterprise', 'enterprise_elaborate.id')
			->leftJoin('enterprises as enterprise_review', 'request_models.idEnterpriseR', 'enterprise_review.id')
			->leftJoin('areas as area_elaborate', 'request_models.idArea', 'area_elaborate.id')
			->leftJoin('areas as area_review', 'request_models.idAreaR', 'area_review.id')
			->leftJoin('departments as department_elaborate', 'request_models.idDepartment', 'department_elaborate.id')
			->leftJoin('departments as department_review', 'request_models.idDepartamentR', 'department_review.id')
			->leftJoin('projects as project_elaborate', 'request_models.idProject', 'project_elaborate.idproyect')
			->leftJoin('projects as project_review', 'request_models.idProjectR', 'project_review.idproyect')
			->where(function($q) use($place_id, $category, $enterprise, $account_id, $maxdate, $mindate, $concept)
			{
				if($place_id != '')
				{
					$q->where('warehouses.place_location', $place_id);
				}
				if ($category != "")
				{
					$q->where('cat_warehouse_types.id',$category);
				}
				if ($enterprise != "" && $enterprise != "todas")
				{
					$q->where('lots.idEnterprise', $enterprise);
				}
				if ($account_id != "" && $account_id != "todas")
				{
					$q->where('lots.account', $account_id);
				}
				if($concept != "")
				{
					$q->where('cat_warehouse_concepts.description','LIKE','%'.$concept.'%');
				}
				if ($mindate != "" && $maxdate != "")
				{
					$q->whereBetween('request_models.deliveryDate',[$mindate.' 00:00:00', $maxdate.' 23:59:59']);
				}
			})
			->orderBy('request_models.folio')
			->select('request_models.folio', 'stationeries.title', 'stationeries.datetitle', 'stationeries.subtotal', 'stationeries.iva', 'stationeries.total', 'user_request.name as userRequest_name', 'user_request.last_name as userRequest_lastName', 'user_request.scnd_last_name as userRequest_secondLastName', 'user_elaborate.name as userElaborate_name', 'user_elaborate.last_name as userElaborate_lastName', 'user_elaborate.scnd_last_name as userElaborate_secondLastName', 'enterprise_elaborate.name as enterpriseElaborate', 'area_elaborate.name as areaElaborate', 'department_elaborate.name as departmentElaborate', 'project_elaborate.proyectName as projectElaborate', 'user_check.name as userCheck_name', 'user_check.last_name as userCheck_lastName', 'user_check.scnd_last_name as userCheck_secondLastName', 'request_models.reviewDate', 'enterprise_review.name as enterpriseReview', 'area_review.name as areaReview', 'department_review.name as departmentReview', 'project_review.proyectName as projectReview', 'user_authorize.name as userAuthorize_name', 'user_authorize.last_name as userAuthorize_lastName', 'user_authorize.scnd_last_name as userAuthorize_secondLastName', 'request_models.authorizeDate', 'detail_stationeries.product', 'detail_stationeries.quantity', 'detail_stationeries.subtotal as ds_subtotal', 'detail_stationeries.iva as ds_iva', 'detail_stationeries.total as ds_total', 'cat_warehouse_concepts.description as productDescription', 'places.place')
			->get();
		Excel::create('Reporte-de-entregas-almacén', function($excel) use ($deliveries)
		{
			if (count($deliveries)>0) 
			{
				$excel->sheet('entregas',function($sheet) use ($deliveries)
				{
					$sheet->setStyle([
							'font' => [
								'name'	=> 'Calibri',
								'size'	=> 12
							],
							'alignment' => [
								'vertical' => 'center',
							]
					]);
					$sheet->setColumnFormat(array(
						'X'	=> '@',
						'AC'	=> '"$"#,##0.00_-',
						'AD'	=> '"$"#,##0.00_-',
						'AE'	=> '"$"#,##0.00_-',
						'AF'	=> '"$"#,##0.00_-',
						'AG'	=> '"$"#,##0.00_-',
						'AH'	=> '"$"#,##0.00_-',
						'AJ'	=> '"$"#,##0.00_-',
					));
					$sheet->mergeCells('A1:AA1');

					$sheet->mergeCells('A2:F2');
					$sheet->mergeCells('G2:L2');
					$sheet->mergeCells('M2:R2');
					$sheet->mergeCells('S2:T2');
					$sheet->mergeCells('V2:AA2');

					$sheet->cell('A1:AA1', function($cells)
					{
						$cells->setBackground('#000000');
						$cells->setFontColor('#ffffff');
					});
					$sheet->cell('A2:AA2', function($cells)
					{
						$cells->setBackground('#1d353d');
						$cells->setFontColor('#ffffff');
					});
					$sheet->cell('A3:AA3', function($cells)
					{
						$cells->setBackground('#104f64');
						$cells->setFontColor('#ffffff');
					});
					$sheet->cell('A1:AA3', function($cells)
					{
						$cells->setFontWeight('bold');
						$cells->setAlignment('center');
						$cells->setFont(array('family' => 'Calibri','size' => '16','bold' => true));
					});
					$sheet->setAutoSize(array(
						'A',
						'C',
						'D',
						'F',
						'G',
						'H',
						'I',
						'J',
						'K',
						'L',
						'M',
						'N',
						'O',
						'P',
						'Q',
						'S',
						'T',
						'U',
						'V',
						'W',
						'X',
						'Y',
						'Z',
						'AA'
					));
					$sheet->setWidth(array(
						'B'	=>  70,
					));
					$sheet->row(1,['Reporte de entregas']);
					$sheet->row(2,['Datos de la solicitud','','','','','','Datos de solicitante','','','','','','Datos de revisión','','','','','','Datos de autorización','','Datos de artpiculos entregados','','','','','','']);
					$sheet->row(3,['Folio','Título','Fecha','Subtotal','IVA','Total','Solicitante','Elaborado por','Empresa','Dirección','Departamento','Proyecto','Revisada por','Fecha de revisión','Empresa','Dirección','Departamento','Proyecto','Autorizada por','Fecha de autorización','Artículo solicitado','Cantidad de artículos solicitados','Artículo entregado (Del inventario)','Ubicación del artículo entregado','Subtotal','Iva','Total']);

					foreach ($deliveries as $delivery)
					{
						$row	= [];
						$row[]	= $delivery->folio;
						$row[]	= $delivery->title != "" ? $delivery->title : '';
						$row[]	= date('d-m-Y H:s',strtotime($delivery->datetitle));
						$row[]	= $delivery->subtotal;
						$row[]	= $delivery->iva;
						$row[]	= $delivery->total;
						$row[]	= $delivery->userRequest_name." ".$delivery->userRequest_lastName." ".$delivery->userRequest_secondLastName;
						$row[]	= $delivery->userElaborate_name." ".$delivery->userElaborate_lastName." ".$delivery->userElaborate_secondLastName;
						$row[]	= $delivery->enterpriseElaborate;
						$row[]	= $delivery->areaElaborate;
						$row[]	= $delivery->departmentElaborate;
						$row[]	= $delivery->projectElaborate;
						$row[]	= $delivery->userCheck_name." ".$delivery->userCheck_lastName." ".$delivery->userCheck_secondLastName;
						$row[]	= date('d-m-Y H:s',strtotime($delivery->reviewDate));
						$row[]	= $delivery->enterpriseReview;
						$row[]	= $delivery->areaReview;
						$row[]	= $delivery->departmentReview;
						$row[]	= $delivery->projectReview;
						$row[]	= $delivery->userAuthorize_name." ".$delivery->userAuthorize_lastName." ".$delivery->userAuthorize_secondLastName;
						$row[]	= date('d-m-Y H:s',strtotime($delivery->authorizeDate));
						$row[]	= $delivery->product != "" ? $delivery->product : '';
						$row[]	= $delivery->quantity != "" ? $delivery->quantity : '';
						$row[]	= $delivery->productDescription != "" ? $delivery->productDescription : '';
						$row[]	= $delivery->place != "" ? $delivery->place : '';
						$row[]	= $delivery->ds_subtotal; // "ds" se refiere al DetailStationery 
						$row[]	= $delivery->ds_iva;
						$row[]	= $delivery->ds_total;
						$sheet->appendRow($row);
					}
				});
			}
		})->export('xlsx');
	}

	public function toolPurchaseExport(Request $request)
	{
		if(Auth::user()->module->where('id',113)->count()>0)
		{
			$requests = App\RequestModel::selectRaw(
					'
					request_models.folio as folio,
					CONCAT(purchases.title," - ",purchases.datetitle) as title,
					detail_purchases.idDetailPurchase as idDetailPurchase,
					CONCAT("") as place,
					CONCAT("","") as category,
					request_models.accountR as account,
					detail_purchases.code as code,
					detail_purchases.quantity as quantity,
					CONCAT("","") as damaged,
					detail_purchases.unit as unit,
					detail_purchases.description as description,
					detail_purchases.unitPrice as unitPrice,
					detail_purchases.subtotal as subtotal,
					detail_purchases.tax as tax,
					IFNULL(taxes_purchase.taxes_amount,0) as taxes,
					IFNULL(retention_purchase.retention_amount,0) as retentions,
					detail_purchases.amount as amount
					'
				)
				->leftJoin('purchases',function($q)
				{
					$q->on('request_models.folio','=','purchases.idFolio')
						->on('request_models.kind','=','purchases.idKind');
				})
				->leftJoin('detail_purchases','purchases.idPurchase','detail_purchases.idPurchase')
				->leftJoin(DB::raw('(SELECT idDetailPurchase, SUM(amount) as taxes_amount FROM taxes_purchases GROUP BY idDetailPurchase) AS taxes_purchase'),'detail_purchases.idDetailPurchase','taxes_purchase.idDetailPurchase')
				->leftJoin(DB::raw('(SELECT idDetailPurchase, SUM(amount) as retention_amount FROM retention_purchases GROUP BY idDetailPurchase) AS retention_purchase'),'detail_purchases.idDetailPurchase','retention_purchase.idDetailPurchase')
				->where('request_models.kind',1)
				->whereHas('budget',function($q)
				{
					$q->where('status',1);
				})
				->whereIn('request_models.status',[5,10,11,12])
				->where('request_models.statusWarehouse',0)
				->where('request_models.goToWarehouse',1)
				->whereIn('request_models.idEnterpriseR',Auth::user()->inChargeEnt(113)->pluck('enterprise_id'))
				->where(function($query) use ($request)
				{
					if($request->search != '')
					{
						$query->where('folio','like','%'.$request->search.'%')
						->orWhereHas('purchases',function($q) use ($request)
						{
							$q->where('title','like','%'.$request->search.'%');
						})
						->orWhereHas('elaborateUser',function($q) use ($request)
						{
							$q->where(DB::raw("CONCAT_WS(' ',users.name,users.last_name,users.scnd_last_name)"),'like','%'.$request->search.'%');
						})
						->orWhereHas('requestEnterprise',function($q) use ($request)
						{
							$q->where('name','like','%'.$request->search.'%');
						});
					}
				})
				->has('purchases.documents')
				->orderBy('fDate','DESC')
				->orderBy('folio','DESC')
				->get();
			$default_style = (new StyleBuilder())
				->setFontName('Calibri')
				->setFontSize(12)
				->build();
			$currency_format = (new StyleBuilder())
				->setFormat('_("$"* #,##0.00_),_("$"* \(#,##0.00\),_("$"* "-"??_),_(@_)')
				->build();
			$dark_format = (new StyleBuilder())
				->setBackgroundColor('F0F0F0')
				->build();
			$header_format = (new StyleBuilder())
				->setBackgroundColor('1d353d')
				->setFontColor(Color::WHITE)
				->build();
			$subheader_format = (new StyleBuilder())
				->setBackgroundColor('104f64')
				->setFontColor(Color::WHITE)
				->setCellAlignment(CellAlignment::CENTER)
				->build();
			$writer = WriterEntityFactory::createXLSXWriter();
			$writer->setDefaultRowStyle($default_style)->openToBrowser('compras.xlsx');
			$headers      = ['Datos de la solicitud','','','','','','','','','','','','','','','',''];
			$temp_headers = [];
			foreach($headers as $header)
			{
				$temp_headers[] = WriterEntityFactory::createCell($header,$header_format);
			}
			$row = WriterEntityFactory::createRow($temp_headers);
			$writer->addRow($row);
			$subheaders      = ['folio','titulo','id','ubicacion_sede','categoria','cuenta','código','cantidad','dañados','unidad','descripcion','precio_unitario','subtotal','iva','impuesto_adicional','retenciones','total'];
			$temp_subheaders = [];
			foreach($subheaders as $subheader)
			{
				$temp_subheaders[] = WriterEntityFactory::createCell($subheader,$subheader_format);
			}
			$row = WriterEntityFactory::createRow($temp_subheaders);
			$writer->addRow($row);
			$folio	= null;
			$dark	= true;
			foreach($requests as $request)
			{
				if($folio != $request->folio)
				{
					$folio = $request->folio;
					$dark  = !$dark;
				}
				else
				{
					$request->folio = null;
					$request->title = null;
				}
				$row_temp = [];
				foreach($request->toArray() as $key => $value)
				{
					if(in_array($key,['unitPrice','subtotal','tax','taxes','retentions','amount',]))
					{
						if($value != '')
						{
							$row_temp[] = WriterEntityFactory::createCell((double) $value, $currency_format);
						}
						else
						{
							$row_temp[] = WriterEntityFactory::createCell($value);
						}
					}
					elseif($key == 'quantity')
					{
						if($value != '')
						{
							$row_temp[] = WriterEntityFactory::createCell((double) $value);
						}
						else
						{
							$row_temp[] = WriterEntityFactory::createCell($value);
						}
					}
					else
					{
						$row_temp[] = WriterEntityFactory::createCell($value);
					}
				}
				if($dark)
				{
					$row = WriterEntityFactory::createRow($row_temp,$dark_format);
				}
				else
				{
					$row = WriterEntityFactory::createRow($row_temp);
				}
				$writer->addRow($row);
			}
			return $writer->close();
		}
		else
		{
			return redirect('error');
		}
	}

	public function toolMassiveStore(Request $request)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$data		= App\Module::find($this->module_id);

			$old_lot_id = "";

			if (isset($request->t_folio) && count($request->t_folio)>0) 
			{
				for ($i=0; $i < count($request->t_folio); $i++) 
				{ 
					$t_request	= App\RequestModel::find($request->t_folio[$i]);
					$dp			= App\DetailPurchase::where('idDetailPurchase',$request->t_id[$i])->first();

					if ($dp->statusWarehouse != 1) 
					{
						$checkLot 		= App\Lot::where('idFolio',$t_request->folio)->get();

						if (count($checkLot)>0) 
						{
							$t_lot	= $checkLot->first();
						}
						else
						{
							$t_lot	= new App\Lot();
						}

						$t_lot->subtotal		= $t_lot->subtotal + $request->tsub_total[$i];
						$t_lot->iva				= $t_lot->iva + $request->tiva[$i];
						$t_lot->total			= $t_lot->total + $request->tamount[$i];
						$t_lot->articles		= $t_lot->articles + $request->tamount[$i];
						$t_lot->date			= Carbon::now();
						$t_lot->idEnterprise	= $t_request->idEnterpriseR;
						$t_lot->idElaborate		= Auth::user()->id;
						$t_lot->idKind			= 7;
						$t_lot->idFolio			= $request->t_folio[$i];
						$t_lot->status 			= 2;
						$t_lot->save();


						if (App\LotIdFolios::where('idLot',$t_lot->idlot)->where('idFolio',$t_request->folio)->count() <= 0) 
						{
							App\LotIdFolios::create([
								'idLot'     => $t_lot->idlot,
								'idFolio'   => $t_request->folio,
							]);
						}


						// $request->t_folio[$i];
						// $request->t_title[$i];
						// $request->tplace_id[$i];
						// $request->tcategory_id[$i];
						// $request->taccount_id[$i];
						// $request->tshort_code[$i];
						// $request->tquanty[$i];
						// $request->tdamaged[$i];
						// $request->tmeasurement_id[$i];
						// $request->tconcept_name[$i];
						// $request->tuamount[$i];
						// $request->tsub_total[$i];
						// $request->tiva[$i];
						// $request->tamount[$i];

						// ----------------------------------------------------------------------------------------------------------------

						$t_warehouse    = new App\Warehouse();
						$cat            = App\CatWarehouseConcept::where('description',$request->tconcept_name[$i])->count();
						
						if($cat == 0)
						{
							$cat    = App\CatWarehouseConcept::create([
									'description'   => $request->tconcept_name[$i],
									'warehouseType' => $request->tcategory_id[$i],
								]);
						}
						else
						{
							$cat    = App\CatWarehouseConcept::where('description',$request->tconcept_name[$i])->first();
						}

						$t_warehouse->concept      = $cat->id;
						$t_warehouse->quantity     = $request->tquanty[$i];
						$t_warehouse->quantityReal = $request->tquanty[$i] + $request->tdamaged[$i];
						$t_warehouse->damaged      = $request->tdamaged[$i];

						if(($request->tquanty[$i] + $request->tdamaged[$i]) == 0)
						{
							$t_warehouse->status = 0;
						}

						$t_warehouse->measurement		= $request->tmeasurement_id[$i];
						$t_warehouse->amountUnit		= $request->tuamount[$i];
						$t_warehouse->iva				= $request->tiva[$i];
						$t_warehouse->typeTax			= $request->tiva_kind[$i];
						$t_warehouse->subtotal			= $request->tsub_total[$i];
						$t_warehouse->amount			= $request->tamount[$i];
						$t_warehouse->idLot				= $t_lot->idlot;
						$t_warehouse->warehouseType		= $request->tcategory_id[$i];
						$t_warehouse->short_code		= $request->tshort_code[$i];
						$t_warehouse->place_location	= $request->tplace_id[$i];
						$t_warehouse->account			= $request->taccount_id[$i];

						$t_warehouse->commentaries      = null;

						/*
							if($request->tcategory_id[$i] == 4)
							{
								$t_warehouse->type      = $request->ttype[$i];
								$t_warehouse->brand     = $request->tbrand[$i];
								$t_warehouse->storage   = $request->tstorage[$i];
								$t_warehouse->processor = $request->tprocessor[$i];
								$t_warehouse->ram       = $request->tram[$i];
								$t_warehouse->sku       = $request->tsku[$i];
							}
						*/

						$t_warehouse->save();

						$dp->statusWarehouse = 1;
						$dp->save();

						$total_altas	=  App\DetailPurchase::where('idPurchase',$t_request->purchases->first()->idPurchase)
										->where('statusWarehouse',1)
										->count();

						if($total_altas == $t_request->purchases()->first()->detailPurchase()->count())
						{
							$t_request->statusWarehouse = 1;
							$t_request->save();
						}
					}
				}
			}
			$alert	= "swal('','".Lang::get("messages.record_created")."', 'success');";
			return redirect('/warehouse')->with('alert',$alert);
		}
		else
		{
			return redirect('/');
		}
	}

	public function toolCatExport()
	{
		if(Auth::user()->module->where('id',113)->count() > 0)
		{
			Excel::create('Catalogos de Almacén', function($excel)
			{
				$excel->sheet('Categorías',function($sheet)
				{
					$sheet->setStyle(array(
						'font' => array(
								'name' => 'Calibri',
								'size' => 12
							)
						));
					$sheet->cell('A1:B1', function($cells)
					{
						$cells->setFontWeight('bold');
						$cells->setAlignment('center');
						$cells->setFont(array('family' => 'Calibri','size' => '16','bold' => true));
					});
					
					$sheet->row(1,
					[
						'ID',
						'Descripción'
					]);

					foreach(App\CatWarehouseType::selectRaw('id,description')->get() as $category)
					{
						$sheet->appendRow($category->toArray());
					}
				});

				$excel->sheet('Ubicación',function($sheet)
				{
					$sheet->setStyle(array(
						'font' => array(
								'name' => 'Calibri',
								'size' => 12
							)
						));
					$sheet->cell('A1:B1', function($cells)
					{
						$cells->setFontWeight('bold');
						$cells->setAlignment('center');
						$cells->setFont(array('family' => 'Calibri','size' => '16','bold' => true));
					});
					$sheet->row(1,
					[
						'ID',
						'Ubicación'
					]);

					foreach(App\Place::selectRaw('id,place')->where('status',1)->get() as $place)
					{
						$sheet->appendRow($place->toArray());
					}
				});
			})->export('xlsx');
		}
		else
		{
			return redirect('/');
		}
	}
}
