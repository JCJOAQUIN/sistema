@extends('layouts.child_module')
@section('data')
	@component("components.forms.form",["attributeEx" => "method=\"GET\" action=\"".route('bank.acount.search')."\" id=\"formsearch\""])
	@component("components.labels.title-divisor") BUSCAR CUENTAS BANCARIAS @endcomponent
		@component("components.containers.container-form")
			<div class="col-span-2">
				@component("components.labels.label") Alias: @endcomponent
				@component("components.inputs.input-text")
					@slot("attributeEx")
						type="text" name="alias" value="{{ isset($alias) ? $alias : '' }}" id="input-search" placeholder="Ingrese el alias"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") CLABE: @endcomponent
				@component("components.inputs.input-text")
					@slot("attributeEx")
						type="text" name="clabe" value="{{ isset($clabe) ? $clabe : '' }}" id="input-search" placeholder="Ingrese la CLABE"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Cuenta: @endcomponent
				@component("components.inputs.input-text")
					@slot("attributeEx")
						type="text" name="account" value="{{ isset($account) ? $account : '' }}" id="input-search" placeholder="Ingrese la cuenta"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Empresa: @endcomponent
				@php
					$options = collect();
					foreach(App\Enterprise::orderName()->get() as $enterprise)
					{
						$description = strlen($enterprise->name) >= 35 ? substr(strip_tags($enterprise->name),0,40).'...' : $enterprise->name;
						if(isset($enterpriseid) && $enterpriseid == $enterprise->id)
						{
							$options = $options->concat([["value" => $enterprise->id, "description" => $description, "selected" => "selected"]]);
						}
						else
						{
							$options = $options->concat([["value" => $enterprise->id, "description" => $description]]);
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
	@if(count($bankAccounts) > 0)
		@php
			$modelHead = 
			[
				[
					["value" => "ID"],
					["value" => "Alias"],
					["value" => "CLABE"],
					["value" => "Cuenta"],
					["value" => "Empresa"],
					["value" => "Acción"],
				]
			];
			foreach($bankAccounts as $account)
			{
				$body = 
				[
					[					
						"content" => 
						[
							[
								"kind" 	=> "components.labels.label",
								"label" => $account->id
							]
						]
					],
					[					
						"content" => 
						[
							[
								"kind" 	=> "components.labels.label",
								"label" => $account->alias!="" ? htmlentities($account->alias) : '---'
							]
						]
					],
					[
						"content" => 
						[
							[
								"kind" 	=> "components.labels.label",
								"label" => $account->clabe
							]
						]
					],
					[
						"content" => 
						[
							[
								"kind" 	=> "components.labels.label",
								"label" => $account->account
							]
						]
					],
					[
						"content" => 
						[
							[
								"kind" 	=> "components.labels.label",
								"label" => $account->enterprise->name
							]
						]
					],
					[
						"content" => 
						[
							[
								"kind" 	=> "components.buttons.button",
								"attributeEx" => "title=\"Editar Cuenta Bancaria\" href=\"".route('bank.acount.edit',$account->id)."\"",
								"buttonElement" => "a",
								"variant" => "success",
								"label" => "<span class=\"icon-pencil\"></span>"
							]
						]
					],
				];
				$modelBody[] = $body;
			}
		@endphp
		@Table(["modelHead" => $modelHead, "modelBody" => $modelBody, "attributeEx" => "table"]) @endTable
		{{ $bankAccounts->appends($_GET)->links() }}
	@else
		@component("components.labels.not-found") @endcomponent
	@endif
@endsection

@section('scripts')
	<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
	<script src="{{ asset('js/jquery-ui.js') }}"></script>
	<script>
		$(document).ready(function()
		{
			@php
				$selects = collect(
					[
						[
							"identificator"				=> ".js-enterprise",
							"placeholder"				=> "Seleccione la empresa",
							"maximumSelectionLength"	=> "1",
						]
					]
				);
			@endphp
			@component("components.scripts.selects",["selects" => $selects]) @endcomponent
		});
	</script>
@endsection