@php
  $mainClasses = "modal m-auto justify-center overflow-auto";
  $arrayFind = ["1" => ["m-", "mx-", "my-"]];

  !isset($variant) ? $v = "large" : $v = $variant;
  
  $size = [
		"xl"    => "max-w-screen-xl",
		"large" => "max-w-screen-lg",
		"small" => "max-w-screen-sm",
		"medium" => "max-w-screen-md",
  ];
@endphp
<div @isset($id) id="{{$id}}" @endisset class="@isset($classEx) {{replaceClassEX($classEx, $mainClasses, $arrayFind)}} @else {{$mainClasses}} @endisset" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" @isset($attributeEx) {!!$attributeEx!!} @endisset data-backdrop="static">
  <div class="m-auto {{$size[$v] ?? $size["large"]}}" role="document">
	 <div class="modal-content">
		<div class="modal-header text-center @isset($classExTitleContainer) {{$classExTitleContainer}} @endisset">
		  @component("components.buttons.button", ["classEx" => "close close-modal", "variant" => "none"])
			 @slot("attributeEx")
				type="button" class="close" data-dismiss="modal" aria-label="Close"
			 @endslot
			 <span class="icon-cross text-base"></span> 
		  @endcomponent
		  <label class="modal-title font-medium text-2xl @isset($classExTitle){{$classExTitle}}@endisset" id="myModalLabel">@isset($modalTitle){!! $modalTitle !!}@endisset</label>
		</div>
		<div class="modal-body @isset($classExBody) {{$classExBody}} @endif" @isset($attributeExBody) {{$attributeExBody}} @endif>
		  @isset($modalBody)
			 {!! $modalBody !!}
		  @endisset
		</div>
		@isset($modalFooter)
		  <div class="modal-footer">
				{!! $modalFooter !!}
		  </div>
		@endisset
	 </div>
  </div>
</div>
