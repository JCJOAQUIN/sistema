@extends('layouts.child_module')
@section('data')	
	@component('components.forms.form', ["attributeEx" => "method=\"POST\" id=\"container-alta\" action=\"".route('tickets.solve.follow', $ticket->idTickets)."\"", "methodEx" => "PUT", "files" => true])
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
			if ($ticket->documentsTickets()->count()>0) {
				foreach ($ticket->documentsTickets as $document)
				{
					$fileComponent[]	=
					[
						"kind"	=>	"components.buttons.button", "classEx" => "text-center w-24", "variant" => "secondary", "buttonElement" => "a", "attributeEx" => "target=\"_blank\" title=\"".$document->path."\" href=\"".url('docs/tickets/'.$document->path)."\"", "label" => "Archivo"
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
		@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "Historial de respuestas"]) @endcomponent
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
								@if ($answer->documentsTickets()->count()>0)
									<div class="mt-8">
										@component('components.labels.label', ["classEx" => "inline-block font-bold", "label" => "Archivo(s) adjunto(s):"]) @endcomponent
										@foreach ($answer->documentsTickets as $document)
											@component('components.buttons.button', ["classEx" => "inline-block", "variant" => "secondary", "attributeEx" => "target=\"_blank\" title=\"".$document->path."\" href=\"".url('docs/tickets/'.$document->path)."\"", "buttonElement" => "a", "label" => "Archivo"]) @endcomponent
										@endforeach
									</div>
								@endif
							</div>
						</div>
					@endforeach
				</div>
			</div>
		@endif
		@if (count($ticket->answerTicket) == 0 && $ticket->idStatusTickets == 1)
			@component('components.containers.container-form', ["classEx" => "mt-8"])
				<div class="show-answer col-span-2 md:col-span-4">
					<div class="mb-4">
						@component('components.labels.label', ["label" => "Respuesta:"]) @endcomponent
						@component('components.inputs.text-area', ["attributeEx" => "name=\"answer\" data-validation=\"required\" placeholder=\"Ingrese una respuesta\""]) @endcomponent
					</div>
					@component('components.labels.label', ["label" => "Archivo adjunto (opcional):"]) @endcomponent
					<div class="col-span-2 md:col-span-4 grid grid-cols-1 md:grid-cols-2 gap-6 hidden" id="documents"></div>
					<div class="col-span-2 md:col-span-4 grid justify-items-center md:justify-items-start">
						@component('components.buttons.button', ["variant" => "warning", "attributeEx" => "type=\"button\" name=\"addDoc\" id=\"addDoc\"", "classEx" => "", "label" => "<span class='icon-plus'></span> Agregar documento"]) @endcomponent
					</div>
				</div>
			@endcomponent
		@endif
		@if (count($ticket->answerTicket) >= 1 && ($ticket->idStatusTickets == 1 || $ticket->idStatusTickets == 2))
			@component('components.containers.container-form', ["classEx" => "mt-8"])
				<div class="show-answer col-span-2 md:col-span-4">
					<div class="mb-4">
						@component('components.labels.label', ["label" => "Respuesta:"]) @endcomponent
						@component('components.inputs.text-area', ["attributeEx" => "name=\"answer\" data-validation=\"required\" placeholder=\"Ingrese una respuesta\""]) @endcomponent
					</div>
					@component('components.labels.label', ["label" => "Archivo adjunto (opcional):"]) @endcomponent
					<div class="col-span-2 md:col-span-4 grid grid-cols-1 md:grid-cols-2 gap-6 hidden" id="documents"></div>
					<div class="col-span-2 md:col-span-4 grid justify-items-center md:justify-items-start">
						@component('components.buttons.button', ["variant" => "warning", "attributeEx" => "type=\"button\" name=\"addDoc\" id=\"addDoc\"", "classEx" => "", "label" => "<span class='icon-plus'></span> Agregar documento"]) @endcomponent
					</div>
				</div>
			@endcomponent
		@endif
		@if (count($ticket->answerTicket) >= 1 && $ticket->idStatusTickets == 3)
			@if ($ticket->idStatusTickets == 1 || $ticket->idStatusTickets == 3)
				@component('components.containers.container-form', ["classEx" => "mt-8"])
					<div class="col-span-2">
						@component('components.labels.label', ["label" => "¿Resolvió su problema?"]) @endcomponent
						<div class="flex row mb-4 space-x-2">
							@component('components.buttons.button-approval')
								@slot('attributeEx')
									type="radio" name="status" id="no" value="1"
								@endslot
								@slot('label')
									No
								@endslot
							@endcomponent
							@component('components.buttons.button-approval')
								@slot('attributeEx')
									type="radio" name="status" id="si" value="2"
								@endslot
								@slot('label')
									Sí
								@endslot
							@endcomponent
						</div>
					</div>
					<div class="show-answer col-span-2 md:col-span-4">
						<div class="mb-4">
							@component('components.labels.label', ["label" => "Respuesta:"]) @endcomponent
							@component('components.inputs.text-area', ["attributeEx" => "name=\"answer\" data-validation=\"required\" placeholder=\"Ingrese una respuesta\""]) @endcomponent
						</div>
						@component('components.labels.label', ["label" => "Archivo adjunto (opcional):"]) @endcomponent
						<div class="col-span-2 md:col-span-4 grid grid-cols-1 md:grid-cols-2 gap-6 hidden" id="documents"></div>
						<div class="col-span-2 md:col-span-4 grid justify-items-center md:justify-items-start">
							@component('components.buttons.button', ["variant" => "warning", "attributeEx" => "type=\"button\" name=\"addDoc\" id=\"addDoc\"", "classEx" => "", "label" => "<span class='icon-plus'></span> Agregar documento"]) @endcomponent
						</div>
					</div>
				@endcomponent
			@endif
		@endif
		<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mt-8">
			@if ($ticket->idStatusTickets == 1 || $ticket->idStatusTickets == 2 || $ticket->idStatusTickets == 3)
				@component('components.buttons.button', ["variant" => "primary", "attributeEx" => "type=\"submit\" name=\"save\" value=\"ENVIAR\"", "label" => "ENVIAR"]) @endcomponent
			@endif
			@if ($ticket->idStatusTickets == 4)
				@component('components.buttons.button', ["variant" => "primary", "attributeEx" => "type=\"submit\" name=\"save\" value=\"REABRIR\" formaction=\"".route('tickets.reopen',$ticket->idTickets)."\"", "label" => "REABRIR"]) @endcomponent
			@endif
			@php
				$href	=	isset($option_id) ? url(App\Module::find($option_id)->url) : url(App\Module::find($child_id)->url);
			@endphp
			@component('components.buttons.button', ["classEx" => "load-actioner", "variant" => "reset", "buttonElement" => "a", "attributeEx" => "href=\"".$href."\"", "label" => "REGRESAR"]) @endcomponent
		</div>
	@endcomponent
@endsection
@section('scripts')
<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
<script src="{{ asset('js/jquery-ui.js') }}"></script>
<script>
	$(document).ready(function()
	{
		@if (count($ticket->answerTicket) >= 1 && ($ticket->idStatusTickets == 1 || $ticket->idStatusTickets == 2 || $ticket->idStatusTickets == 3))
			$.validate(
			{
				form		: '#container-alta',
				onError   	: function($form)
				{
					swal('', '{{ Lang::get("messages.form_error") }}', 'error');
				},
				onSuccess	: function($form)
				{
					if($('.table').find($('input[name="status"]')).length>0)
					{
						status = $('input[name="status"]').is(':checked');
						if(status == "false")
						{
							swal('', 'Por favor seleccione si resolvió o no su problema.', 'error');
							return false;
						}
					}
					else
					{
						if($('#documents').find('.path').length>0)
						{
							flag = true;
							$('.path').each(function()
							{
								if($(this).val() == "" )
								{
									swal("","Por favor cargue todos los archivos que tenga pendientes.","error");
									flag = false;
								}
							});
							if(flag)
							{
								swal({
								icon: '{{ asset(getenv('LOADING_IMG')) }}',
								button: false,
								closeOnClickOutside: false,
								closeOnEsc: false
								});
								return true;
							}
							else
							{
								return false;
							}
						}
						else
						{
							swal({
							icon: '{{ asset(getenv('LOADING_IMG')) }}',
							button: false,
							closeOnClickOutside: false,
							closeOnEsc: false
							});
							return true;
						}
					}
				}
			});
		@endif
		@php
			$selects = collect([
				[
					"identificator"				=> ".js-users",
					"placeholder"				=> "Seleccione un usuario",
					"language"					=> "es",
					"maximumSelectionLength"	=> "1"
				]
			]);
		@endphp
		@component('components.scripts.selects',["selects" => $selects]) @endcomponent
	});
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
 	.on('click','#addDoc',function()
	{
		if ($('#documents').hasClass('hidden'))
		{
			$('#documents').removeClass('hidden');
		}
		@php
			$newDoc = html_entity_decode((String)view('components.documents.upload-files',[
				"attributeExInput"		=>	"name=\"path\"",
				"classExRealPath"		=>	"path",
				"attributeExRealPath"	=>	"name=\"realPath[]\"",
				"classExInput"			=>	"pathActioner",
				"classExDelete"			=>	"delete-doc",
			]));
		@endphp
		newDoc			=	'{!!preg_replace("/(\r)*(\n)*/", "", $newDoc)!!}';
		containerNewDoc	=	$(newDoc);
		$('#documents').append(containerNewDoc);
	})
	.on('click','.delete-doc',function()
	{
		swal(
		{
			icon	: '{{ asset(getenv('LOADING_IMG')) }}',
			button	: false
		});
		actioner		= $(this);
		uploadedName	= $(this).parents('.docs-p').find('input[name="realPath[]"]');
		formData		= new FormData();
		formData.append(uploadedName.attr('name'),uploadedName.val());
		$.ajax(
		{
			type		: 'post',
			url			: '{{ route("tickets.upload") }}',
			data		: formData,
			contentType	: false,
			processData	: false,
			success		: function(r)
			{
				swal.close();
				actioner.parents('.docs-p').remove();
			},
			error		: function()
			{
				swal.close();
				actioner.parents('.docs-p').remove();
			}
		});
		$(this).parents('div.docs-p').remove();
	})
	.on('change','.pathActioner',function(e)
	{
		filename		= $(this);
		uploadedName 	= $(this).parent('.uploader-content').siblings('input[name="realPath[]"]');
		extention		= /\.jpg|\.jpeg|\.png|\.doc|\.docx|\.ppt|\.pptx|\.xls|\.xlsx|\.zip|\.pdf/i;
		
		if (this.files[0].size>315621376)
		{
			swal('', 'El tamaño máximo de su archivo no debe ser mayor a 300Mb', 'warning');
		}
		else
		{
			$(this).css('visibility','hidden').parent('.uploader-content').addClass('loading').removeClass(function (index, css)
			{
				return (css.match (/\bimage_\S+/g) || []).join(' '); // removes anything that starts with "image_"
			});
			formData	= new FormData();
			formData.append(filename.attr('name'), filename.prop("files")[0]);
			formData.append(uploadedName.attr('name'),uploadedName.val());
			$.ajax(
			{
				type		: 'post',
				url			: '{{ route("tickets.upload") }}',
				data		: formData,
				contentType	: false,
				processData	: false,
				success		: function(r)
				{
					if(r.error=='DONE')
					{
						$(e.currentTarget).removeAttr('style').parent('.uploader-content').removeClass('loading').addClass('image_success');
						$(e.currentTarget).parent('.uploader-content').siblings('input[name="realPath[]"]').val(r.path);
					}
					else
					{
						swal('',r.message, 'error');
						$(e.currentTarget).removeAttr('style').parent('.uploader-content').removeClass('loading');
						$(e.currentTarget).val('');
						$(e.currentTarget).parent('.uploader-content').siblings('input[name="realPath[]"]').val('');
					}
				},
				error: function()
				{
					swal('', 'Ocurrió un error durante la carga del archivo, intente de nuevo, por favor', 'error');
					$(e.currentTarget).removeAttr('style').parent('.uploader-content').removeClass('loading');
					$(e.currentTarget).val('');
					$(e.currentTarget).parent('.uploader-content').siblings('input[name="realPath[]"]').val('');
				}
			})
		}
	});
</script>
@endsection
