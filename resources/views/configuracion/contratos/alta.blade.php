@extends('layouts.child_module')
@section('data')
	@if (isset($contract))
		@component("components.forms.form",["methodEx" => "PUT", "attributeEx" => "method=\"POST\" action=\"".route("contract.update", $contract->id)."\" id=\"container-alta\""])
	@else
		@component("components.forms.form",["attributeEx" => "method=\"POST\" action=\"".route("contract.store")."\" id=\"container-alta\""])
	@endif
		@component("components.labels.title-divisor") DATOS DEL CONTRATO @endcomponent
		@component("components.labels.subtitle") Para {{ (isset($contract)) ? "editar el contrato" : "agregar un contrato nuevo" }} es necesario colocar los siguientes campos: @endcomponent
		@component("components.containers.container-form")
			<div class="col-span-2">
				@component("components.labels.label")
					Proyecto
				@endcomponent
				@php
					$options = collect();
					if(isset($contract) && $contract->project_id)
					{
						$options = $options->concat([["value" => $contract->project_id, "selected" => "selected", "description" => $contract->project->proyectName]]);
					}
					$attributeEx = "name=\"contract_project\" multiple=\"multiple\" data-validation=\"required\"";
					$classEx = "js-projects removeselect";
				@endphp
				@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])@endcomponent
			</div>
			<div class="col-span-2 wbs-content @if(isset($contract) && $contract->project->codeWBS()->exists()) block @else hidden @endif">	
				@component("components.labels.label")
					WBS
				@endcomponent
				@php
					$options = collect();
					if(isset($contract))
					{
						foreach($contract->wbs as $code)
						{
							$options = $options->concat([["value" => $code->id, "selected" => "selected", "description" => $code->code_wbs]]);
						}
					}
					$attributeEx = "name=\"contract_wbs[]\" multiple=\"multiple\" data-validation=\"required\"";
					$classEx = "js-code_wbs removeselect";
				@endphp
				@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label")
					Número de contrato
				@endcomponent
				@component("components.inputs.input-text")
					@slot("classEx")
						contractNumber remove
					@endslot
					@slot("attributeEx")
						type="text"
						name="contract_number"
						placeholder="Ingrese el número de contrato"
						data-validation="server" 
						data-validation-url="{{ route("contract.validation") }}" 
						@if (isset($contract)) 
							data-validation-req-params="{{ json_encode(array('oldContract'=>$contract->number, 'project_id'=>$contract->project_id)) }}" 
							value="{{$contract->number}}" 
						@endif
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Nombre del contrato @endcomponent
				@component("components.inputs.input-text", ["classEx" => "contractName remove"])
					@slot("attributeEx")
						type="text" 
						name="contract_name" 
						placeholder="Ingrese el nombre del contrato" 
						data-validation="required" 
						@if (isset($contract))
							value="{{ $contract->name}}"
						@endif
					@endslot
				@endcomponent
			</div>
		@endcomponent
		<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mb-6 mt-4">
			@component("components.buttons.button")
				@slot("attributeEx")
					type="submit"
					name="send"
				@endslot
				@if (isset($contract)) ACTUALIZAR @else REGISTRAR @endif
			@endcomponent
			@if(isset($contract)) 
				@component('components.buttons.button', ["variant"=>"reset", "buttonElement"=>"a"])
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
					Regresar
				@endcomponent
			@else
				@component("components.buttons.button", ["classEx" => "btn-delete-form", "variant" => "reset"]) 
					@slot("attributeEx")
						type="reset"
						name="borrar"
					@endslot
					BORRAR CAMPOS
				@endcomponent
			@endif
		</div>
	@endcomponent
@endsection
@section('scripts')
	<script src="{{ asset('js/jquery.numeric.js') }}"></script>
	<script type="text/javascript">
		$(document).ready(function()
		{	
			validation();
			@php
				$selects = collect([
					[
						"identificator"				=> ".js-contract",
						"placeholder"				=> "Seleccione el contrato",
						"language"					=> "es",
						"maximumSelectionLength"	=> "1"
					],
					[
						"identificator"				=> ".js-status",
						"placeholder"				=> "Seleccione el estatus",
						"language"					=> "es",
						"maximumSelectionLength"	=> "1"
					]
				]);
			@endphp
			@component("components.scripts.selects",["selects" => $selects])@endcomponent
			generalSelect({'selector': '.js-projects', 'model': 14});
			generalSelect({'selector': '.js-code_wbs', 'depends': '.js-projects','model': 22});
			$(document).on('change','.js-projects',function()
			{
				$('.js-code_wbs').html('');
				id = $(this).val();
				if (id != undefined && id != "")
				{
					oldContract							= "{{ (isset($contract) ? $contract->number : '') }}";
					contractValidation					= new Object;
					contractValidation['project_id']	= id;
					if(oldContract != '')
					{
						contractValidation['oldContract'] = oldContract;
					}
					$('[name="contract_number"]').attr('data-validation-req-params',JSON.stringify(contractValidation));
					$.each(generalSelectProject,function(i,v)
					{
						if(id == v.id)
						{
							if(v.flagWBS != null)
							{
								$('.wbs-content').removeClass('hidden').addClass('block');
								generalSelect({'selector': '.js-code_wbs', 'depends': '.js-projects','model': 22});
							}
							else
							{
								$('.js-code_wbs').html('');
								$('.wbs-content').removeClass('block').addClass('hidden');
							}
						}
					});
				}
				else
				{
					$('.js-code_wbs').html('');
					$('.wbs-content').removeClass('block').addClass('hidden');				
				}
			})
		});
		function validation()
		{
			$.validate(
			{
				form	:	'#container-alta',
				modules	:	'security',
				onError :	function($form)
				{
					swal('', '{{ Lang::get("messages.form_error") }}', 'error');
				},
				onSuccess : function($form)
				{
					$('.contractNumber').removeClass('error');
					$('.contractName').removeClass('error');
				}
			});
		}
	</script>
@endsection