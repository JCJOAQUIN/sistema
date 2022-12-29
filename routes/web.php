<?php
use Illuminate\Http\Request;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
#Route::get('/layout', 'HomeController@menu');

Route::get('/', function ()
{
	return redirect('/login');
});

Auth::routes();
/*
|--------------------------------------------------------------------------
| Parent Modules Routes
|--------------------------------------------------------------------------
*/
Route::get('/home', 'HomeController@index')->name('home');
Route::get('/table', 'HomeController@table')->name('home');
Route::get('/stamped-fail', 'HomeController@stamped')->name('stamped-fail');
Route::get('/cancelled-fail/{bill}', 'HomeController@cancelled')->name('cancelled-fail');
Route::get('/tutorial', 'HomeController@tutorial')->name('tutorial');
Route::get('/administration', 'HomeController@administration');
Route::get('/operation', 'HomeController@operation');
Route::get('/report', 'HomeController@report');
Route::get('/configuration', 'HomeController@configuration');
Route::get('/purchase-docs/{folios}', 'HomeController@purchase_docs');
Route::get('/tools', 'HomeController@tools');
Route::get('/update', 'HomeController@updateTax');
Route::get('/construction', 'HomeController@construction');
Route::get('/period/{bill}', function(App\Bill $bill)
{
	if($bill->status == 0 || $bill->status == 7)
	{
		$start_date   = $bill->nominaReceiver->laboralDateStart;
		$end_date     = $bill->nomina->paymentEndDate;
		$start        = new \Carbon\Carbon($start_date);
		$ending	      = new \Carbon\Carbon($end_date);
		$ending       = $ending->addDay();
		$years        = $start->diff($ending);
		$months_start = new \Carbon\Carbon($start->addYearsNoOverflow($years->format('%y')));
		$months       = $months_start->diff($ending);
		$days_start   = new \Carbon\Carbon($months_start->addMonthsNoOverflow($months->format('%m')));
		$days         = $days_start->diff($ending);
		$antiquity    = 'P';
		if($years->format('%y') > 0)
		{
			$antiquity .= $years->format('%y').'Y';
			if($months->format('%m') > 0)
			{
				$antiquity .= $months->format('%m').'M';
			}
			$antiquity .= $days->format('%d').'D';
		}
		else
		{
			if($months->format('%m') > 0)
			{
				$antiquity .= $months->format('%m').'M';
				$antiquity .= $days->format('%d').'D';
			}
			else
			{
				$week = floor($days->format('%d') / 7);
				$antiquity = 'P'.$week.'W';
			}
		}
		$receiver            = $bill->nominaReceiver;
		$receiver->antiquity = $antiquity;
		$receiver->save();
		return "Actualizado";
	}
	else
	{
		return "No";
	}
});

Route::post('/mobile-request','MobileRequestController@functionRequest')->name('mobile.request');

//Route::get('/sendpass', 'SendMailsPantigerController@index');

/*
|--------------------------------------------------------------------------
| Administration Routes
|--------------------------------------------------------------------------
*/
Route::prefix('/administration')->group(function()
{
	/*** Boardroom ***/
	Route::prefix('/general_services/boardroom')->name('boardroom.')->group(function ()
	{
		Route::get('/','AdministracionSalaJuntasController@index')->name('index');
		Route::get('/create','AdministracionSalaJuntasController@create')->name('create');
		Route::post('/store','AdministracionSalaJuntasController@store')->name('store');
		Route::get('/search','AdministracionSalaJuntasController@search')->name('search');
		Route::get('/edit/{id}','AdministracionSalaJuntasController@update')->name('update');
		Route::post('/save','AdministracionSalaJuntasController@save')->name('save');
		Route::get('/reservation','AdministracionSalaJuntasController@reservationSearch')->name('reservation.search');
		Route::get('/reservation/{id}','AdministracionSalaJuntasController@reservationBoardroom')->name('reservation.edit');
		Route::post('/reservation','AdministracionSalaJuntasController@storeReservationBoardroom')->name('reservation.update');
		Route::get('/administration','AdministracionSalaJuntasController@administrationSearch')->name('administration.search');
		Route::get('/administration/excel','AdministracionSalaJuntasController@exportReservations')->name('administration.export');
		Route::get('/follow/export','AdministracionSalaJuntasController@exportBoardroomFollow')->name('follow.export');
	});
	/*** Bill ***/
	Route::prefix('/billing')->name('bill.')->group(function ()
	{
		Route::get('/','AdministracionFacturacionController@Index')->name('index');
		/*Route::get('/pending','AdministracionFacturacionController@pending')->name('pending');
		Route::get('/pending/{bill}/edit','AdministracionFacturacionController@pendingEdit')->name('pending.edit');
		Route::post('/pending/{bill}/update','AdministracionFacturacionController@pendingUpdate')->name('pending.update');
		Route::get('/pending/{bill}/stamp','AdministracionFacturacionController@pendingStamp')->name('pending.stamp');
		Route::post('/pending/{bill}/pac','AdministracionFacturacionController@pendingPac')->name('pending.pac');*/
		Route::get('/sat','AdministracionFacturacionController@sat')->name('sat');
		Route::get('/stamped','AdministracionFacturacionController@stamped')->name('stamped');
		Route::get('/stamped/consolidated','AdministracionFacturacionController@reportStampedConsolidated')->name('stamped.report-consolidated');
		Route::get('/stamped/detailed','AdministracionFacturacionController@reportStampedDetailed')->name('stamped.report-detailed');
		Route::get('/stamped/massive','AdministracionFacturacionController@downloadDocuments')->name('stamped.massive');
		Route::get('/stamped/{bill}/view','AdministracionFacturacionController@stampedView')->name('stamped.view');
		Route::get('/stamped/pdf/{uuid}/download','AdministracionFacturacionController@downloadPDF')->name('stamped.download.pdf');
		Route::get('/stamped/xml/{uuid}/download','AdministracionFacturacionController@downloadXML')->name('stamped.download.xml');
		Route::get('/cancelled','AdministracionFacturacionController@cancelled')->name('cancelled');
		Route::get('/cancelled/consolidated','AdministracionFacturacionController@reportCancelledConsolidated')->name('cancelled.report-consolidated');
		Route::get('/cancelled/detailed','AdministracionFacturacionController@reportCancelledDetailed')->name('cancelled.report-detailed');
		Route::post('/{bill}/cancel','AdministracionFacturacionController@cancelBill')->name('cancel');
		Route::get('/cancelled/{bill}/view','AdministracionFacturacionController@cancelledView')->name('cancelled.view');
		Route::get('/cancelled/{bill}/status','AdministracionFacturacionController@cancelledStatusUpdate')->name('cancelled.status');
		Route::get('/cancelled/pdf/{uuid}/download','AdministracionFacturacionController@downloadCancelledPDF')->name('cancelled.download.pdf');
		Route::get('/cancelled/xml/{uuid}/download','AdministracionFacturacionController@downloadCancelledXML')->name('cancelled.download.xml');
		Route::get('/cfdi','AdministracionFacturacionController@cfdi')->name('cfdi');
		Route::post('/cfdi/related','AdministracionFacturacionController@cfdiRelated')->name('cfdi.related');
		Route::post('/cfdi/related/search','AdministracionFacturacionController@cfdiRelatedSearch')->name('cfdi.related.search');
		Route::post('/cfdi/save','AdministracionFacturacionController@cfdiSave')->name('cfdi.save');
		Route::post('/cfdi/{bill}/save','AdministracionFacturacionController@cfdiSaveSaved')->name('cfdi.save.saved');
		Route::post('/cfdi/stamp','AdministracionFacturacionController@cfdiStamp')->name('cfdi.stamp');
		Route::post('/cfdi/{bill}/stamp','AdministracionFacturacionController@cfdiStampSaved')->name('cfdi.stamp.saved');
		Route::get('/cfdi/pending','AdministracionFacturacionController@cfdiPending')->name('cfdi.pending');
		Route::get('/cfdi/pending/consolidated','AdministracionFacturacionController@reportPendingConsolidated')->name('cfdi.pending.report-consolidated');
		Route::get('/cfdi/pending/detailed','AdministracionFacturacionController@reportPendingDetailed')->name('cfdi.pending.report-detailed');
		Route::get('/cfdi/pending/{bill}/stamp','AdministracionFacturacionController@cfdiPendingStamp')->name('cfdi.pending.stamp');
		Route::get('/nomina/pending','AdministracionFacturacionController@nominaPending')->name('nomina.pending');
		Route::get('/nomina/export','AdministracionFacturacionController@exportNominaPending')->name('nomina.export');
		Route::get('/nomina/pending/{bill}/stamp','AdministracionFacturacionController@nominaPendingStamp')->name('nomina.pending.stamp');
		Route::post('/nomina/{bill}/stamp','AdministracionFacturacionController@nominaStampSaved')->name('nomina.stamp.saved');
		Route::post('/nomina/{bill}/save','AdministracionFacturacionController@nominaSaveSaved')->name('nomina.save.saved');
		Route::post('/nomina/{bill}/queue','AdministracionFacturacionController@nominaAddQueue')->name('nomina.add.queue');
		Route::post('/nomina/queue','AdministracionFacturacionController@nominaAddQueueMassive')->name('nomina.add.queue.massive');
	});

	/*** Budget Allocation ***/
	Route::prefix('/budget')->name('budget.')->group(function ()
	{
		Route::get('/','AdministrationBudgetController@index')->name('index');
		Route::get('/pending','AdministrationBudgetController@pending')->name('pending');
		Route::post('/store','AdministrationBudgetController@storeBudget')->name('store');
		Route::post('/massive','AdministrationBudgetController@massiveBudget')->name('massive');
		Route::get('/export','AdministrationBudgetController@pendingExport')->name('export');
		Route::get('/approved','AdministrationBudgetController@approvedExport')->name('export-approved');
		Route::get('/pending/{id}/review','AdministrationBudgetController@showReview')->where('id','[0-9]+')->name('review.edit');
		Route::get('/edit','AdministrationBudgetController@editBudget')->name('edit');
		Route::get('/edit/{id}/review','AdministrationBudgetController@showBudget')->where('id','[0-9]+')->name('show');
		Route::get('/view/{id}/review','AdministrationBudgetController@viewReview')->where('id','[0-9]+')->name('view');
		Route::put('/update/{id}','AdministrationBudgetController@updateBudget')->where('id','[0-9]+')->name('update');
	});

	/*** Budget ***/
	Route::prefix('/budgets')->group(function ()
	{
		Route::get('/administration','AdministracionPresupuestosController@administrationIndex')->name('budget.administration.index');
		Route::get('/administration/search','AdministracionPresupuestosController@administrationSearch')->name('budget.administration.search');
		Route::get('/administration/edit/{budget}','AdministracionPresupuestosController@administrationEdit')->name('budget.administration.edit');
		Route::get('/administration/download/{budget}','AdministracionPresupuestosController@downloadBudget')->name('budget.administration.download');
		Route::put('/administration/update/{budget}','AdministracionPresupuestosController@administrationUpdate')->name('budget.administration.update');
		Route::put('/administration/update-from-file/{budget}','AdministracionPresupuestosController@updateFromFile')->name('budget.administration.update-from-file');
		Route::get('/administration/create','AdministracionPresupuestosController@administrationCreate')->name('budget.administration.create');
		Route::post('/administration/download-upload-layout','AdministracionPresupuestosController@downloadUploadLayoutBudget')->name('budget.administration.download-upload-layout');
		Route::post('/administration/upload-file','AdministracionPresupuestosController@uploadFile')->name('budget.administration.upload-file');
		Route::get('/administration/all','AdministracionPresupuestosController@updateBudgets')->name('budget.administration.all');
		Route::get('/','AdministracionPresupuestosController@index')->name('budget.index');
		Route::get('/obra','AdministracionPresupuestosController@obraIndex')->name('obra.index');
		Route::get('/search','AdministracionPresupuestosController@search')->name('budget.search');
		//supplies
		Route::get('/create/supplies','AdministracionPresupuestosController@create')->name('supplies.create');
		Route::post('/create/supplies','AdministracionPresupuestosController@suppliesUpload')->name('supplies.create.send');
		Route::get('/create/supplies/validate/{budget_id}','AdministracionPresupuestosController@suppliesValidate')->name('supplies.create.validate');
		Route::post('/article/supplies/edit','AdministracionPresupuestosController@editSupplie')->name('supplies.article.edit');
		Route::post('/create/supplies/validate/finish','AdministracionPresupuestosController@finishSupplie')->name('supplies.finish');
		Route::get('/supplies/delete/{budget_id}','AdministracionPresupuestosController@SuppliesDelete')->where('id','[0-9]+')->name('supplies.delete');
		//budget
		Route::get('/create/budget','AdministracionPresupuestosController@createBudget')->name('budget.create');
		Route::post('/create/budget','AdministracionPresupuestosController@budgetUpload')->name('budget.create.send');
		Route::get('/create/budget/validate/{budget_id}','AdministracionPresupuestosController@budgetValidate')->name('budget.create.validate');
		Route::post('/article/budget/edit','AdministracionPresupuestosController@editBudget')->name('budget.article.edit');
		Route::post('/create/budget/validate/finish','AdministracionPresupuestosController@finishBudget')->name('budget.finish');
		Route::get('/delete/{budget_id}','AdministracionPresupuestosController@BudgetsDelete')->where('id','[0-9]+')->name('budget.delete');
		//breakdown_wages
		Route::get('/create/breakdown_wages','AdministracionPresupuestosController@createBreakdownWages')->name('BreakdownWages.create');
		Route::post('/create/breakdown_wages','AdministracionPresupuestosController@sendBreakdownWages')->name('BreakdownWages.create.send');
		Route::get('/create/breakdown_wages/validate/{budget_id}','AdministracionPresupuestosController@validateBreakdownWages')->name('BreakdownWages.create.validate');
		Route::post('/article/breakdown_wages/edit','AdministracionPresupuestosController@editBreakdownWages')->name('BreakdownWages.article.edit');
		Route::post('/create/breakdown_wages/validate/finish','AdministracionPresupuestosController@finishBreakdownWages')->name('BreakdownWages.finish');
		Route::get('/breakdown_wages/delete/{budget_id}','AdministracionPresupuestosController@BreakdownWagesDelete')->name('BreakdownWages.delete');
		//unit_prices
		Route::get('/create/unit_prices','AdministracionPresupuestosController@UnitPricesCreate')->name('UnitPrices.create');
		Route::post('/create/unit_prices','AdministracionPresupuestosController@UnitPricesSend')->name('UnitPrices.create.send');
		Route::get('/create/unit_prices/validate/{budget_id}','AdministracionPresupuestosController@UnitPricesValidate')->name('UnitPrices.create.validate');
		Route::post('/article/unit_prices/edit','AdministracionPresupuestosController@UnitPricesEditArt')->name('UnitPrices.article.edit');
		Route::post('/create/unit_prices/validate/finish','AdministracionPresupuestosController@UnitPricesFinish')->name('UnitPrices.finish');
		Route::get('/unit_prices/delete/{budget_id}','AdministracionPresupuestosController@UnitPricesDelete')->where('id','[0-9]+')->name('UnitPrices.delete');
		//obra_program
		Route::get('/create/obra_program','AdministracionPresupuestosController@ObraProgramCreate')->name('ObraProgram.create');
		Route::post('/create/obra_program','AdministracionPresupuestosController@ObraProgramSend')->name('ObraProgram.create.send');
		Route::get('/create/obra_program/validate/{budget_id}','AdministracionPresupuestosController@ObraProgramValidate')->name('ObraProgram.create.validate');
		Route::post('/create/obra_program/paginate_arts_edit','AdministracionPresupuestosController@ObraProgramPaginateArtsEdit')->name('ObraProgram.pagintate.arts.edit');
		Route::post('/article/obra_program/edit','AdministracionPresupuestosController@ObraProgramArtEdit')->name('ObraProgram.article.edit');
		Route::post('/create/obra_program/validate/finish','AdministracionPresupuestosController@ObraProgramFinish')->name('ObraProgram.finish');
		Route::get('/obra_program/delete/{budget_id}','AdministracionPresupuestosController@ObraProgramDelete')->where('id','[0-9]+')->name('ObraProgram.delete');
		//sobrecosto
		Route::get('/create/sobrecosto','AdministracionPresupuestosController@SobrecostoCreate')->name('Sobrecosto.create');
		Route::post('/create/sobrecosto','AdministracionPresupuestosController@SobrecostoSend')->name('Sobrecosto.create.send');
		Route::get('/create/sobrecosto/validate/{budget_id}','AdministracionPresupuestosController@SobrecostoValidate')->name('Sobrecosto.create.validate');
		Route::post('/create/sobrecosto/validate/{budget_id}','AdministracionPresupuestosController@SobrecostoValidate')->name('Sobrecosto.create.validate');
		Route::get('/sobrecosto/delete/{budget_id}','AdministracionPresupuestosController@SobrecostoDelete')->where('id','[0-9]+')->name('Sobrecosto.delete');
		Route::get('/create/sobrecosto/status','AdministracionPresupuestosController@SobrecostoStatus')->name('Sobrecosto.status');
		Route::prefix('/create/sobrecosto/save')->group(function ()
		{
			Route::post('/generales/{budget_id}','AdministracionPresupuestosController@SobrecostoGeneralesSave')->name('Sobrecosto.save.generales');
			Route::get('/generales/{budget_id}','AdministracionPresupuestosController@SobrecostoGeneralesSave')->name('Sobrecosto.save.generales');
			Route::post('/datos_obra/{budget_id}','AdministracionPresupuestosController@SobrecostoDatosObraSave')->name('Sobrecosto.save.datosObra');
			Route::get('/datos_obra/{budget_id}','AdministracionPresupuestosController@SobrecostoDatosObraSave')->name('Sobrecosto.save.datosObra');
			Route::post('/programa/{budget_id}','AdministracionPresupuestosController@SobrecostoProgramaSave')->name('Sobrecosto.save.programa');
			Route::get('/programa/{budget_id}','AdministracionPresupuestosController@SobrecostoProgramaSave')->name('Sobrecosto.save.programa');
			Route::post('/plantilla/{budget_id}','AdministracionPresupuestosController@SobrecostoPlantillaSave')->name('Sobrecosto.save.plantilla');
			Route::get('/plantilla/{budget_id}','AdministracionPresupuestosController@SobrecostoPlantillaSave')->name('Sobrecosto.save.plantilla');
			Route::post('/indirectos_desglosados/{budget_id}','AdministracionPresupuestosController@SobrecostoIndirectosDesglosadosSave')->name('Sobrecosto.save.indirectosDesglosados');
			Route::get('/indirectos_desglosados/{budget_id}','AdministracionPresupuestosController@SobrecostoIndirectosDesglosadosSave')->name('Sobrecosto.save.indirectosDesglosados');
			Route::post('/resumen_indirectos/{budget_id}','AdministracionPresupuestosController@SobrecostoResumenIndirectosSave')->name('Sobrecosto.save.resumenIndirectos');
			Route::get('/resumen_indirectos/{budget_id}','AdministracionPresupuestosController@SobrecostoResumenIndirectosSave')->name('Sobrecosto.save.resumenIndirectos');
			Route::post('/pers_tecnico/{budget_id}','AdministracionPresupuestosController@SobrecostoPersTecnicoSave')->name('Sobrecosto.save.persTecnico');
			Route::get('/pers_tecnico/{budget_id}','AdministracionPresupuestosController@SobrecostoPersTecnicoSave')->name('Sobrecosto.save.persTecnico');
			Route::post('/pers_tecnico_salario/{budget_id}','AdministracionPresupuestosController@SobrecostoPersTecnicoSalarioSave')->name('Sobrecosto.save.persTecnicoSalario');
			Route::get('/pers_tecnico_salario/{budget_id}','AdministracionPresupuestosController@SobrecostoPersTecnicoSalarioSave')->name('Sobrecosto.save.persTecnicoSalario');
			Route::post('/finan_c_horizontal/{budget_id}','AdministracionPresupuestosController@SobrecostofinanCHorizontalSave')->name('Sobrecosto.save.finanCHorizontal');
			Route::get('/finan_c_horizontal/{budget_id}','AdministracionPresupuestosController@SobrecostofinanCHorizontalSave')->name('Sobrecosto.save.finanCHorizontal');
			Route::post('/utilidad/{budget_id}','AdministracionPresupuestosController@SobrecostoUtilidadSave')->name('Sobrecosto.save.utilidad');
			Route::get('/utilidad/{budget_id}','AdministracionPresupuestosController@SobrecostoUtilidadSave')->name('Sobrecosto.save.utilidad');
			Route::post('/cargos_adicionales/{budget_id}','AdministracionPresupuestosController@SobrecostoCargosAdicionalesSave')->name('Sobrecosto.save.cargosAdicionales');
			Route::get('/cargos_adicionales/{budget_id}','AdministracionPresupuestosController@SobrecostoCargosAdicionalesSave')->name('Sobrecosto.save.cargosAdicionales');
			Route::post('/resumen/{budget_id}','AdministracionPresupuestosController@SobrecostoResumenSave')->name('Sobrecosto.save.resumen');
			Route::get('/resumen/{budget_id}','AdministracionPresupuestosController@SobrecostoResumenSave')->name('Sobrecosto.save.resumen');
			Route::post('/documentacion/{budget_id}','AdministracionPresupuestosController@SobrecostoDocumentacionSave')->name('Sobrecosto.save.documentacion');
			Route::get('/documentacion/{budget_id}','AdministracionPresupuestosController@SobrecostoDocumentacionSave')->name('Sobrecosto.save.documentacion');
		});
		//exel
		Route::get('/breakdown_wages/excel/{id}','AdministracionPresupuestosController@BreakdownWagesExcel')->name('BreakdownWages.excel');
		Route::get('/supplies/excel/{id}','AdministracionPresupuestosController@SuppliesExcel')->name('supplies.excel');
		Route::get('/budget/excel/{id}','AdministracionPresupuestosController@BudgetExcel')->name('budget.excel');
		Route::get('/unit_prices/excel/{id}','AdministracionPresupuestosController@UnitPricesExcel')->name('UnitPrices.excel');
		Route::get('/obra_program/excel/{id}','AdministracionPresupuestosController@ObraProgramExcel')->name('ObraProgram.excel');
		Route::get('/sobrecosto/excel/{id}','AdministracionPresupuestosController@SobrecostoExcel')->name('Sobrecosto.excel');
		// paginate search module
		Route::get('/search/paginate_search','AdministracionPresupuestosController@paginate_search_supplies')->name('supplies.search.paginate');
		Route::get('/supplies/search/paginate_search','AdministracionPresupuestosController@paginate_search_budget')->name('budget.search.paginate');
		Route::get('/breakdown_wages/search/paginate_search','AdministracionPresupuestosController@paginate_search_BreakdownWages')->name('breakdownWages.search.paginate');
		Route::get('/unit_prices/search/paginate_search','AdministracionPresupuestosController@UnitPricesFinishPaginateSearch')->name('UnitPrices.search.paginate');
		Route::get('/unit_prices/search/obra_program','AdministracionPresupuestosController@ObraPaginateSearch')->name('Obra.search.paginate');
		Route::get('/unit_prices/search/sobrecosto','AdministracionPresupuestosController@SobrecostoPaginateSearch')->name('Sobrecosto.search.paginate');
		//paginate arts
		Route::get('/create/supplies/paginate_arts','AdministracionPresupuestosController@paginate_supplies_arts')->name('supplies.paginate.arts');
		Route::get('/create/budget/paginate_arts','AdministracionPresupuestosController@paginate_budget_arts')->name('budget.paginate.arts');
		Route::get('/create/breakdown_wages/paginate_arts','AdministracionPresupuestosController@paginate_breakdown_wages_arts')->name('BreakdownWages.paginate.arts');
		Route::get('/create/unit_prices/paginate_arts','AdministracionPresupuestosController@UnitPricesPaginateArts')->name('UnitPrices.paginate.arts');
		Route::get('/create/obra_program/paginate_arts','AdministracionPresupuestosController@ObraProgramPaginateArts')->name('ObraProgram.paginate.arts');
	});

	/*** Computer ***/
	Route::prefix('/computer')->name('computer.')->group(function()
	{
		Route::get('/search','AdministracionComputoController@search')->name('search');
		Route::get('/export/follow','AdministracionComputoController@exportFollow')->name('export.follow');
		Route::get('/export/review','AdministracionComputoController@exportReview')->name('export.review');
		Route::get('/export/authorization','AdministracionComputoController@exportAuthorize')->name('export.authorization');
		Route::get('/export/delivery','AdministracionComputoController@exportDelivery')->name('export.delivery');
		Route::get('/search/{id}/destroy', 'AdministracionComputoController@destroy')->where('id','[0-9]+')->name('destroy2');
		Route::get('/review','AdministracionComputoController@review')->name('review');
		Route::get('/review/{id}/edit', 'AdministracionComputoController@showReview')->where('id','[0-9]+')->name('review.edit');
		Route::put('/review/{id}', 'AdministracionComputoController@updateReview')->where('id','[0-9]+')->name('review.update');
		Route::post('/unsent','AdministracionComputoController@unsent')->name('unsent');
		Route::get('/follow/{id}/edit', 'AdministracionComputoController@follow')->where('id','[0-9]+')->name('follow.edit');
		Route::put('/unsent/{id}', 'AdministracionComputoController@updateFollow')->where('id','[0-9]+')->name('follow.update');
		Route::put('/unsentfollow/{id}', 'AdministracionComputoController@updateUnsentFollow')->where('id','[0-9]+')->name('follow.updateunsent');
		Route::get('/authorization','AdministracionComputoController@authorization')->name('authorization');
		Route::get('/authorization/{id}/edit','AdministracionComputoController@showAuthorize')->where('id','[0-9]+')->name('authorization.edit');
		Route::put('/authorization/{id}','AdministracionComputoController@updateAuthorize')->where('id','[0-9]+')->name('authorization.update');
		Route::get('/delivery','AdministracionComputoController@delivery')->name('delivery');
		Route::get('/delivery/{id}/edit','AdministracionComputoController@showDelivery')->where('id','[0-9]+')->name('delivery.edit');
		Route::put('/delivery/{id}','AdministracionComputoController@updateDelivery')->where('id','[0-9]+')->name('delivery.update');
		Route::post('/create/software', 'AdministracionComputoController@getSoftware')->name('create.software'); 
		Route::get('/create/{id}','AdministracionComputoController@newRequest')->where('id','[0-9]+')->name('create.new');
		Route::post('/validate','AdministracionComputoController@validation')->name('validation');
		Route::get('/create/account', 'AdministracionComputoController@getAccount')->name('create.account'); 
		Route::post('/requestsdelivery','AdministracionComputoController@requestsdelivery')->name('requestsdelivery');
	});
	Route::resource('/computer','AdministracionComputoController')->except(['show','edit','update','destroy']);

	/*** Employees ***/
	Route::prefix('/employees')->name('administration.employee.')->group(function() 
	{
		Route::get('/','AdministrationEmployeeController@index')->name('index');
		Route::get('/pending','AdministrationEmployeeController@pending')->name('pending');
		Route::get('/pending-export','AdministrationEmployeeController@pendingExport')->name('pending-export');
		Route::get('/pending/{employee}/{request_model}','AdministrationEmployeeController@editEmployee')->name('edit-employee');
		Route::put('/pending/{employee}/update','AdministrationEmployeeController@updateEmployee')->name('update-employee');
		Route::put('/pending/approved','AdministrationEmployeeController@approvedEmployee')->name('approved-employee');
		Route::get('/approved','AdministrationEmployeeController@approved')->name('approved');
		Route::get('/approved/{employee}','AdministrationEmployeeController@approvedView')->name('approved-view');
		Route::get('/approved-massive','AdministrationEmployeeController@approvedMassive')->name('approved-massive');
		Route::post('/approved-massive/upload','AdministrationEmployeeController@massiveUpload')->name('massive-upload');
		Route::post('/approved-massive/upload/continue','AdministrationEmployeeController@massiveContinue')->name('massive-continue');
		Route::post('/approved-massive/upload/cancel','AdministrationEmployeeController@massiveCancel')->name('massive-cancel');
		Route::post('/validate-curp','AdministrationEmployeeController@curpValidate')->name('validate-curp');
		Route::post('/validate-rfc','AdministrationEmployeeController@rfcValidate')->name('validate-rfc');
	});

	/*** Expenses ***/
	Route::prefix('/expenses')->name('expenses.')->group(function()
	{
		Route::get('/search','AdministracionGastosController@search')->name('search');
		Route::get('/export/follow','AdministracionGastosController@exportFollow')->name('export.follow');
		Route::get('/export/review','AdministracionGastosController@exportReview')->name('export.review');
		Route::get('/export/authorization','AdministracionGastosController@exportAuthorize')->name('export.authorization');
		Route::get('/search/{id}/destroy', 'AdministracionGastosController@destroy')->where('id','[0-9]+')->name('destroy2');
		Route::get('/review','AdministracionGastosController@review')->name('review');
		Route::get('/review/{id}/edit', 'AdministracionGastosController@showReview')->where('id','[0-9]+')->name('review.edit');
		Route::put('/review/{id}', 'AdministracionGastosController@updateReview')->where('id','[0-9]+')->name('review.update');
		Route::post('/unsent','AdministracionGastosController@unsent')->name('unsent');
		Route::get('/follow/{id}/edit', 'AdministracionGastosController@follow')->where('id','[0-9]+')->name('follow.edit');
		Route::put('/unsent/{id}', 'AdministracionGastosController@updateFollow')->where('id','[0-9]+')->name('follow.update');
		Route::put('/unsentfollow/{id}', 'AdministracionGastosController@updateUnsentFollow')->where('id','[0-9]+')->name('follow.updateunsent');
		Route::get('/authorization','AdministracionGastosController@authorization')->name('authorization');
		Route::get('/authorization/{id}/edit','AdministracionGastosController@showAuthorize')->where('id','[0-9]+')->name('authorization.edit');
		Route::put('/authorization/{id}','AdministracionGastosController@updateAuthorize')->where('id','[0-9]+')->name('authorization.update');
		Route::get('/search/user', 'AdministracionGastosController@getEmployee')->name('search.user');
		Route::post('/getresourcedetail', 'AdministracionGastosController@getResourceDetail')->name('resource.detail');
		Route::post('/getresourcetotal', 'AdministracionGastosController@getResourceTotal')->name('resource.total'); 
		Route::post('/getresourcedetaildelete', 'AdministracionGastosController@getResourceDetailDelete')->name('resource.detaildelete'); 
		Route::post('/search/bank', 'AdministracionGastosController@getBanks')->name('search.bank');
		Route::get('/create/{id}','AdministracionGastosController@newRequest')->where('id','[0-9]+')->name('create.new');
		Route::post('/resource','AdministracionGastosController@getResource')->name('resource');
		Route::post('/dates','AdministracionGastosController@getDates')->name('dates');
		Route::post('/upload','AdministracionGastosController@uploader')->name('upload');
		Route::put('/code/{id}','AdministracionGastosController@updateCode')->name('code');
		Route::post('/validation-document','AdministracionGastosController@validationDocs')->name('validation-document');
	});
	Route::resource('/expenses','AdministracionGastosController')->except(['show','edit','update','destroy']);

	/*** Finance ***/
	Route::prefix('/finance')->name('finance.')->group(function()
	{
		Route::post('/unsent','AdministrationFinanceController@storeOnly')->name('unsent');
		Route::put('/unsent/{id}','AdministrationFinanceController@updateOnly')->name('update.only');
		Route::get('/search','AdministrationFinanceController@search')->name('search');
		Route::get('/review','AdministrationFinanceController@review')->name('review');
		Route::put('/review/{id}', 'AdministrationFinanceController@updateReview')->where('id','[0-9]+')->name('review.update');
		Route::get('/review/{id}/edit', 'AdministrationFinanceController@showReview')->where('id','[0-9]+')->name('review.edit');
		Route::get('/authorization','AdministrationFinanceController@authorization')->name('authorization');
		Route::put('/authorization/{id}', 'AdministrationFinanceController@updateAuthorize')->where('id','[0-9]+')->name('authorization.update');
		Route::get('/authorization/{id}/edit', 'AdministrationFinanceController@showAuthorize')->where('id','[0-9]+')->name('authorization.edit');
		Route::get('/create/{id}', 'AdministrationFinanceController@newRequest')->where('id','[0-9]+')->name('create.new');
		Route::get('/export/{action}', 'AdministrationFinanceController@export')->name('export');
		Route::get('/check-budget','AdministrationFinanceController@checkBudget')->name('check-budget');
	});
	Route::resource('/finance','AdministrationFinanceController');

	/*** Flights Lodging ***/
	Route::prefix('/flights-lodging')->name('flights-lodging.')->group(function()
	{
		Route::get('/','AdministrationFlightsLodgingController@index')->name('index');
		Route::get('upload','AdministrationFlightsLodgingController@uploader')->name('upload');
		Route::get('/create','AdministrationFlightsLodgingController@create')->name('create');
		Route::post('/store','AdministrationFlightsLodgingController@store')->name('store');
		Route::post('/unsend','AdministrationFlightsLodgingController@unsend')->name('unsend');
		Route::post('/loadDocument/{request_model}', 'AdministrationFlightsLodgingController@loadNewDocument')->name('follow.loadNewDocument');
		Route::post('/update/{id}/unsend','AdministrationFlightsLodgingController@updateUnsend')->name('update.unsend');
		Route::post('/review/{id}/send','AdministrationFlightsLodgingController@sendToReview')->name('sendToReview');
		Route::get('/search','AdministrationFlightsLodgingController@search')->name('search');
		Route::get('/create/{id}/new', 'AdministrationFlightsLodgingController@newFlight')->name('follow.newFlight');
		Route::get('/search/{id}/edit', 'AdministrationFlightsLodgingController@edit')->name('follow.edit');
		Route::get('/review','AdministrationFlightsLodgingController@review')->name('review');
		Route::get('/review/{id}/edit', 'AdministrationFlightsLodgingController@showReview')->where('id','[0-9]+')->name('review.edit');
		Route::get('/authorization','AdministrationFlightsLodgingController@authorization')->name('authorization');
		Route::post('/uploader','AdministrationFlightsLodgingController@uploader')->name('uploader');
		Route::get('/export','AdministrationFlightsLodgingController@export')->name('export');
		Route::get('/review','AdministrationFlightsLodgingController@review')->name('review');
		Route::get('/authorization/{id}/edit', 'AdministrationFlightsLodgingController@showAuthorization')->where('id','[0-9]+')->name('authorization.edit');
		Route::post('/changeStatus/{submodule}', 'AdministrationFlightsLodgingController@changeStatus')->name('changeStatus');
		Route::get('/review/{id}/details', 'AdministrationFlightsLodgingController@details')->where('id','[0-9]+')->name('details');
		Route::get('/export-pdf/{flight_request}','AdministrationFlightsLodgingController@exportPdf')->name('export-pdf');
	});

	/*** Income Routes ***/
	Route::prefix('/income')->name('income.')->group(function()
	{
		Route::put('/updatebill/{id}','AdministracionIngresosController@updateBill')->name('updatebill');
		Route::get('/prefactura/{bill}','AdministracionIngresosController@prefactura')->name('prefactura');
		Route::get('/search','AdministracionIngresosController@search')->name('search');
		Route::get('/export/follow','AdministracionIngresosController@exportFollow')->name('export.follow');
		Route::get('/export/review','AdministracionIngresosController@exportReview')->name('export.review');
		Route::get('/export/authorization','AdministracionIngresosController@exportAuthorize')->name('export.authorization');
		Route::get('/export/authorized','AdministracionIngresosController@exportAuthorized')->name('export.authorized');
		Route::get('/search/{id}/destroy', 'AdministracionIngresosController@destroy')->where('id','[0-9]+')->name('destroy2');
		Route::get('/review','AdministracionIngresosController@review')->name('review');
		Route::get('/review/{id}/edit', 'AdministracionIngresosController@showReview')->where('id','[0-9]+')->name('review.edit');
		Route::put('/review/{id}', 'AdministracionIngresosController@updateReview')->where('id','[0-9]+')->name('review.update');
		Route::post('/unsent','AdministracionIngresosController@unsent')->name('unsent');
		Route::get('/follow/{id}/edit', 'AdministracionIngresosController@follow')->where('id','[0-9]+')->name('follow.edit');
		Route::put('/unsent/{id}', 'AdministracionIngresosController@updateFollow')->where('id','[0-9]+')->name('follow.update');
		Route::put('/unsentfollow/{id}', 'AdministracionIngresosController@updateUnsentFollow')->where('id','[0-9]+')->name('follow.updateunsent');
		Route::get('/authorization','AdministracionIngresosController@authorization')->name('authorization');
		Route::get('/authorization/{id}/edit','AdministracionIngresosController@showAuthorize')->where('id','[0-9]+')->name('authorization.edit');
		Route::put('/authorization/{id}','AdministracionIngresosController@updateAuthorize')->where('id','[0-9]+')->name('authorization.update');
		Route::post('/create/client', 'AdministracionIngresosController@getClients')->name('create.client'); 
		Route::get('/create/{id}','AdministracionIngresosController@newRequest')->where('id','[0-9]+')->name('create.new');
		Route::get('/create/{id}/{createChild}','AdministracionIngresosController@newRequest')->where('id','[0-9]+')->name('create.newchild');
		Route::post('/upload','AdministracionIngresosController@uploader')->name('upload');
		Route::put('/upload-documents/{id}','AdministracionIngresosController@uploadDocuments')->name('upload-documents');
		Route::get('/document/download/{id}','AdministracionIngresosController@downloadDocument')->name('download.document');
		Route::post('/search/bank', 'AdministracionIngresosController@getBanks')->name('search.bank');
		Route::post('/client/validate','AdministracionIngresosController@validation')->name('client.validation');
		Route::get('/validation','AdministracionIngresosController@validationPayment')->name('validation');
		Route::get('/validation/{id}','AdministracionIngresosController@validationPaymentShow')->name('validation.edit');
		Route::post('/validation/store','AdministracionIngresosController@validationPaymentStore')->name('validation.store');
		Route::get('/projection','AdministracionIngresosController@projection')->name('projection');
		Route::get('/projection/income/{requestModel}','AdministracionIngresosController@projectionIncome')->name('projection.income');
		Route::get('/projection/income/{requestModel}/{bill}','AdministracionIngresosController@projectionIncomeBill')->name('projection.income.bill');
		Route::post('/projection/income/save/{submodule_id}','AdministracionIngresosController@projectionIncomeSave')->name('projection.income.save');
		Route::post('/projection/no-fiscal/save','AdministracionIngresosController@projectionIncomeNFSave')->name('projection.income.nf.save');
		Route::post('/projection/detail','AdministracionIngresosController@projectionDetail')->name('projection.detail');
		Route::post('/projection/replicate','AdministracionIngresosController@projectionReplicate')->name('projection.replicate');
		Route::post('/projection/detail/nf','AdministracionIngresosController@projectionDetailNf')->name('projection.detailnf');
		Route::post('/catalogue/product','AdministracionIngresosController@catProdServ')->name('catalogue.product');
		Route::post('/catalogue/unity','AdministracionIngresosController@catUnity')->name('catalogue.unity');
		Route::put('/add-concept/{id}','AdministracionIngresosController@addConcept')->name('add-concept');
		Route::get('/bad/{id}','AdministracionIngresosController@bad')->name('projection.bad');
	});
	Route::resource('/income','AdministracionIngresosController')->except(['show','edit','update','destroy']);

	/*** Internal control ***/
	Route::prefix('/internal_control')->name('internal_control.')->group(function()
	{
		Route::get('/follow','AdministracionInternalControlController@search')->name('search');
		Route::get('/export','AdministracionInternalControlController@export')->name('export');
		Route::get('/delete/{id}', 'AdministracionInternalControlController@destroy')->name('delete');
		Route::post('/masive', 'AdministracionInternalControlController@storeMasive')->name('store_masive');
		Route::get('/download_control',function()
		{
			return \Storage::disk('reserved')->download('/massive_requisition/plantilla_control_interno.xlsm');
		})->name('download-control');
	});
	Route::resource('/internal_control','AdministracionInternalControlController');

	/*** Loan ***/
	Route::prefix('/loan')->name('loan.')->group(function()
	{
		Route::get('/search','AdministracionPrestamoController@search')->name('search');
		Route::get('/export/follow','AdministracionPrestamoController@exportFollow')->name('export.follow');
		Route::get('/export/review','AdministracionPrestamoController@exportReview')->name('export.review');
		Route::get('/export/authorization','AdministracionPrestamoController@exportAuthorize')->name('export.authorization');
		Route::get('/search/request','AdministracionPrestamoController@getRequest')->name('search.request');
		Route::get('/search/getrequestpage','AdministracionPrestamoController@getRequestPage')->name('search.getrequestpage');
		Route::get('/search/{id}/destroy', 'AdministracionPrestamoController@destroy')->where('id','[0-9]+')->name('destroy2');
		Route::get('/review','AdministracionPrestamoController@review')->name('review');
		Route::get('/review/{id}/edit', 'AdministracionPrestamoController@showReview')->where('id','[0-9]+')->name('review.edit');
		Route::put('/review/{id}', 'AdministracionPrestamoController@updateReview')->where('id','[0-9]+')->name('review.update');
		Route::post('/unsent','AdministracionPrestamoController@unsent')->name('unsent');
		Route::get('/follow/{id}/edit', 'AdministracionPrestamoController@follow')->where('id','[0-9]+')->name('follow.edit');
		Route::put('/unsent/{id}', 'AdministracionPrestamoController@updateFollow')->where('id','[0-9]+')->name('follow.update');
		Route::put('/unsentfollow/{id}', 'AdministracionPrestamoController@updateUnsentFollow')->where('id','[0-9]+')->name('follow.updateunsent');
		Route::get('/authorization','AdministracionPrestamoController@authorization')->name('authorization');
		Route::get('/authorization/document','AdministracionPrestamoController@document')->name('authorization.document');
		Route::get('/authorization/document/download/{id}','AdministracionPrestamoController@downloadDocument')->name('authorization.downloaddocument');
		Route::get('/authorization/{id}/edit','AdministracionPrestamoController@showAuthorize')->where('id','[0-9]+')->name('authorization.edit');
		Route::put('/authorization/{id}','AdministracionPrestamoController@updateAuthorize')->where('id','[0-9]+')->name('authorization.update');
		Route::post('/search/user', 'AdministracionPrestamoController@getEmployee')->name('search.user');
		Route::get('/create/account', 'AdministracionPrestamoController@getAccount')->name('create.account'); 
		Route::post('/search/bank', 'AdministracionPrestamoController@getBanks')->name('search.bank');
		Route::get('/create/{id}','AdministracionPrestamoController@newRequest')->where('id','[0-9]+')->name('create.new');
		Route::post('/upload','AdministracionPrestamoController@uploader')->name('upload');
	});
	Route::resource('/loan','AdministracionPrestamoController')->except(['show','edit','update','destroy']);

	/*** Movements between accounts ***/
	Route::prefix('/movements-accounts')->name('movements-accounts.')->group(function()
	{
		Route::get('/create/adjustment','AdministracionMovimientosController@adjustment')->name('adjustment');
		Route::post('/create/adjustment/getdetail', 'AdministracionMovimientosController@getDetailRequest')->name('adjustment.create.detailrequest'); 
		Route::post('/create/adjustment/store','AdministracionMovimientosController@storeAdjustment')->name('adjustment.store');
		Route::post('/create/adjustment/unsent','AdministracionMovimientosController@unsentAdjustment')->name('adjustment.unsent');
		Route::put('/follow/adjustment/update/{id}','AdministracionMovimientosController@updateFollowAdjustment')->name('adjustment.follow.update');
		Route::put('/follow/adjustment/unsent/{id}','AdministracionMovimientosController@unsentFollowAdjustment')->name('adjustment.follow.unsent');
		Route::put('/review/adjustment/update/{id}','AdministracionMovimientosController@updateReviewAdjustment')->name('adjustment.updateReview');
		Route::get('/create/loan','AdministracionMovimientosController@loan')->name('loan');
		Route::post('/create/loan/store','AdministracionMovimientosController@storeLoan')->name('loan.store');
		Route::post('/create/loan/unsent','AdministracionMovimientosController@unsentLoan')->name('loan.unsent');
		Route::put('/follow/loan/update/{id}','AdministracionMovimientosController@updateFollowLoan')->name('loan.follow.update');
		Route::put('/follow/loan/unsent/{id}','AdministracionMovimientosController@unsentFollowLoan')->name('loan.follow.unsent');
		Route::put('/review/loan/update/{id}','AdministracionMovimientosController@updateReviewLoan')->name('loan.updateReview');
		Route::get('/create/purchase','AdministracionMovimientosController@purchase')->name('purchase');
		Route::post('/create/purchase/store','AdministracionMovimientosController@storePurchase')->name('purchase.store');
		Route::post('/create/purchase/unsent','AdministracionMovimientosController@unsentPurchase')->name('purchase.unsent');
		Route::put('/follow/purchase/update/{id}','AdministracionMovimientosController@updateFollowPurchase')->name('purchase.follow.update');
		Route::put('/follow/purchase/unsent/{id}','AdministracionMovimientosController@unsentFollowPurchase')->name('purchase.follow.unsent');
		Route::put('/review/purchase/update/{id}','AdministracionMovimientosController@updateReviewPurchase')->name('purchase.updateReview');
		Route::post('/search/bank', 'AdministracionMovimientosController@getBanks')->name('search.bank');
		Route::get('/create/groups','AdministracionMovimientosController@groups')->name('groups');
		Route::post('/create/provider', 'AdministracionMovimientosController@getProviders')->name('create.provider'); 
		Route::post('/create/groups/store','AdministracionMovimientosController@storeGroups')->name('groups.store');
		Route::post('/create/groups/unsent','AdministracionMovimientosController@unsentGroups')->name('groups.unsent');
		Route::put('/follow/groups/update/{id}','AdministracionMovimientosController@updateFollowGroups')->name('groups.follow.update');
		Route::put('/follow/groups/unsent/{id}','AdministracionMovimientosController@unsentFollowGroups')->name('groups.follow.unsent');
		Route::put('/review/groups/update/{id}','AdministracionMovimientosController@updateReviewGroups')->name('groups.updateReview');
		Route::get('/create/movements','AdministracionMovimientosController@movements')->name('movements');
		Route::post('/create/movements/store','AdministracionMovimientosController@storeMovements')->name('movements.store');
		Route::post('/create/movements/unsent','AdministracionMovimientosController@unsentMovements')->name('movements.unsent');
		Route::put('/follow/movements/update/{id}','AdministracionMovimientosController@updateFollowMovements')->name('movements.follow.update');
		Route::put('/follow/movements/unsent/{id}','AdministracionMovimientosController@unsentFollowMovements')->name('movements.follow.unsent');
		Route::put('/review/movements/update/{id}','AdministracionMovimientosController@updateReviewMovements')->name('movements.updateReview');
		Route::post('/upload','AdministracionMovimientosController@uploader')->name('upload');
		Route::put('/updatedocuments/{id}','AdministracionMovimientosController@updateDocuments')->name('update.documents');
		Route::get('/follow','AdministracionMovimientosController@search')->name('search');
		Route::get('/follow/excel','AdministracionMovimientosController@followExcel')->name('follow.excel');
		Route::get('/review/excel','AdministracionMovimientosController@reviewExcel')->name('review.excel');
		Route::get('/authorization/excel','AdministracionMovimientosController@authorizationExcel')->name('authorization.excel');
		Route::get('/billing/excel','AdministracionMovimientosController@billingExcel')->name('billing.excel');
		Route::get('/follow/{id}','AdministracionMovimientosController@follow')->name('follow.edit');
		Route::get('/review','AdministracionMovimientosController@review')->name('review');
		Route::get('/review/{id}/edit', 'AdministracionMovimientosController@showReview')->where('id','[0-9]+')->name('review.edit');
		Route::put('/review/{id}', 'AdministracionMovimientosController@updateReview')->where('id','[0-9]+')->name('review.update');
		Route::get('/authorization','AdministracionMovimientosController@authorization')->name('authorization');
		Route::get('/authorization/{id}/edit', 'AdministracionMovimientosController@showAuthorization')->where('id','[0-9]+')->name('authorization.edit');
		Route::put('/authorization/{id}', 'AdministracionMovimientosController@updateAuthorization')->where('id','[0-9]+')->name('authorization.update');
		Route::get('/billing','AdministracionMovimientosController@billing')->name('billing');
		Route::get('/billing/{id}','AdministracionMovimientosController@billingEdit')->name('billing.edit');
		Route::get('/create/{id}','AdministracionMovimientosController@newRequest')->where('id','[0-9]+')->name('create.new');
	});
	Route::resource('/movements-accounts','AdministracionMovimientosController');

	/*** News ***/
	Route::prefix('/news')->name('news-api.')->group(function()
	{
		Route::get('/','AdministrationNewsControlller@index')->name('index');
		Route::get('/search','AdministrationNewsControlller@search')->name('search');
		Route::get('/notifications','AdministrationNewsControlller@notifications')->name('notifications');
		Route::post('/notifications/store','AdministrationNewsControlller@notificationStore')->name('notification-store');
		Route::delete('/notifications/inactive/{notification}','AdministrationNewsControlller@notificationInactive')->name('notification-inactive');
		Route::delete('/notifications/active/{notification}','AdministrationNewsControlller@notificationActive')->name('notification-active');
		Route::get('/send-mail','AdministrationNewsControlller@sendMails')->name('send-mail');
		
	});

	/*** Nomina ***/
	Route::prefix('/nomina')->name('nomina.')->group(function()
	{
		Route::get('/prenomina-create','AdministracionNominaController@prenominaCreate')->name('prenomina-create');
		Route::post('/prenomina-create/getemployee','AdministracionNominaController@getEmployee')->name('prenomina-create.getemployee');
		Route::post('/prenomina-create/getdetailemployee','AdministracionNominaController@getDetailEmployee')->name('prenomina-create.getdetailemployee');
		Route::post('/prenomina-create/viewdetailemployee','AdministracionNominaController@viewDetailEmployee')->name('prenomina-create.viewdetailemployee');
		Route::post('/prenomina-create/store','AdministracionNominaController@storePrenomina')->name('prenomina-create.store');
		Route::post('/prenomina-create/select-massive','AdministracionNominaController@selectMassive')->name('select.massive');
		Route::post('/prenomina-create/update-employee','AdministracionNominaController@updateEmployee')->name('prenomina-create.employeeupdate');
		Route::get('/prenomina-create/{prenomina}','AdministracionNominaController@prenominaEdit')->name('prenomina-edit');
		Route::delete('/prenomina-delete/{prenomina}','AdministracionNominaController@prenominaDelete')->name('prenomina-delete');
		Route::get('/nomina-search','AdministracionNominaController@nominaSearch')->name('nomina-search');
		Route::get('/nomina-create/{id}','AdministracionNominaController@nominaCreate')->where('id','[0-9]+')->name('nomina-create');
		Route::put('/nomina-create/update/{id}','AdministracionNominaController@updateNomina')->where('id','[0-9]+')->name('nomina-create.update');
		Route::put('/nomina-create/unsent/{id}','AdministracionNominaController@unsentNomina')->where('id','[0-9]+')->name('nomina-create.unsent');
		Route::get('/nomina-precalculate/{id}','AdministracionNominaController@nominaPrecalculate')->where('id','[0-9]+')->name('nomina-precalculate');
		Route::put('/nomina-create/precalculate/{id}','AdministracionNominaController@getNominaPrecalculate')->where('id','[0-9]+')->name('nomina-create.precalculate');
		Route::put('/nomina-create/precalculate-full/{id}','AdministracionNominaController@getNominaPrecalculateFull')->where('id','[0-9]+')->name('nomina-create.precalculate-full');
		Route::post('/nominanf-create/update/{id}','AdministracionNominaController@updateNominaNF')->where('id','[0-9]+')->name('nominanf-create.update');
		Route::post('/nomina-create/datanf','AdministracionNominaController@getDataEmployeeNF')->name('nomina-create.datanf');
		Route::post('/nomina-create/updatedatanf','AdministracionNominaController@updateDataEmployeeNF')->name('nomina-create.updatedatanf');
		Route::put('/nomina-create/updatedataf/{id}','AdministracionNominaController@updateDataEmployeeF')->name('nomina-create.updatedataf');
		Route::post('/nomina-create/getdetailemployeenomina','AdministracionNominaController@getDetailEmployeeNomina')->name('nomina-create.getdetailemployeenomina');
		Route::post('/nomina-create/update-employee-nomina','AdministracionNominaController@updateEmployeeNomina')->name('nomina-create.employeeupdatenomina');
		Route::post('/nomina-create/change-type','AdministracionNominaController@changeType')->name('nomina-create.changetype');
		Route::post('/nomina-create/change-type-update','AdministracionNominaController@changeTypeUpdate')->name('nomina-create.changetypeupdate');
		Route::get('/search','AdministracionNominaController@nominaFollowSearch')->name('nomina-follow-search');
		Route::get('/nf/receipt/{receipt}','AdministracionNominaController@receipt_download')->name('nf.receipt');
		Route::get('/search/{id}','AdministracionNominaController@nominaFollow')->where('id','[0-9]+')->name('nomina-follow');
		Route::get('/new/{id}','AdministracionNominaController@nominaCreateNew')->where('id','[0-9]+')->name('nomina-new');
		Route::put('/new/update/{id}','AdministracionNominaController@updateNewNomina')->where('id','[0-9]+')->name('nomina-new.update');
		Route::post('/nomina-create/viewdata','AdministracionNominaController@viewData')->name('nomina-create.viewdata');
		Route::get('/review','AdministracionNominaController@reviewSearch')->name('nomina-review-search');
		Route::get('/review/{id}','AdministracionNominaController@showReview')->where('id','[0-9]+')->name('nomina-review');
		Route::put('/review/update/{id}','AdministracionNominaController@updateReview')->where('id','[0-9]+')->name('review.update');
		Route::get('/construction-review','AdministracionNominaController@constructionReviewSearch')->name('nomina-constructionreview-search');
		Route::get('/construction-review/{id}','AdministracionNominaController@showConstructionReview')->where('id','[0-9]+')->name('nomina-constructionreview');
		Route::put('/construction-review/update/{id}','AdministracionNominaController@updateConstructionReview')->where('id','[0-9]+')->name('constructionreview.update');
		Route::get('/authorization','AdministracionNominaController@authorizationSearch')->name('nomina-authorization-search');
		Route::get('/authorization/{id}','AdministracionNominaController@showAuthorize')->where('id','[0-9]+')->name('nomina-authorization');
		Route::put('/authorization/update/{id}','AdministracionNominaController@updateAuthorize')->where('id','[0-9]+')->name('authorization.update');
		Route::get('/review/export-nf/{id}','AdministracionNominaController@exportReviewNF')->where('id','[0-9]+')->name('review-nf.export');
		Route::get('/construction-review/export-nf/{id}','AdministracionNominaController@exportConstructionReviewNF')->where('id','[0-9]+')->name('construction-review-nf.export');
		Route::post('/construction-review/complement-upload','AdministracionNominaController@complementUpload')->name('constructionreview.complement-upload');
		Route::post('/construction-review/complement-update','AdministracionNominaController@complementUpdate')->name('constructionreview.complement-update');
		Route::post('/construction-review/complement-update-construction','AdministracionNominaController@complementUpdateConstruction')->name('constructionreview.complement-update-construction');
		Route::get('/authorization/export-nf/{id}','AdministracionNominaController@exportAuthorizeNF')->where('id','[0-9]+')->name('authorization-nf.export');
		Route::get('/export-nom35/{id}','AdministracionNominaController@exportNom35')->where('id','[0-9]+')->name('nom35.export');
		Route::post('/nomina-create/data','AdministracionNominaController@getDataEmployeeF')->name('nomina-create.data');
		Route::post('/nomina-create/data-payment','AdministracionNominaController@getDataPaymentWay')->name('nomina-create.data-payment');
		Route::post('/data-nomina','AdministracionNominaController@getDataNomina')->name('data-nomina');
		Route::post('/data-nomina/update','AdministracionNominaController@updateDataNomina')->name('data-nomina.update');
		Route::get('/export-salary/{id}','AdministracionNominaController@exportSalary')->where('id','[0-9]+')->name('export.salary');
		Route::get('/export-bonus/{id}','AdministracionNominaController@exportBonus')->where('id','[0-9]+')->name('export.bonus');
		Route::get('/export-settlement/{id}','AdministracionNominaController@exportSettlement')->where('id','[0-9]+')->name('export.settlement');
		Route::get('/export-liquidation/{id}','AdministracionNominaController@exportLiquidation')->where('id','[0-9]+')->name('export.liquidation');
		Route::get('/export-profitsharing/{id}','AdministracionNominaController@exportProfitSharing')->where('id','[0-9]+')->name('export.profitsharing');
		Route::get('/export-vacationpremium/{id}','AdministracionNominaController@exportVacationPremium')->where('id','[0-9]+')->name('export.vacationpremium');
		Route::get('/export-employee/{id}','AdministracionNominaController@exportNominaEmployee')->where('id','[0-9]+')->name('export.employee');
		Route::put('/delete-employee/{id}','AdministracionNominaController@deleteEmployee')->where('id','[0-9]+')->name('delete.employee');
		Route::post('/salary-update','AdministracionNominaController@salaryUpdate')->name('salary-update');
		Route::post('/bonus-update','AdministracionNominaController@bonusUpdate')->name('bonus-update');
		Route::post('/liquidation-update','AdministracionNominaController@liquidationUpdate')->name('liquidation-update');
		Route::post('/settlement-update','AdministracionNominaController@settlementUpdate')->name('settlement-update');
		Route::post('/vacationpremium-update','AdministracionNominaController@vacationPremiumUpdate')->name('vacationpremium-update');
		Route::post('/profitsharing-update','AdministracionNominaController@profitSharingUpdate')->name('profitsharing-update');
		Route::post('/payment','AdministracionPagosController@storePaymentNomina')->name('payment.store');
		Route::put('/updatepayment/{id}','AdministracionPagosController@updatePaymentNomina')->where('id','[0-9]+')->name('payment.update');
		Route::put('/decline/{id}/{submodule_id}','AdministracionNominaController@declineRequest')->name('decline');
		Route::post('/upload','AdministracionNominaController@uploader')->name('uploader');
		Route::put('/upload-documents/{id}','AdministracionNominaController@uploadDocuments')->name('upload-documents');
		Route::get('/{path}/download','AdministracionNominaController@downloadPayment')->name('download.payment');
		Route::post('/validateEmployeeDocument','AdministracionNominaController@validationEmployeeDocument')->name('validation.document');
		Route::get('/prenomina-obra-create','AdministracionNominaController@prenominaObra')->name('prenomina-obra-create');
		Route::post('/add-employee-obra','AdministracionNominaController@getDataEmployeeObra')->name('add-employee-obra');
		Route::get('/massive-template',function()
		{
			return \Storage::disk('reserved')->download('/massive_employee/plantilla_prenomina_obra.csv');
		})->name('massive-template');
		Route::post('/prenomina-obra-create/select-massive','AdministracionNominaController@massiveEmployeeObra')->name('employee-obra.massive');
		Route::post('/prenomina-obra-store','AdministracionNominaController@storePrenominaObra')->name('employee-obra.store');
		Route::post('/prenomina-obra-save','AdministracionNominaController@savePrenominaObra')->name('employee-obra.save');
		Route::get('/prenomina-obra-create/{prenomina}','AdministracionNominaController@prenominaObraEdit')->name('prenomina-obra-edit');
		Route::get('/prenomina-obra-download/{prenomina}','AdministracionNominaController@prenominaObraDownload')->name('prenomina-obra-download');
		Route::delete('/prenomina-obra-delete/{prenomina}','AdministracionNominaController@prenominaObraDelete')->name('prenomina-obra-delete');
		Route::get('/export-layout-nf/{request_model}','AdministracionNominaController@exportLayoutNF')->where('id','[0-9]+')->name('export.layout-nf');
		Route::put('/upload-layout-nf/{request_model}','AdministracionNominaController@uploadLayout')->where('id','[0-9]+')->name('upload-layout');
		Route::get('/export-layout-fiscal/{request_model}','AdministracionNominaController@exportLayoutFiscal')->where('id','[0-9]+')->name('export.layout-fiscal');
		Route::put('/upload-layout-fiscal/{request_model}','AdministracionNominaController@uploadLayoutFiscal')->where('id','[0-9]+')->name('upload.layout-fiscal');
		Route::get('/report-nom35/{request_model}','AdministracionNominaController@reportNom035')->name('report-nom035');
		Route::get('/','AdministracionNominaController@index')->name('index');
	});

	/*** Other Income ***/
	Route::prefix('/other-income')->name('other-income.')->group(function ()
	{
		Route::get('/','AdministrationOtherIncomeController@index')->name('index');
		Route::get('/create','AdministrationOtherIncomeController@create')->name('create');
		Route::post('/create/store','AdministrationOtherIncomeController@store')->name('store');
		Route::get('/create/new-income/{t_request}','AdministrationOtherIncomeController@newRequest')->name('new-income');
		Route::post('/create/save','AdministrationOtherIncomeController@save')->name('save');
		Route::get('/edit','AdministrationOtherIncomeController@edit')->name('edit');
		Route::get('/edit/{request}','AdministrationOtherIncomeController@editIncome')->name('edit-income');
		Route::put('/edit/{t_request}/save-update','AdministrationOtherIncomeController@saveUpdate')->name('save-update');
		Route::put('/edit/{t_request}/update','AdministrationOtherIncomeController@update')->name('update');
		Route::get('/review','AdministrationOtherIncomeController@review')->name('review');
		Route::get('/review/{request}','AdministrationOtherIncomeController@showReview')->name('review.show');
		Route::put('/review/{t_request}/update','AdministrationOtherIncomeController@updateReview')->name('review.update');
		Route::get('/authorization','AdministrationOtherIncomeController@authorization')->name('authorization');
		Route::get('/authorization/{request}','AdministrationOtherIncomeController@showAuthorization')->name('authorization.show');
		Route::put('/authorization/{t_request}/update','AdministrationOtherIncomeController@updateAuthorization')->name('authorization.update');
		Route::get('/search/bank', 'AdministrationOtherIncomeController@getBanks')->name('search-bank');
		Route::post('/upload','AdministrationOtherIncomeController@uploader')->name('upload-file');
		Route::put('/{id}/upload-documents','AdministrationOtherIncomeController@uploadDocuments')->name('upload-documents');
		Route::get('/excel/follow','AdministrationOtherIncomeController@exportFollow')->name('excel-follow');
		Route::get('/excel/review','AdministrationOtherIncomeController@exportReview')->name('excel-review');
		Route::get('/excel/authorization','AdministrationOtherIncomeController@exportAuthorization')->name('excel-authorization');
	});

	/*** Payments ***/
	Route::prefix('/payments')->name('payments.')->group(function()
	{
		Route::post('/view-detail','AdministracionPagosController@viewPaymentDetail')->name('view-detail');
		Route::get('/pending','AdministracionPagosController@pending')->name('pending');
		Route::get('/export','AdministracionPagosController@pendingExport')->name('export');
		Route::get('/pending/{id}/review','AdministracionPagosController@showReview')->where('id','[0-9]+')->name('review.edit');
		Route::get('/edit','AdministracionPagosController@editPayment')->name('paymentedit');
		Route::get('/export-edit','AdministracionPagosController@exportEditPayment')->name('paymentedit-export');
		Route::get('/edit/{id}/review','AdministracionPagosController@showPayment')->where('id','[0-9]+')->name('showpayment');
		Route::get('/view/{id}/review','AdministracionPagosController@viewReview')->where('id','[0-9]+')->name('viewpayment');
		Route::put('/updatepay/{id}','AdministracionPagosController@updatePayment')->where('id','[0-9]+')->name('updatepayment');
		Route::put('/delete/{id}/pay','AdministracionPagosController@paymentDelete')->name('delete');
		Route::get('/account', 'AdministracionPagosController@getAccount')->name('account'); 
		Route::post('/validate','AdministracionPagosController@validation')->name('validation');
		Route::post('/upload','AdministracionPagosController@uploader')->name('upload');
		Route::get('/movements','AdministracionPagosController@movement')->name('movement');
		Route::get('/movements/create','AdministracionPagosController@movementCreate')->name('movement.create');
		Route::post('/movements/store','AdministracionPagosController@movementStore')->name('movement.store');
		Route::get('/movements/edit/','AdministracionPagosController@movementEdit')->name('movement.edit');
		Route::get('/movements/edit/{id}/show','AdministracionPagosController@movementShow')->name('movement.show');
		Route::put('/movements/edit/update/{id}','AdministracionPagosController@movementUpdate')->where('id','[0-9]+')->name('movement.update');
		Route::get('/movements/export','AdministracionPagosController@exportMovement')->name('movement.export');
		Route::get('/movements/movement-view/{id}/','AdministracionPagosController@movementView')->name('movement.view');
		Route::delete('/movements/delete/{id}','AdministracionPagosController@movementDelete')->name('movement.delete');
		Route::get('/movements/massive','AdministracionPagosController@movementMassive')->name('movement-massive');
		Route::post('/movements/massive/upload','AdministracionPagosController@movementMassiveUpload')->name('movement-massive.upload');
		Route::post('/movements/massive/upload/continue','AdministracionPagosController@movementMassiveContinue')->name('movement-massive.continue');
		Route::post('/movements/massive/upload/cancel','AdministracionPagosController@movementMassiveCancel')->name('movement-massive.cancel');
		Route::get('/movements/massive/edit','AdministracionPagosController@editMassive')->name('movement-massive.edit');
		Route::post('/movements/massive/update','AdministracionPagosController@updateMassive')->name('movement-massive.update');
		Route::get('/movements/massive/delete','AdministracionPagosController@movementDeleteMassive')->name('movement-massive.delete');
		Route::get('/conciliation-income','AdministracionPagosController@conciliationIncome')->name('conciliation-income');
		Route::get('/conciliation-income/create','AdministracionPagosController@conciliationIncomeCreate')->name('conciliation-income.create');
		Route::post('/conciliation-income/store','AdministracionPagosController@conciliationIncomeStore')->name('conciliation-income.store');
		Route::post('/conciliation-income/search','AdministracionPagosController@conciliationIncomeSearch')->name('conciliation-income.search');
		Route::get('/conciliation-income/edit','AdministracionPagosController@conciliationIncomeEdit')->name('conciliation-income.edit');
		Route::post('/conciliation-income/detail','AdministracionPagosController@viewConciliation')->name('conciliation-income.detail');
		Route::post('/conciliation-income/detail-bill','AdministracionPagosController@viewBill')->name('conciliation-income.detail-bill');
		Route::put('/conciliation-income/edit/update/{id}','AdministracionPagosController@conciliationIncomeUpdate')->where('id','[0-9]+')->name('conciliation-income.update');
		Route::get('/conciliation-income/edit/export','AdministracionPagosController@conciliationIncomeExport')->name('conciliation-income.export');
		Route::get('/conciliation','AdministracionPagosController@conciliation')->name('conciliation');
		Route::get('/conciliation/create','AdministracionPagosController@conciliationCreate')->name('conciliation.create');
		Route::get('/conciliation/create/normal','AdministracionPagosController@conciliationNormalCreate')->name('conciliation-normal.create');
		Route::get('/conciliation/create/nomina','AdministracionPagosController@conciliationNominaCreate')->name('conciliation-nomina.create');
		Route::post('/conciliation/search','AdministracionPagosController@conciliationSearch')->name('conciliation.search');
		Route::post('/conciliation/store','AdministracionPagosController@conciliationStore')->name('conciliation.store');
		Route::get('/conciliation/edit','AdministracionPagosController@conciliationView')->name('conciliation.view');
		Route::get('/conciliation/edit-normal','AdministracionPagosController@conciliationEdit')->name('conciliation.edit');
		Route::put('/conciliation/edit-normal/update/{id}','AdministracionPagosController@conciliationUpdate')->where('id','[0-9]+')->name('conciliation.update');
		Route::post('/conciliation/edit-normal/detail','AdministracionPagosController@conciliationDetail')->name('conciliation.details');
		Route::get('/conciliation/edit-normal/export','AdministracionPagosController@exportNormalConciliation')->name('conciliation-normal.export');
		Route::get('/conciliation/edit-nomina','AdministracionPagosController@conciliationNominaEdit')->name('conciliation-nomina.edit');
		Route::put('/conciliation/edit-nomina/update/{id}','AdministracionPagosController@conciliationNominaUpdate')->where('id','[0-9]+')->name('conciliation-nomina.update');
		Route::post('/conciliation/edit-nomina/detail','AdministracionPagosController@conciliationNominaDetail')->name('conciliation-nomina.details');
		Route::get('/conciliation/edit-nomina/export','AdministracionPagosController@exportNominaConciliation')->name('conciliation-nomina.export');
		Route::post('/conciliation/detail','AdministracionPagosController@requestDetail')->name('conciliation.detail');
		Route::post('/conciliation/search-nomina','AdministracionPagosController@conciliationNominaSearch')->name('conciliation.search.nomina');
		Route::post('/conciliation/store-nomina','AdministracionPagosController@conciliationStoreNomina')->name('conciliation.store.nomina');
		Route::get('/account', 'AdministracionPagosController@getAccount')->name('account'); 
		Route::post('/validate','AdministracionPagosController@validation')->name('validation');
		Route::post('/upload','AdministracionPagosController@uploader')->name('upload');
		Route::put('/pending/{id}/review/partial-update/{partial}','AdministracionPagosController@partialPaymentUpdate')->where('id','[0-9]+')->where('partial','[0-9]+')->name('partial-update');
	});
	Route::resource('/payments','AdministracionPagosController');

	/*** Payroll ***/
	Route::prefix('/payroll')->name('payroll.')->group(function()
	{
		Route::get('/search','AdministracionComplementoNominaController@search')->name('search');
		Route::get('/export/follow','AdministracionComplementoNominaController@exportFollow')->name('export.follow');
		Route::get('/export/review','AdministracionComplementoNominaController@exportReview')->name('export.review');
		Route::get('/export/authorization','AdministracionComplementoNominaController@exportAuthorize')->name('export.authorization');
		Route::get('/search/{id}/destroy', 'AdministracionComplementoNominaController@destroy')->where('id','[0-9]+')->name('destroy2');
		Route::get('/review','AdministracionComplementoNominaController@review')->name('review');
		Route::get('/review/{id}/edit', 'AdministracionComplementoNominaController@showReview')->where('id','[0-9]+')->name('review.edit');
		Route::put('/review/{id}', 'AdministracionComplementoNominaController@updateReview')->where('id','[0-9]+')->name('review.update');
		Route::post('/unsent','AdministracionComplementoNominaController@unsent')->name('unsent');
		Route::get('/follow/{id}/edit', 'AdministracionComplementoNominaController@follow')->where('id','[0-9]+')->name('follow.edit');
		Route::put('/unsent/{id}', 'AdministracionComplementoNominaController@updateFollow')->name('follow.update');
		Route::put('/unsentfollow/{id}', 'AdministracionComplementoNominaController@updateUnsentFollow')->where('id','[0-9]+')->name('follow.updateunsent');
		Route::get('/authorization','AdministracionComplementoNominaController@authorization')->name('authorization');
		Route::get('/authorization/{id}/edit','AdministracionComplementoNominaController@showAuthorize')->where('id','[0-9]+')->name('authorization.edit');
		Route::put('/authorization/{id}','AdministracionComplementoNominaController@updateAuthorize')->where('id','[0-9]+')->name('authorization.update');
		Route::get('/search/user', 'AdministracionComplementoNominaController@getEmployee')->name('search.user');
		Route::get('/create/account', 'AdministracionComplementoNominaController@getAccount')->name('create.account'); 
		Route::get('/create/{id}','AdministracionComplementoNominaController@newRequest')->where('id','[0-9]+')->name('create.new');
		Route::get('/search/bank', 'AdministracionComplementoNominaController@getBanks')->name('search.bank');
	});
	Route::resource('/payroll','AdministracionComplementoNominaController')->except(['show','edit','update','destroy']);

	/*** Procurement ***/
	Route::prefix('/procurement-purchases')->name('procurement-purchases.')->group(function () 
	{
		Route::get('/','ProcuracionComprasController@index')->name('index');
		Route::get('/purchase','ProcuracionComprasController@purchaseCreate')->name('purchase-create');
		Route::post('/purchase/save','ProcuracionComprasController@purchaseSave')->name('purchase-save');
		Route::get('/edit','ProcuracionComprasController@purchaseFollow')->name('purchase-follow');
		Route::get('/edit/{request}','ProcuracionComprasController@purchaseEdit')->name('purchase-edit');
		Route::get('/view/{request}','ProcuracionComprasController@purchaseView')->name('purchase-view');
		Route::put('/update/{purchase}','ProcuracionComprasController@purchaseUpdate')->name('purchase-update');
		Route::get('/download/{request}','ProcuracionComprasController@purchaseDownload')->name('purchase-download');
		Route::get('/export','ProcuracionComprasController@purchaseExport')->name('purchase-export');
		Route::put('/remarks/{purchase}','ProcuracionComprasController@saveRemarks')->name('purchase-save-remarks');
		Route::post('/view-detail','ProcuracionComprasController@viewDetail')->name('view-detail');
		Route::get('/cancel/{request}','ProcuracionComprasController@cancelPurchase')->name('purchase-cancel');
		Route::get('/warehouse','ProcuracionComprasController@warehouse')->name('warehouse');
		Route::get('/warehouse/{purchase}','ProcuracionComprasController@warehouseCreate')->name('warehouse-create');
		Route::post('/warehouse/save','ProcuracionComprasController@warehouseSave')->name('warehouse-save');
		Route::get('/report','ProcuracionComprasController@report')->name('report');
		Route::get('/report-dtr','ProcuracionComprasController@reportDTR')->name('report-dtr');
		Route::get('/report-epsr','ProcuracionComprasController@reportEPSR')->name('report-epsr');
		Route::get('/report-msr','ProcuracionComprasController@reportMSR')->name('report-msr');
		Route::get('/report/{request}','ProcuracionComprasController@reportView')->name('report-view');
		Route::get('/warehouse-search','ProcuracionComprasController@warehouseSearch')->name('warehouse-search');
		Route::get('/warehouse-export','ProcuracionComprasController@warehouseExport')->name('warehouse-export');
	});

	/*** Property ***/
	Route::prefix('/property')->name('property.')->group(function()
	{
		Route::get('/','AdministrationPropertyController@index')->name('index');
		Route::get('/create','AdministrationPropertyController@create')->name('create');
		Route::post('/upload','AdministrationPropertyController@uploader')->name('upload');
		Route::post('/store','AdministrationPropertyController@store')->name('store');
		Route::get('/edit/{property}','AdministrationPropertyController@edit')->name('edit');
		Route::put('/update/{property}','AdministrationPropertyController@update')->name('update');
		Route::get('/search','AdministrationPropertyController@search')->name('search');
		Route::get('/export','AdministrationPropertyController@export')->name('export');
	});

	/*** Purchase ***/
	Route::prefix('/purchase')->name('purchase.')->group(function()
	{
		Route::put('/updatebill/{id}','AdministracionCompraController@updateBill')->name('updatebill');
		Route::get('/search','AdministracionCompraController@search')->name('search');
		Route::get('/export/follow','AdministracionCompraController@exportFollow')->name('export.follow');
		Route::get('/export/review','AdministracionCompraController@exportReview')->name('export.review');
		Route::get('/export/authorization','AdministracionCompraController@exportAuthorize')->name('export.authorization');
		Route::get('/search/{id}/destroy', 'AdministracionCompraController@destroy')->where('id','[0-9]+')->name('destroy2');
		Route::get('/review','AdministracionCompraController@review')->name('review');
		Route::get('/review/{id}/edit', 'AdministracionCompraController@showReview')->where('id','[0-9]+')->name('review.edit');
		Route::put('/review/{id}', 'AdministracionCompraController@updateReview')->where('id','[0-9]+')->name('review.update');
		Route::post('/unsent','AdministracionCompraController@unsent')->name('unsent');
		Route::get('/follow/{id}/edit', 'AdministracionCompraController@follow')->where('id','[0-9]+')->name('follow.edit');
		Route::put('/unsent/{id}', 'AdministracionCompraController@updateFollow')->where('id','[0-9]+')->name('follow.update');
		Route::put('/unsentfollow/{id}', 'AdministracionCompraController@updateUnsentFollow')->where('id','[0-9]+')->name('follow.updateunsent');
		Route::put('/partialfollow/{id}', 'AdministracionCompraController@updatePartialFollow')->where('id','[0-9]+')->name('follow.updatepartial');
		Route::get('/authorization','AdministracionCompraController@authorization')->name('authorization');
		Route::get('/authorization/{id}/edit','AdministracionCompraController@showAuthorize')->where('id','[0-9]+')->name('authorization.edit');
		Route::put('/authorization/{id}','AdministracionCompraController@updateAuthorize')->where('id','[0-9]+')->name('authorization.update');
		Route::post('/create/provider', 'AdministracionCompraController@getProviders')->name('create.provider'); 
		Route::get('/create/account', 'AdministracionCompraController@getAccount')->name('create.account'); 
		Route::get('/create/{id}','AdministracionCompraController@newRequest')->where('id','[0-9]+')->name('create.new');
		Route::post('/upload','AdministracionCompraController@uploader')->name('upload');
		Route::get('/document/download/{id}','AdministracionCompraController@downloadDocument')->name('download.document');
		Route::get('/requisition-validation','AdministracionCompraController@requisitionValidation')->name('requisition-validation');
		Route::get('/check-budget','AdministracionCompraController@checkBudget')->name('check-budget');
		Route::post('/purchase-validatedocs','AdministracionCompraController@validationDocs')->name('validationDocs');
		Route::get('/provider/export', 'AdministracionCompraController@exportProvider')->name('export.provider');
		Route::post('/purchase/zip','AdministracionCompraController@catZip')->name('catalogue.zip');
		Route::post('/purchase-validatedocs-partial','AdministracionCompraController@validationDocsPartial')->name('validationDocs-partial');
	});
	Route::resource('/purchase','AdministracionCompraController')->except(['show','edit','update','destroy']);

	/*** Purchase Record ***/
	Route::prefix('/purchase-record')->name('purchase-record.')->group(function()
	{
		Route::put('/updatebill/{id}','AdministracionRegistroCompraController@updateBill')->name('updatebill');
		Route::get('/search','AdministracionRegistroCompraController@search')->name('search');
		Route::get('/export/follow','AdministracionRegistroCompraController@exportFollow')->name('export.follow');
		Route::get('/export/review','AdministracionRegistroCompraController@exportReview')->name('export.review');
		Route::get('/export/authorization','AdministracionRegistroCompraController@exportAuthorize')->name('export.authorization');
		Route::get('/search/{id}/destroy', 'AdministracionRegistroCompraController@destroy')->where('id','[0-9]+')->name('destroy2');
		Route::get('/review','AdministracionRegistroCompraController@review')->name('review');
		Route::get('/review/{id}/edit', 'AdministracionRegistroCompraController@showReview')->where('id','[0-9]+')->name('review.edit');
		Route::put('/review/{id}', 'AdministracionRegistroCompraController@updateReview')->where('id','[0-9]+')->name('review.update');
		Route::post('/unsent','AdministracionRegistroCompraController@unsent')->name('unsent');
		Route::get('/follow/{id}/edit', 'AdministracionRegistroCompraController@follow')->where('id','[0-9]+')->name('follow.edit');
		Route::put('/unsent/{id}', 'AdministracionRegistroCompraController@updateFollow')->where('id','[0-9]+')->name('follow.update');
		Route::put('/unsentfollow/{id}', 'AdministracionRegistroCompraController@updateUnsentFollow')->where('id','[0-9]+')->name('follow.updateunsent');
		Route::get('/authorization','AdministracionRegistroCompraController@authorization')->name('authorization');
		Route::get('/authorization/{id}/edit','AdministracionRegistroCompraController@showAuthorize')->where('id','[0-9]+')->name('authorization.edit');
		Route::put('/authorization/{id}','AdministracionRegistroCompraController@updateAuthorize')->where('id','[0-9]+')->name('authorization.update');
		Route::get('/create/provider', 'AdministracionRegistroCompraController@getProviders')->name('create.provider'); 
		Route::get('/create/credit-card', 'AdministracionRegistroCompraController@getCreditCard')->name('create.credit-card'); 
		Route::get('/create/{id}','AdministracionRegistroCompraController@newRequest')->where('id','[0-9]+')->name('create.new');
		Route::post('/upload','AdministracionRegistroCompraController@uploader')->name('upload');
		Route::get('/document/download/{id}','AdministracionRegistroCompraController@downloadDocument')->name('download.document');
		Route::post('/data/credit-cards', 'AdministracionRegistroCompraController@getCreditCardsData')->name('credit-cards-data'); 
		Route::get('/check-budget','AdministracionRegistroCompraController@checkBudget')->name('check-budget');
		Route::post('/purchase-record-validateDocs','AdministracionRegistroCompraController@validationDocs')->name('validationDocs');
	});
	Route::resource('/purchase-record','AdministracionRegistroCompraController');

	/*** Reclasification ***/
	Route::prefix('/reclassification')->name('reclassification.')->group(function()
	{
		Route::get('/search','AdministracionReclasificacionController@search')->name('search');
		Route::get('/{id}/edit','AdministracionReclasificacionController@follow')->name('edit');
		Route::put('/update-purchase/{id}','AdministracionReclasificacionController@updateReclassificationPurchase')->name('update-purchase');
		Route::put('/update-resource/{id}','AdministracionReclasificacionController@updateReclassificationResource')->name('update-resource');
		Route::put('/update-refund/{id}','AdministracionReclasificacionController@updateReclassificationRefund')->name('update-refund');
		Route::put('/update-expense/{id}','AdministracionReclasificacionController@updateReclassificationExpense')->name('update-expense');
		Route::put('/update-loan/{id}','AdministracionReclasificacionController@updateReclassificationLoan')->name('update-loan');
		Route::put('/update-purchase-record/{id}','AdministracionReclasificacionController@updateReclassificationPurchaseRecord')->name('update-purchaserecord');
		Route::put('/update-purchase-enterprise/{id}','AdministracionReclasificacionController@updateReclassificationPurchaseEnterprise')->name('update-purchase-enterprise');
		Route::put('/update-groups/{id}','AdministracionReclasificacionController@updateReclassificationGroups')->name('update-groups');
		Route::put('/update-movements-enterprise/{id}','AdministracionReclasificacionController@updateReclassificationMovementsEnterprise')->name('update-movements-enterprise');
		Route::put('/update-loan-enterprise/{id}','AdministracionReclasificacionController@updateReclassificationLoanEnterprise')->name('update-loan-enterprise');
	});
	Route::resource('/reclassification','AdministracionReclasificacionController');

	/*** Refund ***/
	Route::prefix('/refund')->name('refund.')->group(function()
	{
		Route::get('/search','AdministracionReembolsoController@search')->name('search');
		Route::get('/export/follow','AdministracionReembolsoController@exportFollow')->name('export.follow');
		Route::get('/export/review','AdministracionReembolsoController@exportReview')->name('export.review');
		Route::get('/export/authorization','AdministracionReembolsoController@exportAuthorize')->name('export.authorization');
		Route::get('/search/{id}/destroy', 'AdministracionReembolsoController@destroy')->where('id','[0-9]+')->name('destroy2');
		Route::get('/review','AdministracionReembolsoController@review')->name('review');
		Route::get('/review/{id}/edit', 'AdministracionReembolsoController@showReview')->where('id','[0-9]+')->name('review.edit');
		Route::put('/review/{id}', 'AdministracionReembolsoController@updateReview')->where('id','[0-9]+')->name('review.update');
		Route::post('/unsent','AdministracionReembolsoController@unsent')->name('unsent');
		Route::get('/follow/{id}/edit', 'AdministracionReembolsoController@follow')->where('id','[0-9]+')->name('follow.edit');
		Route::put('/unsent/{id}', 'AdministracionReembolsoController@updateFollow')->where('id','[0-9]+')->name('follow.update');
		Route::put('/unsentfollow/{id}', 'AdministracionReembolsoController@updateUnsentFollow')->where('id','[0-9]+')->name('follow.updateunsent');
		Route::get('/authorization','AdministracionReembolsoController@authorization')->name('authorization');
		Route::get('/authorization/{id}/edit','AdministracionReembolsoController@showAuthorize')->where('id','[0-9]+')->name('authorization.edit');
		Route::put('/authorization/{id}','AdministracionReembolsoController@updateAuthorize')->where('id','[0-9]+')->name('authorization.update');
		Route::get('/search/user', 'AdministracionReembolsoController@getEmployee')->name('search.user');
		Route::post('/create/account', 'AdministracionReembolsoController@getAccount')->name('create.account');
		Route::get('/getresourcedetail', 'AdministracionReembolsoController@getResourceDetail')->name('resource.detail');
		Route::get('/getresourcetotal', 'AdministracionReembolsoController@getResourceTotal')->name('resource.total'); 
		Route::get('/getresourcedetaildelete', 'AdministracionReembolsoController@getResourceDetailDelete')->name('resource.detaildelete'); 
		Route::post('/search/bank', 'AdministracionReembolsoController@getBanks')->name('search.bank');
		Route::get('/create/{id}','AdministracionReembolsoController@newRequest')->where('id','[0-9]+')->name('create.new');
		Route::post('/resource','AdministracionReembolsoController@getResource')->name('resource');
		Route::post('/upload','AdministracionReembolsoController@uploader')->name('upload');
		Route::get('/check-budget','AdministracionReembolsoController@checkBudget')->name('check-budget');
		Route::post('/validation-document','AdministracionReembolsoController@validationDocument')->name('validation-document');
		Route::get('/get-wbs', 'AdministracionReembolsoController@getWBS')->name('get-wbs'); 
		Route::get('/get-edt', 'AdministracionReembolsoController@getEDT')->name('get-edt');
		Route::post('/add-bankAccount','AdministracionReembolsoController@addBankAccount')->name('add-bankAccount');
	});
	Route::resource('/refund','AdministracionReembolsoController')->except(['show','edit','update','destroy']);

	/*** Requisition ***/
	Route::prefix('/requisition')->name('requisition.')->group(function()
	{
		Route::get('/revision/export/completo', 'AdministracionRequisicionController@exportReviewComplete')->name('export.revision.completo');
		Route::get('/revision/export', 'AdministracionRequisicionController@exportReview')->name('export.revision');
		Route::get('/seguimiento/export/completo', 'AdministracionRequisicionController@exportTracingComplete')->name('export.seguimiento.completo');
		Route::get('/seguimiento/export', 'AdministracionRequisicionController@exportTracing')->name('export.seguimiento');
		Route::get('/','AdministracionRequisicionController@index')->name('index');
		Route::get('/authorization/export', 'AdministracionRequisicionController@exportAuthorization')->name('export.authorization');
		Route::get('/authorization/export/complete', 'AdministracionRequisicionController@exportAuthorizationComplete')->name('export.authorization.complete');
		Route::get('/create','AdministracionRequisicionController@create')->name('create');
		Route::get('/create/new/{id}','AdministracionRequisicionController@createNew')->name('create.new');
		Route::get('/create/material','AdministracionRequisicionController@material')->name('create.material');
		Route::get('/create/service','AdministracionRequisicionController@service')->name('create.service');
		Route::get('/create/nomina','AdministracionRequisicionController@nomina')->name('create.nomina');
		Route::post('/store','AdministracionRequisicionController@store')->name('store');
		Route::post('/save','AdministracionRequisicionController@save')->name('save');
		Route::get('/follow','AdministracionRequisicionController@search')->name('search');
		Route::get('/{id}/edit','AdministracionRequisicionController@edit')->name('edit');
		Route::get('/{id}/personal','AdministracionRequisicionController@personalDownload')->name('personal');
		Route::get('/{id}/delete','AdministracionRequisicionController@delete')->name('delete');
		Route::get('/{id}/cancel','AdministracionRequisicionController@cancel')->name('cancel');
		Route::put('/{id}/update','AdministracionRequisicionController@update')->name('update');
		Route::put('/{id}/save','AdministracionRequisicionController@saveFollow')->name('save-follow');
		Route::put('/store-provider/{id}','AdministracionRequisicionController@storeProviderSecondary')->name('store-provider');
		Route::get('/review','AdministracionRequisicionController@review')->name('review');
		Route::get('/{id}/reviewedit','AdministracionRequisicionController@Reviewedit')->name('reviewedit');   
		Route::put('/review/{id}/save','AdministracionRequisicionController@saveReview')->name('save-review');
		Route::put('/review/{id}/reject','AdministracionRequisicionController@rejectReview')->name('reject-review');
		Route::put('/authorization/{id}/save','AdministracionRequisicionController@saveAuthorization')->name('save-authorization');
		Route::put('/authorization/{id}/reject','AdministracionRequisicionController@rejectAuthorization')->name('reject-authorization');
		Route::get('/{id}/review','AdministracionRequisicionController@showReview')->name('review.show');
		Route::put('/{id}/review','AdministracionRequisicionController@updateReview')->name('review.update');
		Route::get('/authorization','AdministracionRequisicionController@authorization')->name('authorization');
		Route::get('/{id}/authorizationedit','AdministracionRequisicionController@authorizationEdit')->name('authorizationedit');  
		Route::get('/{id}/authorization','AdministracionRequisicionController@showAuthorization')->name('authorization.show');
		Route::put('/{id}/authorization','AdministracionRequisicionController@updateAuthorization')->name('authorization.update');
		Route::post('/store-detail','AdministracionRequisicionController@uploadDetails')->name('store-detail');
		Route::put('/delete-provider/{id}','AdministracionRequisicionController@deleteProvider')->name('delete-provider');
		Route::post('/search-provider','AdministracionRequisicionController@searchProvider')->name('search-provider');
		Route::post('/edit-provider','AdministracionRequisicionController@editProvider')->name('edit-provider');
		Route::get('/update-provider/','AdministracionRequisicionController@updateProviderSecondary')->name('update-provider');
		Route::get('/export/{id}','AdministracionRequisicionController@export')->name('export');
		Route::get('/export/pdf/{id}','AdministracionRequisicionController@exportPdf')->name('export.pdf');
		Route::post('/provider-validation', 'AdministracionRequisicionController@validationProvider')->name('provider-validation'); 
		Route::put('/generate/{id}','AdministracionRequisicionController@generateRequest')->name('generate-request');
		Route::post('/upload','AdministracionRequisicionController@uploader')->name('upload');
		Route::put('/provider-documents/store/{id}','AdministracionRequisicionController@storeDocumentsProvider')->name('provider-documents.store');
		Route::post('/provider-documents/view', 'AdministracionRequisicionController@viewDocumentsProvider')->name('provider-documents.view'); 
		Route::put('/{id}/upload-documents','AdministracionRequisicionController@uploadDocuments')->name('upload-documents');
		Route::get('/validation-code', 'AdministracionRequisicionController@validationCode')->name('validation-code'); 
		Route::get('/get-edt', 'AdministracionRequisicionController@getEDT')->name('get-edt'); 
		Route::put('/{id}/upload-articles','AdministracionRequisicionController@uploadArticles')->name('upload-articles');
		Route::get('/get-number-requisition', 'AdministracionRequisicionController@getNumberRequisiction')->name('get-number-requisition'); 
		Route::get('/get-wbs', 'AdministracionRequisicionController@getWBS')->name('get-wbs'); 
		Route::get('/vote','AdministracionRequisicionController@vote')->name('vote');   
		Route::get('/{id}/voteedit','AdministracionRequisicionController@Voteedit')->name('voteedit');    
		Route::get('/{id}/vote','AdministracionRequisicionController@showVote')->name('vote.show');
		Route::put('/{id}/vote','AdministracionRequisicionController@updateVote')->name('vote.update');
		Route::put('/{id}/vote/save','AdministracionRequisicionController@saveVote')->name('vote.save');
		Route::put('/{id}/vote/reject','AdministracionRequisicionController@rejectVote')->name('vote.reject');
		Route::get('/follow-get-wbs', 'AdministracionRequisicionController@followGetWBS')->name('follow-get-wbs'); 
		Route::get('/follow-get-edt', 'AdministracionRequisicionController@followGetEDT')->name('follow-get-edt');
		Route::post('/unit','AdministracionRequisicionController@getUnit')->name('unit');
		Route::post('/validation-document','AdministracionRequisicionController@validationDocs')->name('validation-document');
		Route::post('/view-detail-employee','AdministracionRequisicionController@viewDetailEmployee')->name('view-detail-employee');
		Route::get('/catalogs','AdministracionRequisicionController@exportCatalogs')->name('export.catalogs');
		Route::get('/{employee}/individual','AdministracionRequisicionController@individualDocumentDownload')->name('personal.individual');
		Route::post('/curp-validate','AdministracionRequisicionController@curpValidate')->name('employee.curp-validate'); 
		Route::post('/rfc-validate','AdministracionRequisicionController@rfcValidate')->name('employee.rfc-validate'); 
		Route::post('/catalogue/zip','AdministracionRequisicionController@zipCode')->name('catalogue.zip');
		Route::post('/view-detail-purchase','AdministracionRequisicionController@viewDetailPurchase')->name('view-detail-purchase');
		Route::get('/employee/massive/template',function()
		{
			return \Storage::disk('reserved')->download('/massive_employee/plantilla_personal.xlsx');
		})->name('download-layout-personal');
		Route::get('/layout-comercial',function()
		{
			return \Storage::disk('reserved')->download('/massive_requisition/plantilla_articulos_comercial.xlsx');
		})->name('download-layout-comercial');
		Route::get('/layout-comercial-multiple',function()
		{
			return \Storage::disk('reserved')->download('/massive_requisition/plantilla_articulos_comercial_multiple.xlsx');
		})->name('download-layout-comercial-multiple');
		Route::get('/layout-machine',function()
		{
			return \Storage::disk('reserved')->download('/massive_requisition/plantilla_articulos_maquinaria.xlsx');
		})->name('download-layout-machine');
		Route::get('/layout-machine-multiple',function()
		{
			return \Storage::disk('reserved')->download('/massive_requisition/plantilla_articulos_maquinaria_multiple.xlsx');
		})->name('download-layout-machine-multiple');
		Route::get('/layout-material',function()
		{
			return \Storage::disk('reserved')->download('/massive_requisition/plantilla_articulos_material.xlsx');
		})->name('download-layout-material');
		Route::get('/layout-material-multiple',function()
		{
			return \Storage::disk('reserved')->download('/massive_requisition/plantilla_articulos_material_multiple.xlsx');
		})->name('download-layout-material-multiple');
		Route::get('/layout-service',function()
		{
			return \Storage::disk('reserved')->download('/massive_requisition/plantilla_articulos_servicios.xlsx');
		})->name('download-layout-service');
		Route::get('/layout-service-multiple',function()
		{
			return \Storage::disk('reserved')->download('/massive_requisition/plantilla_articulos_servicios_multiple.xlsx');
		})->name('download-layout-service-multiple');
		Route::get('/layout-subcontract',function()
		{
			return \Storage::disk('reserved')->download('/massive_requisition/plantilla_articulos_subcontracto.xlsx');
		})->name('download-layout-subcontract');
		Route::get('/layout-subcontract-multiple',function()
		{
			return \Storage::disk('reserved')->download('/massive_requisition/plantilla_articulos_subcontractos_multiple.xlsx');
		})->name('download-layout-subcontract-multiple');
		Route::get('/layout-articles',function()
		{
			return \Storage::disk('reserved')->download('/massive_requisition/plantilla_articulos.xlsx');
		})->name('download-layout');
		Route::get('/layout-articles-multiple',function()
		{
			return \Storage::disk('reserved')->download('/massive_requisition/plantilla_requisicion_multiple.xlsm');
		})->name('download-layout-multiple');
		Route::get('/layout-nomina-service',function()
		{
			return \Storage::disk('reserved')->download('/massive_requisition/plantilla_servicio_nomina.xlsx');
		})->name('download-layout.service-nomina');
	});

	/*** Resource ***/
	Route::prefix('/resource')->name('resource.')->group(function()
	{
		Route::get('/search','AdministracionRecursoController@search')->name('search');
		Route::get('/export/follow','AdministracionRecursoController@exportFollow')->name('export.follow');
		Route::get('/export/review','AdministracionRecursoController@exportReview')->name('export.review');
		Route::get('/export/authorization','AdministracionRecursoController@exportAuthorize')->name('export.authorization');
		Route::get('/search/{id}/destroy', 'AdministracionRecursoController@destroy')->where('id','[0-9]+')->name('destroy2');
		Route::get('/review','AdministracionRecursoController@review')->name('review');
		Route::get('/review/{id}/edit', 'AdministracionRecursoController@showReview')->where('id','[0-9]+')->name('review.edit');
		Route::put('/review/{id}', 'AdministracionRecursoController@updateReview')->where('id','[0-9]+')->name('review.update');
		Route::post('/unsent','AdministracionRecursoController@unsent')->name('unsent');
		Route::get('/follow/{id}/edit', 'AdministracionRecursoController@follow')->where('id','[0-9]+')->name('follow.edit');
		Route::put('/unsent/{id}', 'AdministracionRecursoController@updateFollow')->where('id','[0-9]+')->name('follow.update');
		Route::put('/unsentfollow/{id}', 'AdministracionRecursoController@updateUnsentFollow')->where('id','[0-9]+')->name('follow.updateunsent');
		Route::get('/authorization','AdministracionRecursoController@authorization')->name('authorization');
		Route::get('/authorization/{id}/edit','AdministracionRecursoController@showAuthorize')->where('id','[0-9]+')->name('authorization.edit');
		Route::put('/authorization/{id}','AdministracionRecursoController@updateAuthorize')->where('id','[0-9]+')->name('authorization.update');
		Route::get('/search/user', 'AdministracionRecursoController@getEmployee')->name('search.user'); 
		Route::get('/create/account', 'AdministracionRecursoController@getAccount')->name('create.account'); 
		Route::get('/create/{id}','AdministracionRecursoController@newRequest')->where('id','[0-9]+')->name('create.new');
		Route::get('/check-budget','AdministracionRecursoController@checkBudget')->name('check-budget');
		Route::get('/get-wbs','AdministracionRecursoController@getWBS')->name('get-wbs');
		Route::get('/get-edt','AdministracionRecursoController@getEDT')->name('get-edt');
		Route::get('/check-balance','AdministracionRecursoController@checkBalance')->name('check-balance');
		Route::post('/get-accounts-employee','AdministracionRecursoController@getAccountEmployee')->name('get-accounts-employee');
		Route::post('/upload','AdministracionRecursoController@uploader')->name('upload');
		Route::post('/validation-document','AdministracionRecursoController@validationDocs')->name('validation-document');
	});
	Route::resource('/resource','AdministracionRecursoController')->except(['show','edit','update','destroy']);

	/*** Staff ***/
	Route::prefix('/staff')->name('staff.')->group(function()
	{
		Route::get('/search','AdministracionPersonalController@search')->name('search');
		Route::get('/export/follow','AdministracionPersonalController@exportFollow')->name('export.follow');
		Route::get('/export/review','AdministracionPersonalController@exportReview')->name('export.review');
		Route::get('/export/authorization','AdministracionPersonalController@exportAuthorize')->name('export.authorization');
		Route::get('/search/{id}/destroy', 'AdministracionPersonalController@destroy')->where('id','[0-9]+')->name('destroy2');
		Route::get('/review','AdministracionPersonalController@review')->name('review');
		Route::get('/review/{id}/edit', 'AdministracionPersonalController@showReview')->where('id','[0-9]+')->name('review.edit');
		Route::put('/review/{id}', 'AdministracionPersonalController@updateReview')->where('id','[0-9]+')->name('review.update');
		Route::post('/unsent','AdministracionPersonalController@unsent')->name('unsent');
		Route::get('/follow/{id}/edit', 'AdministracionPersonalController@follow')->where('id','[0-9]+')->name('follow.edit');
		Route::put('/unsent/{id}', 'AdministracionPersonalController@updateFollow')->where('id','[0-9]+')->name('follow.update');
		Route::put('/unsentfollow/{id}', 'AdministracionPersonalController@updateUnsentFollow')->where('id','[0-9]+')->name('follow.updateunsent');
		Route::get('/authorization','AdministracionPersonalController@authorization')->name('authorization');
		Route::get('/authorization/{id}/edit','AdministracionPersonalController@showAuthorize')->where('id','[0-9]+')->name('authorization.edit');
		Route::put('/authorization/{id}','AdministracionPersonalController@updateAuthorize')->where('id','[0-9]+')->name('authorization.update');
		Route::get('/search/user', 'AdministracionPersonalController@getEmployee')->name('search.user');
		Route::get('/create/{id}','AdministracionPersonalController@newRequest')->where('id','[0-9]+')->name('create.new');
		Route::post('/accounts','AdministracionPersonalController@account')->name('account');
		Route::post('/catalogue/zip','AdministracionPersonalController@catZip')->name('catalogue.zip');
		Route::post('/upload','AdministracionPersonalController@uploader')->name('upload');
		Route::post('/view-detail-employee','AdministracionPersonalController@viewDetailEmployee')->name('view-detail-employee');
	});
	Route::resource('/staff','AdministracionPersonalController')->except(['show','edit','update','destroy']);

	/*** Stationery ***/
	Route::prefix('/stationery')->name('stationery.')->group(function()
	{
		Route::get('/delivery','AdministracionPapeleriaController@delivery')->name('delivery');
		Route::get('/delivery/{id}/edit','AdministracionPapeleriaController@showDelivery')->where('id','[0-9]+')->name('delivery.edit');
		Route::put('/delivery/{id}','AdministracionPapeleriaController@updateDelivery')->where('id','[0-9]+')->name('delivery.update');
		Route::get('/search','AdministracionPapeleriaController@search')->name('search');
		Route::get('/export/follow','AdministracionPapeleriaController@exportFollow')->name('export.follow');
		Route::get('/export/review','AdministracionPapeleriaController@exportReview')->name('export.review');
		Route::get('/export/authorization','AdministracionPapeleriaController@exportAuthorize')->name('export.authorization');
		Route::get('/export/delivery','AdministracionPapeleriaController@exportDelivery')->name('export.delivery');
		Route::get('/search/{id}/destroy', 'AdministracionPapeleriaController@destroy')->where('id','[0-9]+')->name('destroy2');
		Route::get('/review','AdministracionPapeleriaController@review')->name('review');
		Route::get('/review/{id}/edit', 'AdministracionPapeleriaController@showReview')->where('id','[0-9]+')->name('review.edit');
		Route::put('/review/{id}', 'AdministracionPapeleriaController@updateReview')->where('id','[0-9]+')->name('review.update');
		Route::post('/unsent','AdministracionPapeleriaController@unsent')->name('unsent');
		Route::get('/follow/{id}/edit', 'AdministracionPapeleriaController@follow')->where('id','[0-9]+')->name('follow.edit');
		Route::put('/unsent/{id}', 'AdministracionPapeleriaController@updateFollow')->where('id','[0-9]+')->name('follow.update');
		Route::put('/unsentfollow/{id}', 'AdministracionPapeleriaController@updateUnsentFollow')->where('id','[0-9]+')->name('follow.updateunsent');
		Route::get('/authorization','AdministracionPapeleriaController@authorization')->name('authorization');
		Route::get('/authorization/{id}/edit','AdministracionPapeleriaController@showAuthorize')->where('id','[0-9]+')->name('authorization.edit');
		Route::put('/authorization/{id}','AdministracionPapeleriaController@updateAuthorize')->where('id','[0-9]+')->name('authorization.update');
		Route::get('/create/account', 'AdministracionPapeleriaController@getAccount')->name('create.account'); 
		Route::get('/create/{id}','AdministracionPapeleriaController@newRequest')->where('id','[0-9]+')->name('create.new');
		Route::post('/validate','AdministracionPapeleriaController@validation')->name('validation');
		Route::get('/document/download/{id}','AdministracionPapeleriaController@downloadDocument')->name('download.document');
		Route::post('/articlerequest','AdministracionPapeleriaController@ArticleRequest')->name('articlerequest');
	});
	Route::resource('/stationery','AdministracionPapeleriaController')->except(['show','edit','update','destroy']);

	/*** Vehicles ***/
	Route::prefix('/vehicles')->name('vehicle.')->group(function()
	{
		Route::get('/','AdministrationVehiclesController@index')->name('index');
		Route::get('/create','AdministrationVehiclesController@create')->name('create');
		Route::post('/upload','AdministrationVehiclesController@uploader')->name('upload');
		Route::post('/store','AdministrationVehiclesController@store')->name('store');
		Route::get('/edit/{vehicle}','AdministrationVehiclesController@edit')->name('edit');
		Route::put('/update/{vehicle}','AdministrationVehiclesController@update')->name('update');
		Route::get('/search','AdministrationVehiclesController@search')->name('search');
		Route::get('/export','AdministrationVehiclesController@export')->name('export');
		Route::post('/validate-serial-number','AdministrationVehiclesController@validateSerialNumber')->name('validate-serial-number');
		Route::post('/get-data-owner','AdministrationVehiclesController@getDataOwner')->name('get-data-owner');
		Route::post('/validate-curp','AdministrationVehiclesController@validateCurp')->name('validate-curp'); 
		Route::post('/validate-rfc','AdministrationVehiclesController@validateRfc')->name('validate-rfc');
	});

	/*** Work Order ***/
	Route::prefix('/work_order')->name('work_order.')->group(function () 
	{
		Route::get('/','AdministracionOrdenTrabajoController@index')->name('index');
		Route::get('/create','AdministracionOrdenTrabajoController@create')->name('create');
		Route::post('/store','AdministracionOrdenTrabajoController@store')->name('store');
		Route::post('/upload','AdministracionOrdenTrabajoController@uploader')->name('upload');
		Route::get('/follow','AdministracionOrdenTrabajoController@search')->name('search');
		Route::post('/store-detail','AdministracionOrdenTrabajoController@uploadDetails')->name('store.detail');
		Route::get('/edit/{id}','AdministracionOrdenTrabajoController@edit')->name('edit');
		Route::put('/{id}/update','AdministracionOrdenTrabajoController@update')->name('update');
		Route::put('/upload-documents/{id}','AdministracionOrdenTrabajoController@uploadDocuments')->name('upload-documents');
		Route::post('/save','AdministracionOrdenTrabajoController@save')->name('save');
		Route::put('/{id}/save','AdministracionOrdenTrabajoController@saveFollow')->name('save-follow');
		Route::get('/layout-articles',function()
		{
			return \Storage::disk('reserved')->download('/massive_work_order/plantilla_articulos.xlsx');
		})->name('download-layout');
		Route::get('/layout-multiple',function()
		{
			return \Storage::disk('reserved')->download('/massive_work_order/plantilla_orden_trabajo_multiple.xlsm');
		})->name('download-layout-multiple');
	});
});

/*
|--------------------------------------------------------------------------
| Configuration Routes
|--------------------------------------------------------------------------
*/
Route::prefix('/configuration')->group(function()
{
	/*** Account ***/
	Route::prefix('/account')->name('account.')->group(function()
	{
		Route::get('/search','ConfiguracionCuentasController@search')->name('search');
		Route::get('/search/search', 'ConfiguracionCuentasController@getData')->name('search.search'); 
		Route::post('/validate','ConfiguracionCuentasController@validation')->name('validation');
	});
	Route::resource('/account','ConfiguracionCuentasController')->except(['show','destroy']);

	/*** Account concentrate ***/
	Route::prefix('/account-concentrated')->name('account-concentrated.')->group(function()
	{
		Route::get('/search', 'ConfigurationGroupingAccountController@search')->name('search');
		Route::get('/edit/{id}', 'ConfigurationGroupingAccountController@edit')->where('id','[0-9]+')->name('edit');
		Route::put('/update/{id}', 'ConfigurationGroupingAccountController@update')->where('id','[0-9]+')->name('update');
		Route::delete('/delete/{id}', 'ConfigurationGroupingAccountController@delete')->where('id','[0-9]+')->name('delete');
		Route::post('/get-accounts', 'ConfigurationGroupingAccountController@getAccounts')->name('get-accounts');
		Route::get('/create', 'ConfigurationGroupingAccountController@create')->name('create');
		Route::post('/store','ConfigurationGroupingAccountController@store')->name('store');
		Route::post('/validate', 'ConfigurationGroupingAccountController@validation')->name('validation');
		Route::get('/','ConfigurationGroupingAccountController@index')->name('index');
	});

	/*** Area ***/
	Route::prefix('/area')->name('area.')->group(function()
	{
		Route::get('/search','ConfiguracionAreaController@search')->name('search');
		Route::get('/search/search', 'ConfiguracionAreaController@getData')->name('search.search'); 
		Route::delete('/search/{id}/inactive', 'ConfiguracionAreaController@inactive')->name('inactive');
		Route::delete('/search/{id}/reactive', 'ConfiguracionAreaController@reactive')->name('reactive');
		Route::post('/validate','ConfiguracionAreaController@validation')->name('validation');
		Route::post('/validateName','ConfiguracionAreaController@validationName')->name('validationname');
		Route::get('/export','ConfiguracionAreaController@export')->name('export');
	});
	Route::resource('/area', 'ConfiguracionAreaController');

	/*** Automatic Requests ***/
	Route::prefix('/requests')->name('requests.')->group(function()
	{
		Route::get('/','ConfiguracionSolicitudes@index')->name('index');
		Route::get('/create','ConfiguracionSolicitudes@create')->name('create');
		Route::get('/edit','ConfiguracionSolicitudes@edit')->name('edit');
		Route::get('/show/{id}','ConfiguracionSolicitudes@show')->where('id','[0-9]+')->name('show');
		Route::post('/store','ConfiguracionSolicitudes@store')->name('store');
		Route::put('/update/{id}','ConfiguracionSolicitudes@update')->where('id','[0-9]+')->name('update');
		Route::post('/select','ConfiguracionSolicitudes@selectRequest')->name('select');
		Route::get('/active/{id}','ConfiguracionSolicitudes@activeRequest')->where('id','[0-9]+')->name('active');
		Route::get('/inactive/{id}','ConfiguracionSolicitudes@inactiveRequest')->where('id','[0-9]+')->name('inactive');
	});

	/*** Bank Account ***/
	Route::prefix('/bank-account')->name('bank.acount.')->group(function()
	{
		Route::get('/search','ConfiguracionCuentasBancariasController@search')->name('search');
		Route::get('/create','ConfiguracionCuentasBancariasController@create')->name('create');
		Route::get('/edit/{bank_account}','ConfiguracionCuentasBancariasController@edit')->name('edit');
		Route::post('/store','ConfiguracionCuentasBancariasController@store')->name('store');
		Route::put('/update/{bank_account}','ConfiguracionCuentasBancariasController@update')->name('update');
		Route::post('/validateClabe','ConfiguracionCuentasBancariasController@validateClabe')->name('validate.clabe');
		Route::post('/validateAccount','ConfiguracionCuentasBancariasController@validateAccount')->name('validate.account');
		Route::get('/','ConfiguracionCuentasBancariasController@index')->name('index');
	});

	/*** Banks ***/
	Route::prefix('/banks')->name('banks.')->group(function()
	{
		Route::get('/search','ConfiguracionBancosController@search')->name('search');
		Route::get('/account','ConfiguracionBancosController@getAccount')->name('account');
		Route::post('/validateAccount', 'ConfiguracionBancosController@validateAccount')->name('validateAccount');
		Route::post('/validateClabe', 'ConfiguracionBancosController@validateClabe')->name('validateClabe');
		Route::get('/export', 'ConfiguracionBancosController@export')->name('export');
	});
	Route::resource('/banks','ConfiguracionBancosController');

	/*** Blueprints ***/
	Route::prefix('/blueprints')->name('blueprints.')->group(function()
	{
		Route::post('/validate', 'ConfigurationBlueprintsController@validation')->name('validation');
		Route::get('/follow', 'ConfigurationBlueprintsController@follow')->name('follow');
		Route::get('/{id}/update', 'ConfigurationBlueprintsController@update')->name('update');
		Route::get('/follow/{id}', 'ConfigurationBlueprintsController@edit')->name('edit');
		Route::get('/store', 'ConfigurationBlueprintsController@store')->name('store');
		Route::get('/create', 'ConfigurationBlueprintsController@create')->name('create');
	});
	Route::resource('/blueprints', 'ConfigurationBlueprintsController')->except(['show','destroy']);

	/*** Client ***/
	Route::prefix('/client')->name('client.')->group(function()
	{
		Route::get('/search','ConfiguracionClienteController@search')->name('search');
		Route::get('/search/search', 'ConfiguracionClienteController@getClients')->name('search.search');
		Route::get('/export','ConfiguracionClienteController@export')->name('export');
		Route::get('/search/{id}/destroy','ConfiguracionClienteController@destroy')->name('destroy2');
		Route::post('/validaterfc','ConfiguracionClienteController@validationrfc')->name('validation.rfc');
		Route::post('/validatereason','ConfiguracionClienteController@validationReason')->name('validation.reason');
		Route::post('/catalogue/zip','ConfiguracionClienteController@zipCode')->name('catalogue.zip');
	});
	Route::resource('/client','ConfiguracionClienteController')->except(['show']);

	/*** Contract ***/
	Route::prefix('/contract')->name('contract.')->group(function()
	{
		Route::post('/validate', 'ConfigurationContractController@validation')->name('validation');
		Route::put('/follow/{id}/update', 'ConfigurationContractController@update')->name('update');
		Route::get('/follow', 'ConfigurationContractController@follow')->name('follow');
		Route::get('/follow/{id}', 'ConfigurationContractController@edit')->name('edit');
		Route::get('/create', 'ConfigurationContractController@create')->name('create');
		Route::post('/store', 'ConfigurationContractController@store')->name('store');
	});
	Route::resource('/contract', 'ConfigurationContractController')->except(['show','destroy']);

	/*** Contractor ***/
	Route::prefix('/contractor')->name('contractor.')->group(function()
	{
		Route::post('/validate', 'ConfigurationContractorController@validation')->name('validation');
		Route::post('/follow/{id}/inactive', 'ConfigurationContractorController@inactive')->name('inactive');
		Route::post('/follow/{id}/reactive', 'ConfigurationContractorController@reactive')->name('reactive');
		Route::put('/follow/{id}/update', 'ConfigurationContractorController@update')->name('update');
		Route::get('/follow/{id}', 'ConfigurationContractorController@edit')->name('edit');
		Route::get('/follow', 'ConfigurationContractorController@follow')->name('follow');
		Route::get('/create', 'ConfigurationContractorController@create')->name('create');
		Route::post('/store', 'ConfigurationContractorController@store')->name('store');
	});
	Route::resource('/contractor', 'ConfigurationContractorController')->except(['show','destroy']);

	/*** Department ***/
	Route::prefix('/department')->name('department.')->group(function()
	{
		Route::get('/search','ConfiguracionDepartamentoController@search')->name('search');
		Route::get('/search/search', 'ConfiguracionDepartamentoController@getData')->name('search.search'); 
		Route::delete('/search/{id}/inactive', 'ConfiguracionDepartamentoController@inactive')->name('inactive');
		Route::delete('/search/{id}/reactive', 'ConfiguracionDepartamentoController@reactive')->name('reactive');
		Route::post('/validate','ConfiguracionDepartamentoController@validation')->name('validation');
		Route::get('/export','ConfiguracionDepartamentoController@export')->name('export');
	});
	Route::resource('/department', 'ConfiguracionDepartamentoController');

	/*** Discipline ***/
	Route::prefix('/discipline')->name('discipline.')->group(function()
	{
		Route::get('','ConfigurationDisciplineController@index')->name('index');
		Route::get('/create','ConfigurationDisciplineController@create')->name('create');
		Route::get('/search','ConfigurationDisciplineController@search')->name('search');
		Route::post('/store','ConfigurationDisciplineController@store')->name('store');
		Route::get('/search','ConfigurationDisciplineController@follow')->name('follow');
		Route::get('/search/{id}','ConfigurationDisciplineController@edit')->name('edit');
		Route::put('/search/{id}/update','ConfigurationDisciplineController@update')->name('update');
		Route::post('/indicator','ConfigurationDisciplineController@validateIndicator')->name('indicator');
	});

	/*** Employee ***/
	Route::prefix('/employee')->name('employee.')->group(function()
	{
		Route::get('/documents/{employee}','ConfiguracionEmpleadoController@documents')->name('documents');
		Route::put('/documents/{employee}','ConfiguracionEmpleadoController@updateDocs')->name('update.docs');
		Route::post('/curp','ConfiguracionEmpleadoController@curpValidate')->name('curp'); 
		Route::post('/rfc','ConfiguracionEmpleadoController@rfcValidate')->name('rfc');
		Route::post('/email','ConfiguracionEmpleadoController@emailValidate')->name('email'); 
		Route::post('/account/validate','ConfiguracionEmpleadoController@accountValidate')->name('account.validate');
		Route::post('/clabe/validate','ConfiguracionEmpleadoController@clabeValidate')->name('clabe.validate');
		Route::post('/card/validate','ConfiguracionEmpleadoController@cardValidate')->name('card.validate');
		Route::get('/search','ConfiguracionEmpleadoController@search')->name('search');
		Route::get('/massive','ConfiguracionEmpleadoController@massive')->name('massive');
		Route::post('/massive/upload','ConfiguracionEmpleadoController@massiveUpload')->name('massive.upload');
		Route::post('/massive/upload/continue','ConfiguracionEmpleadoController@massiveContinue')->name('massive.continue');
		Route::get('/massive/template', function()
		{
			return \Storage::disk('reserved')->download('/massive_employee/plantilla_empleados.xlsx');
		})->name('massive.template');
		Route::post('/massive/upload/cancel','ConfiguracionEmpleadoController@massiveCancel')->name('massive.cancel');
		Route::post('/export','ConfiguracionEmpleadoController@export')->name('export');
		Route::post('/export/complete','ConfiguracionEmpleadoController@exportComplete')->name('export.complete');
		Route::post('/export/catalogs','ConfiguracionEmpleadoController@exportCatalogs')->name('export.catalogs');
		Route::post('/export-movement','ConfiguracionEmpleadoController@exportMovement')->name('export-movement');
		Route::post('/export-layout','ConfiguracionEmpleadoController@exportLayout')->name('export-layout');
		Route::get('/historic/{employee}','ConfiguracionEmpleadoController@historic')->name('historic');
		Route::post('/get-wbs','ConfiguracionEmpleadoController@getWbs')->name('get-wbs');
		//Route::get('/reactive/{id}','ConfiguracionEmpleadoController@reactive')->name('reactive');
	});
	Route::resource('/employee','ConfiguracionEmpleadoController')->except(['show', 'destroy']);

	/*** Enterprise ***/
	Route::prefix('/enterprise')->name('enterprise.')->group(function()
	{
		Route::get('/search','ConfiguracionEmpresaController@search')->name('search');
		Route::get('/export', 'ConfiguracionEmpresaController@export')->name('export'); 
		Route::get('/search/search', 'ConfiguracionEmpresaController@search')->name('search'); 
		Route::delete('/search/{id}/inactive', 'ConfiguracionEmpresaController@inactive')->name('inactive');
		Route::delete('/search/{id}/reactive', 'ConfiguracionEmpresaController@reactive')->name('reactive');
		Route::post('/validate','ConfiguracionEmpresaController@validation')->name('validation');
		Route::post('/validate-rfc','ConfiguracionEmpresaController@rfcValidation')->name('validation-rfc');
	});
	Route::resource('/enterprise', 'ConfiguracionEmpresaController')->except(['destroy','show']);

	/*** Items ***/
	Route::prefix('/items')->name('configuration-items.')->group(function()
	{
		Route::post('/validate', 'ConfigurationItemsController@validation')->name('validation');
		Route::get('/search', 'ConfigurationItemsController@search')->name('search');
	});
	Route::resource('/items', 'ConfigurationItemsController')->except(['show','destroy']);

	/*** Job Positions Routes ***/
	Route::prefix('/job-positions')->name('job-positions.')->group(function()
	{
		Route::get('/', 'ConfigurationJobPositionsController@index')->name('index'); 
		Route::get('/create', 'ConfigurationJobPositionsController@create')->name('create'); 
		Route::post('/store', 'ConfigurationJobPositionsController@store')->name('store'); 
		Route::get('/edit', 'ConfigurationJobPositionsController@edit')->name('edit'); 
		Route::get('/edit/{job_position}', 'ConfigurationJobPositionsController@show')->name('show'); 
		Route::put('/update/{job_position}', 'ConfigurationJobPositionsController@update')->name('update'); 
		Route::post('/validate','ConfigurationJobPositionsController@validation')->name('validation');
	});

	/*** Labels ***/
	Route::prefix('/labels')->name('labels.')->group(function()
	{
		Route::get('/search','ConfiguracionEtiquetaController@search')->name('search');
		Route::get('/search/search', 'ConfiguracionEtiquetaController@search')->name('search.search'); 
		Route::get('/export', 'ConfiguracionEtiquetaController@export')->name('export');
		Route::post('/validate','ConfiguracionEtiquetaController@validation')->name('validation');
	});
	Route::resource('/labels','ConfiguracionEtiquetaController')->except(['show','destroy']);

	/*** Machinery ***/
	Route::prefix('/machinery')->name('machinery.')->group(function()
	{
		Route::get('/search', 'ConfigurationMachineryController@search')->name('search');
	});
	Route::resource('/machinery', 'ConfigurationMachineryController')->except(['show','destroy']);

	/*** Parameter ***/
	Route::prefix('/parameter')->name('parameter.')->group(function()
	{
		Route::get('/','ConfiguracionParametroController@index')->name('index');
		Route::post('/update','ConfiguracionParametroController@update')->name('update');
	});

	/*** Places ***/
	Route::prefix('/places')->name('places.')->group(function()
	{
		Route::get('/search', 'ConfiguracionLugaresTrabajoController@getPlaces')->name('search');
		Route::post('/validate','ConfiguracionLugaresTrabajoController@validation')->name('validation');
		Route::get('/search', 'ConfiguracionLugaresTrabajoController@getPlaces')->name('search');
		Route::get('/export', 'ConfiguracionLugaresTrabajoController@export')->name('export');
	});
	Route::resource('/places', 'ConfiguracionLugaresTrabajoController');

	/*** Project ***/
	Route::prefix('/project')->name('project.')->group(function()
	{
		Route::get('/search','ConfiguracionProyectoController@search')->name('search'); 
		Route::get('/export','ConfiguracionProyectoController@export')->name('export'); 
		Route::get('/search/search', 'ConfiguracionProyectoController@getData')->name('search.search'); 
		Route::post('/validateProjectNumber','ConfiguracionProyectoController@validateProject')->name('validation.number');
		Route::post('/validate','ConfiguracionProyectoController@validation')->name('validation');
		Route::post('/subValidate','ConfiguracionProyectoController@subValidation')->name('sub.validation');
		Route::post('/store-subproject','ConfiguracionProyectoController@storeSubProject')->name('sub-store');
		Route::post('/delete-subproject','ConfiguracionProyectoController@deleteSubProject')->name('sub-delete');
		Route::get('/{id}/repair','ConfiguracionProyectoController@repair')->name('repair');
		Route::get('/configuration/project/{id}/destroy2','ConfiguracionProyectoController@destroy2')->name('project.destroy2');
	});
	Route::resource('/project','ConfiguracionProyectoController')->except(['show']);

	/*** Project Stages ***/
	Route::prefix('/project-stages')->name('project-stage.')->group(function()
	{
		Route::get('/search', 'ConfigurationProjectStagesController@search')->name('search');
		Route::post('/validate','ConfigurationProjectStagesController@validation')->name('validation');
	});
	Route::resource('/project-stages', 'ConfigurationProjectStagesController')->except(['show','destroy']);

	/*** Provider ***/
	Route::prefix('/provider')->name('provider.')->group(function()
	{
		Route::get('/search','ConfiguracionProveedorController@search')->name('search');
		Route::get('/search/{id}/destroy','ConfiguracionProveedorController@destroy')->name('destroy2');
		Route::post('/validate','ConfiguracionProveedorController@validation')->name('validation');
		Route::post('/validateAccount', 'ConfiguracionProveedorController@validateAccount')->name('validateAccount');
		Route::post('/upload','ConfiguracionProveedorController@uploader')->name('upload');
		Route::post('/catalogue/zip','ConfiguracionProveedorController@zipCode')->name('catalogue.zip');
	});
	Route::resource('/provider','ConfiguracionProveedorController');

	/*** Releases ***/
	Route::prefix('/releases')->name('releases.')->group(function()
	{
		Route::post('/store','ConfiguracionComunicadosController@store')->name('store');
		Route::get('/edit','ConfiguracionComunicadosController@search')->name('search');
		Route::get('/edit/{id}','ConfiguracionComunicadosController@showRelease')->name('edit.release');
		Route::delete('/delete/{id}','ConfiguracionComunicadosController@deleteRelease')->name('delete.release');
		Route::put('/update/{id}','ConfiguracionComunicadosController@updateRelease')->name('update.release');
	});
	Route::resource('/releases', 'ConfiguracionComunicadosController')->except(['show','edit','update','destroy']);

	/*** Requisition Units ***/
	Route::prefix('/unit')->name('unit.')->group(function()
	{
		Route::post('/category', 'ConfigurationUnitController@getCategory')->name('category');
		Route::post('/validate', 'ConfigurationUnitController@validateUnit')->name('validate');
		Route::get('/search','ConfigurationUnitController@search')->name('search');
	});
	Route::resource('/unit', 'ConfigurationUnitController')->except(['show','destroy']);

	/*** Responsibility ***/
	Route::prefix('/responsibility')->name('responsibility.')->group(function()
	{
		Route::get('/search','ConfiguracionResponsabilidadesController@search')->name('search');
		Route::get('/search/search', 'ConfiguracionResponsabilidadesController@getData')->name('search.search'); 
		Route::post('/validate','ConfiguracionResponsabilidadesController@validation')->name('validation');
	});
	Route::resource('/responsibility','ConfiguracionResponsabilidadesController')->except(['show','destroy']);

	/*** Risk Time ***/
	Route::prefix('/risk-time-category')->name('risk-time-category.')->group(function()
	{
		Route::get('/search', 'ConfigurationRiskTimeController@search')->name('search');
		Route::post('/validate', 'ConfigurationRiskTimeController@validation')->name('validation');
	});
	Route::resource('/risk-time-category', 'ConfigurationRiskTimeController')->except(['show','destroy']);

	/*** Roles ***/
	Route::prefix('/role')->name('role.')->group(function()
	{
		Route::get('/search/role', 'ConfiguracionRolController@getData')->name('search.role');
		Route::get('/search/module', 'ConfiguracionRolController@getMod')->name('search.module');  
		Route::get('/search/roles', 'ConfiguracionRolController@getModules')->name('search.roles'); 
		Route::get('/search','ConfiguracionRolController@search')->name('search');
		Route::delete('/search/{id}/inactive', 'ConfiguracionRolController@inactive')->name('inactive');
		Route::delete('/search/{id}/reactive', 'ConfiguracionRolController@reactive')->name('reactive');
		Route::post('/validate','ConfiguracionRolController@validation')->name('validation');
	});
	Route::resource('/role', 'ConfiguracionRolController');

	/*** Status ***/
	Route::prefix('/status')->name('status.')->group(function()
	{
		Route::get('/search', 'ConfiguracionEstadoSolicitudController@search')->name('search');
		Route::get('/export', 'ConfiguracionEstadoSolicitudController@export')->name('export');
	});
	Route::resource('/status','ConfiguracionEstadoSolicitudController')->except(['create','show', 'destroy']);

	/*** TDC ***/
	Route::prefix('/credit-card')->name('credit-card.')->group(function()
	{
		Route::post('/validate','ConfiguracionTdcController@validation')->name('validation');
		Route::get('/search','ConfiguracionTdcController@search')->name('search');
		Route::post('/upload','ConfiguracionTdcController@uploader')->name('upload');
		Route::put('/{id}/account-status', 'ConfiguracionTdcController@accountStatus')->where('id','[0-9]+')->name('account-status');
	});
	Route::resource('/credit-card','ConfiguracionTdcController')->except(['show','destroy']);

	/*** TypeDocument ***/
	Route::prefix('/type-document')->name('type.document.')->group(function()
	{
		Route::get('/','ConfigurationTypeDocumentController@index')->name('index');
		Route::get('/create','ConfigurationTypeDocumentController@create')->name('create');
		Route::get('/search','ConfigurationTypeDocumentController@search')->name('search');
		Route::post('/store','ConfigurationTypeDocumentController@store')->name('store');
		Route::get('/search','ConfigurationTypeDocumentController@follow')->name('follow');
		Route::get('/search/{id}','ConfigurationTypeDocumentController@edit')->name('edit');
		Route::put('/search/{id}/update','ConfigurationTypeDocumentController@update')->name('update');
		Route::post('/validate-name','ConfigurationTypeDocumentController@validateNameDoc')->name('validateName');
	});

	/*** User ***/
	Route::prefix('/user')->name('user.')->group(function()
	{
		Route::get('/search', 'ConfiguracionUsuarioController@search')->name('search'); 
		Route::get('/search/user', 'ConfiguracionUsuarioController@getData')->name('search.user'); 
		Route::get('/getentdep', 'ConfiguracionUsuarioController@getEntDep')->name('entdep'); 
		Route::get('/search/module', 'ConfiguracionUsuarioController@getMod')->name('search.module');
		Route::post('/validate','ConfiguracionUsuarioController@validation')->name('validation');
		Route::get('/{id}/suspend','ConfiguracionUsuarioController@suspend')->name('suspend');
		Route::get('/{id}/reentry','ConfiguracionUsuarioController@reentry')->name('reentry');
		Route::get('/{id}/delete','ConfiguracionUsuarioController@delete')->name('delete');
		Route::post('/module/permission','ConfiguracionUsuarioController@modulePermission')->name('module.permission');
		Route::post('/module/permission/update','ConfiguracionUsuarioController@modulePermissionUpdate')->name('module.permission.update');
		Route::post('/module/permission/update-simple','ConfiguracionUsuarioController@modulePermissionUpdateSimple')->name('module.permission.update.simple');
		Route::post('/module/permission/update-global','ConfiguracionUsuarioController@modulePermissionUpdateGlobal')->name('module.permission.update.global');
		Route::post('/massive-store','ConfiguracionUsuarioController@massiveStore')->name('massive-store');
	});
	Route::resource('/user', 'ConfiguracionUsuarioController');

	/*** WBS ***/
	Route::prefix('/wbs')->name('wbs.')->group(function()
	{
		Route::get('/{id}/up','ConfigurationWbsController@up')->name('up');
		Route::get('/search', 'ConfigurationWbsController@search')->name('search'); 
		Route::get('/massive','ConfigurationWbsController@massive')->name('massive');
		Route::post('/code/validate','ConfigurationWbsController@validation')->name('code.validation');
		Route::post('/massive/upload','ConfigurationWbsController@massiveUpload')->name('massive.upload');
		Route::post('/export/projects','ConfigurationWbsController@exportProjects')->name('export.projects');
		Route::post('/massive/upload/cancel','ConfigurationWbsController@massiveCancel')->name('massive.cancel');
		Route::post('/massive/upload/continue','ConfigurationWbsController@massiveContinue')->name('massive.continue');
		Route::get('/massive/template', function()
		{
			return \Storage::disk('reserved')->download('/massive_wbs/plantilla_wbs.xlsx');
		})->name('massive.template');
	});
	Route::resource('/wbs', 'ConfigurationWbsController')->except(['show']);

	/*** Weather Conditions ***/
	Route::prefix('/weather-condition')->name('weather-condition.')->group(function()
	{
		Route::get('/search', 'ConfigurationWeatherConditionController@search')->name('search');
	});
	Route::resource('/weather-condition', 'ConfigurationWeatherConditionController')->except(['show','destroy']);
});

/*
|--------------------------------------------------------------------------
| Construction Routes
|--------------------------------------------------------------------------
*/

Route::prefix('/construction')->name('construction.')->group(function()
{
	Route::prefix('/procurement')->name('procurement.')->group(function()
	{
		Route::get('/','ConstructionProcurementController@index')->name('index');
		Route::get('/upload','ConstructionProcurementController@upload')->name('upload');
		Route::post('/upload','ConstructionProcurementController@fileUpload')->name('upload.file');
		Route::post('/upload/cancel','ConstructionProcurementController@cancelUpload')->name('upload.cancel');
		Route::post('/upload/continue','ConstructionProcurementController@massiveContinue')->name('upload.continue');
		Route::get('/download','ConstructionProcurementController@download')->name('download');
		Route::get('/search','ConstructionProcurementController@search')->name('search');
		Route::post('/search/export','ConstructionProcurementController@searchExport')->name('export');
	});
});

/*
|--------------------------------------------------------------------------
| General Select Route
|--------------------------------------------------------------------------
*/
Route::post('/general-select', function(Request $request)
{
    return App\Helpers\CollectionHelper::select($request);
})->name('general.select');

/*
|--------------------------------------------------------------------------
| News Routes
|--------------------------------------------------------------------------
*/
Route::prefix('/news')->name('news.')->group(function()
{
	Route::get('/search','NoticiasController@search')->name('search');
	Route::get('/search/new','NoticiasController@getNews')->name('search.new');
	Route::get('/history','NoticiasController@history')->name('history');
	Route::get('/history/{id}','NoticiasController@history')->name('history.show');
	Route::get('/delete/{id}','NoticiasController@delete')->name('delete');
	Route::get('/releases','NoticiasController@releases')->name('releases');
	Route::post('/upload','NoticiasController@uploader')->name('upload');
});
Route::resource('/news', 'NoticiasController')->except(['destroy']);

/*
|--------------------------------------------------------------------------
| Operation Routes
|--------------------------------------------------------------------------
*/
Route::prefix('/operation')->group(function()
{
	/*** Activity Program ***/
	Route::prefix('/activity-program')->name('activitiesprogramation.')->group(function()
	{
		Route::get('/', 'OperationActivitiesProgramationController@index')->name('index');
		Route::get('/create','OperationActivitiesProgramationController@create')->name('create');
		Route::get('/get-wbs','OperationActivitiesProgramationController@getWBS')->name('get-wbs');
		Route::post('/store','OperationActivitiesProgramationController@store')->name('store');
		Route::get('/follow','OperationActivitiesProgramationController@follow')->name('follow');
		Route::get('/follow/{id}/edit','OperationActivitiesProgramationController@edit')->name('follow.edit');
		Route::put('/update/{activity}','OperationActivitiesProgramationController@update')->name('update');	
		Route::get('/export', 'OperationActivitiesProgramationController@export')->name('export');
		Route::get('/massive','OperationActivitiesProgramationController@massive')->name('massive');
		Route::post('/massive/upload', 'OperationActivitiesProgramationController@massiveUpload')->name('massive.upload');
		Route::get('/export/catalogs','OperationActivitiesProgramationController@exportCatalogs')->name('export.catalogs');
		Route::post('/massive/upload/continue','OperationActivitiesProgramationController@massiveContinue')->name('massive.continue');
		Route::post('massive/upload/cancel','OperationActivitiesProgramationController@massiveCancel')->name('massive.cancel');
		Route::get('/massive/templete',function()
		{
			return \Storage::disk('reserved')->download('/massive_activity/plantilla_actividades.csv');
		})->name('massive.template');
	});

	/*** Audits ***/
	Route::prefix('/audits')->name('audits.')->group(function()
	{
		Route::get('/','OperationAuditsController@index')->name('index');
		Route::get('/create','OperationAuditsController@create')->name('create');
		Route::get('/follow','OperationAuditsController@follow')->name('follow');
		Route::get('/follow/{audit}','OperationAuditsController@edit')->name('edit');
		Route::put('/follow/{audit}/update','OperationAuditsController@update')->name('update');
		Route::post('/store','OperationAuditsController@store')->name('store');
		Route::get('/get-subcat', 'OperationAuditsController@getSubCat')->name('get-subcat');
		Route::post('/upload','OperationAuditsController@uploader')->name('upload');
		Route::get('/export/{audit}/dos-bocas','OperationAuditsController@exportDosBocas')->name('export.dos-bocas');
		Route::get('/export/{audit}/tula','OperationAuditsController@exportTula')->name('export.tula');
		Route::get('/export/{audit}/pim','OperationAuditsController@exportPIM')->name('export.pim');
		Route::get('/analytics','OperationAuditsController@analytics')->name('analitycs');
	});

	/*** Incident Control ***/
	Route::prefix('/incident-control')->name('incident-control.')->group(function()
	{
		Route::get('/','OperationIncidentControlController@index')->name('index');
		Route::get('/create','OperationIncidentControlController@create')->name('create');
		Route::get('/follow','OperationIncidentControlController@search')->name('follow');
		Route::get('/edit/{incident}','OperationIncidentControlController@edit')->name('edit');
		Route::put('/update/{id}','OperationIncidentControlController@update')->where('id','[0-9]+')->name('update');
		Route::get('/massive','OperationIncidentControlController@massive')->name('massive');
		Route::post('/massive/upload','OperationIncidentControlController@massiveUpload')->name('massive.upload');
		Route::post('/massive/upload/continue','OperationIncidentControlController@massiveContinue')->name('massive.continue');
		Route::get('/massive/template',
		function()
		{
			return \Storage::disk('reserved')->download('/massive_incident/plantilla_incidentes.csv');
		})->name('massive.template');
		Route::post('/massive/upload/cancel','OperationIncidentControlController@massiveCancel')->name('massive.cancel');
		Route::get('/export/catalogs','OperationIncidentControlController@exportCatalogs')->name('export.catalogs');
		Route::get('/get-employee', 'OperationIncidentControlController@getEmployee')->name('get-employee');
		Route::get('/excel','OperationIncidentControlController@export')->name('excel'); 
		Route::post('/store','OperationIncidentControlController@store')->name('store');
		Route::post('/upload','OperationIncidentControlController@uploader')->name('upload');
		Route::post('/delete/{incident}','OperationIncidentControlController@delete')->name('delete');
	});

	/*** No Conformities Status ***/
	Route::prefix('/status-no-conformities')->name('status-nc.')->group(function()
	{
		Route::get('/','OperationNonConformityStatusController@index')->name('index');
		Route::get('/create','OperationNonConformityStatusController@create')->name('create');
		Route::post('/store','OperationNonConformityStatusController@store')->name('store');
		Route::get('/follow','OperationNonConformityStatusController@follow')->name('follow');
		Route::get('/follow/{id}','OperationNonConformityStatusController@edit')->name('edit');
		Route::put('/follow/{n_c_status}','OperationNonConformityStatusController@update')->name('update');
		Route::get('/get-wbs','OperationNonConformityStatusController@getWBS')->name('get-wbs');
		Route::get('/export', 'OperationNonConformityStatusController@export')->name('export');
		Route::get('/massive','OperationNonConformityStatusController@massive')->name('massive');
		Route::post('/massive/upload','OperationNonConformityStatusController@massiveUpload')->name('massive.upload');
		Route::get('/export/catalogs','OperationNonConformityStatusController@exportCatalogs')->name('export.catalogs');
		Route::post('/massive/upload/continue','OperationNonConformityStatusController@massiveContinue')->name('massive.continue');
		Route::post('massive/upload/cancel','OperationNonConformityStatusController@massiveCancel')->name('massive.cancel');
		Route::get('/massive/templete',
		function(){
			return \Storage::disk('reserved')->download('/massive_ncstatus/plantilla_estados_nc.csv');
		})->name('massive.template');
		Route::post('/upload','OperationNonConformityStatusController@uploader')->name('upload');
		Route::get('/export-pdf', 'OperationNonConformityStatusController@exportPDF')->name('export-pdf');
	});

	/*** Project Control ***/
	Route::prefix('/project-control/daily-report')->name('project-control.daily-report.')->group(function()
	{
		Route::get('/','OperationPCDailyReportController@index')->name('index');
		Route::get('/create','OperationPCDailyReportController@create')->name('create');
		Route::post('/store','OperationPCDailyReportController@store')->name('store');
		Route::get('/search','OperationPCDailyReportController@search')->name('search');
		Route::get('/export/follow','OperationPCDailyReportController@exportFollow')->name('export.follow');
		Route::get('/{id}/edit', 'OperationPCDailyReportController@follow')->where('id','[0-9]+')->name('edit');
		Route::put('/{id}/delete','OperationPCDailyReportController@delete')->name('delete');
		Route::put('/update/{id}', 'OperationPCDailyReportController@update')->where('id','[0-9]+')->name('edit.update');
		Route::get('/contracts','OperationPCDailyReportController@contracts_search')->name('contracts');
		Route::get('/wbs','OperationPCDailyReportController@wbs_search')->name('wbs');
		Route::get('/contract-item','OperationPCDailyReportController@contract_item_search')->name('contract.item');
		Route::post('/contract-item-data','OperationPCDailyReportController@contract_item_search_data')->name('contract.item.data');
		Route::get('/contractor','OperationPCDailyReportController@contractor_search')->name('contractor');
		Route::get('/blueprints','OperationPCDailyReportController@blueprints_search')->name('blueprints');
		Route::post('/upload','OperationPCDailyReportController@uploader')->name('uploader');
		Route::get('/{id}/reportPDF','OperationPCDailyReportController@reportPDF')->where('id','[0-9]+')->name('pdf');
	});

	/*** Preventive Risk Inspections ***/
	Route::prefix('/preventive-risk-inspections')->name('preventive.')->group(function()
	{
		Route::get('/','OperationPreventiveRiskInspectionController@index')->name('index');
		Route::get('/create','OperationPreventiveRiskInspectionController@create')->name('create');
		Route::post('/get-subcategory','OperationPreventiveRiskInspectionController@getSubCategory')->name('get-subcategory');
		Route::post('/store','OperationPreventiveRiskInspectionController@store')->name('store');
		Route::get('/follow','OperationPreventiveRiskInspectionController@follow')->name('follow');
		Route::get('/export', 'OperationPreventiveRiskInspectionController@export')->name('export');
		Route::get('/follow/{id}','OperationPreventiveRiskInspectionController@edit')->name('edit');
		Route::put('/update/{id}','OperationPreventiveRiskInspectionController@update')->name('update');
		Route::get('/massive','OperationPreventiveRiskInspectionController@massive')->name('massive');
		Route::post('/massive/upload', 'OperationPreventiveRiskInspectionController@massiveUpload')->name('massive.upload');
		Route::get('/export/catalogs','OperationPreventiveRiskInspectionController@exportCatalogs')->name('export.catalogs');
		Route::post('/massive/upload/continue','OperationPreventiveRiskInspectionController@massiveContinue')->name('massive.continue');
		Route::post('massive/upload/cancel','OperationPreventiveRiskInspectionController@massiveCancel')->name('massive.cancel');
		Route::get('/massive/templete', function()
		{
			return \Storage::disk('reserved')->download('/massive_preventive/plantilla_inspeccion_preventiva.csv');
		})->name('massive.template');
		Route::get('/export/{preventive}/dos-bocas','OperationPreventiveRiskInspectionController@exportDosBocas')->name('export.dos-bocas');
		Route::get('/export/{preventive}/tula','OperationPreventiveRiskInspectionController@exportTula')->name('export.tula');
	});

	/*** Work Force ***/
	Route::prefix('/work-force')->name('work-force.')->group(function()
	{
		Route::get('/','OperationWorkForceController@index')->name('index');
		Route::get('/create','OperationWorkForceController@create')->name('create');
		Route::post('/store','OperationWorkForceController@store')->name('store');
		Route::get('/follow','OperationWorkForceController@follow')->name('follow');
		Route::get('/follow/{work_force}','OperationWorkForceController@edit')->name('edit');
		Route::put('/follow/{work_force}','OperationWorkForceController@update')->name('update');
		Route::get('/massive','OperationWorkForceController@massiveCreate')->name('massive');
		Route::post('/massive/upload','OperationWorkForceController@massiveUpload')->name('upload');
		Route::post('/massive/upload/continue','OperationWorkForceController@massiveContinue')->name('continue');
		Route::post('/massive/upload/cancel','OperationWorkForceController@massiveCancel')->name('cancel');
		Route::get('/export','OperationWorkForceController@export')->name('export');
		Route::get('/export/catalogs','OperationWorkForceController@exportCatalogs')->name('export.catalogs');
		Route::get('/massive/template', function()
		{
			return \Storage::disk('reserved')->download('/massive_work_force/plantilla_fuerza_de_trabajo.csv');
		})->name('massive.template');
	});
});

/*
|--------------------------------------------------------------------------
| Profile Routes
|--------------------------------------------------------------------------
*/
Route::prefix('/profile')->name('profile.')->group(function()
{
	Route::post('/validate','PerfilController@accountValidate')->name('validate.account');
	Route::get('/password','PerfilController@changepass')->name('password');
	Route::post('/password','PerfilController@updatepass')->name('password.update');
	Route::put('/{id}', 'PerfilController@update')->name('update');
	Route::get('/', 'PerfilController@index')->name('index');
});

/*
|--------------------------------------------------------------------------
| Releases Routes
|--------------------------------------------------------------------------
*/
Route::prefix('/releases')->group(function()
{
	Route::get('/','ConfiguracionComunicadosController@releases')->name('releases');
	Route::get('/history','ConfiguracionComunicadosController@history')->name('releases.history');
});

/*
|--------------------------------------------------------------------------
| Report Routes
|--------------------------------------------------------------------------
*/
Route::prefix('/report')->name('report.')->group(function()
{
	Route::prefix('/administration')->group(function()
	{
		Route::get('/','ReportAdministrationController@index')->name('administration.index');
		Route::prefix('/purchase-record')->name('purchase-record.')->group(function()
		{
			Route::get('/','ReportAdministrationPurchaseRecordController@purchaseRecordReport')->name('index');
			Route::get('/export','ReportAdministrationPurchaseRecordController@purchaseRecordExport')->name('export');
			Route::get('/detail','ReportAdministrationPurchaseRecordController@purchaseRecordDetail')->name('detail');
		});
		Route::prefix('/purchase')->name('purchase.')->group(function()
		{
			Route::get('/','ReportAdministrationPurchaseController@purchaseReport')->name('index');
			Route::get('/excel','ReportAdministrationPurchaseController@purchaseExcel')->name('excel');
			Route::get('/detail','ReportAdministrationPurchaseController@purchaseDetail')->name('detail');
			Route::get('/account','ReportAdministrationPurchaseController@getAccount')->name('account');
		});
		Route::prefix('/expenses')->name('expenses.')->group(function()
		{
			Route::get('/','ReportAdministrationExpensesController@expensesReport')->name('index');
			Route::get('/excel','ReportAdministrationExpensesController@expensesExcel')->name('excel');
			Route::get('/excelwg','ReportAdministrationExpensesController@expensesExcelWithoutGrouping')->name('excelwg'); // checar
			Route::get('/detail','ReportAdministrationExpensesController@expensesDetail')->name('detail');
		});
		Route::prefix('/refunds')->name('refunds.')->group(function()
		{
			Route::get('/','ReportAdministrationRefundController@refundReport')->name('index');
			Route::get('/excel','ReportAdministrationRefundController@refundExcel')->name('excel');
			Route::get('/excelwg','ReportAdministrationRefundController@refundExcelWithoutGrouping')->name('excelwg');
			Route::get('/detail','ReportAdministrationRefundController@refundDetail')->name('detail');
		});
		Route::prefix('/stationery')->name('stationery.')->group(function()
		{
			Route::get('/','ReportAdministrationStationeryController@stationeryReport')->name('index');
			Route::get('/excel','ReportAdministrationStationeryController@stationeryExcel')->name('excel');
			Route::get('/detail','ReportAdministrationStationeryController@stationeryDetail')->name('detail');
		});
		Route::prefix('/warehouse')->name('warehouse.')->group(function()
		{
			Route::get('/','ReportAdministrationWarehouseController@warehouseReport')->name('index');
			Route::get('/table','ReportAdministrationWarehouseController@warehouseTable')->name('table');
			Route::get('/excel','ReportAdministrationWarehouseController@warehouseExcel')->name('excel');
			Route::post('/detail','ReportAdministrationWarehouseController@warehouseDetail')->name('detail');
		});
		Route::prefix('/computer')->name('computer.')->group(function()
		{
			Route::get('/','ReportAdministrationComputerController@computerReport')->name('index');
			Route::get('/table','ReportAdministrationComputerController@computerTable')->name('table');
			Route::get('/excel','ReportAdministrationComputerController@computerExcel')->name('excel');
			Route::get('/detail','ReportAdministrationComputerController@computerDetail')->name('detail');
		});
		Route::prefix('/accounts')->name('accounts.')->group(function()
		{
			Route::get('/','ReportAdministrationAccountController@accountsReport')->name('index');
			Route::get('/table','ReportAdministrationAccountController@accountsTable')->name('table');
			Route::get('/excel','ReportAdministrationAccountController@accountsExcel')->name('excel');
			Route::get('/detail','ReportAdministrationAccountController@accountsDetail')->name('detail');
			Route::get('/calc','ReportAdministrationAccountController@calc')->name('calc');
		});
		Route::prefix('/balance')->name('balance.')->group(function()
		{
			Route::get('/','ReportAdministrationBalanceController@balanceReport')->name('index');
			Route::get('/excel','ReportAdministrationBalanceController@balanceExcel')->name('excel');
			Route::get('/detail','ReportAdministrationBalanceController@balanceDetail')->name('detail');
		});
		Route::prefix('/tickets')->name('tickets.')->group(function()
		{
			Route::get('/','ReportAdministrationTicketController@ticketsReport')->name('index');
			Route::get('/excel','ReportAdministrationTicketController@ticketsExcel')->name('excel');
			Route::get('/detail','ReportAdministrationTicketController@ticketsDetail')->name('detail');
		});
		Route::prefix('/expenses-requests')->name('expenses.request.')->group(function()
		{
			Route::get('/','ReportAdministrationExpensesRequestController@expensesRequestReport')->name('index');
			Route::post('/wbs','ReportAdministrationExpensesRequestController@expensesWbs')->name('wbs');
			Route::get('/excelwg','ReportAdministrationExpensesRequestController@expensesRequestExcelWithoutGrouping')->name('excelwg');
			Route::get('/excel/wbs','ReportAdministrationExpensesRequestController@expensesRequestWbsExcelReport')->name('excel.wbs');
			Route::get('/excel/wbs/total','ReportAdministrationExpensesRequestController@expensesRequestWbsTotalExcelReport')->name('excel.wbs.total');
			Route::get('/detail','ReportAdministrationExpensesRequestController@expensesRequestDetail')->name('detail');
		});
		Route::prefix('/resource')->name('resource.')->group(function()
		{
			Route::get('/','ReportAdministrationResourceController@resourceReport')->name('index');
			Route::get('/excel','ReportAdministrationResourceController@resourceExcel')->name('excel');
			Route::get('/excelwg','ReportAdministrationResourceController@resourceExcelWithoutGrouping')->name('excelwg');
			Route::get('/detail','ReportAdministrationResourceController@resourceDetail')->name('detail');
		});
		Route::prefix('/labels')->name('labels.')->group(function()
		{
			Route::get('/','ReportAdministrationLabelController@labelsReport')->name('index');
			Route::get('/excel','ReportAdministrationLabelController@labelsExcel')->name('excel');
		});
		Route::prefix('/payroll')->name('payroll.')->group(function()
		{
			Route::get('/','ReportAdministrationPayrollController@payrollReport')->name('index');
			Route::get('/table','ReportAdministrationPayrollController@payrollTable')->name('table');
			Route::get('/excel','ReportAdministrationPayrollController@payrollExcel')->name('excel');
			Route::get('/excelwg','ReportAdministrationPayrollController@payrollExcelWithoutGrouping')->name('excelwg');
			Route::get('/detail','ReportAdministrationPayrollController@payrollDetail')->name('detail');
		});
		Route::prefix('/nomina')->name('nomina.')->group(function()
		{
			Route::get('/','ReportAdministrationNominaController@nominaReport')->name('index');
			Route::get('/excel','ReportAdministrationNominaController@nominaExcel')->name('excel');
			Route::get('/detail','ReportAdministrationNominaController@nominaDetail')->name('detail');
			Route::get('/payments/{req}','ReportAdministrationNominaController@paymentsZip')->name('payments');
			Route::get('/cfdi/{req}','ReportAdministrationNominaController@cfdiZip')->name('cfdi');
			Route::get('/receipt/{req}','ReportAdministrationNominaController@receiptZip')->name('receipt');
		});
		Route::prefix('/movements-accounts')->name('movements-accounts.')->group(function()
		{
			Route::get('/','ReportAdministrationMovementsAccountsController@movementsAccountReport')->name('index');
			Route::get('/excel','ReportAdministrationMovementsAccountsController@movementsAccountExcel')->name('excel');
			Route::get('/detail','ReportAdministrationMovementsAccountsController@movementsAccountDetail')->name('detail');
		});
		Route::prefix('/payments')->name('payments.')->group(function()
		{
			Route::get('/','ReportAdministrationPaymentController@paymentsReport')->name('index');
			Route::get('/export','ReportAdministrationPaymentController@paymentsExport')->name('export');
			Route::get('/detail','ReportAdministrationPaymentController@paymentsDetail')->name('detail');
		});
		Route::prefix('/movements')->name('movements.')->group(function()
		{
			Route::get('/','ReportAdministrationMovementController@movementsReport')->name('index');
			Route::get('/export','ReportAdministrationMovementController@movementsExport')->name('export');
			Route::get('/detail','ReportAdministrationMovementController@movementsDetail')->name('detail');
		});
		Route::prefix('/conciliation')->name('conciliation.')->group(function()
		{
			Route::get('/','ReportAdministrationConciliationsController@conciliationReport')->name('index');
			Route::get('/export','ReportAdministrationConciliationsController@conciliationExport')->name('export');
		});
		Route::prefix('/conciliation-nomina')->name('conciliation-nomina.')->group(function()
		{
			Route::get('/','ReportAdministrationConciliationsController@conciliationNominaReport')->name('index');
			Route::get('/export','ReportAdministrationConciliationsController@conciliationNominaExport')->name('export');
		});
		Route::prefix('/nomina-employee')->name('nomina-employee.')->group(function()
		{
			Route::get('/','ReportAdministrationNominaEmployeeController@nominaEmployeeReport')->name('index');
			Route::get('/detail','ReportAdministrationNominaEmployeeController@nominaEmployeeDetail')->name('detail');
			Route::get('/excel','ReportAdministrationNominaEmployeeController@nominaEmployeeExcel')->name('excel');
			Route::get('/report/administration/nomina-employee/table','ReportAdministrationNominaEmployeeController@nominaEmployeeTable')->name('table');
			Route::get('/report/administration/nomina-employee/table-complete','ReportAdministrationNominaEmployeeController@nominaEmployeeTableComplete')->name('table.complete');
			Route::get('/receipts/{case}','ReporteAdministracionController@nominaReceipts')->name('receipts');
			Route::get('/disbursement/subdepartment','ReportAdministrationNominaEmployeeController@nominaDisbursementSubdepartment')->name('disbursement.subdepartment');
			Route::get('/disbursement/wbs','ReportAdministrationNominaEmployeeController@nominaDisbursementWbs')->name('disbursement.wbs');
			Route::get('/zip-receipts','ReportAdministrationNominaEmployeeController@nominaEmployeeZipReceipts')->name('zip.receipts');
		});
		Route::prefix('/employee-nomina')->name('employee-nomina.')->group(function()
		{
			Route::get('/','ReportAdministrationEmployeeTypeNominaController@employeeNominaReport')->name('index');
			Route::get('/employer-register','ReportAdministrationEmployeeTypeNominaController@getEmployerRegister')->name('er');
			Route::get('/excel','ReportAdministrationEmployeeTypeNominaController@employeeNominaExcel')->name('excel');
		});
		Route::prefix('/isr')->name('isr.')->group(function()
		{
			Route::get('/','ReportAdministrationIsrController@isrReport')->name('index');
			Route::get('/excel','ReportAdministrationIsrController@isrExcel')->name('excel');
		});
		Route::prefix('/group-commissions')->name('group.commissions.')->group(function()
		{
			Route::get('/','ReportAdministrationGroupComissionsController@groupCommissions')->name('index');
			Route::get('/excel','ReportAdministrationGroupComissionsController@groupCommissionsExcel')->name('excel');
		});
		Route::prefix('/requisition')->name('requisition.')->group(function()
		{
			Route::get('/','ReportAdministrationRequisitionController@requisition')->name('index');
			Route::get('/excel','ReportAdministrationRequisitionController@requisitionExcel')->name('excel');
		});
		Route::prefix('/payroll-amounts')->name('payroll-amounts.')->group(function()
		{
			Route::get('/','ReportAdministrationPayrollAmountController@payrollAmount')->name('index');
			Route::get('/export','ReportAdministrationPayrollAmountController@exportPayrollAmount')->name('export');
		});
		Route::prefix('/attendance')->name('attendance.')->group(function()
		{
			Route::get('/','ReportAdministrationAttendanceController@attendance')->name('index');
			Route::get('/excel','ReportAdministrationAttendanceController@attendanceExcel')->name('excel');
		});
		Route::get('/get-accounts','ReportAdministrationController@getAccounts')->name('get.accounts');
	});
	Route::prefix('/finance')->group(function()
	{
		Route::get('/','ReportFinanceController@index')->name('finance.index');
		Route::prefix('/breakdown')->name('breakdown.')->group(function()
		{
			Route::get('/','ReportFinanceAccountsBreakdownController@breakdownReport')->name('index');
			Route::get('/result','ReportFinanceAccountsBreakdownController@breakdownReportResult')->name('result');
			Route::get('/charts','ReportFinanceAccountsBreakdownController@breakdownCharts')->name('charts');
			Route::get('/table','ReportFinanceAccountsBreakdownController@breakdownTable')->name('table');
			Route::get('/excel','ReportFinanceAccountsBreakdownController@breakdownExcel')->name('excel');
			Route::get('/detail','ReportFinanceAccountsBreakdownController@breakdownDetail')->name('detail');
		});
		Route::prefix('/concentrated')->name('concentrated.')->group(function()
		{
			Route::get('/','ReportFinanceAccountsConcentratedController@concentratedReport')->name('index');
			Route::get('/charts/bar/','ReportFinanceAccountsConcentratedController@concentratedChartsBar')->name('chartsbar');
			Route::get('/charts','ReportFinanceAccountsConcentratedController@concentratedCharts')->name('charts');
			Route::get('/result','ReportFinanceAccountsConcentratedController@concentratedReportResult')->name('result');
			Route::get('/excel','ReportFinanceAccountsConcentratedController@concentratedExcel')->name('excel');
		});
		Route::prefix('/account-concentrated')->name('account-concentrated.')->group(function()
		{
			Route::get('/','ReportFinanceItemsConcentratedController@accountConcentratedReport')->name('index');
			Route::get('/excel','ReportFinanceItemsConcentratedController@accountConcentratedExcel')->name('excel');
			Route::get('/charts','ReportFinanceItemsConcentratedController@accountConcentratedCharts')->name('charts');
			Route::get('/get-account','ReportFinanceItemsConcentratedController@getAccountsConcentrated')->name('getaccount');
		});
		Route::prefix('/expenses-concentrated')->name('expenses-concentrated.')->group(function()
		{
			Route::get('/','ReportFinanceExpensesConcentratedController@expensesConcentrated')->name('index');
			Route::get('/result','ReportFinanceExpensesConcentratedController@expensesConcentratedResult')->name('result');
			Route::get('/get-account','ReportFinanceExpensesConcentratedController@getAccountExpensesConcentrated')->name('get-account');
			Route::get('/{name}/download','ReportFinanceExpensesConcentratedController@downloadExcel')->name('download.excel');
		});
		Route::prefix('/income')->name('income.')->group(function()
		{
			Route::get('/','ReportFinanceIncomeController@incomeReport')->name('index');
			Route::get('/table','ReportFinanceIncomeController@incomeTable')->name('table');
			Route::get('/excel','ReportFinanceIncomeController@incomeExcel')->name('excel');
			Route::get('/excelwg','ReportFinanceIncomeController@incomeExcelWithoutGrouping')->name('excelwg');
			Route::get('/detail','ReportFinanceIncomeController@incomeDetail')->name('detail');
		});

		Route::prefix('/balance-sheet')->name('balance-sheet.')->group(function()
		{
			Route::get('/','ReportFinanceBalanceSheetController@balanceSheet')->name('index');
			Route::get('/result','ReportFinanceBalanceSheetController@balanceSheetResult')->name('result');
			Route::get('/queue','ReportFinanceBalanceSheetController@reportQueue')->name('queue');
			Route::get('/view-result/{id}','ReportFinanceBalanceSheetController@balanceSheetViewResult')->name('view-result');
			Route::get('/generate','ReportFinanceBalanceSheetController@generate')->name('generate');
			Route::get('/{name}/download','ReportFinanceBalanceSheetController@downloadExcel')->name('download.excel');
		});

		Route::prefix('/global')->name('global.')->group(function()
		{
			Route::get('/','ReportFinanceGlobalController@globalIndex')->name('index');
			Route::post('/export','ReportFinanceGlobalController@globalExport')->name('export');
		});

		Route::prefix('/iva')->name('iva.')->group(function()
		{
			Route::get('/','ReportFinanceIvaController@ivaReport')->name('index');
			Route::get('/result','ReportFinanceIvaController@ivaResult')->name('result');
			Route::get('/queue','ReportFinanceIvaController@ivaQueue')->name('queue');
			Route::get('/view-result/{id}','ReportFinanceIvaController@ivaViewResult')->name('view-result');
		});

	});
});

/*
|--------------------------------------------------------------------------
| Suggestions Module Routes
|--------------------------------------------------------------------------
*/
Route::prefix('/suggestions')->group(function()
{
	Route::get('/view','SugerenciaController@view')->name('suggestions.view');
	Route::get('/view/export','SugerenciaController@export')->name('suggestions.export');
	Route::post('/','SugerenciaController@store')->name('suggestions.store');
	Route::get('/','SugerenciaController@index')->name('suggestions.index');
});

/*
|--------------------------------------------------------------------------
| Tickets Routes
|--------------------------------------------------------------------------
*/
Route::prefix('/tickets')->group(function()
{
	Route::get('/all','TicketsController@allTickets')->name('tickets.all');
	Route::get('/all/{id}','TicketsController@allTicketsView')->where('id','[0-9]+')->name('tickets.all.view');
	Route::get('/new','TicketsController@newTickets');
	Route::post('/new/save','TicketsController@newTicketsSave')->name('tickets.new.save');
	//Route::get('/notassigned','TicketsController@notAssignedTickets')->name('tickets.notassigned');
	//Route::get('/notassigned/{id}','TicketsController@assignedTicket')->where('id','[0-9]+')->name('tickets.assigned');
	Route::put('/notassigned/{id}/update','TicketsController@assignedTicketUpdate')->where('id','[0-9]+')->name('tickets.assigned.update');
	Route::get('/without-resolving','TicketsController@withoutResolvingTickets')->name('tickets.withoutresolving');
	Route::get('/without-resolving/{id}','TicketsController@resolvingTickets')->where('id','[0-9]+')->name('tickets.resolving');
	Route::put('/without-resolving/{id}/update','TicketsController@resolvingTicketsUpdate')->where('id','[0-9]+')->name('tickets.resolving.update');
	Route::get('/assigned','TicketsController@assignedTicket')->where('id','[0-9]+')->name('tickets.assigned');
	Route::get('/assigned/{id}','TicketsController@showAssignedTicket')->where('id','[0-9]+')->name('tickets.show.assigned');
	Route::put('/assigned/{id}/solve','TicketsController@solvedAssignedTicket')->name('tickets.solve.assigned');
	Route::get('/follow','TicketsController@followTicket')->where('id','[0-9]+')->name('tickets.follow');
	Route::get('/follow/{id}','TicketsController@showFollowTicket')->where('id','[0-9]+')->name('tickets.show.follow');
	Route::put('/follow/{id}/solve','TicketsController@updateFollowTicket')->name('tickets.solve.follow');
	Route::put('/follow/reopen/{id}','TicketsController@reopenTicket')->where('id','[0-9]+')->name('tickets.reopen');
	Route::post('/upload','TicketsController@uploader')->name('tickets.upload');
	Route::get('/pending','TicketsController@pendingTickets');
	Route::get('/erased','TicketsController@erasedTickets');
	Route::get('/resolved','TicketsController@resolvedTickets');
	Route::get('/discontinued','TicketsController@discontinuedTickets');
	Route::put('/notassigned/{id}/re-asign','TicketsController@ReAsignTicketUpdate')->where('id','[0-9]+')->name('tickets.re-asign.update');
	Route::get('/','TicketsController@index')->name('tickets.index');
});

/*
|--------------------------------------------------------------------------
| Tools Routes
|--------------------------------------------------------------------------
*/
Route::prefix('/tools')->group(function ()
{
	/*** Construction processes ***/
	Route::prefix('/construction-processes')->name('construction-processes.')->group(function ()
	{
		Route::get('/','ToolConstructionProcessController@index')->name('index');
		Route::post('/upload','ToolConstructionProcessController@upload_file')->name('upload');
		Route::get('/download/{node}/{ids}','ToolConstructionProcessController@download_files')->name('download');
		Route::post('/delete','ToolConstructionProcessController@delete_files')->name('delete');
		Route::post('/rename','ToolConstructionProcessController@rename_file')->name('rename');
		Route::post('/move','ToolConstructionProcessController@move_file')->name('move');
		Route::get('/folder','ToolConstructionProcessController@folder_get')->name('folder');
		Route::post('/folder/create','ToolConstructionProcessController@folder_create')->name('folder.create');
		Route::post('/folder/delete','ToolConstructionProcessController@folder_delete')->name('folder.delete');
		Route::post('/folder/rename','ToolConstructionProcessController@folder_rename')->name('folder.rename');
		Route::post('/folder/move','ToolConstructionProcessController@folder_move')->name('folder.move');
		Route::post('/folder/files','ToolConstructionProcessController@folder_files')->name('folder.files');
	});

	/*** Compress ***/
	Route::prefix('/compress')->name('compress.')->group(function ()
	{
		Route::get('/','ToolsCompressController@index')->name('index');
		Route::post('/upload','ToolsCompressController@upload_file')->name('upload');
		Route::get('/download/{node}/{ids}','ToolsCompressController@download_files')->name('download');
		Route::post('/delete','ToolsCompressController@delete_files')->name('delete');
		Route::post('/rename','ToolsCompressController@rename_file')->name('rename');
		Route::post('/move','ToolsCompressController@move_file')->name('move');
		Route::get('/folder','ToolsCompressController@folder_get')->name('folder');
		Route::post('/folder/create','ToolsCompressController@folder_create')->name('folder.create');
		Route::post('/folder/delete','ToolsCompressController@folder_delete')->name('folder.delete');
		Route::post('/folder/rename','ToolsCompressController@folder_rename')->name('folder.rename');
		Route::post('/folder/move','ToolsCompressController@folder_move')->name('folder.move');
		Route::post('/folder/files','ToolsCompressController@folder_files')->name('folder.files');
	});

	/*** Nomina Calculator ***/
	Route::prefix('/calculator')->name('nomina.')->group(function ()
	{
		Route::get('/','AdministracionNominaController@nominaCalculator')->where('id','[0-9]+')->name('nomina-calculator');
		Route::post('/partial','AdministracionNominaController@formCalculator')->where('id','[0-9]+')->name('nomina-calculator-partial');
		Route::post('/excel','AdministracionNominaController@calculatorExcel')->where('id','[0-9]+')->name('nomina-calculator-excel');
		Route::post('/employer_register','AdministracionNominaController@validationEmployerRegister')->name('nomina-calculator.employee_register');
	});

	/*** Global Requests ***/
	Route::prefix('/global-requests')->name('global-requests.')->group(function ()
	{
		Route::get('/','ToolConstructionProcessController@search')->name('index');
		Route::get('/follow/{id}/show', 'ToolConstructionProcessController@show')->where('id','[0-9]+')->name('follow.show');
	});
});

/*
|--------------------------------------------------------------------------
| Warehouse Routes
|--------------------------------------------------------------------------
*/
Route::prefix('/warehouse')->group(function()
{
	Route::get('/create','WarehouseController@stationery')->name('warehouse.stationery');
	Route::get('/inventory','WarehouseController@inventory')->name('warehouse.inventory');
	Route::post('/stationery/search_w','WarehouseController@search_w')->name('warehouse.inventory.search_w');
	Route::get('/stationery/search_compras','WarehouseController@search_compras')->name('warehouse.inventory.search_compras');
	Route::post('/stationery/search_compras_request','WarehouseController@search_compras_request')->name('warehouse.inventory.search_compras_request');
	Route::post('/stationery/store','WarehouseController@stationeryStore')->name('warehouse.stationery.store');
	Route::post('/stationery/store_compras','WarehouseController@stationeryStoreCompras')->name('warehouse.stationery.store.compras');
	Route::post('/stationery/upload','WarehouseController@uploader')->name('warehouse.upload');
	Route::get('/computer','WarehouseController@computer')->name('warehouse.computer');
	Route::post('/computerStore','WarehouseController@computerStore')->name('warehouse.computer.store');
	Route::get('/search','WarehouseController@search')->name('warehouse.computer.search');
	Route::get('/stationery/table','WarehouseController@warehouseTable')->name('warehouse.stationery.table');
	Route::get('/stationery/excel','WarehouseController@warehouseExcel')->name('warehouse.stationery.excel');
	Route::get('/computer/excel','WarehouseController@computerExcel')->name('warehouse.computer.excel');
	Route::post('/computer/table','WarehouseController@computerTable')->name('warehouse.computer.table');
	Route::get('/tool','WarehouseController@tool')->name('warehouse.tool');
	Route::get('/tool/massive','WarehouseController@toolMassive')->name('warehouse.tool.massive');
	Route::get('/tool/purchase','WarehouseController@toolPurchase')->name('warehouse.tool.purchase');
	Route::get('/tool/purchase-export','WarehouseController@toolPurchaseExport')->name('warehouse.tool.purchase-export');
	Route::post('/stationery/fileName','WarehouseController@fileName')->name('warehouse.fileName');
	Route::post('/stationery/create_lot_file','WarehouseController@create_lot_file')->name('warehouse.create_lot_file');
	Route::post('/stationery/create_warehouse','WarehouseController@create_warehouse')->name('warehouse.create_warehouse');
	Route::post('/stationery/search_concept','WarehouseController@search_concept')->name('warehouse.search_concept');
	Route::get('/stationery/{id}/edit','WarehouseController@edit')->name('warehouse.edit');
	Route::get('/computer/edit/{id}','WarehouseController@computer_edit')->name('warehouse.computer.edit');
	Route::post('/stationery/edit_send','WarehouseController@edit_send')->name('warehouse.edit_send');
	Route::post('/computer/edit_send','WarehouseController@computer_edit_send')->name('warehouse.computer.edit_send');
	Route::get('/stationery/accounts','WarehouseController@getAccount')->name('warehouse.accounts');
	Route::get('/report/requisition','WarehouseController@reportRequisition')->name('warehouse.report.requisition');
	Route::get('/report/requisition/excel','WarehouseController@requisitionExcel')->name('warehouse.report.requisition.excel');
	Route::get('/report/requisition/pdf','WarehouseController@requisitionPdf')->name('warehouse.report.requisition.pdf');
	Route::get('/report/requisition/modal','WarehouseController@requisitionModal')->name('warehouse.report.requisition.modal');
	Route::get('/report/inputs_outputs','WarehouseController@inputsOutputs')->name('warehouse.report.inputsOutputs');
	Route::get('/report/inputs_outputs/excel','WarehouseController@inputsOutputsExcel')->name('warehouse.report.inputsOutputs.excel');
	Route::get('/report/inputs_outputs/modal','WarehouseController@inputsOutputsModal')->name('warehouse.inputs_outputs.modal');
	Route::get('/remove','WarehouseController@remove')->name('warehouse.remove');
	Route::post('/remove/detail','WarehouseController@removeDetail')->name('warehouse.remove.detail');
	Route::post('/remove/delete','WarehouseController@delete')->name('warehouse.remove.delete');
	Route::get('/delivery/report','WarehouseController@deliveryReportIndex')->name('warehouse.delivery.report');
	Route::get('/delivery/report/download/{id}','WarehouseController@downloadDeliveryReportPDF')->name('warehouse.delivery.report.download');
	Route::get('/delivery/report/export','WarehouseController@exportDeliveryReport')->name('warehouse.delivery.report.export');
	Route::post('/tool/massive/upload','WarehouseController@uploadCsv')->name('warehouse.upload-csv');
	Route::post('/tool/massive/checkMassive','WarehouseController@checkMassive')->name('warehouse.check-massive');
	Route::get('/tool/cat-export','WarehouseController@toolCatExport')->name('warehouse.tool.cat-export');
	Route::post('/tool/store/massive','WarehouseController@toolMassiveStore')->name('warehouse.store-massive');
	Route::get('/','WarehouseController@index');

});