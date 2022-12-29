@extends('layouts.child_module')
@section('data')
	@php
		$values = ["minDate" => $mindate, "maxDate" => $maxdate];
	@endphp
	@component("components.labels.title-divisor") BUSCAR COMUNICADOS @endcomponent
	@component("components.forms.searchForm",
	[
		"hidden" => ["enterprise", "name", "folio"],
		"values" => $values,
		"form" => ['route' => 'releases.search', 'method' => 'GET', 'id'=>'formsearch']
	])
		@slot("contentEx")
			<div class="col-span-2">
				@component("components.labels.label") Título: @endcomponent
				@component("components.inputs.input-text") 
					@slot("attributeEx")
						type="text" name="titleRelease" value="{{ isset($titleRelease) ? $titleRelease : '' }}" class="input-text-search" id="input-search" placeholder="Ingrese el título"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Descripción: @endcomponent
				@component("components.inputs.input-text") 
					@slot("attributeEx")
						type="text" name="content" value="{{ isset($content) ? $content : '' }}" class="input-text-search" id="input-search" placeholder="Ingrese la descripción"
					@endslot
				@endcomponent
			</div>
		@endslot
	@endcomponent
	@if(count($releases) > 0)
		@php
			$modelHead = 
			[
				[
					["value" => "ID"],
					["value" => "Título"],
					["value" => "Descripción"],
					["value" => "Acción"],
				]
			];
			foreach($releases as $release)
			{
				$body =
				[
					[					
						"content" =>
						[
							["label" => $release->idreleases]
						]
					],
					[
						"content" =>
						[
							["label" => htmlentities($release->title)]
						]
					],
					[
						"content" =>
						[
							["label" => nl2br(htmlentities($release->content))]
						]
					],
					[
						"content" =>
						[
							["kind" => "components.buttons.button", "label" => "<span class=\"icon-pencil\"></span>", "variant" => "success", "buttonElement" => "a", "attributeEx" => "title=\"Editar Comunidado\" href=\"".route('releases.edit.release',$release->idreleases)."\""],
							["kind" => "components.buttons.button", "label" => "<span class=\"icon-bin\"></span>", "variant" => "red", "buttonElement" => "a", "attributeEx" => "title=\"Eliminar Comunidado\" href=\"".route('releases.delete.release',$release->idreleases)."\"", "classEx" => "delete-release"],
						]
					],
				];
				$modelBody[] = $body;
			}
		@endphp
		@Table(["modelHead" => $modelHead, "modelBody" => $modelBody]) @endTable
		{{ $releases->appends($_GET)->links() }}
	@else
		@component("components.labels.not-found", ["attributeEx" => "id=\"not-found\""]) Resultado no encontrado: @endcomponent
	@endif
@endsection
@section('scripts')
<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
<script src="{{ asset('js/jquery-ui.js') }}"></script>
<script src="{{ asset('js/jquery.numeric.js') }}"></script>
<script src="{{ asset('js/datepicker.js') }}"></script>
	<script>
		$(document).ready(function(){
			$(function() 
			{
				$( ".datepicker" ).datepicker({ maxDate: 0, dateFormat: "dd-mm-yy" });
			});
			$(document).on('click','.delete-release',function(e)
			{
				e.preventDefault();
				url = $(this).attr('href');
				swal({
					title		: "",
					text		: "Confirme que desea eliminar el comunicado.",
					icon		: "warning",
					buttons		: ["Cancelar", "Eliminar"],
					dangerMode	: true,
				})
				.then((isConfirm) => {
					if (isConfirm)
					{
						swal('Cargando',{
							icon: '{{ asset(getenv('LOADING_IMG')) }}',
							button: false,
							closeOnClickOutside: false,
							closeOnEsc: false
						});
						form = $('<form></form>').attr('action',url).attr('method','post').append('@csrf').append('@method("delete")');
						$(document.body).append(form);
						form.submit();
					}
				});
			});
		});
	</script>
@endsection