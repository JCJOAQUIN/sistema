@extends('layouts.child_module')
@section('data')
@php
	$months			= array('','Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre');
	$containers		= '';
	$optionsProject	= collect();
	$optionsAccount	= collect();
	$enterpriseName	= $enterprise != "" ? App\Enterprise::find($enterprise)->name : "";
@endphp
@component("components.labels.title-divisor") BUSCAR @endcomponent
@component("components.forms.form",["attributeEx" => "id=\"formsearch\" action=\"".route('report.account-concentrated.excel')."\"","variant"=>"deafult"])
	@component("components.containers.container-form")
		<div class="col-span-2">
			@component("components.labels.label") Empresa: {{ $enterpriseName }} @endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label')Cuenta: 
				@foreach(App\GroupingAccount::where('idEnterprise',$enterprise)->whereIn('id',$account)->orderBy('name')->get() as $group)
					<li>{{ $group->name }}</li>
				@endforeach
			@endcomponent
		</div>
		<div class="col-span-2">
			@component("components.labels.label") Proyecto: 
				@foreach(App\Project::whereIn('status',[1,2])->whereIn('idproyect',$project)->orderBy('proyectName','asc')->get() as $pro)
					<li>{{ $pro->proyectName }}</li>
				@endforeach
			@endcomponent
			
		</div>
		<div class="col-span-2">
			@component("components.labels.label") Año: 
				@for($i = 0; $i<count($year); $i++)
					<li>{{ $year[$i] }}</li>
				@endfor
			@endcomponent
		</div>
		<div class="col-span-2">
			@component("components.labels.label") Meses:
				@for($m = 1; $m <= 12; $m++)
					@if(in_array($m, $month))
						<li>{{ $months[$m] }}</li>
					@endif
				@endfor
			@endcomponent
		</div>
	@endcomponent
@endcomponent

<div class="table-responsive">
	@foreach($year as $key=>$valYear)
		<div class="border-4 border-warm-gray-100 rounded p-3" id="accountsChartCircle_{{ $valYear }}"></div><br><br>
	@endforeach
</div>
<div id="chart-container"></div>
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
		@foreach($year as $key=>$valYear)
			accountsChartCircle{{ $valYear }}();
		@endforeach
		$('.js-enterprise-excel').select2(
		{
			placeholder				: 'Seleccione la empresa',
			language				: 'es',
			maximumSelectionLength	: 1,

		})
		.on("change",function(e)
		{
			if($(this).val().length>1)
			{
				$(this).val($(this).val().slice(0,1)).trigger('change');
			}
		});
		$('.js-projects-excel').select2(
		{
			placeholder : 'Seleccione uno o varios proyectos (Opcional)',
			language 	: 'es',
		});
		$('.year-excel').select2(
		{
			placeholder : 'Seleccione uno o varios años',
			language 	: 'es',
		});
		$('.month-excel').select2(
		{
			placeholder : 'Seleccione uno o varios meses',
			language 	: 'es',
		});
		$('.js-account-excel').select2(
		{
			placeholder : 'Seleccione la cuenta',
			language 	: 'es',
		});
	});


	@foreach($year as $key=>$valYear)
		@php
			$data = [];
			$count = 0;
			$total = 0;
		@endphp
		@foreach($groupingDesg as $key=>$valGroup)
			@php
				$data[$count]['name']				= $valGroup['name'];
				$data[$count]['total_'.$valYear]	= round($valGroup['total_'.$valYear],2);
				$total += round($valGroup['total_'.$valYear],2);
				$count++;
			@endphp
		@endforeach
		function accountsChartCircle{{ $valYear }}() 
		{
			var options = 
			{
				series	: [
					@foreach($data as $d)
						{{ round($d['total_'.$valYear],2) }},
					@endforeach
				],
				colors:
				[
					'#f44336','#b02466','#9c27b0','#673ab7','#3f51b5','#2196f3','#34418e','#00bcd4','#009688','#4caf50','#18aa71','#cddc39','#feb300','#ffc107','#ff9800','#ff5722','#795548','#9e9e9e','#607d8b'
				],
				chart	: 
				{
					type	: 'donut',
					toolbar: 
					{
						show			: true,
						offsetX			: 0,
						offsetY			: 0,
						tools			: 
						{
							download		: '<img src="{{ asset('images/charts/download.png') }}" class="ico-download" width="20">'

						},
						export:
						{
							csv:
							{
								filename: 'Concentrado de Partidas - {{ $valYear }}',
								headerCategory: 'CATEGORIA',
								headerValue: 'VALOR'
							},
							svg:
							{
								filename: 'Concentrado de Partidas - {{ $valYear }}',
							},
							png:
							{
								filename: 'Concentrado de Partidas - {{ $valYear }}',
							}
						},
			     	},
					defaultLocale: 'es',
					locales: [{
						name: 'es',
						options: 
						{
							toolbar:
							{
								exportToSVG	: "Descargar SVG",
								exportToPNG	: "Descargar PNG",
								exportToCSV	: "Descargar CSV",
								menu		: "Menú",
							},
						},
					}],
		        },
				legend:
				{
					formatter: function(labels, opts) {
						return [labels, " - $", new Intl.NumberFormat("es-MX").format(opts.w.globals.series[opts.seriesIndex])];
					}
				},
		        plotOptions: 
		        {
			        pie: 
			        {
			            donut: 
			            {
							size	: '60%',
							labels	: 
			              	{
			                	show: true,
								value:
								{
									show	: true,
									formatter: function (val) 
									{
										return "$" + new Intl.NumberFormat("es-MX").format(val)
									}
								},
			                	total: 
								{
									show		: true,
									showAlways	: true,
									label		: 'Total',
									fontSize	: '22px',
									fontFamily	: 'Helvetica, Arial, sans-serif',
									fontWeight	: 600,
									color		: '#373d3f',
									formatter	: function (w) 
						          	{
						            	return w.globals.seriesTotals.reduce((a, b) => 
						            	{
						              		return '${{ number_format($total,2) }}'
						            	}, 0)
						          	}
						        }
			              	}
			            }
			        }
			    },
	       		labels 	: [
					@foreach($data as $d)
						//'{{ $d['name'] }} - ${{ number_format($d['total_'.$valYear],2) }}',
						'{{ $d['name'] }}',
					@endforeach
				],
	       		title: 
	       		{
					text		: '{{ App\Enterprise::find($enterprise)->name }}',
					align		: 'left',
					margin		: 10,
					offsetX		: 0,
					offsetY		: 0,
					floating	: false,
				    style 		: 
				    {
						fontSize	:  '18px',
						fontWeight	:  'bold',
						fontFamily	:  undefined,
						color		:  '#263238'
				    },
				},
				subtitle:
				{
					text		: 'Año {{ $valYear }}',
					align		: 'left',
					style 		:
					{
						fontSize	:  '16px',
						fontWeight	:  'bold',
						fontFamily	:  undefined,
						color		:  '#263238'
				    },
				},
				tooltip: 
				{
					y: 
					{
						formatter: function (val) 
						{
							return "$" + new Intl.NumberFormat("es-MX").format(val)
						}
					}
				},
	        	responsive 	: 
				[{
					breakpoint	: 700,
					options 	: 
					{
						chart: 
						{
							width: 400
						},
						legend: 
						{
							position: 'bottom',
							horizontalAlign: 'left',
							width: 300,
							offsetX: 50
						},
						title: 
						{
							text		: '{{ App\Enterprise::find($enterprise)->name }}',
							align		: 'left',
							margin		: 10,
							offsetX		: 0,
							offsetY		: 0,
							floating	: false,
							style 		: 
							{
								fontSize	:  '13px',
								fontWeight	:  'bold',
								fontFamily	:  undefined,
								color		:  '#263238'
							},
						},
						subtitle:
						{
							text		: 'Año {{ $valYear }}',
							align		: 'left',
							style 		:
							{
								fontSize	:  '11px',
								fontWeight	:  'bold',
								fontFamily	:  undefined,
								color		:  '#263238'
							},
						},
						plotOptions: 
						{
							pie: 
							{
								offsetY: 20,
								donut: 
								{
									size	: '60%',
									labels	: 
									{
										show: false,
									}
								}
							}
						},
					}
				}],
	        };

	        chart = new ApexCharts(document.querySelector("#accountsChartCircle_{{ $valYear }}"), options);
	        chart.render();
		}
	@endforeach

	@if(isset($alert)) 
		{!! $alert !!} 
	@endif 
</script> 
@endsection


