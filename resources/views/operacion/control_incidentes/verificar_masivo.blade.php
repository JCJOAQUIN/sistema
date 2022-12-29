@extends('layouts.child_module')
@section('data')
	@component("components.labels.title-divisor") ALTA MASIVA @endcomponent
	@component("components.labels.not-found", ["variant" => "note", "title" => ""])
		@component("components.labels.label") Por favor verifique que su información se encuentre estructurada como en su archivo CSV. @endcomponent
		@component("components.labels.label") Solo se muestran las primeras 10 líneas. @endcomponent
		@component("components.labels.label") Para continuar con el proceso dé clic en el botón «Continuar» @endcomponent
	@endcomponent
	@if(count($csv) > 0)
		@php
			foreach(array_keys(current($csv)) as $headers)
			{
				$heads = ["value" => $headers];
				$modelHead[] = $heads;
			}
			$count = 0;
			$modelBody = [];
			foreach ($csv as $row)
			{
				$mainBody	= [];
				foreach($row as $key => $data_row)
				{
					$body = 
					[
						"content" =>
						[
							"kind"	=> "components.labels.label",
							"label"	=> $data_row !="" ? $data_row : "---"
						]
					];
					$mainBody[] = $body;
				}
				if($count == 10)
				{
					break;					
				}
				$count++;
				$modelBody[] = $mainBody;
			}
		@endphp
		@component("components.tables.table",["modelHead" => [$modelHead], "modelBody" => $modelBody])@endcomponent
	@else
		@component("components.labels.not-found", ["text" => "El archivo cargado no tenía ningún registro."]) @endcomponent
	@endif
	@if(count($csv) > 10)
		@component("components.labels.not-found", ["variant" => "note"]) 
			@component("components.labels.label") Solo se muestran las primeras 10 líneas del archivo. @endcomponent
		@endcomponent
	@endif
	<div class="@if(count($csv) > 0) w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mt-8 @else flex justify-center @endif">
		@if(count($csv) > 0)
			@component("components.forms.form", ["attributeEx" => "action=\"".route('incident-control.massive.continue')."\" method=\"POST\" id=\"incident_massive\""])
				@component("components.inputs.input-text", ["attributeEx" => "type=\"hidden\" name=\"fileName\" value=\"$fileName\""]) @endcomponent
				@component("components.inputs.input-text", ["attributeEx" => "type=\"hidden\" name=\"delimiter\" value=\"$delimiter\""]) @endcomponent
				@component("components.buttons.button", ["variant" => "primary", "classEx" => "btn-submit"])
					@slot("attributeEx")
						type="submit"
					@endslot
					CONTINUAR
				@endcomponent
			@endcomponent	
		@endif
		@component("components.forms.form", ["attributeEx" => "action=\"".route('incident-control.massive.cancel')."\" method=\"POST\" id=\"incident_massive\""])
			@component("components.inputs.input-text", ["attributeEx" => "type=\"hidden\" name=\"fileName\" value=\"$fileName\""]) @endcomponent
			@component("components.buttons.button", ["variant" => "reset", "classEx" => "btn-submit"])
				@slot("attributeEx")
					type="submit"
				@endslot
				CANCELAR Y VOLVER
			@endcomponent
		@endcomponent
	</div>
@endsection
@section('scripts')
	<script type="text/javascript">
		$(document).ready(function()
		{
			$(document).on('click','.btn-submit',function(e)
			{
				swal('Cargando',{
					icon: '{{ asset(getenv("LOADING_IMG")) }}',
					button: false,
					closeOnClickOutside: false,
					closeOnEsc: false
				});
			});	
		});
	</script>
@endsection