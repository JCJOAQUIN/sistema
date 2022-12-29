@extends('layouts.child_module')

@section('data')
	@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "BUSCAR"]) @endcomponent
	@php
		$values =
		[
			'enterprise_option_id' => $option_id,
			'enterprise_id'        => $enterpriseid,
			'minDate'               => $mindate,
			'maxDate'               => $maxdate,
		];
		$hidden =   ['folio','name'];
	@endphp
	@component('components.forms.searchForm', ["attributeEx" => "method=\"get\" action=\"".route('warehouse.remove')."\"", "values" => $values, "hidden" => $hidden])
		@slot('contentEx')
			<div class="col-span-2">
				@component('components.labels.label', ["label" => "Categoría:"]) @endcomponent
				@php
					$options	=	collect();
					if (isset($category) && $category!="")
					{
						$categoryData   =   App\CatWarehouseType::find($category);
						$options        =   $options->concat([["value"  =>  $categoryData->id,   "description"   =>  $categoryData->description,   "selected"  =>  "selected"]]);
					}
				@endphp
				@component('components.inputs.select', ["options" => $options,"classEx" => "js-cat", "attributeEx" => "title=\"Categoría\" name=\"cat\" multiple=\"multiple\""]) @endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label', ["label" => "Ubicación/Sede"]) @endcomponent
				@php
					$options	=	collect();
					foreach (App\Place::where('status',1)->get() as $place)
					{
						if (isset($place_id) && $place_id == $place->id)
						{
							$options    =   $options->concat([["value"  =>  $place->id,  "description"    =>  $place->place, "selected"   =>  "selected"]]);
						}
						else
						{
							$options    =   $options->concat([["value"  =>  $place->id,  "description"    =>  $place->place]]);
						}
					}
				@endphp
				@component('components.inputs.select', ["options" => $options,"classEx" => "js-places removeselect", "attributeEx" => "name=\"place_id\" multiple=\"multiple\""]) @endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label', ["label" => "Cuenta"]) @endcomponent
				@php
					$options	=	collect();
					if ((isset($account_id) && $account_id!="") && (isset($enterpriseid) && $enterpriseid!=""))
					{
						$accountData	=	App\Account::find($account_id);
						$options	=	$options->concat([["value"	=>	$accountData->idAccAcc,	"description"	=>	$accountData->account." - ".$accountData->description." (".$accountData->content.")",	"selected"	=>	"selected"]]);
					}
				@endphp
				@component('components.inputs.select', ["options" => $options,"classEx" => "js-accounts removeselect", "attributeEx" => "name=\"account_id\" multiple=\"multiple\" id=\"multiple-accounts select2-selection--multiple\""]) @endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label', ["label" => "Concepto"]) @endcomponent
				@component('components.inputs.input-text', ["classEx" => "input-all", "attributeEx" => "name=\"concept\" placeholder=\"Ingrese el concepto\" value=\"".(isset($concept) && $concept!="" ? htmlentities($concept) : "")."\""]) @endcomponent
			</div>
		@endslot
	@endcomponent
	@if (count($warehouses) > 0)
		@php
			$modelHead	=	[];
			$body		=	[];
			$modelBody	=	[];
			$modelHead	=
			[
				[
					["value"	=>	"Cantidad"],
					["value"	=>	"Concepto"],
					["value"	=>	"Ubicación"],
					["value"	=>	"Acción"]
				]
			];
			foreach($warehouses as $warehouse)
			{
				$body	=
				[
					[
						"content"	=>	["label"	=>	$warehouse->quantity!="" ? $warehouse->quantity : "---"]
					],
					[
						"content"	=>	["label"	=>	$warehouse->cat_C->description!="" ? htmlentities($warehouse->cat_C->description) : "---"]
					],
					[
						"content"	=>	["label"	=>	$warehouse->place_location != null ? $warehouse->location->place : "---"]
					],
					[
						"content"	=>
						[
							[
								"kind"			=>	"components.buttons.button",
								"attributeEx"	=>	"type=\"button\"",
								"classEx"		=>	"follow-btn modalDetails",
								"label"			=>	"<span class='icon-bin'></span>",
								"variant"		=>	"red"
							],
							["kind" =>  "components.inputs.input-text", "attributeEx"   =>  "type=\"hidden\" value=\"".$warehouse->concept."\"",    "classEx"   =>  "conceptWarehouse"],
							["kind" =>  "components.inputs.input-text", "attributeEx"   =>  "type=\"hidden\" value=\"".$enterpriseid."\"",          "classEx"   =>  "idEnterprise"],
							["kind" =>  "components.inputs.input-text", "attributeEx"   =>  "type=\"hidden\" value=\"".$place_id."\"",              "classEx"   =>  "place_id"],
							["kind" =>  "components.inputs.input-text", "attributeEx"   =>  "type=\"hidden\" value=\"".$account_id."\"",            "classEx"   =>  "account_id"],
							["kind" =>  "components.inputs.input-text", "attributeEx"   =>  "type=\"hidden\" value=\"".$mindate."\"",               "classEx"   =>  "mindate"],
							["kind" =>  "components.inputs.input-text", "attributeEx"   =>  "type=\"hidden\" value=\"".$maxdate."\"",               "classEx"   =>  "maxdate"]
						]
					],
				];
				$modelBody[]	=	$body;
			}
		@endphp
		@component('components.tables.table', ["modelHead" => $modelHead, "modelBody" => $modelBody, "classEx" => "mt-4"])@endcomponent
		{{$warehouses->appends($_GET)->links()}}
	@else
		@component('components.labels.not-found') @endcomponent
	@endif
	@component('components.forms.form', ["attributeEx" => "method=\"POST\" id=\"form-delete\" action=\"".route('warehouse.remove.delete')."\""])
		@component("components.modals.modal", ["attributeEx" => "id=\"myModal\"", "modalTitle" => "BAJA DE ARTÍCULOS DEL INVENTARIO"]) @endcomponent
	@endcomponent
@endsection

@section('scripts')
	<script src="{{ asset('js/jquery.numeric.js') }}"></script>
	<script type="text/javascript">
		current_selection = 1;
		$(document).ready(function()
		{
			generalSelect({'selector':'.js-cat','model':56});
			warehouseType	= $('.js-cat option:selected').val();
			generalSelect({'selector':'.js-accounts', 'depends':'.js-enterprise', 'model':57, 'warehouseType':warehouseType});
			@php
				$selects = collect([
					[
						"identificator"          => ".js-enterprise", 
						"placeholder"            => "Seleccione la empresa", 
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => ".js-places", 
						"placeholder"            => "Seleccione la ubicación/sede", 
						"maximumSelectionLength" => "1"
					],
				]);
			@endphp
			@component("components.scripts.selects",["selects" => $selects])@endcomponent
			$(function() 
			{
				enterprise = $('.js-enterprise option:selected').val();
				warehouseType = $('.js-cat option:selected').val();
				generalSelect({'selector':'.js-accounts', 'depends':'.js-enterprise', 'model':57, 'warehouseType':warehouseType});
			});
		});
		$(document).on('change','.js-enterprises,.js-cat',function()
		{
			warehouseType	= $('.js-cat option:selected').val();
			$('.js-accounts').html('');
			generalSelect({'selector':'.js-accounts', 'depends':'.js-enterprise', 'model':57, 'warehouseType':warehouseType});
		})
		.on('click', '.modalDetails', function()
		{
			$.ajax(
			{
				type 	: 'post',
				url 	: '{{ route("warehouse.remove.detail") }}',
				data 	: {
					'warehouseConcept': $(this).siblings('.conceptWarehouse').val(),
					'idEnterprise'    : $(this).siblings('.idEnterprise').val(),
					'place_id'        : $(this).siblings('.place_id').val(),
					'account_id'      : $(this).siblings('.account_id').val(),
					'mindate'         : $(this).siblings('.mindate').val(),
					'maxdate'         : $(this).siblings('.maxdate').val()
				},
				success : function(data)
				{
					$('#myModal').modal('show');
					$('.modal-body').html(data);
					$(".quantityRemove").numeric({negative : false, decimal: false});
					validation();
				},
				error : function()
				{
					swal('','Sucedió un error, por favor intente de nuevo.','error');
					$('#myModal').modal('hide');
				}
			})
		})
		.on('click','.set-warehouse',function(e)
		{
			e.preventDefault();
			$('.quantityRemove').each( function()
			{
				quantityRemove  = Number($('.quantityRemove').val());
				removeCant  = Number($(this).parents('.tr-remove').find('.quantity').val());
				if(quantityRemove > removeCant)
				{
					swal('Error', 'No puede dar de baja más artículos de los disponibles por lote.', 'error');
					return false;
				}
				else if(quantityRemove == 0)
				{
					swal('Error', 'No puede dar de baja artículos en cantidades iguales a cero.', 'error');
					return false;
				}
				else
				{
					action = $(this).attr('formaction');
					form = $('form#form-delete').attr('action',action);
					form.submit();
				}
			});
		})
		.on('change','.quantityRemove',function()
		{
			if(Number($(this).val()).toFixed(2) == "NaN")
			{
				$(this).val(0).trigger('change');
			}
		});
		function validation()
		{
			$.validate(
			{
				form    : '#form-delete',
				modules	: 'security',
				onError : function($form)
				{
					swal('', '{{ Lang::get("messages.form_error") }}', 'error');
					return false;
				}
			});
		}
	</script>
@endsection