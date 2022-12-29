@extends('layouts.child_module')

@section('css')
	<style type="text/css">
		ul
		{
			list-style		: disc;
			padding-left	: 1rem;
		}
		form
		{
			display: inline-block;
		}
		h3
		{
			font-size	: 1.2rem;
			font-weight	: 600;
			padding		: .3rem 0;
			text-align	: center;
		}
	</style>
@endsection

@section('data')
	@component('components.labels.title-divisor') ALTA MASIVA @endcomponent
	@component("components.labels.not-found", ["variant" => "note"])
		Alta concluída.
	@endcomponent 
	@php
		$modelBody	 = [];
		$modelHead	 = ["Fila","Estatus"];

		foreach($csv as $k => $e)
		{
			$status = "<span class=\"".($e["status"] == "Guardado" || $e["status"]=="Actualizado" ? "text-blue-900" : "text-red-900" )."\"><span class=\"".($e["status"]=="Guardado" || $e["status"]=="Actualizado" ? "icon-arrow-up" : "icon-blocked" )."\"></span>".$e["status"]."</span>";
			$body 		 = 
			[
				[
					"content" =>
					[
						"label" => $k + 2
					]
				],
				[
					"content" =>
					[
						"label" => $status
					]
				]
			];
			$modelBody[] = $body;
		}
	@endphp
	@component('components.tables.alwaysvisibleTable', [
		"modelBody" => $modelBody,
		"modelHead" => $modelHead
	])
		@slot('attributeEx')
			id="table"
		@endslot
		@slot('classExBody')
			request-validate
		@endslot
		@slot('attributeExBody')
			id="body-payroll"
		@endslot
	@endcomponent
@endsection