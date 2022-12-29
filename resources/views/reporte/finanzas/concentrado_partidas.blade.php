@extends('layouts.child_module')
@section('css')
	<style type="text/css">
		.all-select
		{
			display	: block;
			margin	: 0 0 0 auto;
		}
		.all-select.select:before
		{
			content: 'Seleccionar';
		}
		.all-select:before
		{
			content: 'Deseleccionar';
		}
		.group
		{
			border			: 3px solid #17323f;
			border-radius	: 10px;
			padding			: 10px;
			background		: #ffffff;
		}
		.group-account
		{
			padding	: 12px;
			margin	: 10px;
			width 	: 150px;
			max-width: 100%;
		}
		.group-year
		{
			display			: flex;
			flex-wrap		: wrap;
			padding			: 15px;
		}
	</style>
@endsection
@section('data')
	@php
		$months			= array('','Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre');
		$containers		= '';
		$optionsProject	= collect();
		$optionsAccount = collect();

	@endphp
	@component("components.labels.title-divisor") BUSCAR @endcomponent
	@component("components.forms.form",["attributeEx" => "id=\"formsearch\" action=\"".route('report.account-concentrated.excel')."\"","variant"=>"deafult"])
		@component("components.containers.container-form")
			<div class="col-span-2">
				@component("components.labels.label") Empresa: @endcomponent
				@php
					$options = collect();
					foreach(App\Enterprise::where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeEnt($option_id)->pluck('enterprise_id'))->orderBy('name','asc')->get() as $e)
					{
						$options = $options->concat([["value"=>$e->id, "description"=>$e->name]]);
					}
				@endphp
				@component("components.inputs.select", 
				[
					"attributeEx"	=> "name=\"idEnterprise\" title=\"Empresa\" multiple=\"multiple\" data-validation=\"required\"", 
					"classEx"		=>  "js-enterprise",
					"options"		=> $options, 
				])
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label')Cuenta:@endcomponent
				@component('components.inputs.select', 
				[
					'attributeEx' => "title=\"Cuenta\" multiple=\"multiple\" name=\"account[]\" data-validation=\"required\"", 
					'classEx'     => "js-account removeselect", 
					"options"     => $optionsAccount
				])
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Proyecto: @endcomponent
				@component("components.inputs.select", 
				[
					"attributeEx"	=> "name=\"idProject[]\" title=\"Proyecto\" multiple=\"multiple\"", 
					"classEx"		=> "js-projects project",
					"options"		=> $optionsProject
				])
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Año: @endcomponent
				@php
					$options = collect();
					for($year = 2019; $year<= date("Y"); $year++)
					{
						$options = $options->concat([["value"=> $year, "description"=> $year]]);
					}
				@endphp
				@component("components.inputs.select", 
				[
					"attributeEx"	=> "name=\"year[]\" title=\"Año\" data-validation=\"required\" multiple=\"multiple\"",
					"classEx"		=> "js-year year",
					"options"		=> $options
				])
				@endcomponent
			</div>
			<div class="col-span-2">
				<div class="col-span-2 md:col-span-4 space-x-2 text-center md:text-right">
					@component("components.buttons.button", ["attributeEx" => "data-target=\"month\" type=\"button\"" ??'', "classEx" => "all-select select"]) todos los meses @endcomponent
				</div>
				@component("components.labels.label") Meses: @endcomponent
				@php
					$options = collect();
					for($month = 1; $month <= 12; $month++)
					{
						$options = $options->concat([["value"=> $month, "description"=> $months[$month]]]);
					}
				@endphp
				@component("components.inputs.select", 
				[
					"attributeEx"	=>  "name=\"month[]\" title=\"Meses\" data-validation=\"required\" multiple=\"multiple\"", 
					"classEx"		=> "js-months month",
					"options"		=> $options
				])
				@endcomponent
			</div>
			<div class="col-span-2 flex md:col-span-4 space-x-2 text-center md:text-left">
				<div class="p-3 m-3 w-36">
					@component("components.labels.label")ARCHIVO DE EXCEL @endcomponent
					@component("components.buttons.button",['variant'=>'none','attributeEx' => "id=\"export_excel\"", 'classEx'=>"btn follow-btn"]) 
						<img src="{{ asset('images/charts/excel.svg') }}" class="img-responsive" width="100"> 
					@endcomponent
				</div>
				<div class="p-3 m-3 w-36">
					@component("components.labels.label")GRÁFICA CIRCULAR @endcomponent
					@component("components.buttons.button",['variant'=>'none','attributeEx' => "id=\"showChartCircle\" formaction=\"".route('report.account-concentrated.charts')."\" type=\"submit\"", 'classEx'=>"btn follow-btn",]) 
						<img src="{{ asset('images/charts/graphic_circle.svg') }}" class="img-responsive" width="100"> 
					@endcomponent
				</div>
			</div>
		@endcomponent
	@endcomponent
@endsection

@section('scripts')
<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
<script src="{{ asset('js/jquery-ui.js') }}"></script>
<script src="{{ asset('js/datepicker.js') }}"></script>
<script src="{{ asset('js/apexcharts.js') }}"></script>
<script src="{{ asset('js/loader.js') }}"></script>
<script type="text/javascript">
	$(document).ready(function()
	{
		$.validate(
		{
			form 		: '#formsearch',
			modules 	: 'security',
			onError   	: function($form)
			{
				swal('', '{{ Lang::get("messages.form_error") }}', 'error');
			},
			onSuccess : function($form)
			{
				swal("Cargando, espere por favor...",{
					icon: '{{ asset(getenv('LOADING_IMG')) }}',
					button: false,
					closeOnClickOutside: false,
					closeOnEsc: false,
					timer:	3000
				});
				return true;
			}
		});

		@php
			$selects = collect([
				[
					"identificator"			=> ".js-enterprise",
					"placeholder"			=> "Seleccione una empresa",
					"languaje"				=> "es",
					"maximumSelectionLength"=> "1"
				],
				[
					"identificator"			=> ".js-year",
					"placeholder"			=> "Seleccione un año",
					"languaje"				=> "es",
				],
				[
					"identificator"			=> ".js-months",
					"placeholder"			=> "Seleccione un mes",
					"languaje"				=> "es",
				]
			]);
		@endphp
		@component("components.scripts.selects",["selects" => $selects]) @endcomponent
		generalSelect({'selector':'.js-projects', 'model': 21, 'maxSelection': -1});
		generalSelect({'selector':'.js-account','depends': '.js-enterprise','model': 60, 'maxSelection': -1});

		$(document).on('select2:unselecting','.js-enterprise',function(e)
		{
			e.preventDefault();
			$(this).val(null).trigger('change');
			$('#select_accounts').prop('disabled',true);
		})
		.on('click','.all-select',function()
		{
			target	= '.'+$(this).attr('data-target');
			if($(this).hasClass('select'))
			{
				$(this).removeClass('select');
				$(target+' option').each(function(i,v)
				{
					$(this).prop('selected',true);
					$(target).trigger('change');
				});
			}
			else
			{
				$(this).addClass('select');
				$(target+' option').each(function(i,v)
				{
					$(this).prop('selected',false);
					$(target).trigger('change');
				});
			}
		});
	});
	
	@if(isset($alert)) 
		{!! $alert !!} 
	@endif 
</script> 
@endsection


