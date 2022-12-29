@php
    $mainClasses = "cursor-pointer px-3 py-1 border border-gray-300 rounded label-checked:bg-orange-400 label-checked:text-white bg-white w-full text-center flex items-center justify-center";
    $arrayFind = ["1" => ["p-", "py-"], "2"=>["p-", "px"], "4" =>["border-"], "5" => ["rounded-"], "6" => ["label-checked:bg"]];
@endphp

<div class="@isset($classExContainer) {{ $classExContainer }} @endisset flex">
    <input type="radio" 
        @isset($attributeEx) {!! $attributeEx !!} @endisset
        class="@isset($classEx) {{ $classEx }} @endisset" hidden/>
    <label
		@isset($attributeEx) for="{{ getAttribute($attributeEx, 'id') }}" @else for="" @endisset 
        @isset($attributeExLabel) {!!$attributeExLabel!!} @endisset
        class="@isset($massiveVariant) cursor-pointer px-5 py-2 text-orange-400 label-checked:bg-orange-400 label-checked:text-white rounded-full border border-orange-400 @else @if(isset($classExLabel) && $classExLabel != '') {{replaceClassEX($classExLabel, $mainClasses, $arrayFind)}} @else {{$mainClasses}} @endif @endisset ">
        @isset($label) {!!$label!!} @else {!! $slot !!} @endisset
    </label>
</div>