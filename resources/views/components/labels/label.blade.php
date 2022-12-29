@php
    $mainClasses = "block tracking-wide text-blue-gray-700 ";
    $arrayFind = ["2" => ["text-"]];
@endphp
<label class="@if(isset($classEx)) {{replaceClassEX($classEx, $mainClasses, $arrayFind)}} @else {{$mainClasses}} @endif" @if(isset($attributeEx)) {!!$attributeEx!!} @endif>
    @isset($label) {!!$label!!} @else @isset($slot) {!!$slot!!} @endisset @endisset
</label>
