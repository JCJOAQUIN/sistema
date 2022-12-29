@extends('layouts.child_module')

@section('data')
	@if (isset($globalRequests) && $globalRequests == true)
		@component("components.labels.not-found", ["variant" => "note"])
			@slot("slot")
				@component("components.labels.label")
					@slot("classEx")
						font-bold
						inline-block
						text-blue-900
					@endslot
						TIPO DE SOLICITUD: 
				@endcomponent
				{{ mb_strtoupper($request->requestkind->kind) }}
			@endslot
		@endcomponent
	@endif
	@php
		$taxesCount = $taxesCountBilling = 0;
		$taxes = $retentions = $taxesBilling = $retentionsBilling = 0;
	@endphp
	<div id="form-loan">
		@component('components.forms.form', ["attributeEx"	=>	"method=\"POST\" action=\"".route('movements-accounts.loan.follow.update', $request->folio)."\" id=\"container-alta\"",	"methodEx"	=>	"PUT",	"files"	=>	true])
			@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "FORMULARIO DE PRÉSTAMOS"]) @endcomponent
			@component('components.containers.container-form')
				<div class="col-span-2">
					@component('components.labels.label', ["label" => "Título:"]) @endcomponent
					@component('components.inputs.input-text', ["classEx" => "removeselect"])
						@slot('attributeEx')
							@if($request->status!=2) disabled="disabled" @endif type="text" name="title" placeholder="Ingrese un título" data-validation="required"
							@if($request->loanEnterprise()->exists()) value="{{ $request->loanEnterprise->first()->title }}" @endif
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label', ["label" => "Fecha:"]) @endcomponent
					@component('components.inputs.input-text', ["classEx" => "removeselect datepicker2"])
						@slot('attributeEx')
							@if($request->status!=2) disabled="disabled" @endif type="text" name="datetitle" @if($request->loanEnterprise()->exists() && $request->loanEnterprise->first()->datetitle != "") value="{{ Carbon\Carbon::createFromFormat('Y-m-d',$request->loanEnterprise->first()->datetitle)->format('d-m-Y') }}" @endif placeholder="Ingrese la fecha" data-validation="required" readonly="readonly"
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label', ["label" => "Fiscal:"]) @endcomponent
					<div class="flex p-0 space-x-2">
						@component('components.buttons.button-approval')
							@slot('attributeEx') id="nofiscal" name="fiscal" value="0" @if($request->status!=2) disabled="disabled" @endif @if(isset($request) && $request->taxPayment==0) checked @endif @endslot
							@slot('classExContainer')
								inline-flex
							@endslot
							No
						@endcomponent
						@component('components.buttons.button-approval')
							@slot('attributeEx') id="fiscal" name="fiscal" value="1" @if($request->status!=2) disabled="disabled" @endif @if(isset($request) && $request->taxPayment==1) checked @endif @endslot
							@slot('classExContainer')
								inline-flex
							@endslot
							Sí
						@endcomponent
					</div>
				</div>
				<div class="col-span-2">
					@component('components.labels.label', ["label" => "Solicitante:"]) @endcomponent
					@php
						$options	=	collect();
						if ($request->loanEnterprise()->exists() && $request->idRequest !="")
						{
							$options	=	$options->concat([["value"	=>	$request->requestUser->id,	"description"	=>	$request->requestUser->fullname(),	"selected"	=>	"selected"]]);
						}
					@endphp
					@component('components.inputs.select', ["options" => $options,"classEx" => "js-users removeselect"])
						@slot('attributeEx')
							@if($request->status!=2) disabled="disabled" @endif name="userid" multiple="multiple"  data-validation="required"
						@endslot
					@endcomponent
				</div>
			@endcomponent
			@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "CUENTA DE ORIGEN"]) @endcomponent
			@component('components.containers.container-form')
				<div class="col-span-2">
					@component('components.labels.label', ["label" => "Empresa:"]) @endcomponent
					@php
						$options	=	collect();
						foreach (App\Enterprise::orderName()->where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeEnt($option_id)->pluck('enterprise_id'))->get() as $enterprise)
						{
							if ($request->loanEnterprise()->exists() && $request->loanEnterprise->first()->idEnterpriseOrigin == $enterprise->id)
							{
								$options	=	$options->concat([["value"	=>	$enterprise->id,	"description"	=>	strlen($enterprise->name) >= 35 ? substr(strip_tags($enterprise->name),0,35).'...' : $enterprise->name,	"selected"	=>	"selected"]]);
							}
							else
							{
								$options	=	$options->concat([["value"	=>	$enterprise->id,	"description"	=>	strlen($enterprise->name) >= 35 ? substr(strip_tags($enterprise->name),0,35).'...' : $enterprise->name]]);
							}
						}
					@endphp
					@component('components.inputs.select', ["options" => $options,"classEx" => "js-enterprises-origin removeselect"])
						@slot('attributeEx')
							@if($request->status!=2) disabled="disabled" @endif name="enterpriseid_origin" multiple="multiple"  data-validation="required"
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label', ["label" => "Clasificación del gasto:"]) @endcomponent
					@php
						$options	=	collect();
						if ($request->loanEnterprise()->exists() && $request->loanEnterprise->first()->idAccAccOrigin !="")
						{
							$options	=	$options->concat([["value"	=>	$request->loanEnterprise->first()->accountOrigin->idAccAcc,	"description"	=>	$request->loanEnterprise->first()->accountOrigin->account." - ".$request->loanEnterprise->first()->accountOrigin->description." (".$request->loanEnterprise->first()->accountOrigin->content.")",	"selected"	=>	"selected"]]);
						}
					@endphp
					@component('components.inputs.select', ["options" => $options,"classEx" => "js-accounts-origin removeselect"])
						@slot('attributeEx')
							@if($request->status!=2) disabled="disabled" @endif multiple="multiple" name="accountid_origin"  data-validation="required"
						@endslot
					@endcomponent
				</div>
			@endcomponent
			@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "CUENTA DESTINO"]) @endcomponent
			@component('components.containers.container-form')
				<div class="col-span-2">
					@component('components.labels.label', ["label" => "Empresa:"]) @endcomponent
					@php
						$options	=	collect();
						foreach (App\Enterprise::orderName()->where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeEnt($option_id)->pluck('enterprise_id'))->get() as $enterprise)
						{
							if ($request->loanEnterprise()->exists() && $request->loanEnterprise->first()->idEnterpriseDestiny == $enterprise->id)
							{
								$options	=	$options->concat([["value"	=>	$enterprise->id,	"description"	=>	strlen($enterprise->name) >= 35 ? substr(strip_tags($enterprise->name),0,35).'...' : $enterprise->name,	"selected"	=>	"selected"]]);
							}
							else
							{
								$options	=	$options->concat([["value"	=>	$enterprise->id,	"description"	=>	strlen($enterprise->name) >= 35 ? substr(strip_tags($enterprise->name),0,35).'...' : $enterprise->name]]);
							}
						}
					@endphp
					@component('components.inputs.select', ["options" => $options,"classEx" => "js-enterprises-destination removeselect"])
						@slot('attributeEx')
							@if($request->status!=2) disabled="disabled" @endif name="enterpriseid_destination" multiple="multiple"  data-validation="required"
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label', ["label" => "Clasificación del gasto:"]) @endcomponent
					@php
						$options	=	collect();
						if ($request->loanEnterprise()->exists() && $request->loanEnterprise->first()->idEnterpriseDestiny !="")
						{
							$options	=	$options->concat([["value"	=>	$request->loanEnterprise->first()->accountDestiny->idAccAcc,	"description"	=>	$request->loanEnterprise->first()->accountDestiny->account." - ".$request->loanEnterprise->first()->accountDestiny->description." (".$request->loanEnterprise->first()->accountDestiny->content.")",	"selected"	=>	"selected"]]);
						}
					@endphp
					@component('components.inputs.select', ["options" => $options,"classEx" => "js-accounts-destination removeselect"])
						@slot('attributeEx')
							@if($request->status!=2) disabled="disabled" @endif multiple="multiple" name="accountid_destination"  data-validation="required"
						@endslot
					@endcomponent
				</div>
			@endcomponent
			@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "DATOS DEL PRÉSTAMO"]) @endcomponent
			@component('components.containers.container-form')
				<div class="col-span-2 md:col-start-2 md:col-end-4">
					@component('components.labels.label', ["label" => "Importe:"]) @endcomponent
					@component('components.inputs.input-text', ["classEx" => "amount"])
						@slot('attributeEx')
							@if($request->status!=2) disabled="disabled" @endif type="text" name="amount" placeholder="Ingrese el importe"  @if($request->loanEnterprise()->exists()) value="{{ $request->loanEnterprise->first()->amount }}" @endif
						@endslot
					@endcomponent
				</div>
			@endcomponent
			@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "CONDICIONES DE PAGO"]) @endcomponent
			@component('components.containers.container-form')
				<div class="col-span-2">
					@component('components.labels.label', ["label" => "Tipo de moneda"]) @endcomponent
					@php
						$options		=	collect();
						$currencyData	=	["MXN","USD","EUR","Otro"];
						foreach ($currencyData as $currency)
						{
							if ($request->loanEnterprise()->exists() && $request->loanEnterprise->first()->currency == $currency)
							{
								$options	=	$options->concat([["value"	=>	$currency, "description"	=>	$currency,	"selected"	=>	"selected"]]);
							}
							else
							{
								$options	=	$options->concat([["value"	=>	$currency, "description"	=>	$currency]]);
							}
						}
					@endphp
					@component('components.inputs.select', ["options" => $options,"classEx" => "remove"])
						@slot('attributeEx')
							name="type_currency" multiple="multiple" data-validation="required" @if($request->status!=2) disabled="disabled" @endif
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label', ["label" => "Fecha de Pago"]) @endcomponent
					@component('components.inputs.input-text', ["classEx" => "remove"])
						@slot('attributeEx')
							type="text" name="date" step="1" placeholder="Ingrese la fecha" data-validation="required" readonly="readonly" id="datepicker" @if($request->loanEnterprise()->exists()) value="{{ $request->loanEnterprise->first()->paymentDate != null ? Carbon\Carbon::createFromFormat('Y-m-d',$request->loanEnterprise->first()->paymentDate)->format('d-m-Y') : null }}" @endif @if($request->status!=2) disabled="disabled" @endif
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label', ["label" => "Forma de pago"]) @endcomponent
					@php
						$options		=	collect();
						$paymentType	=	["1"=>"Cuenta Bancaria","2"=>"Efectivo","3"=>"Cheque"];
						foreach ($paymentType as $k => $payment)
						{
							if ($request->loanEnterprise->first()->idpaymentMethod == $k)
							{
								$options	=	$options->concat([["value"	=>	$k,	"description"	=>	$payment,	"selected"	=>	"selected"]]);
							}
							else
							{
								$options	=	$options->concat([["value"	=>	$k,	"description"	=>	$payment]]);
							}
						}
					@endphp
					@component('components.inputs.select', ["options" => $options,"classEx" => "js-form-pay removeselect"])
						@slot('attributeEx')
							@if($request->status!=2) disabled="disabled" @endif multiple="multiple" name="pay_mode" data-validation="required"
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label', ["label" => "Importe a pagar"]) @endcomponent
					@component('components.inputs.input-text', ["classEx" => "amount_total remove"])
						@slot('attributeEx')
							@if($request->status!=2) disabled="disabled" @endif type="text" name="amount_total" readonly placeholder="Ingrese el importe" data-validation="required" @if($request->loanEnterprise()->exists()) value="${{ number_format($request->loanEnterprise->first()->amount,2) }}" @endif
						@endslot
					@endcomponent
				</div>
			@endcomponent
			@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "DOCUMENTOS"]) @endcomponent
			@php
				$modelHead	=	[];
				$body		=	[];
				$modelBody	=	[];
				if ($request->loanEnterprise->first()->documentsLoan)
				{
					$modelHead	=	["Documento", "Fecha"];
					foreach($request->loanEnterprise->first()->documentsLoan as $doc)
					{
						$body	=
						[
							[
								"content"	=>
								[
									[
										"kind"			=>	"components.buttons.button",
										"variant"		=>	"secondary",
										"label"			=>	"Archivo",
										"buttonElement"	=>	"a",
										"attributeEx"	=>	"type\"button\" target=\"_blank\" href=\"".url('docs/movements/'.$doc->path)."\"",
									]
								]
							],
							[
								"content"	=>	["label"	=>	Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$doc->date)->format('d-m-Y H:i:s')],
							],
						];
						$modelBody[]	=	$body;
					}
				}
				else
				{
					$modelHead	=	["Documento"];
					$body	=
					[
						[
							"content"	=>	["label"	=>	"NO HAY DOCUMENTOS"],
						],
					];
					$modelBody[]	=	$body;
				}
			@endphp
			@component('components.tables.alwaysVisibleTable', ["modelHead" => $modelHead, "modelBody" => $modelBody])@endcomponent
			@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "CARGAR DOCUMENTOS"]) @endcomponent
			@component('components.containers.container-form')
				<div class="col-span-2 md:col-span-4 grid grid-cols-1 md:grid-cols-2 gap-6 hidden" id="documents"></div>
				<div class="col-span-2 md:col-span-4 grid justify-items-center md:justify-items-start">
					<div class="md:block grid">
						@component('components.buttons.button', ["variant" => "warning"])
							@slot('attributeEx')
								type="button" name="addDoc" id="addDoc" @if($request->status==1) disabled="disabled" @endif
							@endslot
							@slot('label')
								<span class="icon-plus"></span>
								<span>Agregar documento</div>
							@endslot
						@endcomponent
						@if ($request->status != 2)
							@component('components.buttons.button', ["variant" => "success"])
								@slot('attributeEx')
									type="submit" name="send" value="CARGAR" formaction="{{ route('movements-accounts.update.documents', $request->folio) }}" @if($request->status==1) disabled="disabled" @endif
								@endslot
								@slot('label')
									CARGAR
								@endslot
							@endcomponent
						@endif
					</div>
				</div>
			@endcomponent
			@if($request->idCheck != "")
				@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "DATOS DE REVISIÓN"]) @endcomponent
				@php
					$enterpriseOriginDetail		=	"";
					$accountsOriginDetail		=	"";
					$enterpriseDestinyDetail	=	"";
					$accountsDestinyDetail		=	"";
					if ($request->loanEnterprise->first()->idEnterpriseOriginR != '')
					{
						$enterpriseOriginDetail		=	App\Enterprise::find($request->loanEnterprise->first()->idEnterpriseOriginR)->name;
						$requestAccount				=	App\Account::find($request->loanEnterprise->first()->idAccAccOriginR);
						$accountsOriginDetail		=	$requestAccount->account." - ".$requestAccount->description;
					}
					if ($request->loanEnterprise->first()->idEnterpriseDestinyR != '')
					{
						$enterpriseDestinyDetail	=	App\Enterprise::find($request->loanEnterprise->first()->idEnterpriseDestinyR)->name;
						$requestAccount				=	App\Account::find($request->loanEnterprise->first()->idAccAccDestinyR);
						$accountsDestinyDetail		=	$requestAccount->account." - ".$requestAccount->description;
					}
					$modelTable	=
					[
						"Revisó"								=>	$request->reviewedUser->name." ".$request->reviewedUser->last_name." ".$request->reviewedUser->scnd_last_name,
						"Nombre de la Empresa de Origen"		=>	$enterpriseOriginDetail,
						"Clasificación del Gasto de Origen"		=>	$accountsOriginDetail,
						"Nombre de la Empresa de Destino"		=>	$enterpriseDestinyDetail,
						"Clasificación del Gasto de Destino"	=>	$accountsDestinyDetail,
						"Comentarios"							=>	$request->checkComment == "" ? "Sin comentarios" : htmlentities($request->checkComment),
					];
				@endphp
				@component('components.templates.outputs.table-detail-single', ["modelTable" => $modelTable])@endcomponent
			@endif
			@if($request->idAuthorize != "")
				@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "DATOS DE AUTORIZACIÓN"]) @endcomponent
				@php
					$modelTable	=
					[
						"Autorizó"		=>	$request->authorizedUser->name." ".$request->authorizedUser->last_name." ".$request->authorizedUser->scnd_last_name,
						"Comentarios"	=>	$request->authorizeComment == "" ? "Sin comentarios" : htmlentities($request->authorizeComment)
					];
				@endphp
				@component('components.templates.outputs.table-detail-single', ["modelTable" => $modelTable])@endcomponent
			@endif
			@if($request->status == 13)
				@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "DATOS DE PAGOS"]) @endcomponent
				@php
					$modelTable	=
					[
						"Comentarios"	=>	$request->paymentComment == "" ? "Sin comentarios" : htmlentities($request->paymentComment),
					];
				@endphp
				@component('components.templates.outputs.table-detail-single', ["modelTable" => $modelTable])@endcomponent
			@endif
			<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mt-8">
				@if($request->status == "2")
					@component('components.buttons.button', ["variant" => "primary", "attributeEx" => "type=\"submit\" name=\"enviar\" value=\"ENVIAR SOLICITUD\"", "label" => "ENVIAR SOLICITUD"]) @endcomponent
					@component('components.buttons.button', ["variant" => "secondary", "attributeEx" => "type=\"submit\" id=\"save\" name=\"save\" value=\"GUARDAR SIN ENVIAR\" formaction=\"".route('movements-accounts.loan.follow.unsent', $request->folio)."\"", "classEx" => "save", "label" => "GUARDAR SIN ENVIAR"]) @endcomponent
				@endif
				@php
					$href	=	isset($option_id) ? url(App\Module::find($option_id)->url) : url(App\Module::find($child_id)->url);
				@endphp
				@component('components.buttons.button', ["variant" => "reset", "attributeEx" => "href=\"".$href."\"", "classEx" => "load-actioner", "label" => "REGRESAR", "buttonElement" => "a"]) @endcomponent
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
				amount	= Number($('input[name="amount"]').val());
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
				else if($('input[name="amount"]').val() != "" && (amount <= 0 || isNaN(amount)))
				{
					if(amount <= 0) 
					{
						swal('', 'El importe no puede ser menor o igual a cero', 'error');
						$('input[name="amount"]').addClass('error');
					}
					if(isNaN(amount)) 
					{
						swal('', 'Por favor, verifique el importe ingresado', 'error');
						$('input[name="amount"]').addClass('error');
					}
					return false;
				}
				swal("Cargando",{
					icon: '{{ asset(getenv('LOADING_IMG')) }}',
					button: false,
					closeOnClickOutside: false,
					closeOnEsc: false
				});
				return true;
			}
		});
	}
	$(document).ready(function()
	{
		validate();
		$('[name="amount"]').on("contextmenu",function(e)
		{
			return false;
		});
		$('.amount,.tquanty,.tprice,.tamount,.descuento,.totaliva,.subtotal,.total,.amountAdditional,.amountAdditional_billing,retentionAmount,retentionAmount_billing',).numeric({ altDecimal: ".", decimalPlaces: 2,negative: false });
		$(function() 
		{
			$("#datepicker").datepicker({ dateFormat: "dd-mm-yy" });
			$(".datepicker2").datepicker({ dateFormat: "dd-mm-yy" });
		});
		generalSelect({'selector': '.js-users', 'model': 13});
		generalSelect({'selector': '.js-accounts-origin', "depends": ".js-enterprises-origin", 'model': 6});
		generalSelect({'selector': '.js-accounts-destination', "depends": ".js-enterprises-destination", 'model': 6});
		@php
			$selects = collect([
				[
					"identificator"				=> ".js-enterprises-origin",
					"placeholder"				=> "Seleccione la empresa",
					"language"					=> "es",
					"maximumSelectionLength"	=> "1"
				],
				[
					"identificator"				=> ".js-enterprises-destination",
					"placeholder"				=> "Seleccione la empresa",
					"language"					=> "es",
					"maximumSelectionLength"	=> "1"
				],
				[
					"identificator"				=> ".js-form-pay",
					"placeholder"				=> "Seleccione una forma de pago",
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
		$(document).on('change','.amount',function()
		{
			amount = Number($('.amount').val());
			if($('.amount').val() == 0)
			{
				$(this).val('');
				$('[name="amount_total"],[name="amount"]').val('');
				swal('','El importe no puede ser 0.','error');
				return false;
			}
			else if(isNaN(amount)) 
			{
				swal('', 'Por favor, verifique el importe ingresado', 'error');
				$('[name="amount"]').addClass('error');
				$('[name="amount_total"],[name="amount"]').val('');
			}
			else
			{
				$('[name="amount_total"]').val('$'+$(this).val());
			}
		})
		.on('click','#save',function()
		{
			amount	=	Number($('[name="amount"]').val());
			if($('[name="amount"]').val() != "" && (amount <= 0 || isNaN(amount)))
			{
				if(amount <= 0) 
				{
					swal('', 'El importe no puede ser menor o igual a cero', 'error');
					$('[name="amount"]').addClass('error');
					$('[name="amount_total"],[name="amount"]').val('');
				}
				if(isNaN(amount)) 
				{
					swal('', 'Por favor, verifique el importe ingresado', 'error');
					$('[name="amount"]').addClass('error');
					$('[name="amount_total"],[name="amount"]').val('');
				}
				return false;
			}
			else
			{
				$('.remove').removeAttr('data-validation');
				$('.removeselect').removeAttr('required');
				$('.removeselect').removeAttr('data-validation');
				$('.request-validate').removeClass('request-validate');
			}
		})
		.on('change','.js-enterprises-origin',function()
		{
			$('.js-accounts-origin').empty();
		})
		.on('change','.js-enterprises-destination',function()
		{
			$('.js-accounts-destination').empty();
		})
		.on('click','#addDoc',function()
		{
			@php
				$newDoc = view('components.documents.upload-files',[
					"attributeExInput"		=>	"type=\"file\" name=\"path\" accept=\".pdf,.jpg,.png\"",
					"attributeExRealPath"	=>	"name=\"realPath[]\"",
					"classExRealPath"		=>	"path",
					"classExInput"			=>	"generalPath pathActioner",
					"classExDelete"			=>	"delete-doc",
				])->render();
			@endphp
			newDoc = '{!!preg_replace("/(\r)*(\n)*/", "", $newDoc)!!}';
			containerNewDoc = $(newDoc);
			$('#documents').append(containerNewDoc);
			$('#documents').removeClass('hidden');
			$(function() 
			{
				$( ".datepicker" ).datepicker({ maxDate: 0, dateFormat: "dd-mm-yy" });
			});
		})
		.on('click','.span-delete',function()
		{
			$(this).parents('span').remove();
		})
		.on('change','.generalPath.pathActioner',function(e)
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
			$(this).parents('.docs-p').remove();
			if($('.docs-p').length<1)
			{
				$('#documents').addClass('hidden');
			}
		});
	});
</script>
@endsection