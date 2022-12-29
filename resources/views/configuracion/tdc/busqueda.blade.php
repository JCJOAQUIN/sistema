@extends('layouts.child_module')
@section('data')
	@component("components.forms.form",["attributeEx" => "action=\"".route('credit-card.search')."\" method=\"GET\" id=\"formsearch\""])
		@component("components.labels.title-divisor") BUSCAR TARJETAS DE CRÉDITO @endcomponent
		@component("components.containers.container-form")
			<div class="col-span-2">
				@component("components.labels.label")
					Empresa:
				@endcomponent
				@php
					$options = collect();
					foreach(App\Enterprise::orderName()->get() as $ent)
					{
						$description = $ent->name;
						if(isset($enterprise_id) && $ent->id == $enterprise_id)
						{
							$options = $options->concat([["value" => $ent->id,"selected" => "selected", "description" => $description]]);
						}
						else
						{
							$options = $options->concat([["value" => $ent->id, "description" => $description]]);
						}
					}
					$attributeEx = "name=\"enterprise_id\" multiple=\"multiple\"";
					$classEx = "js-enterprises removeselect";
				@endphp
				@component("components.inputs.select",["attributeEx" => $attributeEx, "classEx" => $classEx, "options" => $options])@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label")
					Clasificación del Gasto:
				@endcomponent
				@php
					$options = collect();
					if(isset($enterprise_id))
					{
						foreach(App\Account::where('selectable',1)->where('idEnterprise',$enterprise_id)->get() as $acc)
						{
							$description = $acc->account." - ".$acc->description." (".$acc->content.")";
							if($acc->idAccAcc==$account_id)
							{
								$options = $options->concat([["value" => $acc->idAccAcc,"selected" => "selected", "description" => $description]]);
							}
							else
							{
								$options = $options->concat([["value" => $acc->idAccAcc, "description" => $description]]);
							}
						}
					}
					$attributeEx = "name=\"account_id\" multiple=\"multiple\"";
					$classEx = "js-accounts removeselect";
				@endphp
				@component("components.inputs.select",["attributeEx" => $attributeEx, "classEx" => $classEx, "options" => $options])@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label")
					Banco:
				@endcomponent
				@php
					$options = collect();
					foreach(App\Banks::orderName()->get() as $b)
					{
						$description = $b->description;
						if(isset($bank_id) && $b->idBanks == $bank_id)
						{
							$options = $options->concat([["value" => $b->idBanks,"selected" => "selected", "description" => $description]]);
						}
						else
						{
							$options = $options->concat([["value" => $b->idBanks, "description" => $description]]);
						}
					}
					$attributeEx = "multiple=\"multiple\" name=\"bank_id\"";
					$classEx = "js-bank";
				@endphp
				@component("components.inputs.select",["attributeEx" => $attributeEx, "classEx" => $classEx, "options" => $options])@endcomponent
			</div>
			<div class="col-span-2 md:col-span-4 space-x-2 text-center md:text-left flex">
				@component("components.buttons.button-search", ["attributeEx" => $attributeExButtonSearch??'', "classEx" => $classExButtonSearch??'']) @endcomponent
				@component("components.buttons.button", ["buttonElement" => "a", "variant" => "reset", "classEx" => "bg-gray-200 px-7 py-2 rounded cursor-pointer hover:bg-gray-200 uppercase font-bold text-sm h-9 text-blue-gray-700", "attributeEx" => "href=\"".strtok($_SERVER['REQUEST_URI'], '?')."\""])Borrar campos @endcomponent
			</div>
		@endcomponent
	@endcomponent
	@if(count($banksAccounts) > 0)
		@php
			$modelHead = 
			[
				[
					["value" => "Empresa"],
					["value" => "Cuenta"],
					["value" => "Banco"],
					["value" => "Alias"],
					["value" => "Acción"]
				]
			];
			$modelBody = [];
			$body = [];

			foreach($banksAccounts as $baccounts)
			{
				$body = 
				[
					[
						"content" =>
						[
							"label" => ($baccounts->idBanks != "" ? $baccounts->enterprise->name : "No hay"),
						]
					],
					[
						"content" =>
						[
							"label" => ($baccounts->idAccAcc != "" ? $baccounts->accounts->account.' - '.$baccounts->accounts->description.' ('.$baccounts->accounts->description.')' : "No hay"),
						]
					],
					[
						"content" =>
						[
							"label" => ($baccounts->idBanks != "" ? $baccounts->bank->description : "No hay"),
						]
					],
					[
						"content" =>
						[
							"label" => ($baccounts->alias != "" ? htmlentities($baccounts->alias) : "No hay"),
						]
					],
					[
						"content" =>
						[
								"kind"          => "components.buttons.button",
								"buttonElement" => "a", 
								"label"			=> "<span class=\"icon-pencil\"></span>",
								"classEx"	  	=> "follow-btn", 
								"variant" 		=> "success",
								"attributeEx" 	=> "alt=\"Editar\" title=\"Editar\" href=\"".route("credit-card.edit",$baccounts->idcreditCard)."\""
						]
					],
				];
				$modelBody[] = $body;
			}
		@endphp
		@component("components.tables.table",[
			"modelHead" => $modelHead,
			"modelBody" => $modelBody,
		])
		@endcomponent
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
			@php
				$selects = collect(
					[
						[
							"identificator"          => ".js-enterprises", 
							"placeholder"            => "Seleccione la empresa", 
							"language"				 => "es",
							"maximumSelectionLength" => "1"
						]
					]
				);
			@endphp
			@component("components.scripts.selects",["selects" => $selects])@endcomponent
			generalSelect({'selector':'.js-accounts', 'depends':'.js-enterprises', 'model':10});
			generalSelect({'selector':'.js-bank', 'model':27});
			$('.account,.clabe').numeric({ negative : false, decimal : false });
			$(document).on('change','.js-enterprises',function()
			{
				$('.js-accounts').empty();
				$enterprise = $(this).val();
				generalSelect({'selector':'.js-accounts', 'depends':'.js-enterprises', 'model':10});
			})
		});
	</script>
@endsection
