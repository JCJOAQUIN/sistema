@component('components.labels.title-divisor') DATOS DE LA SOLICITUD @endcomponent
@php
	$category = $req->idDepartment == 11 ? 'Obra' : 'Administrativa';

	$kind = $req->taxPayment == 1 ? 'Fiscal' : 'No fiscal';
	
	$modelTable	=
	[
		"Elaboró"					=>	$req->elaborateUser->fullName(),
		"Título"					=>	$req->nominasReal->first()->title,
		"Fecha"						=>	Carbon\Carbon::createFromFormat('Y-m-d', $req->nominasReal->first()->datetitle)->format('d-m-Y'),
		"Solicitante"				=>	$req->requestUser->fullName(),
		"Tipo de nómina"			=>	$req->nominasReal->first()->typePayroll->description,
		"Categoría"					=> 	$category,
		"Tipo"						=>  $kind,
	];

	switch($req->nominasReal->first()->idCatTypePayroll)
	{
		case('001'):
			$periodicity = "";
			foreach(App\CatPeriodicity::orderBy('description','asc')->whereIn('c_periodicity',['02','04','05'])->get() as $per)
			{
				if($req->nominasReal->first()->idCatPeriodicity == $per->c_periodicity)
				{
					$periodicity = $per->description;
				}
			}
			$modelTable ["Desde"] = Carbon\Carbon::createFromFormat('Y-m-d', $req->nominasReal->first()->from_date)->format('d-m-Y');
			$modelTable ["Hasta"] = Carbon\Carbon::createFromFormat('Y-m-d', $req->nominasReal->first()->to_date)->format('d-m-Y');
			$modelTable ["Periodicidad"] = $periodicity;
		break;
			
		case('002'):
			
		break;
		
		case('003'):
		case('004'):
			$modelTable ["Desde"] = Carbon\Carbon::createFromFormat('Y-m-d', $req->nominasReal->first()->down_date)->format('d-m-Y');
		break;

		case('005'):
			
		break;

		case('006'):
			$modelTable["PTU por pagar"] = "$ ".number_format($req->nominasReal->first()->ptu_to_pay,2);
		break;
	}

@endphp
@component('components.templates.outputs.table-detail-single', ["modelTable" => $modelTable]) @endcomponent

@component('components.labels.title-divisor') LISTA DE EMPLEADOS @endcomponent
@if($req->status != 2)
	@if($req->taxPayment == 0)
		<div class="float-right">
			<label>
				@component("components.buttons.button",["variant" => "success", "buttonElement" => "a"])
				@slot("attributeEx") href="{{ route('nomina.review-nf.export',$req->folio) }}" @endslot
				@slot('classEx') export @endslot
				@slot("slot") <span>Exportar datos no fiscales a Excel</span><span class="icon-file-excel"></span> @endslot
				@endcomponent
			</label>
		</div>
	@endif
@endif
@if($req->taxPayment == 1)
	@switch($req->nominasReal->first()->idCatTypePayroll)
		@case('001')
			@if($req->status != 2)
				<div class="float-right">
					<label>
						@component("components.buttons.button",["variant" => "success", "buttonElement" => "a"])
						@slot("attributeEx") href="{{ route('nomina.export.salary',$req->folio) }}" @endslot
						@slot('classEx') export @endslot
						@slot("slot") <span>Exportar a Excel</span><span class="icon-file-excel"></span> @endslot
						@endcomponent
					</label>
				</div>
			@endif
			@if($req->status == 2)
				<div class="float-right">
					<label>
						@component("components.buttons.button",["variant" => "success", "buttonElement" => "a"])
						@slot("attributeEx") href="{{ route('nomina.export.employee',$req->folio) }}" @endslot
						@slot('classEx') export @endslot
						@slot("slot") <span>Exportar a Excel</span><span class="icon-file-excel"></span> @endslot
						@endcomponent
					</label>
				</div>
			@endif

			@php
				$modelHead =
				[
					[
						["value" => "Nombre del Empleado"],
						["value" => "Desde"],
						["value" => "Hasta"],
						["value" => "Periodicidad"],
						["value" => "Faltas"],
						["value" => "Préstamo (Percepción)"],
						["value" => "Préstamo (Retención)"]
					]
				];
				
				if($req->status != 2)
				{
					$modelHead [[0]] = ["value" => "XML"];
					$modelHead [[0]] = ["value" => "PDF"];
					$modelHead [[0]] = ["value" => "Documentos de Pago"];
				}
				
				$modelBody = [];
				foreach($req->nominasReal->first()->nominaEmployee->where('visible',1) as $n)
				{
					$periodicity = App\CatPeriodicity::where('c_periodicity',$n->idCatPeriodicity)->first()->description;
					$body = 
					[
						"classEx" => "tr",
						[
							"classEx" => "td",
							"content" =>
							[
								[
									"kind" => "components.labels.label",
									"label" => $n->employee->first()->fullName(),
								]
							]
						],
						[
							"classEx" => "td",
							"content" =>
							[
								[
									"kind" => "components.labels.label",
									"label" => Carbon\Carbon::createFromFormat('Y-m-d', $n->from_date)->format('d-m-Y'),
								]
							]
						],
						[
							"classEx" => "td",
							"content" =>
							[
								[
									"kind" => "components.labels.label",
									"label" => Carbon\Carbon::createFromFormat('Y-m-d', $n->to_date)->format('d-m-Y'),
								]
							]
						],
						[
							"classEx" => "td",
							"content" =>
							[
								[
									"kind" => "components.labels.label",
									"label" => $periodicity,
								]
							]
						],
						[
							"classEx" => "td",
							"content" =>
							[
								[
									"kind" => "components.labels.label",
									"label" => $n->absence!= '' ? $n->absence : '---',
								]
							]
						],
						[
							"classEx" => "td",
							"content" =>
							[
								[
									"kind" => "components.labels.label",
									"label" => $n->loan_perception!= '' ? $n->loan_perception : '---',
								]
							]
						],
						[
							"classEx" => "td",
							"content" =>
							[
								[
									"kind" => "components.labels.label",
									"label" => $n->loan_retention!= '' ? $n->loan_retention : '---',
								]
							]
						],
					];
					
					if($req->status != 2 && $n->nominaCFDI()->exists())
					{
						if(\Storage::disk('reserved')->exists('/stamped/'.$n->nominaCFDI->first()->uuid.'.xml'))
						{
							$body [] = 
							[
								"classEx" => "td",
								"content" =>
								[
									[
										"kind" => "components.buttons.button", 
										"buttonElement" => "a",
										"attributeEx" => "alt=\"XML\" title=\"XML\" href=\"".route('bill.stamped.download.xml',$n->nominaCFDI->first()->uuid)."\"",
										"variant" => "success",
										"label" => "<span class='icon-xml'></span>",
									]
								]
							];
						}
						else 
						{
							$body [] = 
							[
								"classEx" => "td",
								"content" =>
								[
									[
										"kind" => "components.labels.label",
										"label" => "---",
									]
								]
							];	
						}

						if(\Storage::disk('reserved')->exists('/stamped/'.$n->nominaCFDI->first()->uuid.'.pdf'))
						{
							$body [] = 
							[
								"classEx" => "td",
								"content" =>
								[
									[
										"kind" => "components.buttons.button", 
										"buttonElement" => "a",
										"attributeEx" => "alt=\"PDF\" title=\"PDF\" href=\"".route('bill.stamped.download.pdf',$n->nominaCFDI->first()->uuid)."\"",
										"variant" => "dark-red",
										"label" => "PDF",
									]
								]
							];
						}
						else
						{
							$body [] = 
							[
								"classEx" => "td",
								"content" =>
								[
									[
										"kind" => "components.labels.label",
										"label" => "---",
									]
								]
							];
						}

						if($n->payments()->first()->documentsPayments()->exists())
						{
							$documentsPayments = [];
							foreach($n->payments->first()->documentsPayments as $pay)
							{
								// $documentsPayments[] = 
								// [
								// 	"kind" => "components.buttons.button", 
								// 	"buttonElement" => "a",
								// 	"attributeEx" => "href=\"".asset('docs/payments/'.$pay->path)."\" target=\"_blank\"",
								// 	"variant" => "dark-red",
								// 	"label" => "PDF",
								// ];
								$containerButton = "";
								$containerButton .= '<div class="w-full">';
								$containerButton .= view('components.buttons.button',[																
									"buttonElement" => "a",
									"attributeEx" => "href=\"".asset('docs/payments/'.$pay->path)."\" target=\"_blank\"",
									"variant" => "dark-red",
									"label" => "PDF",
								])->render();
								$containerButton .= '</div>';
								$documentsPayments [] =
								[
									"label" => $containerButton,
								];
							}

							$body [] = 
							[
								"classEx" => "td",
								"content" => $documentsPayments
								
							];
						}
						else
						{
							$body [] = 
							[
								"classEx" => "td",
								"content" =>
								[
									[
										"kind" => "components.labels.label",
										"label" => "---",
									]
								]
							];
						}
						
					}
					else 
					{
						$body [] = 
						[
							"classEx" => "td",
							"content" =>
							[
								[
									"kind" => "components.labels.label",
									"label" => "---",
								]
							]
						];
						$body [] = 
						[
							"classEx" => "td",
							"content" =>
							[
								[
									"kind" => "components.labels.label",
									"label" => "---",
								]
							]
						];
						$body [] = 
						[
							"classEx" => "td",
							"content" =>
							[
								[
									"kind" => "components.labels.label",
									"label" => "---",
								]
							]
						];	
					}
					
					$modelBody [] = $body;
				}
			@endphp
			@component("components.tables.table", ["modelHead" => $modelHead, "modelBody" => $modelBody])
				@slot('attributeEx')
					id="table"
				@endslot
				@slot('classEx')
					table
				@endslot
				@slot('attributeExBody')
					id="body-payroll"
				@endslot
			@endcomponent
		@break

		@case('002')
			@if($req->status != 2)
				<div class="float-right">
					<label>
						@component("components.buttons.button",["variant" => "success", "buttonElement" => "a"])
						@slot("attributeEx") href="{{ route('nomina.export.bonus',$req->folio) }}" @endslot
						@slot('classEx') export @endslot
						@slot("slot") <span>Exportar a Excel</span><span class="icon-file-excel"></span> @endslot
						@endcomponent
					</label>
				</div>
			@endif
			@if($req->status == 2)
				<div class="float-right">
					<label>
						@component("components.buttons.button",["variant" => "success", "buttonElement" => "a"])
						@slot("attributeEx") href="{{ route('nomina.export.employee',$req->folio) }}" @endslot
						@slot('classEx') export @endslot
						@slot("slot") <span>Exportar a Excel</span><span class="icon-file-excel"></span> @endslot
						@endcomponent
					</label>
				</div>
			@endif
			@php
				$modelHead =
				[
					[
						["value" => "Nombre del Empleado", "show" => "true"],
						["value" => "Días para aguinaldo"],
					]
				];
				
				if($req->status != 2)
				{
					$modelHead [[0]] = ["value" => "XML"];
					$modelHead [[0]] = ["value" => "PDF"];
					$modelHead [[0]] = ["value" => "Documentos de Pago"];
				}
				
				$modelBody = [];
				foreach($req->nominasReal->first()->nominaEmployee->where('visible',1) as $n)
				{
					$periodicity = App\CatPeriodicity::where('c_periodicity',$n->idCatPeriodicity)->first()->description;
					$body = 
					[
						"classEx" => "tr",
						[
							"classEx" => "td",
							"content" =>
							[
								[
									"kind" => "components.labels.label",
									"label" => $n->employee->first()->fullName(),
								]
							]
						],
						[
							"classEx" => "td",
							"content" =>
							[
								[
									"kind" => "components.labels.label",
									"label" => $n->day_bonus,
								]
							]
						],
					];
					
					if($req->status != 2 && $n->nominaCFDI()->exists())
					{
						if(\Storage::disk('reserved')->exists('/stamped/'.$n->nominaCFDI->first()->uuid.'.xml'))
						{
							$body [] = 
							[
								"classEx" => "td",
								"content" =>
								[
									[
										"kind" => "components.buttons.button", 
										"buttonElement" => "a",
										"attributeEx" => "href=\"".route('bill.stamped.download.xml',$n->nominaCFDI->first()->uuid)."\" alt=\"XML\" title=\"XML\"",
										"variant" => "success",
										"label" => "<span class='icon-xml'></span>",
									]
								]
							];
						}
						else 
						{
							$body [] = 
							[
								"classEx" => "td",
								"content" =>
								[
									[
										"kind" => "components.labels.label",
										"label" => "---",
									]
								]
							];	
						}

						if(\Storage::disk('reserved')->exists('/stamped/'.$n->nominaCFDI->first()->uuid.'.pdf'))
						{
							$body [] = 
							[
								"classEx" => "td",
								"content" =>
								[
									[
										"kind" => "components.buttons.button", 
										"buttonElement" => "a",
										"attributeEx" => "href=\"".route('bill.stamped.download.pdf',$n->nominaCFDI->first()->uuid)."\" alt=\"PDF\" title=\"PDF\"",
										"variant" => "dark-red",
										"label" => "PDF",
									]
								]
							];
						}
						else
						{
							$body [] = 
							[
								"classEx" => "td",
								"content" =>
								[
									[
										"kind" => "components.labels.label",
										"label" => "---",
									]
								]
							];
						}

						if($n->payments()->first()->documentsPayments()->exists())
						{
							$documentsPayments = [];
							foreach($n->payments->first()->documentsPayments as $pay)
							{
								// $documentsPayments [] = 
								// [
								// 	"kind" => "components.buttons.button", 
								// 	"buttonElement" => "a",
								// 	"attributeEx" => "href=\"".asset('docs/payments/'.$pay->path)."\" target=\"_blank\"",
								// 	"variant" => "dark-red",
								// 	"label" => "PDF",
								// ];
								$containerButton = "";
								$containerButton .= '<div class="w-full">';
								$containerButton .= view('components.buttons.button',[																
									"buttonElement" => "a",
									"attributeEx" => "href=\"".asset('docs/payments/'.$pay->path)."\" target=\"_blank\"",
									"variant" => "dark-red",
									"label" => "PDF",
								])->render();
								$containerButton .= '</div>';
								$documentsPayments [] =
								[
									"label" => $containerButton,
								];
							}

							$body [] = 
							[
								"classEx" => "td",
								"content" => $documentsPayments
							];
						}
						else
						{
							$body [] = 
							[
								"classEx" => "td",
								"content" =>
								[
									[
										"kind" => "components.labels.label",
										"label" => "---",
									]
								]
							];
						}
					}
					else 
					{
						$body [] = 
						[
							"classEx" => "td",
							"content" =>
							[
								[
									"kind" => "components.labels.label",
									"label" => "---",
								]
							]
						];
						$body [] = 
						[
							"classEx" => "td",
							"content" =>
							[
								[
									"kind" => "components.labels.label",
									"label" => "---",
								]
							]
						];
						$body [] = 
						[
							"classEx" => "td",
							"content" =>
							[
								[
									"kind" => "components.labels.label",
									"label" => "---",
								]
							]
						];	
					}
					$modelBody [] = $body;
				}
			@endphp
			@component("components.tables.table", ["modelHead" => $modelHead, "modelBody" => $modelBody]) 
				@slot('attributeEx')
					id="table"
				@endslot
				@slot('classEx')
					table
				@endslot
				@slot('attributeExBody')
					id="body-payroll"
				@endslot
			@endcomponent
		@break

		@case('003')
			@if($req->status != 2)
				<div class="float-right">
					<label>
						@component("components.buttons.button",["variant" => "success", "buttonElement" => "a"])
						@slot("attributeEx") href="{{ route('nomina.export.settlement',$req->folio) }}" @endslot
						@slot('classEx') export @endslot
						@slot("slot") <span>Exportar a Excel</span><span class="icon-file-excel"></span> @endslot
						@endcomponent
					</label>
				</div>
			@endif
			@if($req->status == 2)
				<div class="float-right">
					<label>
						@component("components.buttons.button",["variant" => "success", "buttonElement" => "a"])
						@slot("attributeEx") href="{{ route('nomina.export.employee',$req->folio) }}" @endslot
						@slot('classEx') export @endslot
						@slot("slot") <span>Exportar a Excel</span><span class="icon-file-excel"></span> @endslot
						@endcomponent
					</label>
				</div>
			@endif
			@php
				$modelHead =
				[
					[
						["value" => "Nombre del Empleado"],
						["value" => "Fecha de baja"],
						["value" => "Días trabajados"],
						["value" => "Otras percepciones"]
					]
				];
				
				if($req->status != 2)
				{
					$modelHead [[0]] = ["value" => "XML"];
					$modelHead [[0]] = ["value" => "PDF"];
					$modelHead [[0]] = ["value" => "Documentos de Pago"];
				}
				
				$modelBody = [];
				foreach($req->nominasReal->first()->nominaEmployee->where('visible',1) as $n)
				{
					$periodicity = App\CatPeriodicity::where('c_periodicity',$n->idCatPeriodicity)->first()->description;
					$body = 
					[
						"classEx" => "tr",
						[
							"classEx" => "td",
							"content" =>
							[
								[
									"kind" => "components.labels.label",
									"label" => $n->employee->first()->fullName(),
								]
							]
						],
						[
							"classEx" => "td",
							"content" =>
							[
								[
									"kind" => "components.labels.label",
									"label" => Carbon\Carbon::createFromFormat('Y-m-d', $n->down_date)->format('d-m-Y'),
								]
							]
						],
						[
							"classEx" => "td",
							"content" =>
							[
								[
									"kind" => "components.labels.label",
									"label" => $n->worked_days,
								]
							]
						],
						[
							"classEx" => "td",
							"content" =>
							[
								[
									"kind" => "components.labels.label",
									"label" => $n->other_perception,
								]
							]
						],
					];
					
					if($req->status != 2 && $n->nominaCFDI()->exists())
					{
						if(\Storage::disk('reserved')->exists('/stamped/'.$n->nominaCFDI->first()->uuid.'.xml'))
						{
							$body [] = 
							[
								"classEx" => "td",
								"content" =>
								[
									[
										"kind" => "components.buttons.button", 
										"buttonElement" => "a",
										"attributeEx" => "href=\"".route('bill.stamped.download.xml',$n->nominaCFDI->first()->uuid)."\" alt=\"XML\" title=\"XML\"",
										"variant" => "success",
										"label" => "<span class='icon-xml'></span>",
									]
								]
							];
						}
						else 
						{
							$body [] = 
							[
								"classEx" => "td",
								"content" =>
								[
									[
										"kind" => "components.labels.label",
										"label" => "---",
									]
								]
							];	
						}

						if(\Storage::disk('reserved')->exists('/stamped/'.$n->nominaCFDI->first()->uuid.'.pdf'))
						{
							$body [] = 
							[
								"classEx" => "td",
								"content" =>
								[
									[
										"kind" => "components.buttons.button", 
										"buttonElement" => "a",
										"attributeEx" => "href=\"".route('bill.stamped.download.pdf',$n->nominaCFDI->first()->uuid)."\" alt=\"PDF\" title=\"PDF\"",
										"variant" => "dark-red",
										"label" => "PDF",
									]
								]
							];
						}
						else
						{
							$body [] = 
							[
								"classEx" => "td",
								"content" =>
								[
									[
										"kind" => "components.labels.label",
										"label" => "---",
									]
								]
							];
						}

						if($n->payments()->first()->documentsPayments()->exists())
						{
							$documentsPayments = [];
							foreach($n->payments->first()->documentsPayments as $pay)
							{
								// $documentsPayments [] = 
								// [
								// 	"kind" => "components.buttons.button", 
								// 	"buttonElement" => "a",
								// 	"attributeEx" => "href=\"".asset('docs/payments/'.$pay->path)."\" target=\"_blank\"",
								// 	"variant" => "dark-red",
								// 	"label" => "PDF",
								// ];
								$containerButton = "";
								$containerButton .= '<div class="w-full">';
								$containerButton .= view('components.buttons.button',[																
									"buttonElement" => "a",
									"attributeEx" => "href=\"".asset('docs/payments/'.$pay->path)."\" target=\"_blank\"",
									"variant" => "secondary",
									"label" => "Archivo",
								])->render();
								$containerButton .= '</div>';
								$documentsPayments [] =
								[
									"label" => $containerButton,
								];
							}
							$body [] = 
							[
								"classEx" => "td",
								"content" => $documentsPayments
							];
						}
						else
						{
							$body [] = 
							[
								"classEx" => "td",
								"content" =>
								[
									[
										"kind" => "components.labels.label",
										"label" => "---",
									]
								]
							];
						}
					}
					else 
					{
						$body [] = 
						[
							"classEx" => "td",
							"content" =>
							[
								[
									"kind" => "components.labels.label",
									"label" => "---",
								]
							]
						];
						$body [] = 
						[
							"classEx" => "td",
							"content" =>
							[
								[
									"kind" => "components.labels.label",
									"label" => "---",
								]
							]
						];
						$body [] = 
						[
							"classEx" => "td",
							"content" =>
							[
								[
									"kind" => "components.labels.label",
									"label" => "---",
								]
							]
						];	
					}
					$modelBody [] = $body;
				}
			@endphp
			@component("components.tables.table", ["modelHead" => $modelHead, "modelBody" => $modelBody]) 
				@slot('attributeEx')
					id="table"
				@endslot
				@slot('classEx')
					table
				@endslot
				@slot('attributeExBody')
					id="body-payroll"
				@endslot
			@endcomponent
		@break

		@case('004')
			@if($req->status != 2)
				<div class="float-right">
					<label>
						@component("components.buttons.button",["variant" => "success", "buttonElement" => "a"])
						@slot("attributeEx") href="{{ route('nomina.export.liquidation',$req->folio) }}" @endslot
						@slot('classEx') export @endslot
						@slot("slot") <span>Exportar a Excel</span><span class="icon-file-excel"></span> @endslot
						@endcomponent
					</label>
				</div>
			@endif
			@if($req->status == 2)
				<div class="float-right">
					<label>
						@component("components.buttons.button",["variant" => "success", "buttonElement" => "a"])
						@slot("attributeEx") href="{{ route('nomina.export.employee',$req->folio) }}" @endslot
						@slot('classEx') export @endslot
						@slot("slot") <span>Exportar a Excel</span><span class="icon-file-excel"></span> @endslot
						@endcomponent
					</label>
				</div>
			@endif
			
			@php
				$modelHead =
				[
					[
						["value" => "Nombre del Empleado"],
						["value" => "Fecha de baja"],
						["value" => "Días trabajados"],
						["value" => "Otras percepciones"]
					]
				];
				
				if($req->status != 2)
				{
					$modelHead [[0]] = ["value" => "XML"];
					$modelHead [[0]] = ["value" => "PDF"];
					$modelHead [[0]] = ["value" => "Documentos de Pago"];
				}
				
				$modelBody = [];
				foreach($req->nominasReal->first()->nominaEmployee->where('visible',1) as $n)
				{
					$periodicity = App\CatPeriodicity::where('c_periodicity',$n->idCatPeriodicity)->first()->description;
					$body = 
					[
						"classEx" => "tr",
						[
							"classEx" => "td",
							"content" =>
							[
								[
									"kind" => "components.labels.label",
									"label" => $n->employee->first()->fullName(),
								]
							]
						],
						[
							"classEx" => "td",
							"content" =>
							[
								[
									"kind" => "components.labels.label",
									"label" => Carbon\Carbon::createFromFormat('Y-m-d', $n->down_date)->format('d-m-Y'),
								]
							]
						],
						[
							"classEx" => "td",
							"content" =>
							[
								[
									"kind" => "components.labels.label",
									"label" => $n->worked_days,
								]
							]
						],
						[
							"classEx" => "td",
							"content" =>
							[
								[
									"kind" => "components.labels.label",
									"label" => $n->other_perception != "" ? "$ ".$n->other_perception : "$ 0.00",
								]
							]
						],
					];
					
					if($req->status != 2 && $n->nominaCFDI()->exists())
					{
						if(\Storage::disk('reserved')->exists('/stamped/'.$n->nominaCFDI->first()->uuid.'.xml'))
						{
							$body [] = 
							[
								"classEx" => "td",
								"content" =>
								[
									[
										"kind" => "components.buttons.button", 
										"buttonElement" => "a",
										"attributeEx" => "href=\"".route('bill.stamped.download.xml',$n->nominaCFDI->first()->uuid)."\" alt=\"XML\" title=\"XML\"",
										"variant" => "success",
										"label" => "<span class='icon-xml'></span>",
									]
								]
							];
						}
						else 
						{
							$body [] = 
							[
								"classEx" => "td",
								"content" =>
								[
									[
										"kind" => "components.labels.label",
										"label" => "---",
									]
								]
							];	
						}

						if(\Storage::disk('reserved')->exists('/stamped/'.$n->nominaCFDI->first()->uuid.'.pdf'))
						{
							$body [] = 
							[
								"classEx" => "td",
								"content" =>
								[
									[
										"kind" => "components.buttons.button", 
										"buttonElement" => "a",
										"attributeEx" => "href=\"".asset('docs/payments/'.$pay->path)."\" alt=\"PDF\" title=\"PDF\"",
										"variant" => "dark-red",
										"label" => "PDF",
									]
								]
							];
						}
						else
						{
							$body [] = 
							[
								"classEx" => "td",
								"content" =>
								[
									[
										"kind" => "components.labels.label",
										"label" => "---",
									]
								]
							];
						}

						if($n->payments()->first()->documentsPayments()->exists())
						{
							$documentsPayments = [];
							foreach($n->payments->first()->documentsPayments as $pay)
							{
								// $documentsPayments [] = 
								// [
								// 	"kind" => "components.buttons.button", 
								// 	"buttonElement" => "a",
								// 	"attributeEx" => "href=\"".asset('docs/payments/'.$pay->path)."\" target=\"_blank\"",
								// 	"variant" => "dark-red",
								// 	"label" => "PDF",
								// ];
								$containerButton = "";
								$containerButton .= '<div class="w-full">';
								$containerButton .= view('components.buttons.button',[																
									"buttonElement" => "a",
									"attributeEx" => "href=\"".asset('docs/payments/'.$pay->path)."\" target=\"_blank\"",
									"variant" => "secondary",
									"label" => "Archivo",
								])->render();
								$containerButton .= '</div>';
								$documentsPayments [] =
								[
									"label" => $containerButton,
								];
							}
							$body [] = 
							[
								"classEx" => "td",
								"content" => $documentsPayments
							];
						}
						else
						{
							$body [] = 
							[
								"classEx" => "td",
								"content" =>
								[
									[
										"kind" => "components.labels.label",
										"label" => "---",
									]
								]
							];
						}
					}
					else 
					{
						$body [] = 
						[
							"classEx" => "td",
							"content" =>
							[
								[
									"kind" => "components.labels.label",
									"label" => "---",
								]
							]
						];
						$body [] = 
						[
							"classEx" => "td",
							"content" =>
							[
								[
									"kind" => "components.labels.label",
									"label" => "---",
								]
							]
						];
						$body [] = 
						[
							"classEx" => "td",
							"content" =>
							[
								[
									"kind" => "components.labels.label",
									"label" => "---",
								]
							]
						];	
					}
					$modelBody [] = $body;
				}
			@endphp
			@component("components.tables.table", ["modelHead" => $modelHead, "modelBody" => $modelBody]) 
				@slot('attributeEx')
					id="table"
				@endslot
				@slot('classEx')
					table
				@endslot
				@slot('attributeExBody')
					id="body-payroll"
				@endslot
			@endcomponent
		@break

		@case('005')
			@if($req->status != 2)
				<div class="float-right">
					<label>
						@component("components.buttons.button",["variant" => "success", "buttonElement" => "a"])
						@slot("attributeEx") href="{{ route('nomina.export.vacationpremium',$req->folio) }}" @endslot
						@slot('classEx') export @endslot
						@slot("slot") <span>Exportar a Excel</span><span class="icon-file-excel"></span> @endslot
						@endcomponent
					</label>
				</div>
			@endif
			@if($req->status == 2)
				<div class="float-right">
					<label>
						@component("components.buttons.button",["variant" => "success", "buttonElement" => "a"])
						@slot("attributeEx") href="{{ route('nomina.export.employee',$req->folio) }}" @endslot
						@slot('classEx') export @endslot
						@slot("slot") <span>Exportar a Excel</span><span class="icon-file-excel"></span> @endslot
						@endcomponent
					</label>
				</div>
			@endif
			@php
				$modelHead =
				[
					[
						["value" => "Nombre del Empleado", "show" => "true"],
						["value" => "Días trabajados"]
					]
				];
				
				if($req->status != 2)
				{
					$modelHead [[0]] = ["value" => "XML"];
					$modelHead [[0]] = ["value" => "PDF"];
					$modelHead [[0]] = ["value" => "Documentos de Pago"];
				}
				
				$modelBody = [];
				foreach($req->nominasReal->first()->nominaEmployee->where('visible',1) as $n)
				{
					$periodicity = App\CatPeriodicity::where('c_periodicity',$n->idCatPeriodicity)->first()->description;
					$body = 
					[
						"classEx" => "tr",
						[
							"classEx" => "td",
							"content" =>
							[
								[
									"kind" => "components.labels.label",
									"label" => $n->employee->first()->fullName(),
								]
							]
						],
						[
							"classEx" => "td",
							"content" =>
							[
								[
									"kind" => "components.labels.label",
									"label" => $n->worked_days,
								]
							]
						],
					];
					
					if($req->status != 2 && $n->nominaCFDI()->exists())
					{
						if(\Storage::disk('reserved')->exists('/stamped/'.$n->nominaCFDI->first()->uuid.'.xml'))
						{
							$body [] = 
							[
								"classEx" => "td",
								"content" =>
								[
									[
										"kind" => "components.buttons.button", 
										"buttonElement" => "a",
										"attributeEx" => "href=\"".route('bill.stamped.download.xml',$n->nominaCFDI->first()->uuid)."\" alt=\"XML\" title=\"XML\"",
										"variant" => "success",
										"label" => "<span class='icon-xml'></span>",
									]
								]
							];
						}
						else 
						{
							$body [] = 
							[
								"classEx" => "td",
								"content" =>
								[
									[
										"kind" => "components.labels.label",
										"label" => "---",
									]
								]
							];	
						}

						if(\Storage::disk('reserved')->exists('/stamped/'.$n->nominaCFDI->first()->uuid.'.pdf'))
						{
							$body [] = 
							[
								"classEx" => "td",
								"content" =>
								[
									[
										"kind" => "components.buttons.button", 
										"buttonElement" => "a",
										"attributeEx" => "href=\"".route('bill.stamped.download.pdf',$n->nominaCFDI->first()->uuid)."\" alt=\"PDF\" title=\"PDF\"",
										"variant" => "dark-red",
										"label" => "PDF",
									]
								]
							];
						}
						else
						{
							$body [] = 
							[
								"classEx" => "td",
								"content" =>
								[
									[
										"kind" => "components.labels.label",
										"label" => "---",
									]
								]
							];
						}

						if($n->payments()->first()->documentsPayments()->exists())
						{
							$documentsPayments = [];
							foreach($n->payments->first()->documentsPayments as $pay)
							{
								// $documentsPayments [] = 
								// [
								// 	"kind" => "components.buttons.button", 
								// 	"buttonElement" => "a",
								// 	"attributeEx" => "href=\"".asset('docs/payments/'.$pay->path)."\" target=\"_blank\"",
								// 	"variant" => "dark-red",
								// 	"label" => "PDF",
								// ];
								$containerButton = "";
								$containerButton .= '<div class="w-full">';
								$containerButton .= view('components.buttons.button',[																
									"buttonElement" => "a",
									"attributeEx" => "href=\"".asset('docs/payments/'.$pay->path)."\" target=\"_blank\"",
									"variant" => "secondary",
									"label" => "Archivo",
								])->render();
								$containerButton .= '</div>';
								$documentsPayments [] =
								[
									"label" => $containerButton,
								];
							}
							$body [] = 
							[
								"classEx" => "td",
								"content" => $documentsPayments
							];
						}
						else
						{
							$body [] = 
							[
								"classEx" => "td",
								"content" =>
								[
									[
										"kind" => "components.labels.label",
										"label" => "---",
									]
								]
							];
						}
					}
					else 
					{
						$body [] = 
						[
							"classEx" => "td",
							"content" =>
							[
								[
									"kind" => "components.labels.label",
									"label" => "---",
								]
							]
						];
						$body [] = 
						[
							"classEx" => "td",
							"content" =>
							[
								[
									"kind" => "components.labels.label",
									"label" => "---",
								]
							]
						];
						$body [] = 
						[
							"classEx" => "td",
							"content" =>
							[
								[
									"kind" => "components.labels.label",
									"label" => "---",
								]
							]
						];	
					}
					$modelBody [] = $body;
				}
			@endphp
			@component("components.tables.table", ["modelHead" => $modelHead, "modelBody" => $modelBody]) 
				@slot('attributeEx')
					id="table"
				@endslot
				@slot('classEx')
					table
				@endslot
				@slot('attributeExBody')
					id="body-payroll"
				@endslot
			@endcomponent
		@break

		@case('006')
			@if($req->status != 2)
				<div class="float-right">
					<label>
						@component("components.buttons.button",["variant" => "success", "buttonElement" => "a"])
						@slot("attributeEx") href="{{ route('nomina.export.profitsharing',$req->folio) }}" @endslot
						@slot('classEx') export @endslot
						@slot("slot") <span>Exportar a Excel</span><span class="icon-file-excel"></span> @endslot
						@endcomponent
					</label>
				</div>
			@endif
			@if($req->status == 2)
				<div class="float-right">
					<label>
						@component("components.buttons.button",["variant" => "success", "buttonElement" => "a"])
						@slot("attributeEx") href="{{ route('nomina.export.employee',$req->folio) }}" @endslot
						@slot('classEx') export @endslot
						@slot("slot") <span>Exportar a Excel</span><span class="icon-file-excel"></span> @endslot
						@endcomponent
					</label>
				</div>
			@endif
			@php
				$modelHead =
				[
					[
						["value" => "Nombre del Empleado"],
						["value" => "Días trabajados"]
					]
				];
				
				if($req->status != 2)
				{
					$modelHead [[0]] = ["value" => "XML"];
					$modelHead [[0]] = ["value" => "PDF"];
					$modelHead [[0]] = ["value" => "Documentos de Pago"];
				}
				
				$modelBody = [];
				foreach($req->nominasReal->first()->nominaEmployee->where('visible',1) as $n)
				{
					$periodicity = App\CatPeriodicity::where('c_periodicity',$n->idCatPeriodicity)->first()->description;
					$body = 
					[
						"classEx" => "tr",
						[
							"classEx" => "td",
							"content" =>
							[
								[
									"kind" => "components.labels.label",
									"label" => $n->employee->first()->fullName(),
								]
							]
						],
						[
							"classEx" => "td",
							"content" =>
							[
								[
									"kind" => "components.labels.label",
									"label" => $n->worked_days,
								]
							]
						],
					];
					
					if($req->status != 2 && $n->nominaCFDI()->exists())
					{
						if(\Storage::disk('reserved')->exists('/stamped/'.$n->nominaCFDI->first()->uuid.'.xml'))
						{
							$body [] = 
							[
								"classEx" => "td",
								"content" =>
								[
									[
										"kind" => "components.buttons.button", 
										"buttonElement" => "a",
										"attributeEx" => "href=\"".route('bill.stamped.download.xml',$n->nominaCFDI->first()->uuid)."\" alt=\"XML\" title=\"XML\"",
										"variant" => "success",
										"label" => "<span class='icon-xml'></span>",
									]
								]
							];
						}
						else 
						{
							$body [] = 
							[
								"classEx" => "td",
								"content" =>
								[
									[
										"kind" => "components.labels.label",
										"label" => "---",
									]
								]
							];	
						}

						if(\Storage::disk('reserved')->exists('/stamped/'.$n->nominaCFDI->first()->uuid.'.pdf'))
						{
							$body [] = 
							[
								"classEx" => "td",
								"content" =>
								[
									[
										"kind" => "components.buttons.button", 
										"buttonElement" => "a",
										"attributeEx" => "href=\"".route('bill.stamped.download.pdf',$n->nominaCFDI->first()->uuid)."\" alt=\"PDF\" title=\"PDF\"",
										"variant" => "dark-red",
										"label" => "PDF",
									]
								]
							];
						}
						else
						{
							$body [] = 
							[
								"classEx" => "td",
								"content" =>
								[
									[
										"kind" => "components.labels.label",
										"label" => "---",
									]
								]
							];
						}

						if($n->payments()->first()->documentsPayments()->exists())
						{
							$documentsPayments = [];
							foreach($n->payments->first()->documentsPayments as $pay)
							{
								// $documentsPayments [] = 
								// [
								// 	"kind" => "components.buttons.button", 
								// 	"buttonElement" => "a",
								// 	"attributeEx" => "href=\"".asset('docs/payments/'.$pay->path)."\" target=\"_blank\"",
								// 	"variant" => "dark-red",
								// 	"label" => "PDF",
								// ];
								$containerButton = "";
								$containerButton .= '<div class="w-full">';
								$containerButton .= view('components.buttons.button',[																
									"buttonElement" => "a",
									"attributeEx" => "href=\"".asset('docs/payments/'.$pay->path)."\" target=\"_blank\"",
									"variant" => "secondary",
									"label" => "Archivo",
								])->render();
								$containerButton .= '</div>';
								$documentsPayments [] =
								[
									"label" => $containerButton,
								];
							}
							$body [] = 
							[
								"classEx" => "td",
								"content" => $documentsPayments
							];
						}
						else
						{
							$body [] = 
							[
								"classEx" => "td",
								"content" =>
								[
									[
										"kind" => "components.labels.label",
										"label" => "---",
									]
								]
							];
						}
					}
					else 
					{
						$body [] = 
						[
							"classEx" => "td",
							"content" =>
							[
								[
									"kind" => "components.labels.label",
									"label" => "---",
								]
							]
						];
						$body [] = 
						[
							"classEx" => "td",
							"content" =>
							[
								[
									"kind" => "components.labels.label",
									"label" => "---",
								]
							]
						];
						$body [] = 
						[
							"classEx" => "td",
							"content" =>
							[
								[
									"kind" => "components.labels.label",
									"label" => "---",
								]
							]
						];	
					}
					$modelBody [] = $body;
				}
			@endphp
			@component("components.tables.table", ["modelHead" => $modelHead, "modelBody" => $modelBody]) 
				@slot('attributeEx')
					id="table"
				@endslot
				@slot('classEx')
					table
				@endslot
				@slot('attributeExBody')
					id="body-payroll"
				@endslot
			@endcomponent
		@break
	@endswitch
@else
	<div class="font-semibold">* Verifique que el sueldo neto sea correcto para cada empleado </div>
	<div class="content-start items-start flex flex-row flex-wrap w-full mb-6">
		@if($req->status == 2)
		<div class="float-right">
			<label>
				@component("components.buttons.button",["variant" => "success", "buttonElement" => "a"])
				@slot("attributeEx") href="{{ route('nomina.export.employee',$req->folio) }}" @endslot
				@slot('classEx') export @endslot
				@slot("slot") <span>Exportar a Excel</span><span class="icon-file-excel"></span> @endslot
				@endcomponent
			</label>
		</div>
	@endif
	</div>
	@php
		$modelHead = ["Nombre del Empleado", "Tipo", "Documentos de Pago"];
		
		$modelBody = [];
		foreach($req->nominasReal->first()->nominaEmployee->where('visible',1) as $n)
		{
			$type   = $n->type == 1 ? 'Obra' : 'Administrativa';
			$fiscal = $n->fiscal == 1 ? 'Fiscal' : 'No fiscal';
			$body = 
			[
				"classEx" => "tr",
				[
					"classEx" => "td",
					"show" => "true",
					"content" =>
					[
						[
							"kind" => "components.labels.label",
							"label" => $n->employee->first()->fullName(),
						]
					]
				],
				[
					"classEx" => "td",
					"content" =>
					[
						[
							"kind" => "components.labels.label",
							"label" => $type." - ".$fiscal,
						]
					]
				],
			];

			if($n->payments()->first() != "" && $n->payments()->first()->documentsPayments()->exists())
			{
				$documentsPayments = [];
				foreach($n->payments->first()->documentsPayments as $pay)
				{
					// $documentsPayments [] = 
					// [
					// 	"kind" => "components.buttons.button", 
					// 	"buttonElement" => "a",
					// 	"attributeEx" => "href=\"".asset('docs/payments/'.$pay->path)."\" target=\"_blank\"",
					// 	"variant" => "dark-red",
					// 	"label" => "PDF",
					// ];
					$containerButton = "";
					$containerButton .= '<div class="w-full">';
					$containerButton .= view('components.buttons.button',[																
						"buttonElement" => "a",
						"attributeEx" => "href=\"".asset('docs/payments/'.$pay->path)."\" target=\"_blank\"",
						"variant" => "secondary",
						"label" => "Archivo",
					])->render();
					$containerButton .= '</div>';
					$documentsPayments [] =
					[
						"label" => $containerButton,
					];
				}
				$body [] = 
				[
					"classEx" => "td",
					"content" => $documentsPayments
				];
			}
			else
			{
				$body [] = 
				[
					"classEx" => "td",
					"content" =>
					[
						[
							"kind" => "components.labels.label",
							"label" => "---",
						]
					]
				];
			}
			$modelBody [] = $body;
		}
	@endphp
	@component("components.tables.alwaysVisibleTable", ["modelHead" => $modelHead, "modelBody" => $modelBody]) @endcomponent
@endif

@if($req->idCheckConstruction != "" || $req->idCheck != "" || $req->idAuthorize != "")
	@component('components.labels.title-divisor') DATOS DE REVISIÓN @endcomponent
	@php
		$modelTable = [];
		if($req->idCheckConstruction != "")
		{
			$modelTable["Revisó en Obra"] = $req->constructionReviewedUser->fullName();
		}
		if($req->idCheck != "")
		{
			$modelTable["Revisó en RH"] = $req->reviewedUser->fullName();
		}
		if($req->idAuthorize != "")
		{
			$modelTable["Autorizó"] = $req->authorizedUser->fullName();
		}
	@endphp
	@component('components.templates.outputs.table-detail-single', ["modelTable" => $modelTable])
		@slot('classEx')
			employee-details
		@endslot
	@endcomponent
@endif

@component('components.labels.title-divisor') PAGO @endcomponent
@php
	$modelHead =
	[
		[
			["value" => "Cuenta"],
			["value" => "Cantidad"],
			["value" => "Documento"],
			["value" => "Fecha"]
		]
	];
	
	$documentsPayments = [];
	foreach($payment->documentsPayments as $doc)
	{	
		$containerButton = "";
		$containerButton .= '<div class="w-full">';
		$containerButton .= view('components.buttons.button',[																
			"buttonElement" => "a",
			"attributeEx" => "href=\"".asset('docs/payments/'.$doc->path)."\" target=\"_blank\" title=\"".$doc->path."\"",
			"variant" => "secondary",
			"label" => "Archivo",
		])->render();
		$containerButton .= '</div>';
		$documentsPayments [] =
		[
			"label" => $containerButton,
		];
	}
	$modelBody = 
	[
		[
			"classEx" => "tr",
			[
				"classEx" => "td",
                "content" =>
                [
                    [
                        "kind" => "components.labels.label",
                        "label" => $payment->accounts->account." - ".$payment->accounts->description." (".$payment->accounts->content.")",
                    ],
                ],
			],
			[
				"classEx" => "td",
                "content" =>
                [
                    [
                        "kind" => "components.labels.label",
                        "label" => "$ ".number_format($payment->amount,2),
                    ],
                ],
			],
			[
				"classEx" => "td",
                "content" => $documentsPayments
			],
			[
				"classEx" => "td",
                "content" =>
                [
                    [
                        "kind" => "components.labels.label",
                        "label" => Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $payment->paymentDate)->format('d-m-Y'),
                    ],
                ],
			],
		]
	];
@endphp
@component("components.tables.table", ["modelHead" => $modelHead, "modelBody" => $modelBody]) @endcomponent

@component('components.labels.title-divisor') MOVIMIENTO @endcomponent
@php
	$modelHead =
	[
		[
			["value" => "Descripción"],
			["value" => "Comentarios"],
			["value" => "Clasificación del gasto"],
			["value" => "Fecha de Alta de Movimiento"],
			["value" => "Fecha de Conciliación"],
			["value" => "Importe"]
		]
	];
	
	$conciliationDate = App\Payment::select('conciliationDate')->where('idmovement','=',$movement->idmovement)->first();
	
	$modelBody = 
	[
		[
			"classEx" => "tr",
			[
				"classEx" => "td",
                "content" =>
                [
                    [
                        "kind" => "components.labels.label",
                        "label" => $movement->description,
                    ],
                ],
			],
			[
				"classEx" => "td",
                "content" =>
                [
                    [
                        "kind" => "components.labels.label",
                        "label" => $movement->commentaries,
                    ],
                ],
			],
			[
				"classEx" => "td",
                "content" =>
				[
                    [
                        "kind" => "components.labels.label",
                        "label" => $movement->accounts->account." - ".$movement->accounts->description." (".$movement->accounts->content.")",
                    ],
                ],
			],
			[
				"classEx" => "td",
                "content" =>
                [
                    [
                        "kind" => "components.labels.label",
                        "label" => Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $movement->movementDate)->format('d-m-Y'),
                    ],
                ],
			],
			[
				"classEx" => "td",
                "content" =>
                [
                    [
						
                        "kind" => "components.labels.label",
						"label" => Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $conciliationDate->conciliationDate)->format('d-m-Y H:i:s'),
                    ],
                ],
			],
			[
				"classEx" => "td",
                "content" =>
                [
                    [
                        "kind" => "components.labels.label",
                        "label" => "$ ".number_format($movement->amount,2),
                    ],
                ],
			],
		]
	];
@endphp
@component("components.tables.table", ["modelHead" => $modelHead, "modelBody" => $modelBody]) @endcomponent

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