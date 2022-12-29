@php
	$toShowCount    = 0;
	$toHide			= 0;
	$totalColCount  = 0;
	
	if(isset($modelHead))
	{
		foreach ($modelHead as $key => $heads)
		{
			$totalColCount++;
			if(array_key_exists('show', $heads))
			{
				$toShowCount++;
			}else
			{
				$toHide++;
			}
		}
	}
@endphp
@if(!isset($modelHead) || !is_array($modelHead))
	[[Error en tabla: verifique la variable de cabeceras]]
@elseif(isset($modelBody) && !is_array($modelBody))
	[[Error en tabla: verifique la variable del cuerpo]]
@elseif(!isset($modelGroup))
	[[Error en tabla: verifique las agrupaciones]]
@elseif($toShowCount == 0)
	[[Error en tabla: Se debe asignar al menos una cabecera para mostrar]]
@else
	@php
		$head 			= [];
		$tempCounter	= 0;
		$limit			= $modelGroup[0]['colNumber'];
	@endphp
	<div class="{{count($modelHead) > 12 ? 'overflow-x-auto' : ''}} md:border border-orange-200 w-full text-xs @isset($classEx) {{ $classEx }} @endisset" @isset($attributeEx ) {!!$attributeEx!!} @endisset>
		@isset($title)
			<div class="w-screen w-full text-center text-xl text-white bg-orange-500 border-b-2 border-white font-semibold">
				{!!$title!!}
			</div>
		@endisset
		@if(isset($modelGroup))
			<div class="{{count($modelHead) > 12 ? 'w-screen' : ''}} md:grid md:grid-rows-2 divide-x divide-orange-200 md:grid-cols-{{count($modelHead)}}">
				@foreach($modelGroup as $index => $group)
					@isset($modelHead)
						<div class="w-full md:divide-y divide-orange-200 col-span-{{$group['colNumber']}} md:grid md:grid-cols-{{$group['colNumber']}} flex items-stretch items-start md:items-center justify-center bg-orange-500 row-span-2 text-center text-white font-bold @isset($classExHead) {{ $classExHead }} @endisset" @isset($attributeExHead ) {!!$attributeExHead!!} @endisset>
							@if($group["name"] != "")
								<div class=" hidden md:block text-white bg-orange-500 col-span-{!!$group['colNumber']!!} p-1 text-center @isset($group['classEx']) {!!$group['classEx']!!} @endisset" @isset($group['attributeEx']) {!!$group['attributeEx']!!} @endisset>{!!$group["name"]!!}</div>
							@endif
							<div class="h-full w-full divide-x divide-orange-200 col-span-{!!$group['colNumber']!!} grid grid-cols-{{$toShowCount}} md:grid-cols-{!!$group['colNumber']!!}">
								@for($i = $tempCounter; $i < $limit; $i++)
									@php
										$head = $modelHead[$i];
									@endphp
									<div class="md:grid h-full w-full flex items-stretch items-start items-center justify-center @if(!(array_key_exists('show', $head) && $head["show"] == "true")) hidden @endif @isset($head['classEx']) {{$head['classEx']}} @endisset" @isset($head['attributeEx']) {!!$head['attributeEx']!!} @endisset>
											@isset($head["componentsEx"])
												@foreach($head["componentsEx"] as $component)
													@component($component['kind'], slotsItem($component))	@endcomponent
												@endforeach
											@else
												{!!$head['value']!!}
											@endisset
									</div>
								@endfor
							</div>
						</div>
					@endisset
					@php
						if(isset($modelGroup[$index+1]['colNumber']))
						{
							$tempCounter += $modelGroup[$index]['colNumber'];
							$limit = $limit+$modelGroup[$index+1]['colNumber'];
						}
						else
						{
							
							$tempCounter =count($modelHead) - $limit;
							$limit = count($modelHead);
						}
					@endphp
				@endforeach
			</div>
		@endif
		<div class="{{count($modelHead) > 12 ? 'w-screen' : ''}} w-full md:divide-y divide-orange-200 children:even:bg-orange-100 @isset($classExBody ) {{ $classExBody  }} @endisset grid-cols-{{count($modelHead)}}" @isset($attributeExBody) {!!$attributeExBody!!} @endisset>
			@foreach($modelBody as $body)
				<div class="table-group w-full flex flex-wrap md:flex-nowrap md:grid md:grid-cols-{{$totalColCount}} text-center @if(array_key_exists('classEx', $body)) {{$body['classEx']}} @endif" @if(array_key_exists('attributeEx', $body)) {!!$body['attributeEx']!!} @endif>
					<div class="md:hidden z-10 w-0">
						<svg class="cursor-pointer arrow-action" fill="none" height="35" stroke="#f97316" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" width="35" xmlns="http://www.w3.org/2000/svg">
							<polyline class="polyline" points="18 15 12 9 6 15"></polyline>
						</svg>
					</div>
					@foreach($body as $key => $col)
						@if(is_array($col))
							@isset($modelGroup)
								@php 
									$colGroup = 0;
								@endphp
								@foreach($modelGroup as $keyGroup => $group)
									@php 
										$colGroup = $colGroup + $group['colNumber'];
									@endphp
									@if($key == $colGroup)
										{{--Favor de no borrar la clase "hidden-cols-header" porque es utilizada para funcionamiento en js--}}
										<div class="flex justify-center items-center flex-wrap content-center w-full hidden md:hidden col-span-{{$toShowCount+1}} hidden-cols-header bg-orange-500 text-white">
											{!!$modelGroup[$keyGroup+1]['name']!!}
										</div>
									@endif
								@endforeach
							@endisset
							<div class="p-1 w-full break-words 
								@if($key != 0) 
									md:border-l border-orange-200 
								@endif
								@if(!(array_key_exists('show', $col) && $col["show"] == "true")) 
									 hidden md:grid col-span-{{$toShowCount+1}} md:col-span-1 hidden-cols
								@endif 
								@if($loop->last && isset($col['content'][0]) && (in_array('components.buttons.button-table-icon',$col['content'][0]) || in_array('components.buttons.button',$col['content'][0]) || in_array('components.forms.form',$col['content'][0])))
									grid-cols-1 
								@else
									grid-cols-2 md:grid-cols-1 
								@endif 
								@if(array_key_exists('classEx', $col)) 
									{{$col['classEx']}} 
								@endif" 
								@if(array_key_exists('attributeEx', $col)) 
									{!!$col['attributeEx']!!} 
								@endif>
									{{--Favor de no borrar la clase "hideen-cols" porque es utilizada para funcionamiento en js--}}
									@if(!(array_key_exists('show', $col) && $col["show"] == "true") && !($loop->last && isset($col['content'][0]) && (in_array('components.buttons.button-table-icon',$col['content'][0]) || in_array('components.buttons.button',$col['content'][0]) || in_array('components.forms.form',$col['content'][0]))))
										<div class="flex justify-center items-center flex-wrap content-center p-1 col-span-1 md:hidden font-bold break-words text-center">
											@isset($modelHead[$key]["componentsEx"])
												@foreach($modelHead[$tkey]["componentsEx"] as $component)
													@component($component['kind'], slotsItem($component))	@endcomponent
												@endforeach
											@else
												{!! $modelHead[$key]["value"] !!}
											@endisset
										</div>
									@endif
									<div class="flex justify-center items-center flex-wrap content-center p-1 col-span-1 break-words text-center @if(array_key_exists('classEx', $col)) {{$col['classEx']}} @endif" @if(array_key_exists('attributeEx', $col)) {!!$col['attributeEx']!!} @endif>
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
												{!!$col['content']['label'] !!}
											@endisset
										@endif
									</div>
							</div>
						@endif
					@endforeach
				</div>
			@endforeach
		</div>
	</div>
@endif
{{-- Js Section inside "Layout" file with class "arrow-action" --}}