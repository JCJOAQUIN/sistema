@component('components.labels.title-divisor')    DATOS DEL PROVEEDOR @endcomponent
@component('components.containers.container-form')
	<div class="col-span-2">
		@component('components.labels.label') Razón Social: @endcomponent
		@component('components.inputs.input-text')
			@slot('attributeEx')
				name="businessName_edit" value="{{ $provider->businessName }}" data-old-reason="{{ $provider->businessName }}" placeholder="Ingrese la razón social" data-validation="required length" data-validation-length="max150"
			@endslot
			@slot('classEx')
				remove businessName
			@endslot
		@endcomponent
	</div>
	<div class="col-span-2">
		@component('components.labels.label') Calle: @endcomponent
		@component('components.inputs.input-text')
			@slot('attributeEx')
				name="address_edit" value="{{ $provider->address }}" placeholder="Ingrese una calle" data-validation="required length" data-validation-length="max100"
			@endslot
			@slot('classEx')
				remove
			@endslot
		@endcomponent
	</div>
	<div class="col-span-2">
		@component('components.labels.label') Número: @endcomponent
		@component('components.inputs.input-text')
			@slot('attributeEx')
				name="number_edit" value="{{ $provider->number }}" placeholder="Ingrese un número" data-validation="required length" data-validation-length="max45"
			@endslot
			@slot('classEx')
				remove
			@endslot
		@endcomponent
	</div>
	<div class="col-span-2">
		@component('components.labels.label') Colonia: @endcomponent
		@component('components.inputs.input-text')
			@slot('attributeEx')
				name="colony_edit" value="{{ $provider->colony }}" placeholder="Ingrese una colonia" data-validation="required length" data-validation-length="max70"
			@endslot
			@slot('classEx')
				remove
			@endslot
		@endcomponent
	</div>
	<div class="col-span-2">
		@component('components.labels.label') Código Postal: @endcomponent
		@php
			$options = collect();
			if(isset($provider->postalCode))
			{
				$options = $options->concat(
				[
					[
						"value"			=> $provider->postalCode, 
						"description"	=> $provider->postalCode,
						"selected"		=> ((isset($provider->postalCode) && $provider->postalCode != "") ? "selected" : "")
					]
				]);
			}
		@endphp
		@component("components.inputs.select", ["options" => $options])
			@slot('attributeEx')
				name="postalCode_edit" id="cp" placeholder="Ingrese un código postal" data-validation="required"
			@endslot
			@slot('classEx')
				remove cp
			@endslot
		@endcomponent
	</div>
	<div class="col-span-2">
		@component('components.labels.label') Ciudad: @endcomponent
		@component('components.inputs.input-text')
			@slot('attributeEx')
				name="city_edit" value="{{ $provider->city }}" placeholder="Ingrese una ciudad" data-validation="required length" data-validation-length="max70"
			@endslot
			@slot('classEx')
				remove
			@endslot
		@endcomponent
	</div>
	<div class="col-span-2">
		@component('components.labels.label') Estado: @endcomponent
		@php
			$options = collect();
			foreach(App\State::orderName()->get() as $state)
			{
				if($provider->state_idstate == $state->idstate)
				{
					$options = $options->concat([['value'=>$state->idstate, 'selected' => 'selected', 'description'=>$state->description]]);
				}
				else
				{
					$options = $options->concat([['value'=>$state->idstate, 'description'=>$state->description]]);
				}
			}
			$attributeEx = "name=\"state_idstate_edit\" multiple=\"multiple\" data-validation=\"required\"";
			$classEx = "js-state";
		@endphp
		@component('components.inputs.select', ['options' => $options, 'attributeEx' => $attributeEx, 'classEx' => $classEx])
		@endcomponent
	</div>
	<div class="col-span-2">
		@component('components.labels.label') RFC: @endcomponent
		@component('components.inputs.input-text')
			@slot('attributeEx')
				type="text" name="rfc" placeholder="Ingrese el RFC" data-validation="server" value="{{ $provider->rfc }}" data-validation-url="{{url('configuration/provider/validate')}}" @isset($provider->rfc) data-validation-req-params="{{ json_encode(array('oldRfc'=>$provider->rfc)) }}" @endisset data-old-rfc="{{ $provider->rfc }}"
			@endslot
			@slot('classEx')
				remove rfc_edit
			@endslot
		@endcomponent
	</div>
	<div class="col-span-2">
		@component('components.labels.label') Teléfono: @endcomponent
		@component('components.inputs.input-text')
			@slot('attributeEx')
				id="input-small" name="phone_edit" placeholder="Ingrese el teléfono" value="{{ $provider->phone }}" data-validation="phone required"
			@endslot
			@slot('classEx')
				phone remove
			@endslot
		@endcomponent
	</div>
	<div class="col-span-2">
		@component('components.labels.label') Contacto: @endcomponent
		@component('components.inputs.input-text')
			@slot('attributeEx')
				name="contact_edit" placeholder="Ingrese el contacto" data-validation="required" value="{{ $provider->contact }}"
			@endslot
			@slot('classEx')
				remove
			@endslot
		@endcomponent
	</div>
	<div class="col-span-2">
		@component('components.labels.label') Beneficiario: @endcomponent
		@component('components.inputs.input-text')
			@slot('attributeEx')
				name="beneficiary_edit" placeholder="Ingrese el nombre del beneficiario" data-validation="required" value="{{ $provider->beneficiary }}"
			@endslot
			@slot('classEx')
				remove
			@endslot
		@endcomponent
	</div>
	<div class="col-span-2">
		@component('components.labels.label')Comentarios (opcional): @endcomponent
		@component('components.inputs.text-area')
			@slot('attributeEx')
				name="commentaries_edit" placeholder="Ingrese un comentario"
			@endslot
			{{ $provider->commentaries }}
		@endcomponent
	</div>
@endcomponent
<div class="block form-container">
	@component('components.labels.title-divisor')
		CUENTAS BANCARIAS
	@endcomponent
	
	<div id="banks" class="form-container pt-4">
		<div id="form-container-inline">
			@component('components.labels.label') Para agregar una cuenta nueva es necesario colocar los siguientes campos: @endcomponent
			@component('components.containers.container-form')
				<div class="col-span-2">
					@component('components.labels.label') Banco: @endcomponent
					@php
						$attributeEx = "multiple=\"multiple\"";
						$classEx = "js-bank";
					@endphp
					@component('components.inputs.select', ['attributeEx' => $attributeEx, 'classEx' => $classEx])
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Alias: @endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							placeholder="Ingrese un alias"
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
							placeholder="Ingrese una cuenta bancaria"
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
							placeholder="Ingrese una sucursal"
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
							placeholder="Ingrese una referencia"
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
							placeholder="Ingrese una CLABE" data-validation="clabe"
						@endslot
						@slot('classEx')
							clabe
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Moneda: @endcomponent
					@php
						$options = collect(
							[
								['value'=>'MXN', 'description'=>'MXN'], 
								['value'=>'USD', 'description'=>'USD'], 
								['value'=>'EUR', 'description'=>'EUR'], 
								['value'=>'Otro', 'description'=>'Otro']
							]
						);
						$classEx = "custom-select currency";
					@endphp
					@component('components.inputs.select', ['options' => $options, 'attributeEx' => $attributeEx, 'classEx' => $classEx])
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') IBAN: @endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							placeholder="Ingrese un IBAN" data-validation="iban"
						@endslot
						@slot('classEx')
							iban
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') BIC/SWIFT: @endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							placeholder="Ingrese el BIC/SWIFT" data-validation="bic_swift"
						@endslot
						@slot('classEx')
							bic_swift
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Convenio (opcional): @endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							placeholder="Ingrese un convenio"
						@endslot
						@slot('classEx')
							agreement
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2 md:col-span-4 grid justify-items-center md:justify-items-start">
					@component('components.buttons.button', ["variant" => "warning"])
						@slot('attributeEx') id="addAccount" type="button" @endslot
						<span class="icon-plus"></span>
						<span>Agregar</span>
					@endcomponent
				</div>
			@endcomponent
		</div>
	</div>
	@php
		$modelHead = ["Banco", "Alias", "Cuenta", "Sucursal", "Referencia", "CLABE", "Moneda", "IBAN", "BIC/SWIFT", "Convenio", "Acciones"];
		$body 	   = [];
		$modelBody = [];
		foreach($provider->accounts->where('visible',1) as $bank)
		{
			$row =
			[
				[
					"content" =>
					[
						[
							"label" => $bank->bank->description
						],
						[
							"kind"			=> "components.inputs.input-text",
							"classEx"		=> "bank_account_id",
							"attributeEx"	=> "type=\"hidden\" value=\"".$bank->id."\""
						],
						[
							"kind"			=> "components.inputs.input-text",
							"classEx"		=> "providerBank",
							"attributeEx"	=> "type=\"hidden\" value=\"".$bank->idProvider."\""
						],
						[
							"kind"			=> "components.inputs.input-text",
							"attributeEx"	=> "type=\"hidden\" value=\"".$bank->idBanks."\""
						]
					]
				],
				[
					"content" =>
					[
						[
							"label" => $bank->alias != "" ? $bank->alias : "---"
						],
						[
							"kind"			=> "components.inputs.input-text",
							"attributeEx"	=> "type=\"hidden\" value=\"".$bank->alias."\""
						]
					]
				],
				[
					"content" =>
					[
						[
							"label" => $bank->account != "" ? $bank->account : "---"
						],
						[
							"kind"			=> "components.inputs.input-text",
							"attributeEx"	=> "type=\"hidden\" value=\"".$bank->account."\""
						]
					]
				],
				[
					"content" =>
					[
						[
							"label" => $bank->branch != "" ? $bank->branch : "---"
						],
						[
							"kind"			=> "components.inputs.input-text",
							"attributeEx"	=> "type=\"hidden\" class=\"js-branch\" value=\"".$bank->branch."\""
						]
					]
				],
				[
					"content" =>
					[
						[
							"label" => $bank->reference != "" ? $bank->reference : "---"
						],
						[
							"kind"			=> "components.inputs.input-text",
							"attributeEx"	=> "type=\"hidden\" value=\"".$bank->reference."\""
						]
					]
				],
				[
					"content" =>
					[
						[
							"label" => $bank->clabe != "" ? $bank->clabe : "---"
						],
						[
							"kind"			=> "components.inputs.input-text",
							"attributeEx"	=> "type=\"hidden\" value=\"".$bank->clabe."\""
						]
					]
				],
				[
					"content" =>
					[
						[
							"label" => $bank->currency
						],
						[
							"kind"			=> "components.inputs.input-text",
							"attributeEx"	=> "type=\"hidden\" value=\"".$bank->currency."\""
						]
					]
				],
				[
					"content" =>
					[
						[
							"label" => $bank->iban != "" ? $bank->iban : "---"
						],
						[
							"kind"			=> "components.inputs.input-text",
							"attributeEx"	=> "type=\"hidden\" value=\"".$bank->iban."\""
						]
					]
				],
				[
					"content" =>
					[
						[
							"label" => $bank->bic_swift != "" ? $bank->bic_swift : "---"
						],
						[
							"kind"			=> "components.inputs.input-text",
							"attributeEx"	=> "type=\"hidden\" value=\"".$bank->bic_swift."\""
						]
					]
				],
				[
					"content" =>
					[
						[
							"label" => $bank->agreement == '' ? "---" : $bank->agreement
						],
						[
							"kind"			=> "components.inputs.input-text",
							"attributeEx"	=> "type=\"hidden\" value=\"".$bank->agreement."\""
						]
					]
				],
				[
					"content" =>
					[
						[
							"kind"		  => "components.buttons.button",
							"classEx"     => "delete-account",
							"variant"	  => "red",
							"label"		  => "<span class=\"icon-x\"></span>"
						]
					]
				]
			];
			array_push($modelBody, $row);
		}
	@endphp
	@component('components.tables.AlwaysVisibleTable',[
		"modelHead" 			=> $modelHead,
		"modelBody" 			=> $modelBody,
		"themeBody" 			=> "striped"
	])
		@slot('attributeExBody')
			id="banks-body"
		@endslot
	@endcomponent
	<div id="delete_account"></div>
</div>
<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mb-6 py-4">
	<input type="hidden" name="idRequisition" value="{{ $requisition_id }}">
	<input type="hidden" name="idProviderSecondaryUpdate" value="{{ $provider->id }}">
	@component("components.buttons.button",["variant" => "success"])
		@slot('attributeEx') 
			name="btnUpdateProvider" id="updateProvider"
		@endslot
		@slot('classEx')
			w-48 md:w-auto
		@endslot
		<span class="icon-check"></span> Actualizar
	@endcomponent
	@component("components.buttons.button",["variant" => "red"])
		@slot('attributeEx')
			data-dismiss="modal"
		@endslot
		@slot('classEx') 
			w-48 md:w-auto
		@endslot
		<span class="icon-x"></span> Cerrar formulario
	@endcomponent
	@component("components.buttons.button",["variant" => "reset"])
		@slot('attributeEx') 
			id="return"
		@endslot
		@slot('classEx') 
			btn
		@endslot
		REGRESAR
	@endcomponent
</div>