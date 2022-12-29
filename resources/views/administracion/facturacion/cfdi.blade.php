@extends('layouts.child_module')
@php	
	$cfdi_version = env('CFDI_VERSION','3_3');
@endphp

@section('data')
	@if(isset($bill))
		@component('components.forms.form', ["attributeEx" => "method=\"POST\" id=\"container-factura\" action=\"".route('bill.cfdi.save.saved',$bill->idBill)."\""])
	@else
		@component('components.forms.form', ["attributeEx" => "method=\"POST\" id=\"container-factura\" action=\"".route('bill.cfdi.save')."\""])
	@endif
			@include('administracion.facturacion.cfdi_form')
		@endcomponent
	@include('administracion.facturacion.cfdi_modals')
@endsection
@section('scripts')
	<script type="text/javascript" src="{{ asset('js/moment.min.js') }}"></script>
	<script src="{{ asset('js/daterangepicker.js') }}"></script>
	<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
	<link rel="stylesheet" type="text/css" href="{{ asset('css/daterangepicker.css') }}" />
	<script src="{{ asset('js/jquery-ui.js') }}"></script>
	<script src="{{ asset('js/datepicker.js') }}"></script>
	<script src="{{ asset('js/jquery.numeric.js') }}"></script>
	@include('administracion.facturacion.cfdi_script')
@endsection
