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
		$months 	= array('','Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre');
		$containers	= '';
	@endphp
	@component("components.labels.title-divisor") BUSCAR @endcomponent
	@component("components.forms.form",["attributeEx" => "id=\"formsearch\" action=\"".route('report.iva.result')."\"","variant"=>"deafult"])
		@component("components.containers.container-form")
			<div class="col-span-2">
				@component("components.labels.label") Empresa: @endcomponent
				@php
					$options = collect();
					foreach(App\Enterprise::where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeEnt($option_id)->pluck('enterprise_id'))->orderBy('name','asc')->get() as $e)
					{
						$description = strlen($e->name) >= 35 ? substr(strip_tags($e->name),0,35)."..." : $e->name;
						if( isset($enterprise) && $enterprise == $e->id)
						{
							$options = $options->concat([["value"=>$e->id, "selected"=>"selected", "description"=>$description]]);
						}
						else
						{
							$options = $options->concat([["value"=>$e->id, "description"=>$description]]);
						}
					}
					$attributeEx	= "name=\"enterprise\" title=\"Empresa\" multiple=\"multiple\"";
					$classEx		= "js-enterprise";
				@endphp
				@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Proyecto: @endcomponent
				@php
					$options = collect();
					if (isset($project)) 
					{
						foreach(App\Project::whereIn('idproyect',$project)->orderName()->get() as $p)
						{
							$options = $options->concat([["value"=>$p->idproyect, "selected"=>"selected", "description"=>$p->proyectName]]);
						}
					}
					$attributeEx	= "name=\"project[]\" title=\"Proyecto\" multiple=\"multiple\"";
					$classEx		= "js-projects project";
				@endphp
				@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Año: @endcomponent
				@php
					$options = collect();
					for($yearFor = 2019; $yearFor<= date("Y"); $yearFor++)
					{
						if(isset($year) && in_array($yearFor, $year))
						{
							$options = $options->concat([["value"=> $yearFor, "selected"=>"selected", "description" => $yearFor]]);
						}
						else
						{
							$options = $options->concat([["value"=> $yearFor, "description"=> $yearFor]]);
						}
					}


					$attributeEx	= "name=\"year\" title=\"Año\" data-validation=\"required\" multiple=\"multiple\"";
					$classEx		= "js-year year";
				@endphp
				@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])@endcomponent
			</div>
			<div class="col-span-2">
				<div class="col-span-2 md:col-span-4 space-x-2 text-center md:text-right">
					@component("components.buttons.button", ["attributeEx" => "data-target=\"month\" type=\"button\"" ??'', "classEx" => "all-select select"]) todos los meses @endcomponent
				</div>
				@component("components.labels.label") Meses: @endcomponent
				@php
					$options = collect();
					for($monthFor = 1; $monthFor <= 12; $monthFor++)
					{
						if(isset($month) && in_array($monthFor, $month))
						{
							$options = $options->concat([["value"=> $monthFor, "selected"=>"selected", "description" => $months[$monthFor]]]);
						}
						else
						{
							$options = $options->concat([["value"=> $monthFor, "description"=> $months[$monthFor]]]);
						}
					}
					$attributeEx	= "name=\"months[]\" title=\"Meses\" data-validation=\"required\" multiple=\"multiple\"";
					$classEx		= "js-months month";
				@endphp
				@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])@endcomponent
			</div>
			<div class="col-span-2 md:col-span-4 space-x-2 text-center md:text-left">
				@component("components.buttons.button",['variant' => 'secondary']) OBTENER RESULTADOS @endcomponent
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
					"maximumSelectionLength"=> "1"
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
		
		$(document).on('change','.year',function()
		{
			yearfull 		= $(this).children('option:selected').val();
			date_current	= new Date();
			date_year 		= (new Date).getFullYear();
			date_month 		= date_current.getMonth()+1;
			monthsArray 	= ['','Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
			
			$('.month option').remove();
			if(yearfull == date_year)
			{
				for(f=1; f<=date_month; f++)
				{
					$('.month').append('<option value="'+f+'">'+monthsArray[f]+'</option>');
				};
			}
			else
			{
				$('#all-months').addClass('select');
				for(f=1; f<monthsArray.length; f++)
				{
					$('.month').append('<option value="'+f+'">'+monthsArray[f]+'</option>');
				};
			}
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


