@extends('layouts.child_module')
@section('data')
	<div class="mx-auto w-full md:w-1/2 grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center bg-gray-100 py-4 rounded rounded-lg">
		@component("components.labels.label", ["classEx" => "font-semibold"]) Tipo de Conciliación: @endcomponent
		@component('components.buttons.button-link', ["variant" => "red"])
			@slot('classEx')
				sub-block
			@endslot
			@slot('attributeEx')
				href="{{ route('payments.conciliation.edit') }}"
			@endslot
			Normal
		@endcomponent
		@component('components.buttons.button-link', ["variant" => "reset"])
			@slot('classEx')
				bg-gray-300
				border-none
				text-white
				shadow-md
			@endslot
			@slot('attributeEx')
				href="{{ route('payments.conciliation-nomina.edit') }}"
			@endslot
			De nómina
		@endcomponent
	</div>
	@component('components.labels.title-divisor') BUSCAR CONCILIACIÓN @endcomponent
	@component("components.forms.searchForm", ["variant" => "deafult", "attributeEx" => "id=\"formsearch\""])
		<div class="col-span-2">
			@component("components.labels.label") Folio: @endcomponent
			@component("components.inputs.input-text")
				@slot("attributeEx")
					name="folio" 
					id="input-search" 
					placeholder="Ingrese el folio"
					value="{{ isset($folio) ? $folio : "" }}"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component("components.labels.label") Rango de fechas: @endcomponent
			@php			
				if(isset($mindate) && isset($maxdate))
				{ 
					$inputs= [
						[
							"input_classEx" => "input-text-date datepicker",
							"input_attributeEx" => "name=\"mindate\" id=\"mindate\" step=\"1\" placeholder=\"Desde\" value=\"".$mindate."\"",
						],
						[
							"input_classEx" => "input-text-date datepicker",
							"input_attributeEx" => "name=\"maxdate\" id=\"maxdate\" step=\"1\" placeholder=\"Hasta\" value=\"".$maxdate."\"",
						]
					];
				}
				else if(!isset($mindate) && isset($maxdate))
				{
					$inputs= [
						[
							"input_classEx" => "input-text-date datepicker",
							"input_attributeEx" => "name=\"mindate\" id=\"mindate\" step=\"1\" placeholder=\"Desde\"",
						],
						[
							"input_classEx" => "input-text-date datepicker",
							"input_attributeEx" => "name=\"maxdate\" id=\"maxdate\" step=\"1\" placeholder=\"Hasta\" value=\"".$maxdate."\"",
						]
					];
				}
				else if(isset($minDate) && !isset($maxdate))
				{
					$inputs= [
						[
							"input_classEx" => "input-text-date datepicker",
							"input_attributeEx" => "name=\"mindate\" id=\"mindate\" step=\"1\" placeholder=\"Desde\" value=\"".$minDate."\"",
						],
						[
							"input_classEx" => "input-text-date datepicker",
							"input_attributeEx" => "name=\"maxdate\" id=\"maxdate\" step=\"1\" placeholder=\"Hasta\"",
						]
					];
				}
				else
				{
					$inputs= [
						[
							"input_classEx" => "input-text-date datepicker",
							"input_attributeEx" => "name=\"mindate\" id=\"mindate\" step=\"1\" placeholder=\"Desde\"",
						],
						[
							"input_classEx" => "input-text-date datepicker",
							"input_attributeEx" => "name=\"maxdate\" id=\"maxdate\" step=\"1\" placeholder=\"Hasta\"",
						]
					];
				}
			@endphp
			@component("components.inputs.range-input",["inputs" => $inputs]) @endcomponent
		</div>
		<div class="col-span-2">
			@component("components.labels.label") Empresa: @endcomponent
			@php
				$options = collect();
				foreach(App\Enterprise::orderBy('name','asc')->whereIn('id',Auth::user()->inChargeEnt(103)->pluck('enterprise_id'))->get() as $enterprise)
				{
					$description = strlen($enterprise->name) >= 35 ? substr(strip_tags($enterprise->name),0,35)."..." : $enterprise->name;
					if(isset($enterpriseid) && $enterpriseid == $enterprise->id)
					{
						$options = $options->concat([["value"=>$enterprise->id, "selected"=>"selected", "description"=>$description]]);
					}
					else
					{
						$options = $options->concat([["value"=>$enterprise->id, "description"=>$description]]);
					}
				}
				$attributeEx = "title=\"Empresa\" name=\"enterpriseid\" multiple=\"multiple\" id=\"multiple-enterprises\"";
				$classEx = "js-enterprise";
			@endphp
			@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])
			@endcomponent
		</div>
		<div class="col-span-2">
			@component("components.labels.label") Cuenta: @endcomponent
			@php
				if(isset($enterpriseid) || isset($account))
				{
					$options = collect();
					foreach(App\Account::where('selectable',1)->where('idEnterprise',$enterpriseid)->get() as $acc)
					{
						$description = $acc->account." - ".$acc->description." (".$acc->content.")";
						if(isset($account) && $acc->idAccAcc==$account)
						{
							$options = $options->concat([["value"=>$acc->idAccAcc, "selected"=>"selected", "description"=>$description]]);
						}
						else
						{
							$options = $options->concat([["value"=>$acc->idAccAcc, "description"=>$description]]);
						}
					}
				}
				else 
				{
					$options = collect();	
				}
				$attributeEx = "title=\"Cuenta\" name=\"account\" multiple=\"multiple\"";
				$classEx = "js-account removeselect";
			@endphp
			@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])
			@endcomponent
		</div>
		@if(count($payments) > 0)
			@slot("export")
			<div class="text-right">
				<label>
					@component("components.buttons.button",["variant" => "success"])
					@slot("attributeEx") type="submit" formaction="{{ route('payments.conciliation-nomina.export') }}" @endslot
					@slot("slot") <span>Exportar a Excel</span><span class="icon-file-excel"></span> @endslot
					@endcomponent
				</label>
			</div>
			@endslot
		@endif
	@endcomponent

	@if(count($payments) > 0)
		@php			
			$modelHead = 
			[
				[
					[
						"value" 	=> "Datos del Pago",
						"colspan" 	=> 5,						
					],
					[
						"value" 	=> "Datos del Movimiento",
						"colspan"	=> 4,
					]
				],
				[
					["value" => "Solicitud"],
					["value" => "Empresa"],
					["value" => "Empleado"],
					["value" => "Importe del Pago"],
					["value" => "Clasificación del gasto"],
					["value" => "Movimiento"],
					["value" => "Importe del Movimiento"],
					["value" => "Fecha de Conciliación"],
					["value" => "Acción"]
				]
			];

			$modelBody = [];
			foreach($payments as $payment)
			{
				$body = 
				[
					"classEx" => "tr",
					[
						"classEx" => "td",
						"show" => "true",
						"content" =>
						[
							[
								"kind" => "components.labels.label",
								"label" => $payment->idFolio,
							]
						]
					],
					[
						"classEx" => "td",
						"content" =>
						[
							[
								"kind" => "components.labels.label",
								"label" => $payment->enterprise->name,
							]
						]
					],
					[
						"classEx" => "td",
						"content" =>
						[
							[
								"kind" => "components.labels.label",
								"label" => $payment->nominaEmployee->employee->first()->fullName(),
							]
						]
					],
					[
						"classEx" => "td",
						"content" =>
						[
							[
								"kind" => "components.labels.label",
								"label" => "$ ".number_format($payment->amount,2),
							]
						]
					],
					[
						"classEx" => "td",
						"content" =>
						[
							[
								"kind" => "components.labels.label",
								"label" => $payment->accounts->account." - ".$payment->accounts->description." (".$payment->accounts->content.")",
							]
						]
					],
					[
						"classEx" => "td",
						"content" =>
						[
							[
								"kind" => "components.labels.label",
								"label" => $payment->movement->description,
							]
						]
					],
					[
						"classEx" => "td",
						"content" =>
						[
							[
								"kind" => "components.labels.label",
								"label" => "$ ".number_format($payment->movement->amount,2),
							]
						]
					],
					[
						"classEx" => "td",
						"content" =>
						[
							[
								"kind" => "components.labels.label",
								"label" => Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $payment->conciliationDate)->format('d-m-Y H:i:s'),
							]
						]
					],
					[
						"classEx" => "td",
						"content" =>
						[
							[
								"kind" => "components.buttons.button",
								"attributeEx" => "title=\"Detalles de Conciliación\" alt=\"Detalles de Conciliación\" type=\"button\" data-toggle=\"modal\" data-target=\"#myModal\"",
								"classEx" => "detailConciliation",
								"variant" => "secondary",
								"label" => "<span class=\"icon-search\"></span>",
							],
							[
								"kind" => "components.buttons.button", 
								"attributeEx" => "alt=\"Eliminar Conciliación\" title=\"Eliminar Conciliación\" type=\"button\" data-link=\"".route('payments.conciliation-nomina.update',$payment->idpayment)."\"",
								"variant" => "red",
								"label" => "<span class='icon-bin'></span>",
								"classEx"	  => "delete-conciliation",
							],
							[
								"kind" => "components.inputs.input-text",
								"attributeEx" => "type=\"hidden\" value=\"".$payment->idpayment."\"",
								"classEx" => "payment",
							],
						]
					],
				];
				$modelBody [] = $body;
			}
		@endphp
		@component("components.tables.table", ["modelHead" => $modelHead, "modelBody" => $modelBody]) @endcomponent
		@component("components.modals.modal",[ "variant" => "large" ])
			@slot("id")
				myModal
			@endslot
			@slot("attributeEx")
				tabindex="-1"
			@endslot
			@slot("modalHeader")
				@component("components.buttons.button")
					@slot("attributeEx")
						type="button"
						data-dismiss="modal"
					@endslot
					@slot('classEx')
						close
					@endslot
					<span aria-hidden="true">&times;</span>
				@endcomponent
			@endslot
			@slot("modalBody")

			@endslot
		@endcomponent
		{{ $payments->appends($_GET)->links() }}
	@else
		@component("components.labels.not-found") @endcomponent
	@endif
@endsection

@section('scripts')

<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
<script src="{{ asset('js/jquery-ui.js') }}"></script>
<script src="{{ asset('js/datepicker.js') }}"></script>
<script src="{{ asset('js/jquery.numeric.js') }}"></script>
	<script type="text/javascript"> 
	$(document).ready(function()
	{
		@php
			$selects = collect([
				[
					"identificator"          => ".js-enterprise", 
					"placeholder"            => "Seleccione la empresa",
					"maximumSelectionLength" => "1"
				],
			]);
		@endphp
		@component("components.scripts.selects",["selects" => $selects]) @endcomponent
		generalSelect({'selector': '.js-account', 'depends': '.js-enterprise', 'model': 10});
		
	});
	$(document).on('click','.detailConciliation',function()
	{
		swal({
			icon               : '{{ asset(getenv('LOADING_IMG')) }}',
			button             : false,
			closeOnClickOutside: false,
			closeOnEsc         : false
		});
		$('#myModal .modal-body').html('');
		idpayment = $(this).parents('.tr').find('.payment').val();
		$.ajax(
		{
			type 	: 'post',
			url 	: '{{ route("payments.conciliation-nomina.details") }}',
			data 	: {'idpayment':idpayment},
			success	: function(data)
			{
				$('#myModal .modal-body').html(data);
				$('#myModal').show();
				$('.detail').attr('disabled','disabled');
			},
			error: function(data)
			{
				$('#detail').slideUp();
				$('#myModal').hide();
				$('.detail').removeAttr('disabled');
				$('.modal-backdrop').remove();
				swal('','Sucedió un error, por favor intente de nuevo.','error');
			}
		}).done(function(data)
		{
			swal.close();
		});
	})
	.on('click','.exit',function()
	{
		$('#detail').slideUp();
		$('#myModal').hide();
		$('.detail').removeAttr('disabled');
	})
	.on('click','.delete-conciliation', function(e)
	{	
		e.preventDefault();
		url	= $(this).attr('data-link');
		swal({
			title		: "Confirmar",
			text		: "¿Desea eliminar la conciliación?",
			icon		: "warning",
			buttons		: ["Cancelar","Eliminar"],
			dangerMode	: true,
			})
			.then((isConfirm) =>
			{
				if(isConfirm)
				{
					swal({
						icon               : '{{ asset(getenv('LOADING_IMG')) }}',
						button             : false,
						closeOnClickOutside: false,
						closeOnEsc         : false
					});
					form = $('<form action="'+url+'" method="POST"></form>')
						.append($('@csrf'))
						.append($('@method("PUT")'));
					$(document.body).append(form);
					form.submit();
				}
			});
	})
	.on('change','.js-enterprise',function()
	{
		$('.js-account').html('');
	});
    </script> 
@endsection
