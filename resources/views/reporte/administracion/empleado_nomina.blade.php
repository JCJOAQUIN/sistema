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
					foreach(App\Enterprise::where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeEnt($option_id)->pluck('enterprise_id'))->orderBy('name','asc')->get() as $ent)
					{
						$description = strlen($ent->name) >= 35 ? substr(strip_tags($ent->name),0,35)."..." : $ent->name;
						if (isset($enterprise) && $ent->id == $enterprise)
						{
							$options = $options->concat([["value"=>$ent->id, "selected"=>"selected", "description"=>$description]]);
						}
						else
						{
							$options = $options->concat([["value"=>$ent->id, "description"=>$description]]);
						}
					}
					$attributeEx	= "name=\"enterprise\" title=\"Empresa\" multiple=\"multiple\"";
					$classEx		= "js-enterprise";
				@endphp
				@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label')Registro Patronal:@endcomponent
				@php
					$options = collect();
					if(isset($enterprise) && isset($register))
					{
						$emp = App\EmployerRegister::where('enterprise_id',$enterprise)->where('employer_register',$register)->get();
						$options = $options->concat([["value"=>$emp->first()->employer_register, "selected"=>"selected", "description"=> $emp->first()->employer_register]]);
					}
				@endphp
				@component('components.inputs.select', 
					[
						'attributeEx' => "title=\"Registro patronal\" name=\"register\" multiple=\"multiple\"",
						'classEx'     => "js-register", 
						"options"     => $options
					]
				)
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label')Periodicidad:@endcomponent
				@php
					$options = collect();
					foreach(App\CatPeriodicity::orderBy('description','asc')->get() as $per)
					{
						$description = $per->description;
						if (isset($periodicity) && $per->c_periodicity == $periodicity)
						{
							$options = $options->concat([["value"=>$per->c_periodicity, "selected"=>"selected", "description"=>$description]]);
						}
						else
						{
							$options = $options->concat([["value"=>$per->c_periodicity, "description"=>$description]]);
						}
					}
				@endphp
				@component('components.inputs.select', 
					[
						'attributeEx' => "title=\"Periodicidad\" name=\"periodicity\" multiple=\"multiple\"",
						'classEx'     => "js-periodicity", 
						"options"     => $options
					]
				)
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label')Tipo:@endcomponent
				@php
					$options = collect();
					if(isset($type) && $type == 1)
					{
						$options = $options->concat([["value"=>1, "selected"=>"selected", "description"=> "Obra"]]);
					}
					else
					{
						$options = $options->concat([["value"=>1, "description"=> "Obra"]]);
					}

					if(isset($type) && $type == 2)
					{
						$options = $options->concat([["value"=>2, "selected"=>"selected", "description"=> "Administrativa"]]);
					}
					else
					{
						$options = $options->concat([["value"=>2, "description"=> "Administrativa"]]);
					}
				@endphp
				@component('components.inputs.select', 
					[
						'attributeEx' => "title=\"Tipo\" name=\"type\" multiple=\"multiple\"",
						'classEx'     => "js-type", 
						"options"     => $options
					]
				)
				@endcomponent
			</div>
		@endslot
		@if(isset($employees) && count($employees)>0)
			@slot("export")
				<div class="float-right">
					@component('components.labels.label')
						@component('components.buttons.button',['variant' => 'success'])
							@slot('attributeEx') 
								type=submit 
								formaction={{ route('report.employee-nomina.excel') }} @endslot
							@slot('label')
								<span>Exportar a Excel</span><span class="icon-file-excel"></span> 
							@endslot
						@endcomponent
					@endcomponent
				</div>
			@endslot
		@endif
	@endcomponent
	@if(isset($employees) && count($employees)>0)
		@php	
			$modelHead = 
			[	
				[
					["value" => "#"],
					["value" => "Nombre"],
					["value" => "Curp"],
					["value" => "RFC"],
					["value" => "Empresa"]
				]
			];

			$modelBody = [];
			foreach($employees as $employe)
			{
				$body = 
				[
					[
						"content" => 
						[
							[
								"kind"  => "components.labels.label",
								"label" => $employe->id
							]
						]
					],
					[
						"content" =>
						[
							[
								"kind"  => "components.labels.label",
								"label" => $employe->fullName()
							]
						]
					],
					[
						"content" =>
						[
							[
								"kind"  => "components.labels.label",
								"label" =>  $employe->curp,
							]
						]
					],
					[
						"content" =>
						[
							[
								"kind"  => "components.labels.label",
								"label" =>  $employe->rfc,
							]
						]
					],
					[
						"content" =>
						[
							[
								"kind"  => "components.labels.label",
								"label" =>  $employe->workerDataVisible()->first() ? $employe->workerDataVisible()->first()->enterprises->name : ''
							]
						]
					]
				];
				$modelBody [] = $body;
			}
		@endphp
		@component("components.tables.table", ["modelHead" => $modelHead, "modelBody" => $modelBody]) @endcomponent
		{{ $employees->appends($_GET)->links() }}
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
						"identificator"			=> ".js-register",
						"placeholder"			=> "Seleccione un registro patronal",
						"languaje"				=> "es",
						"maximumSelectionLength" => "1",
					],
					[
						"identificator"			=> ".js-periodicity",
						"placeholder"			=> "Seleccione una periocidad",
						"languaje"				=> "es",
						"maximumSelectionLength" => "1",
					],
					[
						"identificator"			=> ".js-type",
						"placeholder"			=> "Seleccione el tipo",
						"languaje"				=> "es",
						"maximumSelectionLength" => "1",
					]
				]);
			@endphp
			@component("components.scripts.selects",["selects" => $selects]) @endcomponent
			generalSelect({'selector':'[name="register"]','depends': '[name="enterprise"]','model': 47});
		});
	</script> 
@endsection
