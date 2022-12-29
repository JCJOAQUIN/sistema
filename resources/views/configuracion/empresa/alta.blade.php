@extends('layouts.child_module')
@section('data')
	@component("components.labels.title-divisor") NUEVA EMPRESA @endcomponent
	@component("components.labels.subtitle") Para agregar una empresa nueva es necesario colocar los siguientes campos: @endcomponent
	@component("components.forms.form", ["attributeEx" => "method=\"POST\" id=\"container-data\" action=\"".route('enterprise.store')."\"", "files" => true ])
		@component("components.containers.container-form")
			<div class="col-span-2">
				@component("components.labels.label")
					Nombre:
				@endcomponent
				@component("components.inputs.input-text")
					@slot("attributeEx")
						type="text" 
						name="name"
						placeholder="Ingrese el nombre" 
						data-validation="server" 
						data-validation-url="{{ route('enterprise.validation') }}"
					@endslot
					@slot("classEx")
						enterprise
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label")
					RFC:
				@endcomponent
				@component("components.inputs.input-text")
					@slot("attributeEx")
						type="text" 
						name="rfc"
						placeholder="Ingrese el RFC" 
						data-validation="server" 
						data-validation-url="{{ route('enterprise.validation-rfc') }}"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label")
					Detalles:
				@endcomponent
				@component("components.inputs.input-text")
					@slot("attributeEx")
						name="details"
						data-validation="required"
						placeholder="Ingrese los detalles"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label")
					Teléfono:
				@endcomponent
				@component("components.inputs.input-text")
					@slot("attributeEx")
						name="phone"
						data-validation="phone required"
						placeholder="Ingrese el teléfono"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label")
					Logo:
				@endcomponent
				@component("components.documents.upload-files", ["noDelete" => "true"])
					@slot("attributeExInput")
						data-validation="required mime size"
						data-validation-max-size="4M"
						data-validation-allowing="jpg, png, gif"
						accept=".jpg, .gif, .png"
						name="path"
					@endslot
				@endcomponent
				@component("components.labels.label")
					Tamaño máximo 4MB. en formato JPG, PNG, GIF
				@endcomponent
			</div>
			<div class="col-span-2">
				@php
					$options = collect();
					foreach(App\CatTaxRegime::orderName()->where('moral','Sí')->get() as $t)
					{
						$options = $options->concat([["value" => $t->taxRegime, "description" => $t->description]]);
					}
				@endphp
				@component("components.labels.label")
					Régimen fiscal:
				@endcomponent
				@component("components.inputs.select", ["options" => $options, "classEx" => "js-regime removeselect", "attributeEx" => "name=\"taxRegime\" data-validation=\"required\""]) @endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label")
					Calle:
				@endcomponent
				@component("components.inputs.input-text")
					@slot("attributeEx")
						name="address"
						data-validation="required"
						placeholder="Ingrese la calle"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label")
					Número:
				@endcomponent
				@component("components.inputs.input-text")
					@slot("attributeEx")
						name="number"
						data-validation="required"
						placeholder="Ingrese el número"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label")
					Colonia:
				@endcomponent
				@component("components.inputs.input-text")
					@slot("attributeEx")
						name="colony"
						data-validation="required"
						placeholder="Ingrese la colonia"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label")
					Código Postal:
				@endcomponent
				@php
					$options = collect();
					$attributeEx = "name=\"postalCode\" id=\"cp\" data-validation=\"required\" multiple=\"multiple\"";
					$classEx = "postalcode removeselect";
				@endphp
				@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label")
					Ciudad:
				@endcomponent
				@component("components.inputs.input-text")
					@slot("attributeEx")
						name="city"
						data-validation="required"
						placeholder="Ingrese la ciudad"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label")
					Estado:
				@endcomponent
				@php
					$options = collect();
				@endphp
				@component("components.inputs.select", ["options" => $options, "classEx" => "js-states removeselect"])
					@slot("attributeEx")
						data-validation="required"
						name="state_idstate"
					@endslot
				@endcomponent
			</div>
		@endcomponent
		@component("components.containers.container-form")
			<div class="col-span-2">
				@component("components.labels.label") # Registro: @endcomponent
				@component("components.inputs.input-text", ["attributeEx" => "type=\"text\" id=\"employer_register\" placeholder=\"Ingrese el número de registro\""]) @endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Prima de riesgo: @endcomponent
				@component("components.inputs.input-text", ["attributeEx" => "type=\"text\" id=\"risk_number\" placeholder=\"Ingrese la prima de riesgo\""]) @endcomponent
			</div>
			<div class="col-span-2">
				@php
					$options = collect();
					foreach(App\CatPositionRisk::orderName()->get() as $pos)
					{
						$options = $options->concat([["value" => $pos->id, "description" => $pos->description]]);
					}
				@endphp
				@component("components.labels.label") Riesgo de puesto: @endcomponent
				@component("components.inputs.select", 
					["options" => $options, 
						"attributeEx" => "name=\"position_risk\" id=\"position_risk\"", 
						"classEx" => "js-position laboral-data removeselect"]) 
				@endcomponent
			</div>
			<div class="col-span-2 md:col-span-4 grid justify-items-center md:justify-items-start">
				@component("components.buttons.button", 
					[
						"variant"		=> "warning",
						"classEx"		=> "add",
						"attributeEx"	=> "type=\"button\"",
						"label"			=> "<span class=\"icon-plus\"></span> Agregar"
					]) 
				@endcomponent
			</div>
		@endcomponent
		@php
			$modelBody = [];
			$modelHead =
			[
				"# Registro",
				"Prima de riesgo",
				"Riesgo de puesto",
				"Acción"
			];
		@endphp
			@AlwaysVisibleTable(["classExBody" => "body_content", "title" => "Registro patronal", "classEx" => "rp-table", "modelHead" => $modelHead, "modelBody" => []]) @endAlwaysVisibleTable
		<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mb-6 mt-4">
			@component("components.buttons.button", ["attributeEx" => "type=\"submit\""]) 
				REGISTRAR 
			@endcomponent
			@component("components.buttons.button", ["classEx" => "btn-delete-form", "variant" => "reset", "attributeEx" => "type=\"button\""])
				Borrar campos
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
		validation();
		@php
			$selects = collect(
				[
					[
						"identificator"				=> ".js-regime",
						"placeholder"				=> "Seleccione el régimen fiscal",
						"language"					=> "es",
						"maximumSelectionLength"	=> "1"
					],
					[
						"identificator"				=> ".js-position",
						"placeholder"				=> "Seleccione el riesgo de puesto",
						"language"					=> "es",
						"maximumSelectionLength"	=> "1"
					],
				]
			);
		@endphp
		@component("components.scripts.selects",["selects" => $selects])@endcomponent
		generalSelect({'selector': '.postalcode', 'model': 2});
		generalSelect({'selector': '.js-states', 'model': 31});
		$('#risk_number').numeric({ altDecimal: ".", decimalPlaces: 2, negative:false});
		$("form").submit(function() 
		{
			$(this).find('input[type="submit"]').prop("disabled", true);
		});
		$(document).on('click','.add',function()
		{
			$(".table").show();
			employer_register	= $('#employer_register').val();
			risk_number			= (isNaN(Number($('#risk_number').val())) ? 0 : Number($('#risk_number').val()));
			position_risk		= $('#position_risk option:selected').val();
			position_risk_text	= $('#position_risk option:selected').text();
			if(employer_register != '' && $('#risk_number').val() != '')
			{
				@php
					$modelHead = 
					[
						"# Registro",
						"Prima de riesgo",
						"Riesgo de puesto",
						"Acción"
					];
					$modelBody =
					[
						[
							[
								"content" =>
								[
									[
										"kind"			=> "components.labels.label",
										"classEx"		=> "employer_register",
										"label"			=> ""
									],
									[
										"kind"			=> "components.inputs.input-text",
										"attributeEx"	=> "readonly type=\"hidden\" name=\"employer_register[]\""
									],
									[
										"kind"			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"hidden\" name=\"rp_id[]\" value=\"x\""
									]
								]
							],
							[
								"content" =>
								[
									[
										"kind"			=> "components.labels.label",
										"classEx"		=> "risk_number",
										"label"			=> ""
									],
									[
										"kind"			=> "components.inputs.input-text",
										"attributeEx"	=> "readonly type=\"hidden\" name=\"risk_number[]\""
									]
								]
							],
							[
								"content" =>
								[
									[
										"kind"			=> "components.labels.label",
										"label"			=> "",
										"classEx"		=> "position_risk"
									],
									[
										"kind"			=> "components.inputs.input-text",
										"attributeEx"	=> "readonly type=\"hidden\" name=\"position_risk[]\""
									]
								]
							],
							[
								"content" =>
								[
									[
										"kind"			=> "components.buttons.button",
										"variant"		=> "red",
										"attributeEx"	=> "type=\"button\"",
										"classEx"		=> "delete-rp",
										"label"			=> "<span class=\"icon-x\"></span>"
									]
								]
							]
						]
					];
					$table = view("components.tables.alwaysVisibleTable",[
						"modelHead" => $modelHead,
						"modelBody" => $modelBody,
						"noHead"	=> true
					])->render();
					$table = html_entity_decode(preg_replace("/(\r)*(\n)*/", "", $table));
				@endphp
				
				table = '{!!preg_replace("/(\r)*(\n)*/", "", $table)!!}';
				tr = $(table);
				tr = rowColor('.body_content', tr);
				tr.find('.employer_register').text(employer_register);
				tr.find('[name="employer_register[]"]').val(employer_register);
				tr.find('.risk_number').text(risk_number);
				tr.find('[name="risk_number[]"]').val(risk_number);
				tr.find('.position_risk').text(position_risk_text);
				tr.find('[name="position_risk[]"]').val(position_risk);
				$('.body_content').append(tr);
				$('#risk_number').val('');
				$('#employer_register').val('');
				$('[name="position_risk"]').val(null).trigger('change');
			}
			else
			{
				swal('','Por favor verifique que haya ingresado todos los campos','error');
			}
		})
		.on('click', '.btn-delete-form', function(e)
		{
			e.preventDefault();
			form = $(this).parents('form');
			swal({
				title		: "Limpiar formulario",
				text		: "¿Confirma que desea limpiar el formulario?",
				icon		: "warning",
				buttons		: true,
				dangerMode	: true,
			})
			.then((willClean) =>
			{
				if(willClean)
				{
					$('.docs-p').parents('div').removeClass('image_success');
					$('.uploader-content,.docs-p').removeClass('image_success has-success');
					$('.removeselect').val(null).trigger('change');
					$('.body_content').empty();
					$('.path').empty();
					form[0].reset();
				}
				else
				{
					swal.close();
				}
			});
		})
		.on('click','.delete-rp',function()
		{
			$(this).parents('.tr').remove();
		})
		.on('change', 'input[type="file"]', function(){
			$(this).parents('div').addClass("image_success");
		});	
		$('.phone').numeric(false);
		$('#risk_number').numeric({ altDecimal: ".", decimalPlaces: 2, negative:false});
	});
	function validation()
	{
		$.validate(
		{
			form   : '#container-data',
			modules: 'file security',
			onError: function($form)
			{
				swal('', '{{ Lang::get("messages.form_error") }}', 'error');
				return false;
			}
		});
	}
</script>
@endsection
