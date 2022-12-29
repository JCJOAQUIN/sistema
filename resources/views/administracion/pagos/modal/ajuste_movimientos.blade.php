<div class="content-start items-start flex flex-row flex-wrap justify-center w-full mt-10 mb-6">
	A continuación podrá verificar la información de la solicitud antes de continuar con el proceso:
</div>
@php 
	$taxes 		= 0;
	$retentions = 0;
@endphp
@component('components.labels.title-divisor',["classExContainer" => "my-6"]) DATOS DE ORIGEN @endcomponent
@foreach($request->adjustment->first()->adjustmentFolios as $af)
	@php
		$modelTable	=
		[
			["Empresa:",					$af->requestModel->reviewedEnterprise->name],
			["Dirección:",					$af->requestModel->reviewedDirection->name],
			["Departamento:",				$af->requestModel->reviewedDepartment->name],
			["Clasificación del gasto:",	$af->requestModel->accountsReview()->exists() ? $af->requestModel->accountsReview->account.' - '.$af->requestModel->accountsReview->description.' ('.$af->requestModel->accountsReview->content.')' : 'Varias'],
			["Proyecto:",					$af->requestModel->reviewedProject->proyectName],
		];
	@endphp
	@component('components.templates.outputs.table-detail', ["modelTable" => $modelTable])
		@slot('classEx')
			mt-4
		@endslot
		@slot('title')
			@component('components.labels.label')
				@slot('classEx')
					w-11/12
					text-center
					text-white
					ml-14
				@endslot
				FOLIO  #{{ $af->idFolio }}
			@endcomponent
		@endslot
	@endcomponent
@endforeach

@component('components.labels.title-divisor') DATOS DEL PEDIDO @endcomponent
@php
	$modelHead = 
	[
		[
			["value" => "#"],
			["value" => "Solicitud de"],
			["value" => "Cantidad"],
			["value" => "Unidad"],
			["value" => "Descripción"],
			["value" => "Precio Unitario"],
			["value" => "IVA"],
			["value" => "Impuesto Adicional"],
			["value" => "Retenciones"],
			["value" => "Importe"]
		]
	];
	$modelBody = [];
	$countConcept = 1;
	foreach($request->adjustment->first()->adjustmentFolios as $detail)
	{
		switch($detail->requestModel->kind)
		{
			case 1:
				foreach($detail->requestModel->purchases->first()->detailPurchase as $detpurchase)
				{
					$taxesConcept = 0;
					foreach($detpurchase->taxes as $tax)
					{
						$taxesConcept+=$tax->amount;
					} 
					$retentionConcept = 0;
					foreach($detpurchase->retentions as $ret)
		            {
						$retentionConcept+=$ret->amount;
					}
		                      
					$modelBody[] = 
					[
						"classEx" => "tr",
						[
							"classEx" 	=> "td",
							"content" 	=>
							[
								[
									"kind" 		=> "components.labels.label",
									"label"		=> $countConcept,
								],
							],
						],
						[
							"classEx" 	=> "td",
							"content" 	=>
							[
								[
									"kind" 	=> "components.labels.label",
									"label"	=> $detail->requestModel->requestkind->kind.' #'.$detail->requestModel->folio,
								],
							],
						],
						[
							"classEx" => "td",
							"content" => 
							[
								[
									"kind" => "components.labels.label",
									"label" => $detpurchase->quantity,
								]
							]
						],
						[
							"classEx" => "td",
							"content" =>
							[
								[
									"kind" => "components.labels.label",
									"label"	=> $detpurchase->unit != "" ? $detpurchase->unit : "---",
								],
							],
						],
						[
							"classEx" => "td",
							"content" =>
							[
								[
									"kind" 	=> "components.labels.label",
									"label"	=> htmlentities($detpurchase->description),
								],
							],
						],
						[
							"classEx" => "td",
							"content" =>
							[
								[
									"kind" => "components.labels.label",
									"label"	=> "$ ".number_format($detpurchase->unitPrice,2),
								],
							],
						],
						[
							"classEx" => "td",
							"content" => 
							[
								[
									"kind"	=> "components.labels.label",
									"label"	=> "$ ".number_format($detpurchase->tax,2),
								],
							],
						],
						[
							"classEx" => "td",
							"content" => 
							[
								[
									"kind"	=> "components.labels.label",
									"label"	=> "$ ".number_format($taxesConcept,2),
								],
							],
						],
						[
							"classEx" => "td",
							"content" =>
							[
								[
									"kind" => "components.labels.label",
									"label" => "$ ".number_format($retentionConcept,2),
								],
							]
						],
						[
							"classEx" => "td",
							"content" =>
							[
								[
									"kind" => "components.labels.label",
									"label" => "$ ".number_format($detpurchase->amount,2),
								],
							]
						],
					];
					$countConcept++;
				}
				break;

			case 3:
				foreach($detail->requestModel->expenses->first()->expensesDetail as $detexpenses)
				{
					$taxesConcept = 0;
					foreach($detexpenses->taxes as $tax)
					{
						$taxesConcept+=$tax->amount;
					} 
					$retentionConcept = 0;
					foreach($detexpenses->retentions as $ret)
		            {
						$retentionConcept+=$ret->amount;
					}
		                      
					$modelBody[] = 
					[
						"classEx" => "tr",
						[
							"classEx" 	=> "td",
							"content" 	=>
							[
								[
									"kind" 		=> "components.labels.label",
									"label"		=> $countConcept,
								],
							],
						],
						[
							"classEx" 	=> "td",
							"content" 	=>
							[
								[
									"kind" 	=> "components.labels.label",
									"label"	=> $detail->requestModel->requestkind->kind.' #'.$detail->requestModel->folio,
								],
							],
						],
						[
							"classEx" => "td",
							"content" => 
							[
								[
									"kind" => "components.labels.label",
									"label" => "---",
								]
							]
						],
						[
							"classEx" => "td",
							"content" =>
							[
								[
									"kind" => "components.labels.label",
									"label"	=> "---",
								],
							],
						],
						[
							"classEx" => "td",
							"content" =>
							[
								[
									"kind" 	=> "components.labels.label",
									"label"	=> $detexpenses->description,
								],
							],
						],
						[
							"classEx" => "td",
							"content" =>
							[
								[
									"kind" => "components.labels.label",
									"label"	=> "$ ".number_format($detexpenses->unitPrice,2),
								],
							],
						],
						[
							"classEx" => "td",
							"content" => 
							[
								[
									"kind"	=> "components.labels.label",
									"label"	=> "$ ".number_format($detexpenses->tax,2),
								],
							],
						],
						[
							"classEx" => "td",
							"content" => 
							[
								[
									"kind"	=> "components.labels.label",
									"label"	=> "$ ".number_format($taxesConcept,2),
								],
							],
						],
						[
							"classEx" => "td",
							"content" =>
							[
								[
									"kind" => "components.labels.label",
									"label" => "$ ".number_format($retentionConcept,2),
								],
							]
						],
						[
							"classEx" => "td",
							"content" =>
							[
								[
									"kind" => "components.labels.label",
									"label" => "$ ".number_format($detexpenses->amount,2),
								],
							]
						],
					];
					$countConcept++;
				}
				break;

			case 9:
				foreach($detail->requestModel->refunds->first()->refundDetail as $detrefund)
				{
					$taxesConcept = 0;
					foreach($detrefund->taxes as $tax)
					{
						$taxesConcept+=$tax->amount;
					} 
		                      
					$modelBody[] = 
					[
						"classEx" => "tr",
						[
							"classEx" 	=> "td",
							"content" 	=>
							[
								[
									"kind" 		=> "components.labels.label",
									"label"		=> $countConcept,
								],
							],
						],
						[
							"classEx" 	=> "td",
							"content" 	=>
							[
								[
									"kind" 	=> "components.labels.label",
									"label"	=> $detail->requestModel->requestkind->kind.' #'.$detail->requestModel->folio,
								],
							],
						],
						[
							"classEx" => "td",
							"content" => 
							[
								[
									"kind" => "components.labels.label",
									"label" => "---",
								]
							]
						],
						[
							"classEx" => "td",
							"content" =>
							[
								[
									"kind" => "components.labels.label",
									"label"	=> "---",
								],
							],
						],
						[
							"classEx" => "td",
							"content" =>
							[
								[
									"kind" 	=> "components.labels.label",
									"label"	=> $detrefund->concept,
								],
							],
						],
						[
							"classEx" => "td",
							"content" =>
							[
								[
									"kind" => "components.labels.label",
									"label"	=> "$ ".number_format($detrefund->unitPrice,2),
								],
							],
						],
						[
							"classEx" => "td",
							"content" => 
							[
								[
									"kind" 	=> "components.labels.label",
									"label" => "$ ".number_format($detrefund->tax,2),
								],
							],
						],
						[
							"classEx" => "td",
							"content" => 
							[
								[
									"kind"	=> "components.labels.label",
									"label"	=> "$ ".number_format($taxesConcept,2),
								],
							],
						],
						[
							"classEx" => "td",
							"content" =>
							[
								[
									"kind" => "components.labels.label",
									"label" => "$ 0.00",
								],
							]
						],
						[
							"classEx" => "td",
							"content" =>
							[
								[
									"kind" => "components.labels.label",
									"label" => "$ ".number_format($detrefund->amount,2),
								],
							]
						],
					];
					$countConcept++;
				}
				break;
		}
	}
@endphp
@component("components.tables.table",[
	"modelHead" => $modelHead,
	"modelBody" => $modelBody,
	"themeBody" => "striped"
])
	@slot("attributeEx")
		id="table"
	@endslot
	@slot("classExBody")
		request-validate
	@endslot
	@slot("attributeExBody")
		id="body"
	@endslot
@endcomponent

@php
	$modelTable = 
	[
		["label" => "Subtotal: ", "inputsEx" => [["kind" =>	"components.labels.label",	"label" => "$ ".number_format($request->adjustment->first()->subtotales,2,".",","), "classEx" => "my-2 label-subtotal"]]],
		["label" => "IVA: ", "inputsEx" => [["kind" => "components.labels.label",	"label"	=>	"$ ".number_format($request->adjustment->first()->tax,2), "classEx" => "my-2 label-IVA"]]],
		["label" => "Impuesto Adicional: ", "inputsEx" => [["kind" => "components.labels.label",	"label" => "$ ".number_format($request->adjustment->first()->additionalTax,2), "classEx" => "my-2 label-taxes"]]],
		["label" => "Retenciones: ", "inputsEx" => [["kind"	=> "components.labels.label",	"label"	=> "$ ".number_format($request->adjustment->first()->retention,2), "classEx" => "my-2 label-retentions"]]],
		["label" => "TOTAL: ", "inputsEx" => [["kind" => "components.labels.label",	"label"	=>	"$ ".number_format($request->adjustment->first()->amount,2), "classEx" => "my-2 label-total"]]],
	];
@endphp
@component("components.templates.outputs.form-details", ["modelTable" => $modelTable]) @endcomponent

<div class="my-6">
    <div class="text-center">
        @component("components.buttons.button",[
            "variant"		=> "success",
            "attributeEx" 	=> "type=\"button\" title=\"Ocultar\" data-dismiss=\"modal\"",
            "label"			=> "« Ocultar",
            "classEx"		=> "exit",
        ])  
        @endcomponent
    </div>
</div>