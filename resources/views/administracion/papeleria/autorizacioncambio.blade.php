@extends("layouts.child_module")
@section("data")
	<div class="sm:text-center text-left my-5">
		A continuación podrá verificar la información de la solicitud antes de continuar con el proceso:
	</div>
	@php
		$modelTable = 
		[
			["Folio", $request->new_folio != null ? $request->new_folio : $request->folio],
			["Título y fecha", htmlentities($request->stationery->first()->title). " - " .Carbon\Carbon::createFromFormat('Y-m-d',$request->stationery->first()->datetitle)->format('d-m-Y')],
			["Solicitante", $request->requestUser->name." ".$request->requestUser->last_name." ".$request->requestUser->scnd_last_name],
			["Elaborado", $request->elaborateUser->name." ".$request->elaborateUser->last_name." ".$request->elaborateUser->scnd_last_name],
			["Empresa", $request->requestEnterprise->name],
			["Dirección", $request->requestDirection->name],
			["Departamento", $request->requestDepartment->name],
			["Proyecto", $request->requestProject()->exists() ? $request->requestProject->proyectName : ""],
			["Clasificación del Gasto", $request->accounts->account. " - " .$request->accounts->description],
		];
		if(isset($request) && $request->stationery()->first())
		{
			$value=$request->stationery()->first()->subcontractorProvider;
			if(strlen($value) > 0)
			{
				$modelTable[]=["Subcontratista/Proveedor", $value ];
			}
			else
			{
				$modelTable[]=["Subcontratista/Proveedor", "No aplica" ];
			}						
		}
	@endphp	
	@component("components.templates.outputs.table-detail", ["modelTable" => $modelTable, "title" => "Detalles de la Solicitud"])@endcomponent

	@component("components.labels.title-divisor") Detalles del artículo @endcomponent
	<div class="my-4">
		@php
			$body 		= [];
			$modelBody 	= [];
			$modelHead 	= [
				[
					["value"=>"#"],
					["value"=>"Categoría"],
					["value"=>"Cantidad"],
					["value"=>"Concepto"],
					["value"=>"Código corto"],
					["value"=>"Código largo"],
					["value"=>"Comentario"],
				]
			];

			$countConcept = 1;

			foreach ($request->stationery->first()->detailStat as $key=>$detail) 
			{
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
								"label" => $detail->categoryData()->exists() ? $detail->categoryData->description : "---",
							]
						]
					],
					[
						"content" =>
						[
							[
								"label" => $detail->quantity,
							]
						]
					],
					[
						"content" =>
						[
							[
								"label" => ($detail->product != "" ? htmlentities($detail->product) : "---"),
							]
						]
					],
					[
						"content" =>
						[
							[
								"label" => ($detail->short_code != "" ? htmlentities($detail->short_code) : "---"),
							]
						]
					],
					[
						"content" =>
						[
							[
								"label" => ($detail->long_code != "" ? htmlentities($detail->long_code) : "---"),
							]
						]
					],
					[
						"content" =>
						[
							[
								"label" => htmlentities($detail->commentaries),
							]
						]
					],
				];
				$countConcept++;
				$modelBody[] = $body; 
			}
		@endphp

		@component("components.tables.table",[
			"modelHead" 			=> $modelHead,
			"modelBody" 			=> $modelBody,
		])
			@slot("attributeExBody")
				id="body"
			@endslot
		@endcomponent
	</div>
	@component("components.labels.title-divisor") Datos de Revisión @endcomponent
	<div class="my-6">
		@component("components.tables.table-request-detail.container",["variant"=>"simple"])
			@php
				$reviewAccount = App\Account::find($request->accountR);
				$varAccounts = "";
				if(isset($reviewAccount->account))
				{
					$varAccounts = $reviewAccount->account." ".$reviewAccount->description;
				}else {
					$varAccounts = "No hay";
				}

				$varLabels = "";
				if(count($request->labels))
				{
					foreach($request->labels as $label)
					{
						$varLabels .= $label->description;
					}
				}
				else {
					$varLabels = "Sin etiqueta";
				}

				$varComment = "";
				if($request->checkComment == "")
				{
					$varComment = "Sin comentarios";
				}else {
					$varComment = htmlentities($request->checkComment);
				}
				$modelTable = [
					"Revisó" 					=> $request->reviewedUser->name." ".$request->reviewedUser->last_name." ".$request->reviewedUser->scnd_last_name,
					"Nombre de la Empresa" 		=> App\Enterprise::find($request->idEnterpriseR)->name,
					"Nombre de la Dirección" 	=> $request->reviewedDirection->name,
					"Nombre del Departamento" 	=> App\Department::find($request->idDepartamentR)->name,
					"Clasificación del gasto" 	=> $varAccounts,
					"Comentarios"				=> $varComment
				];
			@endphp
			@component("components.templates.outputs.table-detail-single", ["modelTable" => $modelTable])@endcomponent
		@endcomponent
	</div>
	@component("components.labels.title-divisor") Etiquetas Asignadas @endcomponent
		@php
			$heads = ["#","Cantidad","Concepto","Etiquetas"];
			$modelBody = [];
			$countConcept = 1;

			foreach($request->stationery->first()->detailStat as $key=>$detail)
			{
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
								"label" => $detail->quantity,
							]
						]
					],
					[
						"content" =>
						[
							[
								"label" => htmlentities($detail->product),
							]
						]
					],
				];
				$etiquetas = "";

				foreach ($detail->labels as $label) {
					$etiquetas = $etiquetas." ".$label->label->description;
				}
				$body[] =
				[
					"content" =>
					[
						"label" => $etiquetas ? $etiquetas : "Sin etiquetas",
					]
				];
				$countConcept++;
				$modelBody[] = $body;
			}
		@endphp
		@component("components.tables.alwaysVisibleTable",[
			"modelHead" => $heads,
			"modelBody" => $modelBody,
		])
		@slot("attributeExBody")
			id="tbody-conceptsNew"
		@endslot
		@endcomponent
		@component("components.forms.form",["methodEx" => "PUT", "attributeEx" => "method=\"POST\" action=\"".route("stationery.authorization.update",$request->folio)."\" id=\"container-alta\""])
			<div class="my-4">	
				@component("components.containers.container-approval")
					@slot("attributeExButton")
						name="status"
						id="aprobar"
						value="5"
					@endslot
					@slot("classExButton")
						approve
					@endslot
					@slot("attributeExButtonTwo")
						name="status"
						id="rechazar"
						value="7"
					@endslot
					@slot("classExButtonTwo")
						refuse
					@endslot
				@endcomponent
			</div>		
			<div id="aceptar" class="hidden">
				@component("components.labels.label")
					Comentarios (opcional) 
				@endcomponent
				@component("components.inputs.text-area")
					@slot("attributeEx")
						name="authorizeCommentA"
						id="authorizeCommentA"
						cols="90"
						rows="10"
					@endslot
				@endcomponent
			</div>
			<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mt-4 mb-6">
				@component("components.buttons.button",["variant" => "primary"])
					@slot("attributeEx") 
						type="submit"
						name="enviar"
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
<script src="{{ asset('js/jquery.numeric.js') }}"></script>
<script src="{{ asset('js/datepicker.js') }}"></script>
<script>
	$(document).ready(function()
	{
		@php
			$selects = collect([
				[
					"identificator"				=> ".js-users",
					"placeholder"				=> "Seleccione el solicitante",
					"language"					=> "es",
					"maximumSelectionLength"	=> "1",
				],
				[
					"identificator"				=> ".js-enterprises",
					"placeholder"				=> "Seleccione la empresa",
					"language"					=> "es",
					"maximumSelectionLength"	=> "1",
				],
				[
					"identificator"				=> ".js-accounts",
					"placeholder"				=> "Seleccione la clasificación del gasto",
					"language"					=> "es",
					"maximumSelectionLength"	=> "1",
				],
				[
					"identificator"				=> ".js-areas",
					"placeholder"				=> "Seleccione la dirección",
					"language"					=> "es",
					"maximumSelectionLength"	=> "1",
				],
				[
					"identificator"				=> ".js-departments",
					"placeholder"				=> "Seleccione el departamento",
					"language"					=> "es",
					"maximumSelectionLength"	=> "1",
				],
			]);		
		@endphp
		@component("components.scripts.selects",["selects" => $selects]) @endcomponent
		$('.card_number,.destination_account,.destination_key,.employee_number').numeric(false);    // números
		$('.amount,.importe',).numeric({ altDecimal: ".", decimalPlaces: 2 });
		$('input[name="status"]').change(function()
		{
			$("#aceptar").slideDown("slow");
		}); 
		$.validate(
		{
			form: '#container-alta',
			onSuccess : function($form)
			{
				if($('input[name="status"]').is(':checked'))
				{
					swal('Cargando',{
						icon : '{{ url(getenv('LOADING_IMG')) }}',
						button: false,
					});
					return true;
				}
				else
				{
					swal('', 'Debe seleccionar al menos un estatus', 'error');
					return false;
				}
			}
		});		
	});
</script>
@endsection
