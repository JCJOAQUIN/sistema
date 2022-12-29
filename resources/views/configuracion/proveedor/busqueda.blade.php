@extends('layouts.child_module')
@section('data')
	@component("components.forms.form", ["attributeEx" => "action=\"".route('provider.search')."\" method=\"GET\""])
		@component("components.labels.title-divisor") BUSCAR PROVEEDORES @endcomponent
		@component("components.containers.container-form")
			<div class="col-span-4 md:col-start-2 md:col-span-2 md:col-end-4">
				@component("components.labels.label") RFC: @endcomponent
				@component("components.inputs.input-text")
					@slot("attributeEx")
						type = "text" 
						name = "search"
						placeholder = "Ingrese el RFC" 
						value = "{{$search}}"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-4 md:col-start-2 md:col-span-2 md:col-end-4 grid justify-items-center md:justify-items-start">
				@component("components.buttons.button-search", ["variant" => "warning", "attributeEx" => "type=\"submit\"", "label" => "<span class=\"icon-search\"></span> Buscar"]) @endcomponent
			</div>
		@endcomponent

		@if(count($providers) > 0)
			@php
				$modelHead = 
				[
					[
						["value" => "ID"], 
						["value" => "RFC"], 
						["value" => "Nombre"], 
						["value" => "Acción"]
					]
				];
				foreach ($providers as $provider)
				{
					$body =
					[
						[ 
							"content"	=> 
							[
								["label" => $provider->idProvider]
							]		
						],
						[ 
							"content"	=> 
							[
								["label" => $provider->rfc]
							]	
						],
						[ 
							"content" => 
							[
								["label" => htmlentities($provider->businessName)]
							]	
						]		
					];

					$buttons = 
					[
						["kind" => "components.buttons.button","variant" => "success", "buttonElement" => "a", "attributeEx" => "href=\"".route('provider.edit',[$provider->idProvider])."\" alt=\"Editar Proveedor\" title=\"Editar Proveedor\"", "label" => "<span class=\"icon-pencil\"></span>"],
						["kind" => "components.buttons.button","variant" => "red", "buttonElement" => "a", "attributeEx" => "type=\"button\" href=\"".route('provider.destroy2',[$provider->idProvider])."\" alt=\"Eliminar Proveedor\" title=\"Eliminar Proveedor\"", "classEx" => "provider-delete", "label" => "<span class=\"icon-bin\"></span>"],

					];
					array_push($body, ["content" => $buttons]);
					$modelBody[] = $body;
				}
			@endphp
		
			@component("components.tables.table",
			[
				"modelHead" => $modelHead,
				"modelBody" => $modelBody,
			])
			@endcomponent
		@else
			@component("components.labels.not-found", ["attributeEx" => "id=\"not-found\""]) RESULTADO NO ENCONTRADO @endcomponent
		@endif

		{{$providers->appends($_GET)->links()}}
	@endcomponent
@endsection

@section('scripts')
	<script>
		$(document).ready(function()
		{
			$(document).on('click','.provider-delete',function(e)
			{
				e.preventDefault();
				attr = $(this).attr('href');
				swal({
					title		: "",
					text		: "Confirme que desea eliminar el proveedor",
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
			});
		});
    </script> 
@endsection

