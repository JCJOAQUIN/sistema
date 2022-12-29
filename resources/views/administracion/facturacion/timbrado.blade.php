@extends('layouts.child_module')

@section('data')
	@component('components.labels.title-divisor') BUSCAR CFDI TIMBRADOS @endcomponent
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
			@component('components.labels.label') Folio de Solicitud: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="folioRequest" placeholder="Ingrese el folio de solicitud" value="{{ isset($folioRequest) ? $folioRequest : '' }}"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Concepto: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="concept" placeholder="Ingrese el concepto" value="{{ isset($concept) ? $concept : '' }}"
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
			@component('components.labels.label') Receptor: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="clientRfc" placeholder="Ingrese el receptor" value="{{ isset($clientRfc) ? $clientRfc : '' }}"
				@endslot
			@endcomponent
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
			@component('components.labels.label') Registro patronal: @endcomponent
			@php
				$optionEmployer = [];
				foreach(App\EmployerRegister::whereIn('enterprise_id',Auth::user()->inChargeEnt($option_id)->pluck('enterprise_id'))->get() as $employerRegister)
				{
					if(isset($employerRegister_id) && in_array($employerRegister->employer_register, $employerRegister_id))
					{
						$optionEmployer[] = ["value" => $employerRegister->employer_register, "description" => $employerRegister->employer_register, "selected" => "selected"];
					}
					else 
					{
						$optionEmployer[] = ["value" => $employerRegister->employer_register, "description" => $employerRegister->employer_register];
					}
				}
			@endphp
			@component('components.inputs.select', ["options" => $optionEmployer])
				@slot('attributeEx')
					title="Registro Patronal"
					name="employerRegister_id[]"
					multiple="multiple"
				@endslot
				@slot('classEx')
					js-employerRegister_id
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Semana del año: @endcomponent
			@php
				$optionYear = [];
				for ($i=1; $i < 53; $i++)
				{
					if(isset($weekOfYear) && $i==$weekOfYear)
					{
						$optionYear[] = ["value" => $i, "description" => "Semana"." ".$i, "selected" => "selected"];
					}
					else 
					{
						$optionYear[] = ["value" => $i, "description" => "Semana"." ".$i];
					}
				}
			@endphp
			@component('components.inputs.select', ["options" => $optionYear])
				@slot('attributeEx')
					name="weekOfYear"
					multiple="multiple"
				@endslot
				@slot('classEx')
					js-weekOfYear
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Periodo: @endcomponent
			@php
				$optionPeriodicity = [];
				foreach(App\CatPeriodicity::orderName()->get() as $per)
				{
					if(isset($periodicity) && in_array($per->c_periodicity, $periodicity))
					{
						$optionPeriodicity[] = ["value" => $per->c_periodicity, "description" => $per->description, "selected" => "selected"];
					}
					else 
					{
						$optionPeriodicity[] = ["value" => $per->c_periodicity, "description" => $per->description]; 
					}
				}					 
			@endphp
			@component('components.inputs.select', ["options" => $optionPeriodicity])
				@slot('attributeEx')
					title="Periodicidad" 
					name="periodicity[]" 
					multiple="multiple"
				@endslot
				@slot('classEx')
					js-periodicity
				@endslot
			@endcomponent
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
							type="submit" formaction="{{ route('bill.stamped.report-consolidated') }}"
						@endslot
						<span>Reporte Consolidado</span> <span class='icon-file-excel'></span> 
					@endcomponent
					@component('components.buttons.button', ["variant" => "success"])
						@slot('classEx')
							export
						@endslot
						@slot('attributeEx')
							type="submit" formaction="{{ route('bill.stamped.report-detailed') }}"
						@endslot
						<span>Reporte Detallado</span> <span class='icon-file-excel'></span> 
					@endcomponent
					@component('components.buttons.button', ["variant" => "secondary"])
						@slot('classEx')
							export
						@endslot
						@slot('attributeEx')
							type="submit" formaction="{{ route('bill.stamped.massive') }}"
						@endslot
						<span>Descargar (ZIP)</span> <i class="fas fa-file-archive"></i>
					@endcomponent
				</div>
			@endslot
		@endif
	@endcomponent
	@if(count($pending) > 0)
		@php
			$body 		= [];
			$modelBody 	= [];
			$modelHead 	= [
				[
					["value" => "Folio"],
					["value" => "Emisor"],
					["value" => "Tipo"],
					["value" => "Folio de solicitud"],
					["value" => "Serie"],
					["value" => "Receptor"],
					["value" => "Monto"],
					["value" => "Versión"],
					["value" => "Acciones"]
				]
			];
			foreach($pending as $bill)
			{    
				$body = [
					[
						"content" =>
						[
							"label" => isset($bill->folio) ? $bill->folio : '---'
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
							"label" => isset($bill->cfdiType->description) ? $bill->cfdiType->description : '---'
						]
					],
					[ 
						"content" =>
						[
							"label" => isset($bill->folioRequest) ? $bill->folioRequest : '---'
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
								"label" 		=> "<span class=\"icon-search\"></span>", 
								"buttonElement" => "a",
								"attributeEx" 	=> "title=\"Ver detalles\" href=\"".route('bill.stamped.view',$bill->idBill)."\""
							]
						]
					]
				];
				if(\Storage::disk('reserved')->exists('/stamped/'.$bill->uuid.'.xml'))
				{						
					array_splice($body[8]['content'], 2, 0, [
						"content" => 
						[
							"kind" 			=> "components.buttons.button", 
							"variant" 		=> "success", 
							"label"			=> "<span class=\"icon-xml\"></span>", 
							"classEx"		=> "tooltip",
							"buttonElement" => "a",
							"attributeEx" 	=> "type=\"button\" alt=\"XML\" title=\"XML\" href=".route('bill.stamped.download.xml',$bill->uuid)."\""
						]
					]);
				}
				if(\Storage::disk('reserved')->exists('/stamped/'.$bill->uuid.'.pdf'))
				{						
					array_splice($body[8]['content'], 3, 0, [
						"content" => 
						[
							"kind" 			=> "components.buttons.button", 
							"variant" 		=> "dark-red",
							"classEx"		=> "tooltip",
							"label" 		=> "PDF", 
							"buttonElement" => "a","attributeEx" => "type=\"button\" alt=\"PDF\" title=\"PDF\" href=".route('bill.stamped.download.pdf',$bill->uuid)."\""
						]
					]);
				}
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
				"modelHead" => $modelHead,
				"modelBody" => $modelBody
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
					],
					[
						"identificator"          => '[name="employerRegister_id[]"]',
						"placeholder"            => "Seleccione el registro patronal"
					],
					[
						"identificator"          => '[name="weekOfYear"]',
						"placeholder"            => "Seleccione la semana del año en curso",
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => '[name="periodicity[]"]',
						"placeholder"            => "Seleccione la periocidad"
					],
					[
						"identificator"          => '[name="project_id[]"]',
						"placeholder"            => "Seleccione un proyecto"
					],
				]);
			@endphp
			@component('components.scripts.selects', ["selects" => $selects]) @endcomponent
			$('.tooltip').tooltip();
			$(function() 
			{
				$( ".datepicker" ).datepicker({ maxDate: 0, dateFormat: "dd-mm-yy" });
			});
			$(document).on('change','[name="kind[]"]',function()
			{
				if($('[name="kind[]"]:checked').length==0)
				{
					$(this).prop('checked',true);
				}
			});
		});
	</script>
@endsection
