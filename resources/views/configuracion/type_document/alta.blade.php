@extends('layouts.child_module')

@section('data')
    @if(isset($documents))
        @component("components.forms.form", ["attributeEx" => "action=\"".route('type.document.update', $documents->id)."\" method=\"POST\" id=\"container-alta\""])
        @slot("methodEx") PUT @endslot
    @else
        @component("components.forms.form", ["attributeEx" => "action=\"".route('type.document.store')."\" method=\"POST\" id=\"container-alta\""]) 
    @endif
        @component('components.labels.title-divisor') DATOS DEL DOCUMENTO @endcomponent
        @component("components.labels.subtitle") Para {{ (isset($documents)) ? "editar el tipo" : "agregar un tipo nuevo" }} es necesario colocar los siguientes campos: @endcomponent
        @component("components.containers.container-form")
            <div class="col-span-2">
                @component("components.labels.label") Siglas: @endcomponent
                @component("components.inputs.input-text")
                    @slot("classEx") nameDocument @endslot
                    @slot("attributeEx")
                        type="text" 
                        name="nameDocument"
                        data-validation-url="{{ route('type.document.validateName') }}" 
                        @if(isset($documents))
                            value="{{ $documents->name }}"
                            data-validation-req-params="{{ json_encode(array('oldName' => $documents->id)) }}" 
                        @endif
                        data-validation="server"
                        placeholder="Ingrese las siglas" 
                    @endslot
                @endcomponent
            </div>
            <div class="col-span-2">
                @component("components.labels.label") Descripción: @endcomponent
                @component("components.inputs.text-area")
                    @slot("classEx") descriptionDocument @endslot
                    @slot("attributeEx")
                        data-validation = "required"
                        name = "descriptionDocument"
                        id = "description"
                        placeholder = "Ingrese la descripción"
                    @endslot
                    @if(isset($documents))
                        {{ $documents->description }}
                    @endif
                @endcomponent
            </div>
        @endcomponent
        <div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mt-8">
            @component('components.buttons.button', ["variant"=>"primary"])
                @slot('attributeEx')
                    type="submit"
                    name="enviar"
                @endslot
                @slot('classEx')
                    send
                @endslot
                @if(isset($documents)) Actualizar @else Registrar @endif
            @endcomponent
            @if(isset($documents)) 
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
            @else
                @component("components.buttons.button", ["classEx" => "btn-delete-form", "variant" => "reset"]) 
                    @slot("attributeEx")
                        type = "reset" 
                        name = "borrar"
                    @endslot
                    Borrar campos
                @endcomponent
            @endif
        </div>
    @endcomponent
@endsection

@section('scripts')
    <script type="text/javascript">
        function validate()
        {
            $.validate(
            {
                form        : '#container-alta',
                modules		: 'security',
                onError		: function($form)
                {
                    swal('', '{{ Lang::get("messages.form_error") }}', 'error');
                },
                onSuccess : function($form)
                {
                    $('[name="nameDocument"]').removeClass('error');
                    $('[name="descriptionDocument"]').removeClass('error');

                    if($('input[name="nameDocument"]').hasClass('error'))
                    {
                        swal('', 'Por favor ingrese siglas correctas.', 'error');
                        return false;
                    }
                    else
                    {
                        return true;
                    }
                }
            });
        }
        $(document).ready(function()
        {
            validate();
        });
    </script>
@endsection
