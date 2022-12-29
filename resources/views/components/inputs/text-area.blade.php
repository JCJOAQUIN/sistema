@php 
    $mainClasses = "resize-none h-40 w-full text-gray-700 border rounded p-4 leading-tight";
    $arrayFind = [
        '1' => ['h-'],
		'6' => ['p-', 'py-', 'px-'],
	];
    if (isset($label))
    {
        $text = $label;
    } else if(isset($slot))
    {
       $text = $slot;
    }
@endphp
<textarea 
    class="@if(isset($classEx)) {{replaceClassEX($classEx, $mainClasses, $arrayFind)}} @else {{ $mainClasses }} @endif" 
    @if(isset($attributeEx)) {!!$attributeEx!!} @endif>{!!$text!!}
</textarea>