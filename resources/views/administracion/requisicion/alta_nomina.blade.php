@extends('layouts.child_module')

@section('css')
	<style type="text/css">
		#container-data
		{
			display		: block;
			margin		: auto;
			max-width	: 600px;
			padding		: 1em 5%;
		}
		.box
		{
			background-color	: #fff;
			padding				: 2rem 1rem;
		}
		.inputfile
		{
			height		: 0.1px;
			opacity		: 0;
			overflow	: hidden;
			position	: absolute;
			width		: 0.1px;
			z-index		: -1;
		}
		.inputfile + label
		{
			background-color	: #eb3621;
			color				: #fff;
			cursor				: pointer;
			display				: inline-block;
			font-size			: 1.25rem;
			font-weight			: 700;
			max-width			: 80%;
			overflow			: hidden;
			padding				: 0.625rem 1.25rem;
			text-overflow		: ellipsis;
			white-space			: nowrap;
		}
		.inputfile + label svg
		{
			fill			: currentColor;
			height			: 1em;
			margin-right	: 0.25em;
			margin-top		: -0.25em;
			vertical-align	: middle;
			width			: 1em;
		}
		.inputfile:focus + label,
		.inputfile + label:hover
		{
			background-color	: #db3831;
		}
		ul
		{
			list-style		: disc;
			padding-left	: .5em;
		}

		.table .thead-dark th 
		{
			width: 50em;
		}
		.select_father
		{
			display: none;
		}
	</style>
@endsection

@section('data')
	
	<label class="label">Seleccione el tipo de requisición:</label>
	<div class="container-sub-blocks">
		<a href="{{ route('requisition.create.material') }}" class="sub-block">Material</a>
		<a href="{{ route('requisition.create.service') }}" class="sub-block">Servicio</a>
		<a href="{{ route('requisition.create.nomina') }}" class="sub-block sub-block-active">Nómina</a>
	</div>
	<p><br></p>
	@if(isset($request))
		@if (isset($new_requisition) && $new_requisition)
			{!! Form::open(['route' => 'requisition.store', 'method' => 'POST', 'id' => 'container-alta','files' => true]) !!}
		@else
			{!! Form::open(['route' => ['requisition.update',$request->folio], 'method' => 'PUT', 'id' => 'container-alta','files' => true]) !!}
		@endif
	@else
		{!! Form::open(['route' => 'requisition.store', 'method' => 'POST', 'id' => 'container-alta','files' => true]) !!}
	@endif
		@if(isset($request) && !isset($new_requisition))
		<p>
			<b>Folio: {{ $request->folio }}</b>
		</p>
	@endif
	<input type="hidden" name="requisition_type" value="3">
	<div class="table-responsive">
		<table class="table">
			<thead class="thead-dark" style="min-width: 100%;">
				<th colspan="4">NUEVA REQUISICIÓN</th>
			</thead>
		</table>
	</div>
	<div class="form-row px-3">
		<div class="form-group col-md-6 mb-4">
			<label><b>Proyecto:</b></label>
			<select name="project_id" class="form-control removeselect" multiple="multiple" data-validation="required" @if(isset($request) && $request->status != 2) disabled="disabled" @endif>
				@foreach(App\Project::where('status',1)->
				whereIn('idproyect',Auth::user()->inChargeProject(229)->pluck('project_id'))
				->orderBy('proyectName','asc')->get() as $project)
					<option value="{{ $project->idproyect }}" @if(isset($request) && $request->idProject == $project->idproyect) selected="selected" @endif>{{ $project->proyectName }}</option>
				@endforeach
			</select>
		</div>
		<div class="form-group col-md-6 mb-4 select_father" @if(isset($request)) @if($request->idProject == 75 || $request->idProject == 126) style="display: table-row;" @endif @else style="display: table-row;" @endif>
			<label><b>Código WBS:</b></label>
			<select name="code_wbs" class="removeselect" multiple="multiple" data-validation="required" @if(isset($request) && $request->status != 2) disabled="disabled" @endif>
				@if(isset($request) && ($request->idProject == 75 || $request->idProject == 126))
					@foreach(App\CatCodeWBS::where('project_id',$request->idProject)->orderBy('code_wbs','asc')->get() as $code)
						<option value="{{ $code->id }}" @if(isset($request) && $request->requisition->code_wbs == $code->id) selected="selected" @endif>{{ $code->code_wbs }}</option>
					@endforeach
				@endif
			</select>
		</div>
		<div id="codeEDTContainer" class="form-group col-md-6 mb-4 select_father" @if(isset($request)) @if(($request->idProject == 75 || $request->idProject == 126) && $request->requisition->wbs()->exists() && $request->requisition->wbs->codeEDT()->exists())) style="display: table-row;" @endif @else style="display: table-row;" @endif>
			<label><b>Código EDT:</b></label>
			<select name="code_edt" class="removeselect" multiple="multiple" data-validation="required" @if(isset($request) && $request->status != 2) disabled="disabled" @endif>
				@if(isset($request))
					@foreach(App\CatCodeEDT::where('codewbs_id',$request->requisition->code_wbs)->get() as $edt)
						<option value="{{ $edt->id }}" @if(isset($request) && $request->requisition->code_edt == $edt->id) selected="selected" @endif>{{ $edt->code.' ('.$edt->description.')' }}</option>
					@endforeach
				@endif
			</select>
		</div>
	</div>
	<div class="form-row px-3">
		<div class="form-group col-md-6 mb-4">
			<label><b>Título:</b></label>
			<input type="text" name="title" class="new-input-text removeselect" placeholder="Ej. Nómina de..." data-validation="required" value="{{ isset($request) ? $request->requisition->title : '' }}" @if(isset($request) && $request->status != 2) disabled="disabled" @endif>
		</div>
		<div class="form-group col-md-6 mb-4">
			<label><b>No.</b></label>
			@php
				if (isset($request) && !isset($new_requisition)) 
				{
					$value = $request->requisition->number;
				}
				elseif(isset($request) && isset($new_requisition))
				{
					$count	= App\RequestModel::where('kind',19)->where('idProject',$request->idProject)->count();
					$value	= $count + 1;
				}	
				else
				{
					$value = '';
				}
			@endphp	
			<input type="text" name="number" class="new-input-text removeselect" placeholder="Escribe aquí" data-validation="required" @if(isset($request) && ($request->idProject != 75 && $request->idProject != 126)) readonly="readonly" @endif value="{{ $value }}" @if(isset($request) && $request->status != 2) disabled="disabled" @endif>
		</div>
		<div class="form-group col-md-6 mb-4">
			<label><b>Fecha en que deben estar en obra:</b></label>
			<input type="text" class="new-input-text removeselect datepicker" name="date_obra" data-validation="required" placeholder="Seleccione una fecha" readonly="readonly" value="{{ isset($request) ? $request->requisition->date_obra : '' }}" @if(isset($request) && $request->status != 2) disabled="disabled" @endif>
		</div>
		<div class="form-group col-md-6 mb-4">
			<label><b>Solicitante</b></label>
			<select name="request_requisition" class="removeselect" multiple="multiple" data-validation="required" @if(isset($request) && $request->status != 2) disabled="disabled" @endif>
				@foreach(App\CatRequestRequisition::orderBy('name','ASC')->get() as $requestRequisition)
					<option value="{{ $requestRequisition->name }}" @if(isset($request) && $request->requisition->request_requisition != "" && $request->requisition->request_requisition == $requestRequisition->name) selected="selected" @endif>{{ $requestRequisition->name }}</option>
				@endforeach
			</select>
		</div>
		<div class="form-group col-md-6 mb-4">
			<label><b>Prioridad:</b></label>
			<select name="urgent" class="removeselect form-control" multiple="multiple" data-validation="required" @if(isset($request) && $request->status != 2) disabled="disabled" @endif>
				<option value="0" @if(isset($request) && $request->requisition->urgent == 0) selected="selected" @endif>Baja</option>
				<option value="1" @if(isset($request) && $request->requisition->urgent == 1) selected="selected" @endif>Media</option>
				<option value="2" @if(isset($request) && $request->requisition->urgent == 2) selected="selected" @endif>Alta</option>
			</select>
		</div>
	</div>
	<p><br></p>
	@component('components.labels.title-divisor')    CARGA MASIVA (OPCIONAL) @endcomponent
	<div class="alert alert-info" id="error_request" role="alert">
		Si desea cargar artículos de forma masiva, utilice la siguiente plantilla. <a class="btn btn-blue" href="{{route('requisition.download-layout.service-nomina')}}">DESCARGAR PLANTILLA</a>
		<br>En el archivo se indica como debe llenarse los campos "categoría y unidad".
	</div>
	<center>
		<div style="text-align: center; background: #FFFBF0; padding: 15px; width: 50%;">
			<p>
				<input type="file" name="csv_file" id="files" class="inputfile inputfile-1" accept=".csv"/>
				<label for="files"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="17" viewBox="0 0 20 17"><path d="M10 0l-5.2 4.9h3.3v5.1h3.8v-5.1h3.3l-5.2-4.9zm9.3 11.5l-3.2-2.1h-2l3.4 2.6h-3.5c-.1 0-.2.1-.2.1l-.8 2.3h-6l-.8-2.2c-.1-.1-.1-.2-.2-.2h-3.6l3.4-2.6h-2l-3.2 2.1c-.4.3-.7 1-.6 1.5l.6 3.1c.1.5.7.9 1.2.9h16.3c.6 0 1.1-.4 1.3-.9l.6-3.1c.1-.5-.2-1.2-.7-1.5z"/></svg> <span>Seleccione un archivo&hellip;</span></label>
			</p>
			<p>
				<label class="label-form">Separador</label>
				<select class="custom-select" name="separator">
					<option value=",">coma (,)</option>
					<option value=";">punto y coma (;)</option>
				</select>
				<br>
				<b>*Solo archivos .csv</b>
			</p>
			<p>
				@if(isset($request) && !isset($new_requisition))
					@if(isset($request) && $request->status == 2) 
						<button type="submit" id="upload_file" class="btn btn-blue" formaction="{{ route('requisition.save-follow',$request->folio) }}">CARGAR ARCHIVO</button>
					@endif
				@else
					<button type="submit" id="upload_file" class="btn btn-blue" formaction="{{ route('requisition.store-detail') }}">CARGAR ARCHIVO</button>
				@endif
			</p>
		</div>
	</center>
	<p><br></p>
	<div class="table-responsive table-striped">
		<table class="table">
			<thead class="thead-dark">
				<tr>
					<th colspan="4">ARTÍCULOS</th>
				</tr>
				<tr>
					<th width="10%">Partida</th>
					<th width="10%">Cant.</th>
					<th width="35%">Descripción</th>
					<th></th>
				</tr>
			</thead>
			<tbody id="body_art" class="request-validate">
				@if(isset($request) && $request->requisition->details()->exists())
					@foreach($request->requisition->details as $detail)
						<tr>
							<td>
								@if (isset($new_requisition))
									<input type="hidden" class="t_part" name="part[]" value="{{ $detail->part }}">
									{{ $detail->part }}
								@else
									<input type="hidden" name="idRequisitionDetail[]" class="id" value="{{ $detail->id }}">
									<input type="hidden" class="t_part" value="{{ $detail->part }}">
									{{ $detail->part }}
								@endif
							</td>
							<td>
								@if (isset($new_requisition))
									<input type="hidden" class="t_quantity" name="quantity[]" value="{{ $detail->quantity }}">
								@else
									<input type="hidden" class="t_quantity" value="{{ $detail->quantity }}">
								@endif
								{{ $detail->quantity }}
							</td>
							<td>
								@if (isset($new_requisition))
									<input type="hidden" class="t_description" name="description[]" value="{{ $detail->description }}">
								@else
									<input type="hidden" class="t_description" value="{{ $detail->description }}">
								@endif
								{{ $detail->description }}
							</td>
							<td class="text-nowrap">
								@if(isset($request) && ($request->status == 2 || isset($new_requisition)))
									<button class="btn btn-green edit-art" type="button"><span class="icon-pencil"></span></button>
									<button class="btn btn-red delete-art" type="button"><span class="icon-x"></span></button>
								@endif
							</td>
						</tr>
					@endforeach
				@endif
			</tbody>
			<tfoot>
				<tr>
					<td>
						<input type="text" class="part-art new-input-text" placeholder="0">
					</td>
					<td>
						<input type="text" class="quantity-art new-input-text" placeholder="0">
					</td>
					<td>
						<input type="text" class="description-art new-input-text" placeholder="Descripción">
					</td>
					<td>
						<button class="btn btn-green" id="addArt" type="button"><span class="icon-plus"></span></button>
					</td>
				</tr>
			</tfoot>
		</table>
	</div>
	<p><br></p>
	@component('components.labels.title-divisor')    DOCUMENTOS DE LA REQUISICIÓN @endcomponent
	@if(isset($request) && $request->requisition->documents()->exists())
		<div class="table-responsive table-striped">
			<table class="table">
				<thead class="thead-dark">
					<th>Nombre</th>
					<th>Archivo</th>
					<th>Modificado Por</th>
					<th>Fecha</th>
				</thead>
				<tbody>
					@foreach($request->requisition->documents->sortByDesc('created') as $doc)
						<tr>
							<td>
								{{ $doc->name }}
							</td>
							<td>
								<a target="_blank" href="{{ url('docs/requisition/'.$doc->path) }}">{{ $doc->path }}</a>
							</td>
							<td>
								{{ $doc->user->fullName() }}
							</td>
							<td>
								{{ Carbon\Carbon::parse($doc->created)->format('d-m-Y') }}
							</td>
						</tr>
					@endforeach
				</tbody>
			</table>
		</div>
	@endif
	<center>
		@if(isset($request) && !isset($new_requisition))
			@if($request->status != 17 || $request->status == 2)
				<div id="documents-requisition">
					<div class="docs-p">
						<div class="docs-p-l">
							<select class="custom-select nameDocumentRequisition" name="nameDocumentRequisition[]">
								<option value="0" disabled selected>Seleccione uno</option>
								<option value="Cotización">Cotización</option>
								<option value="Ficha Técnica">Ficha Técnica</option>
								<option value="Control de Calidad">Control de Calidad</option>
								<option value="Contrato">Contrato</option>
								<option value="Factura">Factura</option>
								<option value="REQ. OC. FAC.">REQ. OC. FAC.</option>
								<option value="Otro">Otro</option>
							</select><br><br>
							<div class="uploader-content">
								<input type="file" name="path" class="input-text pathActionerRequisition" accept=".pdf,.jpg,.png">
							</div>
							<input type="hidden" name="realPathRequisition[]" class="path">
						</div>
						<div class="docs-p-r">
							<button class="delete-doc" type="button"><span class="icon-x delete-span"></span></button>
						</div>
					</div>
				</div>
				<p>
					<button type="button" class="btn btn-orange" name="addDocRequisition" id="addDocRequisition"><span class="icon-plus"></span><span>Nuevo documento</span></button>
				</p>
				<input class="btn btn-green save" type="submit" id="save" name="save" value="CARGAR DOCUMENTOS" formaction="{{ route('requisition.upload-documents',$request->folio) }}">
			@endif
		@else
			<div id="documents-requisition">
				<div class="docs-p">
					<div class="docs-p-l">
						<select class="custom-select nameDocumentRequisition" name="nameDocumentRequisition[]">
							<option value="0" disabled selected>Seleccione uno</option>
							<option value="Cotización">Cotización</option>
							<option value="Ficha Técnica">Ficha Técnica</option>
							<option value="Control de Calidad">Control de Calidad</option>
							<option value="Contrato">Contrato</option>
							<option value="Factura">Factura</option>
							<option value="REQ. OC. FAC.">REQ. OC. FAC.</option>
							<option value="Otro">Otro</option>
						</select><br><br>
						<div class="uploader-content">
							<input type="file" name="path" class="input-text pathActionerRequisition" accept=".pdf,.jpg,.png">
						</div>
						<input type="hidden" name="realPathRequisition[]" class="path">
					</div>
					<div class="docs-p-r">
						<button class="delete-doc" type="button"><span class="icon-x delete-span"></span></button>
					</div>
				</div>
			</div>
			<p>
				<button type="button" class="btn btn-orange" name="addDocRequisition" id="addDocRequisition"><span class="icon-plus"></span><span>Nuevo documento</span></button>
			</p>
		@endif
	</center>
	<span id="spanDelete"></span>
	<center>

		@if(isset($request))
			@if($request->status == 2 && !isset($new_requisition))
				<input class="btn btn-red" type="submit" name="send" value="ENVIAR REQUISICIÓN">
				<input class="btn btn-blue save" type="submit" id="save" name="save" value="GUARDAR CAMBIOS" formaction="{{ route('requisition.save-follow',$request->folio) }}">
			@endif
			@if (isset($new_requisition) && $new_requisition)
				<input class="btn btn-red" type="submit" name="send" value="ENVIAR REQUISICIÓN">
				<input class="btn btn-blue save" type="submit" id="save" name="save" value="GUARDAR CAMBIOS" formaction="{{ route('requisition.save') }}">	
			@endif
		@else
			<input class="btn btn-red" type="submit" name="send" value="ENVIAR REQUISICIÓN">
			<input class="btn btn-blue save" type="submit" id="save" name="save" value="GUARDAR CAMBIOS" formaction="{{ route('requisition.save') }}">
		@endif

	</center>
	<div id="myModal" class="modal"></div>
@endsection
@section('scripts')
	<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
	<script src="{{ asset('js/jquery-ui.js') }}"></script>
	<script src="{{ asset('js/jquery.numeric.js') }}"></script>
	<script src="{{ asset('js/datepicker.js') }}"></script>
	<script src="{{ asset('js/papaparse.min.js') }}"></script>
	<script type="text/javascript">
		function validation()
		{

			$.validate(
			{
				form	: '#container-alta',
				modules	: 'security',
				onError   : function($form)
				{
					swal('', '{{ Lang::get("messages.form_error") }}', 'error');
				},
				onSuccess : function($form)
				{

					needFileName = false
					$('input[name="realPathRequisition[]').each(function(){
						if($(this).val() != "" )
						{
							
							select = $(this).parents('div').find('.nameDocumentRequisition')
							name = select.find('option:selected').val()

							if(name == 0)
							{
								needFileName = true;
							}
						}
					});

					if(needFileName)
					{
						swal('', 'Debe seleccionar el tipo de documento', 'error');
						return false;
					}

					if($('.request-validate').length>0)
					{
						conceptos	= $('#body_art tr').length;
						if(conceptos>0)
						{
							swal("Cargando",{
								icon				: '{{ asset(getenv('LOADING_IMG')) }}',
								button				: true,
								closeOnClickOutside	: false,
								closeOnEsc			: false
							});
							return true;
						}
						else
						{
							swal('', 'Debe ingresar al menos un concepto de pedido', 'error');
							return false;
						}
					}
					else
					{	
						swal("Cargando",{
							icon				: '{{ asset(getenv('LOADING_IMG')) }}',
							button				: true,
							closeOnClickOutside	: false,
							closeOnEsc			: false
						});
						return true;
					}		
				}
			});
		}
		validation();

		$(document).ready(function()
		{
			$('.quantity,.subtotal-art,.iva-art,.total-art,.quantity-art',).numeric({ altDecimal: ".", decimalPlaces: 2, negative: false });
			$('[name="date_obra"]').datepicker({  dateFormat: "yy-mm-dd" });
			$('[name="urgent"],[name="project_id"],[name="account_id"],[name="code_wbs"],[name="code_edt"]').select2(
			{
				language				: "es",
				maximumSelectionLength	: 1,
				placeholder 			: "Seleccione uno",
				width 					: "100%"
			})
			.on("change",function(e)
			{
				if($(this).val().length>1)
				{
					$(this).val($(this).val().slice(0,1)).trigger('change');
				}
			});
			$('[name="request_requisition"]').select2(
			{
				language				: "es",
				maximumSelectionLength	: 1,
				placeholder 			: "Seleccione uno",
				width 					: "100%",
				tags 					: true,
			})
			.on("change",function(e)
			{
				if($(this).val().length>1)
				{
					$(this).val($(this).val().slice(0,1)).trigger('change');
				}
			});
			$('.unit').select2(
			{
				language				: "es",
				maximumSelectionLength	: 1,
				placeholder 			: "Unidad",
			})
			.on("change",function(e)
			{
				if($(this).val().length>1)
				{
					$(this).val($(this).val().slice(0,1)).trigger('change');
				}
			});
			$('.js-name').select2(
			{
				language				: "es",
				maximumSelectionLength	: 1,
				placeholder 			: "Nombre",
				tags: true
			})
			.on("change",function(e)
			{
				if($(this).val().length>1)
				{
					$(this).val($(this).val().slice(0,1)).trigger('change');
				}
			});
			$('.category').select2(
			{
				language				: "es",
				maximumSelectionLength	: 1,
				placeholder 			: "Categoría"
			})
			.on("change",function(e)
			{
				if($(this).val().length>1)
				{
					$(this).val($(this).val().slice(0,1)).trigger('change');
				}
			});
			$('.js-measurement-unit').select2(
			{
				language				: "es",
				maximumSelectionLength	: 1,
				placeholder 			: "Medida",
				tags: true
			})
			.on("change",function(e)
			{
				if($(this).val().length>1)
				{
					$(this).val($(this).val().slice(0,1)).trigger('change');
				}
			});
			$(document).on('click','#upload_file,#save',function()
			{
				$('.remove').removeAttr('data-validation');
				$('.removeselect').removeAttr('required');
				$('.removeselect').removeAttr('data-validation');
				$('.request-validate').removeClass('request-validate');
			})
			.on('change','#files',function(e)
			{
				label		= $(this).next('label');
				fileName	= e.target.value.split( '\\' ).pop();
				if(fileName)
				{
					label.find('span').html(fileName);
				}
				else
				{
					label.html(labelVal);
				}
			})
			.on('click','[name="send"]',function (e)
			{
				form = $(this).parents('form');
				if ($('[name="csv_file"]').val() != "") 
				{
					e.preventDefault();
					swal({
						title: "Tiene un archivo sin cargar ¿Desea enviar la solicitud?",
						text : "Los registros que se encuentren en el archivo no serán cargados, primero deberá guardar los cambios en el sistema y comprobar que se hayan subido los registros de su archivo.",
						icon: "warning",
						buttons: ["Cancelar","OK"],
					})
					.then((isConfirm) =>
					{
						if(isConfirm)
						{
							form.submit();
						}
					});
				}
				else
				{
					form.submit();
				}
			})
			.on('change','[name="project_id"]',function()
			{
				idproject = $('[name="project_id"] option:selected').val();

				if (idproject == 75 || idproject == 126) 
				{
					$('.select_father').show();
					
					$('[name="number"]').removeAttr('readonly');
					$('[name="code_wbs"]').html('');

					$.ajax(
					{
						type	: 'get',
						url		: '{{ url("administration/requisition/get-wbs") }}',
						data	: {'idproject':idproject},
						success : function(data)
						{
							$.each(data,function(i, d) 
							{
								$('[name="code_wbs"]').append('<option value='+d.id+'>'+d.code_wbs+'</option>');
						 	});

							$('[name="code_wbs"],[name="code_edt"]').select2(
							{
								language				: "es",
								maximumSelectionLength	: 1,
								placeholder 			: "Seleccione uno",
								width 					: "100%"
							})
							.on("change",function(e)
							{
								if($(this).val().length>1)
								{
									$(this).val($(this).val().slice(0,1)).trigger('change');
								}
							});
						},
						error : function()
						{
							swal('','Sucedió un error, por favor intente de nuevo.','error');
							$('.select_father').hide();
						}
					});
				}
				else
				{
					$('.select_father').hide();
					$('[name="code_wbs"]').val(0).trigger('change');
					$('[name="number"]').attr('readonly',true);
					if (idproject != undefined || idproject != "") 
					{
						$.ajax(
						{
							type	: 'get',
							url		: '{{ url("administration/requisition/get-number-requisition") }}',
							data	: {'idproject':idproject},
							success : function(data)
							{
								$('[name="number"]').val(data);
							},
							error : function()
							{
								swal('','Sucedió un error, por favor intente de nuevo.','error');
								$('[name="number"]').val('');
							}
						});
					}
				}


			})
			.on('click','#addArt',function()
			{
				part		= $(this).parents('tr').find('.part-art').val();
				quantity	= $(this).parents('tr').find('.quantity-art').val();
				description	= $(this).parents('tr').find('.description-art').val();

				if (part == "" ||  quantity == "" || description == "") 
				{
					swal('','Faltan datos','error');
				}
				else
				{
					tr = $('<tr></tr>')
							.append($('<td></td>')
								.append($('<input class="t_part" type="hidden" name="part[]" value="'+part+'">'))
								.append(part))
							.append($('<td></td>')
								.append($('<input class="t_quantity" type="hidden" name="quantity[]" value="'+quantity+'">'))
								.append(quantity))
							.append($('<td></td>')
								.append($('<input class="t_description" type="hidden" name="description[]" value="'+description+'">'))
								.append(description))
							.append($('<td class="text-nowrap"></td>')
								.append($('<button class="btn btn-green edit-art" type="button"><span class="icon-pencil"></span></button>'))
								.append($('<button class="btn btn-red delete-art" type="button"><span class="icon-x"></span></button>'))
								);

					$('#body_art').append(tr);

					$(this).parents('tr').find('.part-art').val('');
					$(this).parents('tr').find('.quantity-art').val('');
					$(this).parents('tr').find('.description-art').val('');

					swal('','Artículo agregado','success');
				}
			})
			.on('click','.delete-art',function()
			{
				id = $(this).parents('tr').find('.id').val();
				if (id != "x") 
				{
					deleteID = $('<input type="hidden" name="delete[]" value="'+id+'">');
					$('#spanDelete').append(deleteID);
				}
				$(this).parents('tr').remove();
				swal('','Concepto eliminado','success');
			})
			.on('click','.edit-art',function()
			{
				id = $(this).parents('tr').find('.id').val();

				tr = $(this).parents('tr')

				t_part        = tr.find('.t_part').val()
				t_quantity    = tr.find('.t_quantity').val()
				t_description = tr.find('.t_description').val()
				
				
				$('.part-art').val(t_part);
				$('.quantity-art').val(t_quantity);
				$('.description-art').val(t_description);

				
				if (id != "x") 
				{
					deleteID = $('<input type="hidden" name="delete[]" value="'+id+'">');
					$('#spanDelete').append(deleteID);
				}
				$(this).parents('tr').remove();
			})
			.on('click','#addDocRequisition',function()
			{
				newdoc	= $('<div class="docs-p"></div>')
							.append($('<div class="docs-p-l"></div>')
								.append($('<select class="custom-select nameDocumentRequisition" name="nameDocumentRequisition[]"></select><br><br>')
									.append($('<option value="0" disabled selected>Seleccione uno</option>'))	
									.append($('<option value="Cotización">Cotización</option>'))
									.append($('<option value="Ficha Técnica">Ficha Técnica</option>'))
									.append($('<option value="Control de Calidad">Control de Calidad</option>'))
									.append($('<option value="Contrato">Contrato</option>'))
									.append($('<option value="Factura">Factura</option>'))
									.append($('<option value="REQ. OC. FAC.">REQ. OC. FAC.</option>'))
									.append($('<option value="Otro">Otro</option>')))
								.append($('<div class="uploader-content"></div>')
									.append($('<input type="file" name="path" class="input-text pathActionerRequisition" accept=".pdf,.jpg,.png">'))	
								)
								.append($('<input type="hidden" name="realPathRequisition[]" class="path">')
									)
							)
							.append($('<div class="docs-p-r"></div>')
								.append($('<button class="delete-doc" type="button"><span class="icon-x delete-span"></span></button>')
								)
							);
				$('#documents-requisition').append(newdoc);
				$(function() 
				{
					$( ".datepicker" ).datepicker({ maxDate: 0, dateFormat: "dd-mm-yy" });
				});
			})
			.on('change','.input-text.pathActionerRequisition',function(e)
			{
				filename		= $(this);
				uploadedName 	= $(this).parent('.uploader-content').siblings('input[name="realPathRequisition[]"]');
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
						url			: '{{ url("/administration/requisition/upload") }}',
						data		: formData,
						contentType	: false,
						processData	: false,
						success		: function(r)
						{
							if(r.error=='DONE')
							{
								$(e.currentTarget).removeAttr('style').parent('.uploader-content').removeClass('loading').addClass('image_success');
								$(e.currentTarget).parent('.uploader-content').siblings('input[name="realPathRequisition[]"]').val(r.path);
								$(e.currentTarget).val('');
							}
							else
							{
								swal('',r.message, 'error');
								$(e.currentTarget).removeAttr('style').parent('.uploader-content').removeClass('loading');
								$(e.currentTarget).val('');
								$(e.currentTarget).parent('.uploader-content').siblings('input[name="realPathRequisition[]"]').val('');
							}
						},
						error: function()
						{
							swal('', 'Ocurrió un error durante la carga del archivo, intente de nuevo, por favor', 'error');
							$(e.currentTarget).removeAttr('style').parent('.uploader-content').removeClass('loading');
							$(e.currentTarget).val('');
							$(e.currentTarget).parent('.uploader-content').siblings('input[name="realPathRequisition[]"]').val('');
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
				uploadedName	= $(this).parent('.docs-p-r').siblings('.docs-p-l').children('input[name="realPathRequisition[]"]');
				formData		= new FormData();
				formData.append(uploadedName.attr('name'),uploadedName.val());
				$.ajax(
				{
					type		: 'post',
					url			: '{{ url("/administration/requisition/upload") }}',
					data		: formData,
					contentType	: false,
					processData	: false,
					success		: function(r)
					{
						swal.close();
						actioner.parent('.docs-p-r').parent('.docs-p').remove();
					},
					error		: function()
					{
						swal.close();
						actioner.parent('.docs-p-r').parent('.docs-p').remove();
					}
				});
				$(this).parents('div.docs-p').remove();
			})
			.on('change','[name="code_wbs"]',function()
			{
				code_wbs = $(this).val();
				$('[name="code_edt"]').empty();
				
				if(code_wbs[0] > 0)
				{
					hideEDT = false;
					switch (code_wbs[0]) {
						case "1":
						case "30":
						case "31":
						case "32":
						case "35":
						case "64":
						case "65":
						case "66":
						case "69":
							hideEDT = true;
							break;
						default:
							break;
					}
					(hideEDT && (code_wbs[0].length > 0) ) ? $("#codeEDTContainer").hide() : $("#codeEDTContainer").show();
				}

				$.ajax(
				{
					type 	: 'get',
					url 	: '{{ url("administration/requisition/get-edt") }}',
					data 	: {
						'code_wbs':code_wbs,
					},
					success : function(data)
					{
						$.each(data,function(i, d) {
							$('[name="code_edt"]').append('<option value='+d.id+'>'+d.code+' ('+d.description+')</option>');
						});
					},
					error : function(data)
					{
						swal('','Sucedió un error, por favor intente de nuevo.','error');
					}
				})
			})
		});
	</script>	
@endsection