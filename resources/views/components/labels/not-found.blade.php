@php
	$mainClasses = "border-solid p-3 my-4 roudend rounded-md h-22 border-transparent";
	$arrayFind = ["1"=> ["p-", "px-", "py-"], "2" => ["m-", "mx-", "my-"]];
	$flagDefault = true;
	if(isset($variant))
	{
		switch($variant)
		{
			case "alert":
				$mainClasses = $mainClasses." bg-red-600 text-left";
				$text        = "ALERTA";
				$flagDefault = false;
				$labelColor  = "text-white";
				break;
			case "note":
				$mainClasses = $mainClasses." bg-blue-50 text-left";
				$text        = "NOTA";
				$flagDefault = false;
				$labelColor  = "text-blue-900";
				break;
			default:
				$mainClasses = $mainClasses." bg-red-300 text-red-900 text-center font-bold bg-opacity-25";
				$text = "No se han encontrado coincidencias con la búsqueda";
				break;
		}
	}
	else
	{
		$mainClasses = $mainClasses." bg-red-300 text-red-900 text-center font-bold bg-opacity-25";
		if(!isset($text))
		{
			$text = "No se han encontrado coincidencias con la búsqueda";
		}
	}
@endphp
<div class="@if(isset($classEx) && $classEx != ''){{ replaceClassEX($classEx, $mainClasses, $arrayFind) }} @else{{$mainClasses}} @endif" 
	@if(isset($attributeEx)) {!!$attributeEx!!} @endif>
	@if($flagDefault)
		{!!$text!!}
	@else 
		@component('components.labels.label') 
			@slot('classEx')
				{!!$labelColor!!} font-bold
			@endslot
			@if(!isset($title)) {!!$text!!} @else {!!$title!!} @endisset
		@endcomponent
		@component('components.labels.label') 
			@slot('classEx')
				{!!$labelColor!!}
			@endslot
			{!!$slot!!} 
		@endcomponent
	@endif
	@isset($contentEx)
		<div class="w-full flex flex-wrap justify-center">
			@foreach($contentEx as $component)
				@if(is_array($component))
					<div class="w-full sm:w-1/2 md:w-1/4">
						@if(collect($component)->has('label') && !collect($component)->has('kind'))
							{!!$component['label']!!}
						@else
							@if(collect($component)->has('kind'))
								@component($component['kind'], slotsItem($component)) @endcomponent
							@endif
						@endif
					</div>
				@endif
			@endforeach
		</div>
	@endisset
</div>