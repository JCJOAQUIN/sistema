@extends('layouts.child_module')

@section('data')
    @component('components.forms.form',[ "attributeEx" => "method=\"POST\" id=\"container-alta\" action=\"".route('requisition.vote.update', $request->folio)."\"", "methodEx" => "PUT", "files" => true ])
        <div class="sm:text-center text-left my-5">
            A continuación podrá verificar la información de la solicitud antes de continuar con el proceso:
        </div>
        @php
            $requestUser    = App\User::find($request->idRequest);
            $elaborateUser  = App\User::find($request->idElaborate);
            $modelTable = [
                [
                    'Tipo de Requisición:',
                    [
                        [
                            'kind'  => 'components.labels.label',
                            'label' => $request->requisition->typeRequisition->name
                        ]
                    ]
                ],
                [
                    'Proyecto:',
                    [
                        [
                            'kind'  => 'components.labels.label',
                            'label' => $request->requestProject()->exists() ? $request->requestProject->proyectName : 'No hay'
                        ]
                    ]
                ]
            ];
            if ($request->requisition->code_wbs != '')
            {
                array_push($modelTable, [
                    'Subproyecto/Código WBS:',
                    [
                        [
                            'kind'  => 'components.labels.label',
                            'label' => $request->requisition->wbs()->exists() ? $request->requisition->wbs->code_wbs : 'No hay'
                        ]
                    ]
                ]);
                array_push($modelTable, [
                    'Código EDT:',
                    [
                        [
                            'kind'  => 'components.labels.label',
                            'label' => $request->requisition->edt()->exists() ? $request->requisition->edt->fullName() : 'No hay'
                        ]
                    ]
                ]);
            }
            array_push($modelTable, [
                'Prioridad:',
                [
                    [
                        'kind'  => 'components.labels.label',
                        'label' => $request->requisition->urgent == 1 ? 'Alta' : 'Baja'
                    ]
                ]
            ]);
            array_push($modelTable, [
                'Folio:',
                [
                    [
                        'kind'  => 'components.labels.label',
                        'label' => $request->folio
                    ]
                ]
            ]);
            array_push($modelTable, [
                'Solicitante:   ',
                [
                    [
                        'kind'  => 'components.labels.label',
                        'label' => ($request->requisition()->exists() && $request->requisition->request_requisition != '' ? ($request->requisition()->exists() ? $request->requisition->request_requisition : 'Sin solicitante') : $request->requestUser()->exists()) ? $request->requestUser->fullName() : 'Sin solicitante'
                    ]
                ]
            ]);
            array_push($modelTable, [
                'Título:',
                [
                    [
                        'kind'  => 'components.labels.label',
                        'label' => htmlentities($request->requisition->title),
                    ]
                ]
            ]);
            array_push($modelTable, [
                'Número:',
                [
                    [
                        'kind'  => 'components.labels.label',
                        'label' => $request->requisition->number
                    ]
                ]
            ]);
            if ($request->requisition->generated_number != '')
            {
                array_push($modelTable, [
                    'Número de requisición:',
                    [
                        [
                            'kind'  => 'components.labels.label',
                            'label' => $request->requisition->generated_number
                        ]
                    ]
                ]);
            }
            if ($request->requisition->requisition_type == 5)
            {
                array_push($modelTable, [
                    'Compra/Renta:',
                    [
                        [
                            'kind'  => 'components.labels.label',
                            'label' => $request->requisition->buy_rent
                        ]
                    ]
                ]);
                if ($request->requisition->buy_rent == 'Renta')
                {
                    array_push($modelTable, [
                        'Vigencia:',
                        [
                            [
                                'kind'  => 'components.labels.label',
                                'label' => $request->requisition->validity
                            ]
                        ]
                    ]);
                }
            }
            array_push($modelTable, [
                'Fecha en que se solicitó:',
                [
                    [
                        'kind'  => 'components.labels.label',
                        'label' => Carbon\Carbon::createFromFormat('Y-m-d',$request->requisition->date_request)->format('d-m-Y')
                    ]
                ]
            ]);
            if ($request->requisition->date_obra != '')
            {
                array_push($modelTable, [
                    'Fecha en que debe estar en obra:',
                    [
                        [
                            'kind'  => 'components.labels.label',
                            'label' =>  Carbon\Carbon::createFromFormat('Y-m-d',$request->requisition->date_obra)->format('d-m-Y')
                        ]
                    ]
                ]);
            }
        @endphp
        @component('components.templates.outputs.table-detail',
            [
                'modelTable'    => $modelTable,
                'title'         => 'Detalles de la Solicitud',
            ])
        @endcomponent
        @if ($request->requisition->requisition_type != 3)
            @component('components.labels.title-divisor') CONCEPTOS @endcomponent
            <div class="flex flex-row justify-end">
                @component('components.buttons.button',["variant" => "success", "buttonElement" => "a"])
                    @slot('attributeEx')
                        type="button"
                        href="{{ route('requisition.export',$request->folio) }}"
                        title="Exportar a Excel"
                    @endslot
                    @slot('label')
                        <span>Exportar a Excel</span><span class="icon-file-excel"></span>
                    @endslot
                @endcomponent
            </div>
            @php
                $modelBody      = [];
                $usersVoting    = App\User::leftJoin('user_has_modules', 'users.id', 'user_has_modules.user_id')
                    ->leftJoin('permission_projects','user_has_modules.iduser_has_module','permission_projects.user_has_module_iduser_has_module')
                    ->leftJoin('permission_reqs','user_has_modules.iduser_has_module','permission_reqs.user_has_module_id')
                    ->where('user_has_modules.module_id', 276)
                    ->where('permission_projects.project_id', $request->idProject)
                    ->where('permission_reqs.requisition_type_id', $request->requisition->requisition_type)
                    ->get();
                /* Headers section */
                if (isset($request))
                {
                    switch ($request->requisition->requisition_type)
                    {
                        case 1:
                            $modelHead  = [
                                ['value' => 'Nombre', "show" => true],
                                ['value' => 'Descripción', "show" => true],
                                ['value' => 'Existencia en Almacén', "show" => true],
                                ['value' => 'Categoría'],
                                ['value' => 'Tipo'],
                                ['value' => 'Cant.'],
                                ['value' => 'Medida'],
                                ['value' => 'Unidad']
                            ];
                            break;
                        case 2:
                            $modelHead = [
                                ['value' => 'Nombre', "show" => true],
                                ['value' => 'Descripción', "show" => true],
                                ['value' => 'Periodo'],
                                ['value' => 'Categoría'],
                                ['value' => 'Cant.'],
                                ['value' => 'Unidad'],
                            ];
                            break;
                        case 4:
                            $modelHead  = [
                                ['value' => 'Nombre', "show" => true], 
                                ['value' => 'Descripción', "show" => true],
                                ['value' => 'Cant.'], 
                                ['value' => 'Unidad']
                            ];
                            break;
                        case 5:
                            $modelHead  = [
                                ['value' => 'Nombre', "show" => true], 
                                ['value' => 'Descripción', "show" => true], 
                                ['value' => 'Marca'], 
                                ['value' => 'Modelo'], 
                                ['value' => 'Tiempo de Utilización'],
                                ['value' => 'Existencia en Almacén'],
                                ['value' => 'Categoría'], 
                                ['value' => 'Cant.'], 
                                ['value' => 'Medida'], 
                                ['value' => 'Unidad']
                            ];
                            break;
                        case 6:
                            $modelHead  = [
                                ['value' => 'Nombre', "show" => true], 
                                ['value' => 'Descripción', "show" => true],
                                ['value' => 'Cant.'], 
                                ['value' => 'Unidad']
                            ];
                            break;
                    }
                    if(in_array($request->status,[3,4,5,17,27]))
                    {
                        array_splice($modelHead, count(array_column($modelHead,'show')), 0, [["value" => "Part."]]);
                    }
                    $modelGroup = 
                    [
                        [
                            "name"      =>  "Conceptos",
                            "id"        =>  "concepts",
                            "colNumber" =>  count(array_column($modelHead,'show'))
                        ],
                        [
                            "name"      => "Detalles",
                            "id"        =>  "details",
                            "colNumber" =>  (count($modelHead)-count(array_column($modelHead,'show')))
                        ]
                    ];
                    if ($request->requisition->requisitionHasProvider()->exists())
                    {
                        foreach ($request->requisition->requisitionHasProvider as $provider)
                        {
                            $modelHead[] = ["value" => "Precio Unitario"];
                            $modelHead[] = ["value" => "Subtotal"];
                            $modelHead[] = ["value" => "IVA"];
                            $modelHead[] = ["value" => "Impuesto Adicional"];
                            $modelHead[] = ["value" => "Retenciones"];
                            $modelHead[] = ["value" => "Total"];

                            $headersProvider =
                                "<div>
                                    <input type=\"hidden\" class=\"provider_count\" value=\"".$provider->providerData->businessName."\">
                                    <input type=\"hidden\" class=\"provider_exists_requisition\" value=\"".$provider->providerData->id."\">
                                    <input type=\"hidden\" name=\"idRequisitionHasProvider[]\" class=\"id_provider_secondary\" value=\"".$provider->id."\">
                                </div>";
                            if ($provider->documents()->exists())
                            {
                                $headersProvider .= view('components.buttons.button', [
                                    'classEx'       => 'viewDocumentProvider bg-white rounded rounded-full text-light-blue-500',
                                    'attributeEx'   => "data-id=\"".$provider->id."\"data-toggle=\"modal\" data-target=\"#viewDocumentProvider\" type=\"button\"",
                                    'label'         => "<span class=\"icon-search\"></span> Ver Documentos",
                                    'buttonElement' => 'noVariant',
                                ])->render();
                            }
                
                            $modelTable = [];
                            $modelTable['Tipo de Moneda']               = $provider->type_currency.view('components.inputs.input-text', ['attributeEx' => "readonly=\"readonly\" type=\"hidden\" name=\"type_currency_provider_".$provider->id."\"  value=\"".$provider->type_currency."\""])->render();
                            $modelTable['Tiempo de Entrega (Opcional)'] = htmlentities($provider->delivery_time).view('components.inputs.input-text', ['attributeEx' => "readonly=\"readonly\" type=\"hidden\" name=\"delivery_time_".$provider->id."\" value=\"".htmlentities($provider->delivery_time)."\""])->render();
                            $modelTable['Crédito Días (Opcional)']      = htmlentities($provider->credit_time).view('components.inputs.input-text', ['attributeEx' => "readonly=\"readonly\" type=\"hidden\" name=\"credit_time_".$provider->id."\" value=\"".htmlentities($provider->credit_time)."\""])->render();
                            $modelTable['Garantía (Opcional)']          = htmlentities($provider->guarantee).view('components.inputs.input-text', ['attributeEx' => "readonly=\"readonly\" type=\"hidden\" name=\"guarantee_".$provider->id."\" value=\"".htmlentities($provider->guarantee)."\""])->render();
                            if ($request->requisition->requisition_type == 1 || $request->requisition->requisition_type == 5)
                            {
                                $modelTable['Partes de Repuesto (Opcional)'] = htmlentities($provider->spare).view('components.inputs.input-text', ['attributeEx' => "readonly=\"readonly\" type=\"hidden\" name=\"spare_".$provider->id."\" value=\"".htmlentities($provider->spare)."\""])->render();
                            }
                            $modelTable['Comentarios (Opcional)'] = htmlentities($provider->commentaries).view('components.inputs.input-text', ['attributeEx' => "readonly=\"readonly\" type=\"hidden\" name=\"commentaries_provider_".$provider->id."\" value=\"".htmlentities($provider->commentaries)."\""])->render();
                            $modelGroup[]	=	
                            [
                                "name"			=> $provider->providerData->businessName,
                                "id"			=> 'providers',
                                "colNumber"		=> 6,
                                "footer"		=> [["kind" => "components.templates.outputs.table-detail-single", "modelTable" => $modelTable]],
                                "content"		=> $headersProvider
                            ];
                            if (count($usersVoting) > 0)
                            {
                                foreach ($usersVoting as $user)
                                {
                                    $modelHead[] = ['value' => $user->fullName()];
                                }
                                $modelGroup[]	=
                                [
                                    "name"			=> "Votaciones",
                                    "id"			=> 'voting',
                                    "colNumber"		=> count($usersVoting)
                                ];
                            }
                            
                        }
                    }
                
                    if ($request->requisition->details()->exists())
                    {
                        foreach ($request->requisition->details as $key => $detail)
                        {
                            switch ($request->requisition->requisition_type)
                            {
                                case 1:
                                    $body = 
                                    [
                                        [
                                            'content' => 
                                            [
                                                [
                                                    'label' => $detail->name,
                                                ],
                                            ],
                                        ],
                                        [
                                            'content' => 
                                            [
                                                [
                                                    'label' => htmlentities($detail->description),
                                                ],
                                            ],
                                        ],
                                        [
                                            'content' => 
                                            [
                                                [
                                                    'label' => $detail->exists_warehouse,
                                                ],
                                            ],
                                        ],
                                        [
                                            'content' => 
                                            [
                                                [
                                                    'kind'          => 'components.inputs.input-text',
                                                    'attributeEx'   => "type=\"hidden\" name=\"idRequisitionDetail[]\" value=\"".$detail->id."\"",
                                                    'classEx'       => 't_id',
                                                ],
                                                [
                                                    'kind'          => 'components.inputs.input-text',
                                                    'attributeEx'   => "type=\"hidden\" value=\"".$detail->category."\"",
                                                    'classEx'       => 't_category',
                                                ],
                                                [
                                                    'label' => $detail->categoryData()->exists() ? $detail->categoryData->description : '',
                                                ],
                                            ],
                                        ],
                                        [
                                            'content' => 
                                            [
                                                [
                                                    'kind'        => 'components.inputs.input-text',
                                                    'attributeEx' => "type=\"hidden\" value=\"".$detail->cat_procurement_material_id."\"",
                                                    'classEx'     => 't_type',
                                                ],
                                                [
                                                    'label' => $detail->procurementMaterialType()->exists() ? $detail->procurementMaterialType->name : '',
                                                ],
                                            ],
                                        ],
                                        [
                                            'content' => 
                                            [
                                                [
                                                    'kind'          => 'components.inputs.input-text',
                                                    'attributeEx'   => "type=\"hidden\" value=\"".$detail->quantity."\"",
                                                    'classEx'       => 't_quantity',
                                                ],
                                                [
                                                    'label' => $detail->quantity,
                                                ],
                                            ],
                                        ],
                                        [
                                            'content' => 
                                            [
                                                [
                                                    'label' => htmlentities($detail->measurement),
                                                ],
                                            ],
                                        ],
                                        [
                                            'content' => 
                                            [
                                                [
                                                    'label' => $detail->unit,
                                                ],
                                            ],
                                        ]
                                    ];
                                    break;
                                case 2:
                                    $body = 
                                    [
                                       
                                        [
                                            'content' => 
                                            [
                                                [
                                                    'label' => $detail->name,
                                                ],
                                            ],
                                        ],
                                        [
                                            'content' => 
                                            [
                                                [
                                                    'label' => htmlentities($detail->description),
                                                ],
                                            ],
                                        ],
                                        [
                                            'content' => 
                                            [
                                                [
                                                    'label' => $detail->period,
                                                ],
                                            ],
                                        ],
                                        [
                                            'content' => 
                                            [
                                                [
                                                    'kind' => 'components.inputs.input-text',
                                                    'attributeEx' => "type=\"hidden\" name=\"idRequisitionDetail[]\" value=\"".$detail->id."\"",
                                                    'classEx' => 't_id',
                                                ],
                                                [
                                                    'kind' => 'components.inputs.input-text',
                                                    'attributeEx' => "type=\"hidden\" value=\"".$detail->category."\"",
                                                    'classEx' => 't_category',
                                                ],
                                                [
                                                    'label' => $detail->categoryData()->exists() ? $detail->categoryData->description : '',
                                                ],
                                            ],
                                        ],
                                        [
                                            'content' => 
                                            [
                                                [
                                                    'kind' => 'components.inputs.input-text',
                                                    'attributeEx' => "type=\"hidden\" value=\"".$detail->quantity."\"",
                                                    'classEx' => 't_quantity',
                                                ],
                                                [
                                                    'label' => $detail->quantity,
                                                ],
                                            ],
                                        ],
                                        [
                                            'content' => 
                                            [
                                                [
                                                    'label' => $detail->unit,
                                                ],
                                            ],
                                        ],
                                    ];
                                    break;
                                case 4:
                                    $body = 
                                    [
                                       
                                        [
                                            'content' => 
                                            [
                                                [
                                                    'label' => $detail->name,
                                                ],
                                            ],
                                        ],
                                        [
                                            'content' => 
                                            [
                                                [
                                                    'label' => htmlentities($detail->description),
                                                ],
                                            ],
                                        ],
                                        [
                                            'content' => 
                                            [
                                                [
                                                    'kind' => 'components.inputs.input-text',
                                                    'attributeEx' => "type=\"hidden\" value=\"".$detail->quantity."\"",
                                                    'classEx' => 't_quantity',
                                                ],
                                                [
                                                    'label' => $detail->quantity,
                                                ],
                                            ],
                                        ],
                                        [
                                            'content' => 
                                            [
                                                [
                                                    'label' => $detail->unit,
                                                ],
                                            ],
                                        ],
                                    ];
                                    break;
                                case 5:
                                    $body = 
                                    [
                                        [
                                            'content' => 
                                            [
                                                [
                                                    'label' => $detail->name,
                                                ],
                                            ],
                                        ],
                                        [
                                            'content' => 
                                            [
                                                [
                                                    'label' => htmlentities($detail->description),
                                                ],
                                            ],
                                        ],
                                        [
                                            'content' => 
                                            [
                                                [
                                                    'label' => $detail->brand,
                                                ],
                                            ],
                                        ],
                                        [
                                            'content' => 
                                            [
                                                [
                                                    'label' => $detail->model,
                                                ],
                                            ],
                                        ],
                                        [
                                            'content' => 
                                            [
                                                [
                                                    'label' => $detail->usage_time,
                                                ],
                                            ],
                                        ],
                                        [
                                            'content' => 
                                            [
                                                [
                                                    'label' => $detail->exists_warehouse,
                                                ],
                                            ],
                                        ],
                                        [
                                            'content' => 
                                            [
                                                [
                                                    'kind' => 'components.inputs.input-text',
                                                    'attributeEx' => "type=\"hidden\" name=\"idRequisitionDetail[]\" value=\"".$detail->id."\"",
                                                    'classEx' => 't_id',
                                                ],
                                                [
                                                    'kind' => 'components.inputs.input-text',
                                                    'attributeEx' => "type=\"hidden\" value=\"".$detail->category."\"",
                                                    'classEx' => 't_category',
                                                ],
                                                [
                                                    'label' => $detail->categoryData()->exists() ? $detail->categoryData->description : '',
                                                ],
                                            ],
                                        ],
                                        [
                                            'content' => 
                                            [
                                                [
                                                    'kind' => 'components.inputs.input-text',
                                                    'attributeEx' => "type=\"hidden\" value=\"".$detail->quantity."\"",
                                                    'classEx' => 't_quantity',
                                                ],
                                                [
                                                    'label' => $detail->quantity,
                                                ],
                                            ],
                                        ],
                                        [
                                            'content' => 
                                            [
                                                [
                                                    'label' => htmlentities($detail->measurement),
                                                ],
                                            ],
                                        ],
                                        [
                                            'content' => 
                                            [
                                                [
                                                    'label' => $detail->unit,
                                                ],
                                            ],
                                        ],
                                    ];
                                    break;
                                case 6:
                                    $body = 
                                    [
                                        [
                                            'content' => 
                                            [
                                                [
                                                    'label' => $detail->name,
                                                ],
                                            ],
                                        ],
                                        [
                                            'content' => 
                                            [
                                                [
                                                    'label' => htmlentities($detail->description),
                                                ],
                                            ],
                                        ],
                                        [
                                            'content' => [
                                                [
                                                    'kind' => 'components.inputs.input-text',
                                                    'attributeEx' => "type=\"hidden\" value=\"".$detail->quantity."\"",
                                                    'classEx' => 't_quantity',
                                                ],
                                                [
                                                    'label' => $detail->quantity,
                                                ],
                                            ],
                                        ],
                                        [
                                            'content' => [
                                                [
                                                    'label' => $detail->unit,
                                                ],
                                            ],
                                        ],
                                    ];
                                    break;
                            }
                            if(in_array($request->status,[3,4,5,17,27]))
                            {
                                array_splice($body, count(array_column($modelHead,'show')), 0, [["content" => [["label" => $detail->part]]]]);
                            }
                            if ($request->requisition->requisitionHasProvider()->exists())
                            {
                                foreach ($request->requisition->requisitionHasProvider as $provider) 
                                {
                                    $price = App\ProviderSecondaryPrice::where('idRequisitionDetail', $detail->id)
                                        ->where('idRequisitionHasProvider', $provider->id)
                                        ->first();
                                    $taxesData = [];
                                    if ($price != '' && $price->taxesData()->exists()) 
                                    {
                                        foreach ($price->taxesData as $tax) 
                                        {
                                            array_push($taxesData, [
                                                'id' => $tax->id,
                                                'name' => $tax->name,
                                                'amount' => $tax->amount,
                                            ]);
                                        }
                                    }
                                    $retentionsData = [];
                                    if ($price != '' && $price->retentionsData()->exists()) 
                                    {
                                        foreach ($price->retentionsData as $retention) 
                                        {
                                            array_push($retentionsData, [
                                                'id' => $retention->id,
                                                'name' => $retention->name,
                                                'amount' => $retention->amount,
                                            ]);
                                        }
                                    }
                                    $priceId = $price != '' ? $price->id : 'x';
                                    $body[] = 
                                    [
                                        'content' => 
                                        [
                                            [
                                                'kind' => 'components.inputs.input-text',
                                                'attributeEx' => "type=\"hidden\" name=\"idProviderSecondaryPrice_".$detail->id.'_'.$provider->id."\" value=\"".$priceId."\"",
                                            ],
                                            [
                                                'kind' => 'components.labels.label',
                                                'label' => $price->unitPrice != '' ? '$ '.number_format($price->unitPrice,2) : '$0.00',
                                            ],
                                        ]
                                    ];
                                    $body[] = 
                                    [
                                        'content' => 
                                        [
                                            [
                                                'kind' => 'components.labels.label',
                                                'label' => $price->subtotal != '' ? '$ '.number_format($price->subtotal,2) : '$0.00'
                                            ]
                                        ],
                                    ];
                                    $body[] = 
                                    [
                                        'content' => 
                                        [
                                            [
                                                'kind' => 'components.labels.label',
                                                'label' => $price->iva != '' ? '$ '.number_format($price->iva,2) : '$0.00'
                                            ]
                                        ],
                                    ];
                                    $body[] = 
                                    [
                                        'content' => 
                                        [
                                            [
                                                'kind' => 'components.labels.label',
                                                'label' => $price->taxes != '' ? '$ '.number_format($price->taxes,2) : '$0.00'
                                            ]
                                        ],
                                    ];
                                    $body[] = 
                                    [
                                        'content' => [
                                            [
                                                'kind' => 'components.labels.label',
                                                'label' => $price->retentions != '' ? '$ '.number_format($price->retentions,2) : '$0.00'
                                            ]
                                        ],
                                    ];
                                    $body[] = 
                                    [
                                        'content' => [
                                                [
                                                'kind' => 'components.labels.label',
                                                'label' => $price->total != '' ? '$ '.number_format($price->total,2) : '$0.00'
                                            ]
                                        ],
                                    ];
                                    if (count($usersVoting) > 0) 
                                    {
                                        foreach ($usersVoting as $user) 
                                        {
                                            if ($detail->votingProvider()->exists()) 
                                            {
                                                if ($user->user_id != Auth::user()->id) 
                                                {
                                                    if ($detail->votingProvider()->where('user_id',$user->user_id)->exists()) 
                                                    {
                                                        foreach ($detail->votingProvider->where('user_id',$user->user_id) as $votingUser) 
                                                        {
                                                            if ($provider->id == $votingUser->idRequisitionHasProvider) 
                                                            {
                                                                $body[] = 
                                                                [
                                                                    'content' => 
                                                                    [
                                                                        [
                                                                            'kind'          => 'components.inputs.input-text',
                                                                            'attributeEx'   => "type=\"hidden\" value=\"".$votingUser->commentaries."\"",
                                                                            'classEx'       => 'view-comment',
                                                                        ],
                                                                        [
                                                                            'kind'          => 'components.buttons.button',
                                                                            'attributeEx'   => "data-toggle=\"modal\" type=\"button\" data-target=\"#viewComment\"",
                                                                            'classEx'       => 'btnCommentView',
                                                                            'variant'       => 'secondary',
                                                                            'label'         => "<span class=\"icon-search\"></span>",
                                                                        ],
                                                                        [
                                                                            'kind'          => 'components.labels.label',
                                                                            'attributeEx'   => "style=\"color: rgb(17, 179, 81);font-size: 23px;\"",
                                                                            'classEx'       => 'request-validate',
                                                                            'label'         => "<span class=\"icon-check\"></span>",
                                                                        ],
                                                                    ],
                                                                ];
                                                            } 
                                                            else 
                                                            {
                                                                $body[] = 
                                                                [
                                                                    'content' => 
                                                                    [
                                                                        [
                                                                            'label' => '---',
                                                                        ],
                                                                    ],
                                                                ];
                                                            }
                                                        }
                                                    } 
                                                    else 
                                                    {
                                                        $body[] = 
                                                        [
                                                            'content' => 
                                                            [
                                                                [
                                                                    'label' => '---',
                                                                ],
                                                            ],
                                                        ];
                                                    }
                                                }
                                                else 
                                                {
                                                    if ($detail->votingProvider()->where('user_id',Auth::user()->id)->exists()) 
                                                    {
                                                        foreach ($detail->votingProvider->where('user_id',Auth::user()->id) as $votingUser) 
                                                        {
                                                            $commentariesInput = isset($provider->id) && $provider->id == $votingUser->idRequisitionHasProvider ? view('components.inputs.input-text', ['attributeEx' => "type=\"hidden\" value=\"".$votingUser->commentaries."\" name=\"commentaries_".$detail->id."\"", "classEx" => "edit-comment"])->render() : "";
                                                            $body[] = 
                                                            [
                                                                'content' => 
                                                                [
                                                                    [
                                                                        'kind'        => 'components.buttons.button',
                                                                        'attributeEx' => $provider->id != $votingUser->idRequisitionHasProvider ? "type=\"button\" data-toggle=\"modal\" data-target=\"#newComment\" data-provider=\"".$provider->id."\" data-detail=\"".$detail->id."\" title=\"Agregar Comentario\"" : "type=\"button\" data-toggle=\"modal\" data-target=\"#newComment\" data-provider=\"".$provider->id."\" data-detail=\"".$detail->id."\" title=\"Editar Comentario\"",
                                                                        'classEx'     => $provider->id != $votingUser->idRequisitionHasProvider ? 'modalComment hidden' : 'modalComment ',
                                                                        'variant'     => 'success',
                                                                        'label'       => "<span class=\"icon-pencil\"></span>"
                                                                    ],
                                                                    [
                                                                        'label' => "<span class=\"span_commentaries\" data-provider=\"".$provider->id."\" data-detail=\"".$detail->id."\">".$commentariesInput."</span>"
                                                                    ],
                                                                    [
                                                                        "kind"             => "components.inputs.checkbox",
                                                                        "label"            => "<span class=\"icon-check\"></span>", 
                                                                        "radio"			   => "true",
                                                                        "attributeEx"      => isset($provider->id) && $provider->id == $votingUser->idRequisitionHasProvider ? "id=\"id_".$detail->id."_".$provider->id."\" name=\"voting_".$detail->id."\" value=\"".$provider->id."\" checked=\"checked\"" : "id=\"id_".$detail->id."_".$provider->id."\" name=\"voting_".$detail->id."\" value=\"".$provider->id."\"",
                                                                        "classExContainer" => "inline-flex",
                                                                        "classEx"		   => "validate-vote checkbox",
                                                                        "classExLabel"	   => "request-validate"
                                                                    ]
                                                                ],
                                                            ];
                                                        }
                                                    } 
                                                    else 
                                                    {
                                                        $body[] = 
                                                        [
                                                            'content' => 
                                                            [
                                                                [
                                                                    'kind'        => 'components.buttons.button',
                                                                    'attributeEx' => "type=\"button\" title=\"Agregar Comentario\" data-toggle=\"modal\" data-target=\"#newComment\" data-provider=\"".$provider->id."\" data-detail=\"".$detail->id."\"",
                                                                    'classEx'     => 'modalComment hidden',
                                                                    'variant'     => 'success',
                                                                    'label'       => "<span class=\"icon-pencil\"></span>"
                                                                ],
                                                                [
                                                                    'label' => "<span class=\"span_commentaries\" data-provider=\"".$provider->id."\" data-detail=\"".$detail->id."\"></span>"
                                                                ],
                                                                [
                                                                    "kind"          => "components.inputs.checkbox",
                                                                    'radio'         => 'true',
                                                                    'attributeEx'   => "id=\"id_".$detail->id."_".$provider->id."\" name=\"voting_".$detail->id."\" value=\"".$provider->id."\"",
                                                                    'classEx'       => 'validate-vote checkbox',
                                                                    'label'         => "<span class=\"icon-check\"></span>",
                                                                ],
                                                            ],
                                                        ];
                                                    }
                                                }
                                            }
                                            else 
                                            {
                                                if ($user->user_id != Auth::user()->id) 
                                                {
                                                    $body[] = 
                                                    [
                                                        'content' => 
                                                        [
                                                            [
                                                                'label' => '---',
                                                            ],
                                                        ],
                                                    ];
                                                } 
                                                else 
                                                {
                                                    $body[] = 
                                                    [
                                                        'content' => 
                                                        [
                                                            [
                                                                'kind'        => 'components.buttons.button',
                                                                'attributeEx' => "type=\"button\" title=\"Agregar Comentario\" data-toggle=\"modal\" data-target=\"#newComment\" name=\"commentaries_".$detail->id."\" data-provider=\"".$provider->id."\" data-detail=\"".$detail->id."\"",
                                                                'classEx'     => 'modalComment hidden',
                                                                'variant'     => 'success',
                                                                'label'       => "<span class=\"icon-pencil\"></span>"
                                                            ],
                                                            [
                                                                'label' => "<span class=\"span_commentaries\" data-provider=\"".$provider->id."\" data-detail=\"".$detail->id."\"> </span>"
                                                            ],
                                                            [
                                                                "kind"             => "components.inputs.checkbox",
                                                                "label"            => "<span class=\"icon-check\"></span>", 
                                                                "radio"			   => "true",
                                                                'attributeEx'      => "id=\"id_".$detail->id."_".$provider->id."\" name=\"voting_".$detail->id."\" value=\"".$provider->id."\"",
                                                                "classExContainer" => "inline-flex",
                                                                "classEx"		   => "validate-vote checkbox",
                                                                "classExLabel"	   => "request-validate"
                                                            ]
                                                        ],
                                                    ];   
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                            $modelBody[] = $body;
                        }
                    }
                }
            @endphp
            @component('components.tables.table-provider',
                [
                    'modelHead'       => $modelHead,
                    'modelBody'       => $modelBody,
                    'modelGroup'       => $modelGroup,
                    "attributeExBody" => "id=\"body_art\"",
                    "classExBody"     => "body_art"
                ])
            @endcomponent
        @else
            @component('components.labels.title-divisor')
                DATOS DE LA VACANTE
                @slot('classEx')
                    pb-4
                @endslot
            @endcomponent
            <div class="employee-details">
                <div class="flex justify-center px-6">
                    <div class="justify-center">
                        @component('components.tables.table-request-detail.container', ['variant' => 'simple'])
                            @php
                                $modelTable = [];
                                $modelTable['Jefe inmediato']                       = $request->requisition->staff->boss->fullName();
                                $modelTable['Horario']                              = $request->requisition->staff->staff_schedule_start.'  -  '.$request->requisition->staff->staff_schedule_end;
                                $modelTable['Rango de sueldo']                      = "$ ".number_format($request->requisition->staff->staff_min_salary, 2)." - $ ".number_format($request->requisition->staff->staff_max_salary, 2);
                                $modelTable['Motivo']                               = $request->requisition->staff->staff_reason;
                                $modelTable['Puesto']                               = $request->requisition->staff->staff_position;
                                $modelTable['Periodicidad']                         = $request->requisition->staff->staff_periodicity;
                                $modelTable['Descripción general de la vacante']    = $request->requisition->staff->staff_s_description;
                                $modelTable['Habilidades requeridas']               = $request->requisition->staff->staff_habilities;
                                $modelTable['Experiencia deseada']                  = $request->requisition->staff->staff_experience;
                                
                                $responsabilities = '';
                                foreach ($request->requisition->staffResponsabilities as $responsibilityStaff) {
                                    $responsabilities = $responsabilities . $responsibilityStaff->dataResponsibilities->responsibility . ', ';
                                }
                                $modelTable['Responsabilidades'] = $responsabilities;
                            @endphp
                            @component('components.templates.outputs.table-detail-single', ['modelTable' => $modelTable])
                            @endcomponent
                        @endcomponent
                    </div>
                </div>
                <div class="w-full mx-3">
                    @php
                        $body = [];
                        $modelBody = [];
                        $modelHead = ['Función', 'Descripción'];
                        foreach ($request->requisition->staffFunctions as $function)
                        {
                            $body = [
                                'classEx' => 'tr',
                                [
                                    'content' => [
                                        [
                                            'label' => $function->function,
                                        ],
                                    ],
                                ],
                                [
                                    'content' => [
                                        [
                                            'label' => $function->description,
                                        ],
                                    ],
                                ],
                            ];
                            array_push($modelBody, $body);
                        }
                    @endphp
                    @component('components.tables.alwaysVisibleTable',
                        [
                            'modelHead' => $modelHead,
                            'modelBody' => $modelBody,
                            'title' => 'Funciones',
                        ])
                        @slot('classEx')
                            text-center employee-details
                        @endslot
                    @endcomponent
                </div>
                <div class="w-full mx-3">
                    @php
                        $body = [];
                        $modelBody = [];
                        $modelHead = ['Deseables', 'Descripción'];
                        foreach ($request->requisition->staffDesirables as $desirable)
                        {
                            $body = [
                                'classEx' => 'tr',
                                [
                                    'content' => [
                                        [
                                            'label' => $desirable->desirable,
                                        ],
                                    ],
                                ],
                                [
                                    'content' => [
                                        [
                                            'label' => $desirable->description,
                                        ],
                                    ],
                                ],
                            ];
                            array_push($modelBody, $body);
                        }
                    @endphp
                    @component('components.tables.alwaysVisibleTable',
                        [
                            'modelHead' => $modelHead,
                            'modelBody' => $modelBody,
                            'title' => 'Deseables',
                        ])
                        @slot('classEx')
                            text-center employee-details
                        @endslot
                    @endcomponent
                </div>
            </div>
        @endif
        @if ($request->requisition->documents()->exists())
            @component('components.labels.title-divisor')
                DOCUMENTOS DE LA REQUISICIÓN
            @endcomponent
            <div class="mx-3">
                @php
                    $body = [];
                    $modelBody = [];
                    $modelHead = ['Tipo de documento','Folio Fiscal','Archivo', 'Modificado Por', 'Fecha'];
                    foreach ($request->requisition->documents->sortByDesc('created') as $doc)
                    {
                        $body = [
                            'classEx' => 'tr',
                            [
                                'content' => [
                                    'label' => $doc->name,
                                ],
                            ],
                            [
                                'content' => [
                                    'label' => htmlentities($doc->fiscal_folio)
                                ],
                            ],
                            [
                                'content' => [
                                    [
                                        'kind'          => 'components.buttons.button',
                                        'buttonElement' => 'a',
                                        'variant'       => 'secondary',
                                        'attributeEx'   => "target=\"_blank\" href=\"".url('docs/requisition/'.$doc->path)."\"",
                                        'label'         => "Archivo",
                                    ],
                                ],
                            ],
                            [
                                'content' => [
                                    'label' => $doc->user->fullName(),
                                ],
                            ],
                            [
                                'content' => [
                                    'label' =>  Carbon\Carbon::createFromFormat('Y-m-d H:s:i',$doc->created)->format('d-m-Y')
                                ],
                            ],
                        ];
                        array_push($modelBody, $body);
                    }
                @endphp
                @component('components.tables.alwaysVisibleTable',
                    [
                        'modelHead' => $modelHead,
                        'modelBody' => $modelBody
                    ])
                    @slot('attributeEx')
                        id="table"
                    @endslot
                    @slot('attributeExBody')
                        id="body"
                    @endslot
                    @slot('classExBody')
                        request-validate
                    @endslot
                @endcomponent
            </div>
        @endif
        <span id="spanDelete"></span>
        <div id="comment" class="mt-4">
            @component('components.labels.label') Comentarios (opcional): @endcomponent
            @component('components.inputs.text-area')
                @isset($classExComment)
                    @slot('classEx')
                        text-area w-full
                    @endslot
                @endisset
                @slot('attributeEx')
                    cols="90" rows="10" name="revisionComment"
                @endslot
                {{ $request->authorizeComment }}
            @endcomponent
        </div>
        <div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center my-4 mb-6">
            @component('components.buttons.button', ['variant' => 'primary'])
                @slot('attributeEx')
                    type="submit" name="send"
                @endslot
                @slot('classEx')
                    w-48 md:w-auto
                @endslot
                APROBAR REQUISICIÓN
            @endcomponent
            @component('components.buttons.button', ['variant' => 'secondary'])
                @slot('attributeEx')
                    type="submit" name="btnSave" id="save" formaction="{{ route('requisition.vote.save', $request->folio) }}"
                @endslot
                @slot('classEx')
                    w-48 md:w-auto save
                @endslot
                GUARDAR CAMBIOS
            @endcomponent
            @component('components.buttons.button', ['variant' => 'red'])
                @slot('attributeEx')
                    type="submit"
                    id="reject"
                    name="btnReject"
                    formaction="{{ route('requisition.vote.reject', $request->folio) }}"
                @endslot
                @slot('classEx')
                    w-48 md:w-auto text-center reject
                @endslot
                RECHAZAR REQUISICIÓN
            @endcomponent
        </div>
        @component('components.modals.modal', ["variant" => "large"])
            @slot('id')
                viewDocumentProvider
            @endslot
            @slot('classExBody')
                modal-view-document
            @endslot
            @slot('modalFooter')
                <div class="text-center">
                    @component('components.buttons.button', ["variant" => "red"])
                        @slot('attributeEx')
                            type="button"
                            data-dismiss="modal"
                            closeViewDocument
                        @endslot
                        @slot('classEx')
                            closeViewDocument
                        @endslot
                        <span class="icon-x"></span> Cerrar
                    @endcomponent
                </div>
            @endslot
        @endcomponent
        @component('components.modals.modal', ["variant" => "large"])
            @slot('id')
                newComment
            @endslot
            @slot('attributeEx')
                tabindex="-1"
            @endslot
            @slot('modalBody')
                <div id="comment" class="px-4">
                    @component('components.labels.label') Comentario de votación: @endcomponent
                    @component('components.inputs.text-area')
                        @slot('attributeEx')
                            cols="90" rows="10" name="comment"
                        @endslot
                    @endcomponent
                    @component('components.inputs.input-text')
                        @slot('attributeEx')
                            type="hidden" name="id_detail"
                        @endslot
                    @endcomponent
                    @component('components.inputs.input-text')
                        @slot('attributeEx')
                            type="hidden" name="id_provider"
                        @endslot
                    @endcomponent
                </div>
            @endslot
            @slot('modalFooter')
                <div class="text-center">
                    @component('components.buttons.button', ["variant" => "warning"])
                        @slot('attributeEx')
                            type="button" 
                            name="btnAddCommentaries"
                        @endslot
                        <span class="icon-check"></span> Agregar comentario
                    @endcomponent
                    @component('components.buttons.button', ["variant" => "red"])
                        @slot('attributeEx')
                            type="button"
                            data-dismiss="modal"
                        @endslot
                        <span class="icon-x"></span> Cerrar
                    @endcomponent
                </div>
            @endslot
        @endcomponent
        @component('components.modals.modal', ["variant" => "large"])
            @slot('id')
                viewComment
            @endslot
            @slot('attributeEx')
                tabindex="-1"
            @endslot
            @slot('modalBody')
                <div id="comment" class="px-4">
                    @component('components.labels.label') Comentario de votación @endcomponent
                    @component('components.inputs.text-area')
                        @slot('attributeEx')
                            cols="90" rows="10" name="commentView" readonly="readonly"
                        @endslot
                    @endcomponent
                </div>
            @endslot
            @slot('modalFooter')
                <div class="text-center">
                    @component('components.buttons.button', ["variant" => "red"])
                        @slot('attributeEx')
                            type="button"
                            data-dismiss="modal"
                        @endslot
                        <span class="icon-x"></span> Cerrar
                    @endcomponent
                </div>
            @endslot
        @endcomponent
        @component('components.inputs.input-text')
            @slot('attributeEx')
                type="hidden" name="data_validate" value="1"
            @endslot
        @endcomponent
    @endcomponent
@endsection

@section('scripts')
    <link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
    <script src="{{ asset('js/jquery-ui.js') }}"></script>
    <script src="{{ asset('js/jquery.numeric.js') }}"></script>
    <script src="{{ asset('js/datepicker.js') }}"></script>
    <script src="{{ asset('js/papaparse.min.js') }}"></script>
    <script type="text/javascript">
        function validation()
        {
            $.validate({
                form    : '#container-alta',
                modules : 'security',
                onError : function($form) 
                {
                    swal('', '{{ Lang::get("messages.form_error") }}', 'error');
                },
                onSuccess: function($form) 
                {
                    needFileName = false
                    $('input[name="realPathRequisition[]').each(function() 
                    {
                        if ($(this).val() != "") 
                        {
                            select  = $(this).parents('div').find('.nameDocumentRequisition')
                            name    = select.find('option:selected').val();
                            if (name == 0)
                            {
                                needFileName = true;
                            }
                        }
                    });
                    if (needFileName) 
                    {
                        swal('', 'Debe seleccionar el tipo de documento', 'error');
                        return false;
                    }
                    dataValidate = $('[name="data_validate"]').val();
                    if (dataValidate == 1) 
                    {
                        if ($('.request-validate').length > 0) 
                        {
                            conceptos = $('#body_art .tr').length;
                            providers = $('.provider_exists_requisition').length;

                            if (providers == 0) 
                            {
                                swal('', 'Debe agregar al menos un proveedor', 'error');
                                return false;
                            }
                            if (conceptos > 0) 
                            {
                                flag = true;
                                $('#body_art .tr').each(function(i, v) 
                                {
                                    if ($(this).find('.validate-vote:checked').length == 0) 
                                    {
                                        flag = false;
                                    }
                                });
                                if (flag) 
                                {
                                    swal("Cargando", 
                                    {
                                        icon: '{{ asset(getenv('LOADING_IMG')) }}',
                                        button: true,
                                        closeOnClickOutside: false,
                                        closeOnEsc: false
                                    });
                                    return true;
                                } 
                                else 
                                {
                                    swal('', 'Falta emitir algunos votos', 'error');
                                    return false;
                                }
                            } 
                            else 
                            {
                                swal('', 'Debe ingresar al menos un concepto de pedido', 'error');
                                return false;
                            }
                        } 
                        else 
                        {
                            swal("Cargando", 
                            {
                                icon: '{{ asset(getenv('LOADING_IMG')) }}',
                                button: true,
                                closeOnClickOutside: false,
                                closeOnEsc: false
                            });
                            return true;
                        }
                    } 
                    else 
                    {
                        swal("Cargando", 
                        {
                            icon: '{{ asset(getenv('LOADING_IMG')) }}',
                            button: true,
                            closeOnClickOutside: false,
                            closeOnEsc: false,
                            timer: 1500,
                        });
                        return true;
                    }
                }
            });
        }

        $(document).ready(function()
        {
            validation();
            zipCode();
            $(".datepicker").datepicker({ dateFormat: "yy-mm-dd" });
            $('.t_unitPrice,.t_subtotal,.t_total,.clabe,.account').numeric({ altDecimal: ".", decimalPlaces: 2, negative: false });
            @php
                $selects = collect(
                [
                    [
                        "identificator"          => "[name=\"state_idstate\"],.js-bank", 
                        "placeholder"            => "Seleccione uno", 
                        "maximumSelectionLength" => "1"
                    ]
                ]);
            @endphp
            @component("components.scripts.selects",["selects" => $selects]) @endcomponent
            $(document).on('click','#upload_file,#save,[name="export_excel"],[name="btnAddProviderDocuments"],[name="btnReject"]', function()
            {
                $('.remove').removeAttr('data-validation');
                $('.removeselect').removeAttr('required');
                $('.removeselect').removeAttr('data-validation');
                $('.request-validate').removeClass('request-validate');
                $('.validate-vote').removeClass('validate-vote');
                $('.provider_exists_requisition').removeClass('provider_exists_requisition');
                $('[name="data_validate"]').val('0');
            })
            .on('click', '[name="btnAddProviderDocuments"]', function(e)
            {
                e.preventDefault()
                action  = $(this).attr('formaction');
                form    = $('form').attr('action', action);
                needFileName = false;
                $('[name="realPath[]"]').each(function()
                {
                    if ($(this).val() != "")
                    {
                        select  = $(this).siblings('.components-ex-up').find('.nameDocument');
                        name    = select.find('option:selected').val()
                        if (name == 0)
                        {
                            needFileName = true;
                            select.addClass('error')
                        }
                    }
                });
                if (!needFileName)
                {
                    form.submit();
                }
                else
                {
                    swal('', 'Debe seleccionar el tipo de documento', 'error');
                }
            })
            .on('click', '[data-toggle="modal"]', function() 
            {
                @php
                    $selects = collect(
                    [
                        [
                            "identificator"          => "[name=\"state_idstate\"],.js-bank", 
                            "placeholder"            => "Seleccione uno", 
                            "maximumSelectionLength" => "1"
                        ]
                    ]);
                @endphp
                @component("components.scripts.selects",["selects" => $selects]) @endcomponent
            })
            .on('change', 'input[name="status"]', function()
            {
                $("#comment").show();
            })
            .on('click','[name="btnSave"],[name="btnAddProvider"],[name="btnDeleteProvider"],[name="idProviderBtn"],[name="addMultiProvider"],[name="export_excel"],[name="btnAddProviderDocuments"]',function()
            {
                $('.remove').removeAttr('data-validation');
                $('.removeselect').removeAttr('required');
                $('.removeselect').removeAttr('data-validation');
                $('.request-validate').removeClass('request-validate');
                $('.validate-vote').removeClass('validate-vote');
                $('.provider_exists_requisition').removeClass('provider_exists_requisition');
                $('[name="data_validate"]').val('0');
            })
            .on('change', 'input[name="prov"]', function()
            {
                if ($('input[name="prov"]:checked').val() == "nuevo")
                {
                    $(".form-add-provider").fadeIn();
                    $(".form-search-provider").fadeOut();
                    $('[name="state_idstate"],.js-bank').select2({
                        language: "es",
                        maximumSelectionLength: 1,
                        placeholder: "Seleccione uno"
                    })
                    .on("change", function(e)
                    {
                        if ($(this).val().length > 1)
                        {
                            $(this).val($(this).val().slice(0, 1)).trigger('change');
                        }
                    });
                    zipCode();
                }
                else if ($('input[name="prov"]:checked').val() == "buscar")
                {
                    $(".form-search-provider").fadeIn();
                    $(".form-add-provider").fadeOut();
                }
            })
            .on('click', '.checkbox', function()
            {
                $(this).parents('.tr').find('.span_commentaries').empty();
                $(this).parents('.tr').find('.modalComment').hide();
                $(this).parents('.voting').find('.modalComment').show();
            })
            .on('click', '.btnCommentView', function()
            {
                comment = $(this).parent('.voting').find('.view-comment').val();
                $('[name="commentView"]').val(comment);
            })
            .on('click', '.modalComment', function()
            {
                $('.span_commentaries').removeAttr('data-temp');
                id_detail = $(this).attr('data-detail');
                $('[name="id_detail"]').val(id_detail);

                id_provider = $(this).attr('data-provider');
                $('[name="id_provider"]').val(id_provider);

                comment = $(this).parent('.voting').find('.edit-comment').val();
                $('[name="comment"]').val(comment);
                $(this).parent('.voting').find('.span_commentaries').attr('data-temp', '1');
            })
            .on('click', '[name="btnAddCommentaries"]', function()
            {
                $('[data-temp="1"]').empty();
                id_detail   = $('[name="id_detail"]').val();
                id_provider = $('[name="id_provider"]').val();
                comment     = $('[name="comment"]').val();
                input       = $('<input type="hidden" class="edit-comment" value="'+comment+'"name="commentaries_'+id_detail+'">');

                if(comment != null && comment != "" && comment != undefined)
                {
                    $('[data-temp="1"]').append(input);
                    $('[data-temp="1"]').removeAttr('data-temp');
                    $('#newComment').modal('hide');
                    swal('Comentario agregado', '', 'success');

                    $('[name="id_detail"]').val('');
                    $('[name="id_provider"]').val('');
                    $('[name="comment"]').val('');
                }
                else
                {
                    swal('No ha agregado comentarios', '', 'error');
                }
                
            })
            .on('click', '.closeViewDocument', function()
            {
                $('.modal-view-document').empty();
            })
            .on('click', '.viewDocumentProvider', function()
            {
                id = $(this).attr('data-id');
                $.ajax({
                    type    : 'get',
                    url     : '{{ route("requisition.provider-documents.view") }}',
                    data    : { 'id': id },
                    success: function(data)
                    {
                        $('.modal-view-document').html(data);
                    },
                    error: function(data)
                    {
                        swal('','Sucedió un error, por favor intente de nuevo.','error');
                        $('#viewDocumentProvider').hide();
                    }
                })
            })
        });

        function zipCode()
        {
            $('#cp').select2({
                    maximumSelectionLength: 1,
                    width: "80%",
                    placeholder: "Ingrese un código postal",
                    ajax: {
                        delay: 400,
                        url: '{{ route('requisition.catalogue.zip') }}',
                        dataType: 'json',
                        method: 'post',
                        data: function(params) {
                            s = {
                                search: params.term,
                            }
                            return s;
                        }
                    },
                    minimumInputLength: 3,
                    language: {
                        noResults: function() {
                            return "No hay resultados";
                        },
                        searching: function() {
                            return "Buscando...";
                        },
                        inputTooShort: function(args) {
                            return 'Por favor ingrese más de 3 caracteres';
                        }
                    }
                })
                .on("change", function(e) {
                    if ($(this).val().length > 1) {
                        $(this).val($(this).val().slice(0, 1)).trigger('change');
                    }
                });
        }
    </script>
@endsection
