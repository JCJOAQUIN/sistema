<div class="justify-center flex">
    <div class="p-4 bg-gray-100 flex flex-wrap justify-center w-full md:w-1/2">
        <div class="w-full font-bold text-center @isset($classExLabel) {!!$classExLabel!!} @endisset" 
            @isset($attributeExLabel) {!!$attributeExLabel!!} @endisset>
            @isset($textLabel) {!!$textLabel!!} @else Por favor, seleccione una opción para dictaminar la solicitud @endisset
        </div>
        @component('components.buttons.button-approval')
            @slot('classExContainer') 
                w-full md:w-1/2 flex py-2
            @endslot
            @slot('classExLabel') 
                w-full rounded-full m-2 px-3 py-1 label-checked:text-white bg-white border-blue-500 text-blue-500 label-checked:bg-blue-500 border text-center
            @endslot
            @isset($classExButton)
                @slot('classEx') {!! $classExButton !!} @endslot
            @endisset
            @isset($attributeExButton) 
                @slot('attributeEx') {!!$attributeExButton!!} @endslot
            @endisset 
            <span class="icon-check pr-1"></span> Aprobar
        @endcomponent
        @component('components.buttons.button-approval')
            @slot('classExContainer') 
                w-full md:w-1/2 flex py-2
            @endslot
            @slot('classExLabel') 
                w-full rounded-full m-2 px-3 py-1 label-checked:text-white bg-white border-red-500 text-red-500 label-checked:bg-red-500 border text-center
            @endslot
            @isset($classExButtonTwo)
                @slot('classEx') {!! $classExButtonTwo !!} @endslot
            @endisset
            @isset($attributeExButtonTwo)
                @slot('attributeEx') {!!$attributeExButtonTwo!!} @endslot
            @endisset
            <span class="icon-cross pr-1"></span> Rechazar            
        @endcomponent
    </div>
</div>