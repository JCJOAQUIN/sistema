@extends('layouts.child_module')
@section('css')
	<link rel="stylesheet" href="{{ asset('css/daterangepicker.min.css') }}">
@endsection
@switch($request->kind)
	@case(1)
		@include('administracion.pagos.compra')
		@break

	@case(2)
		@include('administracion.pagos.complementonomina')
		@break

	@case(3)
		@include('administracion.pagos.gasto')
		@break

	@case(5)
		@include('administracion.pagos.prestamo')
		@break

	@case(8)
		@include('administracion.pagos.recurso')
		@break

	@case(9)
		@include('administracion.pagos.reembolso')
		@break

	@case(11)
		@include('administracion.pagos.ajuste')
		@break

	@case(12)
		@include('administracion.pagos.prestamoempresa')
		@break

	@case(13)
		@include('administracion.pagos.compraempresa')
		@break

	@case(14)
		@include('administracion.pagos.grupos')
		@break

	@case(15)
		@include('administracion.pagos.movimientosempresa')
		@break
@endswitch

@section('pay-form')
	
	@if(isset($request->purchases->first()->idPurchase))
		<div id="contentPartials" class="hidden">
			@component('components.labels.title-divisor', ["label" => "PROGRAMA DE PAGOS", "classEx" => "mt-12"]) @endcomponent
			@php
				if(isset($request))
				{
					$idPurchase			=	$request->purchases->first()->idPurchase;
					$partialPayments	=	$request->purchases->first()->partialPayment;
				}
				$modelHead	=	[];
				$body		=	[];
				$modelBody	=	[];
				$modelHead	=
				[
					[
						["value"	=>	""],
						["value"	=>	"Parcialidad"],
						["value"	=>	"Monto"],
						["value"	=>	"Fecha pago"],
						["value"	=>	"Estado"],
						["value"	=>	"Documento(s)"],
						["value"	=>	"Acción"]
					]
				];
				if (isset($partialPayments))
				{
					$countPartial = 1;
					foreach($partialPayments as $p)
					{
						$documentsInformation	=	[];
						$state = 0;
						if($p->date_delivery != null)
						{
							$state = 1;
						}
						if ($state == 0)
						{
							$disabledCheck	=	"check-small request-validate";
							$disabledBtn	=	"partial-edit";
						}
						else
						{
							$disabledCheck	=	"check-small request-validate hidden";
							$disabledBtn	=	"partial-edit hidden";
						}
						if ($p['tipe'])
						{
							$presign	=	"$";
							$percentage	=	"";
						}
						else if (!$p['tipe'])
						{
							$presign	=	"";
							$percentage	=	"%";
						}
						if ($state == 1 )
						{
							$statusPayment	=	"Pagado";
						}
						else
						{
							$statusPayment	=	"Sin pagar";
						}
						
						$docs_counter = $countPartial;
						if (count($p->documentsPartials)>0)
						{
							foreach ($p->documentsPartials as $doc)
							{
								$dateDoc = \Carbon\Carbon::parse($doc->datepath)->format('d-m-Y');
								$documentsInformation[]	=
								[
									[
										"kind"			=>	"components.buttons.button",
										"variant"		=>	"dark-red",
										"attributeEx"	=>	"target=\"_blank\" href=\"".url('docs/purchase/'.$doc->path)."\" title=\"".$doc->path."\"",
										"label"			=>	"<span class=\"icon-pdf\"></span>",
										"buttonElement"	=>	"a"
									],
									["label"			=>	$doc->name],
									["label"			=>	$dateDoc],
									[
										"kind"			=>	"components.inputs.input-text",
										"attributeEx"	=>	"type=\"hidden\"	name=\"path_p".$docs_counter."[]\"	value=\"".$doc->path."\"",
										"classEx"		=>	"path_p"
									],
									[
										"kink"			=>	"components.inputs.input-text",
										"attributeEx"	=>	"type=\"hidden\"	name=\"name_p".$docs_counter."[]\"	value=\"".$doc->name."\"",
										"classEx"		=>	"name_p"
									],
									[
										"kink"			=>	"components.inputs.input-text",
										"attributeEx"	=>	"type=\"hidden\"	name=\"folio_p".$docs_counter."[]\"	value=\"".htmlentities($doc->fiscal_folio)."\"",
										"classEx"		=>	"folio_p"
									],
									[
										"kink"			=>	"components.inputs.input-text",
										"attributeEx"	=>	"type=\"hidden\"	name=\"ticket_p".$docs_counter."[]\"	value=\"".htmlentities($doc->ticket_number)."\"",
										"classEx"		=>	"ticket_p"
									],
									[
										"kink"			=>	"components.inputs.input-text",
										"attributeEx"	=>	"type=\"hidden\"	name=\"monto_p".$docs_counter."[]\"	value=\"".$doc->amount."\"",
										"classEx"		=>	"monto_p"
									],
									[
										"kink"			=>	"components.inputs.input-text",
										"attributeEx"	=>	"type=\"hidden\"	name=\"timepath_p".$docs_counter."[]\"	value=\"".Carbon\Carbon::parse($doc->timepath)->format('H:i')."\"",
										"classEx"		=>	"timepath_p"
									],
									[
										"kink"			=>	"components.inputs.input-text",
										"attributeEx"	=>	"type=\"hidden\"	name=\"datepath_p".$docs_counter."[]\"	value=\"".$doc->datepath."\"",
										"classEx"		=>	"datepath_p"
									],
									[
										"kink"			=>	"components.inputs.input-text",
										"attributeEx"	=>	"type=\"hidden\"	name=\"num_p".$docs_counter."[]\"	value=\"0\"",
										"classEx"		=>	"num_p"
									],
								];
							}
						}
						else
						{
							$documentsInformation	=	["label"	=>	"Sin documento"];
						}
						
						$body	=
						[
							"classEx"	=>	"trPartial",
							[
								"content"	=>
								[
									"kind"			=>	"components.inputs.checkbox",
									"attributeEx"	=>	"id=\"idPartial_".$p['id']."\" type=\"checkbox\" name=\"checkPartial[]\" value=\"".$p['id']."\" ",
									"classEx"		=>	"checkbox",
									"classExLabel"	=>	"$disabledCheck",
									"label"			=>	'<span class="icon-check"></span>'
								]
							],
							[
								"content"	=>
								[
									[
										"kind"			=>	"components.inputs.input-text",
										"attributeEx"	=>	"id=\"idPartialPay\" type=\"hidden\" name=\"numPayPartial[]\" value=\"".$p['payment_id']."\"",
										"classEx"		=>	"numPayPartial"
									],
									[
										"kind"			=>	"components.inputs.input-text",
										"attributeEx"	=>	"type=\"hidden\" name=\"partial_id[]\" value=\"".$p['id']."\"",
										"classEx"		=>	"partial_id"
									],
									[
										"kind"			=>	"components.inputs.input-text",
										"attributeEx"	=>	"readonly value=\"".$countPartial."\"",
										"classEx"		=>	"partial numPartial input-table"
									],
								],
							],
							[
								"content"	=>
								[
									[
										"kind"			=>	"components.inputs.input-text",
										"attributeEx"	=>	"readonly value=\"".$presign.$p['payment'].$percentage."\"",
										"classEx"		=>	"partial_paymentText input-table"
									],
									[
										"kind"			=>	"components.inputs.input-text",
										"attributeEx"	=>	"type=\"hidden\" name=\"partialPayment[]\" value=\"".$p['payment']."\"",
										"classEx"		=>	"partial_payment"
									],
									[
										"kind"			=>	"components.inputs.input-text",
										"attributeEx"	=>	"type=\"hidden\" name=\"partialType[]\" value=\"".$p['tipe']."\"",
										"classEx"		=>	"partial_type"
									],
								],
							],
							[
								"content"	=>
								[
									"kind"			=>	"components.inputs.input-text",
									"attributeEx"	=>	"readonly name=\"partial_date[]\" value=\"".Carbon\Carbon::createFromFormat('Y-m-d',$p->date_requested)->format('d-m-Y')."\"",
									"classEx"		=>	"partial_date input-table"
								]
							],
							[
								"content"	=>
								[
									"kind"			=>	"components.inputs.input-text",
									"attributeEx"	=>	"readonly value=\"".$statusPayment."\"",
									"classEx"		=>	"partial_stateText partial input-table"
								],
								[
									"kind"			=>	"components.inputs.input-text",
									"attributeEx"	=>	"type=\"hidden\" value=\"".$state."\"",
									"classEx"		=>	"partial_state"
								]
							],
							[
								"classEx"	=>	"contentDocs",
								"content"	=>	$documentsInformation
							],
						];
						if(!$p->state && $state == 0)
						{
							$body[]["content"] = 
							[
								"kind"          => "components.buttons.button",
								"buttonElement" => "button",
								"label"         => "<span class=\"icon-pencil\"></span>",
								"classEx"       => "$disabledBtn",
								"attributeEX"   => "alt=\"Editar Parcialidad\" title=\"Editar Parcialidad\"",
								"variant"       => "success"
							];
						}
						$countPartial++;
						$modelBody[]	=	$body;
					}
				}
			@endphp
			@component('components.tables.table', ["modelHead" => $modelHead, "modelBody" => $modelBody])
				@slot('classEx')
					mt-4 table
				@endslot
				@slot('attributeExBody')
					id="partialBody"
				@endslot
			@endcomponent

			<div class="p-6">
				@component('components.containers.container-form')
					@slot('classEx')
						form-partial hidden
					@endslot
					@component('components.forms.form', ["attributeEx" => "method=\"POST\" id=\"form_partial\"", "methodEx" => "PUT"])
						<div class="col-span-2">
							@component('components.labels.label')
								@slot('classEx')
									font-bold
									mt-4
								@endslot
								Porcentaje/Neto
							@endcomponent
							@php
								$optionsPartial = collect(
									[
										['value'=>'0', 'description'=>'Porcentaje'], 
										['value'=>'1', 'description'=>'Neto']
									]
								);
								$classEx = "partialTypePayment js-partial";
								$attributeEx = "name=\"form_partial_type\" data-validation=\"required\"";
								
							@endphp
							@component('components.inputs.select', ['options' => $optionsPartial, 'attributeEx' => $attributeEx, 'classEx' => $classEx])
							@endcomponent
						</div>
						<div class="col-span-2">
							@component('components.labels.label') 
								@slot('classEx')
									font-bold
									mt-4
								@endslot
								Monto:
							@endcomponent
							@component('components.inputs.input-text')
								@slot('classEx')
									partialPayment
								@endslot
								@slot('attributeEx')
									placeholder="Ingrese monto o porcentaje" name="form_partial_amount" data-validation="required"
									@isset($globalRequests) disabled @endisset
								@endslot
							@endcomponent
						</div>
						<div class="col-span-2">
							@component('components.labels.label') 
								@slot('classEx')
									font-bold
									mt-4
								@endslot
								Fecha de Pago:
							@endcomponent
							@component('components.inputs.input-text')
								@slot('classEx')
									partialDate datepicker
								@endslot
								@slot('attributeEx')
									id = "datepickerPartial"
									readonly
									placeholder = "Ingrese la fecha"
									name = "form_partial_date"
									data-validation	= "required"
									@isset($globalRequests) disabled @endisset
								@endslot
							@endcomponent
							@component('components.inputs.input-text')
								@slot('classEx')
									partial_payment_id
								@endslot
								@slot('attributeEx')
									type="hidden"
									name="partial_payment_id"
								@endslot
							@endcomponent
						</div>
						
						<div class="col-span-2">
							@component('components.labels.label') 
								@slot('classEx')
									font-bold
									mt-4
								@endslot
								Resta por programar:
							@endcomponent
							@component('components.labels.label') 
								@slot('classEx')
									remainingPayment
								@endslot
							@endcomponent
							@component('components.inputs.input-text')
								@slot('attributeEx')
									type="hidden"
									name="partials_total"
								@endslot
							@endcomponent
						</div>
						<div class="w-full col-span-1 mb-2 flex items-center">
							@component('components.buttons.button', ["variant" => "success"])
								@slot('attributeEx') id="update_partial" type="submit" @endslot
								Actualizar 
							@endcomponent
							@component('components.buttons.button', ["variant" => "warning"])
								@slot('attributeEx') id="cancel_partial" type="button" @endslot
								Cancelar 
							@endcomponent
						</div>
					@endcomponent
				@endcomponent
			</div>
		</div>
	@endif
	
	@component('components.forms.form', ["attributeEx" => "method=\"POST\" id=\"container-alta\" action=\"".route('payments.store')."\"", "files" => true])
		<div id="checkPartials"></div>
		@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "DATOS DEL PAGO"]) @endcomponent
		@php
			$flag	=	true;
		@endphp
		@if($request->purchases()->exists())
			@php
				$totalPurchase	=	$request->purchases->first()->amount;
				$validation		=	$request->purchases->first()->provider->providerData->providerClassification()->exists() ? $request->purchases->first()->provider->providerData->providerClassification->classification : 0;
				$parameterMax	=	App\Parameter::where('parameter_name','MONTO_MAX_PROVEEDOR')->first()->parameter_value;
			@endphp	
			@if($totalPurchase > $parameterMax && $validation == 0)
				@php
					$flag	=	false;
				@endphp
				@component('components.labels.not-found', ["attributeEx" => "id=\"error_request\"", "text"	=>	"El proveedor se encuentra en Lista Negra o no está validado. Por favor solicite su validación."]) @endcomponent
			@endif
		@endif
		@if ($flag)
			@if ($request->status == 12)
				@component('components.labels.not-found', ["attributeEx" => "id=\"error_request\"", "text"	=>	"Ya no es posible rechazar un pago debido a que ya se ha relizado un pago anteriormente."]) @endcomponent
			@endif
			<div class="my-4 @if($request->status == 12) hidden @endif">
				@component('components.containers.container-approval')
					@slot('attributeExButton')
						name="status" id="aprobar" value="x"
						@if($request->status == 12) checked="checked" @endif
					@endslot
					@slot('attributeExButtonTwo')
						id="rechazar" name="status" value="13"
						@if($request->status == 12) disabled @endif
					@endslot
				@endcomponent
			</div>
			<div id="aceptar">
				@php
					$modelHead	=	[];
					$modelHead	=	["INGRESAR DATOS"];
					$modelBody	=	[];
				@endphp
				@component('components.tables.alwaysVisibleTable', ["modelHead"	=>	$modelHead, "modelBody"	=>	$modelBody])@endcomponent
				@component('components.containers.container-form')
					@if(isset($request->purchases->first()->idPurchase))
						@php
							$idpurchase	=	$request->purchases->first()->idPurchase;
							$partial	=	App\PartialPayment::where('purchase_id',$idpurchase)->where('date_delivery',null)->orderBy('date_requested')->first();
						@endphp
					@endif
					<div class="col-span-2">
						@component('components.labels.label') Empresa: @endcomponent
						@php
							foreach (App\Enterprise::orderBy('name','asc')->where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeEnt($option_id)->pluck('enterprise_id'))->get() as $enterprise)
							{
								if ($request->kind == 11)
								{
									if ($request->adjustment->first()->idEnterpriseDestiny == $enterprise->id)
									{
										$options[]	=	["value"	=>	$enterprise->id,	"description"	=>	$enterprise->name,	"selected"	=>	"selected"];
									}
								}
								elseif ($request->kind == 12)
								{
									if ($request->loanEnterprise->first()->idEnterpriseOrigin == $enterprise->id)
									{
										$options[]	=	["value"	=>	$enterprise->id,	"description"	=>	$enterprise->name,	"selected"	=>	"selected"];
									}
								}
								elseif ($request->kind == 13)
								{
									if ($request->purchaseEnterprise->first()->idEnterpriseOrigin == $enterprise->id)
									{
										$options[]	=	["value"	=>	$enterprise->id,	"description"	=>	$enterprise->name,	"selected"	=>	"selected"];
									}
								}
								elseif ($request->kind == 14)
								{
									if ($request->groups->first()->idEnterpriseDestiny == $enterprise->id)
									{
										$options[]	=	["value"	=>	$enterprise->id,	"description"	=>	$enterprise->name,"selected"	=>	"selected"];
									}
								}
								elseif ($request->kind == 15)
								{
									if ($request->movementsEnterprise->first()->idEnterpriseOrigin == $enterprise->id)
									{
										$options[]	=	["value"	=>	$enterprise->id,	"description"	=>	$enterprise->name,	"selected"	=>	"selected"];
									}
								}
								else
								{
									$options[]	=	["value"	=>	$enterprise->id,	"description"	=>	$enterprise->name,];
								}
							}
						@endphp
						@component('components.inputs.select', ["options" => $options])
							@slot('classEx')
								custom-select select-enterprise removeselect
							@endslot
							@slot('attributeEx')
								multiple="multiple" name="enterprise_id" style="position: relative;" data-validation="required"
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component('components.labels.label') Clasificación del gasto: @endcomponent
						@component('components.inputs.select')
							@slot('classEx')
								custom-select select-accounts relative
							@endslot
							@slot('attributeEx')
								name="account" data-validation="required" multiple="multiple"
							@endslot
						@endcomponent
						@component('components.inputs.input-text')
							@slot('attributeEx')
								type="hidden" name="idfolio" value="{{ $request->folio }}"
							@endslot
						@endcomponent
						@component('components.inputs.input-text')
							@slot('attributeEx')
								type="hidden" name="idkind" value="{{ $request->kind }}"
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component('components.labels.label') Subtotal: @endcomponent
						@component('components.inputs.input-text')
							@slot('attributeEx')
								placeholder="Ingrese el subtotal" type="text" name="subtotal" data-validation="required"
							@endslot
							@slot('classEx')
								subtotalRes
							@endslot
						@endcomponent
						@component('components.inputs.input-text')
							@slot('attributeEx')
								placeholder="$0.00" type="hidden" name="subtotalRes"
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component('components.labels.label') IVA: @endcomponent
						@component('components.inputs.input-text')
							@slot('attributeEx')
								placeholder="Ingrese el iva" type="text" name="iva" data-validation="required"
							@endslot
							@slot('classEx')
								ivaRes 
							@endslot
						@endcomponent
						@component('components.inputs.input-text')
							@slot('attributeEx')
								placeholder="$0.00" type="hidden" name="ivaRes"
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component('components.labels.label') Impuesto Adicional: @endcomponent
						@component('components.inputs.input-text')
							@slot('attributeEx')
								placeholder="Ingrese el impuesto" type="text" name="tax" data-validation="required"
							@endslot
							@slot('classEx')
								taxRes 
							@endslot
						@endcomponent
						@component('components.inputs.input-text')
							@slot('attributeEx')
								placeholder="$0.00" type="hidden" name="taxRes"
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component('components.labels.label') Retención: @endcomponent
						@component('components.inputs.input-text')
							@slot('attributeEx')
								placeholder="Ingrese la retención" type="text" name="retention" data-validation="required"
							@endslot
							@slot('classEx')
								retentionRes 
							@endslot
						@endcomponent
						@component('components.inputs.input-text')
							@slot('attributeEx')
								placeholder="$0.00" type="hidden" name="retentionRes"
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component('components.labels.label') Importe: @endcomponent
						@component('components.inputs.input-text')
							@slot('attributeEx')
								placeholder="Ingrese el importe" type="text" name="amountRes" data-validation="required"
							@endslot
							@slot('classEx')
								amountRes
							@endslot
						@endcomponent
						<input placeholder="$0.00" type="hidden" name="amountPartialSelected" class="amountPartialSelected">
						@component('components.inputs.input-text')
							@slot('attributeEx')
								placeholder="$0.00" type="hidden" name="amount"
							@endslot
							@slot('classEx')
								amount
							@endslot
						@endcomponent
						<input type="hidden" name="idfolio" value="{{ $request->folio }}">
					<input type="hidden" name="idkind" value="{{ $request->kind }}">
					</div>
					<div class="col-span-2">
						@component('components.labels.label') Fecha de pago: @endcomponent
						@component('components.inputs.input-text')
							@slot('attributeEx')
								placeholder="Ingrese la fecha" type="text" name="paymentDate" readonly="readonly" data-validation="required"
							@endslot
							@slot('classEx')
								datepicker
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component('components.labels.label') Tasa de cambio (Opcional): @endcomponent
						@component('components.inputs.input-text')
							@slot('attributeEx')
								type="text" name="exchange_rate" placeholder="Ingrese la tasa de cambio"
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component('components.labels.label') Descripción de tasa de cambio (Opcional): @endcomponent
						@component('components.inputs.input-text')
							@slot('attributeEx')
								name="exchange_rate_description" placeholder="Ingrese la descripción de la tasa de cambio"
							@endslot
						@endcomponent
					</div>
					<div class="md:col-span-4 col-span-2">
						@component('components.labels.label')
							Comentarios (Opcional):
						@endcomponent
						@component('components.inputs.text-area')
							@slot('attributeEx')
								type="text" name="commentaries" placeholder="Ingrese un comentario"
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component('components.labels.label')
							Comprobante:
						@endcomponent
					</div>
					<div class="col-span-2 md:col-span-4 grid grid-cols-1 md:grid-cols-2 gap-6" id="documents"></div>
					<div class="col-span-2 md:col-span-4 grid justify-items-center md:justify-items-start">
						@component('components.buttons.button', ["variant" => "warning"])
							@slot('attributeEx')
								type=button name="addDoc" id="addDoc"
							@endslot
							<span class="icon-plus"></span>
							<span>Agregar documento</span>
						@endcomponent
					</div>
				@endcomponent
			</div>
			<div class="form-container" id="viewCode" style="display: none;">
				<div class="table-responsive">
					@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "CÓDIGO DE LIBERACIÓN"]) @endcomponent
				</div>
				@component('components.inputs.input-text')
					@slot('classEx')
						mt-4 font-bold div-code text-center
					@endslot
					@slot('attributeEx')
						type="text"  name="code" id="code" placeholder="Ingrese el código de autorización" readonly="readonly" value="{{ rand(10000000,99999999) }}"
					@endslot
				@endcomponent
				@component('components.labels.label')
					@slot('classEx')
						mt-4
						text-center
					@endslot
					Proporcione éste código al solicitante para liberar la solicitud.
				@endcomponent
			</div>
			<div id="rechaza" class="md:col-span-4 col-span-2 hidden">
				@component('components.labels.label') Comentarios (opcional)
				@endcomponent
				@component('components.inputs.text-area')
					@slot('attributeEx') name="paymentComment" @endslot
				@endcomponent
			</div>
			<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mt-4">
				@component("components.buttons.button", ["variant" => "primary", "attributeEx" => "type=\"submit\" name=\"enviar\" value=\"ENVIAR PAGO\"", "classEx" => "enviar mr-2", "label" => "ENVIAR PAGO"]) @endcomponent
				@php
					$href	=	isset($option_id) ? url(getUrlRedirect($option_id)) : url(getUrlRedirect($child_id));
				@endphp
				@component('components.buttons.button', ["variant" => "reset", "attributeEx" => "href=\"".$href."\"", "classEx" => "load-actioner", "buttonElement" => "a", "label" => "REGRESAR"]) @endcomponent
			</div>
			@component('components.modals.modal')
				@slot('id')
					viewPayment
				@endslot
				@slot('attributeEx')
					tabindex="-1"
				@endslot
				@slot('modalBody')
					@component('components.labels.title-divisor')
						DATOS DEL PAGO
					@endcomponent
				@endslot
				@slot('modalFooter')
					@component('components.buttons.button', ["variant" => "red", "attributeEx" => "data-dismiss=\"modal\" type=\"button\"", "classEx" => "", "label" => "<span class='icon-x'></span> Cerrar"]) @endcomponent
				@endslot
			@endcomponent
		@endif
	@endcomponent
@endsection

@section('scripts')
	<script src="{{ asset('js/jquery.numeric.js') }}"></script>
	<script src="{{ asset('js/daterangepicker.js') }}"></script>
	<script>
		$(document).ready(function(e)
		{
			generalSelect({'selector': '.select-accounts', 'depends': '.select-enterprise', 'model': 10});
			@php
				$selects = collect([
					[
						"identificator"				=> ".js-partial",
						"placeholder"				=> "Seleccione un porcentaje/neto",
						"language"					=> "es",
						"maximumSelectionLength"	=> "1"
					],
					[
						"identificator"				=> ".select-enterprise",
						"placeholder"				=> "Seleccione la empresa",
						"language"					=> "es",
						"maximumSelectionLength"	=> "1"
					]
				]);
			@endphp
			@component("components.scripts.selects",["selects" => $selects]) @endcomponent
			$('#aceptar').addClass('hidden');
			checkAcept	=	$('[name="status"]:checked').val();
			if (checkAcept == "x")
			{
				$('#aceptar').removeClass('hidden');
			}
			$.validate(
			{
				form: '#container-alta',
				modules: 'security',
				onError   : function()
				{
					swal('', '{{ Lang::get("messages.form_error") }}', 'error');
				},
				onSuccess : function()
				{
					pathFlag = true;
					$('.path').each(function()
					{
						path = $(this).val();
						
						if(path == "")
						{
							pathFlag = false;
						}
					});

					amount     = $('.amount').val();
					exchange_rate =  $('[name="exchange_rate"]').val() != "" ? Number($('[name="exchange_rate"]').val()).toFixed() : 1;

					if(!pathFlag) 
					{
						swal('','Por favor agregue los documentos faltantes.','error');
						return false;
					}
					else if ($('[name="checkPartial[]"]:checked').length > 0) 
					{
						amountPartialSelected = Number($('[name="amountPartialSelected"]').val()).toFixed(2);
						amountRes 	= Number($('[name="amount"]').val()).toFixed(2);
						if (amountPartialSelected != amountRes) 
						{
							swal('','El importe no coincide con el total de las parcialidades seleccionadas.','error');
							return false;
						}
					}
					else if(exchange_rate != "" && exchange_rate == '0' || exchange_rate == '0.00' || exchange_rate == '0.0')
					{
						$('[name="exchange_rate"]').removeClass('valid').addClass('error')
						swal('','La tasa de cambio no puede ser 0, por favor verifique los datos.','error');
						return false;
					}
					else if(amount <= 0 || amount == '' || amount == 'NaN' || amount == null)
					{
						$('.amount').removeClass('valid').addClass('error');
						swal('','El importe no puede ser cero ó negativo, por favor verifique los datos.','error');
						return false;
					}
					else
					{
						swal({
							icon				:	'{{ asset(getenv('LOADING_IMG')) }}',
							button				:	false,
							closeOnClickOutside	:	false,
							closeOnEsc			:	false
						});
						return true;
					}
				}
			});

			//Llenado de formulario Datos de pago, las variables Res, son las variabnlea auxiliares que se ocupan para saber si el usuario 
			//no esta pagando mas de la cuenta, esta solucion es una replica de como se estaba manejando antes el codigo
			$('input[name="subtotal"], input[name="tax"], input[name="iva"], input[name="retention"]').numeric({ negative : false, altDecimal: '.', decimalPlaces: 2 });
			$('input[name="amount"]').numeric({ altDecimal: '.', decimalPlaces: 2 });
			$('input[name="exchange_rate"]').numeric({ negative : false, altDecimal: ".", decimalPlaces: 4 });

			amount 		= ($('#restaTotal').val()) ? 		parseFloat($('#restaTotal').val()) : 0;
			subtotal	= ($('#restaSubtotal').val()) ?	 	parseFloat($('#restaSubtotal').val()) : 0;
			iva			= ($('#restaIva').val()) ? 			parseFloat($('#restaIva').val()) : 0;
			tax			= ($('#restaTax').val()) ? 			parseFloat($('#restaTax').val()) : 0;
			retention	= ($('#restaRetention').val()) ? 	parseFloat($('#restaRetention').val()) : 0;

			$('input[name="subtotalRes"]').attr('value',subtotal);
			$('input[name="taxRes"]').attr('value',tax);
			$('input[name="ivaRes"]').attr('value',iva);
			$('input[name="retentionRes"]').attr('value',retention);
			$('input[name="amountRes"]').attr('value',amount);

			$('input[name="subtotal"]').attr('value',subtotal);
			$('input[name="tax"]').attr('value',tax);
			$('input[name="iva"]').attr('value',iva);
			$('input[name="retention"]').attr('value',retention);
			$('input[name="amount"]').attr('value',amount);
			$('[name="amount"],[name="exchange_rate"]').on("contextmenu",function(e)
			{
				return false;
			});
			code = $('#codeIsTrue').val();
			if (code != undefined) 
			{
				$('#viewCode').show();
			}

			$('input[name="amount"]').numeric({ negative : false, altDecimal: ".", decimalPlaces: 2 });
			$('input[name="exchange_rate"]').numeric({ negative : false, altDecimal: ".", decimalPlaces: 4, negative:false});

			@component('components.scripts.selects',["selects" => $selects]) @endcomponent
			if($('.trPartial').length > 0)
			{
				$('#contentPartials').removeClass('hidden');
			}

			$(function()
			{
				$('.partialPayment').numeric({ negative : false, altDecimal: ".", decimalPlaces: 2  });
				
				$("#datepickerPartial").datepicker({ minDate: 0, dateFormat: "dd-mm-yy" });
			});
			
			$('.js-partial').select2(
			{
				placeholder				: ' Seleccione un porcentaje/neto',
				language				: "es",
				maximumSelectionLength	: 1,
				width 					: '100%'
			})
			.on("change",function(e)
			{
				if($(this).val().length>1)
				{
					$(this).val($(this).val().slice(0,1)).trigger('change');
				}
			});
				
			@if($request->purchases()->exists() && $request->purchases->first()->partialPayment()->exists() && $request->purchases->first()->partialPayment->where('date_delivery','=',null)->count()>0 )
				$(document).on('click','.checkbox', function()
				{
					$('#checkPartials').empty();
					subtotal 		= 0;
					iva 			= 0;
					amountPay		= 0;

					if($(this).is(':checked'))
					{
						$(this).parents('.tr').find('.partial-edit').prop('disabled',true);
					}
					else
					{
						$(this).parents('.tr').find('.partial-edit').prop('disabled',false);
					}

					$('.checkbox').each(function(i,v)
					{
						
						if($(this).is(':checked'))
						{
							value = $(this).val();
							$('#checkPartials').append($('<input type="hidden" name="checkPartial[]" value="'+value+'">'));
						}

						typePay			= $(this).parents('.trPartial').find('.partial_type').val();
						totalPayment	= Number($('.amount_purchase').val());
						
						if(typePay == 0)
						{
							if($(this).is(':checked'))
							{
								@if($request->purchases->first()->tax > 0)
									valuePayment	= Number($(this).parents('.trPartial').find('.partial_payment').val());

									amountPay		= (valuePayment/100*totalPayment) + amountPay;
									iva				= (amountPay * 0.16) / 1.16;
									subtotal 		= (amountPay - iva);
								@else
									valuePayment	= Number($(this).parents('.trPartial').find('.partial_payment').val());

									amountPay		= (valuePayment/100*totalPayment) + amountPay;
									iva				= 0;
									subtotal 		= (amountPay - iva);
								@endif
							}
						}

						if(typePay == 1)
						{
							if($(this).is(':checked'))
							{
								@if($request->purchases->first()->tax > 0)
									valuePayment	= Number($(this).parents('.trPartial').find('.partial_payment').val());
				
									amountPay 		= valuePayment + amountPay;
									iva 			= (amountPay * 0.16) / 1.16;
									subtotal 		= (amountPay - iva);
								@else
									valuePayment	= Number($(this).parents('.trPartial').find('.partial_payment').val());
				
									amountPay 		= valuePayment + amountPay;
									iva 			= 0;
									subtotal 		= (amountPay - iva);
								@endif
							}
						}
					});

					if ($('[name="checkPartial[]"]:checked').length > 0) 
					{
						$('.subtotalRes').val(Number(subtotal).toFixed(2));
						$('[name="subtotalRes"]').val(Number(subtotal).toFixed(2));
						$('.ivaRes').val(Number(iva).toFixed(2));
						$('[name="ivaRes"]').val(Number(iva).toFixed(2));
						$('.amount').val(Number(amountPay).toFixed(2));
						$('[name="amountRes"]').val(Number(amountPay).toFixed(2));
						$('[name="amountPartialSelected"]').val(Number(amountPay).toFixed(2));
					}
					else
					{
						restaSubtotal = $('#restaSubtotal').attr('data-restaSubtotal');
						restaIva = $('#restaIva').attr('data-restaIva');
						restaTax = $('#restaTax').attr('data-restaTax');
						restaRetention = $('#restaRetention').attr('data-restaRetention');
						restaTotal = $('#restaTotal').attr('data-restaTotal');

						$('.subtotalRes').val(Number(restaSubtotal).toFixed(2));
						$('[name="subtotalRes"]').val(Number(restaSubtotal).toFixed(2));
						$('.ivaRes').val(Number(restaIva).toFixed(2));
						$('[name="ivaRes"]').val(Number(restaIva).toFixed(2));
						$('.amount').val(Number(restaTotal).toFixed(2));
						$('[name="amountRes"]').val(Number(restaTotal).toFixed(2));
						$('[name="amountPartialSelected"]').val(Number(restaTotal).toFixed(2));
					}

				})
				.on('change','.retentionRes', function()
				{
					valueSub		= Number($('.subtotalRes').val());
					valueRetention	= Number($('.retentionRes').val());
					if(valueRetention > 0)
					{
						$('[name="retentionRes"]').val(Number(valueRetention).toFixed(2));
						$('.subtotalRes').val(Number(valueSub+valueRetention).toFixed(2));
						$('[name="subtotalRes"]').val(Number(valueSub+valueRetention).toFixed(2));
					}
					else
					{
						subtotal 		= 0;
						iva 			= 0;
						amountPay		= 0;
			
						$('.checkbox').each(function(i,v)
						{
							if($(this).is(':checked'))
							{
								typePay			= $(this).parents('.trPartial').find('.partial_type').val();
								totalPayment	= Number($("#input-extrasmallData").val());
								
								if(typePay == 0)
								{
									valuePayment	= Number($(this).parents('.trPartial').find('.partial_payment').val());
									
									amountPay		= (valuePayment/100*totalPayment) + amountPay;
									iva				= (amountPay * 0.16) / 1.16;
									subtotal 		= (amountPay - iva);
								}
								if(typePay == 1)
								{
									valuePayment	= Number($(this).parents('.trPartial').find('.partial_payment').val());
			
									amountPay 		= valuePayment + amountPay;
									iva 			= (amountPay * 0.16) / 1.16;
									subtotal 		= (amountPay - iva);
								}
							}
						});
						$('.subtotalRes').val(Number(subtotal).toFixed(2));
						$('[name="subtotalRes"]').val(Number(subtotal).toFixed(2));
						$('.ivaRes').val(Number(iva).toFixed(2));
						$('[name="ivaRes"]').val(Number(iva).toFixed(2));
						$('.amount').val(Number(amountPay).toFixed(2));
						$('[name="amountRes"]').val(Number(amountPay).toFixed(2));
						$('[name="retentionRes"]').val(0);
					}
				})
				.on('change','.taxRes', function()
				{
					valueSub		= Number($('.subtotalRes').val());
					valueAdditional	= Number($('.taxRes').val());
					if(valueAdditional > 0)
					{
						$('[name="taxRes"]').val(Number(valueAdditional).toFixed(2));
						$('.subtotalRes').val(Number(valueSub-valueAdditional).toFixed(2));
						$('[name="subtotalRes"]').val(Number(valueSub-valueAdditional).toFixed(2));
					}
					else
					{
						subtotal 		= 0;
						iva 			= 0;
						amountPay		= 0;
						
						$('.checkbox').each(function(i,v)
						{
							if($(this).is(':checked'))
							{
								typePay			= $(this).parents('.trPartial').find('.partial_type').val();
								totalPayment	= Number($("#input-extrasmallData").val());
								
								if(typePay == 0)
								{
									valuePayment	= Number($(this).parents('.trPartial').find('.partial_payment').val());
									amountPay		= (valuePayment/100*totalPayment) + amountPay;
									iva				= (amountPay * 0.16) / 1.16;
									subtotal		= (amountPay - iva);
								}
								if(typePay == 1)
								{
									valuePayment	= Number($(this).parents('.trPartial').find('.partial_payment').val());
									amountPay 		= valuePayment + amountPay;
									iva 			= (amountPay * 0.16) / 1.16;
									subtotal 		= (amountPay - iva);
								}
							}
						});
						$('.subtotalRes').val(Number(subtotal).toFixed(2));
						$('[name="subtotalRes"]').val(Number(subtotal).toFixed(2));
						$('.ivaRes').val(Number(iva).toFixed(2));
						$('[name="ivaRes"]').val(Number(iva).toFixed(2));
						$('.amount').val(Number(amountPay).toFixed(2));
						$('[name="amountRes"]').val(Number(amountPay).toFixed(2));
						$('[name="taxRes"]').val(0);
					}
				})
				.on('change','.subtotalRes, .ivaRes, .taxRes, .retentionRes',function()
				{
					subtotalRes		= Number($('.subtotalRes').val());
					ivaRes			= Number($('.ivaRes').val());
					taxRes			= Number($('.taxRes').val());
					retentionRes	= Number($('.retentionRes').val());
					$('.amount').val(Number(subtotalRes+ivaRes+taxRes-retentionRes).toFixed(2));

					amountRes = Number($('.amount').val());


					$('[name="subtotalRes"]').val(subtotalRes).trigger('change');
					$('[name="ivaRes"]').val(ivaRes).trigger('change');
					$('[name="taxRes"]').val(taxRes).trigger('change');
					$('[name="retentionRes"]').val(retentionRes).trigger('change');
					$('[name="amountRes"]').val(amountRes).trigger('change');
				})
				.on('click','.partial-edit',function()
				{
					disableEdition();
					$('.form-partial').fadeIn();
					if($(this).parents('.trPartial').hasClass('partial_select'))
					{
						$('.trPartial').removeClass('partial_select');
						paymentAmount = 0;
					}
					else
					{ 
						$('.trPartial').removeClass('partial_select');

						tr		= $(this).parents('.trPartial');
						tr.addClass('partial_select');
						tr.addClass('marktr');

						payment	= tr.find('.partial_payment').val();
						type	= tr.find('.partial_type').val();
						date	= tr.find('.partial_date').val();
						partial_payment_id = tr.find('.partial_id').val();
						
						$('.partialTypePayment').val(parseInt(type)).trigger('change');
						paymentAmount = parseFloat(payment);
						$('.partialPayment').val(paymentAmount).trigger('change');
						$('.partialDate').val(date).trigger('change');
						$('.partial_payment_id').val(partial_payment_id).trigger('change');
						$('#addNewPartialPayment').addClass('partial_edit_button');
					}
					
					count 		= $(this).parents('.trPartial').find('.partial').val();
					countPaths	= count;

					$( ".datepicker" ).datepicker({ maxDate: 0, dateFormat: "dd-mm-yy" });
					$('.timepath_partial').daterangepicker({
						timePicker			: true,
						singleDatePicker	: true,
						timePicker24Hour	: true,
						timePickerIncrement	: 1,
						autoApply			: false,
						autoUpdateInput : true,
						locale : 
						{
							format : 'HH:mm',
							"applyLabel": "Seleccionar",
							"cancelLabel": "Cancelar",
						}
					}).on('show.daterangepicker', function (ev, picker) 
					{
						picker.container.find(".calendar-table").remove();
					});
					$('.partial-edit').prop('disabled',true);
				})
				.on('change','.partialPayment',function()
				{
					partialTypePayment = $('.partialTypePayment option:selected').val();
					if (partialTypePayment == '0') 
					{
						if ($(this).val() > 100) 
						{
							swal("","El porcentaje no puede ser mayor a 100.","error");
							$(this).addClass('error');
							$(this).val('');
						}
					}
				})
				.on('change','.partialTypePayment',function()
				{
					$('.partialPayment').val('');
				})
				.on('click','#update_partial',function(e) 
				{
					e.preventDefault();
					form		= $(this).parents('form');
					partial_id	= $('[name="partial_payment_id"]').val();
					url			= "{{ route('payments.review.edit',['id' => $request->folio]) }}";
					url += "/partial-update/"+partial_id;
					form.attr('action',url);
					form.submit();
				})
				.on('click','#cancel_partial',function()
				{
					$('.form-partial').fadeOut();
					$('[name="form_partial_date"]').val('');
					$('[name="form_partial_amount"]').val('');
					$('[name="form_partial_type"]').val(null).trigger('change');
					$('.partial-edit').prop('disabled',false);
					$('.trPartial').removeClass('partial_select').removeClass('marktr');
				})
			@else
				$(document).on('change','.subtotalRes, .ivaRes, .taxRes, .retentionRes',function()
				{
					subtotalRes		= $('.subtotalRes').val() != "" ? Number($('.subtotalRes').val()) : 0;
					ivaRes			= $('.ivaRes').val() != "" ? Number($('.ivaRes').val()) : 0;
					taxRes			= $('.taxRes').val() != "" ? Number($('.taxRes').val()) : 0;
					retentionRes	= $('.retentionRes').val() != "" ? Number($('.retentionRes').val()) : 0;
					$('.amount,.amountRes').val(Number(subtotalRes+ivaRes+taxRes-retentionRes).toFixed(2));
				})
			@endif
			.on('change','.amount',function()
			{
				var amount = 0;
				if((Number($('.subtotalRes').val())) != 0)
				{
					subtotalRes		= Number($('.subtotalRes').val());
					amount = amount + subtotalRes
				}
				if((Number($('.ivaRes').val())) != 0)
				{
					ivaRes			= Number($('.ivaRes').val());
					amount = amount + ivaRes
				}
				if((Number($('.taxRes').val())) != 0)
				{
					taxRes			= Number($('.taxRes').val());
					amount = amount + taxRes
				}
				if((Number($('.retentionRes').val())) != 0)
				{
					retentionRes	= Number($('.retentionRes').val());
					amount = amount - retentionRes
				}
				if(amount != 0 && $('.amount').val() != amount)
				{
					$('.amount').val(Number(amount).toFixed(2));
				}
			})
			.on('change','input[name="status"]',function()
			{
				if ($('input[name="status"]:checked').val() == "x") 
				{
					$("#rechaza").slideUp("slow");
					$("#aceptar").slideToggle("slow").addClass('form-container').css('display','block');
					@component('components.scripts.selects',["selects" => $selects]) @endcomponent
				}
				else if ($('input[name="status"]:checked').val() == "13") 
				{
					$("#aceptar").slideUp("slow");
					$("#rechaza").slideToggle("slow").addClass('form-container').css('display','block');
				}
			})
			.on('click','.enviar',function (e)
			{
				e.preventDefault();
				form = $('#container-alta');
				$('.amount').removeClass('error');
				amount = $('.amount').val();
				if(amount == '' || amount == 'NaN' || amount == null)
				{
					$('.amount').val('0');
				}
				if($('input[name="status"]').is(':checked'))
				{
					if ($('input[name="status"]:checked').val() == "x")
					{
						docFlag = true;
						if($('.path').length <= 0)
						{
							docFlag = false;
						}

						if(!docFlag) 
						{
							swal({
								title: "¿Desea enviar el pago sin comprobante?",
								icon: "warning",
								buttons: ["Cancelar","OK"],
							})
							.then((isConfirm) =>
							{
								if(isConfirm)
								{
									form.submit();
								}
							});
						}
						else
						{
							form.submit();
						}
					}
					else
					{
						form.submit();
					}
				}
				else
				{
					swal('','Por favor apruebe o rechace la solicitud.','error');
				}
			})
			.on('change','.select-enterprise',function()
			{
				$('.select-accounts').empty();
			})
			.on('click','#addDoc',function()
			{
				@php
					$uploadDoc	=	html_entity_decode((String)view("components.documents.upload-files",
					[
						"classExInput"			=>	"inputDoc pathActioner",
						"attributeExInput"		=>	"type=\"file\" name=\"path\" accept=\".pdf,.jpg,.png\"",
						"attributeExRealPath"	=>	"name=\"realPath[]\"",
						"classExRealPath"		=>	"path",
						"classExDelete"			=>	"delete-doc"
					]));
				@endphp
				uploadDoc = '{!!preg_replace("/(\r)*(\n)*/", "", $uploadDoc)!!}';
				$('#documents').append(uploadDoc);
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
					url			: '{{ route("payments.upload") }}',
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
				$(this).parents('.docs-p').remove();
			})
			.on('click','.exist-doc',function()
			{
				docR		= $(this).parents('p.removeDoc').find('.iddocumentsPayments').val();
				inputDelete	= $('<input type="text" name="deleteDoc[]">').val(docR);
				$('#docs-remove').append(inputDelete);
				$(this).parents('p.removeDoc').remove();
			})
			.on('change','.inputDoc.pathActioner',function(e)
			{
				filename		= $(this);
				uploadedName 	= $(this).parent('.uploader-content').siblings('input[name="realPath[]"]');
				extention		= /\.jpg|\.png|\.jpeg|\.pdf/i;
				
				if (filename.val().search(extention) == -1){
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
						return (css.match (/\bimage_\S+/g) || []).join(' '); // removes anything that starts with "image_"
					});
					formData	= new FormData();
					formData.append(filename.attr('name'), filename.prop("files")[0]);
					formData.append(uploadedName.attr('name'),uploadedName.val());
					$.ajax(
					{
						type		: 'post',
						url			: '{{ route("payments.upload") }}',
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
			.on('click','[data-toggle="modal"]',function()
			{
				idpayment = $(this).attr('data-payment');
				$.ajax({
					type		: 'post',
					url			: '{{ route("payments.view-detail") }}',
					data		: {'idpayment':idpayment},
					success: function(data)
					{
						$('.modal-body').html(data);
					},
					error: function(data)
					{
						swal('','Sucedió un error, por favor intente de nuevo.','error');
						$('.modal-body').hide();
					}
				});
			})
			.on("focusout",".subtotalRes,.ivaRes,.taxRes,.retentionRes,.amount",function()
			{
				valueThis = $.isNumeric($(this).val());
				if(valueThis == false)
				{
					$(this).val(null);
				}
			});
			
			//Desaparece los campos ya pagados por completo o que no son pagables en el Pago 
			if($('input[name="amount"]').attr('value') == 0)	$('input[name="amount"]').parent('div').hide();
			if($('input[name="subtotal"]').attr('value') == 0)	$('input[name="subtotal"]').parent('div').hide();
			if($('input[name="iva"]').attr('value') == 0)		$('input[name="iva"]').parent('div').hide();
			if($('input[name="tax"]').attr('value') == 0)		$('input[name="tax"]').parent('div').hide();
			if($('input[name="retention"]').attr('value') == 0)	$('input[name="retention"]').parent('div').hide();

			@if($request->purchases()->exists() && $request->purchases->first()->partialPayment()->exists() && $request->purchases->first()->partialPayment->where('date_delivery','=',null)->count()>0 )

				@php
					$total 			 = $request->purchases->first()->amount;
				@endphp

				function disableEdition()
				{
					$('#addNewPartialPayment').removeClass('partial_edit_button');
					$('#partialBody').removeClass('partial_select');
					$('#partialBody').find('.partial-edit').addClass('follow-btn');
					$('.trPartial').removeClass('marktr');
					$('.partialPayment, .partialTypePayment, .partialDate').removeClass('error valid');
					$('#typePaymentSpan').removeClass('help-block form-error').hide();
					$('.partialPayment, .partialDate').val('');
					$('.partialTypePayment').val('').trigger('change');
					$('.documents_partial').empty();
				}

				function totalPartialPayments()
				{
					totalPurchase	= Number({{ $total }});
					totalPartials	= 0;

					@if(isset($request) && $request->paymentsRequest()->exists())
						@php
							$totalPaid = 0;
							foreach ($request->paymentsRequest as $key => $payment) 
							{
								if (!$payment->partialPayments()->exists() && $payment->partial_id == "")
								{
									$totalPaid += $payment->amount;
								}
							}
						@endphp
						totalPaid = {{ $totalPaid }};
					@else
						totalPaid = 0;
					@endif

					if ($('#partialBody .tr').length > 0) 
					{
						$('#partialBody .tr').each(function(i,v)
						{
							if (!$(this).hasClass('partial_select')) 
							{
								amountPartial	= Number($(this).find('.partial_payment').val());
								type			= $(this).find('.partial_type').val();

								if (type == '1') 
								{
									totalPartials += amountPartial;
								}
								else if(type == '0')
								{
									totalPartials += Number((totalPurchase/100)*amountPartial);
								}
							}
						});
					}

					partials_total = Number(totalPartials).toFixed(2);
					$('[name="partials_total"]').val(Number(totalPartials).toFixed(2));

					remainingPayment = Number(totalPurchase) - Number(partials_total) - Number(totalPaid);
					if (remainingPayment < 0) 
					{
						$('.remainingPayment').text('Verificar datos');
					}
					else
					{
						$('.remainingPayment').text('$'+Number(remainingPayment).toFixed(2));
					}
				}

				totalPartialPayments();

				$.validate(
				{
					form	: '#form_partial',
					onError   : function()
					{
						swal('', '{{ Lang::get("messages.form_error") }}', 'error');
					},
					onSuccess : function()
					{
						amount = $('[name="form_partial_amount"]').val();
						if (Number(amount) <= 0) 
						{
							$('[name="form_partial_amount"]').removeClass('valid').addClass('error');
							swal('', 'El monto no puede ser menos o igual a 0.', 'error');
							return false;
						}
						else
						{
							return true;
						}
					}
				});
			@endif
		});
	</script>
@append
