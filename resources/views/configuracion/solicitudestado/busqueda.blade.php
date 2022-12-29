@extends('layouts.child_module')

@section('data')
	@component("components.forms.form", ["attributeEx" => "id=\"formsearch\""])
		@component("components.labels.title-divisor") BUSCAR ESTADOS @endcomponent
		@component("components.containers.container-form")
			<div class="col-span-4 md:col-start-2 md:col-span-2 md:col-end-4">
				@component("components.labels.label", ["label" => "Estado:"]) @endcomponent

				@component('components.inputs.input-text')
					@slot('attributeEx')
						placeholder = "Ingrese el estado" 
						type  = "text" 
						value = "{{ $search }}"
						name  = "search"
					@endslot
				@endcomponent
			</div>

			<div class="col-span-4 md:col-start-2 md:col-span-2 md:col-end-4 grid justify-items-center md:justify-items-start">
				@component("components.buttons.button-search", ["variant" => "warning", "attributeEx" => "type=\"submit\"", "label" => "<span class=\"icon-search\"></span> Buscar"]) @endcomponent
			</div>
		@endcomponent

		@if($statusRequest->count() >0)
			<div class="float-right">
				@component("components.buttons.button", ["variant" => "success", "classEx" => "export"])
					@slot("attributeEx")
						type 	   = "submit"
						formaction = "{{ route('status.export') }}" 
						formmethod = "get"
					@endslot
					Exportar a Excel
					<span class='icon-file-excel'></span>
				@endcomponent
			</div>
			@php 
				$modelHead = 
				[
					[
						["value" => "ID"], 
						["value" => "Nombre"], 
						["value" => "Acción"]
					]
				];
				foreach($statusRequest as $item)
				{
					$body =
					[
						[
							"content"	=> 
							[
								[
									"label" => $item->idrequestStatus
								]		
							]
						],
						[
							"content" => 
							[
								[
									"label" => htmlentities($item->description),
								]		
							]
						],
						[
							"content" =>
							[
								[
									"kind"			=> "components.buttons.button",
									"buttonElement"	=> "a",
									"attributeEx"	=> "title=\"Editar estado\" href=\"".route('status.edit',$item->idrequestStatus)."\"",
									"label"			=> "<span class=\"icon-pencil\"></span>",
									"variant"		=> "success"
								]
							]
						]	
					];
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

			{{$statusRequest->appends($_GET)->links()}}
		@else
			@component("components.labels.not-found", ["attributeEx" => "not-found"]) RESULTADO NO ENCONTRADO @endcomponent
		@endif
	@endcomponent
@endsection
