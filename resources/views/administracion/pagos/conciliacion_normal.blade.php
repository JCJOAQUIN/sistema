@extends('layouts.child_module')
@section('data')
	<div class="mx-auto w-full md:w-1/2 grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center bg-gray-100 py-4 rounded rounded-lg">
		@component("components.labels.label", ["classEx" => "font-semibold"]) Tipo de Conciliación: @endcomponent
		@component('components.buttons.button-link', ["variant" => "red"])
			@slot('classEx')
				bg-red-400
				border-none
				text-white
				shadow-md
			@endslot
			@slot('attributeEx')
				href="{{ route('payments.conciliation-normal.create') }}"
			@endslot
			Normal
		@endcomponent
		@component('components.buttons.button-link', ["variant" => "reset"])
			@slot('classEx')
				sub-block
			@endslot
			@slot('attributeEx')
				href="{{ route('payments.conciliation-nomina.create') }}"
			@endslot
			De nómina
		@endcomponent
	</div>
	@component("components.forms.form", ["attributeEx" => "action=\"".route('payments.conciliation.store')."\" method=\"POST\" id=\"container-alta\"", "files" => true])
		@component("components.containers.container-form")
			<div class="md:col-start-2 col-span-2 md:col-end-4">
				@component('components.labels.label')  Seleccione el año: @endcomponent
				@php
					$options = collect();
					$options = $options->concat([["value"=>"all", "description"=>"Todos"]]);
					for($y=2018;$y<=date("Y"); $y++)
					{
						$options = $options->concat([["value"=>$y, "description"=>$y]]);
					}
					$attributeEx = "id=\"year\"";
					$classEx = "js-years";
				@endphp
				@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])
				@endcomponent
			</div>
			<div class="md:col-start-2 col-span-2 md:col-end-4">
				@component('components.labels.label')  Seleccione el mes: @endcomponent
				@php
					$options = collect();
					$options = $options->concat([['value'=>"all", 'description'=>'Todos']]);
					$month = array('','Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre');
					for($m=1;$m<=12;$m++)
					{
						$options = $options->concat([['value'=>$m, 'description'=>$month[$m]]]);
					}
					$attributeEx = "id=\"month\"";
					$classEx = "js-months";
				@endphp
				@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])
				@endcomponent
			</div>
		@endcomponent
		@component("components.containers.container-form")
			<div class="md:col-start-2 col-span-2 md:col-end-4 text-center">
				Seleccione un
				@component("components.labels.label")
					@slot("classEx")
						font-bold
						inline-block
					@endslot
					Movimiento del Sistema <span class="help-btn" id="help-btn"></span>
				@endcomponent
				@component('components.labels.label')
					(Para ordenar la información dé clic en cada cabecera)
				@endcomponent
				<div class="relative">
					@component("components.inputs.input-text")
						@slot("attributeEx")
							id="search-sys"
						@endslot
					@endcomponent
					<div class="placeholder pointer-events-none text-true-gray-400 text-xl absolute bottom-0 left-0 right-0 m-0 mb-2">
						<span class="icon-search"></span> Buscar por descripción
					</div>
				</div>
			</div>
		@endcomponent
		<div class="px-2">
			@php
				$modelHead =
				[
					[
						["value" => "Tipo <span class='icon-arrow-up'></span>", "classEx" => "arrow", "attributeEx" => "data-sort=\"kind\""],
						["value" => "Empresa <span class='icon-arrow-up'></span>", "classEx" => "arrow", "attributeEx" => "data-sort=\"enterprise\""],
						["value" => "Cuenta <span class='icon-arrow-up'></span>", "classEx" => "arrow", "attributeEx" => "data-sort=\"account\""],
						["value" => "Monto <span class='icon-arrow-up'></span>", "classEx" => "arrow", "attributeEx" => "data-sort=\"amount\""],
						["value" => "Fecha <span class='icon-arrow-up'></span>", "classEx" => "arrow", "attributeEx" => "data-sort=\"date\""],
						["value" => "Descripción <span class='icon-arrow-up'></span>", "classEx" => "arrow", "attributeEx" => "data-sort=\"description\""],
					]
				];
				$modelBody = [];
			@endphp
			@component('components.tables.table',[
				"modelHead" => $modelHead,
				"modelBody" => $modelBody,
				])
				@slot('attributeEx')
				id="system-move"
				@endslot
				@slot('classEx')
				table-move
				@endslot
				@slot('attributeExBody')
				id="body-pay"
				@endslot
				@slot('classExBody')
					tbody
				@endslot
			@endcomponent
			@component("components.inputs.input-text")
				@slot("attributeEx")
					type="hidden"
					name="idpayment"
					id="idpayment"
				@endslot
			@endcomponent
			@component("components.inputs.input-text")
				@slot("attributeEx")
					type="hidden"
					id="kindSortPayments"
					value="amount"
				@endslot
			@endcomponent
			@component("components.inputs.input-text")
				@slot("attributeEx")
					type="hidden"
					id="ascDescPayments"
					value="ASC"
				@endslot
			@endcomponent
			@component("components.inputs.input-text")
				@slot("attributeEx")
					type="hidden"
					id="pagePayments"
					value="{{ route("payments.conciliation.search") }}"
				@endslot
			@endcomponent
		</div>
		@component("components.inputs.input-text")
			@slot("attributeEx")
				type="hidden"
				name="idFolio"
				id="idFolio"
			@endslot
		@endcomponent
		<div id="idmovements">
		</div>
		@component("components.containers.container-form")
			<div class="md:col-start-2 col-span-2 md:col-end-4 text-center">
				Seleccione un
				@component("components.labels.label")
					@slot("classEx")
						font-bold
						inline-block
					@endslot
					Movimiento Bancario
				@endcomponent
				@component('components.labels.label')
					(Para ordenar la información dé clic en cada cabecera)
				@endcomponent
				<div class="relative">
					@component("components.inputs.input-text")
					@slot("attributeEx")
						type="text"
						id="search-bank"
					@endslot
					@endcomponent
					<div class="placeholder pointer-events-none text-true-gray-400 text-xl absolute bottom-0 left-0 right-0 m-0 mb-2">
						<span class="icon-search"></span> Buscar por descripción
					</div>
				</div>
			</div>
		@endcomponent
		<div class="px-2">
			@php
				$modelHead =
				[
					[
						["value" => "Empresa <span class='icon-arrow-up'></span>", "classEx" => "arrow", "attributeEx" => "data-sort=\"enterprise\""],
						["value" => "Cuenta <span class='icon-arrow-up'></span>", "classEx" => "arrow", "attributeEx" => "data-sort=\"account\""],
						["value" => "Monto <span class='icon-arrow-up'></span>", "classEx" => "arrow", "attributeEx" => "data-sort=\"amount\""],
						["value" => "Fecha <span class='icon-arrow-up'></span>", "classEx" => "arrow", "attributeEx" => "data-sort=\"date\""],
						["value" => "Descripción <span class='icon-arrow-up'></span>", "classEx" => "arrow", "attributeEx" => "data-sort=\"description\""],
						["value" => "Tipo <span class='icon-arrow-up'></span>", "classEx" => "arrow", "attributeEx" => "data-sort=\"kind\""],
					]
				];
				$modelBody = [];
			@endphp
			@component('components.tables.table',[
				"modelHead" => $modelHead,
				"modelBody" => $modelBody,
			])
				@slot('attributeEx')
					id="bank-move"
				@endslot
				@slot('classEx')
					table-move
				@endslot
				@slot('attributeExBody')
					id="body-move"
				@endslot
				@slot('classExBody')
					tbody
				@endslot
			@endcomponent
			@component("components.inputs.input-text")
				@slot("attributeEx")
					type="hidden"
					id="kindSortMovements"
					value="amount"
				@endslot
			@endcomponent
			@component("components.inputs.input-text")
				@slot("attributeEx")
					type="hidden"
					id="ascDescMovements"
					value="ASC"
				@endslot
			@endcomponent
			@component("components.inputs.input-text")
				@slot("attributeEx")
					type="hidden"
					id="pageMovements"
					value="{{ route("payments.conciliation.search") }}"
				@endslot
			@endcomponent
		</div>
		@component("components.modals.modal",[ "variant" => "large" ])
			@slot("id")
				myModal
			@endslot
			@slot("attributeEx")
				tabindex="-1"
			@endslot
			@slot("modalHeader")
				@component("components.buttons.button")
					@slot("attributeEx")
						type="button"
						data-dismiss="modal"
					@endslot
					@slot('classEx')
						close
					@endslot
					<span aria-hidden="true">&times;</span>
				@endcomponent
			@endslot
			@slot("modalBody")

			@endslot
		@endcomponent
		<div class="text-center my-6">
			@component("components.buttons.button", ["variant"=>"primary"])
				@slot("classEx")
					w-48
					md:w-auto
					text-center
					enviar
				@endslot
				@slot("attributeEx")
					type="submit"
					value="Conciliar"
					name="send"
				@endslot
				CONCILIAR
			@endcomponent
		</div>
	@endcomponent
@endsection
@section('scripts')
<script src="{{ asset('js/jquery.tablesorter.combined.js') }}"></script>
<script>
	$(document).ready(function()
	{
		@php
			$selects = collect([
				[
					"identificator"          => ".js-years",
					"placeholder"            => "Seleccione el año",
					"maximumSelectionLength" => "1"
				],
				[
					"identificator"          => ".js-months",
					"placeholder"            => "Seleccione el mes",
					"maximumSelectionLength" => "1"
				]
			])
		@endphp
		@component("components.scripts.selects",["selects" => $selects]) @endcomponent
		$('#bank-move').tablesorter();
		$('#system-move').tablesorter();
		$('#bank-move-nomina').tablesorter();
		$('#system-move-nomina').tablesorter();
		$(document).on('click','#system-move .tr',function()
		{
			$(this).parents('#system-move').find('.selected').removeClass('selected');
			$(this).addClass('selected');
		})
		.on('dblclick','#system-move .tr',function()
		{
			$('#myModal .modal-body').html('');
			$(this).addClass('selected');
			folio = $(this).find('.folio').val();
			$.ajax(
			{
				type : 'post',
				url  : '{{ route("payments.conciliation.detail") }}',
				data : {'folio':folio},
				success : function(data)
				{
					$('#myModal .modal-body').html(data);
					$('#myModal').modal('show');
				},
				error: function(data)
				{
					$('#myModal').hide();
					$('.detail').removeAttr('disabled');
					$('.modal-backdrop').remove();
					swal('','Sucedió un error, por favor intente de nuevo.','error');
				}
			})
		})
		.on('click','#bank-move .tr',function()
		{
			$(this).addClass('selected');
		})
		.on('click','.tr.selected',function()
		{
			$(this).removeClass('selected');
		})
		.on('change','#year,#month',function()
		{
			year  = $('#year option:selected').val();
			month = $('#month option:selected').val();
			$('#system-move .tbody,#bank-move .tbody').html('');
			dataSearch = [];
			if(year != null && month != null)
			{
				swal({
					icon               : '{{ asset(getenv('LOADING_IMG')) }}',
					button             : false,
					closeOnClickOutside: false,
					closeOnEsc         : false
				});
				dataSearch.push({'name':'year','value':year});
				dataSearch.push({'name':'month','value':month});
				dataSearch.push({'name':'url','value':'{{ route("payments.conciliation.search") }}'});
				dataSearch.push({'name':'table','value':'all'});
				dataSearch.push({'name':'kindSort','value':''});
				dataSearch.push({'name':'ascDesc','value':''});
				dataSearch.push({'name':'search','value':''});
				dataSearch.push({'name':'selected','value':''});
				searchConciliation(dataSearch);
			}
		})
		.on('click','.result_pagination a',function(e)
		{
			e.preventDefault();
			swal({
				icon               : '{{ asset(getenv('LOADING_IMG')) }}',
				button             : false,
				closeOnClickOutside: false,
				closeOnEsc         : false
			});
			href    = $(this).attr('href');
			url     = new URL(href);
			dataSearch = [];
			dataSearch.push({'name':'year','value':$('#year option:selected').val()});
			dataSearch.push({'name':'month','value':$('#month option:selected').val()});
			dataSearch.push({'name':'url','value':url});
			if($(this).parents('.table-move').attr('id') == 'system-move')
			{
				table    = 'payments';
				kindSort = $('#kindSortPayments').val();
				ascDesc  = $('#ascDescPayments').val();
				search   = $('#search-sys').val();
				selected = [];
				$("#system-move").find('.selected').each(function() {
					selected.push($(this).find('.idpayment').val());
				});
				$('#pagePayments').val(url);
			}
			else
			{
				table    = 'movements';
				kindSort = $('#kindSortMovements').val();
				ascDesc  = $('#ascDescMovements').val();
				search   = $('#search-bank').val();
				selected = [];
				$("#bank-move").find('.selected').each(function() {
					selected.push($(this).find('.idmovement').val());
				});
				$('#pageMovements').val(url);
			}
			dataSearch.push({'name':'table','value':table});
			dataSearch.push({'name':'kindSort','value':kindSort});
			dataSearch.push({'name':'ascDesc','value':ascDesc});
			dataSearch.push({'name':'search','value':search});
			dataSearch.push({'name':'selected','value':selected});
			searchConciliation(dataSearch);
		})
		.on('click','.arrow',function()
		{
			if($(this).parents('.table-move').find('.tr').length>0)
			{
				swal({
					icon               : '{{ asset(getenv('LOADING_IMG')) }}',
					button             : false,
					closeOnClickOutside: false,
					closeOnEsc         : false
				});
				dataSearch = [];
				dataSearch.push({'name':'year','value':$('#year option:selected').val()});
				dataSearch.push({'name':'month','value':$('#month option:selected').val()});
				kindSort = $(this).attr('data-sort');
				if($(this).children('span').hasClass('icon-arrow-up'))
				{
					ascDesc = 'ASC';
					$(this).children('span').removeClass('icon-arrow-up').addClass('icon-arrow-down');
				}
				else
				{
					ascDesc = 'DESC';
					$(this).children('span').removeClass('icon-arrow-down').addClass('icon-arrow-up');
				}

				if($(this).parents('.table-move').attr('id') == 'system-move')
				{
					$('#ascDescPayments').val(ascDesc);
					$('#kindSortPayments').val(kindSort);
					search = $('#search-sys').val();
					url    = $('#pagePayments').val();
					table  = 'payments';
					selected = [];
					$("#system-move").find('.selected').each(function() {
						selected.push($(this).find('.idpayment').val());
					});
				}
				else
				{
					$('#ascDescMovements').val(ascDesc);
					$('#kindSortMovements').val(kindSort);
					search = $('#search-bank').val();
					url    = $('#pageMovements').val();
					table  = 'movements';
					selected = [];
					$("#bank-move").find('.selected').each(function() {
						selected.push($(this).find('.idmovement').val());
					});
				}
				dataSearch.push({'name':'url','value':url});
				dataSearch.push({'name':'table','value':table});
				dataSearch.push({'name':'kindSort', 'value':kindSort});
				dataSearch.push({'name':'ascDesc','value':ascDesc});
				dataSearch.push({'name':'search','value':search});
				dataSearch.push({'name':'selected','value':selected});
				searchConciliation(dataSearch);
			}
		})
		.on('keyup','#search-sys',function()
		{
			text = $(this).val();
			dataSearch = [];
			dataSearch.push({'name':'year','value':$('#year option:selected').val()});
			dataSearch.push({'name':'month','value':$('#month option:selected').val()});
			dataSearch.push({'name':'url','value':'{{ route("payments.conciliation.search") }}'});
			dataSearch.push({'name':'table','value':'payments'});
			dataSearch.push({'name':'kindSort','value':$('#kindSortPayments').val()});
			dataSearch.push({'name':'ascDesc','value':$('#ascDescPayments').val()});
			selected = [];
			$("#system-move").find('.selected').each(function() {
				selected.push($(this).find('.idpayment').val());
			});
			if(text != "")
			{
				$(this).parent().find(".placeholder").hide();
				dataSearch.push({'name':'search','value':text});
			}
			else
			{
				$(this).parent().find(".placeholder").show();
				dataSearch.push({'name':'search','value':''});
			}
			dataSearch.push({'name':'selected','value':selected});
			$('#pagePayments').val('{{ route("payments.conciliation.search") }}');
			searchConciliation(dataSearch);
		})
		.on('keyup','#search-bank',function()
		{
			text = $(this).val();
			dataSearch = [];
			dataSearch.push({'name':'year','value':$('#year option:selected').val()});
			dataSearch.push({'name':'month','value':$('#month option:selected').val()});
			selected = [];
			$("#bank-move").find('.selected').each(function() {
				selected.push($(this).find('.idmovement').val());
			});
			if(text != "")
			{
				$(this).parent().find(".placeholder").hide();
				dataSearch.push({'name':'url','value':'{{ route("payments.conciliation.search") }}'});
				dataSearch.push({'name':'table','value':'movements'});
				dataSearch.push({'name':'kindSort','value':$('#kindSortMovements').val()});
				dataSearch.push({'name':'ascDesc','value':$('#ascDescMovements').val()});
				dataSearch.push({'name':'search','value':text});
				dataSearch.push({'name':'selected','value':selected});
			}
			else
			{
				$(this).parent().find(".placeholder").show();
				dataSearch.push({'name':'url','value':'{{ route("payments.conciliation.search") }}'});
				dataSearch.push({'name':'table','value':'movements'});
				dataSearch.push({'name':'kindSort','value':$('#kindSortMovements').val()});
				dataSearch.push({'name':'ascDesc','value':$('#ascDescMovements').val()});
				dataSearch.push({'name':'search','value':''});
				dataSearch.push({'name':'selected','value':selected});
			}
			$('#pageMovements').val('{{ route("payments.conciliation.search") }}');
			searchConciliation(dataSearch);
		})
		.on('click','.enviar',function (e)
		{
			$('#idmovements').empty();
			flag          = false;
			amountBank    = 0;
			idkind 		  = $('#system-move .tr.selected').find('.idkind').val();
			documento     = $('#system-move .tr.selected').find('.document').val();
			amountSys     = $('#system-move .tr.selected').find('.amount').val();
			enterprisePay = $('#system-move .tr.selected').find('.enterprisePay').val();
			flag = true;
			$('#bank-move .tr.selected').each(function()
			{
				total			= parseFloat($(this).find('.amount').val());
				amountBank		= amountBank+total;
				idmovement		= $(this).find('.idmovement').val();
				conciliation	= $('<input type="hidden" name="idmovement[]" value="'+idmovement+'">');
				$('#idmovements').append(conciliation);
				ent				= $(this).find('.enterpriseMov').val();
				if (enterprisePay != ent)
				{
					flag = false;
				}
			});

			if (flag)
			{
				enterpriseMov 	= $('#bank-move .tr.selected').find('.enterpriseMov').val();
			}
			else
			{
				enterpriseMov 	= null;
			}

			//PARA VERIFICAR LAS EMPRESAS SOLO USAR UNA BANDERA TRUE/FALSE... SI ES TRUE TOMA EL NOMBRE DE LA EMPRESA DE LA PRIMERA FILA DEL RECORRIDO DE LAS EMPRESAS QUE SE SELECCIONARON, DE NO SER ASI MANDAR EL MENSAKE DE LAS EMPRESAS NO COINCIDEN

			idpayment 	= $('#system-move .tr.selected').find('.idpayment').val();
			idFolio 	= $('#system-move .tr.selected').find('.folio').val();
			bill 		= $('#system-move .tr.selected').find('.billpurchase').val();
			$('#idpayment').val(idpayment);
			$('#idFolio').val(idFolio);
			e.preventDefault();
			if (idkind == "1")
			{
				if (bill == "")
				{
					swal({
						title: "Error",
						text: "El pago seleccionado no tiene factura de compra.",
						icon: "error",
						buttons:
						{
							confirm: true,
						},
					});
					return false;
				}
			}
			if (documento == '')
			{
				swal({
					title: "Error",
					text: "El movimiento seleccionado no tiene comprobante de pago.",
					icon: "error",
					buttons:
					{
						confirm: true,
					},
				});
			}
			if (Number(amountBank).toFixed(2) != Number(amountSys).toFixed(2))
			{
				swal({
					title: "Error",
					text: "Los importes no coinciden, por favor verifique que los movimientos seleccionados sean los correctos.",
					icon: "error",
					buttons:
					{
						confirm: true,
					},
				});
			}
			if (amountBank == null || amountSys == null || amountBank == "" || amountSys == "")
			{
				swal({
					title: "Error",
					text: "Por favor seleccione un movimiento y un pago.",
					icon: "error",
					buttons:
					{
						confirm: true,
					},
				});
			}
			if (amountBank != undefined || amountSys != undefined)
			{
				if (documento != '' && Number(amountBank).toFixed(2) == Number(amountSys).toFixed(2))
				{
					if (enterpriseMov != enterprisePay)
					{
						swal({
							title: "Error",
							text: "Las empresas son diferentes, solo se permite hacer conciliación con la misma empresa.",
							icon: "error",
							buttons:
							{
								confirm: true,
							},
						});
					}
					else
					{
						swal({
							title: "Confirmación",
							text: "¿Desea realizar la conciliación de los movimientos seleccionados?",
							icon: "warning",
							buttons: ["Cancelar","OK"],
						})
						.then((isConfirm) =>
						{
							if(isConfirm)
							{
								swal({
									icon               : '{{ asset(getenv('LOADING_IMG')) }}',
									button             : false,
									closeOnClickOutside: false,
									closeOnEsc         : false
								});
								$('#container-alta').submit();
							}
						});
					}
				}
			}
		})
		.on('click','#help-btn',function()
		{
			swal('Ayuda','Dé doble clic sobre un Movimiento del Sistema para mostrar más detalles.','info');
		})
		.on('click','#ver',function()
		{
			nameEmp           = $(this).parents('.tr').find('.name').val();
			lastnameEmp       = $(this).parents('.tr').find('.last_name').val();
			scnd_last_nameEmp = $(this).parents('.tr').find('.scnd_last_name').val();
			bankEmp           = $(this).parents('.tr').find('.bank').val();
			cardEmp           = $(this).parents('.tr').find('.cardNumber').val();
			accountEmp        = $(this).parents('.tr').find('.account').val();
			clabeEmp          = $(this).parents('.tr').find('.clabe').val();
			referenceEmp      = $(this).parents('.tr').find('.reference').val();
			amountEmp         = "$ "+$(this).parents('.tr').find('.importe').val();
			reason_paymentEmp = $(this).parents('.tr').find('.description').val();
			accounttext       = $(this).parents('.tr').find('.accounttext').val();
			enterprise    	  = $(this).parents('.tr').find('.enterprise').val();
			project           = $(this).parents('.tr').find('.project').val();
			area              = $(this).parents('.tr').find('.area').val();
			department        = $(this).parents('.tr').find('.department').val();
			if(accountEmp == '')
			{
				accountEmp = '-----';
			}

			if(cardEmp == '')
			{
				cardEmp = '-----';
			}

			if(clabeEmp == '')
			{
				clabeEmp = '-----';
			}

			$('#nameEmp').html(nameEmp+' '+lastnameEmp+' '+scnd_last_nameEmp);
			$('#idBanksEmp').html(bankEmp);
			$('#card_numberEmp').html(cardEmp);
			$('#accountEmp').html(accountEmp);
			$('#clabeEmp').html(clabeEmp);
			$('#referenceEmp').html(referenceEmp);
			$('#amountEmp').html(amountEmp);
			$('#reason_paymentEmp').html(reason_paymentEmp);
			$('#accounttext').html(accounttext);
			$('#enterprise').html(enterprise);
			$('#project').html(project);
			$('#area').html(area);
			$('#department').html(department);
			$(".dataEmployee").stop().slideToggle();
		})
		.on('click','#exit', function(){
			$(".dataEmployee").slideToggle();
		})
		.on('click','input[name="type_conciliation"]',function()
		{
			if($(this).val() == 1)
			{
				$('#conciliation_nomina').hide();
				$('#conciliation_normal').stop(true,true).slideDown().show();
			}
			if($(this).val() == 2)
			{
				$('#conciliation_normal').hide();
				$('#conciliation_nomina').stop(true,true).slideDown().show();
			}
		})
		.on('click','.exit',function()
		{
			$('#myModal_nomina').hide();
		})
	});

	function searchConciliation(dataSearch)
	{
		table = dataSearch[3]['value'];
		$.ajax(
		{
			type	: 'post',
			url		: dataSearch[2]['value'],
			data	: dataSearch,
			success	: function(data)
			{
				json = JSON.parse(data);
				if(table == 'payments')
				{
					$('#system-move').find('#body-pay').html(urldecode(json[0]));
					$('#system-move').trigger('updateAll');
				}
				else if (table == 'movements')
				{
					$('#bank-move').find('#body-move').html(urldecode(json[1]));
					$('#bank-move').trigger('updateAll');
				}
				else if (table == 'all')
				{
					$('#system-move').find('#body-pay').html(urldecode(json[0]));
					$('#system-move').trigger('updateAll');
					$('#bank-move').find('#body-move').html(urldecode(json[1]));
					$('#bank-move').trigger('updateAll');
				}
			},
			error: function(data)
			{
				swal('','Sucedió un error, por favor intente de nuevo.','error');
			}
		}).done(function(data)
		{
			swal.close();
		});
	}
</script>
@endsection