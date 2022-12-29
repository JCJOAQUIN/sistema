@extends('layouts.child_module')
@section('data')
	@component("components.forms.form", ["attributeEx" => "action=\"".route('area.search')."\" method=\"GET\" id=\"formsearch\""])
		@component("components.labels.title-divisor") BUSCAR DIRECCIÓN @endcomponent
		@component("components.containers.container-form")
			<div class="col-span-4 md:col-start-2 md:col-span-2 md:col-end-4">	
				@component("components.labels.label") Dirección: @endcomponent
				@component("components.inputs.input-text")
					@slot("attributeEx")
						type="text" 
						name="search" 
						id="input-search" 
						placeholder="Ingrese la dirección" 
						value="{{ isset($search) ? $search: ''}}"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-4 md:col-start-2 md:col-span-2 md:col-end-4 grid justify-items-center md:justify-items-start">
				@component("components.buttons.button-search", ["attributeEx" => $attributeExButtonSearch??'', "classEx" => $classExButtonSearch??'']) @endcomponent
			</div>
		@endcomponent
		@if ($countAreas > 0)
			<div class="flex flex-row justify-end">
				@component('components.labels.label')
					@component("components.buttons.button",['variant' => 'success'])
						@slot("attributeEx")
							type="submit"
							formaction="{{ route("area.export") }}"
						@endslot
						@slot("label")
							<span>Exportar a Excel</span><span class="icon-file-excel"></span>
						@endslot
					@endcomponent
				@endcomponent
			</div>
		@endif
	@endcomponent
	@if ($countAreas > 0)
		@php
			$modelHead = 
			[
				[
					["value" => "ID"], 
					["value" => "Nombre"], 
					["value" => "Acciones"]
				]
			];
			foreach($areas as $area)
			{
				if($area->status == 'ACTIVE') 
				{
					$buttons = 
					[
						"content" =>
						[
							[
								"kind"			=> "components.buttons.button",
								"buttonElement"	=> "a",
								"attributeEx"	=> "title=\"Editar Departamento\" href=\"".route('area.edit',$area->id)."\"",
								"label"			=> "<span class=\"icon-pencil\"></span>",
								"variant"		=> "success"
							],
							[
								"kind"			=> "components.buttons.button",
								"buttonElement"	=> "a",
								"attributeEx"	=> "title=\"Suspender Direccion\" href=\"".route('area.inactive',$area->id)."\"",
								"label"			=> "<span class=\"icon-bin\"></span>",
								"variant"		=> "red",
								"classEx"		=> "area-delete"
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
								"kind"			=> "components.buttons.button",
								"buttonElement"	=> "a",
								"attributeEx"	=> "title=\"Editar Departamento\" href=\"".route('area.edit',$area->id)."\"",
								"label"			=> "<span class=\"icon-pencil\"></span>",
								"variant"		=> "success"
							],
							[
								"kind"			=> "components.buttons.button",
								"buttonElement"	=> "a",
								"attributeEx"	=> "title=\"Reactivar Direccion\" href=\"".route('area.reactive',$area->id)."\"",
								"label"			=> "<span class=\"icon-check\"></span>",
								"variant"		=> "success",
								"classEx"		=> "area-reactive"
							]
						]
					];
				}
				$body = 
				[
					[
						"content"	=>
						[
							[
								"label" => $area->id
							]
						]
					],
					[
						"content" =>
						[
							[
								"label" => htmlentities($area->name),
							]
						]
					],
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
		{{ $areas->appends($_GET)->links() }}
	@else
		@component("components.labels.not-found") @endcomponent
	@endif
@endsection

@section('scripts')
	<script>
		$(document).ready(function()
		{
			// $('#input-search').on('keyup', function()
			// {
			// 	$text = $(this).val();
			// 	if ($text == "" || $text == " " || $text == "  " || $text == "   ")
			// 	{
			// 		$('#not-found').stop().show();
			// 		$('#not-found').html("RESULTADO NO ENCONTRADO");
			// 		$('#table').stop().hide();
			// 	}
			// 	else
			// 	{
			// 		$('#not-found').stop().hide();
			// 		$.ajax(
			// 		{
			// 			type	: 'get',
			// 			url		: '{{ url("configuration/area/search/search") }}',
			// 			data	: {'search':$text},
			// 			success	:function(data)
			// 			{
			// 				$('.table-responsive').html(data);
			// 			}
			// 		}); 
			// 	}
			// });
			$(document).on('click','.area-delete',function(e)
			{
				e.preventDefault();
				url = $(this).attr('href');
				swal({
					title		: "",
					text		: "Confirme que desea suspender la dirección",
					icon		: "warning",
					buttons		: ["Cancelar", "Suspender"],
					dangerMode	: true,
				})
				.then((isConfirm) => {
					if (isConfirm)
					{
						swal('Cargando',{
							icon: '{{ asset(getenv('LOADING_IMG')) }}',
							button: false,
							closeOnClickOutside: false,
							closeOnEsc: false
						});
						form = $('<form></form>').attr('action',url).attr('method','post').append('@csrf').append('@method("delete")');
						$(document.body).append(form);
						form.submit();
					}
				});
			})
			.on('click','.area-reactive',function(e)
			{
				e.preventDefault();
				url = $(this).attr('href');
				swal({
					title		: "",
					text		: "Confirme que desea reactivar la dirección",
					icon		: "warning",
					buttons		: ["Cancelar", "Reactivar"],
					dangerMode	: true,
				})
				.then((isConfirm) => {
					if (isConfirm)
					{
						swal('Cargando',{
							icon: '{{ asset(getenv('LOADING_IMG')) }}',
							button: false,
							closeOnClickOutside: false,
							closeOnEsc: false
						});
						form = $('<form></form>').attr('action',url).attr('method','post').append('@csrf').append('@method("delete")');
						$(document.body).append(form);
						form.submit();
					}
				});
			});
		});
	</script>
@endsection
