@extends("layouts.child_module")
@section("data")
	<div class="sm:text-center text-left my-5">
		A continuación podrá verificar la información de la solicitud antes de continuar con el proceso:
	</div>
	@php
		$modelTable =
		[
			["Folio", $request->new_folio != null ? $request->new_folio : $request->folio],
			["Título y fecha", htmlentities($request->stationery->first()->title). " - " .Carbon\Carbon::createFromFormat('Y-m-d',$request->stationery->first()->datetitle)->format('d-m-Y')],
			["Solicitante", $request->requestUser->name." ".$request->requestUser->last_name." ".$request->requestUser->scnd_last_name],
			["Elaborado", $request->elaborateUser->name." ".$request->elaborateUser->last_name." ".$request->elaborateUser->scnd_last_name],
			["Empresa", $request->requestEnterprise->name],
			["Dirección", $request->requestDirection->name],
			["Departamento", $request->requestDepartment->name],
			["Proyecto", $request->requestProject()->exists() ? $request->requestProject->proyectName : ""],
			["Clasificación del Gasto", $request->accounts->account. " - " .$request->accounts->description],
		];
		if(isset($request) && $request->stationery()->first())
		{
			$value=$request->stationery()->first()->subcontractorProvider;
			if(strlen($value) > 0)
			{
				$modelTable[]=["Subcontratista/Proveedor", $value ];
			}
			else
			{
				$modelTable[]=["Subcontratista/Proveedor", "No aplica" ];
			}						
		}				
		@endphp	
	@component("components.templates.outputs.table-detail", ["modelTable" => $modelTable, "title" => "Detalles de la Solicitud"])@endcomponent
	
	@component("components.inputs.input-text")
		@slot("attributeEx")
			type="hidden"
			value="{{$request->folio}}"
		@endslot
		@slot("classEx")
			folio
		@endslot
	@endcomponent
	@component("components.inputs.input-text")
		@slot("attributeEx")
			type="hidden"
			value="{{$request->requestEnterprise->id}}"
		@endslot
		@slot("classEx")
			enterprise
		@endslot
	@endcomponent
	@component("components.labels.title-divisor") Detalles del Artículo @endcomponent
	<div class="my-4">
		@php
			$body 		= [];
			$modelBody 	= [];
			$modelHead 	= [
				[
					["value"=>"#"],
					["value"=>"Categoría"],
					["value"=>"Cantidad"],
					["value"=>"Concepto"],
					["value"=>"Código corto"],
					["value"=>"Código largo"],
					["value"=>"Comentario"],
				]
			];

			$countConcept = 1;

			foreach ($request->stationery->first()->detailStat as $key=>$detail) 
			{
				$body =
				[
					"classEx" => "tr",
					[
						"content" =>
						[
							[
								"label" => $countConcept,
							]
						]
					],
					[
						"content" =>
						[
							[
								"label" => $detail->categoryData()->exists() ? $detail->categoryData->description : "",
							]
						]
					],
					[
						"content" =>
						[
							[
								"label" => $detail->quantity,
							]
						]
					],
					[
						"content" =>
						[
							[
								"label" => htmlentities($detail->product),
							]
						]
					],
					[
						"content" =>
						[
							[
								"label" => ($detail->short_code != "" ? htmlentities($detail->short_code) : "---"),
							]
						]
					],
					[
						"content" =>
						[
							[
								"label" => ($detail->long_code != "" ? htmlentities($detail->long_code) : "---"),
							]
						]
					],
					[
						"content" =>
						[
							[
								"label" => ($detail->commentaries != "" ? htmlentities($detail->commentaries) : "---"),
							]
						]
					],
				];
				$countConcept++;
				$modelBody[] = $body; 
			}
		@endphp
		@component("components.tables.table",[
			"modelHead" 			=> $modelHead,
			"modelBody" 			=> $modelBody,
			"themeBody" 			=> "striped"
		])
			@slot("attributeExBody")
				id="body"
			@endslot
		@endcomponent
	</div>
	@component("components.labels.title-divisor") Datos de Revisión @endcomponent
	<div class="my-6">
		@component("components.tables.table-request-detail.container",["variant"=>"simple"])
			@php
				$reviewAccount = App\Account::find($request->accountR);
				$varAccounts = "";
				if(isset($reviewAccount->account))
				{
					$varAccounts = $reviewAccount->account." ".$reviewAccount->description;
				}else {
					$varAccounts = "No hay";
				}

				$varLabels = "";
				if(count($request->labels))
				{
					foreach($request->labels as $label)
					{
						$varLabels .= $label->description;
					}
				}
				else {
					$varLabels = "Sin etiqueta";
				}

				$varComment = "";
				if($request->checkComment == "")
				{
					$varComment = "Sin comentarios";
				}else {
					$varComment = htmlentities($request->checkComment);
				}
				$modelTable = [
					"Revisó" 					=> $request->reviewedUser->name." ".$request->reviewedUser->last_name." ".$request->reviewedUser->scnd_last_name,
					"Nombre de la Empresa" 		=> App\Enterprise::find($request->idEnterpriseR)->name,
					"Nombre de la Dirección" 	=> $request->reviewedDirection->name,
					"Nombre del Departamento" 	=> App\Department::find($request->idDepartamentR)->name,
					"Clasificación del gasto" 	=> $varAccounts,
					"Etiquetas"					=> $varLabels,
					"Comentarios"				=> $varComment
				];
			@endphp
		@endcomponent
		@component("components.templates.outputs.table-detail-single", ["modelTable" => $modelTable])@endcomponent
	</div>			
	@component("components.labels.title-divisor") Etiquetas Asignadas @endcomponent
	<div class="my-4">
		@php
			$heads = ["#","Cantidad","Concepto","Etiquetas"];
			$modelBody = [];
			$countConcept = 1;
			foreach($request->stationery->first()->detailStat as $key=>$detail)
			{
				$body =
				[
					"classEx" => "tr",
					[
						"content" =>
						[
							[
								"label" => $countConcept,
							]
						]
					],
					[
						"content" =>
						[
							[
								"label" => $detail->quantity,
							]
						]
					],
					[
						"content" =>
						[
							[
								"label" => htmlentities($detail->product),
							]
						]
					],
				];
				$etiquetas = "";

				foreach ($detail->labels as $label) {
					$etiquetas = $etiquetas." ".$label->label->description;
				}
				$body[] =
				[
					"content" =>
					[
						"label" => $etiquetas ? $etiquetas : "Sin etiquetas",
					]
				];
				$countConcept++;
				$modelBody[] = $body;
			}
		@endphp
		@component("components.tables.alwaysVisibleTable",[
				"modelHead" => $heads,
				"modelBody" => $modelBody,
			])
			@slot("attributeExBody")
				id="tbody-conceptsNew"
			@endslot
		@endcomponent
	</div>
	@component("components.labels.title-divisor") Datos de autorización @endcomponent
	<div class="my-6">
		@component("components.tables.table-request-detail.container",["variant"=>"simple"])
			@php
				$modelTable = [
					"Autorizó" 		=> $request->authorizedUser->name." ".$request->authorizedUser->last_name." ".$request->authorizedUser->scnd_last_name,
					"Comentarios" 	=> $request->authorizeComment = "" ? "Sin comentarios" : htmlentities($request->authorizeComment), 
				]
			@endphp
			@component("components.templates.outputs.table-detail-single", ["modelTable" => $modelTable])@endcomponent
		@endcomponent
	</div>
	@component("components.labels.title-divisor") Datos de entrega <span class="help-btn" id="help-btn-delivery"> @endcomponent
	@component("components.containers.container-form")
		<div class="col-span-2">
			<div class="text-center mb-2">
				Seleccione un Artículo Solicitado
				(Para ordenar la información dé clic en cada cabecera)
				<div class="relative">
					@component("components.inputs.input-text")
						@slot("attributeEx")
							required
							id="search-req"
						@endslot
					@endcomponent
					<div class="placeholder pointer-events-none text-true-gray-400 text-xl absolute bottom-0 left-0 right-0 m-0 mb-2">
						<span class="icon-search"></span> Buscar por descripción
					</div>
				</div>
			</div>
			<div class="overflow-y-scroll max-h-80 px-2">
				@php
					$modelHead = [
						["label" => "Categoría <span class='icon-arrow-up'></span>", "classEx" => "arrow", "attributeEx" => "data-sort=\"categoria\""],
						["label" => "Cantidad <span class='icon-arrow-up'></span>", "classEx" => "arrow", "attributeEx" => "data-sort=\"cantidad\""],
						["label" => "Concepto <span class='icon-arrow-up'></span>", "classEx" => "arrow", "attributeEx" => "data-sort=\"concepto\""],
					];
					$modelBody = [];
				@endphp
				@component('components.tables.alwaysVisibleTable',
					[
						"modelHead" => $modelHead,
						"modelBody" => $modelBody,
						"variant"	=> "default",
					])
					@slot('classEx')
						table-move
						cursor-pointer
					@endslot
					@slot('attributeEx')
						id="article-request"
					@endslot
					@slot("attributeExBody")
						id="body-pay"
					@endslot
					@slot("classExHead")
						select-none
						rounded
					@endslot
					@slot("classExBody")
						body
					@endslot
				@endcomponent
				@component("components.inputs.input-text")
					@slot("attributeEx")
						type="hidden"
						id="kindSortRequest"
						value="quantity"
					@endslot
				@endcomponent
				@component("components.inputs.input-text")
					@slot("attributeEx")
						type="hidden"
						id="ascDescRequest"
						value="ASC"
					@endslot
				@endcomponent
				@component("components.inputs.input-text")
					@slot("attributeEx")
						type="hidden"
						id="pageRequests"
						value="{{ route("stationery.articlerequest") }}"
					@endslot
				@endcomponent
			</div>			
		</div>
		<div class="col-span-2 inventary">
			<div class="text-center mb-2">
				Seleccione un Artículo del Inventario
				(Para ordenar la información dé clic en cada cabecera)
				<div class="relative">
					@component("components.inputs.input-text")
						@slot("attributeEx")
							required
							id="search-inv"
						@endslot
					@endcomponent
					<div class="placeholder pointer-events-none text-true-gray-400 text-xl absolute bottom-0 left-0 right-0 m-0 mb-2">
						<span class="icon-search"></span> Buscar por descripción
					</div>
				</div>
			</div>
			<div class="overflow-y-scroll max-h-80 px-2">
				@php
					$modelHead = [
						["label" => "Categoría <span class='icon-arrow-up'></span>", "classEx" => "arrow", "attributeEx" => "data-sort=\"categoria\""],
						["label" => "Cantidad <span class='icon-arrow-up'></span>", "classEx" => "arrow", "attributeEx" => "data-sort=\"cantidad\""],
						["label" => "Concepto <span class='icon-arrow-up'></span>", "classEx" => "arrow", "attributeEx" => "data-sort=\"concepto\""],
						["label" => "Precio unitario <span class='icon-arrow-up'></span>", "classEx" => "arrow", "attributeEx" => "data-sort=\"concepto\""],
					];
					$modelBody = [];
				@endphp
				@component('components.tables.alwaysVisibleTable',
					[
						"modelHead" => $modelHead,
						"modelBody" => $modelBody,
						"variant"	=> "default",
					])
					@slot('classEx')
						table-move
						cursor-pointer
					@endslot
					@slot('attributeEx')
						id="article-inventary"
					@endslot
					@slot("attributeExBody")
						id="body-move"
					@endslot
					@slot("classExHead")
						select-none
						rounded
					@endslot
					@slot("classExBody")
						body
					@endslot
				@endcomponent
				@component("components.inputs.input-text")
					@slot("attributeEx")
						type="hidden"
						id="kindSortInventary"
						value="quantity"
					@endslot
				@endcomponent
				@component("components.inputs.input-text")
					@slot("attributeEx")
						type="hidden"
						id="ascDescInventary"
						value="ASC"
					@endslot
				@endcomponent
				@component("components.inputs.input-text")
					@slot("attributeEx")
						type="hidden"
						id="pageInventories"
						value="{{ route("stationery.articlerequest") }}"
					@endslot
				@endcomponent
			</div>
		</div>
	@endcomponent
	@component("components.labels.title-divisor") Cantidad a entregar: @endcomponent
	<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mb-2">
		<div class="w-full sm:w-5/12 md:w-3/12 text-center">
			@component("components.inputs.input-text")
				@slot("attributeEx") 
					id="amount_delivery" 
					placeholder="Ingrese la cantidad a entregar" 
				@endslot
				@slot("classEx")
					text-center
				@endslot
			@endcomponent
		</div>
	</div>
	<div class="text-center">
		@component("components.buttons.button",["variant" => "warning"])
			@slot("classEx")
				add 
				text-center
				md:w-auto
			@endslot
			@slot("attributeEx")
				value="Agregar" 
				type="button"
			@endslot
			Agregar
		@endcomponent
	</div>
	@component("components.forms.form",["methodEx" => "PUT", "attributeEx" => "method=\"POST\" action=\"".route("stationery.delivery.update",$request->folio)."\" id=\"container-alta\""])
		@component("components.labels.title-divisor") Artículos a Entregar @endcomponent
		<div class="block overflow-auto w-full text-center">
			@php
				$heads = ["Cantidad","Producto/Material"];
				$modelTable = [];
			@endphp
			@component("components.tables.alwaysVisibleTable",[
					"modelHead" => $heads,
					"modelBody" => $modelTable,
					"variant"	=> "default",
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
		<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mb-2">
			<div class="w-full sm:w-5/12 md:w-3/12 text-center">
				@component("components.inputs.input-text")
					@slot("attributeEx")
						name="code"
						id="code"
						placeholder="Ingrese el código de autorización"
						data-validation="server"
						data-validation-url="{{ route("stationery.validation") }}"
						data-validation-req-params="{{ json_encode(array("oldCode"=>$request->code)) }}"
					@endslot
				@endcomponent
			</div>
		</div>
		<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mt-4 mb-6">
			@component("components.buttons.button",["variant" => "primary"])
				@slot("classEx")
					text-center
					md:w-auto
				@endslot
				@slot("attributeEx")
					type="submit"
					value="ENTREGAR"
					name="send"
				@endslot
				ENTREGAR
			@endcomponent
			@component('components.buttons.button', [ "buttonElement" => "a", "variant" => "reset"])
				@slot("attributeEx")
					@if(isset($option_id)) 
						href="{{ url(getUrlRedirect($option_id)) }}" 
					@else 
						href="{{ url(getUrlRedirect($child_id)) }}" 
					@endif 
				@endslot
				@slot('classEx')
					load-actioner
				@endslot
				REGRESAR 
			@endcomponent
		</div>
	@endcomponent
@endsection
@section('scripts')
	<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
	<script src="{{ asset('js/jquery-ui.js') }}"></script>
	<script src="{{ asset('js/jquery.numeric.js') }}"></script>
	<script src="{{ asset('js/datepicker.js') }}"></script>
	<script src="{{ asset('js/jquery.tablesorter.combined.js') }}"></script>
	<script>
		$('#amount_delivery').numeric({ negative : false, decimal : false });
		
		$('#article-request').tablesorter();
		$('#article-inventary').tablesorter();
		$(document).ready(function()
		{
			@php
			$selects = collect([
				[
					"identificator"        	=>".js-users", 
					"placeholder"           =>"Seleccione el solicitante", 
					"maximumSelectionLength"=>"1",
					"language"				=>"es",
				],
				[
					"identificator"        	=>".js-enterprises", 
					"placeholder"           =>"Seleccione la empresa", 
					"maximumSelectionLength"=>"1",
					"language"				=>"es",
				],
				[
					"identificator"        	=>".js-accounts", 
					"placeholder"           =>"Seleccione la clasificación del gasto", 
					"maximumSelectionLength"=>"1",
					"language"				=>"es",
				],
				[
					"identificator"        	=>".js-areas", 
					"placeholder"           =>"Seleccione la dirección", 
					"maximumSelectionLength"=>"1",
					"language"				=>"es",
				],
				[
					"identificator"        	=>".js-departments", 
					"placeholder"           =>"Seleccione el departamento", 
					"maximumSelectionLength"=>"1",
					"language"				=>"es",
				]
			]);
			@endphp
			@component("components.scripts.selects",["selects"=>$selects])
			@endcomponent

			$.validate(
			{
				modules: 'security',
				form: '#container-alta',
				onSuccess : function($form)
				{
					if($('#delivery .tr').length == 0)
					{
						
						swal('', 'Debes entregar minimo un artículo', 'error');
						return false;
					}
					else
					{
						swal('Cargando',{
							icon: '{{ asset(getenv('LOADING_IMG')) }}',
							button: false,
						});
						return true;
					}
				}
			});

			folio = $('.folio').val();
			enterprise = $('.enterprise').val();
			
			dataSearch = [];
			dataSearch.push({'name':'folio','value':folio});
			dataSearch.push({'name':'idEnterprise','value':enterprise});
			dataSearch.push({'name':'url','value':'{{ route("stationery.articlerequest") }}'});
			dataSearch.push({'name':'kindSort', 'value':''});
			dataSearch.push({'name':'ascDesc','value':''});
			dataSearch.push({'name':'table','value':'all'});
			searchArticle(dataSearch);
			$('.card_number,.destination_account,.destination_key,.employee_number').numeric(false);    // números
			$('.amount,.importe',).numeric({ altDecimal: ".", decimalPlaces: 2 });
			$('input[name="status"]').change(function()
			{
				$("#aceptar").slideDown("slow");
			})
			.on('click','#help-btn-delivery',function()
			{
				swal('Ayuda','Aqui deberá seleccionar un artículo de la lista de artículos solicitado y uno de la lista de artículos que se encuentren en inventario, posteriormente deberá dar clic en el botón "Agregar".','info');
			})
			
			$(document).on('click','#article-request .tr',function()
			{
				$(this).parents('#article-request').find('.selected').removeClass('selected');
				$(this).addClass('selected');
			})
			.on('click', '#article-request .tr.selected',function()
			{
				$(this).parents('#article-request').find('.selected').removeClass('selected');
			})
			$(document).on('click','#article-inventary .tr',function()
			{
				$(this).parents('#article-inventary').find('.selected').removeClass('selected');
				$(this).addClass('selected');
			})
			.on('click','#article-inventary .tr.selected', function()
			{
				$(this).parents('#article-inventary').find('.selected').removeClass('selected');
			})		
			.on('click','.arrow',function()
			{
				
				if($(this).parents('.table-move').find('.tr').length>0)
				{
					swal({
						icon 				: '{{ asset(getenv('LOADING_IMG')) }}',
						button             	: false,
						closeOnClickOutside	: false,
						closeOnEsc         	: false
					});

					dataSearch = [];

					kindSort = $(this).attr('data-sort');
					folio = $('.folio').val();
					enterprise = $('.enterprise').val();
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

					if($(this).parents('.table-move').attr('id') == 'article-request')
					{
						$('#ascDescRequest').val(ascDesc);
						$('#kindSortRequest').val(kindSort);
						search = $('#search-req').val();
						url	   = $('#pageRequests').val();
						table  = 'requests';	
						selected = $('#article-request .tr.selected').find('.id').val();
									
					}
					else
					{
						$('#ascDescInventary').val(ascDesc);
						$('#kindSortInventary').val(kindSort);
						search = $('#search-inv').val();
						url	   = $('#pageInventories').val();
						table  = 'inventories';

						selected = $('#article-inventary .tr.selected').find('.id').val();
						
						
					}

					dataSearch.push({'name':'folio','value':folio});
					dataSearch.push({'name':'idEnterprise','value':enterprise});
					dataSearch.push({'name':'url','value':url});
					dataSearch.push({'name':'kindSort', 'value':kindSort});
					dataSearch.push({'name':'ascDesc','value':ascDesc});
					dataSearch.push({'name':'table','value':table});
					dataSearch.push({'name':'selected','value':selected});
					searchArticle(dataSearch);
				}
					
			})
			.on('keyup','#search-req', function()
			{
				article = $(this).val().trim();
				dataSearch = [];
				dataSearch.push({'name':'folio','value':folio});
				dataSearch.push({'name':'idEnterprise','value':enterprise});
				if(article != "")
				{
					$(this).parent().find('.placeholder').hide();
					dataSearch.push({'name':'url','value':'{{ route("stationery.articlerequest") }}'});
					dataSearch.push({'name':'kindSort', 'value':$('#kindSortRequest').val()});
					dataSearch.push({'name':'ascDesc','value':$('#ascDescRequest').val()});
					dataSearch.push({'name':'table','value':'requests'});
					dataSearch.push({'name':'search','value':article});
				}
				searchArticle(dataSearch);
			})
			.on('keyup','#search-inv', function()
			{
				inventary = $(this).val().trim();
				dataSearch = [];
				dataSearch.push({'name':'folio','value':folio});
				dataSearch.push({'name':'idEnterprise','value':enterprise});
				dataSearch.push({'name':'url','value':'{{ route("stationery.articlerequest") }}'});
				dataSearch.push({'name':'kindSort', 'value':$('#kindSortRequest').val()});
				dataSearch.push({'name':'ascDesc','value':$('#ascDescRequest').val()});
				dataSearch.push({'name':'table','value':'inventories'});

				if(inventary != "")
				{
					$(this).parent().find('.placeholder').hide();
					
					dataSearch.push({'name':'search','value':inventary});
				}
				else
				{
					$(this).parent().find('.placeholder').show();
					
					dataSearch.push({'name':'search','value':''});
				}

				selected = $('#article-inventary .tr.selected').find('.id').val();
				dataSearch.push({'name':'selected','value':selected});
				searchArticle(dataSearch);
			})
			.on('click','.result_pagination a', function(e)
			{
				e.preventDefault();
				swal({
					icon               : '{{ asset(getenv('LOADING_IMG')) }}',
					button             : false,
					closeOnClickOutside: false,
					closeOnEsc         : false
				});
				href	= $(this).attr('href');
				url		= new URL(href);
				dataSearch = [];
				dataSearch.push({'name':'folio','value':folio});
				dataSearch.push({'name':'idEnterprise','value':enterprise});
				dataSearch.push({'name':'url','value':url});
				
				table    = 'inventories';
				kindSort = $('#kindSortInventary').val();
				ascDesc  = $('#ascDescInventary').val();
				search   = $('#search-inv').val();
				
				selected = $('#article-inventary .tr.selected').find('.id').val();
				$('#pageInventories').val(url);
				
				dataSearch.push({'name':'kindSort', 'value':kindSort});
				dataSearch.push({'name':'ascDesc','value':ascDesc});
				dataSearch.push({'name':'table','value':table});
				dataSearch.push({'name':'selected','value':selected});
				
				
				searchArticle(dataSearch);
			})
			.on('click','.add',function()
			{
				category_req	= parseInt($('#article-request .tr.selected').find('.category').val());
				category_inv	= parseInt($('#article-inventary .tr.selected').find('.category').val());

				quantity_req	= parseInt($('#article-request .tr.selected').find('.td_quantity').html());
				quantity_inv	= parseInt($('#article-inventary .tr.selected').find('.td_quantity').html());

				if (!quantity_req) 
				{
					swal('Error','Debe seleccionar un artículo solicitado','error');
					return
				}
				if (!quantity_inv) 
				{
					swal('Error','Debe seleccionar un artículo del almacén','error');
					return
				}

				if (category_req != category_inv) 
				{
					swal('Error','Los artículos no pertenecen a la misma categoría','error');
				}
				else
				{

					amount_delivery = $('#amount_delivery').val()


					if(amount_delivery == 0)
					{
						swal('Error','La cantidad de entrega debe ser mayor a 0','error');
						return
					}
					if(amount_delivery > quantity_inv)
					{
						swal('Error','La cantidad de entrega es mayor a la de almacén','error');
						return
					}
					if(amount_delivery > quantity_req)
					{
						swal('Error','La cantidad de entrega es mayor requerida','error');
						return
					}
					@php
						$modelHead = ["Cantidad","Producto/Material"];
						$modelBody = 
						[
							[
								"classEx" => "tr",
								[
									"content" =>
									[
										[
											"kind" => "components.labels.label",
											"classEx" => "delivery",
											"label" => ""
										],
										[
											"kind" => "components.inputs.input-text",
											"attributeEx" => "type=\"hidden\" readonly=\"true\" name=\"tquanty[]\"",
											"classEx" => "tquanty",
										],
										[
											"kind" => "components.inputs.input-text",
											"attributeEx" => "type=\"hidden\" readonly=\"true\" name=\"tid_art[]\"",
											"classEx" => "tid_art",
										],
										[
											"kind" => "components.inputs.input-text",
											"attributeEx" => "type=\"hidden\" readonly=\"true\" name=\"tid_art_req[]\"",
											"classEx" => "tid_art_req",
										],
										[
											"kind" => "components.inputs.input-text",
											"attributeEx" => "type=\"hidden\" readonly=\"true\" name=\"tid_art_delivery[]\"",
											"classEx" => "tid_art_delivery",
										]
									]
								],
								[
									"content" =>
									[
										[
											"kind" => "components.labels.label",
											"classEx" => "art_inv",
											"label" => ""
										],
										[
											"kind" => "components.inputs.input-text",
											"attributeEx" => "type=\"hidden\" readonly=\"true\" name=\"tmaterial[]\"",
											"classEx" => "tdescr",
										]
									]
								],
							]
						];
						$table_body = view("components.tables.alwaysVisibleTable",[
							"modelHead" => $modelHead,
							"modelBody" => $modelBody,
							"noHead" => true
						])->render();
						$table_body 	= html_entity_decode(preg_replace("/(\r)*(\n)*/", "", $table_body));
					@endphp

					swal({
							title		: "Confirmar",
							text		: "¿Está seguro de que desea hacer la entrega de los siguientes artículos?",
							icon		: "warning",
							buttons		: ["Cancelar","OK"],
							dangerMode	: true,
						})
					.then((continuar) =>
					{
						if(continuar)
						{
							id_art_req		= $('#article-request .tr.selected').find('.id').val();
							id_art_inv		= $('#article-inventary .tr.selected').find('.id').val();
							art_req			= $('#article-request .tr.selected').find('.material').val();
							art_inv			= $('#article-inventary .tr.selected').find('.material').val();
							
							new_request_quantity	= quantity_req - amount_delivery;
							new_quantity			= quantity_inv - amount_delivery;
							
							if(new_quantity <= 0)
							{
								new_quantity = 0
								$('#article-inventary .tr.selected').remove();
							}

							delivery = Math.abs( new_quantity -  quantity_inv)

							$('#article-inventary .tr.selected').find('.td_quantity').html(new_quantity);

							table_body = '{!!preg_replace("/(\r)*(\n)*/", "", $table_body)!!}';
							row = $(table_body);
							row = rowColor('#delivery', row);
							row.find('div').each(function()
							{
								$(this).find('.delivery').text(delivery);
								$(this).find('.tquanty').val(new_quantity);
								$(this).find('.tid_art').val(id_art_inv);
								$(this).find('.tid_art_req').val(id_art_req);
								$(this).find('.tid_art_delivery').val(delivery);
								$(this).find('.art_inv').text(art_inv);
								$(this).find('.tdescr').val(art_inv);
							})

							$('#delivery').append(row);
							$('#delivery').append(row);
							//$('#delivery').append(tr_table);
							//$('#delivery').append(tr_table);
							
							if(new_request_quantity <= 0)
							{
								$('#article-request .tr.selected').remove();
							}
							else
							{
								$('#article-request .tr.selected').find('.td_quantity').html(new_request_quantity)
							}
							$('#article-request .tr.selected').removeClass('selected')
							$('#article-inventary .tr.selected').removeClass('selected')
							$('#amount_delivery').val('')
							swal.close();
							swal('','Artículo agregado.','success')
						}
						else
						{
							swal.close();
						}
					});
				}
			})
			.on('click','#help-btn-delivery',function()
			{
				swal('Ayuda','Aqui deberá seleccionar un artículo de la lista de artículos solicitado y uno de la lista de artículos que se encuentren en inventario, posteriormente deberá dar clic en el botón "Agregar".','info');
			})
		});

		function searchArticle(dataSearch)
		{
			
			table = dataSearch[5]['value'];

			$.ajax(
			{
				type	: 'post',
				url		: dataSearch[2]['value'],
				data	: dataSearch,
				success : function(data)
				{
					json = JSON.parse(data);
					if(table == 'requests')
					{
						$('#article-request').find('#body-pay').html(urldecode(json[0]));
						$('#article-request').trigger('updateAll');
					}
					else if(table == 'inventories')
					{
						$('#article-inventary').find('#body-move').html(urldecode(json[1]));
						$('#article-inventary').trigger('updateAll');
					}
					else if(table == 'all')
					{
						$('#article-request').find('#body-pay').html(urldecode(json[0]));
						$('#article-request').trigger('updateAll');
						$('#article-inventary').find('#body-move').html(urldecode(json[1]));
						$('#article-inventary').trigger('updateAll');
					}
				},
				error : function(data)
				{
					swal("","Lo sentimos ocurrió un error, por favor intente de nuevo.","error");
				}
			}).done(function(data)
			{

				$('.inventary').find('.result_pagination').remove();
				$('#article-inventary').parent('div').parent('div').parent('div').find('.paginate-old').remove();
				page = $(json[2]);
				$('#article-inventary').parent('div').parent('div').parent('div').append(page);
				swal.close();
				
			});
		}

		function comparer(index) 
		{
			return function(a, b) 
			{
				valA = getVal(a, index), valB = getVal(b, index);
				return $.isNumeric(valA) && $.isNumeric(valB) ? valA - valB : valA.toString().localeCompare(valB);
			}
		}
		function getVal(row, index)
		{ 
			return $(row).children('.td').eq(index).text();
		}
	</script>
@endsection
