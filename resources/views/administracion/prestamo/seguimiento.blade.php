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
	@component('components.forms.form', [ "attributeEx" => "method=\"POST\" id=\"container-alta\" action=\"".route('loan.follow.update', $request->folio)."\"", "methodEx" => "PUT"])
		@component('components.labels.title-divisor') Folio: {{ $request->folio }} @endcomponent
		@component('components.labels.subtitle')
			Elaborado por: {{ $request->elaborateUser->fullName() }}
		@endcomponent
		@component('components.containers.container-form')
			<div class="col-span-2">
				@component('components.labels.label') Título: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" 
						name="title"
						placeholder="Ingrese el título"
						data-validation="required"
						@if(isset($request)) value="{{ $request->loan->first()->title }}" @endif
						@if($request->status!=2) disabled="disabled" @endif
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
					@slot('attributeEx')
						type="text"
						name="datetitle"
						data-validation="required"
						placeholder="Ingrese la fecha"
						readonly="readonly"
						@if(isset($request)) value="{{ $dateTitle }}" @endif
						@if($request->status!=2) disabled="disabled" @endif
					@endslot
					@slot('classEx')
						removeselect datepicker
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Solicitante: @endcomponent
				@php
					$optionUser = [];
					$user		= App\User::find($request->idRequest);
					if(isset($request) && $user != "" && $request->idRequest == $user->id)
					{
						$optionUser[] = ["value" => $user->id, "description" => $request->requestUser->fullName(), "selected" => "selected"];
					}
				@endphp
				@component('components.inputs.select', ["options" => $optionUser])
					@slot('attributeEx')
						name="user_id"
						multiple="multiple"
						id="multiple-users"
						data-validation="required"
						@if($request->status != 2) disabled="disabled" @endif
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
				@component('components.inputs.select', ["options" => $optionEnterprise])
					@slot('attributeEx')
						name="enterprise_id"
						multiple="multiple"
						id="multiple-enterprises"
						data-validation="required"
						@if($request->status != 2) disabled="disabled" @endif
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
				@component('components.inputs.select', ["options" => $optionArea])
					@slot('attributeEx')
						multiple="multiple" name="area_id" id="multiple-areas" data-validation="required" @if($request->status != 2) disabled="disabled" @endif
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
				@component('components.inputs.select', ["options" => $optionDepartment])
					@slot('attributeEx')
						multiple="multiple" name="department_id" id="multiple-departments" data-validation="required" @if($request->status != 2) disabled="disabled" @endif
					@endslot
					@slot('classEx')
						js-departments removeselect
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Clasificación del gasto: @endcomponent
				@php
					$optionC = [];
					foreach(App\Account::where('selectable',1)->where('idEnterprise',$request->idEnterprise)->where('description','FUNCIONARIOS Y EMPLEADOS')->get() as $account)
					{
						if($request->account == $account->idAccAcc)
						{
							$optionC[] = ["value" => $account->idAccAcc, "description" => $account->account.' '.$account->description.' '.'('.$account->content.')', "selected" => "selected"];
						}
						else
						{
							$optionC[] = ["value" => $account->idAccAcc, "description" => $account->account.' '.$account->description.' '.'('.$account->content.')'];
						}
					}
				@endphp
				@component('components.inputs.select', ["options" => $optionC])
					@slot('attributeEx')
						multiple="multiple" name="account_id" data-validation="required" @if($request->status != 2) disabled="disabled" @endif
					@endslot
					@slot('classEx')
						js-accounts removeselect
					@endslot
				@endcomponent
			</div>
		@endcomponent
		@component('components.labels.title-divisor') FORMA DE PAGO <span class="help-btn" id="help-btn-method-pay"></span> @endcomponent
		@php
			$disabled = (isset($globalRequests) || $request->status != 2) ? " disabled=\"disabled\"" : ""; 
			$buttons = 
			[
				[
					"textButton" 		=> "Cuenta Bancaria",
					"attributeButton" 	=> "type=\"radio\" name=\"method\" value=\"1\" id=\"accountBank\"".($request->loan->first()->idpaymentMethod == 1 ? " checked" : "").$disabled,
				],
				[
					"textButton" 		=> "Efectivo",
					"attributeButton" 	=> "type=\"radio\" name=\"method\" value=\"2\" id=\"cash\"".($request->loan->first()->idpaymentMethod == 2 ? " checked" : "").$disabled,
				],
				[
					"textButton" 		=> "Cheque",
					"attributeButton" 	=> "type=\"radio\" name=\"method\" value=\"3\" id=\"checks\"".($request->loan->first()->idpaymentMethod == 3 ? " checked" : "").$disabled,
				],							
			];
		@endphp
		@component("components.buttons.buttons-pay-method", ["buttons" => $buttons]) @endcomponent
		@if($request->status != 2)
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="hidden" name="employee_number" id="efolio" placeholder="Número de empleado" value="@foreach($request->loan as $loan){{ $loan->idUsers }}@endforeach"
				@endslot
				@slot('classEx')
					employee_number
				@endslot
			@endcomponent
			<div class="resultbank @if($request->loan->first()->idpaymentMethod == 1) block @else hidden @endif">
				@component('components.labels.title-divisor') CUENTA @endcomponent
				@php
					$body		= [];
					$modelBody	= [];
					$modelHead	= 
					[
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
							if($loan->idEmployee == $bank->idEmployee)
							{
								$disabeldBank = '';
								if(isset($globalRequests))
								{
									$disabeldBank = " disabled";
								}

								$body = 
								[
									[
										"content" =>
										[
											"kind"				=> "components.inputs.checkbox",
											"classEx"			=> "checkbox",
											"attributeEx"		=> "name=\"idEmployee\" checked=\"checked\" id=\"id".$bank->idEmployee."\"".' '."value=\"".$bank->idEmployee."\" ".$disabled,
											"classExLabel"		=> "request-validate".$disabeldBank,
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
											"label" => $bank->alias!=null ? $bank->alias : '---'
										]
									],
									[
										"content" =>
										[
											"label" => $bank->cardNumber!=null ? $bank->cardNumber : '---'
										]
									],
									[
										"content" =>
										[
											"label" => $bank->clabe!=null ? $bank->clabe : '---'
										]
									],
									[
										"content" =>
										[
											"label" => $bank->account!=null ? $bank->account : '---'
										]
									]
								];
								$modelBody[] = $body;
							}
						}
					}
				@endphp
				@component('components.tables.table',[
					'modelBody' => $modelBody,
					'modelHead' => $modelHead
				])
					@slot('attributeEx')
						id="table2"
					@endslot
					@slot('classExBody')
						request-validate
					@endslot					
				@endcomponent
			</div>
		@else
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="hidden" name="employee_number" id="efolio" placeholder="Número de empleado" value="@foreach($request->loan as $loan){{ $loan->idUsers }}@endforeach"
				@endslot
				@slot('classEx')
					employee_number
				@endslot
			@endcomponent
			<div class="resultbank @if($request->loan->first()->idpaymentMethod == 1) block @else hidden @endif">
				@foreach($request->loan as $loan)
					@if(App\Employee::join('banks','employees.idBanks','banks.idBanks')->where('employees.idUsers',$loan->idUsers)->get() != "")
						@component('components.labels.title-divisor') SELECCIONE UNA CUENTA @endcomponent
						@php
							$body		= [];
							$modelBody	= [];
							$modelHead	= 
							[
								[
									["value" => "Acción"],
									["value" => "Banco"],
									["value" => "Alias"],
									["value" => "Número de tarjeta"],
									["value" => "CLABE"],
									["value" => "Número de cuenta"]
								]
							];
							$disabled = (isset($globalRequests) || $request->status != 2) ? " disabled=\"disabled\"" : ""; 
							foreach($request->loan as $loan)
							{
								foreach(App\Employee::join('banks','employees.idBanks','banks.idBanks')->where('employees.idUsers',$loan->idUsers)->where('visible',1)->get() as $bank)
								{
									$classMrktr = '';
									$checked	= "";
									if($loan->idEmployee == $bank->idEmployee)
									{
										$classMrktr = "marktr";
										$checked 	= "checked";
									}
									$disabeldBank = '';
									if(isset($globalRequests))
									{
										$disabeldBank = "disabled";
									}

									$body =
									[ "classEx" => $classMrktr,
										[
											"content" =>
											[
												"kind"				=> "components.inputs.checkbox",
												"classEx"			=> "checkbox",
												"attributeEx"		=> "name=\"idEmployee\" id=\"id".$bank->idEmployee."\"".' '."value=\"".$bank->idEmployee."\"".' '.$checked.' '.$disabeldBank.' '.$disabled,
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
												"label" => $bank->alias!=null ? $bank->alias : '---'
											]
										],
										[
											"content" =>
											[
												"label" => $bank->cardNumber!=null ? $bank->cardNumber : '---'
											]
										],
										[
											"content" =>
											[
												"label" => $bank->clabe!=null ? $bank->clabe : '---' 
											]
										],
										[
											"content" =>
											[
												"label" => $bank->account!=null ? $bank->account : '---'  
											]
										]
									];
									$modelBody[] = $body;
								}
							}
						@endphp
						@component('components.tables.table',[
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
					@endif
				@endforeach
			</div>
		@endif
		<div class="form-container">
			@if(empty($request->loan[0]))
				@component('components.containers.container-form')
					<div class="col-span-2">
						@component('components.labels.label') Referencia: @endcomponent
						@component('components.inputs.input-text')
							@slot('attributeEx')
								type="text" name="reference" placeholder="Ingrese la referencia" @if($request->status != 2) readonly="readonly" @endif @if(isset($globalRequests)) disabled @endif
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
								type="text" name="amount" placeholder="Ingrese el importe" @if($request->status == 2) data-validation="required" @endif @if($request->status != 2) readonly="readonly" @endif @if(isset($globalRequests)) disabled @endif
							@endslot
							@slot('classEx')
								remove request-validate importe
							@endslot
						@endcomponent
					</div>
				@endcomponent
			@else
				@foreach($request->loan as $loan)
					@component('components.containers.container-form')
						<div class="col-span-2">
							@component('components.labels.label') Referencia: @endcomponent
							@component('components.inputs.input-text')
								@slot('attributeEx')
									type="text" name="reference" placeholder="Ingrese la referencia" @if($request->status != 2) readonly="readonly" @endif value="{{ $loan->reference }}" @if(isset($globalRequests)) disabled @endif
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
									type="text" name="amount" placeholder="Ingrese el importe" @if($request->status == 2) data-validation="required" @endif @if($request->status != 2) readonly="readonly" @endif value="{{ $loan->amount,2 }}" @if(isset($globalRequests)) disabled @endif
								@endslot
								@slot('classEx')
									remove request-validate importe
								@endslot
							@endcomponent
						</div>
					@endcomponent
				@endforeach
			@endif
		</div>
		@foreach($request->loan as $loan)
			@if($loan->path != "")
				 <div class="bg-warm-gray-100 text-center p-4 flex">
					 @component('components.labels.label') 
						@slot('classEx')
							text-black
						@endslot
						Documento de autorización:
					@endcomponent
					 @component('components.buttons.button',["variant" => "dark-red"])
						 @slot('buttonElement')
							a
						 @endslot
						 @slot('attributeEx')
							target="_blank"
							title="{{ $loan->path }}"
							href="{{ asset('docs/loan/'.$loan->path) }}"
						 @endslot
						 @slot('slot')
							PDF
						 @endslot
					 @endcomponent
					 @component('components.inputs.input-text')
						 @slot('attributeEx')
							type="hidden" class="num_path" name="old_path" value="{{ $loan->path }}"
						 @endslot
					 @endcomponent
				 </div>
			@endif
		@endforeach
		@if($request->idCheck != "")
			@component('components.labels.title-divisor') DATOS DE REVISIÓN @endcomponent
			@php
				$nameEnterprise	= '';
				$nameDirection 	= '';
				$nameDepartment = '';
				$varAccount		= '';
				$varDescription = '';
				$varComment 	= '';
				if($request->idEnterpriseR!="")
				{
					$nameEnterprise = App\Enterprise::find($request->idEnterpriseR)->name;
					$nameDirection	= $request->reviewedDirection->name;
					$nameDepartment	= App\Department::find($request->idDepartamentR)->name;
					$reviewAccount = App\Account::find($request->accountR);
					if(isset($reviewAccount->account))
					{
						$varAccount = $reviewAccount->account.' '.$reviewAccount->description;
					}
					else
					{
						$varAccount	= "No hay";
					}
					if (count($request->labels))
					{
						foreach($request->labels as $label)
						{
							$varDescription .= $label->description.', ';
						}
					}
					else
					{
						$varDescription = "Sin etiqueta";
					}
				}

				$modelTable = [
					"Revisó"					=> $request->reviewedUser->name.' '.$request->reviewedUser->last_name.' '.$request->reviewedUser->scnd_last_name,
					"Nombre de la Empresa"		=> $nameEnterprise 	!= '' ? $nameEnterprise : '---',
					"Nombre de la Dirección"	=> $nameDirection 	!= '' ? $nameDirection 	: '---',
					"Nombre del Departamento"	=> $nameDepartment 	!= '' ? $nameDepartment : '---',
					"Clasificación del gasto"	=> $varAccount 		!= '' ? $varAccount 	: '---',
					"Etiquetas"					=> $varDescription 	!= '' ? $varDescription : '---',
					"Comentarios"				=> $request->checkComment == "" ? "Sin comentarios" : htmlentities($request->checkComment),
				];
			@endphp
			@component('components.templates.outputs.table-detail-single',['modelTable' => $modelTable]) @endcomponent
		@endif
		@if($request->idAuthorize != "")
			@component('components.labels.title-divisor') DATOS DE AUTORIZACIÓN @endcomponent
			@php
				$modelTable = [
					"Autorizó" 		=> $request->authorizedUser->name.' '.$request->authorizedUser->last_name.' '.$request->authorizedUser->scnd_last_name,
					"Comentarios"	=> $request->authorizeComment == "" ? "Sin comentarios" : htmlentities($request->authorizeComment),
				];
			@endphp
			@component('components.templates.outputs.table-detail-single',["modelTable" => $modelTable]) @endcomponent
		@endif
		@if($request->status == 13)
			@component('components.labels.title-divisor') DATOS DE PAGOS @endcomponent
			@php
				$modelTable = [
					"Comentarios" => $request->paymentComment == "" ? "Sin comentarios" : $request->paymentComment
				];	
			@endphp
			@component('components.templates.outputs.table-detail-single', ["modelTable" => $modelTable]) @endcomponent
		@endif
		@php
			$payments 		= App\Payment::where('idFolio',$request->folio)->get();
			$totalPagado 	= 0;
		@endphp
		@if(count($payments) > 0)
			@php
				$total = $request->loan->first()->amount;
			@endphp
			@component('components.labels.title-divisor') HISTORIAL DE PAGOS @endcomponent
			@php
				$body		= [];
				$modelBody	= [];
				$modelHead	= 
				[
					[
						["value" => "Cuenta"],
						["value" => "Cantidad"],
						["value" => "Documento"],
						["value" => "Fecha"]
					]
				];
				foreach($payments as $pay)
				{
					$body =
					[
						[
							"content" =>
							[
								"label" => $pay->accounts->account.' - '.$pay->accounts->description
							]
						],
						[
							"content" =>
							[
								"label" => '$ '.number_format($pay->amount,2)
							]
						]
					];
					$docsPayments = '';
					if($pay->documentsPayments()->exists())
					{
						foreach($pay->documentsPayments as $doc)
						{
							$docsPayments .= '<div class="content">';
							$docsPayments .= view('components.buttons.button',[
								"variant"		=> "dark-red",
								"buttonElement" => "a",
								"attributeEx"	=> "target=\"_blank\" type=\"button\" title=\"".$doc->path."\"".' '."href=\"".asset('docs/payments/'.$doc->path)."\"",
								"label"			=> 'PDF'
							])->render();
							$docsPayments .= "</div>";
						}
					}
					else 
					{
						$docsPayments = "Sin documento";
					}
					$body[] = [ "content" => [ "label" => $docsPayments ]];
					$body[] = 
					[	
						"content" =>
						[
							"label" => Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$pay->paymentDate)->format('d-m-Y')
						]
					];
					$totalPagado += $pay->amount;
					$modelBody[] = $body;
				}
			@endphp
			@component('components.tables.table', [
				"modelBody" => $modelBody,
				"modelHead" => $modelHead
			])
			@endcomponent
			@php
				$modelTable = [
					[
						"label" => "Total pagado:", "inputsEx" =>
						[
							[
								"kind" 		=> "components.labels.label",
								"label" 	=> "$ ".number_format($totalPagado,2),
								"classEx" 	=> "my-2"
							],
							[
								"kind"			=> "components.inputs.input-text",
								"attributeEx" 	=> "type=\"hidden\" value=\"".number_format($totalPagado,2)."\""
							]
						]
					],
					[
						"label" => "Resta por pagar:", "inputsEx" =>
						[
							[
								"kind" 		=> "components.labels.label",
								"label" 	=> "$ ".number_format($total-$totalPagado,2),
								"classEx" 	=> "my-2"
							],
							[
								"kind"			=> "components.inputs.input-text",
								"attributeEx" 	=> "type=\"hidden\" value=\"".number_format($total-$totalPagado,2)."\""
							]
						]
					]
				]
			@endphp
			@component('components.templates.outputs.form-details', ["modelTable" => $modelTable]) @endcomponent
		@endif
		<div class="w-full mt-4 grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mb-6">
			@if($request->status == "2")
				@component('components.buttons.button', ["variant" => "primary"])
					@slot('attributeEx')
						type="submit" name="enviar"
					@endslot
					ENVIAR SOLICITUD
				@endcomponent
				@component('components.buttons.button', ["variant" => "secondary"])
					@slot('attributeEx')
						type="submit" id="save" name="save" formaction="{{ route('loan.follow.updateunsent', $request->folio) }}"
					@endslot
					@slot('classEx')
						save
					@endslot
					GUARDAR SIN ENVIAR
				@endcomponent
			@endif
			@component('components.buttons.button', [ "buttonElement" => "a", "variant" => "reset"])
				@slot("attributeEx")
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
										icon: '{{ url(getenv('LOADING_IMG')) }}',
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
									icon: '{{ url(getenv('LOADING_IMG')) }}',
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
							icon: '{{ url(getenv('LOADING_IMG')) }}',
							button: false,
							closeOnClickOutside: false,
							closeOnEsc: false
						});
						return true;
					}
				}
			});
			$('.card_number,.destination_account,.destination_key,.employee_number').numeric(false);    // números
			$('.amount,.importe',).numeric({ altDecimal: ".", decimalPlaces: 2 });
			$('.importe').numeric({ negative : false });
			$(function() 
			{
				$( ".datepicker" ).datepicker({ dateFormat: "dd-mm-yy" });
			});
		});
		$(document).on('change', '.js-users', function(){
			id 		= $(this).val();
			folio 	= $('#id'+id).text();

			$('#efolio').val(folio);
			$('.resultbank').stop().show();
			$text = $('#efolio').val();
			$.ajax({
				type : 'post',
				url  : '{{ route("loan.search.bank") }}',
				data : {'idUsers':id},
				success:function(data)
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
		.on('change','.js-enterprises',function()
		{
			generalSelect({'selector':'.js-accounts', 'depends':'.js-enterprises', 'model':11});
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
		});
	</script>
@endsection
