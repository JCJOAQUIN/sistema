<?php

use Illuminate\Database\Seeder;

class CatUseVoucherSeeder extends Seeder
{
	public function run()
	{
		$uses = [
			['useVoucher' => 'G01', 'description' => 'Adquisición de mercancías.', 'physical' => 'Sí', 'moral' => 'Sí', 'validity_start' => '2022-01-01', 'validity_end' => NULL, 'tax_regime_receptor' => '601, 603, 606, 612, 620, 621, 622, 623, 624, 625,626', 'cfdi_3_3' => 1, 'cfdi_4_0' => 1],
			['useVoucher' => 'G02', 'description' => 'Devoluciones, descuentos o bonificaciones.', 'physical' => 'Sí', 'moral' => 'Sí', 'validity_start' => '2022-01-01', 'validity_end' => NULL, 'tax_regime_receptor' => '601, 603, 606, 612, 620, 621, 622, 623, 624, 625,626', 'cfdi_3_3' => 1, 'cfdi_4_0' => 1],
			['useVoucher' => 'G03', 'description' => 'Gastos en general.', 'physical' => 'Sí', 'moral' => 'Sí', 'validity_start' => '2022-01-01', 'validity_end' => NULL, 'tax_regime_receptor' => '601, 603, 606, 612, 620, 621, 622, 623, 624, 625, 626', 'cfdi_3_3' => 1, 'cfdi_4_0' => 1],
			['useVoucher' => 'I01', 'description' => 'Construcciones.', 'physical' => 'Sí', 'moral' => 'Sí', 'validity_start' => '2022-01-01', 'validity_end' => NULL, 'tax_regime_receptor' => '601, 603, 606, 612, 620, 621, 622, 623, 624, 625, 626', 'cfdi_3_3' => 1, 'cfdi_4_0' => 1],
			['useVoucher' => 'I02', 'description' => 'Mobiliario y equipo de oficina por inversiones.', 'physical' => 'Sí', 'moral' => 'Sí', 'validity_start' => '2022-01-01', 'validity_end' => NULL, 'tax_regime_receptor' => '601, 603, 606, 612, 620, 621, 622, 623, 624, 625, 626', 'cfdi_3_3' => 1, 'cfdi_4_0' => 1],
			['useVoucher' => 'I03', 'description' => 'Equipo de transporte.', 'physical' => 'Sí', 'moral' => 'Sí', 'validity_start' => '2022-01-01', 'validity_end' => NULL, 'tax_regime_receptor' => '601, 603, 606, 612, 620, 621, 622, 623, 624, 625, 626', 'cfdi_3_3' => 1, 'cfdi_4_0' => 1],
			['useVoucher' => 'I04', 'description' => 'Equipo de computo y accesorios.', 'physical' => 'Sí', 'moral' => 'Sí', 'validity_start' => '2022-01-01', 'validity_end' => NULL, 'tax_regime_receptor' => '601, 603, 606, 612, 620, 621, 622, 623, 624, 625, 626', 'cfdi_3_3' => 1, 'cfdi_4_0' => 1],
			['useVoucher' => 'I05', 'description' => 'Dados, troqueles, moldes, matrices y herramental.', 'physical' => 'Sí', 'moral' => 'Sí', 'validity_start' => '2022-01-01', 'validity_end' => NULL, 'tax_regime_receptor' => '601, 603, 606, 612, 620, 621, 622, 623, 624, 625, 626', 'cfdi_3_3' => 1, 'cfdi_4_0' => 1],
			['useVoucher' => 'I06', 'description' => 'Comunicaciones telefónicas.', 'physical' => 'Sí', 'moral' => 'Sí', 'validity_start' => '2022-01-01', 'validity_end' => NULL, 'tax_regime_receptor' => '601, 603, 606, 612, 620, 621, 622, 623, 624, 625, 626', 'cfdi_3_3' => 1, 'cfdi_4_0' => 1],
			['useVoucher' => 'I07', 'description' => 'Comunicaciones satelitales.', 'physical' => 'Sí', 'moral' => 'Sí', 'validity_start' => '2022-01-01', 'validity_end' => NULL, 'tax_regime_receptor' => '601, 603, 606, 612, 620, 621, 622, 623, 624, 625, 626', 'cfdi_3_3' => 1, 'cfdi_4_0' => 1],
			['useVoucher' => 'I08', 'description' => 'Otra maquinaria y equipo.', 'physical' => 'Sí', 'moral' => 'Sí', 'validity_start' => '2022-01-01', 'validity_end' => NULL, 'tax_regime_receptor' => '601, 603, 606, 612, 620, 621, 622, 623, 624, 625, 626', 'cfdi_3_3' => 1, 'cfdi_4_0' => 1],
			['useVoucher' => 'D01', 'description' => 'Honorarios médicos, dentales y gastos hospitalarios.', 'physical' => 'Sí', 'moral' => 'No', 'validity_start' => '2022-01-01', 'validity_end' => NULL, 'tax_regime_receptor' => '605, 606, 608, 611, 612, 614, 607, 615, 625', 'cfdi_3_3' => 1, 'cfdi_4_0' => 1],
			['useVoucher' => 'D02', 'description' => 'Gastos médicos por incapacidad o discapacidad.', 'physical' => 'Sí', 'moral' => 'No', 'validity_start' => '2022-01-01', 'validity_end' => NULL, 'tax_regime_receptor' => '605, 606, 608, 611, 612, 614, 607, 615, 625', 'cfdi_3_3' => 1, 'cfdi_4_0' => 1],
			['useVoucher' => 'D03', 'description' => 'Gastos funerales.', 'physical' => 'Sí', 'moral' => 'No', 'validity_start' => '2022-01-01', 'validity_end' => NULL, 'tax_regime_receptor' => '605, 606, 608, 611, 612, 614, 607, 615, 625', 'cfdi_3_3' => 1, 'cfdi_4_0' => 1],
			['useVoucher' => 'D04', 'description' => 'Donativos.', 'physical' => 'Sí', 'moral' => 'No', 'validity_start' => '2022-01-01', 'validity_end' => NULL, 'tax_regime_receptor' => '605, 606, 608, 611, 612, 614, 607, 615, 625', 'cfdi_3_3' => 1, 'cfdi_4_0' => 1],
			['useVoucher' => 'D05', 'description' => 'Intereses reales efectivamente pagados por créditos hipotecarios (casa habitación).', 'physical' => 'Sí', 'moral' => 'No', 'validity_start' => '2022-01-01', 'validity_end' => NULL, 'tax_regime_receptor' => '605, 606, 608, 611, 612, 614, 607, 615, 625', 'cfdi_3_3' => 1, 'cfdi_4_0' => 1],
			['useVoucher' => 'D06', 'description' => 'Aportaciones voluntarias al SAR.', 'physical' => 'Sí', 'moral' => 'No', 'validity_start' => '2022-01-01', 'validity_end' => NULL, 'tax_regime_receptor' => '605, 606, 608, 611, 612, 614, 607, 615, 625', 'cfdi_3_3' => 1, 'cfdi_4_0' => 1],
			['useVoucher' => 'D07', 'description' => 'Primas por seguros de gastos médicos.', 'physical' => 'Sí', 'moral' => 'No', 'validity_start' => '2022-01-01', 'validity_end' => NULL, 'tax_regime_receptor' => '605, 606, 608, 611, 612, 614, 607, 615, 625', 'cfdi_3_3' => 1, 'cfdi_4_0' => 1],
			['useVoucher' => 'D08', 'description' => 'Gastos de transportación escolar obligatoria.', 'physical' => 'Sí', 'moral' => 'No', 'validity_start' => '2022-01-01', 'validity_end' => NULL, 'tax_regime_receptor' => '605, 606, 608, 611, 612, 614, 607, 615, 625', 'cfdi_3_3' => 1, 'cfdi_4_0' => 1],
			['useVoucher' => 'D09', 'description' => 'Depósitos en cuentas para el ahorro, primas que tengan como base planes de pensiones.', 'physical' => 'Sí', 'moral' => 'No', 'validity_start' => '2022-01-01', 'validity_end' => NULL, 'tax_regime_receptor' => '605, 606, 608, 611, 612, 614, 607, 615, 625', 'cfdi_3_3' => 1, 'cfdi_4_0' => 1],
			['useVoucher' => 'D10', 'description' => 'Pagos por servicios educativos (colegiaturas).', 'physical' => 'Sí', 'moral' => 'No', 'validity_start' => '2022-01-01', 'validity_end' => NULL, 'tax_regime_receptor' => '605, 606, 608, 611, 612, 614, 607, 615, 625', 'cfdi_3_3' => 1, 'cfdi_4_0' => 1],
			['useVoucher' => 'S01', 'description' => 'Sin efectos fiscales.  ', 'physical' => 'Sí', 'moral' => 'Sí', 'validity_start' => '2022-01-01', 'validity_end' => NULL, 'tax_regime_receptor' => '601, 603, 605, 606, 608, 610, 611, 612, 614, 616, 620, 621, 622, 623, 624, 607, 615, 625, 626', 'cfdi_3_3' => 0, 'cfdi_4_0' => 1],
			['useVoucher' => 'CP01', 'description' => 'Pagos', 'physical' => 'Sí', 'moral' => 'Sí', 'validity_start' => '2022-01-01', 'validity_end' => NULL, 'tax_regime_receptor' => '601, 603, 605, 606, 608, 610, 611, 612, 614, 616, 620, 621, 622, 623, 624, 607, 615, 625, 626', 'cfdi_3_3' => 0, 'cfdi_4_0' => 1],
			['useVoucher' => 'CN01', 'description' => 'Nómina', 'physical' => 'Sí', 'moral' => 'No', 'validity_start' => '2022-01-01', 'validity_end' => NULL, 'tax_regime_receptor' => '605', 'cfdi_3_3' => 0, 'cfdi_4_0' => 1],
			['useVoucher' => 'P01', 'description' => 'Por definir', 'physical' => 'Sí', 'moral' => 'Sí', 'validity_start' => '2017-03-31', 'validity_end' => '2022-04-30', 'tax_regime_receptor' => NULL, 'cfdi_3_3' => 1, 'cfdi_4_0' => 0]
		];

		foreach ($uses as $use)
		{
			App\CatUseVoucher::create($use);
		}
	}
}
