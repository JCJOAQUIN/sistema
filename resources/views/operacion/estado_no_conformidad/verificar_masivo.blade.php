@extends('layouts.child_module')

@section('data')
	@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "ALTA MASIVA"]) @endcomponent
	@component('components.labels.not-found', ["variant" => "note"])
		@component('components.labels.label', ["label" => "Por favor verifique que su información se encuentre estructurada como en su archivo CSV."]) @endcomponent
		@component('components.labels.label', ["label" => "Sólo se muestran las primeras 10 líneas."]) @endcomponent
		@component('components.labels.label', ["label" => "Para continuar con el proceso dé clic en el botón «Continuar»"]) @endcomponent
	@endcomponent
	@if(count($csv)>0)
		@php
			$modelHead	=	[];
			foreach(array_keys(current($csv)) as $headers)
			{
				$heads			=	["value" => $headers];
				$modelHead[]	=	$heads;
			}
			$modelHead[0]["classEx"]	= "sticky inset-x-0";
			$modelHead[1]["classEx"]	= "sticky inset-x-0";
			$modelBody					= [];
			$count						= 0;
			foreach($csv as $data)
			{
				$tr			=	[];
				$countShow	=	0;
				foreach ($data as $row)
				{
					if($countShow < 2)
					{
						$td	=	[ "classEx" => "sticky inset-x-0", "content" => [ "label" => $row !="" ? $row : "---"]];	
					}
					else
					{ 
						$td	=	[ "content" => [ "label" => $row !="" ? $row : "---"]];
					}
					$tr[]	=	$td;
					$countShow++;
				}
				$count++;
				if ($count>10)
				{
					break;
				}
				$modelBody[] = $tr;
			}
		@endphp
		@component("components.tables.table", ["modelHead" => [$modelHead], "modelBody" => $modelBody]) @endcomponent
	@else
		@component('components.labels.not-found', ["variant" => "note"]) El archivo cargado no tenía ningún registro. @endcomponent
	@endif
	@if(count($csv)>10)
		@component('components.labels.not-found', ["variant" => "note"]) Sólo se muestran las primeras 10 líneas del archivo. @endcomponent
	@endif
	@if(count($csv)==0)
		<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mt-4">
			@component('components.forms.form', ["attributeEx" => "method=\"POST\" id=\"ncstatus_massive\" action=\"".route('status-nc.massive.cancel')."\""])
				@component('components.buttons.button', ["variant" => "reset", "attributeEx" => "type=\"submit\"", "label" => "Cancelar y volver"]) @endcomponent
				@component('components.inputs.input-text', ["classEx" => "", "attributeEx" => "type=\"hidden\" name=\"fileName\" value=\"".$fileName."\""]) @endcomponent
			@endcomponent
		</div>
	@else
		<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mt-4">
			@component('components.forms.form', ["attributeEx" => "method=\"POST\" id=\"ncstatus_massive\" action=\"".route('status-nc.massive.continue')."\""])
				@component('components.buttons.button', ["variant" => "primary", "attributeEx" => "type=\"submit\" name=\"send\"", "label" => "CONTINUAR"]) @endcomponent
				@component('components.inputs.input-text', ["classEx" => "", "attributeEx" => "type=\"hidden\" name=\"fileName\" value=\"".$fileName."\""]) @endcomponent
				@component('components.inputs.input-text', ["attributeEx" => "type=\"hidden\" name=\"delimiter\" value=\"".$delimiter."\""]) @endcomponent
			@endcomponent
			@component('components.forms.form', ["attributeEx" => "method=\"POST\" id=\"ncstatus_massive\" action=\"".route('status-nc.massive.cancel')."\""])
				@component('components.buttons.button', ["variant" => "reset", "attributeEx" => "type=\"submit\"", "label" => "Cancelar y volver"]) @endcomponent
				@component('components.inputs.input-text', ["attributeEx" => "type=\"hidden\" name=\"fileName\" value=\"".$fileName."\""]) @endcomponent
			@endcomponent
		</div>
	@endif
@endsection