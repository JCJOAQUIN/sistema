@component('components.modals.modal', ["variant" => "large"])
	@slot('id')
		relatedCFDIModal
	@endslot
	@slot('attributeEx')
		tabindex="-1"
	@endslot
	@slot('modalHeader')
		@component('components.buttons.button')
			@slot('attributeEx')
				type="button"
				data-dismiss="modal"
			@endslot
			@slot('classEx')
				close
			@endslot
			<span aria-hidden="true">&times;</span>
		@endcomponent
	@endslot
	@slot('modalBody')
		<div class="flex justify-center">
			<img src="{{ asset(getenv('LOADING_IMG')) }}" width="100">
		</div>
	@endslot
	@slot('modalFooter')
		<div class="mt-4 w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mb-6">
			@component('components.buttons.button',[
				"variant" => "primary"
					])
				@slot('classEx')
					add-cfdi-related
				@endslot
				@slot('attributeEx')
					type="button"
				@endslot
					<span class="icon-plus"></span>
					<span>Agregar</span>
			@endcomponent
			@component('components.buttons.button',[
				"variant" => "red"
				])
				@slot('attributeEx')
					type="button"
					data-dismiss="modal"
				@endslot
					<span class="icon-x"></span> <span>Cerrar</span>
			@endcomponent
		</div>
	@endslot
@endcomponent
@if((isset($bill) && $bill->version == '4.0') || (!isset($bill) && $cfdi_version == '4_0'))
	@component('components.modals.modal', ["variant" => "large"])
		@slot('id')
			paymentTaxesModal
		@endslot
		@slot('attributeEx')
			tabindex="-1"
		@endslot
		@slot('modalHeader')
			@component('components.buttons.button')
				@slot('attributeEx')
					type="button"
					data-dismiss="modal"
				@endslot
				@slot('classEx')
					close
				@endslot
				<span aria-hidden="true">&times;</span>
			@endcomponent
		@endslot
		@slot('modalBody')
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="hidden" id="taxes_base"
				@endslot
			@endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="hidden" id="taxes_index"
				@endslot
			@endcomponent
			@php
				$body = [];
				$modelBody = [];
				$modelHead = ["Base","Tipo","Impuesto","¿Tasa o cuota?","Valor de la tasa o cuota","Importe","Acción"]; 

				$body = [

				];
				$modelBody[] = $body;
			@endphp
			@component('components.tables.alwaysVisibleTable',[
				"modelBody" => $modelBody,
				"modelHead" => $modelHead
			])
				@slot('attributeEx')
					id="CFDI_PAYMENT_TAXES"
				@endslot
				@slot('classEx')
					table
				@endslot
				@slot('classExBody')
					body_cfdi_payment
				@endslot
			@endcomponent
			@component('components.buttons.button',["variant" => "warning"])	
				@slot('attributeEx')
					type="button"
				@endslot
				@slot('classEx')
					add-payment-new-tax
				@endslot
				<span class="icon-plus"></span>
				<span>Agregar impuesto</span>
			@endcomponent
		@endslot
		@slot('modalFooter')
			<div class="mt-4 w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mb-6">
				@component('components.buttons.button',[
					"variant" => "primary"
						])
					@slot('classEx')
						add-payment-taxes-table
					@endslot
					@slot('attributeEx')
						type="button"
					@endslot
						<span class="icon-plus"></span>
						<span>Agregar impuestos</span>
				@endcomponent
				@component('components.buttons.button',[
					"variant" => "red"
					])
					@slot('attributeEx')
						type="button"
						data-dismiss="modal"
					@endslot
						<span class="icon-x"></span> <span>Cerrar</span>
				@endcomponent
			</div>
		@endslot
	@endcomponent
@endif