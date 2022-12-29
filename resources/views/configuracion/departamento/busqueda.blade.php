@extends('layouts.child_module')
@section('data')
	@component("components.forms.form", ["attributeEx" => "action=\"".route('department.search')."\" method=\"GET\" id=\"formsearch\""])
		@component("components.labels.title-divisor") BUSCAR DEPARTAMENTO @endcomponent
		@component("components.containers.container-form")
			<div class="col-span-4 md:col-start-2 md:col-span-2 md:col-end-4">
				@component("components.labels.label") Departamento: @endcomponent
				@component("components.inputs.input-text")
					@slot("attributeEx")
						type="text" 
						name="search" 
						id="input-search" 
						placeholder="Ingrese el departamento" 
						value="{{$search}}"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-4 md:col-start-2 md:col-span-2 md:col-end-4 grid justify-items-center md:justify-items-start">
				@component("components.buttons.button-search", ["attributeEx" => $attributeExButtonSearch??'', "classEx" => $classExButtonSearch??'']) @endcomponent
			</div>
		@endcomponent
		@if ($countDepa >= 1)
			<div class="flex flex-row justify-end">
				@component('components.labels.label')
					@component("components.buttons.button",['variant' => 'success'])
						@slot("attributeEx")
							type="submit"
							formaction="{{ route("department.export") }}"
						@endslot
						@slot("label")
							<span>Exportar a Excel</span><span class="icon-file-excel"></span>
						@endslot
					@endcomponent
				@endcomponent
			</div>
		@endif
	@endcomponent	
	@if ($countDepa >= 1)
		@php
			$modelHead = 
			[
				[
					["value" => "ID"], 
					["value" => "Nombre"], 
					["value" => "Acción"]
				]
			];
			foreach($departments as $depa)
			{
				if($depa->status == 'ACTIVE') 
				{
					$buttons = 
					[
						"content" =>
						[
							[
								"kind" 			=> "components.buttons.button",
								"attributeEx"	=> "title=\"Editar Departamento\" href=\"".route('department.edit',$depa->id)."\"",							
								"label"			=> "<span class=\"icon-pencil\"></span>",
								"variant"		=> "success",
								"buttonElement"	=> "a"
							],
							[
								"kind"			=> "components.buttons.button",
								"attributeEx"	=> "title=\"Suspender Departamento\" href=\"".route('department.inactive',$depa->id)."\"",
								"classEx"		=> "department-delete",
								"label"			=> "<span class=\"icon-bin\"></span>",
								"buttonElement"	=> "a",
								"variant"		=> "red"
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
								"kind" 			=> "components.buttons.button",
								"attributeEx"	=> "title=\"Editar Departamento\" href=\"".route('department.edit',$depa->id)."\"",							
								"label"			=> "<span class=\"icon-pencil\"></span>",
								"variant"		=> "success",
								"buttonElement"	=> "a"
							],
							[
								"kind"			=> "components.buttons.button",
								"attributeEx"	=> "title=\"Reactivar Departamento\" href=\"".route('department.reactive',$depa->id)."\"",
								"classEx"		=> "department-reactive",
								"label"			=> "<span class=\"icon-check\"></span>",
								"buttonElement"	=> "a",
								"variant"		=> "success"
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
								"label" => $depa->id
							]
						]
					],
					[
						"content" 	=>
						[
							[
								"label" => htmlentities($depa->name),
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
		{{ $departments->appends($_GET)->links() }}
	@else
		@component("components.labels.not-found") @endcomponent
	@endif
@endsection

@section('scripts')
	<script>
		$(document).ready(function(){
			$(document).on('click','.department-delete',function(e)
			{
				e.preventDefault();
				url = $(this).attr('href');
				swal({
					title		: "",
					text		: "Confirme que desea suspender el departamento",
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
			.on('click','.department-reactive',function(e)
			{
				e.preventDefault();
				url = $(this).attr('href');
				swal({
					title		: "",
					text		: "Confirme que desea reactivar el departamento",
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
