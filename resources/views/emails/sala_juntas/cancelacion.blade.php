
	<div>
	<br><br>
		<div style="width:80%; border:1px solid #c0c0c0; margin:0 auto; font-size:20px;">
			<br>
			<center> <p style="color: #e61860; font-family: Arial;"><b>RESERVACIÓN CANCELADA</b></p></center>
			<p style=" width: 80%; padding:5px; margin: 0 auto;font-family: Arial;">
				<b>{{ auth()->user()->fullName() }}</b> ha cancelado su reservación:
			</p>
			<br>
			<p style=" width: 90%; padding:5px; margin: 0 auto;font-family: Arial">
				<b>Motivo de Cancelación:</b> {{ $newValues->cancel_description }}
			</p>
			<br>
			<p style=" width: 90%; padding:5px; margin: 0 auto;font-family: Arial">
				<b>
					Reservación:
				</b>
			</p>
			<p style=" width: 90%; padding:5px; margin: 0 auto;font-family: Arial">
				<b>Sala:</b> {{ $newValues->boardroom->name }}
			</p>
			<p style=" width: 90%; padding:5px; margin: 0 auto;font-family: Arial">
				<b>Solicitante:</b> {{ $newValues->requestUser->fullName() }}
			</p>
			<p style=" width: 90%; padding:5px; margin: 0 auto;font-family: Arial">
				<b>Inicio:</b> {{ $newValues->start->format('d-m-Y')." a las ".$newValues->start->format('H:i')." hr" }}
			</p>
			<p style=" width: 90%; padding:5px; margin: 0 auto;font-family: Arial">
				<b>Fin:</b> {{ $newValues->end->format('d-m-Y')." a las ".$newValues->end->format('H:i')." hr" }}
			</p>

		    <br>
			<center>
				<a href="{{ route('boardroom.administration.search') }}" style="font-family: Arial;background-color: #e61860;border: 1px solid transparent;border-radius: 3px; color: white; cursor: pointer;display: inline-block;font-size: 20px;font-weight: 400;margin: 3px;padding: 6px 12px; text-align: center;text-decoration : none;">Ir a la plataforma</a>
				<br>
				<br>
				<br>
				<label style="font-family: Arial;">
					Favor de no responder este correo.
				</label>
			</center>
	    </div>
	</div>
