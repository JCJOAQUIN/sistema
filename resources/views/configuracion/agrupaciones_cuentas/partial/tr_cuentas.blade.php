@foreach($accounts as $acc)
	
		@if ($acc->level == 3) 
			<div class="col-span-1 mb-2 grid md:grid md:grid-cols-12">
				<div class="col-span-2 grid place-content-center">
					@component("components.inputs.checkbox", ["classExContainer" => "text-center"])
					@slot("attributeEx")
						name="idAccAcc[]"
						value="{{ $acc->idAccAcc }}"
						id="{{ $acc->idAccAcc }}"
					@endslot
					<span class="icon-check"></span>
				@endcomponent
				</div>
				<div class="col-span-10 self-center">
					@component("components.labels.label")
						{{ $acc->account }} - {{ $acc->description }} ({{ $acc->content }})
					@endcomponent
				</div>
			</div>
		@endif
	
@endforeach