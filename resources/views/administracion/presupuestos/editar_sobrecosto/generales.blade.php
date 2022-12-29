@php
	$CostOverrunsNCGEnterprise = App\CostOverrunsNCGEnterprise::where('idUpload',$budget_id)->first();
	$CostOverrunsNCGCustomers = App\CostOverrunsNCGCustomers::where('idUpload',$budget_id)->first();
	$CostOverrunsNCGCompetition = App\CostOverrunsNCGCompetition::where('idUpload',$budget_id)->first();
	$CostOverrunsNCGConstruction = App\CostOverrunsNCGConstruction::where('idUpload',$budget_id)->first();
	$CostOverrunsNCGHeader = App\CostOverrunsNCGHeader::where('idUpload',$budget_id)->first();
	$CostOverrunsNCGAnnouncement = App\CostOverrunsNCGAnnouncement::where('idUpload',$budget_id)->first();

	$namesNCGEmpresa = [
		'razonsocial' => [
			'name' => 'Nombre de la empresa.',
			'type' => 'text'
		],
		'domicilio' => [
			'name' => 'Domicilio de la empresa.',
			'type' => 'text'
		],
		'colonia' => [
			'name' => 'Colonia de la empresa',
			'type' => 'text'
		],
		'ciudad' => [
			'name' => 'Ciudad donde se localiza la empresa.',
			'type' => 'text'
		],
		'estado' => [
			'name' => 'Entidad federativa o provincia donde se localiza la empresa',
			'type' => 'text'
		],
		'rfc' => [
			'name' => 'RFC de la empresa.',
			'type' => 'text'
		],
		'telefono' => [
			'name' => 'Telefono(s) de la empresa.',
			'type' => 'number'
		],
		'email' => [
			'name' => 'Correo electrónico de la empresa',
			'type' => 'text'
		],
		'cmic' => [
			'name' => 'Registro CMIC de la empresa.',
			'type' => 'text'
		],
		'infonavit' => [
			'name' => 'Registro INFONAVIT de la empresa.',
			'type' => 'text'
		],
		'imss' => [
			'name' => 'Registro IMSS de la empresa.',
			'type' => 'text'
		],
		'responsable' => [
			'name' => 'Nombre del responsable de la empresa (para firmas).',
			'type' => 'text'
		],
		'cargo' => [
			'name' => 'Cargo del responsable (para firmas).',
			'type' => 'text'
		],
	];
	$namesNCGCliente = [
		'nombrecliente' => [
			'name' => 'Nombre del cliente.',
			'type' => 'text'
		],
		'area' => [
			'name' => 'Area del cliente que convoca.',
			'type' => 'text'
		],
		'departamento' => [
			'name' => 'Departamento del cliente que licita.',
			'type' => 'text'
		],
		'direccioncliente' => [
			'name' => 'Dirección del cliente.',
			'type' => 'text'
		],
		'coloniacliente' => [
			'name' => 'Colonia del cliente.',
			'type' => 'text'
		],
		'codigopostalcliente' => [
			'name' => 'Código postal del cliente.',
			'type' => 'text'
		],
		'ciudadcliente' => [
			'name' => 'Ciudad del cliente.',
			'type' => 'text'
		],
		'telefonocliente' => [
			'name' => 'Teléfono del cliente.',
			'type' => 'number'
		],
		'emailcliente' => [
			'name' => 'e-Mail del cliente.',
			'type' => 'text'
		],
		'contactocliente' => [
			'name' => 'Nombre del contacto con el cliente.',
			'type' => 'text'
		],
	];
	$namesNCGConcurso = [
		'fechadeconcurso' => [
			'name' =>  'Fecha del concurso.',
			'type' => 'date'
		],
		'numerodeconcurso' => [
			'name' =>  'Número del concurso.',
			'type' => 'text'
		],
		'direcciondeconcurso' => [
			'name' =>  'Ubicación del concurso (dirección).',
			'type' => 'text'
		],
	];
	$namesNCGObra = [
		'nombredelaobra' => [
			'name' =>  'Nombre de la obra.',
			'type' => 'textarea'
		],
		'direcciondelaobra' => [
			'name' =>  'Dirección de la obra.',
			'type' => 'text'
		],
		'coloniadelaobra' => [
			'name' =>  'Colonia de la obra.',
			'type' => 'text'
		],
		'ciudaddelaobra' => [
			'name' =>  'Ciudad donde se localiza la obra.',
			'type' => 'text'
		],
		'estadodelaobra' => [
			'name' =>  'Estado o provincia donde se localiza la obra.',
			'type' => 'text'
		],
		'codigopostaldelaobra' => [
			'name' =>  'Código postal de la obra.',
			'type' => 'text'
		],
		'telefonodelaobra' => [
			'name' =>  'Teléfono de la obra.',
			'type' => 'number'
		],
		'emaildelaobra' => [
			'name' =>  'e-Mail de la obra.',
			'type' => 'text'
		],
		'responsabledelaobra' => [
			'name' =>  'Responsable de la obra.',
			'type' => 'text'
		],
		'cargoresponsabledelaobra' => [
			'name' =>  'Cargo del responsable de la obra.',
			'type' => 'text'
		],
		'fechainicio' => [
			'name' =>  'Fecha de inicio de la obra (con 1 en programa de obra).',
			'type' => 'date'
		],
		'fechaterminacion' => [
			'name' =>  'Fecha de terminación de la obra (con 1 en programa de obra).',
			'type' => 'date'
		],
		'totalpresupuestoprimeramoneda' => [
			'name' =>  'Total del presupuesto primera moneda.',
			'type' => 'decimal6'
		],
		'totalpresupuestosegundamoneda' => [
			'name' =>  'Total del presupuesto segunda moneda.',
			'type' => 'decimal6'
		],
		'porcentajeivapresupuesto' => [
			'name' =>  'Porcentaje iva presupuesto.',
			'type' => 'decimal2'
		],
	];
	$NCGEncabezado = [
		'plazocalculado' => [
			'name' =>  'Duración de la obra en dias naturales.',
			'type' => 'text'
		],
		'plazoreal' => [
			'name' =>  'Duración de la obra en dias habiles.',
			'type' => 'text'
		],
		'decimalesredondeo' => [
			'name' =>  'Decimales para redondeo de importes.',
			'type' => 'text'
		],
		'primeramoneda' => [
			'name' =>  'Descripción de la moneda 1 en que se muestra el reporte.',
			'type' => 'text'
		],
		'segundamoneda' => [
			'name' =>  'Descripción de la moneda 2 en que se muestra el reporte.',
			'type' => 'text'
		],
		'remateprimeramoneda' => [
			'name' =>  'Remate de la moneda 1',
			'type' => 'text'
		],
		'rematesegundamoneda' => [
			'name' =>  'Remate de la moneda 2',
			'type' => 'text'
		],
	];
	$NCGconvocatoria = [
		'numconvocatoria' => [
			'name' => 'Número de la convocatoria del concurso.',
			'type' => 'text'
		], 
		'fechaconvocatoria' => [
			'name' => 'Fecha de la convocatoria.',
			'type' => 'date'
		], 
		'tipodelicitacion' => [
			'name' => 'Tipo de licitacion',
			'type' => 'text'
		], 
	];

	$campos = [
		'CostOverrunsNCGEnterprise' => [
			'title' => 'DATOS DE LA EMPRESA',
			'db' => $CostOverrunsNCGEnterprise,
			'names' =>	$namesNCGEmpresa,
		],
		'CostOverrunsNCGCustomers' => [
			'title' => 'DATOS DEL CLIENTE',
			'db' => $CostOverrunsNCGCustomers,
			'names' =>	$namesNCGCliente,
		],
		'CostOverrunsNCGCompetition' => [
			'title' => 'DATOS DEL CONCURSO',
			'db' => $CostOverrunsNCGCompetition,
			'names' =>	$namesNCGConcurso,
		],
		'CostOverrunsNCGConstruction' => [
			'title' => 'DATOS DE LA OBRA',
			'db' => $CostOverrunsNCGConstruction,
			'names' =>	$namesNCGObra,
		],
		'CostOverrunsNCGHeader' => [
			'title' => 'DATOS ENCABEZADO',
			'db' => $CostOverrunsNCGHeader,
			'names' =>	$NCGEncabezado,
		],
		'CostOverrunsNCGAnnouncement' => [
			'title' => 'DATOS DE LA CONVOCATORIA',
			'db' => $CostOverrunsNCGAnnouncement,
			'names' =>	$NCGconvocatoria,
		],
	];
@endphp

{!! Form::open(['route' => ['Sobrecosto.save.generales',$budget_id], 'method' => 'POST', 'id' => 'container-alta','files'=>true]) !!}
<input name="save" value="true" hidden>
@foreach ($campos as $campo)
<div class="margin_top">
	@component('components.labels.title-divisor')    {{ $campo['title'] }}</strong>
	</center>
	<div class='divisor'>
		<div class='gray-divisor'></div>
		<div class='orange-divisor'></div>
		<div class='gray-divisor'></div>
	</div>
	@foreach ($campo['names'] as $key => $value)
	<div class='container-blocks'>
		<div class='search-table-center'>
				{!! generateInputForm($value['type'],$campo['db'][$key],$value['name'],$key) !!}
			</div>
		</div>
	@endforeach
</div>
@endforeach
<center>
  <button type="submit" class="btn btn-red">Siguiente</button>
</center>
{!! Form::close() !!}