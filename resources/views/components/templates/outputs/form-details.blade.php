<div class="totales2 @isset($variantProvider) md:flex-nowrap @else md:flex @endisset justify-end @isset($classEx) {{$classEx}} @endisset mx-4 md:mx-0">
    @if(isset($textNotes))
        <div class="totales w-full @if(!isset($variantProvider)) md:w-1/2 @endif p-4 pl-0 md:pl-2">
            @if(isset($variantProvider))
                @component('components.labels.label')
                    Comentarios (opcional)
                @endcomponent 
            @endif  
            @component('components.inputs.text-area')
                @isset($classExComment) 
                    @slot('classEx')
                        {{$classExComment}} @isset($variantProvider) w-full @endisset
                    @endslot
                @endisset
                @slot('attributeEx')
                    @if(!isset($variantProvider)) cols="80" @endif placeholder="Nota" @isset($attributeExComment) {!!$attributeExComment!!} @endisset
                @endslot
                @if(isset($textNotes) && !is_bool($textNotes)){!!$textNotes!!}@endif
            @endcomponent
        </div>
    @endif
    <div class="w-full @if(!isset($variantProvider)) md:w-1/2 @endif grid grid-cols-2 grid-rows-{{count($modelTable)}} pt-4 md:pr-2 self-start">
        @foreach ($modelTable as $key => $value)
            <div class="col-span-2 border-b-2 row-span-1 grid grid-cols-2 m-0 p-0">
                <div class="@isset($variantProvider) col-span-1 @else col-span-2 md:col-span-1 @endisset self-center p-0">
                    @if(array_key_exists('label', $value))
                        @component('components.labels.label')
                            {{ $value['label'] }}
                        @endcomponent   
                    @endif
                </div>
                <div class="@isset($variantProvider) col-span-1 @else col-span-2 md:col-span-1 @endisset self-center p-0">
                    @if(isset($value["inputsEx"]))
                        @foreach($value["inputsEx"] as $components)
                            @component($components['kind'], slotsItem($components)) @slot("classEx") md:text-right @isset($components["classEx"]){{ $components["classEx"] }}@endisset border-0 @endslot @endcomponent
                        @endforeach
                    @else
                        @component("components.inputs.input-text")    
                            @slot("classEx")
                                md:text-right border-0 @isset($value["classExInput"]){{$value["classExInput"]}} @endisset
                            @endslot
                            @slot("attributeEx")
                                placeholder="$0.00"
                                readonly
                                @isset($value["attributeExInput"]) 
                                    {!!$value["attributeExInput"]!!}
                                @endisset
                            @endslot
                        @endcomponent
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>