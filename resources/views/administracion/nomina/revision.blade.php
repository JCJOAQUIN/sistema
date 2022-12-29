@extends('layouts.child_module')
@section('data')
	@component('components.forms.form', ["attributeEx" => "method=\"POST\" id=\"container-alta\" action=\"".route('nomina.review.update', $request->folio)."\"", "methodEx" => "PUT"])
		<div class="sm:text-center text-left my-5">
			A continuación podrá verificar la información de la solicitud antes de continuar con el proceso:
		</div>
		@php
			$departmentId = $request->idDepartment == 11 ? 'Obra' : 'Administrativa';
			$modelTable = [
				["Folio:",			$request->folio],
				["Título y fecha:",	$request->nominasReal->first()->title.' - '.Carbon\Carbon::createFromFormat('Y-m-d',$request->nominasReal->first()->datetitle)->format('d-m-Y')],
				["Solicitante:",	$request->requestUser->fullName()],
				["Elaborado por:",	$request->elaborateUser->fullName()],
				["Tipo:",			$request->nominasReal->first()->typePayroll->description],
				["Categoría:",		$departmentId.' - '.$request->nominasReal->first()->typeNomina()]
			];
			switch($request->nominasReal->first()->idCatTypePayroll)
			{
				case('001'):
					array_push($modelTable, ["Rango de fechas:", Carbon\Carbon::createFromFormat('Y-m-d',$request->nominasReal->first()->from_date)->format('d-m-Y').' - '.Carbon\Carbon::createFromFormat('Y-m-d',$request->nominasReal->first()->to_date)->format('d-m-Y') ]);
					array_push($modelTable, ["Periodicidad:", App\CatPeriodicity::find($request->nominasReal->first()->idCatPeriodicity)->description]);
				break;
				case('002'):
				break;
				case('003'):
					array_push($modelTable, ["Fecha de baja:", Carbon\Carbon::createFromFormat('Y-m-d',$request->nominasReal->first()->down_date)->format('d-m-Y')]);
				break;
				case('004'):
					array_push($modelTable, ["Fecha de baja:", Carbon\Carbon::createFromFormat('Y-m-d',$request->nominasReal->first()->down_date)->format('d-m-Y')]);
				break;
				case('005'):
				break;
				case('006'):
					array_push($modelTable, ["PTU por pagar:", '$ '.number_format($request->nominasReal->first()->ptu_to_pay,2)]);
				break;
			}
		@endphp
		@component('components.templates.outputs.table-detail', ["modelTable" => $modelTable])
			@slot('title')
				Detalles de la Solicitud
			@endslot
			@slot('classEx')
				mb-4
			@endslot
		@endcomponent
		@component('components.labels.title-divisor') Lista de Empleados <span class="help-btn" id="help-btn-add-employee"></span> @endcomponent
		@if($request->taxPayment == 1)
			@switch($request->nominasReal->first()->idCatTypePayroll)
				@case('001')
					<div class="float-right mt-4">
						@component("components.buttons.button", ["variant" => "success"])
							@slot("classEx")
								export
							@endslot
							@slot('buttonElement')
								a
							@endslot
							@slot("attributeEx")
								href="{{ route('nomina.export.salary',$request->folio) }}"
							@endslot
							<span>Exportar a Excel</span><span class="icon-file-excel"></span>
						@endcomponent
					</div>
					@php
						$body		= [];
						$modelBody	= [];
						$modelHead	= [
							[
								["value" => "Nombre del Empleado", "classEx" => "sticky inset-x-0"],
								["value" => "Desde"],
								["value" => "Hasta"],
								["value" => "Periodicidad"],
								["value" => "Faltas"],
								["value" => "Horas extra"],
								["value" => "Días festivos"],
								["value" => "Domingos trabajados"],
								["value" => "Préstamo (Percepción)"],
								["value" => "Préstamo (Retención)"],
								["value" => "Acciones"]
							]
						];
						foreach(App\NominaEmployee::join('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')->where('nomina_employees.idnomina',$request->nominasReal->first()->idnomina)->where('nomina_employees.visible',1)->orderBy('real_employees.last_name','ASC')->orderBy('real_employees.scnd_last_name','ASC')->orderBy('real_employees.name','ASC')->select('nomina_employees.*')->get() as $n)
						{
							$dataWorker = $n->workerData->first()->paymentWay == 1 && $n->employee->first()->bankData()->where('visible',1)->where('type',1)->exists() ? $n->employee->first()->bankData->where('visible',1)->where('type',1)->last()->id : '';
							$body = [ "classEx"	=> "tr_payroll",
								[
									"classEx" => "sticky inset-x-0",
									"content" =>
									[
										[
											"kind"			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"hidden\" value=\"001\"",
											"classEx"		=> "type_payroll"
										],
										[
											"kind"			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"hidden\" name=\"idnominaEmployee_request[]\" value=\"".$n->idnominaEmployee."\"",
											"classEx"		=> "idnominaEmployee"
										],
										[
											"kind"			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"hidden\" name=\"idrealEmployee[]\" value=\"".$n->idrealEmployee."\"",
											"classEx"		=> "idrealEmployee"
										],
										[
											"kind"			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"hidden\" name=\"idworkingData[]\" value=\"".$n->idworkingData."\"",
											"classEx"		=> "idworkingData"
										],
										[
											"kind"			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"hidden\" name=\"paymentWay[]\" value=\"".$n->workerData->first()->paymentWay."\"",
											"classEx"		=> "paymentWay"
										],
										[
											"kind"			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"hidden\" name=\"idemployeeAccount[]\" value=\"".$dataWorker."\"",
											"classEx"		=> "idemployeeAccount"
										],
										[
											"label" => $n->employee->first()->last_name.' '.$n->employee->first()->scnd_last_name.' '.$n->employee->first()->name
										]
									]
								],
								[
									"content" =>
									[
										"label" => Carbon\Carbon::createFromFormat('Y-m-d',$n->from_date)->format('d-m-Y')
									]
								],
								[
									"content" =>
									[
										"label" => Carbon\Carbon::createFromFormat('Y-m-d',$n->to_date)->format('d-m-Y')
									]
								],
								[
									"content" =>
									[
										"label" => $n->idCatPeriodicity!= '' ? App\CatPeriodicity::where('c_periodicity',$n->idCatPeriodicity)->first()->description : ''
									]
								],
								[
									"content" =>
									[
										"label" => $n->absence!= '' ? $n->absence : '---'
									]
								],
								[
									"content" =>
									[
										"label" => $n->extra_hours!= '' ? $n->extra_hours : '---'
									]
								],
								[
									"content" =>
									[
										"label" => $n->holidays!= '' ? $n->holidays : '---'
									]
								],
								[
									"content" => 
									[
										"label" => $n->sundays!= '' ? $n->sundays : '---' 
									]
								],
								[
									"content" =>
									[
										"label" => $n->loan_perception!= '' ? $n->loan_perception : '---' 
									]
								],
								[
									"content" =>
									[
										"label" => $n->loan_retention!= '' ? $n->loan_retention : '---'
									]
								],
								[
									"content" =>
									[
										[
											"kind" 			=> "components.buttons.button",
											"variant"		=> "success",
											"classEx"		=> "btn-edit-user",
											"label"			=> "<span class=\"icon-pencil\"></span>",
											"attributeEx"	=> "title=\"Editar Información del Empleado\" type=\"button\" data-toggle=\"modal\" data-target=\"#myModal\""
										],
										[
											"kind" 			=> "components.buttons.button",
											"variant"		=> "primary",
											"classEx"		=> "btn-edit-data-nomina",
											"label"			=> "<span class=\"icon-pencil\"></span>",
											"attributeEx"	=> "title=\"Editar Información de Nómina\" type=\"button\" data-toggle=\"modal\" data-target=\"#myModal\""
										],
										[
											"kind" 			=> "components.buttons.button",
											"variant"		=> "secondary",
											"classEx"		=> "btn-edit-calculate",
											"label"			=> "<span class=\"icon-pencil\"></span>",
											"attributeEx"	=> "title=\"Editar Cálculo de Nómina\" type=\"button\" data-toggle=\"modal\" data-target=\"#myModal\""
										],
										[
											"kind" 			=> "components.buttons.button",
											"variant"		=> "red",
											"classEx"		=> "btn-delete-employee",
											"label"			=> "<span class=\"icon-x\"></span>",
											"attributeEx"	=> "title=\"Eliminar\" type=\"button\" data-link=\"".route('nomina.delete.employee',$n->idnominaEmployee)."\""
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
				@break
				@case('002')
					<div class="float-right mt-4">
						@component("components.buttons.button", ["variant" => "success"])
							@slot("classEx")
								export
							@endslot
							@slot('buttonElement')
								a
							@endslot
							@slot("attributeEx")
								href="{{ route('nomina.export.bonus',$request->folio) }}"
							@endslot
							<span>Exportar a Excel</span><span class="icon-file-excel"></span>
						@endcomponent
					</div>
					@php
						$body		= [];
						$modelBody	= [];
						$modelHead	= [
							["value" => "Nombre del Empleado", "classEx" => "sticky inset-x-0"],
							["value" => "Días para aguinaldo"],
							["value" => "Acciones"]
						];
						foreach(App\NominaEmployee::join('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')->where('nomina_employees.idnomina',$request->nominasReal->first()->idnomina)->where('nomina_employees.visible',1)->orderBy('real_employees.last_name','ASC')->orderBy('real_employees.scnd_last_name','ASC')->orderBy('real_employees.name','ASC')->select('nomina_employees.*')->get() as $n)
						{
							$body = [ "classEx"	=> "tr_payroll",
								[
									"classEx" => "sticky inset-x-0",
									"content" =>
									[
										[
											"kind"			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"hidden\" value=\"002\"",
											"classEx"		=> "type_payroll"
										],
										[
											"kind"			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"hidden\" value=\"".$n->idnominaEmployee."\"",
											"classEx"		=> "idnominaEmployee"
										],
										[
											"kind"			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"hidden\" name=\"idrealEmployee[]\" value=\"".$n->idrealEmployee."\"",
											"classEx"		=> "idrealEmployee"
										],
										[
											"kind"			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"hidden\" name=\"idworkingData[]\" value=\"".$n->idworkingData."\"",
											"classEx"		=> "idworkingData"
										],
										[
											"label" => $n->employee->first()->last_name.' '.$n->employee->first()->scnd_last_name.' '.$n->employee->first()->name
										]
									]
								],
								[
									"content" =>
									[
										"label" => $n->day_bonus 
									]
								],
								[
									"content" =>
									[
										[
											"kind" 			=> "components.buttons.button",
											"variant"		=> "success",
											"classEx"		=> "btn-edit-user",
											"label"			=> "<span class=\"icon-pencil\"></span>",
											"attributeEx"	=> "title=\"Editar Información del Empleado\" type=\"button\" data-toggle=\"modal\" data-target=\"#myModal\""
										],
										[
											"kind" 			=> "components.buttons.button",
											"variant"		=> "primary",
											"classEx"		=> "btn-edit-data-nomina",
											"label"			=> "<span class=\"icon-pencil\"></span>",
											"attributeEx"	=> "title=\"Editar Información de Nómina\" type=\"button\" data-toggle=\"modal\" data-target=\"#myModal\""
										],
										[
											"kind" 			=> "components.buttons.button",
											"variant"		=> "secondary",
											"classEx"		=> "btn-edit-calculate",
											"label"			=> "<span class=\"icon-pencil\"></span>",
											"attributeEx"	=> "title=\"Editar Cálculo de Nómina\" type=\"button\" data-toggle=\"modal\" data-target=\"#myModal\""
										],
										[
											"kind" 			=> "components.buttons.button",
											"variant"		=> "red",
											"classEx"		=> "btn-delete-employee",
											"label"			=> "<span class=\"icon-x\"></span>",
											"attributeEx"	=> "title=\"Eliminar\" type=\"button\" data-link=\"".route('nomina.delete.employee',$n->idnominaEmployee)."\""
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
				@break
				@case('003')
					<div class="float-right mt-4">
						@component("components.buttons.button", ["variant" => "success"])
							@slot("classEx")
								export
							@endslot
							@slot('buttonElement')
								a
							@endslot
							@slot("attributeEx")
								href="{{ route('nomina.export.settlement',$request->folio) }}"
							@endslot
							<span>Exportar a Excel</span><span class="icon-file-excel"></span>
						@endcomponent
					</div>
					@php
						$body		= [];
						$modelBody	= [];
						$modelHead	= [
							[
								["value" => "Nombre del Empleado", "classEx" => "sticky inset-x-0"],
								["value" => "Fecha de baja"],
								["value" => "Días trabajados"],
								["value" => "Otras percepciones"],
								["value" => "Acciones"]
							]
						];
						foreach(App\NominaEmployee::join('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')->where('nomina_employees.idnomina',$request->nominasReal->first()->idnomina)->where('nomina_employees.visible',1)->orderBy('real_employees.last_name','ASC')->orderBy('real_employees.scnd_last_name','ASC')->orderBy('real_employees.name','ASC')->select('nomina_employees.*')->get() as $n)
						{
							$body = [ "classEx"	=> "tr_payroll",
								[
									"classEx" => "sticky inset-x-0",
									"content" =>
									[
										[
											"kind" 			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"hidden\" value=\"003\"",
											"classEx"		=> "type_payroll"
										],
										[
											"kind" 			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"hidden\" value=\"".$n->idnominaEmployee."\"",
											"classEx"		=> "idnominaEmployee"
										],
										[
											"kind" 			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"hidden\" name=\"idrealEmployee[]\" value=\"".$n->idrealEmployee."\"",
											"classEx"		=> "idrealEmployee"
										],
										[
											"kind" 			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"hidden\" name=\"idworkingData[]\" value=\"".$n->idworkingData."\"",
											"classEx"		=> "idworkingData"
										],
										[
											"label"			=> $n->employee->first()->last_name.' '.$n->employee->first()->scnd_last_name.' '.$n->employee->first()->name
										]
									]
								]
							];
							if($request->status == 2)
							{
								array_push($body, [
									"content" => 
									[
										"kind"			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"text\" name=\"down_date[]\" data-validation=\"required\" placeholder=\"Ingrese la fecha\" readonly=\"readonly\"",
										"classEx"		=> "datepicker2 down_date w-40"
									]
								]);
							}
							else
							{
								array_push($body, ["content" => [ "label" => ($n->down_date != "" ? (Carbon\Carbon::createFromFormat('Y-m-d',$n->down_date)->format('d-m-Y')) : "---") ]]);
							}
							if($request->status == 2)
							{
								array_push($body, [
									"content" => 
									[
										"kind"			=> "components.inputs.input-text",
										"classEx"		=> "w-40",
										"attributeEx"	=> "type=\"text\" name=\"worked_days[]\" placeholder=\"Ingrese los días trabajados\" value=\"365\"",
									]
								]);
							}
							else
							{
								array_push($body, ["content" => [ "label" => $n->worked_days]]);
							}
							if($request->status == 2)
							{
								array_push($body, [
									"content" => 
									[
										"kind"			=> "components.inputs.input-text",
										"classEx"		=> "w-40",
										"attributeEx"	=> "type=\"text\" name=\"other_perception[]\" placeholder=\"Ingrese otra percepción\" value=\"365\"",
									]
								]);
							}
							else
							{
								array_push($body, ["content" => [ "label" => $n->other_perception]]);
							}
							array_push($body, [
								"content" =>
								[
									[
										"kind" 			=> "components.buttons.button",
										"variant"		=> "success",
										"classEx"		=> "btn-edit-user",
										"label"			=> "<span class=\"icon-pencil\"></span>",
										"attributeEx"	=> "title=\"Editar Información del Empleado\" type=\"button\" data-toggle=\"modal\" data-target=\"#myModal\""
									],
									[
										"kind" 			=> "components.buttons.button",
										"variant"		=> "primary",
										"classEx"		=> "btn-edit-data-nomina",
										"label"			=> "<span class=\"icon-pencil\"></span>",
										"attributeEx"	=> "title=\"Editar Información de Nómina\" type=\"button\" data-toggle=\"modal\" data-target=\"#myModal\""
									],
									[
										"kind" 			=> "components.buttons.button",
										"variant"		=> "secondary",
										"classEx"		=> "btn-edit-calculate",
										"label"			=> "<span class=\"icon-pencil\"></span>",
										"attributeEx"	=> "title=\"Editar Cálculo de Nómina\" type=\"button\" data-toggle=\"modal\" data-target=\"#myModal\""
									],
									[
										"kind" 			=> "components.buttons.button",
										"variant"		=> "red",
										"classEx"		=> "btn-delete-employee",
										"label"			=> "<span class=\"icon-x\"></span>",
										"attributeEx"	=> "title=\"Eliminar\" type=\"button\" data-link=\"".route('nomina.delete.employee',$n->idnominaEmployee)."\""
									]
								]
							]);
							$modelBody[] = $body;
						}
					@endphp
					@component('components.tables.table', [
						"modelBody" => $modelBody,
						"modelHead" => $modelHead
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
				@break
				@case('004')
					<div class="float-right mt-4">
						@component("components.buttons.button", ["variant" => "success"])
							@slot("classEx")
								export
							@endslot
							@slot('buttonElement')
								a
							@endslot
							@slot("attributeEx")
								href="{{ route('nomina.export.liquidation',$request->folio) }}"
							@endslot
							<span>Exportar a Excel</span><span class="icon-file-excel"></span>
						@endcomponent
					</div>
					@php
						$body		= [];
						$modelBody	= [];
						$modelHead	= [
							[
								["value" => "Nombre del Empleado", "classEx" => "sticky inset-x-0"],
								["value" => "Fecha de baja"],
								["value" => "Días trabajados"],
								["value" => "Otras percepciones"],
								["value" => "Acciones"]
							]
						];
						foreach(App\NominaEmployee::join('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')->where('nomina_employees.idnomina',$request->nominasReal->first()->idnomina)->where('nomina_employees.visible',1)->orderBy('real_employees.last_name','ASC')->orderBy('real_employees.scnd_last_name','ASC')->orderBy('real_employees.name','ASC')->select('nomina_employees.*')->get() as $n)
						{
							$body = [ "classEx"	=> "tr_payroll",
								[
									"classEx" => "sticky inset-x-0",
									"content" =>
									[
										[
											"kind" 			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"hidden\" value=\"004\"",
											"classEx"		=> "type_payroll"
										],
										[
											"kind" 			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"hidden\" value=\"".$n->idnominaEmployee."\"",
											"classEx"		=> "idnominaEmployee"
										],
										[
											"kind" 			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"hidden\" name=\"idrealEmployee[]\" value=\"".$n->idrealEmployee."\"",
											"classEx"		=> "idrealEmployee"
										],
										[
											"kind" 			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"hidden\" name=\"idworkingData[]\" value=\"".$n->idworkingData."\"",
											"classEx"		=> "idworkingData"
										],
										[
											"label"			=> $n->employee->first()->last_name.' '.$n->employee->first()->scnd_last_name.' '.$n->employee->first()->name
										]
									]
								]
							];
							if($request->status == 2)
							{
								array_push($body, [
									"content" => 
									[
										"kind"			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"text\" name=\"down_date[]\" data-validation=\"required\" placeholder=\"Ingrese la fecha\" readonly=\"readonly\"",
										"classEx"		=> "datepicker2 down_date w-40"
									]
								]);
							}
							else
							{
								array_push($body, ["content" => [ "label" =>Carbon\Carbon::createFromFormat('Y-m-d',$n->down_date)->format('d-m-Y') ]]);
							}
							if($request->status == 2)
							{
								array_push($body, [
									"content" => 
									[
										"kind"			=> "components.inputs.input-text",
										"classEx"		=> "w-40",
										"attributeEx"	=> "type=\"text\" name=\"worked_days[]\" placeholder=\"Ingrese los días trabajados\" value=\"365\"",
									]
								]);
							}
							else
							{
								array_push($body, ["content" => [ "label" => $n->worked_days]]);
							}
							if($request->status == 2)
							{
								array_push($body, [
									"content" => 
									[
										"kind"			=> "components.inputs.input-text",
										"classEx"		=> "w-40",
										"attributeEx"	=> "type=\"text\" name=\"other_perception[]\" placeholder=\"Ingrese otra percepción\" value=\"365\"",
									]
								]);
							}
							else
							{
								array_push($body, ["content" => [ "label" => $n->other_perception]]);
							}
							array_push($body, [
								"content" =>
								[
									[
										"kind" 			=> "components.buttons.button",
										"variant"		=> "success",
										"classEx"		=> "btn-edit-user",
										"label"			=> "<span class=\"icon-pencil\"></span>",
										"attributeEx"	=> "title=\"Editar Información del Empleado\" type=\"button\" data-toggle=\"modal\" data-target=\"#myModal\""
									],
									[
										"kind" 			=> "components.buttons.button",
										"variant"		=> "primary",
										"classEx"		=> "btn-edit-data-nomina",
										"label"			=> "<span class=\"icon-pencil\"></span>",
										"attributeEx"	=> "title=\"Editar Información de Nómina\" type=\"button\" data-toggle=\"modal\" data-target=\"#myModal\""
									],
									[
										"kind" 			=> "components.buttons.button",
										"variant"		=> "secondary",
										"classEx"		=> "btn-edit-calculate",
										"label"			=> "<span class=\"icon-pencil\"></span>",
										"attributeEx"	=> "title=\"Editar Cálculo de Nómina\" type=\"button\" data-toggle=\"modal\" data-target=\"#myModal\""
									],
									[
										"kind" 			=> "components.buttons.button",
										"variant"		=> "red",
										"classEx"		=> "btn-delete-employee",
										"label"			=> "<span class=\"icon-x\"></span>",
										"attributeEx"	=> "title=\"Eliminar\" type=\"button\" data-link=\"".route('nomina.delete.employee',$n->idnominaEmployee)."\""
									]
								]
							]);
							$modelBody[] = $body;
						}
					@endphp
					@component('components.tables.table', [
						"modelBody" => $modelBody,
						"modelHead" => $modelHead
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
				@break
				@case('005')
					<div class="float-right mt-4">
						@component("components.buttons.button", ["variant" => "success"])
							@slot("classEx")
								export
							@endslot
							@slot('buttonElement')
								a
							@endslot
							@slot("attributeEx")
								href="{{ route('nomina.export.vacationpremium',$request->folio) }}"
							@endslot
							<span>Exportar a Excel</span><span class="icon-file-excel"></span>
						@endcomponent
					</div>
					@php
						$body		= [];
						$modelBody	= [];
						$modelHead	= [
							[
								["value" => "Nombre del Empleado", "classEx" => "sticky inset-x-0"],
								["value" => "Días trabajados"],
								["value" => "Acciones"]
							]
						];
						foreach(App\NominaEmployee::join('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')->where('nomina_employees.idnomina',$request->nominasReal->first()->idnomina)->where('nomina_employees.visible',1)->orderBy('real_employees.last_name','ASC')->orderBy('real_employees.scnd_last_name','ASC')->orderBy('real_employees.name','ASC')->select('nomina_employees.*')->get() as $n)
						{
							$body = [ "classEx"	=> "tr_payroll",
								[
									"classEx" => "sticky inset-x-0",
									"content" =>
									[
										[
											"kind" 			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"hidden\" value=\"005\"",
											"classEx"		=> "type_payroll"
										],
										[
											"kind" 			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"hidden\" value=\"".$n->idnominaEmployee."\"",
											"classEx"		=> "idnominaEmployee"
										],
										[
											"kind" 			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"hidden\" name=\"idrealEmployee[]\" value=\"".$n->idrealEmployee."\"",
											"classEx"		=> "idrealEmployee"
										],
										[
											"kind" 			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"hidden\" name=\"idworkingData[]\" value=\"".$n->idworkingData."\"",
											"classEx"		=> "idworkingData"
										],
										[
											"label"			=> $n->employee->first()->last_name.' '.$n->employee->first()->scnd_last_name.' '.$n->employee->first()->name
										]
									]
								],
								[
									"content" =>
									[
										"label" => $n->worked_days
									]
								],
								[
									"content" =>
									[
										[
											"kind" 			=> "components.buttons.button",
											"variant"		=> "success",
											"classEx"		=> "btn-edit-user",
											"label"			=> "<span class=\"icon-pencil\"></span>",
											"attributeEx"	=> "title=\"Editar Información del Empleado\" type=\"button\" data-toggle=\"modal\" data-target=\"#myModal\""
										],
										[
											"kind" 			=> "components.buttons.button",
											"variant"		=> "primary",
											"classEx"		=> "btn-edit-data-nomina",
											"label"			=> "<span class=\"icon-pencil\"></span>",
											"attributeEx"	=> "title=\"Editar Información de Nómina\" type=\"button\" data-toggle=\"modal\" data-target=\"#myModal\""
										],
										[
											"kind" 			=> "components.buttons.button",
											"variant"		=> "secondary",
											"classEx"		=> "btn-edit-calculate",
											"label"			=> "<span class=\"icon-pencil\"></span>",
											"attributeEx"	=> "title=\"Editar Cálculo de Nómina\" type=\"button\" data-toggle=\"modal\" data-target=\"#myModal\""
										],
										[
											"kind" 			=> "components.buttons.button",
											"variant"		=> "red",
											"classEx"		=> "btn-delete-employee",
											"label"			=> "<span class=\"icon-x\"></span>",
											"attributeEx"	=> "title=\"Eliminar\" type=\"button\" data-link=\"".route('nomina.delete.employee',$n->idnominaEmployee)."\""
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
				@break
				@case('006')
					<div class="float-right mt-4">
						@component("components.buttons.button", ["variant" => "success"])
							@slot("classEx")
								export
							@endslot
							@slot('buttonElement')
								a
							@endslot
							@slot("attributeEx")
								href="{{ route('nomina.export.profitsharing',$request->folio) }}"
							@endslot
							<span>Exportar a Excel</span><span class="icon-file-excel"></span>
						@endcomponent
					</div>
					@php
						$body		= [];
						$modelBody	= [];
						$modelHead	= [
							[
								["value" => "Nombre del Empleado", "classEx" => "sticky inset-x-0"],
								["value" => "Días trabajados"],
								["value" => "Acciones"]
							]
						];
						foreach(App\NominaEmployee::join('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')->where('nomina_employees.idnomina',$request->nominasReal->first()->idnomina)->where('nomina_employees.visible',1)->orderBy('real_employees.last_name','ASC')->orderBy('real_employees.scnd_last_name','ASC')->orderBy('real_employees.name','ASC')->select('nomina_employees.*')->get() as $n)
						{
							$body = [ "classEx"	=> "tr_payroll",
								[
									"classEx" => "sticky inset-x-0",
									"content" =>
									[
										[
											"kind" 			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"hidden\" value=\"006\"",
											"classEx"		=> "type_payroll"
										],
										[
											"kind" 			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"hidden\" value=\"".$n->idnominaEmployee."\"",
											"classEx"		=> "idnominaEmployee"
										],
										[
											"kind" 			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"hidden\" name=\"idrealEmployee[]\" value=\"".$n->idrealEmployee."\"",
											"classEx"		=> "idrealEmployee"
										],
										[
											"kind" 			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"hidden\" name=\"idworkingData[]\" value=\"".$n->idworkingData."\"",
											"classEx"		=> "idworkingData"
										],
										[
											"label"			=> $n->employee->first()->last_name.' '.$n->employee->first()->scnd_last_name.' '.$n->employee->first()->name
										]
									]
								],
								[
									"content" =>
									[
										"label" => $n->profitSharing->first()->workedDays
									]
								],
								[
									"content" =>
									[
										[
											"kind" 			=> "components.buttons.button",
											"variant"		=> "success",
											"classEx"		=> "btn-edit-user",
											"label"			=> "<span class=\"icon-pencil\"></span>",
											"attributeEx"	=> "title=\"Editar Información del Empleado\" type=\"button\" data-toggle=\"modal\" data-target=\"#myModal\""
										],
										[
											"kind" 			=> "components.buttons.button",
											"variant"		=> "primary",
											"classEx"		=> "btn-edit-data-nomina",
											"label"			=> "<span class=\"icon-pencil\"></span>",
											"attributeEx"	=> "title=\"Editar Información de Nómina\" type=\"button\" data-toggle=\"modal\" data-target=\"#myModal\""
										],
										[
											"kind" 			=> "components.buttons.button",
											"variant"		=> "secondary",
											"classEx"		=> "btn-edit-calculate",
											"label"			=> "<span class=\"icon-pencil\"></span>",
											"attributeEx"	=> "title=\"Editar Cálculo de Nómina\" type=\"button\" data-toggle=\"modal\" data-target=\"#myModal\""
										],
										[
											"kind" 			=> "components.buttons.button",
											"variant"		=> "red",
											"classEx"		=> "btn-delete-employee",
											"label"			=> "<span class=\"icon-x\"></span>",
											"attributeEx"	=> "title=\"Eliminar\" type=\"button\" data-link=\"".route('nomina.delete.employee',$n->idnominaEmployee)."\""
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
				@break
			@endswitch
		@else
			@component('components.labels.not-found', ["variant" => "note"])
				* Si desea actualizar el complemento debe modificar la columna "sueldo_complemento" que está marcada en color naranja en el archivo de Excel
			@endcomponent
			<div class="float-right">
				@if($request->nominasReal->first()->type_nomina == 3)
					@component("components.buttons.button", ["variant" => "success"])
						@slot("classEx")
							export
						@endslot
						@slot('buttonElement')
							a
						@endslot
						@slot("attributeEx")
							href="{{ route('nomina.nom35.export',$request->folio) }}"
						@endslot
						<span>Exportar datos a Excel</span><span class="icon-file-excel"></span>
					@endcomponent
					@component("components.buttons.button", ["variant" => "success"])
						@slot("classEx")
							export
						@endslot
						@slot('buttonElement')
							a
						@endslot
						@slot("attributeEx")
							href="{{ route('nomina.report-nom035',$request->folio) }}"
						@endslot
						<span>Exportar reporte Nom035</span><span class="icon-file-excel"></span>
					@endcomponent
				@endif
				@if($request->nominasReal->first()->type_nomina == 2)
					@component("components.buttons.button", ["variant" => "success"])
						@slot("classEx")
							export
						@endslot
						@slot('buttonElement')
							a
						@endslot
						@slot("attributeEx")
							href="{{ route('nomina.construction-review-nf.export',$request->folio) }}"
						@endslot
						<span>Exportar a Excel</span><span class="icon-file-excel"></span>
					@endcomponent
				@endif
			</div>
			@php
				$body		= [];
				$modelBody	= [];
				$modelHead	= [
					[
						["value" => "Nombre del Empleado", "classEx" => "sticky inset-x-0"],
						["value" => "Tipo"],
						["value" => "Fiscal/No Fiscal/Nom35"]
					]
				];
				if($request->status != 2)
				{
					array_push($modelHead[0], ["value" => "Acciones"]);
				}
				foreach(App\NominaEmployee::join('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')->where('nomina_employees.idnomina',$request->nominasReal->first()->idnomina)->where('nomina_employees.visible',1)->orderBy('real_employees.last_name','ASC')->orderBy('real_employees.scnd_last_name','ASC')->orderBy('real_employees.name','ASC')->select('nomina_employees.*')->get() as $n)
				{
					$body = [ "classEx" => "tr_payroll",
						[
							"classEx" => "sticky inset-x-0",
							"content" =>
							[
								[
									"kind" 			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" name=\"request_idnominaEmployee[]\" value=\"".$n->idnominaEmployee."\"",
									"classEx"		=> "idnominaEmployee"
								],
								[
									"kind" 			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" name=\"idrealEmployee[]\" value=\"".$n->idrealEmployee."\"",
									"classEx"		=> "idrealEmployee"
								],
								[
									"kind" 			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" name=\"idworkingData[]\" value=\"".$n->idworkingData."\"",
									"classEx"		=> "idworkingData"
								],
								[
									"label" => $n->employee->first()->last_name.' '.$n->employee->first()->scnd_last_name.' '.$n->employee->first()->name
								]
							]
						]
					];
					$dataWorker = $n->workerData->first()->paymentWay==1 ? $n->employee->first()->bankData()->where('visible',1)->where('type',1)->exists() ? $n->employee->first()->bankData->where('visible',1)->where('type',1)->last()->id : '' : '';
					if($n->nominasEmployeeNF()->exists())
					{
						array_push($body[0]["content"],
							[
								"kind"			=> "components.inputs.input-text",
								"attributeEx"	=> "type=\"hidden\" name=\"request_idnominaemployeenf[]\" value=\"".$n->nominasEmployeeNF->first()->idnominaemployeenf."\""
							]
						);
						array_push($body[0]["content"],
							[
								"kind"			=> "components.inputs.input-text",
								"attributeEx"	=> "type=\"hidden\" name=\"request_paymentWay[]\" value=\"".$n->nominasEmployeeNF->first()->idpaymentMethod."\"",
								"classEx"		=> "paymentWay"
							]
						);
						array_push($body[0]["content"],
							[
								"kind"			=> "components.inputs.input-text",
								"attributeEx"	=> "type=\"hidden\" name=\"request_idemployeeAccount[]\" value=\"".$n->nominasEmployeeNF->first()->idemployeeAccounts."\"",
								"classEx"		=> "idemployeeAccount"
							]
						);
						array_push($body[0]["content"],
							[
								"kind"			=> "components.inputs.input-text",
								"attributeEx"	=> "type=\"hidden\" name=\"request_reference[]\" value=\"".$n->nominasEmployeeNF->first()->reference."\"",
							]
						);
						array_push($body[0]["content"],
							[
								"kind"			=> "components.inputs.input-text",
								"attributeEx"	=> "type=\"hidden\" name=\"request_discount[]\" value=\"".$n->nominasEmployeeNF->first()->discount."\"",
							]
						);
						array_push($body[0]["content"],
							[
								"kind"			=> "components.inputs.input-text",
								"attributeEx"	=> "type=\"hidden\" name=\"request_reason_discount[]\" value=\"".$n->nominasEmployeeNF->first()->reasonDiscount."\"",
							]
						);
						array_push($body[0]["content"],
							[
								"kind"			=> "components.inputs.input-text",
								"attributeEx"	=> "type=\"hidden\" name=\"request_amount[]\" value=\"".$n->nominasEmployeeNF->first()->amount."\"",
							]
						);
						array_push($body[0]["content"],
							[
								"kind"			=> "components.inputs.input-text",
								"attributeEx"	=> "type=\"hidden\" name=\"request_reason_payment[]\" value=\"".$n->nominasEmployeeNF->first()->reasonAmount."\"",
							]
						);
					}
					else
					{
						array_push($body[0]["content"], 
							[
								"kind"			=> "components.inputs.input-text",
								"attributeEx"	=> "type=\"hidden\" name=\"request_idnominaemployeenf[]\" value=\"x\""
							]
						);
						array_push($body[0]["content"], 
							[
								"kind"			=> "components.inputs.input-text",
								"attributeEx"	=> "type=\"hidden\" name=\"request_paymentWay[]\" value=\"".$n->workerData->first()->paymentWay."\"",
								"classEx"		=> "paymentWay"
							]
						);
						array_push($body[0]["content"], 
							[
								"kind"			=> "components.inputs.input-text",
								"attributeEx"	=> "type=\"hidden\" name=\"request_idemployeeAccount[]\" value=\"".$dataWorker."\"",
								"classEx"		=> "idemployeeAccount"
							]
						);
						array_push($body[0]["content"], 
							[
								"kind"			=> "components.inputs.input-text",
								"attributeEx"	=> "type=\"hidden\" name=\"request_reference[]\" value=\"\""
							]
						);
						array_push($body[0]["content"], 
							[
								"kind"			=> "components.inputs.input-text",
								"attributeEx"	=> "type=\"hidden\" name=\"request_discount[]\" value=\"\""
							]
						);
						array_push($body[0]["content"], 
							[
								"kind"			=> "components.inputs.input-text",
								"attributeEx"	=> "type=\"hidden\" name=\"request_reason_discount[]\" value=\"\""
							]
						);
						array_push($body[0]["content"], 
							[
								"kind"			=> "components.inputs.input-text",
								"attributeEx"	=> "type=\"hidden\" name=\"request_amount[]\" value=\"".$n->workerData->first()->complement."\"",
							]
						);
						array_push($body[0]["content"], 
							[
								"kind"			=> "components.inputs.input-text",
								"attributeEx"	=> "type=\"hidden\" name=\"request_reason_payment[]\" value=\"\""
							]
						);
					}
					array_push($body,[
						"content" =>
						[
							"label" => $n->category()
						]
					]);
					array_push($body,[
						"content" =>
						[
							"label" => $n->typeNomina()
						]
					]);
					if($request->status != 2)
					{
						array_push($body, [
							"content" =>
							[
								[
									"kind" 			=> "components.buttons.button",
									"variant"		=> "success",
									"classEx"		=> "btn-edit-user",
									"label"			=> "<span class=\"icon-pencil\"></span>",
									"attributeEx"	=> "title=\"Editar Datos Personales\" type=\"button\" data-toggle=\"modal\" data-target=\"#myModal\""
								],
								[
									"kind" 			=> "components.buttons.button",
									"variant"		=> "primary",
									"classEx"		=> "btn-edit-employee",
									"label"			=> "<span class=\"icon-pencil\"></span>",
									"attributeEx"	=> "title=\"Editar Datos de Pago\" type=\"button\" data-toggle=\"modal\" data-target=\"#myModal\""
								],
								[
									"kind" 			=> "components.buttons.button",
									"variant"		=> "red",
									"classEx"		=> "btn-delete-employee",
									"label"			=> "<span class=\"icon-x\"></span>",
									"attributeEx"	=> "title=\"Eliminar\" type=\"button\" data-link=\"".route('nomina.delete.employee',$n->idnominaEmployee)."\""
								]
							]
						]);
					}
					$modelBody[] = $body;
				}
			@endphp
			@component('components.tables.table', [
				"modelBody" => $modelBody,
				"modelHead" => $modelHead
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
		@endif
		@if($request->nominasReal->first()->type_nomina == 3)
			@php
				$modelTable = 
				[
					[
						"label" => "Total de solicitud:", "inputsEx" =>
						[
							[
								"kind" 		=> "components.labels.label",
								"label" 	=> "$ ".number_format($request->nominasReal->first()->amount,2),
								"classEx" 	=> "my-2 totalLabel"
							],
							[
								"kind"			=> "components.inputs.input-text",
								"attributeEx" 	=> "type=\"hidden\" readonly=\"readonly\" name=\"total_nomina\" value=\"".$request->nominasReal->first()->amount."\""
							]
						]
					]
				];
			@endphp
			@component('components.templates.outputs.form-details', ["modelTable" => $modelTable])
				@slot('classEx')
					mb-4
				@endslot
			@endcomponent
		@endif
		@if($request->idCheckConstruction != "")
			@component('components.labels.title-divisor') DATOS DE REVISIÓN @endcomponent
			@if($request->idCheckConstruction != "")
				@php
					$modelTable = [
						"Revisó en Obra" => $request->constructionReviewedUser->name.' - '.$request->constructionReviewedUser->last_name.' - '.$request->constructionReviewedUser->scnd_last_name 
					];
				@endphp
				@component('components.templates.outputs.table-detail-single', ["modelTable" => $modelTable])@endcomponent
			@endif
		@endif
		<div class="w-full mt-4 grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mb-6">
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="hidden" name="decline_status" value="6"
				@endslot
			@endcomponent
			@component('components.buttons.button', [ 
				"variant" => "secondary"
			])
				@slot('attributeEx')
					type="button" name="update" data-toggle="modal" data-target="#modalUpdate"
				@endslot
				CARGAR ARCHIVO
			@endcomponent
			@component('components.buttons.button', [ 
				"variant" => "primary"
			])
				@slot('attributeEx')
					type="submit" name="enviar" value="ENVIAR SOLICITUD"
				@endslot
				ENVIAR SOLICITUD
			@endcomponent
			@component('components.buttons.button', [ 
				"variant" => "red"
			])
				@slot('attributeEx')
					type="submit" name="decline" value="RECHAZAR SOLICITUD" formaction="{{ route('nomina.decline',["id" => $request->folio, "submodule_id" => 168]) }}"
				@endslot
				RECHAZAR SOLICITUD
			@endcomponent
		</div>
		<div id="request"></div>
	@endcomponent
	@component('components.modals.modal', ["variant" => "large"])
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
	@component('components.modals.modal', ["variant" => "large"])
		@slot('id')
			modalUpdate
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
			@if($request->taxPayment == 1)
				@switch($request->nominasReal->first()->idCatTypePayroll)
					@case('001')
						@component('components.forms.form', ["attributeEx" => "method=\"POST\" id=\"employee_massive\" action=\"".route('nomina.salary-update')."\"", "files" => true])
					@break
					@case('002')
						@component('components.forms.form', ["attributeEx" => "method=\"POST\" id=\"employee_massive\" action=\"".route('nomina.bonus-update')."\"", "files" => true])
					@break
					@case('003')
						@component('components.forms.form', ["attributeEx" => "method=\"POST\" id=\"employee_massive\" action=\"".route('nomina.settlement-update')."\"", "files" => true])
					@break
					@case('004')
						@component('components.forms.form', ["attributeEx" => "method=\"POST\" id=\"employee_massive\" action=\"".route('nomina.liquidation-update')."\"", "files" => true])
					@break
					@case('005')
						@component('components.forms.form', ["attributeEx" => "method=\"POST\" id=\"employee_massive\" action=\"".route('nomina.vacationpremium-update')."\"", "files" => true])
					@break
					@case('006')
						@component('components.forms.form', ["attributeEx" => "method=\"POST\" id=\"employee_massive\" action=\"".route('nomina.profitsharing-update')."\"", "files" => true])
					@break
				@endswitch
			@else
				@component('components.forms.form', ["attributeEx" => "method=\"POST\" id=\"employee_massive\" action=\"".route('nomina.constructionreview.complement-update-construction')."\"", "files" => true])
			@endif
			@csrf
				@php
					$buttons = 
					[
						"separator"	=> 
						[
							[
								"kind" 			=> "components.buttons.button-approval",
								"label" 		=> "coma (,)",
								"attributeEx" 	=> "value=\",\" name=\"separator\" id=\"separatorComa\""
							],
							[
								"kind" 			=> "components.buttons.button-approval",
								"label" 		=> "Punto y coma (;)",
								"attributeEx" 	=> "value=\";\" name=\"separator\" id=\"separatorPuntoComa\""
							]
						]
					];
				@endphp
				@component("components.documents.select_file_csv", ["attributeEx" => "id=\"container-data-2\"", "attributeExInput" => "data-validation=\"required\" name=\"csv_file\" id=\"csv\"", "buttons" => $buttons]) @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="hidden" name="idnomina" value="{{ $request->nominasReal->first()->idnomina }}"
					@endslot
				@endcomponent
				<div class="mt-4 w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mb-6">
					@component('components.buttons.button', ["variant" => "primary"])
						@slot('attributeEx')
							type="submit"
						@endslot
						Cargar archivo
					@endcomponent
					@component('components.buttons.button', ["variant" => "red"])
						@slot('attributeEx')
							type="button" title="Cerrar" data-dismiss="modal"
						@endslot
						@slot('classEx')
							exit
						@endslot
						@slot('label')
							<span class="icon-x"></span><span>Cerrar</span>
						@endslot
					@endcomponent
				</div>
			@endcomponent
		@endslot
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
		$('#separatorComa').prop('checked',true);
		$('.js-enterprises').select2({
			placeholder: 'Seleccione la Empresa',
			language: "es",
			maximumSelectionLength: 1
		})
		.on("change",function(e)
		{
			if($(this).val().length>1)
			{
				$(this).val($(this).val().slice(0,1)).trigger('change');
			}
		});
		$(function() 
		{
			$(".datepicker2").datepicker({ minDate: 0, dateFormat: "yy-mm-dd" });
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
		.on('click','#help-btn-add-employee',function()
		{
			swal('Ayuda','El botón verde es para editar los datos del empleado. El botón rojo es para editar los datos del pago del empleado.','info');
		})
		.on('click','#help-btn-edit-employee',function()
		{
			swal('Ayuda','Al habilitar la edición los cambios realizados en "Información Laboral" serán guardados. Al estar deshabilitada la edición los cambios realizados en "Información Laboral" no serán guardados','info');
		})
		.on('click','input[name="method_request"]',function()
		{
			if($(this).val() == 1)
			{
				$('.resultbank').stop(true,true).slideDown().show();
			}
			else
			{
				$('.resultbank').stop(true,true).slideUp().hide();
			}
		})
		.on('click','input[name="salary_idpaymentMethod"]',function()
		{
			if($(this).val() == 1)
			{
				$('.resultbank').stop(true,true).slideDown().show();
			}
			else
			{
				$('.resultbank').stop(true,true).slideUp().hide();
			}
		})
		.on('click','input[name="bonus_idpaymentMethod"]',function()
		{
			if($(this).val() == 1)
			{
				$('.resultbank').stop(true,true).slideDown().show();
			}
			else
			{
				$('.resultbank').stop(true,true).slideUp().hide();
			}
		})
		.on('click','input[name="settlement_idpaymentMethod"]',function()
		{
			if($(this).val() == 1)
			{
				$('.resultbank').stop(true,true).slideDown().show();
			}
			else
			{
				$('.resultbank').stop(true,true).slideUp().hide();
			}
		})
		.on('click','input[name="liquidation_idpaymentMethod"]',function()
		{
			if($(this).val() == 1)
			{
				$('.resultbank').stop(true,true).slideDown().show();
			}
			else
			{
				$('.resultbank').stop(true,true).slideUp().hide();
			}
		})
		.on('click','input[name="vacationpremium_idpaymentMethod"]',function()
		{
			if($(this).val() == 1)
			{
				$('.resultbank').stop(true,true).slideDown().show();
			}
			else
			{
				$('.resultbank').stop(true,true).slideUp().hide();
			}
		})
		.on('click','input[name="profitsharing_idpaymentMethod"]',function()
		{
			if($(this).val() == 1)
			{
				$('.resultbank').stop(true,true).slideDown().show();
			}
			else
			{
				$('.resultbank').stop(true,true).slideUp().hide();
			}
		})
		.on('click','.btn-edit-user',function()
		{
			swal(
			{
				icon	: '{{ asset(getenv('LOADING_IMG')) }}',
				button	: false,
			});
			id 	= $(this).parents('.tr_payroll').find('.idnominaEmployee').val();
			folio = {{ $request->folio }};
			$.ajax(
			{
				type 	: 'post',
				url 	: '{{ route("nomina.nomina-create.getdetailemployeenomina") }}',
				data 	: {'id':id,'folio':folio},
				success : function(data)
				{
					$('#myModal .modal-body').show().html(data);
					generalSelect({'selector': '.js-projects',	'model': 14});
					generalSelect({'selector': '#cp',			'model': 2});
					generalSelect({'selector':'.bank',			'model': 28});
					generalSelect({'selector': '.js-wbs', 'depends': '.js-projects','model': 1,'maxSelection': -1});
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
					validationEditEmployee();
				},
				error : function()
				{
					swal('','Sucedió un error, por favor intente de nuevo.','error');
					$('#myModal').modal('hide');
				}
			});
		})
		.on('click','.btn-edit-employee',function()
		{
			swal(
			{
				icon	: '{{ asset(getenv('LOADING_IMG')) }}',
				button	: false,
			});
			id					= $(this).parents('.tr_payroll').find('.idrealEmployee').val();
			folio				= {{ $request->folio }};
			idnominaEmployee	= $(this).parents('.tr_payroll').find('.idnominaEmployee').val();
			$.ajax(
			{
				type 	: 'post',
				url 	: '{{ route("nomina.nomina-create.datanf") }}',
				data 	: {'id':id,'folio':folio,'idnominaEmployee':idnominaEmployee},
				success : function(data)
				{
					$('#myModal').show().html(data);
					$('.employee_amount, .employee_discount, .employee_extra').numeric({ negative : false });
					$('[name="employee_extra_time"]').numeric({ altDecimal: ".", decimalPlaces: 2,negative: false });
					$('[name="employee_holiday"]').numeric({ altDecimal: ".", decimalPlaces: 2,negative: false });
					$('[name="employee_sundays"]').numeric({ altDecimal: ".", decimalPlaces: 2,negative: false });
					$('[name="employee_complement"]').numeric({ altDecimal: ".", decimalPlaces: 2,negative: false });
					$('[name="employee_amount"]').numeric({ altDecimal: ".", decimalPlaces: 2,negative: false });
					swal.close();
					validation();
				},
				error : function()
				{
					swal('','Sucedió un error, por favor intente de nuevo.','error');
					$('#myModal').modal('hide');
				}
			})
		})
		.on('click','.exit',function()
		{
			$('#myModal').modal('hide');
			$('#modalUpdate').modal('hide');
		})
		.on('click','.checkbox',function()
		{
			$('.marktr').removeClass('marktr');
			$(this).parents('tr').addClass('marktr');
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
		.on('input','.alias_alimony',function()
		{
			if($(this).val() != "")
			{
				$('.alias_alimony').addClass('valid').removeClass('error');
			}
			else
			{
				$('.alias_alimony').addClass('error').removeClass('valid');
			}
		})
		.on('input','.beneficiary',function()
		{
			if($(this).val() != "")
			{
				$('.beneficiary').addClass('valid').removeClass('error');
			}
			else
			{
				$('.beneficiary').addClass('error').removeClass('valid');
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
						$modelHead	= ["Alias", "Banco", "CLABE", "Cuenta", "Tarjeta", "Sucursal", "Acción"];
						$body = [ "classEx" => "tr_body",
							[
								"content" => 
								[
									[
										"kind"		=> "components.labels.label",
										"classEx"	=> "classAlias"
									],
									[
										"kind"			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"hidden\" name=\"alias[]\"",
										"classEx"		=> "aliasClass"
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
										"classEx"	=> "classBank"
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
										"classEx"		=> "bankClass"
									]
								]
							],
							[
								"content" =>
								[
									[
										"kind"		=> "components.labels.label",
										"classEx"	=> "classClabe"
									],
									[
										"kind"			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"hidden\" name=\"clabe[]\"",
										"classEx"		=> "clabeClass"
									]
								] 
							],
							[
								"content" =>
								[
									[
										"kind"		=> "components.labels.label",
										"classEx"	=> "classAccount"
									],
									[
										"kind"			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"hidden\" name=\"account[]\"",
										"classEx"		=> "accountClass"
									]
								] 
							],
							[
								"content" =>
								[
									[
										"kind"		=> "components.labels.label",
										"classEx"	=> "classCard"
									],
									[
										"kind"			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"hidden\" name=\"card[]\"",
										"classEx"		=> "cardClass"
									]
								] 
							],
							[
								"content" =>
								[
									[
										"kind"		=> "components.labels.label",
										"classEx"	=> "classBranch"
									],
									[
										"kind"			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"hidden\" name=\"branch[]\"",
										"classEx"		=> "branchClass"
									]
								] 
							],
							[
								"content" => 
								[
									[
										"kind"			=> "components.buttons.button",
										"variant"		=> "red",
										"attributeEx" 	=> "type=\"button\"",
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
					row.find('.classAlias').text(alias =='' ? ' -- ' :alias);
					row.find('.aliasClass').val(alias);
					row.find('.classBank').text(bankName);
					row.find('.idbank').val('x');
					row.find('.idEmployee').val('x');
					row.find('.bankClass').val(bankid);
					row.find('.classClabe').text(clabe =='' ? ' -- ' :clabe);
					row.find('.clabeClass').val(clabe);
					row.find('.classAccount').text(account =='' ? ' -- ' :account);
					row.find('.accountClass').val(account);
					row.find('.classCard').text(card =='' ? ' -- ' :card);
					row.find('.cardClass').val(card);
					row.find('.classBranch').text(branch =='' ? ' -- ' :branch);
					row.find('.branchClass').val(branch);
					$('.body_content').append(row);
					$('.card, .clabe, .bank, .account, .alias, .branch_office').removeClass('error').removeClass('valid').val('');
					$('.bank').val(null).trigger("change");
				}
			}
			else
			{
				swal('', 'Seleccione un banco, por favor', 'error');
				$('.bank').addClass('error');
			}
		})
		.on('click','#add-bank-alimony',function()
		{
			beneficiary	= $(this).parents('.tr_bank_alimony').find('.beneficiary').val();
			alias		= $(this).parents('.tr_bank_alimony').find('.alias_alimony').val();
			bankid		= $(this).parents('.tr_bank_alimony').find('.bank').val();
			bankName	= $(this).parents('.tr_bank_alimony').find('.bank :selected').text();
			clabe		= $(this).parents('.tr_bank_alimony').find('.clabe_alimony').val();
			account		= $(this).parents('.tr_bank_alimony').find('.account_alimony').val();
			card		= $(this).parents('.tr_bank_alimony').find('.card_alimony').val();
			branch		= $(this).parents('.tr_bank_alimony').find('.branch_office').val();
			$('.card_alimony, .clabe_alimony, .account_alimony').removeClass('valid').removeClass('error');

			if(alias == "" || beneficiary == "")
			{
				if(alias == "")
				{
					$(this).parents('.tr_bank_alimony').find('.alias_alimony').addClass('error');
				}
				if(beneficiary == "")
				{
					$(this).parents('.tr_bank_alimony').find('.beneficiary').addClass('error');
				}
				swal('', 'Por favor ingrese un beneficiario y un alias', 'error');	
			}
			else if(bankid.length>0)
			{
				if (card == "" && clabe == "" && account == "")
				{
					$('.card_alimony, .clabe_alimony, .account_alimony').removeClass('valid').addClass('error');
					swal('', 'Debe ingresar al menos un número de tarjeta, clabe o cuenta bancaria', 'error');
				}
				else if($(this).parents('.tr_bank_alimony').find('.card_alimony').hasClass('error') || $(this).parents('.tr_bank_alimony').find('.clabe_alimony').hasClass('error') || $(this).parents('.tr_bank_alimony').find('.account_alimony').hasClass('error'))
				{
					swal('', 'Por favor ingrese datos correctos', 'error');
				}
				else
				{
					@php
						$body		= [];
						$modelBody	= [];
						$modelHead	= ["Beneficiario", "Alias", "Banco", "CLABE", "Cuenta", "Tarjeta", "Sucursal", "Acción"];

						$body = [ "classEx" => "tr_body",
							[
								"content" => 
								[
									[
										"kind"		=> "components.labels.label",
										"classEx"	=> "classBeneficiary"
									],
									[
										"kind"			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"hidden\" name=\"beneficiary[]\"",
										"classEx"		=> "beneficiaryClass"
									],
									[
										"kind"			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"hidden\" name=\"type_account[]\" value=\"2\"",
									]
								]
							],
							[
								"content" => 
								[
									[
										"kind"		=> "components.labels.label",
										"classEx"	=> "classAliasA"
									],
									[
										"kind"			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"hidden\" name=\"alias[]\"",
										"classEx"		=> "aliasClassA"
									],
								]
							],
							[
								"content" => 
								[
									[
										"kind"		=> "components.labels.label",
										"classEx"	=> "classBankA"
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
										"classEx"		=> "bankClassA"
									]
								]
							],
							[
								"content" =>
								[
									[
										"kind"		=> "components.labels.label",
										"classEx"	=> "classClabeA"
									],
									[
										"kind"			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"hidden\" name=\"clabe[]\"",
										"classEx"		=> "clabeClassA"
									]
								] 
							],
							[
								"content" =>
								[
									[
										"kind"		=> "components.labels.label",
										"classEx"	=> "classAccountA"
									],
									[
										"kind"			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"hidden\" name=\"account[]\"",
										"classEx"		=> "accountClassA"
									]
								] 
							],
							[
								"content" =>
								[
									[
										"kind"		=> "components.labels.label",
										"classEx"	=> "classCardA"
									],
									[
										"kind"			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"hidden\" name=\"card[]\"",
										"classEx"		=> "cardClassA"
									]
								] 
							],
							[
								"content" =>
								[
									[
										"kind"		=> "components.labels.label",
										"classEx"	=> "classBranchA"
									],
									[
										"kind"			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"hidden\" name=\"branch[]\"",
										"classEx"		=> "branchClassA"
									]
								] 
							],
							[
								"content" => 
								[
									[
										"kind"			=> "components.buttons.button",
										"variant"		=> "red",
										"attributeEx" 	=> "type=\"button\"",
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
					row.find('.classBeneficiary').text(beneficiary =='' ? ' -- ' :beneficiary);
					row.find('.beneficiaryClass').val(beneficiary);
					row.find('.classAliasA').text(alias =='' ? ' -- ' :alias);
					row.find('.aliasClassA').val(alias);
					row.find('.classBankA').text(bankName);
					row.find('.idbank').val('x');
					row.find('.idEmployee').val('x');
					row.find('.bankClassA').val(bankid);
					row.find('.classClabeA').text(clabe =='' ? ' -- ' :clabe);
					row.find('.clabeClassA').val(clabe);
					row.find('.classAccountA').text(account =='' ? ' -- ' :account);
					row.find('.accountClassA').val(account);
					row.find('.classCardA').text(card =='' ? ' -- ' :card);
					row.find('.cardClassA').val(card);
					row.find('.classBranchA').text(branch =='' ? ' -- ' :branch);
					row.find('.branchClassA').val(branch);
					$('.body_content_alimony').append(row);
					$('.card_alimony, .bank, .clabe_alimony, .account_alimony, .alias_alimony, .beneficiary, .branch_office').removeClass('error').removeClass('valid').val('');
					$('.bank').val(null).trigger("change");
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
			idbank = $(this).parents('.tr_body').find('.idbank').val();
			del = $('<input type="hidden" value="'+idbank+'" name="deleteBank[]">');
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
						$('.hide-td').show();
					}
					else
					{
						$('.showing').attr('disabled',true).addClass('disabled');
						$('.view-button').hide();
						$('.hide-td').hide();
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
						$('.showing').attr('disabled',true).addClass('disabled').addClass('showing');
						$('.view-button').hide();
						$('.hide-td').hide();
					}
					else
					{
						$('.disabled').removeAttr('disabled');
						$('.view-button').show();
						$('.hide-td').show();
						$(this).prop('checked',true);
					}
				});
			}
		})
		.on('click','.btn-change-type',function()
		{
			swal(
			{
				icon	: '{{ asset(getenv('LOADING_IMG')) }}',
				button	: false,
			});
			idnominaEmployee = $(this).parent('td').parent('tr').find('.idnominaEmployee').val();
			$.ajax(
			{
				type 	: 'route',
				url 	: '{{ route('nomina.nomina-create.changetype') }}',
				data 	: {'idnominaEmployee':idnominaEmployee},
				success : function(data)
				{
					$('#myModal').show().html(data);
					swal.close();
				},
				error : function()
				{
					swal('','Sucedió un error, por favor intente de nuevo.','error');
					$('#myModal').modal('hide');
				}
			});
		})
		.on('change','input[name="employee_discount"]',function()
		{
			discount 	= $(this).val();
			amount 		= $('input[name="employee_amount"]').val();
			total 		= amount - discount;
			$('input[name="employee_amount"]').val(total);
		})
		.on('click','.btn-edit-data-nomina',function()
		{
			swal(
			{
				icon	: '{{ asset(getenv('LOADING_IMG')) }}',
				button	: false,
			});
			id					= $(this).parents('.tr_payroll').find('.idrealEmployee').val();
			folio				= {{ $request->folio }};
			idnominaEmployee	= $(this).parents('.tr_payroll').find('.idnominaEmployee').val();
			type_payroll		= $(this).parents('.tr_payroll').find('.type_payroll').val();
			$.ajax(
			{
				type 	: 'post',
				url 	: '{{ route("nomina.data-nomina") }}',
				data 	: {'id':id,'folio':folio,'idnominaEmployee':idnominaEmployee,'type_payroll':type_payroll},
				success : function(data)
				{
					$('#myModal .modal-body').show().html(data);
					swal.close();
					validation();
					$('.datepicker').datepicker({ dateFormat: "dd-mm-yy" });
				},
				error : function()
				{
					swal('','Sucedió un error, por favor intente de nuevo.','error');
					$('#myModal').modal('hide');
				}
			});
		})
		.on('click','.btn-edit-calculate',function()
		{
			swal(
			{
				icon	: '{{ asset(getenv('LOADING_IMG')) }}',
				button	: false,
			});
			folio				= {{ $request->folio }};
			idnominaEmployee	= $(this).parents('.tr_payroll').find('.idnominaEmployee').val();
			idtypepayroll		= $(this).parents('.tr_payroll').find('.type_payroll').val();
			$.ajax(
			{
				type 	: 'post',
				url 	: '{{ route("nomina.nomina-create.data") }}',
				data 	: {
							'folio'				:folio,
							'idnominaEmployee'	:idnominaEmployee,
							'idtypepayroll' 	:idtypepayroll
						},
				success : function(data)
				{
					$('#myModal .modal-body').show().html(data);
					swal.close();
					validation();
					$('.datepicker').datepicker({ dateFormat: "dd-mm-yy" });
				},
				error : function()
				{
					swal('','Sucedió un error, por favor intente de nuevo.','error');
					$('#myModal').modal('hide');
				}
			})
		})
		.on('click','button[name="update"]',function()
		{
			$('#modalUpdate .modal-body').show();
		})
		.on('submit','#employee_massive',function()
		{
			swal({
				icon: '{{ asset(getenv('LOADING_IMG')) }}',
				button: false,
			});
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
		.on('click','.btn-delete-employee',function(e)
		{
			if ($('#body-payroll .tr_payroll').length == 1) 
			{
				swal('','La solicitud debe tener al menos un empleado','error');
			}
			else
			{
				e.preventDefault();
				url = $(this).attr('data-link');
				swal({
					title		: "Confirmar",
					text		: "¿Desea eliminar el empleado?",
					icon		: "warning",
					buttons		: ["Cancelar","Eliminar"],
					dangerMode	: true,
				})
				.then((isTrue) => 
				{
					if(isTrue)
					{
						swal({
							icon				: '{{ asset(getenv('LOADING_IMG')) }}',
							button				: false,
							closeOnClickOutside	: false,
							closeOnEsc			: false
						});
						form = $('<form action="'+url+'" method="POST"></form>')
							.append($('@csrf'))
							.append($('@method("PUT")'));
						$(document.body).append(form);
						form.submit();
					}
				});
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
		@switch($request->nominasReal->first()->idCatTypePayroll)
			@case('001')
				.on('change','input[name="salary_salary"],input[name="salary_loan_perception"],input[name="salary_puntuality"],input[name="salary_assistance"],input[name="salary_subsidy"],input[name="salary_extra_hours_taxed"],input[name="salary_extra_hours"],input[name="salary_holiday_taxed"],input[name="salary_holiday"],input[name="salary_except_sundays"],input[name="salary_taxed_sundays"]',function()
				{
					salary_extra_hours_taxed	= $('input[name="salary_extra_hours_taxed"]').val();
					salary_extra_hours			= $('input[name="salary_extra_hours"]').val();
					salary_holiday_taxed		= $('input[name="salary_holiday_taxed"]').val();
					salary_holiday				= $('input[name="salary_holiday"]').val();
					salary_except_sundays		= $('input[name="salary_except_sundays"]').val();
					salary_taxed_sundays		= $('input[name="salary_taxed_sundays"]').val();

					salary_salary			= $('input[name="salary_salary"]').val();
					salary_loan_perception	= $('input[name="salary_loan_perception"]').val();
					salary_puntuality		= $('input[name="salary_puntuality"]').val();
					salary_assistance		= $('input[name="salary_assistance"]').val();
					salary_subsidy			= $('input[name="salary_subsidy"]').val();

					total = Number(salary_salary)+Number(salary_loan_perception)+Number(salary_puntuality)+Number(salary_assistance)+Number(salary_subsidy)+Number(salary_extra_hours_taxed)+Number(salary_extra_hours)+Number(salary_holiday_taxed)+Number(salary_holiday)+Number(salary_except_sundays)+Number(salary_taxed_sundays);
					$('input[name="salary_totalPerceptions"]').val(total);

					salary_totalPerceptions	= $('input[name="salary_totalPerceptions"]').val();
					salary_totalRetentions	= $('input[name="salary_totalRetentions"]').val();

					netincome = Number(salary_totalPerceptions) - Number(salary_totalRetentions);
					$('input[name="salary_netIncome"]').val(Number(netincome).toFixed(2));
				})
				.on('change','input[name="salary_imss"],input[name="salary_infonavit"],input[name="salary_fonacot"],input[name="salary_loan_retention"],input[name="salary_isrRetentions"],input[name="salary_other_retention_amount"],input[name="salary_alimony"]',function()
				{
					salary_imss            = $('input[name="salary_imss"]').val();
					salary_infonavit       = $('input[name="salary_infonavit"]').val();
					salary_fonacot         = $('input[name="salary_fonacot"]').val();
					salary_loan_retention  = $('input[name="salary_loan_retention"]').val();
					salary_isrRetentions   = $('input[name="salary_isrRetentions"]').val();
					salary_other_retention = $('input[name="salary_other_retention_amount"]').val();
					salary_alimony         = $('input[name="salary_alimony"]').val();
					total = Number(salary_imss)+Number(salary_infonavit)+Number(salary_fonacot)+Number(salary_loan_retention)+Number(salary_isrRetentions)+Number(salary_other_retention)+Number(salary_alimony);
					$('input[name="salary_totalRetentions"]').val(total);
					salary_totalPerceptions	= $('input[name="salary_totalPerceptions"]').val();
					salary_totalRetentions	= $('input[name="salary_totalRetentions"]').val();
					netincome = Number(salary_totalPerceptions) - Number(salary_totalRetentions);
					$('input[name="salary_netIncome"]').val(Number(netincome).toFixed(2));
				})
			@break
			@case('002')
				.on('change','input[name="bonus_exemptBonus"],input[name="bonus_taxableBonus"]',function()
				{
					bonus_exemptBonus	= $('input[name="bonus_exemptBonus"]').val();
					bonus_taxableBonus	= $('input[name="bonus_taxableBonus"]').val();

					total = Number(bonus_exemptBonus)+Number(bonus_taxableBonus);
					$('input[name="bonus_totalPerceptions"]').val(total);

					bonus_totalPerceptions	= $('input[name="bonus_totalPerceptions"]').val();
					bonus_totalTaxes		= $('input[name="bonus_totalTaxes"]').val();

					netincome = Number(bonus_totalPerceptions) - Number(bonus_totalTaxes);
					$('input[name="bonus_netIncome"]').val(Number(netincome).toFixed(2));
				})
				.on('change','input[name="bonus_isr"]',function()
				{
					bonus_isr = $('input[name="bonus_isr"]').val();
					$('input[name="bonus_totalTaxes"]').val(bonus_isr);

					bonus_totalPerceptions	= $('input[name="bonus_totalPerceptions"]').val();
					bonus_totalTaxes		= $('input[name="bonus_totalTaxes"]').val();

					netincome = Number(bonus_totalPerceptions) - Number(bonus_totalTaxes);
					$('input[name="bonus_netIncome"]').val(Number(netincome).toFixed(2));
				})
			@break
			@case('003')
				.on('change','input[name="liquidation_seniorityPremium"],input[name="liquidation_holidays"],input[name="liquidation_exemptCompensation"],input[name="liquidation_taxedCompensation"],input[name="liquidation_exemptBonus"],input[name="liquidation_taxableBonus"],input[name="liquidation_holidayPremiumExempt"],input[name="liquidation_holidayPremiumTaxed"],input[name="liquidation_otherPerception"]',function()
				{
					liquidation_seniorityPremium			= $('input[name="liquidation_seniorityPremium"]').val();
					liquidation_holidays					= $('input[name="liquidation_holidays"]').val();
					liquidation_exemptCompensation			= $('input[name="liquidation_exemptCompensation"]').val();
					liquidation_taxedCompensation			= $('input[name="liquidation_taxedCompensation"]').val();
					liquidation_exemptBonus					= $('input[name="liquidation_exemptBonus"]').val();
					liquidation_taxableBonus				= $('input[name="liquidation_taxableBonus"]').val();
					liquidation_holidayPremiumExempt		= $('input[name="liquidation_holidayPremiumExempt"]').val();
					liquidation_holidayPremiumTaxed			= $('input[name="liquidation_holidayPremiumTaxed"]').val();
					liquidation_otherPerception				= $('input[name="liquidation_otherPerception"]').val();

					total = Number(liquidation_seniorityPremium)+Number(liquidation_holidays)+Number(liquidation_exemptCompensation)+Number(liquidation_taxedCompensation)+Number(liquidation_exemptBonus)+Number(liquidation_taxableBonus)+Number(liquidation_holidayPremiumExempt)+Number(liquidation_holidayPremiumTaxed)+Number(liquidation_otherPerception);
					$('input[name="liquidation_totalPerceptions"]').val(total);

					liquidation_totalPerceptions	= $('input[name="liquidation_totalPerceptions"]').val();
					liquidation_totalRetentions		= $('input[name="liquidation_totalRetentions"]').val();

					netincome = Number(liquidation_totalPerceptions)-Number(liquidation_totalRetentions);
					$('input[name="liquidation_netIncome"]').val(Number(netincome).toFixed(2));
				})
				.on('change','input[name="liquidation_isr"],input[name="liquidation_alimony"],input[name="liquidation_otherRetention"]',function()
				{
					liquidation_isr				= $('input[name="liquidation_isr"]').val();
					liquidation_alimony			= $('input[name="liquidation_alimony"]').val();
					liquidation_otherRetention	= $('input[name="liquidation_otherRetention"]').val();
					retentionsTotal				= Number(liquidation_isr)+Number(liquidation_alimony)+Number(liquidation_otherRetention);

					$('input[name="liquidation_totalRetentions"]').val(Number(retentionsTotal).toFixed(6));

					liquidation_totalPerceptions	= $('input[name="liquidation_totalPerceptions"]').val();
					liquidation_totalRetentions		= $('input[name="liquidation_totalRetentions"]').val();

					netincome = Number(liquidation_totalPerceptions) - Number(liquidation_totalRetentions);
					$('input[name="liquidation_netIncome"]').val(Number(netincome).toFixed(2));
				})
			@break
			@case('004')
				.on('change','input[name="liquidation_liquidationSalary"],input[name="liquidation_twentyDaysPerYearOfServices"],input[name="liquidation_seniorityPremium"],input[name="liquidation_holidays"],input[name="liquidation_exemptCompensation"],input[name="liquidation_taxedCompensation"],input[name="liquidation_exemptBonus"],input[name="liquidation_taxableBonus"],input[name="liquidation_holidayPremiumExempt"],input[name="liquidation_holidayPremiumTaxed"],input[name="liquidation_otherPerception"]',function()
				{
					liquidation_liquidationSalary			= $('input[name="liquidation_liquidationSalary"]').val();
					liquidation_twentyDaysPerYearOfServices	= $('input[name="liquidation_twentyDaysPerYearOfServices"]').val();
					liquidation_seniorityPremium			= $('input[name="liquidation_seniorityPremium"]').val();
					liquidation_holidays					= $('input[name="liquidation_holidays"]').val();
					liquidation_exemptCompensation			= $('input[name="liquidation_exemptCompensation"]').val();
					liquidation_taxedCompensation			= $('input[name="liquidation_taxedCompensation"]').val();
					liquidation_exemptBonus					= $('input[name="liquidation_exemptBonus"]').val();
					liquidation_taxableBonus				= $('input[name="liquidation_taxableBonus"]').val();
					liquidation_holidayPremiumExempt		= $('input[name="liquidation_holidayPremiumExempt"]').val();
					liquidation_holidayPremiumTaxed			= $('input[name="liquidation_holidayPremiumTaxed"]').val();
					liquidation_otherPerception				= $('input[name="liquidation_otherPerception"]').val();

					total = Number(liquidation_liquidationSalary)+Number(liquidation_twentyDaysPerYearOfServices)+Number(liquidation_seniorityPremium)+Number(liquidation_holidays)+Number(liquidation_exemptCompensation)+Number(liquidation_taxedCompensation)+Number(liquidation_exemptBonus)+Number(liquidation_taxableBonus)+Number(liquidation_holidayPremiumExempt)+Number(liquidation_holidayPremiumTaxed)+Number(liquidation_otherPerception);
					$('input[name="liquidation_totalPerceptions"]').val(total);

					liquidation_totalPerceptions	= $('input[name="liquidation_totalPerceptions"]').val();
					liquidation_totalRetentions		= $('input[name="liquidation_totalRetentions"]').val();

					netincome = Number(liquidation_totalPerceptions)-Number(liquidation_totalRetentions);
					$('input[name="liquidation_netIncome"]').val(Number(netincome).toFixed(2));
				})
				.on('change','input[name="liquidation_isr"],input[name="liquidation_alimony"],input[name="liquidation_otherRetention"]',function()
				{
					liquidation_isr				= $('input[name="liquidation_isr"]').val();
					liquidation_alimony			= $('input[name="liquidation_alimony"]').val();
					liquidation_otherRetention	= $('input[name="liquidation_otherRetention"]').val();
					retentionsTotal				= Number(liquidation_isr)+Number(liquidation_alimony)+Number(liquidation_otherRetention);

					$('input[name="liquidation_totalRetentions"]').val(Number(retentionsTotal).toFixed(6));

					liquidation_totalPerceptions	= $('input[name="liquidation_totalPerceptions"]').val();
					liquidation_totalRetentions		= $('input[name="liquidation_totalRetentions"]').val();

					netincome = Number(liquidation_totalPerceptions) - Number(liquidation_totalRetentions);
					$('input[name="liquidation_netIncome"]').val(Number(netincome).toFixed(2));
				})
			@break
			@case('005')
				.on('change','input[name="vacationpremium_exemptHolidayPremium"],input[name="vacationpremium_holidayPremiumTaxed"]',function()
				{
					vacationpremium_exemptHolidayPremium	= $('input[name="vacationpremium_exemptHolidayPremium"]').val();
					vacationpremium_holidayPremiumTaxed		= $('input[name="vacationpremium_holidayPremiumTaxed"]').val();

					total = Number(vacationpremium_exemptHolidayPremium)+Number(vacationpremium_holidayPremiumTaxed);
					$('input[name="vacationpremium_totalPerceptions"]').val(total);

					vacationpremium_totalPerceptions		= $('input[name="vacationpremium_totalPerceptions"]').val();
					vacationpremium_totalTaxes				= $('input[name="vacationpremium_totalTaxes"]').val();

					netincome = Number(vacationpremium_totalPerceptions)-Number(vacationpremium_totalTaxes);
					$('input[name="vacationpremium_netIncome"]').val(Number(netincome).toFixed(2));
				})
				.on('change','input[name="vacationpremium_isr"]',function()
				{
					vacationpremium_isr = $('input[name="vacationpremium_isr"]').val();
					$('input[name="vacationpremium_totalTaxes"]').val(vacationpremium_isr);

					vacationpremium_totalPerceptions		= $('input[name="vacationpremium_totalPerceptions"]').val();
					vacationpremium_totalTaxes				= $('input[name="vacationpremium_totalTaxes"]').val();

					netincome = Number(vacationpremium_totalPerceptions)-Number(vacationpremium_totalTaxes);
					$('input[name="vacationpremium_netIncome"]').val(Number(netincome).toFixed(2));
				})
			@break
			@case('006')
				.on('change','input[name="profitsharing_exemptPtu"],input[name="profitsharing_taxedPtu"]',function()
				{
					profitsharing_exemptPtu	= $('input[name="profitsharing_exemptPtu"]').val();
					profitsharing_taxedPtu	= $('input[name="profitsharing_taxedPtu"]').val();

					total = Number(profitsharing_exemptPtu) + Number(profitsharing_taxedPtu);

					$('input[name="profitsharing_totalPerceptions"]').val(total)

					profitsharing_totalPerceptions	= $('input[name="profitsharing_totalPerceptions"]').val();
					profitsharing_totalRetentions	= $('input[name="profitsharing_totalRetentions"]').val();

					netincome = Number(profitsharing_totalPerceptions)-Number(profitsharing_totalRetentions);

					$('input[name="profitsharing_netIncome"]').val(netincome);
				})
				.on('change','input[name="profitsharing_isrRetentions"]',function()
				{
					profitsharing_isrRetentions = $('input[name="profitsharing_isrRetentions"]').val();
					$('input[name="profitsharing_totalRetentions"]').val(profitsharing_isrRetentions);

					profitsharing_totalPerceptions	= $('input[name="profitsharing_totalPerceptions"]').val();
					profitsharing_totalRetentions	= $('input[name="profitsharing_totalRetentions"]').val();

					netincome = Number(profitsharing_totalPerceptions)-Number(profitsharing_totalRetentions);

					$('input[name="profitsharing_netIncome"]').val(netincome);
				})
			@break
		@endswitch
		@if($request->taxPayment == 0)
			.on('keyup','input[name="employee_complement"]',function()
			{
				$('input[name="employee_amount"]').val($(this).val());
			})
			.on('click','#add_discount',function()
			{
				employee_discount			= $('.employee_discount').val();
				employee_reason_discount	= $('.employee_reason_discount').val();

				if (employee_discount == '' || employee_reason_discount == '') 
				{
					swal('Error','Debe llenar los dos campos requeridos','error');
				}
				else
				{
					@php
						$body		= [];
						$modelBody	= [];
						$modelHead	= ["Monto", "Motivo", "Acción"];

						$body = [ "classEx"	=> "tr_dis",
							[
								"content" =>
								[
									[
										"kind"		=> "components.labels.label",
										"classEx"	=> "classEmployeeDis"
									],
									[
										"kind"			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"hidden\" name=\"t_id_discount[]\" value=\"x\"",
										"classEx"		=> "t_id_discount"
									],
									[
										"kind"			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"hidden\" name=\"t_employee_discount[]\"",
										"classEx"		=> "t_employee_discount"
									]
								]
							],
							[
								"content" =>
								[
									[
										"kind"		=> "components.labels.label",
										"classEx"	=> "classReasonDis"
									],
									[
										"kind"			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"hidden\" name=\"t_employee_reason_discount[]\"",
										"classEx"		=> "reasonDisClass"
									],
								]
							],
							[
								"content" =>
								[
									[
										"kind"			=> "components.buttons.button",
										"variant"		=> "red",
										"attributeEx"	=> "type=\"button\"",
										"classEx"		=> "delete-discount",
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
					tr	= '{!!preg_replace("/(\r)*(\n)*/", "", $table)!!}';
					row = $(tr);
					row.find('.classEmployeeDis').text(employee_discount);
					row.find('.t_employee_discount').val(employee_discount);
					row.find('.classReasonDis').text(employee_reason_discount);
					row.find('.reasonDisClass').val(employee_reason_discount);
					$('.body_content_dis').append(row);

					total_discount	= Number($('.employee_discount').val());
					total_temp		= Number($('input[name="employee_amount"]').val());
					total_amount	= Number(total_temp - total_discount).toFixed(2);

					$('input[name="employee_amount"]').val(total_amount);
					$('.employee_discount,.employee_reason_discount').val('');
					swal('','Registrado exitosamente','success');
				}
			})
			.on('click','#add_extra',function()
			{
				employee_extra			= $('.employee_extra').val();
				employee_reason_extra	= $('.employee_reason_extra').val();

				if (employee_extra == '' || employee_reason_extra == '') 
				{
					swal('Error','Debe llenar los dos campos requeridos','error');
				}
				else
				{
					@php
						$body		= [];
						$modelBody	= [];
						$modelHead	= ["Monto", "Motivo", "Acción"];

						$body = [ "classEx"	=> "tr_ex",
							[
								"content" =>
								[
									[
										"kind"		=> "components.labels.label",
										"classEx"	=> "classEmployeeEx"
									],
									[
										"kind"			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"hidden\" name=\"t_id_extra[]\" value=\"x\"",
										"classEx"		=> "t_id_extra"
									],
									[
										"kind"			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"hidden\" name=\"t_employee_extra[]\"",
										"classEx"		=> "t_employee_extra"
									]
								]
							],
							[
								"content" =>
								[
									[
										"kind"		=> "components.labels.label",
										"classEx"	=> "classReason"
									],
									[
										"kind"			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"hidden\" name=\"t_employee_reason_extra[]\"",
										"classEx"		=> "reasonClass"
									],
								]
							],
							[
								"content" =>
								[
									[
										"kind"			=> "components.buttons.button",
										"variant"		=> "red",
										"attributeEx"	=> "type=\"button\"",
										"classEx"		=> "delete-extra",
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
					tr	= '{!!preg_replace("/(\r)*(\n)*/", "", $table)!!}';
					row = $(tr);
					row.find('.classEmployeeEx').text(employee_extra);
					row.find('.t_employee_extra').val(employee_extra);
					row.find('.classReason').text(employee_reason_extra);
					row.find('.reasonClass').val(employee_reason_extra);
					$('.body_content_ex').append(row);

					total_extra		= Number($('.employee_extra').val());
					total_temp		= Number($('input[name="employee_amount"]').val());					
					total_amount	= Number(total_temp + total_extra).toFixed(2);

					$('input[name="employee_amount"]').val(total_amount);
					$('.employee_extra,.employee_reason_extra').val('');
					swal('','Registrado exitosamente','success');
				}
			})
			.on('click','.delete-extra',function()
			{
				id_extra		= $(this).parents('.tr_ex').find('.t_id_extra').val();
				total_temp		= Number($('input[name="employee_amount"]').val());
				total_extra		= Number($(this).parents('.tr_ex').find('.t_employee_extra').val());
				total_amount 	= Number(total_temp - total_extra).toFixed(2);

				$('input[name="employee_amount"]').val(total_amount);
				delete_extra = $('<input type="hidden" name="delete_extra[]" value="'+id_extra+'">');
				$('#delete').append(delete_extra);
				$(this).parents('.tr_ex').remove();
				swal('','Eliminado exitosamente','success');
			})
			.on('click','.delete-discount',function()
			{
				id_discount		= $(this).parents('.tr_dis').find('.t_id_discount').val();
				total_temp		= Number($('input[name="employee_amount"]').val());
				total_discount	= Number($(this).parents('.tr_dis').find('.t_employee_discount').val());
				total_amount	= Number(total_temp + total_discount).toFixed(2);

				$('input[name="employee_amount"]').val(total_amount);
				delete_discount = $('<input type="hidden" name="delete_discount[]" value="'+id_discount+'">');
				$('#delete').append(delete_discount);
				$(this).parents('.tr_dis').remove();
				swal('','Eliminado exitosamente','success');
			})
			.on('change','[name="employee_extra_time"],[name="employee_holiday"],[name="employee_sundays"],[name="employee_complement"],[name="employee_amount"]',function()
			{
				extra_time	= $(this).parents('.tr_nominasNF').find('[name="employee_extra_time"]').val();
				holiday		= $(this).parents('.tr_nominasNF').find('[name="employee_holiday"]').val();
				sundays		= $(this).parents('.tr_nominasNF').find('[name="employee_sundays"]').val();
				complement	= $(this).parents('.tr_nominasNF').find('[name="employee_complement"]').val();

				discounts = 0;
				if ($('#discounts .tr_dis').length > 1) 
				{
					$('#discounts .tr_dis').each(function()
					{
						discounts += $(this).find('.t_employee_discount').val() != undefined ? Number($(this).find('.t_employee_discount').val()) : 0;
					});
				}

				extras = 0;
				if ($('#extras .tr_ex').length > 1) 
				{
					$('#extras .tr_ex').each(function()
					{
						extras += $(this).find('.t_employee_extra').val() != undefined ? Number($(this).find('.t_employee_extra').val()) : 0;
					});
				}
				employee_amount = Number(extra_time) + Number(holiday) + Number(sundays) + Number(complement) + Number(extras) - Number(discounts);
				$(this).parents('.tr_nominasNF').find('[name="employee_amount"]').val(Number(employee_amount).toFixed(2));
			})
		@endif
		.on('change','[name="absence_edit"]',function()
		{
			periodicity	= $('[name="periodicity_edit"] option:selected').val();
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
		.on('change','[name="sundays_edit"]',function()
		{
			periodicity	= $('[name="periodicity_edit"] option:selected').val();
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
		.on('change','[name="holidays_edit"]',function()
		{
			periodicity	= $('[name="periodicity_edit"] option:selected').val();
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
		.on('click','.close-modal,.exit', function()
		{
			$('.selectProvider').val(null).trigger('change');
		})
	});

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
				swal("Cargando",{
					icon: '{{ asset(getenv('LOADING_IMG')) }}',
					button: false,
					timer : 1000
				});
				return true;
			}
		});
	}
	function validationEditEmployee() 
	{
		$.validate(
		{
			form	: '#edit_employee',
			modules	: "security",
			onError	: function($form)
			{
				swal('', '{{ Lang::get("messages.form_error") }}', 'error');
			},
			onSuccess : function($form)
			{
				swal("Cargando",{
					icon: '{{ asset(getenv('LOADING_IMG')) }}',
					button: false,
					timer : 1000
				});
				return true;
			}
		});
	}
</script>
@endsection
