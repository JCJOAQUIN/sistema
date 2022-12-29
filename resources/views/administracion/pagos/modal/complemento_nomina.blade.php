@component('components.labels.title-divisor',["classExContainer" => "mb-6"]) DATOS DEL EMPLEADO @endcomponent
@php
    $modelHead = 
    [
        [
            ["value" => "# Empleado"],
            ["value" => "Nombre del Empleado"],
            ["value" => "Empresa"],
            ["value" => "Proyecto"],
            ["value" => "Departamento", "attributeEx" => "hidden"],
            ["value" => "Dirección", "attributeEx" => "hidden"],
            ["value" => "Clasificación de gasto", "attributeEx" => "hidden"],
            ["value" => "Forma de pago"],
            ["value" => "Banco", "attributeEx" => "hidden"],
            ["value" => "# Tarjeta", "attributeEx" => "hidden"],
            ["value" => "Cuenta", "attributeEx" => "hidden"],
            ["value" => "CLABE", "attributeEx" => "hidden"],
            ["value" => "Referencia"],
            ["value" => "Importe"],
            ["value" => "Razón"],
            ["value" => "Acción"]
        ]
    ];
    $modelBody = [];
    foreach(App\NominaAppEmp::join('users','idUsers','id')->where('idNominaApplication',$request->nominas->first()->idNominaApplication)->get() as $noEmp)
    {
        $enterprise	= $noEmp->enterprise()->exists() ? $noEmp->enterprise->name : 'No hay';
        $project	= $noEmp->project()->exists() ? $noEmp->project->proyectName : 'No hay';
        $department	= $noEmp->department()->exists() ? $noEmp->department->name : 'No hay';
        $area		= $noEmp->area()->exists() ? $noEmp->area->name : 'No hay';
        $account	= $noEmp->accounts()->exists() ? $noEmp->accounts->account.' - '.$noEmp->accounts->description.' ('.$noEmp->accounts->content.')' : 'No hay';
        $modelBody [] = 
        [
            "classEx" => "tr",
            [
                "classEx" => "td",
                "content" =>
                [
                    [
                        "kind" => "components.labels.label",
                        "label" => $noEmp->idUsers,
                    ],
                    [
                        "kind"        => "components.inputs.input-text",
                        "attributeEx" => "type=\"hidden\" name=\"t_employee_number[]\" value=\"".$noEmp->idUsers."\"",
                        "classEx"     => "iduser",
                    ],
                ],
            ],
            [
                "classEx" => "td",
                "content" =>
                [
                    [
                        "kind" => "components.labels.label",
                        "label" => $noEmp->name.' '.$noEmp->last_name.' '.$noEmp->scnd_last_name,
                    ],
                    [
                        "kind"        => "components.inputs.input-text",
                        "attributeEx" => "type=\"hidden\" value=\"".$noEmp->name."\"",
                        "classEx"     => "name",
                    ],
                    [
                        "kind"        => "components.inputs.input-text",
                        "attributeEx" => "type=\"hidden\" value=\"".$noEmp->last_name."\"",
                        "classEx"     => "last_name",
                    ],
                    [
                        "kind"        => "components.inputs.input-text",
                        "attributeEx" => "type=\"hidden\" value=\"".$noEmp->scnd_last_name."\"",
                        "classEx"     => "scnd_last_name",
                    ],
                ],
            ],
            [
                "classEx" => "td",
                "content" =>
                [
                    [
                        "kind" => "components.labels.label",
                        "label" => $enterprise,
                    ],
                    [
                        "kind"        => "components.inputs.input-text",
                        "attributeEx" => "type=\"hidden\" name=\"t_enterprise[]\" value=\"".$enterprise."\"",
                        "classEx"     => "enterprise",
                    ],
                ],
            ],
            [
                "classEx" => "td",
                "content" =>
                [
                    [
                        "kind" => "components.labels.label",
                        "label" => $project,
                    ],
                    [
                        "kind"        => "components.inputs.input-text",
                        "attributeEx" => "type=\"hidden\" name=\"t_project[]\" value=\"".$project."\"",
                        "classEx"     => "project",
                    ],
                ],
            ],
            [
                "classEx" => "td",
                "attributeEx" => "hidden",
                "content" =>
                [
                    [
                        "kind" => "components.labels.label",
                        "label" => $department,
                    ],
                    [
                        "kind"        => "components.inputs.input-text",
                        "attributeEx" => "type=\"hidden\" name=\"t_department[]\" value=\"".$department."\"",
                        "classEx"     => "department",
                    ],
                ],
            ],
            [
                "classEx" => "td",
                "attributeEx" => "hidden",
                "content" =>
                [
                    [
                        "kind" => "components.labels.label",
                        "label" => $area,
                    ],
                    [
                        "kind"        => "components.inputs.input-text",
                        "attributeEx" => "type=\"hidden\" name=\"t_direction[]\" value=\"".$area."\"",
                        "classEx"     => "area",
                    ],
                ],
            ],
            [
                "classEx" => "td",
                "attributeEx" => "hidden",
                "content" =>
                [
                    [
                        "kind" => "components.labels.label",
                        "label" => $account,
                    ],
                    [
                        "kind"        => "components.inputs.input-text",
                        "attributeEx" => "type=\"hidden\" name=\"t_accountid[]\" value=\"".$account."\"",
                        "classEx"     => "accounttext",
                    ],
                ],
            ],
            [
                "classEx" => "td",
                "content" =>
                [
                    [
                        "kind" => "components.labels.label",
                        "label" => $noEmp->paymentMethod->method,
                    ],
                ],
            ],
            [
                "classEx" => "td",
                "attributeEx" => "hidden",
                "content" =>
                [
                    [
                        "kind" => "components.labels.label",
                        "label" => $noEmp->bank,
                    ],
                    [
                        "kind"        => "components.inputs.input-text",
                        "attributeEx" => "type=\"hidden\" name=\"t_bank[]\" value=\"".$noEmp->bank."\"",
                        "classEx"     => "bank",
                    ],
                ],
            ],
            [
                "classEx" => "td",
                "attributeEx" => "hidden",
                "content" =>
                [
                    [
                        "kind" => "components.labels.label",
                        "label" => $noEmp->cardNumber,
                    ],
                    [
                        "kind"        => "components.inputs.input-text",
                        "attributeEx" => "type=\"hidden\" name=\"t_card_number[]\" value=\"".$noEmp->cardNumber."\"",
                        "classEx"     => "cardNumber",
                    ],
                ],
            ],
            [
                "classEx" => "td",
                "attributeEx" => "hidden",
                "content" =>
                [
                    [
                        "kind" => "components.labels.label",
                        "label" => $noEmp->account,
                    ],
                    [
                        "kind"        => "components.inputs.input-text",
                        "attributeEx" => "type=\"hidden\" name=\"t_account[]\" value=\"".$noEmp->account."\"",
                        "classEx"     => "account",
                    ],
                ],
            ],
            [
                "classEx" => "td",
                "attributeEx" => "hidden",
                "content" =>
                [
                    [
                        "kind" => "components.labels.label",
                        "label" => $noEmp->clabe,
                    ],
                    [
                        "kind"        => "components.inputs.input-text",
                        "attributeEx" => "type=\"hidden\" name=\"t_clabe[]\" value=\"".$noEmp->clabe."\"",
                        "classEx"     => "clabe",
                    ],
                ],
            ],
            [
                "classEx" => "td",
                "content" =>
                [
                    [
                        "kind" => "components.labels.label",
                        "label" => $noEmp->reference == "" ? "---": $noEmp->reference,
                    ],
                    [
                        "kind"        => "components.inputs.input-text",
                        "attributeEx" => "type=\"hidden\" name=\"t_reference[]\" value=\"".$noEmp->reference."\"",
                        "classEx"     => "reference",
                    ],
                ],
            ],
            [
                "classEx" => "td",
                "content" =>
                [
                    [
                        "kind" => "components.labels.label",
                        "label" => "$ ".$noEmp->amount,
                    ],
                    [
                        "kind"        => "components.inputs.input-text",
                        "attributeEx" => "type=\"hidden\" name=\"t_amount[]\" value=\"".$noEmp->amount."\"",
                        "classEx"     => "importe",
                    ],
                ],
            ],
            [
                "classEx" => "td",
                "content" =>
                [
                    [
                        "kind" => "components.labels.label",
                        "label" => $noEmp->description,
                    ],
                    [
                        "kind"        => "components.inputs.input-text",
                        "attributeEx" => "type=\"hidden\" name=\"t_reason_payment[]\" value=\"".$noEmp->description."\"",
                        "classEx"     => "description",
                    ],
                ],
            ],
            [
                "classEx" => "td",
                "content" =>
                [
                    [
                        "kind" => "components.buttons.button",
                        "attributeEx" => "alt=\"Ver datos\" title=\"Ver datos\" id=\"ver\" type=\"button\"",
                        "variant" => "success",
                        "label" => "Ver datos",
                    ],
                ],
            ],
        ];
    }
@endphp
@component("components.tables.table",[
        "modelHead" => $modelHead,
        "modelBody" => $modelBody,
        "themeBody" => "striped"
    ]);
    @slot('attributeExBody')
        id="body-payroll"
    @endslot
    @slot('classExBody')
        request-validate
    @endslot
@endcomponent
<div class="dataEmployee hidden border border-500-gray rounded-md hidden p-2.5 mt-2 mx-auto w-3/5">
    @php
        $modelTable	=
        [
            "Nombre"					=>	[["kind"	=>	"components.labels.label",	"attributeEx"	=>	"id=\"nameEmp\""]],
            "Empresa"					=>	[["kind"	=>	"components.labels.label",	"attributeEx"	=>	"id=\"enterprise\""]],
            "Departamento"				=>	[["kind"	=>	"components.labels.label",	"attributeEx"	=>	"id=\"department\""]],
            "Dirección"					=>	[["kind"	=>	"components.labels.label",	"attributeEx"	=>	"id=\"area\""]],
            "Proyecto"					=>	[["kind"	=>	"components.labels.label",	"attributeEx"	=>	"id=\"project\""]],
            "Clasificación del gasto"	=>	[["kind"	=>	"components.labels.label",	"attributeEx"	=>	"id=\"accounttext\""]],
            "Banco"						=>	[["kind"	=>	"components.labels.label",	"attributeEx"	=>	"id=\"idBanksEmp\""]],
            "Número de Tarjeta"			=>	[["kind"	=>	"components.labels.label",	"attributeEx"	=>	"id=\"card_numberEmp\""]],
            "Cuenta Bancaria"			=>	[["kind"	=>	"components.labels.label",	"attributeEx"	=>	"id=\"accountEmp\""]],
            "CLABE"						=>	[["kind"	=>	"components.labels.label",	"attributeEx"	=>	"id=\"clabeEmp\""]],
            "Referencia"				=>	[["kind"	=>	"components.labels.label",	"attributeEx"	=>	"id=\"referenceEmp\""]],
            "Importe"					=>	[["kind"	=>	"components.labels.label",	"attributeEx"	=>	"id=\"amountEmp\""]],
            "Razón de pago"				=>	[["kind"	=>	"components.labels.label",	"attributeEx"	=>	"id=\"reason_paymentEmp\""]],
        ];
    @endphp
    @component('components.templates.outputs.table-detail-single', ["modelTable" => $modelTable])
        @slot('classEx')
            employee-details
        @endslot
    @endcomponent
    <div class="mb-6">
        <div class="text-center">
            <p>
                @component('components.buttons.button', ['variant' => 'success'])
                    @slot('attributeEx')
                        type = "button"
                        name = "canc"
                        id = "exit"
                    @endslot
                    « Ocultar
                @endcomponent
            </p>
        </div>
    </div>
</div>
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