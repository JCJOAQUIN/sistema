@extends('layouts.child_module')

@section('data')
	@php
	$taxesCount	=	$taxesCountBilling	=	0;
	$taxes		=	$retentions	=	$taxesBilling = $retentionsBilling = 0;
	@endphp
	<div id="form-loan">
		@component('components.forms.form', ["attributeEx" => "action=\"".route('movements-accounts.loan.store')."\" method=\"POST\" id=\"container-alta\"", "files" => true])
			@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "FORMULARIO DE PRÉSTAMOS"]) @endcomponent
			@component('components.containers.container-form')
				<div class="col-span-2">
					@component('components.labels.label') Título: @endcomponent
					@component('components.inputs.input-text', ["classEx" => "generalInput removeselect"])
						@slot('attributeEx')
							type="text" name="title" placeholder="Ingrese el título" data-validation="required" @if(isset($requests)) value="{{ $requests->loanEnterprise->first()->title }}" @endif
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Fecha: @endcomponent
					@component('components.inputs.input-text', ["classEx" => "generalInput removeselect datepicker"])
						@slot('attributeEx')
							type="text" name="datetitle" @if(isset($requests)) value="{{ $requests->loanEnterprise->first()->datetitle != null ? Carbon\Carbon::createFromFormat('Y-m-d',$requests->loanEnterprise->first()->datetitle)->format('d-m-Y') : null }}" @endif placeholder="Ingrese la fecha" data-validation="required" readonly="readonly"
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Fiscal: @endcomponent
					<div class="flex p-0 space-x-2">
						@component('components.buttons.button-approval')
							@slot('attributeEx') id="nofiscal" name="fiscal" value="0" checked @endslot
							@slot('classExContainer')
								inline-flex
							@endslot
							No
						@endcomponent
						@component('components.buttons.button-approval')
							@slot('attributeEx') id="fiscal" name="fiscal" value="1" @endslot
							@slot('classExContainer') inline-flex @endslot
							Sí
						@endcomponent
					</div>
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Solicitante: @endcomponent
					@php
						$options	=	collect();
						if (isset($requests) && $requests->idRequest != "")
						{
							$options	=	$options->concat([["value"	=>	$requests->requestUser->id, "description"	=>	$requests->requestUser->fullname(),	"selected"	=>	"selected"]]);
						}
					@endphp
					@component('components.inputs.select', ["options" => $options, "attributeEx" => "name=\"userid\" multiple=\"multiple\" data-validation=\"required\"", "classEx" => "js-users removeselect"]) @endcomponent
				</div>
			@endcomponent
			@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "CUENTA DE ORIGEN"]) @endcomponent
			@component('components.containers.container-form')
				<div class="col-span-2">
					@component('components.labels.label') Empresa: @endcomponent
					@php
						$options	=	collect();
						foreach (App\Enterprise::orderName()->where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeEnt($option_id)->pluck('enterprise_id'))->get() as $enterprise)
						{
							if (isset($requests) && $requests->loanEnterprise->first()->idEnterpriseOrigin == $enterprise->id)
							{
								$options	=	$options->concat([["value"	=>	$enterprise->id,	"description"	=>	strlen($enterprise->name) >= 35 ? substr(strip_tags($enterprise->name),0,35).'...' : $enterprise->name,	"selected"	=>	"selected"]]);
							}
							else
							{
								$options	=	$options->concat([["value"	=>	$enterprise->id,	"description"	=>	strlen($enterprise->name) >= 35 ? substr(strip_tags($enterprise->name),0,35).'...' : $enterprise->name]]);
							}
						}
					@endphp
					@component('components.inputs.select', ["options" => $options, "attributeEx" => "name=\"enterpriseid_origin\" multiple=\"multiple\" data-validation=\"required\"", "classEx" => "js-enterprises-origin removeselect"]) @endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Clasificación del gasto: @endcomponent
					@php
						$options	=	collect();
						if (isset($requests) && $requests->loanEnterprise->first()->idAccAccOrigin != "")
						{
							$options	=	$options->concat([["value"	=>	$requests->loanEnterprise->first()->accountOrigin->idAccAcc,	"description"	=>	$requests->loanEnterprise->first()->accountOrigin->account." - ".$requests->loanEnterprise->first()->accountOrigin->description." (".$requests->loanEnterprise->first()->accountOrigin->content.")",	"selected"	=>	"selected"]]);
						}
					@endphp
					@component('components.inputs.select', ["options" => $options, "attributeEx" => "multiple=\"multiple\" name=\"accountid_origin\"  data-validation=\"required\"", "classEx" => "js-accounts-origin removeselect"]) @endcomponent
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
							if (isset($requests) && $requests->loanEnterprise->first()->idEnterpriseDestiny == $enterprise->id)
							{
								$options	=	$options->concat([["value"	=>	$enterprise->id,	"description"	=>	strlen($enterprise->name) >= 35 ? substr(strip_tags($enterprise->name),0,35).'...' : $enterprise->name,	"selected"	=>	"selected"]]);
							}
							else
							{
								$options	=	$options->concat([["value"	=>	$enterprise->id,	"description"	=>	strlen($enterprise->name) >= 35 ? substr(strip_tags($enterprise->name),0,35).'...' : $enterprise->name]]);
							}
						}
					@endphp
					@component('components.inputs.select', ["options" => $options, "attributeEx" => "name=\"enterpriseid_destination\" multiple=\"multiple\" data-validation=\"required\"", "classEx" => "js-enterprises-destination removeselect"])
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label', ["label" => "Clasificación del gasto: "]) @endcomponent
					@php
						$options	=	collect();
						if (isset($requests) && $requests->loanEnterprise->first()->idAccAccDestiny != "")
						{
							$options	=	$options->concat([["value"	=>	$requests->loanEnterprise->first()->accountDestiny->idAccAcc,	"description"	=>	$requests->loanEnterprise->first()->accountDestiny->account." - ".$requests->loanEnterprise->first()->accountDestiny->description." (".$requests->loanEnterprise->first()->accountDestiny->content.")",	"selected"	=>	"selected"]]);
						}
					@endphp
					@component('components.inputs.select', ["options" => $options, "attributeEx" => "multiple=\"multiple\" name=\"accountid_destination\" data-validation=\"required\"", "classEx" => "js-accounts-destination removeselect"])
					@endcomponent
				</div>
			@endcomponent
			@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "DATOS DEL PRÉSTAMO"]) @endcomponent
			@component('components.containers.container-form')
				<div class="col-span-2 md:col-start-2 md:col-end-4">
					@component('components.labels.label', ["label" => "Importe:"]) @endcomponent
					@component('components.inputs.input-text', ["classEx" => "generalInput amount"])
						@slot('attributeEx')
							type="text" name="amount" placeholder="Ingrese el importe" @if(isset($requests)) value="{{ $requests->loanEnterprise->first()->amount }}" @endif
						@endslot
					@endcomponent
				</div>
			@endcomponent
			@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "CONDICIONES DE PAGO"]) @endcomponent
			@component('components.containers.container-form')
				<div class="col-span-2">
					@component('components.labels.label', ["label" => "Tipo de moneda:"]) @endcomponent
					@php
						$options		=	collect();
						$currencyData	=	["MXN","USD","EUR","Otro"];
						foreach ($currencyData as $currency)
						{
							if (isset($requests) && $requests->loanEnterprise->first()->currency == $currency)
							{
								$options	=	$options->concat([["value"	=>	$currency,	"description"	=>	$currency,	"selected"	=>	"selected"]]);
							}
							else
							{
								$options	=	$options->concat([["value"	=>	$currency,	"description"	=>	$currency]]);
							}
						}
					@endphp
					@component('components.inputs.select', ["options" => $options, "attributeEx" => "name=\"type_currency\" multiple=\"multiple\" data-validation=\"required\"", "classEx" => "removeselect"]) @endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label', ["label" => "Fecha de Pago:"]) @endcomponent
					@component('components.inputs.input-text', ["classEx" => "generalInput remove datepicker"])
						@slot('attributeEx')
							type="text" name="date" step="1" placeholder="Ingrese la fecha" data-validation="required" readonly="readonly" id="datepicker" @if(isset($requests)) value="{{ $requests->PaymentDate != null  ? Carbon\Carbon::createFromFormat('Y-m-d',$requests->PaymentDate)->format('d-m-Y') : null }}" @endif
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label', ["label" => "Forma de pago:"]) @endcomponent
					@php
						$options		=	collect();
						$dataPayMode	=	["1" => "Cuenta Bancaria","2" => "Efectivo","3" => "Cheque"];
						foreach ($dataPayMode as $k => $payOption)
						{
							if (isset($requests) && $requests->loanEnterprise->first()->idpaymentMethod == $k)
							{
								$options	=	$options->concat([["value"	=>	$k, "description"	=>	$payOption, "selected"	=>	"selected"]]);
							}
							else
							{
								$options	=	$options->concat([["value"	=>	$k, "description"	=>	$payOption]]);
							}
						}
					@endphp
					@component('components.inputs.select', ["options" => $options, "attributeEx" => "multiple=\"multiple\" name=\"pay_mode\" data-validation=\"required\"", "classEx" => "js-form-pay removeselect"]) @endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label', ["label" => "Importe a pagar:"]) @endcomponent
					@component('components.inputs.input-text', ["classEx" => "generalInput amount_total remove"])
						@slot('attributeEx')
							type="text" name="amount_total" readonly placeholder="Ingrese el importe" data-validation="required" @if(isset($requests)) value="{{ $requests->loanEnterprise->first()->amount }}" @endif
						@endslot
					@endcomponent
				</div>
			@endcomponent
			@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "CARGAR DOCUMENTOS"]) @endcomponent
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
			<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mt-8">
				@component('components.buttons.button', ["variant" => "primary", "attributeEx" => "type=\"submit\"  name=\"enviar\" value=\"ENVIAR SOLICITUD\"", "classEx" => "enviar", "label" => "ENVIAR SOLICITUD"]) @endcomponent
				@component('components.buttons.button', ["variant" => "secondary", "attributeEx" => "type=\"submit\" id=\"save\" name=\"save\" value=\"GUARDAR SIN ENVIAR\" formaction=\"".route('movements-accounts.loan.unsent')."\"", "classEx" => "save", "label" => "GUARDAR SIN ENVIAR"]) @endcomponent
				@component('components.buttons.button', ["variant" => "reset", "attributeEx" => "type=\"reset\" name=\"borra\" value=\"Borrar campos\"", "classEx" => "btn-delete-form", "label" => "Borrar campos"]) @endcomponent
			</div>
		@endcomponent
	</div>
@endsection
@section('scripts')
<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
<script src="{{ asset('js/jquery-ui.js') }}"></script>
<script src="{{ asset('js/jquery.numeric.js') }}"></script>
<script type="text/javascript">
	$(document).ready(function()
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
				else
				{
					swal("Cargando",{
						icon: '{{ getenv('LOADING_IMG') }}',
						button: false,
						closeOnClickOutside: false,
						closeOnEsc: false
					});
					return true;
				}		
			}
		});

		$('[name="amount"]').on("contextmenu",function(e)
		{
			return false;
		});
		$('.amount,.tquanty,.tprice,.tamount,.descuento,.totaliva,.subtotal,.total,.amountAdditional,.amountAdditional_billing,retentionAmount,retentionAmount_billing',).numeric({ altDecimal: ".", decimalPlaces: 2, negative:false });
		generalSelect({'selector': '.js-users', 'model': 13});
		generalSelect({'selector': '.js-accounts-origin', "depends": ".js-enterprises-origin", 'model': 12});
		generalSelect({'selector': '.js-accounts-destination', "depends": ".js-enterprises-destination", 'model': 12});
		$(function() 
		{
			$(".datepicker").datepicker({  dateFormat: "dd-mm-yy" });
		});
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
					$('.generalInput').val('');
					$('.removeselect').val(null).trigger('change');
				}
				else
				{
					swal.close();
				}
			});
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
			uploadedName	= $(this).parent('.docs-p').find('input[name="realPath[]"]');
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
</script>
@endsection