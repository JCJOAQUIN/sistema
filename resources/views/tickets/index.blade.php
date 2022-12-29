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
		@if(count(Auth::user()->module->whereIn('id',[106,107,108,109]))>0)
			Acciones:
		@endif
		<div class="content-start items-center justify-center text-center w-full grid grid-cols-12 mb-4">
			@foreach(Auth::user()->module->where('father',105)->sortBy(function($item) {return $item->itemOrder.' '.$item->category.'-'.$item->name;}) as $key)
				@component('components.buttons.button-secondary')
					@slot('classEx')
						lg:col-span-3 md:col-span-6 col-span-12 text-black
					@endslot
					@slot('href')
						{{ url($key['url']) }}
					@endslot
					{{ $key['name'] }}
				@endcomponent
			@endforeach
		</div>
	</div>
@endsection

@section('scripts')
	<script src="{{ asset('js/jquery-ui.js') }}"></script>
	<script src="{{ asset('js/jquery.numeric.js') }}"></script>
	<script>
		$('.content').each(function()
		{
			var $this = $(this);
			var t = $this.text();
			$this.html(t.replace('&lt','<').replace('&gt', '>'));
		});
	</script>
@endsection
