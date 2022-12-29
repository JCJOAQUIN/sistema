@extends('layouts.child_module')

@section('data')
	<div id="container-cambio" class="div-search">
		@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "BUSCAR"]) @endcomponent
		@php
			$values =
			[
				'enterprise_option_id' => $option_id, 
				'enterprise_id'        => $enterprise, 
				'minDate'              => isset($mindate) ? $mindate : '',
				'maxDate'              => isset($maxdate) ? $maxdate : ''
			];
			$hidden	=	['name','folio'];
		@endphp
		@component('components.forms.searchForm', ["values" => $values, "hidden" => $hidden])
			@slot('contentEx')
				<div class="col-span-2">
					@component('components.labels.label', ["label" => "Categoría:"]) @endcomponent
					@php
						$options	=	collect();
						foreach (App\CatWarehouseType::all() as $w)
						{
							if ($category == $w->id)
							{
								$options	=	$options->concat([["value"	=>	$w->id,	"description"	=>	$w->description,	"selected"	=>	"selected"]]);
							}
							else
							{
								$options	=	$options->concat([["value"	=>	$w->id,	"description"	=>	$w->description]]);
							}
						}
					@endphp
					@component('components.inputs.select', ["options" => $options,"classEx" => "js-cat", "attributeEx" => "title=\"Categoría\" name=\"cat\" multiple=\"multiple\""]) @endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label', ["label" => "Ubicación/Sede:"]) @endcomponent
					@php
						$options	=	collect();
						foreach (App\Place::where('status',1)->get() as $place)
						{
							if ($place_id == $place->id)
							{
								$options	=	$options->concat([["value"	=>	$place->id,	"description"	=>	$place->place,	"selected"	=>	"selected"]]);
							}
							else
							{
								$options	=	$options->concat([["value"	=>	$place->id,	"description"	=>	$place->place]]);
							}
						}
					@endphp
					@component('components.inputs.select', ["options" => $options,"classEx" => "js-places removeselect", "attributeEx" => "name=\"place_id\" multiple=\"multiple\""]) @endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label', ["label" => "Cuenta:"]) @endcomponent
					@php
						$options	=	collect();
						if (isset($account_id) && $account_id!="")
						{
							$accountData	=	App\Account::find($account_id);
							$options		=	$options->concat([["value"	=>	$accountData->idAccAcc,	"description"	=>	$accountData->account." - ".$accountData->description." (".$accountData->content.")",	"selected"	=>	"selected"]]);
						}
					@endphp
					@component('components.inputs.select', ["options" => $options,"classEx" => "js-accounts removeselect", "attributeEx" => "name=\"account_id\" multiple=\"multiple\" id=\"multiple-accounts select2-selection--multiple\""]) @endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label', ["label" => "Concepto:"]) @endcomponent
					@component('components.inputs.input-text', ["classEx" => "input-all", "attributeEx" => "placeholder=\"Ingrese el concepto\"  name=\"concept\" value=\"".(isset($concept) && $concept!="" ? htmlentities($concept) : "")."\""]) @endcomponent
				</div>
			@endslot
			@if (count($results) > 0)
				<div class="flex flex-row justify-end">
					@slot('export')
						@component('components.buttons.button', ["variant" => "success", "attributeEx" => "formaction=\"".route('warehouse.stationery.excel')."\"", "classEx" => "export", "label" => "Exportar a Excel <span class=\"icon-file-excel\"></span>"]) @endcomponent
					@endslot
				</div>
			@endif
		@endcomponent
	</div>
	@if (count($results) > 0)
		@php
			$modelHead	=	[];
			$body		=	[];
			$modelBody	=	[];
			$modelHead	=
			[
				[
					["value"	=>	"Cantidad"],
					["value"	=>	"Concepto"],
					["value"	=>	"Ubicación/Sede"],
					["value"	=>	"Acción"]
				]
			];
			foreach($results as $r)
			{
				$body	=
				[
					[
						"content"	=>	["label"	=>	isset($r->quantity) && $r->quantity !="" ? $r->quantity : "---"]
					],
					[
						"content"	=>	["label"	=>	isset($r->concept) && $r->concept !="" ? htmlentities($r->concept) : "---"]
					],
					[
						"content"	=>	["label"	=>	isset($r->place_location) && $r->place_location !="" ? $r->place_location : "---"]
					],
					[
						"content"	=>
						[
							[
								"kind"			=>	"components.buttons.button",
								"variant"		=>	"secondary",
								"attributeEx"	=>	"type=\"button\" data-warehouse=\"".$r->warehouse_type."\" data-concept=\"".$r->concept."\" data-place=\"".$r->place_location."\" title=\"Detalles\"",
								"label"			=>	"<span class=\"icon-search\"></span>",
								"classEx"		=>	"detail-stationery"
							]
						]
					]
				];
				$modelBody[]	=	$body;
			}
		@endphp
		@component('components.tables.table', ["modelHead" => $modelHead, "modelBody" => $modelBody, "mt-4"]) @endcomponent
		{{ $results->appends($_GET)->links() }}
	@else
		@component('components.labels.not-found') @endcomponent
	@endif
	@component("components.modals.modal", ["attributeEx" => "id=\"myModal\"", "modalTitle" => "DETALLES DE LOTE Y ARTÍCULOS"]) @endcomponent
@endsection
@section('scripts')
<script type="text/javascript">
	current_selection = 1;
	$(document).ready(function()
	{
		warehouseType	= $('.js-cat option:selected').val();
		generalSelect({'selector':'.js-accounts', 'depends':'.js-enterprise', 'model':57, 'warehouseType':warehouseType});
		@php
			$selects = collect([
				[
					"identificator"				=> ".js-cat",
					"placeholder"				=> "Selecciona la categoría",
					"language"					=> "es",
					"maximumSelectionLength"	=> "1"
				],
				[
					"identificator"				=> ".js-enterprise",
					"placeholder"				=> "Seleccione la empresa",
					"language"					=> "es",
					"maximumSelectionLength"	=> "1"
				],
				[
					"identificator"				=> ".js-places",
					"placeholder"				=> "Seleccione la ubicación/sede",
					"language"					=> "es",
					"maximumSelectionLength"	=> "1"
				],
				[
					"identificator"				=> ".js-category",
					"placeholder"				=> "Seleccione la categoría",
					"language"					=> "es",
					"maximumSelectionLength"	=> "1"
				],
			]);
		@endphp
		@component('components.scripts.selects',["selects" => $selects]) @endcomponent
	});
	$(document).on('click','.detail-stationery', function()
	{
		dataToSend = $('form').serializeArray();
		concept   = $(this).attr('data-concept');
		place     = $(this).attr('data-place');
		warehouse = $(this).attr('data-warehouse');
		dataToSend.push({name:'concept_warehouse', value: concept});
		dataToSend.push({name:'place', value: place});
		dataToSend.push({name:'warehouse_kind', value: warehouse});
		swal(
		{
			icon	: '{{ asset(getenv('LOADING_IMG')) }}',
			button	: false
		});
		$.ajax(
		{
			type : 'post',
			url  : '{{ route("report.warehouse.detail") }}',
			data : dataToSend,
			success : function(data)
			{
				swal.close()
				$('#myModal').modal('show');
				$('.modal-body').html(data);
				$('.detail').attr('disabled','disabled');
			},
			error: function (error)
			{
				swal('', 'Error al cargar, por favor intente de nuevo.', 'error');
			}
		})
	})
	$(document).on('click','.exit-stationery',function()
	{
		$('#detail').slideUp();
		$('.detail').removeAttr('disabled');
		$('#myModal').hide();
	})
	.on('click','.detail-computer', function()
	{
		$id = $(this).parents('tr').find('.id').val();
		swal(
		{
			icon	: '{{ asset(getenv('LOADING_IMG')) }}',
			button	: false
		});
		$.ajax(
		{
			type : 'post',
			url  : '{{ route("report.computer.detail") }}',
			data : {'id':$id},
			success : function(data)
			{
				swal.close()
				$('#myModal').show().html(data);
				$('.detail-computer').attr('disabled','disabled');
			},
			error: function (error) {
				swal('', 'Error al buscar, por favor intente de nuevo.', 'error');
			}
		})
	})
	.on('click','.exit-computer',function()
	{
		$('#detail').slideUp();
		$('.detail-computer').removeAttr('disabled');
		$('#myModal').hide();
	})
	.on('change','.js-enterprises,.js-cat',function()
	{
		$('.js-accounts').empty();
		warehouseType	= $('.js-cat option:selected').val();
		generalSelect({'selector':'.js-accounts', 'depends':'.js-enterprise', 'model':57, 'warehouseType':warehouseType});
	});

	function send_computer(page)
	{
		swal({
			icon: '{{ asset(getenv('LOADING_IMG')) }}',
			button: false,
		});
		concept       = $('input[name="concept"]').val();
		type          = $('select[name="type"] option:selected').val();
		place_id      = $('select[name="place_id"] option:selected').val();
		account_id    = $('select[name="account_id"] option:selected').val();
		enterprise_id = $('select[name="idEnterprise"] option:selected').val();
		mindate       = $('input[name="mindate"]').val();
		maxdate       = $('input[name="maxdate"]').val();

		$.ajax(
		{
			type : 'post',
			url  : '{{ route("warehouse.computer.table") }}',
			data : {
				'concept':concept,
				'type':type,
				'place_id':place_id,
				'account_id':account_id,
				'enterprise_id':enterprise_id,
				'mindate':mindate,
				'maxdate':maxdate,
				'page':page,
				},
			success : function(response)
			{
				if(response.table.data.length === 0){
					$('#table-return').slideDown().html("<div id='not-found' style='display:block;'>Resultado no encontrado</div>");
					$('#pagination').html(response['pagination']);
					swal.close()
					return;
				}
				equipments = response.table.data;
				table = 	"<form method='get' action='{{ route('warehouse.computer.excel') }}' accept-charset='UTF-8' id='formsearch'>";
				if(concept)
					table +=	"<input type='hidden' name='concept_export' value='"+concept+"'>";
				if(type)
					table +=	"<input type='hidden' name='type_export' value='"+type+"'>";
				if(account_id)
					table +=	"<input type='hidden' name='account_export' value='"+account_id+"'>";
				if(enterprise_id)
					table +=	"<input type='hidden' name='enterprise_export' value='"+enterprise_id+"'>";
				if(mindate)
					table +=	"<input type='hidden' name='mindate_export' value='"+mindate+"'>";
				if(maxdate)
					table +=	"<input type='hidden' name='maxdate_export' value='"+maxdate+"'>";
				table += 	"<div style='float: right'><label class='label-form'>Exportar a Excel </label><button class='btn btn-green export' type='submit'><span class='icon-file-excel'></span></button></div></form>";
				table += 	"<div class='table-responsive'>"+
							"<table class='table table-striped' id='table-computer'>"+
							"<thead class='thead-dark'>"+
							"<th>Cantidad</th>"+
							"<th>Producto/Material</th>"+
							"<th>Marca</th>"+
							"<th>Empresa</th>"+
							"<th>Cuenta</th>"+
							"<th>Ubicación/sede</th>"+
							"<th colspan='2'>Acción</th>"+
							"</thead>";
				route_edit = "{{ route("warehouse.computer.edit",["id"=>0]) }}"
				equipments.forEach( equipment =>{
					link = route_edit.slice(0, -1) + equipment['id']
					equip = "Smartphone";
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
								
								"<td>"+
								"<a alt='Ver Solicitud' title='Ver Solicitud' href='" + link +"' class='btn follow-btn'>"+
									"<span class='icon-pencil'></span>"+
								"</a>"+
								"</td>"+

								"</tr>";
				})
					
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
								
								"<td>"+
								"<a alt='Ver Solicitud' title='Ver Solicitud' href='" + link +"' class='btn follow-btn'>"+
									"<span class='icon-pencil'></span>"+
								"</a>"+
								"</td>"+

								"</tr>";
				
						
				table += 	"</table>"+
							"</div>"+
							"<div id='detail'></div>"+
							"<br>";
				pagination = response['pagination'].replace(/page-link/g, 'page-link-computo')

				$('#pagination').html(pagination);
				
				
				$('#table-return').slideDown().html(table);
				
				$('.page-link-computo').on('click', function(e){
						e.preventDefault();

						page = $(this).text();
						if($(this).text() === "›"){

							if(response.table.current_page + 1 > response.table.last_page)
								return;
							page = response.table.current_page + 1
						}
						if($(this).text() === "‹"){
							if(response.table.current_page - 1 <= 0)
								return;
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
				})
			}
		})
	}
	
	
</script>

@endsection
