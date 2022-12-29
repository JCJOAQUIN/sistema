@extends('layouts.child_module')
@section('data')
	@component('components.labels.not-found', [ 'variant' => 'note'])
		En esta sección únicamente obtendrá el cálculo de la nómina sin enviarla a revisión.
	@endcomponent
	@component('components.forms.form', ["attributeEx" => "method=\"POST\" id=\"container-alta\" action=\"".route('nomina.nomina-create.precalculate', $request->folio)."\"", "methodEx" => "PUT"])
		@component('components.labels.title-divisor')
			Datos de la Solicitud
		@endcomponent
		@component('components.containers.container-form')
			<div class="col-span-2">
				@component('components.labels.label') Elaboró: @endcomponent
				@component('components.labels.label')
					{{ $request->elaborateUser->name.' '.$request->elaborateUser->last_name.' '.$request->elaborateUser->scnd_last_name }}
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Título: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" name="title" placeholder="Ingrese un título" data-validation="required" value="{{ $request->nominasReal->first()->title }}" @if($request->status != 2) disabled="disabled" @endif
					@endslot
					@slot('classEx')
						removeselect
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@php
					$newDate = $request->nominasReal->first()->datetitle != '' ? Carbon\Carbon::createFromFormat('Y-m-d',$request->nominasReal->first()->datetitle)->format('d-m-Y') : '';
				@endphp
				@component('components.labels.label') Fecha: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" name="datetitle" data-validation="required" placeholder="Ingrese la fecha" readonly="readonly" value="{{ $newDate }}" @if($request->status != 2) disabled="disabled" @endif
					@endslot
					@slot('classEx')
						removeselect datepicker
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
						name="userid" multiple="multiple" data-validation="required" @if($request->status != 2) disabled="disabled" @endif
					@endslot
					@slot('classEx')
						js-users
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Tipo: @endcomponent
				@php
					$optionType = [];
					foreach(App\CatTypePayroll::all() as $t)
					{
						if($request->nominasReal->first()->idCatTypePayroll == $t->id)
						{
							$optionType[] = ["value" => $t->id, "description" => $t->description, "selected" => "selected"];
						}
						else
						{
							$optionType[] = ["value" => $t->id, "description" => $t->description];
						}
					}
				@endphp
				@component('components.inputs.select', ["options" => $optionType])
					@slot('attributeEx')
						title="Tipo de nómina" multiple="multiple" name="type_payroll" data-validation="required"
					@endslot
					@slot('classEx')
						js-type
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Categoría: @endcomponent
				@component('components.labels.label')
					{{ $request->idDepartment == 11 ? 'Obra' : 'Administrativa' }}
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Tipo: @endcomponent
				@component('components.labels.label')
					{{ $request->nominasReal->first()->typeNomina() }}
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Forma de pago: @endcomponent
				@php
					$optionPay = [];
					foreach(App\PaymentMethod::all() as $p)
					{
						$optionPay[] = ["value" =>  $p->idpaymentMethod, "description" => $p->method];
					}
				@endphp
				@component('components.inputs.select', ["options" => $optionPay])
					@slot('attributeEx')
						title="Forma de pago" multiple="multiple" name="payment_method"
					@endslot
					@slot('classEx')
						js-payment
						removeselect
					@endslot
				@endcomponent
			</div>
			@switch($request->nominasReal->first()->idCatTypePayroll)
				@case('001')
					<div class="col-span-2">
						@component('components.labels.label') Rango de Fechas: @endcomponent
						@php
							$disabled		= '';
							$newDateFrom 	= '';
							$newDateTo		= '';
							if($request->status != 2)
							{
								$disabled 		= "disabled";
								$newDateFrom	= $request->nominasReal->first()->from_date != '' ? Carbon\Carbon::createFromFormat('Y-m-d',$request->nominasReal->first()->from_date)->format('d-m-Y') : '' ;
								$newDateTo 		= $request->nominasReal->first()->to_date 	!= '' ? Carbon\Carbon::createFromFormat('Y-m-d',$request->nominasReal->first()->to_date)->format('d-m-Y') 	: '' ;
							}

							$inputs =
							[
								[
									"input_classEx" 	=> "datepicker remove",
									"input_attributeEx"	=> "type=\"text\" name=\"from_date_request\" data-validation=\"required\" placeholder=\"Desde\" readonly=\"readonly\" value=\"".$newDateFrom."\"".' '.$disabled
								],
								[
									"input_classEx"		=> "datepicker remove",
									"input_attributeEx"	=> "type=\"text\" name=\"to_date_request\" data-validation=\"required\" placeholder=\"Hasta\" readonly=\"readonly\" value=\"".$newDateTo."\"".' '.$disabled
								]
							];
						@endphp
						@component('components.inputs.range-input',["inputs" => $inputs]) @endcomponent
					</div>
					<div class="col-span-2">
						@component('components.labels.label') Periodicidad: @endcomponent
						@php
							$optionPeriodicity = [];
							foreach(App\CatPeriodicity::whereIn('c_periodicity',['02','04','05'])->get() as $per)
							{
								if($request->status != 2 && $request->nominasReal->first()->idCatPeriodicity == $per->c_periodicity)
								{
									$optionPeriodicity[] = ["value" => $per->c_periodicity, "description" => $per->description, "selected" => "selected"];
								}
								else
								{
									$optionPeriodicity[] = ["value" => $per->c_periodicity, "description" => $per->description];
								}
							}
						@endphp
						@component('components.inputs.select', ["options" => $optionPeriodicity])
							@slot('attributeEx')
								name="periodicity_request" multiple="multiple" data-validation="required"
							@endslot
							@slot('classEx')
								js-per removeselect
							@endslot
						@endcomponent
					</div>
				@break
				@case('002')
				@break
				@case('003')
					<div class="col-span-2">
						@php
							$newDateDown = $request->nominasReal->first()->down_date != '' ? Carbon\Carbon::createFromFormat('Y-m-d',$request->nominasReal->first()->down_date)->format('d-m-Y') : '' ;
						@endphp
						@component('components.labels.label') Fecha de baja: @endcomponent
						@component('components.inputs.input-text')
							@slot('attributeEx')
								type="text" name="down_date_request" data-validation="required" placeholder="Ingrese la fecha" readonly="readonly" @if($request->status != 2) disabled="disabled" value="{{ $newDateDown }}" @endif
							@endslot
							@slot('classEx')
								datepicker remove
							@endslot
						@endcomponent
					</div>
				@break
				@case('004')
					<div class="col-span-2">
						@php
							$newDateD = $request->nominasReal->first()->down_date != '' ? Carbon\Carbon::createFromFormat('Y-m-d',$request->nominasReal->first()->down_date)->format('d-m-Y') : '' ;
						@endphp
						@component('components.labels.label') Fecha de baja: @endcomponent
						@component('components.inputs.input-text')
							@slot('attributeEx')
								type="text" name="down_date_request" data-validation="required" placeholder="Ingrese la fecha" readonly="readonly" @if($request->status != 2) disabled="disabled" value="{{ $newDateD }}" @endif
							@endslot
							@slot('classEx')
								datepicker remove
							@endslot
						@endcomponent
					</div>
				@break
				@case('005')
				@break
				@case('006')
					<div class="col-span-2">
						@component('components.labels.label') PTU por pagar @endcomponent
						@component('components.inputs.input-text')
							@slot('attributeEx')
								type="text" data-validation="required" name="ptu_to_pay" placeholder="Ingrese el PTU" @if($request->status != 2) disabled="disabled" @endif
							@endslot
							@slot('classEx')
								remove
							@endslot
						@endcomponent
					</div>
				@break
			@endswitch
		@endcomponent
		@component('components.labels.title-divisor') Lista de Empleados @endcomponent
		@if($request->status != 2)
			@if($request->nominasReal->first()->type_nomina == 2)
				<div class="float-right mt-4">
					@component('components.buttons.button', [ "variant" => "success" ])
						@slot('buttonElement')
							a
						@endslot
						@slot('attributeEx')
							href="{{ route('nomina.review-nf.export',$request->folio) }}"
						@endslot
						@slot('classEx')
							export
						@endslot
						<span>Exportar datos no fiscales a Excel</span> <span class='icon-file-excel'></span>
					@endcomponent
				</div>
			@endif
		@endif
		@if($request->nominasReal->first()->type_nomina == 1)
			@switch($request->nominasReal->first()->idCatTypePayroll)
				@case('001')
					@if($request->status != 2)
						<div class="float-right mt-4">
							@component('components.buttons.button', [ "variant" => "success" ])
								@slot('buttonElement')
									a
								@endslot
								@slot('attributeEx')
									href="{{ route('nomina.export.salary',$request->folio) }}"
								@endslot
								@slot('classEx')
									export
								@endslot
								<span>Exportar a Excel</span> <span class='icon-file-excel'></span>
							@endcomponent
						</div>
					@endif
					@if($request->status == 2)
						<div class="float-right mt-4">
							@component('components.buttons.button', [ "variant" => "success" ])
								@slot('buttonElement')
									a
								@endslot
								@slot('attributeEx')
									href="{{ route('nomina.export.employee',$request->folio) }}"
								@endslot
								@slot('classEx')
									export
								@endslot
								<span>Exportar a Excel</span> <span class='icon-file-excel'></span>
							@endcomponent
						</div>
					@endif
					@php
						$body		= [];
						$modelBody	= [];
						$modelHead	= [
							[
								["value" => "Nombre del Empleado", "classEx" => "sticky inset-x-0"],
								["value" => "Desde"],
								["value" => "Hasta"],
								["value" => "Periodicidad"],
								["value" => "Sueldo Neto"],
								["value" => "Faltas"],
								["value" => "Horas extra"],
								["value" => "Días festivos"],
								["value" => "Domingos trabajados"],
								["value" => "Préstamo (Percepción)"],
								["value" => "Préstamo (Retención)"],
								["value" => "Otros (Retención)"],
								["value" => "Forma de pago"],
								["value" => "Acciones"]
							]
						];
						foreach(App\NominaEmployee::join('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')->where('nomina_employees.idnomina',$request->nominasReal->first()->idnomina)->where('nomina_employees.visible',1)->orderBy('real_employees.last_name','ASC')->orderBy('real_employees.scnd_last_name','ASC')->orderBy('real_employees.name','ASC')->select('nomina_employees.*')->get() as $n)
						{
							$paymentWayTemp	= 2;
							$accountTemp	= '';
							if($n->workerData->first()->paymentWay != '' && $n->workerData->first()->paymentWay == 1 && $n->employee->first()->bankData()->where('visible',1)->exists())
							{
								$paymentWayTemp	= 1;
								$accountTemp	= $n->employee->first()->bankData->where('visible',1)->last()->id;	
							}
							elseif($n->workerData->first()->paymentWay != '' && $n->workerData->first()->paymentWay == 3)
							{
								$paymentWayTemp	= 3;
							}
							$body = [ "classEx" => "tr_payroll",
								[
									"classEx" => "sticky inset-x-0",
									"content" =>
									[
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
											"attributeEx"	=> "type=\"hidden\" name=\"idemployeeAccount[]\" value=\"".$accountTemp."\"",
											"classEx"		=> "idemployeeAccount"
										],
										[
											"label"			=> $n->employee->first()->name.' '.$n->employee->first()->last_name.' '.$n->employee->first()->scnd_last_name
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
										"attributeEx"	=> "type=\"text\" name=\"from_date[]\" data-validation=\"required\" placeholder=\"Desde\" readonly=\"readonly\"",
										"classEx"		=> "datepicker from_date remove w-40"
									]
								]);
							}
							else
							{
								array_push($body, [ "content" => [ "label" => $n->from_date ]]);
							}
							if($request->status == 2)
							{
								array_push($body, [
									"content" =>
									[
										"kind"			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"text\" name=\"to_date[]\" data-validation=\"required\" placeholder=\"Hasta\" readonly=\"readonly\"",
										"classEx"		=> "datepicker to_date remove w-40"
									]
								]);
							}
							else
							{
								array_push($body, [ "content" => [ "label" => $n->to_date ]]);
							}
							if($request->status == 2)
							{
								$selectCatPer	= "";
								$selectCatPer	.= '<select class="border rounded py-2 px-3 m-px w-40 periodicity" name="periodicity[]" data-validation="required">';
								foreach(App\CatPeriodicity::whereIn('c_periodicity',['02','04','05'])->get() as $per)
								{
									if($n->workerData->count()>0 && $n->workerData->first()->periodicity == $per->c_periodicity)
									{
										$selectCatPer .= '<option value="'.$per->c_periodicity.'" selected="selected">'.$per->description.'</option>';
									}
									else
									{
										$selectCatPer .= '<option value="'.$per->c_periodicity.'">'.$per->description.'</option>';
									}
								}
								$selectCatPer .= '</select>';
								$body[] = [ "content" => [ "label" => $selectCatPer ]];
							}
							else
							{
								array_push($body, [ "content" => [ "label" => App\CatPeriodicity::where('c_periodicity',$n->idCatPeriodicity)->first()->description ]]);
							}
							$varNetIncome = $n->workerData->first()->netIncome != '' ? $n->workerData->first()->netIncome : '';
							array_push($body, [
								"content" =>
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"text\" name=\"netIncome[]\" placeholder=\"Ingrese el sueldo neto\" data-validation=\"required\" value=\"".$varNetIncome."\"",
									"classEx"		=> "remove w-40"
								]
							]);
							if($request->status == 2)
							{
								array_push($body, [
									"content" =>
									[
										"kind"			=> "components.inputs.input-text",
										"classEx"		=> "w-40",
										"attributeEx"	=> "type=\"text\" name=\"absence[]\" placeholder=\"Ingrese las faltas\" value=\"".$n->absence."\""
									]
								]);
							}
							else
							{
								array_push($body, [ "content" => [ "label" => $n->absence!= '' ? $n->absence : '---' ]]);
							}
							if($request->status == 2)
							{
								array_push($body, ["content" => ["kind" => "components.inputs.input-text", "attributeEx" => "type=\"text\" name=\"extraHours[]\" placeholder=\"Ingrese las horas extras\" value=\"".$n->extra_hours."\"", "classEx" => "w-40"]]);
							}
							else 
							{
								array_push($body, ["content" => ["label" => $n->extra_hours!= '' ? $n->extra_hours : '---' ]]);
							}
							if($request->status == 2)
							{
								array_push($body, ["content" => ["kind" => "components.inputs.input-text", "attributeEx" => "type=\"text\" name=\"holidays[]\" placeholder=\"Ingrese los días festivos\" value=\"".$n->holidays."\"", "classEx" => "w-40"]]);
							}
							else 
							{
								array_push($body, ["content" => ["label" => $n->holidays!= '' ? $n->holidays : '---' ]]);
							}
							if($request->status == 2)
							{
								array_push($body, ["content" => ["kind" => "components.inputs.input-text", "attributeEx" => "type=\"text\" name=\"sundays[]\" placeholder=\"Ingrese los domingos trabajados\" value=\"".$n->sundays."\"", "classEx" => "w-40"]]);
							}
							else 
							{
								array_push($body, ["content" => ["label" => $n->sundays!= '' ? $n->sundays : '---' ]]);
							}
							if($request->status == 2)
							{
								array_push($body, [
									"content" =>
									[
										"kind"			=> "components.inputs.input-text",
										"classEx"		=> "w-40",
										"attributeEx"	=> "type=\"text\" name=\"loan_perception[]\" placeholder=\"Ingrese el préstamo\""
									]
								]);
							}
							else
							{
								array_push($body, [ "content" => [ "label" => $n->loan_perception!= '' ? $n->loan_perception : '---' ]]);
							}
							if($request->status == 2)
							{
								array_push($body, [
									"content" =>
									[
										"kind"			=> "components.inputs.input-text",
										"classEx"		=> "w-40",
										"attributeEx"	=> "type=\"text\" name=\"loan_retention[]\" placeholder=\"Ingrese el préstamo\""
									]
								]);
							}
							else
							{
								array_push($body, [ "content" => [ "label" => $n->loan_retention!= '' ? $n->loan_retention : '---' ]]);
							}
							if($request->status == 2)
							{
								array_push($body, [
									"content" =>
									[
										[
											"kind"			=> "components.inputs.input-text",
											"classEx"		=> "w-40",
											"attributeEx"	=> "type=\"text\" name=\"other_retention_amount[]\" placeholder=\"Ingrese otra retención\" value=\"".$n->other_retention_amount."\""
										],
										[
											"kind"			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"text\" name=\"other_retention_concept[]\" placeholder=\"Ingrese otra retención\" value=\"".$n->other_retention_concept."\"",
											"classEx"		=> "hidden w-40"
										]
									]
								]);
							}
							else
							{
								array_push($body, [ "content" => [ "label" => $n->loan_retention!= '' ? $n->loan_retention : '---' ]]);
							}
							if($request->status == 2)
							{
								$selectPayment	= "";
								$selectPayment	.= '<select class="border rounded py-2 px-3 m-px w-40 paymentWay removeselect" name="paymentWay[]" data-validation="required">';
								foreach(App\PaymentMethod::all() as $p)
								{
									if($paymentWayTemp == $p->idpaymentMethod)
									{
										$selectPayment .= '<option value="'.$p->idpaymentMethod.'" selected="selected">'.$p->method.'</option>';
									}
									else
									{
										$selectPayment .= '<option value="'.$p->idpaymentMethod.'">'.$p->method.'</option>';
									}
								}
								$selectPayment .= '</select>';
								$body[] = [ "content" => [ "label" => $selectPayment ]];
							}
							else
							{
								array_push($body, [ "content" => [ "label" => $n->salary->first()->paymentMethod->method]]);
							}
							if($request->status != 2 && $n->nominaCFDI()->exists())
							{
								if(\Storage::disk('reserved')->exists('/stamped/'.$n->nominaCFDI->first()->uuid.'.xml'))
								{
									array_push($body, [
										"content" => 
										[
											"kind" 			=> "components.buttons.button",
											"variant" 		=> "success",
											"buttonElement" => "a",
											"attributeEx" 	=> "alt=\"XML\" title=\"XML\" href=\"".route('bill.stamped.download.xml',$n->nominaCFDI->first()->uuid)."\"", 
											"label" 		=> "<span class=\"icon-xml\"></span>"
										]
									]);
								}
								else
								{
									array_push($body, [ "content" => [ "label" => "---" ]]);
								}
								if(\Storage::disk('reserved')->exists('/stamped/'.$n->nominaCFDI->first()->uuid.'.pdf'))
								{
									array_push($body, 
									[
										"content" => 
										[
											"kind" 			=> "components.buttons.button",
											"variant" 		=> "dark-red",
											"buttonElement" => "a",
											"attributeEx" 	=> "alt=\"PDF\" title=\"PDF\" href=\"".route('bill.stamped.download.pdf',$n->nominaCFDI->first()->uuid)."\"",
											"label" 		=> "PDF"
										]
									]);
								}
								else
								{
									array_push($body, [ "content" => [ "label" => "---" ]]);
								}
								if($n->payments()->first()->documentsPayments()->exists())
								{
									foreach($n->payments->first()->documentsPayments as $pay)
									{
										array_push($body,
										[
											"content" => 
											[
												"kind" 			=> "components.buttons.button",
												"variant" 		=> "dark-red",
												"buttonElement" => "a",
												"attributeEx" 	=> "target=\"_blank\" href=\"".asset('docs/payments/'.$pay->path)."\"",
												"label" 		=> "PDF"
											]
										]);
									}
								}
								else
								{
									array_push($body, [ "content" => [ "label" => "---" ]]);
								}			
							}
							if($request->status == 2)
							{
								array_push($body,
								[
									"content" =>
									[
										[
											"kind" 			=> "components.buttons.button",
											"variant"		=> "success",
											"classEx"		=> "btn-edit-user",
											"label"			=> "<span class=\"icon-pencil\"></span>",
											"attributeEx"	=> "title=\"Editar datos personales\" type=\"button\" data-toggle=\"modal\" data-target=\"#myModal\""
										],
										[
											"kind" 			=> "components.buttons.button",
											"variant"		=> "primary",
											"classEx"		=> "btn-edit-paymentway",
											"label"			=> "<span class=\"icon-pencil\"></span>",
											"attributeEx"	=> "title=\"Editar forma de pago\" type=\"button\" data-toggle=\"modal\" data-target=\"#myModal\""
										],
										[
											"kind" 			=> "components.buttons.button",
											"variant"		=> "red",
											"classEx"		=> "btn-delete-employee",
											"label"			=> "<span class=\"icon-x\"></span>",
											"attributeEx"	=> "title=\"Eliminar\" type=\"button\""
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
				@break
				@case('002')
					@if($request->status != 2)
						<div class="float-right mt-4">
							@component('components.buttons.button', [ "variant" => "success" ])
								@slot('buttonElement')
									a
								@endslot
								@slot('attributeEx')
									href="{{ route('nomina.export.bonus',$request->folio) }}"
								@endslot
								@slot('classEx')
									export
								@endslot
								<span>Exportar a Excel</span> <span class='icon-file-excel'></span>
							@endcomponent
						</div>
					@endif
					@if($request->status == 2)
						<div class="float-right mt-4">
							@component('components.buttons.button', [ "variant" => "success" ])
								@slot('buttonElement')
									a
								@endslot
								@slot('attributeEx')
									href="{{ route('nomina.export.employee',$request->folio) }}"
								@endslot
								@slot('classEx')
									export
								@endslot
								<span>Exportar a Excel</span> <span class='icon-file-excel'></span>
							@endcomponent
						</div>
					@endif
					@php
						$body 		= [];
						$modelBody	= [];
						$modelHead	= [
							[
								["value" => "Nombre del Empleado", "classEx" => "sticky inset-x-0"],
								["value" => "Días para aguinaldo", "classEx" => "sticky inset-x-0"],
								["value" => "Forma de pago"]
							]
						];
						if($request->status != 2)
						{
							array_push($modelHead[0], ["value" => "XML"]);
							array_push($modelHead[0], ["value" => "PDF"]);
							array_push($modelHead[0], ["value" => "Documentos de pago"]);
						}
						if($request->status == 2)
						{
							array_push($modelHead[0], ["value" => "Acciones"]);	
						}
						foreach(App\NominaEmployee::join('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')->where('nomina_employees.idnomina',$request->nominasReal->first()->idnomina)->where('nomina_employees.visible',1)->orderBy('real_employees.last_name','ASC')->orderBy('real_employees.scnd_last_name','ASC')->orderBy('real_employees.name','ASC')->select('nomina_employees.*')->get() as $n)
						{
							$paymentWayTemp	= 2;
							$accountTemp	= '';
							if($n->workerData->first()->paymentWay != '' && $n->workerData->first()->paymentWay == 1 && $n->employee->first()->bankData()->where('visible',1)->exists())
							{
								$paymentWayTemp	= 1;
								$accountTemp	= $n->employee->first()->bankData->where('visible',1)->last()->id;	
							}
							elseif($n->workerData->first()->paymentWay != '' && $n->workerData->first()->paymentWay == 3)
							{
								$paymentWayTemp	= 3;
							}
							$body = [ "classEx" => "tr_payroll",
								[
									"classEx" => "sticky inset-x-0",
									"content" =>
									[
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
											"attributeEx"	=> "type=\"hidden\" name=\"idemployeeAccount[]\" value=\"".$accountTemp."\"",
											"classEx"		=> "idemployeeAccount"
										],
										[
											"label"			=> $n->employee->first()->name.' '.$n->employee->first()->last_name.' '.$n->employee->first()->scnd_last_name
										]
									]								
								]
							];
							$dayBonus = '';
							if($n->day_bonus != '' || $n->day_bonus != null)
							{
								$dayBonus = $n->day_bonus;
							}
							else
							{
								$dayBonus = "365";
							}
							if($request->status == 2)
							{
								array_push($body, [
									"classEx" => "sticky inset-x-0",
									"content" =>
									[
										"kind"			=> "components.inputs.input-text",
										"classEx"		=> "w-40",
										"attributeEx"	=> "type=\"text\" name=\"day_bonus[]\" placeholder=\"Ingrese los días para aguinaldo\" value=\"".$dayBonus."\""
									]
								]);
							}
							else
							{
								array_push($body, ["classEx" => "sticky inset-x-0", "content" => [ "label" => $n->day_bonus ]]);
							}
							if($request->status == 2)
							{
								$selectPayment	= "";
								$selectPayment	.= '<select class="border rounded py-2 px-3 m-px w-40 paymentWay removeselect" name="paymentWay[]" data-validation="required">';
								foreach(App\PaymentMethod::all() as $p)
								{
									if($paymentWayTemp == $p->idpaymentMethod)
									{
										$selectPayment .= '<option value="'.$p->idpaymentMethod.'" selected="selected">'.$p->method.'</option>';
									}
									else
									{
										$selectPayment .= '<option value="'.$p->idpaymentMethod.'">'.$p->method.'</option>';
									}
								}
								$selectPayment .= '</select>';
								$body[] = [ "content" => [ "label" => $selectPayment ]];
							}
							else
							{
								array_push($body, [ "content" => [ "label" => $n->bonus->first()->paymentMethod->method ]]);
							}
							if($request->status != 2  && $n->nominaCFDI()->exists())
							{
								if(\Storage::disk('reserved')->exists('/stamped/'.$n->nominaCFDI->first()->uuid.'.xml'))
								{
									array_push($body, [
										"content" => 
										[
											"kind" 			=> "components.buttons.button",
											"variant" 		=> "success",
											"buttonElement" => "a",
											"attributeEx" 	=> "alt=\"XML\" title=\"XML\" href=\"".route('bill.stamped.download.xml',$n->nominaCFDI->first()->uuid)."\"", 
											"label" 		=> "<span class=\"icon-xml\"></span>"
										]
									]);
								}
								else
								{
									array_push($body, [ "content" => [ "label" => "---" ]]);
								}
								if(\Storage::disk('reserved')->exists('/stamped/'.$n->nominaCFDI->first()->uuid.'.pdf'))
								{
									array_push($body, 
									[
										"content" => 
										[
											"kind" 			=> "components.buttons.button",
											"variant" 		=> "dark-red",
											"buttonElement" => "a",
											"attributeEx" 	=> "alt=\"PDF\" title=\"PDF\" href=\"".route('bill.stamped.download.pdf',$n->nominaCFDI->first()->uuid)."\"",
											"label" 		=> "PDF"
										]
									]);
								}
								else
								{
									array_push($body, [ "content" => [ "label" => "---" ]]);
								}
								if($n->payments()->first()->documentsPayments()->exists())
								{
									foreach($n->payments->first()->documentsPayments as $pay)
									{
										array_push($body,
										[
											"content" => 
											[
												"kind" 			=> "components.buttons.button",
												"variant" 		=> "dark-red",
												"buttonElement" => "a",
												"attributeEx" 	=> "target=\"_blank\" href=\"".asset('docs/payments/'.$pay->path)."\"",
												"label" 		=> "PDF"
											]
										]);
									}
								}
								else
								{
									array_push($body, [ "content" => [ "label" => "---" ]]);
								}			
							}
							if($request->status == 2)
							{
								array_push($body,
								[
									"content" =>
									[
										[
											"kind" 			=> "components.buttons.button",
											"variant"		=> "success",
											"classEx"		=> "btn-edit-user",
											"label"			=> "<span class=\"icon-pencil\"></span>",
											"attributeEx"	=> "title=\"Editar datos personales\" type=\"button\" data-toggle=\"modal\" data-target=\"#myModal\""
										],
										[
											"kind" 			=> "components.buttons.button",
											"variant"		=> "primary",
											"classEx"		=> "btn-edit-paymentway",
											"label"			=> "<span class=\"icon-pencil\"></span>",
											"attributeEx"	=> "title=\"Editar forma de pago\" type=\"button\" data-toggle=\"modal\" data-target=\"#myModal\""
										],
										[
											"kind" 			=> "components.buttons.button",
											"variant"		=> "red",
											"classEx"		=> "btn-delete-employee",
											"label"			=> "<span class=\"icon-x\"></span>",
											"attributeEx"	=> "title=\"Eliminar\" type=\"button\""
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
				@break
				@case('003')
					@if($request->status != 2)
						<div class="float-right mt-4">
							@component('components.buttons.button', [ "variant" => "success"])
								@slot('buttonElement')
									a
								@endslot
								@slot('attributeEx')
									href="{{ route('nomina.export.settlement',$request->folio) }}"
								@endslot
								@slot('classEx')
									export
								@endslot
								<span>Exportar a Excel</span> <span class='icon-file-excel'></span>
							@endcomponent
						</div>
					@endif
					@if($request->status == 2)
						<div class="float-right mt-4">
							@component('components.buttons.button', [ "variant" => "success"])
								@slot('buttonElement')
									a
								@endslot
								@slot('attributeEx')
									href="{{ route('nomina.export.employee',$request->folio) }}"
								@endslot
								@slot('classEx')
									export
								@endslot
								<span>Exportar a Excel</span> <span class='icon-file-excel'></span>
							@endcomponent
						</div>
					@endif
					@php
						$body		= [];
						$modelBody	= [];
						$modelHead	= [
							[
								["value" => "Nombre del Empleado", "classEx" => "sticky inset-x-0"],
								["value" => "Fecha de baja", "classEx" => "sticky inset-x-0"],
								["value" => "Días trabajados"],
								["value" => "Otras percepciones"],
								["value" => "Forma de pago"]
							]
						];
						if($request->status != 2)
						{
							array_push($modelHead[0], ["value" => "XML"]);
							array_push($modelHead[0], ["value" => "PDF"]);
							array_push($modelHead[0], ["value" => "Documentos de pago"]);
						}
						if($request->status == 2)
						{
							array_push($modelHead[0], ["value" => "Acciones"]);	
						}
						foreach(App\NominaEmployee::join('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')->where('nomina_employees.idnomina',$request->nominasReal->first()->idnomina)->where('nomina_employees.visible',1)->orderBy('real_employees.last_name','ASC')->orderBy('real_employees.scnd_last_name','ASC')->orderBy('real_employees.name','ASC')->select('nomina_employees.*')->get() as $n)
						{
							$paymentWayTemp	= 2;
							$accountTemp	= '';
							if($n->workerData->first()->paymentWay != '' && $n->workerData->first()->paymentWay == 1 && $n->employee->first()->bankData()->where('visible',1)->exists())
							{
								$paymentWayTemp	= 1;
								$accountTemp	= $n->employee->first()->bankData->where('visible',1)->last()->id;	
							}
							elseif($n->workerData->first()->paymentWay != '' && $n->workerData->first()->paymentWay == 3)
							{
								$paymentWayTemp	= 3;
							}
							$body = [ "classEx" => "tr_payroll",
								[
									"classEx" => "sticky inset-x-0",
									"content" => 
									[
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
											"attributeEx"	=> "type=\"hidden\" name=\"idemployeeAccount[]\" value=\"".$accountTemp."\"",
											"classEx"		=> "idemployeeAccount"
										],
										[
											"label"			=> $n->employee->first()->name.' '.$n->employee->first()->last_name.' '.$n->employee->first()->scnd_last_name
										]
									]
								]
							];
							if($request->status == 2)
							{
								array_push($body, [ 
									"classEx" => "sticky inset-x-0",
									"content" => 
									[
										"kind"			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"text\" name=\"down_date[]\" data-validation=\"required\" placeholder=\"Ingrese la fecha\" readonly=\"readonly\"",
										"classEx"		=> "datepicker down_date remove w-40"
									]
								]);
							}
							else
							{
								array_push($body, ["classEx" => "sticky inset-x-0", "content" => [ "label" => $n->down_date ]]);
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
								array_push($body, [ "content" => [ "label" => $n->worked_days ]]);
							}
							if($request->status == 2)
							{
								array_push($body, [ 
									"content" => 
									[
										"kind"			=> "components.inputs.input-text",
										"classEx"		=> "w-40",
										"attributeEx"	=> "type=\"text\" name=\"other_perception[]\" placeholder=\"Ingrese otra percepción\" value=\"0\"",
									]
								]);
							}
							else
							{
								array_push($body, [ "content" => [ "label" => $n->other_perception ]]);
							}
							if($request->status == 2)
							{
								$selectPayment	= "";
								$selectPayment	.= '<select class="border rounded py-2 px-3 m-px w-40 paymentWay removeselect" name="paymentWay[]" data-validation="required">';
								foreach(App\PaymentMethod::all() as $p)
								{
									if($paymentWayTemp == $p->idpaymentMethod)
									{
										$selectPayment .= '<option value="'.$p->idpaymentMethod.'" selected="selected">'.$p->method.'</option>';
									}
									else
									{
										$selectPayment .= '<option value="'.$p->idpaymentMethod.'">'.$p->method.'</option>';
									}
								}
								$selectPayment .= '</select>';
								$body[] = [ "content" => [ "label" => $selectPayment ]];
							}
							else
							{
								array_push($body, [ "content" => [ "label" => $n->liquidation->first()->paymentMethod->method ]]);
							}
							if($request->status != 2  && $n->nominaCFDI()->exists())
							{
								if(\Storage::disk('reserved')->exists('/stamped/'.$n->nominaCFDI->first()->uuid.'.xml'))
								{
									array_push($body, [
										"content" => 
										[
											"kind" 			=> "components.buttons.button",
											"variant" 		=> "success",
											"buttonElement" => "a",
											"attributeEx" 	=> "alt=\"XML\" title=\"XML\" href=\"".route('bill.stamped.download.xml',$n->nominaCFDI->first()->uuid)."\"", 
											"label"			=> "<span class=\"icon-xml\"></span>"
										]
									]);
								}
								else
								{
									array_push($body, [ "content" => [ "label" => "---" ]]);
								}
								if(\Storage::disk('reserved')->exists('/stamped/'.$n->nominaCFDI->first()->uuid.'.pdf'))
								{
									array_push($body, 
									[
										"content" => 
										[
											"kind" 			=> "components.buttons.button",
											"variant" 		=> "dark-red",
											"buttonElement" => "a",
											"attributeEx" 	=> "alt=\"PDF\" title=\"PDF\" href=\"".route('bill.stamped.download.pdf',$n->nominaCFDI->first()->uuid)."\"",
											"label" 		=> "PDF"
										]
									]);
								}
								else
								{
									array_push($body, [ "content" => [ "label" => "---" ]]);
								}
								if($n->payments()->first()->documentsPayments()->exists())
								{
									foreach($n->payments->first()->documentsPayments as $pay)
									{
										array_push($body,
										[
											"content" => 
											[
												"kind" 			=> "components.buttons.button",
												"variant" 		=> "dark-red",
												"buttonElement" => "a",
												"attributeEx" 	=> "target=\"_blank\" href=\"".asset('docs/payments/'.$pay->path)."\"",
												"label" 		=> "PDF"
											]
										]);
									}
								}
								else
								{
									array_push($body, [ "content" => [ "label" => "---" ]]);
								}			
							}
							if($request->status == 2)
							{
								array_push($body,
								[
									"content" =>
									[
										[
											"kind" 			=> "components.buttons.button",
											"variant"		=> "success",
											"classEx"		=> "btn-edit-user",
											"label"			=> "<span class=\"icon-pencil\"></span>",
											"attributeEx"	=> "title=\"Editar datos personales\" type=\"button\" data-toggle=\"modal\" data-target=\"#myModal\""
										],
										[
											"kind" 			=> "components.buttons.button",
											"variant"		=> "primary",
											"classEx"		=> "btn-edit-paymentway",
											"label"			=> "<span class=\"icon-pencil\"></span>",
											"attributeEx"	=> "title=\"Editar forma de pago\" type=\"button\" data-toggle=\"modal\" data-target=\"#myModal\""
										],
										[
											"kind" 			=> "components.buttons.button",
											"variant"		=> "red",
											"classEx"		=> "btn-delete-employee",
											"label"			=> "<span class=\"icon-x\"></span>",
											"attributeEx"	=> "title=\"Eliminar\" type=\"button\""
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
				@break
				@case('004')
					@if($request->status != 2)
						<div class="float-right mt-4">
							@component('components.buttons.button', [ "variant" => "success"])
								@slot('buttonElement')
									a
								@endslot
								@slot('attributeEx')
									href="{{ route('nomina.export.liquidation',$request->folio) }}"
								@endslot
								@slot('classEx')
									export
								@endslot
								<span>Exportar a Excel</span> <span class='icon-file-excel'></span>
							@endcomponent
						</div>
					@endif
					@if($request->status == 2)
						<div class="float-right mt-4">
							@component('components.buttons.button', [ "variant" => "success"])
								@slot('buttonElement')
									a
								@endslot
								@slot('attributeEx')
									href="{{ route('nomina.export.employee',$request->folio) }}"
								@endslot
								@slot('classEx')
									export
								@endslot
								<span>Exportar a Excel</span> <span class='icon-file-excel'></span>
							@endcomponent
						</div>
					@endif
					@php
						$body		= [];
						$modelBody	= [];
						$modelHead	= [
							[
								["value" => "Nombre del Empleado", "classEx" => "sticky inset-x-0"],
								["value" => "Fecha de baja", "classEx" => "sticky inset-x-0"],
								["value" => "Días trabajados"],
								["value" => "Otras percepciones"],
								["value" => "Forma de pago"]
							]
						];
						if($request->status != 2)
						{
							array_push($modelHead[0], ["value" => "XML"]);
							array_push($modelHead[0], ["value" => "PDF"]);
							array_push($modelHead[0], ["value" => "Documentos de pago"]);
						}
						if($request->status == 2)
						{
							array_push($modelHead[0], ["value" => "Acciones"]);	
						}
						foreach(App\NominaEmployee::join('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')->where('nomina_employees.idnomina',$request->nominasReal->first()->idnomina)->where('nomina_employees.visible',1)->orderBy('real_employees.last_name','ASC')->orderBy('real_employees.scnd_last_name','ASC')->orderBy('real_employees.name','ASC')->select('nomina_employees.*')->get() as $n)
						{
							$paymentWayTemp	= 2;
							$accountTemp	= '';
							if($n->workerData->first()->paymentWay != '' && $n->workerData->first()->paymentWay == 1 && $n->employee->first()->bankData()->where('visible',1)->exists())
							{
								$paymentWayTemp	= 1;
								$accountTemp	= $n->employee->first()->bankData->where('visible',1)->last()->id;	
							}
							elseif($n->workerData->first()->paymentWay != '' && $n->workerData->first()->paymentWay == 3)
							{
								$paymentWayTemp	= 3;
							}
							$body = [ "classEx" => "tr_payroll",
								[
									"classEx" => "sticky inset-x-0",
									"content" => 
									[
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
											"attributeEx"	=> "type=\"hidden\" name=\"idemployeeAccount[]\" value=\"".$accountTemp."\"",
											"classEx"		=> "idemployeeAccount"
										],
										[
											"label"			=> $n->employee->first()->name.' '.$n->employee->first()->last_name.' '.$n->employee->first()->scnd_last_name
										]
									]
								]
							];
							if($request->status == 2)
							{
								array_push($body, [ 
									"classEx" => "sticky inset-x-0",
									"content" => 
									[
										"kind"			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"text\" name=\"down_date[]\" data-validation=\"required\" placeholder=\"Ingrese la fecha\" readonly=\"readonly\"",
										"classEx"		=> "datepicker down_date remove w-40"
									]
								]);
							}
							else
							{
								array_push($body, ["classEx" => "sticky inset-x-0", "content" => [ "label" => $n->down_date ]]);
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
								array_push($body, [ "content" => [ "label" => $n->worked_days ]]);
							}
							if($request->status == 2)
							{
								array_push($body, [ 
									"content" => 
									[
										"kind"			=> "components.inputs.input-text",
										"classEx"		=> "w-40",
										"attributeEx"	=> "type=\"text\" name=\"other_perception[]\" placeholder=\"Ingrese otra percepción\" value=\"0\"",
									]
								]);
							}
							else
							{
								array_push($body, [ "content" => [ "label" => $n->other_perception ]]);
							}
							if($request->status == 2)
							{
								$selectPayment	= "";
								$selectPayment	.= '<select class="border rounded py-2 px-3 m-px w-40 paymentWay removeselect" name="paymentWay[]" data-validation="required">';
								foreach(App\PaymentMethod::all() as $p)
								{
									if($paymentWayTemp == $p->idpaymentMethod)
									{
										$selectPayment .= '<option value="'.$p->idpaymentMethod.'" selected="selected">'.$p->method.'</option>';
									}
									else
									{
										$selectPayment .= '<option value="'.$p->idpaymentMethod.'">'.$p->method.'</option>';
									}
								}
								$selectPayment .= '</select>';
								$body[] = [ "content" => [ "label" => $selectPayment ]];
							}
							else
							{
								array_push($body, [ "content" => [ "label" => $n->liquidation->first()->paymentMethod->method ]]);
							}
							if($request->status != 2  && $n->nominaCFDI()->exists())
							{
								if(\Storage::disk('reserved')->exists('/stamped/'.$n->nominaCFDI->first()->uuid.'.xml'))
								{
									array_push($body, [
										"content" => 
										[
											"kind" 			=> "components.buttons.button",
											"variant" 		=> "success",
											"buttonElement" => "a",
											"attributeEx" 	=> "alt=\"XML\" title=\"XML\" href=\"".route('bill.stamped.download.xml',$n->nominaCFDI->first()->uuid)."\"", 
											"label" 		=> "<span class=\"icon-xml\"></span>"
										]
									]);
								}
								else
								{
									array_push($body, [ "content" => [ "label" => "---" ]]);
								}
								if(\Storage::disk('reserved')->exists('/stamped/'.$n->nominaCFDI->first()->uuid.'.pdf'))
								{
									array_push($body, 
									[
										"content" => 
										[
											"kind" 			=> "components.buttons.button",
											"variant" 		=> "dark-red",
											"buttonElement" => "a",
											"attributeEx" 	=> "alt=\"PDF\" title=\"PDF\" href=\"".route('bill.stamped.download.pdf',$n->nominaCFDI->first()->uuid)."\"",
											"label" 		=> "PDF"
										]
									]);
								}
								else
								{
									array_push($body, [ "content" => [ "label" => "---" ]]);
								}
								if($n->payments()->first()->documentsPayments()->exists())
								{
									foreach($n->payments->first()->documentsPayments as $pay)
									{
										array_push($body,
										[
											"content" => 
											[
												"kind" 			=> "components.buttons.button",
												"variant" 		=> "dark-red",
												"buttonElement" => "a",
												"attributeEx" 	=> "target=\"_blank\" href=\"".asset('docs/payments/'.$pay->path)."\"",
												"label" 		=> "PDF"
											]
										]);
									}
								}
								else
								{
									array_push($body, [ "content" => [ "label" => "---" ]]);
								}			
							}
							if($request->status == 2)
							{
								array_push($body,
								[
									"content" =>
									[
										[
											"kind" 			=> "components.buttons.button",
											"variant"		=> "success",
											"classEx"		=> "btn-edit-user",
											"label"			=> "<span class=\"icon-pencil\"></span>",
											"attributeEx"	=> "title=\"Editar datos personales\" type=\"button\" data-toggle=\"modal\" data-target=\"#myModal\""
										],
										[
											"kind" 			=> "components.buttons.button",
											"variant"		=> "primary",
											"classEx"		=> "btn-edit-paymentway",
											"label"			=> "<span class=\"icon-pencil\"></span>",
											"attributeEx"	=> "title=\"Editar forma de pago\" type=\"button\" data-toggle=\"modal\" data-target=\"#myModal\""
										],
										[
											"kind" 			=> "components.buttons.button",
											"variant"		=> "red",
											"classEx"		=> "btn-delete-employee",
											"label"			=> "<span class=\"icon-x\"></span>",
											"attributeEx"	=> "title=\"Eliminar\" type=\"button\""
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
				@break
				@case('005')
					@if($request->status != 2)
						<div class="float-right mt-4">
							@component('components.buttons.button', [ "variant" => "success"])
								@slot('buttonElement')
									a
								@endslot
								@slot('attributeEx')
									href="{{ route('nomina.export.vacationpremium',$request->folio) }}"
								@endslot
								@slot('classEx')
									export
								@endslot
								<span>Exportar a Excel</span> <span class='icon-file-excel'></span>
							@endcomponent
						</div>
					@endif
					@if($request->status == 2)
						<div class="float-right mt-4">
							@component('components.buttons.button', [ "variant" => "success"])
								@slot('buttonElement')
									a
								@endslot
								@slot('attributeEx')
									href="{{ route('nomina.export.employee',$request->folio) }}"
								@endslot
								@slot('classEx')
									export
								@endslot
								<span>Exportar a Excel</span> <span class='icon-file-excel'></span>
							@endcomponent
						</div>
					@endif
					@php
						$body		= [];
						$modelBody	= [];
						$modelHead	= [
							[
								["value" => "Nombre del Empleado", "classEx" => "sticky inset-x-0"],
								["value" => "Días trabajados", "classEx" => "sticky inset-x-0"],
								["value" => "Forma de pago"]
							]
						];
						if($request->status != 2)
						{
							array_push($modelHead[0], ["value" => "XML"]);
							array_push($modelHead[0], ["value" => "PDF"]);
							array_push($modelHead[0], ["value" => "Documentos de pago"]);
						}
						if($request->status == 2)
						{
							array_push($modelHead[0], ["value" => "Acciones"]);	
						}
						
						foreach(App\NominaEmployee::join('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')->where('nomina_employees.idnomina',$request->nominasReal->first()->idnomina)->where('nomina_employees.visible',1)->orderBy('real_employees.last_name','ASC')->orderBy('real_employees.scnd_last_name','ASC')->orderBy('real_employees.name','ASC')->select('nomina_employees.*')->get() as $n)
						{
							$paymentWayTemp	= 2;
							$accountTemp	= '';
							if($n->workerData->first()->paymentWay != '' && $n->workerData->first()->paymentWay == 1 && $n->employee->first()->bankData()->where('visible',1)->exists())
							{
								$paymentWayTemp	= 1;
								$accountTemp	= $n->employee->first()->bankData->where('visible',1)->last()->id;	
							}
							elseif($n->workerData->first()->paymentWay != '' && $n->workerData->first()->paymentWay == 3)
							{
								$paymentWayTemp	= 3;
							}
							$body = [ "classEx" => "tr_payroll",
								[
									"classEx" => "sticky inset-x-0",
									"content" => 
									[
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
											"attributeEx"	=> "type=\"hidden\" name=\"idemployeeAccount[]\" value=\"".$accountTemp."\"",
											"classEx"		=> "idemployeeAccount"
										],
										[
											"label"			=> $n->employee->first()->name.' '.$n->employee->first()->last_name.' '.$n->employee->first()->scnd_last_name
										]
									]
								]
							];
							if($request->status == 2)
							{
								array_push($body, [ 
									"classEx" => "sticky inset-x-0",
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
								array_push($body, ["classEx" => "sticky inset-x-0", "content" => [ "label" => $n->worked_days ]]);
							}
							if($request->status == 2)
							{
								$selectPayment	= "";
								$selectPayment	.= '<select class="border rounded py-2 px-3 m-px w-40 paymentWay removeselect" name="paymentWay[]" data-validation="required">';
								foreach(App\PaymentMethod::all() as $p)
								{
									if($paymentWayTemp == $p->idpaymentMethod)
									{
										$selectPayment .= '<option value="'.$p->idpaymentMethod.'" selected="selected">'.$p->method.'</option>';
									}
									else
									{
										$selectPayment .= '<option value="'.$p->idpaymentMethod.'">'.$p->method.'</option>';
									}
								}
								$selectPayment .= '</select>';
								$body[] = [ "content" => [ "label" => $selectPayment ]];
							}
							else
							{
								array_push($body, [ "content" => [ "label" => $n->vacationPremium->first()->paymentMethod->method ]]);
							}
							if($request->status != 2  && $n->nominaCFDI()->exists())
							{
								if(\Storage::disk('reserved')->exists('/stamped/'.$n->nominaCFDI->first()->uuid.'.xml'))
								{
									array_push($body, [
										"content" => 
										[
											"kind" 			=> "components.buttons.button",
											"variant" 		=> "success",
											"buttonElement" => "a",
											"attributeEx" 	=> "alt=\"XML\" title=\"XML\" href=\"".route('bill.stamped.download.xml',$n->nominaCFDI->first()->uuid)."\"", 
											"label" 		=> "<span class=\"icon-xml\"></span>"
										]
									]);
								}
								else
								{
									array_push($body, [ "content" => [ "label" => "---" ]]);
								}
								if(\Storage::disk('reserved')->exists('/stamped/'.$n->nominaCFDI->first()->uuid.'.pdf'))
								{
									array_push($body, 
									[
										"content" => 
										[
											"kind" 			=> "components.buttons.button",
											"variant" 		=> "dark-red",
											"buttonElement" => "a",
											"attributeEx" 	=> "alt=\"PDF\" title=\"PDF\" href=\"".route('bill.stamped.download.pdf',$n->nominaCFDI->first()->uuid)."\"",
											"label" 		=> "PDF"
										]
									]);
								}
								else
								{
									array_push($body, [ "content" => [ "label" => "---" ]]);
								}
								if($n->payments()->first()->documentsPayments()->exists())
								{
									foreach($n->payments->first()->documentsPayments as $pay)
									{
										array_push($body,
										[
											"content" => 
											[
												"kind" 			=> "components.buttons.button",
												"variant" 		=> "dark-red",
												"buttonElement" => "a",
												"attributeEx" 	=> "target=\"_blank\" href=\"".asset('docs/payments/'.$pay->path)."\"",
												"label" 		=> "PDF"
											]
										]);
									}
								}
								else
								{
									array_push($body, [ "content" => [ "label" => "---" ]]);
								}			
							}
							if($request->status == 2)
							{
								array_push($body,
								[
									"content" =>
									[
										[
											"kind" 			=> "components.buttons.button",
											"variant"		=> "success",
											"classEx"		=> "btn-edit-user",
											"label"			=> "<span class=\"icon-pencil\"></span>",
											"attributeEx"	=> "title=\"Editar datos personales\" type=\"button\" data-toggle=\"modal\" data-target=\"#myModal\""
										],
										[
											"kind" 			=> "components.buttons.button",
											"variant"		=> "primary",
											"classEx"		=> "btn-edit-paymentway",
											"label"			=> "<span class=\"icon-pencil\"></span>",
											"attributeEx"	=> "title=\"Editar forma de pago\" type=\"button\" data-toggle=\"modal\" data-target=\"#myModal\""
										],
										[
											"kind" 			=> "components.buttons.button",
											"variant"		=> "red",
											"classEx"		=> "btn-delete-employee",
											"label"			=> "<span class=\"icon-x\"></span>",
											"attributeEx"	=> "title=\"Eliminar\" type=\"button\""
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
				@break
				@case('006')
					@if($request->status != 2)
						<div class="float-right mt-4">
							@component('components.buttons.button', [ "variant" => "success"])
								@slot('buttonElement')
									a
								@endslot
								@slot('attributeEx')
									href="{{ route('nomina.export.profitsharing',$request->folio) }}"
								@endslot
								@slot('classEx')
									export
								@endslot
								<span>Exportar a Excel</span> <span class='icon-file-excel'></span>
							@endcomponent
						</div>
					@endif
					@if($request->status == 2)
						<div class="float-right mt-4">
							@component('components.buttons.button', [ "variant" => "success"])
								@slot('buttonElement')
									a
								@endslot
								@slot('attributeEx')
									href="{{ route('nomina.export.employee',$request->folio) }}"
								@endslot
								@slot('classEx')
									export
								@endslot
								<span>Exportar a Excel</span> <span class='icon-file-excel'></span>
							@endcomponent
						</div>
					@endif
					@php
						$body		= [];
						$modelBody	= [];
						$modelHead	= [
							[
								["value" => "Nombre del Empleado", "classEx" => "sticky inset-x-0"],
								["value" => "Días trabajados", "classEx" => "sticky inset-x-0"],
								["value" => "Forma de pago"]
							]
						];
						if($request->status != 2)
						{
							array_push($modelHead[0], ["value" => "XML"]);
							array_push($modelHead[0], ["value" => "PDF"]);
							array_push($modelHead[0], ["value" => "Documentos de pago"]);
						}
						if($request->status == 2)
						{
							array_push($modelHead[0], ["value" => "Acciones"]);	
						}
						foreach(App\NominaEmployee::join('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')->where('nomina_employees.idnomina',$request->nominasReal->first()->idnomina)->where('nomina_employees.visible',1)->orderBy('real_employees.last_name','ASC')->orderBy('real_employees.scnd_last_name','ASC')->orderBy('real_employees.name','ASC')->select('nomina_employees.*')->get() as $n)
						{
							$paymentWayTemp	= 2;
							$accountTemp	= '';
							if($n->workerData->first()->paymentWay != '' && $n->workerData->first()->paymentWay == 1 && $n->employee->first()->bankData()->where('visible',1)->exists())
							{
								$paymentWayTemp	= 1;
								$accountTemp	= $n->employee->first()->bankData->where('visible',1)->last()->id;	
							}
							elseif($n->workerData->first()->paymentWay != '' && $n->workerData->first()->paymentWay == 3)
							{
								$paymentWayTemp	= 3;
							}
							$body = [ "classEx" => "tr_payroll",
								[
									"classEx" => "sticky inset-x-0",
									"content" => 
									[
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
											"attributeEx"	=> "type=\"hidden\" name=\"idemployeeAccount[]\" value=\"".$accountTemp."\"",
											"classEx"		=> "idemployeeAccount"
										],
										[
											"label"			=> $n->employee->first()->name.' '.$n->employee->first()->last_name.' '.$n->employee->first()->scnd_last_name
										]
									]
								]
							];
							$workedDays = $n->worked_days != "" ? $n->worked_days : 365;
							if($request->status == 2)
							{
								array_push($body, [ 
									"classEx" => "sticky inset-x-0",
									"content" => 
									[
										"kind"			=> "components.inputs.input-text",
										"classEx"		=> "w-40",
										"attributeEx"	=> "type=\"text\" name=\"worked_days[]\" placeholder=\"Ingrese los días trabajados\" value=\"".$workedDays."\"",
									]
								]);
							}
							else
							{
								array_push($body, ["classEx" => "sticky inset-x-0", "content" => [ "label" => $n->worked_days ]]);
							}
							if($request->status == 2)
							{
								$selectPayment	= "";
								$selectPayment	.= '<select class="border rounded py-2 px-3 m-px w-40 paymentWay removeselect" name="paymentWay[]" data-validation="required">';
								foreach(App\PaymentMethod::all() as $p)
								{
									if($paymentWayTemp == $p->idpaymentMethod)
									{
										$selectPayment .= '<option value="'.$p->idpaymentMethod.'" selected="selected">'.$p->method.'</option>';
									}
									else
									{
										$selectPayment .= '<option value="'.$p->idpaymentMethod.'">'.$p->method.'</option>';
									}
								}
								$selectPayment .= '</select>';
								$body[] = [ "content" => [ "label" => $selectPayment ]];
							}
							else
							{
								array_push($body, [ "content" => [ "label" => $n->profitsharing->first()->paymentMethod->method ]]);
							}
							if($request->status != 2  && $n->nominaCFDI()->exists())
							{
								if(\Storage::disk('reserved')->exists('/stamped/'.$n->nominaCFDI->first()->uuid.'.xml'))
								{
									array_push($body, [
										"content" => 
										[
											"kind" 			=> "components.buttons.button",
											"variant" 		=> "success",
											"buttonElement" => "a",
											"attributeEx" 	=> "alt=\"XML\" title=\"XML\" href=\"".route('bill.stamped.download.xml',$n->nominaCFDI->first()->uuid)."\"", 
											"label" 		=> "<span class=\"icon-xml\"></span>"
										]
									]);
								}
								else
								{
									array_push($body, [ "content" => [ "label" => "---" ]]);
								}
								if(\Storage::disk('reserved')->exists('/stamped/'.$n->nominaCFDI->first()->uuid.'.pdf'))
								{
									array_push($body, 
									[
										"content" => 
										[
											"kind" 			=> "components.buttons.button",
											"variant" 		=> "dark-red",
											"buttonElement" => "a",
											"attributeEx" 	=> "alt=\"PDF\" title=\"PDF\" href=\"".route('bill.stamped.download.pdf',$n->nominaCFDI->first()->uuid)."\"",
											"label" 		=> "PDF"
										]
									]);
								}
								else
								{
									array_push($body, [ "content" => [ "label" => "---" ]]);
								}
								if($n->payments()->first()->documentsPayments()->exists())
								{
									foreach($n->payments->first()->documentsPayments as $pay)
									{
										array_push($body,
										[
											"content" => 
											[
												"kind" 			=> "components.buttons.button",
												"variant" 		=> "dark-red",
												"buttonElement" => "a",
												"attributeEx" 	=> "target=\"_blank\" href=\"".asset('docs/payments/'.$pay->path)."\"",
												"label" 		=> "PDF"
											]
										]);
									}
								}
								else
								{
									array_push($body, [ "content" => [ "label" => "---" ]]);
								}			
							}
							if($request->status == 2)
							{
								array_push($body,
								[
									"content" =>
									[
										[
											"kind" 			=> "components.buttons.button",
											"variant"		=> "success",
											"classEx"		=> "btn-edit-user",
											"label"			=> "<span class=\"icon-pencil\"></span>",
											"attributeEx"	=> "title=\"Editar datos personales\" type=\"button\" data-toggle=\"modal\" data-target=\"#myModal\""
										],
										[
											"kind" 			=> "components.buttons.button",
											"variant"		=> "primary",
											"classEx"		=> "btn-edit-paymentway",
											"label"			=> "<span class=\"icon-pencil\"></span>",
											"attributeEx"	=> "title=\"Editar forma de pago\" type=\"button\" data-toggle=\"modal\" data-target=\"#myModal\""
										],
										[
											"kind" 			=> "components.buttons.button",
											"variant"		=> "red",
											"classEx"		=> "btn-delete-employee",
											"label"			=> "<span class=\"icon-x\"></span>",
											"attributeEx"	=> "title=\"Eliminar\" type=\"button\""
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
				@break
			@endswitch
		@else
			@component('components.labels.label')
				* Verifique que el sueldo neto sea correcto para cada empleado
			@endcomponent
			@if($request->status == 2)
				<div class="float-right mt-4">
					@component('components.buttons.button', [ "variant" => "success"])
						@slot('buttonElement')
							a
						@endslot
						@slot('attributeEx')
							href="{{ route('nomina.export.employee',$request->folio) }}"
						@endslot
						@slot('classEx')
							export
						@endslot
						<span>Exportar a Excel</span> <span class='icon-file-excel'></span>
					@endcomponent
				</div>
			@endif
			@php
				$body		= [];
				$modelBody	= [];
				$modelHead	= [
					[
						["value" => "Nombre del Empleado", "classEx" => "sticky inset-x-0"],
						["value" => "Complemento"],
						["value" => "Sueldo Neto"]
					]
				];
				if($request->status == 2)
				{
					array_push($modelHead[0], ["value" => "Acciones"]);
				}
				$req	= App\RequestModel::find($request->folio);
				$rf = App\RequestModel::where('kind',16)
					->where('idprenomina',$req->idprenomina)
					->where('idDepartment',$req->idDepartment)
					->first();

				foreach(App\NominaEmployee::join('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')->where('nomina_employees.idnomina',$request->nominasReal->first()->idnomina)->where('nomina_employees.visible',1)->orderBy('real_employees.last_name','ASC')->orderBy('real_employees.scnd_last_name','ASC')->orderBy('real_employees.name','ASC')->select('nomina_employees.*')->get() as $n)
				{
					$workedData = $n->workerData->first()->complement != ''  ? $n->workerData->first()->complement : '';
					$netInc = $n->workerData->first()->netIncome != '' ? $n->workerData->first()->netIncome : '';
					$body = [ "classEx" => "tr_payroll",
						[
							"classEx" => "sticky inset-x-0",
							"content" => 
							[
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" name=\"request_idnominaEmployee[]\" value=\"".$n->idnominaEmployee."\"",
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
									"label"			=> $n->employee->first()->name.' '.$n->employee->first()->last_name.' '.$n->employee->first()->scnd_last_name
								]
							]
						],
						[
							"content" =>
							[
								"kind"			=> "components.inputs.input-text",
								"attributeEx"	=> "type=\"text\" data-validation=\"required\" placeholder=\"Ingrese el complemento\" name=\"request_complement[]\" value=\"".$workedData."\"",
								"classEx"		=> "remove w-40"
							]
						],
						[
							"content" =>
							[
								"kind"			=> "components.inputs.input-text",
								"attributeEx"	=> "type=\"text\" data-validation=\"required\" placeholder=\"Ingrese el sueldo neto\" name=\"request_netIncome[]\" value=\"".$netInc."\"",
								"classEx"		=> "remove w-40"
							]
						]
					];
					if($request->status == 2)
					{
						array_push($body,
						[
							"content" =>
							[
								[
									"kind" 			=> "components.buttons.button",
									"variant"		=> "success",
									"classEx"		=> "btn-edit-user",
									"label"			=> "<span class=\"icon-pencil\"></span>",
									"attributeEx"	=> "title=\"Editar datos personales\" type=\"button\" data-toggle=\"modal\" data-target=\"#myModal\""
								],
								[
									"kind" 			=> "components.buttons.button",
									"variant"		=> "red",
									"classEx"		=> "btn-delete-employee",
									"label"			=> "<span class=\"icon-x\"></span>",
									"attributeEx"	=> "title=\"Eliminar\" type=\"button\""
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
		@if($request->status == 2)
			<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mt-4 mb-6">
				@component('components.buttons.button', [ 
					"variant" => "secondary"
				])
					@slot('attributeEx')
						type="submit" name="export_calculate" value="CÁLCULO REDUCIDO" formaction="{{ route('nomina.nomina-create.precalculate', $request->folio) }}"
					@endslot
					CÁLCULO REDUCIDO
				@endcomponent
				@component('components.buttons.button', [ 
					"variant" => "success"
				])
					@slot('attributeEx')
						type="submit" name="export_calculate" value="CÁLCULO COMPLETO" formaction="{{ route('nomina.nomina-create.precalculate-full', $request->folio) }}"
					@endslot
					CÁLCULO COMPLETO
				@endcomponent
				@component('components.buttons.button', [ 
					"variant" => "reset"
				])
					@slot('attributeEx')
						type="reset" name="borra" value="BORRAR CAMPOS"
					@endslot
					@slot('classEx')
						btn-delete-form
					@endslot
					BORRAR CAMPOS
				@endcomponent
			</div>
		@endif
		<div id="request"></div>
		<div id="div-delete-employee"></div>
	@endcomponent
	@component('components.modals.modal', [ "variant" => "large"])
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
		$('input[name="request_netIncome[]"], input[name="request_complement[]"], input[name="worked_days[]"], input[name="other_perception[]"]',).numeric({ altDecimal: ".", decimalPlaces: 2, negative: false });
		$('input[name="netIncome[]"],input[name="absence[]"],input[name="loan_perception[]"],input[name="loan_retention[]"]',).numeric({ altDecimal: ".", decimalPlaces: 2, negative: false });
		$('input[name="other_retention_amount[]"]',).numeric({ altDecimal: ".", decimalPlaces: 2,negative: false });
		@php
			$selects = collect([
				[
					"identificator"				=> ".js-type",
					"placeholder"				=> "Seleccione el tipo",
					"maximumSelectionLength"	=> "1"
				],
				[
					"identificator"				=> ".js-payment",
					"placeholder"				=> "Seleccione la forma de pago",
					"maximumSelectionLength"	=> "1"
				],
				[
					"identificator"				=> ".js-per",
					"placeholder"				=> "Seleccione la periocidad",
					"maximumSelectionLength"	=> "1"
				],
				[
					"identificator"				=> ".js-enterprises",
					"placeholder"				=> "Seleccione la empresa",
					"maximumSelectionLength"	=> "1"
				]
			]);
		@endphp
		@component('components.scripts.selects',["selects" => $selects]) @endcomponent
		generalSelect({'selector':'.js-users','model':13});
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
					$('.removeselect').val(null).trigger('change');
					form[0].reset();
				}
				else
				{
					swal.close();
				}
			});
		})
		.on('change','[name="day_bonus[]"]',function()
		{
			if($(this).val() > 365)
			{
				swal('', 'Ingresa una cantidad de días válida', 'error');
				$(this).addClass('error').val('');
			}
		})
		.on('click','input[name="enviar"]',function()
		{
			$('.remove').attr('data-validation','required');
			$.validate(
			{
				form: '#container-alta',
				modules	  : "security",
				onError   : function($form)
				{
					swal('', '{{ Lang::get("messages.form_error") }}', 'error');
				},
				onSuccess : function($form)
				{
					if($('#body-payroll .tr_payroll').length > 0)
					{
						swal({
							icon	: '{{ asset(getenv('LOADING_IMG')) }}',
							button: false,
							closeOnClickOutside: false,
							closeOnEsc: false
							});
						return true;
					}
					else
					{
						swal("","No tiene empleados en la lista.","error");
						return false;
					}
				}
			});
		})
		.on('click','#help-btn-search-employee',function()
		{
			swal('Ayuda','Escriba el nombre del empleado y de click en el icono del buscador, posteriormente seleccione un empleado.','info');
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
					generalSelect({'selector':'.js-projects',	'model': 14});
					generalSelect({'selector':'#cp',			'model': 2});
					generalSelect({'selector':'.bank',			'model': 28});
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
					validationEmployee();
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
			id					= $(this).parent('td').parent('tr').find('.idrealEmployee').val();
			folio				= {{ $request->folio }};
			idnominaEmployee	= $(this).parent('td').parent('tr').find('.idnominaEmployee').val();
			paymentWay 			= $(this).parent('td').parent('tr').find('.paymentWay').val();
			idemployeeAccount 	= $(this).parent('td').parent('tr').find('.idemployeeAccount').val();
			$.ajax(
			{
				type 	: 'post',
				url 	: '{{ route("nomina.nomina-create.datanf") }}',
				data 	: {
						'id':id,
						'folio':folio,
						'idnominaEmployee':idnominaEmployee,
						'paymentWay':paymentWay,
						'idemployeeAccount':idemployeeAccount
						},
				success : function(data)
				{
					$('#myModal').show().html(data);
					swal.close();
					validation();
					$('.employee_extra,.employee_discount,.employee_amount',).numeric({ altDecimal: ".", decimalPlaces: 2, negative: false });
				},
				error : function()
				{
					swal('','Sucedió un error, por favor intente de nuevo.','error');
					$('#myModal').modal('hide');
				}
			})
		})
		.on('click','.btn-edit-employee-fiscal',function()
		{
			swal(
			{
				icon	: '{{ asset(getenv('LOADING_IMG')) }}',
				button	: false,
			});
			id					= $(this).parent('td').parent('tr').find('.idrealEmployee').val();
			folio				= {{ $request->folio }};
			idnominaEmployee	= $(this).parent('td').parent('tr').find('.idnominaEmployee').val();

			idtypepayroll = $('select[name="type_payroll"] option:selected').val();
			$.ajax(
			{
				type 	: 'post',
				url 	: '{{ route("nomina.nomina-create.data") }}',
				data 	: {'id':id,'folio':folio,'idnominaEmployee':idnominaEmployee},
				success : function(data)
				{
					$('#myModal').show().html(data);
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
		.on('click','.btn-edit-paymentway',function()
		{
			swal(
			{
				icon	: '{{ asset(getenv('LOADING_IMG')) }}',
				button	: false,
			});
			id					= $(this).parents('.tr_payroll').find('.idrealEmployee').val();
			folio				= {{ $request->folio }};
			idnominaEmployee	= $(this).parents('.tr_payroll').find('.idnominaEmployee').val();
			paymentWay			= $(this).parents('.tr_payroll').find('.paymentWay').val();
			idemployeeAccount	= $(this).parents('.tr_payroll').find('.idemployeeAccount').val();
			idtypepayroll 		= $('select[name="type_payroll"] option:selected').val();
			$.ajax(
			{
				type 	: 'post',
				url 	: '{{ route("nomina.nomina-create.data-payment") }}',
				data 	: {
							'id'				:id,
							'folio'				:folio,
							'idnominaEmployee'	:idnominaEmployee,
							'paymentWay'		:paymentWay,
							'idemployeeAccount'	:idemployeeAccount
						},
				success : function(data)
				{
					$('#myModal .modal-body').show().html(data);
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
		.on('click','.update-paymentway-account',function()
		{
			idnominaEmployee	= $('input[name="idnominaEmployee"]').val();
			method_request		= $('input[name="method_request"]:checked').val();
			if (method_request == 1) 
			{
				idEmployeeAccounts_request	= $('input[name="idEmployeeAccounts_request"]:checked').val();
			}
			else
			{
				idEmployeeAccounts_request	= '';
			}

			$('.idnominaEmployee').each(function()
			{
				id = $(this).val();
				if (idnominaEmployee == id) 
				{
					$(this).parents('.tr_payroll').find('.paymentWay').val(method_request).trigger('change');
					$(this).parents('.tr_payroll').find('.idemployeeAccount').val(idEmployeeAccounts_request);
				}
			});
			$('#myModal').modal('hide');
			swal('', 'Datos atualizados exitosamente', 'success');
		})
		.on('click','.exit',function()
		{
			$('#myModal .modal-body').modal('hide');
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
				else if($(this).parents('tr').find('.card').hasClass('error') || $(this).parents('tr').find('.clabe').hasClass('error') || $(this).parents('tr').find('.account').hasClass('error'))
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
					$('.card, .clabe, .bank, .account, .alias').removeClass('error').removeClass('valid').val('');
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
					$('.card_alimony, .clabe_alimony, .account_alimony, .alias_alimony, .beneficiary').removeClass('error').removeClass('valid').val('');
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
						$('showing').attr('disabled',true).addClass('disabled');
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
						$('.showing').attr('disabled',true).addClass('disabled');
						$('.view-button').hide();
						$('.hide-td').hide();
					}
					else
					{
						$('.disabled').removeAttr('disabled').removeClass('disabled').addClass('showing');
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
				type 	: 'post',
				url 	: '{{ route("nomina.nomina-create.changetype") }}',
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
			})
		})
		.on('click','.btn-delete-employee',function()
		{
			if ($('#body-payroll .tr_payroll').length == 1) 
			{
				swal('','La solicitud debe tener al menos un empleado','error');
			}
			else
			{
				$(this).parents('.tr_payroll').find('.selectProvider').select2('close');
				swal(
				{
					icon	: '{{ asset(getenv('LOADING_IMG')) }}',
					button	: false,
					timer 	: 1000
				});
				idnominaEmployee = $(this).parents('.tr_payroll').find('.idnominaEmployee').val();
				del = $('<input type="hidden" value="'+idnominaEmployee+'" name="deleteEmployee[]">');
				$('#div-delete-employee').append(del);
				$(this).parents('.tr_payroll').remove();
			}
		})
		.on('click','.btn-view-data',function()
		{
			swal(
			{
				icon	: '{{ asset(getenv('LOADING_IMG')) }}',
				button	: false,
			});
			id					= $(this).parent('td').parent('tr').find('.idrealEmployee').val();
			folio				= {{ $request->folio }};
			idnominaEmployee	= $(this).parent('td').parent('tr').find('.idnominaEmployee').val();
			$.ajax(
			{
				type 	: 'post',
				url 	: '{{ route("nomina.nomina-create.viewdata") }}',
				data 	: {'id':id,'folio':folio,'idnominaEmployee':idnominaEmployee},
				success : function(data)
				{
					$('#myModal').show().html(data);
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
		.on('change','input[name="employee_discount"]',function()
		{
			discount = $(this).val();
			amount = $('input[name="employee_amount"]').val();

			total = amount - discount;

			$('input[name="employee_amount"]').val(total);
		})
		.on('change','input[name="to_date_request"]',function()
		{
			$('.to_date').val($(this).val());
		})
		.on('change','input[name="from_date_request"]',function()
		{
			$('.from_date').val($(this).val());
		})
		.on('change','select[name="periodicity_request"]',function()
		{
			$('.periodicity').val($(this).val()).trigger('change');
		})
		.on('change','select[name="payment_method"]',function()
		{
			$('.paymentWay').val($(this).val()).trigger('change');
		})
		.on('change','input[name="down_date_request"]',function()
		{
			$('.down_date').val($(this).val());
		})
		.on('change','[name="work_project"]',function()
		{
			project_id = $('option:selected',this).val();
			if(project_id != undefined)
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
		.on('keyup','[name="other_retention_amount[]"]',function()
		{
			if ($(this).val() != "") 
			{
				$(this).parents('.tr_payroll').find('[name="other_retention_concept[]"]').attr('data-validation','required').fadeIn();
				validation();
			}
			else
			{
				$(this).parents('.tr_payroll').find('[name="other_retention_concept[]"]').removeAttr('data-validation').fadeOut();
				validation();
			}
		})
		@if($request->nominasReal->first()->type_nomina == 2)
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
					tr = $('<tr></tr>')
						.append($('<td colspan="2"></td>')
							.append(employee_discount)
							.append($('<input type="hidden" class="t_id_discount" name="t_id_discount[]" value="x">'))
							.append($('<input type="hidden" class="t_employee_discount" name="t_employee_discount[]" value="'+employee_discount+'">')))
						.append($('<td colspan="2"></td>')
							.append(employee_reason_discount)
							.append($('<input type="hidden" name="t_employee_reason_discount[]" value="'+employee_reason_discount+'">')))
						.append($('<td></td>')
							.append($('<button type="button" class="btn btn-red delete-discount"></button>')
								.append($('<span class="icon-x"></span>'))));

					$('#discounts').append(tr);

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
					tr = $('<tr></tr>')
						.append($('<td colspan="2"></td>')
							.append(employee_extra)
							.append($('<input type="hidden" class="t_id_extra" name="t_id_extra[]" value="x">'))
							.append($('<input type="hidden" class="t_employee_extra" name="t_employee_extra[]" value="'+employee_extra+'">')))
						.append($('<td colspan="2"></td>')
							.append(employee_reason_extra)
							.append($('<input type="hidden" name="t_employee_reason_extra[]" value="'+employee_reason_extra+'">')))
						.append($('<td></td>')
							.append($('<button type="button" class="btn btn-red delete-extra"></button>')
								.append($('<span class="icon-x"></span>'))));

					$('#extras').append(tr);

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
				id_extra		= $(this).parents('tr').find('.t_id_extra').val();
				total_temp		= Number($('input[name="employee_amount"]').val());
				total_extra		= Number($(this).parents('tr').find('.t_employee_extra').val());
				total_amount 	= Number(total_temp - total_extra).toFixed(2);

				$('input[name="employee_amount"]').val(total_amount);
				delete_extra = $('<input type="hidden" name="delete_extra[]" value="'+id_extra+'">');
				$('#delete').append(delete_extra);
				$(this).parents('tr').remove();
				swal('','Eliminado exitosamente','success');
			})
			.on('click','.delete-discount',function()
			{
				id_discount		= $(this).parents('tr').find('.t_id_discount').val();
				total_temp		= Number($('input[name="employee_amount"]').val());
				total_discount	= Number($(this).parents('tr').find('.t_employee_discount').val());
				total_amount	= Number(total_temp + total_discount).toFixed(2);

				$('input[name="employee_amount"]').val(total_amount);
				delete_discount = $('<input type="hidden" name="delete_discount[]" value="'+id_discount+'">');
				$('#delete').append(delete_discount);
				$(this).parents('tr').remove();
				swal('','Eliminado exitosamente','success');
			})
		@endif
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
				if($('#body-payroll .tr_payroll').length > 0)
				{
					swal({
						icon	: '{{ asset(getenv('LOADING_IMG')) }}',
						button: false,
						closeOnClickOutside: false,
						closeOnEsc: false,
						timer : 5000
						});
					return true;
				}
				else
				{
					swal("","Su lista de empleados se encuentra vacía, por favor verifique su lista.","error");
					return false;
				}
				return true;
			}
		});
	}
	function validationEmployee()
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
				return true;
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
		cp					= $('[name="cp"] option:selected').val();
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
