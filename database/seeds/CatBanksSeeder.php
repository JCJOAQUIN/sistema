<?php

use Illuminate\Database\Seeder;

class CatBanksSeeder extends Seeder
{
	public function run()
	{
		$banks = [
			['c_bank' => '002', 'description' => 'BANAMEX', 'businessName' => 'Banco Nacional de México, S.A., Institución de Banca Múltiple, Grupo Financiero Banamex', 'validity_start' => '2017-01-01', 'validity_end' => NULL],
			['c_bank' => '006', 'description' => 'BANCOMEXT', 'businessName' => 'Banco Nacional de Comercio Exterior, Sociedad Nacional de Crédito, Institución de Banca de Desarrollo', 'validity_start' => '2017-01-01', 'validity_end' => NULL],
			['c_bank' => '009', 'description' => 'BANOBRAS', 'businessName' => 'Banco Nacional de Obras y Servicios Públicos, Sociedad Nacional de Crédito, Institución de Banca de Desarrollo', 'validity_start' => '2017-01-01', 'validity_end' => NULL],
			['c_bank' => '012', 'description' => 'BBVA BANCOMER', 'businessName' => 'BBVA Bancomer, S.A., Institución de Banca Múltiple, Grupo Financiero BBVA Bancomer', 'validity_start' => '2017-01-01', 'validity_end' => NULL],
			['c_bank' => '014', 'description' => 'SANTANDER', 'businessName' => 'Banco Santander (México), S.A., Institución de Banca Múltiple, Grupo Financiero Santander', 'validity_start' => '2017-01-01', 'validity_end' => NULL],
			['c_bank' => '019', 'description' => 'BANJERCITO', 'businessName' => 'Banco Nacional del Ejército, Fuerza Aérea y Armada, Sociedad Nacional de Crédito, Institución de Banca de Desarrollo', 'validity_start' => '2017-01-01', 'validity_end' => NULL],
			['c_bank' => '021', 'description' => 'HSBC', 'businessName' => 'HSBC México, S.A., institución De Banca Múltiple, Grupo Financiero HSBC', 'validity_start' => '2017-01-01', 'validity_end' => NULL],
			['c_bank' => '030', 'description' => 'BAJIO', 'businessName' => 'Banco del Bajío, S.A., Institución de Banca Múltiple', 'validity_start' => '2017-01-01', 'validity_end' => NULL],
			['c_bank' => '032', 'description' => 'IXE', 'businessName' => 'IXE Banco, S.A., Institución de Banca Múltiple, IXE Grupo Financiero', 'validity_start' => '2017-01-01', 'validity_end' => '2017-08-13'],
			['c_bank' => '036', 'description' => 'INBURSA', 'businessName' => 'Banco Inbursa, S.A., Institución de Banca Múltiple, Grupo Financiero Inbursa', 'validity_start' => '2017-01-01', 'validity_end' => NULL],
			['c_bank' => '037', 'description' => 'INTERACCIONES', 'businessName' => 'Banco Interacciones, S.A., Institución de Banca Múltiple', 'validity_start' => '2017-01-01', 'validity_end' => NULL],
			['c_bank' => '042', 'description' => 'MIFEL', 'businessName' => 'Banca Mifel, S.A., Institución de Banca Múltiple, Grupo Financiero Mifel', 'validity_start' => '2017-01-01', 'validity_end' => NULL],
			['c_bank' => '044', 'description' => 'SCOTIABANK', 'businessName' => 'Scotiabank Inverlat, S.A.', 'validity_start' => '2017-01-01', 'validity_end' => NULL],
			['c_bank' => '058', 'description' => 'BANREGIO', 'businessName' => 'Banco Regional de Monterrey, S.A., Institución de Banca Múltiple, Banregio Grupo Financiero', 'validity_start' => '2017-01-01', 'validity_end' => NULL],
			['c_bank' => '059', 'description' => 'INVEX', 'businessName' => 'Banco Invex, S.A., Institución de Banca Múltiple, Invex Grupo Financiero', 'validity_start' => '2017-01-01', 'validity_end' => NULL],
			['c_bank' => '060', 'description' => 'BANSI', 'businessName' => 'Bansi, S.A., Institución de Banca Múltiple', 'validity_start' => '2017-01-01', 'validity_end' => NULL],
			['c_bank' => '062', 'description' => 'AFIRME', 'businessName' => 'Banca Afirme, S.A., Institución de Banca Múltiple', 'validity_start' => '2017-01-01', 'validity_end' => NULL],
			['c_bank' => '072', 'description' => 'BANORTE/IXE', 'businessName' => 'Banco Mercantil del Norte, S.A., Institución de Banca Múltiple, Grupo Financiero Banorte', 'validity_start' => '2017-01-01', 'validity_end' => NULL],
			['c_bank' => '102', 'description' => 'THE ROYAL BANK', 'businessName' => 'The Royal Bank of Scotland México, S.A., Institución de Banca Múltiple', 'validity_start' => '2017-01-01', 'validity_end' => NULL],
			['c_bank' => '103', 'description' => 'AMERICAN EXPRESS', 'businessName' => 'American Express Bank (México), S.A., Institución de Banca Múltiple', 'validity_start' => '2017-01-01', 'validity_end' => NULL],
			['c_bank' => '106', 'description' => 'BAMSA', 'businessName' => 'Bank of America México, S.A., Institución de Banca Múltiple, Grupo Financiero Bank of America', 'validity_start' => '2017-01-01', 'validity_end' => NULL],
			['c_bank' => '108', 'description' => 'TOKYO', 'businessName' => 'Bank of Tokyo-Mitsubishi UFJ (México), S.A.', 'validity_start' => '2017-01-01', 'validity_end' => NULL],
			['c_bank' => '110', 'description' => 'JP MORGAN', 'businessName' => 'Banco J.P. Morgan, S.A., Institución de Banca Múltiple, J.P. Morgan Grupo Financiero', 'validity_start' => '2017-01-01', 'validity_end' => NULL],
			['c_bank' => '112', 'description' => 'BMONEX', 'businessName' => 'Banco Monex, S.A., Institución de Banca Múltiple', 'validity_start' => '2017-01-01', 'validity_end' => NULL],
			['c_bank' => '113', 'description' => 'VE POR MAS', 'businessName' => 'Banco Ve Por Mas, S.A. Institución de Banca Múltiple', 'validity_start' => '2017-01-01', 'validity_end' => NULL],
			['c_bank' => '116', 'description' => 'ING', 'businessName' => 'ING Bank (México), S.A., Institución de Banca Múltiple, ING Grupo Financiero', 'validity_start' => '2017-01-01', 'validity_end' => '2017-08-13'],
			['c_bank' => '124', 'description' => 'DEUTSCHE', 'businessName' => 'Deutsche Bank México, S.A., Institución de Banca Múltiple', 'validity_start' => '2017-01-01', 'validity_end' => NULL],
			['c_bank' => '126', 'description' => 'CREDIT SUISSE', 'businessName' => 'Banco Credit Suisse (México), S.A. Institución de Banca Múltiple, Grupo Financiero Credit Suisse (México)', 'validity_start' => '2017-01-01', 'validity_end' => NULL],
			['c_bank' => '127', 'description' => 'AZTECA', 'businessName' => 'Banco Azteca, S.A. Institución de Banca Múltiple.', 'validity_start' => '2017-01-01', 'validity_end' => NULL],
			['c_bank' => '128', 'description' => 'AUTOFIN', 'businessName' => 'Banco Autofin México, S.A. Institución de Banca Múltiple', 'validity_start' => '2017-01-01', 'validity_end' => NULL],
			['c_bank' => '129', 'description' => 'BARCLAYS', 'businessName' => 'Barclays Bank México, S.A., Institución de Banca Múltiple, Grupo Financiero Barclays México', 'validity_start' => '2017-01-01', 'validity_end' => NULL],
			['c_bank' => '130', 'description' => 'COMPARTAMOS', 'businessName' => 'Banco Compartamos, S.A., Institución de Banca Múltiple', 'validity_start' => '2017-01-01', 'validity_end' => NULL],
			['c_bank' => '131', 'description' => 'BANCO FAMSA', 'businessName' => 'Banco Ahorro Famsa, S.A., Institución de Banca Múltiple', 'validity_start' => '2017-01-01', 'validity_end' => NULL],
			['c_bank' => '132', 'description' => 'BMULTIVA', 'businessName' => 'Banco Multiva, S.A., Institución de Banca Múltiple, Multivalores Grupo Financiero', 'validity_start' => '2017-01-01', 'validity_end' => NULL],
			['c_bank' => '133', 'description' => 'ACTINVER', 'businessName' => 'Banco Actinver, S.A. Institución de Banca Múltiple, Grupo Financiero Actinver', 'validity_start' => '2017-01-01', 'validity_end' => NULL],
			['c_bank' => '134', 'description' => 'WAL-MART', 'businessName' => 'Banco Wal-Mart de México Adelante, S.A., Institución de Banca Múltiple', 'validity_start' => '2017-01-01', 'validity_end' => '2017-08-13'],
			['c_bank' => '135', 'description' => 'NAFIN', 'businessName' => 'Nacional Financiera, Sociedad Nacional de Crédito, Institución de Banca de Desarrollo', 'validity_start' => '2017-01-01', 'validity_end' => NULL],
			['c_bank' => '136', 'description' => 'INTERCAM BANCO', 'businessName' => 'Intercam Banco, S.A., Institución de Banca Múltiple, Intercam Grupo Financiero', 'validity_start' => '2017-01-01', 'validity_end' => NULL],
			['c_bank' => '137', 'description' => 'BANCOPPEL', 'businessName' => 'BanCoppel, S.A., Institución de Banca Múltiple', 'validity_start' => '2017-01-01', 'validity_end' => NULL],
			['c_bank' => '138', 'description' => 'ABC CAPITAL', 'businessName' => 'ABC Capital, S.A., Institución de Banca Múltiple', 'validity_start' => '2017-01-01', 'validity_end' => NULL],
			['c_bank' => '139', 'description' => 'UBS BANK', 'businessName' => 'UBS Bank México, S.A., Institución de Banca Múltiple, UBS Grupo Financiero', 'validity_start' => '2017-01-01', 'validity_end' => NULL],
			['c_bank' => '140', 'description' => 'CONSUBANCO', 'businessName' => 'Consubanco, S.A. Institución de Banca Múltiple', 'validity_start' => '2017-01-01', 'validity_end' => NULL],
			['c_bank' => '141', 'description' => 'VOLKSWAGEN', 'businessName' => 'Volkswagen Bank, S.A., Institución de Banca Múltiple', 'validity_start' => '2017-01-01', 'validity_end' => NULL],
			['c_bank' => '143', 'description' => 'CIBANCO', 'businessName' => 'CIBanco, S.A.', 'validity_start' => '2017-01-01', 'validity_end' => NULL],
			['c_bank' => '145', 'description' => 'BBASE', 'businessName' => 'Banco Base, S.A., Institución de Banca Múltiple', 'validity_start' => '2017-01-01', 'validity_end' => NULL],
			['c_bank' => '147', 'description' => 'BANKAOOL', 'businessName' => 'Bankaool, S.A., Institución de Banca Múltiple', 'validity_start' => '2017-08-13', 'validity_end' => NULL],
			['c_bank' => '148', 'description' => 'PAGATODO', 'businessName' => 'Banco PagaTodo, S.A., Institución de Banca Múltiple', 'validity_start' => '2017-08-13', 'validity_end' => NULL],
			['c_bank' => '149', 'description' => 'FORJADORES', 'businessName' => 'Banco Forjadores, S.A., Institución de Banca Múltiple', 'validity_start' => '2017-08-13', 'validity_end' => NULL],
			['c_bank' => '150', 'description' => 'INMOBILIARIO', 'businessName' => 'Banco Inmobiliario Mexicano, S.A., Institución de Banca Múltiple', 'validity_start' => '2017-08-13', 'validity_end' => NULL],
			['c_bank' => '151', 'description' => 'DONDÉ', 'businessName' => 'Fundación Dondé Banco, S.A., Institución de Banca Múltiple', 'validity_start' => '2017-08-13', 'validity_end' => NULL],
			['c_bank' => '152', 'description' => 'BANCREA', 'businessName' => 'Banco Bancrea, S.A., Institución de Banca Múltiple', 'validity_start' => '2017-08-13', 'validity_end' => NULL],
			['c_bank' => '153', 'description' => 'PROGRESO', 'businessName' => 'Banco Progreso Chihuahua, S.A.', 'validity_start' => '2017-08-13', 'validity_end' => NULL],
			['c_bank' => '154', 'description' => 'BANCO FINTERRA', 'businessName' => 'Banco Finterra, S.A., Institución de Banca Múltiple', 'validity_start' => '2017-08-13', 'validity_end' => NULL],
			['c_bank' => '155', 'description' => 'ICBC', 'businessName' => 'Industrial and Commercial Bank of China México, S.A., Institución de Banca Múltiple', 'validity_start' => '2017-08-13', 'validity_end' => NULL],
			['c_bank' => '156', 'description' => 'SABADELL', 'businessName' => 'Banco Sabadell, S.A., Institución de Banca Múltiple', 'validity_start' => '2017-08-13', 'validity_end' => NULL],
			['c_bank' => '157', 'description' => 'SHINHAN', 'businessName' => 'Banco Shinhan de México, S.A., Institución de Banca Múltiple', 'validity_start' => '2017-08-13', 'validity_end' => NULL],
			['c_bank' => '158', 'description' => 'MIZUHO BANK', 'businessName' => 'Mizuho Bank México, S.A., Institución de Banca Múltiple', 'validity_start' => '2017-08-13', 'validity_end' => NULL],
			['c_bank' => '159', 'description' => 'BANK OF CHINA', 'businessName' => 'Bank of China México, S.A., Institución de Banca Múltiple', 'validity_start' => '2017-08-13', 'validity_end' => NULL],
			['c_bank' => '160', 'description' => 'BANCO S3', 'businessName' => 'Banco S3 México, S.A., Institución de Banca Múltiple', 'validity_start' => '2017-08-13', 'validity_end' => NULL],
			['c_bank' => '166', 'description' => 'BANSEFI', 'businessName' => 'Banco del Ahorro Nacional y Servicios Financieros, Sociedad Nacional de Crédito, Institución de Banca de Desarrollo', 'validity_start' => '2017-01-01', 'validity_end' => NULL],
			['c_bank' => '168', 'description' => 'HIPOTECARIA FEDERAL', 'businessName' => 'Sociedad Hipotecaria Federal, Sociedad Nacional de Crédito, Institución de Banca de Desarrollo', 'validity_start' => '2017-01-01', 'validity_end' => NULL],
			['c_bank' => '600', 'description' => 'MONEXCB', 'businessName' => 'Monex Casa de Bolsa, S.A. de C.V. Monex Grupo Financiero', 'validity_start' => '2017-01-01', 'validity_end' => NULL],
			['c_bank' => '601', 'description' => 'GBM', 'businessName' => 'GBM Grupo Bursátil Mexicano, S.A. de C.V. Casa de Bolsa', 'validity_start' => '2017-01-01', 'validity_end' => NULL],
			['c_bank' => '602', 'description' => 'MASARI', 'businessName' => 'Masari Casa de Bolsa, S.A.', 'validity_start' => '2017-01-01', 'validity_end' => NULL],
			['c_bank' => '605', 'description' => 'VALUE', 'businessName' => 'Value, S.A. de C.V. Casa de Bolsa', 'validity_start' => '2017-01-01', 'validity_end' => NULL],
			['c_bank' => '606', 'description' => 'ESTRUCTURADORES', 'businessName' => 'Estructuradores del Mercado de Valores Casa de Bolsa, S.A. de C.V.', 'validity_start' => '2017-01-01', 'validity_end' => NULL],
			['c_bank' => '607', 'description' => 'TIBER', 'businessName' => 'Casa de Cambio Tiber, S.A. de C.V.', 'validity_start' => '2017-01-01', 'validity_end' => NULL],
			['c_bank' => '608', 'description' => 'VECTOR', 'businessName' => 'Vector Casa de Bolsa, S.A. de C.V.', 'validity_start' => '2017-01-01', 'validity_end' => NULL],
			['c_bank' => '610', 'description' => 'B&B', 'businessName' => 'B y B, Casa de Cambio, S.A. de C.V.', 'validity_start' => '2017-01-01', 'validity_end' => NULL],
			['c_bank' => '614', 'description' => 'ACCIVAL', 'businessName' => 'Acciones y Valores Banamex, S.A. de C.V., Casa de Bolsa', 'validity_start' => '2017-01-01', 'validity_end' => NULL],
			['c_bank' => '615', 'description' => 'MERRILL LYNCH', 'businessName' => 'Merrill Lynch México, S.A. de C.V. Casa de Bolsa', 'validity_start' => '2017-01-01', 'validity_end' => NULL],
			['c_bank' => '616', 'description' => 'FINAMEX', 'businessName' => 'Casa de Bolsa Finamex, S.A. de C.V.', 'validity_start' => '2017-01-01', 'validity_end' => NULL],
			['c_bank' => '617', 'description' => 'VALMEX', 'businessName' => 'Valores Mexicanos Casa de Bolsa, S.A. de C.V.', 'validity_start' => '2017-01-01', 'validity_end' => NULL],
			['c_bank' => '618', 'description' => 'UNICA', 'businessName' => 'Unica Casa de Cambio, S.A. de C.V.', 'validity_start' => '2017-01-01', 'validity_end' => NULL],
			['c_bank' => '619', 'description' => 'MAPFRE', 'businessName' => 'MAPFRE Tepeyac, S.A.', 'validity_start' => '2017-01-01', 'validity_end' => NULL],
			['c_bank' => '620', 'description' => 'PROFUTURO', 'businessName' => 'Profuturo G.N.P., S.A. de C.V., Afore', 'validity_start' => '2017-01-01', 'validity_end' => NULL],
			['c_bank' => '621', 'description' => 'CB ACTINVER', 'businessName' => 'Actinver Casa de Bolsa, S.A. de C.V.', 'validity_start' => '2017-01-01', 'validity_end' => NULL],
			['c_bank' => '622', 'description' => 'OACTIN', 'businessName' => 'OPERADORA ACTINVER, S.A. DE C.V.', 'validity_start' => '2017-01-01', 'validity_end' => NULL],
			['c_bank' => '623', 'description' => 'SKANDIA', 'businessName' => 'Skandia Vida, S.A. de C.V.', 'validity_start' => '2017-01-01', 'validity_end' => NULL],
			['c_bank' => '626', 'description' => 'CBDEUTSCHE', 'businessName' => 'Deutsche Securities, S.A. de C.V. CASA DE BOLSA', 'validity_start' => '2017-01-01', 'validity_end' => NULL],
			['c_bank' => '627', 'description' => 'ZURICH', 'businessName' => 'Zurich Compañía de Seguros, S.A.', 'validity_start' => '2017-01-01', 'validity_end' => NULL],
			['c_bank' => '628', 'description' => 'ZURICHVI', 'businessName' => 'Zurich Vida, Compañía de Seguros, S.A.', 'validity_start' => '2017-01-01', 'validity_end' => NULL],
			['c_bank' => '629', 'description' => 'SU CASITA', 'businessName' => 'Hipotecaria Su Casita, S.A. de C.V. SOFOM ENR', 'validity_start' => '2017-01-01', 'validity_end' => NULL],
			['c_bank' => '630', 'description' => 'CB INTERCAM', 'businessName' => 'Intercam Casa de Bolsa, S.A. de C.V.', 'validity_start' => '2017-01-01', 'validity_end' => NULL],
			['c_bank' => '631', 'description' => 'CI BOLSA', 'businessName' => 'CI Casa de Bolsa, S.A. de C.V.', 'validity_start' => '2017-01-01', 'validity_end' => NULL],
			['c_bank' => '632', 'description' => 'BULLTICK CB', 'businessName' => 'Bulltick Casa de Bolsa, S.A., de C.V.', 'validity_start' => '2017-01-01', 'validity_end' => NULL],
			['c_bank' => '633', 'description' => 'STERLING', 'businessName' => 'Sterling Casa de Cambio, S.A. de C.V.', 'validity_start' => '2017-01-01', 'validity_end' => NULL],
			['c_bank' => '634', 'description' => 'FINCOMUN', 'businessName' => 'Fincomún, Servicios Financieros Comunitarios, S.A. de C.V.', 'validity_start' => '2017-01-01', 'validity_end' => NULL],
			['c_bank' => '636', 'description' => 'HDI SEGUROS', 'businessName' => 'HDI Seguros, S.A. de C.V.', 'validity_start' => '2017-01-01', 'validity_end' => NULL],
			['c_bank' => '637', 'description' => 'ORDER', 'businessName' => 'Order Express Casa de Cambio, S.A. de C.V', 'validity_start' => '2017-01-01', 'validity_end' => NULL],
			['c_bank' => '638', 'description' => 'AKALA', 'businessName' => 'Akala, S.A. de C.V., Sociedad Financiera Popular', 'validity_start' => '2017-01-01', 'validity_end' => NULL],
			['c_bank' => '640', 'description' => 'CB JPMORGAN', 'businessName' => 'J.P. Morgan Casa de Bolsa, S.A. de C.V. J.P. Morgan Grupo Financiero', 'validity_start' => '2017-01-01', 'validity_end' => NULL],
			['c_bank' => '642', 'description' => 'REFORMA', 'businessName' => 'Operadora de Recursos Reforma, S.A. de C.V., S.F.P.', 'validity_start' => '2017-01-01', 'validity_end' => NULL],
			['c_bank' => '646', 'description' => 'STP', 'businessName' => 'Sistema de Transferencias y Pagos STP, S.A. de C.V.SOFOM ENR', 'validity_start' => '2017-01-01', 'validity_end' => NULL],
			['c_bank' => '647', 'description' => 'TELECOMM', 'businessName' => 'Telecomunicaciones de México', 'validity_start' => '2017-01-01', 'validity_end' => NULL],
			['c_bank' => '648', 'description' => 'EVERCORE', 'businessName' => 'Evercore Casa de Bolsa, S.A. de C.V.', 'validity_start' => '2017-01-01', 'validity_end' => NULL],
			['c_bank' => '649', 'description' => 'SKANDIA', 'businessName' => 'Skandia Operadora de Fondos, S.A. de C.V.', 'validity_start' => '2017-01-01', 'validity_end' => NULL],
			['c_bank' => '651', 'description' => 'SEGMTY', 'businessName' => 'Seguros Monterrey New York Life, S.A de C.V', 'validity_start' => '2017-01-01', 'validity_end' => NULL],
			['c_bank' => '652', 'description' => 'ASEA', 'businessName' => 'Solución Asea, S.A. de C.V., Sociedad Financiera Popular', 'validity_start' => '2017-01-01', 'validity_end' => NULL],
			['c_bank' => '653', 'description' => 'KUSPIT', 'businessName' => 'Kuspit Casa de Bolsa, S.A. de C.V.', 'validity_start' => '2017-01-01', 'validity_end' => NULL],
			['c_bank' => '655', 'description' => 'SOFIEXPRESS', 'businessName' => 'J.P. SOFIEXPRESS, S.A. de C.V., S.F.P.', 'validity_start' => '2017-01-01', 'validity_end' => NULL],
			['c_bank' => '656', 'description' => 'UNAGRA', 'businessName' => 'UNAGRA, S.A. de C.V., S.F.P.', 'validity_start' => '2017-01-01', 'validity_end' => NULL],
			['c_bank' => '659', 'description' => 'OPCIONES EMPRESARIALES DEL NOROESTE', 'businessName' => 'OPCIONES EMPRESARIALES DEL NORESTE, S.A. DE C.V., S.F.P.', 'validity_start' => '2017-01-01', 'validity_end' => NULL],
			['c_bank' => '670', 'description' => 'LIBERTAD', 'businessName' => 'Libertad Servicios Financieros, S.A. De C.V.', 'validity_start' => '2017-01-01', 'validity_end' => NULL],
			['c_bank' => '901', 'description' => 'CLS', 'businessName' => 'Cls Bank International', 'validity_start' => '2017-01-01', 'validity_end' => NULL],
			['c_bank' => '902', 'description' => 'INDEVAL', 'businessName' => 'SD. Indeval, S.A. de C.V.', 'validity_start' => '2017-01-01', 'validity_end' => NULL],
		];

		foreach($banks as $bank)
		{
			App\CatBank::create($bank);
		}
	}
}
