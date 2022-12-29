@extends("layouts.child_module") 
@section("data")
	<div class="sm:text-center text-left my-5">
		A continuación podrá verificar la información de la solicitud antes de continuar con el proceso:
	</div>
	@php
		$modelTable =
		[
			["Folio", $request->new_folio != null ? $request->new_folio : $request->folio],
			["Título y fecha", htmlentities($request->stationery->first()->title). " - " .Carbon\Carbon::createFromFormat('Y-m-d',$request->stationery->first()->datetitle)->format('d-m-Y')],
			["Solicitante", $request->requestUser->name." ".$request->requestUser->last_name." ".$request->requestUser->scnd_last_name],
			["Elaborado", $request->elaborateUser->name." ".$request->elaborateUser->last_name." ".$request->elaborateUser->scnd_last_name],
			["Empresa", $request->requestEnterprise->name],
			["Dirección", $request->requestDirection->name],
			["Departamento", $request->requestDepartment->name],
			["Proyecto", $request->requestProject()->exists() ? $request->requestProject->proyectName : ""],
			["Clasificación del Gasto", $request->accounts->account. " - " .$request->accounts->description],
		];
		if(isset($request) && $request->stationery()->first())
		{
			$value=$request->stationery()->first()->subcontractorProvider;
			if(strlen($value) > 0)
			{
				$modelTable[]=["Subcontratista/Proveedor", $value ];
			}
			else
			{
				$modelTable[]=["Subcontratista/Proveedor", "No aplica" ];
			}						
		}				
	@endphp	
	@component("components.templates.outputs.table-detail", ["modelTable" => $modelTable, "title" => "Detalles de la Solicitud"])@endcomponent
	<div class="container-lg">
		@component("components.labels.title-divisor") DETALLES DEL ARTÍCULO @endcomponent
		@php
			$body		=	[];
			$modelBody	=	[];
			$modelHead	=	[
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
								"label" => $detail->categoryData()->exists() ? $detail->categoryData->description : "",
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
					[
						"content" =>
						[
							[
								"label" => isset($detail->short_code) ? htmlentities($detail->short_code) : " --- ",
							]
						]
					],
					[
						"content" =>
						[
							[
								"label" => isset($detail->long_code) ? htmlentities($detail->long_code) : " --- ",
							]
						]
					],
					[
						"content" =>
						[
							[
								"label" => isset($detail->commentaries) ? htmlentities($detail->commentaries) : "Sin comentarios",
							]
						]
					],
				];
				$countConcept++;
				$modelBody[] = $body; 
			}
		@endphp
		@component("components.tables.table",[
				"modelHead" => $modelHead,
				"modelBody" => $modelBody,
			])
				@slot("attributeExBody")
					id="body"
				@endslot
		@endcomponent
	</div>
	@component("components.forms.form",["methodEx" => "PUT", "attributeEx" => "method=\"POST\" action=\"".route("stationery.review.update",$request->folio)."\" id=\"container-alta\""])
		<div class="justify-center p-8">
			@component("components.containers.container-approval")
				@slot('attributeExLabel')
					id="label-inline"
				@endslot
				@slot("attributeExButton")
					name="status"
					value="4"
					id="aprobar"
				@endslot
				@slot("attributeExButtonTwo")
					name="status"
					value="6"
					id="rechazar"
				@endslot
			@endcomponent
		</div>
		<div id="aceptar" class="hidden">
			@component("components.containers.container-form")
				<div class="col-span-2">
					@component("components.labels.label") Empresa @endcomponent
					@php
						$options = collect();
						foreach (App\Enterprise::orderName()->where("status","ACTIVE")->whereIn("id",Auth::user()->inChargeEnt($option_id)->pluck("enterprise_id"))->get() as $enterprise)
						{
							$description = 	strlen($enterprise->name) >= 35 ? substr(strip_tags($enterprise->name),0,35)."..." : $enterprise->name;
							if($request->idEnterprise == $enterprise->id)
							{
								$options = $options->concat([["value"=>$enterprise->id, "selected" => "selected", "description"=>$description]]);
							}
							else
							{
								$options = $options->concat([["value"=>$enterprise->id, "description"=>$description]]);
							}
						}
						$attributeEx = "id=\"multiple-enterprisesR\" name=\"idEnterpriseR\" multiple=\"multiple\" data-validation=\"required\"";
						$classEx = "js-enterprisesR";
					@endphp
					@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])@endcomponent
				</div>
				<div class="col-span-2">
					@component("components.labels.label") Dirección @endcomponent
					@php
						$options = collect();
						foreach (App\Area::orderName()->where("status","ACTIVE")->get() as $area)
						{
							$description = $area->name;
							if($request->idArea == $area->id)
							{
								$options = $options->concat([["value"=>$area->id, "selected"=>"selected", "description"=>$description]]);
							}
							else
							{
								$options = $options->concat([["value"=>$area->id, "description"=>$description]]);
							}
						}
						$attributeEx = "id=\"multiple-areasR\" multiple=\"multiple\" name=\"idAreaR\" data-validation=\"required\"";
						$classEx = "js-areasR";	
					@endphp
					@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])@endcomponent
				</div>
				<div class="col-span-2">
					@component("components.labels.label") Proyecto/Contrato @endcomponent
					@php
						$options = collect();
						if(isset($request) && $request->idProject)
						{
							$project = App\Project::find($request->idProject);
							$options = $options->concat([["value"=>$project->idproyect, "selected"=>"selected","description"=>$project->proyectName]]);
						}
						$attributeEx = "name=\"project_id\" multiple=\"multiple\" id=\"multiple-projects\" data-validation=\"required\"";
						$classEx = "js-projects removeselect";
					@endphp
					@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])@endcomponent
				</div>
				<div class="col-span-2">
					@component("components.labels.label") Departamento @endcomponent
					@php
						$options = collect();
						foreach (App\Department::orderName()->where("status","ACTIVE")->whereIn("id",Auth::user()->inChargeDep($option_id)->pluck("departament_id"))->get() as $department)
						{
							$description = $department->name;
							if($request->idDepartment == $department->id)
							{
								$options = $options->concat([["value"=>$department->id, "selected"=>"selected", "description"=>$description]]);
							}
							else
							{
								$options = $options->concat([["value"=>$department->id, "description"=>$description]]);
							}
						}
						$attributeEx = "id=\"multiple-departmentsR\" multiple=\"multiple\" name=\"idDepartmentR\" data-validation=\"required\"";
						$classEx = "js-departmentsR";
					@endphp
					@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])@endcomponent
				</div>
				<div class="col-span-2">
					@component("components.labels.label") Clasificación del Gasto @endcomponent
					@php
						$options = collect();
						
							$description = $request->accounts->account. " - " .$request->accounts->description . " (".$request->accounts->content.")";

							$options = $options->concat([["value"=>$request->accounts->idAccAcc, "id"=>"current_account_id", "selected"=>"selected", "description"=>$description]]);
					
						$attributeEx = "id=\"multiple-accountsR\" multiple=\"multiple\" name=\"accountR\" data-validation=\"required\"";
						$classEx = "js-accountsR removeselect";
					@endphp
					@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])@endcomponent
				</div>
			@endcomponent
					
			@component("components.labels.title-divisor")Asignación de Etiquetas<span class="help-btn" id="help-btn-add-label"> @endcomponent
			@php
				$heads = ["","Cantidad","Concepto"];
				$modelBody =[];

				foreach ($request->stationery as $stat) 
				{
					foreach (App\DetailStationery::where("idStat",$stat->idStationery)->get() as $key=>$detail) 
					{
						$body =
						[
							"classEx" => "tr",
							[
								"content"=>
								[
									[
									"classExContainer" 	=> "inline-flex",
									"kind"          	=> "components.inputs.checkbox",
									"classEx"			=> "add-article d-none hidden",
									"label"				=> "<span class=\"icon-check\"></span>",
									"attributeEx"		=> "id=\"id_article_".$detail->idStatDetail."\" name=\"add-article_".$detail->idStatDetail."\" value=\"1\""	
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
								"content"=>
								[
									[
										"label" => htmlentities($detail->product),
									],
									[
										"kind" 			=> "components.inputs.input-text",
										"classEx" 		=> "idStatDetailOld",
										"attributeEx" 	=> "type=\"hidden\" value=\"".$detail->idStatDetail."\"",
									],
									[
										"kind" 			=> "components.inputs.input-text",
										"classEx" 		=> "quantityOld",
										"attributeEx" 	=> "type=\"hidden\" value=\"".$detail->quantity."\"",
									],
									[
										"kind" 			=> "components.inputs.input-text",
										"classEx" 		=> "conceptOld",
										"attributeEx" 	=> "type=\"hidden\" value=\"".htmlentities($detail->product)."\"",
									]
								],
							],
						];
						$modelBody[] = $body;
					}					
				}
				$body=
				[
					"classEx" => "tr",
					[
						"content"=>
						[
							[
								"label" => "",
							]
						]
					],
					[
						"content" =>
						[
							[
								"label" => "Etiquetas",
							],
						]
					],
					[
						"content" =>
						[
							[
								"kind"			=>	"components.inputs.select",
								"attributeEx"	=>	"multiple=\"multiple\" name=\"idLabelsReview[]\"",
								"classEx"		=>	"js-labelsR",
							]
						]
					]
				];			
				$modelBody[] = $body;
			@endphp
			@component("components.tables.alwaysVisibleTable",[
					"modelHead" => $heads,
					"modelBody" => $modelBody,
				])
				@slot("attributeExBody")
					id="tbody-concepts"
				@endslot
			@endcomponent
			
			@component("components.containers.container-form")
				@slot("classEx")
					hidden
					view-label
				@endslot
				@slot("attributeEx")
					id="container-data"
				@endslot
				<div class="col-span-2">
					@component("components.labels.label") Producto @endcomponent
					@component("components.labels.label") 
						@slot("classEx")
							conceptNew
						@endslot
					@endcomponent
					@component("components.inputs.input-text")
						@slot("classEx")
							idStatDetailNew
						@endslot
						@slot("attributeEx")
							type="hidden"
						@endslot
					@endcomponent
					@component("components.inputs.input-text")
						@slot("classEx")
							quantityNew
						@endslot
						@slot("attributeEx")
							type="hidden"
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component("components.labels.label") Etiquetas @endcomponent
					@php
						$options = collect();
						foreach (App\Label::orderName()->get() as $label) 
						{
							$description = $label->description;
							$options = $options->concat([["value"=>$label->idlabels, "description"=>$description]]);
						}
						$attributeEx = "multiple=\"multiple\" name=\"idLabelsReview[]\"";
						$classEx = "js-labelsR labelsNew";
					@endphp
					@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])@endcomponent
				</div>
				<div class="col-span-2 md:col-span-4 grid justify-items-center md:justify-items-start">
					@component("components.buttons.button",["variant" => "primary"])
						@slot("classEx")
						approve-label
						@endslot
						@slot("attributeEx")
						type="button"
						@endslot
						AGREGAR
					@endcomponent
				</div>
			@endcomponent
			<div class="text-center">
				@component("components.buttons.button", ["variant" => "warning"])
					@slot("classEx")
						add-label
					@endslot
					@slot("attributeEx")
						type="button"
					@endslot
					<span class="icon-plus"></span>
					<span>Agregar</span>
				@endcomponent
			</div>
			<div class="my-6">
				@component("components.labels.title-divisor") Etiquetas Asignadas @endcomponent
				
				@php
					$heads = ["#","Concepto", "Etiquetas",""];
				@endphp
					@component("components.tables.alwaysVisibleTable",[
						"modelHead" => $heads,
						"themeBody" => "striped"
					])
					@slot("attributeExBody")
						id="tbody-conceptsNew"
					@endslot
					@endcomponent
			</div>
			<span id="labelsAssign">				
			</span>
			<div class="flex-wrap px-3 w-full grid md:grid-cols-1 grid-cols-1 gap-x-10">
				<div class="w-full col-span-1 mb-4">
					@component("components.labels.label") 
							Comentarios (Opcional)
					@endcomponent
					@component("components.inputs.text-area")
						@slot("attributeEx")
							cols="90"
							rows="10"
							id="checkCommentA"
							name="checkCommentA"
						@endslot
					@endcomponent
				</div>
			</div>
		</div>
		<div id="rechaza" class="hidden">
			<div class="flex-wrap px-3 w-full grid md:grid-cols-1 grid-cols-1 gap-x-10">
				<div class="w-full col-span-1 mb-4">
					@component("components.labels.label") Comentarios @endcomponent
					@component("components.inputs.text-area")
						@slot("attributeEx")
							cols="90"
							rows="10"
							id="checkCommentR"
							name="checkCommentR"
						@endslot
					@endcomponent
				</div>
			</div>
		</div>
		<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mt-4 mb-6">
			@component("components.buttons.button",["variant" => "primary"])
				@slot("attributeEx")
				type="submit" 
				name="enviar"
				@endslot
				ENVIAR SOLICITUD
			@endcomponent
			@component('components.buttons.button', [ "buttonElement" => "a", "variant" => "reset"])
				@slot("attributeEx")
					@if(isset($option_id)) 
						href="{{ url(getUrlRedirect($option_id)) }}" 
					@else 
						href="{{ url(getUrlRedirect($child_id)) }}" 
					@endif 
				@endslot
				@slot('classEx')
					load-actioner
				@endslot
				REGRESAR 
			@endcomponent
		</div>
	@endcomponent
@endsection
@section("scripts")
	<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
	<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
	<script src="{{ asset('js/jquery.numeric.js') }}"></script>
	<script src="{{ asset('js/datepicker.js') }}"></script>
	<script>
		$(document).ready(function() 
		{
			$enterprise = $('select[name="idEnterpriseR"] option:selected').val();
			if($enterprise)
			{
				search_accounts($enterprise,true);
			}
			@php
				$selects = collect([
					[
						"identificator"				=> ".js-category",
						"placeholder"				=> "Seleccione la categoría",
						"language"					=> "es",
						"maximumSelectionLength"	=> "1",
					],
					[
						"identificator"				=> ".js-projects",
						"placeholder"				=> "Seleccione el proyecto/contrato",
						"language"					=> "es",
						"maximumSelectionLength"	=> "1",
					],
					[
						"identificator"				=> ".js-users",
						"placeholder"				=> "Seleccione el solicitante",
						"language"					=> "es",
						"maximumSelectionLength"	=> "1",
					],
					[
						"identificator"				=> ".js-enterprises",
						"placeholder"				=> "Seleccione la empresa",
						"language"					=> "es",
						"maximumSelectionLength"	=> "1",
					],
					[
						"identificator"				=> ".js-areas",
						"placeholder"				=> "Seleccione la dirección",
						"language"					=> "es",
						"maximumSelectionLength"	=> "1",
					],
					[
						"identificator"				=> ".js-accounts",
						"placeholder"				=> "Seleccione la clasificación del gasto",
						"language"					=> "es",
						"maximumSelectionLength"	=> "1",
					],
					[
						"identificator"				=> ".js-departments",
						"placeholder"				=> "Seleccione el departamento",
						"language"					=> "es",
						"maximumSelectionLength"	=> "1",
					],
				]);
			@endphp
			@component("components.scripts.selects",["selects" => $selects]) @endcomponent	
			$('.card_number,.destination_account,.destination_key,.employee_number').numeric(false);    // números
			$('.amount,.importe',).numeric({ altDecimal: ".", decimalPlaces: 2 });
			count = 0;
			$.validate(
			{
				form: '#container-alta',
				onSuccess : function($form)
				{
					if($('input[name="status"]').is(':checked'))
					{
						if($('input#aprobar').is(':checked'))
						{
							enterprise	= $('#multiple-enterprisesR').val();
							area		= $('#multiple-areasR').val();
							department	= $('#multiple-departmentsR').val();
							account		= $('#multiple-accountsR').val();
							if(enterprise == '' || area == '' || department == '' || account == '')
							{
								swal('', 'Todos los campos son requeridos', 'error');
								return false;
							}
							else
							{
								if (($('#tbody-conceptsNew .tr').length+1) != $('#tbody-concepts .tr').length) 
								{
									swal('', 'Tiene conceptos sin asignar etiquetas', 'error');
									return false;
								}
								else
								{
									swal('Cargando',{
										icon : '{{ asset(getenv('LOADING_IMG')) }}',
										button: false,
									});
									return true;
								}
							}
						}
						else
						{
							swal('Cargando',{
								icon : '{{ asset(getenv('LOADING_IMG')) }}',
								button: false,
							});
							return true;
						}
					}
					else
					{
						swal('', 'Debe seleccionar al menos un estado', 'error');
						return false;
					}
				}
			});
			$(document).on('change','input[name="status"]',function()
			{
				if ($('input[name="status"]:checked').val() == "4") 
				{
					$("#rechaza").slideUp("slow");
					$("#aceptar").slideToggle("slow").addClass('form-container').css('display','block');
					generalSelect({'selector': '.js-projects', 'model': 21});
					@php
						$selects = collect([
							[
								"identificator"			=> ".js-enterprisesR",
								"placeholder"			=> "Seleccione la empresa",
								"language"				=> "es",
								"maximumSelectionLength"=> "1",
							],
							[
								"identificator"			=> ".js-areasR",
								"placeholder"			=> "Seleccione la dirección",
								"language"				=> "es",
								"maximumSelectionLength"=> "1",
							],
							[
								"identificator"			=> ".js-accountsR",
								"placeholder"			=> "Seleccione la clasificación del gasto",
								"language"				=> "es",
								"maximumSelectionLength"=> "1",
							],
							[
								"identificator"			=> ".js-departmentsR",
								"placeholder"			=> "Seleccione el departamento",
								"language"				=> "es",
								"maximumSelectionLength"=> "1",
							],
							[
								"identificator"			=> ".js-projectsR",
								"placeholder"			=> "Seleccione el proyecto",
								"language"				=> "es",
								"maximumSelectionLength"=> "1",
							],
						]);
					@endphp
					@component("components.scripts.selects",["selects" => $selects]) @endcomponent
					generalSelect({'selector': '.js-labelsR', 'model': 19, 'maxSelection' : -1});	
				}
				else if ($('input[name="status"]:checked').val() == "6") 
				{
					$("#aceptar").slideUp("slow");
					$("#rechaza").slideToggle("slow").addClass('form-container').css('display','block');
				}
			})
			.on('click','.add-label',function(){
				errorSwalElements=true;
				$('.add-article').each(function(){
					if($(this).is(':checked')) {
						errorSwalElements=false;
						$(this).prop( "checked",false); 
						$(this).parent('div').parent('div').parent('div').parent('div').hide();
						countConcept	= $('.countConcept').length;
						concept 		= $(this).parent().parent().parent().parent().find('.conceptOld').val();
						quantity  		= $(this).parent().parent().parent().parent().find('.quantityOld').val();
						idStatDetail 	= $(this).parent().parent().parent().parent().find('.idStatDetailOld').val().trim();

						countConcept = countConcept+1;
						@php
							$modelHead = ['#','Concepto', 'Etiquetas',''];
							$modelBody =
							[
								[
									"classEx" => "tr",
									[
										"content"=>
										[
											[
												"kind" => "components.labels.label", 
												"classEx" => "countConcept", 
												"label" => ""
											]
										]
									],
									[
										"content" =>
										[
											[
												"kind"			=> 	"components.labels.label",
												"classEx"		=>	"concept",
												"label" 		=> 	""
											]
										]
									],
									[
										"classEx" => "td_etiquetas",
										"content" =>
										[
											[
												"kind"			=>	"components.inputs.input-text",
												"attributeEx"	=>	"type=\"hidden\"",
												"classEx"		=>	"concept"
											],
											[
												"kind"			=>	"components.inputs.input-text",
												"attributeEx" 	=>	"type=\"hidden\" name=\"t_idStatDetail[]\"",
												"classEx"		=>	"idStatDetail"
											],
											[
												"kind"			=>	"components.inputs.input-text",
												"attributeEx"	=>	"type=\"hidden\"",
												"classEx" 		=> 	"quantity"
											],
											[
												"kind"			=>	"components.labels.label",
												"classEx"		=>	"labelsAssign",
											]
										]
									],
									[
										"content" =>
										[
											[
												"kind" => "components.buttons.button",
												"classEx" => "delete-item",
												"label" => "<span class=\"icon-x\"></span>",
												"variant" => "red"
											]
										]
									],
								]
							];
							$table_body = view("components.tables.alwaysVisibleTable",[
								"modelHead" => $modelHead,
								"modelBody" => $modelBody,
								"noHead" => true
							])->render();
							$table_body 	= html_entity_decode(preg_replace("/(\r)*(\n)*/", "", $table_body));
						@endphp
						
						table_body = '{!!preg_replace("/(\r)*(\n)*/", "", $table_body)!!}';
						row = $(table_body);
						row = rowColor('#tbody-conceptsNew', row);
						row.find('.countConcept').text(countConcept);
						row.find('.concept').text(concept);
						row.find('.concept').val(concept);
						row.find('.quantity').val(quantity);
						row.find('.idStatDetail').val(idStatDetail);
						
						$('#tbody-conceptsNew').append(row);
						$('select[name="idLabelsReview[]"] option:selected').each(function(){
							id = $(this).val();
							name = $(this).text();
							@php
								$input = view('components.inputs.input-text',[
									"classEx"=>"idLabelsAssign",
									"attributeEx"=>"type=hidden"
								])->render();
								$input 	= html_entity_decode(preg_replace("/(\r)*(\n)*/", "", $input));
							@endphp
							input = '{!!preg_replace("/(\r)*(\n)*/", "", $input)!!}';
							row_input = $(input);
							row_input.attr('name', 'idLabelsAssign'+count+'[]').val(id);
							row.find('.labelsAssign').append(name+','+'<br>').append(row_input);
						});
						count++;
					}						
				});
				$('.js-labelsR').val(null).trigger('change');

				if(errorSwalElements){
					swal('', 'Seleccione los elementos que les quiera agregar esta(s) etiqueta(s)', 'error');
				}
			})
			.on('click','.approve-label',function(){
				countConcept	= $('.countConcept').length;
				concept 		= $('.conceptNew').text();
				quantity 		= $('.quantityNew').val();
				idStatDetail 	= $('.idStatDetailNew').val();
				@php
					$modelHead = ['#','Concepto', 'Etiquetas',''];
					$modelBody =
					[
						[
							"classEx" => "tr",
							[
								"content"=>
								[
									[
										"kind" => "components.labels.label", 
										"classEx" => "countConcept", 
										"label" => ""
									]
								]
							],
							[
								"content" =>
								[
									[
										"kind"			=> 	"components.labels.label",
										"classEx"		=>	"concept",
										"label" 		=> 	""
									]
								]
							],
							[
								"classEx" => "td_etiquetas",
								"content" =>
								[
									[
										"kind"			=>	"components.inputs.input-text",
										"attributeEx"	=>	"type=\"hidden\"",
										"classEx"		=>	"concept"
									],
									[
										"kind"			=>	"components.inputs.input-text",
										"attributeEx" 	=>	"type=\"hidden\" name=\"t_idStatDetail[]\"",
										"classEx"		=>	"quantity"
									],
									[
										"kind"			=>	"components.inputs.input-text",
										"attributeEx"	=>	"type=\"hidden\"",
										"classEx" 		=> 	"idStatDetail"
									],
									[
										"kind"			=>	"components.labels.label",
										"classEx"		=>	"labelsAssign",
									]
								]
							],
							[
								"content" =>
								[
									[
										"kind" => "components.buttons.button",
										"classEx" => "delete-item",
										"label" => "<span class=\"icon-x\"></span>",
										"variant" => "red"
									]
								]
							],
						]
					];
					$table_body = view("components.tables.alwaysVisibleTable",[
						"modelHead" => $modelHead,
						"modelBody" => $modelBody,
						"noHead" => true
					])->render();					
				@endphp
			})
			.on('click','.delete-item',function(){
				idStatDetail	= $(this).parent().parent().siblings('.td_etiquetas').find('.idStatDetail').val();
				$('.idStatDetailOld').each(function(){
					if($(this).val().trim() == idStatDetail){
						$(this).parents('.tr').show();
					}
				});
				$(this).parents('.tr').remove();
				$('#tbody-conceptsNew .tr').each(function(i,v){
					$(this).find('.idLabelsAssign').attr('name','idLabelsAssign'+i+'[]');
					$(this).find('.labelsAssign').attr('id','labelsAssign'+i+'[]');
				});
				count = $('#tbody-conceptsNew .tr').length;
			})
			.on('click','#help-btn-add-label',function()
			{
				swal('Ayuda','Debe agregar una o más etiquetas a cada artículo solicitado.','info');
			})
			.on('change','.js-enterprisesR',function(){
				$enterprise = $(this).val();
				search_accounts($enterprise)
			})
		});
		function search_accounts($enterprise,first)
		{
			idAccAcc = Number($('#current_account_id').val());
			if(!first)
			{
				$('.js-accountsR').empty();
			}
			generalSelect({'selector': '.js-accountsR', 'depends': '.js-enterprisesR', 'model': 5});
		}
	</script>
@endsection
