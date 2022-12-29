@extends('layouts.child_module')
@section('data')
	@component('components.forms.form', ["attributeEx" => "method=\"POST\" id=\"container-alta\" action=\"".route('nomina.prenomina-create.store')."\""])
		@component('components.labels.title-divisor') Nueva Solicitud @endcomponent
		@component('components.containers.container-form')
			<div class="col-span-2">
				@component('components.labels.label') Título: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" name="title" placeholder="Ingrese un título" data-validation="required" value="{{ $request->nominasReal->first()->title }}"
					@endslot
					@slot('classEx')
						removeselect
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Fecha: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" name="datetitle" data-validation="required" placeholder="Ingrese la fecha" readonly="readonly" value="{{ Carbon\Carbon::createFromFormat('Y-m-d',$request->nominasReal->first()->datetitle)->format('d-m-Y') }}"
					@endslot
					@slot('classEx')
						removeselect datepicker
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Tipo de Nómina: @endcomponent
				@php
					$optionTypeCat = [];
					foreach(App\CatTypePayroll::orderName()->get() as $t)
					{
						if($request->nominasReal->first()->idCatTypePayroll == $t->id)
						{
							$optionTypeCat[] = ["value" => $t->id, "description" => $t->description, "selected" => "selected"];
						}
						else
						{
							$optionTypeCat[] = ["value" => $t->id, "description" => $t->description];
						}
					}
				@endphp
				@component('components.inputs.select', ["options" => $optionTypeCat])
					@slot('attributeEx')
						title="Tipo de nómina" multiple="multiple" name="type_payroll" data-validation="required"
					@endslot
					@slot('classEx')
						js-typepayroll
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Solicitante: @endcomponent
				@php
					$optionUserid = [];
					if(isset($request))
					{
						$optionUserid[] = ["value" => $request->idRequest, "description" => $request->requestUser->fullName(), "selected" => "selected"];
					}
				@endphp
				@component('components.inputs.select', ["options" => $optionUserid])
					@slot('attributeEx')
						name="userid" multiple="multiple" data-validation="required"
					@endslot
					@slot('classEx')
						js-users removeselect
					@endslot
				@endcomponent
			</div>
		@endcomponent
		@component('components.labels.title-divisor') Buscar Empleado <span class="help-btn" id="help-btn-search-employee"></span> @endcomponent
		@component('components.inputs.input-search')
			@slot('attributeExInput')
				type="text" title="Escriba aquí" name="searchEmployee" id="input-search" placeholder="Ingrese un nombre de empleado"
			@endslot
			@slot('attributeExButton')
				id="search-btn" type="button" title="Buscar"
			@endslot
		@endcomponent
		<div id="result" class="mb-4"></div>
		<div class="alert alert-danger my-4 hidden" id="error_request" role="alert">SIN RESULTADOS.</div>
		@component('components.labels.title-divisor')  Datos de Empleado <span class="help-btn" id="help-btn-add-employee"></span> @endcomponent
		@component('components.labels.not-found', [ "variant" => "note"])
			La plataforma permite modificar los campos «Tipo» y «Categoría» de manera masiva.
			Para ello, primero es necesario seleccionar los empleados dando clic en el botón <span class="icon-check"></span>.
			Posteriormente deberá seleccionar la nueva opción de la lista mostrada a continuación.
			Al hacer esto, verá el cambio reflejado en todos los empleados seleccionados.
		@endcomponent
		@component('components.containers.container-form')
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
						js-type
						removeselect
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Seleccione la categoría: @endcomponent
				@php
					$optionCategory		= [];
					$optionCategory[] 	= ["value" => "1", "description" => "Fiscal"];
					$optionCategory[] 	= ["value" => "2", "description" => "No Fiscal"];
					$optionCategory[] 	= ["value" => "3", "description" => "Fiscal/No Fiscal"];
					$optionCategory[] 	= ["value" => "4", "description" => "Nom35"];
					$optionCategory[] 	= ["value" => "5", "description" => "Fiscal/Nom35"];
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
		@component('components.buttons.button', [
				"variant" => "success"
			])
				@slot('classEx')
					checkbox
					request-validate
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
			foreach(App\NominaEmployee::join('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')->where('nomina_employees.idnomina',$request->nominasReal->first()->idnomina)->where('nomina_employees.visible',1)->orderBy('real_employees.last_name','ASC')->orderBy('real_employees.scnd_last_name','ASC')->orderBy('real_employees.name','ASC')->select('nomina_employees.*')->get() as $n)
			{
				$selectType	= "";
				$selectType	.= '<select class="border rounded py-2 px-3 m-px w-full" title="Tipo de nómina" name="type[]" data-validation="required">';
					if($n->type == 1)
					{
						$selectType .= '<option value="1" selected="selected">Obra</option>';
					}
					else
					{
						$selectType .= '<option value="1">Obra</option>';
					}
					if($n->type == 2)
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
					if($n->workerData()->exists() && $n->workerData->first()->downDate != '')
					{
						if(new \DateTime($n->workerData->first()->downDate) > new \DateTime($n->workerData->first()->imssDate)) 
						{
							if($n->workerData->first()->regime_id == '09') 
							{
								$selectFiscal .= '<option value="1" selected="selected">Fiscal</option>';
								$selectFiscal .= '<option value="2">No Fiscal</option>';
								$selectFiscal .= '<option value="3">Fiscal/No Fiscal</option>';
								$selectFiscal .= '<option value="4">Nom35</option>';
								$selectFiscal .= '<option value="5">Fiscal/Nom35</option>';
							}
							else 
							{
								$datetime1	= date_create($n->workerData->first()->downDate);
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
									$selectFiscal .= '<option value="4">Nom35</option>';
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
					if($n->workerData()->exists() && $n->workerData->first()->downDate == '')
					{
						if($n->workerData->first()->imssDate == '')
						{
							if($n->workerData->first()->regime_id == '09')
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
								$selectFiscal .= '<option value="4">Nom35</option>';
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
							"attributeEx" 		=> "id=\"type_check_$n->idrealEmployee\" type=\"checkbox\" name=\"type_check[]\" value=\"".$n->idrealEmployee."\"",
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
								"attributeEx"	=> "type=\"hidden\" name=\"idrealEmployee[]\" value=\"".$n->idrealEmployee."\"",
								"classEx"		=> "idemployee-table-prenomina" 
							],
							[
								"kind"			=> "components.inputs.input-text",
								"attributeEx"	=> "type=\"hidden\" name=\"idworkerData[]\" value=\"".$n->idworkingData."\"",
								"classEx"		=> "idworkingdata-table-prenomina" 
							],
							[
								"kind"			=> "components.inputs.input-text",
								"attributeEx"	=> "type=\"hidden\" value=\"".$n->employee->first()->last_name.' '.$n->employee->first()->scnd_last_name.' '.$n->employee->first()->name."\"",
								"classEx"		=> "fullname-table-prenomina" 
							],
							[
								"label"			=> $n->employee->first()->last_name.' '.$n->employee->first()->scnd_last_name.' '.$n->employee->first()->name
							]

						]
					],
					[
						"content" => 
						[
							"label"	=> $selectType
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
		@endphp
		@component('components.tables.table', [
			"modelHead" => $modelHead,
			"modelBody" => $modelBody
		])
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
		<div class="w-full mt-4 grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mb-6">
			@component('components.buttons.button', ["variant" => "primary"])
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
		@component('components.modals.modal', ["variant" => "large"])
			@slot('id')
				myModal
			@endslot
			@slot('attributeEx')
				tabindex="-1"
			@endslot
			@slot('modelHeader')
				@component('components.buttons.button')
					@slot('attributeEx')
						type="button"
						data-dismiss="modal"
					@endslot
					@slot('classEx')
						close
					@endslot
						<span aria-hidden="true">&times;</span>
					@endslot
				@endcomponent	
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
	$(document).ready(function()
	{
		validation();
		@php
			$selects = collect([
				[
					"identificator"				=> ".js-type",
					"placeholder"				=> "Seleccione el tipo",
					"maximumSelectionLength"	=> "1"
				],
				[
					"identificator"				=> ".js-category",
					"placeholder"				=> "Seleccione la categoria",
					"maximumSelectionLength"	=> "1"
				],
				[
					"identificator"				=> ".js-typepayroll",
					"placeholder"				=> "Seleccione el tipo de pago",
					"maximumSelectionLength"	=> "1"
				]
			]);
		@endphp
		@component('components.scripts.selects',["selects" => $selects]) @endcomponent
		generalSelect({'selector':'.js-users','model':13})
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
						"themeBody" => "striped",
						"noHead"	=> "true"
					])->render();
				@endphp	
				tr = '{!!preg_replace("/(\r)*(\n)*/", "", $table)!!}';
				row = $(tr);
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
				row.find('[name="fiscal[]"]').append($('<option value="2" selected="selected">No Fiscal</option>'))
					.append($('<option value="4">Nom35</option>'));
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
								],
								[
									"kind"		=> "components.labels.label",
									"classEx"	=> "classEmployee" 
								]
							]
						],
						[
							"classEx"	=> "content-select-type", 
							"content"	=>
							[
								"label"	=> ""
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
				idCheck = 'type_check_'+idemployee;				
				row.find('.checkbox').attr('id', idCheck);
				row.find('.checkbox').val(idemployee);
				row.find('.idemployee-table-prenomina').val(idemployee);
				row.find('.idworkingdata-table-prenomina').val(idworkerdata);
				row.find('.fullname-table-prenomina').val(fullname);
				row.find('.classEmployee').text(fullname);
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
				type 	: 'post',
				url 	: '{{ route("nomina.prenomina-create.viewdetailemployee") }}',
				data 	:{'id':id},
				success : function(data)
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
					url 	: '{{ route("nomina.prenomina-create.employeeupdate") }}',
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

							$optionTypeNom = [];
							$optionTypeNom[] = ["value" => "1", "description" => "Obra", "selected" => "selected"];
							$optionTypeNom[] = ["value" => "2", "description" => "Administrativa"];

							$optionFiscals = [];
							$optionFiscals[] = ["value" => "1", "description" => "Fiscal", "selected" => "selected"];
							$optionFiscals[] = ["value" => "2", "description" => "No Fiscal"];
							$optionFiscals[] = ["value" => "3", "description" => "Fiscal/No Fiscal"];
							$optionFiscals[] = ["value" => "4", "description" => "Nom35"];
							$optionFiscals[] = ["value" => "5", "description" => "Fiscal/Nom35"];

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
										],
										[
											"kind"		=> "components.labels.label",
											"classEx"	=> "classEmployee" 
										]
									]
								],
								[
									"content" => 
									[
										"kind" 			=> "components.inputs.select",
										"attributeEx"	=> "title=\"Tipo de nómina\" name=\"type[]\" multiple=\"multiple\"",
										"options"		=> $optionTypeNom,
									]
								],
								[ 
									"content" => 
									[
										"kind" 			=> "components.inputs.select",
										"attributeEx"	=> "title=\"Fiscal/No Fiscal\" name=\"fiscal[]\" multiple=\"multiple\"",
										"options"		=> $optionFiscals,
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
						row 	= $(tr);
						idCheck = 'type_check_'+data[0];				
						row.find('.checkbox').attr('id', idCheck);
						row.find('.checkbox').val(data[0]);
						row.find('.idemployee-table-prenomina').val(data[0]);
						row.find('.idworkingdata-table-prenomina').val(data[1]);
						row.find('.fullname-table-prenomina').val(data[2]);
						row.find('.classEmployee').text(data[2]);
						$('#body-payroll').append(row);
						$('#'+idCheck).next().attr('for', idCheck);
						$('#myModal').modal("hide");

						@php
							$selects = collect([
								[
									"identificator"				=> '[name="type[]"]',
									"placeholder"				=> "Seleccione el tipo de nómina",
									"maximumSelectionLength"	=> "1"
								],
								[
									"identificator"				=> '[name="fiscal[]"]',
									"placeholder"				=> "Seleccione el tipo fiscal",
									"maximumSelectionLength"	=> "1"
								]
							]);
						@endphp
						@component('components.scripts.selects',['selects' => $selects]) @endcomponent
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
			$('.card, .clabe, .account').removeClass('valid').removeClass('error');

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
									],
									[
										"kind"			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"hidden\" name=\"beneficiary[]\" value=\"\"",
										"classEx"		=> "beneficiaryClass"
									],
									[
										"kind"			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"hidden\" name=\"type_account[]\" value=\"1\"",
										"classEx"		=> "typeAccountClass"
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
					bank = '{!!preg_replace("/(\r)*(\n)*/", "", $table)!!}';
					row = $(bank);
					row.find('.classAlias').text((alias =='' ? ' -- ' :alias));
					row.find('.aliasclass').val(alias);
					row.find('.classBank').text(bankName);
					row.find('.idbank').val('x');
					row.find('.idEmployee').val('x');
					row.find('.bankclass').val(bankid)
					row.find('.classClabe').text((clabe =='' ? ' -- ' :clabe));
					row.find('.clabeclass').val(clabe);
					row.find('.classAccount').text((account =='' ? ' -- ' :account));
					row.find('.accountclass').val(account);
					row.find('.classCard').text((card =='' ? ' -- ' :card));
					row.find('.cardclass').val(card);
					row.find('.classBranch').text((branch =='' ? ' -- ' :branch));
					row.find('.branchclass').val(branch);
					$('.body_content').append(row);
					$('.card, .clabe, .account, .alias').removeClass('error').removeClass('valid').val('');
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
						$('.disabled').attr('disabled',true).addClass('disabled');
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
			swal('Ayuda','Escriba el nombre del empleado y de click en el icono del buscador, posteriormente seleccione un empleado.','info');
		})
		.on('click','#help-btn-add-employee',function()
		{
			swal('Ayuda','En este apartado debe seleccionar para cada empleado el tipo de nómina, marcar si la nómina sera fiscal o no fiscal. Si existe algún error con los datos del empleado puede dar clic en el botón verde con el icono de lapíz para poder editar los datos personales y/o laborales del empleado.','info');
		})
		.on('click','#help-btn-edit-employee',function()
		{
			swal('Ayuda','Al habilitar la edición los cambios realizados en "Información Laboral" serán guardados. Al estar deshabilitada la edición los cambios realizados en "Información Laboral" no serán guardados','info');
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
			if ($(this).hasClass('flag'))
			{
				$(this).removeClass('flag');
				type 	= $('#change_category').val();
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
	function validation()
	{
		$.validate(
		{
			form	: '#container-alta',
			modules	: "security",
			onError	: function($form)
			{
				swal('', '{{ Lang::get("messages.form_error") }}', 'error');
			},
			onSuccess : function($form)
			{
				if($('.request-validate').length>0)
				{
					employees   = $('#body-payroll .tr_payroll').length;
					if(employees>0)
					{
						swal("Cargando",{
							icon: '{{ asset(getenv('LOADING_IMG')) }}',
							button: false,
						});
						return true;
					}
					else
					{
						swal('', 'Debe agregar al menos un empleado', 'error');
						return false;
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
		$('select[name="work_employer_register"]').removeClass('error');
		$('input[name="position"]').removeClass('error');
		$('input[name="work_income_date"]').removeClass('error');
		$('input[name="work_net_income"]').removeClass('error');
		$('input[name="work_nomina"]').removeClass('error');
		$('input[name="work_bonus"]').removeClass('error');
		$('#employee_data').find('input').removeClass('error');
		$('#employee_data').find('select').parent('p').find('span.help-block.form-error').remove();

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
			{
				return true;
			}
		}
	}
</script>
@endsection
