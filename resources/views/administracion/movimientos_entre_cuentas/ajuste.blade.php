@extends('layouts.child_module')

@section('data')
 @php
	$taxesCount	=	$taxesCountBilling	=	0;
	$taxes		=	$retentions			=	$taxesBilling	=	$retentionsBilling	=	0;
 @endphp
	<div id="form-adjustment">
		@component('components.forms.form', ["attributeEx" => "action=\"".route('movements-accounts.adjustment.store')."\" id=\"container-alta\" method=\"POST\"", "files" => true])
			@component("components.labels.title-divisor") FORMULARIO AJUSTE DE MOVIMIENTOS @endcomponent
			@component("components.containers.container-form")
				<div class="col-span-2">
					@component("components.labels.label") Título: @endcomponent
					@component("components.inputs.input-text")
						@slot("classEx")
							removeselect
						@endslot
						@slot("attributeEx")
							type="text"
							name="title"
							placeholder="Ingrese el título"
							data-validation="required"
							@if(isset($requests)) value="{{ $requests->adjustment->first()->title }}" @endif
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component("components.labels.label") Fecha: @endcomponent
					@component("components.inputs.input-text")
						@slot("classEx")
							removeselect datepicker2
						@endslot
						@slot("attributeEx")
							type="text" 
							name="datetitle" 
							@if(isset($requests)) value="{{ $requests->adjustment->first()->datetitle!=null ? Carbon\Carbon::createFromFormat('Y-m-d',$requests->adjustment->first()->datetitle)->format('d-m-Y') : null}}" @endif 
							placeholder="Ingrese la fecha" 
							readonly="readonly"
							data-validation="required"
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component("components.labels.label") Nombre del solicitante: @endcomponent
					@php
						$options	=	collect();
						if (isset($requests) && isset($requests->idRequest) && $requests->idRequest != "")
						{
							$options	=	$options->concat([["value" => $requests->requestUser->id, "description" => $requests->requestUser->name." ".$requests->requestUser->last_name." ".$requests->requestUser->scnd_last_name, "selected" => "selected"]]);
						}
					@endphp
					@component("components.inputs.select",["options"=>	$options, "classEx"	=>	"removeselect js-users",	"attributeEx"	=>	"name=\"userid\" data-validation=\"required\""]) @endcomponent
				</div>
				<div class="col-span-2">
					@component("components.labels.label") Comentarios del ajuste: @endcomponent
					@component("components.inputs.text-area", ["classEx"	=>	"w-full",	"attributeEx"	=>	"name=\"commentaries\" placeholder=\"Ingrese un comentario\""]) @endcomponent
				</div>
			@endcomponent
			@component("components.labels.title-divisor")
				@slot('classEx')
					mt-12
				@endslot
				SELECCIÓN DE SOLICITUDES
			@endcomponent
			@component("components.containers.container-form")
				<div class="col-span-2">
					@component("components.labels.label") Empresa: @endcomponent
					@php
						$options = collect();
						foreach(App\Enterprise::orderName()->where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeEnt($option_id)->pluck('enterprise_id'))->get() as $enterprise)
						{
							if(isset($requests) && $requests->adjustment->first()->idEnterpriseOrigin == $enterprise->id)
							{
								$options	=	$options->concat([["value"	=>	$enterprise->id,	"selected"	=>	"selected",	"description"	=>	$enterprise->name]]);
							}
							else
							{
								$options	=	$options->concat([["value"	=>	$enterprise->id,	"description"	=>	$enterprise->name]]);
							}
						}
					@endphp
					@component("components.inputs.select", ["options" => $options, "classEx" => "js-enterprises removeselect", "attributeEx" => "name=\"enterpriseid\" data-validation=\"required\""]) @endcomponent
					@component("components.inputs.input-text",["attributeEx" => "type=\"hidden\" name=\"enterpriseid_origin\" value=\"".(isset($requests) ? $requests->adjustment->first()->idEnterpriseOrigin : '')."\""]) @endcomponent
				</div>
				@if(isset($requests))
					<div class="col-span-2">
						@component("components.labels.label") Folio: @endcomponent
						@component("components.inputs.select", ["classEx" => "js-folios removeselect", "attributeEx" => "name=\"folios\""]) @endcomponent
					</div>
				@else
					<div class="col-span-2">
						@component("components.labels.label") Folio: @endcomponent
						@component("components.inputs.select", ["attributeEx" => "name=\"folios\"", "classEx" => "js-folios removeselect"]) @endcomponent
					</div>
				@endif
			@endcomponent
			@component("components.labels.title-divisor", ["classEx" => "tm-12"]) DATOS DE ORIGEN @endcomponent
			@php
				if (isset($requests)) 
				{
					$classEx = "alert alert-danger hidden";
				}
				else
				{
					$classEx = "alert alert-danger";
				}
			@endphp
			@component("components.labels.not-found",["attributeEx" => "id=\"error_request\" role=\"alert\"", "classEx" => $classEx,'text'=>"Debe seleccionar una solicitud"]) @endcomponent
			@if(isset($requests))
				<div class="folios flex justify-between grid grid-cols-1 md:grid-cols-2">
					@foreach($requests->adjustment->first()->adjustmentFolios as $af)
						@switch($af->requestModel->kind)
							@case(1)
								@php
									$subtotal_request	=	$af->requestModel->purchases->first()->subtotales;
									$iva_request		=	$af->requestModel->purchases->first()->tax;
									$tax_request		=	0;
									$retention_request	=	0;
									$total_request		=	$af->requestModel->purchases->first()->amount;
								@endphp
								@foreach($af->requestModel->purchases->first()->detailPurchase as $detail)
									@foreach($detail->taxes as $tax)
										@php 
											$tax_request	+=	$tax->amount
										@endphp
									@endforeach
								@endforeach
								@foreach($af->requestModel->purchases->first()->detailPurchase as $detail)
									@foreach($detail->retentions as $ret)
										@php 
											$retention_request	+=	$ret->amount
										@endphp
									@endforeach
								@endforeach
							@break
							@case(3)
								@php
									$subtotal_request	=	0;
									$iva_request		=	0;
									$tax_request		=	0;
									$retention_request	=	0;
									$total_request		=	0;
								@endphp
								@foreach($af->requestModel->expenses->first()->expensesDetail as $detail)
									@php
										$subtotal_request	+=	$detail->amount;
										$iva_request		+=	$detail->tax;
										$total_request		+=	$detail->sAmount;
									@endphp
									@foreach($detail->taxes as $tax)
										@php 
											$tax_request	+=	$tax->amount
										@endphp
									@endforeach
								@endforeach
							@break
							@case(9)
								@php
									$subtotal_request	=	0;
									$iva_request		=	0;
									$tax_request		=	0;
									$retention_request	=	0;
									$total_request		=	0;
								@endphp
								@foreach($af->requestModel->refunds->first()->refundDetail as $detail)
									@php
										$subtotal_request	+=	$detail->amount;
										$iva_request		+=	$detail->tax;
										$total_request		+=	$detail->sAmount;
									@endphp
									@foreach($detail->taxes as $tax)
										@php 
											$tax_request	+=	$tax->amount
										@endphp
									@endforeach
								@endforeach
							@break
						@endswitch
						@component('components.inputs.input-text')
							@slot('attributeEx')
								type="hidden" name="folios_adjustment[]" value="{{ $af->idFolio }}"
							@endslot
							@slot('classEx')
								folios_adjustment
							@endslot
						@endcomponent
						
						<div class="content-center container-folio mx-6 col-span-1">
							@php
								$modelTable	=
								[
									["Empresa:",					$af->requestModel->reviewedEnterprise->name],
									["Dirección:",					$af->requestModel->reviewedDirection->name],
									["Departamento:",				$af->requestModel->reviewedDepartment->name],
									["Clasificación del gasto:",	$af->requestModel->accountsReview()->exists() ? $af->requestModel->accountsReview->account.' '. $af->requestModel->accountsReview->description : 'Varias'],
									["Proyecto:",					$af->requestModel->reviewedProject->proyectName],
								];
							@endphp
							@component('components.templates.outputs.table-detail', ["modelTable" => $modelTable, "variant" => true])
								@slot('classEx')
									mt-4
								@endslot
								@slot('title')
									@component('components.labels.label')
										@slot('classEx')
											w-11/12
											text-center
											text-white
											ml-14
										@endslot
										FOLIO  #{{ $af->idFolio }}
									@endcomponent
									@component('components.inputs.input-text')
										@slot('attributeEx')
											type="hidden" value="{{ $af->idFolio }}"
										@endslot
										@slot('classEx')
											del-folio
										@endslot
									@endcomponent
									@component('components.inputs.input-text')
										@slot('attributeEx')
											type="hidden" value="{{ $subtotal_request }}"
										@endslot
										@slot('classEx')
											subtotal_request
										@endslot
									@endcomponent
									@component('components.inputs.input-text')
										@slot('attributeEx')
											type="hidden" value="{{ $tax_request }}"
										@endslot
										@slot('classEx')
											tax_request
										@endslot
									@endcomponent
									@component('components.inputs.input-text')
										@slot('attributeEx')
											type="hidden" value="{{ $retention_request }}"
										@endslot
										@slot('classEx')
											retention_request
										@endslot
									@endcomponent
									@component('components.inputs.input-text')
										@slot('attributeEx')
											type="hidden" value="{{ $iva_request }}"
										@endslot
										@slot('classEx')
											iva_request
										@endslot
									@endcomponent
									@component('components.inputs.input-text')
										@slot('attributeEx')
											type="hidden" value="{{ $total_request }}"
										@endslot
										@slot('classEx')
											total_request
										@endslot
									@endcomponent
									@component('components.buttons.button', ['variant' => 'red'])
										@slot('classEx')
											mr-4
											h-8
											delete-folio
										@endslot
										@slot('attributeEx')
											type="button"
										@endslot
										@slot('label')
											<span class="icon-x"></span>
										@endslot
									@endcomponent
								@endslot
							@endcomponent
						</div>
					@endforeach
				</div>
			@else
				<div class="folios justify-between grid grid-cols-1 md:grid-cols-2">
				</div>
			@endif
			<div id="detail" class="hidden p-4"> </div>
			@component("components.labels.title-divisor")
				@slot('classEx')
					mt-12
				@endslot
				DATOS DE AJUSTE
			@endcomponent
			@component("components.containers.container-form")
				<div class="col-span-2">
					@component("components.labels.label") Empresa: @endcomponent
					@php
						$options	=	collect();
						foreach(App\Enterprise::orderName()->where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeEnt($option_id)->pluck('enterprise_id'))->get() as $enterprise)
						{
							if(isset($requests) && $requests->adjustment->first()->idEnterpriseDestiny == $enterprise->id)
							{
								$options	=	$options->concat([["value"	=>	$enterprise->id,	"description"	=>	strlen($enterprise->name) >= 35 ? substr(strip_tags($enterprise->name),0,35).'...' : $enterprise->name,	"selected"	=>	"selected"]]);
							}
							else
							{
								$options	=	$options->concat([["value"	=>	$enterprise->id,	"description"	=>	strlen($enterprise->name) >= 35 ? substr(strip_tags($enterprise->name),0,35).'...' : $enterprise->name]]);
							}
						}
					@endphp
					@component("components.inputs.select",["options" => $options, "attributeEx" => "name=\"enterpriseid_destination\" data-validation=\"required\"", "classEx" => "js-enterprises-destination removeselect"]) @endcomponent
				</div>
				<div class="col-span-2">
					@component("components.labels.label") Dirección: @endcomponent
					@php
						$options	=	collect();
						foreach(App\Area::orderName()->where('status','ACTIVE')->get() as $area)
						{
							if(isset($requests) && $requests->adjustment->first()->idAreaDestiny == $area->id)
							{
								$options	=	$options->concat([["value"	=>	$area->id,	"description"	=>	$area->name,	"selected"	=>	"selected"]]);
							}
							else
							{
								$options	=	$options->concat([["value"	=>	$area->id,	"description"	=>	$area->name]]);
							}
						}
					@endphp
					@component("components.inputs.select",["options" => $options, "attributeEx" => "name=\"areaid_destination\" data-validation=\"required\"", "classEx" => "js-areas-destination removeselect"]) @endcomponent
				</div>
				<div class="col-span-2">
					@component("components.labels.label") Departamento: @endcomponent
					@php
						$options	=	collect();
						foreach(App\Department::orderName()->where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeDep($option_id)->pluck('departament_id'))->get() as $department)
						{
							if(isset($requests) && $requests->adjustment->first()->idDepartamentDestiny == $department->id)
							{
								$options	=	$options->concat([["value"	=>	$department->id,	"description"	=>	$department->name,	"selected"	=>	"selected"]]);
							}
							else
							{
								$options	=	$options->concat([["value"	=>	$department->id,	"description"	=>	$department->name]]);
							}
						}
					@endphp
					@component("components.inputs.select",["options" => $options, "attributeEx" => "name=\"departmentid_destination\" id=\"multiple-departments\" data-validation=\"required\"", "classEx" => "js-departments-destination removeselect"]) @endcomponent
				</div>
				<div class="col-span-2">
					@component("components.labels.label") Clasificación de gasto: @endcomponent
					@php
						$options	=	collect();
						if (isset($requests) && isset($requests->adjustment->first()->idAccAccDestiny))
						{
							$options	=	$options->concat([["value"	=>	$requests->adjustment->first()->accountDestiny->idAccAcc,	"description"	=>	$requests->adjustment->first()->accountDestiny->account." - ".$requests->adjustment->first()->accountDestiny->description." (".$requests->adjustment->first()->accountDestiny->content.")",	"selected"	=>	"selected"]]);
						}
					@endphp
					@component("components.inputs.select",["options" => $options, "attributeEx" => "name=\"accountid_destination\" data-validation=\"required\"", "classEx" => "js-accounts-destination removeselect"]) @endcomponent
				</div>
				<div class="col-span-2">
					@component("components.labels.label") Proyecto: @endcomponent
					@php
						$options	=	collect();
						$project	=	App\Project::orderName()->whereIn('status',[1,2])->get();
						if (isset($requests) && $requests->adjustment->first()->idProjectDestiny != "") {
							$options	=	$options->concat([["value"	=>	$requests->adjustment->first()->projectDestiny->idproyect,	"description"	=>	$requests->adjustment->first()->projectDestiny->proyectName,	"selected"	=>	"selected"]]);
						}
					@endphp
					@component("components.inputs.select",["options" => $options, "attributeEx" => "name=\"projectid_destination\" data-validation=\"required\"", "classEx" => "js-projects-destination removeselect"]) @endcomponent
				</div>
			@endcomponent
			@component("components.labels.title-divisor")
				@slot('classEx')
					mt-12
				@endslot
				CONDICIONES DE PAGO
			@endcomponent
			@component("components.containers.container-form")
				<div class="col-span-2">
					@component("components.labels.label") Tipo de moneda: @endcomponent
					@php
						$option = collect();
						$options = ["MXN", "USD", "EUR", "Otro"];
						foreach($options as $mainOption)
						{
							if(isset($requests) && $requests->adjustment->first()->currency == $mainOption)
							{
								$option	=	$option->concat([["value"	=>	$mainOption,	"description"	=>	$mainOption,	"selected"	=>	"selected"]]);
							}
							else
							{
								$option	=	$option->concat([["value"	=>	$mainOption,	"description"	=>	$mainOption]]);
							}
						}
					@endphp
					@component("components.inputs.select",["options" => $option, "attributeEx" => "name=\"type_currency\" multiple=\"multiple\" data-validation=\"required\"", "classEx" => "removeselect"]) @endcomponent
				</div>
				<div class="col-span-2">
					@component("components.labels.label") Fecha de pago: @endcomponent
					@component("components.inputs.input-text",["attributeEx" => "type=\"text\" name=\"date\" step=\"1\" placeholder=\"Ingrese la fecha\" readonly=\"readonly\" data-validation=\"required\" id=\"datepicker\"", "classEx" => "generalInput remove"]) @endcomponent
				</div>
				<div class="col-span-2">
					@component("components.labels.label") Forma de pago: @endcomponent
					@php
						$option		=	collect();
						$options	=	[1 => "Cuenta Bancaria", 2 =>  "Efectivo", 3 => "Cheque"];
						
						foreach($options as $key => $mainOption)
						{
							if(isset($requests) && $requests->adjustment->first()->idpaymentMethod == $mainOption)
							{
								$option	=	$option->concat([["value"	=>	$key,	"description"	=>	$mainOption,	"selected"	=>	"selected"]]);
							}
							else
							{
								$option	=	$option->concat([["value"	=>	$key,	"description"	=>	$mainOption]]);
							}
						}
					@endphp
					@component("components.inputs.select",["options" => $option, "attributeEx" => "name=\"pay_mode\" multiple=\"multiple\" data-validation=\"required\"", "classEx" => "js-form-pay removeselect"]) @endcomponent
				</div>
				<div class="col-span-2">
					@component("components.labels.label") Subtotal: @endcomponent
					@php
						$value = isset($requests) && $requests->adjustment->first()->subtotales !="" ? $requests->adjustment->first()->subtotales : 0.00;
					@endphp
					@component("components.inputs.input-text",["attributeEx" => "type=\"text\" name=\"subtotal_adjustment\" readonly=\"readonly\" placeholder=\"Ingrese el subtotal\" value=\"$value\"", "classEx" => "generalInput"]) @endcomponent
				</div>
				<div class="col-span-2">
					@component("components.labels.label") IVA: @endcomponent
					@php
						isset($requests) ? $value = $requests->adjustment->first()->tax : $value = 0;
					@endphp
					@component("components.inputs.input-text",["attributeEx" => "type=\"text\" name=\"iva_adjustment\" readonly=\"readonly\" placeholder=\"Ingrese el iva\" value=\"$value\"", "classEx" => "generalInput"]) @endcomponent
				</div>
				<div class="col-span-2">
					@component("components.labels.label") Impuestos adicionales: @endcomponent
					@php
						isset($requests) ? $value = $requests->adjustment->first()->additionalTax : $value = 0;
					@endphp
						@component("components.inputs.input-text",["attributeEx" => "type=\"text\" name=\"tax_adjustment\" readonly=\"readonly\" placeholder=\"Ingrese el impuesto\" value=\"$value\"", "classEx" => "generalInput"]) @endcomponent
				</div>
				<div class="col-span-2">
					@component("components.labels.label") Retenciones: @endcomponent
					@php
						isset($requests) ? $value = $requests->adjustment->first()->retention : $value = 0;
					@endphp
					@component("components.inputs.input-text",["attributeEx" => "type=\"text\" name=\"retention_adjustment\" readonly=\"readonly\" placeholder=\"Ingrese una retención\" value=\"$value\"", "classEx" => "generalInput"]) @endcomponent
				</div>
				<div class="col-span-2">
					@component("components.labels.label") Total: @endcomponent
					@php
						isset($requests) ? $value = $requests->adjustment->first()->amount : $value = 0;
					@endphp
					@component("components.inputs.input-text",["attributeEx" => "type=\"text\" name=\"total_adjustment\" readonly=\"readonly\" placeholder=\"Ingrese el total\" value=\"$value\"", "classEx" => "generalInput total_adjustment"]) @endcomponent
				</div>
			@endcomponent
			@component("components.labels.title-divisor")
				CARGAR DOCUMENTOS
			@endcomponent
			@component('components.containers.container-form')
				<div class="col-span-2 md:col-span-4 grid grid-cols-1 md:grid-cols-2 gap-6 hidden" id="documents"></div>
				<div class="col-span-2 md:col-span-4 grid justify-items-center md:justify-items-start">
					@component("components.buttons.button", ["attributeEx" => "type=\"button\" name=\"addDoc\" id=\"addDoc\"", "variant" => "warning" ])
						@slot("label")
							<span class="icon-plus"></span>
							<span>Agregar documento</span>
						@endslot
					@endcomponent
				</div>
			@endcomponent
			<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mt-4">
				@component("components.buttons.button", ["classEx"	=>	"enviar",			"variant"	=>	"primary",		"attributeEx"	=>	"type=\"submit\" name=\"enviar\"",	"label"	=>	"ENVIAR SOLICITUD"]) @endcomponent
				@component("components.buttons.button", ["classEx"	=>	"save",				"variant"	=>	"secondary",	"attributeEx"	=>	"type=\"submit\" name=\"save\" id=\"save\" formaction=\"".route('movements-accounts.adjustment.unsent')."\"" ,	"label"	=>	"GUARDAR SIN ENVIAR"]) @endcomponent
				@component("components.buttons.button", ["classEx"	=>	"btn-delete-form",	"variant"	=>	"reset",		"attributeEx"	=>	"type=\"reset\"  name=\"borra\"",	"label"	=>	"Borrar campos"]) @endcomponent
			</div>
		@endcomponent
	</div>
@endsection
@section('scripts')
<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
<script src="{{ asset('js/jquery-ui.js') }}"></script>
<script src="{{ asset('js/jquery.numeric.js') }}"></script>
<script src="{{ asset('js/datepicker.js') }}"></script>
<script type="text/javascript">
	function validate()
	{
		$.validate(
		{
			form: '#container-alta',
			modules		: 'security',
			onError   : function($form)
			{
				swal('', '{{ Lang::get("messages.form_error") }}', 'error');
			},
			onSuccess : function($form)
			{
				path	= $('.path').length;
					if(path>0)
					{
						pas = true;
						$('.path').each(function()
						{
							if($(this).val()=='')
							{
								pas = false;
							}
						});
						if(!pas)
						{
							swal('', 'Por favor cargue los documentos faltantes.', 'error');
							return false;
						}
					}
				if($('.js-folios').val() == "")
				{
					if(folios != 0)
					{
						swal("Cargando",
						{
						icon: '{{ asset(getenv('LOADING_IMG')) }}',
						button: false,
						closeOnClickOutside: false,
						closeOnEsc: false
						});
						return true;
					}
					else
					{
						$('.js-folios').parent().find('.form-error').remove();
						$('.js-folios').parent().append('<span class="help-block form-error">Este campo es obligatorio</span>');
						swal('', 'Por favor seleccione un folio y agregue por lo menos una solicitud.', 'error');
						return false;
					}
				}
				else
				{
					if(folios != 0)
					{
						if($('.js-folios').val() != "")
						{
							swal('', 'Tiene un folio seleccionado para agregar o para cerrar.', 'info');
							return false;
						}
						else
						{
							swal("Cargando",
							{
							icon: '{{ asset(getenv('LOADING_IMG')) }}',
							button: false,
							closeOnClickOutside: false,
							closeOnEsc: false
							});
							return true;
						}
					}
					else
					{
						$('.js-folios').parent().find('.form-error').remove();
						swal('', 'Por favor agregue por lo menos una solicitud.', 'error');
						return false;
					}
				}
				if($('.total_adjustment').val() == 0 || $('.total_adjustment').val() == NULL)
				{
					swal('', 'El total no puede quedar en 0.', 'error');
					return false;
				}
			}
		});
	}
	$(document).ready(function()
	{
		validate();
		folios			=	[];
		count			=	0;
		countB			=	{{ $taxesCount }};
		countBilling	=	{{ $taxesCountBilling }};
		$('.phone,.clabe,.account,.cp').numeric(false);
		$('.price, .dis').numeric({ negative : false });
		$('.quanty').numeric({ negative : false, decimal : false });
		$('.amount,.tquanty,.tprice,.tamount,.descuento,.totaliva,.subtotal,.total,.amountAdditional,.amountAdditional_billing,retentionAmount,retentionAmount_billing',).numeric({ altDecimal: ".", decimalPlaces: 2 });
		$(function() 
		{
			$("#datepicker, .datepicker2").datepicker({  dateFormat: "dd-mm-yy" });
		});

		generalSelect({'selector': '.js-projects-destination', 'model': 21});
		generalSelect({'selector': '.js-users', 'model': 13});
		generalSelect({'selector': '.js-accounts-destination', 'depends': '.js-enterprises-destination', 'model': 23});
		generalSelect({'selector':'.js-folios', 'depends':'.js-enterprises, [name=\"folios_adjustment[]\"]','model': 29});

		@php
			$selects = collect([
				[
					"identificator"				=> ".js-enterprises-destination",
					"placeholder"				=> "Seleccione la empresa",
					"language"					=> "es",
					"maximumSelectionLength"	=> "1"
				],
				[
					"identificator"				=> ".js-areas-destination",
					"placeholder"				=> "Seleccione la dirección",
					"language"					=> "es",
					"maximumSelectionLength"	=> "1"
				],
				[
					"identificator"				=> ".js-departments-destination",
					"placeholder"				=> "Seleccione el departamento",
					"language"					=> "es",
					"maximumSelectionLength"	=> "1"
				],
				[
					"identificator"				=> ".js-enterprises",
					"placeholder"				=> "Seleccione la empresa",
					"language"					=> "es",
					"maximumSelectionLength"	=> "1"
				],
				[
					"identificator"				=> ".js-form-pay",
					"placeholder"				=> "Seleccione la forma de pago",
					"language"					=> "es",
					"maximumSelectionLength"	=> "1"
				],
				[
					"identificator"				=> "[name=\"type_currency\"]",
					"placeholder"				=> "Seleccione el tipo de moneda",
					"language"					=> "es",
					"maximumSelectionLength"	=> "1"
				]
			]);
		@endphp
		@component('components.scripts.selects',["selects" => $selects]) @endcomponent
		$(document).on('click','#save',function()
		{
			$('.remove').removeAttr('data-validation');
			$('.removeselect').removeAttr('required');
			$('.removeselect').removeAttr('data-validation');
			$('.request-validate').removeClass('request-validate');
			folios = 1;
		})
		.on('click','.btn-delete-form',function(e)
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
					$('.folios').html('');
					$('.generalInput').val('');
					$('.removeselect').val(null).trigger('change');
				}
				else
				{
					swal.close();
				}
			});
		})
		.on('change','.js-enterprises',function()
		{
			$('.js-folios').empty();
		})
		.on('change','[name="enterpriseid"]',function()
		{
			$('[name="enterpriseid_origin"]').val($(this, 'option:selected').val());
		})
		.on('change','.js-folios',function()
		{
			$('.js-folios').parent().find('.form-error').remove();
			folio = $(this).val();
			if (($('.folios_adjustment').val() == '' || $('.folios_adjustment').val() == undefined) && (folio == undefined || folio == '')) 
			{
				$('.js-enterprises').removeAttr('disabled');
			}
			else
			{
				swal(
				{
					icon	: '{{ asset(getenv('LOADING_IMG')) }}',
					button	: false
				});
				$('.js-enterprises').removeAttr('disabled');
				$('#detail').empty();
				$.ajax(
				{
					type 	: 'post',
					url 	: '{{ route("movements-accounts.adjustment.create.detailrequest") }}',
					data 	: {'folio':folio[0]},
					success : function(data)
					{
						$('#detail').html(data).stop(true,true).slideDown().show();
						$('.js-enterprises').attr('disabled',true);
						$('#error_request').hide();
						$('.folios').hide();
						swal.close();
					},
					error	: function()
					{
						swal.close();
					}
				});
			}
		})
		.on('click','#close_request',function()
		{
			$('.js-folios').val(null).trigger('change');
			
			$('#detail').stop(true,true).slideUp().hide();
			$('#detail').empty();
			
			if ($('.folios_adjustment').val() == '' || $('.folios_adjustment').val() == undefined) 
			{
				$('#error_request').show();
				$('.folios').hide();
			}
			else
			{
				$('#error_request').hide();
				$('.folios').show();
			}
		})
		.on('click','#add_request',function()
		{
			enterprise_request	=	$('input[name="enterprise_request"]').val();
			department_request	=	$('input[name="department_request"]').val();
			direction_request	=	$('input[name="direction_request"]').val();
			account_request		=	$('input[name="account_request"]').val();
			project_request		=	$('input[name="project_request"]').val();

			subtotal_request	=	$('input[name="subtotal_request"]').val();
			iva_request			=	$('input[name="iva_request"]').val();
			tax_request			=	$('input[name="tax_request"]').val();
			retention_request	=	$('input[name="retention_request"]').val();
			total_request		=	$('input[name="total_request"]').val();

			sumTotales();

			@php
				$component	=	"";
				$modelTable = 
				[
					["Proyecto", [["kind" => "components.labels.label", "label" => $project],["kind" => "components.inputs.input-text", "attributeEx" => "type=\"hidden\" name=\"project_request\" value=\"".$project."\""]]],
				];
				
				$input = view('components.templates.outputs.table-detail',[
					"variant"	 => true,
					"modelTable" => 
					[
						["Empresa",					[["kind"	=>	"components.labels.label",	"classEx"	=>	"enterpriceClass"]]],
						["Dirección",				[["kind"	=>	"components.labels.label",	"classEx"	=>	"directionClass"]]],
						["Departamento",			[["kind"	=>	"components.labels.label",	"classEx"	=>	"departmentClass"]]],
						["Clasificación de gasto",	[["kind"	=>	"components.labels.label",	"classEx"	=>	"accountClass"]]],
						["Proyecto",				[["kind"	=>	"components.labels.label",	"classEx"	=>	"projectClass"]]],
					],
					"title"		 =>
					[
						["kind" => "components.labels.label",		"classEx" => "text-white titleClass text-center", "classParent" => "w-10/12 flex justify-center items-center"],
						["kind" => "components.inputs.input-text",	"attributeEx" => "type=\"hidden\"", "classEx" => "del-folio"],
						["kind" => "components.inputs.input-text",	"attributeEx" => "type=\"hidden\"", "classEx" => "subtotal_request"],
						["kind" => "components.inputs.input-text",	"attributeEx" => "type=\"hidden\"", "classEx" => "tax_request"],
						["kind" => "components.inputs.input-text",	"attributeEx" => "type=\"hidden\"", "classEx" => "retention_request"],
						["kind" => "components.inputs.input-text",	"attributeEx" => "type=\"hidden\"", "classEx" => "iva_request"],
						["kind" => "components.inputs.input-text",	"attributeEx" => "type=\"hidden\"", "classEx" => "total_request"],
						["kind" => "components.inputs.input-text",	"classEx" 	 => "folios_adjustment","attributeEx" 	=> "type=\"hidden\" name=\"folios_adjustment[]\""],
						["kind" => "components.buttons.button",		"attributeEx" => "type=\"button\"", "label" => "<span class=\"icon-x\"></span>", "classEx" => "delete-folio",	"variant"	=>	"red"]
					]
				]);
				$component .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "", "<div class=\"content-center container-folio my-4 mx-6 col-span-1\">".
				$input->render()."</div>"));
			@endphp

			component = '{!!preg_replace("/(\r)*(\n)*/", "", $component)!!}';
			table_detail = $(component);
			table_detail.find(".enterpriceClass").text(enterprise_request);
			table_detail.find(".directionClass").text(direction_request);
			table_detail.find(".departmentClass").text(department_request);
			table_detail.find(".accountClass").text(account_request);
			table_detail.find(".projectClass").text(project_request);
			table_detail.find(".titleClass").text("FOLIO #"+$('select[name="folios"] option:selected').val());
			table_detail.find(".folios_adjustment").val($('select[name="folios"] option:selected').val());
			table_detail.find(".del-folio").val($('select[name="folios"] option:selected').val());
			table_detail.find(".subtotal_request").val(subtotal_request);
			table_detail.find(".tax_request").val(tax_request);
			table_detail.find(".retention_request").val(retention_request);
			table_detail.find(".iva_request").val(iva_request);
			table_detail.find(".total_request").val(total_request);
			$('.folios').append(table_detail);
			$('#error_request').hide();
			$('#detail').stop(true,true).slideUp().hide();
			$('#detail').empty();
			$('.folios').show();

			$('.js-folios').empty();
			enterprise	= $('select[name="enterpriseid"] option:selected').val();
			folios		= [];
			$('.folios_adjustment').each(function()
			{
				folios.push(Number($(this).val()));
			});
			swal(
			{
				icon	: '{{ asset(getenv('LOADING_IMG')) }}',
				button	: false,
				timer	: 500
			});
		})
		.on('click','.delete-folio',function()
		{
			swal(
			{
				icon	: '{{ asset(getenv('LOADING_IMG')) }}',
				button	: false,
				timer	: 500
			});

			folio = $(this).parents('div.container-folio').find('.del-folio').val();
			subtotal_request		= $(this).parents('div.container-folio').find('.subtotal_request').val();
			iva_request				= $(this).parents('div.container-folio').find('.iva_request').val();
			tax_request				= $(this).parents('div.container-folio').find('.tax_request').val();
			retention_request		= $(this).parents('div.container-folio').find('.retention_request').val();
			total_request			= $(this).parents('div.container-folio').find('.total_request').val();

			resTotales(subtotal_request,iva_request,tax_request,retention_request,total_request);
			
			$('.folios_adjustment').each(function()
			{
				if (folio == $(this).val()) 
				{
					$(this).remove();
				}
			});
			

			$('.js-folios').empty();
			enterprise	= $('select[name="enterpriseid"] option:selected').val();
			folios		= [];
			$('.folios_adjustment').each(function()
			{
				folios.push(Number($(this).val()));
			});

			$(this).parents('div.container-folio').remove();
		})
		.on('change','.js-enterprises-destination',function()
		{
			$('.js-accounts-destination').empty();
			
		})
		.on('click','#addDoc',function()
		{
			@php
				$newDoc = view('components.documents.upload-files',[
					"attributeExInput"		=>	"name=\"path\", accept=\".pdf,.jpg,.png\"",
					"classExInput"			=>	"docInput pathActioner",
					"attributeExRealPath"	=>	"name=\"realPath[]\"",
					"classExRealPath"		=>	"path",
					"classExDelete"			=>	"delete-doc",
				])->render();
			@endphp
			newDoc	= '{!!preg_replace("/(\r)*(\n)*/", "", $newDoc)!!}';
			newdoc = $(newDoc);
			$('#documents').append(newdoc);
			$(function() 
			{
				$( ".datepicker" ).datepicker({ maxDate: 0, dateFormat: "dd-mm-yy" });
			});
			$('#documents').removeClass('hidden');
		})
		.on('click','.span-delete',function()
		{
			$(this).parents('span').remove();
		})
		.on('change','.docInput.pathActioner',function(e)
		{
			filename		= $(this);
			uploadedName 	= $(this).parent('.uploader-content').siblings('input[name="realPath[]"]');
			extention		= /\.jpg|\.png|\.jpeg|\.pdf/i;
			
			if (filename.val().search(extention) == -1)
			{
				swal('', 'El tipo de archivo no es soportado, por favor seleccione una imagen jpg, png o un archivo pdf', 'warning');
				$(this).val('');
			}
			else if (this.files[0].size>315621376)
			{
				swal('', 'El tamaño máximo de su archivo no debe ser mayor a 300Mb', 'warning');
			}
			else
			{
				$(this).css('visibility','hidden').parent('.uploader-content').addClass('loading').removeClass(function (index, css)
				{
					return (css.match (/\bimage_\S+/g) || []).join(' ');
				});
				formData	= new FormData();
				formData.append(filename.attr('name'), filename.prop("files")[0]);
				formData.append(uploadedName.attr('name'),uploadedName.val());
				$.ajax(
				{
					type		: 'post',
					url			: '{{ route("movements-accounts.upload") }}',
					data		: formData,
					contentType	: false,
					processData	: false,
					success		: function(r)
					{
						if(r.error=='DONE')
						{
							$(e.currentTarget).removeAttr('style').parent('.uploader-content').removeClass('loading').addClass('image_success');
							$(e.currentTarget).parent('.uploader-content').siblings('input[name="realPath[]"]').val(r.path);
							$(e.currentTarget).val('');
						}
						else
						{
							swal('',r.message, 'error');
							$(e.currentTarget).removeAttr('style').parent('.uploader-content').removeClass('loading');
							$(e.currentTarget).val('');
							$(e.currentTarget).parent('.uploader-content').siblings('input[name="realPath[]"]').val('');
						}
					},
					error: function()
					{
						swal('', 'Ocurrió un error durante la carga del archivo, intente de nuevo, por favor', 'error');
						$(e.currentTarget).removeAttr('style').parent('.uploader-content').removeClass('loading');
						$(e.currentTarget).val('');
						$(e.currentTarget).parent('.uploader-content').siblings('input[name="realPath[]"]').val('');
					}
				})
			}
		})
		.on('click','.delete-doc',function()
		{
			swal(
			{
				icon	: '{{ asset(getenv('LOADING_IMG')) }}',
				button	: false
			});
			actioner		= $(this);
			uploadedName	= $(this).parents('.docs-p').find('input[name="realPath[]"]');
			formData		= new FormData();
			formData.append(uploadedName.attr('name'),uploadedName.val());
			$.ajax(
			{
				type		: 'post',
				url			: '{{ route("movements-accounts.upload") }}',
				data		: formData,
				contentType	: false,
				processData	: false,
				success		: function(r)
				{
					swal.close();
					actioner.parents('.docs-p').remove();
				},
				error		: function()
				{
					swal.close();
					actioner.parents('.docs-p').remove();
				}
			});
			$(this).parents('div.docs-p').remove();
			if($('.docs-p').length<1)
			{
				$('#documents').addClass('hidden');
			}
		});
	});

	function resTotales(subtotal_request,iva_request,tax_request,retention_request,total_request)
	{
		
		subtotal_adjustment		= $('input[name="subtotal_adjustment"]').val();
		iva_adjustment			= $('input[name="iva_adjustment"]').val();
		tax_adjustment			= $('input[name="tax_adjustment"]').val();
		retention_adjustment	= $('input[name="retention_adjustment"]').val();
		total_adjustment		= $('input[name="total_adjustment"]').val();
		
		subtotal_new	= Number(subtotal_adjustment) - Number(subtotal_request);
		iva_new			= Number(iva_adjustment) - Number(iva_request);
		tax_new			= Number(tax_adjustment) - Number(tax_request);
		retention_new	= Number(retention_adjustment) - Number(retention_request);
		total_new		= Number(total_adjustment) - Number(total_request);

		$('input[name="subtotal_adjustment"]').val(Number(subtotal_new).toFixed(2));
		$('input[name="iva_adjustment"]').val(Number(iva_new).toFixed(2));
		$('input[name="tax_adjustment"]').val(tax_new);
		$('input[name="retention_adjustment"]').val(Number(retention_new).toFixed(2));
		$('input[name="total_adjustment"]').val(Number(total_new).toFixed(2));
	}

	function sumTotales()
	{
		
		subtotal_adjustment		= $('input[name="subtotal_adjustment"]').val();
		iva_adjustment			= $('input[name="iva_adjustment"]').val();
		tax_adjustment			= $('input[name="tax_adjustment"]').val();
		retention_adjustment	= $('input[name="retention_adjustment"]').val();
		total_adjustment		= $('input[name="total_adjustment"]').val();
		
		subtotal_request		= $('input[name="subtotal_request"]').val();
		
		iva_request				= $('input[name="iva_request"]').val();
		tax_request				= $('input[name="tax_request"]').val();
		retention_request		= $('input[name="retention_request"]').val();
		total_request			= $('input[name="total_request"]').val();

		subtotal_new	= Number(subtotal_adjustment) + Number(subtotal_request);
		iva_new			= Number(iva_adjustment) + Number(iva_request);
		tax_new			= Number(tax_adjustment) + Number(tax_request);
		retention_new	= Number(retention_adjustment) + Number(retention_request);
		total_new		= Number(total_adjustment) + Number(total_request);
		
		$('input[name="subtotal_adjustment"]').val(Number(subtotal_new).toFixed(2));
		$('input[name="iva_adjustment"]').val(Number(iva_new).toFixed(2));
		$('input[name="tax_adjustment"]').val(tax_new);
		$('input[name="retention_adjustment"]').val(Number(retention_new).toFixed(2));
		$('input[name="total_adjustment"]').val(Number(total_new).toFixed(2));
	}

	function total_cal()
	{
		subtotal	= 0;
		iva			= 0;
		amountAA 	= 0;
		amountR 	= 0;
		//descuento	= Number($('input[name="descuento"]').val());
		$("#body tr").each(function(i, v)
		{
			tempQ		= $(this).find('.tquanty').val();
			tempP		= $(this).find('.tprice').val();
			tempAA 		= null;
			tempR 		= null;
			$(".num_amountAdditional").each(function(i, v)
			{
				tempAA 		+= Number($(this).val());
			});
			$(".num_amountRetention").each(function(i, v)
			{
				tempR 		+= Number($(this).val());
			});
			
			//tempD		= $(this).find('.tdiscount').val();
			subtotal	+= (Number(tempQ)*Number(tempP));
			iva			+= Number($(this).find('.tiva').val());
			amountAA	= Number(tempAA);
			amountR		= Number(tempR);
		});
		total = (subtotal+iva + amountAA)-amountR;
		$('input[name="subtotal"]').val('$ '+Number(subtotal).toFixed(2));
		$('input[name="totaliva"]').val('$ '+Number(iva).toFixed(2));
		$('input[name="total"]').val('$ '+Number(total).toFixed(2));
		$(".amount_total").val('$ '+Number(total).toFixed(2));
		$('input[name="amountAA"]').val('$ '+Number(amountAA).toFixed(2));
		$('input[name="amountR"]').val('$ '+Number(amountR).toFixed(2));
	}
</script>
@endsection