@extends('layouts.child_module')
@section('data')
	@component('components.forms.form', ["attributeEx" => "method=\"POST\" id=\"container-alta\" action=\"".route('tickets.assigned.update', $ticket->idTickets)."\"", "methodEx" => "PUT", "files" => true])
		@component('components.labels.title-divisor', ["classEx" => "mt-12"])
			<div class="w-full">
				<div class="grid grid-cols-3">
					<div class="col-span-1 col-start-2 text-center">
						DATOS DEL TICKET # {{$ticket->idTickets}} - {{$ticket->statusTicket->status}}
					</div>
					<div class="col-span-1 grid place-items-end">
						@php
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
						@endphp
						@component('components.labels.label', ["classEx" => "inline-block text-white font-medium w-24 h-8 flex justify-center py-1 rounded-md $bgPriority", "label" => $ticket->priorityTicket->priority]) @endcomponent
					</div>
				</div>
			</div>
		@endcomponent
		@php
			if ($ticket->documentsTickets()->count()>0)
			{
				foreach ($ticket->documentsTickets as $document)
				{
					$fileComponent[]	=
					[
						"kind"			=>	"components.buttons.button",
						"classEx"		=>	"text-center w-24",
						"variant"		=>	"secondary",
						"buttonElement"	=>	"a",
						"attributeEx"	=>	"target=\"_blank\" title=\"".$document->path."\" href=\"".url('docs/tickets/'.$document->path)."\"",
						"label"			=>	"Archivo"
					];
				}
			}
			else
			{
				$fileComponent	=	"Sin archivo";
			}
			$modelTable	=
			[
				"Solicitante"	=>	$ticket->requestUser->fullName(),
				"Sección"		=>	$ticket->sectionTicket->section,
				"Tipo"			=>	$ticket->typeTicket->type,
				"Fecha y Hora"	=>	isset($ticket->request_date) && $ticket->request_date!="" ? Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$ticket->request_date)->format('d-M-Y H:i:s') :"---",
				"Asunto"		=>	htmlentities($ticket->subject),
				"Mensaje"		=>	nl2br(htmlentities($ticket->question)),
				"Archivo"		=>	$fileComponent,
			];
		@endphp
		@component('components.templates.outputs.table-detail-single', ["classExRow" => "sm:w-full", "classEx" => "max-w-none grid grid-cols-7", "modelTable" => $modelTable])@endcomponent
		@if (count($ticket->answerTicket) > 0)
			<div class="flex justify-center w-full">
				<div class="w-9/12">
					@foreach ($ticket->answerTicket as $answer)
						<div class="grid mt-6 @if ($answer->answerUser->id == $ticket->requestUser->id) justify-items-start @else justify-items-end @endif">
							<div class="border border-gray-400 rounded-md w-10/12 p-4">
								<div class="text-center mt-2 border-b border-gray-500 pb-2">
									@component('components.labels.label', ["label" => "Respuesta de:"]) @endcomponent
									@component('components.labels.label', ["classEx" => "font-bold text-black", "label" => $answer->answerUser->name.' '.$answer->answerUser->last_name]) @endcomponent
								</div>
								<div class="mt-4">
									@component('components.labels.label', ["classEx" => "inline-block font-bold text-black", "label" => "Fecha y hora:"]) @endcomponent
									@component('components.labels.label', ["classEx" => "inline-block", "label" => Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$answer->date)->format('d-M-Y H:i:s')]) @endcomponent
									@component('components.labels.label', ["classEx" => "text-black mt-8", "label" => nl2br(htmlentities($answer->answer))]) @endcomponent
								</div>
								@if ($answer->path != null)
									<div class="mt-8">
										@component('components.labels.label', ["classEx" => "inline-block font-bold", "label" => "Archivo(s) adjunto(s):"]) @endcomponent
										@component('components.buttons.button',
											[
												"classEx"		=>	"inline-block",
												"variant"		=>	"secondary",
												"attributeEx"	=>	"target=\"_blank\" title=\"".$answer->path."\" href=\"".url('docs/tickets/'.$answer->path)."\"",
												"buttonElement"	=>	"a",
												"label"			=>	"Archivo"
											])
										@endcomponent
									</div>
								@endif
							</div>
						</div>
					@endforeach
				</div>
			</div>
		@endif
		<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mt-8">
			@component('components.buttons.button', ["variant" => "primary", "attributeEx" => "type=\"submit\" name=\"save\" value=\"TOMAR TICKET\"", "label" => "TOMAR TICKET"]) @endcomponent
			@if($ticket->idStatusTickets == 1)
				@component('components.buttons.button', ["variant" => "secondary", "attributeEx" => "id=\"re_asign\" data-toggle=\"modal\" data-target=\"#reAsignModal\" type=\"button\"", "label" => "REASIGNAR TICKET"]) @endcomponent
			@endif
			@php
				$href	=	isset($option_id) ? url(App\Module::find($option_id)->url) : url(App\Module::find($child_id)->url);
			@endphp
			@component('components.buttons.button', ["variant" => "reset", "buttonElement" => "a", "attributeEx" => "href=\"".$href."\"", "classEx" => "load-actioner", "label" => "REGRESAR"]) @endcomponent
		</div>
		@component('components.modals.modal')
			@slot('id')
				reAsignModal
			@endslot
			@slot('attributeEx')
				tabindex="-1"
			@endslot
			@slot('modalBody')
				@component('components.containers.container-form')
					<div class="col-span-2 md:col-start-2 md:col-end-4">
						@component('components.labels.label', ["label" => "Seleccione una sección:"]) @endcomponent
						@php
							$options	=	collect();
							foreach (App\SectionTickets::orderName()->get() as $section)
							{
								$options	=	$options->concat([["value"	=>	$section->idsectionTickets,	"description"	=>	$section->section]]);
							}
						@endphp
						@component('components.inputs.select', ["options" => $options, "attributeEx" => "name=\"section\" multiple=\"multiple\""]) @endcomponent
					</div>
				@endcomponent
			@endslot
			@slot('modalFooter')
				@component('components.buttons.button',
					[
						"variant"		=>	"primary",
						"attributeEx"	=>	"type=\"submit\" name=\"re_asign\" value=\"REASINAR SECCIÓN\" formaction=\"".route('tickets.re-asign.update',$ticket->idTickets)."\"",
						"label"			=>	"REASIGNAR SECCIÓN"
					])
				@endcomponent
				@component('components.buttons.button', ["variant" => "red", "attributeEx" => "data-dismiss=\"modal\" type=\"button\"", "label" => "<span class='icon-x'></span> Cerrar"]) @endcomponent
			@endslot
		@endcomponent
	@endcomponent
@endsection

@section('scripts')
	<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
	<script src="{{ asset('js/jquery-ui.js') }}"></script>
	<script>
		$(document).ready(function()
		{
			$.validate(
			{
				form		: '#container-alta',
				modules		: 'security',
				onSuccess	: function($form)
				{
					status = $('input[name="status"]').is(':checked');
					if(status == false)
					{
						swal('', 'Debe seleccionar un tipo de usuario', 'error');
						return false;
					}
					else
					{
						return true;
					}
				}
			});
			@php
				$selects = collect([
					[
						"identificator"				=> ".js-users",
						"placeholder"				=> "Seleccione un usuario",
						"language"					=> "es",
						"maximumSelectionLength"	=> "1"
					],
					[
						"identificator"				=> "section",
						"placeholder"				=> "Seleccione una sección",
						"language"					=> "es",
						"maximumSelectionLength"	=> "1"
					]
				]);
			@endphp
			@component('components.scripts.selects',["selects" => $selects]) @endcomponent
			$(document).on('change','input[name="status"]',function()
			{
				if ($('input[name="status"]:checked').val() == "1") 
				{
					$(".show-answer").slideDown('slow');
				}
				if ($('input[name="status"]:checked').val() == "2") 
				{
					$(".show-answer").slideUp('slow');
				}
			})
			.on('click','#re_asign',function()
			{
				@php
					$selects = collect([
						[
							"identificator"				=> "[name=\"section\"]",
							"placeholder"				=> "Seleccione una sección",
							"language"					=> "es",
							"maximumSelectionLength"	=> "1"
						]
					]);
				@endphp
				@component('components.scripts.selects',["selects" => $selects]) @endcomponent
			});
		});
	</script>
@endsection	