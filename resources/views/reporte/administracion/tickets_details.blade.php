<div class="pb-6">
	@component('components.labels.title-divisor') DATOS DEL TICKET @endcomponent
	@php
		$modelTable =
		[
			"# de Ticket" => $ticket->idTickets,
			"Asunto" => htmlentities($ticket->subject),
			"Fecha" => Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$ticket->request_date)->format('d-m-Y H:i'),
			"Solicitante" => $ticket->requestUser->fullName(),
			"Sección" => $ticket->sectionTicket->section,
			"Asignado" => $ticket->assignedUser()->exists() ? $ticket->assignedUser->fullName() : "Sin Asignar",
			"Tipo" => $ticket->typeTicket->type,
			"Prioridad" => $ticket->priorityTicket->priority,
			"Estado" => $ticket->statusTicket->status,
			"Descripción" => htmlentities($ticket->question),
		];
	@endphp
	@component("components.templates.outputs.table-detail-single", ["modelTable" => $modelTable]) @endcomponent
</div>
@if($ticket->answerTicket()->exists())
	@php
		$body 		= [];
		$modelBody	= [];
		$modelHead	= 
		[
			[
				["value"	=> "Usuario"],
				["value"	=> "Respuesta"],
				["value"	=> "Fecha"]
			]
		];
		foreach ($ticket->answerTicket as $answers)
		{
			$body = 
			[
				[ 
					"content" => 
					[ 
						"label" => $answers->answerUser->fullName()
					]
				],
				[
					"content" => 
					[ 
						"label" => htmlentities($answers->answer)
					]
				],
				[
					"content" => 
					[ 
						"label" => Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$answers->date)->format('d-m-Y H:i')
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
	@endcomponent
@endif
