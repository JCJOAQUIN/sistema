@if(isset($bill) && $bill->version == '3.3' && !isset($incomeBill))
	@component('components.labels.not-found', 
		[
			"text" => "El presente CFDI fue elaborado en la vesión <b>3.3</b> por lo que sólo se podrá timbrar hasta antes del <b>1 de enero del 2023;</b> después de esta fecha ya no podrá ser timbrado."
		])
	@endcomponent
@elseif(isset($bill) && isset($incomeBill) && $bill->version == '3.3')
	@component('components.labels.not-found',
		[
			"text" => "El presente CFDI será elaborado en la vesión <b>3.3</b> por lo que sólo se podrá timbrar hasta antes del <b>1 de enero del 2023;</b> después de esta fecha ya no podrá ser timbrado."
		])
	@endcomponent
@elseif(!isset($bill) && $cfdi_version == '3_3')
	@component('components.labels.not-found',
		[
			"text" => "El presente CFDI será elaborado en la vesión <b>3.3</b> por lo que sólo se podrá timbrar hasta antes del <b>1 de enero del 2023;</b> después de esta fecha ya no podrá ser timbrado."
		])
	@endcomponent
@endif
<div class="card">
	@component('components.labels.title-divisor')
		Generar CFDI @if(isset($bill)) {{ $bill->version }} @else {{ str_replace('_','.',$cfdi_version) }} @endif
	@endcomponent
	@if(isset($incomeBill) && $incomeBill)
	@else
		@if(isset($bill) && $bill->folioRequest != '')
			@component('components.containers.container-form')
				<div class="col-span-4 md:col-start-2 md:col-span-2 md:col-end-4">
					@component('components.labels.label')
						Folio de solicitud de ingreso:
					@endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							type="text" readonly value="{{$bill->folioRequest}}" placeholder="Ingrese el folio de solicitud"
						@endslot
					@endcomponent
				</div>
			@endcomponent
		@else
			@component('components.labels.subtitle') Proyecto @endcomponent
			@component('components.containers.container-form')
				<div class="col-span-4 md:col-start-2 md:col-span-2 md:col-end-4">
					@component('components.labels.label') Proyecto: @endcomponent
					@php
						$optionProyect = [];
						if(isset($bill))
						{
							$project = App\Project::whereIn('status',[1,2])
								->where('idproyect',$bill->idProject)
								->first();

							if($project != '')
							{
								$optionProyect[] = ["value" => $project->idproyect, "description" => $project->proyectName, "selected" => "selected"];
							}
						}
					@endphp
					@component('components.inputs.select', ["options" => $optionProyect ])
						@slot('attributeEx')
							name="project_id" id="project_selector" multiple data-validation="required" placeholder="Ingrese el proyecto"
						@endslot
					@endcomponent
				</div>
			@endcomponent
		@endif
	@endif
	@component('components.labels.subtitle') Emisor @endcomponent
	@component('components.containers.container-form')
		@php
			$optionSelect	= '';
			$rfc			= '';
			$businessName	= '';
			$taxRegime		= '';
			if(isset($incomeBill) && $incomeBill)
			{
				if(isset($requestModel))
				{
					$enterprises = App\Enterprise::orderName()->where('status','ACTIVE')->where('id',$requestModel->idEnterprise)->get();
				}
				elseif(isset($originEnterprise))
				{
					$enterprises = App\Enterprise::orderName()->where('status','ACTIVE')->where('id',$originEnterprise)->get();
				}
				else
				{
					$enterprises = [];
				}
			}
			else
			{
				$enterprises = App\Enterprise::orderName()->where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeEnt($option_id)->pluck('enterprise_id'))->get();
			}
		@endphp
		@foreach($enterprises as $e)
			@php
				if($loop->first)
				{
					$rfc			= $e->rfc;
					$businessName	= $e->name;
					$taxRegime		= ((App\CatTaxRegime::where('taxRegime',$e->taxRegime)->exists())? $e->taxRegime.' - '.App\CatTaxRegime::where('taxRegime',$e->taxRegime)->first()->description : '');
				}
				$optionSelect .= '<option value="'.$e->rfc.'" data-tax-regime="'.((App\CatTaxRegime::where('taxRegime',$e->taxRegime)->exists())? $e->taxRegime.' - '.App\CatTaxRegime::where('taxRegime',$e->taxRegime)->first()->description : '').'" '.((isset($bill) && $bill->rfc == $e->rfc) ? 'selected' : '').'>'.$e->name.'</option>';
				if(isset($bill) && $bill->rfc == $e->rfc)
				{
					$rfc			= $e->rfc;
					$businessName	= $e->name;
					$taxRegime		= ((App\CatTaxRegime::where('taxRegime',$e->taxRegime)->exists())? $e->taxRegime.' - '.App\CatTaxRegime::where('taxRegime',$e->taxRegime)->first()->description : '');
				}
			@endphp
		@endforeach
		<div class="col-span-2">
			@component('components.labels.label') *RFC: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" readonly data-validation="required" name="rfc_emitter" value="{{$rfc}}"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') *Razón social: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="hidden" name="business_name_emitter" value="{{$businessName}}"
				@endslot
			@endcomponent
			<select class="js-enterprise" multiple id="enterprise_selector">
				{!!$optionSelect!!}
			</select>
		</div>
		<div class="col-span-2">
			@component('components.labels.label') *Régimen fiscal: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" data-validation="required" name="tax_regime_cfdi" data-validation-error-msg="La empresa no tiene configurado un Régimen Fiscal y no se podrá proceder" readonly value="{{$taxRegime}}"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Dirección fiscal: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="issuer_address_cfdi" @if(isset($bill)) value="{{$bill->issuer_address}}" @endif  placeholder="Ingrese la dirección fiscal"
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
					type="text" data-validation="custom required" placeholder="Ingrese el RFC" name="rfc_receiver" data-validation-regexp="^([A-ZÑ&]{3,4}) ?(?:- ?)?(\d{2}(?:0[1-9]|1[0-2])(?:0[1-9]|[12]\d|3[01])) ?(?:- ?)?([A-Z\d]{2})([A\d])$" data-validation-error-msg="Por favor, ingrese un RFC válido" @if(isset($bill)) value="{{$bill->clientRfc}}" @endif
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') *Razón social: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" data-validation="required" placeholder="Ingrese la razón social" name="business_name_receiver" @if(isset($bill)) value="{{$bill->clientBusinessName}}" @endif
				@endslot
			@endcomponent
		</div>
		@if((isset($bill) && $bill->version == '4.0') || (!isset($bill) && $cfdi_version == '4_0'))
			<div class="col-span-2">
				@component('components.labels.label') *Régimen fiscal: @endcomponent
				@php
					$optionRegime = [];
					if(isset($bill))
					{
						$kindReceiver = '';
						if(strlen($bill->clientRfc) == 13)
						{
							$kindReceiver = 'physical';
						}
						elseif(strlen($bill->clientRfc) == 12)
						{
							$kindReceiver = 'moral';
						}
						if($kindReceiver != '')
						{
							foreach(App\CatTaxRegime::where($kindReceiver,'Sí')->get() as $regime)
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
						}
					}
				@endphp
				@component('components.inputs.select', ["options" => $optionRegime])
					@slot('attributeEx')
						name="regime_receiver" multiple id="regime_receiver"
					@endslot
					@slot('classEx')
						js-regime
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') *Domicilio fiscal (CP): @endcomponent
				@php
					$optionCP = [];
					if(isset($bill) && $bill->receiver_zip_code != '')
					{ 
						$optionCP[] = ["value" => $bill->receiver_zip_code, "description" => $bill->receiver_zip_code, "selected" => "selected"];
					}
				@endphp
				@component('components.inputs.select',["options" => $optionCP])
					@slot('attributeEx')
						id="cp_receiver_cfdi" name="cp_receiver_cfdi" data-validation="required" multiple placeholder="Ingrese el domicilio fiscal"
					@endslot
				@endcomponent
			</div>
		@endif
		<div class="col-span-2">
			@component('components.labels.label') Dirección fiscal: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="receiver_address_cfdi" @if(isset($bill)) value="{{$bill->receiver_address}}" @endif placeholder="Ingrese la dirección fiscal"
				@endslot
			@endcomponent
		</div>
	@endcomponent
	@component('components.containers.container-form')
		<div class="col-span-2">
			@component('components.labels.label') *Uso de CFDI: @endcomponent
			@php
				$optionCFDI = [];
				if(isset($bill) && $bill->clientRfc != '')
				{
					$kind = strlen($bill->clientRfc) == 12 ? 'moral' : 'physical';
					$useCDFI = App\CatUseVoucher::orderName()->where($kind,'Sí');
					foreach($useCDFI->get() as $u)
					{
						if($bill->useBill==$u->useVoucher)
						{
							$optionCFDI[] = ["value" => $u->useVoucher, "description" => $u->useVoucher.' - '.$u->description, "selected" => "selected"];
						}
						else
						{
							$optionCFDI[] = ["value" => $u->useVoucher, "description" => $u->useVoucher.' - '.$u->description];
						}
					}
				}
			@endphp
			@component('components.inputs.select',["options" => $optionCFDI])
				@slot('attributeEx')
					multiple="multiple" name="cfdi_use" data-validation="required" @if(isset($bill) && $bill->type == 'P') disabled @endif
				@endslot
				@slot('classEx')
					js-cfdi
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') *Tipo de CFDI: @endcomponent
			@php
				$optionTypeCFDI = [];
				foreach(App\CatTypeBill::orderName()->where('typeVoucher','!=','N')->get() as $k)
				{
					if(isset($bill) && $bill->type == $k->typeVoucher)
					{
						$optionTypeCFDI[] = ["value" => $k->typeVoucher, "description" => $k->description, "selected" => "selected"]; 
					}
					elseif(!isset($bill) && $k->typeVoucher == 'I')
					{
						$optionTypeCFDI[] = ["value" => $k->typeVoucher, "description" => $k->description, "selected" => "selected"]; 
					}
					else
					{
						$optionTypeCFDI[] = ["value" => $k->typeVoucher, "description" => $k->description]; 
					}
				}
			@endphp
			@component('components.inputs.select',["options" => $optionTypeCFDI])
				@slot('attributeEx')
					name="cfdi_kind" multiple data-validation="required"
				@endslot
				@slot('classEx')
					js-cfdi-type
				@endslot
			@endcomponent
		</div>
		@if((isset($bill) && $bill->version == '4.0') || (!isset($bill) && $cfdi_version == '4_0'))
			<div class="col-span-2">
				@component('components.labels.label') *Exportación: @endcomponent
				@php
					foreach(App\CatExport::get() as $e)
					{
						if(isset($bill) && $bill->export == $e->id)
						{
							$optionExportCFDI[] = ["value" => $e->id, "description" => $e->id.' - '.$e->description, "selected" => "selected"];
						}
						else
						{
							$optionExportCFDI[] = ["value" => $e->id, "description" => $e->id.' - '.$e->description];
						}
					}
				@endphp
				@component('components.inputs.select',["options" => $optionExportCFDI])
					@slot('attributeEx')
						name="cfdi_export" id="cfdi_export" multiple @if(isset($bill) && $bill->type == 'P') disabled @endif
					@endslot
					@slot('classEx')
						js-cfdi-export
					@endslot
				@endcomponent
			</div>
		@endif
		<div class="col-span-2">
			@component('components.labels.label') *Lugar de expedición (CP): @endcomponent
			@php
				$optionCPCFDI = [];
				if(isset($bill) && $bill->postalCode != '')
				{
					$optionCPCFDI[] = ["value" => $bill->postalCode, "description" => $bill->postalCode, "selected" => "selected"];
				}
			@endphp
			@component('components.inputs.select',["options" => $optionCPCFDI])
				@slot('attributeEx')
					id="cp_cfdi" name="cp_cfdi" data-validation="required" multiple placeholder="Ingrese el lugar de expedición"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') *Moneda: @endcomponent
			@php
				$optionCurrency = [];
				if(isset($bill) && $bill->currency == 'MXN' || (!isset($bill) && $cfdi_version == '4_0'))
				{
					$optionCurrency[] = ["value" => "MXN", "description" => "MXN - Peso Mexicano", "selected" => "selected"];
				}
				else
				{
					$optionCurrency[] = ["value" => "MXN", "description" => "MXN - Peso Mexicano"];
				}
				if(isset($bill) && $bill->currency == 'USD')
				{
					$optionCurrency[] = ["value" => "USD", "description" => "USD - Dolar americano", "selected" => "selected"];
				}
				else
				{
					$optionCurrency[] = ["value" => "USD", "description" => "USD - Dolar americano"];
				}
				if(isset($bill) && $bill->currency == 'XXX')
				{
					$optionCurrency[] = ["value" => "XXX", "description" => "XXX - Los códigos asignados para las transacciones en que intervenga ninguna moneda", "selected" => "selected", "attributeExOption" => "disabled"];
				}
				else
				{
					$optionCurrency[] = ["value" => "XXX", "description" => "XXX - Los códigos asignados para las transacciones en que intervenga ninguna moneda", "attributeExOption" => "disabled"];
				}
			@endphp
			@component('components.inputs.select', ["options" => $optionCurrency])
				@slot('attributeEx')
					multiple="multiple" name="currency_cfdi" data-validation="required" @if(isset($bill) && ($bill->type == 'P' || $bill->type == 'T')) disabled @endif 
				@endslot
				@slot('classEx')
					js-currency
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Tipo de cambio: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					name="exchange" type="text" @if(isset($bill)) value="{{$bill->exchange}}" @endif @if(isset($bill) && ($bill->type == 'P' || $bill->type == 'T')) disabled @endif placeholder="Ingrese el tipo de cambio"
				@endslot	
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') *Forma de pago: @endcomponent
			@php
				$optionPaymentWay = [];
				if(isset($bill) && $bill->type == 'P')
				{
					$optionPaymentWay[] = ["value" => "", "description" => "", "selected" => "selected", "disabled" => "disabled", "hidden"];
				}
				foreach(App\CatPaymentWay::orderName()->get() as $p)
				{
					if(isset($bill) && $bill->paymentWay==$p->paymentWay)
					{
						$optionPaymentWay[] = ["value" => $p->paymentWay, "description" => $p->paymentWay.' - '.$p->description, "selected" => "selected"];	
					}
					else if(!isset($bill) && $p->paymentWay == '01')
					{
						$optionPaymentWay[] = ["value" => $p->paymentWay, "description" => $p->paymentWay.' - '.$p->description, "selected" => "selected"];	
					}
					else
					{
						$optionPaymentWay[] = ["value" => $p->paymentWay, "description" => $p->paymentWay.' - '.$p->description];	
					}
				}
			@endphp
			@component('components.inputs.select', ["options" => $optionPaymentWay])
				@slot('attributeEx')
					multiple="multiple" name="cfdi_payment_way" @if(isset($bill) && $bill->type == 'P') disabled @endif data-validation="required"
				@endslot
				@slot('classEx')
					js-payment-way
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') *Método de pago: @endcomponent
			@php
				$optionPayment = [];
				if(isset($bill) && $bill->type == 'P')
				{
					$optionPayment[] = ["value" => "", "description" => "", "selected" => "selected", "disabled" => "disabled", "hidden"];	
				}
				foreach(App\CatPaymentMethod::orderName()->get() as $p)
				{
					if(isset($bill) && $bill->paymentMethod==$p->paymentMethod)
					{
						$optionPayment[] = ["value" => $p->paymentMethod, "description" => $p->paymentMethod.' - '.$p->description, "selected" => "selected"];	
					}
					else if(!isset($bill) && $p->paymentMethod=='PUE')
					{
						$optionPayment[] = ["value" => $p->paymentMethod, "description" => $p->paymentMethod.' - '.$p->description, "selected" => "selected"];	
					}
					else
					{
						$optionPayment[] = ["value" => $p->paymentMethod, "description" => $p->paymentMethod.' - '.$p->description];	
					}
				}
			@endphp
			@component('components.inputs.select', ["options" => $optionPayment])
				@slot('attributeEx')
					multiple="multiple" name="cfdi_payment_method" @if(isset($bill) && $bill->type == 'P') disabled @endif data-validation="required"
				@endslot
				@slot('classEx')
					js-payment-method
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Serie: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					name="serie" placeholder="Ingrese la serie" type="text" @if(isset($incomeBill) && $incomeBill && isset($requestModel)) value="{{serie($requestModel->bill->count()+1)}}" @elseif(isset($incomeBill) && $incomeBill && isset($request)) value="{{serie($request->bill->count()+1)}}" @else @if(isset($bill)) value="{{$bill->serie}}" @endif @endif
				@endslot	
			@endcomponent
		</div>
		@if(isset($incomeBill) && $incomeBill && isset($requestModel))
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="hidden" name="folio_request" value="{{ $requestModel->folio }}"
				@endslot	
			@endcomponent
		@elseif(isset($incomeBill) && $incomeBill && isset($request))
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="hidden" name="folio_request" value="{{ $request->folio }}"
				@endslot	
			@endcomponent
		@endif
		<div class="col-span-2">
			@component('components.labels.label') Condiciones de pago: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" placeholder="Ingrese las condiciones de pago" name="conditions" @if(isset($bill) && $bill->type == 'P') disabled @endif @if(isset($bill)) value="{{$bill->conditions}}" @endif
				@endslot	
			@endcomponent
		</div>
	@endcomponent
	<div class="modules w-full col-span-1 mb-4 p-6 border border-gray-400">
		<ul>
			<li>
				@component('components.inputs.switch')
					@slot('attributeEx')
						name="related_cfdi" 
						type="checkbox"
						value="1"
						id="related_cfdi" @if(isset($bill) && ($bill->related != '' || $bill->cfdiRelated()->exists())) checked @endif
					@endslot
					@slot('slot')
						CFDI relacionados
					@endslot	 
				@endcomponent
			</li>
		</ul>
		@component('components.buttons.button',[
			"variant" => "warning"
				])
			@slot('classEx')
				add-related
			@endslot
			@slot('attributeEx')
				type="button"
				data-toggle="modal" 
				data-target="#relatedCFDIModal"
				@if(isset($bill) && ($bill->related != '' || $bill->cfdiRelated()->exists()))  @else disabled @endif
			@endslot
			<span class="icon-plus"></span>
			<span>Agregar relación</span>
		@endcomponent
		<div class="related-cfdi-container">
			@if((isset($bill) && $bill->version == '4.0'))
				@if($bill->cfdiRelated()->exists())
					@foreach($bill->cfdiRelated->pluck('cat_relation_id','cat_relation_id') as $relKind)
						@php
							$body		= [];
							$modelBody 	= [];
							$modelHead 	= [
								[
									"content" =>[[ "label" => $relKind.' '.App\CatRelation::where('typeRelation',$relKind)->first()->description ]]
								]
							];

							foreach($bill->cfdiRelated->where('cat_relation_id',$relKind) as $rel)
							{
								$body = [
									[
										"content" => 
										[
											[
												"label" => $rel->cfdi->uuid
											],
											[
												"kind"			=> "components.inputs.input-text",
												"attributeEx"	=> "type=\"hidden\" name=\"cfdi_related_id[]\" value=\"".$rel->idRelated."\""
											],
											[
												"kind"			=> "components.inputs.input-text",
												"attributeEx"	=> "type=\"hidden\" name=\"cfdi_related_kind[]\" value=\"".$relKind."\""
											]
										]
									]
								];
								$modelBody[] = $body;
							}
						@endphp
						@component('components.tables.alwaysVisibleTable',[
								"modelHead" 		=> $modelHead,
								"modelBody"			=> $modelBody,
								"variant"			=> "default",
								"classExContainer"	=> "md:py-0"
							])
						@endcomponent
					@endforeach
				@endif
			@elseif(isset($bill))
				@if($bill->related != '')
					@php
						$body		= [];
						$modelBody	= [];
						$modelHead	= [
							[
								"content" =>
								[
									[ "label" => $bill->related.' '.$bill->relationKind->description ],
									[
										"kind"			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"hidden\" name=\"related_kind_cfdi\" value=\"".$bill->related."\""
									]
								]
							]
						];
						foreach($bill->cfdiRelated as $rel)
						{
							$body = [
								[
									"content" =>
									[
										[ "label" => $rel->cfdi->uuid ],
										[
											"kind"			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"hidden\" name=\"cfdi_related_id[]\" value=\"".$rel->idRelated."\""
										]
									]
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
	</div>
	<div id="cfdi-concepts" @if(isset($bill) && $bill->type == 'P') hidden @endif>
		@php
			$body 		= [];
			$modelBody 	= [];
			$modelHead	= 
			[
				[
					["value" => "*Clave de producto o servicio"],
					["value" => "*Clave de unidad"]
				]
			];
			if((isset($bill) && $bill->version == '4.0') || (!isset($bill) && $cfdi_version == '4_0'))
			{
				array_push($modelHead[0], ["value" => "*Objeto de impuesto"]);
			}
			array_push($modelHead[0], ["value" => "*Cantidad"]);
			array_push($modelHead[0], ["value" => "*Descripción"]);
			array_push($modelHead[0], ["value" => "*Valor unitario"]);
			array_push($modelHead[0], ["value" => "*Importe"]);
			array_push($modelHead[0], ["value" => "*Descuento"]);
			array_push($modelHead[0], ["value" => "Acción"]);

			$body = [ "classEx" => "tr_body",
				[
					"content" =>
					[
						"kind"				=> "components.inputs.select",
						"attributeEx"		=> "id=\"cfdi-product-id\" multiple",
						"classExContainer"	=> "w-40"
					]
				],
				[


					"content" =>
					[
						"kind"				=> "components.inputs.select",
						"attributeEx"		=> "id=\"cfdi-unity-id\" multiple",
						"classExContainer"	=> "w-40"
					]
				]
			];
			if((isset($bill) && $bill->version == '4.0') || (!isset($bill) && $cfdi_version == '4_0'))
			{
				$optionTax = [];
				foreach(App\CatTaxObject::all() as $obj)
				{
					$optionTax[] = ["value" => $obj->id, "description" => $obj->id.' - '.$obj->description];
				}
				array_push($body, [
					"content" =>
					[
						"kind"			=> "components.inputs.select",
						"attributeEx"	=> "id=\"cfdi-tax-object-id\" multiple",
						"options"		=> $optionTax,
						"classEx"		=> "js-tax-object w-40"
					]
				]);
			}
			array_push($body, [
				"content" =>
				[
					"kind"			=> "components.inputs.input-text",
					"attributeEx"	=> "type=\"text\" id=\"cfdi-quantity\" placeholder=\"Ingrese la cantidad\"",
					"classEx"		=> "w-40",
				]
			]);
			array_push($body, [
				"content" =>
				[
					"kind"			=> "components.inputs.input-text",
					"attributeEx"	=> "type=\"text\" id=\"cfdi-description\" placeholder=\"Ingrese la descripción\"",
					"classEx"		=> "w-40",
				]
			]);
			array_push($body, [
				"content" =>
				[
					"kind"			=> "components.inputs.input-text",
					"attributeEx"	=> "type=\"text\" id=\"cfdi-value\" placeholder=\"Ingrese el valor\"",
					"classEx"		=> "w-40",
				]
			]);
			array_push($body, [
				"content" =>
				[
					"kind"			=> "components.inputs.input-text",
					"attributeEx"	=> "type=\"text\" readonly id=\"cfdi-total\" value=\"0\"",
					"classEx"		=> "w-40",
				]
			]);			array_push($body, [


			
				"content" =>
				[
					"kind"			=> "components.inputs.input-text",
					"attributeEx"	=> "type=\"text\" id=\"cfdi-discount\" value=\"0\"",
					"classEx"		=> "w-40",
				]
			]);
			array_push($body, [
				"content" =>
				[
					"kind"			=> "components.buttons.button",
					"variant"		=> "warning",
					"attributeEx"	=> "type=\"button\" id=\"cfdi-discount\" value=\"0\"",
					"classEx"		=> "add-cfdi-concept",
					"label"			=> "<span class=\"icon-plus\"></span>"
				]
			]);
			$modelBody[] = $body;
		@endphp
		@component('components.tables.table', [
			"modelBody" => $modelBody,
			"modelHead" => $modelHead,
		])
			@slot('classEx')
				table cfdi-concepts
			@endslot
		@endcomponent
		<div class="detail_taxes @if((isset($bill) && $bill->version == '4.0') || (!isset($bill) && $cfdi_version == '4_0')) hidden @endif">
			<div id="content_taxes" class="hidden w-full">
				@php
					$modelHead	= ["Tipo","Impuesto","¿Tasa o cuota?","Valor de la tasa o cuota","Importe","Acción"];
				@endphp
				@component('components.tables.alwaysVisibleTable',[
					"modelBody"			=> [],
					"modelHead"			=> $modelHead,
					"attributeExBody"	=> "id=\"CFDI_TAXES_BODY\""
				])
					@slot('classEx')
						table cfdi-concepts
					@endslot
					@slot('attributeEx')
						id="CFDI_TAXES"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2 md:col-span-4 grid justify-items-center md:justify-items-start">
				@component('components.buttons.button',[
						"variant" => "warning"
					])
					@slot('attributeEx')
						type="button"
					@endslot
					@slot('classEx')
						tax-add
					@endslot
					<span class="icon-plus"></span>
					<span>Agregar impuesto</span>
				@endcomponent
			</div>
		</div>
	</div>
	@if(isset($bill))
		@php
			$body = [];
			$modelHead = [
				["value" => "*Clave de producto o servicio"],
				["value" => "*Clave de unidad"]
			];
			if((isset($bill) && $bill->version == '4.0') || (!isset($bill) && $cfdi_version == '4_0'))
			{
				array_push($modelHead, ["value" => "*Objeto de impuesto"]);
			}
			array_push($modelHead, ["value" => "*Cantidad"]);
			array_push($modelHead, ["value" => "*Descripción"]);
			array_push($modelHead, ["value" => "*Valor unitario"]);
			array_push($modelHead, ["value" => "*Importe"]);
			array_push($modelHead, ["value" => "*Descuento"]);
			array_push($modelHead, ["value" => "Acciones"]);
			
			foreach($bill->billDetail as $d)
			{
				$modelBody	= [];
				$id_item	= round(microtime(true) * 1000);
				usleep(10);

				$body =
				[
					[
						"content" => 
						[
							[	"label" => $d->keyProdServ],
							[
								"kind"			=> "components.inputs.input-text",
								"attributeEx"	=> "name=\"product_id[]\" type=\"hidden\" readonly value=\"".$d->keyProdServ."\"".' '."data-text=\"".$d->cfdi_product->description."\""
							],
							[
								"kind"			=> "components.inputs.input-text",
								"attributeEx"	=> "name=\"cfdi_item[]\" type=\"hidden\" value=\"".$id_item."\""
							]
						]
					],
					[
						"content" =>
						[
							[	"label" => $d->keyUnit],
							[
								"kind"			=> "components.inputs.input-text",
								"attributeEx"	=> "name=\"unity_id[]\" type=\"hidden\" readonly value=\"".$d->keyUnit."\"".' '."data-text=\"".$d->cfdi_unity->name."\""
							]
						]
					]
				];
				if(isset($bill) && $bill->version == '4.0')
				{
					array_push($body, [
						"content" =>
						[
							[	"label" => $d->cat_tax_object_id],
							[
								"kind"			=> "components.inputs.input-text",
								"attributeEx"	=> "name=\"tax_object_id[]\" type=\"hidden\" readonly value=\"".$d->cat_tax_object_id."\""
							]
						]
					]);
				}
				array_push($body, [
					"content" =>
					[
						[	"label" => $d->quantity],
						[
							"kind"			=> "components.inputs.input-text",
							"attributeEx"	=> "name=\"quantity[]\" type=\"hidden\" readonly value=\"".$d->quantity."\""
						]
					]
				]);
				array_push($body, [
					"content" =>
					[
						[	"label" => $d->description],
						[
							"kind"			=> "components.inputs.input-text",
							"attributeEx"	=> "name=\"description[]\" type=\"hidden\" readonly value=\"".$d->description."\""
						]
					]
				]);
				array_push($body, [
					"content" =>
					[
						[	"label" => '$ '.number_format($d->value,2)],
						[
							"kind"			=> "components.inputs.input-text",
							"attributeEx"	=> "name=\"valueCFDI[]\" type=\"hidden\" readonly value=\"".$d->value."\""
						]
					]
				]);
				array_push($body, [
					"content" => 
					[
						[	"label" => '$ '.number_format($d->amount,2)],
						[
							"kind"			=> "components.inputs.input-text",
							"attributeEx"	=> "name=\"amount[]\" type=\"hidden\" readonly value=\"".$d->amount."\""
						]
					]
				]);
				array_push($body, [
					"content" => 
					[
						[	"label" => '$ '.number_format($d->discount,2)],
						[
							"kind"			=> "components.inputs.input-text",
							"attributeEx"	=> "name=\"discount[]\" type=\"hidden\" readonly value=\"".$d->discount."\""
						]
					]
				]);
				if($bill->type != 'P')
				{
					array_push($body, [
						"content" => 
						[
							[
								"kind"			=> "components.buttons.button",
								"variant"		=> "red",
								"attributeEx"	=> "type=\"button\"",
								"label"			=> "<span class=\"icon-x\"></span>",
								"classEx"		=> "cfdi-concept-delete"
							],
							[
								"kind"			=> "components.buttons.button",
								"variant"		=> "success",
								"attributeEx"	=> "type=\"button\"",
								"label"			=> "<span class=\"icon-pencil\"></span>",
								"classEx"		=> "cfdi-concept-modify"
							],
						]
					]);
				}
				else
				{
					array_push($body, [
						"content" => 
						[
							[
								"label" => ""
							]
						]
					]);
				}
				$modelBody[] = $body;

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
									["label" => $ret->cfdiTax->description],
									[
										"kind"			=> "components.inputs.input-text",
										"attributeEx"	=> "name=\"ret[$id_item][]\" type=\"hidden\" value=\"".$ret->tax."\""
									]
								],
							],
							[
								"content" => 
								[
									[	"label" => $ret->quota],
									[
										"kind"			=> "components.inputs.input-text",
										"attributeEx"	=> "name=\"ret_fee[$id_item][]\" type=\"hidden\" readonly value=\"".$ret->quota."\""
									]
								],
							],
							[
								"content" => 
								[
									[	"label" => '$ '.number_format($ret->quotaValue,2)],
									[
										"kind"			=> "components.inputs.input-text",
										"attributeEx"	=> "name=\"ret_tax_fee[$id_item][]\" type=\"hidden\" readonly value=\"".$ret->quotaValue."\""
									]
								],
							],
							[
								"content" => 
								[
									[	"label" => '$ '.number_format($ret->amount,2)],
									[
										"kind"			=> "components.inputs.input-text",
										"attributeEx"	=> "name=\"ret_total_tax[$id_item][]\" type=\"hidden\" readonly value=\"".$ret->amount."\""
									]
								],
							],
						];
						$taxesRetention[] = $taxRetention; 
					}
				}

				$taxesTransfer = [];
				if($d->taxesTras->count()>0)
				{
					foreach($d->taxesTras as $tras)
					{
						$taxTranslation =
						[
							[
								"content" =>
								[
									[	"label" => $tras->cfdiTax->description ],
									[
										"kind"			=> "components.inputs.input-text",
										"attributeEx"	=> "name=\"tras[$id_item][]\" type=\"hidden\" value=\"".$tras->tax."\""
									]
								],
							],
							[
								"content" => 
								[
									[	"label" => $tras->quota],
									[
										"kind"			=> "components.inputs.input-text",
										"attributeEx"	=> "name=\"tras_fee[$id_item][]\" type=\"hidden\" readonly value=\"".$tras->quota."\""
									]
								],
							],
							[
								"content" => 
								[
									[	"label" => '$ '.number_format($tras->quotaValue,2) ],
									[
										"kind"			=> "components.inputs.input-text",
										"attributeEx"	=> "name=\"tras_tax_fee[$id_item][]\" type=\"hidden\" readonly value=\"".$tras->quotaValue."\""
									]
								],
							],
							[
								"content" => 
								[
									[	"label" => '$ '.number_format($tras->amount,2) ],
									[
										"kind"			=> "components.inputs.input-text",
										"attributeEx"	=> "name=\"tras_total_tax[$id_item][]\" type=\"hidden\" readonly value=\"".$tras->amount."\""
									]
								],
							],
						];
						$taxesTransfer[] = $taxTranslation;
					}
				}
			@endphp
				@if(!empty(json_decode(json_encode($bill->billDetail))))
					@component("components.tables.table-addTaxes", ["modelHead" => $modelHead, "modelBody" => $modelBody, "taxesRetention" => $taxesRetention, "taxesTransfer" => $taxesTransfer])
						@slot('classEx')
							my-4
							table 
							cfdi-concepts 
							cfdi-concepts-taxes
						@endslot	
					@endcomponent
				@endif
		@php
			}
		@endphp
	@endif
	<div id="body_cfdi_taxes" class="hidden my-4"></div>
	<div id="body_cfdi_concepts" class="hidden my-4"></div>
	@component('components.containers.container-form')	
		<div class="col-span-2">
			@component('components.labels.label') *Subtotal: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" readonly name="subtotal" @if(isset($bill)) value="{{$bill->subtotal}}" @endif placeholder="Ingrese el subtotal"
				@endslot	
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Descuento: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" readonly name="discount_cfdi" @if(isset($bill)) value="{{$bill->discount}}" @endif placeholder="Ingrese el descuento"
				@endslot	
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Total de impuestos trasladados: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" readonly name="tras_total" @if(isset($bill)) value="{{$bill->tras}}" @endif placeholder="Ingrese el total de impuestos trasladados"
				@endslot	
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Total de impuestos retenidos: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" readonly name="ret_total" @if(isset($bill)) value="{{$bill->ret}}" @endif placeholder="Ingrese el total de impuestos retenidos"
				@endslot	
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') *Total: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" readonly name="cfdi_total" @if(isset($bill)) value="{{$bill->total}}" @endif placeholder="Ingrese el total"
				@endslot	
			@endcomponent
		</div>
	@endcomponent
	<div class="card payments-receipt @if(isset($bill) && $bill->type == 'P') block @else hidden @endif">
		@component('components.labels.subtitle') 
			@slot('classExContainer')
				my-4
			@endslot 
			Recepción de pagos
		@endcomponent
		@component('components.containers.container-form')
			<div class="col-span-2">
				@php
					$dateComplement = '';
					if(isset($bill) && $bill->paymentComplement->count()>0)
					{
						$dateComplement = isset($bill->paymentComplement->first()->paymentDate) ? Carbon\Carbon::createFromFormat('Y-m-d',$bill->paymentComplement->first()->paymentDate)->format('d-m-Y') : '';	 
					}
				@endphp
				@component('components.labels.label') *Fecha de pago: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" data-validation="required" placeholder="Ingrese la fecha" name="cfdi_payment_date" @if(isset($bill) && $bill->paymentComplement->count()>0) value="{{$dateComplement}}" @endif
					@endslot	
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') *Forma de pago: @endcomponent
				@php
					$optionPayment = [];
					foreach(App\CatPaymentWay::orderName()->get() as $p)
					{
						if(isset($bill) && $bill->paymentComplement->count()>0 && $bill->paymentComplement->first()->paymentWay==$p->paymentWay)
						{
							$optionPayment[] = ["value" => $p->paymentWay, "description" => $p->paymentWay.' '.$p->description, "selected" => "selected"];
						}
						else
						{
							$optionPayment[] = ["value" => $p->paymentWay, "description" => $p->paymentWay.' '.$p->description];
						}
					}
				@endphp
				@component('components.inputs.select',["options" => $optionPayment])
					@slot('attributeEx')
						name="cfdi_payment_payment_way" multiple data-validation="required"
					@endslot
					@slot('classEx')
						js-payment
					@endslot	
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') *Moneda: @endcomponent
				@php
					$optionCurrencyPay = [];
					if(isset($bill) && $bill->paymentComplement->count()>0 && $bill->paymentComplement->first()->currency == 'MXN')
					{
						$optionCurrencyPay[] = ["value" => "MXN", "description" => "MXN - Peso Mexicano", "selected" => "selected"];
					}
					else
					{
						$optionCurrencyPay[] = ["value" => "MXN", "description" => "MXN - Peso Mexicano"];
					}
					if(isset($bill) && $bill->paymentComplement->count()>0 && $bill->paymentComplement->first()->currency == 'USD')
					{
						$optionCurrencyPay[] = ["value" => "USD", "description" => "USD - Dolar americano", "selected" => "selected"];
					}
					else
					{
						$optionCurrencyPay[] = ["value" => "USD", "description" => "USD - Dolar americano"];
					}
				@endphp
				@component('components.inputs.select', ["options" => $optionCurrencyPay])
					@slot('attributeEx')
						name="cfdi_payment_currency" multiple data-validation="required"
					@endslot
					@slot('classEx')
						js-currency
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Tipo de cambio: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" placeholder="Ingrese el tipo de cambio" name="cfdi_payment_exchange" data-validation="number" data-validation-optional="true" data-validation-allowing="float" @if(isset($bill) && $bill->paymentComplement->count()>0) value="{{$bill->paymentComplement->first()->exchange}}" @else value="1" @endif
					@endslot	
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') *Monto: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" name="cfdi_payment_amount" placeholder="Ingrese el monto" data-validation="number required" data-validation-allowing="float" @if(isset($bill) && $bill->paymentComplement->count()>0) value="{{$bill->paymentComplement->first()->amount}}" @endif
					@endslot	
				@endcomponent
			</div>
		@endcomponent
		<div class="bg-orange-500 w-full text-white text-center font-semibold mt-2"> Documentos relacionados </div>
		@php
			$body		= [];
			$modelBody	= [];
			$modelHead	= 
			[
				[
					["value" => "UUID"],
					["value" => "Serie"],
					["value" => "Folio"],
					["value" => "Moneda"],
					["value" => "Método de pago"],
					["value" => "Número de parcialidad"],
					["value" => "Importe de saldo anterior"],
					["value" => "Importe pagado"],
					["value" => "Importe de saldo insoluto"]
				]
			];
			if((isset($bill) && $bill->version == '4.0') || (!isset($bill) && $cfdi_version == '4_0'))
			{
				$modelHead[0][] = ["value" => "Objeto de impuesto"];
				$modelHead[0][] = ["value" => "Acción"];
			}

			if(isset($bill) && $bill->type == 'P' && ($bill->related != '' || $bill->cfdiRelated()->exists()))
			{
				foreach($bill->cfdiRelated as $rel)
				{
					$body = [ "classEx" => "tr_related_payments",
						[
							"content" =>
							[
								[
									"label" => $rel->cfdi->uuid
								],
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" name=\"cfdi_payment_related_id[]\" value=\"".$rel->idRelated."\""
								]
							]
						],
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
								"label" => $rel->cfdi->currency.' '.$rel->cfdi->cfdiCurrency->description
							]
						],
						[
							"content" =>
							[
								"label" => $rel->cfdi->paymentMethod.' '.$rel->cfdi->cfdiPaymentMethod->description
							]
						],
						[
							"content" =>
							[
								"kind"			=> "components.inputs.input-text",
								"attributeEx"	=> "type=\"text\" name=\"cfdi_payment_partial_number[]\" data-validation=\"number\" value=\"".$rel->partial."\""
							]
						],
						[
							"content" =>
							[
								"kind"			=> "components.inputs.input-text",
								"attributeEx"	=> "type=\"text\" name=\"cfdi_payment_last_amount[]\" data-validation=\"number\" data-validation-allowing=\"float\" value=\"".$rel->prevBalance."\""
							]
						],
						[
							"content" =>
							[
								"kind"			=> "components.inputs.input-text",
								"attributeEx"	=> "type=\"text\" name=\"cfdi_payment_comp_amount[]\" data-validation=\"number\" data-validation-allowing=\"float\" value=\"".$rel->amount."\""
							]
						],
						[
							"content" =>
							[
								"kind"			=> "components.inputs.input-text",
								"attributeEx"	=> "type=\"text\" name=\"cfdi_payment_insolute[]\" data-validation=\"number\" data-validation-allowing=\"float\" value=\"".$rel->unpaidBalance."\""
							]
						]
					];
					if((isset($bill) && $bill->version == '4.0') || (!isset($bill) && $cfdi_version == '4_0'))
					{
						$optionPaymentObj = [];
						foreach(App\CatTaxObject::all() as $obj)
						{
							if($rel->cat_tax_object_id == $obj->id)
							{
								$optionPaymentObj[] = ["value" => $obj->id, "description" => $obj->id.' - '.$obj->description, "selected" => "selected"];
							}
							else
							{
								$optionPaymentObj[] = ["value" => $obj->id, "description" => $obj->id.' - '.$obj->description];
							}
						}
						array_push($body,[
							"content" =>
							[
								"kind"			=> "components.inputs.select",
								"attributeEx"	=> "name=\"cfdi_payment_objeto_imp[]\" multiple data-validation=\"required\"",
								"options" 		=> $optionPaymentObj
							]
						]);
						$tmpTax = [];
						foreach($rel->taxes as $key => $p_tax)
						{
							$tmpTax[$key]['base']      = $p_tax->base;
							$tmpTax[$key]['fee']       = $p_tax->quota;
							$tmpTax[$key]['tax_fee']   = $p_tax->quotaValue;
							$tmpTax[$key]['tax_total'] = $p_tax->amount;
							$tmpTax[$key]['tax_name']  = $p_tax->tax;
							$tmpTax[$key]['tax_kind']  = $p_tax->type;
						}
						$relhidden = '';
						if($rel->cat_tax_object_id == '01' || $rel->cat_tax_object_id == '')
						{
							$relhidden = "hidden add-payment-taxes";
						}
						else
						{
							$relhidden = "add-payment-taxes";
						}
						array_push($body,[
							"content" =>
							[
								[
									"kind" 			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" name=\"cfdi_payment_related_taxes[]\" value=\"".htmlentities(json_encode($tmpTax))."\""
								],
								[
									"kind" 			=> "components.buttons.button",
									"variant" 		=> "warning",
									"attributeEx"	=> "type=\"button\"",
									"classEx"		=> $relhidden,
									"label"			=> "<span class=\"icon-plus\"></span>"
								]
							]
						]);
					}
					$modelBody[] = $body;
				}
			}
		@endphp
		@component('components.tables.table',[
			"modelBody" => $modelBody,
			"modelHead" => $modelHead
		])
			@slot('classEx')
				table related-payments-cfdi
			@endslot
			@slot('classExBody')
				related-payments
			@endslot
		@endcomponent
	</div>
	@if(isset($incomeBill) && $incomeBill)
	@else
		<div class="modules w-full col-span-1 my-4 p-6 border border-gray-400"">
			<ul>
				<li>
					@component('components.inputs.switch')
						@slot('attributeEx')
							name="send_email_cfdi"
							type="checkbox"
							value="1"
							id="email_cfdi"
						@endslot
						@slot('slot')
							Enviar por email
						@endslot 
					@endcomponent
				</li>
			</ul>
		</div>
	@endif
	@if(isset($incomeBill) && $incomeBill)
		<div class="text-center">
			@component('components.buttons.button',[
				"variant" => "primary"
					])
				@slot('attributeEx')
					type="submit"
				@endslot
					Guardar CFDI	
			@endcomponent
		</div>
	@else
		<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mb-2">
			@component('components.buttons.button',[
				"variant" => "primary"
					])
				@slot('attributeEx')
					type="submit" id="save_only"
				@endslot
					Guardar CFDI	
			@endcomponent
			@component('components.buttons.button',[
				"variant" => "success"
					])
				@slot('attributeEx')
					type="submit" @if(isset($bill)) formaction="{{route('bill.cfdi.stamp.saved',$bill->idBill)}}" @else formaction="{{route('bill.cfdi.stamp')}}" @endif
				@endslot
					Timbrar CFDI
			@endcomponent
		</div>
	@endif
</div>
