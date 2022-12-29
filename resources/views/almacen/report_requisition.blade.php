@extends('layouts.child_module')
@section('data')
	@component("components.labels.title-divisor") BUSCAR SOLICITUDES @endcomponent
	@SearchForm(["variant" => "default", "attributeEx" => "action=\"".route('warehouse.report.requisition')."\" id=\"formsearch\""])
		<div class="col-span-2">
			@component("components.labels.label") Folio: @endcomponent
			@component("components.inputs.input-text")
				@slot("attributeEx") type="text" name="folio" id="input-search" placeholder="Ingrese el folio" value="{{isset($folio) ? $folio : ''}}" @endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component("components.labels.label") Título: @endcomponent
			@component("components.inputs.input-text")
				@slot("attributeEx") type="text" name="title_request" id="input-search" placeholder="Ingrese un título" value="{{ isset($title_request) ? htmlentities($title_request) : '' }}" @endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component("components.labels.label") Fecha en que se solicita: @endcomponent
			@php
				$inputs = 
				[
					[
						"input_classEx"		=> "datepicker",
						"input_attributeEx" => "type=\"text\" name=\"mindate_request\" step=\"1\" placeholder=\"Desde\" value=\"".(isset($mindate_request) ? $mindate_request : '')."\""
					],
					[
						"input_classEx"		=> "datepicker",
						"input_attributeEx" => "type=\"text\" name=\"maxdate_request\" step=\"1\" placeholder=\"Hasta\" value=\"".(isset($maxdate_request) ? $maxdate_request : '')."\""
					]
				];
			@endphp
			@component("components.inputs.range-input", ["inputs" => $inputs]) @endcomponent
		</div>
		<div class="col-span-2">
			@component("components.labels.label") Fecha en que deben estar en obra: @endcomponent
			@php
				$inputs = 
				[
					[
						"input_classEx"		=> "datepicker2",
						"input_attributeEx" => "type=\"text\" name=\"mindate_obra\" step=\"1\" placeholder=\"Desde\" value=\"".(isset($mindate_obra) ? $mindate_obra : '')."\""
					],
					[
						"input_classEx"		=> "datepicker2",
						"input_attributeEx" => "type=\"text\" name=\"maxdate_obra\" step=\"1\" placeholder=\"Hasta\" value=\"".(isset($maxdate_obra) ? $maxdate_obra : '')."\""
					]
				];
			@endphp
			@component("components.inputs.range-input", ["inputs" => $inputs]) @endcomponent
		</div>
		<div class="col-span-2">
			@component("components.labels.label") Solicitante: @endcomponent
			@php
				$options = collect();
				
				if(isset($user_request))
				{
					foreach(App\User::whereIn('id',$user_request)->get() as $user_request)
					{
						$options = $options->concat([["value" => $user_request->id, "description" => $user_request->fullName(), "selected" => "selected"]]);
					}
				}
			@endphp
			@component("components.inputs.select", ["options" => $options])
				@slot("classEx") js-users form-control @endslot
				@slot("attributeEx") name="user_request[]" multiple @endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component("components.labels.label") Estado: @endcomponent
			@php
				$options = collect();
				foreach (App\StatusRequest::whereIn('idrequestStatus',[2,3,4,5,6,7,10,11,12])->orderBy('description','asc')->get() as $s)
				{

					$options = $options->concat([["value" => $s->idrequestStatus, "description" => $s->description, "selected" => ((isset($status) && in_array($s->idrequestStatus, $status)) ? "selected" : "")]]);
				}
			@endphp
			@component("components.inputs.select", ["options" => $options])
				@slot("classEx") js-status form-control @endslot
				@slot("attributeEx") title="Estado de Solicitud" name="status[]" multiple="multiple" @endslot
			@endcomponent
		</div>
		@slot('export')
			<div class="float-right md:flex grid">
				@component("components.buttons.button", ["variant" => "success"])
					@slot("classEx") export @endslot
					@slot("attributeEx") type="submit" formaction="{{ route('warehouse.report.requisition.excel') }}" @endslot
					<span>Reporte simple en Excel</span><span class="icon-file-excel"></span>
				@endcomponent
				@component("components.buttons.button", ["variant" => "success"])
					@slot("classEx") export @endslot
					@slot("attributeEx") type="submit" formaction="{{ route('report.requisition.excel') }}" @endslot
					<span>Reporte Completo en Excel</span><span class="icon-file-excel"></span>
				@endcomponent
				@component("components.buttons.button", ["variant" => "red"])
					@slot("attributeEx") type="submit" formaction="{{ route('warehouse.report.requisition.pdf') }}" @endslot
					<span>Exportar a PDF</span><span class="icon-pdf"></span>
				@endcomponent
			</div>
		@endslot
	@endSearchForm
	@if(count($requests) > 0)
		@php
			$modelHead = 
			[
				[
					["value"	=>	"Folio"],
					["value"	=>	"Título"],
					["value"	=>	"Solicitante"],
					["value"	=>	"Proyecto"],
					["value"	=>	"Estado"],
					["value"	=>	"Fecha de elaboración"],
					["value"	=>	"Acción"]
				]
			];
			$body = [];
			$modelBody = [];
			foreach($requests as $request)
			{
				$body = 
				[
					[
						"content" =>
						[
							[
								"label"	=> $request->folio
							]
						]
					],
					[
						"content" =>
						[
							[
								"label"	=> $request->title != "" ? htmlentities($request->title) : 'Sin Título'
							]
						]
					],
					[
						"content" =>
						[
							[
								"label"	=> $request->requestUser()->exists() ? $request->requestUser->fullName() : 'Sin solicitante'
							]
						]
					],
					[
						"content" =>
						[
							[
								"label"	=> $request->requestProject ? $request->requestProject->proyectName : 'Sin proyecto'
							]
						]
					],
					[
						"content" =>
						[
							[
								"label"	=> $request->statusRequest->description
							]
						]
					],
					[
						"content" =>
						[
							[
								"label"	=> $request->fDate
							]
						]
					],
					[
						"content" =>
						[
							[
								"kind"			=> "components.buttons.button",
								"classEx"		=> "detail",
								"variant"		=> "secondary",
								"attributeEx"	=> "value=\"".$request->folio."\" type=\"button\" title=\"Detalles\"",
								"label"			=> "<span class=\"icon-search\"></span>"
							]
						]
					],
				];
				$modelBody[] = $body;
			}
		@endphp
		@component("components.tables.table", ["modelHead" => $modelHead, "modelBody" => $modelBody])
			@slot("classEx")
				table
			@endslot
			@slot("attributeEx")
			@endslot
		@endcomponent
		{{ $requests->appends($_GET)->links() }}
	@else
		@component("components.labels.not-found") @endcomponent
	@endif
	@component("components.modals.modal", ["modalTitle" => "DETALLES"])
		@slot("classEx") modal @endslot
		@slot("attributeEx") id="myModal" @endslot
		@slot('modalFooter')
			@component("components.buttons.button", ["variant" => "red", "classEx" => "exit"]) Cerrar @endcomponent
		@endslot
	@endcomponent
@endsection

@section('scripts')
	<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
	<script src="{{ asset('js/jquery-ui.js') }}"></script>
	<script src="{{ asset('js/datepicker.js') }}"></script>
	<script src="{{ asset('js/jquery.numeric.js') }}"></script>
	<script type="text/javascript"> 
		$(document).ready(function()
		{			
			$(function() 
			{
				$( ".datepicker" ).datepicker({ maxDate: 0, dateFormat: "yy-mm-dd" });
				$( ".datepicker2" ).datepicker({ dateFormat: "yy-mm-dd" });
			});
			@ScriptSelect(
			[
				"selects"	=> 
				[
					[
						"identificator"		=> ".js-status",
						"placeholder"		=> "Seleccione un estatus"
					]
				]
			])
			@endScriptSelect
			generalSelect({'selector':'.js-users', 'model':36});
			$(document).on('click','.detail', function()
			{
				id 	= $(this).val();
				
				$.ajax(
				{
					type : 'get',
					url  : '{{ route("warehouse.report.requisition.modal")}}',
					data : { 'id':id },
					success : function(data)
					{
						$('#myModal').modal('show')
						$('.modal-body').html(data);
					},
					error : function()
					{
						swal('','Sucedió un error, por favor intente de nuevo.','error');
						$('#myModal').modal('hide');
					}
				})
			})
			.on('click','.exit',function()
			{
				$('#detail').slideUp();
				$('#myModal').modal('hide');
				$('.detail').removeAttr('disabled');
			});
		});
	</script>
@endsection
