@extends('layouts.child_module')
  
@section('data')
	@component('components.labels.title-divisor')    BUSCAR CFDI CANCELADOS Y PENDIENTES DE CANCELACIÓN @endcomponent
	@component('components.forms.searchForm', ["variant" => "default", "attributeEx" => "id=\"formsearch\""])
		<div class="col-span-2">
			@component('components.labels.label') Folio: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="folio" placeholder="Ingrese el folio" value="{{ isset($folio) ? $folio : '' }}"
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
			@component('components.labels.label') Estatus: @endcomponent
			<div class="border border-gray-400 p-4">
				@component('components.inputs.switch')
					@slot('attributeEx')
						type="checkbox" name="status[]" value="3" id="pendiente" @if(isset($status) && in_array('3', $status)) checked @elseif(!isset($status)) checked @endif
					@endslot
					Pendiente
				@endcomponent
				@component('components.inputs.switch')
					@slot('attributeEx')
						type="checkbox" name="status[]" value="4" id="cancelado" @if(isset($status) && in_array('4', $status)) checked @elseif(!isset($status)) checked @endif
					@endslot
					Cancelado
				@endcomponent
			</div>	
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Tipo: @endcomponent
			<div class="border border-gray-400 p-4">
				@foreach(App\CatTypeBill::all() as $type)
					<div>
						@component('components.inputs.switch')
							@slot('attributeEx')
								type="checkbox" name="kind[]" value="{{$type->typeVoucher}}" id="{{$type->description}}" @if(isset($kind) && in_array($type->typeVoucher, $kind)) checked @elseif(!isset($kind)) checked @endif
							@endslot
							{{$type->description}}
						@endcomponent
					</div>
				@endforeach
			</div>
		</div>
		@if(count($pending) > 0)
			@slot('export')
				<div class="float-right mt-4 text-right">
					@component('components.buttons.button', ["variant" => "success"])
						@slot('classEx')
							export
						@endslot
						@slot('attributeEx')
							type="submit" formaction="{{ route('bill.cancelled.report-consolidated') }}"
						@endslot
						<span>Reporte Consolidado</span> <span class='icon-file-excel'></span>
					@endcomponent
					@component('components.buttons.button', ["variant" => "success"])
						@slot('classEx')
							export
						@endslot
						@slot('attributeEx')
							type="submit" formaction="{{ route('bill.cancelled.report-detailed') }}"
						@endslot
						<span>Reporte Detallado</span> <span class='icon-file-excel'></span> 
					@endcomponent
				</div>
			@endslot
		@endif
	@endcomponent
	@if(count($pending) > 0)
		@php
			$body= [];
			$modelBody = [];
			$modelHead = 
			[
				[
					["value" => "Tipo"],
					["value" => "Folio"],
					["value" => "Serie"],
					["value" => "Emisor"],
					["value" => "Receptor"],
					["value" => "Monto"],
					["value" => "Estatus CFDI"],
					["value" => "Versión"],
					["value" => "Acciones"]
				]
			];
			foreach($pending as $bill)
			{
				$statusBill = '';
				if($bill->status == 4)
				{
					$statusBill = $bill->statusCancelCFDI;	
				}
				else
				{
					$statusBill = '---';
				}
				$body = [
					[
						"content" => 
						[
							"label" => isset($bill->cfdiType->description) ? $bill->cfdiType->description : '---'
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
					],
					[
						"content" => 
						[
							"label" => $statusBill
						]
					],
					[
						"content" => 
						[
							"label" => $bill->version
						]
					],
					[
						"content" =>
						[
							[
								"kind" 			=> "components.buttons.button", 
								"variant" 		=> "secondary", 
								"classEx"		=> "tooltip",
								"label"			=> "<span class=\"icon-search\"></span>", 
								"buttonElement" => "a", "attributeEx" => "href=\"".route('bill.cancelled.view',$bill->idBill)."\""
							]
						]
					]
				];
				switch($bill->status)
				{
					case 0:
						array_push($body[8]['content'],
						[
							
								"kind"          => "components.buttons.button", 
								"variant"		=> "dark",
								"attributeEx" 	=> "title=\"Pendiente de Timbrado\" alt=\"Pendiente de timbrado\" type=\"button\"",
								"classEx"		=> "tooltip",
								"label" 		=> "PT"
							
						]);
					break;
					case 1:
						array_push($body[8]['content'],
						[
							"kind"          => "components.buttons.button", 
							"variant"		=> "dark",
							"attributeEx" 	=> "title=\"Pendiente de conciliación (Timbrado)\" alt=\"Pendiente de conciliación (Timbrado)\" type=\"button\"",
							"classEx"		=> "tooltip",
							"label" 		=> "PC"
						]);
					break;
					case 2:
						array_push($body[8]['content'],
						[
							"kind"          => "components.buttons.button", 
							"variant"		=> "dark",
							"attributeEx" 	=> "title=\"Conciliado (Timbrado)\" alt=\"Conciliado (Timbrado)\" type=\"button\"",
							"classEx"		=> "tooltip",
							"label" 		=> "CT"
						]);
					break;
					case 3:
						array_push($body[8]['content'],
						[
							"kind"          => "components.buttons.button", 
							"variant"		=> "dark",
							"attributeEx" 	=> "title=\"En proceso de cancelación\" alt=\"En proceso de cancelación\" type=\"button\"",
							"classEx"		=> "tooltip",
							"label" 		=> "EPC"
						]);
					break;
					case 4:
						array_push($body[8]['content'],
						[
							"kind"          => "components.buttons.button", 
							"variant"		=> "dark",
							"attributeEx" 	=> "title=\"Cancelado\" alt=\"Cancelado\" type=\"button\"",
							"classEx"		=> "tooltip",
							"label" 		=> "C"
						]);
					break;
					case 5:
						array_push($body[8]['content'],
						[
							"kind"          => "components.buttons.button", 
							"variant"		=> "dark",
							"attributeEx" 	=> "title=\"En proceso de cancelación (temporal)\" alt=\"En proceso de cancelación (temporal)\" type=\"button\"",
							"classEx"		=> "tooltip",
							"label" 		=> "EPCT"
						]);
					break;
					case 6:
						array_push($body[8]['content'],
						[
							"kind"          => "components.buttons.button", 
							"variant"		=> "dark",
							"attributeEx" 	=> "title=\"En cola para timbrado.\" alt=\"En cola para timbrado.\" type=\"button\"",
							"classEx"		=> "tooltip",
							"label" 		=> "ECT"
						]);
					break;
					case 7:
						array_push($body[8]['content'],
						[
							"kind"          => "components.buttons.button", 
							"variant"		=> "red",
							"attributeEx" 	=> "title=\"".$bill->error."\" data-toggle=\"tooltip\" data-placement=\"top\" alt=\"Error al timbrar\" type=\"button\"",
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
	<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
	<script src="{{ asset('js/datepicker.js') }}"></script>
	<script type="text/javascript"> 
		$(document).ready(function()
		{
			$('.tooltip').tooltip();
			$(function() 
			{
				$( ".datepicker" ).datepicker({ maxDate: 0, dateFormat: "dd-mm-yy" });
			});
			@php
				$selects = collect([
					[
						"identificator"          => ".js-enterprise",
						"placeholder"            => "Seleccione una empresa",
						"maximumSelectionLength" => "1"
					]
				]);
			@endphp
			@component('components.scripts.selects', ["selects" => $selects]) @endcomponent
			$(document).on('change','[name="kind[]"]',function()
			{
				if($('[name="kind[]"]:checked').length==0)
				{
					$(this).prop('checked',true);
				}
			})
			.on('change','[name="status[]"]',function()
			{
				if($('[name="status[]"]:checked').length==0)
				{
					$(this).siblings('[name="status[]"]').prop('checked',true);
				}
			});
		});
	</script>
@endsection
