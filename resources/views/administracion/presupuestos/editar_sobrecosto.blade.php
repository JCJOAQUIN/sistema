@extends('layouts.child_module')
@section('data')
	@include('configuracion.presupuestos.editar_sobrecosto.generate_inputs_form')
	@php
		$names = [
			'Campos Generales',
			'Datos Obra',
			'Programa',
			'Plantilla',
			'Indirectos Desglosados',
			'Resumen Indirectos',
			'Pers.Técnico',
			'Pers.Técnico$',
			'Financ Horizontal',
			'Utilidad',
			'Cargos Adicionales',
			'Resumen',
			'Documentacion'
		];
		$routes = [
			route('Sobrecosto.create.validate',$budget_id),
			route('Sobrecosto.save.generales',$budget_id),
			route('Sobrecosto.save.datosObra',$budget_id),
			route('Sobrecosto.save.programa',$budget_id),
			route('Sobrecosto.save.plantilla',$budget_id),
			route('Sobrecosto.save.indirectosDesglosados',$budget_id),
			route('Sobrecosto.save.resumenIndirectos',$budget_id),
			route('Sobrecosto.save.persTecnico',$budget_id),
			route('Sobrecosto.save.persTecnicoSalario',$budget_id),
			route('Sobrecosto.save.finanCHorizontal',$budget_id),
			route('Sobrecosto.save.utilidad',$budget_id),
			route('Sobrecosto.save.cargosAdicionales',$budget_id),
			route('Sobrecosto.save.resumen',$budget_id),
			route('Sobrecosto.save.documentacion',$budget_id),
		];
		$Sobrecostos = App\CostOverruns::where('id',$budget_id)->first();
		
	@endphp
	@if ($Sobrecostos->status == 'Subiendo')
		<div class="table-responsive table-striped">
			<div class="md-stepper-horizontal orange">
				@foreach ($names as $key => $name)
				
					<form method="get" action="{{ $routes[$key] }}" id="{{ 'container-alta-'.($key + 1) }}">
					</form>
		
		
					<div 
						id="{{ $key+1 }}"
						onclick="document.forms['container-alta-{{ $key + 1 }}'].submit();"
						class="
							md-step
							{{ ($stepp > ($key+1)) ? "active" : "" }}
							{{ (($key+1) == $stepp) ?  "editable " : "" }}
							">
						<div class="md-step-circle"><span>{{ $key + 1 }}</span></div>
						<div class="md-step-title">{{ $name }}</div>
						<div class="md-step-bar-left"></div>
						<div class="md-step-bar-right"></div>
					</div>
				@endforeach
			</div>
		</div>
		@switch($stepp)
				@case(1)
						@include('configuracion.presupuestos.editar_sobrecosto.generales')
						@break
				@case(2)
						@include('configuracion.presupuestos.editar_sobrecosto.datos_obra')
						@break
				@case(3)
						@include('configuracion.presupuestos.editar_sobrecosto.programa')
						@break
				@case(4)
						@include('configuracion.presupuestos.editar_sobrecosto.plantilla')
						@break
				@case(5)
						@include('configuracion.presupuestos.editar_sobrecosto.indirectos_desglozados')
						@break
				@case(6)
						@include('configuracion.presupuestos.editar_sobrecosto.resumen_indirectos')
						@break
				@case(7)
						@include('configuracion.presupuestos.editar_sobrecosto.pers_tecnico')
						@break
				@case(8)
						@include('configuracion.presupuestos.editar_sobrecosto.pers_tecnico_salario')
						@break
				@case(9)
						@include('configuracion.presupuestos.editar_sobrecosto.finan_c_horizontal')
						@break
				@case(10)
						@include('configuracion.presupuestos.editar_sobrecosto.utilidad')
						@break
				@case(11)
						@include('configuracion.presupuestos.editar_sobrecosto.cargos_adicionales')
						@break
				@case(12)
						@include('configuracion.presupuestos.editar_sobrecosto.resumen')
						@break
				@case(13)
						@include('configuracion.presupuestos.editar_sobrecosto.documentacion')
						@break
				@default
						
		@endswitch
	@else
		@component('components.containers.container-form')
			<div class="col-span-2 text-center">
				@component('components.labels.label')
					Estado:
				@endcomponent
				@component('components.labels.label')
					@slot('classEx')
						font-bold
					@endslot
					@slot('attributeEx')
						id="status"
					@endslot
					{{ $budgetUpload->status }}
				@endcomponent
		</div>
		<div class="col-span-2 text-center mt-12">
				@component('components.labels.label')
					Última actualización:
				@endcomponent
				@component('components.labels.label')
					@slot('classEx')
						font-bold
					@endslot
					@slot('attributeEx')
						id="statusDate"
					@endslot
				@endcomponent
			</div>
		@endcomponent
	@endif
@endsection
@section('scripts')
  <link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
  <script src="{{ asset('js/jquery-ui.js') }}"></script>
	<script src="{{ asset('js/jquery.numeric.js') }}"></script>
	<script src="{{ asset('js/moment.js') }}"></script>
	<script type="text/javascript">
		$(document).ready(function ()
		{			
			statusDate = moment().format('DD-MM-YYYY HH:mm:ss');
			$('#statusDate').html(statusDate);
			getUploadStatus();
			$('.decimal6').numeric({ negative : false, altDecimal: ".", decimalPlaces: 6 });
			$('.decimal5').numeric({ negative : false, altDecimal: ".", decimalPlaces: 5 });
			$('.decimal3').numeric({ negative : false, altDecimal: ".", decimalPlaces: 3 });
			$('.decimal2').numeric({ negative : false, altDecimal: ".", decimalPlaces: 2 });
			$('.number').numeric({ negative : false});
			$('.datepicker').each(function()
			{
				id = $(this).attr('name');
				$('#'+id).datepicker({ dateFormat:'dd-mm-yy' });
			})
			@php
				$selects = collect([
					[
						"identificator"				=> ".js-tipodeanticipo,.js-modelodecalculodelfinanciamiento,.js-interesesaconsiderarenelfinanciamiento,.js-tasaactiva,.js-calculodelcargoadicional,.js-diasaconsiderarenelaño,.js-presentaciondelprogramadepersonaltecnico",
						"placeholder"				=> "Seleccione una opción",
						"language"					=> "es",
						"maximumSelectionLength"	=> "1",
					]
				]);
			@endphp
			@component('components.scripts.selects',["selects" => $selects]) @endcomponent
		});
		function getStatus() {

			search = $('#input-search').val()
			$.ajax(
			{
				type : 'get',
				url  : '{{ url("Sobrecosto.status") }}',
				data : {
					'budgetUpload':{{ $budget_id }},
					},
				success : function(response)
				{

					statusDate = moment().format('DD-MM-YYYY HH:mm:ss')
					$('#statusDate').html(statusDate)
					$('#status').html(response.BudgetUploads.status)

					if(response.BudgetUploads.status != 'Subiendo')
					{
						location.reload(true);
					}
				},
				error : function()
				{
					swal('','Sucedió un error, por favor intente de nuevo.','error');
					$('#statusDate').html('')
					$('#status').html('')
				}
			})
		}
		
		
		function getUploadStatus() {
			status = $('#status').html()
			if(status == 'Subiendo')
			{
			getStatus()
			setTimeout(() => {

				getUploadStatus()
			}, 30000);
			}
		}

	</script>
@endsection
