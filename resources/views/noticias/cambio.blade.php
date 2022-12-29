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
		@component('components.forms.form', ["attributeEx" => "method=\"POST\" id=\"container-alta\" action=\"".route('news.update', $new->idnews)."\"", "methodEx" => "PUT", "files" => true])
			@component('components.containers.container-form', ["attributeEx" => "container-data"])
				<div class="col-span-2">
					@component('components.labels.label', ["label" => "Título:"]) @endcomponent
					@component('components.inputs.input-text', ["attributeEx" => "type=\"text\" name=\"title\" placeholder=\"Ingrese el título\" data-validation=\"required\" value=\"".$new->title."\""]) @endcomponent
				</div>
				<div class="md:col-span-4 col-span-2">
					@component('components.labels.label', ["label" => "Detalles:"]) @endcomponent
					@component('components.inputs.text-area', ["attributeEx" => "placeholder=\"Ingrese los detalles\" id=\"details\" name=\"details\" data-validation=\"required\""]) {{$new->details}} @endcomponent
				</div>
				<div class="col-span-2 md:col-span-4 grid grid-cols-1 md:grid-cols-2 gap-6 hidden mt-8" id="documents"> </div>
				<div class="md:col-span-4 col-span-2" >
					<?php
						$info = new SplFileInfo(asset('images/news').'/'.$new->path);
					?>
					@if ($info->getExtension() != 'jpg' && $info->getExtension() != 'png' && $info->getExtension() != 'jpeg')
						@if (isset($new->path) && $new->path!='')
							<div id="show-file">
								@php
									$modelHead	=	[];
									$body		=	[];
									$modelBody	=	[];
									$modelHead	=	["Archivo adjunto", "Acción"];
									$body	=
									[
										[
											"content"	=>	[["kind"	=>	"components.buttons.button",	"variant"	=>	"secondary",	"classEx"	=>	"filePath", "attributeEx"	=>	"value=\"".$new->path."\" href=\"".asset('images/news').'/'.$new->path."\"",	"label"	=>	"Archivo", "buttonElement"	=>	"a"]],
										],
										[
											"content"	=>	[["kind"	=>	"components.buttons.button",	"variant"	=>	"red",	"classEx"	=>	"remove-file", "attributeEx"	=>	"type=\"button\"", "label"	=>	"<span class=\"icon-bin\"></span> Eliminar Archivo"]],
										],
									];
									$modelBody[]	=	$body;
								@endphp
								@component('components.tables.alwaysVisibleTable', ["modelHead" => $modelHead, "modelBody" => $modelBody])@endcomponent
							</div>
							<div class="hidden" id="new-file-image">
								@component('components.labels.label', ["label" => "Imagen/Archivo"]) @endcomponent
								@component('components.inputs.input-text', ["attributeEx" => "data-validation=\"size\" accept=\".pdf,.jpg,.png\" name=\"path\" type=\"file\""])@endcomponent
								@component('components.inputs.input-text', ["attributeEx" => "type=\"hidden\" name=\"removeImage\""])@endcomponent
							</div>
						@endif
					@elseif ($info->getExtension() == 'jpg' || $info->getExtension() == 'png' || $info->getExtension() == 'jpeg')
						@if (isset($new->path) && $new->path!='')
							<div id="show-image">
								@php
									$modelHead	=	[];
									$body		=	[];
									$modelBody	=	[];
									$modelHead	=	["Imagen adjunta", "Acción"];
									$body	=
									[
										[
											"classEx"	=>	"flex justify-center",
											"content"	=>
											[
												["kind"	=>	"components.labels.label", "classEx"	=>	"w-48",	"label" => "<img class=\"enterprise-module-logo\" src=\"".asset('images/news').'/'.$new->path."\">"]
											],
										],
										[
											"content"	=>	[["kind"	=>	"components.buttons.button",	"variant"	=>	"red",	"classEx"	=>	"remove-file", "attributeEx"	=>	"type=\"button\"", "label"	=>	"<span class=\"icon-bin\"></span> Eliminar Imagen"]],
										],
									];
									$modelBody[]	=	$body;
								@endphp
								@component('components.tables.alwaysVisibleTable', ["modelHead" => $modelHead, "modelBody" => $modelBody])@endcomponent
							</div>
							<div class="hidden" id="new-file-image">
								@component('components.labels.label', ["label" => "Imagen/Archivo"]) @endcomponent
								@component('components.inputs.input-text', ["attributeEx" => "data-validation=\"size\" accept=\".pdf,.jpg,.png\" name=\"path\" type=\"file\""])@endcomponent
								@component('components.inputs.input-text', ["attributeEx" => "type=\"hidden\" name=\"removeImage\""])@endcomponent
							</div>
							<div class="w-full hidden" id="addDocuments">
								@component('components.labels.label',	["label"	=>	"Cargar archivo:"]) @endcomponent
								@component('components.buttons.button',	["variant"	=>	"warning", "attributeEx" => "type=\"button\" name=\"addDoc\" id=\"addDoc\"", "label" => "<span class=\"icon-plus\"> </span> Agregar archivo"]) @endcomponent
							</div>
							@php
								$validateImg = 'mime size';
							@endphp
						@else
							@php
								$validateImg = 'required mime size';
							@endphp
						@endif
					@else
						<div id="addDocuments">
							@component('components.labels.label', ["label" => "Cargar archivo:"]) @endcomponent
							@component('components.buttons.button', ["variant" => "warning", "attributeEx" => "type=\"button\" name=\"addDoc\" id=\"addDoc\"", "label" => "<span class=\"icon-plus\"> </span> Agregar archivo"]) @endcomponent
						</div>
					@endif
				</div>
			@endcomponent
			<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mt-4">
				@component('components.buttons.button', ["variant" => "primary", "attributeEx" => "type=\"submit\" name=\"enviar\" value=\"ACTUALIZAR\"", "label" => "ACTUALIZAR"]) @endcomponent
				@php
					$href	=	isset($option_id) ? url(App\Module::find($option_id)->url) : url(App\Module::find($child_id)->url);
				@endphp
				@component('components.buttons.button', ["variant" => "reset", "buttonElement" => "a", "attributeEx" => "value=\"REGRESAR\" href=\"".$href."\"", "classEx" => "load-actioner", "label" => "REGRESAR"]) @endcomponent
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
		fileRemove = $('.filePath').text();
		if(fileRemove != '')
		{
			$('#show-file, #show-file-button').removeClass('hidden');
		}else{
			$('#show-file, #show-file-button').addClass('hidden');
		}
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
			@php
				$newDoc = html_entity_decode((String)view('components.documents.upload-files',[
					"attributeExInput"		=>	"name=\"path\" accept=\".pdf,.jpg,.png\"",
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
			extention		= /\.jpg|\.png|\.jpeg|\.pdf/;
			
			if (filename.val().search(extention) == -1)
			{
				swal('', 'El tipo de archivo no es soportado, por favor seleccione una imagen jpg, png o un archivo pdf', 'warning');
				$(this).val('');
			}
			else if (this.files[0].size>315621376)
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
		.on('click','.remove-file',function()
		{
			$('#show-image, #show-file').remove();
			$('.remove-file').hide();
			$('#documents,#addDocuments').removeClass('hidden');
			$('input[name="removeImage"]').val('1');
		});
	});
</script>
@endsection