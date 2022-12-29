<?php

namespace App\Providers;

use App\BoardroomReservations;
use App\Observers\BoardroomReservationsObserver;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Contracts\Routing\UrlGenerator;
use DB;
use Illuminate\Support\Facades\Blade;
use Illuminate\Pagination\Paginator;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(UrlGenerator $url)
    {
        Paginator::defaultView('vendor.pagination.paginate');
        Blade::component("components.buttons.tutorial", 'buttonTutorial');
        Blade::component("components.tables.table", 'Table');
        Blade::component("components.buttons.button", 'Button');
        Blade::component("components.tables.table-addTaxes", 'TableAddTaxes');
        Blade::component("components.tables.table-form", 'TableForm');
        Blade::component("components.tables.table-users", 'TableUsers');
        Blade::component("components.tables.table-provider", 'TableProvider');
        Blade::component("components.tables.alwaysVisibleTable", 'AlwaysVisibleTable');
        Blade::component("components.templates.outputs.form-details", 'FormDetails');
        Blade::component("components.templates.outputs.table-detail", 'TableDetail');
        Blade::component("components.templates.outputs.table-detail-single", 'TableDetailSingle');
        Blade::component("components.templates.outputs.taxRetention", 'TaxRetention');
        Blade::component("components.templates.outputs.taxTranfer", 'TaxTransfer');
        Blade::component("components.scripts.selects", 'ScriptSelect');
        Blade::component("components.containers.container-form", 'ContainerForm');
        Blade::component("components.inputs.select", 'Select');
        Blade::component("components.labels.label", 'Label');
        Blade::component("components.labels.title-divisor", 'Title');
        Blade::component("components.forms.form", 'Form');
        Blade::component("components.forms.searchForm", 'SearchForm');
        Blade::component("components.inputs.input-text", 'InputText');
        Blade::component("components.inputs.range-input", 'RangeInput');
        Blade::component("components.buttons.button-approval", 'ButtonApproval');
        Blade::component("components.labels.not-found", 'NotFound');
        Blade::component("components.tables.table-body", 'TableBody');
        if(\App::environment('production'))
        {
            $url->forceScheme('https');
        }
        Schema::defaultStringLength(191);
		BoardroomReservations::observe(BoardroomReservationsObserver::class);
		if (Schema::hasTable('users'))
		{
			DB::statement("SET lc_time_names = 'es_ES'");
		}
	}



	/**
	 * Register any application services.
	 *
	 * @return void
	 */
	public function register()
	{
		//
	}
}
