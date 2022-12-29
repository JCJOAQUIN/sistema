@extends('layouts.child_module')
@section('data')
	@Form(["attributeEx" => "action=\"".route('budget.administration.download-upload-layout')."\" id=\"container-alta\" method=\"POST\"", "files" => true])
		<div class="sm:text-center text-left my-5">
			A continuación podrá realizar la carga y descarga de la plantilla para presupuestos administrativos:
		</div>
		@php
			foreach (App\Enterprise::orderName()->where('status','ACTIVE')->get() as $enterprise)
			{
				$optionEnterprise[]	=	["value" => $enterprise->id, "description" =>  $enterprise->name];
			}
			$componentSelect[]	=
			[
				"kind"	=>	"components.inputs.select",	"options"	=>	$optionEnterprise,	"attributeEx"	=>	"data-validation=\"required\" name=\"enterprise_download\""
			];
			$modelTable	=
			[
				["Empresa:",	$componentSelect]
			];
			$componentsEx	=
			[
				"kind"			=>	"components.buttons.button",
				"variant"		=>	"success",
				"attributeEx"	=>	"type=\"submit\" name=\"download\" value=\"Descargar\"",
				"classEx"		=>	"px-12 py-2",
				"label"			=>	"DESCARGAR"
			];
		@endphp
		@component('components.templates.outputs.table-detail', ["modelTable" => $modelTable, "componentsEx" => $componentsEx])
			@slot('classEx')
				mt-4
			@endslot
			@slot('title')
				Descargar plantilla
			@endslot
		@endcomponent
		@php
			foreach (App\Enterprise::orderName()->where('status','ACTIVE')->get() as $enterprise)
			{
				$optionEnterpriseLoad[]	=	["value" => $enterprise->id, "description" =>  $enterprise->name];
			}
			$componentEnterpriseLoad[]	=
			[
				"kind"	=>	"components.inputs.select",	"options"	=>	$optionEnterpriseLoad,	"attributeEx"	=>	"data-validation=\"required\" name=\"enterprise_upload\""
			];
			foreach (App\Department::orderName()->where('status','ACTIVE')->get() as $department)
			{
				$optionDepartment[]	=	["value" => $department->id, "description" =>  $department->name];
			}
			$componentDepartment[]	=
			[
				"kind"	=>	"components.inputs.select",	"options"	=>	$optionDepartment,	"attributeEx"	=>	"data-validation=\"required\" name=\"department_upload\""
			];
			$componentProject[]	=
			[
				"kind"	=>	"components.inputs.select",	"options"	=>	[],	"attributeEx"	=>	"data-validation=\"required\" name=\"project_upload\""
			];
			$optionPeriodicity[]	=	["value" => "1", "description" =>  "Semanal"];
			$optionPeriodicity[]	=	["value" => "2", "description" =>  "Mensual"];
			$componentPeriodicity[]	=
			[
				"kind"	=>	"components.inputs.select",	"options"	=>	$optionPeriodicity,	"attributeEx"	=>	"data-validation=\"required\" name=\"periodicity_upload\""
			];
			$dateRange[]			=	
			[
				"kind"			=>	"components.inputs.range-input",
				"attributeEx" 	=> "data-validation=\"required\"",
				"attributeExInstance" => "data-params=\"'batchMode':'week'\"",
				"inputs" 		=> [
										[
											"input_classEx"		=> "datepicker",
											"input_attributeEx" => "type=\"text\" id=\"initRange\" name=\"week\" step=\"1\" placeholder=\"\""
										],
										[
											"input_classEx"		=> "datepicker",
											"input_attributeEx" => "type=\"text\" id=\"endrange\" name=\"week\" step=\"2\" placeholder=\"\""
										]
									]
			];
			$alertPercent[]			=	["kind"	=>	"components.inputs.input-text", "attributeEx"	=>	"data-validation=\"required\" type=\"text\" name=\"alert_percent\""];
			$optionSeparator[]		=	["value" => ",",	"description"	=>	"coma (,)"];
			$optionSeparator[]		=	["value" => ";",	"description"	=>	"punto y coma (;)"];
			$componentSeparator[]	=
			[
				"kind"	=>	"components.inputs.select",	"options"	=>	$optionSeparator,	"attributeEx"	=>	"data-validation=\"required\" name=\"separator\""
			];
			$modelTable	=
			[
				["Empresa:",				$componentEnterpriseLoad],
				["Departamento:",			$componentDepartment],
				["Proyecto:",				$componentProject],
				["Periodicidad:",			$componentPeriodicity],
				["Semana:",					$dateRange],
				["Porcentaje para alerta:",	$alertPercent],
				["Separador:",				$componentSeparator],
			];
		@endphp
		@component('components.templates.outputs.table-detail', ["modelTable" => $modelTable])
			@slot('classEx')
				mt-12
			@endslot
			@slot('title')
				Cargar presupuesto
			@endslot
			@slot('componentEx')
				<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center">
					<div class="w-3/5">
						@component('components.documents.upload-files')
							@slot('classExInput')
								input-text pathActioner
							@endslot
							@slot('classExDelete')
								delete-doc
							@endslot
							@slot('attributeExInput')
								type="file"
								name="path"
								accept=".csv"
								data-validation="required"
							@endslot
							@slot('attributeExRealPath')
								name="realPath"
							@endslot
							@slot('classExRealPath')
								path
							@endslot
						@endcomponent
					</div>
				</div>
				<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center">
					@component('components.buttons.button', ["variant" => "primary"])
					@slot('classEx')
						px-12 py-2
					@endslot
						@slot('attributeEx')
							type="submit" name="upload" value="Cargar"
						@endslot
						@slot('label')
							Cargar
						@endslot
					@endcomponent
				</div>
			@endslot
		@endcomponent

		<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center py-5">
			<div class="w-3/5">
				@component('components.documents.upload-files')
					@slot('classExInput')
						input-text pathActioner
					@endslot
					@slot('classExDelete')
						delete-doc
					@endslot
					@slot('attributeExInput')
						type="file"
						name="path"
						accept=".csv"
						data-validation="required"
					@endslot
					@slot('attributeExRealPath')
						name="realPath"
					@endslot
					@slot('classExRealPath')
						path
					@endslot
				@endcomponent
			</div>
		</div>
		<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center">
			@component('components.buttons.button',["variant" => "primary"])
			@slot('classEx')
				px-12 py-2
			@endslot
				@slot('attributeEx')
					type="submit" name="upload" value="Cargar"
				@endslot
				@slot('label')
					<span class="fas fa-upload"></span> Cargar
				@endslot
			@endcomponent
		</div>
	@endForm
@endsection
@section('scripts')
	<script src="{{ asset('js/jquery.numeric.js') }}"></script>
	<script type="text/javascript" src="{{ asset('js/moment.min.js') }}"></script>
	<script type="text/javascript">
		$(document).ready(function()
		{
			validate();
			@php
				$selects = collect([
					[
						"identificator"				=> "[name=\"enterprise_download\"]",
						"placeholder"				=> "Seleccione la empresa",
						"language"					=> "es",
						"maximumSelectionLength"	=> "1"
					],
					[
						"identificator"				=> "[name=\"enterprise_upload\"]",
						"placeholder"				=> "Seleccione la empresa",
						"language"					=> "es",
						"maximumSelectionLength"	=> "1"
					],
					[
						"identificator"				=> "[name=\"department_upload\"]",
						"placeholder"				=> "Seleccione el departamento",
						"language"					=> "es",
						"maximumSelectionLength"	=> "1"
					],
					[
						"identificator"				=> "[name=\"periodicity_upload\"]",
						"placeholder"				=> "Seleccione la periodicidad",
						"language"					=> "es",
						"maximumSelectionLength"	=> "1"
					],
					[
						"identificator"				=> "[name=\"separator\"]",
						"placeholder"				=> "Seleccione el separador",
						"language"					=> "es",
						"maximumSelectionLength"	=> "1"
					]
				]);
			@endphp
			@component('components.scripts.selects',["selects" => $selects]) @endcomponent
			generalSelect({'selector':'[name="project_upload"]', 'model': 21});
			$('[name="alert_percent"]').numeric({ negative:false, altDecimal: ".", decimalPlaces: 2 });
			$(document).on('click','[name="upload"],[name="download"]',function(e)
			{
				if($(this).attr('name') == "download")
				{
					$('[name="enterprise_download"]').attr('data-validation', 'required');
					$('[name="enterprise_upload"]').removeAttr('data-validation');
					$('[name="department_upload"]').removeAttr('data-validation');
					$('[name="project_upload"]').removeAttr('data-validation');
					$('[name="periodicity_upload"]').removeAttr('data-validation');
					$('[name="week"]').removeAttr('data-validation');
					$('[name="alert_percent"]').removeAttr('data-validation');
					$('[name="separator"]').removeAttr('data-validation');
					$('[name="path"]').removeAttr('data-validation','required');
				}
				else
				{
					$('[name="enterprise_download"]').removeAttr('data-validation');
					$('[name="enterprise_upload"]').attr('data-validation','required');
					$('[name="department_upload"]').attr('data-validation','required');
					$('[name="project_upload"]').attr('data-validation','required');
					$('[name="periodicity_upload"]').attr('data-validation','required');
					$('[name="week"]').attr('data-validation','required');
					$('[name="alert_percent"]').attr('data-validation','required');
					$('[name="separator"]').attr('data-validation','required');
					$('[name="path"]').attr('data-validation','required');
					
				}
			})
			.on('change','.input-text.pathActioner',function(e)
			{
				filename		= $(this);
				uploadedName 	= $(this).parent('.uploader-content').siblings('input[name="realPath"]');
				extention		= /\.csv/i;
				
				if (filename.val().search(extention) == -1)
				{
					swal('', 'El tipo de archivo no es soportado, por favor seleccione un archivo CSV', 'warning');
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
						url			: '{{ route("budget.administration.upload-file") }}',
						data		: formData,
						contentType	: false,
						processData	: false,
						success		: function(r)
						{
							if(r.error=='DONE')
							{
								$(e.currentTarget).removeAttr('style').parent('.uploader-content').removeClass('loading').addClass('image_success');
								$(e.currentTarget).parent('.uploader-content').siblings('input[name="realPath"]').val(r.path);
							}
							else
							{
								swal('',r.message, 'error');
								$(e.currentTarget).removeAttr('style').parent('.uploader-content').removeClass('loading');
								$(e.currentTarget).val('');
								$(e.currentTarget).parent('.uploader-content').siblings('input[name="realPath"]').val('');
							}
						},
						error: function()
						{
							swal('', 'Ocurrió un error durante la carga del archivo, intente de nuevo, por favor', 'error');
							$(e.currentTarget).removeAttr('style').parent('.uploader-content').removeClass('loading');
							$(e.currentTarget).val('');
							$(e.currentTarget).parent('.uploader-content').siblings('input[name="realPath"]').val('');
						}
					})
				}
			})
			.on('change','[name="alert_percent"]',function()
			{
				if ($(this).val() > 100) 
				{
					swal('','El valor no puede ser mayor a 100','info');
					$(this).val('0');
				}
			})
			.on('changes','[name="periodicity_upload"]',function() // v1
			{
				if($(this).val() == 1)
				{
					$('#initRange').dateRangePicker({
						batchMode		: 'week',
						showShortcuts	: false,
						language		: 'es',
						separator		: ' hasta ',
						startOfWeek		: 'monday',
						getValue: function()
						{
							if ($('#initRange').val() && $('#endrange').val() )
								return $('#initRange').val() + ' hasta ' + $('#endrange').val();
							else
								return '';
						},
						setValue: function(s,s1,s2)
						{
							$('#initRange').val(s1);
							$('#endrange').val(s2);
						}
					});
				}
				else
				{
					$('#initRange').dateRangePicker({
						batchMode		: 'month',
						showShortcuts	: false,
						language		: 'es',
						separator		: ' hasta ',
						startOfWeek		: 'monday',
						getValue: function()
						{
							if ($('#initRange').val() && $('#endrange').val() )
								return $('#initRange').val() + ' hasta ' + $('#endrange').val();
							else
								return '';
						},
						setValue: function(s,s1,s2)
						{
							$('#initRange').val(s1);
							$('#endrange').val(s2);
						}
					});
				}
			})
		});
		function validate()
		{
			$.validate(
			{
				form		: '#container-alta',
				modules		: 'security',
				onError   	: function($form)
				{
					swal('', 'Por favor llene todos los campos que son obligatorios.', 'error');
					return false;
				}
			});
		}
	</script>
@endsection