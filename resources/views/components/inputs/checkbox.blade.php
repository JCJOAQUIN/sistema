@php 
    if(isset($radio))
    {
        $check = "label-checked:bg-red-500";
    }
    else
    {
        $check = "switch-checked:bg-red-500";
    }
    $mainClassesLabel = "cursor-pointer bg-gray-500 rounded-full text-white px-3 py-1 ".$check; 
	$arrayFind = [
		'5' => ['p-', 'px-'], 
		'6' => ['p-', 'py-'], 
		'2' => ['bg-']
	];
    
@endphp
<div class="@isset($classExContainer) {{ $classExContainer }} @endisset">
    <input @isset($radio) type="radio" @else type="checkbox" @endif
        @isset($attributeEx) {!!$attributeEx!!} @endisset
        class="@isset($classEx) {{ $classEx }} @endisset" hidden/>
    <label 
        @isset($attributeEx) for="{{getAttribute($attributeEx, 'id')}}" @endisset
        class="@if(isset($classExLabel) && $classExLabel != "") {{replaceClassEX($classExLabel, $mainClassesLabel, $arrayFind)}} @else {{$mainClassesLabel}} @endif">
        @isset($label) {!! $label !!} @else {!! $slot !!} @endisset
    </label>
</div>