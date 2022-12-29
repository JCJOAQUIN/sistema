<div class="mt-2 @isset($classEx) {{$classEx}} @endisset">
    @isset($title) 
        <div class="bg-warm-gray-100 p-4 h-auto border rounded-sm">
            @component('components.labels.label')
                @slot('classEx')
                    font-semibold
                    @isset($classExTitle) {{ $classExTitle }} @endisset
                @endslot
                {!! $title !!}
            @endcomponent
            @isset($subtitle) @component("components.labels.label") {!!$subtitle!!} @endcomponent @endisset
        </div>
    @endisset
    <div class="bg-white border @isset($title) border-t-0 @endisset p-4">
        @isset($fields) {!!$fields!!} @endisset
    </div>
</div>