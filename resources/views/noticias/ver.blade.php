@extends('layouts.layout')
@section('title', $title)
@section('content')
	<div class="w-full">
		<div class="row">
			<div class="leftcolumn">
				<div class="card-new">
					@component('components.labels.title-divisor') {{ $new->title }} @endcomponent
					@component('components.labels.label', ["classEx" => "font-medium mb-2", "label" => "Fecha: ".(Carbon\Carbon::createFromFormat('Y-m-d',$new->date)->format('d-m-Y'))]) @endcomponent
					@component('components.labels.label', ["classEx"	=>	"content", "label" => $new->details]) @endcomponent
					<div class="text-center mt-8">
						@php
							$info = new SplFileInfo(asset('images/news').'/'.$new->path);
						@endphp
						@if ($info->getExtension() != 'jpg' && $info->getExtension() != 'png' && $info->getExtension() != 'jpeg')
							@if (isset($new->path) && $new->path!='')
								<div id="show-file">
									@component('components.labels.label',	["classEx"	=>	"inline-block",	"label"		=>	"Archivo adjunto:"]) @endcomponent
									@component('components.buttons.button',	["variant"	=>	"secondary",	"classEx"	=>	"inline-block",	"attributeEx"	=>	"href=\"".asset('images/news').'/'.$new->path."\"",	"buttonElement" => "a", "label" => "Archivo"]) @endcomponent
								</div>
							@endif
						@elseif ($info->getExtension() == 'jpg' || $info->getExtension() == 'png' || $info->getExtension() == 'jpeg')
							@if (isset($new->path) && $new->path!='')
								<div id="show-image">
									@component('components.labels.label', ["label" => "Imagen adjunta:"]) @endcomponent
									<div class="w-full flex justify-center mt-4">
										<img class="w-48" src="{{asset('images/news').'/'.$new->path}}">
									</div>
								</div>
								@php
									$validateImg = 'mime size';
								@endphp
							@else
								@php
									$validateImg = 'required mime size';
								@endphp
							@endif
						@endif
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection

@section('scripts')
	<script>
		$('.content').each(function(){
		var $this = $(this);
		var t = $this.text();
		$this.html(t.replace('&lt','<').replace('&gt', '>'));
		});
	</script>
@endsection
