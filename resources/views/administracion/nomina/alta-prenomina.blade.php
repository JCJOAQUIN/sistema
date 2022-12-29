@extends('layouts.child_module')

@section('data')
	@component("components.labels.title-divisor") Prenóminas automáticas @endcomponent
	@php
		$body		= [];
		$modelBody 	= [];
		$modelHead 	= 
		[
			[
				["value" => "Título"],
				["value" => "Fecha"],
				["value" => "Tipo"],
				["value" => "Acciones"]
			]
		];
		foreach(App\Prenomina::where('status',1)->get() as $p)
		{
			$body = 
			[
				[
					"content" => [["label" => htmlentities($p->title)]]
				],
				[
					"content" => [["label" => Carbon\Carbon::createFromFormat('Y-m-d',$p->datetitle)->format('d-m-Y')]]
				],
				[
					"content" => [["label" => $p->typePayroll->description]]
				],
				[
					"content" => 
					[
						[
							"kind" 			=> "components.buttons.button",
							"variant"		=> "success",
							"attributeEx"	=> "href=\"".route('nomina.prenomina-edit',$p->idprenomina)."\"",
							"buttonElement" => "a",
							"label" 		=> "<span class=\"icon-pencil\"></span>"
						],
						[
							"kind"			=> "components.buttons.button",
							"variant"		=> "red",
							"attributeEx"	=> "href=\"".route('nomina.prenomina-delete',$p->idprenomina)."\"",
							"buttonElement"	=> "a",
							"classEx"		=> "delete-prenomina",
							"label"			=> "<span class=\"icon-x\"></span>"
						]
					]
				]
			];
			$modelBody[] = $body;
		}
	@endphp
	@Table(["modelHead" => $modelHead, "modelBody" => $modelBody]) @endTable
	@component('components.forms.form', ["attributeEx" => "method=\"POST\" id=\"container-alta\" action=\"".route('nomina.prenomina-create.store')."\""])
		@if(isset($prenomina))
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="hidden" name="prenom_id" value="{{$prenomina->idprenomina}}"
				@endslot
			@endcomponent
		@endif
		@component("components.labels.title-divisor") Datos de la Solicitud @endcomponent
		@component("components.containers.container-form")
			<div class="col-span-2">
				@component("components.labels.label") Título: @endcomponent
				@component("components.inputs.input-text", ["classEx" => "removeselect"]) 
					@slot("attributeEx") 
						type="text" name="title" placeholder="Ingrese el título" data-validation="required" @if(isset($prenomina))value="{{$prenomina->title}}" @endif 
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Fecha: @endcomponent
				@component("components.inputs.input-text", ["classEx" => "removeselect datepicker"]) 
					@slot("attributeEx") 
						type="text" name="datetitle" data-validation="required" placeholder="Ingrese la fecha" readonly="readonly" @if(isset($prenomina)) value="{{ Carbon\Carbon::createFromFormat('Y-m-d',$prenomina->datetitle)->format('d-m-Y') }}" @endif 
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Tipo de Nómina: @endcomponent
				@php
					$options = collect();
					foreach(App\CatTypePayroll::orderName()->get() as $t)
					{
						if(isset($prenomina) && $prenomina->idCatTypePayroll == $t->id)
						{
							$options = $options->concat([["value" => $t->id, "selected" => "selected",  "description" => $t->description]]);
						}
						else
						{
							$options = $options->concat([["value" => $t->id, "description" => $t->description]]);
						}
					}
				@endphp
				@component("components.inputs.select", ["options" => $options, "classEx" => "js-typepayroll removeselect"])
					@slot("attributeEx")
						title="Tipo de nómina" name="type_payroll" data-validation="required"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Solicitante: @endcomponent
				@component("components.inputs.select", ["options" => [], "classEx" => "js-users removeselect"])
					@slot("attributeEx")
						name="userid" data-validation="required"
					@endslot
				@endcomponent
			</div>
		@endcomponent
		@component("components.labels.title-divisor") Selección masiva <span class="help-btn" id="help-btn-massive-employee"></span> @endcomponent
		@php
			$buttons = 
			[
				"separator"	=> 
				[
					[
						"kind" 			=> "components.buttons.button-approval",
						"label"			=> "coma (,)",
						"attributeEx"	=> "value=\",\" name=\"separator\" id=\"separatorComa\""
					],
					[
						"kind"			=> "components.buttons.button-approval",
						"label"			=> "Punto y coma (;)",
						"attributeEx"	=> "value=\";\" name=\"separator\" id=\"separatorPuntoComa\""
					]
				],
				"buttonEx" => 
				[
					[
						"kind" 			=> "components.buttons.button",
						"label"			=> "SELECCIONAR EMPLEADOS",
						"variant"		=> "primary",
						"attributeEx"	=> "type=\"button\" id=\"update_to_select\"",
						"classEx"		=> "w-max my-2"
					]
				]
			];
		@endphp
		@component("components.documents.select_file_csv",
			[
				"attributeEx" 		=> "id=\"container-data-2\"",
				"attributeExInput"	=> "name=\"csv_file\" id=\"csv\"", "buttons" => $buttons
			])
		@endcomponent
		@component("components.labels.title-divisor") Buscar Empleado <span class="help-btn" id="help-btn-search-employee"></span> @endcomponent
		@component("components.inputs.input-search", 
			[
				"attributeExInput" 	=> "type=\"text\" title=\"Escriba aquí\" name=\"searchEmployee\" id=\"input-search\" placeholder=\"Ingrese un nombre de empleado\"",
				"attributeExButton" => "id=\"search-btn\" type=\"button\" title=\"Buscar\""
			])
		@endcomponent
		<div id="result"></div>
		<div class="alert alert-danger hidden" id="error_request" role="alert">SIN RESULTADOS.</div>
		@component("components.labels.title-divisor") Datos del Empleado <span class="help-btn" id="help-btn-add-employee"></span> @endcomponent
		@component('components.labels.not-found', ["variant" => "note"])
			La plataforma permite modificar los campos «Tipo» y «Categoría» de manera masiva.
			Para ello, primero es necesario seleccionar los empleados dando clic en el botón <span class="icon-check"></span>.
			Posteriormente deberá seleccionar la nueva opción de la lista mostrada a continuación.
			Al hacer esto, verá el cambio reflejado en todos los empleados seleccionados.
		@endcomponent
		@component("components.containers.container-form")
			<div class="col-span-2">
				@component('components.labels.label') Seleccione el tipo: @endcomponent
				@php
					$optionType		= [];
					$optionType[]	= ["value" => "1", "description" => "Obra"];
					$optionType[]	= ["value" => "2", "description" => "Administrativa"];
				@endphp
				@component('components.inputs.select', ["options" => $optionType])
					@slot('attributeEx')
						title="Obra/Administrativa" id="change_category" multiple="multiple"
					@endslot
					@slot('classEx')
						js-type-category
						removeselect
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Seleccione la categoría: @endcomponent
				@php
					$optionCategory   =	[];
					$optionCategory[] =	["value" => "1", "description" => "Fiscal"];
					$optionCategory[] =	["value" => "2", "description" => "No Fiscal"];
					$optionCategory[] =	["value" => "3", "description" => "Fiscal/No Fiscal"];
					$optionCategory[] =	["value" => "4", "description" => "Nom35"];
					$optionCategory[] =	["value" => "5", "description" => "Fiscal/Nom35"];
				@endphp
				@component('components.inputs.select', ["options" => $optionCategory])
					@slot('attributeEx')
						title="Fiscal/No Fiscal" id="change_type" multiple="multiple"
					@endslot
					@slot('classEx')
						js-category
						removeselect
					@endslot
				@endcomponent
			</div>
		@endcomponent
		<div class="my-4">
			@component('components.buttons.button', ["variant" => "success"])
				@slot('classEx')
					checkbox
					request-validate
					flag
				@endslot
				@slot('attributeEx')
					type="button"
					id="type_check"
					name="select_all"
					value="1"
				@endslot
				Seleccionar todo (página actual)				
			@endcomponent
			@php
				$body		= [];
				$modelBody	= [];
				$modelHead	= [
					[
						["value" => ""],
						["value" => "Nombre del Empleado"],
						["value" => "Tipo"],
						["value" => "Fiscal/No Fiscal/Nom35"],
						["value" => "Acciones"]
					]
				];

				if(isset($prenomina))
				{
					foreach($prenomina->employee->sortBy(function($employee){ return $employee->last_name.' '.$employee->scnd_last_name.' '.$employee->name; }) as $emp)
					{
						$dataObra	= App\PrenominaEmployee::where('idprenomina',$prenomina->idprenomina)->where('idreal_employee',$emp->id)->first();
						$selectType	= "";
						$selectType	.= '<select class="border rounded py-2 px-3 m-px w-full" title="Tipo de nómina" name="type[]" data-validation="required">';
							if($prenomina->kind==1)
							{
								$selectType .= '<option value="1" selected="selected">Obra</option>';
							}
							else
							{
								$selectType .= '<option value="1">Obra</option>';
							}
							if($prenomina->kind==2)
							{
								$selectType .= '<option value="2" selected="selected">Administrativa</option>';
							}
							else
							{
								$selectType .= '<option value="2">Administrativa</option>';
							}
						$selectType .= '</select>';

						$fechaActual	= date("Y-m-d H:i:s");
						$selectFiscal	= "";
						$selectFiscal	.= '<select class="border rounded py-2 px-3 m-px w-full" title="Fiscal/No Fiscal" name="fiscal[]" data-validation="required">';
							if ($emp->workerDataVisible->first()->downDate != '')
							{
								if (new \DateTime($emp->workerDataVisible->first()->downDate) > new \DateTime($emp->workerDataVisible->first()->imssDate)) 
								{
									if ($emp->workerDataVisible->first()->regime_id == '09') 
									{
										$selectFiscal .= '<option value="1" selected="selected">Fiscal</option>';
										$selectFiscal .= '<option value="2">No Fiscal</option>';
										$selectFiscal .= '<option value="3">Fiscal/No Fiscal</option>';
										$selectFiscal .= '<option value="4">Nom35</option>';
										$selectFiscal .= '<option value="5">Fiscal/Nom35</option>';
									}
									else
									{
										$datetime1	= date_create($emp->workerDataVisible->first()->downDate);
										$datetime2	= date_create($fechaActual);
										$interval	= date_diff($datetime1, $datetime2);
										$difference = $interval->format('%a');

										if($difference <= 10)
										{
											$selectFiscal .= '<option value="1" selected="selected">Fiscal</option>';
											$selectFiscal .= '<option value="2">No Fiscal</option>';
											$selectFiscal .= '<option value="3">Fiscal/No Fiscal</option>';
											$selectFiscal .= '<option value="4">Nom35</option>';
											$selectFiscal .= '<option value="5">Fiscal/Nom35</option>';
										}
										else
										{
											$selectFiscal .= '<option value="2" selected="selected">No Fiscal</option>';
										}
									}
								}
								else
								{
									$selectFiscal .= '<option value="1" selected="selected">Fiscal</option>';
									$selectFiscal .= '<option value="2">No Fiscal</option>';
									$selectFiscal .= '<option value="3">Fiscal/No Fiscal</option>';
									$selectFiscal .= '<option value="4">Nom35</option>';
									$selectFiscal .= '<option value="5">Fiscal/Nom35</option>';
								}
							}
							if ($emp->workerDataVisible->first()->downDate == '')
							{
								if ($emp->workerDataVisible->first()->imssDate == '') 
								{
									if ($emp->workerDataVisible->first()->regime_id == '09') 
									{
										$selectFiscal .= '<option value="1" selected="selected">Fiscal</option>';
										$selectFiscal .= '<option value="2">No Fiscal</option>';
										$selectFiscal .= '<option value="3">Fiscal/No Fiscal</option>';
										$selectFiscal .= '<option value="4">Nom35</option>';
										$selectFiscal .= '<option value="5">Fiscal/Nom35</option>';
									}
									else 
									{
										$selectFiscal .= '<option value="2" selected="selected">No Fiscal</option>';
									}
								}
								else
								{
									$selectFiscal .= '<option value="1" selected="selected">Fiscal</option>';
									$selectFiscal .= '<option value="2">No Fiscal</option>';
									$selectFiscal .= '<option value="3">Fiscal/No Fiscal</option>';
									$selectFiscal .= '<option value="4">Nom35</option>';
									$selectFiscal .= '<option value="5">Fiscal/Nom35</option>';
								}
							}
						$selectFiscal .= '</select>';

						$body = [ "classEx" => "tr_payroll",
							[
								"content" => 
								[
									"kind" 				=> "components.inputs.checkbox",
									"attributeEx" 		=> " id=\"type_check_$emp->id\" type=\"checkbox\" name=\"type_check[]\" value=\"".$emp->id."\"",
									"classEx" 			=> "checkbox",
									"label"				=> "<span class=\"icon-check\"></span>",
									"classExContainer"	=> "my-4",
									"classExLabel"		=> "request-validate"
								]
							],
							[
								"content" => 
								[
									[
										"kind"			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"hidden\" name=\"idrealEmployee[]\" value=\"".$emp->id."\"",
										"classEx"		=> "idemployee-table-prenomina" 
									],
									[
										"kind"			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"hidden\" name=\"idworkerData[]\" value=\"".$emp->workerDataVisible->first()->id."\"",
										"classEx"		=> "idworkingdata-table-prenomina" 
									],
									[
										"kind"			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"hidden\" value=\"".$emp->last_name.' '.$emp->scnd_last_name.' '.$emp->name."\"",
										"classEx"		=> "fullname-table-prenomina" 
									],
									[
										"label" => $emp->last_name.' '.$emp->scnd_last_name.' '.$emp->name
									]
								]
							],
							[
								"content" => 
								[
									"label" => $selectType
								]
							],
							[
								"content" => 
								[
									"label" => $selectFiscal
								]
							],
							[
								"content" => 
								[
									[
										"kind" 			=> "components.buttons.button",
										"variant" 		=> "secondary",
										"attributeEx"	=> "title=\"Ver Datos\" type=\"button\" data-toggle=\"modal\" data-target=\"#myModal\"",
										"classEx"		=> "btn-view-user",
										"label"			=> "<span class=\"icon-search\">"
									],
									[
										"kind" 			=> "components.buttons.button",
										"variant" 		=> "success",
										"attributeEx"	=> "title=\"Editar Datos\" type=\"button\" data-toggle=\"modal\" data-target=\"#myModal\"",
										"classEx"		=> "btn-edit-user",
										"label"			=> "<span class=\"icon-pencil\">"
									],
									[
										"kind" 			=> "components.buttons.button",
										"variant" 		=> "red",
										"attributeEx"	=> "type=\"button\"",
										"classEx"		=> "btn-delete-tr",
										"label"			=> "<span class=\"icon-x\">"
									]
								]
							]
						];
						$modelBody[] = $body;
					}
				}
			@endphp
			@component('components.tables.table', [
				"modelHead" => $modelHead,
				"modelBody" => $modelBody
			])
				@slot('classEx')
					table
				@endslot
				@slot('attributeEx')
					id="table"
				@endslot
				@slot('classExBody')
					request-validate
				@endslot
				@slot('attributeExBody')
					id="body-payroll"
				@endslot	
			@endcomponent
		</div>
		<div class="w-full mt-4 grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mb-6">
			@component('components.buttons.button', [ "variant" => "primary"])
				@slot('attributeEx')
					type="submit" name="enviar" value="ENVIAR SOLICITUD"
				@endslot
					ENVIAR SOLICITUD
			@endcomponent
			@component('components.buttons.button', [ "variant" => "reset"])
				@slot('attributeEx')
					type="reset" name="borra" value="Borrar campos"
				@endslot
				@slot('classEx')
					btn-delete-form
				@endslot
					Borrar campos
			@endcomponent
		</div>
		@component('components.modals.modal',[ "variant" => "large" ])
			@slot('id')
				myModal
			@endslot
			@slot('attributeEx')
				tabindex="-1"
			@endslot
			@slot('modalHeader')
				@component('components.buttons.button')
					@slot('attributeEx')
						type="button"
						data-dismiss="modal"
					@endslot
					@slot('classEx')
						close
					@endslot
					<span aria-hidden="true">&times;</span>
				@endcomponent
			@endslot
			@slot('modalBody')
				
			@endslot
		@endcomponent
		<div id="request"></div>
	@endcomponent
@endsection

@section('scripts')
<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
<script src="{{ asset('js/jquery-ui.js') }}"></script>
<script src="{{ asset('js/jquery.numeric.js') }}"></script>
<script src="{{ asset('js/datepicker.js') }}"></script>
<script src="{{asset('js/jquery.mask.js')}}"></script>
<script>
	labelVal	= '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="17" viewBox="0 0 20 17"><path d="M10 0l-5.2 4.9h3.3v5.1h3.8v-5.1h3.3l-5.2-4.9zm9.3 11.5l-3.2-2.1h-2l3.4 2.6h-3.5c-.1 0-.2.1-.2.1l-.8 2.3h-6l-.8-2.2c-.1-.1-.1-.2-.2-.2h-3.6l3.4-2.6h-2l-3.2 2.1c-.4.3-.7 1-.6 1.5l.6 3.1c.1.5.7.9 1.2.9h16.3c.6 0 1.1-.4 1.3-.9l.6-3.1c.1-.5-.2-1.2-.7-1.5z"/></svg> <span>Seleccione un archivo&hellip;</span>';
	$(document).ready(function()
	{
		@php
			$selects = collect([
				[
					"identificator"				=> ".js-type-category",
					"placeholder"				=> "Seleccione el tipo",
					"maximumSelectionLength"	=> "1"
				],
				[
					"identificator"				=> ".js-category",
					"placeholder"				=> "Seleccione la categoria",
					"maximumSelectionLength"	=> "1"
				],
				[
					"identificator"				=> ".js-enterprises",
					"placeholder"				=> "Seleccione la empresa",
					"maximumSelectionLength"	=> "1"
				],
				[
					"identificator"				=> ".js-accounts",
					"placeholder"				=> "Seleccione la clasificación de gasto",
					"maximumSelectionLength"	=> "1"
				],
				[
					"identificator"				=> ".js-typepayroll",
					"placeholder"				=> "Seleccione el tipo de nómina",
					"maximumSelectionLength"	=> "1"
				]
			]);
		@endphp
		@component('components.scripts.selects',["selects" => $selects]) @endcomponent
		generalSelect({'selector':'.js-users','model':13});
		validation();
		$('#separatorComa').prop('checked',true);
		$(function() 
		{
			$(".datepicker").datepicker({ dateFormat: "dd-mm-yy" });
		});
		$(document).on('click','.btn-delete-form',function(e)
		{
			e.preventDefault();
			form = $(this).parents('form');
			swal({
				title       : "Limpiar formulario",
				text        : "¿Confirma que desea limpiar el formulario?",
				icon        : "warning",
				buttons     : ["Cancelar","OK"],
				dangerMode  : true,
			})
			.then((willClean) =>
			{
				if(willClean)
				{
					$('#body-payroll').html('');
					$('.removeselect').val(null).trigger('change');
					$('.result').hide();
					$('#csv').parent('.uploader-content').removeClass('image_success');
					form[0].reset();
				}
				else
				{
					swal.close();
				}
			});
		})
		.on('click','#search-btn', function()
		{
			search(1)
		})
		.on('click','.paginate a', function(e)
		{
			e.preventDefault();
			href   = $(this).attr('href');
			url    = new URL(href);
			params = new URLSearchParams(url.search);
			page   = params.get('page');
			search(page)
		})
		.on('click','.add-user',function()
		{
			$('.add-user').attr('disabled','disabled');
			idemployee 		= $(this).parents('.tr_employee').find('.id-employee-table').val();
			idworkerdata 	= $(this).parents('.tr_employee').find('.id-workerdata-table').val();
			fullname 		= $(this).parents('.tr_employee').find('.fullname-table').val();
			filter 			= $(this).parents('.tr_employee').find('.filter').val();

			if(filter == 0) 
			{
				@php
					$body		= [];
					$modelBody	= [];
					$modelHead	= [
						[
							["value" => ""],
							["value" => "Nombre del Empleado"],
							["value" => "Tipo"],
							["value" => "Fiscal/No Fiscal/Nom35"],
							["value" => "Acciones"]
						]
					];
					$body = [ "classEx" => "tr_payroll",
						[
							"content" => 
							[
								"kind"				=> "components.inputs.checkbox",
								"attributeEx"		=> "type=\"checkbox\" name=\"type_check[]\"",
								"label"				=> "<span class=\"icon-check\"></span>",
								"classExLabel"		=> "request-validate",
								"classExContainer"	=> "my-4",
								"classEx"			=> "checkbox"
							]
						],
						[
							"content" => 
							[
								[
									"kind" 			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" name=\"idrealEmployee[]\"",
									"classEx"		=> "idemployee-table-prenomina"
								],
								[
									"kind" 			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" name=\"idworkerData[]\"",
									"classEx"		=> "idworkingdata-table-prenomina"
								],
								[
									"kind" 			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\"",
									"classEx"		=> "fullname-table-prenomina"
								]
							]
						],
						[
							"classEx"	=> "content-select-type",
							"content"	=> 
							[
								"label" => ""
							]
						],
						[
							"classEx" => "content-select-fiscal",
							"content" => 
							[
								"label" => ""
							]
						],
						[
							"content" => 
							[
								[
									"kind" 			=> "components.buttons.button",
									"variant" 		=> "secondary",
									"attributeEx"	=> "title=\"Ver Datos\" type=\"button\" data-toggle=\"modal\" data-target=\"#myModal\"",
									"classEx"		=> "btn-view-user",
									"label"			=> "<span class=\"icon-search\"></span>"
								],
								[
									"kind" 			=> "components.buttons.button",
									"variant" 		=> "success",
									"attributeEx"	=> "title=\"Editar Datos\" type=\"button\" data-toggle=\"modal\" data-target=\"#myModal\"",
									"classEx"		=> "btn-edit-user",
									"label"			=> "<span class=\"icon-pencil\"></span>"
								],
								[
									"kind" 			=> "components.buttons.button",
									"variant" 		=> "red",
									"attributeEx"	=> "type=\"button\"",
									"classEx"		=> "btn-delete-tr",
									"label"			=> "<span class=\"icon-x\"></span>"
								]
							]
						]
					];
					$modelBody[] = $body;
					$table = view('components.tables.table', [ 
						"modelHead" => $modelHead,
						"modelBody" => $modelBody,
						"noHead"	=> "true"
					])->render();
				@endphp	
				tr		= '{!!preg_replace("/(\r)*(\n)*/", "", $table)!!}';
				row		= $(tr);
				idCheck = 'type_check_'+idemployee;				
				row.find('.checkbox').attr('id', idCheck);
				row.find('.checkbox').val(idemployee);
				row.find('.idemployee-table-prenomina').val(idemployee);
				row.find('.idworkingdata-table-prenomina').val(idworkerdata);
				row.find('.fullname-table-prenomina').val(fullname);
				row.find('.fullname-table-prenomina').parent().prepend(fullname);
				row.find('.content-select-type').append($('<select class="border rounded py-2 px-3 m-px w-full" title="Tipo de nómina" name="type[]" data-validation="required">'));
				row.find('[name="type[]"]').append($('<option value="1" selected="selected">Obra</option>'))
					.append($('<option value="2">Administrativa</option>'));
				row.find('.content-select-fiscal').append($('<select class="border rounded py-2 px-3 m-px w-full" title="Fiscal/No Fiscal" name="fiscal[]" data-validation="required">'));
				row.find('[name="fiscal[]"]').append($('<option value="2" selected="selected">No Fiscal</option>'));
			}
			else
			{
				@php
					$body		= [];
					$modelBody	= [];
					$modelHead	= [
						[
							["value" => ""],
							["value" => "Nombre del Empleado"],
							["value" => "Tipo"],
							["value" => "Fiscal/No Fiscal/Nom35"],
							["value" => "Acciones"]
						]
					];
					$body = [ "classEx" => "tr_payroll",
						[
							"content" => 
							[
								"kind"				=> "components.inputs.checkbox",
								"attributeEx"		=> "type=\"checkbox\" name=\"type_check[]\"",
								"label"				=> "<span class=\"icon-check\"></span>",
								"classExLabel"		=> "request-validate",
								"classExContainer"	=> "my-4",
								"classEx"			=> "checkbox"
							]
						],
						[
							"content" => 
							[
								[
									"kind" 			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" name=\"idrealEmployee[]\"",
									"classEx"		=> "idemployee-table-prenomina"
								],
								[
									"kind" 			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" name=\"idworkerData[]\"",
									"classEx"		=> "idworkingdata-table-prenomina"
								],
								[
									"kind" 			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\"",
									"classEx"		=> "fullname-table-prenomina"
								]
							]
						],
						[
							"classEx"	=> "content-select-type",
							"content"	=> 
							[
								"label" => ""
							]
						],
						[
							"classEx"	=> "content-select-fiscal",
							"content"	=> 
							[
								"label" => ""
							]
						],
						[
							"content" => 
							[
								[
									"kind" 			=> "components.buttons.button",
									"variant" 		=> "secondary",
									"attributeEx"	=> "title=\"Ver Datos\" type=\"button\" data-toggle=\"modal\" data-target=\"#myModal\"",
									"classEx"		=> "btn-view-user",
									"label"			=> "<span class=\"icon-search\"></span>"
								],
								[
									"kind" 			=> "components.buttons.button",
									"variant" 		=> "success",
									"attributeEx"	=> "title=\"Editar Datos\" type=\"button\" data-toggle=\"modal\" data-target=\"#myModal\"",
									"classEx"		=> "btn-edit-user",
									"label"			=> "<span class=\"icon-pencil\"></span>"
								],
								[
									"kind" 			=> "components.buttons.button",
									"variant" 		=> "red",
									"attributeEx"	=> "type=\"button\"",
									"classEx"		=> "btn-delete-tr",
									"label"			=> "<span class=\"icon-x\"></span>"
								]
							]
						]
					];
					$modelBody[] = $body;
					$table = view('components.tables.table', [ 
						"modelHead" => $modelHead,
						"modelBody" => $modelBody,
						"noHead"	=> "true"
					])->render();
				@endphp	
				tr		= '{!!preg_replace("/(\r)*(\n)*/", "", $table)!!}';
				row		= $(tr);
				idCheck = 'type_check_'+idemployee;				
				row.find('.checkbox').attr('id', idCheck);
				row.find('.checkbox').val(idemployee);
				row.find('.idemployee-table-prenomina').val(idemployee);
				row.find('.idworkingdata-table-prenomina').val(idworkerdata);
				row.find('.fullname-table-prenomina').val(fullname);
				row.find('.fullname-table-prenomina').parent().prepend(fullname);
				row.find('.content-select-type').append($('<select class="border rounded py-2 px-3 m-px w-full" title="Tipo de nómina" name="type[]" data-validation="required">'));
				row.find('[name="type[]"]').append($('<option value="1" selected="selected">Obra</option>'))
					.append($('<option value="2">Administrativa</option>'));
				row.find('.content-select-fiscal').append($('<select class="border rounded py-2 px-3 m-px w-full" title="Fiscal/No Fiscal" name="fiscal[]" data-validation="required">'));
				row.find('[name="fiscal[]"]').append($('<option value="1" selected="selected">Fiscal</option>'))
					.append($('<option value="2">No Fiscal</option>'))
					.append($('<option value="3">Fiscal/No Fiscal</option>'))
					.append($('<option value="4">Nom35</option>'))
					.append($('<option value="5">Fiscal/Nom35</option>'));
			}
			$('#body-payroll').append(row);
			$('#'+idCheck).next().attr('for', idCheck);
			$('#result').stop(true,true).slideUp();
		})
		.on('click','.btn-delete-tr',function()
		{
			$(this).parents('.tr_payroll').remove();
		})
		.on('click','.btn-view-user',function()
		{
			swal(
			{
				icon	: '{{ asset(getenv('LOADING_IMG')) }}',
				button	: false,
			});
			id 	= $(this).parents('.tr_payroll').find('.idemployee-table-prenomina').val();
			$.ajax(
			{
				type   : 'post',
				url    : '{{ route("nomina.prenomina-create.viewdetailemployee") }}',
				data   : {'id':id},
				success: function(data)
				{
					$('#myModal .modal-body').show().html(data);
					swal.close();
				},
				error : function()
				{
					swal('','Sucedió un error, por favor intente de nuevo.','error');
					$('#myModal').modal('hide');
				}
			});
		})
		.on('click','.btn-edit-user',function()
		{
			swal(
			{
				icon	: '{{ asset(getenv('LOADING_IMG')) }}',
				button	: false,
			});
			id 	= $(this).parents('.tr_payroll').find('.idemployee-table-prenomina').val();
			$.ajax(
			{
				type 	: 'post',
				url 	: '{{ route("nomina.prenomina-create.getdetailemployee") }}',
				data 	: {'id':id},
				success : function(data)
				{
					$('#myModal .modal-body').show().html(data);
					generalSelect({'selector':'.js-projects',	'model':14});
					generalSelect({'selector':'#cp',			'model':2});
					generalSelect({'selector':'.bank',			'model':28});
					generalSelect({'selector':'.js-wbs','depends':'.js-projects','model':1,'maxSelection': -1});
					@php
 						$selects = collect([
							[
								"identificator"				=> '[name="work_place[]"]',
								"placeholder"				=> "Seleccione el lugar de trabajo",
								"maximumSelectionLength"	=> "1"
							],
							[
								"identificator"				=> '[name="work_enterprise"],[name="work_enterprise_old"]',
								"placeholder"				=> "Seleccione la empresa",
								"maximumSelectionLength"	=> "1"
							],
							[
								"identificator"				=> '[name="work_department"]',
								"placeholder"				=> "Seleccione el departamento",
								"maximumSelectionLength"	=> "1"
							],
							[
								"identificator"				=> '[name="work_direction"]',
								"placeholder"				=> "Seleccione la dirección",
								"maximumSelectionLength"	=> "1"
							],
							[
								"identificator"				=> '[name="work_account"]',
								"placeholder"				=> "Seleccione la clasificación del gasto",
								"maximumSelectionLength"	=> "1"
							]
						]);
					@endphp
					@component('components.scripts.selects',['selects' => $selects]) @endcomponent
					$('[name="imss"]').mask('0000000000-0',{placeholder: "__________-_"});
					$('[name="work_income_date"],[name="work_imss_date"],[name="work_down_date"],[name="work_ending_date"],[name="work_reentry_date"],[name="work_income_date_old"]').datepicker({ dateFormat: "dd-mm-yy" });
					swal.close();
					validation();
				},
				error : function()
				{
					swal('','Sucedió un error, por favor intente de nuevo.','error');
					$('#myModal').modal('hide');
				}
			});
		})
		.on('click','.exit',function()
		{
			$('#myModal .modal-body').hide();
		})
		.on('click','.update-employee',function()
		{
			if (validationEditEmployee()) 
			{
				$.ajax(
				{
					type 	: 'post',
					url 	: '{{ route('nomina.prenomina-create.employeeupdate') }}',
					data 	: $('#container-alta').serialize(),
					success : function(data)
					{
						$('.idemployee-table-prenomina').each(function()
						{
							id = $(this).val();
							if (data[0] == id) 
							{
								$(this).parents('.tr_payroll').remove();
							}
						});

						@php
							$body		= [];
							$modelBody	= [];
							$modelHead	= [
								[
									["value" => ""],
									["value" => "Nombre del Empleado"],
									["value" => "Tipo"],
									["value" => "Fiscal/No Fiscal/Nom35"],
									["value" => "Acciones"]
								]
							];
							$body = [ "classEx" => "tr_payroll",
								[
									"content" => 
									[
										"kind"				=> "components.inputs.checkbox",
										"attributeEx"		=> "type=\"checkbox\" name=\"type_check[]\"",
										"label"				=> "<span class=\"icon-check\"></span>",
										"classExLabel"		=> "request-validate",
										"classExContainer"	=> "my-4",
										"classEx"			=> "checkbox"
									]
								],
								[
									"content" => 
									[
										[
											"kind" 			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"hidden\" name=\"idrealEmployee[]\"",
											"classEx"		=> "idemployee-table-prenomina"
 										],
										[
											"kind" 			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"hidden\" name=\"idworkerData[]\"",
											"classEx"		=> "idworkingdata-table-prenomina"
										],
										[
											"kind" 			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"hidden\"",
											"classEx"		=> "fullname-table-prenomina"
										]
									]
								],
								[
									"classEx"	=> "content-select-type", 
									"content"	=> 
									[
										"label" => ""
									]
								],
								[ 
									"classEx"	=> "content-select-fiscal", 
									"content"	=>
									[
										"label" => ""
									]
								],
								[
									"content" => 
									[
										[
											"kind" 			=> "components.buttons.button",
											"variant" 		=> "secondary",
											"attributeEx"	=> "title=\"Ver Datos\" type=\"button\" data-toggle=\"modal\" data-target=\"#myModal\"",
											"classEx"		=> "btn-view-user",
											"label"			=> "<span class=\"icon-search\"></span>"
										],
										[
											"kind" 			=> "components.buttons.button",
											"variant" 		=> "success",
											"attributeEx"	=> "title=\"Editar Datos\" type=\"button\" data-toggle=\"modal\" data-target=\"#myModal\"",
											"classEx"		=> "btn-edit-user",
											"label"			=> "<span class=\"icon-pencil\"></span>"
										],
										[
											"kind" 			=> "components.buttons.button",
											"variant" 		=> "red",
											"attributeEx"	=> "type=\"button\"",
											"classEx"		=> "btn-delete-tr",
											"label"			=> "<span class=\"icon-x\"></span>"
										]
									]
								]
							];
							$modelBody[] = $body;
							$table = view('components.tables.table', [
								"modelHead" => $modelHead,
								"modelBody" => $modelBody,
								"noHead"	=> "true"
							])->render();
						@endphp	
						tr = '{!!preg_replace("/(\r)*(\n)*/", "", $table)!!}';
						row = $(tr);
						idCheck = 'type_check_'+data[0];				
						row.find('.checkbox').attr('id', idCheck);
						row.find('.checkbox').val(data[0]);
						row.find('.idemployee-table-prenomina').val(data[0]);
						row.find('.idworkingdata-table-prenomina').val(data[1]);
						row.find('.fullname-table-prenomina').val(data[2]);
						row.find('.fullname-table-prenomina').parent().prepend(data[2]);
						row.find('.content-select-type').append($('<select class="border rounded py-2 px-3 m-px w-full" title="Tipo de nómina" name="type[]" data-validation="required">'));
						row.find('[name="type[]"]').append($('<option value="1" selected="selected">Obra</option>'))
							.append($('<option value="2">Administrativa</option>'));
						row.find('.content-select-fiscal').append($('<select class="border rounded py-2 px-3 m-px w-full" title="Fiscal/No Fiscal" name="fiscal[]" data-validation="required">'));
						row.find('[name="fiscal[]"]').append($('<option value="1" selected="selected">Fiscal</option>'))
							.append($('<option value="2">No Fiscal</option>'))
							.append($('<option value="3">Fiscal/No Fiscal</option>'))
							.append($('<option value="4">Nom35</option>'))
							.append($('<option value="5">Fiscal/Nom35</option>'));
						$('#body-payroll').append(row);
						$('#'+idCheck).next().attr('for', idCheck);
						$('#myModal').modal("hide");
						swal('Empleado actualizado','','success');
					},
					error : function(data)
					{
						swal('Ocurrió un error','Intente de nuevo','error');
					}
				});
			}
			else
			{
				swal('Error','Revise los datos','error');
			}
		})
		.on('change','[name="work_enterprise"]',function()
		{
			$('[name="work_account"]').html('');
			$('[name="work_employer_register"]').html('');
		})
		.on('change','[name="work_nomina"]',function()
		{
			nomina	= Number($(this).val());
			$('[name="work_bonus"]').val(100-nomina);
		})
		.on('change','[name="work_bonus"]',function()
		{
			bonos	= Number($(this).val());
			$('[name="work_nomina"]').val(100-bonos);
		})
		.on('input','.alias',function()
		{
			if($(this).val() != "")
			{
				$('.alias').addClass('valid').removeClass('error');
			}
			else
			{
				$('.alias').addClass('error').removeClass('valid');
			}
		})
		.on('click','#add-bank',function()
		{
			alias		= $(this).parents('.tr_bank').find('.alias').val();
			bankid		= $(this).parents('.tr_bank').find('.bank').val();
			bankName	= $(this).parents('.tr_bank').find('.bank :selected').text();
			clabe		= $(this).parents('.tr_bank').find('.clabe').val();
			account		= $(this).parents('.tr_bank').find('.account').val();
			card		= $(this).parents('.tr_bank').find('.card').val();
			branch		= $(this).parents('.tr_bank').find('.branch_office').val();
			if(alias == "")
			{
				swal('', 'Por favor ingrese un alias', 'error');
				$('.alias').addClass('error');
			}
			else if(bankid.length>0)
			{
				if (card == "" && clabe == "" && account == "")
				{
					$('.card, .clabe, .account').removeClass('valid').addClass('error');
					swal('', 'Debe ingresar al menos un número de tarjeta, clabe o cuenta bancaria', 'error');
				}
				else if($(this).parents('.tr_bank').find('.card').hasClass('error') || $(this).parents('.tr_bank').find('.clabe').hasClass('error') || $(this).parents('.tr_bank').find('.account').hasClass('error'))
				{
					swal('', 'Por favor ingrese datos correctos', 'error');
				}
				else
				{
					@php
						$body		= [];
						$modelBody	= [];
						$modelHead 	= [ "Alias", "Banco", "CLABE", "Cuenta", "Tarjeta", "Sucursal", "Acción" ];
						$body = [ "classEx" => "tr_body",
							[	
								"content" => 
								[
									[
										"kind"		=> "components.labels.label",
										"classEx"	=> "classAlias",
									],
									[
										"kind"			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"hidden\" name=\"alias[]\"",
										"classEx"		=> "aliasclass"
									]
								]
							],
							[
								"content" => 
								[
									[
										"kind"		=> "components.labels.label",
										"classEx"	=> "classBank",
									],
									[
										"kind"			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"hidden\"",
										"classEx"		=> "idbank"
									],
									[
										"kind"			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"hidden\" name=\"idEmpAcc[]\"",
										"classEx"		=> "idEmployee"
									],
									[
										"kind"			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"hidden\" name=\"bank[]\"",
										"classEx"		=> "bankclass"
									]
								]
							],
							[
								"content" => 
								[
									[
										"kind"		=> "components.labels.label",
										"classEx"	=> "classClabe",
									],
									[
										"kind"			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"hidden\" name=\"clabe[]\"",
										"classEx"		=> "clabeclass"
									]
								]
							],
							[
								"content" => 
								[
									[
										"kind"		=> "components.labels.label",
										"classEx"	=> "classAccount",
									],
									[
										"kind"			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"hidden\" name=\"account[]\"",
										"classEx"		=> "accountclass"
									]
								]
							],
							[
								"content" => 
								[
									[
										"kind"		=> "components.labels.label",
										"classEx"	=> "classCard",
									],
									[
										"kind"			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"hidden\" name=\"card[]\"",
										"classEx"		=> "cardclass"
									]
								]
							],
							[
								"content" => 
								[
									[
										"kind"		=> "components.labels.label",
										"classEx"	=> "classBranch",
									],
									[
										"kind"			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"hidden\" name=\"branch[]\"",
										"classEx"		=> "branchclass"
									]
								]
							],
							[
								"content" => 
								[
									[
										"kind" 			=> "components.buttons.button",
										"variant"		=> "red",
										"attributeEx"	=> "type=\"button\"",
										"classEx"		=> "delete-bank",
										"label"			=> "<span class=\"icon-x\"></span>"
									]
								]
							]
						];
						$modelBody[] = $body;

						$table = view('components.tables.alwaysVisibleTable', [
							"modelHead" => $modelHead,
							"modelBody" => $modelBody,
							"noHead"	=> true,
						])->render();
					@endphp
					bank	= '{!!preg_replace("/(\r)*(\n)*/", "", $table)!!}';
					row		= $(bank);
					row.find('.classAlias').text((alias =='' ? ' -- ' :alias));
					row.find('.aliasclass').val(alias);
					row.find('.classBank').text(bankName);
					row.find('.idbank').val('x');
					row.find('.idEmployee').val('x');
					row.find('.bankclass').val(bankid);
					row.find('.classClabe').text((clabe =='' ? ' -- ' :clabe));
					row.find('.clabeclass').val(clabe);
					row.find('.classAccount').text((account =='' ? ' -- ' :account));
					row.find('.accountclass').val(account);
					row.find('.classCard').text((card =='' ? ' -- ' :card));
					row.find('.cardclass').val(card);
					row.find('.classBranch').text((branch =='' ? ' -- ' :branch));
					row.find('.branchclass').val(branch);
					$('.body_content').append(row);
					$('.card, .clabe, .bank, .account, .alias, .branch_office').removeClass('error').removeClass('valid').val('');
					$('.bank').val(0).trigger("change");
				}
			}
			else
			{
				swal('', 'Seleccione un banco, por favor', 'error');
				$('.bank').addClass('error');
			}
		})
		.on('click','.delete-bank', function()
		{
			idbank	= $(this).parents('.tr_body').find('.idbank').val();
			del		= $('<input type="hidden" value="'+idbank+'" name="deleteBank[]">');
			$('#div-delete').append(del);
			$(this).parents('.tr_body').remove();
		})
		.on('change','#infonavit',function()
		{
			if($(this).is(':checked'))
			{
				$(this).parents('div').find('.tbody').stop(true,true).fadeIn();
				@php
					$selects = collect([
						[
							"identificator"				=> '[name="work_infonavit_discount_type"]',
							"placeholder"				=> "Seleccione tipo de descuento",
							"maximumSelectionLength"	=> "1"
						]
					]);
				@endphp
				@component('components.scripts.selects',['selects' => $selects]) @endcomponent
			}
			else
			{
				$(this).parents('div').find('.tbody').stop(true,true).fadeOut();
			}
		})
		.on('change','#editworker',function()
		{
			if($(this).is(':checked'))
			{
				swal({
					title		: "Habilitar edición de información laboral",
					text		: "¿Desea habilitar la edición de la información laboral?",
					icon		: "warning",
					buttons		: ["Cancelar","OK"],
					dangerMode	: true,
				})
				.then((enable) =>
				{
					if(enable)
					{
						$('.disabled').removeAttr('disabled').removeClass('disabled').addClass('showing');
						$('.view-button').show();
					}
					else
					{
						$('.showing').attr('disabled',true).addClass('disabled');
						$('.view-button').hide();
						$(this).prop('checked',false);
					}
				});
			}
			else
			{
				swal({
					title		: "Deshabilitar edición de información laboral",
					text		: "Si deshabilita la edición las modificaciones realizadas en INFORMACIÓN LABORAL no serán guardadas",
					icon		: "warning",
					buttons		: ["Cancelar","OK"],
					dangerMode	: true,
				})
				.then((disabled) =>
				{
					if(disabled)
					{
						$('.showing').attr('disabled',true).addClass('disabled');
						$('.view-button').hide();
					}
					else
					{
						$('.disabled').removeAttr('disabled').removeClass('disabled').addClass('showing');
						$('.view-button').show();
						$(this).prop('checked',true);
					}
				});
			}
		})
		.on('click','#save',function()
		{
			$('.remove').removeAttr('data-validation');
			$('.removeselect').removeAttr('required');
			$('.removeselect').removeAttr('data-validation');
			$('.request-validate').removeClass('request-validate');
		})
		.on('click','#help-btn-search-employee',function()
		{
			swal('Ayuda','Escriba el nombre del empleado y dé clic en el ícono del buscador, posteriormente seleccione un empleado.','info');
		})
		.on('click','#help-btn-massive-employee',function()
		{
			swal('Ayuda','En este apartado podrá subir el archivo masivo que utilizó en la carga de empleados para seleccionarlos y agregarlos a la prenómina.','info');
		})
		.on('click','#help-btn-add-employee',function()
		{
			swal('Ayuda','En este apartado debe seleccionar para cada empleado el tipo de nómina y marcar si la nómina será fiscal o no fiscal. Si existe algún error con los datos del empleado, puede dar clic en el botón verde con el ícono de lápiz para poder editar los datos personales y/o laborales del empleado','info');
		})
		.on('click','#help-btn-edit-employee',function()
		{
			swal('Ayuda','Al habilitar la edición los cambios realizados en "Información Laboral" serán guardados. Al estar deshabilitada la edición los cambios realizados en "Información Laboral" no serán guardados','info');
		})
		.on('click','.delete-doc',function()
		{
			swal(
			{
				icon	: '{{ asset(getenv('LOADING_IMG')) }}',
				button	: false
			});
			actioner		= $(this);
			uploadedName	= $(this).parents('.docs-p').find('input[name="realPath[]"]');
			formData		= new FormData();
			formData.append(uploadedName.attr('name'),uploadedName.val());
			$.ajax(
			{
				type		: 'post',
				url			: '{{ url("/administration/payments/upload") }}',
				data		: formData,
				contentType	: false,
				processData	: false,
				success		: function(r)
				{
					swal.close();
					actioner.parents('.docs-p').remove();
				},
				error		: function()
				{
					swal.close();
					actioner.parents('.docs-p').remove();
				}
			});
			$(this).parents('.docs-p').remove();
		})
		.on('click','.exist-doc',function()
		{
			docR = $(this).parents('span.removeDoc').find('.iddocumentsPayments').val();
			inputDelete = $('<input type="hidden" name="deleteDoc[]">').val(docR);
			$('#docs-remove').append(inputDelete);
			$(this).parents('span.removeDoc').remove();
		})
		.on('click','#update_to_select',function(e)
		{
			filename		= $(this).parents('#container-data-2').find('[name="csv_file"]');
			delimiter		= $(this).parents('#container-data-2').find('[name="separator"]:checked');
			extention		= /\.csv/i;
			
			if (filename.val().search(extention) == -1)
			{
				swal('', 'El tipo de archivo no es soportado, por favor seleccione un archivo csv', 'warning');
				$('[name="csv_file"]').val('');
			}
			else if ($('[name="csv_file"]').size>315621376)
			{
				swal('', 'El tamaño máximo de su archivo no debe ser mayor a 300Mb', 'warning');
			}
			else
			{
				$(this).parent('div').find('.uploader-content').removeClass("image_success").addClass('loading');
				formData	= new FormData();
				formData.append(filename.attr('name'), filename.prop("files")[0]);
				formData.append(delimiter.attr('name'),delimiter.val());
				$.ajax(
				{
					type		: 'post',
					url			: '{{ route('nomina.select.massive') }}',
					data		: formData,
					contentType	: false,
					processData	: false,
					success		: function(r)
					{
						if(r.error=='DONE')
						{
							$('.uploader-content').removeClass('image_success');
							swal('',r.message, 'info');
							$('#body-payroll').html(r.table);
							$('[name="csv_file"]').val('');
							$('[name="csv_file"]').next('label').html(labelVal);
						}
						else
						{
							$('.uploader-content').removeClass('image_success');
							swal('',r.message, 'error');
							$('[name="csv_file"]').val('');
							$('[name="csv_file"]').next('label').html(labelVal);
						}
					},
					error: function()
					{
						$('.uploader-content').removeClass('loading');
						swal('', 'Ocurrió un error durante la carga del archivo, intente de nuevo, por favor', 'error');
						$('[name="csv_file"]').val('');
						$('[name="csv_file"]').next('label').html(labelVal);
					}
				})
			}
		})
		.on('change','#csv',function(e)
		{
			label		= $(this).next('label');
			fileName	= e.target.value.split( '\\' ).pop();
			if(fileName)
			{
				label.find('span').html(fileName);
			}
			else
			{
				label.html(labelVal);
			}
		})
		.on('click','.delete-prenomina',function(e)
		{
			e.preventDefault();
			url	= $(this).attr('href');
			swal({
				title		: "",
				text		: "Confirme que desea eliminar la prenómina",
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
					form = $('<form action="'+url+'" method="POST"></form>')
						.append($('@csrf'))
						.append($('@method('delete')'));
					$(document.body).append(form);
					form.submit();
				}
			});
		})
		.on('change','#change_type',function()
		{
			fiscal = $(this).val();
			$('.checkbox').each(function()
			{
				if ($(this).is(':checked')) 
				{
					$(this).parents('.tr_payroll').find('[name="fiscal[]"]').val(fiscal).trigger('change');
				}
			});
		})
		.on('change','#change_category',function()
		{
			type = $(this).val();
			$('.checkbox').each(function()
			{
				if ($(this).is(':checked')) 
				{
					$(this).parents('.tr_payroll').find('[name="type[]"]').val(type).trigger('change');
				}
			});
		})
		.on('click','[name="select_all"]',function()
		{
			if($(this).hasClass('flag'))
			{
				$(this).removeClass('flag');
				type	= $('#change_category').val();
				fiscal	= $('#change_type').val();
				$('[name="type_check[]"]').prop('checked',true);
				$('.checkbox').each(function()
				{
					if ($(this).is(':checked')) 
					{
						$(this).parents('.tr_payroll').find('[name="fiscal[]"]').val(fiscal).trigger('change');
						$(this).parents('.tr_payroll').find('[name="type[]"]').val(type).trigger('change');
					}
				});
			}
			else
			{
				$(this).addClass('flag');
				$('[name="type_check[]"]').prop('checked',false);
			}
		})
		.on('change','[name="work_project"]',function()
		{
			project_id = $('option:selected',this).val();
			if (project_id != undefined) 
			{
				$('[name="work_wbs[]"]').empty();
				$('.select_father').show();
				$.each(generalSelectProject, function(i,v)
				{
					if(project_id == v.id)
					{
						if(v.flagWBS != null)
						{
							$('.select_father').show();
						}
						else
						{
							$('.select_father').hide();
						}
					}
				});
			}
			else
			{
				$('[name="work_wbs[]"]').empty();
				$('.select_father').hide();
			}
		})
	});

	function validation()
	{
		$.validate(
		{
			form	: '#container-alta',
			modules	: 'security',
			onError	: function($form)
			{
				swal('', '{{ Lang::get("messages.form_error") }}', 'error');
			},
			onSuccess : function($form)
			{
				if($('.request-validate').length>0)
				{
					employees   = $('#body-payroll .tr_payroll').length;
					if(employees == 0)
					{
						swal('', 'Debe agregar al menos un empleado', 'error');
						return false;
					}
					else if($('[name="csv_file"]').val() != "")
					{
						swal('', 'Tiene un archivo sin cargar.', 'error');
						return false;
					}
					else
					{
						swal("Cargando",{
							icon: '{{ asset(getenv('LOADING_IMG')) }}',
							button: false,
						});
						return true;
					}
				}
				else
				{
					swal("Cargando",{
						icon: '{{ asset(getenv('LOADING_IMG')) }}',
						button: false,
					});
					return true;
				}
			}
		});
	}

	function validationEditEmployee() 
	{
		idemployee				= $('input[name="idemployee"]').val();
		name					= $('input[name="name"]').val();
		last_name				= $('input[name="last_name"]').val();
		curp					= $('input[name="curp"]').val();
		street					= $('input[name="street"]').val();
		number					= $('input[name="number"]').val();
		colony					= $('input[name="colony"]').val();
		cp						= $('input[name="cp"]').val();
		city					= $('input[name="city"]').val();
		email					= $('input[name="email"]').val();
		state					= $('select[name="state"] option:selected').val();
		work_state				= $('select[name="work_state"] option:selected').val();
		work_enterprise			= $('select[name="work_enterprise"] option:selected').val();
		work_account			= $('select[name="work_account"] option:selected').val();
		work_direction			= $('select[name="work_direction"] option:selected').val();
		work_employer_register	= $('select[name="work_employer_register"] option:selected').val();
		position				= $('input[name="position"]').val();
		work_income_date		= $('input[name="work_income_date"]').val();
		work_net_income			= $('input[name="work_net_income"]').val();
		work_nomina				= $('input[name="work_nomina"]').val();
		work_bonus				= $('input[name="work_bonus"]').val();

		$('input[name="idemployee"]').removeClass('error');
		$('input[name="name"]').removeClass('error');
		$('input[name="last_name"]').removeClass('error');
		$('input[name="curp"]').removeClass('error');
		$('input[name="street"]').removeClass('error');
		$('input[name="number"]').removeClass('error');
		$('input[name="colony"]').removeClass('error');
		$('input[name="cp"]').removeClass('error');
		$('input[name="city"]').removeClass('error');
		$('select[name="state"]').removeClass('error');
		$('select[name="work_state"]').removeClass('error');
		$('select[name="work_enterprise"]').removeClass('error');
		$('select[name="work_account"]').removeClass('error');
		$('select[name="work_direction"]').removeClass('error');
		$('select[name="work_employer_register"]').removeClass('error').parent('div').find('span.help-block.form-error').remove();
		$('input[name="position"]').removeClass('error');
		$('input[name="work_income_date"]').removeClass('error');
		$('input[name="work_net_income"]').removeClass('error');
		$('input[name="work_nomina"]').removeClass('error');
		$('input[name="work_bonus"]').removeClass('error');
		$('#employee_data').find('input').removeClass('error');
		$('#employee_data').find('select').parent('div').find('span.help-block.form-error').remove();

		if (email == '' || idemployee == '' || name == '' || last_name == '' || curp == '' || street == '' || number == '' || colony == '' || cp == '' || city == '' || (state == '' || state == undefined) || (work_state == '' || work_state == undefined) || work_enterprise == undefined || work_account == undefined || work_direction == undefined || work_employer_register == undefined || position == '' || work_income_date == '' || work_net_income == '' || work_nomina == '' || work_bonus == '') 
		{
			if(email == '')
			{
				$('input[name="email"]').addClass('error');
			}
			if(idemployee == '')
			{
				$('input[name="idemployee"]').addClass('error');
			}
			if(name == '')
			{
				$('input[name="name"]').addClass('error');
			}
			if(last_name == '')
			{
				$('input[name="last_name"]').addClass('error');
			}
			if(curp == '')
			{
				$('input[name="curp"]').addClass('error');
			}
			if(street == '')
			{
				$('input[name="street"]').addClass('error');
			}
			if(number == '')
			{
				$('input[name="number"]').addClass('error');
			}
			if(colony == '')
			{
				$('input[name="colony"]').addClass('error');
			}
			if(cp == '')
			{
				$('input[name="cp"]').addClass('error');
			}
			if(city == '')
			{
				$('input[name="city"]').addClass('error');
			}
			if(state == '' || state == undefined)
			{
				$('[name="state"]').addClass('error');
				$('[name="state"]').parent('div').append('<span class="help-block form-error">Este campo es obligatorio</span>');
			}
			if(work_state == '' || work_state == undefined)
			{
				$('[name="work_state"]').addClass('error');
				$('[name="work_state"]').parent('div').append('<span class="help-block form-error">Este campo es obligatorio</span>');
			}
			if(work_enterprise == '' || work_enterprise == undefined)
			{
				$('[name="work_enterprise"]').addClass('error');
				$('[name="work_enterprise"]').parent('div').append('<span class="help-block form-error">Este campo es obligatorio</span>');
			}
			if(work_account == '' || work_account == undefined)
			{
				$('[name="work_account"]').addClass('error');
				$('[name="work_account"]').parent('div').append('<span class="help-block form-error">Este campo es obligatorio</span>');
			}
			if(work_direction == '' || work_direction == undefined)
			{
				$('[name="work_direction"]').addClass('error');
				$('[name="work_direction"]').parent('div').append('<span class="help-block form-error">Este campo es obligatorio</span>');
			}
			if(work_employer_register == '' || work_employer_register == undefined)
			{
				$('[name="work_employer_register"]').addClass('error');
				$('[name="work_employer_register"]').parent('div').append('<span class="help-block form-error">Este campo es obligatorio</span>');
			}
			if(position == '')
			{
				$('input[name="position"]').addClass('error');
			}
			if(work_income_date == '')
			{
				$('input[name="work_income_date"]').addClass('error');
			}
			if(work_net_income == '')
			{
				$('input[name="work_net_income"]').addClass('error');
			}
			if(work_nomina == '')
			{
				$('input[name="work_nomina"]').addClass('error');
			}
			if(work_bonus == '')
			{
				$('input[name="work_bonus"]').addClass('error');
			}
			return false;
		}
		else
		{
			if($('[name="rfc"]').hasClass('error'))
			{
				return false;
			}
			else
			{
				return true;
			}
		}
	}
	function search(page)
	{
		swal(
		{
			icon: '{{ asset(getenv('LOADING_IMG')) }}',
			button	: false,
		});
		text = $('input[name="searchEmployee"]').val();
		idrealEmployee = [];

		$('.idemployee-table-prenomina').each(function()
		{
			idrealEmployee.push(Number($(this).val()));
		});
		
		$.ajax(
		{
			type	: 'post',
			url    	: '{{ route("nomina.prenomina-create.getemployee") }}',
			data	: {'search':text,'idrealEmployee':idrealEmployee, 'page':page},
			success	: function(data)
			{
				$('#result').html(data);
				$('#result').stop(true,true).fadeIn();
				$('#error_request').stop(true,true).hide();
				swal.close();
			},
			error: function()
			{
				swal.close();
			}
		}); 
	}
</script>
@endsection
