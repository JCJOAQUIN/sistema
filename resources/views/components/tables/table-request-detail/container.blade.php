@php
    !isset($variant) ? $v = "primary" : $v = $variant;

    $titleClass = [
        "primary" => "bg-gradient-to-r from-amber-500 to-amber-500 via-amber-400 text-white rounded-t-md py-2",
        "simple"  => "",
        "left"    => ""
    ];

    $rowsClass = [
        "primary" => "border rounded-b-md divide-dashed divide-y divide-gray-500 children:py-2",
        "simple"  => "",
        "left"    => ""
    ];

    $containerClass = 
    [
        "primary"   => "max-w-screen-md flex-col items-center mx-auto",
        "left"      => "",
        "simple"    => "max-w-screen-md"
    ];
    
	$mainClasses = $containerClass[$v];

	$arrayFind = ["3" => ["m-", "mx-", "my-"], "0" => ["max-w-"]];
@endphp

<div @isset($classEx) class="{{ replaceClassEX($classEx, $mainClasses, $arrayFind) }}" @else class="{{$mainClasses}}" @endisset>
    @isset($title)
        <div class="@isset($variantDetail) {{$variantDetail}} @endisset {{ $titleClass[$v]?? $titleClass["primary"]  }} w-full flex flex-row justify-center uppercase font-bold">
            {!! $title !!}
        </div>
    @endisset
    <div class="md:col-start-3 col-span-7 md:col-span-3 {{ $rowsClass[$v]?? $rowsClass["primary"]  }}">
        {!! $slot !!}
    </div>
</div>