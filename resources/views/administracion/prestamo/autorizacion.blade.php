@extends('layouts.child_module')
@section('data')
	<div id="container-cambio" class="div-search">
		@component('components.labels.title-divisor') BUSCAR SOLICITUDES @endcomponent
		@php
			$values = ['enterprise_option_id' => $option_id, 'enterprise_id' => $enterpriseid, 'folio' => $folio, 'name' => $name, 'minDate' => $mindate, 'maxDate' => $maxdate, 'account' => $account];
		@endphp
		@component('components.forms.searchForm',[ "attributeEx" => "id=\"formsearch\"", "values" => $values])
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
						foreach (App\StatusRequest::orderName()->whereIn('idrequestStatus',[4,8])->get() as $s)
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
							formaction="{{ route('loan.export.authorization') }}"
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
					["value" => "Acción"]
				]
			];
			foreach($requests as $request)
			{
				$body = [
					[
						"content"	=>
						[
							"label" => $request->folio
						]
					],
					[
						"content"	=>
						[
							"label" => $request->loan->first()->title != null ? htmlentities($request->loan->first()->title) : 'No hay'
						]
					]
				];
				foreach(App\User::where('id',$request->idRequest)->get() as $user)
				{
					array_push($body,[ "content" => 
						[
							"label" => $user->name.' '.$user->last_name.' '.$user->scnd_last_name
						]
					]);
				}
				foreach(App\User::where('id',$request->idElaborate)->get() as $elaborate)
				{
					array_push($body,[ "content" => 
						[
							"label" => $elaborate->name.' '.$elaborate->last_name.' '.$elaborate->scnd_last_name
						]
					]);
				}
				array_push($body,[ "content" => 
					[
						"label" =>  $request->statusrequest->description
					]
				]);
				array_push($body,[ "content" => 
					[
						"label" => Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$request->reviewDate)->format('d-m-Y H:i')
					]
				]);
				array_push($body,[ "content" => 
					[
						"label" => isset($request->reviewedEnterprise->name) ? $request->reviewedEnterprise->name : "No hay"
					]
				]);
				array_push($body,[ "content" => 
					[
						"label" => isset($request->accountsReview->account) ? $request->accountsReview->account.' '.$request->accountsReview->description : "No hay"
					]
				]);
				if($request->status == 8)
				{
					array_push($body,[ "content" =>
						[
							[
								"kind" 			=> "components.buttons.button",
								"variant"		=> "dark-red",
								"buttonElement" => "a",
								"attributeEx"	=> "title=\"Descargar PDF\" href=\"".route('loan.authorization.downloaddocument',$request->folio)."\"",
								"label"			=> "PDF"
							],
							[
								"kind" 			=> "components.buttons.button",
								"variant"		=> "success",
								"buttonElement" => "a",
								"attributeEx"	=> "alt=\"Editar Solicitud\" title=\"Editar Solicitud\" href=\"".route('loan.authorization.edit',$request->folio)."\"",
								"label"			=> "<span class=\"icon-pencil\"></span>"
							]
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
							"attributeEx"	=> "alt=\"Editar Solicitud\" title=\"Editar Solicitud\" href=\"".route('loan.authorization.edit',$request->folio)."\"",
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
			generalSelect({'selector':'.js-account', 'depends':'.js-enterprise', 'model':11});
			$(document).on('change','.js-enterprise',function()
			{
				$('.js-account').empty();
			});
		});
    </script> 
@endsection

