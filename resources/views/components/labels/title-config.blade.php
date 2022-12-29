@php
    $mainClasses = "pt-3 pb-1 flex flex-wrap text-3xl font-medium justify-center text-center uppercase";
    $arrayFind = ["0" => ["p-", "pt-"], "1" => ["p-", "pb-"]];	
@endphp

<div class="@isset($classEx) {{ replaceClassEX($classEx, $mainClasses, $arrayFind) }} @else {{$mainClasses}} @endisset">
    <h1 class="text-gray-800">{!!$slot!!}</h1>
</div>