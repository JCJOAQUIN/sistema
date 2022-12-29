@extends('layouts.child_module')
@php
	$days =
	[
		"Sunday"	=> "Domingo",
		"Monday"	=> "Lunes",
		"Tuesday"	=> "Martes",
		"Wednesday"	=> "Miércoles",
		"Thursday"	=> "Jueves",
		"Friday"	=> "Viernes",
		"Saturday"	=> "Sábado",
	];

	$colors =
	[
		"bg-rose-50",
		"bg-green-50",
		"bg-orange-50",
		"bg-indigo-50",
		"bg-fuchsia-50",
		"bg-emerald-50",
		"bg-teal-50",
		"bg-red-50",
		"bg-yellow-50",
		"bg-cyan-50",
		"bg-pink-50",
		"bg-amber-50",
		"bg-blue-50",
	];
	$indexColors 	   = random_int(0, 12);
	$reservationColors = [];
@endphp
@section('data')
	@component('components.labels.title-divisor') BUSCAR @endcomponent
	@component("components.forms.searchForm", ["variant" => "default", "attributeEx" => "id=\"formsearch\""])
		<div class="col-span-2">
			@component("components.labels.label") Número: @endcomponent
			@component("components.inputs.input-text")
				@slot("attributeEx")
					name="id"
					placeholder="Ingrese un número"
					value="{{ isset($id_boardroom) ? $id_boardroom : '' }}"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component("components.labels.label") Nombre: @endcomponent
			@component("components.inputs.input-text")
				@slot("attributeEx")
					name="name"
					placeholder="Ingrese un nombre"
					value="{{ isset($name) ? $name : '' }}"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component("components.labels.label") Ubicación: @endcomponent
			@php
				$options = collect();
				foreach(App\Property::all() as $p)
				{
					if(isset($location) && $location == $p->id)
					{
						$options = $options->concat([["value"=>$p->id, "selected"=>"selected", "description"=>$p->property]]);
					}
					else
					{
						$options = $options->concat([["value"=>$p->id, "description"=>$p->property]]);
					}
				}
				$attributeEx = "name=\"location\" multiple=\"multiple\"";
				$classEx = "location";
			@endphp
			@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx]) @endcomponent
		</div>
		<div class="col-span-2">
			@component("components.labels.label") Empresa: @endcomponent
			@php
				$options = collect();
				foreach($enterprises as $enterprise)
				{
					$description = strlen($enterprise->name) >= 35 ? substr(strip_tags($enterprise->name),0,35).'...' : $enterprise->name;
					if(isset($enterprise_id) && $enterprise_id == $enterprise->id)
					{
						$options = $options->concat([["value"=>$enterprise->id, "selected"=>"selected", "description"=>$description]]);
					}
					else
					{
						$options = $options->concat([["value"=>$enterprise->id, "description"=>$description]]);
					}
				}
				$attributeEx = "title=\"Empresa\" name=\"enterprise_id\" multiple=\"multiple\"";
				$classEx = "js-enterprise";
			@endphp
			@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx]) @endcomponent
		</div>
		<div class="col-span-2">
			@component("components.labels.label") Estado: @endcomponent
			@php
				$options = collect();
				$options = $options->concat([["value" => 1, "description" => "Activo"]]);
				$options = $options->concat([["value" => 0, "description" => "Cancelado"]]);

				if($status != null)
				{
					if($status == 1)
					{
						$options[0] += array("selected" => "selected");
					}
					else
					{
						$options[1] += array("selected" => "selected");
					}
				}
				$attributeEx = "title=\"Estado\" name=\"status\" multiple=\"multiple\" data-validation=\"required\"";
				$classEx = "js-status";
			@endphp
			@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx]) @endcomponent
		</div>
		<div class="col-span-2">
			@component("components.labels.label") Rango de fechas: @endcomponent
			@php
				if(isset($mindate) && isset($maxdate))
				{
					$inputs= [
						[
							"input_classEx" => "input-text-date datepicker",
							"input_attributeEx" => "name=\"mindate\" id=\"mindate\" step=\"1\" placeholder=\"Desde\" value=\"".$mindate."\" data-validation=\"required\" readonly",
						],
						[
							"input_classEx" => "input-text-date datepicker",
							"input_attributeEx" => "name=\"maxdate\" id=\"maxdate\" step=\"1\" placeholder=\"Hasta\" value=\"".$maxdate."\" data-validation=\"required\" readonly",
						]
					];
				}
				else
				{
					$inputs= [
						[
							"input_classEx" => "input-text-date datepicker",
							"input_attributeEx" => "name=\"mindate\" id=\"mindate\" step=\"1\" placeholder=\"Desde\" data-validation=\"required\" readonly",
						],
						[
							"input_classEx" => "input-text-date datepicker",
							"input_attributeEx" => "name=\"maxdate\" id=\"maxdate\" step=\"1\" placeholder=\"Hasta\" data-validation=\"required\" readonly",
						]
					];
				}
			@endphp
			@component("components.inputs.range-input",["inputs" => $inputs]) @endcomponent
		</div>
	@endcomponent
	@if($startDate)
		@if(count($boardrooms) > 0)
			<div class="flex flex-row justify-end">
				@component("components.forms.form", ["attributeEx" => "action=\"".route('boardroom.administration.export')."\" id=\"formExport\""])
					@component("components.inputs.input-text")
						@slot("attributeEx")
							type="hidden"
							name="id"
							value="{{ isset($id_boardroom) ? $id_boardroom : '' }}"
						@endslot
					@endcomponent
					@component("components.inputs.input-text")
						@slot("attributeEx")
							type="hidden"
							name="name"
							value="{{ isset($name) ? $name : '' }}"
						@endslot
					@endcomponent
					@component("components.inputs.input-text")
						@slot("attributeEx")
							type="hidden"
							name="location"
							value="{{ isset($location) ? $location : '' }}"
						@endslot
					@endcomponent
					@component("components.inputs.input-text")
						@slot("attributeEx")
							type="hidden"
							name="enterprise_id"
							value="{{ $enterprise_id }}"
						@endslot
					@endcomponent
					@component("components.inputs.input-text")
						@slot("attributeEx")
							type="hidden"
							name="status"
							value="{{ $status }}"
						@endslot
					@endcomponent
					@component("components.inputs.input-text")
						@slot("attributeEx")
							type="hidden"
							name="mindate"
							value="{{ isset($mindate) ? $mindate : '' }}"
						@endslot
					@endcomponent
					@component("components.inputs.input-text")
						@slot("attributeEx")
							type="hidden"
							name="maxdate"
							value="{{ isset($maxdate) ? $maxdate : '' }}"
						@endslot
					@endcomponent
					@component("components.buttons.button",["variant" => "success"])
						@slot("slot")
							<span>Exportar a Excel</span><span class="icon-file-excel"></span>
						@endslot
					@endcomponent
				@endcomponent
			</div>
			@php
				$modelHead	=
				[
					["value" => "Sala", "classEx" => "sticky inset-x-0"]
				];
				for ($i = $startDate->copy(); $i <= $endDate; $i->modify('+1 day'))
				{
					array_push($modelHead, ["value" => $days[$i->format('l')]." ".$i->format('d-m')]);
				}

				$queryStart	= $startDate->format('Y-m-d 00:00:00');
				$queryEnd	= $endDate->format('Y-m-d 23:59:59');
				
				$boardRoomsReservations = App\BoardroomReservations::whereIn('boardroom_id', $boardrooms->pluck('id'))
				->where('status',$status)
				->where(function($q) use($queryStart, $queryEnd)
				{
					$q->where(function($q) use($queryStart, $queryEnd)
					{
						$q->whereRaw('"'.$queryStart.'" BETWEEN start AND end')
							->whereRaw('"'.$queryEnd.'" BETWEEN start AND end');
					})
					->orWhere(function($q) use($queryStart, $queryEnd)
					{
						$q->whereRaw('start BETWEEN "'.$queryStart.'" AND "'.$queryEnd.'"')
							->whereRaw('end BETWEEN "'.$queryStart.'" AND "'.$queryEnd.'"');
					})
					->orWhere(function($q) use($queryStart, $queryEnd)
					{
						$q->where("start","<=",$queryEnd)
							->where("end",">",$queryEnd);
					})
					->orWhere(function($q) use($queryStart, $queryEnd)
					{
						$q->where("end",">=",$queryStart)
							->where("start","<",$queryStart);
					});
				})
				->orderBy('start')
				->get();
				
				$modelBody	= [];
				foreach ($boardrooms as $index => $room)
				{
					$modelBody[$index] =
					[
						"classEx"		=> "tr",
						"attributeEx" 	=> "attr-room=\"".$room->id."\"",
						[
							"classEx" 	=> "td sticky inset-x-0",
							"content"	=>
							[
								[
									"kind" 	=> "components.labels.label",
									"classEx" => "font-semibold w-40 p-2",
									"label" => htmlentities($room->name),
								]
							],
						],
					];

					for ($i = $startDate->copy(); $i <= $endDate; $i->modify('+1 day'))
					{
						$tdDays 			= [];
						$divs				= "";
						foreach ($boardRoomsReservations as $reservation)
						{
							if (!array_key_exists($reservation->id, $reservationColors))
							{
								$reservationColors[$reservation->id] = $colors[$indexColors];
								$indexColors < 12 ? $indexColors++ : $indexColors=0;
							}
							if($room->id == $reservation->boardroom_id)
							{
								if($i->copy()->startOfDay()->between($reservation->start->startOfDay(),$reservation->end->endOfDay()))
								{
									$data = "";
									if ($reservation->start >= $i->copy()->startOfDay() && $reservation->end <= $i->copy()->endOfDay())
									{
										$data.= $reservation->start->format('H:i')."-".$reservation->end->format('H:i');
									}
									else
									{
										if ($reservation->end < $i->copy()->endOfDay())
										{
											$data .= $reservation->end->copy()->format('H:i');
										}
										if ($i->copy()->startOfDay() <= $reservation->start && $i->copy()->endOfDay() < $reservation->end)
										{
											$data.= $reservation->start->format('H:i');
										}
									}	
									$divs .= "<div data-json='".json_encode($reservation)."' data-name-request='".$reservation->requestUser->fullName()."' class='date-container cursor-pointer ".$reservationColors[$reservation->id]." ".($data == "" ? "" : "")."p-3'>".$data."</div>";
								}
							}
						}
						
						$tdDays =
						[
							"classEx"	=> "td border-l border-orange-200",
							"content"	=>
							[
								[
									"kind"	=> "components.labels.label",
									"classEx"	=> "w-40 divide-y divide-orange-200",
									"label" => $divs != "" ? $divs : "",
								]
							],
						];
						array_push($modelBody[$index], $tdDays);
					}
				}
			@endphp
			@component('components.tables.table-users', [
				"modelBody" => $modelBody,
				"modelHead" => $modelHead,
				"boardrooms"=> true,
			])
			@endcomponent
			@component("components.forms.form",["attributeEx" => "id=\"form-reservation\" action=\"".route('boardroom.reservation.update')."\" method=\"POST\""])
				@component("components.modals.modal",[ "variant" => "large" ])
					@slot("id")
						reservationModal
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
					@slot("modalTitle")
						Reservación
					@endslot
					@slot("modalBody")
						@component("components.containers.container-form")
							<div class="col-span-2">
								@component("components.inputs.input-text")
									@slot("attributeEx")
										id="reservation_id"
										name="reservation_id"
										type="hidden"
									@endslot
								@endcomponent
								@component("components.labels.label") Sala: @endcomponent
									@php
										$options = collect();
										foreach($modalBoardrooms as $r)
										{
											$options = $options->concat([["value" => $r->id, "description" => $r->name]]);
										}
										$attributeEx = "name=\"room_id\" multiple=\"multiple\" data-validation=\"required\"";
										$classEx = "js-room removeselect removedisabled";
									@endphp
								@component ("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx]) @endcomponent
							</div>
							<div class="col-span-2">
								@component("components.labels.label") Solicitante: @endcomponent
									@php
										$attributeEx = "name=\"user_id\" multiple=\"multiple\" data-validation=\"required\"";
										$classEx = "js-users removeselect removedisabled";
									@endphp
								@component ("components.inputs.select", ["attributeEx" => $attributeEx, "classEx" => $classEx]) @endcomponent
							</div>
							<div class="col-span-2">
								@component("components.labels.label") Fecha de reservación: @endcomponent
								@php
									$inputs=
									[
										[
											"input_classEx" => "remove removedisabled",
											"input_attributeEx" => "id=\"select_dates_start\" name=\"select_dates_start\" placeholder=\"Ingrese el inicio\" data-validation=\"required\"",
										],
										[
											"input_classEx" => "remove removedisabled",
											"input_attributeEx" => "id=\"select_dates_end\" name=\"select_dates_end\" placeholder=\"Ingrese el fin\" data-validation=\"required\"",
										]
									];
								@endphp
								@component("components.inputs.range-input",["inputs" => $inputs, "classIndividual" => "boardroom-date"]) @endcomponent
							</div>
							<div class="col-span-2">
								@component("components.labels.label") Motivo: @endcomponent
								@component("components.inputs.input-text")
									@slot("attributeEx")
										id="reason"
										name="reason"
										data-validation="required"
										placeholder="Ingrese un motivo"
									@endslot
									@slot("classEx")
										removeselect
										removedisabled
									@endslot
								@endcomponent
							</div>
							<div class="col-span-2">
								@component("components.labels.label") Observaciones/Comentarios: @endcomponent
								@component("components.inputs.text-area")
									@slot("attributeEx")
										id="observations"
										name="observations"
										placeholder="Ingrese un comentario"
									@endslot
									@slot("classEx")
										removedisabled
									@endslot
								@endcomponent
							</div>
							<div class="col-span-2 md:col-span-4 @if ($status == 0) hidden @endif">
								@component("components.labels.label") ¿Cancelar reservación? @endcomponent
								<div class="flex space-x-2">
									@component("components.buttons.button-approval")
										@slot("classEx")
											cancelReservation
										@endslot
										@slot("attributeEx")
											name="cancelReservation"
											value="1"
											id="no_cancelReservation"
											checked
										@endslot
										No
									@endcomponent
				
									@component("components.buttons.button-approval")
										@slot("classEx")
											cancelReservation
										@endslot
										@slot("attributeEx")
											name="cancelReservation"
											value="0"
											id="cancelReservation"
										@endslot
										Si
									@endcomponent
								</div>
							</div>
							<div class="col-span-2 @if($status == 1) hidden @endif " id="reasonContainer">
								@component("components.labels.label") Motivo de cancelación: @endcomponent
								@component("components.inputs.text-area")
									@slot("classEx")
										cancel-description
									@endslot
									@slot("attributeEx")
										id="cancel_description"
										name="cancel_description"
										data-validation="required"
										placeholder="Ingrese el motivo de cancelación"
										@if($status == 0) disabled @endif
									@endslot
								@endcomponent
							</div>
						@endcomponent
					@endslot
					@slot("modalFooter")
						@if($status == 1)
							@component("components.buttons.button", ["variant" => "primary"])
								@slot("attributeEx")
									type="submit"
									id="submitButton"
								@endslot
								Actualizar
							@endcomponent
						@endif
						@component("components.buttons.button", ["variant" => "reset"])
							@slot("attributeEx")
								type="button"
								data-dismiss="modal"
							@endslot
							Cerrar
						@endcomponent
					@endslot
				@endcomponent
			@endcomponent
		@else
			@component("components.labels.not-found") @endcomponent
		@endif
	@endif
@endsection
@section('scripts')
	<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
	<script src="{{ asset('js/jquery-ui.js') }}"></script>
	<script src="{{ asset('js/datepicker.js') }}"></script>
	<script src="{{ asset('js/jquery.numeric.js') }}"></script>
	<script src="{{ asset('js/daterangepicker.js') }}"></script>
	<script type="text/javascript">
		var colors = [];
		$(document).ready(function()
		{
			$.validate({
				form: '#formsearch',
				onError   : function($form)
				{
					swal('', '{{ Lang::get("messages.form_error") }}', 'error');
				},
				onSuccess : function($form)
				{
					mindate = moment($('#mindate').val(),'DD-MM-YYYY');
					maxdate = moment($('#maxdate').val(),'DD-MM-YYYY');

					if(maxdate.isBefore(mindate))
					{
						swal('', 'La fecha de finalización debe ser posterior la fecha inicial.', 'error');
						$('#mindate, #maxdate').removeClass('valid').addClass('error');
						return false;
					}
					swal('Cargando',{
						icon: '{{ asset(getenv('LOADING_IMG')) }}',
						button: false,
						closeOnClickOutside: false,
						closeOnEsc: false,
					});
					return true;
				}
			});

			$.validate({
				form: '#form-reservation',
				onError   : function($form)
				{
					swal('', '{{ Lang::get("messages.form_error") }}', 'error');
				},
				onSuccess : function($form)
				{
					if($('#no_cancelReservation').is(':checked'))
					{
						start_date     = $('#select_dates_start').val();
						end_date       = $('#select_dates_end').val();
						sDate          = moment(start_date,'DD/MM/YYYY HH:mm:ss');
						eDate          = moment(end_date,'DD/MM/YYYY HH:mm:ss');

						if(eDate.isSameOrBefore(sDate))
						{
							$('#select_dates_start, #select_dates_end').removeClass('valid').addClass('error');
							swal('', 'La fecha de finalización debe ser posterior la fecha inicial, por favor verifique sus datos.', 'error');
							return false;
						}

						swal('Cargando',{
							icon: '{{ asset(getenv('LOADING_IMG')) }}',
							button: false,
							closeOnClickOutside: false,
							closeOnEsc: false,
						});
						return true;
					}
					swal('Cargando',{
						icon: '{{ asset(getenv('LOADING_IMG')) }}',
						button: false,
						closeOnClickOutside: false,
						closeOnEsc: true,
					});
					return true;
				}
			});
			@php
				$selects = collect([
					[
						"identificator"          => ".js-enterprise",
						"placeholder"            => "Seleccione la empresa",
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => ".js-status",
						"placeholder"            => "Seleccione el estatus",
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => ".location",
						"placeholder"            => "Seleccione la ubicación",
						"maximumSelectionLength" => "1"
					],
				]);
			@endphp
			@component("components.scripts.selects",["selects" => $selects]) @endcomponent
			$(document).on('click','.date-container',function()
			{
				$('.js-users').html('');
				$('#reservationModal').modal('show');
				data	= JSON.parse($(this).attr('data-json'));
				start	= moment(data.start).format('DD-MM-YYYY HH:mm');
				end		= moment(data.end).format('DD-MM-YYYY HH:mm');

				$('#select_dates_start').val(start);
				$('#select_dates_end').val(end);

				startRange	= moment().format('DD-MM-YYYY')+' '+moment(data.start).format('HH:mm');
				endRange	= moment().format('DD-MM')+'-'+(parseInt(moment(data.end).format('YYYY'))+1)+' '+moment(data.end).format('HH:mm');
                
				setRangePicker(startRange,endRange);
				$('#reason').val(data.reason)
				$('#observations').val(data.observations)
				$('#reservation_id').val(data.id);
                $('.js-users').append('<option value='+data.id_request+' selected="selected">'+($(this).attr('data-name-request'))+'</option>');
				$('.js-room').val(data.boardroom_id).trigger('change');

				if(data.status == 1)
				{
					$('#no_cancelReservation').prop('checked',true);
					$('.hideWhenIsCancel').show();
					$('.showWhenIsCancel').hide();
				}
				if(data.status == 0)
				{
					$('#cancelReservation').prop('checked',true);
					$('#cancel_description').html(data.cancel_description);
					$('.showWhenIsCancel').show();
					$('.hideWhenIsCancel').hide();
					$('.removeselect').removeAttr('data-validation');
					$('.removedisabled').attr('disabled','disabled');
					$('.boardroom-date').parent().addClass('hidden');
					$('.boardroom-date').parents('.range_picker_parent').addClass('bg-gray-100').removeClass('bg-white');
					$('.boardroom-date').parents('.range_picker_parent').find('.bg-white').addClass('bg-gray-100').removeClass('bg-white');
				}

                @php
                    $selects = collect([
                        [
                            "identificator"          => ".js-users",
                            "placeholder"            => "Nombre del solicitante",
                            "maximumSelectionLength" => "1"
                        ],
                        [
                            "identificator"          => ".js-room",
                            "placeholder"            => "Seleccione la sala",
                            "maximumSelectionLength" => "1"
                        ],
                    ]);
                @endphp
                @component("components.scripts.selects",["selects" => $selects]) @endcomponent
                generalSelect({'selector': '.js-users', 'model': 13});
			})
			.on('click','[data-dismiss="modal"]',function(e)
			{
				if ('{{ $status }}' == 1)
				{
					$('#reasonContainer').slideUp();
					$('.removedisabled').removeAttr('disabled');
					$('.removeselect').attr('data-validation','required');
					$('.cancel-description').removeClass('error').removeAttr('style').siblings('.form-error').remove();
					$('.boardroom-date').parent().removeClass('hidden');
					$('.boardroom-date').parents('.range_picker_parent').addClass('bg-white').removeClass('bg-gray-100');
					$('.boardroom-date').parents('.range_picker_parent').find('.bg-gray-100').addClass('bg-white').removeClass('bg-gray-100');
				}
				else
				{
					$('#reasonContainer').slideDown();
					$('.removeselect').removeAttr('data-validation');
					$('.removedisabled').attr('disabled','disabled');
					$('.boardroom-date').parent().addClass('hidden');
					$('.boardroom-date').parents('.range_picker_parent').addClass('bg-gray-100').removeClass('bg-white');
					$('.boardroom-date').parents('.range_picker_parent').find('.bg-white').addClass('bg-gray-100').removeClass('bg-white');
				}
                $('.boardroom-date').data('dateRangePicker').close();
				$('#select_dates_start').val("");
				$('#select_dates_end').val("");
				$('#reason').val("")
				$('#observations').val("")
				$('#reservation_id').val("");
				$('.js-users').html('');
				$('.js-room').val(null).trigger('change');
                $('.form-error').remove();
				$('.error').removeClass('error');
				$('.valid').removeClass('valid');
				$('.has-error').removeClass('has-error');
				$('#reason').removeAttr('style');
			})
			.on('change','.cancelReservation',function()
			{
				if ($('input[name="cancelReservation"]:checked').val() == '0') 
				{
					$('#reasonContainer').slideDown();
					$('.removeselect').removeAttr('data-validation');
					$('.removedisabled').attr('disabled','disabled');
					$('.boardroom-date').parent().addClass('hidden');
					$('.boardroom-date').parents('.range_picker_parent').addClass('bg-gray-100').removeClass('bg-white');
					$('.boardroom-date').parents('.range_picker_parent').find('.bg-white').addClass('bg-gray-100').removeClass('bg-white');
				}
				else
				{
					$('#reasonContainer').slideUp();
					$('.removedisabled').removeAttr('disabled');
					$('.removeselect').attr('data-validation','required');
					$('.cancel-description').removeClass('error').removeAttr('style').siblings('.form-error').remove();
					$('.boardroom-date').parent().removeClass('hidden');
					$('.boardroom-date').parents('.range_picker_parent').addClass('bg-white').removeClass('bg-gray-100');
					$('.boardroom-date').parents('.range_picker_parent').find('.bg-gray-100').addClass('bg-white').removeClass('bg-gray-100');
				}
			})
		});

		function setRangePicker(startRange,endRange)
		{
			$('.boardroom-date').dateRangePicker(
			{
				separator : ' a ',
				startOfWeek: 'monday',
				showShortcuts: false,
				format: 'DD-MM-YYYY HH:mm',
				language: 'es',
				autoClose: false,
				endDate: endRange,
				time:
				{
					enabled: true,
				},
				customTopBar: function()
				{
					@php
						$buttonDelete = view('components.buttons.button',["classEx" => "float-right delete_date_search bg-gray-200", "attributeEx" => "type=\"button\"", "variant" => "reset", "label" => "Limpiar"])->render();
					@endphp
					button = $('{!!preg_replace("/(\r)*(\n)*/", "", $buttonDelete)!!}');
					button.attr('onclick', 'cleanRangeInput("boardroom-date")');
					button = button.prop('outerHTML');
					html = button;
					html += '<div class="pt-2 normal-top">' +
							'<span class="selection-top">Seleccionado: </span> <b class="start-day">...</b>';
					html += ' <span class="separator-day"> a </span> <b class="end-day">...</b> <i class="selected-days">(<span class="selected-days-num">3</span> días)</i>';
					html += '</div>';
					html += '<div class="error-top">error</div>' +
							'<div class="pt-2 default-top">default</div>';
					return html;
				},
				getValue: function()
				{
					if ($('#select_dates_start').val() != "" && $('#select_dates_end').val() != "")
					{
						return $('#select_dates_start').val() + ' a ' + $('#select_dates_end').val();
					}
					else
					{
						return '';
					}
				},
				setValue: function(s,s1,s2)
				{
					$('#select_dates_start').val(s1);
					$('#select_dates_end').val(s2);
				}
			});
		}
	</script>
@endsection