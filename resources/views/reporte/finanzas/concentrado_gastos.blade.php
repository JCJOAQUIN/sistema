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
		$months 	= array('','Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre');
		$containers	= '';
	@endphp
	@component("components.labels.title-divisor") BUSCAR @endcomponent
	@component("components.forms.form",["attributeEx" => "id=\"formsearch\" action=\"".route('report.expenses-concentrated.result')."\"","variant"=>"deafult"])
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
				@component('components.labels.label')Cuenta:@endcomponent
				@php
					$optionsAccount = collect();
					if(isset($enterprise) && isset($account))
					{
						foreach( App\Account::where('idEnterprise',$enterprise)->where('account','like','5%')->whereIn('account',$account)->orderBy('account','ASC')->get() as $a)
						{
							if ($a->level == 2)
							{
								$description	= $a->fullClasificacionName();
								$optionsAccount	= $optionsAccount->concat([['value'=>$a->account, 'selected'=>'selected', 'description'=>$description]]);
							}
						}
					}
				@endphp
				@component('components.inputs.select', 
					[
						'attributeEx' => "title=\"Cuenta\" multiple=\"multiple\" name=\"account[]\"", 
						'classEx'     => "js-account removeselect", 
						"options"     => $optionsAccount
					]
				)
				@endcomponent
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


					$attributeEx	= "name=\"year[]\" title=\"Año\" data-validation=\"required\" multiple=\"multiple\"";
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
					$attributeEx	= "name=\"month[]\" title=\"Meses\" data-validation=\"required\" multiple=\"multiple\"";
					$classEx		= "js-months month";
				@endphp
				@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])@endcomponent
			</div>
			<div class="col-span-2 md:col-span-4 space-x-2 text-center md:text-left">
				@component("components.buttons.button",['variant' => 'secondary']) OBTENER RESULTADOS @endcomponent
			</div>
		@endcomponent
	@endcomponent

	
	@if(isset($arrayChart))
		<form method="get">
			<div class="border-4 border-gray-600 rounded p-3">
				@php
					$nameEnterprise = App\Enterprise::find($enterprise)->name;
				@endphp
				@component("components.labels.label",['classEx'=>'font-semibold']) {{ mb_strtoupper($nameEnterprise,'UTF-8') }} @endcomponent
				@component("components.labels.label",['classEx'=>'font-semibold']) {{ $months[reset($month)] }} - {{$months[end($month)] }} @endcomponent
				<div class="flex flex-wrap p-3">
					<div class="p-3 m-3 w-36 max-w-full">
						@component("components.labels.label",['classEx'=>'font-semibold'])GRÁFICA DE ÁREA @endcomponent
						<a class="btn follow-btn" id="graph_area" href="#container_graph_area"><img src="{{ asset('images/charts/area.svg') }}" class="img-responsive" width="100"></a>
					</div>
					<div class="p-3 m-3 w-36 max-w-full">
						@component("components.labels.label",['classEx'=>'font-semibold'])GRÁFICA DE BARRAS @endcomponent
						<a class="btn follow-btn" id="graph_bar" href="#container_graph_bar"><img src="{{ asset('images/charts/graph_bar.svg') }}" class="img-responsive" width="100"></a>
					</div>
					<div class="p-3 m-3 w-36 max-w-full">
						@component("components.labels.label",['classEx'=>'font-semibold'])ARCHIVO DE EXCEL @endcomponent
						<button type="submit" class="btn follow-btn" id="export_excel" formaction="{{ route('report.expenses-concentrated.download.excel',$fileName) }}"><img src="{{ asset('images/charts/excel.svg') }}" class="img-responsive" width="100"></button>
					</div>
				</div>
			</div>
		</form>
		<p><br></p>
		@foreach($year as $y)
			<div class="border-4 border-gray-600 rounded p-3" id="group_graph">
				@php
					$nameEnterprise = App\Enterprise::find($enterprise)->name;
				@endphp
				@component("components.labels.label",['classEx'=>'font-semibold']) {{ mb_strtoupper($nameEnterprise,'UTF-8') }} @endcomponent
				@component("components.labels.label",['classEx'=>'font-semibold']) {{ $months[reset($month)] }} - {{$months[end($month)] }} {{ $y }} @endcomponent
				<div class="flex flex-wrap p-3">
					@foreach($account as $acc)
						@php
							$nameAccount = App\Account::where('idEnterprise',$enterprise)->where('account',$acc)->first()->description;
							$containers .= '<div class="hide" id="container_graph_'.$acc.'_'.$y.'"></div><br>';
						@endphp
						<div class="p-3 m-3 w-36 max-w-full">
							@component("components.labels.label",['classEx'=>'font-semibold']) {{ mb_strtoupper($nameAccount,'UTF-8') }} @endcomponent
							<a class="btn follow-btn" id="graph_{{ $acc }}_{{ $y }}" href="#container_graph_{{ $acc }}_{{ $y }}"><img src="{{ asset('images/charts/graphic_circle.svg') }}" class="img-responsive" width="100"></a>
						</div>
					@endforeach
				</div>
			</div>
			<p><br></p>
		@endforeach
		{!! $containers !!}
		<div class="hide" id="container_graph_area"></div>
		<div class="hide" id="container_graph_bar"></div>
	@endif
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
			form: '#formsearch',
			onError   : function($form)
			{
				swal('', '{{ Lang::get("messages.form_error") }}', 'error');
			},
			onSuccess : function($form)
			{
				swal("Cargando",{
					icon: '{{ asset(getenv('LOADING_IMG')) }}',
					button: false,
					closeOnClickOutside: false,
					closeOnEsc: false
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
		generalSelect({'selector':'.js-account','depends': '.js-enterprise','model': 58, 'maxSelection': -1});

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
		})
		@if(isset($arrayChart))
			@php
				$arrayDataArea 	= [];
				$count 			= 0;
			@endphp
			@foreach($year as $y)
				@php
					$nameEnterprise				= App\Enterprise::find($enterprise)->name;
					$arrayDataArea['total_'.$y]	= [];
					$arrayDataArea['account']	= [];
				@endphp
				@foreach($account as $acc)
					@php
						$totalAccount 	= 0;
						$nameAccount 	= App\Account::where('idEnterprise',$enterprise)->where('account',$acc)->first()->description;
					@endphp
					.on('click','#graph_{{ $acc }}_{{ $y }}',function()
					{
						$('#container_graph_{{ $acc }}_{{ $y }}').empty();
						$('#container_graph_{{ $acc }}_{{ $y }}').hide();
						$('.hide').hide();
						var options = 
						{
							series	: [
								@foreach ($accountRegister as $a)
									@if ($a['selectable_'.$y] == 0 && $a['level_'.$y] == 3 && ($a['father_'.$y]==$acc || $a['account_'.$y]==$acc))
										{{ round($total[$a['account_'.$y].'_'.$y],2) }},
										@php
											$totalAccount += round($total[$a['account_'.$y].'_'.$y],2);
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
									export:
									{
										csv:
										{
											filename: '{{ $nameAccount }} de {{ $months[reset($month)] }} a {{$months[end($month)] }} {{ $y }}',
											headerCategory: 'CATEGORIA',
											headerValue: 'VALOR'
										},
										svg:
										{
											filename: '{{ $nameAccount }} de {{ $months[reset($month)] }} a {{$months[end($month)] }} {{ $y }}',
										},
										png:
										{
											filename: '{{ $nameAccount }} de {{ $months[reset($month)] }} a {{$months[end($month)] }} {{ $y }}',
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
											selection	: "Selección",
											selectionZoom : "Acercar Selección",
											zoomIn		: "Acercar",
											zoomOut		: "Alejar",
											pan			: "Desplazar",
											reset		: "Reestablecer",
										}
									}
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
											name:
											{
												show	: true,
												formatter: function (val) 
												{
													return val
												}
											},
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
												label		: 'TOTAL',
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
								@foreach ($accountRegister as $a)
									@if ($a['selectable_'.$y] == 0 && $a['level_'.$y] == 3 && ($a['father_'.$y]==$acc || $a['account_'.$y]==$acc))
										'{{ mb_strtoupper($a['description_'.$y], 'UTF-8') }}',
									@endif
								@endforeach
							],
							title: 
							{
								text		: '{{ $nameEnterprise }}',
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
								text		: '{{ $nameAccount }} - Periodo {{ $months[reset($month)] }} a {{$months[end($month)] }} {{ $y }}',
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
								// y: 
								// {
								// 	formatter: function(val) 
								// 	{
								// 	return ''
								// 	},
								// 	title: 
								// 	{
								// 		formatter: function (seriesName) 
								// 		{
								// 			return seriesName
								// 		}
								// 	}
								// },
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
										text		: '{{ $nameEnterprise }}', 
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
										floating: true,
										text		: ['{{ $nameAccount }} - Periodo {{ $months[reset($month)] }} a {{$months[end($month)] }} {{ $y }}', 'TOTAL - ${{ number_format($totalAccount,2) }}'],
										align		: 'left',
										style 		:
										{
											fontSize	:  '12px',
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
							}]
						};

						$('#container_graph_{{ $acc }}_{{ $y }}').show();
						var chart = new ApexCharts(document.querySelector("#container_graph_{{ $acc }}_{{ $y }}"), options);
						chart.render();
					})
					@php
						array_push($arrayDataArea['total_'.$y], round($totalAccount,2));
						array_push($arrayDataArea['account'], $nameAccount);
						$count++;
					@endphp
				@endforeach
			@endforeach
			.on('click','#graph_area',function()
			{
				$('#container_graph_area').empty();
				$('#container_graph_area').hide();
				$('.hide').hide();
				var options = 
				{
					series: [
						@foreach($year as $y)
							{
								name: '{{ $y }}',
								data: [
									@foreach($arrayDataArea['total_'.$y] as $data)
										{{ $data }},
									@endforeach
								],
							},
						@endforeach
					],
					chart: 
					{
						height: 550,
						type: 'area',
						toolbar: 
						{
							export:
							{
								csv:
								{
									filename: '{{ $nameAccount }} de {{ $months[reset($month)] }} a {{$months[end($month)] }} {{ $y }}',
									headerCategory: 'CATEGORIA',
									headerValue: 'VALOR'
								},
								svg:
								{
									filename: '{{ $nameAccount }} de {{ $months[reset($month)] }} a {{$months[end($month)] }} {{ $y }}',
								},
								png:
								{
									filename: '{{ $nameAccount }} de {{ $months[reset($month)] }} a {{$months[end($month)] }} {{ $y }}',
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
									selection	: "Selección",
									selectionZoom : "Acercar Selección",
									zoomIn		: "Acercar",
									zoomOut		: "Alejar",
									pan			: "Desplazar",
									reset		: "Reestablecer",
								},
							},
						}],
					},
					dataLabels: 
					{
						enabled: false
					},
					stroke: 
					{
						curve: 'smooth'
					},
					xaxis: 
					{
						categories: [
							@foreach($arrayDataArea['account'] as $data)
								"{{ $data }}",
							@endforeach
						]
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
								return new Intl.NumberFormat().format(val);
							}
						}
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
					title: 
					{
						text		: '{{ $nameEnterprise }}',
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
					markers: 
					{
						size: [4]
					}
				};
				$('#container_graph_area').show();
				var chart = new ApexCharts(document.querySelector("#container_graph_area"), options);
				chart.render();
			})
			.on('click','#graph_bar',function()
			{
				$('#container_graph_bar').empty();
				$('#container_graph_bar').hide();
				$('.hide').hide();
				var options = 
				{
					series: 
					[
						@foreach($year as $y)
							{
								name: '{{ $y }}',
								data: [
									@foreach($arrayDataArea['total_'.$y] as $data)
										{{ $data }},
									@endforeach
								],
							},
						@endforeach
					],
					chart: 
					{
						type: 'bar',
						height: 550,
						toolbar: 
						{
							export:
							{
								csv:
								{
									filename: '{{ $nameAccount }} de {{ $months[reset($month)] }} a {{$months[end($month)] }} {{ $y }}',
									headerCategory: 'CATEGORIA',
									headerValue: 'VALOR'
								},
								svg:
								{
									filename: '{{ $nameAccount }} de {{ $months[reset($month)] }} a {{$months[end($month)] }} {{ $y }}',
								},
								png:
								{
									filename: '{{ $nameAccount }} de {{ $months[reset($month)] }} a {{$months[end($month)] }} {{ $y }}',
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
									selection	: "Selección",
									selectionZoom : "Acercar Selección",
									zoomIn		: "Acercar",
									zoomOut		: "Alejar",
									pan			: "Desplazar",
									reset		: "Reestablecer",
								},
							},
						}],
					},
					plotOptions: 
					{
						bar: 
						{
							horizontal: false,
							columnWidth: '55%',
							endingShape: 'flat'
						},
					},
					dataLabels: 
					{
						enabled: false
					},
					stroke: 
					{
						show: true,
						width: 2,
						colors: ['transparent']
					},
					xaxis: 
					{
						categories: [
							@foreach($arrayDataArea['account'] as $data)
								"{{ $data }}",
							@endforeach
						]
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
								return new Intl.NumberFormat().format(val);
							}
						}
					},
					fill: 
					{
						opacity: 1
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
					title: 
					{
						text		: '{{ $nameEnterprise }}',
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
				};

				$('#container_graph_bar').show();
				var chart = new ApexCharts(document.querySelector("#container_graph_bar"), options);
				chart.render();
			})
		@endif
	});
	
	@if(isset($alert)) 
		{!! $alert !!} 
	@endif 
</script> 
@endsection


