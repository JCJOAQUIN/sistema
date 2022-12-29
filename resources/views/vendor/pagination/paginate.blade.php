@if ($paginator->hasPages())
	@php
		$hover = "hover:bg-orange-100";
	@endphp
	<div class="bg-white px-4 py-3 sm:px-6 result_pagination paginate bg-paginate">
		<div class="flex-1 flex flex-wrap justify-between sm:hidden">
			@if ($paginator->onFirstPage())
				<a href="{{$paginator->url($paginator->firstItem())}}" disabled class=" disabled relative inline-flex items-center px-4 py-2 border border-gray-200 text-sm font-medium rounded-md text-gray-500 bg-white {{$hover}}">
					Anterior
				</a>
			@else
				<a href="{{ $paginator->previousPageUrl() }}" class="load-actioner ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white {{$hover}}">
					Anterior
				</a>
			@endif
			@if ($paginator->hasMorePages())
				<a href="{{ $paginator->nextPageUrl()}}" class="load-actioner ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white {{$hover}}">
					Siguiente
				</a>
			@else
				<a href="{{$paginator->url($paginator->lastPage())}}" disabled class="disabled load-actioner ml-3 relative inline-flex items-center px-4 py-2 border border-gray-200 text-sm font-medium rounded-md text-gray-500 bg-white {{$hover}}">
					Siguiente
				</a>
			@endif
		</div>
		<div class="hidden sm:flex-1 sm:flex flex-wrap justify-center">
			<nav class="relative z-0 inline-flex flex-wrap items-center justify-center rounded-md shadow-sm -space-x-px" aria-label="Pagination">
				@if ($paginator->onFirstPage())
					<a href="{{$paginator->url($paginator->firstItem())}}" disabled class="disabled relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 {{$hover}}">
						<span class="sr-only">Anterior</span>
						<svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="#f97316" stroke="#f97316" aria-hidden="true">
							<path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
						</svg>
					</a>
				@else
					<a href="{{ $paginator->previousPageUrl() }}" class="load-actioner relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 {{$hover}}">
						<span class="sr-only">Anterior</span>
						<svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="#f97316" stroke="#f97316" aria-hidden="true">
							<path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
						</svg>
					</a>
				@endif
				@foreach ($elements as $element)
					{{-- "Three Dots" Separator --}}
					@if (is_string($element))
						<a href="{{$paginator->url($paginator->currentPage())}}" disabled aria-current="page" class="pointer-events-none outline-none z-10 border-gray-300 text-orange-500 relative inline-flex items-center px-4 py-2 border text-sm font-medium">
							...
						</a>
					@endif
					{{-- Array Of Links --}}
					@if (is_array($element))
						@foreach ($element as $page => $url)
							@if ($page == $paginator->currentPage())
								<div aria-current="page" class="load-actioner z-10 border-gray-300 text-orange-500 bg-orange-300 relative inline-flex items-center px-4 py-2 border text-sm font-medium">
									{{ $page }}
								</div>
							@else
								<a href="{{ $url }}" aria-current="page" class="load-actioner z-10 border-gray-300 text-orange-500 {{$hover}} relative inline-flex items-center px-4 py-2 border text-sm font-medium">
									{{ $page }}
								</a>
							@endif
						@endforeach
					@endif
				@endforeach
				@if ($paginator->hasMorePages())
					<a href="{{ $paginator->nextPageUrl() }}" class="load-actioner relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 {{$hover}}">
						<span class="sr-only {{$hover}}">Siguiente</span>
						<svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="#f97316" stroke="#f97316" aria-hidden="true">
						<path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
						</svg>
					</a>
				@else
					<a href="{{$paginator->url($paginator->lastPage())}}" diabled class="disabled load-actioner relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 {{$hover}}">
						<span class="sr-only">Siguiente</span>
						<svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="#f97316" stroke="#f97316" aria-hidden="true">
							<path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
						</svg>
					</a>
				@endif
			</nav>
		</div>
	</div>
@endif