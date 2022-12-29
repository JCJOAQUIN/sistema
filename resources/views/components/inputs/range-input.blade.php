<div @isset($attributeEx) {!!$attributeEx!!} @endisset class="grid grid-cols-12 @if(isset($classIndividual)) {{ $classIndividual }} @else range_date @endif @if (isset($attributeEx) && str_contains($attributeEx, "disabled")) bg-gray-100 @else bg-white @endif border rounded-md border-gray-200 range_picker_parent">
    @isset($inputs)
        @foreach ($inputs as $key => $input)
            @if(isset($variant) && $variant === "time" && $key === array_key_first($inputs))
                <div class="col-span-1 text-gray-400 text-center mt-2">
                    De:
                </div>
            @endif
            <div class="col-span-5 text-center">
                @component('components.inputs.input-text')
                    @slot('classEx')
                    {{ (!isset($variant) ? "disabled" : "shadow-none px-0") }} bg-white border-0 @if(isset($input['input_classEx'])){{$input['input_classEx']}} border-none @endif 
                    @endslot
                    @slot('attributeEx')
                        {!!$input['input_attributeEx']!!} 
                        @if(!isset($variant)) readonly="readonly" @endif
                    @endslot
                @endcomponent
            </div>
            @if($key === array_key_first($inputs))
                @if(isset($variant) && $variant === "time")
                    <div class="col-span-1 text-gray-400 text-center mt-2">
                        A:
                    </div>
                @else
                    <div class="col-span-1 mt-2 text-gray-300">
                        -
                    </div>
                @endif
            @endif
        @endforeach
        @if(!isset($variant))
            <div class="col-span-1 mt-3">
                <label><span class="text-gray-300 text-2xl icon-calendar " @isset($attributeExInstance) {!!$attributeExInstance!!} @endisset></span></label>
            </div>
        @endif
    @endisset
</div>