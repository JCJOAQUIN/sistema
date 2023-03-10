@extends('layouts.child_module')
@section('css')
	<style>
		.leaflet-touch .leaflet-bar
		{
			border-radius: 0.625rem !important;
			width: max-content !important;
			background-color: #ffffff !important;
		}
		.leaflet-touch .leaflet-bar a
		{
			width: 2.188rem !important;
			height: 2.188rem !important;
			background-position-x: 0.5rem !important;
			background-position-y: 0.5rem !important;
		}
		.leaflet-touch .leaflet-bar a:last-child 
		{
			border-bottom-right-radius: 0.625rem !important;
			border-bottom-left-radius: 0.625rem !important;
		}
		.leaflet-touch .leaflet-bar a:first-child 
		{
			border-top-right-radius: 0.625rem !important;
			border-top-left-radius: 0.625rem !important;
		}
		.leaflet-control-search .search-button 
		{
			width: 2.188rem !important;
			height: 2.188rem !important;
			border-radius: 0.625rem !important;
			background-position-x: 0.5rem !important;
			background-position-y: 0.5rem !important;
		}
		.leaflet-container .leaflet-control-search
		{
			border-radius: 0.625rem !important;
		}
		.leaflet-control-search .search-input
		{
			width: 25rem !important;
			height: 2.375rem !important;
			padding-left: 0.25rem !important;
			padding-right: 0.25rem !important;
			border: 1px solid #808080 !important;
			--tw-text-opacity: 1 !important;
		    color: rgba(63, 63, 70, var(--tw-text-opacity)) !important;
			font-size: 1.063rem !important;

		}
		.leaflet-control-search .search-cancel
		{
			width: 2.813rem !important;
			height: 2.813rem !important;
			margin: 0.813rem 0.313rem !important;

		}
		@media (max-width: 200px) {
			.leaflet-control-search .search-input {
				max-width: 12.5rem !important;
			}
		}
		.leaflet-control-search.search-exp
		{
			--tw-bg-opacity: 1 !important;
			background-color: rgba(245, 245, 244, var(--tw-bg-opacity)) !important;
			width: 100%;
		}
		.leaflet-touch .leaflet-bar button
		{
			width: 2.188rem !important;
			height: 2.188rem !important;
			border-radius: 0.625rem !important;
			background-position-x: 0.5rem !important;
			background-position-y: 0.5rem !important;
		}
		.leaflet-touch .leaflet-bar button:svg
		{
			width: 2.188rem !important;
			height: 2.188rem !important;
		}
		.search-exp .search-button {
			margin: 0.4rem 0 0 0.5rem;
		}
	</style>
@endsection
@section('data')
	@component("components.forms.form", ["attributeEx" => "action=\"".route('project.store')."\" method=\"POST\" id=\"container-alta\""])
		@component('components.labels.title-divisor') DATOS DE PROYECTO @endcomponent
		@component("components.labels.subtitle") Para agregar un proyecto nuevo es necesario colocar los siguientes campos: @endcomponent
		@component("components.containers.container-form")
			<div class="col-span-2">
				@component("components.labels.label")
					Tipo:
				@endcomponent
				@php
					$optionsT = collect();
					$optionsT = $optionsT->concat([["value" => 1, "description" => "Proyecto"],["value" => 0, "description" => "Contrato"]]);
				@endphp
				@component("components.inputs.select",["options" => $optionsT, "attributeEx" => "name=\"kindProject\" multiple data-validation=\"required\"", "classEx" =>  "laboral-data removeselect"]) @endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label")
					N??mero del proyecto:
				@endcomponent
				@component("components.inputs.input-text")
					@slot('attributeEx')
						type = "text"
						name = "projectNumber"
						placeholder = "Ingrese el n??mero del proyecto" 
						data-validation = "server" 
						data-validation-url = "{{ route('project.validation') }}"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label")
					Clave del proyecto:
				@endcomponent
				@component("components.inputs.input-text")
					@slot('attributeEx')
						type = "text" 
						name = "projectCode"
						placeholder = "Ingrese la clave del proyecto"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label")
					Nombre del proyecto:
				@endcomponent
				@component("components.inputs.input-text")
					@slot('attributeEx')
						type = "text" 
						name = "projectName"
						data-validation = "required" 
						placeholder = "Ingrese el nombre del proyecto"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label")
					Obra:
				@endcomponent
				@component("components.inputs.input-text")
					@slot('attributeEx')
						type = "text" 
						name = "obra"
						placeholder = "Ingrese la obra"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label")
					Concurso No.:
				@endcomponent
				@component("components.inputs.input-text")
					@slot("attributeEx")
						type = "text" 
						name = "contestNo"
						placeholder = "Ingrese el concurso No."
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label")
					Lugar:
				@endcomponent
				@component("components.inputs.input-text")
					@slot('attributeEx')
						type = "text" 
						name = "placeObra"
						placeholder = "Ingrese el lugar"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@php
					$optionsR = collect();
					$optionsR = $optionsR->concat([["value" => 0, "description" => "No"],["value" => 1, "description" => "S??"]]);
				@endphp
				@component("components.labels.label")
					Necesita requisici??n:
				@endcomponent
				@component("components.inputs.select",["options" => $optionsR, "attributeEx" => "name=\"requisition\" multiple data-validation=\"required\"", "classEx" =>  "laboral-data removeselect"]) @endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label")
					Ciudad:
				@endcomponent
				@component("components.inputs.input-text")
					@slot('attributeEx')
						type = "text" 
						name = "city" 
						placeholder = "Ingrese la ciudad"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@php
					$optionsE = collect();
					$optionsE = $optionsE->concat([
						["value" => 1, "description" => "Activo"],["value" => 2, "description" => "Pospuesto"],
						["value" => 3, "description" => "Cancelado"],["value" => 4, "description" => "Finalizado"],
					]);
				@endphp
				@component("components.labels.label")
					Estado:
				@endcomponent
				@component("components.inputs.select",["options" => $optionsE, "attributeEx" => "name=\"status\" multiple data-validation=\"required\"", "classEx" =>  "laboral-data removeselect"]) @endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label")
					Plaza del proyecto:
				@endcomponent
				@component("components.inputs.input-text")
					@slot('attributeEx')
						type = "text" 
						name = "place"
						placeholder = "Ingrese la plaza del proyecto"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label")
					Cliente:
				@endcomponent
				@component("components.inputs.input-text")
					@slot('attributeEx')
						type = "text" 
						name = "client"
						placeholder = "Ingrese el cliente"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label")
					Inicio de obra:
				@endcomponent
				@component("components.inputs.input-text")
					@slot('attributeEx')
						autocomplete = "off" 
						type = "text" 
						id = "startObra" 
						name = "startObra"
						placeholder = "Seleccione el inicio de obra"
						data-validation = "required"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label")
					Fin de obra:
				@endcomponent
				@component("components.inputs.input-text")
					@slot('attributeEx')
						autocomplete = "off" 
						type = "text" 
						id = "endObra" 
						name = "endObra"
						placeholder = "Seleccione el fin de obra"
						data-validation = "required"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label")
					Descripci??n del proyecto:
				@endcomponent
				@component("components.inputs.text-area")
					@slot('attributeEx')
						name = "description" 
						rows = "5" 
						cols = "20"
						placeholder = "Ingrese la descripci??n del proyecto"
					@endslot
				@endcomponent
			</div>
		@endcomponent
		<div class="text-center">
			@component("components.buttons.button", ["variant" => "warning", "attributeEx" => "type=\"button\" data-toggle=\"modal\" data-target=\"#newSubProject\"", "label" => "<span class=\"icon-plus\"></span> Agregar un sub-proyecto"]) @endcomponent
		</div>
		@component('components.labels.title-divisor') LISTA DE SUBPROYECTOS @endcomponent
		@AlwaysVisibleTable([
			"modelHead" 		=> ["Clave", "Nombre", "Acci??n"],
			"modelBody" 		=> [],
			"attributeExBody" 	=> "id=\"body-subproject\"",
			"attributeEx"		=> "id=\"table-show\" style=\"display:none !important;\"",
			"variant"			=> "default"
		])@endAlwaysVisibleTable
		@component('components.labels.title-divisor') UBICACI??N @endcomponent
		<div class="text-center">
			@component("components.buttons.button", ["variant" => "warning", "attributeEx" => "type=\"button\" id=\"mapToggle\"","classEx" => "location-closed", "label" => "<span class=\"icon-plus\"></span> Agregar ubicaci??n"]) @endcomponent
		</div>
		<div class="map-container hidden">
			<div id="beforeMap">
				<div class="w-full">
					@component("components.labels.label")
						Arraste el indicardor hasta la posici??n exacta o d?? clic sobre el mapa para posicionar el punto donde se ubicar?? el proyecto y a continuaci??n ingrese la tolerancia del mismo en metros.
						@slot('classEx')
							p-2
						@endslot
					@endcomponent
				</div>
				@component("components.containers.container-form")
					<div class="col-span-4 md:col-start-2 md:col-span-2 md:col-end-4">
						@component("components.labels.label")
						Distancia de tolerancia en metros:
						@endcomponent
						@component("components.inputs.input-text")
							@slot("attributeEx")
								autocomplete = "off" 
								type = "text" 
								id = "distance" 
								name = "distance"
								placeholder = "Distancia" 
								value = "10"
							@endslot
						@endcomponent
					</div>
				@endcomponent	
			</div>
			<div id="map" class="h-120"></div>
			@component("components.inputs.input-text")
				@slot("attributeEx")
					id   = "latitude" 
					type = "hidden" 
					name = "latitude"
				@endslot
			@endcomponent
			@component("components.inputs.input-text")
				@slot("attributeEx")
					id 	 = "longitude" 
					type = "hidden" 
					name = "longitude"
				@endslot
			@endcomponent
		</div>
		<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mt-8">
			@component("components.buttons.button") 
				@slot("attributeEx")
					type = "submit" 
					name = "enviar"
					id   = "enviar"
				@endslot
				Registrar
			@endcomponent
			@component("components.buttons.button", ["classEx" => "btn-delete-form", "variant" => "reset"]) 
				@slot("attributeEx")
					type = "reset" 
					name = "borra"
				@endslot
				Borrar campos
			@endcomponent
		</div>
	@endcomponent
	@component("components.forms.form", ["attributeEx" =>  "id=\"container-alta-sub\""])
		@component("components.modals.modal", ["variant" => "large"])
			@slot('id')
				newSubProject
			@endslot
			@slot('attributeEx')
				tabindex="-1"
			@endslot
			@slot('modalHeader')
				@component('components.buttons.button')
					@slot('attributeEx')
						type = "button"
						data-dismiss = "modal"
					@endslot
					@slot('classEx')
						close
					@endslot
					<span aria-hidden="true">&times;</span>
				@endcomponent
			@endslot
			@slot('modalBody')
				@component('components.labels.title-divisor') DATOS DE SUBPROYECTO @endcomponent
				@component("components.containers.container-form")
					<div class="col-span-2">
						@component("components.labels.label")
							Tipo:
						@endcomponent
						@php
							$optionsT = collect();
							$optionsT = $optionsT->concat([["value" => 1, "description" => "Proyecto"],["value" => 0, "description" => "Contrato"]]);
						@endphp
						@component("components.inputs.select",["options" => $optionsT, "attributeEx" => "name=\"form_kindProject\" multiple data-validation=\"required\"", "classEx" =>  "laboral-data removeselect"]) @endcomponent
					</div>
					<div class="col-span-2">
						@component("components.labels.label")
							N??mero del proyecto:
						@endcomponent
						@component("components.inputs.input-text")
							@slot('attributeEx')
								type = "text"
								name = "form_projectNumber"
								placeholder = "Ingrese el n??mero del proyecto"	
								data-validation = "server" 
								data-validation-url = "{{ route('project.sub.validation') }}"	
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component("components.labels.label")
							Clave del proyecto:
						@endcomponent
						@component("components.inputs.input-text")
							@slot('attributeEx')
								type = "text" 
								name = "form_projectCode"
								placeholder = "Ingrese la clave del proyecto"
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component("components.labels.label")
							Nombre del proyecto:
						@endcomponent
						@component("components.inputs.input-text")
							@slot('attributeEx')
								type = "text" 
								name = "form_projectName"
								placeholder = "Ingrese el nombre del proyecto"
								data-validation = "required" 
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component("components.labels.label")
							Obra:
						@endcomponent
						@component("components.inputs.input-text")
							@slot('attributeEx')
								type = "text" 
								name = "form_obra"
								placeholder = "Ingrese la obra"
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component("components.labels.label")
							Concurso No.:
						@endcomponent
						@component("components.inputs.input-text")
							@slot("attributeEx")
								type = "text" 
								name = "form_contestNo"
								placeholder = "Ingrese el concurso No."
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component("components.labels.label")
							Lugar:
						@endcomponent
						@component("components.inputs.input-text")
							@slot('attributeEx')
								type = "text" 
								name = "form_placeObra"
								placeholder = "Ingrese el lugar"
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@php
							$optionsR = collect();
							$optionsR = $optionsR->concat([["value" => 0, "description" => "No"],["value" => 1, "description" => "S??"]]);
						@endphp
						@component("components.labels.label")
							Necesita requisici??n:
						@endcomponent
						@component("components.inputs.select",["options" => $optionsR, "attributeEx" => "name=\"form_requisition\" multiple data-validation=\"required\"", "classEx" =>  "laboral-data removeselect"]) @endcomponent
					</div>
					<div class="col-span-2">
						@component("components.labels.label")
							Ciudad:
						@endcomponent
						@component("components.inputs.input-text")
							@slot('attributeEx')
								type = "text" 
								name = "form_city" 
								placeholder = "Ingrese la ciudad"
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@php
							$optionsE = collect();
		
							$optionsE = $optionsE->concat([
								["value" => 1, "description" => "Activo"],
								["value" => 2, "description" => "Pospuesto"],
								["value" => 3, "description" => "Cancelado"],
								["value" => 4, "description" => "Finalizado"],
							]);
						@endphp
						@component("components.labels.label")
							Estado:
						@endcomponent
						@component("components.inputs.select",["options" => $optionsE, "attributeEx" => "name=\"form_status\" multiple data-validation=\"required\"", "classEx" =>  "laboral-data removeselect"]) @endcomponent
					</div>
					<div class="col-span-2">
						@component("components.labels.label")
							Plaza del proyecto:
						@endcomponent
						@component("components.inputs.input-text")
							@slot('attributeEx')
								type = "text" 
								name = "form_place"
								placeholder = "Ingrese la plaza del proyecto"
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component("components.labels.label")
							Cliente:
						@endcomponent
						@component("components.inputs.input-text")
							@slot('attributeEx')
								type = "text" 
								name = "form_client"
								placeholder = "Ingrese el cliente"
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component("components.labels.label")
							Inicio de obra:
						@endcomponent
						@component("components.inputs.input-text")
							@slot('attributeEx')
								autocomplete = "off" 
								type = "text" 
								id = "form_startObra" 
								name = "form_startObra"
								placeholder = "Seleccione el inicio de obra"
								data-validation = "required"
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component("components.labels.label")
							Fin de obra:
						@endcomponent
						@component("components.inputs.input-text")
							@slot('attributeEx')
								autocomplete = "off" 
								type = "text" 
								id = "form_endObra" 
								name = "form_endObra"
								placeholder = "Seleccione el fin de obra"
								data-validation = "required"
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component("components.labels.label")
							Descripci??n del proyecto:
						@endcomponent
						@component("components.inputs.text-area")
							@slot('attributeEx')
								name = "form_description" 
								rows = "5" 
								cols = "20"
								placeholder = "Ingrese la descripci??n del proyecto"
							@endslot
						@endcomponent
					</div>
				@endcomponent
			@endslot
			@slot('modalFooter')
				<div class="mt-4 w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mb-6">
					@component('components.buttons.button',[
						"variant" => "success"
						])
						@slot('attributeEx')
							type = "submit"
							id   = "add_subproject"
						@endslot
						<span class="icon-check"></span> Agregar
					@endcomponent
					@component('components.buttons.button',[
						"variant" => "red"
						])
						@slot('attributeEx')
							type = "button"
							data-dismiss = "modal"
						@endslot
						<span class="icon-x"></span> Cerrar
					@endcomponent
				</div>
			@endslot
		@endcomponent
	@endcomponent
@endsection

@section('scripts')
	<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
	<link rel="stylesheet" type="text/css" href="{{ asset('css/leaflet.css') }}">
	<link rel="stylesheet" href="{{ asset('css/leaflet-search.css') }}">
	<script src="{{ asset('js/jquery-ui.js') }}"></script>
	<script src="{{ asset('js/jquery.numeric.js') }}"></script>
	<script src="{{ asset('js/moment.js') }}"></script>
	<script src="{{ asset('js/datepicker.js') }}"></script>
	<script src="{{ asset('js/leaflet.js') }}"></script>
	<script src="{{ asset('js/leaflet-search.js') }}"></script>
	<script>
		$(document).ready(function() 
		{
			validate();
			validateSub();
			$('input[name="startObra"]').datepicker({ dateFormat:'dd-mm-yy' });
			$('input[name="endObra"]').datepicker({ dateFormat:'dd-mm-yy' });
			$('input[name="form_startObra"]').datepicker({ dateFormat:'dd-mm-yy' });
			$('input[name="form_endObra"]').datepicker({ dateFormat:'dd-mm-yy' });
			@php
				$selects = collect([
					[
						"identificator"          => "[name=\"kindProject\"]", 
						"placeholder"            => "Seleccione el tipo", 
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => "[name=\"requisition\"]", 
						"placeholder"            => "Seleccione la opci??n", 
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => "[name=\"status\"]", 
						"placeholder"            => "Seleccione el estado", 
						"maximumSelectionLength" => "1"
					]
				]);
			@endphp
			@component("components.scripts.selects",["selects" => $selects])@endcomponent
			$(document).on('change','#startObra,#endObra',function ()
			{
				d1    = $('#startObra').val();
				d2    = $('#endObra').val();
				date1 = moment(d1,'DD-MM-YYYY');
				date2 = moment(d2,'DD-MM-YYYY');
				if(!moment(date1).isBefore(date2) && (d1 != "" && d2 != "") )
				{
					swal('', 'Error, la fecha de inicio de obra debe ser menor que la de fin de obra.', 'error')
					$('#startObra').val('');
					$('#endObra').val('');
				}
			})
			.on('change','#form_startObra,#form_endObra',function () 
			{
				d1    = $('#form_startObra').val();
				d2    = $('#form_endObra').val();
				date1 = moment(d1,'DD-MM-YYYY');
				date2 = moment(d2,'DD-MM-YYYY');
				if(!moment(date1).isBefore(date2) && (d1 != "" && d2 != ""))
				{
					swal('', 'Error, la fecha de inicio de obra debe ser menor que la de fin de obra.', 'error');
					$('#form_startObra').val('');	
					$('#form_endObra').val('');
				}
			})
			.on('change','#form_startObra,#form_endObra',function () 
			{
				d1		= $('#form_startObra').val();
				d2		= $('#form_endObra').val();
				date1	= moment(d1,'DD-MM-YYYY');
				date2	= moment(d2,'DD-MM-YYYY');
				if(!moment(date1).isBefore(date2) && (d1 != "" && d2 != ""))
				{
					swal('', 'Error, la fecha de inicio de obra debe ser menor que la de fin de obra.', 'error');
					$('#form_startObra').val('');	
					$('#form_endObra').val('');
				}
			})
			.on('click','[data-target="#newSubProject"]',function()
			{
				@php
					$selectsModal = collect([
						[
							"identificator"          => "[name=\"form_kindProject\"]", 
							"placeholder"            => "Seleccione el tipo", 
							"maximumSelectionLength" => "1"
						],
						[
							"identificator"          => "[name=\"form_requisition\"]", 
							"placeholder"            => "Seleccione la opci??n", 
							"maximumSelectionLength" => "1"
						],
						[
							"identificator"          => "[name=\"form_status\"]", 
							"placeholder"            => "Seleccione el estado", 
							"maximumSelectionLength" => "1"
						]
					]);
				@endphp
				@component("components.scripts.selects",["selects" => $selectsModal])@endcomponent
			})
			.on('click','.deleteSubproject',function()
			{
				that = $(this);
				id = $(this).parents('.tr').find('.id').val();
				$.ajax(
				{
					type   : 'POST',
					url    : "{{ route('project.sub-delete') }}",
					data   : {'id':id},
					success: function(data)
					{
						swal('','Proyecto eliminado exitosamente','success');
						that.parents('.tr').remove();
					},
					error : function(data)
					{
						swal('','Ocurri?? un error, por favor intente de nuevo.','error');
					},
				});
			})
			.on('click','.btn-delete-form',function(e)
			{
				e.preventDefault();
				form = $(this).parents('form');
				subform = $('#container-alta-sub');
				swal({
					title		: "Limpiar formulario",
					text		: "??Confirma que desea limpiar el formulario? \n Esta acci??n descartar?? toda la informaci??n ingresada, incluyendo los datos de los Subproyectos.",
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
						subform[0].reset();
						if($(".map-container").is(":visible"))
						{
							map.remove();
							$(".map-container").hide();
							$('<div id="map" class="h-120"></div>').insertAfter("#beforeMap");
							$("#mapToggle").text('Agregar Ubicaci??n').removeClass('bg-red-600').addClass('bg-orange-400 hover:bg-orange-300');
							$("#latitude").val('');
							$("#longitude").val('');
						}
						
						rows = $('#table-show .tr').length;
						if(rows > 0){
							id = $('#table-show').children('#body-subproject').children('.tr').find('.id').val();
							$.ajax(
							{
								type	: 'POST',
								url		: "{{ route('project.sub-delete') }}",
								data	: {'id':id},
								success : function(data)
								{
									swal('','Proyecto eliminado exitosamente','success');
									$('#body-subproject').html('');
								},
								error : function(data)
								{
									swal('','Ocurri?? un error, por favor intente de nuevo.','error');
								},
							});
						}
					}
					else
					{
						swal.close();
					}
				});
			})
			.on("click","#mapToggle",function()
			{
				if($(".map-container").is(":visible"))
				{
					map.remove();
					$(".map-container").hide();
					$('<div id="map" class="h-120"></div>').insertAfter("#beforeMap");
					$("#mapToggle").text('Agregar Ubicaci??n').removeClass('bg-red-600 hover:bg-red-300').addClass('bg-orange-400 hover:bg-orange-300');
					$("#latitude").val('');
					$("#longitude").val('');
				}
				else
				{
					$(".map-container").show();
					$("#mapToggle").text('').removeClass('bg-orange-400 hover:bg-orange-300').addClass('bg-red-600 hover:bg-red-300').text('Eliminar Ubicaci??n');
					mapInstance();
				}
			})
			.on("input","#distance",function()
			{
				distance = Number($(this).val());
				if(!isNaN(distance))
				{
					if(distance < 0)
					{
						distance = (-1) * distance;
					}
					distance = Math.round(distance);
					$(this).val(distance);
					circle.setRadius(distance);
				}
				else
				{
					$(this).val(10);
					circle.setRadius(10);
				}
			});
		});
		function validate()
		{
			$.validate(
			{
				modules: 'security',
				form   : '#container-alta',
				onError: function($form)
				{
					projectNumber = $('[name="projectNumber"]').val();
					validationNumber(projectNumber);
				}
			});
		}
		function validateSub()
		{
			$.validate(
			{
				modules: 'security',
				form: '#container-alta-sub',
				onError   : function($form)
				{
					projectNumber	= $('[name="form_projectNumber"]').val();
					validationNumber(projectNumber);
				},
				onSuccess : function($form)
				{
					$($form).find('[type="submit"]').prop('disabled',true);
					addSubProject();
					return false;
				}
			});
		}
		function addSubProject()
		{
			form_kindProject      = $('[name="form_kindProject"] option:selected').val();
			form_kindProject_text = $('[name="form_kindProject"] option:selected').text();
			form_projectNumber    = $('[name="form_projectNumber"]').val();
			form_projectCode      = $('[name="form_projectCode"]').val();
			form_projectName      = $('[name="form_projectName"]').val();
			form_obra             = $('[name="form_obra"]').val();
			form_contestNo        = $('[name="form_contestNo"]').val();
			form_placeObra        = $('[name="form_placeObra"]').val();
			form_requisition      = $('[name="form_requisition"] option:selected').val();
			form_requisition_text = $('[name="form_requisition"] option:selected').text();
			form_city             = $('[name="form_city"]').val();
			form_status           = $('[name="form_status"] option:selected').val();
			form_status_text      = $('[name="form_status"] option:selected').text();
			form_place            = $('[name="form_place"]').val();
			form_client           = $('[name="form_client"]').val();
			form_startObra        = $('[name="form_startObra"]').val();
			form_endObra          = $('[name="form_endObra"]').val();
			form_description      = $('[name="form_description"]').val();
			$.ajax(
			{
				type: 'POST',
				url : "{{ route('project.sub-store') }}",
				data: 
				{
					'form_kindProject'  : form_kindProject,
					'form_projectNumber': form_projectNumber,
					'form_projectCode'  : form_projectCode,
					'form_projectName'  : form_projectName,
					'form_obra'         : form_obra,
					'form_contestNo'    : form_contestNo,
					'form_placeObra'    : form_placeObra,
					'form_requisition'  : form_requisition,
					'form_city'         : form_city,
					'form_status'       : form_status,
					'form_place'        : form_place,
					'form_client'       : form_client,
					'form_startObra'    : form_startObra,
					'form_endObra'      : form_endObra,
					'form_description'  : form_description,
				},
				success	: function(data)
				{
					$('#newSubProject').modal('hide');
					$('#container-alta-sub').find('[type="submit"]').prop('disabled',false);
					$('[name="form_projectNumber"],[name="form_projectName"]').removeClass('error');
					@php 
						$modelHead =	["Clave","Nombre","Acci??n"];
						$modelBody = 
						[
							[
								"classEx" => "tr",
								[
									"content" => 
									[
										[
											"kind" 		=> "components.labels.label",
											"classEx" 	=> "Tclave"
										],
										[
											"kind" 			=> "components.inputs.input-text",
											"attributeEx"  	=> "type=\"hidden\" name=\"idSubProject[]\"",
											"classEx"		=> "id"
										]
									]
								],
								[
									"content" => 
									[
										[
											"kind" 		=> "components.labels.label",
											"classEx" 	=> "Tnombre"
										]
									]
								],
								[
									"content" => 
									[
										[
											"kind" 			=> "components.buttons.button",
											"attributeEx" 	=> "type=\"button\"",
											"classEx"		=> "deleteSubproject",
											"label"			=> "<span class=\"icon-x delete-span\"></span>",
											"variant"		=> "red"
										]
									]
								]
							]
						];
						$table = view('components.tables.alwaysVisibleTable',
						[
							"modelHead" => $modelHead,
							"modelBody" => $modelBody,
							"noHead"	=> true,
							"variant"	=> "default"
						])->render();
					@endphp
					table = '{!!preg_replace("/(\r)*(\n)*/", "", $table)!!}';
					subp = $(table);
					subp.find('.Tclave').text(data.clave != null ? data.clave : "---");
					subp.find('.Tnombre').text(data.nombre);
					subp.find('[name="idSubProject[]"]').val(data.id);
					$("#table-show").removeAttr('style');
					$('#body-subproject').append(subp);
					$('[name="form_kindProject"]').val(null).trigger('change');
					$('[name="form_projectNumber"]').val("").removeClass('valid');
					$('[name="form_projectCode"]').val("");
					$('[name="form_projectName"]').val("").removeClass('valid');
					$('[name="form_obra"]').val("");
					$('[name="form_contestNo"]').val("");
					$('[name="form_placeObra"]').val("");
					$('[name="form_requisition"]').val(null).trigger('change');
					$('[name="form_city"]').val("");
					$('[name="form_status"]').val(null).trigger('change');
					$('[name="form_place"]').val("");
					$('[name="form_client"]').val("");
					$('[name="form_startObra"]').val("").removeClass('valid');
					$('[name="form_endObra"]').val("").removeClass('valid');
					$('[name="form_description"]').val("");
					swal('','Registrado exitosamente','success');
				},
				error 	: function(data)
				{
					swal('','Ocurri?? un error, intente de nuevo.','error');
				}
			});
		}
		function validationNumber(projectNumber)
		{
			if(projectNumber != '')
			{
				$.ajax(
				{
					type	: "POST",
					url 	: "{{route('project.validation.number')}}",
					data	:
					{
						'project' : projectNumber
					},
					success: function (data)
					{
						if (data == "true")
						{
							swal("","El n??mero de proyecto: "+projectNumber+" ya est?? registrado en el sistema, favor de ingresar uno diferente","error");
							return false;
						}
						else
						{
							swal('', '{{ Lang::get("messages.form_error") }}', 'error');
						}
					},
					error : function()
					{
						swal('','Sucedi?? un error, por favor intente de nuevo.','error');
					}
				});
			}
			else
			{
				swal('', '{{ Lang::get("messages.form_error") }}', 'error');
			}
		}
		function onMapClick(e)
		{
			let lat = e.latlng.lat.toFixed(6);
			let lng = e.latlng.lng.toFixed(6);
			marker.setLatLng(L.latLng(lat, lng));
			circle.setLatLng(L.latLng(lat, lng));
			$("#latitude").val(lat);
			$("#longitude").val(lng);
		}
		function onMove(e)
		{
			if(e.originalEvent == undefined)
			{
				let lat = e.latlng.lat.toFixed(6);
				let lng = e.latlng.lng.toFixed(6);
				circle.setLatLng(L.latLng(lat, lng));
				$("#latitude").val(lat);
				$("#longitude").val(lng);
			}
		}
		function onMoveEnd()
		{
			let latlng = this.getLatLng();
			let lat = latlng.lat.toFixed(6);
			let lng = latlng.lng.toFixed(6);
			marker.setLatLng(L.latLng(lat, lng));
			circle.setLatLng(L.latLng(lat, lng));
			$("#latitude").val(lat);
			$("#longitude").val(lng);
		}
		function mapInstance()
		{
			let lat = "19.432606";
			let lng = "-99.132772";
			$("#latitude").val(lat);
			$("#longitude").val(lng);
			var map = L.map('map').setView([lat, lng], 19);
			L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
			{
				maxZoom: 19,
				attribution: '?? OpenStreetMap'
			}).addTo(map);
			circle = L.circle([lat, lng],{radius: $("#distance").val()}).addTo(map);
			marker = L.marker([lat, lng],{draggable: true}).addTo(map);
			marker.on('moveend',onMoveEnd);
			marker.on('move',onMove);
			map.on('click', onMapClick);
			map.addControl( new L.Control.Search(
			{
				url: 'https://nominatim.openstreetmap.org/search?format=json&q={s}',
				jsonpParam: 'json_callback',
				propertyName: 'display_name',
				propertyLoc: ['lat','lon'],
				marker: marker,
				autoCollapse: true,
				autoType: false,
				minLength: 2,
				textErr: 'Ubicaci??n no encontrada',
				textCancel: 'Cancelar',
				textPlaceholder: 'Ingrese su busqueda',
			}));
			L.Control.CurrentLocation = L.Control.extend({
				onAdd: function(map)
				{
					container = L.DomUtil.create('div','leaflet-control-current-location leaflet-bar leaflet-control');
					btn = L.DomUtil.create('button','actual-location-button',container);
					btn.setAttribute('type', 'button');
					btn.setAttribute('alt', 'Ubicaci??n actual');
					btn.setAttribute('title', 'Ubicaci??n actual');
					btn.innerHTML = '<svg width="37" height="22" viewBox="0 0 50 48" version="1.1" xmlns="http://www.w3.org/2000/svg" style="fill-rule:evenodd;clip-rule:evenodd;stroke-linejoin:round;stroke-miterlimit:2;"><path d="M24,16C19.58,16 16,19.58 16,24C16,28.42 19.58,32 24,32C28.42,32 32,28.42 32,24C32,19.58 28.42,16 24,16ZM41.88,22C40.96,13.66 34.34,7.04 26,6.12L26,2L22,2L22,6.12C13.66,7.04 7.04,13.66 6.12,22L2,22L2,26L6.12,26C7.04,34.34 13.66,40.96 22,41.88L22,46L26,46L26,41.88C34.34,40.96 40.96,34.34 41.88,26L46,26L46,22L41.88,22ZM24,38C16.27,38 10,31.73 10,24C10,16.27 16.27,10 24,10C31.73,10 38,16.27 38,24C38,31.73 31.73,38 24,38Z" style="fill-rule:nonzero;"/></svg>';
					L.DomEvent.on(btn,'click',function(e)
					{
						L.DomEvent.stopPropagation(e);
						if(navigator.geolocation)
						{
							navigator.geolocation.getCurrentPosition(
								function(pos)
								{
									let lat = pos.coords.latitude.toFixed(6);
									let lng = pos.coords.longitude.toFixed(6);
									map.setView([lat, lng], 19);
									marker.setLatLng(L.latLng(lat, lng));
									circle.setLatLng(L.latLng(lat, lng));
									$("#latitude").val(lat);
									$("#longitude").val(lng);
								},
								function(err)
								{
									if (err.code == err.TIMEOUT)
									{
										swal('Error','Se ha superado el tiempo de espera.','error');
									}
									if (err.code == err.PERMISSION_DENIED)
									{
										swal('Error','El usuario no permiti?? informar su posici??n.','error');
									}
									if (err.code == err.POSITION_UNAVAILABLE)
									{
										swal('Error','El dispositivo no pudo recuperar la posici??n actual, espere un momento e intente de nuevo.','error');
									}
								},
								{
									enableHighAccuracy: true,
									timeout: 5000,
									maximumAge: 0
								}
							);
						}
						else
						{
							swal('Error','Su navegador no soporta la geolocalizaci??n.','error');
						}
					});
					return container;
				}
			});
			L.control.currentlocation = function(opts)
			{
				return new L.Control.CurrentLocation(opts);
			}
			L.control.currentlocation({ position: 'topleft' }).addTo(map);
		}
	</script>
@endsection
