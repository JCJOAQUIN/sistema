@php
	!isset($variant) ? $v = "green" : $v = $variant;
	
	$color = [
		"green" => "switch-checked:bg-green-400",
		"blue"  => "switch-checked:bg-blue-500"
	];
	$mainClasses = "toggle-checkbox absolute block w-4 h-4 rounded-full shadow-md bg-white border-4 border-white appearance-none cursor-pointer transform ransition duration-200 ease-in checked:translate-x-full";
	$arrayFind = [
		'6' => ['bg-']
	];
	if(!isset($variant) || $variant != "noContainer") $noContainer = true;
@endphp
	<div class="relative inline-block w-8 mr-2 align-middle select-none @isset($classExContainer) {!!$classExContainer!!} @endisset">
		<input type="checkbox" class="{{isset($classExLabel) ? $classExLabel : ''}} @if(isset($classEx)) {{replaceClassEX($classEx, $mainClasses, $arrayFind)}} @else {{$mainClasses}} @endif" @isset($attributeEx) {!!$attributeEx!!} @endisset />
		<label for="{{getAttribute($attributeEx, 'id')}}" class="toggle-label block overflow-hidden h-4 rounded-full bg-gray-300 cursor-pointer {{isset($classExLabel) ? $classExLabel : ''}} @if(!isset($noContainer)) w-8 @endisset {{ $color[$v]?? $color["green"] }} label-checked:m-4">
		</label>
	</div>
<label for="{{getAttribute($attributeEx, 'id')}}" class="{{isset($classExLabel) ? $classExLabel : ''}} {{isset($classLabel) ? replaceClassEX($classLabel, 'w-max', [['w-']]) : 'w-max'}}"> {!! $slot !!} </label>