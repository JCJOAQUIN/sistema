@extends('layouts.child_module')

@section('data')
    @isset($request)
        @component("components.forms.form", ["attributeEx" => "action=\"".route('machinery.update', $request->id)."\" method=\"post\" id=\"container-alta\"", "methodEx" => "put"])
    @else
        @component("components.forms.form", ["attributeEx" => "action=\"".route('machinery.store')."\" method=\"POST\" id=\"container-alta\""])
    @endisset
        @component('components.labels.title-divisor') DATOS DE MAQUINARIA @endcomponent
        @component("components.labels.subtitle") Para {{ (isset($request)) ? "editar la maquinaria" : "agregar una maquinaria nueva" }} es necesario colocar el siguiente campo: @endcomponent
        @component("components.containers.container-form")
            <div class="col-span-4 md:col-start-2 md:col-span-2 md:col-end-4">
                 @component("components.labels.label", ["label" => "Registro maquinaria:"]) @endcomponent

                 @component('components.inputs.input-text')
                 	@slot('attributeEx')
                    	placeholder = "Ingrese el registro de maquinaria" 
                        type  = "text" 
                        value = "{{isset($request) ? $request->name : '' }}"
                        name  = "name"
                        data-validation = "required"
                     @endslot
                 @endcomponent
            </div>
        @endcomponent

        @isset($request)
            <div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mt-8">
                @component('components.buttons.button', ["variant"=>"primary"])
                    @slot('attributeEx')
                        type = "submit"
                    @endslot
                    Actualizar
                @endcomponent
                @component('components.buttons.button', ["variant"=>"reset", "buttonElement"=>"a"])
                    @slot('attributeEx')
                        @if(isset($option_id)) 
                            href="{{ url(getUrlRedirect($option_id)) }}" 
                        @else 
                            href="{{ url(getUrlRedirect($child_id)) }}"
                        @endif 
                    @endslot
                    Regresar
                @endcomponent
            </div>
        @else 
            <div class="text-center">
                @component('components.buttons.button', ["variant"=>"primary"])
                    @slot('attributeEx')
                        type = "submit"
                    @endslot
                    Registrar
                @endcomponent
            </div>
        @endisset
    @endcomponent
@endsection
