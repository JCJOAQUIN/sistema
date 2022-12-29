@extends('layouts.child_module')

@section('data')
	@section('css')
		<style>
		.md-stepper-horizontal {
		display:table;
		width:100%;
		margin:0 auto;
		background-color:#FFFFFF;
		box-shadow: 0 3px 8px -6px rgba(0,0,0,.50);
		overflow-x: visible;
		cursor: pointer;
	}
	.md-stepper-horizontal .md-step {
		display:table-cell;
		position:relative;
		padding:24px;
	}
	.md-stepper-horizontal .md-step:hover,
	.md-stepper-horizontal .md-step:active {
		background-color:rgba(0,0,0,0.04);
	}
	.md-stepper-horizontal .md-step:active {
		background-color:rgba(0,0,0,0.02);
	}
	.md-stepper-horizontal .md-step:first-child:active {
		border-top-left-radius: 0;
		border-bottom-left-radius: 0;
	}
	.md-stepper-horizontal .md-step:last-child:active {
		border-top-right-radius: 0;
		border-bottom-right-radius: 0;
	}
	.md-stepper-horizontal .md-step:hover .md-step-circle {
		background-color:#757575;
	}
	.md-stepper-horizontal .md-step:first-child .md-step-bar-left,
	.md-stepper-horizontal .md-step:last-child .md-step-bar-right {
		display:none;
	}
	.md-stepper-horizontal .md-step .md-step-circle {
		width:30px;
		height:30px;
		margin:0 auto;
		background-color:#999999;
		border-radius: 50%;
		text-align: center;
		line-height:30px;
		font-size: 16px;
		font-weight: 600;
		color:#FFFFFF;
	}
	.md-stepper-horizontal.green .md-step.active .md-step-circle {
		background-color:#00AE4D;
	}
	.md-stepper-horizontal.orange .md-step.active .md-step-circle {
		background-color:#F96302;
	}
	.md-stepper-horizontal .md-step.active .md-step-circle {
		background-color: rgb(33,150,243);
	}
	.md-stepper-horizontal .md-step.done .md-step-circle:before {
		font-family: 'icomoon' !important;
		font-weight:100;
		content: "\ea10";
	}
	.md-stepper-horizontal .md-step.done .md-step-circle *,
	.md-stepper-horizontal .md-step.editable .md-step-circle * {
		display:none;
	}

	.md-stepper-horizontal .md-step.editable .md-step-circle:before {
		font-family: 'icomoon' !important;
		font-weight:100;
		content: "\e906";
	}
	.md-stepper-horizontal .md-step .md-step-title {
		margin-top:16px;
		font-size:16px;
		font-weight:600;
	}
	.md-stepper-horizontal .md-step .md-step-title,
	.md-stepper-horizontal .md-step .md-step-optional {
		text-align: center;
		color:rgba(0,0,0,.26);
	}
	.md-stepper-horizontal .md-step.active .md-step-title {
		font-weight: 600;
		color:rgba(0,0,0,.87);
	}
	.md-stepper-horizontal .md-step.active.done .md-step-title,
	.md-stepper-horizontal .md-step.active.editable .md-step-title {
		font-weight:600;
	}
	.md-stepper-horizontal .md-step .md-step-optional {
		font-size:12px;
	}
	.md-stepper-horizontal .md-step.active .md-step-optional {
		color:rgba(0,0,0,.54);
	}
	.md-stepper-horizontal .md-step .md-step-bar-left,
	.md-stepper-horizontal .md-step .md-step-bar-right {
		position:absolute;
		top:36px;
		height:1px;
		border-top:1px solid #DDDDDD;
	}
	.md-stepper-horizontal .md-step .md-step-bar-right {
		right:0;
		left:50%;
		margin-left:20px;
	}
	.md-stepper-horizontal .md-step .md-step-bar-left {
		left:0;
		right:50%;
		margin-right:20px;
	}
		.margin_top {
			margin-top: 20px;
		}
		</style>
	@endsection
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
	@if ($Sobrecostos->status != 'Subiendo')
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
		<center>
			<div class="container-search">
				<br>
				<label class="label-form">Estatus</label>

				<center>
					<span>
						<b>
							<label id="status">{{ $Sobrecostos->status }}</label>
						</b>
					</span>
				</center>
				<br><br>
			</div>
			<div class="container-search">
				<br>
				<label class="label-form">Ultima actualización</label>

				<center>
					<span>
						<b>
							<label id="statusDate"></label>
						</b>
					</span>
				</center>
				<br><br>
			</div>
		</center>
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
			$('.select').each(function()
			{
				name = $(this).attr('name');
				title = $(this).data('title');
				$('.js-'+name).select2(
				{
					language: "es",
					maximumSelectionLength: 1,
				})
				.on("change",function(e)
				{
					if($(this).val().length>1)
					{
						$(this).val($(this).val().slice(0,1)).trigger('change');
					}
				});
			});
		});
		function getStatus()
		{
			search = $('#input-search').val();
			$.ajax(
			{
				type : 'get',
				url  : '{{ url("/administration/budgets/create/sobrecosto/status") }}',
				data : {
					'budgetUpload':{{ $budget_id }},
				},
				success : function(response)
				{
					statusDate = moment().format('DD-MM-YYYY HH:mm:ss');
					$('#statusDate').html(statusDate);
					$('#status').html(response.BudgetUploads.status);
					if(response.BudgetUploads.status != 'Subiendo')
					{
						location.reload(true);
					}
				},
				error : function()
				{
					swal('','Sucedió un error, por favor intente de nuevo.','error');
					$('#statusDate').html('');
					$('#status').html('');
				}
			});
		}
		function getUploadStatus()
		{
			status = $('#status').html();
			if(status == 'Subiendo')
			{
				getStatus();
				setTimeout(() => {
					getUploadStatus()
				}, 30000);
			}
		}
	</script>
@endsection
