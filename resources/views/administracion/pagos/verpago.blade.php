@extends('layouts.child_module')

@switch($request->kind)
	@case(1)
		@include('administracion.pagos.compra')
		@break

	@case(2)
		@include('administracion.pagos.complementonomina')
		@break

	@case(3)
		@include('administracion.pagos.gasto')
		@break

	@case(5)
		@include('administracion.pagos.prestamo')
		@break

	@case(8)
		@include('administracion.pagos.recurso')
		@break

	@case(9)
		@include('administracion.pagos.reembolso')
		@break

	@case(11)
		@include('administracion.pagos.ajuste')
		@break

	@case(12)
		@include('administracion.pagos.prestamoempresa')
		@break

	@case(13)
		@include('administracion.pagos.compraempresa')
		@break

	@case(14)
		@include('administracion.pagos.grupos')
		@break

	@case(15)
		@include('administracion.pagos.movimientosempresa')
		@break
@endswitch

@section('pay-form')
	@component('components.labels.title-divisor')
		@slot('classEx')
			mt-12
		@endslot
		DATOS DEL PAGO
	@endcomponent
	@php
		$buttonExtra	=	"";
		foreach (App\DocumentsPayments::where('idpayment',$payment->idpayment)->get() as $doc)
		{
			$buttonExtra .= html_entity_decode((String)view("components.buttons.button",
			[
				"variant"		=>	"dark-red",
				"buttonElement"	=>	"a",
				"label"			=>	"PDF",
				"attributeEx"	=>	"type=\"button\" target=\"_blank\" title=\"".$doc->path."\""." href=\"".asset('/docs/payments/'.$doc->path)."\"",
			]));
		}
		$modelTable	=
		[
			"Empresa"						=>	isset($payment->enterprise->name) && $payment->enterprise->name!="" ? $payment->enterprise->name : "---",
			"Cuenta"						=>	isset($payment->accounts->account) && $payment->accounts->account!="" ? $payment->accounts->account.' '.$payment->accounts->description : "---",
			"Importe"						=>	isset($payment->amount) && $payment->amount!="" ? "$".number_format($payment->amount,2) : "---",
			"Fecha del pago"				=>	isset($payment->paymentDate) && $payment->paymentDate!="" ? $payment->paymentDate : "---",
			"Tasa de cambio"				=>	isset($payment->exchange_rate) && $payment->exchange_rate!="" ? $payment->exchange_rate : "---",
			"Descripción de tasa de cambio"	=>	isset($payment->exchange_rate_description) && $payment->exchange_rate_description!="" ? $payment->exchange_rate_description : "---",
			"Comprobante de pago"			=>	$buttonExtra!="" ? $buttonExtra : "---",
			"Comentarios"					=>	isset($payment->commentaries) && $payment->commentaries!="" ? $payment->commentaries : 'Sin comentarios.'
		];
	@endphp
	@component('components.templates.outputs.table-detail-single', ["modelTable"	=>	$modelTable])
		@slot('attributeEx')
			employee-details
		@endslot
	@endcomponent
	<div class="text-center">
		@php
			$href	=	isset($option_id) ? url(getUrlRedirect($option_id)) : url(getUrlRedirect($child_id));
		@endphp
		@component('components.buttons.button', ["variant" => "reset", "attributeEx" => "href=\"".$href."\"", "buttonElement" => "a", "classEx" => "load-actioner", "label" => "REGRESAR"]) @endcomponent
	</div>
	@component('components.modals.modal')
		@slot('id')
			viewPayment
		@endslot
		@slot('attributeEx')
			tabindex="-1"
		@endslot
		@slot('modalBody')
			@component('components.labels.title-divisor')
				DATOS DEL PAGO
			@endcomponent
		@endslot
		@slot('modalFooter')
			@component('components.buttons.button', ["variant" => "red"])
				@slot('attributeEx')
				type="button"
				data-dismiss="modal"
				@endslot
				<span class="icon-x"></span> Cerrar
			@endcomponent
		@endslot
	@endcomponent
@endsection

@section('scripts')
	<script>
		$(document).ready(function() 
		{
			$('#amount').text($('#restaTotal').val());
			$(document).on('click', '.enviar', function (e)
			{
				documento = $('input[name="path"]').val();
				if (documento == '') 
				{
					e.preventDefault();
					form = $(this).parents('form');
					swal({
						title: "¿Desea enviar el pago sin comprobante?",
						icon: "warning",
						buttons: ["Cancelar","OK"],
					})
					.then((isConfirm) =>
					{
						if(isConfirm)
						{
							form.submit();
						}
					});
				}
				else
				{
					form.submit();
				}
			})
			.on('click','[data-toggle="modal"]',function()
			{
				idpayment = $(this).attr('data-payment');
				$.ajax({
					type		: 'post',
					url			: '{{ route("payments.view-detail") }}',
					data		: {'idpayment':idpayment},
					success: function(data)
					{
						$('.modal-body').html(data);
					},
					error: function(data)
					{
						swal('','Sucedió un error, por favor intente de nuevo.','error');
						$('.modal-body').hide();
					}
				});
			});
		});
	</script>
@append
