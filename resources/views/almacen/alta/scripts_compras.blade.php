<script>
	$(document).ready(function() 
	{
		$.validate(
		{
			form	: '#container-alta_compras',
			modules	: 'security',
			onError	: function($form)
			{
				swal('', '{{ Lang::get("messages.form_error") }}', 'error');
			},
			onSuccess	: function($form)
			{
				hasArticle = false;
				$('#table_compras').find('.tr').each(function()
				{
					hasArticle = true;
				});
				if(hasArticle)
				{
					swal({
						closeOnClickOutside: false,
						closeOnEsc         : false,
						icon               : '{{ asset(getenv('LOADING_IMG')) }}',
						button             : false
					});
					return true;
				}
				else
				{
					swal({
						title: "Error",
						text: "Debe agregarse al menos un artículo.",
						icon: "error",
						buttons: 
						{
							confirm: true,
						},
					});
					return false;
				}
			}
		});

		$(document).on('change','.js-category-a',function()
		{
			id				= $(this).parents('.tr-purchases').find('.edit_compras_articulo').val();
			default_account	= $('#account-default-'+id).attr('data-selected');
			$enterprise		= $('select[name="enterprise_id_compras"] option:selected').val();
			if($enterprise)
			{
				if(default_account === 'true')
				{
					$('#account-default-'+id).attr('data-selected','false')
					search_accounts($enterprise,true,'compras',id)
				}
				else
				{
					search_accounts($enterprise,false,'compras',id)
				}
			}
		})
		.on('click','.edit_compras',function (params)
		{
			swal(
			{
				closeOnClickOutside: false,
				closeOnEsc         : false,
				icon               : '{{ asset(getenv('LOADING_IMG')) }}',
				button             : false
			});
			folio = $(this).val();
			$('#request_folio').find('.class-folio').html('Folio: '+folio);
			$('input[name="folio"]').val(folio);
			$.ajax(
			{
				type: 'post',
				url	: '{{ route("warehouse.inventory.search_compras_request") }}',
				data: { 'folio':folio },
				success : function(data)
				{
					swal.close();
					measurements = [
						@foreach(App\CatMeasurementTypes::whereNotNull('type')->get() as $m_types)
							@foreach ($m_types->childrens()->orderBy('child_order','asc')->get() as $child)
								"{{ strtolower($child->description) }}",
							@endforeach
						@endforeach
					]
					$('.js-enterprises_compras').empty();
					$('.js-enterprises_compras').append('<option value=" '+data.enterprise.id+'" selected>'+(data.enterprise.name.length >= 35 ? (data.enterprise.name.substr(0,35) + '...') : data.enterprise.name) +'</option>');
					$('#enterpise_compras').val(data.enterprise.id);
					search_accounts(data.enterprise.id,false,'compras');
					datefDate = moment(data['fDate'],'YYYY/MM/DD HH:mm:ss').format('DD-MM-YYYY');
					$('#datepicker_compras').val(datefDate);
					$('#total_compras').val(data.purchases[0].amount);
					$('#table-return_compras_articulos').html("");				
					data.purchases[0].detail_purchase.forEach(purchase => 
					{
						@php
							$body		= [];
							$modelBody	= [];
							$modelHead	= [
								[
									["value" => "Categoría", "classEx" => "sticky inset-x-0"],
									["value" => "Cuenta", "classEx" => "sticky inset-x-0"],
									["value" => "Código"],
									["value" => "Concepto"],
									["value" => "Detalles"],
									["value" => "Cantidad"],
									["value" => "Dañados"],
									["value" => "Unidad"],
									["value" => "P. unitario"],
									["value" => "IVA"],
									["value" => "Importe"],
									["value" => "Acción"]
								]
							];

							$optionMeasurement = [];
							foreach(App\CatMeasurementTypes::whereNotNull('type')->get() as $m_types)
							{
								foreach ($m_types->childrens()->orderBy('child_order','asc')->get() as $child)
								{
									$optionMeasurement[] = ["value" => $child->id, "description" => $child->description];
								}
							}
							$body = [ "classEx"	=> "tr-purchases",
								[
									"classEx" => "sticky inset-x-0",
									"content"	=>
									[
										[
											"kind"			=> "components.inputs.select",
											"attributeEx"	=> "multiple=\"multiple\" name=\"category_compras\"",
											"classEx"		=> "js-category-a removeselect",
											"options"		=> []
										],
										[
											"kind"			=> "components.inputs.input-text",
											"classEx"		=> "purchase_article",
											"attributeEx"	=> "type=\"hidden\""
										]
									]
								],
								[
									"classEx" => "sticky inset-x-0",
									"content"	=> 
									[
										"kind"			=> "components.inputs.select",
										"attributeEx"	=> "multiple=\"multiple\" name=\"account_compras\"",
										"classEx"		=> "js-account-a removeselect",
										"options"		=> []
									]
								],
								[
									"classEx"	=> "class-code-article",
									"content"	=>
									[
										"label" => ""
									]
								],
								[
									"classEx"	=> "class-description-article",
									"content"	=>
									[
										"label" => ""
									]
								],
								[
									"content"	=>
									[
										"kind"			=> "components.buttons.button",
										"variant"		=> "secondary",
										"attributeEx"	=> "type=\"button\"",
										"classEx"		=> "btn-show-deatils",
										"label"			=> "<span class=\"icon-search\"></span>"
									]
								],
								[
									"classEx"	=> "class-quantity-article",
									"content"	=>
									[
										"label" => ""
									]
								],
								[
									"content"	=>
									[
										"kind"			=> "components.inputs.input-text",
										"classEx"		=> "articulo_damagedd w-40",
										"attributeEx"	=> "placeholder=\"Ingrese una cantidad\""
									]
								],
								[
									"content" =>
									[
										"kind"			=> "components.inputs.select",
										"attributeEx"	=> "multiple=\"multiple\" name=\"measurement_compras\"",
										"classEx"		=> "js-measurement-a removeselect w-40",
										"options"		=> $optionMeasurement
									]
								],
								[
									"classEx"	=> "class-unitPrice-article",
									"content"	=>
									[
										"kind"			=> "components.inputs.input-text",
										"classEx"		=> "articulo_unitPrice",
										"attributeEx"	=> "type=\"hidden\""
									]
								],
								[
									"classEx"	=> "class-tax-article",
									"content" 	=>
									[
										"label" => ""
									]
								],
								[
									"classEx"	=> "class-amount-article",
									"content" 	=>
									[
										"kind"			=> "components.inputs.input-text",
										"classEx"		=> "articulo_amount",
										"attributeEx"	=> "type=\"hidden\""
									]
								],
								[
									"content"	=>
									[
										"kind"			=> "components.buttons.button",
										"variant"		=> "success",
										"attributeEx"	=> "type=\"button\"",
										"classEx"		=> "edit_compras_articulo",
										"label"			=> "Seleccionar"
									]
								],
							];	
							$modelBody[]	= $body;
							$bodyDetail		= view("components.tables.table",[
								"modelHead"	=> $modelHead,
								"modelBody"	=> $modelBody,
								"noHead"	=> true
							])->render();
						@endphp
						bodyDetail	= '{!!preg_replace("/(\r)*(\n)*/", "", $bodyDetail)!!}';
						table		= $(bodyDetail);
						table.find('.purchase_article').addClass('purchase_'+purchase['idDetailPurchase']);
						table.find('.js-category-a').addClass('js-category-edit-'+purchase['idDetailPurchase']);
						table.find('.js-account-a').addClass('js-account-'+purchase['idDetailPurchase']);
						table.find('.js-account-a').append($("<option data-selected='true' selected value='"+data['account_id']+"' id='account-default-"+purchase['idDetailPurchase']+"'>"+data['account_name']+"</option>"));
						table.find('.class-code-article').append($("<label class='articulo_code'>"+purchase['code']+"</label>"));
						table.find('.class-description-article').children('div').prepend($("<label class='w-40 text-center articulo_description'>"+purchase['description']+"</label>"));
						table.find('.btn-show-deatils').val(purchase['commentaries']);
						table.find('.class-quantity-article').append($("<label class='articulo_quantity'>"+purchase['quantity']+"</label>"));
						table.find('.js-measurement-a').addClass('js-measurement-edit-'+purchase['idDetailPurchase']);
						table.find('.class-unitPrice-article').children('div').prepend($("<label class='w-40 text-center'>"+'$ '+Number(purchase['unitPrice']).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,')+"</label>"));
						table.find('.articulo_unitPrice').val(purchase['unitPrice']);
						table.find('.class-tax-article').children('div').prepend($("<label class='articulo_tax w-40 text-center'>"+'$ '+Number(purchase['tax']).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,')+"</label>"+"<label class='hidden articulo_tax_type'>"+purchase['typeTax']+"</label>"));
						table.find('.articulo_amount').parent().prepend($("<label class='w-40 text-center'>"+'$ '+Number(purchase['amount']).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,')+"</label>"));
						table.find('.articulo_amount').val(purchase['amount']);
						table.find('.edit_compras_articulo').val(purchase['idDetailPurchase']);
						$('#table-return_compras_articulos').append(table);
					});
					if(data.purchases[0].idRequisition > 0)
					{
						$('#tbody_requisition').html('');
						baseRoute ="{{ url('/docs/purchase/') }}";
						$.each(data.purchases[0].documents,function(i,v)
						{
							@php
								$body		= [];
								$modelBody	= [];
								$modelHead	= ["Nombre","Archivo","Fecha"];
								$body = [
									[
										"classEx"	=> "name-doc",
										"content"	=>
										[
											[
												"label" => ""
											]
										]
									],
									[
										"content" =>
										[
											[
												"kind"			=> "components.buttons.button",
												"variant"		=> "secondary",
												"buttonElement"	=> "a",
												"attributeEx"	=> "target=\"_blank\" type=\"button\"",
												"classEx"		=> "btn-doc",
												"label"			=> "Archivo"
											]
										]
									],
									[
										"classEx"	=> "date-doc", 
										"content"	=>
										[
											[
												"label" => ""
											]
										]
									]
								];
								$modelBody[]	= $body;
								$bodyPurchase	= view("components.tables.alwaysVisibleTable",[
									"modelHead"	=> $modelHead,
									"modelBody"	=> $modelBody,
									"noHead"	=> true,
									"variant"	=> "default"
								])->render();
							@endphp
							tableDoc	= '{!!preg_replace("/(\r)*(\n)*/", "", $bodyPurchase)!!}';
							trs			= $(tableDoc);
							trs  		= rowColor('#tbody_requisition', trs);
							dateDocs	= moment(v.date,'YYYY/MM/DD HH:mm:ss').format('DD/MM/YYYY');
							trs.find('.name-doc').prepend(v.name);
							trs.find('.btn-doc').attr('href',baseRoute+'/'+v.path);
							trs.find('.btn-doc').attr('title',v.path);
							trs.find('.date-doc').prepend(dateDocs);
							$('#tbody_requisition').append(trs);
						})
						$('#documents_requisition').removeClass('hidden');
					}
					else
					{
						$('#documents_requisition').addClass('hidden');
					}
					$('#container-cambio-compras').slideUp();
					$('#table-warehouse_search').slideUp();
					$('#pagination_compras').slideUp();
					$('#form_compras_container').removeClass('hidden');
					updateSelectsAlta();
					cat_names = [
						@foreach(App\CatMeasurementTypes::whereNotNull('type')->get() as $m_types)
							@foreach ($m_types->childrens()->orderBy('child_order','asc')->get() as $child)
								{
									'id':{{ $child->id }},
									'value':'{{ $child->description }}'
								},
							@endforeach
						@endforeach
					];
					cat_names_category = [
						@foreach (App\CatWarehouseType::all() as $w)
							{
								'id':{{ $w->id }},
								'value':'{{ $w->description }}'
							},
						@endforeach
					];
					data.purchases[0].detail_purchase.forEach(purchase =>
					{
						measurement_id 	= cat_names.find(element => element.value === purchase.unit.toLowerCase())
						category_id 	= cat_names_category.find(element => element.id === purchase.category)
						if(measurement_id)
						{
							$('.js-measurement-edit-'+purchase['idDetailPurchase']).val(measurement_id.id).trigger('change');
						}
						if(category_id)
						{
							$('.js-category-edit-'+purchase['idDetailPurchase']).append(new Option(category_id.value, category_id.id, true, true)).trigger('change');
						}
						$('.articulo_damagedd').numeric({ negative : false, decimal : false });
					})
					stickyAdjustment();
				},
				error: function (error)
				{
					swal('', 'Error al buscar, por favor intente de nuevo.', 'error');
				}
			})
		})
		.on('click','.edit_compras_articulo',function()
		{
			idCompra	= $(this).val();
			category_id = $('.js-category-edit-'+idCompra+' option:selected').val();
			if($('.js-category-edit-'+idCompra).val().length == 0)
			{
				swal('', 'Debe seleccionar la categoría.', 'error');
				return;
			}
			if($('.js-account-'+idCompra).val().length == 0)
			{
				swal('', 'Debe seleccionar la cuenta.', 'error');
				return;
			}
			if(category_id == "4")
			{
				$("#edit_articulo_modal").val(idCompra);
				$('#modalEdit').modal('show');
			}
			else
			{
				addArticleToTable(idCompra);
			}
		})
		.on('click','.send-edit-articulo',function()
		{
			type      = $('input[name="typeCompras"]:checked').val();
			brand     = $('input[name="brandCompras"]').val().trim();
			storage   = $('input[name="storageCompras"]').val().trim();
			processor = $('input[name="processorCompras"]').val().trim();
			ram       = $('input[name="ramCompras"]').val().trim();
			sku       = $('input[name="skuCompras"]').val().trim();
			if(!type)
			{
				swal('', 'Debe seleccionar un tipo.', 'error');
				return
			}
			if(!brand)
			{
				swal('', 'Debe ingresar una marca.', 'error');
				return
			}
			if(!storage)
			{
				swal('', 'Debe ingresar la capacidad de almacenamiento.', 'error');
				return
			}
			if(!processor)
			{
				swal('', 'Debe ingresar un nombre de procesador.', 'error');
				return
			}
			if(!ram)
			{
				swal('', 'Debe ingresar una cantidad de ram.', 'error');
				return
			}
			if(!sku)
			{
				swal('', 'Debe ingresar un sku.', 'error');
				return
			}
			addArticleToTable($("#edit_articulo_modal").val());
			$('#modalEdit').modal('hide');
		})
		.on('click','.btn-show-deatils',function ()
		{
			$('#modalComentairesValue').val($(this).val() != "null" ? $(this).val() : '')
			$('#modalComentaries').modal('show');
		})
		.on('click','.edit-item_compras',function ()
		{
			$('.purchase_'+$(this).val()).show();
			$('input[name="amount"]').removeClass("error");
			$('input[name="quantity"]').removeClass("error");
			category_id		= $(this).parents('tr').find('.tcategory').val();
			place_id		= $(this).parents('tr').find('.place_id').val();
			measurement_id	= $(this).parents('tr').find('.tmeasurement').val();
			concept			= $(this).parents('tr').find('.tconcept').val();
			concept_name_id	= $(this).parents('tr').find('.tconcept_id').val();
			cant			= $(this).parents('tr').find('.tquanty').val();
			amount			= $(this).parents('tr').find('.uamount').val();
			importe			= $(this).parents('tr').find('.importe').val();
			short_code		= $(this).parents('tr').find('.short_code').val();
			long_code		= $(this).parents('tr').find('.long_code').val();
			comm			= $(this).parents('tr').find('.tcommentaries').val();
			$('.js-places_compra').val(place_id).trigger('change');
			$('.js-measurement_compras').val(measurement_id).trigger('change');
			$('input[name="concept_name_compras"]').val(concept);
			$('input[name="quantity_compras"]').val(cant);
			$('input[name="short_code_compras"]').val(short_code);
			$('input[name="long_code_compras"]').val(long_code);
			$('input[name="uamount_compras"]').val(amount);
			$('input[name="amount_compras"]').val(importe);
			$('textarea[id="commentaries_compras"]').val( comm === "Sin comentarios" ? "" : comm );
			$(this).parents('tr').remove();
			totalArticles_compras()
		})
		.on('click','#clean_compras', function ()
		{
			reset_form_compras();
		})
		.on('click','.select-all-category',function()
		{
			val		= 0;
			first	= false;
			$('#table-warehouse_compras').find('.tr-purchases').each(function()
			{
				if(!first)
				{
					val			= $(this).find('.js-category-a').val();
					description	= $(this).find('.js-category-a option:selected').text();
					first		= true;
				}
			});
			if(val.length == 0)
			{
				swal('','Debe seleccionar una categoría en el primer concepto.', 'warning');
			}
			else
			{
				$('.js-category-a').html('');
				$('.js-category-a').append(new Option(description, val, true, true)).trigger('change');
			}
		})
		.on('click','.select-all-acccount',function()
		{
			category		= 0;
			account			= 0;
			account_name	= '';
			first			= false;
			$('#table-warehouse_compras').find('.tr-purchases').each(function()
			{
				if(!first)
				{
					category		= $(this).find('.js-category-a').val();
					category_name	= $(this).find('.js-category-a option:selected').text();
					account			= $(this).find('.js-account-a').val();
					account_name	= $(this).find('.js-account-a option:selected').text().trim();
					first			= true;
				}
			});
			if(category.length == 0)
			{
				swal('', 'Debe seleccionar una categoría en el primer concepto.', 'warning');
				return;
			}
			if(account.length == 0)
			{
				swal('', 'Debe seleccionar una cuenta en el primer concepto.', 'warning');
				return;
			}
			swal({
				title: "¿Colocar cuenta a todos los conceptos?",
				text: "Si las categorías son diferentes, seran cambiadas por las del primer concepto",
				icon: "warning",
				buttons: ["Cancelar","OK"],
				dangerMode: true,
			})
			.then((willDelete) =>
			{
				if (willDelete)
				{
					removeAccountsCompra = false;
					$('.js-category-a').html("");
					$('.js-category-a').append(new Option(category_name, category, true, true)).trigger('change');
					option = '<option value='+account+'>'+account_name+'</option>';
					$('.js-account-a').each(function()
					{
						$(this).empty();
						$(this).append(option);
						$(this).val(account).trigger('change');
					})
				}
				setTimeout(() =>
				{
					removeAccountsCompra = true;
				}, 1500);
			});
		});
	});

	function totalArticles_compras()
	{
		var sumatotal          = 0;
		var sub_total_articles = 0;
		var iva_articles       = 0;
		$('#body_compras').find('.tr').each(function()
		{
			valor				= Number($(this).find('.importe').val());
			sumatotal			= sumatotal + valor;
			iva					= Number($(this).find('.tiva').val());
			iva_articles		= iva_articles + iva;
			sub_total_articles	= sub_total_articles + ( Number($(this).find('.tquanty').val()) * Number($(this).find('.uamount').val())) 
		});
		$('input[name="total_articles_compras"]').val(Number(sumatotal).toFixed(2));
		$('input[name="iva_articles_compras"]').val(Number(iva_articles).toFixed(2));
		$('input[name="sub_total_articles_compras"]').val((Number(sumatotal) - Number(iva_articles)).toFixed(2));
		$('.subTotalLabelCompras').text('$ '+(Number(sumatotal) - Number(iva_articles)).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));
		$('.ivaLabelCompras').text('$ '+Number(iva_articles).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));
		$('.totalLabelCompras').text('$ '+Number(sumatotal).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));
	}
	function reset_form_compras()
	{
		$('#container-cambio-compras').slideDown()
		$('#form_compras_container').addClass('hidden')
		$('#body_compras').find('.tr').each (function()
		{
			$(this).remove()
		});
		$('#table-return_compras_articulos').html('');
		$('.js-enterprises_compras').val(null).trigger('change');
		$('.js-places_compra').val(null).trigger('change');
		$('.js-measurement-a').val(null).trigger('change');
		$('.js-account-a').val(null).trigger('change');
		$('#enterpise_compras').val('');
		$('#datepicker_compras').val('');
		$('#total_compras').val('');
		$('input[name="concept_name_compras"]').val('');
		$('input[name="quantity_compras"]').val('');
		$('input[name="short_code_compras"]').val('');
		$('input[name="long_code_compras"]').val('');
		$('input[name="uamount_compras"]').val('');
		$('input[name="amount_compras"]').val('');
		$('textarea[id="commentaries_compras"]').val('');
		$('input[name="sub_total_articles_compras"]').val('');
		$('input[name="iva_articles_compras"]').val('');
		$('input[name="total_articles_compras"]').val('');
		$('#table-warehouse_search').slideDown();
		$('#pagination_compras').slideDown();
		$('.subTotalLabelCompras').text('$ 0.00');
		$('.ivaLabelCompras').text('$ 0.00');
		$('.totalLabelCompras').text('$ 0.00');
	}
	function addArticleToTable(id)
	{
		item				= $('.purchase_'+id).parents('.tr-purchases')
		category_id			= $('.js-category-edit-'+id+' option:selected').val()
		category_name		= $('.js-category-edit-'+id+' option:selected').text().trim()
		account_id			= $('.js-account-'+id+' option:selected').val()
		account_name		= $('.js-account-'+id+' option:selected').text().trim()
		concepto_compras	= item.find('.articulo_description').html()
		codigo_compras		= item.find('.articulo_code').html()
		cantidad_compras	= item.find('.articulo_quantity').html()
		unitario_compras	= item.find('.articulo_unitPrice').val()
		typeTax				= item.find('.articulo_tax_type').html()
		importe_compras		= item.find('.articulo_amount').val()
		comm				= item.find('.btn-show-deatils').val()
		damaged_compras		= item.find('.articulo_damagedd').val()

		if(!damaged_compras)
		{
			damaged_compras = 0;
		}
		if(Number(damaged_compras) > Number(cantidad_compras))
		{
			swal('', 'La cantidad de artículos dañados debe ser menor o igual a la cantidad de artículos.', 'error');
			return
		}
		type		= $('input[name="typeCompras"]:checked').val();
		brand		= $('input[name="brandCompras"]').val().trim();
		storage		= $('input[name="storageCompras"]').val().trim();
		processor	= $('input[name="processorCompras"]').val().trim();
		ram			= $('input[name="ramCompras"]').val().trim();
		sku			= $('input[name="skuCompras"]').val().trim();
		typeName	= ""
		switch (type)
		{
			case "1":
				typeName = "Smartphone"
				break;
			case "2":
				typeName = "Tablet"
				break;
			case "3":
			typeName = "Laptop"
				break;
			case "4":
				typeName = "Desktop"
				break;
		}
		measurement_compras	= $('.js-measurement-edit-'+id+' option:selected').text().trim()
		cat_names = [
			@foreach(App\CatMeasurementTypes::whereNotNull('type')->get() as $m_types)
				@foreach ($m_types->childrens()->orderBy('child_order','asc')->get() as $child)
					{
						'id':{{ $child->id }},
						'value':'{{ $child->description }}'
					},
				@endforeach
			@endforeach
		]
		measurement_id = cat_names.find(element => element.value === measurement_compras)
		if(measurement_id)
		{
			measurement_id = measurement_id.id;
		}
		iva_kind= item.find('.articulo_tax_type').html()
		iva		= ({{ App\Parameter::where('parameter_name','IVA')->first()->parameter_value }})/100;
		iva2	= ({{ App\Parameter::where('parameter_name','IVA2')->first()->parameter_value }})/100;
		ivaCalc = 0;
		switch(iva_kind)
		{
			case 'no':
				ivaCalc = 0;
				break;
			case 'a':
				ivaCalc = cantidad_compras*unitario_compras*iva;
				break;
			case 'b':
				ivaCalc = cantidad_compras*unitario_compras*iva2;
				break;
		}
		sub_total = (Number(importe_compras) - Number(ivaCalc))

		if($('.js-measurement-edit-'+id).val().length == 0)
		{
			swal('', 'Debe seleccionar la unidad de medición.', 'error');
			return
		}
		@php
			$body		= [];
			$modelBody	= [];
			$modelHead	= [
				[
					["value" => "Categoría", "show" => "true"],
					["value" => "Cuenta"],
					["value" => "Código"],
					["value" => "Concepto"],
					["value" => "Cantidad"],
					["value" => "Dañados"],
					["value" => "Unidad"],
					["value" => "P. unitario"],
					["value" => "IVA"],
					["value" => "Importe"]
				]
			];
			$body = [
				[	
					"show"		=> "true",
					"content"	=>
					[
						[
							"kind"			=> "components.inputs.input-text",
							"attributeEx"	=> "readonly=\"true\" type=\"hidden\" name=\"tcategory_name[]\"",
							"classEx"		=> "tcategoryId"
						],
						[
							"kind"			=> "components.inputs.input-text",
							"attributeEx"	=> "readonly=\"true\" type=\"hidden\" name=\"tcategory_id[]\"",
							"classEx"		=> "tcategoryName"
						]
					]
				],
				[
					"content"	=>
					[
						[
							"kind"			=> "components.inputs.input-text",
							"attributeEx"	=> "readonly=\"true\" type=\"hidden\" name=\"taccount_id[]\"",
							"classEx"		=> "taccountId"
						],
						[
							"kind"			=> "components.inputs.input-text",
							"attributeEx"	=> "readonly=\"true\" type=\"hidden\" name=\"taccount_name[]\"",
							"classEx"		=> "taccountName"
						]
					]
				],
				[
					"content"	=>
					[
						"kind"			=> "components.inputs.input-text",
						"attributeEx"	=> "readonly=\"true\" type=\"hidden\" name=\"tcode[]\"",
						"classEx"		=> "tcode"
					]
				],
				[
					"content"	=>
					[
						[
							"kind"			=> "components.inputs.input-text",
							"attributeEx"	=> "readonly=\"true\" type=\"hidden\" name=\"tconcept_name[]\"",
							"classEx"		=> "tconcept"
						],
						[
							"kind"			=> "components.inputs.input-text",
							"attributeEx"	=> "readonly=\"true\" type=\"hidden\" name=\"tidPurchase[]\"",
							"classEx"		=> "tconcept"
						],
						[
							"kind"			=> "components.inputs.input-text",
							"attributeEx"	=> "readonly=\"true\" type=\"hidden\" name=\"ttype[]\"",
							"classEx"		=> "ttype"
						],
						[
							"kind"			=> "components.inputs.input-text",
							"attributeEx"	=> "readonly=\"true\" type=\"hidden\" name=\"tbrand[]\"",
							"classEx"		=> "tbrand"
						],
						[
							"kind"			=> "components.inputs.input-text",
							"attributeEx"	=> "readonly=\"true\" type=\"hidden\" name=\"tstorage[]\"",
							"classEx"		=> "tstorage"
						],
						[
							"kind"			=> "components.inputs.input-text",
							"attributeEx"	=> "readonly=\"true\" type=\"hidden\" name=\"tprocessor[]\"",
							"classEx"		=> "tprocessor"
						],
						[
							"kind"			=> "components.inputs.input-text",
							"attributeEx"	=> "readonly=\"true\" type=\"hidden\" name=\"tram[]\"",
							"classEx"		=> "tram"
						],
						[
							"kind"			=> "components.inputs.input-text",
							"attributeEx"	=> "readonly=\"true\" type=\"hidden\" name=\"tsku[]\"",
							"classEx"		=> "tsku"
						],
					]
				],
				[
					"content"	=>
					[
						"kind"			=> "components.inputs.input-text",
						"attributeEx"	=> "readonly=\"true\" type=\"hidden\" name=\"tquanty[]\"",
						"classEx"		=> "tquanty class-quanty"
					]
				],
				[
					"content"	=>
					[
						"kind"			=> "components.inputs.input-text",
						"attributeEx"	=> "readonly=\"true\" type=\"hidden\" name=\"tdamaged[]\"",
						"classEx"		=> "tquanty class-quanty-damaged"
					]
				],
				[
					"content"	=>
					[
						"kind"			=> "components.inputs.input-text",
						"attributeEx"	=> "readonly=\"true\" type=\"hidden\" name=\"tmeasurement_id[]\"",
						"classEx"		=> "tmeasurement"
					]
				],
				[
					"content"	=>
					[
						"kind"			=> "components.inputs.input-text",
						"attributeEx"	=> "readonly=\"true\" type=\"hidden\" name=\"tuamount[]\"",
						"classEx"		=> "uamount"
					]
				],
				[
					"content"	=>
					[
						[
							"kind"			=> "components.inputs.input-text",
							"attributeEx"	=> "readonly=\"true\" type=\"hidden\" name=\"tiva[]\"",
							"classEx"		=> "tiva"
						],
						[
							"kind"			=> "components.inputs.input-text",
							"attributeEx"	=> "readonly=\"true\" type=\"hidden\" name=\"tiva_kind[]\"",
							"classEx"		=> "tiva_kind"
						],
						[
							"kind"			=> "components.inputs.input-text",
							"attributeEx"	=> "readonly=\"true\" type=\"hidden\" name=\"tsub_total[]\"",
							"classEx"		=> "tsub_total"
						],
						[
							"kind"			=> "components.inputs.input-text",
							"attributeEx"	=> "readonly=\"true\" type=\"hidden\" name=\"tcommentaries[]\"",
							"classEx"		=> "tcommentaries"
						]
					]
				],
				[
					"content"	=>
					[
						"kind"			=> "components.inputs.input-text",
						"attributeEx"	=> "readonly=\"true\" type=\"hidden\" name=\"tamount[]\"",
						"classEx"		=> "importe"
					]
				]
			];
			$modelBody[] = $body;
			$table = view('components.tables.table',[
				"modelHead" => $modelHead,
				"modelBody" => $modelBody, 
				"noHead"	=> true
			])->render();
		@endphp
		table_row	= '{!!preg_replace("/(\r)*(\n)*/", "", $table)!!}';
		tr_table	= $(table_row);
		tr_table.find('.tcategoryId').parent().prepend(category_name);
		tr_table.find('[name="tcategory_name[]"]').val(category_name);
		tr_table.find('[name="tcategory_id[]"]').val(category_id);
		tr_table.find('.taccountId').parent().prepend(account_name);
		tr_table.find('[name="taccount_id[]"]').val(account_id);
		tr_table.find('[name="taccount_name[]"]').val(account_name);
		tr_table.find('.tcode').parent().prepend(codigo_compras);
		tr_table.find('[name="tcode[]"]').val(codigo_compras);
		tr_table.find('.tconcept').parent().prepend(concepto_compras);
		tr_table.find('[name="tconcept_name[]"]').val(concepto_compras);
		tr_table.find('[name="tidPurchase[]"]').val(id);
		tr_table.find('[name="ttype[]"]').val(type);
		tr_table.find('[name="tbrand[]"]').val(brand);
		tr_table.find('[name="tstorage[]"]').val(storage);
		tr_table.find('[name="tprocessor[]"]').val(processor);
		tr_table.find('[name="tram[]"]').val(ram);
		tr_table.find('[name="tsku[]"]').val(sku);
		tr_table.find('.class-quanty').parent().prepend(cantidad_compras);
		tr_table.find('[name="tquanty[]"]').val(cantidad_compras);
		tr_table.find('.class-quanty-damaged').parent().prepend(damaged_compras);
		tr_table.find('[name="tdamaged[]"]').val(damaged_compras);
		tr_table.find('.tmeasurement').parent().prepend(measurement_compras);
		tr_table.find('[name="tmeasurement_id[]"]').val(measurement_id);
		tr_table.find('.uamount').parent().prepend('$ '+Number(unitario_compras).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));
		tr_table.find('[name="tuamount[]"]').val(unitario_compras);
		tr_table.find('.tiva').parent().prepend('$ '+Number(ivaCalc).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));
		tr_table.find('[name="tiva[]"]').val(Number(ivaCalc).toFixed(2));
		tr_table.find('[name="tiva_kind[]"]').val(iva_kind);
		tr_table.find('[name="tsub_total[]"]').val(sub_total);
		tr_table.find('[name="tcommentaries[]"]').val(comm);
		tr_table.find('.importe').parent().prepend('$ '+Number(importe_compras).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));
		tr_table.find('[name="tamount[]"]').val(importe_compras);
		$('#body_compras').append(tr_table);
		$('input[name="typeCompras"]:checked').prop('checked', false);
		$('input[name="brandCompras"]').val("");
		$('input[name="storageCompras"]').val("");
		$('input[name="processorCompras"]').val("");
		$('input[name="ramCompras"]').val("");
		$('input[name="skuCompras"]').val("");
		totalArticles_compras();
		$('.purchase_'+id).parents('.tr-purchases').remove()
	}
</script>