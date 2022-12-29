<div class="{{($bool ? 'grid grid-cols-1 md:grid-cols-3' : 'grid  gap-y-2 py-2')}} ul">
	@foreach ($modules as $key => $value)
		<div class="col-span-1 {{!$bool ? 'pl-8' : ''}} li">
			@if($value->hybrid == 1)
				<div class="font-semibold flex">
					@component("components.inputs.switch",["variant" => "noContainer", "classExContainer" => "hidden"]) @slot("attributeEx") type="checkbox" id="admin_{{$value->id}}" value="{{$value->id}}" @if(in_array($value->id, $accessMod)) checked @endif @endslot {{ $value->name }} @endcomponent
				</div>
			@elseif($value->permissionRequire == 1 && $value->fatherModule->hybrid == 1)
				<div class="module_buttons p-0 flex items-center ml-10">
					@component("components.inputs.switch", ["classLabel" => "w-auto", "variant" => "noContainer", "classEx" => "newmodules"]) 
						@slot("attributeEx") 
							name="module[]" 
							type="checkbox" 
							value="{{ $value->id }}" 
							id="module_{{ $value->id }}" 
							@if(in_array($value->id, $accessMod)) checked @endif 
							data-father="admin_{{$value->father}}" 
							data-permission-type="{{$value->permission_type}}" 
						@endslot
						{{ $value->name }}
					@endcomponent
					@component("components.buttons.button", ["variant" => "success", "label" => "<span class=\"icon-pencil\"></span>"]) 
						@slot("classEx") 
							@if(!in_array($value->id, $accessMod)) hidden @endif
							follow-btn
							editModule
							w-10
						@endslot 
						@slot("attributeEx") 
							data-permission-type="{{ $value->permission_type }}" 
							type="button" 
							data-id="{{$value->id}}" 
						@endslot 
					@endcomponent
				</div>
			@else
				@php
					$classEx = "";
					if($value->father == '') 
					{
						$classEx = "font-semibold";
					}
					else if($value->fatherModule()->exists() && $value->childrenModule()->count() > 1)
					{
						$classEx = "ml-6";
					}
					else
					{
						$classEx = "ml-10";
					}
				@endphp
				@component("components.inputs.switch", ["variant" => "noContainer", "label" => $value->name]) @slot("attributeEx") name="moduleCheck[]" id="module_{{$value->id}}" type="checkbox" value="{{$value->id}}" @if(in_array($value->id, $accessMod)) checked @endif @endslot {{ ucwords($value->name) }} @endcomponent
			@endif
			{!! App\Http\Controllers\ConfiguracionUsuarioController::build_modules($value->id,$accessMod,false) !!}
		</div>
	@endforeach
</div>