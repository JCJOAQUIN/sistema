@extends("layouts.child_module")
@section("data")
	<div class="sm:text-center text-left my-5">
		A continuación podrá verificar la información de la solicitud antes de continuar con el proceso:
	</div>
	@php
		$modelTable = 
		[
			["Folio:", $request->folio],
			["Título y fecha:", htmlentities($request->computer->first()->title)." - ".Carbon\Carbon::createFromFormat('Y-m-d', $request->computer->first()->datetitle)->format('d-m-Y')],
			["Solicitante:", $request->requestUser->fullName()],
			["Elaborado por:", $request->elaborateUser->fullName()],
			["Empresa:", $request->requestEnterprise->name],
			["Dirección:", $request->requestDirection->name],
			["Departamento:", $request->requestDepartment->name],
			["Proyecto:", $request->requestProject->proyectName],
		];
		if($request->account)
		{
			$modelTable [] = ["Clasificación de gastos:", $request->accounts->account." - ".$request->accounts->description." (".$request->accounts->content.")"];
		}
		$modelTable [] = ["Puesto:", $request->computer->first()->position];
		if($request->computer->first()->entry==0)
		{
			$modelTable [] = ["Nuevo ingreso:", "No"];
		}
		else 
		{
			$modelTable [] = ["Nuevo ingreso:", "Si"];
			$modelTable [] = ["Fecha de ingreso:", Carbon\Carbon::createFromFormat('Y-m-d', $request->computer->first()->entry_date)->format('d-m-Y')];
		}
	@endphp
	@component("components.templates.outputs.table-detail", ["modelTable" => $modelTable, "title" => "Detalles de la Solicitud", "classEx" => "mb-6"])@endcomponent
	@component("components.labels.title-divisor") ASIGNACIÓN DE EQUIPO @endcomponent
	<div class="content-start items-start flex flex-wrap justify-center py-5 w-full mb-6">
		@switch($request->computer->first()->device)
			@case(1)
				@component("components.buttons.button-device",["variant" => "display", "label" => "Smartphone", "icon" => "<span class='icon-phone text-6xl text-white'></span>"])	@endcomponent
				@break
			@case(2)
				@component("components.buttons.button-device",["variant" => "display", "label" => "Tablet", "icon" => "<span class='icon-tablet text-6xl text-white'></span>"])	@endcomponent
				@break
			@case(3)
				@component("components.buttons.button-device",["variant" => "display", "label" => "Laptop", "icon" => "<span class='icon-laptop text-6xl text-white'></span>"])	@endcomponent
				@break
			@case(4)
				@component("components.buttons.button-device",["variant" => "display", "label" => "Computadora", "icon" => "<span class='icon-pc text-6xl text-white'></span>"])	@endcomponent
				@break
		@endswitch
	</div>
	@component("components.labels.title-divisor")    ALTA / CONFIGURACIÓN DE CUENTA @endcomponent
	<div class="block overflow-auto w-full text-center mb-6">
		@php
			$heads = ["Cuentas","Alias"];
			$modelTable = [];
			foreach($request->computer->first()->computerAccounts as $account)
			{
				$modelTable[] = [
					[
						"content" =>
						[
							[
								"kind" => "components.labels.label",
								"label" => htmlentities($account->email_account),
							],
							[
								"kind" => "components.inputs.input-text", 
								"classEx" => "idcomputerEmailsAccounts", 
								"attributeEx" => "type=\"hidden\" value=\"".$account->idcomputerEmailsAccounts."\"",
								"value" => $account->idcomputerEmailsAccounts,
							],
						],
					],						
					[
						"content" =>
						[
							[
								"kind" => "components.labels.label",
								"label" => htmlentities($account->alias_account),
							],
						],
					],			
				];
			}
		@endphp
		@component("components.tables.alwaysVisibleTable",[
			"modelHead" => $heads,
			"modelBody" => $modelTable,
			"variant" => "default",
		])
		@endcomponent
	</div>
	@component("components.labels.title-divisor")    LICENCIA Y APLICACIONES @endcomponent
	<div class="grid grid-cols-4 justify-items-center">
		<div class="md:col-start-2 col-span-4 md:col-span-2 text-left mb-6 font-bold">
			@if(isset($request->computer->first()->device))
				@foreach($request->computer->first()->software as $softwareU)
					@component("components.labels.label")
						{{ $softwareU->name }}
					@endcomponent
				@endforeach
				@if($request->computer->first()->other_software!="")
					@component("components.labels.label")
						{{ $request->computer->first()->other_software }}
					@endcomponent
				@endif
			@endif
		</div>
	</div>
	@component("components.labels.title-divisor") DATOS DE REVISIÓN @endcomponent
	<div class="block overflow-auto w-full text-left">
		
			@php
				$modelTable = [ "Revisó" => $request->reviewedUser->fullName() ];
				if($request->checkComment != "")
				{
					$modelTable["Comentarios"] =  htmlentities($request->checkComment);
				}
				else 
				{
					$modelTable["Comentarios"] =  "Sin Comentarios";
				}
			@endphp
			@component("components.templates.outputs.table-detail-single", ["modelTable" => $modelTable]) 
			@endcomponent
		
	</div>
	@component("components.forms.form",["attributeEx" => "action=\"".route('computer.authorization.update',$request->folio)."\" method=\"POST\" id=\"container-alta\"", "methodEx" => "PUT"])
		@component('components.containers.container-approval')
			@slot('attributeExLabel')
				id="label-inline"
			@endslot
			@slot('attributeExButton')
				name="status"
				value="5"
				id="aprobar"
				data-validation-skipped="1"
			@endslot
			@slot('attributeExButtonTwo')
				name="status"
				value="7"
				id="rechazar"
				data-validation-skipped="1"
			@endslot
		@endcomponent
		<div id="comment" hidden>
			<div class="block overflow-auto w-full ">
				@component("components.labels.label")
					@slot("classEx")
						mt-2
						text-center
					@endslot
					Comentarios (Opcional):
				@endcomponent
				@component("components.inputs.text-area")
					@slot("classEx")
						text-area
					@endslot
					@slot("attributeEx")
						cols="90"
						rows="10"
						name="authorizeComment"
						placeholder="Ingrese un comentario"
					@endslot
				@endcomponent
			</div>
		</div>
		<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mt-8">
			@component("components.buttons.button", ["variant"=>"primary"])
				@slot("classEx")
					w-48
					md:w-auto
					text-center
				@endslot
				@slot("attributeEx")
					type="submit"
				@endslot
				ENVIAR SOLICITUD
			@endcomponent
			@component("components.buttons.button", ["variant"=>"reset", "buttonElement"=>"a"])
				@slot("classEx")
					load-actioner
					w-48
					md:w-auto
					text-center
				@endslot
				@slot("attributeEx")
					type="button"
					@if(isset($option_id)) 
						href="{{ url(getUrlRedirect($option_id)) }}" 
					@else 
						href="{{ url(getUrlRedirect($child_id)) }}" 
					@endif
				@endslot
				REGRESAR
			@endcomponent
		</div>
	@endcomponent
@endsection
@section("scripts")
	<link rel="stylesheet" href="{{ asset("css/jquery-ui.css") }}">
	<script src="{{ asset("js/jquery-ui.js") }}"></script>
	<script src="{{ asset("js/datepicker.js") }}"></script>
	<script>
		$(document).ready(function()
		{
			$.validate(
			{	
				form: "#container-alta",
				onSuccess : function($form)
				{
					if($("input[name='status']").is(":checked"))
					{
						swal("Cargando",{
							icon: '{{ asset(getenv('LOADING_IMG')) }}',
							button: false,
							closeOnClickOutside: false,
							closeOnEsc: false
						});
						return true;
					}
					else
					{
						swal("", "Por favor seleccione una opción.", "error");
						return false;
					}
				}
			});
			$(document).on("change","input[name='status']",function()
			{
				$("#comment").slideDown("slow");
			});
		});
	</script>
@endsection
