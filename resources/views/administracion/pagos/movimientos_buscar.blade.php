@extends('layouts.child_module')
@section('data')
	@component('components.labels.title-divisor')    BUSCAR MOVIMIENTOS @endcomponent
	@component('components.forms.searchForm',["variant" => "default"])
		<div class="col-span-2">
			@component('components.labels.label') Movimiento: @endcomponent
			@component('components.inputs.input-text')
				@slot('classEx')
					input-text-search
					name
				@endslot
				@slot('attributeEx')
					name="mov"
					id="input-search"
					placeholder="Ingrese un movimiento"
					value="{{ isset($mov) ? $mov : '' }}"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Rango de fechas: @endcomponent
			@php
			$minDate = isset($mindate) ? $mindate : '';
			$maxDate = isset($maxdate) ? $maxdate : '';
			$inputsRange =
			[
				[
					"input_classEx"		=>	"input-text-date datepicker",
					"input_attributeEx"	=>	"name=\"mindate_request\" step=\"1\" placeholder=\"Desde\" value=\"".$minDate."\"",
				],
				[
					"input_classEx"		=>	"input-text-date datepicker",
					"input_attributeEx"	=>	"name=\"maxdate_request\" step=\"1\" placeholder=\"Hasta\" value=\"".$maxDate."\"",
				]
			];
			@endphp
			@component("components.inputs.range-input",["inputs" => $inputsRange]) @endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Empresa @endcomponent
			@php
				$options = collect();
				foreach(App\Enterprise::orderBy('name','asc')->whereIn('id',Auth::user()->inChargeEnt(102)->pluck('enterprise_id'))->get() as $enterprise)
				{
					$description = strlen($enterprise->name) >= 35 ? substr(strip_tags($enterprise->name),0,35).'...' : $enterprise->name;
					if(isset($enterpriseid) && $enterpriseid == $enterprise->id)
					{
						$options = $options->concat([["value"=>$enterprise->id, "selected"=>"selected", "description"=>$description]]);
					}
					else
					{
						$options = $options->concat([["value"=>$enterprise->id, "description"=>$description]]);
					}
				}
				$attributeEx =  "name=\"enterpriseid\" multiple=\"multiple\"";
				$classEx = "js-enterprise";
			@endphp
			@component('components.inputs.select',["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])  @endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Clasificación del gasto: @endcomponent
			@php
				$options = collect();
				foreach(App\Account::orderNumber()->where('idEnterprise',$enterpriseid)->where('selectable',1)->selectRaw('idAccAcc, CONCAT(account, " - ", description, " (", content, ")") as accountDescription')->get() as $acc)
				{
					$options = $options->concat(
					[
						[
							'value'			=> $account, 
							'description'	=> $acc->accountDescription,
							'selected'		=> ((isset($account) && $account == $acc->idAccAcc) ? 'selected' : '')
						]
					]);
				}
				$attributeEx =  "name=\"account\" multiple=\"multiple\"";
				$classEx = "js-account removeselect";
			@endphp
			@component('components.inputs.select',["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])  @endcomponent
		</div>
		@if(count($movements)>0)
			@slot('export')
				<div class="text-right">
					@Button(["classEx" => "export","variant" => "success", "attributeEx" => "type=\"submit\" formaction=\"".route('payments.movement.export')."\""]) 
						@slot("slot") <span>Exportar a Excel</span><span class="icon-file-excel"></span> @endslot
					@endButton
				</div>
			@endslot
		@endif
	@endcomponent
	@if(count($movements)>0)
		@php
			$body		= [];
			$modelBody	= [];
			$modelHead	= 
			[
				[
					["value" => "Empresa"],
					["value" => "Cuenta"],
					["value" => "Importe"],
					["value" => "Descripción"],
					["value" => "Fecha"],
					["value" => "Acción"]
				]
			];
			foreach($movements as $movement)
			{
				$body = [
					[
						"content" =>
						[
							"label" => $movement->enterprise->name,
						]
					],
					[
						"content" =>
						[
							"label" => $movement->accounts->account. " - " .$movement->accounts->description,
						]
					],
					[
						"content" =>
						[
							"label" => "$".number_format($movement->amount,2),
						]
					],
					[
						"content" =>
						[
							"label" => htmlentities($movement->description),
						]
					],
					[
						"content" =>
						[				
							"label" => Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$movement->movementDate)->format('d-m-Y'),
						]
					],
					[
						"content" =>
						[
							[
								"kind"			=> "components.buttons.button",
								"buttonElement"	=> "a",
								"classEx"		=> "follow-btn",
								"attributeEx"	=> "alt=\"Editar Movimiento\" title=\"Editar Movimiento\" href=\"".route('payments.movement.show',$movement->idmovement)."\"",
								"variant"		=> "success",
								"label"			=> "<span class=\"icon-pencil\"></span>"
							],
							[
								"kind"			=> "components.buttons.button",
								"buttonElement"	=> "a",
								"classEx"		=> "follow-btn",
								"attributeEx"	=> "alt=\"Ver Movimiento\" title=\"Ver Movimiento\" href=\"".route('payments.movement.view',$movement->idmovement)."\"",
								"variant"		=> "secondary",
								"label"			=> "<span class=\"icon-search\"></span>"
							],
							[
								"kind"			=> "components.buttons.button",
								"buttonElement"	=> "a",
								"classEx"		=> "follow-btn movement-delete",
								"attributeEx"	=> "alt=\"Eliminar Movimiento\" title=\"Eliminar Movimiento\" href=\"".route('payments.movement.delete',$movement->idmovement)."\"",
								"variant"		=> "dark-red",
								"label"			=> "<span class=\"icon-bin\"></span>"
							]
						]
					],
				];
				$modelBody[] = $body;
			}
			
		@endphp
		@component("components.tables.table",[
			"modelHead" 			=> $modelHead,
			"modelBody" 			=> $modelBody,
		])
		@endcomponent
		{{ $movements->appends($_GET)->links() }}
	@else
		@component("components.labels.not-found")@endcomponent
	@endif				
@endsection
@section('scripts')

<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
<script src="{{ asset('js/jquery-ui.js') }}"></script>
<script src="{{ asset('js/datepicker.js') }}"></script>
<script type="text/javascript"> 
	$(document).ready(function()
	{
		@php
			$selects = collect([
				[
					"identificator"				=> ".js-enterprise",
					"placeholder"				=> "Seleccione la empresa",
					"maximumSelectionLength"	=> "1"
				],
			]);
		@endphp
		@component("components.scripts.selects",["selects" => $selects]) @endcomponent
		generalSelect({'selector': '.js-account', 'depends': '.js-enterprise', 'model': 10});
		$(function() 
		{
			$( ".datepicker" ).datepicker({ maxDate: 0, dateFormat: "yy-mm-dd" });
		});

		$(document).on('change','.js-enterprise',function()
		{
			$('.js-account').empty();
			
		});
		// .on('change','.js-account',function()
		// {
		// 	$enterprise = $('.js-enterprise option:selected').val();
		// 	$.ajax(
		// 	{
		// 		type	: 'get',
		// 		url		: '{{ url("/administration/purchase/create/account") }}',
		// 		data	: {'enterpriseid':$enterprise},
		// 		success	: function(data)
		// 		{
		// 			$.each(data,function(i, d)
		// 			{
		// 				$('.js-account').append('<option value='+d.idAccAcc+'>'+d.account+' - '+d.description+' ('+d.content+')</option>');
		// 			});
		// 		},
		// 		error : function()
		// 		{
		// 			swal('','Sucedió un error, por favor intente de nuevo.','error');
		// 			$('.js-account').val(null).trigger('change');
		// 		}
		// 	})
		// });
		$(document).on('click','.movement-delete',function(e)
		{
			e.preventDefault();
			url = $(this).attr('href');
			swal({
				title		: "",
				text		: "Confirme que desea eliminar el movimiento",
				icon		: "warning",
				buttons		: ['Cancelar','Eliminar'],
				dangerMode	: true,

			})
			.then((isConfirm) => {
				if (isConfirm)
				{
					swal('Cargando',{
						icon: '{{ asset(getenv('LOADING_IMG')) }}',
						button: false,
						closeOnClickOutside: false,
						closeOnEsc: false
					});
					form = $('<form></form>').attr('action',url).attr('method','post').append('@csrf').append('@method("delete")');
					$(document.body).append(form);
					form.submit();
				}
			});
		});
	});
</script> 
@endsection
