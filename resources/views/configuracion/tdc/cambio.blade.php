@extends('layouts.child_module')
@section('data')
	@component("components.forms.form", ["attributeEx" => "id=\"container-alta\" method=\"post\" action=\"".route('credit-card.update',$tdc->idcreditCard)."\"", "methodEx" => "PUT"])
		@component('components.labels.title-divisor') DATOS DE TARJETA DE CRÉDITO @endcomponent
		@component('components.labels.subtitle') Para editar la tarjeta de crédito es necesario colocar los siguientes campos: @endcomponent
		@component('components.containers.container-form')
			<div class="col-span-2">
				@component('components.labels.label') Empresa @endcomponent
				@php
					$options = collect();
					foreach(App\Enterprise::orderName()->get() as $enterprise)
					{
						$description = $enterprise->name;
						if($enterprise->id == $tdc->idEnterprise)
						{
							$options = $options->concat([["value" => $enterprise->id, "selected" => "selected", "description" => $description]]);
						}
						else
						{
							$options = $options->concat([["value" => $enterprise->id, "description" => $description]]);
						}
					}
					$attributeEx = "multiple=\"multiple\" name=\"enterprise_id\" data-validation=\"required\"";
					$classEx = "form-control";
				@endphp
				@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx]) @endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Clasificación de Gasto @endcomponent
				@php
					$options = collect();
					foreach(App\Account::where('selectable',1)->where('idEnterprise',$tdc->idEnterprise)->get() as $account)
					{
						$description = $account->account." - ".$account->description." (".$account->content.")";
						if($account->idAccAcc==$tdc->idAccAcc)
						{
							$options = $options->concat([["value" => $account->idAccAcc, "selected" => "selected", "description" => $description]]);
						}
						else
						{
							$options = $options->concat([["value" => $account->idAccAcc, "description" => $description]]);
						}
					}
					$attributeEx = "multiple=\"multiple\" name=\"account_id\" data-validation=\"required\"";
					$classEx = "form-control";
				@endphp
				@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx]) @endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label')
					Banco
				@endcomponent
				@php
					$options = collect();
					if(isset($tdc) && isset($tdc->idBanks))		
					{
						$bank=App\Banks::find($tdc->idBanks);
						$options = $options->concat([["value" => $bank->idBanks, "description" => $bank->description, "selected" => "selected"]]);
					}
					$attributeEx = "multiple=\"multiple\" name=\"bank_id\" data-validation=\"required\"";
					$classEx = "form-control js-bank";
				@endphp
				@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx]) @endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Principal/Adicional @endcomponent
				@php
					$options = collect();
					$value = ["1" => "Principal", "2" => "Adicional"];
					foreach($value as $key => $item)
					{
						$options = $options->concat(
						[
							[
								"value" => $key, 
								"selected" => (($key == $tdc->principal_aditional) ? "selected" : ""), 
								"description" => $item
							]
						]);
					}
					$attributeEx = "name=\"principal_aditional\" data-validation=\"required\"";
					$classEx = "form-control";
				@endphp
				@component('components.inputs.select',["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])  @endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Número de tarjeta principal (solo si es adicional) @endcomponent
				@php
					$options = collect();
					$disabled = '';
					if($tdc->principal_aditional == 1)
					{
						$disabled = "disabled=\"disabled\"";
					}
					foreach(App\CreditCards::where('principal_aditional',1)->get() as $tdcp)
					{
						$description = $tdcp->credit_card;
						if($tdcp->idcreditCard == $tdc->principal_card_id)
						{
							$options = $options->concat([["value" => $tdcp->idcreditCard, "selected" => "selected", "description" => $description]]);
						}
						else
						{
							$options = $options->concat([["value" => $tdcp->idcreditCard, "description" => $description]]);
						}
					}
					$attributeEx = "multiple=\"multiple\" name=\"principal_card_id\" data-validation=\"required\"".$disabled;
					$classEx = "form-control";
				@endphp
				@component('components.inputs.select',["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])  @endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Nombre (el que viene en la tarjeta) @endcomponent
				@component('components.inputs.input-text')
					@slot("attributeEx")
						type="text" 
						name="name_credit_card" 
						placeholder="Ingrese el nombre" 
						data-validation="required" 
						value="{{ $tdc->name_credit_card }}"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Alias @endcomponent
				@component('components.inputs.input-text')
					@slot("attributeEx")
						type="text" 
						name="alias"
						placeholder="Ingrese el alias" 
						data-validation="required" 
						value="{{ $tdc->alias }}"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Asignación (responsable) @endcomponent
				@php
					$options = collect();
					foreach(App\User::orderName()->where('status','ACTIVE')->where('sys_user',1)->get() as $user)
					{
						$description = $user->name." ".$user->last_name." ".$user->scnd_last_name;
						if($tdc->assignment == $user->id)
						{
							$options = $options->concat([["value" => $user->id, "selected" => "selected", "description" => $description]]);
						}
						else
						{
							$options = $options->concat([["value" => $user->id, "description" => $description]]);	
						}
					}
					$attributeEx = "multiple=\"multiple\" name=\"assignment\" data-validation=\"required\"";
					$classEx = "form-control";
				@endphp
				@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx]) @endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Número de tarjeta de crédito @endcomponent
				@component('components.inputs.input-text')
					@slot("attributeEx")
						type="text" 
						name="credit_card"
						placeholder="Ingrese el número de tarjeta" 
						data-validation="tarjeta server" 
						data-validation-url="{{  route('credit-card.validation') }}"
						data-validation-req-params="{{ json_encode(array('oldCreditCard'=>str_replace(' ', '',$tdc->credit_card), 'bank_id'=>$tdc->idBanks, 'principal_id'=>$tdc->principal_aditional)) }}" 
						value="{{ str_replace(' ', '',$tdc->credit_card) }}"
					@endslot
					@slot("classEx")
						credit-card
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Estado @endcomponent
				@php
					$options = collect();
					$value = ["1" => "Vigente","2" => "Bloqueada","3" => "Cancelada"];
					
					foreach($value as $key => $item)
					{
						
						$options = $options->concat(
						[
							[
								"value" => $key, 
								"selected" => (($key == $tdc->status) ? "selected" : ""), 
								"description" => $item
							]
						]);
						
					}
					$attributeEx = "multiple=\"multiple\" name=\"status\" data-validation=\"required\"";
					$classEx = "form-control";
				@endphp
				@component('components.inputs.select',["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])  @endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Tipo de crédito @endcomponent
				@php
					$options = collect();
					$value = ["1" => "Personal","2" => "Empresarial","3" => "Ágil", "4" => "Otro"];
					
					foreach($value as $key => $item)
					{						
						$options = $options->concat(
						[
							[
								"value" => $key, 
								"selected" => (($key == $tdc->type_credit) ? "selected" : ""), 
								"description" => $item
							]
						]);						
					}
					$attributeEx = "multiple=\"multiple\" name=\"type_credit\" data-validation=\"required\"";
					$classEx = "form-control";
				@endphp
				@component('components.inputs.select',["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])  @endcomponent
				@component('components.inputs.input-text')
					@slot("attributeEx")
						type="text"
						name="type_credit_other"
						placeholder="Seleccione el tipo de crédito"
						data-validation="required"
					@endslot
					@slot('classEx')
						hidden
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Fecha de Corte @endcomponent
				@component('components.inputs.input-text')
					@slot("attributeEx")
						type="text" 
						name="cutoff_date"
						readonly="readonly" 
						placeholder="Seleccione la fecha de corte"
						data-validation="required"
						value="{{ Carbon\Carbon::createFromFormat('Y-m-d',$tdc->cutoff_date)->format('d-m-Y') }}"
					@endslot
					@slot("classEx")
						datepicker
					@endslot
				@endcomponent
			</div>
			<div  class="col-span-2">
				@component('components.labels.label') Fecha de Pago @endcomponent
				@component('components.inputs.input-text')
					@slot("attributeEx")
						type="text" 
						name="payment_date"
						readonly="readonly" 
						placeholder="Seleccione la fecha de pago" 
						data-validation="required"
						value="{{ Carbon\Carbon::createFromFormat('Y-m-d',$tdc->payment_date)->format('d-m-Y') }}"
					@endslot
					@slot("classEx")
						datepicker
					@endslot
				@endcomponent
			</div>
			<div  class="col-span-2">
				@component("components.labels.label") Límite de crédito @endcomponent
				@component("components.inputs.input-text")
					@slot("attributeEx")
						type="text" 
						name="limit_credit"
						placeholder="Ingrese el límite de crédito"
						data-validation="required" 
						value="{{ $tdc->limit_credit }}"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label")
					Tipo de Moneda
				@endcomponent
				@php
					$options = collect();
					$value = ["MXN", "EUR", "USD", "Otro"];
					foreach($value as $item)
					{				
						if($item == $tdc->type_currency)
						{
							$options = $options->concat([["value" => $item, "description" => $item, "selected" => "selected"]]);
						}
						else
						{
							$options = $options->concat([["value" => $item, "description" => $item]]);
						}				
					}
					$attributeEx = "name=\"type_currency\" data-validation=\"required\"";
					$classEx = "form-control";
				@endphp
				@component('components.inputs.select',["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])  @endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Gasto promedio del mes @endcomponent
				@php $calcgpm = App\Payment::where('account',$tdc->idAccAcc)->whereMonth('paymentDate',date('m'))->whereYear('paymentDate',date('Y'))->avg('amount'); @endphp
					@if($calcgpm != NULL)
						@component("components.inputs.input-text")
							@slot("attributeEx")
								type="text" 
								name="monthly_average_expense" 
								readonly="readonly"
								value="{{ $calcgpm }}"
							@endslot
						@endcomponent
					@else
						@component("components.inputs.input-text")
							@slot("attributeEx")
								type="text" 
								name="monthly_average_expense" 
								readonly="readonly"
								value="{{'0.00'}}"
							@endslot
						@endcomponent
					@endif
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Saldo Actual @endcomponent
				@component("components.inputs.input-text")
					@slot('attributeEx')
						type="text" 
						name="monthly_average_expense" 
						readonly="readonly"
						value="{{ $tdc->limit_credit-App\Payment::where('account',$tdc->idAccAcc)->whereMonth('paymentDate',date('m'))->whereYear('paymentDate',date('Y'))->sum('amount') }}"
					@endslot
				@endcomponent
			</div>
		@endcomponent
		<div id="chartMonth"></div>
		@php
			$heads = ["",""];
			$body = [];
			$modelBody = [];
			
			$options = collect();
			$option = collect();
			
			for($year = 2019; $year<= date("Y"); $year++)
			{
				$description = $year;
				if($year == date("Y"))
				{
					$options = $options->concat([["value" => $year, "selected" => "selected", "description" => $description]]);
				}
				else
				{
					$options = $options->concat([["value" => $year, "description" => $description]]);
				}
			}
			
			$months = array('','Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre');
			
			for($month = 1; $month <= 12; $month++)
			{
				$description = $months[$month];
				if($month == date("n"))
				{
					$option = $option->concat([["value" => $month, "selected" => "selected", "description" => $description]]);
				}
				else
				{
					$option = $option->concat([["value" => $month, "description" => $description]]);
				}
			}
			
			$body =
			[
				"classEx" => "tr",
				[
					"content" =>
					[
						[
							"kind" 			=> "components.inputs.select",
							"attributeEx" 	=> "multiple=\"multiple\" name=\"year\" data-validation=\"required\"",
							"classEx" 		=> "form-control",
							"options" 		=> $options,
							]
							]
				],
				[
					"content" =>
					[
						[
							"kind" 			=> "components.inputs.select",
							"attributeEx" 	=> "multiple=\"multiple\" name=\"month\" data-validation=\"required\"",
							"classEx" 		=> "form-control",
							"options" 		=> $option,
						]
					]
				]
			];
			$modelBody[] = $body;
		@endphp
		@component("components.tables.alwaysVisibleTable",[
			"modelHead" => $heads,
			"modelBody" => $modelBody,
			"variant"	=> "default",
			"title"		=> "Generar Estado de Cuenta Local"
			])
		@endcomponent
							
		<div class="text-center">
			@component("components.buttons.button")
				@slot("attributeEx")
					type="submit" 
					formaction="{{ route('credit-card.account-status', $tdc->idcreditCard) }}"
				@endslot
				@slot("classEx")
					download
				@endslot
				DESCARGAR
			@endcomponent
		</div>
		
		@php
			$heads = ["Estado de Cuenta","Fecha"];
			$body = [];
			$modelBody = [];
			
			foreach(App\CreditCardDocuments::where('idcreditCard',$tdc->idcreditCard)->get() as $doc)
			{
				$body = 
				[
					"classEx" => "tr",
					[
						"content" =>
						[
							[
								"kind"			=> "components.buttons.button",
								"buttonElement"	=> "a",
								"attributeEx"	=> "href=\"".url('docs/credit_card/'.$doc->path)."\" target=\"_blank\"",
								"label"			=> "Archivo",
								"variant"		=> "secondary",
								]
						]
					],
					[
						"content" =>
						[
							[
								"label" => $doc->date->format('d-m-Y'),
							]
						]
					]
				];
				$modelBody[] = $body;
			}
		@endphp
		@component("components.tables.alwaysVisibleTable",[
			"modelHead" => $heads,
			"modelBody" => $modelBody,
			"variant"	=> "default",
			"title"		=> "ESTADOS DE CUENTA BANCARIOS"
			])
		@endcomponent
			
		@component('components.labels.title-divisor')    CARGAR ESTADOS DE CUENTA BANCARIOS @endcomponent
		@component("components.containers.container-form")
			<div class="col-span-2 md:col-span-4 grid grid-cols-1 md:grid-cols-2 gap-6 hidden" id="documents"></div>
			<div class="col-span-2 md:col-span-4 grid justify-items-center md:justify-items-start">
				@component("components.buttons.button",["variant" => "warning"])
					@slot("attributeEx")
						type="button" 
						name="addDoc" 
						id="addDoc"
					@endslot
					@slot('label')
						<span class="icon-plus"></span>
						<span>Agregar Documento</span>
					@endslot
				@endcomponent
			</div>
		@endcomponent
		<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mt-8">
			@component("components.buttons.button")
				@slot("attributeEx")
					type="submit" 
					name="update"
				@endslot
					ACTUALIZAR
			@endcomponent
			@component('components.buttons.button', ["variant"=>"reset", "buttonElement"=>"a"])
				@slot('attributeEx')
					@if(isset($option_id)) 
						href="{{ url(getUrlRedirect($option_id)) }}" 
					@else 
						href="{{ url(getUrlRedirect($child_id)) }}" 
					@endif 
				@endslot
				Regresar
			@endcomponent
		</div>
	@endcomponent		
@endsection
@section('scripts')
<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
<script src="{{ asset('js/jquery-ui.js') }}"></script>
<script src="{{ asset('js/jquery.numeric.js') }}"></script>
<script src="{{ asset('js/jquery.mask.js')}}"></script>
<script src="{{ asset('js/datepicker.js') }}"></script>
<script src="{{ asset('js/apexcharts.js') }}"></script>
<script type="text/javascript">
	$(document).ready(function() 
	{
		validation();
		generalSelect({'selector': '.js-bank', 'model': 28});
		@php
			$selects = collect(
				[
					[
						"identificator"          => "[name=\"enterprise_id\"]",
						"placeholder"            => "Seleccione la empresa",
						"language"				 => "es",
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => "[name=\"principal_aditional\"]",
						"placeholder"            => "Seleccione la opción",
						"language"				 => "es",
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => "[name=\"principal_card_id\"]",
						"placeholder"            => "Seleccione el número de tarjeta",
						"language"				 => "es",
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => "[name=\"type_currency\"]",
						"placeholder"            => "Seleccione el tipo de moneda",
						"language"				 => "es",
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => "[name=\"type_credit\"]",
						"placeholder"            => "Seleccione el tipo de crédito",
						"language"				 => "es",
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => "[name=\"status\"]",
						"placeholder"            => "Seleccione el estado",
						"language"				 => "es",
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => "[name=\"year\"]", 
						"placeholder"            => "Seleccione el año", 
						"language"				 => "es",
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => "[name=\"month\"]", 
						"placeholder"            => "Seleccione el mes", 
						"language"				 => "es",
						"maximumSelectionLength" => "1"
					],
				]
			);
		@endphp
		@component("components.scripts.selects",["selects" => $selects])@endcomponent	
		generalSelect({'selector':'[name="account_id"]', 'depends':'[name="enterprise_id"]', 'model':10});
		generalSelect({'selector':'[name="bank_id"]', 'model':27});
		generalSelect({'selector':'[name=\"assignment\"]', 'model':36});

		chart();
		$('[name="credit_card"],[name="principal_card"]');
		$('.account,.clabe').numeric({ negative : false, decimal : false });
		$('input[name="limit_credit"]',).numeric({ altDecimal: ".", decimalPlaces: 2, negative:false });
		$(".datepicker").datepicker({ dateFormat: "dd-mm-yy" });
		$(document).on('change','[name="enterprise_id"]',function()
		{
			$('[name="account_id"]').empty();
			$enterprise = $(this).val();
			
		})
		.on('change','[name="principal_aditional"]',function()
		{
			$('[name="credit_card"]').removeClass('valid');
			principalAditional	=	$("option:selected", this).val();
			bankOption			=	$("[name='bank_id'] option:selected").val();
			if (bankOption != undefined)
			{
				$('.help-block').addClass('hidden');
				$('[name="credit_card"]').attr('data-validation-req-params', '{"bank_id": '+bankOption+', "principal_id": '+principalAditional+'}').removeClass('error').removeAttr('style').val('');
			}
			else
			{
				$('.help-block').addClass('hidden');
				$('[name="credit_card"]').attr('data-validation-req-params', '{"principal_id": '+principalAditional+'}').removeClass('error').removeAttr('style').val('');
			}
			if ($(this).val() == 2) 
			{
				$('[name="principal_card_id"]').prop('disabled',false);
			}
			else
			{
				$('[name="principal_card_id"]').prop('disabled',true);	
				$('[name="principal_card_id"]').val(0).trigger("change");	
			}
		})
		.on('change','[name="bank_id"]',function()
		{
			bankOption			=	$("option:selected", this).val();
			principalAditional	=	$("[name='principal_aditional'] option:selected").val();
			if (bankOption != undefined)
			{
				if (principalAditional != undefined)
				{
					$('.help-block').addClass('hidden');
					$('[name="credit_card"]').attr('data-validation-req-params', '{"bank_id": '+bankOption+', "principal_id": '+principalAditional+'}').removeClass('error').removeAttr('style').removeClass('valid').val('');
				}
				else
				{
					$('[name="credit_card"]').removeAttr('data-validation-req-params', '{"bank_id": '+bankOption+'}');
				}
			}
		})
		.on('click','[name="update"]',function(e)
		{	
			e.preventDefault();		
			flag = false;
			$('.path').each(function(i,v)
			{
				if( $(this).val() == '')
				{
					
		 			flag = true;
				}
			});

			if(flag)
			{
				swal('', 'Tiene un archivo sin agregar, por favor verifique sus campos.', 'error');
			}
			else
			{
				$(this).parents('form').submit();
			}
		})
		.on('blur','.credit-card',function ()
		{
			$('.credit-card').attr('data-validation',"tarjeta server");
		})
		.on('click','.download',function()
		{
			$('.credit-card').removeAttr('data-validation');
			setTimeout(() => {
				swal.close();
			}, 2000);
		})
		.on('click','#addDoc',function()
		{
			@php
				$newDoc = view('components.documents.upload-files',
				[
					"attributeExInput" 		=> "name=\"path\" accept=\".pdf,.jpg,.png\"",
					"classExInput"			=> "input-text pathActioner",
					"attributeExRealPath"	=> "type=\"hidden\" name=\"realPath[]\" data-validation=\"required\"",
					"classExRealPath"		=> "path",
					"classExDelete" => "delete-doc",
				])->render();
			@endphp
			newDoc          = '{!!preg_replace("/(\r)*(\n)*/", "", $newDoc)!!}';
			containerNewDoc = $(newDoc);			
			$('#documents').append(containerNewDoc);
			$('#documents').removeClass('hidden');
			$(function() 
			{
				$( ".datepicker" ).datepicker({ maxDate: 0, dateFormat: "dd-mm-yy" });
			});
		})
		.on('change','.input-text.pathActioner',function(e)
		{
			filename		= $(this);
			uploadedName 	= $(this).parent('.uploader-content').siblings('input[name="realPath[]"]');
			extention		= /\.jpg|\.png|\.jpeg|\.pdf/i;
			
			if (filename.val().search(extention) == -1)
			{
				swal('', 'El tipo de archivo no es soportado, por favor seleccione un archivo pdf', 'warning');
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
					url			: '{{ route("credit-card.upload") }}',
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
			uploadedName	= $(this).parent('.docs-p-r').siblings('.docs-p-l').children('input[name="realPath[]"]');
			formData		= new FormData();
			formData.append(uploadedName.attr('name'),uploadedName.val());
			$.ajax(
			{
				type		: 'post',
				url			: '{{ route("credit-card.upload") }}',
				data		: formData,
				contentType	: false,
				processData	: false,
				success		: function(r)
				{
					swal.close();
					actioner.parent('.docs-p-r').parent('.docs-p').remove();
				},
				error		: function()
				{
					swal.close();
					actioner.parent('.docs-p-r').parent('.docs-p').remove();
				}
			});
			$(this).parents('div.docs-p').remove();

			if($('.docs-p').length<1)
			{
				$('#documents').addClass('hidden');
			}
		});
	});
	
	@php
		$count 		= 0;
		$monthAct	= date('m');
		$months		= array();
		$num		= 0;
		for ($i=0; $i < $monthAct; $i++) 
		{ 
			$months[] = $num+1;
			$num++;
		}

		$amounts 	= array();
		$monthsName	= ['','Ene','Feb','Mar','Abr','May','Jun','Julio','Ago','Sep','Oct','Nov','Dic'];
	@endphp
	
	@foreach ($months as $month) 
		@php
			$totales[$count]['mes'] 	= $monthsName[$month];
			$totales[$count]['total'] 	= round(App\Payment::where('account',$tdc->idAccAcc)->whereMonth('paymentDate',$month)->whereYear('paymentDate',date('Y'))->sum('amount'),2);
			$totales[$count]['promedio'] 	= round(App\Payment::where('account',$tdc->idAccAcc)->whereMonth('paymentDate',$month)->whereYear('paymentDate',date('Y'))->avg('amount'),2);
			$totales[$count]['saldoRestante'] 	= round($tdc->limit_credit - App\Payment::where('account',$tdc->idAccAcc)->whereMonth('paymentDate',$month)->whereYear('paymentDate',date('Y'))->sum('amount'),2);
			$count++;
		@endphp
	@endforeach
	function chart() 
	{
	    //
	    var options = 
	    {
	        series: 
	        [{
	          	name: 'Total Del Mes',
	          	data: 
	          	[
	          		@foreach ($totales as $total) 
						'{{ $total['total'] }}',
					@endforeach
	          	]
	        }, 
	        {
	         	name: 'Gasto Promedio Por Cada Compra',
	         	data: 
	         	[
	         		@foreach ($totales as $total) 
						'{{ $total['promedio'] }}',
					@endforeach
	         	]
	        }],
	        chart: 
	        {
	          	height: 350,
	          	type: 'area',
	          	defaultLocale: 'es',
				locales: [{
					name: 'es',
					options: 
					{
						toolbar:
						{
							exportToSVG	: "Descargar SVG",
							exportToPNG	: "Descargar PNG",
							exportToCSV	: "Descargar CSV",
							menu		: "Menú",
							selection	: "Selección",
							selectionZoom : "Acercar Selección",
							zoomIn		: "Acercar",
							zoomOut		: "Alejar",
							pan			: "Desplazar",
							reset		: "Reestablecer",
						},
					},
				}],
	        },
	        dataLabels: 
	        {
	          	enabled: false
	        },
	        title: 
	        {
	          	text: 'Datos del año en curso de: {{ (isset($tdc->accounts->account) ? $tdc->accounts->account : "---") }} - {{ (isset($tdc->accounts->description) ? $tdc->accounts->description : "---") }}',
	        	align: 'left'
	        },
	        stroke: 
	        {
	          	curve: 'smooth'
	        },
	        xaxis: 
	        {
	          categories: 
	          	[	
	          		@foreach ($totales as $total) 
						'{{ $total['mes'] }}',
					@endforeach
	          	],
	        },
		};

        var chart = new ApexCharts(document.querySelector("#chartMonth"), options);
        chart.render();
	}
	function validation(){
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
				swal(
				{
					icon				: '{{ asset(getenv("LOADING_IMG")) }}',
					button             	: false,
					closeOnClickOutside	: false,
					closeOnEsc         	: false
				});
			}
		});
	}
</script>
@endsection