@extends('layouts.child_module')
@section('data')
	@php
		$taxesCount	=	$taxesCountBilling	=	0;
		$taxes		=	$retentions			=	$taxesBilling	=	$retentionsBilling	=	0;
	@endphp
	@if ($request->parent)
		@component('components.labels.not-found',["variant" => "note"])
			@slot('attributeEx')
				id="error_request"
			@endslot
			<span class="icon-bullhorn"></span> Esta solicitud es complementaria a la solicitud #{{ $request->parent->parentRequestModel->folio }}. <br>
		@endcomponent
	@endif
	<div class="sm:text-center text-left my-5">
		A continuación podrá verificar la información de la solicitud antes de continuar con el proceso:
	</div>
	@php
		$requestUser	=	App\User::find($request->idRequest);
		$elaborateUser	=	App\User::find($request->idElaborate);
		$modelTable	=
		[
			["Folio:",			$request->folio],
			["Título y fecha:",	htmlentities($request->income->first()->title)." - ".Carbon\Carbon::createFromFormat('Y-m-d',$request->income->first()->datetitle)->format('d-m-Y')],
			["Fiscal:",			$request->taxPayment == 1 ? "Si" : "No"],
			["Solicitante:",	$requestUser->name." ".$requestUser->last_name." ".$requestUser->scnd_last_name],
			["Elaborado por:",	$elaborateUser->name." ".$elaborateUser->last_name." ".$elaborateUser->scnd_last_name],
			["Empresa:",		App\Enterprise::find($request->idEnterprise)->name],
			["Proyecto:",		isset(App\Project::find($request->idProject)->proyectName) ? App\Project::find($request->idProject)->proyectName : 'No se selccionó proyecto'],
		];
	@endphp
	@component('components.templates.outputs.table-detail', ["modelTable" => $modelTable])
		@slot('classEx')
			mt-4
		@endslot
		@slot('title')
			Detalles de la solicitud
		@endslot
	@endcomponent
	@component('components.labels.title-divisor')
		@slot('classEx')
			mt-12
		@endslot
		DATOS BANCARIOS
	@endcomponent
	@php
		$modelHead	=	[];
		$body		=	[];
		$modelBody	=	[];
		$modelHead	=
		[
			[
				["value"	=>	"Banco"],
				["value"	=>	"Alias"],
				["value"	=>	"Cuenta"],
				["value"	=>	"Sucursal"],
				["value"	=>	"Referencia"],
				["value"	=>	"CLABE"],
				["value"	=>	"Moneda"],
				["value"	=>	"Convenio"]
			]
		];
		foreach(App\BanksAccounts::where('idEnterprise',$request->idEnterprise)->get() as $bank)
		{
			$alias		= $bank->alias!=null ? $bank->alias : '---';
			$clabe		= $bank->clabe!=null ? $bank->clabe : '---';
			$account	= $bank->account!=null ? $bank->account : '---';
			$branch		= $bank->branch!=null ? $bank->branch : '---';
			$reference	= $bank->reference!=null ? $bank->reference : '---';
			$currency	= $bank->currency!=null ? $bank->currency : '---';
			$agreement	= $bank->agreement!=null ? $bank->agreement : '---';
			if ($request->income->first()->idbanksAccounts == $bank->idbanksAccounts)
			{
				$body	=
				[
					"classEx"	=>	"marktr",
					[
						"content"	=>	["label"	=>	$bank->bank->description],
					],
					[
						"content"	=>	["label"	=>	$alias],
					],
					[
						"content"	=>	["label"	=>	$account],
					],
					[
						"content"	=>	["label"	=>	$branch],
					],
					[
						"content"	=>	["label"	=>	$reference],
					],
					[
						"content"	=>	["label"	=>	$clabe],
					],
					[
						"content"	=>	["label"	=>	$currency],
					],
					[
						"content"	=>	["label"	=>	$agreement],
					]
				];
				$modelBody[]	=	$body;
			}
		}
	@endphp
	@component('components.tables.table', ["modelHead" => $modelHead, "modelBody" => $modelBody])
		@slot('classEx')
			mt-4
		@endslot
	@endcomponent
	@component('components.labels.title-divisor')
		@slot('classEx')
			mt-12
		@endslot
		DATOS DEL CLIENTE
	@endcomponent
	@php
		$modelTable	=
		[
			"Razón Social"			=>	isset($request->income->first()->client->businessName) ? $request->income->first()->client->businessName : "",
			"RFC"					=>	isset($request->income->first()->client->rfc) ? $request->income->first()->client->rfc : "",
			"Teléfono"				=>	isset($request->income->first()->client->phone) ? $request->income->first()->client->phone : "",
			"Calle"					=>	isset($request->income->first()->client->address) ? $request->income->first()->client->address : "",
			"Número"				=>	isset($request->income->first()->client->number) ? $request->income->first()->client->number : "",
			"Colonia"				=>	isset($request->income->first()->client->colony) ? $request->income->first()->client->colony : "",
			"CP"					=>	isset($request->income->first()->client->postalCode) ? $request->income->first()->client->postalCode : "",
			"Ciudad"				=>	isset($request->income->first()->client->city) ? $request->income->first()->client->city : "",
			"Estado"				=>	isset($request->income->first()->client->state_idstate) ? App\State::find($request->income->first()->client->state_idstate)->description : "",
			"Contacto"				=>	isset($request->income->first()->client->contact) ? $request->income->first()->client->contact : "",
			"Correo Electrónico"	=>	isset($request->income->first()->client->email) ? $request->income->first()->client->email : "",
			"Otro"					=>	isset($request->income->first()->client->commentaries) ? $request->income->first()->client->commentaries : ""
		];
	@endphp
	@component('components.templates.outputs.table-detail-single', ["modelTable" => $modelTable])@endcomponent
	@component('components.labels.title-divisor')
		@slot('classEx')
			mt-12
		@endslot
		DATOS DE LA VENTA
	@endcomponent
	@component('components.forms.form', ["attributeEx" => "method=\"POST\" id=\"container-alta\" action=\"".route('income.review.update', $request->folio)."\"", "methodEx" => "PUT"])
		@php
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
					["value"	=>	"Impuesto adicional"],
					["value"	=>	"Retenciones"],
					["value"	=>	"Importe"]
				]
			];
			if (isset($request))
			{
				foreach($request->income->first()->incomeDetail as $key=>$detail)
				{
					$body = [
						[
							"content"	=>	["label"	=>	$key+1],
						],
						[
							"content"	=>
							[
								["label"	=>	$detail->quantity],
								[
									"kind"			=>	"components.inputs.input-text",
									"attributeEx"	=>	"readonly=\"true\" type=\"hidden\" name=\"tquanty[]\" value=\"".$detail->quantity."\"",
									"classEx"		=>	"tquanty"
								]
							],
						],
						[
							"content"	=>
							[
								["label"	=>	htmlentities($detail->unit)],
								[
									"kind"			=>	"components.inputs.input-text",
									"attributeEx"	=>	"readonly=\"true\" type=\"hidden\" name=\"tunit[]\" value=\"".htmlentities($detail->unit)."\"",
									"classEx"		=>	"tunit"
								]
							],
						],
						[
							"content"	=>
							[
								["label"	=>	htmlentities($detail->description)],
								[
									"kind"			=>	"components.inputs.input-text",
									"attributeEx"	=>	"readonly=\"true\" type=\"hidden\" name=\"tdescr[]\" value=\"".htmlentities($detail->description)."\"",
									"classEx"		=>	"tdescr"
								],
								[
									"kind"			=>	"components.inputs.input-text",
									"attributeEx"	=>	"readonly=\"true\" type=\"hidden\" name=\"tivakind[]\" value=\"".$detail->typeTax."\"",
									"classEx"		=>	"tivakind"
								],
							],
						],
						[
							"content"	=>
							[
								["label"	=>	"$ ".$detail->unitPrice],
								[
									"kind"			=>	"components.inputs.input-text",
									"attributeEx"	=>	"readonly=\"true\" type=\"hidden\" name=\"tprice[]\" value=\"".$detail->unitPrice."\"",
									"classEx"		=>	"tprice"
								]
							],
						],
						[
							"content"	=>
							[
								["label"	=>	"$ ".$detail->tax],
								[
									"kind"			=>	"components.inputs.input-text",
									"attributeEx"	=>	"readonly=\"true\" type=\"hidden\" name=\"tiva[]\" value=\"".$detail->tax."\"",
									"classEx"		=>	"tiva"
								]
							],
						]
					];
					$componentsAmountAditional = "";
					$taxesConcept=0;
					if (count($detail->taxes)>0)
					{
						foreach ($detail->taxes as $tax)
						{
							$componentsAmountAditional	.= '<div class="contentTaxes">';
							$componentsAmountAditional	.= view('components.inputs.input-text',
							[
								"attributeEx"	=>	"type=\"hidden\" name=\"tamountadditional".$taxesCount."[]\" value=\"".$tax->amount."\"",
								"classEx"		=>	"num_amountAdditional"
							])->render();
							$componentsAmountAditional .= view('components.inputs.input-text',
							[
								"attributeEx"	=>	"type=\"hidden\" name=\"tnameamount".$taxesCount."[]\" value=\"".htmlentities($tax->name)."\"",
								"classEx"		=>	"num_nameAmount"
							])->render();
							$componentsAmountAditional	.= '</div>';
							$taxesConcept+=$tax->amount;
						}
						$componentsAmountAditional	.= '<div><label>'.'$ '.number_format($taxesConcept,2).'</label></div>';
					}
					else
					{
						$componentsAmountAditional	.= '<div><label>$ 0.00</label></div>';
					}
					$body[] = 
					[
						"content" 	=> [ "label" => $componentsAmountAditional ]
					];
					$componentsRetention = "";
					$retentionConcept=0;
					if (count($detail->retentions)>0)
					{
						foreach ($detail->retentions as $ret)
						{
							$componentsRetention 	.= '<div class="contentRetention">';
							$componentsRetention	.= view('components.inputs.input-text',
							[
								"attributeEx"	=>	"type=\"hidden\" name=\"tamountretention".$taxesCount."[]\" value=\"".$ret->amount."\"",
								"classEx"		=>	"num_amountRetention"
							])->render();
							$componentsRetention .= view('components.inputs.input-text',
							[
								"attributeEx"	=>	"type=\"hidden\" name=\"tnameretention".$taxesCount."[]\" value=\"".htmlentities($ret->name)."\"",
								"classEx"		=>	"num_nameRetention"
							])->render();
							$componentsRetention	.= '</div>';
							$retentionConcept+=$ret->amount;
						}
						$componentsRetention	.= '<div><label>'.'$ '.number_format($retentionConcept,2).'</label></div>';
					}
					else
					{
						$componentsRetention	.= '<div><label>$ 0.00</label></div>';
					}
					$taxesCount++;
					$body[] = 
					[
						"content" 	=> [ "label" => $componentsRetention ]
					];
					$body[] = 
					[
						"content"	=>
						[
							[
								"label"	=>	"$ ".$detail->amount
							],
							[
								"kind"			=>	"components.inputs.input-text",
								"attributeEx"	=>	"readonly=\"true\" type=\"hidden\" name=\"tamount[]\" value=\"".$detail->amount."\"",
								"classEx"		=>	"tamount"
							]
						]
					];
					$modelBody[]	=	$body;
				}
			}
		@endphp
		@component('components.tables.table', ["modelHead" => $modelHead, "modelBody" => $modelBody])
			@slot('classEx')
				mt-4
			@endslot
			@slot('attributeExBody')
				id="body" class="request-validate"
			@endslot
		@endcomponent
		@php
			if (isset($request))
			{
				foreach ($request->income->first()->incomeDetail as $detail)
				{
					foreach ($detail->taxes as $tax)
					{
						$taxes += $tax->amount;
					}
					foreach ($detail->retentions as $ret)
					{
						$retentions += $ret->amount;
					}
				}
				$subtotal		=	"$ ".number_format($request->income->first()->subtotales,2,".",",");
				$taxTotal		=	"$ ".number_format($request->income->first()->tax,2,".",",");
				$totalAmount	=	"$ ".number_format($request->income->first()->amount,2,".",",");
			}
			else
			{
				$subtotal		=	"";
				$taxTotal		=	"";
				$totalAmount	=	"";
			}
			$modelTable	=
			[
				["label"	=>	"Subtotal:",			"inputsEx"	=>
					[
						["kind"	=>	"components.labels.label", 		"classEx"		=>	"h-10 py-2", "label" => $subtotal],
						["kind"	=>	"components.inputs.input-text",	"attributeEx"	=>	"type=\"hidden\"	name=\"subtotal\"	value=\"".$subtotal."\""]
					]
				],
				["label"	=>	"Impuesto Adicional:",	"inputsEx"	=>
					[
						["kind"	=>	"components.labels.label", 		"classEx"		=>	"h-10 py-2", "label" => "$ ".number_format($taxes,2)],
						["kind"	=>	"components.inputs.input-text",	"attributeEx"	=>	"type=\"hidden\"	name=\"amountAA\"	value=\"$ ".number_format($taxes,2)."\""]
					]
				],
				["label"	=>	"Retenciones:",			"inputsEx"	=>
					[
						["kind"	=>	"components.labels.label", 		"classEx"		=>	"h-10 py-2", "label" => "$ ".number_format($retentions,2)],
						["kind"	=>	"components.inputs.input-text",	"attributeEx"	=>	"type=\"hidden\"	name=\"amountR\"	value=\"$ ".number_format($retentions,2)."\""]
					]
				],
				["label"	=>	"IVA:",					"inputsEx"	=>
					[
						["kind"	=>	"components.labels.label", 		"classEx"		=>	"h-10 py-2", "label" => $taxTotal],
						["kind"	=>	"components.inputs.input-text",	"attributeEx"	=>	"type=\"hidden\"	name=\"totaliva\"	value=\"".$taxTotal."\""]
					]
				],
				["label"	=>	"TOTAL:",				"inputsEx"	=>
					[
						["kind"	=>	"components.labels.label", 		"classEx"		=>	"h-10 py-2", "label" => $totalAmount],
						["kind"	=>	"components.inputs.input-text",	"attributeEx"	=>	"type=\"hidden\"	name=\"total\"		value=\"".$totalAmount."\",	id=\"input-extrasmall\""]
					]
				],
			];
		@endphp
		@component('components.templates.outputs.form-details', ["modelTable" => $modelTable])@endcomponent
		@component('components.labels.title-divisor')
			@slot('classEx')
				mt-12
			@endslot
			DOCUMENTOS
		@endcomponent
		@php
			$modelHead	=	[];
			$body		=	[];
			$modelBody	=	[];
			if (count($request->income->first()->documents)>0)
			{
				$modelHead	=	["Nombre", "Archivo", "Fecha"];
				foreach($request->income->first()->documents as $doc)
				{
					$body	=
					[
						[
							"content"	=>	["label"	=>	$doc->name],
						],
						[
							"content"	=>
							[
								[
									"kind"			=>	"components.buttons.button",
									"variant"		=>	"secondary",
									"buttonElement"	=>	"a",
									"attributeEx"	=>	"type=\"button\" target=\"_blank\" href=\"".url('docs/income/'.$doc->path)."\"",
									"label"			=>	"Archivo"
								]
							],
						],
						[
							"content"	=>	["label"	=>	Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$doc->created_at)->format('d-m-Y')],
						],
					];
					$modelBody[]	=	$body;
				}
			}
			else
			{
			$modelHead	=	["Archivos"];
			$body	=
				[
					[
						"content"	=>	["label"	=>	"NO HAY DOCUMENTOS"],
					]
				];
				$modelBody[]	=	$body;
			}
		@endphp
		@component('components.tables.alwaysVisibleTable', ["modelHead" => $modelHead, "modelBody" => $modelBody])@endcomponent
		@component('components.containers.container-approval')
			@slot('attributeExButton')
				name="status" id="aprobar" value="4"
			@endslot
			@slot('attributeExButtonTwo')
				id="rechazar" name="status" value="6"
			@endslot
		@endcomponent
		<div id="aceptar" class="hidden">
			<div class="flex flex-col items-center mt-12">
				@component('components.labels.label')
					Comentarios (opcional)
				@endcomponent
				@component("components.inputs.text-area")
					@slot('attributeEx')
						name="checkCommentA"
					@endslot
				@endcomponent
			</div>
		</div>
		<div id="rechaza" class="hidden">
			<div class="flex flex-col items-center mt-12">
				@component('components.labels.label')
					Comentarios (opcional)
				@endcomponent
				@component("components.inputs.text-area")
					@slot('attributeEx')
						name="checkCommentR"
					@endslot
				@endcomponent
			</div>
		</div>
		<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mt-4">
			@component("components.buttons.button",["variant" => "primary"])
				@slot('classEx') mr-2 @endslot
				@slot("attributeEx")
					type="submit"
					name="enviar"
					value="ENVIAR SOLICITUD"
				@endslot
				ENVIAR SOLICITUD
			@endcomponent
			@component("components.buttons.button",["variant" => "reset"])
				@slot("attributeEx")
					@if(isset($option_id)) href="{{ url(App\Module::find($option_id)->url) }}" @else href="{{ url(App\Module::find($child_id)->url) }}" @endif
				@endslot
				@slot('buttonElement')
					a
				@endslot
				@slot('classEx')
					load-actioner
				@endslot
				REGRESAR
			@endcomponent
		</div>
	@endcomponent
@endsection

@section('scripts')
	<script>
		$(document).ready(function()
		{
			$.validate(
			{
				form		: '#container-alta',
				onSuccess	: function($form)
				{
					if($('input[name="status"]').is(':checked'))
					{ 
						return true;
					}
					else
					{
						swal('', 'Debe seleccionar al menos un estado', 'error');
						return false;
					}
				}
			});
			count = 0;
			$(document).on('change','input[name="status"]',function()
			{
				if ($('input[name="status"]:checked').val() == "4") 
				{
					$("#rechaza").addClass('hidden');
					$("#aceptar").removeClass('hidden');
				}
				else if ($('input[name="status"]:checked').val() == "6") 
				{
					$("#aceptar").addClass('hidden');
					$("#rechaza").removeClass('hidden');
				}
			})
		});
	</script>
@endsection
