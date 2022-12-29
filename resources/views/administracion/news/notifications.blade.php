@extends('layouts.child_module')

@section('data')
	@component('components.labels.title-divisor') PROGRAMAR NOTIFICACIÓN DE NOTICIAS @endcomponent
	@component('components.forms.form',["attributeEx" => "method=\"POST\" id=\"container-form\" action=\"".route('news-api.notification-store')."\""])
		@component('components.containers.container-form')
			<div class="col-span-4 md:col-start-2 md:col-span-2 md:col-end-4">
				@component('components.labels.label') Descripción: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" name="description" placeholder="Ingrese una descripción" value="{{ isset($search) ? $search : '' }}" data-validation="required"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2 md:col-span-4 grid justify-items-center md:justify-items-start">
				@component('components.buttons.button',["variant" => "warning"])
					@slot('attributeEx')
						type="submit"
					@endslot
					<span class="icon-plus"></span>
					<span>Agregar</span>
				@endcomponent
			</div>
		@endcomponent
	@endcomponent
	@if(count($notifications)>0)
		@php
			$body		= [];
			$modelBody	= [];
			$modelHead	= [ "ID","Descripción","Estado","Acción" ];
			
			foreach($notifications as $key=>$value)
			{
				$body = 
				[
					[ 
						"content" =>
						[
							[ "label" => $key+1 ]
						]
					],
					[ 
						"content" =>
						[
							[ "label" => htmlentities($value->description) ]
						]
					],
					[ 
						"content" =>
						[
							[ "label" => $value->statusData() ]
						]
					],
				];
				if($value->status == '1')
				{
					array_push($body,
					[
						"content" =>
						[
							[
								"kind"			=> "components.buttons.button",
								"variant"		=> "red",
								"buttonElement" => "a",
								"attributeEx"	=> "title=\"Deshabilitar\" href=\"".route('news-api.notification-inactive',$value->id)."\"",
								"label"			=> "<span class=\"icon-x\"></span>",
								"classEx"		=> "inactive"
							]
						]
					]);
				}
				else
				{
					array_push($body,
					[
						"content" =>
						[
							[
								"kind"			=> "components.buttons.button",
								"variant"		=> "success",
								"buttonElement" => "a",
								"attributeEx"	=> "title=\"Habilitar\" href=\"".route('news-api.notification-active',$value->id)."\"",
								"label"			=> "<span class=\"icon-check\"></span>",
								"classEx"		=> "active"
							]
						]
					]);
				}
				$modelBody[] = $body;
			}
 		@endphp		
		@component('components.tables.alwaysVisibleTable',
		[
			"modelBody" => $modelBody,
			"modelHead"	=> $modelHead
		])
		@endcomponent
	@else
		@component('components.labels.not-found') @endcomponent
	@endif
@endsection

@section('scripts')
	<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
	<script src="{{ asset('js/jquery-ui.js') }}"></script>
	<script type="text/javascript" src="{{ asset('js/moment.min.js') }}"></script>
	<script src="{{ asset('js/daterangepicker.js') }}"></script>
	<link rel="stylesheet" type="text/css" href="{{ asset('css/daterangepicker.css') }}" />
	<script src="{{ asset('js/jquery.numeric.js') }}"></script>
	<script type="text/javascript">
		$(document).ready(function()
		{
			$(document).on('click','.inactive',function(e)
			{
				e.preventDefault();
				url = $(this).attr('href');
				swal({
					title		: "",
					text		: "Confirme que desea desactivar la notificación",
					icon		: "warning",
					buttons		:
					{
						cancel:
						{
							text		: "Cancelar",
							value		: null,
							visible		: true,
							closeModal	: true,
						},
						confirm:
						{
							text		: "Desactivar",
							value		: true,
							closeModal	: false
						}
					},
					dangerMode	: true,
				})
				.then((a) =>
				{
					if (a)
					{
						$.ajax(
						{
							url		: url,
							type	: 'post',
							data	: {'_method': 'delete'},
							success	: function(result)
							{
								if(result)
								{
									swal({
										title				: '',
										text				: 'Notificación desactivada correctamente',
										icon				: 'success',
										closeOnClickOutside	: false,
										closeOnEsc			: false,
									})
									.then((value) => 
									{
										window.location.reload();
									});
								}
								else
								{
									swal('','Error al desactivar la notificación; por favor intente más tarde','error');
								}
							},
							error : function()
							{
								swal('','Sucedió un error, por favor intente de nuevo.','error');
							}
						});
					}
				});
			})
			.on('click','.active',function(e)
			{
				e.preventDefault();
				url = $(this).attr('href');
				swal({
					title	: "",
					text	: "Confirme que desea activar la notificación",
					icon	: "warning",
					buttons	:
					{
						cancel:
						{
							text		: "Cancelar",
							value		: null,
							visible		: true,
							closeModal	: true,
						},
						confirm:
						{
							text		: "Activar",
							value		: true,
							closeModal	: false
						}
					},
					dangerMode	: true,
				})
				.then((a) =>
				{
					if (a)
					{
						$.ajax(
						{
							url		: url,
							type	: 'post',
							data	: {'_method': 'delete'},
							success	: function(result)
							{
								if(result)
								{
									swal({
										title				: '',
										text				: 'Notificación activada correctamente',
										icon				: 'success',
										closeOnClickOutside	: false,
										closeOnEsc			: false,
									})
									.then((value) =>
									{
										window.location.reload();
									});
								}
								else
								{
									swal('','Error al activar notificación; por favor intente más tarde','error');
								}
							},
							error : function()
							{
								swal('','Sucedió un error, por favor intente de nuevo.','error');
							}
						});
					}
				});
			});
		}); 
	</script>
@endsection