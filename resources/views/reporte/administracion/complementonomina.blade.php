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
					@component("components.labels.label") Empresa: @endcomponent
					@php
						$options = collect();
						foreach(App\Enterprise::where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeEnt($option_id)->pluck('enterprise_id'))->orderBy('name','asc')->get() as $enterprise)
						{
							$description = strlen($enterprise->name) >= 35 ? substr(strip_tags($enterprise->name),0,35)."..." : $enterprise->name;
							if (isset($idEnterprise) && in_array($enterprise->id,$idEnterprise))
							{
								$options = $options->concat([["value"=>$enterprise->id, "selected"=>"selected", "description"=>$description]]);
							}
							else
							{
								$options = $options->concat([["value"=>$enterprise->id, "description"=>$description]]);
							}
						}
						$attributeEx	= "name=\"idEnterprise[]\" title=\"Empresa\" multiple=\"multiple\"";
						$classEx		= "js-enterprise";
					@endphp
					@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])@endcomponent
				</div>
				<div class="col-span-2">
					@component("components.labels.label") Dirección: @endcomponent
					@php
						$options = collect();
						foreach (App\Area::orderName()->where('status','ACTIVE')->get() as $area) 
						{
							$description = $area->name;	
							if (isset($idArea) && in_array($area->id,$idArea))
							{
								$options = $options->concat([["value"=>$area->id, "selected"=>"selected", "description"=>$description]]);
							}
							else
							{
								$options = $options->concat([["value"=>$area->id,"description"=>$description]]);
							}
						}
						$attributeEx	= "name=\"idArea[]\" title=\"Dirección\" multiple=\"multiple\"";
						$classEx		= "js-area";
					@endphp
					@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])@endcomponent
				</div>
				<div class="col-span-2">
					@component("components.labels.label") Departamento: @endcomponent
					@php
						$options = collect();
							foreach (App\Department::where('status', 'ACTIVE')->whereIn('id',Auth::user()->inChargeDep($option_id)->pluck('departament_id'))->orderBy('name','asc')->get() as $department) 
							{
								$description = $department->name;	
								if (isset($idDepartment) && in_array($department->id,$idDepartment))
								{
									$options = $options->concat([["value"=>$department->id, "selected"=>"selected", "description"=>$description]]);
								}
								else
								{
									$options = $options->concat([["value"=>$department->id,"description"=>$description]]);
								}
							}
						$attributeEx = "name=\"idDepartment[]\" title=\"Departamento\" multiple=\"multiple\"";
						$classEx = "js-department";
					@endphp
					@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])@endcomponent
				</div>
				<div class="col-span-2">
					@component("components.labels.label") Cuenta: @endcomponent
					@php
						$options = collect();
						if(isset($idEnterprise))
						{
							foreach (App\Account::where('idEnterprise',$idEnterprise)->where('selectable',1)->get() as $acc) 
							{
								$description = $acc->fullClasificacionName();	
								if (isset($account) && $acc->idAccAcc == $account)
								{
									$options = $options->concat([["value"=>$acc->idAccAcc, "selected"=>"selected", "description"=>$description]]);
								}
							}
						}
						$attributeEx = "name=\"account\" title=\"Cuenta\" multiple=\"multiple\"";
						$classEx = "js-accounts";
					@endphp
					@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])@endcomponent
				</div>
				<div class="col-span-2">
					@component("components.labels.label") Estado: @endcomponent
					@php
						$options = collect();
							foreach (App\StatusRequest::whereIn('idrequestStatus',[4,5,10,11,12])->orderBy('description','asc')->get() as $s) 
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
			@endslot
			@if (count($requests) > 0)
				@slot("export")
					<div class="float-right">
						@component('components.labels.label')
							@component('components.buttons.button',['variant' => 'success'])
								@slot('attributeEx') 
									type=submit 
									formaction={{ route('report.payroll.excel') }} @endslot
								@slot('label')
									<span>Exportar a Excel (agrupado)</span><span class="icon-file-excel"></span> 
								@endslot
							@endcomponent
						@endcomponent
					</div>
					<div class="float-right">
						@component('components.labels.label')
							@component('components.buttons.button',['variant' => 'success'])
								@slot('attributeEx') 
									type=submit 
									formaction={{ route('report.payroll.excelwg') }} @endslot
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
						["value"	=> "Estado"],
						["value"	=> "Solicitante"],
						["value"	=> "Empresa"],
						["value"	=> "Dirección"],
						["value"	=> "Departamento"],
						["value"	=> "Clasificación del gasto"],
						["value"	=> "Fecha"],
						["value"	=> "Importe"],
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
								"label" => $request->folio,
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
								"label" => $request->requestUser()->exists() ? $request->requestUser->fullName() : "No hay solicitante",
							]
						],
						[
							"content" =>
							[
								"label" => "Varias",
							]
						],
						[
							"content" =>
							[
								"label" => "Varios",
							]
						],
						[
							"content" =>
							[
								"label" => "Varios",
							]
						],
						[
							"content" =>
							[
								"label" => "Varias",
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
								"label" => "$".number_format($request->nominas->first()->amount,2)
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

			@component("components.modals.modal",[ "variant" => "xl" ])
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
					],
					[
						"identificator"			=> ".js-area",
						"placeholder"			=> "Seleccione la dirección",
						"languaje"				=> "es",
					],
					[
						"identificator"			=> ".js-department",
						"placeholder"			=> "Seleccione el departamento",
						"languaje"				=> "es",
					],
					[
						"identificator"			=> ".js-accounts",
						"placeholder"			=> "Seleccione la cuenta",
						"languaje"				=> "es",
					],
					[
						"identificator"			=> ".js-status",
						"placeholder"			=> "Seleccione un estado de solicitud",
						"languaje"				=> "es",
					]
				]);
			@endphp

			@component("components.scripts.selects",["selects" => $selects]) @endcomponent

			generalSelect({'selector': '.js-accounts', 'depends': '.js-enterprise', 'model': 10});
			$(document).on('click','[data-toggle="modal"]', function()
			{
				folio = $(this).attr('data-folio');
				$.ajax(
				{
					type : 'get',
					url  : '{{ route('report.payroll.detail') }}',
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
			.on('click','.close, .exit',function()
			{
				$('.detail').removeAttr('disabled');
				$('#myModal').modal('hide');
			})
			.on('change','.js-enterprise',function(e)
			{
				$('.js-accounts').empty();
			});
		});
	</script> 
@endsection
