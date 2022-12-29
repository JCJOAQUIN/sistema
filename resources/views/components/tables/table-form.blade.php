@php
	$toShowCount   = 0;
	if(isset($modelHead))
	{
		foreach ($modelHead as $totalColCount => $heads)
		{
			if(array_key_exists('show', $heads))
			{
				$toShowCount++;
			}
		}
	}
@endphp
@if(!isset($modelHead) || !is_array($modelHead))
	[[Error en tabla: verifique la variable de cabeceras]]
@elseif(isset($modelBody) && !is_array($modelBody))
	[[Error en tabla: verifique la variable del cuerpo]]
@elseif($toShowCount == 0)
	[[Error en tabla: Se debe asignar al menos una cabecera para mostrar]]
@else
	@php
		$mdColWith  = 1;
		$allColWith = 1;
		$restCol    = 1;
		if($toShowCount < 12 && $totalColCount < 12)
		{
			$mdColWith  = (int) (12 / $toShowCount);
			$allColWith = round(12 / $totalColCount);
		}
		if(($totalColCount - $toShowCount) < 12)
		{
			$restCol    = $totalColCount - $toShowCount;
		}
		$headersToReplicate = array();
	@endphp
	<div class="relative md:overflow-x-auto min-w-full max-w-full text-xs">
		@isset($title)
			<div class="w-full text-center text-xl text-white bg-orange-500 border-b-2 border-white">
				<strong>{!!$title!!}</strong>
			</div>
		@endisset
		<table class="min-w-full @isset($classEx ) {{ $classEx  }} @endisset" @isset($attributeEx ) {!!$attributeEx!!} @endisset>
			@isset($modelHead)
				<thead class="text-white hidden md:table-header-group @isset($classExHead ) {{ $classExHead  }} @endisset" @isset($attributeExHead ) {!!$attributeExHead!!} @endisset>
					<tr>
						<th class="md:hidden"></th>
						@foreach ($modelHead as $index => $heads)
							<th class="p-3 bg-orange-400 @if($index < 2) sticky inset-x-0 @endif @isset($heads['classEx']) {{$heads['classEx']}} @endisset" @isset($heads['attributeEx']) {!!$heads['attributeEx']!!} @endisset>{!!$heads['value']!!}</th>
						@endforeach
					</tr>
				</thead>
			@endisset
			<tbody class="w-full @isset($classExBody ) {{ $classExBody  }} @endisset" @isset($attributeExBody ) {!!$attributeExBody!!} @endisset>
				@foreach($modelBody as $keyTr => $body)
					<tr class="grid grid-cols-12 md:table-row text-xs @if(array_key_exists('classEx', $body)) {{$body['classEx']}} @endif" @if(array_key_exists('attributeEx', $body)) {!!$body['attributeEx']!!} @endif>
						<td class="md:hidden">
							<svg class="cursor-pointer arrow-action w-full h-full" fill="none" height="35" stroke="#f97316" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg">
								<polyline class="polyline" points="18 15 12 9 6 15"></polyline>
							</svg>
						</td>
						@foreach($body as $keyCol => $col)
							@if(is_array($col))
								@if($keyCol > 1)
									<td class="font-bold md:hidden col-to-hide col-span-5 col-start-2 @if(($keyTr % 2) == 0) bg-white @else bg-orange-100 @endif ">
										<div class="flex items-center justify-center w-full h-full">
											{!!$modelHead[$keyCol]['value']!!}
										</div>
									</td>
								@endif
								<td class="p-3 align-middle
									@if(($keyTr % 2) == 0) bg-white @else bg-orange-100 @endif
									@if($keyCol == 0)	
										col-span-5
									@else
										col-span-6
									@endif
									@if($keyCol < 2) sticky inset-x-0 @else col-to-hide @endif 
									@if(array_key_exists('classEx', $col)) {!!$col['classEx']!!} @endif" 
									@if(array_key_exists('attributeEx', $col)) {!!$col['attributeEx']!!} @endif>
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
								</td>
							@endif
						@endforeach
						{{--<div class="hidden md:grid grid-cols-2 md:grid-cols-{{$restCol}} w-full bg-orange-50 md:bg-transparent p-4 md:p-0">
							@php
								$tmpHeader = 0;
							@endphp
							@php
								$bool = false;
							@endphp
							@foreach($body as $col)
								@if(is_array($col))
									@if(!array_key_exists('show', $col))
										@if($loop->last && isset($col['content'][0]) && (in_array('components.buttons.button',$col['content'][0]) ))
											@php
												$bool = true;
											@endphp
											<div class="col-span-2 md:col-span-1 p-1 md:border-l flex justify-center items-center flex-wrap content-center align-center border-orange-200 space-x-2 md:space-x-0 @if(array_key_exists('classEx', $col)) {{$col['classEx']}} @endif" @if(array_key_exists('attributeEx', $col)) {!!$col['attributeEx']!!} @endif>
										@else
											<div class="col-span-1 md:col-span-1 text-left m-2 md:m-0">
												<div class="p-0 md:hidden font-bold">{!! $headersToReplicate[$tmpHeader] !!}</div>
												<div class="p-0 md:p-1 text-left flex md:justify-center items-center flex-wrap content-center md:text-center md:border-l border-orange-200 text-center @if(array_key_exists('classEx', $col)) {{$col['classEx']}} @endif" @if(array_key_exists('attributeEx', $col)) {!!$col['attributeEx']!!} @endif>
										@endif
										@php
											$tmpHeader++;
										@endphp
												@if(isset($col['content'][0]))
													@foreach($col['content'] as $item)
														@isset($item['kind'])
															@if($item['kind'] == "components.buttons.button" && $bool == false)
																@isset($item["classEx"])
																	@php
																		$item["classEx"] = $item["classEx"]." w-1/2 md:w-auto";
																	@endphp
																@else
																	@php
																		$item["classEx"] = "w-1/2 md:w-auto";
																	@endphp
																@endisset
																@component($item['kind'], slotsItem($item))	@endcomponent
															@else
																@component($item['kind'], slotsItem($item))	@endcomponent
															@endif
														@else
															{!! $item['label'] !!}
														@endisset
													@endforeach
												@else
													@isset($col['content']['kind'])
														@if($col['content']['kind'] == "components.buttons.button")
															@isset($col['content']['classEx'])
																@php
																	$col["content"]["classEx"] = $col["content"]["classEx"]." w-1/2 md:w-auto";
																@endphp
															@else
																@php
																	$col["content"]["classEx"] = "w-1/2 md:w-auto";
																@endphp
															@endisset
															@component($col['content']['kind'], slotsItem($col['content']))  @endcomponent
														@else
															@component($col['content']['kind'], slotsItem($col['content'])) @endcomponent
														@endif
													@else
														{!! $col['content']['label'] !!}
													@endisset
												@endif
										@if($loop->last && isset($col['content'][0]) && (in_array('components.buttons.button',$col['content'][0]) ))
											</div>
										@else
												</div>
											</div>
										@endif
									@endif
								@endif
							@endforeach
						</div>--}}
					</tr>
				@endforeach
			</tbody>
		</table>
	</div>
@endif
{{-- Js Section inside "Layout" file with class "arrow-action" --}}