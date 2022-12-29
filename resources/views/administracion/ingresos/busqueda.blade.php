@extends('layouts.child_module')

@section('data')
	@component('components.labels.title-divisor') BUSCAR SOLICITUDES @endcomponent
	@php
		$values =
		[
			'enterprise_option_id'	=>	isset($option_id) ? $option_id : "",
			'folio'					=>	isset($folio) ? $folio : "",
			'enterprise_id'			=>	isset($enterpriseid) ? $enterpriseid : "",
			'minDate'				=>	isset($mindate) ? date('d-m-Y',strtotime($mindate)) : '',
			'maxDate'				=>	isset($maxdate) ? date('d-m-Y',strtotime($maxdate)) : '',
			'name'					=>	isset($name) ? $name : '',
		];
	@endphp
	@component('components.forms.searchForm', ["attributeEx" => "id=\"formsearch\"", "values" => $values])
		@slot('contentEx')
			<div class="col-span-2">
				@component('components.labels.label')
					Proyecto:
				@endcomponent
				@php
					$options	=	collect();
					$project	=	App\Project::find($projectid);
					if ($project!="")
					{
						$options	=	$options->concat([["value"	=>	$project->idproyect, "description"	=>	$project->proyectName, "selected"	=>	"selected"]]);
					}
				@endphp
				@component('components.inputs.select', ["options" => $options])
					@slot('attributeEx')
						title="Proyecto" multiple="multiple" name="projectid"
					@endslot
					@slot('classEx')
					js-project
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label')
				Estado de Solicitud	:
				@endcomponent
				@php
					$optionsStatus	=	[];
					foreach (App\StatusRequest::orderName()->whereIn('idrequestStatus',[2,3,4,5,6,7,10,11,12,20,21,22])->get() as $s)
					{
						if (isset($status) && $status == $s->idrequestStatus)
						{
							$optionsStatus[]	=	["value"	=>	$s->idrequestStatus,	"description"	=>	$s->description,	"selected"	=>	"selected"];
						}
						else
						{
							$optionsStatus[]	=	["value"	=>	$s->idrequestStatus,	"description"	=>	$s->description];
						}
					}
				@endphp
				@component('components.inputs.select', ["options" => $optionsStatus])
					@slot('attributeEx')
						title="Estado de Solicitud" name="status" multiple="multiple"
					@endslot
					@slot('classEx')
						js-status
					@endslot
				@endcomponent
			</div>
		@endslot
		@if (count($requests) > 0)
			@slot('export')
				@component("components.buttons.button", ["variant" => "success"]) 
					@slot("classEx")
						export mt-4
					@endslot
					@slot('attributeEx')
						type="submit"
						formaction="{{ route('income.export.follow') }}"
					@endslot
					@slot('slot')
						Exportar a Excel <span class='icon-file-excel'></span>
					@endslot
				@endcomponent
			@endslot
		@endif
	@endcomponent
	@if(count($requests) > 0)
		@php
			$modelHead	=	[];
			$body		=	[];
			$modelBody	=	[];
			$modelHead	=
			[
				[
					["value"	=>	"Folio"],
					["value"	=>	"Título"],
					["value"	=>	"Solicitante"],
					["value"	=>	"Elaborado por"],
					["value"	=>	"Estado"],
					["value"	=>	"Fecha de elaboración"],
					["value"	=>	"Empresa"],
					["value"	=>	"Acción"]
				]
			];
			foreach($requests as $request)
			{
				$userApplication		=	"";
				$userCreator			=	"";
				$nameEnterprise			=	"";
				$componentsExtButtons	=	[];
				if ($request->idRequest == "")
				{
					$userApplication	=	"No hay solicitante";
				}
				else
				{
					foreach (App\User::where('id',$request->idRequest)->get() as $user)
					{
						$userApplication	=	$user->name." ".$user->last_name." ".$user->scnd_last_name;
					}
				}
				foreach (App\User::where('id',$request->idElaborate)->get() as $user)
				{
					$userCreator	=	$user->name." ".$user->last_name." ".$user->scnd_last_name;
				}
				if (isset($request->reviewedEnterprise->name))
				{
					$nameEnterprise	=	$request->reviewedEnterprise->name;
				}
				elseif(isset($request->reviewedEnterprise->name) == false && isset($request->requestEnterprise->name))
				{
					$nameEnterprise	=	$request->requestEnterprise->name;
				}
				else
				{
					$nameEnterprise	=	"No hay";
				}
				if ($request->status == 5 || $request->status == 6 || $request->status == 7  || $request->status == 10 || $request->status == 11 || $request->status == 13 || $request->status == 14 || $request->status == 20)
				{
					$componentsExtButtons	=
					[
						[
							"kind"			=>	"components.buttons.button",
							"variant"		=>	"warning",
							"buttonElement"	=>	"a",
							"label"			=>	"<span class='icon-plus'></span>",
							"attributeEx"	=>	"type=\"button\" alt=\"Nueva Solicitud\" title=\"Nueva Solicitud\" href=\"".route('income.create.new',$request->folio)."\"",
							"classEx"		=>	"follow-btn"
						],
						[
							"kind"			=>	"components.buttons.button",
							"variant"		=>	"secondary",
							"buttonElement"	=>	"a",
							"label"			=>	"<span class='icon-search'></span>",
							"attributeEx"	=>	"type=\"button\" alt=\"Ver Solicitud\" title=\"Ver Solicitud\" href=\"".route('income.follow.edit',$request->folio)."\"",
							"classEx"		=>	"follow-btn"
						],
					];
					if ($request->status == 5 || $request->status == 10 || $request->status == 11 || $request->status == 13 || $request->status == 14)
					{
						$componentsExtButtons[]	=
						[
							"kind"			=>	"components.buttons.button",
							"variant"		=>	"dark-red",
							"buttonElement"	=>	"a",
							"label"			=>	"<span class='icon-pdf'></span>",
							"attributeEx"	=>	"type=\"button\" alt=\"Descargar orden\" title=\"Descargar orden\" href=\"".route('income.download.document',$request->folio)."\"",
							"classEx"		=>	"follow-btn"
						];
					}
				}
				else if ($request->status == 3 || $request->status == 4 || $request->status == 5 || $request->status == 10 || $request->status == 11 || $request->status == 13 || $request->status == 14)
				{
					$componentsExtButtons[]	=
					[
						"kind"			=>	"components.buttons.button",
						"variant"		=>	"secondary",
						"buttonElement"	=>	"a",
						"label"			=>	"<span class='icon-search'></span>",
						"attributeEx"	=>	"type=\"button\" alt=\"Ver Solicitud\" title=\"Ver Solicitud\" href=\"".route('income.follow.edit',$request->folio)."\"",
						"classEx"		=>	"follow-btn"
					];
					if ($request->status == 4  || $request->status == 10 || $request->status == 11 || $request->status == 13 || $request->status == 14)
					{
						$componentsExtButtons[]	=
						[
							"kind"			=>	"components.buttons.button",
							"variant"		=>	"dark-red",
							"buttonElement"	=>	"a",
							"label"			=>	"<span class='icon-pdf'></span>",
							"attributeEx"	=>	"type=\"button\" alt=\"Descargar orden\" title=\"Descargar orden\" href=\"".route('income.download.document',$request->folio)."\"",
							"classEx"		=>	"follow-btn"
						];
					}
				}
				else
				{
					$componentsExtButtons[]	=
					[
						"kind"			=>	"components.buttons.button",
						"variant"		=>	"success",
						"buttonElement"	=>	"a",
						"label"			=>	"<span class='icon-pencil'></span>",
						"attributeEx"	=>	"type=\"button\" alt=\"Editar Solicitud\" title=\"Editar Solicitud\" href=\"".route('income.follow.edit',$request->folio)."\"",
						"classEx"		=>	"follow-btn"
					];
				}
				
				$body	=
				[
					[
						"content"	=>	["label"	=>	$request->folio],
					],
					[
						"content"	=>	["label"	=>	$request->income()->exists() ? isset($request->income->first()->title) ? htmlentities($request->income->first()->title) : 'No hay' : 'No hay'],
					],
					[
						"content"	=>	["label"	=>	$userApplication],
					],
					[
						"content"	=>	["label"	=>	$userCreator],
					],
					[
						"content"	=>	["label"	=>	$request->statusrequest->description],
					],
					[
						"content"	=>	["label"	=>	Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$request->fDate)->format('d-m-Y')],
					],
					[
						"content"	=>	["label"	=>	$nameEnterprise],
					],
					[
						"content"	=>	$componentsExtButtons
					],
				];
				$modelBody[]	=	$body;
			}
		@endphp
		@component('components.tables.table', ["modelHead" => $modelHead, "modelBody" => $modelBody]) @endcomponent
		{{ $requests->appends($_GET)->links() }}
	@else
		@component('components.labels.not-found') @endcomponent
	@endif
@endsection

@section('scripts')
	<script type="text/javascript">
		$(document).ready(function()
		{
			generalSelect({'selector': '.js-project', 'model': 21});
			@php
				$selects = collect([
					[
						"identificator"				=> ".js-enterprise",
						"placeholder"				=> "Seleccione la empresa",
						"language"					=> "es",
						"maximumSelectionLength"	=> "1"
					],
					[
						"identificator"				=> ".js-status",
						"placeholder"				=> "Seleccione un estado",
						"language"					=> "es",
						"maximumSelectionLength"	=> "1"
					]
				]);
			@endphp
			@component('components.scripts.selects',["selects" => $selects]) @endcomponent
		});
		@if(isset($alert))
			{!! $alert !!}
		@endif
	</script>
@endsection