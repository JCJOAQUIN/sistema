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
	<link rel="stylesheet" type="text/css" href="{{ asset('css/bootstrap.min.css') }}">
	<link rel="stylesheet" type="text/css" href="{{ asset('css/styles-login.css?v=1.0') }}">
	<link rel="stylesheet" type="text/css" href="{{ asset('css/style.css') }}">
	<script src="http://code.jquery.com/jquery-1.11.0.min.js"></script>
</head> 
<body class="fondo">
	<div class="container-login">
		<div class="div-log">
			<div class="card-body">
				@if (session('status'))
					<div class="alert alert-success">
						{{ session('status') }}
					</div>
				@endif
				<form method="POST" action="{{ route('password.email') }}">
					@csrf
					<div class="title">Recuperar contraseña</div>
					<div class="message">Por favor, ingrese su correo electrónico para que reciba en <br>su bandeja las instrucciones para actualizar la contraseña.</div>
					<div class="div-form-group">
						<label class="label-form">Correo Electrónico</label>
						<input id="email" type="email" class="form-control{{ $errors->has('email') ? ' error is-invalid' : '' }}" name="email" value="{{ old('email') }}" placeholder="Ingrese su correo electrónico">
						@if($errors->has('email'))
							<span class="help-block form-error invalid-feedback">
								<strong>{{ $errors->first('email') }}</strong>
							</span><br>
						@endif
						<button type="submit" class="btn btn-red send-email">
							Enviar email
						</button>
						<a href="{{ route('login') }}" class="btn" style="text-decoration: none; color: black">Regresar</a>
					</div>
				</form>
			</div>		
		</div>
	</div>
</body>
	<script src="{{ asset('js/sweetalert.min.js') }}"></script>
	<script type="text/javascript">
		@if(session()->has('alert'))
			{!!session()->get('alert')!!};
		@endif
	</script>
</html>