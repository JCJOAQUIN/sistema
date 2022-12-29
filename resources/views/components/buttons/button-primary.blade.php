@php
	$mainClasses = "bg-gradient-to-tr from-red-600 via-orange-500 to-amber-400 hover:from-red-700 hover:to-amber-500 text-center m-3 py-1 rounded-full no-underline font-bold w-full";
	$arrayFind = ["7" => ["m-", "mx-", "my-"], "8" => ["p-", "py-"]];
@endphp
<a class="@isset($classEx) {{ replaceClassEX($classEx, $mainClasses, $arrayFind) }} @else {{$mainClasses}} @endisset" href="@if(isset($href)) {{$href}} @endif">
	<div class="grid grid-cols-12 gap-2 items-center pl-1">
		<span class="col-span-2 pt-px rounded-full h-8 w-8 justify-center bg-white text-orange-600 font-black text-xl ">{{substr($slot,0,1)}}</span> 
		<p class="col-span-8 text-white text-sm">{{$slot}}</p>
	</div>
</a>