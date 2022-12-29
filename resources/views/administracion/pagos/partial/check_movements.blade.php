@extends('layouts.child_module')
@section('data')
	@if(count($movements)>0)
		@component("components.forms.form",["attributeEx" => "method=\"POST\" accept-charset=\"UTF-8\" enctype=\"multipart/form-data\" action=\"".route('payments.movement-massive.update')."\""])
			@component("components.labels.not-found",["variant" => "note"]) Edite los movimientos anteriormente subidos, si no hay ningún error proceda a guardar los cambios. @endcomponent
			@component("components.labels.title-divisor") EDICIÓN @endcomponent
			
			@php
				$modelBody = [];
				$modelHead = ["ID","Concepto","Importe","Fecha","Tipo","Acción"];

				foreach($movements as $movement)
				{
					$date = Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$movement->movementDate)->format('d-m-Y');

					$options = collect();
					$values = ["Ingreso","Devolución","Rechazos","Egreso"];

					foreach($values as $value)
					{
						if(isset($movement) && $movement->movementType == $value)
						{
							$options = $options->concat([["value" => $value,"selected" => "selected", "description" => $value]]);
						}
						else
						{
							$options = $options->concat([["value" => $value, "description" => $value]]);
						}
					}

					$body =
					[
						"classEx" => "tr",
						[
							"content" =>
							[
								[
									"label" => $movement->idmovement,
								],
								[
									"kind" 			=> "components.inputs.input-text",
									"attributeEx" 	=> "type=\"hidden\" name=\"idmovement[]\" value=\"".$movement->idmovement."\"",
									"classEx"		=> "idmovement"
								]
							]
						],
						[
							"content" =>
							[
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"text\" name=\"description[]\" value=\"".$movement->description."\"",
								]
							]
						],
						[
							"content" =>
							[
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"text\" name=\"amount[]\" value=\"".$movement->amount."\"",
								]
							]
						],
						[
							"content" =>
							[
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"text\" name=\"movementDate[]\" value=\"".$date."\"",
									"classEx"		=> "datepicker",
								]
							]
						],
						[
							"content" =>
							[
								[
									"kind"			=> "components.inputs.select",
									"attributeEx"	=> "name=\"movementType[]\"",
									"classEx"		=> "custom-select option",
									"options"		=> $options,
								]
							]
						],
						[
							"content" =>
							[
								[
									"kind"			=> "components.buttons.button",
									"attributeEx"	=> "type=\"button\"",
									"variant"		=> "dark-red",
									"classEx"		=> "deleteMovement",
									"label"			=> "<span class=\"icon-x\"></span>"
								]
							]
						],
					];
					$modelBody[] = $body;
				}
			@endphp
			@component("components.tables.alwaysVisibleTable",[
				"modelHead" => $modelHead,
				"modelBody" => $modelBody
			])@endcomponent
			<div class="text-center">
				@component("components.buttons.button")
					@slot("attributeEx")
						type="submit"
						name="send"
					@endslot
					GUARDAR CAMBIOS
				@endcomponent
			</div>
		@endcomponent
	@else
		@component("components.labels.not-found") 
			@slot("slot")
				No se registró ningún movimiento, por favor verifique la información del archivo cargado. 
			@endslot	
		@endcomponent
	@endif
@endsection

@section('scripts')
	<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
	<script src="{{ asset('js/jquery-ui.js') }}"></script>
	<script src="{{ asset('js/jquery.numeric.js') }}"></script>
	<script src="{{ asset('js/datepicker.js') }}"></script>

	<script>
		$(document).ready(function()
		{
			@php
				$selects = collect([
					[
						"identificator"				=> ".option",
						"placeholder"				=> "Seleccione una opción",
						"maximumSelectionLength"	=> "1"
					],
				]);
			@endphp
			@component("components.scripts.selects",["selects" => $selects]) @endcomponent

			$('[name="amount[]"]',).numeric({ altDecimal: ".", decimalPlaces: 2, negative : false });
			$(function()
			{
				$('.datepicker').datepicker(
				{
					dateFormat : 'dd-mm-yy',
				});
			});

			$(document).on('click','.deleteMovement',function()
			{
				id	= $(this).parents('.tr').find('.idmovement').val();
				$.ajax(
				{
					type : 'post',
					url  : '{{ route('payments.movement-massive.delete') }}',
					data : {'id':id},
					success : function(data)
					{
						swal('','Movimiento Eliminado','success');
					},
					error : function()
					{
						swal('','Sucedió un error, por favor intente de nuevo.','error');
					}
				});
				$(this).parents('.tr').remove();

			})
		});
	</script>
@endsection