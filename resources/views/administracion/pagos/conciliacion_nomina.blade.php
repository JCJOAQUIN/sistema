@extends('layouts.child_module')
@section('data')
	<div class="mx-auto w-full md:w-1/2 grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center bg-gray-100 py-4 rounded rounded-lg">
		@component("components.labels.label", ["classEx" => "font-semibold"]) Tipo de Conciliación: @endcomponent
		@component('components.buttons.button-link', ["variant" => "red"])
			@slot('classEx')
				sub-block
			@endslot
			@slot('attributeEx')
				href="{{ route('payments.conciliation-normal.create') }}"
			@endslot
			Normal
		@endcomponent
		@component('components.buttons.button-link', ["variant" => "reset"])
			@slot('classEx')
				bg-gray-300
				border-none
				text-white
				shadow-md
			@endslot
			@slot('attributeEx')
				href="{{ route('payments.conciliation-nomina.create') }}"
			@endslot
			De nómina
		@endcomponent
	</div>
	@component("components.forms.form", ["attributeEx" => "action=\"".route('payments.conciliation.store.nomina')."\" method=\"POST\" id=\"container-alta-nomina\"", "files" => true])
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
					$attributeEx = "id=\"year_nomina\"";
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
					$attributeEx = "id=\"month_nomina\"";
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
					Movimiento de Nómina del Sistema <span class="help-btn" id="help-btn"></span>
				@endcomponent
				@component('components.labels.label')
					(Para ordenar la información dé clic en cada cabecera)
				@endcomponent
				<div class="relative">
					@component("components.inputs.input-text")
						@slot("attributeEx")
							type="text"
							id="search-sys-nomina"
						@endslot
					@endcomponent
					<div class="placeholder pointer-events-none text-true-gray-400 text-xl absolute bottom-0 left-0 right-0 m-0 mb-2">
						<span class="icon-search"></span> Buscar por empleado
					</div>
				</div>
			</div>
		@endcomponent
		<div class="px-2">
			@php
				$modelHead =
				[
					[
						["value" => "Folio <span class='icon-arrow-up'></span>", "classEx" => "arrow", "attributeEx" => "data-sort=\"folio\""],
						["value" => "Empresa <span class='icon-arrow-up'></span>", "classEx" => "arrow", "attributeEx" => "data-sort=\"enterprise\""],
						["value" => "Empleado <span class='icon-arrow-up'></span>", "classEx" => "arrow", "attributeEx" => "data-sort=\"employee\""],
						["value" => "Tipo <span class='icon-arrow-up'></span>", "classEx" => "arrow", "attributeEx" => "data-sort=\"kind\""],
						["value" => "Monto <span class='icon-arrow-up'></span>", "classEx" => "arrow", "attributeEx" => "data-sort=\"amount\""],
						["value" => "Fecha <span class='icon-arrow-up'></span>", "classEx" => "arrow", "attributeEx" => "data-sort=\"date\""],
					]
				];
				$modelBody = [];
			@endphp
			@component('components.tables.table',[
				"modelHead" => $modelHead,
				"modelBody" => $modelBody,
				])
				@slot('attributeEx')
					id="system-move-nomina"
				@endslot
				@slot('classEx')
					table-move
				@endslot
				@slot('attributeExBody')
					id="body-pay-nomina"
				@endslot
				@slot('classExBody')
					tbody
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
					value="{{ route("payments.conciliation.search.nomina") }}"
				@endslot
			@endcomponent
		</div>
		@component("components.inputs.input-text")
			@slot("attributeEx")
				type="hidden"
				name="idmovement_nomina"
				id="idmovement_nomina"
			@endslot
		@endcomponent
		@component("components.inputs.input-text")
			@slot("attributeEx")
				type="hidden"
				name="idFolio_nomina"
				id="idFolio_nomina"
			@endslot
		@endcomponent
		<div id="idpayments_nomina">
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
						id="search-bank-nomina"
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
					id="bank-move-nomina"
				@endslot
				@slot('classEx')
					table-move
				@endslot
				@slot('attributeExBody')
					id="body-move-nomina"
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
					value="{{ route("payments.conciliation.search.nomina") }}"
				@endslot
			@endcomponent
		</div>
		@component("components.modals.modal",[ "variant" => "large" ])
			@slot("id")
				myModal_nomina
			@endslot
			@slot('classEx')
				modal_nomina
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
					enviar-nomina
				@endslot
				@slot("attributeEx")
					type="submit"
					value="Conciliar"
					name="send_nomina"
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
	
	$(document).on('dblclick','#system-move-nomina .tr',function()
	{
		$('#myModal_nomina .modal-body').html('');
		$(this).addClass('selected');
		folio = $(this).find('.folio_nomina').val();
		$.ajax(
		{
			type : 'post',
			url  : '{{ route("payments.conciliation.detail") }}',
			data : {'folio':folio},
			success : function(data)
			{
				$('#myModal_nomina .modal-body').html(data);
				$('#myModal_nomina').modal('show');
			},
			error: function(data)
			{
				$('#myModal_nomina').hide();
				$('.detail').removeAttr('disabled');
				$('.modal-backdrop').remove();
				swal('','Sucedió un error, por favor intente de nuevo.','error');
			}
		})
	})
	.on('click','.exit',function()
	{
		$('#myModal_nomina').hide();
	})
	.on('click','#help-btn',function()
	{
		swal('Ayuda','Dé doble clic sobre un Movimiento del Sistema para mostrar más detalles.','info');
	})
	.on('click','#ver',function()
	{
		nameEmp           = $(this).parent('td').parent('tr').find('.name').val();
		lastnameEmp       = $(this).parent('td').parent('tr').find('.last_name').val();
		scnd_last_nameEmp = $(this).parent('td').parent('tr').find('.scnd_last_name').val();
		bankEmp           = $(this).parent('td').parent('tr').find('.bank').val();
		cardEmp           = $(this).parent('td').parent('tr').find('.cardNumber').val();
		accountEmp        = $(this).parent('td').parent('tr').find('.account').val();
		clabeEmp          = $(this).parent('td').parent('tr').find('.clabe').val();
		referenceEmp      = $(this).parent('td').parent('tr').find('.reference').val();
		amountEmp         = $(this).parent('td').parent('tr').find('.importe').val();
		reason_paymentEmp = $(this).parent('td').parent('tr').find('.description').val();
		accounttext       = $(this).parent('td').parent('tr').find('.accounttext').val();
		enterprise    	  = $(this).parent('td').parent('tr').find('.enterprise').val();
		project           = $(this).parent('td').parent('tr').find('.project').val();
		area              = $(this).parent('td').parent('tr').find('.area').val();
		department        = $(this).parent('td').parent('tr').find('.department').val();
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
		$(".formulario").stop().slideToggle();
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
	.on('click','#system-move-nomina .tr',function()
	{
		$(this).addClass('selected');
	})
	.on('click','#body-move-nomina .tr',function()
	{
		$(this).parent('.tbody').find('.selected').removeClass('selected');
		$(this).addClass('selected');
	})
	.on('keyup','#search-sys-nomina',function()
	{
		text = $(this).val();
		dataSearch = [];
		dataSearch.push({'name':'year','value':$('#year_nomina option:selected').val()});
		dataSearch.push({'name':'month','value':$('#month_nomina option:selected').val()});
		dataSearch.push({'name':'url','value':'{{ route("payments.conciliation.search.nomina") }}'});
		dataSearch.push({'name':'table','value':'payments-nomina'});
		dataSearch.push({'name':'kindSort','value':$('#kindSortPayments').val()});
		dataSearch.push({'name':'ascDesc','value':$('#ascDescPayments').val()});
		selected = [];
		$("#body-pay-nomina").find('.selected').each(function() {
			selected.push($(this).find('.idpayment_nomina').val());
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
		$('#pagePayments').val('{{ route("payments.conciliation.search.nomina") }}');
		searchConciliationNomina(dataSearch);
	})
	.on('keyup','#search-bank-nomina',function()
	{
		text = $(this).val();
		dataSearch = [];
		dataSearch.push({'name':'year','value':$('#year_nomina option:selected').val()});
		dataSearch.push({'name':'month','value':$('#month_nomina option:selected').val()});
		dataSearch.push({'name':'url','value':'{{ route("payments.conciliation.search.nomina") }}'});
		dataSearch.push({'name':'table','value':'movements-nomina'});
		dataSearch.push({'name':'kindSort','value':$('#kindSortMovements').val()});
		dataSearch.push({'name':'ascDesc','value':$('#ascDescMovements').val()});
		selected = [];
		$("#body-move-nomina").find('.selected').each(function() {
			selected.push($(this).find('.idmovement_nomina').val());
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
		$('#pageMovements').val('{{ route("payments.conciliation.search.nomina") }}');
		searchConciliationNomina(dataSearch);
	})
	.on('click','.tr.selected',function()
	{
		$(this).removeClass('selected');
	})
	.on('click','.enviar-nomina',function (e)
	{
		$('#idpayments_nomina').empty();
		flagEnterprise	= true;
		flagDocument 	= true;
		amountSys		= 0;
		amountBank		= parseFloat($('#bank-move-nomina .selected').find('.amount_nomina').val());
		entMov			= $('#bank-move-nomina .selected').find('.enterpriseMov_nomina').val();

		$('#system-move-nomina .selected').each(function()
		{
			idkind			= $(this).find('.idkind_nomina').val();
			amountSys		+= parseFloat($(this).find('.amount_nomina').val());
			documento		= $(this).find('.document_nomina').val();
			enterprisePay	= $(this).find('.enterprisePay_nomina').val();
			
			idpayment		= $(this).find('.idpayment_nomina').val();
			conciliation	= $('<input type="hidden" name="idpayment_nomina[]" value="'+idpayment+'">');
			$('#idpayments_nomina').append(conciliation);
			
			
			if (enterprisePay != entMov) 
			{
				flagEnterprise = false;
			}

			if (documento == '' || documento == null || documento == undefined) 
			{
				flagDocument = false;
			}
		});
		
		if (flagEnterprise) 
		{
			enterpriseMov 	= $('#bank-move-nomina .selected').find('.enterpriseMov_nomina').val();
		}
		else
		{
			enterpriseMov 	= null;
		}
		
		idmovement 	= $('#bank-move-nomina .selected').find('.idmovement_nomina').val();
		idFolio 	= $('#system-move-nomina .selected').find('.folio_nomina').val();
		$('#idmovement_nomina').val(idmovement);
		$('#idFolio_nomina').val(idFolio);
		e.preventDefault();
		
		if (flagDocument == false)
		{
			swal({
				title: "Error",
				text: "El movimiento seleccionado no tiene comprobante de pago, por favor verifique.",
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
				text: "Los importes no coinciden, por favor verifique.",
				icon: "error",
				buttons: 
				{
					confirm: true,
				},
			});
		}
		if (amountBank == null || amountSys == null || amountBank == "" || amountSys == "" || amountBank == 0 || amountSys == 0 || amountBank == undefined || amountSys == undefined) 
		{
			swal({
				title: "Error",
				text: "Por favor seleccione uno o varios pagos y un movimiento.",
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
					form = $('#container-alta-nomina').serializeArray();
					swal({
						title: "Confirmación",
						text: "¿Desea realizar la conciliación de los movimienos seleccionados?",
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
							$('#container-alta-nomina').submit();
						}
					});
				}
			}
		}
	})
	.on('change','#year_nomina,#month_nomina',function()
	{	
		year  = $('#year_nomina option:selected').val();
		month = $('#month_nomina option:selected').val();
		$('#system-move-nomina .tbody,#bank-move-nomina .tbody').html('');
		$('.table-move span').removeClass('icon-arrow-down').addClass('icon-arrow-up');
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
			dataSearch.push({'name':'url','value':'{{ route("payments.conciliation.search.nomina") }}'});
			dataSearch.push({'name':'table','value':'all'});
			dataSearch.push({'name':'kindSort','value':''});
			dataSearch.push({'name':'ascDesc','value':''});
			dataSearch.push({'name':'search','value':''});
			dataSearch.push({'name':'selected','value':''});
			searchConciliationNomina(dataSearch);
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
		href = $(this).attr('href');
		url = new URL(href);
		dataSearch = [];
		dataSearch.push({'name':'year','value':$('#year_nomina option:selected').val()});
		dataSearch.push({'name':'month','value':$('#month_nomina option:selected').val()});
		dataSearch.push({'name':'url','value':url});
		if($(this).parents('.table-move').attr('id') == 'system-move-nomina')
		{
			table    = 'payments-nomina';
			kindSort = $('#kindSortPayments').val();
			ascDesc  = $('#ascDescPayments').val();
			search   = $('#search-sys-nomina').val();
			selected = [];
			$("#body-pay-nomina").find('.selected').each(function() {
				selected.push($(this).find('.idpayment_nomina').val());
			});
			$('#pagePayments').val(url);
		}
		else
		{
			table    = 'movements-nomina';
			kindSort = $('#kindSortMovements').val();
			ascDesc  = $('#ascDescMovements').val();
			search   = $('#search-bank-nomina').val();
			selected = [];
			$("#body-move-nomina").find('.selected').each(function() {
				selected.push($(this).find('.idmovement_nomina').val());
			});
			$('#pageMovements').val(url);
		}
		dataSearch.push({'name':'table','value':table});
		dataSearch.push({'name':'kindSort','value':kindSort});
		dataSearch.push({'name':'ascDesc','value':ascDesc});
		dataSearch.push({'name':'search','value':search});
		dataSearch.push({'name':'selected','value':selected});
		searchConciliationNomina(dataSearch);
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
			dataSearch.push({'name':'year','value':$('#year_nomina option:selected').val()});
			dataSearch.push({'name':'month','value':$('#month_nomina option:selected').val()});
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

			if($(this).parents('.table-move').attr('id') == 'system-move-nomina')
			{
				$('#ascDescPayments').val(ascDesc);
				$('#kindSortPayments').val(kindSort);
				search = $('#search-sys-nomina').val();
				url    = $('#pagePayments').val();
				table  = 'payments-nomina';
				selected = [];
				$("#body-pay-nomina").find('.selected').each(function() {
					selected.push($(this).find('.idpayment_nomina').val());
				});
			}
			else
			{
				$('#ascDescMovements').val(ascDesc);
				$('#kindSortMovements').val(kindSort);
				search = $('#search-bank-nomina').val();
				url    = $('#pageMovements').val();
				table  = 'movements-nomina';
				selected = [];
				$("#body-move-nomina").find('.selected').each(function() {
					selected.push($(this).find('.idmovement_nomina').val());
				});
			}
			dataSearch.push({'name':'url','value':url});
			dataSearch.push({'name':'table','value':table});
			dataSearch.push({'name':'kindSort', 'value':kindSort});
			dataSearch.push({'name':'ascDesc','value':ascDesc});
			dataSearch.push({'name':'search','value':search});
			dataSearch.push({'name':'selected','value':selected});
			searchConciliationNomina(dataSearch);
		}
	});
});

function searchConciliationNomina(dataSearch)
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
			if(table == 'payments-nomina')
			{
				$('#system-move-nomina').find('#body-pay-nomina').html(urldecode(json[0]));
				$('#system-move-nomina').trigger('updateAll');
			}
			else if (table == 'movements-nomina')
			{
				$('#bank-move-nomina').find('#body-move-nomina').html(urldecode(json[1]));
				$('#bank-move-nomina').trigger('updateAll');
			}
			else if (table == 'all')
			{
				$('#system-move-nomina').find('#body-pay-nomina').html(urldecode(json[0]));
				$('#system-move-nomina').trigger('updateAll');
				$('#bank-move-nomina').find('#body-move-nomina').html(urldecode(json[1]));
				$('#bank-move-nomina').trigger('updateAll');
			}
		},
		error : function(data)
		{
			$('#system-move-nomina .tbody,#bank-move-nomina .tbody').html('');
			$('.table-move span').removeClass('icon-arrow-down').addClass('icon-arrow-up');
			swal('','Sucedió un error, por favor intente de nuevo.','error');
		}
	}).done(function(data)
	{
		swal.close();
	});
}
</script>
@endsection
