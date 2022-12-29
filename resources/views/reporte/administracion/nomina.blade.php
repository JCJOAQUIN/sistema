@extends('layouts.child_module')
@section('css')
	<link rel="stylesheet" href="{{ asset('css/daterangepicker.min.css') }}">

	<style type="text/css">
		svg
		{
			fill: currentColor;
			width: 1.4em;
		}
		#dates_container
		{
			position: relative;
		}
	</style>
@endsection
@section('data')
	@component("components.labels.title-divisor") BUSCAR SOLICITUDES @endcomponent
	@php
		$values = ["folio" => $folio, "name"=>$name, "minDate" => $mindate, "maxDate" => $maxdate];
		$hidden = ['enterprise'];
	@endphp
	@component("components.forms.searchForm",["attributeEx" => "id=\"formsearch\"","values" => $values, "hidden" => $hidden])
		@slot("contentEx")
			<div class="col-span-2">
				@component('components.labels.label')Título:@endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type        = "text" 
						name        = "titleRequest" 
						id          = "title" 
						placeholder = "Ingrese un título" 
						value       = "{{ isset($titleRequest) ? $titleRequest : '' }}"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label')Tipo de Nómina:@endcomponent
				@php
					$options = collect();
					foreach(App\CatTypePayroll::orderName()->get() as $t)
					{
						$description = $t->description;
						if(isset($type_payroll) && in_array($t->id, $type_payroll))
						{
							$options = $options->concat([["value"=>$t->id, "selected"=>"selected", "description"=>$description]]);
						}
						else
						{
							$options = $options->concat([["value"=>$t->id, "description"=>$description]]);
						}
					}
					$attributeEx	= "title=\"Tipo de Nómina\" multiple=\"multiple\" name=\"type_payroll[]\"";
					$classEx		= "js-type";
				@endphp
				@component('components.inputs.select', ["options" => $options, 'attributeEx' => $attributeEx, 'classEx' => $classEx])@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label')Fiscal/No Fiscal:@endcomponent
				@php
					$options = collect();
					if(isset($fiscal) && in_array("1", $fiscal))
					{
						$options = $options->concat([["value"=>"1", "selected"=>"selected", "description"=> "Fiscal"]]);
					}
					else
					{
						$options = $options->concat([["value"=>"1", "description"=> "Fiscal"]]);
					}

					if(isset($fiscal) && in_array("0", $fiscal))
					{
						$options = $options->concat([["value"=>"0", "selected"=>"selected", "description"=> "No Fiscal"]]);
					}
					else
					{
						$options = $options->concat([["value"=>"0", "description"=> "No Fiscal"]]);
					}

					$attributeEx = "title=\"Fiscal/No fiscal\" name=\"fiscal[]\" multiple=\"multiple\"";
					$classEx     = "js-fiscal";
				@endphp
				@component('components.inputs.select', ['attributeEx' => $attributeEx,'classEx' => $classEx, "options" => $options]) @endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label')Categoría:@endcomponent
				@php
					$options = collect();
					if(isset($department) && in_array("4", $department))
					{
						$options = $options->concat([["value"=>"4", "selected"=>"selected", "description"=> "Administrativa"]]);
					}
					else
					{
						$options = $options->concat([["value"=>"4", "description"=> "Administrativa"]]);
					}

					if(isset($department) && in_array("11", $department))
					{
						$options = $options->concat([["value"=>"11", "selected"=>"selected", "description"=> "Obra"]]);
					}
					else
					{
						$options = $options->concat([["value"=>"11", "description"=> "Obra"]]);
					}

					$attributeEx = "title=\"Categoría\" name=\"department[]\" multiple=\"multiple\"";
					$classEx     = "js-department";
				@endphp
				@component('components.inputs.select', ['attributeEx' => $attributeEx,'classEx' => $classEx, "options" => $options]) @endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Estado de Solicitud: @endcomponent
				@php
					$options = collect();
					foreach(App\StatusRequest::whereIn('idrequestStatus',[4,5,6,7,10,11,12,13,18])->orderBy('description','asc')->get() as $s) 
					{
						$description = $s->description;
						if (isset($stat) && in_array($s->idrequestStatus,$stat))
						{
							$options = $options->concat([["value"=>$s->idrequestStatus, "selected"=>"selected", "description"=>$description]]);
						}
						else
						{
							$options = $options->concat([["value"=>$s->idrequestStatus,"description"=>$description]]);
						}
					}
					$attributeEx = "name=\"stat[]\" multiple=\"multiple\"";
					$classEx = "js-stat";
				@endphp
				@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Tipo de reporte: @endcomponent
				<div class="flex p-0 space-x-2">
					@component('components.buttons.button-approval')
						@slot('attributeEx') id="normal" name="type_report" value="1" @if(isset($type_report) && $type_report==0) checked @else checked @endif @endslot

						Normal
					@endcomponent
					@component('components.buttons.button-approval')
						@slot('attributeEx') id="reducido" name="type_report" value="2" @if(isset($type_report) && $type_report==1) checked @endif @endslot

						Reducido
					@endcomponent
				</div>
			</div>
		@endslot
		@if (count($requests) > 0)
			@slot("export")
				<div class="float-right">
					@component('components.labels.label')
						@component('components.buttons.button',['variant' => 'success'])
							@slot('attributeEx') 
								type=submit 
								formaction={{ route('report.nomina.excel') }} @endslot
							@slot('label')
								<span>Exportar a Excel</span><span class="icon-file-excel"></span> 
							@endslot
						@endcomponent
					@endcomponent
				</div>
			@endslot
		@endif
	@endcomponent

	@if (count($requests) > 0)
		@php
			$cfdi 		= "hidden";
			$receipt 	= "hidden";
			$body		= [];
			$modelBody	= [];
			$modelHead	= 
			[
				[
					["value"	=> "Folio"],
					["value"	=> "Estado"],
					["value"	=> "Título"],
					["value"	=> "Categoría"],
					["value"	=> "Tipo"],
					["value"	=> "Solicitante"],
					["value"	=> "Fecha de elaboración"],
					["value"	=> "Acción"],
					["value"	=> "Comprobantes"],
					["value"	=> "Timbres"],
					["value"	=> "Recibos"]
				]
			];

			foreach($requests as $request)
			{
				if($request->taxPayment == 1)
				{
					$cfdi = "";
				}
				else
				{
					$cfdi = "hidden";
				}

				if($request->taxPayment == 0)
				{
					$receipt = "";
				}
				else
				{
					$receipt = "hidden";
				}
				$date = $request->fDate != "" ? Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$request->fDate)->format('d-m-Y') : "";
				$body = 
				[
					[
						"content" =>
						[
							"label" => $request->folio,
						]
					],
					[
						"content" =>
						[
							"label" => $request->statusrequest->description,
						]
					],
					[
						"content" =>
						[
							"label" => $request->nominasReal->first()->title != null ? htmlentities($request->nominasReal->first()->title) : 'No hay',
						]
					],

					[
						"content" =>
						[
							"label" => ($request->idDepartment == 4 ? 'Administrativa' : 'Obra')." - ".$request->nominasReal->first()->typeNomina(),
						]
					],
					[
						"content" =>
						[
							"label" => $request->nominasReal->first()->typePayroll->description,
						]
					],
					[
						"content" =>
						[
							"label" =>  $request->requestUser()->exists() ? $request->requestUser->name.' '.$request->requestUser->last_name : 'No hay',
						]
					],
					[
						"content" =>
						[
							"label" =>  $date
						]
					],
					[
						"content" =>
						[
							[
								"kind"          => "components.buttons.button",
								"buttonElement" => "button", 
								"label"			=> "<span class=\"icon-search\"></span>",
								"classEx"	   	=> "follow-btn detail",
								"variant" 		=> "secondary",
								"attributeEx"  	=> "alt=\"Detalles\" title=\"Detalles\" type=\"button\" data-toggle=\"modal\" data-target=\"#myModal\""
							],
							[
								"kind"        => "components.inputs.input-text",
								"attributeEx" => "type=\"hidden\" value=\"".$request->folio."\"",
								"classEx"     => "folio",
							]
						]
					],
					[
						"content" =>
						[
							[
								"kind"          => "components.buttons.button",
								"buttonElement" => "a", 
								"label"			=> "<svg viewBox=\"0 0 512 512\" version=\"1.1\" xmlns=\"http://www.w3.org/2000/svg\" xmlns:xlink=\"http://www.w3.org/1999/xlink\" xml:space=\"preserve\" xmlns:serif=\"http://www.serif.com/\" style=\"fill-rule:evenodd;clip-rule:evenodd;stroke-linejoin:round;stroke-miterlimit:1.41421;\">
										<path d=\"M456,88L240,88L240,80C239.975,58.066 221.934,40.025 200,40L56,40C34.066,40.025 16.025,58.066 16,80L16,432C16.025,453.934 34.066,471.975 56,472L456,472C477.934,471.975 495.975,453.934 496,432L496,128C495.975,106.066 477.934,88.025 456,88ZM456,104C469.16,104.015 479.985,114.84 480,128L480,144.022C473.087,138.808 464.658,135.991 456,136L240,136L240,104L456,104ZM480,432C479.985,445.16 469.16,455.985 456,456L56,456C42.84,455.985 32.015,445.16 32,432L32,80C32.015,66.84 42.84,56.015 56,56L120,56L120,88L104,88C99.611,88 96,91.611 96,96C96,100.389 99.611,104 104,104L120,104L120,136L104,136C99.611,136 96,139.611 96,144C96,148.389 99.611,152 104,152L120,152L120,184L104,184C99.611,184 96,187.611 96,192C96,196.389 99.611,200 104,200L120,200L120,232L104,232C99.611,232 96,235.611 96,240C96,244.389 99.611,248 104,248L120,248L120,280L104,280C99.611,280 96,283.611 96,288C96,292.389 99.611,296 104,296L120,296L120,328L116,328C112.427,328.001 109.267,330.395 108.3,333.834L99.386,365.527C97.175,369.554 96.011,374.073 96,378.667C96,394.841 110.355,408 128,408C145.645,408 160,394.841 160,378.667C159.989,374.073 158.825,369.554 156.615,365.527L147.7,333.834C146.733,330.395 143.573,328.001 140,328L136,328L136,296L152,296C156.389,296 160,292.389 160,288C160,283.611 156.389,280 152,280L136,280L136,248L152,248C156.389,248 160,244.389 160,240C160,235.611 156.389,232 152,232L136,232L136,200L152,200C156.389,200 160,196.389 160,192C160,187.611 156.389,184 152,184L136,184L136,152L152,152C156.389,152 160,148.389 160,144C160,139.611 156.389,136 152,136L136,136L136,104L152,104C156.389,104 160,100.389 160,96C160,91.611 156.389,88 152,88L136,88L136,56L200,56C213.16,56.015 223.985,66.84 224,80L224,144C224,148.389 227.611,152 232,152L456,152C469.16,152.015 479.985,162.84 480,176L480,432ZM122.061,344L133.939,344L141.479,370.805C141.672,371.49 141.955,372.147 142.321,372.757C143.413,374.535 143.994,376.58 144,378.667C144,386.019 136.822,392 128,392C119.178,392 112,386.019 112,378.667C112.006,376.58 112.587,374.535 113.679,372.757C114.045,372.147 114.328,371.49 114.521,370.805L122.061,344Z\" style=\"fill-rule:nonzero;\"/>
									</svg>",
								"classEx"	   	=> "follow-btn detail",
								"variant" 		=> "secondary",
								"attributeEx"  	=> "alt=\"Descargar comprobantes de pago\" title=\"Descargar comprobantes de pago\" href=\"".route('report.nomina.payments',$request->folio)."\""
							]
						]
					],
					[
						"content" =>
						[
							[
								"kind"          => "components.buttons.button",
								"buttonElement" => "a", 
								"label"			=> "<svg viewBox=\"0 0 512 512\" version=\"1.1\" xmlns=\"http://www.w3.org/2000/svg\" xmlns:xlink=\"http://www.w3.org/1999/xlink\" xml:space=\"preserve\" xmlns:serif=\"http://www.serif.com/\" style=\"fill-rule:evenodd;clip-rule:evenodd;stroke-linejoin:round;stroke-miterlimit:1.41421;\">
										<path d=\"M456,88L240,88L240,80C239.975,58.066 221.934,40.025 200,40L56,40C34.066,40.025 16.025,58.066 16,80L16,432C16.025,453.934 34.066,471.975 56,472L456,472C477.934,471.975 495.975,453.934 496,432L496,128C495.975,106.066 477.934,88.025 456,88ZM456,104C469.16,104.015 479.985,114.84 480,128L480,144.022C473.087,138.808 464.658,135.991 456,136L240,136L240,104L456,104ZM480,432C479.985,445.16 469.16,455.985 456,456L56,456C42.84,455.985 32.015,445.16 32,432L32,80C32.015,66.84 42.84,56.015 56,56L120,56L120,88L104,88C99.611,88 96,91.611 96,96C96,100.389 99.611,104 104,104L120,104L120,136L104,136C99.611,136 96,139.611 96,144C96,148.389 99.611,152 104,152L120,152L120,184L104,184C99.611,184 96,187.611 96,192C96,196.389 99.611,200 104,200L120,200L120,232L104,232C99.611,232 96,235.611 96,240C96,244.389 99.611,248 104,248L120,248L120,280L104,280C99.611,280 96,283.611 96,288C96,292.389 99.611,296 104,296L120,296L120,328L116,328C112.427,328.001 109.267,330.395 108.3,333.834L99.386,365.527C97.175,369.554 96.011,374.073 96,378.667C96,394.841 110.355,408 128,408C145.645,408 160,394.841 160,378.667C159.989,374.073 158.825,369.554 156.615,365.527L147.7,333.834C146.733,330.395 143.573,328.001 140,328L136,328L136,296L152,296C156.389,296 160,292.389 160,288C160,283.611 156.389,280 152,280L136,280L136,248L152,248C156.389,248 160,244.389 160,240C160,235.611 156.389,232 152,232L136,232L136,200L152,200C156.389,200 160,196.389 160,192C160,187.611 156.389,184 152,184L136,184L136,152L152,152C156.389,152 160,148.389 160,144C160,139.611 156.389,136 152,136L136,136L136,104L152,104C156.389,104 160,100.389 160,96C160,91.611 156.389,88 152,88L136,88L136,56L200,56C213.16,56.015 223.985,66.84 224,80L224,144C224,148.389 227.611,152 232,152L456,152C469.16,152.015 479.985,162.84 480,176L480,432ZM122.061,344L133.939,344L141.479,370.805C141.672,371.49 141.955,372.147 142.321,372.757C143.413,374.535 143.994,376.58 144,378.667C144,386.019 136.822,392 128,392C119.178,392 112,386.019 112,378.667C112.006,376.58 112.587,374.535 113.679,372.757C114.045,372.147 114.328,371.49 114.521,370.805L122.061,344Z\" style=\"fill-rule:nonzero;\"/>
									</svg>",
								"classEx"	   	=> "follow-btn detail ".$cfdi,
								"variant" 		=> "secondary",
								"attributeEx"  	=> "alt=\"Descargar comprobantes de pago\" title=\"Descargar comprobantes de pago\" href=\"".route('report.nomina.cfdi',$request->folio)."\""
							]
						]
					],
					[
						"content" =>
						[
							[
								"kind"          => "components.buttons.button",
								"buttonElement" => "a", 
								"label"			=> "<svg viewBox=\"0 0 512 512\" version=\"1.1\" xmlns=\"http://www.w3.org/2000/svg\" xmlns:xlink=\"http://www.w3.org/1999/xlink\" xml:space=\"preserve\" xmlns:serif=\"http://www.serif.com/\" style=\"fill-rule:evenodd;clip-rule:evenodd;stroke-linejoin:round;stroke-miterlimit:1.41421;\">
										<path d=\"M456,88L240,88L240,80C239.975,58.066 221.934,40.025 200,40L56,40C34.066,40.025 16.025,58.066 16,80L16,432C16.025,453.934 34.066,471.975 56,472L456,472C477.934,471.975 495.975,453.934 496,432L496,128C495.975,106.066 477.934,88.025 456,88ZM456,104C469.16,104.015 479.985,114.84 480,128L480,144.022C473.087,138.808 464.658,135.991 456,136L240,136L240,104L456,104ZM480,432C479.985,445.16 469.16,455.985 456,456L56,456C42.84,455.985 32.015,445.16 32,432L32,80C32.015,66.84 42.84,56.015 56,56L120,56L120,88L104,88C99.611,88 96,91.611 96,96C96,100.389 99.611,104 104,104L120,104L120,136L104,136C99.611,136 96,139.611 96,144C96,148.389 99.611,152 104,152L120,152L120,184L104,184C99.611,184 96,187.611 96,192C96,196.389 99.611,200 104,200L120,200L120,232L104,232C99.611,232 96,235.611 96,240C96,244.389 99.611,248 104,248L120,248L120,280L104,280C99.611,280 96,283.611 96,288C96,292.389 99.611,296 104,296L120,296L120,328L116,328C112.427,328.001 109.267,330.395 108.3,333.834L99.386,365.527C97.175,369.554 96.011,374.073 96,378.667C96,394.841 110.355,408 128,408C145.645,408 160,394.841 160,378.667C159.989,374.073 158.825,369.554 156.615,365.527L147.7,333.834C146.733,330.395 143.573,328.001 140,328L136,328L136,296L152,296C156.389,296 160,292.389 160,288C160,283.611 156.389,280 152,280L136,280L136,248L152,248C156.389,248 160,244.389 160,240C160,235.611 156.389,232 152,232L136,232L136,200L152,200C156.389,200 160,196.389 160,192C160,187.611 156.389,184 152,184L136,184L136,152L152,152C156.389,152 160,148.389 160,144C160,139.611 156.389,136 152,136L136,136L136,104L152,104C156.389,104 160,100.389 160,96C160,91.611 156.389,88 152,88L136,88L136,56L200,56C213.16,56.015 223.985,66.84 224,80L224,144C224,148.389 227.611,152 232,152L456,152C469.16,152.015 479.985,162.84 480,176L480,432ZM122.061,344L133.939,344L141.479,370.805C141.672,371.49 141.955,372.147 142.321,372.757C143.413,374.535 143.994,376.58 144,378.667C144,386.019 136.822,392 128,392C119.178,392 112,386.019 112,378.667C112.006,376.58 112.587,374.535 113.679,372.757C114.045,372.147 114.328,371.49 114.521,370.805L122.061,344Z\" style=\"fill-rule:nonzero;\"/>
									</svg>",
								"classEx"	   	=> "follow-btn detail ".$receipt,
								"variant" 		=> "secondary",
								"attributeEx"  	=> "alt=\"Descargar comprobantes de pago\" title=\"Descargar comprobantes de pago\" href=\"".route('report.nomina.receipt',$request->folio)."\""
							]
						]
					]
				];

				$modelBody[] = $body;
			}
		@endphp
		@component("components.tables.table",[
			"modelHead" => $modelHead,
			"modelBody" => $modelBody,
		])
		@endcomponent
		{{ $requests->appends($_GET)->links() }}

		@component("components.modals.modal",[ "variant" => "large" ])
			@slot("id")
				myModal
			@endslot
			@slot("attributeEx")
				tabindex="-1"
			@endslot
			@slot("modalHeader")
			@component("components.buttons.button")
				@slot("attributeEx")
					type="button"
					data-dismiss="modal"
				@endslot
				@slot('classEx')
					close
				@endslot
				<span aria-hidden="true">&times;</span>
			@endcomponent
			@endslot
			@slot("modalBody")

			@endslot
		@endcomponent
	@else
		@component("components.labels.not-found")@endcomponent
	@endif
@endsection
@section('scripts')
	<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
	<script src="{{ asset('js/jquery-ui.js') }}"></script>
	<script src="{{ asset('js/jquery.numeric.js') }}"></script>
	<script type="text/javascript"> 
		$(document).ready(function()
		{
			@php
				$selects = collect([
					[
						"identificator"			=> ".js-stat",
						"placeholder"			=> "Seleccione un estado de solicitud",
						"languaje"				=> "es",
					],
					[
						"identificator"			=> ".js-type",
						"placeholder"			=> "Seleccione un tipo",
						"languaje"				=> "es",

					],
					[
						"identificator"			=> ".js-fiscal",
						"placeholder"			=> "Seleccione un tipo",
						"languaje"				=> "es",

					],
					[
						"identificator"			=> ".js-department",
						"placeholder"			=> "Seleccione una categoría",
						"languaje"				=> "es",

					]
				]);
			@endphp
			@component("components.scripts.selects",["selects" => $selects]) @endcomponent

			$(document).on('click','[data-toggle="modal"]', function()
			{
				folio	= $(this).parents('.tr').find('.folio').val();
				$.ajax(
				{
					type : 'get',
					url  : '{{ route("report.nomina.detail") }}',
					data : {
						'folio'	:folio,
					},
					success : function(data)
					{
						$('.modal-body').html(data);
					},
					error: function()
					{
						swal('','Sucedió un error, por favor intente de nuevo.','error');
						$('#myModal').modal('hide');
					}
				})
			})
			.on('click','.exit',function()
			{
				$('#detail').slideUp();
				$('#myModal').hide();
			})
		});
	</script>
@endsection