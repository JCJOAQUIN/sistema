@extends('layouts.child_module')

@section('data')
	@php
		$taxes	=	$retentions	=	0;
	@endphp
	<div class="sm:text-center text-left my-5">
		A continuación podrá verificar la información de la solicitud antes de continuar con el proceso:
	</div>
	@php
		$requestUser	=	$request->requestUser()->exists() ? $request->requestUser->fullName() : "Sin solicitante";
		$elaborateUser	=	$request->elaborateUser()->exists() ? $request->elaborateUser->fullName() : "Sin elaborador";
		$accountOrigin	=	App\Account::find($request->loanEnterprise->first()->idAccAccOrigin);
		$requestAccount	=	App\Account::find($request->loanEnterprise->first()->idAccAccDestiny);
		$modelTable		=
		[
			["Folio:",								$request->folio],
			["Título y fecha:",						htmlentities($request->loanEnterprise->first()->title)." - ".Carbon\Carbon::createFromFormat('Y-m-d',$request->loanEnterprise->first()->datetitle)->format('d-m-Y')],
			["Fiscal:",								$request->taxPayment == 1 ? "Si" : "No"],
			["Solicitante:",						$requestUser],
			["Elaborado por:",						$elaborateUser],
			["Empresa Origen:",						App\Enterprise::find($request->loanEnterprise->first()->idEnterpriseOrigin)->name],
			["Clasificación del Gasto Origen:",		$accountOrigin->account." - ".$accountOrigin->description],
			["Empresa Destino:",					App\Enterprise::find($request->loanEnterprise->first()->idEnterpriseDestiny)->name],
			["Clasificación del Gasto Destino:",	$requestAccount->account." - ".$requestAccount->description],
		];
	@endphp
	@component('components.templates.outputs.table-detail', ["modelTable" => $modelTable])
		@slot('classEx')
			mt-4
		@endslot
		@slot('title')
			Detalles de la Solicitud de {{ $request->requestkind->kind }}
		@endslot
	@endcomponent
	@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "CONDICIONES DE PAGO"]) @endcomponent
	@php
		$modelTable	=
		[
			"Tipo de moneda"	=>	$request->loanEnterprise->first()->currency,
			"Fecha de pago"		=>	Carbon\Carbon::createFromFormat('Y-m-d',$request->loanEnterprise->first()->paymentDate)->format('d-m-Y'),
			"Forma de pago"		=>	$request->loanEnterprise->first()->paymentMethod->method,
			"Importe a pagar"	=>	"$ ".number_format($request->loanEnterprise->first()->amount,2),
		];
	@endphp
	@component('components.templates.outputs.table-detail-single', ["modelTable" => $modelTable])@endcomponent
	@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "DOCUMENTOS"]) @endcomponent
	@php
		$modelHead	=	[];
		$body		=	[];
		$modelBody	=	[];
		if (count($request->loanEnterprise->first()->documentsLoan)>0)
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
								"kind"				=>	"components.buttons.button",
								"variant"			=>	"secondary",
								"buttonElement"		=>	"a",
								"attributeEx"		=>	"type=\"button\" target=\"_blank\" href=\"".url('docs/movements/'.$doc->path)."\"",
								"label"				=>	"Archivo"
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
				]
			];
			$modelBody[]	=	$body;
		}
	@endphp
	@component('components.tables.alwaysVisibleTable', ["modelHead" => $modelHead, "modelBody" => $modelBody])@endcomponent
	@component('components.forms.form', ["attributeEx" => "id=\"container-alta\" method=\"POST\" action=\"".route('movements-accounts.loan.updateReview', $request->folio)."\"", "methodEx" => "PUT"])
		@component('components.labels.label', ["label" => "¿Desea aprobar ó rechazar la solicitud?", "classEx" => "text-center"]) @endcomponent
		@component('components.containers.container-approval')
			@slot('attributeExButton')
				name="status" id="aprobar" value="4"
			@endslot
			@slot('attributeExButtonTwo')
				id="rechazar" name="status" value="6"
			@endslot
		@endcomponent
		<div class="hidden" id="aceptar">
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
					@component('components.inputs.select', ["options" => $options,"classEx" => "js-enterprises-origin removeselect", "attributeEx" => "name=\"enterpriseid_origin\" multiple=\"multiple\" data-validation=\"required\""]) @endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label', ["label" => "Clasificación del gasto:"])  @endcomponent
					@php
						$options	=	collect();
						if (isset($request) && $request->loanEnterprise()->exists() && $request->loanEnterprise->first()->idAccAccOrigin !="")
						{
							$options	=	$options->concat([["value"	=>	$request->loanEnterprise->first()->accountOrigin->idAccAcc,	"description"	=>	$request->loanEnterprise->first()->accountOrigin->account." - ".$request->loanEnterprise->first()->accountOrigin->description." (".$request->loanEnterprise->first()->accountOrigin->content.")",	"selected"	=>	"selected"]]);
						}
					@endphp
					@component('components.inputs.select', ["options" => $options,"classEx" => "js-accounts-origin removeselect", "attributeEx" => "multiple=\"multiple\" name=\"accountid_origin\" data-validation=\"required\""]) @endcomponent
				</div>
			@endcomponent
			@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "CUENTA DE DESTINO"]) @endcomponent
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
					@component('components.inputs.select', ["options" => $options,"classEx" => "js-enterprises-destination removeselect", "attributeEx" => "name=\"enterpriseid_destination\" multiple=\"multiple\" data-validation=\"required\""]) @endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label', ["label" => "Clasificación del gasto:"]) @endcomponent
					@php
						$options	=	collect();
						if (isset($request) && $request->loanEnterprise()->exists() && $request->loanEnterprise->first()->idAccAccDestiny !="")
						{
							$options	=	$options->concat([["value"	=>	$request->loanEnterprise->first()->accountDestiny->idAccAcc,	"description"	=>	$request->loanEnterprise->first()->accountDestiny->account." - ".$request->loanEnterprise->first()->accountDestiny->description." (".$request->loanEnterprise->first()->accountDestiny->content.")",	"selected"	=>	"selected"]]);
						}
					@endphp
					@component('components.inputs.select', ["options" => $options,"classEx" => "js-accounts-destination removeselect", "attributeEx" => "multiple=\"multiple\" name=\"accountid_destination\" data-validation=\"required\""]) @endcomponent
				</div>
			@endcomponent
			@component('components.labels.label', ["label" => "Comentarios (opcional):", "classEx" => "mt-12"]) @endcomponent
			@component('components.inputs.text-area', ["attributeEx" => "name=\"checkCommentA\""]) @endcomponent
		</div>
		<div class="hidden" id="rechaza">
			@component('components.labels.label', ["label" => "Comentarios (opcional):", "classEx" => "mt-12"]) @endcomponent
			@component('components.inputs.text-area', ["attributeEx" => "name=\"checkCommentR\""]) @endcomponent
		</div>
		<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mt-8">
			@component('components.buttons.button', ["variant" => "primary", "attributeEx" => "type=\"submit\" name=\"enviar\" value=\"ENVIAR SOLICITUD\"", "label" => "ENVIAR SOLICITUD"]) @endcomponent
			@php
				$href	=	isset($option_id) ? url(getUrlRedirect($option_id)) : url(getUrlRedirect($child_id));
			@endphp
			@component('components.buttons.button', ["variant" => "reset", "buttonElement" => "a", "attributeEx" => "href=\"".$href."\"", "classEx" => "load-actioner", "label" => "REGRESAR"]) @endcomponent
		</div>
	@endcomponent
@endsection

@section('scripts')
<script>
	$(document).ready(function()
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
		count = 0;
		$(document).on('change','input[name="status"]',function()
		{
			if ($('input[name="status"]:checked').val() == "4") 
			{
				$("#rechaza").slideUp("slow");
				$("#aceptar").slideToggle("slow").addClass('form-container').css('display','block');
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
						]
					]);
				@endphp
				@component('components.scripts.selects',["selects" => $selects]) @endcomponent
			}
			else if ($('input[name="status"]:checked').val() == "6") 
			{
				$("#aceptar").slideUp("slow");
				$("#rechaza").slideToggle("slow").addClass('form-container').css('display','block');
			}
		})
		.on('change','.js-enterprises-origin',function()
		{
			$('.js-accounts-origin').empty();
		})
		.on('change','.js-enterprises-destination',function()
		{
			$('.js-accounts-destination').empty();
		});

		/*$('.subtotal').text("$"+sumatotal);
		sumatotal = 0;
		$('.importe').each(function(i, v)
			{
				valor		= parseFloat($(this).val());
				sumatotal	= sumatotal + valor;
			});*/
	});
</script>
@endsection