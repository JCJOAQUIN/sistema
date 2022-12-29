@extends('layouts.child_module')

@section('data')
	@php
		$taxesCount	=	$taxesCountBilling = 0;
		$taxes		=	$retentions = $taxesBilling = $retentionsBilling = 0;
	@endphp
	@component('components.labels.title-divisor')
		@slot('classEx')
			mt-12
		@endslot
		FORMULARIO DE MOVIMIENTOS
	@endcomponent
	@component('components.forms.form', ["attributeEx" => "action=\"".route('movements-accounts.movements.store')."\" method=\"POST\" id=\"container-alta\"", "files" => true])
		@component('components.containers.container-form')
			<div class="col-span-2">
				@component('components.labels.label') Título: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" name="title" placeholder="Ingrese el título" data-validation="required" @if(isset($requests)) value="{{ $requests->movementsEnterprise->first()->title }}" @endif
					@endslot
					@slot('classEx')
						general-inputs removeselect
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Fecha: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" name="datetitle" @if(isset($requests)) value="{{ $requests->movementsEnterprise->first()->datetitle }}" @endif placeholder="Ingrese la fecha" data-validation="required" readonly="readonly"
					@endslot
					@slot('classEx')
					general-inputs removeselect datepicker2
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Fiscal: @endcomponent
				@component('components.buttons.button-approval')
					@slot('attributeEx')
						id="nofiscal" name="fiscal" value="0" checked
					@endslot
					@slot('classExContainer')
						inline-flex
					@endslot
					No
				@endcomponent
				@component('components.buttons.button-approval')
					@slot('attributeEx') id="fiscal" name="fiscal" value="1" @endslot
					@slot('classExContainer')
						inline-flex
					@endslot
					Sí
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Solicitante: @endcomponent
				@php
					$attributeEx	=	"name=\"userid\" multiple=\"multiple\" data-validation=\"required\"";
					$classEx		=	"js-users removeselect";
				@endphp
				@component('components.inputs.select', ["attributeEx" => $attributeEx, "classEx" => $classEx])@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Empresa: @endcomponent
				@php
					$optionsEnterprise = collect();
					foreach (App\Enterprise::orderName()->where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeEnt($option_id)->pluck('enterprise_id'))->get() as $enterprise)
					{
						if (isset($requests) && $requests->movementsEnterprise->first()->idEnterpriseOrigin == $enterprise->id)
						{
							$optionsEnterprise = $optionsEnterprise->concat([["value"=>$enterprise->id, "description"=>strlen($enterprise->name) >= 35 ? substr(strip_tags($enterprise->name),0,35).'...' : $enterprise->name, "selected"=>"selected"]]);
						}
						else
						{
							$optionsEnterprise = $optionsEnterprise->concat([["value"=>$enterprise->id, "description"=>strlen($enterprise->name) >= 35 ? substr(strip_tags($enterprise->name),0,35).'...' : $enterprise->name]]);
						}
					}
					$attributeEx	=	"name=\"enterpriseid\" multiple=\"multiple\" data-validation=\"required\"";
					$classEx		=	"js-enterprises removeselect";
				@endphp
				@component('components.inputs.select',["options" => $optionsEnterprise, "attributeEx" => $attributeEx, "classEx" => $classEx])
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
				@component('components.labels.label') Clasificación del gasto: @endcomponent
				@php
					$options	=	collect();
					if (isset($requests))
					{
						foreach (App\Account::orderNumber()->where('selectable',1)->where('idEnterprise',$requests->movementsEnterprise->first()->idEnterpriseOrigin)->get() as $account)
						{
							if (isset($requests) && $account->idAccAcc==$requests->movementsEnterprise->first()->idAccAccOrigin)
							{
								$options	=	$options->concat([["value"=> $account->idAccAcc, "description"=> $account->account." - ".$account->description." - ".$account->content, "selected"=>"selected"]]);
							}
							else
							{
								$options	=	$options->concat([["value"=> $account->idAccAcc, "description"=> $account->account." - ".$account->description." - ".$account->content]]);
							}
							
						}
					}
					$attributeEx	=	"multiple=\"multiple\" name=\"accountid_origin\"  data-validation=\"required\"";
					$classEx		=	"js-accounts-origin removeselect";
				@endphp
				@component('components.inputs.select', ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx]) @endcomponent
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
				@component('components.labels.label') Clasificación del gasto: @endcomponent
				@php
					$options	=	collect();
					if (isset($requests))
					{
						foreach (App\Account::where('selectable',1)->where('idEnterprise',$requests->movementsEnterprise->first()->idEnterpriseOrigin)->get() as $account)
						{
							if (isset($requests) && $account->idAccAcc==$requests->movementsEnterprise->first()->idAccAccDestiny)
							{
								$options	=	$options->concat([["value" => $account->idAccAcc, "description" => $account->account." ".$account->description." ".$account->content, "selected" => "selected"]]);
							}
							else
							{
								$options	=	$options->concat([["value" => $account->idAccAcc, "description" => $account->account." ".$account->description." ".$account->content]]);
							}
						}
					}
					$attributeEx	=	"multiple=\"multiple\" name=\"accountid_destination\" data-validation=\"required\"";
					$classEx		=	"js-accounts-destination removeselect";
				@endphp
				@component('components.inputs.select', ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx]) @endcomponent
			</div>
		@endcomponent
		@component('components.labels.title-divisor')
			@slot('classEx')
				mt-12
			@endslot
			DATOS DEL MOVIMIENTO
		@endcomponent
		@component('components.containers.container-form')
			<div class="col-span-4 md:col-start-2 md:col-span-2 md:col-end-4">
				@component('components.labels.label') Importe: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" name="amount" placeholder="Ingrese el importe" @if(isset($requests)) value="{{ $requests->movementsEnterprise->first()->amount }}" @endif
					@endslot
					@slot('classEx')
						general-inputs amount
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
					$optionCurrency	=	collect();
					$values			=	["MXN","USD","EUR","Otro"];
					foreach($values as $value)
					{
						if(isset($requests) && $requests->movementsEnterprise->first()->typeCurrency == $value)
						{
							$optionCurrency	=	$optionCurrency->concat([["value"	=>	$value,	"description"	=>	$value,	"selected"	=>	"selected"]]);
						}
						else
						{
							$optionCurrency	=	$optionCurrency->concat([["value"	=>	$value,	"description"	=>	$value]]);
						}
					}
					$attributeEx	=	"name=\"type_currency\" multiple=\"multiple\" data-validation=\"required\"";
					$classEx		=	"removeselect";
				@endphp
				@component('components.inputs.select', ["options" => $optionCurrency, "attributeEx" => $attributeEx, "classEx" => $classEx]) @endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Fecha de Pago: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" name="date" step="1" placeholder="Ingrese la fecha" readonly="readonly"  data-validation="required"
					@endslot
					@slot('classEx')
						general-inputs remove datepicker2
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Forma de pago: @endcomponent
				@php
					$optionsPay	=	collect();
					$values		=	["1" => "Transferencia", "2" => "Efectivo", "3" => "Cheque"];
					foreach ($values as $k => $value)
					{
						if (isset($requests) && $requests->movementsEnterprise->first()->idpaymentMethod == $k)
						{
							$optionsPay	=	$optionsPay->concat([["value" => $k, "description" => $value, "selected" => "selected"]]);
						}
						else
						{
							$optionsPay	=	$optionsPay->concat([["value" => $k, "description" => $value]]);
						}
					}
					$attributeEx	=	"multiple=\"multiple\" name=\"pay_mode\" data-validation=\"required\"";
					$classEx		=	"js-form-pay removeselect";
				@endphp
				@component('components.inputs.select', ["options" => $optionsPay, "attributeEx" => $attributeEx, "classEx" => $classEx]) @endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Importe a pagar: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" name="amount_total" readonly placeholder="Ingrese el importe" data-validation="required" @if(isset($requests)) value="{{ $requests->movementsEnterprise->first()->amount }}" @endif
					@endslot
					@slot('classEx')
						general-inputs amount_total remove
					@endslot
				@endcomponent
			</div>
		@endcomponent
		@component('components.labels.title-divisor')
			CARGAR DOCUMENTOS
		@endcomponent
		@component('components.containers.container-form')
			<div id="documents" class="col-span-2 md:col-span-4 grid grid-cols-1 md:grid-cols-2 gap-6 hidden"></div>
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
			@component('components.buttons.button',	["variant"	=>	"primary",		"classEx"	=>	"enviar",			"label"	=>	"ENVIAR SOLICITUD",		"attributeEx"	=>	"type=\"submit\" name=\"enviar\"	value=\"ENVIAR SOLICITUD\""]) @endcomponent
			@component('components.buttons.button',	["variant"	=>	"secondary",	"classEx"	=>	"save",				"label"	=>	"GUARDAR SIN ENVIAR",	"attributeEx"	=>	"type=\"submit\" name=\"save\"		value=\"GUARDAR SIN ENVIAR\" id=\"save\" formaction=\"".route('movements-accounts.movements.unsent')."\""]) @endcomponent
			@component('components.buttons.button',	["variant"	=>	"reset",		"classEx"	=>	"btn-delete-form",	"label"	=>	"Borrar campos",		"attributeEx"	=>	"type=\"reset\"	 name=\"borrar\"	value=\"Borrar campos\""]) @endcomponent
		</div>
	@endcomponent
@endsection
@section('scripts')
	<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
	<script src="{{ asset('js/jquery-ui.js') }}"></script>
	<script src="{{ asset('js/jquery.numeric.js') }}"></script>
	<script src="{{ asset('js/datepicker.js') }}"></script>
	<script type="text/javascript">
		$(document).ready(function()
		{
			$.validate(
			{
				form: '#container-alta',
				modules		:	'security',
				onError	:	function($form)
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
			count			=	0;
			countB			=	{{ $taxesCount }};
			countBilling	=	{{ $taxesCountBilling }};
			$('.phone,.clabe,.account,.cp').numeric(false);
			$('.price, .dis').numeric({ negative : false });
			$('.quanty').numeric({ negative : false, decimal : false });
			
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
			.on('click','.btn-delete-form',function(e)
			{
				e.preventDefault();
				form = $(this).parents('form');
				swal({
					title		:	"Limpiar formulario",
					text		:	"¿Confirma que desea limpiar el formulario?",
					icon		:	"warning",
					buttons		:	["Cancelar","OK"],
					dangerMode	:	true,
				})
				.then((willClean) =>
				{
					if(willClean)
					{
						form[0].reset();
						$('#body').html('');
						$('.general-inputs').val('');
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
				actioner		=	$(this);
				uploadedName	=	$(this).parent('.docs-p').find('input[name="realPath[]"]');
				formData		=	new FormData();
				formData.append(uploadedName.attr('name'),uploadedName.val());
				$.ajax(
				{
					type		: 'post',
					url			: '{{ url("movements-accounts.upload") }}',
					data		: formData,
					contentType	: false,
					processData	: false,
					success		: function(r)
					{
						swal.close();
						actioner.parents('.docs-p').remove();
					},
					error	: function()
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
