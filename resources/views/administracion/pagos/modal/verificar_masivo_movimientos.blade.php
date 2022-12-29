@extends('layouts.child_module')
@section('data')
	@component("components.forms.form",["attributeEx" => "method=\"POST\" action=\"".route('payments.movement-massive.continue')."\"", "files"=>true])
		@component('components.labels.title-divisor') ALTA MASIVA @endcomponent
		@component('components.labels.not-found',["variant" => "note"])
			<div>•Por favor verifique que su información se encuentre estructurada como en su archivo CSV.</div>
			<div>•Sólo se muestran las primeras 10 líneas.</div>
			<div>•Para continuar con el proceso dé clic en el botón «Continuar»</div>
		@endcomponent
		@component("components.labels.subtitle") DATOS GENERALES DE LOS MOVIMIENTOS @endcomponent
		@component('components.containers.container-form')
			<div class="col-span-2">
				@component('components.labels.label') Empresa: @endcomponent
				@php
					$options = collect();
					foreach(App\Enterprise::orderBy('name','asc')->whereIn('id',Auth::user()->inChargeEnt(186)->pluck('enterprise_id'))->get() as $e)
					{
						$description = $e->name;
						if($enterprise == $e->id)
						{
							$options = $options->concat([["value"=>$e->id, "selected" => "selected", "description" => $description]]);
						}
						else
						{
							$options = $options->concat([["value"=>$e->id, "description" => $description]]);
						}
					}
					$attributeEx =  "name=\"enterprise\" data-validation=\"required\"";
					$classEx = "custom-select select-enterprise";
				@endphp
				@component('components.inputs.select',["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])  @endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Clasificación del gasto: @endcomponent
				@php
					$options = collect();
					foreach(App\Account::where('idEnterprise',$enterprise)->where('selectable',1)->get() as $a)
					{
						$description = $a->account. " - " .$a->description;
						if($a->idAccAcc == $account)
						{
							$options = $options->concat([["value"=>$a->idAccAcc, "selected" => "selected", "description" => $description]]);
						}
						else
						{
							$options = $options->concat([["value"=>$a->idAccAcc, "description" => $description]]);
						}
					}
					$attributeEx =  "name=\"account\" data-validation=\"required\"";
					$classEx = "custom-select select-account";
				@endphp
				@component('components.inputs.select',["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])  @endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Tipo de movimiento: @endcomponent
				@php
					$options = collect(
						[
							['value'=>'Ingreso', 'description'=>'Ingreso'], 
							['value'=>'Devolución', 'description'=>'Devolución'], 
							['value'=>'Rechazos', 'description'=>'Rechazos'], 
							['value'=>'Egreso', 'description'=>'Egreso']
						]
					);

					if(isset($type) && ($type == "Ingreso" || $type == "Devolución" || $type == "Rechazos" || $type == "Egreso"))
					{
						$options = $options->concat([['value'=>$type, 'selected'=>'selected','description'=>$type]]);
					}
					$attributeEx =  "name=\"type\" data-validation=\"required\"";
					$classEx = "custom-select";
				@endphp
				@component('components.inputs.select',["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])  @endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Separador @endcomponent
				@php
					$options = [];
						$options = [
							['value'=>',', 'description'=>'coma (,)'],
							['value'=>';', 'description'=>'punto y coma (;)'],
						];
						if(isset($separator) && ($separator == ","))
						{
							$options[0]["selected"] = "selected";
						}
						else if(isset($separator) && ($separator == ";"))
						{
							$options[1]["selected"] = "selected";
						}
					$attributeEx =  "name=\"separator\" data-validation=\"required\"";
					$classEx = "custom-select";
				@endphp
				@component('components.inputs.select',["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])  @endcomponent
			</div>
		@endcomponent
		@component("components.labels.not-found", ["variant"=>"alert"])
				@slot('title')
				@endslot
				<div>•Del lado izquierdo se muestran los nombres de los campos requeridos por el sistema.</div>
				<div>•Del lado derecho se muestra una lista con los nombres de las columnas del archivo previamente cargado.</div>
				<div>•Seleccione el nombre de la columna que le corresponde a cada campo requerido en el sistema.</div>
		@endcomponent
		@php

			$options = collect();
			foreach(array_keys(current($csv)) as $head)
			{
				$description = Illuminate\Support\Str::slug($head,'_');	
				$options = $options->concat([["value"=>Illuminate\Support\Str::slug($head,'_'), "description"=>$description]]);
			}

			$options_f = collect(
				[
					['value'=>'m-d-y','description'=>'mm-dd-yy'],
					['value'=>'d-m-y','description'=>'dd-mm-yy'],
					['value'=>'y-m-d','description'=>'yy-mm-dd'],
					['value'=>'m-d-Y','description'=>'mm-dd-yyyy'],
					['value'=>'d-m-Y','description'=>'dd-mm-yyyy'],
					['value'=>'Y-m-d','description'=>'yyyy-mm-dd'],
					['value'=>'m/d/y','description'=>'mm/dd/yy'],
					['value'=>'d/m/y','description'=>'dd/mm/yy'],
					['value'=>'y/m/d','description'=>'yy/mm/dd'],
					['value'=>'m/d/Y','description'=>'mm/dd/yyyy'],
					['value'=>'d/m/Y','description'=>'dd/mm/yyyy'],
					['value'=>'Y/m/d','description'=>'yyyy/mm/dd'],
					['value'=>'mdy','description'=>'mmddyy'],
					['value'=>'dmy','description'=>'ddmmyy'],
					['value'=>'ymd','description'=>'yymmdd'],
					['value'=>'mdY','description'=>'mmddyyyy'],
					['value'=>'dmY','description'=>'ddmmyyyy'],
					['value'=>'Ymd','description'=>'yyyymmdd'],

				]
			);


			$heads = ["DATOS DEL SISTEMA", "DATOS DEL ARCHIVO"];
			$modelBody = [];

			$body = 
			[
				"classEx" => "tr",
				[
					"content"=>
					[
						[
							"label" => "Descripción",
						]
					]
				],
				[
					"content"=>
					[
						[
							"kind" 			=> "components.inputs.select",
							"attributeEx" 	=> "name=\"description\"",
							"classEx" 		=> "custom-select option",
							"options"		=> $options,
						]
					]
				]
			];
			$modelBody[] = $body;
			$body = 
			[
				"classEx" => "tr",
				[
					"content"=>
					[
						[
							"label" => "Formato de fecha",
						]
					]
				],
				[
					"content"=>
					[
						[
							"kind" 			=> "components.inputs.select",
							"attributeEx" 	=> "name=\"date_format\"",
							"classEx" 		=> "custom-select option",
							"options"		=> $options_f,
						]
					]
				]
			];
		$modelBody[] = $body;
		$body = 
			[
				"classEx" => "tr",
				[
					"content"=>
					[
						[
							"label" => "Fecha",
						]
					]
				],
				[
					"content"=>
					[
						[
							"kind" 			=> "components.inputs.select",
							"attributeEx" 	=> "name=\"date\"",
							"classEx" 		=> "custom-select option",
							"options"		=> $options,
						]
					]
				]
			];
		$modelBody[] = $body;
		$body = 
			[
				"classEx" => "tr",
				[
					"content"=>
					[
						[
							"label" => "Importe",
						]
					]
				],
				[
					"content"=>
					[
						[
							"kind" 			=> "components.inputs.select",
							"attributeEx" 	=> "name=\"amount\"",
							"classEx" 		=> "custom-select option",
							"options"		=> $options,
						]
					]
				]
			];
		$modelBody[] = $body;
		@endphp
		@component("components.tables.alwaysVisibleTable",[
			"modelHead" => $heads,
			"modelBody" => $modelBody
		])@endcomponent

		@php
			if(count($csv)>0)
			{
				$modelHead = [];

				foreach(array_keys(current($csv)) as $head)
				{
					$modelHead[[0]] = ["value" => Illuminate\Support\Str::slug($head,'_')];
				}
				
				$modelBody	= [];
				$count = 0;
					
				foreach($csv as $data)
				{
					$tr = ["classEx" => "tr"];
					$countShow = 0;
					foreach ($data as $row)
					{
						if($countShow <= 1)
						{
							$td = [
								"content" =>
								[
									"label" => $row
								]
							];	
						}
						else
						{
							$td = [
								"content" =>
								[
									"label" => $row
								]
							];
						}
						
						$tr[] = $td;
						$countShow++;

						
					}
					$count++;
					if($count==11)
					{
						break;
					}
					$modelBody[] = $tr;
				}
			}
		@endphp
		@component("components.tables.table",[
			"modelHead" => $modelHead,
			"modelBody" => $modelBody,
			"title"		=> "REGISTRO DEL ARCHIVO"
		])
		@endcomponent
		@if(count($csv)>10)
			@component("components.labels.not-found",["variant"=>"alert"])
				@slot('title')
					NOTA:
				@endslot
					Sólo se muestran las primeras 10 líneas del archivo
			@endcomponent
		@endif
		@component('components.inputs.input-text')
			@slot('attributeEx')
				type="hidden"
				name="fileName"
				value="{{$fileName}}"
			@endslot
		@endcomponent
		<div class="content-start items-start flex flex-row flex-wrap justify-center w-full mt-8">
			@component('components.buttons.button',[["variant"=>"primary"]])
			@slot('attributeEx')
				type="submit"
				value="CONTINUAR"
			@endslot
			CONTINUAR
			@endcomponent
			@component('components.buttons.button',["variant"=>"dark-red"])
				@slot('attributeEx')
					type="submit"
					value="CANCELAR"
					formmethod="POST"
					formaction="{{route('payments.movement-massive.cancel')}}"
				@endslot
				CANCELAR
			@endcomponent
		</div>
	@endcomponent
@endsection
@section('scripts')
	<script>
		$(document).ready(function(e)
		{
			@php
				$selects = collect([
					[
						"identificator"				=> ".select-enterprise",
						"placeholder"				=> "Seleccione la empresa",
						"maximumSelectionLength"	=> "1"
					],
					[
						"identificator"				=> ".custom-select",
						"placeholder"				=> "Seleccione un tipo de movimiento",
						"maximumSelectionLength"	=> "1"
					],
					[
						"identificator"				=> ".option",
						"placeholder"				=> "Seleccione una opción",
						"maximumSelectionLength"	=> "1"
					],
				]);
			@endphp
			@component("components.scripts.selects",["selects" => $selects]) @endcomponent
			generalSelect({'selector': '.select-account', 'depends': '.select-enterprise', 'model': 10});
		});
	</script>
@endsection