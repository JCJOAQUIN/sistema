@extends('layouts.child_module')
@section('data')
	@component("components.labels.title-divisor") BUSCAR SOLICITUDES @endcomponent
	@php
		$values = ["folio" => $folio, "name" => $name, "minDate" => $mindate, "maxDate" => $maxdate];
		$hidden = ['enterprise'];
	@endphp
	@component("components.forms.searchForm",["attributeEx" => "id=\"formsearch\"","values" => $values, "hidden" => $hidden])
		@slot("contentEx")
			<div class="col-span-2">
				@component('components.labels.label')Proveedor:@endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type        = "text" 
						name        = "provider" 
						id          = "input-search" 
						placeholder = "Ingrese un proveedor" 
						value       = "{{ isset($provider) ? $provider : '' }}"
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
			<div class="col-span-2">
				@component("components.labels.label") Estado de Solicitud: @endcomponent
				@php
					$options = collect();
					foreach(App\StatusRequest::whereIn('idrequestStatus',[4,5,6,7,10,11,12,13,18])->orderBy('description','asc')->get() as $s) 
					{
						$description = $s->description;
						if (isset($status) && in_array($s->idrequestStatus,$status))
						{
							$options = $options->concat([["value"=>$s->idrequestStatus, "selected"=>"selected", "description"=>$description]]);
						}
						else
						{
							$options = $options->concat([["value"=>$s->idrequestStatus,"description"=>$description]]);
						}
					}
					$attributeEx = "name=\"status[]\" multiple=\"multiple\"";
					$classEx = "js-status";
				@endphp
				@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Estado de Factura: @endcomponent
				@php
					$options = collect();
					
					if (isset($documents) && $documents == 'Pendiente')
					{
						$options = $options->concat([["value"=> "Pendiente", "selected"=>"selected", "description" => "Pendiente"]]);
					}
					else
					{
						$options = $options->concat([["value"=> "Pendiente", "description"=> "Pendiente"]]);
					}

					if (isset($documents) && $documents == 'Entregado')
					{
						$options = $options->concat([["value"=> "Entregado", "selected"=>"selected", "description" => "Entregado"]]);
					}
					else
					{
						$options = $options->concat([["value"=> "Entregado", "description"=> "Entregado"]]);
					}

					if (isset($documents) && $documents == 'No Aplica')
					{
						$options = $options->concat([["value"=> "No Aplica", "selected"=>"selected", "description" => "No Aplica"]]);
					}
					else
					{
						$options = $options->concat([["value"=> "No Aplica", "description"=> "No Aplica"]]);
					}
					$attributeEx	= "name=\"documents\" title=\"Estado de Factura\" multiple=\"multiple\"";
					$classEx		= "js-status-bill";
				@endphp
				@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])@endcomponent
			</div>
		@endslot
		@if (count($requests) > 0)
			@slot("export")
				<div class="float-right">
					@component('components.labels.label')
						@component('components.buttons.button',['variant' => 'success'])
							@slot('attributeEx') 
								type=submit 
								formaction={{ route('report.purchase.excel') }} @endslot
							@slot('label')
								<span>Exportar a Excel</span><span class="icon-file-excel"></span> 
							@endslot
						@endcomponent
					@endcomponent
				</div>
			@endslot
		@endif
	@endcomponent
	@if (count($requests) > 0)
		@php
			$body		= [];
			$modelBody	= [];
			$modelHead	= 
			[
				[
					["value"	=> "Folio"],
					["value"	=> "Título"],
					["value"	=> "Solicitante"],
					["value"	=> "Estado"],
					["value"	=> "Empresa"],
					["value"	=> "Clasificación del gasto"],
					["value"	=> "Fecha de elaboración"],
					["value"	=> "Acción"]
				]
			];

			foreach($requests as $request)
			{
				$body = 
				[
					[
						"content" =>
						[
							"label" => $request->new_folio != null ? $request->new_folio : $request->folio,
						]
					],
					[
						"content" =>
						[
							"label" =>  $request->purchases()->exists() && $request->purchases->first()->title != null ? $request->purchases->first()->title : 'No hay',
						]
					],
					[
						"content" =>
						[
							"label" => $request->requestUser()->exists() ? $request->requestUser->fullName() : "No hay solicitante",
						]
					],

					[
						"content" =>
						[
							"label" => $request->statusrequest()->exists() ? $request->statusrequest->description : "No existe",
						]
					],
					[
						"content" =>
						[
							"label" => $request->reviewedEnterprise()->exists() ? $request->reviewedEnterprise->name : ($request->requestEnterprise()->exists() ? $request->requestEnterprise->name : "No hay empresa"),
						]
					],
					[
						"content" =>
						[
							"label" => $request->accountsReview()->exists() ? $request->accountsReview->account.' '.$request->accountsReview->description :  $request->accounts->account.' '.$request->accounts->description
						]
					],
					[
						"content" =>
						[
							"label" => Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$request->fDate)->format('d-m-Y H:i'),
						]
					],
					[
						"content" =>
						[
							"kind"          => "components.buttons.button", 
							"label"			=> "<span class=\"icon-search\"></span>",
							"classEx"	   	=> "follow-btn detail",
							"variant" 		=> "secondary",
							"attributeEx"  	=> "alt=\"Detalles\" title=\"Detalles\" type=\"button\" data-toggle=\"modal\" data-target=\"#myModal\"  data-folio=\"".$request->folio."\" "
						],
					]
				];

				$modelBody[] = $body;
			}
		@endphp
		@component("components.tables.table",[
			"modelHead" => $modelHead,
			"modelBody" => $modelBody,
		])
		@endcomponent
		{{ $requests->appends($_GET)->links() }}

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

					],
					[
						"identificator"			=> ".js-status",
						"placeholder"			=> "Seleccione un estado de solicitud",
						"languaje"				=> "es",
					],
					[
						"identificator"			=> ".js-status-bill",
						"placeholder"			=> "Seleccione un estado de factura",
						"languaje"				=> "es",
						"maximumSelectionLength" => "1",
					]
				]);
			@endphp
			@component("components.scripts.selects",["selects" => $selects]) @endcomponent
			generalSelect({'selector':'[name="account"]','depends': '[name="enterpriseid"]','model': 10});

			$(document).on('click','[data-toggle="modal"]', function()
			{
				folio = $(this).attr('data-folio');
				$.ajax(
				{
					type : 'get',
					url  : '{{ route("report.purchase.detail") }}',
					data : {'folio':folio},
					success : function(data)
					{
						$('.modal-body').html(data);
						$('.detail').attr('disabled','disabled');
					},
					error: function()
					{
						swal('','Sucedió un error, por favor intente de nuevo.','error');
						$('#myModal').modal('hide');
					}
				})
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
			.on('click','.close, .exit',function()
			{
				$('.detail').removeAttr('disabled');
				$('#myModal').modal('hide');
			});
		});
	</script> 
@endsection
