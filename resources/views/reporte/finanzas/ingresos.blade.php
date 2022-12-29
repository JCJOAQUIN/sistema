@extends('layouts.child_module')
@section('data')
	@component("components.labels.title-divisor") BUSCAR @endcomponent
	@php
		$values = ["name" => $name, "minDate" => $mindate, "maxDate" => $maxdate];
		$hidden = ['enterprise','folio'];
	@endphp
	@component("components.forms.searchForm",["attributeEx" => "id=\"formsearch\"","values" => $values, "hidden" => $hidden])
		@slot("contentEx")
			<div class="col-span-2">
				@component("components.labels.label") Empresa: @endcomponent
				@php
					$options = collect();
					foreach(App\Enterprise::where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeEnt($option_id)->pluck('enterprise_id'))->orderBy('name','asc')->get() as $ent)
					{
						$description = strlen($ent->name) >= 35 ? substr(strip_tags($ent->name),0,35)."..." : $ent->name;
						if(isset($idEnterprise) && in_array($ent->id, $idEnterprise))
						{
							$options = $options->concat([["value"=>$ent->id, "selected"=>"selected", "description"=>$description]]);
						}
						else
						{
							$options = $options->concat([["value"=>$ent->id, "description"=>$description]]);
						}
					}
				@endphp
				@component("components.inputs.select", 
				[
					"options"		=> $options, 
					"attributeEx"	=> "name=\"idEnterprise[]\" title=\"Empresa\" multiple=\"multiple\" data-validation=\"required\"", 
					"classEx"		=> "js-enterprise"
				])@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Proyecto: @endcomponent
				@php
					$optionsProject = collect();
					if (isset($idProject)) 
					{
						foreach(App\Project::whereIn('idproyect',$idProject)->get() as $p)
						{
							$optionsProject = $optionsProject->concat([["value"=>$p->idproyect, "selected"=>"selected", "description"=>$p->proyectName]]);
						}
					}
				@endphp
				@component("components.inputs.select", 
					[
						"attributeEx"	=> "name=\"idProject[]\" title=\"Proyecto\" multiple=\"multiple\"", 
						"classEx"		=> "js-project",
						"options"		=> $optionsProject, 
					])
				@endcomponent
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
								formaction={{ route('report.income.excel') }} @endslot
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
								formaction={{ route('report.income.excelwg') }} @endslot
							@slot('label')
								<span>Exportar sin agrupar</span><span class="icon-file-excel"></span> 
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
					["value"	=> "Estado"],
					["value"	=> "Solicitante"],
					["value"	=> "Empresa"],
					["value"	=> "Proyecto"],
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
							"label" => $request->income->first()->title,
						]
					],
					[
						"content" =>
						[
							"label" =>  $request->statusrequest->description,
						]
					],
					[
						"content" =>
						[
							"label" => $request->requestUser()->exists() ? $request->requestUser->fullName() : "Sin solicitante",
						]
					],

					[
						"content" =>
						[
							"label" => $request->requestEnterprise()->exists() ? $request->requestEnterprise->name : "Sin empresa",
						]
					],
					[
						"content" =>
						[
							"label" => $request->requestProject()->exists() ? $request->requestProject->proyectName : "Sin proyecto",
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
							"label" => "$".number_format($request->income->first()->amount,2)
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
<script src="{{ asset('js/datepicker.js') }}"></script>
<script type="text/javascript"> 
	$(document).ready(function()
	{
		@php
			$selects = collect(
			[
				[
					"identificator"				=> ".js-enterprise",
					"placeholder"				=> "Seleccione una empresa",
					"languaje"					=> "es"
				],
				[
					"identificator"				=> ".js-status",
					"placeholder"				=> "Seleccione un estado de solicitud",
					"languaje"					=> "es"
				]
			]);
		@endphp
		@component("components.scripts.selects",["selects" => $selects]) @endcomponent
		generalSelect({'selector':'.js-project', 'model': 21, 'maxSelection': -1});

		$(document).on('click','[data-toggle="modal"]', function()
		{
			folio = $(this).attr('data-folio');
			$.ajax(
			{
				type : 'get',
				url  : '{{ route("report.income.detail") }}',
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
		});
	});

	
	@if(isset($alert)) 
		{!! $alert !!} 
	@endif 
</script> 
@endsection


