@php
	if(isset($modelHead))
	{
		$theads = collect($modelHead);
	} 
	if(isset($modelBody))
	{
		$tbody = collect($modelBody);
	}
	!isset($variant) ? $v = "hidden" : $v = "";
@endphp
@if(!isset($noHead))
<div class="relative md:overflow-x-auto min-w-full max-w-full text-xs @if(isset($classExContainer)) {{ $classExContainer }} @endif">
	@if(isset($withTitle) && isset($noHead))
		<div class="w-full text-center text-xl text-white bg-orange-500">
			<strong>{!!$title!!}</strong>
		</div>
	@endif
	<table class="min-w-full border-collapse md:border border-orange-200 @isset($classEx ) {{ $classEx  }} @endisset" @isset($attributeEx ) {!!$attributeEx!!} @endisset>
		@if(isset($modelHead))
			<thead class="@if($v == 'hidden') hidden @endif text-white md:table-header-group  
			@isset($classExHead ) {{ $classExHead  }} @endisset" @isset($attributeExHead ) {!!$attributeExHead!!} @endisset>
				<tr>
					@foreach($theads as $key => $th)
						<th class="p-3 bg-orange-500 col-to-hide md:border-r md:border-l border-orange-200 @isset($th['classEx']) {{$th['classEx']}} @endisset" @isset($th['attributeEx']) {!!$th['attributeEx']!!} @endisset>
							<div class="flex items-center justify-center w-full h-full">
								@if(!is_array($th) || isset($th["label"]))
									@isset($th["label"]) {!!$th["label"]!!} @else {!!$th!!} @endisset
								@else
									@if(collect($th)->has('content'))
										@foreach($th["content"] as $component)
											@if(is_array($component))
												@if(collect($component)->has('label') && !collect($component)->has('kind'))
													{!!$component['label']!!}
												@else
													@component($component['kind'], slotsItem($component)) @endcomponent
												@endif
											@endif
										@endforeach
									@endif
								@endif
							</div>
						</th>
					@endforeach
				</tr>
			</thead>
		@endif
		<tbody class="block md:table-row-group @isset($classExBody) {{$classExBody }} @endisset always-visible-tr" 
			@isset($attributeExBody) {!!$attributeExBody!!} @endisset >
@endif
	@isset($modelBody)
		@foreach($tbody as $trNumber => $tr)
			@php
				$defaultClasses = "grid grid-cols-12 md:table-row tr ";
				if(isset($tr['classEx']))
				{
					$defaultClasses = $defaultClasses." ".$tr['classEx'];
				}
				$collection = collect(explode(' ',$defaultClasses))->unique()->values()->all();
				$classString = implode(' ', $collection);
			@endphp
			<tr class="grid grid-cols-12 md:table-row {!!$classString!!}" @isset($tr['attributeEx']) {!!$tr['attributeEx']!!} @endisset>
				@foreach($tr as $key => $td)
					@if(is_array($td))
						@if($key != $theads->count()-1)
							<td class="p-3 @if(!isset($noHead))
												@if($key == 0) 
													bg-orange-50 @if(($trNumber % 2) == 0) md:bg-white @else md:bg-orange-100 @endif main-col-always 
												@else
													@if(($trNumber % 2) == 0) bg-white @else bg-orange-100 @endif
												@endif
											@endif
											@isset($td['classEx'])
												{{$td['classEx']}}
											@endisset
											col-span-6 md:hidden text-left align-middle justify-start">
								@component('components.labels.label') 
									@slot('classEx')
										font-bold
										md:hidden
										justify-start
									@endslot
									@if(is_array($theads[$key]))
										@if(collect($theads[$key])->has('label') && !collect($theads[$key])->has('kind'))
											{!!$theads[$key]['label']!!}
										@else
											@if(collect($theads[$key])->has('content'))
												@if(is_array($theads[$key]['content']))
													@foreach($theads[$key]['content'] as $componentKey => $component)
														@if(collect($component)->has('label') && !collect($component)->has('kind'))
															{!!$component['label']!!}
														@else
															@if(collect($component)->has('kind'))
																@component($component['kind'], slotsItem($component)) @endcomponent
															@endif
														@endif
													@endforeach
												@endif
											@endif
										@endif
									@else
										{!!$theads[$key]!!}
									@endif
								@endcomponent
							</td>
						@endif
						@if($key == $theads->count()-1 && isset($td['content'][0]) && (collect($td['content'][0])->containsStrict('components.buttons.button') || collect($td['content'][0])->containsStrict('kind', 'components.buttons.button')))
							<td class="p-3 @if(!isset($noHead))
												@if($key == 0) 
													bg-orange-50 @if(($trNumber % 2) == 0) md:bg-white @else md:bg-orange-100 @endif main-col-always 
												@else
													@if(($trNumber % 2) == 0) bg-white @else bg-orange-100 @endif
												@endif
											@endif
											@isset($td['classEx'])
												{{$td['classEx']}}
											@endisset col-span-12 md:col-span-1 text-center md:border-l md:border-r border-orange-200">
						@else	
							<td class="p-3 @if(!isset($noHead))
												@if($key == 0) 
													bg-orange-50 @if(($trNumber % 2) == 0) md:bg-white @else md:bg-orange-100 @endif main-col-always 
												@else
													@if(($trNumber % 2) == 0) bg-white @else bg-orange-100 @endif
												@endif
											@endif
											@isset($td['classEx'])
												{{$td['classEx']}}
											@endisset col-span-6 text-center align-middle md:border-l md:border-r border-orange-200">
						@endif
								@foreach($td['content'] as $component)
									<div class="flex items-center justify-center w-full h-full">
										@if(is_array($component))
											@if(collect($component)->has('label') && !collect($component)->has('kind'))
												{!!$component['label']!!}
											@else
												@component($component['kind'], slotsItem($component)) @endcomponent
											@endif
										@else
											{!!$component!!}
										@endif
									</div>
								@endforeach
							</td>
					@endif
				@endforeach
			</tr>
		@endforeach
	@endisset
@if(!isset($noHead))
		</tbody>
	</table>
</div>
@endif
@if(isset($pagination))
	<div class="text-center paginate w-full">
		<div class="px-8">
			{!! $pagination !!}
		</div>
	</div>
@endif