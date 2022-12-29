@extends('layouts.child_module')

@section('data')
	<div id="container-cambio" class="div-search">
		@component('components.labels.title-divisor') BUSCAR SOLICITUDES @endcomponent
		@php
			$values	=
			[
				'enterprise_option_id'	=>	isset($option_id) ? $option_id : "",
				'folio'					=>	isset($folio) ? $folio : '',
				'enterprise_id'			=>	isset($enterpriseid) ? $enterpriseid : "",
				'minDate'				=>	isset($mindate) ? date('d-m-Y',strtotime($mindate)) : '',
				'maxDate'				=>	isset($maxdate) ? date('d-m-Y',strtotime($maxdate)) : '',
				'name'					=>	isset($name) ? $name : '',
			];
		@endphp
		@component('components.forms.searchForm',["attributeEx" => "id=\"formsearch\"", "values" => $values])
			@slot('contentEx')
				<div class="col-span-2">
					@component('components.labels.label')Proyecto @endcomponent
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
			@endslot
			@if (count($requests) > 0)
				@slot('export')
					<div class="flex flex-row justify-end">
						@component("components.buttons.button", ["variant" => "success"])
							@slot('classEx')
								mt-4
							@endslot
							@slot('attributeEx')
								type=submit formaction={{ route('income.export.review') }}
							@endslot
							@slot('label')
								Exportar a Excel <i class="icon-file-excel"></i>
							@endslot
						@endcomponent
					</div>
				@endslot
			@endif
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
					["value"	=>	"Título"],
					["value"	=>	"Solicitante"],
					["value"	=>	"Elaborado por"],
					["value"	=>	"Estado"],
					["value"	=>	"Fecha de elaboración"],
					["value"	=>	"Empresa"],
					["value"	=>	"Acción"],
				]
			];
			foreach($requests as $request)
			{
				$userCompnent		=	[];
				$elaborateCompnent	=	[];
				foreach (App\User::where('id',$request->idRequest)->get() as $user)
				{
					$userCompnent[]	=
					[
						"label"	=>	$user->name." ".$user->last_name." ".$user->scnd_last_name
					];
				}
				foreach (App\User::where('id',$request->idElaborate)->get() as $elaborate)
				{
					$elaborateCompnent[]	=
					[
						"label"	=>	$elaborate->name." ".$elaborate->last_name." ".$elaborate->scnd_last_name
					];
				}
				$body	=
				[
					[
						"content"	=>	["label"	=>	$request->folio]
					],
					[
						"content"	=>	["label"	=>	isset($request->income->first()->title) && $request->income->first()->title != null ? htmlentities($request->income->first()->title) : 'No hay']
					],
					[
						"content"	=>	$userCompnent
					],
					[
						"content"	=>	$elaborateCompnent
					],
					[
						"content"	=>	["label"	=>	$request->statusrequest->description]
					],
					[
						"content"	=>	["label"	=>	Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$request->fDate)->format('d-m-Y')]
					],
					[
						"content"	=>	["label"	=>	isset($request->requestEnterprise->name) ? $request->requestEnterprise->name : "No hay"]
					],
					[
						"content"	=>
						[
							"kind"			=>	"components.buttons.button",
							"label"			=>	"<span class='icon-pencil'></span>",
							"variant"		=>	"success",
							"buttonElement"	=>	"a",
							"attributeEx"	=>	"type=\"button\" title=\"Editar Solicitud\" href=\"".route('income.review.edit',$request->folio)."\""
						]
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
					]
				]);
			@endphp
			@component('components.scripts.selects',["selects" => $selects]) @endcomponent
		});
	</script> 
@endsection

