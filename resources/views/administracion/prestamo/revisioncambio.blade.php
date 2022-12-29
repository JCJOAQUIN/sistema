@extends('layouts.child_module')
@section('data')
	<div class="sm:text-center text-left my-5">
		A continuación podrá verificar la información de la solicitud antes de continuar con el proceso:
	</div>
	@php
		$modelTable = [
			["Folio:", 						$request->folio],
			["Título y fecha:", 			htmlentities($request->loan->first()->title).'  '.Carbon\Carbon::createFromFormat('Y-m-d',$request->loan->first()->datetitle)->format('d-m-Y')],
			["Solicitante:",				$request->requestUser->fullName()],
			["Elaborado por:",				$request->elaborateUser->fullName()],
			["Empresa:",					$request->requestEnterprise->name],
			["Dirección:", 					$request->requestDirection->name],
			["Departamento:", 				$request->requestDepartment->name],
			["Clasificación del gasto:",	$request->accounts->account.' - '.$request->accounts->description]
		];
	@endphp
	@component('components.templates.outputs.table-detail',
		[
			"modelTable"	=> $modelTable,
			"title"			=> "Detalles de la Solicitud"
		])
	@endcomponent
	@component('components.labels.title-divisor') DATOS DEL SOLICITANTE @endcomponent
	@php
		$valueLoan 		= '';
		$valueReference	= '';
		$valueAmount	= '';
		foreach($request->loan as $loan)
		{
			$valueLoan 		= isset($loan->paymentMethod->method) ? $loan->paymentMethod->method : 'No hay';
			$valueReference = ($loan->reference != "" ? htmlentities($loan->reference) : "---");
			$valueAmount	= '$ '.number_format($loan->amount,2);
		}
		$valueBank 		= '';
		$valueAlias		= '';
		$valueCard		= '';
		$valueClabe 	= '';
		$valueAccount	= '';
		foreach($request->loan as $loan)
		{
			foreach(App\Employee::join('banks','employees.idBanks','banks.idBanks')->where('employees.idUsers',$loan->idUsers)->get() as $bank)
			{
				if($loan->idEmployee == $bank->idEmployee)
				{
					$valueBank 		= $bank->description;
					$valueAlias		= $bank->alias!=null ? $bank->alias : '---';
					$valueCard		= $bank->cardNumber!=null ? $bank->cardNumber : '---';
					$valueClabe 	= $bank->clabe!=null ? $bank->clabe : '---';
					$valueAccount	= $bank->account!=null ? $bank->account : '---';
				}
			}
		}

		$modelTable = [
			"Nombre"			=> $request->requestUser->fullName(),
			"Forma de pago"		=> $valueLoan,
			"Referencia"		=> $valueReference,
			"Importe"			=> $valueAmount,
			"Banco"				=> $valueBank,
			"Alias"				=> $valueAlias,
			"Número de tarjeta"	=> $valueCard,
			"CLABE"				=> $valueClabe,
			"Número de cuenta"	=> $valueAccount
		];
	@endphp
	@component('components.templates.outputs.table-detail-single', ["modelTable" => $modelTable]) @endcomponent
	@component('components.inputs.input-text')
		@slot('attributeEx')
			type="hidden" name="employee_number" id="efolio" placeholder="Número de empleado" value="@foreach($request->loan as $loan){{ $loan->idUsers }}@endforeach"
		@endslot
		@slot('classEx')
			employee_number
		@endslot
	@endcomponent
	@component('components.forms.form', [ "attributeEx" => "method=\"POST\" id=\"container-alta\" action=\"".route('loan.review.update', $request->folio)."\"", "methodEx" => "PUT"])
		<div class="my-4">
			@component('components.containers.container-approval')
				@slot('attributeExButton')
					name="status" id="aprobar" value="4"
				@endslot
				@slot('classExButton')
					approve
				@endslot
				@slot('attributeExButtonTwo')
					name="status" id="rechazar" value="6"
				@endslot
				@slot('classExButtonTwo')
					refuse
				@endslot
			@endcomponent
		</div>
		<div  id="aceptar" class="hidden mt-4">
			@component('components.containers.container-form')
				<div class="col-span-2">
					@component('components.labels.label') Empresa: @endcomponent
					@php
						$optionEnterprise = [];
						foreach(App\Enterprise::orderName()->where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeEnt($option_id)->pluck('enterprise_id'))->get() as $enterprise)
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
							id="multiple-enterprisesR" name="idEnterpriseR" multiple="multiple" data-validation="required"
						@endslot
						@slot('classEx')
							js-enterprisesR
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Area: @endcomponent
					@php
						$optionArea = [];
						foreach(App\Area::orderName()->where('status','ACTIVE')->get() as $area)
						{
							if($request->idArea == $area->id)
							{
								$optionArea[] = ["value" => $area->id, "description" => $area->name, "selected" => "selected"];
							}
							else
							{
								$optionArea[] = ["value" => $area->id, "description" => $area->name ];
							}
						}
					@endphp
					@component('components.inputs.select', ['options' => $optionArea])
						@slot('attributeEx')
							id="multiple-areasR" multiple="multiple" name="idAreaR" data-validation="required"
						@endslot
						@slot('classEx')
							js-areasR
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Departamento: @endcomponent
					@php
						$optionDepartment = [];
						foreach(App\Department::orderName()->where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeDep($option_id)->pluck('departament_id'))->get() as $department)
						{
							if($request->idDepartment == $department->id)
							{
								$optionDepartment[] = ["value" => $department->id, "description" => $department->name, "selected" => "selected"];
							}
							else
							{
								$optionDepartment[] = ["value" => $department->id, "description" => $department->name ];
							}
						}
					@endphp
					@component('components.inputs.select', ['options' => $optionDepartment])
						@slot('attributeEx')
							id="multiple-departmentsR" multiple="multiple" name="idDepartmentR" data-validation="required"
						@endslot
						@slot('classEx')
							js-departmentsR
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Cuenta: @endcomponent
					@php
						$optionAccount 	= [];
						$account 		= App\Account::where('idEnterprise',$request->idEnterprise)->where('selectable',1)->where('idAccAcc',$request->account)->first();
						if($request->account == $account->idAccAcc)
						{
							$optionAccount[] = ["value" => $account->idAccAcc, "description" => $account->account.' - '.$account->description, "selected" => "selected"];
						}
					@endphp
					@component('components.inputs.select', ['options' => $optionAccount])
						@slot('attributeEx')
							id="multiple-accountsR" multiple="multiple" name="accountR" data-validation="required"
						@endslot
						@slot('classEx')
							js-accountsR removeselect
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Etiquetas: @endcomponent
					@component('components.inputs.select', ['options' => []])
						@slot('attributeEx')
							id="multiple-labels" multiple="multiple" name="idLabels[]"
						@endslot
						@slot('classEx')
							js-labelsR
						@endslot
					@endcomponent
				</div>
			@endcomponent
			@component('components.labels.label') Comentarios (opcional): @endcomponent
			@component('components.inputs.text-area')
				@slot('attributeEx')
					cols="90" rows="4" name="checkCommentA"
				@endslot
				@slot('classEx')
					text-area
				@endslot
			@endcomponent
		</div>
		<div id="rechaza" class="hidden">
			<div class="mb-4">
				@component('components.labels.label') Comentarios (opcional): @endcomponent
				@component('components.inputs.text-area')
					@slot('attributeEx')
						cols="90" rows="4" name="checkCommentR"
					@endslot
					@slot('classEx')
						text-area
					@endslot
				@endcomponent
			</div>
		</div>
		<div class="w-full mt-4 grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mb-6">
			@component('components.buttons.button', [ "variant" => "primary" ])
				@slot('attributeEx')
					type="submit"
					name="enviar"
				@endslot
				@slot('classEx')
					text-center
					w-48
					md:w-auto
				@endslot
					ENVIAR SOLICITUD
			@endcomponent
			@component('components.buttons.button', ["variant"=>"reset", "buttonElement"=>"a"])
				@slot('classEx')
					load-actioner
					text-center
					w-48
					md:w-auto
				@endslot
				@slot('attributeEx')
					@if(isset($option_id)) 
						href="{{ url(getUrlRedirect($option_id)) }}" 
					@else 
						href="{{ url(getUrlRedirect($child_id)) }}"
					@endif
				@endslot
				REGRESAR
			@endcomponent
		</div>
	@endcomponent
@endsection

@section('scripts')
	<script src="{{ asset('js/jquery-ui.js') }}"></script>
	<script src="{{ asset('js/jquery.numeric.js') }}"></script>
	<script>
		$(document).ready(function()
		{
			$.validate(
			{
				form: '#container-alta',
				modules		: 'security',
				onError		: function($form)
				{
					swal('', '{{ Lang::get("messages.form_error") }}', 'error');
				},
				onSuccess : function($form)
				{
					if($('input[name="status"]').is(':checked'))
					{
						if($('input#aprobar').is(':checked'))
						{
							enterprise	= $('#multiple-enterprisesR').val();
							area		= $('#multiple-areasR').val();
							department	= $('#multiple-departmentsR').val();
							labels		= $('#multiple-labels').val();
							account		= $('#multiple-accountsR').val();
							if(enterprise == '' || area == '' || department == '' || account == '')
							{
								swal('', 'Todos los campos son requeridos', 'error');
								return false;
							}
							else
							{
								swal('Cargando',{
									icon: '{{ url(getenv('LOADING_IMG')) }}',
									button: false,
								});
								return true;
							}
						}
						else
						{
							swal('Cargando',{
								icon: '{{ url(getenv('LOADING_IMG')) }}',
								button: false,
							});
							return true;
						}
					}
					else
					{
						swal('', 'Debe seleccionar al menos un estado', 'error');
						return false;
					}
				}
			});
			$(document).on('click', '.edit', function()
			{
				$('.resultbank').stop().show();
				id=$(this).val();
				folio=$('#id'+id).text();
				$('#efolio').val(folio);
				$text = $('#efolio').val();
				$.ajax({
					type : 'post',
					url  : '{{ route("loan.search.bank") }}',
					data : {'employee_number':$text},
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
			.on('click','#exit', function(){
				$(".formulario").slideToggle();
				$('#table').slideToggle();
				$('.resultbank').slideToggle();
			})
			.on('click','#save', function(){
				$('.remove').removeAttr('data-validation');
				$('.removeselect').removeAttr('required');
				$('.request-validate').removeClass('request-validate');
			})
			.on('click','.checkbox',function()
			{
				$('.marktr').removeClass('marktr');
				$(this).parents('tr').addClass('marktr');
			})
			.on('change','.js-enterprisesR',function()
			{
				generalSelect({'selector':'.js-accountsR', 'depends':'.js-enterprisesR', 'model':11});
				$('.js-accountsR').empty();
			})
			.on('keyup','#input-search', function()
			{
				$('.resultbank').stop().hide();
				$text = $(this).val();
				if ($text == "" || $text == " " || $text == "  " || $text == "   ")
				{
					$('#not-found').stop().show();
					$('#not-found').html("RESULTADO NO ENCONTRADO");
					$('#table').stop().hide();
				}
				else
				{
					$('#not-found').stop().hide();
					$.ajax({
						type : 'post',
						url  : '{{ route("loan.search.user") }}',
						data : {'search':$text},
						success:function(data){
							$('.result').html(data);
						},
						error: function(data)
						{
							$('.result').html('');
							swal('','Sucedió un error, por favor intente de nuevo.','error');
						}
					}); 
				}
			});
			$('.card_number,.destination_account,.destination_key,.employee_number').numeric(false);    // números
			$('.amount,.importe',).numeric({ altDecimal: ".", decimalPlaces: 2 });
			$('input[name="status"]').change(function()
			{
				if ($('input[name="status"]:checked').val() == "4") 
				{
					$("#rechaza").slideUp("slow");
					$("#aceptar").slideToggle("slow");
					@php
						$selects = collect([
							[
								"identificator"				=> ".js-projectsR",
								"placeholder"				=> "Seleccione el proyecto",
								"maximumSelectionLength"	=> "1"
							],
							[
								"identificator"				=> ".js-enterprisesR",
								"placeholder"				=> "Seleccione la empresa",
								"maximumSelectionLength"	=> "1"
							],
							[
								"identificator"				=> ".js-areasR",
								"placeholder"				=> "Seleccione la dirección",
								"maximumSelectionLength"	=> "1"
							],
							[
								"identificator"				=> ".js-departmentsR",
								"placeholder"				=> "Seleccione el departamento",
								"maximumSelectionLength"	=> "1"
							]
						]);
					@endphp
					@component('components.scripts.selects',["selects" => $selects]) @endcomponent
				}
				else if ($('input[name="status"]:checked').val() == "6") 
				{
					$("#aceptar").slideUp("slow");
					$("#rechaza").slideToggle("slow");
				}
				generalSelect({'selector':'.js-accountsR', 'depends':'.js-enterprisesR', 'model':11});
				generalSelect({'selector':'.js-labelsR', 'model': 19, 'maxSelection' : 15});
			});
		});
	</script>
@endsection