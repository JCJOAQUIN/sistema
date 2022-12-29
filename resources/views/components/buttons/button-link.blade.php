@php
	isset($variant) ? $v = $variant : $v = 'default';

	$theme =
	[
		'default'	=> 'underline underline-offset-1 text-blue-700',
		"red"		=> 'cursor-pointer px-6 py-2 border border-red-400 rounded rounded-full bg-white',
		"reset"		=> 'cursor-pointer px-6 py-2 border border-gray-300 rounded rounded-full bg-white',
	];
@endphp
<a
	class="{{$theme[$v]}} @isset($classEx) {{$classEx}} @endisset"
	@isset($attributeEx ) {!!$attributeEx!!} @endisset>
	@isset($label ) {!!$label!!} @else {!! $slot !!} @endisset
</a>