@php 
    $mainClasses = "bg-warm-gray-100 grid grid-cols-2 md:grid-cols-4 gap-2 md:gap-4 p-2 md:p-6 my-4"; 
	$arrayFind = [
		'0' => ['bg-'],
        '8' => ['m-', 'my-']
	];
@endphp
<div class="@if(isset($classEx) && $classEx != "") {{replaceClassEX($classEx, $mainClasses, $arrayFind)}} @else {{ $mainClasses }} @endif"
    @if(isset($attributeEx)) {!!$attributeEx!!} @endif >
    @isset($content) {!!$content!!} @else {{$slot}} @endisset
</div>