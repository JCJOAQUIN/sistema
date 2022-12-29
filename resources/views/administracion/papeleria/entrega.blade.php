@extends("layouts.child_module")
@section("data")
	@component("components.labels.title-divisor") BUSCAR SOLICITUDES @endcomponent	
	@php
		$values = ["enterprise_option_id" => $option_id, "enterprise_id" => $enterpriseid, "folio" => $folio, "name" => $name, "minDate" => $mindate, "maxDate" => $maxdate];
	@endphp
	@component("components.forms.searchForm",["attributeEx" => "id=\"formsearch\"", "values" => $values])
		@slot("contentEx")
			<div class="col-span-2">
				@component("components.labels.label") Cuenta: @endcomponent
				@php
					$options = collect();
					if(isset($enterpriseid))
					{
						foreach(App\Account::orderNumber()->where('idEnterprise',$enterpriseid)->where('selectable',1)->get() as $a)
						{
							$options = $options->concat(
								[
									[
										"value" 		=> $account,
										"selected" 		=> ((isset($account) && $account == $a->idAccAcc) ? 'selected' : ''),
										"description" 	=> $a->account."-".$a->description."(".$a->content.")"
									]
								]
							);
						}
					}
					$attributeEx = "name=\"account\"";
					$classEx = "js-account removeselect";
				@endphp
				@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Proyecto/Contrato: @endcomponent
				@php
					$options = collect();
					foreach (App\Project::orderName()->whereIn("status",[1,2])->get() as $project) 
					{
						$options = $options->concat(
							[
								[
									"value" 		=> $project_id,
									"selected"		=> ((isset($project_id) && $project_id == $project->idproyect) ? "selected" : ''), 
									"description"	=>$project->proyectName
								]
							]
						);
					}
				$attributeEx = "name=\"project_id\"";
				$classEx = "js-projects removeselect";
				@endphp
				@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Categoría: @endcomponent
				@php
				$options = collect();
				foreach(App\CatWarehouseType::all() as $category)
				{
					$description = $category->description;
					if(isset($category_id) && $category_id == $category->id){
						$options = $options->concat([["value"=>$category->id,"selected"=>"selected", "description"=>$description ]]);
					}
					else 
					{
						$options = $options->concat([["value"=>$category->id, "description"=>$description ]]);
					}
				}
				$attributeEx = "name=\"category_id\"";
				$classEx = "js-category removeselect";
				@endphp
				@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])@endcomponent
			</div>
		@endslot
		@if(count($requests) > 0)
			@slot("export")
				<div class="flex flex-row justify-end">
					@component('components.labels.label')
						@component("components.buttons.button",['variant' => 'success'])
							@slot("attributeEx")
								type="submit"
								formaction="{{ route("stationery.export.delivery") }}"
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
	@if(count($requests) > 0)
		@php
			$body		= [];
			$modelBody	= [];
			$modelHead 	= [
				[
					["value" => "Folio"],
					["value" => "Título"],
					["value" => "Solicitante"],
					["value" => "Elaborado por"],
					["value" => "Estado"],
					["value" => "Fecha de revisión"],
					["value" => "Empresa"],
					["value" => "Clasificación del gasto"],
					["value" => "Acción"],
				]
			];
			
			foreach ($requests as $request) 
			{
				$body = [
					[
						"content" =>
						[
							"label" => $request->new_folio != null ? $request->new_folio : $request->folio,
						]
					],
					[
						"content" =>
						[
							"label" => $request->stationery->first()->title != null ? htmlentities($request->stationery->first()->title) : "No hay",
						]
					],
				];	
				if($request->idRequest == "")
				{
					$body [] = [
						"content" =>
						[
							"label" => "No hay solicitante",
						]
					];
				}
				else 
				{
					foreach(App\User::where("id",$request->idRequest)->get() as $user)
					{
						$body [] = [
							"content" =>
							[
								"label" => $user->name." ".$user->last_name." ".$user->scnd_last_name,
							]
						];
					}
				}
				foreach(App\User::where("id",$request->idElaborate)->get() as $user)
				{
					$body[] = [
						"content" =>
						[
							"label" => $user->name." ".$user->last_name." ".$user->scnd_last_name,
						]
					];
				}
				$body[] = [
						"content" =>	
						[
							"label" => $request->statusrequest->description,
						]
					];
				$body[] = [
						"content" =>
						[
							"label" => Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$request->reviewDate)->format('d-m-Y H:i'),
						]
					];
				if (isset($request->reviewedEnterprise->name))
				{
					$body[] = [
							"content" =>
						[
							"label" => $request->reviewedEnterprise->name,
							]
						];
				}
				else if(isset($request->reviewedEnterprise->name) == false && isset($request->requestEnterprise->name))
				{
					$body[] = [
							"content" =>
							[
								"label" => $request->requestEnterprise->name,
							]
						];
				}	
				else
				{
					$body[] = [
							"content" =>
							[
								"label" => "No hay"
							]
						];
				}
				if(isset($request->accountsReview->account))
				{
					$body[] = [
							"content" =>
							[
								"label" => $request->accountsReview->account." ".$request->accountsReview->description
							]
						]; 
				}
				else if(isset($request->accountsReview->account) == false && isset($request->accounts->account))
				{
					$body[] = [
							"content" =>	
							[
								"label" => $request->accounts->account." ".$request->accounts->description
							]
						];  
				}
				else
				{
					$body[] = [
							"content" =>	
							[
								"label" => "No hay"
							]
						];
				}
				$body[]["content"] = 
				[
					[
					"kind" 		  	=> "components.buttons.button",
					"buttonElement" => "a", 
					"label"			=> "<span class=\"icon-pencil\"></span>",
					"variant" 		=> "success",
					"attributeEx" 	=> "alt=\"Editar Solicitud\" title=\"Editar Solicitud\" href=\"".route("stationery.delivery.edit",$request->folio)."\"",
					]
				];
				$modelBody[] = $body;
			}		
		@endphp
		@component("components.tables.table",[
			"modelHead" => $modelHead,
			"modelBody" => $modelBody
		])
		@endcomponent		
		{{ $requests->appends($_GET)->links() }}
	@else
		@component("components.labels.not-found")@endcomponent
	@endif
@endsection
@section("scripts")
	<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
	<script src="{{ asset('js/jquery-ui.js') }}"></script>
	<script src="{{ asset('js/datepicker.js') }}"></script>
	<script src="{{ asset('js/jquery.numeric.js') }}"></script>
	<script> 
		$(document).ready(function()
		{
			@php
				$selects = collect([
					[
						"identificator"				=> ".js-category",
						"placeholder"				=> "Seleccione la categoría",
						"language"					=> "es",
						"maximumSelectionLength"	=> "1"
					],
					[
						"identificator"				=> ".js-enterprise",
						"placeholder"				=> "Seleccione la empresa",
						"language"					=> "es",
						"maximumSelectionLength"	=> "1"
					],
				]);
			@endphp	
			@component("components.scripts.selects",["selects" => $selects]) @endcomponent
			generalSelect({'selector': '.js-account', 'depends': '.js-enterprise', 'model': 5});
			generalSelect({'selector': '.js-projects', 'model': 21});
			$(function() 
			{
				$( ".datepicker" ).datepicker({ maxDate: 0, dateFormat: "dd-mm-yy" });
			});
		});
    </script> 
@endsection
