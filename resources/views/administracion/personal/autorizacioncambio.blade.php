@extends('layouts.child_module')
@section('data')
	<div class="sm:text-center text-left my-5">
		A continuación podrá verificar la información de la solicitud antes de continuar con el proceso:
	</div>
	@php
		$requestUser   = App\User::find($request->idRequest);
		$elaborateUser = App\User::find($request->idElaborate);
		$modelTable    = 
		[
			["Folio:", $request->folio],
			["Título y fecha:", htmlentities($request->staff->first()->title)." - ".Carbon\Carbon::createFromFormat('Y-m-d', $request->staff->first()->datetitle)->format('d-m-Y')],
			["solicitante:", $requestUser->name." ".$requestUser->last_name." ".$requestUser->scnd_last_name],
			["Elaborado por:", $elaborateUser->name." ".$elaborateUser->last_name." ".$elaborateUser->scnd_last_name],
			["Empresa:", App\Enterprise::find($request->idEnterprise)->name],
			["Dirección:", App\Area::find($request->idArea)->name],
			["Departamento:", App\Department::find($request->idDepartment)->name],
			["Proyecto:", isset(App\Project::find($request->idProject)->proyectName) ? App\Project::find($request->idProject)->proyectName : 'No se seleccionó un proyecto'],
		];
	@endphp
	@component("components.templates.outputs.table-detail", 
	[
		"modelTable" => $modelTable, 
		"title"      => "Detalles de la Solicitud"
	]) 
	@endcomponent
@if(isset($request->staff->first()->schedule_start))
	@component('components.labels.title-divisor')  DATOS DE LA VACANTE @endcomponent
	<div class="flex px-4 md:px-20">
		@php
			$boss = App\User::find($request->staff->first()->boss);
			$modelTable =
			[
				"Jefe inmediato "					 => $boss->name." ".$boss->last_name." ".$boss->scnd_last_name,
				"Horario " 						  	 => $request->staff->first()->schedule_start." -  ".$request->staff->first()->schedule_end,
				"Rango de sueldo " 				  	 => "$ ".number_format($request->staff->first()->minSalary, 2)." - $ ".number_format($request->staff->first()->maxSalary, 2),
				"Motivo " 							 => htmlentities($request->staff->first()->reason),
				"Puesto " 							 => htmlentities($request->staff->first()->position),
				"Periodicidad " 					 => htmlentities($request->staff->first()->periodicity),
				"Descripción general de la vacante " => htmlentities($request->staff->first()->description)
			];
		@endphp
		@component("components.templates.outputs.table-detail-single", ["modelTable" => $modelTable]) 
		@endcomponent
	</div>
	<div class="px-4 md:px-20">
		@isset($request)
			@php
				$body 			= [];
				$modelBody		= [];
				$modelHead = ["Función", "Descripción"];
				foreach($request->staff->first()->functions as $function)
				{
					$body = 
					[
						[
							"show" => "true",
							"content" =>
							[
								"label" => $function->function != null ? htmlentities($function->function) : "No hay"
							]
						],
						[
							"show" => "true",
							"content" =>
							[
								"label" => $function->description != null ? htmlentities($function->description) : "No hay"
							]
						]
					];
					array_push($modelBody, $body);
				}
			@endphp
			@component('components.tables.alwaysVisibleTable',[
				"modelHead" 			=> $modelHead,
				"modelBody" 			=> $modelBody,
				"themeBody" 			=> "striped"
			])
				@slot('tittle')
					Funciones
				@endslot
			@endcomponent
		@else
			@component("components.labels.not-found", ["classEx" => "not-found-functions"]) 
				@slot("text")
					No se encontraron funciones registradas 
				@endslot
			@endcomponent
		@endisset
	</div>
	@foreach($request->staff as $staff)
		<div class="flex px-4 md:px-20">
			@php 
				$modelTable = [];
			@endphp
			@foreach($responsibilities as $responsibility)
				@php
					$temp = $responsibility->id;
					$flag = false;
				@endphp
				@foreach($staff->responsibility as $responsibilityStaff)
					@if($temp == $responsibilityStaff->id)
						@php
							$flag=true;
						@endphp
					@endif
				@endforeach
				@if($flag)
					@php
						$modelTable["Responsabilidades"] = $responsibility->responsibility;
					@endphp
				@endif
			@endforeach
			@php
				$modelTable["Habilidades requeridas"] = htmlentities($staff->habilities);
				$modelTable["Experiencia deseada"]    = htmlentities($staff->experience);
			@endphp
			
			@component("components.templates.outputs.table-detail-single", ["modelTable" => $modelTable]) 
			@endcomponent
		</div>
		<div class="px-4 md:px-20">
			@isset($staff)
				@php
					$body 			= [];
					$modelBody		= [];
					$modelHead = ["Deseables", "Descripción"];
					foreach($staff->desirable as $desirable)
					{
						$body = [
							[
								"show"    => "true",
								"content" =>
								[
									"label" => $desirable->desirable != null ? htmlentities($desirable->desirable) : "No hay"
								]
							],
							[
								"show"    => "true",
								"content" =>
								[
									"label" => $desirable->description != null ? htmlentities($desirable->description) : "No hay"
								]
							]
						];
						array_push($modelBody, $body);
					}
				@endphp
				@component('components.tables.alwaysVisibleTable',[
					"modelHead" => $modelHead,
					"modelBody" => $modelBody,
					"themeBody" => "striped"
				])
					@slot('tittle')
						Habilidades deseables
					@endslot
				@endcomponent
			@else
				@component("components.labels.not-found", ["classEx" => "not-found-habilities"]) 
					@slot("text")
						No se encontraron habilidades registradas 
					@endslot
				@endcomponent
			@endisset
		</div>
	@endforeach
	@else
		@component('components.labels.title-divisor') 
			LISTA DE EMPLEADOS 
		@endcomponent
		@php
			$body 		= [];
			$modelBody	= [];
			$modelHead	= ["Nombre", "Puesto", "Acción"];
			
			if(isset($request) && $request->staff()->exists())
			{
				foreach($request->staff as $staff_employees)
				{
					foreach($staff_employees->staffEmployees as $key => $emp)
					{
					$body = 
					[
						[
							"content" => 
							[
								[
									"label" => $emp->fullName()
								]
							]
						],
						[
							"content" => 
							[
								[
									"label" =>  htmlentities($emp->position)
								]
							]
						],
						[
							"content" => 
							[
								[
									"kind"        => "components.buttons.button", 
									"classEx" 	  => in_array($request->status, [2]) ? "view-employee hidden" : "view-employee",
									"variant" 	  => "secondary",
									"label" 	  => "<span class=\"icon-search\"></span>",
									"attributeEx" => "type=\"button\" data-toggle=\"modal\" data-target=\"#detailEmployee\""
								],
								[
									"kind"        => "components.inputs.input-text", 
									"attributeEx" => "name=\"rq_employee_id[]\" type=\"hidden\" value=\"".$emp->id."\""
								]
							]
						]
					];
					array_push($modelBody, $body);
					}
				}
			}
		@endphp
		@component('components.tables.alwaysVisibleTable',[
			"modelHead" => $modelHead,
			"modelBody" => $modelBody
		])
			@slot('attributeExBody')
				id="list_employees"
			@endslot
		@endcomponent
		@component("components.buttons.button",
			[
				"attributeEx" => "type=\"button\" id=\"btnAddEmployee\" data-toggle=\"modal\" data-target=\"#addEmployee\"",
				"label"       => "<span class=\"icon-plus\"></span> Agregar Empleado",
				"variant"     => "warning",
				"classEx"	  => !in_array($request->status, [2, 6, 7]) ? "hidden" : ""
			])
		@endcomponent
	@endif
	@component('components.labels.title-divisor')    DATOS DE REVISIÓN @endcomponent
	@php
		$modelTable                            = [];
		$modelTable["Revisó"]                  = $request->reviewedUser->name." ".$request->reviewedUser->last_name." ".$request->reviewedUser->scnd_last_name;
		$modelTable["Nombre de la Empresa"]    = App\Enterprise::find($request->idEnterpriseR)->name;
		$modelTable["Nombre del Departamento"] = App\Department::find($request->idDepartamentR)->name;
		$modelTable["Nombre de la Dirección"]  = $request->reviewedDirection->name;
		$modelTable["Nombre del Proyecto"]     = $request->reviewedProject->proyectName;
		$labels = "";
		foreach($request->labels as $label)
		{
			$labels = $labels." ".$label->description; 
		}
		$modelTable["Etiquetas"]   = $labels;
		$modelTable["Comentarios"] = !empty($request->checkComment) ? htmlentities($request->checkComment) : "Sin comentarios";
	@endphp
	@component("components.templates.outputs.table-detail-single", ["modelTable" => $modelTable]) 
	@endcomponent
	@component("components.forms.form",["methodEx" => "PUT", "attributeEx" => "method=\"POST\" action=\"".route('staff.authorization.update',$request->folio)."\" id=\"container-alta\""])
		<div class="my-4">	
			@component("components.containers.container-approval")
				@slot("attributeExButton")
					name="status" value="5" required id="Aprobar" 
				@endslot
				@slot("attributeExButtonTwo")
					name="status" value="7" required id="Rechazar" 
				@endslot
			@endcomponent
		</div>	
		<div id="aceptar" class="hidden w-full">
			@component('components.labels.label') Comentarios (opcional): @endcomponent
			@component('components.inputs.text-area')
				@slot('attributeEx')
					name="authorizeCommentA" id="authorizeCommentA" cols="90" rows="10"
				@endslot
			@endcomponent
		</div>
		<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mt-8">
			@component("components.buttons.button",["variant" => "primary"])
				@slot('attributeEx')
					type="submit"  name="enviar"
				@endslot
				@slot('classEx') 
					w-48 md:w-auto
				@endslot
				ENVIAR SOLICITUD
			@endcomponent	
			@component("components.buttons.button",["variant" => "reset"])
				@slot('attributeEx') 
					@if(isset($option_id)) 
						href="{{ url(getUrlRedirect($option_id)) }}" 
					@else 
						href="{{ url(getUrlRedirect($child_id)) }}" 
					@endif
				@endslot
				@slot('classEx') 
					load-actioner w-48 md:w-auto text-center
				@endslot
				REGRESAR
			@endcomponent		
		</div>
	@endcomponent
	@component('components.modals.modal', ["variant" => "large"])
		@slot('id')
			detailEmployee
		@endslot
		@slot('attributeEx')
			tabindex="-1" role="document"
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
		@slot('classExBody') modal-employee @endslot
		@slot('modalFooter')
			<div class="text-center">
				@component('components.buttons.button', ["variant" => "red"])
					@slot('attributeEx')
						type="button"
						data-dismiss="modal"
					@endslot
					<span class="icon-x"></span> Cerrar
				@endcomponent
			</div>
		@endslot
	@endcomponent
@endsection

@section('scripts')
	<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
	<script src="{{ asset('js/jquery-ui.js') }}"></script>
	<link rel="stylesheet" href="{{ asset('css/jquery.timepicker.min.css') }}">
	<script src="{{ asset('js/jquery.timepicker.min.js') }}"></script>
	<script src="{{ asset('js/datepair.min.js') }}"></script>
	<script src="{{ asset('js/jquery.datepair.min.js') }}"></script>
	<script>
		function containerAltaValidation()
		{
			$.validate(
			{
				form: '#container-alta',
				onError   : function()
				{
					swal('','Sucedió un error, por favor intente de nuevo.','error');
				},
				onSuccess : function($form)
				{
					if($('input[name="status"]').is(':checked'))
					{
						swal('Cargando',{
							icon: '{{ asset(getenv('LOADING_IMG')) }}',
							button: false,
						});
						return true;
					}
					else
					{
						swal('', 'Debe seleccionar al menos un estado', 'error');
						return false;
					}
				}
			});
		}
		$(document).ready(function()
		{
			containerAltaValidation()
			@php
				$selects = collect([
					[
						"identificator"          => ".js-roles", 
						"placeholder"            => "Seleccione el rol", 
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => ".js-users", 
						"placeholder"            => "Seleccione el solicitante", 
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => ".js-enterprises", 
						"placeholder"            => "Seleccione la empresa", 
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => ".js-areas", 
						"placeholder"            => "Seleccione la dirección", 
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => ".js-departments", 
						"placeholder"            => "Seleccione el departamento", 
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => ".js-projects", 
						"placeholder"            => "Seleccione el proyecto/contrato", 
						"maximumSelectionLength" => "1"
					],
					[
						"identificator" 		 => ".js-responsibilities", 
						"placeholder"   		 => "Seleccione las responsabilidades"
					],
					[
						"identificator"          => ".js-boss", 
						"placeholder"            => "Seleccione el jefe inmediato", 
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => ".js-enterprisesR",
						"placeholder"			 => "Seleccione el Dirección",
						"maximumSelectionLength" => 1
					],
					[
						"identificator"          => ".js-areasR", 
						"placeholder"            => "Seleccione la dirección", 
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => ".js-accounts",
						"placeholder"			 => "Seleccione la clasificación del gasto",
						"maximumSelectionLength" => 1
					],
					[
						"identificator"          => ".js-departmentsR",
						"placeholder"			 => "Seleccione el departamento",
						"maximumSelectionLength" => 1
					],
					[
						"identificator"          => ".js-projectsR", 
						"placeholder"            => "Seleccione el proyecto", 
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => ".js-labelsR",
						"placeholder"			 => "Seleccione las etiquetas correspondientes"
					]
				]);
			@endphp
			@component("components.scripts.selects",["selects" => $selects])
			@endcomponent
			$('#timePair .time.start').timepicker(
			{
				'timeFormat'	: 'H:i',
				'step'			: 30,
				'maxTime'		: '22:00:00',
				'minTime'		: '05:00:00',
			});
			$('#timePair .time.end').timepicker(
			{
				'showDuration'	: true,
				'timeFormat'	: 'H:i',
				'step'			: 30,
				'maxTime'		: '22:00:00',
				'minTime'		: '05:00:00',
			});
			$('#timePair').datepair();
			$(document).on('change','input[name="status"]',function()
			{
				$("#aceptar").slideDown("slow");
			})
			.on('click','.view-employee',function()
			{
				employee_id = $(this).parents('.tr').find('[name="rq_employee_id[]"]').val();
				$.ajax(
				{
					type	: 'post',
					url		: '{{ route("staff.view-detail-employee") }}',
					data	: {'employee_id':employee_id},
					success : function(data)
					{
						$('.modal-employee').html(data);
					},
					error : function()
					{
						swal('','Sucedió un error, por favor intente de nuevo.','error');
						$('#detailEmployee').hide();
					}
				});
			});
		});
	</script>
@endsection
