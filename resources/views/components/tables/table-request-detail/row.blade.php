@php
    isset($variant) ? $v = $variant : $v = "simple";
    $row = 
    [
        "primary" => "grid sm:grid-cols-2 grid-cols-1 items-center",
        "simple" => "grid sm:grid-cols-2 grid-cols-1 items-center",
        "left"   => ""
    ];
    (isset($simple) && $v = "simple") ? $class = "grid grid-cols-1 sm:grid-cols-2 w-full sm:w-2/3" : $class = $row[$v];
    $arrayFind = ["4" => ["sm:w-"]];
@endphp

<div @if(isset($classExRow) && $classExRow != "") class="{{replaceClassEX($classExRow, $class, $arrayFind)}}" @else class="{{$class}}" @endisset>
    {!! $slot !!}
</div>