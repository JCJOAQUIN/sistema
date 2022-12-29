@extends('layouts.child_module')

@section('data')
	@component('components.labels.title-divisor') CFDI {{ $bill->version }} @endcomponent
	@if(isset($bill) && $bill->folioRequest != '')
		@component('components.containers.container-form')
			<div class="col-span-4 md:col-start-2 md:col-span-2 md:col-end-4">
				@component('components.labels.label')Folio de solicitud de ingreso:@endcomponent
				@component("components.inputs.input-text")
					@slot('attributeEx') 
						type="text"
						readonly
						value="{{$bill->folioRequest}}"
					@endslot
				@endcomponent
			</div> 
		@endcomponent
	@else
		@component('components.labels.subtitle') Proyecto @endcomponent
		@component('components.containers.container-form')
			<div class="col-span-4 md:col-start-2 md:col-span-2 md:col-end-4">
				@component('components.labels.label')*Proyecto:@endcomponent
				@component("components.inputs.input-text")
					@slot('attributeEx') 
						type="text"
						readonly
						value="{{$bill->project()->exists() ? $bill->project->proyectName : ''}}"
						placeholder="Ingrese el proyecto"
					@endslot
				@endcomponent
			</div>
		@endcomponent
	@endif
	@component('components.labels.subtitle') Emisor @endcomponent
	@component('components.containers.container-form')
		<div class="col-span-2">
			@component('components.labels.label') *RFC:  @endcomponent
			@component("components.inputs.input-text")
				@slot('attributeEx') 
					type="text" 
					disabled
					value="{{$bill->rfc}}"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') *Razon Social: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text"
					disabled
					value="{{$bill->businessName}}"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') *Régimen fiscal: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" disabled value="{{$bill->taxRegime}} - {{$bill->cfdiTaxRegime->description}}"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Dirección fiscal: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" disabled value="{{$bill->issuer_address}}" placeholder="Ingrese la dirección fiscal"
				@endslot
			@endcomponent
		</div>
	@endcomponent
	@component('components.labels.subtitle') Receptor @endcomponent
	@component('components.containers.container-form')
		<div class="col-span-2">
			@component('components.labels.label') *RFC:  @endcomponent
			@component("components.inputs.input-text")
				@slot('attributeEx') 
					type="text"
					disabled
					value="{{ $bill->clientRfc }}"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') *Razón Social:  @endcomponent
			@component("components.inputs.input-text")
				@slot('attributeEx') 
					type="text" 
					disabled
					value="{{ $bill->clientBusinessName }}"
				@endslot
			@endcomponent
		</div>
		@if($bill->version == '4.0')
			<div class="col-span-2">
				@component('components.labels.label') Régimen fiscal:  @endcomponent
				@component("components.inputs.input-text")
					@slot('attributeEx') 
						type="text" disabled value="{{ $bill->receiver_tax_regime }} {{ $bill->cfdiReceiverTaxRegime->description }}"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Domicilio fiscal (CP):  @endcomponent
				@component("components.inputs.input-text")
					@slot('attributeEx') 
						type="text" disabled value="{{ $bill->receiver_zip_code }}"
					@endslot
				@endcomponent
			</div>
		@endif
		<div class="col-span-2">
			@component('components.labels.label') Dirección fiscal:  @endcomponent
			@component("components.inputs.input-text")
				@slot('attributeEx') 
					type="text" 
					disabled
					value="{{ $bill->receiver_address }}"
					placeholder="Ingrese la dirección fiscal"
				@endslot
			@endcomponent
		</div>
	@endcomponent
	@component('components.containers.container-form')
		<div class="col-span-2">
			@component('components.labels.label') Folio Fiscal (UUID):  @endcomponent
			@component("components.inputs.input-text")
				@slot('attributeEx') 
					type="text" 
					disabled
					value="{{ $bill->uuid }}"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') No. Certificado Digital: @endcomponent
			@component("components.inputs.input-text")
				@slot('attributeEx') 
					type="text" 
					disabled
					value="{{ $bill->noCertificate }}"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') No. Certificado Digital SAT: @endcomponent
			@component("components.inputs.input-text")
				@slot('attributeEx') 
					type="text" 
					disabled
					value="{{ $bill->satCertificateNo }}"
				@endslot
			@endcomponent
		</div>
		@if($bill->version == '4.0')
			<div class="col-span-2">
				@component('components.labels.label') Exportación: @endcomponent
				@component("components.inputs.input-text")
					@slot('attributeEx') 
						type="text" 
						disabled
						value="{{ $bill->export }} - {{ $bill->cfdiExport->description }}"
					@endslot
				@endcomponent
			</div>
		@endif
		<div class="col-span-2">
			@component('components.labels.label') Uso de CFDI: @endcomponent
			@component("components.inputs.input-text")
				@slot('attributeEx') 
					type="text" 
					disabled
					value="{{ $bill->cfdiUse->description }}"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Tipo de CFDI: @endcomponent
			@component("components.inputs.input-text")
				@slot('attributeEx') 
					type="text" 
					disabled
					value="{{$bill->cfdiType->description}}"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Lugar de expedición (CP): @endcomponent
			@component("components.inputs.input-text")
				@slot('attributeEx') 
					type="text" 
					disabled
					value="{{$bill->postalCode}}"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Fecha de timbrado: @endcomponent
			@component("components.inputs.input-text")
				@slot('attributeEx') 
					type="text" 
					disabled
					value="{{ isset($bill->stampDate) ? Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$bill->stampDate)->format('d-m-Y H:i') : '' }}"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Moneda: @endcomponent
			@component("components.inputs.input-text")
				@slot('attributeEx') 
				type="text" 
				disabled 
				value="{{$bill->currency}} - {{$bill->cfdiCurrency->description}}"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Tipo de cambio: @endcomponent
			@component("components.inputs.input-text")
				@slot('attributeEx') 
					type="text" 
					disabled 
					value="{{$bill->exchange}}"
					placeholder="Ingrese el tipo de cambio"
				@endslot
			@endcomponent
		</div>
		@if($bill->paymentWay != null)
			<div class="col-span-2">
				@component('components.labels.label') Forma de pago: @endcomponent
				@component("components.inputs.input-text")
					@slot('attributeEx') 
						type="text" 
						disabled
						value= "{{$bill->cfdiPaymentWay->paymentWay}} {{$bill->cfdiPaymentWay->description}}"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Método de pago: @endcomponent
				@component("components.inputs.input-text")
					@slot('attributeEx') 
						type="text" 
						disabled
						value="{{$bill->cfdiPaymentMethod->paymentMethod}} {{$bill->cfdiPaymentMethod->description}}"
					@endslot
				@endcomponent
			</div>
		@endif
		<div class="col-span-2">
			@component('components.labels.label') Folio: @endcomponent
			@component("components.inputs.input-text")
				@slot('attributeEx') 
					type="text" 
					disabled
					value="{{$bill->folio}}"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Serie: @endcomponent
			@component("components.inputs.input-text")
				@slot('attributeEx') 
					type="text" 
					disabled
					value="{{$bill->serie}}"
					placeholder="Ingrese la serie"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Condiciones de pago: @endcomponent
			@component("components.inputs.input-text")
				@slot('attributeEx') 
					type="text" 
					disabled
					value="{{$bill->conditions}}"
					placeholder="Ingrese condiciones de pago"
				@endslot
			@endcomponent
		</div>
	@endcomponent
	<div class="related-cfdi-container">
		@if($bill->version == '4.0')
			@if($bill->cfdiRelated()->exists())
				@foreach($bill->cfdiRelated->pluck('cat_relation_id','cat_relation_id') as $relKind)
					@php
						$body		= [];
						$modelBody 	= [];
						$modelHead 	= [
							[
								"content" => [[ "label" => $relKind.' '.$bill->cfdiRelated->where('cat_relation_id',$relKind)->first()->relationKind->description ]]
							]
						];
						foreach($bill->cfdiRelated->where('cat_relation_id',$relKind) as $rel)
						{
							$body = [
								[
									"content" => [[ "label" => $rel->cfdi->uuid	]]
								]
							];
							$modelBody[] = $body;
						}
					@endphp
					@component('components.tables.alwaysVisibleTable',[
							"modelHead" => $modelHead,
							"modelBody"	=> $modelBody,
							"variant"	=> "default"
						])
					@endcomponent
				@endforeach
			@endif
		@else
			@if($bill->related != '')
				@php
					$body		= [];
					$modelBody	= [];
					$modelHead	= [
						[
							"content" => [[ "label" => $bill->related.' '.$bill->relationKind->description ]]
						]
					];
					foreach($bill->cfdiRelated as $rel)
					{
						$body = [
							[
								"content" => [[ "label" => $rel->cfdi->uuid ]]
							]
						];
						$modelBody[] = $body;
					}
				@endphp
				@component('components.tables.alwaysVisibleTable',[
						"modelHead" => $modelHead,
						"modelBody"	=> $modelBody,
						"variant"	=> "default"
					])
				@endcomponent
			@endif
		@endif
	</div>
	@php
		$modelHead = [
			["value" => "Clave de producto o servicio"],
			["value" => "Clave de unidad"],
			["value" => "Cantidad"],
			["value" => "Descripción"],
			["value" => "Valor unitario"],
			["value" => "Importe"],
			["value" => "Descuento"],
		];

		foreach($bill->billDetail as $d)
		{
			$modelBody	= [];
			$body = 
			[
				[
					"content" => 
					[
						["label"	=> $d->keyProdServ],
						["kind"		=> "components.inputs.input-text","attributeEx"	=> "type=\"hidden\" disabled value=\"".$d->keyProdServ."\""]
					]
				],
				[
					"content" =>
					[
						["label"	=> $d->keyUnit],
						["kind"		=> "components.inputs.input-text","attributeEx"	=> "type=\"hidden\" disabled value=\"".$d->keyUnit."\""]
					]
				],
				[
					"content" =>
					[
						["label"	=> $d->quantity],
						["kind"		=> "components.inputs.input-text","attributeEx"	=> "type=\"hidden\" disabled value=\"".$d->quantity."\""]
					]
				],
				[
					"content" =>
					[
						["label"	=> $d->description],
						["kind"		=> "components.inputs.input-text","attributeEx"	=> "type=\"hidden\" disabled value=\"".$d->description."\""]
					]
				],
				[
					"content" =>
					[
						["label"	=> '$ '.number_format($d->value,2)],
						["kind"		=> "components.inputs.input-text","attributeEx"	=> "type=\"hidden\" disabled value=\"".$d->value."\""]
					]
				],
				[
					"content" =>
					[	 
						["label"	=> '$ '.number_format($d->amount,2)],
						["kind"		=> "components.inputs.input-text","attributeEx"	=> "type=\"hidden\" disabled value=\"".$d->amount."\""]
					]
				],
				[
					"content" =>
					[
						["label"	=> '$ '.number_format($d->discount,2)],
						["kind"		=> "components.inputs.input-text","attributeEx"	=> "type=\"hidden\" disabled value=\"".$d->discount."\""]
					]
				]
			];
			$modelBody[]	= $body;
			$taxesRetention = []; 
			if($d->taxesRet->count()>0)
			{
				foreach($d->taxesRet as $ret)
				{
					$taxRetention =
					[
						[
							"content" =>
							[
								["label" => $ret->cfdiTax->description]
							],
						],
						[
							"content" => 
							[
								["label" => $ret->quota]
							],
						],
						[
							"content" => 
							[
								["label" => '$ '.number_format($ret->quotaValue,2)]
							],
						],
						[
							"content" => 
							[
								["label" => '$ '.number_format($ret->amount,2)]
							],
						],
					];
					$taxesRetention[] = $taxRetention; 
				}
			}

			$taxesTransfer =[];
			if($d->taxesTras->count()>0)
			{
				foreach($d->taxesTras as $tras)
				{
					$taxTranslation =
					[
						[
							"content" =>
							[
								["label" => $tras->cfdiTax->description]
							],
						],
						[
							"content" =>
							[
								["label" => $tras->quota]
							],
						],
						[
							"content" =>
							[
								["label" => '$ '.number_format($tras->quotaValue,2)]
							],
						],
						[
							"content" =>
							[
								["label" => '$ '.number_format($tras->amount,2)]
							],
						],
					];
					$taxesTransfer[] = $taxTranslation;
				}
			}
			@endphp
				@component("components.tables.table-addTaxes", ["modelHead" => $modelHead, "modelBody" => $modelBody,"taxesRetention" => $taxesRetention, "taxesTransfer" => $taxesTransfer])
					@slot('classEx')
						mt-4
					@endslot	
				@endcomponent
			@php
		}
	@endphp
	@if($bill->type == 'N')
		<div class="card payments-receipt" block>
			@component('components.labels.subtitle') 
				@slot('classExContainer')
					my-4
				@endslot 
				Datos complementarios del receptor 
			@endcomponent
			@component('components.containers.container-form')
				<div class="col-span-2"> 
					@component('components.labels.label') *CURP: @endcomponent
					@component("components.inputs.input-text")
						@slot('attributeEx') 
							type="text"
							disabled
							value="{{$bill->nominaReceiver->curp}}"
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2"> 
					@component('components.labels.label') *Número de seguridad social: @endcomponent
					@component("components.inputs.input-text")
						@slot('attributeEx') 
							type="text"
							disabled
							value="{{$bill->nominaReceiver->nss}}"
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') *Fecha de inicio de relación laboral: @endcomponent
					@component("components.inputs.input-text")
						@slot('attributeEx') 
							type="text"
							disabled
							value="{{isset($bill->nominaReceiver->laboralDateStart) ? Carbon\Carbon::createFromFormat('Y-m-d',$bill->nominaReceiver->laboralDateStart)->format('d-m-Y') : '' }}"
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') *Antigüedad: @endcomponent
					@component("components.inputs.input-text")
						@slot('attributeEx') 
							type="text"
							disabled
							value="{{$bill->nominaReceiver->antiquity}}"
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label')*Riesgo de puesto: @endcomponent
					@component("components.inputs.input-text")
						@slot('attributeEx') 
							type="text"
							disabled
							value="{{$bill->nominaReceiver->nominaPositionRisk->description}}"
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label')*Salario diario integrado: @endcomponent
					@component('components.labels.label') {{ '$ '.number_format($bill->nominaReceiver->sdi,2) }} @endcomponent
					@component("components.inputs.input-text")
						@slot('attributeEx') 
							type="hidden"
							disabled
							value="{{$bill->nominaReceiver->sdi}}"
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label')*Tipo contrato: @endcomponent
					@component("components.inputs.input-text")
						@slot('attributeEx') 
							type="text"
							disabled
							value="{{$bill->nominaReceiver->nominaContract->description}}"
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label')*Tipo régimen: @endcomponent
					@component("components.inputs.input-text")
						@slot('attributeEx') 
							type="text"
							disabled
							value="{{$bill->nominaReceiver->nominaRegime->description}}"
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label')*Número de empleado: @endcomponent
					@component("components.inputs.input-text")
						@slot('attributeEx') 
							type="text"
							disabled
							value="{{$bill->nominaReceiver->employee_id}}"
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label')*Periodicidad del pago: @endcomponent
					@component("components.inputs.input-text")
						@slot('attributeEx') 
							type="text"
							disabled
							value="{{$bill->nominaReceiver->nominaPeriodicity->description}}"
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label')*Clave entidad federativa: @endcomponent
					@component("components.inputs.input-text")
						@slot('attributeEx') 
							type="text"
							disabled
							value="{{$bill->nominaReceiver->c_state}} - {{$bill->nominaReceiver->state->description}}"
						@endslot
					@endcomponent
				</div>
			@endcomponent
			@component('components.labels.subtitle') 
				@slot('classExContainer')
					my-4
				@endslot 
				Datos generales de la nómina
			@endcomponent
			@component('components.containers.container-form')
				<div class="col-span-2">
					@component('components.labels.label')*Tipo de nómina: @endcomponent
					@component("components.inputs.input-text")
						@slot('attributeEx') 
							type="text"
							disabled
							value="@if($bill->nomina->type=='O') Ordinaria @elseif($bill->nomina->type=='E') Extraordinaria @endif"
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label')*Fecha de pago: @endcomponent
					@component("components.inputs.input-text")
						@slot('attributeEx') 
							type="text"
							disabled
							value="{{isset($bill->nomina->paymentDate) ? Carbon\Carbon::createFromFormat('Y-m-d',$bill->nomina->paymentDate)->format('d-m-Y') : '' }}"
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label')*Fecha inicial de pago: @endcomponent
					@component("components.inputs.input-text")
						@slot('attributeEx') 
							type="text"
							disabled
							value="{{isset($bill->nomina->paymentStartDate) ? Carbon\Carbon::createFromFormat('Y-m-d',$bill->nomina->paymentStartDate)->format('d-m-Y') : ''}}"
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label')*Fecha final de pago: @endcomponent
					@component("components.inputs.input-text")
						@slot('attributeEx') 
							type="text"
							disabled
							value="{{isset($bill->nomina->paymentEndDate) ? Carbon\Carbon::createFromFormat('Y-m-d',$bill->nomina->paymentEndDate)->format('d-m-Y') : ''}}"
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2"> 
					@component('components.labels.label') *Número de días pagados: @endcomponent
					@component("components.inputs.input-text")
						@slot('attributeEx') 
							type="text"
							disabled
							value="{{$bill->nomina->paymentDays}}"
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
				$body		=[];
				$modelBody	=[];
				$modelHead	= 
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
							]
						],
						[
							"content" =>
							[
								"label" => $per->perceptionKey
							]
						],
						[
							"content" =>
							[
								"label" => $per->concept
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
			@endcomponent
			@component('components.labels.subtitle') 
				@slot('classExContainer')
					my-4
				@endslot 
				Deducciones
			@endcomponent
			@php
				$body		=[];
				$modelBody	=[];
				$modelHead	= 
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
			@endcomponent
			@component('components.labels.subtitle') 
				@slot('classExContainer')
					my-4
				@endslot 
				Otros pagos
			@endcomponent
			@php
				$body		=[];
				$modelBody	=[];
				$modelHead	= [
					[
						["value" => "Clave"],
						["value" => "Concepto"],
						["value" => "Tipo otro pago"],
						["value" => "Importe"]
					]
				];
				foreach($bill->nomina->nominaOtherPayment as $other)
				{
					$body = [
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
								"label" => $other->type." - ".$other->otherPayment->description
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
			@endcomponent
		</div>
	@endif
	@component('components.containers.container-form')
		<div class="col-span-2"> 	
			@component('components.labels.label') Subtotal: @endcomponent
			@component('components.labels.label') {{ '$ '.number_format($bill->subtotal,2) }} @endcomponent
			@component("components.inputs.input-text")
				@slot('attributeEx') 
					type="hidden"
					disabled
					value="{{$bill->subtotal}}"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">	
			@component('components.labels.label') Descuento: @endcomponent
			@component('components.labels.label') {{ '$ '.number_format($bill->discount,2) }} @endcomponent
			@component("components.inputs.input-text")
				@slot('attributeEx') 
					type="hidden"
					disabled
					value="{{$bill->discount}}"
				@endslot
			@endcomponent
		</div>
		@if($bill->type != 'P' && $bill->type != 'N')
			<div class="col-span-2">
				@component('components.labels.label')Total de impuestos trasladados: @endcomponent
				@component('components.labels.label') {{ '$ '.number_format($bill->tras,2) }} @endcomponent
				@component("components.inputs.input-text")
					@slot('attributeEx') 
						type="hidden"
						disabled
						value="{{$bill->tras}}"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label')Total de impuestos retenidos: @endcomponent
				@component('components.labels.label') {{ '$ '.number_format($bill->ret,2) }} @endcomponent
				@component("components.inputs.input-text")
					@slot('attributeEx') 
						type="hidden"
						disabled
						value="{{$bill->ret}}"
					@endslot
				@endcomponent
			</div>
		@endif
		<div class="col-span-2">
			@component('components.labels.label') Total: @endcomponent
			@component('components.labels.label') {{ '$ '.number_format($bill->total,2) }} @endcomponent
			@component("components.inputs.input-text")
				@slot('attributeEx') 
					type="hidden"
					disabled
					value="{{$bill->total}}"
				@endslot
			@endcomponent
		</div>
	@endcomponent
	<div class="card payments-receipt" @if($bill->type != 'P') hidden @endif>
		@component('components.labels.subtitle') 
			@slot('classExContainer')
				my-4
			@endslot 
			Recepción de pagos
		@endcomponent
		@component('components.containers.container-form')
			<div class="col-span-2">	
				@component('components.labels.label') *Fecha de pago: @endcomponent
				@component("components.inputs.input-text")
					@slot('attributeEx') 
						type="text"
						disabled
						@if($bill->paymentComplement->count()>0) value="{{ Carbon\Carbon::createFromFormat('Y-m-d',$bill->paymentComplement->first()->paymentDate)->format('d-m-Y') }}" @endif
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') *Forma de pago: @endcomponent
				@component("components.inputs.input-text")
					@slot('attributeEx') 
						type="text"
						disabled
						@if($bill->paymentComplement->count()>0) value="{{$bill->paymentComplement->first()->complementPaymentWay->description}}" @endif
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') *Moneda: @endcomponent
				@component("components.inputs.input-text")
					@slot('attributeEx') 
						type="text"
						disabled
						@if($bill->paymentComplement->count()>0) value="{{$bill->paymentComplement->first()->currency}}" @endif
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Tipo de cambio: @endcomponent
				@component("components.inputs.input-text")
					@slot('attributeEx') 
						type="text"
						disabled
						placeholder="Ingrese el tipo de cambio"
						@if($bill->paymentComplement->count()>0) value="{{$bill->paymentComplement->first()->exchange}}" @endif
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') *Monto: @endcomponent
				@component('components.labels.label') @if($bill->paymentComplement->count()>0) {{ '$ '.number_format($bill->paymentComplement->first()->amount,2) }} @endif @endcomponent
				@component("components.inputs.input-text")
					@slot('attributeEx') 
						type="hidden"
						disabled
						@if($bill->paymentComplement->count()>0) value="{{$bill->paymentComplement->first()->amount}}" @endif
					@endslot
				@endcomponent
			</div>
		@endcomponent
		<div class="bg-orange-500 w-full text-white text-center font-semibold mb-px">Documentos relacionados</div>
		@php
			$body		=[];
			$modelBody	=[];
			$modelHead	=[
				[
					["value" => "Serie"],
					["value" => "Folio"],
					["value" => "UUID"],
					["value" => "Moneda"],
					["value" => "Método de pago"],
					["value" => "Número de parcialidad"],
					["value" => "Importe de saldo anterior"],
					["value" => "Importe pagado"],
					["value" => "Importe de saldo insoluto"]
				]
			];	

			if($bill->type == 'P' && $bill->related != '')
			{
				foreach($bill->cfdiRelated as $rel)
				{
					$body = [
						[
							"content" =>
							[
								"label" => $rel->cfdi->serie
							]
						],
						[
							"content" =>
							[	
								"label" => $rel->cfdi->folio
							]
						],
						[
							"content" =>
							[
								"label" => $rel->cfdi->uuid
							]
						],
						[
							"content" =>
							[
								"label" => $rel->cfdi->currency." - ".$rel->cfdi->cfdiCurrency->description
							]
						],
						[
							"content" =>
							[
								"label" => $rel->cfdi->paymentMethod." - ".$rel->cfdi->cfdiPaymentMethod->description
							]
						],
						[
							"content" =>
							[
								"label" => $rel->partial
							]
						],
						[
							"content" =>
							[
								"label" => '$ '.number_format($rel->prevBalance,2)
							]
						],
						[
							"content" =>
							[
								"label" => '$ '.number_format($rel->amount,2)
							]
						],
						[
							"content" =>
							[
								"label" => '$ '.number_format($rel->unpaidBalance,2)
							]
						]
					];
					$modelBody[] = $body;
				}
			}
		@endphp
		@component('components.tables.table', [
			"modelHead" => $modelHead,
			"modelBody"	=> $modelBody
			])	
		@endcomponent
	</div>
	@if($bill->status == 4)
		<div class="text-center my-4">
			@component('components.labels.label') Estatus SAT @endcomponent
			<div class="w-32 mx-auto">
				@component("components.inputs.input-text")
					@slot('attributeEx') 
						type="text"
						disabled
						value="{{$bill->statusCFDI}} @if($bill->status==5) (esperando aprobación) @endif"
					@endslot
				@endcomponent
			</div>
		</div>
	@endif
	<div class="text-center">
		@if($bill->status == 1 || $bill->status == 2)
			<div class="w-24 mx-auto my-4"> 
				@component('components.labels.label') Estatus SAT @endcomponent
				@component("components.inputs.input-text")
					@slot('attributeEx') 
						type="text"
						disabled
						value="{{$bill->statusCFDI}}"
					@endslot
				@endcomponent
			</div>
			@if(\Storage::disk('reserved')->exists('/stamped/'.$bill->uuid.'.xml'))
				@component('components.buttons.button', [
 					"variant" 		=> "success",
					"buttonElement" => "a",
					"attributeEx"	=> "href=\"".route('bill.stamped.download.xml',$bill->uuid)."\"",
					"label"			=> "<span class=\"icon-xml\"></span>" 
				])
				@endcomponent
			@endif
			@if(\Storage::disk('reserved')->exists('/stamped/'.$bill->uuid.'.pdf'))
				@component('components.buttons.button', [
					"variant" 		=> "dark-red",
					"buttonElement" => "a",
					"attributeEx"	=> "href=\"".route('bill.stamped.download.pdf',$bill->uuid)."\"",
					"label"			=> "PDF" 
				])
		  		@endcomponent
			@endif
			@component('components.labels.subtitle')
				@slot('classExContainer')
					my-4
				@endslot	
				Cancelación de CFDI
			@endcomponent
			@component('components.forms.form', ["attributeEx" => "method=\"POST\" id=\"cancel_cfdi\" action=\"".route('bill.cancel',$bill->idBill)."\""])
				@component('components.containers.container-form')
					<div class="col-span-2"> 	
						@component('components.labels.label')
							@slot('classEx')
								text-left
							@endslot
							Motivo de cancelación
						@endcomponent	
						@php
							$optionReason[] = ["value" => "01", "description" => "01 - Comprobantes emitidos con errores con relación."];
							$optionReason[] = ["value" => "02", "description" => "02 - Comprobantes emitidos con errores sin relación."];
							$optionReason[] = ["value" => "03", "description" => "03 - No se llevó a cabo la operación."];
							$optionReason[] = ["value" => "04", "description" => "04 - Operación nominativa relacionada en una factura global."];
						@endphp
						@component('components.inputs.select', ["options" => $optionReason])
							@slot('attributeEx')
								name="reason"
								id="reason" 
								multiple="multiple"
								data-validation="required"
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2 substitute-folio">
						@component('components.labels.label') 
							@slot('classEx')
								text-left
							@endslot
							Folio fiscal sustituto:
						@endcomponent
						@component('components.inputs.input-text')
							@slot('attributeEx')
								type="text"
								id="fiscal_folio" 
								name="fiscal_folio"  
								placeholder="00000000-0000-0000-0000-000000000000" 
								disabled 
								data-validation="required"
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2 md:col-span-4 grid justify-items-center md:justify-items-start">
						@component('components.buttons.button', ["variant" => "red"])
							@slot('classEx')
								cancel-cfdi
							@endslot
							@slot('attributeEx')
								type="submit"
							@endslot
							@slot('slot')
								<span class="icon-x"></span> <span>Cancelar</span>
							@endslot
						@endcomponent
					</div>
				@endcomponent
			@endcomponent	
		@elseif($bill->status == 4)
			<div class="flex justify-center">
				@if(\Storage::disk('reserved')->exists('/cancelled/'.$bill->uuid.'_acuse.xml'))
					@component('components.buttons.button', [
						"variant" 		=> "success",
						"buttonElement" => "a",
						"attributeEx"	=> "href=\"".route('cancelled.download.xml',$bill->uuid)."\"",
						"label"			=> "<span class=\"icon-xml\"></span>" 
					])
					@endcomponent
				@endif
				@if(\Storage::disk('reserved')->exists('/cancelled/'.$bill->uuid.'_acuse.pdf'))
					@component('components.buttons.button', [
						"variant" 		=> "dark-red",
						"buttonElement" => "a",
						"attributeEx"	=> "href=\"".route('cancelled.download.pdf',$bill->uuid)."\"",
						"label"			=> "PDF" 
					])
					@endcomponent
				@endif
			</div>
		@elseif($bill->status == 3)
			<div class="flex justify-center">
				@component('components.buttons.button', [
					"variant" 		=> "primary",
					"buttonElement" => "a",
					"attributeEx"	=> "href=\"".route('bill.cancelled.status',$bill->idBill)."\"",
					"label"			=> "Consultar y actualizar estatus",
					"classEx"		=> "mt-4"
				])
				@endcomponent
			</div>
		@endif
	</div>
	<div class="flex justify-center">
		@if($option_id == 154)
			@component('components.buttons.button', [
				"variant" 		=> "reset",
				"buttonElement" => "a",
				"attributeEx"	=> "href=\"".route('bill.stamped')."\"",
				"label"			=> "Regresar" 
			])
			@endcomponent
		@else
			@component('components.buttons.button', [
				"variant" 		=> "reset",
				"buttonElement" => "a",
				"attributeEx"	=> "href=\"".route('bill.cancelled')."\"",
				"label"			=> "Regresar" 
			])
			@endcomponent
		@endif			
	</div>
@endsection

@if($bill->status == 1 || $bill->status == 2)
	@section('scripts')
		<script type="text/javascript"> 
			$(document).ready(function()
			{
				@php
					$selects = collect([ 
						[
							"identificator"				=> "#reason",
							"placeholder"				=> "Seleccione un motivo",
							"maximumSelectionLength"	=> "1"
						]
					]);
				@endphp
				@component('components.scripts.selects',["selects" => $selects]) @endcomponent
				$('#reason').on('change', function()
				{
					if($('#reason').val() == '01')
					{
						$('#fiscal_folio').prop('disabled',false);
					}
					else
					{
						$('#fiscal_folio').prop('disabled',true);
					}
				});
				$.validate(
				{
					form : '#cancel_cfdi'
				});
			});
		</script>
	@endsection
@endif