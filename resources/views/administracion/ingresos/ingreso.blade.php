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
		@component('components.forms.searchForm', ["attributeEx" => "id=\"formsearch\"", "values"=>$values])
			@slot('contentEx')
				<div class="col-span-2">
					@component('components.labels.label')Estado de Solicitud @endcomponent
					@php
						$optionsState	=	[];
						foreach (App\StatusRequest::orderName()->whereIn('idrequestStatus',[5,13,21])->get() as $s)
						{
							if (isset($status) && $status == $s->idrequestStatus)
							{
								$optionsState[]	=	["value"	=>	$s->idrequestStatus, "description"	=>	$s->description, "selected"		=>	"selected"];
							}
							else
							{
								$optionsState[]	=	["value"	=>	$s->idrequestStatus,	"description"	=>	$s->description];
							}
						}
					@endphp
					@component('components.inputs.select', ["options" => $optionsState])
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
					<div class="flex flex-row justify-end">
						@component('components.buttons.button',["variant" => "success"])
							@slot('classEx')
								mt-4
							@endslot
							@slot('attributeEx')
								type=submit formaction={{ route('income.export.authorized') }}
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
					["value"	=>	"Saldo pendiente"],
					["value"	=>	"Acción"]
				]
			];
			foreach($requests as $request)
			{
				$userNameRequest	=	"";
				$userNameElaborate	=	"";
				$enterpriseRequest	=	"";
	
				if ($request->idRequest == "")
				{
					$userNameRequest	=	"No hay solicitante";
				}
				else
				{
					foreach (App\User::where('id',$request->idRequest)->get() as $user)
					{
						$userNameRequest	.=	$user->name." ".$user->last_name." ".$user->scnd_last_name;
					}
				}
				foreach (App\User::where('id',$request->idElaborate)->get() as $user)
				{
					$userNameElaborate	.=	$user->name." ".$user->last_name." ".$user->scnd_last_name;
				}
				if (isset($request->reviewedEnterprise->name))
				{
					$enterpriseRequest	=	$request->reviewedEnterprise->name;
				}
				else if (isset($request->reviewedEnterprise->name) == false && isset($request->requestEnterprise->name))
				{
					$enterpriseRequest	=	$request->requestEnterprise->name;
				}
				else
				{
					$enterpriseRequest	=	"No hay";
				}
				$outstandingBalance = $request->income->first()->amount;
				if($request->taxPayment == 1)
				{
					$outstandingBalance -= (App\Bill::where('folioRequest',$request->folio)->whereIn('status',[0,1,2])->where(function($q){$q->where('statusCFDI','Vigente')->orWhereNull('statusCFDI');})->where("type","!=","E")->sum('total') - App\Bill::where('folioRequest',$request->folio)->whereIn('status',[0,1,2])->where(function($q){$q->where('statusCFDI','Vigente')->orWhereNull('statusCFDI');})->where("type","E")->sum('total'));
				}
				else
				{
					$outstandingBalance -= $request->billNF->sum('total');
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
						"content"	=>	["label"	=>	$userNameRequest]
					],
					[
						"content"	=>	["label"	=>	$userNameElaborate]
					],
					[
						"content"	=>	["label"	=>	$request->statusrequest->description]
					],
					[
						"content"	=>	["label"	=>	Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$request->fDate)->format('d-m-Y')]
					],
					[
						"content"	=>	["label"	=>	$enterpriseRequest]
					],
					[
						"content"	=>	["label"	=>	"$ ".number_format($outstandingBalance,2)]
					],
					[
						"content"	=>
						[
							[
								"kind"			=>	"components.buttons.button",
								"variant"		=>	"warning",
								"buttonElement"	=>	"a",
								"label"			=>	"<span class='icon-plus'></span>",
								"attributeEx"	=>	"type=\"button\" title=\"Ingresos\" href=\"".route('income.projection.income',$request->folio)."\"",
							],
							[
								"kind"			=>	"components.buttons.button",
								"variant"		=>	"red",
								"label"			=>	"<span class='icon-bin'></span>",
								"buttonElement"	=>	"a",
								"attributeEx"	=>	"type=\"button\" title=\"Marcar como Incobrable\" href=\"".route('income.projection.bad',$request->folio)."\""
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
		@component('components.labels.not-found')
			@slot('attributeEx')
				id="not-found"
			@endslot
		@endcomponent
	@endif
@endsection

@section('scripts')
<script type="text/javascript"> 
	$(document).ready(function()
	{
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
</script>
@endsection
