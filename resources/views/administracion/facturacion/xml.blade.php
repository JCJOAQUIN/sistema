<?php
echo '<?xml version="1.0" encoding="utf-8"?>';
?>
<cfdi:Comprobante
@if($bill->version == '4.0')
	xmlns:cfdi="http://www.sat.gob.mx/cfd/4"
	@if($bill->type == 'P')
		xmlns:pago20="http://www.sat.gob.mx/Pagos20"
	@endif
@else
	xmlns:cfdi="http://www.sat.gob.mx/cfd/3"
	@if($bill->type == 'P')
		xmlns:pago10="http://www.sat.gob.mx/Pagos"
	@endif
@endif
@if($bill->type == 'N')
	xmlns:nomina12="http://www.sat.gob.mx/nomina12"
@endif
	xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
	xsi:schemaLocation=
"@if($bill->version == '4.0')
	http://www.sat.gob.mx/cfd/4 http://www.sat.gob.mx/sitio_internet/cfd/4/cfdv40.xsd
	@if($bill->type == 'P')
		http://www.sat.gob.mx/Pagos20 http://www.sat.gob.mx/sitio_internet/cfd/Pagos/Pagos20.xsd
	@endif
@else
	http://www.sat.gob.mx/cfd/3 http://www.sat.gob.mx/sitio_internet/cfd/3/cfdv33.xsd
	@if($bill->type == 'P')
		http://www.sat.gob.mx/Pagos http://www.sat.gob.mx/sitio_internet/cfd/Pagos/Pagos10.xsd
	@endif
@endif
@if($bill->type == 'N')
	http://www.sat.gob.mx/nomina12 http://www.sat.gob.mx/sitio_internet/cfd/nomina/nomina12.xsd
@endif"
@if($bill->version == '4.0')
	Version="4.0"
@else
	Version="3.3"
@endif
@if($bill->serie != null)
	Serie="{{$bill->serie}}"
@endif
@if($bill->folio != null)
	Folio="{{$bill->folio}}"
@endif
	Fecha="{{str_replace(" ","T",$bill->expeditionDateCFDI)}}"
@if(isset($sello) && $sello!='')
	Sello="{{$sello}}"
@endif
@if($bill->paymentWay!=null)
	FormaPago="{{$bill->paymentWay}}"
@endif
	NoCertificado="{{$noCertificado}}"
@if(isset($certificado) && $certificado!='')
	Certificado="{{$certificado}}"
@endif
@if($bill->conditions != null)
	CondicionesDePago="{{$bill->conditions}}"
@endif
	SubTotal="{{$bill->subtotal}}"
@if($bill->type != 'P')
	Descuento="{{$bill->discount}}"
@endif
	Moneda="{{$bill->currency}}"
@if($bill->exchange != '')
	TipoCambio="{{$bill->exchange}}"
@endif
	Total="{{$bill->total}}"
	TipoDeComprobante="{{$bill->type}}"
@if($bill->paymentMethod!=null)
	MetodoPago="{{$bill->paymentMethod}}"
@endif
@if($bill->version == '4.0')
	Exportacion="{{$bill->export}}"
@endif
	LugarExpedicion="{{$bill->postalCode}}">
@if($bill->version == '4.0')
	@if($bill->cfdiRelated()->exists())
		@foreach($bill->cfdiRelated->pluck('cat_relation_id','cat_relation_id') as $relKind)
			<cfdi:CfdiRelacionados TipoRelacion="{{$relKind}}">
				@foreach($bill->cfdiRelated->where('cat_relation_id',$relKind) as $rel)
					<cfdi:CfdiRelacionado UUID="{{$rel->cfdi->uuid}}"></cfdi:CfdiRelacionado>
				@endforeach
			</cfdi:CfdiRelacionados>
		@endforeach
	@endif
@else
	@if($bill->related != null)
		<cfdi:CfdiRelacionados TipoRelacion="{{$bill->related}}">
	@foreach($bill->cfdiRelated as $rel)
			<cfdi:CfdiRelacionado UUID="{{$rel->cfdi->uuid}}"></cfdi:CfdiRelacionado>
	@endforeach
		</cfdi:CfdiRelacionados>
	@endif
@endif
	<cfdi:Emisor Rfc="{{$bill->rfc}}" Nombre="{{$bill->businessName}}" RegimenFiscal="{{$bill->taxRegime}}"></cfdi:Emisor>
	<cfdi:Receptor Rfc="{{$bill->clientRfc}}" Nombre="{{$bill->clientBusinessName}}" UsoCFDI="{{$bill->useBill}}" @if($bill->version == '4.0') DomicilioFiscalReceptor="{{$bill->receiver_zip_code}}" RegimenFiscalReceptor="{{$bill->receiver_tax_regime}}" @endif></cfdi:Receptor>
@php
	$trasCFDI = $retCFDI = array();
@endphp
	<cfdi:Conceptos>
	@foreach($bill->billDetail as $d)
		<cfdi:Concepto ClaveProdServ="{{$d->keyProdServ}}" @if($bill->type == 'N') Cantidad="1" @else Cantidad="{{$d->quantity}}" @endif ClaveUnidad="{{$d->keyUnit}}" Descripcion="{{$d->description}}" ValorUnitario="{{$d->value}}" Importe="{{$d->amount}}" @if($bill->type != 'P') Descuento="{{round($d->discount,2)}}" @endif @if($bill->version == '4.0') ObjetoImp="{{$d->cat_tax_object_id}}" @endif>
		@if($d->taxes->count()>0)
			<cfdi:Impuestos>
			@if($d->taxesTras->count()>0)
				<cfdi:Traslados>
				@foreach($d->taxesTras as $t)
					<cfdi:Traslado Base="{{$t->base}}" Impuesto="{{$t->tax}}" TipoFactor="{{$t->quota}}" @if($t->quota != 'Exento') TasaOCuota="{{$t->quotaValue}}" Importe="{{$t->amount}}" @endif></cfdi:Traslado>
					@php
						if(isset($trasCFDI[$t->tax][$t->quota][$t->base][$t->quotaValue]) && $t->quota != 'Exento')
						{
							$trasCFDI[$t->tax][$t->quota][$t->base][$t->quotaValue] += $t->amount;
						}
						elseif($t->quota != 'Exento')
						{
							$trasCFDI[$t->tax][$t->quota][$t->base][$t->quotaValue] = $t->amount;
						}
					@endphp
				@endforeach
				</cfdi:Traslados>
			@endif
			@if($d->taxesRet->count()>0)
				<cfdi:Retenciones>
				@foreach($d->taxesRet as $r)
					<cfdi:Retencion Base="{{$r->base}}" Impuesto="{{$r->tax}}" TipoFactor="{{$r->quota}}" TasaOCuota="{{$r->quotaValue}}" Importe="{{$r->amount}}"></cfdi:Retencion>
					@php
						if(isset($retCFDI[$r->tax][$r->base]))
						{
							$retCFDI[$r->tax][$r->base] += $r->amount;
						}
						else
						{
							$retCFDI[$r->tax][$r->base] = $r->amount;
						}
					@endphp
				@endforeach
				</cfdi:Retenciones>
			@endif
			</cfdi:Impuestos>
		@endif
		</cfdi:Concepto>
	@endforeach
	</cfdi:Conceptos>
@if($bill->type == 'I' || $bill->type == 'E')
	<cfdi:Impuestos @if(count($retCFDI)>0) TotalImpuestosRetenidos="{{$bill->ret}}" @endif @if(count($trasCFDI)>0) TotalImpuestosTrasladados="{{$bill->tras}}" @endif>
	@if(count($retCFDI)>0)
		<cfdi:Retenciones>
			@foreach($retCFDI as $kImp => $kBase)
				@foreach($kBase as $base => $imp)
					<cfdi:Retencion Impuesto="{{$kImp}}" Importe="{{round($imp,2)}}"></cfdi:Retencion>
				@endforeach
			@endforeach
		</cfdi:Retenciones>
	@endif
	@if(count($trasCFDI)>0)
		<cfdi:Traslados>
			@foreach($trasCFDI as $kImp => $imp)
				@foreach($imp as $kTipFac => $tipFac)
					@if($bill->version == '4.0')
						@foreach($tipFac as $kBase => $base)
								@foreach($base as $kTasCuot => $tasCuot)
									<cfdi:Traslado Base="{{round($kBase,2)}}" Impuesto="{{$kImp}}" TipoFactor="{{$kTipFac}}" TasaOCuota="{{$kTasCuot}}" Importe="{{round($tasCuot,2)}}"></cfdi:Traslado>
								@endforeach
						@endforeach
					@else
						@php
							$trasOld = array();
							foreach($tipFac as $kBase => $base)
							{
								foreach ($base as $kTasCuot => $tasCuot)
								{
									if(isset($trasOld[$kTasCuot]))
									{
										$trasOld[$kTasCuot] += $tasCuot;
									}
									else
									{
										$trasOld[$kTasCuot] = $tasCuot;
									}
								}
							}
						@endphp
						@foreach($trasOld as $kTasCuot => $tasCuot)
							<cfdi:Traslado Impuesto="{{$kImp}}" TipoFactor="{{$kTipFac}}" TasaOCuota="{{$kTasCuot}}" Importe="{{round($tasCuot,2)}}"></cfdi:Traslado>
						@endforeach
					@endif
				@endforeach
			@endforeach
		</cfdi:Traslados>
	@endif
	</cfdi:Impuestos>
@endif
@if($bill->version == '4.0')
	@if($bill->type == 'P')
	@php
		$trasPayment = $retPayment = array();
		$taxesRetIVA = $taxesRetISR = $taxesRetIEPS = $taxesTrasBaseIVA16 = $taxesTrasIVA16 = $taxesTrasBaseIVA8 = $taxesTrasIVA8 = $taxesTrasBaseIVA0 = $taxesTrasIVA0 = $taxesTrasBaseIVAExento = 0;
	@endphp
	<cfdi:Complemento>
		<pago20:Pagos Version="2.0">
			@php
			foreach($bill->cfdiRelated as $rel)
			{
				if($rel->taxesRetIVA()->exists())
				{
					$taxesRetIVA += $rel->taxesRetIVA->sum('amount');
				}
				if($rel->taxesRetISR()->exists())
				{
					$taxesRetISR += $rel->taxesRetISR->sum('amount');
				}
				if($rel->taxesRetIEPS()->exists())
				{
					$taxesRetIEPS += $rel->taxesRetIEPS->sum('amount');
				}
				if($rel->taxesTrasIVA16()->exists())
				{
					$taxesTrasBaseIVA16 += $rel->taxesTrasIVA16->sum('base');
					$taxesTrasIVA16 += $rel->taxesTrasIVA16->sum('amount');
				}
				if($rel->taxesTrasIVA8()->exists())
				{
					$taxesTrasBaseIVA8 += $rel->taxesTrasIVA8->sum('base');
					$taxesTrasIVA8 += $rel->taxesTrasIVA8->sum('amount');
				}
				if($rel->taxesTrasIVA0()->exists())
				{
					$taxesTrasBaseIVA0 += $rel->taxesTrasIVA0->sum('base');
					$taxesTrasIVA0 += $rel->taxesTrasIVA0->sum('amount');
				}
				if($rel->taxesTrasIVAExento()->exists())
				{
					$taxesTrasBaseIVAExento += $rel->taxesTrasIVAExento->sum('base');
				}	
			}
			@endphp
			<pago20:Totales
				@if($taxesRetIVA > 0) TotalRetencionesIVA="{{ $taxesRetIVA }}" @endif
				@if($taxesRetISR > 0) TotalRetencionesISR="{{ $taxesRetISR }}" @endif
				@if($taxesRetIEPS > 0) TotalRetencionesIEPS="{{ $taxesRetIEPS }}" @endif
				@if($taxesTrasBaseIVA16 > 0)
					TotalTrasladosBaseIVA16="{{ $taxesTrasBaseIVA16 }}"
					TotalTrasladosImpuestoIVA16="{{ $taxesTrasIVA16 }}"
				@endif
				@if($taxesTrasBaseIVA8 > 0)
					TotalTrasladosBaseIVA8="{{ $taxesTrasBaseIVA8 }}"
					TotalTrasladosImpuestoIVA8="{{ $taxesTrasIVA8 }}"
				@endif
				@if($taxesTrasBaseIVA0 > 0)
					TotalTrasladosBaseIVA0="{{ $taxesTrasBaseIVA0 }}"
					TotalTrasladosImpuestoIVA0="{{ $taxesTrasIVA0 }}"
				@endif
				@if($taxesTrasBaseIVAExento > 0)
					TotalTrasladosBaseIVAExento="{{ $taxesTrasBaseIVAExento }}"
				@endif
				MontoTotalPagos="{{round($bill->cfdiRelated->sum('amount'),2)}}"
			></pago20:Totales>
			<pago20:Pago FechaPago="{{$bill->paymentComplement->first()->paymentDate}}T00:00:00" FormaDePagoP="{{$bill->paymentComplement->first()->paymentWay}}" MonedaP="{{$bill->paymentComplement->first()->currency}}" @if($bill->paymentComplement->first()->exchange != '') @if($bill->paymentComplement->first()->exchange == 1) TipoCambioP="1" @else TipoCambioP="{{$bill->paymentComplement->first()->exchange}}" @endif @endif Monto="{{$bill->paymentComplement->first()->amount}}" NumOperacion="1">
			@foreach($bill->cfdiRelated as $rel)
				<pago20:DoctoRelacionado IdDocumento="{{$rel->cfdi->uuid}}" MonedaDR="{{$rel->cfdi->currency}}" @if($bill->paymentComplement->first()->exchange != '') @if($bill->paymentComplement->first()->exchange != '') @if($bill->paymentComplement->first()->exchange == 1) EquivalenciaDR="1" @else EquivalenciaDR="{{$bill->paymentComplement->first()->exchange}}" @endif @endif @endif NumParcialidad="{{$rel->partial}}" ImpSaldoAnt="{{$rel->prevBalance}}" ImpPagado="{{$rel->amount}}" ImpSaldoInsoluto="{{$rel->unpaidBalance}}" ObjetoImpDR="{{$rel->cat_tax_object_id}}">
					@if($rel->taxes()->exists())
						<pago20:ImpuestosDR>
							@if($rel->taxesTras->count() > 0)
								<pago20:TrasladosDR>
									@foreach($rel->taxesTras as $p_tt)
										<pago20:TrasladoDR BaseDR="{{$p_tt->base}}" ImpuestoDR="{{ $p_tt->tax }}" TipoFactorDR="{{ $p_tt->quota }}" TasaOCuotaDR="{{ $p_tt->quotaValue }}" ImporteDR="{{ $p_tt->amount }}"></pago20:TrasladoDR>
										@php
											if(isset($trasPayment[$p_tt->tax][$p_tt->quota][$p_tt->base][$p_tt->quotaValue]) && $p_tt->quota != 'Exento')
											{
												$trasPayment[$p_tt->tax][$p_tt->quota][$p_tt->base][$p_tt->quotaValue] += $p_tt->amount;
											}
											elseif($p_tt->quota != 'Exento')
											{
												$trasPayment[$p_tt->tax][$p_tt->quota][$p_tt->base][$p_tt->quotaValue] = $p_tt->amount;
											}
										@endphp
									@endforeach
								</pago20:TrasladosDR>
							@endif
							@if($rel->taxesRet->count()>0)
								<pago20:RetencionesDR>
									@foreach($rel->taxesRet as $p_tr)
										<pago20:RetencionDR BaseDR="{{$p_tr->base}}" ImpuestoDR="{{ $p_tr->tax }}" TipoFactorDR="{{ $p_tr->quota }}" TasaOCuotaDR="{{ $p_tr->quotaValue }}" ImporteDR="{{ $p_tr->amount }}"></pago20:RetencionDR>
										@php
											if(isset($retPayment[$p_tr->tax][$p_tr->base]))
											{
												$retPayment[$p_tr->tax][$p_tr->base] += $p_tr->amount;
											}
											else
											{
												$retPayment[$p_tr->tax][$p_tr->base] = $p_tr->amount;
											}
										@endphp
									@endforeach
								</pago20:RetencionesDR>
							@endif
						</pago20:ImpuestosDR>
					@endif
				</pago20:DoctoRelacionado>
			@endforeach
			@if(count($trasPayment) > 0 || count($retPayment) > 0)
				<pago20:ImpuestosP>
					@if(count($trasPayment) > 0)
						<pago20:TrasladosP>
							@foreach($trasPayment as $kImp => $imp)
								@foreach($imp as $kTipFac => $tipFac)
									@foreach($tipFac as $kBase => $base)
										@foreach($base as $kTasCuot => $tasCuot)
											<pago20:TrasladoP BaseP="{{round($kBase,2)}}" ImpuestoP="{{$kImp}}" TipoFactorP="{{$kTipFac}}" TasaOCuotaP="{{$kTasCuot}}" ImporteP="{{round($tasCuot,2)}}"></pago20:TrasladoP>
										@endforeach
									@endforeach
								@endforeach
							@endforeach
						</pago20:TrasladosP>
					@endif
					@if(count($retPayment) > 0)
						<pago20:RetencionesP>
							@foreach($retPayment as $kImp => $kBase)
								@foreach($kBase as $base => $imp)
									<pago20:RetencionP ImpuestoP="{{$kImp}}" ImporteP="{{round($imp,2)}}"></pago20:RetencionP>
								@endforeach
							@endforeach
						</pago20:RetencionesP>
					@endif
				</pago20:ImpuestosP>
			@endif
			</pago20:Pago>
		</pago20:Pagos>
	</cfdi:Complemento>
	@endif
@else
	@if($bill->type == 'P')
		<cfdi:Complemento>
			<pago10:Pagos Version="1.0">
				<pago10:Pago FechaPago="{{$bill->paymentComplement->first()->paymentDate}}T00:00:00" FormaDePagoP="{{$bill->paymentComplement->first()->paymentWay}}" MonedaP="{{$bill->paymentComplement->first()->currency}}" @if($bill->paymentComplement->first()->exchange != '') TipoCambioP="{{$bill->paymentComplement->first()->exchange}}" @endif Monto="{{$bill->paymentComplement->first()->amount}}" NumOperacion="1">
		@foreach($bill->cfdiRelated as $rel)
					<pago10:DoctoRelacionado IdDocumento="{{$rel->cfdi->uuid}}" MonedaDR="{{$rel->cfdi->currency}}" MetodoDePagoDR="{{$rel->cfdi->paymentMethod}}" NumParcialidad="{{$rel->partial}}" ImpSaldoAnt="{{$rel->prevBalance}}" ImpPagado="{{$rel->amount}}" ImpSaldoInsoluto="{{$rel->unpaidBalance}}"></pago10:DoctoRelacionado>
		@endforeach
				</pago10:Pago>
			</pago10:Pagos>
		</cfdi:Complemento>
	@endif
@endif
@if($bill->type == 'N')
	<cfdi:Complemento>
		<nomina12:Nomina Version="1.2" FechaPago="{{$bill->nomina->paymentDate}}" FechaInicialPago="{{$bill->nomina->paymentStartDate}}" FechaFinalPago="{{$bill->nomina->paymentEndDate}}" NumDiasPagados="{{$bill->nomina->paymentDays}}" TipoNomina="{{$bill->nomina->type}}" @if($bill->nomina->perceptions>0) TotalPercepciones="{{$bill->nomina->perceptions}}" @endif @if($bill->nomina->deductions>0) TotalDeducciones="{{round($bill->nomina->deductions,2)}}" @endif @if($bill->nomina->nominaOtherPayment->count()>0) TotalOtrosPagos="{{$bill->nomina->other_payments}}" @endif>
		@if($bill->nominaReceiver->contractType_id != '09' && $bill->nominaReceiver->contractType_id != '10' && $bill->nominaReceiver->contractType_id != '99')
			<nomina12:Emisor RegistroPatronal="{{$bill->nomina->employer_register}}" />
		@endif
			<nomina12:Receptor Curp="{{$bill->nominaReceiver->curp}}" TipoContrato="{{$bill->nominaReceiver->contractType_id}}" TipoRegimen="{{$bill->nominaReceiver->regime_id}}" NumEmpleado="{{$bill->nominaReceiver->employee_id}}" PeriodicidadPago="{{$bill->nominaReceiver->periodicity}}" ClaveEntFed="{{$bill->nominaReceiver->c_state}}" @if($bill->nominaReceiver->contractType_id != '09' && $bill->nominaReceiver->contractType_id != '10' && $bill->nominaReceiver->contractType_id != '99') NumSeguridadSocial="{{$bill->nominaReceiver->nss}}" FechaInicioRelLaboral="{{$bill->nominaReceiver->laboralDateStart}}" Antigüedad="{{$bill->nominaReceiver->antiquity}}" RiesgoPuesto="{{$bill->nominaReceiver->job_risk}}" SalarioDiarioIntegrado="{{$bill->nominaReceiver->sdi}}" @endif/>
		@if($bill->nomina->nominaPerception->count()>0)
			@php
				$salary				= round($bill->nomina->nominaPerception->whereNotIn('type',['022','023','025','039','044'])->sum('taxedAmount') + $bill->nomina->nominaPerception->whereNotIn('type',['022','023','025','039','044'])->sum('exemptAmount'),2);
				$indemnification	= round($bill->nomina->nominaPerception->whereIn('type',['022','023','025'])->sum('taxedAmount') + $bill->nomina->nominaPerception->whereIn('type',['022','023','025'])->sum('exemptAmount'),2);
				$retirement			= round($bill->nomina->nominaPerception->whereIn('type',['039','044'])->sum('taxedAmount') + $bill->nomina->nominaPerception->whereIn('type',['039','044'])->sum('exemptAmount'),2);
			@endphp
			<nomina12:Percepciones TotalGravado="{{$bill->nomina->nominaPerception->sum('taxedAmount')}}" TotalExento="{{$bill->nomina->nominaPerception->sum('exemptAmount')}}" @if($salary > 0) TotalSueldos="{{$salary}}" @endif @if($indemnification > 0) TotalSeparacionIndemnizacion="{{$indemnification}}" @endif @if($retirement > 0) TotalJubilacionPensionRetiro="{{$retirement}}" @endif>
			@foreach($bill->nomina->nominaPerception as $per)
				@if($per->perceptionKey == '019')
					<nomina12:Percepcion TipoPercepcion="{{$per->type}}" Clave="{{$per->perceptionKey}}" Concepto="{{$per->concept}}" ImporteGravado="{{$per->taxedAmount}}" ImporteExento="{{$per->exemptAmount}}">
						@foreach($bill->nomina->nominaExtraHours as $hour)
							<nomina12:HorasExtra Dias="{{$hour->days}}" TipoHoras="{{$hour->cat_type_hour_id}}" HorasExtra="{{$hour->hours}}" ImportePagado="{{$hour->amount}}" />
						@endforeach
					</nomina12:Percepcion>
				@else
					<nomina12:Percepcion TipoPercepcion="{{$per->type}}" Clave="{{$per->perceptionKey}}" Concepto="{{$per->concept}}" ImporteGravado="{{$per->taxedAmount}}" ImporteExento="{{$per->exemptAmount}}" />
				@endif
			@endforeach
			@if($bill->nomina->nominaIndemnification != '')
				<nomina12:SeparacionIndemnizacion TotalPagado="{{$bill->nomina->nominaIndemnification->total_paid}}" NumAñosServicio="{{$bill->nomina->nominaIndemnification->service_year}}" UltimoSueldoMensOrd="{{$bill->nomina->nominaIndemnification->last_ordinary_monthly_salary}}" IngresoAcumulable="{{$bill->nomina->nominaIndemnification->cumulative_income}}" IngresoNoAcumulable="{{$bill->nomina->nominaIndemnification->non_cumulative_income}}"/>
			@endif
			</nomina12:Percepciones>
		@endif
		@if($bill->nomina->nominaDeduction->count()>0)
			<nomina12:Deducciones @if($bill->nomina->nominaDeduction->where('type','!=','002')->sum('amount')>0)TotalOtrasDeducciones="{{$bill->nomina->nominaDeduction->where('type','!=','002')->sum('amount')}}" @endif @if($bill->nomina->nominaDeduction->where('type','002')->sum('amount')>0) TotalImpuestosRetenidos="{{$bill->nomina->nominaDeduction->where('type','002')->sum('amount')}}" @endif>
			@foreach($bill->nomina->nominaDeduction as $ded)
				<nomina12:Deduccion TipoDeduccion="{{$ded->type}}" Clave="{{$ded->deductionKey}}" Concepto="{{$ded->concept}}" Importe="{{$ded->amount}}" />
			@endforeach
			</nomina12:Deducciones>
		@endif
		@if($bill->nomina->nominaOtherPayment->count()>0)
			<nomina12:OtrosPagos>
			@foreach($bill->nomina->nominaOtherPayment as $otr)
				@if($otr->type == '002')
					<nomina12:OtroPago TipoOtroPago="{{$otr->type}}" Clave="{{$otr->otherPaymentKey}}" Concepto="{{$otr->concept}}" Importe="{{$otr->amount}}">
						<nomina12:SubsidioAlEmpleo SubsidioCausado="{{$otr->subsidy_caused}}" />
					</nomina12:OtroPago>
				@else
					<nomina12:OtroPago TipoOtroPago="{{$otr->type}}" Clave="{{$otr->otherPaymentKey}}" Concepto="{{$otr->concept}}" Importe="{{$otr->amount}}"/>
				@endif
			@endforeach
			</nomina12:OtrosPagos>
		@endif
		</nomina12:Nomina>
	</cfdi:Complemento>
@endif
</cfdi:Comprobante>
