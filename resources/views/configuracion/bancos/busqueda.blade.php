@extends('layouts.child_module')
@section('data')
	@component("components.labels.title-divisor") BUSCAR BANCOS @endcomponent
	@component("components.forms.searchForm", ["variant" => "default", "attributeEx" => "action=\"".route('banks.search')."\" id=\"formsearch\""])
		<div class="col-span-2">
			@php
				$options = collect();
				foreach(App\Enterprise::orderName()->get() as $ent)
				{
					if(isset($enterprise) && $ent->id == $enterprise)
					{
						$options = $options->concat([["value" => $ent->id, "description" => $ent->name, "selected" => "selected"]]);
					}
					else
					{
						$options = $options->concat([["value" => $ent->id, "description" => $ent->name]]);
					}
				}
			@endphp
			@component("components.labels.label") Empresa: @endcomponent
			@component("components.inputs.select", ["options" => $options,"classEx" => "js-enterprises", "attributeEx" => "name=\"enterprise_id\""])@endcomponent
		</div>
		<div class="col-span-2">
			@php
				$options = collect();
				if(isset($enterprise) && isset($account))
				{
					$acc = App\Account::find($account);
					$options = $options->concat(
					[
						[
							"value" => $acc->idAccAcc,
							"selected" => (isset($account) ? "selected" : ""), 
							"description" => $acc->account." - ".$acc->description." (".$acc->content.")"
						]
					]);
				}				
			@endphp
			@component("components.labels.label") Clasificación del Gasto: @endcomponent
			@component("components.inputs.select", ["options" => $options, "classEx" => "js-accounts", "attributeEx" => "name=\"account_id\""])
			@endcomponent
		</div>
		<div class="col-span-2">
			@php
				$options = collect();
				foreach(App\Banks::orderName()->get() as $b)
				{
					if(isset($bank) && $b->idBanks == $bank)
					{
						$options = $options->concat([["value" => $b->idBanks, "description" => $b->description, "selected" => "selected"]]);
					}
					else
					{
						$options = $options->concat([["value" => $b->idBanks, "description" => $b->description]]);
					}
				}
			@endphp
			@component("components.labels.label") Banco: @endcomponent
			@component("components.inputs.select", ["options" => $options, "classEx" => "js-bank", "attributeEx" => "name=\"bank_id\""])
			@endcomponent
		</div>
		@if(count($banksAccounts) > 0)
			@slot("export")
				<div class="flex flex-row justify-end">
					@component('components.labels.label')
						@component("components.buttons.button",['variant' => 'success'])
							@slot("attributeEx")
								type="submit"
								formaction="{{ route('banks.export') }}"
							@endslot
							@slot("label")
								<span>Exportar a Excel</span><span class="icon-file-excel"></span>
							@endslot
						@endcomponent
					@endcomponent
				</div>
			@endslot
		@endif
	@endcomponent	
	@if(count($banksAccounts) > 0)
		@php
			$modelHead = [[["value" => "Empresa"], ["value" => "Cuenta"], ["value" => "Banco"], ["value" => "Alias"], ["value" => "Acción"]]];
			foreach($banksAccounts as $baccounts)
			{
				$body = 
				[
					[
						"content" => 
						[
							[
								"kind" => "components.labels.label",
								"label" => $baccounts->enterprise->name
							]
						],
					],
					[
						"content" => 
						[
							[
								"kind" => "components.labels.label",
								"label" => $baccounts->accounts->account.' '.$baccounts->accounts->description
							]
						],
					],
					[
						"content" => 
						[
							[
								"kind" => "components.labels.label",
								"label" => $baccounts->bank->description
							]
						],
					],
					[
						"content" => 
						[
							[
								"kind" => "components.labels.label",
								"label" => htmlentities($baccounts->alias),
							]
						],
					],
					[
						"content" => 
						[
							[
								"kind" => "components.buttons.button",
								"variant" => "success",
								"buttonElement" => "a",
								"label" => "<span class=\"icon-pencil\"></span>",
								"attributeEx" => "href=\"".route('banks.edit',$baccounts->idbanksAccounts)."\" alt=\"Editar\" title=\"Editar\""
							]
						],
					]
				];
				
				$modelBody[] = $body;
			}
		@endphp
		@Table(["attributeEx" => "id=\"table\"", "modelHead" => $modelHead, "modelBody" => $modelBody]) @endTable
		{{ $banksAccounts->appends($_GET)->links() }}
	@else
		@component("components.labels.not-found") @endcomponent
	@endif
@endsection

@section('scripts')
	<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
	<script src="{{ asset('js/jquery-ui.js') }}"></script>
	<script src="{{ asset('js/jquery.numeric.js') }}"></script>
	<script type="text/javascript">
		$(document).ready(function()
		{
			@ScriptSelect(
			[
				"selects" =>
				[
					[
						"identificator"          => ".js-enterprises", 
						"placeholder"            => "Seleccione la empresa", 
						"language"				 => "es",
						"maximumSelectionLength" => "1"
					],
				]
			]) @endScriptSelect
			generalSelect({'selector':'.js-accounts', 'depends':'.js-enterprises', 'model':10});
			generalSelect({'selector':'.js-bank', 'model':27});
			$('.account,.clabe').numeric({ negative : false, decimal : false });
			$(document).on('change','.js-enterprises',function()
			{
				$('.js-accounts').empty();
				$enterprise = $(this).val();
				generalSelect({'selector':'.js-accounts', 'depends':'.js-enterprises', 'model':3});
			})
		});
	</script>
@endsection
