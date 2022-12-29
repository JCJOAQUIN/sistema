@extends('layouts.child_module')
@section('data')
	@component("components.labels.title-divisor") BUSCAR SOLICITUDES @endcomponent
	@php
		$values	= ['folio'=>$folio,'name'=>$name];
		$hidden	= ['enterprise','folio','rangeDate'];
	@endphp
	@component("components.forms.searchForm",["attributeEx" => "id=\"formsearch\"","values" => $values, "hidden" => $hidden])
		@slot("contentEx")
			<div class="col-span-2">
				@component('components.labels.label')Título:@endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type        = "text" 
						name        = "title_search" 
						id          = "title" 
						placeholder = "Ingrese un título" 
						value       = "{{ isset($title_search) ? $title_search : '' }}"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Rango de fechas: @endcomponent
				@php
					$value_one ="";
					$value_two ="";					
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
								"input_attributeEx" => "name=\"mindate\" id=\"mindate\" step=\"1\" placeholder=\"Desde\" value=\"".$mindate."\"",
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

				@component("components.inputs.range-input",["inputs" => $inputs])
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Rango de fechas de revisión: @endcomponent
				@php
					$value_one ="";
					$value_two ="";					
					if(isset($mindate_review) && isset($maxdate_review))
					{ 
						$inputs= [
							[
								"input_classEx" => "input-text-date datepicker",
								"input_attributeEx" => "name=\"mindate_review\" id=\"mindate_review\" step=\"1\" placeholder=\"Desde\" value=\"".$mindate_review."\"",
							],
							[
								"input_classEx" => "input-text-date datepicker",
								"input_attributeEx" => "name=\"maxdate_review\" id=\"maxdate_review\" step=\"1\" placeholder=\"Hasta\" value=\"".$maxdate_review."\"",
							]
						];
					}
					else if(!isset($mindate_review) && isset($maxdate_review))
					{
						$inputs= [
							[
								"input_classEx" => "input-text-date datepicker",
								"input_attributeEx" => "name=\"mindate_review\" id=\"mindate_review\" step=\"1\" placeholder=\"Desde\"",
							],
							[
								"input_classEx" => "input-text-date datepicker",
								"input_attributeEx" => "name=\"maxdate_review\" id=\"maxdate_review\" step=\"1\" placeholder=\"Hasta\" value=\"".$maxdate_review."\"",
							]
						];
					}
					else if(isset($mindate_review) && !isset($maxdate_review))
					{
						$inputs= [
							[
								"input_classEx" => "input-text-date datepicker",
								"input_attributeEx" => "name=\"mindate_review\" id=\"mindate_review\" step=\"1\" placeholder=\"Desde\" value=\"".$mindate_review."\"",
							],
							[
								"input_classEx" => "input-text-date datepicker",
								"input_attributeEx" => "name=\"maxdate_review\" id=\"maxdate_review\" step=\"1\" placeholder=\"Hasta\"",
							]
						];
					}
					else
					{
						$inputs= [
							[
								"input_classEx" => "input-text-date datepicker",
								"input_attributeEx" => "name=\"mindate_review\" id=\"mindate_review\" step=\"1\" placeholder=\"Desde\"",
							],
							[
								"input_classEx" => "input-text-date datepicker",
								"input_attributeEx" => "name=\"maxdate_review\" id=\"maxdate_review\" step=\"1\" placeholder=\"Hasta\"",
							]
						];
					}
				@endphp

				@component("components.inputs.range-input",["inputs" => $inputs])
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Rango de fechas de autorización: @endcomponent
				@php
					$value_one ="";
					$value_two ="";					
					if(isset($mindate_authorize) && isset($maxdate_authorize))
					{ 
						$inputs= [
							[
								"input_classEx" => "input-text-date datepicker",
								"input_attributeEx" => "name=\"mindate_authorize\" id=\"mindate_authorize\" step=\"1\" placeholder=\"Desde\" value=\"".$mindate_authorize."\"",
							],
							[
								"input_classEx" => "input-text-date datepicker",
								"input_attributeEx" => "name=\"maxdate_authorize\" id=\"maxdate_authorize\" step=\"1\" placeholder=\"Hasta\" value=\"".$maxdate_authorize."\"",
							]
						];
					}
					else if(!isset($mindate_authorize) && isset($maxdate_authorize))
					{
						$inputs= [
							[
								"input_classEx" => "input-text-date datepicker",
								"input_attributeEx" => "name=\"mindate_authorize\" id=\"mindate_authorize\" step=\"1\" placeholder=\"Desde\"",
							],
							[
								"input_classEx" => "input-text-date datepicker",
								"input_attributeEx" => "name=\"maxdate_authorize\" id=\"maxdate_authorize\" step=\"1\" placeholder=\"Hasta\" value=\"".$maxdate_authorize."\"",
							]
						];
					}
					else if(isset($mindate_authorize) && !isset($maxdate_authorize))
					{
						$inputs= [
							[
								"input_classEx" => "input-text-date datepicker",
								"input_attributeEx" => "name=\"mindate_authorize\" id=\"mindate_authorize\" step=\"1\" placeholder=\"Desde\" value=\"".$mindate_authorize."\"",
							],
							[
								"input_classEx" => "input-text-date datepicker",
								"input_attributeEx" => "name=\"maxdate_authorize\" id=\"maxdate_authorize\" step=\"1\" placeholder=\"Hasta\"",
							]
						];
					}
					else
					{
						$inputs= [
							[
								"input_classEx" => "input-text-date datepicker",
								"input_attributeEx" => "name=\"mindate_authorize\" id=\"mindate_authorize\" step=\"1\" placeholder=\"Desde\"",
							],
							[
								"input_classEx" => "input-text-date datepicker",
								"input_attributeEx" => "name=\"maxdate_authorize\" id=\"maxdate_authorize\" step=\"1\" placeholder=\"Hasta\"",
							]
						];
					}
				@endphp

				@component("components.inputs.range-input",["inputs" => $inputs])
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label')Tipo de Solicitud:@endcomponent
				@php
					$options = collect();
					foreach(App\RequestKind::whereIn('idrequestkind',[1,2,3,8,9,11,12,13,14,15,16,17])->orderBy('kind','ASC')->get() as $k) 
					{
						$description = $k->kind;
						if(isset($kind) && in_array($k->idrequestkind, $kind))
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
						'attributeEx' => "title=\"Tipo de Solicitud\" multiple=\"multiple\" name=\"kind[]\"", 
						'classEx'     => "js-kind removeselect", 
						"options"     => $options
					]
				)
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Empresa: @endcomponent
				@php
					$options = collect();
					foreach(App\Enterprise::where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeEnt($option_id)->pluck('enterprise_id'))->orderBy('name','asc')->get() as $e)
					{
						$description = strlen($e->name) >= 35 ? substr(strip_tags($e->name),0,35)."..." : $e->name;
						if(isset($enterprise) && in_array($e->id, $enterprise))
						{
							$options = $options->concat([["value"=>$e->id, "selected"=>"selected", "description"=>$description]]);
						}
						else
						{
							$options = $options->concat([["value"=>$e->id, "description"=>$description]]);
						}
					}
					$attributeEx	= "name=\"enterprise[]\" title=\"Empresa\" multiple=\"multiple\"";
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
						if(isset($direction) && in_array($area->id, $direction))
						{
							$options = $options->concat([["value"=>$area->id, "selected"=>"selected", "description"=>$description]]);
						}
						else
						{
							$options = $options->concat([["value"=>$area->id, "description"=>$description]]);
						}
					}
					$attributeEx	= "name=\"direction[]\" title=\"Dirección\" multiple=\"multiple\"";
					$classEx		= "js-direction";
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
						if(isset($department) && in_array($d->id, $department))
						{
							$options = $options->concat([["value"=>$d->id, "selected"=>"selected", "description"=>$description]]);
						}
						else
						{
							$options = $options->concat([["value"=>$d->id, "description"=>$description]]);
						}
					}
					$attributeEx	= "name=\"department[]\" title=\"Departamento\" multiple=\"multiple\"";
					$classEx		= "js-department";
				@endphp
				@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Proyecto: @endcomponent
				@php
					$options = collect();
					if (isset($project)) 
					{
						foreach(App\Project::whereIn('idproyect',Auth::user()->inChargeProject($option_id)->pluck('project_id'))->whereIn('idproyect',$project)->orderName()->get() as $p)
						{
							$options = $options->concat([["value"=>$p->idproyect, "selected"=>"selected", "description"=>$p->proyectName]]);
						}
					}
					$attributeEx	= "name=\"project[]\" title=\"Proyecto\" multiple=\"multiple\"";
					$classEx		= "js-project";
				@endphp
				@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])@endcomponent
			</div>
			<div class="col-span-2 wbs-section">
				@component("components.labels.label") Código WBS: @endcomponent
				@php
					$options = collect();
					if (isset($project) && isset($wbs)) 
					{
						foreach(App\CatCodeWBS::whereIn('project_id',$project)->get() as $w)
						{
							$description = $w->code_wbs;
							if(isset($wbs) && in_array($w->id, $wbs))
							{
								$options = $options->concat([["value"=>$w->id, "selected"=>"selected", "description"=>$description]]);
							}
						}
					}
					$attributeEx	= "name=\"wbs[]\" title=\"Código WBS\" multiple=\"multiple\"";
					$classEx		= "js-wbs";
				@endphp
				@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Estado de Solicitud: @endcomponent
				@php
					$options = collect();
					foreach(App\StatusRequest::orderName()->whereIn('idrequestStatus',[4,5,6,7,10,11,12,13,18])->get() as $s)
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
								formaction={{ route('report.expenses.request.excelwg') }} @endslot
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
					["value" => "Folio"],
					["value" => "Estado"],
					["value" => "Tipo de solicitud"],
					["value" => "Solicitante"],
					["value" => "Empresa"],
					["value" => "Dirección"],
					["value" => "Departamento"],
					["value" => "Fecha"],
					["value" => "Importe"],
					["value" => "Acción"]
				]
			];

			foreach($requests as $request)
			{
				$subtotalFinal = $ivaFinal = $totalFinal = $taxes = 0;
				switch ($request->kind) 
				{
					case 1:
						$totalFinal = $request->purchases->first()->amount;
						break;

					case 2:
						$totalFinal = $request->nominas->first()->amount;
						break;

					case 3:
						$totalFinal = $request->expenses->first()->total;
						break;

					case 8:
						$totalFinal = $request->resource->first()->total;
						break;

					case 9:
						$totalFinal = $request->refunds->first()->total;
						break;

					case 11:
						$totalFinal = $request->adjustment->first()->amount;
						break;

					case 12:
						$totalFinal = $request->loanEnterprise->first()->amount;
						break;

					case 13:
						$totalFinal = $request->purchaseEnterprise->first()->amount;
						break;

					case 14:
						if ($request->groups->first()->operationType == 'Salida') 
						{
							$totalFinal = -$request->groups->first()->amount;
						}
						else
						{
							$totalFinal = $request->groups->first()->amount;
						}
						break;

					case 15:
						$totalFinal = $request->movementsEnterprise->first()->amount;
						break;

					case 16:
						$totalFinal = $request->nominasReal->first()->amount;
						break;

					case 17:
						$totalFinal = $request->purchaseRecord->total;
						break;
					
					default:
						# code...
						break;
				}
				$varias			= 'Varias';
				$enterpriseName	=  $request->reviewedEnterprise()->exists() ? $request->reviewedEnterprise->name : $varias;
				$directionName	=  $request->reviewedDirection()->exists() ? $request->reviewedDirection->name : $varias;
				$departmentName	= $request->reviewedDepartment()->exists() ? $request->reviewedDepartment->name : $varias;

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
							"label" =>  $request->statusrequest->description
						]
					],
					[
						"content" =>
						[
							"label" =>$request->requestkind->kind
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
							"label" => $enterpriseName
						]
					],
					[
						"content" =>
						[
							"label" => $directionName
						]
					],
					[
						"content" =>
						[
							"label" => $departmentName
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
							"label" => "$".number_format($totalFinal,2)
						]
					],
					[
						"content" =>
						[
							"kind"          => "components.buttons.button",
							"buttonElement" => "button", 
							"label"			=> "<span class=\"icon-search\"></span>",
							"classEx"	   	=> "follow-btn detail",
							"variant" 		=> "secondary",
							"attributeEx"  	=> "alt=\"Detalles\" title=\"Detalles\" type=\"button\" data-toggle=\"modal\" data-target=\"#myModal\"  data-folio=\"".$request->folio."\" "
						],
						[
							"kind"        => "components.inputs.input-text",
							"attributeEx" => "type=\"hidden\" value=\"".$request->folio."\"",
							"classEx"     => "folio",
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
					],
					[
						"identificator"			=> ".js-direction",
						"placeholder"			=> "Seleccione una dirección",
						"languaje"				=> "es",

					],
					[
						"identificator"			=> ".js-department",
						"placeholder"			=> "Seleccione un departamento",
						"languaje"				=> "es",

					],
					[
						"identificator"			=> ".js-kind",
						"placeholder"			=> "Seleccione un tipo de solicitud",
						"languaje"				=> "es",

					],
					[
						"identificator"			=> ".js-status",
						"placeholder"			=> "Seleccione un estado de solicitud",
						"languaje"				=> "es",
					],
					[
						"identificator"			=> ".js-wbs",
						"placeholder"			=> "Seleccione un WBS",
						"languaje"				=> "es",
						"maximumSelectionLength" => "1",
					]
				]);
			@endphp
			@component("components.scripts.selects",["selects" => $selects]) @endcomponent
			generalSelect({'selector':'[name="project[]"]','model': 17,'option_id': {{$option_id}} });
			generalSelect({'selector':'[name="wbs[]"]','depends': '[name="project[]"]','model': 22});

			$(document).on('click','[data-toggle="modal"]', function()
			{
				folio = $(this).attr('data-folio');
				$.ajax(
				{
					type : 'get',
					url  : '{{ route("report.expenses.request.detail") }}',
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
			.on('click','.close, .exit',function()
			{
				$('#myModal').modal('hide');
			});
		});
	</script> 
@endsection
