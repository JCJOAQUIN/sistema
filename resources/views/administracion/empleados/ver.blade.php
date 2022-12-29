@extends('layouts.child_module')
@section('data')
	@component('components.labels.title-divisor') Detalles del Empleado @endcomponent
	@php
		$modelTable =
		[
			"Nombre"						=> $employee->fullName(),
			"CURP"							=> $employee->curp	!= '' ? $employee->curp : '---',
			"RFC"							=> $employee->rfc	!= '' ? $employee->rfc	: '---',
			"Régimen Fiscal"				=> $employee->taxRegime()->exists() ? $employee->taxRegime->taxRegime.' '.$employee->taxRegime->description : '---',
			"No. de IMSS"					=> $employee->imss	!= '' ? $employee->imss 	: '---',
			"Correo Electrónico"			=> $employee->email != '' ? $employee->email	: '---',
			"Número teléfonico"				=> $employee->phone	!= '' ? $employee->phone	: '---',
			"Dirección"						=> $employee->street.' '.$employee->number.' '.$employee->colony.' '.$employee->cp.' '.$employee->city.' '.$employee->states->description,
			"En reemplazo de"				=> $employee->replace != '' ? $employee->replace : '---',
			"Próposito básico del puesto"	=> $employee->purpose != '' ? $employee->purpose : '---',
			"Requerimientos del puesto"		=> $employee->requirements != '' ? $employee->requirements : '---',
			"Observaciones"					=> $employee->observations != '' ? $employee->observations : '---',
		];
	@endphp
	@component('components.templates.outputs.table-detail-single', ["modelTable" => $modelTable]) @endcomponent
	@php
		$account = '';
		if($employee->workerDataVisible->first()->accounts()->exists())
		{
			$account = $employee->workerDataVisible->first()->accounts->account.' - '.$employee->workerDataVisible->first()->accounts->description;
		}
		$subdepartments = "";
		if($employee->workerDataVisible->first()->employeeHasSubdepartment()->exists())
		{
			foreach($employee->workerDataVisible->first()->employeeHasSubdepartment as $subdepartment)
			{
				$subdepartments .= $subdepartment->name.", ";
			}
		}
		else
		{
			$subdepartments = "---";
		}
		$table = 
		[
			"Estado Laboral"									=> $employee->workerDataVisible->first()->states()->exists() 		? $employee->workerDataVisible->first()->states->description	: '---',
			"Proyecto"											=> $employee->workerDataVisible->first()->projects()->exists()		? $employee->workerDataVisible->first()->projects->proyectName	: '---',
			"Empresa"											=> $employee->workerDataVisible->first()->enterprises()->exists()	? $employee->workerDataVisible->first()->enterprises->name		: '---',
			"Clasificación de Gasto"							=> $account,
			"Dirección"											=> $employee->workerDataVisible->first()->directions()->exists()	? $employee->workerDataVisible->first()->directions->name	: '---',
			"Departamento"										=> $employee->workerDataVisible->first()->departments()->exists()	? $employee->workerDataVisible->first()->departments->name	: '---',
			"Subdepartamento"									=> $subdepartments,
			"Puesto"											=> $employee->workerDataVisible->first()->position 					!= '' ? $employee->workerDataVisible->first()->position 						: '---',
			"Jefe Inmediato"									=> $employee->workerDataVisible->first()->immediate_boss 			!= '' ? $employee->workerDataVisible->first()->immediate_boss					: '---',
			"Puesto del Jefe Inmediato"							=> $employee->workerDataVisible->first()->position_immediate_boss	!= '' ? $employee->workerDataVisible->first()->position_immediate_boss			: '---',
			"Fecha de ingreso"									=> $employee->workerDataVisible->first()->admissionDate 			!= '' ? $employee->workerDataVisible->first()->admissionDate->format('d-m-Y')	: '---',
			"Estado de IMSS"									=> $employee->workerDataVisible->first()->statusImss(),
			"Fecha de alta (si aplica)"							=> $employee->workerDataVisible->first()->imssDate		!= '' ? $employee->workerDataVisible->first()->imssDate->format('d-m-Y')	: '---',
			"Fecha de baja (si aplica)"							=> $employee->workerDataVisible->first()->downDate		!= '' ? $employee->workerDataVisible->first()->downDate->format('d-m-Y')	: '---',
			"Fecha de término de relación laboral (si aplica)"	=> $employee->workerDataVisible->first()->endingDate	!= '' ? $employee->workerDataVisible->first()->endingDate->format('d-m-Y')	: '---',
			"Reingreso (si aplica)"								=> $employee->workerDataVisible->first()->reentryDate	!= '' ? $employee->workerDataVisible->first()->reentryDate->format('d-m-Y')	: '---',
			"Tipo de Trabajador"								=> $employee->workerDataVisible->first()->worker()->exists() ? $employee->workerDataVisible->first()->worker->description : '---',
			"Régimen"											=> $employee->workerDataVisible->first()->regime()->exists() ? $employee->workerDataVisible->first()->regime->description : '---',
			"Estado de Empleado"								=> $employee->workerDataVisible->first()->workerStatus(),
			"Motivo de estatus (opcional)"						=> $employee->workerDataVisible->first()->status_reason != '' ? $employee->workerDataVisible->first()->status_reason				: '---',
			"SDI (si aplica)"									=> $employee->workerDataVisible->first()->sdi			!= '' ? '$ '.number_format($employee->workerDataVisible->first()->sdi,2)	: '$ 0.00',
			"Periodicidad"										=> $employee->workerDataVisible->first()->periodicities()->exists() ? $employee->workerDataVisible->first()->periodicities->description : '---',
			"Registro Patronal"									=> $employee->employer_register != '' ? $employee->employer_register : '---',
			"Forma de Pago"										=> $employee->workerDataVisible->first()->paymentMethod()->exists() ? $employee->workerDataVisible->first()->paymentMethod->method : '---',
			"Sueldo neto (opcional)"							=> $employee->workerDataVisible->first()->netIncome 	!= '' ? '$ '.number_format($employee->workerDataVisible->first()->netIncome)	: '$ 0.00',
			"Viaticos (opcional)"								=> $employee->workerDataVisible->first()->viatics		!= '' ? '$ '.number_format($employee->workerDataVisible->first()->viatics,2)	: '$ 0.00',
			"Campamento (opcional)"								=> $employee->workerDataVisible->first()->camping 		!= '' ? '$ '.number_format($employee->workerDataVisible->first()->camping,2)	: '$ 0.00',
			"Complemento (si aplica)"							=> $employee->workerDataVisible->first()->complement	!= '' ? '$ '.number_format($employee->workerDataVisible->first()->complement,2)	: '$ 0.00',
			"Monto Fonacot (si aplica)"							=> $employee->workerDataVisible->first()->fonacot 		!= '' ? '$ '.number_format($employee->workerDataVisible->first()->fonacot,2)	: '$ 0.00'
		];
	@endphp
	@component('components.templates.outputs.table-detail-single', ["modelTable" => $table]) @endcomponent
	@if($employee->bankData->where('type',1)->count() > 0)
		@component('components.labels.title-divisor') Cuentas Bancarias @endcomponent
		@php
			$body 		= [];
			$modelBody	= [];
			$modelHead	= [
				[
					["value" => "Alias"],
					["value" => "Banco"],
					["value" => "CLABE"],
					["value" => "Cuenta"],
					["value" => "Tarjeta"],
					["value" => "Sucursal"],
				]
			];
			foreach($employee->bankData->where('type',1) as $bank)
			{
				$body =
				[
					[
						"content" =>
						[
							"label" => $bank->alias
						]
					],
					[
						"content" =>
						[
							"label" => $bank->bank->description
						]
					],
					[
						"content" =>
						[
							"label" => $bank->clabe != '' ? $bank->clabe : '---'
						]
					],
					[
						"content" =>
						[
							"label" => $bank->account != '' ? $bank->account : '---'
						]
					],
					[
						"content" =>
						[
							"label" => $bank->cardNumber != '' ? $bank->cardNumber: '---'
						]
					],
					[
						"content" =>
						[
							"label" => $bank->branch != '' ? $bank->branch : '---'
						]
					]
				];
				$modelBody[] = $body;
			}
		@endphp
		@component('components.tables.table',
			[
				"modelBody" => $modelBody,
				"modelHead" => $modelHead,
			])
		@endcomponent
	@endif
	@if($employee->bankData->where('type',2)->count() > 0)
		@component('components.labels.title-divisor') Cuentas Bancarias de Pensión Alimenticia @endcomponent
		@php
			$body		= [];
			$modelBody	= [];
			$modelHead	= [
				[
					["value" => "Beneficiario"],
					["value" => "Alias"],
					["value" => "Banco"],
					["value" => "CLABE"],
					["value" => "Cuenta"],
					["value" => "Tarjeta"],
					["value" => "Sucursal"]
				]
			];
			foreach($employee->bankData->where('type',2) as $bank)
			{
				$body =
				[
					[						
						"content"	=>
						[
							"label" => $bank->beneficiary
						]
					],
					[
						"content"	=>
						[
							"label" => $bank->alias
						]
					],
					[
						"content" =>
						[
							"label" => $bank->bank->description
						]
					],
					[
						"content" =>
						[
							"label" => $bank->clabe != '' ? $bank->clabe : '---'
						]
					],
					[
						"content" =>
						[
							"label" => $bank->account != '' ? $bank->account : '---'
						]
					],
					[
						"content" =>
						[
							"label" => $bank->cardNumber != '' ? $bank->cardNumber: '---'
						]
					],
					[
						"content" =>
						[
							"label" => $bank->branch != '' ? $bank->branch : '---'
						]
					]
				];
				$modelBody[] = $body;
			}
		@endphp
		@component('components.tables.table',
			[
				"modelBody" => $modelBody,
				"modelHead" => $modelHead,
			])
		@endcomponent
	@endif
	@component('components.labels.title-divisor') Lista de Documentos @endcomponent
	@php
		$modelBody		= [];
		$modelHead		= [ "Nombre de Documento", "Archivo" ];
		
		$docBirthCertificate = [];
		if(\Storage::disk('public')->exists('/docs/requisition/'.$employee->doc_birth_certificate))
		{
			$docBirthCertificate =
			[
				[
					"kind"			=> "components.buttons.button",
					"variant"		=> "secondary",
					"buttonElement" => "a",
					"attributeEx"	=> "type=\"button\" target=\"_blank\" href=\"".url('docs/requisition/'.$employee->doc_birth_certificate)."\"",
					"label"			=> "Archivo"
				]
			];
		}
		elseif(\Storage::disk('public')->exists('/docs/staff/'.$employee->doc_birth_certificate))
		{
			$docBirthCertificate =
			[
				[
					"kind"			=> "components.buttons.button",
					"variant"		=> "secondary",
					"buttonElement" => "a",
					"attributeEx"	=> "type=\"button\" target=\"_blank\" href=\"".url('docs/staff/'.$employee->doc_birth_certificate)."\"",
					"label"			=> "Archivo"
				]
			];
		}
		else
		{
			$docBirthCertificate = [[ "label" => "Sin documento" ]];
		}

		$docAddress = [];
		if(\Storage::disk('public')->exists('/docs/requisition/'.$employee->doc_proof_of_address))
		{
			$docAddress =
			[
				[
					"kind"			=> "components.buttons.button",
					"variant"		=> "secondary",
					"buttonElement" => "a",
					"attributeEx"	=> "type=\"button\" target=\"_blank\" href=\"".url('docs/requisition/'.$employee->doc_proof_of_address)."\"",
					"label"			=> "Archivo"
				]
			];
		}
		elseif(\Storage::disk('public')->exists('/docs/staff/'.$employee->doc_proof_of_address))
		{
			$docAddress =
			[
				[
					"kind"			=> "components.buttons.button",
					"variant"		=> "secondary",
					"buttonElement" => "a",
					"attributeEx"	=> "type=\"button\" target=\"_blank\" href=\"".url('docs/staff/'.$employee->doc_proof_of_address)."\"",
					"label"			=> "Archivo"
				]
			];
		}
		else
		{
			$docAddress = [[ "label" => "Sin documento" ]];
		}

		$docNSS = [];
		if(\Storage::disk('public')->exists('/docs/requisition/'.$employee->doc_nss))
		{
			$docNSS =
			[
				[
					"kind"			=> "components.buttons.button",
					"variant"		=> "secondary",
					"buttonElement" => "a",
					"attributeEx"	=> "type=\"button\" target=\"_blank\" href=\"".url('docs/requisition/'.$employee->doc_nss)."\"",
					"label"			=> "Archivo"
				]
			];
		}
		elseif(\Storage::disk('public')->exists('/docs/staff/'.$employee->doc_nss))
		{
			$docNSS =
			[
				[
					"kind"			=> "components.buttons.button",
					"variant"		=> "secondary",
					"buttonElement" => "a",
					"attributeEx"	=> "type=\"button\" target=\"_blank\" href=\"".url('docs/staff/'.$employee->doc_nss)."\"",
					"label"			=> "Archivo"
				]
			];
		}
		else
		{
			$docNSS = [[ "label" => "Sin documento" ]];
		}

		$docINE = [];
		if(\Storage::disk('public')->exists('/docs/requisition/'.$employee->doc_ine))
		{
			$docINE =
			[
				[
					"kind"			=> "components.buttons.button",
					"variant"		=> "secondary",
					"buttonElement" => "a",
					"attributeEx"	=> "type=\"button\" target=\"_blank\" href=\"".url('docs/requisition/'.$employee->doc_ine)."\"",
					"label"			=> "Archivo"
				]
			];
		}
		elseif(\Storage::disk('public')->exists('/docs/staff/'.$employee->doc_ine))
		{
			$docINE =
			[
				[
					"kind"			=> "components.buttons.button",
					"variant"		=> "secondary",
					"buttonElement" => "a",
					"attributeEx"	=> "type=\"button\" target=\"_blank\" href=\"".url('docs/staff/'.$employee->doc_ine)."\"",
					"label"			=> "Archivo"
				]
			];
		}
		else
		{
			$docINE = [[ "label" => "Sin documento" ]];
		}

		$docCURP = [];
		if(\Storage::disk('public')->exists('/docs/requisition/'.$employee->doc_curp))
		{
			$docCURP =
			[
				[
					"kind"			=> "components.buttons.button",
					"variant"		=> "secondary",
					"buttonElement" => "a",
					"attributeEx"	=> "type=\"button\" target=\"_blank\" href=\"".url('docs/requisition/'.$employee->doc_curp)."\"",
					"label"			=> "Archivo"
				]
			];
		}
		elseif(\Storage::disk('public')->exists('/docs/staff/'.$employee->doc_curp))
		{
			$docCURP =
			[
				[
					"kind"			=> "components.buttons.button",
					"variant"		=> "secondary",
					"buttonElement" => "a",
					"attributeEx"	=> "type=\"button\" target=\"_blank\" href=\"".url('docs/staff/'.$employee->doc_curp)."\"",
					"label"			=> "Archivo"
				]
			];
		}
		else
		{
			$docCURP = [[ "label" => "Sin documento" ]];
		}

		$docRFC = [];
		if(\Storage::disk('public')->exists('/docs/requisition/'.$employee->doc_rfc))
		{
			$docRFC =
			[
				[
					"kind"			=> "components.buttons.button",
					"variant"		=> "secondary",
					"buttonElement" => "a",
					"attributeEx"	=> "type=\"button\" target=\"_blank\" href=\"".url('docs/requisition/'.$employee->doc_rfc)."\"",
					"label"			=> "Archivo"
				]
			];
		}
		elseif(\Storage::disk('public')->exists('/docs/staff/'.$employee->doc_rfc))
		{
			$docRFC =
			[
				[
					"kind"			=> "components.buttons.button",
					"variant"		=> "secondary",
					"buttonElement" => "a",
					"attributeEx"	=> "type=\"button\" target=\"_blank\" href=\"".url('docs/staff/'.$employee->doc_rfc)."\"",
					"label"			=> "Archivo"
				]
			];
		}
		else
		{
			$docRFC = [[ "label" => "Sin documento" ]];
		}

		$docCurriculum = [];
		if(\Storage::disk('public')->exists('/docs/requisition/'.$employee->doc_cv))
		{
			$docCurriculum =
			[
				[
					"kind"			=> "components.buttons.button",
					"variant"		=> "secondary",
					"buttonElement" => "a",
					"attributeEx"	=> "type=\"button\" target=\"_blank\" href=\"".url('docs/requisition/'.$employee->doc_cv)."\"",
					"label"			=> "Archivo"
				]
			];
		}
		elseif(\Storage::disk('public')->exists('/docs/staff/'.$employee->doc_cv))
		{
			$docCurriculum =
			[
				[
					"kind"			=> "components.buttons.button",
					"variant"		=> "secondary",
					"buttonElement" => "a",
					"attributeEx"	=> "type=\"button\" target=\"_blank\" href=\"".url('docs/staff/'.$employee->doc_cv)."\"",
					"label"			=> "Archivo"
				]
			];
		}
		else
		{
			$docCurriculum = [[ "label" => "Sin documento" ]];
		}

		$docStudies = [];
		if(\Storage::disk('public')->exists('/docs/requisition/'.$employee->doc_proof_of_studies))
		{
			$docStudies =
			[
				[
					"kind"			=> "components.buttons.button",
					"variant"		=> "secondary",
					"buttonElement" => "a",
					"attributeEx"	=> "type=\"button\" target=\"_blank\" href=\"".url('docs/requisition/'.$employee->doc_proof_of_studies)."\"",
					"label"			=> "Archivo"
				]
			];
		}
		elseif(\Storage::disk('public')->exists('/docs/staff/'.$employee->doc_proof_of_studies))
		{
			$docStudies =
			[
				[
					"kind"			=> "components.buttons.button",
					"variant"		=> "secondary",
					"buttonElement" => "a",
					"attributeEx"	=> "type=\"button\" target=\"_blank\" href=\"".url('docs/staff/'.$employee->doc_proof_of_studies)."\"",
					"label"			=> "Archivo"
				]
			];
		}
		else
		{
			$docStudies = [[ "label" => "Sin documento" ]];
		}

		$docProLicense = [];
		if(\Storage::disk('public')->exists('/docs/requisition/'.$employee->doc_professional_license))
		{
			$docProLicense =
			[
				[
					"kind"			=> "components.buttons.button",
					"variant"		=> "secondary",
					"buttonElement" => "a",
					"attributeEx"	=> "type=\"button\" target=\"_blank\" href=\"".url('docs/requisition/'.$employee->doc_professional_license)."\"",
					"label"			=> "Archivo"
				]
			];
		}
		elseif(\Storage::disk('public')->exists('/docs/staff/'.$employee->doc_professional_license))
		{
			$docProLicense =
			[
				[
					"kind"			=> "components.buttons.button",
					"variant"		=> "secondary",
					"buttonElement" => "a",
					"attributeEx"	=> "type=\"button\" target=\"_blank\" href=\"".url('docs/staff/'.$employee->doc_professional_license)."\"",
					"label"			=> "Archivo"
				]
			];
		}
		else
		{
			$docProLicense = [[ "label" => "Sin documento" ]];
		}

		$docRequisition = [];
		if(\Storage::disk('public')->exists('/docs/requisition/'.$employee->doc_requisition))
		{
			$docRequisition =
			[
				[
					"kind"			=> "components.buttons.button",
					"variant"		=> "secondary",
					"buttonElement" => "a",
					"attributeEx"	=> "type=\"button\" target=\"_blank\" href=\"".url('docs/requisition/'.$employee->doc_requisition)."\"",
					"label"			=> "Archivo"
				]
			];
		}
		elseif(\Storage::disk('public')->exists('/docs/staff/'.$employee->doc_requisition))
		{
			$docRequisition =
			[
				[
					"kind"			=> "components.buttons.button",
					"variant"		=> "secondary",
					"buttonElement" => "a",
					"attributeEx"	=> "type=\"button\" target=\"_blank\" href=\"".url('docs/staff/'.$employee->doc_requisition)."\"",
					"label"			=> "Archivo"
				]
			];
		}
		else
		{
			$docRequisition = [[ "label" => "Sin documento" ]];
		}

		$modelBody = 
		[
			[
				[	
					"content" => [[ "label" => "Acta de Nacimiento" ]]
				],
				[
					"content" => $docBirthCertificate
				]
			],
			[
				[
					"content" => [[ "label" => "Comprobante de Domicilio" ]]
				],
				[
					"content" => $docAddress
				]
			],
			[
				[
					"content" => [[ "label" => "Número  de Seguridad Social" ]]
				],
				[
					"content" => $docNSS
				]
			],
			[
				[
					"content" => [[ "label" => "INE" ]]
				],
				[
					"content" => $docINE
				]
			],
			[
				[
					"content" => [[ "label" => "CURP" ]]
				],
				[
					"content" => $docCURP
				]
			],
			[
				[
					"content" => [[ "label" => "RFC" ]]
				],
				[
					"content" => $docRFC
				]
			],
			[
				[
					"content" => [[ "label" => "Curriculum Vitae/Solicitud de Empleo" ]]
				],
				[
					"content" => $docCurriculum
				]
			],
			[
				[
					"content" => [[ "label" => "Comprobante de Estudios" ]]
				],
				[
					"content" => $docStudies
				]
			],
			[
				[
					"content" => [[ "label" => "Cédula Profesional" ]]
				],
				[
					"content" => $docProLicense
				]
			],
			[
				[
					"content" => [[ "label" => "Requisición Firmada" ]]
				],
				[
					"content" => $docRequisition
				]
			]
		];
		$docEmployee = [];
		$docPath	 = [];
		foreach($employee->documents as $doc)
		{
			if($doc->path != "")		
			{
				if(\Storage::disk('public')->exists('/docs/requisition/'.$doc->path))
				{
					$docPath =
					[
						[
							"kind"			=> "components.buttons.button",
							"variant"		=> "secondary",
							"buttonElement" => "a",
							"attributeEx"	=> "type=\"button\" target=\"_blank\" href=\"".url('docs/requisition/'.$doc->path)."\"",
							"label"			=> "Archivo"
						]
					];
				}
				elseif(\Storage::disk('public')->exists('/docs/staff/'.$doc->path))
				{
					$docPath =
					[
						[
							"kind"			=> "components.buttons.button",
							"variant"		=> "secondary",
							"buttonElement" => "a",
							"attributeEx"	=> "type=\"button\" target=\"_blank\" href=\"".url('docs/staff/'.$doc->path)."\"",
							"label"			=> "Archivo"
						]
					];
				}
				else
				{
					$docPath = [[ "label" => "Sin documento" ]];
				}
			}
			$docEmployee =
			[
				[
					"content" => [[ "label" => $doc->name ]]
				],
				[
					"content" => $docPath
				]
			];
			$modelBody[] = $docEmployee;
		}
	@endphp
	@component('components.tables.alwaysVisibleTable',
		[
			"modelBody" => $modelBody,
			"modelHead" => $modelHead
		])
	@endcomponent
	<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mt-8">
		@component('components.buttons.button', ['variant' => 'reset', 'buttonElement' => 'a'])
			@slot('attributeEx')
				@if(isset($option_id)) 
					href="{{ url(App\Module::find($option_id)->url) }}" 
				@else 
					href="{{ url(App\Module::find($child_id)->url) }}" 
				@endif
			@endslot
			REGRESAR
			@slot('classEx')
				load-actioner
			@endslot
		@endcomponent
	</div>
@endsection