@extends('layouts.child_module')

@section('css')
	<style type="text/css">
		svg
		{
			fill: currentColor;
			width: 1.4em;
		}
	</style>
@endsection

@section('data')
	@component("components.labels.title-divisor") BUSCAR SOLICITUDES @endcomponent
	@php
		$values = ["folio" => $folio, "minDate" => $mindate, "maxDate" => $maxdate];
		$hidden = ['enterprise','name'];
	@endphp
	@component("components.forms.searchForm",["attributeEx" => "id=\"formsearch\"","values" => $values, "hidden" => $hidden])
		@slot("contentEx")
			<div class="col-span-2">
				@component('components.labels.label')Título:@endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type        = "text" 
						name        = "title" 
						id          = "title" 
						placeholder = "Ingrese un título" 
						value       = "{{ isset($title) ? $title : '' }}"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Empleado: @endcomponent
				@php
					$options = collect();
					if (isset($employee))
					{
						foreach(App\RealEmployee::whereIn('id',$employee)->get() as $emp)
						{
							$description = $emp->fullName();
							if (isset($employee) && in_array($emp->id,$employee))
							{
								$options = $options->concat([["value"=>$emp->id, "selected"=>"selected", "description"=>$description]]);
							}
						}
					}
					$attributeEx	= "name=\"employee[]\" title=\"Empleado\" multiple=\"multiple\"";
					$classEx		= "js-employee";
				@endphp
				@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Empresa: @endcomponent
				@php
					$options = collect();
					foreach(App\Enterprise::where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeEnt($option_id)->pluck('enterprise_id'))->orderBy('name','asc')->get() as $ent)
					{
						$description = strlen($ent->name) >= 35 ? substr(strip_tags($ent->name),0,35)."..." : $ent->name;
						if (isset($enterprise) && in_array($ent->id,$enterprise))
						{
							$options = $options->concat([["value"=>$ent->id, "selected"=>"selected", "description"=>$description]]);
						}
						else
						{
							$options = $options->concat([["value"=>$ent->id, "description"=>$description]]);
						}
					}
					$attributeEx	= "name=\"enterprise[]\" title=\"Empresa\" multiple=\"multiple\"";
					$classEx		= "js-enterprise";
				@endphp
				@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Proyecto: @endcomponent
				@php
					$options = collect();
					if (isset($project)) 
					{
						foreach(App\Project::whereIn('idproyect',$project)->orderName()->get() as $p)
						{
							$options = $options->concat([["value"=>$p->idproyect, "selected"=>"selected", "description"=>$p->proyectName]]);
						}
					}
					$attributeEx	= "name=\"project[]\" title=\"Proyecto\" multiple=\"multiple\"";
					$classEx		= "js-project";
				@endphp
				@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])@endcomponent
			</div>
			<div class="col-span-2">
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
				@component("components.labels.label") Subdepartamento: @endcomponent
				@php
					$options = collect();
					if (isset($subdepartment)) 
					{
						foreach(App\Subdepartment::where('status',1)->whereIn('id',$subdepartment)->orderBy('name','asc')->get() as $subDep)
						{
							$options = $options->concat([["value"=>$subDep->id, "selected"=>"selected", "description"=>$subDep->name]]);
						}
					}
					$attributeEx	= "name=\"subdepartment[]\" title=\"Subdepartamento\" multiple=\"multiple\"";
					$classEx		= "js-subdepartment";
				@endphp
				@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Tipo de Nómina:@endcomponent
				@php
					$options = collect();
					foreach(App\CatTypePayroll::orderName()->get() as $t)
					{
						$description = $t->description;
						if(isset($type) && in_array($t->id, $type))
						{
							$options = $options->concat([["value"=>$t->id, "selected"=>"selected", "description"=>$description]]);
						}
						else
						{
							$options = $options->concat([["value"=>$t->id, "description"=>$description]]);
						}
					}
					$attributeEx	= "title=\"Tipo de Nómina\" multiple=\"multiple\" name=\"type[]\"";
					$classEx		= "js-typepayroll";
				@endphp
				@component('components.inputs.select', ["options" => $options, 'attributeEx' => $attributeEx, 'classEx' => $classEx])@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Fiscal/No Fiscal:@endcomponent
				@php
					$options = collect();
					if(isset($fiscal) && in_array("1", $fiscal))
					{
						$options = $options->concat([["value"=>"1", "selected"=>"selected", "description"=> "Fiscal"]]);
					}
					else
					{
						$options = $options->concat([["value"=>"1", "description"=> "Fiscal"]]);
					}

					if(isset($fiscal) && in_array("0", $fiscal))
					{
						$options = $options->concat([["value"=>"0", "selected"=>"selected", "description"=> "No Fiscal"]]);
					}
					else
					{
						$options = $options->concat([["value"=>"0", "description"=> "No Fiscal"]]);
					}

					$attributeEx = "title=\"Fiscal/No fiscal\" name=\"fiscal[]\" multiple=\"multiple\"";
					$classEx     = "js-fiscal";
				@endphp
				@component('components.inputs.select', ['attributeEx' => $attributeEx,'classEx' => $classEx, "options" => $options]) @endcomponent
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
		@endslot
		@if (count($nominaEmployee) > 0)
			@slot("export")
				<div class="float-right">
					@component('components.labels.label')
						@component('components.buttons.button',['variant' => 'success'])
							@slot('attributeEx') 
								type=submit 
								formaction={{ route('report.nomina-employee.disbursement.subdepartment') }} @endslot
							@slot('label')
								<span>Erogaciones por Subdepartamento</span><span class="icon-file-excel"></span> 
							@endslot
						@endcomponent
					@endcomponent
				</div>
				<div class="float-right">
					@component('components.labels.label')
						@component('components.buttons.button',['variant' => 'success'])
							@slot('attributeEx') 
								type=submit 
								formaction={{ route('report.nomina-employee.disbursement.wbs') }} @endslot
							@slot('label')
								<span>Erogaciones por WBS</span><span class="icon-file-excel"></span> 
							@endslot
						@endcomponent
					@endcomponent
				</div>
				<div class="float-right">
					@component('components.labels.label')
						@component('components.buttons.button',['variant' => 'success'])
							@slot('attributeEx') 
								type=submit 
								formaction={{ route('report.nomina-employee.excel') }} @endslot
							@slot('label')
								<span>Exportar a Excel</span><span class="icon-file-excel"></span> 
							@endslot
						@endcomponent
					@endcomponent
				</div>
			@endslot
		@endif
	@endcomponent

	@if(count($nominaEmployee) > 0)
		@php
			$cfdi 		= "hidden";
			$receipt 	= "hidden";
			$body		= [];
			$modelBody	= [];
			$modelHead	= 
			[
				[
					["value"	=> "Folio"],
					["value"	=> "Título"],
					["value"	=> "Empleado"],
					["value"	=> "Empresa"],
					["value"	=> "Proyecto"],
					["value"	=> "Tipo - Categoría"],
					["value"	=> "Acción"]
				]
			];

			foreach($nominaEmployee as $nom)
			{
				$body = 
				[
					[
						"content" =>
						[
							"label" => $nom->folio
						]
					],
					[
						"content" =>
						[
							"label" =>  htmlentities($nom->title),
						]
					],
					[
						"content" =>
						[
							"label" =>  $nom->employee->first()->fullName()
						]
					],

					[
						"content" =>
						[
							"label" => $nom->workerData->first()->enterprises()->exists() ? $nom->workerData->first()->enterprises->name : ''
						]
					],
					[
						"content" =>
						[
							"label" => $nom->workerData->first()->projects()->exists() ? $nom->workerData->first()->projects->proyectName : ''
						]
					],
					[
						"content" =>
						[
							"label" =>  App\CatTypePayroll::find($nom->idCatTypePayroll)->description." - ".($nom->idDepartment == 4 ? 'Administrativa' : 'Obra')." ".$nom->typeNomina(),
						]
					],
					[
						"content" =>
						[
							[
								"kind"			=> "components.buttons.button",
								"buttonElement"	=> "button", 
								"label"			=> "<span class=\"icon-search\"></span>",
								"classEx"		=> "follow-btn detail",
								"variant"		=> "secondary",
								"attributeEx"	=> "alt=\"Detalles\" title=\"Detalles\" type=\"button\" data-toggle=\"modal\" data-target=\"#myModal\""
							],
							[
								"kind"			=> "components.inputs.input-text",
								"attributeEx"	=> "type=\"hidden\" value=\"".$nom->idnominaEmployee."\"",
								"classEx"		=> "idnominaEmployee",
							],
							[
								"kind"			=> "components.inputs.input-text",
								"attributeEx"	=> "type=\"hidden\" value=\"".$nom->idnomina."\"",
								"classEx"		=> "idnomina",
							],
						]
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
		{{ $nominaEmployee->appends($_GET)->links() }}

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
	<div id="myModal" class="modal"></div>
@endsection

@section('scripts')
	<script src="{{ asset('js/jquery-ui.js') }}"></script>
	<script src="{{ asset('js/jquery.numeric.js') }}"></script>
	<script type="text/javascript"> 
		$(document).ready(function()
		{
			@php
				$selects = collect([
					[
						"identificator"			=> ".js-status",
						"placeholder"			=> "Seleccione un estado de solicitud",
						"languaje"				=> "es",
					],
					[
						"identificator"			=> ".js-typepayroll",
						"placeholder"			=> "Seleccione un tipo",
						"languaje"				=> "es",

					],
					[
						"identificator"			=> ".js-fiscal",
						"placeholder"			=> "Seleccione un tipo",
						"languaje"				=> "es",

					],
					[
						"identificator"			=> ".js-enterprise",
						"placeholder"			=> "Seleccione una empresa",
						"languaje"				=> "es",

					]
				]);
			@endphp
			@component("components.scripts.selects",["selects" => $selects]) @endcomponent
			generalSelect({'selector':'[name="employee[]"]','model': 20, 'maxSelection': -1});
			generalSelect({'selector':'[name="project[]"]','model': 21, 'maxSelection': -1 });
			generalSelect({'selector':'[name="subdepartment[]"]','model': 39, 'maxSelection': -1 });
			generalSelect({'selector':'[name="wbs[]"]','depends': '[name="project[]"]','model': 1, 'maxSelection': -1});

			$(document).on('click','[data-toggle="modal"]', function()
			{
				idnominaEmployee	= $(this).parents('.tr').find('.idnominaEmployee').val();
				idnomina			= $(this).parents('.tr').find('.idnomina').val();
				$.ajax(
				{
					type : 'get',
					url  : '{{ route("report.nomina-employee.detail") }}',
					data : {
						'idnominaEmployee'	:idnominaEmployee,
						'idnomina'			:idnomina,
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
				$('#detail').slideUp();
				$('#myModal').hide();
			})
		});
	</script>
@endsection


