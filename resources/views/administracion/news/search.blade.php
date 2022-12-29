@extends('layouts.child_module')
 
@section('data')
	@component('components.labels.title-divisor') BUSCAR NOTICIAS @endcomponent
	@component('components.forms.searchForm',["variant" => "default", "attributeEx" => "id=\"searchNews\""])
		<div class="col-span-2">
			@component('components.labels.label') Descripción: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="search" placeholder="Ingrese una descripción" value="{{ isset($search) ? $search : '' }}" data-validation="required"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Rango de Fechas: @endcomponent
			@php
				$minDate = isset($initRange)	? $initRange	: '';
				$maxDate = isset($endRange)		? $endRange		: '';
				$inputs =
				[
					[
						"input_classEx"		=> "datepicker",
						"input_attributeEx"	=> "type=\"text\" name=\"initRange\" step=\"1\" placeholder=\"Desde\" readonly data-validation=\"required\" value=\"".$minDate."\"" 
					],
					[
						"input_classEx" 	=> "datepicker",
						"input_attributeEx"	=> "type=\"text\" name=\"endRange\" step=\"1\" placeholder=\"Hasta\" readonly data-validation=\"required\" value=\"".$maxDate."\""
					]
				];
			@endphp
			@component('components.inputs.range-input',["inputs" => $inputs]) @endcomponent
		</div>
	@endcomponent
	@if(count($object)>0)
		<div class="grid sm:grid-cols-2 md:grid-cols-3 md:gap-4 md:p-8 divide-y-2 divide-black divide-x-0 sm:divide-y-0 sm:divide-x-2">
			@foreach($object as $value)
				<div class="border-black sm:border-l-2 sm:border-t-0 border-t-2 col-span-1 my-4 p-2">
					<div class="head">
						@component('components.labels.label')
							@slot('classEx')
								text-xl
								font-medium
								uppercase
							@endslot
							{!! substr(strip_tags($value['title']),0,60).'...' !!}
						@endcomponent
					</div>
					<figure class="figure">
						@if($value['media'] != "")
							<img class="media" width="250" height="250" src="{{ $value['media'] }}">
						@endif
						<figcaption class="text-xs italic mt-2">{{ $value['author'] }}</figcaption>
						<figcaption class="text-xs italic">{{ $value['published_date'] }}</figcaption>
					</figure>
					@component('components.labels.label')
						@slot('classEx')
							my-4
							text-sm
						@endslot
						{!! substr(strip_tags($value['summary']),0,400).'...' !!}
					@endcomponent
					@component('components.buttons.button',['variant' => "reset", "buttonElement" => "a"])
						@slot('attributeEx')
							target="_blank" href="{{ $value['link'] }}"
						@endslot
						@slot('classEx')
							border border-gray-500 hover:border-orange-500 hover:text-orange-500 
						@endslot
						Leer más...
					@endcomponent
				</div>
			@endforeach
		</div>
	@else
		@component('components.labels.not-found') @endcomponent
	@endif
@endsection

@section('scripts')
	<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
	<script src="{{ asset('js/jquery-ui.js') }}"></script>
	<script type="text/javascript" src="{{ asset('js/moment.min.js') }}"></script>
	<script src="{{ asset('js/daterangepicker.js') }}"></script>
	<link rel="stylesheet" type="text/css" href="{{ asset('css/daterangepicker.css') }}" />
	<script src="{{ asset('js/jquery.numeric.js') }}"></script>
	<script src="{{ asset('js/datepicker.js') }}"></script>
	<script type="text/javascript">
		$(function() 
		{
			$( ".datepicker" ).datepicker({ maxDate: 0, dateFormat: "dd-mm-yy" });
		});
	</script>
@endsection