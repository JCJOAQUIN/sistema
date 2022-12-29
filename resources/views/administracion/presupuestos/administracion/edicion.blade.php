@extends('layouts.child_module')
@section('data')
	@component("components.forms.form", ["attributeEx" => "method=\"POST\" action=\"".route('budget.administration.update',$budget->id)."\"", "methodEx" => "PUT", "files" => true])
		<div class="sm:text-center text-left my-5">
			A continuación se muestra el presupuesto asignado a cada cuenta:
		</div>
		@php
			$modelTable	=
			[
				["Empresa:",		$budget->enterprise->name],
				["Departamento:",	$budget->department->name],
				["Proyecto:",		$budget->department->name],
				["Periodicidad:",	$budget->periodicityData()],
				["Elaborado por:",	$budget->user->fullName()],
			];
		@endphp
		@component('components.templates.outputs.table-detail', ["modelTable" => $modelTable])
			@slot('classEx')
				mt-4
			@endslot
			@slot('title')
				Detalles del Presupuesto
			@endslot
		@endcomponent
		@component('components.labels.not-found', ["variant" =>	"note"])
			@slot('attributeEx')
				id="error_request"
			@endslot
			Habilite el botón con el icono <span class="icon-check"></span> para poder realizar un cambio en la cuenta correspondiente. Al deshabilitarlo no se guardará el cambio realizado.
		@endcomponent
		@php
			$modelHead	=	[];
			$body		=	[];
			$modelBody	=	[];
			$modelHead	=	["Acción", "Cuenta", "Monto"];
			foreach($budget->detail as $detail)
			{
				$body	=
				[
					[
						"content"	=>
						[
							[
								"kind"				=>	"components.inputs.checkbox",
								"label"				=>	"<span class='icon-check'></span>",
								"attributeEx"		=>	"id=\"budget_".$detail->id."\" name=\"budget_id[]\" value=\"".$detail->id."\"",
								"classExContainer"	=> "inline-flex",
								"classExLabel"		=>	"request-validate"
							]
						],
					],
					[
						"content"	=>	["label"	=>	$detail->account],
					],
					[
						"content"	=>
						[
							[
								"kind"			=>	"components.inputs.input-text",
								"attributeEx"	=>	"type=\"text\" disabled=\"disabled\" name=\"amount_".$detail->id."\" value=\"".$detail->amount."\"",
								"classEx"		=>	"disabled-amount"
							]
						],
					],
				];
				$modelBody[]	=	$body;
			}
		@endphp
		@component('components.tables.alwaysVisibleTable', ["modelHead" => $modelHead, "modelBody" => $modelBody])@endcomponent
		<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mt-4">
			@component('components.buttons.button', ["variant" => "primary"])
				@slot('attributeEx')
					type="submit" name="save" value="GUARDAR CAMBIOS"
				@endslot
				GUARDAR CAMBIOS
			@endcomponent
		</div>
	@endcomponent
@endsection
@section('scripts')
	<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
	<script src="{{ asset('js/jquery-ui.js') }}"></script>
	<script src="{{ asset('js/jquery.numeric.js') }}"></script>
	<script type="text/javascript">
		$(document).ready(function()
		{
			$('.disabled-amount').numeric({ negative:false, altDecimal: ".", decimalPlaces: 2 });
			$(document).on('click','[name="budget_id[]"]',function()
			{
				if($(this).is(':checked'))
				{
					$(this).parents('.tr').find('.disabled-amount').prop('disabled',false);
				}
				else
				{
					$(this).parents('.tr').find('.disabled-amount').prop('disabled',true);
				}
			})
		});
	</script>
@endsection