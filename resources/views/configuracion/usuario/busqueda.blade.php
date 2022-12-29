@extends('layouts.child_module')

@section('data')

	@component("components.forms.form", ["attributeEx" => "route=\"".route('user.search')."\" method=\"GET\" id=\"formsearch\""])
		@component("components.labels.title-divisor") BUSCAR USUARIO @endcomponent
		@component("components.containers.container-form") 
			<div class="col-span-2">
				@component("components.labels.label") Nombre: @endcomponent
				@php
					isset($name) ? $name = $name : $name = '';
					isset($email) ? $email = $email : $email = '';
					$user_type = ["0" => "Empleado", "1" => "Usuario del sistema"];
				@endphp
				@component("components.inputs.input-text", ["classEx" => "input-text-search", "attributeEx" => "type=\"text\" name=\"name\" value=\"".htmlentities($name)."\" id=\"input-search\" placeholder=\"Ingrese el nombre\""]) @endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Correo electrónico: @endcomponent
				@component("components.inputs.input-text", ["classEx" => "input-text-search", "attributeEx" => "type=\"text\" name=\"email\" value=\"".$email."\" id=\"input-search\" placeholder=\"Ingrese el correo electrónico\""]) @endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Tipo de usuario: @endcomponent
				@php
					$options = collect();
					foreach($user_type as $key => $value)
					{
						if(isset($type) && $type == $key)
						{
							$options = $options->concat([["value" => $key, "description" => $value, "selected" => "selected"]]);
						}
						else
						{
							$options = $options->concat([["value" => $key, "description" => $value]]);
						}
					}
				@endphp
				@component("components.inputs.select", ["options" => $options, "attributeEx" => "name=\"type\" title=\"Tipo de usuario\"", "classEx" => "js-types"]) @endcomponent
			</div>
			<div class="col-span-2 md:col-span-4 space-x-2 text-center md:text-left flex">
				@component("components.buttons.button-search", ["attributeEx" => $attributeExButtonSearch??'', "classEx" => $classExButtonSearch??'']) @endcomponent
				@component("components.buttons.button", ["buttonElement" => "a", "variant" => "reset", "classEx" => "bg-gray-200 px-7 py-2 rounded cursor-pointer hover:bg-gray-200 uppercase font-bold text-sm h-9 text-blue-gray-700", "attributeEx" => "href=\"".strtok($_SERVER['REQUEST_URI'], '?')."\""])Borrar campos @endcomponent
			</div>
		@endcomponent
	@endcomponent
	
	@if(count($users) > 0)
		@php
			$modelHead = [[["value" => "ID", "show" => "true"], ["value" => "Nombre", "show" => "true"], ["value" => "Correo Electrónico"], ["value" => "Estado"], ["value" => "Acción"]]];
			$modelBody = [];
			foreach($users as $user)
			{
				if($user->status=="ACTIVE" || $user->status=="NO-BOLETIN") 
				{
					$status = "Activo";
				}
				else if($user->status=="RE-ENTRY" ||$user->status=="RE-ENTRY-NO-MAIL") 
				{
					$status = "Reingreso";
				}
				else if($user->status=="SUSPENDED") 
				{
					$status = "Suspendido";
				}
				else
				{
					$status = "Baja";
				}
				
				$body = 
				[
					[
						"content" =>
						[
							["kind" => "components.labels.label", "label" => $user->id]
						]
					],
					[
						"content" =>
						[
							["kind" => "components.labels.label", "label" => htmlentities($user->fullName())]
						]
					],
					[
						"content" =>
						[
							["kind" => "components.labels.label", "label" => $user->email]
						]
					],
					[
						"content" =>
						[
							["kind" => "components.labels.label", "label" => $status]
						]
					]
				];
				switch($user->status)
				{
					case('ACTIVE'):
					case('NO-BOLETIN'):
					case("RE-ENTRY"):
					case("RE-ENTRY-NO-MAIL"):
						$buttons = 
						[
							["kind" => "components.buttons.button","variant" => "success", "buttonElement" => "a", "attributeEx" => "href=\"".route('user.edit',$user->id)."\" alt=\"Editar\" title=\"Editar\"", "label" => "<span class=\"icon-pencil\"></span>"],
							["kind" => "components.buttons.button","variant" => "red", "buttonElement" => "a", "attributeEx" => "href=\"".route('user.delete',$user->id)."\" class=\"btn-destroy-user btn btn-red\" alt=\"Baja\" title=\"Baja\"", "label" => "<span class=\"icon-blocked\"></span>"],
							["kind" => "components.buttons.button","variant" => "red", "buttonElement" => "a", "attributeEx" => "href=\"".route('user.suspend',$user->id)."\" class=\"btn-suspend-user btn btn-red\" alt=\"Suspender\" title=\"Suspender\"", "label" => "<span class=\"icon-user-minus\"></span>"],
						];
						break;
					case("SUSPENDED"):
						$buttons = 
						[
							["kind" => "components.buttons.button","variant" => "success", "buttonElement" => "a", "attributeEx" => "href=\"".route('user.edit',$user->id)."\" alt=\"Editar\" title=\"Editar\"", "label" => "<span class=\"icon-pencil\"></span>"],
							["kind" => "components.buttons.button","variant" => "red", "buttonElement" => "a", "attributeEx" => "href=\"".route('user.delete',$user->id)."\" class=\"btn-destroy-user btn btn-red\" alt=\"Baja\" title=\"Baja\"", "label" => "<span class=\"icon-blocked\"></span>"],
							["kind" => "components.buttons.button","variant" => "red", "buttonElement" => "a", "attributeEx" => "href=\"".route('user.reentry',$user->id)."\" class=\"btn-reentry-user btn btn-red\" alt=\"Reingresar\" title=\"Reingresar\"", "label" => "<span class=\"icon-user-check\"></span>"],
						];
						break;
					case("DELETED"):
						$buttons = 
						[
							["kind" => "components.buttons.button","variant" => "secondary", "buttonElement" => "a", "attributeEx" => "href=\"".route('user.show',$user->id)."\" alt=\"Ver Usuario\" title=\"Ver Usuario\"", "label" => "<span class=\"icon-search\"></span>"],
						];
						break;
				}
				array_push($body, ["content" => $buttons]);
				$modelBody[] = $body;
			}
		@endphp
		@Table(["modelHead" => $modelHead, "modelBody" => $modelBody]) @endTable
		
		{{ $users->appends(['name'=> $name,'email'=> $email,'type' => $type])->render() }}
	@else
		@component("components.labels.not-found") @endcomponent
	@endif
@endsection

@section('scripts')
	<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
	<script src="{{ asset('js/jquery-ui.js') }}"></script>
	<script>
		$(document).ready(function()
		{
			$('.js-types').select2(
			{
				placeholder				: 'Seleccione el tipo de usuario',
				language				: "es",
				maximumSelectionLength	: 1
			})
			.on("change",function(e)
			{
				if($(this).val().length>1)
				{
					$(this).val($(this).val().slice(0,1)).trigger('change');
				}
			});
			$(document).on('click','.btn-destroy-user',function(e)
			{
				e.preventDefault();
				attr = $(this).attr('href');
				swal({
					title		: "",
					text		: "Confirme que desea dar de baja definitiva al usuario",
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
							text		: "Baja",
							value		: true,
							closeModal	: false
						}
					},
					dangerMode	: true,
				})
				.then((a) => {
					if (a)
					{
						window.location.href=attr;
					}
				});
			})
			.on('click','.btn-suspend-user',function(e)
			{
				e.preventDefault();
				attr = $(this).attr('href');
				swal({
					title		: "",
					text		: "Confirme que desea suspender el usuario",
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
							text		: "Suspender",
							value		: true,
							closeModal	: false
						}
					},
					dangerMode	: true,
				})
				.then((a) => {
					if (a)
					{
						window.location.href=attr;
					}
				});
			})
			.on('click','.btn-reentry-user',function(e)
			{
				e.preventDefault();
				attr = $(this).attr('href');
				swal({
					title		: "",
					text		: "Confirme que desea reingresar el usuario",
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
							text		: "Reingresar",
							value		: true,
							closeModal	: false
						}
					},
					dangerMode	: true,
				})
				.then((a) => {
					if (a)
					{
						window.location.href=attr;
					}
				});
			});
		}); 
	</script>
@endsection
