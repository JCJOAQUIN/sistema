@if($status == "Revisar" || $status == "Autorizar" || $status == "Entregar" || $status == "Timbrar" || $status == "Solicitar")
@php
	$date1    = strtotime($date);
	$newdate  = date('d/m/Y',$date1);
@endphp
	<div>
		@if($status == "Timbrar")
			<p style="font-size: 20px; width: 80%; padding: 15px;">
				Estimado {{ $name.',' }} acaba de recibir un CDFI pendiente de timbrar.
			</p>
		@elseif($status == "Solicitar")
			<p style="font-size: 20px; width: 80%; padding: 15px;">
				Estimado {{ $name.',' }} se require Equipo de Cómputo para {{ $requestUser }}
			</p>
		@else
			<p style="font-size: 20px; width: 80%; padding: 15px;">
				Estimado {{ $name.',' }} acaba de recibir una nueva Solicitud de {{ $kind }} con los siguientes datos:
			</p>
		@endif
    	<div style="width:80%; border:1px solid #c0c0c0; margin:0 auto; font-size:20px;">
    		@if($status == "Revisar" || $status == "Autorizar" || $status == "Entregar")
	    		<p style=" width: 80%; padding:5px; margin: 0 auto;">
			    	<label><b>Solicitante: </b>{{ $requestUser }}</label><br>
			    	<label><b>Acción: </b>{{ $status }}</label><br>
			    	<label><b>Fecha: </b>{{ $newdate }}</label>
			    </p><br>
		    @endif
		    <center>
		    	<a href="{{ $url }}" style="background-color: #e61860;border: 1px solid transparent;border-radius: 3px; color: white; cursor: pointer;display: inline-block;font-size: 20px;font-weight: 400;margin: 3px;padding: 6px 12px; text-align: center;text-decoration : none;">Ir a la plataforma</a>
		    </center>
		    <br>
	    </div>
	    <br>
	    
	</div>

@elseif($status == "AUTORIZADA" || $status == "RECHAZADA")
	@php
		$date1    = strtotime($date);
		$newdate  = date('d/m/Y',$date1);
	@endphp
	<div>
		
		<p style="font-size: 20px; width: 90%; padding: 15px;">
			Estimado {{ $name.',' }} el estado de su Solicitud de {{ $kind }} ha cambiado.
		</p>
    	<div style="width:80%; border:1px solid #c0c0c0; margin:0 auto; font-size:20px;">
    		<p style=" width: 80%; padding:5px; margin: 0 auto;">
		    	<label><b>Solicitante: </b>{{ $name }}</label><br>
		    	<label><b>Estado: </b>{{ $status }}</label><br>
		    	<label><b>Fecha: </b>{{ $newdate }}</label>
		    </p><br>
		    <center>
		    	<a href="{{ $url }}" style="background-color: #e61860;border: 1px solid transparent;border-radius: 3px; color: white; cursor: pointer;display: inline-block;font-size: 20px;font-weight: 400;margin: 3px;padding: 6px 12px; text-align: center;text-decoration : none;">Ir a la plataforma</a>
		    </center>
		    <br>
	    </div>
	    <br>
	    <center><label style="color: #c0c0c0; font-size: 18px;"></label></center>
	</div>

@elseif($status == "Pendiente")
	@php
		$date1    = strtotime($date);
		$newdate  = date('d/m/Y',$date1);
	@endphp
	<div>
		
		<p style="font-size: 20px; width: 90%; padding: 15px;">
			Estimado {{ $name.',' }} tiene una Solicitud de {{ $kind }} Pendiente de Pago.
		</p>
    	<div style="width:80%; border:1px solid #c0c0c0; margin:0 auto; font-size:20px;">
    		<p style=" width: 80%; padding:5px; margin: 0 auto;">
		    	<label><b>Solicitante: </b>{{ $requestUser }}</label><br>
		    	<label><b>Estado: </b>{{ $status }}</label><br>
		    	<label><b>Fecha: </b>{{ $newdate }}</label>
		    </p><br>
		    <center>
		    	<a href="{{ $url }}" style="background-color: #e61860;border: 1px solid transparent;border-radius: 3px; color: white; cursor: pointer;display: inline-block;font-size: 20px;font-weight: 400;margin: 3px;padding: 6px 12px; text-align: center;text-decoration : none;">Ir a la plataforma</a>
		    </center>
		    <br>
	    </div>
	    <br>
	    <center><label style="color: #c0c0c0; font-size: 18px;"></label></center>
	</div>
@elseif($status == "ENTREGADO")
	@php
		$date1    = strtotime($date);
		$newdate  = date('d/m/Y',$date1);
	@endphp
	<div>
		
		<p style="font-size: 20px; width: 90%; padding: 15px;">
			Estimado {{ $name.',' }} los artículos su Solicitud de {{ $kind }} han sido entregados.
		</p>
    	<div style="width:80%; border:1px solid #c0c0c0; margin:0 auto; font-size:20px;">
    		<p style=" width: 80%; padding:5px; margin: 0 auto;">
		    	<label><b>Solicitante: </b>{{ $name }}</label><br>
		    	<label><b>Estado: </b>{{ $status }}</label><br>
		    	<label><b>Fecha: </b>{{ $newdate }}</label>
		    </p><br>
		    <center>
		    	<a href="{{ $url }}" style="background-color: #e61860;border: 1px solid transparent;border-radius: 3px; color: white; cursor: pointer;display: inline-block;font-size: 20px;font-weight: 400;margin: 3px;padding: 6px 12px; text-align: center;text-decoration : none;">Ir a la plataforma</a>
		    </center>
		    <br>
	    </div>
	    <br>
	    <center><label style="color: #c0c0c0; font-size: 18px;"></label></center>
	</div>
@endif