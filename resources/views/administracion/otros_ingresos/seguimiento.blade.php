@extends('layouts.child_module')
  
@section('data')
<div id="container-cambio" class="div-search">
	{!! Form::open(['route' => 'other-income.edit', 'method' => 'GET', 'id'=>'formsearch']) !!}
		<center>
			<strong>BUSCAR SOLICITUDES</strong>
		</center>
		<div class="divisor">
			<div class="gray-divisor"></div>
			<div class="orange-divisor"></div>
			<div class="gray-divisor"></div>
		</div>
		<center>
			<div class="search-table-center">
				<div class="search-table-center-row">
					<div class="left">
						<br><label class="label-form">Folio:</label>
					</div>
					<div class="right">
						<p>
							<input type="text" name="folio" class="new-input-text" id="input-search" placeholder="Escribe aquí..." value="{{ isset($folio) ? $folio : '' }}">
						</p>
					</div>
				</div>
				<div class="search-table-center-row">
					<div class="left">
						<label class="label-form">Título:</label>
					</div>
					<div class="right">
						<p>
							<input type="text" name="title_request" class="new-input-text" id="input-search" placeholder="Escribe aquí..." value="{{ isset($title_request) ? $title_request : '' }}">
						</p>
					</div>
				</div>
				<div class="search-table-center-row">
					<div class="left">
						<label class="label-form">Rango de fechas:</label>
					</div>
					<div class="right-date">
						<p>
							<input type="text" name="mindate" step="1" class="input-text-date datepicker" placeholder="Desde" value="{{ isset($mindate) ? $mindate : '' }}"> - <input type="text" name="maxdate" step="1" class="input-text-date datepicker" placeholder="Hasta" value="{{ isset($maxdate) ? $maxdate : '' }}">
						</p>
					</div>
				</div>
				<div class="search-table-center-row">
					<p>
						<select title="Solicitante" name="request_id[]"  multiple="multiple">
							@foreach(App\User::whereIn('status',['NO-MAIL','ACTIVE','RE-ENTRY','RE-ENTRY-NO-MAIL'])->where('sys_user',1)->orderName()->get() as $user)
								<option value="{{ $user->id }}" @if(isset($request_id) && in_array($user->id,$request_id)) selected @endif>{{ $user->fullName() }}</option>
							@endforeach
						</select>
					</p>
				</div>
				<div class="search-table-center-row">
					<p>
						<select title="Empresa" name="enterprise_id[]"  multiple="multiple">
							@foreach(App\Enterprise::whereIn('id',Auth::user()->inChargeEnt($option_id)->pluck('enterprise_id'))->orderBy('name','asc')->get() as $enterprise)
								<option value="{{ $enterprise->id }}" @if(isset($enterprise_id) && in_array($enterprise->id,$enterprise_id)) selected @endif>{{ strlen($enterprise->name) >= 35 ? substr(strip_tags($enterprise->name),0,35).'...' : $enterprise->name }}</option>
							@endforeach
						</select>
					</p>
				</div>
				<div class="search-table-center-row">
					<p>
						<select title="Proyecto" name="project_id[]"  multiple="multiple">
							@foreach(App\Project::whereIn('status',[1,2])->orderBy('proyectName','asc')->get() as $project)
								<option value="{{ $project->idproyect }}" @if(isset($project_id) && in_array($project->idproyect,$project_id)) selected @endif>{{ strlen($project->proyectName) >= 35 ? substr(strip_tags($project->proyectName),0,35).'...' : $project->proyectName }}</option>
							@endforeach
						</select>
					</p>
				</div>
				<div class="search-table-center-row">
					<p>
						<select title="Estado de Solicitud" name="status[]" multiple="multiple">
							@foreach (App\StatusRequest::whereIn('idrequestStatus',[2,3,4,5,6,7,10,11,12])->orderBy('description','asc')->get() as $s)
								<option value="{{ $s->idrequestStatus }}" @if (isset($status) && in_array($s->idrequestStatus, $status)) selected @endif>{{ $s->description }}</option>
							@endforeach
						</select>
					</p>
				</div>
			</div>
		</center>
		<center>
			<button class="btn 	btn-search" type="submit"><span class="icon-search"></span> Buscar</button>
		</center>
<br><br>
</div>
<br>
@if(count($requests) > 0)
	<div style='float: right'><label class='label-form'>Exportar a Excel <label><button class='btn btn-green export' type='submit'  formaction="{{ route('other-income.excel-follow') }}"><span class='icon-file-excel'></span></button></div>
	{!! Form::close() !!}
	<div class="table-responsive table-striped">
		<table class="table">
			<thead class="thead-dark">
				<th width="5%">Folio</th>
				<th width="15%">Tipo</th>
				<th width="15%">Título</th>
				<th width="15%">Empresa</th>
				<th>Proyecto</th>
				<th width="15%">Solicitante</th>
				<th width="10%">Estado</th>
				<th width="10%">Fecha de elaboración</th>
				<th width="15%">Acción</th>
			</thead>
			@foreach($requests as $request)
				@php	
					$time	= new DateTime($request->fDate);
					$date	= $time->format('d-m-Y H:i');
				@endphp 
				<tr>
					<td>{{ $request->folio }}</td>
					<td>{{ $request->otherIncome()->exists() ? $request->otherIncome->typeIncome() : 'Sin tipo' }}</td>
					<td>{{ $request->otherIncome()->exists() ? $request->otherIncome->title : 'Sin título' }}</td>
					<td>{{ $request->enterprise()->exists() ? $request->enterprise->name : 'Sin empresa' }}</td>
					<td>{{ $request->requestProject()->exists() ? $request->requestProject->proyectName : 'Sin proyecto' }}</td>
					<td>{{ $request->requestUser()->exists() ? $request->requestUser->fullName() : 'Sin solicitante' }}</td>
					<td>{{ $request->statusrequest->description }}</td>
					<td>{{ $date  }}</td>
					<td>
						@if($request->status == 5 || $request->status == 6 || $request->status == 7  || $request->status == 10 || $request->status == 11 || $request->status == 13) 
							<a alt="Nueva Solicitud" title="Nueva Solicitud" href="{{ route('other-income.new-income',$request->folio) }}" class='btn follow-btn'><span class='icon-plus'></span></a> 
							<a alt="Ver Solicitud" title="Ver Solicitud" href="{{ route('other-income.edit-income',$request->folio) }}" class='btn follow-btn'><span class='icon-search'></span></a>
						@elseif($request->status == 3 || $request->status == 4 || $request->status == 5 || $request->status == 10 || $request->status == 11  || $request->status == 12) 
							<a alt="Ver Solicitud" title="Ver Solicitud" href="{{ route('other-income.edit-income',$request->folio) }}" class='btn follow-btn'><span class='icon-search'></span></a> 
						@else 
							<a alt="Editar Solicitud" title="Editar Solicitud" href="{{ route('other-income.edit-income',$request->folio) }}" class='btn follow-btn'><span class='icon-pencil'></span></a>
						@endif
					</td>
				</tr>
			@endforeach
		</table>
	</div>
	<center>
		{{ $requests->appends([
				'request_id'	=> $request_id,
				'folio'			=> $folio,
				'status'		=> $status,
				'mindate'		=> $mindate,
				'maxdate'		=> $maxdate,
				'enterprise_id'	=> $enterprise_id,
				'project_id'	=> $project_id,
				'title_request' => $title_request
			])->render() }}
	</center>
	<br><br><br>
@else
	<div id="not-found" style="display:block;">No hay solicitudes</div>
@endif
@endsection

@section('scripts')

<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
<script src="{{ asset('js/jquery-ui.js') }}"></script>
<script src="{{ asset('js/datepicker.js') }}"></script>
<script src="{{ asset('js/jquery.numeric.js') }}"></script>
<script type="text/javascript"> 
	$(document).ready(function()
	{
		$(function() 
		{
			$( ".datepicker" ).datepicker({ maxDate: 0, dateFormat: "yy-mm-dd" });
		});
		$('[name="request_id[]"]').select2(
		{
			placeholder : 'Seleccione un solicitante',
			language 	: 'es',
			width 		: '100%',

		});
		$('[name="enterprise_id[]"]').select2(
		{
			placeholder : 'Seleccione la empresa',
			language 	: 'es',
			width 		: '100%',

		});
		$('[name="project_id[]"]').select2(
		{
			placeholder : 'Seleccione el proyecto',
			language 	: 'es',
			width 		: '100%',

		});
		$('[name="status[]"]').select2(
		{
			placeholder : 'Seleccione un estado',
			language 	: 'es',
			width 		: '100%',
		});

		
		
	});
	@if(isset($alert))
		{!! $alert !!}
	@endif
</script>
@endsection