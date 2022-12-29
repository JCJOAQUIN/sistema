@extends('layouts.child_module')
@section('data')
	@component("components.forms.form", ["attributeEx" => "method=\"GET\" id=\"formsearch\""])
		@component('components.labels.title-divisor') BUSCAR PROYECTOS @endcomponent
		@component("components.containers.container-form")
			<div class="col-span-2">
				@component("components.labels.label")
					Nombre:
				@endcomponent

				@component("components.inputs.input-text")
					@slot('attributeEx')
						type = "text" 
						name = "name" 
						value = "{{ isset($name) ? $name : '' }}"
						placeholder = "Ingrese el nombre"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label")
					Número:
				@endcomponent
				@component("components.inputs.input-text")
					@slot('attributeEx')
						type = "text" 
						name = "number" 
						value = "{{ isset($number) ? $number : '' }}"
						placeholder = "Ingrese el número"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label")
					Código:
				@endcomponent
				@component("components.inputs.input-text")
					@slot('attributeEx')
						type = "text" 
						name = "code" 
						value = "{{ isset($code) ? $code : '' }}"
						placeholder = "Ingrese el código"	
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@php
					$optionStatus = collect();

					$value = 0;
					foreach (["Activo", "Pospuesto", "Cancelado", "Finalizado"] as $item)
					{
						$optionStatus = $optionStatus->concat(
						[
							[
								"value" => $value = $value + 1,
								"description" => $item,
								"selected" => ((isset($status) &&  $value == $status ) ? "selected" : "")
							]
						]);	
					}
				@endphp
				@component("components.labels.label")
					Estado:
				@endcomponent
				@component("components.inputs.select",["options" => $optionStatus,"attributeEx" => "name=\"status\" multiple=\"multiple\"", "classEx" =>  "js-status removeselect"]) @endcomponent
			</div>

			<div class="col-span-2 md:col-span-4 space-x-2 text-center md:text-left flex">
				@component("components.buttons.button-search", ["attributeEx" => $attributeExButtonSearch??'', "classEx" => $classExButtonSearch??'']) @endcomponent
				@component("components.buttons.button", ["buttonElement" => "a", "variant" => "reset", "classEx" => "bg-gray-200 px-7 py-2 rounded cursor-pointer hover:bg-gray-200 uppercase font-bold text-sm h-9 text-blue-gray-700", "attributeEx" => "href=\"".strtok($_SERVER['REQUEST_URI'], '?')."\""])Borrar campos @endcomponent
			</div>
		@endcomponent
		<div class="float-right">
			@component("components.buttons.button", ["classEx" => "export", "variant" => "success"])
				@slot("attributeEx")
					type = "submit"
					formaction = "{{ route('project.export') }}" 
					formmethod = "get"
				@endslot
				<span>Exportar a Excel</span>
				<span class="icon-file-excel"></span>
			@endcomponent
		</div>
	@endcomponent
	
	@if(count($projects) > 0)
		@php
			$modelHead = 
			[
				[
					["value" => "ID"],
					["value" => "Número"],
					["value" => "Nombre"], 
					["value" => "Código"], 
					["value" => "Estado"], 
					["value" => "Acción"]
				]
			];
			foreach($projects as $project)
			{
				switch($project->status)
				{
					case'1':
						$stat = "Activo";
						break;
					case'2':
						$stat = "Pospuesto";
						break;
					case'3':
						$stat = "Cancelado";
						break;
					case'4':
						$stat = "Finalizado";
						break;
				}

				$body = 
				[
					[
						"content"	=>
						[
							[
								"label" => $project->idproyect
							]
						]
					],
					[
						"content"	=>
						[
							[
								"label" => htmlentities($project->proyectNumber),
							]
						]
					],
					[
						"content" =>
						[
							[
								"label" => htmlentities($project->proyectName)
							]
						]
					],
					[
						"content" =>
						[
							[
								"label" => $project->projectCode != '' ? htmlentities($project->projectCode) : "---"
							]
						]
					],
					[
						"content" =>
						[
							[
								"label" => $stat
									
							]
						]
					]
				];

				if($project->status==1)
				{
					$buttons = 
					[
						[							
							"kind" 			=> "components.buttons.button",
							"attributeEx"	=> "title=\"Editar\" href=\"".route('project.edit',$project->idproyect)."\"",							
							"label"			=> "<span class=\"icon-pencil\"></span>",
							"variant"		=> "success",
							"buttonElement"	=> "a"
							
						],
						[	"kind" 			=> "components.buttons.button",
							"variant" 		=> "red", 
							"buttonElement" => "a", 
							"attributeEx"	=> "type=\"button\" href=\"".route('project.destroy',$project->idproyect)."\" alt=\"Concluir\" title=\"Concluir\"", 
							"classEx" 		=> "btn-inactive-project", 
							"label" 		=> "<span class=\"icon-cross\"></span>"
						]
					];
				}
				elseif ($project->status==0) 
				{
					$buttons = 
					[
						[							
							"kind" 			=> "components.buttons.button",
							"attributeEx"	=> "title=\"Editar\" href=\"".route('project.edit',$project->idproyect)."\"",							
							"label"			=> "<span class=\"icon-pencil\"></span>",
							"variant"		=> "success",
							"buttonElement"	=> "a"
							
						],
						[	"kind" 			=> "components.buttons.button",
							"variant" 		=> "secondary", 
							"buttonElement" => "a", 
							"attributeEx"	=> "href=\"".route('project.repair',$project->idproyect)."\" alt=\"Reactivar\" title=\"Reactivar\"", 
							"classEx" 		=> "btn-active-project", 
							"label" 		=> "<span class=\"icon-redo\"></span>"
						]
					];
				}
				else {
					$buttons = 
					[
						[							
							"kind" 			=> "components.buttons.button",
							"attributeEx"	=> "title=\"Editar\" href=\"".route('project.edit',$project->idproyect)."\"",							
							"label"			=> "<span class=\"icon-pencil\"></span>",
							"variant"		=> "success",
							"buttonElement"	=> "a"
							
						]
					];
				}
				
				array_push($body, ["content" => $buttons]);
				$modelBody[] = $body;
			}
		@endphp
		<div class="table-responsive">
			@component("components.tables.table",
			[
				"modelHead" => $modelHead,
				"modelBody" => $modelBody,
			])
			@endcomponent
		</div>

		{{ $projects->appends($_GET)->links() }}
	@else
		@component("components.labels.not-found", ["attributeEx" => "id=\"not-found\""]) RESULTADO NO ENCONTRADO @endcomponent
	@endif
@endsection

@section('scripts')
	<script>
		$(document).ready(function()
		{
			@php
				$selects = collect([
					[
						"identificator"          => ".js-status", 
						"placeholder"            => "Seleccione el estado", 
						"maximumSelectionLength" => "1"
					]
				]);
			@endphp
			@component("components.scripts.selects",["selects" => $selects])@endcomponent

			$(document).on('click','.btn-inactive-project',function(e)
			{
				e.preventDefault();
				url = $(this).attr('href');
				swal({
					title		: "",
					text		: "Confirme que desea concluir el proyecto",
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
							text		: "Concluir",
							value		: true,
							closeModal	: false
						}
					},
					dangerMode	: true,
				})
				.then((a) => {
					if (a)
					{
						e.preventDefault();
						url = $(this).attr('href');
						form = $('<form></form>').attr('action',url).attr('method','post').append('@csrf').append('@method("delete")');
						$(document.body).append(form);
						form.submit();
					}
				});
			})
			.on('click','.btn-active-project',function(e)
			{
				e.preventDefault();
				url = $(this).attr('href');
				swal({
					title		: "",
					text		: "Confirme que desea reactivar el proyecto",
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
							text		: "Reactivar",
							value		: true,
							closeModal	: false
						}
					},
					dangerMode	: true,
				})
				.then((a) => {
					if (a)
					{
						window.location.href=url;
					}
				});
			});
		});
	</script> 
@endsection
