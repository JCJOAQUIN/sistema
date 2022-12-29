@extends('layouts.child_module')

@section('data')
	@component('components.labels.title-divisor') ALTA MASIVA @endcomponent
	@component('components.labels.not-found',['variant' => 'note']) Alta concluída. @endcomponent
	@php
		$body		= [];
		$modelBody	= [];
		$modelHead	= [
			[
				["value" => "ID"],
				["value" => "Estatus"],
				["value" => "Nombre"],
				["value" => "CURP"],
				["value" => "Acción"]
			]
		];

		foreach($csv as $k => $e)
		{
			$name		= '';
			$nameTwo	= '';
			$nameThree	= '';
			if(isset($e['nombre']))
			{
				$name = $e['nombre'];
			}
			if(isset($e['apellido']))
			{
				$nameTwo = $e['apellido'];
			}
			if(isset($e['apellido2']))
			{
				$nameThree = $e['apellido2'];
			}
			$classExTr = (!in_array($e['status'], ['Actualizado', 'Nuevo']) ? "tr-red" : '');
			$body = 
			[
				"classEx" => $classExTr,
				[
					"content" =>
					[
						"label" => $e['id'] != '' ? $e['id'] : '---'
					] 
				]
			];
			if($e['status'] == 'Actualizado')
			{
				array_push($body,[
					"classEx" => "bg-gray-50",
					"content" =>
					[
						"label" 		=> $e['status']
					]
				]);
			}
			elseif($e['status'] == 'Nuevo')
			{
				array_push($body,[
					"classEx" => "bg-lime-400",
					"content" =>
					[
						"label" 		=> $e['status']
					]
				]);
			}
			else
			{
				array_push($body,[
					"classEx" => "bg-red-300",
					"content" =>
					[
						"label" 		=> $e['status']
					]
				]);
			}
			array_push($body,[
				"content" =>
				[
					"label" => $name.' '.$nameTwo.' '.$nameThree
				]
			]);
			if($e['curp'] != '')
			{
				array_push($body,[ "content" => [ "label" => $e['curp'] ]]);
			}
			else
			{
				array_push($body,[ "content" => [ "label" => "---" ]]);
			}
			if($e['id'] != '')
			{
				array_push($body,[
					"content" =>
					[
						"kind" 			=> "components.buttons.button",
						"variant"		=> "success",
						"buttonElement"	=> "a",
						"attributeEx"	=> "href=\"".route('employee.edit',$e['id'])."\"",
						"label"			=> "<span class=\"icon-pencil\"></span>"
					]
				]);
			}
			else
			{
				array_push($body,[ "content" => [ "label" => "---" ]]);
			}
			$modelBody[] = $body;
		}
	@endphp
	@component('components.tables.table',
		[
			'modelBody' => $modelBody,
			'modelHead' => $modelHead
		])
	@endcomponent
@endsection