@extends('layouts.child_module')
@section('data')
	<div class="sm:text-center text-left my-5">
		A continuación podrá verificar la información de la solicitud antes de continuar con el proceso:
	</div>
	<div class="pb-6">
		@php
			$requestUser = App\User::find($request->idRequest);
			$elaborateUser = App\User::find($request->idElaborate);
			$modelTable =
			[
				["Folio:", $request->folio],
				["Título y fecha:", htmlentities($request->resource->first()->title). " - " .Carbon\Carbon::createFromFormat('Y-m-d',$request->resource->first()->datetitle)->format('d-m-Y')],
				["Solicitante:", $requestUser->name. " " .$requestUser->last_name. " " .$requestUser->scnd_last_name],
				["Elaborado por:", $elaborateUser->name. " " .$elaborateUser->last_name. " " .$elaborateUser->scnd_last_name],
				["Empresa:", App\Enterprise::find($request->idEnterprise)->name],
				["Dirección:", App\Area::find($request->idArea)->name],
				["Departamento:", App\Department::find($request->idDepartment)->name],
				["Proyecto:", isset(App\Project::find($request->idProject)->proyectName) ? App\Project::find($request->idProject)->proyectName : 'No se selccionó proyecto'],
			];
		@endphp
		@component("components.templates.outputs.table-detail", ["modelTable" => $modelTable, "title" => "Detalles de la Solicitud"])@endcomponent	
	</div>
	<div class="mt-6">
		@component("components.labels.title-divisor")
			DATOS DEL SOLICITANTE
		@endcomponent
		@php
			foreach($request->resource as $resource)
			{
				$modelTable = ["Forma de pago" => $resource->paymentMethod->method,
								"Referencia" => (isset($resource->reference) ? htmlentities($resource->reference) : "---"),
								"Tipo de moneda" => $resource->currency,
								"Importe" => "$".number_format($resource->total,2)];
			}
			foreach($request->resource as $resource)
			{
				foreach(App\Employee::join('banks','employees.idBanks','banks.idBanks')->where('employees.idUsers',$resource->idUsers)->get() as $bank)
				{
					if($resource->idEmployee == $bank->idEmployee)
					{
						$modelTable = ["Banco" => $bank->description,
											"Alias" => $bank->alias!=null ? $bank->alias : '---',
											"Número de tarjeta" => $bank->cardNumber!=null ? $bank->cardNumber : '---',
											"CLABE" => $bank->clabe!=null ? $bank->clabe : '---',
											"Número de cuenta" => $bank->account!=null ? $bank->account : '---'];
					}
				}
			}
		@endphp
		@component("components.templates.outputs.table-detail-single",["modelTable" => $modelTable])@endcomponent
	</div>
	<div class="mt-10">
		@component("components.labels.title-divisor")
			RELACIÓN DE DOCUMENTOS SOLICITADOS
		@endcomponent
		@php
			$body = [];
			$modelBody = [];
			$modelHead = 
			[
				[
					["value"=>"#"],
					["value"=>"Concepto"],
					["value"=>"Clasificación de gasto"],
					["value"=>"Importe"]
				]
			];

			foreach($request->resource as $resource)
			{
				$subtotalFinal = $ivaFinal = $totalFinal = 0;
				$countConcept = 1;

				foreach($resource->resourceDetail as $resourceDetail)
				{
					$totalFinal		+= $resourceDetail->amount;

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
									"label" => htmlentities($resourceDetail->concept),
								]
							]
						],
						[
							"content" =>
							[
								[
									"label" => $resourceDetail->accounts->account." - ".$resourceDetail->accounts->description." (".$resourceDetail->accounts->content.")",
								]
							]
						],
						[
							"content" =>
							[
								[
									"label" => "$ ".number_format($resourceDetail->amount,2)
								]
							]
						]
					];
					$countConcept++;
					$modelBody[] = $body;
				}
			}				
		@endphp
		@component("components.tables.table",[
			"modelHead"	=> $modelHead,
			"modelBody"	=> $modelBody,
			])
			@slot("classEx")	
				text-center
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
		@php
			$total = 0;
			if ($totalFinal!=0)
			{
				$total = number_format($totalFinal,2);
			}
			$modelTable = 
			[
				[
					"label" => "TOTAL:", "inputsEx" => 
					[
						[
							"kind" => "components.labels.label",
							"label" => "$ ".$total,
							"classEx" => "total"
						],
						[
							"kind" => "components.inputs.input-text",
							"classEx" => "total",
							"attributeEx" => "id=\"total\" type=\"hidden\" readonly=\"readonly\" name=\"total\" placeholder=\"$0.00\" value=\"".$total."\""
						]
					]
				]
			];
		@endphp
		@component('components.templates.outputs.form-details',[
			"modelTable" 		 => $modelTable,
			
		])
		@endcomponent
	</div>
	@if($request->resource->first()->documents()->exists())
		<div class="mt-10">
			@component("components.labels.title-divisor")
				DOCUMENTOS CARGADOS
			@endcomponent
			@php
				$body = [];
				$modelBody = [];
				$heads = ["Nombre","Archivo","Modificado por"];

				foreach($request->resource->first()->documents->sortByDesc('created_at') as $doc)
				{
					$body =
					[
						"classEx" => "tr",
						[
							"content" =>
							[
								[
									"label" => $doc->name,
								],
								[
									"kind" => "components.inputs.input-text",
									"attributeEx" => "type=\"hidden\" name=\"document-id[]\" value=\"".$doc->id."\"",
								]
							]
						],
						[
							"content" =>
							[
								[
									"kind" 			=> "components.buttons.button",
									"attributeEx" 	=> "target=\"_blank\" type=\"button\" title=\"".$doc->path."\"".' '."href=\"".asset('docs/resource/'.$doc->path)."\"",
									"buttonElement" => "a",
									"label"			=> "Archivo",
									"variant"		=> "secondary",
								]
							]
						],
						[
							"content" =>
							[
								[
									"label" => $doc->user->fullName(),
								]
							]
						]
					];
					if(isset($request) && $request->status == 2)
					{
						$body[] =
						[
							"content" =>
							[
								"kind" => "components.inputs.input-text",
								"attributeEx" => "type=\"hidden\" name=\"to_delete\"",
							]
						];
					}
					$modelBody[] = $body;
				}
			@endphp
			@component("components.tables.alwaysVisibleTable",[
				"modelHead"	=> $heads,
				"modelBody"	=> $modelBody,
			])
				@slot("classEx")	
					text-center
				@endslot
			@endcomponent
		</div>
	@endif
	@component("components.forms.form",["methodEx" => "PUT", "attributeEx" => "method=\"POST\" action=\"".route('resource.review.update',$request->folio)."\" id=\"container-alta\"","files" => true])
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
					@component("components.labels.label")
						Empresa:
					@endcomponent
					@php
						$options = collect();
						foreach(App\Enterprise::where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeEnt($option_id)->pluck('enterprise_id'))->orderBy('name','asc')->get() as $enterprise)
						{
							$description = strlen($enterprise->name) >= 35 ? substr(strip_tags($enterprise->name),0,35).'...' : $enterprise->name;
							if($request->idEnterprise == $enterprise->id)
							{
								$options = $options->concat([["value"=>$enterprise->id, "selected" => "selected", "description"=>$description]]);
							}
							else
							{
								$options = $options->concat([["value"=>$enterprise->id,"description"=>$description]]);
							}
						}
						$attributeEx = "id=\"multiple-enterprisesR\" name=\"idEnterpriseR\" multiple=\"multiple\" data-validation=\"required\"";
						$classEx = "js-enterprisesR";
					@endphp
					@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])@endcomponent
				</div>
				<div class="col-span-2">
					@component("components.labels.label")
						Dirección:
					@endcomponent
					@php
						$options = collect();
						foreach(App\Area::where('status','ACTIVE')->orderBy('name','asc')->get() as $area)
						{
							$description = $area->name;
							if($request->idArea == $area->id)
							{
								$options = $options->concat([["value"=>$area->id, "selected" => "selected", "description"=>$description]]);
							}
							else
							{
								$options = $options->concat([["value"=>$area->id,"description"=>$description]]);
							}
						}
						$attributeEx = "id=\"multiple-areasR\" name=\"idAreaR\" multiple=\"multiple\" data-validation=\"required\"";
						$classEx = "js-areasR";
					@endphp
					@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])@endcomponent
				</div>
				<div class="col-span-2">
					@component("components.labels.label")
						Departamento:
					@endcomponent
					@php
						$options = collect();
						foreach(App\Department::where('status','ACTIVE')->orderBy('name','asc')->whereIn('id',Auth::user()->inChargeDep($option_id)->pluck('departament_id'))->get() as $department)
						{
							$description = $department->name;
							if($request->idDepartment == $department->id)
							{
								$options = $options->concat([["value"=>$department->id, "selected" => "selected", "description"=>$description]]);
							}
							else
							{
								$options = $options->concat([["value"=>$department->id,"description"=>$description]]);
							}
						}
						$attributeEx = "id=\"multiple-departmentsR\" name=\"idDepartmentR\" multiple=\"multiple\" data-validation=\"required\"";
						$classEx = "js-departmentsR";
					@endphp
					@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])@endcomponent
				</div>
				<div class="col-span-2">
					@component("components.labels.label") Proyecto: @endcomponent
					@php
						$options = collect();
						if(isset($request) && $request->idProject)
						{
							$project = App\Project::find($request->idProject);
							$options = $options->concat([["value"=>$project->idproyect, "selected"=>"selected","description"=>$project->proyectName]]);
						}
						$attributeEx = "id=\"multiple-projectsR\" name=\"project_id\" multiple=\"multiple\" data-validation=\"required\"";
						$classEx = "js-projects";
					@endphp
					@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])@endcomponent
				</div>
				<div class="col-span-2 select_father @if(!isset($request->code_wbs)) hidden @endif" >
					@component('components.labels.label') Código WBS: @endcomponent
					@php
						$options = collect();
						if(isset($request) && $request->code_wbs)
						{
							$code = App\CatCodeWBS::find($request->code_wbs);
							$options = $options->concat([["value"=>$code->id, "selected"=>"selected","description"=>$code->code_wbs]]);
						}						
						$attributeEx = "name=\"code_wbs\" multiple=\"multiple\" data-validation=\"required\"";
						$classEx = "js-code_wbs removeselect";
					@endphp
					@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])@endcomponent
				</div>
				<div class="code-edt col-span-2 @if(!isset($request->code_edt)) hidden @endif">
					<div class="col-span-2">
						@component('components.labels.label') Código EDT: @endcomponent
						@php
							$options = collect();
							if(isset($request) && $request->code_edt)
							{
								$edt = App\CatCodeEDT::find($request->code_edt);
								$options = $options->concat([["value"=>$edt->id, "selected"=>"selected", "description"=>$edt->code.' ('.$edt->description.')']]);
							}
							$attributeEx = "name=\"code_edt\" multiple=\"multiple\" data-validation=\"required\"";
							$classEx = "js-code_edt removeselect";
						@endphp
						@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])@endcomponent
					</div>
				</div>
				<div class="col-span-2">
					@component("components.labels.label") Etiquetas:	@endcomponent
					@php
						$options = collect();
						$attributeEx = "id=\"multiple-labels\" name=\"idLabels[]\" multiple=\"multiple\"";
						$classEx = "js-labelsR removeselect";
					@endphp
					@component('components.inputs.select', ['options' => $options, 'attributeEx' => $attributeEx, 'classEx' => $classEx])@endcomponent
				</div>
			@endcomponent
			<div class="mt-6">
				@component("components.labels.title-divisor")
					RELACIÓN DE DOCUMENTOS SOLICITADOS
				@endcomponent
				@php
					$heads = ["Concepto","Clasificación de gasto","Importe","Acción"];
					$modelBody = [];

					$subtotalFinal = $ivaFinal = $totalFinal = 0;

					foreach($request->resource->first()->resourceDetail as $resourceDetail)
					{
						$totalFinal		+= $resourceDetail->amount;
						$body = 
						[
							"classEx" => "tr",
							[
								"content" =>
								[
									[
										"kind" 			=> "components.inputs.input-text",
										"classEx" 		=> "idRefundDetail",
										"attributeEx" 	=> "type=\"hidden\" name=\"idRDe[]\" value=\"".$resourceDetail->idresourcedetail."\"",
									],
									[
										"label" => htmlentities($resourceDetail->concept),
									],
									[
										"kind" 			=> "components.inputs.input-text",
										"classEx" 		=> "concept",
										"attributeEx" 	=> "type=\"hidden\" name=\"t_concept[]\" value=\"".htmlentities($resourceDetail->concept)."\"",
									]
								]
							],
							[
								"content" =>
								[
									[
										"kind" 		=> "components.labels.label",
										"classEx" 	=> "account-label",
										"label" 	=> $resourceDetail->accounts->account." - ".$resourceDetail->accounts->description." (".$resourceDetail->accounts->content.")",

									],
									[
										"kind" 			=> "components.inputs.input-text",
										"classEx" 		=> "account",
										"attributeEx" 	=> "type=\"hidden\" name=\"t_account[]\" value=\"".$resourceDetail->idAccAcc."\""
									]
								]
							],
							[
								"content" =>
								[
									[
										"label" => "$ ".number_format($resourceDetail->amount,2),
									],
									[
										"kind" 			=> "components.inputs.input-text",
										"classEx" 		=> "amount",
										"attributeEx" 	=> "type=\"hidden\" name=\"t_amount[]\" value=\"".$resourceDetail->amount."\"",
									]
								]
							],
							[
								"content" =>
								[
									[
										"kind" 			=> "components.buttons.button",
										"classEx" 		=> "follow-btn approve2",
										"attributeEx" 	=> "type=\"button\" disabled=\"true\" title=\"Aprobar\"",
										"label" 		=> "<span class=\"icon-check\"></span>",
										"variant" 		=> "secondary"
									],
									[
										"kind" 			=> "components.buttons.button",
										"classEx" 		=> "follow-btn reclassify",
										"attributeEx" 	=> "type=\"button\" disabled=\"true\" title=\"Reclasificar\"",
										"label" 		=> "<span class=\"icon-update\"></span>"
									]
								]
							]
						];
						$modelBody[] = $body;
					}
				@endphp
			
				@component("components.tables.alwaysVisibleTable",[
					"modelHead" => $heads,
					"modelBody" => $modelBody,
				])
				@slot("attributeExBody")
					id="body-classify"
				@endslot
				@slot("classExBody")
					id="request-validate"
				@endslot
				@slot("classEx")
					id="table"
				@endslot
				@endcomponent
			</div>
			<div class="mt-10">
				@component("components.labels.title-divisor")
					RECLASIFICACIÓN
				@endcomponent
				@php
					$heads = ["Concepto","Clasificación de gasto","Importe","Acción"];
					$modelBody = [];

					$subtotalFinal = $ivaFinal = $totalFinal = 0;				

					$body = 
					[
						"classEx" => "tr-reclassify hidden tr",
						[
							"content" =>
							[
								[
									"kind" 			=> "components.inputs.input-text",
									"classEx" 		=> "idRefundDetail",
									"attributeEx" 	=> "type=\"hidden\"",
								],
								[
									"kind" 			=> "components.inputs.input-text",
									"classEx" 		=> "conceptR",
									"attributeEx" 	=> "type=\"hidden\"",
								],
								[
									"kind"			=> "components.labels.label",
									"classEx"		=> "conceptR",
								]
							]
						],
						[
							"content" =>
							[
								[
									"kind"			=>	"components.inputs.select",
									"attributeEx"	=>	"multiple=\"multiple\" name=\"account_idR\"",
									"classEx"		=>	"js-accountsR input-text",
								]
							]
						],
						[
							"content" =>
							[
								[
									"kind" 		=> "components.labels.label",
									"classEx" 	=> "amountR",
									"label"		=> "",
								],
								[
									"kind"			=> "components.inputs.input-text",
									"classEx"		=> "amountR",
									"attributeEx"	=> "type=\"hidden\"",
								]
							]
						],
						[
							"content" =>
							[
								[
									"kind" 			=> "components.buttons.button",
									"classEx" 		=> "follow-btn reclassify2",
									"attributeEx" 	=> "type=\"button\" title=\"Reclasificar\"",
									"label" 		=> "<span class=\"icon-update\"></span>"
								]
							]
						]
					];
					$modelBody[] = $body;
				@endphp
				@component("components.tables.alwaysVisibleTable",[
						"modelHead" => $heads,
						"modelBody" => $modelBody
					])
					@slot("attributeExBody")
						id="body-reclassify"
					@endslot
					@slot("attributeEx")
						id="table"
					@endslot
				@endcomponent	
			</div>
			<div class="mt-10">
				@component("components.labels.title-divisor")
					RELACIÓN DE DOCUMENTOS APROBADOS
				@endcomponent
				@php
					$heads = ["Concepto","Clasificación de gasto","Importe"];
					$modelBody = [];

					$subtotalFinal = $ivaFinal = $totalFinal = 0;

				@endphp
				@component("components.tables.alwaysVisibleTable",[
						"modelHead" => $heads,
						"modelBody" => $modelBody,
					])
					@slot("attributeExBody")
						id="body-approve"
					@endslot
					@slot("classExBody")
						request-validate
					@endslot
					@slot("classEx")
						id="table"
					@endslot
				@endcomponent
			</div>
			<div class="mt-10">
				@component("components.labels.label")
					Comentarios (Opcional):
				@endcomponent
				@component("components.inputs.text-area")
					@slot("classEx")
						text-area
					@endslot
					@slot("attributeEx")
						cols="90"
						rows="10"
						name="checkCommentA"
						placeholder="Ingrese un comentario"
					@endslot
				@endcomponent
			</div>
		</div>
		<div id="rechaza" class="hidden">
			@component("components.labels.label")
				Comentarios (Opcional):
			@endcomponent
			@component("components.inputs.text-area")
				@slot("classEx")
					text-area
				@endslot
				@slot("attributeEx")
					cols="90"
					rows="10"
					name="checkCommentR"
					placeholder="Ingrese un comentario"
				@endslot
			@endcomponent
		</div>
		<div class="text-center my-6">
			@component("components.buttons.button",["variant" => "primary"])
				@slot("attributeEx")
					type="submit" 
					name="enviar"
					value="ENVIAR SOLICITUD"
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

@section('scripts')
<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
<script src="{{ asset('js/jquery-ui.js') }}"></script>
<script src="{{ asset('js/datepicker.js') }}"></script>
<script src="{{ asset('js/jquery.numeric.js') }}"></script>
<script>
	$(document).ready(function()
	{
		totalConcepts = $('#body-classify .tr').length;
		var editDocumentosSolicitados=false;
		$.validate(
		{
			form: '#container-alta',
			onSuccess : function($form)
			{
				if($('input[name="status"]').is(':checked'))
				{
					if($('input#aprobar').is(':checked'))
					{
						generalSelect({'selector': '.js-accountsR', 'depends': '.js-enterprisesR','model': 10});
						enterprise		= $('#multiple-enterprises').val();
						area			= $('#multiple-areas').val();
						department		= $('#multiple-departments').val();
						account			= $('#multiple-accounts').val();
						if(enterprise == '' || area == '' || department == '' || account == '')
						{
							swal('', 'Por favor ingrese los campos obligatorios.', 'error');
							return false;
						}
						if ($('#body-classify .tr').length > 0 || $('#body-approve .tr').length < totalConcepts || editDocumentosSolicitados)
						{
							swal('', 'Por favor reclasifique o apruebe los conceptos solicitados.', 'error');
							return false;
						}
						else
						{
							swal('Cargando',{
								icon: '{{ asset(getenv('LOADING_IMG')) }}',
								button: false,
								closeOnClickOutside: false,
								closeOnEsc: false
							});
							return true;
						}
					}
					else
					{
						swal('Cargando',{
							icon: '{{ asset(getenv('LOADING_IMG')) }}',
							button: false,
							closeOnClickOutside: false,
							closeOnEsc: false
						});
						return true;
					}
				}
				else
				{
					swal('', 'Por favor seleccione un estatus.', 'error');
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
				$('.approve2').removeAttr('disabled');
				$('.reclassify').removeAttr('disabled');
			}
			else if ($('input[name="status"]:checked').val() == "6") 
			{
				$("#aceptar").slideUp("slow");
				$("#rechaza").slideToggle("slow").addClass('form-container').css('display','block');
				$('.approve2').attr('disabled',true);
				$('.reclassify').attr('disabled',true);
			}
			@php
				$selects = collect([
					[
						"identificator"				=> ".js-enterprisesR",
						"placeholder" 				=> "Seleccione la empresa",
						"language"					=> "es",
						"maximumSelectionLength"	=> "1"
					],
					[
						"identificator"				=> ".js-areasR",
						"placeholder" 				=> "Seleccione la dirección",
						"language"					=> "es",
						"maximumSelectionLength"	=> "1"
					],
					[
						"identificator"				=> ".js-departmentsR",
						"placeholder" 				=> "Seleccione el departamento",
						"language"					=> "es",
						"maximumSelectionLength"	=> "1"
					],
				]);
			@endphp
			@component("components.scripts.selects",["selects" => $selects])@endcomponent
			generalSelect({'selector': '.js-labelsR', 'model': 19, 'maxSelection' : 15});
			generalSelect({'selector': '.js-projects', 'model': 21});
			generalSelect({'selector': '.js-code_wbs', 'depends': '.js-projects','model': 22});
			generalSelect({'selector': '.js-code_edt', 'depends': '.js-code_wbs','model': 15});
			generalSelect({'selector': '.js-accountsR', 'depends': '.js-enterprisesR','model': 10});
		})
		.on('change','.js-enterprisesR',function()
		{
			$('.js-accountsR').empty();
			generalSelect({'selector': '.js-accountsR', 'depends': '.js-enterprisesR','model': 10});
			$('.approve2').hide();			
		})
		.on('change','[name="project_id"]',function()
		{
			id = $(this).find('option:selected').val();
			if (id != null)
			{
				$.each(generalSelectProject,function(i,v)
				{
					if(id == v.id)
					{
						if(v.flagWBS != null)
						{
							$('.select_father').removeClass('hidden').addClass('block');
							generalSelect({'selector': '.js-code_wbs', 'depends': '.js-projects','model': 22});
						}
						else
						{
							$('.js-code_wbs, .js-code_edt').html('');
							$('.select_father, .code-edt').removeClass('block').addClass('hidden');
						}
					}
				});
			}
			else
			{
				$('.js-code_wbs, .js-code_edt').html('');
				$('.select_father, .code-edt').removeClass('block').addClass('hidden');				
			}
		})
		.on('change','[name="code_wbs"]',function()
		{
			id = $(this).find('option:selected').val();
			if (id != null)
			{
				$.each(generalSelectWBS,function(i,v)
				{
					if(id == v.id)
					{
						if(v.flagEDT != null)
						{
							$('.code-edt').removeClass('hidden').addClass('block');
							generalSelect({'selector': '.js-code_edt', 'depends': '.js-code_wbs', 'model': 15});
						}
						else
						{
							$('.js-code_edt').html('');
							$('.code-edt').removeClass('block').addClass('hidden');
						}
					}
				});
			}
			else
			{
				$('.js-code_edt').html('');
				$('.code-edt').removeClass('block').addClass('hidden');
			}
		})
		.on('click','.reclassify',function(){
			$('.reclassify').hide();
			editDocumentosSolicitados=true;
			idRefundDetail 	= $(this).parents('#body-classify .tr').find('.idRefundDetail').val();
			concept 		= $(this).parents('#body-classify .tr').find('.concept').val();
			amount 			= $(this).parents('#body-classify .tr').find('.amount').val();
			
			$('#body-reclassify .tr').find('.idRefundDetail').val(idRefundDetail);
			$('#body-reclassify .tr').find('.conceptR').val(concept);
			$('#body-reclassify .tr').find('.amountR').val(amount);
			$('#body-reclassify .tr').find('.idRefundDetail').text(idRefundDetail);
			$('#body-reclassify .tr').find('.conceptR').text(concept);
			$('#body-reclassify .tr').find('.amountR').text("$ "+Number(amount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));
			$('#body-reclassify').find('.tr-reclassify').removeClass('hidden');
			
			$(this).parents('.tr').remove();
			generalSelect({'selector': '.js-accountsR', 'depends': '.js-enterprisesR','model': 10});
		})
		.on('click','.reclassify2',function()
		{
			
			idRefundDetail 	= $(this).parents('#body-reclassify .tr').find('.idRefundDetail').val();
			concept 		= $(this).parents('#body-reclassify .tr').find('.conceptR').val();
			amount 			= $(this).parents('#body-reclassify .tr').find('.amountR').val();
			idaccount 		= $('#body-reclassify .tr select[name="account_idR"] option:selected').val();
			nameaccount 	= $('#body-reclassify .tr select[name="account_idR"] option:selected').text();

			if (idaccount != undefined) 
			{
				$('.reclassify').show();
				editDocumentosSolicitados=false;
				@php
					$heads = ["Concepto","Clasificación de gasto","Importe"];
					$modelBody = [];
					$modelBody[] = 
					[
						"classEx" => "tr",
						[
							"content" =>
							[
								[
									"kind" 			=> "components.inputs.input-text",
									"classEx" 		=> "idRefundDetail",
									"attributeEx" 	=> "type=\"hidden\" name=\"idRDeR[]\"",
								],
								[
									"kind" 			=> "components.inputs.input-text",
									"classEx" 		=> "concept",
									"attributeEx" 	=> "type=\"hidden\" name=\"t_conceptR[]\"",
								],
								[
									"kind" 		=> "components.labels.label",
									"classEx" 	=> "conc",
								]
							]
						],
						[
							"content" =>
							[
								[
									"kind" 		=> "components.labels.label",
									"classEx"	=> "nameaccount",
								],
								[
									"kind"			=> "components.inputs.input-text",
									"classEx"		=> "account",
									"attributeEx"	=> "type=\"hidden\" name=\"t_accountR[]\"",
								]
							]
						],
						[
							"content" =>
							[
								[
									"kind" 		=> "components.labels.label",
									"classEx" 	=> "amount",
								],
								[
									"kind"			=> "components.inputs.input-text",
									"classEx"		=> "amount",
									"attributeEx"	=> "type=\"hidden\" name=\"t_amountR[]\"",
								]
							]
						]
					];
					$table = view("components.tables.alwaysVisibleTable", [
						"modelHead" => $heads,
						"modelBody" => $modelBody,
						"noHead" 	=> true,						
					])->render();
					$table 	= html_entity_decode(preg_replace("/(\r)*(\n)*/", "", $table));
				@endphp	
				approve = '{!!preg_replace("/(\r)*(\n)*/", "", $table)!!}';
				row 	= $(approve);
				row 	= rowColor('#body-approve', row);
				row.find('div').each(function()
				{
					$(this).find('.idRefundDetail').val(idRefundDetail);
					$(this).find('.conc').text(concept);
					$(this).find('.concept').val(concept);
					$(this).find('.nameaccount').text(nameaccount);
					$(this).find('.account').val(idaccount);
					$(this).find('.amount').text("$ "+Number(amount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));
					$(this).find('.amount').val(amount);
				})
				$('#body-approve').append(row);
				$('.js-accountsR').val(null).trigger('change');
				$('#body-reclassify').find('.tr-reclassify').addClass('hidden');
			}
			else
			{
				swal('', 'Debe seleccionar una clasificación de gasto', 'error');
			}
		})
		.on('click','.approve2',function()
		{
			generalSelect({'selector': '.js-accountsR', 'depends': '.js-enterprisesR','model': 10});
			idRefundDetail 	= $(this).parents('#body-classify .tr').find('.idRefundDetail').val();
			concept 		= $(this).parents('#body-classify .tr').find('.concept').val();
			amount 			= $(this).parents('#body-classify .tr').find('.amount').val();
			idaccount 		= $(this).parents('#body-classify .tr').find('.account').val();
			nameaccount 	= $(this).parents('#body-classify .tr').find('.account-label').text();

			@php
					$heads = ["Concepto","Clasificación de gasto","Importe"];
					$modelBody = [];
					$modelBody[] = 
						[
							"classEx" => "tr",
							[
								"content" =>
								[
									[
										"kind" 			=> "components.inputs.input-text",
										"classEx" 		=> "idRefundDetail",
										"attributeEx" 	=> "type=\"hidden\" name=\"idRDeR[]\"",
									],
									[
										"kind" 			=> "components.inputs.input-text",
										"classEx" 		=> "concept",
										"attributeEx" 	=> "type=\"hidden\" name=\"t_conceptR[]\"",
									],
									[
										"kind" 		=> "components.labels.label",
										"classEx" 	=> "conc",
									]
								]
							],
							[
								"content" =>
								[
									[
										"kind" 		=> "components.labels.label",
										"classEx"	=> "nameaccount",
									],
									[
										"kind"			=> "components.inputs.input-text",
										"classEx"		=> "account",
										"attributeEx"	=> "type=\"hidden\" name=\"t_accountR[]\"",
									]
								]
							],
							[
								"content" =>
								[
									[
										"kind" 		=> "components.labels.label",
										"classEx" 	=> "amount",
									],
									[
										"kind"			=> "components.inputs.input-text",
										"classEx"		=> "amount",
										"attributeEx"	=> "type=\"hidden\" name=\"t_amountR[]\"",
									]
								]
							]
						];
						$table = view("components.tables.alwaysVisibleTable", [
							"modelHead" => $heads,
							"modelBody" => $modelBody,
							"noHead" 	=> true,						
						])->render();
						$table 	= html_entity_decode(preg_replace("/(\r)*(\n)*/", "", $table));
				@endphp	
				approve = '{!!preg_replace("/(\r)*(\n)*/", "", $table)!!}';
				row 	= $(approve);
				row 	= rowColor('#body-approve', row);
				row.find('div').each(function()
				{
					$(this).find('.idRefundDetail').val(idRefundDetail);
					$(this).find('.conc').text(concept);
					$(this).find('.concept').val(concept);
					$(this).find('.nameaccount').text(nameaccount);
					$(this).find('.account').val(idaccount);
					$(this).find('.amount').text('$ '+Number(amount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));
					$(this).find('.amount').val(amount);
				})

			$('#body-approve').append(row);
			$(this).parents('.tr').remove();
		})
	});
</script>
@endsection
