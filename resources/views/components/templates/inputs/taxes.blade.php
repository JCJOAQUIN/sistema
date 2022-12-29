@php
    $type = $type ?? 'default';
    switch ($type) {
        case 'retention':
            $title          = 'Retenciones:';
            $labelName      = 'Nombre:';
            $placeholderName= 'Retención';
            $labelAmount    = 'Importe de retención:';
            break;
        default:
            $title          = 'Impuestos adicionales:';
            $labelName      = 'Nombre del Impuesto Adicional:';
            $placeholderName= 'Impuesto Adicional';
            $labelAmount    = 'Impuesto Adicional:';
            break;
    }
@endphp

<div class="w-full col-span-1 md:col-span-2 mb-4">
    @component('components.labels.label') {!!$title!!} @endcomponent
    
    <div class="flex space-x-2">
        @component('components.buttons.button-approval')
            @slot('attributeEx') name="{{$name}}" id="no_{{$name}}" value="no" checked @endslot
            @slot('classEx'){{$name}}CheckComponent @endslot
            @slot('attributeDivEx') class="{{$name}}CheckComponent" @endslot
            No
        @endcomponent

        @component('components.buttons.button-approval')
            @slot('attributeEx') name="{{$name}}"  id="si_{{$name}}"  value="si" @endslot
            @slot('classEx'){{$name}}CheckComponent @endslot
            @slot('attributeDivEx') class="{{$name}}CheckComponent" @endslot
            Sí
        @endcomponent
    </div>
</div>
<div class="w-full md:col-span-2 col-span-1 p-4 mt-4 bg-gray-200 bg-opacity-50 hidden" id="hidde-{{$name}}-component">
    <div id="container-{{$name}}-component" class="w-full grid md:grid-cols-6 grid-cols-1 gap-x-8">
        <div class="w-full col-span-1 md:col-span-3 mb-4 px-4">
            @component('components.labels.label') {{$labelName}} @endcomponent
            @component('components.inputs.input-text')
                @slot('classEx') {{$name}}Name @endslot
                @slot('attributeEx') name="{{$name}}Name" placeholder="{{$placeholderName}}"  @endslot
            @endcomponent 
        </div>
        <div class="w-full col-span-1 md:col-span-2 mb-4 px-4">
            @component('components.labels.label') {{$labelAmount}}  @endcomponent
            @component('components.inputs.input-text')
                @slot('classEx') {{$name}}Amount @endslot
                @slot('attributeEx') name="{{$name}}Amount" placeholder="$0.00"  @endslot
            @endcomponent 
        </div>
        <div class="w-full col-span-1 mb-4 pl-4 md:pt-8 md:pb-2 flex items-center">
            @component("components.buttons.button",["variant" => "red","classEx" => 'col-span-1 '.$name.'-span-delete disabled cursor-not-allowed','attributeEx' => 'type="button" disabled'])
                Quitar
            @endcomponent
        </div>
    </div>
    <div class="{{$name}}ExtraRemove"></div>
    <div class="flex justify-start p-0 mb-6">
        @component("components.buttons.button",["variant" => "warning"])
            @slot('classEx') new{{$name}} @endslot
            @slot('attributeEx')type='button' @endslot
            @if($type == "retention")
                Nueva retención 
            @else 
                Nuevo Impuesto
            @endif
        @endcomponent
    </div>
</div>