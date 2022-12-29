@php
    $mainClasses = "cursor-pointer bg-orange-600 uppercase text-white font-bold px-12 py-2 flex justify-center items-center m-1 rounded rounded-lg text-sm";
    $arrayFind = ["1" => ["bg-"], "5" => ["p-", "py"], "6" => ["p-", "px-"]];
@endphp
<button class="@if(isset($classEx) && $classEx != '') {{ replaceClassEX($classEx, $mainClasses, $arrayFind) }} @else {{ $mainClasses }} @endisset" @isset($attributeEx) {!!$attributeEx!!} @else type="submit" @endisset>
    <span class="icon-search mr-1"></span>
    <span>Buscar</span>
</button>
