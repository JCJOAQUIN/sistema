@extends('layouts.child_module')
@section('data')
	<div class="text-center text-xl font-bold my-6">Calendario de sala de juntas: {{ $boardroom->name }}</div>
	<div class="mb-6" id='calendar'></div>
	@component("components.forms.form",["attributeEx" => "id=\"form-new-reservation\" action=\"".route('boardroom.reservation.update')."\" method=\"POST\""])
		@component("components.modals.modal",[ "variant" => "large" ])
			@slot("id")
				newReservationModal
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
						@component("components.inputs.input-text")
							@slot("attributeEx")
								id="room_id"
								name="room_id"
								type="hidden"
								value="{{ $boardroom->id }}";
							@endslot
						@endcomponent
						@component("components.labels.label") Solicitante: @endcomponent
							@php
								$options = collect();
								$attributeEx = "name=\"user_id\" multiple=\"multiple\" id=\"multiple-users\" data-validation=\"required\"";
								$classEx = "js-users";
							@endphp
						@component ("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx]) @endcomponent
					</div>
					<div class="col-span-2">
						@component("components.labels.label") Fecha de reservación: @endcomponent
						@php
							$inputs= 
							[
								[
									"input_classEx" => "input-text-date datepicker remove",
									"input_attributeEx" => "id=\"select_dates_start\" name=\"select_dates_start\" placeholder=\"Ingrese el inicio\" data-validation=\"required\"",
								],
								[
									"input_classEx" => "input-text-date datepicker remove",
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
								text-area
							@endslot
						@endcomponent
					</div>
				@endcomponent
			@endslot
			@slot("modalFooter")
					@component("components.buttons.button", ["variant" => "primary"])
						@slot("attributeEx")
							type="submit" 
							id="submitButton"	
						@endslot
						Agendar
					@endcomponent
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
@endsection
@section('scripts')
	<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
	<link rel="stylesheet" href="{{ asset('css/jquery.timepicker.min.css') }}">	
	<link rel="stylesheet" type="text/css"  href="{{ asset('css/fullCalendar.css') }}" />
	<script src="{{ asset('js/jquery-ui.js') }}"></script>
	<script src="{{ asset('js/jquery.timepicker.min.js') }}"></script>
	<script src="{{ asset('js/jquery.numeric.js') }}"></script>
	<script src="{{ asset('js/fullCalendar.js') }}"></script>
	<script src="{{ asset('js/daterangepicker.js') }}"></script>
	<script type="text/javascript">
		const today = moment("{{ \Carbon\Carbon::now() }}");
		var calendar;
		var events;

		$(document).ready(function()
		{			
			$.validate({
				form: '#form-new-reservation',
				onError   : function($form)
				{
					swal('', '{{ Lang::get("messages.form_error") }}', 'error');
				},
				onSuccess : function($form)
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
			});

			const roundedStart     = moment();
			const roundedRemainder = 30 - (roundedStart.minute() % 30);
			const nowRounded       = moment(roundedStart).add(roundedRemainder, "minutes");
			
			events = 
			[
				@foreach ( $boardroom->reservations()->where('status',1)->get() as $reservation )
				{
					title  : '{{ $reservation->reason }}',
					start  : '{{ $reservation->start }}',
					end    : '{{ $reservation->end }}',
					backgroundColor:"{{ Auth::user()->id == $reservation->id_elaborate ? 'rgb(22, 93, 152)' : 'rgb(187, 12, 12)' }}",
					extendedProps:{
						elaborate     : "{{ $reservation->id_elaborate }}",
						reason        : "{{ $reservation->reason }}",
						observations  : "{{ $reservation->observations }}",
						id_request    : "{{ $reservation->id_request }}",
						reservation_id: "{{ $reservation->id }}",
						id            : "{{ $reservation->id }}",
						ignore        : false,
						start         : '{{ $reservation->start }}',
						end           : '{{ $reservation->end }}',
					}
				},
				@endforeach
				 {
					start          : today.startOf('day').format('YYYY-MM-DD HH:mm'),
					end            : nowRounded.format('YYYY-MM-DD HH:mm'),
					display        : 'background',
					backgroundColor: '#ddd',
					extendedProps:
					{
						reason          : "",
						observations    : "",
						id_request      : "",
						reservation_id  : "",
						id              : "",
						ignore          : false,
						start: today.startOf('day').format('YYYY-MM-DD HH:mm'),
						end  : moment().format('YYYY-MM-DD HH:mm'),
					}
				},
			];
			
			let FullCalendarActions = 
			{
				currentTime: null,
				isDblClick: function () 
				{
					let prevTime =
					typeof FullCalendarActions.currentTime === null
						? new Date().getTime() - 1000000
						: FullCalendarActions.currentTime;
					FullCalendarActions.currentTime = new Date().getTime();
					return FullCalendarActions.currentTime - prevTime < 500;
				},
			};
			
			var calendarEl = document.getElementById('calendar');
			calendar = new FullCalendar.Calendar(calendarEl, 
			{
				height			: 'auto',
				events			: events,
				locale			: esLocale,
				selectable		: true,
				nowIndicator	: true,
				allDaySlot		: false,
				initialView		: 'timeGridWeek',
				headerToolbar: 
				{
					left	: 'prev,next today',
					center	: 'title',
					right	: 'dayGridMonth,timeGridWeek,timeGridDay'
				},
				buttonIcons:
				{
					prev: ' left-calendar-arrow',
					next: ' right-calendar-arrow',
				},
				views: 
				{
					dayGridMonth:{
						dayHeaderContent:function(e){
							date = moment(e.date)
							return {
								html: 
									"<div>"+
									"    <div class='day-header'>"+date.format('ddd')+"</div>"+
									"</div>"
							}
						}
					},
					timeGridDay:{
						dayHeaderContent:function(e){
							date = moment(e.date)
							return {
								html: 
									"<div>"+
									"    <div class='day-header'>"+date.format('dddd')+"</div>"+
									"</div>"

							}
						}
					},
					timeGridWeek: { 
						titleFormat: function(e){
							date       = moment(e.date);
							end        = moment(e.end);
							isNextMont = ! moment(date).startOf('month').isSame( moment(end).startOf('month') );
							
							
							str = 'Semana del ' + date.format('DD');
							
							if(isNextMont)
							{
								str += ' de ' + date.format('MMMM')
							}

							str += ' al ' + end.format('DD') + ' de ' + end.format('MMMM') +' de '  +end.format('YYYY')
							
							return str
						},
						dayHeaderContent:function(e){
							date = moment(e.date)
							return {
								html: 
									"<div class='grid place-items-center'>"+
									"    <div class='day-header-week'>"+date.format('dddd')+"</div>"+
									"    <div class='day-header-responsive'>"+date.format('ddd')+"</div>"+
									"    <div class='day-date'>"+date.format('MM-DD')+"</div>"+
									"</div>"

							}
						}
					}
				},
				selectConstraint: 
				{
					start: today,
				},
				dateClick: function (date, jsEvent, view) 
				{
					if (FullCalendarActions.isDblClick()) {
						if(date.dayEl.matches('.fc-daygrid-day.fc-day-today') || date.dayEl.matches('.fc-daygrid-day.fc-day-future'))
						{
							calendar.changeView('timeGridWeek', date.date);
						}
					}
				},
				selectAllow: function(info)
				{
					
					start = moment(info.start)
					end = moment(info.end)
					
					var evts = calendar.getEvents();
					
					evts = evts.filter(function(e,i) {
						
						
						if(e.extendedProps['ignore'])
						{
							return false;
						}

						evStart = moment(e.start)
						evEnd = moment(e.end)

						return (moment(evStart).isBetween(start,end) || moment(evEnd).isBetween(start,end) || start.isSame(evStart) || end.isSame(evEnd) )
					});
					return !moment(info.start).isBefore(moment()) && evts.length == 0;
				},
				select : function(info) 
				{
					start		= moment(info.start).format('DD-MM-YYYY HH:mm');
					end   		= moment(info.end).format('DD-MM-YYYY HH:mm');
					$('#select_dates_start').val(start);
					$('#select_dates_end').val(end);
					startRange	= moment().format('DD-MM-YYYY')+' '+moment(info.start).format('HH:mm');
					endRange	= moment().format('DD-MM')+'-'+(parseInt(moment(info.end).format('YYYY'))+1)+' '+moment(info.end).format('HH:mm');
					$('#newReservationModal').modal("show");
					setRangePicker(startRange,endRange);
					@php
						$selects = collect([
							[
								"identificator"          => ".js-users", 
								"placeholder"            => "Seleccione el solicitante", 
								"maximumSelectionLength" => "1"
							],
						]);
					@endphp
					@component("components.scripts.selects",["selects" => $selects]) @endcomponent
					generalSelect({'selector': '.js-users', 'model': 13});
				},
			}
			); 
			calendar.render();

			$(document).on('click','[data-dismiss="modal"]',function(e)
			{
				$('.boardroom-date').data('dateRangePicker').close();
				$('#select_dates_start').val("");
				$('#select_dates_end').val("");
				$('#reason').val("")
				$('#observations').val("")
				$('#reservation_id').val("");				
				$('.form-error').remove();
				$('.error').removeClass('error');
				$('.valid').removeClass('valid');
				$('.has-error').removeClass('has-error');
				$('#reason').removeAttr('style');
			});
		})

		function setRangePicker(startRange, endRange)
		{
			$('.boardroom-date').dateRangePicker(
			{
				separator : ' a ',
				startOfWeek: 'monday', 
				showShortcuts: false, 
				format: 'DD-MM-YYYY HH:mm',
				language: 'es',
				autoClose: false,
				startDate: startRange,
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