@extends('layouts.child_module')

@section('data')
	@component('components.labels.title-divisor')    ALTA MASIVA @endcomponent
	@component("components.labels.not-found", ["variant" => "note", "title" => ""])
		@component("components.labels.label") Por favor verifique que su información se encuentre estructurada como en su archivo CSV. @endcomponent
		@component("components.labels.label") Sólo se muestran las primeras 10 líneas. @endcomponent
		@component("components.labels.label") Para continuar con el proceso dé clic en el botón «Continuar» @endcomponent
	@endcomponent
	@php
		foreach(array_keys(current($csv)) as $headers)
		{
			$heads = ["value" => $headers];
			$modelHead[] = $heads;
		}
		$modelHead[0]['classEx'] = "sticky inset-x-0";
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
						"label"	=> $data_row !="" ? $data_row : "---"
					]
				];
				$mainBody[] = $body;
			}
			$mainBody[0]['classEx'] = "sticky inset-x-0";
			$modelBody[] = $mainBody;
		}
	@endphp
	<div class="table-responsive">  
		@if(count($csv)>0) 
			@component("components.tables.table",["modelHead" => [$modelHead], "modelBody" => $modelBody])@endcomponent
		@else
			@component("components.labels.not-found", ["classEx" => "alert-danger", "attributeEx" => "role=\"alert\""]) Nota: el archivo cargado no tenía ningún registro. @endcomponent
		@endif
	</div>
	@if(count($csv)>10)
		@component("components.labels.not-found", ["classEx" => "alert-danger", "attributeEx" => "role=\"alert\""]) NOTA: sólo se muestran las primeras 10 líneas del archivo. @endcomponent
	@endif
	<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mb-6 mt-4">
		@if(count($csv)==0)
			@component("components.forms.form")
				@slot("attributeEx")
					id="employee_massive" action="{{route('employee.massive.cancel')}}" method="POST"	
				@endslot
				@slot("componentsEx")
					@component("components.inputs.input-text")
						@slot("attributeEx")
							type="hidden" name="fileName" value="{{$fileName}}"
						@endslot
					@endcomponent
					@component("components.buttons.button", ["variant" => "reset"])
						@slot("attributeEx")
							type="submit"
						@endslot
						Cancelar y volver
					@endcomponent
				@endslot
			@endcomponent
		@else
			@component("components.forms.form")
				@slot("attributeEx")
					id="employee_massive" action="{{route('employee.massive.continue')}}" method="POST"	
				@endslot
				@slot("componentsEx")
					@component("components.inputs.input-text")
						@slot("attributeEx")
							type="hidden" name="fileName" value="{{$fileName}}"
						@endslot
					@endcomponent
					@component("components.inputs.input-text")
						@slot("attributeEx")
							type="hidden" name="delimiter" value="{{$delimiter}}"
						@endslot
					@endcomponent
					@component("components.buttons.button")
						@slot("attributeEx")
							type="submit"
						@endslot
						@slot('classEx')
							continue-btn
						@endslot
						CONTINUAR
					@endcomponent
				@endslot
			@endcomponent
			@component("components.forms.form")
				@slot("attributeEx")
					id="employee_massive" action="{{route('employee.massive.cancel')}}" method="POST"
				@endslot
				@slot("componentsEx")
					@component("components.inputs.input-text")
						@slot("attributeEx")
							type="hidden" name="fileName" value="{{$fileName}}"
						@endslot
					@endcomponent
					@component("components.buttons.button", ["variant" => "reset"])
						@slot("attributeEx")
							type="submit"
						@endslot
						@slot('classEx')
							cancel-btn
						@endslot
						Cancelar y volver
					@endcomponent
				@endslot
			@endcomponent
		@endif
	</div>
@endsection

@section('scripts')
	<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
	<script src="{{ asset('js/jquery-ui.js') }}"></script>
	<script type="text/javascript"> 
		$(document).ready(function()
		{
			$('.continue-btn').on('click', function()
			{
				$('.cancel-btn').attr('disabled','disabled');
			});
			$('.cancel-btn').on('click', function(e)
			{
				e.preventDefault();
				buttonCancel = $(this);
				swal({
				title		: "",
				text		: "Confirme que desea cancelar el proceso de alta masiva.",
				icon		: "warning",
				buttons		:
				{
					cancel:
					{
						text		: "Abortar",
						value		: null,
						visible		: true,
						closeModal	: true,
					},
					confirm:
					{
						text		: "Continuar",
						value		: true,
						closeModal	: false
					}
				},
				dangerMode	: true,
				})
				.then((a) => {
					if (a)
					{
						$('.continue-btn').attr('disabled','disabled');
						buttonCancel.parent('form').submit();
					}
				});
			});
		});
	</script>
@endsection