@isset($new)
    <div class="col-span-1 grid grid-cols-5 gap-1 p-1">
        <input type="hidden" class="tax_ret_id" value="x">
        <div class="col-span-2">
            @component("components.labels.label",["slot" => "Nombre"])
            @endcomponent
            @component("components.inputs.input-text",
                [
                    "classEx"     => "remove-validation-concept t_name_add_".$name,
                    'attributeEx' => "placeholder=\"Ingrese el nombre\" "
                ]
            )
            @endcomponent
        </div> 
        <div class="col-span-2">
            @component("components.labels.label",["slot" => "Monto"])
            @endcomponent
            @component("components.inputs.input-text",
                [
                    "classEx"     => "remove-validation-concept t_amount_add_".$name,
                    'attributeEx' => " placeholder=\"$0.00\""
                ]
            )
            @endcomponent
        </div>
        <div class="col-span-1 flex items-center justify-center">
            @component("components.buttons.button",
                [
                    'label'       => "<span class=\"icon-cross\"> </span>",
                    "variant"     => "red",
                    "classEx"     => 'm-0 delete-'.$name.' col-span-1 '.($classExButton??''),
                    "attributeEx" => " type=\"button\""
                ]
            )
            @endcomponent
        </div>
    </div>
@else
    <div class="bg-warm-gray-100 grid @isset($classEx) {!!replaceClassEX($classEx, 'w-full', ['0' => ['w-']])!!} @endisset" @isset($attributeEx) {!! $attributeEx !!} @endisset>
        @isset($addedData)
            @foreach ($addedData as $data)
                <div class="col-span-1 grid grid-cols-5 gap-1 p-1 taxes-row-{{ $detailId }}-{{ $providerId }}">
                    <input type="hidden" class="tax_ret_id" value="{{$data["id"]}}" name="tax_id_{{ $detailId }}_{{ $providerId }}[]">
                    <div class="col-span-2">
                        @component("components.labels.label",["slot" => "Nombre"])
                        @endcomponent
                        @component("components.inputs.input-text",
                            [
                                "classEx"     => "remove-validation-concept t_name_add_".$name,
                                'attributeEx' => "name=\"name_add_".$name."_".$detailId."_".$providerId."[]\" placeholder=\"Ingrese el nombre\" data-provider=\"".$providerId."\" data-item=\"".$detailId."\" value=\"".$data["name"]."\""
                            ]
                        )
                        @endcomponent
                    </div> 
                    <div class="col-span-2">
                        @component("components.labels.label",["slot" => "Monto"])
                        @endcomponent
                        @component("components.inputs.input-text",
                            [
                                "classEx"     => "remove-validation-concept t_amount_add_".$name,
                                'attributeEx' => "name=\"amount_add_".$name."_".$detailId."_".$providerId."[]\" placeholder=\"$0.00\" data-provider=\"".$providerId."\" data-item=\"".$detailId."\" value=\"".$data["amout"]."\""
                            ]
                        )
                        @endcomponent
                    </div>
                    <div class="col-span-1 flex items-center justify-center"> 
                        @component("components.buttons.button",
                            [
                                'slot'    => "<span class=\"icon-cross\"> </span>",
                                "variant" => "red",
                                "classEx" => 'm-0 delete-'.$name.' col-span-1 '.($classExButton??''),
                                'attributeEx' => " type=\"button\" data-provider=\"".$providerId."\" data-item=\"".$detailId."\""
                            ]
                        )
                        @endcomponent
                    </div>
                </div>
            @endforeach
        @endisset
    </div>
    <div class="bg-warm-gray-100 grid grid-cols-5 gap-2 p-2 div-button w-full">
        <div class="col-span-5 flex justify-start">
            @component("components.buttons.button",["variant" => "warning"])
                @slot('classEx') add-{{$name}} @endslot
                @slot('attributeEx')type="button" data-provider="{{ $providerId }}" data-item="{{ $detailId }}" @endslot
                @if($name == "ret")
                    Nueva retención 
                @else 
                    Nuevo Impuesto
                @endif
            @endcomponent
        </div>
    </div>
@endisset