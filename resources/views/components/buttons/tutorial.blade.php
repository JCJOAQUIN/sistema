@php
    $child_module = isset($child_id) ? App\Module::find($child_id) : null;
    $option_module = isset($option_id) ? App\Module::find($option_id) : null;
@endphp
@if (($child_module && $child_module->tutorials()->count() > 0) || ($option_module && $option_module->tutorials()->count() > 0))
    <div class="mb-2">
        <label>Video Tutoriales</label>
    </div>
    <div class="flex flex-wrap mb-4">
        @if ($child_module)
            @foreach ($child_module->tutorials as $tuto)
                @component("components.buttons.button",["variant" => "warning"])
                    @slot('attributeEx')
                        type="button" 
                        data-toggle="modal" 
                        data-target="#dataUrlTutorial" 
                        data-url-tutorial="{{ $tuto->url }}"
                    @endslot
                    @slot('classEx')
                        flex flex-row justify-center items-center m-1
                    @endslot
                    <span class="icon-play_video text-xl mr-2"></span>
                    {{ $tuto->name }}
                @endcomponent
            @endforeach
        @endif
        @if ($option_module)
            @foreach ($option_module->tutorials as $tuto)
                @component("components.buttons.button",["variant" => "warning"])
                    @slot('attributeEx')
                        type="button" 
                        data-toggle="modal" 
                        data-target="#dataUrlTutorial" 
                        data-url-tutorial="{{ $tuto->url }}"
                    @endslot
                    @slot('classEx')
                        flex flex-row justify-center items-center m-1
                    @endslot
                    <span class="icon-play_video text-xl mr-2"></span>
                    {{ $tuto->name }}
                @endcomponent
            @endforeach
        @endif
    </div>
@endif