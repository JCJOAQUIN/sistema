@extends('layouts.child_module')
@section('data')
	<br>
	<center>A continuación podrá verificar la información de la solicitud antes de continuar con el proceso:</center>
	<br>
	<div class="profile-table-center">
		<div class="profile-table-center-header">
			Detalles de la Solicitud
		</div>
		<div class="profile-table-center-row">
			<div class="left">
				Folio:
			</div>
			<div class="right">
				<p>{{ $request->folio }}</p>
			</div>
		</div>
		<div class="profile-table-center-row">
			<div class="left">
				Título y fecha:
			</div>
			<div class="right">
				<p>{{$request->otherincome->title }} - {{ $request->otherincome->datetitle }}</p>
			</div>
		</div>
		<div class="profile-table-center-row">
			<div class="left">
				Fiscal:
			</div>
			<div class="right">
				<p>@if($request->taxPayment == 1) Si @else No @endif</p>
			</div>
		</div>
		<div class="profile-table-center-row">
			<div class="left">
				Solicitante:
			</div>
			<div class="right">
				<p>{{ $request->requestUser->fullName() }}</p>
			</div>
		</div>
		<div class="profile-table-center-row">
			<div class="left">
				Elaborado por:
			</div>
			<div class="right">
				<p>{{ $request->elaborateUser->fullName() }}</p>
			</div>
		</div>
		<div class="profile-table-center-row">
			<div class="left">
				Empresa:
			</div>
			<div class="right">
				<p>{{ $request->requestEnterprise->name }}</p>
			</div>
		</div>
		<div class="profile-table-center-row">
			<div class="left">
				Proyecto:
			</div>
			<div class="right">
				<p>{{ $request->requestProject->proyectName }}</p>
			</div>
		</div>
		<div class="profile-table-center-row">
			<div class="left">
				Tipo:
			</div>
			<div class="right">
				<p>{{ $request->otherIncome->typeIncome() }}</p>
			</div>
		</div>
		<div class="profile-table-center-row no-border">
			<div class="left">
				Prestatario:
			</div>
			<div class="right">
				<p>{{ $request->otherIncome->borrower }}</p>
			</div>
		</div>
	</div>
	
	<center>
		<strong>CONCEPTOS</strong>
	</center>
	<div class="divisor">
		<div class="gray-divisor"></div>
		<div class="orange-divisor"></div>
		<div class="gray-divisor"></div>
	</div>
	<div class="form-container">
		<div class="table-responsive table-striped">
			<table class="table">
				<thead class="thead-dark">
					<th>#</th>
					<th>Cantidad</th>
					<th>Unidad</th>
					<th>Descripci&oacute;n</th>
					<th>Precio Unitario</th>
					<th>IVA</th>
					<th>Impuesto Adicional</th>
					<th>Retenciones</th>
					<th>Importe</th>
				</thead>
				<tbody id="body">
					@php
						$countConcept = 1;
					@endphp
					@foreach($request->otherIncome->details as $detail)
						<tr>
							<td>
								{{ $countConcept }}
							</td>
							<td>
								{{ $detail->quantity }}
							</td>
							<td>
								{{ $detail->unit }}
							</td>
							<td>
								{{ $detail->description }}
							</td>
							<td>
								$ {{ number_format($detail->unit_price,2) }}
							</td>
							<td>
								$ {{ number_format($detail->tax,2) }}
							</td>
							<td>
								$ {{ number_format($detail->total_taxes,2) }}
							</td>
							<td>
								$ {{ number_format($detail->total_retentions,2) }}
							</td>
							<td>
								$ {{ number_format($detail->total,2) }}
							</td>
						</tr>
						@php
							$countConcept++;
						@endphp
					@endforeach
				</tbody>
			</table>
		</div>
		<br>
	</div>
	<div class="totales2">
		<div class="totales" style="margin-left: 10px;"> 
			<table>
				<tr>
					<td>
						<label class="label-form">Subtotal:</label>
					</td>
					<td>
						<input placeholder="0" readonly class="input-table" type="text" name="subtotal" value="$ {{ number_format($request->otherincome->subtotal,2) }}">
					</td>
				</tr>
				<tr>
					<td>
						<label class="label-form">Impuesto Adicional:</label>
					</td>
					<td>
						<input placeholder="0" readonly class="input-table" type="text" name="amountAA" value="$ {{ number_format($request->otherIncome->total_taxes,2) }}" >
					</td>
				</tr>
				<tr>
					<td>
						<label class="label-form">Retenciones:</label>
					</td>
					<td>
						<input placeholder="$0.00" readonly class="input-table" type="text" name="amountR" value="$ {{ number_format($request->otherIncome->total_retentions,2) }}" >

					</td>
				</tr>
				<tr>
					<td>
						<label class="label-form">IVA: </label>
					</td>
					<td>
						<input placeholder="0" readonly class="input-table" type="text" name="totaliva" value="$ {{ number_format($request->otherIncome->total_iva,2) }}">
					</td>
				</tr>
				<tr>
					<td>
						<label class="label-form">TOTAL:</label>
					</td>
					<td>
						<input id="input-extrasmall" placeholder="0" readonly class="input-table" type="text" name="total" value="$ {{ number_format($request->otherIncome->total,2) }}">
					</td>
				</tr>
			</table>
		</div> 
	</div>
	<br><br><br>
	<center>
		<strong>CONDICIONES DE PAGO</strong>
	</center>
	<div class="divisor">
		<div class="gray-divisor"></div>
		<div class="orange-divisor"></div>
		<div class="gray-divisor"></div>
	</div>
	<div>
		<table class="employee-details">
			<tbody>
				<tr>
					<td><b>Referencia/Número de factura:</b></td>
					<td><label>{{ $request->otherincome->reference }}</label></td>
				</tr>
				<tr>
					<td><b>Tipo de moneda:</b></td>
					<td><label>{{ $request->otherincome->type_currency }}</label></td>
				</tr>
				<tr>
					<td><b>Fecha de pago:</b></td>
					<td><label>{{ $request->PaymentDate->format('d-m-Y') }}</label></td>
				</tr>
				<tr>
					<td><b>Forma de pago:</b></td>
					<td><label>{{ $request->otherincome->pay_mode }}</label></td>
				</tr>
				<tr>
					<td><b>Estado  de factura:</b></td>
					<td><label>{{ $request->otherincome->status_bill }}</label></td>
				</tr>
				<tr>
					<td><b>Importe a pagar:</b></td>
					<td><label>${{ number_format($request->otherincome->total,2) }}</label></td>
				</tr>
			</tbody>
		</table>
	</div>
	<br><br><br>
	<center>
		<strong>DOCUMENTOS</strong>
	</center>
	<div class="divisor">
		<div class="gray-divisor"></div>
		<div class="orange-divisor"></div>
		<div class="gray-divisor"></div>
	</div> 	
	<div class="table-responsive table-striped">
		<table class="table">
			<thead class="thead-dark">
				<th>Nombre</th>
				<th>Archivo</th>
				<th>Fecha</th>
			</thead>
			<tbody>
				@if(count($request->otherincome->documents)>0)
					@foreach($request->otherincome->documents as $doc)
						<tr>
							<td>
								{{ $doc->name }}
							</td>
							<td>
								<a target="_blank" href="{{ url('docs/other-income/'.$doc->path) }}" style="text-decoration: none; color: black;">{{ $doc->path }}</a>
							</td>
							<td>
								{{ $Carbon\Carbon::parse($doc->created_at)->format('d-m-Y') }}
							</td>
						</tr>
					@endforeach
				@else
					<tr>
						<td colspan="3" width="10%">
							NO HAY DOCUMENTOS
						</td>
					</tr>
				@endif
			</tbody>
		</table>
	</div>

	{!! Form::open(['route' => ['other-income.review.update', $request->folio], 'method' => 'put']) !!}
		<div class="form-container">
			<center>
				<p>
					<label class="label-form" id="label-inline" >¿Desea aprobar ó rechazar la solicitud?</label><br><br>
					<input type="radio" name="status" id="aprobar" value="4">
					<label for="aprobar" class="approve"><span class="icon-checkmark"></span> Aprobar</label>
					<input type="radio" name="status" id="rechazar" value="6">
					<label for="rechazar" class="refuse"><span class="icon-cross"></span> Rechazar</label>
				</p>
			</center>
		</div>
		<div id="aceptar">
			<div class="form-container">
				<label class="label-form">Comentarios (opcional)</label>
				<textarea class="text-area" cols="90" rows="10" name="commentAccept"></textarea>
			</div>
		</div>
		<div id="rechaza">
			<div class="form-container">
				<label class="label-form">Comentarios (opcional)</label>
				<textarea class="text-area" cols="90" rows="10" name="commentReject"></textarea>
			</div>
		</div>
		<center>
			<p>
				<input class="btn btn-red" type="submit" name="enviar" value="ENVIAR SOLICITUD">
				<a
					@if(isset($option_id)) 
						href="{{ url(getUrlRedirect($option_id)) }}" 
					@else 
						href="{{ url(getUrlRedirect($child_id)) }}" 
					@endif
				>
					<button class="btn" type="button">REGRESAR</button>
				</a>
			</p>
		</center>
		<br>
	{!! Form::close() !!}
@endsection
@section('scripts')
	<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
	<script src="{{ asset('js/jquery-ui.js') }}"></script>
	<script src="{{ asset('js/jquery.numeric.js') }}"></script>
	<script src="{{ asset('js/datepicker.js') }}"></script>
	<script>
		swal({
			icon: '{{ asset(getenv('LOADING_IMG')) }}',
			button: false,
			timer: 1000,
		});
		$.validate(
		{
			form: '#container-alta',
			onSuccess : function($form)
			{
				if($('[name="status"]').is(':checked'))
				{
					swal("Cargando",
					{
						icon: '{{ asset(getenv('LOADING_IMG')) }}',
						button: false,
						closeOnClickOutside: false,
						closeOnEsc: false
					});
					return true;
				}
				else
				{
					swal('', 'Debe seleccionar al menos un estado', 'error');
					return false;
				}
			}
		});
		$(document).ready(function()
		{
			$(document).on('change','[name="status"]',function()
			{
				if ($('[name="status"]:checked').val() == "4") 
				{
					$("#rechaza").slideUp("slow");
					$("#aceptar").slideToggle("slow").addClass('form-container').css('display','block');
				}
				else if ($('[name="status"]:checked').val() == "6") 
				{
					$("#aceptar").slideUp("slow");
					$("#rechaza").slideToggle("slow").addClass('form-container').css('display','block');
				}
			});
		});
	</script>
@endsection
