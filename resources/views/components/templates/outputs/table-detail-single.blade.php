@php
    isset($variant) ? $v = $variant : $v = "simple";
@endphp
<div class="@if($v=='left') my-8 md:m-8 @else my-8 @endif">
    @component("components.tables.table-request-detail.container",["variant" => $v, "classEx" => (isset($classEx)?$classEx : '')])
        @foreach ($modelTable as $key => $value)
            @component('components.tables.table-request-detail.row', ["variant" => $v, "simple" => true, "classExRow" => (isset($classExRow)?$classExRow : '')])
                @component('components.tables.table-request-detail.left', ["variant" => $v, "simple" => true]){!!$key!!}: @endcomponent
                @if(is_array($value))
                    @component('components.tables.table-request-detail.right', ["variant" => $v, "simple" => true])
                        @foreach ($value as $component)
                            @component($component["kind"], slotsItem($component)) @endcomponent
                        @endforeach
                    @endcomponent
                @else
                    @component('components.tables.table-request-detail.right', ["variant" => $v, "simple" => true]){!!$value!!} @endcomponent
                @endif
            @endcomponent
        @endforeach
    @endcomponent
</div>