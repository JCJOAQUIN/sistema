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
		.group-charts
		{
			display			: flex;
			flex-wrap		: wrap;
			padding			: 15px;
		}
	</style>
@endsection
@section('data')
	@php
		$monthsArray = array('','Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre');
		$containers = '';
	@endphp
	@php
		$typeReport = $type == 1 ? 'Anual' : 'Mensual'
	@endphp
	<form method="get">
		<div class="group">
			@php
				$nameEnterprise = App\Enterprise::find($enterprise)->name;
			@endphp
			<b>{{ mb_strtoupper($nameEnterprise,'UTF-8') }}</b><br>
			<b>Reporte {{ $typeReport }} {{ $year }}</b>
			<div class="group-charts">
				<div class="group-account">
					<label><b>ARCHIVO DE EXCEL</b></label><br>
					<button type="submit" class="btn follow-btn" id="export_excel" formaction="{{ route('report.download.excel',$fileName) }}"><img src="{{ asset('images/charts/excel.svg') }}" class="img-responsive" width="100"></button>
				</div>
				@if($type == 1)
					<div class="group-account">
						<label><b>GRÁFICA DE RESUMEN</b></label><br>
						<a class="btn follow-btn" id="graph_bar_summary" href="#container_graph_bar_summary"><img src="{{ asset('images/charts/graph_bar.svg') }}" class="img-responsive" width="100"></a>
					</div>
				@else
					<div class="group-account">
						<label><b>GRÁFICA DE RESUMEN</b></label><br>
						<a class="btn follow-btn" id="graph_bar_summary" href="#container_graph_bar_summary"><img src="{{ asset('images/charts/graph_multiline.svg') }}" class="img-responsive" width="100"></a>
					</div>
				@endif
				@if($type == 2)
					<div class="group-account">
						<label><b>GRÁFICA DE GASTOS</b></label><br>
						<a class="btn follow-btn" id="graph_bar_multi" href="#container_graph_bar_multi"><img src="{{ asset('images/charts/graph_multiline.svg') }}" class="img-responsive" width="100"></a>
					</div>
				@endif
			</div>
		</div>
		<p><br></p>
		@if($type == 1)
			@foreach($accountsER as $accER)
				@if($total[$accER['account']] > 0)
					@php
						$containers .= '<div class="hide" id="container_graph_bar_'.$accER['account'].'"></div><br>';
						$containers .= '<div class="hide" id="container_graph_circle_'.$accER['account'].'"></div><br>';
					@endphp
					<div class="group">
						<b>{{ mb_strtoupper($accER['description'],'UTF-8') }}</b><br>
						<b>{{ $typeReport }} {{ $year }}</b>
						<div class="group-charts">
							<div class="group-account">
								<a class="btn follow-btn" id="graph_circle_{{ $accER['account'] }}" href="#container_graph_circle_{{ $accER['account'] }}"><img src="{{ asset('images/charts/graphic_circle.svg') }}" class="img-responsive" width="100"></a>
							</div>
							<div class="group-account">
								<a class="btn follow-btn" id="graph_bar_{{ $accER['account'] }}" href="#container_graph_bar_{{ $accER['account'] }}"><img src="{{ asset('images/charts/graph_bar.svg') }}" class="img-responsive" width="100"></a>
							</div>
						</div>
					</div>
					<p><br></p>
				@endif
			@endforeach
		@else
			@foreach($accountsER as $accER)
				@php
					$containers .= '<div class="hide" id="container_graph_bar_'.$accER['account'].'"></div><br>';
					$containers .= '<div class="hide" id="container_graph_circle_'.$accER['account'].'"></div><br>';
				@endphp
				<div class="group">
					<b>{{ mb_strtoupper($accER['description'],'UTF-8') }}</b><br>
					<b>{{ $typeReport }} {{ $year }}</b>
					<div class="group-charts">
						<div class="group-account">
							<a class="btn follow-btn" id="graph_circle_{{ $accER['account'] }}" href="#container_graph_circle_{{ $accER['account'] }}"><img src="{{ asset('images/charts/graphic_circle.svg') }}" class="img-responsive" width="100"></a>
						</div>
						<div class="group-account">
							<a class="btn follow-btn" id="graph_bar_{{ $accER['account'] }}" href="#container_graph_bar_{{ $accER['account'] }}"><img src="{{ asset('images/charts/graph_line.svg') }}" class="img-responsive" width="100"></a>
						</div>
					</div>
				</div>
				<p><br></p>
			@endforeach
		@endif
	</form>
	{!! $containers !!}
	<div class="hide" id="container_graph_bar_summary"></div>
	<div class="hide" id="container_graph_bar_multi"></div>
	<br><br>
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
		$('.js-enterprise').select2(
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
		$('.js-projects').select2(
		{
			placeholder : 'Seleccione uno o varios proyectos (Opcional)',
			language 	: 'es',
		});
		$('.type').select2(
		{
			placeholder				: 'Anual/Mensual',
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
		$('.year').select2(
		{
			placeholder				: 'Seleccione uno o varios años',
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
		$('.month').select2(
		{
			placeholder : 'Seleccione uno o varios meses',
			language 	: 'es',
		});
		$('.js-account').select2(
		{
			placeholder : 'Seleccione la cuenta',
			language 	: 'es',
		});

		$(document).on('change','.js-enterprise',function()
		{
			$('#select_accounts').prop('disabled',false);
			$('.js-account').empty();
			enterprise	= $(this).val();
			$.ajax(
			{
				type 	: 'get',
				url 	: '{{ url("/report/finance/expenses-concentrated/get-account") }}',
				data 	: {'enterpriseid':enterprise},
				success : function(data)
				{
					$.each(data,function(i, d) 
					{
						$('.js-account').append('<option value='+d.account+'>'+d.description+'</option>');
					});
				},
				error : function()
				{
					swal('','Sucedió un error, por favor intente de nuevo.','error');
					$('.js-account').val(null).trigger('change');
				}
			});
		})
		.on('select2:unselecting','.js-enterprise',function(e)
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
		})
		@if(isset($accountsER))
			@if($type == 1)
				.on('click','#graph_bar_summary',function()
				{
					$('.hide').hide();
					var options = 
					{
						series: 
						[{
							name: 'Total',
							data: [
								{{ $total['resumen_ventas'] }},{{ $total['resumen_ingresos'] }},{{ $total['resumen_gastos'] }},
							]
						}],
						chart: 
						{
							height: 550,
							type: 'bar',
							toolbar: 
							{
								show			: true,
								offsetX			: 0,
								offsetY			: 0,
								tools			: 
								{
									download		: '<img src="{{ asset('images/charts/download.png') }}" class="ico-download" width="20">'

								},
							},
						},
						plotOptions: 
						{
							bar: 
							{
								columnWidth: '45%',
								distributed: true
							}
						},
						title: 
						{
							text		: 'RESUMEN',
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
						tooltip: 
						{
							y: 
							{
								formatter: function (val) 
								{
									return "$" + new Intl.NumberFormat().format(val) + ""
								}
							}
						},
						dataLabels: 
						{
							enabled: false
						},
						legend: 
						{
							show: false
						},
						xaxis: 
						{
							categories: 
							[
								'VENTAS','INGRESOS','GASTOS'
							],
							labels: 
							{
								style: 
								{
									fontSize: '10px'
								}
							}
						},
						yaxis: 
						{
							title: 
							{
								text: 'Monto'
							},
							labels: 
							{
								formatter: function(val, index) 
								{
									return new Intl.NumberFormat().format(val)
								}
							}
						},
					};
					$('#container_graph_bar_summary').show();
					var chart = new ApexCharts(document.querySelector("#container_graph_bar_summary"), options);
					chart.render();
				})
				@foreach($accountsER as $accER)
					@php
						$totalAccount 	= 0;
					@endphp
					.on('click','#graph_circle_{{ $accER['account'] }}',function()
					{
						$('.hide').hide();
						var options = 
						{
							series	: [
								@foreach($accountRegisterStatement as $accRS)
									@if($accER['account'] == $accRS['father'])
										{{ $total[$accRS['account']] }},
										@php
											$totalAccount += round($total[$accRS['account']],2);
										@endphp
									@endif
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
								},
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
														return '${{ number_format($totalAccount,2) }}'
													}, 0)
												}
											}
										}
									}
								}
							},
							labels 	: [
								@foreach($accountRegisterStatement as $accRS)
									@if($accER['account'] == $accRS['father'])
										'{{ $accRS['descriptionGraph'] }} - ${{ number_format($total[$accRS['account']],2) }}',
									@endif
								@endforeach
							],
							title: 
							{
								text		: '{{ $accER['description'] }}',
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
								text		: '{{ $year }}',
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
										return "$" + new Intl.NumberFormat().format(val) + ""
									}
								}
							},
							responsive 	: 
							[{
								breakpoint	: 480,
								options 	: 
								{
									chart: 
									{
										width: 200
									},
									legend: 
									{
										position: 'bottom'
									}
								}
							}]
						};

						$('#container_graph_circle_{{ $accER['account'] }}').show();
						var chart = new ApexCharts(document.querySelector("#container_graph_circle_{{ $accER['account'] }}"), options);
						chart.render();
					})
					.on('click','#graph_bar_{{ $accER['account'] }}',function()
					{
						$('.hide').hide();
						var options = 
						{
							series: 
							[{
								name: 'Total',
								data: [
									@foreach($accountRegisterStatement as $accRS)
										@if($accER['account'] == $accRS['father'])
											{{ $total[$accRS['account']] }},
										@endif
									@endforeach

								]
							}],
							chart: 
							{
								height: 550,
								type: 'bar',
								toolbar: 
								{
									show			: true,
									offsetX			: 0,
									offsetY			: 0,
									tools			: 
									{
										download		: '<img src="{{ asset('images/charts/download.png') }}" class="ico-download" width="20">'

									},
								},
							},
							plotOptions: 
							{
								bar: 
								{
									columnWidth: '45%',
									distributed: true
								}
							},
							title: 
							{
								text		: '{{ $accER['description'] }}',
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
							tooltip: 
							{
								y: 
								{
									formatter: function (val) 
									{
										return "$" + new Intl.NumberFormat().format(val) + ""
									}
								}
							},
							dataLabels: 
							{
								enabled: false
							},
							legend: 
							{
								show: false
							},
							xaxis: 
							{
								categories: 
								[
									@foreach($accountRegisterStatement as $accRS)
										@if($accER['account'] == $accRS['father'])
											'{{ $accRS['descriptionGraph'] }}',
										@endif
									@endforeach
								],
								labels: 
								{
									style: 
									{
										fontSize: '10px'
									}
								}
							},
							yaxis: 
							{
								title: 
								{
									text: 'Monto'
								},
								labels: 
								{
									formatter: function(val, index) 
									{
										return new Intl.NumberFormat().format(val)
									}
								}
							},
						};
						$('#container_graph_bar_{{ $accER['account'] }}').show();
						var chart = new ApexCharts(document.querySelector("#container_graph_bar_{{ $accER['account'] }}"), options);
						chart.render();
					})
				@endforeach
			@else
				.on('click','#graph_bar_summary',function()
				{
					$('.hide').hide();
					var options = 
					{
					  	series: 
					  	[
						  	{
								name: 'VENTAS',
								data: 
								[
									@foreach($months as $month)
										{{ $total['resumen_ventas_'.$month] }},
									@endforeach
								]
							},
							{
								name: 'INGRESOS',
								data: 
								[
									@foreach($months as $month)
										{{ $total['resumen_ingresos_'.$month] }},
									@endforeach
								]
							},
							{
								name: 'GASTOS',
								data: 
								[
									@foreach($months as $month)
										{{ $total['resumen_gastos_'.$month] }},
									@endforeach
								]
							},

						],
					  	chart: 
					  	{
					  		height: 650,
					  		type: 'line',
					  		zoom: 
					  		{
								enabled: true,
					  		},
					  		tools: 
							{
								download : '<img src="{{ asset('images/charts/download.png') }}" class="ico-download" width="20">'
							},
						},
						colors: ['#f44336','#b02466','#9c27b0','#673ab7','#3f51b5','#2196f3','#34418e','#00bcd4','#009688','#4caf50','#18aa71','#cddc39','#feb300','#ffc107','#ff9800','#ff5722','#795548','#9e9e9e','#607d8b'],
						dataLabels: 
						{
					  		enabled: false
						},
						stroke: 
						{
					  		curve: 'straight'
						},
						title: 
						{
					  		text: 'RESUMEN',
					  		align: 'left',
							style 		:
							{
								fontSize	:  '16px',
								fontWeight	:  'bold',
								fontFamily	:  undefined,
								color		:  '#263238'
							},
						},
						grid: 
						{
					  		row: 
					  		{
								colors: ['#f3f3f3', 'transparent'], // takes an array which will be repeated on columns
								opacity: 0.5
					  		},
						},
						xaxis: 
						{
					  		categories: [
					  			@foreach($months as $month)
					  				'{{ $monthsArray[$month] }}',
					  			@endforeach
					  		],
						},
						yaxis: 
						{
							labels: 
							{
								formatter: function(val, index) 
								{
									return new Intl.NumberFormat().format(val)
								}
							}
						},
						tooltip: 
						{
							y: 
							{
								formatter: function (val) 
								{
									return "$" + new Intl.NumberFormat().format(val) + ""
								}
							}
						},
						markers: 
						{
							size: [1],
						}
					};
					$('#container_graph_bar_summary').show();
					var chart = new ApexCharts(document.querySelector("#container_graph_bar_summary"), options);
					chart.render();
				})
				@foreach($accountsER as $accER)
					@php
						$totalAccount 	= 0;
					@endphp
					.on('click','#graph_circle_{{ $accER['account'] }}',function()
					{
						$('.hide').hide();
						var options = 
						{
							series	: [
								@foreach($accountRegisterStatement as $accRS)
									@php
										$totalMonth = 0;
									@endphp
									@if($accER['account'] == $accRS['father'])
										@foreach($months as $month)
											@php
												$totalMonth += $total[$accRS['account'].'_'.$month];
												$totalAccount += $total[$accRS['account'].'_'.$month];
											@endphp
										@endforeach
										{{ $totalMonth }},
									@endif
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
								},
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
														return '${{ number_format($totalAccount,2) }}'
													}, 0)
												}
											}
										}
									}
								}
							},
							labels 	: [
								@foreach($accountsStatement as $accRS)
									@if($accER['account'] == $accRS->father)
										'{{ strtoupper($accRS->description) }}',
									@endif
								@endforeach
							],
							title: 
							{
								text		: '{{ $accER['description'] }}',
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
								text		: '{{ $year }}',
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
										return "$" + new Intl.NumberFormat().format(val) + ""
									}
								}
							},
							responsive 	: 
							[{
								breakpoint	: 480,
								options 	: 
								{
									chart: 
									{
										width: 200
									},
									legend: 
									{
										position: 'bottom'
									}
								}
							}]
						};

						$('#container_graph_circle_{{ $accER['account'] }}').show();
						var chart = new ApexCharts(document.querySelector("#container_graph_circle_{{ $accER['account'] }}"), options);
						chart.render();
					})
					.on('click','#graph_bar_{{ $accER['account'] }}',function()
					{
						@php
							$totalYear = 0;
						@endphp
						$('.hide').hide();
						var options = 
						{
						  	series: 
						  	[{
								name: "Total",
								data: 
								[
									@foreach($months as $month)
										{{ $total[$accER['account'].'_'.$month] }},
										@php
											$totalYear += $total[$accER['account'].'_'.$month];
										@endphp
									@endforeach
								]
							}],
						  	chart: 
						  	{
						  		height: 550,
						  		type: 'line',
						  		zoom: 
						  		{
									enabled: true,
						  		},
						  		tools: 
								{
									download : '<img src="{{ asset('images/charts/download.png') }}" class="ico-download" width="20">'
								},
							},
							dataLabels: 
							{
						  		enabled: false
							},
							stroke: 
							{
						  		curve: 'straight'
							},
							title: 
							{
						  		text: '{{ $accER['description'] }}',
						  		align: 'left',
								style 		:
								{
									fontSize	:  '16px',
									fontWeight	:  'bold',
									fontFamily	:  undefined,
									color		:  '#263238'
								},
							},
							subtitle:
							{
								text		: 'Total: ${{ number_format($totalYear,2) }}',
								align		: 'left',
								style 		:
								{
									fontSize	:  '16px',
									fontWeight	:  'bold',
									fontFamily	:  undefined,
									color		:  '#263238'
								},
							},
							grid: 
							{
						  		row: 
						  		{
									colors: ['#f3f3f3', 'transparent'], // takes an array which will be repeated on columns
									opacity: 0.5
						  		},
							},
							xaxis: 
							{
						  		categories: [
						  			@foreach($months as $month)
						  				'{{ $monthsArray[$month] }}',
						  			@endforeach
						  		],
							},
							yaxis: 
							{
								labels: 
								{
									formatter: function(val, index) 
									{
										return new Intl.NumberFormat().format(val)
									}
								}
							},
							tooltip: 
							{
								y: 
								{
									formatter: function (val) 
									{
										return "$" + new Intl.NumberFormat().format(val) + ""
									}
								}
							},
							markers: 
							{
								size: [3],
								colors: '#17323f',
							}
						};
						$('#container_graph_bar_{{ $accER['account'] }}').show();
						var chart = new ApexCharts(document.querySelector("#container_graph_bar_{{ $accER['account'] }}"), options);
						chart.render();
						
					})
				@endforeach
				.on('click','#graph_bar_multi',function()
				{
					$('.hide').hide();
					var options = 
					{
					  	series: 
					  	[
					  		@foreach($accountsER as $accER)
							  	{
									name: '{{ $accER['description'] }}',
									data: 
									[
										@foreach($months as $month)
											{{ $total[$accER['account'].'_'.$month] }},
										@endforeach
									]
								},
							@endforeach

						],
					  	chart: 
					  	{
					  		height: 650,
					  		type: 'line',
					  		zoom: 
					  		{
								enabled: true,
					  		},
					  		tools: 
							{
								download : '<img src="{{ asset('images/charts/download.png') }}" class="ico-download" width="20">'
							},
						},
						colors: ['#f44336','#b02466','#9c27b0','#673ab7','#3f51b5','#2196f3','#34418e','#00bcd4','#009688','#4caf50','#18aa71','#cddc39','#feb300','#ffc107','#ff9800','#ff5722','#795548','#9e9e9e','#607d8b'],
						dataLabels: 
						{
					  		enabled: false
						},
						stroke: 
						{
					  		curve: 'straight'
						},
						title: 
						{
					  		text: 'CUENTAS',
					  		align: 'left',
							style 		:
							{
								fontSize	:  '16px',
								fontWeight	:  'bold',
								fontFamily	:  undefined,
								color		:  '#263238'
							},
						},
						grid: 
						{
					  		row: 
					  		{
								colors: ['#f3f3f3', 'transparent'], // takes an array which will be repeated on columns
								opacity: 0.5
					  		},
						},
						xaxis: 
						{
					  		categories: [
					  			@foreach($months as $month)
					  				'{{ $monthsArray[$month] }}',
					  			@endforeach
					  		],
						},
						yaxis: 
						{
							labels: 
							{
								formatter: function(val, index) 
								{
									return new Intl.NumberFormat().format(val)
								}
							}
						},
						tooltip: 
						{
							y: 
							{
								formatter: function (val) 
								{
									return "$" + new Intl.NumberFormat().format(val) + ""
								}
							}
						},
						markers: 
						{
							size: [1],
						}
					};
					$('#container_graph_bar_multi').show();
					var chart = new ApexCharts(document.querySelector("#container_graph_bar_multi"), options);
					chart.render();
				})
			@endif
		@endif
	});
	
	@if(isset($alert)) 
		{!! $alert !!} 
	@endif 
</script>  
@endsection


