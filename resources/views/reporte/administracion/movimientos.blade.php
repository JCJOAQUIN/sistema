@extends('layouts.child_module')
@section('data')
	@component('components.labels.title-divisor')    BUSCAR MOVIMIENTOS @endcomponent
	@component('components.forms.searchForm',["variant" => "default"])
		<div class="col-span-2">
			@component('components.labels.label') Movimiento: @endcomponent
			@component('components.inputs.input-text')
				@slot('classEx')
					input-text-search
					name
				@endslot
				@slot('attributeEx')
					name="mov"
					id="input-search"
					placeholder="Ingrese el movimiento"
					value="{{ isset($mov) ? $mov : '' }}"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Rango de Fechas: @endcomponent
			@php
			$minDate = isset($mindate) ? $mindate : '';
			$maxDate = isset($maxdate) ? $maxdate : '';
			$inputsRange =
			[
				[
					"input_classEx"		=>	"input-text-date datepicker",
					"input_attributeEx"	=>	"name=\"mindate\" step=\"1\" placeholder=\"Desde\" value=\"".$minDate."\"",
				],
				[
					"input_classEx"		=>	"input-text-date datepicker",
					"input_attributeEx"	=>	"name=\"maxdate\" step=\"1\" placeholder=\"Hasta\" value=\"".$maxDate."\"",
				]
			];
			@endphp
			@component("components.inputs.range-input",["inputs" => $inputsRange]) @endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label')Estado de Conciliación :@endcomponent
			@php
				$options = collect();
				if((isset($conciliation) && $conciliation == 'all') || !isset($conciliation))
				{
					$options = $options->concat([["value"=>"all", "selected"=>"selected", "description"=> "Todos"]]);
				}
				else
				{
					$options = $options->concat([["value"=>"all", "description"=> "Todos"]]);
				}

				if(isset($conciliation) && $conciliation == '1')
				{
					$options = $options->concat([["value"=>'1', "selected"=>"selected", "description"=> "Conciliados"]]);
				}
				else
				{
					$options = $options->concat([["value"=>'1', "description"=> "Conciliados"]]);
				}

				if(isset($conciliation) && $conciliation == '0')
				{
					$options = $options->concat([["value"=>'0', "selected"=>"selected", "description"=> "Sin conciliar"]]);
				}
				else
				{
					$options = $options->concat([["value"=>'0', "description"=> "Sin conciliar"]]);
				}
			@endphp
			@component('components.inputs.select', 
				[
					'attributeEx' => "title=\"Estado de Concilación\" name=\"conciliation\" multiple=\"multiple\"",
					'classEx'     => "js-conciliation", 
					"options"     => $options
				]
			)
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label')Tipo de Movimiento :@endcomponent
			@php
				$options = collect();
				if(isset($kind) && in_array('undefined', $kind))
				{
					$options = $options->concat([["value"=>"undefined", "selected"=>"selected", "description"=> "No definido"]]);
				}
				else
				{
					$options = $options->concat([["value"=>"undefined", "description"=> "No definido"]]);
				}

				if(isset($kind) && in_array('Devolución', $kind))
				{
					$options = $options->concat([["value"=>'Devolución', "selected"=>"selected", "description"=> "Devolución"]]);
				}
				else
				{
					$options = $options->concat([["value"=>'Devolución', "description"=> "Devolución"]]);
				}

				if(isset($kind) && in_array('Egreso', $kind))
				{
					$options = $options->concat([["value"=>'Egreso', "selected"=>"selected", "description"=> "Egreso"]]);
				}
				else
				{
					$options = $options->concat([["value"=>'Egreso', "description"=> "Egreso"]]);
				}

				if(isset($kind) && in_array('Ingreso', $kind))
				{
					$options = $options->concat([["value"=>'Ingreso', "selected"=>"selected", "description"=> "Ingreso"]]);
				}
				else
				{
					$options = $options->concat([["value"=>'Ingreso', "description"=> "Ingreso"]]);
				}
			@endphp
			@component('components.inputs.select', 
				[
					'attributeEx' => "title=\"Tipo de movimiento\" name=\"kind[]\" multiple=\"multiple\"",
					'classEx'     => "js-kind", 
					"options"     => $options
				]
			)
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Empresa @endcomponent
			@php
				$options = collect();
				foreach(App\Enterprise::orderBy('name','asc')->whereIn('id',Auth::user()->inChargeEnt(102)->pluck('enterprise_id'))->get() as $enterprise)
				{
					$description = strlen($enterprise->name) >= 35 ? substr(strip_tags($enterprise->name),0,35).'...' : $enterprise->name;
					if(isset($enterpriseid) && $enterpriseid == $enterprise->id)
					{
						$options = $options->concat([["value"=>$enterprise->id, "selected"=>"selected", "description"=>$description]]);
					}
					else
					{
						$options = $options->concat([["value"=>$enterprise->id, "description"=>$description]]);
					}
				}
				$attributeEx =  "name=\"enterpriseid\" multiple=\"multiple\"";
				$classEx = "js-enterprise";
			@endphp
			@component('components.inputs.select',["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])  @endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Clasificación del Gasto: @endcomponent
			@php
				$options = collect();
				foreach(App\Account::orderNumber()->where('idEnterprise',$enterpriseid)->where('selectable',1)->selectRaw('idAccAcc, CONCAT(account, " - ", description, " (", content, ")") as accountDescription')->get() as $acc)
				{
					$options = $options->concat(
					[
						[
							'value'			=> $account, 
							'description'	=> $acc->accountDescription,
							'selected'		=> ((isset($account) && $account == $acc->idAccAcc) ? 'selected' : '')
						]
					]);
				}
				$attributeEx =  "name=\"account\" multiple=\"multiple\"";
				$classEx = "js-account removeselect";
			@endphp
			@component('components.inputs.select',["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])  @endcomponent
		</div>
		@if(count($movements)>0)
			@slot('export')
				<div class="text-right">
					@Button(["classEx" => "export","variant" => "success", "attributeEx" => "type=\"submit\" formaction=\"".route('report.movements.export')."\""]) 
						@slot("slot") <span>Exportar a Excel</span><span class="icon-file-excel"></span> @endslot
					@endButton
				</div>
			@endslot
		@endif
	@endcomponent
	@if(count($movements)>0)
		@php
			$body		= [];
			$modelBody	= [];
			$modelHead	= 
			[
				[
					["value" => "Empresa"],
					["value" => "Cuenta"],
					["value" => "Importe"],
					["value" => "Descripción"],
					["value" => "Fecha"],
					["value" => "Acción"]
				]
			];
			foreach($movements as $movement)
			{
				$body = 
				[
					[
						"content" =>
						[
							"label" => $movement->enterprise->name,
						]
					],
					[
						"content" =>
						[
							"label" => $movement->accounts->account. " - " .$movement->accounts->description,
						]
					],
					[
						"content" =>
						[
							"label" => "$".number_format($movement->amount,2),
						]
					],
					[
						"content" =>
						[
							"label" => htmlentities($movement->description),
						]
					],
					[
						"content" =>
						[				
							"label" => Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$movement->movementDate)->format('d-m-Y'),
						]
					],
					[
						"content" =>
						[
							[
								"kind"          => "components.buttons.button",
								"buttonElement" => "button", 
								"label"			=> "<span class=\"icon-search\"></span>",
								"classEx"	   	=> "follow-btn btn-detail",
								"variant" 		=> "secondary",
								"attributeEx"  	=> "alt=\"Detalles\" title=\"Detalles\" type=\"button\" data-toggle=\"modal\" data-target=\"#myModal\""
							],
							[
								"kind"        => "components.inputs.input-text",
								"attributeEx" => "type=\"hidden\" value=\"".$movement->idmovement."\"",
								"classEx"     => "idmovement",
							]
						]
					],
				];
				$modelBody[] = $body;
			}
			
		@endphp
		@component("components.tables.table",[
			"modelHead" 			=> $modelHead,
			"modelBody" 			=> $modelBody,
		])
		@endcomponent
		{{ $movements->appends($_GET)->links() }}

		@component("components.modals.modal",[ "variant" => "large" ])
			@slot("id")
				myModal
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
	@else
		@component("components.labels.not-found")@endcomponent
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
					"identificator"			=> ".js-enterprise",
					"placeholder"			=> "Seleccione una empresa",
					"languaje"				=> "es",
					"maximumSelectionLength" => "1",
				],
				[
					"identificator"			=> ".js-kind",
					"placeholder"			=> "Seleccione el tipo",
					"languaje"				=> "es",
					"maximumSelectionLength" => "1",
				],
				[
					"identificator"			=> ".js-conciliation",
					"placeholder"			=> "Seleccione un estado",
					"languaje"				=> "es",
					"maximumSelectionLength" => "1",
				]
			]);
		@endphp
		@component("components.scripts.selects",["selects" => $selects]) @endcomponent
		generalSelect({'selector':'[name="account"]','depends': '[name="enterpriseid"]','model': 10});

		$(document).on('change','.js-enterprise',function()
		{
			$('.js-account').empty();
			$enterprise = $(this).val();
			$.ajax(
			{
				type	: 'get',
				url		: '{{ url("/administration/purchase/create/account") }}',
				data	: {'enterpriseid':$enterprise},
				success	: function(data)
				{
					$.each(data,function(i, d)
					{
						$('.js-account').append('<option value='+d.idAccAcc+'>'+d.account+' - '+d.description+' ('+d.content+')</option>');
					});
				},
				error : function()
				{
					swal('','Sucedió un error, por favor intente de nuevo.','error');
					$('.js-account').val(null).trigger('change');
				}
			})
		})
		.on('click','[data-toggle="modal"]', function()
		{
			idmovement = $(this).parents('.tr').find('.idmovement').val();
			$.ajax(
			{
				type	: 'get',
				url		: '{{ route('report.movements.detail') }}',
				data	: 
				{
					'idmovement':idmovement
				},
				success : function(data)
				{
					$('.modal-body').html(data);
				},
				error: function()
				{
					swal('','Sucedió un error, por favor intente de nuevo.','error');
					$('#myModal').modal('hide');
				}
			})
		})
		.on('click','.exit',function()
		{
			$('#myModal').hide();
		})
		
	});
</script> 
@endsection
