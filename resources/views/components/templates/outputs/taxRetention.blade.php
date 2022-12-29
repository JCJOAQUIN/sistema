@php
    $less = 0;
    foreach($taxesRetention as $Retention)
    {
        foreach($Retention as $details)
        {
            if(isset($details["classEx"]))
            {
                $less = 1; 
            }
        }
    }
    $totalColsRetentions    = count($taxesRetention[0]) - $less;
@endphp
<div>
    <div class="grid grid-cols-12">
        <div class="col-span-1 md:hidden text-center">
            <svg class="cursor-pointer arrow-action" fill="none" height="24" stroke="#f97316" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg">
                <polyline class="polyline" points="18 15 12 9 6 15"></polyline>
            </svg>
        </div>
        <div class="md:bg-orange-500 col-span-10 md:col-span-12 font-semibold text-orange-500 md:text-white text-center md:w-full">Retenciones</div>
    </div>
    <div class="add_TaxesData w-full children:even:bg-orange-100 divide-y divide-orange-300 hidden md:block">
        @foreach($taxesRetention as $retentions)
            <div class="w-full grid grid-cols-{{$totalColsRetentions}} md:flex md:flex-wrap md:flex-nowrap text-center divide-x divide-orange-300">
                @foreach($retentions as $details)
                    @if(isset($details['content'][0]))
                        <div class="w-full" @isset($details["classEx"]) {{$details["classEx"]}} @endisset>
                            @foreach($details["content"] as $data)
                                @isset($data["kind"])
                                    @component($data["kind"], slotsItem($data)) @endcomponent
                                @else
                                    {!!$data["label"]!!}
                                @endisset
                            @endforeach
                        </div>
                    @else
                        <div class="w-full" @isset($details["classEx"]) {{$details["classEx"]}} @endisset>{!!$details["content"]["label"]!!}</div>
                    @endif
                @endforeach
            </div>
        @endforeach
    </div>
</div>