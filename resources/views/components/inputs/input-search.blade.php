@php 
    $mainClasses = "flex flex-col max-w-screen-md rounded pt-3 pb-4 mt-4 mx-auto bg-gray-100 text-left font-bold p-6"; 
    $arrayFind = [
		'4' => ['p-', 'pt-'],
		'5' => ['p-', 'pb-'],
		'6' => ['m-', 'mt-'],
		'7' => ['m-', 'mx-'],
		'8' => ['bg-'],
        '11'=> ['p-', 'py-']
	];

    $mainClassesInput = "text-gray-700 border-0 rounded py-3 px-4 w-full rounded-lg border-white focus:outline-none focus:shadow-none"; 
    $arrayFindInput = [
		'4' => ['p-', 'py-'],
		'5' => ['p-', 'px-']
	];

    $mainClassesSpan = "icon-search mt-4"; 
	$arrayFindSpan = [
		'1' => ['m-', 'mt-']
	];
@endphp
<div class="@if(isset($classEx)) {{replaceClassEX($classEx, $mainClasses, $arrayFind)}} @else {{$mainClasses}} @endif">
    {!! $slot !!} 
    <div class="flex flex-row text-gray-700 border bg-white rounded w-full rounded-lg whitespace-nowrap">
        <input class="@if(isset($classExInput)) {!!replaceClassEX($classExInput, $mainClassesInput, $arrayFindInput)!!} @else {!!$mainClassesInput!!} @endif"
        @isset($attributeExInput) {!!$attributeExInput!!} @endisset>
        <span class="icon-search @if(isset($classExSpan)) {{replaceClassEX($classExSpan, $mainClassesSpan, $arrayFindSpan)}} @endif py-4 px-4 text-gray-400"></span>
    </div>
    <div class="w-full text-center pt-4">
        @isset($attributeExButton)    
            @component('components.buttons.button', ['variant' => 'warning'])
                @isset($classExButton)     
                    @slot('classEx') 
                        {{ $classExButton }}
                    @endslot
                @endisset
                @isset($attributeExButton) 
                    @slot('attributeEx') 
                        {!!$attributeExButton!!}
                    @endslot
                @endisset
                Buscar
            @endcomponent
        @endisset
    </div>
</div>