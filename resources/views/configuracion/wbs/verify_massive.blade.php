@extends('layouts.child_module')

@section('data')
	@component('components.labels.title-divisor') ALTA MASIVA @endcomponent

	@component("components.labels.not-found", ["variant" => "note", "title" => ""])
		@component("components.labels.label")
			Por favor verifique que su información se encuentre estructurada como en su archivo CSV.
		@endcomponent		

		@component("components.labels.label")
			Sólo se muestran las primeras 10 líneas.
		@endcomponent
		
		@component("components.labels.label")
			Para continuar con el proceso dé clic en el botón «Continuar»
		@endcomponent
	@endcomponent

	@php
		foreach(array_keys(current($csv)) as $headers)
		{
			$heads = [["value" => $headers]];
			$modelHead[0] = $heads;
		}
		$modelBody	= [];
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
						"label"	=> $data_row
					]
				];
				$mainBody[] = $body;
			}
			$mainBody[0]['show'] = "true";
			$modelBody[] = $mainBody;
		}
	@endphp

	<div class="table-responsive">  
		@if(count($csv)>0)
			@component("components.tables.table",[
				"modelHead" => $modelHead,
				"modelBody" => $modelBody,
				"themeBody" => "striped"
			])
			@endcomponent
		@else
			@component("components.labels.not-found", ["classEx" => "alert-danger", "attributeEx" => "role=\"alert\""]) Nota: el archivo cargado no tenía ningún registro. @endcomponent
		@endif
	</div>
	@if(count($csv)>10)
		@component("components.labels.not-found", ["classEx" => "alert-danger", "attributeEx" => "role=\"alert\""]) NOTA: sólo se muestran las primeras 10 líneas del archivo. @endcomponent
	@endif

	<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mt-8">
		@if(count($csv)!=0)
			@component("components.forms.form", ["attributeEx" => "action=\"".route('wbs.massive.continue')."\" method=\"POST\" id=\"wbs_massive\""])
				@slot("componentsEx")
					@component("components.inputs.input-text")
						@slot("attributeEx")
							type  = "hidden" 
							name  = "fileName" 
							value = "{{$fileName}}"
						@endslot
					@endcomponent
					@component("components.inputs.input-text")
						@slot("attributeEx")
							type  = "hidden" 
							name  = "delimiter" 
							value = "{{$delimiter}}"
						@endslot
					@endcomponent
					@component("components.buttons.button")
						@slot("attributeEx")
							type = "submit"
						@endslot
						CONTINUAR
					@endcomponent
				@endslot
			@endcomponent
		@endif

		@component("components.forms.form", ["attributeEx" => "action=\"".route('wbs.massive.cancel')."\" method=\"POST\" id=\"wbs_massive\""])
			@slot("componentsEx")
				@component("components.inputs.input-text")
					@slot("attributeEx")
						type  = "hidden" 
						name  = "fileName" 
						value = "{{$fileName}}"
					@endslot
				@endcomponent
				@component("components.buttons.button", ["variant" => "red"])
					@slot("attributeEx")
						type = "submit"
					@endslot
					CANCELAR
				@endcomponent
			@endslot
		@endcomponent
	</div>
@endsection
