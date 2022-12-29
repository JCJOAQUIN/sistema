<div class="docs-p col-span-1 @if (!isset($variant) || $variant != "no-border") border border-gray-300 @endif bg-gray-200 bg-opacity-10 p-4 @isset($classEx) {!! $classEx !!} @endisset" @isset($attributeEx) {!! $attributeEx !!} @endisset>
	@isset($componentsExUp)
		<div class="components-ex-up @isset($classExContentUp) {!! $classExContentUp !!} @endisset mb-4" @isset($attributeExContentUp) {!! $attributeExContentUp !!} @endisset>
			@if(is_array($componentsExUp))
				@foreach($componentsExUp as $ExUp)
					@component($ExUp["kind"], slotsItem($ExUp)) @endcomponent
				@endforeach
			@else
				@isset($componentsExUp) {!! $componentsExUp !!} @endisset
			@endif
		</div>
	@endisset
	<div class="uploader-content border-2 border-orange-400 border-dashed rounded-lg mb-4 @isset($classExContainer) {!! $classExContainer !!} @endisset">
		<input type="file" class="appearance-none cursor-pointer h-full w-full opacity-0 @isset($classExInput) {{ $classExInput }} @endisset" @isset($attributeExInput) {!! $attributeExInput !!}  @endisset />
	</div>
	<input
		type="hidden" @isset($attributeExRealPath) {!! $attributeExRealPath !!} @endisset @isset($classExRealPath) class="{{ $classExRealPath }} upload-file-class" @endisset>	
	@isset($componentsExDown) 
		<div class="components-ex-down @isset($classExContentDown) {!! $classExContentDown !!} @endisset mb-4" @isset($attributeExContentDown) {!! $attributeExContentDown !!} @endisset>
			@if(is_array($componentsExDown))
				@foreach($componentsExDown as $ExDown)
					@component($ExDown["kind"], slotsItem($ExDown)) @endcomponent
				@endforeach
			@else
				@isset($componentsExDown) {!! $componentsExDown !!} @endisset
			@endif
		</div>
	@endisset
	@if(!isset($noDelete) || $noDelete != "true")
		<div class="container-actions-files @isset($classExContentAction) {!! $classExContentAction !!} @endisset">
			@isset($componentsAction) 
				@if(is_array($componentsAction))
					@foreach($componentsAction as $component)
						@component($component["kind"], slotsItem($component)) @endcomponent
					@endforeach
				@else
					@isset($component) {!! $component !!} @endisset
				@endif
			@endisset
			@component("components.buttons.button", ["variant" => "red"]) @slot("classEx") @isset($classExDelete) {{ $classExDelete }} @endisset delete-uploaded-file @endslot @slot("attributeEx") @isset($attributeExDelete) {{ $attributeExDelete }} @endisset type="button" @endslot Borrar @endcomponent
		</div>
	@endif
</div>
