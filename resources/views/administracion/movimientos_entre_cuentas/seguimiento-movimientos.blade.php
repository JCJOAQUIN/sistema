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
		$taxesCount	=	$taxesCountBilling = 0;
		$taxes		=	$retentions = $taxesBilling = $retentionsBilling = 0;
	@endphp
	<div id="form-loan">
		@component('components.forms.form', ["attributeEx" => "acction=\"".route('movements-accounts.movements.follow.update', $request->folio)."\" method=\"POST\" id=\"container-alta\"", "files" => true, "methodEx" => "PUT"])
			@component('components.labels.title-divisor')
				@slot('classEx')
					mt-12
				@endslot
				FORMULARIO DE MOVIMIENTOS MISMA EMPRESA
			@endcomponent
			@component('components.containers.container-form')
				<div class="col-span-2">
					@component('components.labels.label') Título: @endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							@if($request->status!=2) disabled="disabled" @endif type="text" name="title" placeholder="Ingrese un título" data-validation="required" @if($request->movementsEnterprise()->exists()) value="{{ $request->movementsEnterprise->first()->title }}" @endif
						@endslot
						@slot('classEx')
							new-input-text removeselect
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Fecha: @endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							@if($request->status!=2) disabled="disabled" @endif type="text" name="datetitle" @if($request->movementsEnterprise()->exists() && $request->movementsEnterprise->first()->datetitle != "") value="{{ Carbon\Carbon::createFromFormat('Y-m-d',$request->movementsEnterprise->first()->datetitle)->format('d-m-Y') }}" @endif placeholder="Ingrese la fecha" data-validation="required" readonly="readonly"
						@endslot
						@slot('classEx')
							new-input-text removeselect datepicker2
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Fiscal: @endcomponent
					@component('components.buttons.button-approval')
						@slot('attributeEx')
							id="nofiscal" name="fiscal" value="0" @if(isset($request) && $request->taxPayment==0) checked @endif @if($request->status!=2) disabled="disabled" @endif
						@endslot
						@slot('classExContainer')
							inline-flex
						@endslot
						No
					@endcomponent
					@component('components.buttons.button-approval')
						@slot('attributeEx')
							id="fiscal" name="fiscal" value="1" @if(isset($request) && $request->taxPayment==1) checked @endif @if($request->status!=2) disabled="disabled" @endif
						@endslot
						@slot('classExContainer')
							inline-flex
						@endslot
						Sí
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Solicitante: @endcomponent
					@php
						$options	=	collect();
						if (isset($request))
						{
							$names		=	App\User::find($request->idRequest);
							if($names != "")
							{
								$options	=	$options->concat([["value" => $names->id, "description" => $names->fullname(), "selected" => "selected"]]);
							}
						}
					@endphp
					@component('components.inputs.select', ["options" => $options])
						@slot('attributeEx')
							@if($request->status!=2)
								disabled="disabled" @endif name="userid" multiple="multiple" data-validation="required"
							@endslot
							@slot('classEx')
								js-users removeselect
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Empresa: @endcomponent
					@php
						$options	=	collect();
						foreach (App\Enterprise::orderName()->where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeEnt($option_id)->pluck('enterprise_id'))->get() as $enterprise)
						{
							if ($request->movementsEnterprise()->exists() && $request->movementsEnterprise->first()->idEnterpriseOrigin == $enterprise->id)
							{
								$options	=	$options->concat([["value" => $enterprise->id, "description" => strlen($enterprise->name) >= 35 ? substr(strip_tags($enterprise->name),0,35).'...' : $enterprise->name, "selected" => "selected"]]);
							}
							else
							{
								$options	=	$options->concat([["value" => $enterprise->id, "description" => strlen($enterprise->name) >= 35 ? substr(strip_tags($enterprise->name),0,35).'...' : $enterprise->name]]);
							}
						}
					@endphp
					@component('components.inputs.select', ["options" => $options])
						@slot('attributeEx')
							@if($request->status!=2) disabled="disabled" @endif name="enterpriseid" multiple="multiple" data-validation="required"
						@endslot
						@slot('classEx')
							js-enterprises removeselect
						@endslot
					@endcomponent
				</div>
			@endcomponent
			@component('components.labels.title-divisor')
				@slot('classEx')
					mt-12
				@endslot
				CUENTA DE ORIGEN
			@endcomponent
			@component('components.containers.container-form')
				<div class="col-span-4 md:col-start-2 md:col-span-2 md:col-end-4">
					@component('components.labels.label') Clasificación del Gasto: @endcomponent
					@php
						$options	=	collect();
						if ($request->movementsEnterprise()->exists())
						{
							foreach(App\Account::orderNumber()->where('selectable',1)->where('idEnterprise',$request->movementsEnterprise->first()->idEnterpriseOrigin)->where(function($query){ $query->where('account','like','1%')->orWhere('account','like','2%'); })->get() as $account)
							{
								if ($request->movementsEnterprise()->exists() && $account->idAccAcc==$request->movementsEnterprise->first()->idAccAccOrigin)
								{
									$options	=	$options->concat([["value" => $account->idAccAcc, "description" => $account->account." - ".$account->description." - ".$account->content, "selected" => "selected"]]);
								}
								else
								{
									$options	=	$options->concat([["value" => $account->idAccAcc, "description" => $account->account." - ".$account->description." - ".$account->content]]);
								}
							}
						}
					@endphp
					@component('components.inputs.select', ["options" => $options])
						@slot('attributeEx')
							@if($request->status!=2) disabled="disabled" @endif multiple="multiple" name="accountid_origin" data-validation="required"
						@endslot
						@slot('classEx')
							js-accounts-origin removeselect
						@endslot
					@endcomponent
				</div>
			@endcomponent
			@component('components.labels.title-divisor')
				@slot('classEx')
					mt-12
				@endslot
				CUENTA DESTINO
			@endcomponent
			@component('components.containers.container-form')
				<div class="col-span-4 md:col-start-2 md:col-span-2 md:col-end-4">
					@component('components.labels.label') Clasificación del Gasto: @endcomponent
					@php
						$options	=	collect();
						if ($request->movementsEnterprise()->exists())
						{
							foreach(App\Account::orderNumber()->where('selectable',1)->where('idEnterprise',$request->movementsEnterprise->first()->idEnterpriseDestiny)->where(function($query){ $query->where('account','like','1%')->orWhere('account','like','2%'); })->get() as $account)
							{
								if ($request->movementsEnterprise()->exists() && $account->idAccAcc==$request->movementsEnterprise->first()->idAccAccDestiny)
								{
									$options	=	$options->concat([["value" => $account->idAccAcc, "description" => $account->account." - ".$account->description." ".$account->content, "selected" => "selected"]]);
								}
								else
								{
									$options	=	$options->concat([["value" => $account->idAccAcc, "description" => $account->account." - ".$account->description." ".$account->content]]);
								}
							}
						}
					@endphp
					@component('components.inputs.select', ["options" => $options])
						@slot('attributeEx')
							@if($request->status!=2) disabled="disabled" @endif multiple="multiple" name="accountid_destination" data-validation="required"
						@endslot
						@slot('classEx')
							js-accounts-destination removeselect
						@endslot
					@endcomponent
				</div>
			@endcomponent
			@component('components.labels.title-divisor')
				@slot('classEx')
					mt-12
				@endslot
				DATOS DEL PRÉSTAMO
			@endcomponent
			@component('components.containers.container-form')
				<div class="col-span-4 md:col-start-2 md:col-span-2 md:col-end-4">
					@component('components.labels.label') Importe: @endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							@if($request->status!=2) disabled="disabled" @endif type="text" name="amount" placeholder="Ingrese el importe"  @if($request->movementsEnterprise()->exists()) value="{{ $request->movementsEnterprise->first()->amount }}" @endif
						@endslot
						@slot('classEx')
							new-input-text amount
						@endslot
					@endcomponent
				</div>
			@endcomponent
			@component('components.labels.title-divisor')
				@slot('classEx')
					mt-12
				@endslot
				CONDICIONES DE PAGO
			@endcomponent
			@component('components.containers.container-form')
				<div class="col-span-2">
					@component('components.labels.label') Tipo de moneda: @endcomponent
					@php
						$options	=	collect();
						$values		=	["MXN","USD","EUR","Otro"];
						foreach ($values as $value)
						{
							if ($request->movementsEnterprise()->exists() && $request->movementsEnterprise->first()->typeCurrency == $value)
							{
								$options	=	$options->concat([["value" => $value, "description" => $value, "selected" => "selected"]]);
							}
							else
							{
								$options	=	$options->concat([["value" => $value, "description" => $value]]);
							}
						}
					@endphp
					@component('components.inputs.select', ["options" => $options])
						@slot('attributeEx')
							name="type_currency" multiple="multiple" data-validation="required" @if($request->status!=2) disabled="disabled" @endif
						@endslot
						@slot('classEx')
							remove
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Fecha de Pago: @endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							@if($request->status!=2) disabled="disabled" @endif type="text" name="date" step="1" placeholder="Ingrese la fecha" readonly="readonly" @if($request->movementsEnterprise()->exists() && $request->movementsEnterprise->first()->paymentDate != "") value="{{ Carbon\Carbon::createFromFormat('Y-m-d',$request->movementsEnterprise->first()->paymentDate)->format('d-m-Y') }}" @endif  data-validation="required"
						@endslot
						@slot('classEx')
							new-input-text remove datepicker2
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Forma de pago: @endcomponent
					@php
						$options	=	collect();
						$values		=	["1"=>"Cuenta Bancaria","2"=>"Efectivo","3"=>"cheque"];
						foreach ($values as $k => $value)
						{
							if ($request->movementsEnterprise()->exists() && $request->movementsEnterprise->first()->idpaymentMethod == $k)
							{
								$options	=	$options->concat([["value" => $k, "description" => $value, "selected" => "selected"]]);
							}
							else
							{
								$options	=	$options->concat([["value" => $k, "description" => $value]]);
							}
						}
					@endphp
					@component('components.inputs.select', ["options" => $options])
						@slot('attributeEx')
							@if($request->status!=2) disabled="disabled" @endif multiple="multiple" name="pay_mode" data-validation="required"
						@endslot
						@slot('classEx')
							js-form-pay removeselect
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Importe a pagar: @endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							@if($request->status!=2) disabled="disabled" @endif type="text" name="amount_total" readonly placeholder="Ingrese el importe" data-validation="required" @if($request->movementsEnterprise()->exists()) value="${{ number_format($request->movementsEnterprise->first()->amount,2) }}" @endif
						@endslot
						@slot('classEx')
							new-input-text amount_total remove
						@endslot
					@endcomponent
				</div>
			@endcomponent
			@component('components.labels.title-divisor')
				@slot('classEx')
					mt-12
				@endslot
				DOCUMENTOS
			@endcomponent
			@php
				$modelHead	=	[];
				$body		=	[];
				$modelBody	=	[];
				if (count($request->movementsEnterprise->first()->documentsMovements)>0)
				{
					$modelHead	=	["Documento", "Fecha"];
					foreach($request->movementsEnterprise->first()->documentsMovements as $doc)
					{
						$body	=
						[
							[
								"content"	=>
								[
									[
										"kind"			=>	"components.buttons.button",
										"variant"		=>	"secondary",
										"buttonElement"	=>	"a",
										"attributeEx"	=>	"type=\"button\" target=\"_blank\" href=\"".url('docs/movements/'.$doc->path)."\"",
										"label"			=>	"Archivo"
									]
								],
							],
							[
								"content"	=>	["label"	=>	Carbon\Carbon::parse($doc->date)->format('d-m-Y')],
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
			@component('components.labels.title-divisor')
				CARGAR DOCUMENTOS
			@endcomponent
			@component('components.containers.container-form', ["classEx" => "documentsContent"])
				<div id="documents" class="col-span-2 md:col-span-4 grid grid-cols-1 md:grid-cols-2 gap-6 hidden"></div>
				<div class="col-span-2 md:col-span-4 grid justify-items-center md:justify-items-start">
					<div class="md:block grid">
						@component('components.buttons.button', ["variant" => "warning"])
							@slot('attributeEx')
								type="button" name="addDoc" id="addDoc" @if($request->status == 1) disabled="disabled" @endif
							@endslot
							@slot('classEx')
								mt-4
							@endslot
							<span class="icon-plus"></span>
							<span>Agregar documento</span>
						@endcomponent
						@if ($request->status != 2)
							@component('components.buttons.button', ["variant" => "success"])
								@slot('attributeEx')
									type="submit" name="send" formaction="{{ route('movements-accounts.update.documents', $request->folio) }}" @if($request->status == 1) disabled @endif
								@endslot
								@slot('label')
									Cargar
								@endslot
							@endcomponent
						@endif
					</div>
				</div>
			@endcomponent
			@if($request->idCheck != "")
				@component('components.labels.title-divisor')
					@slot('classEx')
						mt-12
					@endslot
					DATOS DE REVISIÓN
				@endcomponent
				@php
					$requestAccount = App\Account::find($request->movementsEnterprise->first()->idAccAccOriginR);
					$requestAccount = App\Account::find($request->movementsEnterprise->first()->idAccAccDestinyR);
					$modelTable	=
					[
						"Revisó:"								=>	$request->reviewedUser->name." ".$request->reviewedUser->last_name." ".$request->reviewedUser->scnd_last_name,
						"Nombre de la Empresa de Origen:"		=>	App\Enterprise::find($request->movementsEnterprise->first()->idEnterpriseOriginR)->name,
						"Clasificación del Gasto de Origen:"	=>	$requestAccount->account." - ".$requestAccount->description,
						"Nombre de la Empresa de Destino:"		=>	App\Enterprise::find($request->movementsEnterprise->first()->idEnterpriseDestinyR)->name,
						"Clasificación del Gasto de Destino:"	=>	$requestAccount->account." - ".$requestAccount->description,
						"Comentarios:"							=>	$request->checkComment == "Sin comentarios" ? htmlentities($request->checkComment) : "",
					];
				@endphp
				@component('components.templates.outputs.table-detail-single', ["modelTable" => $modelTable])@endcomponent
			@endif
			@if($request->idAuthorize != "")
				@component('components.labels.title-divisor')
					@slot('classEx')
						mt-12
					@endslot
					DATOS DE AUTORIZACIÓN
				@endcomponent
				@php
					$modelTable	=
					[
						"Autorizó:"		=>	$request->authorizedUser->name." ".$request->authorizedUser->last_name." ".$request->authorizedUser->scnd_last_name,
						"Comentarios:"	=>	$request->authorizeComment == "" ? "Sin comentarios" : htmlentities($request->authorizeComment),
					];
				@endphp
				@component('components.templates.outputs.table-detail-single', ["modelTable" => $modelTable])@endcomponent
			@endif
			@if($request->status == 13)
				@component('components.labels.title-divisor')
					@slot('classEx')
						mt-12
					@endslot
					DATOS DE PAGOS
				@endcomponent
				@php
					$modelTable	=
					[
						"Comentarios:"	=>	$request->paymentComment == "" ? "Sin comentarios" : htmlentities($request->paymentComment),
					];
				@endphp
				@component('components.templates.outputs.table-detail-single', ["modelTable" => $modelTable])@endcomponent
			@endif
			<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mt-8">
				@php
					$optionId	=	isset($option_id) ? url(App\Module::find($option_id)->url) : url(App\Module::find($child_id)->url);
				@endphp
				@if($request->status == "2")
					@component('components.buttons.button', ["variant" =>	"primary",	"label"	=>	"ENVIAR SOLICITUD",		"attributeEx"	=>	"type=\"submit\" name=\"enviar\" value=\"ENVIAR SOLICITUD\""]) @endcomponent
					@component('components.buttons.button', ["variant" =>	"secondary",	"label"	=>	"GUARDAR SIN ENVIAR",	"attributeEx"	=>	"type=\"submit\" id=\"save\" name=\"save\" value=\"GUARDAR SIN ENVIAR\" formaction=\"".route('movements-accounts.movements.follow.unsent', $request->folio)."\"",	"classEx"	=>	"save"]) @endcomponent
				@endif
				@component('components.buttons.button', ["variant" =>	"reset", "label" => "REGRESAR", "attributeEx" => "href=\"".$optionId."\"", "classEx" => "load-actioner", "buttonElement"	=>	"a"]) @endcomponent
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
			modules	:	'security',
			onError	:	function($form)
			{
				swal('', 'Por favor llene todos los campos que son obligatorios.', 'error');
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
		$('.amount,.tquanty,.tprice,.tamount,.descuento,.totaliva,.subtotal,.total,.amountAdditional,.amountAdditional_billing,retentionAmount,retentionAmount_billing',).numeric({ altDecimal: ".", decimalPlaces: 2, negative: false});
		$(function() 
		{
			$(".datepicker2").datepicker({  dateFormat: "dd-mm-yy" });
		});
		generalSelect({'selector': '.js-accounts-origin', 'depends': '.js-enterprises', 'model': 6});
		generalSelect({'selector': '.js-accounts-destination', 'depends': '.js-enterprises', 'model': 6});
		generalSelect({'selector': '.js-users', 'model': 13});
		@php
			$selects = collect([
				[
					"identificator"				=>	".js-enterprises",
					"placeholder"				=>	"Seleccione la empresa",
					"language"					=>	"es",
					"maximumSelectionLength"	=>	"1"
				],
				[
					"identificator"				=>	".js-form-pay",
					"placeholder"				=>	"Seleccione una forma de pago",
					"language"					=>	"es",
					"maximumSelectionLength"	=>	"1"
				],
				[
					"identificator"				=>	"[name=\"type_currency\"]",
					"placeholder"				=>	"Seleccione el tipo de moneda",
					"language"					=>	"es",
					"maximumSelectionLength"	=>	"1"
				],
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
		.on('change','.js-enterprises',function()
		{
			$('.js-accounts-origin').empty();
			$('.js-accounts-destination').empty();
		})
		.on('click','#addDoc',function()
		{
			@php
				$newDoc = view('components.documents.upload-files',[
					"attributeExInput"		=>	"type=\"file\" name=\"path\" accept=\".pdf,.jpg,.png\"",
					"attributeExRealPath"	=>	"name=\"realPath[]\"",
					"classExRealPath"		=>	"path",
					"classExInput"			=>	"input-text pathActioner",
					"classExDelete"			=>	"delete-doc",
				])->render();
			@endphp
			newDoc = '{!!preg_replace("/(\r)*(\n)*/", "", $newDoc)!!}';
			containerNewDoc = $(newDoc);
			$('#documents').append(containerNewDoc);
			
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
		.on('change','.input-text.pathActioner',function(e)
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
				$('#documents-resource').addClass('hidden');
			}
		});
	});
</script>
@endsection