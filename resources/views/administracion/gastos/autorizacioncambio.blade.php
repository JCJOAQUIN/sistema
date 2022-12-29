@extends('layouts.child_module')
@section('data')
	@php
		$taxes 	= 0;
		$taxes3 = 0;
		$docs 	= 0;
	@endphp
	<div class="sm:text-center text-left my-5">
		A continuación podrá verificar la información de la solicitud antes de continuar con el proceso:
	</div>
	@php
		$modelTable = 
		[
			["Folio:",			$request->folio],
			["Título y fecha:",	htmlentities($request->expenses->first()->title).' '.Carbon\Carbon::createFromFormat('Y-m-d',$request->expenses->first()->datetitle)->format('d-m-Y')],
			["Solicitante:",	$request->requestUser->fullName()],
			["Elaborado por:",	$request->elaborateUser->fullName()],
			["Empresa:",		$request->requestEnterprise->name],
			["Dirección:",		$request->requestDirection->name],
			["Departamento:",	$request->requestDepartment->name],
			["Proyecto:",		isset($request->requestProject->proyectName) ? $request->requestProject->proyectName : 'No se selccionó proyecto'],
			["Código WBS:",		isset($request->wbs) ? $request->wbs->code_wbs : '---'],
			["Código EDT:",		isset($request->edt) ? $request->edt->fullName() : '---'],
		];
	@endphp
	@component('components.templates.outputs.table-detail', [
			"modelTable" 	=> $modelTable,
			"title"			=> "Detalles de la Solicitud"
		])
	@endcomponent
	@component('components.labels.title-divisor') DATOS DEL SOLICITANTE @endcomponent
	@php
		foreach($request->expenses as $expense)
		{
			if(isset($request))
			{
				foreach($request->expenses->first()->expensesDetail as $detail)
				{
					foreach($detail->taxes as $tax)
					{
						$taxes3 += $tax->amount;
					}
				}
			}
			$var_paymentMethod	= $expense->paymentMethod->method;
			$var_reference		= $expense->reference != '' ? htmlentities($expense->reference) : '---';
			$var_currency		= $expense->currency;
			$var_total			= '$ '.number_format($expense->total,2);
		}
		
		$var_description	= '';
		$var_alias			= '';
		$var_cardNumber	 	= '';
		$var_clabe			= '';
		$var_account	 	= '';
		foreach($request->expenses as $expense)
		{
			foreach(App\Employee::join('banks','employees.idBanks','banks.idBanks')->where('employees.idUsers',$expense->idUsers)->get() as $bank)
			{	
				if($expense->idEmployee == $bank->idEmployee)
				{
					$var_description	= $bank->description;
					$var_alias			= $bank->alias!=null ? $bank->alias : '---';
					$var_cardNumber	 	= $bank->cardNumber!=null ? $bank->cardNumber : '---';
					$var_clabe			= $bank->clabe!=null ? $bank->clabe : '---';
					$var_account	 	= $bank->account!=null ? $bank->account : '---';
				}
			}
		}
		$modelTable = [
			"Forma de pago"		=> $var_paymentMethod,
			"Referencia"		=> $var_reference,
			"Tipo de moneda"	=> $var_currency,
			"Importe"			=> $var_total,
			"Banco"				=> $var_description,
			"Alias"				=> $var_alias,
			"Número de tarjeta"	=> $var_cardNumber,
			"CLABE"				=> $var_clabe,
			"Número de cuenta"	=> $var_account,
		];
	@endphp
	@component('components.templates.outputs.table-detail-single', ["modelTable" => $modelTable]) @endcomponent
	@component('components.labels.title-divisor') RELACIÓN DE DOCUMENTOS @endcomponent
	<div class="mt-4"> 
		@php
			$body		= [];
			$modelBody	= [];
			$modelHead	= [
				[
					["value" => "#"],
					["value" => "Concepto"],
					["value" => "Clasificación del gasto"],
					["value" => "Fiscal"],
					["value" => "Subtotal"],
					["value" => "IVA"],
					["value" => "Impuesto Adicional"],
					["value" => "Importe"],
					["value" => "Documento(s)"],
				]
			];
			$subtotalFinal = $ivaFinal = $totalFinal = 0;
			$countConcept = 1;
			foreach(App\ExpensesDetail::where('idExpenses',$request->expenses->first()->idExpenses)->get() as $expensesDetail)
			{
				$subtotalFinal	+= $expensesDetail->amount;
				$ivaFinal		+= $expensesDetail->tax;
				$totalFinal		+= $expensesDetail->sAmount;

				$varAccount = '';
				if(isset($expensesDetail->account))
				{
					$varAccount = $expensesDetail->account->account.' '.$expensesDetail->account->description;
				}
				$varTax = '';
				if($expensesDetail->taxPayment==1)
				{
					$varTax = 'Si';
				}
				else
				{
					$varTax = 'No';
				}
				$taxes2 = 0;
				foreach($expensesDetail->taxes as $tax)
				{
					$taxes2 += $tax->amount;
				}
				$varTaxAmount = '$ '.number_format($taxes2,2);

				$body = [
					[
						"content" =>
						[
							"label" => $countConcept 
						]
					],
					[
						"content" =>
						[
							"label" => htmlentities($expensesDetail->concept),
						]
					],
					[
						"content" =>
						[
							"label" => $varAccount
						]
					],
					[
						"content" =>
						[
							"label" => $varTax
						]
					],
					[
						"content" =>
						[
							"label" => '$ '.number_format($expensesDetail->amount,2)
						]
					],
					[
						"content" =>
						[
							"label" => '$ '.number_format($expensesDetail->tax,2)
						]
					],
					[
						"content" =>
						[
							"label" => $varTaxAmount
						]
					],
					[
						"content" =>
						[
							"label" => '$ '.number_format($expensesDetail->sAmount,2)
						]
					]
				];
				$testDoc = '';
				if($expensesDetail->documents()->exists())
				{
					foreach($expensesDetail->documents as $doc)
					{
						if($doc->name != '')
						{ 
							$varName = $doc->name; 
						}
						else
						{
							$varName = 'Otro';
						}
						$testDoc .= '<div class="nowrap">';
						$testDoc .= '<div><label>'.$doc->date != '' ? Carbon\Carbon::createFromFormat('Y-m-d',$doc->date)->format('d-m-Y') : ''.'</label></div>';
						$testDoc .= '<div><label>'.$varName.'</label></div>';
						$testDoc .= view('components.buttons.button',[
							"variant"		=> "dark-red",
							"buttonElement"	=> "a",
							"attributeEx"	=> "target=\"_blank\" type=\"button\" title=\"".$doc->path."\"".' '."href=\"".asset('docs/expenses/'.$doc->path)."\"",
							"label"			=> 'PDF'
						])->render();
						$testDoc .= "</div>";
					}
				}
				else
				{
					$testDoc = "Sin documento";
				}
				$body[] = [ "content" => [ "label" => $testDoc ]];
				$modelBody[] = $body;
				$countConcept++;
			}
		@endphp
		@component('components.tables.table', [
			"modelBody" => $modelBody,
			"modelHead"	=> $modelHead
		])
			@slot('attributeEx')
				id="table"
			@endslot
			@slot('attributeExBody')
				id="body"
			@endslot
			@slot('classExBody')
				request-valid
			@endslot
		@endcomponent
	</div>
	<div class="totales">
		@php
			$varSubTotal 		= '';
			$varIva				= '';
			$varTotal 			= '';
			$varSubTotalLabel 	= "$ 0.00";
			$varIvaLabel 		= "$ 0.00";
			$varTotalLabel 		= "$ 0.00";
			if($totalFinal!=0)
			{
				$varSubTotal 		= number_format($subtotalFinal,2);
				$varIva				= number_format($ivaFinal,2);
				$varTotal 			= number_format($totalFinal,2);
				$varSubTotalLabel 	= '$ '.number_format($subtotalFinal,2);
				$varIvaLabel		= '$ '.number_format($ivaFinal,2);
				$varTotalLabel 		= '$ '.number_format($totalFinal,2);
			}  
			$varReintegro 		= '';
			$varReembolso 		= '';
			$varReintegroLabel 	= '$ 0.00';
			$varReembolsoLabel 	= '$ 0.00';
			if(isset($request->expenses))
			{
				foreach($request->expenses as $expense)
				{
					$varReintegro 		= number_format($expense->reintegro,2);
					$varReembolso 		= number_format($expense->reembolso,2);
					$varReintegroLabel 	= '$ '.number_format($expense->reintegro,2);
					$varReembolsoLabel 	= '$ '.number_format($expense->reembolso,2);
				}
			}
			if(isset($request))
			{
				foreach($request->expenses->first()->expensesDetail as $detail)
				{
					foreach($detail->taxes as $tax)
					{
						$taxes += $tax->amount;
					}	 
				}
			}
			$modelTable = [
				[
					"label" => "Subtotal:", "inputsEx" =>
					[
						[
							"kind" 		=> "components.labels.label",
							"label" 	=> $varSubTotalLabel,
							"classEx" 	=> "my-2 subtotal"
						],
						[
							"kind"			=> "components.inputs.input-text",
							"classEx" 		=> "subtotal",	
							"attributeEx" 	=> "type=\"hidden\" readonly id=\"subtotal\" name=\"subtotal\" value=\"".$varSubTotal."\""
						]
					]
				], 
				[
					"label" => "IVA:", "inputsEx" =>
					[
						[
							"kind" 		=> "components.labels.label",
							"label" 	=> $varIvaLabel,
							"classEx" 	=> "my-2 ivaTotal"
						],
						[
							"kind"			=> "components.inputs.input-text",
							"classEx" 		=> "ivaTotal",	
							"attributeEx" 	=> "type=\"hidden\" readonly id=\"iva\" name=\"iva\" value=\"".$varIva."\""
						]
					]
				], 
				[
					"label" => "Impuesto Adicional:", "inputsEx" =>
					[
						[
							"kind" 		=> "components.labels.label",
							"label" 	=> '$ '.number_format($taxes,2),
							"classEx"	=> "my-2 labelAmount"
						],
						[
							"kind"			=> "components.inputs.input-text",
							"attributeEx" 	=> "type=\"hidden\" readonly name=\"amountAA\" value=\"".number_format($taxes,2)."\""
						]
					]
				], 
				[
					"label" => "Reintegro:", "inputsEx" =>
					[
						[
							"kind" 		=> "components.labels.label",
							"label" 	=> $varReintegroLabel,
							"classEx" 	=> "my-2 reintegro"
						],
						[
							"kind"			=> "components.inputs.input-text",
							"attributeEx" 	=> "type=\"hidden\" readonly id=\"reintegro\" name=\"reintegro\" value=\"".$varReintegro."\"",
							"classEx" 		=> "reintegro"
						]
					]
				],
				[
					"label" => "Reembolso:", "inputsEx" =>
					[
						[
							"kind" 		=> "components.labels.label",
							"label" 	=> $varReembolsoLabel,
							"classEx" 	=> "my-2 reembolso"
						],
						[
							"kind"			=> "components.inputs.input-text",
							"attributeEx" 	=> "type=\"hidden\" readonly id=\"reembolso\" name=\"reembolso\" value=\"".$varReembolso."\"",
							"classEx" 		=> "reembolso"
						]
					]
				],
				[
					"label" => "TOTAL:", "inputsEx" =>
					[
						[
							"kind" 		=> "components.labels.label",
							"label" 	=> $varTotalLabel,
							"classEx" 	=> "my-2 total"
						],
						[
							"kind"			=> "components.inputs.input-text",
							"classEx" 		=> "total",	
							"attributeEx" 	=> "type=\"hidden\" id=\"total\" readonly name=\"total\" value=\"".$varTotal."\""
						]
					]
				]
			];
		@endphp
		@component('components.templates.outputs.form-details', [ "modelTable" => $modelTable]) @endcomponent
	</div>
	<div class="mt-4">
		@component('components.labels.title-divisor') DATOS DE REVISIÓN	@endcomponent
		@php
			$reviewAccount	= App\Account::find($request->accountR);
			$varAccounts	= '';
			if(isset($reviewAccount->account))
			{
				$varAccounts = $reviewAccount->account.' '.$reviewAccount->description;
			}
			else
			{
				$varAccounts = "Varias";
			}
			$varLabels = "";
			if(count($request->labels))
			{
				foreach($request->labels as $label)
				{
					$varLabels .= $label->description;
				}
			}
			else
			{
				$varLabels = "Sin etiqueta";
			}
			$varComment = '';
			if($request->checkComment == "")
			{
				$varComment = "Sin comentarios";
			}
			else
			{
				$varComment = htmlentities($request->checkComment);
			}

			$modelTable = [
				"Revisó"  					=> $request->reviewedUser->name.' '.$request->reviewedUser->last_name.' '.$request->reviewedUser->scnd_last_name,
				"Nombre de la Empresa"		=> $request->requestEnterprise->name,
				"Nombre de la Dirección"	=> $request->requestDirection->name,
				"Clasificación del gasto"	=> $varAccounts,
				"Nombre del Proyecto"		=> $request->reviewedProject->proyectName,
				"Comentarios"				=> $varComment
			];
			if($varLabels != "Sin etiqueta")
			{
				array_splice($modelTable, 5, "Etiquetas", $varLabels);
			}
		@endphp
		@component('components.templates.outputs.table-detail-single', ["modelTable" => $modelTable]) @endcomponent
	</div>
	<div>
		@component('components.labels.title-divisor') ETIQUETAS ASIGNADAS @endcomponent
		<div class = "mt-4">
			@php
				$body		= [];
				$modelBody	= [];
				$modelHead	= [
					[
						["value" => "Concepto"],
						["value" => "Clasificación de gasto"],
						["value" => "Etiquetas"],
					]
				];

				foreach(App\ExpensesDetail::where('idExpenses',$request->expenses->first()->idExpenses)->get() as $expensesDetail)
				{
					$varDescription = '';
					if(count($expensesDetail->labels))
					{
						foreach($expensesDetail->labels as $label)
						{
							$varDescription .= $label->label->description.", ";
						}
					}
					else
					{
						$varDescription = 'Sin etiqueta';
					}
					$body = [
						[
							"content" 	=>
							[
								"label" => htmlentities($expensesDetail->concept),
							]
						],
						[
							"content" =>
							[
								"label" => $expensesDetail->accountR->account.' '.$expensesDetail->accountR->description
							]
						],
						[
							"content" =>
							[
								"label" => $varDescription
							]
						]
					];
					$modelBody[] = $body;
				}
			@endphp
			@component('components.tables.table', [
				"modelBody" => $modelBody,
				"modelHead" => $modelHead
			])
				@slot('attributeEx')
					id="table"
				@endslot
				@slot('attributeExBody')
					id="tbody-conceptsNew"
				@endslot
				@slot('classEx')
					request-validate
				@endslot
			@endcomponent
		</div>
	</div>
	@component('components.forms.form', ["attributeEx" => "id=\"container-alta\" method=\"POST\" action=\"".route('expenses.authorization.update',$request->folio)."\"", "methodEx" => "PUT"])
		<div class="my-4">	
			@component('components.containers.container-approval')
				@slot('attributeExButton')
					name="status" id="aprobar" value="5"
				@endslot
				@slot('classExButton')
					approve
				@endslot
				@slot('attributeExButtonTwo')
					name="status" id="rechazar" value="7"
				@endslot
				@slot('classExButtonTwo')
					refuse
				@endslot
			@endcomponent
		</div>
		@foreach($request->expenses->first()->expensesDetail as $ED)
			@if($ED->idresourcedetail != null)
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="hidden" name="idresourcedetail[]" value="{{ $ED->idresourcedetail }}"
					@endslot
				@endcomponent
			@endif
		@endforeach
		@component('components.inputs.input-text')
			@slot('attributeEx')
				type="hidden" name="reintegro" value="{{ $request->expenses->first()->reintegro }}"
			@endslot
		@endcomponent
		<div id="aceptar" class="hidden mt-4">
			@component('components.labels.label') Comentarios (opcional): @endcomponent
			@component('components.inputs.text-area')
				@slot('attributeEx')
					cols="90" 
					rows="10" 
					name="authorizeCommentA"
				@endslot
				@slot('classEx')
					text-area
				@endslot
			@endcomponent
		</div>
		<div class="w-full mt-4 grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mb-6">
			@component('components.buttons.button', [
				"variant" => "primary"
			])
				@slot('attributeEx')
					type="submit"
				@endslot
					ENVIAR SOLICITUD
			@endcomponent
			@component('components.buttons.button', ["variant" => "reset", "buttonElement" => "a"])
				@slot('attributeEx')
					@if(isset($option_id)) 
						href="{{ url(getUrlRedirect($option_id)) }}" 
					@else 
						href="{{ url(getUrlRedirect($child_id)) }}" 
					@endif 
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
<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
<script src="{{ asset('js/jquery-ui.js') }}"></script>
<script src="{{ asset('js/datepicker.js') }}"></script>
<script src="{{ asset('js/jquery.numeric.js') }}"></script>
<script>
	$(document).ready(function()
	{
		$.validate(
		{
			form: '#container-alta',
			onSuccess : function($form)
			{
				if($('input[name="status"]').is(':checked'))
				{
					swal('Cargando',{
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
		$(document).on('change','input[name="status"]',function()
		{
			$("#aceptar").slideDown("slow");
		});
	});
</script>
@endsection
