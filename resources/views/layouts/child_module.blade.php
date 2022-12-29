@extends('layouts.layout')

@section('title', $title)

@section('content')
	@if((isset($option_id) && $child_id !='') || ($id != '' && $child_id != $id))
		@component("components.buttons.button-back")
			@slot('classEx')
				load-actioner
			@endslot
			@slot('href')
				@if(isset($option_id))
					{{ url(App\Module::find($child_id)->url) }}
				@else
					{{ url(App\Module::find($id)->url) }}
				@endif
			@endslot
		@endcomponent
	@endif
	<div class="w-full">
		@component("components.labels.title-config")
			{{ $title }}
		@endcomponent
		<div class="text-center mb-4">
			<i style="color: #B1B1B1;">{{ $details }}</i>
		</div>
		<hr class="bg-amber-500 h-px border-0 mb-4">
		@component("components.buttons.tutorial") 
			@slot("child_id") {{isset($child_id) ? $child_id : null}} @endslot
			@slot("option_id") {{isset($option_id) ? $option_id : null}} @endslot 
		@endcomponent
		@if(count(Auth::user()->module->where('father',$child_id)) > 0)
			<h4>Acciones: </h4>
		@endif
		@if(Auth::user()->module->where('father',$child_id)->where('category','!=',NULL)->count()>0)
			@php
				$categoryModule	= '';
			@endphp
			<div class="content-start items-center flex flex-wrap justify-center text-center w-full mb-4">
				@foreach(Auth::user()->module->where('father',$child_id)->where('category','!=',NULL)->sortBy(function($item) {return $item->itemOrder.' '.$item->category.'-'.$item->name;}) as $categItem)
					@if($categItem['category'] != '' && $categoryModule != $categItem['category'])
						@php
							$categoryModule	= $categItem['category'];
						@endphp
						<h2 class="block text-lg font-semibold leading-loose pt-4 pr-12 pb-12 pl-4 text-left w-full">{{$categoryModule}}</h2>
						<hr class="bg-white mr-auto ml-4 w-11/12">
					@endif
					@component('components.buttons.button-secondary')
						@if(isset($option_id) && $option_id==$categItem['id'])
							@slot('classEx')
								load-actioner
								@if(isset($option_id) && $option_id==$categItem['id'])
									bg-orange-600 border-none text-white shadow-md
								@endif
								active:bg-orange-600 active:border-none active:text-white active:shadow-md
							@endslot
						@endif
						@slot('href')
							{{ url($categItem['url'].'#moduleContent') }}
						@endslot
						{{ $categItem['name'] }}
					@endcomponent
				@endforeach
			</div>
		@endif
		@if(Auth::user()->module->where('father',$child_id)->where('category',NULL)->count()>0)
			<div class="content-start items-center justify-center text-center w-full grid grid-cols-12 mb-4">
				@php
					$optionsMovements = count(Auth::user()->module->where('father',$child_id)->where('category',NULL)->whereIn('id',[171,172,173,174,175]));
				@endphp
				@if ($optionsMovements > 1)
					@component('components.buttons.button-secondary')
						@slot('classEx')
							load-actioner
							@if(isset($option_id) && $option_id >=171 && $option_id <=175)
								bg-orange-600 border-none text-white shadow-md
							@endif
							lg:col-span-3 md:col-span-6 col-span-12
						@endslot
						@slot('href')
							@if (isset($option_id) && $option_id >=171 && $option_id <=175)
								{{ route('movements-accounts.index') }}
							@else
								@if (in_array(171,Auth::user()->module->where('father',$child_id)->where('category',NULL)->whereIn('id',[171,172,173,174,175])->pluck('id')->toArray()))
									{{ route('movements-accounts.adjustment') }}
								@elseif (in_array(172,Auth::user()->module->where('father',$child_id)->where('category',NULL)->whereIn('id',[171,172,173,174,175])->pluck('id')->toArray()))
									{{ route('movements-accounts.loan') }}
								@elseif (in_array(173,Auth::user()->module->where('father',$child_id)->where('category',NULL)->whereIn('id',[171,172,173,174,175])->pluck('id')->toArray()))
									{{ route('movements-accounts.purchase') }}
								@elseif (in_array(174,Auth::user()->module->where('father',$child_id)->where('category',NULL)->whereIn('id',[171,172,173,174,175])->pluck('id')->toArray()))
									{{ route('movements-accounts.groups') }}
								@elseif (in_array(175,Auth::user()->module->where('father',$child_id)->where('category',NULL)->whereIn('id',[171,172,173,174,175])->pluck('id')->toArray()))
									{{ route('movements-accounts.movements') }}
								@endif
							@endif
						@endslot
						Alta
					@endcomponent
				@else
					@foreach (Auth::user()->module->where('father',$child_id)->where('category',NULL)->whereIn('id',[171,172,173,174,175]) as $idOptions)
						@component('components.buttons.button-secondary')
							@slot('classEx')
								load-actioner
								@if(isset($option_id) && $option_id==$idOptions['id'])
									bg-orange-600 border-none text-white shadow-md
								@endif
								lg:col-span-3 md:col-span-6 col-span-12
							@endslot
							@slot('href'){{ url($idOptions['url']) }}@endslot
							{{ $idOptions['name'] }}
						@endcomponent
					@endforeach
				@endif
				@foreach(Auth::user()->module->where('father',$child_id)->where('category',NULL)->whereNotIn('id',[171,172,173,174,175])->sortBy(function($item) {return $item->itemOrder.' '.$item->name;}) as $key)
					@component('components.buttons.button-secondary')
						@slot('classEx')
							load-actioner
							@if(isset($option_id) && $option_id==$key['id'])
								bg-orange-600 border-none text-white shadow-md
							@endif
							lg:col-span-3 md:col-span-6 col-span-12
						@endslot
						@slot('href'){{ url($key['url']) }}@endslot
						{{ $key['name'] }}
					@endcomponent
				@endforeach
			</div>
			<div class="content-start items-center justify-center text-center w-full grid grid-cols-12 mb-4">
				@if (isset($option_id) && ($option_id >=171 && $option_id <=175) && $optionsMovements > 1)
					@foreach (Auth::user()->module->where('father',$child_id)->where('category',NULL)->whereIn('id',[171,172,173,174,175])->sortBy(function($item) {return $item->itemOrder.' '.$item->name;}) as $idOptions)
						@component('components.buttons.button-secondary')
							@slot('classEx')
								load-actioner
								@if(isset($option_id) && $option_id==$idOptions['id'])
									bg-orange-600 border-none text-white shadow-md
								@endif
								lg:col-span-3 md:col-span-6 col-span-12
							@endslot
							@slot('href'){{ url($idOptions['url']) }}@endslot
							{{ $idOptions['name'] }}
						@endcomponent
					@endforeach
				@endif
			</div>
		@endif
		<div class="data-container" id="moduleContent">
			@yield('header')
			@yield('data')
			@yield('pay-form')
		</div>
	</div>
	@php
		$day   = date('j');
		$month = '';
		switch (date('n'))
		{
			case 1:
				$month = 'enero';
				break;
			case 2:
				$month = 'febrero';
				break;
			case 3:
				$month = 'marzo';
				break;
			case 4:
				$month = 'abril';
				break;
			case 5:
				$month = 'mayo';
				break;
			case 6:
				$month = 'junio';
				break;
			case 7:
				$month = 'julio';
				break;
			case 8:
				$month = 'agosto';
				break;
			case 9:
				$month = 'septiembre';
				break;
			case 10:
				$month = 'octubre';
				break;
			case 11:
				$month = 'noviembre';
				break;
			case 12:
				$month = 'diciembre';
				break;
		}
		if(isset($_COOKIE['follow']))
		{
			$following	= json_decode(base64_decode($_COOKIE['follow']),true);
		}
		$cookieArray		= array();
		$tempArray			= array();
		$tempArray['name']	= $title;
		$tempArray['id']	= $child_id;
		$tempArray['date']	= $day.' de '.$month;
		$cookieArray[]		= $tempArray;
		if(isset($following))
		{
			foreach ($following as $key => $value)
			{
				if($value['id'] != $child_id)
				{
					$cookieArray[] = $value;
				}
				if(count($cookieArray)>4)
				{
					break;
				}
			}
		}
		$cookie	= base64_encode(json_encode($cookieArray));
		setcookie('follow',$cookie,time()+60*60*24*365,'/');
	@endphp
@endsection