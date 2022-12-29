@component('components.forms.form',["attributeEx" => "id=\"container-cambio-compras\"", "files" => true])
	@component('components.labels.title-divisor') Buscar solicitudes @endcomponent
	@component('components.containers.container-form')
		<div class="col-span-4 md:col-start-2 md:col-span-2 md:col-end-4">
			@component('components.labels.label') Descripción: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="search" value="{{ $search }}" placeholder="Ingrese un folio, título, nombre, empresa"	
				@endslot
			@endcomponent
		</div>
		<div class="col-span-4 md:col-start-2 md:col-span-2 md:col-end-4 grid justify-items-center md:justify-items-start">
			@component('components.buttons.button-search') @endcomponent
		</div>
	@endcomponent
	<div id="select_compras_container">
		<div class="flex float-right mt-4 text-right">
			@component('components.buttons.button',["variant" => "success"])
				@slot('attributeEx')
					type="submit" formaction="{{ route('warehouse.tool.purchase-export') }}"
				@endslot
				@slot('classEx')
					export
				@endslot
				<span>Exportar a Excel</span> <span class='icon-file-excel'></span>
			@endcomponent
			@component('components.buttons.button',["variant" => "success"])
				@slot('attributeEx')
					type="submit" formaction="{{ route('warehouse.tool.cat-export') }}"
				@endslot
				@slot('classEx')
					export-cat
				@endslot
				<span>Descargar catálogos</span> <span class='icon-file-excel'></span>
			@endcomponent
			@component('components.buttons.button',["variant" => "secondary"])
				@slot('attributeEx')
					data-toggle="modal" data-target="#uploadFile" type="button"
				@endslot
				<span>Cargar Archivo</span> <i class="fas fa-upload"></i>
			@endcomponent
		</div>
		@php
			$body		= [];
			$modelBody	= [];
			$modelHead	= 
			[
				[
					["value" => "Folio"],
					["value" => "Título"],
					["value" => "Solicitante"],
					["value" => "Empresa"],
					["value" => "Fecha de elaboración"],
					["value" => "Acción"]
				]
			];
			foreach($result as $item)
			{
				$body = [ "classEx" => "search_compra_".$item->folio,
					[
						"content"	=>
						[
							"label"	=> $item->folio
						]
					],
					[
						"content" =>
						[
							"label" => $item->purchases->first()->title != '' ? $item->purchases->first()->title : "No hay"
						]
					],
					[
						"content" =>
						[
							"label" => $item->elaborateUser()->exists() ? $item->elaborateUser->name.' '.$item->elaborateUser->last_name.' '.$item->elaborateUser->scnd_last_name : "---"
						]
					],
					[
						"content" =>
						[
							"label" => $item->requestEnterprise()->exists() ?  $item->requestEnterprise->name : '---'
						]
					],
					[
						"content" =>
						[
							"label" => Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$item->fDate)->format('d-m-Y')
						]
					],
					[
						"content" =>
						[
							"kind"			=> "components.buttons.button",
							"variant"		=> "success",
							"attributeEx"	=> "type=\"button\" value=\"".$item->folio."\"",
							"classEx"		=> "edit_compras",
							"label"			=> "Seleccionar",
						]
					]
				];
				$modelBody[] = $body;
			}
		@endphp	
		@component('components.tables.table',[
			"modelBody"		=> $modelBody,
			"modelHead"		=> $modelHead,
			"attributeEx"	=> "id=\"table-warehouse_search\""
		])
		@endcomponent
		<div id="pagination_compras">
			{{ $result->appends($_GET)->links() }}
		</div>
	</div>
@endcomponent
@component('components.forms.form',["attributeEx" => "id=\"container-cambio-compras-massive\" method=\"POST\"", "files" => true])
	@component('components.modals.modal', ["variant" => "large"])
		@slot('id')
			uploadFile
		@endslot
		@slot('attributeEx')
			tabindex="-1"
		@endslot
		@slot('modalTitle')
			<div class="alert alert-info">En esta sección puede cargar el archivo descargado</div>
		@endslot
		@slot('modalBody')
			@php
				$buttons = [
					"separator" => 
					[
						[
							"kind" 			=> "components.buttons.button-approval",
							"label"			=> "coma (,)",
							"attributeEx"	=> "value=\",\" name=\"separator\" id=\"separatorComa\""
						],
						[
							"kind"			=> "components.buttons.button-approval",
							"label" 		=> "punto y coma (;)",
							"attributeEx"	=> "value=\";\" name=\"separator\" id=\"separatorPuntoComa\""
						]
					],
					"buttonEx"	=>
					[
						[
							"kind"			=> "components.inputs.input-text",
							"attributeEx"	=> "type=\"hidden\" name=\"realPathPurchase\"",
							"classEx"		=> "path"
						]
					]
				];
			@endphp
			@component('components.documents.select_file_csv', 
			[
				"attributeEx"		=> "id=\"content_massive\"",
				"attributeExInput"	=> "type=\"file\" name=\"csv_file\" id=\"files\" data-validation=\"required\"",
				"buttons"			=> $buttons,
			])
			@endcomponent
		@endslot
		@slot('modalFooter')
			<div class="flex justify-center">
				@component('components.buttons.button', ["variant" => "success"])
					@slot('attributeEx')
						type="submit" formaction="{{ route('warehouse.check-massive') }}"
					@endslot
					<i class="fas fa-upload"></i> <span>Cargar Archivo</span>
				@endcomponent
				@component('components.buttons.button', ["variant" => "red"])
					@slot('attributeEx')
						type="button"
						data-dismiss="modal"
					@endslot
					<span class="icon-x"></span> <span>Cerrar</span>
				@endcomponent
			</div>
		@endslot
	@endcomponent
@endcomponent
<div id="form_compras_container" class="hidden">
	<div id="request_folio">
		@component('components.labels.title-divisor')
			@slot('classEx')
				class-folio
			@endslot
		@endcomponent
	</div>
	<div id="documents_requisition" class="hidden">
		@component('components.labels.subtitle') DOCUMENTOS @endcomponent
		@php
			$body			= [];
			$modelBody		= [];
			$modelHead		= ["Nombre","Archivo","Fecha"];
			$modelBody[]	= $body;
		@endphp
		@component('components.tables.alwaysVisibleTable',[
			"modelBody"			=> $modelBody,
			"modelHead"			=> $modelHead,
			"attributeExBody"	=> "id=\"tbody_requisition\"",
			"variant"			=> "default"
		])
		@endcomponent
	</div>
	@component('components.forms.form', ["attributeEx" => "method=\"POST\" id=\"container-alta_compras\" action=\"".route('warehouse.stationery.store.compras')."\"", "files" => true])
		@component('components.inputs.input-text')
			@slot('classEx')
				hidden
			@endslot
			@slot('attributeEx')
				name="folio"
			@endslot
		@endcomponent
		@component('components.labels.title-divisor') DETALLES DE LOTE @endcomponent
		@component('components.containers.container-form')
			<div class="col-span-2">
				@component('components.inputs.input-text')
					@slot('attributeEx')
						id="enterpise_compras"
					@endslot
					@slot('classEx')
						hidden
					@endslot
				@endcomponent
				@component('components.labels.label') Empresa: @endcomponent
				@component('components.inputs.select',[ "options" => [] ])
					@slot('attributeEx')
						name="enterprise_id_compras" multiple="multiple" id="multiple-enterprises_compras" data-validation="required"
					@endslot
					@slot('classEx')
						js-enterprises_compras removeselect
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Ubicación: @endcomponent
				@component('components.inputs.select',[ "options" => [] ])
					@slot('attributeEx')
						name="place_id_compras" multiple="multiple" data-validation="required"
					@endslot
					@slot('classEx')
						js-places_compra removeselect
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Fecha: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" name="date" id="datepicker_compras" placeholder="Ingrese la fecha" data-validation="required" readonly
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Total de solicitud: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" name="total" id="total_compras" data-validation="required" placeholder="Ingrese el total" readonly
					@endslot
					@slot('classEx')
						remove inversion
					@endslot
				@endcomponent
			</div>
		@endcomponent
		@component('components.labels.title-divisor') ARTÍCULOS @endcomponent
		<div class="flex justify-items-center text-left">
			@component('components.buttons.button',["variant" => "success"])
				@slot('classEx')
					select-all-category
				@endslot
				@slot('attributeEx')
					type="button"
				@endslot
				Seleccionar categorías (se usara la primera)
			@endcomponent
			@component('components.buttons.button',["variant" => "secondary"])
				@slot('classEx')
					select-all-acccount
				@endslot
				@slot('attributeEx')
					type="button"
				@endslot
				Seleccionar cuentas (se usara la primera)
			@endcomponent
		</div>
		<div id="table-search-container_compras_articulos">
			@php
				$modelBody	= [];
				$modelHead	= 
				[
					[
						["value" => "Categoría", "classEx" => "sticky inset-x-0"],
						["value" => "Cuenta", "classEx" => "sticky inset-x-0"],
						["value" => "Código"],
						["value" => "Concepto"],
						["value" => "Detalles"],
						["value" => "Cantidad"],
						["value" => "Dañados"],
						["value" => "Unidad"],
						["value" => "P. unitario"],
						["value" => "IVA"],
						["value" => "Importe"],
						["value" => "Acción"]
					]
				];
			@endphp
			@component("components.tables.table",[
				"modelBody"			=> $modelBody,
				"modelHead"			=> $modelHead,
				"attributeEx"		=> "id=\"table-warehouse_compras\"",
				"attributeExBody"	=> "id=\"table-return_compras_articulos\""
			])
			@endcomponent
		</div>
		@component('components.labels.title-divisor') DETALLES DEL ARTÍCULO @endcomponent
		@php
			$modelBody	= [];
			$modelHead	= 
			[
				[
					["value" => "Categoría"],
					["value" => "Cuenta"],
					["value" => "Código"],
					["value" => "Concepto"],
					["value" => "Cantidad"],
					["value" => "Dañados"],
					["value" => "Unidad"],
					["value" => "P. unitario"],
					["value" => "IVA"],
					["value" => "Importe"]
				]
			];
		@endphp
		@component('components.tables.table', [
				"modelBody"			=> $modelBody,
				"modelHead"			=> $modelHead,
				"attributeEx"		=> "id=\"table_compras\"",
				"attributeExBody"	=> "id=\"body_compras\"",
				"classExBody"		=> "request-validate"
			])
		@endcomponent
		@php
			$modelTable =
			[
				[
					"label" => "Subtotal:", "inputsEx" =>
					[
						[
							"kind" 		=> "components.labels.label",
							"classEx"	=> "my-2 subTotalLabelCompras",
							"label"		=> "$ 0.00"
						],
						[
							"kind"			=> "components.inputs.input-text",
							"attributeEx" 	=> "type=\"hidden\" name=\"sub_total_articles_compras\""
						]
					]
				],
				[
					"label" => "IVA:", "inputsEx" =>
					[
						[
							"kind" 		=> "components.labels.label",
							"classEx"	=> "my-2 ivaLabelCompras",
							"label"		=> "$ 0.00"
						],
						[
							"kind"			=> "components.inputs.input-text",
							"attributeEx" 	=> "type=\"hidden\" name=\"iva_articles_compras\""
						]
					]
				],
				[
					"label" => "TOTAL:", "inputsEx" =>
					[
						[
							"kind" 		=> "components.labels.label",
							"classEx"	=> "my-2 totalLabelCompras",
							"label"		=> "$ 0.00"
						],
						[
							"kind"			=> "components.inputs.input-text",
							"attributeEx" 	=> "type=\"hidden\" name=\"total_articles_compras\""
						]
					]
				]
			];
		@endphp
		@component('components.templates.outputs.form-details', ["modelTable" => $modelTable]) @endcomponent
		<div class="w-full mt-4 grid grid-cols-1 md:flex justify-items-center md:justify-center items-center">
			@component('components.buttons.button',["variant" => "reset"])
				@slot('attributeEx')
					type="button" id="clean_compras"
				@endslot
				Cancelar
			@endcomponent
			@component('components.buttons.button', ["variant" => "primary"])
				@slot('attributeEx')
					type="submit"  name="enviar"
				@endslot
				@slot('classEx')
					enviar
				@endslot
				ENVIAR
			@endcomponent
		</div>
	@endcomponent
	@component('components.modals.modal', ["variant" => "large"])
		@slot('id')
			modalEdit
		@endslot
		@slot('attributeEx')
			tabindex="-1"
		@endslot
		@slot('modalTitle')
			DETALLES DEL ARTÍCULO
		@endslot
		@slot('modalBody')
			@component('components.containers.container-form')
				<div class="col-span-2">
					@component('components.labels.label') Tipo: @endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							type="hidden" id="edit_articulo_modal"
						@endslot
					@endcomponent
					<div class="flex row mb-4 space-x-2">
						<div>
							@component('components.buttons.button-approval')
								@slot('attributeEx')
									type="radio" name="typeCompras" id="smartphoneCompras" value="1" data-validation="checkbox_group" data-validation-qty="min1"
								@endslot
								Smartphone
							@endcomponent
						</div>
						<div>
							@component('components.buttons.button-approval')
								@slot('attributeEx')
									type="radio" name="typeCompras" id="tabletCompras" value="2" data-validation="checkbox_group" data-validation-qty="min1"
								@endslot
								Tablet
							@endcomponent
						</div>
						<div>
							@component('components.buttons.button-approval')
								@slot('attributeEx')
									type="radio" name="typeCompras" id="laptopCompras" value="3" data-validation="checkbox_group" data-validation-qty="min1"
								@endslot
								Laptop
							@endcomponent
						</div>
						<div>
							@component('components.buttons.button-approval')
								@slot('attributeEx')
									type="radio" name="typeCompras" id="desktopCompras" value="4" data-validation="checkbox_group" data-validation-qty="min1"
								@endslot
								Desktop
							@endcomponent
						</div>
					</div>
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Marca: @endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							type="text" name="brandCompras" placeholder="Ingrese la marca"
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Capacidad de Almacenamiento: @endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							type="text" name="storageCompras" placeholder="Ingrese la capacidad"
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Procesador: @endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							type="text" name="processorCompras" placeholder="Ingrese el procesador"
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Memoria RAM: @endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							type="text" name="ramCompras" placeholder="Ingrese la memoria ram"
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') SKU: @endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							type="text" name="skuCompras" placeholder="Ingrese el sku"
						@endslot
					@endcomponent
				</div>
			@endcomponent
		@endslot
		@slot('modalFooter')
			<div class="text-center">
				@component('components.buttons.button', ["variant" => "success"])
					@slot('attributeEx')
						type="button"
					@endslot
					@slot('classEx')
						send-edit-articulo
					@endslot
					SIGUIENTE
				@endcomponent
				@component('components.buttons.button', ["variant" => "red"])
					@slot('attributeEx')
						type="button"
						data-dismiss="modal"
					@endslot
					<span class="icon-x"></span> <span>Cancelar</span>
				@endcomponent
			</div>
		@endslot
	@endcomponent
	@component('components.modals.modal', ["variant" => "large"])
		@slot('id')
			modalComentaries
		@endslot
		@slot('attributeEx')
			tabindex="-1"
		@endslot
		@slot('modalBody')
			<div class="px-6">
				@component('components.labels.label') Comentario del artículo: @endcomponent
				@component('components.inputs.text-area')
					@slot('attributeEx')
						id="modalComentairesValue" readonly="readonly"
					@endslot
				@endcomponent
			</div>
		@endslot
		@slot('modalFooter')
			<div class="flex justify-center">
				@component('components.buttons.button', ["variant" => "red"])
					@slot('attributeEx')
						type="button"
						data-dismiss="modal"
					@endslot
					<span class="icon-x"></span> <span>Cerrar</span>
				@endcomponent
			</div>
		@endslot
	@endcomponent
</div>