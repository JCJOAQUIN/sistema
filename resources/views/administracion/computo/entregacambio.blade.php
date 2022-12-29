@extends("layouts.child_module")
@section("data")
	<div class="sm:text-center text-left my-5">
		A continuación podrá verificar la información de la solicitud antes de continuar con el proceso:
	</div>
	@php
		$modelTable = 
		[
			["Folio:", $request->folio],
			["Título y fecha:", htmlentities($request->computer->first()->title)." - ".Carbon\Carbon::createFromFormat('Y-m-d', $request->computer->first()->datetitle)->format('d-m-Y')],
			["Solicitante:", $request->requestUser->fullName()],
			["Elaborado por:", $request->elaborateUser->fullName()],
			["Empresa:", $request->requestEnterprise->name],
			["Dirección:", $request->requestDirection->name],
			["Departamento:", $request->requestDepartment->name],
			["Proyecto:", $request->requestProject->proyectName],
		];
		if($request->account)
		{
			$modelTable [] = ["Clasificación de gastos:", $request->accounts->account." - ".$request->accounts->description." (".$request->accounts->content.")"];
		}
		$modelTable [] = ["Puesto:", $request->computer->first()->position];
		if($request->computer->first()->entry==0)
		{
			$modelTable [] = ["Nuevo ingreso:", "No"];
		}
		else 
		{
			$modelTable [] = ["Nuevo ingreso:", "Si"];
			$modelTable [] = ["Fecha de ingreso:", Carbon\Carbon::createFromFormat('Y-m-d', $request->computer->first()->entry_date)->format('d-m-Y')];
		}
	@endphp
	@component("components.templates.outputs.table-detail", ["modelTable" => $modelTable, "title" => "Detalles de la Solicitud", "classEx" => "mb-4"])@endcomponent
	@component("components.labels.title-divisor") ASIGNACIÓN DE EQUIPO @endcomponent
	<div class="content-start items-start flex flex-wrap justify-center py-5 w-full mb-4">
		@switch($request->computer->first()->device)
			@case(1)
				@component("components.buttons.button-device",["variant" => "display", "label" => "Smartphone", "icon" => "<span class='icon-phone text-6xl text-white'></span>"])	@endcomponent
				@break
			@case(2)
				@component("components.buttons.button-device",["variant" => "display", "label" => "Tablet", "icon" => "<span class='icon-tablet text-6xl text-white'></span>"])	@endcomponent
				@break
			@case(3)
				@component("components.buttons.button-device",["variant" => "display", "label" => "Laptop", "icon" => "<span class='icon-laptop text-6xl text-white'></span>"])	@endcomponent
				@break
			@case(4)
				@component("components.buttons.button-device",["variant" => "display", "label" => "Computadora", "icon" => "<span class='icon-pc text-6xl text-white'></span>"])	@endcomponent
				@break
		@endswitch
	</div>
	@component("components.labels.title-divisor") ALTA / CONFIGURACIÓN DE CUENTA @endcomponent
	<div class="block overflow-auto w-full text-center mb-2">
		@php
			$heads = ["Cuentas","Alias"];
			$modelTable = [];
			foreach($request->computer->first()->computerAccounts as $account)
			{
				$modelTable[] = [
					[
						"content" =>
						[
							[
								"kind" => "components.labels.label", 
								"label" => htmlentities($account->email_account),
							],
							[
								"kind" => "components.inputs.input-text", 
								"classEx" => "idcomputerEmailsAccounts", 
								"attributeEx" => "type=\"hidden\" value=\"".$account->idcomputerEmailsAccounts."\"",
								"value" => $account->idcomputerEmailsAccounts,
							]
						],	
					],						
					[
						"content" =>
						[
							[
								"kind" => "components.labels.label",
								"label" => htmlentities($account->alias_account),
							]
						]
					],			
				];
			}
		@endphp
		@component("components.tables.alwaysVisibleTable",[
			"modelHead" => $heads,
			"modelBody" => $modelTable,
			"variant" => "default",
		])
		@endcomponent
	</div>
	@component("components.labels.title-divisor") LICENCIA Y APLICACIONES @endcomponent
	<div class="grid grid-cols-4 justify-items-center">
		<div class="md:col-start-2 col-span-4 md:col-span-2 text-left mb-6 font-bold">
			@if(isset($request->computer->first()->device))
				@foreach($request->computer->first()->software as $softwareU)
					@component("components.labels.label")
						{{ $softwareU->name }}
					@endcomponent
				@endforeach
				@if($request->computer->first()->other_software!="")
					@component("components.labels.label")
						{{ $request->computer->first()->other_software }}
					@endcomponent
				@endif
			@endif
		</div>
	</div>
	@component("components.labels.title-divisor") DATOS DE REVISIÓN @endcomponent
	@component("components.tables.table-request-detail.container",["variant" => "simple"])
		@php
			$modelTable = [ "Revisó" => $request->reviewedUser->fullName() ];
			if($request->checkComment != "")
			{
				$modelTable["Comentarios"] =  htmlentities($request->checkComment);
			}
			else 
			{
				$modelTable["Comentarios"] =  "Sin Comentarios";
			}
		@endphp
		@component("components.templates.outputs.table-detail-single", ["modelTable" => $modelTable]) 
		@endcomponent
	@endcomponent
	@component("components.labels.title-divisor") DATOS DE AUTORIZACIÓN @endcomponent
	@component("components.tables.table-request-detail.container",["variant" => "simple"])
		@php
			$modelTable = [ "Revisó" => $request->authorizedUser->fullName() ];
			if($request->authorizeComment != "")
			{
				$modelTable["Comentarios"] =  htmlentities($request->authorizeComment);
			}
			else 
			{
				$modelTable["Comentarios"] =  "Sin Comentarios";
			}
		@endphp
		@component("components.templates.outputs.table-detail-single", ["modelTable" => $modelTable])
		@endcomponent
	@endcomponent
	@component("components.labels.title-divisor") DATOS DE ENTREGA @endcomponent
	@component("components.containers.container-form")
		<div class="col-span-2 md:col-span-4 text-center">
			@php
				$device = "";
				switch ($request->computer->first()->device) 
				{
					case "1":
							$device = "Smartphone";
						break;
					case "2":
							$device = "Tablet";
						break;
					case "3":
							$device = "Laptop";
						break;
					case "4":
							$device = "Computadora";
						break;
				}	
			@endphp
			Equipo solicitado: 
			@component("components.labels.label", ["classEx" => "font-bold inline-block pr-2 article-request"])
				{{ $device }}
			@endcomponent
			Cantidad:
			@component("components.labels.label", ["classEx" => "font-bold inline-block"])
				1
			@endcomponent
			@component("components.inputs.input-text")
				@slot("attributeEx")
					type="hidden"
					value="{{ $request->computer->first()->device }}"
				@endslot
				@slot("classEx")
					material
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2 md:col-start-2">
			<div class="text-center mb-2">
				Seleccione un 
				@component("components.labels.label", ["classEx" => "font-bold inline-block"])
					Artículo del Inventario
				@endcomponent
				(Para ordenar la información dé clic en cada cabecera)
				@component("components.inputs.input-text")
					@slot("attributeEx")
						type="text"
						required
						id="search-inv"
						placeholder="Ingrese un artículo"
					@endslot
					@slot("classEx")
						mt-4
					@endslot
				@endcomponent
			</div>
			<div class="table-move">
				@php
					$modelHead =
					[
						[
							"classEx" => "arrow cantidad",
							"attributeEx" => "data-sort=\"quantity\"",
							"label" => "Cantidad <span class='icon-arrow-up'></span>"
						],
						[
							"classEx" => "arrow equipo",
							"attributeEx" => "data-sort=\"brand\"",
							"label" => "Equipo <span class='icon-arrow-up'></span>"
						],
					];
					$modelBody = [];
				@endphp
				@component("components.tables.alwaysVisibleTable",[
					"modelHead" => $modelHead,
					"modelBody" => $modelBody,
					"variant" => "default"
				])
					@slot("classEx")
						table-move
						cursor-pointer
					@endslot
					@slot("attributeEx")
						id="warehouse_request"
					@endslot
					@slot("classExHead")
						select-none
						rounded
					@endslot
					@slot("classExBody")
						body-warehouse
					@endslot
				@endcomponent
			</div>
			@component("components.inputs.input-text")
				@slot("attributeEx")
					type="hidden"
					id="pageWarehouse"
					value="{{ route("computer.requestsdelivery") }}"
				@endslot
			@endcomponent
			@component("components.inputs.input-text")
				@slot("attributeEx")
					type="hidden"
					id="kindSortWarehouse"
					value=""
				@endslot
			@endcomponent
			@component("components.inputs.input-text")
				@slot("attributeEx")
					type="hidden"
					id="ascDescWarehouse"
					value=""
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2 md:col-start-2">
			@component("components.buttons.button", ["variant"=>"warning"])
				@slot("classEx")
					add
					mb-2
				@endslot
				@slot("attributeEx")
					type="button"
				@endslot
				<span class="icon-plus"></span>
				<span>Agregar</span>
			@endcomponent
		</div>
	@endcomponent
	@component("components.forms.form", ["attributeEx" => "action=\"".route('computer.delivery.update', $request->folio)."\" method=\"POST\" id=\"container-alta\"", "methodEx" => "PUT"])
		@component("components.labels.title-divisor")    ARTÍCULOS A ENTREGAR @endcomponent
		<div class="block overflow-auto w-full text-center">
			@php
				$heads = ["Cantidad", "Producto/Material"];
				$modelTable = [];
			@endphp
			@component("components.tables.alwaysVisibleTable",[
				"modelHead" => $heads,
				"modelBody" => $modelTable,
				"variant" => "default",
			])
			@slot("classEx")
				bg-transparent
				mb-2
			@endslot
			@slot("attributeExBody")
				id="delivery"	
			@endslot
			@endcomponent
		</div>
		@component("components.labels.title-divisor") CONFIRMACIÓN @endcomponent
		<div class="text-center mt-10 mb-6">
			<div class="flex flex-col items-center">
				<div class="w-2/3 md:w-1/4 py-2.5">
					@component("components.inputs.input-text")
						@slot("attributeEx")
							type="text"
							name="code"
							id="code"
							placeholder="Ingrese el código de autorización"
							data-validation="server"
							data-validation-url="{{ route("computer.validation") }}"
							data-validation-req-params="{{ json_encode(array("oldCode"=>$request->code)) }}"
						@endslot
					@endcomponent
				</div>
			</div>
		</div>
		<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mt-8">
			@component("components.buttons.button", ["variant"=>"primary"])
				@slot("classEx")
					text-center
					w-48
					md:w-auto
				@endslot
				@slot("attributeEx")
					type="submit"
				@endslot
				ENTREGAR
			@endcomponent
			@component("components.buttons.button", ["variant"=>"reset", "buttonElement"=>"a"])
				@slot("classEx")
					text-center
					w-48
					md:w-auto
					load-actioner
				@endslot
				@slot("attributeEx")
					type="button"
					@if(isset($option_id))
						href="{{ url(App\Module::find($option_id)->url) }}"
					@else
						href="{{ url(App\Module::find($child_id)->url) }}"
					@endif
				@endslot
				REGRESAR
			@endcomponent
		</div>
	@endcomponent
@endsection
@section("scripts")
	<link rel="stylesheet" href="{{ asset("css/jquery-ui.css") }}">
	<script src="{{ asset("js/jquery-ui.js") }}"></script>
	<script>
		$(document).ready(function()
		{
			folio = @json($request->folio);
			dataSearch = [];
			dataSearch.push({'name':'folio','value':folio});
			dataSearch.push({'name':'url','value':'{{ route("computer.requestsdelivery") }}'});
			dataSearch.push({'name':'table','value':'both'});
			dataSearch.push({'name':'kindSort','value':''});
			dataSearch.push({'name':'ascDesc','value':''});
			dataSearch.push({'name':'search','value':''});
			dataSearch.push({'name':'selected','value':''});
			searchEquipments(dataSearch);
			$.validate(
			{
				modules: "security",
				form: "#container-alta",
				onError   : function($form)
				{
					swal("", '{{ Lang::get("messages.form_error") }}', "error");
				},
				onSuccess : function($form)
				{
					if($("#delivery").find(".tr").length > 0)
					{
						swal("Cargando",{
							icon: '{{ asset(getenv('LOADING_IMG')) }}',
							button: false,
							closeOnClickOutside: false,
							closeOnEsc: false
						});
						return true;
					}
					else
					{
						swal("", "Aún tiene artículos solicitados.", "error");
						return false;
					}
				}
			});
			$(document).on("click",".body-warehouse .tr",function()
			{
				$(this).parents(".body-warehouse").find('.selected').removeClass("selected");
				$(this).addClass("selected");
			})
			.on("click",".body-warehouse .tr.selected",function()
			{
				$(this).removeClass("selected");
			})
			.on("click",".arrow",function()
			{
				if($(this).parents('.table-move').find('.tr').length>0)
				{
					swal("Cargando",{
						icon: '{{ asset(getenv('LOADING_IMG')) }}',
						button: false,
						closeOnClickOutside: false,
						closeOnEsc: false
					});
					dataSearch = [];
					dataSearch.push({'name':'folio','value':folio});

					kindSort = $(this).attr('data-sort');
					if($(this).children('span').hasClass('icon-arrow-up'))
					{
						ascDesc = 'ASC';
						$(this).children('span').removeClass('icon-arrow-up').addClass('icon-arrow-down');
					}
					else
					{
						ascDesc = 'DESC';
						$(this).children('span').removeClass('icon-arrow-down').addClass('icon-arrow-up');
					}
					$('#ascDescWarehouse').val(ascDesc);
					$('#kindSortWarehouse').val(kindSort);
					selected = $(".body-warehouse .selected").find(".id").val();
					if(selected == null || selected == "")
					{
						selected = '';
					}
					dataSearch.push({'name' : 'url','value':$('#pageWarehouse').val()});
					dataSearch.push({'name' : 'table','value':'warehouse_request'});
					dataSearch.push({'name' : 'kindSort','value':kindSort});
					dataSearch.push({'name' : 'ascDesc','value':ascDesc});
					dataSearch.push({'name' : 'search','value':$('#search-inv').val()});
					dataSearch.push({'name' : 'selected','value':selected});
					searchEquipments(dataSearch);
				}
			})
			.on('click','.result_pagination a',function(e)
			{
				e.preventDefault();
				swal("Cargando",{
					icon: '{{ asset(getenv('LOADING_IMG')) }}',
					button: false,
					closeOnClickOutside: false,
					closeOnEsc: false
				});
				href    = $(this).attr('href');
				url     = new URL(href);

				dataSearch = [];
				dataSearch.push({'name':'folio','value':folio});
				dataSearch.push({'name':'url','value':url});
				dataSearch.push({'name':'table','value':'warehouse_request'});
				dataSearch.push({'name':'kindSort','value':$('#kindSortWarehouse').val()});
				dataSearch.push({'name':'ascDesc','value':$('#ascDescWarehouse').val()});
				dataSearch.push({'name':'search','value':$('#search-inv').val()});
				selected = $(".body-warehouse .selected").find(".id").val();
				if(selected == null || selected == "")
				{
					selected = '';
				}
				dataSearch.push({'name':'selected','value':selected});
				$('#pageWarehouse').val(url);
				searchEquipments(dataSearch);
			})
		    .on("keyup","#search-inv",function()
		    {
				text = $(this).val();
			
				dataSearch = [];
				dataSearch.push({'name':'folio','value':folio});
				dataSearch.push({'name':'url','value':'{{ route("computer.requestsdelivery") }}'});
				dataSearch.push({'name':'table','value':'warehouse_request'});
				dataSearch.push({'name':'kindSort','value':$('#kindSortWarehouse').val()});
				dataSearch.push({'name':'ascDesc','value':$('#ascDescWarehouse').val()});
				
				if(text != "")
				{
					$(this).parent().find(".placeholder").hide();
					dataSearch.push({'name':'search','value':text});
				}
				else
				{
					$(this).parent().find(".placeholder").show();
					dataSearch.push({'name':'search','value':''});	
				}
				
				selected = $(".body-warehouse .selected").find(".id").val();
				dataSearch.push({'name':'selected','value':selected});
				$('#pageWarehouse').val('{{ route("computer.requestsdelivery") }}');
				searchEquipments(dataSearch);
		    })
		    .on("click",".add",function()
			{
				if($("#delivery").find(".tr").length > 0)
				{
					swal('','Ya cuenta con un artículo para entregar, por favor verifique sus datos.','error');
					return false;
				}
				flagSelected = false;
				if($(".body-warehouse").find(".selected").length > 0)
				{
					flagSelected = true;
				}
				if(flagSelected)
				{
					quantityWarehouse 	= parseInt($(".body-warehouse .tr.selected").find(".quantity").val());
					idWarehouse 		= $(".body-warehouse .tr.selected").find(".id").val();
					articleRequest		= $(".article-request").text().trim();
					articleWarehouse	= $(".body-warehouse .tr.selected").find(".material").val();
					if(quantityWarehouse == "")
					{
						swal('','Por favor seleccione un artículo del inventario.','error');
					}
					if (1 > quantityWarehouse) 
					{
						swal("", "La cantidad requerida es mayor a la de inventario. \nPor favor de solicite más "+articleRequest+".", "error");
					}
					if (1 <= quantityWarehouse)
					{
						swal({
						title		: "Confirmar",
						text		: "El artículo "+articleWarehouse+" será agregado para la entrega.\n¿Desea continuar?",
						icon		: "warning",
						buttons		: ["Cancelar","OK"],
						dangerMode	: true,
						})
						.then((confirm) =>
						{
							if (confirm)
							{
								newQuantity = (quantityWarehouse - 1);
								$(".body-warehouse .tr.selected").find(".quantity").val(newQuantity);
								$(".body-warehouse .tr.selected").find(".td_quantity").html(newQuantity);
								@php
									$heads = ["Cantidad", "Producto/Material"];
									$modelBody = [];
									$modelBody =
									[
										[
											"classEx" => "tr",
											[
												"classEx" => "quantity_req",
												"content" =>
												[
													[
														"kind" 			=> "components.inputs.input-text",
														"attributeEx"	=> "readonly type=\"hidden\" name=\"tquanty[]\" value=",
														"classEx"		=> "input-table tquanty",
													],
													[
														"kind" 			=> "components.inputs.input-text",
														"attributeEx"	=> "readonly type=\"hidden\" name=\"tid_art[]\" value=",
														"classEx"		=> "input-table tid_art",
													],
												],
											],
											[
												"classEx" => "art_inv",
												"content" =>
												[
													[
														"kind" 			=> "components.inputs.input-text",
														"attributeEx"	=> "readonly type=\"hidden\" name=\"tmaterial[]\" value=",
														"classEx"		=> "input-table tdescr",
													],
												],
											],
										],
									];
									$table = view("components.tables.alwaysVisibleTable",[
										"modelHead" => $heads,
										"modelBody" => $modelBody,
										"noHead" => true,
										"variant" => "default",
									])->render();
									$table2 = html_entity_decode(preg_replace("/(\r)*(\n)*/", "", $table));
								@endphp
								table = '{!!preg_replace("/(\r)*(\n)*/", "", $table2)!!}';
								row = $(table);
								row = rowColor('#delivery',row);
								row.find(".quantity_req").prepend("1");
								row.find(".art_inv").prepend(articleWarehouse);
								row.find("[name='tquanty[]']").val(newQuantity);
								row.find("[name='tid_art[]']").val(idWarehouse);
								row.find("[name='tmaterial[]']").val(articleWarehouse);
								$("#delivery").append(row);
								$("#body-pay .tr.selected").remove();
								$(".body-warehouse .tr.selected").removeClass("selected");
								$(".add").prop("disabled", true);
							}
							else
							{
								swal.close();
							}
						});
					}
				}
				else
				{
					swal("", "Por favor seleccione un artículo.", "error");
				}
			});
		});

		function searchEquipments(dataSearch)
		{
			table = dataSearch[2]['value'];
			$.ajax(
			{
				type	: 'post',
				url		: dataSearch[1]['value'],
				data	: dataSearch,
				success	: function(data)
				{
					$('.body-warehouse').html(data);
				},
				error: function(data)
				{
					swal.close();
					swal('','Lo sentimos ocurrió un error, por favor intente de nuevo.','error');
				}
			}).done(function(data)
			{
				if($('.swal-modal').length > 0)
				{
					swal.close();
				}
				discountWarehouse();
			});
		}

		function discountWarehouse()
		{
			quantityD = $("#delivery").find('.tr').find('.tquanty').val();
			idArtD 	 = $("#delivery").find('.tr').find('.tid_art').val();
			$(".body-warehouse").find('.tr').each(function()
			{
				if($(this).find('.id').val() == idArtD)
				{
					$(this).find('.td_quantity').text(quantityD);
				}
			});
		}
	</script>
@endsection