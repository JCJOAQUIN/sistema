@extends('layouts.child_module')
@section('data')
	@component("components.labels.title-divisor") BUSCAR SOLICITUDES @endcomponent
	@php
		$values = ["folio" => $folio, "name" => $name, "minDate" => $mindate, "maxDate" => $maxdate];
		$hidden = ['enterprise'];
	@endphp
	@component("components.forms.searchForm",["attributeEx" => "id=\"formsearch\"","values" => $values, "hidden" => $hidden])
		@slot("contentEx")
			<div class="col-span-2">
				@component("components.labels.label") Tipo de Solicitud: @endcomponent
				@php
					$options = collect();
					foreach(App\RequestKind::orderName()->whereIn('idrequestkind',[11,12,13,14,15])->orderBy('kind','ASC')->get() as $k)
					{
						$description = $k->kind;
						if(isset($kind) && in_array($k->idrequestkind, $kind))
						{
							$options = $options->concat([["value"=>$k->idrequestkind, "selected"=>"selected", "description"=>$description]]);
						}
						else
						{
							$options = $options->concat([["value"=>$k->idrequestkind,"description"=>$description]]);
						}
					}
					$attributeEx = "name=\"kind[]\" multiple=\"multiple\"";
					$classEx = "js-kind";
				@endphp
				@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Estado de Solicitud: @endcomponent
				@php
					$options = collect();
					foreach (App\StatusRequest::orderName()->whereIn('idrequestStatus',[4,5,10,11,12])->get() as $s)
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
					$classEx = "js-status";
				@endphp
				@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])@endcomponent
			</div>
		@endslot
		@if (count($requests) > 0)
			@slot("export")
				<div class="float-right">
					@component('components.labels.label')
						@component('components.buttons.button',['variant' => 'success'])
							@slot('attributeEx') 
								type=submit 
								formaction={{ route('report.movements-accounts.excel') }} @endslot
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
			$body		= [];
			$modelBody	= [];
			$modelHead	= 
			[
				[
					["value"	=> "Folio"],
					["value"	=> "Tipo"],
					["value"	=> "Solicitante"],
					["value"	=> "Elaborado por"],
					["value"	=> "Estado"],
					["value"	=> "Fecha de elaboración"],
					["value"	=> "Empresa"],
					["value"	=> "Clasificación del gasto"],
					["value"	=> "Acción"]
				]
			];

			foreach($requests as $request)
			{
				$body = 
				[
					[
						"content" =>
						[
							"label" => $request->folio 
						]
					],
					[
						"content" =>
						[
							"label" =>  $request->requestkind->kind 
						]
					],
					[
						"content" =>
						[
							"label" => $request->requestUser()->exists() ? $request->requestUser->name.' '.$request->requestUser->last_name.' '.$request->requestUser->scnd_last_name : 'No hay' 
						]
					],

					[
						"content" =>
						[
							"label" => $request->elaborateUser->name.' '.$request->elaborateUser->last_name.' '.$request->elaborateUser->scnd_last_name 
						]
					],
					[
						"content" =>
						[
							"label" => $request->statusrequest->description
						]
					],
					[
						"content" =>
						[
							"label" => Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$request->fDate)->format('d-m-Y H:i'),
						]
					],
					[
						"content" =>
						[
							"label" => "Varias"
						]
					],
					[
						"content" =>
						[
							"label" => "Varias"
						]
					],
					[
						"content" =>
						[
							"kind"          => "components.buttons.button",
							"buttonElement" => "button", 
							"label"			=> "<span class=\"icon-search\"></span>",
							"classEx"	   	=> "follow-btn view-request",
							"variant" 		=> "secondary",
							"attributeEx"  	=> "alt=\"Detalles\" title=\"Detalles\" type=\"button\" data-toggle=\"modal\" data-target=\"#myModal\"  data-folio=\"".$request->folio."\" "
						],
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
						"identificator"			=> ".js-status",
						"placeholder"			=> "Seleccione un estado de solicitud",
						"languaje"				=> "es",
					],
					[
						"identificator"			=> ".js-kind",
						"placeholder"			=> "Seleccione un tipo de solicitud",
						"languaje"				=> "es",
						"maximumSelectionLength" => "1",
					]
				]);
			@endphp
			@component("components.scripts.selects",["selects" => $selects]) @endcomponent

			$(document).on('click','[data-toggle="modal"]', function()
			{
				folio = $(this).attr('data-folio');
				$.ajax(
				{
					type : 'get',
					url  : '{{ route("report.movements-accounts.detail") }}',
					data : {'idmovement':folio},
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
			.on('click','.close, .exit',function()
			{
				$('.detail').removeAttr('disabled');
				$('#myModal').modal('hide');
			});
		});
	</script> 
@endsection
