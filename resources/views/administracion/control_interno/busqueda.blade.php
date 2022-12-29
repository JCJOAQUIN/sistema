@extends('layouts.child_module')
@section('data')
	@component('components.labels.title-divisor') BUSCAR CONTROL INTERNO @endcomponent
	@component('components.forms.searchForm', ["variant" => "default", "attributeEx" => "id=\"formsearch\""])
		<div class="col-span-2">
			@component('components.labels.label') ID: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="id_search" value="{{$id_search}}" id="input-search" placeholder="Ingrese un ID"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') WBS: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="wbs_search" value="{{$wbs_search}}" id="input-search" placeholder="Ingrese el WBS"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Tipo de Costo: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="cost_type_search" value="{{$cost_type_search}}" id="input-search" placeholder="Ingrese el tipo de costo"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Proveedor: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="provider_search" value="{{$provider_search}}" id="input-search" placeholder="Ingrese el proveedor"
				@endslot	
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Control Interno: @endcomponent
			<div class="border border-gray-400 p-4">
				<div class="text-sm">
					@component('components.inputs.switch')
						@slot('attributeEx')
							type="checkbox" name="requisicion_search" value="true" id="id_requisicion_search" @if($requisicion_search) checked @endif
						@endslot
						REQUISICION
					@endcomponent
				</div>
				<div class="text-sm">
					@component('components.inputs.switch')
						@slot('attributeEx')
							type="checkbox" name="oc_search" value="true" id="id_oc_search" @if($oc_search) checked @endif
						@endslot
						ORDEN DE COMPRA
					@endcomponent
				</div>
				<div class="text-sm">
					@component('components.inputs.switch')
						@slot('attributeEx')
							type="checkbox" name="remesa_search" value="true" id="id_remesa_search" @if($remesa_search) checked @endif
						@endslot
						REMESA
					@endcomponent
				</div>
				<div class="text-sm">
					@component('components.inputs.switch')
						@slot('attributeEx')
							type="checkbox" name="banco_search" value=true id="id_banco_search" @if($banco_search) checked @endif
						@endslot
						BANCOS
					@endcomponent
				</div>
			</div>
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Documento de carga masiva: @endcomponent
			@php
				$optionDoc = [];
				foreach (App\ControlDoc::all() as $item)
				{
					if($doc_search==$item->id)
					{
						$optionDoc[] = ["value" => $item->id, "description" => $item->short_name, "selected" => "selected"];
					}
					else
					{
						$optionDoc[] = ["value" => $item->id, "description" => $item->short_name];
					}
				}
			@endphp
			@component('components.inputs.select', ["options" => $optionDoc])
				@slot('attributeEx')
					name="doc_search" multiple="multiple"
				@endslot
				@slot('classEx')
					js-docs removeselect
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Estado: @endcomponent
			<div class="flex row space-x-2">
				<div>
					@component('components.buttons.button-approval')
						@slot('attributeEx')
							type="radio" name="state_search" id="state_1" value=1 @if($state_search==true) checked @endif
						@endslot
						Activo
					@endcomponent
				</div>
				<div>
					@component('components.buttons.button-approval')
						@slot('attributeEx')
						type="radio" name="state_search" id="state_2" value=0 @if($state_search==false) checked @endif
						@endslot
						Inactivo
					@endcomponent
				</div>
			</div>
		</div>
		@slot("export")
			@if(count($requests) > 0)
				<div class="float-right mt-4">
					@component('components.buttons.button', [ "variant" => "success"])
						@slot('attributeEx')
							type="submit"  formaction="{{ route('internal_control.export') }}"
						@endslot
						@slot('classEx')
							export
						@endslot
							Exportar a Excel <span class='icon-file-excel'></span>
					@endcomponent
				</div>
			@endif
		@endslot
	@endcomponent
	@if(count($requests) > 0)
		@php
			$body		= [];
			$modelBody	= [];
			$modelGroup = [];
			$modelHead = [];
			if(!$requisicion_search && !$oc_search && !$remesa_search && !$banco_search)
			{
				$modelGroup = [[ "name" => "REQUISICION", "colNumber" => "8"]];
				$modelHead	= [
					[
						["value" => "id",],
						["value" => "Fecha Remesa",],
						["value" => "WBS"],
						["value" => "Frentes"],
						["value" => "Tipo de Costo"],
						["value" => "Descripcion de Costo"],
						["value" => "Fecha de Requisicion"],
						["value" => "Solicitante"],
					]
				];
			}
			else 
			{
				$first = true;
				if($requisicion_search)
				{
					$first = false;
					$modelGroup[0][] = ["name" => "REQUISICION", "colNumber" => "8"];

					$modelHead[0][] = ["value" => "id", "show" => "true"];
					$modelHead[0][] = ["value" => "Fecha Remesa", "show" => "true"];
					$modelHead[0][] = ["value" => "WBS"];
					$modelHead[0][] = ["value" => "Frentes"];
					$modelHead[0][] = ["value" => "Tipo de Costo"];
					$modelHead[0][] = ["value" => "Descripcion de Costo"];
					$modelHead[0][] = ["value" => "Fecha de Requisicion"];
					$modelHead[0][] = ["value" => "Solicitante"];
				}
				if($oc_search)
				{
					$modelGroup[0][] = ["name" => "ORDEN DE COMPRA", "colNumber" => "3"];
					if($first)
					{
						$modelHead[0][] = ["value" => "Fechas", "show" => "true"];
					}
					else
					{
						$modelHead[0][] = ["value" => "Fechas"];
					}
					$modelHead[0][] = [ "value" => "Número"];
					$modelHead[0][] = [ "value" => "Proveedor"];
				}
				if($remesa_search)
				{
					$modelGroup[0][] = ["name" => "REMESA", "colNumber" => "5"];
					if($first)
					{
						$modelHead[0][] = [ "value" => "Remesa", "show" => "true"];
					}
					else
					{
						$modelHead[0][] = [ "value" => "Remesa"];
					}
					$modelHead[0][] = [ "value" => "Fecha"];
					$modelHead[0][] = [ "value" => "Factura"];
					$modelHead[0][] = [ "value" => "Importe FACT"];
					$modelHead[0][] = [ "value" => "Total"];

				}
				if($banco_search)
				{
					$modelGroup[0][] = ["name" => "BANCOS", "colNumber" => "3"];
					if($first)
					{
						$modelHead[0][] = [ "value" => "Fecha", "show" => "true"];
					}
					else
					{
						$modelHead[0][] = [ "value" => "Fecha"];
					}
					$modelHead[0][] = [ "value" => "TRASF-CH"];
					$modelHead[0][] = [ "value" => "Importe"];
				}
			}
			$modelHead[0][] = [ "value" => "Acciones"];

			if(!$requisicion_search && !$oc_search && !$remesa_search && !$banco_search)
			{
				foreach($requests as $request)
				{
					$body =[
						[
							"content" =>
							[
								"label" => $request->id
							]
						],
						[
							"content" =>
							[
								"label" => $request->controlRequisition->data_remittances
							]
						],
						[
							"content" =>
							[
								"label" => $request->controlRequisition->WBS
							]
						],
						[
							"content" =>
							[
								"label" => $request->controlRequisition->frentes
							]
						],
						[
							"content" =>
							[
								"label" => $request->controlRequisition->cost_type
							]
						],
						[
							"content" =>
							[
								"label" => $request->controlRequisition->cost_description
							]
						],
						[
							"content" =>
							[
								"label" => $request->controlRequisition->data_requisition
							]
						],
						[
							"content" =>
							[
								"label" => $request->controlRequisition->applicant
							]
						]
					];
					if($request->state == true)
					{
						array_push($body, 
						[
							"content" =>
							[
								[
									"kind" 			=> "components.buttons.button",
									"variant" 		=> "success",
									"buttonElement"	=> "a",
									"attributeEx"	=> "alt=\"Editar Solicitud\" title=\"Editar Solicitud\" href=\"".route('internal_control.edit',$request->id)."\"",
									"label"			=> "<span class=\"icon-pencil\"></span>"
								],
								[
									"kind" 			=> "components.buttons.button",
									"variant" 		=> "red",
									"buttonElement"	=> "a",
									"attributeEx"	=> "title=\"Suspender\" href=\"".route('internal_control.delete',$request->id)."\"",
									"classEx"		=> "enterprise-delete",
									"label"			=> "<span class=\"icon-bin\"></span>"
								]
							]
						]);
					}
					elseif($request->state == false)
					{
						array_push($body, 
						[
							"content" =>
							[
								[
									"kind" 			=> "components.buttons.button",
									"variant" 		=> "success",
									"buttonElement"	=> "a",
									"attributeEx"	=> "alt=\"Editar Solicitud\" title=\"Editar Solicitud\" href=\"".route('internal_control.edit',$request->id)."\"",
									"label"			=> "<span class=\"icon-pencil\"></span>"
								],
								[
									"kind" 			=> "components.buttons.button",
									"variant" 		=> "secondary",
									"buttonElement"	=> "a",
									"attributeEx"	=> "title=\"Reactivar\" href=\"".route('internal_control.delete',$request->id)."\"",
									"classEx"		=> "enterprise-reactive",
									"label"			=> "<span class=\"icon-check\"></span>"
								]
							]
						]);
					}
					$modelBody[] = $body;
				}
			}
			else
			{
				foreach($requests as $request)
				{
					$first = true;
					$body = [];
					if($requisicion_search)
					{
						$first = false;
						array_push($body,   [ "show" => "true", "content" => [ "label" => $request->id]]);
						array_push($body,	[ "show" => "true", "content" => [ "label" => $request->controlRequisition->data_remittances]]);
						array_push($body,	[ "content" => [ "label" => $request->controlRequisition->WBS]]);
						array_push($body,	[ "content" => [ "label" => $request->controlRequisition->frentes]]);
						array_push($body,	[ "content" => [ "label" => $request->controlRequisition->cost_type]]);
						array_push($body,	[ "content" => [ "label" => $request->controlRequisition->cost_description]]);
						array_push($body,	[ "content" => [ "label" => $request->controlRequisition->data_requisition]]);
						array_push($body,	[ "content" => [ "label" => $request->controlRequisition->applicant]]);
					}
					if($oc_search)
					{
						if($first)
						{
							array_push($body, [ "show" => "true", "content" => [ "label" => $request->controlPurchaseOrder->data]]);
						}
						else
						{
							array_push($body, ["content" => [ "label" => $request->controlPurchaseOrder->data]]);
						}
						array_push($body, [ "content" => [ "label" => $request->controlPurchaseOrder->number]]);
						array_push($body, [ "content" => [ "label" => $request->controlPurchaseOrder->provider]]);
					}
					if($remesa_search)
					{
						if($first)
						{
							array_push($body, [ "show" => "true", "content" => [ "label" => $request->controlRemittance->remittances]]);
						}
						else
						{
							array_push($body, [ "content" => [ "label" => $request->controlRemittance->remittances]]);
						}
						array_push($body, [ "content" => [ "label" => $request->controlRemittance->data]]);
						array_push($body, [ "content" => [ "label" => $request->controlRemittance->invoice]]);
						array_push($body, [ "content" => [ "label" => $request->controlRemittance->invoice_amount]]);
						array_push($body, [ "content" => [ "label" => $request->controlRemittance->total]]);
					}
					if($banco_search)
					{
						if($first)
						{
							array_push($body, [ "show" => "true", "content" => [ "label" => $request->controlBank->data]]);
						
						}
						else
						{
							array_push($body, ["content" => [ "label" => $request->controlBank->data]]);
						}
						array_push($body, [ "content" => [ "label" => $request->controlBank->TRASF_CH]]);
						array_push($body, [ "content" => [ "label" => $request->controlBank->amount]]);
					}
					if($request->state == true)
					{
						array_push($body, 
						[
							"content" =>
							[
								[
									"kind" 			=> "components.buttons.button",
									"variant" 		=> "success",
									"buttonElement"	=> "a",
									"attributeEx"	=> "alt=\"Editar Solicitud\" title=\"Editar Solicitud\" href=\"".route('internal_control.edit',$request->id)."\"",
									"label"			=> "<span class=\"icon-pencil\"></span>"
								],
								[
									"kind" 			=> "components.buttons.button",
									"variant" 		=> "red",
									"buttonElement"	=> "a",
									"attributeEx"	=> "title=\"Suspender\" href=\"".route('internal_control.delete',$request->id)."\"",
									"classEx"		=> "enterprise-delete",
									"label"			=> "<span class=\"icon-bin\"></span>"
								]
							]
						]);
					}
					elseif($request->state == false)
					{
						array_push($body, 
						[
							"content" =>
							[
								[
									"kind" 			=> "components.buttons.button",
									"variant" 		=> "success",
									"buttonElement"	=> "a",
									"attributeEx"	=> "alt=\"Editar Solicitud\" title=\"Editar Solicitud\" href=\"".route('internal_control.edit',$request->id)."\"",
									"label"			=> "<span class=\"icon-pencil\"></span>"
								],
								[
									"kind" 			=> "components.buttons.button",
									"variant" 		=> "secondary",
									"buttonElement"	=> "a",
									"attributeEx"	=> "title=\"Reactivar\" href=\"".route('internal_control.delete',$request->id)."\"",
									"classEx"		=> "enterprise-reactive",
									"label"			=> "<span class=\"icon-check\"></span>"
								]
							]
						]);
					}
					$modelBody[] = $body;
				}
			}
		@endphp
		@component('components.tables.table', [
			"modelGroup"=> $modelGroup,
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
	<script src="{{ asset('js/jquery.numeric.js') }}"></script>
	<script type="text/javascript"> 
		$(document).ready(function()
		{
			@php
				$selects = collect([
					[
						"identificator"				=> ".js-docs",
						"placeholder"				=> "Seleccione el nombre del solicitante",
						"maximumSelectionLength"	=> "1"
					]
				]);
			@endphp
			@component('components.scripts.selects',["selects" => $selects]) @endcomponent
			$(function() 
			{
				$( ".datepicker" ).datepicker({ maxDate: 0, dateFormat: "dd-mm-yy" });
			});

			$(document).on('change','.js-enterprise',function()
			{
				$('.js-account').empty();
				$enterprise = $(this).val();
				$.ajax(
				{
					type 	: 'get',
					url 	: '{{ url("/administration/stationery/create/account") }}',
					data 	: {'enterpriseid':$enterprise},
					success : function(data)
					{
						$.each(data,function(i, d) {
							$('.js-account').append('<option value='+d.idAccAcc+'>'+d.account+' - '+d.description+' ('+d.content+')</option>');
						});
					},
					error : function()
					{
						swal('','Sucedió un error, por favor intente de nuevo.','error');
						$('.js-account').val(null).trigger('change');
					}
				});
			});
		});
	</script>
@endsection
