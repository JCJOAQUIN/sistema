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
	</style>
@endsection
@section('data')
	@component("components.labels.title-divisor") BUSCAR @endcomponent
	@component("components.forms.form",["attributeEx" => "id=\"formsearch\" action=\"".route('report.breakdown.excel')."\"","variant"=>"deafult"])
		@component("components.containers.container-form")
			<div class="col-span-2">
				@component("components.labels.label") Empresa: @endcomponent
				@php
					$options = collect();
					foreach(App\Enterprise::where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeEnt($option_id)->pluck('enterprise_id'))->orderBy('name','asc')->get() as $ent)
					{
						$description = strlen($ent->name) >= 35 ? substr(strip_tags($ent->name),0,35)."..." : $ent->name;
						if(isset($enterprise) && $ent->id == $enterprise)
						{
							$options = $options->concat([["value"=>$ent->id, "selected"=>"selected", "description"=>$description]]);
						}
						else
						{
							$options = $options->concat([["value"=>$ent->id, "description"=>$description]]);
						}
					}
				@endphp
				@component("components.inputs.select", 
				[
					"options"		=> $options, 
					"attributeEx"	=> "name=\"idEnterprise\" title=\"Empresa\" multiple=\"multiple\" data-validation=\"required\"", 
					"classEx"		=> "js-enterprise"
				])@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label')Cuenta:@endcomponent
				@php
					$optionsAccount = collect();
					if(isset($enterprise) && isset($father))
					{
						foreach( App\Account::where('idEnterprise',$enterprise)->where('account',$father)->orderBy('account','ASC')->get() as $a)
						{
							if ($a->level == 1 || $a->level == 2)
							{
								$description	= $a->fullClasificacionName();
								$optionsAccount	= $optionsAccount->concat([['value'=>$a->account, 'selected'=>'selected', 'description'=>$description]]);
							}
						}
					}
				@endphp
				@component('components.inputs.select', 
					[
						'attributeEx' => "title=\"Cuenta\" multiple=\"multiple\" name=\"father\" data-validation=\"required\"", 
						'classEx'     => "js-account", 
						"options"     => $optionsAccount
					]
				)
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Proyecto: @endcomponent
				@php
					$optionsProject = collect();
					if (isset($project)) 
					{
						foreach(App\Project::whereIn('idproyect',$project)->get() as $p)
						{
							$optionsProject = $optionsProject->concat([["value"=>$p->idproyect, "selected"=>"selected", "description"=>$p->proyectName]]);
						}
					}
				@endphp
				@component("components.inputs.select", 
					[
						"attributeEx"	=> "name=\"idProject[]\" title=\"Proyecto\" multiple=\"multiple\"", 
						"classEx"		=> "js-projects",
						"options"		=> $optionsProject, 
					])
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Rango de Fechas: @endcomponent
				@php
					$mindate	= isset($mindate) ? $mindate : '';
					$maxdate	= isset($maxdate) ? $maxdate : '';
					$inputs		= 
					[
						[
							"input_classEx"		=> "input-text-date datepicker",
							"input_attributeEx"	=> "name=\"mindate\" placeholder=\"Desde\" value=\"".$mindate."\" data-validation=\"required\"",
						],
						[
							"input_classEx"		=> "input-text-date datepicker",
							"input_attributeEx"	=> "name=\"maxdate\" placeholder=\"Hasta\" value=\"".$maxdate."\" data-validation=\"required\"",
						]
					];
				@endphp
				@component("components.inputs.range-input",["inputs" => $inputs]) @endcomponent
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
					@component("components.buttons.button",['variant'=>'none','attributeEx' => "id=\"showChartCircle\" type=\"button\"", 'classEx'=>"btn follow-btn",]) 
						<img src="{{ asset('images/charts/graphic_circle.svg') }}" class="img-responsive" width="100"> 
					@endcomponent
				</div>
				<div class="p-3 m-3 w-36">
					@component("components.labels.label")GRÁFICA DE BARRAS @endcomponent
					@component("components.buttons.button",['variant'=>'none','attributeEx' => "id=\"showChartBar\" type=\"button\"", 'classEx'=>"btn follow-btn",]) 
						<img src="{{ asset('images/charts/graph_bar.svg') }}" class="img-responsive" width="100"> 
					@endcomponent
				</div>
				@component('components.inputs.input-text',['attributeEx' => "type=\"hidden\" name=\"type\" id=\"type\""]) @endcomponent
				@component('components.inputs.input-text',['attributeEx' => "type=\"hidden\" name=\"projectname\" id=\"projectname\""]) @endcomponent
				@component('components.inputs.input-text',['attributeEx' => "type=\"hidden\" name=\"accountdesc\" id=\"accountdesc\""]) @endcomponent
			</div>
		@endcomponent
	@endcomponent

	@if (count($arrayResult)>0)
		<div id="accountsChart"></div>
	@endif
@endsection

@section('scripts')
<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
<script src="{{ asset('js/jquery-ui.js') }}"></script>
<script src="{{ asset('js/datepicker.js') }}"></script>
<script src="{{ asset('js/apexcharts.js?v=3') }}"></script>
<script src="{{ asset('js/loader.js') }}"></script>
<script type="text/javascript"> 
	$(document).ready(function()	
	{
		type			= @json($type);
		arrayResult		= @json($arrayResult);
		enterprise		= @json($enterprise);
		enterprisename	= @json($enterprisename);
		father			= @json($father);
		@php
			$selects = collect(
			[
				[
					"identificator"				=> ".js-enterprise",
					"placeholder"				=> "Seleccione una empresa",
					"languaje"					=> "es",
					"maximumSelectionLength"	=> 1,
				]
			]);
		@endphp
		@component("components.scripts.selects",["selects" => $selects]) @endcomponent
		generalSelect({'selector':'.js-projects', 'model': 21, 'maxSelection': -1});
		generalSelect({'selector':'.js-account','depends': '.js-enterprise','model': 59});

		if(type != null)
		{
			labels = [];
			series = [];
			$.each(arrayResult,function(i) 
			{
				labels.push(arrayResult[i]["description"]);
				series.push(arrayResult[i]["total"]);
			});
			$.each(enterprisename, function (i) {
			entname = enterprisename[i]["name"];
			});
			if(type == 1)
			{
				var options = 
				{
					series	: series,
					colors:
					[
						'#f44336','#b02466','#9c27b0','#673ab7','#3f51b5','#2196f3','#34418e','#00bcd4','#009688','#4caf50','#18aa71','#cddc39','#feb300','#ffc107','#ff9800','#ff5722','#795548','#9e9e9e','#607d8b'
					],
					chart	: 
					{
						width	: 900,
						type	: 'donut',
						toolbar :
						{
							show : true,
							offsetX : 0,
							offsetY : 0,
							tools :
							{
								download : '<img src="{{ asset('images/charts/download.png') }}" class="ico-download" width="20">'
							},
							export:
							{
								csv:
								{
									filename: '{{$accountdesc}}',
									headerCategory: 'CATEGORIA',
									headerValue: 'VALOR'
								},
								svg:
								{
									filename: '{{$accountdesc}}',
								},
								png:
								{
									filename: '{{$accountdesc}}',
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
					tooltip:
					{
						y: 
						{
							formatter: function (val) 
							{
								return "$" + new Intl.NumberFormat("es-MX").format(val)
							}
						},
					},
					title: 
					{
						text		: entname,
						align		: 'left',
						margin		: 10,
						offsetX		: 0,
						offsetY		: 0,
						floating	: false,
						style 		: 
						{
							fontSize	:  '14px',
							fontWeight	:  'bold',
							fontFamily	:  undefined,
							color		:  '#263238'
						},
					},
					subtitle:
					{
						text		: ['{{$projectname}}', '{{$accountdesc}}'],
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
							donut: 
							{
								size	: '60%',
								labels	: 
								{
									show: true,
									name:
									{
										show	: true,
										fontSize: '10px',
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
										fontFamily	: 'Helvetica, Arial, sans-serif',
										fontWeight	: 600,
										color		: '#373d3f',
										formatter	: function (w) 
										{
											total = series.reduce((a, b) => a + b, 0)
											return "$" + new Intl.NumberFormat("es-MX").format(total)
										}
									}
								}
							}
						}
					},
					labels 		: labels,
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
								text		: entname, 
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
				chart = new ApexCharts(document.querySelector("#accountsChart"), options);
				chart.render();
			}
			else if (type == 2)
			{
				var options = 
				{
					series: 
					[{
						name: 'TOTAL',
						data: series
					}],
					chart: 
					{
						height: 650,
						type: 'bar',
						toolbar :
						{
							show : true,
							offsetX : 0,
							offsetY : 0,
							tools :
							{
								download : '<img src="{{ asset('images/charts/download.png') }}" class="ico-download" width="20">'
							},
							export:
							{
								csv:
								{
									filename: '{{$accountdesc}}',
									headerCategory: 'CATEGORIA',
									headerValue: 'VALOR'
								},
								svg:
								{
									filename: '{{$accountdesc}}',
								},
								png:
								{
									filename: '{{$accountdesc}}',
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
									exportToSVG		: "Descargar SVG",
									exportToPNG		: "Descargar PNG",
									exportToCSV		: "Descargar CSV",
									menu			: "Menú",
									selection		: "Selección",
									selectionZoom	: "Acercar Selección",
									zoomIn			: "Acercar",
									zoomOut			: "Alejar",
									pan				: "Desplazar",
									reset			: "Reestablecer",
								},
							},
						}],
					},
					theme: 
					{
						palette: 'palette1'
					},
					title: 
					{
						text		: entname,
						align		: 'left',
						margin		: 10,
						offsetX		: 0,
						offsetY		: 0,
						floating	: false,
						style 		: 
						{
							fontSize	:  '14px',
							fontWeight	:  'bold',
							fontFamily	:  undefined,
							color		:  '#263238'
						},
					},
					subtitle:
					{
						text		: ['{{$projectname}}', '{{$accountdesc}}'],
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
						bar: 
						{
							columnWidth: '60%',
							distributed: true
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
						categories: labels,
						labels: 
						{
							style: 
							{
								fontSize: '12px'
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
					}
				};
				chart = new ApexCharts(document.querySelector("#accountsChart"), options);
				chart.render();
			}
		}

		$(document).on('click','#showChartBar',function()
		{
			$('.js-enterprise, .js-account').parent().find('.form-error').remove();
			$('.mindate, .maxdate').removeClass('error');
			$('#accountsChartCircle').hide();
			$('#accountsChartBar').empty();
			idProject		= $('.js-projects').val();
			projectname		= $('.js-projects option:selected').text();
			idEnterprise	= $('.js-enterprise').val();
			enterprisename	= $('.js-enterprise option:selected').text();
			father			= $('.js-account').val();
			account			= $('.js-account option:selected').text();
			mindate			= $('input[name="mindate"]').val();
			maxdate			= $('input[name="maxdate"]').val();
			
			if (idEnterprise == "" || father == "" || mindate == "" || maxdate == "")
			{
				if(idEnterprise == "")
				{
					$('.js-enterprise').parent().append('<span class="help-block form-error">Este campo es obligatorio</span>');
				}
				if(father == "")
				{
					$('.js-account').parent().append('<span class="help-block form-error">Este campo es obligatorio</span>');
				}
				if(mindate == '')
				{
					$('.mindate').addClass('error');
				}
				if(maxdate == '')
				{
					$('.maxdate').addClass('error');
				}
			
				swal('', 'Por favor llene todos los campos', 'error');			
			}
			else if(mindate >= maxdate)
			{
				swal('', 'La fecha final no puede ser mayor a la inicial', 'error');
				$('.maxdate').removeClass('valid');
				$('.maxdate').addClass('error');
			}
			else
			{
				swal("Cargando",{
					icon				: '{{ url('images/loading.svg') }}',
					button				: false,
					closeOnClickOutside	: false,
					closeOnEsc			: false
				});
				$.ajax(
				{
					type  : 'get',
					url   : '{{ url("/report/finance/breakdown/charts") }}',
					data  : {'idEnterprise':idEnterprise,'mindate':mindate,'maxdate':maxdate,'father':father[0],'idProject':idProject,'type':type},
					success : function(data)
					{
						$.each(data,function(i, d) 
						{
							labels.push(d.description);
							series.push(d.total);
						});
						accountsChartBar(labels,series);
						$('#accountsChartBar').show();
						swal.close();
					},
					error	: function()
					{
						swal.close();
						swal('', 'Error al generar la gráfica', 'error');
					}
				});
				$('#type').attr('value','2');
				$('#enterprisename').attr('value', enterprisename);
				$('#projectname').attr('value', projectname);
				$('#accountdesc').attr('value', account);
				$('#formsearch').attr('action', '{{ route("report.breakdown.result") }}').submit();
			}
		})
		.on('click','#showChartCircle',function()
		{
			$('.js-enterprise, .js-account').parent().find('.form-error').remove();
			$('.mindate, .maxdate').removeClass('error');
			$('#accountsChartBar').hide();
			$('#accountsChartCircle').empty('');
			idProject		= $('.js-projects').val();
			projectname		= $('.js-projects option:selected').text();
			idEnterprise	= $('.js-enterprise').val();
			enterprisename	= $('.js-enterprise option:selected').text();
			father			= $('.js-account').val();
			account			= $('.js-account option:selected').text();
			mindate			= $('input[name="mindate"]').val();
			maxdate			= $('input[name="maxdate"]').val();
			
			if (idEnterprise == "" || father == "" || mindate == "" || maxdate == "")
			{
				if(idEnterprise == "")
				{
					$('.js-enterprise').parent().append('<span class="help-block form-error">Este campo es obligatorio</span>');
				}
				if(father == "")
				{
					$('.js-account').parent().append('<span class="help-block form-error">Este campo es obligatorio</span>');
				}
				if(mindate == '')
				{
					$('.mindate').addClass('error');
				}
				if(maxdate == '')
				{
					$('.maxdate').addClass('error');
				}
			
				swal('', 'Por favor llene todos los campos', 'error');
			}
			else if(mindate >= maxdate)
			{
				swal('', 'La fecha final no puede ser mayor a la inicial', 'error');
				$('.maxdate').removeClass('valid');
				$('.maxdate').addClass('error');
			}
			else
			{
				swal("Cargando",{
						icon				: '{{ asset(getenv('LOADING_IMG')) }}',
						button				: false,
						closeOnClickOutside	: false,
						closeOnEsc			: false
					});
				$('#type').attr('value','1');
				$('#enterprisename').attr('value', enterprisename);
				$('#projectname').attr('value', projectname);
				$('#accountdesc').attr('value', account);
				$('#formsearch').attr('action', '{{ route("report.breakdown.result") }}').submit();
			}
		});
	});
</script> 
@endsection


