@extends('layouts.layout')
@section('title', $title)
@section('content')
	<div class="w-full">
		@component("components.labels.title-config", ["classEx" => "mt-6"])
			{{ $title }}
		@endcomponent
		<div class="text-center text-gray-400 mb-6 italic">
			{{ $details }}
		</div>
		<hr class="bg-amber-500 h-px border-0 mb-6">
		@component("components.buttons.tutorial") 
			@slot("child_id") {{isset($child_id) ? $child_id : null}} @endslot
			@slot("option_id") {{isset($option_id) ? $option_id : null}} @endslot 
		@endcomponent
		@if(count(Auth::user()->module->whereIn('id',[81,82,104]))>0)
			Acciones:
		@endif
		<div class="content-start items-center justify-center text-center w-full grid grid-cols-12 mb-4">
			@foreach(Auth::user()->module->where('father',80) as $key)
				@component('components.buttons.button-secondary')
					@slot('classEx')
						lg:col-span-3 md:col-span-6 col-span-12
						@if($key['name'] == "Ver")
							@if(isset($option_id) && $option_id==$key['id'])
								hidden
							@else
								hidden
							@endif
						@else
							@if(isset($option_id) && $option_id==$key['id'])
								bg-orange-600 text-white shadow-md
							@else
								text-black
							@endif
						@endif
					@endslot
					@slot('href')
						{{ url($key['url']) }}
					@endslot
					{{ $key['name'] }}
				@endcomponent
			@endforeach
		</div>
		@php
			$values	=
			[
				'minDate'	=>	isset($mindate) ? date('d-m-Y',strtotime($mindate)) : '',
				'maxDate'	=>	isset($maxdate) ? date('d-m-Y',strtotime($maxdate)) : '',
			];
			$hidden	=	['enterprise','name','folio'];
		@endphp
		@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "BUSCAR NOTICIAS"]) @endcomponent
		@component('components.forms.searchForm', ["attributeEx" => "id=\"formsearch\" action=\"".route('news.history')."\"", "values" => $values, "hidden" => $hidden])
			@slot('contentEx')
				<div class="col-span-2">
					@component('components.labels.label', ["label" => "Título:"]) @endcomponent
					@component('components.inputs.input-text', ["attributeEx" => "type=\"text\" name=\"name\" id=\"input-search\" placeholder=\"Ingrese un título\" value=\"".(isset($name) ? $name : '')."\""]) @endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label', ["label" => "Descripción de noticia:"]) @endcomponent
					@component('components.inputs.input-text', ["attributeEx" => "type=\"text\" name=\"description\" id=\"input-search\" placeholder=\"Ingrese una descripción\" value=\"".(isset($description) ? $description : '')."\""]) @endcomponent
				</div>
			@endslot
		@endcomponent
		<div class="mt-12">
			@if (count($news) > 0)
				<div class="row">
					<div class="leftcolumn">
						@php
							$fecha 		= date('Y-m-d');
							$nuevafecha = strtotime('-7 day',strtotime($fecha)) ;
							$nuevafecha = date('Y-m-d',$nuevafecha);
						@endphp
						@foreach ($news as $new)
							<div class="card-new">
								@component('components.labels.title-divisor', ["label"	=>	$new->title]) @endcomponent
								@component('components.labels.label', ["classEx"	=>	"font-medium mb-2",	"label"	=>	"Fecha: ".date('d-m-Y',strtotime($new->date))]) @endcomponent
								@component('components.labels.label', ["classEx"	=>	"content",		"label"	=>	$new->details]) @endcomponent
								<div class="text-center mt-8">
									@php
										$info = new SplFileInfo(asset('images/news').'/'.$new->path);
									@endphp
									@if ($info->getExtension() != 'jpg' && $info->getExtension() != 'png' && $info->getExtension() != 'jpeg')
										@if (isset($new->path) && $new->path!='')
											<div id="show-file">
												@component('components.labels.label', ["classEx"	=>	"inline-block", "label" => "Archivo adjunto:"]) @endcomponent
												@component('components.buttons.button', ["variant" => "secondary", "classEx"	=>	"inline-block", "attributeEx" => "href=\"".asset('images/news').'/'.$new->path."\"", "buttonElement" => "a", "label" => "Archivo"]) @endcomponent
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
						@endforeach
					</div>
					{{ $news->appends($_GET)->links() }}
				</div>
			@else
				@component('components.labels.not-found') @endcomponent
			@endif
		</div>
	</div>
@endsection

@section('scripts')
	<script>
		$('.content').each(function()
		{
			var $this = $(this);
			var t = $this.text();
			$this.html(t.replace('&lt','<').replace('&gt', '>'));
		});
	</script>
@endsection
