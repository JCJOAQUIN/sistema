@extends('layouts.child_module')
@section('data')
	<div class="text-center my-8">
		A continuación podrá editar el movimiento dado de alta anteriormente:
	</div>
	@component("components.forms.form",["attributeEx" => "method=\"POST\" id=\"container-alta\" action=\"".route('payments.movement.update',$movement->idmovement)."\"", "methodEx" => "PUT"])
		@component('components.labels.title-divisor') DATOS GENERALES DE LOS MOVIMIENTOS @endcomponent
		@component('components.containers.container-form')
			<div class="col-span-2">
				@component('components.labels.label') Empresa @endcomponent
				@php
					$options = collect();
					foreach(App\Enterprise::orderBy('name','asc')->whereIn('id',Auth::user()->inChargeEnt(102)->pluck('enterprise_id'))->where('status','ACTIVE')->get() as $enterprise)
					{
						$description = strlen($enterprise->name) >= 35 ? substr(strip_tags($enterprise->name),0,35)."..." : $enterprise->name;
						if (isset($movement) && $movement->idEnterprise == $enterprise->id) 
						{
							$options = $options->concat([["value"=>$enterprise->id, "selected"=>"selected", "description"=>$description]]);
						}
						else 
						{
							$options = $options->concat([["value"=>$enterprise->id, "description"=>$description]]);
						}
					}
					$attributeEx =  "name=\"enterpriseid\" data-validation=\"required\"";
					$classEx = "custom-select js-enterprise";
				@endphp
				@component('components.inputs.select',["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])  @endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Clasificación del gasto @endcomponent
				@php
					$options = collect();
					if(isset($movement) && $movement->idAccount)
					{
						$account = App\Account::find($movement->idAccount);
						$options = $options->concat([["value" => $account->idAccAcc, "selected" => "selected", "description" => $account->account. " - " .$account->description]]);
					}			
					$attributeEx =  "name=\"accountid\" data-validation=\"required\"";
					$classEx = "custom-select js-account";
				@endphp
				@component('components.inputs.select',["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])  @endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Tipo de movimiento @endcomponent
				@php
				$options = collect(
					[
						['value'=>'Ingreso', 'description'=>'Ingreso'], 
						['value'=>'Devolución', 'description'=>'Devolución'], 
						['value'=>'Rechazos', 'description'=>'Rechazos'], 
						['value'=>'Egreso', 'description'=>'Egreso']
					]
				);
				if(isset($movement) && ($movement->movementType == "Ingreso" || $movement->movementType == "Devolución" || $movement->movementType == "Rechazos" || $movement->movementType == "Egreso"))
				{
					$options = $options->concat([['value'=>$movement->movementType, 'selected'=>'selected','description'=>$movement->movementType]]);
				}
					$attributeEx =  "name=\"type\"";
					$classEx = "custom-select js-type";
				@endphp
				@component('components.inputs.select',["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])  @endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Fecha: @endcomponent
				@component('components.inputs.input-text')
					@slot('classEx')
						new-input-text
						datepicker
					@endslot
					@slot('attributeEx')
						id="datmove"
						readonly="true"
						placeholder="Ingrese la fecha"
						name="movementDate"
						value="{{ date('d-m-Y',strtotime($movement->movementDate)) }}"
						data-validation="required"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Importe: @endcomponent
				@component('components.inputs.input-text')
					@slot('classEx')
						new-input-text
						amount
					@endslot
					@slot('attributeEx')
						id="imove"
						name="amount"
						placeholder="Ingrese el importe"
						value="{{ $movement->amount }}"
						data-validation="required"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Descripción: @endcomponent
				@component('components.inputs.input-text')
					@slot('classEx')
						new-input-text
					@endslot
					@slot('attributeEx')
						id="desmove"
						name="description"
						placeholder="Ingrese una descripción"
						value="{{ $movement->description }}"
						data-validation="required"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Comentarios: @endcomponent
				@component('components.inputs.text-area')
					@slot('classEx')
						new-input-text
					@endslot
					@slot('attributeEx')
						id="comove"
						rows="4"
						name="commentaries"
						placeholder="Ingrese un comentario"
						data-validation="required"
					@endslot
					{{$movement->commentaries}}
				@endcomponent
			</div>
		@endcomponent
		<div class="content-start items-start flex flex-row flex-wrap justify-center w-full mt-4">
			@component("components.buttons.button")
				@slot("attributeEx")
				name="save"
				type="submit"
				value="ACTUALIZAR MOVIMIENTO"
				@endslot
				ACTUALIZAR MOVIMIENTO
			@endcomponent
		</div>			
	@endcomponent
@endsection

@section('scripts')
<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
<script src="{{ asset('js/jquery-ui.js') }}"></script>
<script src="{{ asset('js/jquery.numeric.js') }}"></script>
<script src="{{ asset('js/datepicker.js') }}"></script>
<script type="text/javascript">
	
	$(document).ready(function()
	{
		@php
			$selects = collect([
				[
					"identificator"				=> ".js-enterprise",
					"placeholder"				=> "Seleccione la empresa",
					"maximumSelectionLength"	=> "1"
				],
				[
					"identificator"				=> ".js-type",
					"placeholder"				=> "Seleccione un tipo de movimiento",
					"maximumSelectionLength"	=> "1"
				]
			]);
		@endphp
		@component("components.scripts.selects",["selects" => $selects]) @endcomponent
		generalSelect({'selector': '.js-account', 'depends': '.js-enterprise', 'model': 10});
		$('.amount').on("contextmenu",function(e)
		{
			return false;
		});
		$('.amount',).numeric({ altDecimal: ".", decimalPlaces: 2, negative : false });
		$(function(){
			$('.datepicker').datepicker(
			{
				dateFormat : 'dd-mm-yy',
			});
		});
	});
	$(document).on('change','.js-enterprise',function()
	{
		$('.js-account').empty();
	});
</script>
@endsection