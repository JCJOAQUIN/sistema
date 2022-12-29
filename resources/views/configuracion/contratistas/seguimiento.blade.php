@extends('layouts.child_module')
@section('data')		
	@component("components.forms.form",["attributeEx" => "method=\"GET\" action=\"".route('contractor.follow')."\" id=\"formsearch\""])
	@component("components.labels.title-divisor") BUSCAR CONTRATISTAS @endcomponent
		@component("components.containers.container-form")
			<div class="col-span-2">
				@component("components.labels.label") Nombre del contratista @endcomponent
				@component("components.inputs.input-text")
					@slot("attributeEx")
						type="text" name="contractor_name" value="{{ isset($contractor_name) ? $contractor_name : '' }}" placeholder="Ingrese el nombre del Contratista"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Estado: @endcomponent
				@php
				
					$options = collect();
					$values = ["0" => "En trámite", "1" => "Contrato sin firmar", "2" => "En conciliación", "3" => "Contratado", "4" => "Deshabilitado"];
					foreach($values as $key => $item)
					{
						if($contractor_status != null && $contractor_status == $key)
						{
							$options = $options->concat([["value" => $key, "description" => $item, "selected" => "selected"]]);
						}
						else
						{
							$options = $options->concat([["value" => $key, "description" => $item]]);
						}
					}
				@endphp
				@component("components.inputs.select", ["classEx" => "js-status", "options" => $options])
					@slot("attributeEx")
						name="contractor_status"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2 md:col-span-4 space-x-2 text-center md:text-left flex">
				@component("components.buttons.button-search", ["attributeEx" => $attributeExButtonSearch??'', "classEx" => $classExButtonSearch??'']) @endcomponent
				@component("components.buttons.button", ["buttonElement" => "a", "variant" => "reset", "classEx" => "bg-gray-200 px-7 py-2 rounded cursor-pointer hover:bg-gray-200 uppercase font-bold text-sm h-9 text-blue-gray-700", "attributeEx" => "href=\"".strtok($_SERVER['REQUEST_URI'], '?')."\""])Borrar campos @endcomponent
			</div>
		@endcomponent
	@endcomponent
	@if (count($contractors)>0)
	
			@php
				$modelHead = 
				[
					[
						["value" => "ID"],
						["value" => "Nombre del contratista"],
						["value" => "Estado"],
						["value" => "Acciones"]
					]
				];

				foreach($contractors as $contractor)
				{
					switch ($contractor->status)
					{
						case '0':
							$statusContractor	=	"En trámite";
							break;
						case '1':
							$statusContractor	=	"Contrato sin firmar";
							break;
						case '2':
							$statusContractor	=	"En conciliación";
							break;
						case '3':
							$statusContractor	=	"Contratado";
							break;
						default:
							$statusContractor	=	"";
							break;
					}
					if ($contractor->deleted_at == "")
					{
						$status = 
						[
							"content" =>
							[
								[
									"label" => $statusContractor
								]
							]
						];
					}
					else
					{
						$status = 
						[
							"content" =>
							[
								[
									"label" => "Contratista Deshabilitado"
								]
							]
						];
					}
					if ($contractor->deleted_at !="")
					{
						$buttons = 
						[
							"content" =>
							[
								[
									"kind"	=> "components.buttons.button",
									"label" => "<span class=\"icon-search\"></span>",
									"buttonElement" => "a",
									"attributeEx"	=> "title=\"Ver informacion del Contratista\" href=\"".route('contractor.edit',$contractor->id)."\"",
									"classEx"		=> "showContractorDisabled",
									"variant"		=> "secondary"
								],
								[
									"kind"	=> "components.buttons.button",
									"label" => "<span class=\"icon-user-check\"></span>",
									"buttonElement" => "a",
									"attributeEx"	=> "title=\"Habilitar Contratista\" href=\"".route('contractor.reactive',$contractor->id)."\"",
									"classEx"		=> "reactiveContractor",
									"variant"		=> "secondary"
								]
							]
						];
					}
					else
					{
						$buttons = 
						[
							"content" =>
							[
								[
									"kind"	=> "components.buttons.button",
									"label" => "<span class=\"icon-pencil\"></span>",
									"buttonElement" => "a",
									"attributeEx"	=> "title=\"Editar Contratista\" href=\"".route('contractor.edit',$contractor->id)."\"",
									"classEx"		=> "showContractorDisabled",
									"variant"		=> "success"
								],
								[
									"kind"	=> "components.buttons.button",
									"label" => "<span class=\"icon-user-minus\"></span>",
									"buttonElement" => "a",
									"attributeEx"	=> "title=\"Deshabilitar Contratista\" href=\"".route('contractor.inactive',$contractor->id)."\"",
									"classEx"		=> "deleteContractor",
									"variant"		=> "red"
								]
							]
						];
					}
					$body = 
					[
						[
							"content" 	=>
							[
								[
									"label" => $contractor->id
								]
							]
						],
						[
							"content" 	=>
							[
								[
									"label" => htmlentities($contractor->name),
								]
							]
						],
						$status,
						$buttons
					];
					$modelBody[] = $body;
				}
			@endphp			
			@component("components.tables.table",
			[
				"modelHead" => $modelHead,
				"modelBody" => $modelBody,
			])
			@endcomponent
	<div class="invisible"></div>
	{{ $contractors->appends($_GET)->links() }}
	@else
		@component("components.labels.not-found") @endcomponent
	@endif
@endsection
@section('scripts')
<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
<script src="{{ asset('js/jquery-ui.js') }}"></script>
<script type="text/javascript">
	$(document).ready(function()
	{
		@php
			$selects = collect([
				[
					"identificator"			=> ".js-status",
					"placeholder"			=> "Seleccione el estado",
					"languaje"				=> "es",
					"maximumSelectionLength"=> "1",
				]
			]);
		@endphp
		@component("components.scripts.selects",["selects" => $selects]) @endcomponent
	});
	$(document).on('click','.noEdit',function()
	{
		swal('','Para editar la informacion del contratista primero debe de habilitarlo','info');
	})
	$(document).on('click','.deleteContractor',function(e)
	{
		e.preventDefault();
		url = $(this).attr('href');
		swal(
		{
			title		:	"Deshabilitar contratista",
			text		:	"Confirme que desea deshabilitar el contratista seleccionado.",
			icon		:	"warning",
			buttons		: 	["Cancelar", "Deshabilitar"],
			dangerMode	:	true,
		})
		.then((isConfirm)	=>
		{
			if (isConfirm)
			{
				swal('Cargando',{
					icon: '{{ asset(getenv('LOADING_IMG')) }}',
					button: false,
					closeOnClickOutside: false,
					closeOnEsc: false
				});
				form = $('<form></form>').attr('action',url).attr('method','post').append('@csrf').append('@method("post")');
				$(document.body).append(form);
				form.submit();
			}
		});
	});
	$(document).on('click','.reactiveContractor',function(e)
	{
		e.preventDefault();
		url = $(this).attr('href');
		
		swal(
		{
			title		:	"Habilitar contratista",
			text		:	"Confirme que desea habilitar el contratista selecccionado.",
			icon		:	"warning",
			buttons		: 	["Cancelar", "Reactivar"],
			dangerMode	:	true,
		})
		.then((isConfirm)	=>
		{
			if (isConfirm)
			{
				swal('Cargando',{
					icon: '{{ asset(getenv('LOADING_IMG')) }}',
					button: false,
					closeOnClickOutside: false,
					closeOnEsc: false
				});
				form = $('<form></form>').attr('action',url).attr('method','post').append('@csrf').append('@method("post")');
				$(document.body).append(form);
				form.submit();
			}
		});
	});
</script>
@endsection