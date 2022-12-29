@extends('layouts.child_module')
@section('data')
	@component("components.labels.title-divisor") BUSCAR TICKETS @endcomponent
	@component("components.forms.searchForm",["attributeEx" => "id=\"formsearch\"","variant"=>"deafult"])
		@slot("contentEx")
			<div class="col-span-2">
				@component('components.labels.label') Número de ticket:@endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type        = "text" 
						name        = "idticket" 
						id          = "input-search" 
						placeholder = "Ingrese un número de ticket" 
						value       = "{{ isset($idticket) ? $idticket : '' }}"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Asunto:@endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type        = "text" 
						name        = "subject" 
						id          = "input-search" 
						placeholder = "Ingrese una descripción" 
						value       = "{{ isset($subject) ? $subject : '' }}"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Sección: @endcomponent
				@php
					$options = [];
					foreach (App\SectionTickets::orderName()->get() as $sec)
					{
						if(isset($idSection) && in_array($sec->idsectionTickets, $idSection))
						{
							$options[] = ['value' => $sec->idsectionTickets, 'selected'=>'selected', 'description' => $sec->section]; 
						}
						else
						{
							$options[] = ['value' => $sec->idsectionTickets, 'description' => $sec->section]; 
						}
					}
					$attributeEx	= "title=\"Sección\" name=\"idSection[]\"";
					$classEx		= "js-section";
				@endphp
				@component('components.inputs.select', 
					[
						'options'     => $options, 
						'attributeEx' => $attributeEx, 
						'classEx'     => $classEx
					])
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Tipo: @endcomponent
				@php
					$options = [];
					foreach (App\TicketType::orderName()->get() as $types)
					{
						if(isset($idType) && ($types->idTypeTickets == $idType))
						{
							$options[] = ['value' => $types->idTypeTickets, 'selected'=>'selected', 'description' => $types->type]; 
						}
						else
						{
							$options[] = ['value' => $types->idTypeTickets, 'description' => $types->type]; 
						}
					}
					$attributeEx	= "title=\"Tipo\" name=\"idType[]\"";
					$classEx		= "js-type";
				@endphp
				@component('components.inputs.select', 
					[
						'options'     => $options, 
						'attributeEx' => $attributeEx, 
						'classEx'     => $classEx
					])
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Prioridad: @endcomponent
				@php
					$options = [];
					foreach (App\TicketPriority::orderName()->get() as $p)
					{
						if(isset($idPriority) && ($p->idPriorityTickets == $idPriority))
						{
							$options[] = ['value' => $p->idPriorityTickets, 'selected'=>'selected', 'description' => $p->priority]; 
						}
						else
						{
							$options[] = ['value' => $p->idPriorityTickets, 'description' => $p->priority]; 
						}
					}
					$attributeEx	= "title=\"Prioridad\" name=\"idPriority[]\"";
					$classEx		= "js-priority";
				@endphp
				@component('components.inputs.select', 
					[
						'options'     => $options, 
						'attributeEx' => $attributeEx, 
						'classEx'     => $classEx
					])
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Estado: @endcomponent
				@php
					$options = [];
					foreach (App\TicketStatus::orderName()->get() as $s)
					{
						if(isset($idStatus) && ($s->idStatusTickets == $idStatus))
						{
							$options[] = ['value' => $s->idStatusTickets, 'selected'=>'selected', 'description' => $s->status]; 
						}
						else
						{
							$options[] = ['value' => $s->idStatusTickets, 'description' => $s->status]; 
						}
					}
					$attributeEx	= "title=\"Estado\" name=\"idStatus\"";
					$classEx		= "js-status";
				@endphp
				@component('components.inputs.select', 
					[
						'options'     => $options, 
						'attributeEx' => $attributeEx, 
						'classEx'     => $classEx
					])
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Rango de fecha: @endcomponent
				@php
					$min = isset($mindate) ? $mindate : '';
					$max = isset($maxdate) ? $maxdate : '';

					$inputs = 
					[
						[
							"input_attributeEx" => "name=\"mindate\" step=\"1\" placeholder=\"Desde\" value=\"".$min."\"",
						],
						[
							"input_attributeEx" => "name=\"maxdate\" step=\"1\" placeholder=\"Hasta\" value=\"".$max."\"",
						]
					];
				@endphp
				@component("components.inputs.range-input",["inputs" => $inputs]) @endcomponent
			</div>
		@endslot
		@if (count($tickets) > 0)
			@slot("export")
				<div class="float-right">
					@component('components.labels.label')
						@component('components.buttons.button',['variant' => 'success'])
							@slot('attributeEx') 
								type=submit 
								formaction={{ route('report.tickets.excel') }} @endslot
							@slot('label')
								<span>Exportar a Excel</span><span class="icon-file-excel"></span> 
							@endslot
						@endcomponent
					@endcomponent
				</div>
			@endslot
		@endif
	@endcomponent

	@if(count($tickets) > 0)
		@php
			$body 		= [];
			$modelBody	= [];
			$modelHead	= 
			[
				[
					["value"	=> "#"],
					["value"	=> "Asunto"],
					["value"	=> "Solicitante"],
					["value"	=> "Fecha"],
					["value"	=> "Sección"],
					["value"	=> "Tipo"],
					["value"	=> "Prioridad"],
					["value"	=> "Asignado"],
					["value"	=> "Estado"],
					["value"	=> "Acción"]
				]
			];
			foreach ($tickets as $ticket)
			{
				$body = 
				[
					[
						"content" => 
						[
							"label" => $ticket->idTickets
						]
					],
					[ 
						"content" => 
						[ 
							"label" => $ticket->subject
						]
					],
					[
						"content" => 
						[ 
							"label" => $ticket->requestUser->fullName()
						]
					],
					[
						"content" => 
						[ 
							"label" => Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$ticket->request_date)->format('d-m-Y H:i')
						]
					],
					[
						"content" => 
						[ 
							"label" => $ticket->sectionTicket->section
						]
					],
					[
						"content" => 
						[ 
							"label" => $ticket->typeTicket->type
						]
					],
					[
						"content" => 
						[ 
							"label" => $ticket->priorityTicket->priority
						]
					],
					[
						"content" => 
						[ 
							"label" => $ticket->assignedUser()->exists() ? $ticket->assignedUser->fullName() : "Sin Asignar"
						]
					],
					[
						"content" => 
						[ 
							"label" => $ticket->statusTicket->status
						]
					],
					[
						"content" =>
						[
							[
								"kind"			=> "components.buttons.button",
								"buttonElement"	=> "button", 
								"label"			=> "<span class=\"icon-search\"></span>",
								"classEx"		=> "follow-btn details",
								"variant"		=> "secondary",
								"attributeEx"	=> "alt=\"Detalles\" title=\"Detalles\" type=\"button\" data-id=\"".$ticket->idTickets."\" data-toggle=\"modal\" data-target=\"#myModal\""
							]
						]
					]
				];
				array_push($modelBody, $body);
			}
		@endphp
		@component('components.tables.table',[
			"modelHead" 			=> $modelHead,
			"modelBody" 			=> $modelBody
		])
			@slot('classExBody')
				request-validate
			@endslot
		@endcomponent
		{{ $tickets->appends($_GET)->links() }}

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
		@component('components.labels.not-found',["text"=>"No hay respuestas"]) @endcomponent
	@endif
@endsection

@section('scripts')
<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
<script src="{{ asset('js/jquery-ui.js') }}"></script>
<script src="{{ asset('js/datepicker.js') }}"></script>
<script src="{{ asset('js/jquery.numeric.js') }}"></script>
<script type="text/javascript"> 
	$(document).ready(function()
	{
		$('input[name="idticket"]').numeric(false);
		@php
			$selects = collect(
			[
				[
					"identificator"	=> ".js-type",
					"placeholder"	=> "Seleccione una opción",
					"languaje"		=> "es",
				],
				[
					"identificator"	=> ".js-priority",
					"placeholder"	=> "Seleccione una opción",
					"languaje"		=> "es",
				],
				[
					"identificator"				=> ".js-status",
					"placeholder"				=> "Seleccione una opción",
					"languaje"					=> "es",
					"maximumSelectionLength"	=> "1"
				],
				[
					"identificator"	=> ".js-section",
					"placeholder"	=> "Seleccione una opción",
					"languaje"		=> "es",
				],
			]);
		@endphp
		@component("components.scripts.selects",["selects" => $selects]) @endcomponent

		$(document).on('click','[data-toggle="modal"]', function()
		{
			idTicket = $(this).attr('data-id');
			$.ajax(
			{
				type : 'get',
				url  : '{{ route("report.tickets.detail") }}',
				data : {'idTicket':idTicket},
				success : function(data)
				{
					$('.modal-body').html(data);
				},
				error: function()
				{
					swal('','Sucedió un error, por favor intente de nuevo.','error');
					$('#myModal').modal('hide');
				}
			});
		})
		.on('click','.exit',function()
		{
			$('#myModal').hide();
		});
	});
	
	@if(isset($alert)) 
		{!! $alert !!} 
	@endif 
</script> 
@endsection


