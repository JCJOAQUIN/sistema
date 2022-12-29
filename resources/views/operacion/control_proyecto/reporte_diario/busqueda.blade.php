@extends('layouts.child_module')  
@section('data')
	@component("components.labels.title-divisor") BUSCAR REPORTE @endcomponent
	@component("components.forms.searchForm",["variant" => "default", "attributeEx" => "id=\"formsearch\""])
		<div class="col-span-2">
			@component("components.labels.label") No. de Reporte: @endcomponent
			@component("components.inputs.input-text")
				@slot("attributeEx")
					name        = "num_report"
					id          = "input-search"
					placeholder = "Ingrese un número de reporte"
					value       = "{{ isset($num_report) ? $num_report : '' }}"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component("components.labels.label") Personal que elaboró: @endcomponent
			@component("components.inputs.input-text")
				@slot("attributeEx")
					name        = "name"
					placeholder = "Ingrese un nombre"
					value       = "{{ isset($name) ? $name : '' }}"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component("components.labels.label") Rango de fechas: @endcomponent
			@php			
				$inputs = [
					[
						"input_attributeEx" => "name=\"mindate\" id=\"mindate\" step=\"1\" placeholder=\"Desde\" value=\"".(isset($mindate) ? $mindate : "")."\"",
					],
					[
						"input_attributeEx" => "name=\"maxdate\" id=\"maxdate\" step=\"1\" placeholder=\"Hasta\" value=\"".(isset($maxdate) ? $maxdate : "")."\"",
					]
				];
			@endphp
			@component("components.inputs.range-input",["inputs" => $inputs]) @endcomponent
		</div>
		<div class="col-span-2">
			@component("components.labels.label") Proyecto: @endcomponent
			@php
				$options = collect();
				if(isset($project))
				{
					$projectSelected = App\Project::find($project);
					$options = $options->concat([["value" => $project, "selected" => "selected", "description" => $projectSelected->proyectName]]);
				}
				$attributeEx = "title=\"Proyecto\" name=\"project\" multiple=\"multiple\"";
				$classEx = "js-project removeselect";
			@endphp
			@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])
			@endcomponent
		</div>
		<div class="col-span-2">
			@component("components.labels.label") Contrato: @endcomponent
			@php
				$options = collect();
				if(isset($project) && isset($contract))
				{
					$contractSelected = App\Contract::find($contract);
					$options = $options->concat([["value" => $contract, "selected" => "selected", "description" => $contractSelected->number." - ".$contractSelected->name]]);
				}
				$attributeEx = "title=\"Contrato\" name=\"contract\" multiple=\"multiple\"";
				$classEx = "js-contract removeselect";
			@endphp
			@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])
			@endcomponent
		</div>
		<div class="col-span-2">
			@component("components.labels.label") Código WBS: @endcomponent
			@php
				$options = collect();
				if(isset($contract) && isset($code_wbs))
				{
					$wbsSelected = App\CatCodeWBS::find($code_wbs);
					$options = $options->concat([["value" => $code_wbs, "selected" => "selected", "description" => $wbsSelected->code_wbs]]);
				}
				$attributeEx = "title=\"Código WBS\" name=\"code_wbs\" multiple=\"multiple\"";
				$classEx = "js-code_wbs removeselect";
			@endphp
			@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])
			@endcomponent
		</div>
		<div class="col-span-2">
			@component("components.labels.label") Estatus: @endcomponent
			@php
				$statusSelect = [['id' => '1', 'status' => 'Abierto'],['id' => '0', 'status' => 'Cerrado']];
				$options = collect();
				foreach($statusSelect as $s)
				{
					if(isset($status) && $status == $s['id'])
					{
						$options = $options->concat([["value" => $s['id'], "selected" => "selected", "description" => $s['status']]]);
					}
					else 
					{
						$options = $options->concat([["value" => $s['id'], "description" => $s['status']]]);
					}
				}
				
				$attributeEx = "title=\"Estatus\" name=\"status\" multiple=\"multiple\"";
				$classEx = "js-status removeselect";
			@endphp
			@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])
			@endcomponent
		</div>
		@if(count($requests) > 0)
			@slot("export")
			<div class="text-right">				
				@component("components.buttons.button",["variant" => "success"])
					@slot("attributeEx") type="submit" formaction="{{ route('project-control.daily-report.export.follow') }}" @endslot
					@slot("slot") <span>Exportar a Excel</span><span class="icon-file-excel"></span> @endslot
				@endcomponent
			</div>
			@endslot
		@endif
	@endcomponent
	@if(count($requests) > 0)
		@php
			$modelHead = 
			[	
				[
					["value" => "ID"],
					["value" => "Fecha"],
					["value" => "WBS"],
					["value" => "Disciplina"],
					["value" => "Elaboró"],
					["value" => "No. Reporte"],
					["value" => "Paquete"],
					["value" => "Estatus"],
					["value" => "PDF"],
					["value" => "Acciones"]
				]
			];
			$modelBody = [];
			foreach($requests as $request)
			{
				$date	= Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $request->created_at)->format('d-m-Y H:i');
				$modelBody [] = [
					[
						"classEx" => "td",
						"content" => 
						[
							[
								"kind"  => "components.labels.label",
								"label" => $request->id,
							]
						],
					],
					[
						"classEx" => "td",
						"content" => 
						[
							[
								"kind"  => "components.labels.label",
								"label" => $date,
							]
						],
					],
					[
						"classEx" => "td",
						"content" => 
						[
							[
								"kind"  => "components.labels.label",
								"label" => $request->wbs->code,
							]
						],
					],
					[
						"classEx" => "td",
						"content" => 
						[
							[
								"kind"  => "components.labels.label",
								"label" => $request->discipline->name,
							]
						],
					],
					[
						"classEx" => "td",
						"content" => 
						[
							[
								"kind"  => "components.labels.label",
								"label" => $request->elaborateUser->fullName(),
							]
						],
					],
					[
						"classEx" => "td",
						"content" => 
						[
							[
								"kind"  => "components.labels.label",
								"label" => $request->noReport(),
							]
						],
					],
					[
						"classEx" => "td",
						"content" => 
						[
							[
								"kind"  => "components.labels.label",
								"label" => $request->package,
							]
						],
					],
					[
						"classEx" => "td",
						"content" => 
						[
							[
								"kind"  => "components.labels.label",
								"label" => ($request->status == 0 ? "CERRADO" : "ABIERTO"),
							]
						],
					],
					[
						"classEx" => "td",
						"content" => 
						[
							[
								"kind"        	=> "components.buttons.button", 
								"attributeEx" 	=> "alt=\"PDF\" title=\"PDF\" type=\"button\" href=\"".route('project-control.daily-report.pdf',$request->id)."\"",
								"buttonElement" => "a",
								"variant"     	=> "dark-red",
								"label"       	=> "PDF",
								"classEx"	  	=> "pdf",
							]
						],
					],
					[
						"classEx" => "td",
						"content" =>
						[
							[
								"kind"        	=> "components.buttons.button",
								"buttonElement" => "a",
								"attributeEx" 	=> "title=\"Editar Reporte\" alt=\"Editar Reporte\" type=\"button\" href=\"".route('project-control.daily-report.edit',$request->id)."\"",
								"classEx"     	=> "edit",
								"variant"     	=> "success",
								"label"       	=> "<span class=\"icon-pencil\"></span>",
							],
							[
								"kind"        => "components.buttons.button", 
								"attributeEx" => "alt=\"Eliminar Reporte\" title=\"Eliminar Reporte\" type=\"button\" formaction=\"".route('project-control.daily-report.delete',$request->id)."\"",
								"variant"     => "red",
								"label"       => "<span class='icon-bin'></span>",
								"classEx"	  => "delete",
							],
						],
					],
				];
			}
		@endphp
		@component("components.tables.table", ["modelHead" => $modelHead, "modelBody" => $modelBody]) @endcomponent
		{{ $requests->appends($_GET)->links() }}
	@else
		@component("components.labels.not-found") @endcomponent
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
						"identificator"    	     => ".js-status", 
						"placeholder"			 => "Seleccione el estatus",
						"maximumSelectionLength" => "1"
					],
				]);
			@endphp
			@component("components.scripts.selects", ["selects" => $selects]) @endcomponent
			generalSelect({'selector': '.js-project', 'model': 17, 'option_id':{{$option_id}}});
			generalSelect({'selector': '.js-contract', 'depends': '.js-project', 'model': 34});
			generalSelect({'selector': '.js-code_wbs', 'depends': '.js-contract', 'model': 30});
			$(document).on('click','.delete',function(e)
			{	
				e.preventDefault();
				url	= $(this).attr('formaction');
				swal({
					title		: "Confirmar",
					text		: "¿Desea eliminar el reporte?",
					icon		: "warning",
					buttons		: ["Cancelar","OK"],
					dangerMode	: true,
				})
				.then((continuar) =>
				{
					if(continuar)
					{
						swal({
							icon: '{{ asset(getenv("LOADING_IMG")) }}',
							button: false,
							closeOnClickOutside: false,
							closeOnEsc: false
						});
						form = $('<form action="'+url+'" method="POST"></form>')
							.append($('@csrf'))
							.append($('@method("PUT")'));
						$(document.body).append(form);
						form.submit();
					}
				});
			});
		});
    </script>
@endsection