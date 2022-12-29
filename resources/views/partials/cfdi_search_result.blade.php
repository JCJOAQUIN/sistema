@php
	$body		= [];
	$modelBody	= [];
	$modelHead	= [[["value" => "Acción", "show" => "true"]]];
	if($cfdi_version == '4.0')
	{
		array_push($modelHead[0],["value" => "Relación"]);
	}
	array_push($modelHead[0],["value" => "Emisor", "show" => "true"]);
	array_push($modelHead[0],["value" => "Receptor"]);
	array_push($modelHead[0],["value" => "UUID"]);
	array_push($modelHead[0],["value" => "Estatus"]);
	array_push($modelHead[0],["value" => "Fecha"]);
	array_push($modelHead[0],["value" => "Método de pago"]);
	array_push($modelHead[0],["value" => "Monto"]);
	array_push($modelHead[0],["value" => "Moneda"]);
	if($selected != '')
	{
		foreach($selected as $k => $s)
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
					foreach(App\CatRelation::where('typeRelation','04')->get() as $rel)
					{
						if(isset($selected_opt) && isset($selected_opt[$k]) && $selected_opt[$k] == $rel->typeRelation)
						{
							$optionCFDIrel[] = ["value" => $rel->typeRelation, "description" => $rel->typeRelation.' '.$rel->description, "selected" => "selected"];
						}
						else
						{
							$optionCFDIrel[] = ["value" => $rel->typeRelation, "description" => $rel->typeRelation.' '.$rel->description];
						}
					}
				}
				else
				{
					foreach(App\CatRelation::all() as $rel)
					{
						if(isset($selected_opt) && isset($selected_opt[$k]) && $selected_opt[$k] == $rel->typeRelation)
						{
							$optionCFDIrel[] = ["value" => $rel->typeRelation, "description" => $rel->typeRelation.' '.$rel->description, "selected" => "selected"];
						}
						else
						{
							$optionCFDIrel[] = ["value" => $rel->typeRelation, "description" => $rel->typeRelation.' '.$rel->description];
						}
					}
				}
			}
				
			$body = [  "classEx" => "tr_relation",
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
	foreach($result as $r)
	{
		$payment_met = '';
		if($r->paymentMethod != '')
		{
			$payment_met = "data-payment-method=\"".$r->paymentMethod.' '.$r->cfdiPaymentMethod->description."\"";
		} 

		if($cfdi_version == '4.0')
		{
			$optionCFDIrel = [];
			if($cfdi_kind == 'P')
			{
				foreach(App\CatRelation::where('typeRelation','04')->get() as $rel)
				{
					$optionCFDIrel[] = ["value" => $rel->typeRelation, "description" => $rel->typeRelation.' '.$rel->description, "selected" => "selected"];
				}
			}
			else
			{
				foreach(App\CatRelation::all() as $rel)
				{
					$optionCFDIrel[] = ["value" => $rel->typeRelation, "description" => $rel->typeRelation.' '.$rel->description];
				}
			}
		}

		$body = [ "classEx" => "tr_relation",
			[
				"content" => 
				[
					"kind" 				=> "components.inputs.checkbox",
					"attributeEx"		=> "type=\"checkbox\" value=\"$r->idBill\" name=\"cfdi_rel[]\" data-uuid=\"$r->uuid\" id=\"check_$r->uuid\" data-serie=\"$r->serie\" data-folio=\"$r->folio\" data-currency=\"".$r->currency.' '.$r->cfdiCurrency->description."\"".' '.$payment_met,
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
					"classEx"		=> "js-relations",
					"options"		=> $optionCFDIrel
				]
			]);
		}
		array_push($body,[
			"content" =>
			[
				"label" => $r->rfc
			]
		]);
		array_push($body,[
			"content" => 
			[
				"label" => $r->clientRfc
			]
		]);
		array_push($body,[
			"content" => 
			[
				"label" => $r->uuid
			]
		]);
		array_push($body,[
			"content" => 
			[
				"label" => $r->statusCFDI
			]
		]);
		array_push($body,[
			"content" => 
			[
				"label" => isset($r->stampDate) ? date('d-m-Y h:i:s', strtotime($r->stampDate)) : ''
			]
		]);
		array_push($body,[
			"content" => 
			[
				"label" => $r->paymentMethod
			]
		]);
		array_push($body,[
			"content" => 
			[
				"label" => '$'.number_format($r->total,2)
			]
		]);
		array_push($body,[
			"content" =>
			[
				"label" => $r->currency
			]
		]);
		$modelBody[] = $body;
	}
@endphp
@component('components.tables.table', [
		"modelBody" => $modelBody,
		"modelHead" => $modelHead
	])
@endcomponent
{{ $result->appends($_GET)->links() }}