@extends('layouts.child_module')
  
@section('data')
	<div id="container-cambio" class="div-search">
		@component('components.labels.title-divisor') BUSCAR SOLICITUDES @endcomponent
		@php
			$values	=
			[
				'folio'		=>	isset($folio) ? $folio : '',
				'minDate'	=>	isset($mindate) ? date('d-m-Y',strtotime($mindate)) : '',
				'maxDate'	=>	isset($maxdate) ? date('d-m-Y',strtotime($maxdate)) : '',
				'name'		=>	isset($name) ? $name : ''
			];
			$hidden	=
			[
				'enterprise'
			];
		@endphp
		@component('components.forms.searchForm', ["attributeEx" => "id=\"formsearch\"", "values" => $values, "hidden" => $hidden])
			@slot('contentEx')
				<div class="col-span-2">
					@component('components.labels.label') Tipo de solicitud: @endcomponent
					@php
						foreach(App\RequestKind::orderName()->whereIn('idrequestkind',[11,12,13,14,15])->get() as $k)
						{
							if(isset($kind) && $kind == $k->idrequestkind)
							{
								$optionsEnterprises[]	=	["value"	=>	$k->idrequestkind,	"description"	=>	$k->kind,	"selected"		=>	"selected"];
							}
							else 
							{
								$optionsEnterprises[]	=	["value"			=>	$k->idrequestkind,	"description"	=>	$k->kind];
							}
						}
					@endphp
					@component('components.inputs.select',["options" => $optionsEnterprises])
						@slot('attributeEx')
							title="Tipo de Solicitud" name="kind" multiple="multiple"
						@endslot
						@slot('classEx')
							js-kind
						@endslot
					@endcomponent
				</div>
			@endslot
			@if(count($requests) > 0)
				@slot('export')
					@component('components.inputs.input-text')
						@slot('attributeEx')
							type="hidden" value="facturación" name="type"
						@endslot
					@endcomponent
					@component('components.buttons.button', ['variant' => 'success'])
						@slot('classEx')
							mt-4
						@endslot
						@slot('slot')
							Exportar a Excel <span class='icon-file-excel'></span>
						@endslot
						@slot('attributeEx')
							type='submit' formaction="{{ route('movements-accounts.billing.excel') }}" formmethod="GET"
						@endslot
					@endcomponent
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
					["value"	=>	"Tipo"],
					["value"	=>	"Solicitante"],
					["value"	=>	"Elaborado por"],
					["value"	=>	"Estado"],
					["value"	=>	"Fecha de elaboración"],
					["value"	=>	"Empresa"],
					["value"	=>	"Clasificación del gasto"],
					["value"	=>	"Acción"],
				]
			];
			foreach($requests as $request)
			{
				$body	=
				[
					[
						"content"	=>	["label"	=>	$request->folio ]
					],
					[
						"content"	=>	["label"	=>	$request->requestkind->kind ]
					],
					[
						"content"	=>	["label"	=>	$request->requestUser()->exists() ? $request->requestUser->name.' '.$request->requestUser->last_name.' '.$request->requestUser->scnd_last_name : 'No hay' ]
					],
					[
						"content"	=>	["label"	=>	$request->elaborateUser->name.' '.$request->elaborateUser->last_name.' '.$request->elaborateUser->scnd_last_name ]
					],
					[
						"content"	=>	["label"	=>	$request->statusrequest->description ]
					],
					[
						"content"	=>	["label"	=>	 Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$request->fDate)->format('d-m-Y')]
					],
					[
						"content"	=>	["label"	=>	"Varias"]
					],
					[
						"content"	=>	["label"	=>	"Varias"]
					],
					[
						"content"	=>
						[
							[
								"kind"			=>	"components.buttons.button",
								"buttonElement"	=>	"a",
								"variant"		=>	"success",
								"label"			=>	"<span class='icon-pencil'></span>",
								"attributeEx"	=>	"title=\"Agregar factura\" href=\"".route('movements-accounts.billing.edit',$request->folio)."\""
							]
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
<script src="{{ asset('js/jquery.numeric.js') }}"></script>
<script type="text/javascript"> 
	$(document).ready(function()
	{
		$('input[name="folio"]',).numeric(false);
		$(function() 
		{
			$( ".datepicker" ).datepicker({ maxDate: 0, dateFormat: "dd-mm-yy" });
		});
		@php
			$selects = collect([
				[
					"identificator"	=>	".js-kind",
					"placeholder"	=>	"Seleccione el tipo de solicitud",
					"language"		=>	"es"
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
