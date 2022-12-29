@extends('layouts.layout')
@section('title', $title)
@section('content')
	<div class="w-full">
		@component("components.labels.title-config", ["classEx" => "mt-6"])
			{{ $title }}
		@endcomponent
		<div class="text-center text-gray-400 mb-6 italic">
			{{ $details }}
		</div>
		<hr class="bg-amber-500 h-px border-0 mb-6">
		@component("components.buttons.tutorial") 
			@slot("child_id") {{isset($child_id) ? $child_id : null}} @endslot
			@slot("option_id") {{isset($option_id) ? $option_id : null}} @endslot 
		@endcomponent
		@if(count(Auth::user()->module->whereIn('id',[81,82,104]))>0)
			Acciones:
		@endif
		<div class="content-start items-center justify-center text-center w-full grid grid-cols-12 mb-4">
			@foreach(Auth::user()->module->where('father',80) as $key)
				@component('components.buttons.button-secondary')
					@slot('classEx')
						lg:col-span-3 md:col-span-6 col-span-12
						@if($key['name'] == "Ver")
							@if(isset($option_id) && $option_id==$key['id'])
								hidden
							@else
								hidden
							@endif
						@else
							@if(isset($option_id) && $option_id==$key['id'])
								bg-orange-600 text-white shadow-md
							@else
								text-black
							@endif
						@endif
					@endslot
					@slot('href')
						{{ url($key['url']) }}
					@endslot
					{{ $key['name'] }}
				@endcomponent
			@endforeach
		</div>
		@component('components.forms.form', ["attributeEx" => "method=\"POST\" id=\"container-alta\" action=\"".route('news.store')."\"", "files" => true])
			@component('components.containers.container-form', ["attributeEx" => "container-data"])
				<div class="col-span-2">
					@component('components.labels.label', ["label" => "Título:"]) @endcomponent
					@component('components.inputs.input-text', ["attributeEx" => "type=\"text\" name=\"title\" placeholder=\"Ingrese el título\" data-validation=\"required\""]) @endcomponent
				</div>
				<div class="md:col-span-4 col-span-2">
					@component('components.labels.label', ["label" => "Detalles:"]) @endcomponent
					@component('components.inputs.text-area', ["attributeEx" => "placeholder=\"Ingrese los detalles\" id=\"details\" name=\"details\" data-validation=\"required\""]) @endcomponent
				</div>
				<div class="col-span-2 md:col-span-4 grid grid-cols-1 md:grid-cols-2 gap-6 hidden mt-8" id="documents">
				</div>
				<div class="md:col-span-4 col-span-2" >
					@component('components.labels.label', ["label" => "Cargar archivo:"]) @endcomponent
					@component('components.buttons.button', ["variant" => "warning", "attributeEx" => "type=\"button\" name=\"addDoc\" id=\"addDoc\"", "label" => "<span class=\"icon-plus\"> </span> Agregar archivo"]) @endcomponent
				</div>
			@endcomponent
			<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mt-4">
				@component('components.buttons.button', ["variant" => "primary", "attributeEx" => "type=\"submit\" name=\"enviar\" value=\"CREAR NOTICIA\"", "label" => "CREAR NOTICIA"]) @endcomponent
				@component('components.buttons.button', ["variant" => "reset", "attributeEx" => "type=\"reset\" name=\"borra\" value=\"Borrar campos\"", "classEx" => "btn-delete-form", "label" => "Borrar campos"]) @endcomponent
			</div>
		@endcomponent
	</div>
@endsection

@section('scripts')
<script src="{{ asset('js/jquery-ui.js') }}"></script>
<script src="{{ asset('js/jquery.numeric.js') }}"></script>
<link rel="stylesheet" type="text/css" href="{{ asset("tinymce/skins/lightgray/content.inline.min.css") }}">
<script src="{{ asset('tinymce/tinymce.min.js') }}"></script>
<script type="text/javascript">
	tinymce.init({
	  selector: 'textarea', 
	  auto_focus: 'element1',
	  language: 'es_MX'
	});
	$(document).ready(function() {
		$.validate(
		{
			modules : 'file',
			form: '#container-alta',
			onSuccess : function($form)
			{
				body = tinyMCE.get('details').getContent();
				if(body == "")
				{
					swal('', 'Debe agregar contenido a la noticia', 'error');
					$('textarea[id="details"]').addClass('error');
					return false;
				}	
				else
				{
					return true;
				}
			}
		});
		$(document).on('click','.btn-delete-form',function(e)
		{
			e.preventDefault();
			form = $(this).parents('form');
			swal({
				title		: "Limpiar formulario",
				text		: "¿Confirma que desea limpiar el formulario?",
				icon		: "warning",
				buttons		: ["Cancelar","OK"],
				dangerMode	: true,
			})
			.then((willClean) =>
			{
				if(willClean)
				{
					$('#body').html('');
					$('.removeselect').val(null).trigger('change');
					form[0].reset();
				}
				else
				{
					swal.close();
				}
			});
		})
		.on('click','#addDoc', function()
		{
			if ($('#documents').hasClass('hidden'))
			{
				$('#documents').removeClass('hidden');
			}
			@php
				$newDoc = html_entity_decode((String)view('components.documents.upload-files',[
					"attributeExInput"		=>	"name=\"path\"",
					"classExRealPath"		=>	"path",
					"attributeExRealPath"	=>	"name=\"realPath\"",
					"classExInput"			=>	"pathActioner",
					"noDelete"				=>	"true",
				]));
			@endphp
			newDoc			=	'{!!preg_replace("/(\r)*(\n)*/", "", $newDoc)!!}';
			containerNewDoc	=	$(newDoc);
			$('#documents').append(containerNewDoc);
			$(':button[name="addDoc"]').prop('disabled', true);
		})
		.on('change','.pathActioner',function(e)
		{
			filename		= $(this);
			uploadedName 	= $(this).parent('.uploader-content').siblings('input[name="realPath"]');
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
					url			: '{{ route("news.upload") }}',
					data		: formData,
					contentType	: false,
					processData	: false,
					success		: function(r)
					{
						if(r.error=='DONE')
						{
							$(e.currentTarget).removeAttr('style').parent('.uploader-content').removeClass('loading').addClass('image_success');
							$(e.currentTarget).parent('.uploader-content').siblings('input[name="realPath"]').val(r.path);
							$(e.currentTarget).val('');
						}
						else
						{
							swal('',r.message, 'error');
							$(e.currentTarget).removeAttr('style').parent('.uploader-content').removeClass('loading');
							$(e.currentTarget).val('');
							$(e.currentTarget).parent('.uploader-content').siblings('input[name="realPath"]').val('');
						}
					},
					error: function()
					{
						swal('', 'Ocurrió un error durante la carga del archivo, intente de nuevo, por favor', 'error');
						$(e.currentTarget).removeAttr('style').parent('.uploader-content').removeClass('loading');
						$(e.currentTarget).val('');
						$(e.currentTarget).parent('.uploader-content').siblings('input[name="realPath"]').val('');
					}
				})
			}
		})
	});
</script>
@endsection
