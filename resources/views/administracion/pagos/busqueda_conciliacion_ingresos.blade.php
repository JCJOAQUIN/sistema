@extends('layouts.child_module')
@section('data')
	@component('components.labels.title-divisor') BUSCAR CONCILIACIÓN @endcomponent
	@component("components.forms.searchForm", ["variant" => "default", "attributeEx" => "id=\"formsearch\""])
		<div class="col-span-2">
			@component("components.labels.label") Movimiento: @endcomponent
			@component("components.inputs.input-text")
				@slot("attributeEx")
					name="mov"
					id="input-search"
					placeholder="Ingrese una descripción"
					value="{{ isset($mov) ? $mov : '' }}"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component("components.labels.label") Rango de fechas: @endcomponent
			@php			
				if(isset($mindate) && isset($maxdate))
				{ 
					$inputs= [
						[
							"input_classEx" => "input-text-date datepicker",
							"input_attributeEx" => "name=\"mindate\" id=\"mindate\" step=\"1\" placeholder=\"Desde\" value=\"".$mindate."\"",
						],
						[
							"input_classEx" => "input-text-date datepicker",
							"input_attributeEx" => "name=\"maxdate\" id=\"maxdate\" step=\"1\" placeholder=\"Hasta\" value=\"".$maxdate."\"",
						]
					];
				}
				else if(!isset($mindate) && isset($maxdate))
				{
					$inputs= [
						[
							"input_classEx" => "input-text-date datepicker",
							"input_attributeEx" => "name=\"mindate\" id=\"mindate\" step=\"1\" placeholder=\"Desde\"",
						],
						[
							"input_classEx" => "input-text-date datepicker",
							"input_attributeEx" => "name=\"maxdate\" id=\"maxdate\" step=\"1\" placeholder=\"Hasta\" value=\"".$maxdate."\"",
						]
					];
				}
				else if(isset($mindate) && !isset($maxdate))
				{
					$inputs= [
						[
							"input_classEx" => "input-text-date datepicker",
							"input_attributeEx" => "name=\"mindate\" id=\"mindate\" step=\"1\" placeholder=\"Desde\" value=\"".$minDate."\"",
						],
						[
							"input_classEx" => "input-text-date datepicker",
							"input_attributeEx" => "name=\"maxdate\" id=\"maxdate\" step=\"1\" placeholder=\"Hasta\"",
						]
					];
				}
				else
				{
					$inputs= [
						[
							"input_classEx" => "input-text-date datepicker",
							"input_attributeEx" => "name=\"mindate\" id=\"mindate\" step=\"1\" placeholder=\"Desde\"",
						],
						[
							"input_classEx" => "input-text-date datepicker",
							"input_attributeEx" => "name=\"maxdate\" id=\"maxdate\" step=\"1\" placeholder=\"Hasta\"",
						]
					];
				}
			@endphp
			@component("components.inputs.range-input",["inputs" => $inputs]) @endcomponent
		</div>
		<div class="col-span-2">
			@component("components.labels.label") Empresa: @endcomponent
			@php
				$options = collect();
				foreach(App\Enterprise::orderBy('name','asc')->whereIn('id',Auth::user()->inChargeEnt(193)->pluck('enterprise_id'))->get() as $enterprise)
				{
					$description = strlen($enterprise->name) >= 35 ? substr(strip_tags($enterprise->name),0,35)."..." : $enterprise->name;
					if(isset($idEnterprise) && $idEnterprise == $enterprise->id)
					{
						$options = $options->concat([["value"=>$enterprise->id, "selected"=>"selected", "description"=>$description]]);
					}
					else
					{
						$options = $options->concat([["value"=>$enterprise->id, "description"=>$description]]);
					}
				}
				$attributeEx = "title=\"Empresa\" name=\"idEnterprise\" multiple=\"multiple\"";
				$classEx = "js-enterprise";
			@endphp
			@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])
			@endcomponent
		</div>
		<div class="col-span-2">
			@component("components.labels.label") Cuenta: @endcomponent
			@php
				$options = collect();
				if(isset($idEnterprise) && isset($idAccount))
				{
					$accountSelected = App\Account::find($idAccount);
					$options = $options->concat([["value" => $idAccount, "selected"=>"selected", "description" => $accountSelected->account." - ".$accountSelected->description." (".$accountSelected->content.")"]]);
				}
				$attributeEx = "title=\"Cuenta\" name=\"idAccount\" multiple=\"multiple\"";
				$classEx = "js-account";
			@endphp
			@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])
			@endcomponent
		</div>
		@if(count($conciliations) > 0)
			@slot("export")
			<div class="text-right">
				<label>
					@component("components.buttons.button",["variant" => "success"])
					@slot("attributeEx") type="submit" formaction="{{ route('payments.conciliation-income.export') }}" @endslot
					@slot("slot") <span>Exportar a Excel</span><span class="icon-file-excel"></span> @endslot
					@endcomponent
				</label>
			</div>
			@endslot
		@endif
	@endcomponent
	@if(count($conciliations) > 0)
		@php
			$modelHead = 
			[	
				[
					["value" => "Datos del Pago", "colspan" => 3],
					["value" => "Datos del Movimiento", "colspan" => 6]
				],
				[
					["value" => "Empresa"],
					["value" => "Solicitud"],
					["value" => "Importe del Pago"],
					["value" => "Movimiento"],
					["value" => "Tipo"],
					["value" => "Importe del Movimiento"],
					["value" => "Fecha de Conciliación"],
					["value" => "Clasificación del gasto"],
					["value" => "Acción"]
				]
			];

			$modelBody = [];
			foreach($conciliations as $conciliation)
			{
				$account = App\Account::find($conciliation->movements->idAccount);
				$classifyExpense = $account->account." - ".$account->description." (".$account->content.")";
				if($conciliation->type == 1)
				{
					$requestKind 		= "Solicitud de ".$conciliation->bills->requestHasBill->requestkind->kind;
					$totalConciliation 	= "$ ".number_format($conciliation->bills->total,2);
					$idBill				= $conciliation->idbill;
				}
				else
				{
					$requestKind 		= "Solicitud de ".$conciliation->billsNF->requestHasBill->requestkind->kind;
					$totalConciliation 	= "$ ".number_format($conciliation->billsNF->total,2);
					$idBill				= $conciliation->idNoFiscalBill;
				}

				$body = 
				[
					"classEx" => "tr",
					[
						"classEx" => "td",
						"content" =>
						[
							[
								"kind" => "components.labels.label",
								"label" => $conciliation->movements->enterprise->name,
							]
						]
					],
					[
						"classEx" => "td",
						"content" =>
						[
							[
								"kind" => "components.labels.label",
								"label" => $requestKind,
							]
						]
					],
					[
						"classEx" => "td",
						"content" =>
						[
							[
								"kind" => "components.labels.label",
								"label" => $totalConciliation,
							]
						]
					],
					[
						"classEx" => "td",
						"content" =>
						[
							[
								"kind" => "components.labels.label",
								"label" => $conciliation->movements->description,
							]
						]
					],
					[
						"classEx" => "td",
						"content" =>
						[
							[
								"kind" => "components.labels.label",
								"label" => $conciliation->movements->movementType,
							]
						]
					],
					[
						"classEx" => "td",
						"content" =>
						[
							[
								"kind" => "components.labels.label",
								"label" => "$ ".number_format($conciliation->movements->amount,2),
							]
						]
					],
					[
						"classEx" => "td",
						"content" =>
						[
							[
								"kind" => "components.labels.label",
								"label" => Carbon\Carbon::createFromFormat('Y-m-d', $conciliation->conciliationDate)->format('d-m-Y'),
							]
						]
					],
					[
						"classEx" => "td",
						"content" =>
						[
							[
								"kind" => "components.labels.label",
								"label" => $classifyExpense,
							]
						]
					],
					[
						"classEx" => "td",
						"content" =>
						[
							[
								"kind" => "components.buttons.button",
								"attributeEx" => "title=\"Ver Datos\" type=\"button\" data-toggle=\"modal\" data-target=\"#billDetailModal\" data-bill=\"".$idBill."\" data-type=\"".$conciliation->type."\" data-movement=\"".$conciliation->idmovement."\"",
								"classEx" => "detailConciliation",
								"variant" => "secondary",
								"label" => "<span class=\"icon-search\"></span>",
							],
							[
								"kind" => "components.buttons.button", 
								"attributeEx" => "title=\"Eliminar Conciliación\" type=\"button\" data-link=\"".route('payments.conciliation-income.update',$conciliation->idmovement)."\"",
								"classEx" => "delete",
								"variant" => "red",
								"label" => "<span class='icon-bin'></span>",
							],
							[
								"kind" => "components.inputs.input-text",
								"attributeEx" => "type=\"hidden\" value=\"".$idBill."\"",
								"classEx" => "idbill",
							],
							[
								"kind" => "components.inputs.input-text",
								"attributeEx" => "type=\"hidden\" value=\"".$conciliation->type."\"",
								"classEx" => "type",
							],
							[
								"kind" => "components.inputs.input-text",
								"attributeEx" => "type=\"hidden\" value=\"".$conciliation->idmovement."\"",
								"classEx" => "idmovement",
							],
						]
					],
				];
				$modelBody [] = $body;
			}
		@endphp
		@component("components.tables.table", ["modelHead" => $modelHead, "modelBody" => $modelBody]) @endcomponent
		@component("components.modals.modal",[ "variant" => "large" ])
			@slot("id")
				billDetailModal
			@endslot
			@slot("attributeEx")
				tabindex="-1"
			@endslot
			@slot("modalHeader")
			@component("components.buttons.button")
				@slot("attributeEx")
					type="button"
					data-dismiss="modal"
				@endslot
				@slot('classEx')
					close
				@endslot
				<span aria-hidden="true">&times;</span>
			@endcomponent
			@endslot
			@slot("modalBody")

			@endslot
		@endcomponent
		{{ $conciliations->appends($_GET)->links() }}
	@else
		@component("components.labels.not-found") @endcomponent
	@endif
@endsection

@section('scripts')
	<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
	<script src="{{ asset('js/jquery-ui.js') }}"></script>
	<script src="{{ asset('js/datepicker.js') }}"></script>
	<script type="text/javascript"> 
		$(document).ready(function()
		{
			@php
				$selects = collect([
					[
						"identificator"      	 => ".js-enterprise",
						"placeholder"            => "Seleccione la empresa",
						"maximumSelectionLength" => "1"
					]
				])
			@endphp
			@component("components.scripts.selects",["selects" => $selects]) @endcomponent
			generalSelect({'selector': '.js-account', 'depends': '.js-enterprise', 'model': 10});
		});
		$(document).on('click','.exit',function()
		{
			$('#detail').slideUp();
			$('#myModal').hide();
			$('.detail').removeAttr('disabled');
		})
		.on('click','[data-toggle="modal"]',function()
		{
			swal({
				icon               : '{{ asset(getenv('LOADING_IMG')) }}',
				button             : false,
				closeOnClickOutside: false,
				closeOnEsc         : false
			});
			$('.modal-body').html('');
			idbill		= $(this).attr('data-bill');
			idmovement	= $(this).attr('data-movement');
			type		= $(this).attr('data-type');

			$.ajax(
			{
				type	: 'post',
				url		: '{{ route("payments.conciliation-income.detail") }}',
				data	: {'idbill':idbill,'idmovement':idmovement,'type':type},
				success	: function(data)
				{
					$('.modal-body').html(data);
				},
				error	: function(data)
				{

					$('#detail').slideUp();
					$('#billDetailModal').hide();
					$('.modal-backdrop').remove();
					$('.modal-body').html('');
					swal('','Sucedió un error, por favor intente de nuevo.','error');
				}
			}).done(function(data)
			{
				swal.close();
			});
		})
		.on('click','.delete',function(e)
		{
			e.preventDefault();
			swal({
				title		: "Confirmar",
				text		: "¿Desea eliminar la conciliación?",
				icon		: "warning",
				buttons		: ["Cancelar","Eliminar"],
				dangerMode	: true,
				})
				.then((isConfirm) =>
				{
					if(isConfirm)
					{
						swal({
							icon               : '{{ asset(getenv('LOADING_IMG')) }}',
							button             : false,
							closeOnClickOutside: false,
							closeOnEsc         : false
						});
						url			= $(this).attr('data-link');
						idbill 		= $(this).siblings('.idbill').val();
						type		= $(this).siblings('.type').val();
						idmovement	= $(this).siblings('.idmovement').val();
						
						form = $('<form action="'+url+'" method="POST"></form>')
							.append($('@csrf'))
							.append($('@method("PUT")'))
							.append($('<input type="hidden" name="idbill" value="'+idbill+'">'))
							.append($('<input type="hidden" name="type" value="'+type+'">'))
							.append($('<input type="hidden" name="idmovement" value="'+idmovement+'">'));
						$(document.body).append(form);
						form.submit();
					}
				});
		})
		.on('change','.js-enterprise',function()
		{
			$('.js-account').html('');
		});
	</script>
@endsection
