@extends('layouts.layout')
@section('title', $title)
@section('content')
	<div class="lg:my-6 mx-6">
		<div class="flex w-full justify-center uppercase items-center content-center flex-wrap text-3xl font-medium pt-3 pb-1">
			<h1 class="text-gray-800">{{ $title }}</h1>
		</div>
		<center>
			<i class="text-gray-400">{{ $details }}</i>
		</center>
		<br>
		<hr class="bg-amber-500 h-px border-0">
		<br>
		@if(Auth::user()->module->where('father',$id)->where('category','!=',NULL)->count()>0)
			@php
				$categoryModule	= '';
			@endphp
			<div class="content-start items-center justify-start text-left w-full grid xl:grid-cols-4 lg:grid-cols-3 sm:grid-cols-2 grid-cols-1 gap-x-8 lg:gap-x-16 pr-6">
				@foreach(Auth::user()->module->where('father',$id)->where('category','!=',NULL)->sortBy(function($item) {return $item->itemOrder.' '.$item->category.'-'.$item->name;}) as $key)
					@if($key['category'] != '' && $categoryModule != $key['category'])
						@php
							$categoryModule	= $key['category'];
						@endphp
						<h2 class="xl:col-span-4 lg:col-span-3 sm:col-span-2 col-span-1 block text-2xl font-semibold leading-loose py-4 px-5 justify-start w-full">{{$categoryModule}}</h2>
						<hr class="xl:col-span-4 lg:col-span-3 sm:col-span-2 col-span-1 bg-white mr-auto ml-4 w-11/12">
					@endif
					@component("components.buttons.button-primary")
						@slot('classEx')
							load-actioner
						@endslot
						@slot('href')
						{{ url($key['url']) }}
						@endslot
						{{ $key['name'] }}
					@endcomponent
				@endforeach
			</div>
		@endif
		@if(Auth::user()->module->where('father',$id)->where('category',NULL)->count()>0)
			<div class="content-center items-center bg-white justify-center text-center w-full grid xl:grid-cols-4 lg:grid-cols-3 sm:grid-cols-2 grid-cols-1 gap-x-8 lg:gap-x-16 pr-6">
				@foreach(Auth::user()->module->where('father',$id)->where('category',NULL)->sortBy(function($item) {return $item->itemOrder.' '.$item->name;}) as $module)
					@component("components.buttons.button-primary")
						@slot('classEx')
							load-actioner
						@endslot
						@slot('href')
							{{ url($module['url']) }}
						@endslot
						{{ $module['name'] }}
					@endcomponent	
				@endforeach
			</div>
		@endif
	</div>
@endsection
