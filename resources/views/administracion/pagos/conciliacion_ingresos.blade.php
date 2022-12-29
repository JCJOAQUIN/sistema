@extends('layouts.child_module')
@section('data')
	@component("components.forms.form", ["attributeEx" => "action=\"".route('payments.conciliation-income.store')."\" method=\"POST\" id=\"container-alta\"", "files" => true])
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
					Movimiento del Sistema
				@endcomponent
				@component('components.labels.label')
					(Para ordenar la información dé clic en cada cabecera)
				@endcomponent
				@component('components.labels.label')
					(Para ver más detalles del movimiento del sistema, de doble clic sobre el movimiento)
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
						["value" => "Empresa <span class='icon-arrow-up'></span>", "classEx" => "arrow", "attributeEx" => "data-sort=\"enterprise\""],
						["value" => "Cliente <span class='icon-arrow-up'></span>", "classEx" => "arrow", "attributeEx" => "data-sort=\"client\""],
						["value" => "Monto <span class='icon-arrow-up'></span>", "classEx" => "arrow", "attributeEx" => "data-sort=\"amount\""],
						["value" => "Fecha <span class='icon-arrow-up'></span>", "classEx" => "arrow", "attributeEx" => "data-sort=\"date\""],
						["value" => "Solicitud <span class='icon-arrow-up'></span>", "classEx" => "arrow", "attributeEx" => "data-sort=\"request\""],
						["value" => "Folio <span class='icon-arrow-up'></span>", "classEx" => "arrow", "attributeEx" => "data-sort=\"folio\""],
						["value" => "Serie <span class='icon-arrow-up'></span>", "classEx" => "arrow", "attributeEx" => "data-sort=\"serie\""]
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
					name="idFolio_only"
					id="idFolio_only"
				@endslot
			@endcomponent
			@component("components.inputs.input-text")
				@slot("attributeEx")
					type="hidden"
					name="idbill_only"
					id="idbill_only"
				@endslot
			@endcomponent
			@component("components.inputs.input-text")
				@slot("attributeEx")
					type="hidden"
					name="idmovement_only"
					id="idmovement_only"
				@endslot
			@endcomponent
			@component("components.inputs.input-text")
				@slot("attributeEx")
					type="hidden"
					name="type_only"
					id="type_only"
				@endslot
			@endcomponent
			<div id="idmovements"></div>
			<div id="idbills"></div>
			<div id="idfolios"></div>
			@component("components.inputs.input-text")
				@slot("attributeEx")
					type="hidden"
					id="kindSortPayments"
					value="idBill"
				@endslot
			@endcomponent
			@component("components.inputs.input-text")
				@slot("attributeEx")
					type="hidden"
					id="ascDescPayments"
					value="DESC"
				@endslot
			@endcomponent
			@component("components.inputs.input-text")
				@slot("attributeEx")
					type="hidden"
					id="pagePayments"
					value="{{ route("payments.conciliation-income.search") }}"
				@endslot
			@endcomponent
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
					value="{{ route("payments.conciliation-income.search") }}"
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
			@component('components.buttons.button')
				@slot('attributeEx')
				type="submit" name="send"
				@endslot
				@slot('classEx')
				enviar
				@endslot
				Conciliar
			@endcomponent
		</div>
	@endcomponent
@endsection
@section('scripts')
<script src="{{ asset('js/jquery.tablesorter.combined.js') }}"></script>
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<link rel="stylesheet" href="{{ asset('css/jquery.timepicker.min.css') }}">
<script src="{{ asset('js/jquery.numeric.js') }}"></script>
<script src="{{ asset('js/jquery.timepicker.min.js') }}"></script>
<script src="{{ asset('js/datepair.min.js') }}"></script>
<script src="{{ asset('js/jquery.datepair.min.js') }}"></script>
<script src="{{ asset('js/datepicker.js') }}"></script>
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
		$(document).on('click','.tr',function()
		{
			$(this).addClass('selected');
		})
		.on('dblclick','#system-move .tr',function()
		{
			$('#myModal .modal-body').html('');
			$(this).addClass('selected');
			idBill = $(this).find('.idBill').val();
			type = $(this).find('.type').val();
			$.ajax(
			{
				type : 'post',
				url  : '{{ route("payments.conciliation-income.detail-bill") }}',
				data : {'idBill':idBill,'type':type},
				success : function(data)
				{
					$('#myModal .modal-body').html(data);
					$('#myModal').modal('show');
				},
				error : function(data)
				{
					$('#myModal .modal-body').html('');
					swal('','Sucedió un error, por favor intente de nuevo.','error');
				}
			})
		})
		.on('click','.exit',function()
		{
			$('#myModal').hide();
		})
		.on('click','.close',function()
		{
			$('#myModal').hide();
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
				dataSearch.push({'name':'url','value':'{{ route("payments.conciliation-income.search") }}'});
				dataSearch.push({'name':'table','value':'all'});
				dataSearch.push({'name':'kindSort','value':''});
				dataSearch.push({'name':'ascDesc','value':''});
				dataSearch.push({'name':'search','value':''});
				dataSearch.push({'name':'selected','value':''});
				dataSearch.push({'name':'type','value':''});
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
				type 	 = [];
				$("#system-move").find('.selected').each(function() {
					selected.push($(this).find('.idBill').val());
					type.push($(this).find('.type').val());
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
				type 	 = [];
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
			dataSearch.push({'name':'type','value':type});
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
					search 	 = $('#search-sys').val();
					url    	 = $('#pagePayments').val();
					table  	 = 'payments';
					selected = [];
					type	 = [];
					$("#system-move").find('.selected').each(function() {
						selected.push($(this).find('.idBill').val());
						type.push($(this).find('.type').val());
					});
				}
				else
				{
					$('#ascDescMovements').val(ascDesc);
					$('#kindSortMovements').val(kindSort);
					search	 = $('#search-bank').val();
					url   	 = $('#pageMovements').val();
					table 	 = 'movements';
					selected = [];
					type	 = [];
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
				dataSearch.push({'name':'type','value':type});
				searchConciliation(dataSearch);
			}
		})
		.on('keyup','#search-sys',function()
		{
			text = $(this).val();
			dataSearch = [];
			dataSearch.push({'name':'year','value':$('#year option:selected').val()});
			dataSearch.push({'name':'month','value':$('#month option:selected').val()});
			dataSearch.push({'name':'url','value':'{{ route("payments.conciliation-income.search") }}'});
			dataSearch.push({'name':'table','value':'payments'});
			dataSearch.push({'name':'kindSort','value':$('#kindSortPayments').val()});
			dataSearch.push({'name':'ascDesc','value':$('#ascDescPayments').val()});
			selected = [];
			type	 = [];
			$("#system-move").find('.selected').each(function() {
				selected.push($(this).find('.idBill').val());
				type.push($(this).find('.type').val());
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
			dataSearch.push({'name':'type','value':type});
			$('#pagePayments').val('{{ route("payments.conciliation-income.search") }}');
			searchConciliation(dataSearch);
		})
		.on('keyup','#search-bank',function()
		{
			text = $(this).val();
			dataSearch = [];
			dataSearch.push({'name':'year','value':$('#year option:selected').val()});
			dataSearch.push({'name':'month','value':$('#month option:selected').val()});
			selected = [];
			type 	 = [];
			$("#bank-move").find('.selected').each(function() {
				selected.push($(this).find('.idmovement').val());
			});
			if(text != "")
			{
				$(this).parent().find(".placeholder").hide();
				dataSearch.push({'name':'url','value':'{{ route("payments.conciliation-income.search") }}'});
				dataSearch.push({'name':'table','value':'movements'});
				dataSearch.push({'name':'kindSort','value':$('#kindSortMovements').val()});
				dataSearch.push({'name':'ascDesc','value':$('#ascDescMovements').val()});
				dataSearch.push({'name':'search','value':text});
				dataSearch.push({'name':'selected','value':selected});
				dataSearch.push({'name':'type','value':type});
			}
			else
			{
				$(this).parent().find(".placeholder").show();
				dataSearch.push({'name':'url','value':'{{ route("payments.conciliation-income.search") }}'});
				dataSearch.push({'name':'table','value':'movements'});
				dataSearch.push({'name':'kindSort','value':$('#kindSortMovements').val()});
				dataSearch.push({'name':'ascDesc','value':$('#ascDescMovements').val()});
				dataSearch.push({'name':'search','value':''});
				dataSearch.push({'name':'selected','value':selected});
				dataSearch.push({'name':'type','value':type});
			}
			$('#pageMovements').val('{{ route("payments.conciliation-income.search") }}');
			searchConciliation(dataSearch);
		})
		.on('click','.enviar',function (e)
		{
			e.preventDefault();
			if($('#system-move .selected').length == 0 || $('#bank-move .selected').length == 0)
			{
				swal('','Por favor seleccione al menos un movimiento del sistema y un movimiento bancario.','error');
			}
			else if ($('#system-move .selected').length > 1 && $('#bank-move .selected').length > 1)
			{
				$('#idbills,#idmovements,#idfolios').empty();
				$('#idFolio_only,#idbill_only,#idmovement_only').val(null);
				swal('','No puede conciliar muchos movimientos con muchos movimientos bancarios, por favor verifique sus selecciones.','error');
			}
			else
			{
				if ($('#system-move .selected').length > 0 && $('#bank-move .selected').length == 1)
				{
					$('#idbills,#idmovements,#idfolios').empty();
					$('#idFolio_only,#idbill_only,#idmovement_only').val(null);
					idmovement 	= $('#bank-move .selected').find('.idmovement').val();
					$('#idmovement_only').val(idmovement);
					flagEnterprise	= true;
					amountSys		= 0;
					amountBank		= Number($('#bank-move .selected').find('.amount').val());
					entMov			= $('#bank-move .selected').find('.enterpriseMov').val();
					$('#system-move .selected').each(function()
					{
						amountSys		+= Number($(this).find('.amount').val());
						enterprisePay	= $(this).find('.enterprisePay').val();
						idbill			= $(this).find('.idBill').val();
						conciliation	= $('<input type="hidden" name="idbill_multi[]" value="'+idbill+'">');
						$('#idbills').append(conciliation);
						type			= $(this).find('.type').val();
						types			= $('<input type="hidden" name="type_multi[]" value="'+type+'">');
						$('#idfolios').append(types);
						idfolio 		= $(this).find('.idFolio').val();
						folios 			= $('<input type="hidden" name="idfolio_multi[]" value="'+idfolio+'">');
						$('#idfolios').append(folios);
						if (enterprisePay != entMov)
						{
							flagEnterprise = false;
						}
					});
					if (flagEnterprise)
					{
						enterpriseMov 	= $('#bank-move .selected').find('.enterpriseMov').val();
					}
					else
					{
						enterpriseMov 	= null;
					}
					if (amountBank != amountSys)
					{
						swal('','Los importes no coinciden, por favor verifique sus selecciones.','error');
						return;
					}
					if (amountBank == null || amountSys == null || amountBank == "" || amountSys == "" || amountBank == 0 || amountSys == 0 || amountBank == undefined || amountSys == undefined)
					{
						swal('','Por favor seleccione al menos un movimiento y un pago.','error');
						return;
					}
					if (amountBank != undefined || amountSys != undefined)
					{
						if (Number(amountBank).toFixed(2) == Number(amountSys).toFixed(2))
						{
							if (enterpriseMov != enterprisePay)
							{
								swal('','Las empresas no coinciden, solo se permite hacer conciliación con la misma empresa por favor verifique sus selecciones.','error');
							}
							else
							{
								form = $('#container-alta').serializeArray();
								swal({
									title: "",
									text: "¿Confirme que desea realizar la conciliación de los movimienos seleccionados?",
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
				}
				else if ($('#system-move .selected').length == 1 && $('#bank-move .selected').length > 0)
				{
					$('#idmovements,#idbills,#idfolios').empty();
					$('#idFolio_only,#idbill_only,#idmovement_only,#type_only').val(null);

					idFolio		= $('#system-move .selected').find('.idFolio').val();
					idBill		= $('#system-move .selected').find('.idBill').val();
					type		= $('#system-move .selected').find('.type').val();
					$('#idFolio_only').val(idFolio);
					$('#idbill_only').val(idBill);
					$('#type_only').val(type);

					flagEnterprise		= true;
					flagMovementType	= false;
					amountBank			= 0;
					amountSys			= Number($('#system-move .selected').find('.amount').val());
					enterprisePay		= $('#system-move .selected').find('.enterprisePay').val();
					$('#bank-move .selected').each(function()
					{
						if ($(this).find('.movementType').val() == 'Ingreso')
						{
							amountBank	+= Number($(this).find('.amount').val());
						}
						if ($(this).find('.movementType').val() == 'Devolución')
						{
							amountBank	-= Number($(this).find('.amount').val());
						}
						if ($(this).find('.movementType').val() == 'No definido')
						{
							flagMovementType = true;
						}
						idmovement		= $(this).find('.idmovement').val();
						conciliation	= $('<input type="hidden" name="idmovement_multi[]" value="'+idmovement+'">');
						$('#idmovements').append(conciliation);
						entMov = $(this).find('.enterpriseMov').val();
						if (enterprisePay != entMov)
						{
							flagEnterprise = false;
						}
					});
					if (flagEnterprise)
					{
						enterpriseMov 	= $('#bank-move .selected').find('.enterpriseMov').val();
					}
					else
					{
						enterpriseMov 	= null;
					}

					if (flagMovementType)
					{
						swal('','Eligió un movimiento de tipo "No definido", por favor asigne un tipo.','error');
						return;
					}
					if (Number(amountBank).toFixed(2) != Number(amountSys).toFixed(2))
					{
						swal('','Los importes no coinciden, por favor verifique sus selecciones.','error');
						return;
					}
					if (amountBank == undefined || amountSys == undefined || amountBank == null || amountSys == null)
					{
						swal('','Por favor seleccione al menos un movimiento y un pago.','error');
						return;
					}
					if (amountBank != undefined && amountSys != undefined)
					{
						if (Number(amountBank).toFixed(2) == Number(amountSys).toFixed(2))
						{
							if (enterpriseMov != enterprisePay)
							{
								swal('','Las empresas son diferentes, solo se permite hacer conciliación con la misma empresa por favor verifique sus selecciones.','error');							
							}
							else
							{
								form = $('#container-alta').serializeArray();
								swal({
									title: "",
									text: "¿Confirme que desea realizar la conciliación de los movimienos seleccionados?",
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
				}
			}
		})
		.on('click','.exit',function()
		{
			$('#detail').slideUp();
			$('#myModal_nomina').hide();
		})
		.on('click','.selected',function()
		{
			$(this).removeClass('selected');
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
