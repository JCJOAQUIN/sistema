@extends('layouts.child_module')
@section('data')
	<div class="content-start items-center flex flex-wrap justify-center text-center w-full mb-4">
		@component('components.buttons.button-secondary')
			@slot('classEx')
				active:bg-orange-600 active:border-none active:text-white active:shadow-md
			@endslot
			@slot('href')
				{{ url('report/administration/conciliation') }}
			@endslot
			Conciliación normal
		@endcomponent
		@component('components.buttons.button-secondary')
			@slot('classEx')
				bg-orange-600 border-none text-white shadow-md active:bg-orange-600 active:border-none active:text-white active:shadow-md
			@endslot
			@slot('href')
				{{ url('report/administration/conciliation-nomina') }}
			@endslot
			Conciliación de nómina
		@endcomponent
	</div>

	@component("components.labels.title-divisor") BUSCAR CONCILIACIÓN @endcomponent
	@php
		$values = ["minDate" => $mindate, "maxDate" => $maxdate];
		$hidden = ['enterprise','name','folio'];
	@endphp
	@component("components.forms.searchForm",["attributeEx" => "id=\"formsearch\"","values" => $values, "hidden" => $hidden])
		@slot("contentEx")
			<div class="col-span-2">
				@component('components.labels.label')Movimiento:@endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type        = "text" 
						name        = "mov" 
						id          = "input-search" 
						placeholder = "Ingrese una descripción" 
						value       = "{{ isset($mov) ? $mov : '' }}"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Empresa: @endcomponent
				@php
					$options = collect();
					foreach(App\Enterprise::where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeEnt($option_id)->pluck('enterprise_id'))->orderBy('name','asc')->get() as $enterprise)
					{
						$description = strlen($enterprise->name) >= 35 ? substr(strip_tags($enterprise->name),0,35)."..." : $enterprise->name;
						if (isset($enterpriseid) && $enterprise->id == $enterpriseid)
						{
							$options = $options->concat([["value"=>$enterprise->id, "selected"=>"selected", "description"=>$description]]);
						}
						else
						{
							$options = $options->concat([["value"=>$enterprise->id, "description"=>$description]]);
						}
					}
					$attributeEx	= "name=\"enterpriseid\" title=\"Empresa\" multiple=\"multiple\"";
					$classEx		= "js-enterprise";
				@endphp
				@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label')Cuenta:@endcomponent
				@php
					$optionsAccount = collect();
					if(isset($enterpriseid) && isset($account))
					{
						$acc = App\Account::orderNumber()->where('idEnterprise',$enterpriseid)->where('idAccAcc',$account)->where('selectable',1)->get();
						if(count($acc)>0)
						{
							$description    = $acc->first()->account."-".$acc->first()->description."(".$acc->first()->content.")";
							$optionsAccount = $optionsAccount->concat([['value'=>$acc->first()->idAccAcc, 'selected'=>'selected', 'description'=>$description]]);
						}
					}
				@endphp
				@component('components.inputs.select', 
					[
						'attributeEx' => "title=\"Cuenta\" multiple=\"multiple\" name=\"account\"", 
						'classEx'     => "js-account removeselect", 
						"options"     => $optionsAccount
					]
				)
				@endcomponent
			</div>
		@endslot
		@if (count($payments) > 0)
			@slot("export")
				<div class="float-right">
					@component('components.labels.label')
						@component('components.buttons.button',['variant' => 'success'])
							@slot('attributeEx') 
								type=submit 
								formaction={{ route('report.conciliation-nomina.export') }} @endslot
							@slot('label')
								<span>Exportar a Excel</span><span class="icon-file-excel"></span> 
							@endslot
						@endcomponent
					@endcomponent
				</div>
			@endslot
		@endif
	@endcomponent
	@if(count($payments) > 0)
		@php
			$modelHead = 
			[	
				[
					["value" => "Datos del Pago", "colspan" => 5],
					["value" => "Datos del Movimiento", "colspan" => 4]

				],
				[
					["value" => "Solicitud"],
					["value" => "Empleado"],
					["value" => "Empresa"],
					["value" => "Importe del Pago"],
					["value" => "Clasificación del gasto"],
					["value" => "Movimiento"],
					["value" => "Importe del Movimiento"],
					["value" => "Fecha de Conciliación"],
					["value" => "Acción"]
				]
			];

			$modelBody = [];
			foreach($payments as $payment)
			{
				$body = 
				[
					"classEx" => "tr",
					[
						"classEx" => "td",
						"content" => 
						[
							[
								"kind"  => "components.labels.label",
								"label" => $payment->idFolio.' - '.$payment->request->nominasReal->first()->title,
							]
						]
					],
					[
						"classEx" => "td", 
						"content" =>
						[
							[
								"kind"  => "components.labels.label",
								"label" => $payment->nominaEmployee->employee->first()->fullName(),
							]
						]
					],
					[
						"classEx" => "td", 
						"content" =>
						[
							[
								"kind"  => "components.labels.label",
								"label" => $payment->enterprise->name,
							]
						]
					],
					[
						"classEx" => "td",
						"content" =>
						[
							[
								"kind"  => "components.labels.label",
								"label" => "$ ".number_format($payment->amount,2),
							]
						]
					],
					[
						"classEx" => "td",
						"content" =>
						[
							[
								"kind"  => "components.labels.label",
								"label" => $payment->accounts->fullClasificacionName(),
							]
						]
					],
					[
						"classEx" => "td",
						"content" =>
						[
							[
								"kind"  => "components.labels.label",
								"label" => $payment->movement->description,
							]
						]
					],
					[
						"classEx" => "td",
						"content" =>
						[
							[
								"kind"  => "components.labels.label",
								"label" => $payment->movement->amount,
							]
						]
					],
					[
						"classEx" => "td",
						"content" =>
						[
							[
								"kind"  => "components.labels.label",
								"label" => Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $payment->conciliationDate)->format('d-m-Y H:i'),
							]
						]
					],
					[
						"classEx" => "td",
						"content" =>
						[
							[
								"kind"        => "components.buttons.button",
								"attributeEx" => "title=\"Detalles de Conciliación\" alt=\"Detalles de Conciliación\" type=\"button\" data-toggle=\"modal\" data-target=\"#myModal\"",
								"classEx"     => "detailConciliation",
								"variant"     => "secondary",
								"label"       => "<span class=\"icon-search\"></span>",
							],
							[
								"kind"        => "components.inputs.input-text",
								"attributeEx" => "type=\"hidden\" value=\"".$payment->idpayment."\"",
								"classEx"     => "payment",
							],
						]
					],
				];
				$modelBody [] = $body;
			}
		@endphp
		@component("components.tables.table", ["modelGroup" => $modelGroup, "modelHead" => $modelHead, "modelBody" => $modelBody]) @endcomponent
		
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
		{{ $payments->appends($_GET)->links() }}
	@else
		@component("components.labels.not-found") @endcomponent
	@endif
@endsection	
@section('scripts')
	<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
	<script src="{{ asset('js/jquery-ui.js') }}"></script>
	<script src="{{ asset('js/jquery.numeric.js') }}"></script>
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
						"identificator"			=> ".js-account",
						"placeholder"			=> "Seleccione la cuenta",
						"languaje"				=> "es",
						"maximumSelectionLength" => "1",

					]
				]);
			@endphp
			@component("components.scripts.selects",["selects" => $selects]) @endcomponent
			generalSelect({'selector':'[name="account"]','depends': '[name="enterpriseid"]','model': 10});

			$(document).on('click','.detailConciliation',function()
			{
				swal({
					icon               : '{{ asset(getenv('LOADING_IMG')) }}',
					button             : false,
					closeOnClickOutside: false,
					closeOnEsc         : false
				});
				$('#myModal .modal-body').html('');
				idpayment = $(this).parents('.tr').find('.payment').val();
				$.ajax(
				{
					type 	: 'post',
					url 	: '{{ route("payments.conciliation-nomina.details") }}',
					data 	: {'idpayment':idpayment},
					success	: function(data)
					{
						$('#myModal .modal-body').html(data);
						$('.detail').attr('disabled','disabled');
					},
					error: function(data)
					{
						$('#detail').slideUp();
						$('#myModal').hide();
						$('.detail').removeAttr('disabled');
						$('.modal-backdrop').remove();
						swal('','Sucedió un error, por favor intente de nuevo.','error');
					}
				}).done(function(data)
				{
					swal.close();
				});
			})
			.on('change','.js-enterprise',function()
			{
				$('.js-account').empty();
				$enterprise = $(this).val();
				if ($enterprise != 'todas') 
				{
					$.ajax(
					{
						type 	: 'get',
						url 	: '{{ route("report.purchase.account") }}',
						data 	: {'enterpriseid':$enterprise},
						success : function(data)
						{
							$.each(data,function(i, d)
							{
								$('.js-account').append('<option value='+d.idAccAcc+'>'+d.account+' - '+d.description+'</option>');
							});
						},
						error : function()
						{
							swal('','Sucedió un error, por favor intente de nuevo.','error');
							$('.js-account').val(null).trigger('change');
						}
					})
				}
			})
			.on('click','.exit',function()
			{
				$('#detail').slideUp();
				$('#myModal').hide();
				$('.detail').removeAttr('disabled');
			});
		});
	</script> 
@endsection
