@extends('layouts.child_module')
@section('data')

@include('administracion.presupuestos.form_file',['route'=>'BreakdownWages.create.send'])

@endsection
