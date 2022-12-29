@if($cfdi_version != '4.0')
	@component('components.labels.label') Relación: @endcomponent
	@php
		$optionRelation = [];
		foreach(App\CatRelation::all() as $r)
		{
			$optionRelation[] = ["value" => $r->typeRelation, "description" => $r->typeRelation.' '.$r->description];
		}
	@endphp
	@component('components.inputs.select', ["options" => $optionRelation])
		@slot('attributeEx')
			id="cfdi_relation_kind" multiple="multiple"
		@endslot
		@slot('classEx')
			js-relation
		@endslot
	@endcomponent
@endif
@component('components.forms.form',["attributeEx" => "id=\"form-search-cfdi\""])
	@component('components.containers.container-form')
		@component('components.inputs.input-text')
			@slot('attributeEx')
				type="hidden" id="cfdi_related_page" name="page" value="1"
			@endslot	
		@endcomponent
		<div class="col-span-2">
			@component('components.labels.label') Fecha de timbrado: @endcomponent
			@php
				$inputs =
				[
					[
						"input_classEx"		=> "min-date-cfdi",
						"input_attributeEx" => "name=\"min_date_cfdi\" type=\"text\" placeholder=\"Desde\""
					],
					[
						"input_classEx"		=> "max-date-cfdi",
						"input_attributeEx" => "name=\"max_date_cfdi\" type=\"text\" placeholder=\"Hasta\""
					]
				];
			@endphp
			@component('components.inputs.range-input',["inputs" => $inputs]) @endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Emisor: @endcomponent
			@php
				$optionEmiter = [];
				if(isset($income))
				{
					$requestModel = App\RequestModel::find($income);
					switch ($requestModel->kind)
					{
						case 11:
							$enterprises = App\Enterprise::orderName()->where('status','ACTIVE')->where('id',$requestModel->adjustment->first()->enterpriseOrigin->id)->get();
							$rfcSelected = $requestModel->adjustment->first()->enterpriseOrigin->rfc;
							break;
						case 13:
							$enterprises = App\Enterprise::orderName()->where('status','ACTIVE')->where('id',$requestModel->purchaseEnterprise->first()->enterpriseDestiny->id)->get();
							$rfcSelected = $requestModel->purchaseEnterprise->first()->enterpriseDestiny->rfc;
							break;
						case 14:
							$enterprises = App\Enterprise::orderName()->where('status','ACTIVE')->where('id',$requestModel->groups->first()->enterpriseDestiny->id)->get();
							$rfcSelected = $requestModel->groups->first()->enterpriseDestiny->rfc;
							break;
						default:
							$enterprises = App\Enterprise::orderName()->where('status','ACTIVE')->where('id',$requestModel->idEnterprise)->get();
							$rfcSelected = $requestModel->enterprise()->first()->rfc;
							break;
					}
				}
				else
				{
					$enterprises = App\Enterprise::orderName()->where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeEnt($option_id)->pluck('enterprise_id'))->get();
				}
				foreach($enterprises as $e)
				{
					if(isset($requestModel) && $rfcSelected == $e->rfc)
					{
						$optionEmiter[] = ["value" => $e->rfc, "description" => $e->name, "selected" => "selected"];
					}
					else
					{
						$optionEmiter[] = ["value" => $e->rfc, "description" => $e->name];
					}
				}
			@endphp
			@component('components.inputs.select', ["options" => $optionEmiter])
				@slot('attributeEx')
					id="emiter_cfdi_search" name="emiter_cfdi_search[]" multiple="multiple"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') RFC receptor: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="receptor_cfdi_search" placeholder="Ingrese el RFC" data-validation="custom" data-validation-optional="true" data-validation-regexp="^([A-ZÑ&]{3,4}) ?(?:- ?)?(\d{2}(?:0[1-9]|1[0-2])(?:0[1-9]|[12]\d|3[01])) ?(?:- ?)?([A-Z\d]{2})([A\d])$" data-validation-error-msg="Por favor, ingrese un RFC válido"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2 md:col-span-4 grid justify-items-center md:justify-items-start">
			@component('components.buttons.button-search')@endcomponent 
		</div>
	@endcomponent 
@endcomponent
<div class="card-body cfdi-search-container">
	@php
		$body		= [];
		$modelBody	= [];
		$modelHead	= [[["value" => "Acción"]]];
		if($cfdi_version == '4.0')
		{
			array_push($modelHead[0],["value" => "Relación"]);
		}
		array_push($modelHead[0],["value" => "Emisor"]);
		array_push($modelHead[0],["value" => "Receptor"]);
		array_push($modelHead[0],["value" => "UUID"]);
		array_push($modelHead[0],["value" => "Estatus"]);
		array_push($modelHead[0],["value" => "Fecha"]);
		array_push($modelHead[0],["value" => "Método de pago"]);
		array_push($modelHead[0],["value" => "Monto"]);
		array_push($modelHead[0],["value" => "Moneda"]);
	
		if($choosen != '')
		{
			foreach($choosen as $s)
			{
				$payment = '';
				if($s->paymentMethod != '')
				{
					$payment = "data-payment-method=\"".$s->paymentMethod.' '.$s->cfdiPaymentMethod->description."\"";
				} 

				if($cfdi_version == '4.0')
				{
					$optionCFDIrel = [];
					if($cfdi_kind == 'P')
					{
						foreach(App\CatRelation::where('typeRelation','04')->get() as $r)
						{
							if(isset($choosen_rel[$s->idBill]) && $choosen_rel[$s->idBill]['rel'] == $r->typeRelation)
							{
								$optionCFDIrel[] = ["value" => $r->typeRelation, "description" => $r->typeRelation.' '.$r->description, "selected" => "selected"];
							}
							else
							{
								$optionCFDIrel[] = ["value" => $r->typeRelation, "description" => $r->typeRelation.' '.$r->description];
							}
						}
					}
					else
					{
						foreach(App\CatRelation::all() as $r)
						{
							if(isset($choosen_rel[$s->idBill]) && $choosen_rel[$s->idBill]['rel'] == $r->typeRelation)
							{
								$optionCFDIrel[] = ["value" => $r->typeRelation, "description" => $r->typeRelation.' '.$r->description, "selected" => "selected"];
							}
							else
							{
								$optionCFDIrel[] = ["value" => $r->typeRelation, "description" => $r->typeRelation.' '.$r->description];
							}
						}
					}
				}

				$body = [	"classEx" => "tr_relation",
					[
						"content" => 
						[
							"kind" 				=> "components.inputs.checkbox",
							"attributeEx"		=> "type=\"checkbox\" value=\"$s->idBill\" checked name=\"cfdi_rel[]\" data-uuid=\"$s->uuid\" id=\"check_$s->uuid\" data-serie=\"$s->serie\" data-folio=\"$s->folio\" data-currency=\"".$s->currency.' '.$s->cfdiCurrency->description."\"".' '.$payment,
							"label"				=> "<span class=\"icon-check\"></span>",
							"classExContainer"	=> "my-0 md:my-6"
						]
					]
				];
				if($cfdi_version == '4.0')
				{
					array_push($body,[
						"content" => 
						[
							"kind" 			=> "components.inputs.select",
							"attributeEx"	=> "name=\"cfdi_rel_kind[]\" multiple=\"multiple\"",
							"classEx"		=> "js-relation",
							"options"		=> $optionCFDIrel
						]
					]);
				}
				array_push($body,[
					"content" =>
					[
						"label" => $s->rfc
					]
				]);
				array_push($body,[
					"content" => 
					[
						"label" => $s->clientRfc
					]
				]);
				array_push($body,[
					"content" => 
					[
						"label" => $s->uuid
					]
				]);
				array_push($body,[
					"content" => 
					[
						"label" => $s->statusCFDI
					]
				]);
				array_push($body,[
					"content" => 
					[
						"label" => isset($s->stampDate) ? date('d-m-Y h:i:s', strtotime($s->stampDate)) : ''
					]
				]);
				array_push($body,[
					"content" => 
					[
						"label" => $s->paymentMethod
					]
				]);
				array_push($body,[
					"content" => 
					[
						"label" => '$'.number_format($s->total,2)
					]
				]);
				array_push($body,[
					"content" =>
					[
						"label" => $s->currency
					]
				]);
				$modelBody[] = $body;
			}
		}
	@endphp
	@component('components.tables.table', [
		"modelBody" => $modelBody,
		"modelHead" => $modelHead
	])
	@endcomponent
</div>