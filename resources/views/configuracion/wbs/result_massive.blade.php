@extends('layouts.child_module')

@section('data')
	@component('components.labels.title-divisor') ALTA MASIVA @endcomponent
	@component("components.labels.not-found", ["variant" => "note", "title" => "", "attributeEx" => "role=\"alert\""])
		Alta concluída.
	@endcomponent
	@php
		foreach($csv as $k => $e)
		{
			if($e['status']=='WBS actualizado correctamente.')
			{
				$status = 
				[
					"content" =>
					[
						[
							"kind"		=> "components.labels.label",
							"classEx"	=> "text-light-blue-400",
							"label"		=> "<span class=\"icon-updated\"></span>  ".$e['status']
						]
					]
				];
			}
			elseif($e['status']=='WBS registrado con exito.')
			{
				$status = 
				[
					"content" =>
					[
						[
							"kind"		=> "components.labels.label",
							"classEx"	=> "light-blue-400",
							"label"		=> "<span class=\"icon-new\"></span>  ".$e['status']
						]
					]
	
				];
			}
			elseif($e['status']=='WBS no registrado, ya existe.' || $e['status']=='WBS no registrado, campo vacío.' || $e['status']=='Error' || $e['status']=='WBS no registrado, código mayor a 5 caracteres.' || $e['status']=='WBS no registrado, ID de proyecto no existente.')
			{
				$status = 
				[
					"content" =>
					[
						[
							"kind"		=> "components.labels.label",
							"classEx"	=> "text-red-600",
							"label"		=> "<span class=\"icon-blocked text-danger\"></span>  ".$e['status']
						]
					]
	
				];
			}

			$body = 
			[
				isset($status) ? $status : '',
				[
					"content" =>
					[
						["label" => !empty($e['codigo_del_wbs']) ? $e['codigo_del_wbs'] : '---'] 		
					]
				],
				[
					"content" =>
					[
						["label" => !empty($e['nombre_del_wbs']) ? $e['nombre_del_wbs'] : '---'] 			
					]
				],
				[
					"content" =>
					[
						["label" => !empty($e['id_del_proyecto']) ? $e['id_del_proyecto'] : '---'] 		
					]
				],
				[
					"content" => 
					[
						$e['id'] != '' ?
						[
							"kind" 			=> "components.buttons.button",
							"variant"		=> "success",
							"buttonElement"	=> "a",
							"attributeEx"	=> "href=\"".route('wbs.edit',$e['id'])."\"",
							"label"			=> "<span class=\"icon-pencil\"></span>"
						]
						: ''
					]
				]
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
			"Código",
			"Nombre de WBS",
			"ID del proyecto al que pertenece",
			"Editar"
		],
		"modelBody"		=> $modelBody,
		"variant"		=> "default"
	]) @endAlwaysVisibleTable
@endsection