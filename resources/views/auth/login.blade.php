<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="user-scalable=no, initial-scale=1, maximum-scale=1, minimum-scale=1, width=device-width, height=device-height, target-densitydpi=device-dpi" />
	<link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
	<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
	<link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
	<link rel="manifest" href="/site.webmanifest">
	<link rel="mask-icon" href="/safari-pinned-tab.svg" color="#4b4b4b">
	<meta name="msapplication-TileColor" content="#ffffff">
	<meta name="theme-color" content="#ffffff">
	<meta name="csrf-token" content="{{ csrf_token() }}">
	<title>SISTEMA</title>
	<link rel="stylesheet" type="text/css" href="{{ asset('css/validator/theme-default.min.css') }}">
	<link rel="stylesheet" type="text/css" href="{{ asset('css/style.css') }}">
	<link rel="stylesheet" type="text/css" href="{{ asset('css/bootstrap.min.css') }}">
	<link rel="stylesheet" type="text/css" href="{{ asset('css/styles-login.css?v=1.0') }}">
	<script src="https://code.jquery.com/jquery-1.11.0.min.js"></script>
	<script>
		$(document).ready(function()
		{
			$(document).on('click','#show-hide-passwd',function(e)
			{
				e.preventDefault();
				var current = $(this).attr('action');
				if(current == 'hide')
				{
					$(this).prev().attr('type','text');
					$(this).removeClass('glyphicon-eye-open').addClass('glyphicon-eye-close').attr('action','show');
				}
				if(current == 'show')
				{
					$(this).prev().attr('type','password');
					$(this).removeClass('glyphicon-eye-close').addClass('glyphicon-eye-open').attr('action','hide'); 
				}
			})
		})
	</script>
</head> 
<body class="fondo">
	<div class="container-login">
		<div class="div-log">
			<form method="POST" action="{{ route('login') }}" class="login-form">
				@csrf
				<center>
					
					<br><br>
					<label class="title-login"><b>- INICIO DE SESI&Oacute;N -</b></label>
					<br><br>
				</center>
				<div class="div-login">
					<span class="glyphicon glyphicon-user"></span>
					<input placeholder="Usuario" id="email" type="email" class="input-email{{ $errors->has('email') ? ' error is-invalid' : '' }}" name="email" value="{{ old('email') }}" required autofocus>
					@if ($errors->has('email'))
						<span class="help-block form-error invalid-feedback">
							<strong>{{ $errors->first('email') }}</strong>
						</span>
					@endif
				</div>
				<br><br>
				<div class="div-login">
					<span class="glyphicon glyphicon-lock"></span>
					<input placeholder="Contrase&ntilde;a" id="password" class="input-pass{{ $errors->has('password') ? ' error is-invalid' : '' }}" type="password" name="password" required/>
					<span id="show-hide-passwd" action="hide" class="glyphicon glyphicon glyphicon-eye-open"></span>
					@if ($errors->has('password'))
						<span class="help-block form-error invalid-feedback">
							<strong>{{ $errors->first('password') }}</strong>
						</span>
					@endif
				</div><br>
				<div class="div-login" style="border-bottom: 0;">
					<input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}> Recordar datos
				</div>
				<center>
					<br>
					<button type="submit" class="btn btn-danger">
						INICIAR SESI&Oacute;N
					</button>
					<br><br>
					<a style="color: black; font: Raleway, sans-serif;" href="{{ route('password.request') }}">¿Olvidaste la contrase&ntilde;a?</a>
				</center>
				<br><br>
				<div class="footer-login">
					
				</div>
			</form>
		</div>
	</div>
</body>
</html>