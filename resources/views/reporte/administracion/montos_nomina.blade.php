@extends('layouts.child_module')
@section('data')
	@component("components.labels.title-divisor") BUSCAR SOLICITUDES @endcomponent
	@php
		$values = ["minDate" => $mindate, "maxDate" => $maxdate];
		$hidden = ['name','enterprise','folio'];
	@endphp
	@component("components.forms.searchForm",["attributeEx" => "id=\"formsearch\"","values" => $values, "hidden" => $hidden])
		@slot("contentEx")
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
					foreach(App\Project::whereIn('idproyect',Auth::user()->inChargeProject($option_id)->pluck('project_id'))->orderName()->get() as $p)
					{
						$description = $p->proyectName;
						if(isset($project) && in_array($p->idproyect, $project))
						{
							$options = $options->concat([["value"=>$p->idproyect, "selected"=>"selected", "description"=>$description]]);
						}
					}
					$attributeEx	= "name=\"project[]\" title=\"Proyecto\" multiple=\"multiple\"";
					$classEx		= "js-project";
				@endphp
				@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label')Tipo de Nómina:@endcomponent
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
					$classEx		= "js-type";
				@endphp
				@component('components.inputs.select', ["options" => $options, 'attributeEx' => $attributeEx, 'classEx' => $classEx])@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label')Fiscal/No Fiscal:@endcomponent
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

					if(isset($fiscal) && in_array("2", $fiscal))
					{
						$options = $options->concat([["value"=>"2", "selected"=>"selected", "description"=> "No Fiscal"]]);
					}
					else
					{
						$options = $options->concat([["value"=>"2", "description"=> "No Fiscal"]]);
					}

					if(isset($fiscal) && in_array("3", $fiscal))
					{
						$options = $options->concat([["value"=>"3", "selected"=>"selected", "description"=> "Nom 035"]]);
					}
					else
					{
						$options = $options->concat([["value"=>"3", "description"=> "Nom 035"]]);
					}

					$attributeEx = "title=\"Fiscal/No fiscal\" name=\"fiscal[]\" multiple=\"multiple\"";
					$classEx     = "js-fiscal";
				@endphp
				@component('components.inputs.select', ['attributeEx' => $attributeEx,'classEx' => $classEx, "options" => $options]) @endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label')Semana del año:@endcomponent
				@php
					$options = collect();
					for ($i=1; $i < 53; $i++)
					{
						if(isset($weekOfYear) && $i==$weekOfYear)
						{
							$options = $options->concat([["value"=>$i, "selected"=>"selected", "description"=>$i]]);
						}
						else
						{
							$options = $options->concat([["value"=>$i, "description"=>$i]]);
						}
					}
					$attributeEx	= "title=\"Tipo de Nómina\" multiple=\"multiple\" name=\"weekOfYear\"";
					$classEx		= "js-week";
				@endphp
				@component('components.inputs.select', ["options" => $options, 'attributeEx' => $attributeEx, 'classEx' => $classEx]) @endcomponent
			</div>
		@endslot
		@if (count($nominas) > 0)
			@slot("export")
				<div class="float-right">
					@component('components.labels.label')
						@component('components.buttons.button',['variant' => 'success'])
							@slot('attributeEx') 
								type=submit 
								formaction={{ route('report.payroll-amounts.export') }} @endslot
							@slot('label')
								<span>Exportar a Excel</span><span class="icon-file-excel"></span> 
							@endslot
						@endcomponent
					@endcomponent
				</div>
			@endslot
		@endif
	@endcomponent

	@if (count($nominas) > 0)
		@php
			$body		= [];
			$modelBody	= [];
			$modelHead	= 
			[
				[
					["value"	=> "Folio"],
					["value"	=> "Rango de Fecha"],
					["value"	=> "Empleado"],
					["value"	=> "Empresa"],
					["value"	=> "Proyecto"],
					["value"	=> "Tipo - Categoría"],
					["value"	=> "Acción"]
				]
			];

			foreach($nominas as $nom)
			{
				$date = ($nom->from_date != "" && $nom->to_date != "") ? Carbon\Carbon::createFromFormat('Y-m-d',$nom->from_date)->format('d-m-Y')." al ".Carbon\Carbon::createFromFormat('Y-m-d',$nom->to_date)->format('d-m-Y') : Carbon\Carbon::createFromFormat('Y-m-d',$nom->nomina->from_date)->format('d-m-Y')." al ".Carbon\Carbon::createFromFormat('Y-m-d',$nom->nomina->to_date)->format('d-m-Y');
				$body = 
				[
					[
						"content" =>
						[
							"label" => $nom->nomina->idFolio
						]
					],
					[
						"content" =>
						[
							"label" => $date,
						]
					],
					[
						"content" =>
						[
							"label" => $nom->employee->first()->fullName()
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
							"label" =>  $nom->workerData->first()->projects()->exists() ? $nom->workerData->first()->projects->proyectName : ''
						]
					],
					[
						"content" =>
						[
							"label" =>  $nom->nomina->typePayroll->description." -  ".$nom->category()." ".$nom->typeNomina()
						]
					],
					[
						"content" =>
						[
							[
								"kind"          => "components.buttons.button",
								"buttonElement" => "button", 
								"label"			=> "<span class=\"icon-search\"></span>",
								"classEx"	   	=> "follow-btn detail",
								"variant" 		=> "secondary",
								"attributeEx"  	=> "alt=\"Detalles\" title=\"Detalles\" type=\"button\" data-toggle=\"modal\" data-target=\"#myModal\""
							],
							[
								"kind"        => "components.inputs.input-text",
								"attributeEx" => "type=\"hidden\" value=\"".$nom->idnominaEmployee."\"",
								"classEx"     => "idnominaEmployee",
							],
							[
								"kind"        => "components.inputs.input-text",
								"attributeEx" => "type=\"hidden\" value=\"".$nom->idnomina."\"",
								"classEx"     => "idnomina",
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
		{{ $nominas->appends($_GET)->links() }}

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
						"identificator"			=> ".js-type",
						"placeholder"			=> "Seleccione una dirección",
						"languaje"				=> "es",

					],
					[
						"identificator"			=> ".js-fiscal",
						"placeholder"			=> "Seleccione un departamento",
						"languaje"				=> "es",

					],
					[
						"identificator"			=> ".js-week",
						"placeholder"			=> "Seleccione un tipo de solicitud",
						"languaje"				=> "es",

					]
				]);
			@endphp
			@component("components.scripts.selects",["selects" => $selects]) @endcomponent
			generalSelect({'selector':'[name="project[]"]','model': 21});
			generalSelect({'selector':'[name="employee[]"]','model': 20, 'maxSelection': -1});

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