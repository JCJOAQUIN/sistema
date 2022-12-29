@extends('layouts.child_module')
@section('data')
	@php
		$docs 	= 0;
	@endphp
	@if(isset($request) && $request != "")
		@component("components.labels.title-divisor") ID: {{ $request->id }} @endcomponent
		@component("components.forms.form", ["attributeEx" => "action=\"".route('project-control.daily-report.edit.update',['id'=>$request->id])."\" method=\"POST\" id=\"dailyReport\"", "methodEx" => "PUT"])
	@else
		@component("components.labels.title-divisor") ALTA @endcomponent
		@component("components.forms.form", ["attributeEx" => "action=\"".route('project-control.daily-report.store')."\" method=\"POST\" id=\"dailyReport\""])
	@endif
		@component("components.containers.container-form")
			<div class="col-span-2">
				@component("components.labels.label") Registrado por: @endcomponent
				@component("components.inputs.input-text")
					@slot("attributeEx") 
						disabled
						value="{{ (isset($request->user_elaborate_id) && $request->user_elaborate_id != "" ? App\User::find($request->user_elaborate_id)->fullname() : Auth::user()->fullname()) }}"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Estado: @endcomponent
				<div class="flex space-x-2">
					@component("components.buttons.button-approval")
						@slot("attributeEx")
							name="status"
							value="1"
							@if(isset($request) && $request->status == 1) checked @else checked @endif
							id="open"
						@endslot
						Abierto
					@endcomponent
					@component("components.buttons.button-approval")
						@slot("attributeEx")
							name="status"
							value="0"
							@if(isset($request) && $request->status == 0) checked @endif
							id="closed"
						@endslot
						Cerrado
					@endcomponent
				</div>
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Proyecto: @endcomponent
				@php
					$options = collect();
					if(isset($request))
					{
						$options = $options->concat([["value" => $request->project_id, "selected" => "selected", "description" => $request->reportProject->proyectName]]);
					}
					$attributeEx = "name=\"project_id\" id=\"multiple-projects\" data-validation=\"required\" multiple=\"multiple\"";
					$classEx = "js-projects removeselect";
				@endphp
				@component ("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx]) @endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Fecha: @endcomponent
				@component("components.inputs.input-text")
					@slot("classEx")
						removeselect
						datepicker
					@endslot
					@slot("attributeEx") 
						name="datetitle"
						data-validation="required"
						placeholder="Ingrese la fecha" 
						readonly="readonly"
						@if(isset($request) && $request->date != "") value="{{ $request->date }}" @endif
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Contrato: @endcomponent
				@php
					$options = collect();
					if(isset($request->project_id) && $request->project_id != "" && $request->contract_id != "")
					{
						$contractSelected = App\Contract::find($request->contract_id);
						$options = $options->concat([["value" => $request->contract_id, "selected" => "selected", "description" => $contractSelected->number." - ".$contractSelected->name]]);
					}
					$attributeEx = "name=\"contract_id\" id=\"multiple-contracts\" data-validation=\"required\" multiple";
					$classEx = "js-contracts removeselect";
				@endphp
				@component ("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx]) @endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Condiciones climatológicas: @endcomponent
				@php
					$options = collect();
					foreach(App\CatWeatherConditions::orderBy('name','asc')->get() as $wc)
					{
						if(isset($request) && $request->weather_conditions_id == $wc->id)
						{
							$options = $options->concat([["value" => $wc->id, "selected" => "selected", "description" => $wc->name]]);
						}
						else 
						{
							$options = $options->concat([["value" => $wc->id, "description" => $wc->name]]);
						}
					}
					$attributeEx = "name=\"weather\" id=\"multiple-weather\" data-validation=\"required\" multiple=\"multiple\"";
					$classEx = "js-weather removeselect";
				@endphp
				@component ("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx]) @endcomponent
			</div>
			<div class="col-span-2 wbs-container">
				@component("components.labels.label") Código WBS: @endcomponent
				@php
					$options = collect();
					if(isset($request->contract_id) && $request->contract_id != "" && $request->wbs_id != "")
					{
						$options = $options->concat([["value" => $request->wbs_id, "selected" => "selected", "description" => $request->wbs->code_wbs]]);
					}
					$attributeEx = "name=\"code_wbs\" data-validation=\"required\" multiple=\"multiple\"";
					$classEx = "js-code_wbs removeselect";
				@endphp
				@component ("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx]) @endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Disciplina: @endcomponent
				@php
					$options = collect();
					if(isset($request) && $request->discipline_id != "")
					{
						$options = $options->concat([["value" => $request->discipline_id, "selected" => "selected", "description" => $request->discipline->indicator." - ".$request->discipline->name]]);
					}
					$attributeEx = "name=\"discipline\" id=\"multiple-discipline\" data-validation=\"required\" multiple=\"multiple\"";
					$classEx = "js-discipline removeselect";
				@endphp
				@component ("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx]) @endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Horas de trabajo: @endcomponent
				@php
					$inputs = 
					[
						[
							"input_classEx" => "timepath removeselect",
							"input_attributeEx" => "name=\"worker_hours_from\" data-validation=\"required\" placeholder=\"Ingrese una hora\" ".((isset($request) && $request->work_hours_from != "" ? "value=\"".Carbon\Carbon::createFromFormat('H:i:s', $request->work_hours_from)->format('H:i')."\"" : "")),
						],
						[
							"input_classEx" => "timepath removeselect",
							"input_attributeEx" => "name=\"worker_hours_to\" data-validation=\"required\" placeholder=\"Ingrese una hora\" ".((isset($request) && $request->work_hours_to != "" ? "value=\"".Carbon\Carbon::createFromFormat('H:i:s', $request->work_hours_to)->format('H:i')."\"" : "")),
						]
					];
				@endphp
				@component("components.inputs.range-input",["inputs" => $inputs, "variant" => "time", "classIndividual" => "worker-hours"]) @endcomponent
			</div>
			<div class="col-span-2 col-start-1 span-error-tmi">
				@component("components.labels.label") TM (Interno): @endcomponent
				@php
					$inputs = 
					[
						[
							"input_classEx" => "timepath",
							"input_attributeEx" => "name=\"internal_tm_from\" placeholder=\"Ingrese la hora\"".((isset($request) && $request->tm_internal_hours_from != "" ? " value=\"$request->work_hours_from\"" : "")),
						],
						[
							"input_classEx" => "timepath",
							"input_attributeEx" => "name=\"internal_tm_to\" placeholder=\"Ingrese la hora\"".((isset($request) && $request->tm_internal_hours_to != "" ? " value=\"$request->work_hours_to\"" : "")),
						]
					];
				@endphp
				@component("components.inputs.range-input",["inputs" => $inputs, "variant" => "time", "classIndividual" => "tm-hours"]) @endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Categoría de TM (Interno): @endcomponent
				@php
					$options = collect();
					foreach($tmCat as $tm)
					{
						if(isset($request) && $request->tm_internal_id == $tm->id)
						{
							$options = $options->concat([["value" => $tm->id, "selected" => "selected", "description" => $tm->name]]);
						}
						else 
						{
							$options = $options->concat([["value" => $tm->id, "description" => $tm->name]]);
						}
					}
					$attributeEx = "name=\"internal_tm\" id=\"multiple-internal-tm\" multiple=\"multiple\"";
					$classEx = "js-internal-tm removeselect";
				@endphp
				@component ("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx]) @endcomponent
			</div>
			<div class="col-span-2 span-error-tmc">
				@component("components.labels.label") TM (Cliente): @endcomponent
				@php
					$inputs = 
					[
						[
							"input_classEx" => "timepath",
							"input_attributeEx" => "name=\"customer_tm_from\" placeholder=\"Ingrese la hora\"".((isset($request) && $request->tm_client_hours_from != "" ? " value=\"$request->tm_client_hours_from\"" : "")),
						],
						[
							"input_classEx" => "timepath",
							"input_attributeEx" => "name=\"customer_tm_to\" placeholder=\"Ingrese la hora\"".((isset($request) && $request->tm_client_hours_to != "" ? " value=\"$request->tm_client_hours_to\"" : "")),
						]
					];
				@endphp
				@component("components.inputs.range-input",["inputs" => $inputs, "variant" => "time", "classIndividual" => "tm-hours"]) @endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Categoría de TM (Cliente): @endcomponent
				@php
					$options = collect();
					foreach($tmCat as $tm)
					{
						if(isset($request) && $request->tm_client_id == $tm->id)
						{
							$options = $options->concat([["value" => $tm->id, "selected" => "selected", "description" => $tm->name]]);
						}
						else 
						{
							$options = $options->concat([["value" => $tm->id, "description" => $tm->name]]);
						}
					}
					$attributeEx = "name=\"customer_tm\" id=\"multiple-customer-tm\" multiple=\"multiple\"";
					$classEx = "js-customer-tm removeselect";
				@endphp
				@component ("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx]) @endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Comentario: @endcomponent
				@component("components.inputs.text-area")
					@slot("classEx")
						removeselect
					@endslot
					@slot("attributeEx")
						name="comment" 
						id="comment" 
						placeholder="Ingrese un comentario"
					@endslot
					@if(isset($request) && $request->comments != "") {{ $request->comments }} @endif
				@endcomponent
			</div>
		@endcomponent
	@component("components.labels.title-divisor") ACTIVIDADES @endcomponent
	@php
		$modelHead	=
		[
			[
				["value" => "Pda. Contrato"],
				["value" => "Actividad"],
				["value" => "Cant."],
				["value" => "Unid."],
				["value" => "P.U."],
				["value" => "Monto"],
				["value" => "Contratista"],
				["value" => "Área"],
				["value" => "Lugar del área"],
				["value" => "No. de PPT(s)/Órden de trab."],
				["value" => "Planos"],
				["value" => "Observaciones"],
				["value" => "Adj. imagen"],
				["value" => "Doc. calidad"],
				["value" => "Acumulado"],
				["value" => "Acciones"]
			]
		];
		$modelBody		= [];
		$flagQuality	= false;
		if(App\User_has_module::where('user_id',Auth::user()->id)->where('module_id',$option_id)->where('quality_permission',1)->count() > 0)
		{
			$flagQuality = true;
		}
		if(isset($request))
		{
			foreach($request->pcdrDetails as $index => $pcdrDetail)
			{
				$contentDocQuality = [];
				if($flagQuality)
				{
					$docQuality = "";
					$docQuality = App\PCDailyReportDocuments::where('pcdr_details_id',$pcdrDetail->id)->where('kind','DOC_CALIDAD')->first();
					$contentDocQuality[] = 
					[
						"kind"					=> "components.documents.upload-files",
						"attributeExInput"		=> "name=\"path\" accept=\".pdf\"",
						"classExInput"			=> "pathActioner doc-quality",
						"attributeExRealPath" 	=> "name=\"realPath\"".($docQuality['path'] != "" ? " value=\"".$docQuality['path']."\"" : ""),
						"classExRealPath"		=> "path",
						"classEx"				=> "md:p-0 border-0 w-40",
						"componentsExDown"		=>
						[
							[ "kind" => "components.inputs.input-text", "attributeEx" => "type=\"hidden\" name=\"t_doc_quality[]\"".($docQuality['path'] != "" ? " value=\"".$docQuality['path']."\"" : ""), "classEx" => "t-doc-quality"],
							[ "kind" => "components.inputs.input-text", "attributeEx" => "type=\"hidden\" name=\"id_doc_quality[]\" value=\"".($docQuality['id'] != "" ? $docQuality['id'] : "x")."\"", "classEx" => "id-doc-quality"],
							[ "kind" => "components.inputs.input-text", "attributeEx" => "type=\"hidden\" value=\"".($docQuality['path'] != "" ? $docQuality['id'] : "x")."\"", "classEx" => "new-doc"],
							[ "kind" => "components.buttons.button-link", "attributeEx" => "target=\"_blank\"".($docQuality['path'] != "" ? " href=\"".asset('docs/daily_report_operations/'.$docQuality['path'])."\" title=\"".$docQuality['path']."\"" : "href=\"\" title=\"\""), "label" => "Ver archivo", "classEx" => "show-file".($docQuality['path'] == "" ? " hidden" : "")],
						],
						"classExContentDown" 	=> "inline-block md:m-0",
						"classExContainer" 		=> "uploader-small-2 md:m-0".($docQuality['id'] != "" && $docQuality['path'] != "" ? " image_success" : ""),
						"classExContentAction"	=> "inline-block text-xs",
						"classExDelete" 		=> "delete-doc".($docQuality['path'] == "" ? " hidden" : ""),
						"variant"				=> "no-border",
					];
				}
				else 
				{
					$contentDocQuality[] = 
					[
						"label" => " ",
					];
				}
				$optionContractItem = collect();
				if(isset($pcdrDetail->contract_item_id) && $pcdrDetail->contract_item_id != "")
				{
					$optionContractItem = $optionContractItem->concat([["value" => $pcdrDetail->contract_item_id, "selected" => "selected", "description" => $pcdrDetail->contract->contract_item]]);
				}
				$optionContractor = collect();
				if(isset($pcdrDetail->contractor_id) && $pcdrDetail->contractor_id != "")
				{
					$optionContractor = $optionContractor->concat([["value" => $pcdrDetail->contractor_id, "selected" => "selected", "description" => $pcdrDetail->contractor->name]]);
				}
				$optionBlueprint = collect();
				if(isset($pcdrDetail->blueprint_id) && $pcdrDetail->blueprint_id != "")
				{
					$optionBlueprint = $optionBlueprint->concat([["value" => $pcdrDetail->blueprint_id, "selected" => "selected", "description" => $pcdrDetail->blueprint->name]]);
				}
				$adjImage = "";
				$adjImage = App\PCDailyReportDocuments::where('pcdr_details_id',$pcdrDetail->id)->where('kind','ADJ_IMAGEN')->first();
				$modelBody[] =
				[
					"classEx"		=> "tr pt-2",
					[
						"classEx" 	=> "td md:p-0",
						"content"	=>
						[
							[
								"kind"			=> "components.inputs.select",
								"attributeEx"	=> "name=\"t_pda_contract[]\" multiple id=\"js_contract_item_".$docs."\" data-validation=\"required\"",
								"classEx"		=> "js-contract-item w-40",
								"options"		=> $optionContractItem,
							],
							[
								"kind"			=> "components.inputs.input-text",
								"attributeEx"	=> "name=\"idpcdrDetail[]\" value=\"".$pcdrDetail->id."\" type=\"hidden\"",
								"classEx"		=> "idpcdrDetail",
							]
						]
					],
					[
						"classEx" 	=> "td",
						"content"	=>
						[
							"kind"			=> "components.inputs.text-area",
							"attributeEx"	=> "readonly",
							"classEx"		=> "activity h-11 p-2 w-40",
							"label"			=> $pcdrDetail->contract->activity,
						]
					],
					[
						"classEx" 	=> "td",
						"content"	=>
						[
							"kind"			=> "components.inputs.input-text",
							"attributeEx"	=> "name=\"t_quantity[]\" placeholder=\"Ingrese la cantidad\" data-validation=\"required number_no_zero\"".($pcdrDetail->quantity != "" ? " value=\"".$pcdrDetail->quantity."\"" : ""),
							"classEx"		=> "t-quantity w-40",
						]
					],
					[
						"classEx" 	=> "td",
						"content"	=>
						[
							"kind"			=> "components.inputs.input-text",
							"attributeEx"	=> "disabled".($pcdrDetail->contract->unit != "" ? " value=\"".$pcdrDetail->contract->unit."\"" : ""),
							"classEx"		=> "unit w-40",
						]
					],
					[
						"classEx" 	=> "td",
						"content"	=>
						[
							"kind"			=> "components.inputs.input-text",
							"attributeEx"	=> "disabled".($pcdrDetail->contract->pu != "" ? " value=\"".$pcdrDetail->contract->pu."\"" : ""),
							"classEx"		=> "pu w-40",
						]
					],
					[
						"classEx" 	=> "td",
						"content"	=>
						[
							"kind"			=> "components.inputs.input-text",
							"attributeEx"	=> "name=\"t_amount[]\" readonly placeholder=\"Ingrese el monto\" data-validation=\"required number_no_zero\"".($pcdrDetail->amount != "" ? " value=\"".$pcdrDetail->amount."\"" : ""),
							"classEx"		=> "t-amount w-40",
						]
					],
					[
						"classEx" 	=> "td",
						"content"	=>
						[
							"kind"			=> "components.inputs.select",
							"attributeEx"	=> "name=\"t_contractor[]\" multiple id=\"js_contractor_".$docs."\" data-validation=\"required\"",
							"classEx"		=> "js-contractor w-40",
							"options"		=> $optionContractor,
						]
					],
					[
						"classEx" 	=> "td",
						"content"	=>
						[
							"kind"			=> "components.inputs.input-text",
							"attributeEx"	=> "name=\"t_area[]\" placeholder=\"Ingrese el área\" data-validation=\"required\"".($pcdrDetail->area != "" ? " value=\"".htmlentities($pcdrDetail->area)."\"" : ""),
							"classEx"		=> "t-area w-40",
						]
					],
					[
						"classEx" 	=> "td",
						"content"	=>
						[
							"kind"			=> "components.inputs.input-text",
							"attributeEx"	=> "name=\"t_place_area[]\" placeholder=\"Ingrese el lugar del área\" data-validation=\"required\"".($pcdrDetail->place_area != "" ? " value=\"".htmlentities($pcdrDetail->place_area)."\"" : ""),
							"classEx"		=> "t-place-area w-40",
						]
					],
					[
						"classEx" 	=> "td",
						"content"	=>
						[
							"kind"			=> "components.inputs.input-text",
							"attributeEx"	=> "name=\"t_ppt[]\" placeholder=\"Ingrese el No. de PPT(s)/Órden de trabajo\" data-validation=\"required\"".($pcdrDetail->num_ppt != "" ? " value=\"".htmlentities($pcdrDetail->num_ppt)."\"" : ""),
							"classEx"		=> "t-ppt w-40",
						]
					],
					[
						"classEx" 	=> "td",
						"content"	=>
						[
							"kind"			=> "components.inputs.select",
							"attributeEx"	=> "name=\"t_blueprint[]\" multiple id=\"js_blueprints_".$docs."\" data-validation=\"required\"",
							"classEx"		=> "js-blueprints w-40",
							"options"		=> $optionBlueprint,
						]
					],
					[
						"classEx" 	=> "td",
						"content"	=>
						[
							"kind"			=> "components.inputs.input-text",
							"attributeEx"	=> "name=\"t_observs[]\" placeholder=\"Ingrese las observaciones\"".($pcdrDetail->comments ? " value=\"".htmlentities($pcdrDetail->comments)."\"" : ""),
							"classEx"		=> "t-observs w-40",
						]
					],
					[
						"classEx" 	=> "td",
						"content"	=>
						[
							"kind"					=> "components.documents.upload-files",
							"attributeExInput"		=> "type=\"file\" name=\"path\" accept=\".jpg,.jpeg,.png\"",
							"classExInput"			=> "pathActioner activity-image",
							"attributeExRealPath"	=> "name=\"realPath\" data-validation=\"required\"".($adjImage['path'] != "" ? " value=\"".$adjImage['path']."\"" : ""),
							"classExRealPath"		=> "path",
							"classEx"				=> "md:p-0 border-0",
							"componentsExDown"		=>
							[
								[ "kind" => "components.inputs.input-text", "attributeEx" => "type=\"hidden\" name=\"t_image_activity[]\"".($adjImage['path'] != "" ? " value=\"".$adjImage['path']."\"" : ""), "classEx" => "t-image-activity"],
								[ "kind" => "components.inputs.input-text", "attributeEx" => "type=\"hidden\" name=\"id_image_activity[]\" value=\"".($adjImage['id'] != "" ? $adjImage['id'] : "x")."\"", "classEx" => "id-image-activity"],
								[ "kind" => "components.inputs.input-text", "attributeEx" => "type=\"hidden\" value=\"".($adjImage['path'] != "" ? $adjImage['id'] : "x")."\"", "classEx" => "new-image"],
								[ "kind" => "components.buttons.button-link", "attributeEx" => "target=\"_blank\"".($adjImage['path'] != "" ? " href=\"".asset('docs/daily_report_operations/'.$adjImage['path'])."\" title=\"".$adjImage['path']."\"" : "href=\"\" title=\"\""), "label" => "Ver archivo", "classEx" => "show-file".($adjImage['path'] == "" ? " hidden" : "")],
							],
							"classExContentDown" 	=> "inline-block md:m-0",
							"classExContainer" 		=> "uploader-small-2 md:m-0".($adjImage['id'] != "" && $adjImage['path'] != "" ? " image_success" : ""),
							"classExContentAction"	=> "inline-block text-xs",
							"classExDelete" 		=> "delete-doc".($adjImage['path'] == "" ? " hidden" : ""),
							"variant"				=> "no-border",
						],
					],
					[
						"classEx" 	=> "td",
						"content"	=> $contentDocQuality,
					],
					[
						"classEx" 	=> "td",
						"content"	=>
						[
							"kind"			=> "components.inputs.input-text",
							"attributeEx"	=> "name=\"t_accumulated[]\" placeholder=\"Ingrese el acumulado\" data-validation=\"required number_no_zero\" value=\"".$pcdrDetail->accumulated."\"",
							"classEx"		=> "t-accumulated w-40",
						]						
					],
					[
						"classEx" 	=> "td",
						"content"	=>
						[
							"kind"			=> "components.buttons.button",
							"attributeEx"	=> "type=\"button\"".($index == 0 ? " id=\"addActivity\"" : ""),
							"classEx"		=> ($index == 0 ? "addActivity" : "delete-activity"),
							"variant" 		=> ($index == 0 ? "primary" : "red"),
							"label" 		=> ($index == 0 ? "<span class=\"icon-plus\"></span>" : "<span class=\"icon-bin\"></span>"),
						]
					],
				];
				$docs++;
			}
		}
		else 
		{
			$contentDocQuality = [];
			if($flagQuality)
			{
				$contentDocQuality[] = 
				[
					"kind"					=> "components.documents.upload-files",
					"attributeExInput"		=> "type=\"file\" name=\"path\" accept=\".pdf\"",
					"classExInput"			=> "pathActioner doc-quality",
					"attributeExRealPath" 	=> "name=\"realPath\"",
					"classExRealPath"		=> "path",
					"classEx"				=> "md:p-0 border-0 w-40",
					"componentsExDown"		=>
					[
						[ "kind" => "components.inputs.input-text", "attributeEx" => "type=\"hidden\" name=\"t_doc_quality[]\"", "classEx" => "t-doc-quality"],
						[ "kind" => "components.inputs.input-text", "attributeEx" => "type=\"hidden\" name=\"id_doc_quality[]\" value=\"x\"", "classEx" => "id-doc-quality"],
						[ "kind" => "components.inputs.input-text", "attributeEx" => "type=\"hidden\" value=\"x\"", "classEx" => "new-doc"],
						[ "kind" => "components.buttons.button-link", "attributeEx" => "target=\"_blank\" href=\"\" title=\"\"", "label" => "Ver archivo", "classEx" => "show-file hidden"],
					],
					"classExContentDown" 	=> "inline-block md:m-0",
					"classExContainer" 		=> "uploader-small-2 md:m-0",
					"classExContentAction"	=> "inline-block text-xs",
					"classExDelete" 		=> "delete-doc hidden",
					"variant"				=> "no-border",
				];
			}
			else 
			{
				$contentDocQuality[] = 
				[
					"label" => " ",
				];
			}

			$modelBody[] =
			[
				"classEx"		=> "tr pt-2",
				[
					"classEx" 	=> "td md:p-0",
					"content"	=>
					[
						[
							"kind"			=> "components.inputs.select",
							"attributeEx"	=> "name=\"t_pda_contract[]\" multiple id=\"js_contract_item_".$docs."\" data-validation=\"required\"",
							"classEx"		=> "js-contract-item w-40",
						],
						[
							"kind"			=> "components.inputs.input-text",
							"attributeEx"	=> "name=\"idpcdrDetail[]\" value=\"x\" type=\"hidden\"",
							"classEx"		=> "idpcdrDetail",
						]
					]
				],
				[
					"classEx" 	=> "td",
					"content"	=>
					[
						"kind"			=> "components.inputs.text-area",
						"attributeEx"	=> "readonly",
						"classEx"		=> "activity h-11 p-2 w-40",
					]
				],
				[
					"classEx" 	=> "td",
					"content"	=>
					[
						"kind"			=> "components.inputs.input-text",
						"attributeEx"	=> "name=\"t_quantity[]\" placeholder=\"Ingrese la cantidad\" data-validation=\"required number_no_zero\"",
						"classEx"		=> "t-quantity w-40",
					]
				],
				[
					"classEx" 	=> "td",
					"content"	=>
					[
						"kind"			=> "components.inputs.input-text",
						"attributeEx"	=> "disabled",
						"classEx"		=> "unit w-40",
					]
				],
				[
					"classEx" 	=> "td",
					"content"	=>
					[
						"kind"			=> "components.inputs.input-text",
						"attributeEx"	=> "disabled",
						"classEx"		=> "pu w-40",
					]
				],
				[
					"classEx" 	=> "td",
					"content"	=>
					[
						"kind"			=> "components.inputs.input-text",
						"attributeEx"	=> "name=\"t_amount[]\" readonly placeholder=\"Ingrese el monto\" data-validation=\"required number_no_zero\"",
						"classEx"		=> "t-amount w-40",
					]
				],
				[
					"classEx" 	=> "td",
					"content"	=>
					[
						"kind"			=> "components.inputs.select",
						"attributeEx"	=> "name=\"t_contractor[]\" multiple id=\"js_contractor_".$docs."\" data-validation=\"required\"",
						"classEx"		=> "js-contractor w-40",
					]
				],
				[
					"classEx" 	=> "td",
					"content"	=>
					[
						"kind"			=> "components.inputs.input-text",
						"attributeEx"	=> "name=\"t_area[]\" placeholder=\"Ingrese el área\" data-validation=\"required\"",
						"classEx"		=> "t-area w-40",
					]
				],
				[
					"classEx" 	=> "td",
					"content"	=>
					[
						"kind"			=> "components.inputs.input-text",
						"attributeEx"	=> "name=\"t_place_area[]\" placeholder=\"Ingrese el lugar del área\" data-validation=\"required\"",
						"classEx"		=> "t-place-area w-40",
					]
				],
				[
					"classEx" 	=> "td",
					"content"	=>
					[
						"kind"			=> "components.inputs.input-text",
						"attributeEx"	=> "name=\"t_ppt[]\" placeholder=\"Ingrese el No. de PPT(s)/Órden de trabajo\" data-validation=\"required\"",
						"classEx"		=> "t-ppt w-40",
					]
				],
				[
					"classEx" 	=> "td",
					"content"	=>
					[
						"kind"			=> "components.inputs.select",
						"attributeEx"	=> "name=\"t_blueprint[]\" multiple id=\"js_blueprints_".$docs."\" data-validation=\"required\"",
						"classEx"		=> "js-blueprints w-40",
					]
				],
				[
					"classEx" 	=> "td",
					"content"	=>
					[
						"kind"			=> "components.inputs.input-text",
						"attributeEx"	=> "name=\"t_observs[]\" placeholder=\"Ingrese las observaciones\"",
						"classEx"		=> "t-observs w-40",
					]
				],
				[
					"classEx" 	=> "td",
					"content"	=>
					[
						"kind"					=> "components.documents.upload-files",
						"attributeExInput"		=> "type=\"file\" name=\"path\" accept=\".jpg,.jpeg,.png\"",
						"classExInput"			=> "pathActioner activity-image",
						"attributeExRealPath" 	=> "name=\"realPath\" data-validation=\"required\"",
						"classExRealPath"		=> "path",
						"classEx"				=> "md:p-0 border-0 w-40",
						"componentsExDown"		=>
						[
							[ "kind" => "components.inputs.input-text", "attributeEx" => "type=\"hidden\" name=\"t_image_activity[]\"", "classEx" => "t-image-activity"],
							[ "kind" => "components.inputs.input-text", "attributeEx" => "type=\"hidden\" name=\"id_image_activity[]\" value=\"x\"", "classEx" => "id-image-activity"],
							[ "kind" => "components.inputs.input-text", "attributeEx" => "type=\"hidden\" value=\"x\"", "classEx" => "new-image"],
							[ "kind" => "components.buttons.button-link", "attributeEx" => "target=\"_blank\" href=\"\" title=\"\"", "label" => "Ver archivo", "classEx" => "show-file hidden"],
						],
						"classExContentDown" 	=> "inline-block md:m-0",
						"classExContainer" 		=> "uploader-small-2 md:m-0",
						"classExContentAction"	=> "inline-block text-xs",
						"classExDelete" 		=> "delete-doc hidden",
						"variant"				=> "no-border",
					]
				],
				[
					"classEx" 	=> "td",
					"content"	=> $contentDocQuality,
				],
				[
					"classEx" 	=> "td",
					"content"	=>
					[
						"kind"			=> "components.inputs.input-text",
						"attributeEx"	=> "name=\"t_accumulated[]\" placeholder=\"Ingrese el acumulado\" data-validation=\"required number_no_zero\"",
						"classEx"		=> "t-accumulated w-40",
					]
				],
				[
					"classEx" 	=> "td",
					"content"	=>
					[
						"kind"			=> "components.buttons.button",
						"attributeEx"	=> "id=\"addActivity\" type=\"button\"",
						"classEx"		=> "addActivity",
						"variant" 		=> "primary",
						"label" 		=> "<span class='icon-plus'></span>",
					]
				],
			];
			$docs++;
		}
	@endphp
	@component('components.tables.table', [
		"modelBody" 		=> $modelBody,
		"modelHead" 		=> $modelHead,
		"attributeExBody"	=> "id=\"bodyactivity\""
	])
	@endcomponent
	<div id="activity-delete"></div>
	<div id="docs-delete"></div>
	<div class="grid grid-cols-2 md:grid-cols-4">
		<div class="col-span-2 pr-4">
			@component("components.labels.title-divisor") Maquinaria, Equipo y Herramientas @endcomponent
			@php
				$modelHead = ["Cantidad", "Descripción", "Acción"];
				$modelBody = [];
				if (isset($request))
				{
					foreach ($request->pcdrMEH as $index => $pcdrMEH)
					{
						$optionCatMachinery = collect();
						$optionCatMachinery = $optionCatMachinery->concat([["value" => $pcdrMEH->machinery_id, "selected" => "selected", "description" => $pcdrMEH->machineries->name]]);
						$modelBody[] = 
						[
							"classEx" => "tr",
							[
								"content" =>
								[
									[
										"kind" 			=> "components.inputs.input-text",
										"attributeEx"	=> "name=\"t_meh_quantity[]\" value=\"".$pcdrMEH->quantity."\" placeholder=\"Ingrese la cantidad\" data-validation=\"required number_no_zero\"",
										"classEx"		=> "t-meh-quantity",
									],
									[
										"kind" 			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"hidden\" name=\"pcdrMEH[]\" value=\"".$pcdrMEH->id."\"",
										"classEx"		=> "idpcdr-meh",
									]
								],
							],
							[
								"content" =>
								[
									[
										"kind"			=> "components.inputs.select",
										"attributeEx"	=> "name=\"t_meh_desc[]\" multiple data-validation=\"required\"",
										"classEx"		=> "js-meh-description t-meh-desc",
										"options"		=> $optionCatMachinery,
									],
								],
							],
							[
								"content" =>
								[
									[
										"kind"			=> "components.buttons.button",
										"attributeEx"	=> "type=\"button\"".($index == 0 ? " id=\"addmeh\"" : "data-kind=\"meh\""),
										"classEx"		=> ($index == 0 ? "addmeh" : "delete-item"),
										"variant" 		=> ($index == 0 ? "primary" : "red"),
										"label" 		=> ($index == 0 ? "<span class=\"icon-plus\"></span>" : "<span class=\"icon-bin\"></span>"),
									]
								],
							],
						];
					}
				}
				else 
				{
					$modelBody[] = 
					[
						"classEx" => "tr",
						[
							"content" =>
							[
								[
									"kind" 			=> "components.inputs.input-text",
									"attributeEx"	=> "name=\"t_meh_quantity[]\" placeholder=\"Ingrese la cantidad\" data-validation=\"required number_no_zero\"",
									"classEx"		=> "t-meh-quantity",
								],
								[
									"kind" 			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" name=\"pcdrMEH[]\" value=\"x\"",
									"classEx"		=> "idpcdr-meh",
								]
							],
						],
						[
							"content" =>
							[
								[
									"kind"			=> "components.inputs.select",
									"attributeEx"	=> "name=\"t_meh_desc[]\" multiple data-validation=\"required\"",
									"classEx"		=> "js-meh-description t-meh-desc",
								],
							],
						],
						[
							"content" =>
							[
								[
									"kind"			=> "components.buttons.button",
									"attributeEx"	=> "id=\"addmeh\" type=\"button\"",
									"classEx"		=> "addmeh",
									"variant" 		=> "primary",
									"label" 		=> "<span class=\"icon-plus\"></span>",
								]
							],
						],
					];	
				}
			@endphp
			@component("components.tables.alwaysVisibleTable",[
				"modelHead" 		=> $modelHead,
				"modelBody" 		=> $modelBody,
				"variant" 			=> "default",
				"attributeExBody" 	=> "id=\"bodymeh\"",
			])
			@endcomponent
		</div>
		<div class="col-span-2 pl-4">
			@component("components.labels.title-divisor") Personal @endcomponent
			@php
				$modelHead = ["Cantidad", "Personal", "Hrs.", "Acción"];
				$modelBody = [];
				if (isset($request))
				{
					foreach ($request->pcdrStaff as $index => $pcdrStaff)
					{
						$optionCatIndustrialStaff = collect();
						$optionCatIndustrialStaff = $optionCatIndustrialStaff->concat([["value" => $pcdrStaff->industrial_staff_id, "selected" => "selected", "description" => $pcdrStaff->staffIndustry->name]]);
						$modelBody[] = 
						[
							"classEx" => "tr",
							[
								"content" =>
								[
									[
										"kind" 			=> "components.inputs.input-text",
										"attributeEx"	=> "name=\"t_staff_quantity[]\" value=\"".$pcdrStaff->quantity."\" placeholder=\"Ingrese la cantidad\" data-validation=\"required number_no_zero\"",
										"classEx"		=> "t-staff-quantity",
									],
									[
										"kind" 			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"hidden\" name=\"pcdrStaff[]\" value=\"".$pcdrStaff->id."\"",
										"classEx"		=> "idpcdr-staff",
									]
								],
							],
							[
								"content" =>
								[
									[
										"kind"			=> "components.inputs.select",
										"attributeEx"	=> "name=\"t_staff_desc[]\" multiple data-validation=\"required\"",
										"classEx"		=> "js-staff t-staff-desc",
										"options"		=> $optionCatIndustrialStaff,
									],
								],
							],
							[
								"content" =>
								[
									[
										"kind" 			=> "components.inputs.input-text",
										"attributeEx"	=> "name=\"t_staff_quantity_hours[]\" value=\"".$pcdrStaff->hours."\" readonly placeholder=\"Ingrese la cantidad de horas\" data-validation=\"required number_no_zero\"",
										"classEx"		=> "t-staff-quantity-hours",
									],
								],
							],
							[
								"content" =>
								[
									[
										"kind"			=> "components.buttons.button",
										"attributeEx"	=> "type=\"button\"".($index == 0 ? " id=\"addpersonal\" name=\"addpersonal\"" : "data-kind=\"staff\""),
										"classEx"		=> ($index == 0 ? "addpersonal" : "delete-item"),
										"variant" 		=> ($index == 0 ? "primary" : "red"),
										"label" 		=> ($index == 0 ? "<span class=\"icon-plus\"></span>" : "<span class=\"icon-bin\"></span>"),
									]
								],
							],
						];
					}
				}
				else 
				{
					$modelBody[] = 
					[
						"classEx" => "tr",
						[
							"content" =>
							[
								[
									"kind" 			=> "components.inputs.input-text",
									"attributeEx"	=> "name=\"t_staff_quantity[]\" placeholder=\"Ingrese la cantidad\" data-validation=\"required number_no_zero\"",
									"classEx"		=> "t-staff-quantity",
								],
								[
									"kind" 			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" name=\"pcdrStaff[]\" value=\"x\"",
									"classEx"		=> "idpcdr-staff",
								]
							],
						],
						[
							"content" =>
							[
								[
									"kind"			=> "components.inputs.select",
									"attributeEx"	=> "name=\"t_staff_desc[]\" multiple data-validation=\"required\"",
									"classEx"		=> "js-staff t-staff-desc",
								],
							],
						],
						[
							"content" =>
							[
								[
									"kind" 			=> "components.inputs.input-text",
									"attributeEx"	=> "name=\"t_staff_quantity_hours[]\" readonly placeholder=\"Ingrese las horas\" data-validation=\"required number_no_zero\"",
									"classEx"		=> "t-staff-quantity-hours",
								],
							],
						],
						[
							"content" =>
							[
								[
									"kind"			=> "components.buttons.button",
									"attributeEx"	=> "type=\"button\" id=\"addpersonal\" name=\"addpersonal\"",
									"classEx"		=> "addpersonal",
									"variant" 		=> "primary",
									"label" 		=> "<span class=\"icon-plus\"></span>",
								]
							],
						],
					];
				}
			@endphp
			@component("components.tables.alwaysVisibleTable",[
				"modelHead" => $modelHead,
				"modelBody" => $modelBody,
				"variant" 	=> "default",
				"attributeExBody" => "id=\"bodystaff\"",
			])
			@endcomponent
		</div>
	</div>
	<div id="meh-delete"></div>
	<div id="staff-delete"></div>
	@component("components.labels.title-divisor") Firmas @endcomponent
	@php
		$modelHead = ["Nombre", "Cargo", "Acción"];
		$modelBody = [];
		if (isset($request))
		{
			foreach ($request->pcdrSignatures as $index => $pcdrSignatures)
			{
				$modelBody[] = 
				[
					"classEx" => "tr",
					[
						"content" =>
						[
							[
								"kind" 			=> "components.inputs.input-text",
								"attributeEx"	=> "name=\"t_signature_name[]\" value=\"".htmlentities($pcdrSignatures->name)."\" placeholder=\"Ingrese un nombre\" data-validation=\"required\"",
								"classEx"		=> "t-signature-name",
							],
							[
								"kind" 			=> "components.inputs.input-text",
								"attributeEx"	=> "type=\"hidden\" name=\"pcdrSignatures[]\" value=\"".$pcdrSignatures->id."\"",
								"classEx"		=> "idpcdr-signature",
							]
						],
					],
					[
						"content" =>
						[
							[
								"kind"			=> "components.inputs.input-text",
								"attributeEx"	=> "name=\"t_signature_position[]\" value=\"".htmlentities($pcdrSignatures->position)."\" placeholder=\"Ingrese el puesto\" data-validation=\"required\"",
								"classEx"		=> "t-signature-position",
							],
						],
					],
					[
						"content" =>
						[
							[
								"kind"			=> "components.buttons.button",
								"attributeEx"	=> "type=\"button\"".($index == 0 ? " id=\"addfirma\" name=\"addfirma\"" : "data-kind=\"signature\""),
								"classEx"		=> ($index == 0 ? "addfirma" : "delete-item"),
								"variant" 		=> ($index == 0 ? "primary" : "red"),
								"label" 		=> ($index == 0 ? "<span class=\"icon-plus\"></span>" : "<span class=\"icon-bin\"></span>"),
							]
						]
					],
				];
			}
		}
		else 
		{
			$modelBody[] = 
			[
				"classEx" => "tr",
				[
					"content" =>
					[
						[
							"kind" 			=> "components.inputs.input-text",
							"attributeEx"	=> "name=\"t_signature_name[]\" placeholder=\"Ingrese un nombre\" data-validation=\"required\"",
							"classEx"		=> "t-signature-name",
						],
						[
							"kind" 			=> "components.inputs.input-text",
							"attributeEx"	=> "type=\"hidden\" name=\"pcdrSignatures[]\" value=\"x\"",
							"classEx"		=> "idpcdr-signature",
						]
					],
				],
				[
					"content" =>
					[
						[
							"kind"			=> "components.inputs.input-text",
							"attributeEx"	=> "name=\"t_signature_position[]\" placeholder=\"Ingrese el puesto\" data-validation=\"required\"",
							"classEx"		=> "t-signature-position",
						],
					],
				],
				[
					"content" =>
					[
						[
							"kind"			=> "components.buttons.button",
							"attributeEx"	=> " type=\"button\" id=\"addfirma\" name=\"addfirma\"",
							"classEx"		=> "addfirma",
							"variant" 		=> "primary",
							"label" 		=> "<span class=\"icon-plus\"></span>",
						]
					]
				],
			];	
		}
	@endphp
	@component("components.tables.alwaysVisibleTable",[
		"modelHead" => $modelHead,
		"modelBody" => $modelBody,
		"attributeExBody" => "id=\"bodysignature\"",
		"variant" 	=> "default",
	])
	@endcomponent
	<div id="signature-delete"></div>
	<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mt-8">
		@if(isset($request) && $request != "")
			@component("components.buttons.button", ["variant"=>"primary"])
				@slot("classEx")
					text-center
					w-48 
					md:w-auto
				@endslot
				@slot("attributeEx")
					type="submit"
					name="enviar"
				@endslot
				ACTUALIZAR
			@endcomponent
		@else
			@component("components.buttons.button", ["variant"=>"primary"])
				@slot("classEx")
					text-center
					w-48 
					md:w-auto
				@endslot
				@slot("attributeEx")
					type="submit"
					name="enviar"
				@endslot
				REGISTRAR
			@endcomponent
		@endif
		@component("components.buttons.button", ["variant"=>"reset", "buttonElement"=>"a"])
			@slot("classEx")
				text-center
				w-48
				md:w-auto
			@endslot
			@slot("attributeEx")
				type="button"
				@if(isset($child_id))
					href="{{ url(App\Module::find($child_id)->url) }}"
				@else
					href="{{ url(App\Module::find($option_id)->url) }}"
				@endif
			@endslot
			CANCELAR
		@endcomponent
	</div>
	@endcomponent
@endsection
@section('scripts')
	<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
	<link rel="stylesheet" href="{{ asset('timepicker/style.min.css') }}">
	<script src="{{ asset('js/jquery-ui.js') }}"></script>
	<script src="{{ asset('js/datepicker.js') }}"></script>
	<script src="{{ asset('timepicker/timepicker.min.js') }}"></script>
	<script src="{{ asset('js/jquery.numeric.js') }}"></script>
	<script src="{{ asset('js/daterangepicker.js') }}"></script>
	<link rel="stylesheet" type="text/css" href="{{ asset('css/daterangepicker.css') }}" />
	<script>
		$(document).ready(function()
		{
			validateRequired();
			@php
				$selects = collect([
					[
						"identificator"    	     => ".js-weather", 
						"placeholder"			 => "Seleccione la condición climatológica",
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"    	     => ".js-internal-tm, .js-customer-tm", 
						"placeholder"			 => "Seleccione la categoría",
						"maximumSelectionLength" => "1"
					],
				]);
			@endphp
			@component("components.scripts.selects", ["selects" => $selects]) @endcomponent
			generalSelect({'selector': '.js-projects', 'model': 17, 'option_id':{{$option_id}}});
			generalSelect({'selector': '.js-contracts', 'depends': '.js-projects', 'model': 34});
			generalSelect({'selector': '.js-code_wbs', 'depends': '.js-contracts', 'model': 30});
			generalSelect({'selector': '.js-discipline', 'model': 35});
			generalSelect({'selector': '.js-contract-item', 'depends': '.js-contracts', 'model': 42});
			contract = $('.js-contracts option:selected').val();
			generalSelect({'selector': '.js-contractor', 'depends': '.js-code_wbs', 'extra': contract, 'model': 43});
			generalSelect({'selector': '.js-blueprints', 'depends': '.js-code_wbs', 'extra': contract, 'model': 44});
			generalSelect({'selector': '.js-meh-description', 'model': 45});
			generalSelect({'selector': '.js-staff', 'model': 46});
			$('.timepath').daterangepicker({
				timePicker : true,
				singleDatePicker:true,
				timePicker24Hour : true,
				autoApply: true,
				autoUpdateInput: false,
				locale : {
					format : 'HH:mm',
					"applyLabel": "Seleccionar",
					"cancelLabel": "Limpiar",
				}
			})
			.on('show.daterangepicker', function (ev, picker) 
			{
				picker.container.find(".calendar-table").remove();
			})
			.on('apply.daterangepicker', function(ev, picker)
			{
				$(this).val(picker.startDate.format('HH:mm'));
				$(this).removeClass('error');
				$(this).parent().find('.form-error').remove();
			})
			.on('cancel.daterangepicker', function(ev, picker)
			{
				$(this).val('');
				$(this).removeClass('valid');
				picker.container.find(".hourselect").val(0).trigger('change');
				picker.container.find(".minuteselect").val(0).trigger('change');
			})
			.on('hide.daterangepicker', function(ev, picker)
			{
				$(this).val(picker.startDate.format('HH:mm'));
			});
			$('.datepicker').datepicker({ maxDate: 0, dateFormat: "dd-mm-yy" });
			$('.t-quantity').numeric({altDecimal: ".", negative: false, decimalPlaces: 2});
			$('.t-meh-quantity,.t-staff-quantity').numeric({decimal: false, negative: false});
			$('.t-accumulated,.t-amount').numeric({negative: false, decimalPlaces: 2});
			calculateStaffHours();
			$('.t-quantity').each(function()
			{
				quantity = $(this).val();
				if(!($.isNumeric(quantity)))
				{
					$(this).val('');
					$(this).parents('.tr').find('.t-amount').val('');
				}
				else
				{
					pu = $(this).parents('.tr').find('.pu').val();
					if(pu != '' && pu != null)
					{
						amount = (parseFloat(quantity)*parseFloat(pu));
						$(this).parents('.tr').find('.t-amount').val(amount);
					}
				}
			});
			$('.js-projects').on('select2:unselecting', function (e)
			{
				e.preventDefault();
				swal({
					title		: "Eliminar Proyecto",
					text		: "Si elimina el proyecto, el contrato, el WBS y todas las actividades que ya se encontraban agregadas serán eliminadas.\n¿Desea continuar?",
					icon		: "warning",
					buttons		: ["Cancelar","OK"],
					dangerMode	: true,
				})
				.then((willClean) =>
				{
					if(willClean)
					{
						swal({
							icon: '{{ asset(getenv("LOADING_IMG")) }}',
							button: false,
							closeOnClickOutside: false,
							closeOnEsc: false
						});
						flagSwal = false;
						$('#bodyactivity').find('.idpcdrDetail').each(function(i,v)
						{
							if($(this).val() != 'x')
							{
								$('#activity-delete').append($('<input type="hidden" name="deleteActivity[]"/>').val($(this).val()));
								flagSwal = true;
							}
							else
							{
								idImageActivity 	= $(this).parents('.tr').find('.t-image-activity').val();
								idDocQuality 		= $(this).parents('.tr').find('.t-doc-quality').val();
								if(idImageActivity != null)
								{
									deleteDocsActivity(idImageActivity);
								}
								if(idDocQuality != null)
								{
									deleteDocsActivity(idDocQuality);
								}
								flagSwal = true;
							}
						});
						if(flagSwal)
						{
							$(this).val(null).trigger('change');
							$('.js-contracts').val(null).trigger('change');
							$('.js-code_wbs').val(null).trigger('change');
							$('#bodyactivity .tr').each(function(i,v)
							{
								if (i == 0)
								{
									$(this).find('.form-error').remove();
									$(this).find('.js-contract-item').val(null).trigger('change');
									$(this).find('.idpcdrDetail').val('x');
									$(this).find('.t-quantity').val('').removeClass('valid').removeClass('error').removeAttr('style');
									$(this).find('.t-amount').val('').removeClass('valid').removeClass('error').removeAttr('style');
									$(this).find('.js-contractor').val(null).trigger('change');
									$(this).find('.t-area').val('').removeClass('valid').removeClass('error').removeAttr('style');
									$(this).find('.t-place-area').val('').removeClass('valid').removeClass('error').removeAttr('style');
									$(this).find('.t-ppt').val('').removeClass('valid').removeClass('error').removeAttr('style');
									$(this).find('.js-blueprints').val(null).trigger('change');
									$(this).find('.t-observs').val('');
									$(this).find('.image_success').removeClass('image_success');
									$(this).find('.show-file').addClass('hidden');
									$(this).find('.path').val('');
									$(this).find('.t-image-activity').val('');
									$(this).find('.t-doc-quality').val('');
									$(this).find('.t-accumulated').val('').removeClass('valid').removeClass('error').removeAttr('style');
									$(this).find('.id-image-activity').val('x');
									$(this).find('.id-doc-quality').val('x');
									$(this).find('.delete-uploaded-file').addClass('hidden');
								}
								else
								{
									$(this).remove();
								}
							});
							setTimeout(function() {
								swal.close();
							}, 2000);
						}
						else
						{
							swal('','Lo sentimos ocurrió un error, por favor intentelo de nuevo.','error');
						}
					}
					else
					{
						swal.close();
					}
				});
			});
			$('.js-contracts').on('select2:unselecting', function (e)
			{
				e.preventDefault();
				swal({
					title		: "Eliminar Contrato",
					text		: "Si elimina el contrato, el WBS y todas las actividades que ya se encontraban agregadas serán eliminadas.\n¿Desea continuar?",
					icon		: "warning",
					buttons		: ["Cancelar","OK"],
					dangerMode	: true,
				})
				.then((willClean) =>
				{
					if(willClean)
					{
						swal({
							icon: '{{ asset(getenv("LOADING_IMG")) }}',
							button: false,
							closeOnClickOutside: false,
							closeOnEsc: false
						});
						flagSwal = false;
						$('#bodyactivity').find('.idpcdrDetail').each(function(i,v)
						{
							if($(this).val() != 'x')
							{
								$('#activity-delete').append($('<input type="hidden" name="deleteActivity[]"/>').val($(this).val()));
								flagSwal = true;
							}
							else
							{
								idImageActivity 	= $(this).parents('.tr').find('.t-image-activity').val();
								idDocQuality 		= $(this).parents('.tr').find('.t-doc-quality').val();
								if(idImageActivity != null)
								{
									deleteDocsActivity(idImageActivity);
								}
								if(idDocQuality != null)
								{
									deleteDocsActivity(idDocQuality);
								}
								flagSwal = true;
							}
						})
						
						if(flagSwal)
						{
							$(this).val(null).trigger('change');
							$('.js-code_wbs').val(null).trigger('change');
							$('#bodyactivity .tr').each(function(i,v)
							{
								if (i == 0)
								{
									$(this).find('.form-error').remove();
									$(this).find('.js-contract-item').val(null).trigger('change');
									$(this).find('.idpcdrDetail').val('x');
									$(this).find('.t-quantity').val('').removeClass('valid').removeClass('error').removeAttr('style');
									$(this).find('.t-amount').val('').removeClass('valid').removeClass('error').removeAttr('style');
									$(this).find('.js-contractor').val(null).trigger('change');
									$(this).find('.t-area').val('').removeClass('valid').removeClass('error').removeAttr('style');
									$(this).find('.t-place-area').val('').removeClass('valid').removeClass('error').removeAttr('style');
									$(this).find('.t-ppt').val('').removeClass('valid').removeClass('error').removeAttr('style');
									$(this).find('.js-blueprints').val(null).trigger('change');
									$(this).find('.t-observs').val('');
									$(this).find('.image_success').removeClass('image_success');
									$(this).find('.show-file').addClass('hidden');
									$(this).find('.path').val('');
									$(this).find('.t-image-activity').val('');
									$(this).find('.t-doc-quality').val('');
									$(this).find('.t-accumulated').val('').removeClass('valid').removeClass('error').removeAttr('style');
									$(this).find('.id-image-activity').val('x');
									$(this).find('.id-doc-quality').val('x');
									$(this).find('.delete-uploaded-file').addClass('hidden');
								}
								else
								{
									$(this).remove();
								}
							});
							setTimeout(function() {
								swal.close();
							}, 2000);
						}
						else
						{
							swal('','Lo sentimos ocurrio un error, por favor intentelo de nuevo.','error');
						}
					}
					else
					{
						swal.close();
					}
				});
			});
			$('.js-code_wbs').on('select2:unselecting', function (e)
			{
				e.preventDefault();
				swal({
					title		: "Eliminar WBS",
					text		: "Si elimina el WBS, todas las actividades que ya se encontraban agregadas serán eliminadas.\n¿Desea continuar?",
					icon		: "warning",
					buttons		: ["Cancelar","OK"],
					dangerMode	: true,
				})
				.then((willClean) =>
				{
					if(willClean)
					{
						swal({
							icon: '{{ asset(getenv("LOADING_IMG")) }}',
							button: false,
							closeOnClickOutside: false,
							closeOnEsc: false
						});
						flagSwal = false;
						$('#bodyactivity').find('.idpcdrDetail').each(function(i,v)
						{
							if($(this).val() != 'x')
							{
								$('#activity-delete').append($('<input type="hidden" name="deleteActivity[]"/>').val($(this).val()));
								flagSwal = true;
							}
							else
							{
								idImageActivity 	= $(this).parents('.tr').find('.t-image-activity').val();
								idDocQuality 		= $(this).parents('.tr').find('.t-doc-quality').val();
								if(idImageActivity != null)
								{
									deleteDocsActivity(idImageActivity);
								}
								if(idDocQuality != null)
								{
									deleteDocsActivity(idDocQuality);
								}
								flagSwal = true;
							}
						})
						
						if(flagSwal)
						{							
							$(this).val(null).trigger('change');
							$('#bodyactivity .tr').each(function(i,v)
							{
								if (i == 0)
								{
									$(this).find('.form-error').remove();
									$(this).find('.js-contract-item').val(null).trigger('change');
									$(this).find('.idpcdrDetail').val('x');
									$(this).find('.t-quantity').val('').removeClass('valid').removeClass('error').removeAttr('style');
									$(this).find('.t-amount').val('').removeClass('valid').removeClass('error').removeAttr('style');
									$(this).find('.js-contractor').val(null).trigger('change');
									$(this).find('.t-area').val('').removeClass('valid').removeClass('error').removeAttr('style');
									$(this).find('.t-place-area').val('').removeClass('valid').removeClass('error').removeAttr('style');
									$(this).find('.t-ppt').val('').removeClass('valid').removeClass('error').removeAttr('style');
									$(this).find('.js-blueprints').val(null).trigger('change');
									$(this).find('.t-observs').val('');
									$(this).find('.image_success').removeClass('image_success');
									$(this).find('.show-file').addClass('hidden');
									$(this).find('.path').val('');
									$(this).find('.t-image-activity').val('');
									$(this).find('.t-doc-quality').val('');
									$(this).find('.t-accumulated').val('').removeClass('valid').removeClass('error').removeAttr('style');
									$(this).find('.id-image-activity').val('x');
									$(this).find('.id-doc-quality').val('x');
									$(this).find('.delete-uploaded-file').addClass('hidden');
								}
								else
								{
									$(this).remove();
								}
							});
							setTimeout(function() {
								swal.close();
							}, 2000);
						}
						else
						{
							swal('','Lo sentimos ocurrió un error, por favor intentelo de nuevo.','error');
						}
					}
					else
					{
						swal.close();
					}
				});
			});
			doc = {{ $docs }};
			$(document).on('change','.js-contract-item',function()
			{
				contract = $(this).find('option:selected').val();
				tr 		 = $(this).parents('.tr');
				$.ajax(
				{
					type	: 'post',
					url		: '{{ route("project-control.daily-report.contract.item.data") }}',
					data	: {'contract':contract},
					error 	: function()
					{
						swal('','Sucedió un error, por favor intente de nuevo.','error');
					}
				})
				.done(function (data) 
				{
					tr.find('.activity').val(data.activity);
					tr.find('.unit').val(data.unit);
					tr.find('.pu').val(data.pu);
					if(!($.isNumeric(data.pu)))
					{
						tr.find('.t-amount').val('');
					}
					else
					{
						quantity = tr.find('.t-quantity').val();
						if(quantity != '' && quantity != null)
						{
							amount = (parseFloat(quantity)*parseFloat(data.pu));
							tr.find('.t-amount').val(amount);
						}
					}
				});
			})
			.on('change','.js-code_wbs',function()
			{
				contract 	= $('.js-contracts').find('option:selected').val();
				wbs 		= $(this).find('option:selected').val();
				if(wbs != null)
				{
					contract = $('.js-contracts option:selected').val();
					generalSelect({'selector': '.js-contractor', 'depends': '.js-code_wbs', 'extra': contract, 'model': 43});
					generalSelect({'selector': '.js-blueprints', 'depends': '.js-code_wbs', 'extra': contract, 'model': 44});
				}
			})
			.on('change','[name="worker_hours_from"],[name="worker_hours_to"]',function()
			{
				calculateStaffHours();
			})
			.on('change','.pathActioner',function(e)
			{
				filename		= $(this);
				uploadedName 	= $(this).parent('.uploader-content').siblings('.path');
				if(filename.hasClass('activity-image'))
				{
					kind 		= 'image';
					extention	= /\.jpg|\.png|\.jpeg/i;
					message		= 'jpg, jpeg ó png';
				}
				if(filename.hasClass('doc-quality'))
				{
					kind 		= 'pdf';
					extention 	= /\.pdf/i;
					message		= 'pdf';
				}
				
				if (filename.val().search(extention) == -1)
				{
					swal("", "@lang('messages.extension_allowed', ['param' => '"+message+"'])", "warning");
					$(this).val('');
				}
				else if (this.files[0].size>315621376)
				{
					swal('', 'El tamaño máximo de su archivo no debe ser mayor a 300Mb', 'warning');
					$(this).val('');
				}
				else
				{
					newImageActivity 	= filename.parents('.td').find('.new-image');
					newDocQuality 		= filename.parents('.td').find('.new-doc');
					filename.parents('.td').find('.show-file').addClass('hidden');
					filename.parents('.td').find('.delete-uploaded-file').addClass('hidden');
					filename.parent('.uploader-content').removeClass('image_success').addClass('loading');
					if(newImageActivity.length > 0)
					{
						if(newImageActivity.val() != 'x')
						{
							if(uploadedName.val() != null && uploadedName.val() != '')
							{
								$('#docs-delete').append($('<input type="hidden" name="deleteDocs[]"/>').val(uploadedName.val()));
							}
						}
						formData = new FormData();
						formData.append(filename.attr('name'), filename.prop("files")[0]);
						@if($option_id == 314)
							formData.append(uploadedName.attr('name'),uploadedName.val());
						@else
							formData.append(uploadedName.attr('name'),"");
						@endif
						formData.append('kind',kind);
						$.ajax(
						{
							type		: 'post',
							url			: '{{ route("project-control.daily-report.uploader") }}',
							data		: formData,
							contentType	: false,
							processData	: false,
							cache		: false,
							success		: function(r)
							{
								if(r.error=='DONE')
								{
									$(e.currentTarget).removeAttr('style').parent('.uploader-content').removeClass('loading').addClass('image_success');
									$(e.currentTarget).parent('.uploader-content').siblings('input[name="realPath"]').val(r.path);
									$(e.currentTarget).parents('.td').find('.t-image-activity').val(r.path);
									$(e.currentTarget).val('');
									url = '{{ asset('docs/daily_report_operations/') }}/'+r.path;
									$(e.currentTarget).parents('.td').find('.show-file').attr("href", url).attr("title", r.path).removeClass('hidden');
									$(e.currentTarget).parents('.td').find('.delete-uploaded-file').removeClass('hidden');
									$(e.currentTarget).parents('.td').find('.new-image').val('x');
								}
								else
								{
									swal('',r.message, 'error');
									$(e.currentTarget).removeAttr('style').parent('.uploader-content').removeClass('loading');
									$(e.currentTarget).val('');
									$(e.currentTarget).parent('.uploader-content').siblings('input[name="realPath"]').val('');
									$(e.currentTarget).parents('.td').find('.t-image-activity').val('');
									$(e.currentTarget).parents('.td').find('.new-image').val('x');
								}
							},
							error: function(err)
							{
								swal('', 'Ocurrió un error durante la carga del archivo, intente de nuevo, por favor', 'error');
								if($(e.currentTarget).parent('.uploader-content').siblings('input[name="realPath"]').val() != "")
								{
									$(e.currentTarget).removeAttr('style').parent('.uploader-content').removeClass('loading').addClass('image_success');
									$(e.currentTarget).parents('.td').find('.show-file').removeClass('hidden');
									$(e.currentTarget).parents('.td').find('.delete-uploaded-file').removeClass('hidden');
								}
								else
								{
									$(e.currentTarget).removeAttr('style').parent('.uploader-content').removeClass('loading');
									$(e.currentTarget).val('');
									$(e.currentTarget).parent('.uploader-content').siblings('input[name="realPath"]').val('');
									$(e.currentTarget).parents('.td').find('.t-image-activity').val('');
									$(e.currentTarget).parents('.td').find('.new-image').val('x');
								}
							}
						});
					}
					if(newDocQuality.length > 0)
					{
						if(newDocQuality.val() != 'x')
						{
							if(uploadedName.val() != null && uploadedName.val() != '')
							{
								$('#docs-delete').append($('<input type="hidden" name="deleteDocs[]"/>').val(uploadedName.val()));
							}
						}
						formData	= new FormData();
						formData.append(filename.attr('name'), filename.prop("files")[0]);
						@if($option_id == 314)
							formData.append(uploadedName.attr('name'),uploadedName.val());
						@else
							formData.append(uploadedName.attr('name'),"");
						@endif
						formData.append('kind',kind);
						$.ajax(
						{
							type		: 'post',
							url			: '{{ route("project-control.daily-report.uploader") }}',
							data		: formData,
							contentType	: false,
							processData	: false,
							cache		: false,
							success		: function(r)
							{
								if(r.error=='DONE')
								{
									$(e.currentTarget).removeAttr('style').parent('.uploader-content').removeClass('loading').addClass('image_success');
									$(e.currentTarget).parent('.uploader-content').siblings('input[name="realPath"]').val(r.path);
									$(e.currentTarget).parents('.td').find('.t-doc-quality').val(r.path);
									$(e.currentTarget).val('');
									url = '{{ asset('docs/daily_report_operations/') }}/'+r.path;
									$(e.currentTarget).parents('.td').find('.show-file').attr("href", url).attr("title", r.path).removeClass('hidden');
									$(e.currentTarget).parents('.td').find('.delete-uploaded-file').removeClass('hidden');
									$(e.currentTarget).parents('.td').find('.new-doc').val('x');
								}
								else
								{
									swal('',r.message, 'error');
									$(e.currentTarget).removeAttr('style').parent('.uploader-content').removeClass('loading');
									$(e.currentTarget).val('');
									$(e.currentTarget).parent('.uploader-content').siblings('input[name="realPath"]').val('');
									$(e.currentTarget).parents('.td').find('.t-doc-quality').val('');
									$(e.currentTarget).parents('.td').find('.new-doc').val('x');
								}
							},
							error: function(err)
							{
								swal('', 'Ocurrió un error durante la carga del archivo, intente de nuevo, por favor', 'error');
								if($(e.currentTarget).parent('.uploader-content').siblings('input[name="realPath"]').val() != "")
								{
									$(e.currentTarget).removeAttr('style').parent('.uploader-content').removeClass('loading').addClass('image_success');
									$(e.currentTarget).parents('.td').find('.show-file').removeClass('hidden');
									$(e.currentTarget).parents('.td').find('.delete-uploaded-file').removeClass('hidden');
								}
								else
								{
									$(e.currentTarget).removeAttr('style').parent('.uploader-content').removeClass('loading');
									$(e.currentTarget).val('');
									$(e.currentTarget).parent('.uploader-content').siblings('input[name="realPath"]').val('');
									$(e.currentTarget).parents('.td').find('.t-doc-quality').val('');
									$(e.currentTarget).parents('.td').find('.new-doc').val('x');
								}
							}
						})
					}
				}
			})
			.on('click','#addActivity',function()
			{
				@php
					$modelHead	=
					[
						[
							["value" => "Pda. Contrato"],
							["value" => "Actividad"],
							["value" => "Cant."],
							["value" => "Unid."],
							["value" => "P.U."],
							["value" => "Monto"],
							["value" => "Contratista"],
							["value" => "Área"],
							["value" => "Lugar del área"],
							["value" => "No. de PPT(s)/Órden de trab."],
							["value" => "Planos"],
							["value" => "Observaciones"],
							["value" => "Adj. imagen"],
							["value" => "Doc. calidad"],
							["value" => "Acumulado"],
							["value" => "Acciones"],
						]
					];

					$modelBody		= [];
					$contentDocQuality = [];
					if($flagQuality)
					{
						$contentDocQuality[] = 
						[
							"kind"					=> "components.documents.upload-files",
							"attributeExInput"		=> "type=\"file\" name=\"path\" accept=\".pdf\"",
							"classExInput"			=> "pathActioner doc-quality",
							"attributeExRealPath" 	=> "name=\"realPath\"",
							"classExRealPath"		=> "path",
							"classEx"				=> "md:p-0 border-0 w-40",
							"componentsExDown"		=>
							[
								[ "kind" => "components.inputs.input-text", "attributeEx" => "type=\"hidden\" name=\"t_doc_quality[]\"", "classEx" => "t-doc-quality"],
								[ "kind" => "components.inputs.input-text", "attributeEx" => "type=\"hidden\" name=\"id_doc_quality[]\" value=\"x\"", "classEx" => "id-doc-quality"],
								[ "kind" => "components.inputs.input-text", "attributeEx" => "type=\"hidden\" value=\"x\"", "classEx" => "new-doc"],
								[ "kind" => "components.buttons.button-link", "attributeEx" => "target=\"_blank\" href=\"\" title=\"\"", "label" => "Ver archivo", "classEx" => "show-file hidden"],
							],
							"classExContentDown" 	=> "inline-block md:m-0",
							"classExContainer" 		=> "uploader-small-2 md:m-0",
							"classExContentAction"	=> "inline-block text-xs",
							"classExDelete" 		=> "delete-doc hidden",
							"variant"				=> "no-border",
						];
					}
					else 
					{
						$contentDocQuality[] = 
						[
							"label" => " ",
						];
					}

					$modelBody[] =
					[
						"classEx"		=> "tr pt-2",
						[
							"classEx" 	=> "td md:p-0",
							"content"	=>
							[
								[
									"kind"			=> "components.inputs.select",
									"attributeEx"	=> "name=\"t_pda_contract[]\" multiple data-validation=\"required\"",
									"classEx"		=> "js-contract-item w-40",
								],
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "name=\"idpcdrDetail[]\" value=\"x\" type=\"hidden\"",
									"classEx"		=> "idpcdrDetail",
								]
							]
						],
						[
							"classEx" 	=> "td",
							"content"	=>
							[
								"kind"			=> "components.inputs.text-area",
								"attributeEx"	=> "readonly",
								"classEx"		=> "activity h-11 p-2 w-40",
							]
						],
						[
							"classEx" 	=> "td",
							"content"	=>
							[
								"kind"			=> "components.inputs.input-text",
								"attributeEx"	=> "name=\"t_quantity[]\" placeholder=\"Ingrese la cantidad\" data-validation=\"required number_no_zero\"",
								"classEx"		=> "t-quantity w-40",
							]
						],
						[
							"classEx" 	=> "td",
							"content"	=>
							[
								"kind"			=> "components.inputs.input-text",
								"attributeEx"	=> "disabled",
								"classEx"		=> "unit w-40",
							]
						],
						[
							"classEx" 	=> "td",
							"content"	=>
							[
								"kind"			=> "components.inputs.input-text",
								"attributeEx"	=> "disabled",
								"classEx"		=> "pu w-40",
							]
						],
						[
							"classEx" 	=> "td",
							"content"	=>
							[
								"kind"			=> "components.inputs.input-text",
								"attributeEx"	=> "name=\"t_amount[]\" readonly placeholder=\"Ingrese el monto\" data-validation=\"required number_no_zero\"",
								"classEx"		=> "t-amount w-40",
							]
						],
						[
							"classEx" 	=> "td",
							"content"	=>
							[
								"kind"			=> "components.inputs.select",
								"attributeEx"	=> "name=\"t_contractor[]\" multiple data-validation=\"required\"",
								"classEx"		=> "js-contractor w-40",
							]
						],
						[
							"classEx" 	=> "td",
							"content"	=>
							[
								"kind"			=> "components.inputs.input-text",
								"attributeEx"	=> "name=\"t_area[]\" placeholder=\"Ingrese el área\" data-validation=\"required\"",
								"classEx"		=> "t-area w-40",
							]
						],
						[
							"classEx" 	=> "td",
							"content"	=>
							[
								"kind"			=> "components.inputs.input-text",
								"attributeEx"	=> "name=\"t_place_area[]\" placeholder=\"Ingrese el lugar del área\" data-validation=\"required\"",
								"classEx"		=> "t-place-area w-40",
							]
						],
						[
							"classEx" 	=> "td",
							"content"	=>
							[
								"kind"			=> "components.inputs.input-text",
								"attributeEx"	=> "name=\"t_ppt[]\" placeholder=\"Ingrese el No. de PPT(s)/Órden de trabajo\" data-validation=\"required\"",
								"classEx"		=> "t-ppt w-40",
							]
						],
						[
							"classEx" 	=> "td",
							"content"	=>
							[
								"kind"			=> "components.inputs.select",
								"attributeEx"	=> "name=\"t_blueprint[]\" multiple data-validation=\"required\"",
								"classEx"		=> "js-blueprints w-40",
							]
						],
						[
							"classEx" 	=> "td",
							"content"	=>
							[
								"kind"			=> "components.inputs.input-text",
								"attributeEx"	=> "name=\"t_observs[]\" placeholder=\"Ingrese las observaciones\"",
								"classEx"		=> "t-observs w-40",
							]
						],
						[
							"classEx" 	=> "td",
							"content"	=>
							[
								"kind"					=> "components.documents.upload-files",
								"attributeExInput"		=> "type=\"file\" name=\"path\" accept=\".jpg,.jpeg,.png\"",
								"classExInput"			=> "pathActioner activity-image",
								"attributeExRealPath" 	=> "name=\"realPath\" data-validation=\"required\"",
								"classExRealPath"		=> "path",
								"classEx"				=> "md:p-0 border-0 w-40",
								"componentsExDown"		=>
								[
									[ "kind" => "components.inputs.input-text", "attributeEx" => "type=\"hidden\" name=\"t_image_activity[]\"", "classEx" => "t-image-activity"],
									[ "kind" => "components.inputs.input-text", "attributeEx" => "type=\"hidden\" name=\"id_image_activity[]\" value=\"x\"", "classEx" => "id-image-activity"],
									[ "kind" => "components.inputs.input-text", "attributeEx" => "type=\"hidden\" value=\"x\"", "classEx" => "new-image"],
									[ "kind" => "components.buttons.button-link", "attributeEx" => "target=\"_blank\" href=\"\" title=\"\"", "label" => "Ver archivo", "classEx" => "show-file hidden"],
								],
								"classExContentDown" 	=> "inline-block md:m-0",
								"classExContainer" 		=> "uploader-small-2 md:m-0",
								"classExContentAction"	=> "inline-block text-xs",
								"classExDelete" 		=> "delete-doc hidden",
								"variant"				=> "no-border",
							]
						],
						[
							"classEx" 	=> "td",
							"content"	=> $contentDocQuality,
						],
						[
							"classEx" 	=> "td",
							"content"	=>
							[
								"kind"			=> "components.inputs.input-text",
								"attributeEx"	=> "name=\"t_accumulated[]\" placeholder=\"Ingrese el acumulado\" data-validation=\"required number_no_zero\"",
								"classEx"		=> "t-accumulated w-40",
							]
						],
						[
							"classEx" 	=> "td",
							"content"	=>
							[
								"kind"			=> "components.buttons.button",
								"attributeEx"	=> "type=\"button\"",
								"classEx"		=> "delete-activity",
								"variant" 		=> "red",
								"label" 		=> "<span class=\"icon-bin\"></span>",
							]
						],
					];

					$table = view("components.tables.table",[
					"modelBody" 		=> $modelBody,
					"modelHead" 		=> $modelHead,
					"attributeExBody"	=> "id=\"bodyactivity\"",
					"noHead"			=> true
					])->render();
					$table2 = html_entity_decode(preg_replace("/(\r)*(\n)*/", "", $table));
				@endphp
				table = '{!!preg_replace("/(\r)*(\n)*/", "", $table2)!!}';
				row = $(table);
				$('#bodyactivity').append(row);
				$('.t-quantity').numeric({altDecimal: ".", negative: false, decimalPlaces: 2});
				$('.t-accumulated,.t-amount').numeric({negative: false, decimalPlaces: 2});
				validateRequired();
				generalSelect({'selector': '.js-contract-item', 'depends': '.js-contracts', 'model': 42});
				contract = $('.js-contracts option:selected').val();
				generalSelect({'selector': '.js-contractor', 'depends': '.js-code_wbs', 'extra': contract, 'model': 43});
				generalSelect({'selector': '.js-blueprints', 'depends': '.js-code_wbs', 'extra': contract, 'model': 44});
				doc++;
				stickyAdjustment();
			})
			.on('click','.delete-activity',function()
			{
				idDetail 	= $(this).parents('.tr').find('.idpcdrDetail').val();
				actioner 	= $(this);
				path 		= [];
				flagDocs	= false;
				$(this).parents('.tr').find('.path').each(function()
				{
					if($(this).val() != null && $(this).val() != "")
					{
						path.push($(this).val());
					}
				});
				
				if(idDetail != 'x')
				{
					$('#activity-delete').append($('<input type="hidden" name="deleteActivity[]"/>').val(idDetail));
					flagDocs = true;
				}
				else
				{
					if(path != null && path != '')
					{
						countPaths = path.length;
						for (i = 0; i < countPaths; i++) 
						{
							uploadedName = path[i];
							deleteDocsActivity(uploadedName);
							if(i == countPaths-1)
							{
								flagDocs = true;
							}
						}
					}
					else
					{
						flagDocs = true;
					}
				}
				if(flagDocs)
				{
					$(this).parents('.tr').remove();
				}
				else
				{
					swal('','Lo sentimos ocurrió un error, por favor intentelo de nuevo.','error');
				}
			})
			.on('click','#addmeh',function()
			{
				@php
					$modelHead = ["Cantidad", "Descripción", "Acción"];
					$modelBody = [];
					$modelBody[] = 
					[
						"classEx" => "tr",
						[
							"content" =>
							[
								[
									"kind" 			=> "components.inputs.input-text",
									"attributeEx"	=> "name=\"t_meh_quantity[]\" placeholder=\"Ingrese la cantidad\" data-validation=\"required number_no_zero\"",
									"classEx"		=> "t-meh-quantity",
								],
								[
									"kind" 			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" name=\"pcdrMEH[]\" value=\"x\"",
									"classEx"		=> "idpcdr-meh",
								]
							],
						],
						[
							"content" =>
							[
								[
									"kind"			=> "components.inputs.select",
									"attributeEx"	=> "name=\"t_meh_desc[]\" multiple data-validation=\"required\"",
									"classEx"		=> "js-meh-description t-meh-desc",
								],
							],
						],
						[
							"content" =>
							[
								[
									"kind"			=> "components.buttons.button",
									"attributeEx"	=> "data-kind=\"meh\" type=\"button\"",
									"classEx"		=> "delete-item",
									"variant" 		=> "red",
									"label" 		=> "<span class=\"icon-bin\"></span>",
								]
							],
						],
					];
					$table = view("components.tables.alwaysVisibleTable",[
					"modelHead" => $modelHead,
					"modelBody" => $modelBody,
					"noHead" => true,
					"variant" => "default"
					])->render();
					$table2 = html_entity_decode(preg_replace("/(\r)*(\n)*/", "", $table));
				@endphp
				
				table = '{!!preg_replace("/(\r)*(\n)*/", "", $table2)!!}';
				row = $(table);
				row = rowColor('#bodymeh', row);
				$("#bodymeh").append(row);
				generalSelect({'selector': '.js-meh-description', 'model': 45});
				$('.t-meh-quantity').numeric({decimal: false, negative: false});
				validateRequired();

				// Original
				// tr_table	= $('<tr></tr>')
				// 	.append($('<td></td>')
				// 		.append($('<input type="text" class="t-meh-quantity new-input-text" name="t_meh_quantity[]" placeholder="0" data-validation="required number_no_zero"/>'))
				// 		.append($('<input type="hidden" class="idpcdr-meh" name="pcdrMEH[]"/>').val('x'))
				// 	)
				// 	.append($('<td></td>')
				// 		.append($('<select class="js-meh-description t-meh-desc" name="t_meh_desc[]" style="width:100%;" multiple data-validation="required"></select>')
				// 			@foreach(App\CatMachinery::get() as $machinery)
				// 				.append($('<option value="{{ $machinery->id }}">{{ $machinery->name }}</option>'))
				// 			@endforeach
				// 		)
				// 	)
				// 	.append($('<td></td>')
				// 		.append($('<button class="btn btn-red delete-item" type="button" data-kind="meh"></button>')
				// 			.append($('<span class="icon-bin"></span>'))
				// 		)
				// 	);
				// $('#bodymeh').append(tr_table);
				// $('.js-meh-description').select2(
				// {
				// 	language				: "es",
				// 	maximumSelectionLength	: 1,
				// 	placeholder 			: "Seleccione uno",
				// 	width 					: "95%"
				// })
				// .on("change",function(e)
				// {
				// 	if($(this).val().length>1)
				// 	{
				// 		$(this).val($(this).val().slice(0,1)).trigger('change');
				// 	}
				// });
				// $('.t-meh-quantity').numeric({decimal: false, negative: false});
				// validateRequired();
			})
			.on('click','#addpersonal',function()
			{
				@php
					$modelHead = ["Cantidad", "Personal", "Hrs.", "Acción"];
					$modelBody = [];
					$modelBody[] = 
					[
						"classEx" => "tr",
						[
							"content" =>
							[
								[
									"kind" 			=> "components.inputs.input-text",
									"attributeEx"	=> "name=\"t_staff_quantity[]\" placeholder=\"Ingrese la cantidad\" data-validation=\"required number_no_zero\"",
									"classEx"		=> "t-staff-quantity",
								],
								[
									"kind" 			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" name=\"pcdrStaff[]\" value=\"x\"",
									"classEx"		=> "idpcdr-staff",
								]
							],
						],
						[
							"content" =>
							[
								[
									"kind"			=> "components.inputs.select",
									"attributeEx"	=> "name=\"t_staff_desc[]\" multiple data-validation=\"required\"",
									"classEx"		=> "js-staff t-staff-desc",
								],
							],
						],
						[
							"content" =>
							[
								[
									"kind" 			=> "components.inputs.input-text",
									"attributeEx"	=> "name=\"t_staff_quantity_hours[]\" readonly placeholder=\"Ingrese las horas\" data-validation=\"required number_no_zero\"",
									"classEx"		=> "t-staff-quantity-hours",
								],
							],
						],
						[
							"content" =>
							[
								[
									"kind"			=> "components.buttons.button",
									"attributeEx"	=> "data-kind=\"staff\" type=\"button\"",
									"classEx"		=> "delete-item",
									"variant" 		=> "red",
									"label" 		=> "<span class=\"icon-bin\"></span>",
								]
							],
						],
					];
					$table = view("components.tables.alwaysVisibleTable",[
					"modelHead" => $modelHead,
					"modelBody" => $modelBody,
					"noHead" => true,
					"variant" => "default"
					])->render();
					$table2 = html_entity_decode(preg_replace("/(\r)*(\n)*/", "", $table));
				@endphp
				table = '{!!preg_replace("/(\r)*(\n)*/", "", $table2)!!}';
				row = $(table);
				row = rowColor('#bodystaff', row);
				$("#bodystaff").append(row);
				generalSelect({'selector': '.js-staff', 'model': 46});
				$('.t-staff-quantity').numeric({decimal: false, negative: false});
				validateRequired();

				// Original
				// tr_table	= $('<tr></tr>')
				// 	.append($('<td></td>')
				// 		.append($('<input type="text" class="t-staff-quantity new-input-text" name="t_staff_quantity[]" placeholder="0" data-validation="required number_no_zero"/>'))
				// 		.append($('<input type="hidden" class="idpcdr-staff" name="pcdrStaff[]"/>').val('x'))
				// 	)
				// 	.append($('<td></td>')
				// 		.append($('<select class="js-staff t-staff-desc" name="t_staff_desc[]" style="width:100%;" multiple data-validation="required"></select>')
				// 			@foreach (App\CatIndustrialStaff::get() as $staff)
				// 				.append($('<option value="{{ $staff->id }}">{{ $staff->name }}</option>'))
				// 			@endforeach
				// 		)
				// 	)
				// 	.append($('<td></td>')
				// 		.append($('<input type="text" class="t-staff-quantity-hours new-input-text" name="t_staff_quantity_hours[]" readonly placeholder="0" data-validation="required number_no_zero"/>'))
				// 	)
				// 	.append($('<td></td>')
				// 		.append($('<button class="btn btn-red delete-item" type="button" data-kind="staff"></button>')
				// 			.append($('<span class="icon-bin"></span>'))
				// 		)
				// 	);
				// $('#bodystaff').append(tr_table);
				// $('.js-staff').select2(
				// {
				// 	language				: "es",
				// 	maximumSelectionLength	: 1,
				// 	placeholder 			: "Seleccione uno",
				// 	width 					: "95%"
				// })
				// .on("change",function(e)
				// {
				// 	if($(this).val().length>1)
				// 	{
				// 		$(this).val($(this).val().slice(0,1)).trigger('change');
				// 	}
				// });
				// $('.t-staff-quantity').numeric({decimal: false, negative: false});
				// validateRequired();
			})
			.on('click','#addfirma',function()
			{
				@php
					$modelHead = ["Nombre", "Cargo", "Acción"];
					$modelBody = [];
					$modelBody[] = 
					[
						"classEx" => "tr",
						[
							"content" =>
							[
								[
									"kind" 			=> "components.inputs.input-text",
									"attributeEx"	=> "name=\"t_signature_name[]\" placeholder=\"Ingrese un nombre\" data-validation=\"required\"",
									"classEx"		=> "t-signature-name",
								],
								[
									"kind" 			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" name=\"pcdrSignatures[]\" value=\"x\"",
									"classEx"		=> "idpcdr-signature",
								]
							],
						],
						[
							"content" =>
							[
								[
									"kind" 			=> "components.inputs.input-text",
									"attributeEx"	=> "name=\"t_signature_position[]\" placeholder=\"Ingrese el puesto\" data-validation=\"required\"",
									"classEx"		=> "t-signature-position",
								],
							],
						],
						[
							"content" =>
							[
								[
									"kind"			=> "components.buttons.button",
									"attributeEx"	=> "data-kind=\"signature\" type=\"button\"",
									"classEx"		=> "delete-item",
									"variant" 		=> "red",
									"label" 		=> "<span class=\"icon-bin\"></span>",
								]
							],
						],
					];
					$table = view("components.tables.alwaysVisibleTable",[
					"modelHead" => $modelHead,
					"modelBody" => $modelBody,
					"noHead" 	=> true,
					"variant" 	=> "default"
					])->render();
					$table2 = html_entity_decode(preg_replace("/(\r)*(\n)*/", "", $table));
				@endphp
				
				table = '{!!preg_replace("/(\r)*(\n)*/", "", $table2)!!}';
				row = $(table);
				row = rowColor('#bodysignature', row);
				$("#bodysignature").append(row);
				validateRequired();
				// Original
				// tr_table = $('<tr></tr>')
				// 	.append($('<td></td>')
				// 		.append($('<input type="text" class="t-signature-name new-input-text" name="t_signature_name[]" placeholder="Ingrese un nombre" data-validation="required"/>'))
				// 		.append($('<input type="hidden" class="idpcdr-signature" name="pcdrSignatures[]"/>').val('x'))
				// 	)
				// 	.append($('<td></td>')
				// 		.append($('<input type="text" class="t-signature-position new-input-text" name="t_signature_position[]" placeholder="Ingrese el puesto" data-validation="required"/>'))
				// 	)
				// 	.append($('<td></td>')
				// 		.append($('<button class="btn btn-red  delete-item" type="button" data-kind="signature"></button>')
				// 			.append($('<span class="icon-bin"></span>'))
				// 		)
				// 	);
				// $('#bodysignature').append(tr_table);
				// validateRequired();
			})
			.on('click','.delete-doc',function()
			{
				newImageActivity 	= $(this).parents('.td').find('.new-image');
				newDocQuality 		= $(this).parents('.td').find('.new-doc');
				path 				= $(this).parents('.td').find('.path').val();
				
				if(newImageActivity.length > 0)
				{
					if(newImageActivity.val() != 'x')
					{
						if(path != null && path != '')
						{
							$('#docs-delete').append($('<input type="hidden" name="deleteDocs[]"/>').val(path));
						}
					}
					else
					{
						if(path != null && path != '')
						{
							uploadedName = path;
							deleteDocsActivity(uploadedName);
						}
					}
				}

				if(newDocQuality.length > 0)
				{
					if(newDocQuality.val() != 'x')
					{
						if(path != null && path != '')
						{
							$('#docs-delete').append($('<input type="hidden" name="deleteDocs[]"/>').val(path));
						}
					}
					else
					{
						if(path != null && path != '')
						{
							uploadedName = path;
							deleteDocsActivity(uploadedName);
						}
					}
				}
				
				$(this).parents('.td').find('.image_success').removeClass('image_success');
				$(this).parents('.td').find('.show-file').addClass('hidden');
				$(this).parents('.td').find('.path').val('');
				$(this).parents('.td').find('.t-image-activity').val('');
				$(this).parents('.td').find('.t-doc-quality').val('');
				$(this).parents('.td').find('.new-image').val('x');
				$(this).parents('.td').find('.new-doc').val('x');
				$(this).parents('.td').find('.delete-uploaded-file').addClass('hidden');
			})
			.on('click','.delete-item',function()
			{
				kind = $(this).attr('data-kind');
				switch (kind)
				{
					case 'meh':
						id = $(this).parents('.tr').find('.idpcdr-meh').val();
						if(id != 'x')
						{
							$('#meh-delete').append($('<input type="hidden" name="deleteMEH[]"/>').val(id));
						}
						break;
					
					case 'staff':
						id = $(this).parents('.tr').find('.idpcdr-staff').val();
						if(id != 'x')
						{
							$('#staff-delete').append($('<input type="hidden" name="deleteStaff[]"/>').val(id));
						}
						break;

					case 'signature':
						id = $(this).parents('.tr').find('.idpcdr-signature').val();
						if(id != 'x')
						{
							$('#signature-delete').append($('<input type="hidden" name="deleteSignature[]"/>').val(id));
						}
						break;
				}
				$(this).parents('.tr').remove();
			})
			.on('focusout','.t-quantity',function()
			{
				quantity = $(this).val();
				if(!($.isNumeric(quantity)))
				{
					$(this).val('');
					$(this).parents('.tr').find('.t-amount').val('');
				}
				else
				{
					pu = $(this).parents('.tr').find('.pu').val();
					if(pu != '' && pu != null)
					{
						amount = (parseFloat(quantity)*parseFloat(pu));
						$(this).parents('.tr').find('.t-amount').val(amount);
					}
				}
			})
			.on('focusout','.t-staff-quantity',function()
			{
				if(!($.isNumeric($(this).val())))
				{
					$(this).val('');
					$(this).parents('.tr').find('.t-staff-quantity-hours').val('');
				}
				else
				{
					calculateStaffHours();
				}
			})
			.on('focusout','.t-meh-quantity,.t-accumulated',function()
			{
				if(!($.isNumeric($(this).val())))
				{
					$(this).val('');
				}
			});
		});
		function calculateStaffHours()
		{
			from = $('[name="worker_hours_from"]').val().split(":");
			to 	 = $('[name="worker_hours_to"]').val().split(":");
			$('#bodystaff .tr').each(function()
			{
				staffQuantity = $(this).find('.t-staff-quantity').val();
				if(staffQuantity != "" && staffQuantity != null)
				{
					totalHours = parseInt(to[0], 10) - parseInt(from[0], 10);
					totalMinutes = parseInt(to[1], 10) - parseInt(from[1], 10);

					
					if(totalMinutes < 0)
					{
						totalHours = totalHours - 1; 
					}
					if(totalHours < 0) 
					{
						totalHours = 24 + totalHours;
					}

					totalHoursStaff = (parseInt(staffQuantity)*parseInt(totalHours));
					if(totalHoursStaff != 0)
					{
						$(this).find('.t-staff-quantity-hours').val(totalHoursStaff);
					}
					else
					{
						$(this).find('.t-staff-quantity-hours').val('');
					}
				}
			})
		}
		function deleteDocsActivity(uploadedName)
		{
			formData		= new FormData();
			formData.append('realPath',uploadedName);

			$.ajax(
			{
				type		: 'post',
				url			: '{{ route("project-control.daily-report.uploader") }}',
				data		: formData,
				contentType	: false,
				processData	: false,
			});
		}
		function validateRequired()
		{
			$.validate(
			{
				form: '#dailyReport',
				modules: 'security',
				validateHiddenInputs: true,
				onError : function($form)
				{
					$('.error-tm').remove();
					tm_internal_to 		= $('[name="internal_tm_to"]');
					tm_internal_from 	= $('[name="internal_tm_from"]');
					tm_customer_to 		= $('[name="customer_tm_to"]');
					tm_customer_from	= $('[name="customer_tm_from"]');
					if(tm_internal_from.val() != '')
					{
						if(tm_internal_to.val() == '')
						{
							$('.span-error-tmi').append('<span class="error-tm form-error">No puede seleccionar solo una hora</span>');
						}
					} 
					if(tm_internal_to.val() != '')
					{
						if(tm_internal_from.val() == '')
						{
							$('.span-error-tmi').append('<span class="error-tm form-error">No puede seleccionar solo una hora</span>');
						}
					}
					if(tm_customer_from.val() != '')
					{
						if(tm_customer_to.val() == '')
						{
							$('.span-error-tmc').append('<span class="error-tm form-error">No puede seleccionar solo una hora</span>');
						}
					} 
					if(tm_customer_to.val() != '')
					{
						if(tm_customer_from.val() == '')
						{
							$('.span-error-tmc').append('<span class="error-tm form-error">No puede seleccionar solo una hora</span>');
						}
					} 
					swal('', '{{ Lang::get("messages.form_error") }}', 'error');
				},
				onSuccess : function($form)
				{
					$('.error-tm').remove();
					tm_internal_to 		= $('[name="internal_tm_to"]');
					tm_internal_from 	= $('[name="internal_tm_from"]');
					tm_customer_to 		= $('[name="customer_tm_to"]');
					tm_customer_from 	= $('[name="customer_tm_from"]');
					flagTM				= true;

					if(tm_internal_from.val() != '')
					{
						if(tm_internal_to.val() == '')
						{
							$('.span-error-tmc').append('<span class="error-tm form-error">No puede seleccionar solo una hora</span>');
							flagTM = false;
						}
					} 
					if(tm_internal_to.val() != '')
					{
						if(tm_internal_from.val() == '')
						{
							$('.span-error-tmc').append('<span class="error-tm form-error">No puede seleccionar solo una hora</span>');
							flagTM = false;
						}
					}
					if(tm_customer_from.val() != '')
					{
						if(tm_customer_to.val() == '')
						{
							tm_customer_to.parent().parent().append('<span class="error-tm form-error">No puede seleccionar solo una hora</span>');
							flagTM = false;
						}
					} 
					if(tm_customer_to.val() != '')
					{
						if(tm_customer_from.val() == '')
						{
							tm_customer_from.parent().parent().append('<span class="error-tm form-error">No puede seleccionar solo una hora</span>');
							flagTM = false;
						}
					} 

					if(flagTM)
					{
						swal({
							icon: '{{ asset(getenv("LOADING_IMG")) }}',
							button: false,
							closeOnClickOutside: false,
							closeOnEsc: false
						});
						return true;
					}
					else
					{
						swal('', 'Por favor verifique todos sus campos.', 'error');
						return false;
					}
				}
			});
		}
	</script>
@endsection
