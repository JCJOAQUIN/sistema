@extends('layouts.child_module')

@section('data')
	@component('components.labels.title-divisor') Buscar Solicitudes @endcomponent
	@php
		$values	= [	'folio' => isset($folio) ? $folio : "" ];
		$hidden = 
		[
			'enterprise',
			'rangeDate',
			'name'
		];
	@endphp  
	@component('components.forms.searchForm', ["attributeEx" => "id=\"formsearch\"", "values" => $values, "hidden" => $hidden])
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
				@php
					$valNumber = isset($number) ? $number : '';
				@endphp
				@component('components.inputs.input-text', 
					[
						'attributeEx' => "name=\"number\" placeholder=\"Ingrese un número de requisición\" value=\"".$valNumber."\""
					])
				@endcomponent
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
							->whereIn('idproyect',$project_request)
							->orderBy('proyectName','asc')
							->get();
						foreach($projs as $proj)
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
				@component('components.labels.label') Código WBS: @endcomponent
				@php
					$optionsWbs = [];
					if(isset($project_request) && isset($wbs))
					{
						$codes = App\CatCodeWBS::whereIn('project_id',$project_request)
							->whereIn('id',$wbs)
							->orderBy('code_wbs','asc')
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
			<div class="col-span-2">
				@component('components.labels.label') Tipo de requisición: @endcomponent
				@php
					$optionStatus	= [];
					$valueStatus	= ["0" => "Por Votar", "1" => "Autorizadas" ];

					foreach ($valueStatus as $key => $value)
					{
						$optionStatus[] = [
							"value"			=> $key,
							"description"	=> $value,
							"selected"		=> (isset($status) && $status == $key ? "selected" : "")
						];
 					}
					$attributeEx	= "title=\"Revisiones\" id=\"s\" name=\"status\"";
					$classEx		= "js-status";
				@endphp
				@component('components.inputs.select', 
					[
						'options'     => $optionStatus, 
						'attributeEx' => $attributeEx, 
						'classEx'     => $classEx
					])
				@endcomponent
			</div>  
		@endslot
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
					$solicitante = '';
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
								"label" => Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$request->fDate)->format('d-m-Y H:i')
							]
						]
					];
					$actions =
					[
						"content" => 
						[
						]
					];
					if(isset($status) && $status == 0)
					{
						array_push($actions["content"], 
							[
								"kind"          => "components.buttons.button",
								"buttonElement" => "a",
								"variant"       => "success",
								"label"         => "<span class=\"icon-pencil\"></span>",
								"classEx"       => "load-actioner",
								"attributeEx"   => "alt=\"Editar Solicitud\" title=\"Editar Solicitud\" href=\"".route('requisition.vote.show',$request->folio)."\""
							]
						);
					}
					else
					{
						array_push($actions["content"], 
							[
								"kind"          => "components.buttons.button",
								"buttonElement" => "a",
								"variant"       => "secondary",
								"label"         => "<span class=\"icon-search\"></span>",
								"classEx"       => "load-actioner",
								"attributeEx"   => "alt=\"Ver Solicitud\" title=\"Ver Solicitud\" href=\"".route('requisition.voteedit',$request->folio)."\""
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
			"modelBody" 			=> $modelBody,
			"classExBody"			=> "request-validate"
		])
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
						"identificator"				=> ".js-status", 
						"placeholder"				=> "Seleccione un estado",
						"maximumSelectionLength"	=> "1",
						"language"      			=> "es"
					],
					[
						"identificator"				=> ".js-requisition-type", 
						"placeholder"				=> "Seleccione el tipo de requisición",
						"language"					=> "es"
					]
				]);
			@endphp
			@component("components.scripts.selects",["selects" => $selects])@endcomponent
			generalSelect({'selector': '.js-users', 'model': 13, 'maxSelection' : -1 });
			generalSelect({'selector': '.js-project', 'model': 17, 'option_id': 232, 'maxSelection' : -1 });
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
				$( ".datepicker" ).datepicker({ maxDate: 0, dateFormat: "dd-mm-yy" });
			});

			$(document).on('change','[name="project_request[]"]',function()
			{
				$('.js-wbs option').remove();
				$('.js-code_edt option').remove();
				idproject = $('[name="project_request[]"]').val();

				if(idproject != undefined && idproject != "")
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
				if(idcode_wbs != undefined && idcode_wbs != "")
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