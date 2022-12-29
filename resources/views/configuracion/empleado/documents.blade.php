@extends('layouts.child_module')

@section('css')
	<style type="text/css">
		.info-container
		{
			display			: flex;
			flex-wrap		: wrap;
			justify-content	: center;
		}
		p
		{
			position	: relative;
		}
		table tr td .help-block.form-error
		{
			left		: 0;
			position	: absolute;
			white-space	: nowrap;
		}
		:not(.multichoice)+span.select2 ul.select2-selection__rendered li:not(:first-child)
		{
			height		: 0;
			overflow	: hidden;
		}
		.custom-select + span.help-block.form-error
		{
			-moz-appearance		: none;
			-webkit-appearance	: none;
			appearance			: none;
			background			: #cc0404 !important;
			border				: none;
			border-radius		: 0;
			color				: #ffffff !important;
			display				: inline-block !important;
			font-size			: .8em;
			font-weight			: 300;
			height				: auto !important;
			line-height			: 140% !important;
			margin				: -2px 0 0 !important;
			padding				: 2px 10px !important;
			vertical-align		: middle;
			width				: auto !important;
		}
		.select_father
		{
			display: none;
		}
	</style>
@endsection

@section('data')
	<form id="employee_form" action="{{route('employee.update.docs',$employee->id)}}" method="POST">
		@method('PUT')
	@php
		$employee_config = false;
	@endphp
		@csrf
		<p style="text-align: center;margin-bottom: 2rem;">
			<strong style="font-size:2rem;">{{ $employee->name }} {{ $employee->last_name }} {{ $employee->scnd_last_name }}</strong>
		</p>
		<center>
			<strong>LISTA DE DOCUMENTOS</strong>
		</center>
		<div class="divisor">
			<div class="gray-divisor"></div>
			<div class="orange-divisor"></div>
			<div class="gray-divisor"></div>
		</div>
		<div class="table-responsive">
			<table class="table">
				<thead class="thead-dark">
					<th>Nombre de Documento</th>
					<th>Archivo</th>
				</thead>
				<tbody id="documents_employee">
					<tr>
						<td>Acta de Nacimiento</td>
						<td class="doc_birth_certificate">
							@if(isset($employee) && $employee->doc_birth_certificate != "")
								@if(\Storage::disk('public')->exists('/docs/requisition/'.$employee->doc_birth_certificate))
									<a target="_blank" href="{{ url('docs/requisition/'.$employee->doc_birth_certificate) }}">{{ $employee->doc_birth_certificate }}</a>
								@elseif(\Storage::disk('public')->exists('/docs/staff/'.$employee->doc_birth_certificate))
									<a target="_blank" href="{{ url('docs/staff/'.$employee->doc_birth_certificate) }}">{{ $employee->doc_birth_certificate }}</a>
								@endif
							@elseif(isset($employee_edit) && $employee_edit->doc_birth_certificate != "")
								@if(\Storage::disk('public')->exists('/docs/requisition/'.$employee_edit->doc_birth_certificate))
									<a target="_blank" href="{{ url('docs/requisition/'.$employee_edit->doc_birth_certificate) }}">{{ $employee_edit->doc_birth_certificate }}</a>
								@elseif(\Storage::disk('public')->exists('/docs/staff/'.$employee_edit->doc_birth_certificate))
									<a target="_blank" href="{{ url('docs/staff/'.$employee_edit->doc_birth_certificate) }}">{{ $employee_edit->doc_birth_certificate }}</a>
								@endif
							@else
								Sin documento
							@endif
						</td>
					</tr>
					<tr>
						<td>Comprobante de Domicilio</td>
						<td class="doc_proof_of_address">
							@if(isset($employee) && $employee->doc_proof_of_address != "")
								@if(\Storage::disk('public')->exists('/docs/requisition/'.$employee->doc_proof_of_address))
									<a target="_blank" href="{{ url('docs/requisition/'.$employee->doc_proof_of_address) }}">{{ $employee->doc_proof_of_address }}</a>
								@elseif(\Storage::disk('public')->exists('/docs/staff/'.$employee->doc_proof_of_address))
									<a target="_blank" href="{{ url('docs/staff/'.$employee->doc_proof_of_address) }}">{{ $employee->doc_proof_of_address }}</a>
								@endif
							@elseif(isset($employee_edit) && $employee_edit->doc_proof_of_address != "")
								@if(\Storage::disk('public')->exists('/docs/requisition/'.$employee_edit->doc_proof_of_address))
									<a target="_blank" href="{{ url('docs/requisition/'.$employee_edit->doc_proof_of_address) }}">{{ $employee_edit->doc_proof_of_address }}</a>
								@elseif(\Storage::disk('public')->exists('/docs/staff/'.$employee_edit->doc_proof_of_address))
									<a target="_blank" href="{{ url('docs/staff/'.$employee_edit->doc_proof_of_address) }}">{{ $employee_edit->doc_proof_of_address }}</a>
								@endif
							@else
								Sin documento
							@endif
						</td>
					</tr>
					<tr>
						<td>Número  de Seguridad Social</td>
						<td class="doc_nss">
							
							@if(isset($employee) && $employee->doc_nss != "")
								@if(\Storage::disk('public')->exists('/docs/requisition/'.$employee->doc_nss))
									<a target="_blank" href="{{ url('docs/requisition/'.$employee->doc_nss) }}">{{ $employee->doc_nss }}</a>
								@elseif(\Storage::disk('public')->exists('/docs/staff/'.$employee->doc_nss))
									<a target="_blank" href="{{ url('docs/staff/'.$employee->doc_nss) }}">{{ $employee->doc_nss }}</a>
								@endif
							@elseif(isset($employee_edit) && $employee_edit->doc_nss != "")
								@if(\Storage::disk('public')->exists('/docs/requisition/'.$employee_edit->doc_nss))
									<a target="_blank" href="{{ url('docs/requisition/'.$employee_edit->doc_nss) }}">{{ $employee_edit->doc_nss }}</a>
								@elseif(\Storage::disk('public')->exists('/docs/staff/'.$employee_edit->doc_nss))
									<a target="_blank" href="{{ url('docs/staff/'.$employee_edit->doc_nss) }}">{{ $employee_edit->doc_nss }}</a>
								@endif
							@else
								Sin documento
							@endif
						</td>
					</tr>
					<tr>
						<td>INE</td>
						<td class="doc_ine">
							@if(isset($employee) && $employee->doc_ine != "")
								@if(\Storage::disk('public')->exists('/docs/requisition/'.$employee->doc_ine))
									<a target="_blank" href="{{ url('docs/requisition/'.$employee->doc_ine) }}">{{ $employee->doc_ine }}</a>
								@elseif(\Storage::disk('public')->exists('/docs/staff/'.$employee->doc_ine))
									<a target="_blank" href="{{ url('docs/staff/'.$employee->doc_ine) }}">{{ $employee->doc_ine }}</a>
								@endif
							@elseif(isset($employee_edit) && $employee_edit->doc_ine != "")
								@if(\Storage::disk('public')->exists('/docs/requisition/'.$employee_edit->doc_ine))
									<a target="_blank" href="{{ url('docs/requisition/'.$employee_edit->doc_ine) }}">{{ $employee_edit->doc_ine }}</a>
								@elseif(\Storage::disk('public')->exists('/docs/staff/'.$employee_edit->doc_ine))
									<a target="_blank" href="{{ url('docs/staff/'.$employee_edit->doc_ine) }}">{{ $employee_edit->doc_ine }}</a>
								@endif
							@else
								Sin documento
							@endif
						</td>
					</tr>
					<tr>
						<td>CURP</td>
						<td class="doc_curp">
							@if(isset($employee) && $employee->doc_curp != "")
								@if(\Storage::disk('public')->exists('/docs/requisition/'.$employee->doc_curp))
									<a target="_blank" href="{{ url('docs/requisition/'.$employee->doc_curp) }}">{{ $employee->doc_curp }}</a>
								@elseif(\Storage::disk('public')->exists('/docs/staff/'.$employee->doc_curp))
									<a target="_blank" href="{{ url('docs/staff/'.$employee->doc_curp) }}">{{ $employee->doc_curp }}</a>
								@endif
							@elseif(isset($employee_edit) && $employee_edit->doc_curp != "")
								@if(\Storage::disk('public')->exists('/docs/requisition/'.$employee_edit->doc_curp))
									<a target="_blank" href="{{ url('docs/requisition/'.$employee_edit->doc_curp) }}">{{ $employee_edit->doc_curp }}</a>
								@elseif(\Storage::disk('public')->exists('/docs/staff/'.$employee_edit->doc_curp))
									<a target="_blank" href="{{ url('docs/staff/'.$employee_edit->doc_curp) }}">{{ $employee_edit->doc_curp }}</a>
								@endif
							@else
								Sin documento
							@endif
						</td>
					</tr>
					<tr>
						<td>RFC</td>
						<td class="doc_rfc">
							@if(isset($employee) && $employee->doc_rfc != "")
								@if(\Storage::disk('public')->exists('/docs/requisition/'.$employee->doc_rfc))
									<a target="_blank" href="{{ url('docs/requisition/'.$employee->doc_rfc) }}">{{ $employee->doc_rfc }}</a>
								@elseif(\Storage::disk('public')->exists('/docs/staff/'.$employee->doc_rfc))
									<a target="_blank" href="{{ url('docs/staff/'.$employee->doc_rfc) }}">{{ $employee->doc_rfc }}</a>
								@endif
							@elseif(isset($employee_edit) && $employee_edit->doc_rfc != "")
								@if(\Storage::disk('public')->exists('/docs/requisition/'.$employee_edit->doc_rfc))
									<a target="_blank" href="{{ url('docs/requisition/'.$employee_edit->doc_rfc) }}">{{ $employee_edit->doc_rfc }}</a>
								@elseif(\Storage::disk('public')->exists('/docs/staff/'.$employee_edit->doc_rfc))
									<a target="_blank" href="{{ url('docs/staff/'.$employee_edit->doc_rfc) }}">{{ $employee_edit->doc_rfc }}</a>
								@endif
							@else
								Sin documento
							@endif
						</td>
					</tr>
					<tr>
						<td>Curriculum Vitae/Solicitud de Empleo</td>
						<td class="doc_cv">
							@if(isset($employee) && $employee->doc_cv != "")
								@if(\Storage::disk('public')->exists('/docs/requisition/'.$employee->doc_cv))
									<a target="_blank" href="{{ url('docs/requisition/'.$employee->doc_cv) }}">{{ $employee->doc_cv }}</a>
								@elseif(\Storage::disk('public')->exists('/docs/staff/'.$employee->doc_cv))
									<a target="_blank" href="{{ url('docs/staff/'.$employee->doc_cv) }}">{{ $employee->doc_cv }}</a>
								@endif
							@elseif(isset($employee_edit) && $employee_edit->doc_cv != "")
								@if(\Storage::disk('public')->exists('/docs/requisition/'.$employee_edit->doc_cv))
									<a target="_blank" href="{{ url('docs/requisition/'.$employee_edit->doc_cv) }}">{{ $employee_edit->doc_cv }}</a>
								@elseif(\Storage::disk('public')->exists('/docs/staff/'.$employee_edit->doc_cv))
									<a target="_blank" href="{{ url('docs/staff/'.$employee_edit->doc_cv) }}">{{ $employee_edit->doc_cv }}</a>
								@endif
							@else
								Sin documento
							@endif
						</td>
					</tr>
					<tr>
						<td>Comprobante de Estudios</td>
						<td class="doc_proof_of_studies">
							@if(isset($employee) && $employee->doc_proof_of_studies != "")
								@if(\Storage::disk('public')->exists('/docs/requisition/'.$employee->doc_proof_of_studies))
									<a target="_blank" href="{{ url('docs/requisition/'.$employee->doc_proof_of_studies) }}">{{ $employee->doc_proof_of_studies }}</a>
								@elseif(\Storage::disk('public')->exists('/docs/staff/'.$employee->doc_proof_of_studies))
									<a target="_blank" href="{{ url('docs/staff/'.$employee->doc_proof_of_studies) }}">{{ $employee->doc_proof_of_studies }}</a>
								@endif
							@elseif(isset($employee_edit) && $employee_edit->doc_proof_of_studies != "")
								@if(\Storage::disk('public')->exists('/docs/requisition/'.$employee_edit->doc_proof_of_studies))
									<a target="_blank" href="{{ url('docs/requisition/'.$employee_edit->doc_proof_of_studies) }}">{{ $employee_edit->doc_proof_of_studies }}</a>
								@elseif(\Storage::disk('public')->exists('/docs/staff/'.$employee_edit->doc_proof_of_studies))
									<a target="_blank" href="{{ url('docs/staff/'.$employee_edit->doc_proof_of_studies) }}">{{ $employee_edit->doc_proof_of_studies }}</a>
								@endif
							@else
								Sin documento
							@endif
						</td>
					</tr>
					<tr>
						<td>Cédula Profesional</td>
						<td class="doc_professional_license">
							@if(isset($employee) && $employee->doc_professional_license != "")
								@if(\Storage::disk('public')->exists('/docs/requisition/'.$employee->doc_professional_license))
									<a target="_blank" href="{{ url('docs/requisition/'.$employee->doc_professional_license) }}">{{ $employee->doc_professional_license }}</a>
								@elseif(\Storage::disk('public')->exists('/docs/staff/'.$employee->doc_professional_license))
									<a target="_blank" href="{{ url('docs/staff/'.$employee->doc_professional_license) }}">{{ $employee->doc_professional_license }}</a>
								@endif
							@elseif(isset($employee_edit) && $employee_edit->doc_professional_license != "")
								@if(\Storage::disk('public')->exists('/docs/requisition/'.$employee_edit->doc_professional_license))
									<a target="_blank" href="{{ url('docs/requisition/'.$employee_edit->doc_professional_license) }}">{{ $employee_edit->doc_professional_license }}</a>
								@elseif(\Storage::disk('public')->exists('/docs/staff/'.$employee_edit->doc_professional_license))
									<a target="_blank" href="{{ url('docs/staff/'.$employee_edit->doc_professional_license) }}">{{ $employee_edit->doc_professional_license }}</a>
								@endif
							@else
								Sin documento
							@endif
						</td>
					</tr>
					<tr>
						<td>Requisición Firmada</td>
						<td class="doc_requisition">
							@if(isset($employee) && $employee->doc_requisition != "")
								@if(\Storage::disk('public')->exists('/docs/requisition/'.$employee->doc_requisition))
									<a target="_blank" href="{{ url('docs/requisition/'.$employee->doc_requisition) }}">{{ $employee->doc_requisition }}</a>
								@elseif(\Storage::disk('public')->exists('/docs/staff/'.$employee->doc_requisition))
									<a target="_blank" href="{{ url('docs/staff/'.$employee->doc_requisition) }}">{{ $employee->doc_requisition }}</a>
								@endif
							@elseif(isset($employee_edit) && $employee_edit->doc_requisition != "")
								@if(\Storage::disk('public')->exists('/docs/requisition/'.$employee_edit->doc_requisition))
									<a target="_blank" href="{{ url('docs/requisition/'.$employee_edit->doc_requisition) }}">{{ $employee_edit->doc_requisition }}</a>
								@elseif(\Storage::disk('public')->exists('/docs/staff/'.$employee_edit->doc_requisition))
									<a target="_blank" href="{{ url('docs/staff/'.$employee_edit->doc_requisition) }}">{{ $employee_edit->doc_requisition }}</a>
								@endif
							@else
								Sin documento
							@endif
						</td>
					</tr>
					@if(isset($employee))
						@foreach($employee->documents as $doc)
							<tr class="tr-remove">
								<td>{{ $doc->name }}</td>
								<td>
									@if($doc->path != "")
										<a target="_blank" href="{{ url('docs/requisition/'.$doc->path) }}">{{ $doc->path }}</a>
									@else
										Sin documento
									@endif
								</td>
							</tr>
						@endforeach	
					@elseif(isset($employee_edit) && $employee_edit->documents != null)
						@foreach($employee_edit->documents as $doc)
							<tr class="tr-remove">
								<td>{{ $doc->name }}</td>
								<td>
									@if($doc->path != "")
										<a target="_blank" href="{{ url('docs/requisition/'.$doc->path) }}">{{ $doc->path }}</a>
									@else
										Sin documento
									@endif
								</td>
							</tr>
						@endforeach	
					@elseif(isset($employee_edit) && $employee_edit->staffDocuments != null)
						@foreach($employee_edit->staffDocuments as $doc)
							<tr class="tr-remove">
								<td>{{ $doc->name }}</td>
								<td>
									@if($doc->path != "")
										<a target="_blank" href="{{ url('docs/staff/'.$doc->path) }}">{{ $doc->path }}</a>
									@else
										Sin documento
									@endif
								</td>
							</tr>
						@endforeach	
					@endif
				</tbody>
			</table>
		</div>
		
		<center>
			<strong>DOCUMENTOS</strong>
		</center>
		<div class="divisor">
			<div class="gray-divisor"></div>
			<div class="orange-divisor"></div>
			<div class="gray-divisor"></div>
		</div>
		<p><br></p>
		<div class="form-row  ml-3 mr-3">
			<div class="form-group col-md-6 mb-4">
				<label><b>Acta de Nacimiento</b></label><br>
				
				<div>
					<div class="docs-p">
						<div class="docs-p-1">
							<div class="uploader-content @if(isset($employee) && $employee->doc_birth_certificate != "") image_pdf @elseif(isset($employee_edit) && $employee_edit->doc_birth_certificate != "") image_pdf @endif">
								<input type="file" name="path" class="input-text pathActioner" accept=".pdf">
							</div>
							<input type="hidden" name="doc_birth_certificate" class="path" @if(isset($employee) && $employee->doc_birth_certificate != "") value="{{ $employee->doc_birth_certificate }}" @elseif(isset($employee_edit) && $employee_edit->doc_birth_certificate != "") value="{{ $employee_edit->doc_birth_certificate }}" @endif>
						</div>
					</div>
				</div>
			</div>
			<div class="form-group col-md-6 mb-4">
				<label><b>Comprobante de Domicilio</b></label><br>
				<div>
					<div class="docs-p">
						<div class="docs-p-1">
							<div class="uploader-content @if(isset($employee) && $employee->doc_proof_of_address != "") image_pdf @elseif(isset($employee_edit) && $employee_edit->doc_proof_of_address != "") image_pdf @endif">
								<input type="file" name="path" class="input-text pathActioner" accept=".pdf">
							</div>
							<input type="hidden" name="doc_proof_of_address" class="path" @if(isset($employee) && $employee->doc_proof_of_address != "") value="{{ $employee->doc_proof_of_address }}" @elseif(isset($employee_edit) && $employee_edit->doc_proof_of_address != "") value="{{ $employee_edit->doc_proof_of_address }}" @endif>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="form-row  ml-3 mr-3">
			<div class="form-group col-md-6 mb-4">
				<label><b>Número de Seguridad Social</b></label><br>
				
				<div>
					<div class="docs-p">
						<div class="docs-p-1">
							<div class="uploader-content @if(isset($employee) && $employee->doc_nss != "") image_pdf @elseif(isset($employee_edit) && $employee_edit->doc_nss != "") image_pdf @endif">
								<input type="file" name="path" class="input-text pathActioner" accept=".pdf">
							</div>
							<input type="hidden" name="doc_nss" class="path" @if(isset($employee) && $employee->doc_nss != "") value="{{ $employee->doc_nss }}" @elseif(isset($employee_edit) && $employee_edit->doc_nss != "") value="{{ $employee_edit->doc_nss }}" @endif>
						</div>
					</div>
				</div>
			</div>
			<div class="form-group col-md-6 mb-4">
				<label><b>INE</b></label><br>
				<div>
					<div class="docs-p">
						<div class="docs-p-1">
							<div class="uploader-content @if(isset($employee) && $employee->doc_ine != "") image_pdf @elseif(isset($employee_edit) && $employee_edit->doc_ine != "") image_pdf @endif">
								<input type="file" name="path" class="input-text pathActioner" accept=".pdf">
							</div>
							<input type="hidden" name="doc_ine" class="path" @if(isset($employee) && $employee->doc_ine != "") value="{{ $employee->doc_ine }}" @elseif(isset($employee_edit) && $employee_edit->doc_ine != "") value="{{ $employee_edit->doc_ine }}" @endif>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="form-row  ml-3 mr-3">
			<div class="form-group col-md-6 mb-4">
				<label><b>CURP</b></label><br>
				
				<div>
					<div class="docs-p">
						<div class="docs-p-1">
							<div class="uploader-content @if(isset($employee) && $employee->doc_curp != "") image_pdf @elseif(isset($employee_edit) && $employee_edit->doc_curp != "") image_pdf @endif">
								<input type="file" name="path" class="input-text pathActioner" accept=".pdf">
							</div>
							<input type="hidden" name="doc_curp" class="path" @if(isset($employee) && $employee->doc_curp != "") value="{{ $employee->doc_curp }}" @elseif(isset($employee_edit) && $employee_edit->doc_curp != "") value="{{ $employee_edit->doc_curp }}" @endif>
						</div>
					</div>
				</div>
			</div>
			<div class="form-group col-md-6 mb-4">
				<label><b>RFC</b></label><br>
				<div>
					<div class="docs-p">
						<div class="docs-p-1">
							<div class="uploader-content @if(isset($employee) && $employee->doc_rfc != "") image_pdf @elseif(isset($employee_edit) && $employee_edit->doc_rfc != "") image_pdf @endif">
								<input type="file" name="path" class="input-text pathActioner" accept=".pdf">
							</div>
							<input type="hidden" name="doc_rfc" class="path" @if(isset($employee) && $employee->doc_rfc != "") value="{{ $employee->doc_rfc }}" @elseif(isset($employee_edit) && $employee_edit->doc_rfc != "") value="{{ $employee_edit->doc_rfc }}" @endif>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="form-row  ml-3 mr-3">
			<div class="form-group col-md-6 mb-4">
				<label><b>Curriculum Vitae/Solicitud de Empleo</b></label><br>
				
				<div>
					<div class="docs-p">
						<div class="docs-p-1">
							<div class="uploader-content @if(isset($employee) && $employee->doc_cv != "") image_pdf @elseif(isset($employee_edit) && $employee_edit->doc_cv != "") image_pdf @endif">
								<input type="file" name="path" class="input-text pathActioner" accept=".pdf">
							</div>
							<input type="hidden" name="doc_cv" class="path" @if(isset($employee) && $employee->doc_cv != "") value="{{ $employee->doc_cv }}" @elseif(isset($employee_edit) && $employee_edit->doc_cv != "") value="{{ $employee_edit->doc_cv }}" @endif>
						</div>
					</div>
				</div>
			</div>
			<div class="form-group col-md-6 mb-4">
				<label><b>Comprobante de Estudios</b></label><br>
				<div>
					<div class="docs-p">
						<div class="docs-p-1">
							<div class="uploader-content @if(isset($employee) && $employee->doc_proof_of_studies != "") image_pdf @elseif(isset($employee_edit) && $employee_edit->doc_proof_of_studies != "") image_pdf @endif">
								<input type="file" name="path" class="input-text pathActioner" accept=".pdf">
							</div>
							<input type="hidden" name="doc_proof_of_studies" class="path" @if(isset($employee) && $employee->doc_proof_of_studies != "") value="{{ $employee->doc_proof_of_studies }}" @elseif(isset($employee_edit) && $employee_edit->doc_proof_of_studies != "") value="{{ $employee_edit->doc_proof_of_studies }}" @endif>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="form-row  ml-3 mr-3">
			<div class="form-group col-md-6 mb-4">
				<label><b>Cédula Profesional</b></label><br>
				<div>
					<div class="docs-p">
						<div class="docs-p-1">
							<div class="uploader-content @if(isset($employee) && $employee->doc_professional_license != "") image_pdf @elseif(isset($employee_edit) && $employee_edit->doc_professional_license != "") image_pdf @endif">
								<input type="file" name="path" class="input-text pathActioner" accept=".pdf">
							</div>
							<input type="hidden" name="doc_professional_license" class="path" @if(isset($employee) && $employee->doc_professional_license != "") value="{{ $employee->doc_professional_license }}" @elseif(isset($employee_edit) && $employee_edit->doc_professional_license != "") value="{{ $employee_edit->doc_professional_license }}" @endif>
						</div>
					</div>
				</div>
			</div>
			<div class="form-group col-md-6 mb-4">
				<label><b>Requisición Firmada</b></label><br>
				<div>
					<div class="docs-p">
						<div class="docs-p-1">
							<div class="uploader-content @if(isset($employee) && $employee->doc_requisition != "") image_pdf @elseif(isset($employee_edit) && $employee_edit->doc_requisition != "") image_pdf @endif">
								<input type="file" name="path" class="input-text pathActioner" accept=".pdf">
							</div>
							<input type="hidden" name="doc_requisition" class="path" @if(isset($employee) && $employee->doc_requisition != "") value="{{ $employee->doc_requisition }}" @elseif(isset($employee_edit) && $employee_edit->doc_requisition != "") value="{{ $employee_edit->doc_requisition }}" @endif>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="form-row  ml-3 mr-3" id="other_documents">
		</div>
		<center>
			<button class="btn btn-orange" type="button" id="add_document"><span class="icon-plus"></span><span>Agregar documento</span></button>
		</center>
		<center>
			<button type="submit" id="create_employee" class="btn updateBtn btn-red btn_disable">@if(isset($employee)) ACTUALIZAR EMPLEADO @else CREAR EMPLEADO @endif</button>
		</center>
	</form>
@endsection

@section('scripts')
	<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
	<script src="{{ asset('js/jquery-ui.js') }}"></script>
	<script src="{{ asset('js/datepicker.js') }}"></script>
	<script src="{{ asset('js/jquery.numeric.js') }}"></script>
	<script type="text/javascript" src="{{asset('js/jquery.mask.js')}}"></script>
	<script type="text/javascript">
		$('.updateBtn').on('click', function()
		{
			if($('input[name="rfc"]').hasClass('error'))
			{
				swal('', 'Por favor ingrese un RFC válido.', 'error');
				return false;
			}
			else
			{
				return true;
			}
		});
		$.validate(
		{
			form	: '#employee_form',
			modules	: 'security',
			onError   : function($form)
			{
				swal('', '{{ Lang::get("messages.form_error") }}', 'error');
			},
			onSuccess	: function($form)
			{
				if($('[name="rfc"]').val() != '' && $('#tax_regime').val() == '')
				{
					$('#tax_regime').parent().append('<span class="help-block form-error">Este campo es obligatorio</span>');
					swal('', 'Por favor ingrese un régimen fiscal.', 'error');
					return false;
				}
				else
				{
					return true;
				}
			}		
		});
		$(document).ready(function()
		{
			$(document).on('change','.input-text.pathActioner',function(e)
			{
				filename     = $(this);
				uploadedName = $(this).parent('.uploader-content').siblings('.path');
				extention    = /\.pdf/i;
				if(filename.val().search(extention) == -1)
				{
					swal('', 'El tipo de archivo no es soportado, por favor seleccione una imagen jpg, png o un archivo pdf', 'warning');
					$(this).val('');
				}
				else if(this.files[0].size>315621376)
				{
					swal('', 'El tamaño máximo de su archivo no debe ser mayor a 300Mb', 'warning');
				}
				else
				{
					$(this).css('visibility','hidden').parent('.uploader-content').addClass('loading').removeClass(function (index, css)
					{
						return (css.match (/\bimage_\S+/g) || []).join(' '); // removes anything that starts with "image_"
					});
					formData = new FormData();
					formData.append(filename.attr('name'), filename.prop("files")[0]);
					formData.append(uploadedName.attr('name'),uploadedName.val());

					$('.disable-button').prop('disabled', true);

					$('.btn_disable').attr('disabled', true);	
					$.ajax(
					{
						type       : 'post',
						url        : '{{ url("/administration/requisition/upload") }}',
						data       : formData,
						contentType: false,
						processData: false,
						success    : function(r)
						{
							if(r.error == 'DONE')
							{
								$(e.currentTarget).removeAttr('style').parent('.uploader-content').removeClass('loading').addClass('image_'+r.extention);
								$(e.currentTarget).parent('.uploader-content').siblings('.path').val(r.path);
								$(e.currentTarget).val('');
							}
							else
							{
								swal('',r.message, 'error');
								$(e.currentTarget).removeAttr('style').parent('.uploader-content').removeClass('loading');
								$(e.currentTarget).val('');
								$(e.currentTarget).parent('.uploader-content').siblings('.path').val('');
							}
							$('.btn_disable').attr('disabled', false);
						},
						error: function()
						{
							swal('', 'Ocurrió un error durante la carga del archivo, intente de nuevo, por favor', 'error');
							$(e.currentTarget).removeAttr('style').parent('.uploader-content').removeClass('loading');
							$(e.currentTarget).val('');
							$(e.currentTarget).parent('.uploader-content').siblings('.path').val('');
						}
					})
				}
			})
			.on('click','#add_document',function()
			{
				doc = $('<div class="form-group col-md-6 mb-4 form_other_doc"></div>')
						.append($('<div class="docs-p"></div>')
							.append($('<div class="docs-p-1"></div>')
								.append($('<select class="name_other_document" name="name_other_document[]" multiple="multiple" data-validation="required"></select>')
										.append($('<option value="Aviso de retención por crédito Infonavit">Aviso de retención por crédito Infonavit</value>'))
										.append($('<option value="Estado de cuenta">Estado de cuenta</value>'))
										.append($('<option value="Cursos de capacitación">Cursos de capacitación</value>'))
										.append($('<option value="Carta de recomendación">Carta de recomendación</value>'))
										.append($('<option value="Certificado médico">Certificado médico</value>'))
										.append($('<option value="Identificación de beneficiario">Identificación de beneficiario</value>'))
										.append($('<option value="Identificación">Identificación</value>'))
										.append($('<option value="Hoja de expediente">Hoja de expediente</value>'))
										)
								.append($('<br><br>'))
								.append($('<div class="uploader-content"></div>')
									.append($('<input type="file" name="path" class="input-text pathActioner" accept=".pdf">')))
								.append($('<input type="hidden" name="path_other_document[]" class="path path_other_document">')))
							.append($('<div class="docs-p-r"></div>')
									.append($('<button class="btn btn-red delete_other_doc" type="button"><span class="icon-x"></span></button>'))));

				$('#other_documents').append(doc);

				$('[name="name_other_document[]"]').select2(
				{
					language              : "es",
					maximumSelectionLength: 1,
					placeholder           : "Seleccione el tipo de documento",
					width                 : "100%",
				})
				.on("change",function(e)
				{
					if($(this).val().length>1)
					{
						$(this).val($(this).val().slice(0,1)).trigger('change');
					}
				});
			})
			.on('click','.delete_other_doc',function()
			{
				$(this).parents('.form_other_doc').remove();
			})
		});
	@if(isset($alert))
		{!! $alert !!}
	@endif
	</script>
@endsection
