@extends('layouts.child_module')

@section('data')
	@component("components.forms.form", ["attributeEx" => "action=\"".route('wbs.search')."\" method=\"GET\" id=\"formsearch\""])
	@component("components.labels.title-divisor") BUSCAR WBS @endcomponent
		@component("components.containers.container-form")
			<div class="col-span-2">
				@component("components.labels.label") Código: @endcomponent
				@php
					isset($code) ? $code = $code : $code = "";
					isset($namewbs) ? $namewbs = $namewbs : $namewbs = "";
				@endphp
				@component("components.inputs.input-text", ["attributeEx" => "type=\"text\" name=\"code\" value=\"".$code."\" placeholder=\"Ingrese el código\""]) @endcomponent
			</div>
			
			<div class="col-span-2">
				@component("components.labels.label") Nombre: @endcomponent
				@component("components.inputs.input-text", ["attributeEx" => "type=\"text\" name=\"name\" value=\"".$namewbs."\" placeholder=\"Ingrese el nombre\""]) @endcomponent
			</div>

			<div class="col-span-2 md:col-span-4 space-x-2 text-center md:text-left flex">
				@component("components.buttons.button-search", ["variant" => "warning", "attributeEx" => "type=\"submit\"", "label" => "<span class=\"icon-search\"></span> Buscar"]) @endcomponent
				@component("components.buttons.button", ["buttonElement" => "a", "variant" => "reset", "classEx" => "bg-gray-200 px-7 py-2 rounded cursor-pointer hover:bg-gray-200 uppercase font-bold text-sm h-9 text-blue-gray-700", "attributeEx" => "href=\"".strtok($_SERVER['REQUEST_URI'], '?')."\""])Borrar campos @endcomponent
			</div>
		@endcomponent
	@endcomponent

	
	@if(count($projects) > 0)
		@php 
			$modelHead = [[["value" => "ID"], ["value" => "Código"], ["value" => "Nombre"], ["value" => "Proyecto"], ["value" => "Estado"], ["value" => "Acción"]]];
			$modelBody = [];

			foreach($projects as $project)
			{
				if($project->status == 1) 
				{
					$status = "Activo"; 
				}
				else if($project->status == 0) 
				{
					$status = "No Activo"; 
				}

				$body =
				[
					[
						"content" => 
						[
							["kind" => "components.labels.label", "label" => $project->id]
						]		
					],
					[
						"content" => 
						[
							["kind" => "components.labels.label", "label" => $project->code]
						]	
					],
					[
						"content" => 
						[
							["kind" => "components.labels.label", "label" => htmlentities($project->code_wbs)]
						]	
					],
					[
						"content" => 
						[
							["kind" => "components.labels.label", "label" => $project->projectData()->exists() ? htmlentities($project->projectData->proyectName) : "Sin Proyecto"]
						]	
					],
					[
						"content" => 
						[
							["kind" => "components.labels.label", "label" => $status]
						]	
					]		
				];
				switch($project->status)
				{
					case(1):
						$buttons = 
						[
							["kind" => "components.buttons.button","variant" => "success", "buttonElement" => "a", "attributeEx" => "href=\"".route('wbs.edit',$project->id)."\" alt=\"Editar\" title=\"Editar\"", "label" => "<span class=\"icon-pencil\"></span>"],
							["kind" => "components.buttons.button","variant" => "red", "buttonElement" => "a", "attributeEx" => "type=\"button\" href=\"".route('wbs.destroy',["id" => $project->id])."\" alt=\"Desactivar\" title=\"Desactivar\"", "classEx" => "down", "label" => "<span class=\"icon-blocked\"></span>"],

						];
						break;
					case(0):
						$buttons = 
						[
							["kind" => "components.buttons.button","variant" => "secondary", "buttonElement" => "a", "attributeEx" => "href=\"".route('wbs.edit',$project->id)."\" alt=\"Ver\" title=\"Ver\"", "label" => "<span class=\"icon-search\"></span>"],
							["kind" => "components.buttons.button","variant" => "success", "buttonElement" => "a", "attributeEx" => "href=\"".route('wbs.up',$project->id)."\" alt=\"Activar\" title=\"Activar\"", "label" => "<span class=\"icon-check\"></span>"],		
						];
						break;
				}
				array_push($body, ["content" => $buttons]);
				$modelBody[] = $body;
			}
		@endphp
		@Table(["modelHead" => $modelHead, "modelBody" => $modelBody]) @endTable
		{{$projects->appends($_GET)->links()}}
	@else
		@component("components.labels.not-found") RESULTADO NO ENCONTRADO @endcomponent
	@endif
@endsection

@section('scripts')
	<script type="text/javascript">
		$(document).ready(function()
		{
			$(document).on('click','.down',function(e)
			{
				e.preventDefault();
				swal({
					title     : "Confirmación",
					text      : "¿Confirma que desea desactivar el WBS?",
					icon      : "warning",
					buttons   : true,
					dangerMode: true,
				})
				.then((willDelete) =>
				{
					if (willDelete)
					{
						url = $(this).attr('href');
						form = $('<form></form>').attr('method','POST').attr('action',url);
						form.append('@csrf');
						form.append('@method("DELETE")');
						$('body').append(form);
						form.submit();
					}
				});
			});
		});
	</script>
@endsection
