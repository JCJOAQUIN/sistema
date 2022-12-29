@component('components.labels.title-divisor') 
	DETALLES DEL EMPLEADO
@endcomponent
<div class="flex justify-center px-4">
	@component('components.tables.table-request-detail.container',['variant'=>'simple'])
		@php
			$modelTable = [];
			$modelTable["Personal Calificado"] = $employee->qualifiedEmployee();
			$modelTable["Nombre"] = htmlentities($employee->fullName());
			$modelTable["CURP"] = $employee->curp;
			$modelTable["RFC"] = $employee->rfc;
			$modelTable["Régimen Fiscal"] = $employee->taxRegime()->exists() ? $employee->taxRegime->taxRegime.' '.$employee->taxRegime->description : '---';
			$modelTable["No. de IMSS"] = $employee->imss;
			$modelTable["Correo Electrónico"] = htmlentities($employee->email);
			$modelTable["Número teléfonico"] = $employee->phone;
			$modelTable["Dirección"] = $employee->street." ".$employee->number." ".$employee->colony." ".$employee->cp." ".$employee->city." ".$employee->states->description;
			$modelTable["En reemplazo de"] = htmlentities($employee->replace);
			$modelTable["Próposito básico del puesto"] = htmlentities($employee->purpose);
			$modelTable["Requerimientos del puesto"] = $employee->requirements != null ? htmlentities($employee->requirements) : '---';
			$modelTable["Observaciones"] = htmlentities($employee->observations);
			$modelTable["Estado Laboral"] = $employee->statesWork()->exists() ? $employee->statesWork->description : '---';
			$modelTable["Proyecto"] = $employee->projects()->exists() ? $employee->projects->proyectName : '---';
			$modelTable["Empresa"] = $employee->enterprises()->exists() ? $employee->enterprises->name : '---';
			$modelTable["Clasificación de Gasto"] = $employee->accounts()->exists() ? $employee->accounts->account." - ".$employee->accounts->description.' '."(".$employee->accounts->content.")" : "---";
			$modelTable["Dirección"] = $employee->directions()->exists() ? $employee->directions->name : '---';
			$modelTable["Departamento"] = $employee->departments()->exists() ? $employee->departments->name : '---';
			$modelTable["Subdepartamento"] = $employee->subdepartment()->exists() ? $employee->subdepartment->name : '---';
			$modelTable["Puesto"] = htmlentities($employee->position);
			$modelTable["Jefe Inmediato"] = htmlentities($employee->immediate_boss);
			$modelTable["Puesto del Jefe Inmediato"] = htmlentities($employee->position_immediate_boss);
			$modelTable["Fecha de ingreso"] = $employee->admissionDate != null ? $employee->admissionDate->format('d-m-Y') : '---';
			$modelTable["Estado de IMSS"] = $employee->statusImss();
			$modelTable["Fecha de alta (si aplica)"] = $employee->imssDate != null ? $employee->imssDate->format('d-m-Y') : '---';
			$modelTable["Fecha de baja (si aplica)"] = $employee->downDate != null ? $employee->downDate->format('d-m-Y') : '---';
			$modelTable["Fecha de término de relación laboral (si aplica)"] = $employee->reentryDate != null ? $employee->reentryDate->format('d-m-Y') : '---';
			$modelTable["Reingreso (si aplica)"] = $employee->subdepartment()->exists() ? $employee->subdepartment->name : '---';
			$modelTable["Tipo de Trabajador"] = $employee->worker()->exists() ? $employee->worker->description : '---';
			$modelTable["Régimen"] = $employee->regime()->exists() ? $employee->regime->description : '---';
			$modelTable["Estado de Empleado"] = $employee->workerStatus();
			$modelTable["Motivo de estatus (opcional)"] = htmlentities($employee->status_reason);
			$modelTable["SDI (si aplica)"] = $employee->sdi != null ? $employee->sdi : '---';
			$modelTable["Periodicidad"] = $employee->periodicities()->exists() ? $employee->periodicities->description : '---';
			$modelTable["Registro Patronal"] = $employee->employer_register;
			$modelTable["Forma de Pago"] = $employee->paymentMethod()->exists() ? $employee->paymentMethod->method : '---';
			$modelTable["Sueldo neto (opcional)"] = $employee->netIncome != null ? '$ '.number_format($employee->netIncome,2) : '---'; 
			$modelTable["Viaticos (opcional)"] = $employee->viatics != null ? '$ '.number_format($employee->viatics,2) : '---';
			$modelTable["Campamento (opcional)"] = $employee->camping != null ? '$ '.number_format($employee->camping,2) : '---';
			$modelTable["Complemento (si aplica)"] = $employee->complement != null ? '$ '.number_format($employee->complement,2) : '---';
			$modelTable["Monto Fonacot (si aplica)"] = $employee->fonacot != null ? '$ '.number_format($employee->fonacot,2) : '---';
			$modelTable["¿Requiere Equipo de Cómputo?"] = $employee->computerRequired();
		@endphp
		@component("components.templates.outputs.table-detail-single", 
			[
				"modelTable" => $modelTable, 
				"classEx" => "employee-details"
			]
		) 
		@endcomponent
	@endcomponent
</div>
@if(($employee->bankData != "" && $employee->bankData->where('type',1)->count() > 0) || ($employee->staffAccounts != "" && $employee->staffAccounts->where('type',1)->count() > 0))
	@component('components.labels.title-divisor') 
		Cuentas Bancarias
	@endcomponent
	<div class="content">
		@php
			$body		= [];
			$modelBody	= [];
			$modelHead	= ["Alias", "Banco", "CLABE", "Cuenta", "Tarjeta", "Sucursal"];
			if($employee->bankData != "" && $employee->bankData->where('type',1)->count() > 0)
			{
				$banks = $employee->bankData->where('type',1);
			}
			elseif($employee->staffAccounts != "" && $employee->staffAccounts->where('type',1)->count() > 0)
			{
				$banks = $employee->staffAccounts->where('type',1);
			}
			foreach($banks as $bank)
			{
				$body = 
				[
					[
						"content" => 
						[
							[
								"label" => $bank->alias
							]
						]
					],
					[
						"content" => 
						[
							[
								"label" => $bank->bank->description
							]
						]
					],
					[
						"content" => 
						[
							[
								"label" => $bank->clabe != null ? $bank->clabe : '---'
							]
						]
					],
					[
						"content" => 
						[
							[
								"label" => $bank->account != null ? $bank->account : '---'
							]
						]
					],
					[
						"content" => 
						[
							[
								"label" => $bank->cardNumber != null ? $bank->cardNumber : '---'
							]
						]
					],
					[
						"content" => 
						[
							[
								"label" => $bank->branch != null ? $bank->branch : '---'
							]
						]
					]
				];
				array_push($modelBody, $body);
			}
		@endphp
		@component('components.tables.alwaysVisibleTable',[
			"modelHead" => $modelHead,
			"modelBody" => $modelBody,
			"themeBody" => "striped"
		])
			@slot('classEx')
				text-center table
			@endslot
		@endcomponent
	</div>
@endif
@if(($employee->bankData != "" && $employee->bankData->where('type',2)->count() > 0) || ($employee->staffAccounts != "" && $employee->staffAccounts->where('type',2)->count() > 0))
	@component('components.labels.title-divisor') 
		Cuentas Bancarias de Pensión Alimenticia
	@endcomponent
	<div class="content">
		@php
			$body 		= [];
			$modelBody	= [];
			$modelHead	= ["Beneficiario","Alias", "Banco", "CLABE", "Cuenta", "Tarjeta", "Sucursal"];
			if($employee->bankData != "" && $employee->bankData->where('type',2)->count() > 0)
			{
				$banks = $employee->bankData->where('type',2);
			}
			elseif($employee->staffAccounts != "" && $employee->staffAccounts->where('type',2)->count() > 0)
			{
				$banks = $employee->staffAccounts->where('type',2);
			}
			foreach($banks as $bank)
			{
				$body = 
				[
					[
						"content" => 
						[
							[
								"label" => $bank->beneficiary
							]
						]
					],
					[
						"content" => 
						[
							[
								"label" => $bank->alias
							]
						]
					],
					[
						"content" => 
						[
							[
								"label" => $bank->bank->description
							]
						]
					],
					[
						"content" => 
						[
							[
								"label" => $bank->clabe != null ? $bank->clabe : "---"
							]
						]
					],
					[
						"content" => 
						[
							[
								"label" => $bank->account != null ? $bank->account : '---'
							]
						]
					],
					[
						"content" => 
						[
							[
								"label" => $bank->cardNumber != null ? $bank->cardNumber : '---'
							]
						]
					],
					[
						"content" => 
						[
							[
								"label" => $bank->branch != null ? $bank->branch : '---'
							]
						]
					]
				];
				array_push($modelBody, $body);
			}
		@endphp
		@component('components.tables.alwaysVisibleTable',[
			"modelHead" => $modelHead,
			"modelBody" => $modelBody,
			"themeBody" => "striped"
		])
			@slot('classEx')
				text-center table
			@endslot
		@endcomponent
	</div>
@endif
@component('components.labels.title-divisor') 
	Lista de Documentos
@endcomponent
<div class="content">
	@php
		$body		= [];
		$modelHead	= ["Nombre de Documento", "Archivo"];
		if(\Storage::disk('public')->exists('/docs/staff/'.$employee->doc_birth_certificate))
		{
			$contentDocBirthCertificate =
			[
				[
					"kind"          => "components.buttons.button",
					"buttonElement" => "a",
					"variant"       => "secondary",
					"attributeEx"   => "target=\"_blank\" href=\"".url('docs/staff/'.$employee->doc_birth_certificate)."\"",
					"label"         => "ARCHIVO"
				]
			];
		}
		elseif(\Storage::disk('public')->exists('/docs/requisition/'.$employee->doc_birth_certificate))
		{
			$contentDocBirthCertificate =
			[
				[
					"kind"          => "components.buttons.button",
					"buttonElement" => "a",
					"variant"       => "secondary",
					"attributeEx"   => "target=\"_blank\" href=\"".url('docs/requisition/'.$employee->doc_birth_certificate)."\"",
					"label"         => "ARCHIVO"
				]
			];
		}
		else
		{
			$contentDocBirthCertificate =
			[
				[
					"label" => "Sin documento"
				]
			];			
		}

		if(\Storage::disk('public')->exists('/docs/staff/'.$employee->doc_proof_of_address))
		{
			$contentDocProofAddress =
			[
				[
					"kind"          => "components.buttons.button",
					"buttonElement" => "a",
					"variant"       => "secondary",
					"attributeEx"   => "target=\"_blank\" href=\"".url('docs/staff/'.$employee->doc_proof_of_address)."\"",
					"label"         => "ARCHIVO"
				]
			];
		}
		elseif(\Storage::disk('public')->exists('/docs/requisition/'.$employee->doc_proof_of_address))
		{
			$contentDocProofAddress =
			[
				[
					"kind"          => "components.buttons.button",
					"buttonElement" => "a",
					"variant"       => "secondary",
					"attributeEx"   => "target=\"_blank\" href=\"".url('docs/requisition/'.$employee->doc_proof_of_address)."\"",
					"label"         => "ARCHIVO"
				]
			];
		}
		else
		{
			$contentDocProofAddress =
			[
				[
					"label" => "Sin documento"
				]
			];	
		}

		if(\Storage::disk('public')->exists('/docs/staff/'.$employee->doc_nss))
		{
			$contentDocNss =
			[
				[
					"kind"          => "components.buttons.button",
					"buttonElement" => "a",
					"variant"       => "secondary",
					"attributeEx"   => "target=\"_blank\" href=\"".url('docs/staff/'.$employee->doc_nss)."\"",
					"label"         => "ARCHIVO"
				]
			];
		}
		elseif(\Storage::disk('public')->exists('/docs/requisition/'.$employee->doc_nss))
		{
			$contentDocNss =
			[
				[
					"kind"          => "components.buttons.button",
					"buttonElement" => "a",
					"variant"       => "secondary",
					"attributeEx"   => "target=\"_blank\" href=\"".url('docs/requisition/'.$employee->doc_nss)."\"",
					"label"         => "ARCHIVO"
				]
			];
		}
		else
		{
			$contentDocNss =
			[
				[
					"label" => "Sin documento"
				]
			];
		}

		if(\Storage::disk('public')->exists('/docs/staff/'.$employee->doc_ine))
		{	
			$contentDocIne =
			[
				[
					"kind"          => "components.buttons.button",
					"buttonElement" => "a",
					"variant"       => "secondary",
					"attributeEx"   => "target=\"_blank\" href=\"".url('docs/staff/'.$employee->doc_ine)."\"",
					"label"         => "ARCHIVO"
				]
			];
		}
		elseif(\Storage::disk('public')->exists('/docs/requisition/'.$employee->doc_ine))
		{		
			$contentDocIne =
			[
				[
					"kind"          => "components.buttons.button",
					"buttonElement" => "a",
					"variant"       => "secondary",
					"attributeEx"   => "target=\"_blank\" href=\"".url('docs/requisition/'.$employee->doc_ine)."\"",
					"label"         => "ARCHIVO"
				]
			];
		}
		else
		{
			$contentDocIne =
			[
				[
					"label" => "Sin documento"
				]
			];
		}

		if(\Storage::disk('public')->exists('/docs/staff/'.$employee->doc_curp))
		{
			$contentDocCurp =
			[
				[
					"kind"          => "components.buttons.button",
					"buttonElement" => "a",
					"variant"       => "secondary",
					"attributeEx"   => "target=\"_blank\" href=\"".url('docs/staff/'.$employee->doc_curp)."\"",
					"label"         => "ARCHIVO"
				]
			];
		}
		elseif(\Storage::disk('public')->exists('/docs/requisition/'.$employee->doc_curp))
		{
			$contentDocCurp =
			[
				[
					"kind"          => "components.buttons.button",
					"buttonElement" => "a",
					"variant"       => "secondary",
					"attributeEx"   => "target=\"_blank\" href=\"".url('docs/requisition/'.$employee->doc_curp)."\"",
					"label"         => "ARCHIVO"
				]
			];
		}
		else
		{
			$contentDocCurp =
			[
				[
					"label" => "Sin documento"
				]
			];
		}

		if(\Storage::disk('public')->exists('/docs/staff/'.$employee->doc_rfc))
		{
			$contentDocRfc =
			[
				[
					"kind"          => "components.buttons.button",
					"buttonElement" => "a",
					"variant"       => "secondary",
					"attributeEx"   => "target=\"_blank\" href=\"".url('docs/staff/'.$employee->doc_rfc)."\"",
					"label"         => "ARCHIVO"
				]
			];
		}
		elseif(\Storage::disk('public')->exists('/docs/requisition/'.$employee->doc_rfc))
		{
			$contentDocRfc =
			[
				[
					"kind"          => "components.buttons.button",
					"buttonElement" => "a",
					"variant"       => "secondary",
					"attributeEx"   => "target=\"_blank\" href=\"".url('docs/requisition/'.$employee->doc_rfc)."\"",
					"label"         => "ARCHIVO"
				]
			];
		}
		else
		{
			$contentDocRfc =
			[
				[
					"label" => "Sin documento"
				]
			];
		}

		if(\Storage::disk('public')->exists('/docs/staff/'.$employee->doc_cv))
		{		
			$contentDocCv =
			[
				[
					"kind"          => "components.buttons.button",
					"buttonElement" => "a",
					"variant"       => "secondary",
					"attributeEx"   => "target=\"_blank\" href=\"".url('docs/staff/'.$employee->doc_cv)."\"",
					"label"         => "ARCHIVO"
				]
			];
		}
		elseif(\Storage::disk('public')->exists('/docs/requisition/'.$employee->doc_cv))
		{	
			$contentDocCv =
			[
				[
					"kind"          => "components.buttons.button",
					"buttonElement" => "a",
					"variant"       => "secondary",
					"attributeEx"   => "target=\"_blank\" href=\"".url('docs/requisition/'.$employee->doc_cv)."\"",
					"label"         => "ARCHIVO"
				]
			];
		}
		else
		{
			$contentDocCv =
			[
				[
					"label" => "Sin documento"
				]
			];
		}

		if(\Storage::disk('public')->exists('/docs/staff/'.$employee->doc_proof_of_studies))
		{		
			$contentDocProofStudies =
			[
				[
					"kind"          => "components.buttons.button",
					"buttonElement" => "a",
					"variant"       => "secondary",
					"attributeEx"   => "target=\"_blank\" href=\"".url('docs/staff/'.$employee->doc_proof_of_studies)."\"",
					"label"         => "ARCHIVO"
				]
			];
		}
		elseif(\Storage::disk('public')->exists('/docs/requisition/'.$employee->doc_proof_of_studies))
		{	
			$contentDocProofStudies =
			[
				[
					"kind"          => "components.buttons.button",
					"buttonElement" => "a",
					"variant"       => "secondary",
					"attributeEx"   => "target=\"_blank\" href=\"".url('docs/requisition/'.$employee->doc_proof_of_studies)."\"",
					"label"         => "ARCHIVO"
				]
			];
		}
		else
		{
			$contentDocProofStudies =
			[
				[
					"label" => "Sin documento"
				]
			];
		}

		if(\Storage::disk('public')->exists('/docs/staff/'.$employee->doc_professional_license))
		{	
			$contentDocProofLicense =
			[
				[
					"kind"          => "components.buttons.button",
					"buttonElement" => "a",
					"variant"       => "secondary",
					"attributeEx"   => "target=\"_blank\" href=\"".url('docs/staff/'.$employee->doc_professional_license)."\"",
					"label"         => "ARCHIVO"
				]
			];
		}
		elseif(\Storage::disk('public')->exists('/docs/requisition/'.$employee->doc_professional_license))
		{		
			$contentDocProofLicense =
			[
				[
					"kind"          => "components.buttons.button",
					"buttonElement" => "a",
					"variant"       => "secondary",
					"attributeEx"   => "target=\"_blank\" href=\"".url('docs/requisition/'.$employee->doc_professional_license)."\"",
					"label"         => "ARCHIVO"
				]
			];
		}
		else
		{
			$contentDocProofLicense =
			[
				[
					"label" => "Sin documento"
				]
			];
		}

		if(\Storage::disk('public')->exists('/docs/staff/'.$employee->doc_requisition))
		{
			$contentDocRequisition =
			[
				[
					"kind"          => "components.buttons.button",
					"buttonElement" => "a",
					"variant"       => "secondary",
					"attributeEx"   => "target=\"_blank\" href=\"".url('docs/staff/'.$employee->doc_requisition)."\"",
					"label"         => "ARCHIVO"
				]
			];
		}
		elseif(\Storage::disk('public')->exists('/docs/requisition/'.$employee->doc_requisition))
		{
			$contentDocRequisition =
			[
				[
					"kind"          => "components.buttons.button",
					"buttonElement" => "a",
					"variant"       => "secondary",
					"attributeEx"   => "target=\"_blank\" href=\"".url('docs/requisition/'.$employee->doc_requisition)."\"",
					"label"         => "ARCHIVO"
				]
			];
		}
		else
		{
			$contentDocRequisition =
			[
				[
					"label" => "Sin documento"
				]
			];
		}
		$modelBody =
		[ 
			[
				"classEx" => "tr",
				[
					"content" => 
					[
						[
							"label" => "Acta de Nacimiento"
						]
					]
				],
				[
					"content" => $contentDocBirthCertificate
				],
			],
			[
				"classEx" => "tr",
				[
					"content" => 
					[
						[
							"label" => "Comprobante de Domicilio"
						]
					]
				],
				[
					"content" => $contentDocProofAddress
				],
			],
			[
				"classEx" => "tr",
				[
					"content" => 
					[
						[
							"label" => "Número  de Seguridad Social"
						]
					]
				],
				[
					"content" => $contentDocNss
				],
			],
			[
				"classEx" => "tr",
				[
					"content" => 
					[
						[
							"label" => "INE"
						]
					]
				],
				[
					"content" => $contentDocIne
				],
			],
			[
				"classEx" => "tr",
				[
					"content" => 
					[
						[
							"label" => "CURP"
						]
					]
				],
				[
					"content" => $contentDocCurp
				],
			],
			[
				"classEx" => "tr",
				[
					"content" => 
					[
						[
							"label" => "RFC"
						]
					]
				],
				[
					"content" =>  $contentDocRfc
				],
			],
			[
				"classEx" => "tr",
				[
					"content" => 
					[
						[
							"label" => "Curriculum Vitae/Solicitud de Empleo"
						]
					]
				],
				[
					"content" => $contentDocCv
				],
			],
			[
				"classEx" => "tr",
				[
					"content" => 
					[
						[
							"label" => "Comprobante de Estudios"
						]
					]
				],
				[
					"content" => $contentDocProofStudies
				],
			],
			[
				"classEx" => "tr",
				[
					"content" => 
					[
						[
							"label" => "Cédula Profesional"
						]
					]
				],
				[
					"content" => $contentDocProofLicense
				],
			],
			[
				"classEx" => "tr",
				[
					"content" => 
					[
						[
							"label" => "Requisición Firmada"
						]
					]
				],
				[
					"content" => $contentDocRequisition
				],
			]
		];

		if(isset($employee->documents))
		{
			foreach($employee->documents as $doc)
			{
				array_push($modelBody, [
					"classEx" => "tr",
					[
						"content" => 
						[
							[
								"label" => $doc->name
							]
						]
					],
					[
						"content" => $doc->path != "" ?  [["kind" => "components.buttons.button", "buttonElement" => "a", "variant"       => "secondary", "attributeEx"   => "target=\"_blank\" href=\"".url('docs/requisition/'.$employee->doc_requisition)."\"", "label"         => "Archivo"]] : [["label" => "Sin documento"]]
					],
				]);
			}
		}

		if(isset($employee->staffDocuments))
		{
			foreach($employee->staffDocuments as $doc)
			{
				array_push($modelBody, [
					"classEx" => "tr",
					[
						"content" => 
						[
							[
								"label" => $doc->name
							]
						]
					],
					[
						"content" => $doc->path != "" ?  [["kind" => "components.buttons.button", "buttonElement" => "a", "variant" => "secondary", "attributeEx" => "target=\"_blank\" href=\"".url('docs/staff/'.$doc->path)."\"", "label" => "Archivo"]] : [["label" => "Sin documento"]]
					],
				]);
			}
		}
	@endphp
	@component('components.tables.alwaysVisibleTable',[
		"modelHead" => $modelHead,
		"modelBody" => $modelBody,
		"themeBody" => "striped"
	])
		@slot('classEx')
			text-center table
		@endslot
	@endcomponent
</div>