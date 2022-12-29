@if(!isset($modelHead) || !is_array($modelHead))
	[[Error en tabla: verifique la variable de cabeceras]]
@elseif(isset($modelBody) && !is_array($modelBody))
	[[Error en tabla: verifique la variable del cuerpo]]
@else
	@php
		$orangeHead = [];
		$indexTwo	= 0;
		foreach($modelHead[0] as $index => $head)
		{
			if(isset($head["colspan"]))
			{
				$orangeHead[$index] = $head['value'];
				$orangeHead[($index + $head["colspan"])] = 0;
			}
		}
	@endphp
	@if(!isset($noHead))
	<div class="flex items-center justify-left">
		@foreach($modelHead[0] as $index => $buttons)
			@switch($index)
				@case(0)
					@component("components.inputs.checkbox", ["classEx" => "arrow-action", "classExContainer" => "py-2", "attributeEx" => "disabled id=\"all-id\""]) <span class="icon-plus"></span> Todo @endcomponent
					@break;
				@case(1)
					@component("components.inputs.checkbox", ["classEx" => "arrow-action providers-x", "classExContainer" => "py-2", "attributeEx" => "checked id=\"details-id\""]) <span class="icon-plus"></span> Detalles @endcomponent
					@break;
				@case(2)
					@component("components.inputs.checkbox", ["classEx" => "arrow-action providers-x", "classExContainer" => "py-2", "attributeEx" => "checked id=\"providers-id\""]) <span class="icon-plus"></span> Proveedores @endcomponent
					@break;
				@case(3)
					@if($buttons['value'] == "Votaciones")
						@component("components.inputs.checkbox", ["classEx" => "arrow-action providers-x", "classExContainer" => "py-2", "attributeEx" => "checked id=\"voting-id\""]) <span class="icon-plus"></span> Votaciones @endcomponent
					@endif
					@break;
			@endswitch
		@endforeach
	</div>
	<div class="relative overflow-x-auto min-w-full max-w-full w-full text-xs @isset($classEx) {{ $classEx }} @endisset tables-table" @isset($attributeEx ) {!!$attributeEx!!} @endisset>
		<table class="border-collapse border border-orange-200 w-full">
			<thead class="table-header-group text-white font-bold @isset($classExHead) {{ $classExHead }} @endisset" @isset($attributeExHead ) {!!$attributeExHead!!} @endisset>
				@foreach ($modelHead as $index => $head)
					<tr class="table-row">
						@foreach ($head as $headIndex => $headCell)
							<th class="bg-orange-500 {{($headIndex == 1 ? 'col-span-6' : 'col-span-5')}} {{($index == 0 && $headIndex == 0 ? 'sticky inset-x-0' : ($index!=0 && $headIndex< array_keys($orangeHead)[1] ? 'sticky inset-x-0' : ''))}} p-1 border-r border-l border-orange-200 @isset($headCell['classEx']) {{$headCell['classEx']}} @endisset" @isset($headCell['attributeEx']) {!!$headCell['attributeEx']!!} @endisset @isset($headCell['rowspan']) rowspan="{{$headCell['rowspan']}}" @endisset @isset($headCell['colspan']) colspan="{{$headCell['colspan']}}" @endisset>
								<div class="flex items-center justify-center w-full h-full">
									@isset($headCell["componentsEx"])
										@foreach($headCell["componentsEx"] as $component)
											@component($component['kind'], slotsItem($component)) @endcomponent
										@endforeach
									@else
										{!!$headCell['value']!!}
									@endisset
								</div>
								@if(isset($headCell['contentEx']))
									{!!$headCell['contentEx']!!}
								@endif
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
					<tr class="table-row text-xs {{$putBg==1? 'bg-orange-100' : 'bg-white'}} border-b border-orange-200 @if(array_key_exists('classEx', $body)){!! implode(' ', collect(explode(' ',$body['classEx'].' tr'))->unique()->values()->all()) !!}@else tr @endif" @if(array_key_exists('attributeEx', $body)) {!!$body['attributeEx']!!} @endif>
						@foreach($body as $index => $tr)
							@if(is_array($tr))
								<td class="{{array_keys($orangeHead)[1] > $index ? 'sticky inset-x-0' : ''}} p-3 border-l border-r border-orange-200 bg-white align-middle {{$putBg==1? 'bg-orange-100' : 'bg-white'}} {{($index == 1 ? 'col-span-6' : ($index > 1 ? 'col-span-6' : 'col-span-5'))}}  bg-white @if(array_key_exists('classEx', $tr)) {{$tr['classEx']}} @endif" @if(array_key_exists('attributeEx', $tr)) {!!$tr['attributeEx']!!} @endif>
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
				<tr class="table-row providers-id">
					@foreach($modelHead[0] as $index => $footer)
						@isset($footer['colspan'])
							<td class="{{$footer['id']??''}}" colspan="{{$footer['colspan']}}">
								@isset($footer['footer'])
									@foreach($footer['footer'] as $footer)
										@component($footer['kind'], slotsItem($footer))	@endcomponent
									@endforeach
								@endisset
							</td>
						@endisset
					@endforeach
				</tr>
	@if(!isset($noHead))
			</tbody>
		</table>
	</div>
	@endif
@endif
{{--{{array_sum(array_slice(array_column($modelHead[0], 'colspan'), 0, $index, true))}}--}}