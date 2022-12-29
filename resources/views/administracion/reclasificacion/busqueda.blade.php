@extends('layouts.child_module')
  
@section('data')
	<div id="container-cambio" class="div-search">
		@component('components.labels.title-divisor')
			BUSCAR SOLICITUDES
		@endcomponent
		@php
			$values	=
			[
				'enterprise_option_id'	=>	$option_id,
				'folio'					=>	isset($folio) ? $folio : null,
				'enterprise_id'			=>	$enterpriseid,
				'name'					=>	isset($name) ? $name : null,
			];
			$hidden	=	['rangeDate'];
		@endphp
		@component('components.forms.searchForm', ["attributeEx" => "id=\"formsearch\"", "values" => $values, "hidden" => $hidden])
			@slot('contentEx')
				<div class="col-span-2">
					@component('components.labels.label')
						Fecha de Autorización:
					@endcomponent
					@php
						$minDateRequest	=	isset($mindate) ? date('d-m-Y',strtotime($mindate)) : null;
						$maxDateRequest	=	isset($maxdate) ? date('d-m-Y',strtotime($maxdate)) : null;
						$inputsRequest	=
						[
							[
								"input_classEx"		=>	"input-text-date datepicker",
								"input_attributeEx"	=>	"name=\"mindate\" step=\"1\" placeholder=\"Desde\" value=\"".$minDateRequest."\"",
							],
							[
								"input_classEx"		=>	"input-text-date datepicker",
								"input_attributeEx"	=>	"name=\"maxdate\" step=\"1\" placeholder=\"Hasta\" value=\"".$maxDateRequest."\"",
							]
						];
					@endphp
					@component("components.inputs.range-input",["inputs" => $inputsRequest])
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label')
						Tipo de solicitud:
					@endcomponent
					@php
						$optionsKind	=	[];
						foreach (App\RequestKind::orderName()->whereIn('idrequestkind',[1,3,5,8,9,12,13,14,15,17])->get() as $k)
						{
							if (isset($kind) && in_array($k->idrequestkind,$kind))
							{
								$optionsKind[]	=	["description"	=>	$k->kind,	"value"	=>	$k->idrequestkind,	"selected"	=>	"selected"];
							}
							else
							{
								$optionsKind[]	=	["description"	=>	$k->kind,	"value"	=>	$k->idrequestkind];
							}
						}
					@endphp
					@component('components.inputs.select',["options" => $optionsKind])
						@slot('attributeEx')
							title="Tipo de solicitud"
							name="kind[]"
							class="js-kind"
							multiple="multiple"
						@endslot
						@slot('classEx')
							js-kind
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label')
						Cuenta:
					@endcomponent
					@php
						$optionsAccount	=	[];
						if (isset($enterpriseid))
						{
							foreach (App\Account::where('idEnterprise',$enterpriseid)->where('selectable',1)->get() as $acc)
							{
								if (isset($accountid) && $accountid == $acc->idAccAcc)
								{
									$optionsAccount[]	=	["description"	=>	strlen($acc->account.' - '.$acc->description) >= 35 ? substr(strip_tags($acc->description), 0,35).'...' : $acc->account.' - '.$acc->description,	"value"	=>	$acc->idAccAcc,	"selected"	=>	"selected"];
								}
								else
								{
									$optionsAccount[]	=
									[
										"description"	=>	strlen($acc->account.' - '.$acc->description) >= 35 ? substr(strip_tags($acc->description), 0,35).'...' : $acc->account.' - '.$acc->description,
										"value"			=>	$acc->idAccAcc
									];
								}
							}
						}
					@endphp
					@component('components.inputs.select',["options" => $optionsAccount])
						@slot('attributeEx')
							title="Clasificación de gasto"
							name="accountid"
							multiple="multiple"
						@endslot
						@slot('classEx')
							js-account
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label')
						Departamento:
					@endcomponent
					@php
						$optionsDepartment	=	[];
						foreach (App\Department::orderName()->where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeDep($option_id)->pluck('departament_id'))->get() as $dep)
						{
							if (isset($departmentid) && $departmentid == $dep->id)
							{
								$optionsDepartment[]	=	["description"	=>	strlen($dep->name) >= 35 ? substr(strip_tags($dep->name),0,35) : $dep->name,	"value"	=>	$dep->id,	"selected"	=>	"selected"];
							}
							else
							{
								$optionsDepartment[]	=	["description"	=>	strlen($dep->name) >= 35 ? substr(strip_tags($dep->name),0,35) : $dep->name,	"value"	=>	$dep->id];
							}
						}
					@endphp
					@component('components.inputs.select',["options" => $optionsDepartment])
						@slot('attributeEx')
							title="Departamento"
							name="departmentid"
							multiple="multiple"
						@endslot
						@slot('classEx')
							js-department
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label')
						Dirección:
					@endcomponent
					@php
						$optionsAddress	=	[];
						foreach (App\Area::orderName()->where('status','ACTIVE')->get() as $area)
						{
							if (isset($areaid) && $areaid == $area->id)
							{
								$optionsAddress[]	=	["description"	=>	strlen($area->name) >= 35 ? substr(strip_tags($area->name),0,35) : $area->name,	"value"	=>	$area->id,	"selected"	=>	"selected"];
							}
							else
							{
								$optionsAddress[]	=	["description"	=>	strlen($area->name) >= 35 ? substr(strip_tags($area->name),0,35) : $area->name,	"value"	=>	$area->id];
							}
						}
					@endphp
					@component('components.inputs.select',["options" => $optionsAddress])
						@slot('attributeEx')
							title="Dirección"
							multiple="multiple"
							name="areaid"
						@endslot
						@slot('classEx')
							js-areas
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label')
						Proyecto:
					@endcomponent
					@php
						$options	=	collect();
						$projectData	=	App\Project::find($projectid);
						if (isset($projectid) && $projectData != "")
						{
							$options[]	=	["description"		=>	$projectData->proyectName, "value"	=>	$projectData->idproyect, "selected"	=>	"selected"];
						}
					@endphp
					@component('components.inputs.select',["options" => $options])
						@slot('attributeEx')
							title="Proyecto" name="projectid" multiple="multiple"
						@endslot
						@slot('classEx')
							js-projects
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label')
						Estado:
					@endcomponent
					@php
						$optionsStatus	=	[];
						foreach (App\StatusRequest::orderName()->whereIn('idrequestStatus',[5,10,11,12,18])->get() as $s)
						{
							if (isset($status) && $status == $s->idrequestStatus)
							{
								$optionsStatus[]	=	["description"	=>	$s->description,"value"	=>	$s->idrequestStatus,	"selected"	=>	"selected"];
							}
							else
							{
								$optionsStatus[]	=	["description"	=>	$s->description,"value"	=>	$s->idrequestStatus];
							}
						}
					@endphp
					@component('components.inputs.select',["options" => $optionsStatus])
						@slot('attributeEx')
							title="Estado de Solicitud"
							name="status"
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
					["value"	=>	"Tipo"],
					["value"	=>	"Título"],
					["value"	=>	"Solicitante"],
					["value"	=>	"Elaborado por"],
					["value"	=>	"Estado"],
					["value"	=>	"Fecha de autorización"],
					["value"	=>	"Empresa"],
					["value"	=>	"Clasificación del gasto"],
					["value"	=>	"Acción"]
				]
			];
			foreach ($requests as $request)
			{
				switch ($request->kind)
				{
					case '1':
						$titlePurchase	=	isset($request->purchases->first()->title) && $request->purchases->first()->title != '' ? $request->purchases->first()->title : 'No hay';
					break;
					case '2':
						$titlePurchase	=	isset($request->nominas->first()->title) && $request->nominas->first()->title != '' ? $request->nominas->first()->title : 'No hay';
					break;
					case '3':
						$titlePurchase	=	isset($request->expenses->first()->title) && $request->expenses->first()->title != '' ? $request->expenses->first()->title : 'No hay';
					break;
					case '5':
						$titlePurchase	=	isset($request->loan->first()->title) && $request->loan->first()->title != '' ? $request->loan->first()->title : 'No hay';
					break;
					case '8':
						$titlePurchase	=	isset($request->resource->first()->title) && $request->resource->first()->title != '' ? $request->resource->first()->title : 'No hay';
					break;
					case '9':
						$titlePurchase	=	isset($request->refunds->first()->title) && $request->refunds->first()->title != '' ? $request->refunds->first()->title : 'No hay';
					break;
					case '12':
						$titlePurchase	=	isset($request->loanEnterprise->first()->title) && $request->loanEnterprise->first()->title != '' ? $request->loanEnterprise->first()->title : 'No hay';
					break;
					case '13':
						$titlePurchase	=	isset($request->purchaseEnterprise->first()->title) && $request->purchaseEnterprise->first()->title != '' ? $request->purchaseEnterprise->first()->title : 'No hay';
					break;
					case '14':
						$titlePurchase	=	isset($request->groups->first()->title) && $request->groups->first()->title != '' ? $request->groups->first()->title : 'No hay';
					break;
					case '15':
						$titlePurchase	=	isset($request->movementsEnterprise->first()->title) && $request->movementsEnterprise->first()->title != '' ? $request->movementsEnterprise->first()->title : 'No hay';
					break;
					case '17':
						$titlePurchase	=	$request->purchaseRecord()->exists() && $request->purchaseRecord->title != null ? $request->purchaseRecord->title : 'No hay';
					break;
				}
				$body	=
				[
					[
						"content"	=>	
						[
							"label"	=>	$request->folio
						]
					],
					[
						"content"	=>	
						[
							"label"	=>	$request->requestkind->kind
						]
					],
					[
						"content"	=>	
						[
							"label"	=>	htmlentities($titlePurchase)
						]
					],
					[
						"content"	=>	
						[
							"label"	=>	$request->requestUser->name.' '.$request->requestUser->last_name.' '.$request->requestUser->scnd_last_name
						]
					],
					[
						"content"	=>	
						[
							"label"	=>	$request->elaborateUser->name.' '.$request->elaborateUser->last_name.' '.$request->scnd_last_name
						]
					],
					[
						"content"	=>	
						[
							"label"	=>	$request->statusrequest->description
						]
					],
					[
						"content"	=>	
						[
							"label"	=>	Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$request->authorizeDate)->format('d-m-Y')
						]
					],
					[
						"content"	=>	["label"	=>	$request->reviewedEnterprise()->exists() ? $request->reviewedEnterprise->name : 'Varias']
					],
					[
						"content"	=>	
						[
							"label"	=>	$request->accountsReview()->exists() ?  $request->accountsReview->account.' '.$request->accountsReview->description : 'Varias'
						]
					],
					[
						"content"	=>
						[
							"kind"			=>	"components.buttons.button",
							"variant"		=>	"success",
							"label"			=>	"<span class='icon-pencil'></span>",
							"buttonElement"	=>	"a",
							"attributeEx"	=>	"type=\"button\" title=\"Editar\" alt=\"Editar\" href=\"".route('reclassification.edit',$request->folio)."\""
						]
					],
				];
				$modelBody[]	=	$body;
			}
		@endphp
		@component('components.tables.table', ["modelHead" => $modelHead, "modelBody" => $modelBody]) @endcomponent
		{{ $requests->appends($_GET)->links() }}
	@else
		@component('components.labels.not-found')
				@slot('classEx')
					id="not-found"
				@endslot
		@endcomponent
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
			generalSelect({'selector': '.js-account', 'depends': '.js-enterprise', 'model': 10});
			generalSelect({'selector': '.js-projects', 'model': 14});
			@php
				$selects = collect([
					[
						"identificator"				=> ".js-enterprise",
						"placeholder"				=> "Seleccione la empresa",
						"language"					=> "es",
						"maximumSelectionLength"	=> "1"
					],
					[
						"identificator"				=> ".js-department",
						"placeholder"				=> "Seleccione el departamento",
						"language"					=> "es",
						"maximumSelectionLength"	=> "1"
					],
					[
						"identificator"				=> ".js-areas",
						"placeholder"				=> "Seleccione la dirección",
						"language"					=> "es",
						"maximumSelectionLength"	=> "1"
					],
					[
						"identificator"				=> ".js-status",
						"placeholder"				=> "Seleccione un estado",
						"language"					=> "es",
						"maximumSelectionLength"	=> "1"
					],
					[
						"identificator"				=> ".js-kind",
						"placeholder"				=> "Seleccione un tipo de solicitud",
						"language"					=> "es",
						"maximumSelectionLength"	=> "1"
					],
				]);
			@endphp
			@component('components.scripts.selects',["selects" => $selects]) @endcomponent
			$(document).on('change','.js-enterprise',function()
			{
				$('.js-account').empty();
			});
		});
	</script>
@endsection
