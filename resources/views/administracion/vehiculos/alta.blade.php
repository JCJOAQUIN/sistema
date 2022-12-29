@extends('layouts.child_module')
@section('data')
	@if(isset($vehicle))
		@component("components.forms.form",["attributeEx" => "id=\"form-vehicles\" method=\"post\" action=\"".route('vehicle.update',$vehicle->id)."\"", "methodEx" => "PUT"])		
	@else
		@component("components.forms.form",["attributeEx" => "id=\"form-vehicles\" method=\"post\" action=\"".route('vehicle.store')."\""])
	@endif
		@component('components.labels.title-divisor') Especificaciones Técnicas @endcomponent
		@component('components.containers.container-form')
			<div class="col-span-2">
				@component('components.labels.label') Marca: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" data-validation="required" placeholder="Ingrese la marca" name="brand" @isset($vehicle) value="{{ $vehicle->brand }}" @endisset
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Submarca (Opcional): @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" placeholder="Ingrese la submarca" name="sub_brand" @isset($vehicle) value="{{ $vehicle->sub_brand }}" @endisset
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Modelo: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" data-validation="required" placeholder="Ingrese el modelo" name="model" @isset($vehicle) value="{{ $vehicle->model }}" @endisset
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Número de serie: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text"
						name="serial_number"
						data-validation="server"
						placeholder="Ingrese el número de serie"
						data-validation-url="{{ route("vehicle.validate-serial-number") }}"
						@isset($vehicle) value="{{ $vehicle->serial_number }}" data-validation-req-params="{{json_encode(array('oldSerialNumber'=>$vehicle->serial_number))}}" @endisset
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Placas: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" data-validation="required" placeholder="Ingrese las placas" name="plates" @isset($vehicle) value="{{ $vehicle->plates }}" @endisset
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Estado: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" data-validation="required" placeholder="Ingrese un estado" name="vehicle_status" @isset($vehicle) value="{{ $vehicle->vehicle_status }}" @endisset
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2 md:col-span-4 grid grid-cols-1 md:grid-cols-2 gap-6 documents_technical_specifications form-content-tsd"></div>
			<div class="col-span-2 md:col-span-4 space-x-2 text-center md:text-left">
				@component('components.buttons.button', ["variant" => "warning"])
					@slot('attributeEx')
						id="add_technical_specifications_document" type="button"
					@endslot
					@slot('classEx')
						mt-4
					@endslot
						<span class="icon-plus"></span>
						<span>Anexar documento</span>
				@endcomponent
			</div>
		@endcomponent
		@if(isset($vehicle) && $vehicle->documentsTechnical()->exists())
			@php
				$body		= [];
				$modelBody	= [];
				$modelHead	= 
				[
					[
						["value" => "#"],
						["value" => "Nombre"],
						["value" => "Documentos"],
						["value" => "Acción"]
					]
				];

				if(isset($vehicle))
				{
					foreach($vehicle->documentsTechnical as $key=>$doc)
					{
						$body = [ "classEx" => "body-row",
							[
								"classEx"	=> "count_doc_technical",
								"content"	=>
								[
									"label" => $key+1
								]
							],
							[
								"content" =>
								[
									[
										"label" => $doc->name
									],
									[
										"kind" 			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"hidden\" value=\"".$doc->id."\"",
										"classEx"		=> "vehicle_document_id"
									]
								]
							],
							[
								"content" =>
								[
									"kind"			=> "components.buttons.button",
									"variant"		=> "dark-red",
									"buttonElement"	=> "a",
									"attributeEx"	=> "target=\"_blank\" title=\"".$doc->name."\"".' '."href=\"".url('docs/vehicles/'.$doc->path)."\"",
									"label"			=> "PDF"
								]
							],
							[
								"content" =>
								[
									"kind"			=> "components.buttons.button",
									"variant"		=> "red",
									"attributeEx"	=> "type=\"button\"",
									"classEx"		=> "delete-document",
									"label"			=> "<span class=\"icon-x\"></span>"	
								]
							]
						];
						$modelBody[] = $body;
					}
				}
			@endphp
			@component('components.tables.table',[
				"modelBody" 		=> $modelBody,
				"modelHead" 		=> $modelHead,
				"attributeExBody"	=> "id=\"body_technical\"" 
			])
			@endcomponent
		@endif
		@component('components.labels.title-divisor') Kilometraje @endcomponent
		@component('components.containers.container-form')
			@slot("attributeEx")
				id="kilometraje-content"
			@endslot
			<div class="col-span-2">
				@component('components.labels.label') Fecha: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="hidden"
					@endslot
					@slot('classEx')
						num_kilometer
					@endslot
				@endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="hidden"
					@endslot
					@slot('classEx')
						id_kilometer
					@endslot
				@endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" readonly="readonly" placeholder="Ingrese la fecha"
					@endslot
					@slot('classEx')
						datepicker date_kilometer_start check-empty-input
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Kilometraje: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" placeholder="Ingrese el número de kilómetros" name="start_kilometer"
					@endslot
					@slot('classEx')
						start_kilometer check-empty-input
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2 md:col-span-4 space-x-2 text-center md:text-left">
				@component('components.buttons.button', ["variant" => "warning"])
					@slot('attributeEx')
						id="add_kilometer" type="button"
					@endslot
						<span class="icon-plus"></span>
						<span>Agregar registro</span>
				@endcomponent
			</div>
		@endcomponent
		@php
			$body		= [];
			$modelBody	= [];
			$modelHead	= [ "#", "Fecha", "Kilometraje", "Acción"];
		
			if(isset($vehicle))
			{
				foreach ($vehicle->dataKilometers as $key=>$kilometers)
				{
					$body = [ "classEx" => "body-kilometer",
						[
							"classEx"	=> "count_kilometer",
							"content"	=>
							[
								[
									"label" => $key+1
								]
							]
						],
						[
							"content"	=>
							[
								[
									"label" => isset($kilometers->date_kilometer) ? Carbon\Carbon::createFromFormat('Y-m-d',$kilometers->date_kilometer)->format('d-m-Y') : '---'
								],
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" name=\"t_init_date[]\" value=\"".Carbon\Carbon::createFromFormat('Y-m-d',$kilometers->date_kilometer)->format('d-m-Y')."\"",
									"classEx"		=> "t_init_date"
								],
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" name=\"t_id_kilometer[]\" value=\"".$kilometers->id."\"",
									"classEx"		=> "t_id_kilometer"
								]
							]
						],
						[
							"content" =>
							[
								[
									"label" => $kilometers->kilometer
								],
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" name=\"t_init_kilometer[]\" value=\"".$kilometers->kilometer."\"",
									"classEx"		=> "t_init_kilometer"
								]
							]
						],
						[
							"content" =>
							[
								[
									"kind"			=> "components.buttons.button",
									"variant"		=> "success",
									"attributeEx"	=> "type=\"button\" title=\"Editar kilometraje\"",
									"classEx"		=> "edit-kilometer",
									"label"			=> "<span class=\"icon-pencil\"></span>"	
								]
							]
						]
					];
					$modelBody[] = $body;
				}
			}
		@endphp
		@component('components.tables.alwaysVisibleTable',[
			"modelBody" 		=> $modelBody,
			"modelHead" 		=> $modelHead,
			"attributeExBody"	=> "id=\"body_kilometer\"",
			"variant"			=> "default"
		])
		@endcomponent
		@component('components.labels.title-divisor') Datos del Propietario @endcomponent
		@component('components.containers.container-form')
			<div class="col-span-2">
				@component('components.labels.label') Tipo de propietario: @endcomponent
				@php
					$optionOwnerType = [];
					if(isset($vehicle) && $vehicle->owner_type == "fisica")
					{
						$optionOwnerType[] = ["value" => "fisica", "description" => "Física", "selected" => "selected"];
					}
					else
					{
						$optionOwnerType[] = ["value" => "fisica", "description" => "Física"];
					}
					if(isset($vehicle) && $vehicle->owner_type == "moral")
					{
						$optionOwnerType[] = ["value" => "moral", "description" => "Moral", "selected" => "selected"];
					}
					else
					{
						$optionOwnerType[] = ["value" => "moral", "description" => "Moral"];
					}
				@endphp
				@component('components.inputs.select', ['options' => $optionOwnerType])
					@slot('attributeEx')
						name="owner_type" multiple="multiple" data-validation="required"
					@endslot
					@slot('classEx')
						owner_type removeselect
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') ¿Es nuevo o existente en el sistema? @endcomponent
				@php
					$optionOwnerExternal = [];
					if(isset($vehicle))
					{
						$optionOwnerExternal[] = ["value" => "existente", "description" => "Existente", "selected" => "selected"];
						$optionOwnerExternal[] = ["value" => "nuevo", "description" => "Nuevo"];
					}
					else
					{
						$optionOwnerExternal[] = ["value" => "existente", "description" => "Existente"];
						$optionOwnerExternal[] = ["value" => "nuevo", "description" => "Nuevo"];
					}
				@endphp
				@component('components.inputs.select', ['options' => $optionOwnerExternal])
					@slot('attributeEx')
						name="owner_external" multiple="multiple" data-validation="required"
					@endslot
					@slot('classEx')
						owner_external removeselect
					@endslot
				@endcomponent
			</div>
			<div class="md:col-span-4 col-span-2 grid grid-cols-1 md:grid-cols-2 gap-6 select-owner">
				<div class="div_owner">
					@component('components.labels.label') Propietario: @endcomponent
					@php
						$optionOwner = [];
						if(isset($vehicle) && $vehicle->dataOwnerExternal()->exists())
						{
							foreach(App\VehicleOwner::where('type',$vehicle->dataOwnerExternal->type)->orderName()->get() as $owner)
							{
								if(isset($vehicle) && $vehicle->vehicles_owners_id == $owner->id)
								{
									$optionOwner[] = ['value' => $owner->id, 'description' => $owner->fullName(), 'selected' => 'selected'];
								}
								else
								{
									$optionOwner[] = ['value' => $owner->id, 'description' => $owner->fullName()];
								}
							}
						}
						else
						{
							foreach(App\VehicleOwner::orderName()->get() as $owner)
							{
								if(isset($vehicle) && $vehicle->vehicles_owners_id == $owner->id)
								{
									$optionOwner[] = ['value' => $owner->id, 'description' => $owner->name, 'selected' => 'selected'];
								}
								else
								{
									$optionOwner[] = ['value' => $owner->id, 'description' => $owner->name];
								}
							}
						}
					@endphp
					@component('components.inputs.select', ['options' => $optionOwner])
						@slot('attributeEx')
							name="owner_exists" multiple="multiple" data-validation="required"
						@endslot
						@slot('classEx')
							owner_exists removeselect
						@endslot
					@endcomponent
				</div>
			</div>
			<div class="md:col-span-4 col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4 form-owner-moral">
				<div>
					@component('components.labels.label') Razón Social: @endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							placeholder="Ingrese la razón social" type="text" name="moral_name" @if(isset($vehicle) && $vehicle->dataOwnerExternal()->exists()) value="{{ $vehicle->dataOwnerExternal->name }}" @endif data-validation="required"
						@endslot
						@slot('classEx')
							general-class type_moral
						@endslot
					@endcomponent
				</div>
				<div>
					@component('components.labels.label') RFC (Opcional): @endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							placeholder="Ingrese el RFC con homoclave" type="text" name="moral_rfc" data-validation="server" data-validation-url="{{ route('vehicle.validate-rfc') }}" @if(isset($vehicle) && $vehicle->dataOwnerExternal()->exists()) value="{{ $vehicle->dataOwnerExternal->rfc}}" data-validation-req-params="{{json_encode(array('oldRfc'=> $vehicle->dataOwnerExternal->rfc))}}" @endif
						@endslot
						@slot('classEx')
							general-class
						@endslot
					@endcomponent
				</div>
				<div>
					@component('components.labels.label') Correo electrónico (Opcional): @endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
						placeholder="Ingrese el correo electrónico" type="text" name="moral_email" data-validation="email"  @if(isset($vehicle) && $vehicle->dataOwnerExternal()) value="{{ $vehicle->dataOwnerExternal->email }}" @endif data-validation-optional="true"
						@endslot
						@slot('classEx')
							general-class
						@endslot
					@endcomponent
				</div>
				<div>
					@component('components.labels.label') Calle (Opcional): @endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							placeholder="Ingrese la calle" type="text" name="moral_street" @if(isset($vehicle) && $vehicle->dataOwnerExternal()->exists()) value="{{ $vehicle->dataOwnerExternal->street }}" @endif
						@endslot
						@slot('classEx')
							general-class
						@endslot
					@endcomponent
				</div>
				<div>
					@component('components.labels.label') Número (Opcional): @endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							type="text" name="moral_number" placeholder="Ingrese el número" @if(isset($vehicle) && $vehicle->dataOwnerExternal()->exists()) value="{{ $vehicle->dataOwnerExternal->number }}" @endif
						@endslot
						@slot('classEx')
							general-class
						@endslot
					@endcomponent
				</div>
				<div>
					@component('components.labels.label') Colonia (Opcional): @endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							type="text" name="moral_colony" placeholder="Ingrese la colonia" @if(isset($vehicle) && $vehicle->dataOwnerExternal()->exists()) value="{{ $vehicle->dataOwnerExternal->colony }}" @endif
						@endslot
						@slot('classEx')
							general-class
						@endslot
					@endcomponent
				</div>
				<div>
					@component('components.labels.label') Código Postal (Opcional): @endcomponent
					@php
						$optionCP = [];
						if(isset($vehicle) && $vehicle->dataOwnerExternal->cp != "")
						{
							$optionCP[] = ["value" => $vehicle->dataOwnerExternal->cp, "description" => $vehicle->dataOwnerExternal->cp, "selected" => "selected"];
						}
					@endphp
					@component('components.inputs.select',["options" => $optionCP])
						@slot('attributeEx')
							name="moral_cp" id="cp" placeholder="Seleccione el código postal" multiple="multiple"
						@endslot
						@slot('classEx')
							general-class
						@endslot
					@endcomponent
				</div>
				<div>
					@component('components.labels.label') Ciudad (Opcional): @endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							type="text" name="moral_city" placeholder="Ingrese la ciudad"  @if(isset($vehicle) && $vehicle->dataOwnerExternal()->exists()) value="{{ $vehicle->dataOwnerExternal->city }}" @endif
						@endslot
						@slot('classEx')
							general-class
						@endslot
					@endcomponent
				</div>
				<div>
					@component('components.labels.label') Estado (Opcional): @endcomponent
					@php
						$optionState = [];
						if(isset($vehicle))
						{
							$e = App\State::where('idstate',$vehicle->dataOwnerExternal->state_idstate)->first();
							if($vehicle->dataOwnerExternal()->exists() && $e != '')
							{
								$optionState[] = ["value" => $e->idstate, "description" => $e->description, "selected" => "selected"];
							}
						}
					@endphp
					@component('components.inputs.select', ['options' => $optionState])
						@slot('attributeEx')
							name="moral_state" multiple
						@endslot
						@slot('classEx')
							state-moral removeselect
						@endslot
					@endcomponent
				</div>
			</div>	
			<div class="md:col-span-4 col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4 form-owner-physical">
				<div>
					@component('components.labels.label') Nombre(s): @endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							placeholder="Ingrese el nombre" type="text" name="physical_name" @if(isset($vehicle) && $vehicle->dataOwnerExternal()->exists()) value="{{ $vehicle->dataOwnerExternal->name }}" @endif data-validation="required"
						@endslot
						@slot('classEx')
							general-class
						@endslot
					@endcomponent
				</div>
				<div class="col-span-1 mb-4">
					@component('components.labels.label') Apellido Paterno: @endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							placeholder="Ingrese el apellido paterno" type="text" name="physical_last_name" @if(isset($vehicle) && $vehicle->dataOwnerExternal()->exists()) value="{{ $vehicle->dataOwnerExternal->last_name }}" @endif data-validation="required"
						@endslot
						@slot('classEx')
							general-class
						@endslot
					@endcomponent
				</div>
				<div>
					@component('components.labels.label') Apellido Materno (Opcional): @endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							placeholder="Ingrese el apellido materno" type="text" name="physical_scnd_last_name" @if(isset($vehicle) && $vehicle->dataOwnerExternal()->exists()) value="{{ $vehicle->dataOwnerExternal->scnd_last_name }}" @endif
						@endslot
						@slot('classEx')
							general-class
						@endslot
					@endcomponent
				</div>
				<div class="curp-form">
					@component('components.labels.label') CURP (Opcional): @endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							placeholder="Ingrese el CURP" type="text" name="physical_curp" data-validation="server" data-validation-url="{{ route('vehicle.validate-curp') }}" @if(isset($vehicle) && $vehicle->dataOwnerExternal()->exists()) value="{{ $vehicle->dataOwnerExternal->curp}}" data-validation-req-params="{{json_encode(array('oldCurp'=> $vehicle->dataOwnerExternal->curp))}}" @endif
						@endslot
						@slot('classEx')
							general-class
						@endslot
					@endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							type="hidden" name="oldCurp" @if(isset($vehicle)) value="{{ $vehicle->dataOwnerExternal->curp }}" @endif
						@endslot
						@slot('classEx')
							general-class
						@endslot
					@endcomponent
				</div>
				<div>
					@component('components.labels.label') RFC (opcional): @endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							placeholder="Ingrese el RFC con homoclave" type="text" name="physical_rfc" data-validation="server" data-validation-url="{{ route('vehicle.validate-rfc') }}" @if(isset($vehicle)) value="{{ $vehicle->dataOwnerExternal->rfc}}" data-validation-req-params="{{json_encode(array('oldRfc'=> $vehicle->dataOwnerExternal->rfc))}}" @endif
						@endslot
						@slot('classEx')
							general-class
						@endslot
					@endcomponent
				</div>
				<div>
					@component('components.labels.label') #IMSS (opcional): @endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							placeholder="Ingrese el número IMSS" type="text" name="physical_imss" data-validation="custom" data-validation-regexp="^(\d{10}-\d{1})$" data-validation-error-msg="Por favor, ingrese un # IMSS válido" data-validation-optional="true" @if(isset($vehicle) && ($vehicle->owner_type == "fisica") && ($vehicle->owner_external == "nuevo")) value="{{$vehicle->dataOwnerExternal != "" ? $vehicle->dataOwnerExternal->imss : $vehicle->dataOwnerPhysical->imss}}" @endif
						@endslot
						@slot('classEx')
							general-class
						@endslot
					@endcomponent
				</div>
				<div>
					@component('components.labels.label') Correo electrónico (Opcional): @endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							placeholder="Ingrese el correo electrónico" type="text" name="physical_email" data-validation="email"  @if(isset($vehicle) && $vehicle->dataOwnerExternal()->exists()) value="{{ $vehicle->dataOwnerExternal->email }}" @endif data-validation-optional="true"
						@endslot
						@slot('classEx')
							general-class
						@endslot
					@endcomponent
				</div>
				<div>
					@component('components.labels.label') Calle (Opcional): @endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							placeholder="Ingrese la calle" type="text" name="physical_street" @if(isset($vehicle) && $vehicle->dataOwnerExternal()->exists()) value="{{ $vehicle->dataOwnerExternal->street }}" @endif
						@endslot
						@slot('classEx')
							general-class
						@endslot
					@endcomponent
				</div>
				<div>
					@component('components.labels.label') Número (Opcional): @endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							type="text" name="physical_number" placeholder="Ingrese el número" @if(isset($vehicle) && $vehicle->dataOwnerExternal()->exists()) value="{{ $vehicle->dataOwnerExternal->number }}" @endif
						@endslot
						@slot('classEx')
							general-class
						@endslot
					@endcomponent
				</div>
				<div>
					@component('components.labels.label') Colonia (Opcional): @endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							type="text" name="physical_colony" placeholder="Ingrese la colonia" @if(isset($vehicle) && $vehicle->dataOwnerExternal()->exists()) value="{{ $vehicle->dataOwnerExternal->colony }}" @endif
						@endslot
						@slot('classEx')
							general-class
						@endslot
					@endcomponent
				</div>
				<div>
					@component('components.labels.label') Código Postal (Opcional): @endcomponent
					@php
						$option = [];
						if(isset($vehicle) && $vehicle->dataOwnerExternal()->exists() && $vehicle->dataOwnerExternal->cp != "")
						{
							$option[] = ["value" => $vehicle->dataOwnerExternal->cp, "description" => $vehicle->dataOwnerExternal->cp, "selected" => "selected"];
						}
					@endphp
					@component('components.inputs.select',["options" => $option])
						@slot('attributeEx')
							name="physical_cp" id="physical_cp" placeholder="Seleccione el código postal" multiple="multiple"
						@endslot
						@slot('classEx')
							general-class
						@endslot
					@endcomponent
				</div>
				<div>
					@component('components.labels.label') Ciudad (Opcional): @endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							type="text" name="physical_city" placeholder="Ingrese la ciudad"  @if(isset($vehicle) && $vehicle->dataOwnerExternal()->exists()) value="{{ $vehicle->dataOwnerExternal->city }}" @endif
						@endslot
						@slot('classEx')
							general-class
						@endslot
					@endcomponent
				</div>
				<div>
					@component('components.labels.label') Estado (Opcional): @endcomponent
					@php
						$optionStateP = [];
						if(isset($vehicle))
						{
							$e = App\State::where('idstate',$vehicle->dataOwnerExternal->state_id)->first();
							if($vehicle->dataOwnerExternal()->exists() && $e != '')
							{
								$optionStateP[] = ["value" => $e->idstate, "description" => $e->description, "selected" => "selected"];
							}
						}
					@endphp
					@component('components.inputs.select', ['options' => $optionStateP])
						@slot('attributeEx')
							name="physical_state" multiple
						@endslot
						@slot('classEx')
							state-physical removeselect
						@endslot
					@endcomponent
				</div>
			</div>
			<div class="col-span-2 md:col-span-4 grid grid-cols-1 md:grid-cols-2 gap-6 documents_owner form-content-od"></div>
			<div class="col-span-2 md:col-span-4 space-x-2 text-center md:text-left">
				@component('components.buttons.button', ["variant" => "warning"])
					@slot('attributeEx')
						id="add_owner_document" type="button"
					@endslot
					@slot('classEx')
						mt-4
					@endslot
						<span class="icon-plus"></span>
						<span>Anexar documento</span>
				@endcomponent
			</div>
		@endcomponent
		@if(isset($vehicle) && $vehicle->documentsOwner()->exists())
			@php
				$body		= [];
				$modelBody	= [];
				$modelHead	= 
				[
					[
						["value" => "#"],
						["value" => "Nombre"],
						["value" => "Documentos"],
						["value" => "Acción"]
					]
				];
				if(isset($vehicle))
				{
					foreach($vehicle->documentsOwner as $key=>$doc)
					{
						$body = [ "classEx" => "body-row",
							[ 
								"classEx"	=> "count_doc_owner",
								"content" 	=>
								[
									"label" => $key+1 
								]
							],
							[
								"content" =>
								[
									[
										"label" => $doc->name
									],
									[
										"kind"			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"hidden\" value=\"".$doc->id."\"",
										"classEx"		=> "vehicle_document_id"
									]
								]
							],
							[
								"content" =>
								[
									"kind"			=> "components.buttons.button",
									"variant"		=> "dark-red",
									"buttonElement"	=> "a",
									"attributeEx"	=> "target=\"_blank\" title=\"".$doc->name."\"".' '."href=\"".url('docs/vehicles/'.$doc->path)."\"",
									"label"			=> "PDF"
								]
							],
							[
								"content" =>
								[
									"kind"			=> "components.buttons.button",
									"variant"		=> "red",
									"attributeEx"	=> "type=\"button\"",
									"classEx"		=> "delete-document",
									"label"			=> "<span class=\"icon-x\"></span>"
								]
							]
						];
						$modelBody[] = $body;
					}
				}
			@endphp
			@component('components.tables.table', [
				"modelBody" 		=> $modelBody,
				"modelHead" 		=> $modelHead,
				"attributeExBody"	=> "id=\"body_owner\""
			])
			@endcomponent
		@endif
		@component('components.labels.title-divisor') Datos de Combustible @endcomponent
		@component('components.containers.container-form')
			<div class="col-span-2"> 
				@component('components.labels.label') Tipo de Combustible: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" placeholder="Ingrese el tipo de combustible" @isset($vehicle) value="{{ $vehicle->fuel_type }}" @endisset
					@endslot
					@slot('classEx')
						fuel_type check-empty-input
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2"> 
				@component('components.labels.label') Tag (Opcional): @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" placeholder="Ingrese el tag" @isset($vehicle) value="{{ $vehicle->tag }}" @endisset
					@endslot
					@slot('classEx')
						tag
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2"> 
				@component('components.labels.label') Fecha: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" readonly="readonly" placeholder="Ingrese la fecha"
					@endslot
					@slot('classEx')
						datepicker fuel_date check-empty-input
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2"> 
				@component('components.labels.label') Total: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" placeholder="Ingrese el total"
					@endslot
					@slot('classEx')
						fuel_total check-empty-input
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2 md:col-span-4 grid grid-cols-1 md:grid-cols-2 gap-6 documents_fuel"></div>
			<div class="col-span-2 md:col-span-4 space-x-2 text-center md:text-left" >
				<div class="block">
					@component('components.inputs.input-text')
						@slot('classEx')
							fuel_id
						@endslot
						@slot('attributeEx')
							type="hidden" value="x"
						@endslot
					@endcomponent
					@component('components.buttons.button', ["variant" => "warning"])
						@slot('attributeEx')
							id="add_fuel_document" type="button"
						@endslot
						@slot('classEx')
							mt-4
						@endslot
							<span class="icon-plus"></span> 
							<span>Anexar documento</span>
					@endcomponent
					@component('components.buttons.button', ["variant" => "success"])
						@slot('attributeEx')
							id="add_fuel" type="button"
						@endslot
						@slot('classEx')
							mt-4
						@endslot
							<span class="icon-plus"></span> 
							<span>Agregar registro</span>
					@endcomponent	
				</div>
			</div>
		@endcomponent
		@php
			$body		= [];
			$modelBody	= [];
			$modelHead	= 
			[
				[
					["value" => "#"],
					["value" => "Tipo de combustible"],
					["value" => "Tag"],
					["value" => "Fecha"],
					["value" => "Total"],
					["value" => "Documentos"],
					["value" => "Acción"]
				]
			];

			if(isset($vehicle))
			{
				foreach($vehicle->fuel as $key=>$fuel)
				{
					$body = [ "classEx" => "body-fuel",
						[ 
							"classEx"	=> "count_fuel",
							"content"	=>
							[
								"label" => $key+1
							]
						],
						[ 
							"content" =>
							[
								[
									"label" => $fuel->fuel_type
								],
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" name=\"t_vehicle_fuel_id[]\" value=\"".$fuel->id."\"",
									"classEx"		=> "t_vehicle_fuel_id"
								],
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" name=\"t_fuel_type[]\" value=\"".$fuel->fuel_type."\""
								],
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" name=\"t_tag[]\" value=\"".$fuel->tag."\""
								],
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" name=\"t_fuel_date[]\" value=\"".Carbon\Carbon::createFromFormat('Y-m-d',$fuel->date)->format('d-m-Y')."\""
								],
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" name=\"t_fuel_total[]\" value=\"".$fuel->total."\""
								]
							]
						],
						[
							"content" => [ "label" => $fuel->tag != "" ? $fuel->tag : "---"]
						],
						[
							"content" => [ "label" => isset($fuel->date) ? Carbon\Carbon::createFromFormat('Y-m-d',$fuel->date)->format('d-m-Y') : '---' ]
						],
						[
							"content" => [ "label" => '$ '.number_format($fuel->total,2) ]
						]
					];
					$documentFuel = '';
					if($fuel->documents()->exists())
					{
						foreach($fuel->documents as $doc)
						{
							$documentFuel	.= '<div class="nowrap have-docs">';
							$documentFuel	.= view('components.buttons.button',
							[
								"variant"		=> "dark-red",
								"buttonElement"	=> "a",
								"attributeEx"	=> "target=\"_blank\" type=\"button\" title=\"".$doc->name."\"".' '."href=\"".url('docs/vehicles/'.$doc->path)."\"",
								"label"			=> "PDF",
							])->render();
							$documentFuel	.= '<div><label>'.$doc->name.'</label></div>';
							$documentFuel	.= view('components.inputs.input-text',
							[
								"classEx"		=> "edit_fuel_id_doc",
								"attributeEx"	=> "type=\"hidden\" value=\"".$doc->id."\"" 
							])->render();
							$documentFuel	.= view('components.inputs.input-text',
							[
								"classEx"		=> "edit_fuel_name_document",
								"attributeEx"	=> "type=\"hidden\" value=\"".$doc->name."\""
							])->render();
							$documentFuel	.= view('components.inputs.input-text',
							[
								"classEx"		=> "edit_fuel_path",
								"attributeEx"	=> "type=\"hidden\" value=\"".$doc->path."\""
							])->render();
							$documentFuel	.= '</div>';
						}
					}
					else
					{
						$documentFuel	.= '<div class="nowrap">';
						$documentFuel	.= '<label>No hay documentos</label>';
						$documentFuel	.= '</div>';
					}
					$body[] = [ "content" 	=> [ "label" => $documentFuel ]];
					$body[] = 
					[
						"content" =>
						[
							[
								"kind"			=> "components.buttons.button",
								"variant"		=> "success",
								"attributeEx"	=> "type=\"button\" title=\"Editar registro\"",
								"classEx"		=> "edit-fuel",
								"label"			=> "<span class=\"icon-pencil\"></span>"
							],
							[
								"kind"			=> "components.buttons.button",
								"variant"		=> "red",
								"attributeEx"	=> "type=\"button\" title=\"Eliminar registro\"",
								"classEx"		=> "delete-fuel",
								"label"			=> "<span class=\"icon-x\"></span>"	
							]
						]
					];
					$modelBody[] = $body;
				}
			}
		@endphp
		@component('components.tables.table',[
			"modelBody" 		=> $modelBody,
			"modelHead" 		=> $modelHead,
			"attributeExBody"	=> "id=\"body_fuel\"" 
		])
		@endcomponent
		@component('components.labels.title-divisor') Datos de Impuestos @endcomponent
		@component('components.containers.container-form')
			<div class="col-span-2"> 
				@component('components.labels.label') Fecha de Verificación: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" readonly="readonly" placeholder="Ingrese la fecha"
					@endslot
					@slot('classEx')
						date_verification check-empty-input
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2"> 
				@component('components.labels.label') Próxima Fecha de Verificación: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" readonly="readonly" placeholder="Ingrese una fecha"
					@endslot
					@slot('classEx')
						next_date_verification check-empty-input
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2"> 
				@component('components.labels.label') Monto Verificación: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" placeholder="Ingrese el total"
					@endslot
					@slot('classEx')
						total_verification check-empty-input
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2"> 
				@component('components.labels.label') Monto Gestoría (Opcional): @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" placeholder="Ingrese el total"
					@endslot
					@slot('classEx')
						monto_gestoria
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2 md:col-span-4 grid grid-cols-1 md:grid-cols-2 gap-6 documents_taxes"></div>
			<div class="col-span-2 md:col-span-4 space-x-2 text-center md:text-left">
				<div class="block">
					@component('components.inputs.input-text')
						@slot('classEx')
							taxes_id
						@endslot
						@slot('attributeEx')
							type="hidden" value="x"
						@endslot
					@endcomponent
					@component('components.buttons.button', ["variant" => "warning"])
						@slot('attributeEx')
							id="add_taxes_document" type="button"
						@endslot
						@slot('classEx')
							mt-4
						@endslot
							<span class="icon-plus"></span> 
							<span>Anexar documento</span>
					@endcomponent
					@component('components.buttons.button', ["variant" => "success"])
						@slot('attributeEx')
							id="add_taxes" type="button"
						@endslot
						@slot('classEx')
							mt-4
						@endslot
							<span class="icon-plus"></span> 
							<span>Agregar registro</span>
					@endcomponent
				</div>
			</div>
		@endcomponent
		@php
			$body		= [];
			$modelBody	= [];
			$modelHead	= 
			[
				[
					["value" => "#"],
					["value" => "Fecha de Verificación"],
					["value" => "Próxima Fecha de Verificación"],
					["value" => "Monto Total"],
					["value" => "Monto Gestoría"],
					["value" => "Documentos"],
					["value" => "Acción"]
				]
			];
			if(isset($vehicle))
			{
				foreach($vehicle->taxes as $key=>$tax)
				{
					$body = [ "classEx" => "body-taxes",
						[
							"classEx"	=> "count_taxes",
							"content"	=>
							[
								"label" => $key+1
							]
						],
						[
							"content"	=>
							[
								[
									"label" => isset($tax->date_verification) ? Carbon\Carbon::createFromFormat('Y-m-d',$tax->date_verification)->format('d-m-Y') : '---'
								],
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" name=\"t_vehicle_taxes_id[]\" value=\"".$tax->id."\"",
									"classEx"		=> "t_vehicle_taxes_id"
								],
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" name=\"t_date_verification[]\" value=\"".Carbon\Carbon::createFromFormat('Y-m-d',$tax->date_verification)->format('d-m-Y')."\""
								],
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" name=\"t_next_date_verification[]\" value=\"".Carbon\Carbon::createFromFormat('Y-m-d',$tax->next_date_verification)->format('d-m-Y')."\""
								],
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" name=\"t_total_verification[]\" value=\"".$tax->total."\""
								],
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" name=\"t_monto_gestoria[]\" value=\"".$tax->monto_gestoria."\""
								]
							]
						],
						[
							"content" => [ "label" => isset($tax->next_date_verification) ? Carbon\Carbon::createFromFormat('Y-m-d',$tax->next_date_verification)->format('d-m-Y') : '---' ]
						],
						[
							"content" => [ "label" => '$ '.number_format($tax->total,2) ]
						],
						[
							"content" => [ "label" => '$ '.number_format($tax->monto_gestoria,2) ]
						]
					];
					$docsTaxes = '';
					if($tax->documents()->exists())
					{
						foreach($tax->documents as $doc)
						{
							$docsTaxes	.= '<div class="nowrap have-docs">';
							$docsTaxes	.= view('components.buttons.button',
							[
								"variant"		=> "dark-red",
								"buttonElement"	=> "a",
								"attributeEx"	=> "target=\"_blank\" type=\"button\" title=\"".$doc->name."\"".' '."href=\"".url('docs/vehicles/'.$doc->path)."\"",
								"label"			=> "PDF"
							])->render();
							$docsTaxes	.= '<div><label>'.$doc->name.'</label></div>';
							$docsTaxes	.= view('components.inputs.input-text',
							[
								"classEx"		=> "edit_taxes_id_doc",
								"attributeEx"	=> "type=\"hidden\" value=\"".$doc->id."\""
							])->render();
							$docsTaxes	.= view('components.inputs.input-text',
							[
								"classEx"		=> "edit_taxes_name_document",
								"attributeEx"	=> "type=\"hidden\" value=\"".$doc->name."\"" 
							])->render();
							$docsTaxes	.= view('components.inputs.input-text',
							[
								"classEx"		=> "edit_taxes_path",
								"attributeEx"	=> "type=\"hidden\" value=\"".$doc->path."\"" 
							])->render();
							$docsTaxes	.= view('components.inputs.input-text',
							[
								"classEx"		=> "edit_taxes_date",
								"attributeEx"	=> "type=\"hidden\" value=\"".Carbon\Carbon::createFromFormat('Y-m-d',$doc->date)->format('d-m-Y')."\""
							])->render();
							$docsTaxes	.= '</div>';
						}
					}
					else
					{
						$docsTaxes	.= '<div class="nowrap">';
						$docsTaxes	.= '<div><label>No hay documentos</label></div>';
						$docsTaxes	.= '</div>';
					}
					$body[] = [ "content" => [ "label" => $docsTaxes ]];
					$body[] = 
					[
						"content" =>
						[
							[
								"kind"			=> "components.buttons.button",
								"variant"		=> "success",
								"attributeEx"	=> "type=\"button\" title=\"Editar impuesto\"",
								"classEx"		=> "edit-tax",
								"label"			=> "<span class=\"icon-pencil\"></span>"
							],
							[
								"kind"			=> "components.buttons.button",
								"variant"		=> "red",
								"attributeEx"	=> "type=\"button\" title=\"Eliminar registro\"",
								"classEx"		=> "delete-tax",
								"label"			=> "<span class=\"icon-x\"></span>"	
							]
						]
					];
					$modelBody[] = $body;
				} 
			}
		@endphp
		@component('components.tables.table',[
			"modelBody" 		=> $modelBody,
			"modelHead" 		=> $modelHead,
			"attributeExBody"	=> "id=\"body_taxes\"" 
		])
		@endcomponent
		@component('components.labels.title-divisor') Datos de Multas @endcomponent
		@component('components.containers.container-form')
			<div class="col-span-2 select-class">
				@component('components.labels.label') Conductor: @endcomponent
				@component('components.inputs.select',["options" => []])
					@slot('attributeEx')
						multiple
					@endslot
					@slot('classEx')
						fine_driver removeselect check-empty-select
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2 select-class"> 
				@component('components.labels.label') Estado de Multa: @endcomponent
				@php
					$optionM = [];
					$optionM[] = ["value" => "Pagado", "description" => "Pagado"];
					$optionM[] = ["value" => "No Pagado", "description" => "No Pagado"];
				@endphp
				@component('components.inputs.select',["options" => $optionM])
					@slot('attributeEx')
						multiple
					@endslot
					@slot('classEx')
						fine_status removeselect check-empty-select
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2"> 
				@component('components.labels.label') Fecha de Multa: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" readonly="readonly" placeholder="Ingrese la fecha"
					@endslot
					@slot('classEx')
						datepicker fine_date check-empty-input
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2"> 
				@component('components.labels.label') Fecha Límite de Pago: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" readonly="readonly" placeholder="Ingrese la fecha"
					@endslot
					@slot('classEx')
						datepicker fine_payment_limit_date check-empty-input
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2 div_payment_date"> 
				@component('components.labels.label') Fecha de Pago: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" readonly="readonly" placeholder="Ingrese la fecha"
					@endslot
					@slot('classEx')
						datepicker fine_payment_date check-empty-input
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2"> 
				@component('components.labels.label') Total: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" placeholder="Ingrese el total"
					@endslot
					@slot('classEx')
						fine_total check-empty-input
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2 md:col-span-4 grid grid-cols-1 md:grid-cols-2 gap-6 documents_fines"></div>
			<div class="col-span-2 md:col-span-4 space-x-2 text-center md:text-left">
				<div class="block">
					@component('components.inputs.input-text')
						@slot('attributeEx')
							type="hidden" value="x"
						@endslot
						@slot('classEx')
							fine_id
						@endslot
					@endcomponent
					@component('components.buttons.button', ["variant" => "warning"])
						@slot('attributeEx')
							id="add_fines_document" type="button"
						@endslot
						@slot('classEx')
							mt-4
						@endslot
							<span class="icon-plus"></span> 
							<span>Anexar documento</span>
					@endcomponent
					@component('components.buttons.button', ["variant" => "success"])
						@slot('attributeEx')
							id="add_fines" type="button"
						@endslot
						@slot('classEx')
							mt-4
						@endslot
							<span class="icon-plus"></span> 
							<span>Agregar registro</span>
					@endcomponent
				</div>
			</div>
		@endcomponent
		@php
			$body		= [];
			$modelBody	= [];
			$modelHead	= 
			[
				[
					["value" => "#"],
					["value" => "Conductor"],
					["value" => "Estado de Multa"],
					["value" => "Fecha de Multa"],
					["value" => "Fecha Límite de Pago"],
					["value" => "Fecha de Pago"],
					["value" => "Total"],
					["value" => "Documentos"],
					["value" => "Acción"]
				]
			];
			if (isset($vehicle)) 
			{
				foreach($vehicle->fines as $key=>$fine)
				{
					$limitDate 	= $fine->payment_limit_date != '' ? Carbon\Carbon::createFromFormat('Y-m-d',$fine->payment_limit_date)->format('d-m-Y') : '';
					$payDate	= $fine->payment_date != '' ? Carbon\Carbon::createFromFormat('Y-m-d',$fine->payment_date)->format('d-m-Y') : '';
					$body = [ "classEx" => "body-fines",
						[
							"classEx"	=> "count_fine",
							"content" 	=>
							[
								"label" => $key+1
							]
						],
						[
							"content"	=>
							[
								[
									"label" => $fine->driverData->fullName()
								],
								[
									"kind"			=> "components.inputs.input-text",
									"classEx"		=> "fine-driver-class",
									"attributeEx"	=> "type=\"hidden\" value=\"".$fine->driverData->fullName()."\""
								],
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" name=\"t_vehicle_fine_id[]\" value=\"".$fine->id."\"",
									"classEx"		=> "t_vehicle_fine_id"
								],
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" name=\"t_fine_driver[]\" value=\"".$fine->real_employee_id."\""
								],
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" name=\"t_fine_status[]\" value=\"".$fine->status."\""
								],
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" name=\"t_fine_date[]\" value=\"".Carbon\Carbon::createFromFormat('Y-m-d',$fine->date)->format('d-m-Y')."\""
								],
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" name=\"t_fine_payment_limit_date[]\" value=\"".$limitDate."\""
								],
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" name=\"t_fine_payment_date[]\" value=\"".$payDate."\""
								],
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" name=\"t_fine_total[]\" value=\"".$fine->total."\""
								]
							]
						],
						[
							"content" => [ "label" => $fine->status ]
						],
						[
							"content" => [ "label" => isset($fine->date) ? Carbon\Carbon::createFromFormat('Y-m-d',$fine->date)->format('d-m-Y') : '---' ]
						],
						[
							"content" => [ "label" => isset($fine->payment_limit_date) ? Carbon\Carbon::createFromFormat('Y-m-d',$fine->payment_limit_date)->format('d-m-Y') : '---' ]
						],
						[
							"content" => [ "label" => isset($fine->payment_date) ? Carbon\Carbon::createFromFormat('Y-m-d',$fine->payment_date)->format('d-m-Y') : '---' ]
						],
						[
							"content" => [ "label" => '$ '.number_format($fine->total,2) ]
						]
					];
					$docsFine = '';
					if($fine->documents()->exists())
					{
						foreach($fine->documents as $doc)
						{
							$docsFine .= '<div class="nowrap have-docs">';
							$docsFine .= view("components.buttons.button",
							[
								"variant"		=> "dark-red",
								"buttonElement"	=> "a",
								"attributeEx"	=> "target=\"_blank\" type=\"button\" title=\"".$doc->name."\"".' '."href=\"".url('docs/vehicles/'.$doc->path)."\"",
								"label"			=> "PDF"
							])->render();
							$docsFine	.= '<div><label>'.$doc->name.'</label></div>';
							$docsFine	.= view('components.inputs.input-text',
							[
								"classEx"		=> "t_id_doc_fine",
								"attributeEx"	=> "type=\"hidden\" value=\"".$doc->id."\""
							])->render();
							$docsFine .= view("components.inputs.input-text",
							[
								"attributeEx"	=> "type=\"hidden\" value=\"".$doc->name."\"",
								"classEx"		=> "t_name_doc_fine"
							])->render();
							$docsFine .= view("components.inputs.input-text",
							[
								"attributeEx"	=> "type=\"hidden\" value=\"".$doc->path."\"",
								"classEx"		=> "t_path_doc_fine"
							])->render();
							$docsFine .= "</div>";
						}
					}
					else
					{
						$docsFine	.= '<div class="nowrap">';
						$docsFine	.= '<div><label>No hay documentos</label></div>';
						$docsFine	.= "</div>";
					}
					$body[] = [ "content" => ["label" => $docsFine]];
					$body[] = 
					[ 
						"content" => 
						[
							[
								"kind"			=> "components.buttons.button",
								"attributeEx"	=> "type=\"button\" title=\"Editar multa\"",
								"variant"		=> "success",
								"classEx"		=> "edit-fine",
								"label"			=> "<span class=\"icon-pencil\"></span>"
							],
							[
								"kind"			=> "components.buttons.button",
								"attributeEx"	=> "type=\"button\" title=\"Eliminar\"",
								"variant"		=> "red",
								"classEx"		=> "delete-fine",
								"label"			=> "<span class=\"icon-x\"></span>"
							]
						]
					];
					$modelBody[] = $body;
				}
			}
		@endphp
		@component('components.tables.table', [
			"modelBody" 		=> $modelBody,
			"modelHead" 		=> $modelHead,
			"attributeExBody" 	=> "id=\"body_fines\""
		])
		@endcomponent
		@component('components.labels.title-divisor') Datos de Seguro @endcomponent
		@component('components.containers.container-form')
			<div class="col-span-2">
				@component('components.labels.label') Aseguradora: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" placeholder="Ingrese el nombre de la aseguradora"
					@endslot
					@slot('classEx')
						insurance_carrier check-empty-input
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Fecha de Vencimiento: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" readonly="readonly" placeholder="Ingrese la fecha"
					@endslot
					@slot('classEx')
						datepicker expiration_date check-empty-input
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Total (Prima): @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" placeholder="Ingrese el total"
					@endslot
					@slot('classEx')
						insurance_total check-empty-input
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2 md:col-span-4 grid grid-cols-1 md:grid-cols-2 gap-6 documents_insurance"></div>
			<div class="col-span-2 md:col-span-4 space-x-2 text-center md:text-left">
				<div class="block">
					@component('components.inputs.input-text')
						@slot('classEx')
							insurance_id
						@endslot
						@slot('attributeEx')
							type="hidden" value="x"
						@endslot
					@endcomponent
					@component('components.buttons.button', ["variant" => "warning"])
						@slot('attributeEx')
							id="add_insurance_document" type="button"
						@endslot
						@slot('classEx')
							mt-4
						@endslot
							<span class="icon-plus"></span> 
							<span>Anexar documento</span>
					@endcomponent
					@component('components.buttons.button', ["variant" => "success"])
						@slot('attributeEx')
							id="add_insurance" type="button"
						@endslot
						@slot('classEx')
							mt-4
						@endslot
							<span class="icon-plus"></span> 
							<span>Agregar registro</span>
					@endcomponent
				</div>
			</div>
		@endcomponent
		@php
			$body		= [];
			$modelBody	= [];
			$modelHead	= 
			[
				[
					["value" => "#"],
					["value" => "Aseguradora"],
					["value" => "Fecha de Vencimiento"],
					["value" => "Total"],
					["value" => "Documentos"],
					["value" => "Acción"]
				]
			];

			if(isset($vehicle))
			{
				foreach($vehicle->insurances as $key=>$insurance)
				{
					$body = [ "classEx" => "body-insurances",
						[
							"classEx"	=> "count_insurances",
							"content"	=>
							[
								"label" => $key+1
							]
						],
						[
							"content"	=>
							[
								[
									"label" => $insurance->insurance_carrier
								],
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" name=\"t_insurance_id[]\" value=\"".$insurance->id."\"",
									"classEx"		=> "t_insurance_id"
								],
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" name=\"t_insurance_carrier[]\" value=\"".$insurance->insurance_carrier."\""
								],
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" name=\"t_expiration_date[]\" value=\"".Carbon\Carbon::createFromFormat('Y-m-d',$insurance->expiration_date)->format('d-m-Y')."\""
								],
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" name=\"t_insurance_total[]\" value=\"".$insurance->total."\""
								]
							]
						],
						[
							"content" => [ "label" => isset($insurance->expiration_date) ? Carbon\Carbon::createFromFormat('Y-m-d',$insurance->expiration_date)->format('d-m-Y') : '---' ]
						],
						[
							"content" => [ "label" => '$ '.number_format($insurance->total,2) ]
						]
					];
					$docInsurances = '';
					if($insurance->documents()->exists())
					{
						foreach($insurance->documents as $doc)
						{
							$docInsurances .= '<div class="nowrap have-docs">';
							$docInsurances .= view("components.buttons.button",
							[
								"variant"		=> "dark-red",
								"buttonElement"	=> "a",
								"attributeEx"	=> "target=\"_blank\" type=\"button\" title=\"".$doc->name."\"".' '."href=\"".url('docs/vehicles/'.$doc->path)."\"",
								"label"			=> "PDF"
							])->render();
							$docInsurances	.= view('components.inputs.input-text',
							[
								"classEx"		=> "edit_insurance_id_doc",
								"attributeEx"	=> "type=\"hidden\" value=\"".$doc->id."\""
							])->render();
							$docInsurances	.= view('components.inputs.input-text',
							[
								"classEx"		=> "edit_insurance_name_document",
								"attributeEx"	=> "type=\"hidden\" value=\"".$doc->name."\""
							])->render();
							$docInsurances	.= view('components.inputs.input-text',
							[
								"classEx"		=> "edit_insurance_path",
								"attributeEx"	=> "type=\"hidden\" value=\"".$doc->path."\""
							])->render();
							$docInsurances .= '<div><label>'.$doc->name.'</label></div>';
							$docInsurances .= "</div>";
						}
					}
					else
					{
						$docInsurances	.= '<div class="nowrap">';
						$docInsurances	.= '<div><label>No hay documentos</label></div>';
						$docInsurances	.= "</div>";
					}
					$body[] = [ "content" => [ "label" => $docInsurances ]];
					$body[] = 
					[ 
						"content" =>
						[
							[
								"kind"			=> "components.buttons.button",
								"attributeEx"	=> "type=\"button\" title=\"Editar seguro\"",
								"variant"		=> "success",
								"classEx"		=> "edit-insurance",
								"label"			=> "<span class=\"icon-pencil\"></span>"
							],
							[
								"kind"			=> "components.buttons.button",
								"attributeEx"	=> "type=\"button\" title=\"Eliminar registro\"",
								"variant"		=> "red",
								"classEx"		=> "delete-insurance",
								"label"			=> "<span class=\"icon-x\"></span>"	
							]
						]
					];
					$modelBody[] = $body;
				}
			}
		@endphp
		@component('components.tables.table',[
			"modelHead"			=> $modelHead,
			"modelBody" 		=> $modelBody,
			"attributeExBody" 	=> "id=\"body_insurances\""
		])
		@endcomponent
		@component('components.labels.title-divisor') Datos de Servicios Mecánicos @endcomponent
		@component('components.containers.container-form')
			<div class="col-span-2">
				@component('components.labels.label') Fecha de Último Servicio: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" readonly="readonly" placeholder="Ingrese la fecha"
					@endslot
					@slot('classEx')
						date_last_service check-empty-input
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Fecha de Próximo Servicio (Opcional): @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" readonly="readonly" placeholder="Ingrese la fecha"
					@endslot
					@slot('classEx')
						datepicker next_service_date
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Total: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" placeholder="Ingrese el total"
					@endslot
					@slot('classEx')
						mechanical_service_total check-empty-input
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Reparaciones: @endcomponent
				@component('components.inputs.text-area')
					@slot('attributeEx')
						rows="3" placeholder="Ingrese las reparaciones realizadas"
					@endslot
					@slot('classEx')
						repairs check-empty-input
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2 md:col-span-4 grid grid-cols-1 md:grid-cols-2 gap-6 documents_mechanical_services"></div>
			<div class="col-span-2 md:col-span-4 space-x-2 text-center md:text-left">
				<div class="block">
					@component('components.inputs.input-text')
						@slot('classEx')
							ms_id
						@endslot
						@slot('attributeEx')
							type="hidden" value="x"
						@endslot
					@endcomponent
					@component('components.buttons.button', ["variant" => "warning"])
						@slot('attributeEx')
							id="add_mechanical_services_document" type="button"
						@endslot
						@slot('classEx')
							mt-4
						@endslot
							<span class="icon-plus"></span> 
							<span>Anexar documento</span>
					@endcomponent
					@component('components.buttons.button', ["variant" => "success"])
						@slot('attributeEx')
							id="add_mechanical_services" type="button"
						@endslot
						@slot('classEx')
							mt-4
						@endslot
						<span class="icon-plus"></span>
						<span>Agregar registro</span>
					@endcomponent
				</div>
			</div>
		@endcomponent
		@php
			$body 		= [];
			$modelBody	= [];
			$modelHead	= 
			[
				[
					["value" => "#"],
					["value" => "Fecha de Último Servicio"],
					["value" => "Fecha de Próximo Servicio"],
					["value" => "Reparaciones"],
					["value" => "Total"],
					["value" => "Documentos"],
					["value" => "Acción"]
				]
			];
			if (isset($vehicle))
			{
				foreach($vehicle->mechanicalServices as $key=>$ms)
				{
					$dateNext = $ms->next_service_date != '' ? Carbon\Carbon::createFromFormat('Y-m-d',$ms->next_service_date)->format('d-m-Y') : '';
					$body = [ "classEx" => "body-mechanical",
						[
							"classEx"	=> "count_ms",
							"content"	=>
							[
								"label" => $key+1
							]
						],
						[
							"content"	=>
							[
								[
									"label" => isset($ms->date_last_service) ? Carbon\Carbon::createFromFormat('Y-m-d',$ms->date_last_service)->format('d-m-Y') : '---'
								],
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" name=\"t_mechanical_services_id[]\" value=\"".$ms->id."\"",
									"classEx"		=> "t_mechanical_services_id"
								],
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" name=\"t_date_last_service[]\" value=\"".Carbon\Carbon::createFromFormat('Y-m-d',$ms->date_last_service)->format('d-m-Y')."\""
								],
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" name=\"t_next_service_date[]\" value=\"".$dateNext."\""
								],
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" name=\"t_repairs[]\" value=\"".$ms->repairs."\""
								],
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" name=\"t_mechanical_service_total[]\" value=\"".$ms->total."\""
								]
							]
						],
						[
							"content" => [ "label" => isset($ms->next_service_date) ? Carbon\Carbon::createFromFormat('Y-m-d',$ms->next_service_date)->format('d-m-Y') : '---' ]
						],
						[
							"content" => [ "label" => $ms->repairs ]
						],
						[
							"content" => [ "label" => '$ '.number_format($ms->total,2) ]
						]
					];
					$docsMechanical = '';
					if($ms->documents()->exists())
					{
						foreach($ms->documents as $doc)
						{
							$docsMechanical .= '<div class="nowrap have-docs">';
							$docsMechanical .= view('components.buttons.button',
							[
								"variant"		=> "dark-red",
								"buttonElement"	=> "a",
								"attributeEx"	=> "target=\"_blank\" type=\"button\" title=\"".$doc->name."\"".' '."href=\"".url('docs/vehicles/'.$doc->path)."\"",
								"label"			=> "PDF"
							])->render();
							$docsMechanical	.= view('components.inputs.input-text',
							[
								"classEx"		=> "edit_ms_id_document",
								"attributeEx"	=> "type=\"hidden\" value=\"".$doc->id."\""
							])->render();
							$docsMechanical	.= view('components.inputs.input-text',
							[
								"classEx"		=> "edit_ms_name_document",
								"attributeEx"	=> "type=\"hidden\" value=\"".$doc->name."\""
							])->render();
							$docsMechanical	.= view('components.inputs.input-text',
							[
								"classEx"		=> "edit_ms_path",
								"attributeEx"	=> "type=\"hidden\" value=\"".$doc->path."\""
							])->render();
							$docsMechanical .= '<div><label>'.$doc->name.'</label></div>';
							$docsMechanical .= "</div>";
						}
					}
					else
					{
						$docsMechanical	.= '<div class="nowrap">';
						$docsMechanical	.= '<div><label>No hay documentos</label></div>';
						$docsMechanical	.= "</div>";
					}
					$body[] = [ "content" => [ "label" => $docsMechanical ]];
					$body[] = 
					[ 
						"content" =>
						[
							[
								"kind"			=> "components.buttons.button",
								"attributeEx"	=> "type=\"button\" title=\"Editar servicio mecánico\"",
								"variant"		=> "success",
								"classEx"		=> "edit-mechanical-service",
								"label"			=> "<span class=\"icon-pencil\"></span>"
							],
							[
								"kind"			=> "components.buttons.button",
								"attributeEx"	=> "type=\"button\" title=\"Eliminar registro\"",
								"variant"		=> "red",
								"classEx"		=> "delete-mechanical-service",
								"label"			=> "<span class=\"icon-x\"></span>"	
							]
						]
					];
					$modelBody[] = $body;
				}
			}
		@endphp
		@component('components.tables.table',[
			"modelBody" 		=> $modelBody,
			"modelHead" 		=> $modelHead,
			"attributeExBody"	=> "id=\"body_mechanical_services\""
		])
		@endcomponent
		<div id="invisible"></div>
		<div class="w-full mt-4 grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mb-6">
			@component('components.buttons.button', [ "variant" => "secondary"])
				@slot('attributeEx')
					type="submit" name="save"
				@endslot
				@slot('classEx')
					text-center w-48 md:w-auto
				@endslot
					GUARDAR VEHÍCULO
			@endcomponent
			@if(isset($vehicle))
				@component('components.buttons.button', ["variant"=>"reset", "buttonElement"=>"a"])
					@slot('classEx')
						load-actioner text-center w-48 md:w-auto
					@endslot
					@slot('attributeEx')
						type="button"
						@if(isset($option_id)) 
							href="{{ url(App\Module::find($option_id)->url) }}" 
						@else 
							href="{{ url(App\Module::find($child_id)->url) }}" 
						@endif 
					@endslot
					REGRESAR
				@endcomponent
			@endif
			@if(!isset($vehicle))
				@component('components.buttons.button',["variant" => "reset"])
					@slot('attributeEx')
						type="reset"
					@endslot
					@slot('classEx')
						reset
					@endslot
					BORRAR CAMPOS
				@endcomponent
			@endif
		</div>
	@endcomponent
@endsection

@section('scripts')
	<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
	<link rel="stylesheet" href="{{ asset('css/jquery.timepicker.min.css') }}">
	<link rel="stylesheet" type="text/css" href="{{ asset('css/daterangepicker.css') }}" />
	<script type="text/javascript" src="{{ asset('js/moment.min.js') }}"></script>
	<script src="{{ asset('js/jquery-ui.js') }}"></script>
	<script src="{{ asset('js/jquery.numeric.js') }}"></script>
	<script src="{{ asset('js/daterangepicker.js') }}"></script>
	<script src="{{ asset('js/datepicker.js') }}"></script>
	<script type="text/javascript" src="{{asset('js/jquery.mask.js')}}"></script>
	<script type="text/javascript">	
		function validation()
		{
			$.validate(
			{
				form	: '#form-vehicles',
				modules	: 'security',
				onError	: function($form)
				{
					spanError	=	$('.curp-form').children('span').text();
					if (spanError == "Los valores proporcionados no son válidos")
					{
						swal('', '{{ Lang::get("messages.form_error") }}', 'error');
					}
					else
					{
						swal('', 'Por favor llene todos los campos que son obligatorios.', 'error');
					}
				},
				onSuccess : function($form)
				{
					$('.error').removeClass('error');
					$('.form-error').remove();

					flag			= false;
					flagInputsForms = false; 
					if($('.form-content-tsd').length > 0)
					{
						$('.form-content-tsd').each(function(i,v)
						{
							technical_specifications_document = $(this).find('[name="technical_specifications_document[]"]').val();
							technical_specifications_path = $(this).find('[name="technical_specifications_path[]"]').val();
							
							if (technical_specifications_document == "" || technical_specifications_path == "") 
							{
								flag = true;
							}
						});
					}
					if($('.form-content-od').length > 0)
					{
						$('.form-content-od').each(function(i,v)
						{
							owner_document = $(this).find('[name="owner_document[]"]').val();
							owner_path = $(this).find('[name="owner_path[]"]').val();
							
							if (owner_document == "" || owner_path == "") 
							{
								flag = true;
							}
						});
					}

					$('.check-empty-input').each(function(){
						if($(this).val() != "")
						{
							flagInputsForms = true;
							$(this).addClass('error');
						}
					});

					$('.check-empty-select').each(function(){
						if($(this).find('option:selected').val() != null)
						{
							flagInputsForms = true;
							$(this).parent().append('<span class="help-block form-error">Este campo se encuentra seleccionado</span>');
						}
					});

					if (flag || flagInputsForms) 
					{
						if(flag)
						{
							swal('', 'Por favor llene todos los campos que son obligatorios de los documentos.', 'error');
							$('[name="owner_document[]"]').parent('div').append($('<span class="help-block form-error">Este campo es obligatorio</span>'));
							$('[name="technical_specifications_document[]"]').parent('div').append($('<span class="help-block form-error">Este campo es obligatorio</span>'));
						}
						else if(flagInputsForms)
						{
							swal('', 'Tiene campos con información, por favor verifique los campos marcados.', 'error');
						}
						return false;
					}
					else
					{
						swal("Cargando",{
							icon 				: '{{ url(getenv('LOADING_IMG')) }}',
							button				: false,
							closeOnClickOutside	: false,
							closeOnEsc			: false
						});
						return true;
					}
				}
			});
		}
		$(document).ready(function()
		{
			@php
				$selects = collect ([
					[
						"identificator"				=> ".owner_type,.owner_physical,.owner_moral,.owner_external,.fine_status,.owner_new,.owner_exists",
						"placeholder"				=> "Seleccione uno",
						"maximumSelectionLength"	=> "1"
					],
					[
						"identificator"				=> '[name="state"],[name="work_state"]',
						"placeholder"				=> "Seleccione el estado",
						"maximumSelectionLength"	=> "1"
					]
				]);
			@endphp
			@component('components.scripts.selects',["selects" => $selects]) @endcomponent
			generalSelect({'selector':'.fine_driver','model':20});
			validation();
			@if(!isset($vehicle) || (isset($vehicle) && $vehicle->vehicles_owners_id != "" && App\VehicleOwner::find($vehicle->vehicles_owners_id) == ""))
				$('.div_owner').hide();
			@endif
			ownerType		=	$('.owner_type').val();
			ownerExternal	=	$('.owner_external').val();
			$('.form-owner-physical').hide();
			$('.form-owner-moral').hide();
			$('[name="physical_imss"]').mask('0000000000-0',{placeholder: "__________-_"});
			$('.datepicker').datepicker({ dateFormat: "dd-mm-yy" });
			$('.date_last_service').datepicker({ dateFormat: "dd-mm-yy", maxDate: 0, });
			$('.next_service_date').datepicker({ dateFormat: "dd-mm-yy", minDate: 1, });
			$('.date_verification').datepicker({ dateFormat: "dd-mm-yy", maxDate: 0, });
			$('.next_date_verification').datepicker({ dateFormat: "dd-mm-yy", minDate: 1, });
			$('.fuel_total,.total_verification,.monto_gestoria,.fine_total,.insurance_total,.mechanical_service_total,[name="start_kilometer"],[name="end_kilometer"]').numeric({ altDecimal: ".", decimalPlaces: 2, negative: false });
			
			$(document).on('change','.fine_date',function()
			{
				dateFine		=	$('.fine_date').val();
				datePymentLimit	=	$('.fine_payment_limit_date').val();
				datePyment		=	$('.fine_payment_date').val();
				if (dateFine!="" || dateFine!=null)
				{
					if (datePymentLimit < dateFine)
					{
						$('.fine_payment_limit_date').removeClass('hasDatepicker');
						$('.fine_payment_limit_date').val('');
						$('.fine_payment_limit_date').datepicker({ dateFormat: "dd-mm-yy", minDate: dateFine});
					}
					if (datePyment < dateFine)
					{
						$('.fine_payment_date').removeClass('hasDatepicker');
						$('.fine_payment_date').val('');
						$('.fine_payment_date').datepicker({ dateFormat: "dd-mm-yy", minDate: dateFine});
					}
				}
			})
			$(document).on('click','#add_technical_specifications_document',function()
			{
				@php
					$optionSpecifications = [];
					$optionSpecifications[] = ["value" => "Factura", "description" => "Factura"];
					$optionSpecifications[] = ["value" => "Ficha Técnica", "description" => "Ficha Técnica"];
					$optionSpecifications[] = ["value" => "Otro", "description" => "Otro"];

					$docs_specifications = view("components.documents.upload-files",
					[
						"classExInput"			=> "pathActioner",
						"attributeExInput"		=> "type=\"file\" name=\"path\" accept=\".pdf,.jpg,.png\"",
						"classExDelete"			=> "delete-doc",
						"attributeExRealPath"	=> "type=\"hidden\" name=\"technical_specifications_path[]\"",
						"classExRealPath"		=> "path",
						"componentsExUp"		=> 
						[
							[
								"kind"			=> "components.inputs.input-text",
								"classEx"		=> "id-doc",
								"attributeEx"	=> "type=\"hidden\" value=\"x\"" 
							],
							[
								"kind"	=> "components.labels.label", 
								"label" => "Seleccione el tipo de documento:"
							],
							[
								"kind" 			=> "components.inputs.select", 
								"options" 		=> $optionSpecifications,
								"classEx" 		=> "nameDocument",
								"attributeEx"	=> "name=\"technical_specifications_document[]\" multiple data-validation=\"required\"" 
							]
						]
					])->render();
				@endphp
				newDocSpecifications = '{!!preg_replace("/(\r)*(\n)*/", "", $docs_specifications)!!}';
				$('.documents_technical_specifications').append(newDocSpecifications);
				@php
					$selects = collect ([
						[
							"identificator"				=> ".nameDocument",
							"placeholder"				=> "Seleccione el tipo de documento",
							"maximumSelectionLength"	=> "1"
						]
					]);
				@endphp
				@component('components.scripts.selects',["selects" => $selects]) @endcomponent
			})
			.on('click','#add_owner_document',function()
			{
				type		= $('.owner_type option:selected').val();
				external	= $('.owner_external option:selected').val();
				if (type=="fisica")
				{
					typeDocumentINE			=	$('<option value="INE">INE</option>');
					typeDocumentLicencia	=	$('<option value="Licencia de manejo">Licencia de manejo</option>');
					typeDocumentCartilla	=	$('<option value="Cartilla militar">Cartilla militar</option>');
					typeDocumentOther		=	$('<option value="Otro Documento">Otro Documento</option>');
					typeDocumentActa		=	"";
				}
				else
				{
					typeDocumentINE			=	"";
					typeDocumentLicencia	=	"";
					typeDocumentCartilla	=	"";
					typeDocumentActa		=	$('<option value="Acta Constitutiva">Acta Constitutiva</option>');
					typeDocumentOther		=	$('<option value="Otro Documento">Otro Documento</option>');
				}
				@php
					$docs_owner = view("components.documents.upload-files",
					[
						"classExInput"			=> "pathActioner",
						"attributeExInput"		=> "type=\"file\" name=\"path\" accept=\".pdf,.jpg,.png\"",
						"classExDelete"			=> "delete-doc",
						"attributeExRealPath"	=> "type=\"hidden\" name=\"owner_path[]\"",
						"classExRealPath"		=> "path",
						"componentsExUp"		=>	
						[
							[
								"kind"			=> "components.inputs.input-text",
								"classEx"		=> "id-doc",
								"attributeEx"	=> "type=\"hidden\" value=\"x\"" 
							],
							[
								"kind" => "components.labels.label", 
								"label" => "Seleccione el tipo de documento:"
							],
							[
								"kind" 			=> "components.inputs.select",
								"classEx" 		=> "nameDocument",
								"attributeEx"	=> "name=\"owner_document[]\" multiple data-validation=\"required\"" 
							]
						]
					])->render();
				@endphp
				docOwner 	= '{!!preg_replace("/(\r)*(\n)*/", "", $docs_owner)!!}';
				newDocOwner = $(docOwner);
				newDocOwner.find('[name="owner_document[]"]').append(typeDocumentINE);
				newDocOwner.find('[name="owner_document[]"]').append(typeDocumentLicencia);
				newDocOwner.find('[name="owner_document[]"]').append(typeDocumentCartilla);
				newDocOwner.find('[name="owner_document[]"]').append(typeDocumentActa);
				newDocOwner.find('[name="owner_document[]"]').append(typeDocumentOther);
				$('.documents_owner').append(newDocOwner);
				@php
					$selects = collect ([
						[
							"identificator"				=> ".nameDocument",
							"placeholder"				=> "Seleccione el tipo de documento",
							"maximumSelectionLength"	=> "1"
						]
					]);
				@endphp
				@component('components.scripts.selects',["selects" => $selects]) @endcomponent
			})
			.on('click','#add_fuel_document',function()
			{
				@php
					$optionFuel	= [];
					$optionFuel[] = ["value" => "Factura", "description" => "Factura"];
					$optionFuel[] = ["value" => "Ticket", "description" => "Ticket"];
					$optionFuel[] = ["value" => "Otro", "description" => "Otro"];

					$docs_fuel = view("components.documents.upload-files",
					[
						"classExInput"			=> "pathActioner",
						"attributeExInput"		=> "type=\"file\" name=\"path\" accept=\".pdf,.jpg,.png\"",
						"classExDelete"			=> "delete-doc",
						"attributeExRealPath"	=> "type=\"hidden\"",
						"classExRealPath"		=> "fuel_path path",
						"componentsExUp"		=>	
						[
							[
								"kind"			=> "components.inputs.input-text",
								"classEx"		=> "id-doc",
								"attributeEx"	=> "type=\"hidden\" value=\"x\"" 
							],
							[
								"kind" => "components.labels.label", 
								"label" => "Seleccione el tipo de documento:"
							],
							[
								"kind" 			=> "components.inputs.select",
								"classEx" 		=> "nameDocument fuel_document",
								"options"		=> $optionFuel,
								"attributeEx"	=> "multiple" 
							]
						]
					])->render();
				@endphp
				newDocFuel	= '{!!preg_replace("/(\r)*(\n)*/", "", $docs_fuel)!!}';
				$('.documents_fuel').append(newDocFuel);
				@php
					$selects = collect ([
						[
							"identificator"				=> ".nameDocument",
							"placeholder"				=> "Seleccione el tipo de documento",
							"maximumSelectionLength"	=> "1"
						]
					]);
				@endphp
				@component('components.scripts.selects',["selects" => $selects]) @endcomponent
			})
			.on('click','#add_taxes_document',function()
			{
				@php
					$optionTaxes = [];
					$optionTaxes[] = ["value" => "Pago de Tenencia", "description" => "Pago de Tenencia"];
					$optionTaxes[] = ["value" => "Pago de Verificación", "description" => "Pago de Verificación"];

					$docs_taxes = view('components.documents.upload-files',
					[
						"classExInput" 			=> "pathActioner",
						"attributeExInput"		=> "type=\"file\" name=\"path\" accept=\".pdf,.jpg,.png\"",
						"classExDelete"			=> "delete-doc",
						"attributeExRealPath"	=> "type=\"hidden\"",
						"classExRealPath"		=> "path taxes_path",
						"componentsExUp"		=> 	
						[
							[
								"kind"			=> "components.inputs.input-text",
								"classEx"		=> "id-doc",
								"attributeEx"	=> "type=\"hidden\" value=\"x\"" 
							],
							[
								"kind" => "components.labels.label", 
								"label" => "Seleccione el tipo de documento:"
							],
							[
								"kind" 			=> "components.inputs.select",
								"classEx" 		=> "nameDocument taxes_document",
								"options"		=> $optionTaxes,
								"attributeEx"	=> "multiple" 
							],
						],
						"componentsExDown" =>
						[
							[
								"kind" => "components.labels.label", 
								"label" => "Fecha de Pago:"
							],
							[
								"kind"			=> "components.inputs.input-text",
								"attributeEx"	=> "type=\"text\" placeholder=\"Ingrese la fecha\"",
								"classEx"		=> "datepicker taxes_date"
							]
						],
					])->render();
				@endphp
				newDocTaxes	= '{!!preg_replace("/(\r)*(\n)*/", "", $docs_taxes)!!}';
				$('.documents_taxes').append(newDocTaxes);
				$('.datepicker').datepicker({ maxDate:0, dateFormat: "dd-mm-yy" });
				@php
					$selects = collect ([
						[
							"identificator"				=> ".nameDocument",
							"placeholder"				=> "Seleccione el tipo de documento",
							"maximumSelectionLength"	=> "1"
						]
					]);
				@endphp
				@component('components.scripts.selects',["selects" => $selects]) @endcomponent
			})
			.on('click','#add_fines_document',function()
			{
				@php
					$optionFines = [];
					$optionFines[] = ["value" => "Comprobante de Pago", "description" => "Comprobante de Pago"];
					$optionFines[] = ["value" => "Multa", "description" => "Multa"];

					$docs_fines = view('components.documents.upload-files',
					[
						"classExInput" 			=> "pathActioner",
						"attributeExInput"		=> "type=\"file\" name=\"path\" accept=\".pdf,.jpg,.png\"",
						"classExDelete"			=> "delete-doc",
						"attributeExRealPath"	=> "type=\"hidden\" name=\"fines_path\"",
						"classExRealPath"		=> "path fines_path",
						"componentsExUp"		=> 	
						[
							[
								"kind"			=> "components.inputs.input-text",
								"classEx"		=> "id-doc",
								"attributeEx"	=> "type=\"hidden\" value=\"x\"" 
							],
							[
								"kind" => "components.labels.label", 
								"label" => "Seleccione el tipo de documento:"
							],
							[
								"kind" 			=> "components.inputs.select",
								"classEx" 		=> "nameDocument fines_document",
								"options"		=> $optionFines,
								"attributeEx"	=> "multiple" 
							]
						]
					])->render();
				@endphp
				newDocFines = '{!!preg_replace("/(\r)*(\n)*/", "", $docs_fines)!!}';
				$('.documents_fines').append(newDocFines);
				@php
					$selects = collect ([
						[
							"identificator"				=> ".nameDocument",
							"placeholder"				=> "Seleccione el tipo de documento",
							"maximumSelectionLength"	=> "1"
						]
					]);
				@endphp
				@component('components.scripts.selects',["selects" => $selects]) @endcomponent
			})
			.on('click','#add_insurance_document',function()
			{
				@php
					$optionInsurances = [];
					$optionInsurances[] = ["value" => "Comprobante de Pago", "description" => "Comprobante de Pago"];
					$optionInsurances[] = ["value" => "Póliza de Seguro", "description" => "Póliza de Seguro"];

					$docs_insurances = view("components.documents.upload-files",
					[
						"classExInput"			=> "pathActioner",
						"attributeExInput"		=> "type=\"file\" name=\"path\" accept=\".pdf,.jpg,.png\"",
						"classExDelete"			=> "delete-doc",
						"attributeExRealPath"	=> "type=\"hidden\"",
						"classExRealPath"		=> "path insurance_path",
						"componentsExUp"		=>	
						[
							[
								"kind"			=> "components.inputs.input-text",
								"classEx"		=> "id-doc",
								"attributeEx"	=> "type=\"hidden\" value=\"x\"" 
							],
							[
								"kind" => "components.labels.label", 
								"label" => "Seleccione el tipo de documento:"
							],
							[
								"kind" 			=> "components.inputs.select",
								"classEx" 		=> "nameDocument insurance_document",
								"options"		=> $optionInsurances,
								"attributeEx"	=> "multiple" 
							]
						]
					])->render();
				@endphp
				newDocInsurances = '{!!preg_replace("/(\r)*(\n)*/", "", $docs_insurances)!!}';
				$('.documents_insurance').append(newDocInsurances);
				@php
					$selects = collect ([
						[
							"identificator"				=> ".nameDocument",
							"placeholder"				=> "Seleccione el tipo de documento",
							"maximumSelectionLength"	=> "1"
						]
					]);
				@endphp
				@component('components.scripts.selects',["selects" => $selects]) @endcomponent
			})
			.on('click','#add_mechanical_services_document',function()
			{
				@php
					$optionMechanical = [];
					$optionMechanical[] = ["value" => "Comprobante de Pago", "description" => "Comprobante de Pago"];
					$optionMechanical[] = ["value" => "Otro", "description" => "Otro"];

					$docs_mechanical = view('components.documents.upload-files',
					[
						"classExInput"			=> "pathActioner",
						"attributeExInput"		=> "type=\"file\" name=\"path\" accept=\".pdf,.jpg,.png\"",
						"classExDelete"			=> "delete-doc",
						"attributeExRealPath"	=> "type=\"hidden\" name=\"mechanical_services_path\"",
						"classExRealPath"		=> "path mechanical_services_path",
						"componentsExUp"		=>	
						[
							[
								"kind"			=> "components.inputs.input-text",
								"classEx"		=> "id-doc",
								"attributeEx"	=> "type=\"hidden\" value=\"x\"" 
							],
							[
								"kind" => "components.labels.label", 
								"label" => "Seleccione el tipo de documento:"
							],
							[
								"kind" 			=> "components.inputs.select",
								"classEx" 		=> "nameDocument mechanical_services_document",
								"options"		=> $optionMechanical,
								"attributeEx"	=> "multiple" 
							]
						]
					])->render();
				@endphp
				newDocMechanical = '{!!preg_replace("/(\r)*(\n)*/", "", $docs_mechanical)!!}';
				$('.documents_mechanical_services').append(newDocMechanical);
				@php
					$selects = collect ([
						[
							"identificator"				=> ".nameDocument",
							"placeholder"				=> "Seleccione el tipo de documento",
							"maximumSelectionLength"	=> "1"
						]
					]);
				@endphp
				@component('components.scripts.selects',["selects" => $selects]) @endcomponent
			})
			.on('change','.pathActioner',function(e)
			{
				filename		= $(this);
				uploadedName 	= $(this).parent('.uploader-content').siblings('.path');
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
						url			: '{{ route("vehicle.upload") }}',
						data		: formData,
						contentType	: false,
						processData	: false,
						success		: function(r)
						{
							if(r.error=='DONE')
							{
								$(e.currentTarget).removeAttr('style').parent('.uploader-content').removeClass('loading').addClass('image_success');
								$(e.currentTarget).parent('.uploader-content').siblings('.path').val(r.path);
								$(e.currentTarget).val('');
							}
							else
							{
								swal('',r.message, 'error');
								$(e.currentTarget).removeAttr('style').parent('.uploader-content').removeClass('loading');
								$(e.currentTarget).val('');
								$(e.currentTarget).parent('.uploader-content').siblings('.path').val('');
							}
						},
						error: function()
						{
							swal('', 'Ocurrió un error durante la carga del archivo, intente de nuevo, por favor', 'error');
							$(e.currentTarget).removeAttr('style').parent('.uploader-content').removeClass('loading');
							$(e.currentTarget).val('');
							$(e.currentTarget).parent('.uploader-content').siblings('.path').val('');
						}
					})
				}
			})
			.on('click','.delete-doc',function()
			{
				section		= "div-only";
				actioner 	= $(this);
				path 		= actioner.parents('.docs-p').find('.path').val();
				id 			= actioner.parents('.docs-p').find('.id-doc').val();
				if (path != "")
				{
					swal(
					{
						title		: "Confirmar",
						text		: "¿Desea eliminar el documento?",
						icon		: "warning",
						buttons		: ["Cancelar","OK"],
						dangerMode	: true,
					})
					.then((willDelete) => 
					{
						if (willDelete) 
						{
							if (id != "x")
							{
								input = $('<input type="hidden" name="delete_document[]" value="'+id+'">');
								$('#invisible').append(input);
								$(this).parents('.docs-p').remove();
							}
							else
							{
								deleteDocs(actioner, section);			
							}
						}
					});
				}
				else
				{
					deleteDocs(actioner, section);
				}
			})
			.on('change','.owner_type,.owner_external',function()
			{
				$('.owner_exists').empty();
				idDocs = $('#body_owner .body-row').length;
				if (idDocs > 0)
				{
					$('#body_owner .body-row').each(function()
					{
						id = $(this).find('.vehicle_document_id').val();
						if (id != "x") 
						{
							input = $('<input type="hidden" name="delete_document[]" value="'+id+'">');
							$('#invisible').append(input);
						}
						$(this).remove();
					});
				}
				
				type		= $('.owner_type option:selected').val();
				external	= $('.owner_external option:selected').val();
				if (type != undefined && external != undefined) 
				{
					if (type=="fisica" && external == "existente") 
					{
						$('.select-owner').show();
						$('.label-owner').show();
						$('.div_owner').show();
						$(".owner_exists").val(null).trigger('change');
							@php
							$selects = collect ([
								[
									"identificator"				=> ".owner_exists",
									"placeholder"				=> "Seleccione uno",
									"maximumSelectionLength"	=> "1"
								]
							]);
						@endphp
						@component('components.scripts.selects',["selects" => $selects]) @endcomponent
						$('.document_oficial').remove();
						$('.documents_owner').find('.docs-p').remove();
						$.ajax(
						{
							type		: 'post',
							url			: '{{route('vehicle.get-data-owner')}}',
							data		: { 'type_owner':type },
							success		: function(data)
							{
								if (data != "") 
								{
									$.each(data,function(i, d)
									{
										$('.owner_exists').append('<option value='+d.id+'>'+d.name+'</option>');
									});
								}
							},
							error : function()
							{
								swal('','Sucedió un error, por favor intente de nuevo.','error');
								$('.owner_exists').val(null).trigger('change');
							}
						});
					}
					else if (type=="moral" && external == "existente") 
					{
						$('.select-owner').show();
						$('.label-owner').show();
						$('.div_owner').show();
						$(".owner_exists").val(null).trigger('change');
						@php
							$selects = collect ([
								[
									"identificator"				=> ".owner_exists",
									"placeholder"				=> "Seleccione uno",
									"maximumSelectionLength"	=> "1"
								]
							]);
						@endphp
						@component('components.scripts.selects',["selects" => $selects]) @endcomponent
						$('.document_oficial').remove();
						$('.documents_owner').find('.docs-p').remove();
						$.ajax(
						{
							type		: 'post',
							url			: '{{route('vehicle.get-data-owner')}}',
							data		: { 'type_owner':type },
							success		: function(data)
							{
								if (data != "") 
								{
									$.each(data,function(i, d)
									{
										$('.owner_exists').append('<option value='+d.id+'>'+d.name+'</option>');
									});
								}
							},
							error : function()
							{
								swal('','Sucedió un error, por favor intente de nuevo.','error');
								$('.owner_exists').val(null).trigger('change');
							}
						});
					}
					else if (type=="fisica" && external == "nuevo")
					{
						$('.select-owner').show();
						$('.label-owner').hide();
						$('.document_oficial').remove();
						$(".owner_exists").val(null).trigger('change');
						$('.form-owner-moral').hide();
						$('.form-owner-physical').show();
						$('.form-owner-physical .general-class').val('');
						$('.form-owner-moral .general-class').val('');
						$('.div_owner_new .general-class').val('');
						$("[name='physical_state']").val(null).trigger('change');
						@php
							$selects = collect ([
								[
									"identificator"				=> ".nameDocument",
									"placeholder"				=> "Seleccione el tipo de documento",
									"maximumSelectionLength"	=> "1"
								]
							]);
						@endphp
						@component('components.scripts.selects',["selects" => $selects]) @endcomponent
						$('.documents_owner').find('.docs-p').remove();
					}
					else if (type=="moral" && external == "nuevo")
					{
						$('.select-owner').show();
						$('.label-owner').hide();
						$(".owner_exists").val(null).trigger('change');
						$('.form-owner-moral').show();
						$('.form-owner-physical').hide();
						$('.form-owner-physical .general-class').val('');
						$('.form-owner-moral .general-class').val('');
						$("[name='moral_state']").val(null).trigger('change');
						$('.document_oficial').remove();
						$('.documents_owner').find('.docs-p').remove();
					}
				}
				else
				{
					$('.select-owner').hide();
					$('.div_owner').hide();
					$('.owner_exists').val(null).trigger('change');
					$('.form-owner-moral').hide();
					$('.form-owner-physical').hide();
				}
				generalSelect({'selector':'#cp', 'model':2});
				generalSelect({'selector':'#physical_cp', 'model':2});
				generalSelect({'selector':'.state-physical,.state-moral', 'model':31});
			})
			.on('change','.fine_status',function()
			{
				fine_status = $('.fine_status option:selected').val();
				if (fine_status != undefined) 
				{
					if (fine_status == "Pagado") 
					{
						$('.div_payment_date').show();
					}
					else
					{
						$('.div_payment_date').hide();
					}
				}
				else
				{
					$('.div_payment_date').hide();
				}
			})
			.on('click','#add_fines',function()
			{
				$('.fine_status').parents('.select-class').find('.form-error').remove();
				$('.fine_driver').parents('.select-class').find('.form-error').remove();
				$('.fine_date').removeClass('error');
				$('.fine_payment_date,.fine_payment_limit_date').removeClass('error');
				num_fine				= $('#body_fines .body-fines').length + 1;
				fine_driver				= $('.fine_driver option:selected').val();
				fine_driver_text		= $('.fine_driver option:selected').text();
				fine_status				= $('.fine_status option:selected').val();
				fine_status_text		= $('.fine_status option:selected').text();
				fine_date				= $('.fine_date').val();
				fine_payment_date		= $('.fine_payment_date').val();
				fine_payment_limit_date	= $('.fine_payment_limit_date').val();
				fine_total				= $('.fine_total').val();
				fine_id 				= $('.fine_id').val();

				if (fine_status == undefined || fine_driver == undefined || fine_date == "" || fine_total == "") 
				{
					if (fine_driver == undefined)
					{
						return swal('','Por favor, ingrese un conductor','error');
					}
					if (fine_status == undefined)
					{
						return swal('','Por favor, ingrese un estado','error');
					}
					if (fine_date == "")
					{
						$('.fine_date').addClass('error');
					}
					if (fine_total == "")
					{
						$('.fine_total').addClass('error');
					}
					return swal('','Debe llenar los campos obligatorios','error');
				}
				else if(fine_total == 0)
				{
					$('.fine_total').addClass('error');
					return swal('','El total no puede ser cero','error');
				}
				else
				{
					if (fine_status == "Pagado" && fine_payment_date == "")  
					{
						$('.fine_payment_date').addClass('error');
						return swal('','Por favor seleccione la fecha de pago','info');
					}
					else if (fine_status == "No Pagado" && fine_payment_limit_date == "")
					{
						$('.fine_payment_limit_date').addClass('error');
						return swal('','Por favor seleccione la fecha límite de pago','info');
					}
					else if(fine_status == "No Pagado" && fine_payment_date != "")
					{
						return swal('','Por favor cambie el estado de pago a "Pagado"','info');
					}
					else
					{
						flag = false;
						$('.fines_path').each(function()
						{
							path = $(this).val();
							name = $(this).parents('.docs-p').find('.fines_document').val();
							if (name == "" || path == "") 
							{
								flag = true;
							}
						});

						@php
							$body		= [];
							$modelBody	= [];
							$modelHead	= [
								[
									["value" => "#"],
									["value" => "Conductor"],
									["value" => "Estado de Multa"],
									["value" => "Fecha de Multa"],
									["value" => "Fecha Límite de Pago"],
									["value" => "Fecha de Pago"],
									["value" => "Total"],
									["value" => "Documentos"],
									["value" => "Acción"]
								]
							];

							$body = [ "classEx" => "body-fines",
								[
									"classEx"	=> "count_fine",
									"content" 	=>
									[
										"label" => ""
									]																		
								],
								[
									"content" 	=>
									[
										[
											"kind"			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"hidden\"",
											"classEx"		=> "fine-driver-class"
										],
										[
											"kind"			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"hidden\" name=\"t_vehicle_fine_id[]\"",
											"classEx"		=> "t_vehicle_fine_id"
										],
										[
											"kind"			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"hidden\" name=\"t_fine_driver[]\"",
											"classEx"		=> "t_fine_driver"
										]
									]														
								],
								[
									"content" =>
									[
										[
											"kind"			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"hidden\" name=\"t_fine_status[]\"",
											"classEx"		=> "t_fine_status"
										]
									]
								],
								[
									"content" =>
									[
										[
											"kind"			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"hidden\" name=\"t_fine_date[]\"",
											"classEx"		=> "t_fine_date"
										]
									]
								],
								[
									"content" =>
									[
										[
											"kind"			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"hidden\" name=\"t_fine_payment_limit_date[]\"",
											"classEx"		=> "t_fine_payment_limit_date"
										]
									]
								],
								[
									"content" =>
									[
										[
											"kind"			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"hidden\" name=\"t_fine_payment_date[]\"",
											"classEx"		=> "t_fine_payment_date"
										]
									]
								],
								[
									"content" =>
									[
										[
											"kind"			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"hidden\" name=\"t_fine_total[]\"",
											"classEx"		=> "t_fine_total"
										]
									]
								],
								[
									"classEx" => "docsFines",
									"content" =>
									[
										"label" => ""
									]
								],
								[
									"content" =>
									[
										[
											"kind"  		=> "components.buttons.button",
											"variant"	 	=> "success",
											"label" 		=> "<span class=\"icon-pencil\"></span>",
											"attributeEx" 	=> "type=\"button\" title=\"Editar multa\"",
											"classEx" 		=> "edit-fine"
										],
										[
											"kind"  		=> "components.buttons.button",
											"variant"	 	=> "red",
											"label" 		=> "<span class=\"icon-x\"></span>",
											"attributeEx" 	=> "type=\"button\" title=\"Eliminar multa\"",
											"classEx" 		=> "delete-fine"
										]
									]
								]
							];
							$modelBody[]	= $body;
							$tableFines		= view('components.tables.table',[
								"modelBody" => $modelBody,
								"modelHead" => $modelHead, 
								"noHead"	=> "true"
							])->render();
						@endphp
						finesBody	= '{!!preg_replace("/(\r)*(\n)*/", "", $tableFines)!!}';
						tr_fines	= $(finesBody);

						if (flag) 
						{
							return swal('','Por favor agregue los datos de todos los documentos','info');
						}
						else
						{
							if($('.fines_path').length > 0)
							{
								$('.fines_path').each(function(i, v)
								{
									name 	= $(this).parents('.docs-p').find('.fines_document option:selected').val();
									path 	= $(this).val();
									idPath	= $(this).parents('.docs-p').find('.id-doc').val();
									url 	= '{{ url("docs/vehicles/") }}/'+path;
									
									@php
										$buttonPdf = view("components.buttons.button",[
											"buttonElement" => "a",
											"attributeEx" 	=> "target=\"_blank\"",
											"variant" 		=> "dark-red",
											"label"   		=> "PDF",
										])->render();
									@endphp
									newButtonFines	= '{!!preg_replace("/(\r)*(\n)*/", "", $buttonPdf)!!}';
									tr_fines.find('.docsFines').append($('<div class="nowrap have-docs"></div>')
										.append($(newButtonFines).attr('href',url).attr('title',name))
										.append($('<input type="hidden" class="edit_fine_id_document t_id_doc_fine" name="t_fines_id'+num_fine+'[]" value="'+idPath+'">'))
										.append($('<input type="hidden" class="edit_fine_name_document t_name_doc_fine" name="t_fines_name_document'+num_fine+'[]" value="'+name+'">'))
										.append($('<input type="hidden" class="edit_fine_path path-delete t_path_doc_fine" name="t_fines_path'+num_fine+'[]" value="'+path+'">'))
										.append($('<div><label>'+name+'</label></div>'))
									);
								});
							}
							else
							{
								tr_fines.find('.docsFines').append($('<div class="nowrap">No hay documentos</div>'));
							}
						}
						moment.defaultFormat = "DD.MM.YYYY";
						if(fine_status == "Pagado" && fine_payment_date != "")
						{
							startDate	= moment(fine_date,moment.defaultFormat);
							endDate		= moment(fine_payment_limit_date,moment.defaultFormat);
							diff		= moment(endDate).diff(startDate, 'days');
							if(diff < 0)
							{
								$('.fine_payment_limit_date').val('')
								return swal('','La fecha límite de pago de la multa tiene que ser superior a la fecha de la multa.','error');
							}
						}
						if(fine_status == "Pagado" && fine_payment_date != "")
						{
							startDate	= moment(fine_date,moment.defaultFormat);
							endDate		= moment(fine_payment_date,moment.defaultFormat);
							diff		= moment(endDate).diff(startDate, 'days');
							if(diff < 0)
							{
								$('.fine_payment_date').val('')
								return swal('','La fecha del pago de la multa tiene que ser superior a la fecha de la multa.','error');
							}
						}
						tr_fines.find('.count_fine').prepend(num_fine);
						tr_fines.find('.fine-driver-class').val(fine_driver_text);
						tr_fines.find('.t_vehicle_fine_id').parent().prepend(fine_driver_text);
						tr_fines.find('.t_vehicle_fine_id').val(fine_id);
						tr_fines.find('.t_fine_driver').val(fine_driver);
						tr_fines.find('.t_fine_status').parent().prepend(fine_status_text);
						tr_fines.find('.t_fine_status').val(fine_status);
						tr_fines.find('.t_fine_date').parent().prepend(fine_date);
						tr_fines.find('.t_fine_date').val(fine_date);
						tr_fines.find('.t_fine_payment_limit_date').parent().prepend(fine_payment_limit_date);
						tr_fines.find('.t_fine_payment_limit_date').val(fine_payment_limit_date);
						tr_fines.find('.t_fine_payment_date').parent().prepend(fine_payment_date != '' ? fine_payment_date : "---");
						tr_fines.find('.t_fine_payment_date').val(fine_payment_date);
						tr_fines.find('.t_fine_total').parent().prepend("$ "+Number(fine_total).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));
						tr_fines.find('.t_fine_total').val(fine_total);
						$('#body_fines').append(tr_fines);
						$('.fine_status').val(null).trigger('change');
						$('.fine_driver').empty();
						$('.fine_date,.fine_payment_date,.fine_total,.fine_payment_limit_date').removeClass('error').val('');
						$('.fine_id').val('x');
						$('.documents_fines').empty();
						$('.edit-fine').removeAttr('disabled');
						swal('','Multa agregada exitosamente','success');
					}
				}
			})
			.on('click', '#add_kilometer', function()
			{
				$('.date_kilometer_start, .start_kilometer').removeClass('error');
				num_kilometer			=	$('.num_kilometer').val();
				idKilometer				=	$('.id_kilometer').val();
				initDate				=	$('.date_kilometer_start').val();
				initKilometer			=	$('.start_kilometer').val();
				if (num_kilometer == "")
				{
					num_kilometer	=	$('#body_kilometer .body-kilometer').length + 1;
				}
				if (idKilometer == "" || idKilometer == undefined)
				{
					idKilometer	=	"X"	;
				}
				if (initDate == "" || initKilometer == "")
				{
					if (initDate == "")
					{
						$('.date_kilometer_start').addClass('error');
					}
					if (initKilometer == "")
					{
						$('.start_kilometer').addClass('error');
					}
					return swal('','Debe llenar los campos obligatorios','error');
				}
				else if(initKilometer == 0)
				{
					$('.start_kilometer').addClass('error');
					return swal('','El kilometraje no puede ser cero','error');
				}
				else
				{
					@php
						$body	 	= [];
						$modelBody	= [];
						$modelHead	= [ "#", "Fecha", "Kilometraje", "Acción"];

						$body = [ "classEx" => "body-kilometer",
							[
								"classEx"	=> "count_kilometer",
								"content"	=> 
								[
									[
										"label" => ""
									]
								]
							],
							[
								"content"	=>
								[
									[
										"kind"			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"hidden\" name=\"t_init_date[]\"",
										"classEx"		=> "t_init_date"
									],
									[
										"kind"			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"hidden\" name=\"count_kilometer[]\""
									],
									[
										"kind"			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"hidden\" name=\"t_id_kilometer[]\"",
										"classEx"		=> "t_id_kilometer"
									],
									[
										"kind"			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"hidden\"",
										"classEx"		=> "t_num_kilometer"
									]
								]
							],
							[
								"content" =>
								[
									[
										"kind"			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"hidden\" name=\"t_init_kilometer[]\"",
										"classEx"		=> "t_init_kilometer"
									]
								]
							],
							[
								"content" =>
								[
									[
										"kind"			=> "components.buttons.button",
										"variant"		=> "success",
										"attributeEx"	=> "type=\"button\" title=\"Editar kilometraje\"",
										"classEx"		=> "edit-kilometer",
										"label"			=> "<span class=\"icon-pencil\"></span>"	
									]
								]
							]
						];
						$modelBody[] = $body;
						$table = view('components.tables.alwaysVisibleTable',[
							"modelBody" => $modelBody,
							"modelHead" => $modelHead,
							"noHead"	=> true,
							"variant"	=> "default"
						])->render();
					@endphp
					table = '{!!preg_replace("/(\r)*(\n)*/", "", $table)!!}';
					tr = $(table);
					tr.find('.count_kilometer').prepend(num_kilometer);
					tr.find('.t_id_kilometer').val(idKilometer);
					tr.find('.t_num_kilometer').val(num_kilometer);
					tr.find('.t_init_date').parent().prepend(initDate);
					tr.find('.t_init_date').val(initDate);
					tr.find('.t_init_kilometer').parent().prepend(Number(initKilometer));
					tr.find('.t_init_kilometer').val(Number(initKilometer));
					
					$('#body_kilometer').append(tr);
					$('.date_kilometer_start, .start_kilometer').removeClass('error valid').val('');
					swal('','Dato agregado exitosamente','success');
					$('.num_kilometer').val("");
					$('.id_kilometer').val("");
					$('.edit-kilometer').removeAttr('disabled');
				}
			})
			.on('click', '.edit-kilometer', function()
			{
				num_kilometer	=	$(this).parents('.body-kilometer').find('.t_num_kilometer').val();
				id_kilometer	=	$(this).parents('.body-kilometer').find('.t_id_kilometer').val();
				id_kilometer	=	$(this).parents('.body-kilometer').find('.t_id_kilometer').val();
				initDate		=	$(this).parents('.body-kilometer').find('.t_init_date').val();
				initKilometer	=	$(this).parents('.body-kilometer').find('.t_init_kilometer').val();
				
				if ((initDate != null || initDate != "") && (initKilometer != null || initKilometer != ""))
				{
					$('.num_kilometer').val(num_kilometer);
					$('.id_kilometer').val(id_kilometer);
					$('.date_kilometer_start').val(initDate);
					$('.start_kilometer').val(initKilometer);
					$(this).parents('.body-kilometer').remove();
					$('.edit-kilometer').attr('disabled',true);
					$('#body_kilometer .body-kilometer').each(function(i,v)
					{
						$(this).find('.count_kilometer').text(i+1)
					});
				}
				else if (initDate == "" || initKilometer == "")
				{
					if (initDate == "")
					{
						$('.date_kilometer_start').addClass('error');
					}
					if (initKilometer == "")
					{
						$('.start_kilometer').addClass('error');
					}
					return swal('','Debe llenar los campos obligatorios','error');
				}
			})
			.on('click','#add_fuel',function()
			{
				$('.fuel_type,.tag,.fuel_total,.fuel_date').removeClass('error');
				num_fuel	= $('#body_fuel .body-fuel').length + 1;
				fuel_type	= $('.fuel_type').val();
				tag			= $('.tag').val();
				fuel_date	= $('.fuel_date').val();
				fuel_total	= $('.fuel_total').val();
				fuel_id 	= $('.fuel_id').val();

				if (fuel_type == "" || fuel_total == "" || fuel_date == "") 
				{
					if (fuel_type == "")
					{
						$('.fuel_type').addClass('error');
					}
					if (fuel_date == "")
					{
						$('.fuel_date').addClass('error');
					}
					if (fuel_total == "")
					{
						$('.fuel_total').addClass('error');
					}
					return swal('','Debe llenar los campos obligatorios','error');
				}
				else if(fuel_total == 0)
				{
					$('.fuel_total').addClass('error');
					return swal('','El total no puede ser cero','error');
				}
				else
				{
					flag = false;
					$('.fuel_path').each(function()
					{
						path = $(this).val();
						name = $(this).parents('.docs-p').find('.fuel_document').val();
						if (name == "" || path == "") 
						{
							flag = true;
						}
					});

					@php
						$body		= [];
						$modelBody	= [];
						$modelHead	= [
							[
								["value" => "#"],
								["value" => "Tipo de combustible"],
								["value" => "Tag"],
								["value" => "Fecha"],
								["value" => "Total"],
								["value" => "Documentos"],
								["value" => "Acción"],
							]
						];

						$body = [ "classEx" => "body-fuel",
							[
								"classEx"	=> "count_fuel",
								"content"	=>
								[
									"label" => ""
								]
							],
							[
								"content"	=>
								[
									[
										"kind" 			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"hidden\" name=\"t_vehicle_fuel_id[]\"",
										"classEx"		=> "t_vehicle_fuel_id"	
									],
									[
										"kind" 			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"hidden\" name=\"t_fuel_type[]\"",
										"classEx"		=> "t_fuel_type"	
									]
								]
							],
							[
								"content" =>
								[
									[
										"kind" 			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"hidden\" name=\"t_tag[]\"",
										"classEx"		=> "t_tag"	
									]
								]
							],
							[
								"content" =>
								[
									[
										"kind" 			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"hidden\" name=\"t_fuel_date[]\"",
										"classEx"		=> "t_fuel_date"	
									]
								]
							],
							[
								"content" =>
								[
									[
										"kind" 			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"hidden\" name=\"t_fuel_total[]\"",
										"classEx"		=> "t_fuel_total"	
									]
								]
							],
							[
								"classEx"	=> "docsFuel",
								"content"	=>
								[
									"label" => "",	
								]
							],
							[
								"content" =>
								[
									[
										"kind"			=> "components.buttons.button",
										"variant"		=> "success",
										"classEx"		=> "edit-fuel",
										"attributeEx"	=> "type=\"button\" title=\"Editar registro\"",
										"label"			=> "<span class=\"icon-pencil\"></span>"
									],
									[
										"kind"  		=> "components.buttons.button",
										"variant"	 	=> "red",
										"label" 		=> "<span class=\"icon-x\"></span>",
										"attributeEx" 	=> "type=\"button\" title=\"Eliminar registro\"",
										"classEx" 		=> "delete-fuel"
									]
								]
							]
						];
						$modelBody[]	= $body;
						$tableFuel		= view('components.tables.table', [
							"modelHead" => $modelHead,
							"modelBody" => $modelBody, 
							"noHead"	=> "true"
						])->render();
					@endphp
					fuelBody	= '{!!preg_replace("/(\r)*(\n)*/", "", $tableFuel)!!}';
					tr_fuel		= $(fuelBody);

					if (flag) 
					{
						return swal('','Por favor agregue los datos de todos los documentos','info');
					}
					else
					{
						if($('.fuel_path').length > 0)
						{
							$('.fuel_path').each(function(i, v)
							{
								path 	= $(this).val();
								name 	= $(this).parents('.docs-p').find('.fuel_document option:selected').val();
								idPath	= $(this).parents('.docs-p').find('.id-doc').val();
								url 	= '{{ url("docs/vehicles/") }}/'+path;

								@php
									$buttonPdf = view("components.buttons.button",[
										"buttonElement" => "a",
										"attributeEx" 	=> "target=\"_blank\"",
										"variant" 		=> "dark-red",
										"label"   		=> "PDF",
									])->render();
								@endphp
								newButtonPDF	= '{!!preg_replace("/(\r)*(\n)*/", "", $buttonPdf)!!}';

								tr_fuel.find('.docsFuel').append($('<div class="nowrap have-docs"></div>')
									.append($(newButtonPDF).attr('title',name).attr('href',url))
									.append($('<input type="hidden" class="edit_fuel_id_doc" name="t_fuel_id_document'+num_fuel+'[]" value="'+idPath+'">'))
									.append($('<input type="hidden" class="edit_fuel_name_document" name="t_fuel_name_document'+num_fuel+'[]" value="'+name+'">'))
									.append($('<input type="hidden" class="edit_fuel_path path-delete" name="t_fuel_path'+num_fuel+'[]" value="'+path+'">'))
									.append($('<div><label>'+name+'</label></div>'))
								);
							});
						}
						else
						{
							tr_fuel.find('.docsFuel').append($('<div class="nowrap">No hay documentos</div>'));
						}
					}
					tr_fuel.find('.t_vehicle_fuel_id').val(fuel_id);
					tr_fuel.find('.count_fuel').prepend(num_fuel);
					fuel_type = String(fuel_type).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
					tr_fuel.find('.t_fuel_type').parent().prepend(fuel_type);
					tr_fuel.find('.t_fuel_type').val(fuel_type);
					tag = String(tag).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
					tr_fuel.find('.t_tag').parent().prepend(tag != "" ? tag : "---");
					tr_fuel.find('.t_tag').val(tag);
					tr_fuel.find('.t_fuel_date').parent().prepend(fuel_date);
					tr_fuel.find('.t_fuel_date').val(fuel_date);
					tr_fuel.find('.t_fuel_total').parent().prepend("$ "+Number(fuel_total).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));
					tr_fuel.find('.t_fuel_total').val(fuel_total);

					$('#body_fuel').append(tr_fuel);
					$('.fuel_type,.tag,.fuel_total,.fuel_date').removeClass('error').val('');
					$('.fuel-id').val('x');
					$('.documents_fuel').empty();
					$('.edit-fuel').removeAttr('disabled');
					swal('','Dato agregado exitosamente','success');
				}
			})
			.on('click','#add_taxes',function()
			{
				$('.date_verification,.next_date_verification,.total_verification').removeClass('error');
				num_taxes				= $('#body_taxes .body-taxes').length + 1;
				date_verification		= $('.date_verification').val();
				next_date_verification	= $('.next_date_verification').val();
				total_verification		= $('.total_verification').val();
				total_gestoria			= $('.monto_gestoria').val();
				taxes_id 				= $('.taxes_id').val();

				if (date_verification == "" || next_date_verification == "" || total_verification == "") 
				{
					if (date_verification == "")
					{
						$('.date_verification').addClass('error');
					}
					if (next_date_verification == "")
					{
						$('.next_date_verification').addClass('error');
					}
					if (total_verification == "")
					{
						$('.total_verification').addClass('error');
					}

					return swal('','Debe llenar los campos obligatorios','error');
				}
				else if(total_verification == 0)
				{
					$('.fuel_total').addClass('error');
					return swal('','El monto de verificación no puede ser cero','error');
				}
				else
				{
					flag = false;
					$('.taxes_path').each(function()
					{
						path = $(this).val();
						name = $(this).parents('.docs-p').find('.taxes_document').val();
						date = $(this).parents('.docs-p').find('.taxes_date').val();
						if (name == "" || path == "" || date == "") 
						{
							flag = true;
						}
					});

					@php
						$body		= [];
						$modelBody	= [];
						$modelHead	= [
							[
								["value" => "#"],
								["value" => "Fecha de Verificación"],
								["value" => "Próxima Fecha de Verificación"],
								["value" => "Monto Total"],
								["value" => "Monto Gestoría"],
								["value" => "Documentos"],
								["value" => "Acción"]
							]
						];
						$body = [ "classEx" => "body-taxes",
							[
								"classEx"	=> "count_taxes",
								"content"	=>
								[
									"label"	=> ""
								]
							],
							[
								"content"	=>
								[
									[
										"kind"			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"hidden\" name=\"t_vehicle_taxes_id[]\"",
										"classEx"		=> "t_vehicle_taxes_id"
									],
									[
										"kind"			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"hidden\" name=\"t_date_verification[]\"",
										"classEx"		=> "t_date_verification"
									]
								]
							],
							[
								"content"	=>
								[
									[
										"kind"			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"hidden\" name=\"t_next_date_verification[]\"",
										"classEx"		=> "t_next_date_verification"
									]
								]
							],
							[
								"content"	=>
								[
									[
										"kind"			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"hidden\" name=\"t_total_verification[]\"",
										"classEx"		=> "t_total_verification"
									]
								]
							],
							[
								"content"	=>
								[
									[
										"kind"			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"hidden\" name=\"t_monto_gestoria[]\"",
										"classEx"		=> "t_monto_gestoria"
									]
								]
							],
							[
								"classEx" => "docsTaxes",
								"content" =>
								[
									"label" => ""
								]
							],
							[
								"content" =>
								[
									[
										"kind"			=> "components.buttons.button",
										"variant"		=> "success",
										"classEx"		=> "edit-tax",
										"attributeEx"	=> "type=\"button\" title=\"Editar impuesto\"",
										"label"			=> "<span class=\"icon-pencil\"></span>"
									],		
									[
										"kind"  		=> "components.buttons.button",
										"variant"	 	=> "red",
										"label" 		=> "<span class=\"icon-x\"></span>",
										"attributeEx" 	=> "type=\"button\" title=\"Eliminar impuesto\"",
										"classEx" 		=> "delete-tax"
									]
								]
							]
						];
						$modelBody[] 	= $body;
						$tableTaxes		= view('components.tables.table', [
							"modelHead" => $modelHead,
							"modelBody" => $modelBody, 
							"noHead"	=> "true"
						])->render();
					@endphp
					taxesBody	= '{!!preg_replace("/(\r)*(\n)*/", "", $tableTaxes)!!}';
					tr_taxes	= $(taxesBody);

					if (flag) 
					{
						return swal('','Por favor agregue los datos de todos los documentos','info');
					}
					else
					{
						if($('.taxes_path').length > 0)
						{
							$('.taxes_path').each(function(i, v)
							{
								name 	= $(this).parents('.docs-p').find('.taxes_document option:selected').val();
								path 	= $(this).val();
								date 	= $(this).parents('.docs-p').find('.taxes_date').val();
								idPath	= $(this).parents('.docs-p').find('.id-doc').val();
								url 	= '{{ url("docs/vehicles/") }}/'+path;

								@php
									$buttonPdfTax = view("components.buttons.button",[
										"buttonElement" => "a",
										"attributeEx" 	=> "target=\"_blank\"",
										"variant" 		=> "dark-red",
										"label"   		=> "PDF",
									])->render();
								@endphp
								newButtonTax 	= '{!!preg_replace("/(\r)*(\n)*/", "", $buttonPdfTax)!!}';
								tr_taxes.find('.docsTaxes').append($('<div class="nowrap have-docs"></div>')
									.append($(newButtonTax).attr('title',name).attr('href',url))
									.append($('<input type="hidden" class="edit_taxes_id_doc" name="t_taxes_id_document'+num_taxes+'[]" value="'+idPath+'">'))
									.append($('<input type="hidden" class="edit_taxes_name_document" name="t_taxes_name_document'+num_taxes+'[]" value="'+name+'">'))
									.append($('<input type="hidden" class="edit_taxes_path path-delete" name="t_taxes_path'+num_taxes+'[]" value="'+path+'">'))
									.append($('<input type="hidden" class="edit_taxes_date" name="t_taxes_date'+num_taxes+'[]" value="'+date+'">'))
									.append($('<div><label>'+name+'</label></div>'))
								);
							});
						}
						else
						{
							tr_taxes.find('.docsTaxes').append($('<div class="nowrap">No hay documentos</div>'));
						}
					}
					moment.defaultFormat	= "DD.MM.YYYY";
					startDate				= moment(date_verification,moment.defaultFormat);
					endDate					= moment(next_date_verification,moment.defaultFormat);
					diff					= moment(endDate).diff(startDate, 'days');
					if(diff <= 0)
					{
						$('.next_date_verification').val('')
						return swal('','La fecha de la próxima verificación tiene que ser superior a la fecha de la actual verificación.','error');
					}
					tr_taxes.find('.count_taxes').prepend(num_taxes);
					tr_taxes.find('.t_date_verification').parent().prepend(date_verification);
					tr_taxes.find('.t_date_verification').val(date_verification);
					tr_taxes.find('.t_vehicle_taxes_id').val(taxes_id);
					tr_taxes.find('.t_next_date_verification').parent().prepend(next_date_verification);
					tr_taxes.find('.t_next_date_verification').val(next_date_verification);
					tr_taxes.find('.t_total_verification').parent().prepend("$ "+Number(total_verification).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));
					tr_taxes.find('.t_total_verification').val(total_verification);
					tr_taxes.find('.t_monto_gestoria').parent().prepend("$ "+Number(total_gestoria).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));
					tr_taxes.find('.t_monto_gestoria').val(total_gestoria);

					$('#body_taxes').append(tr_taxes);
					$('.date_verification,.next_date_verification,.total_verification,.monto_gestoria').removeClass('error').val('');
					$('.documents_taxes').empty();
					$('.taxes_id').val('x');
					$('.edit-tax').removeAttr('disabled');
					swal('','Impuesto agregado exitosamente','success');
				}
			})
			.on('click','#add_mechanical_services',function()
			{
				$('.date_last_service,.next_service_date,.repairs').removeClass('error');
				num_mechanical_services		= $('#body_mechanical_services .body-mechanical').length + 1;
				date_last_service			= $('.date_last_service').val();
				next_service_date			= $('.next_service_date').val();
				repairs						= $('.repairs').val();
				mechanical_service_total	= $('.mechanical_service_total').val();
				msID						= $('.ms_id').val();

				if (date_last_service == "" || repairs == "" || mechanical_service_total == "") 
				{
					if (date_last_service == "")
					{
						$('.date_last_service').addClass('error');
					}
					if (repairs == "")
					{
						$('.repairs').addClass('error');
					}
					if (mechanical_service_total == "")
					{
						$('.mechanical_service_total').addClass('error');
					}
					return swal('','Debe llenar los campos obligatorios','error');
				}
				else if(mechanical_service_total == 0)
				{
					$('.mechanical_service_total').addClass('error');
					return swal('','El total no puede ser cero','error');
				}
				else
				{
					flag = false;
					$('.mechanical_services_path').each(function()
					{
						path = $(this).val();
						name = $(this).parents('.docs-p').find('.mechanical_services_document').val();
						if (name == "" || path == "") 
						{
							flag = true;
						}
					});

					@php
						$body 		= [];
						$modelBody	= [];
						$modelHead	= [
							[
								["value" => "#"],
								["value" => "Fecha de Último Servicio"],
								["value" => "Fecha de Próximo Servicio"],
								["value" => "Reparaciones"],
								["value" => "Total"],
								["value" => "Documentos"],
								["value" => "Acción"]
							]
						];
						$body = [ "classEx" => "body-mechanical",
							[
								"classEx"	=> "count_ms",
								"content"	=>
								[
									"label" => ""
								]
							],
							[
								"content"	=>
								[
									[
										"kind"			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"hidden\" name=\"t_mechanical_services_id[]\"",
										"classEx"		=> "t_mechanical_services_id"
									],
									[
										"kind"			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"hidden\" name=\"t_date_last_service[]\"",
										"classEx"		=> "t_date_last_service"
									]
								]
							],
							[
								"content"	=>
								[
									[
										"kind"			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"hidden\" name=\"t_next_service_date[]\"",
										"classEx"		=> "t_next_service_date"
									]
								]
							],
							[
								"content"	=>
								[
									[
										"kind"			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"hidden\" name=\"t_repairs[]\"",
										"classEx"		=> "t_repairs"
									]
								]
							],
							[
								"content"	=>
								[
									[
										"kind"			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"hidden\" name=\"t_mechanical_service_total[]\"",
										"classEx"		=> "t_mechanical_service_total"
									]
								]
							],
							[
								"classEx" => "docsMechanical",
								"content" =>
								[
									"label" => ""
								]
							],
							[
								"content" =>
								[
									[
										"kind"			=> "components.buttons.button",
										"variant"		=> "success",
										"classEx"		=> "edit-mechanical-service",
										"attributeEx"	=> "type=\"button\" title=\"Editar servicio mecánico\"",
										"label"			=> "<span class=\"icon-pencil\"></span>"
									],
									[
										"kind"  		=> "components.buttons.button",
										"variant"	 	=> "red",
										"label" 		=> "<span class=\"icon-x\"></span>",
										"attributeEx" 	=> "type=\"button\" title=\"Eliminar servicio mecánico\"",
										"classEx" 		=> "delete-mechanical-service"
									]
								]
							]
						];
						$modelBody[]		= $body;
						$tableMechanical	= view('components.tables.table', [ "modelBody" => $modelBody, "modelHead" => $modelHead, "noHead" => "true" ])->render();
					@endphp
					mechanicalBody	= '{!!preg_replace("/(\r)*(\n)*/", "", $tableMechanical)!!}';
					tr_mechanical	= $(mechanicalBody);

					if (flag) 
					{
						return swal('','Por favor agregue los datos de todos los documentos','info');
					}
					else
					{
						if($('.mechanical_services_path').length > 0)
						{
							$('.mechanical_services_path').each(function()
							{
								name 	= $(this).parents('.docs-p').find('.mechanical_services_document option:selected').val();
								path 	= $(this).val();
								idPath	= $(this).parents('.docs-p').find('.id-doc').val();
								url		= '{{ url("docs/vehicles/") }}/'+path;

								@php
									$buttonPdf = view("components.buttons.button",[
										"buttonElement" => "a",
										"attributeEx" 	=> "target=\"_blank\"",
										"variant" 		=> "dark-red",
										"label"   		=> "PDF",
									])->render();
								@endphp
								newButtonMechanical = '{!!preg_replace("/(\r)*(\n)*/", "", $buttonPdf)!!}';
								tr_mechanical.find('.docsMechanical').append($('<div class="nowrap have-docs"></div>')
									.append($(newButtonMechanical).attr('title',name).attr('href',url))
									.append($('<input type="hidden" class="edit_ms_id_document" name="t_ms_id_document'+num_mechanical_services+'[]" value="'+idPath+'">'))
									.append($('<input type="hidden" class="edit_ms_name_document" name="t_ms_name_document'+num_mechanical_services+'[]" value="'+name+'">'))
									.append($('<input type="hidden" class="edit_ms_path path-delete" name="t_ms_path'+num_mechanical_services+'[]" value="'+path+'">'))
									.append($('<div><label>'+name+'</label></div>'))
								);		
							});
						}
						else
						{
							tr_mechanical.find('.docsMechanical').append($('<div class="nowrap">No hay documentos</div>'));
						}
					}
					moment.defaultFormat	= "DD.MM.YYYY";
					startDate				= moment(date_last_service,moment.defaultFormat);
					endDate					= moment(next_service_date,moment.defaultFormat);
					diff					= moment(endDate).diff(startDate, 'days');
					if(diff <= 0)
					{
						$('.next_service_date').val('')
						return swal('','La fecha del próximo servicio tiene que ser superior a la fecha del último servicio.','error');
					}
					tr_mechanical.find('.t_mechanical_services_id').val(msID);
					tr_mechanical.find('.count_ms').prepend(num_mechanical_services);
					tr_mechanical.find('.t_date_last_service').parent().prepend(date_last_service);
					tr_mechanical.find('.t_date_last_service').val(date_last_service);
					tr_mechanical.find('.t_next_service_date').parent().prepend(next_service_date != "" ? next_service_date : "---");
					tr_mechanical.find('.t_next_service_date').val(next_service_date);
					repairs = String(repairs).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
					tr_mechanical.find('.t_repairs').parent().prepend(repairs);
					tr_mechanical.find('.t_repairs').val(repairs);
					tr_mechanical.find('.t_mechanical_service_total').parent().prepend("$ "+Number(mechanical_service_total).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));
					tr_mechanical.find('.t_mechanical_service_total').val(mechanical_service_total);
					$('#body_mechanical_services').append(tr_mechanical);
					$('.date_last_service,.next_service_date,.repairs,.mechanical_service_total').removeClass('error').val('');
					$('.documents_mechanical_services').empty();
					$('.edit-mechanical-service').removeAttr('disabled');
					$('.ms_id').val('x');
					swal('','Servicio mecánico agregado exitosamente','success');
				}
			})
			.on('click','#add_insurance',function()
			{
				$('.insurance_carrier,.expiration_date,.insurance_total').removeClass('error');
				num_insurances			= $('#body_insurances .body-insurances').length + 1;
				insurance_carrier		= $('.insurance_carrier').val();
				expiration_date			= $('.expiration_date').val();
				insurance_total			= $('.insurance_total').val();
				insurance_id 			= $('.insurance_id').val();

				if (insurance_carrier == "" || expiration_date == "" || insurance_total == "") 
				{
					if (insurance_carrier == "")
					{
						$('.insurance_carrier').addClass('error');
					}
					if (expiration_date == "")
					{
						$('.expiration_date').addClass('error');
					}
					if (insurance_total == "")
					{
						$('.insurance_total').addClass('error');
					}
					return swal('','Debe llenar los campos obligatorios','error');
				}
				else if(insurance_total == 0)
				{
					$('.insurance_total').addClass('error');
					return swal('','El total no puede ser cero','error');
				}
				else
				{
					flag = false;
					$('.insurance_path').each(function()
					{
						path = $(this).val();
						name = $(this).parents('.docs-p').find('.insurance_document').val();
						if (name == "" || path == "") 
						{
							flag = true;
						}
					});

					@php
						$body		= [];
						$modelBody	= [];
						$modelHead	= [
							[
								["value" => "#"],
								["value" => "Aseguradora"],
								["value" => "Fecha de Vencimiento"],
								["value" => "Total"],
								["value" => "Documentos"],
								["value" => "Acción"]
							]
						];
						$body = [ "classEx" => "body-insurances",
							[							
								"classEx"	=> "count_insurances",
								"content"	=>
								[
									"label" => ""
								]
							],
							[
								"content"	=>
								[
									[
										"kind"			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"hidden\" name=\"t_insurance_id[]\"",
										"classEx"		=> "t_insurance_id"
									],
									[
										"kind"			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"hidden\" name=\"t_insurance_carrier[]\"",
										"classEx"		=> "t_insurance_carrier"
									]
								]
							],
							[
								"content" =>
								[
									[
										"kind"			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"hidden\" name=\"t_expiration_date[]\"",
										"classEx"		=> "t_expiration_date"
									]
								]
							],
							[
								"content" =>
								[
									[
										"kind"			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"hidden\" name=\"t_insurance_total[]\"",
										"classEx"		=> "t_insurance_total"
									]
								]
							],
							[
								"classEx" => "docsInsurances",
								"content" =>
								[
									"label" => ""
								]
							],
							[
								"content" =>
								[
									[
										"kind"			=> "components.buttons.button",
										"variant"		=> "success",
										"classEx"		=> "edit-insurance",
										"attributeEx"	=> "type=\"button\" title=\"Editar seguro\"",
										"label"			=> "<span class=\"icon-pencil\"></span>"
									],
									[	 
										"kind"  		=> "components.buttons.button",
										"variant"	 	=> "red",
										"label" 		=> "<span class=\"icon-x\"></span>",
										"attributeEx" 	=> "type=\"button\" title=\"Eliminar servicio mecánico\"",
										"classEx" 		=> "delete-insurance"	
									]
								]
							]
						];
						$modelBody[] 		= $body;
						$tableInsurances	= view('components.tables.table',[
							"modelBody"		=> $modelBody,
							"modelHead" 	=> $modelHead, 
							"noHead"		=> "true"
						])->render();
					@endphp
					insurancesBody	= '{!!preg_replace("/(\r)*(\n)*/", "", $tableInsurances)!!}';
					tr_insurances	= $(insurancesBody);
					
					if (flag) 
					{
						return swal('','Por favor agregue los datos de todos los documentos','info');
					}
					else
					{
						if($('.insurance_path').length > 0)
						{
							$('.insurance_path').each(function()
							{
								path 	= $(this).val();
								name 	= $(this).parents('.docs-p').find('.insurance_document option:selected').val();
								idPath	= $(this).parents('.docs-p').find('.id-doc').val();
								url 	= '{{ url("docs/vehicles/") }}/'+path;

								@php
									$buttonPdf = view("components.buttons.button",[
										"buttonElement" => "a",
										"attributeEx" 	=> "target=\"_blank\"",
										"variant" 		=> "dark-red",
										"label"   		=> "PDF",
									])->render();
								@endphp
								newButtonInsurances = '{!!preg_replace("/(\r)*(\n)*/", "", $buttonPdf)!!}';
								tr_insurances.find('.docsInsurances').append($('<div class="nowrap have-docs"></div>')
									.append($(newButtonInsurances).attr('href',url).attr('title',name))
									.append($('<input type="hidden" class="edit_insurance_id_doc" name="t_insurance_id_document'+num_insurances+'[]" value="'+idPath+'">'))
									.append($('<input type="hidden" class="edit_insurance_name_document" name="t_insurance_name_document'+num_insurances+'[]" value="'+name+'">'))
									.append($('<input type="hidden" class="edit_insurance_path path-delete" name="t_insurance_path'+num_insurances+'[]" value="'+path+'">'))
									.append($('<div><label>'+name+'</label></div>'))
								);
							});
						}
						else
						{
							tr_insurances.find('.docsInsurances').append($('<div class="nowrap">No hay documentos</div>'));
						}
					}
					tr_insurances.find('.t_insurance_id').val(insurance_id);
					tr_insurances.find('.count_insurances').prepend(num_insurances);
					insurance_carrier = String(insurance_carrier).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
					tr_insurances.find('.t_insurance_carrier').parent().prepend(insurance_carrier);
					tr_insurances.find('.t_insurance_carrier').val(insurance_carrier);
					tr_insurances.find('.t_expiration_date').parent().prepend(expiration_date);
					tr_insurances.find('.t_expiration_date').val(expiration_date);
					tr_insurances.find('.t_insurance_total').parent().prepend("$ "+Number(insurance_total).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));
					tr_insurances.find('.t_insurance_total').val(insurance_total);
					$('#body_insurances').append(tr_insurances);
					$('.insurance_carrier,.expiration_date,.insurance_total').removeClass('error').val('');
					$('.documents_insurance').empty();
					$('.edit-insurance').removeAttr('disabled');
					swal('','Seguro agregado exitosamente','success');
				}
			})
			.on('click','.delete-fuel',function()
			{
				swal(
				{
					title		: "Confirmar",
					text		: "¿Desea eliminar el registro?",
					icon		: "warning",
					buttons		: ["Cancelar","OK"],
					dangerMode	: true,
				})
				.then((willDelete) => 
				{
					if (willDelete) 
					{
						id = $(this).parents('.body-fuel').find('.t_vehicle_fuel_id').val();
						if (id != "x") 
						{
							input = $('<input type="hidden" name="delete_vehicle_fuel[]" value="'+id+'">');
							$('#invisible').append(input);
							$(this).parents('.body-fuel').remove();
						}
						else
						{
							actioner = $(this);
							section  = "table";
							deleteDocs(actioner, section);
						}
						$('#body_fuel .body-fuel').each(function(i,v)
						{
							$(this).find('.count_fuel').text(i+1);
						});
						swal('','Registro eliminado exitosamente','success');
					}
				});
			})
			.on('click','.delete-fine',function()
			{
				swal(
				{
					title		: "Confirmar",
					text		: "¿Desea eliminar el registro?",
					icon		: "warning",
					buttons		: ["Cancelar","OK"],
					dangerMode	: true,
				})
				.then((willDelete) => 
				{
					if (willDelete) 
					{
						id = $(this).parents('.body-fines').find('.t_vehicle_fine_id').val();
						if (id != "x") 
						{
							input = $('<input type="hidden" name="delete_vehicle_fine[]" value="'+id+'">');
							$('#invisible').append(input);
							$(this).parents('.body-fines').remove();
						}
						else
						{
							actioner = $(this);
							section  = "table";
							deleteDocs(actioner, section);
						}
						$('#body_fines .body-fines').each(function(i,v)
						{
							$(this).find('.count_fine').text(i+1);
						});
						swal('','Registro eliminado exitosamente','success');
					}
				});
			})
			.on('click','.delete-tax',function()
			{
				swal(
				{
					title		: "Confirmar",
					text		: "¿Desea eliminar el registro?",
					icon		: "warning",
					buttons		: ["Cancelar","OK"],
					dangerMode	: true,
				})
				.then((willDelete) => 
				{
				  	if (willDelete) 
				  	{
						id = $(this).parents('.body-taxes').find('.t_vehicle_taxes_id').val();
						if (id != "x") 
						{
							input = $('<input type="hidden" name="delete_vehicle_taxes[]" value="'+id+'">');
							$('#invisible').append(input);
							$(this).parents('.body-taxes').remove();
						}
						else
						{
							actioner = $(this);
							section  = "table";
							deleteDocs(actioner, section);
						}
						$('#body_taxes .body-taxes').each(function(i,v)
						{
							$(this).find('.count_taxes').text(i+1);
						});
						swal('','Registro eliminado exitosamente','success');
					}
				});
			})
			.on('click','.delete-mechanical-service',function()
			{
				swal(
				{
					title		: "Confirmar",
					text		: "¿Desea eliminar el registro?",
					icon		: "warning",
					buttons		: ["Cancelar","OK"],
					dangerMode	: true,
				})
				.then((willDelete) => 
				{
				  	if (willDelete) 
				  	{
						id = $(this).parents('.body-mechanical').find('.t_mechanical_services_id').val();
						if (id != "x") 
						{
							input = $('<input type="hidden" name="delete_mechanical_services[]" value="'+id+'">');
							$('#invisible').append(input);
							$(this).parents('.body-mechanical').remove();
						}
						else
						{
							actioner = $(this);
							section  = "table";
							deleteDocs(actioner, section);
						}
						$('#body_mechanical_services .body-mechanical').each(function(i,v)
						{
							$(this).find('.count_ms').text(i+1);
						});
						swal('','Registro eliminado exitosamente','success');
					}
				});
			})
			.on('click','.delete-insurance',function()
			{
				swal(
				{
					title		: "Confirmar",
					text		: "¿Desea eliminar el registro?",
					icon		: "warning",
					buttons		: ["Cancelar","OK"],
					dangerMode	: true,
				})
				.then((willDelete) => 
				{
				  	if (willDelete) 
				  	{
						id = $(this).parents('.body-insurances').find('.t_insurance_id').val();
						if (id != "x") 
						{
							input = $('<input type="hidden" name="delete_insurance[]" value="'+id+'">');
							$('#invisible').append(input);
							$(this).parents('.body-insurances').remove();
						}
						else
						{
							actioner = $(this);
							section  = "table";
							deleteDocs(actioner, section);
						}
						$('#body_insurances .body-insurances').each(function(i,v)
						{
							$(this).find('.count_insurances').text(i+1);
						});
						swal('','Registro eliminado exitosamente','success');
					}
				});
			})
			.on('click','.delete-document',function()
			{
				swal(
				{
					title		: "",
					text		: "¿Desea eliminar el documento?",
					icon		: "warning",
					buttons		: ["Cancelar","OK"],
					dangerMode	: true,
				})
				.then((willDelete) => 
				{
				  	if (willDelete) 
				  	{
						id = $(this).parents('.body-row').find('.vehicle_document_id').val();
						if (id != "x") 
						{
							input = $('<input type="hidden" name="delete_document[]" value="'+id+'">');
							$('#invisible').append(input);
						}
						$(this).parents('.body-row').remove();
						$('#body_technical .body-row').each(function(i,v)
						{
							$(this).find('.count_doc_technical').text(i+1);
						});
						$('#body_owner .body-row').each(function(i,v)
						{
							$(this).find('.count_doc_owner').text(i+1);
						});
						swal('','Documento eliminado exitosamente','success');
					}
				});
			})
			.on('click','.reset',function(e)
			{
				e.preventDefault();
				form = $(this).parents('form');
				swal({
					title		: "Limpiar formulario",
					text		: "¿Confirma que desea limpiar el formulario?",
					icon		: "warning",
					buttons		: ["Cancelar","OK"],
					dangerMode	: true,
				})
				.then((clean) =>
				{
					if(clean)
					{
						form[0].reset();
						$('#body_fuel,#body_fines,#body_taxes,#body_insurances,#body_owner,#body_mechanical_services,#body_kilometer').html('');
						$('.documents_fuel,.documents_fines,.documents_insurance,.documents_owner,.documents_technical_specifications,.documents_mechanical_services,.documents_taxes').empty();
						$('.general-class').val('');
						$('.removeselect').val(null).trigger('change');
					}
					else
					{
						swal.close();
					}
				});
			})
			.on('click','.edit-fine',function()
			{
				t_vehicle_fine_id			= $(this).parents('.body-fines').find('[name="t_vehicle_fine_id[]"]').val();
				t_fine_driver				= $(this).parents('.body-fines').find('[name="t_fine_driver[]"]').val();
				t_name_driver				= $(this).parents('.body-fines').find('.fine-driver-class').val();
				t_fine_status				= $(this).parents('.body-fines').find('[name="t_fine_status[]"]').val();
				t_fine_date					= $(this).parents('.body-fines').find('[name="t_fine_date[]"]').val();
				t_fine_payment_limit_date	= $(this).parents('.body-fines').find('[name="t_fine_payment_limit_date[]"]').val();
				t_fine_payment_date			= $(this).parents('.body-fines').find('[name="t_fine_payment_date[]"]').val();
				t_fine_total				= $(this).parents('.body-fines').find('[name="t_fine_total[]"]').val();
				docs 						= $(this).parents('.body-fines').find('.have-docs');
				$('.documents_fines').empty();

				$(docs).each(function(i,v)
				{
					name 	= $(this).find('.t_name_doc_fine').val();
					path 	= $(this).find('.t_path_doc_fine').val();
					idPath 	= $(this).find('.t_id_doc_fine').val();

					if (name != "" && path != "") 
					{
						@php
							$editFine = view('components.documents.upload-files',
							[
								"classExContainer"		=> "image_success",
								"classExInput" 			=> "pathActioner",
								"attributeExInput"		=> "type=\"file\" name=\"path\" accept=\".pdf,.jpg,.png\"",
								"classExDelete"			=> "delete-doc",
								"attributeExRealPath"	=> "type=\"hidden\" name=\"fines_path\"",
								"classExRealPath"		=> "path fines_path",
								"componentsExUp"		=> 	
								[
									[
										"kind"			=> "components.inputs.input-text",
										"classEx"		=> "id-doc",
										"attributeEx"	=> "type=\"hidden\" name=\"idFine\""
									],
									[
										"kind" 	=> "components.labels.label", 
										"label" => "Seleccione el tipo de documento:"
									],
									[
										"kind" 			=> "components.inputs.select",
										"classEx" 		=> "nameDocument fines_document",
										"attributeEx"	=> "multiple name=\"fineSelect\"" 
									]
								],
								"componentsAction" => 
								[[
									"kind" 			=> "components.buttons.button",
									"variant" 		=> "warning",
									"buttonElement"	=> "a",
									"attributeEx" 	=> "target=\"_blank\"",
									"classEx" 		=> "set-href",
									"label" 		=> "Ver documento"
								]],
							])->render();
						@endphp
						newDocFines = '{!!preg_replace("/(\r)*(\n)*/", "", $editFine)!!}';
						docFine 	= $(newDocFines);

						docFine.find('[name="idFine"]').val(idPath);
						docFine.find('[name="fines_path"]').val(path);
						docFine.find('[name="fineSelect"]').append($('<option value="Comprobante de Pago"'+(name == "Comprobante de Pago" ? "selected='selected'" : "")+'>Comprobante de Pago</option>'));
						docFine.find('[name="fineSelect"]').append($('<option value="Multa"'+(name == "Multa" ? "selected='selected'" : "")+'>Multa</option>'));
						docFine.find('.set-href').prop("href", '{{ url("docs/vehicles/") }}/'+path);
						docFine.find('.set-href').removeClass('set-href');
						$('.documents_fines').append(docFine);
					}
				});
				$('.datepicker').datepicker({ maxDate:0, dateFormat: "dd-mm-yy" });
				@php
					$selects = collect ([
						[
							"identificator"				=> ".nameDocument",
							"placeholder"				=> "Seleccione el tipo de documento",
							"maximumSelectionLength"	=> "1"
						]
					]);
				@endphp
				@component('components.scripts.selects',["selects" => $selects]) @endcomponent
				
				$('.fine_driver').append(new Option(t_name_driver, t_fine_driver, true, true)).trigger('change');
				$('.fine_status').val(t_fine_status).trigger('change');
				$('.fine_date').val(t_fine_date);
				$('.fine_payment_limit_date').val(t_fine_payment_limit_date);
				$('.fine_payment_date').val(t_fine_payment_date);
				$('.fine_total').val(t_fine_total);
				$('.fine_id').val(t_vehicle_fine_id);
				$('.edit-fine').attr('disabled','disabled');
				$(this).parents('.body-fines').remove();
				$('#body_fines .body-fines').each(function(i,v)
				{
					$(this).find('.count_fine').text(i+1);
				});
			})
			.on('input','.start_kilometer',function() 
			{ 
			    this.value = this.value.replace(/[^0-9]/g,'');
			})
			.on('click','.edit-insurance',function()
			{
				t_insurance_carrier	= $(this).parents('.body-insurances').find('[name="t_insurance_carrier[]"]').val();
				t_expiration_date	= $(this).parents('.body-insurances').find('[name="t_expiration_date[]"]').val();
				t_insurance_total	= $(this).parents('.body-insurances').find('[name="t_insurance_total[]"]').val();
				t_insurance_id		= $(this).parents('.body-insurances').find('[name="t_insurance_id[]"]').val();
				docs				= $(this).parents('.body-insurances').find('.have-docs');
				$('.documents_insurance').empty();
				$(docs).each(function(i,v)
				{
					name 	= $(this).find('.edit_insurance_name_document').val();
					path 	= $(this).find('.edit_insurance_path').val();
					idPath 	= $(this).find('.edit_insurance_id_doc').val();
					if (name != "" && path != "") 
					{
						@php
							$editInsurances = view('components.documents.upload-files',
							[
								"classExContainer"		=> "image_success",
								"classExInput"			=> "pathActioner",
								"attributeExInput"		=> "type=\"file\" name=\"path\" accept=\".pdf,.jpg,.png\"",
								"classExDelete"			=> "delete-doc",
								"attributeExRealPath"	=> "type=\"hidden\"",
								"classExRealPath"		=> "path insurance_path",
								"componentsExUp"		=>	
								[
									[
										"kind"			=> "components.inputs.input-text",
										"classEx"		=> "id-doc",
										"attributeEx"	=> "type=\"hidden\" name=\"insurancesID\"" 
									],
									[
										"kind" 	=> "components.labels.label", 
										"label" => "Seleccione el tipo de documento:"
									],
									[
										"kind" 			=> "components.inputs.select",
										"classEx" 		=> "nameDocument insurance_document",
										"attributeEx"	=> "multiple name=\"insurancesSelect\"" 
									]
								],
								"componentsAction" => 
								[[
									"kind" 			=> "components.buttons.button",
									"variant" 		=> "warning",
									"buttonElement"	=> "a",
									"attributeEx" 	=> "target=\"_blank\"",
									"classEx" 		=> "set-href",
									"label" 		=> "Ver documento"
								]],
							])->render();
						@endphp
						insuranceDocs	= '{!!preg_replace("/(\r)*(\n)*/", "", $editInsurances)!!}';
						docInsurances	= $(insuranceDocs);

						docInsurances.find('.insurance_path').val(path);
						docInsurances.find('[name="insurancesID"]').val(idPath);
						docInsurances.find('[name="insurancesSelect"]').append($('<option value="Comprobante de Pago"  '+(name == "Comprobante de Pago" ? "selected='selected'" : "")+'>Comprobante de Pago</option>'));
						docInsurances.find('[name="insurancesSelect"]').append($('<option value="Póliza de Seguro"  '+(name == "Póliza de Seguro" ? "selected='selected'" : "")+'>Póliza de Seguro</option>'));
						docInsurances.find('.set-href').prop("href", '{{ url("docs/vehicles/") }}/'+path);
						docInsurances.find('.set-href').removeClass('set-href');
						$('.documents_insurance').append(docInsurances);
						@php
							$selects = collect ([
								[
									"identificator"				=> ".nameDocument",
									"placeholder"				=> "Seleccione el tipo de documento",
									"maximumSelectionLength"	=> "1"
								]
							]);
						@endphp
						@component('components.scripts.selects',["selects" => $selects]) @endcomponent
					}
				});
				$('.insurance_carrier').val(t_insurance_carrier);
				$('.expiration_date').val(t_expiration_date);
				$('.insurance_total').val(t_insurance_total);
				$('.insurance_id').val(t_insurance_id);
				$('.edit-insurance').attr('disabled','disabled');
				$(this).parents('.body-insurances').remove();
				$('#body_insurances .body-insurances').each(function(i,v)
				{
					$(this).find('.count_insurances').text(i+1);
				});
			})
			.on('click','.edit-mechanical-service',function()
			{	
				t_date_last_service  		= $(this).parents('.body-mechanical').find('[name="t_date_last_service[]"]').val();
				t_next_service_date  		= $(this).parents('.body-mechanical').find('[name="t_next_service_date[]"]').val();
				t_repairs	 				= $(this).parents('.body-mechanical').find('[name="t_repairs[]"]').val();
				t_mechanical_service_total 	= $(this).parents('.body-mechanical').find('[name="t_mechanical_service_total[]"]').val();
				t_ms_id						= $(this).parents('.body-mechanical').find('[name="t_mechanical_services_id[]"]').val();
				docs 						= $(this).parents('.body-mechanical').find('.have-docs');
				$('.documents_mechanical_services').empty();

				$(docs).each(function(i,v)
				{
					name 	= $(this).find('.edit_ms_name_document').val();
					path 	= $(this).find('.edit_ms_path').val();
					idPath 	= $(this).find('.edit_ms_id_document').val();
					if (name != "" && path != "") 
					{
						@php
							$editMechanical = view('components.documents.upload-files',
							[
								"classExContainer" 		=> "image_success",
								"classExInput"			=> "pathActioner",
								"attributeExInput"		=> "type=\"file\" name=\"path\" accept=\".pdf,.jpg,.png\"",
								"classExDelete"			=> "delete-doc",
								"attributeExRealPath"	=> "type=\"hidden\" name=\"mechanical_services_path\"",
								"classExRealPath"		=> "path mechanical_services_path",
								"componentsExUp"		=>
								[
									[
										"kind"			=> "components.inputs.input-text",
										"classEx"		=> "id-doc",
										"attributeEx"	=> "type=\"hidden\" name=\"mechanicalID\""
									],
									[
										"kind" 	=> "components.labels.label", 
										"label" => "Seleccione el tipo de documento:"
									],
									[
										"kind" 			=> "components.inputs.select",
										"classEx" 		=> "nameDocument mechanical_services_document",
										"attributeEx"	=> "multiple name=\"mechanicalSelect\"" 
									]
								],
								"componentsAction" => 
								[[
									"kind" 			=> "components.buttons.button",
									"variant" 		=> "warning",
									"buttonElement"	=> "a",
									"attributeEx" 	=> "target=\"_blank\"",
									"classEx" 		=> "set-href",
									"label" 		=> "Ver documento"
								]],
							])->render();
						@endphp
						mechanicalDocs 	= '{!!preg_replace("/(\r)*(\n)*/", "", $editMechanical)!!}';
						docMechanical	= $(mechanicalDocs);

						docMechanical.find('[name="mechanical_services_path"]').val(path);
						docMechanical.find('[name="mechanicalID"]').val(idPath);
						docMechanical.find('[name="mechanicalSelect"]').append($('<option value="Comprobante de Pago" '+(name == "Comprobante de Pago" ? "selected='selected'" : "")+'>Comprobante de Pago</option>'));
						docMechanical.find('[name="mechanicalSelect"]').append($('<option value="Otro" '+(name == "Otro" ? "selected='selected'" : "")+'>Otro</option>'));
						docMechanical.find('.set-href').prop("href", '{{ url("docs/vehicles/") }}/'+path);
						docMechanical.find('.set-href').removeClass('set-href');

						$('.documents_mechanical_services').append(docMechanical);
					}
				});
				@php
					$selects = collect ([
						[
							"identificator"				=> ".nameDocument",
							"placeholder"				=> "Seleccione el tipo de documento",
							"maximumSelectionLength"	=> "1"
						]
					]);
				@endphp
				@component('components.scripts.selects',["selects" => $selects]) @endcomponent
				$('.date_last_service').val(t_date_last_service);
				$('.next_service_date').val(t_next_service_date);
				$('.mechanical_service_total').val(t_mechanical_service_total);
				$('.repairs').val(t_repairs);				
				$('.edit-mechanical-service').attr('disabled','disabled');
				$('.ms_id').val(t_ms_id);
				$(this).parents('.body-mechanical').remove();
				$('#body_mechanical_services .body-mechanical').each(function(i,v)
				{
					$(this).find('.count_ms').text(i+1);
				});
			})
			.on('click','.edit-fuel',function()
			{
				t_vehicle_fuel_id 	= $(this).parents('.body-fuel').find('[name="t_vehicle_fuel_id[]"]').val();
				t_fuel_type  		= $(this).parents('.body-fuel').find('[name="t_fuel_type[]"]').val();
				t_tag		 		= $(this).parents('.body-fuel').find('[name="t_tag[]"]').val();
				t_fuel_date	 		= $(this).parents('.body-fuel').find('[name="t_fuel_date[]"]').val();
				t_fuel_total 		= $(this).parents('.body-fuel').find('[name="t_fuel_total[]"]').val();				
				docs				= $(this).parents('.body-fuel').find('.have-docs');
				$('.documents_fuel').empty();

				$(docs).each(function(i,v)
				{
					name 	= $(this).find('.edit_fuel_name_document').val();
					path 	= $(this).find('.edit_fuel_path').val();
					idPath	= $(this).find('.edit_fuel_id_doc').val();

					if (name != "" && path != "") 
					{
						@php
							$editFuel = view('components.documents.upload-files',[
								"classExContainer"		=> "image_success",
								"classExInput"			=> "pathActioner",
								"attributeExInput"		=> "type=\"file\" name=\"path\" accept=\".pdf,.jpg,.png\"",
								"classExDelete"			=> "delete-doc",
								"attributeExRealPath"	=> "type=\"hidden\"",
								"classExRealPath"		=> "fuel_path path",
								"componentsExUp"		=> 
								[
									[
										"kind"			=> "components.inputs.input-text",
										"classEx"		=> "id-doc",
										"attributeEx"	=> "type=\"hidden\" name=\"fuelID\"" 
									],
									[
										"kind" 	=> "components.labels.label", 
										"label" => "Seleccione el tipo de documento:"
									],
									[
										"kind" 			=> "components.inputs.select",
										"classEx" 		=> "nameDocument fuel_document",
										"attributeEx"	=> "multiple name=\"fuelSelect\"" 
									]
								],
								"componentsAction" => 
								[[
									"kind" 			=> "components.buttons.button",
									"variant" 		=> "warning",
									"buttonElement"	=> "a",
									"attributeEx" 	=> "target=\"_blank\"",
									"classEx" 		=> "set-href",
									"label" 		=> "Ver documento"
								]],
							])->render();
						@endphp
						fuelDocs	= '{!!preg_replace("/(\r)*(\n)*/", "", $editFuel)!!}';
						docFuel		= $(fuelDocs);

						docFuel.find('.fuel_path').val(path);
						docFuel.find('[name="fuelID"]').val(idPath);
						docFuel.find('[name="fuelSelect"]').append($('<option value="Factura" '+(name == "Factura" ? "selected='selected'" : "")+'>Factura</option>'));
						docFuel.find('[name="fuelSelect"]').append($('<option value="Ticket" '+(name == "Ticket" ? "selected='selected'" : "")+'>Ticket</option>'));
						docFuel.find('[name="fuelSelect"]').append($('<option value="Otro" '+(name == "Otro" ? "selected='selected'" : "")+'>Otro</option>'));
						docFuel.find('.set-href').prop("href", '{{ url("docs/vehicles/") }}/'+path);
						docFuel.find('.set-href').removeClass('set-href');
						$('.documents_fuel').append(docFuel);
						@php
							$selects = collect ([
								[
									"identificator"				=> ".nameDocument",
									"placeholder"				=> "Seleccione el tipo de documento",
									"maximumSelectionLength"	=> "1"
								]
							]);
						@endphp
						@component('components.scripts.selects',["selects" => $selects]) @endcomponent
					}
				});
				$('.fuel_id').val(t_vehicle_fuel_id);
				$('.fuel_type').val(t_fuel_type);
				$('.tag').val(t_tag);
				$('.fuel_date').val(t_fuel_date);
				$('.fuel_total').val(t_fuel_total);
				$('.edit-fuel').attr('disabled','disabled');
				$(this).parents('.body-fuel').remove();
				$('#body_fuel .body-fuel').each(function(i,v)
				{
					$(this).find('.count_fuel').text(i+1);
				});
			})
			.on('click','.edit-tax',function()
			{				
				t_vehicle_taxes_id 			= $(this).parents('.body-taxes').find('[name="t_vehicle_taxes_id[]"]').val();
				t_date_verification 		= $(this).parents('.body-taxes').find('[name="t_date_verification[]"]').val();
				t_next_date_verification 	= $(this).parents('.body-taxes').find('[name="t_next_date_verification[]"]').val();
				t_total_verification		= $(this).parents('.body-taxes').find('[name="t_total_verification[]"]').val();
				t_monto_gestoria			= $(this).parents('.body-taxes').find('[name="t_monto_gestoria[]"]').val();
				docs 						= $(this).parents('.body-taxes').find('.have-docs');
				$('.documents_taxes').empty();

				$(docs).each(function(i,v)
				{
					name 	= $(this).find('.edit_taxes_name_document').val();
					path 	= $(this).find('.edit_taxes_path').val();
					date	= $(this).find('.edit_taxes_date').val();
					idPath	= $(this).find('.edit_taxes_id_doc').val();
					if (name != "" && path != "" && date != "") 
					{
						@php
							$editTaxes = view('components.documents.upload-files',
							[
								"classExContainer"		=> "image_success",
								"classExInput"			=> "pathActioner",
								"attributeExInput"		=> "type=\"file\" name=\"path\" accept=\".pdf,.jpg,.png\"",
								"classExDelete"			=> "delete-doc",
								"attributeExRealPath"	=> "type=\"hidden\"",
								"classExRealPath"		=> "path taxes_path",
								"componentsExUp"		=>	
								[
									[
										"kind"			=> "components.inputs.input-text",
										"classEx"		=> "id-doc",
										"attributeEx"	=> "type=\"hidden\" name=\"taxesID\"" 
									],
									[
										"kind" 	=> "components.labels.label", 
										"label" => "Seleccione el tipo de documento:"
									],
									[
										"kind" 			=> "components.inputs.select",
										"classEx" 		=> "nameDocument taxes_document",
										"attributeEx"	=> "multiple name=\"taxesSelect\"" 
									],
								],
								"componentsExDown" =>
								[
									[
										"kind" 	=> "components.labels.label", 
										"label" => "Fecha de Pago:"
									],
									[
										"kind"			=> "components.inputs.input-text",
										"classEx"		=> "datepicker taxes_date",
										"attributeEx"	=> "type=\"text\" name=\"taxesDate\" placeholder=\"Ingrese la fecha\"" 
									]
								],
								"componentsAction" => 
								[[
									"kind" 			=> "components.buttons.button",
									"variant" 		=> "warning",
									"buttonElement"	=> "a",
									"attributeEx" 	=> "target=\"_blank\"",
									"classEx" 		=> "set-href",
									"label" 		=> "Ver documento"
								]],
							])->render();
						@endphp
						taxesDocs	= '{!!preg_replace("/(\r)*(\n)*/", "", $editTaxes)!!}';
						docTaxes	= $(taxesDocs);

						docTaxes.find('.taxes_path').val(path);
						docTaxes.find('[name="taxesID"]').val(idPath);
						docTaxes.find('[name="taxesSelect"]').append($('<option value="Pago de Tenencia" '+(name == "Pago de Tenencia" ? "selected='selected'" : "")+'>Pago de Tenencia</option>'));
						docTaxes.find('[name="taxesSelect"]').append($('<option value="Pago de Verificación" '+(name == "Pago de Verificación" ? "selected='selected'" : "")+'>Pago de Verificación</option>'));
						docTaxes.find('[name="taxesDate"]').val(date);
						docTaxes.find('.set-href').prop("href", '{{ url("docs/vehicles/") }}/'+path);
						docTaxes.find('.set-href').removeClass('set-href');

						$('.documents_taxes').append(docTaxes);
						$('.datepicker').datepicker({ maxDate:0, dateFormat: "dd-mm-yy" });
						@php
							$selects = collect ([
								[
									"identificator"				=> ".nameDocument",
									"placeholder"				=> "Seleccione el tipo de documento",
									"maximumSelectionLength"	=> "1"
								]
							]);
						@endphp
						@component('components.scripts.selects',["selects" => $selects]) @endcomponent
					}
				});
				$('.taxes_id').val(t_vehicle_taxes_id);
				$('.date_verification').val(t_date_verification);
				$('.next_date_verification').val(t_next_date_verification);
				$('.total_verification').val(t_total_verification);
				$('.monto_gestoria').val(t_monto_gestoria);
				$('.edit-tax').attr('disabled','disabled');
				$(this).parents('.body-taxes').remove();
				$('#body_taxes .body-taxes').each(function(i,v)
				{
					$(this).find('.count_taxes').text(i+1);
				});
			})
		});

		function deleteDocs(actioner, section)
		{
			flagRemove 		= true;
			realPath 		= [];
			if(section == "div-only")
			{
				uploadedVal = actioner.parents('.docs-p').find('.path').val();
				realPath.push(uploadedVal);
			}
			else
			{
				actioner.parents('.tr').find('.path-delete').each(function()
				{
					realPath.push($(this).val());
				});
			}
			$.ajax(
			{
				type		: 'post',
				url			: '{{ route("vehicle.upload") }}',
				data		: {'realPath': realPath},
				error		: function(data)
				{
					flagRemove = false;
					swal('','Lo sentimos ocurrió un error, intentelo de nuevo.','error');
				}
			}).done(function (data)
			{
				if(flagRemove)
				{
					if(section == "div-only")
					{
						actioner.parents('.docs-p').remove();
					}
					else
					{
						actioner.parents('.tr').remove();
					}
				}
			});
		}
	</script>
@endsection
