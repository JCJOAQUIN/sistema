@extends('layouts.child_module')
@section('data')
	@component("components.forms.form",["attributeEx" => "action=\"".route('account-concentrated.search')."\" method=\"GET\" id "])
		@component("components.labels.title-divisor") BUSCAR REPORTE @endcomponent
		@component("components.containers.container-form")
			<div class="col-span-2">
				@component("components.labels.label") Nombre de agrupación: @endcomponent
				@component("components.inputs.input-text")
					@slot("attributeEx")
						type="text"
						name="name"
						value="{{ isset($name) ? $name : '' }}"
						id="input-search"
						placeholder="Ingrese el nombre"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Empresa: @endcomponent
				@php
					$options = collect();
					foreach(App\Enterprise::orderName()->get() as $ent)
					{
						$description = $ent->name;
						if(isset($enterprise_id) && in_array($ent->id, $enterprise_id))
						{
							$options = $options->concat([["value" => $ent->id, "selected" => "selected", "description" => $description]]);
						}
						else
						{
							$options = $options->concat([["value" => $ent->id, "description" => $description]]);
						}
					}
					$attributeEx = "name=\"enterprise_id[]\" multiple=\"multiple\"";
					$classEx = "js-enterprises form-control";
				@endphp
				@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])@endcomponent
			</div>
			<div class="col-span-2 md:col-span-4 space-x-2 text-center md:text-left flex">
				@component("components.buttons.button-search", ["attributeEx" => $attributeExButtonSearch??'', "classEx" => $classExButtonSearch??'']) @endcomponent
				@component("components.buttons.button", ["buttonElement" => "a", "variant" => "reset", "classEx" => "bg-gray-200 px-7 py-2 rounded cursor-pointer hover:bg-gray-200 uppercase font-bold text-sm h-9 text-blue-gray-700", "attributeEx" => "href=\"".strtok($_SERVER['REQUEST_URI'], '?')."\""])Borrar campos @endcomponent
			</div>
		@endcomponent
	@endcomponent
	@if (count($groups) > 0)
		@php
			$body = [];
			$modelBody = [];
			$modelHead = 
			[
				[
					["value"	=> "Nombre"],
					["value"	=> "Empresa"],
					["value"	=> "Acción"]
				]
			];

			foreach($groups as $g)
			{
				$body = 
				[
					"classEx" => "tr",
					[
						"content" =>
						[
							[
								"label" => htmlentities($g->name),
							]
						]
					],
					[
						"content" =>
						[
							[
								"label" => $g->enterprise->name,
							]
						]
					],
					[
						"content" =>
						[
							[
								"kind"			=> "components.buttons.button",
								"variant" 		=> "success",
								"buttonElement" => "a",
								"label" 		=> "<span class=\"icon-pencil\"></span>",
								"attributeEx" 	=> "href=\"".route('account-concentrated.edit',$g->id)."\" alt=\"Editar\" title=\"Editar\""
							],
							[
								"kind" 			=> "components.buttons.button",
								"variant" 		=> "dark-red",
								"buttonElement" => "a",
								"classEx" 		=> "groupingAccount-delete",
								"label" 		=> "<span class=\"icon-bin\"></span>",
								"attributeEx" 	=> "href=\"".route('account-concentrated.delete',$g->id)."\" alt=\"Eliminar\" title=\"Eliminar\""
							]
						]
					]

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
		{{ $groups->appends($_GET)->links() }}
	@else
		@component("components.labels.not-found")@endcomponent
	@endif
@endsection
@section('scripts')
<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
<script src="{{ asset('js/jquery-ui.js') }}"></script>
<script src="{{ asset('js/jquery.numeric.js') }}"></script>
<script type="text/javascript">
	$(document).ready(function() {
		@php
			$selects = collect([
				[
					"identificator"          => ".js-enterprises", 
					"placeholder"            => "Seleccione la empresa",
				]
			]);
		@endphp
		@component("components.scripts.selects",["selects" => $selects])@endcomponent
		$(document).on('click','.groupingAccount-delete',function(e){
			e.preventDefault();
			url = $(this).attr('href');
			swal({
				title		: "",
				text		: "Confirme que desea eliminar la Partida",
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
			}).then((a) => {
				if (a)
				{
					form = $('<form></form>').attr('action',url).attr('method','post').append('@csrf').append('@method("delete")');
					$(document.body).append(form);
					form.submit();
				}
			});
		});
	});
</script>
@endsection
