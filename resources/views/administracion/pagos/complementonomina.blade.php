@section('data')
	@php 
		$user		=	App\User::find($request->idRequest);
		$enterprise	=	App\Enterprise::find($request->idEnterprise);
		$area		=	App\Area::find($request->idArea);
		$department	=	App\Department::find($request->idDepartment);
		$account	=	App\Account::find($request->account);
	@endphp
	<div class="sm:text-center text-left my-5">
		A continuación podrá verificar la información de la solicitud antes de continuar con el proceso:
	</div>
	@php
		$requestUser	=	App\User::find($request->idRequest);
		$elaborateUser	=	App\User::find($request->idElaborate);
		$modelTable		=
		[
			["Folio:",			$request->folio],
			["Título y fecha:",	htmlentities($request->nominas->first()->title)." ".Carbon\Carbon::createFromFormat('Y-m-d',$request->nominas->first()->datetitle)->format('d-m-Y')],
			["Solicitante:",	$requestUser->name." ".$requestUser->last_name." ".$requestUser->scnd_last_name],
			["Elaborado por:",	$elaborateUser->name." ".$elaborateUser->last_name." ".$elaborateUser->scnd_last_name]
		]
	@endphp
	@component('components.templates.outputs.table-detail', ['modelTable' => $modelTable, "classEx" => "mt-4", "title" => "Detalles de la Solicitud"]) @endcomponent
	@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "DATOS DEL EMPLEADO"]) @endcomponent
	@php
		$modelHead	=	[];
		$body		=	[];
		$modelBody	=	[];
		$modelHead	=
		[
			[
				["value"	=>	"# Empleado"],
				["value"	=>	"Nombre del Empleado"],
				["value"	=>	"Empresa"],
				["value"	=>	"Proyecto"],
				["value"	=>	"Departamento",				"attributeEx"	=>	"hidden"],
				["value"	=>	"Dirección",				"attributeEx"	=>	"hidden"],
				["value"	=>	"Clasificación de gasto",	"attributeEx"	=>	"hidden"],
				["value"	=>	"Forma de pago"],
				["value"	=>	"Banco",					"attributeEx"	=>	"hidden"],
				["value"	=>	"Tarjeta",					"attributeEx"	=>	"hidden"],
				["value"	=>	"Cuenta",					"attributeEx"	=>	"hidden"],
				["value"	=>	"CLABE",					"attributeEx"	=>	"hidden"],
				["value"	=>	"Referencia"],
				["value"	=>	"Importe"],
				["value"	=>	"Razon"],
				["value"	=>	"Acción"]
			]
		];
		foreach (App\NominaAppEmp::join('users','idUsers','id')->where('idNominaApplication',$request->nominas->first()->idNominaApplication)->get() as $noEmp)
		{
			switch ($noEmp->idpaymentMethod)
			{
				case '1':
					$method	=	"Cuenta Bancaria";
					break;
				case '2':
					$method	=	"Efectivo";
					break;
				case '3':
					$method	=	"Cheque";
					break;
			}
			if ($request->status != 2)
			{
				$ver = "Ver datos";
			}
			$project			=	$noEmp->project()->exists() ? $noEmp->project->proyectName : 'No hay';
			$enterpriseName		=	$noEmp->enterprise()->exists() ? $noEmp->enterprise->name : "No hay";
			$departmentName		=	$noEmp->department()->exists() ? $noEmp->department->name : 'No hay';
			$areaName			=	$noEmp->area()->exists() ? $noEmp->area->name : 'No hay';
			$descriptionName	=	$noEmp->accounts()->exists() ? $noEmp->accounts->account.' - '.$noEmp->accounts->description.' ('.$noEmp->accounts->content.")" : 'No hay';
			$body	=
			[
				[
					"content"	=>
					[
						[
							"label"			=> isset($noEmp->idUsers) &&$noEmp->idUsers!="" ? $noEmp->idUsers : "---"
						],
						[
							"kind"			=>	"components.inputs.input-text",
							"attributeEx"	=>	"readonly type=\"hidden\" name=\"t_employee_number[]\" value=\"".$noEmp->idUsers."\"",
							"classEx"		=>	"iduser"
						]
					]
				],
				[
					"content"	=>
					[
						[
							"label"			=>	isset($noEmp->name) && $noEmp->name ? $noEmp->name." ".$noEmp->last_name." ".$noEmp->scnd_last_name : "---"
						],
						[
							"kind"			=>	"components.inputs.input-text",
							"attributeEx"	=>	"readonly type=\"hidden\" value=\"".$noEmp->name."\"",
							"classEx"		=>	"name"
						],
						[
							"kind"			=>	"components.inputs.input-text",
							"attributeEx"	=>	"readonly type=\"hidden\" value=\"".$noEmp->last_name."\"",
							"classEx"		=>	"last_name"
						],
						[
							"kind"			=>	"components.inputs.input-text",
							"attributeEx"	=>	"readonly type=\"hidden\" value=\"".$noEmp->scnd_last_name."\"",
							"classEx"		=>	"scnd_last_name"
						]
					]
				],
				[
					"content"	=>
					[
						[
							"label"			=>	$noEmp->enterprise()->exists() ? $noEmp->enterprise->name : 'No hay'
						],
						[
							"kind"			=>	"components.inputs.input-text",
							"attributeEx"	=>	"readonly name=\"t_enterprise[]\" type=\"hidden\" value=\"".$enterpriseName."\"",
							"classEx"		=>	"enterprise"
						]
					]
				],
				[
					"content"	=>
					[
						[
							"label"			=>	$noEmp->project()->exists() ? $noEmp->project->proyectName : 'No hay'
						],
						[
							"kind"			=>	"components.inputs.input-text", 
							"attributeEx"	=>	"readonly type=\"hidden\" name=\"t_project[]\" value=\"".$project."\"",
							"classEx"		=>	"project"
						]
					]
				],
				[
					"attributeEx"	=>	"hidden",
					"content"		=>
					[
						[
							"kind"			=>	"components.labels.label",
							"label"			=>	$noEmp->department()->exists() ? $noEmp->department->name : 'No hay',
							"attributeEx"	=>	"type=\"hidden\""
						],
						[
							"kind"			=>	"components.inputs.input-text",
							"attributeEx"	=>	"readonly name=\"t_department[]\" type=\"hidden\" value=\"".$departmentName."\"",
							"classEx"		=>	"department"
						]
					]
				],
				[
					"attributeEx"	=>	"hidden",
					"content"		=>
					[
						[
							"kind"			=>	"components.labels.label",
							"label"			=>	$noEmp->area()->exists() ? $noEmp->area->name : 'No hay',
							"attributeEx"	=>	"type=\"hidden\""
						],
						[
							"kind"			=>	"components.inputs.input-text",
							"attributeEx"	=>	"readonly name=\"t_direction[]\" type=\"hidden\" value=\"".$areaName."\"",
							"classEx"		=>	"area"
						]
					]
				],
				[
					"attributeEx"	=>	"hidden",
					"content"		=>
					[
						[
							"kind"			=>	"components.labels.label",
							"label"			=>	$noEmp->accounts()->exists() ? $noEmp->accounts->account.' - '.$noEmp->accounts->description : 'No hay',
							"attributeEx"	=>	"type=\"hidden\""
						],
						[
							"kind"			=>"components.inputs.input-text",
							"attributeEx"	=>"readonly name=\"t_accountid[]\" type=\"hidden\" value=\"".$descriptionName."\"",
							"classEx"		=>"accounttext"
						]
					]
				],
				[
					"content"	=>	["label"	=>	isset($method) && $method!="" ? $method : "---"]
				],
				[
					"attributeEx"	=>	"hidden",
					"content"		=>
					[
						[
							"kind"			=>	"components.labels.label",
							"label"			=>	 $noEmp->bank,
							"attributeEx"	=>	"type=\"hidden\""
						],
						[
							"kind"			=>	"components.inputs.input-text",
							"attributeEx"	=>	"readonly type=\"hidden\" name=\"t_bank[]\" value=\"".$noEmp->bank."\"",
							"classEx"		=>	"bank"
						]
					]
				],
				[
					"attributeEx"	=>	"hidden",
					"content"		=>
					[
						[
							"kind"			=>	"components.labels.label",
							"label"			=>	$noEmp->cardNumber,
							"attributeEx"	=>	"type=\"hidden\""
						],
						[
							"kind"			=>	"components.inputs.input-text",
							"attributeEx"	=>	"readonly type=\"hidden\" name=\"t_card_number[]\" value=\"". $noEmp->cardNumber."\"",
							"classEx"		=>	"cardNumber"
						]
					]
				],
				[
					"attributeEx"	=>	"hidden",
					"content"		=>
					[
						[
							"kind"			=>	"components.labels.label",
							"label"			=>	$noEmp->account,
							"attributeEx"	=>	"type=\"hidden\"",
						],
						[
							"kind"			=>	"components.inputs.input-text",
							"attributeEx"	=>	"readonly type=\"hidden\" name=\"t_account[]\" value=\"".$noEmp->account."\"",
							"classEx"		=>	"account"
						]
					]
				],
				[
					"attributeEx"	=>	"hidden",
					"content"		=>
					[
						[
							"kind"			=>	"components.labels.label",
							"label"			=>	$noEmp->clabe,
							"attributeEx"	=>	"type=\"hidden\""
						],
						[
							"kind"			=>	"components.inputs.input-text",
							"attributeEx"	=>	"readonly type=\"hidden\" name=\"t_clabe[]\" value=\"".$noEmp->clabe."\"",
							"classEx"		=>	"clabe"
						]
					]
				],
				[
					"content"	=>
					[
						[
							"label"			=>	isset($noEmp->reference) && $noEmp->reference!="" ? htmlentities($noEmp->reference) : "---"
						],
						[
							"kind"			=>	"components.inputs.input-text",
							"attributeEx"	=>	"readonly type=\"hidden\" name=\"t_reference[]\" value=\"".htmlentities($noEmp->reference)."\"",
							"classEx"		=>	"reference"
						]
					]
				],
				[
					"content"	=>
					[
						[
							"label"			=>	isset($noEmp->amount) && $noEmp->amount!="" ? "$ ".number_format($noEmp->amount,2) : "---"
						],
						[
							"kind"			=>	"components.inputs.input-text",
							"attributeEx"	=>	"readonly type=\"hidden\" name=\"t_amount[]\" value=\"".$noEmp->amount."\"",
							"classEx"		=>	"importe"
						]
					]
				],
				[
					"content"	=>
					[
						[
							"label"			=>	isset($noEmp->description) && $noEmp->description!="" ? $noEmp->description : "---"
						],
						[
							"kind"			=>	"components.inputs.input-text",
							"attributeEx"	=>	"readonly type=\"hidden\" name=\"t_reason_payment[]\" value=\"".$noEmp->description."\"",
							"classEx"		=>	"description"
						]
					]
				],
				[
					"content"	=>
					[
						[
							"kind"			=>	"components.buttons.button", 
							"label"			=>	$ver,
							"variant"		=>	"success",
							"attributeEx"	=>	"id=\"ver\" type=\"button\""
						]
					]
				]
			];
			$modelBody[]	=	$body;
		}
	@endphp
	@component('components.tables.table', ["modelHead" => $modelHead, "modelBody" => $modelBody, "classEx" => "mt-4"]) @endcomponent
	<div class="formulario border border-500-gray rounded-md hidden p-2.5 mt-2 mx-auto w-3/5">
		@php
			$modelTable	=
			[
				"Nombre"					=>	["kind"	=>	"components.labels.label",	"attributeEx"	=>	"id=\"nameEmp\""],
				"Empresa"					=>	["kind"	=>	"components.labels.label",	"attributeEx"	=>	"id=\"enterprise\""],
				"Departamento"				=>	["kind"	=>	"components.labels.label",	"attributeEx"	=>	"id=\"department\""],
				"Dirección"					=>	["kind"	=>	"components.labels.label",	"attributeEx"	=>	"id=\"area\""],
				"Proyecto"					=>	["kind"	=>	"components.labels.label",	"attributeEx"	=>	"id=\"project\""],
				"Clasificación del gasto"	=>	["kind"	=>	"components.labels.label",	"attributeEx"	=>	"id=\"accounttext\""],
				"Banco"						=>	["kind"	=>	"components.labels.label",	"attributeEx"	=>	"id=\"idBanksEmp\""],
				"Número de Tarjeta"			=>	["kind"	=>	"components.labels.label",	"attributeEx"	=>	"id=\"card_numberEmp\""],
				"Cuenta Bancaria"			=>	["kind"	=>	"components.labels.label",	"attributeEx"	=>	"id=\"accountEmp\""],
				"CLABE"						=>	["kind"	=>	"components.labels.label",	"attributeEx"	=>	"id=\"clabeEmp\""],
				"Referencia"				=>	["kind"	=>	"components.labels.label",	"attributeEx"	=>	"id=\"referenceEmp\""],
				"Importe"					=>	["kind"	=>	"components.labels.label",	"attributeEx"	=>	"id=\"amountEmp\""],
				"Razón de pago"				=>	["kind"	=>	"components.labels.label",	"attributeEx"	=>	"id=\"reason_paymentEmp\""]
			];
		@endphp
		@component('components.templates.outputs.table-detail-single', ["modelTable" => $modelTable, "classEx" => "employee-details"]) @endcomponent
		@component('components.buttons.button', ['variant' => 'success', "attributeEx" => "type=\"button\" name=\"canc\" id=\"exit\"", "label" => "« Ocultar"]) @endcomponent
	</div>
	<div class="flex justify-center mt-8">
		@component('components.labels.label', ["label" => "TOTAL: $ ".number_format($request->nominas->first()->amount,2), "classEx" => "w-auto text-center border-2 border-amber-500 rounded p-1.5 font-bold"]) @endcomponent
	</div>
	@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "DATOS DE REVISIÓN"]) @endcomponent
	@php
		$modelTable	=
		[
			"Revisó"		=>	$request->reviewedUser->name." ".$request->reviewedUser->last_name." ".$request->reviewedUser->scnd_last_name,
			"Comentarios"	=>	$request->checkComment == "" ? "Sin comentarios" : htmlentities($request->checkComment)
		]
	@endphp
	@component('components.templates.outputs.table-detail-single', ['modelTable' => $modelTable])@endcomponent
	@if($request->idAuthorize != "")
		@component('components.labels.title-divisor')
			@slot('classEx')
				mt-12
			@endslot
			DATOS DE AUTORIZACIÓN
		@endcomponent
		@php
			$modelTable	=
			[
				"Autorizó"		=>	$request->authorizedUser->name." ".$request->authorizedUser->last_name." ".$request->authorizedUser->scnd_last_name,
				"Comentarios"	=>	$request->authorizeComment == "" ? "Sin comentarios" : htmlentities($request->authorizeComment)
			]
		@endphp
		@component('components.templates.outputs.table-detail-single', ['modelTable' => $modelTable])@endcomponent
	@endif
	@php
		$payments		=	App\Payment::where('idFolio',$request->folio)->get();
		$total			=	$request->nominas->first()->amount;
		$totalPagado	=	0;
	@endphp
	@if(count($payments))
		@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "HISTORIAL DE PAGOS"]) @endcomponent
		@php
			$modelHead		=	[];
			$body			=	[];
			$modelBody		=	[];
			$modelHead		=
			[
				[
					["value"	=>	"Cuenta"],
					["value"	=>	"Cantidad"],
					["value"	=>	"Documento"],
					["value"	=>	"Fecha"]
				]
			];
			foreach ($payments as $pay)
			{
				$componentsExt	=	[];
				foreach ($pay->documentsPayments as $doc)
				{
					$componentsExt[]	=
					[
						"kind"			=>	"components.Buttons.button",
						"variant"		=>	"dark-red",
						"label"			=>	"PDF",
						"buttonElement"	=>	"a",
						"attributeEx"	=>	"target=\"_blank\" href=\"".asset('docs/payments/'.$doc->path)."\""."title=\"".$doc->path."\""
					];
				}
				$body	=
				[
					[
						"content"	=>	["label"	=>	$pay->accounts->account.' - '.$pay->accounts->description.' ('.$pay->accounts->content.")"]
					],
					[
						"content"	=>	["label"	=>	"$".number_format($pay->amount,2)]
					],
					[
						"content"	=>	$componentsExt
					],
					[
						"content"	=>	["label"	=>	Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$pay->paymentDate)->format('d-m-Y')]
					]
				];
				$modelBody[]	=	$body;
				$totalPagado	+=	$pay->amount;
			}
		@endphp
		@component('components.tables.table', ["modelHead" => $modelHead, "modelBody" => $modelBody])
			@slot('classEx')
				mt-4
			@endslot
		@endcomponent
		@php
			$model	=
			[
				["label"	=>	"Total pagado:",	"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"label"	=>	"$ ".number_format($totalPagado,2),			"classEx" => "h-10 py-2"]]],
				["label"	=>	"Resta:",			"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"label"	=>	"$ ".number_format($total-$totalPagado,2),	"classEx" => "h-10 py-2"]]]
			]
		@endphp
		@component('components.templates.outputs.form-details', ['modelTable' => $model])@endcomponent
	@endif
	@component('components.inputs.input-text', ["attributeEx" => "type=\"hidden\" id=\"restaTotal\" value=\"".($total-$totalPagado)."\""]) @endcomponent
	@component('components.inputs.input-text', ["attributeEx" => "type=\"hidden\" id=\"restaSubtotal\" value=\"".($total-$totalPagado)."\""]) @endcomponent
@endsection

@section('scripts')
<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
<script src="{{ asset('js/jquery-ui.js') }}"></script>
<script src="{{ asset('js/datepicker.js') }}"></script>
<script src="{{ asset('js/jquery.numeric.js') }}"></script>
<script>
	$(document).ready(function(){
		$(function()
		{
			$('.datepicker').datepicker(
			{
				dateFormat : 'dd-mm-yy',
			});
		});
	  	$('.card_number,.destination_account,.destination_key,.employee_number,.amount').numeric(false);    // números
	   	$('.amount,.importe',).numeric({ altDecimal: ".", decimalPlaces: 2 });
	
		$(document).on('click','#exit', function(){

			$(".formulario").slideToggle();
		})
		.on('click','.btn-delete-form',function(e)
		{
			e.preventDefault();
			form = $(this).parents('form');
			swal({
				title		: "Limpiar formulario",
				text		: "¿Confirma que desea limpiar el formulario?",
				icon		: "warning",
				buttons		: ["Cancelar","OK"],
				dangerMode	: true,
			})
			.then((willClean) =>
			{
				if(willClean)
				{
					$('#body-payroll').html('');
					$('.removeselect').val(null).trigger('change');
					form[0].reset();
				}
				else
				{
					swal.close();
				}
			});
		})
		.on('click','#ver',function()
		{
			nameEmp           = $(this).parent('div').parent('div').parent('div').parent('div').children('div').children('div').find('.name').val();
			lastnameEmp       = $(this).parent('div').parent('div').parent('div').parent('div').children('div').children('div').find('.last_name').val();
			scnd_last_nameEmp = $(this).parent('div').parent('div').parent('div').parent('div').children('div').children('div').find('.scnd_last_name').val();
			bankEmp           = $(this).parent('div').parent('div').children('div').find('.bank').val();
			cardEmp           = $(this).parent('div').parent('div').children('div').find('.cardNumber').val();
			accountEmp        = $(this).parent('div').parent('div').children('div').find('.account').val();
			clabeEmp          = $(this).parent('div').parent('div').children('div').find('.clabe').val();
			referenceEmp      = $(this).parent('div').parent('div').parent('div').parent('div').children('div').children('div').children('div').find('.reference').val();
			amountEmp         = $(this).parent('div').parent('div').parent('div').parent('div').children('div').children('div').children('div').find('.importe').val();
			reason_paymentEmp = $(this).parent('div').parent('div').parent('div').parent('div').children('div').children('div').children('div').find('.description').val();
			accounttext       = $(this).parent('div').parent('div').children('div').find('.accounttext').val();
			enterprise    	  = $(this).parent('div').parent('div').parent('div').parent('div').children('div').children('div').find('.enterprise').val();
			project           = $(this).parent('div').parent('div').parent('div').parent('div').children('div').children('div').children('div').find('.project').val();
			area              = $(this).parent('div').parent('div').children('div').find('.area').val();
			department        = $(this).parent('div').parent('div').children('div').find('.department').val();
			if(accountEmp == '')
			{
				accountEmp = '---';
			}

			if(cardEmp == '')
			{
				cardEmp = '---';
			}

			if(clabeEmp == '')
			{
				clabeEmp = '---';
			}			

			$('#nameEmp').html(nameEmp+' '+lastnameEmp+' '+scnd_last_nameEmp);
			$('#idBanksEmp').html(bankEmp);
			$('#card_numberEmp').html(cardEmp);
			$('#accountEmp').html(accountEmp);
			$('#clabeEmp').html(clabeEmp);
			$('#referenceEmp').html(referenceEmp);
			$('#amountEmp').html(amountEmp);
			$('#reason_paymentEmp').html(reason_paymentEmp);
			$('#accounttext').html(accounttext);
			$('#enterprise').html(enterprise);
			$('#project').html(project);
			$('#area').html(area);
			$('#department').html(department);
			$(".formulario").stop().slideToggle();
		})
	});
</script>
@endsection
