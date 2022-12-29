@component('components.forms.form', [ 'attributeEx' => "method=\"POST\" id=\"container-alta\" action=\"".route('warehouse.stationery.store')."\"", "files" => true])
	@component('components.labels.title-divisor') DETALLES DE LOTE @endcomponent
	@component('components.containers.container-form')
		<div class="col-span-2">
			@component('components.labels.label') Empresa: @endcomponent
			@php
				$optionEnterprise = [];
				foreach(App\Enterprise::where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeEnt($option_id)->pluck('enterprise_id'))->orderBy('name','asc')->get() as $enterprise)
				{
					$optionEnterprise[] = ["value" => $enterprise->id, "description" => strlen($enterprise->name) >= 35 ? substr(strip_tags($enterprise->name),0,35).'...' : $enterprise->name];
				}
			@endphp
			@component('components.inputs.select',['options' => $optionEnterprise])
				@slot('attributeEx')
					name="enterprise_id" multiple="multiple" data-validation="required"
				@endslot
				@slot('classEx')
					js-enterprises removeselect
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Fecha: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="date" id="datepicker" placeholder="Ingrese la fecha" data-validation="required" readonly="true"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Total de Factura/Ticket: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="total" data-validation="required" placeholder="Ingrese el total"
				@endslot
				@slot('classEx')
					remove inversion
				@endslot
			@endcomponent
		</div>
	@endcomponent
	@component('components.labels.title-divisor') CARGAR TICKET/FACTURA @endcomponent
	@component('components.containers.container-form')
		<div id="documents" class="col-span-2 md:col-span-4 grid-cols-1 md:grid-cols-2 gap-6 hidden"></div>
		<div class="col-span-2 md:col-span-4 space-x-2 text-center md:text-left">
			@component("components.buttons.button", ["variant" => "warning"])
				@slot('attributeEx')
					type="button" name="addDoc" id="addDoc"
				@endslot
				<span class="icon-plus"></span>
				<span>Agregar documento</span>
			@endcomponent
		</div>
	@endcomponent
	@component('components.labels.title-divisor') DETALLES DE ARTÍCULOS @endcomponent
	<div class="text-center my-4">
		@component('components.inputs.input-search') 
			@slot('attributeExInput')
				type="text"
				name="search" 
				id="input-search"
				placeholder="Ingrese un concepto" 
			@endslot
			@slot('attributeExButton')
				type="button"
			@endslot
			@slot('classExButton')
				button-search
			@endslot
			Concepto:
		@endcomponent
	</div>
	<div id="table-search-container" class="my-4">
		<div id="table-return"></div>
		<div id="pagination"></div>
	</div>
	@component('components.containers.container-form')
		<div class="col-span-2">
			@component('components.labels.label') Categoria: @endcomponent
			@component('components.inputs.select',[ 'options' => [] ])
				@slot('attributeEx')
					id="category_id" name="category_id" multiple="multiple"
				@endslot
				@slot('classEx')
					js-category removeselect
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Cuenta: @endcomponent
			@component('components.inputs.select',[ 'options' => [] ])
				@slot('attributeEx')
					name="account_id" multiple="multiple"
				@endslot
				@slot('classEx')
					js-accounts removeselect
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Ubicación/Sede: @endcomponent
			@component('components.inputs.select', [ 'options' => [] ])
				@slot('attributeEx')
					name="place_id" multiple="multiple"
				@endslot
				@slot('classEx')
					js-places removeselect
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Unidad: @endcomponent
			@php
				$optionType = [];
				foreach(App\CatMeasurementTypes::whereNotNull('type')->get() as $m_types)
				{
					foreach ($m_types->childrens()->orderBy('child_order','asc')->get() as $child)
					{
						$optionType[] = ['value' => $child->id, 'description' => $child->description ];
					}
				}
			@endphp
			@component('components.inputs.select',[ 'options' => $optionType ])
				@slot('attributeEx')
					name="measurement_id" multiple="multiple"
				@endslot
				@slot('classEx')
					js-measurement removeselect
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Concepto: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="concept_name" placeholder="Ingrese el concepto"
				@endslot
				@slot('classEx')
					remove
				@endslot
			@endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="hidden" name="concept_name_id"
				@endslot
				@slot('classEx')
					disabled
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2 options-computer hidden">
			@component('components.labels.label') Tipo: @endcomponent
			<div class="flex row mb-4 space-x-2">
				<div>
					@component('components.buttons.button-approval')
						@slot('attributeEx')
							type="radio" name="type" id="smartphone" value="1" data-validation="checkbox_group" data-validation-qty="min1"
						@endslot
						Smartphone
					@endcomponent
				</div>
				<div>
					@component('components.buttons.button-approval')
						@slot('attributeEx')
							type="radio" name="type" id="tablet" value="2" data-validation="checkbox_group" data-validation-qty="min1"
						@endslot
						Tablet
					@endcomponent
				</div>
				<div>
					@component('components.buttons.button-approval')
						@slot('attributeEx')
							type="radio" name="type" id="laptop" value="3" data-validation="checkbox_group" data-validation-qty="min1"
						@endslot
						Laptop
					@endcomponent
				</div>
				<div>
					@component('components.buttons.button-approval')
						@slot('attributeEx')
							type="radio" name="type" id="desktop" value="4" data-validation="checkbox_group" data-validation-qty="min1"
						@endslot
						Desktop
					@endcomponent
				</div>
			</div>	
		</div>
		<div class="col-span-2 options-computer hidden">
			@component('components.labels.label') Marca: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="brand" placeholder="Ingrese la marca"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2 options-computer hidden">
			@component('components.labels.label') Capacidad de Almacenamiento: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="storage" placeholder="Ingrese la capacidad"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2 options-computer hidden">
			@component('components.labels.label') Procesador: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="processor" placeholder="Ingrese el procesador"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2 options-computer hidden">
			@component('components.labels.label') Memoria RAM: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="ram" placeholder="Ingrese la memoria RAM"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2 options-computer hidden">
			@component('components.labels.label') SKU: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="sku" placeholder="Ingrese el SKU"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Código corto (Opcional): @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="short_code" placeholder="Ingrese el código corto"
				@endslot
				@slot('classEx')
					remove short_code
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Código largo (Opcional): @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="long_code" placeholder="Ingrese el código largo"
				@endslot
				@slot('classEx')
					remove long_code
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Cantidad de artículos no dañados: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="quantity_not_damaged" placeholder="Ingrese la cantidad"
				@endslot
				@slot('classEx')
					remove quantity_not_damaged
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Cantidad de artículos dañados: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="damaged" placeholder="Ingrese la cantidad"
				@endslot
				@slot('classEx')
					remove damaged
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Cantidad de artículos recibidos: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					readonly type="text" name="quantity" placeholder="Ingrese la cantidad"
				@endslot
				@slot('classEx')
					remove quantity
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Precio unitario: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="uamount" placeholder="Ingrese el precio"
				@endslot
				@slot('classEx')
					remove uamount
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Tipo de IVA: @endcomponent
			<div class="flex row mb-4 space-x-2">
				<div>
					@component('components.buttons.button-approval')
						@slot('attributeEx')
							type="radio" name="iva_kind" class="iva_kind" title="No IVA" id="iva_no" value="no" checked="checked"
						@endslot
						@slot('classEx')
							iva_kind
						@endslot
						No
					@endcomponent
				</div>
				<div>
					@component('components.buttons.button-approval')
						@slot('attributeEx')
							type="radio" name="iva_kind" class="iva_kind" id="iva_a" value="a"
						@endslot
						@slot('classEx')
							iva_kind
						@endslot
						@slot('attributeExLabel')
							title="{{App\Parameter::where('parameter_name','IVA')->first()->parameter_value}}%"
						@endslot
						A
					@endcomponent
				</div>
				<div>
					@component('components.buttons.button-approval')
						@slot('attributeEx')
							type="radio" name="iva_kind" class="iva_kind" id="iva_b" value="b"
						@endslot
						@slot('classEx')
							iva_kind
						@endslot
						@slot('attributeExLabel')
							title="{{App\Parameter::where('parameter_name','IVA2')->first()->parameter_value}}%"
						@endslot
						B
					@endcomponent
				</div>
			</div>	
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Importe: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					readonly type="text" name="amount" placeholder="Ingrese el importe"
				@endslot
				@slot('classEx')
					remove amount
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Comentario (Opcional): @endcomponent
			@component('components.inputs.text-area')
				@slot('attributeEx')
					id="commentaries" name="commentaries" cols="20" rows="4" placeholder="Ingrese el comentario"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2 md:col-span-4 grid md:flex md:items-center justify-center md:justify-start space-x-2">
			@component('components.buttons.button',['variant' => 'warning'])
				@slot('attributeEx')
					type="button" name="add" id="add"
				@endslot
				@slot('classEx')
					add2
				@endslot
				<span class="icon-plus"></span>
				<span>Agregar artículo</span>
			@endcomponent
			@component('components.buttons.button',['variant' => 'success'])
				@slot('attributeEx')
					id="edit_button" type="button" onclick="edit_material_button()"
				@endslot
				@slot('classEx')
					hidden
				@endslot
				<span class="icon-pencil"></span> <span>Editar</span>
			@endcomponent
			@component('components.buttons.button',['variant' => 'reset'])
				@slot('attributeEx')
					type="button" onclick="clean_button()"
				@endslot
				Limpiar
			@endcomponent
		</div>
	@endcomponent
	@php
		$body		= [];
		$modelBody	= [];
		$modelHead	= [
			[
				["value" => "Categoría", "classEx" => "sticky inset-x-0"],
				["value" => "Cuenta", "classEx" => "sticky inset-x-0"],
				["value" => "Concepto"],
				["value" => "Tipo"],
				["value" => "Marca"],
				["value" => "Capacidad"],
				["value" => "Procesador"],
				["value" => "Memoria RAM"],
				["value" => "sku"],
				["value" => "Unidad"],
				["value" => "Cód. corto"],
				["value" => "Cód. largo"],
				["value" => "Ubicación/sede"],
				["value" => "Cantidad"],
				["value" => "Dañados"],
				["value" => "P. unitario"],
				["value" => "IVA"],
				["value" => "Importe"],
				["value" => "Acciones"]
			]
		];
	@endphp
	@component('components.tables.table', [
			"modelBody"			=> $modelBody,
			"modelHead"			=> $modelHead,
			"attributeEx"		=> "id=\"table\"",
			"attributeExBody"	=> "id=\"body\"",
			"classExBody"		=> "request-validate"
		])
	@endcomponent
	<div id="table2">
		@php
			$modelTable =
			[
				[
					"label" => "Subtotal:", "inputsEx" =>
					[
						[
							"kind" 		=> "components.labels.label",
							"classEx"	=> "my-2 general-class subTotalLabel"
						],
						[
							"kind"			=> "components.inputs.input-text",
							"classEx" 		=> "removeselect",
							"attributeEx" 	=> "type=\"hidden\" name=\"sub_total_articles\""
						]
					]
				],
				[
					"label" => "IVA:", "inputsEx" =>
					[
						[
							"kind" 		=> "components.labels.label",
							"classEx"	=> "my-2 general-class ivaLabel"
						],
						[
							"kind"			=> "components.inputs.input-text",
							"classEx" 		=> "removeselect",
							"attributeEx" 	=> "type=\"hidden\" name=\"iva_articles\""
						]
					]
				],
				[
					"label" => "TOTAL:", "inputsEx" =>
					[
						[
							"kind" 		=> "components.labels.label",
							"classEx"	=> "my-2 general-class totalLabel"
						],
						[
							"kind"			=> "components.inputs.input-text",
							"classEx" 		=> "removeselect",
							"attributeEx" 	=> "type=\"hidden\" name=\"total_articles\""
						]
					]
				]
			];
		@endphp
		@component('components.templates.outputs.form-details', ["modelTable" => $modelTable]) @endcomponent
	</div>
	<div class="w-full mt-4 grid grid-cols-1 md:flex justify-items-center md:justify-center items-center">
		@component('components.buttons.button', ["variant" => "primary"])
			@slot('attributeEx')
				type="submit" name="enviar"
			@endslot
			@slot('classEx')
				enviar
			@endslot
			ENVIAR
		@endcomponent
		@component('components.buttons.button',["variant" => "reset"])
			@slot('attributeEx')
				type="reset" name="borra"
			@endslot
			BORRAR CAMPOS
		@endcomponent
	</div>
@endcomponent
