@php 
    $mainClassesLabel = "bg-gray-100 border h-11 p-2 border-gray-300"; 
	$arrayFindLabel = [
		'3' => ['p-'],
		'0' => ['bg-']
	];

    $mainClassesInput = "appearance-none border border-gray-300 block w-full h-11 text-gray-700 bg-gray-100 py-4 px-4"; 
    $arrayFindInput = [
		'8' => ['p-', 'py-'],
		'9' => ['p-', 'px-'],
		'7' => ['bg-']
	];
@endphp
<div class="my-2 flex @if(isset($classEx)) {{ $classEx }} @endif">
    <label class="@if(isset($classExLabel)) {{replaceClassEX($classExLabel, $mainClassesLabel, $arrayFindLabel)}} @else {{$mainClassesLabel}} @endif">{{$slot}}</label>
    
    @isset($select)
        <div class="w-full h-11 border border-gray-300">
            {!!$select!!}
        </div>
    @else
        <input  class="@if(isset($classExInput)) {{replaceClassEX($classExInput, $mainClassesInput, $arrayFindInput)}} @else {{$mainClassesInput}} @endif"
            @if(isset($attributeExInput)) {!!$attributeExInput!!} @endif
            >
    @endisset
</div>