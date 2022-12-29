<?php

namespace App\Functions;
use App\RequestModel;
use App;

class DocsValidate
{
	static function validate($options, $folio = null)
	{
		$requestCheck = -1;
		if(isset($options['fiscal_val']) && $options['fiscal_val'] != '')
		{
			$requestCheck = App\RefundDocuments::leftJoin('refund_details','refund_details.idRefundDetail','refund_documents.idRefundDetail')
				->leftJoin('refunds','refund_details.idRefund','refunds.idRefund')
				->leftJoin('request_models','refunds.idFolio','request_models.folio')
				->whereIn('request_models.status',[2,3,4,5,10,11,12,18])
				->where(function($check) use ($options)
				{
					$check->where('refund_documents.fiscal_folio', $options['fiscal_val'])
						->where('refund_documents.datepath',  $options['date'])
						->where('refund_documents.timepath', $options['time'])
						->whereNotNull('refund_documents.fiscal_folio');
				})
				->where(function($query) use ($folio)
				{
					if ($folio != null) 
					{
						$query->whereNotIn('request_models.folio', [$folio]);
					}
				})
				->count();
			if($requestCheck > 0) 
			{
				return $requestCheck;
			}
			$requestCheck = App\RequisitionDocuments::leftJoin('requisitions','requisitions.id','requisition_documents.idRequisition')
				->leftJoin('request_models','requisitions.idFolio','request_models.folio')
				->whereIn('request_models.status',[2,3,4,5,17,27])
				->where(function($check) use ($options)
				{
					$check->where('requisition_documents.fiscal_folio', $options['fiscal_val'])
						->where('requisition_documents.datepath', $options['date'])
						->where('requisition_documents.timepath', $options['time'])
						->whereNotNull('requisition_documents.fiscal_folio');
				})
				->where(function($query) use ($folio)
				{
					if ($folio != null) 
					{
						$query->whereNotIn('request_models.folio', [$folio]);
					}
				})
				->count();
			if($requestCheck > 0) 
			{
				return $requestCheck;
			}
			$requestCheck = App\DocumentsPurchase::leftJoin('purchases','documents_purchases.idPurchase','purchases.idPurchase')
				->leftJoin('request_models','purchases.idFolio','request_models.folio')
				->whereIn('request_models.status',[2,3,4,5,10,11,12,18])
				->where(function($check) use ($options)
				{
					$check->where('documents_purchases.fiscal_folio', $options['fiscal_val'])
						->where('documents_purchases.datepath',  $options['date'])
						->where('documents_purchases.timepath', $options['time'])
						->whereNotNull('documents_purchases.fiscal_folio');
				})
				->where(function($query) use ($folio)
				{
					if ($folio != null) 
					{
						$query->whereNotIn('request_models.folio', [$folio]);
					}
				})
				->count();
			if($requestCheck > 0)
			{
				return $requestCheck;
			}
			$requestCheck = App\DocumentsPartials::leftJoin('partial_payments','documents_partials.partial_id','partial_payments.id')
				->leftJoin('purchases','partial_payments.purchase_id','purchases.idPurchase')
				->leftJoin('request_models','purchases.idFolio','request_models.folio')
				->whereIn('request_models.status',[2,3,4,5,10,11,12,18])
				->where(function($check) use ($options)
				{
					$check->where('documents_partials.fiscal_folio', $options['fiscal_val'])
						->where('documents_partials.datepath',  $options['date'])
						->where('documents_partials.timepath', $options['time'])
						->whereNotNull('documents_partials.fiscal_folio');
				})
				->where(function($query) use ($folio)
				{
					if ($folio != null) 
					{
						$query->whereNotIn('request_models.folio', [$folio]);
					}
				})
				->count();	
			if($requestCheck > 0)
			{
				return $requestCheck;
			}
			$requestCheck = App\ExpensesDocuments::leftJoin('expenses_details','expenses_details.idExpensesDetail','expenses_documents.idExpensesDetail')
				->leftJoin('expenses','expenses_details.idExpenses','expenses.idExpenses')
				->leftJoin('request_models','expenses.idFolio','request_models.folio')
				->whereIn('request_models.status',[2,3,4,5,10,11,12,18])
				->where(function($check) use ($options)
				{
					$check->where('expenses_documents.fiscal_folio', $options['fiscal_val'])
							->where('expenses_documents.date',  $options['date'])
							->where('expenses_documents.timepath', $options['time'])
							->whereNotNull('expenses_documents.fiscal_folio');
				})
				->where(function($query) use ($folio)
				{
					if ($folio != null) 
					{
						$query->whereNotIn('request_models.folio', [$folio]);
					}
				})
				->count();
			if($requestCheck > 0)
			{
				return $requestCheck;
			}
			$requestCheck = App\ResourceDocument::leftJoin('resources','resources.idresource','resource_documents.resource_id')
				->leftJoin('request_models','resources.idFolio','request_models.folio')
				->whereIn('request_models.status',[2,3,4,5,17,27])
				->where(function($check) use ($options)
				{
					$check->where('resource_documents.fiscal_folio', $options['fiscal_val'])
						->where('resource_documents.datepath', 'like', '%'.$options['date'].'%')
						->where('resource_documents.timepath','like' ,'%'.$options['time'].'%')
						->whereNotNull('resource_documents.fiscal_folio');
				})
				->count();
			if($requestCheck > 0)
			{
				return $requestCheck;
			}
		}
		if(isset($options['ticket_val']) && $options['ticket_val'] != "")
		{
			$requestCheck = App\RefundDocuments::leftJoin('refund_details','refund_details.idRefundDetail','refund_documents.idRefundDetail')
				->leftJoin('refunds','refund_details.idRefund','refunds.idRefund')
				->leftJoin('request_models','refunds.idFolio','request_models.folio')
				->whereIn('request_models.status',[2,3,4,5,10,11,12,18])
				->where(function($check) use ($options)
				{
					$check->where('refund_documents.ticket_number', $options['ticket_val'])
						->where('refund_documents.datepath', 'like', $options['date'].'%')
						->where('refund_documents.timepath','like', $options['time'].'%')
						->where('refund_documents.amount', $options['amount'])
						->whereNotNull('refund_documents.ticket_number');
				})
				->where(function($query) use ($folio)
				{
					if ($folio != null) 
					{
						$query->whereNotIn('request_models.folio', [$folio]);
					}
				})
				->count();
			if($requestCheck > 0)
			{
				return $requestCheck;
			}
			$requestCheck = App\RequisitionDocuments::leftJoin('requisitions','requisitions.id','requisition_documents.idRequisition')
				->leftJoin('request_models','requisitions.idFolio','request_models.folio')
				->whereIn('request_models.status',[2,3,4,5,17,27])
				->where(function($check) use ($options)
				{
					$check->where('requisition_documents.ticket_number', $options['ticket_val'])
						->where('requisition_documents.datepath', 'like', $options['date'].'%')
						->where('requisition_documents.timepath','like', $options['time'].'%')
						->where('requisition_documents.amount', $options['amount'])
						->whereNotNull('requisition_documents.ticket_number');
				})
				->where(function($query) use ($folio)
				{
					if ($folio != null) 
					{
						$query->whereNotIn('request_models.folio', [$folio]);
					}
				})
				->count();
			if ($requestCheck > 0) 
			{
				return $requestCheck;
			}
			$requestCheck = App\DocumentsPurchase::leftJoin('purchases','documents_purchases.idPurchase','purchases.idPurchase')
				->leftJoin('request_models','purchases.idFolio','request_models.folio')
				->whereIn('request_models.status',[2,3,4,5,10,11,12,18])
				->where(function($check) use ($options)
				{
					$check->where('documents_purchases.ticket_number', $options['ticket_val'])
						->where('documents_purchases.datepath', 'like', $options['date'].'%')
						->where('documents_purchases.timepath','like', $options['time'].'%')
						->where('documents_purchases.amount', $options['amount'])
						->whereNotNull('documents_purchases.ticket_number');
				})
				->where(function($query) use ($folio)
				{
					if ($folio != null) 
					{
						$query->whereNotIn('request_models.folio', [$folio]);
					}
				})
				->count();
			if($requestCheck > 0)
			{
				return $requestCheck;
			}
			$requestCheck = App\DocumentsPartials::leftJoin('partial_payments','documents_partials.partial_id','partial_payments.id')
				->leftJoin('purchases','partial_payments.purchase_id','purchases.idPurchase')
				->leftJoin('request_models','purchases.idFolio','request_models.folio')
				->whereIn('request_models.status',[2,3,4,5,10,11,12,18])
				->where(function($check) use ($options)
				{
					$check->where('documents_partials.ticket_number', $options['ticket_val'])
						->where('documents_partials.datepath',  'like',  $options['date'].'%')
						->where('documents_partials.timepath',  'like', $options['time'].'%')
						->where('documents_partials.amount', $options['amount'])
						->whereNotNull('documents_partials.ticket_number');
				})
				->where(function($query) use ($folio)
				{
					if ($folio != null) 
					{
						$query->whereNotIn('request_models.folio', [$folio]);
					}
				})
				->count();
			if($requestCheck > 0)
			{
				return $requestCheck;
			}
			$requestCheck = App\ExpensesDocuments::leftJoin('expenses_details','expenses_details.idExpensesDetail','expenses_documents.idExpensesDetail')
				->leftJoin('expenses','expenses_details.idExpenses','expenses.idExpenses')
				->leftJoin('request_models','expenses.idFolio','request_models.folio')
				->whereIn('request_models.status',[2,3,4,5,10,11,12,18])
				->where(function($check) use ($options)
				{
					$check->where('expenses_documents.ticket_number', $options['ticket_val'])
							->where('expenses_documents.date', 'like', $options['date'].'%')
							->where('expenses_documents.timepath','like', $options['time'].'%')
							->where('expenses_documents.amount', $options['amount'])
							->whereNotNull('expenses_documents.ticket_number');
				})
				->where(function($query) use ($folio)
				{
					if ($folio != null) 
					{
						$query->whereNotIn('request_models.folio', [$folio]);
					}
				})
				->count();
			if ($requestCheck > 0) 
			{
				return $requestCheck;
			}
			$requestCheck = App\ResourceDocument::leftJoin('resources','resources.idresource','resource_documents.resource_id')
				->leftJoin('request_models','resources.idFolio','request_models.folio')
				->whereIn('request_models.status',[2,3,4,5,10,11,12,17,18,27])
				->where(function($check) use ($options)
				{
					$check->where('resource_documents.ticket_number', $options['ticket_val'])
					->where('resource_documents.datepath', 'like', '%'.$options['date'].'%')
					->where('resource_documents.timepath','like' ,'%'.$options['time'].'%')
					->where('resource_documents.amount', $options['amount'])
					->whereNotNull('resource_documents.ticket_number');
				})
				->count();
			if($requestCheck > 0)
			{
				return $requestCheck;
			}
		}
		return $requestCheck;
	}
}
