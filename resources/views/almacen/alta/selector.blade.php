<div class="flex justify-center mb-5">
	@component('components.buttons.button',['variant' => 'reset', 'buttonElement' => 'a'])
		@slot('attributeEx')
			href="{{ route('warehouse.tool') }}"
		@endslot
		@slot('classEx')
			@if($selected_item == 1)
				bg-orange-500 text-white hover:bg-orange-500
			@endif
		@endslot
		Alta
	@endcomponent
	@component('components.buttons.button',['variant' => 'reset', 'buttonElement' => 'a'])
		@slot('attributeEx')
			href="{{ route('warehouse.tool.massive') }}"
		@endslot
		@slot('classEx')
			@if($selected_item == 2)
				bg-orange-500 text-white hover:bg-orange-500
			@endif
		@endslot
		Alta masiva
	@endcomponent
	@component('components.buttons.button',['variant' => 'reset', 'buttonElement' => 'a'])
		@slot('attributeEx')
			href="{{ route('warehouse.tool.purchase') }}"
		@endslot
		@slot('classEx')
			@if($selected_item == 3)
				bg-orange-500 text-white hover:bg-orange-500
			@endif
		@endslot
		Desde compras
	@endcomponent
</div>