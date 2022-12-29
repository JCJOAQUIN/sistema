	<div class="min-w-full bg-orange-600 text-center text-white font-semibold text-xl md:pl-0 pl-20">
		@isset($title) 
			@if(is_array($title))
				@foreach($title as $hiddenComponent)
					@component($hiddenComponent['kind'], slotsItem($hiddenComponent)) @endcomponent
				@endforeach
			@else
				{!!$title!!}
			@endif
		@endisset
	</div>
	<div class="relative md:overflow-x-auto min-w-full max-w-full text-xs w-full">
		<table class="min-w-full border-collapse {{isset($boardrooms) ? 'border' : 'md:border'}} border-orange-200 bg-white @isset($classEx ) {{ $classEx  }} @endisset" @isset($attributeEx ) {!!$attributeEx!!} @endisset>
			@if (!isset($noHeads))
				<thead class="md:table-header-group text-white font-bold">
					<tr>
						@foreach ($modelHead as $index => $heads)
							<th class="inset-x-0 bg-orange-500 @isset($heads['classEx']) {{$heads['classEx']}} @endisset" @isset($heads['attributeEx']) {!!$heads['attributeEx']!!} @endisset>
								{!!$heads['value']!!}
							</th>
						@endforeach
					</tr>
				</thead>
			@endif
			<tbody class="w-full @isset($classExBody) {{$classExBody}} @endisset" @isset($attributeExBody) {!!$attributeExBody!!} @endisset>
				@foreach($modelBody as $mainIndex => $body)
					<tr class="@if (!isset($boardrooms)) grid grid-cols-12 md:table-row @else table-row border-b @endif text-xs users_class md:border-b border-orange-200 @if(array_key_exists('classEx', $body)) {{$body['classEx']}} @endif" @if(array_key_exists('attributeEx', $body)) {!!$body['attributeEx']!!} @endif>
						@if (!isset($boardrooms))
							<td class=" md:hidden">
								<svg class="cursor-pointer arrow-action" fill="none" height="24" stroke="#f97316" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg">
									<polyline class="polyline" points="18 15 12 9 6 15"></polyline>
								</svg>
							</td>
						@endif
						@foreach($body as $index => $col)
							@if(is_array($col))
								@php
									$emptyCell = array_column($col['content'], 'label');
								@endphp
								<td class="align-middle text-center @if (!isset($boardrooms))
									{{$index > 0 ? (count($emptyCell) == 0 ? 'hidden md:table-cell' : 'col-to-hide') : 'sticky inset-x-0 border-l border-orange-200'}}
									@else
										table-cell
									@endif  {{($index == 0 ? 'col-span-11' : ($index % 2 == 0 ? 'col-span-6' : 'col-span-5 col-start-2'))}}  @if($index > 0) bg-white @else bg-orange-100 @endif @if(array_key_exists('classEx', $col)) {{$col['classEx']}} @endif">
									<div class="{{!isset($boardrooms) ? 'p-3' : ''}} flex items-center justify-center">
										@if(isset($col['content'][0]))
											@foreach($col['content'] as $item)
												@isset($item['kind'])
												
													@component($item['kind'], slotsItem($item))	@endcomponent
												@else
													{!! $item['label'] !!} 
												@endisset
											@endforeach
										@else
											@isset($col['content']['kind'])
												@component($col['content']['kind'], slotsItem($col['content'])) @endcomponent
											@else
												{!! $col['content']['label'] !!}
											@endisset
										@endif
									</div>
								</td>
							@endif
						@endforeach
					</tr>
				@endforeach
			</tbody>
		</table>
	</div>