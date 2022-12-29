@extends('layouts.child_module')
@section('data')
	@if (isset($globalRequests) && $globalRequests == true)
		@component("components.labels.not-found", ["variant" => "note"])
			@slot("slot")
				@component("components.labels.label")
					@slot("classEx")
						font-bold
						inline-block
						text-blue-900
					@endslot
						TIPO DE SOLICITUD: 
				@endcomponent
				{{ mb_strtoupper($request->requestkind->kind) }}
			@endslot
		@endcomponent
	@endif

	@if(isset($request) && !isset($new_request))
		@component("components.forms.form",["methodEx" => "PUT","attributeEx" => "method=\"POST\" action=\"".route("stationery.follow.update",$request->folio)."\" id=\"container-alta\""])
	@elseif(isset($request) && isset($new_request))
		@component("components.forms.form",["attributeEx" => "method=\"POST\" action=\"".route("stationery.store")."\" id=\"container-alta\""])
	@else
		@component("components.forms.form",["attributeEx" => "method=\"POST\" action=\"".route("stationery.store")."\" id=\"container-alta\""])
	@endif
	@component('components.labels.title-divisor') NUEVA SOLICITUD @endcomponent
	@component('components.containers.container-form')
		<div class="col-span-2">
			@component("components.labels.label") Título: @endcomponent
			@component("components.inputs.input-text")
				@slot("classEx")
					removeselect
				@endslot
				@slot("attributeEx")
					name="title"
					placeholder="Ingrese un título"
					data-validation="required"
					@if(isset($request)) 
						value="{{ $request->stationery->first()->title }}"
						@if($request->status!=2 && !isset($new_request)) 
							disabled="disabled"
						@endif
					@endif
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component("components.labels.label") Fecha: @endcomponent
			@component("components.inputs.input-text")
				@slot("classEx")
					datepicker
					removeselect
				@endslot
				@slot("attributeEx")
					name="datetitle"
					data-validation="required"
					placeholder="Ingrese la fecha"
					readonly="readonly"
					@if(isset($request))
						value="{{ $request->stationery->first()->datetitle != "" ? Carbon\Carbon::createFromFormat('Y-m-d',$request->stationery->first()->datetitle)->format('d-m-Y') : '' }}" 
						@if($request->status!=2 && !isset($new_request))
							disabled="disabled"
						@endif
					@endif
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component("components.labels.label") Solicitante: @endcomponent
			@php
				$options = collect();
				if(isset($request) && $request->idRequest)
				{
					$user = App\User::find($request->idRequest);
					$options = $options->concat([["value"=>$user->id, "selected"=>"selected","description"=>$user->name. " " .$user->last_name. " " .$user->scnd_last_name]]);
				}
				if(isset($request) && $request->status!=2 && !isset($new_request))
				{
					$attributeEx = "name=\"user_id\" id=\"multiple-users\" data-validation=\"required\" disabled=\"disabled\"";
				}
				else
				{
					$attributeEx = "name=\"user_id\" id=\"multiple-users\" data-validation=\"required\"";
				}
				$classEx = "js-users removeselect";
			@endphp
			@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])@endcomponent
		</div>
		<div class="col-span-2">
			@component("components.labels.label") Departamento: @endcomponent
			@php
				$options = collect();
				foreach ($departments as $department)
				{
					$description = 	$department->name;
					if(isset($request) && $request->idDepartment == $department->id)
					{
						$options = $options->concat([["value"=>$department->id, "selected"=>"selected", "description"=>$description]]);
					}
					else 
					{
						$options = $options->concat([["value"=>$department->id, "description"=>$description]]);
					}
				}
				if(isset($request) && $request->status!=2 && !isset($new_request)){
					$attributeEx = "name=\"department_id\" id=\"multiple-departments\" data-validation=\"required\" disabled=\"disabled\"";
				}
				else
				{
					$attributeEx = "name=\"department_id\" id=\"multiple-departments\" data-validation=\"required\"";
				}
				$classEx = "js-departments removeselect";
			@endphp
			@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])@endcomponent
		</div>
		<div class="col-span-2">
			@component("components.labels.label") Dirección: @endcomponent
			@php
				$options = collect();
				foreach ($areas as $area) 
				{
					$description = $area->name;
					if(isset($request) && $request->idArea == $area->id)
					{
						$options = $options->concat([["value"=>$area->id, "selected"=>"selected", "description"=>$description]]);
					}
					else 
					{
						$options = $options->concat([["value"=>$area->id, "description"=>$description]]);	
					}
				}
				if(isset($request) && $request->status!=2 && !isset($new_request))
				{
					$attributeEx = "name=\"area_id\" id=\"multiple-areas\" data-validation=\"required\" disabled=\"disabled\"";
				}
				else
				{
					$attributeEx = "name=\"area_id\" id=\"multiple-areas\" data-validation=\"required\"";
				}
				$classEx = "js-areas removeselect";
			@endphp
			@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])@endcomponent
		</div>
		<div class="col-span-2">
			@component("components.labels.label") Empresa: @endcomponent
			@php
				$options = collect();
				foreach ($enterprises as $enterprise) 
				{
					$description = strlen($enterprise->name) >= 35 ? substr(strip_tags($enterprise->name),0,35)."..." : $enterprise->name;
					if (isset($request) && $request->idEnterprise == $enterprise->id) 
					{
						$options = $options->concat([["value"=>$enterprise->id, "selected"=>"selected", "description"=>$description]]);
					}
					else 
					{
						$options = $options->concat([["value"=>$enterprise->id, "description"=>$description]]);
					}
				}
				if(isset($request) && $request->status!=2 && !isset($new_request)){
					$attributeEx = "name=\"enterprise_id\" id=\"multiple-enterprises select2-selection--multiple\" data-validation=\"required\" disabled=\"disabled\"";
				}
				else
				{
					$attributeEx = "name=\"enterprise_id\" id=\"multiple-enterprises select2-selection--multiple\" data-validation=\"required\"";
				}
				$classEx = "js-enterprises removeselect";
			@endphp
			@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])@endcomponent
		</div>
		<div class="col-span-2">
			@component("components.labels.label") Clasificación del gasto: @endcomponent
			@php
				$options = collect();
				if(isset($request) && $request->account)
				{
					$account = App\Account::find($request->account);
					$options = $options->concat([["value"=>$account->idAccAcc,"selected"=>"selected", "description"=>$account->account." ".$account->description." ".$account->content]]);
				}				
				if(isset($request) && $request->status!=2 && !isset($new_request)){
					$attributeEx = "name=\"account_id\" data-validation=\"required\" disabled=\"disabled\"";
				}
				else
				{
					$attributeEx = "name=\"account_id\" data-validation=\"required\"";
				}
				$classEx = "js-accounts removeselect";
			@endphp
			@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])@endcomponent
		</div>
		<div class="col-span-2">
			@component("components.labels.label") Proyecto/Contrato: @endcomponent
			@php
				$options = collect();
				if(isset($request) && $request->idProject)
				{
					$project = App\Project::find($request->idProject);
					$options = $options->concat([["value"=>$project->idproyect, "selected"=>"selected","description"=>$project->proyectName]]);
				}
				if(isset($request) && $request->status!=2 && !isset($new_request)){
					$attributeEx = "name=\"project_id\" id=\"multiple-projects\" data-validation=\"required\" disabled=\"disabled\"";
				}
				else
				{
					$attributeEx = "name=\"project_id\" id=\"multiple-projects\" data-validation=\"required\"";
				}
				$classEx = "js-projects removeselect";
			@endphp
			@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])@endcomponent
		</div>
		<div class="col-span-2">
			@component("components.labels.label") Subcontratista/Proveedor: @endcomponent
			@php
				$value = "";
				if (isset($request))
				{
					if($request->stationery()->first())
					{
						$value=$request->stationery()->first()->subcontractorProvider;
					}
				}
			@endphp
			@component('components.inputs.input-text')
				@slot('attributeEx')
					name='SubcontractorProvider' 
					placeholder='Ingrese un nombre de proveedor' 
					value='{{$value}}' 
					@if(isset($request) && $request->status!=2 && !isset($new_request))
						disabled 
					@endif 
				@endslot
				@slot('classEx')
					remove SubcontractorProvider
				@endslot
			@endcomponent
		</div>
	@endcomponent		
	@component("components.labels.title-divisor")    
		Detalles del articulo 
		<span class="help-btn" id="help-btn-articles"></span> 
	@endcomponent
	@if(isset($request) || isset($new_request))
		@if($request->status==2 || isset($new_request))
			@component("components.containers.container-form")
				<div class="col-span-2">
					@component("components.labels.label") Categoría: @endcomponent
					@php
						$options = collect();
						foreach(App\CatWarehouseType::all() as $category)
						{
							$description = $category->description;
							$options = $options->concat([["value"=>$category->id , "description"=>$description]]);
						}
						$attributeEx = "name=\"category\"";
						$classEx = "js-category removeselect";
					@endphp
					@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])@endcomponent
				</div>
				<div class="col-span-2">
					@component("components.labels.label") Concepto: @endcomponent
					@component("components.inputs.input-text")
						@slot("classEx")
							remove
						@endslot
						@slot("attributeEx")
							name="material"
							placeholder="Ingrese el concepto"
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component("components.labels.label") Cantidad: @endcomponent
					@component("components.inputs.input-text")
						@slot("classEx")
							remove 
							quantity
						@endslot
						@slot("attributeEx")
							name="quantity"
							placeholder="Ingrese la cantidad"
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component("components.labels.label")Código corto (Si es que tiene): @endcomponent
					@component("components.inputs.input-text")
						@slot("classEx")
							remove 
							short_code
						@endslot
						@slot("attributeEx")
							name="short_code"
							placeholder="Ingrese el código corto"
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component("components.labels.label") Código largo (Si es que tiene): @endcomponent
					@component("components.inputs.input-text")
						@slot("classEx")
							remove 
							long_code
						@endslot
						@slot("attributeEx")
							name="long_code"
							placeholder="Ingrese el código largo"
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component("components.labels.label") Comentario (Opcional): @endcomponent
					@component("components.inputs.text-area")
						@slot("attributeEx")
							name="commentaries"
							id="commentaries"
							rows="7"
							placeholder="Ingrese un comentario"
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2 md:col-span-4 grid justify-items-center md:justify-items-start">
					@component("components.buttons.button", ["variant" => "warning"])
						@slot("attributeEx")
							id="add"
							type="button"
							name="add"
						@endslot
						<span class="icon-plus"></span>
						<span>Agregar concepto</span>
					@endcomponent
				</div>
			@endcomponent
		@endif
	@else
		@component("components.containers.container-form")
			<div class="col-span-2">
				@component("components.labels.label") Categoria: @endcomponent
				@php
					$options = collect();
					foreach(App\CatWarehouseType::all() as $category)
					{
						$description = $category->description;
						$options = $options->concat([["value"=>$category->id , "description"=>$description]]);
					}
					$attributeEx = "name=\"category\"";
					$classEx = "js-category removeselect";
				@endphp
				@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Concepto: @endcomponent
				@component("components.inputs.input-text")
					@slot("classEx")
					remove
					@endslot
					@slot("attributeEx")
						name="material"
						placeholder="Ingrese el concepto"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Cantidad: @endcomponent
				@component("components.inputs.input-text")
					@slot("classEx")
						remove 
						quantity
					@endslot
					@slot("attributeEx")
						name="quantity"
						placeholder="Ingrese la cantidad"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Código corto (Si es que tiene): @endcomponent
				@component("components.inputs.input-text")
					@slot("classEx")
						remove 
						short_code
					@endslot
					@slot("attributeEx")
						name="short_code"
						placeholder="Ingrese el código corto"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Código largo (Si es que tiene): @endcomponent
				@component("components.inputs.input-text")
					@slot("classEx")
						remove 
						long_code
					@endslot
					@slot("attributeEx")
						name="long_code"
						placeholder="Ingrese el código largo"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Comentario (Opcional): @endcomponent
				@component("components.inputs.text-area")
					@slot("attributeEx")
						name="commentaries"
						id="commentaries"
						rows="7"
						placeholder="Ingrese un comentario"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2 md:col-span-4 grid justify-items-center md:justify-items-start">
				@component("components.buttons.button", ["variant" => "warning"])
					@slot("attributeEx")
						type="button"
						id="add"
						name="add"
					@endslot
					<span class="icon-plus"></span>
					<span>Agregar concepto</span>
				@endcomponent
			</div>
		@endcomponent
	@endif
	@php
		$body		=	[];
		$modelBody	=	[];
		$modelHead 	= [
			[
				["value" => "#", "show" => "true"],
				["value" => "Categoría", "show" => "true"],
				["value" => "Cantidad"],
				["value" => "Concepto"],
				["value" => "Código corto"],
				["value" => "Código largo"],
				["value" => "Comentario"]
			]
		];
		
		if(isset($request) && ($request->status == 9 || $request->status == 19))
		{
			$modelHead[0][] = ["value" => "Producto entregado"];
		}
		
		if(!isset($request) || (isset($request) && ($request->status == 2 || isset($new_request))))
		{
			$modelHead[0][] = ["value" => "Acción"];
		}		

		if(isset($request) && $request->stationery->first()->detailStat()->exists())
		{
			foreach ($request->stationery->first()->detailStat as $key=>$detail) 
			{
				$body = 
				[
					
					[
						"show"		=>"true",
						"classEx"	=> "countConcept",
						"content"	=>
						[
							[
								"label"	=> $key+1,
							]
						]
					],
					[
						"content"=>
						[
							[
								"label"	=>	$detail->categoryData()->exists() ? $detail->categoryData->description : "",
							],
							[
								"kind"			=>	"components.inputs.input-text",
								"classEx"		=>	"tidStatDetail",										
								"attributeEx"	=>	"readonly=\"true\" type=\"hidden\" name=\"tidStatDetail[]\" value=\"".$detail->idStatDetail."\"",
							],
							[
								"kind"			=>	"components.inputs.input-text",
								"classEx"		=>	"tcategory",
								"attributeEx"	=>	"readonly=\"true\" type=\"hidden\" name=\"tcategory[]\" value=\"".$detail->category."\"",
							]
						]
					],
					[
						"content"=>
						[
							[
								"label"=>$detail->quantity,
							],
							[
								"kind"			=>	"components.inputs.input-text",
								"classEx"		=>	"tquanty",
								"attributeEx"	=>	"readonly=\"true\" type=\"hidden\" name=\"tquanty[]\" value=\"".$detail->quantity."\"",
							]
						]
					],
					[
						"content"=>
						[
							[
								"label"=> htmlentities($detail->product),
							],
							[
								"kind"			=>	"components.inputs.input-text",
								"classEx"		=>	"tmaterial",
								"attributeEx"	=>	"readonly=\"true\" type=\"hidden\" name=\"tmaterial[]\" value=\"".htmlentities($detail->product)."\"",
							]
						]
					],
					[
						"content"=>
						[
							[
								"label"=>isset($detail->short_code) ? htmlentities($detail->short_code) : " --- ",
							],
							[
								"kind"			=>	"components.inputs.input-text",
								"classEx"		=>	"tshort_code",
								"attributeEx"	=>	"readonly=\"true\" type=\"hidden\" name=\"tshort_code[]\" value=\"".htmlentities($detail->short_code)."\"",
							]
						]
					],
					[
						"content"=>
						[
							[
							"label"=>isset($detail->long_code) ? htmlentities($detail->long_code) : " --- ",
							],
							[
								"kind"			=>	"components.inputs.input-text",
								"classEx"		=>	"tlong_code",
								"attributeEx"	=>	"readonly=\"true\" type=\"hidden\" name=\"tlong_code[]\" value=\"".htmlentities($detail->long_code)."\"",
							]
						]
					],
					[
						"content"=>
						[
							[
							"label"=>isset($detail->commentaries) ? htmlentities($detail->commentaries) : "Sin comentarios",
							],
							[
								"kind"			=>	"components.inputs.input-text",
								"classEx"		=>	"tcommentaries",
								"attributeEx"	=>	"readonly=\"true\" type=\"hidden\" name=\"tcommentaries[]\" value=\"".htmlentities($detail->commentaries)."\"",
							]
						]
					],
				];
				if (isset($request) && ($request->status == 9 || $request->status == 19)) 
				{
					$body[] = [
						"content" =>
						[
							"label"=>$detail->productDelivery()->exists() ? $detail->productDelivery->cat_c->description : "Aún no se entrega"
						]
					];	
				}
				if(isset($request) || isset($new_request))
				{
					if ($request->status == 2 || isset($new_request)) 
					{
						$body[]["content"] =
						[
							[
								"kind"      => "components.buttons.button",
								"classEx"	=> "delete-item",
								"label"		=> "<span class=icon-x></span>",
								"variant"	=> "red",
							]
						];
					}
				}
				else 
				{
					$body[]["content"] =
					[
						[
							"kind"      => "components.buttons.button",
							"classEx"	=> "delete-item",
							"label"		=> "<span class=icon-x></span>",
							"variant"	=> "red",		
						]
					];	
				}
				
				$modelBody[] = $body;
			}
		}
		else 
		{
			$modelBody = [];
		}			
	@endphp
	@component("components.tables.table",[
		"modelHead"	=> $modelHead,
		"modelBody"	=> $modelBody,
	])
		@slot("classEx")	
			text-center table
		@endslot
		@slot("attributeEx")
			id="table"
		@endslot
		@slot("attributeExBody")
			id="body"
		@endslot
		@slot("classExBody")
			request-validate
		@endslot
	@endcomponent
	<div id="delete_arts"></div>

	@if(isset($request) && $request->idCheck != "" && !isset($new_request))
		<div class="mt-4">
			@component("components.labels.title-divisor") DATOS DE REVISIÓN @endcomponent
		</div>
		<div class="my-6">
			@component("components.tables.table-request-detail.container",["variant"=>"simple"])
				@php
					$modelTable = ["Reviso" => $request->reviewedUser->name." ".$request->reviewedUser->last_name." ".$request->reviewedUser->scnd_last_name];
					if ($request->idEnterpriseR!="")
					{
						$modelTable = ["Nombre de la Empresa" => App\Enterprise::find($request->idEnterpriseR)->name,
										"Nombre de la Dirección" => $request->reviewedDirection->name,
										"Nombre del Departamento" => App\Department::find($request->idDepartamentR)->name,
										"Clasificación del gasto" => $reviewAccount = App\Account::find($request->accountR),
										];
						if(isset($reviewAccount->account))
						{
							$account = $reviewAccount->account. " - ".$reviewAccount->description;
						}
						else
						{
							$account = "No hay";
						}
						$modelTable ['Clasificación del gasto'] = $account;
						$labels = "";
						foreach($request->labels as $label)
						{
							$labels = $labels." ".$label->description."," ;
						}
						$modelTable ['Etiquetas'] = $labels ? $labels : "Sin etiquetas";
					}
					if($request->checkComment == "")
					{
						$modelTable["Comentarios"] = "Sin comentarios";
					}
					else 
					{
						$modelTable["Comentarios"] = htmlentities($request->checkComment);
					}
				@endphp
				@component("components.templates.outputs.table-detail-single", ["modelTable" => $modelTable])@endcomponent
			@endcomponent
		</div>

		@component("components.labels.title-divisor") Etiquetas Asignadas @endcomponent
		@if(isset($request) && $request->idEnterpriseR!="")
			<div class="block overflow-auto w-full text-center">
				@php
					$heads = ["#","Cantidad", "Concepto", "Etiquetas"];
					$modelBody = [];

					$countConcept = 1;
					foreach ($request->stationery->first()->detailStat as $key=>$detail)
					{
						$body =
						[
							"classEx" => "tr",
							[
								"content" =>
								[
									[
										"label" => $countConcept,
									]
								]
							],
							[
								"content" =>
								[
									[
										"label" => $detail->quantity,
									]
								]
							],
							[
								"content" =>
								[
									[
										"label" => htmlentities($detail->product),
									]
								]
							],						
						];
						$etiquetas = "";

						foreach ($detail->labels as $label) {
							$etiquetas = $etiquetas." ".$label->label->description;
						}
						$body[] =
						[
							"content" =>
							[
								"label" => $etiquetas ? $etiquetas : "Sin etiquetas",
							]
						];
						$countConcept++;
						$modelBody[] = $body;
					}
				@endphp
				@component("components.tables.alwaysVisibleTable",[
					"modelHead" => $heads,
					"modelBody" => $modelBody,
				])
				@slot("classExBody")
					request-validate
				@endslot
				@slot("attributeExBody")
					id="tbody-conceptsNew"
				@endslot
				@endcomponent
			</div>
		@endif
	@endif
	@if(isset($request) && $request->idAuthorize != "" && !isset($new_request))
		@component("components.labels.title-divisor") Datos de Autorización @endcomponent
		<div class="my-6">
			@component("components.tables.table-request-detail.container",["variant" => "simple"])
				@php
					$modelTable = ["Autorizó" => $request->authorizedUser->name." ".$request->authorizedUser->last_name." ".$request->authorizedUser->scnd_last_name,
									"Comentarios" => $request->authorizeComment == "" ? "Sin Comentarios" : htmlentities($request->authorizeComment)]
				@endphp
				@component("components.templates.outputs.table-detail-single", ["modelTable" => $modelTable])@endcomponent
			@endcomponent
		</div>
		<div>
			@if($request->code != null)
				<div class="text-center space-y-2 justify-center grid">
					<div class="w-full">Código:</div>
					<div class="w-full justify-center grid">
						@component("components.labels.label")
							@slot("classEx")
								text-3xl border-2 border-warm-gray-400
							@endslot
							{{ $request->code  }}
						@endcomponent
					</div>
					<div class="w-full">Este código es necesario para que le entreguen sus artículos.</div>
				</div>		
			@endif
		</div>
	@endif
	<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mt-8">
		@if (isset($request) && !isset($new_request))
			@if ($request->status == "2")
				@component('components.buttons.button',["variant"=>"primary"])
					@slot('attributeEx')
						type="submit"
						name="enviar"
					@endslot
					ENVIAR SOLICITUD
				@endcomponent
				@if(!isset($new_request))
					@component('components.buttons.button',["variant"=>"secondary"])
						@slot('classEx')
							save
						@endslot
						@slot('attributeEx')
							type="submit"
							id="save"
							name="save"
							formaction="{{ route('stationery.follow.updateunsent', $request->folio) }}"
						@endslot
						GUARDAR SIN ENVIAR
					@endcomponent
				@endif
			@endif
			@component('components.buttons.button', [ "buttonElement" => "a", "variant" => "reset"])
				@slot("attributeEx")
					@if(isset($option_id)) 
						href="{{ url(App\Module::find($option_id)->url) }}" 
					@else 
						href="{{ url(App\Module::find($child_id)->url) }}" 
					@endif
				@endslot
				@slot('classEx')
					load-actioner
				@endslot
				REGRESAR 
			@endcomponent
		@elseif(isset($request) && isset($new_request))
			@component('components.buttons.button', ["variant" => "primary"])
				@slot('classEx')
					enviar
				@endslot
				@slot('attributeEx')
					type="submit"
					name="enviar"
				@endslot
				ENVIAR SOLICITUD
			@endcomponent
			@component('components.buttons.button',["variant"=>"secondary"])
				@slot('classEx')
					save
				@endslot
				@slot('attributeEx')
					type="submit"
					id="save"
					name="save"
					value="Guardar sin enviar"
					formaction="{{ route('stationery.unsent') }}"
				@endslot
				GUARDAR SIN ENVIAR
			@endcomponent
			@component('components.buttons.button',["variant"=>"reset"])
				@slot('classEx')
					btn-delete-form
				@endslot
				@slot('attributeEx')
					type="reset"
					name="borra"
					value="Borrar campos"
				@endslot
				BORRAR CAMPOS
			@endcomponent
		@else
			@component('components.buttons.button',["variant"=>"primary"])
				@slot('classEx')
					enviar
				@endslot
				@slot('attributeEx')
					type="submit"
					id="enviar"
					value="Enviar Solicitud"
				@endslot
				ENVIAR SOLICITUD
			@endcomponent
			@component('components.buttons.button',["variant"=>"secondary"])
				@slot('classEx')
					save
				@endslot
				@slot('attributeEx')
					type="submit"
					id="save"
					name="save"
					value="Guardar sin enviar"
					formaction="{{ route('stationery.unsent') }}"
				@endslot
				GUARDAR SIN ENVIAR
			@endcomponent
			@component('components.buttons.button',["variant"=>"reset"])
				@slot('classEx')
					btn-delete-form
				@endslot
				@slot('attributeEx')
					type="reset"
					name="borra"
					value="Borrar campos"
				@endslot
				BORRAR CAMPOS
			@endcomponent
		@endif
	</div>
	@endcomponent
@endsection
@section("scripts")
	<link rel="stylesheet" href="{{ asset("css/jquery-ui.css") }}">
	<script src="{{ asset('js/jquery-ui.js') }}"></script>
	<script src="{{ asset('js/jquery.numeric.js') }}"></script>
	<script src="{{ asset('js/datepicker.js') }}"></script>
	<script>
		$(document).ready(function()
		{
			@php
				$selects = collect([
					[
						"identificator"				=> ".js-category",
						"placeholder"				=> "Seleccione la categoria",
						"language"					=> "es",
						"maximumSelectionLength"	=> "1"
					],
					[
						"identificator"				=> ".js-enterprises",
						"placeholder"				=> "Seleccione la empresa",
						"language"				 	=> "es",
						"maximumSelectionLength"	=> "1"
					],
					[
						"identificator"				=> ".js-areas",
						"placeholder"				=> "Seleccione la dirección",
						"language"				 	=> "es",
						"maximumSelectionLength"	=> "1"
					],
					[
						"identificator"				=> ".js-departments",
						"placeholder"				=> "Seleccione el departamento",
						"language"				 	=> "es",
						"maximumSelectionLength"	=> "1"
					]
				]);	
			@endphp
			@component("components.scripts.selects",["selects" => $selects])@endcomponent
			generalSelect({'selector': '.js-users', 'model': 13});
			generalSelect({'selector': '.js-projects', 'model': 21});
			generalSelect({'selector': '.js-accounts', 'depends': '.js-enterprises', 'model': 5});
			$('.quantity').numeric({ negative : false, altDecimal: ".", decimalPlaces: 2 });
			$('.price,.amount,.tquanty,.tprice,.tamount,.descuento,.totaliva,.subtotal,.total',).numeric({ altDecimal: ".", decimalPlaces: 2 });
			$(function() 
			{
				$( ".datepicker" ).datepicker({ minDate: 0, dateFormat: "dd-mm-yy" });
			});
			$(document).on('click','#save',function()
			{
				$('.remove').removeAttr('data-validation');
				$('.removeselect').removeAttr('required');
				$('.removeselect').removeAttr('data-validation');
				$('.request-validate').removeClass('request-validate');
			})
			.on('click','#add',function()
			{
				countConcept	= $('.countConcept').length;
				cant			= $('input[name="quantity"]').val().trim();
				short_code		= $('input[name="short_code"]').val().trim();
				long_code		= $('input[name="long_code"]').val().trim();
				material		= $('input[name="material"]').val().trim();
				comm			= $('textarea[id="commentaries"]').val().trim();
				category_id		= $('[name="category"] option:selected').val();
				category_name  	= $('[name="category"] option:selected').text();
				
				if (comm == "") 
				{
					comm = "Sin comentarios"
				}
				if (cant == "" || material == "" || category_id == undefined)
				{
					if (cant == "") 
					{
						$('input[name="quantity"]').addClass('error');
					} 
					if(material == "")
					{
						$('input[name="material"]').addClass('error');
					}
					if(category_id == undefined)
					{
						$('[name="category"]').addClass('error');
					}
					swal('', 'Por favor llene los campos necesarios', 'error');
				}
				else if (cant <= 0)
				{
					$('input[name="quantity"]').addClass('error');
					swal('', 'La cantidad no puede ser 0', 'error');
				}
				else
				{
					if($('input[name="new_request"]').val()!="")
					{
						
					}
					if(cant == 0)
					{
						swal('','La cantidad no puede ser 0.','error');
						return false;
					}
					countConcept = countConcept + 1;
					@php
						$modelHead = [
							[
								["value" => "#"],
								["value" => "Categoría"],
								["value" => "Cantidad"],
								["value" => "Concepto"],
								["value" => "Código corto"],
								["value" => "Código largo"],
								["value" => "Comentario"],
							]
						];
						if(isset($new_request))
						{
							if(isset($new_request) && ($request->status == 9 || $request->status == 19))
							{
								$modelHead[0][] = ["value" => "Producto Entregado"];
							}
						}
						$modelHead[0][] = ["value" => "Acción"];
						
						$modelBody = 
						[
							[
								
								[
									"content" =>
									[
										[
											"kind" 		=> "components.labels.label", 
											"classEx" 	=> "countConcept", 
											"label" 	=> ""
										]
									]
								],
								[
									"content" =>
									[
										[
											"kind" 		=> "components.labels.label", 
											"classEx" 	=> "category_name", 
											"label" 	=> ""
										],
										[
											"kind" 			=> "components.inputs.input-text", 
											"attributeEx" 	=> "readonly=\"true\" name=\"tcategory[]\" type=\"hidden\"", 
											"classEx" 		=> "tcategory"
										],
										[
											"kind" 			=> "components.inputs.input-text", 
											"attributeEx" 	=> "readonly=\"true\" name=\"tidStatDetail[]\"  type=\"hidden\"", 
											"classEx" 		=> "tidStatDetail"
										]
									]
								],
								[
									"content" =>
									[
										[
											"kind" 			=> "components.inputs.input-text", 
											"attributeEx" 	=> "readonly=\"true\" name=\"tquanty[]\" type=\"hidden\"", 
											"classEx" 		=> "tquanty"
										],
										[
											"kind" 		=> "components.labels.label", 
											"classEx" 	=> "cant", 
											"label" 	=> ""
										],
									]
								],
								[
									"content" => 
									[
										[
											"kind" 		=> "components.labels.label", 
											"classEx" 	=> "material",
											 "label" 	=> ""
										],
										[
											"kind" 			=> "components.inputs.input-text", 
											"attributeEx" 	=> "readonly=true name=tmaterial[] type=hidden", 
											"classEx" 		=> "tdescr"
										]
									]
								],
								[
									"content" =>
									[
										[
											"kind" => "components.labels.label", 
											"classEx" => "short_code", 
											"label" => ""
										],
										[
											"kind" => "components.inputs.input-text", 
											"attributeEx" => "readonly=true name=tshort_code[] type=hidden", 
											"classEx" => "tshort_code"
										],
									]
								],
								[
									"content" =>
									[
										[	
											"kind" => "components.labels.label", 
											"classEx" => "long_code", 
											"label" => ""
										],
										[
											"kind" => "components.inputs.input-text", 
											"attributeEx" => "readonly=true name=tlong_code[] type=hidden", 
											"classEx" => "tlong_code"
										],
									]
								],
								[
									"content" => 
									[
										[
											"kind" => "components.labels.label", 
											"classEx" => "comm", 
											"label" => ""
										],
										[
											"kind" => "components.inputs.input-text", 
											"attributeEx" => "readonly=true name=tcommentaries[] type=hidden", 
											"classEx" => "tcommentaries"
										],
									]
								]
							]
						];
						if(isset($new_request))
						{
							if(isset($new_request) && ($request->status == 9 || $request->status == 19))
							{
								array_push($modelBody[0], [
									"content" => 
									[
										[
											"kind" => "components.labels.label",
											"label" => "---"
										]
									]
								]);
							}
						}
						array_push($modelBody[0], [
							"content" => 
							[
								[
									"kind" => "components.buttons.button",
									"attributeEx" => "type=button",
									"classEx" => "delete-item", 
									"label" => "<span class=icon-x></span>",
									"variant" => "red",
								]
							]
						]);

						$table_body = view("components.tables.table", [
							"modelHead" => $modelHead,
							"modelBody" => $modelBody, 
							"noHead"	=> "true"							
						])->render();
						$table_body 	= html_entity_decode(preg_replace("/(\r)*(\n)*/", "", $table_body));
					@endphp

					table_body = '{!!preg_replace("/(\r)*(\n)*/", "", $table_body)!!}';
					row = $(table_body);
					
					row.find('.countConcept').text(countConcept);
					row.find('.category_name').text(category_name);
					row.find('.tcategory').val(category_id);
					row.find('.tidStatDetail').val("x");
					row.find('.cant').text(Number(cant));
					row.find('.tquanty').val(Number(cant));
					row.find('.tdescr').val(material);
					row.find('.material').text(material);
					row.find('.tshort_code').val(short_code);
					row.find('.short_code').text(short_code != "" ? short_code : "---");
					row.find('.tlong_code').val(long_code);
					row.find('.long_code').text(long_code != "" ? long_code : "---");
					row.find('.tcommentaries').val(comm);
					row.find('.comm').text(comm);
					$('#body').append(row);
					$(".js-category").val(null).trigger("change");
					$('input[name="quantity"]').val("");
                    $('input[name="short_code"]').val("");
                    $('input[name="long_code"]').val("");
                    $('input[name="material"]').val("");
                    $('textarea[id="commentaries"]').val("");
					$(".js-category").removeClass('valid').removeClass('error');
					$('[name="material"]').removeClass('valid').removeClass('error');
					$('[name="quantity"]').removeClass('valid').removeClass('error');
				}
			})
			.on('click','.delete-item',function()
			{
				idStatDetail = $(this).parent('div').parent('div').parent('div').parent('div').find('.tidStatDetail').val();

				del = $('<input type="hidden" name="delete[]" value="'+idStatDetail+'">');
				$('#delete_arts').append(del);

				$(this).parent('div').parent('div').parent('div').remove();
				if($('.countConcept').length>0)
				{
					$('.countConcept').each(function(i,v)
					{
						$(this).html(i+1);
					});
				}
			})
			.on("click",".btn-delete-form",function(e)
			{
				e.preventDefault();
				form = $(this).parents("form");
				swal({
					title		: "Limpiar formulario",
					text		: "¿Confirma que desea limpiar el formulario?",
					icon		: "warning",
					buttons		: true,
					dangerMode	: true,
				})
				.then((willClean) =>
				{
					if(willClean)
					{
						form[0].reset();
						$("#body").html("");
						$(".removeselect").val(null).trigger("change");
					}
					else
					{
						swal.close();
					}
				});
			})
			.on('click','#help-btn-articles',function()
			{
				swal('Ayuda','Aqui deberá especificar el nombre y la cantidad del artículo que solicita. Le pedimos especificar en "Comentarios" si desea aceptar algún artículo similar al que solicita','info');
			})
			$.validate(
			{
				form: '#container-alta',
				onError   : function($form)
				{
					swal('', '{{ Lang::get("messages.form_error") }}', 'error');
				},
				onSuccess : function($form)
				{
					if($('.request-validate').length>0)
					{
						conceptos	= $('#body .tr').length;
						if(conceptos>0)
						{
							swal('Cargando',{
								icon	: '{{ asset(getenv('LOADING_IMG')) }}',
								button	: false,
							});
							return true;
						}
						else
						{
							$('#body .tr').addClass('error');
							swal('', 'Debe agregar al menos un producto', 'error');
							return false;
						}
					}
					else
					{
						swal('Cargando',{
							icon: '{{ asset(getenv('LOADING_IMG')) }}',
							button: false,
						});
						return true;
					}
				}
			});
		});
	</script>
@endsection
