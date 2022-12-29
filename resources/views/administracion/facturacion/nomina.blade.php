@extends('layouts.child_module')

@section('data')
	@if($bill->status == 0 || $bill->status == 7)
		@component('components.forms.form', ["attributeEx" => "method=\"POST\" id=\"container-factura\" action=\"".route('bill.nomina.save.saved',$bill->idBill)."\""])
	@endif
		@if($bill->version == '3.3')
			@component('components.labels.not-found',
				[
					"text" => "El presente CFDI fue elaborado en la vesión <b>3.3</b> por lo que sólo se podrá timbrar hasta antes del <b>30 de abril del 2022;</b> después de esta fecha ya no podrá ser timbrado."
				])
			@endcomponent
		@endif
		@component('components.labels.title-divisor') Nómina @endcomponent
		@component('components.labels.subtitle') Emisor @endcomponent
		@component('components.containers.container-form')
			<div class="col-span-2">
				@component('components.labels.label') *RFC: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" disabled @if(isset($bill)) value="{{$bill->rfc}}" @endif
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') *Razón social: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" disabled @if(isset($bill)) value="{{$bill->businessName}}" @endif
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') *Régimen fiscal: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" disabled @if(isset($bill)) value="{{$bill->taxRegime}} - {{$bill->cfdiTaxRegime->description}}" @endif
					@endslot
				@endcomponent
			</div>
		@endcomponent
		@component('components.labels.subtitle') Receptor @endcomponent
		@component('components.containers.container-form')
			<div class="col-span-2">
				@component('components.labels.label') *RFC: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" disabled data-validation="custom required" placeholder="Ingrese el RFC" name="rfc_receiver" data-validation-regexp="^([A-ZÑ&]{4}) ?(?:- ?)?(\d{2}(?:0[1-9]|1[0-2])(?:0[1-9]|[12]\d|3[01])) ?(?:- ?)?([A-Z\d]{2})([A\d])$" data-validation-error-msg="Por favor, ingrese un RFC válido" @if(isset($bill)) value="{{$bill->clientRfc}}" @endif
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') *Razón social: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" data-validation="required" name="business_name_receiver" @if(isset($bill)) value="{{$bill->clientBusinessName}}" @endif
					@endslot
				@endcomponent
			</div>
			@if($bill->version == '4.0')
				<div class="col-span-2">
					@component('components.labels.label') *Régimen fiscal: @endcomponent
					@php
						$optionRegime = [];
						foreach(App\CatTaxRegime::where('physical','Sí')->get() as $regime)
						{
							if($regime->taxRegime == $bill->receiver_tax_regime)
							{
								$optionRegime[] = ["value" => $regime->taxRegime, "description" => $regime->taxRegime.' - '.$regime->description, "selected" => "selected"];
							}
							else
							{
								$optionRegime[] = ["value" => $regime->taxRegime, "description" => $regime->taxRegime.' - '.$regime->description];
							}
						}
					@endphp
					@component('components.inputs.select',["options" => $optionRegime])
						@slot('attributeEx')
							name="regime_receiver" id="regime_receiver" multiple
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') *Domicilio fiscal (CP): @endcomponent
					@php
						$optionCP = [];
						if($bill->receiver_zip_code != '')
						{
							$optionCP[] = ["value" => $bill->receiver_zip_code, "description" => $bill->receiver_zip_code, "selected" => "selected"];
						}
						else
						{
							$optionCP[] = ["value" => $bill->receiver_zip_code, "description" => $bill->receiver_zip_code];
						}
					@endphp
					@component('components.inputs.select',["options" => $optionCP])
						@slot('attributeEx')
							id="cp_receiver_cfdi" name="cp_receiver_cfdi" data-validation="required" multiple
						@endslot
					@endcomponent
				</div>
			@endif
		@endcomponent
		@component('components.containers.container-form')
			<div class="col-span-2">
				@component('components.labels.label') *Uso de CFDI: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" disabled @if(isset($bill)) value="{{$bill->cfdiUse->description}}" @endif
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') *Tipo de CFDI: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" disabled @if(isset($bill)) value="Nómina" @endif
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') *Lugar de expedición (CP): @endcomponent
				@php
					$optionPostal = [];
					if(isset($bill) && $bill->postalCode != '')
					{
						$optionPostal[] = ["value" => $bill->postalCode, "description" => $bill->postalCode, "selected" => "selected"];
					}
				@endphp
				@component('components.inputs.select',["options" => $optionPostal])
					@slot('attributeEx')
						id="cp_cfdi" name="cp_cfdi" data-validation="required" multiple
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') *Forma de pago: @endcomponent
				@if($bill->version == '4.0')
					@component('components.inputs.input-text')
						@slot('attributeEx')
							type="text" disabled
						@endslot
					@endcomponent
				@else
					@component('components.inputs.input-text')
						@slot('attributeEx')
							disabled value="{{$bill->paymentWay}} {{$bill->cfdiPaymentWay->description}}"
						@endslot
					@endcomponent
				@endif
			</div>
			<div class="col-span-2">
				@component('components.labels.label') *Método de pago: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" disabled value="{{$bill->paymentMethod}} {{$bill->cfdiPaymentMethod->description}}"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Serie: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						name="serie" type="text" @if(isset($bill)) value="{{$bill->serie}}" @endif placeholder="Ingrese la serie"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Condiciones de pago: @endcomponent
				@if($bill->version == '4.0')
					@component('components.inputs.input-text')
						@slot('attributeEx')
							type="text" disabled
						@endslot
					@endcomponent
				@else
					@component('components.inputs.input-text')
						@slot('attributeEx')
							type="text" @if(isset($bill)) value="{{$bill->conditions}}" @endif placeholder="Ingrese las condiciones de pago"
						@endslot
					@endcomponent
				@endif
			</div>
		@endcomponent
		@php
			$body = [];
			$modelBody = [];
			$modelHead = 
			[
				[
					["value" => "Clave de producto o servicio"],
					["value" => "Clave de unidad"],
					["value" => "Cantidad"],
					["value" => "Descripción"],
					["value" => "Valor unitario"],
					["value" => "Importe"],
					["value" => "Descuento"]
				]
			];
			
			foreach($bill->billDetail as $d)
			{
				$body = [ "classEx" => "tr_concepts",
					[
						"content" =>
						[
							[
								"label" => $d->keyProdServ
							],
							[
								"kind" 			=> "components.inputs.input-text",
								"attributeEx"	=> "type=\"hidden\" disabled value=\"".$d->keyProdServ."\""
							]
						]
					],
					[
						"content" =>
						[
							[
								"label" => $d->keyUnit
							],
							[
								"kind" 			=> "components.inputs.input-text",
								"attributeEx"	=> "type=\"hidden\" disabled value=\"".$d->keyUnit."\""
							]
						]
					],
					[
						"content" =>
						[
							[
								"label" => $d->quantity
							],
							[
								"kind" 			=> "components.inputs.input-text",
								"attributeEx"	=> "type=\"hidden\" disabled value=\"".$d->quantity."\""
							]
						]
					],
					[
						"content" =>
						[
							[
								"label" => $d->description
							],
							[
								"kind" 			=> "components.inputs.input-text",
								"attributeEx"	=> "type=\"hidden\" disabled value=\"".$d->description."\""
							]
						]
					],
					[
						"content" =>
						[
							[
								"label" => '$ '.number_format($d->value,2)
							],
							[
								"kind" 			=> "components.inputs.input-text",
								"attributeEx"	=> "type=\"hidden\" disabled value=\"".$d->value."\""
							]
						]
					],
					[
						"content" =>
						[
							[ 
								"label" => '$ '.number_format($d->amount,2)
							],
							[
								"kind" 			=> "components.inputs.input-text",
								"attributeEx"	=> "type=\"hidden\" disabled value=\"".$d->amount."\""
							]
						]
					],
					[
						"content" =>
						[
							[
								"label"	=> '$ '.number_format($d->discount,2)
							],
							[
								"kind" 			=> "components.inputs.input-text",
								"attributeEx"	=> "type=\"hidden\" disabled value=\"".$d->discount."\""
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
				table cfdi-concepts
			@endslot
		@endcomponent
		@component('components.labels.subtitle')
			@slot('classExContainer')
				mt-4
			@endslot	
			Datos complementarios del receptor
		@endcomponent
		@component('components.containers.container-form')
			<div class="col-span-2">
				@component('components.labels.label') *CURP: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" name="nomina_curp" data-validation="custom required" data-validation-regexp="^([A-Z][A,E,I,O,U,X][A-Z]{2})(\d{2})((01|03|05|07|08|10|12)(0[1-9]|[12]\d|3[01])|02(0[1-9]|[12]\d)|(04|06|09|11)(0[1-9]|[12]\d|30))([M,H])(AS|BC|BS|CC|CS|CH|CL|CM|DF|DG|GT|GR|HG|JC|MC|MN|MS|NT|NL|OC|PL|QT|QR|SP|SL|SR|TC|TS|TL|VZ|YN|ZS|NE)([B,C,D,F,G,H,J,K,L,M,N,Ñ,P,Q,R,S,T,V,W,X,Y,Z]{3})([0-9,A-Z][0-9])$" data-validation-error-msg="Por favor, ingrese un CURP válido" @if(isset($bill)) value="{{$bill->nominaReceiver->curp}}" @endif
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') *Número de seguridad social: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" name="nss" @if(isset($bill)) value="{{$bill->nominaReceiver->nss}}" @endif
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@php
					$dateStart = '';
					if(isset($bill))
					{
						$dateStart = isset($bill->nominaReceiver->laboralDateStart) ? Carbon\Carbon::createFromFormat('Y-m-d',$bill->nominaReceiver->laboralDateStart)->format('d-m-Y') : '';
					}
				@endphp
				@component('components.labels.label') *Fecha de inicio de relación laboral: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" disabled @if(isset($bill)) value="{{$dateStart}}" @endif
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') *Antigüedad: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" disabled @if(isset($bill)) value="{{$bill->nominaReceiver->antiquity}}" @endif
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') *Riesgo de puesto: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" disabled @if(isset($bill)) value="{{$bill->nominaReceiver->nominaPositionRisk->description}}" @endif
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') *Salario diario integrado: @endcomponent
				@component('components.labels.label') @if(isset($bill)) {{ '$ '.number_format($bill->nominaReceiver->sdi,2) }} @endif @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="hidden" disabled @if(isset($bill)) value="{{$bill->nominaReceiver->sdi}}" @endif
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') *Tipo contrato: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" disabled @if(isset($bill)) value="{{$bill->nominaReceiver->nominaContract->description}}" @endif
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') *Tipo régimen: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" disabled @if(isset($bill)) value="{{$bill->nominaReceiver->nominaRegime->description}}" @endif
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') *Número de empleado: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" disabled @if(isset($bill)) value="{{$bill->nominaReceiver->employee_id}}" @endif
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') *Periodicidad del pago: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" disabled @if(isset($bill)) value="{{$bill->nominaReceiver->nominaPeriodicity->description}}" @endif
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') *Lugar de expedición (CP): @endcomponent
				@php
					$optionEstate = [];
					foreach(App\State::orderName()->get() as $state)
					{
						if($bill->nominaReceiver->c_state==$state->c_state)
						{
							$optionEstate[] = ["value" => $state->c_state, "description" => $state->c_state.' - '.$state->description, "selected" => "selected"];
						}
						else
						{
							$optionEstate[] = ["value" => $state->c_state, "description" => $state->c_state.' - '.$state->description];
						}
					}
				@endphp
				@component('components.inputs.select',["options" => $optionEstate])
					@slot('attributeEx')
						name="nomina_state" multiple
					@endslot
					@slot('classEx')
						js-state
					@endslot
				@endcomponent
			</div>
		@endcomponent 
		@component('components.labels.subtitle')
			@slot('classExContainer')
				mt-4
			@endslot	
			Datos generales de la nómina
		@endcomponent
		@component('components.containers.container-form')
			<div class="col-span-2">
				@component('components.labels.label') *Tipo de nómina: @endcomponent
				@php
					$optionType = [];
					if($bill->nomina->type=='O')
					{
						$optionType[] = ["value" => "O", "description" => "Ordinaria", "selected" => "selected"];
					}
					else
					{
						$optionType[] = ["value" => "O", "description" => "Ordinaria"];
					}
					if($bill->nomina->type=='E')
					{
						$optionType[] = ["value" => "E", "description" => "Extraordinaria", "selected" => "selected"];
					}
					else
					{
						$optionType[] = ["value" => "E", "description" => "Extraordinaria"];
					}	 
				@endphp
				@component('components.inputs.select',["options" => $optionType])
					@slot('attributeEx')
						name="nomina_type" multiple
					@endslot
					@slot('classEx')
						js-type
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@php
					$datePayment	= '';
					if(isset($bill))
					{
						$datePayment = isset($bill->nomina->paymentDate) ? Carbon\Carbon::createFromFormat('Y-m-d',$bill->nomina->paymentDate)->format('d-m-Y') : '';
					}
				@endphp
				@component('components.labels.label') *Fecha de pago: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" disabled @if(isset($bill)) value="{{$datePayment}}" @endif
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@php
					$dateStartDate = '';
					if(isset($bill))
					{
						$dateStartDate = isset($bill->nomina->paymentStartDate) ? Carbon\Carbon::createFromFormat('Y-m-d',$bill->nomina->paymentStartDate)->format('d-m-Y') : '';
					}
				@endphp
				@component('components.labels.label') *Fecha inicial de pago: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" disabled @if(isset($bill)) value="{{$dateStartDate}}" @endif
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@php
					$dateEnd = '';
					if(isset($bill))
					{
						$dateEnd = isset($bill->nomina->paymentEndDate) ? Carbon\Carbon::createFromFormat('Y-m-d',$bill->nomina->paymentEndDate)->format('d-m-Y') : '';
					}
				@endphp
				@component('components.labels.label') *Fecha final de pago: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" disabled @if(isset($bill)) value="{{$dateEnd}}" @endif
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') *Número de días pagados: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" disabled @if(isset($bill)) value="{{$bill->nomina->paymentDays}}" @endif
					@endslot
				@endcomponent
			</div>
		@endcomponent
		@component('components.labels.subtitle')
			@slot('classExContainer')
				my-4
			@endslot	
			Percepciones
		@endcomponent
		@php
			$body	   =[];
			$modelBody =[];
			$modelHead = 
			[
				[
					["value" => "Tipo de percepción"],
					["value" => "Clave"],
					["value" => "Concepto"],
					["value" => "Importe gravado"],
					["value" => "Importe excento"]
				]
			];
			foreach($bill->nomina->nominaPerception as $per)
			{
				$body = [
					[
						"content" => 
						[
							"label" => $per->type." - ".$per->perception->description
						],
					],
					[
						"content" => 
						[
							"label" =>  $per->perceptionKey
						]
					],
					[	 
						"content" => 
						[
							"label" =>  $per->concept
						]

					],
					[
						"content" => 
						[
							"label" => '$ '.number_format($per->taxedAmount,2)
						] 
					],
					[
						"content" => 
						[
							"label" => '$ '.number_format($per->exemptAmount,2)
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
			@slot('classEx')
				table
			@endslot
		@endcomponent
		@if($bill->nomina->nominaExtraHours()->exists())
			@component('components.labels.subtitle')
				@slot('classExContainer')
					my-4
				@endslot	
				Horas extra
			@endcomponent
			@php
				$body	   = [];
				$modelBody = [];
				$modelHead = 
				[
					[
						["value" => "*Días"],
						["value" => "*Tipo de horas"],
						["value" => "*Horas"],
						["value" => "*Importe pagado"]
					]
				];
				foreach($bill->nomina->nominaExtraHours as $hour)
				{
					$body = [
						[
							"content" => 
							[
								"label" => $hour->days
							]
						],
						[
							"content" => 
							[
								"label" =>  $hour->cat_type_hour_id.' - '.$hour->hourType->name
							]
						],
						[	 
							"content" => 
							[
								"label" =>  $hour->hours
							]
						],
						[
							"content" => 
							[
								"label" => '$ '.number_format($hour->amount,2)
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
				@slot('classEx')
					table
				@endslot
			@endcomponent
		@endif
		@component('components.labels.subtitle')
			@slot('classExContainer')
				my-4
			@endslot	
			Deducciones
		@endcomponent
		@php
			$body	   =[];
			$modelBody =[];
			$modelHead = 
			[
				[
					["value" => "Tipo de deducción"],
					["value" => "Clave"],
					["value" => "Concepto"],
					["value" => "Importe"]
				]
			];
			foreach($bill->nomina->nominaDeduction as $ded)
			{
				$body = [
					[
						"content" => 
						[
							"label" => $ded->type." - ".$ded->deduction->description
						]
					],
					[
						"content" => 
						[
							"label" => $ded->deductionKey
						]
					],
					[	 
						"content" => 
						[
							"label" => $ded->concept
						]
					],
					[	 
						"content" => 
						[
							"label" => '$ '.number_format($ded->amount,2) 
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
			@slot('classEx')
				table
			@endslot
		@endcomponent
		@component('components.labels.subtitle')
			@slot('classExContainer')
				my-4
			@endslot	
			Otros pagos
		@endcomponent
		@php
			$body	   =[];
			$modelBody =[];
			$modelHead = 
			[
				[
					["value" => "Tipo otro pago"],
					["value" => "Clave"],
					["value" => "Concepto"],
					["value" => "Importe"]
				]
			];
			foreach($bill->nomina->nominaOtherPayment as $other)
			{
				$body = [
					[
						"content" => 
						[
							"label" => $other->type." - ".$other->otherPayment->description
						],
					],
					[
						"content" => 
						[
							"label" => $other->otherPaymentKey
						]
					],
					[	 
						"content" => 
						[
							"label" => $other->concept
						]
					],
					[	 
						"content" => 
						[
							"label" => '$ '.number_format($other->amount,2)
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
			@slot('classEx')
				table
			@endslot
		@endcomponent
		@component('components.containers.container-form')
			<div class="col-span-2">
				@component('components.labels.label')*Subtotal: @endcomponent
				@component('components.labels.label') @if(isset($bill)) {{ '$ '.number_format($bill->subtotal,2) }} @endif @endcomponent
				@component("components.inputs.input-text")
					@slot('attributeEx') 
						type="hidden"
						disabled
						@if(isset($bill)) value="{{$bill->subtotal}}" @endif
					@endslot
				@endcomponent	 
			</div>
			<div class="col-span-2">
				@component('components.labels.label')Descuento: @endcomponent
				@component('components.labels.label') @if(isset($bill)) {{ '$ '.number_format($bill->discount,2) }} @endif @endcomponent
				@component("components.inputs.input-text")
					@slot('attributeEx') 
						type="hidden"
						disabled
						@if(isset($bill)) value="{{$bill->discount}}" @endif
					@endslot
				@endcomponent	 
			</div>
			<div class="col-span-2">
				@component('components.labels.label')*Total: @endcomponent
				@component('components.labels.label') @if(isset($bill)) {{ '$ '.number_format($bill->total,2) }} @endif @endcomponent
				@component("components.inputs.input-text")
					@slot('attributeEx') 
						type="hidden"
						disabled
						@if(isset($bill)) value="{{$bill->total}}" @endif
					@endslot
				@endcomponent	 
			</div>
		@endcomponent
		<div class="card-footer text-right mt-4"> 
			@if($bill->status == 0 || $bill->status == 7)
				@if($bill->status == 7)
					@component('components.labels.not-found')
						@slot('text')
							Error durante el timbrado:
							<div>
								{!!$bill->error!!}
							</div> 
						@endslot
					@endcomponent
				@endif
				<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mb-6">
					@component('components.buttons.button',[
						"variant" => "primary"
							])
						@slot('attributeEx')
							id="save_only"
							type="submit"
						@endslot
							Guardar nómina
					@endcomponent
					@component('components.buttons.button',[
						"variant" => "secondary"
							])
						@slot('attributeEx')
							type="submit"
							formaction="{{route('bill.nomina.add.queue',$bill->idBill)}}"
						@endslot
							Agregar a cola de timbrado
					@endcomponent
					@component('components.buttons.button',[
						"variant" => "success"
							])
						@slot('attributeEx')
							type="submit"
							formaction="{{route('bill.nomina.stamp.saved',$bill->idBill)}}"
						@endslot
							Timbrar ahora
					@endcomponent
				</div>
				@elseif($bill->status == 6)
					@component('components.labels.not-found',
						[
							"text" => "	El CFDI de nómina se encuentra en cola para timbrado."
						])
					@endcomponent
				@endif
			</div>
		</div>
	@if($bill->status == 0 || $bill->status == 7)
		@endcomponent
	@endif
@endsection

@section('scripts')
	<script type="text/javascript" src="{{ asset('js/moment.min.js') }}"></script>
	<script src="{{ asset('js/daterangepicker.js') }}"></script>
	<link rel="stylesheet" type="text/css" href="{{ asset('css/daterangepicker.css') }}" />
	<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
	<script src="{{ asset('js/jquery-ui.js') }}"></script>
	<script src="{{ asset('js/datepicker.js') }}"></script>
	<script>
		$(document).ready(function()
		{
			@php
				$selects = collect([
					[
						"identificator"          => ".js-state",
						"placeholder"            => "Seleccione un estado",
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => ".js-type",
						"placeholder"            => "Seleccione un tipo",
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => '[name="regime_receiver"]',
						"placeholder"            => "Seleccione el régimen fiscal",
						"maximumSelectionLength" => "1"
					]
				]);
			@endphp
			@component('components.scripts.selects', ["selects" => $selects]) @endcomponent
			formValidate();
			generalSelect({'selector':'#cp_cfdi', 'model':2});
			generalSelect({'selector':'#cp_receiver_cfdi', 'model':2});
			$(document).on('change','#emiter_cfdi_search,[name="receptor_cfdi_search"]',function()
			{
				$('.cfdi-search-container').html('');
			})
			.on('change','#enterprise_selector',function()
			{
				rfc				= $(this).val();
				businessName	= $(this).find('option:selected').text();
				taxRegime		= $(this).find('option:selected').attr('data-tax-regime');
				$('[name="business_name_emitter"]').val(businessName);
				$('[name="rfc_emitter"]').val(rfc);
				$('[name="tax_regime_cfdi"]').val(taxRegime);
			})
			.on('click','#save_only',function()
			{
				$('#container-factura')[0].submit();
			});
		});
		function formValidate()
		{
			$.validate(
			{
				form		: '#container-factura',
				onSuccess	: function($form)
				{
					if($('#taxRegime').val()!='')
					{
						if($('.tr_concepts').length<1)
						{
							swal('','Al menos debe ingresar un concepto','error');
							return false;
						}
						else if(Number($('[name="cfdi_total"]').val()) <= 0 && $('[name="cfdi_kind"]').val() != 'P')
						{
							swal('','No pueden timbrarse facturas en cero o total negativo','error');
							return false;
						}
						else
						{
							swal({
								icon 				: '{{ url(getenv('LOADING_IMG')) }}',
								button				: false,
								closeOnEsc			: false,
								closeOnClickOutside	: false,
							});
						}
					}
					else
					{
						swal('','La empresa seleccionada no cuenta con régimen fiscal registrado por lo que no se podrá proceder.','warning');
						return false;
					}
				},
			});
		}
	</script>
@endsection