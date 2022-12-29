@extends('layouts.child_module')
@section('data')
	@include('almacen.alta.selector')
	@switch($selected_item)
		@case(1)
			@include('almacen.alta.alta_form')
			@break
		@case(2)
			@include('almacen.alta.masiva')
			@break
		@case(3)
			@include('almacen.alta.compras')
			@break
	@endswitch
@endsection

@section('scripts')
	<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
	<script src="{{ asset('js/jquery-ui.js') }}"></script>
	<script src="{{ asset('js/jquery.numeric.js') }}"></script>
	<script src="{{ asset('js/datepicker.js') }}"></script>
	<script>
		var removeAccountsCompra = true;
		$(document).ready(function() 
		{
			$.validate(
			{
				form	: '#container-alta',
				modules	: 'security',
				onError	: function($form)
				{
					swal('', '{{ Lang::get("messages.form_error") }}', 'error');
				},
				onSuccess : function($form)
				{
					documents		= $('.path').length;
					total			= parseFloat($('input[name="total"]').val());
					total_articles	= parseFloat($('input[name="total_articles"]').val());
					countbody		= $('#body .tr-concepts').length;
					if(total_articles == "" || countbody <= 0)
					{
						swal({
							title: "Error",
							text: "Debe agregarse al menos un artículo.",
							icon: "error",
							buttons: 
							{
								confirm: true,
							},
						});
						return false;
					}
					else if (total_articles > total || total_articles < total)
					{
						swal({
							title: "Error",
							text: "La inversión de artículos no coincide con el monto del ticket/factura.",
							icon: "error",
							buttons: 
							{
								confirm: true,
							},
						});
						return false;
					}
					else if (documents > 0) 
					{
						flag = true;
						$('.path').each(function()
						{
							if($(this).val() == '')
							{
								flag = false;
							}
						});
						if(flag)
						{
							return true;
						}
						else
						{
							swal('','Por favor agregue los documentos faltantes.','error');
							return false;
						}
					}
					else
					{
						return true;
					}
				}
			});
		});
	</script>
	@include('almacen.alta.scripts_alta')
	@include('almacen.alta.scripts_masiva')
	@include('almacen.alta.scripts_compras')
@endsection
