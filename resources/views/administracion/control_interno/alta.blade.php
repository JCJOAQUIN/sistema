@extends('layouts.child_module')
@section('data')
	@component('components.labels.title-divisor') CONTROL INTERNO @endcomponent
	@if(!isset($request))
		<div class="flex row justify-center mt-4 mb-4 space-x-2">
			<div>
				@component('components.buttons.button-approval')
					@slot('attributeEx')
						type="radio" name="normal_massive" id="normal" value="1"
					@endslot
					Alta Normal
				@endcomponent
			</div>
			<div>
				@component('components.buttons.button-approval')
					@slot('attributeEx')
						type="radio" name="normal_massive" id="massive" value="2"	
					@endslot
					Carga Masiva
				@endcomponent
			</div>
		</div>
	@endif
	@if(isset($request))
		@component('components.forms.form', [ "attributeEx" => "method=\"POST\" id=\"container-alta\" action=\"".route('internal_control.update',$request->id)."\"", "methodEx" => "PUT", "files" => true])
	@else
		@component('components.forms.form', ["attributeEx" => "method=\"POST\" id=\"container-alta\" action=\"".route('internal_control.store')."\"", "files" => true])
	@endif
		@if(!isset($request))
			<div id="masiveid" hidden>
				@component('components.labels.title-divisor') CARGA MASIVA @endcomponent
				@component('components.labels.not-found', ["variant" => "note" ])
					<div>
						Si desea cargar el control interno de forma masiva, utilice la siguiente plantilla.
						@component('components.buttons.button', [ "variant" => "secondary"])
							@slot('buttonElement')
								a
							@endslot
							@slot('attributeEx')
								href="{{route('internal_control.download-control')}}"
							@endslot
							DESCARGAR PLANTILLA
						@endcomponent	
					</div>
					<div>
						En el archivo se indica como debe llenarse los campos para REQUISICION, ORDEN DE COMPRA, REMESA y BANCOS.
					</div>
				@endcomponent
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
						]
					];
				@endphp
				@component('components.documents.select_file_csv', 
				[
					"attributeExInput"	=> "type=\"file\" name=\"csv_file\" id=\"files\"",
					"buttons"			=> $buttons
				])
				@endcomponent
				<div class="text-center">
					@component('components.buttons.button', [ "variant" => "primary"])
						@slot('attributeEx')
							disabled
							type="submit"
							id="upload_file"
							formaction="{{ route('internal_control.store_masive') }}"
						@endslot
						CARGAR ARCHIVO
					@endcomponent
				</div>
			</div>
		@endif
		<div id="normalid" @if(!isset($request)) hidden @endif>
			<div id="content_data" class="request-validate">
				@component('components.labels.subtitle') REQUISICION @endcomponent
				@component('components.containers.container-form')
					<div class="col-span-2">
						@component('components.labels.label') Fecha Remesa: @endcomponent
						@component('components.inputs.input-text')
							@slot('attributeEx')
								type="text" name="fecha_remesa" placeholder="Ingrese una fecha" @isset($request) value="{{$request->controlRequisition->data_remittances}}" @endisset
							@endslot
							@slot('classEx')
								request-validate
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component('components.labels.label') Centro de Costos: @endcomponent
						@component('components.inputs.input-text')
							@slot('attributeEx')
								type="text" name="centro_costos" placeholder="Ingrese centro de costos" @isset($request) value="{{$request->controlRequisition->cost_center}}" @endisset
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component('components.labels.label') WBS: @endcomponent
						@component('components.inputs.input-text')
							@slot('attributeEx')
								type="text" name="wbs" placeholder="Ingrese un WBS" @isset($request) value="{{$request->controlRequisition->WBS}}" @endisset
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component('components.labels.label') Frentes: @endcomponent
						@component('components.inputs.input-text')
							@slot('attributeEx')
								type="text" name="frentes" placeholder="Ingrese frentes" @isset($request) value="{{$request->controlRequisition->frentes}}" @endisset
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component('components.labels.label') EDT: @endcomponent
						@component('components.inputs.input-text')
							@slot('attributeEx')
							type="text" name="edt" placeholder="Ingrese el EDT" @isset($request) value="{{$request->controlRequisition->EDT}}" @endisset
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component('components.labels.label') Tipo de Costo: @endcomponent
						@component('components.inputs.input-text')
							@slot('attributeEx')
								type="text" name="tipo_costo" placeholder="Ingrese tipo de costo" @isset($request) value="{{$request->controlRequisition->cost_type}}" @endisset
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component('components.labels.label') Descripcion de Costo: @endcomponent
						@component('components.inputs.input-text')
							@slot('attributeEx')
								type="text" name="descripcion_costo" placeholder="Ingrese la descripcion de costo" @isset($request) value="{{$request->controlRequisition->cost_description}}" @endisset
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component('components.labels.label') Area de Trabajo: @endcomponent
						@component('components.inputs.input-text')
							@slot('attributeEx')
								type="text" name="area_trabajo" placeholder="Ingrese area de trabajo" @isset($request) value="{{$request->controlRequisition->work_area}}" @endisset
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component('components.labels.label') Fecha de Requisicion: @endcomponent
						@component('components.inputs.input-text')
							@slot('attributeEx')
								type="text" name="fecha_requisicion" placeholder="Ingrese una fecha" @isset($request) value="{{$request->controlRequisition->data_requisition}}" @endisset
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component('components.labels.label') Requisicion: @endcomponent
						@component('components.inputs.input-text')
							@slot('attributeEx')
								type="text" name="requisicion" placeholder="Ingrese una requisicion" @isset($request) value="{{$request->controlRequisition->requisition}}" @endisset
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component('components.labels.label') Solicitante: @endcomponent
						@component('components.inputs.input-text')
							@slot('attributeEx')
								type="text" name="solicitante" placeholder="Ingrese un solicitante" @isset($request) value="{{$request->controlRequisition->applicant}}" @endisset
							@endslot
						@endcomponent
					</div>
				@endcomponent
				@component('components.labels.subtitle') ORDEN DE COMPRA @endcomponent
				@component('components.containers.container-form')
					<div class="col-span-2">
						@component('components.labels.label') Fecha de OC: @endcomponent
						@component('components.inputs.input-text')
							@slot('attributeEx')
								type="text" name="fecha_oc" placeholder="Ingrese una fecha" @isset($request) value="{{$request->controlPurchaseOrder->data}}" @endisset
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component('components.labels.label') Número de Orden de Compra: @endcomponent
						@component('components.inputs.input-text')
							@slot('attributeEx')
								type="text" name="numero_oc" placeholder="Ingrese un número de onder" @isset($request) value="{{$request->controlPurchaseOrder->number}}" @endisset
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component('components.labels.label') Proveedor: @endcomponent
						@component('components.inputs.input-text')
							@slot('attributeEx')
								type="text" name="proveedor" placeholder="Ingrese un proveedor" @isset($request) value="{{$request->controlPurchaseOrder->provider}}" @endisset
							@endslot
						@endcomponent
					</div>
				@endcomponent
				@component('components.labels.subtitle') REMESA @endcomponent
				@component('components.containers.container-form')
					<div class="col-span-2">
						@component('components.labels.label') Remesa: @endcomponent
						@component('components.inputs.input-text')
							@slot('attributeEx')
								type="text" name="remesa" placeholder="Ingrese una remesa" @isset($request) value="{{$request->controlRemittance->remittances}}" @endisset
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component('components.labels.label') Fecha: @endcomponent
						@component('components.inputs.input-text')
							@slot('attributeEx')
								type="text" name="fecha" placeholder="Ingrese una fecha" @isset($request) value="{{$request->controlRemittance->data}}" @endisset
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component('components.labels.label') Factura: @endcomponent
						@component('components.inputs.input-text')
							@slot('attributeEx')
								type="text" name="factura" placeholder="Ingrese una factura" @isset($request) value="{{$request->controlRemittance->invoice}}" @endisset
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component('components.labels.label') Importe FACT: @endcomponent
						@component('components.inputs.input-text')
							@slot('attributeEx')
								type="number" step="0.01" name="importe_fact" placeholder="Ingrese el importe" @isset($request) value="{{$request->controlRemittance->invoice_amount}}" @endisset
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component('components.labels.label') Nota de Credito: @endcomponent
						@component('components.inputs.input-text')
							@slot('attributeEx')
								type="text" name="nota_credito" placeholder="Ingrese una nota" @isset($request) value="{{$request->controlRemittance->credit_note}}" @endisset
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component('components.labels.label') Subtotal: @endcomponent
						@component('components.inputs.input-text')
							@slot('attributeEx')
								type="number" step="0.01" name="subtotal" placeholder="Ingrese el subtotal" @isset($request) value="{{$request->controlRemittance->subtotal}}" @endisset
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component('components.labels.label') Retencion/Descuento: @endcomponent
						@component('components.inputs.input-text')
							@slot('attributeEx')
								type="number" step="0.01" name="descuento" placeholder="Ingrese el descuento" @isset($request) value="{{$request->controlRemittance->discount}}" @endisset
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component('components.labels.label') IVA: @endcomponent
						@component('components.inputs.input-text')
							@slot('attributeEx')
								type="number" step="0.01" name="IVA" placeholder="Ingrese el IVA" @isset($request) value="{{$request->controlRemittance->IVA}}" @endisset
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component('components.labels.label') Total: @endcomponent
						@component('components.inputs.input-text')
							@slot('attributeEx')
								type="number" step="0.01" name="total" placeholder="Ingrese el total" @isset($request) value="{{$request->controlRemittance->total}}" @endisset
							@endslot
						@endcomponent
					</div>
				@endcomponent
				@component('components.labels.subtitle') BANCOS @endcomponent
				@component('components.containers.container-form')
					<div class="col-span-2">
						@component('components.labels.label') Fecha: @endcomponent
						@component('components.inputs.input-text')
							@slot('attributeEx')
								type="text" name="fecha_banco" placeholder="Ingrese una fecha" @isset($request) value="{{$request->controlBank->data}}" @endisset
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component('components.labels.label') TRASF-CH: @endcomponent
						@component('components.inputs.input-text')
							@slot('attributeEx')
								type="text" name="TRASF_CH" placeholder="Ingrese el TRASF-CH" @isset($request) value="{{$request->controlBank->TRASF_CH}}" @endisset
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component('components.labels.label') Importe: @endcomponent
						@component('components.inputs.input-text')
							@slot('attributeEx')
								type="text" name="importe" placeholder="Ingrese el importe" @isset($request) value="{{$request->controlBank->amount}}" @endisset
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
					</div>
					<div class="col-span-2">
						@component('components.labels.label') Observaciones: @endcomponent
						@component('components.inputs.text-area')
							@slot('attributeEx')
								type="text" name="observaciones" placeholder="Ingrese una observación"
							@endslot
							@isset($request) {{$request->controlBank->observations}} @endisset
						@endcomponent
					</div>
					<div class="col-span-2">
						@component('components.labels.label') Nota: @endcomponent
						@component('components.inputs.text-area')
							@slot('attributeEx')
								type="text" name="nota" placeholder="Ingrese una nota"
							@endslot
							@isset($request) {{$request->controlBank->note}} @endisset
						@endcomponent
					</div> 
				@endcomponent
			</div> 
			<div class="w-full mt-4 grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mb-6">
				@component('components.buttons.button', [
						"varian" => "primary"
					])
					@slot('attributeEx')
						type="submit"
						name="enviar"
						value="GUARDAR"
					@endslot
					@slot('classEx')
						text-center
						w-48
						md:w-auto
					@endslot
						GUARDAR
				@endcomponent
				@component('components.buttons.button', [
						"variant" => "reset"
					])
					@slot('attributeEx')
						type="reset" 
						name="borra" 
					@endslot
					@slot('classEx')
						btn-delete-form
						text-center
						w-48
						md:w-auto
					@endslot
						Borrar campos
				@endcomponent
			</div>
		</div>
	@endcomponent
@endsection

@section('scripts')
	<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
	<script src="{{ asset('js/jquery-ui.js') }}"></script>
	<script src="{{ asset('js/jquery.numeric.js') }}"></script>
	<script src="{{ asset('js/datepicker.js') }}"></script>
	<script type="text/javascript">
		$.validate(
		{
			form: '#container-alta',
			onError   : function($form)
			{
				swal('', '{{ Lang::get("messages.form_error") }}', 'error');
			},
			onSuccess : function($form)
			{
				if($('.request-validate').length>0)
				{
					conceptos	= $('#content_data div').length;
					if(conceptos>0)
					{
						swal('Cargando',{
							icon: '{{ url(getenv('LOADING_IMG')) }}',
							button: false,
						});
						return true;
					}
					else
					{
						$('#content_data div').addClass('error');
						swal('', 'Debe agregar al menos un producto', 'error');
						return false;
					}
				}
				else
				{
					swal('Cargando',{
						icon: '{{ url(getenv('LOADING_IMG')) }}',
						button: false,
					});
					return true;
				}
			}
		});
		$(document).ready(function()
		{
			$('#separatorComa').prop('checked',true);			
			$('.quantity').numeric(false);    // números
			$('.price,.amount,.tquanty,.tprice,.tamount,.descuento,.totaliva,.subtotal,.total',).numeric({ altDecimal: ".", decimalPlaces: 2 });
			$(function() 
			{
				$( "#datepicker" ).datepicker({ minDate: 0, dateFormat: "yy-mm-dd" });
				$( ".datepicker2" ).datepicker({ dateFormat: "yy-mm-dd" });
			});
			$(document).on('click','.btn-delete-form',function(e)
			{
				e.preventDefault();
				form = $(this).parents('form');
				swal({
					title		: "Limpiar formulario",
					text		: "¿Confirma que desea limpiar el formulario?",
					icon		: "warning",
					buttons		: ["Cancelar","OK"],
					dangerMode	: true,
				})
				.then((willClean) =>
				{
					if(willClean)
					{
						form[0].reset();
						$('#body').html('');
						$('.removeselect').val(null).trigger('change');
					}
					else
					{
						swal.close();
					}
				});
			}).on('change','input[name="normal_massive"]',function(){
				if ($('input[name="normal_massive"]:checked').val() == "1"){
					$('#normalid').stop(true,true).slideDown();
					$('#masiveid').stop(true,true).slideUp();
				}else if ($('input[name="normal_massive"]:checked').val() == "2"){
					$('#masiveid').stop(true,true).slideDown();
					$('#normalid').stop(true,true).slideUp();
				}
			}).on('change','#files',function(e)
			{
				label		= $(this).next('label');
				fileName	= e.target.value.split( '\\' ).pop();
				if(fileName)
				{
					label.find('span').html(fileName);
				}
				else
				{
					label.html(labelVal);
				}
				$('#upload_file').prop('disabled',false);
			})
		});
	</script>
@endsection
