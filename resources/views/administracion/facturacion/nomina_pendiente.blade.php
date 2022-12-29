@extends('layouts.child_module')

@section('data')
	@component('components.labels.title-divisor') BUSCAR PENDIENTES DE TIMBRADO @endcomponent
	@component('components.forms.searchForm', ["variant" => "default", "attributeEx" => "id=\"formsearch\""])
		<div class="col-span-2">
			@component('components.labels.label') Empleado: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="employee" placeholder="Ingrese el nombre del empleado" value="{{ isset($employee) ? $employee : '' }}"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Rango de Fechas: @endcomponent
			@php
				$valueMin = isset($mindate) ? $mindate : '';
				$valueMax = isset($maxdate) ? $maxdate : '';

				$inputs = 
				[
					[
						'input_classEx'		=> "datepicker",
						'input_attributeEx'	=> "name=\"mindate\" step=\"1\" placeholder=\"Desde\" value=\"".$valueMin."\""
					],
					[
						'input_classEx'		=> "datepicker",
						'input_attributeEx'	=> "name=\"maxdate\" step=\"1\" placeholder=\"Hasta\" value=\"".$valueMax."\""
					]
				];
			@endphp
			@component('components.inputs.range-input', ["inputs" => $inputs]) @endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Seleccione la empresa: @endcomponent
			@php
				$optionEnterprise = [];
				foreach(App\Enterprise::orderName()->whereIn('id',Auth::user()->inChargeEnt($option_id)->pluck('enterprise_id'))->get() as $enterprise)
				{
					if(isset($enterpriseid) && $enterpriseid == $enterprise->id)
					{
						$optionEnterprise[] = ["value" => $enterprise->id, "description" => strlen($enterprise->name) >= 35 ? substr(strip_tags($enterprise->name),0,35).'...' : $enterprise->name, "selected" => "selected"];
					}
					else
					{
						$optionEnterprise[] = ["value" => $enterprise->id, "description" => strlen($enterprise->name) >= 35 ? substr(strip_tags($enterprise->name),0,35).'...' : $enterprise->name];
					}
				}
			@endphp
			@component('components.inputs.select', ["options" => $optionEnterprise])
				@slot('attributeEx')
					title="Empresa" name="enterpriseid" multiple="multiple"
				@endslot
				@slot('classEx')
					js-enterprise
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Estado: @endcomponent
			<div class="border border-gray-400 p-4">
				<div>
					@component('components.inputs.switch')
						@slot('attributeEx')
							type="checkbox" name="status[]" value="0" id="Pendiente" @if(isset($status) && in_array(0, $status)) checked @elseif(!isset($status)) checked @endif
						@endslot
						Pendiente
					@endcomponent
				</div>
				<div>
					@component('components.inputs.switch')
						@slot('attributeEx')
							type="checkbox" name="status[]" value="6" id="Cola" @if(isset($status) && in_array(6, $status)) checked @elseif(!isset($status)) checked @endif
						@endslot
						En cola
					@endcomponent
				</div>
				<div>
					@component('components.inputs.switch')
						@slot('attributeEx')
							type="checkbox" name="status[]" value="7" id="Error" @if(isset($status) && in_array(7, $status)) checked @elseif(!isset($status)) checked @endif
						@endslot
						Error al timbrar
					@endcomponent
				</div>
			</div>
		</div>
		@if(count($pending) > 0)
			@slot('export')
				<div class="float-right mt-4 text-right">
					@component('components.buttons.button', ["variant" => "success"])
						@slot('classEx')
							select-all
						@endslot
						@slot('attributeEx')
							type="button"
						@endslot
						Seleccionar todos (página actual)
					@endcomponent
					@component('components.buttons.button', ["variant" => "primary"])
						@slot('classEx')
							stamp_queue
						@endslot
						@slot('attributeEx')
							type="button" disabled
						@endslot
						Enviar seleccionados a cola de timbrado
					@endcomponent
					@component('components.buttons.button', ["variant" => "success"])
						@slot('classEx')
							export
						@endslot
						@slot('attributeEx')
							type="submit" formaction="{{ route('bill.nomina.export') }}"
						@endslot
						<span>Exportar a Excel</span> <span class='icon-file-excel'></span> 
					@endcomponent
				</div>
			@endslot
		@endif
	@endcomponent
	@if(count($pending) > 0)
		@php
			$body 		=[];
			$modelBody 	=[];
			$modelHead 	=
			[
				[
					["value" => ""],
					["value" => "Folio" ],
					["value" => "Serie"],
					["value" => "Emisor"],
					["value" => "Receptor"],
					["value" => "Monto"],
					["value" => "Acciones"]
				]
			];
			foreach($pending as $bill)
			{
				$body = [
					[
						"content" =>
						[
							"kind" 				=> "components.inputs.checkbox", 
							"attributeEx"		=> "type=\"checkbox\" name=\"idBill[]\" value=\"$bill->idBill\" id=\"idBill_$bill->idBill\"",
							"classExLabel" 		=> "icon-check",
							"classExContainer"	=> "my-6"
						]	
					],
					[
						"content" =>
						[
							"label" => isset($bill->folio) ? $bill->folio : '---'
						]
					],
					[
						"content" =>
						[
							"label" => isset($bill->serie) ? $bill->serie : '---'
						]
					],
					[
						"content" =>
						[
							"label" => isset($bill->businessName) ? $bill->businessName : '---'
						]
					],
					[
						"content" =>
						[
							"label" => isset($bill->clientBusinessName) ? $bill->clientBusinessName : '---'
						]
					],
					[
						"content" =>
						[
							"label" => isset($bill->total) ? '$ '.number_format($bill->total,2) : '---'
						]
					]
				];
				if($bill->status==0)
				{
					array_push($body, [
						"content" =>
						[
							[
								"kind"			=> "components.buttons.button",
								"variant"		=> "dark-red",
								"buttonElement" => "a",
								"attributeEx"	=> "alt=\"Descargar pre-factura\" title=\"Descargar pre-factura\" href=\"".route('income.prefactura',$bill->idBill)."\"",
								"label"			=> "PDF",
								"classEx"		=> "tooltip"
							],
							[
								"kind"			=> "components.buttons.button",
								"variant"		=> "success",
								"buttonElement" => "a",
								"attributeEx"	=> "title=\"Editar\" href=\"".route('bill.nomina.pending.stamp',$bill->idBill)."\"",
								"label"			=> "<span class=\"icon-pencil\"></span>",
								"classEx"		=> "tooltip"
							]
						]
					]);
				}
				else
				{
					array_push($body, [
						"content" =>
						[
							[
								"kind"			=> "components.buttons.button",
								"variant"		=> "success",
								"buttonElement" => "a",
								"attributeEx"	=> "title=\"Editar\" href=\"".route('bill.nomina.pending.stamp',$bill->idBill)."\"",
								"label"			=> "<span class=\"icon-pencil\"></span>",
								"classEx"		=> "tooltip"
							]
						]
					]);
				}
				switch($bill->status)
				{
					case 0:
						array_push($body[6]['content'],
						[
							"kind"          => "components.buttons.button", 
							"variant"		=> "dark",
							"attributeEx" 	=> "title=\"Pendiente de Timbrado\" alt=\"Pendiente de timbrado\" type=\"button\"",
							"classEx"		=> "tooltip",
							"label" 		=> "PT"
						]);
					break;
					case 1:
						array_push($body[6]['content'],
						[
							"kind"          => "components.buttons.button", 
							"variant"		=> "dark",
							"attributeEx" 	=> "title=\"Pendiente de conciliación (Timbrado)\" alt=\"Pendiente de conciliación (Timbrado)\" type=\"button\"",
							"classEx"		=> "tooltip",
							"label" 		=> "PC"
						]);
					break;
					case 2:
						array_push($body[6]['content'],
						[
							"kind"          => "components.buttons.button", 
							"variant"		=> "dark",
							"attributeEx" 	=> "title=\"Conciliado (Timbrado)\" alt=\"Conciliado (Timbrado)\" type=\"button\"",
							"classEx"		=> "tooltip",
							"label" 		=> "CT"
						]);
					break;
					case 3:
						array_push($body[6]['content'],
						[
							"kind"          => "components.buttons.button", 
							"variant"		=> "dark",
							"attributeEx" 	=> "title=\"En proceso de cancelación\" alt=\"En proceso de cancelación\" type=\"button\"",
							"classEx"		=> "tooltip",
							"label" 		=> "EPC"
						]);
					break;
					case 4:
						array_push($body[6]['content'],
						[
							"kind"          => "components.buttons.button", 
							"variant"		=> "dark",
							"attributeEx" 	=> "title=\"Cancelado\" alt=\"Cancelado\" type=\"button\"",
							"classEx"		=> "tooltip",
							"label" 		=> "C"
						]);
					break;
					case 5:
						array_push($body[6]['content'],
						[
							"kind"          => "components.buttons.button", 
							"variant"		=> "dark",
							"attributeEx" 	=> "title=\"En proceso de cancelación (temporal)\" alt=\"En proceso de cancelación (temporal)\" type=\"button\"",
							"classEx"		=> "tooltip",
							"label" 		=> "EPCT"
						]);
					break;
					case 6:
						array_push($body[6]['content'],
						[
							"kind"          => "components.buttons.button", 
							"variant"		=> "dark",
							"attributeEx" 	=> "title=\"En cola para timbrado.\" alt=\"En cola para timbrado.\" type=\"button\"",
							"classEx"		=> "tooltip",
							"label" 		=> "ECT"
						]);
					break;
					case 7:
						array_push($body[6]['content'],
						[
							"kind"          => "components.buttons.button", 
							"variant"		=> "red",
							"attributeEx" 	=> "title=\"Error al timbrar\" alt=\"Error al timbrar\" type=\"button\"",
							"classEx"		=> "tooltip",
							"label" 		=> "Error"
						]);
					break;
					default;
				}
				$modelBody[] = $body;
			}
		@endphp
		@component('components.tables.table', [
			"modelBody" => $modelBody,
			"modelHead" => $modelHead
		])
		@endcomponent
		{{ $pending->appends($_GET)->links() }}
	@else
		@component('components.labels.not-found') @endcomponent
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
					"identificator"          => ".js-enterprise",
					"placeholder"            => "Seleccione la empresa",
					"maximumSelectionLength" => "1"
				]
			]);
		@endphp
		@component('components.scripts.selects', ["selects" => $selects]) @endcomponent
		$('.tooltip').tooltip();
		$(function() 
		{
			$( ".datepicker" ).datepicker({ maxDate: 0, dateFormat: "dd-mm-yy" });
		});
		$(document).on('click','[name="idBill[]"]',function()
		{
			if($('[name="idBill[]"]:checked').length > 0)
			{
				$('.stamp_queue').prop('disabled',false);
			}
			else
			{
				$('.stamp_queue').prop('disabled',true);
			}
		})
		.on('click','.stamp_queue',function()
		{
			id = [];
			$('[name="idBill[]"]:checked').each(function(i,v)
			{
				id.push($(this).val());
			});
			swal({
				icon 				: '{{ url(getenv('LOADING_IMG')) }}',
				button				: false,
				closeOnEsc			: false,
				closeOnClickOutside	: false,
			});
			$.ajax(
			{
				type	: 'post',
				url		: '{{ route("bill.nomina.add.queue.massive") }}',
				data	: {'id':id},
				success	: function(data)
				{
					window.location.reload(true);
				},
				error : function()
				{
					swal('','Sucedió un error, por favor intente de nuevo.','error');
				}
			});
		})
		.on('click','.select-all',function()
		{
			$('[name="idBill[]"]').prop('checked',true);
			$('.stamp_queue').prop('disabled',false);
		})
		.on('change','[name="status[]"]',function()
		{
			if($('[name="status[]"]:checked').length==0)
			{
				$(this).prop('checked',true);
			}
		});
	});
</script>
@endsection