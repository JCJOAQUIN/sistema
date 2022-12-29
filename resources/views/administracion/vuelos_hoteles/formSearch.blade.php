@extends('layouts.child_module')
@section('data')
	@component("components.labels.title-divisor") BUSCAR SOLICITUDES @endcomponent
	@php
		$values = ["enterprise_option_id" => $option_id,"folio" => $folio, "enterprise_id" => $enterpriseid, "minDate" => $mindate, "maxDate" => $maxdate];
		$hidden = ['name'];
	@endphp
	@component("components.forms.searchForm",["attributeEx" => "id=\"formsearch\"","values" => $values, "hidden" => $hidden])
		@slot("contentEx")
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type        = "hidden" 
					name        = "option_id" 
					value       = "{{ $option_id }}"
				@endslot
			@endcomponent
			<div class="col-span-2">
				@component('components.labels.label')Pasajero:@endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type        = "text" 
						name        = "passenger_name" 
						id          = "input-search" 
						placeholder = "Ingrese un nombre" 
						value       = "{{ isset($passenger_name) ? $passenger_name : '' }}"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Solicitante: @endcomponent
				@php
					$options = collect();
					if (isset($user_request))
					{
						$user			= App\User::find($user_request);
						$description	= $user->fullName();
						$options		= $options->concat([["value"=>$user->id, "selected"=>"selected", "description"=>$description]]);
					}
					$attributeEx	= "name=\"user_request\" title=\"Solicitante\" multiple=\"multiple\"";
					$classEx		= "js-users";
				@endphp
				@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Proyecto: @endcomponent
				@php
					$options = collect();
					if (isset($projectId))
					{
						$project		= App\Project::find($projectId);
						$description	= $project->proyectName;
						$options		= $options->concat([["value"=>$project->idproyect, "selected"=>"selected", "description"=>$description]]);
					}
					$attributeEx	= "name=\"projectId\" title=\"Proyecto\" multiple=\"multiple\"";
					$classEx		= "js-project";
				@endphp
				@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Tipo de vuelo: @endcomponent
				@php
					$options = collect();

					if(isset($type_fligth) && $type_fligth==1)
					{
						$options = $options->concat([["value"=>"1", "selected"=>"selected", "description"=>"Sencillo"]]);
					}
					else
					{
						$options = $options->concat([["value"=>"1", "description"=>"Sencillo"]]);
					}

					if(isset($type_fligth) && $type_fligth==2)
					{
						$options = $options->concat([["value"=>"2", "selected"=>"selected", "description"=>"Redondo"]]);
					}
					else
					{
						$options = $options->concat([["value"=>"2", "description"=>"Redondo"]]);
					}
					$attributeEx	= "name=\"type_fligth\" title=\"Proyecto\" multiple=\"multiple\"";
					$classEx		= "js-type-flight";
				@endphp
				@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])@endcomponent
			</div>
			@if($option_id == 286)
				<div class="col-span-2">
					@component('components.labels.label')Estado de Solicitud:@endcomponent
					@php
						$options = collect();
						foreach(App\StatusRequest::whereIn('idrequestStatus',[2,3,4,5,6,7,10,11,12])->orderBy('description','asc')->get() as $s)
						{
							if(isset($status) && in_array($s->idrequestStatus, $status))
							{
								$options = $options->concat([['value'=>$s->idrequestStatus, 'selected' => 'selected', 'description'=>$s->description]]);
							}
							else
							{
								$options = $options->concat([['value'=>$s->idrequestStatus, 'description'=>$s->description]]);
							}
						}
					@endphp
					@component('components.inputs.select', 
						[
							'attributeEx' => "title=\"Estado de Solicitud\" multiple=\"multiple\" name=\"status[]\"", 
							'classEx'     => "js-status", 
							"options"     => $options
						]
					)
					@endcomponent
				</div>
			@endif
		@endslot
		@if (count($requests_fligths) > 0)
			@slot("export")
				<div class="float-right">
					@component('components.labels.label')
						@component('components.buttons.button',['variant' => 'success'])
							@slot('attributeEx') 
								type=submit 
								formaction={{ route('flights-lodging.export') }} @endslot
							@slot('label')
								<span>Exportar a Excel</span><span class="icon-file-excel"></span> 
							@endslot
						@endcomponent
					@endcomponent
				</div>
			@endslot
		@endif
	@endcomponent
	@if (count($requests_fligths) > 0)
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
					["value"	=> "Fecha de elaboración"],
					["value"	=> "Elaborado por"],
					["value"	=> "Acción"]
				]
			];

			foreach($requests_fligths as $requests_fligth)
			{
				$body = 
				[
					[
						"content" =>
						[
							"label" => $requests_fligth->folio_request
						]
					],
					[
						"content" =>
						[
							"label" => $requests_fligth->title != null ? htmlentities($requests_fligth->title) : 'No hay'
						]
					],
					[
						"content" =>
						[
							"label" => $requests_fligth->request->requestUser()->exists() ? $requests_fligth->request->requestUser->fullName() : 'No hay'
						]
					],

					[
						"content" =>
						[
							"label" => $requests_fligth->request->statusrequest->description 
						]
					],
					[
						"content" =>
						[
							"label" =>  $requests_fligth->request->requestEnterprise()->exists() ? $requests_fligth->request->requestEnterprise->name : 'No hay'
						]
					],
					[
						"content" =>
						[
							"label" =>  Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$requests_fligth->request->fDate)->format('d-m-Y H:i')
						]
					],
					[
						"content" =>
						[
							"label" =>  $requests_fligth->request->elaborateUser()->exists() ? $requests_fligth->request->elaborateUser->fullName() : 'No hay'
						]
					]
				];

				if($option_id == 286)
				{
					if($requests_fligth->request->status == 5 || $requests_fligth->request->status == 6 || $requests_fligth->request->status == 7  || $requests_fligth->request->status == 10 || $requests_fligth->request->status == 11)
					{
						if($requests_fligth->request->status == 5 || $requests_fligth->request->status == 10 || $requests_fligth->request->status == 11)
						{
							$body[]["content"] = 
							[
								[
									"kind"          => "components.buttons.button",
									"buttonElement" => "a",
									"classEx"		=> "follow-btn",
									"attributeEx"   => "alt=\"Nueva Solicitud\" title=\"Nueva Solicitud\" href=\"".route('flights-lodging.follow.newFlight',$requests_fligth->folio_request)."\"",
									"variant"		=> "warning",	
									"label"			=> "<span class=\"icon-plus\"></span>"
								],
								[
									"kind"          => "components.buttons.button",
									"buttonElement" => "a", 
									"classEx"		=> "follow-btn",
									"attributeEx"   => "alt=\"Ver Solicitud\" title=\"Ver Solicitud\" href=\"".route('flights-lodging.follow.edit',$requests_fligth->folio_request)."\"",
									"variant" 		=> "secondary",
									"label"			=> "<span class=\"icon-search\"></span>"
								],
								[
									"kind"          => "components.buttons.button",
									"buttonElement" => "a", 
									"classEx"		=> "follow-btn",
									"attributeEx"   => "alt=\"Descargar Solicitud\" title=\"Descargar Solicitud\" href=\"".route('flights-lodging.export-pdf',$requests_fligth->folio_request)."\"",
									"variant" 		=> "dark-red",
									"label"			=> "<span class=\"icon-pdf\"></span>"
								],
							];
						}
						else
						{
							$body[]["content"] = 
							[
								[
									"kind"          => "components.buttons.button",
									"buttonElement" => "a",
									"classEx"		=> "follow-btn",
									"attributeEx"   => "alt=\"Nueva Solicitud\" title=\"Nueva Solicitud\" href=\"".route('flights-lodging.follow.newFlight',$requests_fligth->folio_request)."\"",
									"variant"		=> "warning",	
									"label"			=> "<span class=\"icon-plus\"></span>"
								],
								[
									"kind"          => "components.buttons.button",
									"buttonElement" => "a", 
									"classEx"		=> "follow-btn",
									"attributeEx"   => "alt=\"Ver Solicitud\" title=\"Ver Solicitud\" href=\"".route('flights-lodging.follow.edit',$requests_fligth->folio_request)."\"",
									"variant" 		=> "secondary",
									"label"			=> "<span class=\"icon-search\"></span>"
								],
							];
						}
					}
					elseif($requests_fligth->request->status == 3 || $requests_fligth->request->status == 4) 
					{
						$body[]["content"] = 
						[
							[
								"kind"          => "components.buttons.button",
								"buttonElement" => "a", 
								"classEx"		=> "follow-btn",
								"attributeEx"   => "alt=\"Ver Solicitud\" title=\"Ver Solicitud\" href=\"".route('flights-lodging.follow.edit',$requests_fligth->folio_request)."\"",
								"variant" 		=> "secondary",
								"label"			=> "<span class=\"icon-search\"></span>"
							],
						];
					}
					else 
					{
						$body[]["content"] = 
						[
							[
								"kind"          => "components.buttons.button",
								"buttonElement" => "a", 
								"classEx"		=> "follow-btn",
								"attributeEx"   => "alt=\"Editar Solicitud\" title=\"Editar Solicitud\" href=\"".route('flights-lodging.follow.edit',$requests_fligth->folio_request)."\"",
								"variant" 		=> "success",
								"label"			=> "<span class=\"icon-pencil\"></span>"
							],
						];
					}	
				}
				elseif($option_id == 287)
				{
					$body[]["content"] = 
					[
						[
							"kind"          => "components.buttons.button",
							"buttonElement" => "a", 
							"classEx"		=> "follow-btn",
							"attributeEx"   => "alt=\"Ver Solicitud\" title=\"Ver Solicitud\" href=\"".route('flights-lodging.review.edit',$requests_fligth->folio_request)."\"",
							"variant" 		=> "success",
							"label"			=> "<span class=\"icon-pencil\"></span>"
						],
					];
				}
				elseif($option_id == 288)
				{
					$body[]["content"] = 
					[
						[
							"kind"          => "components.buttons.button",
							"buttonElement" => "a", 
							"classEx"		=> "follow-btn",
							"attributeEx"   => "alt=\"Ver Solicitud\" title=\"Ver Solicitud\" href=\"".route('flights-lodging.authorization.edit',$requests_fligth->folio_request)."\"",
							"variant" 		=> "success",
							"label"			=> "<span class=\"icon-pencil\"></span>"
						],
					];
				}
				$modelBody[] = $body;
			}
		@endphp
		@component("components.tables.table",
			[
				"modelHead" => $modelHead,
				"modelBody" => $modelBody,
			])
		@endcomponent
		{{ $requests_fligths->appends($_GET)->links() }}
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
						"maximumSelectionLength"=> "1"
					],
					[
						"identificator"			=> ".js-status",
						"placeholder"			=> "Seleccione un estado",
						"languaje"				=> "es",

					],
					[
						"identificator"			=> ".js-type-flight",
						"placeholder"			=> "Seleccione el tipo de vuelo",
						"languaje"				=> "es",
						"maximumSelectionLength"=> "1",

					]
				]);
			@endphp
			@component("components.scripts.selects",["selects" => $selects]) @endcomponent

			generalSelect({'selector':'[name="projectId"]','model': 21, 'maxSelection': 1});
			generalSelect({'selector':'[name="user_request"]','model': 36, 'maxSelection': 1});
		});
	</script> 
@endsection