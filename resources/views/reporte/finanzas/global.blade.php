@extends('layouts.child_module')
@section('data')
	@component("components.labels.title-divisor") BUSCAR @endcomponent
	@component("components.forms.form",["attributeEx" => "id=\"formsearch\" method=\"post\" action=\"".route('report.global.export')."\"","variant"=>"deafult"])
		@component("components.containers.container-form")
			<div class="col-span-2">
				@component('components.labels.label')Tipo de Solicitud:@endcomponent
				@php
					$options = collect();
					foreach(App\RequestKind::whereIn('idrequestkind',[1,2,3,8,9,11,12,13,14,15,16,17])->orderBy('kind','ASC')->get() as $k) 
					{
						$description = $k->kind;
						if(isset($kind) && in_array($k->idrequestkind, $kind))
						{
							$options = $options->concat([["value"=>$k->idrequestkind, "selected"=>"selected", "description"=>$description]]);
						}
						else
						{
							$options = $options->concat([["value"=>$k->idrequestkind, "description"=>$description]]);
						}
					}

					$attributeEx = "title=\"Tipo de Solicitud\" multiple=\"multiple\" name=\"kind[]\"";
					$classEx     = "js-kind removeselect";
				@endphp
				@component('components.inputs.select', 
					[
						'attributeEx' => $attributeEx, 
						'classEx'     => $classEx, 
						"options"     => $options
					]
				)
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Empresa: @endcomponent
				@php
					$options = collect();
					foreach(App\Enterprise::where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeEnt($option_id)->pluck('enterprise_id'))->orderBy('name','asc')->get() as $e)
					{
						$description = strlen($e->name) >= 35 ? substr(strip_tags($e->name),0,35)."..." : $e->name;
						if(isset($enterprise) && in_array($e->id, $enterprise))
						{
							$options = $options->concat([["value"=>$e->id, "selected"=>"selected", "description"=>$description]]);
						}
						else
						{
							$options = $options->concat([["value"=>$e->id, "description"=>$description]]);
						}
					}
					$attributeEx	= "name=\"idEnterprise[]\" title=\"Empresa\" multiple=\"multiple\"";
					$classEx		= "js-enterprise";
				@endphp
				@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Dirección: @endcomponent
				@php
					$options = collect();
					foreach(App\Area::orderName()->where('status','ACTIVE')->get() as $area)
					{
						$description = $area->name;
						if(isset($direction) && in_array($area->id, $direction))
						{
							$options = $options->concat([["value"=>$area->id, "selected"=>"selected", "description"=>$description]]);
						}
						else
						{
							$options = $options->concat([["value"=>$area->id, "description"=>$description]]);
						}
					}
					$attributeEx	= "name=\"idArea[]\" title=\"Dirección\" multiple=\"multiple\"";
					$classEx		= "js-direction";
				@endphp
				@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Departamento: @endcomponent
				@php
					$options = collect();
					foreach(App\Department::orderName()->where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeDep($option_id)->pluck('departament_id'))->get() as $d)
					{
						$description = $d->name;
						if(isset($department) && in_array($d->id, $department))
						{
							$options = $options->concat([["value"=>$d->id, "selected"=>"selected", "description"=>$description]]);
						}
						else
						{
							$options = $options->concat([["value"=>$d->id, "description"=>$description]]);
						}
					}
					$attributeEx	= "name=\"idDepartment[]\" title=\"Departamento\" multiple=\"multiple\"";
					$classEx		= "js-department";
				@endphp
				@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Estado de Solicitud: @endcomponent
				@php
					$options = collect();
					foreach(App\StatusRequest::orderName()->whereIn('idrequestStatus',[4,5,6,7,10,11,12,13,18])->get() as $s)
					{
						$description = $s->description;
						if (isset($status) && in_array($s->idrequestStatus,$status))
						{
							$options = $options->concat([["value"=>$s->idrequestStatus, "selected"=>"selected", "description"=>$description]]);
						}
						else
						{
							$options = $options->concat([["value"=>$s->idrequestStatus,"description"=>$description]]);
						}
					}
					$attributeEx = "name=\"status[]\" multiple=\"multiple\"";
					$classEx = "js-status";
				@endphp
				@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label')Solicitante:@endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type        = "text" 
						name        = "name" 
						id          = "title" 
						placeholder = "Ingrese un nombre" 
						value       = "{{ isset($name) ? $name : '' }}"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Rango de Elaboración: @endcomponent
				@php
					$mindate	= isset($mindate) ? $mindate : '';
					$maxdate	= isset($maxdate) ? $maxdate : '';
					$inputs		= 
					[
						[
							"input_classEx"		=> "input-text-date datepicker",
							"input_attributeEx"	=> "name=\"mindate\" placeholder=\"Desde\" value=\"".$mindate."\"",
						],
						[
							"input_classEx"		=> "input-text-date datepicker",
							"input_attributeEx"	=> "name=\"maxdate\" placeholder=\"Hasta\" value=\"".$maxdate."\"",
						]
					];
				@endphp
				@component("components.inputs.range-input",["inputs" => $inputs]) @endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Fecha de Revisión: @endcomponent
				@php
					$mindate_review	= isset($mindate_review) ? $mindate_review : '';
					$maxdate_review	= isset($maxdate_review) ? $maxdate_review : '';
					$inputs			= 
					[
						[
							"input_classEx"		=> "input-text-date datepicker",
							"input_attributeEx"	=> "name=\"mindate_review\" placeholder=\"Desde\" value=\"".$mindate_review."\"",
						],
						[
							"input_classEx"		=> "input-text-date datepicker",
							"input_attributeEx"	=> "name=\"maxdate_review\" placeholder=\"Hasta\" value=\"".$maxdate_review."\"",
						]
					];
				@endphp
				@component("components.inputs.range-input",["inputs" => $inputs]) @endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Fecha de Autorización: @endcomponent
				@php
					$mindate_authorize	= isset($mindate_authorize) ? $mindate_authorize : '';
					$maxdate_authorize	= isset($maxdate_authorize) ? $maxdate_authorize : '';
					$inputs				= 
					[
						[
							"input_classEx"		=> "input-text-date datepicker",
							"input_attributeEx"	=> "name=\"mindate_authorize\" placeholder=\"Desde\" value=\"".$mindate_authorize."\"",
						],
						[
							"input_classEx"		=> "input-text-date datepicker",
							"input_attributeEx"	=> "name=\"maxdate_authorize\" placeholder=\"Hasta\" value=\"".$maxdate_authorize."\"",
						]
					];
				@endphp
				@component("components.inputs.range-input",["inputs" => $inputs]) @endcomponent
			</div>
			<div class="col-span-2 md:col-span-4 space-x-2 text-center md:text-left">
				@component("components.buttons.button",['variant' => 'secondary']) EXPORTAR @endcomponent
			</div>
		@endcomponent
	@endcomponent
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
					"identificator"			=> ".js-enterprise",
					"placeholder"			=> "Seleccione una empresa",
					"languaje"				=> "es",
				],
				[
					"identificator"			=> ".js-direction",
					"placeholder"			=> "Seleccione una dirección",
					"languaje"				=> "es",

				],
				[
					"identificator"			=> ".js-department",
					"placeholder"			=> "Seleccione un departamento",
					"languaje"				=> "es",

				],
				[
					"identificator"			=> ".js-kind",
					"placeholder"			=> "Seleccione un tipo de solicitud",
					"languaje"				=> "es",

				],
				[
					"identificator"			=> ".js-status",
					"placeholder"			=> "Seleccione un estado de solicitud",
					"languaje"				=> "es",
				],
				[
					"identificator"			=> ".js-wbs",
					"placeholder"			=> "Seleccione un WBS",
					"languaje"				=> "es",
					"maximumSelectionLength" => "1",
				]
			]);
		@endphp
		@component("components.scripts.selects",["selects" => $selects]) @endcomponent
		$('.export_button').on('click', function()
		{
			value_mindate			= $('#mindate').val();
			value_mindate_review	= $('#mindate_review').val();
			value_mindate_authorize	= $('#mindate_authorize').val();
			value_maxdate			= $('#maxdate').val();
			value_maxdate_review	= $('#maxdate_review').val();
			value_maxdate_authorize	= $('#maxdate_authorize').val();
			
			if(value_maxdate == '' && value_maxdate_review == '' && value_maxdate_authorize == '')
			{
				swal('', 'Ingrese al menos una Fecha de Elaboración, de Revisión o Autorización', 'error');
				return false;
			}
		});
	});
	
	@if(isset($alert)) 
		{!! $alert !!} 
	@endif 
</script> 
@endsection


