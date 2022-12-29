@extends('layouts.child_module')
@if(isset($request))
	@section('data') 
		@component('components.forms.form', [ "attributeEx" => "method=\"POST\" id=\"container-alta\" action=\"".route('loan.store')."\""])
			@component('components.labels.title-divisor') Nueva solicitud @endcomponent
			@component('components.containers.container-form')
				<div class="col-span-2">
					@component('components.labels.label') Título: @endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							type="text" name="title" placeholder="Ingrese el título" data-validation="required" @if(isset($request)) value="{{ $request->loan->first()->title }}" @endif
						@endslot
						@slot('classEx')
							removeselect
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@php
						$dateTitle = isset($request->loan->first()->datetitle) ? Carbon\Carbon::createFromFormat('Y-m-d',$request->loan->first()->datetitle)->format('d-m-Y') : '';
					@endphp
					@component('components.labels.label') Fecha: @endcomponent
					@component('components.inputs.input-text')
						@slot('classEx')
							removeselect datepicker
						@endslot
						@slot('attributeEx')
							type="text" name="datetitle" @if(isset($request)) value="{{ $dateTitle }}" @endif data-validation="required" placeholder="Ingrese la fecha" readonly="readonly"
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Solicitante: @endcomponent
					@php
						$optionUser 	= [];
						$user 			= App\User::find($request->idRequest);
						$optionUser[]	= ["value" => $user->id, "description" => $request->requestUser->fullName(), "selected" => "selected"]; 
					@endphp
					@component('components.inputs.select', ['options' => $optionUser])
						@slot('attributeEx')
							name="user_id" multiple="multiple" id="multiple-users" data-validation="required"
						@endslot
						@slot('classEx')
							js-users removeselect
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Empresa: @endcomponent
					@php
						$optionEnterprise = [];
						foreach($enterprises as $enterprise)
						{
							if($request->idEnterprise == $enterprise->id)
							{
								$optionEnterprise[] = ["value" => $enterprise->id, "description" => strlen($enterprise->name) >= 35 ? substr(strip_tags($enterprise->name),0,35).'...' : $enterprise->name, "selected" => "selected"];
							}
							else
							{
								$optionEnterprise[] = ["value" => $enterprise->id, "description" => strlen($enterprise->name) >= 35 ? substr(strip_tags($enterprise->name),0,35).'...' : $enterprise->name];
							}
						}
					@endphp
					@component('components.inputs.select', ['options' => $optionEnterprise])
						@slot('attributeEx')
							name="enterprise_id" multiple="multiple" id="multiple-enterprises" data-validation="required"
						@endslot
						@slot('classEx')
							js-enterprises removeselect
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Area: @endcomponent
					@php
						$optionArea = [];
						foreach($areas as $area)
						{
							if($request->idArea == $area->id)
							{
								$optionArea[] = ["value" => $area->id, "description" => $area->name, "selected" => "selected"];
							}
							else
							{
								$optionArea[] = ["value" => $area->id, "description" => $area->name];
							}
						}
					@endphp
					@component('components.inputs.select', ['options' => $optionArea])
						@slot('attributeEx')
							multiple="multiple" name="area_id" id="multiple-areas" data-validation="required"
						@endslot
						@slot('classEx')
							js-areas removeselect
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Departamento: @endcomponent
					@php
						$optionDepartment = [];
						foreach($departments as $department)
						{
							if($request->idDepartment == $department->id)
							{
								$optionDepartment[] = ["value" => $department->id, "description" => $department->name, "selected" => "selected"];
							}
							else
							{
								$optionDepartment[] = ["value" => $department->id, "description" => $department->name];
							}
						}
					@endphp
					@component('components.inputs.select', ['options' => $optionDepartment])
						@slot('attributeEx')
							multiple="multiple" name="department_id" id="multiple-departments" data-validation="required"
						@endslot
						@slot('classEx')
							js-departments removeselect
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Cuenta: @endcomponent
					@php
						$optionAccount 	= [];
						$account 		= App\Account::where('selectable',1)->where('idEnterprise',$request->idEnterprise)->where('description','FUNCIONARIOS Y EMPLEADOS')->first();
						if($request->account == $account->idAccAcc)
						{
							$optionAccount[] = ["value" => $account->idAccAcc, "description" => $account->account.' '.$account->description.' '.'('.$account->content.')', "selected" => "selected"];
						}
					@endphp
					@component('components.inputs.select', ['options' => $optionAccount])
						@slot('attributeEx')
							multiple="multiple" name="account_id" data-validation="required"
						@endslot
						@slot('classEx')
							js-accounts removeselect
						@endslot
					@endcomponent
				</div>
			@endcomponent
			@component('components.labels.title-divisor') FORMA DE PAGO <span class="help-btn" id="help-btn-method-pay"></span> @endcomponent
			@php
				$buttons = 
				[
					[
						"textButton" 		=> "Cuenta Bancaria",
						"attributeButton" 	=> "type=\"radio\" name=\"method\" value=\"1\" id=\"accountBank\"".($request->loan->first()->idpaymentMethod == 1 ? " checked" : ""),
					],
					[
						"textButton" 		=> "Efectivo",
						"attributeButton" 	=> "type=\"radio\" name=\"method\" value=\"2\" id=\"cash\"".($request->loan->first()->idpaymentMethod == 2 ? " checked" : ""),
					],
					[
						"textButton" 		=> "Cheque",
						"attributeButton" 	=> "type=\"radio\" name=\"method\" value=\"3\" id=\"checks\"".($request->loan->first()->idpaymentMethod == 3 ? " checked" : ""),
					],							
				];
			@endphp
			@component("components.buttons.buttons-pay-method", ["buttons" => $buttons]) @endcomponent

			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="hidden" name="employee_number" id="efolio" placeholder="Número de empleado" value="@foreach($request->loan as $loan){{ $loan->idUsers }}@endforeach"
				@endslot
				@slot('classEx')
					employee_number
				@endslot
			@endcomponent
			<div class="resultbank @if($request->loan->first()->idpaymentMethod == 1) block @else hidden @endif">
				@component('components.labels.title-divisor') SELECCIONE UNA CUENTA: @endcomponent
				@php
					$body 		= [];
					$modelBody	= [];
					$modelHead	= [
						[
							["value" => "Acción"],
							["value" => "Banco"],
							["value" => "Alias"],
							["value" => "Número de tarjeta"],
							["value" => "CLABE"],
							["value" => "Número de cuenta"]
						]
					];

					foreach($request->loan as $loan)
					{
						foreach(App\Employee::join('banks','employees.idBanks','banks.idBanks')->where('employees.idUsers',$loan->idUsers)->where('visible',1)->get() as $bank)
						{
							$class 		= '';
							$checked	= '';
							if($loan->idEmployee == $bank->idEmployee)
							{
								$class 		= "marktr";
								$checked	= "checked";
							}
							$body = [ "classEx" => $class,
								[
									"content" =>
									[
										"kind"				=> "components.inputs.checkbox",
										"attributeEx"		=> "id=\"idEmp$bank->idEmployee\" type=\"radio\" name=\"idEmployee\" value=\"".$bank->idEmployee."\"".' '.$checked,
										"classEx"			=> "checkbox",
										"classExLabel"		=> "request-validate",
										"label"				=> "<span class=\"icon-check\"></span>",
										"classExContainer"	=> "my-2",
										"radio"				=> true
									]
								],
								[
									"content" =>
									[
										"label" => $bank->description
									]
								],
								[
									"content" => 
									[
										"label" => $bank->alias != '' ? $bank->alias : '---'
									]
								],
								[
									"content" => 
									[
										"label" => $bank->cardNumber != '' ? $bank->cardNumber : '---'
									]
								],
								[
									"content" => 
									[
										"label" => $bank->clabe != '' ? $bank->clabe : '---'
									]
								],
								[
									"content" => 
									[
										"label" => $bank->account != '' ? $bank->account : '---'
									]
								]
							];
							$modelBody[] = $body;
						}
					}
				@endphp
				@component('components.tables.table', [
					"modelBody" => $modelBody,
					"modelHead" => $modelHead
				])
					@slot('attributeEx')
						id="table2"
					@endslot
					@slot('classExBody')
						request-validate
					@endslot
				@endcomponent
			</div>
			@component('components.containers.container-form')
				<div class="col-span-2">
					@component('components.labels.label') Referencia: @endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							type="text" name="reference" placeholder="Ingrese la referencia" value="{{ $loan->reference }}"
						@endslot
						@slot('classEx')
							remove request-validate removeselect
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Importe: @endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							type="text" name="amount" placeholder="Ingrese el importe" data-validation="required" value="{{ $loan->amount }}"
						@endslot
						@slot('classEx')
							amount remove request-validate removeselect
						@endslot
					@endcomponent
				</div>
			@endcomponent
			<div class="w-full mt-4 grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mb-6">
				@component('components.buttons.button', [
						"varian" => "primary"
					])
					@slot('attributeEx')
						type="submit" name="enviar"
					@endslot
					@slot('classEx')
						text-center
						w-48
						md:w-auto
					@endslot
						ENVIAR SOLICITUD	
				@endcomponent
				@component('components.buttons.button', [
						"variant" => "secondary"
					])
					@slot('attributeEx')
						type="submit" name="save" formaction="{{ route('loan.unsent') }}" id="save"
					@endslot
					@slot('classEx')
						save
						text-center
						w-48
						md:w-auto
					@endslot
						GUARDAR SIN ENVIAR	 
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
		@endcomponent 
	@endsection
	@section('scripts')
		<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
		<script src="{{ asset('js/jquery-ui.js') }}"></script>
		<script src="{{ asset('js/jquery.numeric.js') }}"></script>
		<script src="{{ asset('js/datepicker.js') }}"></script>
		<script>
			$(document).ready(function() 
			{
				@php
					$selects = collect([
						[
							"identificator"				=> ".js-enterprises",
							"placeholder"				=> "Seleccione la empresa",
							"maximumSelectionLength"	=> "1"
						],
						[
							"identificator"				=> ".js-areas",
							"placeholder"				=> "Seleccione la dirección",
							"maximumSelectionLength"	=> "1"
						],
						[
							"identificator"				=> ".js-departments",
							"placeholder"				=> "Seleccione el departamento",
							"maximumSelectionLength"	=> "1"
						],
					]);
				@endphp
				@component('components.scripts.selects',["selects" => $selects]) @endcomponent
				generalSelect({'selector':'.js-users','model':13});
				generalSelect({'selector':'.js-accounts', 'depends':'.js-enterprises', 'model':11});
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
							check 		=  $('.checkbox:checked').length;
							method 		= $('input[name="method"]:checked').val();
							if(method != undefined)
							{
								if (method == 1) 
								{
									if (check>0) 
									{
										swal('Cargando',{
											icon : '{{ url(getenv('LOADING_IMG')) }}',
											button: false,
											closeOnClickOutside: false,
											closeOnEsc: false
										});
										return true;
									}
									else
									{
										swal('', 'Debe seleccionar un cuenta', 'error');
										return false;
									}
								}
								else
								{
									swal('Cargando',{
										icon : '{{ url(getenv('LOADING_IMG')) }}',
										button: false,
										closeOnClickOutside: false,
										closeOnEsc: false
									});
									return true;
								}
								
							}
							else if (method == undefined) 
							{
								swal('', 'Debe seleccionar un método de pago', 'error');
								return false;
							}
						}
						else
						{
							swal('Cargando',{
								icon : '{{ url(getenv('LOADING_IMG')) }}',
								button: false,
								closeOnClickOutside: false,
								closeOnEsc: false
							});
							return true;
						}
					}
				});
				$('.card_number,.destination_account,.destination_key,.employee_number,.amount').numeric(false);    // números
				$('.amount,.importe',).numeric({ altDecimal: ".", decimalPlaces: 2 });
				$('.amount').numeric({ negative : false });
				$(function() 
				{
					$( ".datepicker" ).datepicker({ dateFormat: "dd-mm-yy" });
				});
			});
			$(document).on('change', '.js-users', function()
			{
				id 		= $(this).val();
				folio 	= $('#id'+id).text();

				$('#efolio').val(folio);
				$('.resultbank').stop().show();
				$text = $('#efolio').val();
				$.ajax({
					type : 'post',
					url  : '{{ route("loan.search.bank") }}',
					data : {'idUsers':id},
					success:function(data){
						$('.resultbank').html(data);
					},
					error: function(data)
					{
						$('.resultbank').html('');
						swal('','Sucedió un error, por favor intente de nuevo.','error');
					}
				}); 
			})
			.on('change','.js-enterprises',function()
			{
				$('.js-accounts').empty();
			})
			.on('click','#exit', function(){

				$(".formulario").slideToggle();
				$('#table').slideToggle();
				$('.resultbank').slideToggle();
			})
			.on('click','#save', function(){
				$('.remove').removeAttr('data-validation');
				$('.removeselect').removeAttr('required');
				$('.removeselect').removeAttr('data-validation');
				$('.request-validate').removeClass('request-validate');
			})
			.on('click','.checkbox',function()
			{
				$('.marktr').removeClass('marktr');
				$(this).parents('tr').addClass('marktr');
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
			.on('click','#help-btn-method-pay',function()
			{
				swal('Ayuda','En este apartado debe seleccionar la forma de pago, si usted selecciona "Cuenta Bancaria", deberá seleccionar una de las cuentas que se le muestran del "Solicitante", en caso de que no tenga cuenta, por favor solicite al "Solicitante" que agregue al menos una cuenta bancaria.','info');
			})
		</script>
	@endsection
@else
	@section('data')
		@component('components.forms.form', [ "attributeEx" => "method=\"POST\" id=\"container-alta\" action=\"".route('loan.store')."\""])
			@component('components.labels.title-divisor') Nueva solicitud @endcomponent
			@component('components.containers.container-form')
				<div class="col-span-2">
					@component('components.labels.label') Título: @endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							type="text" name="title" placeholder="Ingrese el título" data-validation="required"
						@endslot
						@slot('classEx')
							removeselect
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Fecha: @endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							type="text" name="datetitle" data-validation="required" placeholder="Ingrese la fecha" readonly="readonly"
						@endslot
						@slot('classEx')
							removeselect datepicker
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Solicitante: @endcomponent
					@component('components.inputs.select', ["options" => []])
						@slot('attributeEx')
							name="user_id" multiple="multiple" id="multiple-users" data-validation="required"
						@endslot
						@slot('classEx')
							js-users removeselect
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Empresa: @endcomponent
					@php
						$optionEnterprise = [];
						foreach($enterprises as $enterprise)
						{
							$optionEnterprise[] = ["value" => $enterprise->id, "description" => strlen($enterprise->name) >= 35 ? substr(strip_tags($enterprise->name),0,35).'...' : $enterprise->name];
						}
					@endphp
					@component('components.inputs.select', ["options" => $optionEnterprise])
						@slot('attributeEx')
							name="enterprise_id" multiple="multiple" id="multiple-enterprises" data-validation="required"
						@endslot
						@slot('classEx')
							js-enterprises removeselect
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Area: @endcomponent
					@php
						$optionArea = [];
						foreach($areas as $area)
						{
							$optionArea[] = ["value" => $area->id, "description" => $area->name];
						}
					@endphp
					@component('components.inputs.select', ["options" => $optionArea])
						@slot('attributeEx')
							multiple="multiple" name="area_id" id="multiple-areas" data-validation="required"
						@endslot
						@slot('classEx')
							js-areas removeselect
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Departamento: @endcomponent
					@php
						$optionDepartment = [];
						foreach($departments as $department)
						{
							$optionDepartment[] = ["value" => $department->id, "description" => $department->name];
						}
					@endphp
					@component('components.inputs.select', ["options" => $optionDepartment])
						@slot('attributeEx')
							multiple="multiple" name="department_id" id="multiple-departments" data-validation="required"
						@endslot
						@slot('classEx')
							js-departments removeselect
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Clasificación del gasto: @endcomponent
					@component('components.inputs.select', ["options" => []])
						@slot('attributeEx')
							multiple="multiple" name="account_id" data-validation="required"
						@endslot
						@slot('classEx')
							js-accounts removeselect
						@endslot
					@endcomponent
				</div>
			@endcomponent
			@component('components.labels.title-divisor') FORMA DE PAGO <span class="help-btn" id="help-btn-method-pay"></span> @endcomponent
			@php
				$buttons = 
				[
					[
						"textButton" 		=> "Cuenta Bancaria",
						"attributeButton" 	=> "type=\"radio\" name=\"method\" value=\"1\" id=\"accountBank\"",
					],
					[
						"textButton" 		=> "Efectivo",
						"attributeButton" 	=> "type=\"radio\" name=\"method\" value=\"2\" id=\"cash\" checked",
					],
					[
						"textButton" 		=> "Cheque",
						"attributeButton" 	=> "type=\"radio\" name=\"method\" value=\"3\" id=\"checks\"",
					],							
				];
			@endphp
			@component("components.buttons.buttons-pay-method", ["buttons" => $buttons]) @endcomponent
			<div class="resultbank table-responsive hidden"></div>
			@component('components.containers.container-form')
				<div class="col-span-2">
					@component('components.labels.label') Referencia: @endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							type="text" name="reference" placeholder="Ingrese la referencia"
						@endslot
						@slot('classEx')
							remove request-validate
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Importe: @endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							type="text" name="amount" placeholder="Ingrese el importe" data-validation="required"
						@endslot
						@slot('classEx')
							amount remove request-validate
						@endslot
					@endcomponent
				</div>
			@endcomponent
			<div class="w-full mt-4 grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mb-6">
				@component('components.buttons.button', [
						"varian" => "primary"
					])
					@slot('attributeEx')
						type="submit" name="enviar"
					@endslot
					@slot('classEx')
						text-center
						w-48
						md:w-auto
					@endslot
						ENVIAR SOLICITUD	
				@endcomponent
				@component('components.buttons.button', [
						"variant" => "secondary"
					])
					@slot('attributeEx')
						type="submit" name="save" formaction="{{ route('loan.unsent') }}" id="save"
					@endslot
					@slot('classEx')
						save
						text-center
						w-48
						md:w-auto
					@endslot
						GUARDAR SIN ENVIAR	 
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
		@endcomponent
	@endsection
	@section('scripts')
		<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
		<script src="{{ asset('js/jquery-ui.js') }}"></script>
		<script src="{{ asset('js/jquery.numeric.js') }}"></script>
		<script src="{{ asset('js/datepicker.js') }}"></script>
		<script>
			$(document).ready(function() 
			{
				@php
					$selects = collect([
						[
							"identificator"				=> ".js-enterprises",
							"placeholder"				=> "Seleccione la empresa",
							"maximumSelectionLength"	=> "1"
						],
						[
							"identificator"				=> ".js-areas",
							"placeholder"				=> "Seleccione la dirección",
							"maximumSelectionLength"	=> "1"
						],
						[
							"identificator"				=> ".js-departments",
							"placeholder"				=> "Seleccione el departamento",
							"maximumSelectionLength"	=> "1"
						],
					]);
				@endphp
				@component('components.scripts.selects',["selects" => $selects]) @endcomponent
				generalSelect({'selector':'.js-users', 'model':13});
				generalSelect({'selector':'.js-accounts', 'depends':'.js-enterprises', 'model':11});
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
							check 		=  $('.checkbox:checked').length;
							method 		= $('input[name="method"]:checked').val();
							amount		= $('input[name="amount"]').val();
							if(amount == 0)
							{
								swal('', 'El importe no puede ser 0', 'error');
								return false;
							}
							if(method != undefined)
							{
								if (method == 1) 
								{
									if (check>0) 
									{
										swal('Cargando',{
											icon : '{{ url(getenv('LOADING_IMG')) }}',
											button: false,
											closeOnClickOutside: false,
											closeOnEsc: false
										});
										return true;
									}
									else
									{
										swal('', 'Debe seleccionar un cuenta', 'error');
										return false;
									}
								}
								else
								{
									swal('Cargando',{
										icon : '{{ url(getenv('LOADING_IMG')) }}',
										button: false,
										closeOnClickOutside: false,
										closeOnEsc: false
									});
									return true;
								}					
							}
							else if (method == undefined) 
							{
								swal('', 'Debe seleccionar un método de pago', 'error');
								return false;
							}
						}
						else
						{
							swal('Cargando',{
								icon : '{{ url(getenv('LOADING_IMG')) }}',
								button: false,
								closeOnClickOutside: false,
								closeOnEsc: false
							});
							return true;
						}
					}
				});
				$('.card_number,.destination_account,.destination_key,.employee_number,.amount').numeric(false);    // números
				$('.amount,.importe',).numeric({ altDecimal: ".", decimalPlaces: 2 });
				$('.amount').numeric({ negative : false });
				$(function() 
				{
					$( ".datepicker" ).datepicker({ dateFormat: "dd-mm-yy" });
				});
			});
			$(document).on('change', '.js-users', function()
			{
				id 		= $(this).val();
				folio 	= $('#id'+id).text();

				$('#efolio').val(folio);
				$text = $('#efolio').val();
				$.ajax({
					type : 'post',
					url  : '{{ route("loan.search.bank") }}',
					data : {'idUsers':id},
					success:function(data)
					{
						$('.resultbank').html(data);
					},
					error: function(data)
					{
						$('.resultbank').html('');
						swal('','Lo sentimos ocurrió un error en la conexión, por favor intente de nuevo.','error');
					}
				}); 
			})
			.on('change','.js-enterprises',function()
			{
				$('.js-accounts').empty();
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
						$('.resultbank').hide();
						$('#table-provider').stop().hide();
						$('.removeselect').val(null).trigger('change');
						form[0].reset();
					}
					else
					{
						swal.close();
					}
				});
			})
			.on('click','#exit', function(){

				$(".formulario").slideToggle();
				$('#table').slideToggle();
				$('.resultbank').slideToggle();
			})
			.on('click','#save', function(){
				$('.remove').removeAttr('data-validation');
				$('.removeselect').removeAttr('required');
				$('.removeselect').removeAttr('data-validation');
				$('.request-validate').removeClass('request-validate');
				$('.input-search').removeClass('input-search');
			})
			.on('click','.checkbox',function()
			{
				$('.marktr').removeClass('marktr');
				$(this).parents('.tr_bank').addClass('marktr');
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
			.on('click','#help-btn-method-pay',function()
			{
				swal('Ayuda','En este apartado debe seleccionar la forma de pago, si usted selecciona "Cuenta Bancaria", deberá seleccionar una de las cuentas que se le muestran del "Solicitante", en caso de que no tenga cuenta, por favor solicite al "Solicitante" que agregue al menos una cuenta bancaria.','info');
			});
		</script>
	@endsection
@endif
