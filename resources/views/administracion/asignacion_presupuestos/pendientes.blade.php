@extends('layouts.child_module')

@section('data')
	@component('components.labels.title-divisor') Buscar presupuestos @endcomponent
	@php
		$values = 
		[
			'enterprise_option_id' => $option_id, 
			'enterprise_id'        => $enterpriseid, 
			'folio'                => $folio, 
			'name'                 => $name, 
			'minDate'              => $mindate, 
			'maxDate'              => $maxdate
		];
	@endphp
	@component('components.forms.searchForm', ["attributeEx" => "id=\"formsearch\"", "values" => $values])
		@slot('contentEx')
			<div class="col-span-2">
				@component('components.labels.label')Cuenta:@endcomponent
				@php
					$attributeEx    = "title = \"Solicitud de Estado\" name = \"status\" multiple = \"multiple\"";
					$optionsAccount = collect();
					$classEx        = 'js-status';
					if(isset($enterpriseid))
					{
						foreach(App\Account::orderNumber()->where('idEnterprise',$enterpriseid)
							->where('selectable',1)
							->get() as $acc)
						{
							$description    = $acc->account."-".$acc->description."(".$acc->content.")";
							$optionsAccount = $optionsAccount->concat([['value'=>$acc->idAccAcc, 'description'=>$description]]);
						}
					}
					if(isset($account))
					{
						$accountSelected = collect($optionsAccount->where('value', $account)->first())->put('selected', 'selected');
						$optionsAccount  = $optionsAccount->concat($optionsAccount->where('value', $account)->push($accountSelected));
					}
				@endphp
				@component('components.inputs.select',
				[
					'attributeEx' => "title=\"Cuenta\" multiple=\"multiple\" name=\"account\"", 
					'classEx'     => "js-account removeselect", 
					"options"     => $optionsAccount
				]) @endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label')Solicitud:@endcomponent
				@php
					$options = collect();
					foreach(App\RequestKind::orderBy('kind','asc')->whereIn('idrequestkind',[1,9])->orderBy('kind','ASC')->get() as $k)
					{
						$description = $k->kind;
						if(isset($kind) && $kind == $k->idrequestkind)
						{
							$options = $options->concat([['value'=>$k->idrequestkind, 'selected'=>'selected', 'description'=>$description]]);
						}
						else
						{
							$options = $options->concat([['value'=>$k->idrequestkind, 'description'=>$description]]);
						}
					}
					$attributeEx = "name=\"kind\" multiple=\"multiple\"";
					$classEx     = "js-kind";
				@endphp
				@component('components.inputs.select', ['options' => $options, 'attributeEx' => $attributeEx, 'classEx' => $classEx]) @endcomponent
			</div>
		@endslot
		@if(count($requests) > 0)
			@slot('export')
				<div class="flex flex-row justify-end">
					@component('components.labels.label')
						@component('components.buttons.button',["variant" => "success"])
							@slot('attributeEx')
								type       = "submit "
								formaction = "{{ route('budget.export') }}"
							@endslot
							@slot('label')
								<span>Exportar a Excel</span><span class="icon-file-excel"></span>
							@endslot
						@endcomponent
					@endcomponent
				</div>
				@component("components.buttons.button",["variant" => "success"])
					@slot('classEx')
						select-all
					@endslot
					@slot('attributeEx')
						type="button"
					@endslot
					@slot('label')
						Seleccionar todo (página actual)
					@endslot
				@endcomponent
				@component("components.buttons.button",["variant" => "secondary"])
					@slot('classEx')
						massive-action cursor-not-allowed opacity-50 modal-open
					@endslot
					@slot('attributeEx')
						type        = "button" 
						data-toggle = "modal" 
						disabled 
						data-target = "#massiveActionModal"
					@endslot
					@slot('label')
						Autorizar/Rechazar seleccionados
					@endslot
				@endcomponent
			@endslot
		@endif
	@endcomponent
	@if(count($requests) > 0)
		@php
			$body      = [];
			$modelBody = [];
			$modelHead = 
			[
				[
					["value" => "Seleccionar"],
					["value" => "Folio"],
					["value" => "Folio de requisición"],
					["value" => "Tipo de solicitud"],
					["value" => "Título"],
					["value" => "Empresa"],
					["value" => "Solicitante"],
					["value" => "Estado de la solicitud"],
					["value" => "Fecha de autorización"],
					["value" => "Clasificación del gasto"],
					["value" => "Importe"],
					["value" => "Acción"]
				]
			];
			if(isset($requests))
			{
				foreach($requests as $request)
				{
					switch($request->kind)
					{
						case 1:
							$total        = $request->purchases->first()->amount;
							$titleRequest = htmlentities($request->purchases->first()->title);
							break;
						case 9:
							$total        = $request->refunds->first()->total;
							$titleRequest = htmlentities($request->refunds->first()->title);
							break;
					}
					if($request->requestUser()->exists())
					{
						$userRequest = $request->requestUser->name." ".$request->requestUser->last_name." ".$request->requestUser->scnd_last_name;
					}
					else
					{
						$userRequest = "Sin nombre";
					}
					if(isset($request->accountsReview->account) && $request->kind == 1)
					{
						$clasificacion = $request->accountsReview->account.' '.$request->accountsReview->description;
					}
					else if(!isset($request->accountsReview->account) && $request->kind == 1)
					{
						$clasificacion = "---";
					}
					else
					{
						$clasificacion = "Varias";
					}
					if (isset($request->reviewedEnterprise->name))
					{
						$enterprise = $request->reviewedEnterprise->name;
					}
					else if(isset($request->reviewedEnterprise->name) == false && isset($request->requestEnterprise->name))
					{
						$enterprise = $request->requestEnterprise->name;
					}
					else
					{
						$enterprise = "Sin empresa";
					}
					$body = 
					[
						[
							"content" => 
							[
								[
									"kind"             => "components.inputs.checkbox",
									"label"            => "<span class=\"icon-check\"></span>", 
									"attributeEx"      => "name=\"budget[]\" value=\"".$request->folio."\" id=\"budget_".$request->folio."\"",
									"classExContainer" => "inline-flex"
								]
							]
						],
						[
							"content" => 
							[
								"label" => $request->folio,
							]
						],
						[
							"content" => 
							[
								"label" => $request->idRequisition != null ? $request->idRequisition : ''
							]
						],
						[ 
							"content" => 
							[
								"label" => $request->requestkind->kind != null ? $request->requestkind->kind : 'No hay'
							]
						],
						[
							"content" =>
							[
								"label" => $titleRequest
							],
						],
						[
							"content" =>
							[
								"label" => $enterprise
							]
						],
						[
							"content" => 
							[
								"label" => $userRequest
							]
						],
						[
							"content" =>
							[
								"label" => $request->statusrequest != null ? $request->statusrequest->description : 'Sin estado'
							]
						],
						[
							"content" =>
							[
								"label" => $request->authorizeDate != '' ? Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$request->authorizeDate)->format('d-m-Y H:i') : 'Aún no se autoriza'
							]
						],
						[
							"content" =>
							[
								"label" => $clasificacion
							]
						],
						[
							"content" =>
							[
								"label" => "$".number_format($total,2)
							]
						],
						[
							"content" => 
							[
								[
									"kind"          => "components.buttons.button",
									"label"         => "<span class=\"icon-pencil\"></span>", 
									"buttonElement" => "a",
									"variant"       => "success",
									"classEx"       => "load-actioner",
									"attributeEx"   => "alt=\"Editar Solicitud\" title=\"Editar Solicitud\" href=\"".route('budget.review.edit',$request->folio)."\""
								]
							]
						]
					];
					$modelBody[] = $body;
				}
			}
		@endphp
		@component('components.tables.table',[
			"modelHead" => $modelHead,
			"modelBody" => $modelBody
		])
			@slot('attributeEx')
				id="table"
			@endslot
			@slot('attributeExBody')
				id="body2"
			@endslot
			@slot('classExBody')
				request-validate
			@endslot
		@endcomponent
		{{ $requests->appends($_GET)->links() }}
	@else
		@component("components.labels.not-found")
			@slot("slot")
				Resultado no encontrado
			@endslot
		@endcomponent
	@endif
	@component("components.modals.modal",["variant" => "large"])
		@slot('id')massiveActionModal @endslot
		@slot('modalTitle')
			Acciones masivas para seleccionados
		@endslot
		@slot('modalBody')
			@component('components.labels.label')¿Desea aprobar o rechazar las solicitudes seleccionadas?@endcomponent
			@php
				$options = collect(
					[
						[
							"value"       => "0", 
							"description" =>  "Rechazar"
						], 
						[
							"value"       => "1", 
							"description" =>  "Aprobar"
						]
					]
				);
			@endphp
			@component('components.inputs.select', ['options' => $options, 'attributeEx' => 'name=budgetMassiveStatus', 'classEx' => 'budgetMassiveStatus']) @endcomponent
			@component('components.labels.label')Comentarios (opcional) [éste será aplicada a cada una de las solicitudes seleccionadas]@endcomponent
			@component("components.inputs.text-area")
				@slot('attributeEx')
					cols = "90" 
					rows = "10" 
					name = "budgetComment"
				@endslot
			@endcomponent
			@slot('modalFooter')
				@component("components.buttons.button",["variant" => "success"])
					@slot('classEx')
						send-massive mr-4
					@endslot
					@slot('attributeEx')
						type="button"
					@endslot
					Enviar
				@endcomponent
				@component("components.buttons.button",["variant" => "secondary"])
					@slot('attributeEx')
						type         = "button" 
						data-dismiss = "modal"
					@endslot
					Cerrar
				@endcomponent
			@endslot
		@endslot
	@endcomponent
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
				$selects = collect(
				[
					[
						"identificator"          => ".js-kind", 
						"placeholder"            => "Seleccione el tipo de solicitud", 
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => ".budgetMassiveStatus", 
						"placeholder"            => "Seleccione una opción", 
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => ".js-enterprise", 
						"placeholder"            => "Seleccione la empresa", 
						"maximumSelectionLength" => "1"
					]
				]);
			@endphp
			@component("components.scripts.selects",["selects" => $selects]) @endcomponent
			generalSelect({'selector': '.js-account', 'depends': '.js-enterprise','model': 10});
			$(function()
			{
				$('.datepicker').datepicker(
				{
					dateFormat : 'yy-mm-dd',
				});
			});
			$(document).on('click','.select-all',function()
			{
				$('[name="budget[]"]').prop('checked',true);
				$('.massive-action').removeClass('opacity-50');
				$('.massive-action').removeClass('cursor-not-allowed');
				$('.massive-action').prop('disabled',false);
			})
			.on('change','.js-enterprise', function()
			{
				$('.js-account').empty();
				generalSelect({'selector': '.js-account', 'depends': '.js-enterprise','model': 10});
			})
			.on('click','[name="budget[]"]',function()
			{
				if($('[name="budget[]"]:checked').length > 0)
				{
					$('.massive-action').removeClass('opacity-50');
					$('.massive-action').removeClass('cursor-not-allowed');
					$('.massive-action').prop('disabled',false);
				}
				else
				{
					$('.massive-action').addClass('opacity-50');
					$('.massive-action').addClass('cursor-not-allowed');
					$('.massive-action').prop('disabled',true);
				}
			})
			.on('click','.send-massive',function(e)
			{
				e.preventDefault();
				
				status = $('[name="budgetMassiveStatus"]').val();
				if(status.length === 0)
				{
					swal('', 'Debes seleccionar el estatus.', 'error');
					return;
				}
				swal({
					title  : "",
					text   : "Confirme que desea actualizar los estados de las solicitudes",
					icon   : "warning",
					buttons: 
					{
						cancel:
						{
							text      : "Cancelar",
							value     : null,
							visible   : true,
							closeModal: true,
						},
						confirm:
						{
							text      : "Aceptar",
							value     : true,
							closeModal: false
						}
					},
					dangerMode : true,
				})
				.then((a) =>
				{
					if (a)
					{
						form = $('<form action="{{route('budget.massive')}}" method="POST"></form>')
							.append($('@csrf'))
							.append('<input type="hidden" name="status" value="'+status[0]+'">')
							.append('<input type="hidden" name="comment" value="'+$('[name="budgetComment"]').val()+'">');
						$('[name="budget[]"]:checked').each(function(i,v)
						{
							form.append('<input type="hidden" name="budget[]" value="'+$(this).val()+'">');
						});
						$(document.body).append(form);
						form.submit();
					}
				});
			});
		});
		function checkbox_click()
		{
			if($(this).children('.check_box').is(":checked"))
			{
				$(this).children('.check_box').prop('checked', false);
			}
			else
			{
				$(this).children('.check_box').attr('type', 'text');
			}
		}
	</script>
@endsection