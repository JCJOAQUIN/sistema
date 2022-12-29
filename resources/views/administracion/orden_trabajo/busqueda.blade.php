@extends('layouts.child_module')
@section('data')
	@component("components.labels.title-divisor") BUSCAR SOLICITUDES @endcomponent
	<div id="container-cambio" class="div-search">
		@php
			$values	=	['folio'	=>	isset($folio) ? $folio : ''];
			$hidden	=	['enterprise', 'rangeDate', 'name'];
		@endphp
		@component('components.forms.searchForm', ["attributeEx" => "id=\"formsearch\"", "values" => $values, "hidden" => $hidden])
			@slot('contentEx')
				<div class="col-span-2">
					@component('components.labels.label')
						Número (No.):
					@endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							type="text"
							name="number"
							id="input-search"
							placeholder="Ingrese un número de orden"
							value="{{ isset($number) ? $number : '' }}"
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label')
						Título:
					@endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							type="text"
							name="title_request"
							id="input-search"
							placeholder="Ingrese el título"
							value="{{ isset($title_request) ? $title_request : '' }}"
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label')
						Fecha en que se solicita:
					@endcomponent
					@php
						$minDateRequest	=	isset($mindate_request) ? $mindate_request : '';
						$maxDateRequest	=	isset($maxdate_request) ? $maxdate_request : '';
						$inputsRequest=
						[
							[
								"input_classEx"		=>	"input-text-date datepicker",
								"input_attributeEx"	=>	"name=\"mindate_request\" step=\"1\" placeholder=\"Desde\" value=\"".$minDateRequest."\"",
							],
							[
								"input_classEx"		=>	"input-text-date datepicker",
								"input_attributeEx"	=>	"name=\"maxdate_request\" step=\"1\" placeholder=\"Hasta\" value=\"".$maxDateRequest."\"",
							]
						];
					@endphp
					@component("components.inputs.range-input",["inputs" => $inputsRequest])
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label')
						Fecha en que deben estar en obra:
					@endcomponent
					@php
						$minDateObra	=	isset($mindate_obra) ? $mindate_obra : '';
						$maxDateObra	=	isset($maxdate_obra) ? $maxdate_obra : '';
						$inputsObra		=
						[
							[
								"input_classEx"		=>	"input-text-date datepicker",
								"input_attributeEx"	=>	"name=\"mindate_obra\" step=\"1\" placeholder=\"Desde\" value=\"".$minDateObra."\"",
							],
							[
								"input_classEx"		=>	"input-text-date datepicker",
								"input_attributeEx"	=>	"name=\"maxdate_obra\" step=\"1\" placeholder=\"Hasta\" value=\"".$maxDateObra."\"",
							]
						];
					@endphp
					@component("components.inputs.range-input",["inputs" => $inputsObra])
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label')
						Solicitante:
					@endcomponent
					@php
						$options		=	collect();
						$applicantData	=	App\CatRequestRequisition::where('id',$applicant)->first();
						if (isset($applicant) && $applicant!="")
						{
							$options	=	$options->concat([["value"	=>	$applicantData->id,	"description"	=>	$applicantData->name,	"selected"	=>	"selected"]]);
						}
					@endphp
					@component('components.inputs.select', ["options" => $options])
						@slot('attributeEx')
							name="applicant[]" multiple
						@endslot
						@slot('classEx')
							js-users
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label')
						Proyecto:
					@endcomponent
					@php
						$options		=	collect();
						$projectData 	=	App\Project::where('idproyect',$project_request)->first();
						if (isset($project_request) && $project_request!="")
						{
							$options	=	$options->concat([["value"	=>	$projectData->idproyect,	"description"	=>	$projectData->proyectName,	"selected"	=>	"selected"]]);
						}
					@endphp
					@component('components.inputs.select', ["options" => $options])
						@slot('attributeEx')
							title="Proyecto" name="project_request[]" multiple="multiple"
						@endslot
						@slot('classEx')
							js-project
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label')
						Estado:
					@endcomponent
					@php
						$optionsStatus	=	[];
						foreach (App\StatusRequest::whereIn('idrequestStatus',[2,3,5])->orderBy('description','asc')->get() as $s)
						{
							if (isset($status) && in_array($s->idrequestStatus, $status))
							{
								$optionsStatus[]	=	["value"	=>	$s->idrequestStatus,	"description"	=>	$s->description,	"selected"	=>	"selected"];
							}
							else
							{
								$optionsStatus[]	=	["value"	=>	$s->idrequestStatus,	"description"	=>	$s->description,];
							}
						}
					@endphp
					@component('components.inputs.select', ["options" => $optionsStatus])
						@slot('attributeEx')
							title="Estado de Solicitud"
							name="status[]"
							multiple="multiple"
						@endslot
						@slot('classEx')
							js-status
						@endslot
					@endcomponent
				</div>
			@endslot
		@endcomponent
	</div>
	@if(count($requests) > 0)
		@php
			$modelHead	=	[];
			$body		=	[];
			$modelBody	=	[];
			$modelHead	=
			[
				[
					["value"	=>	"Folio"],
					["value"	=>	"No."],
					["value"	=>	"Título"],
					["value"	=>	"Proyecto"],
					["value"	=>	"Solicitante"],
					["value"	=>	"Estado"],
					["value"	=>	"Fecha de elaboración"],
					["value"	=>	"Acción"],
				]
			];
			foreach($requests as $request)
			{
				$componentsExButtons	=	[];
				if ($request->status == 5 || $request->status == 6 || $request->status == 7  || $request->status == 10 || $request->status == 11 || $request->status == 13)
				{
					$titleAlt	=	"Ver Solicitud";
					$iconLabel	=	"<span class='icon-search'></span>";
					$variant	=	"secondary";
				}
				elseif ($request->status == 3 || $request->status == 4 || $request->status == 5 || $request->status == 10 || $request->status == 11  || $request->status == 12)
				{
					$titleAlt	=	"Ver Solicitud";
					$iconLabel	=	"<span class='icon-search'></span>";
					$variant	=	"secondary";
				}
				else
				{
					$titleAlt	=	"Editar Solicitud";
					$iconLabel	=	"<span class='icon-pencil'></span>";
					$variant	=	"success";
				}
				
				$body	=
				[
					[
						"content"	=>	["label"	=>	$request->folio],
					],
					[
						"content"	=>	["label"	=>	isset($request->workOrder->number) && $request->workOrder->number!="" ? $request->workOrder->number : "---"],
					],
					[
						"content"	=>	["label"	=>	isset($request->workOrder->title) && $request->workOrder->title != "" ? htmlentities($request->workOrder->title) : 'Sin Título'],
					],
					[
						"content"	=>	["label"	=>	$request->requestProject()->exists() ? $request->requestProject->proyectName : 'Sin Proyecto'],
					],
					[
						"content"	=>	["label"	=>	$request->workOrder()->exists() ? ($request->workOrder->relationApplicant->name !="" ? $request->workOrder->relationApplicant->name : 'Sin solicitante' ) : 'Sin solicitante'],
					],
					[
						"content"	=>	["label"	=>	isset($request->statusRequest->description) && $request->statusRequest->description!="" ? $request->statusRequest->description : "---"],
					],
					[
						"content"	=>	["label"	=>	Carbon\Carbon::parse($request->fDate)->format('d-m-Y')],
					],
					[
						"classEx"	=>	"text-nowrap",
						"content"	=>
						[
							"kind"			=>	"components.buttons.button",
							"variant"		=>	$variant,
							"label"			=>	"$iconLabel",
							"buttonElement"	=>	"a",
							"attributeEx"	=>	"type=\"button\" alt=\"$titleAlt\" title=\"$titleAlt\" href=\"".route('work_order.edit',$request->folio)."\""
						]
					],
				];
				$modelBody[]	=	$body;
			}
		@endphp
		@component('components.tables.table', ["modelHead" => $modelHead, "modelBody" => $modelBody])
			@slot('classEx')
				mt-4
			@endslot
		@endcomponent
		{{ $requests->appends($_GET)->links() }}
	@else
		@component('components.labels.not-found', ["variant" => "not-found"])@endcomponent
	@endif
@endsection

@section('scripts')

<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
<script src="{{ asset('js/jquery-ui.js') }}"></script>
<script src="{{ asset('js/datepicker.js') }}"></script>
<script src="{{ asset('js/jquery.numeric.js') }}"></script>
<script type="text/javascript"> 
	$(document).ready(function()
	{
		generalSelect({'selector': '.js-project', 'option_id': '{{$option_id}}', 'model': 17});
		generalSelect({'selector': '.js-users', 'model': 37});

		@php
			$selects = collect([ 
				[
					"identificator"				=> ".js-status",
					"placeholder"				=> "Seleccione el/los estado(s)",
					"language"					=> "es",
					"maximumSelectionLength"	=> "1"
				]
			]);
		@endphp
		@component('components.scripts.selects',["selects" => $selects]) @endcomponent
		$(function() 
		{
			$( ".datepicker" ).datepicker({ maxDate: 0, dateFormat: "yy-mm-dd" });
		});
		$(document).on('click','.delete-requisition',function(e)
			{
				e.preventDefault();
				attr = $(this).attr('href');
				swal({
					title		: "",
					text		: "Confirme que desea eliminar la requisición",
					icon		: "warning",
					buttons		:
					{
						cancel:
						{
							text		: "Cancelar",
							value		: null,
							visible		: true,
							closeModal	: true,
						},
						confirm:
						{
							text		: "Eliminar",
							value		: true,
							closeModal	: false
						}
					},
					dangerMode	: true,
				})
				.then((a) => {
					if (a)
					{
						window.location.href=attr;
					}
				});
			});
	});
</script>
@endsection
