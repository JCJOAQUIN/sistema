@extends('layouts.child_module')
  
@section('data')
	@component("components.labels.title-divisor") BUSCAR SOLICITUDES @endcomponent
	@php
		$values = ["folio" => $folio, "minDate" => $mindate, "maxDate" => $maxdate];
		$hidden = ['enterprise','name'];
	@endphp
	@component("components.forms.searchForm",["attributeEx" => "id=\"formsearch\"","values" => $values, "hidden" => $hidden])
		@slot("contentEx")
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
			<div class="col-span-2">
				@component("components.labels.label") Proyecto: @endcomponent
				@php
					$options = collect();
					if (isset($projectid)) 
					{
						$project = App\Project::where('idproyect',$projectid)->get();
						$options = $options->concat([["value"=>$project->first()->idproyect, "selected"=>"selected", "description"=>$project->first()->proyectName]]);
					}
					$attributeEx	= "name=\"projectid\" title=\"Proyecto\" multiple=\"multiple\"";
					$classEx		= "js-project";
				@endphp
				@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label')Tipo de Solicitud:@endcomponent
				@php
					$options = collect();
					foreach(App\RequestKind::whereIn('idrequestkind',[1,2,3,8,9,11,12,13,14,15,16,17])->orderBy('kind','ASC')->get() as $k) 
					{
						$description = $k->kind;
						if(isset($kind) && $k->idrequestkind == $kind)
						{
							$options = $options->concat([["value"=>$k->idrequestkind, "selected"=>"selected", "description"=>$description]]);
						}
						else
						{
							$options = $options->concat([["value"=>$k->idrequestkind, "description"=>$description]]);
						}
					}
				@endphp
				@component('components.inputs.select', 
					[
						'attributeEx' => "title=\"Tipo de Solicitud\" multiple=\"multiple\" name=\"kind\"", 
						'classEx'     => "js-kind removeselect", 
						"options"     => $options
					]
				)
				@endcomponent
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
		@endslot
		@if (count($payments) > 0)
			@slot("export")
				<div class="float-right">
					@component('components.labels.label')
						@component('components.buttons.button',['variant' => 'success'])
							@slot('attributeEx') 
								type=submit 
								formaction={{ route('report.payments.export') }} @endslot
							@slot('label')
								<span>Exportar a Excel</span><span class="icon-file-excel"></span> 
							@endslot
						@endcomponent
					@endcomponent
				</div>
			@endslot
		@endif
	@endcomponent
	@if(count($payments)>0)
		@php
			$body		= [];
			$modelBody	= [];
			$modelHead	= 
			[
				[
					["value" => "Folio"],
					["value" => "Tipo de solicitud"],
					["value" => "Empresa"],
					["value" => "Clasificación del gasto"],
					["value" => "Fecha de pago"],
					["value" => "Importe"],
					["value" => "Acción"]
				]
			];
			foreach($payments as $payment)
			{
				$body = 
				[
					[
						"content" =>
						[
							"label" => $payment->idFolio
						]
					],
					[
						"content" =>
						[
							"label" => $payment->request->requestkind->kind
						]
					],
					[
						"content" =>
						[
							"label" => $payment->enterprise->name
						]
					],
					[
						"content" =>
						[
							"label" => $payment->accounts->fullClasificacionName()
						]
					],
					[
						"content" =>
						[				
							"label" => Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$payment->paymentDate)->format('d-m-Y'),
						]
					],
					[
						"content" =>
						[
							"label" => "$".number_format($payment->amount,2)
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
								"attributeEx" => "type=\"hidden\" value=\"".$payment->idpayment."\"",
								"classEx"     => "idpayment",
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
		{{ $payments->appends($_GET)->links() }}

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
					"identificator"			=> ".js-conciliation",
					"placeholder"			=> "Seleccione una opción",
					"languaje"				=> "es",
					"maximumSelectionLength" => "1",

				],
				[
					"identificator"			=> ".js-kind",
					"placeholder"			=> "Seleccione un tipo de solicitud",
					"languaje"				=> "es",
					"maximumSelectionLength" => "1",
				]
			]);
		@endphp
		@component("components.scripts.selects",["selects" => $selects]) @endcomponent
		generalSelect({'selector':'[name="account"]','depends': '[name="enterpriseid"]','model': 10});
		generalSelect({'selector':'[name="projectid"]','model':21});

		$(document).on('click','[data-toggle="modal"]', function()
		{
			idpayment = $(this).parents('.tr').find('.idpayment').val();
			$.ajax(
			{
				type : 'get',
				url  : '{{ route("report.payments.detail") }}',
				data : {'idpayment':idpayment},
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
