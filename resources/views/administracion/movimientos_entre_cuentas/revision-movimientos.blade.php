@extends('layouts.child_module')
@section('data')
	@php
		$taxes = $retentions = 0;
	@endphp
	<div class="sm:text-center text-left my-5">
		A continuación podrá verificar la información de la solicitud antes de continuar con el proceso:
	</div>
	@php
		$requestUser			=	$request->requestUser()->exists() ? $request->requestUser->fullName() : "Sin solicitante";
		$elaborateUser			=	$request->elaborateUser()->exists() ? $request->elaborateUser->fullName() : "Sin elaborador";
		$requestAccountOrigin	=	App\Account::find($request->movementsEnterprise->first()->idAccAccOrigin);
		$requestAccountDestiny	=	App\Account::find($request->movementsEnterprise->first()->idAccAccDestiny);
		$modelTable	=
		[
			["Folio:",								$request->folio ],
			["Título y fecha:",						htmlentities($request->movementsEnterprise->first()->title)." - ".Carbon\Carbon::createFromFormat("Y-m-d",$request->movementsEnterprise->first()->datetitle)->format('d-m-Y ')],
			["Fiscal:",								$request->taxPayment == 1 ? "Si" : "No"],
			["Solicitante:",						$requestUser],
			["Elaborado por:",						$elaborateUser],
			["Empresa Origen:",						App\Enterprise::find($request->movementsEnterprise->first()->idEnterpriseOrigin)->name],
			["Clasificación del Gasto Origen:",		$requestAccountOrigin->account." - ".$requestAccountOrigin->description],
			["Empresa Destino:",					App\Enterprise::find($request->movementsEnterprise->first()->idEnterpriseDestiny)->name],
			["Clasificación del Gasto Destino:",	$requestAccountDestiny->account." - ".$requestAccountDestiny->description],
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
	@component('components.labels.title-divisor')
		@slot('classEx')
			mt-12
		@endslot
		CONDICIONES DE PAGO
	@endcomponent
	@php
		$modelTable	=
		[
			"Tipo de moneda"	=>	$request->movementsEnterprise->first()->typeCurrency,
			"Fecha de pago"		=>	$request->movementsEnterprise->first()->paymentDate != '' ? Carbon\Carbon::createFromFormat('Y-m-d',$request->movementsEnterprise->first()->paymentDate)->format('d-m-Y') : '',
			"Forma de pago"		=>	$request->movementsEnterprise->first()->paymentMethod->method,
			"Importe a pagar"	=>	"$ ".number_format($request->movementsEnterprise->first()->amount,2),
		];
	@endphp
	@component('components.templates.outputs.table-detail-single', ["modelTable" => $modelTable])@endcomponent
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
								"label"			=>	"Archivo",
							]
						],
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
	@component('components.forms.form', ["attributeEx" => "id=\"container-alta\" method=\"POST\" action=\"".route('movements-accounts.movements.updateReview', $request->folio)."\"", "methodEx" => "PUT"])
		<div class="text-center">
			@component('components.labels.label')
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
		<div id="aceptar" class="hidden">
			@component('components.containers.container-form')
				<div class="col-span-2">
					@component('components.labels.label') EMPRESA @endcomponent
					@php
						$options	=	collect();
						foreach (App\Enterprise::orderName()->where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeEnt($option_id)->pluck('enterprise_id'))->get() as $enterprise)
						{
							if ($request->movementsEnterprise()->exists() && $request->movementsEnterprise->first()->idEnterpriseOrigin == $enterprise->id)
							{
								$options	=	$options->concat([["value"	=>	$enterprise->id, "description"	=>	strlen($enterprise->name) >= 35 ? substr(strip_tags($enterprise->name),0,35).'...' : $enterprise->name,	"selected" => "selected"]]);
							}
							else
							{
								$options	=	$options->concat([["value"	=>	$enterprise->id, "description"	=>	strlen($enterprise->name) >= 35 ? substr(strip_tags($enterprise->name),0,35).'...' : $enterprise->name]]);
							}
						}
					@endphp
					@component('components.inputs.select', ["options" => $options])
						@slot('attributeEx')
							name="enterpriseid" multiple="multiple" border: 0px;" data-validation="required"
						@endslot
						@slot('classEx')
							js-enterprises removeselect
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') CUENTA DE ORIGEN @endcomponent
					@php
						$options	=	collect();
						if (isset($request) && $request->movementsEnterprise()->exists() && $request->movementsEnterprise->first()->idAccAccOrigin != "")
						{
							$options	=	$options->concat([["value"	=>	$request->movementsEnterprise->first()->accountOrigin->idAccAcc, "description"	=>	$request->movementsEnterprise->first()->accountOrigin->account." - ".$request->movementsEnterprise->first()->accountOrigin->description." (".$request->movementsEnterprise->first()->accountOrigin->content.")", "selected" => "selected"]]);
						}
					@endphp
					@component('components.inputs.select', ["options" => $options])
						@slot('attributeEx')
							multiple="multiple" name="accountid_origin" data-validation="required"
						@endslot
						@slot('classEx')
							js-accounts-origin removeselect
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') CUENTA DE DESTINO @endcomponent
					@php
						$options	=	collect();
						if (isset($request) && $request->movementsEnterprise()->exists() && $request->movementsEnterprise->first()->idAccAccDestiny !="")
						{
							$options	=	$options->concat([["value"	=>	$request->movementsEnterprise->first()->accountDestiny->idAccAcc, "description"	=>	$request->movementsEnterprise->first()->accountDestiny->account." - ".$request->movementsEnterprise->first()->accountDestiny->description." (".$request->movementsEnterprise->first()->accountDestiny->content.")",	"selected" => "selected"]]);
						}
					@endphp
					@component('components.inputs.select', ["options" => $options])
						@slot('attributeEx')
							multiple="multiple" name="accountid_destination" data-validation="required"
						@endslot
						@slot('classEx')
							js-accounts-destination removeselect
						@endslot
					@endcomponent
				</div>
				<div class="md:col-span-4 col-span-2">
					@component('components.labels.label') Comentarios (opcional) @endcomponent
					@component('components.inputs.text-area')
						@slot('attributeEx')
							name="checkCommentA"
						@endslot
					@endcomponent
				</div>
			@endcomponent
		</div>
		<div id="rechaza" class="hidden">
			@component('components.containers.container-form')
				<div class="md:col-span-4 col-span-2">
					@component('components.labels.label') Comentarios (opcional)
					@endcomponent
					@component('components.inputs.text-area')
						@slot('attributeEx')
							name="checkCommentR"
						@endslot
					@endcomponent
				</div>
			@endcomponent
		</div>
		<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mt-8">
			@component('components.buttons.button', ["variant" => "primary", "label" => "ENVIAR SOLICITUD"])
				@slot('attributeEx')
					type="submit" name="enviar" value="ENVIAR SOLICITUD"
				@endslot
			@endcomponent
			@component('components.buttons.button', ["variant" => "reset", "buttonElement" => "a", "classEx" => "load-actioner", "label" => "REGRESAR"])
				@slot('attributeEx')
					type="button"
					@if(isset($option_id)) 
						href="{{ url(getUrlRedirect($option_id)) }}" 
					@else 
						href="{{ url(getUrlRedirect($child_id)) }}" 
					@endif
				@endslot
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
		$.validate(
		{
			form: '#container-alta',
			onSuccess : function($form)
			{
				if($('input[name="status"]').is(':checked'))
				{
					if($('input#aprobar').is(':checked'))
					{
						enterprise		= $('.js-enterprises').val();
						accountorigin	= $('.js-accounts-origin').val();
						accountdestiny	= $('.js-accounts-destination').val();
						if(enterprise == '' || accountdestiny == '' || accountorigin == '' || enterprise == undefined || accountdestiny == undefined || accountorigin == undefined)
						{
							swal('', 'Todos los campos son requeridos', 'error');
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
				generalSelect({'selector': '.js-accounts-origin', 'depends': '.js-enterprises', 'model': 16});
				generalSelect({'selector': '.js-accounts-destination', 'depends': '.js-enterprises', 'model': 16});
				@php
					$selects = collect([
						[
							"identificator"				=> ".js-enterprises",
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
		.on('change','.js-enterprises',function()
		{
			$('.js-accounts-origin').empty();
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
