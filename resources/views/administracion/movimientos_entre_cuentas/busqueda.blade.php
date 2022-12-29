@extends('layouts.child_module')
  
@section('data')
	<div id="container-cambio" class="div-search">
		@component("components.labels.title-divisor") BUSCAR SOLICITUDES @endcomponent
		@php
			$values	=
			[
				'folio'		=>	isset($folio) ? $folio : '',
				'minDate'	=>	isset($mindate) ? date('d-m-Y',strtotime($mindate)) : '',
				'maxDate'	=>	isset($maxdate) ? date('d-m-Y',strtotime($maxdate)) : '',
				'name'		=>	isset($name) ? $name : '',
			];
			$hidden	=	['enterprise'];
		@endphp
		@component('components.forms.searchForm', ["attributeEx" => "id=\"formsearch\"", "values" => $values, "hidden" => $hidden])
			@slot('contentEx')
				<div class="col-span-2">
					@component("components.labels.label") Tipo de solicitud: @endcomponent
					@php
						$option	=	collect();
						foreach (App\RequestKind::orderName()->whereIn('idrequestkind',[11,12,13,14,15])->get() as $k)
						{
							if (isset($kind) && $kind == $k->idrequestkind)
							{
								$option	=	$option->concat([["value" => $k->idrequestkind, "description" => $k->kind, "selected" => "selected"]]);
							}
							else
							{
								$option	=	$option->concat([["value" => $k->idrequestkind, "description" => $k->kind]]);
							}
						}
					@endphp
					@component("components.inputs.select",
					[
						"attributeEx"	=>	"type=\"text\" title=\"Tipo de Solicitud\" name=\"kind\" id=\"input-search\" placeholder=\"Ingrese un nombre\"", 
						"classEx"		=>	"js-kind",
						"options"		=>	$option
					]) @endcomponent
				</div>
				<div class="col-span-2">
					@component("components.labels.label") Estado de solicitud: @endcomponent
					@php
						$option = collect();
						foreach (App\StatusRequest::orderName()->whereIn('idrequestStatus',[2,3,4,5,6,7,10,11,12])->get() as $s)
						{
							if (isset($status) && $status == $s->idrequestStatus)
							{
								$option	=	$option->concat([["value" => $s->idrequestStatus, "description" => $s->description, "selected" => "selected"]]);
							}
							else
							{
								$option	=	$option->concat([["value" => $s->idrequestStatus, "description" => $s->description]]);
							}
						}
					@endphp
					@component("components.inputs.select",
					[
						"attributeEx"	=>	"type=\"text\" title=\"Estado de Solicitud\" name=\"status\"", 
						"classEx"		=>	"js-status",
						"options"		=>	$option
					])
					@endcomponent
				</div>
			@endslot
			@if(count($requests) > 0)
				@slot('export')
					<div class='flex flex-row justify-end'>
						@component("components.buttons.button", ["variant" => "success", "attributeEx" => "type=\"submit\" formaction=\"".route('movements-accounts.follow.excel')."\" formmethod=\"GET\"", "label" => "<span>Exportar a Excel</span><span class=\"icon-file-excel\"></span>"]) @endcomponent
					</div>
				@endslot
			@endif
		@endcomponent
	</div>
	@if(count($requests) > 0)
		@php
			$modelHead	=
			[
				[
					["value"	=>	"Folio"],
					["value"	=>	"Tipo"],
					["value"	=>	"Solicitante"],
					["value"	=>	"Elaborado por"],
					["value"	=>	"Estado"],
					["value"	=>	"Fecha de elaboración"],
					["value"	=>	"Empresa"],
					["value"	=>	"Clasificación de gastos"],
					["value"	=>	"Acción"]
				]
			];
			foreach($requests as $request)
			{
				if($request->status == 5 || $request->status == 6 || $request->status == 7  || $request->status == 10 || $request->status == 11 || $request->status == 12 || $request->status == 13)
				{
					$buttons	=
					[
						["kind"	=>	"components.buttons.button",	"buttonElement"	=>	"a",	"attributeEx"	=>	"alt=\"Nueva Solicitud\" title=\"Nueva Solicitud\" href=\"".route('movements-accounts.create.new',$request->folio)."\"",	"label"	=>	"<span class=\"icon-plus\"></span>",	"variant"	=>	"warning"],
						["kind"	=>	"components.buttons.button",	"buttonElement"	=>	"a",	"attributeEx"	=>	"alt=\"Ver Solicitud\" title=\"Ver Solicitud\" href=\"".route('movements-accounts.follow.edit',$request->folio)."\"",		"label"	=>	"<span class=\"icon-search\"></span>",	"variant"	=>	"secondary"],
					];
				}
				elseif($request->status == 3 || $request->status == 4 || $request->status == 5 || $request->status == 10 || $request->status == 11 || $request->status == 12 || $request->status == 13)
				{
					$buttons	=
					[
						["kind"	=>	"components.buttons.button",	"buttonElement"	=>	"a",	"attributeEx"	=>	"alt=\"Ver Solicitud\" title=\"Ver Solicitud\" href=\"".route('movements-accounts.follow.edit',$request->folio)."\"",	"label"	=>	"<span class=\"icon-search\"></span>", "variant"	=>	"secondary"],
					];
				}
				else
				{
					$buttons	=
					[
						["kind"	=>	"components.buttons.button",	"buttonElement"	=>	"a",	"attributeEx"	=>	"alt=\"Editar Solicitud\" title=\"Editar Solicitud\" href=\"".route('movements-accounts.follow.edit',$request->folio)."\"",	"label"	=>	"<span class=\"icon-pencil\"></span>",	"variant"	=>	"success"],
					];
				}
				$body	=
				[
					[
						"content"	=>	["label"	=>	$request->folio]
					],
					[
						"content"	=>	["label"	=>	$request->requestkind->kind]
					],
					[
						"content"	=>	["label"	=>	$request->requestUser()->exists() ? $request->requestUser->name.' '.$request->requestUser->last_name.' '.$request->requestUser->scnd_last_name : 'No hay']
					],
					[
						"content"	=>	["label"	=>	$request->elaborateUser->name.' '.$request->elaborateUser->last_name.' '.$request->elaborateUser->scnd_last_name]
					],
					[
						"content"	=>	["label"	=>	$request->statusrequest->description]
					],
					[
						"content"	=>	["label"	=>	Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$request->fDate)->format('d-m-Y')]
					],
					[
						"content"	=>	["label"	=>	"Varias"]
					],
					[
						"content"	=>	["label"	=>	"Varias"]
					],
					[
						"content"	=>	$buttons
					],
				];
				$modelBody[]	=	$body;
			}
		@endphp
		@component("components.tables.table",["modelHead" => $modelHead, "modelBody" => $modelBody, "classEx" => "table"]) @endcomponent
		{{$requests->appends($_GET)->links()}}
	@else
		@component('components.labels.not-found') @endcomponent
	@endif
@endsection

@section('scripts')
	<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
	<script src="{{ asset('js/jquery-ui.js') }}"></script>
	<script src="{{ asset('js/jquery.numeric.js') }}"></script>
	<script type="text/javascript"> 
		$(document).ready(function()
		{
			$('input[name="folio"]').numeric(false);
			@php
				$selects = collect([
					[
						"identificator"				=> ".js-kind",
						"placeholder"				=> "Seleccione el tipo de solicitud",
						"language"					=> "es",
						"maximumSelectionLength"	=> "1"
					],
					[
						"identificator"				=> ".js-status",
						"placeholder"				=> "Seleccione el estado",
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
