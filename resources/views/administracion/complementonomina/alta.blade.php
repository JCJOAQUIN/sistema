@extends('layouts.child_module')
@if(isset($request)) 
	@section('data')
		{!! Form::open(['route' => 'payroll.store', 'method' => 'POST', 'id' => 'container-alta']) !!}
			<center>
				@component('components.labels.title-divisor') Nueva solicitud @endcomponent<br>
			</center>
			<div class="div-form-group" style="max-width: 400px;">
				<p>
					<b>Título:</b><input type="text" name="title" class="input-text removeselect" placeholder="Ingrese un título" data-validation="required" @if(isset($request)) value="{{ $request->nominas->first()->title }}" @endif>
				</p>
				<p>
					<b>Fecha:</b><input type="text" class="input-text removeselect datepicker2" name="datetitle" @if(isset($request)) value="{{ $request->nominas->first()->datetitle }}" @endif data-validation="required" placeholder="Seleccione una fecha" readonly="readonly">
				</p>
				<p>
					<select class="js-users removeselect" name="user_id" multiple="multiple" style="width: 98%;" data-validation="required"> 
						@foreach(App\User::orderName()->whereIn('status',['NO-MAIL','ACTIVE','RE-ENTRY','RE-ENTRY-NO-MAIL'])->where('sys_user',1)->get() as $user) 
						<option value="@if($request->idRequest == $user->id)
						{{ $user->id }}" selected="selected">{{ $user->name }} {{ $user->last_name }} {{ $user->scnd_last_name }}</option> 
						@else
						{{ $user->id }}">{{ $user->name }} {{ $user->last_name }} {{ $user->scnd_last_name }}</option> 
						@endif
						@endforeach 
					</select><br> 
				</p>
			</div><br><br>
			<center>
				@component('components.labels.title-divisor')    Datos de empleado @endcomponent<br>
			</center>
			<div class="div-form-group" style="max-width: 400px;">
				<p>
					<select class="js-employees removeselect" name="employeeid" multiple="multiple" style="width: 98%;" id="multiple-users">
						@foreach($users as $user)
						<option value="{{ $user->id }}">{{ $user->name }} {{ $user->last_name }} {{ $user->scnd_last_name }}</option>
						@endforeach
					</select><br>
				</p>
				<p>
					<select class="js-enterprises removeselect" name="enterprise_id" multiple="multiple" id="multiple-enterprises select2-selection--multiple" style="width: 98%; border: 0px;">
						@foreach($enterprises as $enterprise)
						<option value="{{ $enterprise->id }}">{{ strlen($enterprise->name) >= 35 ? substr(strip_tags($enterprise->name),0,35).'...' : $enterprise->name }}</option>
						@endforeach
					</select><br>
				</p>

				<p>
					<select class="js-areas removeselect input-text" multiple="multiple" name="area_id" style="width: 98%;" id="multiple-areas">
						@foreach($areas as $area)
						<option value="{{ $area->id }}">{{ $area->name }}</option>
						@endforeach
					</select><br>
				</p>
				<p>
					<select class="js-departments removeselect input-text" multiple="multiple" name="department_id" style="width: 98%;" id="multiple-departments">
						@foreach($departments as $department)
						<option value="{{ $department->id }}">{{ $department->name }}</option>
						@endforeach
					</select><br>
				</p>
				<p>
					<select class="js-accounts removeselect" class="input-text" multiple="multiple" name="accountid" style="width: 98%;" id="multiple-accounts">
						
					</select><br>
				</p>
				<p>
					<select class="js-projects removeselect" name="projectid" multiple="multiple" style="width: 98%;" id="multiple-projects">
						@foreach(App\Project::orderName()->whereIn('status',[1,2])->get() as $project)
						<option value="{{ $project->idproyect }}">{{ $project->proyectName }}</option>
						@endforeach
					</select><br>
				</p>
			</div><br><br>
			<div class="methodForm" style="display: none;">
				<br><br>
				@component('components.labels.title-divisor')    FORMA DE PAGO @endcomponent<br>
				<center>
					<div class="div-form-group" style="display: flex; overflow-x: auto;">
						<input type="radio" name="method" id="accountBank" value="1">
						<label for="accountBank">Cuenta Bancaria</label> 
						<input type="radio" name="method" id="cash" value="2">
						<label for="cash">Efectivo</label>
						<input type="radio" name="method" id="checks" value="3">
						<label for="checks">Cheque</label>
						<br>
					</div>
				</center>
			</div>
			<input type="hidden" name="employee_number" class="input-text employee_number" id="efolio" placeholder="Ingrese el número de empleado">
			<input type="hidden" class="input-text name">
			<input type="hidden" class="input-text last_name">
			<input type="hidden" class="input-text scnd_last_name">
			<div class="resultbank table-responsive" style="display: none;">
			</div>
			<br><br>
			<div class="datos" style="display: none;">
				@component('components.labels.title-divisor') INGRESE LOS SIGUIENTES DATOS @endcomponent
				<div class='form-container'>
					<p>
						<label class='label-form'>Referencia</label><input type='text' placeholder='Ingrese la referencia' class='input-text reference'>
					</p>
					<p>
						<label class='label-form'>Importe</label><input type='text' placeholder='Ingrese el importe' class='input-text amount'>
					</p>
					<p>
						<label class='label-form'>Razón de pago</label><input type='text' placeholder='Ingrese la razón de pago' class='input-text reason_payment'>
					</p>
				</div>
				<div class='form-container'><p><button type='button' name='add' id='add'><div class='btn_plus'>+</div> Agregar concepto</button><button type='button' name='canc' id='exit' class='btn'>Cancelar</button></p></div>
			</div>
			<div class="form-container">
				<div class="table-responsive">
					<table id="table2" class="table-no-bordered">
						<thead>
							<th># Empleado</th>
							<th>Nombre del Empleado</th>
							<th>Empresa</th>
							<th>Proyecto</th>
							<th hidden>Departamento</th>
							<th hidden>Dirección</th>
							<th hidden>Clasificación de gasto</th>
							<th hidden>Banco</th>
							<th hidden># Tarjeta</th>
							<th hidden>Cuenta</th>
							<th hidden>CLABE</th>
							<th>Referencia</th>
							<th>Importe</th>
							<th>Razón</th>
							<th></th>
						</thead>
						<tbody id="body-payroll" class="request-validate">
							@foreach($request->nominas as $nomina)
								@foreach(App\NominaAppEmp::join('users','idUsers','id')->where('idNominaApplication',$nomina->idNominaApplication)->get() as $noEmp)
								<tr>
									<td hidden>
										<input type="text" name="t_idpaymentmethod[]" value="{{ $noEmp->idpaymentMethod }}">
									</td>
									<td>
										{{ $noEmp->idUsers }}
										<input readonly class="input-table" type="hidden" name="t_employee_number[]" value="{{ $noEmp->idUsers }}">
									</td>
									<td>
										{{ $noEmp->name }} {{ $noEmp->last_name }} {{ $noEmp->scnd_last_name }}
										<input readonly class="input-table name" type="hidden" value="{{ $noEmp->name }}">
										<input readonly class="input-table last_name" type="hidden" value="{{ $noEmp->last_name }}">
										<input readonly class="input-table scnd_last_name" type="hidden" value="{{ $noEmp->scnd_last_name }}">
									</td>
									<td>
										{{ $noEmp->enterprise()->exists() ? $noEmp->enterprise->name : 'No hay' }}
										<input readonly class="input-table" type="hidden" name="t_enterprise[]" value="{{ $noEmp->idEnterprise }}">
									</td>
									<td>
										{{  $noEmp->project()->exists() ? $noEmp->project->proyectName : 'No hay' }}
										<input readonly class="input-table" type="hidden" name="t_project[]" value="{{ $noEmp->idProject }}">
									</td>
									<td hidden>
										{{ $noEmp->department()->exists() ? $noEmp->department->name : 'No hay' }}
										<input readonly class="input-table" type="hidden" name="t_department[]" value="{{ $noEmp->idDepartment }}">

										<input readonly class="input-table enterprise" type="hidden" value="{{ $noEmp->enterprise()->exists() ? $noEmp->enterprise->name : 'No hay' }}">
										<input readonly class="input-table project" type="hidden" value="{{  $noEmp->project()->exists() ? $noEmp->project->proyectName : 'No hay' }}">
										<input readonly class="input-table department" type="hidden" value="{{ $noEmp->department()->exists() ? $noEmp->department->name : 'No hay' }}">
										<input readonly class="input-table area" type="hidden" value="{{ $noEmp->area()->exists() ? $noEmp->area->name : 'No hay' }}">
										<input readonly class="input-table accounttext" type="hidden" value="{{ $noEmp->accounts()->exists() ? $noEmp->accounts->account.' - '.$noEmp->accounts->description : 'No hay' }}">
									</td>
									<td hidden>
										{{ $noEmp->area()->exists() ? $noEmp->area->name : 'No hay' }}
										<input readonly class="input-table" type="hidden" name="t_direction[]" value="{{ $noEmp->idArea }}">
									</td>
									<td hidden>
										{{ $noEmp->accounts()->exists() ? $noEmp->accounts->account.' - '.$noEmp->accounts->description : 'No hay' }}
										<input readonly class="input-table" type="hidden" name="t_accountid[]" value="{{ $noEmp->idAccount }}">
									</td>
									<td hidden>
										{{ $noEmp->bank }}
										<input readonly value="{{ $noEmp->bank }}" class="input-table" type="hidden" name="t_bank[]">
									</td>
									<td hidden>
										{{ $noEmp->cardNumber }}
										<input value="{{ $noEmp->cardNumber }}" readonly class="input-table" type="hidden" name="t_card_number[]">
									</td>
									<td hidden>
										{{ $noEmp->account }}
										<input value="{{ $noEmp->account }}" readonly class="input-table" type="hidden" name="t_account[]">
									</td>
									<td hidden>
										{{ $noEmp->clabe }}
										<input value="{{ $noEmp->clabe }}" readonly value="" class="input-table" type="hidden" name="t_clabe[]">
									</td>
									<td>
										{{ $noEmp->reference }}
										<input value="{{ $noEmp->reference }}" readonly class="input-table" type="hidden" name="t_reference[]">
									</td>
									<td>
										{{ $noEmp->amount }}
										<input value="{{ $noEmp->amount }}" readonly class="input-table importe" type="hidden" name="t_amount[]">
									</td>
									<td>
										{{ $noEmp->description }}
										<input readonly class="input-table" type="hidden" name="t_reason_payment[]" value="{{ $noEmp->description }}">
									</td>
									<td>
										<button class="delete-item"><span class="icon-x delete-span"></span></button>
									</td>
								</tr>
								@endforeach
							@endforeach
						</tbody>
						<tfoot>
							<th></th>
						</tfoot>
					</table>
				</div>
				<br>
			</div>
			<div class="form-container">
				<div class="table-responsive">
					<table class="table-no-bordered">
						<thead>
							<th width="20%"></th>
							<th width="20%"></th>
							<th width="20%"></th>
							<th width="20%">TOTAL:</th>
							<th width="20%">@foreach($request->nominas as $nomina)<input value="{{ $nomina->amount }}" id="input-extrasmall" placeholder="Ingrese el total" readonly class="input-table total" type="text" name="total">@endforeach</th>
						</thead>
					</table>
				</div>
				<br>
			</div>
			<center> 
				<p> 
				<input class="btn btn-red" type="submit" name="enviar" value="ENVIAR SOLICITUD"> 
				<input class="btn btn-blue save" type="submit" id="save" name="save" value="GUARDAR SIN ENVIAR" formaction="{{ route('payroll.unsent', $request->folio) }}">  
					<a 
						@if(isset($option_id)) 
							href="{{ url(App\Module::find($option_id)->url) }}" 
						@else 
							href="{{ url(App\Module::find($child_id)->url) }}" 
						@endif 
					><button class="btn" type="button">REGRESAR</button></a>
				</p> 
			</center> 
		{!! Form::close() !!}
	@endsection

	@section('scripts')
		<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
		<script src="{{ asset('js/jquery-ui.js') }}"></script>
		<script src="{{ asset('js/jquery.numeric.js') }}"></script>
		<script src="{{ asset('js/datepicker.js') }}"></script>
		<script>
			$.validate(
			{
				form: '#container-alta',
				onError   : function($form)
				{
					swal('', '{{ Lang::get("messages.form_error") }}', 'error');
				},
				onSuccess : function($form)
				{
					if($('.request-validate').length>0)
					{
						conceptos   = $('#body-payroll tr').length;
						if(conceptos>0)
						{
							swal("Cargando",{
								icon: '{{ url('images/loading.svg') }}',
								button: false,
							});
							return true;
						}
						else
						{
							swal('', 'Todos los campos son requeridos', 'error');
							return false;
						}
					}
					else
					{
						swal("Cargando",{
							icon: '{{ url('images/loading.svg') }}',
							button: false,
						});
						return true;
					}
				}
			});

			$(document).ready(function()
			{
				$('.js-users').select2({
					placeholder: 'Nombre del Solicitante',
					language: "es",
					maximumSelectionLength: 1
				})
				.on("change",function(e)
				{
					if($(this).val().length>1)
					{
						$(this).val($(this).val().slice(0,1)).trigger('change');
					}
				});
				$('.js-employees').select2({
					placeholder: 'Seleccione un Empleado',
					language: "es",
					maximumSelectionLength: 1
				})
				.on("change",function(e)
				{
					if($(this).val().length>1)
					{
						$(this).val($(this).val().slice(0,1)).trigger('change');
					}
				});
				$('.js-enterprises').select2(
				{
					placeholder: 'Seleccione la Empresa',
					language: "es",
					maximumSelectionLength: 1
				})
				.on("change",function(e)
				{
					if($(this).val().length>1)
					{
						$(this).val($(this).val().slice(0,1)).trigger('change');
					}
				});
				$('.js-areas').select2(
				{
					placeholder: 'Seleccione la Dirección',
					language: "es",
					maximumSelectionLength: 1
				})
				.on("change",function(e)
				{
					if($(this).val().length>1)
					{
						$(this).val($(this).val().slice(0,1)).trigger('change');
					}
				});
				$('.js-departments').select2(
				{
					placeholder: 'Seleccione el Departamento',
					language: "es",
					maximumSelectionLength: 1
				})
				.on("change",function(e)
				{
					if($(this).val().length>1)
					{
						$(this).val($(this).val().slice(0,1)).trigger('change');
					}
				});
				$('.js-projects').select2(
				{
					placeholder: 'Seleccione el Proyecto/Contrato',
					language: "es",
					maximumSelectionLength: 1
				})
				.on("change",function(e)
				{
					if($(this).val().length>1)
					{
						$(this).val($(this).val().slice(0,1)).trigger('change');
					}
				});
				$('.js-accounts').select2(
				{
					placeholder: 'Clasificación de Gasto',
					language: "es",
					maximumSelectionLength: 1
				})
				.on("change",function(e)
				{
					if($(this).val().length>1)
					{
						$(this).val($(this).val().slice(0,1)).trigger('change');
					}
				});

				$('.card_number,.destination_account,.destination_key,.employee_number,.amount').numeric(false);    // números
				$('.amount,.importe',).numeric({ altDecimal: ".", decimalPlaces: 2 });
				$('.amount',).numeric({ negative : false });

				$(function() 
				{
					$(".datepicker2").datepicker({ minDate: 0, dateFormat: "yy-mm-dd" });
				});
			
				$(document).on('change', '.js-employees', function()
				{
					id = $(this).val();
					$('.datos').stop().show();
					$('.methodForm').stop().show();
					text = $('#efolio').val();
					$.ajax(
					{
						type : 'get',
						url  : '{{ url("administration/payroll/search/bank") }}',
						data : {'employee_number':id},
						success:function(data)
						{
							$('.resultbank').html(data);
						},
						error : function()
						{
							swal('','Sucedió un error, por favor intente de nuevo.','error');
							$('.resultbank').html('');
						}
					});
				})
				.on('click','#exit', function(){

					$(".formulario").slideToggle();
					$('#table').slideToggle();
				})
				.on('click','.btn-delete-form',function(e)
				{
					e.preventDefault();
					form = $(this).parents('form');
					swal({
						title       : "Limpiar formulario",
						text        : "¿Confirma que desea limpiar el formulario?",
						icon        : "warning",
						buttons     : ["Cancelar","OK"],
						dangerMode  : true,
					})
					.then((willClean) =>
					{
						if(willClean)
						{
							$('#body-payroll').html('');
							$('.removeselect').val(null).trigger('change');
							$('.resultbank').hide();
							$('.result').hide();
							form[0].reset();
						}
						else
						{
							swal.close();
						}
					});
				})
				.on('change','.js-enterprises',function()
				{
					$('.js-accounts').empty();
					$enterprise = $(this).val();
					$.ajax(
					{
						type    : 'get',
						url     : '{{ url("/administration/payroll/create/account") }}',
						data    : {'enterpriseid':$enterprise},
						success : function(data)
						{
							$.each(data,function(i, d)
							{
								$('.js-accounts').append('<option value='+d.idAccAcc+'>'+d.account+' - '+d.description+' ('+d.content+')</option>');
							});
						},
						error : function()
						{
							swal('','Sucedió un error, por favor intente de nuevo.','error');
							$('.js-accounts').val(null).trigger('change');
						}
					})
				})
				.on('keyup','#input-search', function(){
				
					$text = $(this).val().trim();
					if ($text == "") 
					{
						$('#not-found').stop().show();
						$('#not-found').html("RESULTADO NO ENCONTRADO");
						$('#table').stop().hide();
						$('.resultbank').stop().hide();
						$('.result').stop().hide();
						$('.datos').stop().hide();
						$('.methodForm').stop().hide();
					}
					else
					{
						$('#not-found').stop().hide();
						$('.result').stop().show();
						$.ajax({
							type : 'get',
							url  : '{{ url("administration/payroll/search/user") }}',
							data : {'search':$text},
							success:function(data)
							{
								$('.result').html(data);
							},
							error : function()
							{
								swal('','Sucedió un error, por favor intente de nuevo.','error');
								$('.result').html('');
							}
						}); 
					}
				})
				.on('click','#save',function()
				{
					$('.remove').removeAttr('data-validation');
					$('.removeselect').removeAttr('required');
					$('.removeselect').removeAttr('data-validation');
					$('.request-validate').removeClass('request-validate');
				})
				.on('click','.checkbox',function()
				{
					$('.marktr').removeClass('marktr');
					$(this).parents('tr').addClass('marktr');
				})
				.on('click','.delete-item',function()
				{
					$(this).parents('tr').remove();
					var sumatotal = 0;
					$(".importe").each(function(i, v)
					{
						valor = parseFloat($(this).val());
						sumatotal = sumatotal + valor ;
					});
					$(".total").val(sumatotal);
					
				})
				.on('click','#add',function()
				{
					employee_number = $('.js-employees option:checked').val();
					name            = $('.js-employees option:checked').text();
					
					enterprise      = $('.js-enterprises option:checked').text();
					enterpriseid    = $('.js-enterprises option:checked').val();
					
					department      = $('.js-departments option:checked').text();
					departmentid    = $('.js-departments option:checked').val();
					
					direction       = $('.js-areas option:checked').text();
					directionid     = $('.js-areas option:checked').val();
					
					accounttext     = $('.js-accounts option:checked').text();
					accountid       = $('.js-accounts option:checked').val();
					
					project         = $('.js-projects option:checked').text();
					projectid       = $('.js-projects option:checked').val();
					
					reference       = $('.reference').val();
					amount          = $('.amount').val();
					reason          = $('.reason_payment').val();
					check           = $('input[name="idemp"]').is(':checked');
					bank            = $('input[name="idemp"]:checked').parent('td').parent('tr').find('.bank').val();
					clabe           = $('input[name="idemp"]:checked').parent('td').parent('tr').find('.clabe').val();
					account         = $('input[name="idemp"]:checked').parent('td').parent('tr').find('.account').val();
					card_number     = $('input[name="idemp"]:checked').parent('td').parent('tr').find('.card_number').val();
					idpaymentmethod = $('input[name="method"]:checked').val();

					if (idpaymentmethod == "" || idpaymentmethod == undefined) 
					{
						swal('', 'Por favor seleccione una forma de pago', 'error');
					}
					else
					{
						if (idpaymentmethod == 1) 
						{
							if (employee_number == "" || name == "" || amount == "" || reason == "" || enterpriseid=="" || directionid=="" || accountid=="" || projectid=="" ||  enterpriseid == undefined ||directionid==undefined || accountid==undefined || projectid==undefined ) 
							{
								if(amount=="" || amount == 0)
								{
									$('.amount').addClass('error');
								}
								if(reason=="")
								{
									$('.reason_payment').addClass('error');
								}
								swal('', 'Por favor llene los campos necesarios', 'error');
							}
							else if(check == false)
							{
								swal('', 'Por favor seleccione una cuenta', 'error');
							}
							else
							{
								tr_table    = $('<tr></tr>')
												.append($('<td hidden></td>')
													.append(idpaymentmethod)
													.append($('<input readonly class="input-table" type="hidden" name="t_idpaymentmethod[]">').val(idpaymentmethod))
												)
												.append($('<td></td>')
													.append(employee_number)
													.append($('<input readonly class="input-table" type="hidden" name="t_employee_number[]">').val(employee_number))
												)
												.append($('<td></td>')
													.append(name)
													.append($('<input readonly class="input-table" type="hidden"/>').val(name))
												)
												.append($('<td></td>')
													.append(enterprise)
													.append($('<input readonly class="input-table" type="hidden" name="t_enterprise[]">').val(enterpriseid))
												)
												.append($('<td></td>')
													.append(project)
													.append($('<input readonly class="input-table" type="hidden" name="t_project[]">').val(projectid))
												)
												.append($('<td hidden></td>')
													.append(department)
													.append($('<input readonly class="input-table" type="hidden" name="t_department[]">').val(departmentid))
												)
												.append($('<td hidden></td>')
													.append(direction)
													.append($('<input readonly class="input-table" type="hidden" name="t_direction[]">').val(directionid))
												)
												.append($('<td hidden></td>')
													.append(accounttext)
													.append($('<input readonly class="input-table" type="hidden" name="t_accountid[]">').val(accountid))
												)
												.append($('<td hidden></td>')
													.append(bank)
													.append($('<input readonly class="input-table" type="hidden" name="t_bank[]">').val(bank))
												)
												.append($('<td hidden></td>')
													.append(card_number)
													.append($('<input readonly class="input-table" type="hidden" name="t_card_number[]">').val(card_number))
												)
												.append($('<td hidden></td>')
													.append(account)
													.append($('<input readonly class="input-table" type="hidden" name="t_account[]">').val(account))
												)
												.append($('<td hidden></td>')
													.append(clabe)
													.append($('<input readonly class="input-table" type="hidden" name="t_clabe[]">').val(clabe))
												)
												.append($('<td></td>')
													.append(reference)
													.append($('<input readonly class="input-table" type="hidden" name="t_reference[]">').val(reference))
												)
												.append($('<td></td>')
													.append(amount)
													.append($('<input readonly class="input-table importe" type="hidden" name="t_amount[]">').val(amount))
												)
												.append($('<td></td>')
													.append(reason)
													.append($('<input readonly class="input-table" type="hidden" name="t_reason_payment[]">').val(reason))
												)
												.append($('<td></td>')
													.append($('<button class="delete-item"></button>')
														.append($('<span class="icon-x delete-span"></span>'))
													)
												);

								$('#body-payroll').append(tr_table);

								var sumatotal = 0;
								$(".importe").each(function(i, v)
								{
									valor = parseFloat($(this).val());
									sumatotal = sumatotal + valor ;
								});

								$('.js-accounts,.js-departments,.js-enterprises,.js-employees,.js-projects,.js-areas').val(null).trigger('change');
								$(".total").val(sumatotal);
								$('#input-search').val('');
								$(".formulario").slideToggle();
								$('#table').slideToggle();
								$('.datos').stop().hide();
								$('.methodForm').stop().hide();
								$('.result').stop().hide();
								$('.reference').val('');
								$('.amount').val('');
								$('.reason_payment').val('');
								$('.resultbank').stop().hide();
							}   
						}
						else
						{
							if (employee_number == "" || name == "" || amount == "" || reason == "" || enterpriseid=="" || directionid=="" || accountid=="" || projectid=="" ||  enterpriseid == undefined ||directionid==undefined || accountid==undefined || projectid==undefined ) 
							{
								if(amount=="" || amount == 0)
								{
									$('.amount').addClass('error');
								}
								if(reason=="")
								{
									$('.reason_payment').addClass('error');
								}
								swal('', 'Por favor llene los campos necesarios', 'error');
							}
							else
							{
								tr_table    = $('<tr></tr>')
												.append($('<td hidden></td>')
													.append(idpaymentmethod)
													.append($('<input readonly class="input-table" type="hidden" name="t_idpaymentmethod[]">').val(idpaymentmethod))
												)
												.append($('<td></td>')
													.append(employee_number)
													.append($('<input readonly class="input-table" type="hidden" name="t_employee_number[]">').val(employee_number))
												)
												.append($('<td></td>')
													.append(name)
													.append($('<input readonly class="input-table" type="hidden"/>').val(name))
												)
												.append($('<td></td>')
													.append(enterprise)
													.append($('<input readonly class="input-table" type="hidden" name="t_enterprise[]">').val(enterpriseid))
												)
												.append($('<td></td>')
													.append(project)
													.append($('<input readonly class="input-table" type="hidden" name="t_project[]">').val(projectid))
												)
												.append($('<td hidden></td>')
													.append(department)
													.append($('<input readonly class="input-table" type="hidden" name="t_department[]">').val(departmentid))
												)
												.append($('<td hidden></td>')
													.append(direction)
													.append($('<input readonly class="input-table" type="hidden" name="t_direction[]">').val(directionid))
												)
												.append($('<td hidden></td>')
													.append(accounttext)
													.append($('<input readonly class="input-table" type="hidden" name="t_accountid[]">').val(accountid))
												)
												.append($('<td hidden></td>')
													.append('x')
													.append($('<input readonly class="input-table" type="hidden" name="t_bank[]">').val('x'))
												)
												.append($('<td hidden></td>')
													.append('x')
													.append($('<input readonly class="input-table" type="hidden" name="t_card_number[]">').val('x'))
												)
												.append($('<td hidden></td>')
													.append('x')
													.append($('<input readonly class="input-table" type="hidden" name="t_account[]">').val('x'))
												)
												.append($('<td hidden></td>')
													.append('x')
													.append($('<input readonly class="input-table" type="hidden" name="t_clabe[]">').val('x'))
												)
												.append($('<td></td>')
													.append(reference)
													.append($('<input readonly class="input-table" type="hidden" name="t_reference[]">').val(reference))
												)
												.append($('<td></td>')
													.append(amount)
													.append($('<input readonly class="input-table importe" type="hidden" name="t_amount[]">').val(amount))
												)
												.append($('<td></td>')
													.append(reason)
													.append($('<input readonly class="input-table" type="hidden" name="t_reason_payment[]">').val(reason))
												)
												.append($('<td></td>')
													.append($('<button class="delete-item"></button>')
														.append($('<span class="icon-x delete-span"></span>'))
													)
												);

								$('#body-payroll').append(tr_table);

								var sumatotal = 0;
								$(".importe").each(function(i, v)
								{
									valor = parseFloat($(this).val());
									sumatotal = sumatotal + valor ;
								});

								$('.js-accounts,.js-departments,.js-enterprises,.js-employees,.js-projects,.js-areas').val(null).trigger('change');
								$(".total").val(sumatotal);
								$('#input-search').val('');
								$(".formulario").slideToggle();
								$('#table').slideToggle();
								$('.datos').stop().hide();
								$('.methodForm').stop().hide();
								$('.result').stop().hide();
								$('.reference').val('');
								$('.amount').val('');
								$('.reason_payment').val('');
								$('.resultbank').stop().hide();
							}
						}
					}
					
				})
				.on('click','input[name="method"]',function()
				{
					if($(this).val() == 1)
					{
						$('.resultbank').stop(true,true).slideDown().show();
					}
					else
					{
						$('.resultbank').stop(true,true).slideUp().hide();
					}
				});
			});
		</script>
	@endsection
@else
	@section('data')
		{!! Form::open(['route' => 'payroll.store', 'method' => 'POST', 'id' => 'container-alta']) !!}
			<center>
				@component('components.labels.title-divisor')    Nueva solicitud @endcomponent<br>
				
			</center>
			<div class="div-form-group" style="max-width: 400px;">
				<p>
					<b>Título:</b><input type="text" name="title" class="input-text removeselect" placeholder="Ingrese el título" data-validation="required">
				</p>
				<p>
					<b>Fecha:</b><input type="text" class="input-text removeselect datepicker2" name="datetitle" data-validation="required" placeholder="Ingrese una fecha" readonly="readonly">
				</p>
				<p>
					<select class="js-users removeselect" name="user_id" multiple="multiple" style="width: 98%;" data-validation="required">
						@foreach(App\User::orderName()->whereIn('status',['NO-MAIL','ACTIVE','RE-ENTRY','RE-ENTRY-NO-MAIL'])->where('sys_user',1)->get() as $user)
						<option value="{{ $user->id }}">{{ $user->name }} {{ $user->last_name }} {{ $user->scnd_last_name }}</option>
						@endforeach
					</select><br>
				</p>
			</div>
			<br><br>
			<center>
				@component('components.labels.title-divisor')    Datos de empleado @endcomponent<br>
				
			</center>
			<div class="div-form-group" style="max-width: 400px;">
				<p>
					<select class="js-employees removeselect" name="employeeid" multiple="multiple" style="width: 98%;" id="multiple-users">
						@foreach($users as $user)
						<option value="{{ $user->id }}">{{ $user->name }} {{ $user->last_name }} {{ $user->scnd_last_name }}</option>
						@endforeach
					</select><br>
				</p>
				<p>
					<select class="js-enterprises removeselect" name="enterprise_id" multiple="multiple" id="multiple-enterprises select2-selection--multiple" style="width: 98%; border: 0px;">
						@foreach($enterprises as $enterprise)
						<option value="{{ $enterprise->id }}">{{ strlen($enterprise->name) >= 35 ? substr(strip_tags($enterprise->name),0,35).'...' : $enterprise->name }}</option>
						@endforeach
					</select><br>
				</p>

				<p>
					<select class="js-areas removeselect input-text" multiple="multiple" name="area_id" style="width: 98%;" id="multiple-areas">
						@foreach($areas as $area)
						<option value="{{ $area->id }}">{{ $area->name }}</option>
						@endforeach
					</select><br>
				</p>
				<p>
					<select class="js-departments removeselect input-text" multiple="multiple" name="department_id" style="width: 98%;" id="multiple-departments">
						@foreach($departments as $department)
						<option value="{{ $department->id }}">{{ $department->name }}</option>
						@endforeach
					</select><br>
				</p>
				<p>
					<select class="js-accounts removeselect" class="input-text" multiple="multiple" name="accountid" style="width: 98%;" id="multiple-accounts">
						
					</select><br>
				</p>
				<p>
					<select class="js-projects removeselect" name="projectid" multiple="multiple" style="width: 98%;" id="multiple-projects">
						@foreach(App\Project::orderName()->where('status',1)->get() as $project)
						<option value="{{ $project->idproyect }}">{{ $project->proyectName }}</option>
						@endforeach
					</select><br>
				</p>
			</div>
			<div class="methodForm" style="display: none;">
				<br><br>
				@component('components.labels.title-divisor')    FORMA DE PAGO @endcomponent<br>
				<center>
					<div class="div-form-group" style="display: flex; overflow-x: auto;">
						<input type="radio" name="method" id="accountBank" value="1">
						<label for="accountBank">Cuenta Bancaria</label> 
						<input type="radio" name="method" id="cash" value="2">
						<label for="cash">Efectivo</label>
						<input type="radio" name="method" id="checks" value="3">
						<label for="checks">Cheque</label>
						<br>
					</div>
				</center>
			</div>
			
			<input type="hidden" name="employee_number" class="input-text employee_number" id="efolio" placeholder="Ingrese el número de empleado">
			<input type="hidden" class="input-text name">
			<input type="hidden" class="input-text last_name">
			<input type="hidden" class="input-text scnd_last_name">

			<div class="resultbank table-responsive" style="display: none;">
				
			</div>
			<br><br>
			<div class="datos" style="display: none;">
				@component('components.labels.title-divisor')    INGRESE LOS SIGUIENTES DATOS @endcomponent
				<div class='form-container'>
					<p>
						<label class='label-form'>Referencia</label><input type='text' placeholder='Ingrese la referencia' class='input-text reference'>
					</p>
					<p>
						<label class='label-form'>Importe</label><input type='text' placeholder='Ingrese el importe' class='input-text amount'>
					</p>
					<p>
						<label class='label-form'>Razón de pago</label><input type='text' placeholder='Ingrese la razón de pago' class='input-text reason_payment'>
					</p>
				</div>
				<div class='form-container'><p><button type='button' name='add' id='add'><div class='btn_plus'>+</div> Agregar concepto</button><button type='button' name='canc' id='exit' class='btn'>Cancelar</button></p></div>
			</div>
				
			<div class="form-container" >
				<div class="form-container">
					<div class="table-responsive">
						<table id="table2" class="table-no-bordered">
							<thead>
								<th># Empleado</th>
								<th>Nombre del Empleado</th>
								<th>Empresa</th>
								<th>Proyecto</th>
								<th hidden>Departamento</th>
								<th hidden>Dirección</th>
								<th hidden>Clasificación de gasto</th>
								<th hidden>Banco</th>
								<th hidden># Tarjeta</th>
								<th hidden>Cuenta</th>
								<th hidden>CLABE</th>
								<th>Referencia</th>
								<th>Importe</th>
								<th>Razón</th>
								<th></th>
							</thead>
							<tbody id="body-payroll" class="request-validate">
								
							</tbody>
							<tfoot>
								<th></th>
							</tfoot>
						</table>
					</div>
					<br>
					
				</div>
				<div class="form-container">
					<div class="table-responsive">
						<table class="table-no-bordered">
							<thead>
								<th width="20%"></th>
								<th width="20%"></th>
								<th width="20%"></th>
								<th width="20%">TOTAL:</th>
								<th width="20%"><input id="input-extrasmall" placeholder="Ingrese el total" readonly class="input-table total" type="text" name="total"></th>
							</thead>
						</table>
					</div>
					<br>
				</div>
				<center>
					<p>
						<input class="btn btn-red" type="submit" name="enviar" value="ENVIAR SOLICITUD"> 
						<input type="submit" name="save" value="GUARDAR SIN ENVIAR" class="btn btn-blue save" formaction="{{ route('payroll.unsent') }}" id="save"> 
						<input class="btn btn-delete-form" type="reset" name="borra" value="Borrar campos"> 
					</p>
				</center>
			</div>
				
		{!! Form::close() !!}


	@endsection

	@section('scripts')
		<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
		<script src="{{ asset('js/jquery-ui.js') }}"></script>
		<script src="{{ asset('js/jquery.numeric.js') }}"></script>
		<script src="{{ asset('js/datepicker.js') }}"></script>
		<script>
			$.validate(
			{
				form: '#container-alta',
				onError   : function($form)
				{
					swal('', '{{ Lang::get("messages.form_error") }}', 'error');
				},
				onSuccess : function($form)
				{
					if($('.request-validate').length>0)
					{
						conceptos   = $('#body-payroll tr').length;
						reference   = $('.reference').val();
						amount      = $('.amount').val();
						reason      = $('.reason_payment').val();
						check       = $('input[name="idemp"]').is(':checked');
						
						if(check == false)
						{
							swal('', 'Por favor seleccione una cuenta', 'error');
						}
						
						if(reason=="" || amount=="")
						{
							if(amount=="")
							{
								$('.amount').addClass('error');
							}
							if(reason=="")
							{
								$('.reason_payment').addClass('error');
							}
						}
						
						if(conceptos>0)
						{
							swal("Cargando",{
								icon: '{{ url('images/loading.svg') }}',
								button: false,
							});
							return true;
						}
						else
						{
							swal('', 'Debe seleccionar un empleado y una cuenta bancaria', 'error');
							return false;
						}
					}
					else
					{
						swal("Cargando",{
							icon: '{{ url('images/loading.svg') }}',
							button: false,
						});
						return true;
					}
				}
			});

			$(document).ready(function()
			{
				$('.js-users').select2({
					placeholder: 'Nombre del Solicitante',
					language: "es",
					maximumSelectionLength: 1
				})
				.on("change",function(e)
				{
					if($(this).val().length>1)
					{
						$(this).val($(this).val().slice(0,1)).trigger('change');
					}
				});
				$('.js-employees').select2({
					placeholder: 'Seleccione un Empleado',
					language: "es",
					maximumSelectionLength: 1
				})
				.on("change",function(e)
				{
					if($(this).val().length>1)
					{
						$(this).val($(this).val().slice(0,1)).trigger('change');
					}
				});
				$('.js-enterprises').select2(
				{
					placeholder: 'Seleccione la Empresa',
					language: "es",
					maximumSelectionLength: 1
				})
				.on("change",function(e)
				{
					if($(this).val().length>1)
					{
						$(this).val($(this).val().slice(0,1)).trigger('change');
					}
				});
				$('.js-areas').select2(
				{
					placeholder: 'Seleccione la Dirección',
					language: "es",
					maximumSelectionLength: 1
				})
				.on("change",function(e)
				{
					if($(this).val().length>1)
					{
						$(this).val($(this).val().slice(0,1)).trigger('change');
					}
				});
				
				$('.js-departments').select2(
				{
					placeholder: 'Seleccione el Departamento',
					language: "es",
					maximumSelectionLength: 1
				})
				.on("change",function(e)
				{
					if($(this).val().length>1)
					{
						$(this).val($(this).val().slice(0,1)).trigger('change');
					}
				});
				$('.js-projects').select2(
				{
					placeholder: 'Seleccione el Proyecto/Contrato',
					language: "es",
					maximumSelectionLength: 1
				})
				.on("change",function(e)
				{
					if($(this).val().length>1)
					{
						$(this).val($(this).val().slice(0,1)).trigger('change');
					}
				});
				$('.js-accounts').select2(
				{
					placeholder: 'Clasificación de Gasto',
					language: "es",
					maximumSelectionLength: 1
				})
				.on("change",function(e)
				{
					if($(this).val().length>1)
					{
						$(this).val($(this).val().slice(0,1)).trigger('change');
					}
				});

				$('.card_number,.destination_account,.destination_key,.employee_number,.amount').numeric(false);    // números
				$('.amount,.importe',).numeric({ altDecimal: ".", decimalPlaces: 2 });
				$('.amount').numeric({ negative : false });

				$(function() 
				{
					$(".datepicker2").datepicker({ minDate: 0, dateFormat: "yy-mm-dd" });
				});

				$(document).on('change', '.js-employees', function()
				{
					id = $(this).val();
					$('.datos').stop().show();
					$('.methodForm').stop().show();
					text = $('#efolio').val();
					$.ajax(
					{
						type : 'get',
						url  : '{{ url("administration/payroll/search/bank") }}',
						data : {'employee_number':id},
						success:function(data)
						{
							$('.resultbank').html(data);
						},
						error : function()
						{
							swal('','Sucedió un error, por favor intente de nuevo.','error');
							$('.resultbank').html('');
						}
					});

				})
				.on('click','#exit', function(){

					$(".formulario").slideToggle();
					$('#table').slideToggle();
					$('.resultbank').stop().hide();
					$('.result').stop().hide();
				})
				.on('click','.btn-delete-form',function(e)
				{
					e.preventDefault();
					form = $(this).parents('form');
					swal({
						title       : "Limpiar formulario",
						text        : "¿Confirma que desea limpiar el formulario?",
						icon        : "warning",
						buttons     : ["Cancelar","OK"],
						dangerMode  : true,
					})
					.then((willClean) =>
					{
						if(willClean)
						{
							$('#body-payroll').html('');
							$('.removeselect').val(null).trigger('change');
							$('.resultbank').hide();
							$('.result').hide();
							form[0].reset();
						}
						else
						{
							swal.close();
						}
					});
				})
				.on('keyup','#input-search', function(){
				
					$text = $(this).val().trim();
					if ($text == "") 
					{
						$('#not-found').stop().show();
						$('#not-found').html("RESULTADO NO ENCONTRADO");
						$('#table').stop().hide();
						$('.resultbank').stop().hide();
						$('.result').stop().hide();
						$('.datos').stop().hide();
						$('.methodForm').stop().hide();
					}
					else
					{
						$('#not-found').stop().hide();
						$('.result').stop().show();
						$.ajax({
							type : 'get',
							url  : '{{ url("administration/payroll/search/user") }}',
							data : {'search':$text},
							success:function(data)
							{
								$('.result').html(data);
							},
							error : function()
							{
								swal('','Sucedió un error, por favor intente de nuevo.','error');
								$('.result').html('');
							}
						}); 
					}
				})
				.on('change','.js-enterprises',function()
				{
					$('.js-accounts').empty();
					$enterprise = $(this).val();
					$.ajax(
					{
						type    : 'get',
						url     : '{{ url("/administration/payroll/create/account") }}',
						data    : {'enterpriseid':$enterprise},
						success : function(data)
						{
							$.each(data,function(i, d)
							{
								$('.js-accounts').append('<option value='+d.idAccAcc+'>'+d.account+' - '+d.description+' ('+d.content+')</option>');
							});
						},
						error : function()
						{
							swal('','Sucedió un error, por favor intente de nuevo.','error');
							$('.js-accounts').val(null).trigger('change');
						}
					})
				})
				.on('click','#save',function()
				{
					$('.remove').removeAttr('data-validation');
					$('.removeselect').removeAttr('required');
					$('.removeselect').removeAttr('data-validation');
					$('.request-validate').removeClass('request-validate');
				})
				.on('click','.checkbox',function()
				{
					$('.marktr').removeClass('marktr');
					$(this).parents('tr').addClass('marktr');
				})
				.on('click','.delete-item',function()
				{
					$(this).parents('tr').remove();

					var sumatotal = 0;
					
					$(".importe").each(function(i, v)
					{
						valor = parseFloat($(this).val());
						sumatotal = sumatotal + valor ;
					});
					
					$(".total").val(sumatotal);
					
				})
				.on('click','#add',function()
				{
					employee_number = $('.js-employees option:checked').val();
					name            = $('.js-employees option:checked').text();
					
					enterprise      = $('.js-enterprises option:checked').text();
					enterpriseid    = $('.js-enterprises option:checked').val();
					
					department      = $('.js-departments option:checked').text();
					departmentid    = $('.js-departments option:checked').val();
					
					direction       = $('.js-areas option:checked').text();
					directionid     = $('.js-areas option:checked').val();
					
					accounttext     = $('.js-accounts option:checked').text();
					accountid       = $('.js-accounts option:checked').val();
					
					project         = $('.js-projects option:checked').text();
					projectid       = $('.js-projects option:checked').val();
					
					reference       = $('.reference').val();
					amount          = $('.amount').val();
					reason          = $('.reason_payment').val();
					check           = $('input[name="idemp"]').is(':checked');
					bank            = $('input[name="idemp"]:checked').parent('td').parent('tr').find('.bank').val();
					clabe           = $('input[name="idemp"]:checked').parent('td').parent('tr').find('.clabe').val();
					account         = $('input[name="idemp"]:checked').parent('td').parent('tr').find('.account').val();
					card_number     = $('input[name="idemp"]:checked').parent('td').parent('tr').find('.card_number').val();
					idpaymentmethod = $('input[name="method"]:checked').val();

					if (idpaymentmethod == "" || idpaymentmethod == undefined) 
					{
						swal('', 'Por favor seleccione una forma de pago', 'error');
					}
					else
					{
						if (idpaymentmethod == 1) 
						{
							if (employee_number == "" || name == "" || amount == "" || reason == "" || enterpriseid=="" || directionid=="" || accountid=="" || projectid=="" ||  enterpriseid == undefined ||directionid==undefined || accountid==undefined || projectid==undefined )
							{
								if(amount=="" || amount == 0)
								{
									$('.amount').addClass('error');
								}
								if(reason=="")
								{
									$('.reason_payment').addClass('error');
								}
								swal('', 'Por favor llene los campos necesarios', 'error');
							}
							else if(check == false)
							{
								swal('', 'Por favor seleccione una cuenta', 'error');
							}
							else
							{
								tr_table    = $('<tr></tr>')
												.append($('<td hidden></td>')
													.append(idpaymentmethod)
													.append($('<input readonly class="input-table" type="hidden" name="t_idpaymentmethod[]">').val(idpaymentmethod))
												)
												.append($('<td></td>')
													.append(employee_number)
													.append($('<input readonly class="input-table" type="hidden" name="t_employee_number[]">').val(employee_number))
												)
												.append($('<td></td>')
													.append(name)
													.append($('<input readonly class="input-table" type="hidden"/>').val(name))
												)
												.append($('<td></td>')
													.append(enterprise)
													.append($('<input readonly class="input-table" type="hidden" name="t_enterprise[]">').val(enterpriseid))
												)
												.append($('<td></td>')
													.append(project)
													.append($('<input readonly class="input-table" type="hidden" name="t_project[]">').val(projectid))
												)
												.append($('<td hidden></td>')
													.append(department)
													.append($('<input readonly class="input-table" type="hidden" name="t_department[]">').val(departmentid))
												)
												.append($('<td hidden></td>')
													.append(direction)
													.append($('<input readonly class="input-table" type="hidden" name="t_direction[]">').val(directionid))
												)
												.append($('<td hidden></td>')
													.append(accounttext)
													.append($('<input readonly class="input-table" type="hidden" name="t_accountid[]">').val(accountid))
												)
												.append($('<td hidden></td>')
													.append(bank)
													.append($('<input readonly class="input-table" type="hidden" name="t_bank[]">').val(bank))
												)
												.append($('<td hidden></td>')
													.append(card_number)
													.append($('<input readonly class="input-table" type="hidden" name="t_card_number[]">').val(card_number))
												)
												.append($('<td hidden></td>')
													.append(account)
													.append($('<input readonly class="input-table" type="hidden" name="t_account[]">').val(account))
												)
												.append($('<td hidden></td>')
													.append(clabe)
													.append($('<input readonly class="input-table" type="hidden" name="t_clabe[]">').val(clabe))
												)
												.append($('<td></td>')
													.append(reference)
													.append($('<input readonly class="input-table" type="hidden" name="t_reference[]">').val(reference))
												)
												.append($('<td></td>')
													.append(amount)
													.append($('<input readonly class="input-table importe" type="hidden" name="t_amount[]">').val(amount))
												)
												.append($('<td></td>')
													.append(reason)
													.append($('<input readonly class="input-table" type="hidden" name="t_reason_payment[]">').val(reason))
												)
												.append($('<td></td>')
													.append($('<button class="delete-item"></button>')
														.append($('<span class="icon-x delete-span"></span>'))
													)
												);

								$('#body-payroll').append(tr_table);

								var sumatotal = 0;
								$(".importe").each(function(i, v)
								{
									valor = parseFloat($(this).val());
									sumatotal = sumatotal + valor ;
								});

								$('.js-accounts,.js-departments,.js-enterprises,.js-employees,.js-projects,.js-areas').val(null).trigger('change');
								$(".total").val(sumatotal);
								$('#input-search').val('');
								$(".formulario").slideToggle();
								$('#table').slideToggle();
								$('.datos').stop().hide();
								$('.methodForm').stop().hide();
								$('.result').stop().hide();
								$('.reference').val('');
								$('.amount').val('');
								$('.reason_payment').val('');
								$('.resultbank').stop().hide();
							}   
						}
						else
						{
							if (employee_number == "" || name == "" || amount == "" || reason == "" || enterpriseid=="" || directionid=="" || accountid=="" || projectid=="" ||  enterpriseid == undefined ||directionid==undefined || accountid==undefined || projectid==undefined )
							{
								if(amount=="" || amount == 0)
								{
									$('.amount').addClass('error');
								}
								if(reason=="")
								{
									$('.reason_payment').addClass('error');
								}
								swal('', 'Por favor llene los campos necesarios', 'error');
							}
							else
							{
								tr_table    = $('<tr></tr>')
												.append($('<td hidden></td>')
													.append(idpaymentmethod)
													.append($('<input readonly class="input-table" type="hidden" name="t_idpaymentmethod[]">').val(idpaymentmethod))
												)
												.append($('<td></td>')
													.append(employee_number)
													.append($('<input readonly class="input-table" type="hidden" name="t_employee_number[]">').val(employee_number))
												)
												.append($('<td></td>')
													.append(name)
													.append($('<input readonly class="input-table" type="hidden"/>').val(name))
												)
												.append($('<td></td>')
													.append(enterprise)
													.append($('<input readonly class="input-table" type="hidden" name="t_enterprise[]">').val(enterpriseid))
												)
												.append($('<td></td>')
													.append(project)
													.append($('<input readonly class="input-table" type="hidden" name="t_project[]">').val(projectid))
												)
												.append($('<td hidden></td>')
													.append(department)
													.append($('<input readonly class="input-table" type="hidden" name="t_department[]">').val(departmentid))
												)
												.append($('<td hidden></td>')
													.append(direction)
													.append($('<input readonly class="input-table" type="hidden" name="t_direction[]">').val(directionid))
												)
												.append($('<td hidden></td>')
													.append(accounttext)
													.append($('<input readonly class="input-table" type="hidden" name="t_accountid[]">').val(accountid))
												)
												.append($('<td hidden></td>')
													.append('x')
													.append($('<input readonly class="input-table" type="hidden" name="t_bank[]">').val('x'))
												)
												.append($('<td hidden></td>')
													.append('x')
													.append($('<input readonly class="input-table" type="hidden" name="t_card_number[]">').val('x'))
												)
												.append($('<td hidden></td>')
													.append('x')
													.append($('<input readonly class="input-table" type="hidden" name="t_account[]">').val('x'))
												)
												.append($('<td hidden></td>')
													.append('x')
													.append($('<input readonly class="input-table" type="hidden" name="t_clabe[]">').val('x'))
												)
												.append($('<td></td>')
													.append(reference)
													.append($('<input readonly class="input-table" type="hidden" name="t_reference[]">').val(reference))
												)
												.append($('<td></td>')
													.append(amount)
													.append($('<input readonly class="input-table importe" type="hidden" name="t_amount[]">').val(amount))
												)
												.append($('<td></td>')
													.append(reason)
													.append($('<input readonly class="input-table" type="hidden" name="t_reason_payment[]">').val(reason))
												)
												.append($('<td></td>')
													.append($('<button class="delete-item"></button>')
														.append($('<span class="icon-x delete-span"></span>'))
													)
												);

								$('#body-payroll').append(tr_table);

								var sumatotal = 0;
								$(".importe").each(function(i, v)
								{
									valor = parseFloat($(this).val());
									sumatotal = sumatotal + valor ;
								});

								$('.js-accounts,.js-departments,.js-enterprises,.js-employees,.js-projects,.js-areas').val(null).trigger('change');
								$(".total").val(sumatotal);
								$('#input-search').val('');
								$(".formulario").slideToggle();
								$('#table').slideToggle();
								$('.datos').stop().hide();
								$('.methodForm').stop().hide();
								$('.result').stop().hide();
								$('.reference').val('');
								$('.amount').val('');
								$('.reason_payment').val('');
								$('.resultbank').stop().hide();
							}
						}
					}
					
				})
				.on('click','input[name="method"]',function()
				{
					if($(this).val() == 1)
					{
						$('.resultbank').stop(true,true).slideDown().show();
					}
					else
					{
						$('.resultbank').stop(true,true).slideUp().hide();
					}
				})
			});

		</script>
	@endsection
@endif