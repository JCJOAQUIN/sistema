<script type="text/javascript">
@if(!isset($bill) && $cfdi_version == '4_0')
	$("#enterprise_selector>option[value='ACR150225DZ6']").attr("selected",true);
	$("#cfdi_export>option[value='01']").attr("selected",true);
@endif
$(document).ready(function()
{
	@php
		$selects = collect([
			[
				"identificator"          => ".js-enterprise",
				"placeholder"            => "Seleccione la empresa",
				"maximumSelectionLength" => "1"
			],
			[
				"identificator"          => ".js-regime",
				"placeholder"            => "Seleccione el régimen fiscal",
				"maximumSelectionLength" => "1"
			],
			[
				"identificator"          => ".js-cfdi",
				"placeholder"            => "Seleccione el uso de CFDI",
				"maximumSelectionLength" => "1"
			],
			[
				"identificator"          => ".js-cfdi-type",
				"placeholder"            => "Seleccione el tipo de CFDI",
				"maximumSelectionLength" => "1"
			],
			[
				"identificator"          => ".js-cfdi-export",
				"placeholder"            => "Seleccione el tipo de exportación",
				"maximumSelectionLength" => "1"
			],
			[
				"identificator"          => ".js-currency",
				"placeholder"            => "Seleccione el tipo de moneda",
				"maximumSelectionLength" => "1"
			],
			[
				"identificator"          => ".js-payment-way",
				"placeholder"            => "Seleccione el tipo de pago",
				"maximumSelectionLength" => "1"
			],
			[
				"identificator"          => ".js-payment-method",
				"placeholder"            => "Seleccione el Método de pago",
				"maximumSelectionLength" => "1"
			],
			[
				"identificator"          => ".js-tax-object",
				"placeholder"            => "Seleccione el objeto de impuesto",
				"maximumSelectionLength" => "1"
			],
			[
				"identificator"          => ".js-payment",
				"placeholder"            => "Seleccione la forma de pago",
				"maximumSelectionLength" => "1"
			]
		]);
	@endphp
	@component('components.scripts.selects', ["selects" => $selects]) @endcomponent

	formValidate();
	$('[name="rfc_receiver"]').keyup(function()
	{
		this.value = this.value.toLocaleUpperCase();
	});
	$("#cfdi-value,#cfdi-discount").on("contextmenu",function(e)
	{
		return false;
	});
	$('#cfdi-quantity,#cfdi-value,#cfdi-discount,[name="exchange"]').numeric({ altDecimal: ".", decimalPlaces: 2, negative: false });
	$('.value-tax_fee').numeric({ negative:false});
	$('[name="cfdi_payment_date"]').datepicker({ dateFormat: "dd-mm-yy" });
	generalSelect({'selector':'#cp_cfdi',			'model':2});
	generalSelect({'selector':'#cp_receiver_cfdi',	'model':2});
	generalSelect({'selector':'#project_selector',	'model':21});
	generalSelect({'selector':'#cfdi-unity-id',		'model':25});
	generalSelect({'selector':'#cfdi-product-id',	'model':26});
	add		= true;

	$(document).on('click','.tax-add',function()
	{
		if($('#cfdi-product-id').val()=='' || $('#cfdi-unity-id').val()=='' || $('#cfdi-quantity').val()=='' || $('#cfdi-value').val()=='' || $('#cfdi-description').val()=='' || $('#cfdi-discount').val()=='')
		{
			swal('','Por favor, concluya con los campos del concepto antes de proceder','error');
		}
		else if(Number($('#cfdi-quantity').val())==0 || Number($('#cfdi-value').val())==0)
		{
			swal('','La cantidad y el valor unitario no puede ser cero','error');
		}
		else
		{
			nextStep	= true;
			$('.value-tax_fee').each(function(i,v)
			{
				if($(this).parents('.tr_tax_taxes').find('.fee').val() != 'Exento' && $(this).val()=='')
				{
					nextStep	= false;
				}
			});
			$('.total-tax').each(function(i,v)
			{
				if($(this).parents('.tr_tax_taxes').find('.fee').val() != 'Exento' && $(this).val()=='')
				{
					nextStep	= false;
				}
			});
			if(nextStep)
			{
				@php
					$body = [];
					$modelBody = [];
					$modelHead = ["Tipo","Impuesto","¿Tasa o cuota?","Valor de la tasa o cuota","Importe","Acción"];

					$optionTax = [];
					$optionTax[] = ["value"  => "1", "description" => "Retención", "selected" => "selected"];
					$optionTax[] = ["value"  => "2", "description" => "Traslado"];

					$optionTaxName = [];
					$optionTaxName[] = ["value"  => "001", "description" => "ISR", "selected" => "selected"];
					$optionTaxName[] = ["value"  => "002", "description" => "IVA"];
					$optionTaxName[] = ["value"  => "003", "description" => "IEPS"];

					$optionFee = [];
					$optionFee[] = ["value"  => "Tasa", "description" => "Tasa", "selected" => "selected"];
					$optionFee[] = ["value"  => "Cuota", "description" => "Cuota", "attributeExOption" => "disabled=\"disabled\""];
					$optionFee[] = ["value"  => "Exento", "description" => "Exento", "attributeExOption" => "disabled=\"disabled\""];
					
					$body = 
					[ 
						"classEx" => "tr_tax_taxes",
						[
							"content" => 
							[
								[
									"kind" 			=> "components.inputs.select",
									"attributeEx"	=> "multiple",
									"classEx"		=> "tax-select",
									"options"		=> $optionTax
								]
							]
						],
						[
							"content" => 
							[
								[
									"kind" 			=> "components.inputs.select",
									"attributeEx"	=> "multiple",
									"classEx"		=> "tax-name",
									"options"		=> $optionTaxName
								]
							]
						],
						[
							"content" => 
							[
								[
									"kind" 			=> "components.inputs.select",
									"attributeEx"	=> "multiple",
									"classEx"		=> "fee",
									"options"		=> $optionFee
								]
							]
						],
						[
							"content" => 
							[
								[
									"kind"	 		=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"text\" placeholder=\"Ingrese el valor\"",
									"classEx"		=> "value-tax_fee"
								]
							]
						],
						[
							"content" => 
							[
								[
									"kind"	 		=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"text\" readonly=\"\" placeholder=\"Ingrese el importe\"",
									"classEx"		=> "total-tax"
								]
							]
						],
						[
							"content" => 
							[
								[
									"kind"		 	=> "components.buttons.button",
									"variant"		=> "red",
									"attributeEx"	=> "type=\"button\"",
									"classEx"		=> "tax-delete",
									"label"			=> "<span class=\"icon-x\"></span>"
								]
							]
						]
					];
					$modelBody[] = $body;
					$table = view("components.tables.alwaysVisibleTable",[
						"modelHead" => $modelHead,
						"modelBody" => $modelBody,
						"noHead" 	=> true
					])->render();
					$table = html_entity_decode(preg_replace("/(\r)*(\n)*/", "", $table));
				@endphp
				tax_Row = '{!!preg_replace("/(\r)*(\n)*/", "", $table)!!}';
				taxRow = $(tax_Row);
				taxRow = rowColor('#CFDI_TAXES_BODY',taxRow);
				$('#content_taxes').removeClass('hidden');
				$('#CFDI_TAXES_BODY').append(taxRow);
				$('.value-tax_fee').numeric({ negative:false});
				@php
					$selects = collect([
						[
							"identificator"          => ".tax-select,.tax-name,.fee",
							"placeholder"            => "Seleccione uno",
							"maximumSelectionLength" => "1"
						]
					]);
				@endphp
				@component('components.scripts.selects', ["selects" => $selects]) @endcomponent

				if($('#CFDI_TAXES .tr_tax_taxes').length==1)
				{
					$('#CFDI_TAXES').fadeIn();
				}
			}
			else
			{
				swal('','Por favor, concluya el registro del impuesto antes de agregar uno adicional','error');
			}
		}
	})
	@if((isset($bill) && $bill->version == '4.0') || (!isset($bill) && $cfdi_version == '4_0'))
		.on('change','#cfdi-tax-object-id',function()
		{
			if($(this).val() == '')
			{
				$('.detail_taxes').hide();
			}
			else if($(this).val() != '01')
			{
				$('.detail_taxes').show();
			}
			else
			{
				$('.detail_taxes').hide();
			}
		})
		.on('change','[name="cfdi_payment_objeto_imp[]"]',function()
		{
			if($(this).val() == '')
			{
				$(this).parents('.tr_related_payments').find('.add-payment-taxes').hide();
			}
			else if($(this).val() != '01')
			{
				$(this).parents('.tr_related_payments').find('.add-payment-taxes').show();
			}
			else
			{
				$(this).parents('.tr_related_payments').find('.add-payment-taxes').hide();
			}
		})
		.on('click','.add-payment-taxes',function()
		{
			index = $('.related-payments .tr_related_payments').index($(this).parents('.tr_related_payments'));
			$('#taxes_index').val(index);
			base = $(this).parent().siblings().find('[name="cfdi_payment_comp_amount[]"]').val();
			if(base == '')
			{
				swal('','Debe ingresar el importe pagado para continuar.','error')
			}
			else if(Number(base) == 0)
			{
				swal('','El importe pagado no puede ser cero.','error')
			}
			else
			{
				$('#taxes_base').val(base);
				taxes = [];
				$('#CFDI_PAYMENT_TAXES .body_cfdi_payment').html('');
				if($(this).siblings('[name="cfdi_payment_related_taxes[]"]').val() != '')
				{
					taxes = JSON.parse($(this).siblings('[name="cfdi_payment_related_taxes[]"]').val());
				}
				$.each(taxes,function(i,v)
				{
					@php
						$body		= [];
						$modelBody	= [];
						$modelHead = ["Base","Tipo","Impuesto","¿Tasa o cuota?","Valor de la tasa o cuota","Importe","Acción"];

						$body = [ "classEx" => "tr",
							[
								"classEx" => "td",
								"content" =>
								[
									[
										"kind"			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"text\"",
										"classEx"		=> "payment-base-tax"
									]
								]
							],
							[
								"classEx" => "td",
								"content" =>
								[
									[
										"kind"			=> "components.inputs.select",
										"attributeEx"	=> "multiple",
										"classEx"		=> "payment-tax-select",
									]
								]
							],
							[
								"classEx" => "td",
								"content" =>
								[
									[
										"kind"			=> "components.inputs.select",
										"attributeEx"	=> "multiple",
										"classEx"		=> "payment-tax-name"
									]
								]
							],
							[
								"classEx" => "td",
								"content" =>
								[
									[
										"kind"			=> "components.inputs.select",
										"attributeEx"	=> "multiple",
										"classEx"		=> "payment-fee"
									]
								]
							],
							[
								"classEx" => "td",
								"content" =>
								[
									[
										"kind"			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"text\"",
										"classEx"		=> "payment-value-tax_fee"
									]
								]
							],
							[
								"classEx" => "td",
								"content" =>
								[
									[
										"kind"			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"text\" readonly=\"\"",
										"classEx"		=> "payment-total-tax"
									]
								]
							],
							[
								"classEx" => "td",
								"content" =>
								[
									[
										"kind"			=> "components.buttons.button",
										"variant"		=> "red",
										"attributeEx"	=> "type=\"button\"",
										"classEx"		=> "tax-delete",
										"label"			=> "<span class=\"icon-x\"></span>"
									]
								]
							]
						];
						$modelBody[] = $body;
						$body_payment = view("components.tables.alwaysVisibleTable",[
							"modelHead" => $modelHead,
							"modelBody" => $modelBody,
							"attributeEx" => "id=\"CFDI_PAYMENT_TAXES\"",
							"noHead"	=> true
						])->render();
						$table = html_entity_decode(preg_replace("/(\r)*(\n)*/", "", $body_payment));
					@endphp
					table = '{!!preg_replace("/(\r)*(\n)*/", "", $table)!!}';
					tr = $(table);
					tr = rowColor('#CFDI_PAYMENT_TAXES .body_cfdi_payment',tr);
					tr.find('.payment-base-tax').val(v.base);
					tr.find('.payment-tax-select').append($('<option value="Retención">Retención</option>'))
												.append($('<option value="Traslado">Traslado</option>')).val(v.tax_kind);
					tr.find('.payment-tax-name').append($('<option value="001">ISR</option>'))
												.append($('<option value="002">IVA</option>'))
												.append($('<option value="003">IEPS</option>')).val(v.tax_name);
					tr.find('.payment-fee').append($('<option value="Tasa">Tasa</option>'))
											.append($('<option value="Cuota" disabled="">Cuota</option>'))
											.append($('<option value="Exento" disabled="">Exento</option>')).val(v.fee);
					tr.find('.payment-value-tax_fee').val(v.tax_fee);
					tr.find('.payment-total-tax').val(v.tax_total);
					$('#CFDI_PAYMENT_TAXES .body_cfdi_payment').append(tr);
					@php
						$selects = collect([
							[
								"identificator"          => ".payment-tax-select",
								"placeholder"            => "Seleccione el tipo",
								"maximumSelectionLength" => "1"
							],
							[
								"identificator"          => ".payment-tax-name",
								"placeholder"            => "Seleccione el impuesto",
								"maximumSelectionLength" => "1"
							],
							[
								"identificator"          => ".payment-fee",
								"placeholder"            => "Seleccione tasa o cuota",
								"maximumSelectionLength" => "1"
							],
						]);
					@endphp
					@component('components.scripts.selects', ["selects" => $selects]) @endcomponent
				})
				$('#paymentTaxesModal').modal('show');
			}
		})
		.on('click','.add-payment-new-tax',function()
		{
			nextStep	= true;
			$('.payment-value-tax_fee').each(function(i,v)
			{
				if($(this).parents('.td').parents('.tr').find('.payment-fee').val() != 'Exento' && $(this).val()=='')
				{
					nextStep	= false;
				}
			});
			$('.payment-total-tax').each(function(i,v)
			{
				if($(this).parents('.td').parents('.tr').find('.payment-fee').val() != 'Exento' && $(this).val()=='')
				{
					nextStep	= false;
				}
			});
			if(nextStep)
			{

				@php
					$body		= [];
					$modelBody	= [];
					$modelHead = ["Base","Tipo","Impuesto","¿Tasa o cuota?","Valor de la tasa o cuota","Importe","Acción"];

					$optionPayTax = [];
					$optionPayTax[] = ["value" => "1", "description" => "Retención", "selected" => "selected"];
					$optionPayTax[] = ["value" => "2", "description" => "Traslado"];

					$optionPayName = [];
					$optionPayName[] = ["value" => "001", "description" => "ISR", "selected" => "selected"];
					$optionPayName[] = ["value" => "002", "description" => "IVA"];
					$optionPayName[] = ["value" => "003", "description" => "IEPS"];

					$optionPayFee = [];
					$optionPayFee[] = ["value" => "Tasa", "description" => "Tasa", "selected" => "selected"];
					$optionPayFee[] = ["value" => "Cuota", "description" => "Cuota", "attributeExOption" => "disabled" ];
					$optionPayFee[] = ["value" => "Exento", "description" => "Exento", "attributeExOption" => "disabled"];

					$body = [ "classEx" => "tr",
						[
							"classEx" => "td",
							"content" =>
							[
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"text\"",
									"classEx"		=> "payment-base-tax"
								]
							]
						],
						[
							"classEx" => "td",
							"content" =>
							[
								[
									"kind"			=> "components.inputs.select",
									"attributeEx"	=> "multiple",
									"classEx"		=> "payment-tax-select",
									"options"		=> $optionPayTax
								]
							]
						],
						[
							"classEx" => "td",
							"content" =>
							[
								[
									"kind"			=> "components.inputs.select",
									"attributeEx"	=> "multiple",
									"classEx"		=> "payment-tax-name",
									"options"		=> $optionPayName
								]
							]
						],
						[
							"classEx" => "td",
							"content" =>
							[
								[
									"kind"			=> "components.inputs.select",
									"attributeEx"	=> "multiple",
									"classEx"		=> "payment-fee",
									"options"		=> $optionPayFee
								]
							]
						],
						[
							"classEx" => "td",
							"content" =>
							[
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"text\" placeholder=\"Ingrese el valor\"",
									"classEx"		=> "payment-value-tax_fee"
								]
							]
						],
						[
							"classEx" => "td",
							"content" =>
							[
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"text\" readonly=\"\" placeholder=\"Ingrese el importe\"",
									"classEx"		=> "payment-total-tax"
								]
							]
						],
						[
							"classEx" => "td",
							"content" =>
							[
								[
									"kind"			=> "components.buttons.button",
									"variant"		=> "red",
									"attributeEx"	=> "type=\"button\"",
									"classEx"		=> "tax-delete",
									"label"			=> "<span class=\"icon-x\"></span>"
								]
							]
						]
					];
					$modelBody[] = $body;

					$body_payment = view("components.tables.alwaysVisibleTable",[
						"modelHead" => $modelHead,
						"modelBody" => $modelBody,
						"attributeEx" => "id=\"CFDI_PAYMENT_TAXES\"",
						"noHead"	=> true
					])->render();
					$table = html_entity_decode(preg_replace("/(\r)*(\n)*/", "", $body_payment));
				@endphp
				table	= '{!!preg_replace("/(\r)*(\n)*/", "", $table)!!}';
				tr 		= $(table);
				tr = rowColor('#CFDI_PAYMENT_TAXES .body_cfdi_payment',tr);
				tr.find('.payment-base-tax').val($('#taxes_base').val());

				$('#CFDI_PAYMENT_TAXES .body_cfdi_payment').append(tr);
				$('.payment-value-tax_fee').numeric({ negative:false });
				@php
					$selects = collect([
						[
							"identificator"          => ".payment-tax-select",
							"placeholder"            => "Seleccione el tipo",
							"maximumSelectionLength" => "1"
						],
						[
							"identificator"          => ".payment-tax-name",
							"placeholder"            => "Seleccione el impuesto",
							"maximumSelectionLength" => "1"
						],
						[
							"identificator"          => ".payment-fee",
							"placeholder"            => "Seleccione tasa o cuota",
							"maximumSelectionLength" => "1"
						],
					]);
				@endphp
				@component('components.scripts.selects', ["selects" => $selects]) @endcomponent
			}
			else
			{
				swal('','Por favor, concluya el registro del impuesto antes de agregar uno adicional','error');
			}
		})
		.on('change','.payment-tax-select', function()
		{
			tipoImp		= Number($(this).val());
			impuesto	= $(this).parents('.td').parents('.tr').find('.payment-tax-name').val();
			factor		= $(this).parents('.td').parents('.tr').find('.payment-fee').val();
			valor		= $(this).parents('.td').parents('.tr').find('.payment-value-tax_fee').val()!='' ? Number($(this).parents('.td').parents('.tr').find('.payment-value-tax_fee').val()) : null;
			tr			= $(this).parents('.td').parents('.tr');
			if(tipoImp==1)
			{
				$(this).parents('.td').parents('.tr').find('.payment-fee').val('Tasa').trigger('change');
				$(this).parents('.td').parents('.tr').find('.payment-fee option[value="Exento"]').prop('disabled',true).trigger('change');
				$(".payment-fee").select2("destroy").select2();
			}
			else
			{
				$(this).parents('.td').parents('.tr').find('.payment-fee').val('Tasa').trigger('change');
				$(this).parents('.td').parents('.tr').find('.payment-fee option[value="Exento"]').prop('disabled',false).trigger('change');
				$(".payment-fee").select2("destroy").select2();
			}
			if(impuesto != '' && factor != '' && valor != null)
			{
				rules(tipoImp,impuesto,factor,valor,tr,'taxes');
			}
		})
		.on('change','.payment-fee',function()
		{
			if($(this).val() == 'Exento')
			{
				$(this).parents('.td').parents('.tr').find('.payment-value-tax_fee').val('').prop('disabled',true);
				$(this).parents('.td').parents('.tr').find('.payment-total-tax').val('');
			}
			else
			{
				$(this).parents('.td').parents('.tr').find('.payment-value-tax_fee').val('').prop('disabled',false);
				tipoImp		= Number($(this).parents('.td').parents('.tr').find('.payment-tax-select').val());
				impuesto	= $(this).parents('.td').parents('.tr').find('.payment-tax-name').val();
				factor		= $(this).parents('.td').parents('.tr').find('.payment-fee').val();
				valor		= $(this).parents('.td').parents('.tr').find('.payment-value-tax_fee').val()!='' ? Number($(this).parents('.td').parents('.tr').find('.payment-value-tax_fee').val()) : null;
				tr			= $(this).parents('.td').parents('.tr');
				if(impuesto != '' && factor != '' && valor != null)
				{
					rules(tipoImp,impuesto,factor,valor,tr,'taxes');
				}
			}
		})
		.on('change','.payment-tax-name', function()
		{
			impuesto	= $(this).val();
			if(impuesto == '001' || impuesto == '002')
			{
				$(this).parents('.td').parents('.tr').find('.payment-fee option[value="Cuota"]').prop('disabled',true).trigger('change');
				$(this).parents('.td').parents('.tr').find('.payment-fee').val('Tasa').trigger('change');
				$(".payment-fee").select2("destroy").select2();
			}
			else
			{
				$(this).parents('.td').parents('.tr').find('.payment-fee option[value="Cuota"]').prop('disabled',false).trigger('change');
				$(".payment-fee").select2("destroy").select2();
			}
			tipoImp		= Number($(this).parents('.td').parents('.tr').find('.payment-tax-select').val());
			factor		= $(this).parents('.td').parents('.tr').find('.payment-fee').val();
			valor		= $(this).parents('.td').parents('.tr').find('.payment-value-tax_fee').val()!='' ? Number($(this).parents('.td').parents('.tr').find('.payment-value-tax_fee').val()) : null;
			tr			= $(this).parents('.td').parents('.tr');
			if(impuesto != '' && factor != '' && valor != null)
			{
				rules(tipoImp,impuesto,factor,valor,tr,'taxes');
			}
		})
		.on('focusout','.payment-value-tax_fee',function()
		{
			tipoImp		= Number($(this).parents('.td').parents('.tr').find('.payment-tax-select').val());
			impuesto	= $(this).parents('.td').parents('.tr').find('.payment-tax-name').val();
			factor		= $(this).parents('.td').parents('.tr').find('.payment-fee').val();
			valor		= $(this).val()!='' ? Number($(this).val()) : null;
			tr			= $(this).parents('.td').parents('.tr');
			if(impuesto != '' && factor != '')
			{
				rules(tipoImp,impuesto,factor,valor,tr,'taxes');
			}
		})
		.on('input','.payment-base-tax',function()
		{
			tr       = $(this).parents('.td').parents('.tr');
			tipoImp  = tr.find('.payment-tax-select').val();
			impuesto = tr.find('.payment-tax-name').val();
			factor   = tr.find('.payment-fee').val();
			valor    = tr.find('.payment-value-tax_fee').val();
			rules(tipoImp,impuesto,factor,valor,tr,'taxes');
		})
		.on('click','.add-payment-taxes-table',function()
		{
			if($('#CFDI_PAYMENT_TAXES .body_cfdi_payment').length == 0)
			{
				swal('','Por favor, ingrese al menos un impuesto para continuar','error');
			}
			nextStep	= true;
			$('.payment-value-tax_fee').each(function(i,v)
			{
				if($(this).parents('.td').parents('.tr').find('.payment-fee').val() != 'Exento' && $(this).val()=='')
				{
					nextStep	= false;
				}
			});
			$('.payment-total-tax').each(function(i,v)
			{
				if($(this).parents('.td').parents('.tr').find('.payment-fee').val() != 'Exento' && $(this).val()=='')
				{
					nextStep	= false;
				}
			});
			if(nextStep)
			{
				taxes = new Object();
				$('#CFDI_PAYMENT_TAXES .tr').each(function(i,v)
				{
					taxes[i]              = new Object();
					taxes[i]['base']      = $(this).find('.payment-base-tax').val();
					taxes[i]['tax_kind']  = $(this).find('.payment-tax-select').val();
					taxes[i]['tax_name']  = $(this).find('.payment-tax-name').val();
					taxes[i]['fee']       = $(this).find('.payment-fee').val();
					taxes[i]['tax_fee']   = $(this).find('.payment-value-tax_fee').val();
					taxes[i]['tax_total'] = $(this).find('.payment-total-tax').val();
				});
				index = $('#taxes_index').val();
				$('.related-payments .tr_related_payments').each(function(i,v)
				{
					if(i == index)
					{
						$(this).find('[name="cfdi_payment_related_taxes[]"]').val(JSON.stringify(taxes));
					}
				});
				$('#paymentTaxesModal').modal('hide');
			}
			else
			{
				swal('','Por favor, concluya con el registro de todos los campos para continuar','error');
			}
		})
	@endif
	.on('click','[data-toggle="modal"]',function()
	{
		cfdi_version = @if(isset($bill)) '{{$bill->version}}' @else '{{str_replace("_",".",$cfdi_version)}}' @endif;
		cfdi_kind    = $('[name="cfdi_kind"]').val();
		choosen      = new Object();
		@if(isset($incomeBill) && $incomeBill && isset($requestModel))
			income = {{ $requestModel->folio }};
		@elseif(isset($incomeBill) && $incomeBill && isset($request))
			income = {{ $request->folio }};
		@endif
		option_id = {{ $option_id }};
		$('[name="cfdi_related_id[]"]').each(function(i,v)
		{
			choosen[$(this).val()] = new Object();
			choosen[$(this).val()]['id'] = $(this).val();
			@if((isset($bill) && $bill->version == '4.0') || (!isset($bill) && $cfdi_version == '4_0'))
				choosen[$(this).val()]['rel'] = $('[name="cfdi_related_kind[]"]')[i].value;
			@endif
		});
		$.ajax(
		{
			type	: 'post',
			url		: '{{ route("bill.cfdi.related") }}',
			data	: {'choosen':choosen, 'cfdi_kind':cfdi_kind, 'cfdi_version':cfdi_version, 'option_id':option_id @if(isset($incomeBill) && $incomeBill) ,income:income @endif },
			success	: function(data)
			{
				$('#relatedCFDIModal .modal-body').html(data);
				rangeDate();
				$('.delete_date_search').on('click', function()
				{
					$('[name="cfdi_rel[]"]:not(:checked)').parents('.tr_relation').remove();
					$('#related_result_pagination').remove();
					$('#cfdi_related_page').val('1');
				});
				@php
					$selects = collect([
						[
							"identificator"          => "#emiter_cfdi_search",
							"placeholder"            => "Seleccione el emisor"
						],
						[
							"identificator"          => ".js-relation",
							"placeholder"            => "Seleccione una relación",
							"maximumSelectionLength" => "1"
						]
					]);
				@endphp
				@component('components.scripts.selects', ["selects" => $selects]) @endcomponent

				$.validate({
					form		: '#form-search-cfdi',
					lang		: 'es',
					onSuccess	: function($form)
					{
						info         = $form.serializeArray();
						cfdi_version = @if(isset($bill)) '{{$bill->version}}' @else '{{str_replace("_",".",$cfdi_version)}}' @endif;
						cfdi_kind    = $('[name="cfdi_kind"]').val();
						info.push({'name':'cfdi_version','value':cfdi_version});
						info.push({'name':'cfdi_kind','value':cfdi_kind});
						info.push({'name':'option_id','value':{{ $option_id }} });
						@if(isset($incomeBill) && $incomeBill && isset($requestModel))
							info.push({'name':'income','value':{{ $requestModel->folio }} });
						@elseif(isset($incomeBill) && $incomeBill && isset($request))
							info.push({'name':'income','value':{{ $request->folio }} });
						@endif
						$('[name="cfdi_rel[]"]:checked').each(function(i,v)
						{
							temp	= $(this).serializeArray();
							info.push(temp[0]);
						});
						@if((isset($bill) && $bill->version == '4.0') || (!isset($bill) && $cfdi_version == '4_0'))
							$('[name="cfdi_rel[]"]:checked').parents('.tr_relation').find('[name="cfdi_rel_kind[]"]').each(function(i,v)
							{
								temp	= $(this).serializeArray();
								info.push(temp[0]);
							});
						@endif
						$('.cfdi-search-container').html('<center><img src="{{asset(getenv('LOADING_IMG'))}}" width="100"></center>');
						$.ajax({
							method	: 'post',
							data	: info,
							url		: '{{ route('bill.cfdi.related.search') }}',
							success	: function (response)
							{
								$('.cfdi-search-container').html(response);
								@if(!isset($bill) && $cfdi_version == '4_0')
									$(".js-relations>option[value='01']").attr("selected",true);
								@endif
								@php
									$selects = collect([
										[
											"identificator"          => ".js-relation,.js-relations",
											"placeholder"            => "Seleccione una relación",
											"maximumSelectionLength" => "1"
										]
									]);
								@endphp
								@component('components.scripts.selects', ["selects" => $selects]) @endcomponent
							},
							error : function(data)
							{
								swal('','Sucedió un error, por favor intente de nuevo.','error');
								$('.cfdi-search-container').html('');
							}
						});
						return false;
					}
				});
				if($('[name="related_kind_cfdi"]').val())
				{
					$('#cfdi_relation_kind').val($('[name="related_kind_cfdi"]').val()).trigger('change');
				}
				else
				{
					$('#cfdi_relation_kind').val('01').trigger('change');
				}
			},
			error : function(data)
			{
				swal('','Sucedió un error, por favor intente de nuevo.','error');
				$('#relatedCFDIModal').modal('hide');
			}
		});
	})
	.on('click','#related_result_pagination a',function(e)
	{
		e.preventDefault();
		href   = $(this).attr('href');
		url    = new URL(href);
		params = new URLSearchParams(url.search);
		page   = params.get('page');
		$('#cfdi_related_page').val(page);
		$('#form-search-cfdi').submit();
	})
	.on('change','#cfdi-product-id',function()
	{
		$('#cfdi-unity-id').val(null).trigger('change');
		$('#cfdi-quantity').val('');
		$('#cfdi-description').val('');
		$('#cfdi-value').val('');
		$('#cfdi-total').val(0);
		$('#cfdi-discount').val(0);
		$('#cfdi-tax-object-id').val('01').trigger('change');
		$('#CFDI_TAXES tbody tr').remove();
		$('#CFDI_TAXES').hide();
	})
	.on('change','#emiter_cfdi_search,[name="receptor_cfdi_search"]',function()
	{
		$('[name="cfdi_rel[]"]:not(:checked)').parents('.tr_relation').remove();
		$('#related_result_pagination').remove();
		$('#cfdi_related_page').val('1');
	})
	.on('click','.tax-delete',function()
	{
		$(this).parents('.tr_tax_taxes, .tr_cfdi_payment, .tr').remove();
		if($('#CFDI_TAXES .tr_tax_taxes').length<1)
		{
			$('#CFDI_TAXES').fadeOut();
			add = true;
		}
	})
	.on('click','.cfdi-concept-delete',function()
	{
		$(this).parents('.cfdi-concepts-taxes').remove();
		subtotalGlobal	= 0;
		discountGlobal	= 0;
		retentionGlobal	= 0;
		transferGlobal	= 0;
		$('[name="amount[]"]').each(function(i,v)
		{
			subtotalGlobal += Number($(this).val());
		});
		$('[name="discount[]"]').each(function(i,v)
		{
			discountGlobal += Number($(this).val());
		});
		$('[name^="tras_total_tax"]').each(function(i,v)
		{
			transferGlobal += Number($(this).val());
		});
		$('[name^="ret_total_tax"]').each(function(i,v)
		{
			retentionGlobal += Number($(this).val());
		});
		totalGlobal = Number(Number(subtotalGlobal.toFixed(2)) - Number(discountGlobal.toFixed(2)) + Number(transferGlobal.toFixed(2)) - Number(retentionGlobal.toFixed(2)));
		$('[name="subtotal"]').val(subtotalGlobal.toFixed(2));
		$('[name="discount_cfdi"]').val(discountGlobal.toFixed(2));
		$('[name="tras_total"]').val(transferGlobal.toFixed(2));
		$('[name="ret_total"]').val(retentionGlobal.toFixed(2));
		$('[name="cfdi_total"]').val(totalGlobal.toFixed(2));
	})
	.on('click','.cfdi-concept-modify',function()
	{
		$('.cfdi-concept-modify').prop('disabled', true);
		selector	= $(this).parents('.cfdi-concepts');
		selector2	= $(this).parents('.cfdi-concepts');
		id			= selector.find('[name="cfdi_item"]').val();
		$('#cfdi-product-id').html('<option value="'+selector.find('[name="product_id[]"]').val()+'" selected>'+selector.find('[name="product_id[]"]').attr('data-text')+'</option>');
		$('#cfdi-unity-id').html('<option value="'+selector.find('[name="unity_id[]"]').val()+'" selected>'+selector.find('[name="unity_id[]"]').attr('data-text')+'</option>');
		$('#cfdi-quantity').val(selector.find('[name="quantity[]"]').val());
		@if((isset($bill) && $bill->version == '4.0') || (!isset($bill) && $cfdi_version == '4_0'))
			$('#cfdi-tax-object-id').val(selector.find('[name="tax_object_id[]"]').val()).trigger('change');
		@endif
		$('#cfdi-description').val(selector.find('[name="description[]"]').val());
		$('#cfdi-value').val(selector.find('[name="valueCFDI[]"]').val());
		$('#cfdi-total').val(selector.find('[name="amount[]"]').val());
		$('#cfdi-discount').val(selector.find('[name="discount[]"]').val());
		ret				= selector2.find('[name^="ret["]');
		ret_fee			= selector2.find('[name^="ret_fee["]');
		ret_tax_fee		= selector2.find('[name^="ret_tax_fee["]');
		ret_total_tax	= selector2.find('[name^="ret_total_tax["]');
		if(ret.length>0)
		{
			ret.each(function(i,v)
			{
				@php
					$body = [];
					$modelBody = [];
					$modelHead = ["Tipo","Impuesto","¿Tasa o cuota?","Valor de la tasa o cuota","Importe","Acción"];

					$optionTax = [];
					$optionTax[] = ["value"  => "1", "description" => "Retención", "selected" => "selected"];
					$optionTax[] = ["value"  => "2", "description" => "Traslado"];

					$body = 
					[ 
						"classEx" => "tr_tax_taxes",
						[
							"content" => 
							[
								[
									"kind" 			=> "components.inputs.select",
									"attributeEx"	=> "multiple",
									"classEx"		=> "tax-select",
									"options"		=> $optionTax
								]
							]
						],
						[
							"content" => 
							[
								[
									"kind" 			=> "components.inputs.select",
									"attributeEx"	=> "multiple",
									"classEx"		=> "tax-name",
								]
							]
						],
						[
							"content" => 
							[
								[
									"kind" 			=> "components.inputs.select",
									"attributeEx"	=> "multiple",
									"classEx"		=> "fee",
								]
							]
						],
						[
							"content" => 
							[
								[
									"kind"	 		=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"text\"",
									"classEx"		=> "value-tax_fee"
								]
							]
						],
						[
							"content" => 
							[
								[
									"kind"	 		=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"text\" readonly=\"\"",
									"classEx"		=> "total-tax"
								]
							]
						],
						[
							"content" => 
							[
								[
									"kind"		 	=> "components.buttons.button",
									"variant"		=> "red",
									"attributeEx"	=> "type=\"button\"",
									"classEx"		=> "tax-delete",
									"label"			=> "<span class=\"icon-x\"></span>"
								]
							]
						]
					];
					$modelBody[] = $body;
					$table = view("components.tables.alwaysVisibleTable",[
						"modelHead" => $modelHead,
						"modelBody" => $modelBody,
						"attributeEx" => "id=\"CFDI_TAXES\"",
						"noHead" 	=> true
					])->render();
					$table = html_entity_decode(preg_replace("/(\r)*(\n)*/", "", $table));
				@endphp
				tax_Row = '{!!preg_replace("/(\r)*(\n)*/", "", $table)!!}';
				taxRow = $(tax_Row);
				taxRow = rowColor('#CFDI_TAXES_BODY',taxRow);
				taxRow.find('.tax-name').append('<option value="001" '+(ret.get(i).value=='001'?'selected':'')+'>ISR</option>');
				taxRow.find('.tax-name').append('<option value="002" '+(ret.get(i).value=='002'?'selected':'')+'>IVA</option>');
				taxRow.find('.tax-name').append('<option value="003" '+(ret.get(i).value=='003'?'selected':'')+'>IEPS</option>');
				taxRow.find('.fee').append('<option value="Tasa" '+(ret_fee.get(i).value=='Tasa'?'selected':'')+'>Tasa</option>');
				taxRow.find('.fee').append('<option value="Cuota" '+(ret_fee.get(i).value=='Cuota'?'selected':'')+'>Cuota</option>');
				taxRow.find('.fee').append('<option value="Exento" disabled>Exento</option>');
				taxRow.find('.value-tax_fee').val(ret_tax_fee.get(i).value);
				taxRow.find('.total-tax').val(ret_total_tax.get(i).value);
				
				$('#content_taxes').removeClass('hidden');
				$('#CFDI_TAXES').removeAttr('style');
				$('#CFDI_TAXES_BODY').fadeIn(300).append(taxRow);

				@php
					$selects = collect([
						[
							"identificator"          => ".tax-select,.tax-name,.fee",
							"placeholder"            => "Seleccione uno",
							"maximumSelectionLength" => "1"
						]
					]);
				@endphp
				@component('components.scripts.selects', ["selects" => $selects]) @endcomponent
			});
		}
		tras				= selector2.find('[name^="tras["]');
		tras_fee			= selector2.find('[name^="tras_fee["]');
		tras_tax_fee		= selector2.find('[name^="tras_tax_fee["]');
		tras_total_tax		= selector2.find('[name^="tras_total_tax["]');
		if(tras.length>0)
		{
			tras.each(function(i,v)
			{
				@php
					$body = [];
					$modelBody = [];
					$modelHead = ["Tipo","Impuesto","¿Tasa o cuota?","Valor de la tasa o cuota","Importe","Acción"];

					$optionTax = [];
					$optionTax[] = ["value"  => "1", "description" => "Retención"];
					$optionTax[] = ["value"  => "2", "description" => "Traslado", "selected" => "selected"];

					$body = [ "classEx" => "tr_tax_taxes",
						[
							"content" => 
							[
								[
									"kind" 			=> "components.inputs.select",
									"attributeEx"	=> "multiple",
									"classEx"		=> "tax-select",
									"options"		=> $optionTax
								]
							]
						],
						[
							"content" => 
							[
								[
									"kind" 			=> "components.inputs.select",
									"attributeEx"	=> "multiple",
									"classEx"		=> "tax-name",
								]
							]
						],
						[
							"content" => 
							[
								[
									"kind" 			=> "components.inputs.select",
									"attributeEx"	=> "multiple",
									"classEx"		=> "fee",
								]
							]
						],
						[
							"content" => 
							[
								[
									"kind"	 		=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"text\"",
									"classEx"		=> "value-tax_fee"
								]
							]
						],
						[
							"content" => 
							[
								[
									"kind"	 		=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"text\" readonly=\"\"",
									"classEx"		=> "total-tax"
								]
							]
						],
						[
							"content" => 
							[
								[
									"kind"		 	=> "components.buttons.button",
									"variant"		=> "red",
									"attributeEx"	=> "type=\"button\"",
									"classEx"		=> "tax-delete",
									"label"			=> "<span class=\"icon-x\"></span>"
								]
							]
						]
					];
					$modelBody[] = $body;
					$table = view("components.tables.alwaysVisibleTable",[
						"modelHead" 	=> $modelHead,
						"modelBody" 	=> $modelBody,
						"attributeEx" 	=> "id=\"CFDI_TAXES\"",
						"noHead" 		=> true
					])->render();
					$table = html_entity_decode(preg_replace("/(\r)*(\n)*/", "", $table));
				@endphp
				tax_Row = '{!!preg_replace("/(\r)*(\n)*/", "", $table)!!}';
				taxRow = $(tax_Row);
				taxRow = rowColor('#CFDI_TAXES_BODY',taxRow);
				taxRow.find('.tax-name').append('<option value="001" '+(tras.get(i).value=='001'?'selected':'')+'>ISR</option>');
				taxRow.find('.tax-name').append('<option value="002" '+(tras.get(i).value=='002'?'selected':'')+'>IVA</option>');
				taxRow.find('.tax-name').append('<option value="003" '+(tras.get(i).value=='003'?'selected':'')+'>IEPS</option>');
				taxRow.find('.fee').append('<option value="Tasa" '+(tras_fee.get(i).value=='Tasa'?'selected':'')+'>Tasa</option>');
				taxRow.find('.fee').append('<option value="Cuota" '+(tras_fee.get(i).value=='Cuota'?'selected':'')+'>Cuota</option>');
				taxRow.find('.fee').append('<option value="Exento" disabled>Exento</option>');
				taxRow.find('.value-tax_fee').val(tras_tax_fee.get(i).value);
				taxRow.find('.total-tax').val(tras_total_tax.get(i).value);

				$('#content_taxes').removeClass('hidden');
				$('#CFDI_TAXES').removeAttr('style');
				$('#CFDI_TAXES_BODY').fadeIn(300).append(taxRow);

				@php
					$selects = collect([
						[
							"identificator"          => ".tax-select,.tax-name,.fee",
							"placeholder"            => "Seleccione uno",
							"maximumSelectionLength" => "1"
						]
					]);
				@endphp
				@component('components.scripts.selects', ["selects" => $selects]) @endcomponent
			});
		}
		selector.remove();
		subtotalGlobal	= 0;
		discountGlobal	= 0;
		retentionGlobal	= 0;
		transferGlobal	= 0;
		$('[name="amount[]"]').each(function(i,v)
		{
			subtotalGlobal += Number($(this).val());
		});
		$('[name="discount[]"]').each(function(i,v)
		{
			discountGlobal += Number($(this).val());
		});
		$('[name^="tras_total_tax"]').each(function(i,v)
		{
			transferGlobal += Number($(this).val());
		});
		$('[name^="ret_total_tax"]').each(function(i,v)
		{
			retentionGlobal += Number($(this).val());
		});
		totalGlobal = Number(Number(subtotalGlobal.toFixed(2)) - Number(discountGlobal.toFixed(2)) + Number(transferGlobal.toFixed(2)) - Number(retentionGlobal.toFixed(2)));
		$('[name="subtotal"]').val(subtotalGlobal.toFixed(2));
		$('[name="discount_cfdi"]').val(discountGlobal.toFixed(2));
		$('[name="tras_total"]').val(transferGlobal.toFixed(2));
		$('[name="ret_total"]').val(retentionGlobal.toFixed(2));
		$('[name="cfdi_total"]').val(totalGlobal.toFixed(2));
	})
	.on('click','.add-cfdi-concept',function()
	{
		if(add)
		{
			id			= Date.now();
			product		= $('#cfdi-product-id').val();
			productText	= $('#cfdi-product-id option:selected').text();
			unity		= $('#cfdi-unity-id').val();
			unityText	= $('#cfdi-unity-id option:selected').text();
			@if((isset($bill) && $bill->version == '4.0') || (!isset($bill) && $cfdi_version == '4_0'))
				taxObject = $('#cfdi-tax-object-id').val();
			@endif
			quantity	= isNaN(Number($('#cfdi-quantity').val())) ? 0 : Number($('#cfdi-quantity').val());
			description	= $('#cfdi-description').val();
			description	= description.replace(/"/g, '&quot;');
			valueCFDI	= isNaN(Number($('#cfdi-value').val())) ? 0 : Number($('#cfdi-value').val());
			total		= $('#cfdi-total').val();
			discount	= isNaN(Number($('#cfdi-discount').val())) ? 0 : Number($('#cfdi-discount').val()).toFixed(6);
			if(quantity == 0 || valueCFDI == 0)
			{
				swal('','La cantidad y el valor unitario no puede ser cero.','error');
			}
			else if(product == '' || unity=='' || quantity=='' || description =='' || valueCFDI=='' || discount=='' @if((isset($bill) && $bill->version == '4.0') || (!isset($bill) && $cfdi_version == '4_0')) || taxObject == '' @endif )
			{
				swal('','Por favor complete los datos del producto.','error');
			}
			else if((Number(discount) > total) && Number(discount)!='')
			{
				swal('','El descuento debe ser menor o igual al importe.','error');
			}
			@if((isset($bill) && $bill->version == '4.0') || (!isset($bill) && $cfdi_version == '4_0'))
				else if(taxObject == '02' && $('#CFDI_TAXES .tr_tax_taxes').length == 0)
				{
					swal('','Debe agregar al menos un impuesto ya que su concepto es «objeto de impuesto».','error');
				}
			@endif
			else
			{
				nextStep	= true;
				$('.value-tax_fee').each(function(i,v)
				{
					if($(this).parents('.tr_tax_taxes').find('.fee').val() != 'Exento' && $(this).val()=='')
					{
						nextStep	= false;
					}
				});
				$('.total-tax').each(function(i,v)
				{
					if($(this).parents('.tr_tax_taxes').find('.fee').val() != 'Exento' && $(this).val()=='')
					{
						nextStep	= false;
					}
				});
				if(nextStep)
				{
					@php
						$taxesRetention = [];
						$Retention 		= [];
						$taxesTransfer	= [];
						$Transfer 		= [];
						$modelBody		= [];
						$body			= [];
						$modelHead		= [
							["value" => "Clave de producto o servicio"],
							["value" => "Clave de unidad"]
						];
						if((isset($bill) && $bill->version == '4.0') || (!isset($bill) && $cfdi_version == '4_0'))
						{
							array_push($modelHead,["value" => "Objeto de impuesto"]);
						}
						array_push($modelHead,["value" => "Cantidad"]);
						array_push($modelHead,["value" => "Descripción"]);
						array_push($modelHead,["value" => "Valor unitario"]);
						array_push($modelHead,["value" => "Importe"]);
						array_push($modelHead,["value" => "Descuento"]);
						array_push($modelHead,["value" => "Acciones"]);
						$body = 
						[
							[
								"content" =>
								[
									[ "kind"	=> "components.labels.label",		"classEx"		=> "class-product-id"],
									[ "kind"	=> "components.inputs.input-text",	"attributeEx"	=> "name=\"product_id[]\" type=\"hidden\" readonly" ],
									[ "kind"	=> "components.inputs.input-text",	"attributeEx"	=> "name=\"cfdi_item[]\" type=\"hidden\"" ]
								]
							],
							[
								"content" =>
								[
									[ "kind"	=> "components.labels.label",		"classEx"		=> "class-unity-id"],
									[ "kind"	=> "components.inputs.input-text",	"attributeEx"	=> "name=\"unity_id[]\" type=\"hidden\" readonly" ]
								]
							]
						];
						if((isset($bill) && $bill->version == '4.0') || (!isset($bill) && $cfdi_version == '4_0'))
						{
							array_push($body, 
							[
								"content" =>
								[
									[ "kind"	=> "components.labels.label",		"classEx"		=> "class-tax-object-id"],
									[ "kind"	=> "components.inputs.input-text",	"attributeEx"	=> "name=\"tax_object_id[]\" type=\"hidden\" readonly" ]
								]
							]);
						}
						array_push($body, [
							"content" =>
							[
								[ "kind"	=> "components.labels.label",		"classEx"		=> "class-quantity"],
								[ "kind"	=> "components.inputs.input-text",	"attributeEx"	=> "name=\"quantity[]\" type=\"hidden\" readonly"]
							]
						]);
						array_push($body, [
							"content" =>
							[
								[ "kind"	=> "components.labels.label",		"classEx"		=> "class-description"],
								[ "kind"	=> "components.inputs.input-text",	"attributeEx"	=> "name=\"description[]\" type=\"hidden\" readonly"]
							]
						]);
						array_push($body, [
							"content" =>
							[
								[ "kind"	=> "components.labels.label",		"classEx"		=> "class-valueCFDI"],
								[ "kind"	=> "components.inputs.input-text",	"attributeEx"	=> "name=\"valueCFDI[]\" type=\"hidden\" readonly"]
							]
						]);
						array_push($body, [
							"content" =>
							[
								["kind"	=> "components.labels.label",		"classEx"		=> "class-amount"],
								["kind"	=> "components.inputs.input-text",	"attributeEx"	=> "name=\"amount[]\" type=\"hidden\" readonly"]
							]
						]);
						array_push($body, [
							"content" =>
							[
								["kind"	=> "components.labels.label",		"classEx"		=> "class-discount"],
								["kind" => "components.inputs.input-text",	"attributeEx"	=> "name=\"discount[]\" type=\"hidden\" readonly"]
							]
						]);
						array_push($body, [
							"content" => 
							[
								[
									"kind"			=> "components.buttons.button",
									"variant"		=> "red",
									"attributeEx"	=> "type=\"button\"",
									"label"			=> "<span class=\"icon-x\"></span>",
									"classEx"		=> "cfdi-concept-delete"
								],
								[
									"kind"			=> "components.buttons.button",
									"variant"		=> "success",
									"attributeEx"	=> "type=\"button\"",
									"label"			=> "<span class=\"icon-pencil\"></span>",
									"classEx"		=> "cfdi-concept-modify"
								]
							]
						]);
						$modelBody[] = $body;
					@endphp
					retention_bool		= false;
					translation_bool	= false;
					@php
						$Retention = [
							[
								"content" => 
								[
									[ "kind"	=> "components.labels.label",		"classEx"		=> "retText" ],
									[ "kind"	=> "components.inputs.input-text",	"attributeEx"	=> "type=\"hidden\" name=\"ret\"", "classEx" => "retClass" ],
								]
							],
							[
								"content" =>
								[
									[ "kind" => "components.labels.label",		"classEx"		=> "class-ret-fee" ],
									[ "kind" => "components.inputs.input-text",	"attributeEx"	=> "type=\"hidden\" readonly name=\"ret_fee\"", "classEx" => "ret_fet_Class"]
								]
							],
							[
								"content" =>
								[
									[ "kind" => "components.labels.label",		"classEx"		=> "class-ret-tax-fee" ],
									[ "kind" => "components.inputs.input-text", "attributeEx"	=> "type=\"hidden\" readonly name=\"ret_tax_fee\"", "classEx" => "ret_tax_fee_Class"]
								]
							],
							[
								"content" =>
								[
									[ "kind" => "components.labels.label",		"classEx"		=> "class-ret-total-tax" ],
									[ "kind" => "components.inputs.input-text", "attributeEx"	=> "type=\"hidden\" readonly name=\"ret_total_tax\"", "classEx" => "ret_total_tax_Class"]
								]
							]
						];
						$taxesRetention[] = $Retention;
						$Transfer = [
							[
								"content" =>
								[
									[ "kind"	=> "components.labels.label",		"classEx"		=> "trasText" ],
									[ "kind"	=> "components.inputs.input-text",	"attributeEx"	=> "type=\"hidden\" name=\"tras\"", "classEx" => "trasClass" ],
								]
							],
							[
								"content" =>
								[
									[ "kind" => "components.labels.label",		"classEx"		=> "class-tras-fee" ],
									[ "kind" => "components.inputs.input-text",	"attributeEx"	=> "type=\"hidden\"  name=\"tras_fee\"", "classEx" => "tras_fee_Class"]
								]
							],
							[
								"content" =>
								[
									[ "kind" => "components.labels.label",		"classEx"		=> "class-tras-tax-fee" ],
									[ "kind" => "components.inputs.input-text", "attributeEx"	=> "type=\"hidden\"  name=\"tras_tax_fee\"", "classEx" => "tras_tax_fee_Class"]
								]
							],
							[
								"content" =>
								[
									[ "kind"	=> "components.labels.label",		"classEx"		=> "class-tras-total-tax" ],
									[ "kind"	=> "components.inputs.input-text",	"attributeEx"	=> "type=\"hidden\" name=\"tras_total_tax\"", "classEx" => "tras_total_tax_Class"]
								]
							]
						];
						$taxesTransfer[] = $Transfer;
						$transferPHP = view('components.templates.outputs.taxTransfer', [
							"taxesTransfer" => $taxesTransfer
						])->render();
						$transferPHP = html_entity_decode(preg_replace("/(\r)*(\n)*/", "", $transferPHP));

						$retentionPHP = view('components.templates.outputs.taxRetention', [
							"taxesRetention" => $taxesRetention
						])->render();
						$retentionPHP = html_entity_decode(preg_replace("/(\r)*(\n)*/", "", $retentionPHP));
					@endphp
					transferHtml = '{!!preg_replace("/(\r)*(\n)*/", "", $transferPHP)!!}';
					transferTrBlade = $(transferHtml);

					retentionHtml = '{!!preg_replace("/(\r)*(\n)*/", "", $retentionPHP)!!}';
					retentionTrBlade = $(retentionHtml);
					
					retentionTr		= '';
					transferTr		= '';
					$('#CFDI_TAXES_BODY .tr_tax_taxes').each(function(i,v)
					{
						if($(this).find('.tax-select option:selected').val()==1)
						{
							tmpR = retentionTrBlade;
							tmpR.find('[name="ret"]').attr("name",'ret['+id+'][]');
							tmpR.find('[name="ret['+id+'][]"]').attr('value', $(v).find('.tax-name option:selected').val());
							tmpR.find('.retText').text($(v).find('.tax-name option:selected').text());
							tmpR.find('[name="ret_fee"]').attr('name','ret_fee['+id+'][]');
							tmpR.find('[name="ret_fee['+id+'][]"]').attr('value', $(v).find('.fee option:selected').val());
							tmpR.find('.class-ret-fee').text($(v).find('.fee option:selected').val());
							tmpR.find('[name="ret_tax_fee"]').attr('name','ret_tax_fee['+id+'][]');
							tmpR.find('[name="ret_tax_fee['+id+'][]"]').attr('value', $(v).find('.value-tax_fee').val());
							tmpR.find('.class-ret-tax-fee').text('$ '+Number($(v).find('.value-tax_fee').val()).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));
							tmpR.find('[name="ret_total_tax"]').attr('name','ret_total_tax['+id+'][]'); 
							tmpR.find('[name="ret_total_tax['+id+'][]"]').attr('value', $(v).find('.total-tax').val());
							tmpR.find('.class-ret-total-tax').text('$ '+Number($(v).find('.total-tax').val()).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));
							retentionTr += tmpR.prop('outerHTML');
						}
						else
						{
							tmpT = transferTrBlade;
							tmpT.find('[name="tras"]').attr('name','tras['+id+'][]');
							tmpT.find('[name="tras['+id+'][]"]').attr('value', $(v).find('.tax-name option:selected').val());
							tmpT.find('.trasText').text($(v).find('.tax-name option:selected').text());
							tmpT.find('[name="tras_fee"]').attr('name','tras_fee['+id+'][]');
							tmpT.find('[name="tras_fee['+id+'][]"]').attr('value', $(v).find('.fee option:selected').val());
							tmpT.find('.class-tras-fee').text($(v).find('.fee option:selected').val());
							tmpT.find('[name="tras_tax_fee"]').attr('name','tras_tax_fee['+id+'][]');
							tmpT.find('[name="tras_tax_fee['+id+'][]"]').attr('value', $(v).find('.value-tax_fee').val());
							tmpT.find('.class-tras-tax-fee').text('$'+Number($(v).find('.value-tax_fee').val()).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));
							tmpT.find('[name="tras_total_tax"]').attr('name','tras_total_tax['+id+'][]');
							tmpT.find('[name="tras_total_tax['+id+'][]"]').attr('value', $(v).find('.total-tax').val());
							tmpT.find('.class-tras-total-tax').text('$ '+Number($(v).find('.total-tax').val()).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));
							transferTr += tmpT.prop('outerHTML');
						}
					});
					@php
						$table = view('components.tables.table-addTaxes', [
							"modelHead"		=> $modelHead, 
							"modelBody" 	=> $modelBody,
							"classEx"		=> "cfdi-concepts cfdi-concepts-taxes my-4",
							"script"		=> true
						])->render();
						$table = html_entity_decode(preg_replace("/(\r)*(\n)*/", "", $table));
					@endphp
					table = '{!!preg_replace("/(\r)*(\n)*/", "", $table)!!}';
					tr = $(table);

					tr.find(".class-product-id").text(product);
					tr.find("[name='product_id[]']").attr("data-text", productText).val(product);
					tr.find("[name='cfdi_item[]']").val(id);
					tr.find(".class-unity-id").text(unity);
					tr.find("[name='unity_id[]']").attr("data-text", unityText).val(unity);
					@if((isset($bill) && $bill->version == '4.0') || (!isset($bill) && $cfdi_version == '4_0'))
						tr.find(".class-tax-object-id").text(taxObject);
						tr.find("[name='tax_object_id[]']").val(taxObject);
					@endif
					tr.find(".class-quantity").text(quantity);
					tr.find("[name='quantity[]']").val(quantity);
					tr.find(".class-description").text(description);
					tr.find("[name='description[]']").val(description);
					tr.find(".class-valueCFDI").text('$ '+Number(valueCFDI).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));
					tr.find("[name='valueCFDI[]']").val(valueCFDI);
					tr.find(".class-amount").text('$ '+Number(total).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));
					tr.find("[name='amount[]']").val(total);
					tr.find(".class-discount").text('$ '+Number(discount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));
					tr.find("[name='discount[]']").val(discount);

					$('#body_cfdi_taxes').removeClass('hidden');
					$('#body_cfdi_taxes').append(tr);
					$('.retention_tax_body').append(retentionTr);
					$('.transfer_tax_body').append(transferTr);
					($('.retention_tax_body > div').length) > 0 ? $('.retention_tax_body').removeAttr('style') : '';
					($('.transfer_tax_body > div').length) > 0 ? $('.transfer_tax_body').removeAttr('style') : '';
					$('.retention_tax_body').removeClass('retention_tax_body');
					$('.transfer_tax_body').removeClass('transfer_tax_body');
					// $('.cfdi-concepts>tbody').append(tr);
					$('#cfdi-product-id').val(null).trigger('change');
					$('#cfdi-unity-id').val(null).trigger('change');
					@if((isset($bill) && $bill->version == '4.0') || (!isset($bill) && $cfdi_version == '4_0'))
						$('#cfdi-tax-object-id').val(null).trigger('change');
					@endif
					$('#cfdi-quantity').val('');
					$('#cfdi-description').val('');
					$('#cfdi-value').val('');
					$('#cfdi-total').val(0);
					$('#cfdi-discount').val(0);
					$('#CFDI_TAXES_BODY .tr_tax_taxes').remove();
					$('#CFDI_TAXES').hide();
					subtotalGlobal	= 0;
					discountGlobal	= 0;
					retentionGlobal	= 0;
					transferGlobal	= 0;
					$('[name="amount[]"]').each(function(i,v)
					{
						subtotalGlobal += Number($(this).val());
					});
					$('[name="discount[]"]').each(function(i,v)
					{
						discountGlobal += Number($(this).val());
					});
					$('[name^="tras_total_tax"]').each(function(i,v)
					{
						transferGlobal += Number($(this).val());
					});
					$('[name^="ret_total_tax"]').each(function(i,v)
					{
						retentionGlobal += Number($(this).val());
					});
					totalGlobal = Number(Number(subtotalGlobal.toFixed(2)) - Number(discountGlobal.toFixed(2)) + Number(transferGlobal.toFixed(2)) - Number(retentionGlobal.toFixed(2)));
					$('[name="subtotal"]').val(subtotalGlobal.toFixed(2));
					$('[name="discount_cfdi"]').val(discountGlobal.toFixed(2));
					$('[name="tras_total"]').val(transferGlobal.toFixed(2));
					$('[name="ret_total"]').val(retentionGlobal.toFixed(2));
					$('[name="cfdi_total"]').val(totalGlobal.toFixed(2));
					$('.cfdi-concept-modify').removeAttr('disabled');
				}
				else
				{
					swal('','Por favor complete todos los datos de los impuestos','error');
				}
			}
		}
		else
		{
			swal('','Hay un error en sus datos, por favor verifique antes de continuar','error');
		}
	})
	.on('input','#cfdi-quantity',function()
	{
		q		= isNaN(Number($(this).val())) ? 0 : Number($(this).val());
		v		= isNaN(Number($('#cfdi-value').val())) ? 0 : Number($('#cfdi-value').val());
		total	= q * v;
		$('#cfdi-total').val(total);
	})
	.on('input','#cfdi-value',function()
	{
		v		= isNaN(Number($(this).val())) ? 0 : Number($(this).val());
		q		= isNaN(Number($('#cfdi-quantity').val())) ? 0 : Number($('#cfdi-quantity').val());
		total	= q * v;
		$('#cfdi-total').val(total);
	})
	.on('change','.tax-select', function()
	{
		tipoImp		= Number($(this).val());
		impuesto	= $(this).parents('.tr_tax_taxes').find('.tax-name').val();
		factor		= $(this).parents('.tr_tax_taxes').find('.fee').val();
		valor		= $(this).parents('.tr_tax_taxes').find('.value-tax_fee').val()!='' ? Number($(this).parents('.tr_tax_taxes').find('.value-tax_fee').val()) : null;
		tr			= $(this).parents('.tr_tax_taxes');
		if(tipoImp==1)
		{
			$(this).parents('.tr_tax_taxes').find('.fee').val('Tasa').trigger('change');
			$(this).parents('.tr_tax_taxes').find('.fee option[value="Exento"]').prop('disabled',true).trigger('change');
			$(".fee").select2("destroy").select2();
		}
		else
		{
			$(this).parents('.tr_tax_taxes').find('.fee').val('Tasa').trigger('change');
			$(this).parents('.tr_tax_taxes').find('.fee option[value="Exento"]').prop('disabled',false).trigger('change');
			$(".fee").select2("destroy").select2();
		}
		if(impuesto != '' && factor != '' && valor != null)
		{
			rules(tipoImp,impuesto,factor,valor,tr);
		}
	})
	.on('change','.fee',function()
	{
		if($(this).val() == 'Exento')
		{
			$(this).parents('.tr_tax_taxes').find('.value-tax_fee').val('').prop('disabled',true);
			$(this).parents('.tr_tax_taxes').find('.total-tax').val('');
		}
		else
		{
			$(this).parents('.tr_tax_taxes').find('.value-tax_fee').val('').prop('disabled',false);
			tipoImp		= Number($(this).parents('.tr_tax_taxes').find('.tax-select').val());
			impuesto	= $(this).parents('.tr_tax_taxes').find('.tax-name').val();
			factor		= $(this).parents('.tr_tax_taxes').find('.fee').val();
			valor		= $(this).parents('.tr_tax_taxes').find('.value-tax_fee').val()!='' ? Number($(this).parents('.tr_tax_taxes').find('.value-tax_fee').val()) : null;
			tr			= $(this).parents('.tr_tax_taxes');
			if(impuesto != '' && factor != '' && valor != null)
			{
				rules(tipoImp,impuesto,factor,valor,tr);
			}
		}
	})
	.on('change','.tax-name', function()
	{
		impuesto	= $(this).val();
		if(impuesto == '001' || impuesto == '002')
		{
			$(this).parents('.tr_tax_taxes').find('.fee option[value="Cuota"]').prop('disabled',true).trigger('change');
			$(this).parents('.tr_tax_taxes').find('.fee').val('Tasa').trigger('change');
			$(".fee").select2("destroy").select2();
		}
		else
		{
			$(this).parents('.tr_tax_taxes').find('.fee option[value="Cuota"]').prop('disabled',false).trigger('change');
			$(".fee").select2("destroy").select2();
		}
		tipoImp		= Number($(this).parents('.tr_tax_taxes').find('.tax-select').val());
		factor		= $(this).parents('.tr_tax_taxes').find('.fee').val();
		valor		= $(this).parents('.tr_tax_taxes').find('.value-tax_fee').val()!='' ? Number($(this).parents('.tr_tax_taxes').find('.value-tax_fee').val()) : null;
		tr			= $(this).parents('.tr_tax_taxes');
		if(impuesto != '' && factor != '' && valor != null)
		{
			rules(tipoImp,impuesto,factor,valor,tr);
		}
	})
	.on('focusout','.value-tax_fee',function()
	{
		tipoImp		= Number($(this).parents('.tr_tax_taxes').find('.tax-select').val());
		impuesto	= $(this).parents('.tr_tax_taxes').find('.tax-name').val();
		factor		= $(this).parents('.tr_tax_taxes').find('.fee').val();
		valor		= $(this).val()!='' ? Number($(this).val()) : null;
		tr			= $(this).parents('.tr_tax_taxes');
		if(impuesto != '' && factor != '')
		{
			rules(tipoImp,impuesto,factor,valor,tr);
		}
	})
	.on('focusout','#cfdi-value,#cfdi-quantity',function()
		{
			v		= isNaN(Number($('#cfdi-value').val())) ? 0 : Number($('#cfdi-value').val());
			q		= isNaN(Number($('#cfdi-quantity').val())) ? 0 : Number($('#cfdi-quantity').val());
			total	= q * v;
			$('#cfdi-total').val(total);
		})
	.on('change','[name="cfdi_payment_way"]',function()
	{
		if($(this).val()=='99')
		{
			$('[name="cfdi_payment_method"]').val('PPD');
		}
		else
		{
			$('[name="cfdi_payment_method"]').val('PUE');
		}
	})
	.on('change','[name="cfdi_payment_method"]',function()
	{
		if($(this).val()=='PUE' && $('[name="cfdi_payment_way"]').val()=='99')
		{
			$('[name="cfdi_payment_way"]').val('01');
		}
		else if($(this).val()=='PPD' && $('[name="cfdi_payment_way"]').val()!='99')
		{
			$('[name="cfdi_payment_way"]').val('99');
		}
	})
	.on('input','#cfdi-quantity,#cfdi-value,#cfdi-discount',function(i, v)
	{		
		$('#CFDI_TAXES .tr_tax_taxes').each(function(i,v)
		{
			tipoImp		= $(this).find('.tax-select').val();
			impuesto	= $(this).find('.tax-name').val();
			factor		= $(this).find('.fee').val();
			valor		= $(this).find('.value-tax_fee').val();
			rules(tipoImp,impuesto,factor,valor,$(this))
		});
	})
	.on('change','[name="send_email_cfdi"]',function()
	{
		if($(this).is(':checked'))
		{
			@php
				$buttonAdd = view("components.buttons.button",
				[
					"variant"		=>"warning",
					"attributeEx"	=>"type=\"button\"",
					"classEx"		=>"add-email-cfdi mb-1",
					"slot"			=>"<span class=\"icon-plus\"></span> <span>Agregar email</span>",
				])->render();
				$inputAdd = view("components.inputs.input-text",
				[
					"attributeEx" => "type=\"email\" data-validation=\"required email\" name=\"email_cfdi[]\" placeholder=\"Ingrese el email\""
				])->render();
			@endphp
			buttonAdd 	= '{!!preg_replace("/(\r)*(\n)*/", "", $buttonAdd)!!}';
			button		= $(buttonAdd);
			input		= '{!!preg_replace("/(\r)*(\n)*/", "", $inputAdd)!!}';
			inputAdd	= $(input).prop('outerHTML');
			$(this).parents('.modules').append(button);
			$(this).parents('.modules').append('<div class="class-email mb-1 email-cfdi">'+inputAdd+'</div>');
			formValidate();
		}
		else
		{
			$('.add-email-cfdi,.email-cfdi').remove();
			form = $(this).parent('li').parent('ul').parent('div').parent('form');
			form[0].reset();
		}
	})
	.on('click','.add-email-cfdi',function()
	{
		@php
			$input= view("components.inputs.input-text",
			[
				"attributeEx"=>"type=\"email\" data-validation=\"required email\" name=\"email_cfdi[]\" placeholder=\"Ingrese el email\""
			])->render();
			$button = view("components.buttons.button",
			[
				"variant"		=>"red",
				"attributeEx"	=>"type=\"button\"",
				"classEx"		=>"delete-email-cfdi",
				"slot"			=>"<span class=\"icon-x\"></span>",
			])->render();
		@endphp
		input	= '{!!preg_replace("/(\r)*(\n)*/", "", $input)!!}';
		input	= $(input).prop('outerHTML');
		button	= '{!!preg_replace("/(\r)*(\n)*/", "", $button)!!}';
		button	= $(button).prop('outerHTML');
		$(this).parents('.modules').append('<div class="class-email flex mb-1 email-cfdi"><div class="w-full">'+input+'</div><div>'+button+'</div></div>');
		formValidate();
	})
	.on('change','#related_cfdi',function()
	{
		if($(this).is(':checked'))
		{
			$('.add-related').prop('disabled',false);
		}
		else
		{
			$('.add-related').prop('disabled',true);
			$('.related-cfdi-container').html('');
			$('.related-payments').html('');
		}
	})
	.on('click','.delete-email-cfdi',function()
	{
		$(this).parents('.class-email').remove();
		formValidate();
	})
	.on('change','#enterprise_selector',function()
	{
		rfc				= $(this).val();
		businessName	= $(this).find('option:selected').text();
		taxRegime		= $(this).find('option:selected').attr('data-tax-regime');
		$('[name="business_name_emitter"]').val(businessName);
		$('[name="rfc_emitter"]').val(rfc);
		$('[name="tax_regime_cfdi"]').val(taxRegime);
	})
	.on('input','[name="rfc_receiver"]',function()
	{
		m = p = '';
		@if((isset($bill) && $bill->version == '4.0') || (!isset($bill) && $cfdi_version == '4_0'))
			m_reg = p_reg = '';
			@foreach(App\CatTaxRegime::orderName()->where('moral','Sí')->get() as $m)
				m_reg += '<option value="{{$m->taxRegime}}" selected>{{$m->taxRegime}} - {{$m->description}}</option>';
			@endforeach
			@foreach(App\CatTaxRegime::orderName()->where('physical','Sí')->get() as $p)
				p_reg += '<option value="{{$p->taxRegime}}" selected>{{$p->taxRegime}} - {{$p->description}}</option>';
			@endforeach
		@endif
		@foreach(App\CatUseVoucher::orderName()->where('moral','Sí')->get() as $m)
			m += '<option value="{{$m->useVoucher}}" selected>{{$m->useVoucher}} - {{$m->description}}</option>';
		@endforeach
		@foreach(App\CatUseVoucher::orderName()->where('physical','Sí')->get() as $p)
			p += '<option value="{{$p->useVoucher}}" selected>{{$p->useVoucher}} - {{$p->description}}</option>';
		@endforeach
		if($(this).val().length == 12)
		{
			@if((isset($bill) && $bill->version == '4.0') || (!isset($bill) && $cfdi_version == '4_0'))
				$('#regime_receiver').html(m_reg).trigger('change');
			@endif
			$('[name="cfdi_use"]').html(m).trigger('change');
		}
		else if($(this).val().length == 13)
		{
			@if((isset($bill) && $bill->version == '4.0') || (!isset($bill) && $cfdi_version == '4_0'))
				$('#regime_receiver').html(p_reg).trigger('change');
			@endif
			$('[name="cfdi_use"]').html(p).trigger('change');
		}
		else
		{
			@if((isset($bill) && $bill->version == '4.0') || (!isset($bill) && $cfdi_version == '4_0'))
				$('#regime_receiver').html('').trigger('change');
			@endif
			$('[name="cfdi_use"]').html('').trigger('change');
			$('[name="cfdi_use"]').select2(
			{
				placeholder	: "Seleccione el uso de CFDI",
				language	: 
				{
					noResults: function()
					{
						return "Favor de ingresar el RFC del receptor.";
					}
				}
			});
		}
		if($('[name="cfdi_kind"]').val() == 'P')
		{
			@if((isset($bill) && $bill->version == '4.0') || (!isset($bill) && $cfdi_version == '4_0'))
				$('[name="cfdi_use"]').val('CP01').trigger('change');
			@else
				$('[name="cfdi_use"]').val('P01').trigger('change');
			@endif
		}
	})
	.on('click','.add-cfdi-related',function()
	{
		if($('[name="cfdi_rel[]"]:checked').length>0)
		{
			@if((isset($bill) && $bill->version == '4.0') || (!isset($bill) && $cfdi_version == '4_0'))
				objRel = new Object();
				$('[name="cfdi_rel[]"]:checked').parents('.tr_relation').find('[name="cfdi_rel_kind[]"]').each(function(i,v)
				{
					objRel[$(this).val()]          = new Object();
					objRel[$(this).val()]['data']  = new Object();
					objRel[$(this).val()]['title'] = $(this).find('option:selected').text();
				});
				$('[name="cfdi_rel[]"]').each(function(i,v)
				{
					if($(this).is(':checked'))
					{
						relTemp = $('[name="cfdi_rel_kind[]"]')[i].value;
						objRel[relTemp]['data'][i] = new Object;
						objRel[relTemp]['data'][i]['uuid'] = $(this).attr('data-uuid');
						objRel[relTemp]['data'][i]['id'] = $(this).val();
					}
				});
				table = '';
				$.each(objRel,function(i,v)
				{
					tr = '';
					$.each(v.data,function(ii,vv)
					{
						tr += '<div class="text-center border border-orange-200 py-2">'+vv.uuid+'<input type="hidden" name="cfdi_related_id[]" value="'+vv.id+'"><input type="hidden" name="cfdi_related_kind[]" value="'+i+'"></div>';
					});
					table += '<div class="bg-orange-500 w-full text-white text-center text-lg py-2">'+v.title+'</div></div>'+tr+'</div>';
				});
			@else
				tr = '';
				$('[name="cfdi_rel[]"]:checked').each(function(i,v)
				{
					tr += '<div class="text-center border border-orange-200 py-2">'+$(this).attr('data-uuid')+'<input type="hidden" name="cfdi_related_id[]" value="'+$(this).val()+'"></div>';
				});
				table = '<div class="bg-orange-500 w-full text-white text-center text-lg py-2">'+$('#cfdi_relation_kind option:selected').text()+'<input type="hidden" name="related_kind_cfdi" value="'+$('#cfdi_relation_kind').val()+'"></div><div>'+tr+'</div>';
			@endif
			$('.related-cfdi-container').html(table);
			if($('[name="cfdi_kind"]').val() == 'P')
			{
				tr2					= '';
				trTable				= '';
				$('.related-payments').html('');
				$('[name="cfdi_rel[]"]:checked').each(function(i,v)
				{
					@if((isset($bill) && $bill->version == '4.0') || (!isset($bill) && $cfdi_version == '4_0'))
						valueUUID 			= $(this).attr('data-uuid');
						valueCFDIPayment	= $(this).val();
						valueSerie			= $(this).attr('data-serie');
						valueFolio			= $(this).attr('data-folio');
						valueCurrency		= $(this).attr('data-currency');
						valuePaymentMethod	= $(this).attr('data-payment-method');

						@php
							$body		= [];
							$modelBody	= [];
							$modelHead	= [
								[
									["value" => "UUID"],
									["value" => "Serie"],
									["value" => "Folio"],
									["value" => "Moneda"],
									["value" => "Método de pago"],
									["value" => "Número de parcialidad"],
									["value" => "Importe de saldo anterior"],
									["value" => "Importe pagado"],
									["value" => "Importe de saldo insoluto"],
									["value" => "Objeto de impuesto"],
									["value" => "Agregar impuestos"]
								]
							];

							$optionPayObj = [];
							foreach(App\CatTaxObject::all() as $obj)
							{
								$optionPayObj[] = ["value" => $obj->id, "description" => $obj->id.' '.$obj->description];
							}

							$body = [ "classEx" => "tr_related_payments",
								[
									"content"	=> 
									[
										[
											"kind" 		=> "components.labels.label",
											"classEx"	=> "class_UUID"
										],
										[
											"kind" 			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"hidden\" name=\"cfdi_payment_related_id[]\"",
											"classEx"		=> "class_CFDIPayment"
										]
									]
								],
								[
									"content" =>
									[
										"kind" 		=> "components.labels.label",
										"classEx"	=> "class_serie"
									]
								],
								[
									"content" =>
									[
										"kind"		=> "components.labels.label",
										"classEx"	=> "class_folio"
									]
								],
								[
									"content" =>
									[
										"kind" => "components.labels.label",
										"classEx" => "class_currency"
									]
								],
								[
									"content" =>
									[
										"kind"		=> "components.labels.label",
										"classEx"	=> "class_payment"
									]
								],
								[
									"content" => 
									[
										"kind" 			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"text\" name=\"cfdi_payment_partial_number[]\" placeholder=\"Ingrese un número de parcialidad\" data-validation=\"number\"",
										"classEx"		=> "class_payment_partial"
									]
								],
								[
									"content" => 
									[
										"kind" 			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"text\" name=\"cfdi_payment_last_amount[]\" placeholder=\"Ingrese el importe de saldo anterior\" data-validation=\"number\" data-validation-allowing=\"float\"",
										"classEx"		=> "class_payment_last"
									]
								],
								[
									"content" => 
									[
										"kind" 			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"text\" name=\"cfdi_payment_comp_amount[]\" placeholder=\"Ingrese el importe pagado\" data-validation=\"number\" data-validation-allowing=\"float\"",
										"classEx"		=> "class_payment_comp"
									]
								],
								[
									"content" => 
									[
										"kind" 			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"text\" name=\"cfdi_payment_insolute[]\" placeholder=\"Ingrese el importe de saldo insoluto\" data-validation=\"number\" data-validation-allowing=\"float\"",
										"classEx"		=> "class_payment_insolute"
									]
								],
								[
									"content" =>
									[
										"kind"			=> "components.inputs.select",
										"attributeEx"	=> "name=\"cfdi_payment_objeto_imp[]\" multiple data-validation=\"required\"",
										"options"		=> $optionPayObj
									]
								],
								[
									"content" =>
									[
										[
											"kind" 			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"hidden\" name=\"cfdi_payment_related_taxes[]\" value=\"\"",
										],
										[
											"kind"			=> "components.buttons.button",
											"variant"		=> "warning",
											"attributeEx"	=> "type=\"button\"",
											"classEx"		=> "add-payment-taxes hidden",
											"label"			=> "<span class=\"icon-plus\"></span>"
										]
									]
								]
							];
							$modelBody[] = $body;
							$table = view("components.tables.table",[
								"modelHead" 	=> $modelHead,
								"modelBody" 	=> $modelBody,
								"classExBody"	=> "related-payments",
								"noHead"		=> "true"
							])->render();
							$table = html_entity_decode(preg_replace("/(\r)*(\n)*/", "", $table));
						@endphp	
						trTable = '{!!preg_replace("/(\r)*(\n)*/", "", $table)!!}';
						tr2 = $(trTable);
						tr2.find('.class_UUID').text(valueUUID);
						tr2.find('.class_CFDIPayment').val(valueCFDIPayment);
						tr2.find('.class_serie').text(valueSerie);
						tr2.find('.class_folio').text(valueFolio);
						tr2.find('.class_currency').text(valueCurrency);
						tr2.find('.class_payment').text(valuePaymentMethod);			
						$('.related-payments').append(tr2);		
					@else
						valueUUID 			= $(this).attr('data-uuid');
						valueCFDIPayment	= $(this).val();
						valueSerie			= $(this).attr('data-serie');
						valueFolio			= $(this).attr('data-folio');
						valueCurrency		= $(this).attr('data-currency');
						valuePaymentMethod	= $(this).attr('data-payment-method');

						@php
							$body 		= [];
							$modelBody 	= [];
							$modelHead	= [
								[
									["value" => "UUID"],
									["value" => "Serie"],
									["value" => "Folio"],
									["value" => "Moneda"],
									["value" => "Método de pago"],
									["value" => "Número de parcialidad"],
									["value" => "Importe de saldo anterior"],
									["value" => "Importe pagado"],
									["value" => "Importe de saldo insoluto"]
								]
							];

							$body = [
								[
									"content" =>
									[
										[
											"kind" 		=> "components.labels.label",
											"classEx"	=> "class_UUID"
										],
										[
											"kind"			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"hidden\" name=\"cfdi_payment_related_id[]\"",
											"classEx"		=> "class_CFDIPayment"
										]
									]
								],
								[
									"content" =>
									[
										"kind" 		=> "components.labels.label",
										"classEx"	=> "class_serie"
									]
								],
								[
									"content" =>
									[
										"kind" 		=> "components.labels.label",
										"classEx"	=> "class_folio"
									]
								],
								[
									"content" =>
									[
										"kind"		=> "components.labels.label",
										"classEx"	=> "class_currency"
									]
								],
								[
									"content" =>
									[
										"kind"		=> "components.labels.label",
										"classEx"	=> "class_payment"
									]
								],
								[
									"content" => 
									[
										"kind" 			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"text\" name=\"cfdi_payment_partial_number[]\" data-validation=\"number\" data-validation-optional=\"true\"",
										"classEx"		=> "class_payment_partial"
									]
								],
								[
									"content" => 
									[
										"kind" 			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"text\" name=\"cfdi_payment_last_amount[]\" data-validation=\"number\" data-validation-allowing=\"float\" data-validation-optional=\"true\"",
										"classEx"		=> "class_payment_last"
									]
								],
								[
									"content" => 
									[
										"kind" 			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"text\" name=\"cfdi_payment_comp_amount[]\" data-validation=\"number\" data-validation-allowing=\"float\" data-validation-optional=\"true\"",
										"classEx"		=> "class_payment_comp"
									]
								],
								[
									"content" => 
									[
										"kind" 			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"text\" name=\"cfdi_payment_insolute[]\" data-validation=\"number\" data-validation-allowing=\"float\" data-validation-optional=\"true\"",
										"classEx"		=> "class_payment_insolute"
									]
								]
							];
							$modelBody[] = $body;
							
							$table = view("components.tables.table",[
								"modelHead" => $modelHead,
								"modelBody" => $modelBody,
								"noHead"	=> "true"
							])->render();
							$table = html_entity_decode(preg_replace("/(\r)*(\n)*/", "", $table));
						@endphp			
						trTable = '{!!preg_replace("/(\r)*(\n)*/", "", $table)!!}';
						tr2 = $(trTable);
						tr2.find('.class_UUID').text(valueUUID);
						tr2.find('.class_CFDIPayment').val(valueCFDIPayment);
						tr2.find('.class_serie').text(valueSerie);
						tr2.find('.class_folio').text(valueFolio);
						tr2.find('.class_currency').text(valueCurrency);
						tr2.find('.class_payment').text(valuePaymentMethod);
						$('.related-payments').append(tr2);			
					@endif
				});
				//$('.related-payments').html(tr2);
				formValidate();
			}
			$(this).parents('.modal').modal('hide');
		}
		else
		{
			swal('','Debe seleccionar al menos un CFDI para relacionar','error');
		}
	})
	.on('change','[name="cfdi_kind"]',function()
	{
		$('[name="related_cfdi"]').prop('checked',false);
		$('.add-related').prop('disabled',true);
		$('.related-cfdi-container').html('');
		$('[name="subtotal"]').val('0.00');
		$('[name="discount_cfdi"]').val('0.00');
		$('[name="tras_total"]').val('0.00');
		$('[name="ret_total"]').val('0.00');
		$('[name="cfdi_total"]').val('0.00');
		@if((isset($bill) && $bill->version == '4.0') || (!isset($bill) && $cfdi_version == '4_0'))
			relation = '';
			$('[name="cfdi_export"]').prop('disabled',false).val('01').trigger('change');
			$('[name="conditions"]').prop('disabled',false).val('');
		@endif
		if($(this).val() == 'P')
		{
			$('.tax-add').hide();
			$('#cfdi-concepts').hide();
			$('[name="cfdi_payment_way"]').prop('disabled',true).val('').trigger('change');
			$('[name="cfdi_payment_method"]').prop('disabled',true).val('').trigger('change');
			$('[name="currency_cfdi"]').prop('disabled',true).val('XXX').trigger('change');
			@if((isset($bill) && $bill->version == '4.0') || (!isset($bill) && $cfdi_version == '4_0'))
				$('[name="cfdi_export"]').prop('disabled',true).val('01').trigger('change');
				$('[name="conditions"]').prop('disabled',false).val('');
				$('[name="cfdi_use"]').prop('disabled',true).val('CP01').trigger('change');
				$('[name="cfdi_payment_currency"]').val('MXN').trigger('change');
				$('[name="cfdi_payment_payment_way"]').val(27).trigger('change');
				@php
					$body		= [];
					$modelBody	= [];
					$modelHead	= ["Clave de producto o servicio","Clave de unidad","Objeto de impuesto","Cantidad","Descripción","Valor unitario","Importe","Descuento","Acción"];
					$body = [
						[ 
							"content" => 
							[
								[ "kind"	=> "components.inputs.input-text", "attributeEx" => "name=\"product_id[]\" type=\"text\" readonly value=\"84111506\""],
								[ "kind"	=> "components.inputs.input-text", "attributeEx" => "name=\"cfdi_item[]\" type=\"hidden\" value=\"1568074175290\""]
							]
						],
						[ "content" => [[ "kind" => "components.inputs.input-text", "attributeEx" => "name=\"unity_id[]\" type=\"text\" readonly value=\"ACT\""]]],
						[ "content" => [[ "kind" => "components.inputs.input-text", "attributeEx" => "name=\"tax_object_id[]\" type=\"text\" readonly value=\"01\""]]],
						[ "content" => [[ "kind" => "components.inputs.input-text", "attributeEx" => "name=\"quantity[]\" type=\"text\" readonly value=\"1\""]]],
						[ "content" => [[ "kind" => "components.inputs.input-text", "attributeEx" => "name=\"description[]\" type=\"text\" readonly value=\"Pago\""]]],
						[ "content" => [[ "kind" => "components.inputs.input-text", "attributeEx" => "name=\"valueCFDI[]\" type=\"text\" readonly value=\"0\""]]],
						[ "content" => [[ "kind" => "components.inputs.input-text", "attributeEx" => "name=\"amount[]\" type=\"text\" readonly value=\"0\""]]],
						[ "content" => [[ "kind" => "components.inputs.input-text", "attributeEx" => "name=\"discount[]\" type=\"text\" readonly value=\"\""]]]
					];
					$modelBody[] = $body;
					$body_concepts = view("components.tables.alwaysVisibleTable",[
						"modelHead" => $modelHead,
						"modelBody" => $modelBody,
					])->render();
					$table = html_entity_decode(preg_replace("/(\r)*(\n)*/", "", $body_concepts));
				@endphp
				table = '{!!preg_replace("/(\r)*(\n)*/", "", $table)!!}';
				row = $(table);
				$('#body_cfdi_concepts').removeClass('hidden');
				$('#body_cfdi_concepts').html(row);				
			@else
				$('[name="cfdi_use"]').prop('disabled',true).val('P01').trigger('change');
				@php
					$body		= [];
					$modelBody	= [];
					$modelHead	= ["Clave de producto o servicio","Clave de unidad","Cantidad","Descripción","Valor unitario","Importe","Descuento","Acción"];
					$body = [
						[ 
							"content" => 
							[
								[ "kind"	=> "components.inputs.input-text", "attributeEx" => "name=\"product_id[]\" type=\"text\" readonly value=\"84111506\""],
								[ "kind"	=> "components.inputs.input-text", "attributeEx" => "name=\"cfdi_item[]\" type=\"hidden\" value=\"1568074175290\""]
							]
						],
						[ "content" => [[ "kind" => "components.inputs.input-text", "attributeEx" => "name=\"unity_id[]\" type=\"text\" readonly value=\"ACT\""]]],
						[ "content" => [[ "kind" => "components.inputs.input-text", "attributeEx" => "name=\"quantity[]\" type=\"text\" readonly value=\"1\""]]],
						[ "content" => [[ "kind" => "components.inputs.input-text", "attributeEx" => "name=\"description[]\" type=\"text\" readonly value=\"Pago\""]]],
						[ "content" => [[ "kind" => "components.inputs.input-text", "attributeEx" => "name=\"valueCFDI[]\" type=\"text\" readonly value=\"0\""]]],
						[ "content" => [[ "kind" => "components.inputs.input-text", "attributeEx" => "name=\"amount[]\" type=\"text\" readonly value=\"0\""]]],
						[ "content" => [[ "kind" => "components.inputs.input-text", "attributeEx" => "name=\"discount[]\" type=\"text\" readonly value=\"\""]]]
					];
					$modelBody[] = $body;
					$body_concepts = view("components.tables.alwaysVisibleTable",[
						"modelHead" => $modelHead,
						"modelBody" => $modelBody,
					])->render();
					$table = html_entity_decode(preg_replace("/(\r)*(\n)*/", "", $body_concepts));
				@endphp
				table = '{!!preg_replace("/(\r)*(\n)*/", "", $table)!!}';
				row = $(table);
				$('#body_cfdi_concepts').removeClass('hidden');
				$('#body_cfdi_concepts').html(row);
				
			@endif
			$('.payments-receipt').show();
			$('[name="exchange"]').prop('disabled',true).val('');
		}
		else if($(this).val() == 'T')
		{
			$('[name="currency_cfdi"]').prop('disabled',true).val('XXX').trigger('change');
			$('[name="exchange"]').prop('disabled',true).val('');
		}
		else
		{
			$('#body_cfdi_concepts').addClass('hidden');
			$('.tax-add').show();
			$('#cfdi-concepts').show();
			$('[name="cfdi_payment_way"]').prop('disabled',false).val('01').trigger('change');
			$('[name="cfdi_payment_method"]').prop('disabled',false).val('PUE').trigger('change');
			$('[name="cfdi_use"]').prop('disabled',false).trigger('change');
			$('.cfdi-concepts-taxes').html('');
			$('related-payments-cfdi').html('');
			$('.payments-receipt').hide();
			$('[name="currency_cfdi"]').prop('disabled',false).val('MXN').trigger('change');
			$('[name="exchange"]').prop('disabled',false).val('');
		}
		formValidate();
	})
	.on('change','[name="currency_cfdi"]',function()
	{
		if($(this).val() != 'MXN')
		{
			$('[name="exchange"]').attr('data-validation','required').trigger('change');
		}
		else
		{
			$('[name="exchange"]').removeAttr('data-validation').trigger('change');
		}
		formValidate();
	})
	.on('click','#save_only',function()
	{
		@if((isset($bill) && $bill->version == '4.0') || (!isset($bill) && $cfdi_version == '4_0'))
			$('[name="cfdi_payment_objeto_imp[]"]').each(function(i,v)
			{
				if($(this).val() == '')
				{
					$(this).parent().html('<input type="hidden" name="cfdi_payment_objeto_imp[]">');
				}
			});
		@endif
		$('#container-factura').submit();
	})
	.on('select2:unselecting','[name="cfdi_kind"]', function (e)
	{
		e.preventDefault();
		swal({
			title		: "",
			text		: "Si cambia el tipo de CFDI, los conceptos agregados serán removidos.\n¿Desea continuar?",
			icon		: "warning",
			buttons		: ["Cancelar","OK"],
			dangerMode	: true,
		})
		.then((willClean) =>
		{
			if(willClean)
			{
				swal(
				{
					icon				: '{{ asset(getenv("LOADING_IMG")) }}',
					button				: false,
					closeOnClickOutside	: false,
					closeOnEsc			: false,
					timer				: 500
				});
				$(this).val(null).trigger('change');
			}
			else
			{
				swal.close();
			}
		});
	});
});

	function rules(tipoImp,impuesto,factor,valor,tr,taxes = '')
	{
		if(tipoImp == 1) //Retención
		{
			if(impuesto == '002' && factor == 'Tasa' && valor >= 0 && valor <= 0.16)
			{
				acceptedTax(valor,tr,taxes);
			}
			else if(impuesto == '003' && factor == 'Tasa' && (valor == 0.265000 || valor == 0.300000 || valor== 0.530000 || valor == 0.500000 || valor == 1.600000 || valor == 0.304000 || valor == 0.250000 || valor == 0.090000 || valor == 0.080000 || valor == 0.070000 || valor == 0.060000))
			{
				acceptedTax(valor,tr,taxes);
			}
			else if(impuesto == '003' && factor == 'Cuota' && valor <= 55.085200 && valor >= 0.000000)
			{
				acceptedTax(valor,tr,taxes);
			}
			else if(impuesto == '001' && factor == 'Tasa' && valor <= 0.350000 && valor >= 0.000000)
			{
				acceptedTax(valor,tr,taxes);
			}
			else
			{
				notAcceptedTax(tr)
			}
		}
		else //Traslado
		{
			if(impuesto == '002' && factor == 'Tasa' && (valor == 0 || valor == 0.16))
			{
				acceptedTax(valor,tr,taxes);
			}
			else if(impuesto == '002' && factor == 'Tasa' && valor == 0.080000)
			{
				acceptedTax(valor,tr,taxes);
			}
			else if(impuesto == '003' && factor == 'Tasa' && (valor == 0.265000 || valor == 0.300000 || valor== 0.530000 || valor == 0.500000 || valor == 1.600000 || valor == 0.304000 || valor == 0.250000 || valor == 0.090000 || valor == 0.080000 || valor == 0.070000 || valor == 0.060000 || valor == 0.030000 || valor == 0.000000))
			{
				acceptedTax(valor,tr,taxes);
			}
			else if(impuesto == '003' && factor == 'Cuota' && valor <= 55.085200 && valor >= 0.000000)
			{
				acceptedTax(valor,tr,taxes);
			}
			else
			{
				notAcceptedTax(tr)
			}
		}
	}
	function notAcceptedTax(tr)
	{
		tr.addClass('taxError').addClass('bg-red-300');
		swal('','No se encuentra la combinación de impuesto y tipo factor en el catálogo de tasa o cuota','error');
		add	= false;
	}
	function acceptedTax(value,tr,taxes = '')
	{
		if(taxes == '')
		{
			tr.removeClass('taxError').removeClass('bg-red-300');
			total    = Number($('#cfdi-total').val()).toFixed(6);
			discount = Number($('#cfdi-discount').val()).toFixed(6);
			tax      = Number((total-discount) * value).toFixed(6);
			tr.find('.total-tax').val(tax);
			add = true;
		}
		else
		{
			tr.removeClass('taxError').removeClass('bg-red-300');
			tax = Number(Number(tr.find('.payment-base-tax').val()) * value).toFixed(6);
			tr.find('.payment-total-tax').val(tax);
			add = true;
		}
	}
	function formValidate()
	{
		@if((isset($bill) && $bill->version == '4.0') || (!isset($bill) && $cfdi_version == '4_0'))
			@php
				$selects = collect([
					[
						"identificator"          => "#cfdi-tax-object-id",
						"placeholder"            => "Seleccione uno",
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => '[name="cfdi_payment_objeto_imp[]"]',
						"placeholder"            => "Seleccione uno",
						"maximumSelectionLength" => "1"
					]
				]);
			@endphp
			@component('components.scripts.selects', ["selects" => $selects]) @endcomponent
		@endif
		$.validate(
		{
			form	: '#container-factura',
			modules	: 'security',
			onError	: function($form)
			{
				swal("", '{{ Lang::get("messages.form_error") }}', "error");
			},
			onSuccess	: function($form)
			{
				if($('[name="cfdi_kind"]').val() == 'P' && $('[name="cfdi_payment_related_id[]"]').length < 1)
				{
					swal('','Al menos debe ingresar un documento relacionado.','error');
					return false;
				}
				@if((isset($bill) && $bill->version == '4.0') || (!isset($bill) && $cfdi_version == '4_0'))
					else if($('[name="cfdi_kind"]').val() == 'P')
					{
						hasTaxes = true;
						$('.related-payments').each(function(i,v)
						{
							if($(this).find('[name="cfdi_payment_objeto_imp[]"]').val() == '02' && ($(this).find('[name="cfdi_payment_related_taxes[]"]').val() == '[]' || $(this).find('[name="cfdi_payment_related_taxes[]"]').val() == '{}'))
							{
								$(this).attr('style','background-color: #ffe7e7');
								hasTaxes = false;
							}
						});
						if(!hasTaxes)
						{
							swal('','Los documentos marcados son «objeto de impuesto» pero no cuentan con éstos; es necesario agregarlos para continuar.','error');
							return false;
						}
					}
				@endif
				if($('#taxRegime').val() != '')
				{
					if($('.cfdi-concepts-taxes').length<1)
					{
						swal('','Al menos debe ingresar un concepto','error');
						return false;
					}
					else if(Number($('[name="cfdi_total"]').val()) <= 0 && $('[name="cfdi_kind"]').val() != 'P')
					{
						swal('','No pueden timbrarse facturas en cero o total negativo','error');
						return false;
					}
					else
					{
						swal({
							icon 				: '{{ url(getenv('LOADING_IMG')) }}',
							button				: false,
							closeOnEsc			: false,
							closeOnClickOutside	: false,
						});
					}
				}
				else
				{
					swal('','La empresa seleccionada no cuenta con régimen fiscal registrado por lo que no se podrá proceder.','error');
					return false;
				}
			},
		});
	}
</script>
