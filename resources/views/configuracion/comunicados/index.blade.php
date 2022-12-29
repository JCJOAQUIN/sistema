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
			<a class=" sub-block" href="{{ url('releases/history') }}">Historial</a>
		</div>
		@php
	  		$today 		= date('Y-m-d');
			$releasesDate = strtotime('-30 day',strtotime($today));
			$releasesDate = date('Y-m-d',$releasesDate);
	  	@endphp
		@foreach(App\Releases::whereBetween('date',[''.$releasesDate.' '.date('00:00:00').'',''.$today.' '.date('23:59:59').''])->orderBy('idreleases','desc')->get() as $banner)
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
	
@endsection