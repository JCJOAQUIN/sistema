@extends('layouts.child_module')
@section('data')
	<div class="sm:text-center text-left my-5">
		A continuación podrá verificar la información de la solicitud antes de continuar con el proceso:
	</div>
	<div class="pb-6">
		@php
			$elaborateUser = App\User::find($request->idElaborate);
			$modelTable =
			[
				["Folio:", $request->folio],
				["Título y fecha:", htmlentities($request->resource->first()->title). " - " .Carbon\Carbon::createFromFormat('Y-m-d',$request->resource->first()->datetitle)->format('d-m-Y')],
				["Solicitante:", $request->requestUser->fullName()],
				["Elaborado por:", $request->elaborateUser->fullName()],
				["Empresa:", App\Enterprise::find($request->idEnterprise)->name],
				["Dirección:", App\Area::find($request->idArea)->name],
				["Departamento:", App\Department::find($request->idDepartment)->name],
				["Proyecto:", isset(App\Project::find($request->idProject)->proyectName) ? App\Project::find($request->idProject)->proyectName : 'No se selccionó proyecto'],
			];
		@endphp
		@component("components.templates.outputs.table-detail", ["modelTable" => $modelTable, "title" => "Detalles de la Solicitud"])@endcomponent	
	</div>
	<div class="mt-6">
		@component("components.labels.title-divisor")
			DATOS DEL SOLICITANTE
		@endcomponent
		@php
			$modelTable = ["Forma de pago" => $request->resource->first()->paymentMethod->method,
			"Referencia" => (isset($request->resource->first()->reference) ?  $request->resource->first()->reference : "---"),
			"Tipo de moneda" => $request->resource->first()->currency,
			"Importe" => "$ ".number_format($request->resource->first()->total,2)];
			if ($request->resource->first()->bankData != "")
			{
				$modelTable = 
				[
					"Banco" 			=> $request->resource->first()->bankData->bank->description!=null ? $request->resource->first()->bankData->bank->description : '---',
					"Alias" 			=> $request->resource->first()->bankData->alias!=null ? $request->resource->first()->bankData->alias : '---',
					"Número de tarjeta" => $request->resource->first()->bankData->cardNumber!=null ? $request->resource->first()->bankData->cardNumber : '---',
					"CLABE" 			=> $request->resource->first()->bankData->clabe!=null ? $request->resource->first()->bankData->clabe : '---',
					"Número de cuenta" 	=> $request->resource->first()->bankData->account!=null ? $request->resource->first()->bankData->account : '---'
				];
			}
		@endphp
		@component("components.templates.outputs.table-detail-single",["modelTable" => $modelTable])@endcomponent
	</div>
	<div class="mt-10">
		@component("components.labels.title-divisor")
			RELACIÓN DE DOCUMENTOS SOLICITADOS
		@endcomponent
		@php
			$body = [];
			$modelBody = [];
			$modelHead = 
			[
				[
					["value"=>"#"],
					["value"=>"Concepto"],
					["value"=>"Clasificación de gasto"],
					["value"=>"Importe"],
				]
			];

			$subtotalFinal = $ivaFinal = $totalFinal = 0;
			$countConcept = 1;

			foreach($request->resource->first()->resourceDetail as $resourceDetail)
			{
				$totalFinal		+= $resourceDetail->amount;

				$body = 
				[
					"classEx" => "tr",
					[
						"content" =>
						[
							[
								"label" => $countConcept,
							]
						]
					],
					[
						"content" => 
						[
							[
								"label" => htmlentities($resourceDetail->concept),
							]
						]
					],
					[
						"content" =>
						[
							[
								"label" => $resourceDetail->accounts->account." - ".$resourceDetail->accounts->description." (".$resourceDetail->accounts->content.")",
							]
						]
					],
					[
						"content" =>
						[
							[
								"label" => "$ ".number_format($resourceDetail->amount,2)
							]
						]
					]
				];
				$countConcept++;
				$modelBody[] = $body;
			}			
		@endphp
		@component("components.tables.table",[
			"modelHead"	=> $modelHead,
			"modelBody"	=> $modelBody,
			])
			@slot("classEx")	
				text-center
			@endslot
			@slot("attributeEx")
				id="table"
			@endslot
			@slot("attributeExBody")
				id="body"
			@endslot
			@slot("classExBody")
				request-validate
			@endslot
		@endcomponent
		@php
			if ($totalFinal!=0)
			{
				$total = number_format($totalFinal,2);
			}
			$modelTable = 
			[
				[
					"label" => "TOTAL:", "inputsEx" => 
					[
						[
							"kind" => "components.labels.label",
							"label" => "$ ".$total,
							"classEx" => "total"
						],
						[
							"kind" => "components.inputs.input-text",
							"classEx" => "total",
							"attributeEx" => "id=\"total\" type=\"hidden\" readonly=\"readonly\" name=\"total\" placeholder=\"$ 0.00\" value=\"".$total."\""
						]
					]
				]
			];
		@endphp
		@component('components.templates.outputs.form-details',[
			"modelTable" => $modelTable,			
		])
		@endcomponent
	</div>
	@if($request->resource->first()->documents()->exists())
		<div class="mt-10">
			@component("components.labels.title-divisor")
				DOCUMENTOS CARGADOS
			@endcomponent
			@php
				$body = [];
				$modelBody = [];
				$heads = ["Nombre","Archivo","Modificado por"];

				foreach($request->resource->first()->documents->sortByDesc('created_at') as $doc)
				{
					$body =
					[
						"classEx" => "tr",
						[
							"content" =>
							[
								[
									"label" => $doc->name,
								],
								[
									"kind" => "components.inputs.input-text",
									"attributeEx" => "type=\"hidden\" name=\"document-id[]\" value=\"".$doc->id."\"",
								]
							]
						],
						[
							"content" =>
							[
								[
									"kind" 			=> "components.buttons.button",
									"attributeEx" 	=> "target=\"_blank\" type=\"button\" title=\"".$doc->path."\"".' '."href=\"".asset('docs/resource/'.$doc->path)."\"",
									"buttonElement" => "a",
									"label"			=> "Archivo",
									"variant"		=> "secondary",
								]
							]
						],
						[
							"content" =>
							[
								[
									"label" => $doc->user->fullName(),
								]
							]
						]
					];
					if(isset($request) && $request->status == 2)
					{
						$body[] =
						[
							"content" =>
							[
								"kind" => "components.inputs.input-text",
								"attributeEx" => "type=\"hidden\" name=\"to_delete\"",
							]
						];
					}
					$modelBody[] = $body;
				}
			@endphp
			@component("components.tables.alwaysVisibleTable",[
				"modelHead"	=> $heads,
				"modelBody"	=> $modelBody,
			])
				@slot("classEx")	
					text-center
				@endslot
			@endcomponent
		</div>
	@endif
	<div class="mt-10">
		@component("components.labels.title-divisor")
			DATOS DE REVISIÓN
		@endcomponent
		<div class="my-6">
			@component("components.tables.table-request-detail.container",["variant"=>"simple"])
				@php
					$account = "";
					$modelTable = 
					[
						"Reviso" 					=> (isset($request->reviewedUser) ? $request->reviewedUser->fullName() : "No hay"),
						"Nombre de la Empresa" 		=> (isset($request->reviewedEnterprise) ? $request->reviewedEnterprise->name : "No hay"),
						"Nombre de la Dirección" 	=> (isset($request->reviewedDirection) ? $request->reviewedDirection->name : "No hay"),
						"Nombre del Departamento" 	=> (isset($request->reviewedDepartment) ? $request->reviewedDepartment->name : "No hay"),
					];
					if(isset($request->accountsReview->account))
					{
						$account = $request->accountsReview->account. " - ".$request->accountsReview->description." (".$request->accountsReview->content.")";
					}
					else
					{
						$account = "No hay";
					}
					$modelTable ['Clasificación del gasto'] = $account;
					$modelTable ["Nombre del Proyecto"] = isset($request->reviewedProject->proyectName) ? $request->reviewedProject->proyectName : 'No se seleccionó proyecto';
					$labels = "";
					foreach($request->labels as $label)
					{
						$labels = $labels." ".$label->description."," ;
					}
					$modelTable ['Etiquetas'] = $labels ? $labels : "Sin etiquetas";
					if($request->checkComment == "")
					{
						$modelTable["Comentarios"] = "Sin comentarios";
					}
					else 
					{
						$modelTable["Comentarios"] = htmlentities($request->checkComment);
					}
				@endphp
				@component("components.templates.outputs.table-detail-single", ["modelTable" => $modelTable])@endcomponent
			@endcomponent
		</div>
	</div>
	<div class="mt-10">
		@component("components.labels.title-divisor")
			RELACIÓN DE DOCUMENTOS APROBADOS
		@endcomponent
		@php
			$heads = ["Concepto","Clasificación de gasto","Importe"];
			$modelBody = [];
			$subtotalFinal = $ivaFinal = $totalFinal = 0;
			foreach($request->resource->first()->resourceDetail as $resourceDetail)
			{
				$totalFinal		+= $resourceDetail->amount;
				$body = 
				[
					"classEx" => "tr",
					[
						"content" =>
						[
							[
								"label" => htmlentities($resourceDetail->concept),
							]
						]
					],
					[
						"content" =>
						[
							[
								"label" => (isset($resourceDetail->accountsReview->account) && $resourceDetail->accountsReview->account != null) ? $resourceDetail->accountsReview->account. " - ".$resourceDetail->accountsReview->description." (".$resourceDetail->accountsReview->content.")" : "No hay",
							]
						]
					],
					[
						"content" =>
						[
							[
								"label" => "$ ".number_format($resourceDetail->amount,2),
							]
						]
					]
				];
				$modelBody[] = $body; 
			}
		@endphp
		@component("components.tables.alwaysVisibleTable",[
			"modelHead"	=> $heads,
			"modelBody"	=> $modelBody,
			])
			@slot("attributeEx")	
				id="table"
			@endslot
			@slot("classExBody")	
				request-validate
			@endslot
			@slot("attributeExBody")	
				id="body"
			@endslot
		@endcomponent
	</div>
	
	@component("components.forms.form", ["attributeEx" => "id=\"container-alta\" method=\"post\" action=\"".route('resource.authorization.update',$request->folio)."\"", "methodEx" => "PUT"])
		<div class="justify-center p-8">
			@component("components.containers.container-approval")
				@slot('attributeExLabel')
					id="label-inline"
				@endslot
				@slot("attributeExButton")
					name="status"
					value="5"
					id="aprobar"
				@endslot
				@slot("attributeExButtonTwo")
					name="status"
					value="7"
					id="rechazar"
				@endslot
			@endcomponent
		</div>
		<div id="aceptar" class="hidden">
			<div class="mt-6">
				@component("components.labels.label")
					Comentarios (Opcional):
				@endcomponent
				@component("components.inputs.text-area")
					@slot("classEx")
						text-area
					@endslot
					@slot("attributeEx")
						cols="90"
						rows="10"
						name="authorizeCommentA"
						placeholder="Ingrese un comentario"
					@endslot
				@endcomponent
			</div>
		</div>
		<div class="text-center my-6">
			@component("components.buttons.button",["variant" => "primary"])
				@slot("attributeEx")
					type="submit" 
					name="enviar"
					value="ENVIAR SOLICITUD"
				@endslot
				ENVIAR SOLICITUD
			@endcomponent
			@component('components.buttons.button', [ "buttonElement" => "a", "variant" => "reset"])
				@slot("attributeEx")
					@if(isset($option_id)) 
						href="{{ url(getUrlRedirect($option_id)) }}"
					@else 
						href="{{ url(getUrlRedirect($child_id)) }}" 
					@endif 
				@endslot
				@slot('classEx')
					load-actioner
				@endslot
				REGRESAR 
			@endcomponent
		</div>
	@endcomponent
@endsection
@section('scripts')
<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
<script src="{{ asset('js/jquery-ui.js') }}"></script>
<script src="{{ asset('js/datepicker.js') }}"></script>
<script src="{{ asset('js/jquery.numeric.js') }}"></script>
<script>
	$(document).ready(function()
	{
		$.validate(
		{
			form: '#container-alta',
			onSuccess : function($form)
			{
				if($('input[name="status"]').is(':checked'))
				{
					swal('Cargando',{
						icon: '{{ asset(getenv('LOADING_IMG')) }}',
						button: false,
						closeOnClickOutside: false,
						closeOnEsc: false
					});
					return true;
				}
				else
				{
					swal('', 'Por favor seleccione un estatus.', 'error');
					return false;
				}
			}
		});
		$(document).on('change','input[name="status"]',function()
		{
			$("#aceptar").slideDown("slow");
		});
	});
</script>

@endsection