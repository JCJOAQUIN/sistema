<div class="grid justify-items-center items-center w-full mt-4">
	<div class="w-full sm:w-3/4 md:w-1/2 "{!! isset($attributeEx) ? $attributeEx : ''!!}>
		<div class="uploader-content w-full rounded-lg border-2 border-orange-500 border-dashed text-center">
			<input type="file" {!! isset($attributeExInput) ? $attributeExInput : ''!!} class="massive-component input-text appearance-none cursor-pointer h-full w-full opacity-0" accept=".csv"/>
		</div>
		@isset($buttons["separator"])
			@component("components.labels.label") @slot("classEx") font-semibold pt-4 @endslot Tipo de separador: @endcomponent
			<div class="flex flex-wrap justify-center w-full space-x-2 py-4">
				@foreach($buttons["separator"] as $button)
					@component($button["kind"], slotsItem($button)) @slot("massiveVariant") massiveButton @endslot @endcomponent
				@endforeach
			</div>
		@endisset
		{{-- ButtonEx realmente puede recibir cualquier tipo de componente, no solo botones, hubo error al asignarl nombre --}}
		<div class="flex flex-wrap justify-center w-full space-x-2 py-4">
			@isset($buttons["buttonEx"])
				@foreach($buttons["buttonEx"] as $button)
					@component($button["kind"], slotsItem($button)) @slot("classEx") rounded-full @endslot @endcomponent
				@endforeach
			@endisset
		</div>
		@component("components.labels.label") @slot("classEx") w-full text-center @endslot *Sólo archivos CSV @endcomponent
	</div>
</div>
