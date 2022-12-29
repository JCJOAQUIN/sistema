@extends('layouts.child_module')
@section('data')
	@component('components.forms.form', ["attributeEx" => "method=\"POST\" id=\"container-alta\" action=\"".route('tickets.new.save')."\"", "files" => true])
		@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "NUEVO TICKET"]) @endcomponent
		@component('components.containers.container-form')
			<div class="col-span-2">
				@component('components.labels.label', ["label" => "Sección:"]) @endcomponent
				@php
					$options	=	collect();
					foreach (App\SectionTickets::orderName()->get() as $section)
					{
						$options	=	$options->concat([["value"	=>	$section->idsectionTickets,	"description"	=>	$section->section]]);
					}
				@endphp
				@component('components.inputs.select', ["options" => $options, "classEx" => "js-sections", "attributeEx" => "name=\"section\" data-validation=\"required\""]) @endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label', ["label" => "Tipo de ticket:"]) @endcomponent
				@php
					$options	=	collect();
					foreach (App\TicketType::orderName()->get() as $type)
					{
						$options	=	$options->concat([["value"	=>	$type->idTypeTickets,	"description"	=>	$type->type]]);
					}
				@endphp
				@component('components.inputs.select', ["options" => $options, "classEx" => "js-types", "attributeEx" => "name=\"type\" data-validation=\"required\""]) @endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label', ["label" => "Prioridad:"]) @endcomponent
				@php
					$options	=	collect();
					foreach (App\TicketPriority::orderName()->get() as $priority)
					{
						$options	=	$options->concat([["value"	=>	$priority->idPriorityTickets,	"description"	=>	$priority->priority]]);
					}
				@endphp
				@component('components.inputs.select', ["options" => $options, "classEx" => "removeselect js-priority", "attributeEx" => "name=\"priority\" data-validation=\"required\""]) @endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label', ["label" => "Asunto:"]) @endcomponent
				@component('components.inputs.input-text', ["attributeEx" => "type=\"text\" id=\"subject\" name=\"subject\" name=\"subject\" placeholder=\"Ingrese el asunto\" data-validation=\"required\""]) @endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label', ["label" => "Mensaje:"]) @endcomponent
				@component('components.inputs.text-area', ["attributeEx" => "name=\"question\" placeholder=\"Ingrese una descripción\" data-validation=\"required\""]) @endcomponent
			</div>
			@component('components.labels.label', ["label" => "Archivo adjunto (opcional):", "classEx" => "md:col-span-4 col-span-2"]) @endcomponent
			<div class="col-span-2 md:col-span-4 grid grid-cols-1 md:grid-cols-2 gap-6 hidden" id="documents"></div>
			<div class="md:col-span-4 col-span-2" >
				@component('components.buttons.button', ["variant" => "warning", "attributeEx" => "type=\"button\" name=\"addDoc\" id=\"addDoc\"", "label" => "<span class=\"icon-plus\"> </span> Agregar documento"]) @endcomponent
			</div>
		@endcomponent
		<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mt-4">
			@component('components.buttons.button', ["variant" => "primary", "attributeEx" => "type=\"submit\" name=\"save\"", "label" => "ENVIAR"]) @endcomponent
		</div>
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
			modules : 'file',
			form: '#container-alta',
			onError   : function($form)
			{
				swal('', '{{ Lang::get("messages.form_error") }}', 'error');
			},
			onSuccess : function($form)
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
		});
		@php
			$selects = collect([
				[
					"identificator"				=> ".js-types",
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
					"identificator"				=> ".js-sections",
					"placeholder"				=> "Seleccione la sección",
					"language"					=> "es",
					"maximumSelectionLength"	=> "1"
				]
			]);
		@endphp
		@component('components.scripts.selects',["selects" => $selects]) @endcomponent
		$(document).on('click','#addDoc',function()
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
						swal('', '{{ Lang::get("messages.file_upload_error") }}', 'error');
						$(e.currentTarget).removeAttr('style').parent('.uploader-content').removeClass('loading');
						$(e.currentTarget).val('');
						$(e.currentTarget).parent('.uploader-content').siblings('input[name="realPath[]"]').val('');
					}
				})
			}
		});
	});
</script>
@endsection
