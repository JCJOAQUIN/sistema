<div class="grid text-center">
    @isset($variant)
        <div class="bg-orange-400 rounded-md w-24 h-24 flex justify-center items-center text-center">
            @component('components.labels.label')
                {!!$icon!!}
            @endcomponent
        </div>
    @else
        <div class="@isset($classExContainer) {{ $classExContainer }} @endisset">
            <input type="radio" 
                @isset($attributeEx) {!! $attributeEx !!} @endisset
                class="@isset($classEx) {{ $classEx }} @endisset" hidden/>
            <label
                @isset($attributeEx) for="{{ getAttribute($attributeEx, 'id') }}" @else for="" @endisset 
                @isset($attributeExLabel) {!!$attributeExLabel!!} @endisset
                class="p-5 rounded block border-0 bg-orange-400 label-checked:bg-orange-600 @if(!strstr($attributeEx, "disabled")) hover:bg-orange-500 cursor-pointer @endif ">
                {!!$icon!!} 
            </label>
        </div>
    @endisset
    @component('components.labels.label', ["classEx" => "font-semibold"])
        {!!$label!!}
    @endcomponent
</div>