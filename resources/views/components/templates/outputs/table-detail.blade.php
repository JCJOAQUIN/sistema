@php
    $class = 'relative text-center h-auto py-2 min-h-12 px-1';
@endphp
@component("components.tables.table-request-detail.container", ["variantDetail" => $class])
    @slot("title") 
        @isset($title) 
            @if(is_array($title))
                @foreach($title as $componentEx)
                    @if(!strpos($componentEx['kind'], 'label'))
                        <div class="@isset($componentEx["classParent"]) {{$componentEx["classParent"]}} @endisset">
                            @component($componentEx["kind"], slotsItem($componentEx)) 
                                @slot("classEx")
                                    @isset($componentEx["classEx"])
                                        {{ $componentEx["classEx"]}} text-white
                                    @else 
                                        text-white 
                                    @endisset
                                @endslot 
                            @endcomponent
                        </div>
                    @else
                        @component($componentEx["kind"], slotsItem($componentEx))
                            @slot("classEx")
                                @isset($componentEx["classEx"])
                                    {{ $componentEx["classEx"]}} ml-14 w-11/12
                                @endisset
                            @endslot 
                        @endcomponent
                    @endif
                @endforeach
            @else
                {!!$title!!}
            @endif
        @endisset
    @endslot
    @isset($classEx)
        @slot("classEx")
            {{$classEx}}
        @endslot
    @endisset
    @foreach ($modelTable as $item)
        @component("components.tables.table-request-detail.row")
            @component("components.tables.table-request-detail.left") {!!$item[0]!!} @endcomponent
            @if(is_array($item[1]))
                @foreach($item[1] as $key)
                    <div>
                        @component($key["kind"], slotsItem($key)) 
                            @slot("classEx") 
                                @isset($key["classEx"]) 
                                    {{$key["classEx"]}} ml-4 md:ml-0 md:text-right md:mr-2 
                                @else 
                                    ml-4 md:ml-0 md:text-right md:mr-2 
                                @endisset
                            @endslot 
                        @endcomponent
                    </div>
                @endforeach
            @else
                @component('components.tables.table-request-detail.right'){!!$item[1]!!} @endcomponent
            @endif
        @endcomponent
    @endforeach

    @isset($componentsEx)
        <div class="flex justify-items-center justify-center items-center">
            @isset($componentsEx[0])
                @foreach($componentsEx as $component)
                    @component($component["kind"], slotsItem($component)) @endcomponent
                @endforeach
            @else
                @component($componentsEx["kind"], slotsItem($componentsEx)) @endcomponent
            @endisset
        </div>
    @endisset

@endcomponent