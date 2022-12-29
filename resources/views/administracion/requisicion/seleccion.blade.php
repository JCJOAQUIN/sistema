@extends('layouts.child_module')

@section('data')
	
	<label class="label">Seleccione el tipo de requisición:</label>
	<div class="container-sub-blocks">
		<a href="{{ route('requisition.create.material') }}" class="sub-block">Material</a>
		<a href="{{ route('requisition.create.service') }}" class="sub-block">Servicio</a>
		<a href="{{ route('requisition.create.nomina') }}" class="sub-block">Nómina</a>
	</div>
@endsection