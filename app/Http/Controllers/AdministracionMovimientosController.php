<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\URL;
use App\http\Requests\GeneralRequest;
use App;
use Alert;
use Auth;
use Lang;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\Notificacion;
use Ilovepdf\CompressTask;
use PDF;
use Excel;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Common\Entity\Row;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Box\Spout\Common\Entity\Style\Color;
use Box\Spout\Common\Entity\Style\CellAlignment;
use Box\Spout\Common\Entity\Style\Border;
use Box\Spout\Writer\Common\Creator\Style\BorderBuilder;
use App\Functions\Files;
use Illuminate\Support\Facades\Cookie;

class AdministracionMovimientosController extends Controller
{
	private $module_id = 147;
	public function index()
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$data  = App\Module::find($this->module_id);
			return view('layouts.child_module',
				[
					'id'        => $data['father'],
					'title'     => $data['name'],
					'details'   => $data['details'],
					'child_id'  => $this->module_id
				]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function create()
	{
		if (Auth::user()->module->where('id',171)->count()>0)
		{
			$data   = App\Module::find($this->module_id);
			return view('administracion.movimientos_entre_cuentas.alta',
				[
					'id'		=> $data['father'],
					'title'		=> $data['name'],
					'details'	=> $data['details'],
					'child_id'	=> $this->module_id,
					'option_id'	=> 171
				]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function newRequest($id)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			if(Auth::user()->globalCheck->where('module_id',149)->count()>0)
			{
				$global_permission =  Auth::user()->globalCheck->where('module_id',149)->first()->global_permission;
			}
			else
			{
				$global_permission = 0;
			}

			$data		= App\Module::find($this->module_id);
			$requests 	= App\RequestModel::whereIn('status',[5,6,7,10,11,12,13])
						->where(function ($q) use ($global_permission)
						{
							if ($global_permission == 0) 
							{
								$q->where('idElaborate',Auth::user()->id)->orWhere('idRequest',Auth::user()->id);
							}
						})
						->find($id);
			if($requests != "")
			{
				switch ($requests->kind) 
				{
					case 11:
						return view('administracion.movimientos_entre_cuentas.ajuste',
						[
							'id'		=> $data['father'],
							'title'		=> $data['name'],
							'details'	=> $data['details'],
							'child_id'	=> $this->module_id,
							'option_id'	=> 171,
							'requests' 	=> $requests
						]);
						break;

					case 12:
						return view('administracion.movimientos_entre_cuentas.prestamo',
						[
							'id'		=> $data['father'],
							'title'		=> $data['name'],
							'details'	=> $data['details'],
							'child_id'	=> $this->module_id,
							'option_id'	=> 172,
							'requests' 	=> $requests
						]);
						break;

					case 13:
						return view('administracion.movimientos_entre_cuentas.compra',
						[
							'id'		=> $data['father'],
							'title'		=> $data['name'],
							'details'	=> $data['details'],
							'child_id'	=> $this->module_id,
							'option_id'	=> 173,
							'requests' 	=> $requests
						]);
						break;

					case 14:
						return view('administracion.movimientos_entre_cuentas.grupos',
						[
							'id'		=> $data['father'],
							'title'		=> $data['name'],
							'details'	=> $data['details'],
							'child_id'	=> $this->module_id,
							'option_id'	=> 174,
							'requests' 	=> $requests
						]);
						break;

					case 15:
						return view('administracion.movimientos_entre_cuentas.movimientos',
						[
							'id'		=> $data['father'],
							'title'		=> $data['name'],
							'details'	=> $data['details'],
							'child_id'	=> $this->module_id,
							'option_id'	=> 175,
							'requests' 	=> $requests
						]);
						break;
					
					default:
						# code...
						break;
				}
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

	public function adjustment(Request $request)
	{
		if(Auth::user()->module->where('id',171)->count()>0)
		{
			$data  = App\Module::find($this->module_id);
			return view('administracion.movimientos_entre_cuentas.ajuste',
				[
					'id'        => $data['father'],
					'title'     => $data['name'],
					'details'   => $data['details'],
					'child_id'  => $this->module_id,
					'option_id' => 171
				]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function getDetailRequest(Request $request)
	{
		$request	= App\RequestModel::find($request->folio);
		$enterprise	= $request->reviewedEnterprise->name!=null ? $request->reviewedEnterprise->name : "";
		$direction	= $request->reviewedDirection->name;
		$department	= $request->reviewedDepartment->name;
		$account	= $request->accountsReview()->exists() ? $request->accountsReview->account.' '.$request->accountsReview->description : 'Varias';
		$project 	= $request->reviewedProject->proyectName;
		$kind 		= $request->requestkind->kind;
		
		$subtotalFinal = $ivaFinal = $totalFinal = 0;

			$modelTable	=
			[
				["Empresa",					[["kind"	=>	"components.labels.label",	"label"	=>	$enterprise]]],
				["Dirección",				[["kind"	=>	"components.labels.label",	"label"	=>	$direction]]],
				["Departamento",			[["kind"	=>	"components.labels.label",	"label"	=>	$department]]],
				["Clasificación de gasto",	[["kind"	=>	"components.labels.label",	"label"	=>	$account]]],
				["Proyecto",				[["kind"	=>	"components.labels.label",	"label"	=>	$project]]],
			];
			$detail = view('components.templates.outputs.table-detail',[
				"modelTable"	=>	$modelTable,
				"title"	=>
				[
					["kind" =>	"components.labels.label",	"label"	=>	"Detalles de la Solicitud de ".$kind, "classEx" => "text-center text-white"],
					["kind"	=>	"components.inputs.input-text",	"attributeEx"	=>	"type=\"hidden\"	name=\"enterprise_request\"	value=\"".$enterprise."\""],
					["kind"	=>	"components.inputs.input-text",	"attributeEx"	=>	"type=\"hidden\"	name=\"direction_request\"	value=\"".$direction."\""],
					["kind"	=>	"components.inputs.input-text",	"attributeEx"	=>	"type=\"hidden\"	name=\"department_request\"	value=\"".$department."\""],
					["kind"	=>	"components.inputs.input-text",	"attributeEx"	=>	"type=\"hidden\"	name=\"account_request\"	value=\"".$account."\""],
					["kind"	=>	"components.inputs.input-text",	"attributeEx"	=>	"type=\"hidden\"	name=\"project_request\"	value=\"".$project."\""],
				]
			]);
		switch ($request->kind)
		{
			case 1:
				$detail.=html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.labels.title-divisor", ["classEx" => "mt-4", "slot"	=>	"DATOS DE LA SOLICITUD"])));
				$modelHead	=	[];
				$body		=	[];
				$modelBody	=	[];
				$modelHead	=
				[
					[
						["value"	=>	"#"],
						["value"	=>	"Cantidad"],
						["value"	=>	"Unidad"],
						["value"	=>	"Descripción"],
						["value"	=>	"Precio Unitario"],
						["value"	=>	"IVA"],
						["value"	=>	"Impuesto Adicional"],
						["value"	=>	"Retenciones"],
						["value"	=>	"Importe"],
					]
				];
				$countConcept	=	1;
				$taxes			=	$retentions	=	0;
				foreach($request->purchases->first()->detailPurchase as $det)
				{
					$taxesConcept	=	0;
					foreach($det->taxes as $tax)
					{
						$taxesConcept+=$tax->amount;
					}
					$retentionConcept	=	0;
					foreach($det->retentions as $ret)
					{
						$retentionConcept+=$ret->amount;
					}
					$body	=
					[
						[
							"content"	=>	["label"	=>	$countConcept]
						],
						[
							"content"	=>	["label"	=>	$det->quantity]
						],
						[
							"content"	=>	["label"	=>	$det->unit]
						],
						[
							"content"	=>	["label"	=>	$det->description]
						],
						[
							"content"	=>	["label"	=>	"$ ".number_format($det->unitPrice,2)]
						],
						[
							"content"	=>	["label"	=>	"$ ".number_format($det->tax,2)]
						],
						[
							"content"	=>	["label"	=>	"$ ".number_format($taxesConcept,2)]
						],
						[
							"content"	=>	["label"	=>	"$ ".number_format($retentionConcept,2)]
						],
						[
							"content"	=>	["label"	=>	"$ ".number_format($det->amount,2)]
						],
					];
					$modelBody[]	=	$body;
				}
				$detail.=html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.tables.table",
				[
					"modelHead"			=>	$modelHead,
					"modelBody"			=>	$modelBody,
					"attributeEx"		=>	"id=\"table\"",
					"attributeExBody"	=>	"id=\"body\"",
					"classEx"			=>	"mt-4"
				])));

				foreach($request->purchases->first()->detailPurchase as $det)
				{
					foreach($det->taxes as $tax)
					{
						$taxes += $tax->amount;
					}
					foreach($det->retentions as $ret)
					{
							$retentions += $ret->amount;
					}
				}
				$modelTable	=
				[
					["label"	=>	"Subtotal:",			"inputsEx"	=>
						[
							["kind" => "components.labels.label",		"label"			=>	"$ ".number_format($request->purchases->first()->subtotales,2,".",",")],
							["kind" => "components.inputs.input-text",	"attributeEx"	=>	"name=\"subtotal_request\" value=\"".$request->purchases->first()->subtotales."\"", "classEx" => "hidden"]
						]
					],
					["label"	=>	"Impuesto Adicional:",	"inputsEx"	=>
						[
							["kind"	=>	"components.labels.label",		"label"			=>	"$ ".number_format($taxes,2,".",",")],
							["kind"	=>	"components.inputs.input-text",	"attributeEx"	=>	"name=\"tax_request\" value=\"".$taxes."\"", "classEx" => "hidden"],
						]
					],
					["label"	=>	"Retenciones:",			"inputsEx"	=>
						[
							["kind"	=>	"components.labels.label",		"label"			=>	"$ ".number_format($retentions,2,".",",")],
							["kind"	=>	"components.inputs.input-text",	"attributeEx"	=>	"name=\"retention_request\" value=\"".$retentions."\"", "classEx" => "hidden"],
						]
					],
					["label"	=>	"IVA:",					"inputsEx"	=>
						[
							["kind"	=>	"components.labels.label",		"label"			=>	"$ ".number_format($request->purchases->first()->tax,2,".",",")],
							["kind"	=>	"components.inputs.input-text",	"attributeEx"	=>	"name=\"iva_request\" value=\"".$request->purchases->first()->tax."\"", "classEx" => "hidden"],
						]
					],
					["label"	=>	"TOTAL: ",				"inputsEx"	=>
						[
							["kind"	=>	"components.labels.label",		"label"			=>	"$ ".number_format($request->purchases->first()->amount,2,".",","),	"attrinbuteEx"	=>	"id=\"input-extrasmall\""],
							["kind"	=>	"components.inputs.input-text",	"attributeEx"	=>	"name=\"total_request\" value=\"".$request->purchases->first()->amount."\"", "classEx" => "hidden"],
						]
					]
				];

				$detail	.=html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.templates.outputs.form-details",
				[
					"modelTable"			=>	$modelTable,
					"attributeExComment"	=>	"name=\"note\"",
					"textNotes"				=>	$request->purchases->first()->notes
				])));
				$detail.="<div class='flex md:justify-start justify-center mt-12'>";
					$detail	.=html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.buttons.button", ["variant" => "warning", "attributeEx" => "type=\"button\" id=\"add_request\"", "label" => "<span class=\"icon-plus\"></span><span>Agregar</span>"])));
					$detail	.=html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.buttons.button", ["variant" => "red", "attributeEx" => "type=\"button\" id=\"close_request\"", "label" => "<span class=\"icon-x\"></span><span>Cerrar</span>"])));
				$detail.="</div>";
				break;

			case 3:
				$detail	.=html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.labels.title-divisor", ["classEx" => "mt-4", "slot"	=>	"DATOS DE LA SOLICITUD"])));
				$modelHead	=	[];
				$body		=	[];
				$modelBody	=	[];
				$modelHead	=
				[
					[
						["value"	=>	"#"],
						["value"	=>	"Concepto"],
						["value"	=>	"Clasificación del gasto"],
						["value"	=>	"Subtotal"],
						["value"	=>	"IVA"],
						["value"	=>	"Impuesto Adicional"],
						["value"	=>	"Importe"]
					]
				];
				$countConcept = 1;
				$taxes = $retentions = 0;
				foreach($request->expenses->first()->expensesDetail as $det)
				{
					$subtotalFinal	+= $det->amount;
					$ivaFinal		+= $det->tax;
					$totalFinal		+= $det->sAmount;
					$taxes2 = 0;
					foreach($det->taxes as $tax)
					{
						$taxes2 += $tax->amount;
					}
					$body	=
					[
						[
							"content"	=>	["label"	=>	$countConcept]
						],
						[
							"content"	=>	["label"	=>	$det->concept]
						],
						[
							"content"	=>	["label"	=>	$det->accountR->account.' '.$det->accountR->description]
						],
						[
							"content"	=>	["label"	=>	"$ ".number_format($det->amount,2)]
						],
						[
							"content"	=>	["label"	=>	"$ ".number_format($det->tax,2)]
						],
						[
							"content"	=>	["label"	=>	"$ ".number_format($taxes2,2)]
						],
						[
							"content"	=>	["label"	=>	"$ ".number_format($det->sAmount,2)]
						]
					];
					$countConcept++;
					$modelBody[]	=	$body;
				}
				$detail	.=html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view('components.tables.table', ["modelHead" => $modelHead, "modelBody" => $modelBody, "attributeEx" => "id=\"table\"", "attributeExBody" => "id=\"body\"", "classEx" => "mt-4"])));
				if(isset($request))
				{
					foreach($request->expenses->first()->expensesDetail as $det)
					{
						foreach($det->taxes as $tax)
						{
							$taxes += $tax->amount;
						}
					}
				}
				$modelTable	=
				[
					["label"	=>	"Subtotal:",	"inputsEx"	=>
						[
							["kind"	=>	"components.labels.label",		"attributeEx"	=>	"id=\"subtotal\"", "classEx"	=>	"subtotal",	"label"	=>	"$ ".number_format($subtotalFinal,2,".",",")],
							["kind"	=>	"components.inputs.input-text",	"attributeEx"	=>	"name=\"subtotal_request\" value=\"".$subtotalFinal."\"", "classEx"	=>	"hidden"],
						],
					],
					["label"	=>	"IVA:",	"inputsEx"	=>
						[
							["kind"	=>	"components.labels.label",		"attributeEx"	=>	"id=\"iva\"", "classEx"	=>	"ivaTotal",	"label"	=>	"$ ".number_format($ivaFinal,2,".",",")],
							["kind"	=>	"components.inputs.input-text",	"attributeEx"	=>	"name=\"iva_request\" value=\"".$ivaFinal."\"", "classEx"	=>	"hidden"],
						],
					],
					["label"	=>	"Impuesto Adicional:",	"inputsEx"	=>
						[
							["kind"	=>	"components.labels.label",		"label"			=>	"$ ".number_format($taxes,2,".",",")],
							["kind"	=>	"components.inputs.input-text",	"attributeEx"	=>	"name=\"tax_request\" value=\"".$taxes."\"", "classEx"	=>	"hidden"],
							["kind"	=>	"components.inputs.input-text",	"attributeEx"	=>	"name=\"retention_request\" value=\"0\"", "classEx"	=>	"hidden"],
						],
					],
					["label"	=>	"Reintegro:",	"inputsEx"	=>
						[
							["kind"	=>	"components.labels.label",		"attributeEx"	=>	"id=\"reintegro\"", "classEx"	=>	"reintegro",	"label"	=>	"$ ".number_format($request->expenses->first()->reintegro,2,".",",")],
							["kind"	=>	"components.inputs.input-text",	"attributeEx"	=>	"name=\"reintegro\" value=\"".$request->expenses->first()->reintegro."\"", "classEx"	=>	"hidden"],
						],
					],
					["label"	=>	"Reembolso:",	"inputsEx"	=>
						[
							["kind"	=>	"components.labels.label",		"attributeEx"	=>	"id=\"reembolso\"", "classEx"	=>	"reembolso",	"label"	=>	"$ ".number_format($request->expenses->first()->reembolso,2,".",",")],
							["kind"	=>	"components.inputs.input-text",	"attributeEx"	=>	"name=\"reembolso\" value=\"".$request->expenses->first()->reembolso."\"", "classEx"	=>	"hidden"],
						],
					],
					["label"	=>	"TOTAL:",	"inputsEx"	=>
						[
							["kind"	=>	"components.labels.label",		"attributeEx"	=>	"id=\"total\"", "classEx"	=>	"total",	"label"	=>	"$ ".number_format($totalFinal,2,".",",")],
							["kind"	=>	"components.inputs.input-text",	"attributeEx"	=>	"name=\"total_request\" value=\"".$totalFinal."\"", "classEx"	=>	"hidden"],
						],
					],
				];
				$detail.=html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view('components.templates.outputs.form-details', ["modelTable" => $modelTable])));
				$detail.="<div class='md:text-left text-center mt-12'>";
					$detail.=html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.buttons.button", ["variant" => "warning", "attributeEx" => "type=\"button\" id=\"add_request\"", "label" => "<span class=\"icon-plus\"></span> Agregar"])));
					$detail.=html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.buttons.button", ["variant" => "red", "attributeEx" => "type=\"button\" id=\"close_request\"", "label" => "<span class=\"icon-x\"></span> Cerrar"])));
				$detail.="</div>";
				break;

			case 9:
				$taxes	=	0;
				$detail	.=html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.labels.title-divisor", ["classEx" => "mt-4", "slot"	=>	"DATOS DE LA SOLICITUD"])));
				$modelHead	=	[];
				$body		=	[];
				$modelBody	=	[];
				$modelHead	=
				[
					[
						["value"	=>	"Concepto",],
						["value"	=>	"Clasificación del gasto",],
						["value"	=>	"Tipo de Documento/No. Factura",],
						["value"	=>	"Fiscal"],
						["value"	=>	"Subtotal"],
						["value"	=>	"IVA"],
						["value"	=>	"Impuesto Adicional"],
						["value"	=>	"Importe"],
						["value"	=>	"Documento(s)"],
					]
				];
				foreach(App\RefundDetail::where('idRefund',$request->refunds->first()->idRefund)->get() as $refundDetail)
				{
					$subtotalFinal	+=	$refundDetail->amount;
					$ivaFinal		+=	$refundDetail->tax;
					$totalFinal		+=	$refundDetail->sAmount;
					$fiscaDetail	=	$refundDetail->taxPayment==1 ? "Si" : "No";
					$taxes2 = 0;
					foreach($refundDetail->taxes as $tax)
					{
						$taxes2 += $tax->amount;
					}
					if(App\RefundDocuments::where('idRefundDetail',$refundDetail->idRefundDetail)->get()->count()>0)
					{
						$totalComponent	=	[];
						foreach(App\RefundDocuments::where('idRefundDetail',$refundDetail->idRefundDetail)->get() as $doc)
						{
							$totalComponent[] =
							[
								"kind"	=>	"components.buttons.button",	"variant" => "dark-red",	"buttonElement"	=>	"a",	"attributeEx"	=>	"target=\"_blank\" href=\"".asset('docs/refounds/'.$doc->path)."\" title=\"".$doc->path."\"",	"label"	=>	"PDF"
							];
						}
					}
					else
					{
						$totalComponent[] =	["label"	=>	"---"];
					}
					$body	=
					[
						[
							"content"	=>	[["label"	=>	$refundDetail->concept]]
						],
						[
							"content"	=>	[["label"	=>	$refundDetail->account->account." ".$refundDetail->account->description]]
						],
						[
							"content"	=>	[["label"	=>	$refundDetail->document]]
						],
						[
							"content"	=>	[["label"	=>	$fiscaDetail]]
						],
						[
							"content"	=>
							[
								["label"	=>	"$ ".number_format($refundDetail->amount,2)],
								[
									"kind"			=>	"components.inputs.input-text",
									"attributeEx"	=>	"name=\"t_amount[]\" value=\"".$refundDetail->amount."\"",
									"classEx"		=>	"t-amount hidden"
								]
							]
						],
						[
							"content"	=>	[["label"	=>	"$ ".number_format($refundDetail->tax,2)]]
						],
						[
							"content"	=>	[["label"	=>	"$ ".number_format($taxes2,2)]]
						],
						
						[
							"content"	=>
							[
								["label"	=>	"$ ".number_format($refundDetail->sAmount,2)],
								[
									"kind"			=>	"components.inputs.input-text",
									"attributeEx"	=>	"name=\"t_total[]\"",
									"classEx"		=>	"t-iva hidden"
								]
							]
						],
						[
							"content"	=>	$totalComponent
						],
					];
					$modelBody[]	=	$body;
				}
				$detail	.=html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.tables.table", ["modelHead" => $modelHead, "modelBody" => $modelBody, "attributeEx" => "id=\"table\"", "attributeExBody" => "id=\"body\"", "classExBody" => "request-validate"])));
				
				if(isset($request))
				{
					foreach($request->refunds->first()->refundDetail as $det)
					{
						foreach($det->taxes as $tax)
						{
							$taxes += $tax->amount;
						}
					}
				}
				$modelTable	=
				[
					["label"	=>	"Subtotal:",	"inputsEx"	=>
						[
							["kind"	=>	"components.labels.label",		"attributeEx"	=>	"id='subtotal'",	"label"	=>	"$ ".number_format($subtotalFinal,2,".",","), "classEx"	=>	"subtotal"],
							["kind"	=>	"components.inputs.input-text",	"attributeEx"	=>	"name=\"subtotal_request\" value=\"".$subtotalFinal."\"", "classEx" => "hidden"]
						]
					],
					["label"	=>	"IVA:",	"inputsEx"	=>
						[
							["kind"	=>	"components.labels.label",		"attributeEx"	=>	"id=\"iva\"",	"label"			=>	"$ ".number_format($ivaFinal,2,".",","), "classEx"	=>	"ivaTotal"],
							["kind"	=>	"components.inputs.input-text",	"attributeEx"	=>	"name=\"iva_request\" value=\"".$ivaFinal."\"", "classEx" => "hidden"]
						]
					],
					["label"	=>	"Impuesto Adicional:",	"inputsEx"	=>
						[
							["kind"	=>	"components.labels.label",		"label"			=>	"$ ".number_format($taxes,2,".",",")],
							["kind"	=>	"components.inputs.input-text",	"attributeEx"	=>	"name=\"tax_request\" value=\"".$taxes."\"", "classEx" => "hidden"],
							["kind"	=>	"components.inputs.input-text",	"attributeEx"	=>	"name=\"retention_request\" value=\"0\"", "classEx" => "hidden"]
						]
					],
					["label"	=>	"TOTAL:",	"inputsEx"	=>
						[
							["kind"	=>	"components.labels.label",		"attributeEx"	=>	"id=\"total\"",	"label"	=>	"$ ".number_format($totalFinal,2,".",","),	"classEx"	=>	"total"],
							["kind"	=>	"components.inputs.input-text",	"attributeEx"	=>	"name=\"total_request\" value=\"".$totalFinal."\"", "classEx" => "hidden"]
						]
					],
				];
				$detail.=html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.templates.outputs.form-details", ["modelTable" => $modelTable])));
				$detail.="<div class='md:text-left text-center mt-12'>";
					$detail.=html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.buttons.button", ["variant" => "warning", "attributeEx" => "type=\"button\" id=\"add_request\"", "label" => "<span class=\"icon-plus\"></span> Agregar"])));
					$detail.=html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.buttons.button", ["variant" => "red", "attributeEx" => "type=\"button\" id=\"close_request\"", "label" => "<span class=\"icon-x\"></span> Cerrar"])));
				$detail.="</div>";
			default:
				# code...
				break;
		}
		return Response($detail);
	}

	public function storeAdjustment(Request $request)
	{
		if (Auth::user()->module->where('id',171)->count()>0) 
		{
			$time  						= strtotime($request->date);
			$data						= App\Module::find($this->module_id);
			$t_request					= new App\RequestModel();
			$t_request->kind			= 11;
			$t_request->taxPayment		= 1;
			$t_request->fDate			= Carbon::now();
			$t_request->status			= 3;
			$t_request->idRequest		= $request->userid;
			$t_request->idElaborate		= Auth::user()->id;
			$t_request->save();
			$folio						= $t_request->folio;
			$kind						= $t_request->kind;

			/*$subtotales	= 0;
			$iva		= 0;
			$taxes 		= 0;
			$retentions = 0;

			for ($i=0;isset($request->tquanty) && $i < count($request->tquanty); $i++)
			{
				$tamountadditional 	= 'tamountadditional'.$i;
				$tnameamount 		= 'tnameamount'.$i;

				if (isset($request->$tamountadditional) && $request->$tamountadditional != "") 
				{
					for ($d=0; $d < count($request->$tamountadditional); $d++) 
					{ 
						if ($request->$tamountadditional[$d] != "") 
						{
							$taxes 	+= $request->$tamountadditional[$d];
						}
					}
				}

				$tamountretention = 'tamountretention'.$i;
				if (isset($request->$tamountretention) && $request->$tamountretention != "") 
				{
					for ($d=0; $d < count($request->$tamountretention); $d++) 
					{ 
						if ($request->$tamountretention[$d] != "") 
						{
							$retentions 	+= $request->$tamountretention[$d];
						}
					}
				}


				$subtotales	+= (($request->tquanty[$i] * $request->tprice[$i])-$request->tdiscount[$i]);
				$iva		+= $request->tiva[$i];
			}

			$total								= ($subtotales+$iva+$taxes)-$retentions;*/
			$t_adjustment						= new App\Adjustment();
			$t_adjustment->title				= $request->title;
			$t_adjustment->tax					= $request->tax;
			$t_adjustment->datetitle			= $request->datetitle != "" ? Carbon::createFromFormat('d-m-Y',$request->datetitle)->format('Y-m-d') : null;
			$t_adjustment->numberOrder			= $request->numberOrder;
			$t_adjustment->folio				= $request->folios;
			$t_adjustment->commentaries			= $request->commentaries;
			$t_adjustment->currency				= $request->type_currency;
			$t_adjustment->notes 				= $request->notes;
			$t_adjustment->paymentDate			= $request->date !='' ? Carbon::createFromFormat('d-m-Y',$request->date)->format('Y-m-d') : null;
			$t_adjustment->subtotales			= $request->subtotal_adjustment;
			$t_adjustment->tax					= $request->iva_adjustment;
			$t_adjustment->additionalTax 		= $request->tax_adjustment;
			$t_adjustment->retention 			= $request->retention_adjustment;
			$t_adjustment->amount				= $request->total_adjustment;
			$t_adjustment->idpaymentMethod		= $request->pay_mode;
			$t_adjustment->idEnterpriseOrigin	= $request->enterpriseid_origin;
			$t_adjustment->idEnterpriseDestiny	= $request->enterpriseid_destination;
			$t_adjustment->idAreaDestiny		= $request->areaid_destination;
			$t_adjustment->idDepartamentDestiny	= $request->departmentid_destination;
			$t_adjustment->idAccAccDestiny		= $request->accountid_destination;
			$t_adjustment->idProjectDestiny		= $request->projectid_destination;
			$t_adjustment->idFolio				= $folio;
			$t_adjustment->idKind				= $kind;
			$t_adjustment->save();

			$adjustment					= $t_adjustment->idadjustment;

			for ($i=0; $i < count($request->folios_adjustment); $i++) 
			{ 
				$t_adjustmentfolios					= new App\AdjustmentFolios();
				$t_adjustmentfolios->idFolio		= $request->folios_adjustment[$i];
				$t_adjustmentfolios->idadjustment	= $adjustment;
				$t_adjustmentfolios->save();
			}
			
			if (isset($request->realPath) && count($request->realPath)>0) 
			{
				for ($i=0; $i < count($request->realPath); $i++) 
				{ 
					if ($request->realPath[$i] != "") 
					{
						$documents					= new App\AdjustmentDocuments();
						$new_file_name = Files::rename($request->realPath[$i],$folio);
						$documents->path			= $new_file_name;
						$documents->idadjustment	= $adjustment;
						$documents->save();
					}
				}
			}
			/*
			for ($i=0; isset($request->tamount) && $i < count($request->tamount); $i++)
			{
				$t_detailAdjustment					= new App\AdjustmentDetail();
				$t_detailAdjustment->idadjustment	= $adjustment;
				$t_detailAdjustment->quantity		= $request->tquanty[$i];
				$t_detailAdjustment->unit			= $request->tunit[$i];
				$t_detailAdjustment->description	= $request->tdescr[$i];
				$t_detailAdjustment->unitPrice		= $request->tprice[$i];
				$t_detailAdjustment->tax			= $request->tiva[$i];
				$t_detailAdjustment->amount			= $request->tamount[$i];
				$t_detailAdjustment->typeTax		= $request->tivakind[$i];
				$t_detailAdjustment->subtotal		= $request->tquanty[$i] * $request->tprice[$i];
				$t_detailAdjustment->save();

				$idadjustmentDetail	= $t_detailAdjustment->idadjustmentDetail;
				$tamountadditional	= 'tamountadditional'.$i;
				$tnameamount		= 'tnameamount'.$i;
				if (isset($request->$tamountadditional) && $request->$tamountadditional != "") 
				{
					for ($d=0; $d < count($request->$tamountadditional); $d++) 
					{ 
						if ($request->$tamountadditional[$d] != "") 
						{
							$t_taxes						= new App\AdjustmentTaxes();
							$t_taxes->name					= $request->$tnameamount[$d];
							$t_taxes->amount				= $request->$tamountadditional[$d];
							$t_taxes->idadjustmentDetail	= $idadjustmentDetail;
							$t_taxes->save();
						}
					}
				}

				$tamountretention 	= 'tamountretention'.$i;
				$tnameretention 	= 'tnameretention'.$i;
				if (isset($request->$tamountretention) && $request->$tamountretention != "") 
				{
					for ($d=0; $d < count($request->$tamountretention); $d++) 
					{ 
						if ($request->$tamountretention[$d] != "") 
						{
							$t_retention						= new App\AdjustmentRetention();
							$t_retention->name					= $request->$tnameretention[$d];
							$t_retention->amount				= $request->$tamountretention[$d];
							$t_retention->idadjustmentDetail	= $idadjustmentDetail;
							$t_retention->save();
						}
					}
				}
			}
			*/
			$alert = "swal('','".Lang::get("messages.request_sent")."', 'success')";
			/*$emails = App\User::whereHas('module',function($q)
						{
							$q->where('id', 36);
						})
						->whereHas('inChargeDepGet',function($q) use ($t_request)
						{
							$q->where('departament_id', $t_request->idDepartment)
								->where('module_id',36);
						})
						->whereHas('inChargeEntGet',function($q) use ($t_request)
						{
							$q->where('enterprise_id', $t_request->idEnterprise)
								->where('module_id',36);
						})
						->where('active',1)
						->where('notification',1)
						->get();*/
			/*$emails	= App\User::join('user_has_department','users.id','user_has_department.user_id')
						->join('user_has_modules','users.id','user_has_modules.user_id')
						->where('user_has_modules.module_id',36)
						->where('user_has_department.departament_id',$request->departmentid)
						->where('users.active',1)
						->where('users.notification',1)
						->get();*/
			/*$user 	=  App\User::find($request->userid);
			if ($emails != "")
			{
				foreach ($emails as $email)
				{
					$name 			= $email->name.' '.$email->last_name.' '.$email->scnd_last_name;
					$to 			= $email->email;
					$kind 			= "Compra";
					$status 		= "Revisar";
					$date 			= Carbon::now();
					$requestUser	= $user->name.' '.$user->last_name.' '.$user->scnd_last_name;
					$url 			= route('purchase.review.edit',['id'=>$folio]);
					$subject 		= "Solicitud por Revisar";
					Mail::to($to)->send(new App\Mail\Notificacion($name,$kind,$status,$date,$url,$subject,$requestUser));
				}
			}*/
			return redirect('administration/movements-accounts')->with('alert',$alert);
		}
	}

	public function unsentAdjustment(Request $request)
	{
		if (Auth::user()->module->where('id',171)->count()>0) 
		{
			$time  						= strtotime($request->date);
			$data						= App\Module::find($this->module_id);
			$t_request					= new App\RequestModel();
			$t_request->kind			= 11;
			$t_request->taxPayment		= 1;
			$t_request->fDate			= Carbon::now();
			$t_request->status			= 2;
			$t_request->idRequest		= $request->userid;
			$t_request->idElaborate		= Auth::user()->id;
			$t_request->save();
			$folio						= $t_request->folio;
			$kind						= $t_request->kind;

			/*$subtotales	= 0;
			$iva		= 0;
			$taxes 		= 0;
			$retentions = 0;

			for ($i=0;isset($request->tquanty) && $i < count($request->tquanty); $i++)
			{
				$tamountadditional 	= 'tamountadditional'.$i;
				$tnameamount 		= 'tnameamount'.$i;

				if (isset($request->$tamountadditional) && $request->$tamountadditional != "") 
				{
					for ($d=0; $d < count($request->$tamountadditional); $d++) 
					{ 
						if ($request->$tamountadditional[$d] != "") 
						{
							$taxes 	+= $request->$tamountadditional[$d];
						}
					}
				}

				$tamountretention = 'tamountretention'.$i;
				if (isset($request->$tamountretention) && $request->$tamountretention != "") 
				{
					for ($d=0; $d < count($request->$tamountretention); $d++) 
					{ 
						if ($request->$tamountretention[$d] != "") 
						{
							$retentions 	+= $request->$tamountretention[$d];
						}
					}
				}


				$subtotales	+= (($request->tquanty[$i] * $request->tprice[$i])-$request->tdiscount[$i]);
				$iva		+= $request->tiva[$i];
			}

			$total								= ($subtotales+$iva+$taxes)-$retentions;*/
			$t_adjustment						= new App\Adjustment();
			$t_adjustment->title				= $request->title;
			$t_adjustment->datetitle			= $request->datetitle != "" ? Carbon::createFromFormat('d-m-Y',$request->datetitle)->format('Y-m-d') : null;
			$t_adjustment->tax					= $request->tax;
			$t_adjustment->numberOrder			= $request->numberOrder;
			$t_adjustment->folio				= $request->folios;
			$t_adjustment->commentaries			= $request->commentaries;
			$t_adjustment->currency				= $request->type_currency;
			$t_adjustment->paymentDate			= $request->date !='' ? Carbon::createFromFormat('d-m-Y',$request->date)->format('Y-m-d') : null;
			$t_adjustment->subtotales			= $request->subtotal_adjustment;
			$t_adjustment->tax					= $request->iva_adjustment;
			$t_adjustment->additionalTax 		= $request->tax_adjustment;
			$t_adjustment->retention 			= $request->retention_adjustment;
			$t_adjustment->amount				= $request->total_adjustment;
			$t_adjustment->notes 				= $request->notes;
			$t_adjustment->idpaymentMethod		= $request->pay_mode;
			$t_adjustment->idEnterpriseOrigin	= $request->enterpriseid_origin;
			$t_adjustment->idEnterpriseDestiny	= $request->enterpriseid_destination;
			$t_adjustment->idAreaDestiny		= $request->areaid_destination;
			$t_adjustment->idDepartamentDestiny	= $request->departmentid_destination;
			$t_adjustment->idAccAccDestiny		= $request->accountid_destination;
			$t_adjustment->idProjectDestiny		= $request->projectid_destination;
			$t_adjustment->idFolio				= $folio;
			$t_adjustment->idKind				= $kind;
			$t_adjustment->save();

			$adjustment					= $t_adjustment->idadjustment;

			if (isset($request->folios_adjustment) && count($request->folios_adjustment)>0) 
			{
				for ($i=0; $i < count($request->folios_adjustment); $i++) 
				{ 
					$t_adjustmentfolios					= new App\AdjustmentFolios();
					$t_adjustmentfolios->idFolio		= $request->folios_adjustment[$i];
					$t_adjustmentfolios->idadjustment	= $adjustment;
					$t_adjustmentfolios->save();
				}
			}
			
			if (isset($request->realPath) && count($request->realPath)>0) 
			{
				for ($i=0; $i < count($request->realPath); $i++) 
				{ 
					if ($request->realPath[$i] != "") 
					{
						$documents					= new App\AdjustmentDocuments();
						$new_file_name = Files::rename($request->realPath[$i],$folio);
						$documents->path			= $new_file_name;
						$documents->idadjustment	= $adjustment;
						$documents->save();
					}
				}
			}
			/*
			for ($i=0; isset($request->tamount) && $i < count($request->tamount); $i++)
			{
				$t_detailAdjustment					= new App\AdjustmentDetail();
				$t_detailAdjustment->idadjustment	= $adjustment;
				$t_detailAdjustment->quantity		= $request->tquanty[$i];
				$t_detailAdjustment->unit			= $request->tunit[$i];
				$t_detailAdjustment->description	= $request->tdescr[$i];
				$t_detailAdjustment->unitPrice		= $request->tprice[$i];
				$t_detailAdjustment->tax			= $request->tiva[$i];
				$t_detailAdjustment->amount			= $request->tamount[$i];
				$t_detailAdjustment->typeTax		= $request->tivakind[$i];
				$t_detailAdjustment->subtotal		= $request->tquanty[$i] * $request->tprice[$i];
				$t_detailAdjustment->save();

				$idadjustmentDetail	= $t_detailAdjustment->idadjustmentDetail;
				$tamountadditional	= 'tamountadditional'.$i;
				$tnameamount		= 'tnameamount'.$i;
				if (isset($request->$tamountadditional) && $request->$tamountadditional != "") 
				{
					for ($d=0; $d < count($request->$tamountadditional); $d++) 
					{ 
						if ($request->$tamountadditional[$d] != "") 
						{
							$t_taxes						= new App\AdjustmentTaxes();
							$t_taxes->name					= $request->$tnameamount[$d];
							$t_taxes->amount				= $request->$tamountadditional[$d];
							$t_taxes->idadjustmentDetail	= $idadjustmentDetail;
							$t_taxes->save();
						}
					}
				}

				$tamountretention 	= 'tamountretention'.$i;
				$tnameretention 	= 'tnameretention'.$i;
				if (isset($request->$tamountretention) && $request->$tamountretention != "") 
				{
					for ($d=0; $d < count($request->$tamountretention); $d++) 
					{ 
						if ($request->$tamountretention[$d] != "") 
						{
							$t_retention						= new App\AdjustmentRetention();
							$t_retention->name					= $request->$tnameretention[$d];
							$t_retention->amount				= $request->$tamountretention[$d];
							$t_retention->idadjustmentDetail	= $idadjustmentDetail;
							$t_retention->save();
						}
					}
				}
			}
			*/
			$id = $folio;
			$alert = "swal('','".Lang::get("messages.request_saved")."', 'success')";
			
			return redirect()->route('movements-accounts.follow.edit',['id'=>$id])->with('alert',$alert);
		}
	}

	public function unsentFollowAdjustment(Request $request,$id)
	{
		if (Auth::user()->module->where('id',171)->count()>0) 
		{
			$time					= strtotime($request->date);
			$data					= App\Module::find($this->module_id);
			$t_request				= App\RequestModel::find($id);
			$t_request->kind		= 11;
			$t_request->taxPayment	= 1;
			$t_request->status		= 2;
			$t_request->idRequest	= $request->userid;
			$t_request->idElaborate	= Auth::user()->id;
			$t_request->save();
			$folio					= $t_request->folio;
			$kind					= $t_request->kind;

			/*$subtotales	= 0;
			$iva		= 0;
			$taxes 		= 0;
			$retentions = 0;

			for ($i=0;isset($request->tquanty) && $i < count($request->tquanty); $i++)
			{
				$tamountadditional 	= 'tamountadditional'.$i;
				$tnameamount 		= 'tnameamount'.$i;

				if (isset($request->$tamountadditional) && $request->$tamountadditional != "") 
				{
					for ($d=0; $d < count($request->$tamountadditional); $d++) 
					{ 
						if ($request->$tamountadditional[$d] != "") 
						{
							$taxes 	+= $request->$tamountadditional[$d];
						}
					}
				}

				$tamountretention = 'tamountretention'.$i;
				if (isset($request->$tamountretention) && $request->$tamountretention != "") 
				{
					for ($d=0; $d < count($request->$tamountretention); $d++) 
					{ 
						if ($request->$tamountretention[$d] != "") 
						{
							$retentions 	+= $request->$tamountretention[$d];
						}
					}
				}


				$subtotales	+= (($request->tquanty[$i] * $request->tprice[$i])-$request->tdiscount[$i]);
				$iva		+= $request->tiva[$i];
			}

			$total		= ($subtotales+$iva+$taxes)-$retentions;*/
			$adjustmentID	= App\Adjustment::where('idFolio',$folio)->first()->idadjustment;

			//isset(App\AdjustmentDetail::where('idadjustment',$adjustmentID)->first()->idadjustmentDetail) ? $detailID = App\AdjustmentDetail::where('idadjustment',$adjustmentID)->first()->idadjustmentDetail : $detailID = null;

			$t_adjustment						= App\Adjustment::find($adjustmentID);
			$t_adjustment->title				= $request->title;
			$datetitle							= $request->datetitle != "" ? Carbon::createFromFormat('d-m-Y',$request->datetitle)->format('Y-m-d') : null;
			$t_adjustment->datetitle			= $datetitle;
			$t_adjustment->numberOrder			= $request->numberOrder;
			$t_adjustment->folio				= $request->folios;
			$t_adjustment->commentaries			= $request->commentaries;
			$t_adjustment->currency				= $request->type_currency;
			$t_adjustment->paymentDate			= $request->date !='' ? Carbon::createFromFormat('d-m-Y',$request->date)->format('Y-m-d') : null;
			$t_adjustment->subtotales			= $request->subtotal_adjustment;
			$t_adjustment->tax					= $request->iva_adjustment;
			$t_adjustment->additionalTax 		= $request->tax_adjustment;
			$t_adjustment->retention 			= $request->retention_adjustment;
			$t_adjustment->amount				= $request->total_adjustment;
			$t_adjustment->notes 				= $request->notes;
			$t_adjustment->idpaymentMethod		= $request->pay_mode;
			$t_adjustment->idEnterpriseOrigin	= $request->enterpriseid_origin;
			$t_adjustment->idEnterpriseDestiny	= $request->enterpriseid_destination;
			$t_adjustment->idAreaDestiny		= $request->areaid_destination;
			$t_adjustment->idDepartamentDestiny	= $request->departmentid_destination;
			$t_adjustment->idAccAccDestiny		= $request->accountid_destination;
			$t_adjustment->idProjectDestiny		= $request->projectid_destination;
			$t_adjustment->idFolio				= $folio;
			$t_adjustment->idKind				= $kind;
			$t_adjustment->save();

			$adjustment					= $t_adjustment->idadjustment;

			$delFolios = App\AdjustmentFolios::where('idadjustment',$adjustmentID)->delete();

			if (isset($request->folios_adjustment) && count($request->folios_adjustment)>0) 
			{
				for ($i=0; $i < count($request->folios_adjustment); $i++) 
				{ 
					$t_adjustmentfolios					= new App\AdjustmentFolios();
					$t_adjustmentfolios->idFolio		= $request->folios_adjustment[$i];
					$t_adjustmentfolios->idadjustment	= $adjustmentID;
					$t_adjustmentfolios->save();
				}
			}
			
			if (isset($request->realPath) && count($request->realPath)>0) 
			{
				for ($i=0; $i < count($request->realPath); $i++) 
				{ 
					if ($request->realPath[$i] != "") 
					{
						$documents					= new App\AdjustmentDocuments();
						$new_file_name = Files::rename($request->realPath[$i],$folio);
						$documents->path			= $new_file_name;
						$documents->idadjustment	= $adjustmentID;
						$documents->save();
					}
				}
			}
			/*
			$deleteTaxes 	= App\AdjustmentTaxes::where('idadjustmentDetail',$detailID)->delete();
			$deleteRetentions = App\AdjustmentRetention::where('idadjustmentDetail',$detailID)->delete();
			$delete 		= App\AdjustmentDetail::where('idadjustment',$adjustmentID)->delete();

			for ($i=0; isset($request->tamount) && $i < count($request->tamount); $i++)
			{
				$t_detailAdjustment					= new App\AdjustmentDetail();
				$t_detailAdjustment->idadjustment	= $adjustmentID;
				$t_detailAdjustment->quantity		= $request->tquanty[$i];
				$t_detailAdjustment->unit			= $request->tunit[$i];
				$t_detailAdjustment->description	= $request->tdescr[$i];
				$t_detailAdjustment->unitPrice		= $request->tprice[$i];
				$t_detailAdjustment->tax			= $request->tiva[$i];
				$t_detailAdjustment->amount			= $request->tamount[$i];
				$t_detailAdjustment->typeTax		= $request->tivakind[$i];
				$t_detailAdjustment->subtotal		= $request->tquanty[$i] * $request->tprice[$i];
				$t_detailAdjustment->save();

				$idadjustmentDetail	= $t_detailAdjustment->idadjustmentDetail;
				$tamountadditional	= 'tamountadditional'.$i;
				$tnameamount		= 'tnameamount'.$i;
				if (isset($request->$tamountadditional) && $request->$tamountadditional != "") 
				{
					for ($d=0; $d < count($request->$tamountadditional); $d++) 
					{ 
						if ($request->$tamountadditional[$d] != "") 
						{
							$t_taxes						= new App\AdjustmentTaxes();
							$t_taxes->name					= $request->$tnameamount[$d];
							$t_taxes->amount				= $request->$tamountadditional[$d];
							$t_taxes->idadjustmentDetail	= $idadjustmentDetail;
							$t_taxes->save();
						}
					}
				}

				$tamountretention 	= 'tamountretention'.$i;
				$tnameretention 	= 'tnameretention'.$i;
				if (isset($request->$tamountretention) && $request->$tamountretention != "") 
				{
					for ($d=0; $d < count($request->$tamountretention); $d++) 
					{ 
						if ($request->$tamountretention[$d] != "") 
						{
							$t_retention						= new App\AdjustmentRetention();
							$t_retention->name					= $request->$tnameretention[$d];
							$t_retention->amount				= $request->$tamountretention[$d];
							$t_retention->idadjustmentDetail	= $idadjustmentDetail;
							$t_retention->save();
						}
					}
				}
			}*/

			$alert = "swal('','".Lang::get("messages.request_saved")."', 'success')";
			
			return redirect()->route('movements-accounts.follow.edit',['id'=>$id])->with('alert',$alert);
		}
	}

	public function updateFollowAdjustment(Request $request,$id)
	{
		if (Auth::user()->module->where('id',171)->count()>0) 
		{
			$time					= strtotime($request->date);
			$data					= App\Module::find($this->module_id);
			$t_request				= App\RequestModel::find($id);
			$t_request->kind		= 11;
			$t_request->taxPayment	= 1;
			$t_request->status		= 3;
			$t_request->idRequest	= $request->userid;
			$t_request->idElaborate	= Auth::user()->id;
			$t_request->save();
			$folio					= $t_request->folio;
			$kind					= $t_request->kind;

			/*$subtotales	= 0;
			$iva		= 0;
			$taxes 		= 0;
			$retentions = 0;

			for ($i=0;isset($request->tquanty) && $i < count($request->tquanty); $i++)
			{
				$tamountadditional 	= 'tamountadditional'.$i;
				$tnameamount 		= 'tnameamount'.$i;

				if (isset($request->$tamountadditional) && $request->$tamountadditional != "") 
				{
					for ($d=0; $d < count($request->$tamountadditional); $d++) 
					{ 
						if ($request->$tamountadditional[$d] != "") 
						{
							$taxes 	+= $request->$tamountadditional[$d];
						}
					}
				}

				$tamountretention = 'tamountretention'.$i;
				if (isset($request->$tamountretention) && $request->$tamountretention != "") 
				{
					for ($d=0; $d < count($request->$tamountretention); $d++) 
					{ 
						if ($request->$tamountretention[$d] != "") 
						{
							$retentions 	+= $request->$tamountretention[$d];
						}
					}
				}


				$subtotales	+= (($request->tquanty[$i] * $request->tprice[$i])-$request->tdiscount[$i]);
				$iva		+= $request->tiva[$i];
			}

			$total		= ($subtotales+$iva+$taxes)-$retentions;*/
			$adjustmentID	= App\Adjustment::where('idFolio',$folio)->first()->idadjustment;

			//isset(App\AdjustmentDetail::where('idadjustment',$adjustmentID)->first()->idadjustmentDetail) ? $detailID = App\AdjustmentDetail::where('idadjustment',$adjustmentID)->first()->idadjustmentDetail : $detailID = null;

			$t_adjustment						= App\Adjustment::find($adjustmentID);
			$t_adjustment->title				= $request->title;
			$datetitle							= $request->datetitle != "" ? Carbon::createFromFormat('d-m-Y',$request->datetitle)->format('Y-m-d') : null;
			$t_adjustment->datetitle			= $datetitle;
			$t_adjustment->numberOrder			= $request->numberOrder;
			$t_adjustment->folio				= $request->folios;
			$t_adjustment->commentaries			= $request->commentaries;
			$t_adjustment->currency				= $request->type_currency;
			$t_adjustment->subtotales			= $request->subtotal_adjustment;
			$t_adjustment->paymentDate			= $request->date !='' ? Carbon::createFromFormat('d-m-Y',$request->date)->format('Y-m-d') : null;
			$t_adjustment->tax					= $request->iva_adjustment;
			$t_adjustment->additionalTax 		= $request->tax_adjustment;
			$t_adjustment->retention 			= $request->retention_adjustment;
			$t_adjustment->amount				= $request->total_adjustment;
			$t_adjustment->notes 				= $request->notes;
			$t_adjustment->idpaymentMethod		= $request->pay_mode;
			$t_adjustment->idEnterpriseOrigin	= $request->enterpriseid_origin;
			$t_adjustment->idEnterpriseDestiny	= $request->enterpriseid_destination;
			$t_adjustment->idAreaDestiny		= $request->areaid_destination;
			$t_adjustment->idDepartamentDestiny	= $request->departmentid_destination;
			$t_adjustment->idAccAccDestiny		= $request->accountid_destination;
			$t_adjustment->idProjectDestiny		= $request->projectid_destination;
			$t_adjustment->idFolio				= $folio;
			$t_adjustment->idKind				= $kind;
			$t_adjustment->save();

			$adjustment					= $t_adjustment->idadjustment;

			$delFolios = App\AdjustmentFolios::where('idadjustment',$adjustmentID)->delete();
			if (isset($request->folios_adjustment) && count($request->folios_adjustment)>0) 
			{
				for ($i=0; $i < count($request->folios_adjustment); $i++) 
				{ 
					$t_adjustmentfolios					= new App\AdjustmentFolios();
					$t_adjustmentfolios->idFolio		= $request->folios_adjustment[$i];
					$t_adjustmentfolios->idadjustment	= $adjustmentID;
					$t_adjustmentfolios->save();
				}
			}
			
			if (isset($request->realPath) && count($request->realPath)>0) 
			{
				for ($i=0; $i < count($request->realPath); $i++) 
				{ 
					if ($request->realPath[$i] != "") 
					{
						$documents					= new App\AdjustmentDocuments();
						$new_file_name = Files::rename($request->realPath[$i],$folio);
						$documents->path			= $new_file_name;
						$documents->idadjustment	= $adjustmentID;
						$documents->save();
					}
				}
			}
			/*
			$deleteTaxes 	= App\AdjustmentTaxes::where('idadjustmentDetail',$detailID)->delete();
			$deleteRetentions = App\AdjustmentRetention::where('idadjustmentDetail',$detailID)->delete();
			$delete 		= App\AdjustmentDetail::where('idadjustment',$adjustmentID)->delete();

			for ($i=0; isset($request->tamount) && $i < count($request->tamount); $i++)
			{
				$t_detailAdjustment					= new App\AdjustmentDetail();
				$t_detailAdjustment->idadjustment	= $adjustmentID;
				$t_detailAdjustment->quantity		= $request->tquanty[$i];
				$t_detailAdjustment->unit			= $request->tunit[$i];
				$t_detailAdjustment->description	= $request->tdescr[$i];
				$t_detailAdjustment->unitPrice		= $request->tprice[$i];
				$t_detailAdjustment->tax			= $request->tiva[$i];
				$t_detailAdjustment->amount			= $request->tamount[$i];
				$t_detailAdjustment->typeTax		= $request->tivakind[$i];
				$t_detailAdjustment->subtotal		= $request->tquanty[$i] * $request->tprice[$i];
				$t_detailAdjustment->save();

				$idadjustmentDetail	= $t_detailAdjustment->idadjustmentDetail;
				$tamountadditional	= 'tamountadditional'.$i;
				$tnameamount		= 'tnameamount'.$i;
				if (isset($request->$tamountadditional) && $request->$tamountadditional != "") 
				{
					for ($d=0; $d < count($request->$tamountadditional); $d++) 
					{ 
						if ($request->$tamountadditional[$d] != "") 
						{
							$t_taxes						= new App\AdjustmentTaxes();
							$t_taxes->name					= $request->$tnameamount[$d];
							$t_taxes->amount				= $request->$tamountadditional[$d];
							$t_taxes->idadjustmentDetail	= $idadjustmentDetail;
							$t_taxes->save();
						}
					}
				}

				$tamountretention 	= 'tamountretention'.$i;
				$tnameretention 	= 'tnameretention'.$i;
				if (isset($request->$tamountretention) && $request->$tamountretention != "") 
				{
					for ($d=0; $d < count($request->$tamountretention); $d++) 
					{ 
						if ($request->$tamountretention[$d] != "") 
						{
							$t_retention						= new App\AdjustmentRetention();
							$t_retention->name					= $request->$tnameretention[$d];
							$t_retention->amount				= $request->$tamountretention[$d];
							$t_retention->idadjustmentDetail	= $idadjustmentDetail;
							$t_retention->save();
						}
					}
				}
			}*/

			/*$emails = App\User::whereHas('module',function($q)
						{
							$q->where('id', 36);
						})
						->whereHas('inChargeDepGet',function($q) use ($t_request)
						{
							$q->where('departament_id', $t_request->idDepartment)
								->where('module_id',36);
						})
						->whereHas('inChargeEntGet',function($q) use ($t_request)
						{
							$q->where('enterprise_id', $t_request->idEnterprise)
								->where('module_id',36);
						})
						->where('active',1)
						->where('notification',1)
						->get();*/
			/*$emails	= App\User::join('user_has_department','users.id','user_has_department.user_id')
						->join('user_has_modules','users.id','user_has_modules.user_id')
						->where('user_has_modules.module_id',36)
						->where('user_has_department.departament_id',$request->departmentid)
						->where('users.active',1)
						->where('users.notification',1)
						->get();*/
			/*$user 	=  App\User::find($request->userid);
			if ($emails != "")
			{
				foreach ($emails as $email)
				{
					$name 			= $email->name.' '.$email->last_name.' '.$email->scnd_last_name;
					$to 			= $email->email;
					$kind 			= "Compra";
					$status 		= "Revisar";
					$date 			= Carbon::now();
					$requestUser	= $user->name.' '.$user->last_name.' '.$user->scnd_last_name;
					$url 			= route('purchase.review.edit',['id'=>$folio]);
					$subject 		= "Solicitud por Revisar";
					Mail::to($to)->send(new App\Mail\Notificacion($name,$kind,$status,$date,$url,$subject,$requestUser));
				}
			}*/
			$alert = "swal('','".Lang::get("messages.request_sent")."', 'success')";
			return redirect('administration/movements-accounts')->with('alert',$alert);
		}
	}

	public function updateReviewAdjustment(Request $request,$id)
	{
		if(Auth::user()->module->where('id',150)->count()>0)
		{
			$data	= App\Module::find($this->module_id);
			$checkStatus    = App\RequestModel::find($id);

			if ($checkStatus->status == 4 || $checkStatus->status == 6) 
			{
				$alert = "swal('','".Lang::get("messages.request_already_ruled")."', 'error')";
			}
			else
			{
				$review	= App\RequestModel::find($id);
				if ($request->status == "4")
				{
					$review->status			= $request->status;
					$review->idCheck		= Auth::user()->id;
					$review->checkComment	= $request->checkCommentA;
					$review->reviewDate 	= Carbon::now();
					$review->save();

					$idadjustment 	= App\Adjustment::where('idFolio',$id)->first()->idadjustment;

					$t_adjustment							= App\Adjustment::find($idadjustment);
					$t_adjustment->idEnterpriseOriginR		= $t_adjustment->idEnterpriseOrigin;
					$t_adjustment->idEnterpriseDestinyR		= $request->enterpriseid_destination;
					$t_adjustment->idAreaDestinyR			= $request->areaid_destination;
					$t_adjustment->idDepartamentDestinyR	= $request->departmentid_destination;
					$t_adjustment->idAccAccDestinyR			= $request->accountid_destination;
					$t_adjustment->idProjectDestinyR		= $request->projectid_destination;
					$t_adjustment->save();

					/*if ($request->idLabels != "")
					{
						$review->labels()->detach();
						$review->labels()->attach($request->idLabels,array('request_kind'=>'1'));
					}
					$emails = App\User::whereHas('module',function($q)
						{
							$q->where('id', 37);
						})
						->whereHas('inChargeDepGet',function($q) use ($review)
						{
							$q->where('departament_id', $review->idDepartamentR)
								->where('module_id',37);
						})
						->whereHas('inChargeEntGet',function($q) use ($review)
						{
							$q->where('enterprise_id', $review->idEnterpriseR)
								->where('module_id',37);
						})
						->where('active',1)
						->where('notification',1)
						->get();*/
					/*$emails	= App\User::join('user_has_department','users.id','user_has_department.user_id')
								->join('user_has_modules','users.id','user_has_modules.user_id')
								->where('user_has_modules.module_id',37)
								->where('user_has_department.departament_id',$review->idDepartamentR)
								->where('users.active',1)
								->where('users.notification',1)
								->get();*/
					/*$user 	= App\User::find($review->idRequest);
					if ($emails != "")
					{
						foreach ($emails as $email)
						{
							$name 			= $email->name.' '.$email->last_name.' '.$email->scnd_last_name;
							$to 			= $email->email;
							$kind 			= "Compra";
							$status 		= "Autorizar";
							$date 			= Carbon::now();
							$url 			= route('purchase.authorization.edit',['id'=>$id]);
							$subject 		= "Solicitud por Autorizar";
							$requestUser	= $user->name.' '.$user->last_name.' '.$user->scnd_last_name;
							Mail::to($to)->send(new App\Mail\Notificacion($name,$kind,$status,$date,$url,$subject,$requestUser));
						}
					}*/
				}
				elseif ($request->status == "6")
				{
					$review->status			= $request->status;
					$review->idCheck		= Auth::user()->id;
					$review->checkComment	= $request->checkCommentR;
					$review->reviewDate 	= Carbon::now();
					$review->save();

					/*$emailRequest 			= "";

					if ($review->idElaborate == $review->idRequest) 
					{
						$emailRequest 	= App\User::where('id',$review->idElaborate)
										->where('notification',1)
										->get();
					}
					else
					{
						$emailRequest 	= App\User::where('id',$review->idElaborate)
										->orWhere('id',$review->idRequest)
										->where('notification',1)
										->get();
					}
					
					if ($emailRequest != "")
					{
						foreach ($emailRequest as $email)
						{
							$name 			= $email->name.' '.$email->last_name.' '.$email->scnd_last_name;
							$to 			= $email->email;
							$kind 			= "Compra";
							$status 		= "RECHAZADA";
							$date 			= Carbon::now();
							$url 			= route('purchase.follow.edit',['id'=>$id]);
							$subject 		= "Estado de Solicitud";
							$requestUser	= null;
							Mail::to($to)->send(new App\Mail\Notificacion($name,$kind,$status,$date,$url,$subject,$requestUser));
						}
					}*/
				}
				$alert = "swal('','".Lang::get("messages.request_updated")."', 'success')";
			}
			return searchRedirect(150, $alert, 'administration/movements-accounts');
		}
		else
		{
			return redirect('/');
		}
	}

	public function loan(Request $request)
	{
		if(Auth::user()->module->where('id',172)->count()>0)
		{
			$data  = App\Module::find($this->module_id);
			return view('administracion.movimientos_entre_cuentas.prestamo',
				[
					'id'        => $data['father'],
					'title'     => $data['name'],
					'details'   => $data['details'],
					'child_id'  => $this->module_id,
					'option_id' => 172
				]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function storeLoan(Request $request)
	{
		if(Auth::user()->module->where('id',172)->count()>0)
		{
			$data					= App\Module::find($this->module_id);
			$t_request				= new App\RequestModel();
			$t_request->kind		= 12;
			$t_request->taxPayment	= $request->fiscal;
			$t_request->fDate		= Carbon::now();
			$t_request->status		= 3;
			$t_request->idRequest	= $request->userid;
			$t_request->idElaborate	= Auth::user()->id;
			$t_request->save();
			$folio					= $t_request->folio;
			$kind					= $t_request->kind;

			$t_loan							= new App\LoanEnterprise();
			$t_loan->title					= $request->title;
			$t_loan->datetitle				= $request->datetitle != "" ? Carbon::createFromFormat('d-m-Y',$request->datetitle)->format('Y-m-d') : null;
			$t_loan->idFolio 				= $folio;
			$t_loan->idKind 				= $kind;
			$t_loan->tax					= $request->fiscal;
			$t_loan->amount					= $request->amount;
			$t_loan->currency				= $request->type_currency;
			$t_loan->paymentDate			= $request->date !='' ? Carbon::createFromFormat('d-m-Y',$request->date)->format('Y-m-d') : null;
			$t_loan->idpaymentMethod		= $request->pay_mode;
			$t_loan->idEnterpriseOrigin		= $request->enterpriseid_origin;
			$t_loan->idAccAccOrigin			= $request->accountid_origin;
			$t_loan->idEnterpriseDestiny	= $request->enterpriseid_destination;
			$t_loan->idAccAccDestiny		= $request->accountid_destination;
			$t_loan->save();

			$loan = $t_loan->idloanEnterprise;
			
			if (isset($request->realPath) && count($request->realPath)>0) 
			{
				for ($i=0; $i < count($request->realPath); $i++) 
				{ 
					if ($request->realPath[$i] != "") 
					{
						$documents						= new App\LoanEnterpriseDocuments();
						$new_file_name = Files::rename($request->realPath[$i],$folio);
						$documents->path				= $new_file_name;
						$documents->idloanEnterprise	= $loan;
						$documents->save();
					}
				}
			}

			$alert = "swal('','".Lang::get("messages.request_sent")."', 'success')";
			/*$emails = App\User::whereHas('module',function($q)
						{
							$q->where('id', 36);
						})
						->whereHas('inChargeDepGet',function($q) use ($t_request)
						{
							$q->where('departament_id', $t_request->idDepartment)
								->where('module_id',36);
						})
						->whereHas('inChargeEntGet',function($q) use ($t_request)
						{
							$q->where('enterprise_id', $t_request->idEnterprise)
								->where('module_id',36);
						})
						->where('active',1)
						->where('notification',1)
						->get();*/
			/*$emails	= App\User::join('user_has_department','users.id','user_has_department.user_id')
						->join('user_has_modules','users.id','user_has_modules.user_id')
						->where('user_has_modules.module_id',36)
						->where('user_has_department.departament_id',$request->departmentid)
						->where('users.active',1)
						->where('users.notification',1)
						->get();*/
			/*$user 	=  App\User::find($request->userid);
			if ($emails != "")
			{
				foreach ($emails as $email)
				{
					$name 			= $email->name.' '.$email->last_name.' '.$email->scnd_last_name;
					$to 			= $email->email;
					$kind 			= "Compra";
					$status 		= "Revisar";
					$date 			= Carbon::now();
					$requestUser	= $user->name.' '.$user->last_name.' '.$user->scnd_last_name;
					$url 			= route('purchase.review.edit',['id'=>$folio]);
					$subject 		= "Solicitud por Revisar";
					Mail::to($to)->send(new App\Mail\Notificacion($name,$kind,$status,$date,$url,$subject,$requestUser));
				}
			}*/
			return redirect('administration/movements-accounts')->with('alert',$alert);
		}
		else
		{
			return redirect('/error');
		}
	}

	public function unsentLoan(Request $request)
	{
		if(Auth::user()->module->where('id',172)->count()>0)
		{
			$data					= App\Module::find($this->module_id);
			$t_request				= new App\RequestModel();
			$t_request->kind		= 12;
			$t_request->taxPayment	= $request->fiscal;
			$t_request->fDate		= Carbon::now();
			$t_request->status		= 2;
			$t_request->idRequest	= $request->userid;
			$t_request->idElaborate	= Auth::user()->id;
			$t_request->save();
			$folio					= $t_request->folio;
			$kind					= $t_request->kind;

			$t_loan							= new App\LoanEnterprise();
			$t_loan->title					= $request->title;
			$t_loan->datetitle				= $request->datetitle != "" ? Carbon::createFromFormat('d-m-Y',$request->datetitle)->format('Y-m-d') : null;
			$t_loan->idFolio 				= $folio;
			$t_loan->idKind 				= $kind;
			$t_loan->tax					= $request->fiscal;
			$t_loan->amount					= $request->amount;
			$t_loan->currency				= $request->type_currency;
			$t_loan->paymentDate			= $request->date !='' ? Carbon::createFromFormat('d-m-Y',$request->date)->foramt('Y-m-d') : null;
			$t_loan->idpaymentMethod		= $request->pay_mode;
			$t_loan->idEnterpriseOrigin		= $request->enterpriseid_origin;
			$t_loan->idAccAccOrigin			= $request->accountid_origin;
			$t_loan->idEnterpriseDestiny	= $request->enterpriseid_destination;
			$t_loan->idAccAccDestiny		= $request->accountid_destination;
			$t_loan->save();

			$loan = $t_loan->idloanEnterprise;
			
			if (isset($request->realPath) && count($request->realPath)>0) 
			{
				for ($i=0; $i < count($request->realPath); $i++) 
				{ 
					if ($request->realPath[$i] != "") 
					{
						$documents						= new App\LoanEnterpriseDocuments();
						$new_file_name = Files::rename($request->realPath[$i],$folio);
						$documents->path				= $new_file_name;
						$documents->idloanEnterprise	= $loan;
						$documents->save();
					}
				}
			}
			
			$id		= $folio;
			$alert = "swal('','".Lang::get("messages.request_saved")."', 'success')";
			
			return redirect()->route('movements-accounts.follow.edit',['id'    =>$id])->with('alert',$alert);
		}
		else
		{
			return redirect('/error');
		}
	}

	public function unsentFollowLoan(Request $request,$id)
	{
		if(Auth::user()->module->where('id',172)->count()>0)
		{
			$data					= App\Module::find($this->module_id);
			$t_request				= App\RequestModel::find($id);
			$t_request->kind		= 12;
			$t_request->taxPayment	= $request->fiscal;
			$t_request->status		= 2;
			$t_request->idRequest	= $request->userid;
			$t_request->save();
			$folio					= $t_request->folio;
			$kind					= $t_request->kind;

			$loanID	= App\LoanEnterprise::where('idFolio',$folio)->first()->idloanEnterprise;
			
			$t_loan							= App\LoanEnterprise::find($loanID);
			$t_loan->title					= $request->title;
			$t_loan->datetitle				= $request->datetitle != '' ? Carbon::createFromFormat('d-m-Y',$request->datetitle)->format('Y-m-d') : null;
			$t_loan->idFolio 				= $folio;
			$t_loan->idKind 				= $kind;
			$t_loan->tax					= $request->fiscal;
			$t_loan->amount					= $request->amount;
			$t_loan->currency				= $request->type_currency;
			$t_loan->paymentDate			= $request->date !='' ? Carbon::createFromFormat('d-m-Y',$request->date)->format('Y-m-d') : null;
			$t_loan->idpaymentMethod		= $request->pay_mode;
			$t_loan->idEnterpriseOrigin		= $request->enterpriseid_origin;
			$t_loan->idAccAccOrigin			= $request->accountid_origin;
			$t_loan->idEnterpriseDestiny	= $request->enterpriseid_destination;
			$t_loan->idAccAccDestiny		= $request->accountid_destination;
			$t_loan->save();

			$loan = $t_loan->idloanEnterprise;
			
			if (isset($request->realPath) && count($request->realPath)>0) 
			{
				for ($i=0; $i < count($request->realPath); $i++) 
				{ 
					if ($request->realPath[$i] != "") 
					{
						$documents						= new App\LoanEnterpriseDocuments();
						$new_file_name = Files::rename($request->realPath[$i],$folio);
						$documents->path				= $new_file_name;
						$documents->idloanEnterprise	= $loan;
						$documents->save();
					}
				}
			}
			$alert = "swal('','".Lang::get("messages.request_saved")."', 'success')";
			
			return redirect()->route('movements-accounts.follow.edit',['id'=>$id])->with('alert',$alert);
		}
		else
		{
			return redirect('/error');
		}
	}

	public function updateFollowLoan(Request $request,$id)
	{
		if(Auth::user()->module->where('id',172)->count()>0)
		{
			$data					= App\Module::find($this->module_id);
			$t_request				= App\RequestModel::find($id);
			$t_request->kind		= 12;
			$t_request->taxPayment	= $request->fiscal;
			$t_request->status		= 3;
			$t_request->idRequest	= $request->userid;
			$t_request->save();
			$folio					= $t_request->folio;
			$kind					= $t_request->kind;

			$loanID	= App\LoanEnterprise::where('idFolio',$folio)->first()->idloanEnterprise;
			
			$t_loan							= App\LoanEnterprise::find($loanID);
			$t_loan->title					= $request->title;
			$t_loan->datetitle				= $request->datetitle != '' ? Carbon::createFromFormat('d-m-Y',$request->datetitle)->format('Y-m-d') : null;
			$t_loan->tax					= $request->fiscal;
			$t_loan->idFolio 				= $folio;
			$t_loan->idKind 				= $kind;
			$t_loan->amount					= $request->amount;
			$t_loan->currency				= $request->type_currency;
			$t_loan->paymentDate			= $request->date !='' ? Carbon::createFromFormat('d-m-Y',$request->date)->format('Y-m-d') : null;
			$t_loan->idpaymentMethod		= $request->pay_mode;
			$t_loan->idEnterpriseOrigin		= $request->enterpriseid_origin;
			$t_loan->idAccAccOrigin			= $request->accountid_origin;
			$t_loan->idEnterpriseDestiny	= $request->enterpriseid_destination;
			$t_loan->idAccAccDestiny		= $request->accountid_destination;
			$t_loan->save();

			$loan = $t_loan->idloanEnterprise;
			
			if (isset($request->realPath) && count($request->realPath)>0) 
			{
				for ($i=0; $i < count($request->realPath); $i++) 
				{ 
					if ($request->realPath[$i] != "") 
					{
						$documents						= new App\LoanEnterpriseDocuments();
						$new_file_name = Files::rename($request->realPath[$i],$folio);
						$documents->path				= $new_file_name;
						$documents->idloanEnterprise	= $loan;
						$documents->save();
					}
				}
			}
			$alert = "swal('','".Lang::get("messages.request_sent")."', 'success')";
			/*$emails = App\User::whereHas('module',function($q)
						{
							$q->where('id', 36);
						})
						->whereHas('inChargeDepGet',function($q) use ($t_request)
						{
							$q->where('departament_id', $t_request->idDepartment)
								->where('module_id',36);
						})
						->whereHas('inChargeEntGet',function($q) use ($t_request)
						{
							$q->where('enterprise_id', $t_request->idEnterprise)
								->where('module_id',36);
						})
						->where('active',1)
						->where('notification',1)
						->get();*/
			/*$emails	= App\User::join('user_has_department','users.id','user_has_department.user_id')
						->join('user_has_modules','users.id','user_has_modules.user_id')
						->where('user_has_modules.module_id',36)
						->where('user_has_department.departament_id',$request->departmentid)
						->where('users.active',1)
						->where('users.notification',1)
						->get();*/
			/*$user 	=  App\User::find($request->userid);
			if ($emails != "")
			{
				foreach ($emails as $email)
				{
					$name 			= $email->name.' '.$email->last_name.' '.$email->scnd_last_name;
					$to 			= $email->email;
					$kind 			= "Compra";
					$status 		= "Revisar";
					$date 			= Carbon::now();
					$requestUser	= $user->name.' '.$user->last_name.' '.$user->scnd_last_name;
					$url 			= route('purchase.review.edit',['id'=>$folio]);
					$subject 		= "Solicitud por Revisar";
					Mail::to($to)->send(new App\Mail\Notificacion($name,$kind,$status,$date,$url,$subject,$requestUser));
				}
			}*/
			return redirect('administration/movements-accounts')->with('alert',$alert);
		}
		else
		{
			return redirect('/error');
		}
	}

	public function updateReviewLoan(Request $request,$id)
	{
		if(Auth::user()->module->where('id',150)->count()>0)
		{
			$data	= App\Module::find($this->module_id);
			$checkStatus    = App\RequestModel::find($id);

			if ($checkStatus->status == 4 || $checkStatus->status == 6) 
			{
				$alert = "swal('','".Lang::get("messages.request_already_ruled")."', 'error')";
			}
			else
			{
				$review	= App\RequestModel::find($id);
				if ($request->status == "4")
				{

					$review->status			= $request->status;
					$review->idCheck		= Auth::user()->id;
					$review->checkComment	= $request->checkCommentA;
					$review->reviewDate 	= Carbon::now();
					$review->save();

					$idloanEnterprise 	= App\LoanEnterprise::where('idFolio',$id)->first()->idloanEnterprise;

					$t_groups							= App\LoanEnterprise::find($idloanEnterprise);
					$t_groups->idEnterpriseOriginR		= $request->enterpriseid_origin;
					$t_groups->idAccAccOriginR			= $request->accountid_origin;
					$t_groups->idEnterpriseDestinyR		= $request->enterpriseid_destination;
					$t_groups->idAccAccDestinyR			= $request->accountid_destination;
					$t_groups->save();

					/*if ($request->idLabels != "")
					{
						$review->labels()->detach();
						$review->labels()->attach($request->idLabels,array('request_kind'=>'1'));
					}
					$emails = App\User::whereHas('module',function($q)
						{
							$q->where('id', 37);
						})
						->whereHas('inChargeDepGet',function($q) use ($review)
						{
							$q->where('departament_id', $review->idDepartamentR)
								->where('module_id',37);
						})
						->whereHas('inChargeEntGet',function($q) use ($review)
						{
							$q->where('enterprise_id', $review->idEnterpriseR)
								->where('module_id',37);
						})
						->where('active',1)
						->where('notification',1)
						->get();*/
					/*$emails	= App\User::join('user_has_department','users.id','user_has_department.user_id')
								->join('user_has_modules','users.id','user_has_modules.user_id')
								->where('user_has_modules.module_id',37)
								->where('user_has_department.departament_id',$review->idDepartamentR)
								->where('users.active',1)
								->where('users.notification',1)
								->get();*/
					/*$user 	= App\User::find($review->idRequest);
					if ($emails != "")
					{
						foreach ($emails as $email)
						{
							$name 			= $email->name.' '.$email->last_name.' '.$email->scnd_last_name;
							$to 			= $email->email;
							$kind 			= "Compra";
							$status 		= "Autorizar";
							$date 			= Carbon::now();
							$url 			= route('purchase.authorization.edit',['id'=>$id]);
							$subject 		= "Solicitud por Autorizar";
							$requestUser	= $user->name.' '.$user->last_name.' '.$user->scnd_last_name;
							Mail::to($to)->send(new App\Mail\Notificacion($name,$kind,$status,$date,$url,$subject,$requestUser));
						}
					}*/
				}
				elseif ($request->status == "6")
				{
					$review->status			= $request->status;
					$review->idCheck		= Auth::user()->id;
					$review->checkComment	= $request->checkCommentR;
					$review->reviewDate 	= Carbon::now();
					$review->save();

					/*$emailRequest 			= "";

					if ($review->idElaborate == $review->idRequest) 
					{
						$emailRequest 	= App\User::where('id',$review->idElaborate)
										->where('notification',1)
										->get();
					}
					else
					{
						$emailRequest 	= App\User::where('id',$review->idElaborate)
										->orWhere('id',$review->idRequest)
										->where('notification',1)
										->get();
					}
					
					if ($emailRequest != "")
					{
						foreach ($emailRequest as $email)
						{
							$name 			= $email->name.' '.$email->last_name.' '.$email->scnd_last_name;
							$to 			= $email->email;
							$kind 			= "Compra";
							$status 		= "RECHAZADA";
							$date 			= Carbon::now();
							$url 			= route('purchase.follow.edit',['id'=>$id]);
							$subject 		= "Estado de Solicitud";
							$requestUser	= null;
							Mail::to($to)->send(new App\Mail\Notificacion($name,$kind,$status,$date,$url,$subject,$requestUser));
						}
					}*/
				}
				$alert = "swal('','".Lang::get("messages.request_updated")."', 'success')";
			}
			return searchRedirect(150, $alert, 'administration/movements-accounts');
		}
		else
		{
			return redirect('/');
		}
	}

	public function purchase(Request $request)
	{
		if(Auth::user()->module->where('id',173)->count()>0)
		{
			$data  = App\Module::find($this->module_id);
			return view('administracion.movimientos_entre_cuentas.compra',
				[
					'id'        => $data['father'],
					'title'     => $data['name'],
					'details'   => $data['details'],
					'child_id'  => $this->module_id,
					'option_id' => 173
				]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function storePurchase(Request $request)
	{
		if (Auth::user()->module->where('id',173)->count()>0) 
		{
			$time  						= strtotime($request->date);
			$data						= App\Module::find($this->module_id);
			$t_request					= new App\RequestModel();
			$t_request->kind			= 13;
			$t_request->taxPayment		= $request->fiscal;
			$t_request->fDate			= Carbon::now();
			$t_request->status			= 3;
			$t_request->idRequest		= $request->userid;
			$t_request->idElaborate		= Auth::user()->id;
			$t_request->save();
			$folio						= $t_request->folio;
			$kind						= $t_request->kind;

			$subtotales	= 0;
			$iva		= 0;
			$taxes 		= 0;
			$retentions = 0;

			for ($i=0;isset($request->tquanty) && $i < count($request->tquanty); $i++)
			{
				$tamountadditional 	= 'tamountadditional'.$i;
				$tnameamount 		= 'tnameamount'.$i;

				if (isset($request->$tamountadditional) && $request->$tamountadditional != "") 
				{
					for ($d=0; $d < count($request->$tamountadditional); $d++) 
					{ 
						if ($request->$tamountadditional[$d] != "") 
						{
							$taxes 	+= $request->$tamountadditional[$d];
						}
					}
				}

				$tamountretention = 'tamountretention'.$i;
				if (isset($request->$tamountretention) && $request->$tamountretention != "") 
				{
					for ($d=0; $d < count($request->$tamountretention); $d++) 
					{ 
						if ($request->$tamountretention[$d] != "") 
						{
							$retentions 	+= $request->$tamountretention[$d];
						}
					}
				}


				$subtotales	+= (($request->tquanty[$i] * $request->tprice[$i])-$request->tdiscount[$i]);
				$iva		+= $request->tiva[$i];
			}

			$total								= ($subtotales+$iva+$taxes)-$retentions;
			$t_purchase							= new App\PurchaseEnterprise();
			$t_purchase->title					= $request->title;
			$datetitle							= $request->datetitle != "" ? Carbon::createFromFormat('d-m-Y',$request->datetitle)->format('Y-m-d') : null;
			$t_purchase->datetitle				= $datetitle;
			$t_purchase->tax					= $request->tax;
			$t_purchase->numberOrder			= $request->numberOrder;
			$t_purchase->typeCurrency			= $request->type_currency;
			$t_purchase->notes 					= $request->notes;
			$t_purchase->paymentDate			= $request->date !='' ? Carbon::createFromFormat('d-m-Y',$request->date)->format('Y-m-d') : null;
			$t_purchase->subtotales				= $subtotales;
			$t_purchase->tax					= $iva;
			$t_purchase->amount					= $total;
			$t_purchase->idpaymentMethod		= $request->pay_mode;
			$t_purchase->idEnterpriseOrigin		= $request->enterpriseid_origin;
			$t_purchase->idAreaOrigin			= $request->areaid_origin;
			$t_purchase->idDepartamentOrigin	= $request->departmentid_origin;
			$t_purchase->idAccAccOrigin			= $request->accountid_origin;
			$t_purchase->idProjectOrigin		= $request->projectid_origin;
			$t_purchase->idEnterpriseDestiny	= $request->enterpriseid_destination;
			$t_purchase->idAccAccDestiny		= $request->accountid_destination;
			$t_purchase->idProjectDestiny		= $request->projectid_destination;
			$t_purchase->idbanksAccounts 		= $request->idbanksAccounts;
			$t_purchase->idFolio				= $folio;
			$t_purchase->idKind					= $kind;
			$t_purchase->save();

			$purchase                    = $t_purchase->idpurchaseEnterprise;
			
			if (isset($request->realPath) && count($request->realPath)>0) 
			{
				for ($i=0; $i < count($request->realPath); $i++) 
				{ 
					if ($request->realPath[$i] != "") 
					{
						$documents							= new App\PurchaseEnterpriseDocuments();
						$new_file_name						= Files::rename($request->realPath[$i],$folio);
						$documents->path					= $new_file_name;
						$documents->idpurchaseEnterprise	= $purchase;
						$documents->save();
					}
				}
			}

			for ($i=0; isset($request->tamount) && $i < count($request->tamount); $i++)
			{
				$t_detailPurchase						= new App\PurchaseEnterpriseDetail();
				$t_detailPurchase->idpurchaseEnterprise	= $purchase;
				$t_detailPurchase->quantity				= $request->tquanty[$i];
				$t_detailPurchase->unit					= $request->tunit[$i];
				$t_detailPurchase->description			= $request->tdescr[$i];
				$t_detailPurchase->unitPrice			= $request->tprice[$i];
				$t_detailPurchase->tax					= $request->tiva[$i];
				$t_detailPurchase->amount				= $request->tamount[$i];
				$t_detailPurchase->typeTax				= $request->tivakind[$i];
				$t_detailPurchase->subtotal				= $request->tquanty[$i] * $request->tprice[$i];
				$t_detailPurchase->save();

				$idPurchaseEnterpriseDetail	= $t_detailPurchase->idPurchaseEnterpriseDetail;
				$tamountadditional			= 'tamountadditional'.$i;
				$tnameamount				= 'tnameamount'.$i;
				if (isset($request->$tamountadditional) && $request->$tamountadditional != "") 
				{
					for ($d=0; $d < count($request->$tamountadditional); $d++) 
					{ 
						if ($request->$tamountadditional[$d] != "") 
						{
							$t_taxes								= new App\PurchaseEnterpriseTaxes();
							$t_taxes->name							= $request->$tnameamount[$d];
							$t_taxes->amount						= $request->$tamountadditional[$d];
							$t_taxes->idPurchaseEnterpriseDetail	= $idPurchaseEnterpriseDetail;
							$t_taxes->save();
						}
					}
				}

				$tamountretention	= 'tamountretention'.$i;
				$tnameretention		= 'tnameretention'.$i;
				if (isset($request->$tamountretention) && $request->$tamountretention != "") 
				{
					for ($d=0; $d < count($request->$tamountretention); $d++) 
					{ 
						if ($request->$tamountretention[$d] != "") 
						{
							$t_retention								= new App\PurchaseEnterpriseRetention();
							$t_retention->name							= $request->$tnameretention[$d];
							$t_retention->amount						= $request->$tamountretention[$d];
							$t_retention->idPurchaseEnterpriseDetail	= $idPurchaseEnterpriseDetail;
							$t_retention->save();
						}
					}
				}
			}
			$alert = "swal('','".Lang::get("messages.request_sent")."', 'success')";
			/*$emails = App\User::whereHas('module',function($q)
						{
							$q->where('id', 36);
						})
						->whereHas('inChargeDepGet',function($q) use ($t_request)
						{
							$q->where('departament_id', $t_request->idDepartment)
								->where('module_id',36);
						})
						->whereHas('inChargeEntGet',function($q) use ($t_request)
						{
							$q->where('enterprise_id', $t_request->idEnterprise)
								->where('module_id',36);
						})
						->where('active',1)
						->where('notification',1)
						->get();*/
			/*$emails	= App\User::join('user_has_department','users.id','user_has_department.user_id')
						->join('user_has_modules','users.id','user_has_modules.user_id')
						->where('user_has_modules.module_id',36)
						->where('user_has_department.departament_id',$request->departmentid)
						->where('users.active',1)
						->where('users.notification',1)
						->get();*/
			/*$user 	=  App\User::find($request->userid);
			if ($emails != "")
			{
				foreach ($emails as $email)
				{
					$name 			= $email->name.' '.$email->last_name.' '.$email->scnd_last_name;
					$to 			= $email->email;
					$kind 			= "Compra";
					$status 		= "Revisar";
					$date 			= Carbon::now();
					$requestUser	= $user->name.' '.$user->last_name.' '.$user->scnd_last_name;
					$url 			= route('purchase.review.edit',['id'=>$folio]);
					$subject 		= "Solicitud por Revisar";
					Mail::to($to)->send(new App\Mail\Notificacion($name,$kind,$status,$date,$url,$subject,$requestUser));
				}
			}*/
			return redirect('administration/movements-accounts')->with('alert',$alert);
		}
	}

	public function unsentPurchase(Request $request)
	{
		if (Auth::user()->module->where('id',173)->count()>0) 
		{
			$time  						= strtotime($request->date);
			$data						= App\Module::find($this->module_id);
			$t_request					= new App\RequestModel();
			$t_request->kind			= 13;
			$t_request->taxPayment		= $request->fiscal;
			$t_request->fDate			= Carbon::now();
			$t_request->status			= 2;
			$t_request->idRequest		= $request->userid;
			$t_request->idElaborate		= Auth::user()->id;
			$t_request->save();
			$folio						= $t_request->folio;
			$kind						= $t_request->kind;

			$subtotales	= 0;
			$iva		= 0;
			$taxes 		= 0;
			$retentions = 0;

			for ($i=0;isset($request->tquanty) && $i < count($request->tquanty); $i++)
			{
				$tamountadditional 	= 'tamountadditional'.$i;
				$tnameamount 		= 'tnameamount'.$i;

				if (isset($request->$tamountadditional) && $request->$tamountadditional != "") 
				{
					for ($d=0; $d < count($request->$tamountadditional); $d++) 
					{ 
						if ($request->$tamountadditional[$d] != "") 
						{
							$taxes 	+= $request->$tamountadditional[$d];
						}
					}
				}

				$tamountretention = 'tamountretention'.$i;
				if (isset($request->$tamountretention) && $request->$tamountretention != "") 
				{
					for ($d=0; $d < count($request->$tamountretention); $d++) 
					{ 
						if ($request->$tamountretention[$d] != "") 
						{
							$retentions 	+= $request->$tamountretention[$d];
						}
					}
				}


				$subtotales	+= (($request->tquanty[$i] * $request->tprice[$i])-$request->tdiscount[$i]);
				$iva		+= $request->tiva[$i];
			}

			$total								= ($subtotales+$iva+$taxes)-$retentions;
			$t_purchase							= new App\PurchaseEnterprise();
			$t_purchase->title					= $request->title;
			$datetitle							= $request->datetitle != '' ? Carbon::createFromFormat('d-m-Y',$request->datetitle)->format('Y-m-d') : null;
			$t_purchase->datetitle				= $datetitle;
			$t_purchase->tax					= $request->tax;
			$t_purchase->numberOrder			= $request->numberOrder;
			$t_purchase->typeCurrency			= $request->type_currency;
			$t_purchase->notes 					= $request->notes;
			$t_purchase->paymentDate			= $request->date !='' ? Carbon::createFromFormat('d-m-Y',$request->date)->format('Y-m-d') : null;
			$t_purchase->subtotales				= $subtotales;
			$t_purchase->tax					= $iva;
			$t_purchase->amount					= $total;
			$t_purchase->idpaymentMethod		= $request->pay_mode;
			$t_purchase->idEnterpriseOrigin		= $request->enterpriseid_origin;
			$t_purchase->idAreaOrigin			= $request->areaid_origin;
			$t_purchase->idDepartamentOrigin	= $request->departmentid_origin;
			$t_purchase->idAccAccOrigin			= $request->accountid_origin;
			$t_purchase->idProjectOrigin		= $request->projectid_origin;
			$t_purchase->idEnterpriseDestiny	= $request->enterpriseid_destination;
			$t_purchase->idAccAccDestiny		= $request->accountid_destination;
			$t_purchase->idProjectDestiny		= $request->projectid_destination;
			$t_purchase->idbanksAccounts 		= $request->idbanksAccounts;
			$t_purchase->idFolio				= $folio;
			$t_purchase->idKind					= $kind;
			$t_purchase->save();

			$purchase					= $t_purchase->idpurchaseEnterprise;
			
			if (isset($request->realPath) && count($request->realPath)>0) 
			{
				for ($i=0; $i < count($request->realPath); $i++) 
				{ 
					if ($request->realPath[$i] != "") 
					{
						$documents							= new App\PurchaseEnterpriseDocuments();
						$new_file_name = Files::rename($request->realPath[$i],$folio);
						$documents->path					= $new_file_name;
						$documents->idpurchaseEnterprise	= $purchase;
						$documents->save();
					}
				}
			}

			for ($i=0; isset($request->tamount) && $i < count($request->tamount); $i++)
			{
				$t_detailPurchase						= new App\PurchaseEnterpriseDetail();
				$t_detailPurchase->idpurchaseEnterprise	= $purchase;
				$t_detailPurchase->quantity				= $request->tquanty[$i];
				$t_detailPurchase->unit					= $request->tunit[$i];
				$t_detailPurchase->description			= $request->tdescr[$i];
				$t_detailPurchase->unitPrice			= $request->tprice[$i];
				$t_detailPurchase->tax					= $request->tiva[$i];
				$t_detailPurchase->amount				= $request->tamount[$i];
				$t_detailPurchase->typeTax				= $request->tivakind[$i];
				$t_detailPurchase->subtotal				= $request->tquanty[$i] * $request->tprice[$i];
				$t_detailPurchase->save();

				$idPurchaseEnterpriseDetail	= $t_detailPurchase->idPurchaseEnterpriseDetail;
				$tamountadditional			= 'tamountadditional'.$i;
				$tnameamount				= 'tnameamount'.$i;
				if (isset($request->$tamountadditional) && $request->$tamountadditional != "") 
				{
					for ($d=0; $d < count($request->$tamountadditional); $d++) 
					{ 
						if ($request->$tamountadditional[$d] != "") 
						{
							$t_taxes								= new App\PurchaseEnterpriseTaxes();
							$t_taxes->name							= $request->$tnameamount[$d];
							$t_taxes->amount						= $request->$tamountadditional[$d];
							$t_taxes->idPurchaseEnterpriseDetail	= $idPurchaseEnterpriseDetail;
							$t_taxes->save();
						}
					}
				}

				$tamountretention	= 'tamountretention'.$i;
				$tnameretention		= 'tnameretention'.$i;
				if (isset($request->$tamountretention) && $request->$tamountretention != "") 
				{
					for ($d=0; $d < count($request->$tamountretention); $d++) 
					{ 
						if ($request->$tamountretention[$d] != "") 
						{
							$t_retention								= new App\PurchaseEnterpriseRetention();
							$t_retention->name							= $request->$tnameretention[$d];
							$t_retention->amount						= $request->$tamountretention[$d];
							$t_retention->idPurchaseEnterpriseDetail	= $idPurchaseEnterpriseDetail;
							$t_retention->save();
						}
					}
				}
			}

			$id = $folio;
			$alert     = "swal('', '".Lang::get("messages.request_saved")."', 'success');";
			
			return redirect()->route('movements-accounts.follow.edit',['id'=>$id])->with('alert',$alert);
		}
	}

	public function unsentFollowPurchase(Request $request,$id)
	{
		if (Auth::user()->module->where('id',173)->count()>0) 
		{
			$time					= strtotime($request->date);
			$data					= App\Module::find($this->module_id);
			$t_request				= App\RequestModel::find($id);
			$t_request->kind		= 13;
			$t_request->taxPayment	= $request->fiscal;
			$t_request->status		= 2;
			$t_request->idRequest	= $request->userid;
			$t_request->idElaborate	= Auth::user()->id;
			$t_request->save();
			$folio					= $t_request->folio;
			$kind					= $t_request->kind;

			$subtotales	= 0;
			$iva		= 0;
			$taxes 		= 0;
			$retentions = 0;

			for ($i=0;isset($request->tquanty) && $i < count($request->tquanty); $i++)
			{
				$tamountadditional 	= 'tamountadditional'.$i;
				$tnameamount 		= 'tnameamount'.$i;

				if (isset($request->$tamountadditional) && $request->$tamountadditional != "") 
				{
					for ($d=0; $d < count($request->$tamountadditional); $d++) 
					{ 
						if ($request->$tamountadditional[$d] != "") 
						{
							$taxes 	+= $request->$tamountadditional[$d];
						}
					}
				}

				$tamountretention = 'tamountretention'.$i;
				if (isset($request->$tamountretention) && $request->$tamountretention != "") 
				{
					for ($d=0; $d < count($request->$tamountretention); $d++) 
					{ 
						if ($request->$tamountretention[$d] != "") 
						{
							$retentions 	+= $request->$tamountretention[$d];
						}
					}
				}


				$subtotales	+= (($request->tquanty[$i] * $request->tprice[$i])-$request->tdiscount[$i]);
				$iva		+= $request->tiva[$i];
			}

			$total		= ($subtotales+$iva+$taxes)-$retentions;
			$purchaseID	= App\PurchaseEnterprise::where('idFolio',$folio)->first()->idpurchaseEnterprise;

			isset(App\PurchaseEnterpriseDetail::where('idpurchaseEnterprise',$purchaseID)->first()->idPurchaseEnterpriseDetail) ? $detailID = App\PurchaseEnterpriseDetail::where('idpurchaseEnterprise',$purchaseID)->first()->idPurchaseEnterpriseDetail : $detailID = null;

			$t_purchase							= App\PurchaseEnterprise::find($purchaseID);
			$t_purchase->title					= $request->title;
			$t_purchase->datetitle				= $request->datetitle !="" ? Carbon::createFromFormat('d-m-Y',$request->datetitle)->format('Y-m-d') : null;
			$t_purchase->numberOrder			= $request->numberOrder;
			$t_purchase->typeCurrency			= $request->type_currency;
			$t_purchase->paymentDate			= $request->date !='' ? Carbon::createFromFormat('d-m-Y',$request->date)->format('Y-m-d') : null;
			$t_purchase->notes 					= $request->notes;
			$t_purchase->subtotales				= $subtotales;
			$t_purchase->tax					= $iva;
			$t_purchase->amount					= $total;
			$t_purchase->idpaymentMethod		= $request->pay_mode;
			$t_purchase->idEnterpriseOrigin		= $request->enterpriseid_origin;
			$t_purchase->idAreaOrigin			= $request->areaid_origin;
			$t_purchase->idDepartamentOrigin	= $request->departmentid_origin;
			$t_purchase->idAccAccOrigin			= $request->accountid_origin;
			$t_purchase->idProjectOrigin		= $request->projectid_origin;
			$t_purchase->idEnterpriseDestiny	= $request->enterpriseid_destination;
			$t_purchase->idAccAccDestiny		= $request->accountid_destination;
			$t_purchase->idProjectDestiny		= $request->projectid_destination;
			$t_purchase->idbanksAccounts 		= $request->idbanksAccounts;
			$t_purchase->idFolio				= $folio;
			$t_purchase->idKind					= $kind;
			$t_purchase->save();

			$purchaseID = $t_purchase->idpurchaseEnterprise;
			
			if (isset($request->realPath) && count($request->realPath)>0) 
			{
				for ($i=0; $i < count($request->realPath); $i++) 
				{ 
					if ($request->realPath[$i] != "") 
					{
						$documents							= new App\PurchaseEnterpriseDocuments();
						$new_file_name = Files::rename($request->realPath[$i],$folio);
						$documents->path					= $new_file_name;
						$documents->idpurchaseEnterprise	= $purchaseID;
						$documents->save();
					}
				}
			}

			$deleteTaxes		= App\PurchaseEnterpriseTaxes::where('idPurchaseEnterpriseDetail',$detailID)->delete();
			$deleteRetentions	= App\PurchaseEnterpriseRetention::where('idPurchaseEnterpriseDetail',$detailID)->delete();
			$delete				= App\PurchaseEnterpriseDetail::where('idpurchaseEnterprise',$purchaseID)->delete();

			for ($i=0; isset($request->tamount) && $i < count($request->tamount); $i++)
			{
				$t_detailPurchase						= new App\PurchaseEnterpriseDetail();
				$t_detailPurchase->idpurchaseEnterprise	= $purchaseID;
				$t_detailPurchase->quantity				= $request->tquanty[$i];
				$t_detailPurchase->unit					= $request->tunit[$i];
				$t_detailPurchase->description			= $request->tdescr[$i];
				$t_detailPurchase->unitPrice			= $request->tprice[$i];
				$t_detailPurchase->tax					= $request->tiva[$i];
				$t_detailPurchase->amount				= $request->tamount[$i];
				$t_detailPurchase->typeTax				= $request->tivakind[$i];
				$t_detailPurchase->subtotal				= $request->tquanty[$i] * $request->tprice[$i];
				$t_detailPurchase->save();

				$idPurchaseEnterpriseDetail	= $t_detailPurchase->idPurchaseEnterpriseDetail;
				$tamountadditional			= 'tamountadditional'.$i;
				$tnameamount				= 'tnameamount'.$i;
				if (isset($request->$tamountadditional) && $request->$tamountadditional != "") 
				{
					for ($d=0; $d < count($request->$tamountadditional); $d++) 
					{ 
						if ($request->$tamountadditional[$d] != "") 
						{
							$t_taxes								= new App\PurchaseEnterpriseTaxes();
							$t_taxes->name							= $request->$tnameamount[$d];
							$t_taxes->amount						= $request->$tamountadditional[$d];
							$t_taxes->idPurchaseEnterpriseDetail	= $idPurchaseEnterpriseDetail;
							$t_taxes->save();
						}
					}
				}

				$tamountretention	= 'tamountretention'.$i;
				$tnameretention		= 'tnameretention'.$i;
				if (isset($request->$tamountretention) && $request->$tamountretention != "") 
				{
					for ($d=0; $d < count($request->$tamountretention); $d++) 
					{ 
						if ($request->$tamountretention[$d] != "") 
						{
							$t_retention								= new App\PurchaseEnterpriseRetention();
							$t_retention->name							= $request->$tnameretention[$d];
							$t_retention->amount						= $request->$tamountretention[$d];
							$t_retention->idPurchaseEnterpriseDetail	= $idPurchaseEnterpriseDetail;
							$t_retention->save();
						}
					}
				}
			}

			$alert     = "swal('', '".Lang::get("messages.request_saved")."', 'success');";
			
			return redirect()->route('movements-accounts.follow.edit',['id'=>$id])->with('alert',$alert);
		}
	}

	public function updateFollowPurchase(Request $request,$id)
	{
		if (Auth::user()->module->where('id',173)->count()>0) 
		{
			$time					= strtotime($request->date);
			$data					= App\Module::find($this->module_id);
			$t_request				= App\RequestModel::find($id);
			$t_request->kind		= 13;
			$t_request->taxPayment	= $request->fiscal;
			$t_request->status		= 3;
			$t_request->idRequest	= $request->userid;
			$t_request->idElaborate	= Auth::user()->id;
			$t_request->save();
			$folio					= $t_request->folio;
			$kind					= $t_request->kind;

			$subtotales	= 0;
			$iva		= 0;
			$taxes 		= 0;
			$retentions = 0;

			for ($i=0;isset($request->tquanty) && $i < count($request->tquanty); $i++)
			{
				$tamountadditional 	= 'tamountadditional'.$i;
				$tnameamount 		= 'tnameamount'.$i;

				if (isset($request->$tamountadditional) && $request->$tamountadditional != "") 
				{
					for ($d=0; $d < count($request->$tamountadditional); $d++) 
					{ 
						if ($request->$tamountadditional[$d] != "") 
						{
							$taxes 	+= $request->$tamountadditional[$d];
						}
					}
				}

				$tamountretention = 'tamountretention'.$i;
				if (isset($request->$tamountretention) && $request->$tamountretention != "") 
				{
					for ($d=0; $d < count($request->$tamountretention); $d++) 
					{ 
						if ($request->$tamountretention[$d] != "") 
						{
							$retentions 	+= $request->$tamountretention[$d];
						}
					}
				}


				$subtotales	+= (($request->tquanty[$i] * $request->tprice[$i])-$request->tdiscount[$i]);
				$iva		+= $request->tiva[$i];
			}

			$total		= ($subtotales+$iva+$taxes)-$retentions;
			$purchaseID	= App\PurchaseEnterprise::where('idFolio',$folio)->first()->idpurchaseEnterprise;

			isset(App\PurchaseEnterpriseDetail::where('idpurchaseEnterprise',$purchaseID)->first()->idPurchaseEnterpriseDetail) ? $detailID = App\PurchaseEnterpriseDetail::where('idpurchaseEnterprise',$purchaseID)->first()->idPurchaseEnterpriseDetail : $detailID = null;

			$t_purchase							= App\PurchaseEnterprise::find($purchaseID);
			$t_purchase->title					= $request->title;
			$t_purchase->datetitle				= $request->datetitle !="" ? Carbon::createFromFormat('d-m-Y',$request->datetitle)->format('Y-m-d') : null;
			$t_purchase->numberOrder			= $request->numberOrder;
			$t_purchase->typeCurrency			= $request->type_currency;
 			$t_purchase->notes 					= $request->notes;
			$t_purchase->paymentDate			= $request->date !='' ? Carbon::createFromFormat('d-m-Y',$request->date)->format('Y-m-d') : null;
			$t_purchase->subtotales				= $subtotales;
			$t_purchase->tax					= $iva;
			$t_purchase->amount					= $total;
			$t_purchase->idpaymentMethod		= $request->pay_mode;
			$t_purchase->idEnterpriseOrigin		= $request->enterpriseid_origin;
			$t_purchase->idAreaOrigin			= $request->areaid_origin;
			$t_purchase->idDepartamentOrigin	= $request->departmentid_origin;
			$t_purchase->idAccAccOrigin			= $request->accountid_origin;
			$t_purchase->idProjectOrigin		= $request->projectid_origin;
			$t_purchase->idEnterpriseDestiny	= $request->enterpriseid_destination;
			$t_purchase->idAccAccDestiny		= $request->accountid_destination;
			$t_purchase->idProjectDestiny		= $request->projectid_destination;
			$t_purchase->idbanksAccounts 		= $request->idbanksAccounts;
			$t_purchase->idFolio				= $folio;
			$t_purchase->idKind					= $kind;
			$t_purchase->save();

			$purchaseID = $t_purchase->idpurchaseEnterprise;
			
			if (isset($request->realPath) && count($request->realPath)>0) 
			{
				for ($i=0; $i < count($request->realPath); $i++) 
				{ 
					if ($request->realPath[$i] != "") 
					{
						$documents					= new App\PurchaseEnterpriseDocuments();
						$new_file_name = Files::rename($request->realPath[$i],$folio);
						$documents->path			= $new_file_name;
						$documents->idpurchaseEnterprise	= $purchaseID;
						$documents->save();
					}
				}
			}

			foreach(App\PurchaseEnterpriseDetail::where('idpurchaseEnterprise',$purchaseID)->get() as $detailID)
			{
				App\PurchaseEnterpriseTaxes::where('idPurchaseEnterpriseDetail',$detailID->idPurchaseEnterpriseDetail)->delete();
				App\PurchaseEnterpriseRetention::where('idPurchaseEnterpriseDetail',$detailID->idPurchaseEnterpriseDetail)->delete();
				$detailID->delete();
			}

			for ($i=0; isset($request->tamount) && $i < count($request->tamount); $i++)
			{
				$t_detailPurchase						= new App\PurchaseEnterpriseDetail();
				$t_detailPurchase->idpurchaseEnterprise	= $purchaseID;
				$t_detailPurchase->quantity				= $request->tquanty[$i];
				$t_detailPurchase->unit					= $request->tunit[$i];
				$t_detailPurchase->description			= $request->tdescr[$i];
				$t_detailPurchase->unitPrice			= $request->tprice[$i];
				$t_detailPurchase->tax					= $request->tiva[$i];
				$t_detailPurchase->amount				= $request->tamount[$i];
				$t_detailPurchase->typeTax				= $request->tivakind[$i];
				$t_detailPurchase->subtotal				= $request->tquanty[$i] * $request->tprice[$i];
				$t_detailPurchase->save();

				$idPurchaseEnterpriseDetail    = $t_detailPurchase->idPurchaseEnterpriseDetail;
				$tamountadditional	= 'tamountadditional'.$i;
				$tnameamount		= 'tnameamount'.$i;
				if (isset($request->$tamountadditional) && $request->$tamountadditional != "") 
				{
					for ($d=0; $d < count($request->$tamountadditional); $d++) 
					{ 
						if ($request->$tamountadditional[$d] != "") 
						{
							$t_taxes						= new App\PurchaseEnterpriseTaxes();
							$t_taxes->name					= $request->$tnameamount[$d];
							$t_taxes->amount				= $request->$tamountadditional[$d];
							$t_taxes->idPurchaseEnterpriseDetail	= $idPurchaseEnterpriseDetail;
							$t_taxes->save();
						}
					}
				}

				$tamountretention 	= 'tamountretention'.$i;
				$tnameretention 	= 'tnameretention'.$i;
				if (isset($request->$tamountretention) && $request->$tamountretention != "") 
				{
					for ($d=0; $d < count($request->$tamountretention); $d++) 
					{ 
						if ($request->$tamountretention[$d] != "") 
						{
							$t_retention						= new App\PurchaseEnterpriseRetention();
							$t_retention->name					= $request->$tnameretention[$d];
							$t_retention->amount				= $request->$tamountretention[$d];
							$t_retention->idPurchaseEnterpriseDetail	= $idPurchaseEnterpriseDetail;
							$t_retention->save();
						}
					}
				}
			}

			/*$emails = App\User::whereHas('module',function($q)
						{
							$q->where('id', 36);
						})
						->whereHas('inChargeDepGet',function($q) use ($t_request)
						{
							$q->where('departament_id', $t_request->idDepartment)
								->where('module_id',36);
						})
						->whereHas('inChargeEntGet',function($q) use ($t_request)
						{
							$q->where('enterprise_id', $t_request->idEnterprise)
								->where('module_id',36);
						})
						->where('active',1)
						->where('notification',1)
						->get();*/
			/*$emails	= App\User::join('user_has_department','users.id','user_has_department.user_id')
						->join('user_has_modules','users.id','user_has_modules.user_id')
						->where('user_has_modules.module_id',36)
						->where('user_has_department.departament_id',$request->departmentid)
						->where('users.active',1)
						->where('users.notification',1)
						->get();*/
			/*$user 	=  App\User::find($request->userid);
			if ($emails != "")
			{
				foreach ($emails as $email)
				{
					$name 			= $email->name.' '.$email->last_name.' '.$email->scnd_last_name;
					$to 			= $email->email;
					$kind 			= "Compra";
					$status 		= "Revisar";
					$date 			= Carbon::now();
					$requestUser	= $user->name.' '.$user->last_name.' '.$user->scnd_last_name;
					$url 			= route('purchase.review.edit',['id'=>$folio]);
					$subject 		= "Solicitud por Revisar";
					Mail::to($to)->send(new App\Mail\Notificacion($name,$kind,$status,$date,$url,$subject,$requestUser));
				}
			}*/
			
			$alert     = "swal('', '".Lang::get("messages.request_saved")."', 'success');";
			return redirect('administration/movements-accounts')->with('alert',$alert);
		}
	}

	public function updateReviewPurchase(Request $request,$id)
	{
		if(Auth::user()->module->where('id',150)->count()>0)
		{
			$data	= App\Module::find($this->module_id);
			$checkStatus    = App\RequestModel::find($id);

			if ($checkStatus->status == 4 || $checkStatus->status == 6) 
			{
				$alert = "swal('', '".Lang::get("messages.request_already_ruled")."', 'error');";
			}
			else
			{
				$review	= App\RequestModel::find($id);
				if ($request->status == "4")
				{
					for ($i=0; $i < count($request->t_idPurchaseEnterpriseDetail); $i++) 
					{ 
						$idLabelsAssign = 'idLabelsAssign'.$i;
						if ($request->$idLabelsAssign != "") 
						{
							for ($d=0; $d < count($request->$idLabelsAssign); $d++) 
							{ 
								$labelPurchase								= new App\PurchaseEnterpriseDetailLabel();
								$labelPurchase->idlabels					= $request->$idLabelsAssign[$d];
								$labelPurchase->idPurchaseEnterpriseDetail	= $request->t_idPurchaseEnterpriseDetail[$i];
								$labelPurchase->save();
							}
						}
					}

					$review->status			= $request->status;
					$review->idCheck		= Auth::user()->id;
					$review->checkComment	= $request->checkCommentA;
					$review->reviewDate 	= Carbon::now();
					$review->save();

					$idpurchaseEnterprise 	= App\PurchaseEnterprise::where('idFolio',$id)->first()->idpurchaseEnterprise;

					$t_adjustment							= App\PurchaseEnterprise::find($idpurchaseEnterprise);
					$t_adjustment->idEnterpriseOriginR		= $request->enterpriseid_origin;
					$t_adjustment->idAreaOriginR			= $request->areaid_origin;
					$t_adjustment->idDepartamentOriginR		= $request->departmentid_origin;
					$t_adjustment->idAccAccOriginR			= $request->accountid_origin;
					$t_adjustment->idProjectOriginR			= $request->projectid_origin;
					$t_adjustment->idEnterpriseDestinyR		= $request->enterpriseid_destination;
					$t_adjustment->idAccAccDestinyR			= $request->accountid_destination;
					$t_adjustment->idProjectDestinyR		= $request->projectid_destination;
					$t_adjustment->save();

					/*if ($request->idLabels != "")
					{
						$review->labels()->detach();
						$review->labels()->attach($request->idLabels,array('request_kind'=>'1'));
					}
					$emails = App\User::whereHas('module',function($q)
						{
							$q->where('id', 37);
						})
						->whereHas('inChargeDepGet',function($q) use ($review)
						{
							$q->where('departament_id', $review->idDepartamentR)
								->where('module_id',37);
						})
						->whereHas('inChargeEntGet',function($q) use ($review)
						{
							$q->where('enterprise_id', $review->idEnterpriseR)
								->where('module_id',37);
						})
						->where('active',1)
						->where('notification',1)
						->get();*/
					/*$emails	= App\User::join('user_has_department','users.id','user_has_department.user_id')
								->join('user_has_modules','users.id','user_has_modules.user_id')
								->where('user_has_modules.module_id',37)
								->where('user_has_department.departament_id',$review->idDepartamentR)
								->where('users.active',1)
								->where('users.notification',1)
								->get();*/
					/*$user 	= App\User::find($review->idRequest);
					if ($emails != "")
					{
						foreach ($emails as $email)
						{
							$name 			= $email->name.' '.$email->last_name.' '.$email->scnd_last_name;
							$to 			= $email->email;
							$kind 			= "Compra";
							$status 		= "Autorizar";
							$date 			= Carbon::now();
							$url 			= route('purchase.authorization.edit',['id'=>$id]);
							$subject 		= "Solicitud por Autorizar";
							$requestUser	= $user->name.' '.$user->last_name.' '.$user->scnd_last_name;
							Mail::to($to)->send(new App\Mail\Notificacion($name,$kind,$status,$date,$url,$subject,$requestUser));
						}
					}*/
				}
				elseif ($request->status == "6")
				{
					$review->status			= $request->status;
					$review->idCheck		= Auth::user()->id;
					$review->checkComment	= $request->checkCommentR;
					$review->reviewDate 	= Carbon::now();
					$review->save();

					/*$emailRequest 			= "";

					if ($review->idElaborate == $review->idRequest) 
					{
						$emailRequest 	= App\User::where('id',$review->idElaborate)
										->where('notification',1)
										->get();
					}
					else
					{
						$emailRequest 	= App\User::where('id',$review->idElaborate)
										->orWhere('id',$review->idRequest)
										->where('notification',1)
										->get();
					}
					
					if ($emailRequest != "")
					{
						foreach ($emailRequest as $email)
						{
							$name 			= $email->name.' '.$email->last_name.' '.$email->scnd_last_name;
							$to 			= $email->email;
							$kind 			= "Compra";
							$status 		= "RECHAZADA";
							$date 			= Carbon::now();
							$url 			= route('purchase.follow.edit',['id'=>$id]);
							$subject 		= "Estado de Solicitud";
							$requestUser	= null;
							Mail::to($to)->send(new App\Mail\Notificacion($name,$kind,$status,$date,$url,$subject,$requestUser));
						}
					}*/
				}
				$alert = "swal('', '".Lang::get("messages.request_updated")."', 'success');";
			}
			return searchRedirect(150, $alert, 'administration/movements-accounts');
		}
		else
		{
			return redirect('/');
		}
	}

	public function groups(Request $request)
	{
		if(Auth::user()->module->where('id',174)->count()>0)
		{
			$data  = App\Module::find($this->module_id);
			return view('administracion.movimientos_entre_cuentas.grupos',
				[
					'id'        => $data['father'],
					'title'     => $data['name'],
					'details'   => $data['details'],
					'child_id'  => $this->module_id,
					'option_id' => 174
				]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function storeGroups(Request $request)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			if ($request->fiscal == 1 && ($request->rfc != '' || $request->rfc == '' || $request->rfc == null)) 
			{
				$rfc = $request->rfc;
			}	
			elseif ($request->fiscal == 1 && (isset($request->idProvider) && $request->idProvider != '') && (isset($request->rfc) && $request->rfc == '')) 
			{
				$alert	= "swal('', 'Lo sentimos ocurrió un problema, la solicitud Fiscal tiene que llevar RFC obligatorio.', 'error');";
				$data	= App\Module::find($this->module_id);
				return view('administracion.movimientos_entre_cuentas.grupos',
				[
					'id'		=> $data['father'],
					'title'		=> $data['name'],
					'details'	=> $data['details'],
					'child_id' 	=> $this->module_id,
					'option_id' => 149,
					'alert' 	=> $alert
				]);
			}
			elseif($request->fiscal == 0 && $request->rfc != '')
			{
				$rfc = $request->rfc;
			}
			elseif($request->fiscal == 0 && $request->rfc == '')
			{
				$rfc = 'XAXX1'.str_pad(App\Provider::where('rfc','like','%XAXX1%')->count(), 8, "0", STR_PAD_LEFT);
			}

			$data						= App\Module::find($this->module_id);
			$t_request					= new App\RequestModel();
			$t_request->kind			= 14;
			$t_request->taxPayment		= $request->fiscal;
			$t_request->fDate			= Carbon::now();
			$t_request->PaymentDate		= $request->date != null ? Carbon::createFromFormat('d-m-Y',$request->date)->format('Y-m-d') : null;
			$t_request->status			= 3;
			$t_request->idRequest		= $request->userid;
			$t_request->idElaborate		= Auth::user()->id;
			$t_request->save();
			$folio						= $t_request->folio;
			$kind						= $t_request->kind;
			$provider_has_banks_id 		= NULL;
			$provider_data_id 			= $request->provider_data_id;

			if ($request->prov == "nuevo")
			{
				$t_provider_data 			= new App\ProviderData();
				$t_provider_data->users_id 	= Auth::user()->id;
				$t_provider_data->save();

				$t_provider					= new App\Provider();
				$t_provider->businessName	= $request->reason;
				$t_provider->beneficiary	= $request->beneficiary;
				$t_provider->phone			= $request->phone;
				$t_provider->rfc			= $rfc;
				$t_provider->contact		= $request->contact;
				$t_provider->commentaries	= $request->other;
				$t_provider->status			= 2;
				$t_provider->users_id		= Auth::user()->id;
				$t_provider->address		= $request->address;
				$t_provider->number			= $request->number;
				$t_provider->colony			= $request->colony;
				$t_provider->postalCode		= $request->cp;
				$t_provider->city			= $request->city;
				$t_provider->state_idstate	= $request->state;
				$t_provider->provider_data_id	= $t_provider_data->id;
				$t_provider->save();
				$provider_id				= $t_provider->idProvider;
				$provider_data_id 			= $t_provider->provider_data_id;

				if(isset($request->providerBank))
				{
					for ($i=0; $i < count($request->providerBank); $i++)
					{
						$t_providerBank							= new App\ProviderBanks;
						$t_providerBank->provider_idProvider	= $provider_id;
						$t_providerBank->banks_idBanks			= $request->bank[$i];
						$t_providerBank->alias 					= $request->alias[$i];
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
						if ($request->pay_mode == 1) 
						{
							if ($request->checked[$i] == 1) 
							{
								$provider_has_banks_id = $t_providerBank->id;
							}
						}
					}
				}
			}
			elseif($request->prov == "buscar")
			{
				if (isset($request->edit))
				{
					$oldProvider			= App\Provider::find($request->idProvider);
					if($oldProvider->status==0)
					{
						$oldProvider->businessName	= $request->reason;
						$oldProvider->beneficiary	= $request->beneficiary;
						$oldProvider->phone			= $request->phone;
						$oldProvider->rfc			= $rfc;
						$oldProvider->contact		= $request->contact;
						$oldProvider->commentaries	= $request->other;
						$oldProvider->status		= 0;
						$oldProvider->users_id		= Auth::user()->id;
						$oldProvider->address		= $request->address;
						$oldProvider->number		= $request->number;
						$oldProvider->colony		= $request->colony;
						$oldProvider->postalCode	= $request->cp;
						$oldProvider->city			= $request->city;
						$oldProvider->state_idstate	= $request->state;
						$oldProvider->save();
						$provider_id				= $oldProvider->idProvider;

						if(isset($request->providerBank))
						{
							for ($i=0; $i < count($request->providerBank); $i++)
							{
								if ($request->providerBank[$i] == "x") 
								{	
									$t_providerBank							= new App\ProviderBanks;
									$t_providerBank->provider_idProvider	= $provider_id;
									$t_providerBank->banks_idBanks			= $request->bank[$i];
									$t_providerBank->alias 					= $request->alias[$i];
									$t_providerBank->account				= $request->account[$i];
									$t_providerBank->branch					= $request->branch_office[$i];
									$t_providerBank->reference				= $request->reference[$i];
									$t_providerBank->clabe					= $request->clabe[$i];
									$t_providerBank->currency				= $request->currency[$i];
									$t_providerBank->agreement				= $request->agreement[$i];
									$t_providerBank->iban 					= $request->iban[$i];
									$t_providerBank->bic_swift 				= $request->bic_swift[$i];
									$t_providerBank->provider_data_id 		= $oldProvider->provider_data_id;
									$t_providerBank->save();

									if ($request->pay_mode == 1) 
									{
										if ($request->checked[$i] == 1) 
										{
											$provider_has_banks_id = $t_providerBank->id;
										}
									}
								}
								else
								{
									$t_providerBank	= App\ProviderBanks::find($request->providerBank[$i]);
									if ($request->pay_mode == 1) 
									{
										if ($request->checked[$i] == 1) 
										{
											$provider_has_banks_id = $t_providerBank->id;
										}
									}
								}
							}
						}
					}
					else
					{
						$oldProvider->status		= 1;
						$oldProvider->save();
						$provider_data_id 			= $oldProvider->provider_data_id;

						$t_provider					= new App\Provider();
						$t_provider->businessName	= $request->reason;
						$t_provider->beneficiary	= $request->beneficiary;
						$t_provider->phone			= $request->phone;
						$t_provider->rfc			= $rfc;
						$t_provider->contact		= $request->contact;
						$t_provider->commentaries	= $request->other;
						$t_provider->status			= 2;
						$t_provider->users_id		= Auth::user()->id;
						$t_provider->address		= $request->address;
						$t_provider->number			= $request->number;
						$t_provider->colony			= $request->colony;
						$t_provider->postalCode		= $request->cp;
						$t_provider->city			= $request->city;
						$t_provider->state_idstate	= $request->state;
						$t_provider->provider_data_id	= $provider_data_id;
						$t_provider->save();
						$provider_id				= $t_provider->idProvider;
						$provider_data_id 			= $t_provider->provider_data_id;
						if(isset($request->providerBank))
						{
							for ($i=0; $i < count($request->providerBank); $i++)
							{
								if ($request->providerBank[$i] == "x") 
								{	
									$t_providerBank							= new App\ProviderBanks;
									$t_providerBank->provider_idProvider	= $provider_id;
									$t_providerBank->banks_idBanks			= $request->bank[$i];
									$t_providerBank->alias 					= $request->alias[$i];
									$t_providerBank->account				= $request->account[$i];
									$t_providerBank->branch					= $request->branch_office[$i];
									$t_providerBank->reference				= $request->reference[$i];
									$t_providerBank->clabe					= $request->clabe[$i];
									$t_providerBank->currency				= $request->currency[$i];
									$t_providerBank->agreement				= $request->agreement[$i];
									$t_providerBank->iban 					= $request->iban[$i];
									$t_providerBank->bic_swift 				= $request->bic_swift[$i];
									$t_providerBank->provider_data_id 		= $oldProvider->provider_data_id;
									$t_providerBank->save();

									if ($request->pay_mode == 1) 
									{
										if ($request->checked[$i] == 1) 
										{
											$provider_has_banks_id = $t_providerBank->id;
										}
									}
								}
								else
								{
									$t_providerBank	= App\ProviderBanks::find($request->providerBank[$i]);
									if ($request->pay_mode == 1) 
									{
										if ($request->checked[$i] == 1) 
										{
											$provider_has_banks_id = $t_providerBank->id;
										}
									}
								}
							}
						}
					}
				}
				else
				{
					$provider_id			= $request->idProvider;
					$provider_has_banks_id 	= $request->provider_has_banks_id;
				}
			}

			$subtotales	= 0;
			$iva		= 0;
			$taxes 		= 0;
			$retentions = 0;

			if(isset($request->tquanty) && count($request->tquanty)>0)
			{
				for ($i=0; $i < count($request->tquanty); $i++)
				{
					$tamountadditional 	= 'tamountadditional'.$i;
					$tnameamount 		= 'tnameamount'.$i;

					if (isset($request->$tamountadditional) && $request->$tamountadditional != "") 
					{
						for ($d=0; $d < count($request->$tamountadditional); $d++) 
						{ 
							if ($request->$tamountadditional[$d] != "") 
							{
								$taxes 	+= $request->$tamountadditional[$d];
							}
						}
					}

					$tamountretention = 'tamountretention'.$i;
					if (isset($request->$tamountretention) && $request->$tamountretention != "") 
					{
						for ($d=0; $d < count($request->$tamountretention); $d++) 
						{ 
							if ($request->$tamountretention[$d] != "") 
							{
								$retentions 	+= $request->$tamountretention[$d];
							}
						}
					}


					$subtotales	+= (($request->tquanty[$i] * $request->tprice[$i])-$request->tdiscount[$i]);
					$iva		+= $request->tiva[$i];
				}
			}	

			$total								= ($subtotales+$iva+$taxes)-$retentions;
			$t_groups							= new App\Groups();
			$t_groups->title					= $request->title;
			$t_groups->datetitle				= $request->datetitle != null ? Carbon::createFromFormat('d-m-Y',$request->datetitle)->format('Y-m-d') : null;
			$t_groups->numberOrder				= $request->numberOrder;
			$t_groups->operationType			= $request->typeOperation;
			$t_groups->amountMovement			= $request->amountTotal;
			$t_groups->amountRetake				= $request->amountRetake;
			$t_groups->commission				= $request->commission;
			$t_groups->reference				= $request->referencePurchase;
			$t_groups->typeCurrency				= $request->type_currency;
			$t_groups->paymentDate				= $request->date !=null ? Carbon::createFromFormat('d-m-Y',$request->date)->format('Y-m-d') : null;
			$t_groups->idpaymentMethod			= $request->pay_mode;
			$t_groups->statusBill				= $request->status_bill;
			$t_groups->subtotales				= $subtotales;
			$t_groups->tax						= $iva;
			$t_groups->amount					= $total;
			$t_groups->idFolio					= $folio;
			$t_groups->idKind					= $kind;
			$t_groups->idEnterpriseOrigin		= $request->enterpriseid_origin;
			$t_groups->idDepartamentOrigin		= $request->departmentid_origin;
			$t_groups->idAreaOrigin				= $request->areaid_origin;
			$t_groups->idProjectOrigin			= $request->projectid_origin;
			$t_groups->idAccAccOrigin			= $request->accountid_origin;
			$t_groups->idEnterpriseDestiny		= $request->enterpriseid_destination;
			$t_groups->idAccAccDestiny			= $request->accountid_destination;
			$t_groups->idProvider				= $provider_id;
			$t_groups->provider_has_banks_id	= $provider_has_banks_id;
			$t_groups->provider_data_id 		= $provider_data_id;
			$t_groups->save();

			$groups					= $t_groups->idgroups;
			
			if (isset($request->realPath) && count($request->realPath)>0) 
			{
				for ($i=0; $i < count($request->realPath); $i++) 
				{ 
					if ($request->realPath[$i] != "") 
					{
						$documents 					= new App\GroupsDocuments();
						$new_file_name = Files::rename($request->realPath[$i],$folio);
						$documents->path 			= $new_file_name;
						$documents->idgroups 		= $groups;
						$documents->save();
					}
				}
			}

			if(isset($request->tamount) && count($request->tamount)>0)
			{
				for ($i=0; $i < count($request->tamount); $i++)
				{
					$t_detailGroups					= new App\GroupsDetail();
					$t_detailGroups->idgroups		= $groups;
					$t_detailGroups->quantity		= $request->tquanty[$i];
					$t_detailGroups->unit			= $request->tunit[$i];
					$t_detailGroups->description	= $request->tdescr[$i];
					$t_detailGroups->unitPrice		= $request->tprice[$i];
					$t_detailGroups->tax			= $request->tiva[$i];
					$t_detailGroups->discount		= $request->tdiscount[$i];
					$t_detailGroups->amount			= $request->tamount[$i];
					$t_detailGroups->typeTax		= $request->tivakind[$i];
					$t_detailGroups->subtotal		= $request->tquanty[$i] * $request->tprice[$i];
					$t_detailGroups->save();

					$idgroupsDetail     = $t_detailGroups->idgroupsDetail;
					$tamountadditional 	= 'tamountadditional'.$i;
					$tnameamount 		= 'tnameamount'.$i;
					if (isset($request->$tamountadditional) && $request->$tamountadditional != "") 
					{
						for ($d=0; $d < count($request->$tamountadditional); $d++) 
						{ 
							if ($request->$tamountadditional[$d] != "") 
							{
								$t_taxes 					= new App\GroupsTaxes();
								$t_taxes->name 				= $request->$tnameamount[$d];
								$t_taxes->amount 			= $request->$tamountadditional[$d];
								$t_taxes->idgroupsDetail 	= $idgroupsDetail;
								$t_taxes->save();
							}
						}
					}

					$tamountretention 	= 'tamountretention'.$i;
					$tnameretention 	= 'tnameretention'.$i;
					if (isset($request->$tamountretention) && $request->$tamountretention != "") 
					{
						for ($d=0; $d < count($request->$tamountretention); $d++) 
						{ 
							if ($request->$tamountretention[$d] != "") 
							{
								$t_retention 					= new App\GroupsRetention();
								$t_retention->name 				= $request->$tnameretention[$d];
								$t_retention->amount 			= $request->$tamountretention[$d];
								$t_retention->idgroupsDetail 	= $idgroupsDetail;
								$t_retention->save();
							}
						}
					}
				}
			}

			$alert 	= "swal('', '".Lang::get("messages.request_sent")."', 'success');";
			/*$emails = App\User::whereHas('module',function($q)
						{
							$q->where('id', 36);
						})
						->whereHas('inChargeDepGet',function($q) use ($t_request)
						{
							$q->where('departament_id', $t_request->idDepartment)
								->where('module_id',36);
						})
						->whereHas('inChargeEntGet',function($q) use ($t_request)
						{
							$q->where('enterprise_id', $t_request->idEnterprise)
								->where('module_id',36);
						})
						->where('active',1)
						->where('notification',1)
						->get();
			$emails	= App\User::join('user_has_department','users.id','user_has_department.user_id')
						->join('user_has_modules','users.id','user_has_modules.user_id')
						->where('user_has_modules.module_id',36)
						->where('user_has_department.departament_id',$request->departmentid)
						->where('users.active',1)
						->where('users.notification',1)
						->get();
			$user 	=  App\User::find($request->userid);
			if ($emails != "")
			{
				foreach ($emails as $email)
				{
					$name 			= $email->name.' '.$email->last_name.' '.$email->scnd_last_name;
					$to 			= $email->email;
					$kind 			= "Compra";
					$status 		= "Revisar";
					$date 			= Carbon::now();
					$requestUser	= $user->name.' '.$user->last_name.' '.$user->scnd_last_name;
					$url 			= route('purchase.review.edit',['id'=>$folio]);
					$subject 		= "Solicitud por Revisar";
					Mail::to($to)->send(new App\Mail\Notificacion($name,$kind,$status,$date,$url,$subject,$requestUser));
				}
			}*/
			return redirect('administration/movements-accounts')->with('alert',$alert);
		}
		else
		{
			return redirect('/');
		}
	}

	public function unsentGroups(Request $request)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			if ($request->fiscal == 1 && ($request->rfc != '' || $request->rfc == '' || $request->rfc == null)) 
			{
				$rfc = $request->rfc;
			}	
			elseif ($request->fiscal == 1 && (isset($request->idProvider) && $request->idProvider != '') && (isset($request->rfc) && $request->rfc == '')) 
			{
				$alert	= "swal('', 'Lo sentimos ocurrió un problema, la solicitud Fiscal tiene que llevar RFC obligatorio.', 'error');";
				$data	= App\Module::find($this->module_id);
				return view('administracion.movimientos_entre_cuentas.grupos',
				[
					'id'		=> $data['father'],
					'title'		=> $data['name'],
					'details'	=> $data['details'],
					'child_id' 	=> $this->module_id,
					'option_id' => 149,
					'alert' 	=> $alert
				]);
			}
			elseif($request->fiscal == 0 && $request->rfc != '')
			{
				$rfc = $request->rfc;
			}
			elseif($request->fiscal == 0 && $request->rfc == '')
			{
				$rfc = 'XAXX1'.str_pad(App\Provider::where('rfc','like','%XAXX1%')->count(), 8, "0", STR_PAD_LEFT);
			}

			$data						= App\Module::find($this->module_id);
			$t_request					= new App\RequestModel();
			$t_request->kind			= 14;
			$t_request->taxPayment		= $request->fiscal;
			$t_request->fDate			= Carbon::now();
			$t_request->PaymentDate		= $request->date !=null ? Carbon::createFromFormat('d-m-Y',$request->date)->format('Y-m-d') : null;
			$t_request->status			= 2;
			$t_request->idRequest		= $request->userid;
			$t_request->idElaborate		= Auth::user()->id;
			$t_request->save();
			$folio						= $t_request->folio;
			$kind						= $t_request->kind;
			$provider_id				= NULL;
			$provider_has_banks_id 		= NULL;
			$provider_data_id 			= $request->provider_data_id;

			if ($request->prov == "nuevo")
			{
				$t_provider_data 			= new App\ProviderData();
				$t_provider_data->users_id 	= Auth::user()->id;
				$t_provider_data->save();

				$t_provider					= new App\Provider();
				$t_provider->businessName	= $request->reason;
				$t_provider->beneficiary	= $request->beneficiary;
				$t_provider->phone			= $request->phone;
				$t_provider->rfc			= $rfc;
				$t_provider->contact		= $request->contact;
				$t_provider->commentaries	= $request->other;
				$t_provider->status			= 0;
				$t_provider->users_id		= Auth::user()->id;
				$t_provider->address		= $request->address;
				$t_provider->number			= $request->number;
				$t_provider->colony			= $request->colony;
				$t_provider->postalCode		= $request->cp;
				$t_provider->city			= $request->city;
				$t_provider->state_idstate	= $request->state;
				$t_provider->provider_data_id	= $t_provider_data->id;
				$t_provider->save();
				$provider_id				= $t_provider->idProvider;
				$provider_data_id 			= $t_provider->provider_data_id;

				if(isset($request->providerBank))
				{
					for ($i=0; $i < count($request->providerBank); $i++)
					{
						$t_providerBank							= new App\ProviderBanks;
						$t_providerBank->provider_idProvider	= $provider_id;
						$t_providerBank->banks_idBanks			= $request->bank[$i];
						$t_providerBank->alias 					= $request->alias[$i];
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

						if ($request->pay_mode == 1) 
						{
							if ($request->checked[$i] == 1) 
							{
								$provider_has_banks_id = $t_providerBank->id;
							}
						}
					}
				}
			}
			elseif($request->prov == "buscar")
			{
				if (isset($request->edit))
				{
					$oldProvider			= App\Provider::find($request->idProvider);
					if($oldProvider->status==0)
					{
						$oldProvider->businessName	= $request->reason;
						$oldProvider->beneficiary	= $request->beneficiary;
						$oldProvider->phone			= $request->phone;
						$oldProvider->rfc			= $rfc;
						$oldProvider->contact		= $request->contact;
						$oldProvider->commentaries	= $request->other;
						$oldProvider->status		= 0;
						$oldProvider->users_id		= Auth::user()->id;
						$oldProvider->address		= $request->address;
						$oldProvider->number		= $request->number;
						$oldProvider->colony		= $request->colony;
						$oldProvider->postalCode	= $request->cp;
						$oldProvider->city			= $request->city;
						$oldProvider->state_idstate	= $request->state;
						$oldProvider->save();
						$provider_id				= $oldProvider->idProvider;

						if(isset($request->providerBank))
						{
							for ($i=0; $i < count($request->providerBank); $i++)
							{
								if ($request->providerBank[$i] == "x") 
								{	
									$t_providerBank							= new App\ProviderBanks;
									$t_providerBank->provider_idProvider	= $provider_id;
									$t_providerBank->banks_idBanks			= $request->bank[$i];
									$t_providerBank->alias 					= $request->alias[$i];
									$t_providerBank->account				= $request->account[$i];
									$t_providerBank->branch					= $request->branch_office[$i];
									$t_providerBank->reference				= $request->reference[$i];
									$t_providerBank->clabe					= $request->clabe[$i];
									$t_providerBank->currency				= $request->currency[$i];
									$t_providerBank->agreement				= $request->agreement[$i];
									$t_providerBank->iban 					= $request->iban[$i];
									$t_providerBank->bic_swift 				= $request->bic_swift[$i];
									$t_providerBank->provider_data_id 		= $oldProvider->provider_data_id;
									$t_providerBank->save();

									if ($request->pay_mode == 1) 
									{
										if ($request->checked[$i] == 1) 
										{
											$provider_has_banks_id = $t_providerBank->id;
										}
									}
								}
								else
								{
									$t_providerBank	= App\ProviderBanks::find($request->providerBank[$i]);
									if ($request->pay_mode == 1) 
									{
										if ($request->checked[$i] == 1) 
										{
											$provider_has_banks_id 	= $t_providerBank->id;
										}
									}
								}
							}
						}
					}
					else
					{
						$oldProvider->status		= 1;
						$oldProvider->save();
						$provider_data_id 			= $oldProvider->provider_data_id;

						$t_provider					= new App\Provider();
						$t_provider->businessName	= $request->reason;
						$t_provider->beneficiary	= $request->beneficiary;
						$t_provider->phone			= $request->phone;
						$t_provider->rfc			= $rfc;
						$t_provider->contact		= $request->contact;
						$t_provider->commentaries	= $request->other;
						$t_provider->status			= 2;
						$t_provider->users_id		= Auth::user()->id;
						$t_provider->address		= $request->address;
						$t_provider->number			= $request->number;
						$t_provider->colony			= $request->colony;
						$t_provider->postalCode		= $request->cp;
						$t_provider->city			= $request->city;
						$t_provider->state_idstate	= $request->state;
						$t_provider->provider_data_id	= $provider_data_id;
						$t_provider->save();
						$provider_id				= $t_provider->idProvider;
						$provider_data_id 			= $t_provider->provider_data_id;
						if(isset($request->providerBank))
						{
							for ($i=0; $i < count($request->providerBank); $i++)
							{
								if ($request->providerBank[$i] == "x") 
								{	
									$t_providerBank							= new App\ProviderBanks;
									$t_providerBank->provider_idProvider	= $provider_id;
									$t_providerBank->banks_idBanks			= $request->bank[$i];
									$t_providerBank->alias 					= $request->alias[$i];
									$t_providerBank->account				= $request->account[$i];
									$t_providerBank->branch					= $request->branch_office[$i];
									$t_providerBank->reference				= $request->reference[$i];
									$t_providerBank->clabe					= $request->clabe[$i];
									$t_providerBank->currency				= $request->currency[$i];
									$t_providerBank->agreement				= $request->agreement[$i];
									$t_providerBank->iban 					= $request->iban[$i];
									$t_providerBank->bic_swift 				= $request->bic_swift[$i];
									$t_providerBank->provider_data_id 		= $oldProvider->provider_data_id;
									$t_providerBank->save();

									if ($request->pay_mode == 1) 
									{
										if ($request->checked[$i] == 1) 
										{
											$provider_has_banks_id = $t_providerBank->id;
										}
									}
								}
								else
								{
									$t_providerBank	= App\ProviderBanks::find($request->providerBank[$i]);
									if ($request->pay_mode == 1) 
									{
										if ($request->checked[$i] == 1) 
										{
											$provider_has_banks_id 	= $t_providerBank->id;
										}
									}
								}
							}
						}
					}
				}
				else
				{
					$provider_id	= $request->idProvider;
					$provider_has_banks_id 	= $request->provider_has_banks_id;
				}
			}

			$subtotales	= 0;
			$iva		= 0;
			$taxes 		= 0;
			$retentions = 0;

			if(isset($request->tquanty) && count($request->tquanty)>0)
			{
				for ($i=0;isset($request->tquanty) && $i < count($request->tquanty); $i++)
				{
					$tamountadditional 	= 'tamountadditional'.$i;
					$tnameamount 		= 'tnameamount'.$i;

					if (isset($request->$tamountadditional) && $request->$tamountadditional != "") 
					{
						for ($d=0; $d < count($request->$tamountadditional); $d++) 
						{ 
							if ($request->$tamountadditional[$d] != "") 
							{
								$taxes 	+= $request->$tamountadditional[$d];
							}
						}
					}

					$tamountretention = 'tamountretention'.$i;
					if (isset($request->$tamountretention) && $request->$tamountretention != "") 
					{
						for ($d=0; $d < count($request->$tamountretention); $d++) 
						{ 
							if ($request->$tamountretention[$d] != "") 
							{
								$retentions 	+= $request->$tamountretention[$d];
							}
						}
					}


					$subtotales	+= (($request->tquanty[$i] * $request->tprice[$i])-$request->tdiscount[$i]);
					$iva		+= $request->tiva[$i];
				}
			}

			$total								= ($subtotales+$iva+$taxes)-$retentions;
			$t_groups							= new App\Groups();
			$t_groups->title					= $request->title;
			$t_groups->datetitle				= $request->datetitle != null ? Carbon::createFromFormat('d-m-Y',$request->datetitle)->format('Y-m-d') : null;
			$t_groups->numberOrder				= $request->numberOrder;
			$t_groups->operationType			= $request->typeOperation;
			$t_groups->amountMovement			= $request->amountTotal;
			$t_groups->amountRetake				= $request->amountRetake;
			$t_groups->commission				= $request->commission;
			$t_groups->reference				= $request->referencePurchase;
			$t_groups->typeCurrency				= $request->type_currency;
			$t_groups->paymentDate				= $request->date !=null ? Carbon::createFromFormat('d-m-Y',$request->date)->format('Y-m-d') : null;
			$t_groups->idpaymentMethod			= $request->pay_mode;
			$t_groups->statusBill				= $request->status_bill;
			$t_groups->subtotales				= $subtotales;
			$t_groups->tax						= $iva;
			$t_groups->amount					= $total;
			$t_groups->idFolio					= $folio;
			$t_groups->idKind					= $kind;
			$t_groups->idEnterpriseOrigin		= $request->enterpriseid_origin;
			$t_groups->idDepartamentOrigin		= $request->departmentid_origin;
			$t_groups->idAreaOrigin				= $request->areaid_origin;
			$t_groups->idProjectOrigin			= $request->projectid_origin;
			$t_groups->idAccAccOrigin			= $request->accountid_origin;
			$t_groups->idEnterpriseDestiny		= $request->enterpriseid_destination;
			$t_groups->idAccAccDestiny			= $request->accountid_destination;
			$t_groups->idProvider				=  
			$t_groups->provider_has_banks_id	= $provider_has_banks_id;
			$t_groups->provider_data_id 		= $provider_data_id;
			$t_groups->save();

			$groups					= $t_groups->idgroups;
			
			if (isset($request->realPath) && count($request->realPath)>0) 
			{
				for ($i=0; $i < count($request->realPath); $i++) 
				{ 
					if ($request->realPath[$i] != "") 
					{
						$documents 					= new App\GroupsDocuments();
						$new_file_name = Files::rename($request->realPath[$i],$folio);
						$documents->path 			= $new_file_name;
						$documents->idgroups 		= $groups;
						$documents->save();
					}
				}
			}

			if(isset($request->tamount) && count($request->tamount)>0)
			{
				for ($i=0; isset($request->tamount) && $i < count($request->tamount); $i++)
				{
					$t_detailGroups					= new App\GroupsDetail();
					$t_detailGroups->idgroups		= $groups;
					$t_detailGroups->quantity		= $request->tquanty[$i];
					$t_detailGroups->unit			= $request->tunit[$i];
					$t_detailGroups->description	= $request->tdescr[$i];
					$t_detailGroups->unitPrice		= $request->tprice[$i];
					$t_detailGroups->tax			= $request->tiva[$i];
					$t_detailGroups->discount		= $request->tdiscount[$i];
					$t_detailGroups->amount			= $request->tamount[$i];
					$t_detailGroups->typeTax		= $request->tivakind[$i];
					$t_detailGroups->subtotal		= $request->tquanty[$i] * $request->tprice[$i];
					$t_detailGroups->save();

					$idgroupsDetail     = $t_detailGroups->idgroupsDetail;
					$tamountadditional 	= 'tamountadditional'.$i;
					$tnameamount 		= 'tnameamount'.$i;
					if (isset($request->$tamountadditional) && $request->$tamountadditional != "") 
					{
						for ($d=0; $d < count($request->$tamountadditional); $d++) 
						{ 
							if ($request->$tamountadditional[$d] != "") 
							{
								$t_taxes 					= new App\GroupsTaxes();
								$t_taxes->name 				= $request->$tnameamount[$d];
								$t_taxes->amount 			= $request->$tamountadditional[$d];
								$t_taxes->idgroupsDetail 	= $idgroupsDetail;
								$t_taxes->save();
							}
						}
					}

					$tamountretention 	= 'tamountretention'.$i;
					$tnameretention 	= 'tnameretention'.$i;
					if (isset($request->$tamountretention) && $request->$tamountretention != "") 
					{
						for ($d=0; $d < count($request->$tamountretention); $d++) 
						{ 
							if ($request->$tamountretention[$d] != "") 
							{
								$t_retention 					= new App\GroupsRetention();
								$t_retention->name 				= $request->$tnameretention[$d];
								$t_retention->amount 			= $request->$tamountretention[$d];
								$t_retention->idgroupsDetail 	= $idgroupsDetail;
								$t_retention->save();
							}
						}
					}
				}
			}

			$id = $folio;
			$alert     = "swal('', '".Lang::get("messages.request_saved")."', 'success');";
			
			return redirect()->route('movements-accounts.follow.edit',['id'=>$id])->with('alert',$alert);
		}
		else
		{
			return redirect('/');
		}
	}

	public function unsentFollowGroups(Request $request,$id)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			if ($request->fiscal == 1 && $request->rfc != '') 
			{
				$rfc = $request->rfc;
			}	
			elseif ($request->fiscal == 1 && (isset($request->idProvider) && $request->idProvider != '') && (isset($request->rfc) && $request->rfc == '')) 
			{
				$alert = "swal('', 'Lo sentimos ocurrió un problema, la solicitud Fiscal tiene que llevar RFC obligatorio.', 'error');";
				return redirect()->route('movements-accounts.follow.edit',['id'=>$id])->with('alert',$alert);
			}
			elseif($request->fiscal == 0 && $request->rfc != '')
			{
				$rfc = $request->rfc;
			}
			elseif($request->fiscal == 0 && $request->rfc == '')
			{
				$rfc = 'XAXX1'.str_pad(App\Provider::where('rfc','like','%XAXX1%')->count(), 8, "0", STR_PAD_LEFT);
			}

			$data						= App\Module::find($this->module_id);
			$t_request					= App\RequestModel::find($id);
			$t_request->taxPayment		= $request->fiscal;
			$t_request->PaymentDate		= $request->date !="" ?Carbon::createFromFormat('d-m-Y',$request->date)->format('Y-m-d') : null;
			$t_request->status			= 2;
			$t_request->idRequest		= $request->userid;
			$t_request->save();

			$folio						= $t_request->folio;
			$kind						= $t_request->kind;
			$provider_id				= NULL;
			$provider_has_banks_id 		= NULL;
			$provider_data_id 			= $request->provider_data_id;
			if ($request->prov == "nuevo")
			{
				$t_provider_data 			= new App\ProviderData();
				$t_provider_data->users_id 	= Auth::user()->id;
				$t_provider_data->save();

				$t_provider					= new App\Provider();
				$t_provider->businessName	= $request->reason;
				$t_provider->beneficiary	= $request->beneficiary;
				$t_provider->phone			= $request->phone;
				$t_provider->rfc			= $rfc;
				$t_provider->contact		= $request->contact;
				$t_provider->commentaries	= $request->other;
				$t_provider->status			= 0;
				$t_provider->users_id		= Auth::user()->id;
				$t_provider->address		= $request->address;
				$t_provider->number			= $request->number;
				$t_provider->colony			= $request->colony;
				$t_provider->postalCode		= $request->cp;
				$t_provider->city			= $request->city;
				$t_provider->state_idstate	= $request->state;
				$t_provider->provider_data_id	= $t_provider_data->id;
				$t_provider->save();
				$provider_id				= $t_provider->idProvider;
				$provider_data_id 			= $t_provider->provider_data_id;

				if(isset($request->providerBank))
				{
					for ($i=0; $i < count($request->providerBank); $i++)
					{
						$t_providerBank							= new App\ProviderBanks;
						$t_providerBank->provider_idProvider	= $provider_id;
						$t_providerBank->banks_idBanks			= $request->bank[$i];
						$t_providerBank->alias 					= $request->alias[$i];
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

						if ($request->pay_mode == 1) 
						{
							if ($request->checked[$i] == 1) 
							{
								$provider_has_banks_id = $t_providerBank->id;
							}
						}
					}
				}
			}
			elseif($request->prov == "buscar")
			{
				if (isset($request->edit))
				{
					$oldProvider			= App\Provider::find($request->idProvider);
					if($oldProvider->status==0)
					{
						$oldProvider->businessName	= $request->reason;
						$oldProvider->beneficiary	= $request->beneficiary;
						$oldProvider->phone			= $request->phone;
						$oldProvider->rfc			= $rfc;
						$oldProvider->contact		= $request->contact;
						$oldProvider->commentaries	= $request->other;
						$oldProvider->status		= 0;
						$oldProvider->users_id		= Auth::user()->id;
						$oldProvider->address		= $request->address;
						$oldProvider->number		= $request->number;
						$oldProvider->colony		= $request->colony;
						$oldProvider->postalCode	= $request->cp;
						$oldProvider->city			= $request->city;
						$oldProvider->state_idstate	= $request->state;
						$oldProvider->save();
						$provider_id				= $oldProvider->idProvider;

						if(isset($request->providerBank))
						{
							for ($i=0; $i < count($request->providerBank); $i++)
							{
								if ($request->providerBank[$i] == "x") 
								{	
									$t_providerBank							= new App\ProviderBanks;
									$t_providerBank->provider_idProvider	= $provider_id;
									$t_providerBank->banks_idBanks			= $request->bank[$i];
									$t_providerBank->alias 					= $request->alias[$i];
									$t_providerBank->account				= $request->account[$i];
									$t_providerBank->branch					= $request->branch_office[$i];
									$t_providerBank->reference				= $request->reference[$i];
									$t_providerBank->clabe					= $request->clabe[$i];
									$t_providerBank->currency				= $request->currency[$i];
									$t_providerBank->agreement				= $request->agreement[$i];
									$t_providerBank->iban 					= $request->iban[$i];
									$t_providerBank->bic_swift 				= $request->bic_swift[$i];
									$t_providerBank->provider_data_id 		= $oldProvider->provider_data_id;
									$t_providerBank->save();

									if ($request->pay_mode == 1) 
									{
										if ($request->checked[$i] == 1) 
										{
											$provider_has_banks_id = $t_providerBank->id;
										}
									}
								}
								else
								{
									$t_providerBank	= App\ProviderBanks::find($request->providerBank[$i]);
									if ($request->pay_mode == 1) 
									{
										if ($request->checked[$i] == 1) 
										{
											$provider_has_banks_id 	= $t_providerBank->id;
										}
									}
								}
							}
						}
					}
					else
					{
						$oldProvider->status		= 1;
						$oldProvider->save();
						$provider_data_id 			= $oldProvider->provider_data_id;

						$t_provider					= new App\Provider();
						$t_provider->businessName	= $request->reason;
						$t_provider->beneficiary	= $request->beneficiary;
						$t_provider->phone			= $request->phone;
						$t_provider->rfc			= $rfc;
						$t_provider->contact		= $request->contact;
						$t_provider->commentaries	= $request->other;
						$t_provider->status			= 2;
						$t_provider->users_id		= Auth::user()->id;
						$t_provider->address		= $request->address;
						$t_provider->number			= $request->number;
						$t_provider->colony			= $request->colony;
						$t_provider->postalCode		= $request->cp;
						$t_provider->city			= $request->city;
						$t_provider->state_idstate	= $request->state;
						$t_provider->provider_data_id	= $provider_data_id;
						$t_provider->save();
						$provider_id				= $t_provider->idProvider;
						$provider_data_id 			= $t_provider->provider_data_id;
						if(isset($request->providerBank))
						{
							for ($i=0; $i < count($request->providerBank); $i++)
							{
								if ($request->providerBank[$i] == "x") 
								{	
									$t_providerBank							= new App\ProviderBanks;
									$t_providerBank->provider_idProvider	= $provider_id;
									$t_providerBank->banks_idBanks			= $request->bank[$i];
									$t_providerBank->alias 					= $request->alias[$i];
									$t_providerBank->account				= $request->account[$i];
									$t_providerBank->branch					= $request->branch_office[$i];
									$t_providerBank->reference				= $request->reference[$i];
									$t_providerBank->clabe					= $request->clabe[$i];
									$t_providerBank->currency				= $request->currency[$i];
									$t_providerBank->agreement				= $request->agreement[$i];
									$t_providerBank->iban 					= $request->iban[$i];
									$t_providerBank->bic_swift 				= $request->bic_swift[$i];
									$t_providerBank->provider_data_id 		= $oldProvider->provider_data_id;
									$t_providerBank->save();

									if ($request->pay_mode == 1) 
									{
										if ($request->checked[$i] == 1) 
										{
											$provider_has_banks_id = $t_providerBank->id;
										}
									}
								}
								else
								{
									$t_providerBank	= App\ProviderBanks::find($request->providerBank[$i]);
									if ($request->pay_mode == 1) 
									{
										if ($request->checked[$i] == 1) 
										{
											$provider_has_banks_id 	= $t_providerBank->id;
										}
									}
								}
							}
						}
					}
				}
				else
				{
					$provider_id	= $request->idProvider;
					$provider_has_banks_id 	= $request->provider_has_banks_id;
				}
			}

			$subtotales	= 0;
			$iva		= 0;
			$taxes 		= 0;
			$retentions = 0;

			if(isset($request->tquanty) && count($request->tquanty)>0)
			{
				for ($i=0;isset($request->tquanty) && $i < count($request->tquanty); $i++)
				{
					$tamountadditional 	= 'tamountadditional'.$i;
					$tnameamount 		= 'tnameamount'.$i;

					if (isset($request->$tamountadditional) && $request->$tamountadditional != "") 
					{
						for ($d=0; $d < count($request->$tamountadditional); $d++) 
						{ 
							if ($request->$tamountadditional[$d] != "") 
							{
								$taxes 	+= $request->$tamountadditional[$d];
							}
						}
					}

					$tamountretention = 'tamountretention'.$i;
					if (isset($request->$tamountretention) && $request->$tamountretention != "") 
					{
						for ($d=0; $d < count($request->$tamountretention); $d++) 
						{ 
							if ($request->$tamountretention[$d] != "") 
							{
								$retentions 	+= $request->$tamountretention[$d];
							}
						}
					}


					$subtotales	+= (($request->tquanty[$i] * $request->tprice[$i])-$request->tdiscount[$i]);
					$iva		+= $request->tiva[$i];
				}
			}

			$total		= ($subtotales+$iva+$taxes)-$retentions;
			$groupsID	= App\Groups::where('idFolio',$folio)->first()->idgroups;

			isset(App\GroupsDetail::where('idgroups',$groupsID)->first()->idgroupsDetail) ? $detailID = App\GroupsDetail::where('idgroups',$groupsID)->first()->idgroupsDetail : $detailID = null;

			$t_groups							= App\Groups::find($groupsID);
			$t_groups->title					= $request->title;
			$t_groups->datetitle				= $request->datetitle != null ? Carbon::createFromFormat('d-m-Y',$request->datetitle)->format('Y-m-d') : null;
			$t_groups->numberOrder				= $request->numberOrder;
			$t_groups->operationType			= $request->typeOperation;
			$t_groups->amountMovement			= $request->amountTotal;
			$t_groups->amountRetake				= $request->amountRetake;
			$t_groups->commission				= $request->commission;
			$t_groups->reference				= $request->referencePurchase;
			$t_groups->typeCurrency				= $request->type_currency;
			$t_groups->paymentDate				= $request->date != null ? Carbon::createFromFormat('d-m-Y',$request->date)->format('Y-m-d') : null;
			$t_groups->idpaymentMethod			= $request->pay_mode;
			$t_groups->statusBill				= $request->status_bill;
			$t_groups->subtotales				= $subtotales;
			$t_groups->tax						= $iva;
			$t_groups->amount					= $total;
			$t_groups->idFolio					= $folio;
			$t_groups->idKind					= $kind;
			$t_groups->idEnterpriseOrigin		= $request->enterpriseid_origin;
			$t_groups->idDepartamentOrigin		= $request->departmentid_origin;
			$t_groups->idAreaOrigin				= $request->areaid_origin;
			$t_groups->idProjectOrigin			= $request->projectid_origin;
			$t_groups->idAccAccOrigin			= $request->accountid_origin;
			$t_groups->idEnterpriseDestiny		= $request->enterpriseid_destination;
			$t_groups->idAccAccDestiny			= $request->accountid_destination;
			$t_groups->idProvider				= $provider_id;
			$t_groups->provider_has_banks_id	= $provider_has_banks_id;
			$t_groups->provider_data_id			= $provider_data_id;
			$t_groups->save();

			$groups					= $t_groups->idgroups;
			
			if (isset($request->realPath) && count($request->realPath)>0) 
			{
				for ($i=0; $i < count($request->realPath); $i++) 
				{ 
					if ($request->realPath[$i] != "") 
					{
						$documents 					= new App\GroupsDocuments();
						$new_file_name = Files::rename($request->realPath[$i],$folio);
						$documents->path 			= $new_file_name;
						$documents->idgroups 		= $groupsID;
						$documents->save();
					}
				}
			}

			$deleteTaxes		= App\GroupsTaxes::where('idgroupsDetail',$detailID)->delete();
			$deleteRetentions	= App\GroupsRetention::where('idgroupsDetail',$detailID)->delete();
			$delete				= App\GroupsDetail::where('idgroups',$groupsID)->delete();


			if(isset($request->tamount) && count($request->tamount)>0)
			{
				for ($i=0; isset($request->tamount) && $i < count($request->tamount); $i++)
				{
					$t_detailGroups					= new App\GroupsDetail();
					$t_detailGroups->idgroups		= $groupsID;
					$t_detailGroups->quantity		= $request->tquanty[$i];
					$t_detailGroups->unit			= $request->tunit[$i];
					$t_detailGroups->description	= $request->tdescr[$i];
					$t_detailGroups->unitPrice		= $request->tprice[$i];
					$t_detailGroups->tax			= $request->tiva[$i];
					$t_detailGroups->discount		= $request->tdiscount[$i];
					$t_detailGroups->amount			= $request->tamount[$i];
					$t_detailGroups->typeTax		= $request->tivakind[$i];
					$t_detailGroups->subtotal		= $request->tquanty[$i] * $request->tprice[$i];
					$t_detailGroups->save();

					$idgroupsDetail     = $t_detailGroups->idgroupsDetail;
					$tamountadditional 	= 'tamountadditional'.$i;
					$tnameamount 		= 'tnameamount'.$i;
					if (isset($request->$tamountadditional) && $request->$tamountadditional != "") 
					{
						for ($d=0; $d < count($request->$tamountadditional); $d++) 
						{ 
							if ($request->$tamountadditional[$d] != "") 
							{
								$t_taxes 					= new App\GroupsTaxes();
								$t_taxes->name 				= $request->$tnameamount[$d];
								$t_taxes->amount 			= $request->$tamountadditional[$d];
								$t_taxes->idgroupsDetail 	= $idgroupsDetail;
								$t_taxes->save();
							}
						}
					}

					$tamountretention 	= 'tamountretention'.$i;
					$tnameretention 	= 'tnameretention'.$i;
					if (isset($request->$tamountretention) && $request->$tamountretention != "") 
					{
						for ($d=0; $d < count($request->$tamountretention); $d++) 
						{ 
							if ($request->$tamountretention[$d] != "") 
							{
								$t_retention 					= new App\GroupsRetention();
								$t_retention->name 				= $request->$tnameretention[$d];
								$t_retention->amount 			= $request->$tamountretention[$d];
								$t_retention->idgroupsDetail 	= $idgroupsDetail;
								$t_retention->save();
							}
						}
					}
				}
			}

			$id = $folio;
			$alert     = "swal('', '".Lang::get("messages.request_saved")."', 'success');";
			
			return redirect()->route('movements-accounts.follow.edit',['id'=>$id])->with('alert',$alert);
		}
		else
		{
			return redirect('/');
		}
	}

	public function updateFollowGroups(Request $request,$id)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			if ($request->fiscal == 1 && $request->rfc != '') 
			{
				$rfc = $request->rfc;
			}	
			elseif ($request->fiscal == 1 && (isset($request->idProvider) && $request->idProvider != '') && (isset($request->rfc) && $request->rfc == '')) 
			{
				$alert = "swal('', 'Lo sentimos ocurrió un problema, la solicitud Fiscal tiene que llevar RFC obligatorio.', 'error');";
				return redirect()->route('movements-accounts.follow.edit',['id'=>$id])->with('alert',$alert);
			}
			elseif($request->fiscal == 0 && $request->rfc != '')
			{
				$rfc = $request->rfc;
			}
			elseif($request->fiscal == 0 && $request->rfc == '')
			{
				$rfc = 'XAXX1'.str_pad(App\Provider::where('rfc','like','%XAXX1%')->count(), 8, "0", STR_PAD_LEFT);
			}

			$data						= App\Module::find($this->module_id);
			$t_request					= App\RequestModel::find($id);
			$t_request->taxPayment		= $request->fiscal;
			$t_request->PaymentDate		= $request->date != "" ? Carbon::createFromFormat('d-m-Y',$request->date)->format('Y-m-d') : null;
			$t_request->status			= 3;
			$t_request->idRequest		= $request->userid;
			$t_request->save();

			$folio						= $t_request->folio;
			$kind						= $t_request->kind;

			$provider_has_banks_id 		= NULL;
			$provider_data_id 			= $request->provider_data_id;
			if ($request->prov == "nuevo")
			{
				$t_provider_data 			= new App\ProviderData();
				$t_provider_data->users_id 	= Auth::user()->id;
				$t_provider_data->save();

				$t_provider					= new App\Provider();
				$t_provider->businessName	= $request->reason;
				$t_provider->beneficiary	= $request->beneficiary;
				$t_provider->phone			= $request->phone;
				$t_provider->rfc			= $rfc;
				$t_provider->contact		= $request->contact;
				$t_provider->commentaries	= $request->other;
				$t_provider->status			= 2;
				$t_provider->users_id		= Auth::user()->id;
				$t_provider->address		= $request->address;
				$t_provider->number			= $request->number;
				$t_provider->colony			= $request->colony;
				$t_provider->postalCode		= $request->cp;
				$t_provider->city			= $request->city;
				$t_provider->state_idstate	= $request->state;
				$t_provider->provider_data_id	= $t_provider_data->id;
				$t_provider->save();
				$provider_id				= $t_provider->idProvider;
				$provider_data_id 			= $t_provider->provider_data_id;
				if(isset($request->providerBank))
				{
					for ($i=0; $i < count($request->providerBank); $i++)
					{
						$t_providerBank							= new App\ProviderBanks;
						$t_providerBank->provider_idProvider	= $provider_id;
						$t_providerBank->banks_idBanks			= $request->bank[$i];
						$t_providerBank->alias 					= $request->alias[$i];
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

						if ($request->pay_mode == 1) 
						{
							if ($request->checked[$i] == 1) 
							{
								$provider_has_banks_id = $t_providerBank->id;
							}
						}
					}
				}
			}
			elseif($request->prov == "buscar")
			{
				if (isset($request->edit))
				{
					$oldProvider			= App\Provider::find($request->idProvider);
					if($oldProvider->status==0)
					{
						$oldProvider->businessName	= $request->reason;
						$oldProvider->beneficiary	= $request->beneficiary;
						$oldProvider->phone			= $request->phone;
						$oldProvider->rfc			= $rfc;
						$oldProvider->contact		= $request->contact;
						$oldProvider->commentaries	= $request->other;
						$oldProvider->status		= 2;
						$oldProvider->users_id		= Auth::user()->id;
						$oldProvider->address		= $request->address;
						$oldProvider->number		= $request->number;
						$oldProvider->colony		= $request->colony;
						$oldProvider->postalCode	= $request->cp;
						$oldProvider->city			= $request->city;
						$oldProvider->state_idstate	= $request->state;
						$oldProvider->save();
						$provider_id				= $oldProvider->idProvider;

						if(isset($request->providerBank))
						{
							for ($i=0; $i < count($request->providerBank); $i++)
							{
								if ($request->providerBank[$i] == "x") 
								{	
									$t_providerBank							= new App\ProviderBanks;
									$t_providerBank->provider_idProvider	= $provider_id;
									$t_providerBank->banks_idBanks			= $request->bank[$i];
									$t_providerBank->alias 					= $request->alias[$i];
									$t_providerBank->account				= $request->account[$i];
									$t_providerBank->branch					= $request->branch_office[$i];
									$t_providerBank->reference				= $request->reference[$i];
									$t_providerBank->clabe					= $request->clabe[$i];
									$t_providerBank->currency				= $request->currency[$i];
									$t_providerBank->agreement				= $request->agreement[$i];
									$t_providerBank->iban 					= $request->iban[$i];
									$t_providerBank->bic_swift 				= $request->bic_swift[$i];
									$t_providerBank->provider_data_id 		= $oldProvider->provider_data_id;
									$t_providerBank->save();

									if ($request->pay_mode == 1) 
									{
										if ($request->checked[$i] == 1) 
										{
											$provider_has_banks_id = $t_providerBank->id;
										}
									}
								}
								else
								{
									$t_providerBank	= App\ProviderBanks::find($request->providerBank[$i]);
									if ($request->pay_mode == 1) 
									{
										if ($request->checked[$i] == 1) 
										{
											$provider_has_banks_id 	= $t_providerBank->id;
										}
									}
								}
							}
						}
					}
					else
					{
						$oldProvider->status		= 1;
						$oldProvider->save();
						$provider_data_id 			= $oldProvider->provider_data_id;

						$t_provider					= new App\Provider();
						$t_provider->businessName	= $request->reason;
						$t_provider->beneficiary	= $request->beneficiary;
						$t_provider->phone			= $request->phone;
						$t_provider->rfc			= $rfc;
						$t_provider->contact		= $request->contact;
						$t_provider->commentaries	= $request->other;
						$t_provider->status			= 2;
						$t_provider->users_id		= Auth::user()->id;
						$t_provider->address		= $request->address;
						$t_provider->number			= $request->number;
						$t_provider->colony			= $request->colony;
						$t_provider->postalCode		= $request->cp;
						$t_provider->city			= $request->city;
						$t_provider->state_idstate	= $request->state;
						$t_provider->provider_data_id	= $provider_data_id;
						$t_provider->save();
						$provider_id				= $t_provider->idProvider;
						$provider_data_id 			= $t_provider->provider_data_id;
						if(isset($request->providerBank))
						{
							for ($i=0; $i < count($request->providerBank); $i++)
							{
								if ($request->providerBank[$i] == "x") 
								{	
									$t_providerBank							= new App\ProviderBanks;
									$t_providerBank->provider_idProvider	= $provider_id;
									$t_providerBank->banks_idBanks			= $request->bank[$i];
									$t_providerBank->alias 					= $request->alias[$i];
									$t_providerBank->account				= $request->account[$i];
									$t_providerBank->branch					= $request->branch_office[$i];
									$t_providerBank->reference				= $request->reference[$i];
									$t_providerBank->clabe					= $request->clabe[$i];
									$t_providerBank->currency				= $request->currency[$i];
									$t_providerBank->agreement				= $request->agreement[$i];
									$t_providerBank->iban 					= $request->iban[$i];
									$t_providerBank->bic_swift 				= $request->bic_swift[$i];
									$t_providerBank->provider_data_id 		= $oldProvider->provider_data_id;
									$t_providerBank->save();

									if ($request->pay_mode == 1) 
									{
										if ($request->checked[$i] == 1) 
										{
											$provider_has_banks_id = $t_providerBank->id;
										}
									}
								}
								else
								{
									$t_providerBank	= App\ProviderBanks::find($request->providerBank[$i]);
									if ($request->pay_mode == 1) 
									{
										if ($request->checked[$i] == 1) 
										{
											$provider_has_banks_id 	= $t_providerBank->id;
										}
									}
								}
							}
						}
					}
				}
				else
				{
					$provider_id	= $request->idProvider;
					$provider_has_banks_id 	= $request->provider_has_banks_id;
				}
			}

			$subtotales	= 0;
			$iva		= 0;
			$taxes 		= 0;
			$retentions = 0;

			if(isset($request->tquanty) && count($request->tquanty)>0)
			{
				for ($i=0;isset($request->tquanty) && $i < count($request->tquanty); $i++)
				{
					$tamountadditional 	= 'tamountadditional'.$i;
					$tnameamount 		= 'tnameamount'.$i;

					if (isset($request->$tamountadditional) && $request->$tamountadditional != "") 
					{
						for ($d=0; $d < count($request->$tamountadditional); $d++) 
						{ 
							if ($request->$tamountadditional[$d] != "") 
							{
								$taxes 	+= $request->$tamountadditional[$d];
							}
						}
					}

					$tamountretention = 'tamountretention'.$i;
					if (isset($request->$tamountretention) && $request->$tamountretention != "") 
					{
						for ($d=0; $d < count($request->$tamountretention); $d++) 
						{ 
							if ($request->$tamountretention[$d] != "") 
							{
								$retentions 	+= $request->$tamountretention[$d];
							}
						}
					}


					$subtotales	+= (($request->tquanty[$i] * $request->tprice[$i])-$request->tdiscount[$i]);
					$iva		+= $request->tiva[$i];
				}
			}

			$total		= ($subtotales+$iva+$taxes)-$retentions;
			$groupsID	= App\Groups::where('idFolio',$folio)->first()->idgroups;

			isset(App\GroupsDetail::where('idgroups',$groupsID)->first()->idgroupsDetail) ? $detailID = App\GroupsDetail::where('idgroups',$groupsID)->first()->idgroupsDetail : $detailID = null;

			$t_groups							= App\Groups::find($groupsID);
			$t_groups->title					= $request->title;
			$t_groups->datetitle				= $request->datetitle != null ? Carbon::createFromFormat('d-m-Y',$request->datetitle)->format('Y-m-d') : null;
			$t_groups->numberOrder				= $request->numberOrder;
			$t_groups->operationType			= $request->typeOperation;
			$t_groups->amountMovement			= $request->amountTotal;
			$t_groups->amountRetake				= $request->amountRetake;
			$t_groups->commission				= $request->commission;
			$t_groups->reference				= $request->referencePurchase;
			$t_groups->typeCurrency				= $request->type_currency;
			$t_groups->paymentDate				= $request->date !='' ? date('Y-m-d',strtotime($request->date)) : null;
			$t_groups->idpaymentMethod			= $request->pay_mode;
			$t_groups->statusBill				= $request->status_bill;
			$t_groups->subtotales				= $subtotales;
			$t_groups->tax						= $iva;
			$t_groups->amount					= $total;
			$t_groups->idFolio					= $folio;
			$t_groups->idKind					= $kind;
			$t_groups->idEnterpriseOrigin		= $request->enterpriseid_origin;
			$t_groups->idDepartamentOrigin		= $request->departmentid_origin;
			$t_groups->idAreaOrigin				= $request->areaid_origin;
			$t_groups->idProjectOrigin			= $request->projectid_origin;
			$t_groups->idAccAccOrigin			= $request->accountid_origin;
			$t_groups->idEnterpriseDestiny		= $request->enterpriseid_destination;
			$t_groups->idAccAccDestiny			= $request->accountid_destination;
			$t_groups->idProvider				= $provider_id;
			$t_groups->provider_has_banks_id	= $provider_has_banks_id;
			$t_groups->provider_data_id			= $provider_data_id;
			$t_groups->save();

			$groups					= $t_groups->idgroups;
			
			if (isset($request->realPath) && count($request->realPath)>0) 
			{
				for ($i=0; $i < count($request->realPath); $i++) 
				{ 
					if ($request->realPath[$i] != "") 
					{
						$documents 					= new App\GroupsDocuments();
						$new_file_name = Files::rename($request->realPath[$i],$folio);
						$documents->path 			= $new_file_name;
						$documents->idgroups 		= $groupsID;
						$documents->save();
					}
				}
			}

			$deleteTaxes		= App\GroupsTaxes::where('idgroupsDetail',$detailID)->delete();
			$deleteRetentions	= App\GroupsRetention::where('idgroupsDetail',$detailID)->delete();
			$delete				= App\GroupsDetail::where('idgroups',$groupsID)->delete();

			if(isset($request->tamount) && count($request->tamount)>0)
			{
				for ($i=0; isset($request->tamount) && $i < count($request->tamount); $i++)
				{
					$t_detailGroups					= new App\GroupsDetail();
					$t_detailGroups->idgroups		= $groupsID;
					$t_detailGroups->quantity		= $request->tquanty[$i];
					$t_detailGroups->unit			= $request->tunit[$i];
					$t_detailGroups->description	= $request->tdescr[$i];
					$t_detailGroups->unitPrice		= $request->tprice[$i];
					$t_detailGroups->tax			= $request->tiva[$i];
					$t_detailGroups->discount		= $request->tdiscount[$i];
					$t_detailGroups->amount			= $request->tamount[$i];
					$t_detailGroups->typeTax		= $request->tivakind[$i];
					$t_detailGroups->subtotal		= $request->tquanty[$i] * $request->tprice[$i];
					$t_detailGroups->save();

					$idgroupsDetail     = $t_detailGroups->idgroupsDetail;
					$tamountadditional 	= 'tamountadditional'.$i;
					$tnameamount 		= 'tnameamount'.$i;
					if (isset($request->$tamountadditional) && $request->$tamountadditional != "") 
					{
						for ($d=0; $d < count($request->$tamountadditional); $d++) 
						{ 
							if ($request->$tamountadditional[$d] != "") 
							{
								$t_taxes 					= new App\GroupsTaxes();
								$t_taxes->name 				= $request->$tnameamount[$d];
								$t_taxes->amount 			= $request->$tamountadditional[$d];
								$t_taxes->idgroupsDetail 	= $idgroupsDetail;
								$t_taxes->save();
							}
						}
					}

					$tamountretention 	= 'tamountretention'.$i;
					$tnameretention 	= 'tnameretention'.$i;
					if (isset($request->$tamountretention) && $request->$tamountretention != "") 
					{
						for ($d=0; $d < count($request->$tamountretention); $d++) 
						{ 
							if ($request->$tamountretention[$d] != "") 
							{
								$t_retention 					= new App\GroupsRetention();
								$t_retention->name 				= $request->$tnameretention[$d];
								$t_retention->amount 			= $request->$tamountretention[$d];
								$t_retention->idgroupsDetail 	= $idgroupsDetail;
								$t_retention->save();
							}
						}
					}
				}
			}

			$alert 	= "swal('', '".Lang::get("messages.request_sent")."', 'success');";
			/*$emails = App\User::whereHas('module',function($q)
						{
							$q->where('id', 36);
						})
						->whereHas('inChargeDepGet',function($q) use ($t_request)
						{
							$q->where('departament_id', $t_request->idDepartment)
								->where('module_id',36);
						})
						->whereHas('inChargeEntGet',function($q) use ($t_request)
						{
							$q->where('enterprise_id', $t_request->idEnterprise)
								->where('module_id',36);
						})
						->where('active',1)
						->where('notification',1)
						->get();
			$emails	= App\User::join('user_has_department','users.id','user_has_department.user_id')
						->join('user_has_modules','users.id','user_has_modules.user_id')
						->where('user_has_modules.module_id',36)
						->where('user_has_department.departament_id',$request->departmentid)
						->where('users.active',1)
						->where('users.notification',1)
						->get();
			$user 	=  App\User::find($request->userid);
			if ($emails != "")
			{
				foreach ($emails as $email)
				{
					$name 			= $email->name.' '.$email->last_name.' '.$email->scnd_last_name;
					$to 			= $email->email;
					$kind 			= "Compra";
					$status 		= "Revisar";
					$date 			= Carbon::now();
					$requestUser	= $user->name.' '.$user->last_name.' '.$user->scnd_last_name;
					$url 			= route('purchase.review.edit',['id'=>$folio]);
					$subject 		= "Solicitud por Revisar";
					Mail::to($to)->send(new App\Mail\Notificacion($name,$kind,$status,$date,$url,$subject,$requestUser));
				}
			}*/
			return redirect('administration/movements-accounts')->with('alert',$alert);
		}
		else
		{
			return redirect('/');
		}
	}

	public function updateReviewGroups(Request $request,$id)
	{
		if(Auth::user()->module->where('id',150)->count()>0)
		{
			$data	= App\Module::find($this->module_id);
			$checkStatus    = App\RequestModel::find($id);

			if ($checkStatus->status == 4 || $checkStatus->status == 6) 
			{
				$alert = "swal('', '".Lang::get("messages.request_already_ruled")."', 'error');";
			}
			else
			{
				$review	= App\RequestModel::find($id);
				if ($request->status == "4")
				{
					for ($i=0; $i < count($request->t_idgroupsDetail); $i++) 
					{ 
						$idLabelsAssign = 'idLabelsAssign'.$i;
						if ($request->$idLabelsAssign != "") 
						{
							for ($d=0; $d < count($request->$idLabelsAssign); $d++) 
							{ 
								$labelPurchase					= new App\GroupsDetailLabel();
								$labelPurchase->idlabels		= $request->$idLabelsAssign[$d];
								$labelPurchase->idgroupsDetail	= $request->t_idgroupsDetail[$i];
								$labelPurchase->save();
							}
						}
					}

					$review->status			= $request->status;
					$review->idCheck		= Auth::user()->id;
					$review->checkComment	= $request->checkCommentA;
					$review->reviewDate 	= Carbon::now();
					$review->save();

					$idgroups 	= App\Groups::where('idFolio',$id)->first()->idgroups;

					$t_groups							= App\Groups::find($idgroups);
					$t_groups->idEnterpriseOriginR		= $request->enterpriseid_origin;
					$t_groups->idAreaOriginR			= $request->areaid_origin;
					$t_groups->idDepartamentOriginR		= $request->departmentid_origin;
					$t_groups->idAccAccOriginR			= $request->accountid_origin;
					$t_groups->idProjectOriginR			= $request->projectid_origin;
					$t_groups->idEnterpriseDestinyR		= $request->enterpriseid_destination;
					$t_groups->idAccAccDestinyR			= $request->accountid_destination;
					$t_groups->save();

					/*if ($request->idLabels != "")
					{
						$review->labels()->detach();
						$review->labels()->attach($request->idLabels,array('request_kind'=>'1'));
					}
					$emails = App\User::whereHas('module',function($q)
						{
							$q->where('id', 37);
						})
						->whereHas('inChargeDepGet',function($q) use ($review)
						{
							$q->where('departament_id', $review->idDepartamentR)
								->where('module_id',37);
						})
						->whereHas('inChargeEntGet',function($q) use ($review)
						{
							$q->where('enterprise_id', $review->idEnterpriseR)
								->where('module_id',37);
						})
						->where('active',1)
						->where('notification',1)
						->get();*/
					/*$emails	= App\User::join('user_has_department','users.id','user_has_department.user_id')
								->join('user_has_modules','users.id','user_has_modules.user_id')
								->where('user_has_modules.module_id',37)
								->where('user_has_department.departament_id',$review->idDepartamentR)
								->where('users.active',1)
								->where('users.notification',1)
								->get();*/
					/*$user 	= App\User::find($review->idRequest);
					if ($emails != "")
					{
						foreach ($emails as $email)
						{
							$name 			= $email->name.' '.$email->last_name.' '.$email->scnd_last_name;
							$to 			= $email->email;
							$kind 			= "Compra";
							$status 		= "Autorizar";
							$date 			= Carbon::now();
							$url 			= route('purchase.authorization.edit',['id'=>$id]);
							$subject 		= "Solicitud por Autorizar";
							$requestUser	= $user->name.' '.$user->last_name.' '.$user->scnd_last_name;
							Mail::to($to)->send(new App\Mail\Notificacion($name,$kind,$status,$date,$url,$subject,$requestUser));
						}
					}*/
				}
				elseif ($request->status == "6")
				{
					$review->status			= $request->status;
					$review->idCheck		= Auth::user()->id;
					$review->checkComment	= $request->checkCommentR;
					$review->reviewDate 	= Carbon::now();
					$review->save();

					/*$emailRequest 			= "";

					if ($review->idElaborate == $review->idRequest) 
					{
						$emailRequest 	= App\User::where('id',$review->idElaborate)
										->where('notification',1)
										->get();
					}
					else
					{
						$emailRequest 	= App\User::where('id',$review->idElaborate)
										->orWhere('id',$review->idRequest)
										->where('notification',1)
										->get();
					}
					
					if ($emailRequest != "")
					{
						foreach ($emailRequest as $email)
						{
							$name 			= $email->name.' '.$email->last_name.' '.$email->scnd_last_name;
							$to 			= $email->email;
							$kind 			= "Compra";
							$status 		= "RECHAZADA";
							$date 			= Carbon::now();
							$url 			= route('purchase.follow.edit',['id'=>$id]);
							$subject 		= "Estado de Solicitud";
							$requestUser	= null;
							Mail::to($to)->send(new App\Mail\Notificacion($name,$kind,$status,$date,$url,$subject,$requestUser));
						}
					}*/
				}
				$alert = "swal('', '".Lang::get("messages.request_updated")."', 'success');";
			}
			return searchRedirect(150, $alert, 'administration/movements-accounts');
		}
		else
		{
			return redirect('/');
		}
	}

	public function movements(Request $request)
	{
		if(Auth::user()->module->where('id',175)->count()>0)
		{
			$data  = App\Module::find($this->module_id);
			return view('administracion.movimientos_entre_cuentas.movimientos',
				[
					'id'        => $data['father'],
					'title'     => $data['name'],
					'details'   => $data['details'],
					'child_id'  => $this->module_id,
					'option_id' => 175
				]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function storeMovements(Request $request)
	{
		if(Auth::user()->module->where('id',175)->count()>0)
		{
			$data					= App\Module::find($this->module_id);
			$t_request				= new App\RequestModel();
			$t_request->kind		= 15;
			$t_request->taxPayment	= $request->fiscal;
			$t_request->fDate		= Carbon::now();
			$t_request->status		= 3;
			$t_request->idRequest	= $request->userid;
			$t_request->idElaborate	= Auth::user()->id;
			$t_request->save();
			$folio					= $t_request->folio;
			$kind					= $t_request->kind;
			
			$t_movements						= new App\MovementsEnterprise();
			$t_movements->title					= $request->title;
			$t_movements->datetitle				= $request->datetitle !="" ? Carbon::createFromFormat('d-m-Y',$request->datetitle)->format('Y-m-d') : null;
			$t_movements->tax					= 0;
			$t_movements->amount				= $request->amount;
			$t_movements->typeCurrency			= $request->type_currency;
			$t_movements->paymentDate			= $request->date !="" ? Carbon::createFromFormat('d-m-Y',$request->date)->format('Y-m-d') : null;
			$t_movements->idpaymentMethod		= $request->pay_mode;
			$t_movements->idEnterpriseOrigin	= $request->enterpriseid;
			$t_movements->idAccAccOrigin		= $request->accountid_origin;
			$t_movements->idEnterpriseDestiny	= $request->enterpriseid;
			$t_movements->idAccAccDestiny		= $request->accountid_destination;
			$t_movements->idFolio				= $folio;
			$t_movements->idKind				= $kind;
			$t_movements->save();
			
			// return $t_movements->idFolio;
			$movements = $t_movements->idmovementsEnterprise;
			
			if (isset($request->realPath) && count($request->realPath)>0) 
			{
				for ($i=0; $i < count($request->realPath); $i++) 
				{ 
					if ($request->realPath[$i] != "") 
					{
						$documents							= new App\MovementsEnterpriseDocuments();
						$new_file_name = Files::rename($request->realPath[$i],$folio);
						$documents->path					= $new_file_name;
						$documents->idmovementsEnterprise	= $movements;
						$documents->save();
					}
				}
			}


			$alert     = "swal('', '".Lang::get("messages.request_sent")."', 'success');";
			/*$emails = App\User::whereHas('module',function($q)
						{
							$q->where('id', 36);
						})
						->whereHas('inChargeDepGet',function($q) use ($t_request)
						{
							$q->where('departament_id', $t_request->idDepartment)
								->where('module_id',36);
						})
						->whereHas('inChargeEntGet',function($q) use ($t_request)
						{
							$q->where('enterprise_id', $t_request->idEnterprise)
								->where('module_id',36);
						})
						->where('active',1)
						->where('notification',1)
						->get();*/
			/*$emails	= App\User::join('user_has_department','users.id','user_has_department.user_id')
						->join('user_has_modules','users.id','user_has_modules.user_id')
						->where('user_has_modules.module_id',36)
						->where('user_has_department.departament_id',$request->departmentid)
						->where('users.active',1)
						->where('users.notification',1)
						->get();*/
			/*$user 	=  App\User::find($request->userid);
			if ($emails != "")
			{
				foreach ($emails as $email)
				{
					$name 			= $email->name.' '.$email->last_name.' '.$email->scnd_last_name;
					$to 			= $email->email;
					$kind 			= "Compra";
					$status 		= "Revisar";
					$date 			= Carbon::now();
					$requestUser	= $user->name.' '.$user->last_name.' '.$user->scnd_last_name;
					$url 			= route('purchase.review.edit',['id'=>$folio]);
					$subject 		= "Solicitud por Revisar";
					Mail::to($to)->send(new App\Mail\Notificacion($name,$kind,$status,$date,$url,$subject,$requestUser));
				}
			}*/
			return redirect('administration/movements-accounts')->with('alert',$alert);
		}
		else
		{
			return redirect('/error');
		}
	}

	public function unsentMovements(Request $request)
	{
		if(Auth::user()->module->where('id',175)->count()>0)
		{
			$data					= App\Module::find($this->module_id);
			$t_request				= new App\RequestModel();
			$t_request->kind		= 15;
			$t_request->taxPayment	= $request->fiscal;
			$t_request->fDate		= Carbon::now();
			$t_request->status		= 2;
			$t_request->idRequest	= $request->userid;
			$t_request->idElaborate	= Auth::user()->id;
			$t_request->save();
			$folio					= $t_request->folio;
			$kind					= $t_request->kind;

			$t_movements						= new App\MovementsEnterprise();
			$t_movements->title					= $request->title;
			$t_movements->datetitle				= $request->datetitle != "" ? Carbon::createFromFormat('d-m-Y',$request->datetitle)->format('Y-m-d') : null;
			$t_movements->tax					= 0;
			$t_movements->amount				= $request->amount;
			$t_movements->typeCurrency			= $request->type_currency;
			$t_movements->paymentDate			= $request->date !='' ? Carbon::createFromFormat('d-m-Y',$request->date)->format('Y-m-d') : null;
			$t_movements->idpaymentMethod		= $request->pay_mode;
			$t_movements->idEnterpriseOrigin	= $request->enterpriseid;
			$t_movements->idAccAccOrigin		= $request->accountid_origin;
			$t_movements->idEnterpriseDestiny	= $request->enterpriseid;
			$t_movements->idAccAccDestiny		= $request->accountid_destination;
			$t_movements->idFolio				= $folio;
			$t_movements->idKind				= $kind;
			$t_movements->save();

			$movements = $t_movements->idmovementsEnterprise;
			
			if (isset($request->realPath) && count($request->realPath)>0) 
			{
				for ($i=0; $i < count($request->realPath); $i++) 
				{ 
					if ($request->realPath[$i] != "") 
					{
						$documents							= new App\MovementsEnterpriseDocuments();
						$new_file_name = Files::rename($request->realPath[$i],$folio);
						$documents->path					= $new_file_name;
						$documents->idmovementsEnterprise	= $movements;
						$documents->save();
					}
				}
			}
			
			$id		= $folio;
			$alert	= "swal('', '".Lang::get("messages.request_saved")."', 'success');";
			
			return redirect()->route('movements-accounts.follow.edit',['id'    =>$id])->with('alert',$alert);
		}
		else
		{
			return redirect('/error');
		}
	}

	public function unsentFollowMovements(Request $request,$id)
	{
		if(Auth::user()->module->where('id',175)->count()>0)
		{
			$data					= App\Module::find($this->module_id);
			$t_request				= App\RequestModel::find($id);
			$t_request->kind		= 15;
			$t_request->taxPayment	= $request->fiscal;
			$t_request->status		= 2;
			$t_request->idRequest	= $request->userid;
			$t_request->save();
			$folio					= $t_request->folio;
			$kind					= $t_request->kind;

			$movementsID	= App\MovementsEnterprise::where('idFolio',$folio)->first()->idmovementsEnterprise;
			
			$t_movements						= App\MovementsEnterprise::find($movementsID);
			$t_movements->title					= $request->title;
			$t_movements->datetitle				= $request->datetitle != "" ? Carbon::createFromFormat('d-m-Y',$request->datetitle)->format('Y-m-d') : null;
			$t_movements->tax					= 0;
			$t_movements->amount				= $request->amount;
			$t_movements->typeCurrency			= $request->type_currency;
			$t_movements->paymentDate			= $request->date !='' ? Carbon::createFromFormat('d-m-Y',$request->date)->format('Y-m-d') : null;
			$t_movements->idpaymentMethod		= $request->pay_mode;
			$t_movements->idEnterpriseOrigin	= $request->enterpriseid;
			$t_movements->idAccAccOrigin		= $request->accountid_origin;
			$t_movements->idEnterpriseDestiny	= $request->enterpriseid;
			$t_movements->idAccAccDestiny		= $request->accountid_destination;
			$t_movements->idFolio				= $folio;
			$t_movements->idKind				= $kind;
			$t_movements->save();

			$movements = $t_movements->idmovementsEnterprise;
			
			if (isset($request->realPath) && count($request->realPath)>0) 
			{
				for ($i=0; $i < count($request->realPath); $i++) 
				{ 
					if ($request->realPath[$i] != "") 
					{
						$documents							= new App\MovementsEnterpriseDocuments();
						$new_file_name = Files::rename($request->realPath[$i],$folio);
						$documents->path					= $new_file_name;
						$documents->idmovementsEnterprise	= $movements;
						$documents->save();
					}
				}
			}

			$alert     = "swal('', '".Lang::get("messages.request_saved")."', 'success');";
			
			return redirect()->route('movements-accounts.follow.edit',['id'=>$id])->with('alert',$alert);
		}
		else
		{
			return redirect('/error');
		}
	}

	public function updateFollowMovements(Request $request,$id)
	{
		if(Auth::user()->module->where('id',175)->count()>0)
		{
			$data					= App\Module::find($this->module_id);
			$t_request				= App\RequestModel::find($id);
			$t_request->kind		= 15;
			$t_request->taxPayment	= $request->fiscal;
			$t_request->status		= 3;
			$t_request->idRequest	= $request->userid;
			$t_request->save();
			$folio					= $t_request->folio;
			$kind					= $t_request->kind;

			$movementsID	= App\MovementsEnterprise::where('idFolio',$folio)->first()->idmovementsEnterprise;
			
			$t_movements						= App\MovementsEnterprise::find($movementsID);
			$t_movements->title					= $request->title;
			$t_movements->datetitle				= $request->datetitle !="" ? Carbon::creatFromFromat('d-m-Y',$request->datetitle)->format('Y-m-d') : null;
			$t_movements->tax					= 0;
			$t_movements->amount				= $request->amount;
			$t_movements->typeCurrency			= $request->type_currency;
			$t_movements->paymentDate			= $request->date !='' ? Carbon::creatFromFromat('d-m-Y',$request->date)->format('Y-m-d') : null;
			$t_movements->idpaymentMethod		= $request->pay_mode;
			$t_movements->idEnterpriseOrigin	= $request->enterpriseid;
			$t_movements->idAccAccOrigin		= $request->accountid_origin;
			$t_movements->idEnterpriseDestiny	= $request->enterpriseid;
			$t_movements->idAccAccDestiny		= $request->accountid_destination;
			$t_movements->idFolio				= $folio;
			$t_movements->idKind				= $kind;
			$t_movements->save();

			$movements = $t_movements->idmovementsEnterprise;
			
			if (isset($request->realPath) && count($request->realPath)>0) 
			{
				for ($i=0; $i < count($request->realPath); $i++) 
				{ 
					if ($request->realPath[$i] != "") 
					{
						$documents						= new App\MovementsEnterpriseDocuments();
						$new_file_name = Files::rename($request->realPath[$i],$folio);
						$documents->path				= $new_file_name;
						$documents->idmovementsEnterprise	= $movements;
						$documents->save();
					}
				}
			}

			$alert     = "swal('', '".Lang::get("messages.request_sent")."', 'success');";
			/*$emails = App\User::whereHas('module',function($q)
						{
							$q->where('id', 36);
						})
						->whereHas('inChargeDepGet',function($q) use ($t_request)
						{
							$q->where('departament_id', $t_request->idDepartment)
								->where('module_id',36);
						})
						->whereHas('inChargeEntGet',function($q) use ($t_request)
						{
							$q->where('enterprise_id', $t_request->idEnterprise)
								->where('module_id',36);
						})
						->where('active',1)
						->where('notification',1)
						->get();*/
			/*$emails	= App\User::join('user_has_department','users.id','user_has_department.user_id')
						->join('user_has_modules','users.id','user_has_modules.user_id')
						->where('user_has_modules.module_id',36)
						->where('user_has_department.departament_id',$request->departmentid)
						->where('users.active',1)
						->where('users.notification',1)
						->get();*/
			/*$user 	=  App\User::find($request->userid);
			if ($emails != "")
			{
				foreach ($emails as $email)
				{
					$name 			= $email->name.' '.$email->last_name.' '.$email->scnd_last_name;
					$to 			= $email->email;
					$kind 			= "Compra";
					$status 		= "Revisar";
					$date 			= Carbon::now();
					$requestUser	= $user->name.' '.$user->last_name.' '.$user->scnd_last_name;
					$url 			= route('purchase.review.edit',['id'=>$folio]);
					$subject 		= "Solicitud por Revisar";
					Mail::to($to)->send(new App\Mail\Notificacion($name,$kind,$status,$date,$url,$subject,$requestUser));
				}
			}*/
			return redirect('administration/movements-accounts')->with('alert',$alert);
		}
		else
		{
			return redirect('/error');
		}
	}

	public function updateReviewMovements(Request $request,$id)
	{
		if(Auth::user()->module->where('id',150)->count()>0)
		{
			$data	= App\Module::find($this->module_id);
			$checkStatus    = App\RequestModel::find($id);

			if ($checkStatus->status == 4 || $checkStatus->status == 6) 
			{
				$alert = "swal('', '".Lang::get("messages.request_already_ruled")."', 'error');";
			}
			else
			{
				$review	= App\RequestModel::find($id);
				if ($request->status == "4")
				{

					$review->status			= $request->status;
					$review->idCheck		= Auth::user()->id;
					$review->checkComment	= $request->checkCommentA;
					$review->reviewDate 	= Carbon::now();
					$review->save();

					$idmovementsEnterprise 	= App\MovementsEnterprise::where('idFolio',$id)->first()->idmovementsEnterprise;

					$t_groups							= App\MovementsEnterprise::find($idmovementsEnterprise);
					$t_groups->idEnterpriseOriginR		= $request->enterpriseid;
					$t_groups->idAccAccOriginR			= $request->accountid_origin;
					$t_groups->idEnterpriseDestinyR		= $request->enterpriseid;
					$t_groups->idAccAccDestinyR			= $request->accountid_destination;
					$t_groups->save();

					/*if ($request->idLabels != "")
					{
						$review->labels()->detach();
						$review->labels()->attach($request->idLabels,array('request_kind'=>'1'));
					}
					$emails = App\User::whereHas('module',function($q)
						{
							$q->where('id', 37);
						})
						->whereHas('inChargeDepGet',function($q) use ($review)
						{
							$q->where('departament_id', $review->idDepartamentR)
								->where('module_id',37);
						})
						->whereHas('inChargeEntGet',function($q) use ($review)
						{
							$q->where('enterprise_id', $review->idEnterpriseR)
								->where('module_id',37);
						})
						->where('active',1)
						->where('notification',1)
						->get();*/
					/*$emails	= App\User::join('user_has_department','users.id','user_has_department.user_id')
								->join('user_has_modules','users.id','user_has_modules.user_id')
								->where('user_has_modules.module_id',37)
								->where('user_has_department.departament_id',$review->idDepartamentR)
								->where('users.active',1)
								->where('users.notification',1)
								->get();*/
					/*$user 	= App\User::find($review->idRequest);
					if ($emails != "")
					{
						foreach ($emails as $email)
						{
							$name 			= $email->name.' '.$email->last_name.' '.$email->scnd_last_name;
							$to 			= $email->email;
							$kind 			= "Compra";
							$status 		= "Autorizar";
							$date 			= Carbon::now();
							$url 			= route('purchase.authorization.edit',['id'=>$id]);
							$subject 		= "Solicitud por Autorizar";
							$requestUser	= $user->name.' '.$user->last_name.' '.$user->scnd_last_name;
							Mail::to($to)->send(new App\Mail\Notificacion($name,$kind,$status,$date,$url,$subject,$requestUser));
						}
					}*/
				}
				elseif ($request->status == "6")
				{
					$review->status			= $request->status;
					$review->idCheck		= Auth::user()->id;
					$review->checkComment	= $request->checkCommentR;
					$review->reviewDate 	= Carbon::now();
					$review->save();

					/*$emailRequest 			= "";

					if ($review->idElaborate == $review->idRequest) 
					{
						$emailRequest 	= App\User::where('id',$review->idElaborate)
										->where('notification',1)
										->get();
					}
					else
					{
						$emailRequest 	= App\User::where('id',$review->idElaborate)
										->orWhere('id',$review->idRequest)
										->where('notification',1)
										->get();
					}
					
					if ($emailRequest != "")
					{
						foreach ($emailRequest as $email)
						{
							$name 			= $email->name.' '.$email->last_name.' '.$email->scnd_last_name;
							$to 			= $email->email;
							$kind 			= "Compra";
							$status 		= "RECHAZADA";
							$date 			= Carbon::now();
							$url 			= route('purchase.follow.edit',['id'=>$id]);
							$subject 		= "Estado de Solicitud";
							$requestUser	= null;
							Mail::to($to)->send(new App\Mail\Notificacion($name,$kind,$status,$date,$url,$subject,$requestUser));
						}
					}*/
				}
				$alert = "swal('', '".Lang::get("messages.request_updated")."', 'success');";
			}
			return searchRedirect(150, $alert, 'administration/movements-accounts');
		}
		else
		{
			return redirect('/');
		}
	}

	public function search(Request $request)
	{
		if(Auth::user()->module->where('id',149)->count()>0)
		{
			if(Auth::user()->globalCheck->where('module_id',149)->count()>0)
			{
				$global_permission =  Auth::user()->globalCheck->where('module_id',149)->first()->global_permission;
			}
			else
			{
				$global_permission = 0;
			}

			$data		= App\Module::find($this->module_id);
			$name		= $request->name;
			$folio		= $request->folio;
			$status		= $request->status;
			$mindate	= $request->mindate!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate) : null;
			$maxdate	= $request->maxdate!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate) : null;
			$kind		= $request->kind;

			$requests 		= App\RequestModel::whereIn('kind',[11,12,13,14,15])
							->where(function ($q) use ($global_permission)
							{
								if ($global_permission == 0) 
								{
									$q->where('idElaborate',Auth::user()->id)->orWhere('idRequest',Auth::user()->id);
								}
							})
							->where(function ($query) use ($name, $mindate, $maxdate, $folio,$kind,$status)
								{
									if($name != "")
									{
										$query->where(function($query) use($name)
										{
											$query->whereHas('requestUser', function($queryU) use($name)
											{
												$queryU->where(DB::raw("CONCAT_WS(' ',name,last_name,scnd_last_name)"),'LIKE','%'.preg_replace("/\s+/", "%", $name).'%');
											})
											->orWhereHas('elaborateUser', function($queryU) use($name)
											{
												$queryU->where(DB::raw("CONCAT_WS(' ',name,last_name,scnd_last_name)"),'LIKE','%'.preg_replace("/\s+/", "%", $name).'%');
											});
										});
									}
									if($folio != "")
									{
										$query->where('request_models.folio',$folio);
									}
									if($mindate != "" && $maxdate != "")
									{
										$query->whereBetween('fDate',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 23:59:59')]);
									}
									if ($kind != "") 
									{
										$query->where('kind',$kind);
									}
									if ($status != "") 
									{
										$query->where('status',$status);
									}
								})
							->orderBy('fDate','DESC')
							->orderBy('folio','DESC')
							->paginate(10);

			return view('administracion.movimientos_entre_cuentas.busqueda',
				[
					'id'		=> $data['father'],
					'title'		=> $data['name'],
					'details'	=> $data['details'],
					'child_id'	=> $this->module_id,
					'option_id'	=> 149,
					'requests'	=> $requests,
					'name'		=> $name, 
					'mindate'	=> $request->mindate,
					'maxdate'	=> $request->maxdate,
					'folio'		=> $folio,
					'status'	=> $status,
					'kind'		=> $kind
				]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function follow(Request $request,$id)
	{
		if(Auth::user()->module->where('id',149)->count()>0)
		{
			if(Auth::user()->globalCheck->where('module_id',149)->count()>0)
			{
				$global_permission =  Auth::user()->globalCheck->where('module_id',149)->first()->global_permission;
			}
			else
			{
				$global_permission = 0;
			}

			$data		= App\Module::find($this->module_id);
			$request	= App\RequestModel::whereIn('kind',[11,12,13,14,15])
						->where(function ($q) use ($global_permission)
						{
							if ($global_permission == 0) 
							{
								$q->where('idElaborate',Auth::user()->id)->orWhere('idRequest',Auth::user()->id);
							}
						})
						->find($id);

			if($request != "")
			{
				switch ($request->kind) 
				{
					case 11:
						return view('administracion.movimientos_entre_cuentas.seguimiento-ajuste',
						[

							'id'		=> $data['father'],
							'title'		=> $data['name'],
							'details'	=> $data['details'],
							'child_id'	=> $this->module_id,
							'option_id'	=> 149,
							'request'	=> $request
						]);
						break;

					case 12:
						return view('administracion.movimientos_entre_cuentas.seguimiento-prestamo',
						[

							'id'		=> $data['father'],
							'title'		=> $data['name'],
							'details'	=> $data['details'],
							'child_id'	=> $this->module_id,
							'option_id'	=> 149,
							'request'	=> $request
						]);
						break;

					case 13:
						return view('administracion.movimientos_entre_cuentas.seguimiento-compra',
						[

							'id'		=> $data['father'],
							'title'		=> $data['name'],
							'details'	=> $data['details'],
							'child_id'	=> $this->module_id,
							'option_id'	=> 149,
							'request'	=> $request
						]);
						break;

					case 14:
						return view('administracion.movimientos_entre_cuentas.seguimiento-grupos',
						[

							'id'		=> $data['father'],
							'title'		=> $data['name'],
							'details'	=> $data['details'],
							'child_id'	=> $this->module_id,
							'option_id'	=> 149,
							'request'	=> $request
						]);
						break;

					case 15:
						return view('administracion.movimientos_entre_cuentas.seguimiento-movimientos',
						[

							'id'		=> $data['father'],
							'title'		=> $data['name'],
							'details'	=> $data['details'],
							'child_id'	=> $this->module_id,
							'option_id'	=> 149,
							'request'	=> $request
						]);
						break;
					
					default:
						# code...
						break;
				}
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

	public function getProviders(Request $request)
	{
		if($request->ajax())
		{
			$paginate	= "";
			$providers	= App\Provider::where(function($query) use ($request)
			{
				$query->where('rfc','LIKE','%'.$request->search.'%')
					->orWhere('businessName','LIKE','%'.$request->search.'%');
			})
			->where('status',2)
			->orderBy('idProvider','DESC')
			->paginate(10);
			if (count($providers) > 0)
			{
					$table		= "";
					$modelHead	=	[];
					$body		=	[];
					$modelBody	=	[];
					$modelHead	=
					[
						[
							["value"	=>	"ID"],
							["value"	=>	"Nombre"],
							["value"	=>	"RFC"],
							["value"	=>	"Acción"],
						]
					];
					foreach($providers as $provider)
					{
						$providerJSON['provider']	= $provider;
						$providerJSON['banks']		= $provider->providerData->providerBank;
					
						$body	=
						[
							[
								"content"	=>	["label"	=>	$provider->idProvider],
							],
							[
								"content"	=>	["label"	=>	$provider->businessName],
							],
							[
								"content"	=>	["label"	=>	$provider->rfc],
							],
							[
								"content"	=>
								[
									[
										"kind"			=>	"components.buttons.button",
										"variant"		=>	"success",
										"classEx"		=>	"edit",
										"attributeEx"	=>	"type=\"button\" value=\"".$provider->idProvider."\"",
										"label"			=>	"Seleccionar"
									],
									[
										"kind"			=>	"components.inputs.input-text",
										"attributeEx"	=>	"type=\"hidden\" id=\"provider_".$provider->idProvider."\" value='".base64_encode(json_encode($providerJSON))."'",
									],
								]
							],
						];
						$modelBody[]	=	$body;
					}
					$table	.= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.tables.table",
					[
						"modelHead"			=>	$modelHead,
						"modelBody"			=>	$modelBody,
						"attributeEx"		=>	"id=\"table-provider\"",
						"classExBody"		=>	"table",
					])));
				$pagination = "<center class='pagination mt-5'> $providers </center>";
				return Response(html_entity_decode($table.$pagination));

			}
			else
			{
				$notfound	=html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.labels.not-found", ["text" => "No se encontraron proveedores registrados"])));
				return Response($notfound);
			}
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
					\Storage::disk('public')->delete('/docs/movements/'.$request->realPath[$i]);
				}
			}

			if($request->file('path'))
			{
				$extention				= strtolower($request->path->getClientOriginalExtension());
				$nameWithoutExtention	= 'AdG'.round(microtime(true) * 1000).'_doc.';
				$name					= $nameWithoutExtention.$extention;
				$destinity				= '/docs/movements/'.$name;
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

	public function review(Request $request)
	{
		if(Auth::user()->module->where('id',150)->count()>0)
		{
			$data		= App\Module::find($this->module_id);
			$name		= $request->name;
			$folio		= $request->folio;
			$mindate	= $request->mindate!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate) : null;
			$maxdate	= $request->maxdate!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate) : null;
			$kind		= $request->kind;
			$requests	= App\RequestModel::whereIn('kind',[11,12,13,14,15])
						->where('status',3)
						->where(function ($query) use ($name, $mindate, $maxdate, $folio,$kind)
							{
								if($name != "")
								{
									$query->where(function($query) use($name)
									{
										$query->whereHas('requestUser', function($queryU) use($name)
										{
											$queryU->where(DB::raw("CONCAT_WS(' ',name,last_name,scnd_last_name)"),'LIKE','%'.preg_replace("/\s+/", "%", $name).'%');
										})
										->orWhereHas('elaborateUser', function($queryU) use($name)
										{
											$queryU->where(DB::raw("CONCAT_WS(' ',name,last_name,scnd_last_name)"),'LIKE','%'.preg_replace("/\s+/", "%", $name).'%');
										});
									});
								}
								if($folio != "")
								{
									$query->where('request_models.folio',$folio);
								}
								if($mindate != "" && $maxdate != "")
								{
									$query->whereBetween('fDate',[$mindate->format('Y-m-d 00:00:00'), $maxdate->format('Y-m-d 23:59:59')]);
								}
								if ($kind != "") 
								{
									$query->where('kind',$kind);
								}
							})
						->orderBy('fDate','DESC')
						->orderBy('folio','DESC')
						->paginate(10);
		
			return response(
				view('administracion.movimientos_entre_cuentas.revision',
					[
						'id'		=> $data['father'],
						'title'		=> $data['name'],
						'details'	=> $data['details'],
						'child_id'	=> $this->module_id,
						'option_id'	=> 150,
						'requests'	=> $requests,
						'name'		=> $name, 
						'mindate'	=> $request->mindate,
						'maxdate'	=> $request->maxdate,
						'folio'		=> $folio,
						'kind'		=> $kind
					]
				)
			)
			->cookie(
				'urlSearch', storeUrlCookie(150), 2880
			);
		}
		else
		{
			return redirect('/');
		}
	}

	public function showReview(Request $request,$id)
	{
		if(Auth::user()->module->where('id',150)->count()>0)
		{
			$data			= App\Module::find($this->module_id);
			$request		= App\RequestModel::whereIn('kind',[11,12,13,14,15])
									->where('status',3)
									->find($id);
			
			if($request != "")
			{
				switch ($request->kind) 
				{
					case 11:
						return view('administracion.movimientos_entre_cuentas.revision-ajuste',
							[
								'id'		=> $data['father'],
								'title'		=> $data['name'],
								'details'	=> $data['details'],
								'child_id'	=> $this->module_id,
								'option_id'	=> 150,
								'request'	=> $request
							]
						);
						break;

					case 12:
						return view('administracion.movimientos_entre_cuentas.revision-prestamo',
							[

								'id'		=> $data['father'],
								'title'		=> $data['name'],
								'details'	=> $data['details'],
								'child_id'	=> $this->module_id,
								'option_id'	=> 150,
								'request'	=> $request
							]
						);
						break;

					case 13:
						return view('administracion.movimientos_entre_cuentas.revision-compra',
							[

								'id'		=> $data['father'],
								'title'		=> $data['name'],
								'details'	=> $data['details'],
								'child_id'	=> $this->module_id,
								'option_id'	=> 150,
								'request'	=> $request
							]
						);
						break;

					case 14:
						return view('administracion.movimientos_entre_cuentas.revision-grupos',
							[

								'id'		=> $data['father'],
								'title'		=> $data['name'],
								'details'	=> $data['details'],
								'child_id'	=> $this->module_id,
								'option_id'	=> 150,
								'request'	=> $request
							]
						);
						break;

					case 15:
						return view('administracion.movimientos_entre_cuentas.revision-movimientos',
							[

								'id'		=> $data['father'],
								'title'		=> $data['name'],
								'details'	=> $data['details'],
								'child_id'	=> $this->module_id,
								'option_id'	=> 150,
								'request'	=> $request
							]
						);
						break;
					
					default:
						# code...
						break;
				}
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

	public function authorization(Request $request)
	{
		if(Auth::user()->module->where('id',151)->count()>0)
		{
			$data		= App\Module::find($this->module_id);
			$name		= $request->name;
			$folio		= $request->folio;
			$mindate	= $request->mindate!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate) : null;
			$maxdate	= $request->maxdate!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate) : null;
			$kind		= $request->kind;
			$requests	= App\RequestModel::whereIn('kind',[11,12,13,14,15])
						->where('status',4)
						->where(function ($query) use ($name, $mindate, $maxdate, $folio,$kind)
							{
								if($name != "")
								{
									$query->where(function($query) use($name)
									{
										$query->whereHas('requestUser', function($queryU) use($name)
										{
											$queryU->where(DB::raw("CONCAT_WS(' ',name,last_name,scnd_last_name)"),'LIKE','%'.preg_replace("/\s+/", "%", $name).'%');
										})
										->orWhereHas('elaborateUser', function($queryU) use($name)
										{
											$queryU->where(DB::raw("CONCAT_WS(' ',name,last_name,scnd_last_name)"),'LIKE','%'.preg_replace("/\s+/", "%", $name).'%');
										});
									});
								}
								if($folio != "")
								{
									$query->where('request_models.folio',$folio);
								}
								if($mindate != "" && $maxdate != "")
								{
									$query->whereBetween('reviewDate',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 23:59:59').'']);
								}
								if ($kind != "") 
								{
									$query->where('kind',$kind);
								}
							})
						->orderBy('fDate','DESC')
						->orderBy('folio','DESC')
						->paginate(10);
			return response(
				view('administracion.movimientos_entre_cuentas.autorizacion',
					[
						'id'		=> $data['father'],
						'title'		=> $data['name'],
						'details'	=> $data['details'],
						'child_id'	=> $this->module_id,
						'option_id'	=> 151,
						'requests'	=> $requests,
						'name'		=> $name, 
						'mindate'	=> $request->mindate,
						'maxdate'	=> $request->maxdate,
						'folio'		=> $folio,
						'kind'		=> $kind
					]
				)
			)
			->cookie(
				'urlSearch', storeUrlCookie(151), 2880
			);
		}
		else
		{
			return redirect('/');
		}
	}

	public function showAuthorization(Request $request,$id)
	{
		if(Auth::user()->module->where('id',151)->count()>0)
		{
			$data			= App\Module::find($this->module_id);
			$request		= App\RequestModel::whereIn('kind',[11,12,13,14,15])
									->where('status',4)
									->find($id);
			
			if($request != "")
			{
				switch ($request->kind) 
				{
					case 11:
						return view('administracion.movimientos_entre_cuentas.autorizacion-ajuste',
							[

								'id'		=> $data['father'],
								'title'		=> $data['name'],
								'details'	=> $data['details'],
								'child_id'	=> $this->module_id,
								'option_id'	=> 151,
								'request'	=> $request
							]
						);
						break;

					case 12:
						return view('administracion.movimientos_entre_cuentas.autorizacion-prestamo',
							[

								'id'		=> $data['father'],
								'title'		=> $data['name'],
								'details'	=> $data['details'],
								'child_id'	=> $this->module_id,
								'option_id'	=> 151,
								'request'	=> $request
							]
						);
						break;

					case 13:
						return view('administracion.movimientos_entre_cuentas.autorizacion-compra',
							[

								'id'		=> $data['father'],
								'title'		=> $data['name'],
								'details'	=> $data['details'],
								'child_id'	=> $this->module_id,
								'option_id'	=> 151,
								'request'	=> $request
							]
						);
						break;

					case 14:			
						return view('administracion.movimientos_entre_cuentas.autorizacion-grupos',
							[

								'id'		=> $data['father'],
								'title'		=> $data['name'],
								'details'	=> $data['details'],
								'child_id'	=> $this->module_id,
								'option_id'	=> 151,
								'request'	=> $request
							]
						);
						break;

					case 15:
						return view('administracion.movimientos_entre_cuentas.autorizacion-movimientos',
							[

								'id'		=> $data['father'],
								'title'		=> $data['name'],
								'details'	=> $data['details'],
								'child_id'	=> $this->module_id,
								'option_id'	=> 151,
								'request'	=> $request
							]
						);
						break;
					
					default:
						# code...
						break;
				}
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

	public function updateAuthorization(Request $request, $id)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$checkStatus    = App\RequestModel::find($id);

			if ($checkStatus->status == 5 || $checkStatus->status == 7) 
			{
				$alert = "swal('', '".Lang::get("messages.request_already_ruled")."', 'error');";
			}
			else
			{
				$data							= App\Module::find($this->module_id);
				$authorize						= App\RequestModel::find($id);
				$authorize->status				= $request->status;
				$authorize->idAuthorize			= Auth::user()->id;
				$authorize->authorizeComment	= $request->authorizeCommentA;
				$authorize->authorizeDate 		= Carbon::now();
				$authorize->save();
				$alert = "swal('', '".Lang::get("messages.request_updated")."', 'success');";

				$emailRequest 			= "";
					
				if ($authorize->idElaborate == $authorize->idRequest) 
				{
					$emailRequest 	= App\User::where('id',$authorize->idElaborate)
									->where('notification',1)
									->get();
				}
				else
				{
					$emailRequest 	= App\User::where('id',$authorize->idElaborate)
									->orWhere('id',$authorize->idRequest)
									->where('notification',1)
									->get();
				}
				/*
				$emailPay 		= App\User::join('user_has_modules','users.id','user_has_modules.user_id')
									->where('user_has_modules.module_id',90)
									->where('users.active',1)
									->where('users.notification',1)
									->get();
				$user 			= App\User::find($authorize->idRequest);
				if ($emailRequest != "")
				{
					foreach ($emailRequest as $email)
					{
						$name 			= $email->name.' '.$email->last_name.' '.$email->scnd_last_name;
						$to 			= $email->email;
						$kind 			= "Compra";
						if ($request->status == 5)
						{
							$status = "AUTORIZADA";
						}
						else
						{
							$status = "RECHAZADA";
						}
						$date 			= Carbon::now();
						$url 			= route('purchase.follow.edit',['id'=>$id]);
						$subject 		= "Estado de Solicitud";
						$requestUser 	= null;
						Mail::to($to)->send(new App\Mail\Notificacion($name,$kind,$status,$date,$url,$subject,$requestUser));
					}
				}
				if ($request->status == 5)
				{
					if ($emailPay != "")
					{
						foreach ($emailPay as $email)
						{
							$name 			= $email->name.' '.$email->last_name.' '.$email->scnd_last_name;
							$to 			= $email->email;
							$kind 			= "Compra";
							$status 		= "Pendiente";
							$date 			= Carbon::now();
							$url 			= route('payments.review.edit',['id'=>$id]);
							$subject 		= "Solicitud Pendiente de Pago";
							$requestUser	= $user->name.' '.$user->last_name.' '.$user->scnd_last_name;
							Mail::to($to)->send(new App\Mail\Notificacion($name,$kind,$status,$date,$url,$subject,$requestUser));
						}
					}
				}*/
			}
			return searchRedirect(151, $alert, 'administration/movements-accounts');
		}
	}


	public function followExcel(Request $request)
	{
		if(Auth::user()->module->where('id',149)->count()>0)
		{
			$data       = App\Module::find($this->module_id);
			$name       = $request->name;
			$folio      = $request->folio;
			$mindate    = $request->mindate!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate) : null;
			$maxdate    = $request->maxdate!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate) : null;
			$kind       = $request->kind;
			$status		= $request->status;

			if(Auth::user()->globalCheck->where('module_id',149)->count()>0)
			{
				$global_permission =  Auth::user()->globalCheck->where('module_id',149)->first()->global_permission;
			}
			else
			{
				$global_permission = 0;
			}

			$requestsAdjustment = DB::table('request_models')
							->selectRaw('
								request_models.folio as folio,
								status_requests.description as status,
								adjustments.title as title,
								IF(adjustments.datetitle IS NOT NULL, DATE_FORMAT(adjustments.datetitle, "%d-%m-%Y"),"") as datetitle,
								CONCAT_WS(" ",request_user.name,request_user.last_name,request_user.scnd_last_name) as request_user,
								CONCAT_WS(" ",elaborate_user.name,elaborate_user.last_name,elaborate_user.scnd_last_name) as elaborate_user,
								DATE_FORMAT(request_models.fDate, "%d-%m-%Y %H:%i") as date,
								adjustments.commentaries as commentaries,
								IFNULL(enterprise_origin_reviewed.name, enterprise_origin.name) as enterprise_origin,
								IFNULL(enterprise_destiny_reviewed.name, enterprise_destiny.name) as enterprise_destiny,
								IFNULL(direction_destiny_reviewed.name, direction_destiny.name) as direction_destiny,
								IFNULL(department_destiny_reviewed.name, department_destiny.name) as department_destiny,
								IF(account_destiny_reviewed.account IS NOT NULL,CONCAT_WS(" - ",account_destiny_reviewed.account,account_destiny_reviewed.description), CONCAT_WS(" - ",account_destiny.account,account_destiny.description)) as account_destiny,
								IFNULL(project_destiny_reviewed.proyectName, project_destiny.proyectName) as project_destiny,
								adjustments.currency as currency,
								DATE_FORMAT(adjustments.paymentDate, "%d-%m-%Y") as payment_date,
								payment_methods.method as payment_method,
								adjustments.amount as amount
							')
							->leftJoin('adjustments','adjustments.idFolio','request_models.folio')
							->leftJoin('status_requests','request_models.status','status_requests.idrequestStatus')
							->leftJoin('users as request_user','request_models.idRequest','request_user.id')
							->leftJoin('users as elaborate_user','request_models.idElaborate','elaborate_user.id')
							->leftJoin('enterprises as enterprise_origin','adjustments.idEnterpriseOrigin','enterprise_origin.id')
							->leftJoin('enterprises as enterprise_destiny','adjustments.idEnterpriseDestiny','enterprise_destiny.id')
							->leftJoin('areas as direction_destiny','adjustments.idAreaDestiny','direction_destiny.id')
							->leftJoin('departments as department_destiny','adjustments.idDepartamentDestiny','department_destiny.id')
							->leftJoin('accounts as account_destiny','adjustments.idAccAccDestiny','account_destiny.idAccAcc')
							->leftJoin('projects as project_destiny','adjustments.idProjectDestiny','project_destiny.idproyect')
							->leftJoin('enterprises as enterprise_origin_reviewed','adjustments.idEnterpriseOriginR','enterprise_origin_reviewed.id')
							->leftJoin('enterprises as enterprise_destiny_reviewed','adjustments.idEnterpriseDestinyR','enterprise_destiny_reviewed.id')
							->leftJoin('areas as direction_destiny_reviewed','adjustments.idAreaDestinyR','direction_destiny_reviewed.id')
							->leftJoin('departments as department_destiny_reviewed','adjustments.idDepartamentDestinyR','department_destiny_reviewed.id')
							->leftJoin('accounts as account_destiny_reviewed','adjustments.idAccAccDestinyR','account_destiny_reviewed.idAccAcc')
							->leftJoin('projects as project_destiny_reviewed','adjustments.idProjectDestinyR','project_destiny_reviewed.idproyect')
							->leftJoin('payment_methods','payment_methods.idpaymentMethod','adjustments.idpaymentMethod')
							->where('request_models.kind',11)
							->where(function ($q) use ($global_permission)
							{
								if ($global_permission == 0) 
								{
									$q->where('idElaborate',Auth::user()->id)->orWhere('idRequest',Auth::user()->id);
								}
							})
							->where(function ($query) use ($name, $mindate, $maxdate, $folio,$kind)
							{
								if($name != "")
								{
									$query->where(function($q) use($name)
									{
										$q->where(DB::raw("CONCAT_WS(' ',request_user.name,request_user.last_name,request_user.scnd_last_name)"),'LIKE','%'.$name.'%')
											->orWhere(DB::raw("CONCAT_WS(' ',elaborate_user.name,elaborate_user.last_name,elaborate_user.scnd_last_name)"),'LIKE','%'.$name.'%');
									});
								}
								if($folio != "")
								{
									$query->where('request_models.folio',$folio);
								}
								if($mindate != "" && $maxdate != "")
								{
									$query->whereBetween('request_models.fDate',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 23:59:59')]);
								}
								if ($kind != "") 
								{
									$query->where('request_models.kind',$kind);
								}
							})
							->orderBy('request_models.fDate','DESC')
							->orderBy('request_models.folio','DESC')
							->get();

			$requestsLoan 	= DB::table('request_models')
							->selectRaw('
								request_models.folio as folio,
								status_requests.description as status,
								loan_enterprises.title as title,
								IF(loan_enterprises.datetitle IS NOT NULL, DATE_FORMAT(loan_enterprises.datetitle, "%d-%m-%Y"),"") as datetitle,
								CONCAT_WS(" ",request_user.name,request_user.last_name,request_user.scnd_last_name) as request_user,
								CONCAT_WS(" ",elaborate_user.name,elaborate_user.last_name,elaborate_user.scnd_last_name) as elaborate_user,
								DATE_FORMAT(request_models.fDate, "%d-%m-%Y %H:%i") as date,
								IF(request_models.taxPayment = 1,"Sí","No") as tax_payment,
								IFNULL(enterprise_origin_reviewed.name, enterprise_origin.name) as enterprise_origin,
								IF(account_origin_reviewed.account IS NOT NULL, CONCAT_WS(" ",account_origin_reviewed.account,account_origin_reviewed.description), CONCAT_WS(" ",account_origin.account,account_origin.description))  as account_origin,
								IFNULL(enterprise_destiny_reviewed.name, enterprise_destiny.name) as enterprise_destiny,
								IF(account_destiny_reviewed.account IS NOT NULL, CONCAT_WS(" ",account_destiny_reviewed.account,account_destiny_reviewed.description), CONCAT_WS(" ",account_destiny.account,account_destiny.description))  as account_destiny,
								loan_enterprises.currency as currency,
								DATE_FORMAT(loan_enterprises.paymentDate, "%d-%m-%Y") as payment_date,
								payment_methods.method as payment_method,
								loan_enterprises.amount as amount
							')
							->leftJoin('loan_enterprises','loan_enterprises.idFolio','request_models.folio')
							->leftJoin('status_requests','request_models.status','status_requests.idrequestStatus')
							->leftJoin('users as request_user','request_models.idRequest','request_user.id')
							->leftJoin('users as elaborate_user','request_models.idElaborate','elaborate_user.id')
							->leftJoin('enterprises as enterprise_origin','loan_enterprises.idEnterpriseOrigin','enterprise_origin.id')
							->leftJoin('accounts as account_origin','loan_enterprises.idAccAccOrigin','account_origin.idAccAcc')
							->leftJoin('enterprises as enterprise_destiny','loan_enterprises.idEnterpriseDestiny','enterprise_destiny.id')
							->leftJoin('accounts as account_destiny','loan_enterprises.idAccAccDestiny','account_destiny.idAccAcc')
							->leftJoin('enterprises as enterprise_origin_reviewed','loan_enterprises.idEnterpriseOriginR','enterprise_origin_reviewed.id')
							->leftJoin('accounts as account_origin_reviewed','loan_enterprises.idAccAccOriginR','account_origin_reviewed.idAccAcc')
							->leftJoin('enterprises as enterprise_destiny_reviewed','loan_enterprises.idEnterpriseDestinyR','enterprise_destiny_reviewed.id')
							->leftJoin('accounts as account_destiny_reviewed','loan_enterprises.idAccAccDestinyR','account_destiny_reviewed.idAccAcc')
							->leftJoin('payment_methods','payment_methods.idpaymentMethod','loan_enterprises.idpaymentMethod')
							->where('request_models.kind',12)
							->where(function ($q) use ($global_permission)
							{
								if ($global_permission == 0) 
								{
									$q->where('idElaborate',Auth::user()->id)->orWhere('idRequest',Auth::user()->id);
								}
							})
							->where(function ($query) use ($name, $mindate, $maxdate, $folio,$kind, $status)
								{
									if($status != "")
									{
										$query->where('request_models.status',$status);
									}
									if($name != "")
									{
										$query->where(function($q) use($name)
										{
											$q->where(DB::raw("CONCAT_WS(' ',request_user.name,request_user.last_name,request_user.scnd_last_name)"),'LIKE','%'.$name.'%')
												->orWhere(DB::raw("CONCAT_WS(' ',elaborate_user.name,elaborate_user.last_name,elaborate_user.scnd_last_name)"),'LIKE','%'.$name.'%');
										});
									}
									if($folio != "")
									{
										$query->where('request_models.folio',$folio);
									}
									if($mindate != "" && $maxdate != "")
									{
										$query->whereBetween('request_models.fDate',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 23:59:59')]);
									}
									if ($kind != "") 
									{
										$query->where('request_models.kind',$kind);
									}
								})
							->orderBy('request_models.fDate','DESC')
							->orderBy('request_models.folio','DESC')
							->get();
			
			$requestsPurchase 	= DB::table('request_models')
							->selectRaw('
								request_models.folio as folio,
								status_requests.description as status,
								purchase_enterprises.title as title,
								IF(purchase_enterprises.datetitle IS NOT NULL, DATE_FORMAT(purchase_enterprises.datetitle, "%d-%m-%Y"),"") as datetitle,
								CONCAT_WS(" ",request_user.name,request_user.last_name,request_user.scnd_last_name) as request_user,
								CONCAT_WS(" ",elaborate_user.name,elaborate_user.last_name,elaborate_user.scnd_last_name) as elaborate_user,
								DATE_FORMAT(request_models.fDate, "%d-%m-%Y %H:%i") as date,
								IF(request_models.taxPayment = 1,"Sí","No") as tax_payment,
								purchase_enterprises.numberOrder as number_order,
								IFNULL(enterprise_origin_reviewed.name, enterprise_origin.name) as enterprise_origin,
								IFNULL(area_origin_reviewed.name, area_origin.name) as area_origin,
								IFNULL(department_origin_reviewed.name, department_origin.name) as department_origin,
								IF(account_origin_reviewed.account IS NOT NULL, CONCAT_WS(" ",account_origin_reviewed.account,account_origin_reviewed.description), CONCAT_WS(" ",account_origin.account,account_origin.description))  as account_origin,
								IFNULL(project_origin_reviewed.proyectName, project_origin.proyectName) as project_origin,
								IFNULL(enterprise_destiny_reviewed.name, enterprise_destiny.name) as enterprise_destiny,
								IF(account_destiny_reviewed.account IS NOT NULL, CONCAT_WS(" ",account_destiny_reviewed.account,account_destiny_reviewed.description), CONCAT_WS(" ",account_destiny.account,account_destiny.description))  as account_destiny,
								IFNULL(project_destiny_reviewed.proyectName, project_destiny.proyectName) as project_destiny,
								purchase_enterprise_details.quantity as quantity,
								purchase_enterprise_details.unit as unit,
								purchase_enterprise_details.description as description,
								purchase_enterprise_details.unitPrice as unitPrice,
								purchase_enterprise_details.tax as tax,
								pe_taxes.taxes_amount as taxes_amount,
								pe_retention.retention_amount as retention_amount,
								purchase_enterprise_details.amount as amount_detail,
								purchase_enterprises.typeCurrency as currency,
								DATE_FORMAT(purchase_enterprises.paymentDate, "%d-%m-%Y") as payment_date,
								payment_methods.method as payment_method,
								purchase_enterprises.amount as amount
							')
							->leftJoin('purchase_enterprises','purchase_enterprises.idFolio','request_models.folio')
							->leftJoin('status_requests','request_models.status','status_requests.idrequestStatus')
							->leftJoin('users as request_user','request_models.idRequest','request_user.id')
							->leftJoin('users as elaborate_user','request_models.idElaborate','elaborate_user.id')
							->leftJoin('enterprises as enterprise_origin','purchase_enterprises.idEnterpriseOrigin','enterprise_origin.id')
							->leftJoin('areas as area_origin','purchase_enterprises.idAreaOrigin','area_origin.id')
							->leftJoin('departments as department_origin','purchase_enterprises.idDepartamentOrigin','department_origin.id')
							->leftJoin('accounts as account_origin','purchase_enterprises.idAccAccOrigin','account_origin.idAccAcc')
							->leftJoin('projects as project_origin','purchase_enterprises.idProjectOrigin','project_origin.idProyect')
							->leftJoin('enterprises as enterprise_destiny','purchase_enterprises.idEnterpriseDestiny','enterprise_destiny.id')
							->leftJoin('accounts as account_destiny','purchase_enterprises.idAccAccDestiny','account_destiny.idAccAcc')
							->leftJoin('projects as project_destiny','purchase_enterprises.idProjectDestiny','project_destiny.idproyect')
							->leftJoin('enterprises as enterprise_origin_reviewed','purchase_enterprises.idEnterpriseOriginR','enterprise_origin_reviewed.id')
							->leftJoin('areas as area_origin_reviewed','purchase_enterprises.idAreaOriginR','area_origin_reviewed.id')
							->leftJoin('departments as department_origin_reviewed','purchase_enterprises.idDepartamentOriginR','department_origin_reviewed.id')
							->leftJoin('accounts as account_origin_reviewed','purchase_enterprises.idAccAccOriginR','account_origin_reviewed.idAccAcc')
							->leftJoin('projects as project_origin_reviewed','purchase_enterprises.idProjectOriginR','project_origin_reviewed.idProyect')
							->leftJoin('enterprises as enterprise_destiny_reviewed','purchase_enterprises.idEnterpriseDestinyR','enterprise_destiny_reviewed.id')
							->leftJoin('accounts as account_destiny_reviewed','purchase_enterprises.idAccAccDestinyR','account_destiny_reviewed.idAccAcc')
							->leftJoin('projects as project_destiny_reviewed','purchase_enterprises.idProjectDestinyR','project_destiny_reviewed.idproyect')
							->leftJoin('payment_methods','payment_methods.idpaymentMethod','purchase_enterprises.idpaymentMethod')
							->leftJoin('purchase_enterprise_details','purchase_enterprise_details.idpurchaseEnterprise','purchase_enterprises.idpurchaseEnterprise')
							->leftJoin(DB::raw('(SELECT idPurchaseEnterpriseDetail, SUM(amount) as taxes_amount FROM purchase_enterprise_taxes GROUP BY idPurchaseEnterpriseDetail) AS pe_taxes'),'purchase_enterprise_details.idPurchaseEnterpriseDetail','pe_taxes.idPurchaseEnterpriseDetail')
							->leftJoin(DB::raw('(SELECT idPurchaseEnterpriseDetail, SUM(amount) as retention_amount FROM purchase_enterprise_retentions GROUP BY idPurchaseEnterpriseDetail) AS pe_retention'),'purchase_enterprise_details.idPurchaseEnterpriseDetail','pe_retention.idPurchaseEnterpriseDetail')
							->where('request_models.kind',13)
							->where(function ($q) use ($global_permission)
							{
								if ($global_permission == 0) 
								{
									$q->where('idElaborate',Auth::user()->id)->orWhere('idRequest',Auth::user()->id);
								}
							})
							->where(function ($query) use ($name, $mindate, $maxdate, $folio,$kind, $status)
								{
									if($status != "")
									{
										$query->where('request_models.status',$status);
									}
									if($name != "")
									{
										$query->where(function($q) use($name)
										{
											$q->where(DB::raw("CONCAT_WS(' ',request_user.name,request_user.last_name,request_user.scnd_last_name)"),'LIKE','%'.$name.'%')
												->orWhere(DB::raw("CONCAT_WS(' ',elaborate_user.name,elaborate_user.last_name,elaborate_user.scnd_last_name)"),'LIKE','%'.$name.'%');
										});
									}
									if($folio != "")
									{
										$query->where('request_models.folio',$folio);
									}
									if($mindate != "" && $maxdate != "")
									{
										$query->whereBetween('request_models.fDate',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 23:59:59')]);
									}
									if ($kind != "") 
									{
										$query->where('request_models.kind',$kind);
									}
								})
							->orderBy('request_models.fDate','DESC')
							->orderBy('request_models.folio','DESC')
							->get();
			
			$requestsGroups 	= DB::table('request_models')
							->selectRaw('
								request_models.folio as folio,
								status_requests.description as status,
								groups.title as title,
								IF(groups.datetitle IS NOT NULL, DATE_FORMAT(groups.datetitle, "%d-%m-%Y"),"") as datetitle,
								CONCAT_WS(" ",request_user.name,request_user.last_name,request_user.scnd_last_name) as request_user,
								CONCAT_WS(" ",elaborate_user.name,elaborate_user.last_name,elaborate_user.scnd_last_name) as elaborate_user,
								DATE_FORMAT(request_models.fDate, "%d-%m-%Y %H:%i") as date,
								IF(request_models.taxPayment = 1,"Sí","No") as tax_payment,
								groups.numberOrder as number_order,
								groups.operationType as operation_type,
								IFNULL(enterprise_origin_reviewed.name, enterprise_origin.name) as enterprise_origin,
								IFNULL(area_origin_reviewed.name, area_origin.name) as area_origin,
								IFNULL(department_origin_reviewed.name, department_origin.name) as department_origin,
								IF(account_origin_reviewed.account IS NOT NULL, CONCAT_WS(" ",account_origin_reviewed.account,account_origin_reviewed.description), CONCAT_WS(" ",account_origin.account,account_origin.description))  as account_origin,
								IFNULL(project_origin_reviewed.proyectName, project_origin.proyectName) as project_origin,
								IFNULL(enterprise_destiny_reviewed.name, enterprise_destiny.name) as enterprise_destiny,
								IF(account_destiny_reviewed.account IS NOT NULL, CONCAT_WS(" ",account_destiny_reviewed.account,account_destiny_reviewed.description), CONCAT_WS(" ",account_destiny.account,account_destiny.description))  as account_destiny,
								groups_details.quantity as quantity,
								groups_details.unit as unit,
								groups_details.description as description,
								groups_details.unitPrice as unitPrice,
								groups_details.tax as tax,
								groups_taxes.taxes_amount as taxes_amount,
								groups_retentions.retention_amount as retention_amount,
								groups_details.amount as amount_detail,
								groups.reference as reference,
								groups.typeCurrency as type_currency,
								DATE_FORMAT(groups.paymentDate, "%d-%m-%Y") as payment_date,
								payment_methods.method as payment_method,
								groups.statusBill as status_bill,
								groups.amount as amount,
								groups.commission as commission,
								groups.amountRetake as amount_retake
							')
							->leftJoin('groups','groups.idFolio','request_models.folio')
							->leftJoin('status_requests','request_models.status','status_requests.idrequestStatus')
							->leftJoin('users as request_user','request_models.idRequest','request_user.id')
							->leftJoin('users as elaborate_user','request_models.idElaborate','elaborate_user.id')
							->leftJoin('enterprises as enterprise_origin','groups.idEnterpriseOrigin','enterprise_origin.id')
							->leftJoin('areas as area_origin','groups.idAreaOrigin','area_origin.id')
							->leftJoin('departments as department_origin','groups.idDepartamentOrigin','department_origin.id')
							->leftJoin('accounts as account_origin','groups.idAccAccOrigin','account_origin.idAccAcc')
							->leftJoin('projects as project_origin','groups.idProjectOrigin','project_origin.idproyect')
							->leftJoin('enterprises as enterprise_destiny','groups.idEnterpriseDestiny','enterprise_destiny.id')
							->leftJoin('accounts as account_destiny','groups.idAccAccDestiny','account_destiny.idAccAcc')
							->leftJoin('enterprises as enterprise_origin_reviewed','groups.idEnterpriseOriginR','enterprise_origin_reviewed.id')
							->leftJoin('areas as area_origin_reviewed','groups.idAreaOriginR','area_origin_reviewed.id')
							->leftJoin('departments as department_origin_reviewed','groups.idDepartamentOriginR','department_origin_reviewed.id')
							->leftJoin('accounts as account_origin_reviewed','groups.idAccAccOriginR','account_origin_reviewed.idAccAcc')
							->leftJoin('projects as project_origin_reviewed','groups.idProjectOriginR','project_origin_reviewed.idProyect')
							->leftJoin('enterprises as enterprise_destiny_reviewed','groups.idEnterpriseDestinyR','enterprise_destiny_reviewed.id')
							->leftJoin('accounts as account_destiny_reviewed','groups.idAccAccDestinyR','account_destiny_reviewed.idAccAcc')
							->leftJoin('payment_methods','payment_methods.idpaymentMethod','groups.idpaymentMethod')
							->leftJoin('groups_details','groups_details.idgroups','groups.idgroups')
							->leftJoin(DB::raw('(SELECT idgroupsDetail, SUM(amount) as taxes_amount FROM groups_taxes GROUP BY idgroupsDetail) AS groups_taxes'),'groups_details.idgroupsDetail','groups_taxes.idgroupsDetail')
							->leftJoin(DB::raw('(SELECT idgroupsDetail, SUM(amount) as retention_amount FROM groups_retentions GROUP BY idgroupsDetail) AS groups_retentions'),'groups_details.idgroupsDetail','groups_retentions.idgroupsDetail')
							->where('request_models.kind',14)
							->where(function ($q) use ($global_permission)
							{
								if ($global_permission == 0) 
								{
									$q->where('idElaborate',Auth::user()->id)->orWhere('idRequest',Auth::user()->id);
								}
							})
							->where(function ($query) use ($name, $mindate, $maxdate, $folio,$kind, $status)
							{
								if($status != "")
								{
									$query->where('request_models.status',$status);
								}
								if($name != "")
								{
									$query->where(function($q) use($name)
									{
										$q->where(DB::raw("CONCAT_WS(' ',request_user.name,request_user.last_name,request_user.scnd_last_name)"),'LIKE','%'.$name.'%')
											->orWhere(DB::raw("CONCAT_WS(' ',elaborate_user.name,elaborate_user.last_name,elaborate_user.scnd_last_name)"),'LIKE','%'.$name.'%');
									});
								}
								if($folio != "")
								{
									$query->where('request_models.folio',$folio);
								}
								if($mindate != "" && $maxdate != "")
								{
									$query->whereBetween('request_models.fDate',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 23:59:59')]);
								}
								if ($kind != "") 
								{
									$query->where('request_models.kind',$kind);
								}
							})
							->orderBy('request_models.fDate','DESC')
							->orderBy('request_models.folio','DESC')
							->get();

			$requestsMovements = DB::table('request_models')
							->selectRaw('
								request_models.folio as folio,
								status_requests.description as status,
								movements_enterprises.title as title,
								movements_enterprises.datetitle as datetitle,
								IF(movements_enterprises.datetitle IS NOT NULL, DATE_FORMAT(movements_enterprises.datetitle, "%d-%m-%Y"),"") as datetitle,
								CONCAT_WS(" ",request_user.name,request_user.last_name,request_user.scnd_last_name) as request_user,
								CONCAT_WS(" ",elaborate_user.name,elaborate_user.last_name,elaborate_user.scnd_last_name) as elaborate_user,
								DATE_FORMAT(request_models.fDate, "%d-%m-%Y %H:%i") as date,
								IF(request_models.taxPayment = 1,"Sí","No") as tax_payment,
								IFNULL(enterprise_origin_reviewed.name, enterprise_origin.name) as enterprise_origin,
								IF(account_origin_reviewed.account IS NOT NULL, CONCAT_WS(" ",account_origin_reviewed.account,account_origin_reviewed.description), CONCAT_WS(" ",account_origin.account,account_origin.description))  as account_origin,
								IFNULL(enterprise_destiny_reviewed.name, enterprise_destiny.name) as enterprise_destiny,
								IF(account_destiny_reviewed.account IS NOT NULL, CONCAT_WS(" ",account_destiny_reviewed.account,account_destiny_reviewed.description), CONCAT_WS(" ",account_destiny.account,account_destiny.description))  as account_destiny,
								movements_enterprises.typeCurrency as currency,
								DATE_FORMAT(movements_enterprises.paymentDate, "%d-%m-%Y") as payment_date,
								payment_methods.method as payment_method,
								movements_enterprises.amount as amount
							')
							->leftJoin('movements_enterprises','movements_enterprises.idFolio','request_models.folio')
							->leftJoin('status_requests','request_models.status','status_requests.idrequestStatus')
							->leftJoin('users as request_user','request_models.idRequest','request_user.id')
							->leftJoin('users as elaborate_user','request_models.idElaborate','elaborate_user.id')
							->leftJoin('enterprises as enterprise_origin','movements_enterprises.idEnterpriseOrigin','enterprise_origin.id')
							->leftJoin('accounts as account_origin','movements_enterprises.idAccAccOrigin','account_origin.idAccAcc')
							->leftJoin('enterprises as enterprise_destiny','movements_enterprises.idEnterpriseDestiny','enterprise_destiny.id')
							->leftJoin('accounts as account_destiny','movements_enterprises.idAccAccDestiny','account_destiny.idAccAcc')
							->leftJoin('enterprises as enterprise_origin_reviewed','movements_enterprises.idEnterpriseOriginR','enterprise_origin_reviewed.id')
							->leftJoin('accounts as account_origin_reviewed','movements_enterprises.idAccAccOriginR','account_origin_reviewed.idAccAcc')
							->leftJoin('enterprises as enterprise_destiny_reviewed','movements_enterprises.idEnterpriseDestinyR','enterprise_destiny_reviewed.id')
							->leftJoin('accounts as account_destiny_reviewed','movements_enterprises.idAccAccDestinyR','account_destiny_reviewed.idAccAcc')
							->leftJoin('payment_methods','payment_methods.idpaymentMethod','movements_enterprises.idpaymentMethod')
							->where('request_models.kind',15)
							->where(function ($q) use ($global_permission)
							{
								if ($global_permission == 0) 
								{
									$q->where('idElaborate',Auth::user()->id)->orWhere('idRequest',Auth::user()->id);
								}
							})
							->where(function ($query) use ($name, $mindate, $maxdate, $folio,$kind, $status)
							{
								if($status != "")
								{
									$query->where('request_models.status',$status);
								}
								if($name != "")
								{
									$query->where(function($q) use($name)
									{
										$q->where(DB::raw("CONCAT_WS(' ',request_user.name,request_user.last_name,request_user.scnd_last_name)"),'LIKE','%'.$name.'%')
											->orWhere(DB::raw("CONCAT_WS(' ',elaborate_user.name,elaborate_user.last_name,elaborate_user.scnd_last_name)"),'LIKE','%'.$name.'%');
									});
								}
								if($folio != "")
								{
									$query->where('request_models.folio',$folio);
								}
								if($mindate != "" && $maxdate != "")
								{
									$query->whereBetween('request_models.fDate',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 23:59:59')]);
								}
								if ($kind != "") 
								{
									$query->where('request_models.kind',$kind);
								}
							})
							->orderBy('request_models.fDate','DESC')
							->orderBy('request_models.folio','DESC')
							->get();

			$new_sheet 		= true;
			$defaultStyle	= (new StyleBuilder())->setFontName('Calibri')->setFontSize(12)->setCellAlignment(CellAlignment::LEFT)->build();
			$currencyFormat	= (new StyleBuilder())->setFormat('_("$"* #,##0.00_),_("$"* \(#,##0.00\),_("$"* "-"??_),_(@_)')->build();
			$rowDark		= (new StyleBuilder())->setBackgroundColor('F0F0F0')->build();
			$mhStyleCol1	= (new StyleBuilder())->setBackgroundColor('000000')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$mhStyleCol2	= (new StyleBuilder())->setBackgroundColor('1d353d')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$mhStyleCol3	= (new StyleBuilder())->setBackgroundColor('104f64')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$writer			= WriterEntityFactory::createXLSXWriter();
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('Movimientos Entre Cuentas.xlsx');


			if (count($requestsAdjustment)>0) 
			{
				$new_sheet		= false;
				$writer->getCurrentSheet()->setName('Ajuste de Movimientos');
				$headers		= ['Ajuste de Movimientos','','','','','','','','','','','','','','','','',''];
				$tempHeaders	= [];
				foreach($headers as $k => $header)
				{
					$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol1);
				}
				$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
				$writer->addRow($rowFromValues);

				$subHeaders		= ['Datos generales','','','','','','','','Cuenta de Origen','Cuenta de Destino','','','','','Condiciones de pago','','',''];
				$tempSubHeader	= [];
				foreach($subHeaders as $k => $subHeader)
				{
					$tempSubHeader[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol2);
				}
				$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
				$writer->addRow($rowFromValues);

				$subHeaders		= ['Folio','Estado','Título','Fecha','Solicitante','Elaborado por','Fecha de elaboración','Comentarios','Empresa de origen','Empresa de destino','Dirección de destino','Departamento de destino','Clasificación del gasto de destino','Proyecto de destino','Tipo de moneda','Fecha de pago','Forma de pago','Importe total a pagar'];
				$tempSubHeader	= [];
				foreach($subHeaders as $k => $subHeader)
				{
					$tempSubHeader[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol3);
				}
				$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
				$writer->addRow($rowFromValues);

				$tempFolio	= '';
				$kindRow	= true;

				foreach($requestsAdjustment as $request)
				{
					if($tempFolio != $request->folio)
					{
						$tempFolio = $request->folio;
						$kindRow = !$kindRow;
					}

					$tmpArr = [];
					foreach($request as $k => $r)
					{
						if(in_array($k,['amount']))
						{
							if($r != '')
							{
								$tmpArr[] = WriterEntityFactory::createCell((double)$r, $currencyFormat);
							}
							else
							{
								$tmpArr[] = WriterEntityFactory::createCell($r);
							}
						}
						else
						{
							$tmpArr[] = WriterEntityFactory::createCell($r);
						}
					}

					if($kindRow)
					{
						$rowFromValues = WriterEntityFactory::createRow($tmpArr,$rowDark);
					}
					else
					{
						$rowFromValues = WriterEntityFactory::createRow($tmpArr);
					}
					$writer->addRow($rowFromValues);
				}
			}
			if (count($requestsLoan)>0) 
			{
				if ($new_sheet) 
				{
					$writer->getCurrentSheet()->setName('Préstamo Inter-Empresa');
					$new_sheet = false;
				}
				else
				{
					$newSheet = $writer->addNewSheetAndMakeItCurrent();
					$writer->getCurrentSheet()->setName('Préstamo Inter-Empresa');
				}

				$headers		= ['Préstamo Inter-Empresa','','','','','','','','','','','','','','',''];
				$tempHeaders	= [];
				foreach($headers as $k => $header)
				{
					$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol1);
				}
				$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
				$writer->addRow($rowFromValues);

				$subHeaders		= ['Datos generales','','','','','','','','Cuenta de Origen','','Cuenta de Destino','','Condiciones de pago','','',''];
				$tempSubHeader	= [];
				foreach($subHeaders as $k => $subHeader)
				{
					$tempSubHeader[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol2);
				}
				$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
				$writer->addRow($rowFromValues);

				$subHeaders		= ['Folio','Estado','Título','Fecha','Solicitante','Elaborado por','Fecha de elaboración','Fiscal','Empresa de origen','Clasificación del gasto de origen','Empresa de destino','Clasificación del gasto de destino','Tipo de moneda','Fecha de pago','Forma de pago','Importe total a pagar'];
				$tempSubHeader	= [];
				foreach($subHeaders as $k => $subHeader)
				{
					$tempSubHeader[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol3);
				}
				$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
				$writer->addRow($rowFromValues);

				$tempFolio	= '';
				$kindRow	= true;

				foreach($requestsLoan as $request)
				{
					if($tempFolio != $request->folio)
					{
						$tempFolio = $request->folio;
						$kindRow = !$kindRow;
					}

					$tmpArr = [];
					foreach($request as $k => $r)
					{
						if(in_array($k,['amount']))
						{
							if($r != '')
							{
								$tmpArr[] = WriterEntityFactory::createCell((double)$r, $currencyFormat);
							}
							else
							{
								$tmpArr[] = WriterEntityFactory::createCell($r);
							}
						}
						else
						{
							$tmpArr[] = WriterEntityFactory::createCell($r);
						}
					}

					if($kindRow)
					{
						$rowFromValues = WriterEntityFactory::createRow($tmpArr,$rowDark);
					}
					else
					{
						$rowFromValues = WriterEntityFactory::createRow($tmpArr);
					}
					$writer->addRow($rowFromValues);
				}
			}
			
			if (count($requestsPurchase)>0) 
			{
				if ($new_sheet) 
				{
					$writer->getCurrentSheet()->setName('Compras Inter-Empresa');
					$new_sheet = false;
				}
				else
				{
					$newSheet = $writer->addNewSheetAndMakeItCurrent();
					$writer->getCurrentSheet()->setName('Compras Inter-Empresa');
				}

				$headers		= ['Compras Inter-Empresa','','','','','','','','','','','','','','','','','','','','','','','','','','','',''];
				$tempHeaders	= [];
				foreach($headers as $k => $header)
				{
					$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol1);
				}
				$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
				$writer->addRow($rowFromValues);

				$subHeaders		= ['Datos generales','','','','','','','','','Cuenta de Origen','','','','','Cuenta de Destino','','','Datos de la solicitud','','','','','','','','Condiciones de pago','','',''];
				$tempSubHeader	= [];
				foreach($subHeaders as $k => $subHeader)
				{
					$tempSubHeader[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol2);
				}
				$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
				$writer->addRow($rowFromValues);

				$subHeaders		= ['Folio','Estado','Título','Fecha','Solicitante','Elaborado por','Fecha de elaboración','Fiscal','Número de orden','Empresa de origen','Clasificación del gasto de origen','Proyecto de origen','Empresa de destino','Dirección de destino','Departamento de destino','Clasificación del gasto de destino','Proyecto de destino','Cantidad','Unidad','Descripción','Precio Unitario','IVA','Impuesto Adicional','Retenciones','Total','Tipo de moneda','Fecha de pago','Forma de pago','Importe total a pagar'];
				$tempSubHeader	= [];
				foreach($subHeaders as $k => $subHeader)
				{
					$tempSubHeader[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol3);
				}
				$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
				$writer->addRow($rowFromValues);

				$tempFolio	= '';
				$kindRow	= true;

				foreach($requestsPurchase as $request)
				{
					if($tempFolio != $request->folio)
					{
						$tempFolio = $request->folio;
						$kindRow = !$kindRow;
					}
					else
					{
						$request->folio					= null;
						$request->status				= '';
						$request->title					= '';
						$request->datetitle				= '';
						$request->request_user			= '';
						$request->elaborate_user		= '';
						$request->date					= '';
						$request->tax_payment			= '';
						$request->number_order			= '';
						$request->enterprise_origin		= '';
						$request->area_origin			= '';
						$request->department_origin		= '';
						$request->account_origin		= '';
						$request->project_origin		= '';
						$request->enterprise_destiny	= '';
						$request->account_destiny		= '';
						$request->project_destiny		= '';
						$request->currency				= '';
						$request->payment_date			= '';
						$request->payment_method		= '';
						$request->amount				= '';
					}

					$tmpArr = [];
					foreach($request as $k => $r)
					{
						if(in_array($k,['amount','unitPrice','tax','taxes_amount','retention_amount','amount_detail']))
						{
							if($r != '')
							{
								$tmpArr[] = WriterEntityFactory::createCell((double)$r, $currencyFormat);
							}
							else
							{
								$tmpArr[] = WriterEntityFactory::createCell($r);
							}
						}
						elseif(in_array($k,['quantity']))
						{
							$tmpArr[] = WriterEntityFactory::createCell((double)$r);
						}
						else
						{
							$tmpArr[] = WriterEntityFactory::createCell($r);
						}
					}

					if($kindRow)
					{
						$rowFromValues = WriterEntityFactory::createRow($tmpArr,$rowDark);
					}
					else
					{
						$rowFromValues = WriterEntityFactory::createRow($tmpArr);
					}
					$writer->addRow($rowFromValues);
				}
			}

			if (count($requestsGroups)>0) 
			{
				if ($new_sheet) 
				{
					$writer->getCurrentSheet()->setName('Grupos');
					$new_sheet = false;
				}
				else
				{
					$newSheet = $writer->addNewSheetAndMakeItCurrent();
					$writer->getCurrentSheet()->setName('Grupos');
				}

				$headers		= ['Grupos','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',''];
				$tempHeaders	= [];
				foreach($headers as $k => $header)
				{
					$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol1);
				}
				$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
				$writer->addRow($rowFromValues);

				$subHeaders		= ['Datos generales','','','','','','','','','','Cuenta de Origen','','','','','Cuenta de Destino','','Datos de la solicitud','','','','','','','','Condiciones de pago','','','','','','',''];
				$tempSubHeader	= [];
				foreach($subHeaders as $k => $subHeader)
				{
					$tempSubHeader[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol2);
				}
				$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
				$writer->addRow($rowFromValues);

				$subHeaders		= ['Folio','Estado','Título','Fecha','Solicitante','Elaborado por','Fecha de elaboración','Fiscal','Número de orden','Tipo de operación','Empresa de origen','Dirección de origen','Departamento de origen','Clasificación del gasto de origen','Proyecto de origen','Empresa de destino','Clasificación del gasto de destino','Cantidad','Unidad','Descripción','Precio Unitario','IVA','Impuesto Adicional','Retenciones','Total','Referencia/Número de Factura','Tipo de moneda','Fecha de pago','Forma de pago','Estado  de factura','Importe total a pagar','Comisión','Importe a retomar'];
				$tempSubHeader	= [];
				foreach($subHeaders as $k => $subHeader)
				{
					$tempSubHeader[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol3);
				}
				$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
				$writer->addRow($rowFromValues);

				$tempFolio	= '';
				$kindRow	= true;

				foreach($requestsGroups as $request)
				{
					if($tempFolio != $request->folio)
					{
						$tempFolio = $request->folio;
						$kindRow = !$kindRow;
					}
					else
					{
						$request->folio					= null;
						$request->status				= '';
						$request->title					= '';
						$request->datetitle				= '';
						$request->request_user			= '';
						$request->elaborate_user		= '';
						$request->date					= '';
						$request->tax_payment			= '';
						$request->number_order			= '';
						$request->operation_type		= '';
						$request->enterprise_origin		= '';
						$request->area_origin			= '';
						$request->department_origin		= '';
						$request->account_origin		= '';
						$request->project_origin		= '';
						$request->enterprise_destiny	= '';
						$request->account_destiny		= '';
						$request->reference				= '';
						$request->type_currency			= '';
						$request->payment_date			= '';
						$request->payment_method		= '';
						$request->status_bill			= '';
						$request->amount				= '';
						$request->commission			= '';
						$request->amount_retake			= '';
					}

					$tmpArr = [];
					foreach($request as $k => $r)
					{
						if(in_array($k,['unitPrice','tax','taxes_amount','retention_amount','amount_detail','amount','commission','amount_retake']))
						{
							if($r != '')
							{
								$tmpArr[] = WriterEntityFactory::createCell((double)$r, $currencyFormat);
							}
							else
							{
								$tmpArr[] = WriterEntityFactory::createCell($r);
							}
						}
						elseif(in_array($k,['quantity']))
						{
							$tmpArr[] = WriterEntityFactory::createCell((double)$r);
						}
						else
						{
							$tmpArr[] = WriterEntityFactory::createCell($r);
						}
					}

					if($kindRow)
					{
						$rowFromValues = WriterEntityFactory::createRow($tmpArr,$rowDark);
					}
					else
					{
						$rowFromValues = WriterEntityFactory::createRow($tmpArr);
					}
					$writer->addRow($rowFromValues);
				}
			}
			
			if (count($requestsMovements)>0) 
			{
				if ($new_sheet) 
				{
					$writer->getCurrentSheet()->setName('Movimientos Misma Empresa');
					$new_sheet = false;
				}
				else
				{
					$newSheet = $writer->addNewSheetAndMakeItCurrent();
					$writer->getCurrentSheet()->setName('Movimientos Misma Empresa');
				}

				$new_sheet		= false;
				$headers		= ['Movimientos Misma Empresa','','','','','','','','','','','','','','',''];
				$tempHeaders	= [];
				foreach($headers as $k => $header)
				{
					$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol1);
				}
				$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
				$writer->addRow($rowFromValues);

				$subHeaders		= ['Datos generales','','','','','','','','Cuenta de Origen','','Cuenta de Destino','','Condiciones de pago','','',''];
				$tempSubHeader	= [];
				foreach($subHeaders as $k => $subHeader)
				{
					$tempSubHeader[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol2);
				}
				$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
				$writer->addRow($rowFromValues);

				$subHeaders		= ['Folio','Estado','Título','Fecha','Solicitante','Elaborado por','Fecha de elaboración','Fiscal','Empresa de origen','Clasificación del gasto de origen','Empresa de destino','Clasificación del gasto de destino','Tipo de moneda','Fecha de pago','Forma de pago','Importe total a pagar'];
				$tempSubHeader	= [];
				foreach($subHeaders as $k => $subHeader)
				{
					$tempSubHeader[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol3);
				}
				$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
				$writer->addRow($rowFromValues);

				$tempFolio	= '';
				$kindRow	= true;

				foreach($requestsMovements as $request)
				{
					if($tempFolio != $request->folio)
					{
						$tempFolio = $request->folio;
						$kindRow = !$kindRow;
					}

					$tmpArr = [];
					foreach($request as $k => $r)
					{
						if(in_array($k,['amount']))
						{
							if($r != '')
							{
								$tmpArr[] = WriterEntityFactory::createCell((double)$r, $currencyFormat);
							}
							else
							{
								$tmpArr[] = WriterEntityFactory::createCell($r);
							}
						}
						else
						{
							$tmpArr[] = WriterEntityFactory::createCell($r);
						}
					}

					if($kindRow)
					{
						$rowFromValues = WriterEntityFactory::createRow($tmpArr,$rowDark);
					}
					else
					{
						$rowFromValues = WriterEntityFactory::createRow($tmpArr);
					}
					$writer->addRow($rowFromValues);
				}
			}

			return $writer->close();
		}
		else
		{
			return redirect('/');
		}
	}

	public function reviewExcel(Request $request)
	{
		if(Auth::user()->module->where('id',150)->count()>0)
		{
			$data       = App\Module::find($this->module_id);
			$name       = $request->name;
			$folio      = $request->folio;
			$mindate    = $request->mindate!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate) : null;
			$maxdate    = $request->maxdate!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate) : null;
			$kind       = $request->kind;

			$requestsAdjustment = DB::table('request_models')
							->selectRaw('
								request_models.folio as folio,
								status_requests.description as status,
								adjustments.title as title,
								DATE_FORMAT(adjustments.datetitle, "%d-%m-%Y") as datetitle,
								CONCAT_WS(" ",request_user.name,request_user.last_name,request_user.scnd_last_name) as request_user,
								CONCAT_WS(" ",elaborate_user.name,elaborate_user.last_name,elaborate_user.scnd_last_name) as elaborate_user,
								DATE_FORMAT(request_models.fDate, "%d-%m-%Y %H:%i") as date,
								adjustments.commentaries as commentaries,
								IFNULL(enterprise_origin_reviewed.name, enterprise_origin.name) as enterprise_origin,
								IFNULL(enterprise_destiny_reviewed.name, enterprise_destiny.name) as enterprise_destiny,
								IFNULL(direction_destiny_reviewed.name, direction_destiny.name) as direction_destiny,
								IFNULL(department_destiny_reviewed.name, department_destiny.name) as department_destiny,
								IF(account_destiny_reviewed.account IS NOT NULL,CONCAT_WS(" - ",account_destiny_reviewed.account,account_destiny_reviewed.description), CONCAT_WS(" - ",account_destiny.account,account_destiny.description)) as account_destiny,
								IFNULL(project_destiny_reviewed.proyectName, project_destiny.proyectName) as project_destiny,
								adjustments.currency as currency,
								DATE_FORMAT(adjustments.paymentDate, "%d-%m-%Y") as payment_date,
								payment_methods.method as payment_method,
								adjustments.amount as amount
							')
							->leftJoin('adjustments','adjustments.idFolio','request_models.folio')
							->leftJoin('status_requests','request_models.status','status_requests.idrequestStatus')
							->leftJoin('users as request_user','request_models.idRequest','request_user.id')
							->leftJoin('users as elaborate_user','request_models.idElaborate','elaborate_user.id')
							->leftJoin('enterprises as enterprise_origin','adjustments.idEnterpriseOrigin','enterprise_origin.id')
							->leftJoin('enterprises as enterprise_destiny','adjustments.idEnterpriseDestiny','enterprise_destiny.id')
							->leftJoin('areas as direction_destiny','adjustments.idAreaDestiny','direction_destiny.id')
							->leftJoin('departments as department_destiny','adjustments.idDepartamentDestiny','department_destiny.id')
							->leftJoin('accounts as account_destiny','adjustments.idAccAccDestiny','account_destiny.idAccAcc')
							->leftJoin('projects as project_destiny','adjustments.idProjectDestiny','project_destiny.idproyect')
							->leftJoin('enterprises as enterprise_origin_reviewed','adjustments.idEnterpriseOriginR','enterprise_origin_reviewed.id')
							->leftJoin('enterprises as enterprise_destiny_reviewed','adjustments.idEnterpriseDestinyR','enterprise_destiny_reviewed.id')
							->leftJoin('areas as direction_destiny_reviewed','adjustments.idAreaDestinyR','direction_destiny_reviewed.id')
							->leftJoin('departments as department_destiny_reviewed','adjustments.idDepartamentDestinyR','department_destiny_reviewed.id')
							->leftJoin('accounts as account_destiny_reviewed','adjustments.idAccAccDestinyR','account_destiny_reviewed.idAccAcc')
							->leftJoin('projects as project_destiny_reviewed','adjustments.idProjectDestinyR','project_destiny_reviewed.idproyect')
							->leftJoin('payment_methods','payment_methods.idpaymentMethod','adjustments.idpaymentMethod')
							->whereIn('request_models.status',[3])
							->where('request_models.kind',11)
							->where(function ($query) use ($name, $mindate, $maxdate, $folio,$kind)
							{
								if($name != "")
								{
									$query->where(function($q) use($name)
									{
										$q->where(DB::raw("CONCAT_WS(' ',request_user.name,request_user.last_name,request_user.scnd_last_name)"),'LIKE','%'.$name.'%')
											->orWhere(DB::raw("CONCAT_WS(' ',elaborate_user.name,elaborate_user.last_name,elaborate_user.scnd_last_name)"),'LIKE','%'.$name.'%');
									});
								}
								if($folio != "")
								{
									$query->where('request_models.folio',$folio);
								}
								if($mindate != "" && $maxdate != "")
								{
									$query->whereBetween('request_models.fDate',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 23:59:59')]);
								}
								if ($kind != "") 
								{
									$query->where('request_models.kind',$kind);
								}
							})
							->orderBy('request_models.fDate','DESC')
							->orderBy('request_models.folio','DESC')
							->get();


			$requestsLoan 	= DB::table('request_models')
							->selectRaw('
								request_models.folio as folio,
								status_requests.description as status,
								loan_enterprises.title as title,
								DATE_FORMAT(loan_enterprises.datetitle, "%d-%m-%Y") as datetitle,
								CONCAT_WS(" ",request_user.name,request_user.last_name,request_user.scnd_last_name) as request_user,
								CONCAT_WS(" ",elaborate_user.name,elaborate_user.last_name,elaborate_user.scnd_last_name) as elaborate_user,
								DATE_FORMAT(request_models.fDate, "%d-%m-%Y %H:%i") as date,
								IF(request_models.taxPayment = 1,"Sí","No") as tax_payment,
								IFNULL(enterprise_origin_reviewed.name, enterprise_origin.name) as enterprise_origin,
								IF(account_origin_reviewed.account IS NOT NULL, CONCAT_WS(" ",account_origin_reviewed.account,account_origin_reviewed.description), CONCAT_WS(" ",account_origin.account,account_origin.description))  as account_origin,
								IFNULL(enterprise_destiny_reviewed.name, enterprise_destiny.name) as enterprise_destiny,
								IF(account_destiny_reviewed.account IS NOT NULL, CONCAT_WS(" ",account_destiny_reviewed.account,account_destiny_reviewed.description), CONCAT_WS(" ",account_destiny.account,account_destiny.description))  as account_destiny,
								loan_enterprises.currency as currency,
								DATE_FORMAT(loan_enterprises.paymentDate, "%d-%m-%Y") as payment_date,
								payment_methods.method as payment_method,
								loan_enterprises.amount as amount
							')
							->leftJoin('loan_enterprises','loan_enterprises.idFolio','request_models.folio')
							->leftJoin('status_requests','request_models.status','status_requests.idrequestStatus')
							->leftJoin('users as request_user','request_models.idRequest','request_user.id')
							->leftJoin('users as elaborate_user','request_models.idElaborate','elaborate_user.id')
							->leftJoin('enterprises as enterprise_origin','loan_enterprises.idEnterpriseOrigin','enterprise_origin.id')
							->leftJoin('accounts as account_origin','loan_enterprises.idAccAccOrigin','account_origin.idAccAcc')
							->leftJoin('enterprises as enterprise_destiny','loan_enterprises.idEnterpriseDestiny','enterprise_destiny.id')
							->leftJoin('accounts as account_destiny','loan_enterprises.idAccAccDestiny','account_destiny.idAccAcc')
							->leftJoin('enterprises as enterprise_origin_reviewed','loan_enterprises.idEnterpriseOriginR','enterprise_origin_reviewed.id')
							->leftJoin('accounts as account_origin_reviewed','loan_enterprises.idAccAccOriginR','account_origin_reviewed.idAccAcc')
							->leftJoin('enterprises as enterprise_destiny_reviewed','loan_enterprises.idEnterpriseDestinyR','enterprise_destiny_reviewed.id')
							->leftJoin('accounts as account_destiny_reviewed','loan_enterprises.idAccAccDestinyR','account_destiny_reviewed.idAccAcc')
							->leftJoin('payment_methods','payment_methods.idpaymentMethod','loan_enterprises.idpaymentMethod')
							->whereIn('request_models.status',[3])
							->where('request_models.kind',12)
							->where(function ($query) use ($name, $mindate, $maxdate, $folio,$kind)
								{
									if($name != "")
									{
										$query->where(function($q) use($name)
										{
											$q->where(DB::raw("CONCAT_WS(' ',request_user.name,request_user.last_name,request_user.scnd_last_name)"),'LIKE','%'.$name.'%')
												->orWhere(DB::raw("CONCAT_WS(' ',elaborate_user.name,elaborate_user.last_name,elaborate_user.scnd_last_name)"),'LIKE','%'.$name.'%');
										});
									}
									if($folio != "")
									{
										$query->where('request_models.folio',$folio);
									}
									if($mindate != "" && $maxdate != "")
									{
										$query->whereBetween('request_models.fDate',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 23:59:59')]);
									}
									if ($kind != "") 
									{
										$query->where('request_models.kind',$kind);
									}
								})
							->orderBy('request_models.fDate','DESC')
							->orderBy('request_models.folio','DESC')
							->get();
			
			$requestsPurchase 	= DB::table('request_models')
							->selectRaw('
								request_models.folio as folio,
								status_requests.description as status,
								purchase_enterprises.title as title,
								DATE_FORMAT(purchase_enterprises.datetitle, "%d-%m-%Y") as datetitle,
								CONCAT_WS(" ",request_user.name,request_user.last_name,request_user.scnd_last_name) as request_user,
								CONCAT_WS(" ",elaborate_user.name,elaborate_user.last_name,elaborate_user.scnd_last_name) as elaborate_user,
								DATE_FORMAT(request_models.fDate, "%d-%m-%Y %H:%i") as date,
								IF(request_models.taxPayment = 1,"Sí","No") as tax_payment,
								purchase_enterprises.numberOrder as number_order,
								IFNULL(enterprise_origin_reviewed.name, enterprise_origin.name) as enterprise_origin,
								IFNULL(area_origin_reviewed.name, area_origin.name) as area_origin,
								IFNULL(department_origin_reviewed.name, department_origin.name) as department_origin,
								IF(account_origin_reviewed.account IS NOT NULL, CONCAT_WS(" ",account_origin_reviewed.account,account_origin_reviewed.description), CONCAT_WS(" ",account_origin.account,account_origin.description))  as account_origin,
								IFNULL(project_origin_reviewed.proyectName, project_origin.proyectName) as project_origin,
								IFNULL(enterprise_destiny_reviewed.name, enterprise_destiny.name) as enterprise_destiny,
								IF(account_destiny_reviewed.account IS NOT NULL, CONCAT_WS(" ",account_destiny_reviewed.account,account_destiny_reviewed.description), CONCAT_WS(" ",account_destiny.account,account_destiny.description))  as account_destiny,
								IFNULL(project_destiny_reviewed.proyectName, project_destiny.proyectName) as project_destiny,
								purchase_enterprise_details.quantity as quantity,
								purchase_enterprise_details.unit as unit,
								purchase_enterprise_details.description as description,
								purchase_enterprise_details.unitPrice as unitPrice,
								purchase_enterprise_details.tax as tax,
								pe_taxes.taxes_amount as taxes_amount,
								pe_retention.retention_amount as retention_amount,
								purchase_enterprise_details.amount as amount_detail,
								purchase_enterprises.typeCurrency as currency,
								DATE_FORMAT(purchase_enterprises.paymentDate, "%d-%m-%Y") as payment_date,
								payment_methods.method as payment_method,
								purchase_enterprises.amount as amount
							')
							->leftJoin('purchase_enterprises','purchase_enterprises.idFolio','request_models.folio')
							->leftJoin('status_requests','request_models.status','status_requests.idrequestStatus')
							->leftJoin('users as request_user','request_models.idRequest','request_user.id')
							->leftJoin('users as elaborate_user','request_models.idElaborate','elaborate_user.id')
							->leftJoin('enterprises as enterprise_origin','purchase_enterprises.idEnterpriseOrigin','enterprise_origin.id')
							->leftJoin('areas as area_origin','purchase_enterprises.idAreaOrigin','area_origin.id')
							->leftJoin('departments as department_origin','purchase_enterprises.idDepartamentOrigin','department_origin.id')
							->leftJoin('accounts as account_origin','purchase_enterprises.idAccAccOrigin','account_origin.idAccAcc')
							->leftJoin('projects as project_origin','purchase_enterprises.idProjectOrigin','project_origin.idProyect')
							->leftJoin('enterprises as enterprise_destiny','purchase_enterprises.idEnterpriseDestiny','enterprise_destiny.id')
							->leftJoin('accounts as account_destiny','purchase_enterprises.idAccAccDestiny','account_destiny.idAccAcc')
							->leftJoin('projects as project_destiny','purchase_enterprises.idProjectDestiny','project_destiny.idproyect')
							->leftJoin('enterprises as enterprise_origin_reviewed','purchase_enterprises.idEnterpriseOriginR','enterprise_origin_reviewed.id')
							->leftJoin('areas as area_origin_reviewed','purchase_enterprises.idAreaOriginR','area_origin_reviewed.id')
							->leftJoin('departments as department_origin_reviewed','purchase_enterprises.idDepartamentOriginR','department_origin_reviewed.id')
							->leftJoin('accounts as account_origin_reviewed','purchase_enterprises.idAccAccOriginR','account_origin_reviewed.idAccAcc')
							->leftJoin('projects as project_origin_reviewed','purchase_enterprises.idProjectOriginR','project_origin_reviewed.idProyect')
							->leftJoin('enterprises as enterprise_destiny_reviewed','purchase_enterprises.idEnterpriseDestinyR','enterprise_destiny_reviewed.id')
							->leftJoin('accounts as account_destiny_reviewed','purchase_enterprises.idAccAccDestinyR','account_destiny_reviewed.idAccAcc')
							->leftJoin('projects as project_destiny_reviewed','purchase_enterprises.idProjectDestinyR','project_destiny_reviewed.idproyect')
							->leftJoin('payment_methods','payment_methods.idpaymentMethod','purchase_enterprises.idpaymentMethod')
							->leftJoin('purchase_enterprise_details','purchase_enterprise_details.idpurchaseEnterprise','purchase_enterprises.idpurchaseEnterprise')
							->leftJoin(DB::raw('(SELECT idPurchaseEnterpriseDetail, SUM(amount) as taxes_amount FROM purchase_enterprise_taxes GROUP BY idPurchaseEnterpriseDetail) AS pe_taxes'),'purchase_enterprise_details.idPurchaseEnterpriseDetail','pe_taxes.idPurchaseEnterpriseDetail')
							->leftJoin(DB::raw('(SELECT idPurchaseEnterpriseDetail, SUM(amount) as retention_amount FROM purchase_enterprise_retentions GROUP BY idPurchaseEnterpriseDetail) AS pe_retention'),'purchase_enterprise_details.idPurchaseEnterpriseDetail','pe_retention.idPurchaseEnterpriseDetail')
							->whereIn('request_models.status',[3])
							->where('request_models.kind',13)
							->where(function ($query) use ($name, $mindate, $maxdate, $folio,$kind)
								{
									if($name != "")
									{
										$query->where(function($q) use($name)
										{
											$q->where(DB::raw("CONCAT_WS(' ',request_user.name,request_user.last_name,request_user.scnd_last_name)"),'LIKE','%'.$name.'%')
												->orWhere(DB::raw("CONCAT_WS(' ',elaborate_user.name,elaborate_user.last_name,elaborate_user.scnd_last_name)"),'LIKE','%'.$name.'%');
										});
									}
									if($folio != "")
									{
										$query->where('request_models.folio',$folio);
									}
									if($mindate != "" && $maxdate != "")
									{
										$query->whereBetween('request_models.fDate',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 23:59:59')]);
									}
									if ($kind != "") 
									{
										$query->where('request_models.kind',$kind);
									}
								})
							->orderBy('request_models.fDate','DESC')
							->orderBy('request_models.folio','DESC')
							->get();
			
			$requestsGroups 	= DB::table('request_models')
							->selectRaw('
								request_models.folio as folio,
								status_requests.description as status,
								groups.title as title,
								DATE_FORMAT(groups.datetitle, "%d-%m-%Y") as datetitle,
								CONCAT_WS(" ",request_user.name,request_user.last_name,request_user.scnd_last_name) as request_user,
								CONCAT_WS(" ",elaborate_user.name,elaborate_user.last_name,elaborate_user.scnd_last_name) as elaborate_user,
								DATE_FORMAT(request_models.fDate, "%d-%m-%Y %H:%i") as date,
								IF(request_models.taxPayment = 1,"Sí","No") as tax_payment,
								groups.numberOrder as number_order,
								groups.operationType as operation_type,
								IFNULL(enterprise_origin_reviewed.name, enterprise_origin.name) as enterprise_origin,
								IFNULL(area_origin_reviewed.name, area_origin.name) as area_origin,
								IFNULL(department_origin_reviewed.name, department_origin.name) as department_origin,
								IF(account_origin_reviewed.account IS NOT NULL, CONCAT_WS(" ",account_origin_reviewed.account,account_origin_reviewed.description), CONCAT_WS(" ",account_origin.account,account_origin.description))  as account_origin,
								IFNULL(project_origin_reviewed.proyectName, project_origin.proyectName) as project_origin,
								IFNULL(enterprise_destiny_reviewed.name, enterprise_destiny.name) as enterprise_destiny,
								IF(account_destiny_reviewed.account IS NOT NULL, CONCAT_WS(" ",account_destiny_reviewed.account,account_destiny_reviewed.description), CONCAT_WS(" ",account_destiny.account,account_destiny.description))  as account_destiny,
								groups_details.quantity as quantity,
								groups_details.unit as unit,
								groups_details.description as description,
								groups_details.unitPrice as unitPrice,
								groups_details.tax as tax,
								groups_taxes.taxes_amount as taxes_amount,
								groups_retentions.retention_amount as retention_amount,
								groups_details.amount as amount_detail,
								groups.reference as reference,
								groups.typeCurrency as type_currency,
								DATE_FORMAT(groups.paymentDate, "%d-%m-%Y") as payment_date,
								payment_methods.method as payment_method,
								groups.statusBill as status_bill,
								groups.amount as amount,
								groups.commission as commission,
								groups.amountRetake as amount_retake
							')
							->leftJoin('groups','groups.idFolio','request_models.folio')
							->leftJoin('status_requests','request_models.status','status_requests.idrequestStatus')
							->leftJoin('users as request_user','request_models.idRequest','request_user.id')
							->leftJoin('users as elaborate_user','request_models.idElaborate','elaborate_user.id')
							->leftJoin('enterprises as enterprise_origin','groups.idEnterpriseOrigin','enterprise_origin.id')
							->leftJoin('areas as area_origin','groups.idAreaOrigin','area_origin.id')
							->leftJoin('departments as department_origin','groups.idDepartamentOrigin','department_origin.id')
							->leftJoin('accounts as account_origin','groups.idAccAccOrigin','account_origin.idAccAcc')
							->leftJoin('projects as project_origin','groups.idProjectOrigin','project_origin.idproyect')
							->leftJoin('enterprises as enterprise_destiny','groups.idEnterpriseDestiny','enterprise_destiny.id')
							->leftJoin('accounts as account_destiny','groups.idAccAccDestiny','account_destiny.idAccAcc')
							->leftJoin('enterprises as enterprise_origin_reviewed','groups.idEnterpriseOriginR','enterprise_origin_reviewed.id')
							->leftJoin('areas as area_origin_reviewed','groups.idAreaOriginR','area_origin_reviewed.id')
							->leftJoin('departments as department_origin_reviewed','groups.idDepartamentOriginR','department_origin_reviewed.id')
							->leftJoin('accounts as account_origin_reviewed','groups.idAccAccOriginR','account_origin_reviewed.idAccAcc')
							->leftJoin('projects as project_origin_reviewed','groups.idProjectOriginR','project_origin_reviewed.idProyect')
							->leftJoin('enterprises as enterprise_destiny_reviewed','groups.idEnterpriseDestinyR','enterprise_destiny_reviewed.id')
							->leftJoin('accounts as account_destiny_reviewed','groups.idAccAccDestinyR','account_destiny_reviewed.idAccAcc')
							->leftJoin('payment_methods','payment_methods.idpaymentMethod','groups.idpaymentMethod')
							->leftJoin('groups_details','groups_details.idgroups','groups.idgroups')
							->leftJoin(DB::raw('(SELECT idgroupsDetail, SUM(amount) as taxes_amount FROM groups_taxes GROUP BY idgroupsDetail) AS groups_taxes'),'groups_details.idgroupsDetail','groups_taxes.idgroupsDetail')
							->leftJoin(DB::raw('(SELECT idgroupsDetail, SUM(amount) as retention_amount FROM groups_retentions GROUP BY idgroupsDetail) AS groups_retentions'),'groups_details.idgroupsDetail','groups_retentions.idgroupsDetail')
							->whereIn('request_models.status',[3])
							->where('request_models.kind',14)
							->where(function ($query) use ($name, $mindate, $maxdate, $folio,$kind)
							{
								if($name != "")
								{
									$query->where(function($q) use($name)
									{
										$q->where(DB::raw("CONCAT_WS(' ',request_user.name,request_user.last_name,request_user.scnd_last_name)"),'LIKE','%'.$name.'%')
											->orWhere(DB::raw("CONCAT_WS(' ',elaborate_user.name,elaborate_user.last_name,elaborate_user.scnd_last_name)"),'LIKE','%'.$name.'%');
									});
								}
								if($folio != "")
								{
									$query->where('request_models.folio',$folio);
								}
								if($mindate != "" && $maxdate != "")
								{
									$query->whereBetween('request_models.fDate',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 23:59:59')]);
								}
								if ($kind != "") 
								{
									$query->where('request_models.kind',$kind);
								}
							})
							->orderBy('request_models.fDate','DESC')
							->orderBy('request_models.folio','DESC')
							->get();

			$requestsMovements = DB::table('request_models')
							->selectRaw('
								request_models.folio as folio,
								status_requests.description as status,
								movements_enterprises.title as title,
								DATE_FORMAT(movements_enterprises.datetitle, "%d-%m-%Y") as datetitle,
								CONCAT_WS(" ",request_user.name,request_user.last_name,request_user.scnd_last_name) as request_user,
								CONCAT_WS(" ",elaborate_user.name,elaborate_user.last_name,elaborate_user.scnd_last_name) as elaborate_user,
								DATE_FORMAT(request_models.fDate, "%d-%m-%Y %H:%i") as date,
								IF(request_models.taxPayment = 1,"Sí","No") as tax_payment,
								IFNULL(enterprise_origin_reviewed.name, enterprise_origin.name) as enterprise_origin,
								IF(account_origin_reviewed.account IS NOT NULL, CONCAT_WS(" ",account_origin_reviewed.account,account_origin_reviewed.description), CONCAT_WS(" ",account_origin.account,account_origin.description))  as account_origin,
								IFNULL(enterprise_destiny_reviewed.name, enterprise_destiny.name) as enterprise_destiny,
								IF(account_destiny_reviewed.account IS NOT NULL, CONCAT_WS(" ",account_destiny_reviewed.account,account_destiny_reviewed.description), CONCAT_WS(" ",account_destiny.account,account_destiny.description))  as account_destiny,
								movements_enterprises.typeCurrency as currency,
								DATE_FORMAT(movements_enterprises.paymentDate, "%d-%m-%Y") as payment_date,
								payment_methods.method as payment_method,
								movements_enterprises.amount as amount
							')
							->leftJoin('movements_enterprises','movements_enterprises.idFolio','request_models.folio')
							->leftJoin('status_requests','request_models.status','status_requests.idrequestStatus')
							->leftJoin('users as request_user','request_models.idRequest','request_user.id')
							->leftJoin('users as elaborate_user','request_models.idElaborate','elaborate_user.id')
							->leftJoin('enterprises as enterprise_origin','movements_enterprises.idEnterpriseOrigin','enterprise_origin.id')
							->leftJoin('accounts as account_origin','movements_enterprises.idAccAccOrigin','account_origin.idAccAcc')
							->leftJoin('enterprises as enterprise_destiny','movements_enterprises.idEnterpriseDestiny','enterprise_destiny.id')
							->leftJoin('accounts as account_destiny','movements_enterprises.idAccAccDestiny','account_destiny.idAccAcc')
							->leftJoin('enterprises as enterprise_origin_reviewed','movements_enterprises.idEnterpriseOriginR','enterprise_origin_reviewed.id')
							->leftJoin('accounts as account_origin_reviewed','movements_enterprises.idAccAccOriginR','account_origin_reviewed.idAccAcc')
							->leftJoin('enterprises as enterprise_destiny_reviewed','movements_enterprises.idEnterpriseDestinyR','enterprise_destiny_reviewed.id')
							->leftJoin('accounts as account_destiny_reviewed','movements_enterprises.idAccAccDestinyR','account_destiny_reviewed.idAccAcc')
							->leftJoin('payment_methods','payment_methods.idpaymentMethod','movements_enterprises.idpaymentMethod')							
							->whereIn('request_models.status',[3])
							->where('request_models.kind',15)
							->where(function ($query) use ($name, $mindate, $maxdate, $folio,$kind)
							{
								if($name != "")
								{
									$query->where(function($q) use($name)
									{
										$q->where(DB::raw("CONCAT_WS(' ',request_user.name,request_user.last_name,request_user.scnd_last_name)"),'LIKE','%'.$name.'%')
											->orWhere(DB::raw("CONCAT_WS(' ',elaborate_user.name,elaborate_user.last_name,elaborate_user.scnd_last_name)"),'LIKE','%'.$name.'%');
									});
								}
								if($folio != "")
								{
									$query->where('request_models.folio',$folio);
								}
								if($mindate != "" && $maxdate != "")
								{
									$query->whereBetween('request_models.fDate',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 23:59:59')]);
								}
								if ($kind != "") 
								{
									$query->where('request_models.kind',$kind);
								}
							})
							->orderBy('request_models.fDate','DESC')
							->orderBy('request_models.folio','DESC')
							->get();

			$new_sheet 		= true;
			$defaultStyle	= (new StyleBuilder())->setFontName('Calibri')->setFontSize(12)->setCellAlignment(CellAlignment::LEFT)->build();
			$currencyFormat	= (new StyleBuilder())->setFormat('_("$"* #,##0.00_),_("$"* \(#,##0.00\),_("$"* "-"??_),_(@_)')->build();
			$rowDark		= (new StyleBuilder())->setBackgroundColor('F0F0F0')->build();
			$mhStyleCol1	= (new StyleBuilder())->setBackgroundColor('000000')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$mhStyleCol2	= (new StyleBuilder())->setBackgroundColor('1d353d')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$mhStyleCol3	= (new StyleBuilder())->setBackgroundColor('104f64')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$writer			= WriterEntityFactory::createXLSXWriter();
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('Movimientos Entre Cuentas.xlsx');

			if (count($requestsAdjustment)>0) 
			{
				$new_sheet		= false;
				$writer->getCurrentSheet()->setName('Ajuste de Movimientos');
				$headers		= ['Ajuste de Movimientos','','','','','','','','','','','','','','','','',''];
				$tempHeaders	= [];
				foreach($headers as $k => $header)
				{
					$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol1);
				}
				$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
				$writer->addRow($rowFromValues);

				$subHeaders		= ['Datos generales','','','','','','','','Cuenta de Origen','Cuenta de Destino','','','','','Condiciones de pago','','',''];
				$tempSubHeader	= [];
				foreach($subHeaders as $k => $subHeader)
				{
					$tempSubHeader[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol2);
				}
				$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
				$writer->addRow($rowFromValues);

				$subHeaders		= ['Folio','Estado','Título','Fecha','Solicitante','Elaborado por','Fecha de elaboración','Comentarios','Empresa de origen','Empresa de destino','Dirección de destino','Departamento de destino','Clasificación del gasto de destino','Proyecto de destino','Tipo de moneda','Fecha de pago','Forma de pago','Importe total a pagar'];
				$tempSubHeader	= [];
				foreach($subHeaders as $k => $subHeader)
				{
					$tempSubHeader[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol3);
				}
				$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
				$writer->addRow($rowFromValues);

				$tempFolio	= '';
				$kindRow	= true;

				foreach($requestsAdjustment as $request)
				{
					if($tempFolio != $request->folio)
					{
						$tempFolio = $request->folio;
						$kindRow = !$kindRow;
					}

					$tmpArr = [];
					foreach($request as $k => $r)
					{
						if(in_array($k,['amount']))
						{
							if($r != '')
							{
								$tmpArr[] = WriterEntityFactory::createCell((double)$r, $currencyFormat);
							}
							else
							{
								$tmpArr[] = WriterEntityFactory::createCell($r);
							}
						}
						else
						{
							$tmpArr[] = WriterEntityFactory::createCell($r);
						}
					}

					if($kindRow)
					{
						$rowFromValues = WriterEntityFactory::createRow($tmpArr,$rowDark);
					}
					else
					{
						$rowFromValues = WriterEntityFactory::createRow($tmpArr);
					}
					$writer->addRow($rowFromValues);
				}
			}

			if (count($requestsLoan)>0) 
			{
				if ($new_sheet) 
				{
					$writer->getCurrentSheet()->setName('Préstamo Inter-Empresa');
					$new_sheet = false;
				}
				else
				{
					$newSheet = $writer->addNewSheetAndMakeItCurrent();
					$writer->getCurrentSheet()->setName('Préstamo Inter-Empresa');
				}

				$headers		= ['Préstamo Inter-Empresa','','','','','','','','','','','','','','',''];
				$tempHeaders	= [];
				foreach($headers as $k => $header)
				{
					$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol1);
				}
				$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
				$writer->addRow($rowFromValues);

				$subHeaders		= ['Datos generales','','','','','','','','Cuenta de Origen','','Cuenta de Destino','','Condiciones de pago','','',''];
				$tempSubHeader	= [];
				foreach($subHeaders as $k => $subHeader)
				{
					$tempSubHeader[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol2);
				}
				$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
				$writer->addRow($rowFromValues);

				$subHeaders		= ['Folio','Estado','Título','Fecha','Solicitante','Elaborado por','Fecha de elaboración','Fiscal','Empresa de origen','Clasificación del gasto de origen','Empresa de destino','Clasificación del gasto de destino','Tipo de moneda','Fecha de pago','Forma de pago','Importe total a pagar'];
				$tempSubHeader	= [];
				foreach($subHeaders as $k => $subHeader)
				{
					$tempSubHeader[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol3);
				}
				$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
				$writer->addRow($rowFromValues);

				$tempFolio	= '';
				$kindRow	= true;

				foreach($requestsLoan as $request)
				{
					if($tempFolio != $request->folio)
					{
						$tempFolio = $request->folio;
						$kindRow = !$kindRow;
					}

					$tmpArr = [];
					foreach($request as $k => $r)
					{
						if(in_array($k,['amount']))
						{
							if($r != '')
							{
								$tmpArr[] = WriterEntityFactory::createCell((double)$r, $currencyFormat);
							}
							else
							{
								$tmpArr[] = WriterEntityFactory::createCell($r);
							}
						}
						else
						{
							$tmpArr[] = WriterEntityFactory::createCell($r);
						}
					}

					if($kindRow)
					{
						$rowFromValues = WriterEntityFactory::createRow($tmpArr,$rowDark);
					}
					else
					{
						$rowFromValues = WriterEntityFactory::createRow($tmpArr);
					}
					$writer->addRow($rowFromValues);
				}
			}

			if (count($requestsPurchase)>0) 
			{
				if ($new_sheet) 
				{
					$writer->getCurrentSheet()->setName('Compras Inter-Empresa');
					$new_sheet = false;
				}
				else
				{
					$newSheet = $writer->addNewSheetAndMakeItCurrent();
					$writer->getCurrentSheet()->setName('Compras Inter-Empresa');
				}

				$headers		= ['Compras Inter-Empresa','','','','','','','','','','','','','','','','','','','','','','','','','','','',''];
				$tempHeaders	= [];
				foreach($headers as $k => $header)
				{
					$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol1);
				}
				$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
				$writer->addRow($rowFromValues);

				$subHeaders		= ['Datos generales','','','','','','','','','Cuenta de Origen','','','','','Cuenta de Destino','','','Datos de la solicitud','','','','','','','','Condiciones de pago','','',''];
				$tempSubHeader	= [];
				foreach($subHeaders as $k => $subHeader)
				{
					$tempSubHeader[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol2);
				}
				$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
				$writer->addRow($rowFromValues);

				$subHeaders		= ['Folio','Estado','Título','Fecha','Solicitante','Elaborado por','Fecha de elaboración','Fiscal','Número de orden','Empresa de origen','Clasificación del gasto de origen','Proyecto de origen','Empresa de destino','Dirección de destino','Departamento de destino','Clasificación del gasto de destino','Proyecto de destino','Cantidad','Unidad','Descripción','Precio Unitario','IVA','Impuesto Adicional','Retenciones','Total','Tipo de moneda','Fecha de pago','Forma de pago','Importe total a pagar'];
				$tempSubHeader	= [];
				foreach($subHeaders as $k => $subHeader)
				{
					$tempSubHeader[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol3);
				}
				$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
				$writer->addRow($rowFromValues);

				$tempFolio	= '';
				$kindRow	= true;

				foreach($requestsPurchase as $request)
				{
					if($tempFolio != $request->folio)
					{
						$tempFolio = $request->folio;
						$kindRow = !$kindRow;
					}
					else
					{
						$request->folio					= null;
						$request->status				= '';
						$request->title					= '';
						$request->datetitle				= '';
						$request->request_user			= '';
						$request->elaborate_user		= '';
						$request->date					= '';
						$request->tax_payment			= '';
						$request->number_order			= '';
						$request->enterprise_origin		= '';
						$request->area_origin			= '';
						$request->department_origin		= '';
						$request->account_origin		= '';
						$request->project_origin		= '';
						$request->enterprise_destiny	= '';
						$request->account_destiny		= '';
						$request->project_destiny		= '';
						$request->currency				= '';
						$request->payment_date			= '';
						$request->payment_method		= '';
						$request->amount				= '';
					}

					$tmpArr = [];
					foreach($request as $k => $r)
					{
						if(in_array($k,['amount','unitPrice','tax','taxes_amount','retention_amount','amount_detail']))
						{
							if($r != '')
							{
								$tmpArr[] = WriterEntityFactory::createCell((double)$r, $currencyFormat);
							}
							else
							{
								$tmpArr[] = WriterEntityFactory::createCell($r);
							}
						}
						elseif(in_array($k,['quantity']))
						{
							$tmpArr[] = WriterEntityFactory::createCell((double)$r);
						}
						else
						{
							$tmpArr[] = WriterEntityFactory::createCell($r);
						}
					}

					if($kindRow)
					{
						$rowFromValues = WriterEntityFactory::createRow($tmpArr,$rowDark);
					}
					else
					{
						$rowFromValues = WriterEntityFactory::createRow($tmpArr);
					}
					$writer->addRow($rowFromValues);
				}
			}

			if (count($requestsGroups)>0) 
			{
				if ($new_sheet) 
				{
					$writer->getCurrentSheet()->setName('Grupos');
					$new_sheet = false;
				}
				else
				{
					$newSheet = $writer->addNewSheetAndMakeItCurrent();
					$writer->getCurrentSheet()->setName('Grupos');
				}

				$headers		= ['Grupos','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',''];
				$tempHeaders	= [];
				foreach($headers as $k => $header)
				{
					$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol1);
				}
				$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
				$writer->addRow($rowFromValues);

				$subHeaders		= ['Datos generales','','','','','','','','','','Cuenta de Origen','','','','','Cuenta de Destino','','Datos de la solicitud','','','','','','','','Condiciones de pago','','','','','','',''];
				$tempSubHeader	= [];
				foreach($subHeaders as $k => $subHeader)
				{
					$tempSubHeader[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol2);
				}
				$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
				$writer->addRow($rowFromValues);

				$subHeaders		= ['Folio','Estado','Título','Fecha','Solicitante','Elaborado por','Fecha de elaboración','Fiscal','Número de orden','Tipo de operación','Empresa de origen','Dirección de origen','Departamento de origen','Clasificación del gasto de origen','Proyecto de origen','Empresa de destino','Clasificación del gasto de destino','Cantidad','Unidad','Descripción','Precio Unitario','IVA','Impuesto Adicional','Retenciones','Total','Referencia/Número de Factura','Tipo de moneda','Fecha de pago','Forma de pago','Estado  de factura','Importe total a pagar','Comisión','Importe a retomar'];
				$tempSubHeader	= [];
				foreach($subHeaders as $k => $subHeader)
				{
					$tempSubHeader[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol3);
				}
				$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
				$writer->addRow($rowFromValues);

				$tempFolio	= '';
				$kindRow	= true;

				foreach($requestsGroups as $request)
				{
					if($tempFolio != $request->folio)
					{
						$tempFolio = $request->folio;
						$kindRow = !$kindRow;
					}
					else
					{
						$request->folio					= null;
						$request->status				= '';
						$request->title					= '';
						$request->datetitle				= '';
						$request->request_user			= '';
						$request->elaborate_user		= '';
						$request->date					= '';
						$request->tax_payment			= '';
						$request->number_order			= '';
						$request->operation_type		= '';
						$request->enterprise_origin		= '';
						$request->area_origin			= '';
						$request->department_origin		= '';
						$request->account_origin		= '';
						$request->project_origin		= '';
						$request->enterprise_destiny	= '';
						$request->account_destiny		= '';
						$request->reference				= '';
						$request->type_currency			= '';
						$request->payment_date			= '';
						$request->payment_method		= '';
						$request->status_bill			= '';
						$request->amount				= '';
						$request->commission			= '';
						$request->amount_retake			= '';
					}

					$tmpArr = [];
					foreach($request as $k => $r)
					{
						if(in_array($k,['unitPrice','tax','taxes_amount','retention_amount','amount_detail','amount','commission','amount_retake']))
						{
							if($r != '')
							{
								$tmpArr[] = WriterEntityFactory::createCell((double)$r, $currencyFormat);
							}
							else
							{
								$tmpArr[] = WriterEntityFactory::createCell($r);
							}
						}
						elseif(in_array($k,['quantity']))
						{
							$tmpArr[] = WriterEntityFactory::createCell((double)$r);
						}
						else
						{
							$tmpArr[] = WriterEntityFactory::createCell($r);
						}
					}

					if($kindRow)
					{
						$rowFromValues = WriterEntityFactory::createRow($tmpArr,$rowDark);
					}
					else
					{
						$rowFromValues = WriterEntityFactory::createRow($tmpArr);
					}
					$writer->addRow($rowFromValues);
				}
			}

			if (count($requestsMovements)>0) 
			{
				if ($new_sheet) 
				{
					$writer->getCurrentSheet()->setName('Movimientos Misma Empresa');
					$new_sheet = false;
				}
				else
				{
					$newSheet = $writer->addNewSheetAndMakeItCurrent();
					$writer->getCurrentSheet()->setName('Movimientos Misma Empresa');
				}

				$new_sheet		= false;
				$headers		= ['Movimientos Misma Empresa','','','','','','','','','','','','','','',''];
				$tempHeaders	= [];
				foreach($headers as $k => $header)
				{
					$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol1);
				}
				$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
				$writer->addRow($rowFromValues);

				$subHeaders		= ['Datos generales','','','','','','','','Cuenta de Origen','','Cuenta de Destino','','Condiciones de pago','','',''];
				$tempSubHeader	= [];
				foreach($subHeaders as $k => $subHeader)
				{
					$tempSubHeader[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol2);
				}
				$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
				$writer->addRow($rowFromValues);

				$subHeaders		= ['Folio','Estado','Título','Fecha','Solicitante','Elaborado por','Fecha de elaboración','Fiscal','Empresa de origen','Clasificación del gasto de origen','Empresa de destino','Clasificación del gasto de destino','Tipo de moneda','Fecha de pago','Forma de pago','Importe total a pagar'];
				$tempSubHeader	= [];
				foreach($subHeaders as $k => $subHeader)
				{
					$tempSubHeader[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol3);
				}
				$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
				$writer->addRow($rowFromValues);

				$tempFolio	= '';
				$kindRow	= true;

				foreach($requestsMovements as $request)
				{
					if($tempFolio != $request->folio)
					{
						$tempFolio = $request->folio;
						$kindRow = !$kindRow;
					}

					$tmpArr = [];
					foreach($request as $k => $r)
					{
						if(in_array($k,['amount']))
						{
							if($r != '')
							{
								$tmpArr[] = WriterEntityFactory::createCell((double)$r, $currencyFormat);
							}
							else
							{
								$tmpArr[] = WriterEntityFactory::createCell($r);
							}
						}
						else
						{
							$tmpArr[] = WriterEntityFactory::createCell($r);
						}
					}

					if($kindRow)
					{
						$rowFromValues = WriterEntityFactory::createRow($tmpArr,$rowDark);
					}
					else
					{
						$rowFromValues = WriterEntityFactory::createRow($tmpArr);
					}
					$writer->addRow($rowFromValues);
				}
			}
			
			return $writer->close();
		}
		else
		{
			return redirect('/');
		}
	}

	public function authorizationExcel(Request $request)
	{
		if(Auth::user()->module->where('id',151)->count()>0)
		{
			$data       = App\Module::find($this->module_id);
			$name       = $request->name;
			$folio      = $request->folio;
			$mindate    = $request->mindate!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate) : null;
			$maxdate    = $request->maxdate!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate) : null;
			$kind       = $request->kind;

			$requestsAdjustment = DB::table('request_models')
							->selectRaw('
								request_models.folio as folio,
								status_requests.description as status,
								adjustments.title as title,
								DATE_FORMAT(adjustments.datetitle, "%d-%m-%Y") as datetitle,
								CONCAT_WS(" ",request_user.name,request_user.last_name,request_user.scnd_last_name) as request_user,
								CONCAT_WS(" ",elaborate_user.name,elaborate_user.last_name,elaborate_user.scnd_last_name) as elaborate_user,
								DATE_FORMAT(request_models.fDate, "%d-%m-%Y %H:%i") as date,
								adjustments.commentaries as commentaries,
								IFNULL(enterprise_origin_reviewed.name, enterprise_origin.name) as enterprise_origin,
								IFNULL(enterprise_destiny_reviewed.name, enterprise_destiny.name) as enterprise_destiny,
								IFNULL(direction_destiny_reviewed.name, direction_destiny.name) as direction_destiny,
								IFNULL(department_destiny_reviewed.name, department_destiny.name) as department_destiny,
								IF(account_destiny_reviewed.account IS NOT NULL,CONCAT_WS(" - ",account_destiny_reviewed.account,account_destiny_reviewed.description), CONCAT_WS(" - ",account_destiny.account,account_destiny.description)) as account_destiny,
								IFNULL(project_destiny_reviewed.proyectName, project_destiny.proyectName) as project_destiny,
								adjustments.currency as currency,
								DATE_FORMAT(adjustments.paymentDate, "%d-%m-%Y") as payment_date,
								payment_methods.method as payment_method,
								adjustments.amount as amount
							')
							->leftJoin('adjustments','adjustments.idFolio','request_models.folio')
							->leftJoin('status_requests','request_models.status','status_requests.idrequestStatus')
							->leftJoin('users as request_user','request_models.idRequest','request_user.id')
							->leftJoin('users as elaborate_user','request_models.idElaborate','elaborate_user.id')
							->leftJoin('enterprises as enterprise_origin','adjustments.idEnterpriseOrigin','enterprise_origin.id')
							->leftJoin('enterprises as enterprise_destiny','adjustments.idEnterpriseDestiny','enterprise_destiny.id')
							->leftJoin('areas as direction_destiny','adjustments.idAreaDestiny','direction_destiny.id')
							->leftJoin('departments as department_destiny','adjustments.idDepartamentDestiny','department_destiny.id')
							->leftJoin('accounts as account_destiny','adjustments.idAccAccDestiny','account_destiny.idAccAcc')
							->leftJoin('projects as project_destiny','adjustments.idProjectDestiny','project_destiny.idproyect')
							->leftJoin('enterprises as enterprise_origin_reviewed','adjustments.idEnterpriseOriginR','enterprise_origin_reviewed.id')
							->leftJoin('enterprises as enterprise_destiny_reviewed','adjustments.idEnterpriseDestinyR','enterprise_destiny_reviewed.id')
							->leftJoin('areas as direction_destiny_reviewed','adjustments.idAreaDestinyR','direction_destiny_reviewed.id')
							->leftJoin('departments as department_destiny_reviewed','adjustments.idDepartamentDestinyR','department_destiny_reviewed.id')
							->leftJoin('accounts as account_destiny_reviewed','adjustments.idAccAccDestinyR','account_destiny_reviewed.idAccAcc')
							->leftJoin('projects as project_destiny_reviewed','adjustments.idProjectDestinyR','project_destiny_reviewed.idproyect')
							->leftJoin('payment_methods','payment_methods.idpaymentMethod','adjustments.idpaymentMethod')
							->whereIn('request_models.status',[4])
							->where('request_models.kind',11)
							->where(function ($query) use ($name, $mindate, $maxdate, $folio,$kind)
							{
								if($name != "")
								{
									$query->where(function($q) use($name)
									{
										$q->where(DB::raw("CONCAT_WS(' ',request_user.name,request_user.last_name,request_user.scnd_last_name)"),'LIKE','%'.$name.'%')
											->orWhere(DB::raw("CONCAT_WS(' ',elaborate_user.name,elaborate_user.last_name,elaborate_user.scnd_last_name)"),'LIKE','%'.$name.'%');
									});
								}
								if($folio != "")
								{
									$query->where('request_models.folio',$folio);
								}
								if($mindate != "" && $maxdate != "")
								{
									$query->whereBetween('request_models.reviewDate',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 23:59:59')]);
								}
								if ($kind != "") 
								{
									$query->where('request_models.kind',$kind);
								}
							})
							->orderBy('request_models.fDate','DESC')
							->orderBy('request_models.folio','DESC')
							->get();

			$requestsLoan 	= DB::table('request_models')
							->selectRaw('
								request_models.folio as folio,
								status_requests.description as status,
								loan_enterprises.title as title,
								DATE_FORMAT(loan_enterprises.datetitle, "%d-%m-%Y") as datetitle,
								CONCAT_WS(" ",request_user.name,request_user.last_name,request_user.scnd_last_name) as request_user,
								CONCAT_WS(" ",elaborate_user.name,elaborate_user.last_name,elaborate_user.scnd_last_name) as elaborate_user,
								DATE_FORMAT(request_models.fDate, "%d-%m-%Y %H:%i") as date,
								IF(request_models.taxPayment = 1,"Sí","No") as tax_payment,
								IFNULL(enterprise_origin_reviewed.name, enterprise_origin.name) as enterprise_origin,
								IF(account_origin_reviewed.account IS NOT NULL, CONCAT_WS(" ",account_origin_reviewed.account,account_origin_reviewed.description), CONCAT_WS(" ",account_origin.account,account_origin.description))  as account_origin,
								IFNULL(enterprise_destiny_reviewed.name, enterprise_destiny.name) as enterprise_destiny,
								IF(account_destiny_reviewed.account IS NOT NULL, CONCAT_WS(" ",account_destiny_reviewed.account,account_destiny_reviewed.description), CONCAT_WS(" ",account_destiny.account,account_destiny.description))  as account_destiny,
								loan_enterprises.currency as currency,
								DATE_FORMAT(loan_enterprises.paymentDate, "%d-%m-%Y") as payment_date,
								payment_methods.method as payment_method,
								loan_enterprises.amount as amount
							')
							->leftJoin('loan_enterprises','loan_enterprises.idFolio','request_models.folio')
							->leftJoin('status_requests','request_models.status','status_requests.idrequestStatus')
							->leftJoin('users as request_user','request_models.idRequest','request_user.id')
							->leftJoin('users as elaborate_user','request_models.idElaborate','elaborate_user.id')
							->leftJoin('enterprises as enterprise_origin','loan_enterprises.idEnterpriseOrigin','enterprise_origin.id')
							->leftJoin('accounts as account_origin','loan_enterprises.idAccAccOrigin','account_origin.idAccAcc')
							->leftJoin('enterprises as enterprise_destiny','loan_enterprises.idEnterpriseDestiny','enterprise_destiny.id')
							->leftJoin('accounts as account_destiny','loan_enterprises.idAccAccDestiny','account_destiny.idAccAcc')
							->leftJoin('enterprises as enterprise_origin_reviewed','loan_enterprises.idEnterpriseOriginR','enterprise_origin_reviewed.id')
							->leftJoin('accounts as account_origin_reviewed','loan_enterprises.idAccAccOriginR','account_origin_reviewed.idAccAcc')
							->leftJoin('enterprises as enterprise_destiny_reviewed','loan_enterprises.idEnterpriseDestinyR','enterprise_destiny_reviewed.id')
							->leftJoin('accounts as account_destiny_reviewed','loan_enterprises.idAccAccDestinyR','account_destiny_reviewed.idAccAcc')
							->leftJoin('payment_methods','payment_methods.idpaymentMethod','loan_enterprises.idpaymentMethod')						
							->whereIn('request_models.status',[4])
							->where('request_models.kind',12)
							->where(function ($query) use ($name, $mindate, $maxdate, $folio,$kind)
								{
									if($name != "")
									{
										$query->where(function($q) use($name)
										{
											$q->where(DB::raw("CONCAT_WS(' ',request_user.name,request_user.last_name,request_user.scnd_last_name)"),'LIKE','%'.$name.'%')
												->orWhere(DB::raw("CONCAT_WS(' ',elaborate_user.name,elaborate_user.last_name,elaborate_user.scnd_last_name)"),'LIKE','%'.$name.'%');
										});
									}
									if($folio != "")
									{
										$query->where('request_models.folio',$folio);
									}
									if($mindate != "" && $maxdate != "")
									{
										$query->whereBetween('request_models.reviewDate',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 23:59:59')]);
									}
									if ($kind != "") 
									{
										$query->where('request_models.kind',$kind);
									}
								})
							->orderBy('request_models.fDate','DESC')
							->orderBy('request_models.folio','DESC')
							->get();
			
			$requestsPurchase 	= DB::table('request_models')
							->selectRaw('
								request_models.folio as folio,
								status_requests.description as status,
								purchase_enterprises.title as title,
								DATE_FORMAT(purchase_enterprises.datetitle, "%d-%m-%Y") as datetitle,
								CONCAT_WS(" ",request_user.name,request_user.last_name,request_user.scnd_last_name) as request_user,
								CONCAT_WS(" ",elaborate_user.name,elaborate_user.last_name,elaborate_user.scnd_last_name) as elaborate_user,
								DATE_FORMAT(request_models.fDate, "%d-%m-%Y %H:%i") as date,
								IF(request_models.taxPayment = 1,"Sí","No") as tax_payment,
								purchase_enterprises.numberOrder as number_order,
								IFNULL(enterprise_origin_reviewed.name, enterprise_origin.name) as enterprise_origin,
								IFNULL(area_origin_reviewed.name, area_origin.name) as area_origin,
								IFNULL(department_origin_reviewed.name, department_origin.name) as department_origin,
								IF(account_origin_reviewed.account IS NOT NULL, CONCAT_WS(" ",account_origin_reviewed.account,account_origin_reviewed.description), CONCAT_WS(" ",account_origin.account,account_origin.description))  as account_origin,
								IFNULL(project_origin_reviewed.proyectName, project_origin.proyectName) as project_origin,
								IFNULL(enterprise_destiny_reviewed.name, enterprise_destiny.name) as enterprise_destiny,
								IF(account_destiny_reviewed.account IS NOT NULL, CONCAT_WS(" ",account_destiny_reviewed.account,account_destiny_reviewed.description), CONCAT_WS(" ",account_destiny.account,account_destiny.description))  as account_destiny,
								IFNULL(project_destiny_reviewed.proyectName, project_destiny.proyectName) as project_destiny,
								purchase_enterprise_details.quantity as quantity,
								purchase_enterprise_details.unit as unit,
								purchase_enterprise_details.description as description,
								purchase_enterprise_details.unitPrice as unitPrice,
								purchase_enterprise_details.tax as tax,
								pe_taxes.taxes_amount as taxes_amount,
								pe_retention.retention_amount as retention_amount,
								purchase_enterprise_details.amount as amount_detail,
								purchase_enterprises.typeCurrency as currency,
								DATE_FORMAT(purchase_enterprises.paymentDate, "%d-%m-%Y") as payment_date,
								payment_methods.method as payment_method,
								purchase_enterprises.amount as amount
							')
							->leftJoin('purchase_enterprises','purchase_enterprises.idFolio','request_models.folio')
							->leftJoin('status_requests','request_models.status','status_requests.idrequestStatus')
							->leftJoin('users as request_user','request_models.idRequest','request_user.id')
							->leftJoin('users as elaborate_user','request_models.idElaborate','elaborate_user.id')
							->leftJoin('enterprises as enterprise_origin','purchase_enterprises.idEnterpriseOrigin','enterprise_origin.id')
							->leftJoin('areas as area_origin','purchase_enterprises.idAreaOrigin','area_origin.id')
							->leftJoin('departments as department_origin','purchase_enterprises.idDepartamentOrigin','department_origin.id')
							->leftJoin('accounts as account_origin','purchase_enterprises.idAccAccOrigin','account_origin.idAccAcc')
							->leftJoin('projects as project_origin','purchase_enterprises.idProjectOrigin','project_origin.idProyect')
							->leftJoin('enterprises as enterprise_destiny','purchase_enterprises.idEnterpriseDestiny','enterprise_destiny.id')
							->leftJoin('accounts as account_destiny','purchase_enterprises.idAccAccDestiny','account_destiny.idAccAcc')
							->leftJoin('projects as project_destiny','purchase_enterprises.idProjectDestiny','project_destiny.idproyect')
							->leftJoin('enterprises as enterprise_origin_reviewed','purchase_enterprises.idEnterpriseOriginR','enterprise_origin_reviewed.id')
							->leftJoin('areas as area_origin_reviewed','purchase_enterprises.idAreaOriginR','area_origin_reviewed.id')
							->leftJoin('departments as department_origin_reviewed','purchase_enterprises.idDepartamentOriginR','department_origin_reviewed.id')
							->leftJoin('accounts as account_origin_reviewed','purchase_enterprises.idAccAccOriginR','account_origin_reviewed.idAccAcc')
							->leftJoin('projects as project_origin_reviewed','purchase_enterprises.idProjectOriginR','project_origin_reviewed.idProyect')
							->leftJoin('enterprises as enterprise_destiny_reviewed','purchase_enterprises.idEnterpriseDestinyR','enterprise_destiny_reviewed.id')
							->leftJoin('accounts as account_destiny_reviewed','purchase_enterprises.idAccAccDestinyR','account_destiny_reviewed.idAccAcc')
							->leftJoin('projects as project_destiny_reviewed','purchase_enterprises.idProjectDestinyR','project_destiny_reviewed.idproyect')
							->leftJoin('payment_methods','payment_methods.idpaymentMethod','purchase_enterprises.idpaymentMethod')
							->leftJoin('purchase_enterprise_details','purchase_enterprise_details.idpurchaseEnterprise','purchase_enterprises.idpurchaseEnterprise')
							->leftJoin(DB::raw('(SELECT idPurchaseEnterpriseDetail, SUM(amount) as taxes_amount FROM purchase_enterprise_taxes GROUP BY idPurchaseEnterpriseDetail) AS pe_taxes'),'purchase_enterprise_details.idPurchaseEnterpriseDetail','pe_taxes.idPurchaseEnterpriseDetail')
							->leftJoin(DB::raw('(SELECT idPurchaseEnterpriseDetail, SUM(amount) as retention_amount FROM purchase_enterprise_retentions GROUP BY idPurchaseEnterpriseDetail) AS pe_retention'),'purchase_enterprise_details.idPurchaseEnterpriseDetail','pe_retention.idPurchaseEnterpriseDetail')
							->whereIn('request_models.status',[4])
							->where('request_models.kind',13)
							->where(function ($query) use ($name, $mindate, $maxdate, $folio,$kind)
								{
									if($name != "")
									{
										$query->where(function($q) use($name)
										{
											$q->where(DB::raw("CONCAT_WS(' ',request_user.name,request_user.last_name,request_user.scnd_last_name)"),'LIKE','%'.$name.'%')
												->orWhere(DB::raw("CONCAT_WS(' ',elaborate_user.name,elaborate_user.last_name,elaborate_user.scnd_last_name)"),'LIKE','%'.$name.'%');
										});
									}
									if($folio != "")
									{
										$query->where('request_models.folio',$folio);
									}
									if($mindate != "" && $maxdate != "")
									{
										$query->whereBetween('request_models.reviewDate',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 23:59:59')]);
									}
									if ($kind != "") 
									{
										$query->where('request_models.kind',$kind);
									}
								})
							->orderBy('request_models.fDate','DESC')
							->orderBy('request_models.folio','DESC')
							->get();
			
			$requestsGroups 	= DB::table('request_models')
							->selectRaw('
								request_models.folio as folio,
								status_requests.description as status,
								groups.title as title,
								DATE_FORMAT(groups.datetitle, "%d-%m-%Y") as datetitle,
								CONCAT_WS(" ",request_user.name,request_user.last_name,request_user.scnd_last_name) as request_user,
								CONCAT_WS(" ",elaborate_user.name,elaborate_user.last_name,elaborate_user.scnd_last_name) as elaborate_user,
								DATE_FORMAT(request_models.fDate, "%d-%m-%Y %H:%i") as date,
								IF(request_models.taxPayment = 1,"Sí","No") as tax_payment,
								groups.numberOrder as number_order,
								groups.operationType as operation_type,
								IFNULL(enterprise_origin_reviewed.name, enterprise_origin.name) as enterprise_origin,
								IFNULL(area_origin_reviewed.name, area_origin.name) as area_origin,
								IFNULL(department_origin_reviewed.name, department_origin.name) as department_origin,
								IF(account_origin_reviewed.account IS NOT NULL, CONCAT_WS(" ",account_origin_reviewed.account,account_origin_reviewed.description), CONCAT_WS(" ",account_origin.account,account_origin.description))  as account_origin,
								IFNULL(project_origin_reviewed.proyectName, project_origin.proyectName) as project_origin,
								IFNULL(enterprise_destiny_reviewed.name, enterprise_destiny.name) as enterprise_destiny,
								IF(account_destiny_reviewed.account IS NOT NULL, CONCAT_WS(" ",account_destiny_reviewed.account,account_destiny_reviewed.description), CONCAT_WS(" ",account_destiny.account,account_destiny.description))  as account_destiny,
								groups_details.quantity as quantity,
								groups_details.unit as unit,
								groups_details.description as description,
								groups_details.unitPrice as unitPrice,
								groups_details.tax as tax,
								groups_taxes.taxes_amount as taxes_amount,
								groups_retentions.retention_amount as retention_amount,
								groups_details.amount as amount_detail,
								groups.reference as reference,
								groups.typeCurrency as type_currency,
								DATE_FORMAT(groups.paymentDate, "%d-%m-%Y") as payment_date,
								payment_methods.method as payment_method,
								groups.statusBill as status_bill,
								groups.amount as amount,
								groups.commission as commission,
								groups.amountRetake as amount_retake
							')
							->leftJoin('groups','groups.idFolio','request_models.folio')
							->leftJoin('status_requests','request_models.status','status_requests.idrequestStatus')
							->leftJoin('users as request_user','request_models.idRequest','request_user.id')
							->leftJoin('users as elaborate_user','request_models.idElaborate','elaborate_user.id')
							->leftJoin('enterprises as enterprise_origin','groups.idEnterpriseOrigin','enterprise_origin.id')
							->leftJoin('areas as area_origin','groups.idAreaOrigin','area_origin.id')
							->leftJoin('departments as department_origin','groups.idDepartamentOrigin','department_origin.id')
							->leftJoin('accounts as account_origin','groups.idAccAccOrigin','account_origin.idAccAcc')
							->leftJoin('projects as project_origin','groups.idProjectOrigin','project_origin.idproyect')
							->leftJoin('enterprises as enterprise_destiny','groups.idEnterpriseDestiny','enterprise_destiny.id')
							->leftJoin('accounts as account_destiny','groups.idAccAccDestiny','account_destiny.idAccAcc')
							->leftJoin('enterprises as enterprise_origin_reviewed','groups.idEnterpriseOriginR','enterprise_origin_reviewed.id')
							->leftJoin('areas as area_origin_reviewed','groups.idAreaOriginR','area_origin_reviewed.id')
							->leftJoin('departments as department_origin_reviewed','groups.idDepartamentOriginR','department_origin_reviewed.id')
							->leftJoin('accounts as account_origin_reviewed','groups.idAccAccOriginR','account_origin_reviewed.idAccAcc')
							->leftJoin('projects as project_origin_reviewed','groups.idProjectOriginR','project_origin_reviewed.idProyect')
							->leftJoin('enterprises as enterprise_destiny_reviewed','groups.idEnterpriseDestinyR','enterprise_destiny_reviewed.id')
							->leftJoin('accounts as account_destiny_reviewed','groups.idAccAccDestinyR','account_destiny_reviewed.idAccAcc')
							->leftJoin('payment_methods','payment_methods.idpaymentMethod','groups.idpaymentMethod')
							->leftJoin('groups_details','groups_details.idgroups','groups.idgroups')
							->leftJoin(DB::raw('(SELECT idgroupsDetail, SUM(amount) as taxes_amount FROM groups_taxes GROUP BY idgroupsDetail) AS groups_taxes'),'groups_details.idgroupsDetail','groups_taxes.idgroupsDetail')
							->leftJoin(DB::raw('(SELECT idgroupsDetail, SUM(amount) as retention_amount FROM groups_retentions GROUP BY idgroupsDetail) AS groups_retentions'),'groups_details.idgroupsDetail','groups_retentions.idgroupsDetail')
							->whereIn('request_models.status',[4])
							->where('request_models.kind',14)
							->where(function ($query) use ($name, $mindate, $maxdate, $folio,$kind)
							{
								if($name != "")
								{
									$query->where(function($q) use($name)
									{
										$q->where(DB::raw("CONCAT_WS(' ',request_user.name,request_user.last_name,request_user.scnd_last_name)"),'LIKE','%'.$name.'%')
											->orWhere(DB::raw("CONCAT_WS(' ',elaborate_user.name,elaborate_user.last_name,elaborate_user.scnd_last_name)"),'LIKE','%'.$name.'%');
									});
								}
								if($folio != "")
								{
									$query->where('request_models.folio',$folio);
								}
								if($mindate != "" && $maxdate != "")
								{
									$query->whereBetween('request_models.reviewDate',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 23:59:59')]);
								}
								if ($kind != "") 
								{
									$query->where('request_models.kind',$kind);
								}
							})
							->orderBy('request_models.fDate','DESC')
							->orderBy('request_models.folio','DESC')
							->get();

			$requestsMovements = DB::table('request_models')
							->selectRaw('
								request_models.folio as folio,
								status_requests.description as status,
								movements_enterprises.title as title,
								DATE_FORMAT(movements_enterprises.datetitle, "%d-%m-%Y") as datetitle,
								CONCAT_WS(" ",request_user.name,request_user.last_name,request_user.scnd_last_name) as request_user,
								CONCAT_WS(" ",elaborate_user.name,elaborate_user.last_name,elaborate_user.scnd_last_name) as elaborate_user,
								DATE_FORMAT(request_models.fDate, "%d-%m-%Y %H:%i") as date,
								IF(request_models.taxPayment = 1,"Sí","No") as tax_payment,
								IFNULL(enterprise_origin_reviewed.name, enterprise_origin.name) as enterprise_origin,
								IF(account_origin_reviewed.account IS NOT NULL, CONCAT_WS(" ",account_origin_reviewed.account,account_origin_reviewed.description), CONCAT_WS(" ",account_origin.account,account_origin.description))  as account_origin,
								IFNULL(enterprise_destiny_reviewed.name, enterprise_destiny.name) as enterprise_destiny,
								IF(account_destiny_reviewed.account IS NOT NULL, CONCAT_WS(" ",account_destiny_reviewed.account,account_destiny_reviewed.description), CONCAT_WS(" ",account_destiny.account,account_destiny.description))  as account_destiny,
								movements_enterprises.typeCurrency as currency,
								DATE_FORMAT(movements_enterprises.paymentDate, "%d-%m-%Y") as payment_date,
								payment_methods.method as payment_method,
								movements_enterprises.amount as amount
							')
							->leftJoin('movements_enterprises','movements_enterprises.idFolio','request_models.folio')
							->leftJoin('status_requests','request_models.status','status_requests.idrequestStatus')
							->leftJoin('users as request_user','request_models.idRequest','request_user.id')
							->leftJoin('users as elaborate_user','request_models.idElaborate','elaborate_user.id')
							->leftJoin('enterprises as enterprise_origin','movements_enterprises.idEnterpriseOrigin','enterprise_origin.id')
							->leftJoin('accounts as account_origin','movements_enterprises.idAccAccOrigin','account_origin.idAccAcc')
							->leftJoin('enterprises as enterprise_destiny','movements_enterprises.idEnterpriseDestiny','enterprise_destiny.id')
							->leftJoin('accounts as account_destiny','movements_enterprises.idAccAccDestiny','account_destiny.idAccAcc')
							->leftJoin('enterprises as enterprise_origin_reviewed','movements_enterprises.idEnterpriseOriginR','enterprise_origin_reviewed.id')
							->leftJoin('accounts as account_origin_reviewed','movements_enterprises.idAccAccOriginR','account_origin_reviewed.idAccAcc')
							->leftJoin('enterprises as enterprise_destiny_reviewed','movements_enterprises.idEnterpriseDestinyR','enterprise_destiny_reviewed.id')
							->leftJoin('accounts as account_destiny_reviewed','movements_enterprises.idAccAccDestinyR','account_destiny_reviewed.idAccAcc')
							->leftJoin('payment_methods','payment_methods.idpaymentMethod','movements_enterprises.idpaymentMethod')					
							->whereIn('request_models.status',[4])
							->where('request_models.kind',15)
							->where(function ($query) use ($name, $mindate, $maxdate, $folio,$kind)
							{
								if($name != "")
								{
									$query->where(function($q) use($name)
									{
										$q->where(DB::raw("CONCAT_WS(' ',request_user.name,request_user.last_name,request_user.scnd_last_name)"),'LIKE','%'.$name.'%')
											->orWhere(DB::raw("CONCAT_WS(' ',elaborate_user.name,elaborate_user.last_name,elaborate_user.scnd_last_name)"),'LIKE','%'.$name.'%');
									});
								}
								if($folio != "")
								{
									$query->where('request_models.folio',$folio);
								}
								if($mindate != "" && $maxdate != "")
								{
									$query->whereBetween('request_models.reviewDate',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 23:59:59')]);
								}
								if ($kind != "") 
								{
									$query->where('request_models.kind',$kind);
								}
							})
							->orderBy('request_models.fDate','DESC')
							->orderBy('request_models.folio','DESC')
							->get();

			$new_sheet 		= true;
			$defaultStyle	= (new StyleBuilder())->setFontName('Calibri')->setFontSize(12)->setCellAlignment(CellAlignment::LEFT)->build();
			$currencyFormat	= (new StyleBuilder())->setFormat('_("$"* #,##0.00_),_("$"* \(#,##0.00\),_("$"* "-"??_),_(@_)')->build();
			$rowDark		= (new StyleBuilder())->setBackgroundColor('F0F0F0')->build();
			$mhStyleCol1	= (new StyleBuilder())->setBackgroundColor('000000')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$mhStyleCol2	= (new StyleBuilder())->setBackgroundColor('1d353d')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$mhStyleCol3	= (new StyleBuilder())->setBackgroundColor('104f64')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$writer			= WriterEntityFactory::createXLSXWriter();
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('Movimientos Entre Cuentas.xlsx');

			if (count($requestsAdjustment)>0) 
			{
				$new_sheet		= false;
				$writer->getCurrentSheet()->setName('Ajuste de Movimientos');
				$headers		= ['Ajuste de Movimientos','','','','','','','','','','','','','','','','',''];
				$tempHeaders	= [];
				foreach($headers as $k => $header)
				{
					$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol1);
				}
				$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
				$writer->addRow($rowFromValues);

				$subHeaders		= ['Datos generales','','','','','','','','Cuenta de Origen','Cuenta de Destino','','','','','Condiciones de pago','','',''];
				$tempSubHeader	= [];
				foreach($subHeaders as $k => $subHeader)
				{
					$tempSubHeader[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol2);
				}
				$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
				$writer->addRow($rowFromValues);

				$subHeaders		= ['Folio','Estado','Título','Fecha','Solicitante','Elaborado por','Fecha de elaboración','Comentarios','Empresa de origen','Empresa de destino','Dirección de destino','Departamento de destino','Clasificación del gasto de destino','Proyecto de destino','Tipo de moneda','Fecha de pago','Forma de pago','Importe total a pagar'];
				$tempSubHeader	= [];
				foreach($subHeaders as $k => $subHeader)
				{
					$tempSubHeader[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol3);
				}
				$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
				$writer->addRow($rowFromValues);

				$tempFolio	= '';
				$kindRow	= true;

				foreach($requestsAdjustment as $request)
				{
					if($tempFolio != $request->folio)
					{
						$tempFolio = $request->folio;
						$kindRow = !$kindRow;
					}

					$tmpArr = [];
					foreach($request as $k => $r)
					{
						if(in_array($k,['amount']))
						{
							if($r != '')
							{
								$tmpArr[] = WriterEntityFactory::createCell((double)$r, $currencyFormat);
							}
							else
							{
								$tmpArr[] = WriterEntityFactory::createCell($r);
							}
						}
						else
						{
							$tmpArr[] = WriterEntityFactory::createCell($r);
						}
					}

					if($kindRow)
					{
						$rowFromValues = WriterEntityFactory::createRow($tmpArr,$rowDark);
					}
					else
					{
						$rowFromValues = WriterEntityFactory::createRow($tmpArr);
					}
					$writer->addRow($rowFromValues);
				}
			}

			if (count($requestsLoan)>0) 
			{
				if ($new_sheet) 
				{
					$writer->getCurrentSheet()->setName('Préstamo Inter-Empresa');
					$new_sheet = false;
				}
				else
				{
					$newSheet = $writer->addNewSheetAndMakeItCurrent();
					$writer->getCurrentSheet()->setName('Préstamo Inter-Empresa');
				}

				$headers		= ['Préstamo Inter-Empresa','','','','','','','','','','','','','','',''];
				$tempHeaders	= [];
				foreach($headers as $k => $header)
				{
					$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol1);
				}
				$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
				$writer->addRow($rowFromValues);

				$subHeaders		= ['Datos generales','','','','','','','','Cuenta de Origen','','Cuenta de Destino','','Condiciones de pago','','',''];
				$tempSubHeader	= [];
				foreach($subHeaders as $k => $subHeader)
				{
					$tempSubHeader[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol2);
				}
				$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
				$writer->addRow($rowFromValues);

				$subHeaders		= ['Folio','Estado','Título','Fecha','Solicitante','Elaborado por','Fecha de elaboración','Fiscal','Empresa de origen','Clasificación del gasto de origen','Empresa de destino','Clasificación del gasto de destino','Tipo de moneda','Fecha de pago','Forma de pago','Importe total a pagar'];
				$tempSubHeader	= [];
				foreach($subHeaders as $k => $subHeader)
				{
					$tempSubHeader[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol3);
				}
				$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
				$writer->addRow($rowFromValues);

				$tempFolio	= '';
				$kindRow	= true;

				foreach($requestsLoan as $request)
				{
					if($tempFolio != $request->folio)
					{
						$tempFolio = $request->folio;
						$kindRow = !$kindRow;
					}

					$tmpArr = [];
					foreach($request as $k => $r)
					{
						if(in_array($k,['amount']))
						{
							if($r != '')
							{
								$tmpArr[] = WriterEntityFactory::createCell((double)$r, $currencyFormat);
							}
							else
							{
								$tmpArr[] = WriterEntityFactory::createCell($r);
							}
						}
						else
						{
							$tmpArr[] = WriterEntityFactory::createCell($r);
						}
					}

					if($kindRow)
					{
						$rowFromValues = WriterEntityFactory::createRow($tmpArr,$rowDark);
					}
					else
					{
						$rowFromValues = WriterEntityFactory::createRow($tmpArr);
					}
					$writer->addRow($rowFromValues);
				}
			}

			if (count($requestsPurchase)>0) 
			{
				if ($new_sheet) 
				{
					$writer->getCurrentSheet()->setName('Compras Inter-Empresa');
					$new_sheet = false;
				}
				else
				{
					$newSheet = $writer->addNewSheetAndMakeItCurrent();
					$writer->getCurrentSheet()->setName('Compras Inter-Empresa');
				}

				$headers		= ['Compras Inter-Empresa','','','','','','','','','','','','','','','','','','','','','','','','','','','',''];
				$tempHeaders	= [];
				foreach($headers as $k => $header)
				{
					$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol1);
				}
				$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
				$writer->addRow($rowFromValues);

				$subHeaders		= ['Datos generales','','','','','','','','','Cuenta de Origen','','','','','Cuenta de Destino','','','Datos de la solicitud','','','','','','','','Condiciones de pago','','',''];
				$tempSubHeader	= [];
				foreach($subHeaders as $k => $subHeader)
				{
					$tempSubHeader[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol2);
				}
				$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
				$writer->addRow($rowFromValues);

				$subHeaders		= ['Folio','Estado','Título','Fecha','Solicitante','Elaborado por','Fecha de elaboración','Fiscal','Número de orden','Empresa de origen','Clasificación del gasto de origen','Proyecto de origen','Empresa de destino','Dirección de destino','Departamento de destino','Clasificación del gasto de destino','Proyecto de destino','Cantidad','Unidad','Descripción','Precio Unitario','IVA','Impuesto Adicional','Retenciones','Total','Tipo de moneda','Fecha de pago','Forma de pago','Importe total a pagar'];
				$tempSubHeader	= [];
				foreach($subHeaders as $k => $subHeader)
				{
					$tempSubHeader[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol3);
				}
				$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
				$writer->addRow($rowFromValues);

				$tempFolio	= '';
				$kindRow	= true;

				foreach($requestsPurchase as $request)
				{
					if($tempFolio != $request->folio)
					{
						$tempFolio = $request->folio;
						$kindRow = !$kindRow;
					}
					else
					{
						$request->folio					= null;
						$request->status				= '';
						$request->title					= '';
						$request->datetitle				= '';
						$request->request_user			= '';
						$request->elaborate_user		= '';
						$request->date					= '';
						$request->tax_payment			= '';
						$request->number_order			= '';
						$request->enterprise_origin		= '';
						$request->area_origin			= '';
						$request->department_origin		= '';
						$request->account_origin		= '';
						$request->project_origin		= '';
						$request->enterprise_destiny	= '';
						$request->account_destiny		= '';
						$request->project_destiny		= '';
						$request->currency				= '';
						$request->payment_date			= '';
						$request->payment_method		= '';
						$request->amount				= '';
					}

					$tmpArr = [];
					foreach($request as $k => $r)
					{
						if(in_array($k,['amount','unitPrice','tax','taxes_amount','retention_amount','amount_detail']))
						{
							if($r != '')
							{
								$tmpArr[] = WriterEntityFactory::createCell((double)$r, $currencyFormat);
							}
							else
							{
								$tmpArr[] = WriterEntityFactory::createCell($r);
							}
						}
						elseif(in_array($k,['quantity']))
						{
							$tmpArr[] = WriterEntityFactory::createCell((double)$r);
						}
						else
						{
							$tmpArr[] = WriterEntityFactory::createCell($r);
						}
					}

					if($kindRow)
					{
						$rowFromValues = WriterEntityFactory::createRow($tmpArr,$rowDark);
					}
					else
					{
						$rowFromValues = WriterEntityFactory::createRow($tmpArr);
					}
					$writer->addRow($rowFromValues);
				}
			}

			if (count($requestsGroups)>0) 
			{
				if ($new_sheet) 
				{
					$writer->getCurrentSheet()->setName('Grupos');
					$new_sheet = false;
				}
				else
				{
					$newSheet = $writer->addNewSheetAndMakeItCurrent();
					$writer->getCurrentSheet()->setName('Grupos');
				}

				$headers		= ['Grupos','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',''];
				$tempHeaders	= [];
				foreach($headers as $k => $header)
				{
					$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol1);
				}
				$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
				$writer->addRow($rowFromValues);

				$subHeaders		= ['Datos generales','','','','','','','','','','Cuenta de Origen','','','','','Cuenta de Destino','','Datos de la solicitud','','','','','','','','Condiciones de pago','','','','','','',''];
				$tempSubHeader	= [];
				foreach($subHeaders as $k => $subHeader)
				{
					$tempSubHeader[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol2);
				}
				$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
				$writer->addRow($rowFromValues);

				$subHeaders		= ['Folio','Estado','Título','Fecha','Solicitante','Elaborado por','Fecha de elaboración','Fiscal','Número de orden','Tipo de operación','Empresa de origen','Dirección de origen','Departamento de origen','Clasificación del gasto de origen','Proyecto de origen','Empresa de destino','Clasificación del gasto de destino','Cantidad','Unidad','Descripción','Precio Unitario','IVA','Impuesto Adicional','Retenciones','Total','Referencia/Número de Factura','Tipo de moneda','Fecha de pago','Forma de pago','Estado  de factura','Importe total a pagar','Comisión','Importe a retomar'];
				$tempSubHeader	= [];
				foreach($subHeaders as $k => $subHeader)
				{
					$tempSubHeader[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol3);
				}
				$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
				$writer->addRow($rowFromValues);

				$tempFolio	= '';
				$kindRow	= true;

				foreach($requestsGroups as $request)
				{
					if($tempFolio != $request->folio)
					{
						$tempFolio = $request->folio;
						$kindRow = !$kindRow;
					}
					else
					{
						$request->folio					= null;
						$request->status				= '';
						$request->title					= '';
						$request->datetitle				= '';
						$request->request_user			= '';
						$request->elaborate_user		= '';
						$request->date					= '';
						$request->tax_payment			= '';
						$request->number_order			= '';
						$request->operation_type		= '';
						$request->enterprise_origin		= '';
						$request->area_origin			= '';
						$request->department_origin		= '';
						$request->account_origin		= '';
						$request->project_origin		= '';
						$request->enterprise_destiny	= '';
						$request->account_destiny		= '';
						$request->reference				= '';
						$request->type_currency			= '';
						$request->payment_date			= '';
						$request->payment_method		= '';
						$request->status_bill			= '';
						$request->amount				= '';
						$request->commission			= '';
						$request->amount_retake			= '';
					}

					$tmpArr = [];
					foreach($request as $k => $r)
					{
						if(in_array($k,['unitPrice','tax','taxes_amount','retention_amount','amount_detail','amount','commission','amount_retake']))
						{
							if($r != '')
							{
								$tmpArr[] = WriterEntityFactory::createCell((double)$r, $currencyFormat);
							}
							else
							{
								$tmpArr[] = WriterEntityFactory::createCell($r);
							}
						}
						elseif(in_array($k,['quantity']))
						{
							$tmpArr[] = WriterEntityFactory::createCell((double)$r);
						}
						else
						{
							$tmpArr[] = WriterEntityFactory::createCell($r);
						}
					}

					if($kindRow)
					{
						$rowFromValues = WriterEntityFactory::createRow($tmpArr,$rowDark);
					}
					else
					{
						$rowFromValues = WriterEntityFactory::createRow($tmpArr);
					}
					$writer->addRow($rowFromValues);
				}
			}

			if (count($requestsMovements)>0) 
			{
				if ($new_sheet) 
				{
					$writer->getCurrentSheet()->setName('Movimientos Misma Empresa');
					$new_sheet = false;
				}
				else
				{
					$newSheet = $writer->addNewSheetAndMakeItCurrent();
					$writer->getCurrentSheet()->setName('Movimientos Misma Empresa');
				}

				$new_sheet		= false;
				$headers		= ['Movimientos Misma Empresa','','','','','','','','','','','','','','',''];
				$tempHeaders	= [];
				foreach($headers as $k => $header)
				{
					$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol1);
				}
				$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
				$writer->addRow($rowFromValues);

				$subHeaders		= ['Datos generales','','','','','','','','Cuenta de Origen','','Cuenta de Destino','','Condiciones de pago','','',''];
				$tempSubHeader	= [];
				foreach($subHeaders as $k => $subHeader)
				{
					$tempSubHeader[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol2);
				}
				$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
				$writer->addRow($rowFromValues);

				$subHeaders		= ['Folio','Estado','Título','Fecha','Solicitante','Elaborado por','Fecha de elaboración','Fiscal','Empresa de origen','Clasificación del gasto de origen','Empresa de destino','Clasificación del gasto de destino','Tipo de moneda','Fecha de pago','Forma de pago','Importe total a pagar'];
				$tempSubHeader	= [];
				foreach($subHeaders as $k => $subHeader)
				{
					$tempSubHeader[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol3);
				}
				$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
				$writer->addRow($rowFromValues);

				$tempFolio	= '';
				$kindRow	= true;

				foreach($requestsMovements as $request)
				{
					if($tempFolio != $request->folio)
					{
						$tempFolio = $request->folio;
						$kindRow = !$kindRow;
					}

					$tmpArr = [];
					foreach($request as $k => $r)
					{
						if(in_array($k,['amount']))
						{
							if($r != '')
							{
								$tmpArr[] = WriterEntityFactory::createCell((double)$r, $currencyFormat);
							}
							else
							{
								$tmpArr[] = WriterEntityFactory::createCell($r);
							}
						}
						else
						{
							$tmpArr[] = WriterEntityFactory::createCell($r);
						}
					}

					if($kindRow)
					{
						$rowFromValues = WriterEntityFactory::createRow($tmpArr,$rowDark);
					}
					else
					{
						$rowFromValues = WriterEntityFactory::createRow($tmpArr);
					}
					$writer->addRow($rowFromValues);
				}
			}	
			
			return $writer->close();
		}
		else
		{
			return redirect('/');
		}
	}

	public function billingExcel(Request $request)
	{
		if(Auth::user()->module->where('id',152)->count()>0)
		{
			$data       = App\Module::find($this->module_id);
			$name       = $request->name;
			$folio      = $request->folio;
			$mindate    = $request->mindate!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate) : null;
			$maxdate    = $request->maxdate!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate) : null;
			$kind       = $request->kind;
			
			$requestsPurchase 	= DB::table('request_models')
							->selectRaw('
								request_models.folio as folio,
								status_requests.description as status,
								purchase_enterprises.title as title,
								DATE_FORMAT(purchase_enterprises.datetitle, "%d-%m-%Y") as datetitle,
								CONCAT_WS(" ",request_user.name,request_user.last_name,request_user.scnd_last_name) as request_user,
								CONCAT_WS(" ",elaborate_user.name,elaborate_user.last_name,elaborate_user.scnd_last_name) as elaborate_user,
								DATE_FORMAT(request_models.fDate, "%d-%m-%Y %H:%i") as date,
								IF(request_models.taxPayment = 1,"Sí","No") as tax_payment,
								purchase_enterprises.numberOrder as number_order,
								IFNULL(enterprise_origin_reviewed.name, enterprise_origin.name) as enterprise_origin,
								IFNULL(area_origin_reviewed.name, area_origin.name) as area_origin,
								IFNULL(department_origin_reviewed.name, department_origin.name) as department_origin,
								IF(account_origin_reviewed.account IS NOT NULL, CONCAT_WS(" ",account_origin_reviewed.account,account_origin_reviewed.description), CONCAT_WS(" ",account_origin.account,account_origin.description))  as account_origin,
								IFNULL(project_origin_reviewed.proyectName, project_origin.proyectName) as project_origin,
								IFNULL(enterprise_destiny_reviewed.name, enterprise_destiny.name) as enterprise_destiny,
								IF(account_destiny_reviewed.account IS NOT NULL, CONCAT_WS(" ",account_destiny_reviewed.account,account_destiny_reviewed.description), CONCAT_WS(" ",account_destiny.account,account_destiny.description))  as account_destiny,
								IFNULL(project_destiny_reviewed.proyectName, project_destiny.proyectName) as project_destiny,
								purchase_enterprise_details.quantity as quantity,
								purchase_enterprise_details.unit as unit,
								purchase_enterprise_details.description as description,
								purchase_enterprise_details.unitPrice as unitPrice,
								purchase_enterprise_details.tax as tax,
								pe_taxes.taxes_amount as taxes_amount,
								pe_retention.retention_amount as retention_amount,
								purchase_enterprise_details.amount as amount_detail,
								purchase_enterprises.typeCurrency as currency,
								DATE_FORMAT(purchase_enterprises.paymentDate, "%d-%m-%Y") as payment_date,
								payment_methods.method as payment_method,
								purchase_enterprises.amount as amount
							')
							->leftJoin('purchase_enterprises','purchase_enterprises.idFolio','request_models.folio')
							->leftJoin('status_requests','request_models.status','status_requests.idrequestStatus')
							->leftJoin('users as request_user','request_models.idRequest','request_user.id')
							->leftJoin('users as elaborate_user','request_models.idElaborate','elaborate_user.id')
							->leftJoin('enterprises as enterprise_origin','purchase_enterprises.idEnterpriseOrigin','enterprise_origin.id')
							->leftJoin('areas as area_origin','purchase_enterprises.idAreaOrigin','area_origin.id')
							->leftJoin('departments as department_origin','purchase_enterprises.idDepartamentOrigin','department_origin.id')
							->leftJoin('accounts as account_origin','purchase_enterprises.idAccAccOrigin','account_origin.idAccAcc')
							->leftJoin('projects as project_origin','purchase_enterprises.idProjectOrigin','project_origin.idProyect')
							->leftJoin('enterprises as enterprise_destiny','purchase_enterprises.idEnterpriseDestiny','enterprise_destiny.id')
							->leftJoin('accounts as account_destiny','purchase_enterprises.idAccAccDestiny','account_destiny.idAccAcc')
							->leftJoin('projects as project_destiny','purchase_enterprises.idProjectDestiny','project_destiny.idproyect')
							->leftJoin('enterprises as enterprise_origin_reviewed','purchase_enterprises.idEnterpriseOriginR','enterprise_origin_reviewed.id')
							->leftJoin('areas as area_origin_reviewed','purchase_enterprises.idAreaOriginR','area_origin_reviewed.id')
							->leftJoin('departments as department_origin_reviewed','purchase_enterprises.idDepartamentOriginR','department_origin_reviewed.id')
							->leftJoin('accounts as account_origin_reviewed','purchase_enterprises.idAccAccOriginR','account_origin_reviewed.idAccAcc')
							->leftJoin('projects as project_origin_reviewed','purchase_enterprises.idProjectOriginR','project_origin_reviewed.idProyect')
							->leftJoin('enterprises as enterprise_destiny_reviewed','purchase_enterprises.idEnterpriseDestinyR','enterprise_destiny_reviewed.id')
							->leftJoin('accounts as account_destiny_reviewed','purchase_enterprises.idAccAccDestinyR','account_destiny_reviewed.idAccAcc')
							->leftJoin('projects as project_destiny_reviewed','purchase_enterprises.idProjectDestinyR','project_destiny_reviewed.idproyect')
							->leftJoin('payment_methods','payment_methods.idpaymentMethod','purchase_enterprises.idpaymentMethod')
							->leftJoin('purchase_enterprise_details','purchase_enterprise_details.idpurchaseEnterprise','purchase_enterprises.idpurchaseEnterprise')
							->leftJoin(DB::raw('(SELECT idPurchaseEnterpriseDetail, SUM(amount) as taxes_amount FROM purchase_enterprise_taxes GROUP BY idPurchaseEnterpriseDetail) AS pe_taxes'),'purchase_enterprise_details.idPurchaseEnterpriseDetail','pe_taxes.idPurchaseEnterpriseDetail')
							->leftJoin(DB::raw('(SELECT idPurchaseEnterpriseDetail, SUM(amount) as retention_amount FROM purchase_enterprise_retentions GROUP BY idPurchaseEnterpriseDetail) AS pe_retention'),'purchase_enterprise_details.idPurchaseEnterpriseDetail','pe_retention.idPurchaseEnterpriseDetail')
							->whereIn('request_models.status',[5,10,11,12,18])
							->where('request_models.kind',13)
							->where(function ($query) use ($name, $mindate, $maxdate, $folio,$kind)
								{
									if($name != "")
									{
										$query->where(function($q) use($name)
										{
											$q->where(DB::raw("CONCAT_WS(' ',request_user.name,request_user.last_name,request_user.scnd_last_name)"),'LIKE','%'.$name.'%')
												->orWhere(DB::raw("CONCAT_WS(' ',elaborate_user.name,elaborate_user.last_name,elaborate_user.scnd_last_name)"),'LIKE','%'.$name.'%');
										});
									}
									if($folio != "")
									{
										$query->where('request_models.folio',$folio);
									}
									if($mindate != "" && $maxdate != "")
									{
										$query->whereBetween('request_models.reviewDate',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 23:59:59')]);
									}
									if ($kind != "") 
									{
										$query->where('request_models.kind',$kind);
									}
								})
							->orderBy('request_models.fDate','DESC')
							->orderBy('request_models.folio','DESC')
							->get();
			
			$requestsGroups 	= DB::table('request_models')
							->selectRaw('
								request_models.folio as folio,
								status_requests.description as status,
								groups.title as title,
								DATE_FORMAT(groups.datetitle, "%d-%m-%Y") as datetitle,
								CONCAT_WS(" ",request_user.name,request_user.last_name,request_user.scnd_last_name) as request_user,
								CONCAT_WS(" ",elaborate_user.name,elaborate_user.last_name,elaborate_user.scnd_last_name) as elaborate_user,
								DATE_FORMAT(request_models.fDate, "%d-%m-%Y %H:%i") as date,
								IF(request_models.taxPayment = 1,"Sí","No") as tax_payment,
								groups.numberOrder as number_order,
								groups.operationType as operation_type,
								IFNULL(enterprise_origin_reviewed.name, enterprise_origin.name) as enterprise_origin,
								IFNULL(area_origin_reviewed.name, area_origin.name) as area_origin,
								IFNULL(department_origin_reviewed.name, department_origin.name) as department_origin,
								IF(account_origin_reviewed.account IS NOT NULL, CONCAT_WS(" ",account_origin_reviewed.account,account_origin_reviewed.description), CONCAT_WS(" ",account_origin.account,account_origin.description))  as account_origin,
								IFNULL(project_origin_reviewed.proyectName, project_origin.proyectName) as project_origin,
								IFNULL(enterprise_destiny_reviewed.name, enterprise_destiny.name) as enterprise_destiny,
								IF(account_destiny_reviewed.account IS NOT NULL, CONCAT_WS(" ",account_destiny_reviewed.account,account_destiny_reviewed.description), CONCAT_WS(" ",account_destiny.account,account_destiny.description))  as account_destiny,
								groups_details.quantity as quantity,
								groups_details.unit as unit,
								groups_details.description as description,
								groups_details.unitPrice as unitPrice,
								groups_details.tax as tax,
								groups_taxes.taxes_amount as taxes_amount,
								groups_retentions.retention_amount as retention_amount,
								groups_details.amount as amount_detail,
								groups.reference as reference,
								groups.typeCurrency as type_currency,
								DATE_FORMAT(groups.paymentDate, "%d-%m-%Y") as payment_date,
								payment_methods.method as payment_method,
								groups.statusBill as status_bill,
								groups.amount as amount,
								groups.commission as commission,
								groups.amountRetake as amount_retake
							')
							->leftJoin('groups','groups.idFolio','request_models.folio')
							->leftJoin('status_requests','request_models.status','status_requests.idrequestStatus')
							->leftJoin('users as request_user','request_models.idRequest','request_user.id')
							->leftJoin('users as elaborate_user','request_models.idElaborate','elaborate_user.id')
							->leftJoin('enterprises as enterprise_origin','groups.idEnterpriseOrigin','enterprise_origin.id')
							->leftJoin('areas as area_origin','groups.idAreaOrigin','area_origin.id')
							->leftJoin('departments as department_origin','groups.idDepartamentOrigin','department_origin.id')
							->leftJoin('accounts as account_origin','groups.idAccAccOrigin','account_origin.idAccAcc')
							->leftJoin('projects as project_origin','groups.idProjectOrigin','project_origin.idproyect')
							->leftJoin('enterprises as enterprise_destiny','groups.idEnterpriseDestiny','enterprise_destiny.id')
							->leftJoin('accounts as account_destiny','groups.idAccAccDestiny','account_destiny.idAccAcc')
							->leftJoin('enterprises as enterprise_origin_reviewed','groups.idEnterpriseOriginR','enterprise_origin_reviewed.id')
							->leftJoin('areas as area_origin_reviewed','groups.idAreaOriginR','area_origin_reviewed.id')
							->leftJoin('departments as department_origin_reviewed','groups.idDepartamentOriginR','department_origin_reviewed.id')
							->leftJoin('accounts as account_origin_reviewed','groups.idAccAccOriginR','account_origin_reviewed.idAccAcc')
							->leftJoin('projects as project_origin_reviewed','groups.idProjectOriginR','project_origin_reviewed.idProyect')
							->leftJoin('enterprises as enterprise_destiny_reviewed','groups.idEnterpriseDestinyR','enterprise_destiny_reviewed.id')
							->leftJoin('accounts as account_destiny_reviewed','groups.idAccAccDestinyR','account_destiny_reviewed.idAccAcc')
							->leftJoin('payment_methods','payment_methods.idpaymentMethod','groups.idpaymentMethod')
							->leftJoin('groups_details','groups_details.idgroups','groups.idgroups')
							->leftJoin(DB::raw('(SELECT idgroupsDetail, SUM(amount) as taxes_amount FROM groups_taxes GROUP BY idgroupsDetail) AS groups_taxes'),'groups_details.idgroupsDetail','groups_taxes.idgroupsDetail')
							->leftJoin(DB::raw('(SELECT idgroupsDetail, SUM(amount) as retention_amount FROM groups_retentions GROUP BY idgroupsDetail) AS groups_retentions'),'groups_details.idgroupsDetail','groups_retentions.idgroupsDetail')
							->where('groups.operationType','Entrada')
							->whereIn('request_models.status',[5,10,11,12,18])
							->where('request_models.kind',14)
							->where(function ($query) use ($name, $mindate, $maxdate, $folio,$kind)
							{
								if($name != "")
								{
									$query->where(function($q) use($name)
									{
										$q->where(DB::raw("CONCAT_WS(' ',request_user.name,request_user.last_name,request_user.scnd_last_name)"),'LIKE','%'.$name.'%')
											->orWhere(DB::raw("CONCAT_WS(' ',elaborate_user.name,elaborate_user.last_name,elaborate_user.scnd_last_name)"),'LIKE','%'.$name.'%');
									});
								}
								if($folio != "")
								{
									$query->where('request_models.folio',$folio);
								}
								if($mindate != "" && $maxdate != "")
								{
									$query->whereBetween('request_models.reviewDate',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 23:59:59')]);
								}
								if ($kind != "") 
								{
									$query->where('request_models.kind',$kind);
								}
							})
							->orderBy('request_models.fDate','DESC')
							->orderBy('request_models.folio','DESC')
							->get();

			$requestsAdjustment = DB::table('request_models')
							->selectRaw('
								request_models.folio as folio,
								status_requests.description as status,
								adjustments.title as title,
								DATE_FORMAT(adjustments.datetitle, "%d-%m-%Y") as datetitle,
								CONCAT_WS(" ",request_user.name,request_user.last_name,request_user.scnd_last_name) as request_user,
								CONCAT_WS(" ",elaborate_user.name,elaborate_user.last_name,elaborate_user.scnd_last_name) as elaborate_user,
								DATE_FORMAT(request_models.fDate, "%d-%m-%Y %H:%i") as date,
								adjustments.commentaries as commentaries,
								IFNULL(enterprise_origin_reviewed.name, enterprise_origin.name) as enterprise_origin,
								IFNULL(enterprise_destiny_reviewed.name, enterprise_destiny.name) as enterprise_destiny,
								IFNULL(direction_destiny_reviewed.name, direction_destiny.name) as direction_destiny,
								IFNULL(department_destiny_reviewed.name, department_destiny.name) as department_destiny,
								IF(account_destiny_reviewed.account IS NOT NULL,CONCAT_WS(" - ",account_destiny_reviewed.account,account_destiny_reviewed.description), CONCAT_WS(" - ",account_destiny.account,account_destiny.description)) as account_destiny,
								IFNULL(project_destiny_reviewed.proyectName, project_destiny.proyectName) as project_destiny,
								adjustments.currency as currency,
								DATE_FORMAT(adjustments.paymentDate, "%d-%m-%Y") as payment_date,
								payment_methods.method as payment_method,
								adjustments.amount as amount
							')
							->leftJoin('adjustments','adjustments.idFolio','request_models.folio')
							->leftJoin('status_requests','request_models.status','status_requests.idrequestStatus')
							->leftJoin('users as request_user','request_models.idRequest','request_user.id')
							->leftJoin('users as elaborate_user','request_models.idElaborate','elaborate_user.id')
							->leftJoin('enterprises as enterprise_origin','adjustments.idEnterpriseOrigin','enterprise_origin.id')
							->leftJoin('enterprises as enterprise_destiny','adjustments.idEnterpriseDestiny','enterprise_destiny.id')
							->leftJoin('areas as direction_destiny','adjustments.idAreaDestiny','direction_destiny.id')
							->leftJoin('departments as department_destiny','adjustments.idDepartamentDestiny','department_destiny.id')
							->leftJoin('accounts as account_destiny','adjustments.idAccAccDestiny','account_destiny.idAccAcc')
							->leftJoin('projects as project_destiny','adjustments.idProjectDestiny','project_destiny.idproyect')
							->leftJoin('enterprises as enterprise_origin_reviewed','adjustments.idEnterpriseOriginR','enterprise_origin_reviewed.id')
							->leftJoin('enterprises as enterprise_destiny_reviewed','adjustments.idEnterpriseDestinyR','enterprise_destiny_reviewed.id')
							->leftJoin('areas as direction_destiny_reviewed','adjustments.idAreaDestinyR','direction_destiny_reviewed.id')
							->leftJoin('departments as department_destiny_reviewed','adjustments.idDepartamentDestinyR','department_destiny_reviewed.id')
							->leftJoin('accounts as account_destiny_reviewed','adjustments.idAccAccDestinyR','account_destiny_reviewed.idAccAcc')
							->leftJoin('projects as project_destiny_reviewed','adjustments.idProjectDestinyR','project_destiny_reviewed.idproyect')
							->leftJoin('payment_methods','payment_methods.idpaymentMethod','adjustments.idpaymentMethod')
							->whereIn('request_models.status',[5,10,11,12,18])
							->where('request_models.kind',11)
							->where(function ($query) use ($name, $mindate, $maxdate, $folio,$kind)
							{
								if($name != "")
								{
									$query->where(function($q) use($name)
									{
										$q->where(DB::raw("CONCAT_WS(' ',request_user.name,request_user.last_name,request_user.scnd_last_name)"),'LIKE','%'.$name.'%')
											->orWhere(DB::raw("CONCAT_WS(' ',elaborate_user.name,elaborate_user.last_name,elaborate_user.scnd_last_name)"),'LIKE','%'.$name.'%');
									});
								}
								if($folio != "")
								{
									$query->where('request_models.folio',$folio);
								}
								if($mindate != "" && $maxdate != "")
								{
									$query->whereBetween('request_models.reviewDate',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 23:59:59')]);
								}
								if ($kind != "") 
								{
									$query->where('request_models.kind',$kind);
								}
							})
							->orderBy('request_models.fDate','DESC')
							->orderBy('request_models.folio','DESC')
							->get();						

			$new_sheet 		= true;
			$defaultStyle	= (new StyleBuilder())->setFontName('Calibri')->setFontSize(12)->setCellAlignment(CellAlignment::LEFT)->build();
			$currencyFormat	= (new StyleBuilder())->setFormat('_("$"* #,##0.00_),_("$"* \(#,##0.00\),_("$"* "-"??_),_(@_)')->build();
			$rowDark		= (new StyleBuilder())->setBackgroundColor('F0F0F0')->build();
			$mhStyleCol1	= (new StyleBuilder())->setBackgroundColor('000000')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$mhStyleCol2	= (new StyleBuilder())->setBackgroundColor('1d353d')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$mhStyleCol3	= (new StyleBuilder())->setBackgroundColor('104f64')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$writer			= WriterEntityFactory::createXLSXWriter();
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('Movimientos Entre Cuentas.xlsx');

			if (count($requestsPurchase)>0) 
			{
				if ($new_sheet) 
				{
					$writer->getCurrentSheet()->setName('Compras Inter-Empresa');
					$new_sheet = false;
				}
				else
				{
					$newSheet = $writer->addNewSheetAndMakeItCurrent();
					$writer->getCurrentSheet()->setName('Compras Inter-Empresa');
				}

				$headers		= ['Compras Inter-Empresa','','','','','','','','','','','','','','','','','','','','','','','','','','','',''];
				$tempHeaders	= [];
				foreach($headers as $k => $header)
				{
					$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol1);
				}
				$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
				$writer->addRow($rowFromValues);

				$subHeaders		= ['Datos generales','','','','','','','','','Cuenta de Origen','','','','','Cuenta de Destino','','','Datos de la solicitud','','','','','','','','Condiciones de pago','','',''];
				$tempSubHeader	= [];
				foreach($subHeaders as $k => $subHeader)
				{
					$tempSubHeader[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol2);
				}
				$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
				$writer->addRow($rowFromValues);

				$subHeaders		= ['Folio','Estado','Título','Fecha','Solicitante','Elaborado por','Fecha de elaboración','Fiscal','Número de orden','Empresa de origen','Clasificación del gasto de origen','Proyecto de origen','Empresa de destino','Dirección de destino','Departamento de destino','Clasificación del gasto de destino','Proyecto de destino','Cantidad','Unidad','Descripción','Precio Unitario','IVA','Impuesto Adicional','Retenciones','Total','Tipo de moneda','Fecha de pago','Forma de pago','Importe total a pagar'];
				$tempSubHeader	= [];
				foreach($subHeaders as $k => $subHeader)
				{
					$tempSubHeader[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol3);
				}
				$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
				$writer->addRow($rowFromValues);

				$tempFolio	= '';
				$kindRow	= true;

				foreach($requestsPurchase as $request)
				{
					if($tempFolio != $request->folio)
					{
						$tempFolio = $request->folio;
						$kindRow = !$kindRow;
					}
					else
					{
						$request->folio					= null;
						$request->status				= '';
						$request->title					= '';
						$request->datetitle				= '';
						$request->request_user			= '';
						$request->elaborate_user		= '';
						$request->date					= '';
						$request->tax_payment			= '';
						$request->number_order			= '';
						$request->enterprise_origin		= '';
						$request->area_origin			= '';
						$request->department_origin		= '';
						$request->account_origin		= '';
						$request->project_origin		= '';
						$request->enterprise_destiny	= '';
						$request->account_destiny		= '';
						$request->project_destiny		= '';
						$request->currency				= '';
						$request->payment_date			= '';
						$request->payment_method		= '';
						$request->amount				= '';
					}

					$tmpArr = [];
					foreach($request as $k => $r)
					{
						if(in_array($k,['amount','unitPrice','tax','taxes_amount','retention_amount','amount_detail']))
						{
							if($r != '')
							{
								$tmpArr[] = WriterEntityFactory::createCell((double)$r, $currencyFormat);
							}
							else
							{
								$tmpArr[] = WriterEntityFactory::createCell($r);
							}
						}
						elseif(in_array($k,['quantity']))
						{
							$tmpArr[] = WriterEntityFactory::createCell((double)$r);
						}
						else
						{
							$tmpArr[] = WriterEntityFactory::createCell($r);
						}
					}

					if($kindRow)
					{
						$rowFromValues = WriterEntityFactory::createRow($tmpArr,$rowDark);
					}
					else
					{
						$rowFromValues = WriterEntityFactory::createRow($tmpArr);
					}
					$writer->addRow($rowFromValues);
				}
			}

			if (count($requestsGroups)>0) 
			{
				if ($new_sheet) 
				{
					$writer->getCurrentSheet()->setName('Grupos');
					$new_sheet = false;
				}
				else
				{
					$newSheet = $writer->addNewSheetAndMakeItCurrent();
					$writer->getCurrentSheet()->setName('Grupos');
				}

				$headers		= ['Grupos','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',''];
				$tempHeaders	= [];
				foreach($headers as $k => $header)
				{
					$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol1);
				}
				$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
				$writer->addRow($rowFromValues);

				$subHeaders		= ['Datos generales','','','','','','','','','','Cuenta de Origen','','','','','Cuenta de Destino','','Datos de la solicitud','','','','','','','','Condiciones de pago','','','','','','',''];
				$tempSubHeader	= [];
				foreach($subHeaders as $k => $subHeader)
				{
					$tempSubHeader[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol2);
				}
				$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
				$writer->addRow($rowFromValues);

				$subHeaders		= ['Folio','Estado','Título','Fecha','Solicitante','Elaborado por','Fecha de elaboración','Fiscal','Número de orden','Tipo de operación','Empresa de origen','Dirección de origen','Departamento de origen','Clasificación del gasto de origen','Proyecto de origen','Empresa de destino','Clasificación del gasto de destino','Cantidad','Unidad','Descripción','Precio Unitario','IVA','Impuesto Adicional','Retenciones','Total','Referencia/Número de Factura','Tipo de moneda','Fecha de pago','Forma de pago','Estado  de factura','Importe total a pagar','Comisión','Importe a retomar'];
				$tempSubHeader	= [];
				foreach($subHeaders as $k => $subHeader)
				{
					$tempSubHeader[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol3);
				}
				$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
				$writer->addRow($rowFromValues);

				$tempFolio	= '';
				$kindRow	= true;

				foreach($requestsGroups as $request)
				{
					if($tempFolio != $request->folio)
					{
						$tempFolio = $request->folio;
						$kindRow = !$kindRow;
					}
					else
					{
						$request->folio					= null;
						$request->status				= '';
						$request->title					= '';
						$request->datetitle				= '';
						$request->request_user			= '';
						$request->elaborate_user		= '';
						$request->date					= '';
						$request->tax_payment			= '';
						$request->number_order			= '';
						$request->operation_type		= '';
						$request->enterprise_origin		= '';
						$request->area_origin			= '';
						$request->department_origin		= '';
						$request->account_origin		= '';
						$request->project_origin		= '';
						$request->enterprise_destiny	= '';
						$request->account_destiny		= '';
						$request->reference				= '';
						$request->type_currency			= '';
						$request->payment_date			= '';
						$request->payment_method		= '';
						$request->status_bill			= '';
						$request->amount				= '';
						$request->commission			= '';
						$request->amount_retake			= '';
					}

					$tmpArr = [];
					foreach($request as $k => $r)
					{
						if(in_array($k,['unitPrice','tax','taxes_amount','retention_amount','amount_detail','amount','commission','amount_retake']))
						{
							if($r != '')
							{
								$tmpArr[] = WriterEntityFactory::createCell((double)$r, $currencyFormat);
							}
							else
							{
								$tmpArr[] = WriterEntityFactory::createCell($r);
							}
						}
						elseif(in_array($k,['quantity']))
						{
							$tmpArr[] = WriterEntityFactory::createCell((double)$r);
						}
						else
						{
							$tmpArr[] = WriterEntityFactory::createCell($r);
						}
					}

					if($kindRow)
					{
						$rowFromValues = WriterEntityFactory::createRow($tmpArr,$rowDark);
					}
					else
					{
						$rowFromValues = WriterEntityFactory::createRow($tmpArr);
					}
					$writer->addRow($rowFromValues);
				}
			}

			if (count($requestsAdjustment)>0) 
			{
				if ($new_sheet) 
				{
					$writer->getCurrentSheet()->setName('Ajuste de Movimientos');
					$new_sheet = false;
				}
				else
				{
					$newSheet = $writer->addNewSheetAndMakeItCurrent();
					$writer->getCurrentSheet()->setName('Ajuste de Movimientos');
				}

				$headers		= ['Ajuste de Movimientos','','','','','','','','','','','','','','','','',''];
				$tempHeaders	= [];
				foreach($headers as $k => $header)
				{
					$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol1);
				}
				$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
				$writer->addRow($rowFromValues);

				$subHeaders		= ['Datos generales','','','','','','','','Cuenta de Origen','Cuenta de Destino','','','','','Condiciones de pago','','',''];
				$tempSubHeader	= [];
				foreach($subHeaders as $k => $subHeader)
				{
					$tempSubHeader[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol2);
				}
				$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
				$writer->addRow($rowFromValues);

				$subHeaders		= ['Folio','Estado','Título','Fecha','Solicitante','Elaborado por','Fecha de elaboración','Comentarios','Empresa de origen','Empresa de destino','Dirección de destino','Departamento de destino','Clasificación del gasto de destino','Proyecto de destino','Tipo de moneda','Fecha de pago','Forma de pago','Importe total a pagar'];
				$tempSubHeader	= [];
				foreach($subHeaders as $k => $subHeader)
				{
					$tempSubHeader[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol3);
				}
				$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
				$writer->addRow($rowFromValues);

				$tempFolio	= '';
				$kindRow	= true;

				foreach($requestsAdjustment as $request)
				{
					if($tempFolio != $request->folio)
					{
						$tempFolio = $request->folio;
						$kindRow = !$kindRow;
					}

					$tmpArr = [];
					foreach($request as $k => $r)
					{
						if(in_array($k,['amount']))
						{
							if($r != '')
							{
								$tmpArr[] = WriterEntityFactory::createCell((double)$r, $currencyFormat);
							}
							else
							{
								$tmpArr[] = WriterEntityFactory::createCell($r);
							}
						}
						else
						{
							$tmpArr[] = WriterEntityFactory::createCell($r);
						}
					}

					if($kindRow)
					{
						$rowFromValues = WriterEntityFactory::createRow($tmpArr,$rowDark);
					}
					else
					{
						$rowFromValues = WriterEntityFactory::createRow($tmpArr);
					}
					$writer->addRow($rowFromValues);
				}
			}
			
			return $writer->close();
		}
		else
		{
			return redirect('/');
		}
	}

	public function billing(Request $request)
	{
		if (Auth::user()->module->where('id',152)->count()>0) 
		{
			$data		= App\Module::find($this->module_id);
			$name		= $request->name;
			$folio		= $request->folio;
			$mindate	= $request->mindate!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate) : null;
			$maxdate	= $request->maxdate!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate) : null;
			$kind		= $request->kind;

			if (Auth::user()->id == 43) 
			{
				$groups = App\Groups::select('idFolio')->where('operationType','Salida')->get();
				$requests = App\RequestModel::whereIn('kind',[11,13,14])
							->whereIn('status',[5,10,11,12,18])
							->whereNotIn('folio',$groups)
							->where(function ($query) use ($name, $mindate, $maxdate, $folio,$kind)
								{
									if($name != "")
									{
										$query->where(function($query) use($name)
										{
											$query->whereHas('requestUser', function($queryU) use($name)
											{
												$queryU->where(DB::raw("CONCAT_WS(' ',name,last_name,scnd_last_name)"),'LIKE','%'.preg_replace("/\s+/", "%", $name).'%');
											})
											->orWhereHas('elaborateUser', function($queryU) use($name)
											{
												$queryU->where(DB::raw("CONCAT_WS(' ',name,last_name,scnd_last_name)"),'LIKE','%'.preg_replace("/\s+/", "%", $name).'%');
											});
										});
									}
									if($folio != "")
									{
										$query->where('request_models.folio',$folio);
									}
									if($mindate != "" && $maxdate != "")
									{
										$query->whereBetween('reviewDate',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 23:59:59')]);
									}
									if ($kind != "") 
									{
										$query->where('kind',$kind);
									}
								})
							->orderBy('fDate','DESC')
							->orderBy('folio','DESC')
							->paginate(10);
			}
			else
			{
				$groups = App\Groups::select('idFolio')->where('operationType','Salida')->get();
				$requests = App\RequestModel::whereIn('kind',[11,13,14])
							->whereIn('status',[5,10,11,12])
							->whereNotIn('folio',$groups)
							->where(function ($query) use ($name, $mindate, $maxdate, $folio,$kind)
								{
									if($name != "")
									{
										$query->where(function($query) use($name)
										{
											$query->whereHas('requestUser', function($queryU) use($name)
											{
												$queryU->where(DB::raw("CONCAT_WS(' ',name,last_name,scnd_last_name)"),'LIKE','%'.preg_replace("/\s+/", "%", $name).'%');
											})
											->orWhereHas('elaborateUser', function($queryU) use($name)
											{
												$queryU->where(DB::raw("CONCAT_WS(' ',name,last_name,scnd_last_name)"),'LIKE','%'.preg_replace("/\s+/", "%", $name).'%');
											});
										});
									}
									if($folio != "")
									{
										$query->where('request_models.folio',$folio);
									}
									if($mindate != "" && $maxdate != "")
									{
										$query->whereBetween('reviewDate',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 23:59:59').'']);
									}
									if ($kind != "") 
									{
										$query->where('kind',$kind);
									}
								})
							->orderBy('fDate','DESC')
							->orderBy('folio','DESC')
							->paginate(10);
			}
			return response(
				view('administracion.movimientos_entre_cuentas.facturacion',
					[
						'id'		=> $data['father'],
						'title'		=> $data['name'],
						'details'	=> $data['details'],
						'child_id'	=> $this->module_id,
						'option_id'	=> 152,
						'requests'	=> $requests,
						'name'		=> $name, 
						'mindate'	=> $request->mindate,
						'maxdate'	=> $request->maxdate,
						'folio'		=> $folio,
						'kind'		=> $kind
					]
				)
			)
			->cookie(
				'urlSearch', storeUrlCookie(152), 2880
			);
		}
	}

	public function billingEdit(Request $request,$id)
	{
		if(Auth::user()->module->where('id',152)->count()>0)
		{
			$data = App\Module::find($this->module_id);
			$request = App\RequestModel::whereIn('kind',[11,13,14])
				->whereIn('status',[5,10,11,12])
				->find($id);
			if ($request != '') 
			{
				return view('administracion.movimientos_entre_cuentas.factura',
						[
							'id'		=> $data['father'],
							'title'		=> $data['name'],
							'details'	=> $data['details'],
							'child_id'	=> $this->module_id,
							'option_id'	=> 152,
							'request'	=> $request
						]
					);
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

	public function updateDocuments(Request $request, $id)
	{
		if(!isset($request->realPath))
		{
			$alert 	= "swal('', 'Por favor, seleccione al menos un archivo.', 'error');";
			return back()->with('alert',$alert);
		}
		elseif (isset($request->realPath) && count($request->realPath) > 0)
		{
			foreach ($request->realPath as $countPath)
			{
				if ($countPath == "")
				{
					$alert 	= "swal('', 'Tiene documentos pendientes de agregar.', 'error');";
					return back()->with('alert',$alert);
				}
			}
			$r 	= App\RequestModel::find($id);
			$alert 	= "swal('', 'Documentos Enviados Exitosamente', 'success');";
			switch ($r->kind) 
			{
				case 11:

					if (isset($request->realPath) && count($request->realPath)>0) 
					{
						for ($i=0; $i < count($request->realPath); $i++) 
						{ 
							if ($request->realPath[$i] != "") 
							{
								$documents					= new App\AdjustmentDocuments();
								$new_file_name = Files::rename($request->realPath[$i],$r->folio);
								$documents->path			= $new_file_name;
								$documents->idadjustment	= $r->adjustment->first()->idadjustment;
								$documents->save();
							}
						}
					}

					break;
				
				case 12:

					if (isset($request->realPath) && count($request->realPath)>0) 
					{
						for ($i=0; $i < count($request->realPath); $i++) 
						{ 
							if ($request->realPath[$i] != "") 
							{
								$documents						= new App\LoanEnterpriseDocuments();
								$new_file_name = Files::rename($request->realPath[$i],$r->folio);
								$documents->path				= $new_file_name;
								$documents->idloanEnterprise	= $r->loanEnterprise->first()->idloanEnterprise;
								$documents->save();
							}
						}
					}

					break;

				case 13:

					if (isset($request->realPath) && count($request->realPath)>0) 
					{
						for ($i=0; $i < count($request->realPath); $i++) 
						{ 
							if ($request->realPath[$i] != "") 
							{
								$documents							= new App\PurchaseEnterpriseDocuments();
								$new_file_name = Files::rename($request->realPath[$i],$r->folio);
								$documents->path					= $new_file_name;
								$documents->idpurchaseEnterprise	= $r->purchaseEnterprise->first()->idpurchaseEnterprise;
								$documents->save();
							}
						}
					}
					break;

				case 14:

					
					if (isset($request->realPath) && count($request->realPath)>0) 
					{
						$g = $r->groups->first(); 
						$g->statusBill = $request->status_bill;
						$g->save();
						for ($i=0; $i < count($request->realPath); $i++) 
						{ 
							if ($request->realPath[$i] != "") 
							{
								$documents				= new App\GroupsDocuments();
								$new_file_name = Files::rename($request->realPath[$i],$r->folio);
								$documents->path		= $new_file_name;
								$documents->idgroups	= $r->groups->first()->idgroups;
								$documents->save();
							}
						}
					}else{
						$g = $r->groups->first(); 
						$g->statusBill = $request->status_bill;
						$g->save();
						$alert 	= "swal('', 'Estado  de Factura Actualizado Exitosamente', 'success');";
					}
					break;

				case 15:

					if (isset($request->realPath) && count($request->realPath)>0) 
					{
						for ($i=0; $i < count($request->realPath); $i++) 
						{ 
							if ($request->realPath[$i] != "") 
							{
								$documents							= new App\MovementsEnterpriseDocuments();
								$new_file_name = Files::rename($request->realPath[$i],$r->folio);
								$documents->path					= $new_file_name;
								$documents->idmovementsEnterprise	= $r->movementsEnterprise->first()->idmovementsEnterprise;
								$documents->save();
							}
						}
					}
					break;

				default:
					# code...
					break;
			}
			
			return back()->with('alert',$alert);
		}
		
	}

	public function getBanks(Request $request)
	{
		if ($request->ajax()) 
		{
			$banks      = App\BanksAccounts::where('idEnterprise',$request->idEnterprise)->get();
			return view('administracion.movimientos_entre_cuentas.modal.cuentas-bancarias')->with('banks',$banks);
		}
	}
}
