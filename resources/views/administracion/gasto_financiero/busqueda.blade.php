@extends('layouts.child_module')
  
@section('data')
	<div id="container-cambio" class="div-search">
		@component("components.labels.title-divisor")
			BUSCAR SOLICITUDES
		@endcomponent
		@php
			$values	=
			[
				'enterprise_option_id'	=>	isset($option_id) ? $option_id : "",
				'folio'					=>	isset($folio) ? $folio : '',
				'enterprise_id'			=>	isset($enterpriseid) ? $enterpriseid : "",
				'minDate'				=>	isset($mindate) ? $mindate : '',
				'maxDate'				=>	isset($maxdate) ? $maxdate : '',
				'name'					=>	isset($name) ? $name : '',
			];
		@endphp
		@component('components.forms.searchForm', ["attributeEx" => "id=\"formsearch\"", "values" => $values])
			@slot('contentEx')
				<div class="col-span-2">
					@php
						$options	=	collect();
						if(isset($enterpriseid) && isset($account))
						{
							$acc			= App\Account::find($account);
							$description	= $acc->account." - ".$acc->description." ".$acc->content;
							$options		= $options->concat([['value'=>$acc->idAccAcc, 'selected'=>'selected', 'description'=>$description]]);
						}
						$classEx		=	"js-account";
						$attributeEx	=	"title=\"Cuenta\" name=\"account\" multiple=\"multiple\"";
					@endphp
					@component("components.labels.label")
						Cuenta:
					@endcomponent
					@component("components.inputs.select", ["classEx" => $classEx, "attributeEx" => $attributeEx, "options" => $options]) @endcomponent
				</div>
				@if(!isset($action))
					<div class="col-span-2">
						@php
							$options	=	collect();
								foreach(App\StatusRequest::orderName()->whereIn('idrequestStatus',[2,3,4,5,6,7,10,11,12])->get() as $s)
								{
									if(isset($status) && $status == $s->idrequestStatus)
									{
										$options	=	$options->concat([['value'=>$s->idrequestStatus, 'selected'=>'selected', 'description'=>$s->description]]);
									}
									else
									{
										$options = $options->concat([['value'=>$s->idrequestStatus, 'description'=>$s->description]]);
									}
								}
							$classEx		=	"js-status";
							$attributeEx	=	"title=\"Estado de Solicitud\" name=\"status\" multiple=\"multiple\"";
						@endphp
						@component("components.labels.label")
							Estado:
						@endcomponent
						@component("components.inputs.select", ["classEx" => $classEx, "attributeEx" => $attributeEx, "options" => $options]) @endcomponent
					</div>
				@endif
			@endslot
			@if (count($requests) > 0)
				@slot('export')
					@php
						if(!isset($action))
						{
							$formaction	=	'formaction="'.route("finance.export","follow").'"';
						} 
						else
						{
							$formaction	=	'formaction="'.route("finance.export",$action).'"';
						}
					@endphp
					@component('components.labels.label')
						@component("components.buttons.button", ["attributeEx" => "type=submit ".$formaction, "variant" => "success"]) 
							@slot("classEx")
								export mt-4
							@endslot
							@slot('slot')
								Exportar a Excel <span class='icon-file-excel'></span>
							@endslot
						@endcomponent
					@endcomponent
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
					["value"	=>	"Título"],
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
				$date	=	Carbon\Carbon::parse($request->fDate)->format('d-m-Y');
			if (isset($request->reviewedEnterprise->name))
			{
				$enterprice	=	$request->reviewedEnterprise->name;
			}
			elseif(isset($request->reviewedEnterprise->name) == false && isset($request->requestEnterprise->name))
			{
				$enterprice	=	$request->requestEnterprise->name;
			}
			else
			{
				$enterprice = "No hay";
			}	
			if(isset($request->accountsReview->account))
			{
				$acount	=	$request->accountsReview->account.' '.$request->accountsReview->description;
			}
			elseif(isset($request->accountsReview->account) == false && isset($request->accounts->account))
			{
				$acount	=	$request->accounts->account.' '.$request->accounts->description;
			}
			else
			{
				$acount	=	"No hay";
			}
			$newRequest	=	[];
			if(!isset($action))
			{
				if($request->status == 5 || $request->status == 6 || $request->status == 7  || $request->status == 10 || $request->status == 11 || $request->status == 13) 
				{
					$newRequest	=
					[
						"content"	=>
						[
							["kind"	=>	"components.buttons.button","classEx" => "load-actioner", "attributeEx" => "alt=\"Nueva Solicitud\" title=\"Nueva Solicitud\"  href=\"".route("finance.create.new",$request->folio)."\"", "variant"	=>	"warning", "buttonElement" => "a", "label" => "<span class='icon-plus'></span>"],
							["kind"	=>	"components.buttons.button","classEx" => "load-actioner", "attributeEx" => "alt=\"Ver Solicitud\" title=\"Ver Solicitud\" href=\"".route("finance.show",$request->folio)."\"", "variant"	=>	"secondary", "buttonElement" => "a", "label" => "<span class='icon-search'></span>"],
						]
					];
				}
				elseif($request->status == 3 || $request->status == 4 || $request->status == 5 || $request->status == 10 || $request->status == 11  || $request->status == 12) 
				{
					$newRequest	=
					[
						"content"	=>
						[
							"kind"	=>	"components.buttons.button","classEx" => "load-actioner", "attributeEx" => "alt=\"Ver Solicitud\" title=\"Ver Solicitud\" href=\"".route("finance.show",$request->folio)."\"", "buttonElement" => "a", "label" => "<span class='icon-search'></span>"
						]
					];
				}
				else
				{
					$newRequest = 
					[
						"content" =>
						[
							"kind"	=>	"components.buttons.button","classEx" => "load-actioner", "attributeEx" => "alt=\"Editar Solicitud\" title=\"Editar Solicitud\" href=\"".route("finance.edit",$request->folio)."\"","variant" => "success", "buttonElement" => "a", "label" => "<span class='icon-pencil'></span>"
						]
					];
				}
			}
			elseif($action=='review')
			{
				$newRequest	=
				[
					"content"	=>
					[
						"kind"	=>	"components.buttons.button","classEx" => "load-actioner", "attributeEx" => "title=\"Editar Solicitud\" href=\"".route("finance.review.edit",$request->folio)."\"", "variant" => "success", "buttonElement" => "a", "label" => "<span class='icon-pencil'></span>"
					]
				];
			}
			elseif($action=='authorization')
			{
				$newRequest	=
				[
					"content"	=>
					[
						"kind"	=>	"components.buttons.button","classEx" => "load-actioner", "attributeEx" => "title=\"Editar Solicitud\" href=\"".route("finance.authorization.edit",$request->folio)."\"", "variant" => "success", "buttonElement" => "a", "label" => "<span class='icon-pencil'></span>"
					]
				];
			}
			$body = 
			
				[
					[
						"content"	=>	["label" => $request->folio]
					],
					[
						"content"	=>	["label"	=>	$request->finance()->exists() && $request->finance->title != null ? htmlentities($request->finance->title) : 'No hay']
					],
					[ 
						"content"	=>	["label"	=>	$request->requestUser()->exists() ? $request->requestUser->name.' '.$request->requestUser->last_name.' '.$request->requestUser->scnd_last_name : 'No hay']
					],
					[ 
						"content"	=>	["label"	=>	$request->elaborateUser()->exists() ? $request->elaborateUser->name.' '.$request->elaborateUser->last_name.' '.$request->elaborateUser->scnd_last_name : 'No hay']
					],
					[ 
						"content"	=>	["label"	=>	$request->statusrequest->description]
					],
					[ 
						"content"	=>	["label"	=>	$date]
					],
					[ 
						"content"	=>	["label"	=>	$enterprice]
					],
					[ 
						"content"	=>	["label"	=>	$acount]
					],
					$newRequest
				];
				$modelBody[] = $body;
			}
		@endphp
		@component("components.tables.table", ["modelBody" => $modelBody, "modelHead" => $modelHead]) @endcomponent
		{{ $requests->appends($_GET)->links() }}
	@else
		@component("components.labels.not-found")
			No hay solicitudes
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
			generalSelect({'selector': '.js-account', 'depends': '.js-enterprise', 'model': 3});
			@php
				$selects	= collect([
					[
						"identificator"				=> ".js-enterprise",
						"placeholder"				=> "Seleccione la empresa",
						"maximumSelectionLength"	=> "1"
					],
					[
						"identificator"				=> ".js-status",
						"placeholder"				=> "Seleccione un estado",
						"maximumSelectionLength"	=> "1"
					],
				]);
			@endphp
			@component('components.scripts.selects', ["selects" => $selects])
			@endcomponent
			$(document).on('change','.js-enterprise',function()
			{
				$('.js-account').empty();
			});
		});
		@if(isset($alert))
			{!! $alert !!}
		@endif
	</script>
@endsection
