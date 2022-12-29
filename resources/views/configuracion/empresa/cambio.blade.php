@extends('layouts.child_module')
@section('data')
	@component("components.labels.title-divisor") EDITAR EMPRESA @endcomponent
	@component("components.labels.subtitle") Para editar la empresa es necesario colocar los siguientes campos: @endcomponent
	@component("components.forms.form", ["methodEx" => "PUT", "attributeEx" => "method=\"POST\" id=\"container-data\" action=\"".route('enterprise.update', $enterprise->id)."\"", "files" => true])
		<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mb-6 mt-4">
			@if(isset($enterprise->path) && $enterprise->path!='')
				<img class="w-full max-w-sm enterprise-module-logo" src="{{asset('images/enterprise').'/'.$enterprise->path}}">
				@php
					$validateImg = 'mime size';
				@endphp
			@else
				@php
					$validateImg = 'required mime size';
				@endphp
			@endif
		</div>
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
						value="{{ $enterprise->name }}" 
						data-validation="server"
						data-validation-url="{{ route('enterprise.validation') }}" 
						data-validation-req-params="{{ json_encode(array('oldEnterprise'=>$enterprise->id)) }}"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") RFC: @endcomponent
				@component("components.inputs.input-text")
					@slot("attributeEx")
						type="text" 
						name="rfc" 
						placeholder="Ingrese el RFC" 
						value="{{ $enterprise->rfc }}" 
						data-validation="server" 
						data-validation-url="{{ route('enterprise.validation-rfc') }}" 
						data-validation-req-params="{{ json_encode(array('oldEnterprise'=>$enterprise->id)) }}"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Detalles: @endcomponent
				@component("components.inputs.input-text")
					@slot("attributeEx")
						data-validation="required"
						placeholder="Ingrese los detalles"
						value="{{$enterprise->details}}"
						name="details"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Teléfono: @endcomponent
				@component("components.inputs.input-text")
					@slot("attributeEx")
						value="{{$enterprise->phone}}"
						name="phone"
						data-validation="required phone"
						placeholder="Ingrese el teléfono"
					@endslot
					@slot("classEx")
						phone
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Logo: @endcomponent
				@component("components.documents.upload-files", ["noDelete" => "true"])
					@slot("attributeExInput")
						name="path"
						data-validation="{{$validateImg}}"
						data-validation-max-size='4M'
						data-validation-allowing='jpg, png, gif'
						accept='.jpg, .gif, .png'
					@endslot
				@endcomponent
				@component("components.labels.label") Tamaño máximo 4MB. en formato JPG, PNG, GIF @endcomponent
			</div>
			<div class="col-span-2">
				@php
					$options = collect();
					foreach(App\CatTaxRegime::orderName()->where('moral','Sí')->get() as $t)
					{
						if($t->taxRegime==$enterprise->taxRegime)
						{
							$options = $options->concat([["value" => $t->taxRegime, "description" => $t->description, "selected" => "selected"]]);
						}
						else
						{
							$options = $options->concat([["value" => $t->taxRegime, "description" => $t->description]]);
						}
					}
				@endphp
				@component("components.labels.label") Régimen fiscal @endcomponent
				@component("components.inputs.select", ["options" => $options, "classEx" => "js-regime"])
					@slot("attributeEx")
						name="taxRegime" data-validation="required"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Calle: @endcomponent
				@component("components.inputs.input-text")
					@slot("attributeEx")
						name="address"
						data-validation="required"
						value="{{$enterprise->address}}"
						placeholder="Ingrese la calle"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Número: @endcomponent
				@component("components.inputs.input-text", ["classEx" => "number"])
					@slot("attributeEx")
						name="number"
						data-validation="required"
						value="{{$enterprise->number}}"
						placeholder="Ingrese el número"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Colonia: @endcomponent
				@component("components.inputs.input-text")
					@slot("attributeEx")
						name="colony"
						data-validation="required"
						value="{{$enterprise->colony}}"
						placeholder="Ingrese la colonia"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Código Postal: @endcomponent
				@php
					$options = collect();
					if(isset($enterprise->postalCode))
					{
						$options = $options->concat([["value" => $enterprise->postalCode, "selected" => "selected", "description" => $enterprise->postalCode]]);
					}
					$attributeEx = "name=\"postalCode\" id=\"cp\" data-validation=\"required\" multiple=\"multiple\"";
					$classEx = "postalcode";
				@endphp
				@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Ciudad: @endcomponent
				@component("components.inputs.input-text")
					@slot("attributeEx")
						name="city"
						data-validation="required"
						value="{{$enterprise->city}}"
						placeholder="Ingrese la ciudad"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@php
					$options = collect();
					if($enterprise->state_idstate)
					{
						$options = $options->concat([["value" => $enterprise->state_idstate, "description" => $enterprise->state->description, "selected" => "selected"]]);
					}
				@endphp
				@component("components.labels.label") Estado: @endcomponent
				@component("components.inputs.select", ["classEx" => "js-states", "options" => $options])
					@slot("attributeEx")
						name="state_idstate"
						data-validation="required"
					@endslot
				@endcomponent
			</div>
		@endcomponent
		@component("components.labels.title-divisor")
			Registro patronal
		@endcomponent
		@component("components.containers.container-form")
			<div class="col-span-2">
				@component("components.labels.label") # Registro: @endcomponent
				@component("components.inputs.input-text")
					@slot("attributeEx")
						type="text" 
						id="employer_register" 
						placeholder="Ingrese el número de registro"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Prima de riego: @endcomponent
				@component("components.inputs.input-text")
					@slot("attributeEx")
						type="text" 
						id="risk_number"
						placeholder="Ingrese la prima de riesgo"
					@endslot
				@endcomponent
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
				@component("components.inputs.select", ["options" => $options])
					@slot("attributeEx")
						type="text" id="position_risk"
					@endslot
					@slot("classEx")
						js-position
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2 md:col-span-4 grid justify-items-center md:justify-items-start">
				@component("components.buttons.button", ["variant" => "warning"])
					@slot("attributeEx")
						type="button"
					@endslot
					@slot("classEx")
						add
					@endslot
					<span class="icon-plus"></span>
					<span>Agregar</span>
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
				"Acciones"
			];
			
			foreach($enterprise->employerRegister as $er)
			{
				$body = 
				[
					[
						"content" =>
						[
							[
								"kind"			=> "components.labels.label",
								"classEx"		=> "employer_register",
								"label"			=> htmlentities($er->employer_register),
							],
							[
								"kind"			=> "components.inputs.input-text",
								"attributeEx"	=> "type=\"hidden\" readonly name=\"employer_register[]\" value=\"".htmlentities($er->employer_register)."\""
							],
							[
								"kind"			=> "components.inputs.input-text",
								"attributeEx"	=> "type=\"hidden\" name=\"rp_id[]\" value=\"".$er->id."\""
							]
						]
					],
					[
						"content" =>
						[
							[
								"kind"			=> "components.labels.label",
								"classEx"		=> "risk_number",
								"label"			=> $er->risk_number,
							],
							[
								"kind"			=> "components.inputs.input-text",
								"attributeEx"	=> "type=\"hidden\" readonly name=\"risk_number[]\" value=\"".$er->risk_number."\""
							]
						]
					],
					[
						"content" =>
						[
							[
								"kind"			=> "components.labels.label",
								"classEx"		=> "position_risk",
								"label"			=> $er->positionRisk->description,
							],
							[
								"kind"			=> "components.inputs.input-text",
								"attributeEx"	=> "type=\"hidden\" readonly value=\"".$er->positionRisk->description."\""
							],
							[
								"kind"			=> "components.inputs.input-text",
								"attributeEx"	=> "type=\"hidden\" readonly name=\"position_risk[]\" value=\"".$er->position_risk_id."\""
							]
						]
					],
					[
						"content" =>
						[
							[
								"kind"			=> "components.buttons.button",
								"variant"		=> "red",
								"classEx"		=> "delete-rp",
								"attributeEx"	=> "type=\"button\"",
								"label"			=> "<span class=\"icon-x\"></span>"
							]
						]
					]
				];
				$modelBody[] = $body;
			}
		@endphp
		@AlwaysVisibleTable(["classEx" => "rp-table", "classExBody" => "body_content", "title" => "Registro patronal", "modelHead" => $modelHead, "modelBody" => $modelBody]) @endAlwaysVisibleTable
		<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mb-6 mt-4">
			@component("components.buttons.button", ["attributeEx" => "type=\"submit\" name=\"enviar\""]) ACTUALIZAR @endcomponent
			@component('components.buttons.button', ["variant"=>"reset", "buttonElement"=>"a"])
				@slot('attributeEx')
					@if(isset($option_id)) 
						href="{{ url(getUrlRedirect($option_id)) }}" 
					@else 
						href="{{ url(getUrlRedirect($child_id)) }}"
					@endif 
				@endslot
				@slot('classEx')
					load-actioner
				@endslot
				REGRESAR
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
			$(document).on('click','.add',function()
			{
				$(".table").show();
				employer_register	= $('#employer_register').val();
				risk_number			= (isNaN(Number($('#risk_number').val())) ? 0 : Number($('#risk_number').val()));
				position_risk		= $('#position_risk').val();
				position_risk_text	= $('#position_risk option:selected').text()
				if(employer_register != '' && $('#risk_number').val() != '')
				{
					@php
						$modelHead = 
						[
							"# Registro",
							"Prima de riesgo",
							"Riesgo de puesto",
							"Acciones"
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
											"attributeEx"	=> "readonly type=\"hidden\" name=\"rp_id[]\" value=\"x\""
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
											"attributeEx"	=> "readonly type=\"hidden\"",
											"classEx"		=> "risk_class"
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
					tr.find("[name='employer_register[]']").val(employer_register);
					tr.find("[name='rp_id[]']").val('x');
					tr.find('.risk_number').text(risk_number);
					tr.find("[name='risk_number[]']").val(risk_number);
					tr.find(".position_risk").text(position_risk_text);
					tr.find(".risk_class").val(position_risk_text);
					tr.find("[name='position_risk[]']").val(position_risk);
					$('.body_content').append(tr);
					$('#risk_number').val('');
					$('#employer_register').val('');
					$('.js-position').val(null).trigger('change');
				}
				else
				{
					swal('','Por favor verifique que haya ingresado todos los campos','error');
				}
			})
			.on('change', 'input[type="file"]', function()
			{
				$(this).parents('div').addClass("image_success");
			})
			.on('click','.delete-rp',function()
			{
				$(this).parents('.tr').remove();
			})
			$('.postalcode,.phone').numeric(false);
			$('#risk_number').numeric({ altDecimal: ".", decimalPlaces: 2, negative:false});
		});
	</script>
@endsection
