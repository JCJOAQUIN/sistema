<script>
	var SubTotal             = null;
	var Total                = null;
	var Fecha                = null;
	var conceptops           = [];
	var realPath             = [];
	var lotId                = null;
	var currentArticleNumber = 0;
	$(document).ready(function() 
	{
		$(function()
		{
			$('#date_masiva').datepicker({ dateFormat:'dd-mm-yy' });
		})
		$('input[name="xmlfile"]').change(function(e)
		{
			SubTotal             = null;
			Total                = null;
			Fecha                = null;
			conceptops           = [];
			realPath             = [];
			currentArticleNumber = 0;
			var reader           = new FileReader();
			reader.onload        = function(e)
			{  
				var xmlDoc      = $.parseXML(e.target.result);
				var comprobante = $(xmlDoc).find("cfdi\\:Comprobante");
				SubTotal        = comprobante.attr('SubTotal')
				Total           = comprobante.attr('Total')
				Fecha           = comprobante.attr('Fecha')
				$(xmlDoc).find('cfdi\\:Concepto').each(function()
				{
					var Cantidad      = $(this).attr('Cantidad');
					var Descripcion   = $(this).attr('Descripcion');
					var ValorUnitario = $(this).attr('ValorUnitario');
					var Importe       = Number($(this).attr('Importe'));
					var TotalConcepto = Importe;
					$(this).find('cfdi\\:Traslado').each(function()
					{
						TotalConcepto += Number($(this).attr('Importe'))
					});
					$(this).find('cfdi\\:Retencion').each(function()
					{
						TotalConcepto -= Number($(this).attr('Importe'))
					});
					conceptops.push(
					{
						Cantidad,
						Descripcion,
						PrecioUnitario: ValorUnitario,
						Importe:TotalConcepto,
					});
				});
			}
			reader.readAsText(e.target.files[0]);  
			swal(
			{
				closeOnClickOutside: false,
				closeOnEsc         : false,
				icon               : '{{ asset(getenv('LOADING_IMG')) }}',
				button             : false
			});
			var fileName = e.target.files[0].name;
			formData     = new FormData();
			formData.append('fileName', fileName);
			$.ajax(
			{
				type       : 'post',
				url        : '{{ route("warehouse.fileName") }}',
				data       : formData,
				contentType: false,
				processData: false,
				success    : function(data)
				{
					if(!data.exist)
					{
						swal.close();
						show_form_create_lot();
						updateSelectsAlta();
						new_masiva();
					}
					else
					{
						if(data.finish)
						{
							reset_form();
							swal('','Los artículos ya han sido dados de alta.','error');
						}
						else
						{
							swal.close()
							swal(
								{
								title: "Advertencia",
								text: "Ya existe un lote registrado con este nombre de archivo. Confirme que desea continuar registrando los artículos.",
								icon: "warning",
								buttons: ["Cancelar","OK"],
								dangerMode: true,
							})
							.then((response) => 
							{
								if (response)
								{
									lotId = data.lot.idlot;
									currentArticleNumber = data.articles_count;
									$('#idEnterpriseMasiva').val(data.lot.idEnterprise);
									new_masiva();
									show_articles_details_form();
									updateSelectsAlta();
								}
								else
								{
									reset_form();
								}
							});
						}
					}
				},
				error		: function()
				{
					swal.close();
					swal('', 'Error al guardar la información', 'error');
				}
			});
		});
		$(document).on('click','#delete-doc-masiva',function()
		{
			swal(
			{
				closeOnClickOutside: false,
				closeOnEsc         : false,
				icon               : '{{ asset(getenv('LOADING_IMG')) }}',
				button             : false,
			});
			actioner     = $(this);
			uploadedName = $(this).parents('.docs-p').find('.pathMasiva');
			formData     = new FormData();
			formData.append("realPath[]",uploadedName.val());
			$.ajax(
			{
				type       : 'post',
				url        : '{{ route("warehouse.upload") }}',
				data       : formData,
				contentType: false,
				processData: false,
				success    : function(r)
				{
					swal.close();
					actioner.parents('.docs-p').remove();
				},
				error : function()
				{
					swal.close();
					actioner.parents('.docs-p').remove();
				}
			});
			$(this).parents('docs-p').remove();
		})
		.on('change','.pathActionerMasiva',function(e)
		{
			filename     = $(this);
			uploadedName = $(this).parent('.uploader-content').siblings('.pathMasiva');
			extention    = /\.jpg|\.png|\.jpeg|\.pdf/i;
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
					return (css.match (/\bimage_\S+/g) || []).join(' ');
				});
				formData = new FormData();
				formData.append('path', filename.prop("files")[0]);
				formData.append('realPath[]','');
				$.ajax(
				{
					type       : 'post',
					url        : '{{ route("warehouse.upload") }}',
					data       : formData,
					contentType: false,
					processData: false,
					success    : function(r)
					{
						if(r.error=='DONE')
						{
							$(e.currentTarget).removeAttr('style').parent('.uploader-content').removeClass('loading').addClass('image_success');
							$(e.currentTarget).parent('.uploader-content').siblings('.pathMasiva').val(r.path);
						}
						else
						{
							swal('',r.message, 'error');
							$(e.currentTarget).removeAttr('style').parent('.uploader-content').removeClass('loading');
							$(e.currentTarget).val('');
							$(e.currentTarget).parent('.uploader-content').siblings('.pathMasiva').val('');
						}
					},
					error: function()
					{
						swal('', 'Ocurrió un error durante la carga del archivo, intente de nuevo, por favor', 'error');
						$(e.currentTarget).removeAttr('style').parent('.uploader-content').removeClass('loading');
						$(e.currentTarget).val('');
						$(e.currentTarget).parent('.uploader-content').siblings('.pathMasiva').val('');
					}
				})
			}
		})
		.on('change','.quantity_not_damaged_masiva,.damaged_masiva', function()
		{
			nd = Number($('input[name="quantity_not_damaged_masiva"]').val());
			d  = Number($('input[name="damaged_masiva"]').val());
			t  = nd + d;
			$('input[name="quantity_masiva"]').val(t);
		})
		.on('click','#help-btn-lote', function()
		{
			swal({
				title  : "Ayuda",
				text   : "Al enviar los datos de la solicitud podrá continuar después si sube el mismo documento.",
				icon   : "info",
				buttons: 
				{
					cancel : false,
					confirm: true,
				},
			});
		})
		.on('click','[name="addDocMasiva"]', function()
		{
			@php
				$newdoc = view('components.documents.upload-files',[
					"attributeExInput"		=> "type=\"file\" name=\"pathMasiva\" accept=\".pdf,.jpg,.png\"",
					"classExInput" 			=> "pathActionerMasiva",
					"attributeExRealPath"	=> "type=\"hidden\"",
					"classExRealPath"		=> "pathMasiva",
					"classExDelete"			=> "delete-span",
					"attributeExDelete"		=> "id=delete-doc-masiva"
				])->render();
			@endphp
			newDocs = '{!!preg_replace("/(\r)*(\n)*/", "", $newdoc)!!}';
			newdoc 	= $(newDocs);
			$('#documentsMasiva').append(newdoc);
		})
		.on('change','#concept_name_masiva_sugerido_check',function()
		{
			if($(this).is(":checked"))
			{
				hide_concept_container();
			}
			else
			{
				show_concept_container();
			}
		})
		.on('click','#clean_masiva',function()
		{
			reset_form();
		})
		.on('change','#quantity_masiva,#uamount_masiva,[name="masiva_iva_kind"]',function()
		{
			cant    = $('input[name="quantity_masiva"]').val();
			precio  = $('input[name="uamount_masiva"]').val();
			iva     = ({{ App\Parameter::where('parameter_name','IVA')->first()->parameter_value }})/100;
			iva2    = ({{ App\Parameter::where('parameter_name','IVA2')->first()->parameter_value }})/100;
			ivaCalc = 0;
			switch($('input[name="masiva_iva_kind"]:checked').val())
			{
				case 'no':
					ivaCalc = 0;
					break;
				case 'a':
					ivaCalc = cant*precio*iva;
					break;
				case 'b':
					ivaCalc = cant*precio*iva2;
					break;
			}
			totalImporte = (cant * precio) + ivaCalc;
			$('input[name="amount_masiva"]').val(totalImporte.toFixed(2));
		})
		.on('change','#category_id_masiva',function()
		{
			$('.js-accounts-masiva').html("");
			search_accounts($('#idEnterpriseMasiva').val(),false,'masiva');
			switch ($('#category_id_masiva option:selected').val())
			{
				case "1":
				case "2":
				case "3":
					$('.options_computer_masiva').addClass('hidden');
					break;
				case "4":
					$('.options_computer_masiva').removeClass('hidden');
					break;
				default:
					$('.options_computer_masiva').addClass('hidden');
					$('input[name="type_masiva"]:checked').prop('checked', false);
					$('input[name="brand_masiva"]').val("");
					$('input[name="storage_masiva"]').val("");
					$('input[name="processor_masiva"]').val("");
					$('input[name="ram_masiva"]').val("");
					$('input[name="sku_masiva"]').val("");
					break;
			}
		})
		.on('click','#masiva_siguiente', function()
		{
			swal(
			{
				closeOnClickOutside: false,
				closeOnEsc         : false,
				icon               : '{{ asset(getenv('LOADING_IMG')) }}',
				button             : false,
			});
			var files = $('input[name="xmlfile"]').prop('files');
			if(!files)
			{
				swal.close();
				swal('', 'No se encontro ningún archivo', 'error');
				return;
			}
			idEnterprise = $('.js-enterprises_masiva option:selected').val();
			if(idEnterprise == null)
			{
				swal('','Debe seleccionar una empresa.','error');
				return;
			}
			else
			{
				$('#idEnterpriseMasiva').val(idEnterprise);
			}
			$('.pathMasiva').each(function()
			{
				realPath.push($(this).val());
			})
			if(realPath.length == 0)
			{
				swal({
					title  : "Error",
					text   : "Debe agregar al menos un ticket de compra.",
					icon   : "error",
					buttons:
					{
						confirm: true,
					},
				});
				return;
			}
			formData = new FormData();
			formData.append('fileName', files[0].name);
			formData.append('sub_total', $('#sub_total_masiva').val());
			formData.append('total', $('#total_masiva').val());
			formData.append('fecha', $('#date_masiva').val());
			formData.append('idEnterprise', $('.js-enterprises_masiva option:selected').val());
			formData.append('file', $('input[name="xmlfile"]')[0].files[0]);
			$('.pathMasiva').each(function()
			{
				formData.append('realPath[]', $(this).val());
			});
			$.ajax(
			{
				type       : 'post',
				url        : '{{ route("warehouse.create_lot_file") }}',
				data       : formData,
				contentType: false,
				processData: false,
				success    : function(data)
				{
					lotId = data.lot.idlot;
					$('#account_id_masiva').html('');
					hide_form_create_lot();
					show_articles_details_form();
					updateSelectsAlta();
					swal.close();
				},
				error : function()
				{
					swal.close();
					swal('', 'Error al guardar la información', 'error');
				}
			});
		})
		.on('click','#masiva_send_article', function()
		{
			category_id = $('#category_id_masiva option:selected').val();
			if($('.js-category-masiva').val().length == 0)
			{
				swal('','Debe seleccionar una categoría.','error');
				return;
			}
			if($('.js-accounts-masiva').val().length == 0)
			{
				swal('','Debe seleccionar una cuenta.','error');
				return;
			}
			if($('.js-places_masiva').val().length == 0)
			{
				swal('','Debe seleccionar la ubicación/sede.','error');
				return;
			}
			if($('.js-measurement_masiva').val().length == 0)
			{
				swal('','Debe seleccionar la unidad de medición.','error');
				return;
			}
			concept              = $('#concept_name_masiva_sugerido_check').is(":checked") ? $('#concept_name_masiva_sugerido').val() : $('#concept_name_masiva').val();
			account_id           = $('.js-accounts-masiva').val();
			damaged              = $('#damaged_masiva').val();
			quanty               = $('#quantity_masiva').val();
			short_code           = $('#short_code_masiva').val();
			long_code            = $('#long_code_masiva').val();
			measurement_id       = $('.js-measurement_masiva').val();
			place_id             = $('.js-places_masiva').val();
			measurement_quantity = $('#measurement_quantity_masiva').val();
			commentaries         = $('#commentaries_masiva').val();
			amount               = $('#amount_masiva').val();
			idlot                = lotId;
			category_id          = $('.js-category-masiva').val();
			iva_kind             = $('input[name="masiva_iva_kind"]:checked').val();
			uamount              = $('input[name="uamount_masiva"]').val();
			type                 = $('input[name="type_masiva"]:checked').val();
			brand                = $('input[name="brand_masiva"]').val().trim();
			storage              = $('input[name="storage_masiva"]').val().trim();
			processor            = $('input[name="processor_masiva"]').val().trim();
			ram                  = $('input[name="ram_masiva"]').val().trim();
			sku                  = $('input[name="sku_masiva"]').val().trim();
			if(!damaged)
			{
				damaged = 0;
			}
			if(!concept)
			{
				swal('','Debe ingresar un concepto.','error');
				return;
			}
			if(!quanty)
			{
				swal('','Debe ingresar una cantidad.','error');
				return;
			}
			if(Number(damaged) > Number(quanty))
			{
				swal('', 'La cantidad de artículos dañados debe ser menor o igual a la cantidad de artículos.', 'error');
				return;
			}
			if(!uamount)
			{
				swal('','Debe ingresar el precio unitario.','error');
				return;
			}
			if(category_id == "4")
			{
				if(!type)
				{
					swal('', 'Debe seleccionar un tipo.', 'error');
					return;
				}
				if(!brand)
				{
					swal('', 'Debe ingresar una marca.', 'error');
					return;
				}
				if(!storage)
				{
					swal('', 'Debe ingresar la capacidad de almacenamiento.', 'error');
					return;
				}
				if(!processor)
				{
					swal('', 'Debe ingresar un nombre de procesador.', 'error');
					return;
				}
				if(!ram)
				{
					swal('', 'Debe ingresar una cantidad de ram.', 'error');
					return;
				}
				if(!sku)
				{
					swal('', 'Debe ingresar un sku.', 'error');
					return;
				}
			}
			finish = (currentArticleNumber + 1) == conceptops.length ? true : false;
			swal(
			{
				closeOnClickOutside: false,
				closeOnEsc         : false,
				icon               : '{{ asset(getenv('LOADING_IMG')) }}',
				button             : false,
			});
			formData = new FormData();
			formData.append('concept',concept);
			formData.append('quanty',quanty);
			formData.append('damaged',damaged);
			formData.append('short_code',short_code);
			formData.append('long_code',long_code);
			formData.append('measurement_id',measurement_id);
			formData.append('measurement_quantity',measurement_quantity);
			formData.append('place_id',place_id);
			formData.append('commentaries',commentaries);
			formData.append('amount',amount);
			formData.append('idlot',idlot);
			formData.append('category_id',category_id);
			formData.append('finish',finish);
			formData.append('iva_kind',iva_kind);
			formData.append('uamount',uamount);
			formData.append('account_id',account_id);
			formData.append('type',type);
			formData.append('brand',brand);
			formData.append('storage',storage);
			formData.append('processor',processor);
			formData.append('ram',ram);
			formData.append('sku',sku);
			$.ajax(
			{
				type       : 'post',
				url        : '{{ route("warehouse.create_warehouse") }}',
				data       : formData,
				contentType: false,
				processData: false,
				success    : function(data)
				{
					if(data.status)
					{
						scrollTop();
						currentArticleNumber += 1;
						if(currentArticleNumber == conceptops.length)
						{
							swal('', 'Registro finalizado exitosamente.', 'success');
							reset_form();
						}
						else
						{
							new_masiva();
							swal('', 'Artículo guardado', 'success');
						}
					}
					else
					{
						swal('', 'Error al guardar la información', 'error');
					}
				},
				error : function()
				{
					swal('', 'Error al guardar la información', 'error');
				}
			});
		});
	});

	function new_masiva()
	{
		hide_concepto_sugerido_container();
		show_concept_container();
		hide_documents_masiva();
		$('#date_masiva').val($.datepicker.formatDate('dd-mm-yy', new Date(Fecha)));
		$('#total_masiva').val(Total);
		$('#sub_total_masiva').val(SubTotal);
		$('.js-measurement_masiva').val(null).trigger('change');
		$('.js-places_masiva').val(null).trigger('change');
		$('#measurement_quantity_masiva').val("");
		$('#damaged_masiva').val("");
		$('#concept_name_masiva').val(conceptops[currentArticleNumber].Descripcion);
		$('#short_code_masiva').val("");
		$('#long_code_masiva').val("");
		$('#quantity_masiva').val(conceptops[currentArticleNumber].Cantidad);
		$('#uamount_masiva').val(conceptops[currentArticleNumber].PrecioUnitario);
		$('#amount_masiva').val(conceptops[currentArticleNumber].Importe);
		$('#commentaries_masiva').val("");
		$('#articles_count').html("Artículos: "+ (currentArticleNumber+1)+ "/" +conceptops.length);
		$("#concept_name_masiva_sugerido_check").prop("checked", false);
		search_concept();
	}
	function search_concept()
	{
		str = conceptops[currentArticleNumber].Descripcion.toLowerCase().split(" ");
		var array = str.filter( s => 
		{
			if(s.length == 1)
			{
				if((s.match(/[aeiou]/gi)))
				{
					return false;
				}
			}
			else if(
				s.match("\\by\\b") ||
				s.match("\\bde\\b") ||
				s.match("\\bdel\\b") ||
				s.match("\\bpara\\b") ||
				s.match("\\bal\\b") ||
				s.match("\\bcon\\b")  ||
				s.match("\\ben\\b") 
				)
			{
				return false;
			}
			else
			{
				return true;
			}
		});
		swal(
		{
			closeOnClickOutside: false,
			closeOnEsc         : false,
			icon               : '{{ asset(getenv('LOADING_IMG')) }}',
			button             : false
		});
		formData = new FormData();
		array.forEach(e => 
		{
			formData.append('search[]',e);
		});
		$.ajax(
		{
			type       : 'post',
			url        : '{{ route("warehouse.search_concept") }}',
			data       : formData,
			contentType: false,
			processData: false,
			success    : function(data)
			{
				if(data.concept)
				{
					$('#concept_name_masiva_sugerido').val(data.concept.description);
					show_concepto_sugerido_container();
				}
				swal.close();
			},
			error : function()
			{
				swal.close();
				swal('', 'Error al buscar concepto.', 'error');
			}
		});
	}
	function remove_doc()
	{
		$input = $('input[name="xmlfile"]').parent('.uploader-content');
		$('input[name="xmlfile"]').val("");
		$('form_create_lot').slideDown('fast');
		$('articles_count_container').slideDown('fast');
		$('articles_details_form').slideDown('fast');
	}
	function reset_form()
	{
		$input = $('input[name="xmlfile"]').parent('.uploader-content');
		$input.removeClass('image_success');
		$('input[name="xmlfile"]').val("");
		Total                = null;
		Fecha                = null;
		conceptops           = [];
		realPath             = [];
		currentArticleNumber = 0;
		$('#date_masiva').val("");
		$('#total_masiva').val("");
		$('#concept_name_masiva').val("");
		$('#short_code_masiva').val("");
		$('#long_code_masiva').val("");
		$('#quantity_masiva').val("");
		$('#damaged_masiva').val("");
		$('#uamount_masiva').val("");
		$('#amount_masiva').val("");
		$('#commentaries_masiva').val("");
		$('#articles_count').html("Artículos: 1/");
		$("#concept_name_masiva_sugerido_check").prop("checked", false);
		hide_form_create_lot();
		hide_articles_details_form();
		show_documents_masiva();
	}
	function show_documents_masiva()
	{
		$('#documents_masiva').slideDown("fast");
	}
	function hide_documents_masiva()
	{
		$('#documents_masiva').slideUp("fast");
	}
	function show_form_create_lot()
	{
		$('#form_create_lot').slideDown('fast');
	}
	function hide_form_create_lot()
	{
		$('#form_create_lot').slideUp('fast');
	}
	function show_articles_details_form()
	{
		$('#articles_details_form').slideDown('fast');
	}
	function hide_articles_details_form()
	{
		$('#articles_details_form').slideUp('fast');
	}
	function show_concepto_sugerido_container()
	{
		$('#concepto_sugerido_container').slideDown('fast');
	}
	function hide_concepto_sugerido_container()
	{
		$('#concepto_sugerido_container').slideUp('fast');
	}
	function show_concept_container()
	{
		$('#concept_container').slideDown('fast');
	}
	function hide_concept_container()
	{
		$('#concept_container').slideUp('fast');
	}
	function scrollTop()
	{
		$('.container-right-content').scrollTop(0);
	}
</script>