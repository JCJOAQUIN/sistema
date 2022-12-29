@extends('layouts.child_module')

@section('data')
	@component('components.labels.title-divisor')    ALTA MASIVA @endcomponent
	@component("components.labels.not-found", ["variant" => "note", "title" => "", "attributeEx" => "role=\"alert\""])
		Alta concluída.
	@endcomponent
	@php
		foreach($csv as $k => $e)
		{
			if($e['status']=='Actualizado')
			{
				$status = 
				[
					"content" =>
					[
						[
							"kind"		=> "components.labels.label",
							"classEx"	=> "text-light-blue-400",
							"label"		=> "<span class=\"icon-updated\"></span>".$e['status']
						]
					]
				];
			}
			elseif($e['status']=='Nuevo')
			{
				$status = 
				[
					"content" =>
					[
						[
							"kind"		=> "components.labels.label",
							"classEx"	=> "light-blue-400",
							"label"		=> "<span class=\"icon-new\"></span>".$e['status']
						]
					]
	
				];
			}
			else
			{
				$status = 
				[
					"content" =>
					[
						[
							"kind"		=> "components.labels.label",
							"classEx"	=> "text-red-600",
							"label"		=> "<span class=\"icon-blocked text-danger\"></span>".$e['status']
						]
					]
	
				];
			}

		
			isset($e['nombre'])		? $name = $e['nombre'] : '';
			isset($e['apellido'])	? $name .= " ".$e['apellido'] : '';
			isset($e['apellido2'])	? $name .= " ".$e['apellido2'] : '';
			
			$body = 
			[
				isset($status) ? $status : '',
				[
					"content" =>
					[
						["label" => $e['id']]
					]
				],
				[
					"content" =>
					[
						["label" => $name]
					]
				],
				[
					"content" =>
					[
						["label" => isset($e['curp']) ? $e['curp'] : '']
					]
				],
				$e['id'] != '' ? 
				[
					"content" => 
					[
						[
							"kind" 			=> "components.buttons.button",
							"variant"		=> "success",
							"buttonElement"	=> "a",
							"attributeEx"	=> "href=\"".route('employee.edit',$e['id'])."\"",
							"label"			=> "<span class=\"icon-pencil\"></span>"
						]
					]
				] : ''
			];
			$modelBody[] = $body;
		}
		
	@endphp
	@AlwaysVisibleTable([
		"classEx"		=> "table",
		"attributeEx"	=> "id=\"table\"",
		"modelHead"		=> 
		[
			"Estado",
			"ID",
			"Nombre",
			"CURP",
			"Acción"
		],
		"modelBody"		=> $modelBody
	]) @endAlwaysVisibleTable
@endsection