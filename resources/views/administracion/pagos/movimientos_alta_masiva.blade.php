@extends('layouts.child_module')
@section('data')
	@component("components.forms.form", ["attributeEx" => "method=\"POST\" id=\"movements_massive\" action=\"".route('payments.movement-massive.upload')."\"","files" => true])
		@component('components.labels.title-divisor') DATOS GENERALES DE LOS MOVIMIENTOS @endcomponent
		@component('components.containers.container-form')
			<div class="col-span-2">
				@component('components.labels.label') Empresa: @endcomponent
				@php
					$options = collect();
					foreach(App\Enterprise::orderBy('name','asc')->whereIn('id',Auth::user()->inChargeEnt(186)->pluck('enterprise_id'))->get() as $enterprise)
					{
						$description = $enterprise->name;
						$options = $options->concat([["value"=>$enterprise->id, "description"=>$description]]);
					}
					$attributeEx =  "name=\"enterprise\" data-validation=\"required\"";
					$classEx = "custom-select select-enterprise";
				@endphp
				@component('components.inputs.select',["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])  @endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Clasificación del gasto: @endcomponent
				@php
					$options = collect();
					
					$attributeEx =  "name=\"account\" data-validation=\"required\"";
					$classEx = "custom-select select-account";
				@endphp
				@component('components.inputs.select',["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])  @endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Tipo de movimiento: @endcomponent
				@php
				$options = collect(
					[
						['value'=>'Ingreso', 'description'=>'Ingreso'], 
						['value'=>'Devolución', 'description'=>'Devolución'], 
						['value'=>'Rechazos', 'description'=>'Rechazos'], 
						['value'=>'Egreso', 'description'=>'Egreso']
					]
				);
					$attributeEx =  "name=\"type\" data-validation=\"required\"";
					$classEx = "custom-select";
				@endphp
				@component('components.inputs.select',["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])  @endcomponent
			</div>
		@endcomponent
		<div class="col-span-4 items-center mt-10">
			@php
				$buttons = [
					"separator" => 
					[
						[
							"kind" 			=> "components.buttons.button-approval",
							"label"			=> "coma (,)",
							"attributeEx"	=> "value=\",\" name=\"separator\" id=\"separatorComa\""
						],
						[
							"kind"			=> "components.buttons.button-approval",
							"label" 		=> "punto y coma (;)",
							"attributeEx"	=> "value=\";\" name=\"separator\" id=\"separatorPuntoComa\""
						]
					]
				];
			@endphp
			@component('components.documents.select_file_csv', 
			[
				"attributeExInput"	=> "type=\"file\" name=\"csv_file\" id=\"files\"",
				"buttons"			=> $buttons
			])
			@endcomponent
		</div>
		<div class="w-full mt-8 grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mb-6">
			@component('components.buttons.button',["variant"=>"primary"])
				@slot('attributeEx')
					type="submit"
				@endslot
				SUBIR MOVIMIENTOS
			@endcomponent
		</div>
	@endcomponent
@endsection

@section('scripts')
	<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
	<script src="{{ asset('js/jquery-ui.js') }}"></script>
	<script src="{{ asset('js/jquery.numeric.js') }}"></script>
	<script src="{{ asset('js/datepicker.js') }}"></script>
	<script>
		labelVal	= '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="17" viewBox="0 0 20 17"><path d="M10 0l-5.2 4.9h3.3v5.1h3.8v-5.1h3.3l-5.2-4.9zm9.3 11.5l-3.2-2.1h-2l3.4 2.6h-3.5c-.1 0-.2.1-.2.1l-.8 2.3h-6l-.8-2.2c-.1-.1-.1-.2-.2-.2h-3.6l3.4-2.6h-2l-3.2 2.1c-.4.3-.7 1-.6 1.5l.6 3.1c.1.5.7.9 1.2.9h16.3c.6 0 1.1-.4 1.3-.9l.6-3.1c.1-.5-.2-1.2-.7-1.5z"/></svg> <span>Seleccione un archivo&hellip;</span>';
		$(document).ready(function()
		{
			@php
				$selects = collect([
					[
						"identificator"				=> ".select-enterprise",
						"placeholder"				=> "Seleccione la empresa",
						"maximumSelectionLength"	=> "1"
					],
					[
						"identificator"				=> ".custom-select",
						"placeholder"				=> "Seleccione un tipo de movimiento",
						"maximumSelectionLength"	=> "1"
					],
					[
						"identificator"				=> ".js-separator",
						"placeholder"				=> "Seleccione un separador",
						"maximumSelectionLength"	=> "1"
					]
				]);
			@endphp
			@component("components.scripts.selects",["selects" => $selects]) @endcomponent
			generalSelect({'selector': '.select-account', 'depends': '.select-enterprise', 'model': 10});
			$('#separatorComa').prop('checked',true);
			countbody = $('#body .tr').length;
			if (countbody <= 0) 
			{
				$('#table').hide();
			}
			else
			{
				$('#table').show();
			}
			$('.amount',).numeric({ altDecimal: ".", decimalPlaces: 2, negative : false });
			$(function()
			{
				$('.datepicker').datepicker(
				{
					dateFormat : 'dd-mm-yy',
				});
			});
		});
		$(document).on('click','.delete-item',function()
		{
			$(this).parents('.tr').remove();
			countbody = $('#body .tr').length;
			if (countbody <= 0) 
			{
				$('#table').hide();
			}
		})
		.on('change','.select-enterprise',function()
		{
			$('.select-account').empty();			
		})
		.on('submit','#movements_massive',function()
		{
			swal({
				icon: '{{ asset(getenv('LOADING_IMG')) }}',
				button: false,
				timer: 8000,
			});
		})
		.on('change','#csv',function(e)
		{
			path = $('#csv').val();
			extension = (path.substring(path.lastIndexOf("."))).toLowerCase();
			if(extension != ".csv")
			{
				swal('','El tipo de archivo no es válido, favor de verificar.','error');
				return false;
			}

			label		= $(this).next('label');
			fileName	= e.target.value.split( '\\' ).pop();
			if(fileName)
			{
				label.find('span').html(fileName);
			}
			else
			{
				label.html(labelVal);
			}
		});
	</script>
@endsection
