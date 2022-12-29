<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
	<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
	<link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
	<link rel="manifest" href="/site.webmanifest">
	<link rel="mask-icon" href="/safari-pinned-tab.svg" color="#4b4b4b">
	<meta name="msapplication-TileColor" content="#ffffff">
	<meta name="theme-color" content="#ffffff">
	<!-- CSRF Token -->
	<meta name="csrf-token" content="{{ csrf_token() }}">
	<title>Sistema - @yield('title')</title>
	<!-- Styles -->
	<link href="https://fonts.googleapis.com/css?family=Raleway:300,400,600" rel="stylesheet">
	<link rel="stylesheet" type="text/css" href="{{ asset(mix('css/app.css')) }}">
	<link rel="stylesheet" type="text/css" href="{{ asset('fontawesome-free-5.15.2-web/css/all.css') }}"/>
	<link rel="stylesheet" href="{{ asset('css/daterangepicker.min.css') }}">
	<style>
		.embed-container
		{
			position: relative;
			padding-bottom: 56.25%;
			height: 0;
			overflow: hidden;
		}
		.embed-container iframe
		{
			position: absolute;
			top:0;
			left: 0;
			width: 100%;
			height: 100%;
		}
	</style>	
	@yield('css')
</head>
<body class="overflow-hidden">
	@php
		if(Auth::user()->adglobal == 1 || !Auth::user()->enterprise()->exists())
		{
			$url = url('images/logo-inicio.jpg');
		}
		else
		{
			$url = url('images/enterprise/'.Auth::user()->enterprise->first()->path);
		}
	@endphp
	<script src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.x.x/dist/alpine.min.js" defer></script>
	<div class="antialiased font-body flex-col lg:flex lg:flex-row h-screen w-full bg-cool-gray-100 overflow-hidden info-container">
		<div @click.away="open = false" @resize.window="open = false" class="z-50 overflow-auto bg-gradient-radial to-cyan-900 from-light-blue-900 flex flex-col w-full lg:w-64 text-white flex-shrink-0" x-data="{ open: false }" :class="{'absolute h-full': open, 'static': !open}"  >
			<div class="lg:hidden flex-shrink-0 px-8 py-4 flex flex-row items-center justify-between">
				<button class="rounded-lg lg:hidden focus:outline-none focus:shadow-outline" @click="open = !open">
					<svg fill="currentColor" viewBox="0 0 20 20" class="w-6 h-6">
						<path x-show="!open" fill-rule="evenodd" d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM9 15a1 1 0 011-1h6a1 1 0 110 2h-6a1 1 0 01-1-1z" clip-rule="evenodd"></path>
						<path x-show="open" fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
					</svg>
				</button>
				<div class="lg:invisible">
					<label>{{ Auth::user()->name }}</label>
				</div>
				<div class="lg:invisible">
					<a style="text-decoration: none; color: white;" href="{{ url('home') }}">
						<span class="icon-home"></span>
					</a>
				</div>
			</div>
			<div class="flex justify-center">
				<nav :class="{'block': open, 'hidden': !open}" class="flex-grow lg:block pb-4 lg:pb-0 md:mx-24 md:my-16 lg:m-0">
					<div class="flex justify-center w-6/12 md:w-9/12 m-auto my-6 bg-white rounded-xl">
						<a class="load-actioner" href="{{ url('home') }}">
							<img class="rounded-lg mt-2 mb-6 w-24"  src="{{ $url }}">
						</a>
					</div>
					<a href="{{ url('home') }}" class="load-actioner block px-8 py-2 mt-1 hover:text-black hover:bg-white focus:bg-white focus:text-black focus:outline-none focus:shadow-outline @unless(isset($id)) text-black bg-white @endunless ">
						<span class="icon-home"></span> Inicio
					</a>
					@foreach(Auth::user()->module->where('father',NULL)->sortBy('id') as $key)
						<a class="load-actioner block px-8 py-2 mt-1 hover:text-black focus:bg-white focus:text-black hover:bg-white focus:outline-none focus:shadow-outline @if( isset($id) && $id == $key['id']) text-black bg-white @endif " href="{{ url($key['url']) }}">
							<span class="{{ $key['icon'] }}"></span> {{ $key['name'] }}
						</a>
					@endforeach
					<a class="block px-8 py-2 mt-1 hover:text-black focus:bg-white focus:text-black hover:bg-white focus:outline-none focus:shadow-outline" href="#" id="logout-actioner">
						<span class="icon-exit"></span> Salir
					</a>
					<form id="logout-form" class="hidden" action="{{ route('logout') }}" method="POST">
						@csrf
					</form>
					<div class="mb-10 pb-2 bg-black bg-opacity-25 flex justify-center md:invisible lg:visible xl:visible mt-4">
						<p class="px-4 py-2 mt-2 text-sm font-semibold text-white rounded-lg">{{ Auth::user()->name }}</p>
					</div>
				</nav>
			</div>
		</div>
		<div class="p-6 md:p-6 h-right-container lg:h-screen w-full overflow-hidden">
			<div class ="lg:py-3 px-3 pb-10 bg-white h-full overflow-auto relative">
				@yield('content')
			</div>
		</div>
	</div>
	<div class="loading-page fixed w-full top-0 z-50">
		<div class="flex justify-center h-screen bg-white">
			<img src="{{asset(getenv('LOADING_IMG'))}}" alt="Cargando..." class="w-40">
		</div>
	</div>
	@component("components.modals.modal", ["variant" => "xl"])
		@slot('id')
			dataUrlTutorial
		@endslot
		@slot('modalBody')
			<div class="embed-container">
				<iframe id="frame" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
			</div>
		@endslot
		@slot('modalFooter')
			<div class="flex flex-row justify-end">
				@component("components.buttons.button", ["variant" => "secondary"])
					@slot('attributeEx')
						type="button" data-dismiss="modal"
					@endslot
					Cerrar
				@endcomponent
			</div>
		@endslot
	@endcomponent
	<script src="{{ asset('js/jquery-3.3.1.min.js') }}"></script>
	<script src="{{ asset('js/sweetalert.min.js') }}"></script>
	<script src="{{ asset('js/main.js') }}"></script>
	<script src="{{ asset('js/validator/jquery.form-validator.min.js') }}"></script>
	<script src="{{ asset(mix('js/app.js')) }}"></script>
	<script type="text/javascript" src="{{ asset('js/modal.js') }}"></script>
	<script src="{{ asset('js/select2.min.js') }}"></script>
	<script type="text/javascript" src="{{ asset('js/jquery.daterangepicker.min.js') }}"></script>
	@yield('scripts')
	<script>
		$.ajaxSetup(
		{
			headers:
			{
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			}
		});
		var do_sticky;
		$(document).ready(function()
		{
			stickyAdjustment();
			@php
				$selects = collect([
					[
						"identificator"          	=> ".selectProvider", 
						"placeholder"            	=> "Acciones:", 
						"minimumResultsForSearch"	=> "Infinity",
						"allowClear"			 	=> "true"
					]
				]);
			@endphp
			@component("components.scripts.selects",["selects" => $selects])
			@endcomponent
			$("form").submit(function()
			{
				$("#mindate,#maxdate").removeAttr("disabled");
			});
			rangeDate();
			$(document).on('click','.go-back',function()
			{
				window.history.back();
			})
			.on('click','[data-toggle="modal"]',function()
			{
				url	= $(this).attr('data-url-tutorial');
				$('#frame').attr('src',url);
			})
			.on('click','#dataUrlTutorial [data-dismiss="modal"]', function()
			{
				$('#frame').attr('src','');
			})
			.on('submit','form',function()
			{
				//$('button[type="submit"]').attr('disabled',true);
				$('[name="send"],[name="enviar"],[name="save"],[name="btnReject"],[name="decline"],[name="btnSave"],#send,#submitButton,#create_employee,[name="btnAddProviderDocuments"]').attr('disabled',true);
			})
			.on('click','.go-back',function()
			{
				window.history.back();
			})
			.on('click','.arrow-action',function()
			{
				selector = "."+$(this).attr('id');
				$(this).parent('div').parent('div').next().find(selector).toggleClass('hidden');
				if($('input[type="checkbox"].arrow-action.providers-x').prop("checked") == false)
				{
					$('input[type="checkbox"]#all-id').removeAttr('disabled');
				}
				else
				{
					$('input[type="checkbox"]#all-id').prop("checked", false).attr('disabled');
				}
				if(selector == ".all-id")
				{
					$('.details-id,.providers-id,.concepts-id,.voting-id').removeClass('hidden');
					$('input[type="checkbox"].arrow-action').prop("checked", true);
					$('input[type="checkbox"]#all-id').prop("checked", false).prop('disabled', true);
				}
				var svg = $(this).find('.polyline').attr('points');
				if(svg == "6 9 12 15 18 9") //Down
				{
					$(this).find('.polyline').attr('points','18 15 12 9 6 15');
					$(this).parent().siblings('.col-to-hide').addClass('hidden');
					$(this).parents('thead').parents('table').find('.col-to-hide').addClass('hidden');
				}
				if(svg == "18 15 12 9 6 15") //Up
				{
					$(this).find('.polyline').attr('points','6 9 12 15 18 9');
					$(this).parent().siblings('.col-to-hide').removeClass('hidden');
					$(this).parents('thead').parents('table').find('.col-to-hide').removeClass('hidden');
				}
				stickyAdjustment();
			})
			.on('change', '.massive-component', function(e)
			{
				$(this).parent('div').addClass("image_success");
			})
			.on('click','[data-toggle="modal"]',function()
			{
				url	= $(this).attr('data-url-tutorial');
				
				$('#frame').attr('src',url);
			})
			.on('change','.selectProvider',function()
			{
				selector = $(this).parent('div');
				$('option:selected',this).each(function(i,v) 
				{
					attrs = "";
					$.each(this.attributes, function(w,e) 
					{
						if(this.specified) 
						{
							attrs = attrs+this.name+'="'+this.value+'" ';
							(this.name == "type") ? kindElement = "input" : kindElement = "a";
						}
					});
				});
				switch(kindElement)
				{
					case ('input'):
						element = $('<input '+attrs+'/>').addClass('hidden');
						selector.append(element);
					break;
					case ('a'):
						element = $('<a '+attrs+'></a>').addClass('hidden');
						selector.append(element);
					break;
				}
				$($(element)[0].click());
				element.remove();
			})
			.on('keyup',".select2-search__field",function(e)
			{
				if((e.keyCode==46 || e.keyCode==8) && ($(this).val().length > 2))
				{
					selector = $(this);
					selector.prop('disabled', true);
					setTimeout(function()
					{
						selector.prop('disabled', false);
						selector.focus();
					},500);
				}
			})
			.on('click','.load-actioner',function(e)
			{
				e.preventDefault();
				continueFlag = true;
				if($(this).parents('.paginate').length > 0)
				{
					$.each($._data(document, "events").click,function(i,v)
					{
						if(v.selector == '.paginate a')
						{
							continueFlag = false;
						}
					});
				}
				if($(this).parents('.result_pagination').length > 0)
				{
					$.each($._data(document, "events").click,function(i,v)
					{
						if(v.selector == '.result_pagination a')
						{
							continueFlag = false;
						}
					});
				}
				if($(this).parents('.paginateSearch').length > 0)
				{
					$.each($._data(document, "events").click,function(i,v)
					{
						if(v.selector == '.paginateSearch a')
						{
							continueFlag = false;
						}
					});
				}
				if(continueFlag)
				{
					url = $(this).attr('href');
					$('.loading-page').removeClass('hidden');
					setTimeout(function()
					{
						window.location = url;
					},100);
				}
			});
			if($(window).width() <= 768)
			{
				$('.col-to-hide').addClass('hidden');
			}
			else
			{
				$('.col-to-hide').removeClass('hidden');
			}
			$(window).on('resize', function()
			{
				var win = $(this);
				if (win.width() <= 768)
				{
					$('.col-to-hide').addClass('hidden');
					$('.arrow-action').find('.polyline').attr('points','18 15 12 9 6 15');
				}
				else
				{
					$('.col-to-hide').removeClass('hidden');
				}
				clearTimeout(do_sticky);
				do_sticky = setTimeout(stickyAdjustment, 100);
			});
			$("input[name='mindate'], input[name='maxdate']").keydown(function (event) 
			{
				if(event.which != 8)
				{
					return false;
				}
				else
				{
					$(this).val("");
				}
			});
		});
		$.formUtils.addValidator(
		{
			name : 'rfc',
			validatorFunction : function(value, $el, config, language, $form)
			{
				if(value.match(/^([A-Z,Ñ,&]{3,4}([0-9]{2})(0[1-9]|1[0-2])(0[1-9]|1[0-9]|2[0-9]|3[0-1])[A-Z|\d]{3}){0,1}$/i)!=null || value.match(/^XAXX1[0-9]{8}$/i)!=null)
				{
					return true;
				}
				else
				{
					return false;
				}
			},
			errorMessage : 'El RFC debe ser válido.',
			errorMessageKey: 'badRfc'
		});
		$.formUtils.addValidator(
		{
			name: 'clabe',
			validatorFunction : function(value, $el, config, language, $form)
			{
				if(value.match(/^(\d{18}){0,1}$/i)!=null)
				{
					return true;
				}
				else
				{
					return false;
				}
			},
			errorMessage : 'La CLABE debe ser de 18 dígitos.',
			errorMessageKey: 'badClabe'
		});
		$.formUtils.addValidator(
		{
			name: 'tarjeta',
			validatorFunction : function(value, $el, config, language, $form)
			{
				if(value.match(/^(\d{16}){0,1}$/i)!=null)
				{
					return true;
				}
				else
				{
					return false;
				}
			},
			errorMessage : 'El número de tarjeta debe ser de 16 dígitos.',
			errorMessageKey: 'badTarjeta'
		});
		$.formUtils.addValidator(
		{
			name: 'cuenta',
			validatorFunction : function(value, $el, config, language, $form)
			{
				if(value.match(/^(\d{5,15}){0,1}$/i)!=null)
				{
					return true;
				}
				else
				{
					return false;
				}
			},
			errorMessage : 'La cuenta debe ser entre 5 y 15 dígitos.',
			errorMessageKey: 'badCuenta'
		});
		$.formUtils.addValidator(
		{
			name: 'iban',
			validatorFunction : function(value, $el, config, language, $form)
			{
				if(value.match(/^[A-Za-z0-9_.-]+$/)!=null || value.length == 0)
				{
					if((value.length >= 14 && value.length <=35) || value.length == 0)
					{
						return true;
					}
					else
					{
						return false;
					}
				}
				else
				{
					return false;
				}
			},
			errorMessage : 'La IBAN debe ser entre 14 y 35 dígitos.',
			errorMessageKey: 'badIban'
		});
		$.formUtils.addValidator(
		{
			name: 'bic_swift',
			validatorFunction : function(value, $el, config, language, $form)
			{
				if(value.match(/^[A-Za-z0-9_.-]+$/)!=null || value.length == 0)
				{
					if((value.length >= 8 && value.length <=11) || value.length == 0)
					{
						return true;
					}
					else
					{
						return false;
					}
				}
				else
				{
					return false;
				}
			},
			errorMessage : 'El BIC/SWIFT debe ser entre 8 y 11 dígitos.',
			errorMessageKey: 'badIban'
		});
		$.formUtils.addValidator(
		{
			name: 'phone',
			validatorFunction : function(value, $el, config, language, $form)
			{
				if(value.match(/^(((?:\+|00)[17](?: |\-)?|(?:\+|00)[1-9]\d{0,2}(?: |\-)?|(?:\+|00)1\-\d{3}(?: |\-)?)?(0\d|\([0-9]{3}\)|[1-9]{0,3})(?:((?: |\-)[0-9]{2}){4}|((?:[0-9]{2}){4})|((?: |\-)[0-9]{3}(?: |\-)[0-9]{4})|([0-9]{7}))){0,1}$/i)!=null)
				{
					return true;
				}
				else
				{
					return false;
				}
			},
			errorMessage : 'Ingrese un número telefónico válido.',
			errorMessageKey: 'badPhone'
		});
		$.formUtils.addValidator(
		{
			name: 'custom_name',
			validatorFunction : function(value, $el, config, language, $form)
			{
				if(value.match(/^[a-zA-ZÀ-ÿ\u00f1\u00d1]+(\s*[a-zA-ZÀ-ÿ\u00f1\u00d1]*)*[a-zA-ZÀ-ÿ\u00f1\u00d1]+$/)!=null)
				{
					if(value.length > 2)
					{
						return true;
					}
					else
					{
						return false;
					}
				}
				else
				{
					return false;
				}
			},
			errorMessage : 'El nombre debe ser mayor a 2 caracteres.',
			errorMessageKey: 'badIban'
		});
		$.formUtils.addValidator(
		{
			name: 'custom_last_name',
			validatorFunction : function(value, $el, config, language, $form)
			{
				if(value.match(/^[a-zA-ZÀ-ÿ\u00f1\u00d1]+(\s*[a-zA-ZÀ-ÿ\u00f1\u00d1]*)*[a-zA-ZÀ-ÿ\u00f1\u00d1]+$/)!=null)
				{
					if(value.length > 2)
					{
						return true;
					}
					else
					{
						return false;
					}
				}
				else
				{
					return false;
				}
			},
			errorMessage : 'El apellido debe ser mayor a 2 caracteres.',
			errorMessageKey: 'badIban'
		});
		$.formUtils.addValidator(
		{
			name: 'custom_wbs_name',
			validatorFunction : function(value, $el, config, language, $form)
			{
				if(value.length > 0)
				{
					return true;
				}
				else
				{
					return false;
				}
			},
			errorMessage : 'Este campo es obligatorio',
			errorMessageKey: 'badIban'
		});
		$.formUtils.addValidator(
		{
			name: 'number_no_zero',
			validatorFunction : function(value, $el, config, language, $form)
			{
				if(value.match(/^(0*[1-9][0-9]*(\.[0-9]+)?|0+\.[0-9]*[1-9][0-9]*)$/)!=null)
				{
					return true;
				}
				else
				{
					return false;
				}
			},
			errorMessage : 'El campo debe ser mayor a 0',
			errorMessageKey: 'badIban'
		});
		$.validate(
		{
			lang: 'es'
		});
		@if(session('alert'))
			{!! session('alert') !!}
		@endif
		$("body").on("DOMMouseScroll MouseScrollEvent MozMousePixelScroll wheel scroll", function ()
		{
			if($("#ui-datepicker-div").is(":visible"))
			{
				$('#datepicker, .datepicker, .datepicker2').datepicker("hide").blur();
			}
			if($(".date-picker-wrapper").is(":visible"))
			{
				$('.date-picker-wrapper').hide();
			}
		});
		$(window).on('load',function()
		{
			$('.loading-page').addClass('hidden');
		});
		var generalSelectProject;
		var generalSelectWBS;
		function generalSelect(paramsArray)
		{
			paramsArray['dependsTitle'] = (paramsArray['title'] ? paramsArray['title'] : $(paramsArray['depends']).parent('div').parent('div').find('label').text());
			paramsArray['dependsTitle'] = paramsArray['dependsTitle'].replace(/:/i, "");
			minimumLength 				= (paramsArray["minInput"] ? paramsArray["minInput"] : 2);
			$(paramsArray["selector"]).select2({
				maximumSelectionLength: paramsArray["maxSelection"] ? paramsArray["maxSelection"] : 1,
				width		: "100%",
				placeholder : "Ingrese su búsqueda",
				ajax        :
				{
					delay   : 400,
					url     : '{{route('general.select')}}',
					dataType: 'json',
					method  : 'post',
					data    : function (params)
					{
						s =
						{
							search			: params.term,
							model			: paramsArray["model"],
							module_id		: {{isset($child_id) ? $child_id : 'null'}},
							params_data 	: {'id': ($(paramsArray['depends']).length > 0 ? $(paramsArray['depends']).val()[0] : ""), 'extra':paramsArray},
							page: params.page || 1,
						}
						return s;
					},
					processResults: function (data) 
					{
						if(paramsArray["selector"] == ".js-projects")
						{
							generalSelectProject = data['results'];
						}
						if(paramsArray["selector"] == ".js-code_wbs")
						{
							generalSelectWBS = data['results'];
						}
						if(paramsArray["depends"] != null && $(paramsArray["depends"]).find('option:selected').val() == null)
						{
							message = "Por favor, primero ingrese el campo "+paramsArray['dependsTitle'];
						}
						else
						{
							message = "No hay resultados"
						}
						return data;
					}
				},
				minimumInputLength: minimumLength,
				language          : 
				{
					noResults: function()
					{
						return message;
					},
					searching: function()
					{
						return "Buscando...";
					},
					inputTooShort: function(args)
					{
						return 'Por favor ingrese más de '+minimumLength+' caracteres';
					},
					loadingMore: function () 
					{
						return 'Buscando más datos...';
					},
				}
			})
			.on("change",function(e)
			{
				if(paramsArray["maxSelection"] == null)
				{
					if($(this).val().length>1)
					{
						$(this).val($(this).val().slice(0,1)).trigger('change');
					}
				}
				else if(paramsArray["maxSelection"]>=1)
				{
					if($(this).val().length>paramsArray["maxSelection"])
					{
						$(this).val($(this).val().slice(0,paramsArray["maxSelection"])).trigger('change');
					}
				}
			});
		}
		function cleanRangeInput(element)
		{
			$("."+element).data('dateRangePicker').clear();
			$("."+element).parents('.range_picker_parent').find('input').each(function(i,v)
			{
				$(this).val('');
			});
			$('.hour-val').text('00');
			$('.minute-val').text('00');
			$('.hour-range').val('0');
			$('.minute-range').val('0');
		}
		function rangeDate()
		{
			if($('.range_date').length > 0)
			{
				$('.range_date').each(function(i,v)
				{
					paramValue = "none";
					if($('.range_date').attr('data-params'))
					{
						params = $('.range_date').attr('data-params').split(":");
						paramValue = params[1].replace(/(')+/g,'');
					}
					$withTime = $(this).find('.with-time').length > 0 ? [true,'DD-MM-YYYY HH:mm'] : [false, 'DD-MM-YYYY'];
					$(this).addClass('custom-'+i);
					$(this).dateRangePicker(
					{
						separator : ' a ',
						startOfWeek: 'monday', 
						showShortcuts: false, 
						batchMode : paramValue,
						format: $withTime[1],
						language: 'es',
						autoClose: (paramValue != 'none' ? false : !$withTime[0]),
						time:
						{
							enabled: $withTime[0]
						},
						customTopBar: function()
						{
							@php
								$buttonDelete = view('components.buttons.button',["classEx" => "float-right delete_date_search bg-gray-200", "attributeEx" => "type=\"button\"", "variant" => "reset", "label" => "Limpiar"])->render();
							@endphp
							button = $('{!!preg_replace("/(\r)*(\n)*/", "", $buttonDelete)!!}');
							button.attr('onclick', 'cleanRangeInput("custom-'+i+'")');
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
							dates = new Array();
							$(this).find('input').each(function(i,v)
							{
								id = $(this).attr('id');
								name = $(this).attr('name');
								if(name != 'undefined')
								{
									//name
									dates.push('[name="'+$(this).attr('name')+'"]');
								}
								else if(id != 'undefined')
								{
									//id
									dates.push('#'+$(this).attr('id'));
								}
							});
						},
						setValue: function(s,s1,s2)
						{ 
							$(dates[0]).val(moment(s1, $withTime[1], true)['_i']).trigger('change');
							$(dates[1]).val(moment(s2, $withTime[1], true)['_i']).trigger('change');
						}
					});
				});
			}
		}
		function allFilterProviders()
		{
			if(!$("#details-id").prop("checked") && !$("#providers-id").prop("checked") && !$("#voting-id").prop("checked"))
			{
				$('#all-id').prop('checked', true);
				$('#all-id').attr('disabled', false);
				// $('.group-concepts').toggleClass('col-span-full');
				$('.w-provider').addClass('flag-w-provider').removeClass('w-provider');
			}
			else
			{
				$('#all-id').prop('checked', false);
				$('#all-id').attr('disabled', true);
				$('#all-id').trigger('change');
				// $('.group-concepts').addClass('md:w-auto !w-[560px]');
				$('.flag-w-provider').addClass('w-provider').removeClass('flag-w-provider');
			}
		}
		function stickyAdjustment()
		{
			$('tr').each(function()
			{
				left = 0;
				$(this).find('.sticky').each(function()
				{
					$(this).css('left',left);
					left += Number($(this).outerWidth().toFixed());
				});
			});
		}
		function rowColor(selector, row)
		{
			if($(selector).find('tr').length % 2 != 0)
			{
				row.find('td').removeClass('bg-white').addClass('bg-orange-100');
			}
			return row;
		}
	</script>
</body>
</html>
