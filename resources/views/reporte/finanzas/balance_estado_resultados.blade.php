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
	@component("components.labels.title-divisor") BUSCAR @endcomponent
	@component("components.forms.form",["attributeEx" => "id=\"formsearch\" action=\"".route('report.balance-sheet.result')."\"","variant"=>"deafult"])
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
					$classEx		= "js-project project";
				@endphp
				@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Tipo de reporte: @endcomponent
				@php
					$options = collect();
					
					if(isset($type) && $type == 1 )
					{
						$options = $options->concat([["value"=> "1", "selected"=>"selected", "description" => "Anual"]]);
					}
					else
					{
						$options = $options->concat([["value"=> "1", "description"=> "Anual"]]);
					}

					if(isset($type) && $type == 2 )
					{
						$options = $options->concat([["value"=> "2", "selected"=>"selected", "description" => "Mensual"]]);
					}
					else
					{
						$options = $options->concat([["value"=> "2", "description"=> "Mensual"]]);
					}

					$attributeEx	= "name=\"type\" title=\"Tipo\" data-validation=\"required\" multiple=\"multiple\"";
					$classEx		= "js-type type";
				@endphp
				@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Año: @endcomponent
				@php
					$options = collect();
					for($yearY = 2019; $yearY<= date("Y"); $yearY++)
					{
						if( isset($year) && $year == $yearY)
						{
							$options = $options->concat([["value"=> $yearY, "selected"=>"selected", "description" => $yearY]]);
						}
						else
						{
							$options = $options->concat([["value"=> $yearY, "description"=> $yearY]]);
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
					for($monthM = 1; $monthM <= 12; $monthM++)
					{
						if(isset($months) && in_array($monthM, $months))
						{
							$options = $options->concat([["value"=> $monthM, "selected"=>"selected", "description" => $monthsArray[$monthM]]]);
						}
						else
						{
							$options = $options->concat([["value"=> $monthM, "description"=> $monthsArray[$monthM]]]);
						}
					}
					$attributeEx	= "name=\"months[]\" title=\"Meses\" data-validation=\"required\" multiple=\"multiple\"";
					$classEx		= "js-months month";
				@endphp
				@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])@endcomponent
			</div>
			<div class="col-span-2 md:col-span-4 space-x-2 text-center md:text-left">
				@php
					$attributeExButtonSearch = "formaction=\"".route('report.balance-sheet.queue')."\""
				@endphp
				@component("components.buttons.button",['variant' => 'secondary']) GENERAR @endcomponent
				@component("components.buttons.button", ["attributeEx" => $attributeExButtonSearch??'', "classEx" => $classExButtonSearch??'', 'variant' => 'secondary']) ENVIAR A COLA @endcomponent
				<!--button class="btn btn-red" type="submit" formaction="{{ route('report.balance-sheet.generate') }}">GENERAR</button-->
			</div>
		@endcomponent
	@endcomponent
	@if(isset($accountRegister))
		@php
			$typeReport = $type == 1 ? 'Anual' : 'Mensual'
		@endphp
		<form method="get">
			<div class="border-4 border-gray-600 rounded p-3">
				@php
					$nameEnterprise = App\Enterprise::find($enterprise)->name;
				@endphp
				@component("components.labels.label",['classEx'=>'font-semibold']) {{ mb_strtoupper($nameEnterprise,'UTF-8') }} @endcomponent
				@component("components.labels.label",['classEx'=>'font-semibold']) Reporte  {{ $typeReport }} {{ $year }} @endcomponent
				<div class="flex flex-wrap p-3">
					<div class="p-3 m-3 w-36 max-w-full">
						@component("components.labels.label")ARCHIVO DE EXCEL @endcomponent
						<button type="submit" class="btn follow-btn" id="export_excel" formaction="{{ route('report.download.excel',$fileName) }}"><img src="{{ asset('images/charts/excel.svg') }}" class="img-responsive" width="100"></button>
					</div>
					@if($type == 1)
						<div class="p-3 m-3 w-36 max-w-full">
							@component("components.labels.label")GRÁFICA DE RESUMEN @endcomponent
							<a class="btn follow-btn" id="graph_bar_summary" href="#container_graph_bar_summary"><img src="{{ asset('images/charts/graph_bar.svg') }}" class="img-responsive" width="100"></a>
						</div>
					@else
						<div class="p-3 m-3 w-36 max-w-full">
							@component("components.labels.label")GRÁFICA DE RESUMEN @endcomponent
							<a class="btn follow-btn" id="graph_bar_summary" href="#container_graph_bar_summary"><img src="{{ asset('images/charts/graph_multiline.svg') }}" class="img-responsive" width="100"></a>
						</div>
					@endif
					@if($type == 2)
						<div class="p-3 m-3 w-36 max-w-full">
							@component("components.labels.label")GRÁFICA DE GASTOS @endcomponent
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
						<div class="border-4 border-gray-600 rounded p-3">
							@component("components.labels.label",['classEx'=>'font-semibold']) {{ mb_strtoupper($accER['description'],'UTF-8') }} @endcomponent
							@component("components.labels.label",['classEx'=>'font-semibold']) {{ $typeReport }} {{ $year }} @endcomponent
							<div class="flex flex-wrap p-3">
								<div class="p-3 m-3 w-36 max-w-full">
									<a class="btn follow-btn" id="graph_circle_{{ $accER['account'] }}" href="#container_graph_circle_{{ $accER['account'] }}"><img src="{{ asset('images/charts/graphic_circle.svg') }}" class="img-responsive" width="100"></a>
								</div>
								<div class="p-3 m-3 w-36 max-w-full">
									<a class="btn follow-btn" id="graph_bar_{{ $accER['account'] }}" href="#container_graph_bar_{{ $accER['account'] }}"><img src="{{ asset('images/charts/graph_bar.svg') }}" class="img-responsive" width="100"></a>
								</div>
							</div>
						</div>
						<p><br></p>
					@endif
				@endforeach
			@else
				@php
					$accERDescendents = [];
					foreach ($accountsER as $accER) {
						foreach ($accountRegisterStatement as $accRS) {
							if($accRS['identifier'] == 3 || $accRS['identifier'] == 4)
							{
								if($accER['account'] == $accRS['father'])
								{
									if(!in_array($accER, $accERDescendents))
									{
										array_push($accERDescendents, $accER);
									}
								}
							}
						}
					}
				@endphp
				@foreach($accERDescendents as $accER)
					@php
					$containers .= '<div class="hide" id="container_graph_bar_'.$accER['account'].'"></div><br>';
					$containers .= '<div class="hide" id="container_graph_circle_'.$accER['account'].'"></div><br>';
					@endphp
					<div class="border-4 border-gray-600 rounded p-3">
						@component("components.labels.label",['classEx'=>'font-semibold']) {{ mb_strtoupper($accER['description'],'UTF-8') }} @endcomponent
						@component("components.labels.label",['classEx'=>'font-semibold']) {{ $typeReport }} {{ $year }} @endcomponent
						<div class="flex flex-wrap p-3">
							<div class="p-3 m-3 w-36 max-w-full">
								<a class="btn follow-btn" id="graph_circle_{{ $accER['account'] }}" href="#container_graph_circle_{{ $accER['account'] }}"><img src="{{ asset('images/charts/graphic_circle.svg') }}" class="img-responsive" width="100"></a>
							</div>
							<div class="p-3 m-3 w-36 max-w-full">
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
	@else
		@if (count($reports) > 0)
			@php
				$body		= [];
				$modelBody	= [];
				$modelHead	= 
				[
					[
						["value"	=> "ID", "show" => "true"],
						["value"	=> "Tipo de reporte", "show" => "true"],
						["value"	=> "Empresa"],
						["value"	=> "Fecha"],
						["value"	=> "Estado"],
						["value"	=> "Acción"]
					]
				];

				$btnElements = 
				[
					"content"	=> 
					[
						"label" => ""
					]
				];

				foreach($reports as $report)
				{
					$body = 
					[
						[
							"content"	=>
							[
								"label" => $report->id
							]
						],
						[
							"content"	=>
							[
								"label" => $report->type == 1 ? 'Anual' : 'Mensual'
							]
						],
						[
							"content"	=>
							[
								"label" => $report->dataEnterprise->enterprise->name
							]
						],
						[
							"content"	=>
							[
								"label" => Carbon\Carbon::createFromFormat('Y-m-d',$report->date)->format('d-m-Y')
							]
						],
						[
							"content"	=>
							[
								"label" => $report->status == 0 ? 'Pendiente' : 'Generado'
							]
						]
					];

					if($report->status == 1)
					{
						$btnElements = [
							"content"	=> 
							[ 
								[
									"kind"			=> "components.buttons.button",
									"buttonElement"	=> "a",
									"label"			=> "<span class=\"icon-plus\"></span>",
									"variant"		=> "warning",
									"classEx"		=> "follow-btn px-2 py-2",
									"attributeEx"	=> "alt=\"Descargar reporte\" title=\"Descargar reporte\" href=".route('report.download.excel',$report->file)
								],
								[
									"kind"			=> "components.buttons.button",
									"buttonElement"	=> "a",
									"variant"		=> "secondary",
									"label"			=> "<span class=\"icon-search\"></span>",
									"classEx"		=> "follow-btn px-2 py-2",
									"attributeEx"	=> "alt=\"Ver detalles\" title=\"Ver detalles\" href=".route('report.balance-sheet.view-result',$report->id)
								]
							]
						];
					}
					$body[] = $btnElements;

					$modelBody[] = $body;
				}
			@endphp
			@component("components.tables.table",[
				"modelHead" => $modelHead,
				"modelBody" => $modelBody,
			])
			@endcomponent
			{{ $reports->links() }}
		@else
			@component("components.labels.not-found")@endcomponent
		@endif
	@endif
	<br><br>
@endsection

@section('scripts')
<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
<script src="{{ asset('js/jquery-ui.js') }}"></script>
<script src="{{ asset('js/datepicker.js') }}"></script>
<script src="{{ asset('js/apexcharts.js?v=3') }}"></script>
<script src="{{ asset('js/loader.js') }}"></script>
<script type="text/javascript">
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
					"identificator"			=> ".js-type",
					"placeholder"			=> "Seleccione un tipo",
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
		generalSelect({'selector':'.js-project', 'model': 21, 'maxSelection': -1});

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
		@if(isset($accountsER))
			@if($type == 1)
				.on('click','#graph_bar_summary',function()
				{
					$('#container_graph_bar_summary').empty();
					$('#container_graph_bar_summary').hide();
					$('.hide').hide();
					var options = 
					{
						series: 
						[{
							name: 'TOTAL',
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
									download		: '<img src="{{ asset('images/charts/download.png') }}" class="ico-download" width="20">',
								},
								export:
								{
									csv:
									{
										filename: 'Resumen - {{$year}}',
										headerCategory: 'CATEGORIA',
										headerValue: 'VALOR'
									},
									svg:
									{
										filename: 'Resumen - {{$year}}',
									},
									png:
									{
										filename: 'Resumen - {{$year}}',
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
								columnWidth: '45%',
								distributed: true
							}
						},
						title: 
						{
							text		: 'Resumen',
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
									return "$" + new Intl.NumberFormat("es-MX").format(val)
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
							},
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
							series	: 
							[
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
									export:
									{
										csv:
										{
											filename: '{{ mb_convert_case($accER['description'], MB_CASE_TITLE, "UTF-8") }} - {{$year}}',
											headerCategory: 'CATEGORIA',
											headerValue: 'VALOR'
										},
										svg:
										{
											filename: '{{ mb_convert_case($accER['description'], MB_CASE_TITLE, "UTF-8") }} - {{$year}}',
										},
										png:
										{
											filename: '{{ mb_convert_case($accER['description'], MB_CASE_TITLE, "UTF-8") }} - {{$year}}',
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
									},
								}
							},
							labels 	: [
								@foreach($accountRegisterStatement as $accRS)
									@if($accER['account'] == $accRS['father'])
										'{{ mb_strtoupper($accRS['descriptionGraph'],"UTF-8") }}',
									@endif
								@endforeach
							],
							title: 
							{
								text		: '{{ mb_convert_case($accER['description'], MB_CASE_TITLE, "UTF-8") }}',
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
										text		: '{{ mb_convert_case($accER['description'], MB_CASE_TITLE, "UTF-8") }}',
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
										text		: '{{ $year }}',
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

						$('#container_graph_circle_{{ $accER['account'] }}').show();
						var chart = new ApexCharts(document.querySelector("#container_graph_circle_{{ $accER['account'] }}"), options);
						chart.render();
					})
					.on('click','#graph_bar_{{ $accER['account'] }}',function()
					{
						$('#container_graph_bar_{{ $accER['account'] }}').empty();
						$('#container_graph_bar_{{ $accER['account'] }}').hide();
						$('.hide').hide();
						var options = 
						{
							series: 
							[{
								name: 'TOTAL',
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
									export:
									{
										csv:
										{
											filename: '{{ mb_convert_case($accER['description'], MB_CASE_TITLE, "UTF-8") }} - {{$year}}',
											headerCategory: 'CATEGORIA',
											headerValue: 'VALOR'
										},
										svg:
										{
											filename: '{{ mb_convert_case($accER['description'], MB_CASE_TITLE, "UTF-8") }} - {{$year}}',
										},
										png:
										{
											filename: '{{ mb_convert_case($accER['description'], MB_CASE_TITLE, "UTF-8") }} - {{$year}}',
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
									columnWidth: '45%',
									distributed: true
								}
							},
							title: 
							{
								text		: '{{ mb_convert_case($accER['description'], MB_CASE_TITLE, "UTF-8") }}',
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
										return "$" + new Intl.NumberFormat("es-MX").format(val)
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
											'{{ mb_strtoupper($accRS['descriptionGraph'],"UTF-8") }}',
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
					$('#container_graph_bar_summary').empty();
					$('#container_graph_bar_summary').hide();
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
							toolbar:
							{
								export:
								{
									csv:
									{
										filename: 'Resumen - {{$year}}',
										headerCategory: 'CATEGORIA',
										headerValue: 'VALOR'
									},
									svg:
									{
										filename: 'Resumen - {{$year}}',
									},
									png:
									{
										filename: 'Resumen - {{$year}}',
									}
								},
							},
					  		tools: 
							{
								download : '<img src="{{ asset('images/charts/download.png') }}" class="ico-download" width="20">'
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
					  		text: 'Resumen',
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
									return "$" + new Intl.NumberFormat("es-MX").format(val)
								}
							}
						},
						markers: 
						{
							size: [1],
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
									export:
									{
										csv:
										{
											filename: '{{ mb_convert_case($accER['description'], MB_CASE_TITLE, "UTF-8") }} - {{$year}}',
											headerCategory: 'CATEGORIA',
											headerValue: 'VALOR'
										},
										svg:
										{
											filename: '{{ mb_convert_case($accER['description'], MB_CASE_TITLE, "UTF-8") }} - {{$year}}',
										},
										png:
										{
											filename: '{{ mb_convert_case($accER['description'], MB_CASE_TITLE, "UTF-8") }} - {{$year}}',
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
											exportToSVG : "Descargar SVG",
											exportToPNG : "Descargar PNG",
											exportToCSV : "Descargar CSV",
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
								@foreach($accountsStatement as $accRS)
									@if($accER['account'] == $accRS->father)
										'{{ mb_strtoupper($accRS->description)}}',
									@endif
								@endforeach
							],
							title: 
							{
								text		: '{{ mb_convert_case($accER['description'], MB_CASE_TITLE, "UTF-8") }}',
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
										text		: '{{ mb_convert_case($accER['description'], MB_CASE_TITLE, "UTF-8") }}',
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
										text		: '{{ $year }}',
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

						$('#container_graph_circle_{{ $accER['account'] }}').show();
						var chart = new ApexCharts(document.querySelector("#container_graph_circle_{{ $accER['account'] }}"), options);
						chart.render();
					})
					.on('click','#graph_bar_{{ $accER['account'] }}',function()
					{
						@php
							$totalYear = 0;
						@endphp
						$('#container_graph_bar_{{ $accER['account'] }}').empty();
						$('#container_graph_bar_{{ $accER['account'] }}').hide();
						$('.hide').hide();
						var options = 
						{
						  	series: 
						  	[{
								name: "TOTAL",
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
								toolbar:
								{
									export:
									{
										csv:
										{
											filename: '{{ mb_convert_case($accER['description'], MB_CASE_TITLE, "UTF-8") }} - {{$year}}',
											headerCategory: 'CATEGORIA',
											headerValue: 'VALOR'
										},
										svg:
										{
											filename: '{{ mb_convert_case($accER['description'], MB_CASE_TITLE, "UTF-8") }} - {{$year}}',
										},
										png:
										{
											filename: '{{ mb_convert_case($accER['description'], MB_CASE_TITLE, "UTF-8") }} - {{$year}}',
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
								text: '{{ mb_convert_case($accER['description'], MB_CASE_TITLE, "UTF-8") }}',
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
										return "$" + new Intl.NumberFormat("es-MX").format(val)
									}
								}
							},
							markers: 
							{
								size: [3],
								colors: '#17323f',
							},
						};
						$('#container_graph_bar_{{ $accER['account'] }}').show();
						var chart = new ApexCharts(document.querySelector("#container_graph_bar_{{ $accER['account'] }}"), options);
						chart.render();
						
					})
				@endforeach
				.on('click','#graph_bar_multi',function()
				{
					$('#container_graph_bar_multi').empty();
					$('#container_graph_bar_multi').hide();
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
							toolbar:
							{
								export:
								{
									csv:
									{
										filename: 'Cuentas - {{$year}}',
										headerCategory: 'CATEGORIA',
										headerValue: 'VALOR'
									},
									svg:
									{
										filename: 'Cuentas - {{$year}}',
									},
									png:
									{
										filename: 'Cuentas - {{$year}}',
									}
								},
							},
					  		tools: 
							{
								download : '<img src="{{ asset('images/charts/download.png') }}" class="ico-download" width="20">'
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
					  		text: 'Cuentas',
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
									return "$" + new Intl.NumberFormat("es-MX").format(val)
								}
							}
						},
						markers: 
						{
							size: [1],
						},
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


