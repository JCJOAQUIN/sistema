@extends('layouts.child_module')

@section('data')
	<div id="container-cambio" class="div-search">
		@component('components.labels.title-divisor') Buscar Solicitudes @endcomponent
		@php
			$values = ['folio' => isset($folio) ? $folio : ""];
			$hidden = ['enterprise', 'rangeDate', 'name'];
		@endphp  
		@component('components.forms.searchForm', ["attributeEx" => "id=\"formsearch\"", "values" => $values, "hidden" => $hidden])
			@slot('contentEx')
				<div class="col-span-2">
					@component('components.labels.label') Solicitante: @endcomponent
					@php
						$options = collect();
						if(isset($user_request))
						{
							$usersSelected = App\User::whereIn('id', $user_request)->orderBy('name','asc')->orderBy('last_name','asc')->orderBy('scnd_last_name','asc')->get();
							foreach($usersSelected as $user)
							{
								$options = $options->concat([['value' => $user->id, 'selected' => 'selected', 'description' => $user->fullName()]]);
							}
						}						
						$attributeEx = "name=\"user_request[]\" multiple=\"multiple\"";
						$classEx = "js-users";
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
					@component('components.labels.label') Número (No.): @endcomponent
					@isset($number)
						@component('components.inputs.input-text', 
							[
								'attributeEx' => "name=\"number\" placeholder=\"Ingrese un número de requisición\" value=\"".$number. "\""
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
					@isset($title_request)
						@component('components.inputs.input-text', 
							[
								'attributeEx' => "name=\"title_request\" placeholder=\"Ingrese un título\" value=\"". isset($title_request) ? htmlentities($title_request) : ''. "\""
							])
						@endcomponent
					@else
						@component('components.inputs.input-text', 
							[
								'attributeEx' => "name=\"title_request\" placeholder=\"Ingrese un título\""
							])
						@endcomponent
					@endif
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Nombre del personal: @endcomponent
					@component('components.inputs.input-text', [
						'attributeEx' => "name=\"employee\" id=\"input-search\" placeholder=\"Ingrese un nombre\"".(isset($employee) ? " value=\"".$employee."\"" : "")
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
						$options = collect();
						if(isset($project_request) && $project_request != "")
						{
							$projectsSelected = App\Project::whereIn('idproyect',$project_request)->orderBy('proyectName','asc')->get();
							foreach($projectsSelected as $proj)
							{
								$options = $options->concat([['value' => $proj->idproyect, 'selected' => 'selected', 'description' => $proj->proyectName]]);
							}
						}
						$attributeEx = "title=\"Proyecto\" name=\"project_request[]\"";
						$classEx = "js-project";
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
						$options = collect();
						if(isset($project_request) && isset($wbs))
						{
							$wbsSelected = App\CatCodeWBS::whereIn('id', $wbs)->orderBy('code_wbs','asc')->get();
							foreach($wbsSelected as $code)
							{
								$options = $options->concat([['value' => $code->id, 'selected' => 'selected', 'description' => $code->code_wbs]]);
							}
						}
						$attributeEx = "title=\"Código WBS\" name=\"wbs[]\"";
						$classEx = "js-wbs";
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
					@component('components.labels.label') Código EDT: @endcomponent
					@php
						$options = collect();
						if(isset($wbs) && isset($edt))
						{
							$edtSelected = App\CatCodeEDT::whereIn('id',$edt)->get();
							foreach($edtSelected as $code_edt)
							{
								$options = $options->concat([['value' => $code_edt->id, 'selected' => 'selected', 'description' => $code_edt->code.' ('.$code_edt->description.')']]);
							}
						}
						$attributeEx = "title=\"Código EDT\" name=\"edt[]\"";
						$classEx = "js-edt";
						$attributeEx = "title=\"Código EDT\" name=\"edt[]\"";
						$classEx = "js-edt";
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
						$attributeEx = "title=\"Tipo de requisición\" name=\"type[]\"";
						$classEx = "js-requisition-type";
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
					@component('components.labels.label') Historial: @endcomponent
					@php
						$options = collect([
							["value" => "0", "selected" => (isset($status) && $status == 0 ? "selected" : (!isset($status) ? "selected" : "")), "description" => "Por Revisar"],
							["value" => "1", "selected" => (isset($status) && $status == 1 ? "selected" : ""), "description" => "Revisadas"]
						]);
						$attributeEx = "title=\"Revisiones\" name=\"status\"";
						$classEx = "js-status";
					@endphp
					@component('components.inputs.select', ['options'     => $options, 'attributeEx' => $attributeEx, 'classEx'     => $classEx]) @endcomponent
				</div>  
			@endslot
			@if(count($requests) > 0)
				@slot('export')
					<div class="flex flex-row justify-end">
						@component('components.labels.label')
							@component('components.buttons.button',["variant" => "success"])
								@slot('attributeEx')
									type="submit"
									formaction="{{ route('requisition.export.revision') }}"
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
	</div>
	@if(count($requests) > 0)
		@php
			$body 			= [];
			$modelBody		= [];
			$modelHead = 
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
								"label" => Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $request->fDate)->format('d-m-Y'),
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
								"kind"  => "components.buttons.button",
								"buttonElement" => "a",
								"variant" => "success",
								"label" => "<span class=\"icon-pencil\"></span>",
								"attributeEx" => "alt=\"Editar Solicitud\" title=\"Editar Solicitud\" href=\"".route('requisition.review.show',$request->folio)."\""
							]
						);
					}
					else
					{
						array_push($actions["content"], 
							[
								"kind"  => "components.buttons.button",
								"buttonElement" => "a",
								"variant" => "secondary",
								"label" => "<span class=\"icon-search\"></span>",
								"classEx" => "",
								"attributeEx" => "alt=\"Ver Solicitud\" title=\"Ver Solicitud\" href=\"".route('requisition.reviewedit',$request->folio)."\""
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
			"themeBody" 			=> "striped"
		])
			@slot('classEx')
				text-center
			@endslot
			@slot('classExBody')
				request-validate
			@endslot
		@endcomponent
		{{ $requests->appends($_GET)->links() }}
	@else
		@component('components.labels.not-found')@endcomponent
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
			$(function() 
			{
				$( ".datepicker" ).datepicker({ maxDate: 0, dateFormat: "dd-mm-yy" });
			});
			@php
				$selects = collect([
					[
						"identificator"         => ".js-requisition-type", 
						"placeholder"           => "Seleccione el tipo", 
					],
					[
						"identificator"         => ".js-status", 
						"placeholder"           => "Seleccione el historial",
						"maximumSelectionLength"=> "1" 
					],
				]);
			@endphp
			@component("components.scripts.selects",["selects"=>$selects]) @endcomponent
			generalSelect({'selector': '.js-project', 'model': 17, 'option_id':{{$option_id}}, 'maxSelection' : -1});
			proyects = [];
			$('.js-project option:selected').each(function()
			{
				proyects.push($(this).val());
			});
			generalSelect({'selector': '.js-wbs', 'depends': '.js-project', 'extra': proyects, 'model': 51, 'maxSelection' : -1});
			wbs = [];
			$('.js-wbs option:selected').each(function()
			{
				wbs.push($(this).val());
			});
			generalSelect({'selector': '.js-edt', 'depends': '.js-wbs', 'extra': wbs, 'model': 52, 'maxSelection' : -1});
			generalSelect({'selector': '.js-users', 'model': 13, 'maxSelection' : -1});

			$(document).on('change','[name="project_request[]"]',function()
			{
				$('.js-wbs option').remove();
				$('.js-edt option').remove();
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
					$('.js-edt').val(null).trigger('change');
				}
			})
			.on('change','[name="wbs[]"]',function()
			{
				idcode_wbs = $('[name="wbs[]"]').val();			
				$('.js-edt option').remove();

				if(idcode_wbs!= "" && idcode_wbs != undefined)
				{
					wbs = [];
					$('.js-wbs option:selected').each(function()
					{
						wbs.push($(this).val());
					});
					generalSelect({'selector': '.js-edt', 'extra': wbs, 'model': 52, 'maxSelection' : -1});
				}
				else
				{
					$('.js-edt').val(null).trigger('change');
				}
			});
		});
	</script>
@endsection