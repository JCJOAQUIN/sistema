@component('components.labels.subtitle') DATOS @endcomponent
@component('components.containers.container-form')
	<div class="col-span-2">
		@component('components.labels.label') Título: @endcomponent
		@component('components.inputs.input-text')
			@slot('attributeEx')
				type="text" name="title" placeholder="Ingrese el título" data-validation="required" @if(isset($request->purchase->title)) value="{{ $request->purchase->title }}" @endif
			@endslot
			@slot('classEx')
				removeselect
			@endslot
		@endcomponent
	</div>
	<div class="col-span-2">
		@component('components.labels.label') Fiscal: @endcomponent
		<div class="col-span-2 flex row space-x-2">
			<div>
				@component('components.buttons.button-approval')
					@slot('attributeEx')
						checked name="fiscal" id="nofiscal" value="0" @if(isset($request) && $request->taxPayment == 0) checked="checked" @endif
					@endslot
					No
				@endcomponent
			</div>
			<div>
				@component('components.buttons.button-approval')
					@slot('attributeEx')
						name="fiscal" id="fiscal" value="1" @if(isset($request) && $request->taxPayment == 1) checked="checked" @endif
					@endslot
					Sí
				@endcomponent
			</div>
		</div>
	</div>
	<div class="col-span-2">
		@component('components.labels.label') Empresa: @endcomponent
		@php
			$optionEnterprise = [];
			foreach(App\Enterprise::where('status','ACTIVE')->orderBy('name','asc')->get() as $enterprise)
			{
				$optionEnterprise[] = [
					"value"			=> $enterprise->id,
					"description"	=> $enterprise->name,
					"selected"		=> (isset($request) && $request->idEnterprise == $enterprise->id ? "selected" : "")
				];
			}
		@endphp
		@component('components.inputs.select',["options" => $optionEnterprise])
			@slot('attributeEx')
				name="enterpriseid" data-validation="required" multiple="multiple"
			@endslot
			@slot('classEx')
				removeselect
			@endslot
		@endcomponent
	</div>
	<div class="col-span-2">
		@component('components.labels.label') Dirección: @endcomponent
		@php
			$optionDirection = [];
			foreach(App\Area::where('status','ACTIVE')->orderBy('name','asc')->get() as $area)
			{
				$optionDirection[] = [
					"value"			=> $area->id,
					"description"	=> $area->name,
					"selected"		=> (isset($request) && $request->idArea == $area->id ? "selected" : "")
				];
			}
		@endphp
		@component('components.inputs.select',["options" => $optionDirection])
			@slot('attributeEx')
				name="areaid" data-validation="required" multiple="multiple"
			@endslot
			@slot('classEx')
				removeselect
			@endslot
		@endcomponent
	</div>
	<div class="col-span-2">
		@component('components.labels.label') Proyecto: @endcomponent
		@php
			$optionProject = [];
			if(isset($request))
			{
				$project = App\Project::find($request->idProject);
				$optionProject[] = [
					"value" 		=> $project->idproyect, 
					"description"	=> $project->proyectName,
					"selected"		=> "selected"
				];
			}
		@endphp
		@component('components.inputs.select',["options" => $optionProject])
			@slot('attributeEx')
				name="projectid" data-validation="required" multiple="multiple"
			@endslot
			@slot('classEx')
				removeselect
			@endslot
		@endcomponent
	</div>
	<div class="col-span-2">
		@component('components.labels.label') Número de Orden (Opcional): @endcomponent
		@component('components.inputs.input-text')
			@slot('attributeEx')
				type="text" name="numberOrder" placeholder="Ingrese el número de orden" @if(isset($request->purchase->numberOrder)) value="{{ $request->purchase->numberOrder }}" @endif
			@endslot
			@slot('classEx')
				removeselect
			@endslot
		@endcomponent
	</div>
	<div class="col-span-2">
		@component('components.labels.label') Solicitante: @endcomponent
		@php
			$optionUser = [];
			if(isset($request) && $request->idRequest != "")
			{
				$optionUser[] = ["value" => $request->idRequest, "description" => $request->requestUser->fullName(), "selected" => "selected"];
			}
		@endphp
		@component('components.inputs.select',["options" => $optionUser])
			@slot('attributeEx')
				name="userid" data-validation="required" multiple="multiple"
			@endslot
			@slot('classEx')
				removeselect
			@endslot
		@endcomponent
	</div>
	<div class="col-span-2">
		@component('components.labels.label') Clasificación de Gasto: @endcomponent
		@php
			$optionAccount = [];
			if(isset($request))
			{
				$account = App\Account::where('selectable',1)
					->where('idEnterprise',$request->idEnterprise)
					->where('idAccAcc',$request->idAccAcc)
					->first();

				$optionAccount[] = [
					"value"			=> $account->idAccAcc,
					"description"	=> $account->account.' - '.$account->description.' '."(".$account->content.")",
					"selected"		=> $account->idAccAcc == $request->idAccAcc ? "selected" : ""
				];
			}
		@endphp
		@component('components.inputs.select',["options" => $optionAccount])
			@slot('attributeEx')
				name="accountid" data-validation="required" multiple="multiple"
			@endslot
			@slot('classEx')
				removeselect
			@endslot
		@endcomponent
	</div>
	<div class="col-span-2">
		@component('components.labels.label') Departamento: @endcomponent
		@php
			$optionDepartment = [];
			foreach(App\Department::where('status','ACTIVE')->orderBy('name','asc')->get() as $department)
			{
				$optionDepartment[] = [
					"value"			=> $department->id,
					"description"	=> $department->name,
					"selected"		=> (isset($request) && $request->idDepartment == $department->id ? "selected" : "")
				];
			}
		@endphp
		@component('components.inputs.select',["options" => $optionDepartment])
			@slot('attributeEx')
				name="departmentid" data-validation="required" multiple="multiple"
			@endslot
			@slot('classEx')
				removeselect
			@endslot
		@endcomponent
	</div>
@endcomponent
@component('components.labels.title-divisor') SELECCIONAR PROVEEDOR <span class="help-btn" id="help-btn-select-provider"> @endcomponent
<div class="flex row justify-center my-4 space-x-2">
	<div>
		@component('components.buttons.button-approval')
			@slot('attributeEx')
				type="radio" name="prov" id="new-prov" value="nuevo"
			@endslot
				Nuevo
		@endcomponent
	</div>
	<div>
		@component('components.buttons.button-approval')
			@slot('attributeEx')
				name="prov" id="buscar-prov" value="buscar" @if(isset($request)) checked @endif
			@endslot
				Buscar
		@endcomponent
	</div>
</div>
<div id="buscar" class="@if(isset($request)) block @else hidden @endif">
	@component("components.inputs.input-search", 
		[
			"attributeExInput" 	=> "type=\"text\" title=\"Escriba aquí\" name=\"search\" id=\"input-search\" placeholder=\"Ingrese un nombre\"",
		])
		Nombre:
	@endcomponent
	<div class="provider"></div>
	<div class="my-4 bg-red-300 text-red-900 text-center font-bold bg-opacity-25" id="not-found"></div>
</div>
<div id="form-prov" class="request-validate @if(isset($request)) block @else hidden @endif">
	@component('components.labels.title-divisor') - DATOS DEL PROVEEDOR - @endcomponent
	<div class="checks text-center">
		@component('components.inputs.switch')
			@slot('attributeEx')
				name="edit" type="checkbox" value="1" id="edit"
			@endslot
			Habilitar edición
		@endcomponent
	</div>
	@component('components.containers.container-form')
		<div class="col-span-2">
			<div class="mb-4">
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="hidden" name="idProvider" @if(isset($request) && isset($request->purchase->provider)) value="{{ $request->purchase->provider->idProvider }}" @endif
					@endslot
				@endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="hidden" name="provider_data_id" @if(isset($request) && isset($request->purchase->provider)) value="{{ $request->purchase->provider->provider_data_id }}" @endif
					@endslot
				@endcomponent
				@component('components.labels.label') Razón Social: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" name="reason" placeholder="Ingrese la razón social" data-validation="length server" data-validation-length="max150" data-validation-url="{{ url('configuration/provider/validate') }}" @if(isset($request) && isset($request->purchase->provider)) disabled="disabled" data-validation-req-params="{{ json_encode(array('oldReason'=>$request->purchase->provider->businessName)) }}" value="{{ $request->purchase->provider->businessName }}" @endif
					@endslot
				@endcomponent
			</div>
			<div class="mb-4">
				@component('components.labels.label') Calle: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" name="address" placeholder="Ingrese la calle" data-validation="required length" data-validation-length="max100" @if(isset($request) && isset($request->purchase->provider)) disabled="disabled" value="{{ $request->purchase->provider->address }}" @endif
					@endslot
					@slot('classEx')
						remove
					@endslot
				@endcomponent
			</div>
			<div class="mb-4">
				@component('components.labels.label') Número: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" name="number" placeholder="Ingrese el número" data-validation="required length" data-validation-length="max45" @if(isset($request) && isset($request->purchase->provider)) disabled="disabled" value="{{ $request->purchase->provider->number }}" @endif
					@endslot
					@slot('classEx')
						remove
					@endslot
				@endcomponent
			</div>
			<div class="mb-4">
				@component('components.labels.label') Colonia: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" name="colony" placeholder="Ingrese la colonia" data-validation="required length" data-validation-length="max70" @if(isset($request) && isset($request->purchase->provider))disabled="disabled" value="{{ $request->purchase->provider->colony }}" @endif
					@endslot
					@slot('classEx')
						remove
					@endslot
				@endcomponent
			</div>
			<div class="mb-4">
				@component('components.labels.label') Código Postal: @endcomponent
				@php
					$optionCP = [];
					if(isset($request) && isset($request->purchase->provider))
					{
						$optionCP[] = [
							"value"			=> $request->purchase->provider->postalCode,
							"description"	=> $request->purchase->provider->postalCode,
							"selected"		=> "selected"
						];
					}
				@endphp
				@component('components.inputs.select',["options" => $optionCP])
					@slot('attributeEx')
						name="cp" id="cp" data-validation="required" multiple="multiple" placeholder="Ingrese el código postal" @if(isset($request) && isset($request->purchase->provider)) disabled @endif
					@endslot
					@slot('classEx')
						remove cp
					@endslot
				@endcomponent
			</div>
			<div class="mb-4">
				@component('components.labels.label') Ciudad: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" name="city" placeholder="Ingrese la ciudad" data-validation="required length" data-validation-length="max70" @if(isset($request) && isset($request->purchase->provider))disabled="disabled" value="{{ $request->purchase->provider->city }}" @endif
					@endslot
					@slot('classEx')
						remove
					@endslot
				@endcomponent
			</div>
		</div>
		<div class="col-span-2">
			<div class="mb-4">
				@component('components.labels.label') Estado: @endcomponent
				@php
					$optionState = [];
					foreach(App\State::orderBy('description','asc')->get() as $state)
					{
						$optionState[] = [
							"value"			=> $state->idstate,
							"description" 	=> $state->description,
							"selected"		=> (isset($request) && isset($request->purchase->provider) && $request->purchase->provider->state_idstate == $state->idstate ? "selected" : "")
						];
					}
				@endphp
				@component('components.inputs.select',['options' => $optionState])
					@slot('attributeEx')
						name="state" multiple="multiple" data-validation="required" @if(isset($request)) disabled="disabled" @endif
					@endslot
					@slot('classEx')
						js-state remove
					@endslot
				@endcomponent
			</div>
			<div class="mb-4">
				@component('components.labels.label') RFC: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" name="rfc" placeholder="Ingrese el RFC" data-validation="server" data-validation-url="{{ url('configuration/provider/validate') }}" @if(isset($request) && isset($request->purchase->provider)) data-validation-req-params="{{ json_encode(array('oldRfc'=>$request->purchase->provider->idProvider)) }}" disabled="disabled" value="{{ $request->purchase->provider->rfc }}" @endif
					@endslot
				@endcomponent
			</div>
			<div class="mb-4">
				@component('components.labels.label') Teléfono: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" name="phone" placeholder="Ingrese el teléfono" data-validation="phone required" @if(isset($request) && isset($request->purchase->provider)) disabled="disabled" value="{{ $request->purchase->provider->phone }}" @endif
					@endslot
					@slot('classEx')
						phone remove
					@endslot
				@endcomponent
			</div>
			<div class="mb-4">
				@component('components.labels.label') Contacto: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" name="contact" placeholder="Ingrese el contacto" data-validation="required" @if(isset($request) && isset($request->purchase->provider)) disabled="disabled" value="{{ $request->purchase->provider->contact }}" @endif
					@endslot
					@slot('classEx')
						remove
					@endslot
				@endcomponent
			</div>
			<div class="mb-4">
				@component('components.labels.label') Beneficiario: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" name="beneficiary" placeholder="Ingrese el beneficiario" data-validation="required" @if(isset($request) && isset($request->purchase->provider)) disabled="disabled" value="{{ $request->purchase->provider->beneficiary }}" @endif
					@endslot
					@slot('classEx')
						remove
					@endslot
				@endcomponent
			</div>
			<div class="mb-4">
				@component('components.labels.label') Otro (opcional): @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" name="other" placeholder="Ingrese otro dato" @if(isset($request) && isset($request->purchase->provider)) disabled="disabled" value="{{ $request->purchase->provider->commentaries }}" @endif
					@endslot
				@endcomponent
			</div>
		</div>
	@endcomponent
	@component('components.labels.title-divisor') CUENTAS BANCARIAS @endcomponent
	<div class="my-4 @if(isset($request)) hidden @else block @endif" id="banks">
		@component('components.labels.not-found',["variant" => "note"])
			Para agregar una cuenta nueva es necesario colocar los siguientes campos:
		@endcomponent
		@component('components.containers.container-form')
			@slot('attributeEx')
				id="contentBank"
			@endslot
			<div class="col-span-2">
				@component('components.labels.label') Banco: @endcomponent
				@php
					$optionBank = [];
					foreach(App\Banks::orderBy('description','asc')->get() as $bank)
					{
						$optionBank[] = ["value" => $bank->idBanks, "description" => $bank->description];
					}
				@endphp
				@component('components.inputs.select',["options" => $optionBank])
					@slot('attributeEx')
						multiple="multiple"
					@endslot
					@slot('classEx')
						js-bank
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Alias: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" placeholder="Ingrese el alias"
					@endslot
					@slot('classEx')
						alias
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Cuenta bancaria: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" placeholder="Ingrese la cuenta bancaria" data-validation="cuenta"
					@endslot
					@slot('classEx')
						account
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Sucursal: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" placeholder="Ingrese la sucursal"
					@endslot
					@slot('classEx')
						branch_office
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Referencia: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" placeholder="Ingrese la referencia"
					@endslot
					@slot('classEx')
						reference
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') CLABE: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" placeholder="Ingrese la CLABE" data-validation="clabe"
					@endslot
					@slot('classEx')
						clabe
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Moneda: @endcomponent
				@php
					$optionCurrency = [];
					$valCurrency	= ["MXN","USD","EUR","Otro"];

					foreach ($valCurrency as $v)
					{
						$optionCurrency[] = ["value" => $v, "description" => $v];
					}
				@endphp
				@component('components.inputs.select',["options" => $optionCurrency])
					@slot('attributeEx')
						multiple="multiple"
					@endslot
					@slot('classEx')
						currency
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Convenio (opcional): @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" placeholder="Ingrese el convenio"
					@endslot
					@slot('classEx')
						agreement
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2 md:col-span-4 grid justify-items-center md:justify-items-start">
				@component('components.buttons.button',['variant' => "warning"])
					@slot('attributeEx')
						type="button" id="addAccountPurchase"
					@endslot
					<span class="icon-plus"></span>
					<span>Agregar</span>
				@endcomponent
			</div>
		@endcomponent
	</div>
	@php
		$body		= [];
		$modelBody	= [];
		$modelHead	= 
		[
			[
				["value" => "Acción"],
				["value" => "Banco"],
				["value" => "Alias"],
				["value" => "Cuenta"],
				["value" => "Sucursal"],
				["value" => "Referencia"],
				["value" => "CLABE"],
				["value" => "Moneda"],
				["value" => "Convenio"]
			]
		];
		if(isset($request) && isset($request->purchase->provider))
		{
			foreach($request->purchase->provider->providerData->providerBank as $bank)
			{
				$class		= "";
				$checked	= "";
				$valueCheck	= "";
				if($request->purchase->provider_has_banks_id == $bank->id)
				{
					$class		= "marktr";
					$checked	= "checked";
					$valueCheck	= "1";
				}
				else
				{
					$valueCheck	= "0";
				}
				$description = "";
				if($bank->agreement == '')
				{
					$description = "---";
				}
				else
				{
					$description = $bank->agreement;
				}
				$body = [ "classEx" => "tr-banks ".$class,
					[
						"content" =>
						[
							[
								"kind"				=> "components.inputs.checkbox",
								"attributeEx"		=> "id=\"id$bank->id\" type=\"radio\" name=\"provider_has_banks_id\" value=\"".$bank->id."\""." ".$checked,
								"classEx"			=> "checkbox id_provider_banks",
								"classExLabel"		=> "request-validate",
								"label"				=> "<span class=\"icon-check\"></span>",
								"classExContainer"	=> "my-2",
								"radio"				=> true
							],
							[
								"kind"			=> "components.buttons.button",
								"variant"		=> "red",
								"classEx"		=> "delete-item hidden",
								"attributeEx"	=> "type=\"button\"",
								"label"			=> "<span class=\"icon-x delete-span\"></span>"
							]
						]
					],
					[
						"content"	=>
						[
							[
								"label" => $bank->bank->description
							],
							[
								"kind"			=> "components.inputs.input-text",
								"attributeEx"	=> "type=\"hidden\" name=\"providerBank[]\" value=\"".$bank->id."\"",
								"classEx"		=> "providerBank"
							],
							[
								"kind"			=> "components.inputs.input-text",
								"attributeEx"	=> "type=\"hidden\" name=\"bank[]\" value=\"".$bank->banks_idBanks."\""
							]
						]
					],
					[
						"content" =>
						[
							[
								"label" => $bank->alias != "" ? $bank->alias : '---'
							],
							[
								"kind"			=> "components.inputs.input-text",
								"attributeEx"	=> "type=\"hidden\" name=\"alias[]\" value=\"".$bank->alias."\""
							]
						]
					],
					[
						"content" =>
						[
							[
								"label" => $bank->account != "" ? $bank->account : '---'
							],
							[
								"kind"			=> "components.inputs.input-text",
								"attributeEx"	=> "type=\"hidden\" name=\"account[]\" value=\"".$bank->account."\""
							]
						]
					],
					[
						"content" =>
						[
							[
								"label" => $bank->branch != "" ? $bank->branch : '---'
							],
							[
								"kind"			=> "components.inputs.input-text",
								"attributeEx"	=> "type=\"hidden\" name=\"branch_office[]\" value=\"".$bank->branch."\""
							]
						]
					],
					[
						"content" =>
						[
							[
								"label" => $bank->reference != "" ? $bank->reference : '---'
							],
							[
								"kind"			=> "components.inputs.input-text",
								"attributeEx"	=> "type=\"hidden\" name=\"reference[]\" value=\"".$bank->reference."\""
							]
						]
					],
					[
						"content" =>
						[
							[
								"label" => $bank->clabe != "" ? $bank->clabe : '---'
							],
							[
								"kind"			=> "components.inputs.input-text",
								"attributeEx"	=> "type=\"hidden\" name=\"clabe[]\" value=\"".$bank->clabe."\""
							]
						]
					],
					[
						"content" =>
						[
							[
								"label" => $bank->currency != "" ? $bank->currency : '---'
							],
							[
								"kind"			=> "components.inputs.input-text",
								"attributeEx"	=> "type=\"hidden\" name=\"currency[]\" value=\"".$bank->currency."\""
							]
						]
					],
					[
						"content" =>
						[
							[
								"label" => $description
							],
							[
								"kind"			=> "components.inputs.input-text",
								"attributeEx"	=> "type=\"hidden\" name=\"agreement[]\" value=\"".$bank->agreement."\""
							],
							[
								"kind"			=> "components.inputs.input-text",
								"attributeEx"	=> "type=\"hidden\" name=\"checked[]\" value=\"".$valueCheck."\"",
								"classEx"		=> "idchecked"
							]
						]
					]
				];
				$modelBody[] = $body;
			}
		}
	@endphp
	@component('components.tables.table',[
		"modelBody"			=> $modelBody,
		"modelHead"			=> $modelHead,
		"attributeExBody"	=> "id=\"banks-body\""
	])	
	@endcomponent
	<div id="delete-accounts"></div>
</div>
@component('components.labels.title-divisor') DATOS DEL PEDIDO  @endcomponent
@component('components.containers.container-form')
	<div class="col-span-2">
		@component('components.labels.label') Cantidad: @endcomponent
		@component('components.inputs.input-text')
			@slot('attributeEx')
				type="text" name="quantity" placeholder="Ingrese la cantidad"
			@endslot
			@slot('classEx')
				quanty
			@endslot
		@endcomponent
	</div>
	<div class="col-span-2">
		@component('components.labels.label') Unidad: @endcomponent
		@php
			$optionUnit = [];
			foreach(App\CatMeasurementTypes::whereNotNull('type')->get() as $m_types)
			{
				foreach ($m_types->childrens()->orderBy('child_order','asc')->get() as $child)
				{
					$optionUnit[] = ["value" => $child->description, "description" => $child->description ];
				}
			}
		@endphp
		@component('components.inputs.select',["options" => $optionUnit])
			@slot('attributeEx')
				name="unit" multiple="multiple"
			@endslot
			@slot('classEx')
				unit
			@endslot
		@endcomponent
	</div>
	<div class="col-span-2">
		@component('components.labels.label') Descripción: @endcomponent
		@component('components.inputs.input-text')
			@slot('attributeEx')
				type="text" name="description" placeholder="Ingrese la descripción"
			@endslot
		@endcomponent
	</div>
	<div class="col-span-2">
		@component('components.labels.label') Precio Unitario: @endcomponent
		@component('components.inputs.input-text')
			@slot('attributeEx')
				type="text" name="price" placeholder="Ingrese el precio unitario"
			@endslot
			@slot('classEx')
				price
			@endslot
		@endcomponent
	</div>
	<div class="col-span-2 content-iva @if(isset($request) && $request->taxPayment == 0) hidden @else block @endif">
		@component('components.labels.label') Tipo de IVA: @endcomponent
		<div class="flex row mb-4 space-x-2">
			<div>
				@component('components.buttons.button-approval')
					@slot('attributeEx')
						type="radio" name="iva_kind" id="iva_no" value="no" checked="" @if(isset($request) && $request->taxPayment == 0) disabled @endif
					@endslot
					@slot('classEx')
						iva_kind
					@endslot
					@slot('attributeExLabel')
						title="No IVA"
					@endslot
					No
				@endcomponent
			</div>
			<div>
				@component('components.buttons.button-approval')
					@slot('attributeEx')
						type="radio" name="iva_kind" id="iva_a" value="a" @if(isset($request) && $request->taxPayment == 0) disabled @endif
					@endslot
					@slot('classEx')
						iva_kind
					@endslot
					@slot('attributeExLabel')
						title="{{App\Parameter::where('parameter_name','IVA')->first()->parameter_value}}%"
					@endslot
					A
				@endcomponent
			</div>
			<div>
				@component('components.buttons.button-approval')
					@slot('attributeEx')
						type="radio" name="iva_kind" id="iva_b" value="b" @if(isset($request) && $request->taxPayment == 0) disabled @endif
					@endslot
					@slot('classEx')
						iva_kind
					@endslot
					@slot('attributeExLabel')
						title="{{App\Parameter::where('parameter_name','IVA2')->first()->parameter_value}}%"
					@endslot
					B
				@endcomponent
			</div>
		</div>
	</div>
	<div class="md:col-span-4 col-span-2">
		@component('components.templates.inputs.taxes',[ "type"	=> "taxes", "name"	=> "additional_exist"])@endcomponent
	</div>
	<div class="md:col-span-4 col-span-2">
		@component('components.templates.inputs.taxes',[ "type" => "retention", "name" => "retention_new"])@endcomponent
	</div>
	<div class="col-span-2">
		@component('components.labels.label') Importe: @endcomponent
		@component('components.inputs.input-text')
			@slot('attributeEx')
				readonly type="text" name="amount" placeholder="$0.00"
			@endslot
			@slot('classEx')
				amount
			@endslot
		@endcomponent
	</div>
	<div class="col-span-2 md:col-span-4 grid justify-items-center md:justify-items-start">
		@component('components.inputs.input-text')
			@slot('attributeEx')
				type="hidden" value="x"
			@endslot
			@slot('classEx')
				purchase_id
			@endslot
		@endcomponent
		@component('components.buttons.button',["variant" => "warning"])
			@slot('attributeEx')
				type="button" name="addConceptPurchase" id="addConceptPurchase"
			@endslot
			<span class="icon-plus"></span>
			<span>Agregar concepto</span>
		@endcomponent
	</div>
@endcomponent
@php
	$body		= [];
	$modelBody	= [];
	$modelHead	= 
	[
		[
			["value" => "#"],
			["value" => "Cantidad"],
			["value" => "Unidad"],
			["value" => "Descripción"],
			["value" => "Precio Unitario"],
			["value" => "IVA"],
			["value" => "Impuesto adicional"],
			["value" => "Retenciones"],
			["value" => "Importe"],
			["value" => "Acciones"]
		]
	];
	if(isset($request))
	{
		foreach($request->purchase->detailPurchase as $key=>$detail)
		{
			$body = [ "classEx"	=> "tr-purchase",
				[
					"classEx"	=> "countConcept",
					"show"		=> "true",
					"content"	=>
					[
						"label" => $key+1
					]
				],
				[
					"content"	=>
					[
						[
							"label" => $detail->quantity
						],
						[
							"kind"			=> "components.inputs.input-text",
							"attributeEx"	=> "readonly=\"true\" type=\"hidden\" name=\"tquanty[]\" value=\"".$detail->quantity."\"",
							"classEx"		=> "tquanty"
						],
						[
							"kind"			=> "components.inputs.input-text",
							"attributeEx"	=> "type=\"hidden\" name=\"idDetail[]\" value=\"".$detail->id."\""
						]
					] 
				],
				[
					"content" =>
					[
						[
							"label" => $detail->unit
						],
						[
							"kind"			=> "components.inputs.input-text",
							"attributeEx"	=> "readonly=\"true\" type=\"hidden\" name=\"tunit[]\" value=\"".$detail->unit."\"",
							"classEx"		=> "tunit"
						]
					]
				],
				[
					"content" =>
					[
						[
							"label" => $detail->description
						],
						[
							"kind"			=> "components.inputs.input-text",
							"attributeEx"	=> "readonly=\"true\" type=\"hidden\" name=\"tdescr[]\" value=\"".$detail->description."\"",
							"classEx"		=> "tdescr"
						],
						[
							"kind"			=> "components.inputs.input-text",
							"attributeEx"	=> "readonly=\"true\" type=\"hidden\" name=\"tivakind[]\" value=\"".$detail->typeTax."\"",
							"classEx"		=> "tivakind"
						]
					]
				],
				[
					"content" =>
					[
						[
							"label" => '$ '.number_format($detail->unitPrice,2)
						],
						[
							"kind"			=> "components.inputs.input-text",
							"attributeEx"	=> "readonly=\"true\" type=\"hidden\" name=\"tprice[]\" value=\"".$detail->unitPrice."\"",
							"classEx"		=> "tprice"
						]
					]
				],
				[
					"content" =>
					[
						[
							"label" => '$ '.number_format($detail->tax,2)
						],
						[
							"kind"			=> "components.inputs.input-text",
							"attributeEx"	=> "readonly=\"true\" type=\"hidden\" name=\"tiva[]\" value=\"".$detail->tax."\"",
							"classEx"		=> "tiva"
						]
					]
				]
			];
			$additionalTaxes = "";
			foreach($detail->taxes as $tax)
			{
				$additionalTaxes	.= '<div class="contentTaxes">';
				$additionalTaxes	.= view('components.inputs.input-text',
				[
					"attributeEx"	=> "type=\"hidden\" name=\"tamountadditional".$taxesCount."[]\" value=\"$tax->amount\"",
					"classEx"		=> "num_amountAdditional"
				])->render();
				$additionalTaxes	.= view('components.inputs.input-text',
				[
					"attributeEx"	=> "type=\"hidden\" name=\"tnameamount".$taxesCount."[]\" value=\"$tax->name\"",
					"classEx"		=> "num_nameAmount"
				])->render();
				$additionalTaxes	.= view('components.inputs.input-text',
				[
					"attributeEx"	=> "type=\"hidden\" name=\"idTaxes[]\" value=\"$tax->id\"",
					"classEx"		=> "id_taxes"
				])->render();
				$additionalTaxes	.= '</div>';
			}
			$additionalTaxes	.= view('components.labels.label',
			[
				"label" => "$ ".number_format($detail->taxes->sum('amount'),2)
			])->render();
			$body[] = 
			[
				"content" 	=> [ "label" => $additionalTaxes ]
			];
			$retentionsContent = "";
			foreach($detail->retentions as $ret)
			{
				$retentionsContent	.= '<div class="contentRetention">';
				$retentionsContent	.= view('components.inputs.input-text',
				[
					"kind"			=> "components.inputs.input-text",
					"attributeEx"	=> "type=\"hidden\" name=\"tamountretention".$taxesCount."[]\" value=\"$ret->amount\"",
					"classEx"		=> "num_amountRetention"
				])->render();
				$retentionsContent	.= view('components.inputs.input-text',
				[
					"kind"			=> "components.inputs.input-text",
					"attributeEx"	=> "type=\"hidden\" name=\"tnameretention".$taxesCount."[]\" value=\"$ret->name\"",
					"classEx"		=> "num_nameRetention"
				])->render();
				$retentionsContent	.= '</div>';
			}
			$taxesCount++;
			$retentionsContent	.= view('components.labels.label',
			[
				"label" => "$ ".number_format($detail->retentions->sum('amount'),2)
			])->render();
			$body[] =
			[
				"content" 	=> [ "label" => $retentionsContent ]
			];
			$body[] =
			[
				"content" =>
				[
					[
						"label" => '$ '.number_format($detail->amount,2)
					],
					[
						"kind"			=> "components.inputs.input-text",
						"attributeEx"	=> "readonly=\"true\" type=\"hidden\" name=\"tamount[]\" value=\"".$detail->amount."\"",
						"classEx"		=> "tamount"
					]
				]
			];
			$body[] =
			[
				"content" =>
				[
					[
						"kind"			=> "components.buttons.button",
						"variant"		=> "success",
						"attributeEx"	=> "id=\"edit\" type=\"button\"",
						"classEx"		=> "edit-item",
						"label"			=> "<span class=\"icon-pencil\"></span>"
					],
					[
						"kind"			=> "components.buttons.button",
						"variant"		=> "red",
						"attributeEx"	=> "type=\"button\"",
						"classEx"		=> "delete-item-purchase",
						"label"			=> "<span class=\"icon-x\"></span>"
					]
				]
			];
			$modelBody[] = $body;
		}
	}
@endphp
@component('components.tables.table',
	[
		"modelBody" 		=> $modelBody,
		"modelHead" 		=> $modelHead,
		"attributeEx"		=> "id=\"table\"",
		"attributeExBody"	=> "id=\"body\"",
		"classExBody"		=> "request-validate"
	])
@endcomponent
@php
	$subtotalLabel	= "$ 0.00";
	$varSubtotal	= "";
	$taxesLabel		= "$ 0.00";
	$varTaxes		= "";
	$retentionLabel	= "$ 0.00";
	$varRetention	= "";
	$ivaLabel		= "$ 0.00";
	$varIva			= "";
	$totalLabel		= "$ 0.00";
	$varTotal		= "";
	if(isset($request))
	{
		$subtotalLabel	= "$ ".number_format($request->purchase->subtotal,2);
		$varSubtotal	= $request->purchase->subtotal;
		foreach($request->purchase->detailPurchase as $detail)
		{
			foreach($detail->taxes as $tax)
			{
				$taxes += $tax->amount;
			}
		}
		$taxesLabel	= "$ ".number_format($taxes,2);
		$varTaxes	= $taxes;
		foreach($request->purchase->detailPurchase as $detail)
		{
			foreach($detail->retentions as $ret)
			{
				$retentions += $ret->amount;
			}
		}
		$retentionLabel	= "$ ".number_format($retentions,2);
		$varRetention	= $retentions;
		$ivaLabel		= "$ ".number_format($request->purchase->tax,2);
		$varIva			= $request->purchase->tax;
		$totalLabel		= "$ ".number_format($request->purchase->amount,2);
		$varTotal		= $request->purchase->amount;		
	}
	$modelTable =
	[
		[
			"label"		=> "Subtotal:",
			"inputsEx"	=>
			[
				[
					"kind" 		=> "components.labels.label",
					"label"		=> $subtotalLabel,
					"classEx"	=> "my-2 removeselect subtotalLabel"
				],
				[
					"kind"			=> "components.inputs.input-text",
					"classEx" 		=> "removeselect",	
					"attributeEx" 	=> "type=\"hidden\" readonly name=\"subtotal\" value=\"".$varSubtotal."\""
				]
			]
		],
		[
			"label"		=> "Impuesto Adicional:",
			"inputsEx"	=>
			[
				[
					"kind" 		=> "components.labels.label",
					"label"		=> $taxesLabel,
					"classEx"	=> "my-2 removeselect taxesLabel"
				],
				[
					"kind"			=> "components.inputs.input-text",
					"classEx" 		=> "removeselect",	
					"attributeEx" 	=> "type=\"hidden\" readonly name=\"amountAA\" value=\"".$varTaxes."\""
				]
			]
		],
		[
			"label"		=> "Retenciones:",
			"inputsEx"	=>
			[
				[
					"kind" 		=> "components.labels.label",
					"label"		=> $retentionLabel,
					"classEx"	=> "my-2 removeselect retentionLabel"
				],
				[
					"kind"			=> "components.inputs.input-text",
					"classEx" 		=> "removeselect",	
					"attributeEx" 	=> "type=\"hidden\" readonly name=\"amountR\" value=\"".$varRetention."\""
				]
			]
		],
		[
			"label"		=> "IVA:",
			"inputsEx"	=>
			[
				[
					"kind" 		=> "components.labels.label",
					"label"		=> $ivaLabel,
					"classEx"	=> "my-2 removeselect ivaLabel"
				],
				[
					"kind"			=> "components.inputs.input-text",
					"classEx" 		=> "removeselect",	
					"attributeEx" 	=> "type=\"hidden\" readonly name=\"totaliva\" value=\"".$varIva."\""
				]
			]
		],
		[
			"label"		=> "TOTAL:",
			"inputsEx"	=>
			[
				[
					"kind" 		=> "components.labels.label",
					"label"		=> $totalLabel,
					"classEx"	=> "my-2 removeselect totalLabel"
				],
				[
					"kind"			=> "components.inputs.input-text",
					"classEx" 		=> "removeselect",	
					"attributeEx" 	=> "type=\"hidden\" readonly name=\"total\" value=\"".$varTotal."\""
				]
			]
		]
	];
@endphp
@component('components.templates.outputs.form-details', ["modelTable" => $modelTable])
	@slot('textNotes')
		@if(isset($request)) {{ $request->purchase->notes }} @endif
	@endslot
	@slot('attributeExComment')
		name="note"
	@endslot
@endcomponent
@component('components.labels.title-divisor') CONDICIONES DE PAGO  @endcomponent
@component('components.containers.container-form')
	<div class="col-span-2">
		@component('components.labels.label') Referencia/Número de factura: @endcomponent
		@component('components.inputs.input-text')
			@slot('attributeEx')
				type="text" name="referencePuchase" data-validation="required" placeholder="Ingrese la referencia" @if(isset($request)) value="{{ $request->purchase->reference }}" @endif
			@endslot
			@slot('classEx')
				remove
			@endslot
		@endcomponent
	</div>
	<div class="col-span-2">
		@component('components.labels.label') Tipo de moneda: @endcomponent
		@php
			$optionC = [];
			$valCurrency = ["MXN","USD","EUR","Otro"];
			foreach ($valCurrency as $c)
			{
				$optionC[] =
				[
					"value"			=> $c,
					"description"	=> $c,
					"selected"		=> (isset($request) && $request->purchase->typeCurrency == $c ? "selected" : "")
				];
			}
		@endphp
		@component('components.inputs.select',["options" => $optionC])
			@slot('attributeEx')
				name="type_currency" multiple="multiple" data-validation="required"
			@endslot
			@slot('classEx')
				remove
			@endslot
		@endcomponent
	</div>
	<div class="col-span-2">
		@component('components.labels.label') Fecha de Pago: @endcomponent
		@component('components.inputs.input-text')
			@slot('attributeEx')
				type="text" name="payment_date" step="1" placeholder="Seleccione la fecha de pago" readonly="readonly" id="datepicker" data-validation="required"
				@if(isset($request)) value="{{ Carbon\Carbon::createFromFormat('Y-m-d',$request->purchase->payment_date)->format('d-m-Y') }}" @endif
			@endslot
			@slot('classEx')
				remove
			@endslot
		@endcomponent
	</div>
	<div class="col-span-2">
		@component('components.labels.label') Forma de pago: @endcomponent
		@php
			$optionPay	= [];
			$valPay		= ["Cheque","Efectivo","Transferencia"];
			foreach ($valPay as $p)
			{
				$optionPay[] =
				[
					"value"			=> $p,
					"description"	=> $p,
					"selected"		=> (isset($request) && $request->purchase->paymentMode == $p ? "selected" : "")
				];
			}
		@endphp
		@component('components.inputs.select',["options" => $optionPay])
			@slot('attributeEx')
				name="pay_mode" multiple="multiple" data-validation="required"
			@endslot
			@slot('classEx')
				js-form-pay removeselect
			@endslot
		@endcomponent
	</div>
	<div class="col-span-2">
		@component('components.labels.label') Estado  de factura: @endcomponent
		@php
			$optionBill	= [];
			if (isset($request)) 
			{
				$selected = $request->purchase->billStatus;
			}
			else
			{
				$selected = "No Aplica";
			}
			$valBill	= ["Pendiente","Entregado","No Aplica"];
			foreach ($valBill as $b)
			{
				$optionBill[] =
				[
					"value"			=> $b,
					"description"	=> $b,
					"selected"		=> ($selected == $b ? "selected" : "")
				];
			}
		@endphp
		@component('components.inputs.select',["options" => $optionBill])
			@slot('attributeEx')
				name="status_bill" multiple="multiple" data-validation="required"
			@endslot
			@slot('classEx')
				js-ef removeselect
			@endslot
		@endcomponent
	</div>
@endcomponent
@php
	$amountLabel	= "$ 0.00";
	$varAmount		= "";
	if(isset($request))
	{
		$amountLabel	= "$ ".number_format($request->purchase->amount,2);
		$varAmount		= $request->purchase->amount;
	} 
	$modelTable =
	[
		[
			"label"		=> "Importe a pagar:",
			"inputsEx"	=>
			[
				[
					"kind" 		=> "components.labels.label",
					"label"		=> $amountLabel,
					"classEx"	=> "my-2 removeselect amountLabel"
				],
				[
					"kind"			=> "components.inputs.input-text",
					"classEx" 		=> "removeselect",	
					"attributeEx" 	=> "type=\"hidden\" readonly name=\"amount_total\" data-validation=\"required\" value=\"".$varAmount."\"",
					"classEx"		=> "amount_total remove"
				]
			]
		]
	];
@endphp
@component('components.templates.outputs.form-details',["modelTable" => $modelTable]) @endcomponent
