@extends('layouts.child_module')

@section('data')
	@component("components.labels.title-divisor") Prenóminas automáticas @endcomponent
	@php
		$body 		= [];
		$modelBody	= [];
		$modelHead	= [
			[
				["value" => "Título"],
				["value" => "Fecha"],
				["value" => "Tipo"],
				["value" => "Acciones"]
			]
		];
		foreach(App\Prenomina::where('status',0)->whereIn('project_id',Auth::user()->inChargeProject(307)->pluck('project_id'))->get() as $p)
		{
			$body = [
				[
					"content" =>
					[
						"label" => htmlentities($p->title),
					]
				],
				[
					"content" =>
					[
						"label" => Carbon\Carbon::createFromFormat('Y-m-d',$p->datetitle)->format('d-m-Y')
					]
				],
				[
					"content" =>
					[
						"label" => $p->typePayroll->description
					]
				],
				[
					"content" =>
					[
						[
							"kind" 			=> "components.buttons.button",
							"variant" 		=> "success",
							"buttonElement" => "a",
							"attributeEx"	=> "href=\"".route('nomina.prenomina-obra-edit',$p->idprenomina)."\"",
							"label" 		=> "<span class=\"icon-pencil\"></span>"
						],
						[
							"kind" 			=> "components.buttons.button",
							"variant" 		=> "secondary",
							"buttonElement" => "a",
							"attributeEx"	=> "href=\"".route('nomina.prenomina-obra-download',$p->idprenomina)."\"",
							"label" 		=> "<i class=\"fas fa-download\"></i>"
						],
						[
							"kind" 			=> "components.buttons.button",
							"variant" 		=> "red",
							"buttonElement" => "a",
							"attributeEx"	=> "href=\"".route('nomina.prenomina-obra-delete',$p->idprenomina)."\"",
							"classEx"		=> "delete-prenomina",
							"label" 		=> "<span class=\"icon-x\"></span>"
						]
					]
				]
			];
			$modelBody[] = $body;
		}
	@endphp
	@component('components.tables.table', [
		"modelBody" => $modelBody,
		"modelHead" => $modelHead
	])	
		@slot('classEx')
			table
		@endslot
	@endcomponent
	@component('components.forms.form', ["attributeEx" => "method=\"POST\" id=\"container-alta\" action=\"".route('nomina.employee-obra.store')."\""])
		@if(isset($prenomina))
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="hidden" name="prenomina_id" value="{{$prenomina->idprenomina}}"
				@endslot
			@endcomponent
		@endif
		@component("components.labels.title-divisor") Datos de la Prenómina @endcomponent
		@component("components.containers.container-form")	
			<div class="col-span-2">
				@component('components.labels.label') Título: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" name="title" placeholder="Ingrese el título" data-validation="required" @if(isset($prenomina))value="{{ $prenomina->title }}"@endif
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
						type="text" name="datetitle" data-validation="required" placeholder="Ingrese la fecha" readonly="readonly" @if(isset($prenomina))value="{{ Carbon\Carbon::createFromFormat('Y-m-d',$prenomina->datetitle)->format('d-m-Y') }}"@endif
					@endslot
					@slot('classEx')
						removeselect datepicker
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Tipo de Nómina: @endcomponent
				@php
					$optionPayroll = [];
					foreach(App\CatTypePayroll::where('id','001')->get() as $t)
					{
						if (isset($prenomina) && $prenomina->idCatTypePayroll == $t->id)
						{
							$optionPayroll[] = ["value" => $t->id, "description" => $t->description, "selected" => "selected"];
						}
						else
						{
							$optionPayroll[] = ["value" => $t->id, "description" => $t->description];
						}
					}
				@endphp
				@component('components.inputs.select', ["options" => $optionPayroll])
					@slot('attributeEx')
						title="Tipo de nómina" name="type_payroll" data-validation="required" multiple="multiple"
					@endslot
					@slot('classEx')
						js-typepayroll
					@endslot	
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Proyecto: @endcomponent
				@php
					$optionProyect = [];
					foreach(App\Project::whereIn('idproyect',Auth::user()->inChargeProject(307)->pluck('project_id'))->get() as $project)
					{
						if (isset($prenomina) && $prenomina->project_id == $project->idproyect)
						{
							$optionProyect[] = ['value' => $project->idproyect, 'description' => $project->proyectName, "selected" => "selected"];
						}
						else
						{
							$optionProyect[] = ['value' => $project->idproyect, 'description' => $project->proyectName];
						}
					}
				@endphp
				@component('components.inputs.select', ["options" => $optionProyect])
					@slot('attributeEx')
						title="Proyecto" name="project_id" data-validation="required" multiple="multiple"
					@endslot
					@slot('classEx')
						js-project
					@endslot
				@endcomponent
			</div>
		@endcomponent
		@component("components.labels.title-divisor") Selección masiva <span class="help-btn" id="help-btn-massive-employee"> @endcomponent
		@component('components.labels.not-found', ["variant" => "note"])
			<div>
				* En cada prenómina registrada se mostrará un botón de color azul, con el cual se descargará una plantilla toda la información previamente registrada.
				* Si desea descargar una plantilla con los campos vacíos, dé clic en el siguiente botón: 
			</div>
			<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center">
				@component('components.buttons.button', ["variant" => "secondary", "buttonElement" => "a"])
					@slot('attributeEx')
						id="download_layout" href="{{ route('nomina.massive-template') }}"
					@endslot
					@slot('label')
						<i class="fas fa-download"></i> <span>DESCARGAR PLANTILLA</span>
					@endslot
				@endcomponent
			</div>
		@endcomponent
		@php
			$buttons = 
			[
				"separator"	=> 
				[
					[
						"kind"			=> "components.buttons.button-approval",
						"label" 		=> "coma (,)",
						"attributeEx" 	=> "value=\",\" name=\"separator\" id=\"separatorComa\""
					],
					[
						"kind" 			=> "components.buttons.button-approval",
						"label" 		=> "Punto y coma (;)",
						"attributeEx" 	=> "value=\";\" name=\"separator\" id=\"separatorPuntoComa\""
					]
				],
				"buttonEx" => 
				[
					[
						"kind" 			=> "components.buttons.button",
						"label" 		=> "<i class=\"fas fa-upload\"></i> <span>CARGAR PLANTILLA</span>",
						"variant" 		=> "primary",
						"attributeEx" 	=> "type=\"button\" id=\"update_to_select\"",
						"classEx" 		=> "w-max my-2"
					]
				]
			];
		@endphp
		@component("components.documents.select_file_csv",
			[
				"attributeEx" 		=> "id=\"container-data-2\"",
				"attributeExInput"	=> "name=\"csv_file\" id=\"csv\"",
				"buttons"			=> $buttons
			])
		@endcomponent
		@component("components.labels.title-divisor") Buscar Empleado <span class="help-btn" id="help-btn-search-employee"></span> @endcomponent
		@component("components.inputs.input-search", 
			[
				"attributeExInput"	=> "type=\"text\" title=\"Escriba aquí\" name=\"searchEmployee\" id=\"input-search\" placeholder=\"Ingrese un nombre de empleado\"",
				"attributeExButton"	=> "id=\"search-btn\" type=\"button\" title=\"Buscar\""
			])
		@endcomponent
		<div id="result"></div>
		<div class="alert alert-danger hidden" id="error_request" role="alert">SIN RESULTADOS.</div>
		@component("components.labels.title-divisor") Datos de Empleado @endcomponent
		@php
			$body		= [];
			$modelBody	= [];
			$modelHead	= [
				[
					["value" => "Nombre del Empleado"],
					["value" => "Faltas"],
					["value" => "Horas extra"],
					["value" => "Días festivos"],
					["value" => "Domingos trabajados"],
					["value" => "Acción"]
				]
			];
			if(isset($prenomina))
			{
				foreach($prenomina->employee->sortBy(function($employee){ return $employee->last_name.' '.$employee->scnd_last_name.' '.$employee->name; }) as $emp)
				{
					$dataObra		= App\PrenominaEmployee::where('idprenomina',$prenomina->idprenomina)->where('idreal_employee',$emp->id)->first();
					$periodicity	= $emp->workerDataVisible->first()->periodicity;
					$body = [ "classEx"	=> "tr_bodypayroll",
						[
							"content" => 
							[
								"label" => $emp->last_name.' '.$emp->scnd_last_name.' '.$emp->name
							]
						],
						[
							"content" =>
							[
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" name=\"employee_id[]\" value=\"".$dataObra->idreal_employee."\""
								],
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" value=\"".$periodicity."\"",
									"classEx"		=> "periodicity"
								],
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"text\" name=\"absence[]\" value=\"".$dataObra->absence."\"",
								]
							]
						],
						[
							"content" =>
							[
								"kind"			=> "components.inputs.input-text",
								"attributeEx"	=> "type=\"text\" name=\"extra_hours[]\" value=\"".$dataObra->extra_hours."\"",
							]
						],
						[
							"content" =>
							[
								"kind"			=> "components.inputs.input-text",
								"attributeEx"	=> "type=\"text\" name=\"holidays[]\" value=\"".$dataObra->holidays."\"",
							]
						],
						[
							"content" =>
							[
								"kind"			=> "components.inputs.input-text",
								"attributeEx"	=> "type=\"text\" name=\"sundays[]\" value=\"".$dataObra->sundays."\"",
							]
						],
						[
							"content" =>
							[
								"kind"		=> "components.buttons.button",
								"variant"	=> "red",
								"classEx"	=> "btn-delete-tr",
								"label"		=> "<span class=\"icon-x\"></span>"
							]
						]
					];
					$modelBody[] = $body;
				}
			}
		@endphp
		@component('components.tables.table',
			[
				"modelBody" => $modelBody,
				"modelHead" => $modelHead
			])
			@slot('attributeExBody')
				id="body-payroll" 
			@endslot
			@slot('classExBody')
				request-validate
			@endslot
			@slot('attributeEx')
				id="table"
			@endslot
		@endcomponent
		<div class="w-full mt-4 grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mb-6">
			@component('components.buttons.button', [ "variant" => "primary"])
				@slot('attributeEx')
					type="submit" name="enviar" value="ENVIAR PRENÓMINA"
				@endslot
					ENVIAR PRENÓMINA
			@endcomponent
			@component('components.buttons.button', ["variant" => "secondary"])
				@slot('attributeEx')
					type="submit" name="save" formaction="{{ route('nomina.employee-obra.save') }}" value="GUARDAR CAMBIOS"
				@endslot
				GUARDAR CAMBIOS	
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
		<div id="myModal" class="modal"></div>
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
		validation();
		$('#separatorComa').prop('checked',true);
		$('[name="absence[]"]').numeric({decimal: false, negative: false});
		$('[name="extra_hours[]"]').numeric({altDecimal: ".", decimalPlaces: 2, negative: false });
		$('[name="holidays[]"]').numeric({decimal: false, negative: false});
		$('[name="sundays[]"]').numeric({decimal: false, negative: false});
		@php
			$selects = collect([
				[
					"identificator"				=> ".js-project",
					"placeholder"				=> "Seleccione un proyecto",
					"maximumSelectionLength"	=> "1"
				],
				[
					"identificator"				=> ".js-typepayroll",
					"placeholder"				=> "Seleccione el tipo de nómina",
					"maximumSelectionLength"	=> "1"
				]
			]);
		@endphp
		@component('components.scripts.selects',['selects' => $selects]) @endcomponent
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
					label = $('#csv').next('label');
					label.html(labelVal);
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
			swal(
			{
				icon	: '{{ asset(getenv('LOADING_IMG')) }}',
				button	: false,
			});
			text = $('input[name="searchEmployee"]').val();
			idrealEmployee = [];
			$('.idemployee-table-prenomina').each(function()
			{
				idrealEmployee.push(Number($(this).val()));
			});
			option_id = {{ $option_id }};
			
			$.ajax(
			{
				type   : 'post',
				url    : '{{ route("nomina.prenomina-create.getemployee") }}',
				data   : {'search':text,'idrealEmployee':idrealEmployee,'option_id':option_id},
				success: function(data)
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
		})
		.on('click','.paginate a', function(e)
		{
			e.preventDefault();
			href   = $(this).attr('href');
			url    = new URL(href);
			params = new URLSearchParams(url.search);
			page   = params.get('page');
			swal(
			{
				icon	: '{{ asset(getenv('LOADING_IMG')) }}',
				button	: false,
			});
			text = $('input[name="searchEmployee"]').val();
			idrealEmployee = [];
			$('.idemployee-table-prenomina').each(function()
			{
				idrealEmployee.push(Number($(this).val()));
			});
			option_id = {{ $option_id }};
			$.ajax(
			{
				type	: 'post',
				url		: '{{ route("nomina.prenomina-create.getemployee") }}',
				data	: {'search':text,'idrealEmployee':idrealEmployee,'option_id':option_id,'page':page},
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
		})
		.on('click','.add-user',function()
		{
			$('.add-user').attr('disabled','disabled');
			employee_id  = $(this).parents('.tr_employee').find('.id-employee-table').val();
			$.ajax(
			{
				type 	: 'post',
				url 	: '{{ route('nomina.add-employee-obra') }}',
				data 	:{'employee_id':employee_id},
				success : function(data)
				{
					$('#body-payroll').append(data);
					$('[name="absence[]"]').numeric({decimal: false, negative: false});
					$('[name="extra_hours[]"]').numeric({altDecimal: ".", decimalPlaces: 2, negative: false });
					$('[name="holidays[]"]').numeric({decimal: false, negative: false});
					$('[name="sundays[]"]').numeric({decimal: false, negative: false});
				},
				error : function()
				{
					swal('','Sucedió un error, por favor intente de nuevo.','error');
					$('#body-payroll').html('');
				}
			});
			$('#result').stop(true,true).slideUp();
		})
		.on('click','.btn-delete-tr',function()
		{
			$(this).parents('.tr_bodypayroll').remove();
		})
		.on('click','.exit',function()
		{
			$('#myModal').hide();
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
			swal('Ayuda','Escriba el nombre del empleado y de clic en el ícono del buscador, posteriormente seleccione un empleado.','info');
		})
		.on('click','#help-btn-massive-employee',function()
		{
			swal('Ayuda','En este apartado podrá subir la plantilla que descargó previamente.','info');
		})
		.on('click','#help-btn-add-employee',function()
		{
			swal('Ayuda','En este apartado deberá ingresar las datos solicitados para cada empleado.','info');
		})
		.on('click','#help-btn-edit-employee',function()
		{
			swal('Ayuda','Al habilitar la edición los cambios realizados en "Información Laboral" serán guardados. Al estar deshabilitada la edición los cambios realizados en "Información Laboral" no serán guardados','info');
		})
		.on('click','#update_to_select',function(e)
		{
			filename		= $(this).parents('#container-data-2').find('[name="csv_file"]');
			delimiter		= $(this).parents('#container-data-2').find('[name="separator"]:checked');
			extention		= /\.csv/i;
			swal({
				icon				: '{{ asset(getenv('LOADING_IMG')) }}',
				button				: false,
				closeOnClickOutside	: false,
				closeOnEsc			: false
			});
			if (filename.val().search(extention) == -1)
			{
				swal('', 'El tipo de archivo no es soportado, por favor seleccione un archivo csv', 'warning');
				$('[name="csv_file"]').val('');
				$('.uploader-content').removeClass('image_success')
			}
			else if ($('[name="csv_file"]').size>315621376)
			{
				swal('', 'El tamaño máximo de su archivo no debe ser mayor a 300Mb', 'warning');
			}
			else
			{
				formData	= new FormData();
				formData.append(filename.attr('name'), filename.prop("files")[0]);
				formData.append(delimiter.attr('name'),delimiter.val());
				$.ajax(
				{
					type		: 'post',
					url			: '{{ route('nomina.employee-obra.massive') }}',
					data		: formData,
					contentType	: false,
					processData	: false,
					success		: function(r)
					{
						if(r.error=='DONE')
						{
							swal('',r.message, 'info');
							$('#body-payroll').html(r.table);
							$('[name="csv_file"]').val('');
							$('[name="csv_file"]').next('label').html(labelVal);
							$('[name="absence[]"]').numeric({decimal: false, negative: false});
							$('[name="extra_hours[]"]').numeric({altDecimal: ".", decimalPlaces: 2, negative: false });
							$('[name="holidays[]"]').numeric({decimal: false, negative: false});
							$('[name="sundays[]"]').numeric({decimal: false, negative: false});
							$('.uploader-content').removeClass('image_success')
						}
						else
						{
							swal('',r.message, 'error');
							$('[name="csv_file"]').val('');
							$('[name="csv_file"]').next('label').html(labelVal);
							$('.uploader-content').removeClass('image_success')
						}
					},
					error: function()
					{
						swal('', 'Ocurrió un error durante la carga del archivo, intente de nuevo, por favor', 'error');
						$('[name="csv_file"]').val('');
						$('[name="csv_file"]').next('label').html(labelVal);
						$('.uploader-content').removeClass('image_success')
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
		.on('change','[name="absence[]"]',function()
		{
			periodicity	= $(this).parents('.tr_bodypayroll').find('.periodicity').val();
			value		= $(this).val();

			if(periodicity == "01" && value > 1)
			{
				swal('','Las faltas no puede ser mayor a 1','error');
				$(this).val('0');
			}

			if(periodicity == "02" && value > 7)
			{
				swal('','Las faltas no puede ser mayor a 7','error');
				$(this).val('0');
			}

			if(periodicity == "03" && value > 14)
			{
				swal('','Las faltas no puede ser mayor a 14','error');
				$(this).val('0');
			}

			if(periodicity == "04" && value > 14)
			{
				swal('','Las faltas no puede ser mayor a 14','error');
				$(this).val('0');
			}

			if(periodicity == "05" && value > 30)
			{
				swal('','Las faltas no puede ser mayor a 30','error');
				$(this).val('0');
			}
		})
		.on('change','[name="sundays[]"]',function()
		{
			periodicity	= $(this).parents('.tr_bodypayroll').find('.periodicity').val();
			value		= $(this).val();

			if(periodicity == "02" && value > 1)
			{
				swal('','Los domingos no puede ser mayor a 1','error');
				$(this).val('0');
			}

			if(periodicity == "03" && value > 2)
			{
				swal('','Los domingos no puede ser mayor a 2','error');
				$(this).val('0');
			}

			if(periodicity == "04" && value > 2)
			{
				swal('','Los domingos no puede ser mayor a 2','error');
				$(this).val('0');
			}

			if(periodicity == "05" && value > 4)
			{
				swal('','Los domingos no puede ser mayor a 4','error');
				$(this).val('0');
			}
		})
		.on('change','[name="holidays[]"]',function()
		{
			periodicity	= $(this).parents('.tr_bodypayroll').find('.periodicity').val();
			value		= $(this).val();

			if(periodicity == "01" && value > 1)
			{
				swal('','Los días festivos no puede ser mayor a 1','error');
				$(this).val('0');
			}

			if(periodicity == "02" && value > 1)
			{
				swal('','Los días festivos no puede ser mayor a 1','error');
				$(this).val('0');
			}

			if(periodicity == "03" && value > 2)
			{
				swal('','Los días festivos no puede ser mayor a 2','error');
				$(this).val('0');
			}

			if(periodicity == "04" && value > 2)
			{
				swal('','Los días festivos no puede ser mayor a 2','error');
				$(this).val('0');
			}

			if(periodicity == "05" && value > 4)
			{
				swal('','Los días festivos no puede ser mayor a 4','error');
				$(this).val('0');
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
					employees   = $('#body-payroll .tr_bodypayroll').length;
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
		idemployee			= $('input[name="idemployee"]').val();
		name				= $('input[name="name"]').val();
		last_name			= $('input[name="last_name"]').val();
		curp				= $('input[name="curp"]').val();
		street				= $('input[name="street"]').val();
		number				= $('input[name="number"]').val();
		colony				= $('input[name="colony"]').val();
		cp					= $('input[name="cp"]').val();
		city				= $('input[name="city"]').val();
		state				= $('select[name="state"] option:selected').val();
		work_state			= $('select[name="work_state"] option:selected').val();
		work_enterprise		= $('select[name="work_enterprise"] option:selected').val();
		work_account		= $('select[name="work_account"] option:selected').val();
		work_direction		= $('select[name="work_direction"] option:selected').val();
		position			= $('input[name="position"]').val();
		work_income_date	= $('input[name="work_income_date"]').val();
		work_net_income		= $('input[name="work_net_income"]').val();
		work_nomina			= $('input[name="work_nomina"]').val();
		work_bonus			= $('input[name="work_bonus"]').val();

		if (idemployee == '' || name == '' || last_name == '' || curp == '' || street == '' || number == '' || colony == '' || cp == '' || city == '' || state == '' || work_state == '' || work_enterprise == '' || work_account == '' || work_direction == '' || position == '' || work_income_date == '' || work_net_income == '' || work_nomina == '' || work_bonus == '') 
		{
			return false;
		}
		else
		{
			return true;
		}
	}
</script>
@endsection
