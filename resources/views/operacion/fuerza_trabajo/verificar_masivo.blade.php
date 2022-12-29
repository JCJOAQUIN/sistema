@extends('layouts.child_module')

@section('data')
	@component('components.labels.title-divisor') ALTA MASIVA @endcomponent
	@component('components.labels.not-found', ['variant' => 'note'])
		<li>Por favor verifique que su información se encuentre estructurada como en su archivo CSV.</li>
		<li>Sólo se muestran las primeras 10 líneas.</li>
		<li>Para continuar con el proceso dé clic en el botón «Continuar»</li>
	@endcomponent
	@if($csv[0]['proyecto'] != '')
		@php
			$modelHead = [];
			foreach(array_keys(current($csv)) as $headers)
			{
				$heads = ["value" => $headers];
				$modelHead[] = $heads;
			}
			$modelBody = [];
			foreach($csv as $data)
			{
				$tr = [];
				foreach ($data as $row)
				{
					$td		= [ "content" => [ "label" => $row ]];
					$tr[]	= $td;					
				}
				$modelBody[] = $tr;
			}
		@endphp
		@component("components.tables.table",
			[
				"modelHead" => [$modelHead],
				"modelBody" => $modelBody,
			])
		@endcomponent
	@else
		@component('components.labels.not-found')
			@slot('text')
				Nota: el archivo cargado no tenía ningún registro.
			@endslot
		@endcomponent
	@endif
	@if(count($csv)>10)
		@component('components.labels.not-found')
			@slot('text')
				<li>NOTA: sólo se muestran las primeras 10 líneas del archivo</li>
			@endslot
		@endcomponent
	@endif
	@if($csv[0]['proyecto'] == '')
		@component('components.forms.form',[ "attributeEx" => "method=\"POST\" id=\"employee_massive\" action=\"".route('work-force.cancel')."\"" ])
			<div class="flex justify-center">
				@component('components.buttons.button',[ 'variant' => "red"])
					@slot('attributeEx')
						type="submit"
					@endslot
					Cancelar y volver
				@endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="hidden" name="fileName" value="{{$fileName}}"
					@endslot
				@endcomponent
			</div>
		@endcomponent
	@else
		<div class="w-full mt-4 grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mb-6">
			@component('components.forms.form',[ "attributeEx" => "method=\"POST\" id=\"employee_massive\" action=\"".route('work-force.continue')."\"" ])
				@component('components.buttons.button',[ 'variant' => "secondary"])
					@slot('attributeEx')
						type="submit"
						name="send"
					@endslot
					CONTINUAR
				@endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="hidden" name="fileName" value="{{$fileName}}"
					@endslot
				@endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="hidden" name="delimiter" value="{{$delimiter}}"
					@endslot
				@endcomponent
			@endcomponent
			@component('components.forms.form',[ "attributeEx" => "method=\"POST\" id=\"employee_massive\" action=\"".route('work-force.cancel')."\"" ])
				@component('components.buttons.button',[ 'variant' => "red"])
					@slot('attributeEx')
						type="submit"
					@endslot
					Cancelar y volver
				@endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="hidden" name="fileName" value="{{$fileName}}"
					@endslot
				@endcomponent
			@endcomponent
		</div>		 
	@endif
@endsection