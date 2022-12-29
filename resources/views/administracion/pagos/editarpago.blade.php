@extends('layouts.child_module')

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
	@case(17)
		@include('administracion.pagos.registro_compra')
		@break
@endswitch

@section('pay-form')
	@if(isset($request->purchases->first()->idPurchase))
		<div id="contentPartials" class="hidden">
			@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "PROGRAMA DE PAGOS"]) @endcomponent
			@php
				if(isset($request))
				{
					$idPurchase			=	$request->purchases->first()->idPurchase;
					$partialPayments	=	App\PartialPayment::where('purchase_id', $idPurchase)->get();
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
						["value"	=>	"Documento(s)"]
					]
				];
				if (isset($partialPayments))
				{
					$countPartial = 1;
					foreach($partialPayments as $p)
					{
						$documentsInformation	=	[];
						$state	=	$p->date_delivery != null ? 1 : 0;
						$disabled	=	$state == 0 ? "disabled" :"";
						$statusPayment	=	$state == 1 ? "Pagado" : "Sin pagar";
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
						if (count($partialPayments) > 0)
						{
							$docs_counter = $countPartial;
							if (count($p->documentsPartials)>0)
							{
								foreach ($p->documentsPartials as $doc)
								{
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
										["label"			=>	Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$doc->datepath)->format('d-m-Y')],
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
											"attributeEx"	=>	"type=\"hidden\"	name=\"folio_p".$docs_counter."[]\"	value=\"".$doc->fiscal_folio."\"",
											"classEx"		=>	"folio_p"
										],
										[
											"kink"			=>	"components.inputs.input-text",
											"attributeEx"	=>	"type=\"hidden\"	name=\"ticket_p".$docs_counter."[]\"	value=\"".$doc->ticket_number."\"",
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
								$documentsInformation	=	["label"	=>	"---"];
							}
						}
						else
						{
							$documentsInformation	=	["label"	=>	"---"];
						}
						$body	=
						[
							"classEx"	=>	"trPartial",
							[
								"content"	=>
								[
									"kind"			=>	"components.inputs.checkbox",
									"attributeEx"	=>	"id=\"idPartial_".$p['id']."\" type=\"checkbox\" name=\"checkPartial[]\" value=\"".$p['id']."\" $disabled",
									"classEx"		=>	"checkbox",
									"classExLabel"	=>	"check-small request-validate",
									"label"			=>	'<span class="icon-check"></span>'
								]
							],
							[
								"content"	=>
								[
									[
										"kind"			=> "components.labels.label",
										"label"			=> $countPartial
									],
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
										"attributeEx"	=>	"readonly type=\"hidden\" value=\"".$countPartial."\"",
										"classEx"		=>	"partial numPartial input-table"
									],
								],
							],
							[
								"content"	=>
								[
									[
										"kind"			=> "components.labels.label",
										"label"			=> $presign.number_format($p['payment'],2).$percentage
									],
									[
										"kind"			=>	"components.inputs.input-text",
										"attributeEx"	=>	"readonly type=\"hidden\" value=\"".$presign.number_format($p['payment'],2).$percentage."\"",
										"classEx"		=>	"partial_paymentText input-table"
									],
									[
										"kind"			=>	"components.inputs.input-text",
										"attributeEx"	=>	"type=\"hidden\" name=\"partialPayment[]\" value=\"".$p['payment']."\"",
										"classEx"		=>	"partialPayment"
									],
									[
										"kind"			=>	"components.inputs.input-text",
										"attributeEx"	=>	"type=\"hidden\" name=\"partialType[]\" value=\"".$p['tipe']."\"",
										"classEx"		=>	"partialType"
									],
								],
							],
							[
								"content"	=>
								[
									[
										"kind"			=> "components.labels.label",
										"label"			=> Carbon\Carbon::createFromFormat('Y-m-d',$p->date_requested)->format('d-m-Y')
									],
									[	
										"kind"			=>	"components.inputs.input-text",
										"attributeEx"	=>	"readonly type=\"hidden\" name=\"partial_date[]\" value=\"".Carbon\Carbon::createFromFormat('Y-m-d',$p->date_requested)->format('d-m-Y')."\"",
										"classEx"		=>	"partial_date input-table"
									]
								]
							],
							[
								"content"	=>
								[
									[
										"kind"			=> "components.labels.label",
										"label"			=> $statusPayment
									],
									[	
										"kind"			=>	"components.inputs.input-text",
										"attributeEx"	=>	"readonly type=\"hidden\" value=\"".$statusPayment."\"",
										"classEx"		=>	"partial_stateText partial input-table"
									],
									[
										"kind"			=>	"components.inputs.input-text",
										"attributeEx"	=>	"type=\"hidden\" value=\"".$state."\"",
										"classEx"		=>	"partial_state partial"
									]
								],
							],
							[
								"classEx"	=>	"contentDocs",
								"content"	=>	$documentsInformation
							],
						];
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
		</div>
	@endif
	@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "DATOS DEL PAGO"]) @endcomponent
	@component('components.forms.form', ["attributeEx" => "method=\"POST\" id=\"container-alta\" action=\"".route('payments.updatepayment',$payment->idpayment)."\"", "methodEx" => "PUT", "files" => true])
		@php
			$modelHead	=	[];
			$modelHead	=	["DATOS INGRESADOS"];
			$modelBody	=	[];
		@endphp
		@component('components.tables.alwaysVisibleTable', ["modelBody" => $modelBody, "modelHead" => $modelHead])@endcomponent
		@component('components.containers.container-form')
			<div class="col-span-2">
				@component('components.labels.label') Empresa: @endcomponent
				@php
					$options	=	collect();
					foreach (App\Enterprise::orderBy('name','asc')->get() as $enterprise)
					{
						if ($payment->idEnterprise == $enterprise->id)
						{
							$options	=	$options->concat([["description"	=>	$enterprise->name,	"value"	=>	$enterprise->id,	"selected"	=>	"selected"]]);
						}
						else
						{
							$options	=	$options->concat([["description"	=>	$enterprise->name,	"value"	=>	$enterprise->id]]);
						}
					}
				@endphp
				@component('components.inputs.select', ["options" => $options])
					@slot('classEx')
						select-enterprise
					@endslot
					@slot('attributeEx')
						multiple="multiple" name="enterprise_id" data-validation="required"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Clasificación del gasto: @endcomponent
				@php
					$options		=	collect();
					$accountData	=	App\Account::find($payment->account);
					if (isset($payment->account) && $payment->account!="")
					{
						$options	=	$options->concat([["value"	=>	$accountData->idAccAcc,	"description"	=>	$accountData->account." - ".$accountData->description." (".$accountData->content.")",	"selected"	=>	"selected"]]);
					}
				@endphp
				@component('components.inputs.select', ["options" => $options])
					@slot('classEx')
						select-accounts
					@endslot
					@slot('attributeEx')
						multiple="multiple" name="account" data-validation="required"
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
						placeholder="Ingrese el subtotal" type="text" name="subtotalRes" data-validation="required" value="{{ $payment->subtotal_real }}" @if($payment->partialPayments()->exists()) readonly @endif
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Impuesto Adicional: @endcomponent
				@component('components.inputs.input-text')
					@slot('classEx')
						taxRes
					@endslot
					@slot('attributeEx')
						placeholder="Ingrese el impuesto" type="text" name="tax" value="{{ $payment->tax_real }}" @if($payment->partialPayments()->exists()) readonly @endif
					@endslot
				@endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						placeholder="$0.00" type="hidden" name="taxRes"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Importe: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						placeholder="Ingrese el importe" type="text" name="amount" data-validation="required" value="{{ $payment->amount_real }}"
					@endslot
				@endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						placeholder="$0.00" type="hidden" name="amountRes" data-validation="required"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Tasa de cambio: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" name="exchange_rate" placeholder="Ingrese la tasa de cambio" value="{{ $payment->exchange_rate }}"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') IVA: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						placeholder="Ingrese el iva" type="text" name="ivaRes" value="{{ $payment->iva_real }}" data-validation="required" @if($payment->partialPayments()->exists()) readonly @endif
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Retención: @endcomponent
				@component('components.inputs.input-text')
					@slot('classEx')
						retentionRes
					@endslot
					@slot('attributeEx')
						placeholder="Ingrese la retención" type="text" name="retention" value="{{$payment->retention_real}}" @if($payment->partialPayments()->exists()) readonly @endif
					@endslot
				@endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						placeholder="$0.00" type="hidden" name="retentionRes"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Fecha de pago: @endcomponent
				@component('components.inputs.input-text')
					@slot('classEx')
						datepicker
					@endslot
					@slot('attributeEx')
						placeholder="Ingrese la fecha" type="text" name="paymentDate" readonly="readonly" data-validation="required" value="@if(isset($payment->paymentDate)) {{ Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$payment->paymentDate)->format('d-m-Y') }} @endif"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Descripción de tasa de cambio: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" name="exchange_rate_description" placeholder="Ingrese la descripción de la tasa de cambio" value="{{ $payment->exchange_rate_description }}" @if($payment->partialPayments()->exists()) readonly @endif
					@endslot
				@endcomponent
			</div>
			<div class="md:col-span-4 col-span-2">
				@component('components.labels.label') Comentarios: @endcomponent
				@component('components.inputs.text-area')
					@slot('attributeEx')
						name="commentaries" placeholder="Ingrese el comentario"
					@endslot
					{{ $payment->commentaries }}
				@endcomponent
			</div>
		@endcomponent
		@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "COMPROBANTES"]) @endcomponent
		@php
			$modelHead	=	[];
			$body		=	[];
			$modelBody	=	[];
			if (App\DocumentsPayments::where('idpayment',$payment->idpayment)->count()>0)
			{
				$modelHead	=	["Documento", "Acciones"];
				foreach (App\DocumentsPayments::where('idpayment',$payment->idpayment)->get() as $doc)
				{
					$body	=
					[
						"classEx"	=>	"removeDoc",
						[
							"content"	=>
							[
								[
									"kind"			=>	"components.buttons.button",
									"variant"		=>	"secondary",
									"buttonElement"	=>	"a",
									"label"			=>	"Archivo",
									"attributeEx"	=>	"title=\"Ver documento\" target=\"_blank\" href=\"".asset('/docs/payments/'.$doc->path)."\""
								],
								[
									"kind"			=>	"components.inputs.input-text",
									"attributeEx"	=>	"type=\"hidden\" value=\"".$doc->iddocumentsPayments."\"",
									"classEx"		=>	"iddocumentsPayments"
								]
							]
						],
						[
							"content"	=>
							[
								[
									"kind"			=>	"components.buttons.button",
									"variant"		=>	"red",
									"label"			=>	"<span class='icon-x delete-span'></span>",
									"classEx"		=>	"delete-item exist-doc",
									"attributeEx"	=>	"type=\"button\" id=\"delete-doc\""
								]
							]
						],
					];
					$modelBody[] = $body;
				}
			}
			else
			{
				$modelHead	=	["Documento"];
				$body		=
				[
					[
						"content"	=>
						[
							"label"	=>	"Sin Documentos"
						]
					]
				];
				$modelBody[] = $body;
			}
		@endphp
		@component('components.tables.alwaysVisibleTable', ["modelHead" => $modelHead, "modelBody" => $modelBody])@endcomponent
		<div id="docs-remove"></div>
		@component('components.containers.container-form')
			<div class="col-span-2 md:col-span-4 grid grid-cols-1 md:grid-cols-2 gap-6 hidden" id="documents"></div>
			<div class="col-span-2 md:col-span-4 grid justify-items-center md:justify-items-start">
				@component('components.buttons.button', ["variant" => "warning"])
					@slot('attributeEx')
						type="button" name="addDoc" id="addDoc"
					@endslot
					<span class="icon-plus"></span>
					<span>Agregar documento</span>
				@endcomponent
			</div>
		@endcomponent
		<div class="w-full mt-4 grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mb-6">
			@component('components.buttons.button', ["variant"	=>	"primary"])
				@slot('classEx')
					enviar
				@endslot
				@slot('attributeEx')
					type="submit" name="enviar" value="ACTUALIZAR PAGO"
				@endslot
				ACTUALIZAR PAGO
			@endcomponent
			@component('components.buttons.button', ["variant" => "reset"])
				@slot('attributeEx')
					@if(isset($option_id)) 
						href="{{ url(getUrlRedirect($option_id)) }}" 
					@else 
						href="{{ url(getUrlRedirect($child_id)) }}" 
					@endif
				@endslot
				@slot('buttonElement')
					a
				@endslot
				@slot('classEx')
					load-actioner
				@endslot
				REGRESAR
			@endcomponent
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
				@component('components.buttons.button', ["variant" => "red"])
					@slot('attributeEx')
						data-dismiss="modal"
						type="button"
					@endslot
					<span class="icon-x"></span> Cerrar
				@endcomponent
			@endslot
		@endcomponent
	@endcomponent
@endsection

@section('scripts')
	<script>
		$('input[name="amountRes"]').attr('value',$('#restaTotal').val());
		$('input[name="exchange_rate"]').numeric({ altDecimal: ".", decimalPlaces: 4, negative:false});
		$(document).ready(function()
		{
			generalSelect({'selector': '.select-accounts', 'depends': '.select-enterprise', 'model': 10});

			@php
				$selects = collect([
					[
						"identificator"				=> ".select-enterprise",
						"placeholder"				=> "Seleccione la empresa",
						"language"					=> "es",
						"maximumSelectionLength"	=> "1"
					]
				]);
			@endphp
			@component('components.scripts.selects',["selects" => $selects]) @endcomponent
			$('input[name="subtotalRes"],input[name="ivaRes"],.taxRes,.retentionRes').numeric({ negative : false, altDecimal: ".", decimalPlaces: 2 });
			$('input[name="amount"]').numeric({ altDecimal: ".", decimalPlaces: 2 });
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
					
					amount     = $('input[name="amount"]').val();
					exchange_rate =  $('[name="exchange_rate"]').val() != "" ? Number($('[name="exchange_rate"]').val()).toFixed() : 1;

					if(!pathFlag) 
					{
						swal('','Por favor agregue los documentos faltantes.','error');
						return false;
					}
					else if(exchange_rate != "" && exchange_rate == '0' || exchange_rate == '0.00' || exchange_rate == '0.0')
					{
						$('[name="exchange_rate"]').removeClass('valid').addClass('error')
						swal('','La tasa de cambio no puede ser 0, por favor verifique los datos.','error');
						return false;
					}
					else if( amount <= 0 || amount == '' || amount == 'NaN' || amount == null)
					{
						$('input[name="amount"]').removeClass('valid').addClass('error');	
						swal('','El importe no puede ser cero ó negativo, por favor verifique los datos.','error');
						return false;
					}
					else
					{
						swal({
							icon               : '{{ asset(getenv('LOADING_IMG')) }}',
							button             : false,
							closeOnClickOutside: false,
							closeOnEsc         : false
						});
						return true;
					}
				}
			});
			$('[name="amount"],[name="exchange_rate"]').on("contextmenu",function(e)
			{
				return false;
			});
			code = $('#codeIsTrue').val();
			if (code != undefined) 
			{
				$('#viewCode').show();
			}
			
			if($('.trPartial').length > 0)
			{
				$('#contentPartials').removeClass('hidden');
			}
		});
		$(document).on('click','.enviar',function (e)
		{
			e.preventDefault();
			subtotal = $('input[name="subtotalRes"],input[name="amount"]').removeClass('error');
			form = $('#container-alta');
			amount   = $('input[name="amount"]').val();
			if(amount == '' || amount == 'NaN' || amount == null)
			{
				$('input[name="amount"]').val('0');
			}

			docFlag = true;
			if($('.path').length <= 0 && $('.iddocumentsPayments').length <= 0)
			{
				docFlag = false;
			}

			if(!docFlag) 
			{
				swal({
					title: "¿Desea actualizar el pago sin comprobante?",
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
		})
		.on('change','[name="enterprise_id"]',function()
		{
			$('[name="account"]').empty();
		})
		.on('click','#addDoc',function()
		{
			hasHidden	=	$('#documents').hasClass('hidden');
			if (hasHidden)
			{
				$('#documents').removeClass('hidden');
			}
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
			uploadDoc 	=	'{!!preg_replace("/(\r)*(\n)*/", "", $uploadDoc)!!}';
			$('#documents').append(uploadDoc);
		})
		.on('click','.delete-doc',function()
		{
			swal(
			{
				icon	:	'{{ asset(getenv('LOADING_IMG')) }}',
				button	:	false
			});
			actioner		=	$(this);
			uploadedName	=	$(this).parents('.docs-p').find('input[name="realPath[]"]');
			formData		=	new FormData();
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
			if($('.docs-p').length<1)
			{
				$('#documents').addClass('hidden');
			}
		})
		.on('click','.exist-doc',function()
		{
			docR = $(this).parents('.removeDoc').find('.iddocumentsPayments').val();
			inputDelete = $('<input type="hidden" name="deleteDoc[]">').val(docR);
			$('#docs-remove').append(inputDelete);
			$(this).parents('.removeDoc').remove();
		})
		.on('change','.inputDoc.pathActioner',function(e)
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
					swal('Ups!','Ocurrió un error, intente de nuevo','error');
				}
			});
		})
		.on('change','input[name="subtotalRes"], input[name="ivaRes"], .taxRes, .retentionRes',function()
		{
			subtotalRes		= $('input[name="subtotalRes"]').val() != "" ? parseFloat($('input[name="subtotalRes"]').val()) : 0;
			ivaRes			= $('input[name="ivaRes"]').val() != "" ? parseFloat($('input[name="ivaRes"]').val()) : 0;
			taxRes			= $('.taxRes').val() != "" ? parseFloat($('.taxRes').val()) : 0;
			retentionRes	= $('.retentionRes').val() != "" ? parseFloat($('.retentionRes').val()) : 0;
			$('input[name="amount"]').val((subtotalRes+ivaRes+taxRes-retentionRes).toFixed(2));

		})
		.on("focusout","input[name='subtotalRes'], input[name='ivaRes'], .taxRes, .retentionRes, input[name='amount']",function()
		{
			valueThis = $.isNumeric($(this).val());
			if(valueThis == false)
			{
				$(this).val(null);
			}
		});
	</script>
@append
