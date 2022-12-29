@extends('layouts.child_module')
@section('data')
	@component("components.forms.form", ["attributeEx" => "method=\"GET\" action=\"".route('account.search')."\" id=\"formsearch\""])
	@component("components.labels.title-divisor") BUSCAR CUENTAS @endcomponent
		@component("components.containers.container-form")
			<div class="col-span-2">
				@component("components.labels.label") Número de cuenta: @endcomponent
				@component("components.inputs.input-text")
					@slot("attributeEx")
						type="text" name="accountNumber" value="{{ isset($accountNumber) ? $accountNumber : '' }}" id="input-search" placeholder="Ingrese el número de cuenta"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Cuenta: @endcomponent
				@component("components.inputs.input-text")
					@slot("attributeEx")
						type="text" name="acc" value="{{ isset($acc) ? $acc : '' }}" id="input-search" placeholder="Ingrese el nombre de la cuenta"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Empresa: @endcomponent
				@php
					$options = collect();
					foreach(App\Enterprise::orderName()->get() as $enterprise)
					{
						if(isset($enterpriseid) && $enterpriseid == $enterprise->id)
						{
							$options =  $options->concat([["value" => $enterprise->id, "description" => strlen($enterprise->name) >= 35 ? substr(strip_tags($enterprise->name),0,40).'...' : $enterprise->name, "selected" => "selected"]]);
						}
						else
						{
							$options =  $options->concat([["value" => $enterprise->id, "description" => strlen($enterprise->name) >= 35 ? substr(strip_tags($enterprise->name),0,40).'...' : $enterprise->name]]);
						}
					}
				@endphp
				@component("components.inputs.select", ["classEx" => "js-enterprise", "options" => $options])
					@slot("attributeEx")
						title="Empresa" name="enterpriseid"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2 md:col-span-4 space-x-2 text-center md:text-left flex">
				@component("components.buttons.button-search", ["attributeEx" => $attributeExButtonSearch??'', "classEx" => $classExButtonSearch??'']) @endcomponent
				@component("components.buttons.button", ["buttonElement" => "a", "variant" => "reset", "classEx" => "bg-gray-200 px-7 py-2 rounded cursor-pointer hover:bg-gray-200 uppercase font-bold text-sm h-9 text-blue-gray-700", "attributeEx" => "href=\"".strtok($_SERVER['REQUEST_URI'], '?')."\""])Borrar campos @endcomponent
			</div>
		@endcomponent
	@endcomponent
	@if(count($accounts) > 0)
		@php
			$modelHead = 
			[
				[
					["value" => "ID"],
					["value" => "Número de Cuenta"],
					["value" => "Cuenta"],
					["value" => "Empresa"],
					["value" => "Acciones"]
				]
			];
			foreach($accounts as $account)
			{
				$body = 
				[
					[					
						"content" => 
						[
							[
								"kind"		=> "components.labels.label",
								"label"	=> $account->idAccAcc
							]
						]
					],
					[
						"content" => 
						[
							[
								"kind"		=> "components.labels.label",
								"label"	=> $account->account
							]
						]
					],
					[
						"content" => 
						[
							[
								"kind"		=> "components.labels.label",
								"label"	=> htmlentities($account->description),
							]
						]
					],
					[
						"content" => 
						[
							[
								"kind"		=> "components.labels.label",
								"label"	=> $account->enterprise->name
							]
						]
					],
					[
						"content" => 
						[
							[
								"kind"			=> "components.buttons.button",
								"buttonElement"	=> "a",
								"variant"		=> "success",
								"attributeEx"	=> "title=\"Editar Cuenta\" href=\"".route('account.edit',$account->idAccAcc)."\"",
								"label" 		=> "<span class=\"icon-pencil\"></span>"
							]
						]
					]
				];
				$modelBody[] = $body;
			}
		@endphp
		@Table(["modelHead" => $modelHead, "modelBody" => $modelBody, "attributeEx" => "id=\"table\""]) @endTable
		{{ $accounts->appends($_GET)->links() }}
	@else
		@component("components.labels.not-found") @endcomponent
	@endif
@endsection

@section('scripts')
	<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
	<script src="{{ asset('js/jquery-ui.js') }}"></script>
	<script src="{{ asset('js/jquery.numeric.js') }}"></script>
	<script>
		$(document).ready(function()
		{
			@php
				$selects = collect(
					[
						[
							"identificator"          => ".js-enterprise", 
							"placeholder"            => "Seleccione la empresa", 
							"language"				 => "es",
							"maximumSelectionLength" => "1"
						]
					]
				);
			@endphp
			@component("components.scripts.selects",["selects" => $selects])@endcomponent

			$('input[name="accountNumber"]').numeric(false);
		});
	</script>
@endsection