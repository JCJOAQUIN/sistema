<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
	public function run()
	{
		Eloquent::unguard();
		$this->call(ModuleSeeder::class);
		$this->call(StateSeeder::class);
		$this->call(CatBanksSeeder::class);
		$this->call(CatContractTypeSeeder::class);
		$this->call(CatCurrencySeeder::class);
		$this->call(CatDeductionSeeder::class);
		$this->call(CatElementSeeder::class);
		$this->call(CatOtherPaymentSeeder::class);
		$this->call(CatPaymentMethodSeeder::class);
		$this->call(CatPaymentWaySeeder::class);
		$this->call(CatPerceptionSeeder::class);
		$this->call(CatPeriodicitySeeder::class);
		$this->call(CatPositionRiskSeeder::class);
		$this->call(CatRegimeTypeSeeder::class);
		$this->call(CatTaxObject::class);
		$this->call(CatExport::class);
		$this->call(CatRelationSeeder::class);
		$this->call(CatTaxesSeeder::class);
		$this->call(CatTaxRegimeSeeder::class);
		$this->call(CatTypeHours::class);
		$this->call(CatUseVoucherSeeder::class);
		$this->call(CatTypeBillSeeder::class);
		$this->call(CatProdServSeeder::class);
		$this->call(CatUnitySeeder::class);
		$this->call(CatZipCodeSeeder::class);
		$this->call(AuditCategorySeeder::class);
		$this->call(AuditSubcategorySeeder::class);
		$this->call(ContractorsSeeder::class);
		$this->call(CatAuditorSeeder::class);
		$this->call(CatTypeDocumentSeeder::class);
		$this->call(CatWeatherConditionsSeeder::class);
		$this->call(CatDisciplineSeeder::class);
		$this->call(CatTMSeeder::class);
		$this->call(CatContractItemSeeder::class);
		$this->call(CatMachinerySeeder::class);
		$this->call(CatIndustrialStaffSeeder::class);
		$this->command->info("Database seeded.");
		Eloquent::reguard();
	}
}
