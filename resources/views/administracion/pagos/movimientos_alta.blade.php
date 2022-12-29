@extends('layouts.child_module')
@section('data')
	<div class="text-center my-8">
		A continuación podrá dar de alta los movimientos con los pagos registrados en el sistema:
	</div>
	@component("components.forms.form",["attributeEx" => "method=\"POST\" id=\"container-alta\" action=\"".route('payments.movement.store')."\""])
		@component('components.labels.title-divisor') DATOS GENERALES DE LOS MOVIMIENTOS @endcomponent
		@component('components.containers.container-form')
			<div class="col-span-2">
				@component('components.labels.label') Empresa: @endcomponent
				@php
					$options = collect();
					foreach(App\Enterprise::orderBy('name','asc')->whereIn('id',Auth::user()->inChargeEnt(92)->pluck('enterprise_id'))->get() as $enterprise)
					{
						$description = strlen($enterprise->name) >= 35 ? substr(strip_tags($enterprise->name),0,35)."..." : $enterprise->name;
						if (isset($request) && $request->idEnterprise == $enterprise->id) 
						{
							$options = $options->concat([["value"=>$enterprise->id, "selected"=>"selected", "description"=>$description]]);
						}
						else 
						{
							$options = $options->concat([["value"=>$enterprise->id, "description"=>$description]]);
						}
					}
					$attributeEx =  "name=\"enterprise_id\"";
					$classEx = "custom-select js-enterprise";
				@endphp
				@component('components.inputs.select',["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])  @endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Clasificación del gasto: @endcomponent
				@php
					$options = collect();

					$attributeEx =  "name=\"account\"";
					$classEx = "custom-select js-account";
				@endphp
				@component('components.inputs.select',["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])  @endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Tipo de movimiento: @endcomponent
				@php
				$options = collect(
					[
						['value'=>'Ingreso', 'description'=>'Ingreso'], 
						['value'=>'Devolución', 'description'=>'Devolución'], 
						['value'=>'Rechazos', 'description'=>'Rechazos'], 
						['value'=>'Egreso', 'description'=>'Egreso']
					]
				);
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
						placeholder="Ingrese el importe"
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
						placeholder="Ingrese una descripción"
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
						placeholder="Ingrese un comentario"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2 md:col-span-4 grid justify-items-center md:justify-items-start">
				@component('components.buttons.button', ["variant" => "warning"])
					@slot('attributeEx')
						type="button"
						name="add"
						id="add"
					@endslot
					@slot('classEx')
						add
					@endslot
					<span class="icon-plus"></span>
					<span>Agregar</span>
				@endcomponent
			</div>
		@endcomponent
		@php
		$modelHead = 
		[
			[
				["value" => "Empresa"],
				["value" => "Cuenta"],
				["value" => "Fecha"],
				["value" => "Importe"],
				["value" => "Descripción"],
				["value" => "Comentarios"],
				["value" => "Acción"]
			]
		];
			$modelBody = [];
		@endphp

		@component("components.tables.table",[
			"modelHead"	=> $modelHead,
			"modelBody"	=> $modelBody
		])
			@slot("classEx")
				text-center
			@endslot
			@slot("attributeEx")
				id="table"
			@endslot
			@slot("attributeExBody")
				id="body"
			@endslot
		@endcomponent

		<div class="content-start items-start flex flex-row flex-wrap justify-center w-full mt-4">
			@component("components.buttons.button")
				@slot("classEx")
					btn-green
				@endslot
				@slot("attributeEx")
				name="save"
				type="submit"
				value="GUARDAR MOVIMIENTOS"
				@endslot
				GUARDAR MOVIMIENTOS
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
		$.validate(
		{
			form: '#container-alta',
			onSuccess : function($form)
			{
				countbody = $('#body .tr').length;
				if(countbody>0)
				{
					return true;
				}
				else
				{
					swal('', 'Debe agregar al menos un movimiento', 'error');
					return false;
				}
			}
		});
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
		countbody = $('#body .tr').length;
		if (countbody <= 0) 
		{
			$('#table').hide();
		}
		else
		{
			$('#table').show();
		}
		$('.amount',).numeric({ altDecimal: ".", decimalPlaces: 2, negative : false });
		
		$(function(){
			$('.datepicker').datepicker(
			{
				dateFormat : 'dd-mm-yy',
				maxDate: 0
			});
		});
	});
	$(document).on('click','.add',function()
	{
		$('.js-enterprise, .js-account, .js-type').parent().find('.form-error').remove();
		date 			= $('#datmove').val();
		amount 			= $('#imove').val();
		description 	= $('#desmove').val().trim();
		comment  		= $('textarea[id="comove"]').val().trim();
		accountid 		= $('select[name="account"] option:selected').val();
		enterpriseid 	= $('select[name="enterprise_id"] option:selected').val();
		account 		= $('select[name="account"] option:selected').text();
		enterprise 		= $('select[name="enterprise_id"] option:selected').text();
		type 			= $('select[name="type"] option:selected').val();
		num = parseFloat(amount);
		if(date == "" || amount == "" || description == "" || comment == "" || account == "" || enterprise == "" || account == undefined || enterprise == undefined || type == "" || type ==  undefined)
		{
			if (date == "") 
			{
				$('#datmove').addClass('error');
			}
			else
			{
				$('#datmove').addClass('valid');	
			}
			if (amount == "" || num <= 0) 
			{
				$('#imove').addClass('error');
			}
			else
			{
				$('#imove').addClass('valid');	
			}
			if (description == "") 
			{
				$('#desmove').addClass('error');
			}
			else
			{
				$('#desmove').addClass('valid');	
			}
			if (comment == "") 
			{
				$('#comove').addClass('error');
			}
			else
			{
				$('#comove').addClass('valid');	
			}
			if (enterprise == undefined || enterprise == "") 
			{
				$('.js-enterprise').parent().append('<span class="help-block form-error">Este campo es obligatorio</span>');
			}
			else
			{
				$('.js-enterprise').parent().find('.form-error').remove();
			}
			if (account == undefined || account == "") 
			{
				$('.js-account').parent().append('<span class="help-block form-error">Este campo es obligatorio</span>');
			}
			else
			{
				$('.js-account').parent().find('.form-error').remove();
			}
			if (type == "" || type == undefined) 
			{
				$('.js-type').parent().append('<span class="help-block form-error">Este campo es obligatorio</span>');
			}
			else
			{
				$('.js-type').parent().find('.form-error').remove();
			}
			swal('', 'Debe ingresar todos los campos requeridos', 'error');
		}
		else if (num <= 0) 
		{
			$('#imove').addClass('error');
			swal('', 'El importe ingresado es incorrecto', 'error');
		}
		else
		{
			@php
				$modelHead = [
					["value" => "Empresa", "show" => "true"],
					["value" => "Cuenta", "show" => "true"],
					["value" => "Fecha"],
					["value" => "Importe"],
					["value" => "Descripción"],
					["value" => "Comentarios"],
					["value" => "Acción"],
				];

				$modelBody =
				[
					[
						"classEx" => "tr",
						[
							"show" => "true",
							"content" =>
							[
								[
									"kind"		=> "components.labels.label",
									"classEx"	=> "enterprise",
									"label"		=> ""
								],
								[
									"kind"			=> "components.inputs.input-text",
									"classEx"		=> "enterpriseid",
									"attributeEx"	=> "type=\"hidden\" name=\"enterpriseid[]\"",
								],
								[
									"kind"			=> "components.inputs.input-text",
									"classEx"		=> "type",
									"attributeEx"	=> "type=\"hidden\" name=\"type[]\"",
								]
							]
						],
						[
							"show" => "true",
							"content" =>
							[
								[
									"kind"			=> "components.labels.label",
									"classEx"		=> "account",
									"label"			=> "",
								],
								[
									"kind"			=> "components.inputs.input-text",
									"classEx"		=> "accountid",
									"attributeEx"	=> "type=\"hidden\" name=\"accountid[]\"",
								]
							]
						],
						[
							"content" =>
							[
								[
									"kind"			=> "components.labels.label",
									"classEx"		=> "date",
									"label"			=> "",
								],
								[
									"kind"			=> "components.inputs.input-text",
									"classEx"		=> "mdate",
									"attributeEx"	=> "type=\"hidden\" name=\"date[]\"",
								]
							]
						],
						[
							"content" =>
							[
								[
									"kind"			=> "components.labels.label",
									"classEx"		=> "amount",
									"label"			=> "",
								],
								[
									"kind"			=> "components.inputs.input-text",
									"classEx"		=> "mamount",
									"attributeEx"	=> "type=\"hidden\" name=\"amount[]\"",
								]
							]
						],
						[
							"content" =>
							[
								[
									"kind"			=> "components.labels.label",
									"classEx"		=> "description",
									"label"			=> "",
								],
								[
									"kind"			=> "components.inputs.input-text",
									"classEx"		=> "mdescription",
									"attributeEx"	=> "type=\"hidden\" name=\"description[]\"",
								]
							]
						],
						[
							"content" =>
							[
								[
									"kind"			=> "components.labels.label",
									"classEx"		=> "comment",
									"label"			=> "",
								],
								[
									"kind"			=> "components.inputs.input-text",
									"classEx"		=> "mcomment",
									"attributeEx"	=> "type=\"hidden\" name=\"commentaries[]\"",
								]
							]
						],
						[
							"content" =>
							[
								[
									"kind" => "components.buttons.button",
									"attributeEx" => "type=\"button\"",
									"classEx" => "delete-item",
									"variant" => "dark-red",
									"label" => "<span class=icon-x></span>"
								]
							]
						]
					]
				];
				$table_body = view("components.tables.table", [
					"modelHead" => $modelHead,
					"modelBody" => $modelBody,
					"noHead"	=> "true"						
				])->render();
				
				$table_body 	= html_entity_decode(preg_replace("/(\r)*(\n)*/", "", $table_body));
			@endphp

			table_body = '{!!preg_replace("/(\r)*(\n)*/", "", $table_body)!!}';
			row = $(table_body);
			rowColor('#body',row);
			row.find('td').each(function()
			{
				$(this).find('.enterprise').text(enterprise);
				$(this).find('.enterpriseid').val(enterpriseid);
				$(this).find('.type').val(type);
				$(this).find('.accountid').val(accountid);
				$(this).find('.account').text(account);
				$(this).find('.date').text(date);
				$(this).find('.mdate').val(date);
				$(this).find('.mamount').val(amount);
				$(this).find('.amount').text(amount);
				$(this).find('.mdescription').val(description);
				$(this).find('.description').text(description);
				$(this).find('.mcomment').val(comment);
				$(this).find('.comment').text(comment);
			})

			$('#body').append(row);
			$('#datmove,#desmove,#imove').removeClass('error').removeClass('valid').val('');
			$('textarea[id="comove"]').removeClass('error').removeClass('valid').val('');
			$('select[name="enterprise_id"],select[name="account"],select[name="type"]').removeClass('error').removeClass('valid').val(null).trigger('change');	
		}
		countbody = $('#body .tr').length;
		if (countbody <= 0) 
		{
			$('#table').hide();
		}
		else
		{
			$('#table').show();
		}
	})
	.on('click','.delete-item',function()
	{
		$(this).parents('.tr').remove();
		countbody = $('#body .tr').length;
		if (countbody <= 0) 
		{
			$('#table').hide();
		}
	})
</script>
@endsection
