@extends('layouts.child_module')
@section('data')
@php
	$taxes=$retentions=0;
@endphp
	@if($request->purchases->first()->idRequisition != "")
		@component("components.labels.not-found", ["variant" => "note"])
			@slot("slot")
				<span class="icon-bullhorn"></span> Esta solicitud viene de la requisición #{{ $request->purchases->first()->idRequisition }}. 
			@endslot
		@endcomponent
		@component("components.labels.not-found", ["variant" => "note"])
			@slot("slot")
				<div class="flex flex-row">
					@component('components.labels.label') 
						FOLIO: 
					@endcomponent @component('components.labels.label')
						@slot('classEx')
							px-2
						@endslot 
						{{ $request->new_folio }} 
					@endcomponent
				</div>
				@if($request->purchases->first()->requisitionRequest->idProject == 75)
					<div class="flex flex-row">
						@component('components.labels.label') 
							SUBPROYECTO/CÓDIGO WBS:
						@endcomponent @component('components.labels.label')
							@slot('classEx')
								px-2
							@endslot 
							{{ $request->purchases->first()->requisitionRequest->requisition->wbs->code_wbs }}.
						@endcomponent
					</div>
					@if($request->purchases->first()->requisitionRequest->requisition->edt()->exists())
						<div class="flex flex-row">
							@component('components.labels.label') 
								CÓDIGO EDT:
							@endcomponent @component('components.labels.label')
								@slot('classEx')
									px-2
								@endslot 
								{{ $request->purchases->first()->requisitionRequest->requisition->edt()->exists() ? $request->purchases->first()->requisitionRequest->requisition->edt->fullName() : '' }}.
							@endcomponent
						</div>
					@endif
				@endif
			@endslot
		@endcomponent
	@endif
	<div class="sm:text-center text-left my-5">
		A continuación podrá verificar la información de la solicitud antes de continuar con el proceso:
	</div>
	@php
		$requestUser    = App\User   ::find($request->idRequest);
		$elaborateUser  = App\User   ::find($request->idElaborate);
		$requestAccount = App\Account::find($request->account);
		$modelTable    = 
		[
			[
				"Folio:", $request->new_folio != null ? $request->new_folio : $request->folio
			],
			[
				"Título y fecha:", ($request->purchases->count() > 0 && $request->purchases->first()->title != '' ? htmlentities($request->purchases->first()->title).($request->purchases->first()->datetitle != "" ? " - ".Carbon\Carbon::createFromFormat('Y-m-d', $request->purchases->first()->datetitle)->format('d-m-Y') : '') : ''),
			],
			[
				"Número de Orden:", empty($request->purchases->first()) ? "---" : htmlentities($request->purchases->first()->numberOrder),
			],
			[
				"Fiscal:", $request->taxPayment == 1 ? "Si" : "No"
			],
			[
				"Solicitante:", $requestUser->name." ".$requestUser->last_name." ".$requestUser->scnd_last_name
			],
			[
				"Elaborado por:", $elaborateUser->name." ".$elaborateUser->last_name." ".$elaborateUser->scnd_last_name
			],
			[
				"Empresa:", App\Enterprise::find($request->idEnterprise)->name
			],
			[
				"Dirección:", App\Area::find($request->idArea)->name
			],
			[
				"Departamento:", App\Department::find($request->idDepartment)->name
			],
			[
				"Clasificación del gasto:", $requestAccount->account." - ".$requestAccount->description
			],
			[
				"Proyecto:", isset(App\Project::find($request->idProject)->proyectName) ? App\Project::find($request->idProject)->proyectName : 'No se selccionó proyecto'
			]
		];
	@endphp
	@component("components.templates.outputs.table-detail", 
	[
		"modelTable" => $modelTable, 
		"title"      => "Detalles de la Solicitud"
	]) 
	@endcomponent
	@component('components.labels.title-divisor') DATOS DEL PROVEEDOR @endcomponent
	@php
		$modelTable =
		[
			"Razón Social "		=> isset($request->purchases->first()->provider->businessName) ? $request->purchases->first()->provider->businessName : "",
			"RFC " 				=> isset($request->purchases->first()->provider->rfc) ? $request->purchases->first()->provider->rfc : "",
			"Teléfono " 		=> isset($request->purchases->first()->provider->phone) ? $request->purchases->first()->provider->phone : "",
			"Calle " 			=> isset($request->purchases->first()->provider->address) ? $request->purchases->first()->provider->address : "",
			"Número " 			=> isset($request->purchases->first()->provider->number) ? $request->purchases->first()->provider->number : "",
			"Colonia " 			=> isset($request->purchases->first()->provider->colony) ? $request->purchases->first()->provider->colony : "",
			"CP"				=> isset($request->purchases->first()->provider->postalCode) ? $request->purchases->first()->provider->postalCode : "",
			"Ciudad " 			=> isset($request->purchases->first()->provider->city) ? $request->purchases->first()->provider->city : "",
			"Estado " 			=> isset($request->purchases->first()->provider->state_idstate) ? App\State::find($request->purchases->first()->provider->state_idstate)->description : "",
			"Contacto " 		=> isset($request->purchases->first()->provider->contact) ? $request->purchases->first()->provider->contact : "",
			"Beneficiario " 	=> isset($request->purchases->first()->provider->beneficiary) ? $request->purchases->first()->provider->beneficiary : "",
			"Otro " 			=> isset($request->purchases->first()->provider->commentaries) ? $request->purchases->first()->provider->commentaries : ""
		];
	@endphp
	@component('components.labels.subtitle')
		CUENTAS BANCARIAS <span class="help-btn" id="help-btn-account-bank">
	@endcomponent
	@php
		$body 	   = [];
		$modelBody = [];
		$modelHead = 
		[
			[
				["value" => "Banco"],
				["value" => "Alias"],
				["value" => "Cuenta"],
				["value" => "Sucursal"],
				["value" => "Referencia"],
				["value" => "CLABE"],
				["value" => "Moneda"],
				["value" => "IBAN"],
				["value" => "BIC/SWIFT"],
				["value" => "Convenio"],
			],
		];
		if(isset($request->purchases->first()->provider->providerData))
		{
			$classEx = "";
			foreach($request->purchases->first()->provider->providerData->providerBank as $bank)
			{
				if($request->purchases->first()->provider_has_banks_id == $bank->id) 
				{
					$classEx = "marktr";
				}

				if($bank->iban == "")
				{
					$iban = "---";
				}
				else
				{
					$iban = $bank->iban;
				}
				
				if($bank->bic_swift=='')
				{
					$swift = "---";
				}
				else
				{
					$swift = $bank->bic_swift;
				}
						
				if($bank->agreement=='')
				{
					$agreement = "---";
				}
				else
				{
					$agreement = $bank->agreement;
				}

				if($request->purchases->first()->provider_has_banks_id == $bank->id)
				{
					$value_idchecked = 1;
				}
				else 
				{
					$value_idchecked = 0; 
				}
				$body = 
				[
					"classEx" => $classEx,
					[
						"content" => 
						[
							"label" => $bank->bank->description
						]
					],
					[
						"content" => 
						[
							"label" => $bank->alias != "" ? $bank->alias : '---' 
						]
					],
					[
						"content" => 
						[ 
							"label" => $bank->account != "" ? $bank->account : '---'
						]
					],
					[
						"content" => 
						[ 
							"label" => $bank->branch != "" ? $bank->branch : '---'
						]
					],
					[
						"content" => 
						[
							"label" => $bank->reference != "" ? $bank->reference : "---"
						]
					],
					[
						"content" => 
						[
							"label" => $bank->clabe != "" ? $bank->clabe : '---'
						]
					],
					[
						"content" => 
						[
							"label" => $bank->currency
						]
					],
					[
						"content" => 
						[ 
							"label" => $iban
						]
					],
					[
						"content" => 
						[ 
							"label" => $swift
						]
					],
					[
						"content" => 
						[
							"label" => $agreement
						]
					]
				];
				array_push($modelBody, $body);
				
			}
		}
	@endphp
	@component('components.tables.table',[
		"modelHead" 			=> $modelHead,
		"modelBody" 			=> $modelBody,
		"themeBody" 			=> "striped"
	])
		@slot('attributeExBody')
			id="table2"
		@endslot
	@endcomponent
	@component('components.labels.title-divisor') 
		DATOS DEL PEDIDO 
		@slot('classExContainer')
			pb-4
		@endslot
	@endcomponent
	@php
		$countConcept = 1;
		$body 	      = [];
		$modelBody    = [];
		$modelHead    = 
		[
			[
				["value" => "#"],
				["value" => "Cantidad"],
				["value" => "Unidad"],
				["value" => "Descripción"],
				["value" => "Precio Unitario"],
				["value" => "IVA"],
				["value" => "Impuesto Adicional"],
				["value" => "Retenciones"],
				["value" => "Importe"]
			]
		];
		if(isset($request->purchases))
		{
			foreach($request->purchases->first()->detailPurchase as $detail)
			{
				$taxesConcept=0;
				foreach($detail->taxes as $tax)
				{
					$taxesConcept+=$tax->amount;
				}

				$retentionConcept=0;
				foreach($detail->retentions as $ret)
				{
					$retentionConcept+=$ret->amount;
				}
				$body = 
				[
					[
						"content" => 
						[
							"label" => "$".$countConcept
						]
					],
					[
						"content" => 
						[
							"label" => $detail->quantity
						]
					],
					[
						"content" => 
						[ 
							"label" => $detail->unit
						]
					],
					[
						"content" => 
						[ 
							"label" => htmlentities($detail->description),
						]
					],
					[
						"content" => 
						[
							"label" => "$ ".number_format($detail->unitPrice,2)
						]
					],
					[
						"content" => 
						[
							"label" => "$ ".number_format($detail->tax,2)
						]
					],
					[
						"content" => 
						[
							"label" => "$ ".number_format($taxesConcept,2)
						]
					],
					[
						"content" => 
						[ 
							"label" => "$ ".number_format($retentionConcept,2)
						]
					],
					[
						"content" => 
						[ 
							"label" => "$ ".number_format($detail->amount,2)
						]
					]
				];
				array_push($modelBody, $body);
				$countConcept++;
			}
		}
	@endphp
	@component('components.tables.table',[
		"modelHead" 			=> $modelHead,
		"modelBody" 			=> $modelBody,
		"themeBody" 			=> "striped"
	])
		@slot('attributeExBody')
			id="table"
		@endslot
	@endcomponent
	<div class="totales2">
	@php
		$subtotal  		= "$ ".number_format(0,2,".",",");
		$iva       		= "$ ".number_format(0,2,".",",");
		$total     		= "$ ".number_format(0,2,".",",");
		$taxes_val 		= "$ ".number_format(0,2,".",",");
		$retentions_val = "$ ".number_format(0,2,".",",");
		$notas     		= "name=\"note\" placeholder=\"Ingrese una nota\" cols=\"80\" readonly=\"readonly\"";
		$textNotes 		= "";
		if(isset($request))
		{
			$subtotal	= "$ ".number_format($request->purchases->first()->subtotales,2,".",",");
			$iva		= "$ ".number_format($request->purchases->first()->tax,2,".",",");
			$total		= "$ ".number_format($request->purchases->first()->amount,2,".",",");
			$textNotes	= $request->purchases->first()->notes;
			foreach($request->purchases->first()->detailPurchase as $detail)
			{				
				foreach($detail->taxes as $tax)
				{
					$taxes += $tax->amount;
				}
			}
			foreach($request->purchases->first()->detailPurchase as $detail)
			{					
				foreach($detail->retentions as $ret)
				{
					$retentions += $ret->amount;
				}
			}
			$taxes_val		= "$ ".number_format($taxes,2);
			$retentions_val = "$ ".number_format($retentions,2); 
		}
		$modelTable = 
		[
			[
				"label"            => "Subtotal: ", 
				"inputsEx"		   => 
									[
										[
											"kind" 		  => "components.labels.label",
											"label"		  => $subtotal,
											"classEx"     => "h-10 py-2"
										],
										[
											"kind" 		  => "components.inputs.input-text",
											"attributeEx" => "type=\"hidden\" value=\"".(isset($request) ? number_format($request->purchases->first()->subtotales,2,".",",") : "0.00")."\" readonly name=\"subtotal\"",
											"classEx"     => "removeInput"
										]
									]
			],
			[
				"label"            => "Impuesto Adicional: ",	
				"inputsEx"		   => 
									[
										[
											"kind" 		  => "components.labels.label",
											"label"		  => $taxes_val,
											"classEx"     => "h-10 py-2"
										],
										[
											"kind" 		  => "components.inputs.input-text",
											"attributeEx" => "type=\"hidden\" value=\"".(isset($request) ? number_format($taxes,2) : "0.00")."\" readonly name=\"amountAA\"",
											"classEx"     => "removeInput"
										]
									]
			],
			[
				"label"            => "Retenciones: ",	
				"inputsEx"		   => 
									[
										[
											"kind" 		  => "components.labels.label",
											"label"		  => $retentions_val,
											"classEx"     => "h-10 py-2"
										],
										[
											"kind" 		  => "components.inputs.input-text",
											"attributeEx" => "type=\"hidden\" value=\"".(isset($request) ? number_format($retentions,2) : "0.00")."\" readonly name=\"amountR\"",
											"classEx"     => "removeInput"
										]
									]
			],
			[
				"label"            => "IVA: ",	
				"inputsEx"		   => 
									[
										[
											"kind" 		  => "components.labels.label",
											"label"		  => $iva,
											"classEx"     => "h-10 py-2"
										],
										[
											"kind" 		  => "components.inputs.input-text",
											"attributeEx" => "type=\"hidden\" value=\"".(isset($request) ? number_format($request->purchases->first()->tax,2,".",",") : "0.00")."\" readonly name=\"totaliva\"",
											"classEx"     => "removeInput"
										]
									]
			],
			[
				"label"            => "TOTAL: ", 
				"inputsEx"		   => 
									[
										[
											"kind" 		  => "components.labels.label",
											"label"		  => $total,
											"classEx"     => "h-10 py-2"
										],
										[
											"kind" 		  => "components.inputs.input-text",
											"attributeEx" => "type=\"hidden\" value=\"".(isset($request) ? number_format($request->purchases->first()->amount,2,".",",") : "0.00")."\" readonly name=\"total\"",
											"classEx"     => "removeInput"
										]
									]
			],
		];
	@endphp
	@component('components.templates.outputs.form-details',[
		"modelTable" 		 => $modelTable,
		"attributeExComment" => $notas,
		"textNotes"          => $textNotes
	])
	@endcomponent
	@component('components.labels.title-divisor') 
		CONDICIONES DE PAGO
		@slot('classExContainer')
			pb-4
		@endslot
	@endcomponent
	@php
		$date = $request->PaymentDate != '' ? $request->PaymentDate->format('d-m-Y') : "";
		$modelTable =
		[
			"Referencia/Número de factura " => (($request->purchases->first()->reference != "") ? htmlentities($request->purchases->first()->reference) : "---"),
			"Tipo de moneda " 				=> $request->purchases->first()->typeCurrency,
			"Fecha de pago " 				=> $date,
			"Forma de pago " 				=> $request->purchases->first()->paymentMode,
			"Estatus de factura " 			=> $request->purchases->first()->billStatus,
			"Importe a pagar " 				=> number_format($request->purchases->first()->amount,2)
		];
	@endphp
	@component("components.templates.outputs.table-detail-single", ["modelTable" => $modelTable]) 
	@endcomponent
	@component('components.labels.title-divisor') 
		DOCUMENTOS
		@slot('classExContainer')
			pb-4
		@endslot
	@endcomponent
	@php
		$body 	   = [];
		$no_result = true;
		$modelBody = [];
		$modelHead = ["Tipo de Documento" ,"Archivo", "Fecha"];
		if(count($request->purchases->first()->documents)>0)
		{
			foreach($request->purchases->first()->documents as $doc)
			{
				$body = 
				[
					[
						"content" => 
						[
							[
								"label" => $doc->name
							]
						]
					],
					[
						"content" => 
						[
							[
								"kind"          => "components.buttons.button",
								"variant"       => "secondary",
								"label"         => "Archivo",
								"buttonElement" => "a",
								"attributeEx"   => "target = \"_blank\" href = \"".url('docs/purchase/'.$doc->path)."\""
							]
						]
					],
					[ 
						"content" => 
						[
							[
								"label" => $doc->date->format('d-m-Y')
							]
						]
					]
				];
				array_push($modelBody, $body);
			}
			$no_result = false;
		}
	@endphp
	@if($no_result)
		@component("components.labels.not-found", ["text" => "No se han encontrado documentos registrados"]) @endcomponent
	@else
		@component('components.tables.alwaysVisibleTable',[
			"modelHead" 			=> $modelHead,
			"modelBody" 			=> $modelBody,
			"themeBody" 			=> "striped"
		])
			@slot('attributeExBody')
				id="banks-body"
			@endslot
		@endcomponent
	@endif
	@component('components.labels.title-divisor') 
		DATOS DE REVISIÓN
		@slot('classExContainer')
			pb-4
		@endslot
	@endcomponent
	@php
		$date = $request->PaymentDate != '' ? $request->PaymentDate->format('d-m-Y') : "";
		$reviewAccount = App\Account::find($request->accountR);
		$labels = "";
		foreach($request->labels as $label)
		{
			$labels = $labels." ".$label->description;
		}
		$modelTable =
		[
			"Revisó "                  => $request->reviewedUser->name." ".$request->reviewedUser->last_name." ".$request->reviewedUser->scnd_last_name,
			"Nombre de la Empresa "    => App\Enterprise::find($request->idEnterpriseR)->name,
			"Nombre de la Dirección "  => $request->reviewedDirection->name,
			"Nombre del Departamento " => App\Department::find($request->idDepartamentR)->name,
			"Clasificación del gasto " => isset($reviewAccount->account) ? $reviewAccount->account." - ".$reviewAccount->description: "No hay",
			"Nombre del Proyecto " 	   => $request->reviewedProject->proyectName,
			"Etiquetas " 			   => $labels != "" ? $labels: "No hay etiquetas",
			"Comentarios " 			   => $request->checkComment== "" ? "Sin comentarios": htmlentities($request->checkComment), 
		];
	@endphp
	@component("components.templates.outputs.table-detail-single", ["modelTable" => $modelTable]) 
	@endcomponent
	@component('components.labels.title-divisor') 
		ETIQUETAS ASIGNADAS
		@slot('classExContainer')
			pb-4
		@endslot
	@endcomponent

	@php
		$body 	   = [];
		$no_result = true;
		$labels = "";
		foreach($detail->labels as $label)
		{
			$labels = $labels." ".$label->label->description;
		}
		$modelBody = [];
		$modelHead = ["Cantidad" ,"Descripción", "Etiquetas"];
		if(!empty($request->purchases->first()))
		{
			foreach($request->purchases->first()->detailPurchase as $detail)
			{
				$body = 
				[
					[
						"content" => 
						[
							"label" => $detail->quantity.' '.$detail->unit
						]
					],
					[
						"content" => 
						[
							"label" => htmlentities($detail->description),
						]
					],
					[ 
						"content" => 
						[
							[
								"label" => $labels
							]
						]
					]
				];
				array_push($modelBody, $body);
			}
			$no_result = false;
		}
	@endphp
	@if($no_result)
		@component("components.labels.not-found")
		@endcomponent
	@else
		@component('components.tables.alwaysVisibleTable',[
			"modelHead" 			=> $modelHead,
			"modelBody" 			=> $modelBody,
			"themeBody" 			=> "striped"
		])
			@slot('attributeExBody')
				id="banks-body"
			@endslot
		@endcomponent
	@endif
	@php
		$i					= 1; 
		$idPurchase			= $request->purchases->first()->idPurchase;
		$partialPayments	= $request->purchases->first()->partialPayment;
		$editable			= false;
	@endphp

	<input value="0" class="partial_total" type="hidden">
	@include('administracion.compra.form.partial')
	
	<div class="pt-4">
		@component("components.forms.form",
		[
			"attributeEx" => "id=\"container-alta\" method=\"post\" action=\"".route('purchase.authorization.update', $request->folio)."\"",
			"methodEx"	  => "PUT",
			"token"       => "true"
		])
			<div class="my-4">	
				@component("components.containers.container-approval")
					@slot("attributeExButton")
						name="status" value="5" id="aprobar"  
					@endslot
					@slot("attributeExButtonTwo")
						name="status" value="7" id="rechazar"
					@endslot
				@endcomponent
			</div>
			<div id="aceptar" class="hidden">
				@component('components.labels.label') Comentarios @endcomponent
				@component('components.inputs.text-area')
					@slot('attributeEx')
						name            = "authorizeCommentA" 
						cols            = "90" 
						rows            = "10"
						placeholder     = "Ingrese un comentario" 
					@endslot
				@endcomponent
			</div>
			<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mt-8">
				@component("components.buttons.button",["variant" => "primary"])
					@slot('attributeEx') 
						type="submit"  name="enviar"
					@endslot
					@slot('classEx') 
						w-48 md:w-auto
					@endslot
					ENVIAR SOLICITUD
				@endcomponent	
				@component("components.buttons.button",["variant" => "reset"])
					@slot('buttonElement')
						a
					@endslot 
					@slot('attributeEx') 
						@if(isset($option_id)) 
							href="{{ url(getUrlRedirect($option_id)) }}" 
						@else 
							href="{{ url(getUrlRedirect($child_id)) }}" 
						@endif 
					@endslot
					@slot('classEx') 
						load-actioner w-48 md:w-auto text-center
					@endslot
					REGRESAR
				@endcomponent		
			</div>
		@endcomponent
	</div>
@endsection
@section('scripts')
<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
<script src="{{ asset('js/jquery-ui.js') }}"></script>
<script src="{{ asset('js/jquery.numeric.js') }}"></script>
<script src="{{ asset('js/datepicker.js') }}"></script>

<script>
	function validate()
	{
		$.validate(
		{
			form: '#container-alta',
			onSuccess : function($form)
			{
				if($('input[name="status"]').is(':checked'))
				{
					swal("Cargando",{
						icon: '{{ asset(getenv('LOADING_IMG')) }}',
						button: false,
						closeOnClickOutside: false,
						closeOnEsc: false
					});
					return true;
				}
				else
				{
					swal('', 'Debe seleccionar al menos un estado', 'error');
					return false;
				}
			}
		});
	}
	$(document).ready(function() 
	{
		validate();
		generalSelect({'selector': '.js-users', 'model': 36});
		generalSelect({'selector': '.js-projects', 'model': 17, 'option_id': {{$option_id}} });
		generalSelect({'selector': '.js-accounts', 'depends':'.js-enterprises', 'model':10});
		generalSelect({'selector': '.js-state', 'model': 31});
		generalSelect({'selector': '.js-bank', 'model': 28});
		generalSelect({'selector': '#cp', 'model': 2});
		@php
			$selects = collect([
				[
					"identificator"          => ".js-enterprises", 
					"placeholder"            => "Seleccione la empresa", 
					"maximumSelectionLength" => "1"
				],
				[
					"identificator"          => ".js-areas", 
					"placeholder"            => "Seleccione la dirección", 
					"maximumSelectionLength" => "1"
				],
				[
					"identificator"          => ".js-departments", 
					"placeholder"            => "Seleccione el departamento", 
					"maximumSelectionLength" => "1"
				],
				[
					"identificator"          => ".js-labels", 
					"placeholder"            => "Seleccione las etiquetas correspondientes"
				]
			]);
		@endphp
		@component("components.scripts.selects",["selects" => $selects])
		@endcomponent
	});

	$('input[name="status"]').change(function()
	{
		$("#aceptar").slideDown("slow");
	});

	sumatotal = 0;
	$('.importe').each(function(i, v){
		valor = parseFloat($(this).val());
		sumatotal = sumatotal + valor;
	});
	$('.subtotal').text("$"+sumatotal);
		
	$(function()
	{
		$( "#datepicker" ).datepicker({ minDate: 0, dateFormat: "dd-mm-yy" });
	});
</script>
@endsection
