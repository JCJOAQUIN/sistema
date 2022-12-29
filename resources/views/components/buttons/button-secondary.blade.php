@php
    $mainClasses = "border-gray-200 border rounded-md text-black cursor-pointer m-5 max-w-full p-2 no-underline uppercase hover:bg-orange-500 hover:border-none hover:shadow-md block ";
    $arrayFind = ["3" => ["text-"], "7" => ["p-", "px-", "py-"], "5" => ["m-", "mx-", "my-"]];
@endphp
<a class="@isset($classEx) {{replaceClassEX($classEx, $mainClasses, $arrayFind)}} hover:text-white @else {{ $mainClasses }} hover:text-white @endisset" @if (isset($href)) href="{{ $href }}" @endif>
    {!! $slot !!}
</a>