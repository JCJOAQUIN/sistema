@extends('layouts.child_module')
@section('data')
	@component('components.labels.title-divisor') DATOS DE WBS @endcomponent
	@component("components.labels.subtitle") Para editar el WBS es necesario colocar los siguientes campos: @endcomponent
	@component("components.forms.form", ["attributeEx" => "action=\"".route('wbs.update', $wbs->id)."\" method=\"POST\" id=\"container-alta\""])
		@slot("methodEx") PUT @endslot
		@component("components.containers.container-form") 
			@php
				$project = \App\Project::where('idproyect', $wbs->project_id)->first();
			@endphp
			@if($wbs->status == 1)
				<div class="col-span-2">
						@component("components.labels.label", ["label" => "Proyecto al que pertenece:"]) @endcomponent
						@component("components.labels.label", ["label" => htmlentities($project->proyectName)]) @endcomponent
						@component("components.labels.label", ["label" => "Código:"]) @endcomponent
						@component("components.inputs.input-text")
							@slot("attributeEx")
								type = "text" 
								name = "code" 
								placeholder = "Ingrese el código" 
								value = "{{ $wbs->code }}" 
								data-validation = "server" 
								data-validation-url = "{{ route('wbs.code.validation') }}" 
								data-validation-req-params = "{{ json_encode(array('oldCode'=>$wbs->id)) }}"
							@endslot
						@endcomponent
				</div>
				@component("components.inputs.input-text", ["attributeEx" => "type=\"hidden\" name=\"idProject\" value=\"".$wbs->project_id."\""]) @endcomponent
				<div class="col-span-2">
					@component("components.labels.label", ["label" => "Nombre:"]) @endcomponent
					@component("components.inputs.input-text",["attributeEx" => "placeholder=\"Ingrese el nombre\" value=\"".htmlentities($wbs->code_wbs)."\" type=\"text\" name=\"nameWbs\" data-validation=\"custom_wbs_name\""]) @endcomponent   
				</div>
			@else
				<div class="col-span-2">
					@component("components.labels.label", ["label" => "Proyecto al que pertenece"]) @endcomponent
					@component("components.labels.label", ["label" => htmlentities($project->proyectName)]) @endcomponent
					@component("components.labels.label", ["label" => "Código"]) @endcomponent
					@component("components.inputs.input-text",["attributeEx" => "placeholder=\"Ingrese el código\" value=\"".$wbs->code."\" type=\"text\" disabled name=\"code\" data-validation=\"custom_wbs_code, custom_wbs_codeC\""]) @endcomponent
				</div>
				@component("components.inputs.input-text", ["attributeEx" => "type=\"hidden\" name=\"idProject\" value=\"".$wbs->project_id."\""]) @endcomponent
				<div class="col-span-2">
					@component("components.labels.label", ["label" => "Nombre"]) @endcomponent
					@component("components.inputs.input-text",["attributeEx" => "placeholder=\"Ingrese el nombre\" value=\"".htmlentities($wbs->code_wbs)."\" type=\"text\" disabled name=\"nameWbs\" data-validation=\"custom_wbs_name\""]) @endcomponent
				</div>
			@endif 
		@endcomponent
		<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mt-8">
			@if($wbs->status == 1)
				@component("components.buttons.button", ["variant" => "primary", "attributeEx" => "type=\"submit\" name=\"enviar\"", "label" => "Actualizar"]) @endcomponent
			@endif
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
				REGRESAR
			@endcomponent
		</div>
	@endcomponent
@endsection

@section('scripts')
	<script>
		$(document).ready(function()
		{
			$.validate(
			{
				modules		: 'security',
				form        : '#container-alta',
				onError     : function($form)
				{
					swal('', '{{ Lang::get("messages.form_error") }}', 'error');
				}
			});
		});
	</script>
@endsection
