
	<div>
	<br><br>
		<div style="width:80%; border:1px solid #c0c0c0; margin:0 auto; font-size:20px;">
			<br>
			<center> <p style="color: #e61860; font-family: Arial;"><b>NUEVA RESERVACIÓN DE SALA</b></p></center>
			<p style=" width: 80%; padding:5px; margin: 0 auto;">
				<table style="width: 100%">
					<tbody>
						<tr>
							<td style="text-align: left; width: 50%;"><label style="font-family: Arial;"><b>Sala reservada:</label></td>
							<td style="text-align: right; width: 50%;font-family: Arial;">{{ $reservation->boardroom->name }}</td>
						</tr>
					</tbody>
				</table>
				<br>
				<table style="width: 100%">
					<tbody>
						<tr>
							<td style="text-align: left; width: 50%;"><label style="font-family: Arial;"><b>Motivo:</label></td>
							<td style="text-align: right; width: 50%;font-family: Arial;">{{ $reservation->reason }}</td>
						</tr>
					</tbody>
				</table>
				<br>
				<table style="width: 100%">
					<tbody>
						<tr>
							<td style="text-align: left; width: 50%;"><label style="font-family: Arial;"><b>Observaciones/Comentarios:</label></td>
							<td style="text-align: right; width: 50%;font-family: Arial;">{{ $reservation->observations }}</td>
						</tr>
					</tbody>
				</table>
				<br>
				<table style="width: 100%">
					<tbody>
						<tr>
							<td style="text-align: left; width: 50%;"><label style="font-family: Arial;"><b>Inicio:</label></td>
							<td style="text-align: right; width: 50%;font-family: Arial;">{{ $reservation->start->format('d-m-Y')." a las ".$reservation->start->format('H:i')." hr" }}</td>
						</tr>
					</tbody>
				</table>
				<br>
				<table style="width: 100%">
					<tbody>
						<tr>
							<td style="text-align: left; width: 50%;"><label style="font-family: Arial;"><b>Fin:</label></td>
							<td style="text-align: right; width: 50%;font-family: Arial;">{{ $reservation->end->format('d-m-Y')." a las ".$reservation->end->format('H:i')." hr" }}</td>
						</tr>
					</tbody>
				</table>
		    </p><br>
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
