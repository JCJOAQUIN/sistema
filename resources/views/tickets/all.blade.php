@extends('layouts.child_module')

@section('data')
	<div id="container-cambio">
		@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "BUSCAR TICKETS"]) @endcomponent
		@php
			$values	=
			[
				'minDate'	=>	isset($mindate) ? date('d-m-Y',strtotime($mindate)) : '',
				'maxDate'	=>	isset($maxdate) ? date('d-m-Y',strtotime($maxdate)) : '',
			];
			$hidden	=	['enterprise','name','folio'];
		@endphp
		@component('components.forms.searchForm', ["attributeEx" => "id=\"formsearch\"", "values" => $values, "hidden" => $hidden])
			@slot('contentEx')
				@php
					$idSection = [];
					foreach(Auth::user()->inReview as $review)
					{ 
						$idSection[] = $review->idsectionTickets; 
					}
				@endphp
				<div class="col-span-2">
					@component('components.labels.label', ["label" => "Número de Ticket:"]) @endcomponent
					@component('components.inputs.input-text', ["attributeEx" => "type=\"text\" name=\"id\" id=\"input-search\" placeholder=\"Ingrese un número de ticket\" value=\"".(isset($idticket) ? $idticket : '')."\""]) @endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label', ["label" => "Asunto:"]) @endcomponent
					@component('components.inputs.input-text', ["attributeEx" => "type=\"text\" name=\"subject\" placeholder=\"Ingrese el asunto\" value=\"".(isset($subject) ? htmlentities($subject) : '')."\""]) @endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label', ["label" => "Asignado a:"]) @endcomponent
					@php
						$users = App\User::whereHas('module',function($query)
						{
							$query->where('module_id',106);
						})
						->whereHas('inReview',function($query) use ($idSection)
						{
							$query->whereIn('section_tickets_idsectionTickets',$idSection);
						})
						->where(function($query)
						{
							$query->where('status','ACTIVE')->orWhere('status','RE-ENTRY');
						})
						->get();
						$options	=	collect();
						$optionSelected	=	"";
						if ($assign == 'Sin asignar')
						{
							$optionSelected	=	"selected";
						}
						$options	=	$options->concat([["value"	=>	"Sin asignar",	"description"	=>	"Sin asignar",	"selected"	=>	$optionSelected]]);
						foreach ($users  as $u)
						{
							if (isset($assign) && $assign == $u->id)
							{
								$options	=	$options->concat([["value"	=>	$u->id.' '.$u->last_name.' '.$u->scnd_last_name,	"description"	=>	$u->name.' '.$u->last_name.' '.$u->scnd_last_name,	"selected"	=>	"selected"]]);
							}
							else
							{
								$options	=	$options->concat([["value"	=>	$u->id.' '.$u->last_name.' '.$u->scnd_last_name,	"description"	=>	$u->name.' '.$u->last_name.' '.$u->scnd_last_name]]);
							}
						}
					@endphp
					@component('components.inputs.select', ["options" => $options,"classEx" => "js-assign", "attributeEx" => "name=\"section\" title=\"Asignado a\" multiple=\"multiple\""]) @endcomponent
					@component('components.inputs.input-text', ["attributeEx" => "type=\"hidden\" name=\"assign\" value=\"".(isset($assign) ? $assign : '')."\""]) @endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label', ["label" => "Sección:"]) @endcomponent
					@php
						$options	=	collect();
						foreach ($sections as $sec)
						{
							if (isset($section) && $section == $sec->idsectionTickets)
							{
								$options	=	$options->concat([["value"	=>	$sec->idsectionTickets,	"description"	=>	$sec->section,	"selected"	=>	"selected"]]);
							}
							else
							{
								$options	=	$options->concat([["value"	=>	$sec->idsectionTickets,	"description"	=>	$sec->section]]);
							}
						}
					@endphp
					@component('components.inputs.select', ["options" => $options,"classEx" => "js-section", "attributeEx" => "title=\"Sección\" multiple=\"multiple\""]) @endcomponent
					@component('components.inputs.input-text', ["attributeEx" => "type=\"hidden\" name=\"section\" value=\"".(isset($section) ? $section : '')."\""]) @endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label', ["label" => "Tipo:"]) @endcomponent
					@php
						$options	=	collect();
						foreach (App\TicketType::orderName()->get() as $types)
						{
							if (isset($type) && $type == $types->idTypeTickets)
							{
								$options	=	$options->concat([["value"	=>	$types->idTypeTickets,	"description"	=>	$types->type,	"selected"	=>	"selected"]]);
							}
							else
							{
								$options	=	$options->concat([["value"	=>	$types->idTypeTickets,	"description"	=>	$types->type]]);
							}
						}
					@endphp
					@component('components.inputs.select', ["options" => $options,"classEx" => "js-type", "attributeEx" => "title=\"Tipo\" multiple=\"multiple\""]) @endcomponent
					@component('components.inputs.input-text', ["attributeEx" => "type=\"hidden\" name=\"type\" value=\"".(isset($type) ? $type : '')."\""]) @endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label', ["label" => "Prioridad:"]) @endcomponent
					@php
						$options	=	collect();
						foreach (App\TicketPriority::orderName()->get() as $p)
						{
							if (isset($priority) && $priority == $p->idPriorityTickets)
							{
								$options	=	$options->concat([["value"	=>	$p->idPriorityTickets,	"description"	=>	$p->priority,	"selected"	=>	"selected"]]);
							}
							else
							{
								$options	=	$options->concat([["value"	=>	$p->idPriorityTickets,	"description"	=>	$p->priority]]);
							}
						}
					@endphp
					@component('components.inputs.select', ["options" => $options,"classEx" => "js-priority", "attributeEx" => "title=\"Prioridad\" multiple=\"multiple\""]) @endcomponent
					@component('components.inputs.input-text', ["attributeEx" => "type=\"hidden\" name=\"priority\" value=\"".(isset($priority) ? $priority : '')."\""]) @endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label', ["label" => "Estado:"]) @endcomponent
					@php
						$options	=	collect();
						foreach (App\TicketStatus::orderName()->get() as $s)
						{
							if (isset($status) && $status == $s->idStatusTickets)
							{
								$options	=	$options->concat([["value"	=>	$s->idStatusTickets,	"description"	=>	$s->status,	"selected"	=>	"selected"]]);
							}
							else
							{
								$options	=	$options->concat([["value"	=>	$s->idStatusTickets,	"description"	=>	$s->status]]);
							}
						}
					@endphp
					@component('components.inputs.select', ["options" => $options,"classEx" => "js-status", "attributeEx" => "title=\"Estado\" multiple=\"multiple\""]) @endcomponent
					@component('components.inputs.input-text', ["attributeEx" => "type=\"hidden\" name=\"status\" value=\"".(isset($status) ? $status : '')."\""]) @endcomponent
				</div>
			@endslot
		@endcomponent
		@if (count($tickets) > 0)
			@php
				$modelHead	=	[];
				$body		=	[];
				$modelBody	=	[];
				$modelHead	=
				[
					[
						["value"	=>	"# Ticket"],
						["value"	=>	"Asunto"],
						["value"	=>	"Solicitante"],
						["value"	=>	"Fecha"],
						["value"	=>	"Sección"],
						["value"	=>	"Tipo"],
						["value"	=>	"Prioridad"],
						["value"	=>	"Asignado"],
						["value"	=>	"Estado"],
						["value"	=>	"Acción"],
					]
				];
				foreach($tickets as $ticket)
				{
					switch ($ticket->priorityTicket->priority)
					{
						case 'Urgente':
							$bgPriority='bg-red-500';
							break;
						case 'Alta':
							$bgPriority='bg-amber-400';
							break;
						case 'Normal':
							$bgPriority='bg-lime-500';
							break;
						case 'Baja':
							$bgPriority='bg-cyan-700';
							break;
						default:
							break;
					}
					foreach (App\User::where('id',$ticket->request_id)->get() as $user)
					{
						$userName	=	$user->fullName();
					}
					$assignedTo	=	"Sin Asignar";
					if ($ticket->assigned_id != null)
					{
						foreach (App\User::where('id',$ticket->assigned_id)->get() as $user)
						{
							$assignedTo	=	$user->fullName();
						}
					}
					if ($ticket->assigned_id == null && $ticket->idStatusTickets == 1)
					{
						$actionComponent	=
						[
							["kind"	=>	"components.buttons.button", "variant"	=>	"secondary", "buttonElement" => "a", "attributeEx" => "title=\"Ver Ticket\" alt=\"Ver Ticket\" type=\"submit\" href=\"".route('tickets.all.view',$ticket->idTickets)."\"", "label" => "<span class='icon-search'></span>"]
						];
					} else
					{
						$actionComponent	=
						[
							"label"	=>	""
						];
					}
					$body	=
					[
						[
							"content"	=>	["label"	=>	$ticket->idTickets!="" ? $ticket->idTickets : "---"]
						],
						[
							"content"	=>	["label"	=>	$ticket->subject!="" ? htmlentities($ticket->subject) : "---"]
						],
						[
							"content"	=>	["label"	=>	$userName!="" ? $userName : "---"]
						],
						[
							"content"	=>	["label"	=>	$ticket->request_date!="" ? Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$ticket->request_date)->format('d-m-Y') : "---"]
						],
						[
							"content"	=>	["label"	=>	$ticket->sectionTicket->section !="" ? $ticket->sectionTicket->section : "---"]
						],
						[
							"content"	=>	["label"	=>	$ticket->typeTicket->type !="" ? $ticket->typeTicket->type : "---"]
						],
						[
							"content"	=>	["kind"	=>	"components.labels.label", "classEx"	=>	"text-white rounded-md p-2 $bgPriority", "label"	=>	$ticket->priorityTicket->priority !="" ? $ticket->priorityTicket->priority : "---"]
						],
						[
							"content"	=>	["label"	=>	$assignedTo]
						],
						[
							"content"	=>	["label"	=>	$ticket->statusTicket->status !="" ? $ticket->statusTicket->status : "---"]
						],
						[
							"content"	=>	$actionComponent
						],
					];
					$modelBody[]	=	$body;
				}
			@endphp
			@component('components.tables.table', ["modelHead" => $modelHead, "modelBody" => $modelBody, "classEx" => "mt-4"]) @endcomponent
			{{ $tickets->appends($_GET)->links() }}
		@else
			@component('components.labels.not-found') @endcomponent
		@endif
	</div>
@endsection

@section('scripts')
<script src="{{ asset('js/jquery.numeric.js') }}"></script>
<script type="text/javascript"> 
	$(document).ready(function()
	{
		$('#input-search').numeric(false);
		@php
			$selects = collect([
				[
					"identificator"				=> ".js-type",
					"placeholder"				=> "Seleccione el tipo",
					"language"					=> "es",
					"maximumSelectionLength"	=> "1"
				],
				[
					"identificator"				=> ".js-priority",
					"placeholder"				=> "Seleccione la prioridad",
					"language"					=> "es",
					"maximumSelectionLength"	=> "1"
				],
				[
					"identificator"				=> ".js-status",
					"placeholder"				=> "Seleccione el estado",
					"language"					=> "es",
					"maximumSelectionLength"	=> "1"
				],
				[
					"identificator"				=> ".js-section",
					"placeholder"				=> "Seleccione la sección",
					"language"					=> "es",
					"maximumSelectionLength"	=> "1"
				],
				[
					"identificator"				=> ".js-assign",
					"placeholder"				=> "Seleccione el asignado",
					"language"					=> "es",
					"maximumSelectionLength"	=> "1"
				]
			]);
		@endphp
		@component('components.scripts.selects',["selects" => $selects]) @endcomponent
		$(document).on('change','.js-assign',function()
		{
			$('input[name="assign"]').val($(this).val());
		})
		.on('change','.js-section',function()
		{
			$('input[name="section"]').val($(this).val());
		})
		.on('change','.js-type',function()
		{
			$('input[name="type"]').val($(this).val());
		})
		.on('change','.js-priority',function()
		{
			$('input[name="priority"]').val($(this).val());
		})
		.on('change','.js-status',function()
		{
			$('input[name="status"]').val($(this).val());
		});
	});
</script>
@endsection