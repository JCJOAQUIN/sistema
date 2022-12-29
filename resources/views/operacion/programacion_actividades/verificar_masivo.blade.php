@extends('layouts.child_module')
@section('data')
	@component("components.labels.title-divisor") ALTA MASIVA @endcomponent
	@component("components.labels.not-found", ["variant" => "note"])
		@component("components.labels.label")
			Por favor verifique que su información se encuentre estructurada como en su archivo CSV.
		@endcomponent
		@component("components.labels.label")
			Sólo se muestran las primeras 10 líneas.
		@endcomponent
		@component("components.labels.label")
			Para continuar con el proceso dé clic en el botón «Continuar»
		@endcomponent
	@endcomponent
	@if(count($csv)>0) 
		@php
			foreach(array_keys(current($csv)) as $head)
			{
				$modelHead[] = 
				[
					"value" => $head
				];
			}
			$modelHead[0]["classEx"]	= "sticky inset-x-0";
			$modelHead[1]["classEx"]	= "sticky inset-x-0";	
			$body						= [];
			$modelBody					= [];
			foreach ($csv as $row)
			{
				$body = 
				[
					[
						"classEx" => "sticky inset-x-0",
						"content" =>
						[
							"label"		=> $row["proyecto"]
						]
					],
					[
						"classEx" => "sticky inset-x-0",
						"content" =>
						[
							"label"		=> $row["codigo_wbs"]
						]
					],
					[
						"content" =>
						[
							"label"		=> $row["folio_permiso_de_trabajo"]
						]
					],
					[
						"content" =>
						[
							"label"		=> $row["descripcion"]
						]
					],
					[
						"content" =>
						[
							"label"		=> $row["contratista"]
						]
					],
					[
						"content" =>
						[
							"label"		=> $row["especialidad"]
						]
					],
					[
						"content" =>
						[
							"label"		=> $row["fecha_inicio"] != "" ? Carbon\Carbon::createFromFormat('d/m/Y', $row["fecha_inicio"])->format('d-m-Y') : ""
						]
					],
					[
						"content" =>
						[
							"label"		=> $row["hora_inicio"]
						]
					],
					[
						"content" =>
						[
							"label"		=> $row["fecha_finalizacion"] != "" ? Carbon\Carbon::createFromFormat('d/m/Y', $row["fecha_finalizacion"])->format('d-m-Y') : ""
						]
					],
					[
						"content" =>
						[
							"label"		=> $row["hora_finalizacion"]
						]
					],
					[
						"content" =>
						[
							"label"		=> $row["area_ubicacion"]
						]
					],
					[
						"content" =>
						[
							"label"		=> $row["num_personal"]
						]
					],
					[
						"content" =>
						[
							"label"		=> $row["recursos"]
						]
					],
					[
						"content" =>
						[
							"label"		=> $row["estatus"]
						]
					],
					[
						"content" =>
						[
							"label"		=> $row["causas_incumplimiento"]
						]
					],
				];
				$modelBody[] = $body;
			}
		@endphp
		@component("components.tables.table", ["modelHead" => [$modelHead], "modelBody" => $modelBody]) @endcomponent
	@else
		@component("components.labels.not-found", ["text" => "Sólo se muestran las primeras 10 líneas del archivo."]) @endcomponent
	@endif
	@if(count($csv)>10)
		@component("components.labels.not-found", ["text" => "El archivo cargado no tenía ningún registro."]) @endcomponent
	@endif
	@component("components.forms.form", ["attributeEx" => "id=\"activity_massive\" method=\"POST\""])
		@if(count($csv)==0)
			<div class="flex justify-center">
				@component("components.buttons.button", ["variant" => "reset"])
					@slot("attributeEx") type="submit" formaction="{{route('activitiesprogramation.massive.cancel')}}" @endslot
					Cancelar y volver
				@endcomponent
				@component("components.inputs.input-text")
					@slot("attributeEx") type="hidden" name="fileName" value="{{$fileName}}" @endslot
				@endcomponent
			</div>
		@else
			<div class="w-full mt-4 grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mb-6">
				@component("components.buttons.button", ["variant" => "primary"])
					@slot("attributeEx") type="submit" name="send" formaction="{{route('activitiesprogramation.massive.continue')}}" @endslot
					CONTINUAR
				@endcomponent
				@component("components.inputs.input-text")
					@slot("attributeEx") type="hidden" name="delimiter" value="{{$delimiter}}" @endslot
				@endcomponent
				@component("components.inputs.input-text")
					@slot("attributeEx") type="hidden" name="fileName" value="{{$fileName}}" @endslot
				@endcomponent
				@component("components.buttons.button", ["variant" => "reset"])
					@slot("attributeEx") type="submit" name="send" formaction="{{route('activitiesprogramation.massive.cancel')}}" @endslot
					Cancelar y volver
				@endcomponent
			</div>
		@endif
	@endcomponent
@endsection