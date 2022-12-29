@php
    $totalCols          = count($modelBody[0]);  
    $bool = false;
    $flag = false;
    foreach($modelBody as $main)
    {
        foreach($main as $index => $tr)
        {
            if($index == array_key_last($main) && !isset($tr["content"][0]["kind"]))  
            {
                $bool = true;
            }
            foreach($tr["content"] as $td)
            {
                if(isset($td["kind"]) && $td["kind"] == "components.buttons.button")
                {
                    $flag = true;
                    $replicatePositions[] = $td;
                }
            }
        }
    }
    $buttonCondition = "v-".array_search('components.buttons.button', array_column($main[count($main)-1]['content'], "kind"));
    empty($taxesRetention[0]) ? $taxesRetention = [] : $taxesRetention = $taxesRetention;
	empty($taxesTransfer[0]) ? $taxesTransfer = [] : $taxesTransfer = $taxesTransfer;
@endphp
<div class="grid {{$buttonCondition == 'v-0' ? 'grid-cols-12' : 'grid-cols-11'}} text-xs gap-x-1 @isset($classEx) {{$classEx}} @endisset w-full" @isset($attributeEx) {!!$attributeEx!!} @endisset>
    @isset($title)
        <div class="w-full text-center text-xl text-white bg-orange-500 border-b-2 border-white">
            <strong>{!!$title!!}</strong>
        </div>
    @endisset
    <table class="border-collapse border border-orange-300 @if($bool==true) md:col-span-11 col-span-12 @elseif($flag == false) md:col-span-11 col-span-12 @else col-span-12 md:col-span-11 @endif">
        <thead class="hidden md:table-header-group bg-white md:bg-orange-500 text-orange-500 md:text-white text-center font-semibold text-sm">
            <tr class="grid auto-cols-fr grid-flow-col">
                @foreach($modelHead as $index => $value)
                    @if($index != array_key_last($modelHead))
                        <th class="border-b border-r border-orange-300">
                            {!! $value["value"] !!}
                        </th>
                    @else
                        @if($bool == true)
                            <th class="border-b border-r border-orange-300">
                                {!! $value["value"] !!}
                            </th>
                        @elseif($flag == false)
                            <th class="border-b border-r border-orange-300">
                                {!! $value["value"] !!}
                            </th>
                        @endif
                    @endif
                @endforeach
            </tr>
        </thead>
        <tbody class="children:even:bg-orange-100">
            <tr class="tr grid md:grid-cols-{{$totalCols-($buttonCondition == 'v-0' ? 1 : 0)}} grid-cols-12">
                @foreach($main as $index => $tr)
                    @if($index < count($main)-($buttonCondition == 'v-0' ? 1 : 0))
                        <td class="border-r md:hidden {{$index == array_key_last($main)-($buttonCondition == 'v-0' ? 1 : 0) ? 'border-b-0' : 'border-b'}} col-span-6 flex items-center justify-center font-semibold text-orange-500 border-orange-300">
                            {!!$modelHead[$index]['value']!!}
                        </td>
                        <td class="border-r-0 md:border-r md:border-0 {{$index == array_key_last($main)-($buttonCondition == 'v-0' ? 1 : 0) ? 'border-b-0' : 'border-b'}} flex items-start justify-center md:col-span-1 col-span-6 border-orange-300">
                            @if(isset($tr['content'][0]))
                                @foreach($tr["content"] as $td)
                                    @isset($td["kind"])
                                        @component($td["kind"], slotsItem($td)) @endcomponent
                                    @else
                                        {!!$td["label"]!!}
                                    @endisset
                                @endforeach
                            @else
                                {!!$tr["content"]["label"]!!}
                            @endif
                        </td>
                    @endif
                @endforeach
            </tr>
        </tbody>
    </table>
    @if(isset($replicatePositions) )
        <table class="col-span-12 md:col-span-1 md:row-span-3 m-0 justify-center border border-orange-300 md:m-0 mt-1"> {{--check vertical-alignment--}}
            <thead class="text-sm row-span-1 bg-orange-500 border border-orange-300 text-center font-semibold text-white hidden md:block">
                <tr class="flex items-center justify-center">
                    <th colspan="2">
                        <div class="">
                            Acción
                        </div>
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <div class="flex items-center justify-center">
                            @foreach($replicatePositions as $buttons)
                                @component($buttons["kind"], slotsItem($buttons)) @endcomponent
                            @endforeach
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    @endif
    <table class="add_TaxesTable @isset($script) hidden retention_tax_body @endisset @if($bool==true) col-span-12 md:col-span-11 @elseif($flag == false) col-span-12 md:col-span-11 @else md:col-span-11 col-span-12 @endif grid-word-wrap w-full border border-orange-300 divide-y divide-orange-300 mt-1" @isset($classExRetention) {{$classExRetention}} @endisset @isset($attributeExRetention) {!!$attributeExRetention!!} @endisset @if(empty($taxesRetention) || !isset($taxesRetention)) style="display:none;" @endif>
        @if(isset($taxesRetention) && !empty($taxesRetention))
            <thead class="grid grid-cols-12">
                <tr class="md:bg-orange-500 col-span-12 font-semibold text-orange-500 md:text-white grid grid-cols-12">
                    <th class="col-start-1 col-span-1 md:hidden text-center">
                        <div class="flex items-center justify-center w-full h-full">
                            <svg class="cursor-pointer arrow-action" fill="none" height="35" stroke="#f97316" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg">
                                <polyline class="polyline" points="18 15 12 9 6 15"></polyline>
                            </svg>
                        </div>
                    </th>
                    <th class="col-span-10 md:col-span-12">
                        <div class="flex items-center justify-center w-full h-full">
                            Retenciones
                        </div>
                    </th>
                </tr>
            </thead>
            <tbody class="add_TaxesData retention-row w-full children:even:bg-orange-100 divide-y divide-orange-300">
                @foreach($taxesRetention as $retentions)
                    <tr class="col-to-hide w-full grid auto-cols-fr grid-flow-col md:flex md:flex-nowrap text-center divide-x divide-orange-300">
                        @foreach($retentions as $details)
                            @if(isset($details['content'][0]))
                                <td class="w-full flex justify-center items-center flex-wrap content-center " @isset($details["classEx"]) {{$details["classEx"]}} @endisset>
                                    @foreach($details["content"] as $data)
                                        @isset($data["kind"])
                                            @component($data["kind"], slotsItem($data)) @endcomponent
                                        @else
                                            {!!$data["label"]!!}
                                        @endisset
                                    @endforeach
                                </td>
                            @else
                                <td class="w-full flex justify-center items-center flex-wrap content-center " @isset($details["classEx"]) {{$details["classEx"]}} @endisset>{!!$details["content"]["label"]!!}</td>
                            @endif
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        @endif
    </table>
    <table class="add_TaxesTable @isset($script) hidden transfer_tax_body @endisset @if($bool==true) col-span-12 @elseif($flag == false) col-span-12 @else md:col-span-11 col-span-12 @endif border border-orange-300 w-full divide-y divide-orange-300 md:m-0 mt-1 @isset($classExTransfer) {{$classExTransfer}} @endisset" @isset($attributeExTransfer) {!!$attributeExTransfer!!} @endisset @if(empty($taxesTransfer) || !isset($taxesTransfer)) style="display:none;" @endif>
        @if(isset($taxesTransfer) && !empty($taxesTransfer))
            <thead class="grid grid-cols-12">
                <tr class="md:bg-orange-500 col-span-12 font-semibold text-orange-500 md:text-white grid grid-cols-12">
                    <th class="col-start-1 col-span-1 md:hidden text-center">
                        <div class="flex items-center justify-center w-full h-full">
                            <svg class="cursor-pointer arrow-action" fill="none" height="35" stroke="#f97316" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg">
                                <polyline class="polyline" points="18 15 12 9 6 15"></polyline>
                            </svg>
                        </div>
                    </th>
                    <th class="col-span-10 md:col-span-12">
                        <div class="flex items-center justify-center w-full h-full">
                            Traslados
                        </div>
                    </th>
                </tr>
            </thead>
            <tbody class="col-to-hide add_TaxesData retention-row w-full children:even:bg-orange-100 divide-y divide-orange-300 hidden md:block">
                @foreach($taxesTransfer as $transfer)
                    <tr class="w-full grid auto-cols-fr grid-flow-col md:flex md:flex-nowrap text-center divide-x divide-orange-300">
                        @foreach($transfer as $details)
                            @if(isset($details['content'][0]))
                                <td class="w-full flex justify-center items-center flex-wrap content-center " @isset($details["classEx"]) {{$details["classEx"]}} @endisset>
                                    @foreach($details["content"] as $data)
                                        @isset($data["kind"])
                                            @component($data["kind"], slotsItem($data)) @endcomponent
                                        @else
                                            {!!$data["label"]!!}
                                        @endisset
                                    @endforeach
                                </td>
                            @else
                                <td class="w-full flex justify-center items-center flex-wrap content-center " @isset($details["classEx"]) {{$details["classEx"]}} @endisset>{!!$details["content"]["label"]!!}</td>
                            @endif
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        @endif
    </table>
</div>