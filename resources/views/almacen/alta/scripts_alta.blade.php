<script>
	$(document).ready(function() 
	{
		$('#total_masiva,#amount_masiva,#uamount_masiva,#quantity_masiva').numeric({ negative : false, altDecimal: ".", decimalPlaces: 2 });
		countbody = $('#body .tr-concepts').length;
		if (countbody <= 0) 
		{
			$('#table,#table2').hide();
		}
		else
		{
			$('#table,#table2').show();
		}
		$enterprise = $('select[name="enterprise_id"] option:selected').val();
		search_accounts($enterprise,true)
		
		updateSelectsAlta();

		$('.quantity,.damaged,.quantity_not_damaged,.damaged').numeric({ negative : false, decimal : false });
		$('.inversion, .amount,.uamount').numeric({ negative : false, altDecimal: ".", decimalPlaces: 2 });
		$(function()
		{
			$('#datepicker').datepicker({ dateFormat:'dd-mm-yy' });
		})
		$(document).on('click','#add',function()
		{
			iva		= ({{ App\Parameter::where('parameter_name','IVA')->first()->parameter_value }})/100;
			iva2	= ({{ App\Parameter::where('parameter_name','IVA2')->first()->parameter_value }})/100;
			$('input[name="amount"]').removeClass("error");
			$('input[name="quantity"]').removeClass("error");
			$('input[name="uamount"]').removeClass("error");
			$('input[name="quantity_not_damaged"]').removeClass("error");
			$('input[name="damaged"]').removeClass("error");

			categoty_id    		= $('select[name="category_id"] option:selected').val()
			category_name 		= $('select[name="category_id"] option:selected').text().trim()
			account_id 	   		= $('select[name="account_id"] option:selected').val()
			account_name   		= $('select[name="account_id"] option:selected').text().trim()
			place_id 	   		= $('select[name="place_id"] option:selected').val()
			place_name 	   		= $('select[name="place_id"] option:selected').text().trim()
			measurement_id		= $('select[name="measurement_id"] option:selected').val()
			measurement	   		= $('select[name="measurement_id"] option:selected').text().trim()
			concept		   		= $('input[name="concept_name"]').val()
			concept_id			= $('input[name="concept_name_id"]').val()
			short_code			= $('input[name="short_code"]').val().trim();
			long_code			= $('input[name="long_code"]').val().trim();
			cant				= $('input[name="quantity"]').val().trim();
			damaged				= $('input[name="damaged"]').val().trim();
			uamount				= $('input[name="uamount"]').val().trim();
			amount				= $('input[name="amount"]').val().trim();
			comm				= $('textarea[id="commentaries"]').val().trim();
			quantityNotDamaged	= $('input[name="quantity_not_damaged"]').val().trim();
			iva_kind			= $('input[name="iva_kind"]:checked').val();
			
			if(!damaged)
			{
				damaged = 0;
			}
			type 	  = $('input[name="type"]:checked').val();
			brand 	  = $('input[name="brand"]').val().trim();
			storage   = $('input[name="storage"]').val().trim();
			processor = $('input[name="processor"]').val().trim();
			ram 	  = $('input[name="ram"]').val().trim();
			sku 	  = $('input[name="sku"]').val().trim();

			typeName = "";
			switch (type)
			{
				case "1":
					typeName = "Smartphone";
					break;
				case "2":
					typeName = "Tablet";
					break;
				case "3":
					typeName = "Laptop";
					break;
				case "4":
					typeName = "Desktop";
					break;
			}
			ivaCalc = 0
			switch(iva_kind)
			{
				case 'no':
					ivaCalc = 0;
					break;
				case 'a':
					ivaCalc = cant*uamount*iva;
					break;
				case 'b':
					ivaCalc = cant*uamount*iva2;
					break;
			}
			sub_total = (Number(uamount) * Number(cant))
			if(!categoty_id)
			{
				swal('', 'Seleccione al menos una categoría.', 'error');
				return
			}
			if(!account_id)
			{
				swal('', 'Seleccione al menos una cuenta.', 'error');
				return
			}
			if(!place_id)
			{
				swal('', 'Seleccione la ubicación/sede.', 'error');
				return
			}
			if(!measurement_id)
			{
				swal('', 'Seleccione la unidad de medida.', 'error');
				return
			}
			if(!concept)
			{
				swal('', 'Por favor ingrese un concepto.', 'error');
				return
			}
			if(categoty_id == "4")
			{
				if(!type)
				{
					swal('', 'Por favor seleccione un tipo.', 'error');
					return
				}
				if(!brand)
				{
					swal('', 'Por favor ingrese una marca.', 'error');
					return
				}
				if(!storage)
				{
					swal('', 'Por favor ingrese la capacidad de almacenamiento.', 'error');
					return
				}
				if(!processor)
				{
					swal('', 'Por favor ingrese un nombre de procesador.', 'error');
					return
				}
				if(!ram)
				{
					swal('', 'Por favor ingrese una cantidad de ram.', 'error');
					return
				}
				if(!sku)
				{
					swal('', 'Por favor ingrese un sku.', 'error');
					return
				}
			}
			else
			{
				type 	  = ""
				brand 	  = ""
				storage   = ""
				processor = ""
				ram 	  = ""
				sku 	  = ""
			}
			if (comm == "") 
			{
				comm = "Sin comentarios";
			}
			if(Number(damaged) > Number(cant))
			{
				swal('', 'La cantidad de artículos dañados debe ser menor o igual a la cantidad de artículos.', 'error');
				return
			}
			if (cant == "" || amount == "" || uamount == "" || quantityNotDamaged == "" || damaged == "")
			{
				if (cant == "") 
				{
					$('input[name="quantity"]').addClass('error');
				} 
				if (amount == "") 
				{
					$('input[name="amount"]').addClass('error');
				} 
				if (uamount == "") 
				{
					$('input[name="uamount"]').addClass('error');
				}
				if (quantityNotDamaged == "")
				{
					$('input[name="quantity_not_damaged"]').addClass('error');
				}
				if (damaged == "")
				{
					$('input[name="damaged"]').addClass('error');
				} 
				swal('', 'Por favor llene los campos necesarios', 'error');
				return
			}
			else if( amount <= 0)
			{
				swal('', 'El importe no puede ser menor o igual a cero.', 'error');
			}
			else if(Number(amount).toFixed(2) == "NaN")
			{
				swal('', 'El importe debe ser númerico.', 'error');
			}
			else
			{
				@php
					$body		= [];
					$modelBody	= [];
					$modelHead	= [
						[
							["value" => "Categoría", "classEx" => "sticky inset-x-0"],
							["value" => "Cuenta", "classEx" => "sticky inset-x-0"],
							["value" => "Concepto"],
							["value" => "Tipo"],
							["value" => "Marca"],
							["value" => "Capacidad"],
							["value" => "Procesador"],
							["value" => "Memoria RAM"],
							["value" => "sku"],
							["value" => "Unidad"],
							["value" => "Cód. corto"],
							["value" => "Cód. largo"],
							["value" => "Ubicación/sede"],
							["value" => "Cantidad"],
							["value" => "Dañados"],
							["value" => "P. unitario"],
							["value" => "IVA"],
							["value" => "Importe"],
							["value" => "Acciones"]
						]
					];
					$body = [ "classEx" => "tr-concepts",
						[
							"classEx"	=> "sticky inset-x-0",
							"content"	=>
							[
								[
									"kind" 		=> "components.labels.label",
									"classEx"	=> "class-category-name w-40 text-center"
								],
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "readonly=\"true\" type=\"hidden\" name=\"tcategory_name[]\"",
									"classEx"		=> "tcategoryName"
								],
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "readonly=\"true\" type=\"hidden\" name=\"tcategory_id[]\"",
									"classEx"		=> "tcategory"
								],
							]
						],
						[
							"classEx"	=> "sticky inset-x-0",
							"content"	=>
							[
								[
									"kind" 		=> "components.labels.label",
									"classEx"	=> "class-account-name w-40 text-center"
								],
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "readonly=\"true\" type=\"hidden\" name=\"taccount_id[]\"",
									"classEx"		=> "taccount_id"
								],
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "readonly=\"true\" type=\"hidden\" name=\"taccount_name[]\"",
									"classEx"		=> "taccount_name"
								],
							]
						],
						[
							"content"	=>
							[
								[
									"kind" 		=> "components.labels.label",
									"classEx"	=> "class-concept w-40 text-center"
								],
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "readonly=\"true\" type=\"hidden\" name=\"tconcept_name[]\"",
									"classEx"		=> "tconcept"
								],
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "readonly=\"true\" type=\"hidden\" name=\"tconcept_id[]\"",
									"classEx"		=> "tconcept_id"
								]
							]
						],
						[
							"content"	=>
							[
								[
									"kind" 		=> "components.labels.label",
									"classEx"	=> "class-typeName w-40 text-center"
								],
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "readonly=\"true\" type=\"hidden\" name=\"ttype[]\"",
									"classEx"		=> "ttype"
								]
							]
						],
						[
							"content"	=>
							[
								[
									"kind" 		=> "components.labels.label",
									"classEx"	=> "class-brand w-40 text-center"
								],
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "readonly=\"true\" type=\"hidden\" name=\"tbrand[]\"",
									"classEx"		=> "tbrand"
								]
							]
						],
						[
							"content"	=>
							[
								[
									"kind" 		=> "components.labels.label",
									"classEx"	=> "class-storage w-40 text-center"
								],
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "readonly=\"true\" type=\"hidden\" name=\"tstorage[]\"",
									"classEx"		=> "tstorage"
								]
							]
						],
						[
							"content"	=>
							[
								[
									"kind" 		=> "components.labels.label",
									"classEx"	=> "class-processor w-40 text-center"
								],
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "readonly=\"true\" type=\"hidden\" name=\"tprocessor[]\"",
									"classEx"		=> "tprocessor"
								]
							]
						],
						[
							"content"	=>
							[
								[
									"kind" 		=> "components.labels.label",
									"classEx"	=> "class-ram w-40 text-center"
								],
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "readonly=\"true\" type=\"hidden\" name=\"tram[]\"",
									"classEx"		=> "tram"
								]
							]
						],
						[
							"content"	=>
							[
								[
									"kind" 		=> "components.labels.label",
									"classEx"	=> "class-sku w-40 text-center"
								],
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "readonly=\"true\" type=\"hidden\" name=\"tsku[]\"",
									"classEx"		=> "tsku"
								]
							]
						],
						[
							"content"	=>
							[
								[
									"kind" 		=> "components.labels.label",
									"classEx"	=> "class-measurement w-40 text-center"
								],
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "readonly=\"true\" type=\"hidden\" name=\"tmeasurement_id[]\"",
									"classEx"		=> "tmeasurement"
								]
							]
						],
						[
							"content"	=>
							[
								[
									"kind" 		=> "components.labels.label",
									"classEx"	=> "class-short-code w-40 text-center"
								],
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "readonly=\"true\" type=\"hidden\" name=\"tshort_code[]\"",
									"classEx"		=> "tshort_code"
								]
							]
						],
						[
							"content"	=>
							[
								[
									"kind" 		=> "components.labels.label",
									"classEx"	=> "class-long-code w-40 text-center"
								],
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "readonly=\"true\" type=\"hidden\" name=\"tlong_code[]\"",
									"classEx"		=> "tlong_code"
								]
							]
						],
						[
							"content"	=>
							[
								[
									"kind" 		=> "components.labels.label",
									"classEx"	=> "class-place-name w-40 text-center"
								],
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "readonly=\"true\" type=\"hidden\" name=\"tplace_id[]\"",
									"classEx"		=> "tplace_id"
								]
							]
						],
						[
							"content"	=>
							[
								[
									"kind" 		=> "components.labels.label",
									"classEx"	=> "class-cant w-40 text-center"
								],
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "readonly=\"true\" type=\"hidden\" name=\"tquanty[]\"",
									"classEx"		=> "tquanty"
								]
							]
						],
						[
							"content"	=>
							[
								[
									"kind" 		=> "components.labels.label",
									"classEx"	=> "class-damaged w-40 text-center"
								],
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "readonly=\"true\" type=\"hidden\" name=\"tdamaged[]\"",
									"classEx"		=> "tdamaged"
								],
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "readonly=\"true\" type=\"hidden\" name=\"tquantityNoDamaged[]\"",
									"classEx"		=> "tquantityNoDamaged"
								]
							]
						],
						[
							"content"	=>
							[
								[
									"kind" 		=> "components.labels.label",
									"classEx"	=> "class-uamount w-40 text-center"
								],
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "readonly=\"true\" type=\"hidden\" name=\"tuamount[]\"",
									"classEx"		=> "tuamount"
								]
							]
						],
						[
							"content"	=>
							[
								[
									"kind" 		=> "components.labels.label",
									"classEx"	=> "class-ivaCalc w-40 text-center"
								],
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
								[
									"kind" 		=> "components.labels.label",
									"classEx"	=> "class-amount w-40 text-center"
								],
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "readonly=\"true\" type=\"hidden\" name=\"tamount[]\"",
									"classEx"		=> "timporte"
								]
							]
						],
						[
							"content"	=>
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
									"classEx"		=> "delete-item",
									"label"			=> "<span class=\"icon-x delete-span\"></span>"
								],
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
				table		= '{!!preg_replace("/(\r)*(\n)*/", "", $table)!!}';
				tr_table	= $(table);
				tr_table.find('.class-category-name').text(category_name);
				tr_table.find('.tcategoryName').val(category_name);
				tr_table.find('.tcategory').val(categoty_id);
				tr_table.find('.class-account-name').text(account_name);
				tr_table.find('.taccount_id').val(account_id);
				tr_table.find('.taccount_name').val(account_name);
				tr_table.find('.class-concept').text(concept);
				tr_table.find('.tconcept').val(concept);
				tr_table.find('.tconcept_id').val(concept_id);
				tr_table.find('.class-typeName').text(typeName != "" ? typeName : '---');
				tr_table.find('.ttype').val(type);
				tr_table.find('.class-brand').text(brand != "" ? brand : '---');
				tr_table.find('.tbrand').val(brand);
				tr_table.find('.class-storage').text(storage != "" ? storage : '---');
				tr_table.find('.tstorage').val(storage);
				tr_table.find('.class-processor').text(processor != "" ? processor : '---');
				tr_table.find('.tprocessor').val(processor);
				tr_table.find('.class-ram').text(ram != "" ? ram : '---');
				tr_table.find('.tram').val(ram);
				tr_table.find('.class-sku').text(sku != "" ? sku : '---');
				tr_table.find('.tsku').val(sku);
				tr_table.find('.class-measurement').text(measurement);
				tr_table.find('.tmeasurement').val(measurement_id);
				tr_table.find('.class-short-code').text(short_code != "" ? short_code : '---');
				tr_table.find('.tshort_code').val(short_code);
				tr_table.find('.class-long-code').text(long_code != "" ? long_code : '---');
				tr_table.find('.tlong_code').val(long_code);
				tr_table.find('.class-place-name').text(place_name != "" ? place_name : '---');
				tr_table.find('.tplace_id').val(place_id);
				tr_table.find('.class-cant').text(cant != "" ? cant : '---');
				tr_table.find('.tquanty').val(cant);
				tr_table.find('.class-damaged').text(damaged);
				tr_table.find('.tdamaged').val(damaged);
				tr_table.find('.tquantityNoDamaged').val(quantityNotDamaged);
				tr_table.find('.class-uamount').text(uamount != "" ? '$ '+Number(uamount).toFixed(2) : '$ 0.00');
				tr_table.find('.tuamount').val(uamount);
				tr_table.find('.class-ivaCalc').text(ivaCalc != "" ? '$ '+Number(ivaCalc).toFixed(2) : '$ 0.00');
				tr_table.find('.tiva').val(Number(ivaCalc).toFixed(2));
				tr_table.find('.tiva_kind').val(iva_kind);
				tr_table.find('.tsub_total').val(sub_total);
				tr_table.find('.tcommentaries').val(comm);
				tr_table.find('.class-amount').text(amount != "" ? '$ '+Number(amount).toFixed(2) : '$ 0.00');
				tr_table.find('.timporte').val(amount);		
				$('#body').append(tr_table);
				$('.js-category').html('');
				$('.js-accounts').val(null).trigger('change');
				$('.js-places').html('');
				$('.js-measurement').val(null).trigger('change');
				$('input[name="concept_name"]').val("");
				$('input[name="concept_name_id"]').val("");
				$('input[name="type"]:checked').prop('checked', false);
				$('input[name="brand"]').val("");
				$('input[name="storage"]').val("");
				$('input[name="processor"]').val("");
				$('input[name="ram"]').val("");
				$('input[name="sku"]').val("");
				$('input[name="short_code"]').val("");
				$('input[name="long_code"]').val("");
				$('input[name="quantity"]').val("");
				$('input[name="uamount"]').val("");
				$('input[name="amount"]').val("");
				$('input[name="damaged"]').val("");
				$('input[name="quantity_not_damaged"]').val("");
				$('textarea[id="commentaries"]').val("");
				$('input:radio[name=iva_kind]').filter('[value="no"]').prop('checked', true);
				remove_required_fields()
				countbody = $('#body .tr-concepts').length;
				if (countbody <= 0) 
				{
					$('#table,#table2').hide();
				}
				else
				{
					$('#table,#table2').show();
				}
				totalArticles();
				$('.edit-item').attr('disabled', false);
				stickyAdjustment();
			}
		})
		.on('change','.quantity,.uamount,.iva_kind',function()
		{
			cant	= $('input[name="quantity"]').val();
			precio	= $('input[name="uamount"]').val();
			iva		= ({{ App\Parameter::where('parameter_name','IVA')->first()->parameter_value }})/100;
			iva2	= ({{ App\Parameter::where('parameter_name','IVA2')->first()->parameter_value }})/100;
			totalImporte    = cant * precio;

			switch($('input[name="iva_kind"]:checked').val())
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
			totalImporte    = ((cant * precio)+ivaCalc);
			$('input[name="amount"]').val(totalImporte.toFixed(2));
		})
		.on('click','.delete-item',function()
		{
			$(this).parents('.tr-concepts').remove();
			countbody = $('#body .tr-concepts').length;
			if (countbody <= 0) 
			{
				$('#table,#table2').hide();
			}
			totalArticles();
		})
		.on('click','#addDoc',function()
		{
			@php
				$newdoc	= view('components.documents.upload-files',[
					"attributeExInput"		=> "type=\"file\" name=\"path\" accept=\".pdf,.jpg,.png\"",
					"classExInput" 			=> "pathActioner",
					"attributeExRealPath"	=> "type=\"hidden\" name=\"realPath[]\"",
					"classExRealPath"		=> "path",
					"classExDelete"			=> "delete-doc"
				])->render();
			@endphp
			newDocs = '{!!preg_replace("/(\r)*(\n)*/", "", $newdoc)!!}';
			newdoc 	= $(newDocs);
			$('#documents').append(newdoc).removeClass('hidden').addClass('grid');
		})
		.on('change','.pathActioner',function(e)
		{
			filename		= $(this);
			uploadedName 	= $(this).parent('.uploader-content').siblings('input[name="realPath[]"]');
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
					url			: '{{ route("warehouse.upload") }}',
					data		: formData,
					contentType	: false,
					processData	: false,
					success		: function(r)
					{
						if(r.error=='DONE')
						{
							$(e.currentTarget).removeAttr('style').parent('.uploader-content').removeClass('loading').addClass('image_success');
							$(e.currentTarget).parent('.uploader-content').siblings('input[name="realPath[]"]').val(r.path);
						}
						else
						{
							swal('',r.message, 'error');
							$(e.currentTarget).removeAttr('style').parent('.uploader-content').removeClass('loading');
							$(e.currentTarget).val('');
							$(e.currentTarget).parent('.uploader-content').siblings('input[name="realPath[]"]').val('');
						}
					},
					error: function()
					{
						swal('', 'Ocurrió un error durante la carga del archivo, intente de nuevo, por favor', 'error');
						$(e.currentTarget).removeAttr('style').parent('.uploader-content').removeClass('loading');
						$(e.currentTarget).val('');
						$(e.currentTarget).parent('.uploader-content').siblings('input[name="realPath[]"]').val('');
					}
				})
			}
		})
		.on('click','.delete-doc',function()
		{
			swal(
			{
				icon	: '{{ asset(getenv('LOADING_IMG')) }}',
				button	: false
			});
			actioner		= $(this);
			uploadedName	= $(this).parents('.docs-p').find('input[name="realPath[]"]');
			formData		= new FormData();
			formData.append(uploadedName.attr('name'),uploadedName.val());
			$.ajax(
			{
				type		: 'post',
				url			: '{{ route("warehouse.upload") }}',
				data		: formData,
				contentType	: false,
				processData	: false,
				success		: function(r)
				{
					swal.close();
					actioner.parents('.docs-p').remove();
				},
				error		: function()
				{
					swal.close();
					actioner.parents('.docs-p').remove();
				}
			});
			$(this).parents('.docs-p').remove();
		})
		.on('click','.edit-item',function()
		{
			$('input[name="amount"]').removeClass("error");
			$('input[name="quantity"]').removeClass("error");
			$('input[name="uamount"]').removeClass("error");
			$('input[name="quantity_not_damaged"]').removeClass("error");
			$('input[name="damaged"]').removeClass("error");

			account_id 			 = $(this).parents('.tr-concepts').find('.taccount_id').val();
			category_id 		 = $(this).parents('.tr-concepts').find('.tcategory').val();
			category_id_text 	 = $(this).parents('.tr-concepts').find('.class-category-name').text();
			place_id 			 = $(this).parents('.tr-concepts').find('.tplace_id').val();
			place_id_text 		 = $(this).parents('.tr-concepts').find('.class-place-name').text();
			measurement_id 	     = $(this).parents('.tr-concepts').find('.tmeasurement').val();
			concept 			 = $(this).parents('.tr-concepts').find('.tconcept').val();
			concept_name_id      = $(this).parents('.tr-concepts').find('.tconcept_id').val();
			type				 = $(this).parents('.tr-concepts').find('.ttype').val();
			brand 		         = $(this).parents('.tr-concepts').find('.tbrand').val();
			storage 	         = $(this).parents('.tr-concepts').find('.tstorage').val();
			processor            = $(this).parents('.tr-concepts').find('.tprocessor').val();
			ram 			     = $(this).parents('.tr-concepts').find('.tram').val();
			sku					 = $(this).parents('.tr-concepts').find('.tsku').val();
			cant                 = $(this).parents('.tr-concepts').find('.tquanty').val();
			amount 			     = $(this).parents('.tr-concepts').find('.tuamount').val();
			quantityNoDamaged 	 = $(this).parents('.tr-concepts').find('.tquantityNoDamaged').val();
			damaged 			 = $(this).parents('.tr-concepts').find('.tdamaged').val();
			importe 		     = $(this).parents('.tr-concepts').find('.timporte').val();
			short_code 	         = $(this).parents('.tr-concepts').find('.tshort_code').val();
			long_code 	         = $(this).parents('.tr-concepts').find('.tlong_code').val();
			comm 				 = $(this).parents('.tr-concepts').find('.tcommentaries').val();
			ivaKind    			 = $(this).parents('.tr-concepts').find('.tiva_kind').val();

			$('.js-category').append(new Option(category_id_text, category_id, true, true)).trigger('change');
			$('.js-places').append(new Option(place_id_text, place_id, true, true)).trigger('change');
			$('.js-measurement').val(measurement_id).trigger('change');
			$('input[name="concept_name"]').val(concept);
			$('input[name="concept_name_id"]').val(concept_name_id);
			$('input[name="quantity"]').val(cant);
			$('input[name="quantity_not_damaged"]').val(quantityNoDamaged);
			$('input[name="damaged"]').val(damaged);
			$('input[name="short_code"]').val(short_code);
			$('input[name="long_code"]').val(long_code);
			$('input[name="uamount"]').val(amount);
			$('input[name="amount"]').val(importe);
			$('textarea[id="commentaries"]').val( comm === "Sin comentarios" ? "" : comm );
			if(type != "")
			{
				$('input:radio[name="type"]').filter('[value='+type+']').prop('checked', true);
			}
			$('input[name="brand"]').val(brand);
			$('input[name="storage"]').val(storage);
			$('input[name="processor"]').val(processor);
			$('input[name="ram"]').val(ram);
			$('input[name="sku"]').val(sku);
			$('input:radio[name=iva_kind]').filter('[value='+ivaKind+']').prop('checked', true);
			$(this).parents('.tr-concepts').remove();
			if($('#body .tr-concepts').length > 0)
			{
				$('.edit-item').attr('disabled', true);
			}
			else
			{
				$('#table').hide();
				$('#table2').hide();
			}
			totalArticles();
			setTimeout(function()
			{ 
				$('.js-accounts').val(account_id).trigger('change');
			}, 500);
		})
		.on('click','.button-search', function()
		{
			send_search(1);
		})
		.on('click','.paginateSearch a', function(e)
		{
			e.preventDefault();
			href	=	$(this).attr('href');
			url		=	new URL(href);
			params	=	new URLSearchParams(url.search);
			page	=	params.get('page');
			send_search(page);
		})
		.on('click', '.edit', function()
		{
			$('.js-category').html('');
			$('.js-places').html('');
			search_measurement_id	= $('.search_lot_'+  $(this).val()  ).find('.search_measurement_id').val()
			search_place_id			= $('.search_lot_'+  $(this).val()  ).find('.search_place_id').val()
			search_place_id_text	= $('.search_lot_'+  $(this).val()  ).find('.place_location_text').text()
			search_measurement		= $('.search_lot_'+  $(this).val()  ).find('.search_measurement').val()
			search_type				= $('.search_lot_'+  $(this).val()  ).find('.search_type').text()
			search_concept			= $('.search_lot_'+  $(this).val()  ).find('.search_concept').val()
			search_short_code		= $('.search_lot_'+  $(this).val()  ).find('.search_short_code').val()
			search_long_code		= $('.search_lot_'+  $(this).val()  ).find('.search_long_code').val()
			search_price			= $('.search_lot_'+  $(this).val()  ).find('.search_price').val()
			search_warehouseType	= $('.search_lot_'+  $(this).val()  ).find('.search_warehouseType').val()
				
			$('input[name="concept_name_id"]').val($(this).val());
			$('.js-category').append(new Option(search_type, search_warehouseType, true, true)).trigger('change');
			if(search_place_id != "")
			{
				$('.js-places').append(new Option(search_place_id_text, search_place_id, true, true)).trigger('change');
			}
			$('.js-measurement').val(search_measurement_id).trigger('change');
			$('input[name="concept_name"]').val(search_concept);
			$('input[name="short_code"]').val(search_short_code === 'Sin SKU' ? "" : search_short_code);
			$('input[name="long_code"]').val(search_long_code);
			$('input[name="quantity"]').val("");
			$('input[name="uamount"]').val(Number(search_price));
			$('input[name="amount"]').val("");
			$('textarea[id="commentaries"]').val("");
			$('input[name="amount"]').removeClass("error");

			if(!(typeof editArticle !== 'undefined'))
			{
				add_required_fields()
				show_edit_button()
			}
			$('#table-search-container').slideUp();
		})
		.on('change','.js-enterprises',function()
		{
			$enterprise = $(this).val();
			search_accounts($enterprise)
		})
		.on('change','.js-category',function()
		{
			$('.js-accounts').html("");
			$enterprise = $('select[name="enterprise_id"] option:selected').val();
			if($enterprise)
			{
				search_accounts($enterprise);
			}
			switch ($('select[name="category_id"] option:selected').val())
			{
				case "1":
				case "2":
				case "3":
					$('.options-computer').addClass('hidden');
					$('input[name="type"]:checked').prop('checked', false);
					$('input[name="brand"]').val("");
					$('input[name="storage"]').val("");
					$('input[name="processor"]').val("");
					$('input[name="ram"]').val("");
					$('input[name="sku"]').val("");
					break;
				case "4":
					$('.options-computer').removeClass('hidden');
					break;
			}
		})
		.on('change','.quantity_not_damaged,.damaged', function()
		{
			nd 	= Number($('input[name="quantity_not_damaged"]').val());
			d 	= Number($('input[name="damaged"]').val());
			t 	= nd + d
			if(Number(t).toFixed(2) == "NaN")
			{
				$(this).val(0);
				swal('','Ingrese solo datos numéricos', 'error');
			}
			else 
			{
				$('input[name="quantity"]').val(t);
			}
			cant	= $('input[name="quantity"]').val();
			precio	= $('input[name="uamount"]').val();
			iva		= ({{ App\Parameter::where('parameter_name','IVA')->first()->parameter_value }})/100;
			iva2	= ({{ App\Parameter::where('parameter_name','IVA2')->first()->parameter_value }})/100;
			totalImporte    = cant * precio;

			switch($('input[name="iva_kind"]:checked').val())
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
			totalImporte    = ((cant * precio)+ivaCalc);
			$('input[name="amount"]').val(totalImporte.toFixed(2));
			$('input[name="quantity"]').val(t);
		})
		.on('change','.pathActionerPurchase',function(e)
		{
			filename		= $(this);
			uploadedName 	= $(this).parents('#content_massive').find('input[name="realPathPurchase"]');
			extention		= /\.csv/i;
			formData		= new FormData();
			formData.append(filename.attr('name'), filename.prop("files")[0]);
			formData.append(uploadedName.attr('name'),uploadedName.val());
			$.ajax(
			{
				type		: 'post',
				url			: '{{ route('warehouse.upload-csv') }}',
				data		: formData,
				contentType	: false,
				processData	: false,
				success		: function(r)
				{
					if(r.error=='DONE')
					{
						$(e.currentTarget).removeAttr('style').parent('.uploader-content').removeClass('loading').addClass('image_'+r.extention);
						$(e.currentTarget).parents('#content_massive').find('input[name="realPathPurchase"]').val(r.path);
					}
					else
					{
						swal('',r.message, 'error');
						$(e.currentTarget).removeAttr('style').parent('.uploader-content').removeClass('loading');
						$(e.currentTarget).val('');
						$(e.currentTarget).parents('#content_massive').find('input[name="realPathPurchase"]').val('');
					}
				},
				error: function()
				{
					swal('', 'Ocurrió un error durante la carga del archivo, intente de nuevo, por favor', 'error');
					$(e.currentTarget).removeAttr('style').parent('.uploader-content').removeClass('loading');
					$(e.currentTarget).val('');
					$(e.currentTarget).parent('.uploader-content').siblings('input[name="realPathPurchase"]').val('');
				}
			})
		})
		.on('click','[data-target="#uploadFile"]',function()
		{
			$('[name="csv_file"]').addClass('pathActionerPurchase');
			$('#separatorComa').prop('checked',true);
		})
		.on('click','[name="borra"]',function(e)
		{
			e.preventDefault();
			swal({
				text		: "Al borrar se eliminarán los artículos ya agregados en la lista y los documentos ya cargados. \n¿Desea continuar?",
				icon		: "warning",
				buttons		: ["Cancelar","OK"],
				dangerMode	: true,
			})
			.then((continuar) =>
			{
				if(continuar)
				{
					$('#body').html('');
					$('#table').hide();
					$('#table2').hide();
					$('.removeselect').val(null).trigger('change');
					$('.general-class').text('');
					$('#container-alta')[0].reset();
					clean_button();
					totalArticles();
					$('#table-search-container').hide();
					flag = false;
					$('[name="realPath[]"]').each(function()
					{
						actioner = $(this);
						if(actioner.val() != '')
						{
							formData = new FormData();
							formData.append(actioner.attr('name'),actioner.val());
							$.ajax(
							{
								type		: 'post',
								url			: '{{ route("warehouse.upload") }}',
								data		: formData,
								contentType	: false,
								processData	: false,
							});
						}
						flag = true;
					});
					if(flag)
					{
						$('#documents').html('');
					}
				}
				else
				{
					swal.close();
				}
			});
		});
	});

	function add_required_fields()
	{
		@php
			$selects = collect([
				[
					"identificator"          => ".js-measurement", 
					"placeholder"            => "Seleccione la unidad de medida", 
					"maximumSelectionLength" => "1",
					"width"					 => "100%"
				]
			]);
		@endphp
		@component("components.scripts.selects",["selects" => $selects]) @endcomponent
		generalSelect({'selector' : '.js-category', 'model' : 56});
		generalSelect({'selector' : '.js-places', 'model' : 38});
		$('.js-category').attr('disabled',true);
		$('.js-places').attr('disabled',true);
		$('.js-measurement').attr('disabled',true);
		$('input[name="concept_name"]').attr('disabled',true);
		$('input[name="short_code"]').attr('disabled',true);
		$('input[name="long_code"]').attr('disabled',true);
		$('input[name="uamount"]').attr('disabled',true);
	}
	function remove_required_fields()
	{
		@php
			$selects = collect([
				[
					"identificator"          => ".js-measurement", 
					"placeholder"            => "Seleccione la unidad de medida", 
					"maximumSelectionLength" => "1",
					"width"					 => "100%"
				]
			]);
		@endphp
		@component("components.scripts.selects",["selects" => $selects]) @endcomponent
		generalSelect({'selector' : '.js-category', 'model' : 56});
		generalSelect({'selector' : '.js-places', 'model' : 38});
		$('.js-category').attr('disabled',false);
		$('.js-places').attr('disabled',false);
		$('.js-measurement').attr('disabled',false);
		$('input[name="concept_name"]').attr('disabled',false)
		$('input[name="short_code"]').attr('disabled',false)
		$('input[name="long_code"]').attr('disabled',false)
		$('input[name="uamount"]').attr('disabled',false)
	}
	function send_search(page)
	{
		swal(
		{
			icon: '{{ asset(getenv('LOADING_IMG')) }}',
			button	: false,
		});
    	concept = $('input[name="search"]').val();
		$.ajax(
		{
			type : 'post',
			url  : '{{ route("warehouse.inventory.search_w") }}',
			data :
			{
				'concept'		: concept,
				'page'			: page,
				'category_id'	: [$('select[name="category_id"] option:selected').val()]
			},
			success : function(data)
			{
				$('#table-search-container').html(data);
				$('#table-search-container').slideDown();
				swal.close();
      		},
			error: function()
			{
				swal.close();
			}
		});
	}
	function search_accounts($enterprise,first = false,type='normal',id=0)
	{
		if(!removeAccountsCompra)
		{
			return;
		}
		idAccAcc      = Number($('#current_account_id').val());
		warehouseType = '';
		switch (type)
		{
			case 'normal':
				warehouseType = $('select[name="category_id"] option:selected').val()
				generalSelect({'selector':'.js-accounts', 'depends':'[name="category_id"]', 'model':57, 'warehouseType':warehouseType});
				break;
			case 'masiva':
				warehouseType = $('#category_id_masiva option:selected').val()
				generalSelect({'selector':'.js-accounts-masiva','depends':'#category_id_masiva','model':57,'warehouseType':warehouseType});
				break;
			case 'compras':
				warehouseType = $('.js-category-edit-'+id+' option:selected').val()
				if(!first)
					$('.js-account-edit-'+id).empty();
				else
					idAccAcc = Number($('.js-account-edit-'+id+' option:selected').val());
				break;
			default:
				break;
		}
	}
	function show_edit_button()
	{
		$('#edit_button').show();
	}
	function hidde_clean_button()
	{
		$('#clean_button').attr('hidden',true)
	}
	function hidde_edit_button()
	{
		$('#edit_button').hide()
	}
	function edit_material_button()
	{
		swal({
			title: "¿Editar artículo?",
			text: "Si el concepto es modificado se agregará un artículo nuevo.",
			icon: "warning",
			buttons: ["Cancelar","OK"],
			dangerMode: true,
			buttons: ["Cancelar", "Aceptar"],
		})
		.then((edit) =>
		{
			if (edit)
			{
				hidde_edit_button()
				remove_required_fields()
				$('input[name="concept_name_id"]').val("");
			}
		});
	}
	function clean_button()
	{
		hidde_edit_button()
		$('.js-category').val(null).trigger('change');
		$('.js-measurement').val(null).trigger('change');
		$('.js-places').val(null).trigger('change');
		$('input[name="concept_name"]').val("");
		$('input[name="concept_name_id"]').val("");
		$('input[name="short_code"]').val("");
		$('input[name="long_code"]').val("");
		$('input[name="quantity"]').val("");
		$('input[name="quantity_not_damaged"]').val("");
		$('input[name="damaged"]').val("");
		$('input[name="uamount"]').val("");
		$('input[name="amount"]').val("");
		$('textarea[id="commentaries"]').val("");
		remove_required_fields()
		$('input[name="amount"]').removeClass("error");
		$('input[name="quantity"]').removeClass("error");
		$('input:radio[name=iva_kind]').filter('[value="no"]').prop('checked', true);
		hidde_edit_button();
	}
	function totalArticles()
	{
		var sumatotal 			= 0;
		var sub_total_articles	= 0;
		var iva_articles		= 0;
		
		$('#body').find('.tr-concepts').each(function()
		{
			valor			= Number($(this).find('.timporte').val());
			sumatotal		= sumatotal + valor ;
			iva				= Number($(this).find('.tiva').val());
			iva_articles	= iva_articles + iva ;
			sub_total_articles = sub_total_articles + ( Number($(this).find('.tquanty').val()) * Number($(this).find('.tuamount').val())) 
		});
		$('input[name="total_articles"]').val(Number(sumatotal).toFixed(2));
		$('input[name="iva_articles"]').val(Number(iva_articles).toFixed(2));
		$('input[name="sub_total_articles"]').val(Number(sub_total_articles).toFixed(2));
		$('.subTotalLabel').text('$ '+Number(sub_total_articles).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));
		$('.ivaLabel').text('$ '+Number(iva_articles).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));
		$('.totalLabel').text('$ '+Number(sumatotal).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));
	}
	function updateSelectsAlta()
	{
		@php
			$selects = collect([
				[
					"identificator"          => ".js-enterprises", 
					"placeholder"            => "Seleccione la empresa", 
					"maximumSelectionLength" => "1"
				],
				[
					"identificator"          => ".js-measurement", 
					"placeholder"            => "Seleccione la unidad de medida", 
					"maximumSelectionLength" => "1"
				],
				[
					"identificator"          => ".js-enterprises_masiva", 
					"placeholder"            => "Seleccione la empresa", 
					"maximumSelectionLength" => "1"
				],
				[
					"identificator"          => ".js-measurement_masiva", 
					"placeholder"            => "Seleccione la unidad de medida", 
					"maximumSelectionLength" => "1"
				],
				[
					"identificator"          => ".js-enterprises_compras", 
					"placeholder"            => "Seleccione la empresa", 
					"maximumSelectionLength" => "1"
				],
				[
					"identificator"          => ".js-account-a",
					"placeholder"            => "Seleccione una cuenta", 
					"maximumSelectionLength" => "1"
				],
				[
					"identificator"          => ".js-measurement-a",
					"placeholder"            => "Seleccione la unidad", 
					"maximumSelectionLength" => "1"
				]
			]);
		@endphp
		@component("components.scripts.selects",["selects" => $selects]) @endcomponent
		generalSelect({'selector' : '.js-category', 'model' : 56});
		generalSelect({'selector' : '.js-places', 'model' : 38});
		generalSelect({'selector' : '.js-category-masiva', 'model' : 56});
		generalSelect({'selector' : '.js-accounts-masiva','depends':'#category_id_masiva','model':57});
		generalSelect({'selector' : '.js-places_masiva', 'model' : 38});
		generalSelect({'selector' : '.js-places_compra', 'model' : 38});
		generalSelect({'selector' : '.js-category-a', 'model' : 56});

		$('.js-concept').select2(
		{
			placeholder: 'Seleccione el Artículo',
			language: "es",
			maximumSelectionLength: 1,
		})
		.on("change",function(e)
		{
			if($(this).val().length>1)
			{
				$(this).val($(this).val().slice(0,1)).trigger('change');
			}
		});
		$('.js-category_masiva').select2(
		{
			placeholder: 'Seleccione la categoría',
			language: "es",
			maximumSelectionLength: 1,
		})
		.on("change",function(e)
		{
			if($(this).val().length>1)
			{
				$(this).val($(this).val().slice(0,1)).trigger('change');
			}
		});
		$('.js-measurement_compras').select2(
		{
			placeholder: 'Seleccione la unidad de medida',
			language: "es",
			maximumSelectionLength: 1,
		})
		.on("change",function(e)
		{
			if($(this).val().length>1)
			{
				$(this).val($(this).val().slice(0,1)).trigger('change');
			}
		});
	}
</script>
