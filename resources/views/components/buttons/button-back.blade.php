@php
	$mainClasses = "bg-orange-500 p-1 hover:bg-orange-300 rounded-bl-md text-white font-bold m-0 lg:pt-3 lg:pb-3 lg:pl-5 lg:pr-5 top-15 lg:top-6 fixed right-6 no-underline uppercase w-auto z-10 text-sm lg:text-base";
	$arrayFind = ["1" => ["p-", "px-", "py-"], "0" => ["bg-"], "6" => ["m-", "mx-", "my-"]];
@endphp

<a class="@isset($classEx) {{replaceClassEX($classEx, $mainClasses, $arrayFind)}} @else {{$mainClasses}} @endisset" @if(isset($attributeEx)) {!!$attributeEx!!} @endif href="{{ $href }}">
	« Regresar
</a>