@extends('layouts.child_module')
@section('data')
	@if ($budgetUpload->status != 'Subiendo')
		{!! Form::open(['route' => 'BreakdownWages.finish', 'method' => 'POST', 'id' => 'container-alta','files'=>true]) !!}
			@component('components.containers.container-form')
				<div class="col-span-2">
					@component('components.labels.label')
						@slot('classEx')
							font-bold
						@endslot
							Título:
					@endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							type="text" name="BudgetID" value="{{ $budgetUpload->id }}"
						@endslot
						@slot('classEx')
							hidden
						@endslot
					@endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							type="text" name="name" placeholder="Ingrese el título" data-validation="required" value="{{ $budgetUpload->name }}"
						@endslot
						@slot('classEx')
							remove
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label')
						@slot('classEx')
							font-bold
						@endslot
							Proyecto:
					@endcomponent
					@php
						$optionsProject[] =
						[
							"value"			=>	$budgetUpload->proyect->idproyect,
							"description"	=>	$budgetUpload->proyect->proyectName,
							"selected"		=>	(isset($budgetUpload->proyect->idproyect) ? "selected" : "")
						];
					@endphp
					@component('components.inputs.select', ["options" => $optionsProject])
						@slot('attributeEx')
							name="project_id" multiple="multiple" data-validation="required"
						@endslot
						@slot('classEx')
							js-project removeselect
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label')
						@slot('classEx')
							font-bold
						@endslot
							Cliente:
					@endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							type="text" name="client" placeholder="Ingrese el cliente" data-validation="required" value="{{ $budgetUpload->client }}"
						@endslot
						@slot('classEx')
							remove
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label')
						@slot('classEx')
							font-bold
						@endslot
							Concurso No:
					@endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							type="text" name="contestNo" placeholder="Ingrese el concurso" data-validation="required" value="{{ $budgetUpload->contestNo }}"
						@endslot
						@slot('classEx')
							remove
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label')
						@slot('classEx')
							font-bold
						@endslot
							Obra:
					@endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							type="text" name="obra" placeholder="Ingrese la obra" data-validation="required" value="{{ $budgetUpload->obra }}"
						@endslot
						@slot('classEx')
							remove
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label')
						@slot('classEx')
							font-bold
						@endslot
							Lugar:
					@endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							type="text" name="place" placeholder="Ingrese el lugar" data-validation="required" value="{{ $budgetUpload->place }}"
						@endslot
						@slot('classEx')
							remove
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label')
						@slot('classEx')
							font-bold
						@endslot
							Ciudad:
					@endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							type="text" name="city" placeholder="Ingrese la ciudad" data-validation="required" value="{{ $budgetUpload->city }}"
						@endslot
						@slot('classEx')
							remove
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label')
						@slot('classEx')
							font-bold
						@endslot
							Inicio de obra:
					@endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							data-default="{{ Carbon\Carbon::parse($budgetUpload->startObra)->format('d-m-Y')  }}" id="startObra" value="{{ Carbon\Carbon::parse($budgetUpload->startObra)->format('d-m-Y')  }}" type="text" name="startObra" data-validation="required"
						@endslot
						@slot('classEx')
							remove
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label')
						@slot('classEx')
							font-bold
						@endslot
							Fin de obra:
					@endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							data-default="{{ Carbon\Carbon::parse($budgetUpload->endObra)->format('d-m-Y')  }}" id="endObra" value="{{ Carbon\Carbon::parse($budgetUpload->endObra)->format('d-m-Y')  }}" type="text" name="endObra" data-validation="required"
						@endslot
						@slot('classEx')
							remove
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label')
						@slot('classEx')
							font-bold
						@endslot
							Duración:
					@endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
						id="days" readonly disabled value="{{ Carbon\Carbon::parse($budgetUpload->endObra)->diffInDays(Carbon\Carbon::parse($budgetUpload->startObra))  }}" type="text"
						@endslot
						@slot('classEx')
							remove
						@endslot
					@endcomponent
				</div>
			@endcomponent
			<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mt-12">
				@component('components.buttons.button', ["variant"	=>	"primary"])
					@slot('attributeEx')
						type="submit"
					@endslot
					@slot('label')
						GUARDAR
					@endslot
				@endcomponent
				@component('components.buttons.button', ["variant"	=>	"red"])
					@slot('attributeEx')
						type="submit" formaction="{{ route('BreakdownWages.delete') }}"
					@endslot
					@slot('label')
						ELIMINAR
					@endslot
				@endcomponent
			</div>
		{!! Form::close() !!}
		@component("components.labels.title-divisor") Buscar Artículo @endcomponent
		@component('components.inputs.input-search')
			Artículo:
			@slot('attributeExInput')
				type="text" name="search" id="input-search" placeholder="Ingrese el artículo"
			@endslot
			@slot('attributeExButton')
				type="button"
			@endslot
			@slot('classEx')
				my-4
			@endslot
		@endcomponent
	@endif
	@if ($budgetUpload->status == 'Subiendo')
		@component('components.containers.container-form')
			<div class="col-span-4 text-center">
				@component('components.labels.label')
					Estado:
				@endcomponent
				@component('components.labels.label')
					@slot('classEx')
						font-bold
					@endslot
					@slot('attributeEx')
						id="status"
					@endslot
					{{ $budgetUpload->status }}
				@endcomponent
			</div>
			<div class="col-span-4 text-center">
				@component('components.labels.label')
					Última actualización:
				@endcomponent
				@component('components.labels.label')
					@slot('classEx')
						font-bold
					@endslot
					@slot('attributeEx')
						id="statusDate"
					@endslot
				@endcomponent
			</div>
		@endcomponent
	@endif
	<div id="table-return"></div>
	<div id="pagination"></div>
	@component('components.modals.modal', ["varian" => "large"])
		@slot('id')
			modalEdit
		@endslot
		@slot('modalBody')
			@component('components.labels.title-divisor')
				DETALLES DEL ARTÍCULO
			@endcomponent
			@component('components.containers.container-form')
				@component('components.inputs.input-text')
					@slot('attributeEx')
						name="id"
					@endslot
					@slot('classEx')
						mt-4
						hidden
					@endslot
				@endcomponent
				<div class="col-span-2">
					@component('components.labels.label')
						@slot('classEx')
							font-bold
						@endslot
							Código:
					@endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							type="text" name="code" placeholder="Ingrese el código" data-validation="required"
						@endslot
						@slot('classEx')
							remove
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label')
						@slot('classEx')
							font-bold
						@endslot
							Concepto:
					@endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							type="text" name="concept" placeholder="Ingrese el concepto" data-validation="required"
						@endslot
						@slot('classEx')
							remove
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label')
						@slot('classEx')
							font-bold
						@endslot
							Medida:
					@endcomponent
					@php
						$optionsMeasurement = [];
						foreach (App\CatMeasurementTypes::whereNotNull('type')->get() as $m_types)
						{
							foreach ($m_types->childrens()->orderBy('child_order','asc')->get() as $child)
							{
								$optionsMeasurement[]	=
								[
									"value"			=>	$child->id,
									"description"	=>	$child->abbreviation
								];
							}
						}
					@endphp
					@component('components.inputs.select', ["options" => $optionsMeasurement])
						@slot('attributeEx')
							name="measurement" multiple="multiple"
						@endslot
						@slot('classEx')
							js-measurement removeselect
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label')
						@slot('classEx')
							font-bold
						@endslot
							Salario Base por Jornal:
					@endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							type="text" name="baseSalaryPerDay" placeholder="Ingrese el salario" data-validation="required"
						@endslot
						@slot('classEx')
							remove
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label')
						@slot('classEx')
							font-bold
						@endslot
							Factor Salario Real:
					@endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							type="text" name="realSalaryFactor" placeholder="Ingrese el factor" data-validation="required"
						@endslot
						@slot('classEx')
							remove
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label')
						@slot('classEx')
							font-bold
						@endslot
							Salario Real:
					@endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							type="text" name="realSalary" placeholder="Ingrese el salario real" data-validation="required"
						@endslot
						@slot('classEx')
							remove
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label')
						@slot('classEx')
							font-bold
						@endslot
							Viáticos:
					@endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							type="text" name="viatics" placeholder="Ingrese los viáticos" data-validation="required"
						@endslot
						@slot('classEx')
							remove
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label')
						@slot('classEx')
							font-bold
						@endslot
							Alimentación:
					@endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							type="text" name="feeding" placeholder="Ingrese la alimentación" data-validation="required"
						@endslot
						@slot('classEx')
							remove
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label')
						@slot('classEx')
							font-bold
						@endslot
							Salario Total:
					@endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							type="text" name="totalSalary" placeholder="Ingrese el salario total" data-validation="required"
						@endslot
						@slot('classEx')
							remove
						@endslot
					@endcomponent
				</div>
			@endcomponent
		@endslot
		@slot('modalFooter')
			@component('components.buttons.button', ["variant" => "secondary"])
				@slot('attributeEx')
					type="button" data-dismiss="modal"
				@endslot
				@slot('classEx')
					send-edit
				@endslot
				@slot('label')
					Actualizar
				@endslot
			@endcomponent
			@component('components.buttons.button', ["variant" => "red"])
				@slot('attributeEx')
					type="button" data-dismiss="modal"
				@endslot
				@slot('label')
					Cancelar
				@endslot
			@endcomponent
		@endslot
	@endcomponent
@endsection

@section('scripts')
	<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
	<script src="{{ asset('js/jquery-ui.js') }}"></script>
	<script src="{{ asset('js/jquery.numeric.js') }}"></script>
	<script src="{{ asset('js/moment.js') }}"></script>
	<script type="text/javascript">
		function validate()
		{
			$.validate(
			{
				form: '#container-alta',
				modules		: 'security',
				onSuccess : function($form)
				{
					status = $('#status').html().trim();
					if (status == 'Subiendo') 
					{
						swal({
							title: "Error",
							text: "Debe esperar a que termine de cargar el documento.",
							icon: "error",
							buttons: 
							{
								confirm: true,
							},
						});
						return false;
					}
					else
					{
						return true;
					}
				}
			});
		}
		$(document).ready(function ()
		{
			validate();
			generalSelect({'selector': '.js-project', 'model':24});
			statusDate = moment().format('DD-MM-YYYY HH:mm:ss');
			$('#statusDate').html(statusDate);
			getUploadStatus();
			$('input[name="startObra"]').datepicker({ dateFormat:'dd-mm-yy' });
			$('input[name="endObra"]').datepicker({ dateFormat:'dd-mm-yy' });
			@php
				$selects = collect([
					[
						"identificator"				=> ".js-measurement",
						"placeholder"				=> "Seleccione la unidad",
						"language"					=> "es",
						"maximumSelectionLength"	=> "1"
					]
				]);
			@endphp
			@component('components.scripts.selects',["selects" => $selects]) @endcomponent
			paginate_arts(undefined,true)
			$(document).on('click','.edit-item',function()
			{
				id                = $(this).parents('.id-row').find('.idd').html().trim();
				code              = $(this).parents('.id-row').find('.code').html().trim();
				concept           = $(this).parents('.id-row').find('.concept').html().trim();
				measurement       = $(this).parents('.id-row').find('.measurement').html().trim();
				baseSalaryPerDay  = $(this).parents('.id-row').find('.baseSalaryPerDay').html().trim();
				realSalaryFactor  = $(this).parents('.id-row').find('.realSalaryFactor').html().trim();
				realSalary        = $(this).parents('.id-row').find('.realSalary').html().trim();
				viatics           = $(this).parents('.id-row').find('.viatics').html().trim();
				feeding           = $(this).parents('.id-row').find('.feeding').html().trim();
				totalSalary       = $(this).parents('.id-row').find('.totalSalary').html().trim();
				cat_names = [
					@foreach(App\CatMeasurementTypes::whereNotNull('type')->get() as $m_types)
						@foreach ($m_types->childrens()->orderBy('child_order','asc')->get() as $child)
							{
								'id':{{ $child->id }},
								'value':'{{ $child->abbreviation }}'
							},
						@endforeach
					@endforeach
				];
				cat = cat_names.find(element => element.value === measurement);
				if(cat)
				{
					cat=cat.id;
				}
				$('input[name="id"]').val(id)
				$('input[name="code"]').val(code)
				$('input[name="concept"]').val(concept)
				$('input[name="baseSalaryPerDay"]').val(baseSalaryPerDay)
				$('input[name="realSalaryFactor"]').val(realSalaryFactor)
				$('input[name="realSalary"]').val(realSalary)
				$('input[name="viatics"]').val(viatics)
				$('input[name="feeding"]').val(feeding)
				$('input[name="totalSalary"]').val(totalSalary)
				$('#modalEdit').modal("show");
				@php
					$selects = collect([
						[
							"identificator"				=> ".js-measurement",
							"placeholder"				=> "Seleccione la unidad",
							"language"					=> "es",
							"maximumSelectionLength"	=> "1"
						]
					]);
				@endphp
				@component('components.scripts.selects',["selects" => $selects]) @endcomponent
				$('input[name="baseSalaryPerDay"],input[name="realSalaryFactor"],input[name="realSalary"],input[name="viatics"],input[name="feeding"],input[name="totalSalary"]').numeric({ negative : false, altDecimal: ".", decimalPlaces: 25 });
				$('.js-measurement').val(cat).trigger('change');
			})
			.on('click','.send-edit',function()
			{
				swal(
				{
					closeOnClickOutside: false,
					closeOnEsc         : false,
					icon               : '{{ asset(getenv('LOADING_IMG')) }}',
					button             : false
				});
				id               = $('input[name="id"]').val();
				code             = $('input[name="code"]').val();
				concept          = $('input[name="concept"]').val();
				baseSalaryPerDay = $('input[name="baseSalaryPerDay"]').val();
				realSalaryFactor = $('input[name="realSalaryFactor"]').val();
				realSalary       = $('input[name="realSalary"]').val();
				viatics          = $('input[name="viatics"]').val();
				feeding          = $('input[name="feeding"]').val();
				totalSalary      = $('input[name="totalSalary"]').val();
				measurement      = $('.js-measurement option:selected').text().trim();
				formData         = new FormData();
				formData.append('id', id);
				formData.append('code', code);
				formData.append('concept', concept);
				formData.append('measurement', measurement);
				formData.append('baseSalaryPerDay', baseSalaryPerDay);
				formData.append('realSalaryFactor', realSalaryFactor);
				formData.append('realSalary', realSalary);
				formData.append('viatics', viatics);
				formData.append('feeding', feeding);
				formData.append('totalSalary', totalSalary);

				$.ajax({
				type		: 'post',
				url			: '{{ route("BreakdownWages.article.edit") }}',
				data		: formData,
				contentType	: false,
				processData	: false,
				success		: function(data)
				{
					tr = $('#id-'+id);
					tr.find('.code').html(code);
					tr.find('.concept').html(concept);
					tr.find('.baseSalaryPerDay').html(baseSalaryPerDay);
					tr.find('.realSalaryFactor').html(realSalaryFactor);
					tr.find('.realSalary').html(realSalary);
					tr.find('.viatics').html(viatics);
					tr.find('.feeding').html(feeding);
					tr.find('.totalSalary').html(totalSalary);
					tr.find('.measurement').html(measurement);
					
					swal('','Artículo actualizado.','success')
					$('#modalEdit').hide();
				},
				error		: function()
				{
					swal('', 'Error al actualizar.', 'error')
				}
				});
			})
			.on('change','#startObra,#endObra',function () {
				d1 = $('#startObra').val()
				d2 = $('#endObra').val()
				date1 = moment(d1,'DD-MM-YYYY');
				date2 = moment(d2,'DD-MM-YYYY');
				
				diffDays = date2.diff(date1, 'days')

				if(!moment(date1).isBefore(date2))
				{
					
					swal('', 'Error, la fecha de inicio de obra debe ser mayor que la de fin de obra.', 'error')
					.then(function () {
						set_default_dates()
					})
					return
				}

				$('#days').val(diffDays)
			})
			.on('change keyup paste','#input-search',function () {
				paginate_arts(undefined,true)
			})
		});
		function set_default_dates() {
			default_date1 = $('#startObra').data("default")
			default_date2 = $('#endObra').data("default")
			$('#startObra').val(default_date1)
			$('#endObra').val(default_date2)

			d_date1 = moment(default_date1,'DD-MM-YYYY');
			d_date2 = moment(default_date2,'DD-MM-YYYY');
			
			d_diffDays = d_date2.diff(d_date1, 'days')
			$('#days').val(d_diffDays)
		}
		function paginate_arts(page =undefined,firstSearch = false) {
			if(!firstSearch)
			{
				swal(
				{
					closeOnClickOutside:false,
					closeOnEsc:false,
					icon	: '{{ asset(getenv('LOADING_IMG')) }}',
					button	: false
				});
			}
			search = $('#input-search').val()
			$.ajax(
			{
				type : 'get',
				url  : '{{ route("BreakdownWages.paginate.arts") }}',
				data : {
					'page':page,
					'budgetUpload':{{ $budgetUpload->id }},
					'search':search,
					},
				success : function(response)
				{
					statusDate = moment().format('DD-MM-YYYY HH:mm:ss')
					$('#statusDate').html(statusDate)
					oldStatus = ($('#status').lenght > 0 ? $('#status').html().trim() : "");
					newStatus = response.BreakdownWagesUploads['status']
					if(oldStatus == 'Subiendo' && newStatus != 'Subiendo')
					{
						location.reload(true);
						return
					}
					$('#status').html(response.BreakdownWagesUploads['status'])

					data = response;
					if(response.BreakdownWagesUploads['status'] != 'Subiendo')
					{
						$('#table-return').html(response.table);
						$('#pagination').html(response['pagination']);
						$('.page-link').on('click', function(e){
								e.preventDefault();
								page = $(this).text();
								if($(this).text() === "›"){
									if(response.data.current_page + 1 > response.data.last_page)
										return;
									page = response.data.current_page + 1
								}
								if($(this).text() === "‹"){
									if(response.data.current_page - 1 <= 0)
										return;
									page = response.data.current_page - 1
								}
								paginate_arts(page)
						});
					}
					if(!firstSearch){
						swal.close()
						window.location = '#table'
					}
				},
				error : function()
				{
					swal('','Sucedió un error, por favor intente de nuevo.','error');
				}
			})
		}
		function getUploadStatus() {
			status = ($('#status').lenght > 0 ? $('#status').html().trim() : "");
			if(status == 'Subiendo')
			{
				paginate_arts(undefined,true)
				setTimeout(() => {

					getUploadStatus()
				}, 30000);
			}
		}
	</script>
@endsection