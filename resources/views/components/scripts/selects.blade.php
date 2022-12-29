 @foreach($selects as $select)
	$('@if(isset($select["identificator"])){!!$select["identificator"]!!}@endif').select2(
		{
			width                   : '100%',
			@foreach($select as $key => $value)
				{{$key}}    :   '{{$value}}',
			@endforeach
		}
	)
	@isset($select["maximumSelectionLength"])
		.on("change",function(e)
		{
			if($(this).val().length>1)
			{
				$(this).val($(this).val().slice(0,1)).trigger('change');
			}
		})
	@endisset
	;
@endforeach
