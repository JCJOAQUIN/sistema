@extends('layouts.child_module')

@section('data')
	@if (isset($items))
		@component("components.forms.form", ["attributeEx" => "action=\"".route('items.update', $items->id)."\" method=\"POST\" id=\"container-alta\""])
			@slot('methodEx') PUT @endslot
	@else
		@component("components.forms.form", ["attributeEx" => "action=\"".route('items.store')."\" method=\"POST\" id=\"container-alta\""])
	@endif
			@component('components.labels.title-divisor') DATOS DE CONTRATO @endcomponent
			@component("components.labels.subtitle") Para {{ (isset($items)) ? "editar la partida" : "agregar una partida nueva" }} es necesario colocar los siguientes campos: @endcomponent
			@component('components.containers.container-form')

				<div class="col-span-4 md:col-start-2 md:col-span-2 md:col-end-4">
					@component("components.labels.label")
						Contrato:
					@endcomponent

					@php
						$options = collect();

						foreach(App\Contract::orderBy('name','asc')->get() as $contract)
						{
							if (isset($items) && $items->contract_id == $contract->id)
							{
								$options = $options->concat([["value" => $contract->id, "selected" => "selected", "description" => $contract->number."-".$contract->name]]);
							}
							else 
							{	
								$options = $options->concat([["value" => $contract->id, "description" => $contract->number."-".$contract->name]]);
							}
						}
					@endphp

					@component("components.inputs.select",["options" => $options]) 
						@slot('attributeEx')	
							name = "contractId" 
							multiple = "multiple" 
							data-validation = "required"
							@if(isset($items)) 
								disabled 
							@endif
						@endslot

						@slot('classEx')
							js-contract removeselect
						@endslot
					@endcomponent
				</div>
			@endcomponent

			@component('components.labels.title-divisor') DATOS DE LA PARTIDA @endcomponent

			@if(isset($items))
				@component("components.containers.container-form")
					<div class="col-span-2">
						@component("components.labels.label")
							Pda. Contrato:
						@endcomponent

						@component("components.inputs.input-text")
							@slot("attributeEx")
								type = "text" 
								placeholder = "Ingrese la partida" 
								class = "tpda new-input-text" 
								name = "tpda" 
								data-validation = "server" 
								data-validation-url = "{{ route('configuration-items.validation') }}" 
								data-validation-req-params = "{{ json_encode(array('oldItem'=>$items->contract_item,'contract_id'=>$items->contract_id)) }}" 
								value = "{{$items->contract_item}}"
							@endslot
						@endcomponent

						@component("components.inputs.input-text")				
							@slot('attributeEx')
								type = "hidden"
								name = "tcpda" 
								value = "{{$items->id}}"
							@endslot

							@slot('classEx')
								tcpda
							@endslot
						@endcomponent
					</div>

					<div class="col-span-2">
						@component("components.labels.label")
							Unidad:
						@endcomponent

						@component("components.inputs.input-text")
							@slot('attributeEx')
								type = "text" 
								placeholder = "Ingrese la unidad"
								name = "tunit" 
								value = "{{$items->unit}}" 
								data-validation = "required"
							@endslot

							@slot('classEx')
								tunit
							@endslot
						@endcomponent
					</div>

					<div class="col-span-2">
						@component("components.labels.label")
							PU: 
						@endcomponent

						@component("components.inputs.input-text")
							@slot('attributeEx')
								type = "text" 
								placeholder = "Ingrese el PU"
								name = "tpu" 
								value = "{{$items->pu}}" 
								data-validation = "required"
								onpaste="return false"
							@endslot

							@slot('classEx')
								tpu
							@endslot
						@endcomponent
					</div>

					<div class="col-span-2">
						@component("components.labels.label")
							Monto: 
						@endcomponent

						@component("components.inputs.input-text")
							@slot('attributeEx')
								type = "text" 
								placeholder = "Ingrese el monto"
								name = "tamount" 
								value = "{{$items->amount}}" 
								data-validation = "required"
								onpaste="return false"
							@endslot

							@slot('classEx')
								tamount
							@endslot
						@endcomponent
					</div>

					<div class="col-span-2">
						@component("components.labels.label")
							Actividad: 
						@endcomponent

						@component("components.inputs.text-area")
							@slot('attributeEx')
								name = "tactivity"
								placeholder = "Ingrese la actividad"
								cols = "30" 
								rows = "10" 
								data-validation = "required"
							@endslot

							@slot('classEx')
								tactivity
							@endslot

							{{$items->activity}}
						@endcomponent
					</div>
				@endcomponent
			@else
				@component("components.containers.container-form")
					<div class="col-span-2">
						@component("components.labels.label")
							Pda. Contrato:
						@endcomponent

						@component("components.inputs.input-text")
							@slot('attributeEx')
								type = "text"
								name = "tpda" 
								data-validation = "server" 
								data-validation-url = "{{ route('configuration-items.validation') }}" 
								placeholder = "Ingrese la partida"
							@endslot

							@slot('classEx')
								remove pdaContract
							@endslot
						@endcomponent

						@component("components.inputs.input-text")
							@slot('attributeEx')
								type="hidden"
							@endslot

							@slot('classEx')
								idPda remove
							@endslot
						@endcomponent
					</div>

					<div class="col-span-2">
						@component("components.labels.label")
							Unidad:
						@endcomponent

						@component("components.inputs.input-text")
							@slot('attributeEx')
								type = "text"
								placeholder = "Ingrese la unidad"
							@endslot

							@slot('classEx')
								unit remove
							@endslot
						@endcomponent
					</div>

					<div class="col-span-2">
						@component("components.labels.label")
							PU:
						@endcomponent

						@component("components.inputs.input-text")
							@slot('attributeEx')
								type = "text"
								placeholder = "Ingrese el PU"
								onpaste="return false"
							@endslot

							@slot('classEx')
								pu remove
							@endslot
						@endcomponent
					</div>

					<div class="col-span-2">
						@component("components.labels.label")
							Monto:
						@endcomponent

						@component("components.inputs.input-text")
							@slot('attributeEx')
								type="text"
								placeholder="Ingrese el monto"
								onpaste="return false"
							@endslot

							@slot('classEx')
								amount remove
							@endslot
						@endcomponent
					</div>

					<div class="col-span-2">
						@component("components.labels.label")
							Actividad:
						@endcomponent

						@component("components.inputs.text-area")
							@slot('attributeEx')
								placeholder="Ingrese la actividad"
								cols="35" 
								rows="10"
							@endslot

							@slot('classEx')
								activity remove
							@endslot
						@endcomponent
					</div>

					<div class="col-span-2 md:col-span-4 grid justify-items-center md:justify-items-start">
						@component('components.buttons.button', ["variant" => "warning"])
							@slot('attributeEx')
								id	 = "add"
								name = "add"
								type = "button"
							@endslot
							@slot('classEx')
								add2
							@endslot
							<span class="icon-plus"></span>
							<span>Agregar partida</span>
						@endcomponent
					</div>
				@endcomponent

				@AlwaysVisibleTable([
					"modelHead" 		=> ["#", "Pda. Contrato", "Actividad", "Unidad", "PU", "Monto", "Acciones"],
					"modelBody" 		=> [],
					"attributeExBody" 	=> "id=\"body\"",
					"attributeEx"		=> "id=\"table-show\" style=\"display:none !important;\""
				])@endAlwaysVisibleTable
			@endif

			<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mt-8">
                @component('components.buttons.button', ["variant"=>"primary"])
                    @slot('attributeEx')
                        type = "submit"
						name = "send"
                    @endslot

					@if(isset($items)) 
						Actualizar
					@else 
						Registrar
					@endif
                @endcomponent
				@if(!isset($items)) 
					@component("components.buttons.button", ["classEx" => "btn-delete-form", "variant" => "reset"]) 
						@slot("attributeEx")
							type = "reset" 
							name = "borrar"
						@endslot
						Borrar campos
					@endcomponent
				@else
					@component('components.buttons.button', ["variant"=>"reset", "buttonElement"=>"a"])
						@slot('attributeEx')
							@if(isset($option_id)) 
								href="{{ url(getUrlRedirect($option_id)) }}" 
							@else 
								href="{{ url(getUrlRedirect($child_id)) }}" 
							@endif 
						@endslot
						Regresar
					@endcomponent
				@endif
            </div>
		@endcomponent
@endsection

@section('scripts')
	<script src="{{ asset('js/jquery.numeric.js') }}"></script>
	<script type="text/javascript">
		$(document).ready(function()
		{
			validation();
			$('.pu, .amount, .tpu, .tamount').numeric({ altDecimal: ".", decimalPlaces: 2,  negative : false });
			@php
				$selects = collect([
					[
						"identificator"          => ".js-contract", 
						"placeholder"            => "Seleccione el contrato", 
						"maximumSelectionLength" => "1"
					]
				]);
			@endphp
			@component("components.scripts.selects",["selects" => $selects])@endcomponent

			$('.js-contract').on('select2:unselecting', function (e)
			{
				e.preventDefault();
				swal({
					title		: "Cambiar Contrato",
					text		: "Si cambia el contrato, todas las partidas que ya se encontraban agregadas serán eliminadas.\n¿Desea continuar?",
					icon		: "warning",
					buttons		: ["Cancelar","OK"],
					dangerMode	: true,
				})
				.then((willClean) =>
				{
					if(willClean)
					{
						swal({
							icon: '{{ asset(getenv('LOADING_IMG')) }}',
							button: false,
							closeOnClickOutside: false,
							closeOnEsc: false,
							timer: 1000
						});
						$('#body').empty();
						$('.pdaContract').val('').removeClass('valid').removeClass('error').parent('div').find('.form-error').remove();
						$('.pu').val('').removeClass('valid').removeClass('error').parent('div').find('.form-error').remove();
						$('.activity').val('').removeClass('valid').removeClass('error').parent('div').find('.form-error').remove();
						$('.unit').val('').removeClass('valid').removeClass('error').parent('div').find('.form-error').remove();
						$('.amount').val('').removeClass('valid').removeClass('error').parent('div').find('.form-error').remove();
						$(this).val(null).trigger('change');
					}
					else
					{
						swal.close();
					}
				});
			});

			$(document).on('change','.pu',function()
			{
				pu	=	$('.pu').val();
				if (pu <= 0 || pu == '')
				{
					swal('','El PU no puede ser menor o igual a cero', 'error');
					$('.pu').val("");
					$('.pu').removeClass('valid');
				}
			})
			.on('change','.amount',function()
			{
				amount	=	$('.amount').val();
				if (amount <= 0)
				{
					swal('','El Monto no puede ser menor o igual a cero', 'error');
					$('.amount').val("");
					$('.amount').removeClass('valid');
				}
			})
			.on('keyup','.pdaContract, .activity, .unit, .pu, .amount',function()
			{
				pda			=	$('.pdaContract').val().trim();
				activity	=	$('.activity').val().trim();
				unit		=	$('.unit').val().trim();
				pu			=	$('.pu').val().trim();
				amount		=	$('.amount').val().trim();
				if (pda!="" && pda.length>0)
				{
					if ($('.pdaContract').hasClass("error") == false)
					{
						$('.pdaContract').addClass('valid');
					}
				}
				else
				{
					$('.pdaContract').removeClass('valid');
				}
				if (activity!="")
				{
					$('.activity').removeClass('error').addClass('valid');
				}
				else
				{
					$('.activity').removeClass('valid');
				}
				if (unit!="")
				{
					$('.unit').removeClass('error').addClass('valid');
				}
				else
				{
					$('.unit').removeClass('valid');
				}
				if (pu!="")
				{
					$('.pu').removeClass('error').addClass('valid');
				}
				else
				{
					$('.pu').removeClass('valid');
				}
				if (amount!="")
				{
					$('.amount').addClass('valid');
				}
			})
			.on('click','#add', function(e)
			{
				$('#add').attr('disabled',true);
				countConcept	=	$('.class_countConcept').length;
				idPda			=	$('.idPda').val();
				pda				=	$('.pdaContract').val().trim();
				activity		=	$('.activity').val().trim();
				unit			=	$('.unit').val().trim();
				pu				=	$('.pu').val().trim();
				amount			=	$('.amount').val().trim();
				if (pda == "" || activity == "" || unit == "" || pu == "" || amount == "") 
				{
					contract_id	= $('[name="contractId"] option:selected').val();
					if(contract_id != "" && contract_id != null && contract_id != undefined)
					{
						$('[name="contractId"]').removeClass("error");
						$('[name="contractId"]').siblings(".form-error").remove();
					}
					if (pda == "")
					{
						$('.pdaContract').addClass('error');
					}
					if (activity == "")
					{
						$('.activity').addClass('error');
					}
					if (unit == "")
					{
						$('.unit').addClass('error');
					}
					if (pu == "")
					{
						$('.pu').addClass('error');
					}
					if(amount == "")
					{
						$('.amount').addClass('error');
					}
					swal('', 'Por favor complete los datos que son requeridos', 'error');
					$('#add').removeAttr('disabled');
				}
				else
				{
					@if(!isset($items))
						contract_id	= $('[name="contractId"] option:selected').val();
						tpda		= $('[name="tpda"]').val();
						if(contract_id != "" && contract_id != null && contract_id != undefined)
						{
							$('[name="contractId"]').removeClass("error");
							$('[name="contractId"]').siblings(".form-error").remove();
						}
					@else
						contract_id = {{ $items->contract_id }};
						tpda		= $('[name="tpda"]').val();
					@endif
					if(tpda != "" && tpda != null && tpda != undefined)
					{
						$('.pdaContract').removeClass("error");
						$('.pdaContract').siblings(".form-error").remove();
					}
					$('.pdaContract, .activity, .unit, .pu, .amount').removeClass('error');
					$.ajax(
					{
						type	: 'POST',
						url		: '{{ route('configuration-items.validation') }}',
						data	: {
							'contract_id'	: contract_id,
							'tpda'			: tpda
						},
						success : function(data)
						{
							if (data['valid'] == true) 
							{
								flagExistItem = false;
								if ($('.class_countConcept').length>0)
								{
									$('.class_countConcept').each(function(i,v)
									{
										$('#body .tr').each(function()
										{
											item = $(this).find("[name='tpda[]']").val();
											if (item == pda) 
											{
												flagExistItem = true;
											}
										});
									});

									if (flagExistItem) 
									{
										swal('', 'El nombre de partida ya ha sido agregado anteriormente', 'error');
										$('.pdaContract').removeClass('valid').addClass('error');
									}
									else
									{
										addItem();
									}
								} 
								else
								{
									addItem();
								}
							}
							else
							{
								swal('', data['message'], 'error');
							}
						},
						error : function(data)
						{
							swal('','Ocurrió un error, por favor intente de nuevo.','error');
						},
					})
					.done(function()
					{
						$('#add').removeAttr('disabled');
					});
				}
			})
			.on('click','.delete-item', function()
			{
				swal(
				{
					title		: "",
					text		: "¿Desea eliminar el registro?",
					icon		: "warning",
					buttons		: ["Cancelar","OK"],
					dangerMode	: true,
				})
				.then((willDelete) => 
				{
					if (willDelete)
					{
						$(this).parents('.tr').remove();
						if($('.countConcept').length>0)
						{
							$('.countConcept').each(function(i,v)
							{
								$(this).html(i+1);
							});
						}
					}
				});
			})
			.on('click','.edit-item', function()
			{
				$('.edit-item').attr('disabled',true);
				idPda		=	$(this).parents('.tr').find('.cpda').val();
				pda			=	$(this).parents('.tr').find('.tpda').val();
				activity	=	$(this).parents('.tr').find('.tactivity').val();
				unit		=	$(this).parents('.tr').find('.tunit').val();
				pu			=	$(this).parents('.tr').find('.tpu').val();
				amount		=	$(this).parents('.tr').find('.tamount').val();
				
				$('.idPda').val(idPda);
				$('.pdaContract').removeClass('error').addClass('valid').val(pda);
				$('.activity').val(activity);
				$('.unit').val(unit);
				$('.pu').val(pu);
				$('.amount').val(amount);
				$(this).parents('.tr').remove();

				if($('.countConcept').length>0)
				{
					$('.countConcept').each(function(i,v)
					{
						$(this).html(i+1);
					});
				}
			})
			.on('change', '.js-contract', function()
			{
				if($('option:selected',this).val() != "" && $('option:selected',this).val() != undefined)
				{
					val = $('option:selected',this).val();
					$('[name="tpda"]').attr("data-validation-req-params", '{"contract_id":"'+val+'"}');
				}
			})
			.on('click','.btn-delete-form',function(e)
			{
				e.preventDefault();
				form = $(this).parents('form');
				swal({
					title		: "Limpiar formulario",
					text		: "¿Confirma que desea limpiar el formulario?",
					icon		: "warning",
					buttons		: ["Cancelar","OK"],
					dangerMode	: true,
				})
				.then((willClean) =>
				{
					if(willClean)
					{
						$('.removeselect').val(null).trigger('change');
						$("#body").empty();
						form[0].reset();
					}
					else
					{
						swal.close();
					}
				});
			});
		});

		function validation()
		{
			$.validate(
			{
				form	:	'#container-alta',
				modules	:	'security',
				onError :	function($form)
				{
					@if(!isset($items))
						if ($('[name="contractId"] option:selected').val() != "" && $('[name="contractId"] option:selected').val() != undefined && $('#body .tr').length > 0) 
						{
							$('[name="tpda"]').removeAttr('data-validation');
							$form.submit();
						}
						else
						{
							swal('', '{{ Lang::get("messages.form_error") }}', 'error');
						}
					@endif
				},
				onSuccess : function($form)
				{
					$('.contractNumber').removeClass('error');
					$('.contractName').removeClass('error');
					pdaItem			=	$('.tcpda').val();
					contractNumber	=	$('.contractNumber').val();
					contractName	=	$('.contractName').val();
					pda				=	$('.pdaContract').val();
					activity		=	$('.activity').val();
					unit			=	$('.unit').val();
					pu				=	$('.pu').val();
					amount			=	$('.amount').val();

					@if(!isset($items))
						count = $('#body .tr').length;
						
						if (count == 0)
						{
							swal('', 'Debe de agregar al menos una partida', 'error');
							return false;
						}
						else if (pda!="" || activity!="" || unit!="" || pu!="" || amount !="")
						{
							swal('', 'Tiene datos de Partida sin agregar', 'error');
							return false;
						}
						else
						{
							return true;
						}
					@endif					
				}
			});
		}

		function addItem()
		{
			$('.remove').removeClass('valid');
			$('.edit-item').removeAttr('disabled',true);
			
			countConcept = countConcept+1;

			@php 
				$modelHead = ["#", "Pda. Contrato", "Actividad", "Unidad", "PU", "Monto", "Acciones"];
				$modelBody = 
				[
					[
						"classEx" => "tr",
						[
							"content" => 
							[
								[
									"kind" 		=> "components.labels.label",
									"classEx"   => "class_countConcept"	
								]
							]
						],
						[
							"content" => 
							[
								[
									"kind" 		=> "components.labels.label",
									"classEx"   => "class_tpda"	
								],
								[
									"kind" 			=> "components.inputs.input-text",
									"attributeEx" 	=> "type=\"hidden\" name=\"tpda[]\"",
									"classEx"		=> "tpda"
								]
							]
						],
						[
							"content" => 
							[
								[
									"kind" 		=> "components.labels.label",
									"classEx"   => "class_tactivity"	
								],
								[
									"kind" 			=> "components.inputs.input-text",
									"attributeEx" 	=> "type=\"hidden\" name=\"tactivity[]\"",
									"classEx"		=> "tactivity"
								]
							]
						],
						[
							"content" => 
							[
								[
									"kind" 		=> "components.labels.label",
									"classEx"   => "class_tunit"	
								],
								[
									"kind" 			=> "components.inputs.input-text",
									"attributeEx" 	=> "type=\"hidden\" name=\"tunit[]\"",
									"classEx"		=> "tunit"
								]
							]
						],
						[
							"content" => 
							[
								[
									"kind" 		=> "components.labels.label",
									"classEx"   => "class_tpu"	
								],
								[
									"kind" 			=> "components.inputs.input-text",
									"attributeEx" 	=> "type=\"hidden\" name=\"tpu[]\"",
									"classEx"		=> "tpu"
								]
							]
						],
						[
							"content" => 
							[
								[
									"kind" 		=> "components.labels.label",
									"classEx"   => "class_tamount"	
								],
								[
									"kind" 			=> "components.inputs.input-text",
									"attributeEx" 	=> "type=\"hidden\" name=\"tamount[]\"",
									"classEx"		=> "tamount"
								]
							]
						],
						[
							"content" => 
							[
								[
									"kind" 			=> "components.buttons.button",
									"attributeEx" 	=> "type=\"button\" id=\"edit\"",
									"classEx"		=> "edit-item",
									"label"			=> "<span class=\"icon-pencil\"></span>",
									"variant"		=> "success"
								],
								[
									"kind" 			=> "components.buttons.button",
									"attributeEx" 	=> "type=\"button\"",
									"classEx"		=> "delete-item",
									"label"			=> "<span class=\"icon-x delete-span\"></span>",
									"variant"		=> "red"
								]
							]
						]
					]
				];
				$table = view('components.tables.alwaysVisibleTable',
				[
					"modelHead" => $modelHead,
					"modelBody" => $modelBody,
					"noHead"	=> true,
				])->render();
				
			@endphp

			table = '{!!preg_replace("/(\r)*(\n)*/", "", $table)!!}';
			tr_table = $(table);

			tr_table.find('.class_countConcept').text(countConcept);

			tr_table.find('.class_tpda').text(pda);
			tr_table.find('[name="tpda[]"]').val(pda);

			tr_table.find('.class_tactivity').text(activity);
			tr_table.find('[name="tactivity[]"]').val(activity);

			tr_table.find('.class_tunit').text(unit);
			tr_table.find('[name="tunit[]"]').val(unit);

			tr_table.find('.class_tpu').text(pu);
			tr_table.find('[name="tpu[]"]').val(pu);

			tr_table.find('.class_tamount').text(amount);
			tr_table.find('[name="tamount[]"]').val(amount);

			$("#table-show").removeAttr('style');

			$('#body').append(tr_table);
			$('.idPda').val("");
			$('.pdaContract').val("");
			$('.activity').val("");
			$('.unit').val("");
			$('.pu').val("");
			$('.amount').val("");
		}
	</script>
@endsection
