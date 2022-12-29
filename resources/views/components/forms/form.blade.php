@php
	isset($attributeEx) ? $v = getAttribute($attributeEx, 'method') : $v = $attributeEx = "";
	if(mb_strtoupper($v) != "GET" && $v != "")
	{
		$token = true;
	}
	($v == "") ? $attributeEx = $attributeEx." method=\"GET\"" : '';
	$attributeEx = preg_replace("/(\n)*(\r)*(\t)*/", "", $attributeEx);
	if (isset($files) && $files)
	{
		$attributeEx .= ' accept-charset="UTF-8" enctype="multipart/form-data"';
	}
@endphp
<form @isset($attributeEx) {!!$attributeEx!!} @endisset @isset($classEx) class="{{$classEx}}" @endisset>
	@isset($token) @csrf @endisset
	@isset($methodEx) @method($methodEx) @endisset
	@isset($componentsEx)
		@if(is_array($componentsEx))
			<div class="w-full flex md:grid md:grid-cols-2">
				@foreach ($componentsEx as $component)
					<div class="col-span-1 w-full">
						@component($component["kind"], slotsItem($component)) @endcomponent
					</div>
				@endforeach
			</div>
		@else
			{!! $componentsEx !!}
		@endif
	@endisset
	{!! $slot !!}
</form>
