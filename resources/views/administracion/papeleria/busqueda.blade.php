@extends("layouts.child_module")
@section("data")
	@component("components.labels.title-divisor") BUSCAR SOLICITUDES @endcomponent
	@php
		$values = ["enterprise_option_id" => $option_id, "enterprise_id" => $enterpriseid, "folio" => $folio, "name" => $name, "minDate" => $mindate, "maxDate" => $maxdate];
	@endphp
	@component("components.forms.searchForm",["attributeEx" => "id=\"formsearch\"", "values" => $values])
		@slot("contentEx")
			<div class="col-span-2">
				@component("components.labels.label") 
					Cuenta: 
				@endcomponent
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
				@component("components.labels.label") Estado: @endcomponent
				@php
					$options = collect();
						foreach (App\StatusRequest::orderName()->whereIn("idrequestStatus",[2,3,4,5,9, 19])->get() as $s) 
						{
							$description = $s->description;	
							if (isset($status) && $status == $s->idrequestStatus)
							{
								$options = $options->concat([["value"=>$s->idrequestStatus, "selected"=>"selected", "description"=>$description]]);
							}
							else
							{
								$options = $options->concat([["value"=>$s->idrequestStatus,"description"=>$description]]);
							}
						}
					$attributeEx = "name=\"status\"";
					$classEx = "js-status";
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
								formaction="{{ route("stationery.export.follow") }}"
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
					["value" => "Fecha de elaboración"],
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
							"label" => (isset($request->stationery->first()->title) && $request->stationery->first()->title != null) ? htmlentities($request->stationery->first()->title) : "No hay",
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
				$body[] = 
				[
					"content" =>	
					[
						"label" => $request->statusrequest != null ? $request->statusrequest->description : "No existe",
					]
				];
				$body[] = 
				[
					"content" =>
					[
						"label" => Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$request->fDate)->format('d-m-Y H:i'),
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
				if($request->status == 5 || $request->status == 6 || $request->status == 7 || $request->status == 9  || $request->status == 10 || $request->status == 11 || $request->status == 19) 
				{
					$body[]["content"] = 
					[
						[
							"kind"          => "components.buttons.button",
							"buttonElement" => "a",
							"classEx"		=> "follow-btn load-actioner",
							"attributeEx"   => "alt=\"Nueva Solicitud\" title=\"Nueva Solicitud\" href=\"".route("stationery.create.new",$request->folio)."\"",
							"variant"		=> "warning",	
							"label"			=> "<span class=\"icon-plus\"></span>"
						],
						[
							"kind"          => "components.buttons.button",
							"buttonElement" => "a", 
							"classEx"		=> "follow-btn load-actioner",
							"attributeEx"   => "alt=\"Ver Solicitud\" title=\"Ver Solicitud\" href=\"".route("stationery.follow.edit",$request->folio)."\"",
							"variant" 		=> "secondary",
							"label"			=> "<span class=\"icon-search\"></span>"
						],
					];	
				}
				else if($request->status == 3 || $request->status == 4 || $request->status == 5  || $request->status == 10 || $request->status == 11 || $request->status == 19) 
				{
					$body[]["content"] = 
					[
						[
							"kind"          => "components.buttons.button",
							"buttonElement" => "a", 
							"label"			=> "<span class=\"icon-search\"></span>",
							"classEx"	   	=> "follow-btn load-actioner",
							"variant" 		=> "secondary",
							"attributeEx"  	=> "alt=\"Ver Solicitud\" title=\"Ver Solicitud\" href=\"".route("stationery.follow.edit",$request->folio)."\""
						],
					];	
							
				}
				else 
				{
					$body[]["content"] = 
                    [
                        [
                            "kind"          => "components.buttons.button",
							"buttonElement" => "a", 
                            "label"			=> "<span class=\"icon-pencil\"></span>",
                            "classEx"	  	=> "follow-btn load-actioner", 
							"variant" 		=> "success",
                            "attributeEx" 	=> "alt=\"Ver Solicitud\" title=\"Ver Solicitud\" href=\"".route("stationery.follow.edit",$request->folio)."\""
                        ],
					];				
				}
				if($request->status == 5)
				{
					array_push($body[count($body)-1]["content"], 
					
						[
							"kind"          => "components.buttons.button",
							"buttonElement" => "a", 
                            "label"			=> "<span class=\"icon-pdf\"></span>",
                            "classEx"	  	=> "follow-btn", 
							"variant" 		=> "success",
                            "attributeEx" 	=> "alt=\"Descargar orden\" title=\"Descargar orden\" href=\"".route("stationery.download.document",$request->folio)."\""
						]
					);
				}
				$modelBody[] = $body;
			}			
		@endphp
		@component("components.tables.table",[
			"modelHead" => $modelHead,
			"modelBody" => $modelBody,
		])
		@endcomponent
		{{ $requests->appends($_GET)->links() }}
	@else
		@component("components.labels.not-found")@endcomponent
	@endif
@endsection
@section("scripts")
	<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
	<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
	<script src="{{ asset('js/datepicker.js') }}"></script>
	<script src="{{ asset('js/jquery.numeric.js') }}"></script>
	<script> 
		$(document).ready(function()
		{
			@php
				$selects = collect([
					[
						"identificator"			=> ".js-enterprise",
						"placeholder"			=> "Seleccione la empresa",
						"language"				=> "es",
						"maximumSelectionLength"=> "1"
					],
					[
						"identificator"			=> ".js-category",
						"placeholder"			=> "Seleccione la categoría",
						"language"				=> "es",
						"maximumSelectionLength"=> "1"
					],
					[
						"identificator"			=> ".js-account",
						"placeholder"			=> "Seleccione la cuenta",
						"language"				=> "es",
						"maximumSelectionLength"=> "1"
					],
					[
						"identificator"			=> ".js-status",
						"placeholder"			=> "Seleccione el estado",
						"language"				=> "es",
						"maximumSelectionLength"=> "1"
					]
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
