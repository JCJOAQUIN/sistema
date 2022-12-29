@extends('layouts.child_module')

@section('data')
	<div class="sm:text-center text-left my-5">
		A continuación podrá verificar la información de la solicitud antes de continuar con el proceso:
	</div>
	@php
		$requestUser	=	App\User::find($requests->idRequest);
		$elaborateUser	=	App\User::find($requests->idElaborate);
		$requestAccount	=	App\Account::find($requests->account);
		$modelTable	=
		[
			["Folio:",						$requests->folio],
			["Título y fecha:",				htmlentities($requests->finance->title)." - ".Carbon\Carbon::createFromFormat('Y-m-d',$requests->finance->datetitle)->format('d-m-Y')],
			["Fiscal:",						$requests->taxPayment == 1 ? "Si" : "No"],
			["Solicitante:",				$requestUser->name." ".$requestUser->last_name." ".$requestUser->scnd_last_name],
			["Elaborado por:",				$elaborateUser->name." ".$elaborateUser->last_name." ".$elaborateUser->scnd_last_name],
			["Empresa:",					App\Enterprise::find($requests->idEnterprise)->name],
			["Dirección:",					App\Area::find($requests->idArea)->name],
			["Departamento:",				App\Department::find($requests->idDepartment)->name],
			["Clasificación de gastos:",	$requestAccount->account." - ".$requestAccount->description." (".$requestAccount->content.")"],
			["Proyecto:",					isset(App\Project::find($requests->idProject)->proyectName) ? App\Project::find($requests->idProject)->proyectName : 'No se seleccionó proyecto'],
		];
	@endphp
	@component("components.templates.outputs.table-detail", ["modelTable" => $modelTable, "title" => "Detalles de la Solicitud"]) @endcomponent
	@component('components.labels.title-divisor') DATOS DEL GASTO FINANCIERO @endcomponent
	<div class="flex md:justify-center md:ml-20">
		@php
			$modelTable	=
			[
				"Tipo"				=>	$requests->finance->kind,
				"Fecha de pago"		=>	Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$requests->PaymentDate)->format('d-m-Y'),
				"Método de pago"	=>	$requests->finance->paymentMethod,
				"Banco"				=>	$requests->finance->banks->description,
				"Cuenta"			=>	$requests->finance->bankAccount()->exists() ? $requests->finance->bankAccount->alias.' - '.$requests->finance->bankAccount->account : '',
				"Tarjeta"			=>	$requests->finance->creditCard()->exists() ? $requests->finance->creditCard->alias.' - '.$requests->finance->creditCard->credit_card : '',
				"Moneda"			=>	$requests->finance->currency,
				"Notas"				=>	htmlentities($requests->finance->note),
				"Semana"			=>	$requests->finance->week
			];
		@endphp
		@component("components.templates.outputs.table-detail-single", ["modelTable" => $modelTable]) @endcomponent
	</div>
	@php
		$modelTable = 
		[
			["label"	=>	"Subtotal:",	"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"h-10 py-2",	"attributeEx"	=>	"id=\"subtotalLabel\"",	"label"	=>	"$ ".number_format($requests->finance->subtotal,2,".",",")]]],
			["label"	=>	"IVA:",			"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"h-10 py-2",	"attributeEx"	=>	"id=\"subtotalLabel\"",	"label"	=>	"$ ".number_format($requests->finance->tax,2,".",",")]]],
			["label"	=>	"TOTAL:",		"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"h-10 py-2",	"attributeEx"	=>	"id=\"subtotalLabel\"",	"label"	=>	"$ ".number_format($requests->finance->amount,2,".",",")]]]
		];
	@endphp
	@component("components.templates.outputs.form-details", ["modelTable" => $modelTable]) @endcomponent
	@if($action == 'review')
		@component('components.forms.form', ["attributeEx" => "method=\"POST\" id=\"container-alta\" action=\"".route('finance.review.update',$requests->folio)."\"", "methodEx" => "PUT"])
			<div class="text-center mt-8">
				<div class="form-container mt-12">
					<div class="flex justify-center">
						@component('components.labels.label')
							@slot('attributeEx')
								id="label-inline"
							@endslot
							¿Desea aprobar ó rechazar la solicitud?
						@endcomponent
					</div>
					@component('components.containers.container-approval')
						@slot('attributeExButton')
							name="status" id="aprobar" value="4"
						@endslot
						@slot('attributeExButtonTwo')
							id="rechazar" name="status" value="6"
						@endslot
					@endcomponent
				</div>
			</div>
			<div id="aceptar" class="hidden mt-4">
				@component('components.labels.title-divisor') ASIGNACIÓN DE ETIQUETAS @endcomponent
				@component("components.containers.container-form")
					<div class="col-span-2">
						@php
							$options	=	collect();
							foreach(App\Enterprise::orderName()->where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeEnt($option_id)->pluck('enterprise_id'))->get() as $enterprise)
							{
								if($requests->idEnterprise == $enterprise->id)
								{
									$options	=	$options->concat([["value" => $enterprise->id, "selected" => "selected", "description" => strlen($enterprise->name) >= 35 ? substr(strip_tags($enterprise->name),0,35).'...' : $enterprise->name]]);
								}
								else
								{
									$options	=	$options->concat([["value" => $enterprise->id, "description" => strlen($enterprise->name) >= 35 ? substr(strip_tags($enterprise->name),0,35).'...' : $enterprise->name]]);
								}
							}
							$classEx		=	"js-enterprisesR";
							$attributeEx	=	"id=\"multiple-enterprisesR\" name=\"idEnterpriseR\" data-validation=\"required\"";
						@endphp
						@component('components.labels.label') Empresa: @endcomponent
						@component("components.inputs.select", ["options" => $options, "classEx" => $classEx, "attributeEx" => $attributeEx]) @endcomponent
					</div>
					<div class="col-span-2">
						@php
							$options = collect();
							foreach(App\Department::orderName()->where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeDep($option_id)->pluck('departament_id'))->get() as $department)
							{
								if($requests->idDepartment == $department->id)
								{
									$options	=	$options->concat([["value" => $department->id, "selected" => "selected", "description" => $department->name]]);
								}
								else
								{
									$options	=	$options->concat([["value" => $department->id, "description" => $department->name]]);
								}
							}
							$classEx		=	"js-departmentsR";
							$attributeEx	=	"id=\"multiple-departmentsR\" name=\"idDepartmentR\" data-validation=\"required\"";
						@endphp
						@component('components.labels.label') Departamento: @endcomponent
						@component("components.inputs.select", ["options" => $options, "classEx" => $classEx, "attributeEx" => $attributeEx]) @endcomponent
					</div>
					<div class="col-span-2">
						@php
							$options = collect();
							if($requests->idProject !="")
							{
								$options	=	$options->concat([["value" => $requests->requestProject->idproyect, "description" => $requests->requestProject->proyectName, "selected" => "selected"]]);
							}
							$classEx		=	"js-projectsR";
							$attributeEx	=	"id=\"multiple-projectsR\" name=\"project_id\" data-validation=\"required\"";
						@endphp
						@component('components.labels.label') Proyecto: @endcomponent
						@component("components.inputs.select", ["options" => $options, "classEx" => $classEx, "attributeEx" => $attributeEx]) @endcomponent
					</div>
					<div class="col-span-2">
						@php
							$options = collect();
							foreach(App\Area::orderName()->where('status','ACTIVE')->get() as $area)
							{
								if($requests->idArea == $area->id)
								{
									$options	=	$options->concat([["value" => $area->id, "selected" => "selected", "description" => $area->name]]);
								}
								else
								{
									$options	=	$options->concat([["value" => $area->id, "description" => $area->name]]);
								}
							}
							$classEx		=	"js-areasR";
							$attributeEx	=	"id=\"multiple-areasR\" name=\"idAreaR\" data-validation=\"required\"";
						@endphp
						@component('components.labels.label') Dirección: @endcomponent
						@component("components.inputs.select", ["options" => $options, "classEx" => $classEx, "attributeEx" => $attributeEx]) @endcomponent
					</div>
					<div class="col-span-2">
						@php
							$options	=	collect();
							if ($requests->account !="")
							{
								$options	=	$options->concat([["value"	=>	$requests->accounts->idAccAcc,	"description" => $requests->accounts->account." - ".$requests->accounts->description." (".$requests->accounts->content.")", "selected" => "selected"]]);
							}
							$classEx		=	"js-accountsR";
							$attributeEx	=	"id=\"multiple-accountsR\" name=\"accountR\" data-validation=\"required\"";
						@endphp
						@component('components.labels.label') Clasificación de gastos: @endcomponent
						@component("components.inputs.select", ["options" => $options, "classEx" => $classEx, "attributeEx" => $attributeEx]) @endcomponent
					</div>
					<div class="col-span-2">
						@php
							$classEx		=	"js-labelsR labelsNew";
							$attributeEx	=	"name=\"idLabelsReview[]\" data-validation=\"required\"";
						@endphp
						@component('components.labels.label') Etiquetas: @endcomponent
						@component("components.inputs.select", ["classEx" => $classEx, "attributeEx" => $attributeEx]) @endcomponent
					</div>
					<div class="md:col-span-4 col-span-2">
						@component('components.labels.label') Comentarios (Opcional) @endcomponent
						@component('components.inputs.text-area')
							@slot('attributeEx')
								name="checkCommentA"
							@endslot
						@endcomponent
					</div>
				@endcomponent
			</div>
			<div id="rechaza" class="hidden">
				@component("components.containers.container-form")
				<div class="md:col-span-4 col-span-2">
						@component('components.labels.label', ["label" => "Comentarios (Opcional):"]) @endcomponent
						@component('components.inputs.text-area')
							@slot('attributeEx')
								name="checkCommentR"
							@endslot
						@endcomponent
					@endcomponent
				</div>
			</div>
			<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mt-4">
				@component('components.buttons.button', ["variant"=>"primary", "attributeEx" => "type=\"submit\" name=\"enviar\"", "label" => "ENVIAR SOLICITUD"]) @endcomponent
				@php
					$href	=	isset($option_id) ? url(getUrlRedirect($option_id)) : url(getUrlRedirect($child_id));
				@endphp
				@component('components.buttons.button', ["variant"=>"reset", "buttonElement" => "a", "attributeEx" => "href=\"".$href."\"", "classEx" =>"load-actioner", "label" => "REGRESAR"]) @endcomponent
			</div>
		@endcomponent
	@elseif($action == 'authorization')
		<div class="block overflow-auto w-full text-left mt-2">
			@component('components.labels.title-divisor') DATOS DE REVISIÓN @endcomponent
			@php
				$reviewAccount = App\Account::find($requests->accountR);
				$reviewAccountValue	=	isset($reviewAccount->account) ? $reviewAccountValue = $reviewAccount->account." - ".$reviewAccount->description." (".$reviewAccount->content.")" : "No hay";
				$labels	=	"";
				foreach($requests->labels as $label)
				{
					$labels	.= $label->description.", ";
				}
				$modelTable	=
				[
					"Revisó"					=>	$requests->reviewedUser->name." ".$requests->reviewedUser->last_name." ".$requests->reviewedUser->scnd_last_name,
					"Nombre de la Empresa"		=>	App\Enterprise::find($requests->idEnterpriseR)->name, 
					"Nombre de la Dirección"	=>	$requests->reviewedDirection->name,
					"Nombre del Departamento"	=>	App\Department::find($requests->idDepartamentR)->name,
					"Clasificación de gastos"	=>	$reviewAccountValue,
					"Nombre del Proyecto"		=>	$requests->reviewedProject->proyectName,
					"Etiquetas"					=>	$labels,
					"Comentarios"				=>	htmlentities($requests->checkComment),
				];
			@endphp
			@component("components.templates.outputs.table-detail-single", ["modelTable" => $modelTable]) @endcomponent
		</div>
		@component('components.forms.form', ["attributeEx" => "id=\"container-alta\" method=\"POST\" action=\"".route('finance.authorization.update', $requests->folio)."\"", "methodEx" => "PUT"])
			<div class="text-center mt-8">
				<div class="form-container mt-12">
					<div class="flex justify-center">
						@component('components.labels.label')
							@slot('attributeEx')
								id="label-inline"
							@endslot
							¿Desea aprobar ó rechazar la solicitud?
						@endcomponent
					</div>
					@component('components.containers.container-approval')
						@slot('attributeExButton') name="status" id="aprobar" value="10" @endslot
						@slot('attributeExButtonTwo') id="rechazar" name="status" value="7" @endslot
					@endcomponent
				</div>
			</div>
			<div id="aceptar" class="hidden">
				@component('components.labels.label', ["label" => "Comentarios (Opcional):"]) @endcomponent
				@component('components.inputs.text-area', ["attributeEx" => "name=\"authorizeCommentA\""]) @endcomponent
			</div>
			<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mt-4">
				@component('components.buttons.button', ["variant"=>"primary", "attributeEx" => "type=\"submit\" name=\"enviar\"", "label" => "ENVIAR SOLICITUD"]) @endcomponent
				@php
					$href	=	isset($option_id) ? url(getUrlRedirect($option_id)) : url(getUrlRedirect($child_id));
				@endphp
				@component('components.buttons.button', ["variant"=>"reset", "buttonElement" => "a", "attributeEx" => "href=\"".$href."\"", "classEx" => "load-actioner", "label" => "REGRESAR"]) @endcomponent
			</div>
		@endcomponent
	@endif
@endsection

@section('scripts')
<script>
	@if($action == 'review')
		function validate()
		{
			$.validate(
			{
				form: '#container-alta',
				onSuccess : function($form)
				{
					if($('input[name="status"]').is(':checked'))
					{
						if($('input#aprobar').is(':checked'))
						{
							enterprise	= $('#multiple-enterprisesR').val();
							area		= $('#multiple-areasR').val();
							department	= $('#multiple-departmentsR').val();
							account		= $('#multiple-accountsR').val();
							if(enterprise == '' || area == '' || department == '' || account == '')
							{
								swal('', 'Todos los campos son requeridos', 'error');
								return false;
							}
							else if ($('#tbody-concepts tr').length > 0 || $('.idDetailPurchaseNew').val()) 
							{
								swal('', 'Tiene conceptos sin asignar', 'error');
								return false;
							}
							else
							{
								swal("Cargando",{
									icon: '{{ asset(getenv('LOADING_IMG')) }}',
									button: false,
									closeOnClickOutside: false,
									closeOnEsc: false
								});
								return true;
							}
						}
						else
						{
							swal("Cargando",{
									icon: '{{ asset(getenv('LOADING_IMG')) }}',
									button: false,
									closeOnClickOutside: false,
									closeOnEsc: false
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
		}
		$(document).ready(function()
		{
			validate();
			count = 0;
			$(document).on('change','.js-enterprisesR',function()
			{
				$('.js-accountsR').empty();
			})
			.on('change','input[name="status"]',function()
			{
				if ($('input[name="status"]:checked').val() == "4") 
				{
					$("#rechaza").slideUp("slow");
					$("#aceptar").slideToggle("slow").addClass('form-container').css('display','block');
					generalSelect({'selector': '.js-accountsR', 'depends': '.js-enterprisesR', 'model': 3});
					generalSelect({'selector': '.js-labelsR', 'model': 19, 'maxSelection' : -1});
					generalSelect({'selector': '.js-projectsR', 'model': 21});
					@php
						$selects = collect([
							[
								"identificator"				=> ".js-enterprisesR",
								"placeholder"				=> "Seleccione la empresa",
								"language"					=> "es",
								"maximumSelectionLength"	=> "1"
							],
							[
								"identificator"				=> ".js-areasR",
								"placeholder"				=> "Seleccione la dirección",
								"language"					=> "es",
								"maximumSelectionLength"	=> "1"
							],
							[
								"identificator"				=> ".js-departmentsR",
								"placeholder"				=> "Seleccione el Departamento",
								"language"					=> "es",
								"maximumSelectionLength"	=> "1"
							],
						]);
					@endphp
					@component('components.scripts.selects',["selects" => $selects]) @endcomponent
				}
				else if ($('input[name="status"]:checked').val() == "6") 
				{
					$("#aceptar").slideUp("slow");
					$("#rechaza").slideToggle("slow").addClass('form-container').css('display','block');
				}
			});
		});
	@else
		function validate()
		{
			$.validate(
			{
				form: '#container-alta',
				onSuccess : function($form)
				{
					if($('input[name="status"]').is(':checked'))
					{
						swal("Cargando",{
							icon: '{{ asset(getenv('LOADING_IMG')) }}',
							button: false,
							closeOnClickOutside: false,
							closeOnEsc: false
						});
						return true;
					}
					else
					{
						swal('', 'Debe seleccionar al menos un estado', 'error');
						return false;
					}
				}
			});
		}
		$(document).ready(function()
		{
			validate();
			$(document).on('change','input[name="status"]',function()
			{
				$("#aceptar").slideDown("slow");
			});
		});
	@endif

	@if(isset($alert))
		{!! $alert !!}
	@endif
</script>
@endsection
