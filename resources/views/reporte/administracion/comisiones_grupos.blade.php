@extends('layouts.child_module')
@section('data')
		@component("components.labels.title-divisor") BUSCAR SOLICITUDES @endcomponent
		@php
			$values	= ["folio" => $folio, "title_request" => $title_request];
			$hidden	= ['enterprise','name','rangeDate'];
		@endphp
		@component("components.forms.searchForm",["attributeEx" => "id=\"formsearch\"","values" => $values, "hidden" => $hidden])
			@slot("contentEx")
				<div class="col-span-2">
					@component("components.labels.label") Título: @endcomponent
					@component("components.inputs.input-text")
						@slot("attributeEx")
							name		="title_request" 
							id			= "input-search" 
							placeholder	= "Ingrese un título"
							value		= "{{ isset($values["title_request"]) ? $values["title_request"] : "" }}"
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component("components.labels.label") Tipo de Operación: @endcomponent
					@php
						$options = collect();
						
						if (isset($operation) && in_array('Entrada', $operation))
						{
							$options = $options->concat([["value"=> "Entrada", "selected"=>"selected", "description" => "Entrada"]]);
						}
						else
						{
							$options = $options->concat([["value"=> "Entrada", "description"=> "Entrada"]]);
						}

						if (isset($operation) && in_array('Salida', $operation))
						{
							$options = $options->concat([["value"=> "Salida", "selected"=>"selected", "description" => "Salida"]]);
						}
						else
						{
							$options = $options->concat([["value"=> "Salida", "description"=> "Salida"]]);
						}
						$attributeEx	= "name=\"operation[]\" title=\"Operación\" multiple=\"multiple\"";
						$classEx		= "js-operation";
					@endphp
					@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])@endcomponent
				</div>
			@endslot
			@if (count($requests) > 0)
				@slot("export")
					<div class="float-right">
						@component('components.labels.label')
							@component('components.buttons.button',['variant' => 'success'])
								@slot('attributeEx') 
									type=submit 
									formaction={{ route('report.group.commissions.excel') }} @endslot
								@slot('label')
									<span>Exportar a Excel</span><span class="icon-file-excel"></span> 
								@endslot
							@endcomponent
						@endcomponent
					</div>
				@endslot
			@endif
		@endcomponent

		@if (count($requests) > 0)
			@php
				$body		= [];
				$modelBody	= [];
				$modelHead	= 
				[
					[
						["value"	=> "Folio"],
						["value"	=> "Título"],
						["value"	=> "Tipo de operación"],
						["value"	=> "Importe Total"],
						["value"	=> "Comisión"],
						["value"	=> "Importe a retomar"]
					]
				];

				foreach($requests as $req)
				{
					$body = 
					[
						[
							"content" =>
							[
								"label" => $req->folio,
							]
						],
						[
							"content" =>
							[
								"label" => $req->groups()->exists() ? htmlentities($req->groups->first()->title).' '.$req->groups->first()->datetitle : '',
							]
						],
						[
							"content" =>
							[
								"label" => $req->groups()->exists() ? $req->groups->first()->operationType : '',
							]
						],
						[
							"content" =>
							[
								"label" => $req->groups()->exists() ? "$".number_format($req->groups->first()->amountMovement,2) : "$".number_format('0'),
							]
						],
						[
							"content" =>
							[
								"label" => $req->groups()->exists() ? "$".number_format($req->groups->first()->commission,2) : "$".number_format('0'),
							]
						],
						[
							"content" =>
							[
								"label" => $req->groups()->exists() ? "$".number_format($req->groups->first()->amountRetake,2) : "$".number_format('0'),
							]
						]
					];

					$modelBody[] = $body;
				}
			@endphp
			@component("components.tables.table",[
				"modelHead"	=> $modelHead,
				"modelBody"	=> $modelBody,
			])
			@endcomponent
			{{ $requests->appends($_GET)->links() }}
		@else
			@component("components.labels.not-found")@endcomponent
		@endif
	@endsection	
@section('scripts')
	<script src="{{ asset('js/jquery.numeric.js') }}"></script>
	<script type="text/javascript"> 
		$(document).ready(function()
		{
			@php
				$selects = collect([
					[
						"identificator"			=> ".js-operation",
						"placeholder"			=> "Seleccione una operación",
						"languaje"				=> "es",
					]
				]);
			@endphp
			@component("components.scripts.selects",["selects" => $selects]) @endcomponent
		});
	</script> 
@endsection
