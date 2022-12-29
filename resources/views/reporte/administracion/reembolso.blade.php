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
						if(isset($idEnterprise) && in_array($enterprise->id, $idEnterprise))
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
					foreach(App\Area::orderName()->where('status','ACTIVE')->get() as $area)
					{
						$description = $area->name;
						if(isset($idArea) && in_array($area->id, $idArea))
						{
							$options = $options->concat([["value"=>$area->id, "selected"=>"selected", "description"=>$description]]);
						}
						else
						{
							$options = $options->concat([["value"=>$area->id, "description"=>$description]]);
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
					foreach(App\Department::orderName()->where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeDep($option_id)->pluck('departament_id'))->get() as $d)
					{
						$description = $d->name;
						if(isset($idDepartment) && in_array($d->id, $idDepartment))
						{
							$options = $options->concat([["value"=>$d->id, "selected"=>"selected", "description"=>$description]]);
						}
						else
						{
							$options = $options->concat([["value"=>$d->id, "description"=>$description]]);
						}
					}
					$attributeEx	= "name=\"idDepartment[]\" title=\"Departamento\" multiple=\"multiple\"";
					$classEx		= "js-department";
				@endphp
				@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Estado de Solicitud: @endcomponent
				@php
					$options = collect();
					foreach (App\StatusRequest::orderName()->whereIn('idrequestStatus',[4,5,10,11,12,18])->get() as $s)
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
								formaction={{ route('report.refunds.excel') }} @endslot
							@slot('label')
								<span>Exportar a Excel</span><span class="icon-file-excel"></span> 
							@endslot
						@endcomponent
					@endcomponent
				</div>
				<div class="float-right">
					@component('components.labels.label')
						@component('components.buttons.button',['variant' => 'success'])
							@slot('attributeEx') 
								type=submit 
								formaction={{ route('report.refunds.excelwg') }} @endslot
							@slot('label')
								<span>Exportar sin agrupar</span><span class="icon-file-excel"></span> 
							@endslot
						@endcomponent
					@endcomponent
				</div>
			@endslot
		@endif
	@endcomponent

	@if(count($requests)>0)
		@php
			$body		= [];
			$modelBody	= [];
			$modelHead	= 
			[
				[
					["value"	=> "Folio"],
					["value"	=> "Título"],
					["value"	=> "Estado"],
					["value"	=> "Solicitante"],
					["value"	=> "Empresa"],
					["value"	=> "Dirección"],
					["value"	=> "Departamento"],
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
							"label" => $request->folio
						]
					],
					[
						"content" =>
						[
							"label" => htmlentities($request->refunds->first()->title),
						]
					],
					[
						"content" =>
						[
							"label" => $request->statusrequest->description
						]
					],
					[
						"content" =>
						[
							"label" => $request->requestUser->fullName()
						]
					],
					[
						"content" =>
						[
							"label" => $request->reviewedEnterprise->name
						]
					],
					[
						"content" =>
						[
							"label" => $request->reviewedDirection->name
						]
					],
					[
						"content" =>
						[
							"label" => $request->reviewedDepartment->name
						]
					],
					[
						"content" =>
						[				
							"label" => Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$request->fDate)->format('d-m-Y'),
						]
					],
					[
						"content" =>
						[
							"label" => "$".number_format($request->refunds->first()->total,2)
						]
					],
					[
						"content" =>
						[
							[
								"kind"			=> "components.buttons.button",
								"buttonElement"	=> "button", 
								"label"			=> "<span class=\"icon-search\"></span>",
								"classEx"		=> "follow-btn btn-detail",
								"variant"		=> "secondary",
								"attributeEx"	=> "alt=\"Detalles\" title=\"Detalles\" type=\"button\" data-toggle=\"modal\" data-target=\"#myModal\""
							],
							[
								"kind"			=> "components.inputs.input-text",
								"attributeEx"	=> "type=\"hidden\" value=\"".$request->folio."\"",
								"classEx"		=> "folio",
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
						"placeholder"			=> "Seleccione una dirección",
						"languaje"				=> "es",

					],
					[
						"identificator"			=> ".js-department",
						"placeholder"			=> "Seleccione un departamento",
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

			$(document).on('click','[data-toggle="modal"]', function()
			{
				folio = $(this).parents('.tr').find('.folio').val();
				$.ajax(
				{
					type : 'get',
					url  : '{{ route("report.refunds.detail") }}',
					data : {'folio':folio},
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
			});
		});
	</script>
@endsection