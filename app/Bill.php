<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Bill extends Model
{
	public $timestamps    = false;
	protected $primaryKey = 'idBill';
	protected $fillable   = 
	[
		'idBill',
		'rfc',
		'businessName',
		'taxRegime',
		'clientRfc',
		'clientBusinessName',
		'receiver_tax_regime',
		'receiver_zip_code',
		'uuid',
		'noCertificate',
		'satCertificateNo',
		'expeditionDate',
		'expeditionDateCFDI',
		'stampDate',
		'cancelRequestDate',
		'CancelledDate',
		'cancellation_reason',
		'substitute_folio',
		'postalCode',
		'export',
		'serie',
		'folio',
		'conditions',
		'status',
		'statusCFDI',
		'statusCancelCFDI',
		'subtotal',
		'discount',
		'tras',
		'ret',
		'total',
		'related',
		'originalChain',
		'digitalStampCFDI',
		'digitalStampSAT',
		'signatureValueCancel',
		'type',
		'paymentMethod',
		'paymentWay',
		'currency',
		'exchange',
		'useBill',
		'error',
		'folioRequest',
		'statusConciliation',
		'idProject',
		'issuer_address',
		'receiver_address',
		'version',
	];

	public function requestModel()
	{
		return $this->belongsTo(RequestModel::class,'idFolio','folio');
	}

	public function requestHasBill()
	{
		return $this->hasOne(RequestModel::class,'folio','folioRequest');
	}

	public function billDetail()
	{
		return $this->hasMany(BillDetail::class,'idBill','idBill');
	}

	public function cfdiUse()
	{
		return $this->hasOne(CatUseVoucher::class,'useVoucher','useBill')
			->withoutGlobalScopes();
	}

	public function cfdiType()
	{
		return $this->hasOne(CatTypeBill::class,'typeVoucher','type');
	}

	public function cfdiPaymentWay()
	{
		return $this->hasOne(CatPaymentWay::class,'paymentWay','paymentWay')
			->withoutGlobalScopes();
	}

	public function cfdiPaymentMethod()
	{
		return $this->hasOne(CatPaymentMethod::class,'paymentMethod','paymentMethod')
			->withoutGlobalScopes();
	}

	public function cfdiCurrency()
	{
		return $this->hasOne(CatCurrency::class,'currency','currency');
	}

	public function cfdiTaxRegime()
	{
		return $this->hasOne(CatTaxRegime::class,'taxRegime','taxRegime')
			->withoutGlobalScopes();
	}

	public function cfdiReceiverTaxRegime()
	{
		return $this->hasOne(CatTaxRegime::class,'taxRegime','receiver_tax_regime')
			->withoutGlobalScopes();
	}

	public function cfdiRelated()
	{
		return $this->hasMany(RelatedBill::class,'idBill','idBill');
	}

	public function relationKind()
	{
		return $this->hasOne(CatRelation::class,'typeRelation','related')
			->withoutGlobalScopes();
	}

	public function paymentComplement()
	{
		return $this->hasMany(BillPayment::class,'idBill','idBill');
	}

	public function nomina()
	{
		return $this->hasOne(BillNomina::class,'bill_id');
	}

	public function nominaReceiver()
	{
		return $this->hasOne(BillNominaReceiver::class,'bill_id');
	}

	public function getCfdiFolioAttribute()
	{
		$folioSum   = Bill::where('uuid','!=',NULL)->where('rfc',$this->rfc)->count('folio');
		$enterprise = Enterprise::where('rfc',$this->rfc)->first();
		$folioStart = $enterprise->folioBill + $folioSum + 1;
		return $folioStart;
	}

	public function project()
	{
		return $this->hasOne(Project::class,'idproyect','idProject');
	}

	public function postal_code()
	{
		return $this->hasOne(CatZipCode::class,'zip_code','postalCode');
	}

	public function cfdiExport()
	{
		return $this->hasOne(CatExport::class,'id','export')
			->withoutGlobalScopes();
	}
}
