<div class="text-center my-4">Para la carga masiva, es requerido seleccionar el <b>documento XML</b> de la factura.</div>
@component('components.containers.container-form')
	@slot('attributeEx')
		id="documents_masiva"
	@endslot
	<div class="col-span-4 md:col-start-2 md:col-span-2 md:col-end-4">
		@component('components.documents.upload-files', [
				"attributeExInput"	=> "type=\"file\" name=\"xmlfile\" accept=\".xml\"",
				"classExDelete"		=> "delete-span",
				"attributeExDelete"	=> "onclick=\"remove_doc()\""
			])
		@endcomponent
	</div>
@endcomponent
<div id="concepts_masiva">
	<div id="form_create_lot" class="hidden">
		@component('components.labels.title-divisor') DETALLES DE LOTE <span class="help-btn" id="help-btn-lote"></span> @endcomponent
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
				@component('components.inputs.select',["options" => $optionEnterprise])
					@slot('classEx')
						js-enterprises_masiva removeselect
					@endslot
					@slot('attributeEx')
						name="enterprise_id_masiva" multiple="multiple" data-validation="required"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Fecha: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" id="date_masiva" name="date_masiva" placeholder="Ingrese la fecha" data-validation="required" readonly="true"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Subtotal de Factura/Ticket: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" id="sub_total_masiva" name="sub_total_masiva" data-validation="required" placeholder="Ingrese el subtotal"
					@endslot
					@slot('classEx')
						remove inversion
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Total de Factura/Ticket: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" id="total_masiva" name="total_masiva" data-validation="required" placeholder="Ingrese el total"
					@endslot
					@slot('classEx')
						remove inversion
					@endslot
				@endcomponent
			</div>
			<div id="documentsMasiva" class="col-span-2 md:col-span-4 grid grid-cols-1 md:grid-cols-2 gap-6"></div>
			<div class="col-span-2 md:col-span-4 space-x-2 text-center md:text-left">
				@component('components.buttons.button', ["variant" => "warning"])
					@slot('attributeEx')
						type="button" name="addDocMasiva" id="addDoc"
					@endslot
					@slot('classEx')
						my-4
					@endslot
					<span class="icon-plus"></span>
					<span>Agregar documento</span>
				@endcomponent
			</div>
		 @endcomponent
		<div class="w-full mt-4 grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mb-6">
			@component('components.buttons.button', ["variant" => "reset"])
				@slot('attributeEx')
					type="button" id="clean_masiva"
				@endslot
				Cancelar
			@endcomponent
			@component('components.buttons.button', ["variant" => "red"])
				@slot('attributeEx')
					id="masiva_siguiente" type="button" name="enviar"
				@endslot
				@slot('classEx')
					enviar
				@endslot
				SIGUIENTE
			@endcomponent
		</div>
	</div>
	<div id="articles_details_form" class="hidden">
		<div id="articles_count_container" class="text-center">
			@component('components.labels.label')
				@slot('attributeEx')
					id="articles_count"
				@endslot
				@slot('classEx')
					font-semibold
				@endslot
				Artículos: 1/100
			@endcomponent
		</div>
		@component('components.labels.title-divisor') DETALLES DE ARTÍCULO @endcomponent
		@component('components.containers.container-form')
			<div class="col-span-2">
				@component('components.labels.label') Categoria: @endcomponent
				@component('components.inputs.select',[ 'options' => [] ])
					@slot('attributeEx')
						id="category_id_masiva" multiple="multiple"
					@endslot
					@slot('classEx')
						js-category-masiva removeselect
					@endslot
				@endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="hidden" id="idEnterpriseMasiva"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Cuenta: @endcomponent
				@component('components.inputs.select',[ 'options' => [] ])
					@slot('attributeEx')
						id="account_id_masiva" name="account_id_masiva" multiple="multiple"
					@endslot
					@slot('classEx')
						js-accounts-masiva removeselect
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Ubicación/Sede: @endcomponent
				@component('components.inputs.select', [ 'options' => [] ])
					@slot('attributeEx')
						name="place_id_masiva" multiple="multiple"
					@endslot
					@slot('classEx')
						js-places_masiva removeselect
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
						name="measurement_id_masiva" multiple="multiple" 
					@endslot
					@slot('classEx')
						js-measurement_masiva removeselect
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2 hidden" id="concepto_sugerido_container">
				<div class="flex">
					@component('components.inputs.checkbox')
						@slot('attributeEx')
							type="checkbox" id="concept_name_masiva_sugerido_check"
						@endslot
						@slot('classEx')
							hidden
						@endslot
						<span class="icon-check"></span>
					@endcomponent
					@component('components.labels.label') Concepto sugerido @endcomponent
				</div>
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" id="concept_name_masiva_sugerido" name="concept_name_masiva_sugerido" placeholder="Ingrese el concepto" readonly
					@endslot
					@slot('classEx')
						remove
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2" id="concept_container">
				@component('components.labels.label') Concepto: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" id="concept_name_masiva" name="concept_name_masiva" placeholder="Ingrese un concepto"
					@endslot
					@slot('classEx')
						remove
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2 options_computer_masiva hidden">
				@component('components.labels.label') Tipo: @endcomponent
				<div class="flex row mb-4 space-x-2">
					<div>
						@component('components.buttons.button-approval')
							@slot('attributeEx')
								type="radio" name="type_masiva" id="smartphone_masiva" value="1" data-validation="checkbox_group" data-validation-qty="min1"
							@endslot
							Smartphone
						@endcomponent
					</div>
					<div>
						@component('components.buttons.button-approval')
							@slot('attributeEx')
								type="radio" name="type_masiva" id="tablet_masiva" value="2" data-validation="checkbox_group" data-validation-qty="min1"
							@endslot
							Tablet
						@endcomponent
					</div>
					<div>
						@component('components.buttons.button-approval')
							@slot('attributeEx')
								type="radio" name="type_masiva" id="laptop_masiva" value="3" data-validation="checkbox_group" data-validation-qty="min1"
							@endslot
							Laptop
						@endcomponent
					</div>
					<div>
						@component('components.buttons.button-approval')
							@slot('attributeEx')
								type="radio" name="type_masiva" id="desktop_masiva" value="4" data-validation="checkbox_group" data-validation-qty="min1"
							@endslot
							Desktop
						@endcomponent
					</div>
				</div>	
			</div>
			<div class="col-span-2 options_computer_masiva hidden">
				@component('components.labels.label') Marca: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" name="brand_masiva" placeholder="Ingrese la marca"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2 options_computer_masiva hidden">
				@component('components.labels.label') Capacidad de Almacenamiento: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" name="storage_masiva" placeholder="Ingrese la capacidad"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2 options_computer_masiva hidden">
				@component('components.labels.label') Procesador: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" name="processor_masiva" placeholder="Ingrese el procesador"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2 options_computer_masiva hidden">
				@component('components.labels.label') Memoria RAM: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" name="ram_masiva" placeholder="Ingrese la memoria ram"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2 options_computer_masiva hidden">
				@component('components.labels.label') SKU: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" name="sku_masiva" placeholder="Ingrese el sku"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Código corto (Opcional): @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" id="short_code_masiva" name="short_code_masiva" placeholder="Ingrese el código corto"
					@endslot
					@slot('classEx')
						remove
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Código largo (Opcional): @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" id="long_code_masiva" name="long_code_masiva" placeholder="Ingrese el código largo"
					@endslot
					@slot('classEx')
						remove
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Cantidad de artículos no dañados: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" id="quantity_not_damaged_masiva" name="quantity_not_damaged_masiva" placeholder="Ingrese la cantidad"
					@endslot
					@slot('classEx')
						remove
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Cantidad de artículos dañados: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" id="damaged_masiva" name="damaged_masiva" placeholder="Ingrese la cantidad"
					@endslot
					@slot('classEx')
						remove
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Cantidad de artículos recibidos: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						readonly type="text" id="quantity_masiva" name="quantity_masiva" placeholder="Ingrese la cantidad"
					@endslot
					@slot('classEx')
						remove
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Precio unitario: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" id="uamount_masiva" name="uamount_masiva" placeholder="Ingrese el precio"
					@endslot
					@slot('classEx')
						remove
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Tipo de IVA: @endcomponent
				<div class="flex row mb-4 space-x-2">
					<div>
						@component('components.buttons.button-approval')
							@slot('attributeEx')
								type="radio" name="masiva_iva_kind" id="masiva_iva_no" value="no" checked="checked"
							@endslot
							@slot('classEx')
								masiva_iva_kind
							@endslot
							No
						@endcomponent
					</div>
					<div>
						@component('components.buttons.button-approval')
							@slot('attributeEx')
								type="radio" name="masiva_iva_kind" id="masiva_iva_a" value="a"
							@endslot
							@slot('classEx')
								masiva_iva_kind
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
								type="radio" name="masiva_iva_kind" id="masiva_iva_b" value="b"
							@endslot
							@slot('classEx')
								masiva_iva_kind
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
						readonly type="text" id="amount_masiva" name="amount_masiva" placeholder="Ingrese el importe"
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
						id="commentaries_masiva" name="commentaries_masiva" cols="20" rows="4" placeholder="Ingrese el comentario"
					@endslot
				@endcomponent
			</div>
		@endcomponent
		<div class="w-full mt-4 grid grid-cols-1 md:flex justify-items-center md:justify-center items-center">
			@component('components.buttons.button',["variant" => "reset"])
				@slot('attributeEx')
					type="button" id="clean_masiva"
				@endslot
				CANCELAR
			@endcomponent
			@component('components.buttons.button', ["varian" => "primary"])
				@slot('attributeEx')
					type="button" id="masiva_send_article"
				@endslot
				@slot('classEx')
					enviar
				@endslot
				ENVIAR
			@endcomponent
		</div>
	</div>
</div>

