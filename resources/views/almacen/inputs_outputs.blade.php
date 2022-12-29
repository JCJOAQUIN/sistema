@extends('layouts.child_module')
@section('data')
	{{--@component("components.forms.form", ["attributeEx" => "action=\"".route('warehouse.report.inputsOutputs')."\" id=\"formsearch\""])--}}
	@component("components.labels.title-divisor") BUSQUEDA @endcomponent
	@SearchForm(["variant" => "default"])
		<div class="col-span-2">
			@php
				$options = collect();
				if(isset($category))
				{
					$category	= App\CatWarehouseType::find($category);
					$options	= $options->concat(
					[
						[
							"value"			=> $category->id, 
							"description"	=> $category->description, 
							"selected"		=> "selected"
						]
					]);
				}
			@endphp
			@component("components.labels.label") Categoría: @endcomponent
			@component("components.inputs.select", ["options" => $options])
				@slot("classEx") js-cat @endslot
				@slot("attributeEx") title="Categoría" name="category" multiple="multiple" @endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@php
				$options = collect();
				if(isset($place_id))
				{
					$place 		= App\Place::find($place_id);
					$options 	= $options->concat(
					[
						[
							"value"			=> $place->id, 
							"description"	=> $place->place, 
							"selected"		=> "selected"
						]
					]);
				}
			@endphp
			@component("components.labels.label") Ubicación/Sede: @endcomponent
			@component("components.inputs.select", ["options" => $options])
				@slot("classEx") js-places removeselect @endslot
				@slot("attributeEx") name="place_id" multiple="multiple" @endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@php
				$options = collect();
				foreach(App\Enterprise::orderName()->get() as $enterprise)
				{
					$options 	= $options->concat([["value" => "TODAS", "description" => "TODAS"]]);
					$options 	= $options->concat(
					[
						[
							"value"			=> $enterprise->id, 
							"description"	=> strlen($enterprise->name) >= 35 ? substr(strip_tags($enterprise->name),0,35).'...' : $enterprise->name, 
							"selected"		=> ((isset($idEnterprise) && $idEnterprise == $enterprise->id) ? "selected" : "")
						]
					]);
				}
			@endphp
			@component("components.labels.label") Empresa: @endcomponent
			@component("components.inputs.select", ["options" => $options])
				@slot("classEx") js-enterprises @endslot
				@slot("attributeEx") title="Empresa" name="idEnterprise" multiple="multiple" @endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@php
				$options = collect();
				if(isset($account_id))
				{
					$account = App\Account::where('idAccAcc',$account_id)->first();
					$options = $options->concat(
					[
						[
							"value"				=> $account->idAccAcc, 
							"description"		=> $account->idAccAcc. ' - ' .$account->description . ' ('.$account->content.')', 
							"selected"			=> "selected",
							"attributeExOption"	=> "id=\"current_account_id\""
						]
					]);
				}
			@endphp
			@component("components.labels.label") Cuenta: @endcomponent
			@component("components.inputs.select", ["options" => $options])
				@slot("classEx") js-accounts removeselect @endslot
				@slot("attributeEx")  name="account_id" multiple="multiple" @endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component("components.labels.label") Concepto: @endcomponent
			@component("components.inputs.input-text")
				@slot("classEx") input-all @endslot
				@slot("attributeEx") placeholder="Ingrese el concepto" name="concept" @isset($concept) value="{{ $concept }}" @endisset @endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@php
				$inputs = 
				[
					[
						"input_classEx"		=> "input-text-date datepicker",
						"input_attributeEx" => "autocomplete=\"off\" title=\"Desde\" type=\"text\" name=\"mindate\" step=\"1\" placeholder=\"Desde\" id=\"mindate\" value=\"".(isset($mindate) ? $mindate : '')."\""
					],
					[
						"input_classEx"		=> "input-text-date datepicker",
						"input_attributeEx" => "title=\"Hasta\" type=\"text\" name=\"maxdate\" step=\"1\" placeholder=\"Hasta\" id=\"maxdate\" autocomplete=\"off\" value=\"".(isset($maxdate) ? $maxdate : '')."\""
					]
				];
			@endphp
			@component("components.labels.label") Rango de fechas: @endcomponent
			@RangeInput(["inputs" => $inputs]) @endRangeInput
		</div>
		@slot('export')
			@if(count($data))
				<div class="col-span-4 flex flex-row justify-end">
					@Button(["classEx" => "export", "variant" => "success", "attributeEx" => "type=\"submit\" formaction=\"".route('warehouse.report.inputsOutputs.excel')."\"", "label" => "<span class=\"icon-file-excel\"></span> Exportar a Excel"])@endButton
				</div>
			@endif
		@endslot
	@endSearchForm
	@if (count($data))
		@php
			$modelHead = 
			[
				[
					["value" 	=> "Lote"],
					["value" 	=> "Cantidad"],
					["value" 	=> "Producto/Material"],
					["value" 	=> "Categoría"],
					["value" 	=> "Cuenta"],
					["value" 	=> "Ubicación/sede"],
					["value" 	=> "Acción"]
				]
			];
			$body = [];
			$modelBody = [];
			foreach ($data as $warehouse)
			{
				$body = 
				[
					[
						"content" =>
						[
							"label"	=> $warehouse->lot->idlot
						]
					],
					[
						"content" =>
						[
							"label"	=> $warehouse->quantity
						]
					],
					[
						"content" =>
						[
							"label"	=> ($warehouse->cat_c->description != "" ? htmlentities($warehouse->cat_c->description) : "---")
						]
					],
					[
						"content" =>
						[
							"label"	=> $warehouse->wareHouse ? $warehouse->wareHouse->description : 'Sin categoría'
						]
					],
					[
						"content" =>
						[
							"label"	=> $warehouse->account ? ($warehouse->accounts->account. ' ' .$warehouse->accounts->description . '('.$warehouse->accounts->content.')' ) : '---'
						]
					],
					[
						"content" =>
						[
							"label"	=> $warehouse->location ? $warehouse->location->place : '---'
						]
					],
					[
						"content" =>
						[
							"kind"			=> "components.buttons.button",
							"classEx"		=> "detail",
							"variant"		=> "secondary",
							"attributeEx"	=> "type=\"button\" title=\"Detalles\" value=\"".$warehouse->idwarehouse."\"",
							"label"			=> "<span class=\"icon-search\"></span>"
						]
					]
				];
				$modelBody[] = $body;
			}
		@endphp
		@component("components.tables.table", ["modelHead" => $modelHead, "modelBody" => $modelBody])
			@slot("classEx") table @endslot
			@slot("attributeEx") id="table-computer" @endslot
		@endcomponent
		{{ $data->appends($_GET)->links() }}
	@endif
	@if(isset($data) && count($data) == 0)
		@component("components.labels.not-found", ["attributeEx" => "id=\"not-found\"", "text" => "RESULTADO NO ENCONTRADO"]) @endcomponent
	@endif
	@component("components.modals.modal", ["attributeEx" => "id=\"myModal\"", "modalTitle" => "DETALLES"])
		@slot('modalFooter')
			@Button(["classEx" => "exit", "variant" => "red"]) Cerrar @endButton
		@endslot
	@endcomponent
@endsection

@section('scripts')
	<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
	<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
	<script type="text/javascript">
		$(document).ready(function()
		{
			@ScriptSelect(
			[
				"selects" => 
				[
					[
						"identificator"          => ".js-enterprises", 
						"placeholder"            => "Seleccione la empresa",
						"maximumSelectionLength" => "1"
					]
				]
			]) @endScriptSelect
			generalSelect({'selector':'.js-cat', 'model':56});
			generalSelect({'selector':'.js-places', 'model':38});
			warehouseType	= $('.js-cat option:selected').val();
			generalSelect({'selector':'.js-accounts', 'depends':'.js-enterprises', 'model':57, 'warehouseType':warehouseType});
			$( ".datepicker" ).datepicker({ maxDate: 0, dateFormat: "dd-mm-yy" });
			$(document).on('change','.js-enterprises,.js-cat',function()
			{
				warehouseType	= $('.js-cat option:selected').val();
				$('.js-accounts').html('');
				generalSelect({'selector':'.js-accounts', 'depends':'.js-enterprises', 'model':57, 'warehouseType':warehouseType});
			})
			.on('click','.detail', function()
			{
				id 	= $(this).val();
				
				$.ajax(
				{
					type : 'get',
					url  : '{{ route("warehouse.inputs_outputs.modal")}}',
					data : { 'id':id },
					success : function(data)
					{
						$('#myModal').modal('show');
						$('.modal-body').html(data);
					},
					error : function()
					{
						swal('','Sucedió un error, por favor intente de nuevo.','error');
						$('#myModal').modal('hide');
					}
				})
			})
			.on('click','.exit',function()
			{
				$('#detail').slideUp();
				$('#myModal').modal('hide');
				$('.detail').removeAttr('disabled');
			});
		});
		function search_accounts(first = false)
		{
			enterprise		= $('.js-enterprises option:selected').val();
			warehouseType	= $('.js-cat option:selected').val();
			idAccAcc		= Number($('#current_account_id').val());
			if(!first)
			{
				$('.js-accounts').empty();
			}
			generalSelect({'selector':'.js-accounts', 'depends':'.js-enterprises', 'model':57, 'warehouseType':warehouseType});
		}
	</script>
@endsection
