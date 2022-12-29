@extends('layouts.child_module')

@section('data')
	@component("components.forms.form", 
	[
		"attributeEx" => isset($attributeEx) ? $attributeEx : "", 
		"token"       => "true"]
	)
		@csrf
		@component("components.containers.container-form")
			<div class="col-span-2">
				@component("components.labels.label") WBS: @endcomponent
				@component("components.inputs.input-text")
					@slot("attributeEx")
						name="wbs" 
						value="{{ isset($wbs) ? $wbs : '' }}" 
						placeholder="Ingrese un WBS"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") No RQ: @endcomponent
				@component("components.inputs.input-text")
					@slot("attributeEx")
						name="rq" 
						value="{{ isset($rq) ? $rq : '' }}" 
						placeholder="Ingrese un número de RQ"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Pedido OC: @endcomponent
				@component("components.inputs.input-text")
					@slot("attributeEx")
						type="text" 
						name="oc" 
						value="{{ isset($oc) ? $oc : '' }}" 
						placeholder="Ingrese un pedido"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Comprador: @endcomponent
				@component("components.inputs.input-text")
					@slot("attributeEx")
						type="text" 
						name="comprador" 
						value="{{ isset($comprador) ? $comprador : '' }}" 
						placeholder="Ingrese un comprador"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Proveedor: @endcomponent
				@component("components.inputs.input-text")
					@slot("attributeEx")
						type="text" 
						name="proveedor" 
						value="{{ isset($proveedor) ? $proveedor : '' }}" 
						placeholder="Ingrese un proveedor"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Concepto: @endcomponent
				@component("components.inputs.input-text")
					@slot("attributeEx")
						type="text" 
						name="concepto" 
						value="{{ isset($concepto) ? $concepto : '' }}" 
						placeholder="Ingrese un concepto"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2 md:col-span-4 grid md:flex md:items-center justify-center md:justify-start space-x-2">
				@component("components.buttons.button-search", ["attributeEx" => $attributeExButtonSearch??'', "classEx" => $classExButtonSearch??'']) @endcomponent
				@component("components.buttons.button", ["buttonElement" => "a", "variant" => "reset", "classEx" => "bg-gray-200 px-7 py-2 rounded cursor-pointer hover:bg-gray-200 uppercase font-bold text-sm h-9 text-blue-gray-700", "attributeEx" => "href=\"".strtok($_SERVER['REQUEST_URI'], '?')."\""])Borrar campos @endcomponent
			</div>
		@endcomponent
		<div class="flex flex-row justify-end">
			@component('components.labels.label')
				@component('components.buttons.button',["variant" => "success"])
					@slot('attributeEx')
						type="submit" 
						formmethod="POST" 
						formaction="{{ route('construction.procurement.export') }}"
					@endslot
					@slot('classEx')
						export
					@endslot
					@slot('label')
						<span>Exportar a Excel</span><span class="icon-file-excel"></span>
					@endslot
				@endcomponent
			@endcomponent
		</div>
	@endcomponent
	@isset($procurementData)
		@php
			$body 		= [];
			$modelBody	= [];
			$modelHead	= 
			[
				[
					["value" => "WBS"],
					["value" => "Nombre de WBS"],
					["value" => "RQ"],
					["value" => "OC"],
					["value" => "Proveedor"],
					["value" => "Concepto"]
				]
			];
			foreach($procurementData as $p)
			{
				$body = 
				[
					[
						"content" => 
						[
							"label" => $p->wbs
						]
					],
					[ 
						"content" => 
						[ 
							"label" => $p->nombre_wbs
						]
					],
					[
						"content" => 
						[ 
							"label" => $p->no_rq
						]
					],
					[
						"content" => 
						[
							"label" => $p->pedido_oc
						]
					],
					[
						"content" => 
						[
							"label" => $p->proveedor
						]
					],
					[
						"content" => 
						[
							"label" => htmlentities($p->concepto)
						]
					]
				];
				array_push($modelBody, $body);
			}
		@endphp
		@component('components.tables.table',[
			"modelHead" 			=> $modelHead,
			"modelBody" 			=> $modelBody
		])
		@endcomponent
		{{ $procurementData->appends($_GET)->links() }}
	@else
		@component('components.labels.not-found') @endcomponent
	@endisset
@endsection