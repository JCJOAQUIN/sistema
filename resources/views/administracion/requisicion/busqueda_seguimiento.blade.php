@extends('layouts.child_module')
  
@section('data')
	@component('components.labels.title-divisor') Buscar Solicitudes @endcomponent
	@php
		$form	= [ 'id'	=> 'formsearch'];
		$values = [ 'folio'	=> isset($folio) ? $folio : "" ];
		$hidden = [
			'enterprise',
			'rangeDate',
			'name'
		];
	@endphp  
	@component('components.forms.searchForm',["form" => $form, "values" => $values, "hidden" => $hidden])
		@slot('contentEx')
			<div class="col-span-2">
				@component('components.labels.label') Solicitante: @endcomponent
				@php
					$optionsUser = [];
					if(isset($user_request))
					{
						$users = App\User::whereIn('id',$user_request)->orderBy('name','asc')->orderBy('last_name','asc')->orderBy('scnd_last_name','asc')->get();
						foreach ($users as $user)
						{
							$optionsUser[]	= ["value" => $user->id, "selected" => "selected", "description" => $user->fullname()];
						}
					}
					$attributeEx	= "title=\"Nombre del solicitante\" name=\"user_request[]\"";
					$classEx		= "js-users";
				@endphp
				@component('components.inputs.select', 
					[
						'options'     => $optionsUser, 
						'attributeEx' => $attributeEx, 
						'classEx'     => $classEx
					])
				@endcomponent
			</div> 
			<div class="col-span-2">
				@component('components.labels.label') Número (No.): @endcomponent
				@isset($number)
					@component('components.inputs.input-text', 
						[
							'attributeEx' => "name=\"number\" placeholder=\"Ingrese un número de requisición\" value=\"".$number."\""
						])
					@endcomponent
				@else
					@component('components.inputs.input-text', 
						[
							'attributeEx' => "name=\"number\" placeholder=\"Ingrese un número de requisición\""
						])
					@endcomponent
				@endif
			</div>  
			<div class="col-span-2">
				@component('components.labels.label') Título: @endcomponent
				@php
					$valueTitle = isset($title_request) ? htmlentities($title_request) : '';
				@endphp
				@component('components.inputs.input-text', 
					[
						'attributeEx' => "name=\"title_request\" placeholder=\"Ingrese un título\" value=\"".$valueTitle."\""
					])
				@endcomponent
			</div>  
			<div class="col-span-2">
				@component("components.labels.label") Fecha en que se solicita: @endcomponent
				@php
					$min = isset($mindate_request) ? $mindate_request : '';
					$max = isset($maxdate_request) ? $maxdate_request : '';

					$inputs = [
						[
							"input_classEx"		=> "datepicker",
							"input_attributeEx" => "name=\"mindate_request\" step=\"1\" placeholder=\"Desde\" value=\"".$min."\"",
						],
						[
							"input_classEx"		=> "datepicker",
							"input_attributeEx" => "name=\"maxdate_request\" step=\"1\" placeholder=\"Hasta\" value=\"".$max."\"",
						]
					];
				@endphp
				@component("components.inputs.range-input",["inputs" => $inputs]) @endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Fecha en que deben estar en obra: @endcomponent
				@php
					$minObra = isset($mindate_obra) ? $mindate_obra : '';
					$maxObra = isset($maxdate_obra) ? $maxdate_obra : '';
					$inputs = [
						[
							"input_classEx" => "input-text-date datepicker",
							"input_attributeEx" => "name=\"mindate_obra\" step=\"1\" placeholder=\"Desde\" value=\"".$minObra."\"",
						],
						[
							"input_classEx" => "input-text-date datepicker",
							"input_attributeEx" => "name=\"maxdate_obra\" step=\"1\" placeholder=\"Hasta\" value=\"".$maxObra."\"",
						]
					];
				@endphp
				@component("components.inputs.range-input",["inputs" => $inputs]) @endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Proyecto: @endcomponent
				@php
					$optionProject = [];
					if(isset($project_request))
					{
						$projs = App\Project::whereIn('status',[1,2])
							->whereIn('idproyect', $project_request)
							->get();
						foreach ($projs as $proj)
						{
							$optionProject[] = ['value' => $proj->idproyect, 'selected'=>'selected', 'description' => $proj->proyectName]; 
						}
					}
					$attributeEx	= "title=\"Proyecto\" name=\"project_request[]\"";
					$classEx		= "js-project";
				@endphp
				@component('components.inputs.select', 
					[
						'options'     => $optionProject, 
						'attributeEx' => $attributeEx, 
						'classEx'     => $classEx
					])
				@endcomponent
			</div>  
			<div class="col-span-2">
				@component('components.labels.label') Estado de Solicitud: @endcomponent
				@php
					$options = collect();
					foreach(App\StatusRequest::whereIn('idrequestStatus',[2,3,4,5,6,7,17,27,25])->orderBy('description','asc')->get() as $s)
					{
						$description = $s->description;
						if(isset($status) && in_array($s->idrequestStatus, $status))
						{
							$options = $options->concat([['value'=>$s->idrequestStatus, 'selected'=>'selected', 'description'=>$description]]);
						}
						else
						{
							$options = $options->concat([['value'=>$s->idrequestStatus, 'description'=>$description]]);
						}
					}
					$attributeEx	= "title=\"Estado de Solicitud\" name=\"status[]\" multiple=\"multiple\"";
					$classEx		= "js-status";
				@endphp
				@component('components.inputs.select', 
					[
						'options'     => $options, 
						'attributeEx' => $attributeEx, 
						'classEx'     => $classEx
					])
				@endcomponent
			</div>  
			<div class="col-span-2">
				@component('components.labels.label') Código WBS: @endcomponent
				@php
					$optionsWbs = [];
					if(isset($project_request) && isset($wbs))
					{
						$codes = App\CatCodeWBS::whereIn('project_id',$project_request)
							->whereIn('id',$wbs)
							->get();
						foreach($codes as $code)
						{
							$optionsWbs[] = ['value' => $code->id, 'selected'=>'selected', 'description' => $code->code_wbs ];
						}
					}
					$attributeEx	= "title=\"Código WBS\" name=\"wbs[]\"";
					$classEx		= "js-wbs";
				@endphp
				@component('components.inputs.select', 
					[
						'options'     => $optionsWbs, 
						'attributeEx' => $attributeEx, 
						'classEx'     => $classEx
					])
				@endcomponent
			</div>  
			<div class="col-span-2">
				@component('components.labels.label') Código EDT: @endcomponent
				@php
					$optionsEDT = [];
					if(isset($wbs) && isset($edt))
					{
						$codes = App\CatCodeEDT::whereIn('codewbs_id',$wbs)
							->whereIn('id',$edt)
							->get();
						foreach($codes as $code)
						{
							$optionsEDT[] = [ 'value' => $code->id, 'selected' => 'selected', 'description' => $code->code.' ('.$code->description.')'];
						}
					}
					$attributeEx	= "title=\"Código EDT\" name=\"edt[]\"";
					$classEx		= "js-code_edt";
				@endphp
				@component('components.inputs.select', 
					[
						'options'     => $optionsEDT, 
						'attributeEx' => $attributeEx, 
						'classEx'     => $classEx
					])
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Categoría: @endcomponent
				@php
					$options = collect();
					foreach(App\CatWarehouseType::all() as $cat)
					{
						$description = $cat->description;
						if(isset($category) && in_array($cat->id,$category))
						{
							$options = $options->concat([['value'=>$cat->id, 'selected'=>'selected', 'description'=>$description]]);
						}
						else
						{
							$options = $options->concat([['value'=>$cat->id, 'description'=>$description]]);
						}
					}
					$attributeEx	= "title=\"Categoría\" name=\"category[]\"";
					$classEx		= "js-category";
				@endphp
				@component('components.inputs.select', 
					[
						'options'     => $options, 
						'attributeEx' => $attributeEx, 
						'classEx'     => $classEx
					])
				@endcomponent
			</div>  
			<div class="col-span-2">
				@component('components.labels.label') Tipo de requisición: @endcomponent
				@php
					$options = collect();
					foreach(App\RequisitionType::where('status',1)->get() as $rt)
					{
						$description = $rt->name;
						if(isset($type) && in_array($rt->id,$type))
						{
							$options = $options->concat([['value'=>$rt->id, 'selected'=>'selected', 'description'=>$description]]);
						}
						else
						{
							$options = $options->concat([['value'=>$rt->id, 'description'=>$description]]);
						}
					}
					$attributeEx	= "title=\"Tipo de requisición\" name=\"type[]\"";
					$classEx		= "js-requisition-type";
				@endphp
				@component('components.inputs.select', 
					[
						'options'     => $options, 
						'attributeEx' => $attributeEx, 
						'classEx'     => $classEx
					])
				@endcomponent
			</div>  
		@endslot
		@if(count($requests) > 0)
			@slot('export')
				<div class="flex flex-row justify-end">
					@component('components.labels.label')
						@component('components.buttons.button',["variant" => "success"])
							@slot('attributeEx')
								type="submit"
								formaction="{{ route('requisition.export.seguimiento') }}"
							@endslot
							@slot('label')
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
			$body 		= [];
			$modelBody	= [];
			$modelHead	= 
			[
				[
					["value" => "Folio"],
					["value" => "No."],
					["value" => "Tipo"],
					["value" => "Título"],
					["value" => "Proyecto"],
					["value" => "Solicitante"],
					["value" => "Estado"],
					["value" => "Fecha de elaboración"],
					["value" => "Acción"]
				]
			];
			if(isset($requests))
			{
				foreach($requests as $request)
				{
					$solicitante = "";
					if($request->requisition()->exists() && $request->requisition->request_requisition != "")
					{
						$solicitante = $request->requisition()->exists() ? $request->requisition->request_requisition : 'Sin solicitante';
					}
					else
					{
						$solicitante = $request->requestUser()->exists() ? $request->requestUser->fullName() : 'Sin solicitante';
					}

					$body = 
					[
						[
							"content" => 
							[
								"label" => $request->folio
							]
						],
						[ 
							"content" => 
							[ 
								"label" => $request->requisition()->exists() ? $request->requisition->number : "---"
							]
						],
						[
							"content" => 
							[ 
								"label" => $request->requisition()->exists() ? $request->requisition->typeRequisition->name : "---"
							]
						],
						[
							"content" => 
							[
								"label" => $request->title != "" ? htmlentities($request->title) : 'Sin Título'
							]
						],
						[
							"content" => 
							[
								"label" => $request->requestProject()->exists() ? $request->requestProject->proyectName : 'Sin Proyecto'
							]
						],
						[
							"content" => 
							[
								"label" => $solicitante
							]
						],
						[
							"content" => 
							[
								"label" => $request->statusRequest->description
							]
						],
						[
							"content" => 
							[
								"label" => Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$request->fDate)->format('d-m-Y')
							]
						]
					];
					$actions =
					[
						"content" => 
						[
						]
					];
					if(in_array($request->status,[5,6,7,10,11,13,3,4,12,17,28]))
					{
						array_push($actions["content"], 
							[
								"kind"          => "components.buttons.button",
								"buttonElement" => "a",
								"variant"       => "secondary",
								"label"         => "<span class=\"icon-search\"></span>",
								"classEx"       => "load-actioner",
								"attributeEx"   => "alt=\"Ver Solicitud\" title=\"Ver Solicitud\" href=\"".route('requisition.edit',$request->folio)."\""
							]
						);
					}
					else
					{
						array_push($actions["content"], 
							[
								"kind"          => "components.buttons.button",
								"buttonElement" => "a",
								"variant"       => "success",
								"label"         => "<span class=\"icon-pencil\"></span>",
								"classEx"       => "load-actioner",
								"attributeEx"   => "alt=\"Editar Solicitud\" title=\"Editar Solicitud\" href=\"".route('requisition.edit',$request->folio)."\""
							]
						);
					}
					if($request->status==2)
					{
						array_push($actions["content"], 
							[
								"kind"          => "components.buttons.button",
								"buttonElement" => "a",
								"variant"       => "red",
								"label"         => "<span class=\"icon-bin\"></span>",
								"classEx"       => "delete-requisition",
								"attributeEx"   => "alt=\"Eliminar Solicitud\" title=\"Eliminar Solicitud\" href=\"".route('requisition.delete',$request->folio)."\""
							]
						);
					}
					if(in_array($request->status,[5,17,7,6,28]))
					{
						array_push($actions["content"], 
							[
								"kind"			=> "components.buttons.button",
								"buttonElement"	=> "a",
								"variant"		=> "warning",
								"label"			=> "<span class=\"icon-plus\"></span>",
								"classEx"       => "load-actioner",
								"attributeEx"	=> "alt=\"Nueva Solicitud\" title=\"Nueva Solicitud\" href=\"".route('requisition.create.new',$request->folio)."\""
							]
						);
					}
					if(in_array($request->status,[2,3,4,5,27]))
					{
						array_push($actions["content"], 
							[
								"kind" 			=> "components.buttons.button",
								"buttonElement"	=> "a",
								"variant"		=> "warning",
								"label"			=> "<i class=\"fas fa-ban\"></i>",
								"classEx"		=> "cancel-requisition",
								"attributeEx"	=> "alt=\"Cancelar Solicitud\" title=\"Cancelar Solicitud\" href=\"".route('requisition.cancel',$request->folio)."\""
							]
						);
					}
					array_push($body, $actions);
					array_push($modelBody, $body);
				}
			}
		@endphp
		@component('components.tables.table',[
			"modelHead" 			=> $modelHead,
			"modelBody" 			=> $modelBody
		])
			@slot('classExBody')
				request-validate
			@endslot
		@endcomponent
		{{ $requests->appends($_GET)->links() }}
	@else
		@component('components.labels.not-found') @endcomponent
	@endif
@endsection

@section('scripts')
	<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
	<script src="{{ asset('js/jquery-ui.js') }}"></script>
	<script src="{{ asset('js/datepicker.js') }}"></script>
	<script src="{{ asset('js/jquery.numeric.js') }}"></script>
	<script type="text/javascript"> 
		$(document).ready(function()
		{
			@php
				$selects = collect([
					[
						"identificator" => ".js-status", 
						"placeholder"   => "Seleccione un estado", 
						"language"      => "es"
					],
					[
						"identificator" => ".js-requisition-type", 
						"placeholder"   => "Seleccione el tipo de requisición", 
						"language"      => "es"
					],
					[
						"identificator" => ".js-category", 
						"placeholder"   => "Seleccione la categoría",
						"language"      => "es"
					]
				]);
			@endphp
			@component("components.scripts.selects",["selects" => $selects])@endcomponent
			generalSelect({'selector': '.js-users', 'model': 13, 'maxSelection' : -1});
			generalSelect({'selector': '.js-project', 'model': 17, 'option_id': 230, 'maxSelection' : -1 });
			proyects = [];
			$('.js-project option:selected').each(function()
			{
				proyects.push($(this).val());
			});
			generalSelect({'selector': '.js-wbs', 'extra': proyects, 'model': 51, 'maxSelection' : -1});
			wbs = [];
			$('.js-wbs option:selected').each(function()
			{
				wbs.push($(this).val());
			});
			generalSelect({'selector': '.js-code_edt', 'extra': wbs, 'model': 52, 'maxSelection' : -1});
			$(function() 
			{
				$(".datepicker").datepicker({ maxDate: 0, dateFormat: "dd-mm-yy" });
			});

			$(document).on('click','.delete-requisition',function(e)
			{
				e.preventDefault();
				attr = $(this).attr('href');
				swal({
					title		: "",
					text		: "Confirme que desea eliminar la requisición",
					icon		: "warning",
					buttons		:
					{
						cancel:
						{
							text		: "Cancelar",
							value		: null,
							visible		: true,
							closeModal	: true,
						},
						confirm:
						{
							text		: "Eliminar",
							value		: true,
							closeModal	: false
						}
					},
					dangerMode	: true,
				})
				.then((a) => {
					if (a)
					{
						window.location.href=attr;
					}
				});
			})
			$(document).on('click','.cancel-requisition',function(e)
			{
				e.preventDefault();
				attr = $(this).attr('href');
				swal({
					title		: "",
					text		: "Confirme que desea cancelar la requisición",
					icon		: "warning",
					buttons		:
					{
						cancel:
						{
							text		: "Salir",
							value		: null,
							visible		: true,
							closeModal	: true,
						},
						confirm:
						{
							text		: "Cancelar Solicitud",
							value		: true,
							closeModal	: false
						}
					},
					dangerMode	: true,
				})
				.then((a) => {
					if (a)
					{
						window.location.href=attr;
					}
				});
			})
			.on('change','[name="project_request[]"]',function()
			{
				$('.js-code_edt option').remove();
				$('.js-wbs option').remove();
				idproject = $('[name="project_request[]"]').val();
				if(idproject != "" && idproject != undefined)
				{
					proyects = [];
					$('.js-project option:selected').each(function()
					{
						proyects.push($(this).val());
					});
					generalSelect({'selector': '.js-wbs', 'extra': proyects, 'model': 51, 'maxSelection' : -1});
				}
				else
				{
					$('.js-wbs').val(null).trigger('change');
					$('.js-code_edt').val(null).trigger('change');
				}
			})
			.on('change','[name="wbs[]"]',function()
			{
				idcode_wbs = $('[name="wbs[]"]').val();			
				$('.js-code_edt option').remove();
				if(idcode_wbs != "" && idcode_wbs != undefined)
				{
					wbs = [];
					$('.js-wbs option:selected').each(function()
					{
						wbs.push($(this).val());
					});
					generalSelect({'selector': '.js-code_edt', 'extra': wbs, 'model': 52, 'maxSelection' : -1});
				}
				else
				{
					$('.js-code_edt').val(null).trigger('change');
				}
			});
		});
	</script>
@endsection
