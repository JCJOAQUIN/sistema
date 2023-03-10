<?php

use Illuminate\Database\Seeder;

class CatCurrencySeeder extends Seeder
{
	public function run()
	{
		$currencies = [
			['currency' => 'AED', 'description' => 'Dirham de EAU'],
			['currency' => 'AFN', 'description' => 'Afghani'],
			['currency' => 'ALL', 'description' => 'Lek'],
			['currency' => 'AMD', 'description' => 'Dram armenio'],
			['currency' => 'ANG', 'description' => 'Florín antillano neerlandés'],
			['currency' => 'AOA', 'description' => 'Kwanza'],
			['currency' => 'ARS', 'description' => 'Peso Argentino'],
			['currency' => 'AUD', 'description' => 'Dólar Australiano'],
			['currency' => 'AWG', 'description' => 'Aruba Florin'],
			['currency' => 'AZN', 'description' => 'Azerbaijanian Manat'],
			['currency' => 'BAM', 'description' => 'Convertibles marca'],
			['currency' => 'BBD', 'description' => 'Dólar de Barbados'],
			['currency' => 'BDT', 'description' => 'Taka'],
			['currency' => 'BGN', 'description' => 'Lev búlgaro'],
			['currency' => 'BHD', 'description' => 'Dinar de Bahrein'],
			['currency' => 'BIF', 'description' => 'Burundi Franc'],
			['currency' => 'BMD', 'description' => 'Dólar de Bermudas'],
			['currency' => 'BND', 'description' => 'Dólar de Brunei'],
			['currency' => 'BOB', 'description' => 'Boliviano'],
			['currency' => 'BOV', 'description' => 'Mvdol'],
			['currency' => 'BRL', 'description' => 'Real brasileño'],
			['currency' => 'BSD', 'description' => 'Dólar de las Bahamas'],
			['currency' => 'BTN', 'description' => 'Ngultrum'],
			['currency' => 'BWP', 'description' => 'Pula'],
			['currency' => 'BYR', 'description' => 'Rublo bielorruso'],
			['currency' => 'BZD', 'description' => 'Dólar de Belice'],
			['currency' => 'CAD', 'description' => 'Dólar Canadiense'],
			['currency' => 'CDF', 'description' => 'Franco congoleño'],
			['currency' => 'CHE', 'description' => 'WIR Euro'],
			['currency' => 'CHF', 'description' => 'Franco Suizo'],
			['currency' => 'CHW', 'description' => 'Franc WIR'],
			['currency' => 'CLF', 'description' => 'Unidad de Fomento'],
			['currency' => 'CLP', 'description' => 'Peso chileno'],
			['currency' => 'CNY', 'description' => 'Yuan Renminbi'],
			['currency' => 'COP', 'description' => 'Peso Colombiano'],
			['currency' => 'COU', 'description' => 'Unidad de Valor real'],
			['currency' => 'CRC', 'description' => 'Colón costarricense'],
			['currency' => 'CUC', 'description' => 'Peso Convertible'],
			['currency' => 'CUP', 'description' => 'Peso Cubano'],
			['currency' => 'CVE', 'description' => 'Cabo Verde Escudo'],
			['currency' => 'CZK', 'description' => 'Corona checa'],
			['currency' => 'DJF', 'description' => 'Franco de Djibouti'],
			['currency' => 'DKK', 'description' => 'Corona danesa'],
			['currency' => 'DOP', 'description' => 'Peso Dominicano'],
			['currency' => 'DZD', 'description' => 'Dinar argelino'],
			['currency' => 'EGP', 'description' => 'Libra egipcia'],
			['currency' => 'ERN', 'description' => 'Nakfa'],
			['currency' => 'ETB', 'description' => 'Birr etíope'],
			['currency' => 'EUR', 'description' => 'Euro'],
			['currency' => 'FJD', 'description' => 'Dólar de Fiji'],
			['currency' => 'FKP', 'description' => 'Libra malvinense'],
			['currency' => 'GBP', 'description' => 'Libra Esterlina'],
			['currency' => 'GEL', 'description' => 'Lari'],
			['currency' => 'GHS', 'description' => 'Cedi de Ghana'],
			['currency' => 'GIP', 'description' => 'Libra de Gibraltar'],
			['currency' => 'GMD', 'description' => 'Dalasi'],
			['currency' => 'GNF', 'description' => 'Franco guineano'],
			['currency' => 'GTQ', 'description' => 'Quetzal'],
			['currency' => 'GYD', 'description' => 'Dólar guyanés'],
			['currency' => 'HKD', 'description' => 'Dólar De Hong Kong'],
			['currency' => 'HNL', 'description' => 'Lempira'],
			['currency' => 'HRK', 'description' => 'Kuna'],
			['currency' => 'HTG', 'description' => 'Gourde'],
			['currency' => 'HUF', 'description' => 'Florín'],
			['currency' => 'IDR', 'description' => 'Rupia'],
			['currency' => 'ILS', 'description' => 'Nuevo Shekel Israelí'],
			['currency' => 'INR', 'description' => 'Rupia india'],
			['currency' => 'IQD', 'description' => 'Dinar iraquí'],
			['currency' => 'IRR', 'description' => 'Rial iraní'],
			['currency' => 'ISK', 'description' => 'Corona islandesa'],
			['currency' => 'JMD', 'description' => 'Dólar Jamaiquino'],
			['currency' => 'JOD', 'description' => 'Dinar jordano'],
			['currency' => 'JPY', 'description' => 'Yen'],
			['currency' => 'KES', 'description' => 'Chelín keniano'],
			['currency' => 'KGS', 'description' => 'Som'],
			['currency' => 'KHR', 'description' => 'Riel'],
			['currency' => 'KMF', 'description' => 'Franco Comoro'],
			['currency' => 'KPW', 'description' => 'Corea del Norte ganó'],
			['currency' => 'KRW', 'description' => 'Won'],
			['currency' => 'KWD', 'description' => 'Dinar kuwaití'],
			['currency' => 'KYD', 'description' => 'Dólar de las Islas Caimán'],
			['currency' => 'KZT', 'description' => 'Tenge'],
			['currency' => 'LAK', 'description' => 'Kip'],
			['currency' => 'LBP', 'description' => 'Libra libanesa'],
			['currency' => 'LKR', 'description' => 'Rupia de Sri Lanka'],
			['currency' => 'LRD', 'description' => 'Dólar liberiano'],
			['currency' => 'LSL', 'description' => 'Loti'],
			['currency' => 'LYD', 'description' => 'Dinar libio'],
			['currency' => 'MAD', 'description' => 'Dirham marroquí'],
			['currency' => 'MDL', 'description' => 'Leu moldavo'],
			['currency' => 'MGA', 'description' => 'Ariary malgache'],
			['currency' => 'MKD', 'description' => 'Denar'],
			['currency' => 'MMK', 'description' => 'Kyat'],
			['currency' => 'MNT', 'description' => 'Tugrik'],
			['currency' => 'MOP', 'description' => 'Pataca'],
			['currency' => 'MRO', 'description' => 'Ouguiya'],
			['currency' => 'MUR', 'description' => 'Rupia de Mauricio'],
			['currency' => 'MVR', 'description' => 'Rupia'],
			['currency' => 'MWK', 'description' => 'Kwacha'],
			['currency' => 'MXN', 'description' => 'Peso Mexicano'],
			['currency' => 'MXV', 'description' => 'México Unidad de Inversión (UDI)'],
			['currency' => 'MYR', 'description' => 'Ringgit malayo'],
			['currency' => 'MZN', 'description' => 'Mozambique Metical'],
			['currency' => 'NAD', 'description' => 'Dólar de Namibia'],
			['currency' => 'NGN', 'description' => 'Naira'],
			['currency' => 'NIO', 'description' => 'Córdoba Oro'],
			['currency' => 'NOK', 'description' => 'Corona noruega'],
			['currency' => 'NPR', 'description' => 'Rupia nepalí'],
			['currency' => 'NZD', 'description' => 'Dólar de Nueva Zelanda'],
			['currency' => 'OMR', 'description' => 'Rial omaní'],
			['currency' => 'PAB', 'description' => 'Balboa'],
			['currency' => 'PEN', 'description' => 'Nuevo Sol'],
			['currency' => 'PGK', 'description' => 'Kina'],
			['currency' => 'PHP', 'description' => 'Peso filipino'],
			['currency' => 'PKR', 'description' => 'Rupia de Pakistán'],
			['currency' => 'PLN', 'description' => 'Zloty'],
			['currency' => 'PYG', 'description' => 'Guaraní'],
			['currency' => 'QAR', 'description' => 'Qatar Rial'],
			['currency' => 'RON', 'description' => 'Leu rumano'],
			['currency' => 'RSD', 'description' => 'Dinar serbio'],
			['currency' => 'RUB', 'description' => 'Rublo ruso'],
			['currency' => 'RWF', 'description' => 'Franco ruandés'],
			['currency' => 'SAR', 'description' => 'Riyal saudí'],
			['currency' => 'SBD', 'description' => 'Dólar de las Islas Salomón'],
			['currency' => 'SCR', 'description' => 'Rupia de Seychelles'],
			['currency' => 'SDG', 'description' => 'Libra sudanesa'],
			['currency' => 'SEK', 'description' => 'Corona sueca'],
			['currency' => 'SGD', 'description' => 'Dólar De Singapur'],
			['currency' => 'SHP', 'description' => 'Libra de Santa Helena'],
			['currency' => 'SLL', 'description' => 'Leona'],
			['currency' => 'SOS', 'description' => 'Chelín somalí'],
			['currency' => 'SRD', 'description' => 'Dólar de Suriname'],
			['currency' => 'SSP', 'description' => 'Libra sudanesa Sur'],
			['currency' => 'STD', 'description' => 'Dobra'],
			['currency' => 'SVC', 'description' => 'Colon El Salvador'],
			['currency' => 'SYP', 'description' => 'Libra Siria'],
			['currency' => 'SZL', 'description' => 'Lilangeni'],
			['currency' => 'THB', 'description' => 'Baht'],
			['currency' => 'TJS', 'description' => 'Somoni'],
			['currency' => 'TMT', 'description' => 'Turkmenistán nuevo manat'],
			['currency' => 'TND', 'description' => 'Dinar tunecino'],
			['currency' => 'TOP', 'description' => 'Pa\'anga'],
			['currency' => 'TRY', 'description' => 'Lira turca'],
			['currency' => 'TTD', 'description' => 'Dólar de Trinidad y Tobago'],
			['currency' => 'TWD', 'description' => 'Nuevo dólar de Taiwán'],
			['currency' => 'TZS', 'description' => 'Shilling tanzano'],
			['currency' => 'UAH', 'description' => 'Hryvnia'],
			['currency' => 'UGX', 'description' => 'Shilling de Uganda'],
			['currency' => 'USD', 'description' => 'Dólar americano'],
			['currency' => 'USN', 'description' => 'Dólar estadounidense (día siguiente)'],
			['currency' => 'UYI', 'description' => 'Peso Uruguay en Unidades Indexadas (URUIURUI)'],
			['currency' => 'UYU', 'description' => 'Peso Uruguayo'],
			['currency' => 'UZS', 'description' => 'Uzbekistán Sum'],
			['currency' => 'VEF', 'description' => 'Bolívar'],
			['currency' => 'VND', 'description' => 'Dong'],
			['currency' => 'VUV', 'description' => 'Vatu'],
			['currency' => 'WST', 'description' => 'Tala'],
			['currency' => 'XAF', 'description' => 'Franco CFA BEAC'],
			['currency' => 'XAG', 'description' => 'Plata'],
			['currency' => 'XAU', 'description' => 'Oro'],
			['currency' => 'XBA', 'description' => 'Unidad de Mercados de Bonos Unidad Europea Composite (EURCO)'],
			['currency' => 'XBB', 'description' => 'Unidad Monetaria de Bonos de Mercados Unidad Europea (UEM-6)'],
			['currency' => 'XBC', 'description' => 'Mercados de Bonos Unidad Europea unidad de cuenta a 9 (UCE-9)'],
			['currency' => 'XBD', 'description' => 'Mercados de Bonos Unidad Europea unidad de cuenta a 17 (UCE-17)'],
			['currency' => 'XCD', 'description' => 'Dólar del Caribe Oriental'],
			['currency' => 'XDR', 'description' => 'DEG (Derechos Especiales de Giro)'],
			['currency' => 'XOF', 'description' => 'Franco CFA BCEAO'],
			['currency' => 'XPD', 'description' => 'Paladio'],
			['currency' => 'XPF', 'description' => 'Franco CFP'],
			['currency' => 'XPT', 'description' => 'Platino'],
			['currency' => 'XSU', 'description' => 'Sucre'],
			['currency' => 'XTS', 'description' => 'Códigos reservados específicamente para propósitos de prueba'],
			['currency' => 'XUA', 'description' => 'Unidad ADB de Cuenta'],
			['currency' => 'XXX', 'description' => 'Los códigos asignados para las transacciones en que intervenga ninguna moneda'],
			['currency' => 'YER', 'description' => 'Rial yemení'],
			['currency' => 'ZAR', 'description' => 'Rand'],
			['currency' => 'ZMW', 'description' => 'Kwacha zambiano'],
			['currency' => 'ZWL', 'description' => 'Zimbabwe Dólar']
		];

		foreach ($currencies as $currency)
		{
			App\CatCurrency::create($currency);
		}
	}
}
