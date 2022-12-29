@extends('layouts.child_module')
@section('data')
	@php
		$disableComponent	=	"";
		if (isset($globalRequests))
		{
			$disableComponent	=	"disabled";
		}
	@endphp
	@if (isset($globalRequests) && $globalRequests == true)
		@component("components.labels.not-found", ["variant" => "note"])
			@slot("slot")
				@component("components.labels.label")
					@slot("classEx")
						font-bold inline-block text-blue-900
					@endslot
						TIPO DE SOLICITUD: 
				@endcomponent
				{{ mb_strtoupper($request->requestkind->kind) }}
			@endslot
		@endcomponent
	@endif
	@if (isset($request))
		@component('components.forms.form', ["attributeEx" => "action=\"".route('work_order.update',$request->folio)."\" id=\"container-alta\" method=\"POST\"", "methodEx" => "PUT", "files" => true])
	@else
		@component('components.forms.form', ["attributeEx" => "action=\"".route('work_order.store')."\" method=\"POST\" id=\"container-alta\"", "files" => true])
	@endif
		@if(isset($request))
			@component('components.labels.subtitle', ["label" => "Folio: ".$request->folio, "classExContainer" => "mb-4"]) @endcomponent
		@endif
		<div class="bg-orange-500 w-full text-white text-center font-semibold py-2">
			{{isset($request) ? "ORDEN DE TRABAJO" : "NUEVA ORDEN DE TRABAJO"}}
		</div>
		@component('components.containers.container-form')
			<div class="col-span-2">
				@component('components.labels.label')
					Proyecto:
				@endcomponent
				@php
					$options	=	collect();
					if (isset($request) && $request->idProject != "")
					{
						$options	=	$options->concat([[ "value"	=>	$request->requestProject->idproyect, "description"	=>	$request->requestProject->proyectName, "selected"	=>	"selected"]]);
					}
				@endphp
				@component('components.inputs.select', ["options" => $options])
					@slot('attributeEx')
						id="projectOrder" name="project_id" multiple="multiple" data-validation="required"
						@if(isset($request) && $request->status != 2) disabled="disabled" @endif
					@endslot
					@slot('classEx')
						removeselect
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label')
					Título:
				@endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						id="titleOrder"
						type="text"
						name="title"
						placeholder="Ingrese el título"
						data-validation="required"
						value="{{ isset($request) ? $request->workOrder->title : '' }}"
						@if(isset($request) && $request->status != 2) disabled="disabled" @endif
					@endslot
					@slot('classEx')
						removeselect
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label')
					Orden de trabajo No.:
				@endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						id="numberOrder"
						type="text"
						name="number"
						placeholder="Ingrese el número de orden"
						data-validation="required"
						value="{{ isset($request) ? $request->workOrder->number : App\WorkOrder::count()+1 }}"
						@if(isset($request) && $request->status != 2) disabled="disabled" @endif
					@endslot
					@slot('classEx')
						removeselect
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label')
					Fecha en que deben estar en obra:
				@endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						id="dateObraOrder"
						type="text"
						name="date_obra"
						data-validation="required"
						placeholder="Ingrese la fecha"
						readonly="readonly"
						value="{{ isset($request) ? Carbon\Carbon::parse($request->workOrder->date_obra)->format('d-m-Y') : '' }}"
						@if(isset($request) && $request->status != 2) disabled="disabled" @endif
					@endslot
					@slot('classEx')
						removeselect
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label')
					Solicitante:
				@endcomponent
				@php
					$options	=	collect();
					if (isset($request) && $request->workOrder->applicant && $request->workOrder->relationApplicant->exists() && $request->workOrder->relationApplicant->name  != "")
					{
						$options	=	$options->concat([["value"	=>	$request->workOrder->relationApplicant->name, "description"	=>	$request->workOrder->relationApplicant->name, "selected"		=>	"selected"]]);
					}
				@endphp
				@component('components.inputs.select', ["options" => $options])
					@slot('attributeEx')
						id="applicantOrder"
						name="applicant"
						multiple="multiple"
						data-validation="required"
						@if(isset($request) && $request->status != 2) disabled="disabled" @endif
					@endslot
					@slot('classEx')
						removeselect
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label')
					Prioridad:
				@endcomponent
				@php
					$optionsPriority	=	[];
					$urgent 			= [0 => "Baja",1 => "Media",2 => "Alta"];
					foreach ($urgent as $priority => $description ) 
					{
						if (isset($request) && $request->workOrder->urgent == $priority)
						{
							$optionsPriority[]	=	["value"	=>	$priority, "description"	=>	$description, "selected"	=> "selected"];
						}
						else
						{
							$optionsPriority[]	=	["value"	=>	$priority, "description"	=>	$description];
						}
					}
				@endphp
				@component('components.inputs.select', ["options" => $optionsPriority])
					@slot('attributeEx')
						id="priorityOrder"
						name="urgent"
						multiple="multiple"
						data-validation="required"
						@if(isset($request) && $request->status != 2) disabled="disabled" @endif
					@endslot
					@slot('classEx')
						removeselect
					@endslot
				@endcomponent
			</div>
		@endcomponent
		<div class="@if (isset($globalRequests)) hidden @endif">
			@component('components.labels.title-divisor')
				@slot('classEx')
					mt-12
				@endslot
				CARGA MASIVA (OPCIONAL)
			@endcomponent
			@component('components.labels.not-found', ["variant" =>	"note"])
				<div>
					Si desea cargar conceptos de forma masiva para esta orden de trabajo, utilice la siguiente plantilla.
					<div class="inline-block">
						@component('components.buttons.button', ["variant" =>	"success"])
							@slot('attributeEx')
								type="button" href="{{route('work_order.download-layout')}}"
							@endslot
							@slot('buttonElement')
								a
							@endslot
							@slot('label')
								DESCARGAR PLANTILLA
							@endslot
						@endcomponent
					</div>
				</div>
				@if(!isset($request))
				<div>
					Si desea cargar multiples solicitudes de orden de trabajo, utilice la siguiente plantilla.
					<div class="inline-block">
						@component('components.buttons.button', ["variant" =>	"success"])
							@slot('attributeEx')
							type="button" href="{{route('work_order.download-layout-multiple')}}"
							@endslot
							@slot('buttonElement')
								a
							@endslot
							@slot('label')
								DESCARGAR PLANTILLA
							@endslot
						@endcomponent
					</div>
				</div>
				@endif
			@endcomponent
			<div class="mt-12">
				@php
					if (isset($request))
					{
						if ($request->status == 2 || isset($globalRequests))
						{
							$buttonExload =
							[
								"separator"	=>
								[
									["kind" => "components.buttons.button-approval", "label" => "coma (,)", "attributeEx" => "checked value=\",\" name=\"separator\" id=\"separatorComa\" $disableComponent"],
									["kind" => "components.buttons.button-approval", "label" => "Punto y coma (;)", "attributeEx" => "value=\";\" name=\"separator\" id=\"separatorPuntoComa\" $disableComponent"]
								],
								"buttonEx"	=>
								[
									["kind"	=>	"components.buttons.button",	"label"	=>	"CARGAR ARCHIVO",	"variant"	=>	"primary",	"attributeEx"	=>	"type=\"submit\" id=\"upload_file\" formaction=\"".route('work_order.save-follow',$request->folio)."\" $disableComponent"],
								]
							];
						}
					}
					else
					{
						$buttonExload =
						[
							"separator"	=>
							[
								["kind" => "components.buttons.button-approval", "label" => "coma (,)", "attributeEx" => "value=\",\" name=\"separator\" id=\"separatorComa\" $disableComponent checked"],
								["kind" => "components.buttons.button-approval", "label" => "Punto y coma (;)", "attributeEx" => "value=\";\" name=\"separator\" id=\"separatorPuntoComa\" $disableComponent"]
							],
							"buttonEx"	=>
							[
								["kind"	=>	"components.buttons.button",	"label"	=>	"CARGAR ARCHIVO",	"variant"	=>	"primary",	"attributeEx"	=>	"type=\"submit\" id=\"upload_file\" formaction=\"".route('work_order.store.detail')."\" $disableComponent"],
							]
						];
					}
				@endphp
				@component("components.documents.select_file_csv", ["attributeExInput" => "name=\"csv_file\" id=\"files\" id=\"update_to_select\" $disableComponent", "buttons" => $buttonExload])
				@endcomponent
			</div>
		</div>
		@component('components.labels.title-divisor')
			@slot('classEx')
				mt-12
			@endslot
			Detalles de la orden de trabajo
		@endcomponent
		@php
			$modelHead	=	[];
			$body		=	[];
			$modelBody	=	[];
			$modelHead	=
			[
				[
					["value"	=>	"Part."],
					["value"	=>	"Cant."],
					["value"	=>	"Unidad"],
					["value"	=>	"Descripción"],
					["value"	=>	"Acciones"],
				]
			];
			if (isset($request))
			{
				if ($request->workOrder->details()->exists())
				{
					foreach ($request->workOrder->details as $key=>$detail)
					{
						$body	=
						[
							"classEx"	=>	"row_concepts",
							[
								"content"	=>
								[
									["label"	=>	htmlentities($detail->part)],
									[
										"kind"			=>	"components.inputs.input-text",
										"attributeEx"	=>	"type=\"hidden\" name=\"idWorkOrderDetail[]\" value=\"".$detail->id."\"",
										"classEx"		=>	"id"
									],
									[
										"kind"			=>	"components.inputs.input-text",
										"attributeEx"	=>	"type=\"hidden\" name=\"t_part\" value=\"".htmlentities($detail->part)."\"",
										"classEx"		=>	"t_part"
									],
								]
							],
							[
								"content"	=>
								[
									["label"	=>	$detail->quantity],
									[
										"kind"			=>	"components.inputs.input-text",
										"attributeEx"	=>	"type=\"hidden\" value=\"".$detail->quantity."\"",
										"classEx"		=>	"t_quantity"
									],
								],
							],
							[
								"content"	=>
								[
									["label"	=>	$detail->unit],
									[
										"kind"			=>	"components.inputs.input-text",
										"attributeEx"	=>	"type=\"hidden\" value=\"".$detail->unit."\"",
										"classEx"		=>	"t_unit"
									],
								],
							],
							[
								"content"	=>
								[
									["label"	=>	htmlentities($detail->description)],
									[
										"kind"			=>	"components.inputs.input-text",
										"attributeEx"	=>	"type=\"hidden\" value=\"".htmlentities($detail->description)."\"",
										"classEx"		=>	"t_description"
									],
								],
							],
						];
						if ($request->status == 2)
						{
							$disabled	=	$request->status == 1 ? "disabled" : "";
							
							$body[] =
							[
								"content" => 
								[
									[
										"kind"			=>	"components.buttons.button",
										"variant"		=>	"success",
										"attributeEx"	=>	"type=\"button\" $disabled",
										"classEx"		=>	"edit-art",
										"label"			=>	"<span class='icon-pencil'></span>"
									],
									[
										"kind"			=>	"components.buttons.button",
										"variant"		=>	"red",
										"attributeEx"	=>	"type=\"button\" $disabled",
										"classEx"		=>	"delete-art",
										"label"			=>	"<span class='icon-x'></span>"
									]
								]
							];
						}
						$modelBody[]	=	$body;
					}
				}
			}
		@endphp
		@component('components.tables.table', ["modelHead" => $modelHead, "modelBody" => $modelBody])
			@slot('title')
				ARTÍCULOS
			@endslot
			@slot('classEx')
				mt-4
			@endslot
			@slot('attributeExBody')
				id="body_art"
			@endslot
			@slot('classExBody')
				request-validate
			@endslot
		@endcomponent
		@php
			$modelHead	=	[];
			$body		=	[];
			$modelBody	=	[];
			$modelHead	=	["Part.", "Cant.", "Unidad", "Descripción", ""];
			foreach (App\CatMeasurementTypes::whereNotNull('type')->get() as $m_types)
			{
				foreach ($m_types->childrens()->orderBy('child_order','asc')->get() as $child)
				{
					$optionsUnits[]	=
					[
						"value"			=>	$child->description,
						"description"	=>	$child->description,
					];
				}
			}
			$body	=
			[
				[
					"content"	=>
					[
						[
							"kind"			=>	"components.inputs.input-text",
							"attributeEx"	=>	"type=\"text\" placeholder=\"Ingrese las partes\" $disableComponent",
							"classEx"		=>	"part"
						]
					],
				],
				[
					"content"	=>
					[
						[
							"kind"			=>	"components.inputs.input-text",
							"attributeEx"	=>	"type=\"text\" placeholder=\"Ingrese la cantidad\" $disableComponent",
							"classEx"		=>	"quantity"
						]
					],
				],
				[
					"content"	=>
					[
						[
							"kind"			=>	"components.inputs.select",
							"options"		=>	$optionsUnits,
							"attributeEx"	=>	"multiple=\"multiple\" $disableComponent",
							"classEx"		=>	"js-measurement_compras unit"
						]
					]
				],
				[
					"content"	=>
					[
						[
							"kind"			=>	"components.inputs.input-text",
							"attributeEx"	=>	"type=\"text\" placeholder=\"Ingrese la descripción\" $disableComponent",
							"classEx"		=>	"description"
						]
					],
				],
				[
					"content"	=>
					[
						[
							"kind"			=>	"components.buttons.button",
							"variant"		=>	"warning",
							"label"			=>	"<span class='icon-plus'></span>",
							"attributeEx"	=>	"type=\"button\" id=\"addArt\" $disableComponent",
						]
					],
				],
			];
			$modelBody[]	=	$body;
		@endphp
		@component('components.tables.alwaysVisibleTable', ["modelHead" => $modelHead, "modelBody" => $modelBody])@endcomponent
		@component('components.labels.title-divisor')
			@slot('classEx')
				mt-12
			@endslot
			@if (isset($globalRequests) && !$request->workOrder->documents()->exists())
				@slot("classExContainer")
					hidden
				@endslot
			@endif
			DOCUMENTOS
		@endcomponent
		@if(isset($request) && $request->workOrder->documents()->exists())
			@php
				$modelHead	=	[];
				$body		=	[];
				$modelBody	=	[];
				$modelHead	=	["Nombre","Archivo","Modificado Por","Fecha"];
				foreach($request->workOrder->documents->sortByDesc('created') as $doc)
				{
					$body	=
					[
						[
							"content"	=>	["label"	=>	$doc->name],
						],
						[
							"content"	=>
							[
								[
									"kind"			=>	"components.buttons.button",
									"variant"		=>	"secondary",
									"buttonElement"	=>	"a",
									"attributeEx"	=>	"target=\"_blank\" href=\"".url('docs/work_order/'.$doc->path)."\"",
									"label"			=>	"Archivo"
								]
							],
						],
						[
							"content"	=>	["label"	=>	$doc->user->fullName()],
						],
						[
							"content"	=>	["label"	=>	Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$doc->created)->format('d-m-Y')],
						],
					];
					$modelBody[]	=	$body;
				}
			@endphp
			@component('components.tables.alwaysVisibleTable', ["modelHead" => $modelHead, "modelBody" => $modelBody])@endcomponent
		@endif
		@component('components.containers.container-form', ["classEx" => ((isset($globalRequests)) ? "hidden" : "")])
			<div class="col-span-2 md:col-span-4 grid grid-cols-1 md:grid-cols-2 gap-6" id="documents-workOrder">
				@php
					$options = collect(
						[
							["value"	=>	"Cotización",			"description"	=>	"Cotización"], 
							["value"	=>	"Ficha Técnica",		"description"	=>	"Ficha Técnica"], 
							["value"	=>	"Control de Calidad",	"description"	=>	"Control de Calidad"], 
							["value"	=>	"Contrato",				"description"	=>	"Contrato"], 
							["value"	=>	"Factura",				"description"	=>	"Factura"], 
							["value"	=>	"Otro",					"description"	=>	"Otro"]
						]
					);
					$componentsExUp =
					[
						["kind"	=>	"components.labels.label",		"classEx"		=>	"font-bold",	"label"	=>	"Tipo de documento"],
						["kind"	=>	"components.inputs.select",		"attributeEx"	=>	"id=\"kindDocument\" name=\"nameDocumentWorkOrder[]\" data-validation=\"required\"",	"classEx"	=>	"nameDocumentWorkOrder",	"options"	=>	$options],
					];
				@endphp
				@component('components.documents.upload-files', ["componentsExUp" => $componentsExUp ])
					@slot('classExInput')
						inputDoc pathActionerWorkOrder
					@endslot
					@slot('classExDelete')
						delete-doc
					@endslot
					@slot('attributeExDelete')
						{{$disableComponent}}
					@endslot
					@slot('attributeExInput')
						type="file"
						name="path"
						accept=".pdf,.jpg,.png"
					@endslot
					@slot('attributeExRealPath')
						id="documentOrder"
						name="realPathWorkOrder[]"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2 md:col-span-4 space-x-2 text-center md:text-left">
				@component('components.buttons.button', ["variant" => "warning"])
					@slot('attributeEx')
						type="button" id="addDocWorkOrder" name="addDocWorkOrder" {{$disableComponent}}
					@endslot
					<span class="icon-plus"></span>
					<span>Agregar documento</span>
				@endcomponent
				@isset($request)
					@component('components.buttons.button', ["variant" => "success"])
						@slot('attributeEx')
							type="submit"
							id="save"
							name="save"
							value="CARGAR DOCUMENTOS"
							formaction="{{ route('work_order.upload-documents',$request->folio) }}"
							@if($request->status == 1) disabled @endif
						@endslot
						@slot('classEx')
							save
							mt-4
						@endslot
						@slot('label')
							CARGAR DOCUMENTOS
						@endslot
					@endcomponent
				@endisset
			</div>
		@endcomponent
		<span id="spanDelete"></span>
		<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center">
			@isset($request)
				@if($request->status == 1)
					@component("components.buttons.button", ["variant"=>"reset", "buttonElement"=>"a"])
						@slot("classEx")
							load-actioner
							text-center
							w-48
							md:w-auto
						@endslot
						@slot("attributeEx")
							@if(isset($option_id))
								href="{{ url(App\Module::find($option_id)->url) }}"
							@else
								href="{{ url(App\Module::find($child_id)->url) }}"
							@endif
						@endslot
						REGRESAR
					@endcomponent
				@else
					@component('components.buttons.button', ["variant" =>	"secondary"])
						@slot('classEx')
							mt-4
						@endslot
						@slot('attributeEx')
							type="submit"
							id="save"
							name="save"
							value="GUARDAR CAMBIOS"
							formaction="{{ route('work_order.update',$request->folio) }}"
						@endslot
						@slot('classEx')
							save
							mt-4
						@endslot
						@slot('label')
							GUARDAR CAMBIOS
						@endslot
					@endcomponent
				@endif
				@else
					@component('components.buttons.button', ["variant" => "primary"])
						@slot('attributeEx')
							type="submit"
							id="save"
							name="save"
							value="GUARDAR CAMBIOS"
							formaction="{{ route('work_order.save') }}"
						@endslot
						@slot('classEx')
							save
							mt-4
						@endslot
						@slot('label')
							ENVIAR SOLICITUD
						@endslot
					@endcomponent
			@endisset
		</div>
	@endcomponent
@endsection

@section('scripts')
	<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
<script src="{{ asset('js/jquery-ui.js') }}"></script>
<script src="{{ asset('js/jquery.numeric.js') }}"></script>
<script src="{{ asset('js/datepicker.js') }}"></script>
<script src="{{ asset('js/papaparse.min.js') }}"></script>
<script type="text/javascript">
	function validate()
	{
		$.validate(
		{
			form	: '#container-alta',
			modules	: 'security',
			onError   : function($form)
			{
				swal('', '{{ Lang::get("messages.form_error") }}', 'error');
				return false;
			},
			onSuccess : function($form)
			{
				trpart			=	$('.part').val();
				trquantity		=	$('.quantity').val();
				trunit			=	$('.unit').val();
				trdescription	=	$('.description').val();
				needFileName = false
				$('input[name="realPathWorkOrder[]').each(function(){
					if($(this).val() != "" )
					{
						select = $(this).parents('div').find('.nameDocumentWorkOrder')
						name = select.find('option:selected').val()
						if(name == 0)
						{
							needFileName = true;
						}
					}
				});
				if(needFileName)
				{
					swal('', 'Debe seleccionar el tipo de documento', 'error');
					return false;
				}
				if (trpart!="" || trquantity!="" || trunit!="" || trdescription!="")
				{
					swal('', 'Tiene un concepto pendiente de agregar', 'error');
					return false;
				}
				if($('.request-validate').length>0)
				{
					conceptos	= $('.row_concepts').length;
					if(conceptos>0)
					{
						swal("Cargando",{
							icon				: '{{ asset(getenv('LOADING_IMG')) }}',
							button				: true,
							closeOnClickOutside	: false,
							closeOnEsc			: false
						});
						return true;
					}
					else
					{
						swal('', 'Debe ingresar al menos un concepto de pedido', 'error');
						return false;
					}
				}
				else
				{	
					swal("Cargando",{
						icon				: '{{ asset(getenv('LOADING_IMG')) }}',
						button				: true,
						closeOnClickOutside	: false,
						closeOnEsc			: false
					});
					return true;
				}		
			}
		});
	}

	$(document).ready(function()
	{
		validate();
		generalSelect({'selector': '#projectOrder', 'option_id':'{{$option_id}}', 'model': 41});
		generalSelect({'selector': '#applicantOrder', 'option_id':'{{$option_id}}', 'model': 37});
		@php
			$selects = collect([
				[
					"identificator"				=> "#priorityOrder",
					"placeholder"				=> "Seleccione la prioridad",
					"language"					=> "es",
					"maximumSelectionLength"	=> "1"
				],
				[
					"identificator"				=> ".unit",
					"placeholder"				=> "Seleccione la unidad",
					"language"					=> "es",
					"maximumSelectionLength"	=> "1"
				],
				[
					"identificator"				=> ".nameDocumentWorkOrder",
					"placeholder"				=> "Seleccione el tipo de documento",
					"language"					=> "es",
					"maximumSelectionLength"	=> "1"
				],
			]);
		@endphp
		@component('components.scripts.selects',["selects" => $selects]) @endcomponent
		$('.quantity',).numeric({ altDecimal: ".", decimalPlaces: 2 });
		$('[name="date_obra"]').datepicker({  dateFormat: "dd-mm-yy" });
		
		$(document).on('click','#addArt',function()
		{
			$('button[name="edit-art"]').prop('disabled', false);
			$('button[name="delete-art"]').prop('disabled', false);
			part		=	$(this).parents('div').parents('div').find('.part').val();
			quantity	=	$(this).parents('div').parents('div').find('.quantity').val();
			unit		=	$(this).parents('div').parents('div').find('.unit option:selected').val();
			description	=	$(this).parents('div').parents('div').find('.description').val();

			$('.part,.quantity,.unit,.exists_warehouse').removeClass('error');

			if (part == "" || quantity == "" || unit == undefined || unit == "" || description == "") 
			{
				if (part == "")
					$('.part').addClass('error');
				if (quantity == "")
					$('.quantity').addClass('error');
				if (unit == "" || unit == undefined)
					$('.unit').addClass('error');
				if (description == "")
					$('.description').addClass('error');

				swal('','Faltan campaos por agregar','error');
			}
			else
			{
				@php
					$body		=	[];
					$modelBody	=	[];
					$modelHead	=
					[
						[
							["value"	=>	"Part"],
							["value"	=>	"Cant."],
							["value"	=>	"Unidad"],
							["value"	=>	"Descripción"],
							["value"	=>	""]
						]
					];
					$body	=
					[ "classEx"	=>	"row_concepts",
						[
							"content"	=>
							[
								[
									"kind"			=>	"components.labels.label",
									"classEx"		=>	"t_part_txt",
								],
								[
									"kind"			=>	"components.inputs.input-text",
									"attributeEx"	=>	"type=\"hidden\" name=\"part[]\" placeholder=\"Ingrese las partes\"",
									"classEx"		=>	"t_part"
								]
							]
						],
						[
							"content"	=>
							[
								[
									"kind"			=>	"components.labels.label",
									"classEx"		=>	"t_quantity_txt",
								],
								[
									"kind"			=>	"components.inputs.input-text",
									"attributeEx"	=>	"type=\"hidden\" name=\"quantity[]\" placeholder=\"Ingrese la cantidad\"",
									"classEx"		=>	"t_quantity"
								]
							]
						],
						[
							"content"	=>
							[
								[
									"kind"			=>	"components.labels.label",
									"classEx"	=>	"t_unit_txt",
								],
								[
									"kind"			=>	"components.inputs.input-text",
									"attributeEx"	=>	"type=\"hidden\" name=\"unit[]\" placeholder=\"Ingrese la unidad\"",
									"classEx"		=>	"t_unit"
								]
							]
						],
						[
							"content"	=>
							[
								[
									"kind"			=>	"components.labels.label",
									"classEx"		=>	"t_description_txt",
								],
								[
									"kind"			=>	"components.inputs.input-text",
									"attributeEx"	=>	"type=\"hidden\" name=\"description[]\" placeholder=\"Ingrese la descripción\"",
									"classEx"		=>	"t_description"
								]
							]
						],
						[
							"content" => 
							[
								[
									"kind"			=>	"components.buttons.button",
									"variant"		=>	"success",
									"label"			=>	'<span class="icon-pencil"></span>',
									"classEx"		=>	"edit-art",
									"attributeEx"	=>	"id=\"edit\" name=\"edit-art\" type=\"button\""
								],
								[
									"kind"			=>	"components.buttons.button",
									"variant"		=>	"red",
									"label"			=>	'<span class="icon-x delete-span"></span>',
									"attributeEx"	=>	"id=\"cancel\" name=\"delete-art\" type=\"button\"",
									"classEx"		=>	"delete-art"
								]
							]
						]
					];
					$modelBody[] = $body;
					$table2 = view('components.tables.table', ["modelHead" => $modelHead, "modelBody" => $modelBody, "noHead"	=> "true"])->render();
				@endphp
				body = '{!!preg_replace("/(\r)*(\n)*/", "", $table2)!!}';
				row = $(body);
				row.find('.t_part_txt').text(part);
				row.find('.t_part').val(part);
				row.find('.t_quantity_txt').text(quantity);
				row.find('.t_quantity').val(quantity);
				row.find('.t_unit_txt').text(unit);
				row.find('.t_unit').val(unit);
				row.find('.t_description_txt').text(description);
				row.find('.t_description').val(description);
				$('#body_art').append(row);
				$('.part,.quantity,.unit,.description').val('');
				$('.unit').val(0).trigger('change');
				swal('','Artículo agregado','success');
				$('.edit-art').removeAttr('disabled');
				$('.delete-art').removeAttr('disabled');
			}
		})
		
		.on('click','.delete-art',function()
		{
			id = $(this).parents('div').parents('div').find('.id').val();
			if (id != "x") 
			{
				deleteID = $('<input type="hidden" name="delete[]" value="'+id+'">');
				$('#spanDelete').append(deleteID);
			}
			$(this).parents('.row_concepts').remove();
			swal('','Concepto eliminado','success');
		})
		.on('click','.edit-art',function()
		{
			tr = $(this).parents('div').parent('.row_concepts');
			id = tr.find('.id').val();
			$('.edit-art').prop('disabled', true);
			$('.delete-art').prop('disabled', true);
			t_part             = tr.find('.t_part').val();
			t_quantity         = tr.find('.t_quantity').val();
			t_unit             = tr.find('.t_unit').val();
			t_description      = tr.find('.t_description').val();
			$('.unit').val(t_unit).trigger('change');
			$('.part').val(t_part);
			$('.quantity').val(t_quantity);
			$('.description').val(t_description);

			if (id != "x") 
			{
				deleteID = $('<input type="hidden" name="delete[]" value="'+id+'">');
				$('#spanDelete').append(deleteID);
			}
			$(this).parents('.row_concepts').remove();
		})
		.on('change','#files',function(e)
		{
			label		= $(this).next('label');
			fileName	= e.target.value.split( '\\' ).pop();
			if(fileName)
			{
				label.find('span').html(fileName);
			}
			else
			{
				label.html(labelVal);
			}
		})
		.on('click','[name="send"]',function (e)
		{
			form = $(this).parents('form');
			if ($('[name="csv_file"]').val() != "") 
			{
				e.preventDefault();
				swal({
					title: "Tiene un archivo sin cargar ¿Desea enviar la solicitud?",
					text : "Los registros que se encuentren en el archivo no serán cargados, primero deberá guardar los cambios en el sistema y comprobar que se hayan subido los registros de su archivo.",
					icon: "warning",
					buttons: ["Cancelar","OK"],
				})
				.then((isConfirm) =>
				{
					if(isConfirm)
					{
						form.submit();
					}
				});
			}
			else
			{
				form.submit();
			}
		})
		.on('click','#addDocWorkOrder',function()
		{
			@php
				$options = collect(
					[
						["value"	=>	"Cotización",			"description"	=>	"Cotización"],
						["value"	=>	"Ficha Técnica",		"description"	=>	"Ficha Técnica"],
						["value"	=>	"Control de Calidad",	"description"	=>	"Control de Calidad"],
						["value"	=>	"Contrato",				"description"	=>	"Contrato"],
						["value"	=>	"Factura",				"description"	=>	"Factura"],
						["value"	=>	"Otro",					"description"	=>	"Otro"]
					]
				);
				$newDoc = view('components.documents.upload-files',[
					"attributeExInput"	=>	"type=\"file\" name=\"path\" accept=\".pdf,.jpg,.png\"",
					"componentsExUp"	=>
						[
							["kind"	=>	"components.labels.label",		"classEx"		=>	"font-bold",	"label"	=>	"Tipo de documento"],
							["kind"	=>	"components.inputs.select",		"attributeEx"	=>	"name=\"nameDocumentWorkOrder[]\" data-validation=\"required\"",		"classEx"	=>	"nameDocumentWorkOrder",	"options"	=>	$options], 
						],
					"attributeExRealPath"	=>	"id=\"documentOrder\" name=\"realPathWorkOrder[]\"",
					"classExInput"			=>	"inputDoc pathActionerWorkOrder",
					"classExDelete"			=>	"delete-doc",
				])->render();
			@endphp
			newDoc = '{!!preg_replace("/(\r)*(\n)*/", "", $newDoc)!!}';
			containerNewDoc = $(newDoc);
			$('#documents-workOrder').append(containerNewDoc);
			@php
				$select = collect([
					[
						"identificator"				=> ".nameDocumentWorkOrder",
						"placeholder"				=> "Seleccione el tipo de documento",
						"maximumSelectionLength"	=> "1"
					],
				]);
			@endphp
			@component('components.scripts.selects',["selects" => $select]) @endcomponent
			$(function() 
			{
				$( ".datepicker" ).datepicker({ maxDate: 0, dateFormat: "dd-mm-yy" });
			});
		})
		.on('change','.inputDoc.pathActionerWorkOrder',function(e)
		{
			filename		= $(this);
			uploadedName 	= $(this).parent('.uploader-content').siblings('input[name="realPathWorkOrder[]"]');
			extention		= /\.jpg|\.png|\.jpeg|\.pdf/i;
			
			if (filename.val().search(extention) == -1)
			{
				swal('', 'El tipo de archivo no es soportado, por favor seleccione una imagen jpg, png o un archivo pdf', 'warning');
				$(this).val('');
			}
			else if (this.files[0].size>315621376)
			{
				swal('', 'El tamaño máximo de su archivo no debe ser mayor a 300Mb', 'warning');
			}
			else
			{
				$(this).css('visibility','hidden').parent('.uploader-content').addClass('loading').removeClass(function (index, css)
				{
					return (css.match (/\bimage_\S+/g) || []).join(' '); // removes anything that starts with "image_"
				});
				formData	= new FormData();
				formData.append(filename.attr('name'), filename.prop("files")[0]);
				formData.append(uploadedName.attr('name'),uploadedName.val());
				$.ajax(
				{
					type		: 'post',
					url			: '{{ route("work_order.upload") }}',
					data		: formData,
					contentType	: false,
					processData	: false,
					success		: function(r)
					{
						if(r.error=='DONE')
						{
							$(e.currentTarget).removeAttr('style').parent('.uploader-content').removeClass('loading').addClass('image_success');
							$(e.currentTarget).parent('.uploader-content').siblings('input[name="realPathWorkOrder[]"]').val(r.path);
							$(e.currentTarget).val('');
						}
						else
						{
							swal('',r.message, 'error');
							$(e.currentTarget).removeAttr('style').parent('.uploader-content').removeClass('loading');
							$(e.currentTarget).val('');
							$(e.currentTarget).parent('.uploader-content').siblings('input[name="realPathWorkOrder[]"]').val('');
						}
					},
					error: function()
					{
						swal('', 'Ocurrió un error durante la carga del archivo, intente de nuevo, por favor', 'error');
						$(e.currentTarget).removeAttr('style').parent('.uploader-content').removeClass('loading');
						$(e.currentTarget).val('');
						$(e.currentTarget).parent('.uploader-content').siblings('input[name="realPathWorkOrder[]"]').val('');
					}
				})
			}
		})
		.on('click','.delete-doc-workOrder',function()
		{
			swal(
			{
				icon	: '{{ asset(getenv('LOADING_IMG')) }}',
				button	: false
			});
			actioner		= $(this);
			uploadedName	= $(this).parents('.docs-p').find('input[name="realPathWorkOrder[]"]');
			formData		= new FormData();
			formData.append(uploadedName.attr('name'),uploadedName.val());
			$.ajax(
			{
				type		: 'post',
				url			: '{{ route("work_order.upload") }}',
				data		: formData,
				contentType	: false,
				processData	: false,
				success		: function(r)
				{
					swal.close();
					actioner.parents('.docs-p').remove();
				},
				error		: function()
				{
					swal.close();
					actioner.parents('.docs-p').remove();
				}
			});
			$(this).parents('.docs-p').remove();
		})
		.on('click','.delete-doc',function()
		{
			swal(
			{
				icon	: '{{ asset(getenv('LOADING_IMG')) }}',
				button	: false
			});
			actioner		= $(this);
			uploadedName	= $(this).parents('.docs-p').find('input[name="realPathWorkOrder[]"]');
			formData		= new FormData();
			formData.append(uploadedName.attr('name'),uploadedName.val());
			$.ajax(
			{
				type		: 'post',
				url			: '{{ route("work_order.upload") }}',
				data		: formData,
				contentType	: false,
				processData	: false,
				success		: function(r)
				{
					swal.close();
					actioner.parents('.docs-p').remove();
				},
				error		: function()
				{
					swal.close();
					actioner.parents('.docs-p').remove();
				}
			});
			$(this).parents('.docs-p').remove();
		})
	});
</script>
@endsection
