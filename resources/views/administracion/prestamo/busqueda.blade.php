@extends('layouts.child_module')  
@section('data')
	<div id="container-cambio" class="div-search">
		@component('components.labels.title-divisor') BUSCAR SOLICITUDES @endcomponent
		@php
			$values = ['enterprise_option_id' => $option_id, 'enterprise_id' => $enterpriseid, 'folio' => $folio, 'name' => $name, 'minDate' => $mindate, 'maxDate' => $maxdate, 'account' => $account];
 		@endphp
		@component('components.forms.searchForm', [ "attributeEx" => "id=\"formsearch\"", "values" => $values])
			@slot('contentEx')
				<div class="col-span-2">
					@component('components.labels.label') Cuenta: @endcomponent
					@php
						$optionsAccount = [];
						$a = App\Account::where('selectable',1)->where('idEnterprise',$enterpriseid)->where('description','FUNCIONARIOS Y EMPLEADOS')->first();
						if (isset($account) && $account == $a->idAccAcc)
						{
							$optionsAccount[] = ["value" => $a->idAccAcc, "description" => $a->account." - ". $a->description."(".$a->content.")", "selected" => "selected"];
						}
					@endphp
					@component('components.inputs.select', ['options' => $optionsAccount])
						@slot('attributeEx')
							title="Cuenta" multiple="multiple" name="account"
						@endslot
						@slot('classEx')
							js-account removeselect
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Estado: @endcomponent
					@php
						$optionStatus = [];
						foreach (App\StatusRequest::orderName()->whereIn('idrequestStatus',[2,3,4,5,6,7,8,10,11,12])->get() as $s)
						{
							if (isset($status) && $status == $s->idrequestStatus)
							{
								$optionStatus[] = ["value" => $s->idrequestStatus, "description" => $s->description, "selected" => "selected"];
							}
 							else
							{
								$optionStatus[] = ["value" => $s->idrequestStatus, "description" => $s->description];
							}
						}
					@endphp
					@component('components.inputs.select', ['options' => $optionStatus])
						@slot('attributeEx')
							title="Solicitud de Estado" name="status" multiple="multiple"
						@endslot
						@slot('classEx')
							js-status
						@endslot
					@endcomponent
				</div>
			@endslot
			@if(count($requests) > 0)
				@slot('export')
					@component('components.buttons.button',['variant' => 'success'])
						@slot('classEx')
							export
						@endslot
						@slot('attributeEx')
							type="submit"
							formaction="{{ route('loan.export.follow') }}"
						@endslot		
						@slot('slot')
							<span>Exportar a Excel</span> <span class='icon-file-excel'> </span>
						@endslot			
					@endcomponent
				@endslot
			@endif
		@endcomponent
	</div>
	@if(count($requests) > 0)
		@php
			$body		= [];
			$modelBody	= [];
			$modelHead	= [
				[
					["value" => "Folio"],
					["value" => "Título"],
					["value" => "Solicitante"],
					["value" => "Elaborado por"],
					["value" => "Estado"],
					["value" => "Fecha de elaboración"],
					["value" => "Empresa"],
					["value" => "Clasificación del gasto"],
					["value" => "Acciones"]
				]
			];
			foreach($requests as $request)
			{
				$body = [
					[
						"content" =>
						[
							"label" => $request->folio
						]
					],
					[
						"content" =>
						[
							"label" => isset($request->loan->first()->title) ? htmlentities($request->loan->first()->title) : 'No hay'
						]
					]
				];
				if($request->idRequest == "")
				{
					array_push($body,[ "content" => 
						[
							"label" => "No hay solicitante"
						]
					]);	
				}
				else
				{
					foreach(App\User::where('id',$request->idRequest)->get() as $user)
					{
						array_push($body,[ "content" => 
							[
								"label" => $user->name.' '.$user->last_name.' '.$user->scnd_last_name
							]
						]);	
					}
				}
				foreach(App\User::where('id',$request->idElaborate)->get() as $user)
				{
					array_push($body,[ "content" => 
						[
							"label" => $user->name.' '.$user->last_name.' '.$user->scnd_last_name
						]
					]);	
				}
				array_push($body,[ "content" => 
					[
						"label" => isset($request->statusrequest) ? $request->statusrequest->description : '---'
					]
				]);
				array_push($body,[ "content" => 
					[
						"label" => Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$request->fDate)->format('d-m-Y H:i')
					]
				]);
				if(isset($request->reviewedEnterprise->name))
				{
					array_push($body,[ "content" => 
						[
							"label" => $request->reviewedEnterprise->name
						]
					]);
				}
				elseif(isset($request->reviewedEnterprise->name) == false && isset($request->requestEnterprise->name))
				{
					array_push($body,[ "content" => 
						[
							"label" => $request->requestEnterprise->name
						]
					]);
				}
				else
				{
					array_push($body,[ "content" => 
						[
							"label" => "No hay"
						]
					]);
				}
				if(isset($request->accountsReview->account))
				{
					array_push($body,[ "content" => 
						[
							"label" => $request->accountsReview->account.' '.$request->accountsReview->description
						]
					]);
				}
				elseif(isset($request->accountsReview->account) == false && isset($request->accounts->account))
				{
					array_push($body,[ "content" => 
						[
							"label" => $request->accounts->account.' '.$request->accounts->description
						]
					]);
				}
				else
				{
					array_push($body,[ "content" => 
						[
							"label" => "No hay"
						]
					]);
				}

				if($request->status == 5 || $request->status == 6 || $request->status == 7  || $request->status == 10 || $request->status == 11 || $request->status == 13) 
				{
					array_push($body,[ "content" =>
						[
							[
								"kind" 			=> "components.buttons.button",
								"variant"		=> "warning",
								"buttonElement" => "a",
								"attributeEx"	=> "alt=\"Nueva Solicitud\" title=\"Nueva Solicitud\" href=\"".route('loan.create.new',$request->folio)."\"",
								"label"			=> "<span class=\"icon-plus\"></span>"
							],
							[
								"kind" 			=> "components.buttons.button",
								"variant"		=> "secondary",
								"buttonElement" => "a",
								"attributeEx"	=> "alt=\"Ver Solicitud\" title=\"Ver Solicitud\" href=\"".route('loan.follow.edit',$request->folio)."\"",
								"label"			=> "<span class=\"icon-search\"></span>"
							]
						]
					]);
				}
				elseif($request->status == 3 || $request->status == 4 || $request->status == 5 || $request->status == 8  || $request->status == 10 || $request->status == 11 || $request->status == 12)
				{
					array_push($body,[ "content" =>
						[
							"kind" 			=> "components.buttons.button",
							"variant"		=> "secondary",
							"buttonElement" => "a",
							"attributeEx"	=> "alt=\"Ver Solicitud\" title=\"Ver Solicitud\" href=\"".route('loan.follow.edit',$request->folio)."\"",
							"label"			=> "<span class=\"icon-search\"></span>"
						]
					]);
				}
				else
				{
					array_push($body,[ "content" =>
						[
							"kind" 			=> "components.buttons.button",
							"variant"		=> "success",
							"buttonElement" => "a",
							"attributeEx"	=> "alt=\"Editar Solicitud\" title=\"Editar Solicitud\" href=\"".route('loan.follow.edit',$request->folio)."\"",
							"label"			=> "<span class=\"icon-pencil\"></span>"
						]
					]);
				}
				$modelBody[] = $body;
			}
		@endphp
		@component('components.tables.table', [
			"modelBody" => $modelBody,
			"modelHead" => $modelHead,
		])
		@endcomponent
		{{ $requests->appends($_GET)->links() }}
	@else
		@component('components.labels.not-found') No hay solicitudes @endcomponent
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
			@php
				$selects = collect([
					[
						"identificator"				=> ".js-enterprise",
						"placeholder"				=> "Seleccione la empresa",
						"maximumSelectionLength"	=> "1"
					],
					[
						"identificator"				=> ".js-status",
						"placeholder"				=> "Seleccione un estatus",
						"maximumSelectionLength"	=> "1"
					]
				]);
			@endphp
			@component('components.scripts.selects',["selects" => $selects]) @endcomponent
			generalSelect({'selector':'.js-account','depends':'.js-enterprise','model':11});
			$(document).on('change','.js-enterprise',function()
			{
				$('.js-account').empty();
			});
		});
	</script>
@endsection
