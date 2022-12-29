@php
    $mainClasses = "flex pl-2 border-black border-l-4 h-8 items-center font-medium text-black";
    $arrayFind = ["1" => ["pl-"],"3" => ["border-l-"],"4" => ["h-"],"6" => ["font-"],"7" => ["text-"]];
@endphp
<div class="@isset($classExContainer) {{replaceClassEX($classExContainer, $mainClasses, $arrayFind)}} @else {{$mainClasses}} @endisset">
    <label @if(isset($attributeEx)) {!!$attributeEx!!} @endif>
        @isset($label) {!!$label!!} @else @isset($slot) {!!$slot!!} @endisset @endisset
    </label>
</div>