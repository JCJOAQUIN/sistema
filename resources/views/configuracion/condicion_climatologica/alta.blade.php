@extends('layouts.child_module')

@section('data')
    @if(isset($request))
        @component("components.forms.form", ["attributeEx" => "action=\"".route('weather-condition.update', $request->id)."\" method=\"POST\" id=\"container-alta\"", "methodEx" => "PUT"])
    @else
        @component("components.forms.form", ["attributeEx" => "action=\"".route('weather-condition.store')."\" method=\"POST\" id=\"container-alta\""])
    @endisset
            @component('components.labels.title-divisor') DATOS DE CONDICIÓN CLIMATOLÓGICA @endcomponent
            @component("components.labels.subtitle") Para {{ (isset($request)) ? "editar la condición" : "agregar una condición nueva" }} es necesario colocar el siguiente campo: @endcomponent
            @component("components.containers.container-form")
                <div class="col-span-4 md:col-start-2 md:col-span-2 md:col-end-4">
                    @component("components.labels.label", ["label" => "Condición:"]) @endcomponent
                    @component('components.inputs.input-text')
                        @slot('attributeEx')
                            placeholder = "Ingrese la condición" 
                            type  = "text" 
                            value = "{{isset($request) ? $request->name : '' }}"
                            name  = "name"
                            data-validation = "required"
                        @endslot
                    @endcomponent
                </div>
            @endcomponent
            <div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mt-8">
                @component('components.buttons.button', ["variant"=>"primary"])
                    @slot('attributeEx')
                        type = "submit"
                    @endslot
                    @isset($request) ACTUALIZAR @else REGISTRAR @endisset
                @endcomponent
                @isset($request)
                    @component('components.buttons.button', ["variant"=>"reset", "buttonElement"=>"a"])
                        @slot('attributeEx')
                            @if(isset($option_id)) 
                                href="{{ url(getUrlRedirect($option_id)) }}" 
                            @else 
                                href="{{ url(getUrlRedirect($child_id)) }}" 
                            @endif 
                        @endslot
                        @slot('classEx')
                            load-actioner
                        @endslot
                        Regresar
                    @endcomponent
                @endisset
            </div>
        @endcomponent
@endsection
