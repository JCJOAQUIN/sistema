<script type="text/javascript">
	addPartial   = 0;
	totalPartial = parseFloat($('.request_total').val());
	coutEdit     = 0;
	totalPartialPayments();
	$(document).ready(function()
	{
		$(function()
		{
			$('.partialPayment').numeric({ negative : false, altDecimal: ".", decimalPlaces: 2  });
			$("#datepickerPartial").datepicker({ minDate: 0, dateFormat: "dd-mm-yy" });
		});
		@php
			$selects = collect([
				[
					"identificator"          => ".js-partial", 
					"placeholder"            => "Seleccione una opción", 
					"maximumSelectionLength" => "1"
				]
			]);
		@endphp
		$(document).on('click','#addNewPartialPayment',function()
		{
			object = $(this);
			if ($('.path_partial').length > 0) 
			{
				fiscal_folio  = [];
				ticket_number = [];
				timepath      = [];
				amount_ticket = [];
				datepath      = [];
				folio         = "{{ isset($request) ? $request->folio : "" }}";
				$('.path_partial').each(function(i,v)
				{
					fiscal_folio.push($(this).parents('.docs-p').find('.folio_fiscal_partial').val());
					ticket_number.push($(this).parents('.docs-p').find('.num_ticket_partial').val());
					timepath.push($(this).parents('.docs-p').find('.timepath_partial').val());
					amount_ticket.push(Number($(this).parents('.docs-p').find('.monto_partial').val()).toFixed(2));
					datepath.push($(this).parents('.docs-p').find('.datepath_partial').val());
				});
				$.ajax(
				{
					type: 'post',
					url : '{{ route("purchase.validationDocs-partial") }}',
					data: 
					{
						'fiscal_value_partial': fiscal_folio,
						'num_ticket_partial'  : ticket_number,
						'timepath_partial'    : timepath,
						'monto_partial'       : amount_ticket,
						'datepath_partial'    : datepath,
						'folio'               : folio,
					},
					success : function(data)
					{
						$('.num_ticket_partial').removeClass('error').removeClass('valid');
						$('.folio_fiscal_partial').removeClass('error').removeClass('valid');
						$('.timepath_partial').removeClass('error').removeClass('valid');
						$('.monto_partial').removeClass('error').removeClass('valid');
						$('.datepath_partial').removeClass('error').removeClass('valid');
						flag = false;
						$('.path_partial').each(function(j,v)
						{
							ticket_number = $(this).parents('.docs-p').find('.num_ticket_partial');
							fiscal_folio  = $(this).parents('.docs-p').find('.folio_fiscal_partial');
							timepath      = $(this).parents('.docs-p').find('.timepath_partial');
							amount_ticket = $(this).parents('.docs-p').find('.monto_partial');
							datepath      = $(this).parents('.docs-p').find('.datepath_partial');
							ticket_number.removeClass('error').removeClass('valid');
							fiscal_folio.removeClass('error').removeClass('valid');
							timepath.removeClass('error').removeClass('valid');
							amount_ticket.removeClass('error').removeClass('valid');
							datepath.removeClass('error').removeClass('valid');
							$(data).each(function(i,d)
							{
								if (j == d)
								{
									ticket_number.addClass('error')
									fiscal_folio.addClass('error');
									timepath.addClass('error');
									amount_ticket.addClass('error');
									datepath.addClass('error');
									flag = true;
								}
							});
						});
						if (flag) 
						{
							swal('','Los documentos marcados ya se encuentran registrados.','error');
						}
					},
					error : function()
					{
						swal('','Sucedió un error, por favor intente de nuevo.','error');
					}
				})
				.done(function(data)
				{
					if (!flag) 
					{
						addTr(object);
					}
				});
			}
			else
			{
				addTr(object);
			}
			function addTr(object) 
			{
				totalPartialPayments();
				partialTypePayment = $('.partialTypePayment option:selected').val();
				partialPayment     = $('.partialPayment').val();
				partialDate        = $('.partialDate').val();
				totalPurchase      = Number($('.request_total').val());
				@if(isset($request) && $request->paymentsRequest()->exists())
					@php
						$totalPaid = 0;
						foreach ($request->paymentsRequest as $key => $payment) 
						{
							if (!$payment->partialPayments()->exists() && $payment->partial_id == "")
							{
								$totalPaid += $payment->amount;
							}
						}
					@endphp
					totalPaid = {{ $totalPaid }};
				@else
					totalPaid = 0;
				@endif
				$('#typePaymentSpan').removeClass('help-block form-error').hide();
				$('.partialPayment').removeClass('error').removeClass('valid');
				$('.partialDate').removeClass('error').removeClass('valid');
				flag = false;
				if ($('.path_partial').length > 0) 
				{
					$('.path_partial').each(function(i,v)
					{
						pathP	= $(this).val();
						nameP	= $(this).parent('.docs-p').find('.nameDocumentPartial');

						$(this).parent('.docs-p').find('.form-error').remove();
						if ($('option:selected',nameP).val() == undefined || pathP == "") 
						{
							flag = true;
							if ($('option:selected',nameP).val() == undefined) 
							{
								$(this).parent('.docs-p-l').find('.nameDocumentPartial').parent('div').append($('<span class="help-block form-error">Este campo es obligatorio</span>'));
							}
						}
						if (nameP != undefined) 
						{
							t_fiscal_folio	= $(this).parent('.docs-p').find('.folio_fiscal_partial');
							t_ticket_number	= $(this).parent('.docs-p').find('.num_ticket_partial');
							t_amount		= $(this).parent('.docs-p').find('.monto_partial');
							t_timepath		= $(this).parent('.docs-p').find('.timepath_partial');
							t_datepath		= $(this).parent('.docs-p').find('.datepath_partial');
							t_name_doc		= $(this).parent('.docs-p').find('.nameDocumentPartial option:selected').val();
							if (t_name_doc == "Factura") 
							{
								t_fiscal_folio.removeClass('error');
								t_timepath.removeClass('error');
								t_datepath.removeClass('error');
								if(t_fiscal_folio.val() == "" || t_timepath.val() == "" || t_datepath.val() == "")
								{
									cont = false;
									if(t_fiscal_folio.val() == "")
									{
										t_fiscal_folio.addClass('error');
									}
									if(t_timepath.val() == "")
									{
										t_timepath.addClass('error');
									}
									if(t_datepath.val() == "")
									{
										t_datepath.addClass('error');
									}
									flag = true;
								}
							}
							else if (t_name_doc == "Ticket") 
							{
								t_ticket_number.removeClass('error');
								t_timepath.removeClass('error');
								t_datepath.removeClass('error');
								t_amount.removeClass('error');
								if(t_ticket_number.val() == "" || t_timepath.val() == "" || t_datepath.val() == "" || t_amount.val() == "")
								{
									cont = false;
									if(t_ticket_number.val() == "")
									{
										t_ticket_number.addClass('error');
									}
									if(t_timepath.val() == "")
									{
										t_timepath.addClass('error');
									}
									if(t_datepath.val() == "")
									{
										t_datepath.addClass('error');
									}
									if(t_amount.val() == "")
									{
										t_amount.addClass('error');
									}
									flag = true;
								}
							}
						}
					});
				}

				if (partialTypePayment == undefined || partialTypePayment == "" || partialPayment == "" || partialDate == "") 
				{
					if (partialTypePayment == undefined || partialTypePayment == "")
					{
						$('#typePaymentSpan').addClass('help-block form-error').show();
					} 
					if (partialPayment == "") 
					{
						$('.partialPayment').addClass('error');
					}
					if (partialDate == "") 
					{
						$('.partialDate').addClass('error');
					}
					swal("","Todos los campos son obligatorios","error");
				}
				else if (flag) 
				{
					return swal('','Por favor agregue los datos de todos los documentos','info');
				}
				else if($('#body .tr').length == 0)
				{
					swal("","Primero debe ingresar un concepto a la solicitud.","error");
				}
				else if (partialPayment <= 0) 
				{
					$('.partialPayment').addClass('error');
					swal("","El monto no puede ser menor o igual a cero.","error");
				}
				else if(isNaN(partialPayment))
				{
					$('.partialPayment').addClass('error');
					swal("","Ingrese un monto en formato numérico","error");
				}
				else
				{
					if (partialTypePayment == "0") 
					{
						totalPartial = Number((partialPayment*totalPurchase)/100).toFixed(2);
					}
					else
					{
						totalPartial = Number(partialPayment).toFixed(2);
					}
					remainingPayment = Number(Number($('.request_total').val()) - Number($('.partials_total').val()) - Number(totalPaid)).toFixed(2);

					flagDate = false;
					if ($('.partial_date').length > 0) 
					{
						$('.partial_date').each(function()
						{
							if($(this).val() == partialDate && !$(this).parents('.tr').hasClass('partial_select'))
							{
								flagDate = true;
							} 
						});
					}
					if (flagDate) 
					{
						swal("","Las fechas de las parcialidades ingresadas no deben repetirse, por favor verifique la fecha ingresada.","error");
						$('.partialDate').addClass('error');
					}
					else if(Number(totalPartial) > Number(remainingPayment))
					{
						swal("","El total de la parcialidad es mayor al total que se adeuda.","error");
						$('.partialPayment').addClass('error');
					}
					else
					{
						if(object.hasClass('partial_edit_button'))
						{
							tr	= $('.partial_select');
							if(partialTypePayment == "1")
							{
								partialPaymentText = '$'+partialPayment;
							} 
							else
							{
								partialPaymentText = partialPayment+'%';
							} 
							tr.find('.partial_payment').val(partialPayment);
							tr.find('.partial_paymentText').val(partialPaymentText);
							tr.find('.partial_paymentText_label').text(partialPaymentText);
							tr.find('.partial_type').val(partialTypePayment);
							tr.find('.partial_date').val(partialDate);
							tr.find('.partial_date_label').text(partialDate);
							paths = $('<div></div>');
							countPath = tr.find('.partial').val();
							td = $('.docsNew');
							$('.path_partial').each(function(i, v)
							{
								path_p   = $(this).val();
								name_p   = $(this).parents('.docs-p').find('.nameDocumentPartial option:selected').val();
								date_p   = $(this).parents('.docs-p').find('.datepath_partial').val();
								time_p   = $(this).parents('.docs-p').find('.timepath_partial').val();
								folio_p  = $(this).parents('.docs-p').find('.folio_fiscal_partial').val();
								ticket_p = $(this).parents('.docs-p').find('.num_ticket_partial').val();
								monto_p  = $(this).parents('.docs-p').find('.monto_partial').val();
								url = '{{ url('docs/purchase/') }}/'+path_p;
								@php 
									$linkFile = view("components.buttons.button",[
										"buttonElement" => "a",
										"attributeEx"	=> "target=\"_blank\"",
										"variant" => "secondary",
										"label" => "<span class=\"icon-file\"></span>",
									])->render();
									$filesInfo = '<div>'.view('components.labels.label',[	
																"classEx" 	=> "doc-name",														
																"label" 	=> "",
															])->render().
													'</div> <div>'
													.view('components.labels.label',[
																"classEx" 	=> "doc-date",																
																"label" => "",
															])->render().
													'</div>';
								@endphp
								buttonLink = $('{!!preg_replace("/(\r)*(\n)*/", "", $linkFile)!!}');
								buttonLink.attr("href", url);
								buttonLink.attr("title", name_p);
								filesInfo  = $('{!!preg_replace("/(\r)*(\n)*/", "", $filesInfo)!!}');
								filesInfo.find(".doc-name").append(name_p);
								filesInfo.find(".doc-date").append(date_p);

								paths.append($('<div class="nowrap text-left"></div>')
										.append(buttonLink)
										.append(filesInfo)
										.append($('<input type="hidden"	class="path_p"		name="path_p'+countPath+'[]"		value="'+path_p+'">'))
										.append($('<input type="hidden" class="name_p"		name="name_p'+countPath+'[]"		value="'+name_p+'">'))
										.append($('<input type="hidden" class="folio_p" 	name="folio_p'+countPath+'[]"		value="'+folio_p+'">'))
										.append($('<input type="hidden" class="ticket_p"	name="ticket_p'+countPath+'[]"		value="'+ticket_p+'">'))
										.append($('<input type="hidden" class="monto_p"		name="monto_p'+countPath+'[]"		value="'+monto_p+'">'))
										.append($('<input type="hidden" class="timepath_p"	name="timepath_p'+countPath+'[]"	value="'+time_p+'">'))
										.append($('<input type="hidden" class="datepath_p"	name="datepath_p'+countPath+'[]"	value="'+date_p+'">'))
										.append($('<input type="hidden" class="num_p"		name="num_p'+countPath+'[]"			value="1">')));
							});
							tr.find('.contentDocs').empty();
							tr.find('.contentDocs').append(paths);
							$('.trPartial').removeClass('partial_select');
							paymentAmount = 0;
							$('.partialPayment').val('');
							$('.partialDate').val('');
							$('.partial-edit').removeAttr('disabled');
							$('.partial-delete').removeAttr('disabled');
							if (remainingPayment < 0) 
							{
								$('.remainingPayment').text('Verificar datos');
							}
							else if(!isNaN(remainingPayment))
							{
								$('.remainingPayment').text('$'+Number(remainingPayment).toFixed(2));
							}
						}
						else
						{
							@php 
								$modelHead = [
									[
										["value" => "Parcialidad"],
										["value" => "Monto"],
										["value" => "Fecha pago"],
										["value" => "Estado"], 
										["value" => "Documentos"], 
										["value" => "Acción"]
									]
								];
								$modelBody = 
								[
									[
										"classEx" => "trPartial",
										[
											"content" => 
											[
												[
													"kind"    => "components.labels.label",
													"classEx" => "partial_label"
												],
												[
													"kind" 		  => 'components.inputs.input-text',
													"classEx" 	  => 'partial_id',
													"attributeEx" => "name=\"partial_id[]\" type=\"hidden\" value=\"null\""
												],
												[
													"kind" 		  => 'components.inputs.input-text',
													"classEx" 	  => 'partial',
													"attributeEx" => "type=\"hidden\""
												]
											]
										],
										[
											"content" => 
											[
												[
													"kind" 	  => "components.labels.label",
													"classEx" => "partial_paymentText_label"
												],
												[
													"kind" 		  => 'components.inputs.input-text',
													"classEx" 	  => 'partial_payment',
													"attributeEx" => "name=\"partial_payment[]\" type=\"hidden\""
												],
												[
													"kind" 		  => 'components.inputs.input-text',
													"classEx" 	  => 'partial_type',
													"attributeEx" => "name=\"partial_type[]\" type=\"hidden\""
												]
											]
										],
										[
											"content" => 
											[ 
												[
													"kind"    => 'components.labels.label',
													"classEx" => 'partial_date_label'
												],
												[
													"kind" 		  => 'components.inputs.input-text',
													"classEx" 	  => 'partial_date',
													"attributeEx" => "type=\"hidden\" name=\"partial_date[]\""
												]
											]
										],
										[
											"content" => 
											[ 
												[
													"kind"    => "components.labels.label",
													"classEx" => "partial_stateText",
													"label"	  => "Sin pagar"
												],
												[
													"kind"		  => 'components.inputs.input-text',
													"classEx" 	  => 'partial_state',
													"attributeEx" => "type=\"hidden\" value=\"0\""
												]
											]
										],
										[
											"classEx" => "contentDocs",
											"content" =>
											[
												"label" => ""
											]
										],
										[
											"content" =>
											[
												[
													"kind" => 'components.buttons.button',
													"label" => '<span class="icon-pencil"></span>',
													"classEx" => 'partial-edit follow-btn-read',
													"attributeEx" => "alt=\"Editar programa de pago\" title=\"Editar programa de pago\" type=\"button\"",
													"variant" => "success"
												],
												[
													"kind" => 'components.buttons.button',
													"label" => '<span class="icon-x"></span>',
													"classEx" => 'partial-delete follow-btn',
													"attributeEX" => "title=\"Suspender\" type=\"button\"",
													"variant" => "red"
												]
											]
										]
									]
								];
								$table = view('components.tables.table',[
									"modelHead" => $modelHead,
									"modelBody" => $modelBody,
									"themeBody" => "striped",
									"noHead"	=> "true"
								])->render();
								$table = html_entity_decode(preg_replace("/(\r)*(\n)*/", "", $table));
							@endphp
							table = '{!!preg_replace("/(\r)*(\n)*/", "", $table)!!}';
							row = $(table);
							
							countPath	= $('.trPartial').length+1;
							$('.path_partial').each(function(i, v)
							{
								path_p   = $(this).val();
								name_p   = $(this).parents('.docs-p').find('.nameDocumentPartial option:selected').val();
								date_p   = $(this).parents('.docs-p').find('.datepath_partial').val();
								time_p   = $(this).parents('.docs-p').find('.timepath_partial').val();
								folio_p  = $(this).parents('.docs-p').find('.folio_fiscal_partial').val();
								ticket_p = $(this).parents('.docs-p').find('.num_ticket_partial').val();
								monto_p  = $(this).parents('.docs-p').find('.monto_partial').val();
								url      = '{{ url('docs/purchase/') }}/'+path_p;
								@php 
									$linkFile = view("components.buttons.button",[
										"buttonElement" => "a",
										"attributeEx"	=> "target=\"_blank\"",
										"variant" => "secondary",
										"label" => "<span class=\"icon-file\"></span>",
									])->render();
									$filesInfo = '<div>'.view('components.labels.label',[	
																"classEx" 	=> "doc-name",														
																"label" 	=> "",
															])->render().
													'</div> <div>'
													.view('components.labels.label',[
																"classEx" 	=> "doc-date",																
																"label" => "",
															])->render().
													'</div>';
								@endphp
								buttonLink = $('{!!preg_replace("/(\r)*(\n)*/", "", $linkFile)!!}');
								buttonLink.attr("href", url);
								buttonLink.attr("title", name_p);
								filesInfo  = $('{!!preg_replace("/(\r)*(\n)*/", "", $filesInfo)!!}');
								filesInfo.find(".doc-name").append(name_p);
								filesInfo.find(".doc-date").append(date_p);

								row.find('.contentDocs').append($('<div class="nowrap"></div>').append(buttonLink)
										.append(filesInfo)
										.append($('<input type="hidden"	class="path_p"		name="path_p'+countPath+'[]"		value="'+path_p+'">'))
										.append($('<input type="hidden" class="name_p"		name="name_p'+countPath+'[]"		value="'+name_p+'">'))
										.append($('<input type="hidden" class="folio_p" 	name="folio_p'+countPath+'[]"		value="'+folio_p+'">'))
										.append($('<input type="hidden" class="ticket_p"	name="ticket_p'+countPath+'[]"		value="'+ticket_p+'">'))
										.append($('<input type="hidden" class="monto_p"		name="monto_p'+countPath+'[]"		value="'+monto_p+'">'))
										.append($('<input type="hidden" class="timepath_p"	name="timepath_p'+countPath+'[]"	value="'+time_p+'">'))
										.append($('<input type="hidden" class="datepath_p"	name="datepath_p'+countPath+'[]"	value="'+date_p+'">'))
										.append($('<input type="hidden" class="num_p"		name="num_p'+countPath+'[]"			value="1">')));
							});
							if(partialTypePayment == "1")
							{
								partialPaymentText = '$'+partialPayment;
							} 
							else
							{
								partialPaymentText = partialPayment+'%';
							}
							row.find('.partial_label').append($('.trPartial').length+1);
							row.find('.partial').val($('.trPartial').length+1);
							row.find('[name="partial_payment[]"]').val(partialPayment);
							row.find('[name="partial_type[]"]').val(partialTypePayment);
							row.find('.partial_paymentText_label').append(partialPaymentText);
							row.find('[name="partial_date[]"]').val(partialDate);	
							row.find('.partial_date_label').text(partialDate);
							$('#bodyPartial').append(row);
							$('.documents_partial').empty();
							$('.partialPayment,.partialTypePayment,.partialDate').removeClass('error valid');
							$('#typePaymentSpan').removeClass('help-block form-error').hide();
							$('.partialPayment').val('');
							$('.partialDate').val('');
							$('.partialTypePayment').val('').trigger('change');
							$('.partial-edit').removeAttr('disabled');
							$('.partial-delete').removeAttr('disabled');
						}
						disableEdition();
						totalPartialPayments();
					}
				}
			}
			$(".not-found-payments").removeClass("hidden");
		})
		.on('change','.partialPayment',function()
		{
			partialTypePayment = $('.partialTypePayment option:selected').val();
			if (partialTypePayment == '0') 
			{
				if ($(this).val() > 100) 
				{
					swal("","El porcentaje no puede ser mayor a 100.","error");
					$(this).addClass('error');
					$(this).val('');
				}
			}
		})
		.on('change','.partialTypePayment',function()
		{
			$('.partialPayment').val('');
		})
		.on('click','.partial-edit',function()
		{
			disableEdition();
			totalPartialPayments();
			if($(this).parents('.trPartial').hasClass('partial_select'))
			{
				$('.trPartial').removeClass('partial_select');
				paymentAmount = 0;
			}
			else
			{ 
				$('.trPartial').removeClass('partial_select');
				totalPartialPayments();
				tr = $(this).parents('.trPartial');
				tr.addClass('partial_select');
				tr.addClass('marktr');
				payment = tr.find('.partial_payment').val();
				type    = tr.find('.partial_type').val();
				date    = tr.find('.partial_date').val();
				$('.partialTypePayment').val(parseInt(type)).trigger('change');
				paymentAmount = parseFloat(payment);
				if(!isNaN(paymentAmount))
				{
					$('.partialPayment').val(paymentAmount);
				}
				$('.partialDate').val(date);
				$('#addNewPartialPayment').addClass('partial_edit_button');
			}
			count      = $(this).parents('.trPartial').find('.partial').val();
			countPaths = count;
			$(this).parents('.trPartial').find('.nowrap').each(function(i, v)
			{
				path_p         = $(this).find('[name="path_p'+countPaths+'[]"]').val();
				name_p         = $(this).find('[name="name_p'+countPaths+'[]"]').val();
				folio_p        = $(this).find('[name="folio_p'+countPaths+'[]"]').val();
				ticket_p       = $(this).find('[name="ticket_p'+countPaths+'[]"]').val();
				monto_p        = $(this).find('[name="monto_p'+countPaths+'[]"]').val();
				time_p         = $(this).find('[name="timepath_p'+countPaths+'[]"]').val();
				date_p         = $(this).find('[name="datepath_p'+countPaths+'[]"]').val();
				nameFactura    = '';
				nameTicket     = '';
				displayFactura = '';
				displayTicket  = '';
				if(name_p == 'Factura')
				{
					nameFactura    = 'Selected';
					displayFactura = 'block';
					displayTicket  = 'hidden';
				}
				if(name_p == 'Ticket')
				{
					nameTicket     = 'Selected';
					displayFactura = 'hidden';
					displayTicket  = 'block';
				}
				if(path_p != "")
				{
					@php
						$select = view('components.labels.label',[
								"label"		 => "Seleccione el tipo de documento: "
							])->render()
							.view('components.inputs.select',[
								"classEx"	  => "nameDocument nameDocumentPartial",
								"attributeEx" => "name=\"nameDocument[]\"",
							])->render();
						$select = html_entity_decode(preg_replace("/(\r)*(\n)*/", "", $select));
						$newDoc = view('components.documents.upload-files',[	
							"attributeExInput"    	=> "name=\"path\" accept=\".pdf,.jpg,.png\"",
							"classExContainer"		=> "image_success",
							"attributeExRealPath"	=> "name=\"realPath[]\"",
							"componentsExUp"		=> $select,
							"componentsExDown"		=> 
								[
									[
										"kind"    => "components.labels.label", 
										"label"   => "Seleccione la fecha: ",
										"classEx" => "data_datepath"
									],
									[
										"kind"        => "components.inputs.input-text",
										"attributeEx" => "name=\"datepath[]\" step=\"1\" placeholder=\"Ingrese la fecha\" readonly=\"readonly\"",
										"classEx"     => "datepicker datepath_partial",
									],
									[
										"kind" 		 => "components.labels.label", 
										"label"		 => "Seleccione la hora: ",
										"classEx"    => "data_timepath"
									],
									[
										"kind" 		  => "components.inputs.input-text",
										"attributeEx" => "name=\"timepath[]\" step=\"60\" value=\"00:00\" placeholder=\"Seleccione la hora\" readonly=\"readonly\"",
										"classEx"	  => "timepath_partial",
									],
									[
										"kind" 		 => "components.labels.label", 
										"label"		 => "Folio fiscal: ",
										"classEx"    => "data_folio fiscal_folio_label"
									],
									[
										"kind" 		  => "components.inputs.input-text",
										"attributeEx" => "name=\"folio_fiscal[]\" placeholder=\"Ingrese el folio fiscal\" data-validation=\"required\"",
										"classEx"	  => "data_folio folio_fiscal_partial",
									],
									[
										"kind" 		 => "components.labels.label", 
										"label"		 => "Número de ticket: ",
										"classEx"    => "data_ticket"
									],
									[
										"kind" 		  => "components.inputs.input-text",
										"attributeEx" => "name=\"num_ticket[]\" placeholder=\"Ingrese el número de ticket\" data-validation=\"required\"",
										"classEx"	  => "data_ticket num_ticket_partial"
									],
									[
										"kind" 		 => "components.labels.label", 
										"label"		 => "Monto total: ",
										"classEx"    => "data_amount"
									],
									[
										"kind" 		  => "components.inputs.input-text",
										"attributeEx" => "name=\"monto[]\" placeholder=\"Ingrese el monto total\" data-validation=\"required\"",
										"classEx"	  => "data_amount monto_partial",
									]
								],
							"classExInput"    => "pathActioner",
							"classExDelete"   => "delete-doc",
							"classExRealPath" => "path_partial"
						])->render();
					@endphp
					newDoc			= '{!!preg_replace("/(\r)*(\n)*/", "", $newDoc)!!}';
					containerNewDoc = $(newDoc);
					containerNewDoc.find('[name="nameDocument[]"]').append($('<option value="Factura"'+nameFactura+'>Factura</option>'))
						.append($('<option value="Ticket"'+nameTicket+'>Ticket</option>'));
					containerNewDoc.find('.datepath_partial').val(date_p);
					containerNewDoc.find('.timepath_partial').val(time_p);
					containerNewDoc.find('.data_folio').addClass(displayFactura);
					containerNewDoc.find('.folio_fiscal_partial').val(folio_p);
					containerNewDoc.find('.data_ticket').addClass(displayTicket);
					containerNewDoc.find('.num_ticket_partial').val(ticket_p);
					containerNewDoc.find('.data_amount').addClass(displayTicket);
					containerNewDoc.find('.monto_partial').val(monto_p);
					containerNewDoc.find('.path_partial').val(path_p);
					$('.documents_partial').append(containerNewDoc);
				}
				@php
					$selects = collect([
						[
							"identificator"          => ".nameDocument", 
							"placeholder"            => "Seleccione el tipo de documento", 
							"maximumSelectionLength" => "1"
						]
					]);
				@endphp
				@component("components.scripts.selects",["selects" => $selects])
				@endcomponent
			});
			$('[name="monto[]"]').numeric({ altDecimal: ".", decimalPlaces: 2, negative: false });
			$( ".datepicker" ).datepicker({ maxDate: 0, dateFormat: "dd-mm-yy" });
			$('.timepath_partial').daterangepicker({
				timePicker			: true,
				singleDatePicker	: true,
				timePicker24Hour	: true,
				timePickerIncrement	: 1,
				autoApply			: false,
				autoUpdateInput : true,
				locale : 
				{
					format : 'HH:mm',
					"applyLabel": "Seleccionar",
        			"cancelLabel": "Cancelar",
				}
			}).on('show.daterangepicker', function (ev, picker) 
			{
				picker.container.find(".calendar-table").remove();
			});
			$('.partial-edit').attr('disabled','disabled');
			$('.partial-delete').attr('disabled','disabled');
		})
		.on('click','.partial-delete',function()
		{
			idPartial	= $(this).parents('.trPartial').find('.partial_id').val();
			if(idPartial != '')
			{
				$('#partialForms').append($('<input type="hidden" name="delete_partial[]"/>').val(idPartial));
			}
			tr = $(this).parents('.trPartial').remove();
			  
			setTimeout(function()
			{
				$('.trPartial').find('.contentDocs').each(function(i, v)
				{
					$(this).find('.path_p').attr('name','path_p'+(i+1)+'[]');
					$(this).find('.name_p').attr('name','name_p'+(i+1)+'[]');
					$(this).find('.folio_p').attr('name','folio_p'+(i+1)+'[]');
					$(this).find('.ticket_p').attr('name','ticket_p'+(i+1)+'[]');
					$(this).find('.monto_p').attr('name','monto_p'+(i+1)+'[]');
					$(this).find('.timepath_p').attr('name','timepath_p'+(i+1)+'[]');
					$(this).find('.datepath_p').attr('name','datepath_p'+(i+1)+'[]');
					$(this).find('.num_p').attr('name','num_p'+(i+1)+'[]');
				});
				if($('.trPartial').length>0)
				{
					$('.trPartial').each(function(i,v)
					{
						$(this).find('.numPartial').val(i+1);
					});
				}
			},100);
			totalPartialPayments();
		})
		.on('click','#activePaymentProgram',function()
		{
			$('#partialForms').show();
			$('#cancelPaymentProgram').show();
			$(this).hide();
			@php
				$selects = collect([
					[
						"identificator"          => ".js-partial", 
						"placeholder"            => "Seleccione el porcentaje/neto", 
						"maximumSelectionLength" => "1"
					],
				]);
			@endphp
			@component("components.scripts.selects",["selects" => $selects])
			@endcomponent
			$('#programPaymentForm').removeClass('hidden');
		})
		.on('click','#cancelPaymentProgram',function()
		{
			$('#partialForms').hide();
			$('#activePaymentProgram').show();
			$(this).hide();

		})
		.on('click','#addDocPartial',function()
		{
			@php
				$options = collect(
					[
						["value"=>"Factura", "description"=>"Factura"], 
						["value"=>"Ticket", "description"=>"Ticket"]
					]
				);
				$select = view('components.labels.label',[
								"label"		 => "Seleccione el tipo de documento: "
							])->render()
							.view('components.inputs.select',[
								"options"	  => $options,
								"classEx"	  => "nameDocument nameDocumentPartial",
								"attributeEx" => "name=\"nameDocument[]\"",
							])->render();
				$select = html_entity_decode(preg_replace("/(\r)*(\n)*/", "", $select));
				$newDoc = view('components.documents.upload-files',[	
					"attributeExInput"     => "name=\"path\" accept=\".pdf,.jpg,.png\"",
					"attributeExRealPath"  => "name=\"realPath[]\" data-validation=\"required\"",
					"componentsExUp"       => $select,
					"componentsExDown"	   => 
											[
												[
													"kind" 		 => "components.labels.label", 
													"label"		 => "Seleccione la fecha: ",
													"classEx"    => "data_datepath hidden"
												],
												[
													"kind" 			=> "components.inputs.input-text",
													"attributeEx"   => "name=\"datepath[]\" step=\"1\" placeholder=\"Ingrese la fecha\" readonly=\"readonly\"",
													"classEx"		=> "data_datepath hidden datepicker datepath_partial",
												],
												[
													"kind" 		 => "components.labels.label", 
													"label"		 => "Seleccione la hora: ",
													"classEx"    => "data_timepath hidden"
												],
												[
													"kind" 		  => "components.inputs.input-text",
													"attributeEx" => "name=\"timepath[]\" step=\"60\" value=\"00:00\" placeholder=\"Seleccione la hora\" readonly=\"readonly\"",
													"classEx"	  => "data_timepath hidden timepath_partial",
												],
												[
													"kind" 		 => "components.labels.label", 
													"label"		 => "Folio fiscal: ",
													"classEx"    => "hidden data_folio fiscal_folio_label"
												],
												[
													"kind" 		  => "components.inputs.input-text",
													"attributeEx" => "name=\"folio_fiscal[]\" placeholder=\"Ingrese el folio fiscal\" data-validation=\"required\"",
													"classEx"	  => "hidden data_folio folio_fiscal_partial",
												],
												[
													"kind" 		 => "components.labels.label", 
													"label"		 => "Número de ticket: ",
													"classEx"    => "hidden data_ticket"
												],
												[
													"kind" 		  => "components.inputs.input-text",
													"attributeEx" => "name=\"num_ticket[]\" placeholder=\"Ingrese el número de ticket\" data-validation=\"required\"",
													"classEx"	  => "hidden data_ticket num_ticket_partial"
												],
												[
													"kind" 		 => "components.labels.label", 
													"label"		 => "Monto total: ",
													"classEx"    => "hidden data_amount"
												],
												[
													"kind" 		  => "components.inputs.input-text",
													"attributeEx" => "name=\"monto[]\" placeholder=\"Ingrese el monto total\" data-validation=\"required\"",
													"classEx"	  => "hidden data_amount monto_partial",
												]
											],
					"classExInput"         => "pathActioner",
					"classExDelete"        => "delete-doc",
					"classExRealPath"	   => "path_partial"
				])->render();
			@endphp
			newDoc = '{!!preg_replace("/(\r)*(\n)*/", "", $newDoc)!!}';
			containerNewDoc = $(newDoc);
			$('.documents_partial').append(containerNewDoc);
			@php
				$selects = collect([
					[
						"identificator"          => ".nameDocument", 
						"placeholder"            => "Seleccione el tipo de documento", 
						"maximumSelectionLength" => "1"
					]
				]);
			@endphp
			@component("components.scripts.selects",["selects" => $selects])
			@endcomponent
			$('[name="monto[]"]').numeric({ altDecimal: ".", decimalPlaces: 2, negative: false });
			validation();
			
			$( ".datepicker" ).datepicker({ maxDate: 0, dateFormat: "dd-mm-yy" });
			$('.timepath_partial').daterangepicker({
				timePicker			: true,
				singleDatePicker	: true,
				timePicker24Hour	: true,
				timePickerIncrement	: 1,
				autoApply			: false,
				locale : 
				{
					format : 'HH:mm',
					"applyLabel": "Seleccionar",
        			"cancelLabel": "Cancelar",
				}
			}).on('show.daterangepicker', function (ev, picker) 
			{
				picker.container.find(".calendar-table").remove();
			});
		})
		.on('change','.folio_fiscal_partial,.num_ticket_partial,.timepath_partial,.monto_partial,.datepath_partial',function()
		{
			const array_folios	= $('.folio_fiscal_partial').serializeArray();
			const array_ticket 	= $('.num_ticket_partial').serializeArray();
			const array_path 	= $('.path_partial').serializeArray();
			folio 				= $(this).parents('.docs-p').find('.folio_fiscal_partial').val().toUpperCase();
			num_ticket 			= $(this).parents('.docs-p').find('.num_ticket_partial').val().toUpperCase();
			timepath 			= $(this).parents('.docs-p').find('.timepath_partial').val();
			monto				= $(this).parents('.docs-p').find('.monto_partial').val();
			datepath 			= $(this).parents('.docs-p').find('.datepath_partial').val();
			object 				= $(this);

			$('.datepath_partial').each(function(i,v)
			{
				row          = 0;
				first_fiscal		= $(this).parents('.docs-p').find('.folio_fiscal_partial');
				first_num_ticket	= $(this).parents('.docs-p').find('.num_ticket_partial');
				first_monto			= $(this).parents('.docs-p').find('.monto_partial');
				first_timepath		= $(this).parents('.docs-p').find('.timepath_partial');
				first_datepath		= $(this).parents('.docs-p').find('.datepath_partial');
				first_name_doc		= $(this).parents('.docs-p').find('.nameDocument option:selected').val();

				$('.datepath_partial').each(function(j,v)
				{
					scnd_fiscal		= $(this).parents('.docs-p').find('.folio_fiscal_partial');
					scnd_num_ticket	= $(this).parents('.docs-p').find('.num_ticket_partial');
					scnd_monto		= $(this).parents('.docs-p').find('.monto_partial');
					scnd_timepath	= $(this).parents('.docs-p').find('.timepath_partial');
					scnd_datepath	= $(this).parents('.docs-p').find('.datepath_partial');
					scnd_name_doc	= $(this).parents('.docs-p').find('.nameDocument option:selected').val();

					if (i!==j) 
					{
						if (first_name_doc == "Factura") 
						{
							if (first_name_doc == scnd_name_doc && first_datepath.val() == scnd_datepath.val() && first_timepath.val() == scnd_timepath.val() && first_fiscal.val().toUpperCase() == scnd_fiscal.val().toUpperCase()) 
							{
								swal('', 'Esta factura ya ha sido registrada en esta solicitud, intenta nuevamente.', 'error');
								scnd_fiscal.val('').removeClass('valid').addClass('error');
								scnd_timepath.val('').removeClass('valid').addClass('error');
								scnd_datepath.val('').removeClass('valid').addClass('error');
								$(this).parents('.docs-p-l').find('span.form-error').remove();
								return;
							}
						}
						if (first_name_doc == "Ticket") 
						{
							if (first_name_doc == scnd_name_doc && first_datepath.val() == scnd_datepath.val() && first_timepath.val() == scnd_timepath.val() && first_num_ticket.val().toUpperCase() == scnd_num_ticket.val().toUpperCase()) 
							{
								swal('', 'Este ticket ya ha sido registrado en esta solicitud, intenta nuevamente.', 'error');
								scnd_num_ticket.val('').addClass('error');
								scnd_timepath.val('').addClass('error');
								scnd_datepath.val('').addClass('error');
								$(this).parents('.docs-p-l').find('span.form-error').remove();
								return;
							}
						}
					}
				});
			});
		});
		$('.request_total').trigger('change');
	});

	function totalPartialPayments()
	{
		totalPurchase	= Number($('.request_total').val());
		totalPartials	= 0;

		@if(isset($request) && $request->paymentsRequest()->exists())
			@php
				$totalPaid = 0;
				foreach ($request->paymentsRequest as $key => $payment) 
				{
					if (!$payment->partialPayments()->exists() && $payment->partial_id == "")
					{
						$totalPaid += $payment->amount;
					}
				}
			@endphp
			totalPaid = {{ $totalPaid }};
		@else
			totalPaid = 0;
		@endif

		if ($('#bodyPartial .tr').length > 0) 
		{
			$('#bodyPartial .tr').each(function(i,v)
			{
				if (!$(this).hasClass('partial_select')) 
				{
					amountPartial	= Number($(this).find('.partial_payment').val());
					type			= $(this).find('.partial_type').val();

					if (type == '1') 
					{
						totalPartials += amountPartial;
					}
					else if(type == '0')
					{
						totalPartials += Number((totalPurchase/100)*amountPartial);
					}
				}
			});
		}

		$('.partials_total').val(Number(totalPartials).toFixed(2));
		$('.partials_total').trigger('change');

		remainingPayment = Number($('.request_total').val()) - Number($('.partials_total').val()) - Number(totalPaid);
		if (remainingPayment < 0) 
		{
			$('.remainingPayment').text('Verificar datos');
		}
		else if(!isNaN(remainingPayment))
		{
			$('.remainingPayment').text('$'+Number(remainingPayment).toFixed(2));
		}
	}
	function disableEdition()
	{
		$('#addNewPartialPayment').removeClass('partial_edit_button');
		$('#bodyPartial').removeClass('partial_select');
		$('#bodyPartial').find('.partial-edit').addClass('follow-btn');
		$('.trPartial').removeClass('marktr');
		$('.partialPayment, .partialTypePayment, .partialDate').removeClass('error valid').removeAttr('data-validation','required');
		$('#typePaymentSpan').removeClass('help-block form-error').hide();
		$('.partialPayment, .partialDate').val('');
		$('.partialTypePayment').val('').trigger('change');
		$('.documents_partial').empty();
	}
</script>
