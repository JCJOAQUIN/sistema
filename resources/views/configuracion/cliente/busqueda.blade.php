@extends('layouts.child_module')
@section('data')
	@component("components.forms.form", ["attributeEx" => "method=\"GET\" action=\"".route('client.search')."\" id=\"formsearch\""])
		@component("components.labels.title-divisor") BUSCAR CLIENTES @endcomponent
		@component("components.containers.container-form")
			<div class="col-span-4 md:col-start-2 md:col-span-2 md:col-end-4">
				@component("components.labels.label") RFC/Razón Social: @endcomponent
				@php
					$value = isset($search) ? $search : '';
				@endphp
				@component("components.inputs.input-text", ["classEx" => "input-text-search", "attributeEx" => "type=\"text\" name=\"search\" id=\"input-search\" placeholder=\"Ingrese un RFC o Razón Social\" value=\"".$value."\""]) @endcomponent
			</div>
			<div class="col-span-4 md:col-start-2 md:col-span-2 md:col-end-4 grid justify-items-center md:justify-items-start">
                @component("components.buttons.button-search", ["variant" => "warning", "attributeEx" => "type=\"submit\"", "label" => "<span class=\"icon-search\"></span> Buscar"]) @endcomponent
            </div>
		@endcomponent
		@if ($countClients > 0)
			<div class="flex flex-row justify-end">
				@component('components.labels.label')
					@component("components.buttons.button",['variant' => 'success'])
						@slot("attributeEx")
							type="submit"
							formaction="{{ route('client.export') }}"
						@endslot
						@slot("label")
							<span>Exportar a Excel</span><span class="icon-file-excel"></span>
						@endslot
					@endcomponent
				@endcomponent
			</div>
		@endif
	@endcomponent
	@if ($countClients > 0)
		@php
			$modelHead = 
			[
				[
					["value" => "ID"],
					["value" => "RFC"],
					["value" => "Razón Social"],
					["value" => "Acción"]
				]
			];
			foreach($clients as $client)
			{
				$body = 
				[
					[
						"content" => 
						[
							[
								"kind" => "components.labels.label",
								"label" => $client->idClient
							]
						],
					],
					[
						"content" => 
						[
							[
								"kind" => "components.labels.label",
								"label" => $client->rfc
							]
						],
					],
					[
						"content" => 
						[
							[
								"kind" => "components.labels.label",
								"label" => htmlentities($client->businessName),
							]
						],
					],
					[
						"content" => 
						[
							[
								"kind" => "components.buttons.button",
								"variant" => "success",
								"buttonElement" => "a",
								"label" => "<span class=\"icon-pencil\"></span>",
								"attributeEx" => "href=\"".route('client.edit',$client->idClient)."\" alt=\"Editar\" title=\"Editar\""
							],
							[
								"kind" => "components.buttons.button",
								"variant" => "red",
								"buttonElement" => "a",
								"label" => "<span class=\"icon-bin\"></span>",
								"attributeEx" => "href=\"".route('client.destroy2',$client->idClient)."\" alt=\"Eliminar\" title=\"Eliminar\"",
								"classEx" => "client-delete"
							]
						],
					]
				];
				
				$modelBody[] = $body;
			}
		@endphp
		@Table(["attributeEx" => "id=\"table\"", "modelHead" => $modelHead, "modelBody" => $modelBody]) @endTable
		{{ $clients->appends(['search' => $search])->render() }}
	@else
		@component("components.labels.not-found") RESULTADO NO ENCONTRADO @endcomponent
	@endif
@endsection

@section('scripts')
	<script>
		$(document).ready(function()
		{
			$(document).on('click','.client-delete',function(e)
			{
				e.preventDefault();
				attr = $(this).attr('href');
				swal({
					title		: "",
					text		: "Confirme que desea eliminar el cliente",
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
