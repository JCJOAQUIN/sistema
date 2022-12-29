@extends('layouts.child_module')
@section('data')
	@if (isset($contractor))
		@component("components.forms.form", ["methodEx" => "PUT", "attributeEx" => "method=\"POST\" action=\"".route("contractor.update",$contractor->id)."\" id=\"container-alta\""])
	@else
		@component("components.forms.form", ["attributeEx" => "method=\"POST\" action=\"".route("contractor.store")."\" id=\"container-alta\""])
	@endif
			@component("components.labels.title-divisor") DATOS DEL CONTRATISTA @endcomponent
			@component("components.labels.subtitle") Para {{ (isset($contractor)) ? "editar el contratista" : "agregar un contratista nuevo" }} es necesario colocar los siguientes campos: @endcomponent
			@component("components.containers.container-form")
				<div class="col-span-2">
					@component("components.labels.label") Nombre del contratista: @endcomponent
					@component("components.inputs.input-text")
						@slot("attributeEx")
							type="text"
							name="contractor_name"
							placeholder="Ingrese el nombre del contratista"
							data-validation="server"
							data-validation-url="{{ route("contractor.validation") }}"
							@if (isset($contractor->deleted_at) && $contractor->deleted_at != "")
								disabled="disabled"
							@endif
							@if (isset($contractor))
								data-validation-req-params="{{ json_encode(array('oldContractor'=>$contractor->id)) }}"
								value="{{$contractor->name}}"
							@endif
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">				
					@component("components.labels.label")
						Contrato
					@endcomponent
					@php
						$options = collect();
						foreach(App\Contract::orderBy('name','asc')->get() as $contract)
						{
							$description = $contract->number. " - " .$contract->name;
							if(isset($contractor) && $contractor->contract_id == $contract->id)
							{
								$options = $options->concat([["value"=>$contract->id, "selected" => "selected", "attributeExOption" => "data-wbs=\"".($contract->wbs->count() > 0 ? "1" : "0")."\"", "description" => $description]]);
							} 
							else
							{
								$options = $options->concat([["value"=>$contract->id, "attributeExOption" => "data-wbs=\"".($contract->wbs->count() > 0 ? "1" : "0")."\"", "description" => $description]]);
							}
						}
						if(isset($contractor->deleted_at) && $contractor->deleted_at != ""){
							$attributeEx = "name=\"contract_id\" data-validation=\"required\" disabled=\"disabled\"";
						}
						else
						{
							$attributeEx = "name=\"contract_id\" data-validation=\"required\"";
						}
						$classEx = "js-contract removeselect";
					@endphp
					@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])@endcomponent
				</div>
				<div class="col-span-2">
					@component("components.labels.label") Estatus: @endcomponent
					@php
						$options = collect();
						$values = ["0" => "En trámite", "1" => "Contrato sin firmar", "2" => "En conciliación", "3" => "Contratado"];
						foreach($values as $key => $item)
						{
							if(isset($contractor->status) && $contractor->status == $key)
							{
								$options = $options->concat([["value" => $key, "description" => $item, "selected" => "selected"]]);
							}
							else
							{
								$options = $options->concat([["value" => $key, "description" => $item]]);
							}
						}
						if(isset($contractor->deleted_at) && $contractor->deleted_at != "")
						{
							$attributeEx = "name=\"contractor_status\" multiple=\"multiple\" data-validation=\"required\" disabled=\"disabled\"";
						}
						else
						{
							$attributeEx = "name=\"contractor_status\" multiple=\"multiple\" data-validation=\"required\"";
						}
						$classEx = "js-status removeselect";
					@endphp
					@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])@endcomponent
				</div>
				<div class="col-span-2">
					<div class="wbs-content @if (!isset($contractor)) hidden @elseif (isset($contractor) && $contractor->contract->wbs->count() == 0) hidden @endif">
						@component("components.labels.label") WBS: @endcomponent
						@php
							$options = collect();
							if(isset($contractor) && $contractor->contract_id != "" && $contractor->wbs_id != "")
							{
								$options = $options->concat([["value" => $contractor->wbs_id, "selected" => "selected", "description" => $contractor->contract->wbs->where('id',$contractor->wbs_id)->first()->code_wbs]]);
							}
							$attributeEx = "name=\"contract_wbs\" multiple=\"multiple\" data-validation=\"required\"".(isset($contractor->deleted_at) && $contractor->deleted_at != "" ? " disabled=\"disabled\"" : "");
							$classEx = "js-wbs removeselect";
						@endphp
						@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])@endcomponent
					</div>
				</div>
			@endcomponent
			<div class="@if (isset($contractor->deleted_at) && $contractor->deleted_at != "") text-center @else w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mb-6 mt-4 @endif">
				@if (!isset($contractor->deleted_at))
					@component("components.buttons.button") 
						@slot("attributeEx")
							type="submit"
							name="send"
						@endslot
						@if (isset($contractor)) 
							ACTUALIZAR
						@else
							REGISTRAR
						@endif
					@endcomponent
					@if (!isset($contractor)) 
						@component("components.buttons.button", ["classEx" => "btn-delete-form", "variant" => "reset"]) 
							@slot("attributeEx")
								type="reset"
								name="borrar"
							@endslot
							BORRAR CAMPOS
						@endcomponent	
					@endif
				@endif
				@if (isset($contractor))
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
				@endif
			</div>
	@endcomponent
@endsection

@section('scripts')
<script type="text/javascript">
	$(document).ready(function()
	{
		validation();
		@php
			$selects = collect([
				[
					"identificator"				=> ".js-status",
					"placeholder"				=> "Seleccione el estatus",
					"language"					=> "es",
					"maximumSelectionLength"	=> "1"
				],
				[
					"identificator"				=> ".js-contract",
					"placeholder"				=> "Seleccione el contrato",
					"language"					=> "es",
					"maximumSelectionLength"	=> "1"
				]
			]);
		@endphp
		@component("components.scripts.selects",["selects" => $selects])@endcomponent
		generalSelect({'selector': '.js-wbs', 'depends': '.js-contract','model': 30});
		$(document).on('change','.js-contract', function()
		{
			selected = $(this).find('option:selected');
			$('.js-wbs').html('');
			if (selected.val() != null)
			{
				if(selected.attr('data-wbs') == "1")
				{
					$('.wbs-content').removeClass('hidden');
					generalSelect({'selector': '.js-wbs', 'depends': '.js-contract','model': 30});
				}
			}
			else
			{
				$('.wbs-content').addClass('hidden');
			}
		})
		.on('click','.btn-delete-form',function(e)
		{
			e.preventDefault();
			form = $('#container-alta');
			swal({
				title		: "Limpiar formulario",
				text		: "¿Confirma que desea limpiar el formulario?",
				icon		: "warning",
				buttons		: ["Cancelar","OK"],
				dangerMode	: true,
			})
			.then((willClean) =>
			{
				if(willClean)
				{
					$('.removeselect').val(null).trigger('change');
					form[0].reset();
				}
				else
				{
					swal.close();
				}
			});
		});
	});
	function validation()
	{
		$.validate(
		{
			form: '#container-alta',
			modules		:	'security',
			onError		:	function($form)
			{
				swal('', '{{ Lang::get("messages.form_error") }}', 'error');
			},
			onSuccess : function($form)
			{
				$('[name="contractor_name"]').removeClass('error');
			}
		});
	}	
</script>
@endsection
