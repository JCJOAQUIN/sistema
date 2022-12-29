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
		@component('components.forms.searchForm', ["attributeEx" => "id=\"formsearch\" action=\"".route('news.search')."\"", "values" => $values, "hidden" => $hidden])
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
		@if (count($news) > 0)
			@php
				$fecha		=	date('Y-m-d');
				$nuevafecha	=	strtotime('-7 day',strtotime($fecha)) ;
				$nuevafecha	=	date('Y-m-d',$nuevafecha);
				$modelHead	=	[];
				$body		=	[];
				$modelBody	=	[];
				$modelHead	=
				[
					[
						["value"	=>	"ID"],
						["value"	=>	"Título"],
						["value"	=>	"Descripción"],
						["value"	=>	"Acciones"]
					]
				];
				foreach($news as $new)
				{
					$body	=
					[
						[
							"content"	=>	["label"	=>	$new->idnews!="" ? $new->idnews : "---"]
						],
						[
							"content"	=>	["label"	=>	$new->title!="" ? $new->title : "---"]
						],
						[
							"content"	=>	["label"	=>	$new->details!="" ? substr(strip_tags($new->details),0,150) : "---"]
						],
						[
							"content"	=>	
							[
								["kind" => "components.buttons.button",	"variant"	=>	"success",	"buttonElement"	=>	"a",	"attributeEx"	=>	"title=\"Editar Noticia\" href=\"".route('news.edit',$new->idnews)."\"",		"label"	=>	"<span class='icon-pencil'></span>"],
								["kind" => "components.buttons.button",	"variant"	=>	"red",		"buttonElement"	=>	"a",	"attributeEx"	=>	"title=\"Eliminar Noticia\" href=\"".route('news.delete',$new->idnews)."\"",	"label"	=>	"<span class='icon-bin'></span>"]
							]
						],
					];
					$modelBody[]	=	$body;
				}
			@endphp
			@component('components.tables.table', ["modelHead" => $modelHead, "modelBody" => $modelBody])
				@slot('classEx')
					mt-4
				@endslot
			@endcomponent
			 {{ $news->appends($_GET)->links() }}
		@else
			@component('components.labels.not-found') @endcomponent
		@endif
	</div>
@endsection