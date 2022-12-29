@if(!isset($modelHead) || !is_array($modelHead))
	[[Error en tabla: verifique la variable de cabeceras]]
@elseif(isset($modelBody) && !is_array($modelBody))
	[[Error en tabla: verifique la variable del cuerpo]]
@else
@foreach($modelBody as $body)
	<tr class="grid grid-cols-12 md:table-row md:divide-x divide-orange-200 text-xs text-center @if(array_key_exists('classEx', $body)){!! implode(' ',collect(explode(' ',$body['classEx'].' tr'))->unique()->values()->all()) !!}@else tr @endif" @if(array_key_exists('attributeEx', $body)) {!!$body['attributeEx']!!} @endif>
		<td class="md:hidden">
			<svg class="cursor-pointer arrow-action" fill="none" height="35" stroke="#f97316" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" width="35" xmlns="http://www.w3.org/2000/svg">
				<polyline class="polyline" points="18 15 12 9 6 15"></polyline>
			</svg>
		</td>
		@foreach($body as $index => $tr)
			@if(is_array($tr))
				@if($index > 1)
					<td class="font-bold md:hidden col-to-hide col-span-5 col-start-2">{{$modelHead[$index]['value']}}</td>
				@endif
				<td class="p-3 bg-white text-center align-middle {{($index == 1 ? 'col-span-6' : ($index > 1 ? 'col-span-6' : 'col-span-5'))}} {{$index > 1 ? 'col-to-hide' :''}} bg-white text-center align-middle p-1 @if(array_key_exists('classEx', $tr)) {{$tr['classEx']}} @endif" @if(array_key_exists('attributeEx', $tr)) {!!$tr['attributeEx']!!} @endif>
					<div class="flex items-center justify-center w-full h-full">
						@if(isset($tr['content'][0]))
							@foreach($tr['content'] as $item)
								@isset($item['kind'])
									@component($item['kind'], slotsItem($item))	@endcomponent
								@else
									{!! $item['label'] !!}
								@endisset
							@endforeach
						@else
							@isset($tr['content']['kind'])
								@component($tr['content']['kind'], slotsItem($tr['content'])) @endcomponent
							@else
								{!!$tr['content']['label'] !!}
							@endisset
						@endif
					</div>
				</td>
			@endif
		@endforeach
	</tr>
@endforeach
@endif