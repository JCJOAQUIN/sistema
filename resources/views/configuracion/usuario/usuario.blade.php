@extends('layouts.layout')
@section('title','Usuarios')
	<script src="http://code.jquery.com/jquery-1.11.0.min.js"></script>

<script>
$(document).ready(function(){
	$("#btn-alta-user").click(function(){
		$("#container-alta").stop(true,true).toggle("slow");
		$('#btn-alta-user').toggleClass('sub-block-active');
	});

	$("#btn-cambio-user").click(function(){
		$("#container-cambio").stop(true,true).toggle("slow");
		$('#btn-cambio-user').toggleClass('sub-block-active');
	});
});
</script>

@section('content')
	
	<div class="container-blocks-all">
		<div class="title-config">
			<h1>usuarios</h1>
		</div>
		<center>
			<i style="color: #B1B1B1">En este m&oacute;dulo podr&aacute; crear, eliminar y actualizar cuentas del personal. Por favor verifique que todos los datos sean correctos.</i>
		</center>
		<hr>
		<h4>Acciones: </h4>
		<div class="container-sub-blocks">
			
			<div id="btn-alta-user" class="sub-block">Alta</div>
			<div id="btn-baja-user" class="sub-block">Baja</div>
			<div id="btn-cambio-user" class="sub-block">Cambios</div>
		</div>
		<!-- FORMULARIO ALTA -->
		<form action="#" method="post" id="container-alta" style="display: none;">
			<div class="form-container">
				
					<div class="div-form-group">
					<label class="label-form">Nombre(s)</label>
					<input type="text" name="name" class="input-text"><br><br>
					<label class="label-form">Apellido Paterno</label><br>
					<input type="text" name="name" class="input-text"><br><br>
					<label class="label-form">Apellido Materno (Opcional)</label><br>
					<input type="text" name="name" class="input-text"><br><br>
					<label class="label-form">Seleccione una opci&oacute;n</label><br><br>
					
					<input type="radio" name="sexo" id="hombre">
					<label for="hombre">Hombre</label>
			
					<input type="radio" name="sexo" id="mujer">
					<label for="mujer">Mujer</label> 
					
					<br><br><br>

				</div>
				<div class="div-form-group">
					<label class="label-form">Tel&eacute;fono (Opcional)</label><br>
					<input type="text" name="name" placeholder="10 d&iacute;gitos" class="input-text"><br><br>
					<label class="label-form">Correo Electr&oacute;nico</label> <br>
					<input type="text" name="name" class="input-text" placeholder="ejemplo@ejemplo.com"><br><br>
					<label class="label-form">Agregue el rol</label> <br>
					<input type="text" name="name" class="input-text" placeholder="Teclea para buscar..."><br><br>
					<label class="label-form">Agregue la empresa</label> <br>
					<input type="text" name="name" class="input-text" placeholder="Teclea para buscar..."><br><br>
				</div>
				<div class="form-container">
					<input class="btn btn-red" type="submit" name="enviar" value="CREAR CUENTA"> 
					<input class="btn" type="button" name="borra" value="Borrar campos">
				</div>
			</div>
		</form>
<!-- ----------------------------- FORMULARIO BAJA ----------------------------------- -->
		<center>
			<form id="container-cambio">
				<div class="container-search">
					<br><label class="label-form">Buscar usuario</label><br><br>
					<center>
						<input type="tex" name="role" class="input-text" style="display: inline-block;" placeholder="Escribe un nombre..."><button type="submit" class="btn" style="background: #FFF9EC;"> <span class="icon-search"></span></button>
					</center><br><br>
				</div>
			</form>
		</center>
		<br>
		<div class="table-responsive">
			<table class="table">
			<thead>
				<tr>
					<th>ID</th>
					<th>Nombre</th>
					<th>Correo Electr&oacute;nico</th>
					<th></th>
				</tr>	
			</thead>	
			<tbody>
				<tr>
					<td>1</td>
					<td>Misael</td>
					<td>misael_serena@homail.com</td>
					<td><input type="button" class="btn btn-red" name="edit" value="ELIMINAR"></td>
				</tr>
				<tr>
					<td>2</td>
					<td>Misael</td>
					<td>misael_serena@homail.com</td>
					<td><input type="button" class="btn btn-red" name="edit" value="ELIMINAR"></td>
				</tr>
				<tr>
					<td>3</td>
					<td>Misael</td>
					<td>misael_serena@homail.com</td>
					<td><input type="button" class="btn btn-red" name="edit" value="ELIMINAR"></td>
				</tr>
				<tr>
					<td>4</td>
					<td>Misael</td>
					<td>misael_serena@homail.com</td>
					<td><input type="button" class="btn btn-red" name="edit" value="ELIMINAR"></td>
				</tr>
			</tbody>
		</table>
		</div>
<!-- --------------- FORMULARIO CAMBIO ---------------------- -->
		<center>
			<form id="container-cambio">
				<div class="container-search">
					<br><label class="label-form">Buscar usuario</label><br><br>
					<center>
						<input type="tex" name="role" class="input-text" style="display: inline-block;" placeholder="Escribe un nombre..."><button type="submit" class="btn" style="background: #FFF9EC;"> <span class="icon-search"></span></button>
					</center><br><br>
				</div>
			</form>
		</center>
		<br>
		<div class="table-responsive">
			<table class="table">
			<thead>
				<tr>
					<th>ID</th>
					<th>Nombre</th>
					<th>Correo Electr&oacute;nico</th>
					<th></th>
				</tr>	
			</thead>	
			<tbody>
				<tr>
					<td>1</td>
					<td>Misael</td>
					<td>misael_serena@homail.com</td>
					<td><input type="button" class="btn btn-green" name="edit" value="EDITAR"></td>
				</tr>
				<tr>
					<td>2</td>
					<td>Misael</td>
					<td>misael_serena@homail.com</td>
					<td><input type="button" class="btn btn-green" name="edit" value="EDITAR"></td>
				</tr>
				<tr>
					<td>3</td>
					<td>Misael</td>
					<td>misael_serena@homail.com</td>
					<td><input type="button" class="btn btn-green" name="edit" value="EDITAR"></td>
				</tr>
				<tr>
					<td>4</td>
					<td>Misael</td>
					<td>misael_serena@homail.com</td>
					<td><input type="button" class="btn btn-green" name="edit" value="EDITAR"></td>
				</tr>
			</tbody>
		</table>
		</div>
		

		<form action="#" method="post" id="container-cambio" style="display: none;">
			<div class="form-container">
				<div class="div-form-group">
					<label class="label-form">Nombre(s)</label>
					<input type="text" name="name" class="input-text" value="Ejemplo"><br><br>
					<label class="label-form">Apellido Paterno</label><br>
					<input type="text" name="name" class="input-text" value="Ejemplo"><br><br>
					<label class="label-form">Apellido Materno (Opcional)</label><br>
					<input type="text" name="name" class="input-text" value="Ejemplo"><br><br>
					<label class="label-form">Seleccione una opci&oacute;n</label><br><br>
					<input type="radio" name="sexo-cambio" id="hombre-cambio">
					<label for="hombre-cambio">Hombre</label>
					<input type="radio" name="sexo-cambio" id="mujer-cambio">
					<label for="mujer-cambio">Mujer</label> 
					<br><br><br>
				</div>
				<div class="div-form-group">
					<label class="label-form">Tel&eacute;fono (Opcional)</label><br>
					<input type="text" name="name" placeholder="10 d&iacute;gitos" class="input-text"><br><br>
					<label class="label-form">Correo Electr&oacute;nico</label> <br>
					<input type="text" name="name" class="input-text" placeholder="ejemplo@ejemplo.com" value="Ejemplo"><br><br>
					<label class="label-form">Agregue el rol</label> <br>
					<input type="text" name="name" class="input-text" placeholder="Teclea para buscar..." value="Ejemplo"><br><br>
					<label class="label-form">Agregue la empresa</label> <br>
					<input type="text" name="name" class="input-text" placeholder="Teclea para buscar..." value="Ejemplo"><br><br>
				</div>
				<div class="form-container">
					<input class="btn btn-red" type="submit" name="enviar" value="CREAR CUENTA"> 
					<input class="btn" type="button" name="borra" value="Borrar campos">
				</div>
			</div>
		</form>
		
	</div>
	
@endsection
