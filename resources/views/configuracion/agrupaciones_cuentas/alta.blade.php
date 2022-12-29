@extends('layouts.child_module')

@section('data')
	@if(isset($group))
		@component("components.forms.form",["methodEx" => "PUT","attributeEx" => "method=\"POST\" action=\"".route("account-concentrated.update",$group->id)."\" id=\"container-alta\""])
	@else
		@component("components.forms.form",["attributeEx" => "method=\"POST\" action=\"".route("account-concentrated.store")."\" id=\"container-alta\""])
	@endif
			@component("components.labels.subtitle") Para {{ (isset($group) ? "editar la agrupación" : "agregar una agrupación nueva") }} es necesario colocar los siguientes campos: @endcomponent
			@component("components.containers.container-form")
				<div class="col-span-2">
					@component("components.labels.label")
						Nombre de agrupación:
					@endcomponent
					@component("components.inputs.input-text")
						@slot("classEx")
							name
							form-control
						@endslot
						@slot("attributeEx")
							name="name"
							id="name"
							placeholder="Ingrese el nombre de agrupación"
							data-validation = "server"
							data-validation-url = "{{ route('account-concentrated.validation') }}"
							@if(isset($group))
								data-validation-req-params = "{{ json_encode(array('oldConcentred'=> $group->name,'enterprise_Id'=>$group->idEnterprise)) }}"
								value="{{ $group->name }}"
							@endif
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component("components.labels.label") Empresa: @endcomponent
					@php
						$options = collect();
						foreach (App\Enterprise::orderName()->get() as $enterprise) 
						{
							$description = $enterprise->name;
							if (isset($group) && $group->idEnterprise == $enterprise->id) 
							{
								$options = $options->concat([["value"=>$enterprise->id, "selected"=>"selected", "description"=>$description]]);
							}
							else 
							{
								$options = $options->concat([["value"=>$enterprise->id, "description"=>$description]]);
							}
						}
						$attributeEx = "name=\"idEnterprise\" id=\"idEnterprise\" data-validation=\"required\"";
						$classEx = "form-control removeselect";
					@endphp
					@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])@endcomponent
				</div>
			@endcomponent
			<div class="text-center bg-orange-500 mb-2">
				@component("components.labels.label", ["classEx" => "text-white"])
					SELECCIONE LAS CUENTAS
				@endcomponent
			</div>
			<div id="tbody-account" class="w-full grid grid-cols-2 md:grid-cols-3">
				@if(isset($group))
					@php
						$countTD		= 0;
						$arrayIdAccAcc	= [];
						$countIdAccAcc	= 0;
						foreach ($group->hasAccount as $g) 
						{
							$arrayIdAccAcc[$countIdAccAcc] = $g->idAccAcc;
							$countIdAccAcc++;
						}
						$accountExist = App\GroupingHasAccount::select('idAccAcc')->where('idEnterprise',$group->idEnterprise)->where('idGroupingAccount','!=',$group->id)->get();
					@endphp
					@foreach(App\Account::where('idEnterprise',$group->idEnterprise)->where('account','like','5%')->whereNotIn('idAccAcc',$accountExist)->orderBy('account','ASC')->get() as $acc)
							@if ($acc->level == 3) 
								<div class="col-span-1 p-2 grid md:grid md:grid-cols-12 space-x-2 ">
									<div class="col-span-2 grid place-content-start md:place-content-center">
										@component("components.inputs.checkbox", ["classExContainer" => "text-center"])
											@slot("attributeEx")
												name="idAccAcc[]"
												value="{{ $acc->idAccAcc }}"
												id="{{ $acc->idAccAcc }}"
												@if(isset($group->hasAccount) && in_array($acc->idAccAcc, $arrayIdAccAcc))
													checked="checked"
												@endif
											@endslot
											<span class="icon-check"></span>
										@endcomponent
									</div>
									<div class="col-span-10 place-content-center md:self-center">
										@component("components.labels.label", ["classEx" => "break-words md:break-normal"])
											{{ $acc->account }} - {{ $acc->description }} ({{ $acc->content }})
										@endcomponent
									</div>
								</div>
							@endif
					@endforeach
				@endif
			</div>
			<div class="flex flex-wrap justify-center w-full space-x-2 py-4">
				@component("components.buttons.button",["variant" => "primary"])
					@slot("attributeEx")
						type="submit"
						name="enviar"
					@endslot
					@isset($group) ACTUALIZAR @else REGISTRAR @endif
				@endcomponent
				@if(!isset($group))
					@component("components.buttons.button",["variant" => "reset"])
						@slot("attributeEx")
							type="reset"
							name="borra"
							value="Borrar campos"
						@endslot
						@slot("classEx")
							btn-delete-form
						@endslot
						BORRAR CAMPOS
					@endcomponent
				@endif
				@isset($group)
					@component('components.buttons.button', [ "buttonElement" => "a", "variant" => "reset"])
						@slot("attributeEx")
							@if(isset($option_id)) 
								href="{{ url(getUrlRedirect($option_id)) }}" 
							@else 
								href="{{ url(getUrlRedirect($child_id)) }}" 
							@endif 
						@endslot
						@slot('classEx')
							load-actioner
						@endslot
						REGRESAR 
					@endcomponent
				@endisset
			</div>
		@endcomponent
@endsection

@section('scripts')
	<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
	<script src="{{ asset('js/jquery-ui.js') }}"></script>
	<script src="{{ asset('js/jquery.numeric.js') }}"></script>
	<script src="{{asset('js/jquery.mask.js')}}"></script>
	<script src="{{ asset('js/datepicker.js') }}"></script>
	<script type="text/javascript">
		$(document).ready(function() 
		{
			$.validate(
			{
				form: '#container-alta',
				modules	:	'security',
				onError   : function($form)
				{
					swal('', '{{ Lang::get("messages.form_error") }}', 'error');
				},
				onSuccess : function($form)
				{
					if($('[name="idAccAcc[]"]:checked').length == 0)
					{
						swal('','Seleccione al menos una cuenta','error');
						return false;
					}
					else
					{
						swal('Cargando',{
							icon: '{{ asset(getenv('LOADING_IMG')) }}',
							button: false,
						});
						return true;
					}
				}
			});
			$('[name="idEnterprise"]').select2(
			{
				placeholder				: 'Seleccione la empresa',
				language				: "es",
				maximumSelectionLength	: 1
			})
			.on("change",function(e)
			{
				if($(this).val().length>1)
				{
					$(this).val($(this).val().slice(0,1)).trigger('change');
				}
			});
			$(document).on('change','[name="idEnterprise"]',function()
			{
				if ($(this,'option:selected').val() != "" && $(this,'option:selected').val() != undefined)
				{
					entreterpriseId	=	$('#idEnterprise').val();
					$('[name="name"]').attr("data-validation-req-params", '{"enterprise_Id":"'+entreterpriseId+'"}');
				}
				$('#tbody-account').empty();
				idEnterprise	= $(this).val();
				$.ajax(
				{
					type 	: 'post',
					url 	: '{{ route("account-concentrated.get-accounts") }}',
					data 	: {'idEnterprise':idEnterprise},
					success : function(data)
					{
						$('#tbody-account').append(data)
					},
					error : function()
					{
						swal('','Sucedió un error, por favor intente de nuevo.','error');
						$('#tbody-account').html('');
					}
				});
			})
		});
	</script>
@endsection