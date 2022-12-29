@extends('layouts.child_module')  
@section('data')
	@component("components.forms.form", ["attributeEx" => "action=\"".route('enterprise.search')."\" method=\"GET\" id=\"formsearch\""])
		@component("components.labels.title-divisor") BUSCAR EMPRESA @endcomponent
		@component("components.containers.container-form")
			<div class="col-span-4 md:col-start-2 md:col-span-2 md:col-end-4">
				@component("components.labels.label")
					Empresa:
				@endcomponent
				@component("components.inputs.input-text")
					@slot("attributeEx")
						type="text" 
						value="{{$search}}" 
						name="search" 
						id="input-search" 
						placeholder="Ingrese el nombre"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-4 md:col-start-2 md:col-span-2 md:col-end-4 grid justify-items-center md:justify-items-start">
				@component("components.buttons.button-search", ["attributeEx" => $attributeExButtonSearch??'', "classEx" => $classExButtonSearch??'']) @endcomponent
			</div>
		@endcomponent
		@if($enterprises->count() >0)
			<div class="flex flex-row justify-end">
				@component('components.labels.label')
					@component("components.buttons.button",['variant' => 'success'])
						@slot("attributeEx")
							type="submit"
							formaction="{{ route("enterprise.export") }}"
						@endslot
						@slot("label")
							<span>Exportar a Excel</span><span class="icon-file-excel"></span>
						@endslot
					@endcomponent
				@endcomponent
			</div>
		@endif
	@endcomponent
	@if($enterprises->count() >0)
		@php
			$modelHead = 
			[
				[
					["value" => "ID"],
					["value" => "Nombre"],
					["value" => "RFC"],
					["value" => "Acción"]
				]
			];
			foreach($enterprises as $item)
			{
				$button = [];
				if($item->status == 'ACTIVE')
				{
					$button = 
					[
						"kind"			=> "components.buttons.button",
						"variant"		=> "red",
						"label"			=> "<span class=\"icon-bin\"></span>",
						"buttonElement"	=> "a",
						"classEx" 		=> "enterprise-delete",
						"attributeEx"	=> "title=\"Suspender Empresa\" href=\"".route('enterprise.inactive',$item->id)."\""	
					];
				}
				elseif($item->status == 'INACTIVE')
				{
					$button = 
					[
						"kind"			=> "components.buttons.button",
						"variant"		=> "success",
						"label"			=> "<span class=\"icon-check\"></span>",
						"buttonElement"	=> "a",
						"classEx" 		=> "enterprise-reactive",
						"attributeEx"	=> "title=\"Reactivar Empresa\" href=\"".route('enterprise.reactive',$item->id)."\""
					];
				}
				$body =
				[
					[
						"content"	=> 
						[
							["label" => $item->id]
						]
					],
					[
						"content"	=> 
						[
							["label" => htmlentities($item->name)]
						]
					],
					[
						"content" => 
						[
							["label" => $item->rfc]
						]
					],
					[
						"content" => 
						[
							[
								"kind"	=> "components.buttons.button",
								"variant" => "success",
								"label"	=> "<span class=\"icon-pencil\"></span>",
								"buttonElement"	=> "a",
								"attributeEx"	=> "title=\"Editar Empresa\" href=\"".route('enterprise.edit',$item->id)."\""
							],
							$button
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
		{{ $enterprises->appends($_GET)->links() }}
	@else 
		@component("components.labels.not-found") @endcomponent
	@endif
@endsection

@section('scripts')
	<script>
		$(document).ready(function(){
			$(document).on('click','.enterprise-delete',function(e)
			{
				e.preventDefault();
				url = $(this).attr('href');
				swal({
					title		: "",
					text		: "Confirme que desea suspender la empresa",
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
			.on('click','.enterprise-reactive',function(e)
			{
				e.preventDefault();
				url = $(this).attr('href');
				swal({
					title		: "",
					text		: "Confirme que desea reactivar la empresa",
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
