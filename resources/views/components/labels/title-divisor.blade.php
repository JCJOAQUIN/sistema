@php
    $mainClasses = "my-4";
    $arrayFind = ["0" => ["m-", "my-", "m"]];
@endphp
<div class="@isset($classExContainer) {{replaceClassEX($classExContainer, $mainClasses, $arrayFind)}} @else {{$mainClasses}} @endisset">
    <div class="@isset($classEx) {{ $classEx }} @endisset flex justify-center uppercase font-semibold">
        @isset($label) {!! $label !!} @else {!! $slot !!}  @endisset 
    </div>
    <div class="items-center flex mt-1 mr-0">
        <div class="bg-cool-gray-500 h-px w-1/2"></div>
        <div class="bg-amber-500 h-1 w-56"></div>
        <div class="bg-cool-gray-500 h-px w-1/2"></div>
    </div>
</div>
