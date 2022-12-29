@extends('layouts.child_module')
@section('data')
	<div class="sm:text-center text-left my-5">
		A continuación podrá verificar la información de la solicitud antes de continuar con el proceso:
	</div>
	@php	
		$modelTable = [
			["Folio:", 						$request->folio],
			["Título y fecha:", 			htmlentities($request->loan->first()->title).'  '.Carbon\Carbon::createFromFormat('Y-m-d',$request->loan->first()->datetitle)->format('d-m-Y')],
			["Solicitante:",				$request->requestUser->fullName()],
			["Elaborado por:",				$request->elaborateUser->fullName()],
			["Empresa:",					$request->requestEnterprise->name],
			["Dirección:", 					$request->requestDirection->name],
			["Departamento:", 				$request->requestDepartment->name],
			["Clasificación del gasto:",	$request->accounts->account.' - '.$request->accounts->description]
		];
 	@endphp
	@component('components.templates.outputs.table-detail', [
		"modelTable" => $modelTable,
		"title"		=> "Detalles de la Solicitud"
	])
	@endcomponent
	@component('components.labels.title-divisor') DATOS DEL SOLICITANTE @endcomponent
	@php
		$valueLoan		= '';
		$valueReference	= '';
		$valueAmount	= '';
		foreach($request->loan as $loan)
		{
			$valueLoan 		= isset($loan->paymentMethod->method) ? $loan->paymentMethod->method : '---';
			$valueReference = ($loan->reference != "" ? htmlentities($loan->reference) : "---");
			$valueAmount	= '$ '.number_format($loan->amount,2);
		}
		$valueBank 		= '';
		$valueAlias		= '';
		$valueCard		= '';
		$valueClabe 	= '';
		$valueAccount	= '';
		foreach($request->loan as $loan)
		{
			foreach(App\Employee::join('banks','employees.idBanks','banks.idBanks')->where('employees.idUsers',$loan->idUsers)->get() as $bank)
			{
				if($loan->idEmployee == $bank->idEmployee)
				{
					$valueBank 		= $bank->description;
					$valueAlias		= $bank->alias!=null ? $bank->alias : '---';
					$valueCard		= $bank->cardNumber!=null ? $bank->cardNumber : '---';
					$valueClabe 	= $bank->clabe!=null ? $bank->clabe : '---';
					$valueAccount	= $bank->account!=null ? $bank->account : '---';
				}
			}
		}
		$modelTable = [
			"Nombre"			=> $request->requestUser->fullName(),
			"Forma de pago"		=> $valueLoan,
			"Referencia"		=> $valueReference,
			"Importe"			=> $valueAmount,
			"Banco"				=> $valueBank,
			"Alias"				=> $valueAlias,
			"Número de tarjeta"	=> $valueCard,
			"CLABE"				=> $valueClabe,
			"Número de cuenta"	=> $valueAccount
		];
	@endphp
	@component('components.templates.outputs.table-detail-single',["modelTable" => $modelTable]) @endcomponent
	@component('components.inputs.input-text')
		@slot('attributeEx')
			type="hidden" name="employee_number" id="efolio" placeholder="Número de empleado" value="@foreach($request->loan as $loan){{ $loan->idUsers }}@endforeach"
		@endslot
		@slot('classEx')
			employee_number
		@endslot
	@endcomponent
	@component('components.labels.title-divisor') DATOS DE REVISIÓN @endcomponent
	@php
		$reviewAccount = App\Account::find($request->accountR);
		$labels = '';
		if(count($request->labels))
		{
			foreach($request->labels as $label)
			{
				$labels	.= $label->description.', ';
			}
		}
		else
		{
			$labels = 'Sin etiqueta';
		}
		$comment = '';
		if($request->checkComment == "")
		{
			$comment = 'Sin comentarios';
		}
		else
		{
			$comment = $request->checkComment;
		}
		$modelTable = [
			"Revisó"					=> $request->reviewedUser->name.' '.$request->reviewedUser->last_name.' '.$request->reviewedUser->scnd_last_name,
			"Nombre de la Empresa"		=> $request->requestEnterprise->name,
			"Nombre de la Dirección"	=> $request->reviewedDirection->name,
			"Nombre del Departamento"	=> $request->requestDepartment->name,
			"Clasificación del gasto"	=> $reviewAccount->account.' '.$reviewAccount->description,
			"Etiquetas"					=> $labels,
			"Comentarios"				=> htmlentities($comment),
		];
	@endphp
	@component('components.templates.outputs.table-detail-single',['modelTable' => $modelTable]) @endcomponent
	@if($request->idAuthorize != "")
		@component('components.labels.title-divisor') DATOS DE AUTORIZACIÓN @endcomponent
		@php
			$userAuthorize 	= '';
			$comments		= '';
			foreach(App\User::where('id',$request->idAuthorize)->get() as $authorize)
			{
				$userAuthorize = $authorize->name.' '.$authorize->last_name.' '.$authorize->scnd_last_name;
			}
			if($request->authorizeComment == "")
			{
				$comments = "Sin comentarios";
			}
			else
			{
				$comments = $request->authorizeComment;
			}
			$modelTable = [
				"Autorizó"		=> $userAuthorize,
				"Comentarios"	=> htmlentities($comments),
			];
		@endphp
		@component('components.templates.outputs.table-detail-single',['modelTable' => $modelTable]) @endcomponent
	@endif
	@component('components.forms.form', [ "attributeEx" => "method=\"POST\" id=\"container-alta\" action=\"".route('loan.authorization.update', $request->folio)."\"", "methodEx" => "PUT", "files"=>true])
		@if($request->status == 8)
			@component('components.labels.title-divisor') DOCUMENTO DE AUTORIZACIÓN FIRMADO @endcomponent
			@component('components.containers.container-form')
			<div class="col-span-4 md:col-start-2 md:col-span-2 md:col-end-4">
					@component('components.labels.label') Seleccione un documento: @endcomponent
					@component('components.documents.upload-files', [
							"noDelete" 				=> "true",
							"classExInput"			=> "pathActioner",
							"attributeExInput"		=> "type=\"file\" name=\"path\" accept=\".pdf,.jpg,.png\"",
							"attributeExRealPath" 	=> "type=\"hidden\" name=\"realPath\"",
							"classExRealPath"		=> "path"
						])
					@endcomponent
				</div>
			@endcomponent
		@endif
		@if($request->status == 4)
			<div class="my-4">
				@component('components.containers.container-approval')
					@slot('attributeExButton')
						name="status" id="aprobar" value="8"
					@endslot
					@slot('classExButton')
						approve
					@endslot
					@slot('attributeExButtonTwo')
						name="status" id="rechazar" value="7"
					@endslot
					@slot('classExButtonTwo')
						refuse
					@endslot
				@endcomponent
			</div>
			<div id="aceptar" class="hidden">
				@component('components.labels.label') Comentarios (opcional): @endcomponent
				@component('components.inputs.text-area')
					@slot('attributeEx')
						cols="90" rows="6" name="authorizeCommentA"
					@endslot					
				@endcomponent
			</div>
		@endif
		<div class="w-full mt-4 grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mb-6">
			@component('components.buttons.button', [ "variant" => "primary" ])
				@slot('attributeEx')
					type="submit"
					name="enviar"
				@endslot
				@slot('classEx')
					text-center
					w-48
					md:w-auto
				@endslot
					ENVIAR SOLICITUD
			@endcomponent
			@component('components.buttons.button', ["variant"=>"reset", "buttonElement"=>"a"])
				@slot('classEx')
					load-actioner
					text-center
					w-48
					md:w-auto
				@endslot
				@slot('attributeEx')
					@if(isset($option_id)) 
						href="{{ url(getUrlRedirect($option_id)) }}" 
					@else 
						href="{{ url(getUrlRedirect($child_id)) }}" 
					@endif
				@endslot
				REGRESAR
			@endcomponent
		</div>
	@endcomponent
@endsection

@section('scripts')
	<script src="{{ asset('js/jquery-ui.js') }}"></script>
	<script src="{{ asset('js/jquery.numeric.js') }}"></script>
	<script>
		$(document).ready(function()
		{
			@if($request->status == 4)
				$.validate(
				{
					form: '#container-alta',
					onSuccess : function($form)
					{
						if($('input[name="status"]').is(':checked'))
						{
							swal('Cargando',{
								icon: '{{ url(getenv('LOADING_IMG')) }}',
								button: false,
								closeOnClickOutside: false,
								closeOnEsc: false
							});
							return true;
						}
						else
						{
							swal('', 'Debe seleccionar al menos un estado', 'error');
							return false;
						}
					}
				});
			@elseif($request->status == 8)
				$.validate(
				{
					form: '#container-alta',
					onSuccess : function($form)
					{
						if($('input[name="path"]').val() != "")
						{
							swal('Cargando',{
								icon: '{{ url(getenv('LOADING_IMG')) }}',
								button: false,
								closeOnClickOutside: false,
								closeOnEsc: false
							});
							return true;
						}
						else
						{
							swal('', 'Debe cargar un documento', 'error');
							return false;
						}
					}
				});
			@endif
			 
			$('.card_number,.destination_account,.destination_key,.employee_number').numeric(false);    // números
			$('.amount,.importe',).numeric({ altDecimal: ".", decimalPlaces: 2 });
		 
			$(document).on('change','input[name="status"]',function()
			{
				$("#aceptar").slideDown("slow");
			})
			.on('change','.pathActioner',function(e)
			{
				filename		= $(this);
				uploadedName 	= $(this).parent('.uploader-content').siblings('input[name="realPath"]');
				extention		= /\.jpg|\.png|\.jpeg|\.pdf/i;
				
				if (filename.val().search(extention) == -1)
				{
					swal('', 'El tipo de archivo no es soportado, por favor seleccione una imagen jpg, png o un archivo pdf', 'warning');
					$(this).val('');
				}
				else if (this.files[0].size>315621376)
				{
					swal('', 'El tamaño máximo de su archivo no debe ser mayor a 300Mb', 'warning');
				}
				else
				{
					$(this).css('visibility','hidden').parent('.uploader-content').addClass('loading').removeClass(function (index, css)
					{
						return (css.match (/\bimage_\S+/g) || []).join(' '); // removes anything that starts with "image_"
					});
					formData	= new FormData();
					formData.append(filename.attr('name'), filename.prop("files")[0]);
					formData.append(uploadedName.attr('name'),uploadedName.val());
					$.ajax(
					{
						type		: 'post',
						url			: '{{ route("loan.upload") }}',
						data		: formData,
						contentType	: false,
						processData	: false,
						success		: function(r)
						{
							if(r.error=='DONE')
							{
								$(e.currentTarget).removeAttr('style').parent('.uploader-content').removeClass('loading').addClass('image_success');
								$(e.currentTarget).parent('.uploader-content').siblings('input[name="realPath"]').val(r.path);
							}
							else
							{
								swal('',r.message, 'error');
								$(e.currentTarget).removeAttr('style').parent('.uploader-content').removeClass('loading');
								$(e.currentTarget).val('');
								$(e.currentTarget).parent('.uploader-content').siblings('input[name="realPath"]').val('');
							}
						},
						error: function()
						{
							swal('', 'Ocurrió un error durante la carga del archivo, intente de nuevo, por favor', 'error');
							$(e.currentTarget).removeAttr('style').parent('.uploader-content').removeClass('loading');
							$(e.currentTarget).val('');
							$(e.currentTarget).parent('.uploader-content').siblings('input[name="realPath"]').val('');
						}
					})
				}
			});
		});
	</script>
@endsection