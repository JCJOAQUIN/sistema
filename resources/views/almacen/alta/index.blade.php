@extends('layouts.layout')
@section('title', $title)
@section('content')
	<div class="container-blocks-all">
		<div class="title-config">
			<h1>{{ $title }}</h1>
		</div>
		<center>
			<i style="color: #B1B1B1">{{ $details }}</i>
		</center>
		<br>
		<hr>
		@php
			$child_module = isset($child_id) ? App\Module::find($child_id) : null;
			$option_module = isset($option_id) ? App\Module::find($option_id) : null;
		@endphp
		@if(($child_module && $child_module->tutorials()->count() > 0) || ($option_module && $option_module->tutorials()->count() > 0))
			<div class="mb-2">
				<label>Video Tutoriales</label>
			</div>
			<div class="mb-4">
				@if ($child_module)
					@foreach ($child_module->tutorials as $tuto)
					<button 
						type="button" 
						class="btn btn-orange" 
						data-toggle="modal" 
						data-target="#dataUrlTutorial" 
						data-url-tutorial="{{ $tuto->url }}"><span class="icon-search"></span> {{ $tuto->name }}</button>
					@endforeach
				@endif
				@if ($option_module)
					@foreach ($option_module->tutorials as $tuto)
					<button 
						type="button" 
						class="btn btn-orange" 
						data-toggle="modal" 
						data-target="#dataUrlTutorial" 
						data-url-tutorial="{{ $tuto->url }}"><span class="icon-search"></span> {{ $tuto->name }}</button>
					@endforeach
				@endif
			</div>
		@endif
	<h4>Acciones: </h4>
	<div class="container-sub-blocks">
		@foreach(Auth::user()->module->where('father',41)->sortBy('created_at') as $key)
			<a
			@if(isset($option_id) && $option_id==$key['id'])
				class=" sub-block sub-block-active"
			@else
				class="sub-block"
			@endif
			href="{{ url($key['url']) }}">{{ $key['name'] }}</a>
		@endforeach
	</div>
</div>
<br>
<center>
	<input checked class="r_alta" type="radio" name="r_alta" id="r_alta" value="0">
	<label for="r_alta">Alta</label>
	<input class="r_alta" type="radio" name="r_alta" id="r_masiva" value="1">
	<label for="r_masiva">Alta masiva</label>
	<input class="r_alta" type="radio" name="r_alta" id="r_compras" value="2">
	<label for="r_compras">Desde compras</label>
</center>
<br>
<br>

<div id="alta" >
	@include('almacen.alta.alta')
</div>

<div id="masiva" style="display: none;">
	@include('almacen.alta.masiva')
</div>

<div id="compras" style="display: none;">
	@include('almacen.alta.compras')
</div>

@section('scripts')
<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
<script src="{{ asset('js/jquery-ui.js') }}"></script>
<script src="{{ asset('js/jquery.numeric.js') }}"></script>

<script>
	var removeAccountsCompra = true
	$('input[name=r_alta]').change(function()
	{
		var value = $( 'input[name=r_alta]:checked' ).val();
		if(value == 0)
		{
			$('#alta').slideDown('fast')
			$('#masiva').slideUp('fast')
			$('#compras').slideUp('fast')
			updateSelectsAlta();
		}
		if(value == 1)
		{
			$('#alta').slideUp('fast')
			$('#masiva').slideDown('fast')
			$('#compras').slideUp('fast')
			updateSelectsAlta();
		}
		if(value == 2)
		{
			$('#alta').slideUp('fast');
			$('#masiva').slideUp('fast');
			$('#compras').slideDown('fast');
			updateSelectsAlta();
			search_compras();
			$('#table_compras > tbody').find('tr').each(function()
			{
				$(this).remove();
			});
			$('#tbody_requisition').html('');
			totalArticles_compras();
			$('#form_compras_container').slideUp();
		}
	});
	$.validate(
	{
		form: '#container-alta',
		modules		: 'security',
		onSuccess : function($form)
		{
			path = $('.path').val();
			total = parseFloat($('input[name="total"]').val());
			total_articles = parseFloat($('input[name="total_articles"]').val());
			countbody = $('#body tr').length;
			if(total_articles == "" || countbody <= 0)
			{
				swal({
					title: "Error",
					text: "Debe agregarse al menos un artículo.",
					icon: "error",
					buttons: 
					{
						confirm: true,
					},
				});
				return false;
			}
			else if (total_articles > total || total_articles < total)
			{
				swal({
					title: "Error",
					text: "La inversión de artículos no coincide con el monto del ticket/factura.",
					icon: "error",
					buttons: 
					{
						confirm: true,
					},
				});
				return false;
			}
			else if (path == undefined || path == "") 
			{
				swal({
					title: "Error",
					text: "Debe agregar al menos un ticket de compra.",
					icon: "error",
					buttons: 
					{
						confirm: true,
					},
				});
				return false;
			}
			else
			{
				return true;
			}
		}
	});
</script>
@include('almacen.alta.scripts_alta')
@include('almacen.alta.scripts_masiva')
@include('almacen.alta.scripts_compras')
<script>
	updateSelectsAlta();
</script>
@endsection

@endsection
