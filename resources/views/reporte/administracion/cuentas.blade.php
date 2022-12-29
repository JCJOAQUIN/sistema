@extends('layouts.child_module')
@section('data')
	@component("components.labels.title-divisor") BUSCAR @endcomponent
	@php
		$hidden = ['enterprise','name','folio','rangeDate'];
	@endphp
	@component("components.forms.searchForm",["attributeEx" => "id=\"formsearch\"","values" => [], "hidden" => $hidden])
		@slot("contentEx")
			<div class="col-span-2">
				@component("components.labels.label") Empresa: @endcomponent
				@php
					$options = collect();
					foreach(App\Enterprise::where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeEnt($option_id)->pluck('enterprise_id'))->orderBy('name','asc')->get() as $enterprise)
					{
						$description = strlen($enterprise->name) >= 35 ? substr(strip_tags($enterprise->name),0,35)."..." : $enterprise->name;
						if (isset($idEnterprise) && $enterprise->id == $idEnterprise)
						{
							$options = $options->concat([["value"=>$enterprise->id, "selected"=>"selected", "description"=>$description]]);
						}
						else
						{
							$options = $options->concat([["value"=>$enterprise->id, "description"=>$description]]);
						}
					}
					$attributeEx	= "name=\"idEnterprise\" title=\"Empresa\" multiple=\"multiple\"";
					$classEx		= "js-enterprise";
				@endphp
				@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label')Cuenta:@endcomponent
				@php
					$options = collect();
					if(isset($idEnterprise) && isset($account))
					{
						foreach(App\Account::where('idEnterprise',$idEnterprise)->whereIn('idAccAcc',$account)->get() as $acc)
						{
							$options = $options->concat([["value"=>$acc->idAccAcc, "selected"=>"selected", "description"=> $acc->account."-".$acc->description."(".$acc->content.")"]]);
						}
					}
				@endphp
				@component('components.inputs.select', 
					[
						'attributeEx' => "title=\"Cuenta\" multiple=\"multiple\" name=\"account[]\"", 
						'classEx'     => "js-account removeselect", 
						"options"     => $options
					]
				)
				@endcomponent
			</div>
		@endslot
		@if (count($accounts) > 0)
			@slot("export")
				<div class="float-right">
					@component('components.labels.label')
						@component('components.buttons.button',['variant' => 'success'])
							@slot('attributeEx') 
								type=submit 
								formaction={{ route('report.accounts.excel') }} @endslot
							@slot('label')
								<span>Exportar a Excel</span><span class="icon-file-excel"></span> 
							@endslot
						@endcomponent
					@endcomponent
				</div>
			@endslot
		@endif
	@endcomponent
	@if(count($accounts) > 0)
		@php	
			$modelHead = 
			[	
				[
					["value" => "Empresa"],
					["value" => "Cuenta", "show"  => "true"],
					["value" => "Balance"]
				]
			];

			$modelBody = [];
			foreach($accounts as $acc)
			{
				$body = 
				[
					[
						"content" => 
						[
							[
								"kind"  => "components.labels.label",
								"label" => $acc->enterprise->name,
							]
						]
					],
					[
						"content" =>
						[
							[
								"kind"  => "components.labels.label",
								"label" => $acc->account." ".$acc->description,
							]
						]
					],
					[
						"content" =>
						[
							[
								"kind"  => "components.labels.label",
								"label" =>  $acc->balance,
							]
						]
					]
				];
				$modelBody [] = $body;
			}
		@endphp
		@component("components.tables.table", 
			[
				"modelHead" => $modelHead, 
				"modelBody" => $modelBody
			])
		@endcomponent
		{{ $accounts->appends($_GET)->links() }}
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
				$selects = collect([
					[
						"identificator"			=> ".js-enterprise",
						"placeholder"			=> "Seleccione una empresa",
						"languaje"				=> "es",
						"maximumSelectionLength" => "1",
					],
					[
						"identificator"			=> ".js-account",
						"placeholder"			=> "Seleccione la cuenta",
						"languaje"				=> "es"
					]
				]);
			@endphp
			@component("components.scripts.selects",["selects" => $selects]) @endcomponent
			generalSelect({'selector':'[name="account[]"]','depends': '[name="idEnterprise"]','model': 3, 'maxSelection': -1});
		});
	</script> 
@endsection
