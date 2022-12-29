@extends('layouts.layout')
@section('title', $title)
@section('content')
	<div class="w-full">
		@component("components.labels.title-config") {{ $title }} @endcomponent
		<div class="text-center mb-6">
			<i style="color: #B1B1B1;">{{ $details }}</i>
		</div>		
		<hr class="bg-amber-500 h-px border-0 mb-6">
		<h4>Acciones: </h4>
		<div class="content-start items-center justify-center text-center w-full grid grid-cols-12 mb-4">
			@component('components.buttons.button-secondary')
				@slot('classEx')				
					bg-orange-600
					border-none
					text-white
					shadow-md
					lg:col-span-3
					md:col-span-6
					col-span-12
				@endslot
				@slot('href'){{ url('/suggestions/view') }}@endslot
				Ver
			@endcomponent
		</div>
		<div class="data-container" id="moduleContent">
			@component("components.labels.title-divisor") SUGERENCIAS ENVIADAS @endcomponent
			@if(count($suggestions) > 0)
				<div class="text-right">
					@component("components.forms.form", ["attributeEx" => "action=\"".route('suggestions.export')."\" id=\"container-alta\""])
						@component("components.buttons.button",["variant" => "success"])
						@slot("attributeEx") type="submit" @endslot
						@slot("slot") <span>Exportar a Excel</span><span class="icon-file-excel"></span> @endslot
						@endcomponent
					@endcomponent
				</div>
				@php
					$modelHead = ["Asunto", "Sugerencia", "Fecha"];
					$modelBody = [];
					foreach ($suggestions as $suggestion)
					{
						$modelBody[] = 
						[
							[
								"show" => "true",
								"content" =>
								[
									"label" => htmlentities($suggestion->subject),
								]
							],
							[
								"content" =>
								[
									"label" => htmlentities($suggestion->suggestion),
								]
							],
							[
								"content" =>
								[
									"label" => Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $suggestion->date)->format('d-m-Y H:i'),
								],
							],
						];
					}
				@endphp
				@component("components.tables.AlwaysVisibleTable", [
					"modelHead"	=> $modelHead,
					"modelBody" => $modelBody,
					"variant"	=> "default"
					]) 
				@endcomponent
				{{ $suggestions->appends($_GET)->links() }}
			@else
				@component("components.labels.not-found",["text" => "No hay sugerencias para mostrar"]) @endcomponent
			@endif
		</div>
	</div>
@endsection
@section('scripts')
<script src="{{ asset('js/jquery-ui.js') }}"></script>
<script src="{{ asset('js/jquery.numeric.js') }}"></script>
<script>
	$('.content').each(function()
	{
		var $this = $(this);
		var t = $this.text();
		$this.html(t.replace('&lt','<').replace('&gt', '>'));
	});
</script>
@endsection
