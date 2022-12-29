@if(!isset($modelHead) || !is_array($modelHead))
	[[Error en tabla: verifique la variable de cabeceras]]
@elseif(isset($modelBody) && !is_array($modelBody))
	[[Error en tabla: verifique la variable del cuerpo]]
@else
	@php
		$headForTable = [];
		$orangeHead = [];
		if(count($modelHead) > 1)
		{
			$indexTwo = 0;
			foreach($modelHead[0] as $index => $head)
			{
				if(isset($head["rowspan"]))
				{
					$headForTable[]['value'] = $head['value'];
				}
				if(isset($head["colspan"]))
				{
					$orangeHead[$index] = $head['value'];
					for($i = 0; $i < $head["colspan"]; $i++)
					{
						$headForTable[]['value'] = $modelHead[1][$indexTwo]['value'];
						$indexTwo++;
					}
					$orangeHead[($index + $head["colspan"])] = "";
				}
			}
		}
		else
		{
			$headForTable = $modelHead[0];
		}
	@endphp
	@if(!isset($noHead))
	<div class="relative md:overflow-x-auto min-w-full max-w-full w-full text-xs @isset($classEx) {{ $classEx }} @endisset tables-table" @isset($attributeEx ) {!!$attributeEx!!} @endisset>
		@isset($title)
			<div class="w-full text-center text-xl text-white bg-orange-500 border-b-2 border-white">
				<strong>{!!$title!!}</strong>
			</div>
		@endisset
		<table class="min-w-full border-collapse md:border border-orange-200">
			<thead class="md:table-header-group text-white font-bold @isset($classExHead) {{ $classExHead }} @endisset" @isset($attributeExHead ) {!!$attributeExHead!!} @endisset>
				@foreach ($modelHead as $index => $head)
					@if($index == 0)
						<tr class="grid grid-cols-12 md:table-row">
					@else
						<tr class="hidden md:table-row">
					@endif
						<th class="md:hidden"></th>
						@foreach ($head as $headIndex => $headCell)
							<th class="bg-orange-500 {{$tableCell['classEx']??''}} {{($headIndex == 1 ? 'col-span-6' : 'col-span-5')}} {{$headIndex > 1 ? 'col-to-hide' : ''}} p-1 md:border-r md:border-l border-orange-200 @isset($headCell['classEx']) {{$headCell['classEx']}} @endisset" @isset($headCell['attributeEx']) {!!$headCell['attributeEx']!!} @endisset @isset($headCell['rowspan']) rowspan="{{$headCell['rowspan']}}" @endisset @isset($headCell['colspan']) colspan="{{$headCell['colspan']}}" @endisset>
								<div class="flex items-center justify-center w-full h-full">
									@isset($headCell["componentsEx"])
										@foreach($headCell["componentsEx"] as $component)
											@component($component['kind'], slotsItem($component)) @endcomponent
										@endforeach
									@else
										{!!$headCell['value']!!}
									@endisset
								</div>
							</th>
						@endforeach
					</tr>
				@endforeach
			</thead>
			<tbody class="w-full @isset($classExBody ) {{ $classExBody  }} @endisset" @isset($attributeExBody ) {!!$attributeExBody!!} @endisset>
	@endif
				@foreach($modelBody as $indexBody=>$body)
					@php
						$putBg = $indexBody%2;
					@endphp
					<tr class="grid grid-cols-12 md:table-row text-xs {{$putBg==1? 'bg-orange-100' : 'bg-white'}} @if(array_key_exists('classEx', $body)){!! implode(' ', collect(explode(' ',$body['classEx'].' tr'))->unique()->values()->all()) !!}@else tr @endif" @if(array_key_exists('attributeEx', $body)) {!!$body['attributeEx']!!} @endif>
						<td class="md:hidden {{$putBg==1? 'bg-orange-100' : 'bg-white'}}">
							<svg class="cursor-pointer arrow-action w-full h-full" fill="none" height="35" stroke="#f97316" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" width="35" xmlns="http://www.w3.org/2000/svg">
								<polyline class="polyline" points="18 15 12 9 6 15"></polyline>
							</svg>
						</td>
						@foreach($body as $index => $tr)
							@if(is_array($tr))
								@if(isset($orangeHead[$index]))
									<td class="md:hidden col-start-2 col-span-11 align-middle text-center bg-orange-400 text-white col-to-hide font-bold">
										{{$orangeHead[$index]}}
									</td>
								@endif
								@if($index > 1)
									<td class="font-bold md:hidden col-to-hide col-span-5 col-start-2 {{$putBg==1? 'bg-orange-100' : 'bg-white'}}">
										<div class="flex items-center justify-center w-full h-full">
											{{$headForTable[$index]["value"]}}
										</div>
									</td>
								@endif
								<td class="p-3 md:border-l md:border-r border-orange-200 bg-white align-middle {{$putBg==1? 'bg-orange-100' : 'bg-white'}} {{($index == 1 ? 'col-span-6' : ($index > 1 ? 'col-span-6' : 'col-span-5'))}} {{$index > 1 ? 'col-to-hide' :''}} bg-white @if(array_key_exists('classEx', $tr)) {{$tr['classEx']}} @endif" @if(array_key_exists('attributeEx', $tr)) {!!$tr['attributeEx']!!} @endif>
									<div class="flex items-center justify-center text-center w-full h-full">
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
	@if(!isset($noHead))
			</tbody>
		</table>
	</div>
	@endif
@endif