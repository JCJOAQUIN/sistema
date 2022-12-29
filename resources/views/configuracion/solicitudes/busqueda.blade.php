@extends('layouts.child_module')

@section('data')
	@component('components.labels.title-divisor') BUSCAR SOLICITUDES @endcomponent
	@php
		$values = ["minDate" => $mindate, "maxDate" => $maxdate];
		$hidden = ["folio","name","enterprise"];
	@endphp
	@component('components.forms.searchForm',
		[
			"attributeEx" 	=> "id=\"formsearch\"",
			"values"		=> $values,
			"hidden"		=> $hidden
		])
		@slot('contentEx')
			<div class="col-span-2">
				@component('components.labels.label') Solicitante: @endcomponent
				@php
					$optionUserid	= [];
					$user			= App\User::where('status','ACTIVE')->where('sys_user',1)->where('id',$userid)->first();
					if(isset($userid) && in_array($user->id, $userid))
					{
						$optionUserid[] = ["value" => $user->id, "description" => $user->name.' '.$user->last_name.' '.$user->scnd_last_name, "selected" => "selected"];
					}
				@endphp
				@component('components.inputs.select',["options" => $optionUserid])
					@slot('attributeEx')
						name="userid[]" multiple="multiple"
					@endslot
					@slot('classEx')
						js-users
					@endslot
				@endcomponent
			</div>
			<div clasS="col-span-2">
				@component('components.labels.label') Empresa: @endcomponent
				@php
					$optionEnteprise = [];
					foreach(App\Enterprise::orderBy('name','asc')->get() as $enterprise)
					{
						$optionEnteprise[] = [
							"value"			=> $enterprise->id,
							"description"	=> strlen($enterprise->name) >= 35 ? substr(strip_tags($enterprise->name),0,35).'...' : $enterprise->name,
							"selected"		=> (isset($enterpriseid) && in_array($enterprise->id, $enterpriseid) ? "selected" : "")
						];
					}
				@endphp
				@component('components.inputs.select',["options" => $optionEnteprise])
					@slot('attributeEx')
						title="Empresa" name="enterpriseid[]" multiple="multiple"
					@endslot
					@slot('classEx')
						js-enterprise
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Tipo de solicitud: @endcomponent
				@php
					$optionKind = [];
					foreach (App\RequestKind::whereIn('idrequestkind',[1,8])->orderBy('kind','asc')->get() as $t)
					{
						$optionKind[] = [
							"value"			=> $t->idrequestkind,
							"description"	=> $t->kind,
							"selected"		=> (isset($kind) && in_array($t->idrequestkind, $kind) ? "selected" : "")
						];
					}
				@endphp
				@component('components.inputs.select',["options" => $optionKind])
					@slot('attributeEx')
						title="Tipo de Solicitud" name="kind[]" multiple="multiple"
					@endslot
					@slot('classEx')
						js-type
					@endslot
				@endcomponent
			</div>
		@endslot
	@endcomponent
	@if(count($requests) > 0)
		@php
			$body		= [];
			$modelBody	= [];
			$modelHead	= 
			[
				[
					["value" => "ID"],
					["value" => "Tipo de Solicitud"],
					["value" => "Título"],
					["value" => "Periodo"],
					["value" => "Empresa"],
					["value" => "Solicitante"],
					["value" => "Estado"],
					["value" => "Fecha de Creación"],
					["value" => "Acciones"]
				]
			];
			foreach($requests as $r)
			{
				$titleRequest = "";
				switch($r->kind)
				{
					case 1:
						$titleRequest = $r->purchase()->exists() ? $r->purchase->title : 'Sin Título';
						break;
					case 8:
						$titleRequest = $r->resource()->exists() ? $r->resource->title : 'Sin Título';
						break;
					default;
						$titleRequest = "Sin título";
						break;
				}
				$periodicity = "";
				switch($r->periodicity)
				{
					case('monthlyOn'):
						$periodicity = "Mensual";
						break;
					case('twiceMonthly'):
						$periodicity = "Quincenal";
						break;
					case('yearly'):
						$periodicity = "Anual";
						break;
					case('weeklyOn'):
						$periodicity = "Semanal";
						break;
					default;
						break;
				}

				$body =
				[
					[
						"content" =>
						[
							"label" => $r->id
						]
					],
					[
						"content" =>
						[
							"label" => $r->dataKind->kind
						]
					],
					[
						"content" =>
						[
							"label" => htmlentities($titleRequest),
						]
					],
					[
						"content" =>
						[
							"label" => $periodicity
						]
					],
					[
						"content" =>
						[
							"label" => $r->enterprise->name
						]
					],
					[
						"content" =>
						[
							"label" => $r->requestUser->name.' '.$r->requestUser->last_name.' '.$r->requestUser->scnd_last_name
						]
					],
					[
						"content" =>
						[
							"label" => $r->status == 1 ? 'Activo' : 'Inactivo'
						]
					],
					[
						"content" =>
						[
							"label" => ($r->created_at != null ? $r->created_at->format('d-m-Y H:i:s') : "--")
						]
					],
					[
						"content" =>
						[
							[
								"kind"			=> "components.buttons.button",
								"variant"		=> "success",
								"buttonElement" => "a",
								"attributeEx"	=> "title=\"Editar\" href=\"".route('requests.show',$r->id)."\"",
								"label"			=> "<span class=\"icon-pencil\"></span>"
							],
							[
								"kind"			=> "components.buttons.button",
								"buttonElement" => "a",
								"variant"		=> $r->status == 1 ? "red" : "success",
								"label"			=> $r->status == 1 ? "<span class=\"icon-x\"></span>" : "<span class=\"icon-check\"></span>",
								"attributeEx"	=> $r->status == 1 ? "title=\"Deshabilitar\" href=\"".route('requests.inactive',$r->id)."\"" : "title=\"Habilitar\" href=\"".route('requests.active',$r->id)."\""
							]
						]
					]
				];
				$modelBody[] = $body;
			}
		@endphp
		@component('components.tables.table',[
			"modelBody" => $modelBody,
			"modelHead" => $modelHead
		])	
		@endcomponent
		{{ $requests->appends($_GET)->links() }}
	@else
		@component('components.labels.not-found') @endcomponent
	@endif
@endsection

@section('scripts')
	<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
	<script src="{{ asset('js/jquery-ui.js') }}"></script>
	<script src="{{ asset('js/datepicker.js') }}"></script>
	<script type="text/javascript"> 
		$(document).ready(function()
		{
			generalSelect({'selector':'.js-users','model':13,'maxSelection' : 15});
			@ScriptSelect([ "selects" =>
				[
					[
						"identificator"          => ".js-enterprise", 
						"placeholder"            => "Seleccione la empresa", 
						"language"				 => "es"
					],
					[
						"identificator"          => ".js-type", 
						"placeholder"            => "Seleccione el tipo de solicitud", 
						"language"				 => "es"
					]
				]
			])
			@endScriptSelect
			$(function() 
			{
				$( ".datepicker" ).datepicker({ maxDate: 0, dateFormat: "dd-mm-yy" });
			});
		});
	</script>
@endsection