@extends('layouts.child_module')

@section('data')
	@if (isset($globalRequests) && $globalRequests == true)
		@component("components.labels.not-found", ["variant" => "note"])
			@slot("slot")
				@component("components.labels.label")
					@slot("classEx")
						font-bold
						inline-block
						text-blue-900
					@endslot
						TIPO DE SOLICITUD: 
				@endcomponent
				{{ mb_strtoupper($request->requestkind->kind) }}
			@endslot
		@endcomponent
	@endif
	@component('components.forms.form', ["attributeEx" => "method=\"POST\" id=\"container-alta\" action=\"".route('nomina.nomina-create.update', $request->folio)."\"", "methodEx" => "PUT", "files" => true])
		@component('components.containers.container-form')
			<div class="col-span-2">
				@component('components.labels.label') 
					@slot('classEx')
						text-base
					@endslot
					Elaboró:
				@endcomponent
				@component('components.labels.label') {{ $request->elaborateUser->name.' '.$request->elaborateUser->last_name.' '.$request->elaborateUser->scnd_last_name }} @endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') 
					@slot('classEx')
						text-base
					@endslot
					Categoría:
				@endcomponent
				@component('components.labels.label') {{ $request->idDepartment == 11 ? 'Obra' : 'Administrativa' }} @endcomponent
			</div>
			<div class="md:col-span-4 col-span-2">
				@component('components.labels.label')
					@slot('classEx')
						text-base
					@endslot
					Tipo:
				@endcomponent
				@component('components.labels.label') {{  $request->nominasReal->first()->typeNomina() }} @endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Título: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" name="title" placeholder="Ingrese un título" data-validation="required" value="{{ $request->nominasReal->first()->title }}" @if($request->status != 2 || isset($globalRequests)) disabled="disabled" @endif
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
						type="text" name="datetitle" data-validation="required" placeholder="Ingrese la fecha" readonly="readonly" value="{{ $newDate }}" @if($request->status != 2 || isset($globalRequests)) disabled="disabled" @endif
					@endslot
					@slot('classEx')
						removeselect datepicker
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Solicitante: @endcomponent
				@php
					$optionUser = [];
					if(isset($request))
					{
						$optionUser[] = [ "value" => $request->idRequest, "description" => $request->requestUser->fullName(), "selected" => "selected"];
					}
				@endphp
				@component('components.inputs.select', [ "options" => $optionUser ])
					@slot('attributeEx')
						name="userid" multiple="multiple" data-validation="required" @if($request->status != 2 || isset($globalRequests)) disabled="disabled" @endif
					@endslot
					@slot('classEx')
						removeselect 
						js-user
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Tipo: @endcomponent
				@php
					$optionCatType = [];
					foreach(App\CatTypePayroll::orderName()->get() as $t)
					{
						if($request->nominasReal->first()->idCatTypePayroll == $t->id)
						{
							$optionCatType[] = [ "value" => $t->id, "description" => $t->description, "selected" => "selected"];
						}
						else
						{
							$optionCatType[] = [ "value" => $t->id, "description" => $t->description];
						}
					}
				@endphp
				@component('components.inputs.select', [ "options" => $optionCatType ])
					@slot('attributeEx')
						title="Tipo de nómina" name="type_payroll" disabled="disabled" data-validation="required" multiple="multiple" @if($request->status != 2 || isset($globalRequests)) disabled="disabled" @endif
					@endslot
					@slot('classEx')
						type_payroll
						js-type
					@endslot
				@endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="hidden" name="type_payroll" value="{{ $request->nominasReal->first()->idCatTypePayroll }}"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Forma de pago: @endcomponent
				@php
					$optionPay = [];
					foreach(App\PaymentMethod::orderName()->get() as $p)
					{
						$optionPay[] = ['value' => $p->idpaymentMethod, 'description' => $p->method];
					}
				@endphp
				@component('components.inputs.select', [ "options" => $optionPay ])
					@slot('attributeEx')
						title="Forma de pago" name="payment_method" multiple="multiple" @if($request->status != 2 || isset($globalRequests)) disabled="disabled" @endif
					@endslot
					@slot('classEx')
						removeselect
						js-pay
					@endslot
				@endcomponent
			</div>
			@php
				$req = App\RequestModel::find($request->folio);

				$check_request_fiscal = App\RequestModel::where('kind',16)
						->where('idprenomina',$req->idprenomina)
						->where('idDepartment',$req->idDepartment)
						->where('taxPayment',1)
						->whereNotIn('folio',[$req->folio])
						->first();
			@endphp
			@switch($request->nominasReal->first()->idCatTypePayroll)
				@case('001')
					<div class="col-span-2">
						@component("components.labels.label") Periodicidad: @endcomponent
						@php
							$optionCatPer = [];
							foreach(App\CatPeriodicity::whereIn('c_periodicity',['02','04','05'])->get() as $per)
							{
								if($request->nominasReal->first()->idCatPeriodicity == $per->c_periodicity)
								{
									/*@elseif($check_request_fiscal != "" && $check_request_fiscal->nominasReal->first()->idCatPeriodicity == $per->c_periodicity) selected="selected"  @endif*/
									$optionCatPer[] = ["value" => $per->c_periodicity, "selected" => "selected", "description" => $per->description];
								}
								else
								{
									$optionCatPer[] = ["value" => $per->c_periodicity, "description" => $per->description];
								}
							}
						@endphp
						@component("components.inputs.select", ["options" => $optionCatPer ])
							@slot("attributeEx")
								name="periodicity_request"
								data-validation="required" 
								multiple="multiple"
								@if($request->status != 2 || isset($globalRequests)) disabled="disabled" @endif
							@endslot 
							@slot('classEx')
								periodicity_request removeselect js-periodicity-request
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component('components.labels.label') Rango de Fechas: @endcomponent
						@php
							$disabled = '';
							if($request->status != 2 || isset($globalRequests))
							{
								$disabled = "disabled";
							}
							$newDateFrom	= '';
							$newDateTo 		= '';
							if($check_request_fiscal != "")
							{
								$newDateFrom 	= $check_request_fiscal->nominasReal->first()->from_date	!= '' ? Carbon\Carbon::createFromFormat('Y-m-d',$check_request_fiscal->nominasReal->first()->from_date)->format('d-m-Y')	: '' ;
								$newDateTo		= $check_request_fiscal->nominasReal->first()->to_date		!= '' ? Carbon\Carbon::createFromFormat('Y-m-d',$check_request_fiscal->nominasReal->first()->to_date)->format('d-m-Y') 		: '' ;
							}
							else
							{
								$newDateFrom	= $request->nominasReal->first()->from_date	!= '' ? Carbon\Carbon::createFromFormat('Y-m-d',$request->nominasReal->first()->from_date)->format('d-m-Y')	: '' ;
								$newDateTo		= $request->nominasReal->first()->to_date	!= '' ? Carbon\Carbon::createFromFormat('Y-m-d',$request->nominasReal->first()->to_date)->format('d-m-Y')	: '' ;
							}

							$inputs =
							[
								[
									"input_classEx" 	=> "datepicker remove from_date_request",
									"input_attributeEx"	=> "type=\"text\" name=\"from_date_request\" data-validation=\"required\" placeholder=\"Desde\" readonly=\"readonly\" value=\"".$newDateFrom."\"".' '.$disabled
								],
								[
									"input_classEx"		=> "datepicker remove to_date_request",
									"input_attributeEx"	=> "type=\"text\" name=\"to_date_request\" data-validation=\"required\" placeholder=\"Hasta\" readonly=\"readonly\" value=\"".$newDateTo."\"".' '.$disabled
								]
							];
						@endphp
						@component('components.inputs.range-input',["inputs" => $inputs]) @endcomponent
					</div>
				@break
				@case('002')
					@break
				@case('003')
					<div class="col-span-2">
						@php
							$newDateDown	= $request->nominasReal->first()->down_date != '' ? Carbon\Carbon::createFromFormat('Y-m-d',$request->nominasReal->first()->down_date)->format('d-m-Y') : '' ;
						@endphp
						@component("components.labels.label") Fecha de baja: @endcomponent
						@component("components.inputs.input-text")
							@slot("classEx")
								datepicker remove
							@endslot
							@slot("attributeEx")
								type="text"
								name="down_date_request" 
								data-validation="required" 
								placeholder="Ingrese la fecha"
								readonly="readonly" 
								@if($request->status != 2 || isset($globalRequests)) disabled="disabled" @endif 
								value="{{ $newDateDown }}"
							@endslot
						@endcomponent
					</div>
				@break
				@case('004')
					<div class="col-span-2">
						@php
							$newDateD	= $request->nominasReal->first()->down_date != '' ? Carbon\Carbon::createFromFormat('Y-m-d',$request->nominasReal->first()->down_date)->format('d-m-Y') : '' ;
						@endphp
						@component("components.labels.label") Fecha de baja: @endcomponent
						@component("components.inputs.input-text")
							@slot("classEx")
								datepicker remove
							@endslot
							@slot("attributeEx")
								type="text"
								name="down_date_request" 
								data-validation="required" 
								placeholder="Ingrese la fecha" 
								readonly="readonly" 
								@if($request->status != 2 || isset($globalRequests)) disabled="disabled" @endif 
								value="{{ $newDateD }}"
							@endslot
						@endcomponent
					</div>
				@break
				@case('005')
					@break
				@case('006')
					<div class="col-span-2">
						@component("components.labels.label") PTU por pagar: @endcomponent
						@component("components.inputs.input-text")
							@slot("classEx")
								remove
							@endslot
							@slot("attributeEx")
								type="text" 
								data-validation="required" 
								name="ptu_to_pay" 
								placeholder="Ingrese el PTU"
								@if($request->status != 2 || isset($globalRequests)) disabled="disabled" value="{{ $request->nominasReal->first()->ptu_to_pay }}" @endif
							@endslot
						@endcomponent
					</div>
				@break
			@endswitch
		@endcomponent
		@if($request->status == 2 && !isset($globalRequests))
			@if($check_request_fiscal == '' || ($check_request_fiscal != '' && $check_request_fiscal->status != 2 && $check_request_fiscal->status != 3))
				@component('components.labels.title-divisor') Selección masiva @endcomponent
				@php
					$buttonEx 	= [];
					$disabledA	= '';
					if(isset($globalRequests))
					{
						$disabledA = "disabled";
					}
					if($request->taxPayment == 0)
					{
						$buttonEx = 
						[ 
							"kind"			=> "components.buttons.button",
							"variant"		=> "primary",
							"attributeEx"	=> "type=\"submit\" id=\"upload_layout\" formaction=\"".route('nomina.upload-layout', $request->folio)."\""." ".$disabledA,
							"label" 		=> "CARGAR PLANTILLA",
							"classEx"	 	=> "w-max my-2"
						];
					}
					else
					{
						$buttonEx = 
						[ 
							"kind"			=> "components.buttons.button",
							"variant"		=> "primary",
							"attributeEx"	=> "type=\"submit\" id=\"upload_layout\" formaction=\"".route('nomina.upload.layout-fiscal', $request->folio)."\""." ".$disabledA,
							"label" 		=> "CARGAR PLANTILLA",
							"classEx"	 	=> "w-max my-2"
						];
					}
					$buttons = 
					[
						"separator" => 
						[
							[
								"kind"			=> "components.buttons.button-approval",
								"attributeEx"	=> "value=\",\" name=\"separator\" id=\"separatorComa\""." ".$disabledA,
								"label" 		=> "coma (,)"
							],
							[
								"kind"			=> "components.buttons.button-approval",
								"attributeEx"	=> "value=\";\" name=\"separator\" id=\"separatorPuntoComa\""." ".$disabledA,
								"label"			=> "Punto y coma (;)", 
							]
						],
						"buttonEx" => 
						[
							$buttonEx
						]
					];	
				@endphp
				@component('components.documents.select_file_csv',[
						"buttons" => $buttons
					])
					@slot('attributeEx')
						id="container-data-2"
					@endslot
					@slot('attributeExInput')
						name="csv_file"
						id="csv"
						@if(isset($globalRequests)) disabled @endif
					@endslot
				@endcomponent
			@endif
		@endif
		@component('components.labels.title-divisor') Lista de Empleados @endcomponent
		@if($request->status != 2)
			@component('components.labels.not-found', ["variant" => "note"])
				* Verifique que el sueldo neto sea correcto para cada empleado.
			@endcomponent
			@if($request->nominasReal->first()->type_nomina == 2)
				@component("components.buttons.button", ["variant" => "success"])
					@slot('buttonElement')
						a
					@endslot
					@slot("classEx")
						float-right
						mb-2
					@endslot
					@slot("attributeEx")
						href="{{ route('nomina.construction-review-nf.export',$request->folio) }}"
					@endslot
					<span>Exportar datos no fiscales a Excel</span> <span class="icon-file-excel"></span>
				@endcomponent
			@endif
			@if($request->nominasReal->first()->type_nomina == 3)
				@component('components.buttons.button', ["variant" => "success"])
					@slot('buttonElement')
						a
					@endslot
					@slot("classEx")
						float-right
					@endslot
					@slot('attributeEx')
						href="{{ route('nomina.nom35.export',$request->folio) }}"
					@endslot
					<span>Exportar datos a Excel</span> <span class='icon-file-excel'></span>
				@endcomponent
				@component('components.buttons.button', ["variant" => "success"])
					@slot('buttonElement')
						a
					@endslot
					@slot("classEx")
						float-right
					@endslot
					@slot('attributeEx')
						href="{{ route('nomina.report-nom035',$request->folio) }}"
					@endslot
					<span>Exportar reporte Nom035</span> <span class='icon-file-excel'></span>
				@endcomponent
			@endif
		@endif
		@if($request->nominasReal->first()->type_nomina == 1)
			@switch($request->nominasReal->first()->idCatTypePayroll)
				@case('001')
					@if($request->status != 2)
						<div class="mt-4">
							@component('components.buttons.button', ["variant" => "success"])
								@slot('buttonElement')
									a
								@endslot
								@slot("classEx")
									float-right
								@endslot
								@slot('attributeEx')
									href="{{ route('nomina.export.salary',$request->folio) }}"
								@endslot
								<span>Exportar a Excel</span> <span class='icon-file-excel'></span>
							@endcomponent
						</div>
					@endif
					@if($request->status == 2)
						<div class="mt-4">
							@component('components.buttons.button', ["variant" => "success"])
								@slot('buttonElement')
									a
								@endslot
								@slot("classEx")
									float-right
								@endslot
								@slot('attributeEx')
									href="{{ route('nomina.export.layout-fiscal',$request->folio) }}"
								@endslot
								<span>Exportar Plantilla</span> <span class='icon-file-excel'></span>
							@endcomponent
							@component('components.buttons.button', ["variant" => "success"])
								@slot('buttonElement')
									a
								@endslot
								@slot("classEx")
									float-right
								@endslot
								@slot('attributeEx')
									href="{{ route('nomina.export.employee',$request->folio) }}"
								@endslot
								<span>Exportar a Excel</span> <span class='icon-file-excel'></span>
							@endcomponent
						</div>
					@endif
					@php
						$array_s 	= session('errors_salary');
						$modelBody = [];
						foreach(App\NominaEmployee::join('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')->where('nomina_employees.idnomina',$request->nominasReal->first()->idnomina)->where('nomina_employees.visible',1)->orderBy('real_employees.last_name','ASC')->orderBy('real_employees.scnd_last_name','ASC')->orderBy('real_employees.name','ASC')->select('nomina_employees.*')->get() as $n)
						{
							$modelHead	= 
							[
								[
									["value" => "Nombre del Empleado", "classEx" => "sticky inset-x-0 bg-orange-500"],
									["value" => "Desde"],
									["value" => "Hasta"],
									["value" => "Periodicidad"],
									["value" => "Faltas"],
									["value" => "Horas extra"],
									["value" => "Días festivos"],
									["value" => "Domingos trabajados"],
									["value" => "Préstamo (Percepción)"],
									["value" => "Préstamo (Retención)"],
									["value" => "Otros (Retención)"],
									["value" => "Forma de pago"],
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
							$paymentWayTemp		= 2;
							$accountTemp		= '';
							$accountBeneficiary	= '';
							if($n->workerData->first()->paymentWay != '' && $n->workerData->first()->paymentWay == 1 && $n->employee->first()->bankData()->where('visible',1)->where('type',1)->exists())
							{
								$paymentWayTemp	= 1;
								$accountTemp	= $n->employee->first()->bankData->where('visible',1)->where('type',1)->last()->id;	
							}
							elseif($n->workerData->first()->paymentWay != '' && $n->workerData->first()->paymentWay == 3)
							{
								$paymentWayTemp	= 3;
							}
							if ($n->employee->first()->bankData()->where('visible',1)->where('type',2)->exists()) 
							{
								$accountBeneficiary = $n->employee->first()->bankData->where('visible',1)->where('type',2)->last()->id;	
							}
							$disabledSalary = '';
							if(isset($globalRequests))
							{
								$disabledSalary = "disabled";
							}
							$newDateF	= $n->from_date != '' ? Carbon\Carbon::createFromFormat('Y-m-d',$n->from_date)->format('d-m-Y') : '' ;
							$newDateT	= $n->to_date 	!= '' ? Carbon\Carbon::createFromFormat('Y-m-d',$n->to_date)->format('d-m-Y') 	: '' ;
							$redClass 	= "";
							if (isset($array_s) && in_array($n->idnominaEmployee,$array_s))
							{
								$redClass = "tr-red-sticky";
							}

							$body = 
							[	"classEx" => "tr_payroll ".$redClass,
								[
									"classEx" => "sticky inset-x-0 bg-white",
									"content" => 
									[
										["kind" => "components.inputs.input-text",	"attributeEx" => "type=\"hidden\" name=\"idnominaEmployee_request[]\"	value=\"".$n->idnominaEmployee."\"", 	"classEx" => "idnominaEmployee"],
										["kind" => "components.inputs.input-text",	"attributeEx" => "type=\"hidden\" name=\"idrealEmployee[]\" 			value=\"".$n->idrealEmployee."\"",		"classEx" => "idrealEmployee"],
										["kind" => "components.inputs.input-text",	"attributeEx" => "type=\"hidden\" name=\"idworkingData[]\" 				value=\"".$n->idworkingData."\"", 		"classEx" => "idworkingData"],
										["kind" => "components.inputs.input-text",	"attributeEx" => "type=\"hidden\" name=\"idemployeeAccount[]\" 			value=\"".$accountTemp."\"", 			"classEx" => "idemployeeAccount"],
										["kind" => "components.inputs.input-text",	"attributeEx" => "type=\"hidden\" name=\"idAccountBeneficiary[]\" 		value=\"".$accountBeneficiary."\"",		"classEx" => "idAccountBeneficiary"],
										[
											"kind"		=> "components.labels.label",
											"classEx"	=> "md:p-2 p-0 w-40",
											"label" 	=> $n->employee->first()->last_name.' '.$n->employee->first()->scnd_last_name.' '.$n->employee->first()->name
										]
									]
								]
							];
							if($request->status == 2)
							{
								array_push($body, ["content" => [["kind" => "components.inputs.input-text", "attributeEx" => "type=\"text\" name=\"from_date[]\" readonly=\"readonly\" placeholder=\"Desde\" data-validation=\"required\" value=\"".$newDateF."\"".' '.$disabledSalary, "classEx" => "datepicker from_date remove w-40"]]]);
							}
							else
							{
								array_push($body, ["content" => ["kind" =>"components.labels.label", "classEx" => "text-black w-40", "label" => $newDateF]]);
							}
							if($request->status == 2)
							{
								array_push($body, ["content" => [["kind" => "components.inputs.input-text", "attributeEx" => "type=\"text\" name=\"to_date[]\" readonly=\"readonly\" placeholder=\"Hasta\" data-validation=\"required\" value=\"".$newDateT."\"".' '.$disabledSalary, "classEx" => "datepicker to_date remove w-40"]]]);
							}
							else
							{
								array_push($body, ["content" => ["kind" =>"components.labels.label", "classEx" => "text-black w-40", "label" => $newDateT]]);
							}
							if($request->status == 2)
							{	
								$selectCatPer	= "";
								$selectCatPer	.= '<select class="border rounded py-2 px-3 m-px w-40 periodicity" name="periodicity[]" data-validation="required" '.$disabledSalary.'>';
								foreach(App\CatPeriodicity::whereIn('c_periodicity',['02','04','05'])->get() as $per)
								{
									if($n->idCatPeriodicity != '' && $n->idCatPeriodicity == $per->c_periodicity)
									{
										$selectCatPer .= '<option value="'.$per->c_periodicity.'" selected="selected">'.$per->description.'</option>';
									}
									elseif($n->idCatPeriodicity == '' && $n->workerData->count() > 0 && $n->workerData->first()->periodicity == $per->c_periodicity)
									{
										$selectCatPer .= '<option value="'.$per->c_periodicity.'" selected="selected">'.$per->description.'</option>';	
									}
									else
									{
										$selectCatPer .= '<option value="'.$per->c_periodicity.'">'.$per->description.'</option>';
									}
								}
								$selectCatPer .= '</select>';
								$body[] = [ "content" => ["kind" =>"components.labels.label", "classEx" => "text-black w-40", "label" => $selectCatPer ]];
							}
							else
							{
								array_push($body, ["content" => ["kind" =>"components.labels.label", "classEx" => "text-black w-40", "label" => $n->idCatPeriodicity != '' ? App\CatPeriodicity::where('c_periodicity',$n->idCatPeriodicity)->first()->description : '']]);
							}
							if($request->status == 2)
							{
								array_push($body, ["content" => ["kind" => "components.inputs.input-text", "attributeEx" => "type=\"text\" name=\"absence[]\" placeholder=\"Ingrese las faltas\" value=\"".$n->absence."\"".' '.$disabledSalary, "classEx" => "w-40"]]);
							}
							else
							{
								array_push($body, ["content" => ["kind" =>"components.labels.label", "classEx" => "text-black w-40", "label" => $n->absence!= '' ? $n->absence : '---']]);
							}
							if($request->status == 2)
							{
								array_push($body, ["content" => ["kind" => "components.inputs.input-text", "attributeEx" => "type=\"text\" name=\"extraHours[]\" placeholder=\"Ingrese las horas extras\" value=\"".$n->extra_hours."\"".' '.$disabledSalary, "classEx" => "w-40"]]);
							}
							else 
							{
								array_push($body, ["content" => ["kind" =>"components.labels.label", "classEx" => "text-black w-40", "label" => $n->extra_hours!= '' ? $n->extra_hours : '---' ]]);
							}
							
							if($request->status == 2)
							{
								array_push($body, ["content" => ["kind" => "components.inputs.input-text", "attributeEx" => "type=\"text\" name=\"holidays[]\" placeholder=\"Ingrese los días festivos\" value=\"".$n->holidays."\"".' '.$disabledSalary, "classEx" => "w-40"]]);
							}
							else 
							{
								array_push($body, ["content" => ["kind" =>"components.labels.label", "classEx" => "text-black w-40", "label" => $n->holidays!= '' ? $n->holidays : '---' ]]);
							}
							if($request->status == 2)
							{
								array_push($body, ["content" => ["kind" => "components.inputs.input-text", "attributeEx" => "type=\"text\" name=\"sundays[]\" placeholder=\"Ingrese los domingos trabajados\" value=\"".$n->sundays."\"".' '.$disabledSalary, "classEx" => "w-40"]]);
							}
							else 
							{
								array_push($body, ["content" => ["kind" =>"components.labels.label", "classEx" => "text-black w-40", "label" => $n->sundays!= '' ? $n->sundays : '---' ]]);
							}
							if($request->status == 2)
							{
								array_push($body, ["content" => ["kind" => "components.inputs.input-text", "attributeEx" => "type=\"text\" name=\"loan_perception[]\" placeholder=\"Ingrese el préstamo\" value=\"".$n->loan_perception."\"".' '.$disabledSalary, "classEx" => "w-40"]]);
							}
							else
							{
								array_push($body, ["content" => ["kind" =>"components.labels.label", "classEx" => "text-black w-40", "label" => $n->loan_perception!= '' ? $n->loan_perception : '---']]);
							}
							if($request->status == 2)
							{
								array_push($body, ["content" => ["kind" => "components.inputs.input-text", "attributeEx" => "type=\"text\" name=\"loan_retention[]\" placeholder=\"Ingrese el préstamo\" value=\"".$n->loan_retention."\"".' '.$disabledSalary, "classEx" => "w-40"]]);
							}
							else
							{
								array_push($body, ["content" => ["kind" =>"components.labels.label", "classEx" => "text-black w-40", "label" => $n->loan_retention!= '' ? $n->loan_retention : '---']]);
							}
							if($request->status == 2)
							{
								array_push($body, 
								[
									"content" => 
									[
										["kind" => "components.inputs.input-text", "attributeEx" => "type=\"text\" name=\"other_retention_amount[]\" placeholder=\"Ingrese otro\" value=\"".$n->other_retention_amount."\"".' '.$disabledSalary, "classEx" => "w-40"],
										["kind" => "components.inputs.input-text", "attributeEx" => "type=\"text\" name=\"other_retention_concept[]\" placeholder=\"Ingrese otro\" value=\"".$n->other_retention_concept."\"".' '.$disabledSalary, "classEx" => "hidden"]
									]
								]);
							}
							else
							{
								array_push($body, ["content" => ["kind" =>"components.labels.label", "classEx" => "text-black w-40", "label" => $n->loan_retention!= '' ? $n->loan_retention : '---']]);
							}
							if($request->status == 2)
							{
								$selectPayment	= "";
								$selectPayment	.= '<select class="border rounded py-2 px-3 m-px w-full paymentWay removeselect" name="paymentWay[]" data-validation="required" '.$disabledSalary.'>';
								foreach(App\PaymentMethod::all() as $p)
								{
									if($n->idpaymentMethod != "" && $n->idpaymentMethod == $p->idpaymentMethod)
									{
										$selectPayment .= '<option value="'.$p->idpaymentMethod.'" selected="selected">'.$p->method.'</option>';
									}
									elseif($paymentWayTemp == $p->idpaymentMethod)
									{ 
										$selectPayment .= '<option value="'.$p->idpaymentMethod.'" selected="selected">'.$p->method.'</option>';	
									}
									else
									{
										$selectPayment .= '<option value="'.$p->idpaymentMethod.'">'.$p->method.'</option>';
									}
								}
								$selectPayment .= '</select>';
								$body[] = [ "content" => ["kind" =>"components.labels.label", "classEx" => "text-black w-40", "label" => $selectPayment ]];
							}
							else
							{
								array_push($body, ["content" => ["kind" =>"components.labels.label", "classEx" => "text-black w-40", "label" => $n->salary->first()->paymentMethod->method]]);
							}
							if($request->status != 2)
							{
								if($n->nominaCFDI()->exists() && \Storage::disk('reserved')->exists('/stamped/'.$n->nominaCFDI->first()->uuid.'.xml'))
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
									array_push($body, ["content" => ["label" => "---"]]);
								}
								if($n->nominaCFDI()->exists() && \Storage::disk('reserved')->exists('/stamped/'.$n->nominaCFDI->first()->uuid.'.pdf'))
								{
									array_push($body, [
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
									array_push($body, ["content" => ["label" => "---"]]);
								}
								$docSalary = '';
								if($n->nominaCFDI()->exists() && $n->payments()->exists() && $n->payments()->first()->documentsPayments()->exists())
								{
									foreach($n->payments->first()->documentsPayments as $pay)
									{
										$docSalary .= '<div class="content">';
										$docSalary .= view('components.buttons.button',[
											"variant" 		=> "dark-red",
											"buttonElement" => "a",
											"attributeEx" 	=> "target=\"_blank\" href=\"".asset('docs/payments/'.$pay->path)."\"", 
											"label" 		=> "PDF"
										])->render();
										$docSalary .= "</div>";
									}
								}
								else
								{
									$docSalary = "---";
								}
								$body[] = [ "content" => ["kind" =>"components.labels.label", "classEx" => "text-black w-40", "label" => $docSalary ]];
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
											"attributeEx"	=> "title=\"Editar datos personales\" type=\"button\" data-toggle=\"modal\" data-target=\"#myModal\" ".$disabledSalary
										],
										[
											"kind" 			=> "components.buttons.button",
											"variant"		=> "primary",
											"classEx"		=> "btn-edit-paymentway",
											"label"			=> "<span class=\"icon-pencil\"></span>",
											"attributeEx"	=> "title=\"Editar forma de pago\" type=\"button\" data-toggle=\"modal\" data-target=\"#myModal\" ".$disabledSalary
										],
										[
											"kind" 			=> "components.buttons.button",
											"variant"		=> "red",
											"classEx"		=> "btn-delete-employee",
											"label"			=> "<span class=\"icon-x\"></span>",
											"attributeEx"	=> "title=\"Eliminar\" type=\"button\" ".$disabledSalary
										]
									]
								]);
							}
							$modelBody[] = $body;
						}
					@endphp
					@component("components.tables.table",[ "modelHead" => $modelHead, "modelBody" => $modelBody]) 
						@slot("attributeEx")
							id="table"
						@endslot
						@slot("attributeExBody")
							id="body-payroll"
						@endslot
						@slot('classExBody')
							request-validate
						@endslot
					@endcomponent
				@break
				@case('002')
					@if($request->status != 2)
						@component("components.buttons.button", ["variant" => "success"])
							@slot("classEx")
								mb-2
								float-right
							@endslot
							@slot('buttonElement')
								a
							@endslot
							@slot("attributeEx")
								href={{route('nomina.export.bonus',$request->folio)}}
							@endslot
							<span>Exportar a Excel</span> <span class="icon-file-excel"></span>
						@endcomponent
					@endif
					@if($request->status == 2)
						@component("components.buttons.button", ["variant" => "success"])
							@slot("classEx")
								mb-2
								float-right
							@endslot
							@slot('buttonElement')
								a
							@endslot
							@slot("attributeEx")
								href={{route('nomina.export.employee',$request->folio)}}
							@endslot
							<span>Exportar a Excel</span> <span class="icon-file-excel"></span>
						@endcomponent
					@endif
					@php
						$array_b	= session('errors_bonus');
						$modelBody 	= [];
						$body 		= [];
						foreach(App\NominaEmployee::join('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')->where('nomina_employees.idnomina',$request->nominasReal->first()->idnomina)->where('nomina_employees.visible',1)->orderBy('real_employees.last_name','ASC')->orderBy('real_employees.scnd_last_name','ASC')->orderBy('real_employees.name','ASC')->select('nomina_employees.*')->get() as $n)
						{
							$modelHead	=
							[
								[
									["value" =>  "Nombre del Empleado", "classEx" => "sticky inset-x-0 bg-orange-500"],
									["value" =>  "Días para aguinaldo"],
									["value" =>  "Sueldo Neto"],
									["value" =>  "Periodicidad"],
									["value" =>  "Forma de pago"],
								]
							];
							if($request->status != 2)
							{
								array_push($modelHead[0], ["value" =>  "XML"]);
								array_push($modelHead[0], ["value" =>  "PDF"]);
								array_push($modelHead[0], ["value" =>  "Documentos de pago"]);
							}
							if($request->status == 2)
							{
								array_push($modelHead[0], ["value" =>  "Acciones"]);
							}
							$paymentWayTemp		= 2;
							$accountTemp		= '';
							$accountBeneficiary	= '';
							if($n->workerData->first()->paymentWay != '' && $n->workerData->first()->paymentWay == 1 && $n->employee->first()->bankData()->where('visible',1)->where('type',1)->exists())
							{
								$paymentWayTemp	= 1;
								$accountTemp	= $n->employee->first()->bankData->where('visible',1)->where('type',1)->last()->id;	
							}
							elseif($n->workerData->first()->paymentWay != '' && $n->workerData->first()->paymentWay == 3)
							{
								$paymentWayTemp	= 3;
							}
							if ($n->employee->first()->bankData()->where('visible',1)->where('type',2)->exists()) 
							{
								$accountBeneficiary = $n->employee->first()->bankData->where('visible',1)->where('type',2)->last()->id;	
							}
							$disabledBonus = '';
							if(isset($globalRequests))
							{
								$disabledBonus = 'disabled';
							}
							$redClass = "";
							if (isset($array_b) && in_array($n->idnominaEmployee,$array_b))
							{
								$redClass = "tr-red-sticky";
							}

							$body = 
							[	"classEx" => "tr_payroll ".$redClass,
								[
									"classEx" => "sticky inset-x-0 bg-white",
									"content" => 
									[
										["kind" => "components.inputs.input-text", "classEx" => "idnominaEmployee", "attributeEx" => "type=\"hidden\" name=\"idnominaEmployee_request[]\" value=\"".$n->idnominaEmployee."\""],
										["kind" => "components.inputs.input-text", "classEx" => "idrealEmployee", "attributeEx" => "type=\"hidden\" name=\"idrealEmployee[]\" value=\"".$n->idrealEmployee."\""],
										["kind" => "components.inputs.input-text", "classEx" => "idworkingData", "attributeEx" => "type=\"hidden\" name=\"idworkingData[]\" value=\"".$n->idworkingData."\""],
										["kind" => "components.inputs.input-text", "classEx" => "idemployeeAccount", "attributeEx" => "type=\"hidden\" name=\"idemployeeAccount[]\" value=\"".$accountTemp."\""],
										["kind" => "components.inputs.input-text", "classEx" => "idAccountBeneficiary", "attributeEx" => "type=\"hidden\" name=\"idAccountBeneficiary[]\" value=\"".$accountBeneficiary."\""],
										[
											"kind"		=> "components.labels.label",
											"classEx"	=> "md:p-2 p-0 w-40",
											"label" 	=> $n->employee->first()->last_name.' '.$n->employee->first()->scnd_last_name.' '.$n->employee->first()->name
										]
									]
								]
							];
							if($request->status == 2)
							{
								$value = '';
								if($n->day_bonus != '' || $n->day_bonus != null)
								{
									$value = $n->day_bonus;
								}
								else
								{
									$value = '365';
								}
								array_push($body, ["content" => [["kind" => "components.inputs.input-text", "attributeEx" => "type=\"text\" name=\"day_bonus[]\" placeholder=\"Ingrese los días para aguinaldo\" data-validation=\"required\" value=\"".$value."\"".' '.$disabledBonus, "classEx" => "w-40"]]]);
							}
							else
							{
								array_push($body, ["content" => [["kind" => "components.labels.label", "classEx" => "w-40", "label" => $n->day_bonus]]]);
							}
							if($request->status == 2)
							{
								$valueTotal = $n->total!="" ? $n->total : $n->workerData->first()->netIncome;
								array_push($body, ["content" => [["kind" => "components.inputs.input-text", "attributeEx" => "type=\"text\"  data-validation=\"required\" name=\"netIncome[]\" placeholder=\"Ingrese el sueldo neto\" value=\"".$valueTotal."\"".' '.$disabledBonus, "classEx" => "w-40"]]]);
							}
							else
							{
								array_push($body, ["content" => [["kind" => "components.labels.label", "classEx" => "w-40", "label" => '$ '.number_format($n->total,2)]]]);
							}
							if($request->status == 2)
							{
								$selectCatPer	= "";
								$selectCatPer	.= '<select class="border rounded py-2 px-3 m-px w-40 periodicity" name="periodicity[]" data-validation="required" '.$disabledBonus.'>';
								foreach(App\CatPeriodicity::whereIn('c_periodicity',['02','04','05'])->get() as $per)
								{
									if($n->idCatPeriodicity != '' && $n->idCatPeriodicity == $per->c_periodicity)
									{
										$selectCatPer .= '<option value="'.$per->c_periodicity.'" selected="selected">'.$per->description.'</option>';
									}
									elseif($n->workerData->count() > 0 && $n->workerData->first()->periodicity == $per->c_periodicity)
									{
										$selectCatPer .= '<option value="'.$per->c_periodicity.'" selected="selected">'.$per->description.'</option>';
									}
									else
									{
										$selectCatPer .= '<option value="'.$per->c_periodicity.'">'.$per->description.'</option>';
									}
								}
								$selectCatPer .= '</select>';
								$body[] = [ "content" => ["kind" => "components.labels.label", "classEx" => "w-40", "label" => $selectCatPer ]];
							}
							else
							{
								array_push($body, ["content" => [["kind" => "components.labels.label", "classEx" => "w-40", "label" => $n->idCatPeriodicity != '' ? App\CatPeriodicity::where('c_periodicity',$n->idCatPeriodicity)->first()->description : '']]]);
							}
							if($request->status == 2)
							{
								$selectPayment	= "";
								$selectPayment	.= '<select class="border rounded py-2 px-3 m-px w-40 paymentWay removeselect" name="paymentWay[]" data-validation="required" '.$disabledBonus.'>';
								foreach(App\PaymentMethod::all() as $p)
								{
									if($n->idpaymentMethod != "" && $n->idpaymentMethod == $p->idpaymentMethod)
									{
										$selectPayment .= '<option value="'.$p->idpaymentMethod.'" selected="selected">'.$p->method.'</option>';
									}
									elseif($paymentWayTemp == $p->idpaymentMethod)
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
								array_push($body, ["content" => [["kind" => "components.labels.label", "classEx" => "w-40", "label" => $n->bonus->first()->idpaymentMethod != "" ? $n->bonus->first()->paymentMethod->method : 'Sin forma de pago']]]);
							}
							if($request->status != 2)
							{
								if($n->nominaCFDI()->exists() && \Storage::disk('reserved')->exists('/stamped/'.$n->nominaCFDI->first()->uuid.'.xml'))
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
									array_push($body, ["content" => [["label" => "---"]]]);
								}
								if($n->nominaCFDI()->exists() && \Storage::disk('reserved')->exists('/stamped/'.$n->nominaCFDI->first()->uuid.'.pdf'))
								{
									array_push($body, [
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
									array_push($body, ["content" => [["label" => "---"]]]);
								}
								$docBonus = '';
								if($n->nominaCFDI()->exists() && $n->payments()->first()->documentsPayments()->exists())
								{
									foreach($n->payments->first()->documentsPayments as $pay)
									{
										$docBonus .= '<div class="content">';
										$docBonus .= view('components.buttons.button',[
											"variant" 		=> "dark-red",
											"buttonElement" => "a",
											"attributeEx" 	=> "target=\"_blank\" href=\"".asset('docs/payments/'.$pay->path)."\"",
											"label" 		=> "PDF"	
										])->render();
										$docBonus .= "</div>";
									}
								}
								else
								{
									$docBonus = '---';
								}
								$body[] = [ "content" => [ "label" => $docBonus ]];
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
											"attributeEx"	=> "title=\"Editar datos personales\" type=\"button\" data-toggle=\"modal\" data-target=\"#myModal\" ".$disabledBonus
										],
										[
											"kind" 			=> "components.buttons.button",
											"variant"		=> "primary",
											"classEx"		=> "btn-edit-paymentway",
											"label"			=> "<span class=\"icon-pencil\"></span>",
											"attributeEx"	=> "title=\"Editar forma de pago\" type=\"button\" data-toggle=\"modal\" data-target=\"#myModal\" ".$disabledBonus
										],
										[
											"kind" 			=> "components.buttons.button",
											"variant"		=> "red",
											"classEx"		=> "btn-delete-employee",
											"label"			=> "<span class=\"icon-x\"></span>",
											"attributeEx"	=> "title=\"Eliminar\" type=\"button\" ".$disabledBonus
										]
									]
								]);
							}
							$modelBody[] = $body;
						}
					@endphp
					@component("components.tables.table",["modelHead" => $modelHead, "modelBody" => $modelBody]) @slot("attributeEx") id="table"  @endslot @slot("attributeExBody") id="body-payroll" @endslot @endcomponent
				@break
				@case('003')
					@if($request->status != 2)
						@component("components.buttons.button", [ "variant" => "success" ])
							@slot('attributeEx')
								href="{{route('nomina.export.settlement',$request->folio)}}"
							@endslot
							@slot('buttonElement')
								a
							@endslot
							@slot('classEx')
								float-right 
								mb-2
							@endslot
							<span>Exportar a Excel</span> <span class="icon-file-excel"></span>
						@endcomponent
					@endif
					@if($request->status == 2)
						@component("components.buttons.button", [ "variant" => "success" ])
							@slot('attributeEx')
								href="{{route('nomina.export.employee',$request->folio)}}"
							@endslot
							@slot('buttonElement')
								a
							@endslot
							@slot('classEx')
								float-right 
								mb-2
							@endslot
							<span>Exportar a Excel</span> <span class="icon-file-excel"></span>
						@endcomponent
					@endif
					@php
						$array_set = session('errors_settlement');
						$modelBody = [];
						foreach(App\NominaEmployee::join('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')->where('nomina_employees.idnomina',$request->nominasReal->first()->idnomina)->where('nomina_employees.visible',1)->orderBy('real_employees.last_name','ASC')->orderBy('real_employees.scnd_last_name','ASC')->orderBy('real_employees.name','ASC')->select('nomina_employees.*')->get() as $n)
						{
							$modelHead = 
							[
								[
									["value" => "Nombre del Empleado", "classEx" => "sticky inset-x-0 bg-orange-500"],
									["value" => "Sueldo Neto"],
									["value" => "Periodicidad"],
									["value" => "Fecha de baja"],
									["value" => "Días trabajados"],
									["value" => "Otras percepciones"],
									["value" => "Otras retenciones"],
									["value" => "Forma de pago"],
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
							$paymentWayTemp		= 2;
							$accountTemp		= '';
							$accountBeneficiary	= '';
							if($n->workerData->first()->paymentWay != '' && $n->workerData->first()->paymentWay == 1 && $n->employee->first()->bankData()->where('visible',1)->where('type',1)->exists())
							{
								$paymentWayTemp	= 1;
								$accountTemp	= $n->employee->first()->bankData->where('visible',1)->where('type',1)->last()->id;	
							}
							elseif($n->workerData->first()->paymentWay != '' && $n->workerData->first()->paymentWay == 3)
							{
								$paymentWayTemp	= 3;
							}
							if ($n->employee->first()->bankData()->where('visible',1)->where('type',2)->exists()) 
							{
								$accountBeneficiary = $n->employee->first()->bankData->where('visible',1)->where('type',2)->last()->id;	
							}
							$disabledSettlement = '';
							if(isset($globalRequests))
							{
								$disabledSettlement = 'disabled';
							}
							$newDateDown	= $n->down_date != '' ? Carbon\Carbon::createFromFormat('Y-m-d',$n->down_date)->format('d-m-Y') : '';
							$redClass		= "";
							if (isset($array_set) && in_array($n->idnominaEmployee,$array_set))
							{
								$redClass	= "tr-red-sticky";
							}
						
							$body = 
							[	"classEx" => "tr_payroll ".$redClass,
								[
									"classEx" => "sticky inset-x-0 bg-white",
									"content" =>
									[
										[
											"kind"		=> "components.labels.label",
											"classEx"	=> "md:p-2 p-0 w-40",
											"label" 	=> $n->employee->first()->last_name.' '.$n->employee->first()->scnd_last_name.' '.$n->employee->first()->name
										],
										["kind" => "components.inputs.input-text", "attributeEx" => "type=\"hidden\" name=\"idnominaEmployee_request[]\" value=\"".$n->idnominaEmployee."\"", "classEx" => "idnominaEmployee"],
										["kind" => "components.inputs.input-text", "attributeEx" => "type=\"hidden\" name=\"idrealEmployee[]\" value=\"".$n->idrealEmployee."\"", "classEx" => "idrealEmployee"],
										["kind" => "components.inputs.input-text", "attributeEx" => "type=\"hidden\" name=\"idworkingData[]\" value=\"".$n->idworkingData."\"", "classEx" => "idworkingData"],
										["kind" => "components.inputs.input-text", "attributeEx" => "type=\"hidden\" name=\"idemployeeAccount[]\" value=\"".$accountTemp."\"", "classEx" => "idemployeeAccount"],
										["kind" => "components.inputs.input-text", "attributeEx" => "type=\"hidden\" name=\"idAccountBeneficiary[]\" value=\"".$accountBeneficiary."\"", "classEx" => "idAccountBeneficiary"],
									]
								]
							];
							if($request->status == 2)
							{
								$value = $n->total != "" ? $n->total : $n->workerData->first()->netIncome;
								array_push($body, ["content" =>[["kind" => "components.inputs.input-text", "attributeEx" => "type=\"text\" name=\"netIncome[]\" placeholder=\"Ingrese el sueldo neto\" data-validation=\"required\" value=\"".$value."\"".' '.$disabledSettlement, "classEx" => "w-40"]]]);
							}
							else
							{
								array_push($body, ["content" =>[["kind" => "compoennts.labels.label", "classEx" => "w-40", "label" => '$ '.number_format($n->total,2),]]]);
							}
							if($request->status == 2)
							{
								$selectCatPer	= "";
								$selectCatPer	.= '<select class="border rounded py-2 px-3 m-px w-40 periodicity" name="periodicity[]" data-validation="required" '.$disabledSettlement.'>';
								foreach(App\CatPeriodicity::whereIn('c_periodicity',['02','04','05'])->get() as $per)
								{
									if($n->idCatPeriodicity != '' && $n->idCatPeriodicity == $per->c_periodicity)
									{
										$selectCatPer .= '<option value="'.$per->c_periodicity.'" selected="selected">'.$per->description.'</option>';
									}
									elseif($n->workerData->count() > 0 && $n->workerData->first()->periodicity == $per->c_periodicity)
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
								array_push($body, ["content"=>[["label" => $n->idCatPeriodicity != '' ? App\CatPeriodicity::where('c_periodicity',$n->idCatPeriodicity)->first()->description : '', "classEx" => "w-40",]]]);
							}
							if($request->status == 2)
							{
								array_push($body, ["content" => [["kind" => "components.inputs.input-text", "attributeEx" => "type=\"text\" name=\"down_date[]\" data-validation=\"required\" placeholder=\"Ingrese la fecha\" readonly=\"readonly\" value=\"".$newDateDown."\"".' '.$disabledSettlement, "classEx" => "datepicker down_date w-40"]]]);
							}
							else
							{
								array_push($body, ["content"=>["kind" => "components.labels.label", "classEx" => "text-black w-40", "label" => $newDateDown]]);
							}
							if($request->status == 2)
							{
								$valueWorkedDay = $n->worked_days != "" ? $n->worked_days : 365;
								array_push($body, ["content" => [["kind" => "components.inputs.input-text", "attributeEx" => "type=\"text\" name=\"worked_days[]\" placeholder=\"Ingrese los días trabajados\" value=\"".$valueWorkedDay."\"".' '.$disabledSettlement, "classEx" => "w-40"]]]);
							}
							else
							{
								array_push($body, ["content"=>[["kind" => "components.labels.label", "classEx" => "text-black w-40", "label" => $n->worked_days]]]);
							}
							if($request->status == 2)
							{
								$valuePerception = $n->other_perception != "" ? $n->other_perception : 0;
								array_push($body, ["content" => [["kind" => "components.inputs.input-text", "attributeEx" => "type=\"text\" name=\"other_perception[]\" placeholder=\"Ingrese otra percepción\" value=\"".$valuePerception."\"".' '.$disabledSettlement, "classEx" => "w-40"]]]);
							}
							else
							{
								array_push($body, ["content"=>[["kind" => "components.labels.label", "classEx" => "text-black w-40", "label" => $n->other_perception]]]);
							}
							if($request->status == 2)
							{
								$valueRetention = $n->other_retention != "" ? $n->other_retention : 0;
								array_push($body, ["content" => [["kind" => "components.inputs.input-text", "attributeEx" => "type=\"text\" name=\"other_retention[]\" placeholder=\"Ingrese otra retención\" value=\"".$valueRetention."\"".' '.$disabledSettlement, "classEx" => "w-40"]]]);
							}
							else
							{
								array_push($body, ["content"=>[["kind" => "components.labels.label", "classEx" => "text-black w-40", "label" => $n->other_retention]]]);
							}
							if($request->status == 2)
							{
								$selectPayment	= "";
								$selectPayment	.= '<select class="border rounded py-2 px-3 m-px w-40 paymentWay removeselect" name="paymentWay[]" data-validation="required" '.$disabledSettlement.'>';
								foreach(App\PaymentMethod::all() as $p)
								{
									if($n->idpaymentMethod != "" && $n->idpaymentMethod == $p->idpaymentMethod)
									{
										$selectPayment .= '<option value="'.$p->idpaymentMethod.'" selected="selected">'.$p->method.'</option>';
									}
									elseif($paymentWayTemp == $p->idpaymentMethod)
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
								array_push($body, ["content"=>[["kind" => "components.labels.label", "classEx" => "text-black w-40", "label" => $n->liquidation->first()->idpaymentMethod != "" ? $n->liquidation->first()->paymentMethod->method : "Sin forma de pago" ]]]);
							}
							if($request->status != 2)
							{
								if($n->nominaCFDI()->exists() && \Storage::disk('reserved')->exists('/stamped/'.$n->nominaCFDI->first()->uuid.'.xml'))
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
									array_push($body, ["content" => [["label" => "---"]]]);
								}
								if($n->nominaCFDI()->exists() && \Storage::disk('reserved')->exists('/stamped/'.$n->nominaCFDI->first()->uuid.'.pdf'))
								{
									array_push($body, [
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
									array_push($body, ["content" => [["label" => "---"]]]);
								}
								$docSettlement = '';
								if($n->nominaCFDI()->exists() && $n->payments()->first()->documentsPayments()->exists())
								{
									foreach($n->payments->first()->documentsPayments as $pay)
									{
										$docSettlement .= '<div class="content">';
										$docSettlement .= view('components.buttons.button',[
											"variant" 		=> "dark-red",
											"buttonElement" => "a",
											"attributeEx" 	=> "target=\"_blank\" href=\"".asset('docs/payments/'.$pay->path)."\"",
											"label" 		=> "PDF"
										])->render();
										$docSettlement .= "</div>";
									}	
								}
								else
								{
									$docSettlement = "---";
								}
								$body[] = [ "content" => [ "label" => $docSettlement ]];
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
											"attributeEx"	=> "title=\"Editar datos personales\" type=\"button\" data-toggle=\"modal\" data-target=\"#myModal\" ".$disabledSettlement
										],
										[
											"kind" 			=> "components.buttons.button",
											"variant"		=> "primary",
											"classEx"		=> "btn-edit-paymentway",
											"label"			=> "<span class=\"icon-pencil\"></span>",
											"attributeEx"	=> "title=\"Editar forma de pago\" type=\"button\" data-toggle=\"modal\" data-target=\"#myModal\" ".$disabledSettlement
										],
										[
											"kind" 			=> "components.buttons.button",
											"variant"		=> "red",
											"classEx"		=> "btn-delete-employee",
											"label"			=> "<span class=\"icon-x\"></span>",
											"attributeEx"	=> "title=\"Eliminar\" type=\"button\" ".$disabledSettlement
										]
									]
								]);
							}
							$modelBody[] = $body;
						}
					@endphp
					@component("components.tables.table",["modelHead" => $modelHead, "modelBody" => $modelBody]) @slot("attributeEx") id="table"  @endslot @slot("attributeExBody") id="body-payroll" @endslot @endcomponent
				@break
				@case('004')
					@if($request->status != 2)
						@component('components.buttons.button', [ "variant" => "success"])
							@slot('classEx')
								float-right
								mb-2
							@endslot
							@slot('buttonElement')
								a
							@endslot
							@slot('attributeEx')
								href="{{ route('nomina.export.liquidation',$request->folio) }}"
							@endslot
							<span>Exportar a Excel</span> <span class='icon-file-excel'></span>
						@endcomponent
					@endif
					@if($request->status == 2)
						@component('components.buttons.button', [ "variant" => "success"])
							@slot('classEx')
								float-right
								mb-2
							@endslot
							@slot('buttonElement')
								a
							@endslot
							@slot('attributeEx')
								href="{{ route('nomina.export.employee',$request->folio) }}"
							@endslot
							<span>Exportar a Excel</span> <span class='icon-file-excel'></span>
						@endcomponent
					@endif
					@php
						$array_sett = session('errors_settlement');
						$body		= [];
						$modelBody	= [];
						foreach(App\NominaEmployee::join('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')->where('nomina_employees.idnomina',$request->nominasReal->first()->idnomina)->where('nomina_employees.visible',1)->orderBy('real_employees.last_name','ASC')->orderBy('real_employees.scnd_last_name','ASC')->orderBy('real_employees.name','ASC')->select('nomina_employees.*')->get() as $n)
						{
							$modelHead	=
							[
								[
									["value" => "Nombre del Empleado", "classEx" => "sticky inset-x-0 bg-orange-500"],
									["value" => "Sueldo Neto"],
									["value" => "Periodicidad"],
									["value" => "Fecha de baja"],
									["value" => "Días trabajados"],
									["value" => "Otras percepciones"],
									["value" => "Otras retenciones"],
									["value" => "Forma de pago"],
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
							$paymentWayTemp		= 2;
							$accountTemp		= '';
							$accountBeneficiary	= '';
							if($n->workerData->first()->paymentWay != '' && $n->workerData->first()->paymentWay == 1 && $n->employee->first()->bankData()->where('visible',1)->where('type',1)->exists())
							{
								$paymentWayTemp	= 1;
								$accountTemp	= $n->employee->first()->bankData->where('visible',1)->where('type',1)->last()->id;	
							}
							elseif($n->workerData->first()->paymentWay != '' && $n->workerData->first()->paymentWay == 3)
							{
								$paymentWayTemp	= 3;
							}
							if ($n->employee->first()->bankData()->where('visible',1)->where('type',2)->exists()) 
							{
								$accountBeneficiary = $n->employee->first()->bankData->where('visible',1)->where('type',2)->last()->id;	
							}
							$disabledLiquidation = '';
							if(isset($globalRequests))
							{
								$disabledLiquidation = 'disabled';
							}
							$newDateDown	= $n->down_date != '' ? Carbon\Carbon::createFromFormat('Y-m-d',$n->down_date)->format('d-m-Y') : '';
							$redClass		= "";
							if (isset($array_sett) && in_array($n->idnominaEmployee,$array_sett))
							{
								$redClass		= "tr-red-sticky";
							}

							$body = [ "classEx" => "tr_payroll ".$redClass,
								[
									"classEx" => "sticky inset-x-0 white",
									"content" => 
									[
										[
											"kind" 			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"hidden\" name=\"idnominaEmployee_request[]\" value=\"".$n->idnominaEmployee."\"",
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
											"kind" 			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"hidden\" name=\"idemployeeAccount[]\" value=\"".$accountTemp."\"",
											"classEx"		=> "idemployeeAccount"
										],
										[
											"kind" 			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"hidden\" name=\"idAccountBeneficiary[]\" value=\"".$accountBeneficiary."\"",
											"classEx"		=> "idAccountBeneficiary"
										],
										[
											"kind"		=> "components.labels.label",
											"classEx"	=> "md:p-2 p-0 w-40",
											"label"		=> $n->employee->first()->last_name.' '.$n->employee->first()->scnd_last_name.' '.$n->employee->first()->name
										]
									]
								]
							];
							if($request->status == 2)
							{
								$varNetIncome = $n->total != "" ? $n->total : $n->workerData->first()->netIncome;
								array_push($body, [
									"content" =>
									[
										[
											"kind"			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"text\" name=\"netIncome[]\" placeholder=\"Ingrese el sueldo neto\" data-validation=\"required\" value=\"".$varNetIncome."\"".' '.$disabledLiquidation, "classEx"	=> "w-40", 
										]
									]
								]);
							}
							else
							{
								array_push($body, [ "content" => [["kind" => "components.labels.label", "classEx"	=> "text-black w-40", "label" => '$ '.number_format($n->total,2) ]]]);
							}
							if($request->status == 2)
							{
								$selectCatPer	= "";
								$selectCatPer	.= '<select class="border rounded py-2 px-3 m-px w-40 periodicity" name="periodicity[]" data-validation="required" '.$disabledLiquidation.'>';
								foreach(App\CatPeriodicity::whereIn('c_periodicity',['02','04','05'])->get() as $per)
								{
									if($n->idCatPeriodicity != '' && $n->idCatPeriodicity == $per->c_periodicity)
									{
										$selectCatPer .= '<option value="'.$per->c_periodicity.'" selected="selected">'.$per->description.'</option>';
									}
									elseif($n->workerData->count() > 0 && $n->workerData->first()->periodicity == $per->c_periodicity)
									{
										$selectCatPer .= '<option value="'.$per->c_periodicity.'" selected="selected">'.$per->description.'</option>';	 
									}
									else
									{
										$selectCatPer .= '<option value="'.$per->c_periodicity.'">'.$per->description.'</option>';
									}
								}
								$selectCatPer .= '</select>';
								$body[] = [ "content" => ["kind" => "components.labels.label", "classEx"	=> "text-black w-40", "label" => $selectCatPer ]];
							}
							else
							{
								array_push($body, [
									"content" => 
									[
										[
											"kind" => "components.labels.label",
											"classEx"	=> "text-black w-40", 
											"label" => $n->idCatPeriodicity != '' ? App\CatPeriodicity::where('c_periodicity',$n->idCatPeriodicity)->first()->description : ''
										]
									]
								]);
							}
							if($request->status == 2)
							{
								array_push($body, [
									"content" =>
									[
										[
											"kind"			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"text\" name=\"down_date[]\" placeholder=\"Ingrese la fecha\" data-validation=\"required\" readonly=\"readonly\" value=\"".$newDateDown."\"".' '.$disabledLiquidation,
											"classEx"		=> "datepicker down_date w-40"
										]
									]
								]);
							}
							else
							{
								array_push($body,[ "content" => [["kind" => "components.labels.label", "classEx"	=> "text-black w-40", "label" => $newDateDown ]]]);
							}
							if($request->status == 2)
							{
								$varWorked = $n->worked_days!="" ? $n->worked_days : 365;
								array_push($body, [
									"content" =>
									[
										[
											"kind"			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"text\" name=\"worked_days[]\" placeholder=\"Ingrese los días trabajados\" value=\"".$varWorked."\"".' '.$disabledLiquidation,
											"classEx"		=> "w-40"
										]
									]
								]);
							}
							else
							{
								array_push($body,[ "content" => [["kind" => "components.labels.label", "classEx"	=> "text-black w-40", "label" => $n->worked_days ]]]);
							}
							if($request->status == 2)
							{
								$varOther = $n->other_perception != "" ? $n->other_perception  : 0;
								array_push($body, [
									"content" =>
									[
										[
											"kind"			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"text\" name=\"other_perception[]\" placeholder=\"Ingrese otra percepción\" value=\"".$varOther."\"".' '.$disabledLiquidation,
											"classEx"		=>	"w-40"
										]
									]
								]);
							}
							else
							{
								array_push($body,[ "content" => [["kind" => "components.labels.label", "classEx"	=> "text-black w-40", "label" => $n->other_perception ]]]);
							}
							if($request->status == 2)
							{
								$valueRetention = $n->other_retention != "" ? $n->other_retention : 0 ;
								array_push($body, [
									"content" =>
									[
										[
											"kind"			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"text\" name=\"other_retention[]\" placeholder=\"Ingrese otra retención\" value=\"".$valueRetention."\"".' '.$disabledLiquidation,
											"classEx"		=>	"w-40"
										]
									]
								]);
							}
							else
							{
								array_push($body,[ "content" => [["kind" => "components.labels.label", "classEx"	=> "text-black w-40", "label" => $n->other_retention ]]]);
							}
							if($request->status == 2)
							{
								$selectPayment	= "";
								$selectPayment	.= '<select class="border rounded py-2 px-3 m-px w-40 paymentWay removeselect" name="paymentWay[]" data-validation="required" '.$disabledLiquidation.'>';
								foreach(App\PaymentMethod::all() as $p)
								{
									if($n->idpaymentMethod != "" && $n->idpaymentMethod == $p->idpaymentMethod)
									{
										$selectPayment .= '<option value="'.$p->idpaymentMethod.'" selected="selected">'.$p->method.'</option>';
									}
									elseif($paymentWayTemp == $p->idpaymentMethod)
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
								array_push($body,[ 
									"content" =>
									[
										[
											"kind" => "components.labels.label",
											"classEx"	=> "text-black w-40",
											"label" => $n->liquidation->first()->idpaymentMethod != "" ? $n->liquidation->first()->paymentMethod->method : "Sin forma pago"
										]
									]
								]);
							}
							if($request->status != 2)
							{
								if($n->nominaCFDI()->exists() && \Storage::disk('reserved')->exists('/stamped/'.$n->nominaCFDI->first()->uuid.'.xml'))
								{
									array_push($body, [
										"content" => 
										[
											[
												"kind"				=> "components.buttons.button",
												"variant" 			=> "success",
												"buttonElement"		=> "a",
												"attributeEx"		=> "alt=\"XML\" title=\"XML\" href=\"".route('bill.stamped.download.xml',$n->nominaCFDI->first()->uuid)."\"",
												"label"				=> "<span class=\"icon-xml\"></span>"
											]
										]
									]);
								}
								else
								{
									array_push($body,[ "content" => [[ "label" => "---" ]]]);
								}
								if($n->nominaCFDI()->exists() && \Storage::disk('reserved')->exists('/stamped/'.$n->nominaCFDI->first()->uuid.'.pdf'))
								{
									array_push($body, [
										"content" => 
										[
											[
												"kind"				=> "components.buttons.button",
												"variant" 			=> "dark-red",
												"buttonElement"		=> "a",
												"attributeEx"		=> "alt=\"PDF\" title=\"PDF\" href=\"".route('bill.stamped.download.pdf',$n->nominaCFDI->first()->uuid)."\"",
												"label"				=> "PDF"
											]
										]
									]);
								}
								else
								{
									array_push($body,[ "content" => [["kind" => "components.labels.label", "classEx" => "text-black w-40", "label" => "---" ]]]);
								}
								$docLiquidation = '';
								if($n->nominaCFDI()->exists() && $n->payments()->first()->documentsPayments()->exists())
								{
									foreach($n->payments->first()->documentsPayments as $pay)
									{
										$docLiquidation .= '<div class="content">';
										$docLiquidation .= view('components.buttons.button',[
											"variant" 			=> "dark-red",
											"buttonElement"		=> "a",
											"attributeEx"		=> "target=\"_blank\" href=\"".asset('docs/payments/'.$pay->path)."\"",
											"label"				=> "PDF"
										])->render();
										$docLiquidation .= "</div>";
									}
								}
								else
								{
									$docLiquidation = "---";
								}
								$body[] = [ "content" => [ "label" => $docLiquidation ]];
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
											"attributeEx"	=> "title=\"Editar datos personales\" type=\"button\" data-toggle=\"modal\" data-target=\"#myModal\" ".$disabledLiquidation
										],
										[
											"kind" 			=> "components.buttons.button",
											"variant"		=> "primary",
											"classEx"		=> "btn-edit-paymentway",
											"label"			=> "<span class=\"icon-pencil\"></span>",
											"attributeEx"	=> "title=\"Editar forma de pago\" type=\"button\" data-toggle=\"modal\" data-target=\"#myModal\" ".$disabledLiquidation
										],
										[
											"kind" 			=> "components.buttons.button",
											"variant"		=> "red",
											"classEx"		=> "btn-delete-employee",
											"label"			=> "<span class=\"icon-x\"></span>",
											"attributeEx"	=> "title=\"Eliminar\" type=\"button\" ".$disabledLiquidation
										]
									]
								]);
							}
							$modelBody[] = $body;	
						}
					@endphp
					@component('components.tables.table', [
						"modelBody" => $modelBody,
						"modelHead" => $modelHead,
					])
						@slot('attributeEx')
							id="table"
						@endslot
						@slot('attributeExBody')
							id="body-payroll"
						@endslot
						@slot('classExBody')
							request-validate
						@endslot
					@endcomponent
				@break
				@case('005')
					@if($request->status != 2)
						@component('components.buttons.button', [ "variant" => "success"])
							@slot('classEx')
								float-right
								mb-2
							@endslot
							@slot('buttonElement')
								a
							@endslot
							@slot('attributeEx')
								href="{{ route('nomina.export.vacationpremium',$request->folio) }}"
							@endslot
							<span>Exportar a Excel</span> <span class='icon-file-excel'></span>
						@endcomponent
					@endif
					@if($request->status == 2)
						@component('components.buttons.button', [ "variant" => "success"])
							@slot('classEx')
								float-right
								mb-2
							@endslot
							@slot('buttonElement')
								a
							@endslot
							@slot('attributeEx')
								href="{{ route('nomina.export.employee',$request->folio) }}"
							@endslot
							<span>Exportar a Excel</span> <span class='icon-file-excel'></span>
						@endcomponent
					@endif
					@php
						$array_h	= session('errors_holidaypremium');
						$body		= [];
						$modelBody	= [];
						
						foreach(App\NominaEmployee::join('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')->where('nomina_employees.idnomina',$request->nominasReal->first()->idnomina)->where('nomina_employees.visible',1)->orderBy('real_employees.last_name','ASC')->orderBy('real_employees.scnd_last_name','ASC')->orderBy('real_employees.name','ASC')->select('nomina_employees.*')->get() as $n)
						{
							$modelHead	=
							[
								[
									["value" => "Nombre del Empleado", "classEx" => "sticky inset-x-0 bg-orange-500"],
									["value" => "Sueldo neto"],
									["value" => "Periodicidad"],
									["value" => "Días trabajados"],
									["value" => "Forma de pago"],
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
							$paymentWayTemp		= 2;
							$accountTemp		= '';
							$accountBeneficiary	= '';
							if($n->workerData->first()->paymentWay != '' && $n->workerData->first()->paymentWay == 1 && $n->employee->first()->bankData()->where('visible',1)->where('type',1)->exists())
							{
								$paymentWayTemp	= 1;
								$accountTemp	= $n->employee->first()->bankData->where('visible',1)->where('type',1)->last()->id;	
							}
							elseif($n->workerData->first()->paymentWay != '' && $n->workerData->first()->paymentWay == 3)
							{
								$paymentWayTemp	= 3;
							}
							if ($n->employee->first()->bankData()->where('visible',1)->where('type',2)->exists()) 
							{
								$accountBeneficiary = $n->employee->first()->bankData->where('visible',1)->where('type',2)->last()->id;	
							}
							$disabledPremiun = '';
							if(isset($globalRequests))
							{
								$disabledPremiun = 'disabled';
							}
							$redClass = "";
							if (isset($array_h) && in_array($n->idnominaEmployee,$array_h))
							{
								$redClass = "tr-red-sticky";
							}

							$body = [ "classEx" => "tr_payroll ".$redClass,
								[
									"classEx" => "sticky inset-x-0 bg-white",
									"content" =>
									[

										[
											"kind" 			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"hidden\" name=\"idnominaEmployee_request[]\" value=\"".$n->idnominaEmployee."\"",
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
											"kind" 			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"hidden\" name=\"idemployeeAccount[]\" value=\"".$accountTemp."\"",
											"classEx"		=> "idemployeeAccount"
										],
										[
											"kind" 			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"hidden\" name=\"idAccountBeneficiary[]\" value=\"".$accountBeneficiary."\"",
											"classEx"		=> "idAccountBeneficiary"
										],
										[
											"kind"		=> "components.labels.label",
											"classEx"	=> "md:p-2 p-0 w-40",
											"label"		=> $n->employee->first()->last_name.' '.$n->employee->first()->scnd_last_name.' '.$n->employee->first()->name
										]
									]
								]	
							];
							if($request->status == 2)
							{
								$varNetIn = $n->total != "" ? $n->total : $n->workerData->first()->netIncome;
								array_push($body, [
									"content" =>
									[
										"kind"			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"text\" name=\"netIncome[]\" placeholder=\"Ingrese el sueldo neto\" data-validation=\"required\" value=\"".$varNetIn."\"".' '.$disabledPremiun,
										"classEx"		=>	"w-40"
									]
								]);
							}
							else
							{
								array_push($body, [
									"content" => 
									[
										"kind"		=> "components.labels.label",
										"classEx"	=> "w-40",
										"label" => '$ '.number_format($n->total,2)
									]
								]);
							}
							if($request->status == 2)
							{
								$selectCatPer	= "";
								$selectCatPer	.= '<select class="border rounded py-2 px-3 m-px w-40 periodicity" name="periodicity[]" data-validation="required" '.$disabledPremiun.'>';
								foreach(App\CatPeriodicity::whereIn('c_periodicity',['02','04','05'])->get() as $per)
								{
									if($n->idCatPeriodicity != '' && $n->idCatPeriodicity == $per->c_periodicity)
									{
										$selectCatPer .= '<option value="'.$per->c_periodicity.'" selected="selected">'.$per->description.'</option>';
									}
									elseif($n->workerData->count()>0 && $n->workerData->first()->periodicity == $per->c_periodicity)
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
								array_push($body, [
									"content" => 
									[
										"kind"		=> "components.labels.label",
										"classEx"	=> "w-40",
										"label"		=> $n->idCatPeriodicity != '' ? App\CatPeriodicity::where('c_periodicity',$n->idCatPeriodicity)->first()->description : ''
									]
								]);
							}
							if($request->status == 2)
							{
								$varWork = $n->worked_days!="" ? $n->worked_days : 365;
								array_push($body, [
									"content" =>
									[
										"kind"			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"text\" name=\"worked_days[]\" placeholder=\"Ingrese los días trabajados\" value=\"".$varWork."\"".' '.$disabledPremiun,
										"classEx"		=> "w-40"
									]
								]);
							}
							else
							{
								array_push($body,[ 
									"content" =>
									[
										"kind"		=> "components.labels.label",
										"classEx"	=> "w-40",
										"label" => $n->worked_days
									]
								]);
							}
							if($request->status == 2)
							{
								$selectPayment	= "";
								$selectPayment	.= '<select class="border rounded py-2 px-3 m-px w-40 paymentWay removeselect" name="paymentWay[]" data-validation="required" '.$disabledPremiun.'>';
								foreach(App\PaymentMethod::all() as $p)
								{
									if($n->idpaymentMethod != "" && $n->idpaymentMethod == $p->idpaymentMethod)
									{
										$selectPayment .= '<option value="'.$p->idpaymentMethod.'" selected="selected">'.$p->method.'</option>';
									}
									elseif($paymentWayTemp == $p->idpaymentMethod)
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
								array_push($body,[ 
									"content" =>
									[
										"kind"		=> "components.labels.label",
										"classEx"	=> "w-40",
										"label" => $n->vacationPremium->first()->idpaymentMethod != "" ? $n->vacationPremium->first()->paymentMethod->method : "Sin forma de pago"
									]
								]);
							}
							if($request->status != 2)
							{
								if($n->nominaCFDI()->exists() && \Storage::disk('reserved')->exists('/stamped/'.$n->nominaCFDI->first()->uuid.'.xml'))
								{
									array_push($body, [
										"content" => 
										[
											"kind"				=> "components.buttons.button",
											"variant" 			=> "success",
											"buttonElement"		=> "a",
											"attributeEx"		=> "alt=\"XML\" title=\"XML\" href=\"".route('bill.stamped.download.xml',$n->nominaCFDI->first()->uuid)."\"",
											"label"				=> "<span class=\"icon-xml\"></span>"
										]
									]);
								}
								else
								{
									array_push($body,[ 
										"content" =>
										[
											"kind"		=> "components.labels.label",
											"classEx"	=> "w-40",
											"label"		=> "---"
										]
									]);
								}
								if($n->nominaCFDI()->exists() && \Storage::disk('reserved')->exists('/stamped/'.$n->nominaCFDI->first()->uuid.'.pdf'))
								{
									array_push($body, [
										"content" => 
										[
											"kind"				=> "components.buttons.button",
											"variant" 			=> "dark-red",
											"buttonElement"		=> "a",
											"attributeEx"		=> "alt=\"PDF\" title=\"PDF\" href=\"".route('bill.stamped.download.pdf',$n->nominaCFDI->first()->uuid)."\"",
											"label"				=> "PDF"
										]
									]);
								}
								else
								{
									array_push($body,[ 
										"content" =>
										[
											"kind"		=> "components.labels.label",
											"classEx"	=> "w-40",
											"label"		=> "---"
										]
									]);
								}
								$docVacationpremium = '';
								if($n->nominaCFDI()->exists() && $n->payments()->first()->documentsPayments()->exists())
								{
									foreach($n->payments->first()->documentsPayments as $pay)
									{
										$docVacationpremium .= '<div class="content">';
										$docVacationpremium .= view('components.buttons.button',[
											"variant" 			=> "dark-red",
											"buttonElement"		=> "a",
											"attributeEx"		=> "target=\"_blank\" href=\"".asset('docs/payments/'.$pay->path)."\"",
											"label"				=> "PDF"
										])->render();
										$docVacationpremium .= "</div>";
									}
								}
								else
								{
									$docVacationpremium = "---";
								}
								$body[] = [ "content" => ["kind" => "components.labels.label", "classEx" => "w-40", "label" => $docVacationpremium ]];
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
											"attributeEx"	=> "title=\"Editar datos personales\" type=\"button\" data-toggle=\"modal\" data-target=\"#myModal\" ".$disabledPremiun
										],
										[
											"kind" 			=> "components.buttons.button",
											"variant"		=> "primary",
											"classEx"		=> "btn-edit-paymentway",
											"label"			=> "<span class=\"icon-pencil\"></span>",
											"attributeEx"	=> "title=\"Editar forma de pago\" type=\"button\" data-toggle=\"modal\" data-target=\"#myModal\" ".$disabledPremiun
										],
										[
											"kind" 			=> "components.buttons.button",
											"variant"		=> "red",
											"classEx"		=> "btn-delete-employee",
											"label"			=> "<span class=\"icon-x\"></span>",
											"attributeEx"	=> "title=\"Eliminar\" type=\"button\" ".$disabledPremiun
										]
									]
								]);
							}
							$modelBody[] = $body; 
						}							
					@endphp
					@component('components.tables.table', [
						"modelBody" => $modelBody,
						"modelHead" => $modelHead,
					])
						@slot('attributeEx')
							id="table"
						@endslot
						@slot('attributeExBody')
							id="body-payroll"
						@endslot
						@slot('classExBody')
							request-validate
						@endslot
					@endcomponent
				@break
				@case('006')
					@if($request->status != 2)
						@component('components.buttons.button', [ "variant" => "success"])
							@slot('classEx')
								float-right
								mb-2
							@endslot
							@slot('buttonElement')
								a
							@endslot
							@slot('attributeEx')
								href="{{ route('nomina.export.profitsharing',$request->folio) }}"
							@endslot
							<span>Exportar a Excel</span> <span class='icon-file-excel'></span>
						@endcomponent
					@endif
					@if($request->status == 2)
						@component('components.buttons.button', [ "variant" => "success"])
							@slot('classEx')
								float-right
								mb-2
							@endslot
							@slot('buttonElement')
								a
							@endslot
							@slot('attributeEx')
								href="{{ route('nomina.export.employee',$request->folio) }}"
							@endslot
							<span>Exportar a Excel</span> <span class='icon-file-excel'></span>
						@endcomponent
					@endif
					@php
						$array_p 	= session('errors_profitsharing');
						$body		= [];
						$modelBody	= [];
						foreach(App\NominaEmployee::join('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')->where('nomina_employees.idnomina',$request->nominasReal->first()->idnomina)->where('nomina_employees.visible',1)->orderBy('real_employees.last_name','ASC')->orderBy('real_employees.scnd_last_name','ASC')->orderBy('real_employees.name','ASC')->select('nomina_employees.*')->get() as $n)
						{
							$modelHead	= 
							[
								[
									["value"=> "Nombre del Empleado", "classEx" => "sticky inset-x-0 bg-orange-500"],
									["value"=> "Sueldo neto"],
									["value"=> "Periodicidad"],
									["value"=> "Días trabajados"],
									["value"=> "Forma de pago"]
								]
							];
							if($request->status != 2)
							{
								array_push($modelHead[0], ["value"=> "XML"]);
								array_push($modelHead[0], ["value"=> "PDF"]);
								array_push($modelHead[0], ["value"=> "Documentos de pago"]);
							}
							if($request->status == 2)
							{
								array_push($modelHead[0], ["value"=> "Acciones"]);
							}
							$paymentWayTemp		= 2;
							$accountTemp		= '';
							$accountBeneficiary	= '';
							if($n->workerData->first()->paymentWay != '' && $n->workerData->first()->paymentWay == 1 && $n->employee->first()->bankData()->where('visible',1)->where('type',1)->exists())
							{
								$paymentWayTemp	= 1;
								$accountTemp	= $n->employee->first()->bankData->where('visible',1)->where('type',1)->last()->id;	
							}
							elseif($n->workerData->first()->paymentWay != '' && $n->workerData->first()->paymentWay == 3)
							{
								$paymentWayTemp	= 3;
							}
							if ($n->employee->first()->bankData()->where('visible',1)->where('type',2)->exists()) 
							{
								$accountBeneficiary = $n->employee->first()->bankData->where('visible',1)->where('type',2)->last()->id;	
							}
							$disabledP = '';
							if(isset($globalRequests))
							{
								$disabledP = 'disabled';
							} 
							$redClass = "";
							if (isset($array_p) && in_array($n->idnominaEmployee,$array_p))
							{
								$redClass = "tr-red-sticky";
							}

							$body =
							[	"classEx" => "tr_payroll ".$redClass,
								[
									"classEx" => "sticky inset-x-0 bg-white",
									"content" =>
									[
										["kind" => "components.inputs.input-text", "attributeEx" => "type=\"hidden\" name=\"idnominaEmployee_request[]\" value=\"".$n->idnominaEmployee."\"", "classEx" => "idnominaEmployee"],
										["kind" => "components.inputs.input-text", "attributeEx" => "type=\"hidden\" name=\"idrealEmployee[]\" value=\"".$n->idrealEmployee."\"", "classEx" => "idrealEmployee"],
										["kind" => "components.inputs.input-text", "attributeEx" => "type=\"hidden\" name=\"idworkingData[]\" value=\"".$n->idworkingData."\"", "classEx" => "idworkingData"],
										["kind" => "components.inputs.input-text", "attributeEx" => "type=\"hidden\" name=\"idemployeeAccount[]\" value=\"".$accountTemp."\"", "classEx" => "idemployeeAccount"],
										["kind" => "components.inputs.input-text", "attributeEx" => "type=\"hidden\" name=\"idAccountBeneficiary[]\" value=\"".$accountBeneficiary."\"", "classEx" => "idAccountBeneficiary"],
										[
											"kind"		=> "components.labels.label",
											"classEx"	=> "md:p-2 p-0 w-40",
											"label" 	=> $n->employee->first()->last_name.' '.$n->employee->first()->scnd_last_name.' '.$n->employee->first()->name
										]
									]
								],
							];
							if($request->status == 2)
							{
								$value = $n->total != "" ? $n->total : $n->workerData->first()->netIncome;
								array_push($body, ["content" => ["kind" => "components.inputs.input-text", "attributeEx" => "type=\"text\" name=\"netIncome[]\" placeholder=\"Ingrese el sueldo neto\" data-validation=\"required\" value=\"".$value."\"".' '.$disabledP, "classEx" => "w-40"]]);
							}
							else
							{
								array_push($body, ["content" => [["kind"	=> "components.labels.label", "classEx"	=> "w-40", "label" => '$ '.number_format(round($n->profitSharing->first()->netIncome,2),2)]]]);
							}
							if($request->status == 2)
							{
								$selectCatPer	= "";
								$selectCatPer	.= '<select class="border rounded py-2 px-3 m-px w-40 periodicity" name="periodicity[]" data-validation="required" '.$disabledP.'>';
								foreach(App\CatPeriodicity::whereIn('c_periodicity',['02','04','05'])->get() as $per)
								{
									if($n->idCatPeriodicity != '' && $n->idCatPeriodicity == $per->c_periodicity)
									{
										$selectCatPer .= '<option value="'.$per->c_periodicity.'" selected="selected">'.$per->description.'</option>';
									}
									elseif($n->workerData->count()>0 && $n->workerData->first()->periodicity == $per->c_periodicity)
									{
										$selectCatPer .= '<option value="'.$per->c_periodicity.'" selected="selected">'.$per->description.'</option>';	 
									}
									else
									{
										$selectCatPer .= '<option value="'.$per->c_periodicity.'">'.$per->description.'</option>';
									}
								}
								$selectCatPer .= '</select>';
								$body[] = [ "content" => [["kind"	=> "components.labels.label", "classEx"	=> "w-40", "label" => $selectCatPer ]]];
							}
							else
							{
								array_push($body, ["content" => [["kind"	=> "components.labels.label", "classEx"	=> "w-40", "label" => $n->idCatPeriodicity != '' ? App\CatPeriodicity::where('c_periodicity',$n->idCatPeriodicity)->first()->description : '']]]);
							}
							if($request->status == 2)
							{	
								$varWorkedDays = $n->worked_days != "" ? $n->worked_days : 365;
								array_push($body, ["content" => ["kind" => "components.inputs.input-text", "attributeEx" => "type=\"text\" name=\"worked_days[]\" placeholder=\"Ingrese los días trabajados\" value=\"".$varWorkedDays."\"".' '.$disabledP, "classEx" => "w-40"]]);
							}
							else
							{
								array_push($body, ["content" => [["kind"	=> "components.labels.label", "classEx"	=> "w-40", "label" => $n->profitSharing->first()->workedDays]]]);
							}
							if($request->status == 2)
							{
								$selectPayment	= "";
								$selectPayment	.= '<select class="border rounded py-2 px-3 m-px w-40 paymentWay removeselect" name="paymentWay[]" data-validation="required" '.$disabledP.'>';
								foreach(App\PaymentMethod::all() as $p)
								{
									if($n->idpaymentMethod != "" && $n->idpaymentMethod == $p->idpaymentMethod)
									{
										$selectPayment .= '<option value="'.$p->idpaymentMethod.'" selected="selected">'.$p->method.'</option>';
									}
									elseif($paymentWayTemp == $p->idpaymentMethod)
									{ 
										$selectPayment .= '<option value="'.$p->idpaymentMethod.'" selected="selected">'.$p->method.'</option>';	
									}
									else
									{
										$selectPayment .= '<option value="'.$p->idpaymentMethod.'">'.$p->method.'</option>';
									}
								}
								$selectPayment .= '</select>';
								$body[] = [ "content" => [["kind"	=> "components.labels.label", "classEx"	=> "w-40", "label" => $selectPayment ]]];
							}
							else
							{
								array_push($body, ["content" => [["kind"	=> "components.labels.label", "classEx"	=> "w-40", "label" => $n->profitsharing->first()->idpaymentMethod != "" ? $n->profitsharing->first()->paymentMethod->method : "Sin forma de pago" ]]]);
							}
							if($request->status != 2)
							{
								if($n->nominaCFDI()->exists() && \Storage::disk('reserved')->exists('/stamped/'.$n->nominaCFDI->first()->uuid.'.xml'))
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
									array_push($body, ["content" => [["kind"	=> "components.labels.label", "classEx"	=> "w-40", "label" => "---"]]]);
								}
								if($n->nominaCFDI()->exists() && \Storage::disk('reserved')->exists('/stamped/'.$n->nominaCFDI->first()->uuid.'.pdf'))
								{
									array_push($body, [
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
									array_push($body, ["content" => [["kind"	=> "components.labels.label", "classEx"	=> "w-40", "label" => "---"]]]);
								}
								$docProfitsharing = '';
								if($n->nominaCFDI()->exists() && $n->payments()->first()->documentsPayments()->exists())
								{
									foreach($n->payments->first()->documentsPayments as $pay)
									{
										$docProfitsharing .= '<div class="content">';
										$docProfitsharing .= view('components.buttons.button',[
											"variant" 		=> "dark-red",
											"buttonElement" => "a",
											"attributeEx" 	=> "target=\"_blank\" href=\"".asset('docs/payments/'.$pay->path)."\"",
											"label" 		=> "PDF"
										])->render();
										$docProfitsharing .= "</div>";
									}
								}
								else
								{
									$docProfitsharing = "---";
								}
								$body[] = [ "content" => [["kind"	=> "components.labels.label", "classEx"	=> "w-40", "label" => $docProfitsharing ]]];
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
											"attributeEx"	=> "title=\"Editar datos personales\" type=\"button\" data-toggle=\"modal\" data-target=\"#myModal\" ".$disabledP
										],
										[
											"kind" 			=> "components.buttons.button",
											"variant"		=> "primary",
											"classEx"		=> "btn-edit-paymentway",
											"label"			=> "<span class=\"icon-pencil\"></span>",
											"attributeEx"	=> "title=\"Editar forma de pago\" type=\"button\" data-toggle=\"modal\" data-target=\"#myModal\" ".$disabledP
										],
										[
											"kind" 			=> "components.buttons.button",
											"variant"		=> "red",
											"classEx"		=> "btn-delete-employee",
											"label"			=> "<span class=\"icon-x\"></span>",
											"attributeEx"	=> "title=\"Eliminar\" type=\"button\" ".$disabledP
										]
									]
								]);
							}
							$modelBody[] = $body;
						}
					@endphp
					@component('components.tables.table', [
						"modelBody" => $modelBody,
						"modelHead" => $modelHead,
					])
						@slot('attributeEx')
							id="table"
						@endslot
						@slot('attributeExBody')
							id="body-payroll"
						@endslot
						@slot('classExBody')
							request-validate
						@endslot
					@endcomponent
				@break
			@endswitch
		@else
			@if($request->status == 2)
				@component('components.labels.not-found', ["variant" => "note"])
					* Verifique que el sueldo neto sea correcto para cada empleado.
				@endcomponent
				@if ($request->nominasReal->first()->idCatTypePayroll == '001') 
					@component("components.buttons.button", ["variant" => "success"])
						@slot("classEx")
							float-right
						@endslot
						@slot('buttonElement')
							a
						@endslot
						@slot("attributeEx")
							href="{{ route('nomina.export.layout-nf',$request->folio) }}"
						@endslot
						<span>Exportar Plantilla</span> <span class='icon-file-excel'></span>
					@endcomponent
				@endif
				@component("components.buttons.button", ["variant" => "success"])
					@slot("classEx")
						float-right
					@endslot
					@slot('buttonElement')
						a
					@endslot
					@slot("attributeEx")
						href="{{ route('nomina.export.employee',$request->folio) }}"
					@endslot
					<span>Exportar Datos de Empleado</span> <span class='icon-file-excel'></span>
				@endcomponent
			@endif
			@if($check_request_fiscal != '')
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="hidden" name="type_nf" value="1"
					@endslot
				@endcomponent
			@else
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="hidden" name="type_nf" value="2"
					@endslot
				@endcomponent
			@endif
			@php
				$array_nf 	= session('errors_nofiscal');
				$body		= [];
				$modelBody	= [];
				$modelHead	=
				[
					[
						["value" => "Nombre del Empleado", "classEx" => "sticky inset-x-0 bg-orange-500"]
					]
				];
				if ($request->nominasReal->first()->idCatTypePayroll == '001') 
				{
					array_push($modelHead[0], ["value" => "SD Real"]);
					array_push($modelHead[0], ["value" => "Días trabajados"]);
					array_push($modelHead[0], ["value" => "Faltas"]);
					array_push($modelHead[0], ["value" => "Horas extra"]);
					array_push($modelHead[0], ["value" => "Días festivos"]);
					array_push($modelHead[0], ["value" => "Domingos trabajados"]);
					array_push($modelHead[0], ["value" => "Sueldo Real"]);
					array_push($modelHead[0], ["value" => "Total Horas Extra"]);
					array_push($modelHead[0], ["value" => "Total Días Festivos"]);
					array_push($modelHead[0], ["value" => "Total Domingos Trabajados"]);
					array_push($modelHead[0], ["value" => "Total a Pagar"]);
					if($check_request_fiscal != '')
					{
						array_push($modelHead[0], ["value" => "Horas Extra Fiscal"]);
						array_push($modelHead[0], ["value" => "Días Festivos Fiscal"]);
						array_push($modelHead[0], ["value" => "Domingos Trabajados Fiscal"]);
						array_push($modelHead[0], ["value" => "Neto Fiscal"]);
						array_push($modelHead[0], ["value" => "Total Fiscal Pagado"]);
						array_push($modelHead[0], ["value" => "Infonavit Fiscal"]);
						array_push($modelHead[0], ["value" => "Fonacot Fiscal"]);
						array_push($modelHead[0], ["value" => "Pensión Alimenticia"]);
						array_push($modelHead[0], ["value" => "Préstamo"]);
						array_push($modelHead[0], ["value" => "Infonavit Complemento No Fiscal"]);
					}
					array_push($modelHead[0], ["value" => "Horas Extra No Fiscal"]);
					array_push($modelHead[0], ["value" => "Días Festivos No Fiscal"]);
					array_push($modelHead[0], ["value" => "Domingos Trabajados No Fiscal"]);
					array_push($modelHead[0], ["value" => "Neto No Fiscal"]);
				}
				array_push($modelHead[0], ["value" => "Total No Fiscal Por Pagar"]);
				array_push($modelHead[0], ["value" => "Forma de pago"]);
				if($request->status != 2)
				{
					if($request->nominasReal->first()->type_nomina != 3)
					{
						array_push($modelHead[0], ["value" => "Recibo"]);
					}
					else
					{
						array_push($modelHead[0], ["value" => "Documentos de pago"]);
					}
					array_push($modelHead[0], ["value" => "Comprobante de Pago"]);
				}
				if($request->status == 2)
				{
					array_push($modelHead[0], ["value" => "Acciones"]);
				}

				foreach(App\NominaEmployee::join('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')->where('nomina_employees.idnomina',$request->nominasReal->first()->idnomina)->where('nomina_employees.visible',1)->orderBy('real_employees.last_name','ASC')->orderBy('real_employees.scnd_last_name','ASC')->orderBy('real_employees.name','ASC')->select('nomina_employees.*')->get() as $n)
				{
					if($check_request_fiscal != '')
					{
						$nominaemp = App\NominaEmployee::where('idrealEmployee',$n->idrealEmployee)
									->where('idnomina',$check_request_fiscal->nominasReal->first()->idnomina)
									->first();
					}
					else
					{
						$nominaemp = "";
					}

					$sueldo_fiscal				= 0;
					$infonavit_fiscal			= 0;
					$infonavit_complemento		= 0;
					$fonacot_fiscal				= 0;
					$total_extra_time_fiscal	= 0;
					$total_sundays_fiscal		= 0;
					$total_holiday_fiscal		= 0;
					$total_extra_time_no_fiscal	= 0;
					$total_sundays_no_fiscal	= 0;
					$total_holiday_no_fiscal	= 0;
					$sueldo_total_fiscal  		= 0;
					$alimony_fiscal 			= 0;
					$loan_retention_fiscal 		= 0;

					$total_fiscal_pagado 		= $total_extra_time_fiscal + $total_holiday_fiscal + $total_sundays_fiscal + $sueldo_total_fiscal;

					$total_neto					= $n->workerData->first()->netIncome;
					$worked_days				= ($nominaemp != "" && $nominaemp->idCatPeriodicity != "") ? App\CatPeriodicity::find($nominaemp->idCatPeriodicity)->days : ($n->workerData->first()->periodicity != "" ? App\CatPeriodicity::find($n->workerData->first()->periodicity)->days : 1);
					$sd_real					= round($total_neto/$worked_days,6);

					$horas_extra				= $n->extra_hours;
					$total_extra_time_real 		= $horas_extra < 9 ? round(($sd_real/8)*2*$horas_extra,2) : round((($sd_real/8)*2*9)+(($sd_real/8)*3)*($horas_extra-9),2);
					$total_extra_time_no_fiscal = $n->nominasEmployeeNF()->exists() && $n->nominasEmployeeNF->first()->extra_time != "" ? round($n->nominasEmployeeNF->first()->extra_time,2) : ($total_extra_time_real - $total_extra_time_fiscal);

					$dias_festivo				= $n->holidays;
					$total_dias_festivo_real	= round($dias_festivo*$sd_real*2,2);
					$total_holiday_no_fiscal	= $n->nominasEmployeeNF()->exists() && $n->nominasEmployeeNF->first()->holiday != "" ? round($n->nominasEmployeeNF->first()->holiday,2) : ($total_dias_festivo_real - $total_holiday_fiscal);

					$domingos					= $n->sundays;
					$total_sundays_real			= round(($sd_real*1.25)*$domingos,2);
					$total_sundays_no_fiscal	= $n->nominasEmployeeNF()->exists() && $n->nominasEmployeeNF->first()->sundays != "" ? round($n->nominasEmployeeNF->first()->sundays,2) : ($total_sundays_real - $total_sundays_fiscal);

					$sueldo_real				= round($sd_real*($worked_days-$n->absence),2);
					$sueldo_total_no_fiscal 	= $n->nominasEmployeeNF()->exists() && $n->nominasEmployeeNF->first()->complementPartial != "" ? $n->nominasEmployeeNF->first()->complementPartial : round($sueldo_real-$sueldo_total_fiscal,2);

					$total_no_fiscal_por_pagar 	= round($total_extra_time_no_fiscal + $total_holiday_no_fiscal + $total_sundays_no_fiscal + $sueldo_total_no_fiscal - $infonavit_complemento,2);

					$request_netIncome 			= $n->workerData->first()->complement;

					if ($request->nominasReal->first()->idCatTypePayroll == '002') 
					{
						$initYear	= date('Y').'-01-01'; 
						$endYear	= date('Y').'-12-31'; 
						$daysYear 	= date("L") == 1 ? 366 : 365;

						$calculations = [];
						switch ($n->workerData->first()->periodicity) 
						{
							case '02':
								$calculations['divisor'] = 7;
								break;

							case '04':
								$calculations['divisor'] = 15;
								break;

							case '05':
								$d = new DateTime(Carbon::now());
								$calculations['divisor'] = App\Http\Controllers\AdministracionNominaController::days_month($d->format('m'),$d->format('Y'));
								break;
							
							default:
								$calculations['divisor']  = 1;
								break;
						}

						$calculations['sueldoSemanal'] = $n->workerData->first()->netIncome != "" ? round($n->workerData->first()->netIncome,2) : 0;

						if ($calculations['sueldoSemanal'] > 0) 
						{
							$calculations['sueldoDiarioNF']	= $calculations['sueldoSemanal']/$calculations['divisor'];

							$calculations['fechaIngresoNF']	= $n->workerData->first()->admissionDate;

							if ($calculations['fechaIngresoNF'] != "") 
							{
								if (new \DateTime($calculations['fechaIngresoNF']) < new \DateTime($initYear))
								{
									$calculations['diasTrabajadosNF'] = $daysYear;
								}
								else
								{
									$datetime2	= date_create($endYear);
									$datetime1	= date_create($calculations['fechaIngresoNF']);
									$interval	= date_diff($datetime1, $datetime2);

									$daysDiff = $interval->format('%a');
									$calculations['diasTrabajadosNF'] = $daysDiff+1;
								}

								$calculations['diasParaAguinaldoNF'] = 15 * ($calculations['diasTrabajadosNF']/$daysYear);

								$calculations['sueldoNF'] = $calculations['diasParaAguinaldoNF'] * $calculations['sueldoDiarioNF'];

								$sueldo_fiscal			= 0;
								$request_netIncome 		= round($calculations['sueldoNF']);
							}
							else
							{
								$sueldo_fiscal			= 0;
								$request_netIncome 		= 0;
							}
						}
					}
				
					if ($nominaemp != '' || $nominaemp != null) 
					{
						if ($nominaemp->salary()->exists()) 
						{
							$sueldo_fiscal				= $nominaemp->salary->first()->netIncome;
							$infonavit_fiscal			= $nominaemp->salary->first()->infonavit != '' ? $nominaemp->salary->first()->infonavit : 0;
							$infonavit_complemento		= $nominaemp->salary->first()->infonavitComplement != '' ? $nominaemp->salary->first()->infonavitComplement : 0;
							$fonacot_fiscal				= $nominaemp->salary->first()->fonacot != '' ? $nominaemp->salary->first()->fonacot : 0;
							$alimony_fiscal 			= $nominaemp->salary->first()->alimony;
							$loan_retention_fiscal 		= $nominaemp->salary->first()->loan_retention;
							$request_netIncome			= $n->workerData->first()->netIncome != '' && $n->workerData->first()->netIncome>$sueldo_fiscal ? $n->workerData->first()->netIncome : $sueldo_fiscal;

							$total_extra_time_fiscal	= round($nominaemp->salary->first()->extra_time,2);
							$total_sundays_fiscal		= round($nominaemp->salary->first()->exempt_sunday,2) + round($nominaemp->salary->first()->taxed_sunday,2);
							$total_holiday_fiscal		= round($nominaemp->salary->first()->holiday,2);
							$sueldo_total_fiscal		= round($sueldo_fiscal-$total_extra_time_fiscal-$total_holiday_fiscal-$total_sundays_fiscal,2);
							$total_fiscal_pagado 		= $total_extra_time_fiscal + $total_holiday_fiscal + $total_sundays_fiscal + $sueldo_total_fiscal;

							$total_neto					= $n->workerData->first()->netIncome;
							$worked_days				= ($nominaemp != "" && $nominaemp->idCatPeriodicity != "") ? App\CatPeriodicity::find($nominaemp->idCatPeriodicity)->days : ($n->workerData->first()->periodicity != "" ? App\CatPeriodicity::find($n->workerData->first()->periodicity)->days : 1);
							$sd_real					= round($total_neto/$worked_days,6);

							$worked_days 				= $nominaemp->salary->first()->workedDays;
							$horas_extra				= $n->extra_hours;
							$total_extra_time_real 		= $horas_extra < 9 ? round(($sd_real/8)*2*$horas_extra,2) : round((($sd_real/8)*2*9)+(($sd_real/8)*3)*($horas_extra-9),2);
							$total_extra_time_no_fiscal = $n->nominasEmployeeNF()->exists() && $n->nominasEmployeeNF->first()->extra_time != "" ? round($n->nominasEmployeeNF->first()->extra_time,2) : ($total_extra_time_real - $total_extra_time_fiscal);

							$dias_festivo				= $n->holidays;
							$total_dias_festivo_real	= round($dias_festivo*$sd_real*2,2);
							$total_holiday_no_fiscal	= $n->nominasEmployeeNF()->exists() && $n->nominasEmployeeNF->first()->holiday != "" ? round($n->nominasEmployeeNF->first()->holiday,2) : ($total_dias_festivo_real - $total_holiday_fiscal);

							$domingos					= $n->sundays;
							$total_sundays_real			= round(($sd_real*1.25)*$domingos,2);
							$total_sundays_no_fiscal	= $n->nominasEmployeeNF()->exists() && $n->nominasEmployeeNF->first()->sundays != "" ? round($n->nominasEmployeeNF->first()->sundays,2) : ($total_sundays_real - $total_sundays_fiscal);

							$sueldo_real				= round($sd_real*($worked_days),2);
							$sueldo_total_no_fiscal 	= $n->nominasEmployeeNF()->exists() && $n->nominasEmployeeNF->first()->complementPartial != "" ? round($n->nominasEmployeeNF->first()->complementPartial,2) : round($sueldo_real-($sueldo_total_fiscal+$infonavit_fiscal+$fonacot_fiscal+$alimony_fiscal+$loan_retention_fiscal),2);

							$total_no_fiscal_por_pagar 	= round($total_extra_time_no_fiscal + $total_holiday_no_fiscal + $total_sundays_no_fiscal + $sueldo_total_no_fiscal - $infonavit_complemento,2);
						}
						elseif ($nominaemp->bonus()->exists())
						{
							$sueldo_fiscal		= $nominaemp->bonus->first()->netIncome;
							$request_netIncome	= $nominaemp->bonus->first()->totalIncomeBonus;
						}
						elseif ($nominaemp->liquidation()->exists()) 
						{
							$sueldo_fiscal		= $nominaemp->liquidation->first()->netIncome;
							$request_netIncome	= $nominaemp->liquidation->first()->totalIncomeLiquidation;
						}
						elseif ($nominaemp->vacationPremium()->exists()) 
						{
							$sueldo_fiscal		= $nominaemp->vacationPremium->first()->netIncome;
							$request_netIncome	= $nominaemp->vacationpremium->first()->totalIncomeVP;
						}
						elseif ($nominaemp->profitSharing()->exists()) 
						{
							$sueldo_fiscal		= $nominaemp->profitSharing->first()->netIncome;
							$request_netIncome	= $nominaemp->profitSharing->first()->totalIncomePS;
						}
					}
					$redClass = "";
					if (isset($array_nf) && in_array($n->idnominaEmployee,$array_nf))
					{
						$redClass = "tr-red-sticky";
					}

					$body = 
					[	"classEx" => "tr_payroll ".$redClass,
						[
							"classEx" => "sticky inset-x-0 bg-white",
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
									"kind"		=> "components.labels.label",
									"classEx"	=> "md:p-2 p-0 w-40",
									"label"		=> $n->employee->first()->last_name.' '.$n->employee->first()->scnd_last_name.' '.$n->employee->first()->name
								]
							]
						]
					];
					if ($request->nominasReal->first()->idCatTypePayroll == '001') 
					{
						array_push($body,["content" => ["kind" => "components.labels.label", "classEx" => "w-40",  "label" => '$ '.number_format($sd_real,2)]]);
						array_push($body,["content" => ["kind" => "components.labels.label", "classEx" => "w-40",  "label" => $worked_days]]);
						if($request->status == 2 && ($nominaemp == '' || $nominaemp == null))
						{
							array_push($body, [ "content" => [
								"kind"			=> "components.inputs.input-text",
								"attributeEx"	=> "type=\"text\" name=\"absence[]\" placeholder=\"Ingrese las faltas\" value=\"".$n->absence."\"",
								"classEx"		=> "w-40"
							]]);
						}
						else
						{
							$valueAbsence = $n->absence!= '' ? $n->absence : '0';
							array_push($body, [ "content" => [
								[
									"kind"		=> "components.labels.label",
									"classEx"	=> "w-40",
									"label" 	=> $valueAbsence
								],
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" name=\"absence[]\" placeholder=\"0\" value=\"".$valueAbsence."\""
								]
							]]);
						}
						if($request->status == 2 && ($nominaemp == '' || $nominaemp == null))
						{
							array_push($body, [ "content" => [
								"kind"			=> "components.inputs.input-text",
								"attributeEx"	=> "type=\"text\" name=\"extraHours[]\" placeholder=\"Ingrese las horas extras\" value=\"".$n->extra_hours."\"",
								"classEx"		=>	"w-40"
							]]);
						}
						else
						{
							$valueExtra = $n->extra_hours!= '' ? $n->extra_hours : '0';
							array_push($body, [ "content" => [
								[
									"kind"		=> "components.labels.label",
									"classEx"	=> "w-40",
									"label" => $valueExtra
								],
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" name=\"extraHours[]\" placeholder=\"0\" value=\"".$valueExtra."\""
								]
							]]);
						}
						if($request->status == 2 && ($nominaemp == '' || $nominaemp == null))
						{
							array_push($body, [ "content" => [
								"kind"			=> "components.inputs.input-text",
								"attributeEx"	=> "type=\"text\" name=\"holidays[]\" placeholder=\"Ingrese los días festivos\" value=\"".$n->holidays."\"",
								"classEx"		=> "w-40"
							]]);
						}
						else
						{
							$valueHolidays = $n->holidays!= '' ? $n->holidays : '0';
							array_push($body, [ "content" => [
								[
									"label" => $valueHolidays
								],
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" name=\"holidays[]\" placeholder=\"0\" value=\"".$valueHolidays."\""
								]
							]]);
						}
						if($request->status == 2 && ($nominaemp == '' || $nominaemp == null))
						{
							array_push($body, [ "content" => [
								"kind"			=> "components.inputs.input-text",
								"attributeEx"	=> "type=\"text\" name=\"sundays[]\" placeholder=\"Ingrese los domingos trabajados\" value=\"".$n->sundays."\"",
								"classEx"		=> "w-40"
							]]);
						}
						else
						{
							$valueSundays = $n->sundays!= '' ? $n->sundays : '0';
							array_push($body, [ "content" => [
								[
									"label" => $valueSundays
								],
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" name=\"sundays[]\" placeholder=\"0\" value=\"".$valueSundays."\""
								]
							]]);
						}
						array_push($body, [ "content" => [
							[
								"label" => '$ '.number_format($sueldo_real,2)
							],
							[
								"kind"			=> "components.inputs.input-text",
								"attributeEx"	=> "type=\"hidden\" value=\"".round($sueldo_real,2)."\"",
								"classEx"		=> "sueldo_real"
							]
						]]);
						array_push($body, [ "content" => [
							[
								"kind"		=> "components.labels.label",
								"classEx"	=> "td_total_extra_time w-40",
								"label"		=> $request->status == 2 ? '$ '.number_format($total_extra_time_real,2) : ($n->nominasEmployeeNF()->exists() ? '$ '.number_format($n->nominasEmployeeNF->first()->extra_time,2) : '$ '.number_format($total_extra_time_real,2))
							],
							[
								"kind"			=> "components.inputs.input-text",
								"attributeEx"	=> "type=\"hidden\" value=\"".round($total_extra_time_real,2)."\"",
								"classEx"		=> "total_extra_time"
							]
						]]);
						array_push($body, [ "content" => [
							[
								"kind"		=> "components.labels.label",
								"classEx"	=> "td_total_holiday w-40",
								"label"		=> $request->status == 2 ? '$ '.number_format($total_dias_festivo_real,2) : ($n->nominasEmployeeNF()->exists() ? '$ '.number_format($n->nominasEmployeeNF->first()->holiday,2) : '$ '.number_format($total_dias_festivo_real,2))
							],
							[
								"kind"			=> "components.inputs.input-text",
								"attributeEx"	=> "type=\"hidden\" value=\"".round($total_dias_festivo_real,2)."\"",
								"classEx"		=> "total_holiday"
							]
						]]);
						array_push($body, [ "content" => [
							[
								"kind"		=> "components.labels.label",
								"classEx"	=> "td_total_sundays w-40",
								"label"		=> $request->status == 2 ?  '$ '.number_format($total_sundays_real,2) : ($n->nominasEmployeeNF()->exists() ? '$ '.number_format($n->nominasEmployeeNF->first()->sundays,2) : '$ '.number_format($total_sundays_real,2))
							],
							[
								"kind"			=> "components.inputs.input-text",
								"attributeEx"	=> "type=\"hidden\" value=\"".round($total_sundays_real,2)."\"",
								"classEx"		=> "total_sundays"
							]
						]]);
						$td_total_a_pagar = $sueldo_real + $total_extra_time_fiscal + $total_extra_time_no_fiscal + $total_holiday_fiscal + $total_holiday_no_fiscal + $total_sundays_fiscal + $total_sundays_no_fiscal;
						array_push($body, [ "content" => [
							[
								"kind"		=> "components.labels.label",
								"classEx"	=> "td_total_a_pagar w-40",
								"label"		=> $request->status == 2 ? '$ '.number_format($td_total_a_pagar,2) : ($n->nominasEmployeeNF()->exists() ? '$ '.number_format($n->nominasEmployeeNF->first()->amount + $total_fiscal_pagado,2) : '$ '.number_format($td_total_a_pagar,2))
							],
							[
								"kind"			=> "components.inputs.input-text",
								"attributeEx"	=> "type=\"hidden\" name=\"request_netIncome[]\" value=\"".round($td_total_a_pagar,2)."\"",
								"classEx"		=> "total_a_pagar"
							]
						]]);
						if($check_request_fiscal != '')
						{
							array_push($body, [ "content" => [
								[
									"kind"		=> "components.labels.label",
									"classEx"	=> "w-40",
									"label" 	=> '$ '.number_format($total_extra_time_fiscal,2)
								],
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" value=\"".round($total_extra_time_fiscal,2)."\"",
									"classEx"		=> "total_extra_time_fiscal"
								]
							]]);
							array_push($body, [ "content" => [
								[
									"kind"		=> "components.labels.label",
									"classEx"	=> "w-40",
									"label" => '$ '.number_format($total_holiday_fiscal,2)
								],
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" value=\"".round($total_holiday_fiscal,2)."\"",
									"classEx"		=> "total_holiday_fiscal"
								]
							]]);
							array_push($body, [ "content" => [
								[
									"kind"		=> "components.labels.label",
									"classEx"	=> "w-40",
									"label" => '$ '.number_format($total_sundays_fiscal,2)
								],
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" value=\"".round($total_sundays_fiscal,2)."\"",
									"classEx"		=> "total_sundays_fiscal"
								]
							]]);
							array_push($body, [ "content" => [
								[
									"kind"		=> "components.labels.label",
									"classEx"	=> "w-40",
									"label" => '$ '.number_format($sueldo_total_fiscal,2)
								],
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" value=\"".round($sueldo_total_fiscal,2)."\"",
									"classEx"		=> "sueldo_total_fiscal"
								]
							]]);
							array_push($body, [ "content" => [
								[
									"kind"		=> "components.labels.label",
									"classEx"	=> "w-40",
									"label" => '$ '.number_format($total_fiscal_pagado,2)
								],
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" value=\"".round($total_fiscal_pagado,2)."\"",
									"classEx"		=> "total_fiscal_pagado"
								]
							]]);
							array_push($body, [ "content" => [
								[
									"kind"		=> "components.labels.label",
									"classEx"	=> "w-40",
									"label" => '$ '.number_format($infonavit_fiscal,2)
								],
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" value=\"".round($infonavit_fiscal,2)."\"",
									"classEx"		=> "infonavit_fiscal"
								]
							]]);
							array_push($body, [ "content" => [
								[
									"kind"		=> "components.labels.label",
									"classEx"	=> "w-40",
									"label" 	=> '$ '.number_format($fonacot_fiscal,2)
								],
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" value=\"".round($fonacot_fiscal,2)."\"",
									"classEx"		=> "fonacot_fiscal"
								]
							]]);
							array_push($body, [ "content" => [
								[
									"kind"		=> "components.labels.label",
									"classEx"	=> "w-40",
									"label" 	=> '$ '.number_format($alimony_fiscal,2)
								],
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" value=\"".round($alimony_fiscal,2)."\"",
									"classEx"		=> "alimony_fiscal"
								]
							]]);
							array_push($body, [ "content" => [
								[
									"kind"		=> "components.labels.label",
									"classEx"	=> "w-40",
									"label" 	=> '$ '.number_format($loan_retention_fiscal,2)
								],
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" value=\"".round($loan_retention_fiscal,2)."\"",
									"classEx"		=> "loan_retention_fiscal"
								]
							]]);
							if($request->status == 2)
							{
								array_push($body, [ 
									"content" => 
									[
										"kind"			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"text\" name=\"infonavit_complemento[]\" value=\"".round($infonavit_complemento,2)."\"",
										"classEx"		=> "infonavit_complemento w-40"
									]
								]);
							}
							else
							{
								array_push($body, [ 
									"content" => 
									[
										"kind"		=> "components.labels.label",
										"classEx"	=> "w-40",
										"label"		=> '$ '.number_format($infonavit_complemento,2)
									]
								]);
							}
						}
						if($request->status == 2)
						{
							array_push($body, [ 
								"content" => 
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"text\" name=\"total_extra_time_no_fiscal[]\" value=\"".$total_extra_time_no_fiscal."\"",
									"classEx"		=> "total_extra_time_no_fiscal w-40"
								]
							]);
						}
						else
						{
							array_push($body, [ 
								"content" => 
								[
									"kind"		=> "components.labels.label",
									"classEx"	=> "w-40",
									"label"	=> $n->nominasEmployeeNF()->exists() ? '$ '.number_format($n->nominasEmployeeNF->first()->extra_time,2) : '$ 0.00'
								]
							]);
						}
						if($request->status == 2)
						{
							array_push($body, [ 
								"content" => 
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"text\" name=\"total_holiday_no_fiscal[]\" value=\"".$total_holiday_no_fiscal."\"",
									"classEx"		=> "total_holiday_no_fiscal w-40"
								]
							]);
						}
						else
						{
							array_push($body, [ 
								"content" => 
								[
									"kind"		=> "components.labels.label",
									"classEx"	=> "w-40",
									"label"	=> $n->nominasEmployeeNF()->exists() ? '$ '.number_format($n->nominasEmployeeNF->first()->holiday,2) : '$ 0.00'
								]
							]);
						}
						if($request->status == 2)
						{
							array_push($body, [ 
								"content" => 
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"text\" name=\"total_sundays_no_fiscal[]\" value=\"".$total_sundays_no_fiscal."\"",
									"classEx"		=> "total_sundays_no_fiscal w-40"
								]
							]);
						}
						else
						{
							array_push($body, [ 
								"content" => 
								[
									"kind"		=> "components.labels.label",
									"classEx"	=> "w-40",
									"label"	=> $n->nominasEmployeeNF()->exists() ? '$ '.number_format($n->nominasEmployeeNF->first()->sundays,2) : '$ 0.00'
								]
							]);
						}
						if($request->status == 2)
						{
							array_push($body, [ 
								"content" => 
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"text\" name=\"sueldo_total_no_fiscal[]\" value=\"".$sueldo_total_no_fiscal."\"",
									"classEx"		=> "sueldo_total_no_fiscal w-40"
								]
							]);
						}
						else
						{
							array_push($body, [ 
								"content" => 
								[
									"kind"		=> "components.labels.label",
									"classEx"	=> "w-40",
									"label"	=> $n->nominasEmployeeNF()->exists() ? '$ '.number_format($n->nominasEmployeeNF->first()->complementPartial,2) : '$ 0.00'
								]
							]);
						}
					}
					$disabledR = '';
					if(isset($globalRequests))
					{
						$disabledR = 'disabled';
					}
					if($request->status == 2)
					{
						$valueNet = $n->nominasEmployeeNF()->exists() && $n->nominasEmployeeNF->first()->amount != "" ? $n->nominasEmployeeNF->first()->amount : round($request_netIncome-$infonavit_complemento,2);
						if ($request->nominasReal->first()->idCatTypePayroll == '001')
						{
							array_push($body,[
								"content" =>
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"text\" readonly=\"readonly\" data-validation=\"required\" name=\"total_no_fiscal_por_pagar[]\" value=\"".$total_no_fiscal_por_pagar."\"".' '.$disabledR,
									"classEx"		=> "remove total_no_fiscal_por_pagar w-40"
								]
							]);
						}
						else
						{
							array_push($body,[
								"content" => 
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"text\" data-validation=\"required\" name=\"request_netIncome[]\" value=\"".$valueNet."\"".' '.$disabledR,
									"classEx"		=> "remove w-40"
								]
							]);
						}
					}
					else
					{
						if ($request->nominasReal->first()->idCatTypePayroll == '001') 
						{
							array_push($body,[
								"content" => 
								[
									"kind"		=> "components.labels.label",
									"classEx"	=> "w-40",
									"label"	=> $n->nominasEmployeeNF()->exists() ? '$ '.number_format($n->nominasEmployeeNF->first()->amount,2) : '$ 0.00'
								]
							]);
						}
						else
						{
							array_push($body,[
								"content" => 
								[
									"kind"		=> "components.labels.label",
									"classEx"	=> "w-40",
									"label"	=> $n->nominasEmployeeNF()->exists() ? '$ '.number_format($n->nominasEmployeeNF->first()->amount,2) : '$ 0.00'
								]
							]);
						}
					}
					if($n->nominasEmployeeNF()->exists())
					{
						$paymentWayTemp	= $n->nominasEmployeeNF->first()->idpaymentMethod;
						array_push($body[0]['content'],
						[
							"kind"			=> "components.inputs.input-text",
							"attributeEx"	=> "type=\"hidden\" name=\"request_idnominaemployeenf[]\" value=\"".$n->nominasEmployeeNF->first()->idnominaemployeenf."\""
						]);
						array_push($body[0]['content'],
						[
							"kind"			=> "components.inputs.input-text",
							"attributeEx"	=> "type=\"hidden\" name=\"request_idemployeeAccount[]\" value=\"".$n->nominasEmployeeNF->first()->idemployeeAccounts."\"",
							"classEx"		=> "idemployeeAccount "
						]);
						array_push($body[0]['content'],
						[
							"kind"			=> "components.inputs.input-text",
							"attributeEx"	=> "type=\"hidden\" name=\"request_reference[]\" value=\"".$n->nominasEmployeeNF->first()->reference."\""
						]);
						array_push($body[0]['content'],
						[
							"kind"			=> "components.inputs.input-text",
							"attributeEx"	=> "type=\"hidden\" name=\"request_amount[]\" value=\"".$n->nominasEmployeeNF->first()->amount."\""
						]);
						array_push($body[0]['content'],
						[
							"kind"			=> "components.inputs.input-text",
							"attributeEx"	=> "type=\"hidden\" name=\"request_reason_payment[]\" value=\"".$n->nominasEmployeeNF->first()->reasonAmount."\""
						]);
					}
					else
					{
						$paymentWayTemp	= 2;
						$accountTemp	= '';
						if($n->workerData->first()->paymentWay != '' && $n->workerData->first()->paymentWay == 1 && $n->employee->first()->bankData()->where('visible',1)->where('type',1)->exists())
						{
							$paymentWayTemp	= 1;
							$accountTemp	= $n->employee->first()->bankData->where('visible',1)->where('type',1)->last()->id;	
						}
						elseif($n->workerData->first()->paymentWay != '' && $n->workerData->first()->paymentWay == 3)
						{
							$paymentWayTemp	= 3;
						}
						array_push($body[0]['content'],
						[
							"kind"			=> "components.inputs.input-text",
							"attributeEx"	=> "type=\"hidden\" name=\"request_idnominaemployeenf[]\" value=\"x\""
						]);
						array_push($body[0]['content'],
						[
							"kind"			=> "components.inputs.input-text",
							"attributeEx"	=> "type=\"hidden\" name=\"request_idemployeeAccount[]\" value=\"".$accountTemp."\"",
							"classEx"		=> "idemployeeAccount"
						]);
						array_push($body[0]['content'],
						[
							"kind"			=> "components.inputs.input-text",
							"attributeEx"	=> "type=\"hidden\" name=\"request_reference[]\" value=\"\""
						]);
						array_push($body[0]['content'],
						[
							"kind"			=> "components.inputs.input-text",
							"attributeEx"	=> "type=\"hidden\" name=\"request_amount[]\" value=\"0\""
						]);
						array_push($body[0]['content'],
						[
							"kind"			=> "components.inputs.input-text",
							"attributeEx"	=> "type=\"hidden\" name=\"request_reason_payment[]\" value=\"\""
						]);
					}
					if($request->status == 2)
					{
						$selectPayment	= "";
						$selectPayment	.= '<select class="border rounded py-2 px-3 m-px w-w-40 paymentWay removeselect" name="request_paymentWay[]" data-validation="required" '.$disabledR.'>';
						foreach(App\PaymentMethod::all() as $p)
						{
							if($n->idpaymentMethod != "" && $n->idpaymentMethod == $p->idpaymentMethod)
							{
								$selectPayment .= '<option value="'.$p->idpaymentMethod.'" selected="selected">'.$p->method.'</option>';
							}
							elseif($paymentWayTemp == $p->idpaymentMethod)
							{ 
								$selectPayment .= '<option value="'.$p->idpaymentMethod.'" selected="selected">'.$p->method.'</option>';	
							}
							else
							{
								$selectPayment .= '<option value="'.$p->idpaymentMethod.'">'.$p->method.'</option>';
							}
						}
						$selectPayment .= '</select>';
						$body[] = [ "content" =>
						[
							"kind"		=> "components.labels.label",
							"classEx"	=> "w-40",	
							"label" => $selectPayment
						]];
					}
					else
					{
						array_push($body, [ 
							"content" => 
							[
								"kind"		=> "components.labels.label",
								"classEx"	=> "w-40",
								"label"	=> $n->nominasEmployeeNF()->exists() && $n->nominasEmployeeNF->first()->idpaymentMethod != "" && $n->nominasEmployeeNF->first()->paymentMethod()->exists() ? $n->nominasEmployeeNF->first()->paymentMethod->method : 'Sin forma de pago'
							]
						]);
					}
                    if($request->status != 2)
                    {
                        if($request->nominasReal->first()->type_nomina != 3)
                        {
                            if($n->payment == 1 && $n->nominasEmployeeNF->first()->payroll_receipt()->exists())
                            {
                                if($n->nominasEmployeeNF->first()->payroll_receipt->signed_at != '')
                                {
                                    array_push($body, [
                                        "content" =>
                                        [
                                            [
                                                "kind"			=> "components.buttons.button",
                                                "variant"		=> "dark-red",
                                                "buttonElement" => "a",
                                                "attributeEx"	=> "type=\"button\" target=\"_blank\" href=\"".route('nomina.nf.receipt',['receipt'=>$n->nominasEmployeeNF->first()->payroll_receipt->id])."\"",
                                                "label"			=> "PDF"
                                            ],
                                            [
                                                "kind"			=> "components.buttons.button",
                                                "variant"		=> "dark",
                                                "attributeEx"	=> "type=\"button\" alt=\"Con firma\" title=\"Con firma\"".' '.$disabledR,
                                                "label"			=> "CF"
                                            ]
                                        ]
                                    ]);
                                }
                                else
                                {
                                    array_push($body, [
                                        "content" =>
                                        [
                                            [
                                                "kind"			=> "components.buttons.button",
                                                "variant"		=> "dark-red",
                                                "buttonElement" => "a",
                                                "attributeEx"	=> "type=\"button\" target=\"_blank\" href=\"".route('nomina.nf.receipt',['receipt'=>$n->nominasEmployeeNF->first()->payroll_receipt->id])."\"",
                                                "label"			=> "PDF"
                                            ],
                                            [
                                                "kind"			=> "components.buttons.button",
                                                "variant"		=> "dark",
                                                "attributeEx"	=> "type=\"button\" alt=\"Sin firma\" title=\"Sin firma\"".' '.$disabledR,
                                                "label"			=> "SF"
                                            ]
                                        ]
                                    ]);
                                }
                            }
							else
							{
								array_push($body, [ "content" => [["kind" => "components.labels.label", "classEx" => "w-40", "label"	=> "---" ]]]);
							}
							$docPay = '';
                            if($n->payments()->exists() && $n->payments()->first()->documentsPayments()->exists())
							{
								foreach($n->payments->first()->documentsPayments as $pay)
								{
									$docPay .= '<div class="content">';
									$docPay .= view('components.buttons.button',[
											"variant"		=> "dark-red",
											"buttonElement" => "a",
											"attributeEx"	=> "type=\"button\" target=\"_blank\" href=\"".asset('docs/payments/'.$pay->path)."\"",
											"label"			=> "PDF"
									])->render();
									$docPay .= "</div>";
								}
							}
							else
							{
								$docPay = "---";
							}
							$body[] = [ "content" => ["kind" => "components.labels.label", "classEx" => "w-40", "label" => $docPay ]];
						}
						else
						{
							$docsXML = '';
							if($n->documentsNom35()->exists())
							{
								foreach($n->documentsNom35 as $document)
								{
									if($document->name != "Comprobante de Transferencia" && \Storage::disk('public')->exists('/docs/nomina/'.$document->path))
									{
										$docsXML .= '<div class="content">';
										$docsXML .= view('components.buttons.button',[
											"variant"		=> "success",
											"buttonElement" => "a",
											"attributeEx"	=> "type=\"button\" alt=\"".$document->name."\" title=\"".$document->name."\" href=\"".route('nomina.download.payment',$document->path)."\"",
											"label"			=> "<span class=\"icon-xml\"></span>"
										])->render();
										$docsXML .= "</div>";
									}
									else
									{
										$docsXML = "---";
									}
								}
							}
							else
							{
								$docsXML = "---";
							}
							$body[]		= [ "content" => ["kind" => "components.labels.label", "classEx" => "w-40", "label" => $docsXML ]];
							$docsPDF	= '';
							if($n->documentsNom35()->exists())
							{
								foreach($n->documentsNom35 as $document)
								{
									if($document->name == "Comprobante de Transferencia" && \Storage::disk('public')->exists('/docs/nomina/'.$document->path))
									{
										$docsPDF .= '<div class="content">';
										$docsPDF .= view('components.buttons.button',[
											"variant"		=> "dark-red",
											"buttonElement" => "a",
											"attributeEx"	=> "type=\"button\" target=\"_blank\" href=\"".route('nomina.download.payment',$document->path)."\"",
											"label"			=> "PDF"
										])->render();
										$docsPDF .= "</div>";
									}
									else
									{
										$docsPDF = "---";
									}
								}
							}
							else
							{
								$docsPDF = "---";
							}
							$body[] = [ "content" => ["kind" => "components.labels.label", "classEx" => "w-40", "label" => $docsPDF ]];
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
									"attributeEx"	=> "title=\"Editar datos personales\" type=\"button\" data-toggle=\"modal\" data-target=\"#myModal\" ".$disabledR
								],
								[
									"kind" 			=> "components.buttons.button",
									"variant"		=> "primary",
									"classEx"		=> "btn-edit-employee",
									"label"			=> "<span class=\"icon-pencil\"></span>",
									"attributeEx"	=> "title=\"Editar datos\" type=\"button\" data-toggle=\"modal\" data-target=\"#myModal\" ".$disabledR
								],
								[
									"kind" 			=> "components.buttons.button",
									"variant"		=> "red",
									"classEx"		=> "btn-delete-employee",
									"label"			=> "<span class=\"icon-x\"></span>",
									"attributeEx"	=> "title=\"Eliminar\" type=\"button\" ".$disabledR
								]
							]
						]);
					}
					$modelBody[] = $body;
				}
			@endphp
			@component('components.tables.table', [
				"modelBody" => $modelBody,
				"modelHead" => $modelHead,
			])
				@slot('attributeEx')
					id="table"
				@endslot
				@slot('attributeExBody')
					id="body-payroll"
				@endslot
				@slot('classExBody')
					request-validate
				@endslot
			@endcomponent	
		@endif
		@if($request->idCheckConstruction != "" || $request->idCheck != "" || $request->idAuthorize != "")
			@component('components.labels.title-divisor') 
				@slot('classEx')
					my-4
				@endslot
				DATOS DE REVISIÓN
			@endcomponent
			@php
				$modelTable = [
					"Revisó en Obra"	=> $request->idCheckConstruction != "" ? $request->constructionReviewedUser->name.' '.$request->constructionReviewedUser->last_name.' '.$request->constructionReviewedUser->scnd_last_name : '---',
					"Revisó en RH"		=> $request->idCheck != "" ? $request->reviewedUser->name.' '.$request->reviewedUser->last_name.' '.$request->reviewedUser->scnd_last_name : '---',
					"Autorizó"			=> $request->idAuthorize != "" ? $request->authorizedUser->name.' '.$request->authorizedUser->last_name.' '.$request->authorizedUser->scnd_last_name : '---'
				];
			@endphp
			@component('components.templates.outputs.table-detail-single', ["modelTable" => $modelTable]) @endcomponent
		@endif
		@if($request->nominasReal->first()->type_nomina == 3 && $request->status == 5)
			@component("components.labels.title-divisor") CARGAR DOCUMENTOS @endcomponent
			@component('components.containers.container-form')
				<div id="documents" class="col-span-2 md:col-span-4 grid-cols-1 md:grid-cols-2 gap-6 hidden"></div>
				<div class="col-span-2 md:col-span-4 space-x-2 text-center md:text-left">
					@component("components.buttons.button", ["variant" => "warning"])
						@slot('attributeEx')
							type="button"
							name="addDoc"
							id="addDoc" 
							@if(isset($globalRequests)) 
								disabled
							@endif
						@endslot
						<span class="icon-plus"></span>
						<span>Agregar documento</span> 
					@endcomponent
					@component("components.buttons.button", ["variant" => "success"])
						@slot('classEx')
							save
						@endslot
						@slot('attributeEx')
							type="submit" 
							id="save"
							name="save"
							formaction="{{ route("nomina.upload-documents", $request->folio) }}"
							@if(isset($globalRequests)) disabled @endif
						@endslot
						CARGAR DOCUMENTOS
					@endcomponent
				</div>
			@endcomponent
		@endif
		@if($request->status == 2 && !isset($globalRequests))
			@if($check_request_fiscal == '' || ($check_request_fiscal != '' && $check_request_fiscal->status != 2 && $check_request_fiscal->status != 3))
				<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mt-4 mb-6">
					@component('components.buttons.button', ["variant" => "primary"])
						@slot('attributeEx')
							type="submit" name="enviar" value="ENVIAR SOLICITUD" @if(isset($globalRequests)) disabled @endif
						@endslot
						ENVIAR SOLICITUD
					@endcomponent
					@component('components.buttons.button', ["variant" => "secondary"])
						@slot('attributeEx')
							type="submit" id="save" name="save" value="GUARDAR CAMBIOS" formaction="{{ route('nomina.nomina-create.unsent', $request->folio) }}" @if(isset($globalRequests)) disabled @endif
						@endslot
						@slot('classEx')
							save
						@endslot
						GUARDAR CAMBIOS
					@endcomponent
					@component('components.buttons.button', ["variant" => "reset"])
						@slot('attributeEx')
							type="reset" name="borra" value="BORRAR CAMPOS" @if(isset($globalRequests)) disabled @endif
						@endslot
						@slot('classEx')
							btn-delete-form
						@endslot
						BORRAR CAMPOS
					@endcomponent
				</div>
			@endif
		@elseif(isset($globalRequests))
			<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mt-8">
				@component('components.buttons.button', ["variant" => "reset"])
					@slot('buttonElement')
						a
					@endslot
					@slot('attributeEx')
						@if(isset($option_id)) 
							href="{{ url(App\Module::find($option_id)->url) }}" 
						@else 
							href="{{ url(App\Module::find($child_id)->url) }}" 
						@endif 
					@endslot
					@slot('classEx')
						load-actioner
					@endslot
					REGRESAR
				@endcomponent
			</div>
		@else
			<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mt-8">
				@component('components.buttons.button', ["variant" => "reset"])
					@slot('buttonElement')
						a
					@endslot
					@slot('attributeEx')
						@if(isset($option_id)) 
							href="{{ url(App\Module::find($option_id)->url) }}" 
						@else 
							href="{{ url(App\Module::find($child_id)->url) }}" 
						@endif 
					@endslot
					@slot('classEx')
						load-actioner
					@endslot
					REGRESAR
				@endcomponent
			</div>
		@endif
		<div id="request"></div>
		<div id="div-delete-employee"></div>
		<input type="hidden" name="routeExcel" id="routeExcel" value="{{ session('routeExcel') ?  session('routeExcel') : ""}}">
		<a id="generatedExcel" href=""></a> 
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
						close-modal
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
<script src="{{ asset('js/moment.min.js') }}"></script>
<script>
	$(document).ready(function()
	{
		$('#separatorComa').prop('checked',true);
		validation();
		@if($request->status == 2)
			totalNomina = 0;
			$('[name="request_netIncome[]"]').each(function(i,v)
			{
				totalNomina += Number($(this).val());
			});
			$('[name="total_nomina"]').val(Number(totalNomina).toFixed(2));
		@endif
		if($('#routeExcel').val() != "")
		{
			swal("Cargando",{
				icon: '{{ asset(getenv('LOADING_IMG')) }}',
				button: false,
				closeOnClickOutside: false,
				closeOnEsc: false
			});
			href = $('#routeExcel').val();
			$("#generatedExcel").attr('href',href);
			$('#generatedExcel')[0].click();
			$("#generatedExcel").attr('href','');
			setTimeout(function() {
				swal.close();
			}, 1000);
		}
		$('.infonavit_complemento').numeric({ altDecimal: ".", decimalPlaces: 2, negative: false });
		$('.total_extra_time_no_fiscal').numeric({ altDecimal: ".", decimalPlaces: 2, negative: false });
		$('.total_holiday_no_fiscal').numeric({ altDecimal: ".", decimalPlaces: 2, negative: false });
		$('.total_sundays_no_fiscal').numeric({ altDecimal: ".", decimalPlaces: 2, negative: false });
		$('.sueldo_total_no_fiscal').numeric({ altDecimal: ".", decimalPlaces: 2, negative: false });
		$('.total_no_fiscal_por_pagar').numeric({ altDecimal: ".", decimalPlaces: 2, negative: false });
		$('input[name="request_netIncome[]"]',).numeric({ altDecimal: ".", decimalPlaces: 2, negative: false });
		$('input[name="day_bonus[]"]',).numeric({ negative: false });
		$('input[name="netIncome[]"]',).numeric({ altDecimal: ".", decimalPlaces: 2, negative: false });
		$('input[name="worked_days[]"]',).numeric({ negative: false });
		$('input[name="other_perception[]"]',).numeric({ altDecimal: ".", decimalPlaces: 2,negative: false });
		$('input[name="other_perception[]"]',).numeric({ altDecimal: ".", decimalPlaces: 2,negative: false });
		$('input[name="ptu_to_pay"]',).numeric({ altDecimal: ".", decimalPlaces: 2,negative: false });
		$('input[name="absence[]"]',).numeric({ decimal: false, negative: false });
		$('input[name="loan_perception[]"]',).numeric({ altDecimal: ".", decimalPlaces: 2,negative: false });
		$('input[name="loan_retention[]"]',).numeric({ altDecimal: ".", decimalPlaces: 2,negative: false });
		$('input[name="extraHours[]"]',).numeric({altDecimal: ".", decimalPlaces: 2, negative: false });
		$('input[name="holidays[]"]',).numeric({ decimal: false, negative: false });
		$('input[name="sundays[]"]',).numeric({ decimal: false, negative: false });
		$('input[name="other_retention_amount[]"]',).numeric({ altDecimal: ".", decimalPlaces: 2,negative: false });
		@php
			$selects = collect([
				[
					"identificator"				=> ".js-enterprises",
					"placeholder"				=> "Seleccione la empresa",
					"maximumSelectionLength"	=> "1"
				],
				[
					"identificator"				=> '.type_payroll,[name="payment_method"],[name="periodicity_request"]',
					"placeholder"				=> "Seleccione uno",
					"maximumSelectionLength"	=> "1"
				],
				[
					"identificator"				=> ".js-periodicity",
					"placeholder"				=> "Seleccione una periocidad",
					"maximumSelectionLength"	=> "1"
				],
			]);
		@endphp
		@component('components.scripts.selects',["selects" => $selects]) @endcomponent
		generalSelect({'selector':'.js-user','model':13});
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
					form[0].reset();
					$('.removeselect').val(null).trigger('change');
					$('.remove').val('');
				}
				else
				{
					swal.close();
				}
			});
		})
		.on('click','.export',function(e)
		{
			e.preventDefault();
			route = $(this).attr('href');
			$('#routeExcel').val(route);
			$('.remove').removeAttr('data-validation');
			$('.removeselect').removeAttr('required');
			$('.removeselect').removeAttr('data-validation');
			$('.request-validate').removeClass('request-validate');
			$('#save')[0].click();
		})
		.on('click','#help-btn-search-employee',function()
		{
			swal('Ayuda','Escriba el nombre del empleado y de clic en el ícono del buscador, posteriormente seleccione un empleado.','info');
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
					generalSelect({'selector': '.js-projects',	'model': 14});
					generalSelect({'selector': '#cp',			'model': 2});
					generalSelect({'selector': '.bank',			'model': 28});
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
								"placeholder"				=> "Seleccione  la clasificación del gasto",
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
			id					= $(this).parents('.tr_payroll').find('.idrealEmployee').val();
			folio				= {{ $request->folio }};
			idnominaEmployee	= $(this).parents('.tr_payroll').find('.idnominaEmployee').val();
			paymentWay 			= $(this).parents('.tr_payroll').find('.paymentWay').val();
			idemployeeAccount 	= $(this).parents('.tr_payroll').find('.idemployeeAccount').val();
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
					$('#myModal .modal-body').show().html(data);
					swal.close();
					validation();
					$('.employee_extra,.employee_discount,.employee_amount',).numeric({ altDecimal: ".", decimalPlaces: 2, negative: false });
					$('[name="employee_extra_time"]').numeric({ altDecimal: ".", decimalPlaces: 2,negative: false });
					$('[name="employee_holiday"]').numeric({ altDecimal: ".", decimalPlaces: 2,negative: false });
					$('[name="employee_sundays"]').numeric({ altDecimal: ".", decimalPlaces: 2,negative: false });
					$('[name="employee_complement"]').numeric({ altDecimal: ".", decimalPlaces: 2,negative: false });
					$('[name="employee_amount"]').numeric({ altDecimal: ".", decimalPlaces: 2,negative: false });
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

			idtypepayroll = $('[name="type_payroll"]').val();
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
			id						= $(this).parents('.tr_payroll').find('.idrealEmployee').val();
			
			folio					= {{ $request->folio }};
			idnominaEmployee		= $(this).parents('.tr_payroll').find('.idnominaEmployee').val();
			paymentWay				= $(this).parents('.tr_payroll').find('.paymentWay').val();
			idemployeeAccount		= $(this).parents('.tr_payroll').find('.idemployeeAccount').val();
			idAccountBeneficiary	= $(this).parents('.tr_payroll').find('.idAccountBeneficiary').val();

			idtypepayroll = $('[name="type_payroll"]').val();
			$.ajax(
			{
				type 	: 'post',
				url 	: '{{ route("nomina.nomina-create.data-payment") }}',
				data 	: {
							'id'					:id,
							'folio'					:folio,
							'idnominaEmployee'		:idnominaEmployee,
							'paymentWay'			:paymentWay,
							'idemployeeAccount'		:idemployeeAccount,
							'idAccountBeneficiary'	:idAccountBeneficiary
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
			idnominaEmployee				= $('input[name="idnominaEmployee"]').val();
			method_request					= $('input[name="method_request"]:checked').val();
			idAccountBeneficiary_request	= $('input[name="idAccountBeneficiary_request"]:checked').val();
			
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
					$(this).parents('.tr_payroll').find('.idAccountBeneficiary').val(idAccountBeneficiary_request);
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
			bankName	= $(this).parents('.tr_bank').find('.bank :selected').text().trim();
			clabe		= $(this).parents('.tr_bank').find('.clabe').val().trim();
			account		= $(this).parents('.tr_bank').find('.account').val().trim();
			card		= $(this).parents('.tr_bank').find('.card').val().trim();
			branch		= $(this).parents('.tr_bank').find('.branch_office').val();
			$('.card, .clabe, .account').removeClass('valid').removeClass('error');
			$(this).parents('.tr_bank').find('.span-error').remove();
			
			clabe_tr  = bankAccount_tr = card_tr = true;

			$("#banks-body .tr_body").each(function(i,v)
			{
				var currentRow 	= $(this).closest(".tr_body"); 
				bank_tr			= currentRow.find(".validate_bank").text().trim(); // bank
				account_tr		= currentRow.find(".validate_account").text().trim(); // account
				
				if((clabe == currentRow.find(".validate_clabe").text().trim()) && (clabe != ""))
				{
					clabe_tr = false;
				}
				else if((bankName+account) == (bank_tr+account_tr) && (account != ""))
				{
					bankAccount_tr = false;
				}
				else if((card == currentRow.find(".validate_card").text().trim()) && (card != ""))
				{
					card_tr = false;
				}
			});

			if(clabe_tr == false)
			{
				swal("", "Esta clabe ya ha sido registrada anteriormente", "error");
				return false;
			}
			else if(bankAccount_tr == false)
			{
				swal("", "Esta cuenta bancaria y banco ya han sido registrados anteriormente", "error");
				return false;
			}
			else if(card_tr == false)
			{
				swal("", "Esta tarjeta ya ha sido registrada anteriormente", "error");
				return false;
			}

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
					bool = true;
					if(account != "" && (account.length > 15 || account.length < 5))
					{
						$('.account').addClass('error');
						bool = false;
					}
					if(clabe != "" && (clabe.length != 18))
					{
						$('.clabe').addClass('error');
						bool = false;
					}
					if(card != "" && (card.length != 16))
					{
						$('.card').addClass('error');
						bool = false;
					}
					if(bool)
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
											"classEx"	=> "classBank validate_bank"
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
											"classEx"	=> "classClabe validate_clabe"
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
											"classEx"	=> "classAccount validate_account"
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
											"classEx"	=> "classCard validate_card"
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
						$(this).parents('.tr_bank').find('.span-error').remove();
					}
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
			if ($('#body-payroll .tr_payroll').length > 1) 
			{
				$(this).parents('.tr_payroll').find('.selectProvider').select2('close');
				swal(
				{
					icon	: '{{ asset(getenv('LOADING_IMG')) }}',
					button	: false,
					timer 	: 1000
				});
				idnominaEmployee	= $(this).parents('.tr_payroll').find('.idnominaEmployee').val();
				del 				= $('<input type="hidden" value="'+idnominaEmployee+'" name="deleteEmployee[]">');
				$('#div-delete-employee').append(del);
				$(this).parents('.tr_payroll').remove();
			}
			else
			{
				swal('','No puede eliminar a todos los empleados de la nómina','error');
			}		
		})
		.on('click','.btn-view-data', function()
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
			if ($('[name="from_date_request"]').val() != "" && $('[name="to_date_request"]').val() != "") 
			{
				$('[name="to_date_request"]').removeClass('valid').removeClass('error').val('');
				$('[name="from_date_request"]').removeClass('valid').removeClass('error').val('');
				$('.to_date').removeClass('valid').removeClass('error').val('');
				$('.from_date').removeClass('valid').removeClass('error').val('');
			}
		})
		.on('change','select[name="payment_method"]',function()
		{
			$('.paymentWay').val($(this).val()).trigger('change');
		})
		.on('change','input[name="down_date_request"]',function()
		{
			$('.down_date').val($(this).val());
		})
		.on('click','#save',function(e)
		{
			employees = $('#body-payroll .tr_payroll').length;
			if(employees == 0)
			{
				e.preventDefault();
				swal("","No tiene empleados en su lista, por favor verifique su lista.","error");
				return false;
			}
			else
			{
				$('.remove').removeAttr('data-validation');
				$('.removeselect').removeAttr('required');
				$('.removeselect').removeAttr('data-validation');
				$('.request-validate').removeClass('request-validate');
			}
		})
		.on('click','#upload_layout',function(e)
		{
			employees = $('#body-payroll .tr_payroll').length;
			if($('[name="csv_file"]').val() == "")
			{
				e.preventDefault();
				swal('', '{{ Lang::get("messages.file_null") }}', 'error');
			}
			else if(employees == 0)
			{
				e.preventDefault();
				swal("","No tiene empleados en su lista, por favor verifique su lista.","error");
				return false;
			}
			else
			{
				$('.remove').removeAttr('data-validation');
				$('.removeselect').removeAttr('required');
				$('.removeselect').removeAttr('data-validation');
				$('.request-validate').removeClass('request-validate');
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
		.on('change','[name="absence[]"]',function()
		{
			absence = $(this).val();
			periodicity = $(this).parents('.tr_payroll').find('[name="periodicity[]"] option:selected').val();

			if (periodicity == '02') 
			{
				if (absence > 7) 
				{
					swal('','Las faltan no pueden ser mayor a 7','error');
					$(this).val('0');
				}
			}

			if (periodicity == '04') 
			{
				if (absence > 15) 
				{
					swal('','Las faltan no pueden ser mayor a 15','error');
					$(this).val('0');
				}
			}

			if (periodicity == '05') 
			{
				if (absence > 30) 
				{
					swal('','Las faltan no pueden ser mayor a 30','error');
					$(this).val('0');
				}
			}
		})
		.on('change','[name="work_alimony_discount"]',function()
		{
			type = $('[name="work_alimony_discount_type"]').val();

			if (type == 1 || type == 2) 
			{
				if (type == 2) 
				{
					if ($(this).val() > 100) 
					{
						swal('','El valor no puede ser mayor a 100','error');
						$(this).val('0');
					}
				}
			}
			else
			{
				swal('','Seleccione el tipo de descuento de pensión alimenticia.','info');
				$(this).val('0');
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
		.on('change','.infonavit_complemento,.total_extra_time_no_fiscal,.total_holiday_no_fiscal,.total_sundays_no_fiscal,.sueldo_total_no_fiscal,.total_no_fiscal_por_pagar',function()
		{
			if ($(this).val() == "") 
			{
				$(this).val('0');
			}
			//total_a_pagar
			obj_sueldo_real			= $(this).parents('.tr_payroll').find('.sueldo_real');
			obj_total_extra_time	= $(this).parents('.tr_payroll').find('.total_extra_time');
			obj_total_holiday		= $(this).parents('.tr_payroll').find('.total_holiday');
			obj_total_sundays		= $(this).parents('.tr_payroll').find('.total_sundays');
			obj_sueldo_total_fiscal = $(this).parents('.tr_payroll').find('.sueldo_total_fiscal');
			obj_total_a_pagar		= $(this).parents('.tr_payroll').find('.total_a_pagar');

			variables = 
			{
				infonavit_complemento		: $(this).parents('.tr_payroll').find('.infonavit_complemento').val() == undefined ? 0 :  $(this).parents('.tr_payroll').find('.infonavit_complemento').val(),
				total_extra_time_no_fiscal	: $(this).parents('.tr_payroll').find('.total_extra_time_no_fiscal').val() == undefined ? 0 : $(this).parents('.tr_payroll').find('.total_extra_time_no_fiscal').val(),
				total_holiday_no_fiscal		: $(this).parents('.tr_payroll').find('.total_holiday_no_fiscal').val() == undefined ? 0 : $(this).parents('.tr_payroll').find('.total_holiday_no_fiscal').val(),
				total_sundays_no_fiscal		: $(this).parents('.tr_payroll').find('.total_sundays_no_fiscal').val() == undefined ? 0 : $(this).parents('.tr_payroll').find('.total_sundays_no_fiscal').val(),
				sueldo_total_no_fiscal		: $(this).parents('.tr_payroll').find('.sueldo_total_no_fiscal').val() == undefined ? 0 : $(this).parents('.tr_payroll').find('.sueldo_total_no_fiscal').val(),

				total_extra_time_fiscal		: $(this).parents('.tr_payroll').find('.total_extra_time_fiscal').val() == undefined ? 0 : $(this).parents('.tr_payroll').find('.total_extra_time_fiscal').val(),
				total_holiday_fiscal		: $(this).parents('.tr_payroll').find('.total_holiday_fiscal').val() == undefined ? 0 : $(this).parents('.tr_payroll').find('.total_holiday_fiscal').val(),
				total_sundays_fiscal		: $(this).parents('.tr_payroll').find('.total_sundays_fiscal').val() == undefined ? 0 : $(this).parents('.tr_payroll').find('.total_sundays_fiscal').val(),
				sueldo_real					: obj_sueldo_real.val(),
				sueldo_total_fiscal 		: obj_sueldo_total_fiscal.val() == undefined ? 0 :  obj_sueldo_total_fiscal.val(),
			}

			object = calculateNoFiscal(variables);

			total_no_fiscal_por_pagar	= object.total_no_fiscal_por_pagar;
			total_extra_time			= object.total_extra_time;
			total_holiday				= object.total_holiday;
			total_sundays				= object.total_sundays;
			total_a_pagar				= object.total_a_pagar;

			if (total_no_fiscal_por_pagar < 0) 
			{
				$(this).val('0');
				swal('','El Total No Fiscal no puede ser 0','error');
			}
			else
			{
				obj_total_extra_time.val(total_extra_time);
				obj_total_extra_time.parents('.tr_payroll').find('.td_total_extra_time').text(total_extra_time);

				obj_total_holiday.val(total_holiday);
				obj_total_holiday.parents('.tr_payroll').find('.td_total_holiday').text(total_holiday);

				obj_total_sundays.val(total_sundays);
				obj_total_sundays.parents('.tr_payroll').find('.td_total_sundays').text(total_sundays);

				obj_total_a_pagar.val(total_a_pagar);
				obj_total_a_pagar.parents('.tr_payroll').find('.td_total_a_pagar').text(total_a_pagar);

				$(this).parents('.tr_payroll').find('.total_no_fiscal_por_pagar').val(total_no_fiscal_por_pagar);	
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
		.on('change','[name="absence[]"]',function()
		{
			periodicity	= $(this).parents('.tr_payroll').find('.periodicity').val();
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
			periodicity	= $(this).parents('.tr_payroll').find('.periodicity').val();
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
			periodicity	= $(this).parents('.tr_payroll').find('.periodicity').val();
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
		@if($request->nominasReal->first()->type_nomina == 2 || $request->nominasReal->first()->type_nomina == 3)
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
		@if($request->nominasReal->first()->type_nomina == 3)
			.on('change','[name="request_netIncome[]"]',function()
			{
				totalNomina = 0;
				$('[name="request_netIncome[]"]').each(function(i,v)
				{
					totalNomina += Number($(this).val());
				});
				$('[name="total_nomina"]').val(Number(totalNomina).toFixed(2));
			})
			.on('click','#addDoc',function() 
			{
				@php
					$options	= collect();
					$optionsEx	= collect();
					foreach(App\NominaEmployee::join('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')->where('nomina_employees.idnomina',$request->nominasReal->first()->idnomina)->where('nomina_employees.visible',1)->orderBy('real_employees.last_name','ASC')->orderBy('real_employees.scnd_last_name','ASC')->orderBy('real_employees.name','ASC')->select('nomina_employees.*')->get() as $n)
					{
						$description	= $n->employee->first()->last_name.' '.$n->employee->first()->scnd_last_name.' '.$n->employee->first()->name;
						$options 		= $options->concat([["value" => $n->idnominaEmployee, "description" => $description]]);
					}

					$docName = view("components.labels.label",[
						"classEx"	=> "float-left",
						"label" 	=> "Empleado:"
					])->render();
					$docName 		= html_entity_decode(preg_replace("/(\r)*(\n)*/", "", $docName));
					$nameEmployee	= view("components.inputs.select",[
						"options" 		=> $options,
						"classEx" 		=> "idnominaEmployee",
						"attributeEx"	=> "name=\"idnominaEmployee[]\""
					])->render();
					$nameEmployee 	= html_entity_decode(preg_replace("/(\r)*(\n)*/", "", $nameEmployee));
					$selectName		= view("components.labels.label",[
						"classEx"	=> "float-left",
						"label" 	=> "Tipo de documento:"
					])->render();
					$selectName 	= html_entity_decode(preg_replace("/(\r)*(\n)*/", "", $selectName));
					$nameDocument	= view("components.inputs.select",[
						"options"		=> $optionsEx,
						"classEx" 		=> "nameDocument",
						"attributeEx"	=> "name=\"nameDocument[]\" data-validation=\"required\""
					])->render();
					$nameDocument 	= html_entity_decode(preg_replace("/(\r)*(\n)*/", "", $nameDocument));
					$newDoc 		= view("components.documents.upload-files",[
						"classExInput" 			=> "pathActioner",
						"attributeExInput" 		=> "name=\"path\" accept=\".pdf,.jpg,.png,.xml,.jpeg\"",
						"attributeExRealPath" 	=> "name=\"realPath[]\"",
						"classExRealPath" 		=> "path",
						"classExDelete" 		=> "delete-doc",
						"componentsExUp" 		=> $docName.$nameEmployee.$selectName.$nameDocument
					])->render();
				@endphp
				newDoc = '{!!preg_replace("/(\r)*(\n)*/", "", $newDoc)!!}';
				$('#documents').append(newDoc);
				$('#documents').removeClass('hidden').addClass('grid');
				validation(); 
				@php
					$selects = collect([
						[
							"identificator"				=> "[name=\"idnominaEmployee[]\"]",
							"placeholder"				=> "Seleccione un empleado",
							"maximumSelectionLength"	=> "1"
						],
						[
							"identificator"				=> "[name=\"nameDocument[]\"]",
							"placeholder"				=> "Seleccione un documento",
							"maximumSelectionLength"	=> "1"
						]
					]);
				@endphp
				@component('components.scripts.selects',['selects' => $selects]) @endcomponent
			})
			.on('change','[name="nameDocument[]"]',function()
			{
				$(this).parents('.docs-p').find('[name="path"]').removeAttr('accept');
				nameDocument 		= $(this).parents('.components-ex-up').find('.nameDocument option:selected').val();
				idnominaEmployee	= $(this).parents('.components-ex-up').find('.idnominaEmployee option:selected').val();

				if (nameDocument == 'CFDI PDF')
				{
					$(this).parents('.docs-p').find('[name="path"]').attr('accept','.pdf');
				}
				else if (nameDocument == 'CFDI XML')
				{
					$(this).parents('.docs-p').find('[name="path"]').attr('accept','.xml');
				}
				else if (nameDocument == 'Comprobante de Transferencia')
				{
					$(this).parents('.docs-p').find('[name="path"]').attr('accept','.pdf,.jpg,.png,.xml,.jpeg');
				}
				else
				{
					$(this).parents('.docs-p').find('[name="path"]').attr('accept','.pdf,.jpg,.png,.xml,.jpeg');
				}

				count = 0;
				$('.nameDocument').each(function(i,v)
				{
					check_idnominaEmployee = $(this).parents('.components-ex-up').find('.idnominaEmployee option:selected').val();
					if(nameDocument == $(this).val() && idnominaEmployee == check_idnominaEmployee && nameDocument != "Comprobante de Transferencia" )
					{
						count = count + 1;
					}
					if(count>1)
					{
						swal('', 'Debe seleccionar otro tipo de documento.', 'warning');
						$(this).parents('.components-ex-up').find('.nameDocument').val(null).trigger('change');
					} 
				});
				$(this).parents('.docs-p').find('.image_success').removeClass('image_success');
				$(this).parents('.docs-p').find('.path').val('');
			})
			.on('change','[name="idnominaEmployee[]"]',function()
			{
				idnominaEmployee	= $('option:selected',this).val();
				option 				= $(this).parents('.components-ex-up').find('.nameDocument');
				
				if(idnominaEmployee != "" && idnominaEmployee != undefined)
				{
					$.ajax(
					{
						type		: 'post',
						url			: '{{ route("nomina.validation.document") }}',
						data		: {'idnominaEmployee':idnominaEmployee},
						success		: function(data)
						{
							option.html(data);  
						},
						error : function()
						{
							swal('','Sucedió un error, por favor intente de nuevo.','error');
							option.html('');
						}
					});
				}
				@php
					$selects = collect([
						[
							"identificator"				=> '[name="nameDocument[]"]',
							"placeholder"				=> "Seleccione un documento",
							"maximumSelectionLength"	=> "1"
						]
					]);
				@endphp
				@component('components.scripts.selects',['selects' => $selects]) @endcomponent
				$(this).parents('.docs-p').find('.image_success').removeClass('image_success');
				$(this).parents('.docs-p').find('.path').val('');
			})
			.on('change','.pathActioner',function(e) 
			{
				idnominaEmployee = $(this).parents('.uploader-content').siblings('.components-ex-up').find('.idnominaEmployee option:selected').val();
				if( idnominaEmployee == null || $(this).parents('.uploader-content').siblings('.components-ex-up').find("[name='nameDocument[]'] option:selected").val() == "")
				{
					swal('', 'Debe seleccionar un nombre y el tipo de documento.', 'warning');
					$(this).val("");
				}
				else 
				{
					filename		= $(this);
					uploadedName 	= $(this).parents('div').parents('div').find('[name="realPath[]"]');
					nameDocument 	= $(this).parents('div').parents('div').find('[name="nameDocument[]"] option:selected');
					
					extention		= /\.jpg|\.png|\.jpeg|\.xml|\.pdf/i;
					extentionPDF	= /\.pdf/i;
					extentionXML	= /\.xml/i;

					if (nameDocument.val() == 'CFDI PDF')
					{ 
						if (filename.val().search(extentionPDF) == -1)
						{
							swal('', 'El tipo de archivo no es PDF', 'warning');
							$(this).val("");
						}
						else if (this.files[0].size>315621376)
						{
							swal('', 'El tamaño máximo de su archivo no debe ser mayor a 300Mb', 'warning');
						}
						else
						{
							$(this).css('visibility','hidden').parent('.uploader-content').addClass('loading').removeClass(function (index, css)
							{
								return (css.match (/\bimage_\S+/g) || []).join(' '); // removes anything that starts with "image_"
							});
							formData	= new FormData();
							formData.append(filename.attr('name'), filename.prop("files")[0]);
							formData.append(uploadedName.attr('name'),uploadedName.val());
							formData.append(nameDocument.attr('name'),nameDocument.val());
							$.ajax(
							{
								type		: 'post',
								url			: '{{ route("nomina.uploader") }}',
								data		: formData,
								contentType	: false,
								processData	: false,
								success		: function(r)
								{
									if(r.error=='DONE')
									{
										$(e.currentTarget).removeAttr('style').parent('.uploader-content').removeClass('loading').addClass('image_success');
										$(e.currentTarget).parent('.uploader-content').siblings('input[name="realPath[]"]').val(r.path);
										$(e.currentTarget).val('');
									}
									else
									{
										swal('',r.message, 'error');
										$(e.currentTarget).removeAttr('style').parent('.uploader-content').removeClass('loading');
										$(e.currentTarget).val('');
										$(e.currentTarget).parent('.uploader-content').siblings('input[name="realPath[]"]').val('');
									}
								},
								error: function()
								{
									swal('', 'Ocurrió un error durante la carga del archivo, intente de nuevo, por favor', 'error');
									$(e.currentTarget).removeAttr('style').parent('.uploader-content').removeClass('loading');
									$(e.currentTarget).val('');
									$(e.currentTarget).parent('.uploader-content').siblings('input[name="realPath[]"]').val('');
								}
							})
						}
					}
					else if (nameDocument.val() == 'CFDI XML') 
					{
						if (filename.val().search(extentionXML) == -1) 
						{
							swal('', 'El tipo de archivo no es XML', 'warning');
							$(this).val('');
						}
						else if (this.files[0].size>315621376)
						{
							swal('', 'El tamaño máximo de su archivo no debe ser mayor a 300Mb', 'warning');
						}
						else
						{
							$(this).css('visibility','hidden').parent('.uploader-content').addClass('loading').removeClass(function (index, css)
							{
								return (css.match (/\bimage_\S+/g) || []).join(' '); // removes anything that starts with "image_"
							});
							formData	= new FormData();
							formData.append(filename.attr('name'), filename.prop("files")[0]);
							formData.append(uploadedName.attr('name'),uploadedName.val());
							formData.append(nameDocument.attr('name'),nameDocument.val());
							$.ajax(
							{
								type		: 'post',
								url			: '{{ route("nomina.uploader") }}',
								data		: formData,
								contentType	: false,
								processData	: false,
								success		: function(r)
								{
									if(r.error=='DONE')
									{
										$(e.currentTarget).removeAttr('style').parent('.uploader-content').removeClass('loading').addClass('image_success');
										$(e.currentTarget).parent('.uploader-content').siblings('input[name="realPath[]"]').val(r.path);
										$(e.currentTarget).val('');
									}
									else
									{
										swal('',r.message, 'error');
										$(e.currentTarget).removeAttr('style').parent('.uploader-content').removeClass('loading');
										$(e.currentTarget).val('');
										$(e.currentTarget).parent('.uploader-content').siblings('input[name="realPath[]"]').val('');
									}
								},
								error: function()
								{
									swal('', 'Ocurrió un error durante la carga del archivo, intente de nuevo, por favor', 'error');
									$(e.currentTarget).removeAttr('style').parent('.uploader-content').removeClass('loading');
									$(e.currentTarget).val('');
									$(e.currentTarget).parent('.uploader-content').siblings('input[name="realPath[]"]').val('');
								}
							})
						}
					}
					else
					{
						if (filename.val().search(extention) == -1)
						{
							swal('', 'El tipo de archivo no es soportado, por favor seleccione una imagen jpg, png o un archivo pdf', 'warning');
							$(this).val('');
						}
						else if (this.files[0].size>315621376)
						{
							swal('', 'El tamaño máximo de su archivo no debe ser mayor a 300Mb', 'warning');
						}
						else
						{
							$(this).css('visibility','hidden').parent('.uploader-content').addClass('loading').removeClass(function (index, css)
							{
								return (css.match (/\bimage_\S+/g) || []).join(' '); // removes anything that starts with "image_"
							});
							formData	= new FormData();
							formData.append(filename.attr('name'), filename.prop("files")[0]);
							formData.append(uploadedName.attr('name'),uploadedName.val());
							formData.append(nameDocument.attr('name'),nameDocument.val());
							$.ajax(
							{
								type		: 'post',
								url			: '{{ route("nomina.uploader") }}',
								data		: formData,
								contentType	: false,
								processData	: false,
								success		: function(r)
								{
									if(r.error=='DONE')
									{
										$(e.currentTarget).removeAttr('style').parent('.uploader-content').removeClass('loading').addClass('image_success');
										$(e.currentTarget).parent('.uploader-content').siblings('input[name="realPath[]"]').val(r.path);
										$(e.currentTarget).val('');
									}
									else
									{
										swal('',r.message, 'error');
										$(e.currentTarget).removeAttr('style').parent('.uploader-content').removeClass('loading');
										$(e.currentTarget).val('');
										$(e.currentTarget).parent('.uploader-content').siblings('input[name="realPath[]"]').val('');
									}
								},
								error: function()
								{
									swal('', 'Ocurrió un error durante la carga del archivo, intente de nuevo, por favor', 'error');
									$(e.currentTarget).removeAttr('style').parent('.uploader-content').removeClass('loading');
									$(e.currentTarget).val('');
									$(e.currentTarget).parent('.uploader-content').siblings('input[name="realPath[]"]').val('');
								}
							})
						}
					} 
				}
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
					url			: '{{ route("nomina.uploader") }}',
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
				if($('.docs-p').length<1)
				{
					$('#documents').addClass('hidden').removeClass('grid');
				}
			})
		@endif
		.on('change','[name="to_date_request"],[name="from_date_request"]',function()
		{
			if ($('[name="from_date_request"]').val() != "" && $('[name="to_date_request"]').val() != "") 
			{
				moment.defaultFormat = "DD.MM.YYYY";
				from_date	= moment($('[name="from_date_request"]').val(),moment.defaultFormat);
				to_date		= moment($('[name="to_date_request"]').val(),moment.defaultFormat);
				diff 		= to_date.diff(from_date, 'days');
				periodicity = $('[name="periodicity_request"] option:selected').val();
				days 		= [];
				
				if(periodicity == "02")
				{
					days = [6,7];
				}
				if(periodicity == "04")
				{
					days = [14,15,16];
				}
				if(periodicity == "05")
				{
					days = [28,29,30,31];
				}

				if(!days.includes(diff) && periodicity != undefined)
				{
					swal('','El rango de fechas seleccionado no concuerda con la periodicidad.','error');
					$('[name="to_date_request"]').val('');
					$('[name="from_date_request"]').val('');
				}
				else if(periodicity == undefined)
				{
					swal('','Seleccione primero una periodicidad','error');
					$('[name="to_date_request"]').val('');
					$('[name="from_date_request"]').val('');
				}
			}
		})
		.on('change','.to_date,.from_date',function()
		{
			check_from_date	= $(this).parents('.tr_payroll').find('.from_date');
			check_to_date	= $(this).parents('.tr_payroll').find('.to_date');
			periodicity		= $(this).parents('.tr_payroll').find('.periodicity');

			if (check_from_date.val()!= "" && check_to_date.val() != "") 
			{
				moment.defaultFormat = "DD.MM.YYYY";
				from_date	= moment(check_from_date.val(),moment.defaultFormat);
				to_date		= moment(check_to_date.val(),moment.defaultFormat);
				diff 		= to_date.diff(from_date, 'days');
				days 		= [];

				if(periodicity.val() == "02")
				{
					days = [6,7];
				}
				if(periodicity.val() == "04")
				{
					days = [14,15,16];
				}
				if(periodicity.val() == "05")
				{
					days = [28,29,30,31];
				}

				if(!days.includes(diff) && periodicity.val() != undefined)
				{
					swal('','El rango de fechas seleccionado no concuerda con la periodicidad.','error');
					check_from_date.removeClass('valid').removeClass('error').val('');
					check_to_date.removeClass('valid').removeClass('error').val('');
				}
				else if(periodicity.val() == undefined)
				{
					swal('','Seleccione primero una periodicidad','error');
					check_from_date.removeClass('valid').removeClass('error').val('');
					check_to_date.removeClass('valid').removeClass('error').val('');
				}
			}
		})
		.on('change','.periodicity',function()
		{
			check_from_date	= $(this).parents('.tr_payroll').find('.from_date');
			check_to_date	= $(this).parents('.tr_payroll').find('.to_date');
			if (check_from_date.val() != "" && check_to_date.val() != "") 
			{
				check_from_date.removeClass('valid').removeClass('error').val('');
				check_to_date.removeClass('valid').removeClass('error').val('');
			}
		})
		.on('click','.close-modal,.exit', function()
		{
			$('.selectProvider').val(null).trigger('change');
		})
	});

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
				if ($('.request-validate').length > 0) 
				{
					employees = $('#body-payroll .tr_payroll').length;
					if(employees == 0)
					{
						swal('', 'No tiene empleados en su lista, por favor verifique su lista.', 'error');
						return false;
					}
					else if($('[name="csv_file"]').val() != "")
					{
						swal('', 'Tiene un archivo sin cargar.', 'error');
						return false;
					}
					else
					{
						swal("Cargando",
						{
							icon: '{{ asset(getenv('LOADING_IMG')) }}',
							button: false,
						});
						return true;
					}
				}
				else
				{
					swal("Cargando",
					{
						icon: '{{ asset(getenv('LOADING_IMG')) }}',
						button: false,
					});
					return true;
				}
			}
		});
	}
	function calculateNoFiscal(variables) 
	{
		total_no_fiscal_por_pagar	= Number(variables.total_extra_time_no_fiscal) + Number(variables.total_holiday_no_fiscal) + Number(variables.total_sundays_no_fiscal) + Number(variables.sueldo_total_no_fiscal) - Number(variables.infonavit_complemento);
		total_extra_time			= Number(variables.total_extra_time_fiscal) + Number(variables.total_extra_time_no_fiscal);
		total_holiday				= Number(variables.total_holiday_fiscal) + Number(variables.total_holiday_no_fiscal);
		total_sundays				= Number(variables.total_sundays_fiscal) + Number(variables.total_sundays_no_fiscal);
		total_a_pagar				= Number(variables.sueldo_total_no_fiscal) + Number(variables.sueldo_total_fiscal) + Number(variables.total_extra_time_fiscal) + Number(variables.total_extra_time_no_fiscal) + Number(variables.total_holiday_fiscal) + Number(variables.total_holiday_no_fiscal) + Number(variables.total_sundays_fiscal) + Number(variables.total_sundays_no_fiscal);

		object = 
		{
			total_no_fiscal_por_pagar	: Number(total_no_fiscal_por_pagar).toFixed(2),
			total_extra_time			: Number(total_extra_time).toFixed(2),
			total_holiday				: Number(total_holiday).toFixed(2),
			total_sundays				: Number(total_sundays).toFixed(2),
			total_a_pagar				: Number(total_a_pagar).toFixed(2),
		}
		return object;
	}
	function validationEmployee()
	{
		$.validate(
		{
			form	: '#edit_employee',
			modules	: 'security',
			onError	: function($form)
			{
				swal('', '{{ Lang::get("messages.form_error") }}', 'error');
			},
			onSuccess : function($form)
			{
				swal("Cargando",
				{
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
