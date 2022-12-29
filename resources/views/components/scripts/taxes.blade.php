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

$('.{{$name}}Amount',).numeric({ altDecimal: ".", decimalPlaces: 2, negative:false });
$(document).on('click','input[name="{{$name}}"]',function()
{
    if($(this).val() == 'si')
    {
        $('#hidde-{{$name}}-component').stop(true,true).slideDown().show();
    }
    else
    {
        {{$name}}CleanComponent();
        $('#hidde-{{$name}}-component').stop(true,true).slideUp().hide();
        $(".{{$name}}Name, .{{$name}}Amount").removeClass('error');
    }
    @if(isset($function))
        {{$function}}();
    @else 
        total_cal();
    @endif 
}).on('click','.new{{$name}}',function()
{
    @php
        $newDocImAdd=""; 
        $newDocImAdd .= html_entity_decode( preg_replace("/(\r)*(\n)*/","",'<div class="w-full grid md:grid-cols-6 grid-cols-1 gap-x-8 tr "> <div class="w-full col-span-1 md:col-span-3 mb-4 px-4">'));
        $newDocImAdd .= html_entity_decode( preg_replace("/(\r)*(\n)*/","",view("components.labels.label",["slot" => $labelName])));
        $newDocImAdd .= html_entity_decode( preg_replace("/(\r)*(\n)*/","",view("components.inputs.input-text",["classEx" => $name.'Name','attributeEx' => 'name="'.$name.'Name" placeholder="'.$placeholderName.'"'])));
        
        $newDocImAdd .= html_entity_decode( preg_replace("/(\r)*(\n)*/","",'</div> <div class="w-full col-span-1 md:col-span-2 mb-4 px-4">'));
        $newDocImAdd .= html_entity_decode( preg_replace("/(\r)*(\n)*/","",view("components.labels.label",["slot" => $labelAmount])));
        $newDocImAdd .= html_entity_decode( preg_replace("/(\r)*(\n)*/","",view("components.inputs.input-text",["classEx" => $name.'Amount','attributeEx' => 'name="'.$name.'Amount" placeholder="$0.00"'])));
        $newDocImAdd .= html_entity_decode( preg_replace("/(\r)*(\n)*/","",'</div><div class="w-full col-span-1 justify-start mb-4 pl-4 md:pt-8 md:pb-2 flex items-center"> <div class="">'));
        
        $newDocImAdd .= html_entity_decode( preg_replace("/(\r)*(\n)*/","",view("components.buttons.button",['slot'=>'Quitar',"variant" => "red","classEx" => 'col-span-1 '.$name.'-span-delete','attributeEx' => 'type="button"'])));
        $newDocImAdd .= html_entity_decode( preg_replace("/(\r)*(\n)*/","",'</div></div></div>'));
    @endphp

    newI = $('{!!preg_replace("/(\r)*(\n)*/", "", $newDocImAdd)!!}');
    
    $('.{{$name}}ExtraRemove').append(newI);
    $('[name="{{$name}}Amount"]').on("contextmenu",function(e)
    {
        return false;
    });
    $('.{{$name}}Amount',).numeric({ altDecimal: ".", decimalPlaces: 2, negative:false });
}).on('click','.{{$name}}-span-delete',function(){
    $(this).parents('.tr').remove();
    @if(isset($function))
        {{$function}}();
    @else 
        total_cal();
    @endif 
});
function {{$name}}CleanComponent(){ 
    $('input[name="{{$name}}"]').prop('checked',false);
    $('#no_{{$name}}').prop('checked',true);
    $('#container-{{$name}}-component').find('span').remove();
    $('.{{$name}}Name').val("");
    $('.{{$name}}Amount').val("");
    $('.{{$name}}ExtraRemove').html('');
    $('#hidde-{{$name}}-component').stop(true,true).slideUp().hide();
}