<div class="flex flex-wrap md:space-x-4 justify-center mt-6 mb-4">
    @foreach ($buttons as $button)
        @component("components.buttons.button-approval")
            @slot('classExContainer')
                w-full
                md:w-48
                my-3
            @endslot
            @slot("classExLabel")
                px-0
                {{ (str_contains($button['attributeButton'], "disabled") ? "disabled" : "") }}
            @endslot
            @slot("attributeEx")
                {!!$button['attributeButton']!!}    
            @endslot
            {!!$button['textButton']!!}
        @endcomponent
    @endforeach
</div>