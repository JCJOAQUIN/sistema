@php
    $mainClasses = "select block w-full text-gray-700 border rounded py-4 appearance-none absolute inset-0"; 
    $arrayFind = [
		'2' => ['w-'],
		'6' => ['p-', 'py-'],
	];
@endphp
<div class="relative w-full @isset($classExContainer) {{$classExContainer}} @endisset">
    <select class="@if(isset($variant) && $variant == 'single') selectProvider @endif @if(isset($classEx)) {{replaceClassEX($classEx, $mainClasses, $arrayFind)}} @else {{$mainClasses}} @endif z-1"
        @isset($attributeEx) {!!$attributeEx!!} @endisset @if(!isset($variant)) multiple="multiple" @endif>
            @isset($options)
                @foreach($options as $option)
                    <option @isset($option['value']) value="{{$option['value']}}" @endisset @if(isset($option['selected']) && $option['selected'] == "selected") selected @endif @isset($option['attributeExOption']) {!!$option['attributeExOption']!!} @endisset> {{$option['description']}} </option>
                @endforeach
            @endisset
        {{--{!!preg_replace("/(\r)*(')*(\")*(\t)*(\n)*/", "", $slot)!!}--}}
    </select>
</div>