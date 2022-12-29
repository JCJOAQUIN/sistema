@extends('layouts.child_module')
@section('data')
	@component("components.labels.title-divisor") BUSCAR @endcomponent
	@component("components.forms.searchForm",["attributeEx" => "id=\"formsearch\"","variant" => "default"])
		@slot("contentEx")
			<div class="col-span-2">
				@component("components.labels.label") Categoría: @endcomponent
				@php
					$options	= collect();
					if(isset($cat) && $cat == "computo")
					{
						$options	= $options->concat([["value" => "computo", "selected" => "selected", "description" => "Equipo de cómputo"]]);
					}
					else
					{
						$options	= $options->concat([["value" => "computo", "description" => "Equipo de cómputo"]]);
					}

					foreach (App\CatWarehouseType::all() as $w)
					{
						$description = strlen($w->description) >= 35 ? substr(strip_tags($w->description),0,35)."..." : $w->description;
						if(isset($cat) && $w->id == $cat)
						{
							$options = $options->concat([["value"=>$w->id, "selected"=>"selected", "description"=>$description]]);
						}
						else
						{
							$options = $options->concat([["value"=>$w->id, "description"=>$description]]);
						}
					}
				@endphp
				@component("components.inputs.select", 
				[
					"options"		=> $options, 
					"attributeEx"	=> "name=\"cat\" title=\"Categoría\" multiple=\"multiple\"", 
					"classEx"		=> "js-cat"
				])
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Ubicación/Sede: @endcomponent
				@php
					$options	= collect();
					if(isset($place_id))
					{
						$place			= App\Place::find($place_id);
						$description	= strlen($place->place) >= 35 ? substr(strip_tags($place->place),0,35)."..." : $place->place;
						$options		= $options->concat([["value"=>$place->id, "selected"=>"selected", "description"=>$description]]);
					}
				@endphp
				@component("components.inputs.select", 
				[
					"options"		=> $options, 
					"attributeEx"	=> "name=\"place_id\" title=\"Ubicación/Sede\" multiple=\"multiple\"", 
					"classEx"		=> "js-places"
				])
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Empresa: @endcomponent
				@php
					$options = collect();
					foreach(App\Enterprise::where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeEnt($option_id)->pluck('enterprise_id'))->orderBy('name','asc')->get() as $e)
					{
						$description = strlen($e->name) >= 35 ? substr(strip_tags($e->name),0,35)."..." : $e->name;
						if(isset($idEnterprise) && $e->id == $idEnterprise)
						{
							$options = $options->concat([["value"=>$e->id, "selected"=>"selected", "description"=>$description]]);
						}
						else
						{
							$options = $options->concat([["value"=>$e->id, "description"=>$description]]);
						}
					}
				@endphp
				@component("components.inputs.select", 
				[
					"options"		=> $options, 
					"attributeEx"	=> "name=\"idEnterprise\" title=\"Empresa\" multiple=\"multiple\"", 
					"classEx"		=> "js-enterprises"
				])
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label')Cuenta:@endcomponent
				@php
					$optionsAccount = collect();
					if(isset($idEnterprise) && isset($account_id))
					{
						$acc = App\Account::orderNumber()->where('idEnterprise',$idEnterprise)->where('idAccAcc',$account_id)->where('selectable',1)->get();
						if(count($acc)>0)
						{
							$description	= $acc->first()->account."-".$acc->first()->description."(".$acc->first()->content.")";
							$optionsAccount	= $optionsAccount->concat([['value'=>$acc->first()->idAccAcc, 'selected'=>'selected', 'description'=>$description]]);
						}
					}
				@endphp
				@component('components.inputs.select', 
					[
						"attributeEx"	=> "title=\"Cuenta\" multiple=\"multiple\" name=\"account_id\"", 
						"classEx"		=> "js-accounts removeselect", 
						"options"		=> $optionsAccount
					]
				)
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label')Concepto:@endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type		= "text" 
						name		= "concept" 
						id			= "title" 
						placeholder	= "Ingrese una descripción" 
						value		= "{{ isset($concept) ? $concept : '' }}"
					@endslot
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
							"input_classEx"		=> "input-text-date",
							"input_attributeEx"	=> "name=\"mindate\" placeholder=\"Desde\" value=\"".$mindate."\"",
						],
						[
							"input_classEx"		=> "input-text-date",
							"input_attributeEx"	=> "name=\"maxdate\" placeholder=\"Hasta\" value=\"".$maxdate."\"",
						]
					];
				@endphp
				@component("components.inputs.range-input",["inputs" => $inputs]) @endcomponent
			</div>
		@endslot
		@if (isset($warehouses) && count($warehouses) > 0)
			@slot("export")
				<div class="float-right">
					@component('components.labels.label')
						@component('components.buttons.button',['variant' => 'success'])
							@slot('attributeEx') 
								type=submit 
								formaction={{ route('warehouse.stationery.excel') }} @endslot
							@slot('label')
								<span>Exportar</span><span class="icon-file-excel"></span> 
							@endslot
						@endcomponent
					@endcomponent
				</div>
			@endslot
		@endif
	@endcomponent
	@if (isset($cat) && $cat != "" && $cat != "computo") 
		@if(count($warehouses) > 0)
			@php
				$body 		= [];
				$modelBody 	= [];
				$modelHead 	= 
				[
					[
						["value" => "Cantidad"],
						["value" => "Concepto"],
						["value" => "Ubicación"],
						["value" => "Acción"]
					]
				];
				foreach($warehouses as $warehouse)
				{
					
					$body = [
						[
							"content" =>
							[
								"label" => $warehouse->quantity
							]
						],
						[
							"content" =>
							[
								"label" => $warehouse->cat_c()->exists() ? htmlentities($warehouse->cat_c->description) : ""
							]
						],
						[
							"content" =>
							[
								"label" => $warehouse->location()->exists() ? $warehouse->location->place : ""
							]
						],
						[
							"content" =>
							[
								"kind"          => "components.buttons.button",
								"label"			=> "<span class=\"icon-search\"></span>",
								"classEx"	   	=> "follow-btn detail-stationery",
								"variant" 		=> "secondary",
								"attributeEx"  	=> "alt=\"Detalles\" title=\"Detalles\" type=\"button\" data-toggle=\"modal\" data-target=\"#myModal\"  data-concept=\"".$warehouse->concept."\" data-place-location=\"".$warehouse->place_location."\" "
							]
						]
					];
					$modelBody[] = $body;
				}
			@endphp
			@component('components.tables.table', 
				[
					"modelBody" => $modelBody,
					"modelHead" => $modelHead
				])
			@endcomponent
			{{ $warehouses->appends($_GET)->links() }}
		@else
			@component('components.labels.not-found') @endcomponent
		@endif
	@elseif (isset($cat) && $cat != "" && $cat == "computo")
		@php
			$body 		= [];
			$modelBody 	= [];
			$modelHead 	= 
			[
				[
					["value" => "Cantidad"],
					["value" => "Producto/Material"],
					["value" => "Marca"],
					["value" => "Empresa"],
					["value" => "Cuenta"],
					["value" => "Ubicación/sede"],
					["value" => "Acción"]
				]
			];
			foreach($computers as $computer)
			{
				$body = 
				[
					[
						"content"	=>
						[
							"label"	=> $computer->quantity
						]
					],
						[
						"content"	=>
						[
							"label"	=> $computer->typeEquipment()
						]
					],
					[
						"content"	=>
						[
							"label"	=> htmlentities($computer->brand),
						]
					],
					[
						"content"	=>
						[
							"label"	=> $computer->enterprise()->exists() ? $computer->enterprise->name : ""
						]
					],

					[
						"content"	=>
						[
							"label"	=> $computer->accounts()->exists() ? $computer->accounts->fullClasificacionName() : ""
						]
					],
					[
						"content" =>
						[
							"label"	=> $computer->location()->exists() ? $computer->location->place : ""
						]
					],
					[
						"content" =>
						[
							"kind"			=> "components.buttons.button",
							"label"			=> "<span class=\"icon-search\"></span>",
							"classEx"		=> "follow-btn detail-stationery",
							"variant"		=> "secondary",
							"attributeEx"	=> "alt=\"Detalles\" title=\"Detalles\" type=\"button\" data-toggle=\"modal\" data-target=\"#myModal\"  data-concept=\"".$computer->id."\" "
						]
					]
				];
				$modelBody[] = $body;
			}
		@endphp
		@component('components.tables.table', 
			[
				"modelBody" => $modelBody,
				"modelHead" => $modelHead
			])
		@endcomponent
		{{ $computers->appends($_GET)->links() }}
	@else
		@component('components.labels.not-found') @endcomponent
	@endif
<br>
<div id="table-return"></div>
<div id="pagination"></div>
<div id="myModal" class="modal"></div>


@endsection
@section('scripts')
<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
<script src="{{ asset('js/jquery-ui.js') }}"></script>
<script type="text/javascript">
	current_selection = 1;
	$(document).ready(function()
	{
		@php
			$selects = collect(
			[
				[
					"identificator"				=> ".js-cat",
					"placeholder"				=> "Seleccione la categoría",
					"language"					=> "es",
					"maximumSelectionLength"	=> "1"
				],
				[
					"identificator"				=> ".js-enterprises",
					"placeholder"				=> "Seleccione la empresa",
					"language"					=> "es",
					"maximumSelectionLength"	=> "1"
				]
			]);
		@endphp
		@component("components.scripts.selects",["selects" => $selects])@endcomponent
		generalSelect({'selector': '.js-accounts', 'depends': '.js-enterprise', 'model': 5});
		generalSelect({'selector': '.js-places', 'model': 38});

		$(document).on('click','.send-search', function() 
		{
			cat	= $('select[name="cat"] option:selected').val();
			switch (cat) 
			{
				@foreach (App\CatWarehouseType::all() as $w)
					case "{{ $w->id }}":
				@endforeach
					send_stationery();
					break;
				case "computo":
					send_computer();
					break;
				default:
					swal({
							text: "Debe seleccionar una categoría.",
							icon: "info",
							buttons: 
							{
								confirm: true,
							},
						});
					break;
			}
		})
		.on('click','.detail-stationery', function()
		{
			concept			= $(this).attr('data-concept');
			place_location	= $(this).attr('data-place-location');
			$.ajax(
			{
				type : 'get',
				url  : '{{ route("report.warehouse.detail") }}',
				data : 
				{
					'concept'			: concept,
					'place_location'	: place_location,
					'edit'				: false
				},
				success : function(data)
				{
					$('.modal-body').html(data);
				},
				error: function()
				{
					swal('','Sucedió un error, por favor intente de nuevo.','error');
					$('#myModal').modal('hide');
				}
			});
		})
		.on('click','.exit-stationery',function()
		{
			$('#detail').slideUp();
			$('.detail').removeAttr('disabled');
			$('#myModal').hide();
		})
		.on('click','.detail-computer', function()
		{
			$id = $(this).parents('tr').find('.id').val();
			$.ajax(
			{
				type : 'post',
				url  : '{{ route("report.computer.detail") }}',
				data : {'id':$id},
				success : function(data)
				{
					$('#myModal').show().html(data);
					$('.detail-computer').attr('disabled','disabled');
				},
				error : function()
				{
					swal('','Sucedió un error, por favor intente de nuevo.','error');
					$('#myModal').hide();
				}
			})
		})
		.on('click','.exit-computer',function()
		{
			$('#detail').slideUp();
			$('.detail-computer').removeAttr('disabled');
			$('#myModal').hide();
		});

		function send_stationery(page) 
		{

			swal({
				icon: '{{ asset(getenv('LOADING_IMG')) }}',
				button: false,
			});

			category		= $('select[name="cat"] option:selected').val();
			place_id		= $('select[name="place_id"] option:selected').val();
			idEnterprise	= $('select[name="idEnterprise"] option:selected').val();
			account_id		= $('select[name="account_id"] option:selected').val();
			concept			= $('input[name="concept"]').val();
			mindate			= $('input[name="mindate"]').val();
			maxdate			= $('input[name="maxdate"]').val();

			$.ajax(
			{
				type : 'get',
				url  : '{{ url("/warehouse/stationery/table") }}',
				data : 
				{
						'idEnterprise'	: idEnterprise,
						'account_id'	: account_id,
						'place_id'		: place_id,
						'category'		: category,
						'concept'		: concept,
						'mindate'		: mindate,
						'maxdate'		: maxdate,
						'page'			: page,
				},
				success : function(response)
				{
					if(response.table.data.length === 0)
					{
						@php
							$notFound = view('components.labels.not-found')->render();
						@endphp

						notFound = '{!!preg_replace("/(\r)*(\n)*/", "", $notFound)!!}';
						input_row = $(notFound);

						$('#table-return').slideDown().html(notFound);
						$('#pagination').html(response['pagination']);
						swal.close()
						return;
					}
					lots = response.table.data;
					table = 	"<form method='get' action='{{ route('warehouse.stationery.excel') }}' accept-charset='UTF-8' id='formsearch'>";
					if(idEnterprise)
					{
						table +=	"<input type='hidden' name='enterprise_export' value='"+idEnterprise+"'>";
					}
					if(account_id)
					{
						table +=	"<input type='hidden' name='account_id_export' value='"+account_id+"'>";
					}
					if(mindate)
					{
						table +=	"<input type='hidden' name='min_export' value='"+mindate+"'>";
					}
					if(concept)
					{
						table += 	"<input type='hidden' name='concept_export' value='"+concept+"'>";
					}
					if(maxdate)
					{
						table +=	"<input type='hidden' name='max_export' value='"+maxdate+"'>";
					}
					if(category)
					{
						table += 	"<input type='hidden' name='category_export' value='"+category+"'>";
					}
					if(place_id)
					{
						table += 	"<input type='hidden' name='place_id_export' value='"+place_id+"'>";
					}
					table += 	"<div style='float: right'><label class='label-form'>Exportar a Excel </label><button class='btn btn-green export' type='submit'><span class='icon-file-excel'></span></button></div></form>";
					table += 	"<div class='table-responsive'>"+
								"<table class='table table-striped' id='table-warehouse'>"+
								"<thead class='thead-dark'>"+
								"<th>Cantidad</th>"+
								"<th>Concepto</th>"+
								"<th>Ubicación/sede</th>"+
								"<th>Acción</th>"+
								"</thead>";
					lots.forEach( lot => 
					{
						table += 	
							"<tr>"+
								"<td>"+
								""+ lot['quantity']+""+
								"</td>"+
								"<td>"+
								"" + lot['cat_c']['description'] + "" +
								"<input type='hidden' class='concept' value='" + lot['concept'] + "'>"+
								"</td>" +
								"<td>"+
									"" + (lot['place_location'] !== null ? lot['location']['place'] : "" ) + "" +
									"<input type='hidden' class='place_location' value='" + (lot['place_location'] !== null ? lot['place_location'] : "" ) + "'>"+
								"</td>"+
								"<td>" +
								"<button type='button' class='btn follow-btn detail-stationery' title='Detalles'>"+
								"<span class='icon-search'></span>"+
								"</button>"+
								"</td>"+
							"</tr>";
					})
					table += 	"</table>"+
								"</div>"+
								"<div id='detail'></div>"+
								"<br>";
					$('#pagination').html(response['pagination']);
					
					
					$('#table-return').slideDown().html(table);
					
					$('.page-link').on('click', function(e)
					{
						e.preventDefault();

						page = $(this).text();
						if($(this).text() === "›")
						{
							if(response.table.current_page + 1 > response.table.last_page)
							{
								return;
							}
							page = response.table.current_page + 1;
						}
						if($(this).text() === "‹")
						{
							if(response.table.current_page - 1 <= 0)
							{
								return;
							}
							page = response.table.current_page - 1;
						}
						send_stationery(page);
					});
					swal.close();
					
				},
				error: function(error)
				{
					swal.close();
				}
			});
		}



		function send_computer(page) 
		{

			swal({
				icon: '{{ asset(getenv('LOADING_IMG')) }}',
				button: false,
			});

			concept			= $('input[name="concept"]').val();
			type			= $('select[name="type"] option:selected').val();
			place_id		= $('select[name="place_id"] option:selected').val();
			account_id		= $('select[name="account_id"] option:selected').val();
			enterprise_id	= $('select[name="idEnterprise"] option:selected').val();
			mindate			= $('input[name="mindate"]').val();
			maxdate			= $('input[name="maxdate"]').val();

			$.ajax(
			{
				type : 'post',
				url  : '{{ route("warehouse.computer.table") }}',
				data : 
				{
					'concept'		:concept,
					'type'			:type,
					'place_id'		:place_id,
					'account_id'	:account_id,
					'enterprise_id'	:enterprise_id,
					'mindate'		:mindate,
					'maxdate'		:maxdate,
					'page'			:page
				},
				success : function(response)
				{
					if(response.table.data.length === 0)
					{
						@php
							$notFound = view('components.labels.not-found')->render();
						@endphp

						notFound = '{!!preg_replace("/(\r)*(\n)*/", "", $notFound)!!}';
						input_row = $(notFound);

						$('#table-return').slideDown().html(notFound);

						$('#pagination').html(response['pagination']);
						swal.close()
						return;
					}
					equipments	= response.table.data;
					table		= "<form method='get' action='{{ route('warehouse.computer.excel') }}' accept-charset='UTF-8' id='formsearch'>";
					if(concept)
					{
						table	+= "<input type='hidden' name='concept_export' value='"+concept+"'>";
					}
					if(type)
					{
						table	+= "<input type='hidden' name='type_export' value='"+type+"'>";
					}
					if(account_id)
					{
						table	+= "<input type='hidden' name='account_export' value='"+account_id+"'>";
					}
					if(enterprise_id)
					{
						table	+= "<input type='hidden' name='enterprise_export' value='"+enterprise_id+"'>";
					}
					if(mindate)
					{
						table	+= "<input type='hidden' name='mindate_export' value='"+mindate+"'>";
					}
					if(maxdate)
					{
						table	+= "<input type='hidden' name='maxdate_export' value='"+maxdate+"'>";
					}
					table	+= 	"<div style='float: right'><label class='label-form'>Exportar a Excel </label><button class='btn btn-green export' type='submit'><span class='icon-file-excel'></span></button></div></form>";
					table	+= 	"<div class='table-responsive'>"+
								"<table class='table table-striped' id='table-computer'>"+
								"<thead class='thead-dark'>"+
								"<th>Cantidad</th>"+
								"<th>Producto/Material</th>"+
								"<th>Marca</th>"+
								"<th>Empresa</th>"+
								"<th>Cuenta</th>"+
								"<th>Ubicación/sede</th>"+
								"<th>Acción</th>"+
								"</thead>";
					route_edit = "{{ route("warehouse.computer.edit",["id"=>0]) }}"
					equipments.forEach( equipment =>
					{
						link	= route_edit.slice(0, -1) + equipment['id']
						equip	= "Smartphone";
						switch (equipment['type']) 
						{
							case "1":
								equip = "Smartphone";
								break;
		
							case "2":
								equip = "Tablet";
								break;
		
							case "3":
								equip = "Laptop";
								break;
		
							case "4":
								equip = "Desktop";
								break;
							
							default:
								break;
						}
						table += 	"<tr>"+
									"<td>"+
									""+ equipment['quantity']+""+
									"</td>"+
									"<td>"+
									""+ equip +""+
									"<input type='hidden' class='equip' value='"+ equip +"'>"+
									"</td>"+
									"<td>"+
									""+ equipment['brand'] +""+
									"<input type='hidden' class='id' value='"+ equipment['id'] +"'>"+
									"</td>"+

									"<td>"+
									""+ (equipment['idEnterprise'] ? equipment['enterprise']['name'] : "" ) +""+
									"</td>"+
									
									"<td>"+
									""+ (equipment['account'] ? equipment['accounts']['account'] + ' '+ equipment['accounts']['description'] + ' ('+equipment['accounts']['content']+')' : "" ) +""+
									"</td>"+
									
									"<td>"+
									""+ (equipment['place_location'] ? equipment['location']['place'] : "" ) +""+
									"</td>"+

									"<td>"+
									"<button type='button' class='btn follow-btn detail-computer' title='Detalles'>"+
									"<span class='icon-search'></span>"+
									"</button>"+
									"</td>"+

									"</tr>";
					})
							
							
					table += 	"</table>"+
								"</div>"+
								"<div id='detail'></div>"+
								"<br>";
					pagination = response['pagination'].replace(/page-link/g, 'page-link-computo')

					$('#pagination').html(pagination);
					
					
					$('#table-return').slideDown().html(table);
					
					$('.page-link-computo').on('click', function(e)
					{
						e.preventDefault();
						page = $(this).text();
						if($(this).text() === "›")
						{
							if(response.table.current_page + 1 > response.table.last_page)
							{
								return;
							}
							page = response.table.current_page + 1
						}
						if($(this).text() === "‹")
						{
							if(response.table.current_page - 1 <= 0)
							{
								return;
							}
							page = response.table.current_page - 1
						}
						send_computer(page)
					});
					swal.close()
				},
				error: function(error)
				{
					swal({
						title:'Error',
						text: "Ocurrió un problema, por favor verifique su red e intente nuevamente.",
						icon:'error',
					});
				}
			});
		}
	});

	
</script>

@endsection
