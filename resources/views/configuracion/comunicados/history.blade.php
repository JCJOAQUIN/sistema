@extends('layouts.layout')

@section('title', 'Inicio')

@section('content')
		<div class="title-config">
			<h1>Comunicados</h1>
		</div>
		<center>
			<i style="color: #B1B1B1"></i>
		</center>
		<br>
		<hr>
			<h4>Acciones: </h4>
		<div class="container-sub-blocks">
			<a class=" sub-block sub-block-active" href="{{ url('releases/history') }}">Historial</a>
		</div>
	<center>
		{!! Form::open(['route' => 'releases.history', 'method' => 'GET', 'id'=>'formsearch']) !!}			
			<center>
				<div class="search-table-center">
					<div class="search-table-center-row">
						<div class="left">
							<label class="label-form">Título:</label>
						</div>
						<div class="right">
							<p><input type="text" name="titleRelease" value="{{ isset($titleRelease) ? $titleRelease : '' }}" class="input-text-search" id="input-search" placeholder="Ingrese un título"></p>
						</div>
					</div>
					<div class="search-table-center-row">
						<div class="left">
							<label class="label-form">Descripción:</label>
						</div>
						<div class="right">
							<p><input type="text" name="content" value="{{ isset($content) ? $content : '' }}" class="input-text-search" id="input-search" placeholder="Ingrese una descripción"></p>
						</div>
					</div>
					<div class="search-table-center-row">
						<div class="left">
							<label class="label-form">Rango de fechas:</label>
						</div>
						<div class="right-date">
							<p>
								<input type="text" name="mindate" value="{{ isset($mindate) ? date('d-m-Y',strtotime($mindate)) : '' }}" step="1" class="input-text-date datepicker" placeholder="Desde"> - <input type="text" name="maxdate" value="{{ isset($maxdate) ? date('d-m-Y',strtotime($maxdate)) : '' }}" step="1" class="input-text-date datepicker" placeholder="Hasta">
							</p>
						</div>
					</div>
				</div>
			</center>
			<center>
				<button class="btn btn-search" type="submit"><span class="icon-search"></span> Buscar</button>
			</center>
		{!! Form::close() !!}
	</center>
	<br>
	@if(count($releases) > 0)
		@foreach($releases as $banner)
		 <div class="container-blocks-all">
			<div id="index-container">
				<div class="releases-main">
					<div class="releases-one">
						<div class="releases-one-one">
							<img src="{{ url('images/logo-inicio.jpg') }}" class="releases-one-one-img">
						</div>
						<div class="releases-one-two">
							<center>
								<br><br>
								<label class="releases-one-two-label">{{ $banner->title }}</label>
							</center>
						</div>
					</div>
					<div class="releases-two">
						<div class="releases-two-one">
							<p class="releases-two-one-p">
								{!! nl2br($banner->content) !!}
							</p>
						</div>
					</div>
					<br><br>
				</div>
			</div>
		</div>
		<hr>
		@endforeach
		  <center>
		  	{{ $releases->appends([
				  		'titleRelease'=> $titleRelease,
						'content'	=> $content,
						'mindate' 	=> $mindate,
						'maxdate' 	=> $maxdate
					])->render() }}
		  </center>
	@else
		<div id="not-found" style="display:block;">Resultado no encontrado</div>
	@endif
@endsection
@section('scripts')
<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
<script src="{{ asset('js/jquery-ui.js') }}"></script>
<script src="{{ asset('js/jquery.numeric.js') }}"></script>
<script src="{{ asset('js/datepicker.js') }}"></script>
	<script>
		$(document).ready(function()
		{
			$(function() 
			{
				$( ".datepicker" ).datepicker({ maxDate: 0, dateFormat: "dd-mm-yy" });
			});
		});
	</script>
@endsection