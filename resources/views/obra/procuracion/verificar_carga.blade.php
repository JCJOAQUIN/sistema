@extends('layouts.child_module')
@section('data')
	@component('components.labels.title-divisor') ALTA MASIVA @endcomponent
	@component("components.labels.not-found", ["variant" => "note"])
		@slot("slot")
			<ul>
				<li>Por favor verifique que su información se encuentre estructurada como en su archivo CSV.</li>
				<li>Sólo se muestran las primeras 10 líneas.</li>
				<li>Para continuar con el proceso dé clic en el botón «Continuar»</li>
			</ul>
		@endslot
	@endcomponent 
	@if(count($csv)>0) 
		@php
			$modelBody	 = [];
			$modelHead	 = [];
			$keys 		 = array_keys(current($csv));
			foreach($keys as $key)
			{
				$modelHead[] = ["value" => $key];
			}
			for($showCounter = 0; $showCounter < 1; $showCounter++)
			{
				$modelHead[$showCounter]["classEx"] = "sticky inset-x-0";
			}
			foreach($csv as $row)
			{
				$showCounter = 0;
				$body 		 = 
				[
					[
						"classEx"	  => "sticky inset-x-0",
						"content" =>
						[
						]
					]
				];
				foreach($row as $td)
				{
					if($showCounter < 1)
					{
						$body[$showCounter]["content"] = 
						[
							"kind"  => "components.labels.label",
							"label" => $td
						];
					}
					else
					{
						$body[] = 
						[
							"content" =>
							[
								[
									"kind"  => "components.labels.label",
									"label" => $td
								]
							]
						];
					}
					$showCounter = $showCounter+1;
				}
				$modelBody[] = $body;
			}
		@endphp
		@component('components.tables.table', [
			"modelBody" => $modelBody,
			"modelHead" => [$modelHead]
		])
		@slot('attributeEx')
			id="table"
		@endslot
		@slot('classExBody')
			request-validate
		@endslot
		@slot('attributeExBody')
			id="body-payroll"
		@endslot
		@endcomponent
	@else
		@component("components.labels.not-found", ["variant" => "alert"])
			@slot("slot")
				<ul>
					<li>Nota: el archivo cargado no tenía ningún registro.</li>
				</ul>
			@endslot
		@endcomponent
	@endif
	@if(count($csv)>10)
		@component("components.labels.not-found", ["variant" => "alert"])
			@slot("slot")
				<ul>
					<li>NOTA: sólo se muestran las primeras 10 líneas del archivo</li>
				</ul>
			@endslot
		@endcomponent
	@endif
	<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mb-6">
		@if(count($csv) != 0)
			@component('components.forms.form', 
			[
				"attributeEx" => "method=\"POST\" id=\"employee_massive\" action=\"".route('construction.procurement.upload.continue')."\"", 
				"token"       => "true"
			])
				<input type="hidden" name="fileName" value="{{$fileName}}">
				<input type="hidden" name="delimiter" value="{{$delimiter}}">
				@component("components.buttons.button",["variant" => "primary"])
					@slot('attributeEx') 
						type="submit"
					@endslot
					@slot('classEx') 
						w-48 md:w-auto
					@endslot
					CONTINUAR
				@endcomponent
			@endcomponent
		@endif
		@component('components.forms.form', 
		[
			"attributeEx" => "method=\"POST\" id=\"employee_massive\" action=\"".route('construction.procurement.upload.cancel')."\"", 
			"token"       => "true"
		])
			@component("components.buttons.button",["variant" => "red"])
				@slot('attributeEx') 
					type="submit"
				@endslot
				@slot('classEx') 
					w-48 md:w-auto
				@endslot
				Cancelar y volver
			@endcomponent
			<input type="hidden" name="fileName" value="{{$fileName}}">
		@endcomponent
	</div>
@endsection