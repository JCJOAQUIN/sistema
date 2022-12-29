@extends('layouts.child_module')
@section('data')
	@component("components.labels.title-divisor") BUSCAR SOLICITUDES @endcomponent
	@php
		$values = ["folio" => $folio, "minDate" => $mindate, "maxDate" => $maxdate];
		$hidden = ['name','enterprise'];
	@endphp
	@component("components.forms.searchForm",["attributeEx" => "id=\"formsearch\"","values" => $values, "hidden" => $hidden])
		@slot("contentEx")
			<div class="col-span-2">
				@component('components.labels.label')Título:@endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type        = "text" 
						name        = "title_request" 
						id          = "input-search" 
						placeholder = "Ingrese un título" 
						value       = "{{ isset($title_request) ? $title_request : '' }}"
					@endslot
				@endcomponent
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
		@endslot
		@if (count($requests) > 0)
			@slot("export")
				<div class="float-right">
					@component('components.labels.label')
						@component('components.buttons.button',['variant' => 'success'])
							@slot('attributeEx') 
								type=submit 
								formaction={{ route('report.isr.excel') }} @endslot
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
					["value"	=> "Empleado"],
					["value"	=> "Empresa"],
					["value"	=> "Periodo"],
					["value"	=> "Acción"]
				]
			];

			foreach($requests as $req)
			{
				$body = 
				[
					[
						"content" =>
						[
							"label" => $req->folio
						]
					],
					[
						"content" =>
						[
							"label" => htmlentities($req->title),
						]
					],
					[
						"content" =>
						[
							"label" => $req->name_emp." ".$req->last_name_emp." ".$req->scnd_last_name_emp
						]
					],

					[
						"content" =>
						[
							"label" => $req->enterpriseName,
						]
					],
					[
						"content" =>
						[
							"label" =>  $req->from_date." - ".$req->to_date
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
								"attributeEx" => "type=\"hidden\" value=\"".$req->idnominaEmployee."\"",
								"classEx"     => "idnominaEmployee",
							],
							[
								"kind"        => "components.inputs.input-text",
								"attributeEx" => "type=\"hidden\" value=\"".$req->idnomina."\"",
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
						"identificator"			=> ".js-employee",
						"placeholder"			=> "Seleccione un empleado",
						"languaje"				=> "es",

					]
				]);
			@endphp
			@component("components.scripts.selects",["selects" => $selects]) @endcomponent
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
