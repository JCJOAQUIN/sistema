@php
	if (isset($variant)) {
		$mainClasses = "appearance-none block w-full text-gray-700 focus:outline-none";
	}
	else 
	{
		$mainClasses = "appearance-none block w-full text-gray-700 border rounded py-2 px-3 m-px"; 
	}
	$arrayFind = [
		'2' => ['w-'],
		'6' => ['p-', 'py-'],
		'7' => ['p-', 'px-']
	];
@endphp
<input class="@if(isset($classEx) && $classEx != '') {{replaceClassEX($classEx, $mainClasses, $arrayFind)}} @else {{$mainClasses}} @endif"
		@if(isset($classEx)) 
			@if(strpos($classEx, 'datepicker2') !== false || strpos($classEx, 'datepicker') !== false) onpaste="return false" @endif  
		@endif
		@if(isset($attributeEx)) {!! $attributeEx !!} @endif 
>