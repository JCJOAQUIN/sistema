@extends('layouts.child_module')
@section('data')

@include('administracion.presupuestos.form_file',['route'=>'supplies.create.send'])

@endsection
