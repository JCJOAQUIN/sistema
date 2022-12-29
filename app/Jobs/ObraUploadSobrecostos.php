<?php

namespace App\Jobs;

use App\COAdditionalCharges;
use App\COAdvanceDocumentation;
use App\COAdditionalChargeCalcDocument;
use App\COFinancingCalcDocument;
use App\CODaysToConsiderDocument;
use App\CODaysToPayDocument;
use App\COEnterpriseDocument;
use App\COInterestsToConsiderDocument;
use App\COThousandDocument;
use App\COTechnicalStaffYear;
use App\COTechnicalStaffConcept;
use App\COTechnicalStaffYearSalary;
use App\COTechnicalStaffSalaryConcept;
use App\COFinancialConcept;
use App\COGeneralFinancial;
use App\COFinancialMonth;
use App\COIndirectItemizedConcept;
use App\COIndirectItemizedGeneral;
use App\COGeneralTemplate;
use App\COFieldStaffListTemplate;
use App\COCentralStaffListTemplate;
use App\COFieldStaffTemplate;
use App\COFieldStaffGeneralTemplate;
use App\COCentralStaffTemplate;
use App\COCentralStaffGeneralTemplate;
use App\COAdvanceProgram;
use App\COCostPeriodProgram;
use App\COPeriodProgram;
use App\COSummaryConcept;
use App\COSummaryIndirectConcept;
use App\COSummaryGeneralIndirect;
use Excel;
use App\CostOverruns;
use App\COConstructionAMIP;
use App\COConstructionTwoAdvance;
use App\COConstructionBudgetExceed;
use App\COCAdvanceTypeTable;
use App\COCSecondAdvanceTypeTable;
use App\COCAnAdvance;
use App\COCValues;
use App\COCValuesThatApply;
use App\COCRequiredValues;
use App\CostOverrunsNCGCustomers;
use App\CostOverrunsNCGCompetition;
use App\CostOverrunsNCGAnnouncement;
use App\CostOverrunsNCGEnterprise;
use App\CostOverrunsNCGHeader;
use App\CostOverrunsNCGConstruction;
use App\CODeterminationUtility;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Storage;

class ObraUploadSobrecostos implements ShouldQueue
{
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	protected $Sobrecostos;



	/**
	 * Create a new job instance.
	 *
	 * @return void
	 */
	public function __construct(CostOverruns $Sobrecostos)
	{
		$this->Sobrecostos = $Sobrecostos;
	}

	/**
	 * Execute the job.
	 *
	 * @return void
	 */
	public function handle()
	{
		$bgup = $this->Sobrecostos;

		$path = Storage::disk('public')->path($bgup->file);
		Excel::selectSheets(
			'N_Campos Generales',
			'DatosObra',
			'Programa',
			'a)Plantilla',
			'b)Indirectos Desglosados',
			'c)Resumen Indirectos',
			'd)Pers.Técnico',
			'e)Pers.Técnico$',
			'f)Financ_Horizontal',
			'g)Utilidad',
			'h)Cargos_Adicionales',
			'i)Resumen',
			'z)Documentacion'
		)->load($path, function ($reader) use ($bgup) {
			$reader->noHeading();

			$nc_parent;
			$nc_search = false;

			foreach ($reader->get() as $sheet) {
				if ($sheet->getTitle() == 'N_Campos Generales')
					$this->saveNCG($sheet, $bgup);
				if ($sheet->getTitle() == 'a)Plantilla')
					$this->saveDatosPlantilla($sheet, $bgup);
				if ($sheet->getTitle() == 'b)Indirectos Desglosados')
					$this->saveDatosBIndirectos($sheet, $bgup);
				if ($sheet->getTitle() == 'c)Resumen Indirectos')
					$this->saveDatosRIndirectos($sheet, $bgup);
				if ($sheet->getTitle() == 'g)Utilidad')
					$this->saveDatosUtilidad($sheet, $bgup);
				if ($sheet->getTitle() == 'h)Cargos_Adicionales')
					$this->saveDatosCargosAdicionales($sheet, $bgup);
				if ($sheet->getTitle() == 'i)Resumen')
					$this->saveDatosResumen($sheet, $bgup);
			}
		}, 'UTF-8');
		Excel::selectSheets(
			'N_Campos Generales',
			'DatosObra',
			'Programa',
			'a)Plantilla',
			'b)Indirectos Desglosados',
			'c)Resumen Indirectos',
			'd)Pers.Técnico',
			'e)Pers.Técnico$',
			'f)Financ_Horizontal',
			'g)Utilidad',
			'h)Cargos_Adicionales',
			'i)Resumen',
			'z)Documentacion'
		)->load($path, function ($reader) use ($bgup) {

			$reader->noHeading();
			$reader->formatDates(false);

			$nc_parent;
			$nc_search = false;


			foreach ($reader->get() as $sheet) {

				if ($sheet->getTitle() == 'DatosObra')
					$this->saveDatosObra($sheet, $bgup);
				if ($sheet->getTitle() == 'Programa')
					$this->saveDatosPrograma($sheet, $bgup);
				if ($sheet->getTitle() == 'd)Pers.Técnico')
					$this->saveDatosDPersTecnico($sheet, $bgup);
				if ($sheet->getTitle() == 'e)Pers.Técnico$')
					$this->saveDatosDPersTecnicoSalarios($sheet, $bgup);
				if ($sheet->getTitle() == 'f)Financ_Horizontal')
					$this->saveDatosFinanc($sheet, $bgup);
				if ($sheet->getTitle() == 'z)Documentacion')
					$this->saveDatosDocumentacion($sheet, $bgup);
			}
		}, 'UTF-8');
		$bgup->status = 1;
		$bgup->save();
	}

	public function saveNCG($sheet, $bgup)
	{

		$name = '';
		$CostOverrunsNCGEnterprise;
		$CostOverrunsNCGCustomers;
		$CostOverrunsNCGCompetition;
		$CostOverrunsNCGConstruction;
		$CostOverrunsNCGHeader;
		$CostOverrunsNCGAnnouncement;
		$COCSecondAdvanceTypeTable;
		foreach ($sheet->toArray() as $s) {
			switch ($s[0]) {
				case 'DATOS DE LA EMPRESA':
					$name = 'DATOS DE LA EMPRESA';
					break;
				case 'DATOS DEL CLIENTE':
					$name = 'DATOS DEL CLIENTE';
					break;
				case 'DATOS DEL CONCURSO':
					$name = 'DATOS DEL CONCURSO';
					break;
				case 'DATOS DE LA OBRA':
					$name = 'DATOS DE LA OBRA';
					break;
				case 'DATOS ENCABEZADO':
					$name = 'DATOS ENCABEZADO';
					break;
				case 'DATOS DE LA CONVOCATORIA':
					$name = 'DATOS DE LA CONVOCATORIA';
					break;
				default:
					# code...
					break;
			}


			if ($name == 'DATOS DE LA EMPRESA') {
				switch ($s[0]) {
					case 'razonsocial':
						$CostOverrunsNCGEnterprise = CostOverrunsNCGEnterprise::create([
							'idUpload' => $bgup->id,
							'razonsocial' => $s[2],
						]);
						break;
					case 'domicilio':
						$CostOverrunsNCGEnterprise->domicilio = $s[2];
						$CostOverrunsNCGEnterprise->save();
						break;
					case 'colonia':
						$CostOverrunsNCGEnterprise->colonia = $s[2];
						$CostOverrunsNCGEnterprise->save();
						break;
					case 'ciudad':
						$CostOverrunsNCGEnterprise->ciudad = $s[2];
						$CostOverrunsNCGEnterprise->save();
						break;
					case 'estado':
						$CostOverrunsNCGEnterprise->estado = $s[2];
						$CostOverrunsNCGEnterprise->save();
						break;
					case 'rfc':
						$CostOverrunsNCGEnterprise->rfc = $s[2];
						$CostOverrunsNCGEnterprise->save();
						break;
					case 'telefono':
						$CostOverrunsNCGEnterprise->telefono = $s[2];
						$CostOverrunsNCGEnterprise->save();
						break;
					case 'email':

						$CostOverrunsNCGEnterprise->email1 = $sheet[2][0];
						$CostOverrunsNCGEnterprise->email2 = $sheet[2][1];
						$CostOverrunsNCGEnterprise->email3 = $sheet[2][2];
						$CostOverrunsNCGEnterprise->save();
						break;
					case 'cmic':
						$CostOverrunsNCGEnterprise->cmic = $s[2];
						$CostOverrunsNCGEnterprise->save();
						break;
					case 'infonavit':
						$CostOverrunsNCGEnterprise->infonavit = $s[2];
						$CostOverrunsNCGEnterprise->save();
						break;
					case 'imss':
						$CostOverrunsNCGEnterprise->imss = $s[2];
						$CostOverrunsNCGEnterprise->save();
						break;
					case 'responsable':
						$CostOverrunsNCGEnterprise->responsable = $s[2];
						$CostOverrunsNCGEnterprise->save();
						break;
					case 'cargo':
						$CostOverrunsNCGEnterprise->cargo = $s[2];
						$CostOverrunsNCGEnterprise->save();
						break;

					default:
						# code...
						break;
				}
			}

			if ($name == 'DATOS DEL CLIENTE') {
				switch ($s[0]) {
					case 'nombrecliente':
						$CostOverrunsNCGCustomers = CostOverrunsNCGCustomers::create([
							'idUpload' => $bgup->id,
							'nombrecliente' => $s[2],
						]);
						break;
					case 'area':
						$CostOverrunsNCGCustomers->area = $s[2];
						$CostOverrunsNCGCustomers->save();
						break;
					case 'departamento':
						$CostOverrunsNCGCustomers->departamento = $s[2];
						$CostOverrunsNCGCustomers->save();
						break;
					case 'direccioncliente':
						$CostOverrunsNCGCustomers->direccioncliente = $s[2];
						$CostOverrunsNCGCustomers->save();
						break;
					case 'coloniacliente':
						$CostOverrunsNCGCustomers->coloniacliente = $s[2];
						$CostOverrunsNCGCustomers->save();
						break;
					case 'codigopostalcliente':
						$CostOverrunsNCGCustomers->codigopostalcliente = $s[2];
						$CostOverrunsNCGCustomers->save();
						break;
					case 'ciudadcliente':
						$CostOverrunsNCGCustomers->ciudadcliente = $s[2];
						$CostOverrunsNCGCustomers->save();
						break;
					case 'telefonocliente':
						$CostOverrunsNCGCustomers->telefonocliente = $s[2];
						$CostOverrunsNCGCustomers->save();
						break;
					case 'emailcliente':
						$CostOverrunsNCGCustomers->emailcliente = $s[2];
						$CostOverrunsNCGCustomers->save();
						break;
					case 'contactocliente':
						$CostOverrunsNCGCustomers->contactocliente = $s[2];
						$CostOverrunsNCGCustomers->save();
						break;

					default:
						break;
				}
			}

			if ($name == 'DATOS DEL CONCURSO') {

				switch ($s[0]) {
					case 'fechadeconcurso':

						$CostOverrunsNCGCompetition = CostOverrunsNCGCompetition::create([
							'idUpload' => $bgup->id,
							'fechadeconcurso' => empty($s[2]) ? $s[2] : $s[2]->format('Y-m-d'),
						]);
						break;
					case 'numerodeconcurso':
						$CostOverrunsNCGCompetition->numerodeconcurso = $s[2];
						$CostOverrunsNCGCompetition->save();
						break;
					case 'direcciondeconcurso':
						$CostOverrunsNCGCompetition->direcciondeconcurso = $s[2];
						$CostOverrunsNCGCompetition->save();
						break;
					default:
						break;
				}
			}

			if ($name == 'DATOS DE LA OBRA') {
				switch ($s[0]) {
					case 'nombredelaobra':
						$CostOverrunsNCGConstruction = CostOverrunsNCGConstruction::create([
							'idUpload' => $bgup->id,
							'nombredelaobra' => $s[2],
						]);
						break;
					case 'direcciondelaobra':
						$CostOverrunsNCGConstruction->direcciondelaobra = $s[2];
						$CostOverrunsNCGConstruction->save();
						break;
					case 'coloniadelaobra':
						$CostOverrunsNCGConstruction->coloniadelaobra = $s[2];
						$CostOverrunsNCGConstruction->save();
						break;
					case 'ciudaddelaobra':
						$CostOverrunsNCGConstruction->ciudaddelaobra = $s[2];
						$CostOverrunsNCGConstruction->save();
						break;
					case 'estadodelaobra':
						$CostOverrunsNCGConstruction->estadodelaobra = $s[2];
						$CostOverrunsNCGConstruction->save();
						break;
					case 'codigopostaldelaobra':
						$CostOverrunsNCGConstruction->codigopostaldelaobra = $s[2];
						$CostOverrunsNCGConstruction->save();
						break;
					case 'telefonodelaobra':
						$CostOverrunsNCGConstruction->telefonodelaobra = $s[2];
						$CostOverrunsNCGConstruction->save();
						break;
					case 'emaildelaobra':
						$CostOverrunsNCGConstruction->emaildelaobra = $s[2];
						$CostOverrunsNCGConstruction->save();
						break;
					case 'responsabledelaobra':
						$CostOverrunsNCGConstruction->responsabledelaobra = $s[2];
						$CostOverrunsNCGConstruction->save();
						break;
					case 'cargoresponsabledelaobra':
						$CostOverrunsNCGConstruction->cargoresponsabledelaobra = $s[2];
						$CostOverrunsNCGConstruction->save();
						break;
					case 'fechainicio':
						$CostOverrunsNCGConstruction->fechainicio = empty($s[2]) ? $s[2] : $s[2]->format('Y-m-d');
						$CostOverrunsNCGConstruction->save();
						break;
					case 'fechaterminacion':
						$CostOverrunsNCGConstruction->fechaterminacion = empty($s[2]) ? $s[2] : $s[2]->format('Y-m-d');
						$CostOverrunsNCGConstruction->save();
						break;
					case 'totalpresupuestoprimeramoneda':
						$CostOverrunsNCGConstruction->totalpresupuestoprimeramoneda = $s[2];
						$CostOverrunsNCGConstruction->save();
						break;
					case 'totalpresupuestosegundamoneda':
						$CostOverrunsNCGConstruction->totalpresupuestosegundamoneda = $s[2];
						$CostOverrunsNCGConstruction->save();
						break;
					case 'porcentajeivapresupuesto':
						$CostOverrunsNCGConstruction->porcentajeivapresupuesto = $s[2] * 100;
						$CostOverrunsNCGConstruction->save();
						break;

					default:
						break;
				}
			}

			if ($name == 'DATOS ENCABEZADO') {
				switch ($s[0]) {
					case 'plazocalculado':
						$CostOverrunsNCGHeader = CostOverrunsNCGHeader::create([
							'idUpload' => $bgup->id,
							'plazocalculado' => $s[2],
						]);
						break;
					case 'plazoreal':
						$CostOverrunsNCGHeader->plazoreal = $s[2];
						$CostOverrunsNCGHeader->save();
						break;
					case 'decimalesredondeo':
						$CostOverrunsNCGHeader->decimalesredondeo = $s[2];
						$CostOverrunsNCGHeader->save();
						break;
					case 'primeramoneda':
						$CostOverrunsNCGHeader->primeramoneda = $s[2];
						$CostOverrunsNCGHeader->save();
						break;
					case 'segundamoneda':
						$CostOverrunsNCGHeader->segundamoneda = $s[2];
						$CostOverrunsNCGHeader->save();
						break;
					case 'remateprimeramoneda':
						$CostOverrunsNCGHeader->remateprimeramoneda = $s[2];
						$CostOverrunsNCGHeader->save();
						break;
					case 'rematesegundamoneda':
						$CostOverrunsNCGHeader->rematesegundamoneda = $s[2];
						$CostOverrunsNCGHeader->save();
						break;
					default:
						break;
				}
			}

			if ($name == 'DATOS DE LA CONVOCATORIA') {
				switch ($s[0]) {
					case 'numconvocatoria':
						$CostOverrunsNCGAnnouncement = CostOverrunsNCGAnnouncement::create([
							'idUpload' => $bgup->id,
							'numconvocatoria' => $s[2],
						]);
						break;
					case 'fechaconvocatoria':
						$CostOverrunsNCGAnnouncement->fechaconvocatoria = empty($s[2]) ? $s[2] : $s[2]->format('Y-m-d');
						$CostOverrunsNCGAnnouncement->save();
						break;
					case 'tipodelicitacion':
						$CostOverrunsNCGAnnouncement->tipodelicitacion = $s[2];
						$CostOverrunsNCGAnnouncement->save();
						break;

					default:
						break;
				}
			}
		}
	}

	public function saveDatosObra($sheet, $bgup)
	{

		$name = '';
		$COCValues;
		$COCValuesThatApply;
		$COCRequiredValues;
		$COCAnAdvance;
		$COCAdvanceTypeTable;

		foreach ($sheet->toArray() as $s) {
			switch (strval($s[1])) {
				case 'VALORES DE LA OBRA':
					$name = 'VALORES DE LA OBRA';
					break;
				case 'ELIJA LOS VALORES QUE APLICAN ':
					$name = 'ELIJA LOS VALORES QUE APLICAN ';
					break;
				case 'ESCRIBA LOS VALORES REQUERIDOS':
					$name = 'ESCRIBA LOS VALORES REQUERIDOS';
					break;
				case 'PARA OBRAS EN UN EJERCICIO PRESUPUESTAL CON UN ANTICIPO':
					$name = 'PARA OBRAS EN UN EJERCICIO PRESUPUESTAL CON UN ANTICIPO';
					break;
				case 'ANTICIPO EN UN EJERCICIO PRESUPUESTAL CON DOS ANTICIPOS':
					$name = 'ANTICIPO EN UN EJERCICIO PRESUPUESTAL CON DOS ANTICIPOS';
					break;
				case 'PARA OBRAS QUE REBASEN UN EJERCICIO PRESUPUESTAL':
					$name = 'PARA OBRAS QUE REBASEN UN EJERCICIO PRESUPUESTAL';
					break;
				case 'TABLA DE DATOS DE ACUERDO A LA ELECCION DEL TIPO DE ANTICIPO':
					$name = 'TABLA DE DATOS DE ACUERDO A LA ELECCION DEL TIPO DE ANTICIPO';
					break;
				case 'TABLA DE DATOS DE ACUERDO AL COBRO PRIMERA ESTIMACION Y ENTREGA 2do. ANTICIPO':
					$name = 'TABLA DE DATOS DE ACUERDO AL COBRO PRIMERA ESTIMACION Y ENTREGA 2do. ANTICIPO';
					break;
				case 'ANTICIPOS':
					$name = 'ANTICIPOS';
					break;
				default:
					# code...
					break;
			}

			if ($name == 'VALORES DE LA OBRA') {
				switch ($s[3]) {
					case 'COSTO DIRECTO DE LA OBRA :':
						$COCValues = COCValues::create([
							'idUpload' => $bgup->id,
							'costodirectodelaobra' => $s[4],
						]);
						break;
					case 'IMPORTE TOTAL DE LA MANO DE OBRA GRAVABLE :':
						$COCValues->importetotaldelamanodeobragravable = $s[4];
						$COCValues->save();
						break;
					case 'IMPORTE TOTAL DE LA OBRA:':
						$COCValues->importetotaldelaobra = $s[4];
						$COCValues->save();
						break;
					case 'FACTOR PARA LA OBTENCIÓN DE LA SFP:':
						$COCValues->factorparalaobtenciondelasfp = $s[4];
						$COCValues->save();
						break;
					case 'PORCENTAJE DE UTILIDAD BRUTA PROPUESTA:':
						$COCValues->porcentajedeutilidadbrutapropuesta = $s[4];
						$COCValues->save();
						break;
					case 'TASA DE INTERÉS USADA:':
						$COCValues->tasadeinteresusada = $s[4];
						$COCValues->save();
						break;
					case 'PUNTOS DEL BANCO:':
						$COCValues->puntosdelbanco = $s[4];
						$COCValues->save();
						break;
					case 'INDICADOR ECONÓMICO DE REFERENCIA:':
						$COCValues->indicadoreconomicodereferencia = $s[4];
						$COCValues->save();
						break;
					case 'ISR (Impuesto Sobre la Renta):':
						$COCValues->isr = $s[4];
						$COCValues->save();
						break;
					case 'PTU (Participacion de trabajadores en la utilidad):':
						$COCValues->ptu = $s[4];
						$COCValues->save();
						break;

					default:
						# code...
						break;
				}
			}
			if ($name == 'ELIJA LOS VALORES QUE APLICAN ') {
				switch ($s[3]) {
					case 'Un ejercicio con un Anticipo':
						$COCValuesThatApply = COCValuesThatApply::create([
							'idUpload' => $bgup->id,
							'tipodeanticipo' => $s[4],
						]);
						break;
					case 'Importe Total de Obra':
						$COCValuesThatApply->modelodecalculodelfinanciamiento = $s[4];
						$COCValuesThatApply->save();
						break;
					default:
						# code...
						break;
				}
				if ($s[2] == 'Solo intereses negativos') {
					$COCValuesThatApply->interesesaconsiderarenelfinanciamiento = $s[3];
					$COCValuesThatApply->tasaactiva = $s[5];
					$COCValuesThatApply->save();
				}
				if ($s[2] == 'Sobre el Importe de Estimaciones') {
					$COCValuesThatApply->calculodelcargoadicional = $s[3];
					$COCValuesThatApply->diasaconsiderarenelaño = $s[5];
					$COCValuesThatApply->save();
				}
			}
			if ($name == 'ESCRIBA LOS VALORES REQUERIDOS') {
				switch ($s[3]) {
					case 'ANTICIPO A PROVEEDORES AL INICIO DE OBRA:':
						$COCRequiredValues = COCRequiredValues::create([
							'idUpload' => $bgup->id,
							'anticipoaproveedoresaliniciodeobra' => $s[4],
						]);
						break;
					case 'PORCENTAJE DE IMPUESTO SOBRE NÓMINA:':
						$COCRequiredValues->porcentajedeimpuestosobrenomina = $s[4] * 100;
						$COCRequiredValues->save();
						break;
					case 'Presentación despues del corte:':
						$COCRequiredValues->presentaciondespuesdelcorte = $s[4];
						$COCRequiredValues->save();
						break;
					case 'Revisión y Autorización:':
						$COCRequiredValues->revisionyautorizacion = $s[4];
						$COCRequiredValues->save();
						break;
					case 'Dias para el pago:':
						$COCRequiredValues->diasparaelpago = $s[4];
						$COCRequiredValues->save();
						break;
					case 'PERIODO DE COBRO PRIMERA ESTIMACION:':
						$COCRequiredValues->periododecobroprimeraestimacion = $s[4];
						$COCRequiredValues->save();
						break;
					case 'PERIODO DE ENTREGA SEGUNDO ANTICIPO:':
						$COCRequiredValues->periododeentregasegundoanticipo = $s[4];
						$COCRequiredValues->save();
						break;
					case 'Redondeo para Programa de Personal Tecnico:':
						$COCRequiredValues->redondeoparaprogramadepersonaltecnico = $s[4];
						$COCRequiredValues->save();
						break;
					case 'No. de Personas':
						$COCRequiredValues->presentaciondelprogramadepersonaltecnico = $s[4];
						$COCRequiredValues->save();
						break;
					case 'horas Jornada':
						$COCRequiredValues->horasjornada = $s[4];
						$COCRequiredValues->save();
						break;
					default:
						# code...
						break;
				}
			}

			if ($name == 'PARA OBRAS EN UN EJERCICIO PRESUPUESTAL CON UN ANTICIPO') {
				if ($s[1] != 'NUMERO') {
					if (!empty($s[1]) && !empty($s[2])) {

						COCAnAdvance::create([
							'idUpload' => $bgup->id,
							'numero' => $s[1],
							'anticipos' => $s[2],
							'porcentaje' => $s[3] * 100,
						]);
					}
				}
			}
			if ($name == 'ANTICIPO EN UN EJERCICIO PRESUPUESTAL CON DOS ANTICIPOS') {
				if ($s[1] != 'NUMERO') {
					if (!empty($s[1]) && !empty($s[2])) {

						COConstructionTwoAdvance::create([
							'idUpload' => $bgup->id,
							'numero' => $s[1],
							'anticipos' => $s[2],
							'porcentaje' => $s[3] * 100,
							'periododeentrega' => $s[4],
						]);
					}
				}
			}
			if ($name == 'PARA OBRAS QUE REBASEN UN EJERCICIO PRESUPUESTAL') {
				if ($s[1] != 'NUMERO') {
					if (!empty($s[1]) && !empty($s[2])) {

						COConstructionBudgetExceed::create([
							'idUpload' => $bgup->id,
							'numero' => $s[1],
							'anticipos' => $s[2],
							'porcentaje' => $s[3] * 100,
							'importeaejercer' => $s[4],
							'importedeanticipo' => $s[5],
							'periododeentrega' => $s[6],
						]);
					}
				}
			}
			if ($name == 'TABLA DE DATOS DE ACUERDO A LA ELECCION DEL TIPO DE ANTICIPO') {
				switch ($s[2]) {
					case 'COSTO DIRECTO DE LA OBRA:':
						$COCAdvanceTypeTable = COCAdvanceTypeTable::create([
							'idUpload' => $bgup->id,
							'costodirectodelaobra' => $s[3],
						]);
						break;
					case 'INDIRECTO DE OBRA:':
						$COCAdvanceTypeTable->indirectodeobra = $s[3];
						$COCAdvanceTypeTable->save();
						break;
					case 'COSTO DIRECTO +INDIRECTO:':
						$COCAdvanceTypeTable->costodirectoindirecto = $s[3];
						$COCAdvanceTypeTable->save();
						break;
					case 'MONTO TOTAL DE LA OBRA:':
						$COCAdvanceTypeTable->montototaldelaobra = $s[3];
						$COCAdvanceTypeTable->save();
						break;
					case 'IMPORTE PARA FINANCIAMIENTO:':
						$COCAdvanceTypeTable->importeparafinanciamiento = $s[3];
						$COCAdvanceTypeTable->save();
						break;
					case 'IMPORTE EJERCER1:':
						$COCAdvanceTypeTable->importeejercer1 = $s[3];
						$COCAdvanceTypeTable->save();
						break;
					case 'IMPORTE EJERCER2:':
						$COCAdvanceTypeTable->importeejercer2 = $s[3];
						$COCAdvanceTypeTable->save();
						break;

					default:
						# code...
						break;
				}
			}
			if ($name == 'TABLA DE DATOS DE ACUERDO AL COBRO PRIMERA ESTIMACION Y ENTREGA 2do. ANTICIPO') {
				switch ($s[2]) {
					case 'PERIODOS PROGRAMADOS:':
						$COCSecondAdvanceTypeTable = COCSecondAdvanceTypeTable::create([
							'idUpload' => $bgup->id,
							'periodosprogramados' => $s[3],
						]);
						break;
					case 'PERIODO FINAL DE COBRO:':
						$COCSecondAdvanceTypeTable->periodofinaldecobro = $s[3];
						$COCSecondAdvanceTypeTable->save();
						break;
					case 'PERIODO DE AMORTIZACION 2do ANTICIPO:':
						$COCSecondAdvanceTypeTable->periododeamortizacion2doanticipo = $s[3];
						$COCSecondAdvanceTypeTable->save();
						break;

					default:
						# code...
						break;
				}
			}
			if ($name == 'ANTICIPOS') {

				if (!is_null($s[1]) && !is_null($s[2]) && !is_null($s[3])) {
					COConstructionAMIP::create([
						'idUpload' => $bgup->id,
						'anticipo1' => $s[1],
						'anticipo2' => $s[2],
						'monto1' => $s[3],
						'monto2' => $s[4],
						'importe1' => $s[5],
						'importe2' => $s[6],
						'periodo' => $s[7],
					]);
				}
			}
		}
	}

	public function saveDatosPrograma($sheet, $bgup)
	{

		//$SobreCostoProgramaGeneral;
		$COPeriodProgram;
		$search_p = false;
		foreach ($sheet->toArray() as $s) {

			// if($s[2] == '    FECHA DE INICIO:')
			// {
			// 	$d = \Carbon\Carbon::createFromFormat('d/m/Y', $s[3]);

			// 	$SobreCostoProgramaGeneral = App\SobreCostoProgramaGeneral::create([
			// 		'idUpload' => $bgup->id,
			// 		'fechadeinicio' =>  $d->format('Y-m-d'),
			// 		'ano' => $s[6],
			// 	]);
			// }
			// if($s[2] == '             FECHA DE TERMINACIÓN:')
			// {
			// 	$d = \Carbon\Carbon::createFromFormat('d/m/Y', $s[3]);

			// 	$SobreCostoProgramaGeneral->fechadeterminacion =  $d->format('Y-m-d');
			// 	$SobreCostoProgramaGeneral->diasdelano = $s[6];
			// 	$SobreCostoProgramaGeneral->save();
			// }
			// if($s[2] == 'PLAZO EN DIAS:')
			// {
			// 	$SobreCostoProgramaGeneral->plazoendias = $s[3];
			// 	$SobreCostoProgramaGeneral->save();
			// }

			// if($s[1] == 'PeriodoInicial')
			// {
			// 	$SobreCostoProgramaGeneral->periodoinicial = $s[2];
			// 	$SobreCostoProgramaGeneral->periodofinal = $s[4];
			// 	$SobreCostoProgramaGeneral->save();
			// }

			if ($s[1] == 'PROGRAMADO') {
				$search_p = true;
				continue;
			}

			if ($search_p) {
				if (!empty($s[1]) && !empty($s[2])) {

					$programado = COPeriodProgram::create([
						'idUpload' => $bgup->id,
						'programado' => $s[1],
						'titulo' => $s[2],
						'diasnaturales' => $s[3],
						'diastotales' => $s[4],
						'factorano' => $s[5],
						'ano' => $s[6],
						'importedelperiodo' => $s[7],
					]);

					COCostPeriodProgram::create([
						'idUpload' => $bgup->id,
						'idProgramado' => $programado->id,

						'costomateriales' => $s[9],
						'costomanodeobra' => $s[10],
						'costoequipo' => $s[11],
						'costootrosinsumos' => $s[12],
					]);
					COAdvanceProgram::create([
						'idUpload' => $bgup->id,
						'idProgramado' => $programado->id,

						'parcial' => $s[13] * 100,
						'acumulado' => $s[14] * 100,
					]);
				}
			}
		}
	}

	public function saveDatosPlantilla($sheet, $bgup)
	{

		$COGeneralTemplate;
		$search_campo = false;
		$campo;
		$central;
		$l_central;
		$l_campo;
		$campo_group = '';
		$central_group;
		$l_central_group;
		$l_campo_group;
		$search_central = false;
		$search_listado_campo = false;
		$search_listado_central = false;
		$l_central_group;
		$l_campo_group;
		foreach ($sheet->toArray() as $s) {
			if (strval($s[3]) == 'Duracion de la Obra (en dias)') {
				$COGeneralTemplate = COGeneralTemplate::create([
					'idUpload' => $bgup->id,
				]);
			}

			if (strval($s[0]) == 'Y ') {

				$COGeneralTemplate->factor1 = $s[6];
				$COGeneralTemplate->factor2 = $s[7];
				$COGeneralTemplate->porcentaje = $s[9] * 100;
				$COGeneralTemplate->save();
				SobrecostoPlantillaPOCampoGeneral::create([
					'idUpload' => $bgup->id,
					'montototal' => $s[1],
					'porcentaje' => $s[4] * 100,
				]);
			}

			if (strval($s[0]) == 'ADMINISTRATIVOS' &&  empty($campo)) {
				$search_campo = true;
				continue;
			}

			if ($search_campo)
			{
				if (!empty($s[1]) && !empty($s[2]) && !empty($s[3]) && !empty($s[4]) && !empty($s[6]))
				{
					if (strval($s[0]) != 'Incluye Prestaciones' && !empty($s[0]))
					{
						$campo_group = $s[0];
					}
					$c = COFieldStaffTemplate::create([
						'idUpload' => $bgup->id,
						'group' => ($s[0] != 'Incluye Prestaciones' && !empty($s[0])) ? $campo_group : '',
						'groupId' => (empty($s[0]) || $s[0] == 'Incluye Prestaciones') ? $campo->id : null,
						'category' => $s[1],
						'amount' => $s[2],
						'salary' => $s[3],
						'import' => $s[4],
						'factor1' => $s[6],
						'factor2' => $s[7],
					]);
					if ($c->group)
						$campo = $c;
				}
			}

			if (strval($s[0]) == 'ADMINISTRATIVOS' && $search_campo) {

				$search_campo = false;
				$search_central = true;
				COCentralStaffGeneralTemplate::create([
					'idUpload' => $bgup->id,
					'montototal' => $s[1],
					'porcentaje' => $s[4] * 100,
				]);
			}

			if ($search_central) {
				if (!empty($s[1]) && !empty($s[2]) && !empty($s[3]) && !empty($s[4]) && !empty($s[6])) {
					if (strval($s[0]) != 'Incluye Prestaciones' && !empty($s[0])) {
						$central_group = $s[0];
					}
					$c = COCentralStaffTemplate::create([
						'idUpload' => $bgup->id,

						'group' => ($s[0] != 'Incluye Prestaciones' && !empty($s[0])) ? $central_group : '',
						'groupId' => (empty($s[0]) || $s[0] == 'Incluye Prestaciones') ? $central->id : null,
						'category' => $s[1],
						'amount' => $s[2],
						'salary' => $s[3],
						'import' => $s[4],
						'factor1' => $s[6],
						'factor2' => $s[7],
					]);
					if ($c->group)
						$central = $c;
				}
			}

			if (strval($s[1]) == 'LISTADO DE PERSONAL DE CAMPO' && !$search_listado_campo) {
				$search_central = false;
				$search_listado_campo = true;
				continue;
			}
			if (strval($s[1]) == 'LISTADO DE PERSONAL DE OFICINA CENTRAL' && $search_listado_campo) {
				$search_listado_campo = false;
				$search_listado_central = true;
				continue;
			}

			if ($search_listado_campo) {
				if (!empty($s[0]) && strval($s[0]) != 'Administrativo') {
					$l_campo_group = $s[0];
					if ($l_campo_group == 'Personal ')
						$l_campo_group = 'Personal Administrativo';
				}
				if (!empty($s[1]) && !ctype_space($s[1])) {

					$c = COFieldStaffListTemplate::create([
						'idUpload' => $bgup->id,

						'group' => (!empty($s[0]) && strval($s[0]) != 'Administrativo') ? $l_campo_group : '',
						'groupId' => (empty($s[0]) || strval($s[0]) == 'Administrativo') ? $l_campo->id : null,
						'category' => $s[1],
					]);
					if ($c->group)
						$l_campo = $c;
				}
			}
			if ($search_listado_central) {
				if (!empty($s[0]) && strval($s[0]) != 'Administrativo') {
					$l_central_group = $s[0];
					if ($l_central_group == 'Personal ')
						$l_central_group = 'Personal Administrativo';
				}
				if (!empty($s[1]) && !ctype_space($s[1])) {
					$c = COCentralStaffListTemplate::create([
						'idUpload' => $bgup->id,
						'group' => (!empty($s[0]) && strval($s[0]) != 'Administrativo') ? $l_central_group : '',
						'groupId' => (empty($s[0]) || strval($s[0]) == 'Administrativo') ? $l_central->id : null,
						'category' => $s[1],
					]);
					if ($c->group)
						$l_central = $c;
				}
			}
		}
	}

	public function saveDatosBIndirectos($sheet, $bgup)
	{

		$search_concepts = false;
		$COIndirectItemizedGeneral;

		foreach ($sheet->toArray() as $s) {
			// if(strval($s[1]) == '                              Dependencia:')
			// {
			// 	$COIndirectItemizedGeneral =
			// 		App\COIndirectItemizedGeneral::create([
			// 			'idUpload' => $bgup->id,
			// 			'dependencia' => $s[2]
			// 		]);
			// 	continue;
			// }
			// if(strval($s[1]) == '                              Concurso No.')
			// {
			// 	$COIndirectItemizedGeneral->concurso = $s[2];
			// 	$COIndirectItemizedGeneral->fecha = $s[5];
			// 	$COIndirectItemizedGeneral->save();
			// 	continue;
			// }
			// if(strval($s[1]) == '                              Obra:')
			// {
			// 	$COIndirectItemizedGeneral->obra = $s[2];
			// 	$COIndirectItemizedGeneral->save();
			// 	continue;
			// }
			// if(strval($s[1]) == '                              Lugar:')
			// {
			// 	$COIndirectItemizedGeneral->lugar = $s[2];
			// 	$COIndirectItemizedGeneral->save();
			// 	continue;
			// }
			// if(strval($s[1]) == 'INICIO:')
			// {
			// 	$COIndirectItemizedGeneral->inicio = $s[2];
			// 	$COIndirectItemizedGeneral->terminacion = $s[4];
			// 	$COIndirectItemizedGeneral->plazo = $s[6];
			// 	$COIndirectItemizedGeneral->save();
			// 	continue;
			// }
			if (strval($s[5]) == 'MONTO DE LA OBRA A COSTO DIRECTO $ ') {
				$COIndirectItemizedGeneral =
					COIndirectItemizedGeneral::create([
						'idUpload' => $bgup->id,
						'montoobra' => $s[6]
					]);
				continue;
			}
			if (strval($s[4]) == 'MONTO') {
				$search_concepts = true;
				continue;
			}

			if ($search_concepts) {
				if (!empty($s[0]) || !empty($s[1])) {
					COIndirectItemizedConcept::create([
						'idUpload' => $bgup->id,
						'type' => 0,
						'concepto' => empty($s[0]) ? $s[1] : ($s[0] . ' ' . $s[1]),
						'monto1' => $s[4],
						'porcentaje1' => floatval($s[5])  * 100,
						'monto2' =>    $s[6],
						'porcentaje2' => floatval($s[7])  * 100,
					]);
					continue;
				}
				if ($s[3] == 'SUBTOTALES') {
					COIndirectItemizedConcept::create([
						'idUpload' => $bgup->id,
						'type' => 1,
						'concepto' => 'SUBTOTALES',
						'monto1' => $s[4],
						'porcentaje1' => floatval($s[5])  * 100,
						'monto2' =>    $s[6],
						'porcentaje2' =>    floatval($s[7])  * 100,
					]);
					continue;
				}
				if ($s[2] == 'T O T A L E S') {
					COIndirectItemizedConcept::create([
						'idUpload' => $bgup->id,
						'type' => 2,
						'concepto' => 'T O T A L E S',
						'monto1' => $s[4],
						'porcentaje1' => floatval($s[5]) * 100,
						'monto2' =>    $s[6],
						'porcentaje2' =>    floatval($s[7]) * 100,
					]);
					$search_concepts = false;
					continue;
				}
			}
			if (strval($s[4]) == 'TOTALES') {
				$COIndirectItemizedGeneral->totales = $s[5];
				$COIndirectItemizedGeneral->indirecto = floatval($s[7]) * 100;
				$COIndirectItemizedGeneral->save();
				continue;
			}
		}
	}
	public function saveDatosRIndirectos($sheet, $bgup)
	{

		$COSummaryGeneralIndirect;
		$search_concepts = false;

		foreach ($sheet->toArray() as $key => $s) {
			// if($key == 0)
			// {
			// 	$COSummaryGeneralIndirect = App\COSummaryGeneralIndirect::create([
			// 		'idUpload' => $bgup->id,
			// 		'razonsocial' => $s[0],
			// 	]);
			// 	continue;
			// }
			// if(strval($s[1]) == '                              Dependencia:')
			// {
			// 	$COSummaryGeneralIndirect->dependencia = $s[2];
			// 	$COSummaryGeneralIndirect->save();
			// 	continue;
			// }
			// if(strval($s[1]) == '                              Concurso No.')
			// {
			// 	$COSummaryGeneralIndirect->concurso = $s[2];
			// 	$COSummaryGeneralIndirect->fecha = $s[4];
			// 	$COSummaryGeneralIndirect->save();
			// 	continue;
			// }
			// if(strval($s[1]) == '                              Obra:')
			// {
			// 	$COSummaryGeneralIndirect->obra = $s[2];
			// 	$COSummaryGeneralIndirect->save();
			// 	continue;
			// }
			// if(strval($s[1]) == '                              Lugar:')
			// {
			// 	$COSummaryGeneralIndirect->lugar = $s[2];
			// 	$COSummaryGeneralIndirect->save();
			// 	continue;
			// }
			// if(strval($s[1]) == 'INICIO:')
			// {
			// 	$COSummaryGeneralIndirect->inicio = $s[2];
			// 	$COSummaryGeneralIndirect->termina = $s[4];
			// 	$COSummaryGeneralIndirect->plazo = $s[6];
			// 	$COSummaryGeneralIndirect->save();
			// 	continue;
			// }
			if (strval($s[4]) == 'MONTO DE LA OBRA A COSTO DIRECTO :') {
				$COSummaryGeneralIndirect = COSummaryGeneralIndirect::create([
					'idUpload' => $bgup->id,
					'montoobra' => $s[5],
				]);
				continue;
			}
			if (strval($s[3]) == 'MONTO') {
				$search_concepts = true;
				continue;
			}
			if ($search_concepts) {
				if (!empty($s[0])) {
					COSummaryIndirectConcept::create([
						'idUpload' => $bgup->id,
						'type' => 0,
						'concepto' => $s[0] . $s[1],
						'monto1' => $s[3],
						'porcentaje1' => floatval($s[4]) * 100,
						'monto2' => $s[5],
						'porcentaje2' => floatval($s[6]) * 100,
						'montototal' => $s[7],
						'porcentajetotal' => floatval($s[8]) * 100,
					]);
					continue;
				}
			}
			if (strval($s[2]) == 'T O T A L E S') {
				$search_concepts = false;
				COSummaryIndirectConcept::create([
					'idUpload' => $bgup->id,
					'type' => 2,
					'concepto' => 'T O T A L E S',
					'monto1' => $s[3],
					'porcentaje1' => floatval($s[4]) * 100,
					'monto2' => $s[5],
					'porcentaje2' => floatval($s[6]) * 100,
					'montototal' => $s[7],
					'porcentajetotal' => floatval($s[8]) * 100,
				]);
				continue;
			}
			if (strval($s[3]) == 'TOTALES') {
				$COSummaryGeneralIndirect->totales = $s[4];
				$COSummaryGeneralIndirect->indirecto = $s[8] * 100;
				$COSummaryGeneralIndirect->save();
			}
		}
	}
	public function saveDatosDPersTecnico($sheet, $bgup)
	{

		$prev_row;
		$anos;
		$meses;
		//$SobrecostoDPersTecnicoGeneral;
		$search_concepts = false;
		$parent;
		$count_anos = 0;
		// convertir fechas
		foreach ($sheet->toArray() as $key => $s) {

			// if(strval($s[0]) == '              Dependencia:')
			// {
			// 	$SobrecostoDPersTecnicoGeneral = App\SobrecostoDPersTecnicoGeneral::create([
			// 		'idUpload' => $bgup->id,
			// 		'dependencia' => $s[1],
			// 	]);
			// 	continue;
			// }
			// if(strval($s[0]) == '              Concurso No.')
			// {
			// 	$SobrecostoDPersTecnicoGeneral->concurso = $s[1];
			// 	$d = \Carbon\Carbon::createFromFormat('d/m/Y', $s[5]);
			// 	$SobrecostoDPersTecnicoGeneral->fecha = $d;
			// 	$SobrecostoDPersTecnicoGeneral->save();
			// 	continue;
			// }
			// if(strval($s[0]) == '              Obra:')
			// {
			// 	$SobrecostoDPersTecnicoGeneral->obra = $s[1];
			// 	$SobrecostoDPersTecnicoGeneral->save();
			// 	continue;
			// }
			// if(strval($s[0]) == '              Lugar:')
			// {
			// 	$SobrecostoDPersTecnicoGeneral->lugar = $s[1];

			// 	$inicio = \Carbon\Carbon::createFromFormat('d/m/Y', $s[11]);
			// 	$termina = \Carbon\Carbon::createFromFormat('d/m/Y', $s[13]);
			// 	$SobrecostoDPersTecnicoGeneral->inicio = $inicio;
			// 	$SobrecostoDPersTecnicoGeneral->termina = $termina;
			// 	$SobrecostoDPersTecnicoGeneral->duracion = $s[15];
			// 	$SobrecostoDPersTecnicoGeneral->save();
			// 	$search_social = true;
			// 	continue;
			// }
			if (strval($s[0]) == 'RAZON SOCIAL DEL LICITANTE') {
				// $SobrecostoDPersTecnicoGeneral->razonsocial = $prev_row[0];
				// $SobrecostoDPersTecnicoGeneral->representante = $prev_row[6];
				// $SobrecostoDPersTecnicoGeneral->save();
				$search_social = true;
				continue;
			}


			if (strval($s[0]) == 'AREA DE TRABAJO') {
				$anos = $prev_row;
				$meses = $s;
				$search_concepts = true;
				for ($i = 0; $i < count($meses); $i++) {
					if (!strpos(strval($meses[$i]), date('y'))) {
						$count_anos++;
					}
				}
				continue;
			}

			if ($search_concepts) {

				if (!empty(strval($s[0])) && !strpos($s[0], 'Prestaciones') && !ctype_space($s[0]))
				{
					$parent = COTechnicalStaffConcept::create([
						'idUpload' => $bgup->id,
						'category' => $s[0],
					]);
					continue;
				}
				if (!empty(strval($s[0])) && !ctype_space($s[0]))
				{
					$parent = COTechnicalStaffConcept::create([
						'idUpload' => $bgup->id,
						'parent' => $parent->father ? $parent->father->id : $parent->id,
						'category' => $s[0],
					]);
					continue;
				}

				if (strlen(strval($s[1])) > 0)
				{
					$c = COTechnicalStaffConcept::create([
						'idUpload' => $bgup->id,
						'parent' => $parent->id,
						'category' => $s[1],
						'measurement' => $s[2],
						'total' => $s[3]
					]);

					for ($i = 4; $i < $count_anos; $i++) {
						COTechnicalStaffYear::create([
							'idUpload' => $bgup->id,
							'idConcept' => $c->id,
							'mes' => $meses[$i],
							'ano' => $anos[$i],
							'amount' => $s[$i]
						]);
					}


					continue;
				}
			}

			$prev_row = $s;
		}
	}
	public function saveDatosDPersTecnicoSalarios($sheet, $bgup)
	{

		$prev_row;
		$anos;
		$meses;
		//$SobrecostoDPersTecnicoSalarioGeneral;
		$search_concepts = false;
		$parent;
		$count_anos = 0;

		foreach ($sheet->toArray() as $key => $s) {
			// if(strval($s[3]) == '              Dependencia:')
			// {

			// 	$SobrecostoDPersTecnicoSalarioGeneral = App\SobrecostoDPersTecnicoSalarioGeneral::create([
			// 		'idUpload' => $bgup->id,
			// 		'dependencia' => $s[4],
			// 	]);
			// 	continue;
			// }
			// if(strval($s[3]) == '              Concurso No.')
			// {
			// 	$SobrecostoDPersTecnicoSalarioGeneral->concurso = $s[4];
			// 	$d = \Carbon\Carbon::createFromFormat('d/m/Y', $s[8]);
			// 	$SobrecostoDPersTecnicoSalarioGeneral->fecha = $d;
			// 	$SobrecostoDPersTecnicoSalarioGeneral->save();
			// 	continue;
			// }
			// if(strval($s[3]) == '              Obra:')
			// {
			// 	$SobrecostoDPersTecnicoSalarioGeneral->obra = $s[4];
			// 	$SobrecostoDPersTecnicoSalarioGeneral->save();
			// 	continue;
			// }
			// if(strval($s[3]) == '              Lugar:')
			// {
			// 	$SobrecostoDPersTecnicoSalarioGeneral->lugar = $s[4];

			// 	$inicio = \Carbon\Carbon::createFromFormat('d/m/Y', $s[12]);
			// 	$termina = \Carbon\Carbon::createFromFormat('d/m/Y', $s[13]);
			// 	$SobrecostoDPersTecnicoSalarioGeneral->inicio = $inicio;
			// 	$SobrecostoDPersTecnicoSalarioGeneral->termina = $termina;
			// 	$SobrecostoDPersTecnicoSalarioGeneral->duracion = $s[14];
			// 	$SobrecostoDPersTecnicoSalarioGeneral->save();
			// 	$search_social = true;
			// 	continue;
			// }
			// if(strval($s[0]) == 'RAZON SOCIAL DEL LICITANTE')
			// {
			// 	$SobrecostoDPersTecnicoSalarioGeneral->razonsocial = $prev_row[0];
			// 	$SobrecostoDPersTecnicoSalarioGeneral->representante = $prev_row[7];
			// 	$SobrecostoDPersTecnicoSalarioGeneral->save();
			// 	$search_social = true;
			// 	continue;
			// }


			if (strval($s[0]) == 'AREA DE TRABAJO') {
				$anos = $prev_row;
				$meses = $s;
				$search_concepts = true;
				for ($i = 0; $i < count($meses); $i++) {
					if (!strpos(strval($meses[$i]), date('y'))) {
						$count_anos++;
					}
				}
				continue;
			}

			if ($search_concepts) {

				if (!empty(strval($s[0])) && !strpos($s[0], 'Prestaciones') && !ctype_space($s[0])) {
					$parent = COTechnicalStaffSalaryConcept::create([
						'idUpload' => $bgup->id,
						'category' => $s[0],
						'type' => 0,
					]);
					continue;
				}
				if (!empty(strval($s[0])) && !ctype_space($s[0])) {
					$parent = COTechnicalStaffSalaryConcept::create([
						'idUpload' => $bgup->id,
						'parent' => $parent->father ? $parent->father->id : $parent->id,
						'category' => $s[0],
						'type' => 1,
					]);
					continue;
				}

				if (strlen(strval($s[1])) > 2) {

					$c = COTechnicalStaffSalaryConcept::create([
						'idUpload' => $bgup->id,
						'type' => 2,
						'parent' => $parent->id,
						'category' => $s[1],
						'measurement' => $s[2],
						'amount' => $s[3],
						'salary' => $s[4],
						'import' => $s[5],
					]);

					for ($i = 6; $i < $count_anos; $i++) {
						COTechnicalStaffYearSalary::create([
							'idUpload' => $bgup->id,
							'idConcept' => $c->id,
							'mes' => $meses[$i],
							'ano' => $anos[$i],
							'amount' => $s[$i]
						]);
					}


					continue;
				}

				if (strval($s[4]) == 'Subtotal') {
					COTechnicalStaffSalaryConcept::create([
						'parent' => $parent->father ? $parent->father->id : $parent->id,
						'idUpload' => $bgup->id,
						'import' => $s[5],
						'type' => 3
					]);
					continue;
				}
				if (strval($s[4]) == 'Subtotal por periodo') {
					$c = COTechnicalStaffSalaryConcept::create([
						'parent' => $parent->father ? $parent->father->id : $parent->id,
						'idUpload' => $bgup->id,
						'import' => $s[5],
						'type' => 4
					]);
					for ($i = 6; $i < $count_anos; $i++) {
						COTechnicalStaffYearSalary::create([
							'idUpload' => $bgup->id,
							'idConcept' => $c->id,
							'mes' => $meses[$i],
							'ano' => $anos[$i],
							'amount' => $s[$i]
						]);
					}
					continue;
				}
				if (strval($s[4]) == 'Subtotal acumulado') {
					$c = COTechnicalStaffSalaryConcept::create([
						'parent' => $parent->father ? $parent->father->id : $parent->id,
						'idUpload' => $bgup->id,
						'import' => $s[5],
						'type' => 5
					]);
					for ($i = 6; $i < $count_anos; $i++) {
						COTechnicalStaffYearSalary::create([
							'idUpload' => $bgup->id,
							'idConcept' => $c->id,
							'mes' => $meses[$i],
							'ano' => $anos[$i],
							'amount' => $s[$i]
						]);
					}
					continue;
				}
			}

			$prev_row = $s;
		}
	}
	public function saveDatosFinanc($sheet, $bgup)
	{

		$count_c = false;
		$count_meses = 0;
		$search_ingresos = false;
		$count_periodos = 0;
		$total_i = 0;
		$concept;
		$count_ingresos = 0;
		$count_egresos = 0;
		$meses;
		$parent;
		$parent_egresos;
		$prev_row;
		$search_egresos = false;
		$COGeneralFinancial;
		foreach ($sheet->toArray() as $key => $s) {
			if (strval($s[1]) == 'INDICADOR ECONOMICO DE REFERENCIA :') {
				$COGeneralFinancial = COGeneralFinancial::create([
					'idUpload' => $bgup->id,
					'indicadoreconomicodereferencia' => $s[2] * 100,
					'tasadeinteresdiaria' => $s[6] * 100,
				]);
				continue;
			}
			if (strval($s[1]) == 'PUNTOS DE INTERMEDIACIÓN DE LA BANCA :') {
				$COGeneralFinancial->puntosdeintermediaciondelabanca =    $s[2] * 100;
				$COGeneralFinancial->diasparapagodeestimaciones =    $s[6];
				$COGeneralFinancial->save();
				continue;
			}

			if (strval($s[5]) == '% APLICABLE AL PERIODO :') {
				$COGeneralFinancial->aplicablealperiodo =    $s[6] * 100;
				$COGeneralFinancial->save();
				continue;
			}
			if (strval($s[1]) == 'PORCENTAJE DE FINANCIEAMIENTO=') {
				$COGeneralFinancial->porcentajedefinancieamiento =    $s[8] * 100;
				$COGeneralFinancial->save();
				continue;
			}


			if (strval($s[1]) == 'CONCEPTO') {
				$count_c = true;
				$count_meses++;
				continue;
			}
			if ($count_meses <= 2 && $count_c) {
				$count_meses++;
				continue;
			}
			if ($count_meses == 3) {
				$count_meses++;
				$count_c = false;
				$meses = $s;
				$search_ingresos = true;

				for ($i = 0; $i < count($meses); $i++) {
					$r = strlen($meses[$i]) > 2 ? $meses[$i] : null;
					if ($r != 'TOTAL' && !is_null($r)) {
						$count_periodos++;
					}
					if ($r == 'TOTAL')
						$total_i = $i;
				}
				continue;
			}

			if (!empty(strval($s[1])) && $search_ingresos) {
				if ($count_ingresos <= 5) {

					$c = COFinancialConcept::create([
						'idUpload' => $bgup->id,
						'parent' => !empty($parent) ? $parent->id : null,
						'concept' => $s[1],
					]);
					if ($count_ingresos == 0) {
						$parent = $c;
					} else {
						for ($i = 2; $i <= $count_periodos + 1; $i++) {
							COFinancialMonth::create([
								'idUpload' => $bgup->id,
								'idConcept' => $c->id,
								'mes' => $meses[$i],
								'amount' => $s[$i],
							]);
						}
					}

					$count_ingresos++;
					continue;
				} else {
					$search_ingresos = false;
				}
			}

			if (!empty(strval($s[1])) && strval($s[1]) == 'EGRESOS') {
				$search_egresos = true;
			}
			if ($count_egresos <= 9 && $search_egresos) {

				if ((strlen(strval($s[2])) > 0 || $count_egresos == 0) && ($count_egresos != 6 && $count_egresos != 7)) {

					$c = COFinancialConcept::create([
						'idUpload' => $bgup->id,
						'parent' => !empty($parent_egresos) ? $parent_egresos->id : null,
						'concept' => $s[1],
					]);
				}
				if ($count_egresos == 7) {
					$c = COFinancialConcept::create([
						'idUpload' => $bgup->id,
						'parent' => !empty($parent_egresos) ? $parent_egresos->id : null,
						'concept' => $prev_row[1],
					]);
				}
				if ($count_egresos == 0) {
					$parent_egresos = $c;
				} else {
					if ($count_egresos != 6) {
						for ($i = 2; $i <= $count_periodos + 1; $i++) {
							COFinancialMonth::create([
								'idUpload' => $bgup->id,
								'idConcept' => $c->id,
								'mes' => $meses[$i],
								'amount' => $s[$i],
							]);
						}
					}
				}

				$count_egresos++;
				$prev_row = $s;
				continue;
			}
		}
	}
	public function saveDatosUtilidad($sheet, $bgup)
	{

		$search_concepts = false;
		$end = false;
		foreach ($sheet->toArray() as $key => $s) {

			if (strval($s[0]) == 'CLAVE') {
				$search_concepts = true;
				continue;
			}

			if (strval(!empty($s[1])) && ctype_space($s[1])) {
				$end = true;
			}

			if (($search_concepts && !$end) && (
				(strval(!empty($s[1])) && !ctype_space($s[1]))
				||
				(strval(!empty($s[3])) || strval(!empty($s[4]))))) {



				CODeterminationUtility::create([
					'idUpload' => $bgup->id,
					'clave' => $s[0],
					'concepto' => $s[1],
					'formula' => $s[2],
					'importe' => $s[3],
					'porcentaje' => floatval($s[4]) * 100,
				]);
			}
		}
	}
	public function saveDatosCargosAdicionales($sheet, $bgup)
	{

		$search_concepts = false;
		$end = false;
		foreach ($sheet->toArray() as $key => $s) {

			if (strval($s[0]) == 'CLAVE') {
				$search_concepts = true;
				continue;
			}


			if (($search_concepts && strval(!empty($s[1])) && !$end && !ctype_space($s[1])) || (strval($s[2]) == 'TOTAL DE CARGOS ADICIONALES'))
			{
				COAdditionalCharges::create([
					'idUpload' => $bgup->id,
					'clave' => $s[0],
					'concepto' => $s[1],
					'formula' => $s[2],
					'importe' => $s[3],
					'porcentaje' => $s[4] * 100,
				]);
				if (strval($s[2]) == 'TOTAL DE CARGOS ADICIONALES')
				{
					$end = true;
				}
			}
		}
	}
	public function saveDatosResumen($sheet, $bgup)
	{

		$search_concepts = false;

		foreach ($sheet->toArray() as $key => $s) {

			if (strval($s[1]) == 'CLAVE') {
				$search_concepts = true;
				continue;
			}


			if ($search_concepts && strval(!empty($s[2]))) {
				COSummaryConcept::create([
					'idUpload' => $bgup->id,
					'clave' => $s[1],
					'concepto' => $s[2],
					'importe' => $s[4],
					'porcentaje' => $s[5] * 100,
				]);
			}

			if ($search_concepts && strval($s[4]) == 'FACTOR DE SOBRECOSTO') {
				COSummaryConcept::create([
					'idUpload' => $bgup->id,
					'concepto' => $s[4],
					'importe' => $s[5],
				]);
			}
			if ($search_concepts && strval($s[4]) == 'PORCENTAJE') {
				COSummaryConcept::create([
					'idUpload' => $bgup->id,
					'concepto' => $s[4],
					'porcentaje' => $s[5] * 100,
				]);
			}
		}
	}

	public function saveDatosDocumentacion($sheet, $bgup)
	{

		$count_empresas = 0;
		$empresas_id = [];

		foreach ($sheet->toArray() as $key => $s) {
			if (strval($s[1]) == 'PARAMETRO GENERAL') {
				$count_empresas = count($s);

				for ($i = 3; $i < $count_empresas; $i++) {
					$id = COEnterpriseDocument::create([
						'idUpload' => $bgup->id,
						'name' => $s[$i],
					]);
					array_push($empresas_id, $id->id);
				}
				continue;
			}
			if (strval($s[2]) == 'Un ejercicio con un Anticipo') {

				for ($i = 3; $i < $count_empresas; $i++) {

					COAdvanceDocumentation::create([
						'idUpload' => $bgup->id,
						'idDocEmpresa' => $empresas_id[$i - 3],
					]);
					if (strlen(strval($s[$i])) > 0) {
						COAdvanceDocumentation::where('idDocEmpresa', $empresas_id[$i - 3])->update([
							'unanticipo' => 1,
						]);
					}
				}
				continue;
			}
			if (strval($s[2]) == 'Un ejercicio con 2 anticipos') {

				for ($i = 3; $i < $count_empresas; $i++) {
					if (strlen(strval($s[$i])) > 0) {
						COAdvanceDocumentation::where('idDocEmpresa', $empresas_id[$i - 3])->update([
							'dosanticipo' => 1,
						]);
					}
				}
				continue;
			}
			if (strval($s[2]) == 'Rebasa un Ejercicio presupuestal') {

				for ($i = 3; $i < $count_empresas; $i++) {
					if (strlen(strval($s[$i])) > 0) {
						COAdvanceDocumentation::where('idDocEmpresa', $empresas_id[$i - 3])->update([
							'rebasa' => 1,
						]);
					}
				}
				continue;
			}


			if (strval($s[2]) == 'Importe Total de Obra') {

				for ($i = 3; $i < $count_empresas; $i++) {

					COFinancingCalcDocument::create([
						'idUpload' => $bgup->id,
						'idDocEmpresa' => $empresas_id[$i - 3],
					]);
					if (strlen(strval($s[$i])) > 0) {
						COFinancingCalcDocument::where('idDocEmpresa', $empresas_id[$i - 3])->update([
							'importetotal' => 1,
						]);
					}
				}
				continue;
			}
			if (strval($s[2]) == 'Costo Directo+Indirecto') {

				for ($i = 3; $i < $count_empresas; $i++) {
					if (strlen(strval($s[$i])) > 0) {
						COFinancingCalcDocument::where('idDocEmpresa', $empresas_id[$i - 3])->update([
							'importetotal' => 1,
						]);
					}
				}
				continue;
			}


			if (strval($s[2]) == 'Solo intereses negativos') {

				for ($i = 3; $i < $count_empresas; $i++) {
					COInterestsToConsiderDocument::create([
						'idUpload' => $bgup->id,
						'idDocEmpresa' => $empresas_id[$i - 3],
					]);
					if (strlen(strval($s[$i])) > 0) {
						COInterestsToConsiderDocument::where('idDocEmpresa', $empresas_id[$i - 3])->update([
							'negativos' => 1,
						]);
					}
				}
				continue;
			}
			if (strval($s[2]) == 'Ambos Interes (+ y -)') {

				for ($i = 3; $i < $count_empresas; $i++) {
					if (strlen(strval($s[$i])) > 0) {
						COInterestsToConsiderDocument::where('idDocEmpresa', $empresas_id[$i - 3])->update([
							'ambos' => 1,
						]);
					}
				}
				continue;
			}
			if (strval($s[2]) == 'Tasa Activa') {

				for ($i = 3; $i < $count_empresas; $i++) {
					if (strlen(strval($s[$i])) > 0) {
						COInterestsToConsiderDocument::where('idDocEmpresa', $empresas_id[$i - 3])->update([
							'tasaactiva' => 1,
						]);
					}
				}
				continue;
			}
			if (strval($s[2]) == 'Tasa Pasiva') {

				for ($i = 3; $i < $count_empresas; $i++) {
					if (strlen(strval($s[$i])) > 0) {
						COInterestsToConsiderDocument::where('idDocEmpresa', $empresas_id[$i - 3])->update([
							'tasapasiva' => 1,
						]);
					}
				}
				continue;
			}

			if (strval($s[2]) == 'Sobre el Importe de Estimaciones') {

				for ($i = 3; $i < $count_empresas; $i++) {
					COAdditionalChargeCalcDocument::create([
						'idUpload' => $bgup->id,
						'idDocEmpresa' => $empresas_id[$i - 3],
					]);
					if (strlen(strval($s[$i])) > 0) {
						COAdditionalChargeCalcDocument::where('idDocEmpresa', $empresas_id[$i - 3])->update([
							'sobreelimporte' => 1,
						]);
					}
				}
				continue;
			}
			if (strval($s[2]) == 'Sobre el Costo directo de la Obra') {

				for ($i = 3; $i < $count_empresas; $i++) {
					if (strlen(strval($s[$i])) > 0) {
						COAdditionalChargeCalcDocument::where('idDocEmpresa', $empresas_id[$i - 3])->update([
							'costodirecto' => 1,
						]);
					}
				}
				continue;
			}


			if (strval($s[2]) == 'Año Fiscal (1 Ene al 31 Dic)') {

				for ($i = 3; $i < $count_empresas; $i++) {
					CODaysToConsiderDocument::create([
						'idUpload' => $bgup->id,
						'idDocEmpresa' => $empresas_id[$i - 3],
					]);
					if (strlen(strval($s[$i])) > 0) {
						CODaysToConsiderDocument::where('idDocEmpresa', $empresas_id[$i - 3])->update([
							'anofiscal' => 1,
						]);
					}
				}
				continue;
			}
			if (strval($s[2]) == 'Año Comercial (360 Dias)') {

				for ($i = 3; $i < $count_empresas; $i++) {
					if (strlen(strval($s[$i])) > 0) {
						CODaysToConsiderDocument::where('idDocEmpresa', $empresas_id[$i - 3])->update([
							'anocomercial' => 1,
						]);
					}
				}
				continue;
			}


			if (strval($s[2]) == 'CA= Sub / (1-0.005) - Sub') {

				for ($i = 3; $i < $count_empresas; $i++) {
					COThousandDocument::create([
						'idUpload' => $bgup->id,
						'idDocEmpresa' => $empresas_id[$i - 3],
					]);
					if (strlen(strval($s[$i])) > 0) {
						COThousandDocument::where('idDocEmpresa', $empresas_id[$i - 3])->update([
							'casub' => 1,
						]);
					}
				}
				continue;
			}
			if (strval($s[2]) == 'CA= CA1* Sub ')
			{
				for ($i = 3; $i < $count_empresas; $i++)
				{
					if (strlen(strval($s[$i])) > 0)
					{
						COThousandDocument::where('idDocEmpresa', $empresas_id[$i - 3])->update([
							'caca' => 1,
						]);
					}
				}
				continue;
			}
			if (strval($s[1]) == 'DIAS DE PAGO P/ESTIMACIONES')
			{
				for ($i = 3; $i < $count_empresas; $i++)
				{
					CODaysToPayDocument::create([
						'idUpload' => $bgup->id,
						'idDocEmpresa' => $empresas_id[$i - 3],
						'dias' => $s[$i],
					]);
				}
				continue;
			}
		}
	}
}
