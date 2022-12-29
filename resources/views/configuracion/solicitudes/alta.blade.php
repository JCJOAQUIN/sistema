@extends('layouts.child_module')

@section('data')
	@php
		$taxesCount = 0;
		$taxes		= $retentions = 0;
	@endphp
	@if(isset($request))
		@component('components.forms.form',[
			"attributeEx" => "method=\"POST\" id=\"container-alta\" action=\"".route('requests.update',$request->id)."\"",
			"methodEx"    => "PUT",
			"files"       => true
		])
	@else
		@component('components.forms.form',["attributeEx" => "method=\"POST\" id=\"container-alta\" action=\"".route('requests.store')."\"", "files" => true])
	@endif
		@component('components.labels.title-divisor') DATOS DE LA SOLICITUD @endcomponent
		@component("components.labels.subtitle") Para {{ (isset($request)) ? "editar la solicitud" : "agregar una solicitud nueva" }} es necesario colocar los siguientes campos: @endcomponent
		@component('components.containers.container-form')
			<div class="col-span-2">
				@component('components.labels.label') Tipo de Solicitud: @endcomponent
				@php
					$optionKind = [];
					foreach(App\RequestKind::orderBy('kind','asc')->whereIn('idrequestkind',[1,8])->orderBy('kind','ASC')->get() as $k)
					{
						$optionKind[] =
						[	
							"value"       => $k->idrequestkind,
							"description" => $k->kind,
							"selected"    => ((isset($request) && $request->kind == $k->idrequestkind) ? "selected" : "")
						];
					}
				@endphp
				@component('components.inputs.select',['options' => $optionKind])
					@slot('attributeEx')
						name="kind" multiple="multiple" data-validation="required" @if(isset($request)) disabled="disabled" @endif
					@endslot
					@slot('classEx')
						js-kind removeselect
					@endslot
				@endcomponent
				@if(isset($request))
					@component('components.inputs.input-text')
						@slot('attributeEx')
							type="hidden" name="kind" value="{{ $request->kind }}"
						@endslot
					@endcomponent
				@endif
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Periodo: @endcomponent
				@php
					$optionPeriodicity	= [];
					$valuesPeriodicity	= [
						"weeklyOn" 		=> "Semanal",
						"twiceMonthly"	=> "Catorcenal/Quincenal",
						"monthlyOn"		=> "Mensual",
						"yearly"		=> "Anual"
					];
					foreach ($valuesPeriodicity as $k => $v)
					{
						$optionPeriodicity[] =
						[
							"value"       => $k,
							"description" => $v,
							"selected"    => ((isset($request) && $request->periodicity == $k) ? "selected" : "" )
						];
					}
				@endphp
				@component('components.inputs.select',['options' => $optionPeriodicity])
					@slot('attributeEx')
						name="periodicity" multiple="multiple" data-validation="required"
					@endslot
					@slot('classEx')
						removeselect
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2 select_day @if(isset($request) && $request->periodicity == 'monthlyOn') block @else hidden @endif">
				@component('components.labels.label') Seleccione el día: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" name="day_monthlyOn" data-validation="required" readonly placeholder="Seleccione un día" @if(isset($request) && $request->periodicity == 'monthlyOn') value="{{ $request->day_monthlyOn }}" @endif
					@endslot
					@slot('classEx')
						datepicker removeselect
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2 select_day_twiceMonthly @if(isset($request) && $request->periodicity == 'twiceMonthly') block @else hidden @endif">
				@component('components.labels.label') Primer día del mes: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" name="day_twiceMonthly_one" readonly placeholder="Seleccione un día" @if(isset($request) && $request->periodicity == 'twiceMonthly') value="{{ $request->day_twiceMonthly_one }}" @endif
					@endslot
					@slot('classEx')
						datepicker removeselect
					@endslot
				@endcomponent
				@component('components.labels.label') Segundo día del mes: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" name="day_twiceMonthly_two" readonly placeholder="Seleccione un día" @if(isset($request) && $request->periodicity == 'twiceMonthly') value="{{ $request->day_twiceMonthly_two }}" @endif
					@endslot
					@slot('classEx')
						datepicker removeselect
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2 select_day_weeklyOn @if(isset($request) && $request->periodicity == 'weeklyOn') block  @else hidden @endif">
				@component('components.labels.label') Día: @endcomponent
				@php
					$optionDay	= [];
					$valueDay	= [
						"1" => "Lunes",
						"2" => "Martes",
						"3" => "Miercoles",
						"4" => "Jueves",
						"5" => "Viernes",
						"6" => "Sábado",
						"7" => "Domingo"
					];
					foreach ($valueDay as $k => $v)
					{
						$optionDay[] =
						[
							"value"       => $k,
							"description" => $v,
							"selected"    => ((isset($request) && $request->day_weeklyOn == $k) ? "selected" : "") 
						];
					}
				@endphp
				@component('components.inputs.select',['options' => $optionDay])
					@slot('attributeEx')
						name="day_weeklyOn" multiple="multiple" data-validation="required"
					@endslot
					@slot('classEx')
						removeselect
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2 select_day_yearly @if(isset($request) && $request->periodicity == 'yearly') block  @else hidden @endif">
				@component('components.labels.label') Seleccione el día: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" name="day_yearly" data-validation="required" placeholder="Seleccione un día" @if(isset($request) && $request->periodicity == 'yearly') value="{{ $request->day_yearly }}" @endif
					@endslot
					@slot('classEx')
						datepicker removeselect
					@endslot
				@endcomponent
			</div>
		@endcomponent
		<div id="request_selected">
			@if(isset($request))
				@switch($request->kind)
					@case(1)
						@include('configuracion.solicitudes.complementos.compra')
						@break
					@case(8)
						@include('configuracion.solicitudes.complementos.recurso')
						@break
				@endswitch
			@endif
		</div>
		<div id="delete"></div>
		<div class="w-full mt-4 grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mb-6">
			@component("components.buttons.button",["variant" => "primary"])
				@slot("attributeEx")
					type="submit" name="send"
				@endslot
				@if(isset($request)) ACTUALIZAR @else REGISTRAR @endif
			@endcomponent
			@if(isset($request))
				@component('components.buttons.button',["variant" => "reset", "buttonElement" => "a"])
					@slot('attributeEx')
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
			@else
				@component("components.buttons.button",["classEx" => "btn-delete-form", "variant" => "reset"]) 
					@slot("attributeEx")
						type="reset" 
						name="borrar"
					@endslot
					Borrar campos
				@endcomponent
			@endif
		</div>
	@endcomponent
@endsection

@section('scripts')
	<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
	<script type="text/javascript" src="{{ asset('js/moment.min.js') }}"></script>
	<script src="{{ asset('js/jquery-ui.js') }}"></script>
	<script src="{{ asset('js/jquery.numeric.js') }}"></script>
	<script src="{{ asset('js/datepicker.js') }}"></script>
	<script type="text/javascript">
		$(document).ready(function()
		{
			validation();
			@ScriptSelect([ "selects" =>
				[
					[
						"identificator"          => ".js-kind", 
						"placeholder"            => "Seleccione el tipo de solicitud", 
						"language"               => "es",
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => "[name=\"periodicity\"]", 
						"placeholder"            => "Seleccione el periodo", 
						"language"               => "es",
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => "[name=\"day_weeklyOn\"]", 
						"placeholder"            => "Seleccione el día", 
						"language"               => "es",
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => "[name=\"enterpriseid\"]", 
						"placeholder"            => "Seleccione la empresa", 
						"language"               => "es",
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => "[name=\"departmentid\"]", 
						"placeholder"            => "Seleccione el departamento", 
						"language"               => "es",
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => "[name=\"areaid\"]", 
						"placeholder"            => "Seleccione la dirección", 
						"language"               => "es",
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => "[name=\"type_currency\"], .currency", 
						"placeholder"            => "Seleccione el tipo de moneda", 
						"language"               => "es",
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => "[name=\"unit\"]", 
						"placeholder"            => "Seleccione la unidad", 
						"language"               => "es",
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => "[name=\"pay_mode\"]", 
						"placeholder"            => "Seleccione la forma de pago", 
						"language"               => "es",
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => "[name=\"status_bill\"]", 
						"placeholder"            => "Seleccione el estatus", 
						"language"               => "es",
						"maximumSelectionLength" => "1"
					]
				]
			])
			@endScriptSelect
			generalSelect({'selector':'[name="userid"]','model':13});
			generalSelect({'selector':'[name="projectid"]','model':21});
			generalSelect({'selector':'[name="accountid"]','depends':'[name="enterpriseid"]', 'model':10});
			generalSelect({'selector':'#cp','model':2});
			generalSelect({'selector':'.js-state','model':31});
			generalSelect({'selector':'.js-bank','model':27});
			@component('components.scripts.taxes',['type'=>'taxes','name' => 'additional_exist','function' => 'taxesRetention']) @endcomponent
			@component('components.scripts.taxes',['type'=>'retention','name' => 'retention_new','function' => 'taxesRetention']) @endcomponent
			count	= 0;
			$('[name="quantity"],[name="price"],[name="additional_existAmount"],[name="retention_newAmount"]').on("contextmenu",function(e)
			{
				return false;
			});
			$('.phone,.clabe,.account,.cp').numeric(false);
			$('.price, .dis, .quanty').numeric({ negative : false, decimalPlaces : 2 });
			$('.amount,.tquanty,.tprice,.tamount,.descuento,.totaliva,.subtotal,.total,.additional_existAmount,.retention_newAmount,[name="total_resource"]',).numeric({ negative: false, altDecimal: ".", decimalPlaces: 2 });
			$(function() 
			{
				$("#datepicker").datepicker({ minDate: 0, dateFormat: "dd-mm-yy" });
				$('[name="day_monthlyOn"],[name="day_twiceMonthly_one"],[name="day_twiceMonthly_two"]').datepicker({dateFormat: "dd" });
				$('[name="day_yearly"]').datepicker({dateFormat: "dd-mm" });
			});
			$(document).on('change','[name="periodicity"]',function()
			{
				val = $('[name="periodicity"] option:selected').val();
				if (val != undefined) 
				{
					if (val == "monthlyOn" || val == "twiceMonthly" || val == "weeklyOn" || val == "yearly") 
					{
						if (val == "monthlyOn") 
						{
							$('.select_day').show();
						}
						if (val == "twiceMonthly") 
						{
							$('.select_day_twiceMonthly').show();
						}
						if (val == "weeklyOn") 
						{
							$('.select_day_weeklyOn').show();
							@ScriptSelect([ "selects" =>
								[
									[
										"identificator"          => "[name=\"day_weeklyOn\"]", 
										"placeholder"            => "Seleccione un dia", 
										"language"               => "es",
										"maximumSelectionLength" => "1"
									]
								]
							])
							@endScriptSelect
						}
						if (val == "yearly") 
						{
							$('.select_day_yearly').show();
						}
					}
					else
					{
						$('.select_day, .select_day_twiceMonthly, .select_day_weeklyOn, .select_day_yearly').hide();
						$('[name="day_monthlyOn"],[name="day_weeklyOn"],[name="day_twiceMonthly_one"],[name="day_twiceMonthly_two"],[name="day_yearly]').val('');
					}
				}
				else
				{
					$('.select_day, .select_day_twiceMonthly, .select_day_weeklyOn, .select_day_yearly').hide();
					$('[name="day_monthlyOn"],[name="day_weeklyOn"],[name="day_twiceMonthly_one"],[name="day_twiceMonthly_two"],[name="day_yearly]').val('');
				}
			})
			.on('click','#help-btn-select-provider',function()
			{
				swal('Ayuda','En este apartado puede crear un nuevo proveedor o buscar uno existente para hacer mas fácil el proceso de solicitud de compra.','info');
			})
			.on('change','.js-kind',function()
			{
				kind = $('.js-kind option:selected').val();
				if (kind != undefined) 
				{
					$.ajax(
					{
						type : 'post',
						url	: '{{ route("requests.select") }}',
						data : {'kind':kind},
						success : function(data)
						{
							$('#request_selected').fadeIn();
							$('#request_selected').html(data);
							generalSelect({'selector':'[name="userid"]','model':13});
							generalSelect({'selector':'[name="projectid"]','model':21});
							generalSelect({'selector':'[name="accountid"]', 'depends':'[name="enterpriseid"]', 'model':10});
							@ScriptSelect([ "selects" =>
								[
									[
										"identificator"          => "[name=\"enterpriseid\"]", 
										"placeholder"            => "Seleccione la empresa", 
										"language"               => "es",
										"maximumSelectionLength" => "1"
									],
									[
										"identificator"          => "[name=\"departmentid\"]", 
										"placeholder"            => "Seleccione el departamento", 
										"language"               => "es",
										"maximumSelectionLength" => "1"
									],
									[
										"identificator"          => "[name=\"areaid\"]", 
										"placeholder"            => "Seleccione la dirección", 
										"language"               => "es",
										"maximumSelectionLength" => "1"
									],
									[
										"identificator"          => "[name=\"type_currency\"], .currency", 
										"placeholder"            => "Seleccione el tipo de moneda", 
										"language"               => "es",
										"maximumSelectionLength" => "1"
									],
									[
										"identificator"          => "[name=\"unit\"]", 
										"placeholder"            => "Seleccione la unidad", 
										"language"               => "es",
										"maximumSelectionLength" => "1"
									],
									[
										"identificator"          => "[name=\"pay_mode\"]", 
										"placeholder"            => "Seleccione la forma de pago", 
										"language"               => "es",
										"maximumSelectionLength" => "1"
									],
									[
										"identificator"          => "[name=\"status_bill\"]", 
										"placeholder"            => "Seleccione el estatus", 
										"language"               => "es",
										"maximumSelectionLength" => "1"
									]
								]
							])
							@endScriptSelect
							$('.phone,.clabe,.account,.cp').numeric(false);
							$('.price, .dis, .quanty').numeric({ negative : false, decimalPlaces : 2 });
							$('.amount,.tquanty,.tprice,.tamount,.descuento,.totaliva,.subtotal,.total,.additional_existAmount,.retention_newAmount,[name="total_resource"]',).numeric({ negative: false, altDecimal: ".", decimalPlaces: 2 });
							$(function()
							{
								$("#datepicker").datepicker({ minDate: 0, dateFormat: "dd-mm-yy" });
							});
							$('[name="quantity"],[name="price"],[name="additional_existAmount"],[name="retention_newAmount"]').on("contextmenu",function(e)
							{
								return false;
							});
							validation();
						},
						error : function()
						{
							swal('','Sucedió un error, por favor intente de nuevo.','error');
							$('#request_selected').fadeOut();
							$('#request_selected').html('');
						}
					});
				}
				else
				{
					$('#request_selected').fadeOut();
					$('#request_selected').html('');
				}
			})
			.on('change','[name="enterpriseid"]',function()
			{
				generalSelect({'selector':'[name="accountid"]', 'depends':'[name="enterpriseid"]', 'model':10});
				$('[name="accountid"]').empty();
			})
			.on('change','input[name="prov"]',function()
			{
				if ($('input[name="prov"]:checked').val() == "nuevo") 
				{
					$('#input-search').val('');
					$("#form-prov").slideDown("slow");
					$('input[name="idProvider"]').val('');
					$('input[name="provider_data_id"]').val('');
					$('input[name="reason"]').val('').prop('disabled',false).removeAttr('data-validation-req-params').removeAttr('data-validation-error-msg');
					$('input[name="address"]').val('').prop('disabled',false);
					$('input[name="number"]').val('').prop('disabled',false);
					$('input[name="colony"]').val('').prop('disabled',false);
					$('#cp').empty().prop('disabled',false);
					$('input[name="city"]').val('').prop('disabled',false);
					$('.js-state').empty().prop('disabled',false);
					$('input[name="rfc"]').val('').prop('disabled',false).removeAttr('data-validation-req-params').removeAttr('data-validation-error-msg');
					$('input[name="phone"]').val('').prop('disabled',false);
					$('input[name="contact"]').val('').prop('disabled',false);
					$('input[name="beneficiary"]').val('').prop('disabled',false);
					$('input[name="other"]').val('').prop('disabled',false);
					$("#buscar").slideUp('fast');
					$(".checks").hide();
					$('#banks-body').html('');
					$('#banks').show();
					$('#banks-body .delete-item').show();
					generalSelect({'selector':'.js-state','model':31});
					generalSelect({'selector':'#cp','model':2});
					generalSelect({'selector':'.js-bank','model':27});
					@ScriptSelect([ "selects" =>
						[
							[
								"identificator"          => ".currency", 
								"placeholder"            => "Seleccione el tipo de moneda", 
								"language"               => "es",
								"maximumSelectionLength" => "1"
							]
						]
					])
					@endScriptSelect
				}
				else if ($('input[name="prov"]:checked').val() == "buscar") 
				{
					$("#buscar").slideDown("slow");
					$("#form-prov").slideUp('fast');
				}
			})
			.on('change','input[name="fiscal"]',function()
			{
				if ($('input[name="fiscal"]:checked').val() == "1") 
				{
					$('.iva_kind').prop('disabled',false);
					$('#iva_no').prop('checked',true);
					$('.iva_kind').parents('.content-iva').stop(true,true).fadeIn();
					$('input[name=rfc]').attr('data-validation','rfc required server');
				}
				else if ($('input[name="fiscal"]:checked').val() == "0") 
				{
					$('.iva_kind').prop('disabled',true);
					$('#iva_no').prop('checked',true);
					$('.iva_kind').parents('.content-iva').stop(true,true).fadeOut();
					$('input[name=rfc]').removeAttr('data-validation','required');
				}
			})
			.on('change','.quanty,.price,.iva_kind,.addiotional,.additional_existAmount,.retention,.retention_newAmount',function()
			{
				taxesRetention();
			})
			.on('change','input[name="edit"]',function()
			{
				if($(this).is(':checked'))
				{
					swal({
						title     : "Habilitar edición de proveedor",
						text      : "¿Desea habilitar la edición del proveedor?",
						icon      : "warning",
						buttons   : ["Cancelar","OK"],
						dangerMode: true,
					})
					.then((enable) =>
					{
						if(enable)
						{
							$('input[name="reason"]').prop('disabled',false);
							$('input[name="address"]').prop('disabled',false);
							$('input[name="number"]').prop('disabled',false);
							$('input[name="colony"]').prop('disabled',false);
							$('#cp').prop('disabled',false);
							$('input[name="city"]').prop('disabled',false);
							$('.js-state').prop('disabled',false);
							$('input[name="rfc"]').prop('disabled',false);
							$('input[name="phone"]').prop('disabled',false);
							$('input[name="contact"]').prop('disabled',false);
							$('input[name="beneficiary"]').prop('disabled',false);
							$('input[name="other"]').prop('disabled',false);
							$('#banks').show();
							$('#banks-body .delete-item').show();
							generalSelect({'selector':'.js-bank','model':27});
							@ScriptSelect([ "selects" =>
								[
									[
										"identificator"          => ".currency", 
										"placeholder"            => "Seleccione el tipo de moneda", 
										"language"               => "es",
										"maximumSelectionLength" => "1"
									]
								]
							])
							@endScriptSelect
						}
						else
						{
							$('input[name="reason"]').prop('disabled',true);
							$('input[name="address"]').prop('disabled',true);
							$('input[name="number"]').prop('disabled',true);
							$('input[name="colony"]').prop('disabled',true);
							$('#cp').prop('disabled',true);
							$('input[name="city"]').prop('disabled',true);
							$('.js-state').prop('disabled',true);
							$('input[name="rfc"]').prop('disabled',true);
							$('input[name="phone"]').prop('disabled',true);
							$('input[name="contact"]').prop('disabled',true);
							$('input[name="beneficiary"]').prop('disabled',true);
							$('input[name="other"]').prop('disabled',true);
							$('#banks').hide();
							$('#banks-body .delete-item').hide();
							$(this).prop('checked',false);
							generalSelect({'selector':'.js-bank','model':27});
						}
					});
				}
				else
				{
					swal({
						title     : "Deshabilitar edición de proveedor",
						text      : "Si deshabilita la edición las modificaciones realizadas al proveedor no serán guardadas",
						icon      : "warning",
						buttons   : ["Cancelar","OK"],
						dangerMode: true,
					})
					.then((disabled) =>
					{
						if(disabled)
						{
							$('input[name="reason"]').prop('disabled',true);
							$('input[name="address"]').prop('disabled',true);
							$('input[name="number"]').prop('disabled',true);
							$('input[name="colony"]').prop('disabled',true);
							$('#cp').prop('disabled',true);
							$('input[name="city"]').prop('disabled',true);
							$('.js-state').prop('disabled',true);
							$('input[name="rfc"]').prop('disabled',true);
							$('input[name="phone"]').prop('disabled',true);
							$('input[name="contact"]').prop('disabled',true);
							$('input[name="beneficiary"]').prop('disabled',true);
							$('input[name="other"]').prop('disabled',true);
							$('#banks').hide();
							$('#banks-body .delete-item').hide();
							generalSelect({'selector':'.js-bank','model':27});
						}
						else
						{
							$('input[name="reason"]').prop('disabled',false);
							$('input[name="address"]').prop('disabled',false);
							$('input[name="number"]').prop('disabled',false);
							$('input[name="colony"]').prop('disabled',false);
							$('#cp').prop('disabled',false);
							$('input[name="city"]').prop('disabled',false);
							$('.js-state').prop('disabled',false);
							$('input[name="rfc"]').prop('disabled',false);
							$('input[name="phone"]').prop('disabled',false);
							$('input[name="contact"]').prop('disabled',false);
							$('input[name="beneficiary"]').prop('disabled',false);
							$('input[name="other"]').prop('disabled',false);
							$('#banks').show();
							$('#banks-body .delete-item').show();
							$(this).prop('checked',true);
							generalSelect({'selector':'.js-bank','model':27});
						}
					});
				}
			})
			.on('click','#addConceptPurchase',function()
			{
				purchase_id      = $('.purchase_id').val();
				countConcept     = $('.countConcept').length;
				countB           = $('.countConcept').length;
				cant             = $('input[name="quantity"]').removeClass('error').val();
				unit             = $('[name="unit"] option:selected').removeClass('error').val();
				descr            = $('input[name="description"]').removeClass('error').val();
				precio           = $('input[name="price"]').removeClass('error').val();
				iva              = ({{ App\Parameter::where('parameter_name','IVA')->first()->parameter_value }})/100;
				iva2             = ({{ App\Parameter::where('parameter_name','IVA2')->first()->parameter_value }})/100;
				ivakind          = $('input[name="iva_kind"]:checked').val();
				ivaCalc          = 0;
				taxesConcept     = 0;
				retentionConcept = 0;
				if (cant == "" || descr == "" || precio == "" || unit == null)
				{
					if(cant=="")
					{
						$('input[name="quantity"]').addClass('error');
						swal('', 'Por favor llene todos los campos.', 'error');
					}
					if(unit== null)
					{
						swal('', 'Por favor ingrese la unidad.', 'error');
					}
					if(descr=="")
					{
						$('input[name="description"]').addClass('error');
						swal('', 'Por favor llene todos los campos.', 'error');
					}
					if(precio=="")
					{
						$('input[name="price"]').addClass('error');
						swal('', 'Por favor llene todos los campos.', 'error');
					}
				}
				else
				{
					switch($('input[name="iva_kind"]:checked').val())
					{
						case 'no':
							ivaCalc = 0;
							break;
						case 'a':
							ivaCalc = cant*precio*iva;
							break;
						case 'b':
							ivaCalc = cant*precio*iva2;
							break;
					}
					nameAmounts = $('<div hidden></div>');
					$('.additional_existName').each(function(i,v)
					{
						nameAmount = $(this).val();
						nameAmounts.append($('<input type="hidden" class="num_nameAmount" name="tnameamount'+countB+'[]">').val(nameAmount));
					});
					amountsAA = $('<div hidden></div>');
					if($('input[name="additional_exist"]:checked').val() == 'si')
					{
						$('.additional_existAmount').each(function(i,v)
						{
							amountAA = $(this).val();
							amountsAA.append($('<input type="hidden" class="num_amountAdditional" name="tamountadditional'+countB+'[]">').val(amountAA));
							taxesConcept = Number(taxesConcept) + Number(amountAA);
						});
					}
					nameRetentions = $('<div hidden></div>');
					$('.retention_newName').each(function(i,v)
					{
						name = $(this).val();
						nameRetentions.append($('<input type="hidden" class="num_nameRetention" name="tnameretention'+countB+'[]">').val(name));
					});
					amountsRetentions = $('<div hidden></div>');
					if($('input[name="retention_new"]:checked').val() == 'si')
					{
						$('.retention_newAmount').each(function(i,v)
						{
							amountR = $(this).val();
							amountsRetentions.append($('<input type="hidden" class="num_amountRetention" name="tamountretention'+countB+'[]">').val(amountR));
							retentionConcept = Number(retentionConcept)+Number(amountR);
						});
					}
					if( ((cant*precio)+ivaCalc+taxesConcept-retentionConcept) <= 0)
					{
						swal('', 'El importe no puede ser menor o igual a cero.', 'error');
					}
					else if(Number(((cant*precio)+ivaCalc+taxesConcept-retentionConcept)).toFixed(2) == "NaN")
					{
						swal('', 'El importe debe ser numérico.', 'error');
					}
					else 
					{
						countConcept = countConcept+1;
						@php
							$body		= [];
							$modelBody	= [];
							$modelHead	= [
								["value" => "#", "show" => "true"],
								["value" => "Cantidad", "show" => "true"],
								["value" => "Unidad"],
								["value" => "Descripci&oacute;n"],
								["value" => "Precio Unitario"],
								["value" => "IVA"],
								["value" => "Impuesto adicional"],
								["value" => "Retenciones"],
								["value" => "Importe"],
								["value" => "Acciones"]
							];
							$body = [ "classEx"	=> "tr-purchase",
								[
									"classEx"	=> "countConcept",
									"show"		=> "true",
									"content"	=>
									[
										"label"	=> ""
									]
								],
								[
									"show"		=> "true",
									"content"	=>
									[
										[
											"kind"			=> "components.inputs.input-text",
											"attributeEx"	=> "readonly=\"true\" type=\"hidden\" name=\"idDetail[]\""
										],
										[
											"kind"			=> "components.inputs.input-text",
											"attributeEx"	=> "readonly=\"true\" type=\"hidden\" name=\"tquanty[]\"",
											"classEx"		=> "tquanty"
										],
									]
								],
								[
									"content"	=>
									[
										[
											"kind"			=> "components.inputs.input-text",
											"attributeEx"	=> "readonly=\"true\" type=\"hidden\" name=\"tunit[]\"",
											"classEx"		=> "tunit"
										]
									]
								],
								[
									"content"	=>
									[
										[
											"kind"			=> "components.inputs.input-text",
											"attributeEx"	=> "readonly=\"true\" type=\"hidden\" name=\"tdescr[]\"",
											"classEx"		=> "tdescr"
										],
										[
											"kind"			=> "components.inputs.input-text",
											"attributeEx"	=> "readonly=\"true\" type=\"hidden\" name=\"tivakind[]\"",
											"classEx"		=> "tivakind"
										]
									]
								],
								[
									"content"	=>
									[
										[
											"kind"			=> "components.inputs.input-text",
											"attributeEx"	=> "readonly=\"true\" type=\"hidden\" name=\"tprice[]\"",
											"classEx"		=> "tprice"
										]
									]
								],
								[
									"content"	=>
									[
										[
											"kind"			=> "components.inputs.input-text",
											"attributeEx"	=> "readonly=\"true\" type=\"hidden\" name=\"tiva[]\"",
											"classEx"		=> "tiva"
										]
									]
								],
								[
									"classEx"	=> "containerTaxes",
									"content"	=>
									[
										"label"		=> ""
									]
								],
								[
									"classEx"	=> "containerRetention",
									"content"	=>
									[
										"label"		=> ""
									]
								],
								[
									"content"	=>
									[
										[
											"kind"			=> "components.inputs.input-text",
											"attributeEx"	=> "readonly=\"true\" type=\"hidden\"",
											"classEx"		=> "ttotal"
										],
										[
											"kind"			=> "components.inputs.input-text",
											"attributeEx"	=> "readonly=\"true\" type=\"hidden\" name=\"tamount[]\"",
											"classEx"		=> "tamount"
										]
									]
								],
								[
									"content" =>
									[
										[
											"kind"			=> "components.buttons.button",
											"variant"		=> "success",
											"attributeEx"	=> "id=\"edit\" type=\"button\"",
											"classEx"		=> "edit-item",
											"label"			=> "<span class=\"icon-pencil\"></span>"
										],
										[
											"kind"			=> "components.buttons.button",
											"variant"		=> "red",
											"attributeEx"	=> "type=\"button\"",
											"classEx"		=> "delete-item-purchase",
											"label"			=> "<span class=\"icon-x\"></span>"
										]
									]
								]
							];
							$modelBody[]	= $body;
							$tableConcept	= view('components.tables.table', [
								"modelBody" => $modelBody,
								"modelHead"	=> $modelHead, 
								"noHead"	=> "true"
							])->render();
						@endphp
						row			= '{!!preg_replace("/(\r)*(\n)*/", "", $tableConcept)!!}';
						tr_table	= $(row);
						tr_table.find('.countConcept').text(countConcept);
						tr_table.find('.tquanty').parent().prepend(cant);
						tr_table.find('[name="idDetail[]"]').val(purchase_id);
						tr_table.find('.tquanty').val(cant);
						tr_table.find('.tunit').parent().prepend(unit);
						tr_table.find('.tunit').val(unit);
						descr = String(descr).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
						tr_table.find('.tdescr').parent().prepend(descr);
						tr_table.find('.tdescr').val(descr);
						tr_table.find('.tivakind').val(ivakind);
						tr_table.find('.tprice').parent().prepend('$ '+Number(precio).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));
						tr_table.find('.tprice').val(precio);
						tr_table.find('.tiva').parent().prepend('$ '+Number(ivaCalc).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));
						tr_table.find('.tiva').val(ivaCalc);
						tr_table.find('.containerTaxes').prepend('$ '+Number(taxesConcept).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));
						tr_table.find('.containerTaxes').append(nameAmounts).append(amountsAA);
						tr_table.find('.containerRetention').prepend('$ '+Number(retentionConcept).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));
						tr_table.find('.containerRetention').append(nameRetentions).append(amountsRetentions);
						tr_table.find('.ttotal').parent().prepend('$ '+Number(((cant*precio)+ivaCalc+taxesConcept)-retentionConcept).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));
						tr_table.find('.ttotal').val(((cant*precio)+ivaCalc));
						tr_table.find('.tamount').val(((cant*precio)+ivaCalc+taxesConcept)-retentionConcept);
						$('#body').append(tr_table);
						$('input[name="quantity"]').removeClass('error').val("");
						$('input[name="description"]').removeClass('error').val("");
						$('input[name="price"]').removeClass('error').val("");
						$('input[name="iva_kind"]').prop('checked',false);
						$('#iva_no').prop('checked',true);
						$('input[name="amount"]').val("");
						$('[name="unit"]').val('').trigger('change');
						$('#no_additional_exist').prop('checked',true);
						$('#no_retention_new').prop('checked',true);
						$('.additional_existName').val('');
						$('.additional_existAmount').val('');
						$('.retention_newName').val('');
						$('.retention_newAmount').val('');
						$('.purchase_id').val('x');
						$('#hidde-additional_exist-component').stop(true,true).slideUp().hide();
						$('#hidde-retention_new-component').stop(true,true).slideUp().hide();
						total_cal_purchase();
						countB++;
					}
				}
			})
			.on('click','.delete-item-purchase',function()
			{
				id = $(this).parents('.tr-purchase').find('[name="idDetail[]"]').val();
				if (id != "x") 
				{
					$('#delete').append($('<input type="hidden" name="delete[]" value="'+id+'">'));
				}
				$(this).parents('.tr-purchase').remove();
				total_cal_purchase();
				if($('.countConcept').length>0)
				{
					$('#body .countConcept').each(function(i,v)
					{
						$(this).html(i+1);
					});
				}
			})
			.on('click','.edit-item',function()
			{
				cant				= $('input[name="quantity"]').removeClass('error').val();
				unit				= $('[name="unit"] option:selected').removeClass('error').val();
				descr				= $('input[name="description"]').removeClass('error').val();
				precio				= $('input[name="price"]').removeClass('error').val();
				if (cant == "" || descr == "" || precio == "" || unit == "") 
				{
					tpurchaseid	= $(this).parents('.tr-purchase').find('[name="idDetail[]"]').val();
					tquanty		= $(this).parents('.tr-purchase').find('.tquanty').val();
					tunit		= $(this).parents('.tr-purchase').find('.tunit').val();
					tdescr		= $(this).parents('.tr-purchase').find('.tdescr').val();
					tivakind	= $(this).parents('.tr-purchase').find('.tivakind').val();
					tiva		= $(this).parents('.tr-purchase').find('.tiva').val();	
					tprice		= $(this).parents('.tr-purchase').find('.tprice').val();
					totalAmount	= $(this).parents('.tr-purchase').find('.tamount').val();
					swal({
						title		: "Editar concepto",
						text		: "Al editar, se eliminarán los impuestos adicionales y retenciones agregados ¿Desea continuar?",
						icon		: "warning",
						buttons		: ["Cancelar","OK"],
						dangerMode	: true,
					})
					.then((continuar) =>
					{
						if(continuar)
						{
							if(tivakind == 'a')
							{
								$('#iva_a').prop("checked",true);
							}
							else if(tivakind == 'b')
							{
								$('#iva_b').prop("checked",true);
							}
							else
							{
								$('#iva_no').prop("checked",true);
							}
							$('input[name="quantity"]').val(tquanty);
							$('[name="unit"]').val(tunit).trigger('change');
							$('input[name="description"]').val(tdescr);
							$('input[name="price"]').val(tprice);
							$('.purchase_id').val(tpurchaseid);
							$('.amount').val(Number(totalAmount).toFixed(2));
							$(this).parents('.tr-purchase').remove();
							total_cal_purchase();
							$('#body .tr-purchase').each(function(i,v)
							{
								$(this).find('.num_nameAmount').attr('name','tnameamount'+i+'[]');
								$(this).find('.num_amountAdditional').attr('name','tamountadditional'+i+'[]');
								$(this).find('.num_nameRetention').attr('name','tnameretention'+i+'[]');
								$(this).find('.num_amountRetention').attr('name','tamountretention'+i+'[]');
							});
							if($('.countConcept').length>0)
							{
								$('#body .countConcept').each(function(i,v)
								{
									$(this).html(i+1);
								});
							}
						}
						else
						{
							swal.close();
						}
					});
				}
				else
				{
					swal('', 'Tiene un concepto sin agregar a la lista', 'error');
				}
			})
			.on('click','#addAccountPurchase',function()
			{
				bank			= $(this).parents('#contentBank').find('.js-bank').val();
				bankName		= $(this).parents('#contentBank').find('.js-bank :selected').text();
				account			= $(this).parents('#contentBank').find('.account').val();
				branch_office	= $(this).parents('#contentBank').find('.branch_office').val();
				reference		= $(this).parents('#contentBank').find('.reference').val();
				clabe			= $(this).parents('#contentBank').find('.clabe').val();
				currency		= $(this).parents('#contentBank').find('.currency').val();
				agreement		= $(this).parents('#contentBank').find('.agreement').val();
				alias 			= $(this).parents('#contentBank').find('.alias').val();
				if(bank.length>0)
				{
					$('.account,.reference,.clabe,.currency').removeClass('error');
					if (account == "" && reference=="" && clabe == "" || currency == "")
					{
						if(account == "" && reference=="" && clabe == "")
						{
							if(account == "")
							{
								$('.account').addClass('error');
							}
							if(reference=="")
							{
								$('.reference').addClass('error');
							}
							if(clabe == "")
							{
								$('.clabe').addClass('error');
							}
						}
						if(currency == "")
						{
							$('.currency').addClass('error');
						}
						swal('', 'Debe ingresar todos los campos requeridos', 'error');
					}
					else if($(this).parents('#contentBank').find('.clabe').parent('div').hasClass('has-error') || $(this).parents('#contentBank').find('.account').parent('div').hasClass('has-error'))
					{
						swal('', 'Por favor ingrese datos correctos', 'error');
					}
					else
					{
						flag = false;
						$('#banks-body .tr-banks').each(function()
						{
							tr_account	= $(this).find('[name="account[]"]').val();
							tr_clabe	= $(this).find('[name="clabe[]"]').val();
							tr_bank		= $(this).find('[name="bank[]"]').val();
							if(clabe != "" && tr_clabe != "" && clabe == tr_clabe && bank == tr_bank)
							{
								swal('','La CLABE ya se encuentra registrada, por favor verifique sus datos.','error');
								$('.clabe').removeClass('valid').addClass('error');
								flag = true;
							}
							if(account != "" && tr_account != "" && account == tr_account && bank == tr_bank)
							{
								swal('','La cuenta ya se encuentra registrada, por favor verifique sus datos.','error');
								$('.account').removeClass('valid').addClass('error');
								flag = true;
							}
						});
						if(!flag)
						{
							@php
								$body		= [];
								$modelBody	= [];
								$modelHead	= [
									["value" => "Acción", "show" => "true"],
									["value" => "Banco", "show" => "true"],
									["value" => "Alias"],
									["value" => "Cuenta"],
									["value" => "Sucursal"],
									["value" => "Referencia"],
									["value" => "CLABE"],
									["value" => "Moneda"],
									["value" => "Convenio"]
								];
								$body = [ "classEx" => "tr-banks",
									[
										"show"		=> "true",
										"content"	=>
										[
											[
												"kind"				=> "components.inputs.checkbox",
												"attributeEx"		=> "type=\"radio\" name=\"provider_has_banks_id\"",
												"classEx"			=> "checkbox class-check",
												"classExLabel"		=> "request-validate",
												"label"				=> "<span class=\"icon-check\"></span>",
												"classExContainer"	=> "my-2",
												"radio"				=> true
											],
											[
												"kind"			=> "components.buttons.button",
												"variant"		=> "red",
												"classEx"		=> "delete-item",
												"attributeEx"	=> "type=\"button\"",
												"label"			=> "<span class=\"icon-x delete-span\"></span>"
											]
										]
									],
									[
										"show" 		=> "true",
										"content"	=>
										[
											[
												"kind"			=> "components.inputs.input-text",
												"attributeEx"	=> "type=\"hidden\" name=\"providerBank[]\" value=\"x\"",
												"classEx"		=> "providerBank"
											],
											[
												"kind"			=> "components.inputs.input-text",
												"attributeEx"	=> "type=\"hidden\" name=\"bank[]\""
											]
										]
									],
									[
										"content" =>
										[
											[
												"kind"			=> "components.inputs.input-text",
												"attributeEx"	=> "type=\"hidden\" name=\"alias[]\""
											]
										]
									],
									[
										"content" =>
										[
											[
												"kind"        => "components.inputs.input-text",
												"attributeEx" => "type=\"hidden\" name=\"account[]\""
											]
										]
									],
									[
										"content" =>
										[
											[
												"kind"        => "components.inputs.input-text",
												"attributeEx" => "type=\"hidden\" name=\"branch_office[]\""
											]
										]
									],
									[
										"content" =>
										[
											[
												"kind"        => "components.inputs.input-text",
												"attributeEx" => "type=\"hidden\" name=\"reference[]\""
											]
										]
									],
									[
										"content" =>
										[
											[
												"kind"			=> "components.inputs.input-text",
												"attributeEx"	=> "type=\"hidden\" name=\"clabe[]\""
											]
										]
									],
									[
										"content" =>
										[
											[
												"kind"			=> "components.inputs.input-text",
												"attributeEx"	=> "type=\"hidden\" name=\"currency[]\""
											]
										]
									],
									[
										"content" =>
										[
											[
												"kind"			=> "components.inputs.input-text",
												"attributeEx"	=> "type=\"hidden\" name=\"agreement[]\""
											],
											[
												"kind"			=> "components.inputs.input-text",
												"attributeEx"	=> "type=\"hidden\" name=\"checked[]\" value=\"0\"",
												"classEx"		=> "idchecked"
											]
										]
									]
								];
								$modelBody[]	= $body;
								$tableBank		= view('components.tables.table', [
									"modelBody" => $modelBody,
									"modelHead"	=> $modelHead, 
									"noHead"	=> "true"
								])->render();
							@endphp
							row       = '{!!preg_replace("/(\r)*(\n)*/", "", $tableBank)!!}';
							tableBank = $(row);
							tableBank.find('.class-check').attr('id',"idNew"+count);
							tableBank.find('.class-check').val(count);
							tableBank.find('.providerBank').parent().prepend(bankName);
							tableBank.find('[name="bank[]"]').val(bank);
							tableBank.find('[name="alias[]"]').parent().prepend(alias != "" ? alias : '---');
							tableBank.find('[name="alias[]"]').val(alias);
							tableBank.find('[name="account[]"]').parent().prepend(account != "" ? account : '---');
							tableBank.find('[name="account[]"]').val(account);
							tableBank.find('[name="branch_office[]"]').parent().prepend(branch_office != "" ? branch_office : '---');
							tableBank.find('[name="branch_office[]"]').val(branch_office);
							tableBank.find('[name="reference[]"]').parent().prepend(reference != "" ? reference : '---');
							tableBank.find('[name="reference[]"]').val(reference);
							tableBank.find('[name="clabe[]"]').parent().prepend(clabe != "" ? clabe : '---');
							tableBank.find('[name="clabe[]"]').val(clabe);
							tableBank.find('[name="currency[]"]').parent().prepend(currency != "" ? currency : '---');
							tableBank.find('[name="currency[]"]').val(currency);
							tableBank.find('[name="agreement[]"]').parent().prepend(agreement != "" ? agreement : '---');
							tableBank.find('[name="agreement[]"]').val(agreement);
							$('#banks-body').append(tableBank);
							$("#idNew"+count).next().attr('for',"idNew"+count);
							$('.clabe, .account').removeClass('valid').val('');
							$('.branch_office,.reference,.agreement,.alias').val('');
							$(this).parents('#contentBank').find('.error').removeClass('error');
							$('.js-bank').empty().trigger("change");
							$('.currency').val(null).trigger("change");
							count++;
						}
					}
				}
				else
				{
					swal('', 'Seleccione un banco, por favor', 'error');
					$('.js-bank').addClass('error');
				}
			})
			.on('click', '.edit', function()
			{
				$('#input-search').val('');
				$('#cp').empty().trigger('change');
				encodedString = $('#provider_'+$(this).val()).val();
				decodedString = Base64.decode(encodedString);
				json          = JSON.parse(decodedString);
				reasonTemp    = {'oldReason':json.provider.businessName};
				rfcTemp       = {'oldRfc':json.provider.idProvider};
				$('input[name="idProvider"]').val(json.provider.idProvider);
				$('input[name="provider_data_id"]').val(json.provider.provider_data_id);
				$('input[name="reason"]').val(json.provider.businessName).prop('disabled',true).attr('data-validation-req-params',JSON.stringify(reasonTemp));
				$('input[name="address"]').val(json.provider.address).prop('disabled',true);
				$('input[name="number"]').val(json.provider.number).prop('disabled',true);
				$('input[name="colony"]').val(json.provider.colony).prop('disabled',true);
				$('#cp').append(new Option(json.provider.postalCode, json.provider.postalCode, true, true)).trigger('change').prop('disabled',true);
				$('input[name="city"]').val(json.provider.city).prop('disabled',true);
				$('.js-state').val(json.provider.state_idstate).trigger('change').prop('disabled',true);
				$('input[name="rfc"]').val(json.provider.rfc).prop('disabled',true).attr('data-validation-req-params',JSON.stringify(rfcTemp));
				$('input[name="phone"]').val(json.provider.phone).prop('disabled',true);
				$('input[name="contact"]').val(json.provider.contact).prop('disabled',true);
				$('input[name="beneficiary"]').val(json.provider.beneficiary).prop('disabled',true);
				$('input[name="other"]').val(json.provider.commentaries).prop('disabled',true);
				$('#banks-body').html('');
				$.each(json.banks,function(i,v)
				{
					bankName = $('.js-bank option[value='+v.banks_idBanks+']').text();
					@php
						$body      = [];
						$modelBody = [];
						$modelHead = [
							["value" => "Acción", "show" => "true"],
							["value" => "Banco", "show" => "true"],
							["value" => "Alias"],
							["value" => "Cuenta"],
							["value" => "Sucursal"],
							["value" => "Referencia"],
							["value" => "CLABE"],
							["value" => "Moneda"],
							["value" => "Convenio"]
						];
						$body = [ "classEx" => "tr-banks",
							[
								"show" => "true",
								"content"	=>
								[
									[
										"kind"             => "components.inputs.checkbox",
										"attributeEx"      => "type=\"radio\" name=\"provider_has_banks_id\"",
										"classEx"          => "checkbox id_provider_banks",
										"classExLabel"     => "request-validate",
										"label"            => "<span class=\"icon-check\"></span>",
										"classExContainer" => "my-2",
										"radio"            => true
									],
									[
										"kind"        => "components.buttons.button",
										"variant"     => "red",
										"classEx"     => "delete-item",
										"attributeEx" => "type=\"button\"",
										"label"       => "<span class=\"icon-x delete-span\"></span>"
									]
								]
							],
							[
								"show"    => "true",
								"content" => 
								[
									[
										"kind"        => "components.inputs.input-text",
										"attributeEx" => "type=\"hidden\" name=\"providerBank[]\" value=\"x\"",
										"classEx"     => "providerBank"
									],
									[
										"kind"        => "components.inputs.input-text",
										"attributeEx" => "type=\"hidden\" name=\"bank[]\""
									]
								]
							],
							[
								"content" =>
								[
									[
										"kind"        => "components.inputs.input-text",
										"attributeEx" => "type=\"hidden\" name=\"alias[]\""
									]
								]
							],
							[
								"content" =>
								[
									[
										"kind"			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"hidden\" name=\"account[]\""
									]
								]
							],
							[
								"content" =>
								[
									[
										"kind"			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"hidden\" name=\"branch_office[]\""
									]
								]
							],
							[
								"content" =>
								[
									[
										"kind"			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"hidden\" name=\"reference[]\""
									]
								]
							],
							[
								"content" =>
								[
									[
										"kind"			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"hidden\" name=\"clabe[]\""
									]
								]
							],
							[
								"content" =>
								[
									[
										"kind"			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"hidden\" name=\"currency[]\""
									]
								]
							],
							[
								"content" =>
								[
									[
										"kind"			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"hidden\" name=\"agreement[]\""
									],
									[
										"kind"			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"hidden\" name=\"checked[]\" value=\"0\"",
										"classEx"		=> "idchecked"
									]
								]
							]
						];
						$modelBody[] = $body;
						$tableBanks  = view('components.tables.table', [
							"modelBody" => $modelBody,
							"modelHead"	=> $modelHead, 
							"noHead"	=> "true"
						])->render();
					@endphp
					row		= '{!!preg_replace("/(\r)*(\n)*/", "", $tableBanks)!!}';
					bank	= $(row);
					bank.find('.id_provider_banks').attr('id',"id"+v.id);
					bank.find('.id_provider_banks').val(v.id);
					bank.find('[name="bank[]"]').parent().prepend(bankName);
					bank.find('[name="bank[]"]').val(v.banks_idBanks);
					bank.find('[name="alias[]"]').parent().prepend(v.alias != null ? v.alias : '---');
					bank.find('[name="alias[]"]').val(v.alias);
					bank.find('[name="account[]"]').parent().prepend(v.account != null ? v.account : '---');
					bank.find('[name="account[]"]').val(v.account);
					bank.find('[name="branch_office[]"]').parent().prepend(v.branch != null ? v.branch : '---');
					bank.find('[name="branch_office[]"]').val(v.branch);
					bank.find('[name="reference[]"]').parent().prepend(v.reference != null ? v.reference : '---');
					bank.find('[name="reference[]"]').val(v.reference);
					bank.find('[name="clabe[]"]').parent().prepend(v.clabe != null ? v.clabe : '---');
					bank.find('[name="clabe[]"]').val(v.clabe);
					bank.find('[name="currency[]"]').parent().prepend(v.currency != null ? v.currency : '---');
					bank.find('[name="currency[]"]').val(v.currency);
					bank.find('[name="agreement[]"]').parent().prepend(v.agreement != null ? v.agreement : '---');
					bank.find('[name="agreement[]"]').val(v.agreement);
					$('#banks-body').append(bank);
					$("#id"+v.id).next().attr('for',"id"+v.id);
				});
				$('input[name="edit"]').prop('checked',false);
				$('#form-prov').slideDown();
				$(".checks").show();
				$('#banks').hide();
				$('#banks-body .delete-item').hide();
				$('.provider').slideUp();
				generalSelect({'selector':'.js-state','model':31});
				generalSelect({'selector':'#cp','model':2});
				generalSelect({'selector':'.js-bank','model':27});
			})
			.on('click','.checkbox',function()
			{
				$('.idchecked').val('0');
				$('.marktr').removeClass('marktr');
				$(this).parents('.tr-banks').addClass('marktr');
				$(this).parents('.tr-banks').find('.idchecked').val('1');
			})
			.on('click','#addConceptResource',function()
			{
				countConcept = $('.countConcept').length;
				concept      = $('input[name="concept"]').val().trim();
				amount       = $('input[name="amount"]').val().trim();
				account      = $('.js-accounts :selected').text();
				id_account   = $('.js-accounts :selected').val();
				if (concept == "" || account == "" || amount == "")
				{
					swal('', 'Por favor llene los campos necesarios', 'error');
					if(concept == "")
					{
						$('input[name="concept"]').addClass('error');
					}
					if(account == "")
					{
						$('.js-accounts').addClass('error');
					}
					if(amount == "")
					{
						$('input[name="amount"]').addClass('error');
					}
				}
				else if(amount <= 0)
				{
					swal('', 'El importe no puede ser menor o igual cero ', 'error');
					$('input[name="amount"]').addClass('error');
				}
				else if(Number(amount).toFixed(2) == "NaN")
				{
					swal('', 'El importe debe ser numérico.', 'error');
				}
				else
				{
					countConcept = countConcept+1;
					@php
						$body      = [];
						$modelBody = [];
						$modelHead = [
							["value" => "#", "show" => "true"],
							["value" => "Concepto", "show" => "true"],
							["value" => "Clasificación de gasto"],
							["value" => "Importe"],
							["value" => "Acción"]
						];
						$body = [ "classEx" => "tr-concept",
							[
								"classEx"	=> "countConcept",
								"show"		=> "true",
								"content"	=>
								[
									"label"	=> ""
								]
							],
							[
								"show"		=> "true",
								"content"	=>
								[
									[
										"kind"        => "components.inputs.input-text",
										"attributeEx" => "readonly=\"true\" type=\"hidden\" name=\"idDetail[]\" value=\"x\""
									],
									[
										"kind"        => "components.inputs.input-text",
										"attributeEx" => "type=\"hidden\" name=\"t_concept[]\""
									],
									[
										"kind"        => "components.inputs.input-text",
										"attributeEx" => "type=\"hidden\" name=\"t_account[]\""
									]							
								]
							],
							[
								"content" =>
								[
									[
										"kind"			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"hidden\" name=\"accounts_t\""
									]
								]
							],
							[
								"content" =>
								[
									[
										"kind"			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"hidden\" name=\"t_amount[]\"",
										"classEx"		=> "t-amount"
									]
								]
							],
							[
								"content" =>
								[
									"kind"			=> "components.buttons.button",
									"variant"		=> "red",
									"attributeEx"	=> "type=\"button\"",
									"label"			=> "<span class=\"icon-x\"></span>",
									"classEx"		=> "delete-item-resource"
								]
							]
						];
						$modelBody[]	= $body;
						$table			= view('components.tables.table',[
							"modelBody" => $modelBody,
							"modelHead" => $modelHead, 
							"noHead"	=> "true"
						])->render();
					@endphp
					row      = '{!!preg_replace("/(\r)*(\n)*/", "", $table)!!}';
					tr_table = $(row);
					tr_table.find('.countConcept').prepend(countConcept);
					concept = String(concept).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
					tr_table.find('[name="t_concept[]"]').parent().prepend(concept);
					tr_table.find('[name="t_concept[]"]').val(concept);
					tr_table.find('[name="t_account[]"]').val(id_account);
					tr_table.find('[name="accounts_t"]').parent().prepend(account);
					tr_table.find('[name="accounts_t"]').val(account);
					tr_table.find('.t-amount').parent().prepend('$ '+Number(amount).toFixed(2));
					tr_table.find('.t-amount').val(amount);
					$('#body').append(tr_table);
					$('input[name="concept"]').val('');
					$('input[name="amount"]').val('');
					$('.js-accounts').val(0).trigger("change");
					$('.js-accounts').removeClass('error');
					$('input[name="concept"]').removeClass('error');
					$('input[name="amount"]').removeClass('error');
					total_cal_resource();
				}
			})
			.on('change', '[name="userid"]', function()
			{
				id = $(this).val();
				$.ajax(
				{
					type   : 'post',
					url    : '{{ route("loan.search.bank") }}',
					data   : {'idUsers':id},
					success: function(data)
					{
						$('.resultbank').html(data);
					},
					error : function()
					{
						swal('','Sucedió un error, por favor intente de nuevo.','error');
						$('.resultbank').html('');
					}
				}); 
			})
			.on('click','input[name="method"]',function()
			{
				if($(this).val() == 1)
				{
					$('.resultbank').stop(true,true).slideDown().show();
				}
				else
				{
					$('.resultbank').stop(true,true).slideUp().hide();
				}
			})
			.on('click','.delete-item-resource',function()
			{
				id = $(this).parents('.tr-concept').find('[name="idDetail[]"]').val();
				if (id != "x") 
				{
					$('#delete').append($('<input type="hidden" name="delete[]" value="'+id+'">'));
				}
				$(this).parents('.tr-concept').remove();
				total_cal_resource();
				if($('.countConcept').length>0)
				{
					$('.countConcept').each(function(i,v)
					{
						$(this).html(i+1);
					});
				}
			})
			.on('keyup','#input-search', function()
			{
				provider_search(1);
			})
			.on('click','.paginate a', function(e)
			{
				e.preventDefault();
				href   = $(this).attr('href');
				url    = new URL(href);
				params = new URLSearchParams(url.search);
				page   = params.get('page');
				provider_search(page);
			})
			.on('click','.delete-item',function()
			{
				idProviderBanks = $(this).parents('.tr-banks').find('.providerBank').val();
				if(idProviderBanks != "x")
				{
					$('#delete-accounts').append($('<input type="hidden" name="deleteAccount[]" value="'+idProviderBanks+'">'));
				}
				$(this).parents('.tr-banks').remove();
			})
		});
		function total_cal_purchase()
		{
			subtotal = 0;
			iva      = 0;
			amountAA = 0;
			amountR  = 0;
			$("#body .tr-purchase").each(function(i, v)
			{
				tempQ  = $(this).find('.tquanty').val();
				tempP  = $(this).find('.tprice').val();
				tempAA = null;
				tempR  = null;
				$(".num_amountAdditional").each(function(i, v)
				{
					tempAA += Number($(this).val());
				});
				$(".num_amountRetention").each(function(i, v)
				{
					tempR += Number($(this).val());
				});
				subtotal += (Number(tempQ)*Number(tempP));
				iva      += Number($(this).find('.tiva').val());
				amountAA =  Number(tempAA);
				amountR  =  Number(tempR);
			});
			total = (subtotal+iva + amountAA)-amountR;
			$('input[name="subtotal"]').val(Number(subtotal).toFixed(2));
			$('input[name="totaliva"]').val(Number(iva).toFixed(2));
			$('input[name="total"]').val(Number(total).toFixed(2));
			$(".amount_total").val(Number(total).toFixed(2));
			$('input[name="amountAA"]').val(Number(amountAA).toFixed(2));
			$('input[name="amountR"]').val(Number(amountR).toFixed(2));
			$('.subtotalLabel').text('$ '+Number(subtotal).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));
			$('.ivaLabel').text('$ '+Number(iva).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));
			$('.totalLabel').text('$ '+Number(total).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));
			$('.taxesLabel').text('$ '+Number(amountAA).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));
			$('.retentionLabel').text('$ '+Number(amountR).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));
			$(".amountLabel").text('$ '+Number(total).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));
		}
		function validation()
		{
			$.validate(
			{
				form	: '#container-alta',
				modules	: 'security',
				onError	: function($form)
				{
					swal('', '{{ Lang::get("messages.form_error") }}', 'error');
				},
				onSuccess: function($form)
				{
					kind     = $('.js-kind option:selected').val();
					dayOne   = $('input[name="day_twiceMonthly_one"]').val();
					dayTwo   = $('input[name="day_twiceMonthly_two"]').val();
					dateOne  = Number(dayOne);
					dateTwo  = Number(dayTwo);
					dateDiff = dateTwo - dateOne;
					 
					if ($('[name="periodicity"] option:selected').val() == "twiceMonthly") 
					{	
						if(dateOne > dateTwo)
						{
							swal('','El segundo día tiene que ser posterior al primer día','error');
							$('input[name="day_twiceMonthly_two"]').addClass('error').val('');
							return false;
						}
						else if (dateDiff < 14 || dateDiff > 16)
						{
							swal('','El rango debe de ser quincenal o catorcenal','error');
							$('input[name="day_twiceMonthly_two"]').addClass('error').val('');
							return false;
						}
					}
					if (kind == 1) 
					{	 
						cant	= $('input[name="quantity"]').removeClass('error').val();
						unit	= $('[name="unit"] option:selected').removeClass('error').val();
						descr	= $('input[name="description"]').removeClass('error').val();
						precio	= $('input[name="price"]').removeClass('error').val();
						if (cant != "" || descr != "" || precio != "" || unit != undefined) 
						{
							swal('', 'Tiene un concepto sin agregar', 'error');
							return false;
						}
						conceptos = $('#body .tr-purchase').length;
						if($('#form-prov').is(':visible') && conceptos > 0)
						{
							if ($('[name="pay_mode"] option:selected').val() == "Transferencia") 
							{
								if($('#banks-body .tr-banks').length>0)
								{
									if ($('.checkbox').is(':checked')) 
									{
										return true;
									}
									else
									{
										swal('', 'Debe seleccionar una cuenta de proveedor', 'error');
										return false;
									}
								}
								else
								{
									swal('', 'Debe ingresar al menos una cuenta de proveedor', 'error');
									return false;
								}
							}
						}
						else
						{
							swal('', 'Debe ingresar al menos un concepto de pedido y todos los datos del proveedor', 'error');
							return false;
						}
					}
					if (kind == 8) 
					{
						concept		= $('[name="concept"]').removeClass('error').val();
						accountid	= $('[name="accountid"] option:selected').removeClass('error').val();
						amount		= $('[name="amount"]').removeClass('error').val();
						if (concept != "" || amount != "" || accountid != undefined) 
						{
							swal('', 'Tiene un concepto sin agregar', 'error');
							return false;
						}
						concepts	= $('#body .tr-concept').length;
						check 		= $('.checkbox:checked').length;
						method 		= $('input[name="method"]:checked').val();
						if(concepts > 0 && method != undefined)
						{
							if (method == 1) 
							{
								if (check > 0) 
								{
									return true;
								}
								else
								{
									swal('', 'Debe seleccionar una cuenta del solicitante.', 'error');
									return false;
								}
							}
							else
							{
								return true;
							}
						}
						else if (method == undefined) 
						{
							swal('', 'Debe seleccionar un método de pago.', 'error');
							return false;
						}
						else if (concepts == 0) 
						{
							swal('', 'Debe agregar al menos un concepto al pedido.', 'error');
							return false;
						}
					}	
				}
			});
		}
		function total_cal_resource()
		{
			subtotal	= 0;
			ivaTotal	= 0;
			$("#body .tr-concept").each(function(i, v)
			{
				subtotal	+= Number($(this).find('.t-amount').val());
			});
			total	= subtotal;
			$('[name="total_resource"]').val(total);
			$('.totalLabel').text('$ '+Number(total).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));
		}
		function provider_search(page)
		{
			text = $("#input-search").val().trim();
			if (text == "")
			{
				$('#not-found').stop().show();
				$('#not-found').html('RESULTADO NO ENCONTRADO');
				$('.provider').stop().hide();
			}
			else
			{
				$('#not-found').stop().hide();
				$.ajax(
				{
					type	: 'post',
					url		: '{{ route("purchase.create.provider") }}',
					data	: {'search':text, "page":page},
					success	: function(data)
					{
						$('.provider').html(data).slideDown('slow');
					},
					error : function()
					{
						swal('','Sucedió un error, por favor intente de nuevo.','error');
						$('.provider').html('').slideUp('fast');
					}
				}); 
			}
		}
		function taxesRetention()
		{
			cant	= $('input[name="quantity"]').val();
			precio	= $('input[name="price"]').val();
			iva		= ({{ App\Parameter::where('parameter_name','IVA')->first()->parameter_value }})/100;
			iva2	= ({{ App\Parameter::where('parameter_name','IVA2')->first()->parameter_value }})/100;
			ivaCalc	= 0;
			switch($('input[name="iva_kind"]:checked').val())
			{
				case 'no':
					ivaCalc = 0;
					break;
				case 'a':
					ivaCalc = cant*precio*iva;
					break;
				case 'b':
					ivaCalc = cant*precio*iva2;
					break;
			}
			aditionalTaxes = 0;
			if($('#si_additional_exist').is(':checked'))
			{
				$('.additional_existAmount').each(function(i,v)
				{
					amountAA = $(this).val();
					aditionalTaxes = Number(aditionalTaxes) + Number(amountAA);
				});
			}
			retentions = 0;
			if($('#si_retention_new').is(':checked'))
			{
				$('.retention_newAmount').each(function(i,v)
				{
					amountR = $(this).val();
					retentions = Number(retentions)+Number(amountR);
				});
			}
			totalImporte    = (((cant * precio)+ivaCalc+aditionalTaxes)-retentions);
			$('input[name="amount"]').val(totalImporte.toFixed(2));
		}
	</script>
@endsection