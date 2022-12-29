@extends('layouts.layout')
@section('title', $title)
@section('content')
<style>
* {
	box-sizing: border-box;
}
</style>
	<div class="container-blocks-all">
		<div class="title-config">
			<h1>{{ $title }}</h1>
		</div>
		<center>
			<i style="color: #B1B1B1">{{ $details }}</i>
		</center>
		<br>
		<hr>
		@buttonTutorial(["child_id" => isset($child_id) ? $child_id : null,"option_id" => isset($option_id) ? $option_id : null,])
		@endbuttonTutorial
		@if(count(Auth::user()->module->whereIn('id',[111,112,113,213]))>0)
			<h4>Acciones: </h4>
		@endif
		<div class="container-sub-blocks">
			@foreach(Auth::user()->module->where('father',41)->sortBy('created_at') as $key)
				<a class="sub-block" href="{{ url($key['url']) }}">{{ $key['name'] }}</a>
			@endforeach
		</div>
		<div class="row">
		 
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
@if(isset($alert))
	<script type="text/javascript">
		{!! $alert !!}
	</script>
@endif
@endsection
