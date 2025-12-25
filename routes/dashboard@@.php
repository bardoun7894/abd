<?php
//use App\Http\Controllers\DashboardController;
//use App\Http\Controllers\Dashboard\CategoriesController;
use App\Http\Controllers\Dashboard\WorkersController;
use App\Http\Controllers\Dashboard\EmpsController;
use App\Http\Controllers\Dashboard\AccountingsController;
use App\Http\Controllers\Dashboard\CalculateController;
use App\Http\Controllers\Dashboard\FinancialController;
use App\Http\Controllers\Dashboard\ViolationController;

use App\Http\Controllers\Dashboard\ShopController;
use App\Http\Controllers\Dashboard\ManagerController;
use App\Http\Controllers\Dashboard\PurchaseController;
use App\Http\Controllers\Dashboard\ExpenseController;
use App\Http\Controllers\Dashboard\ConstantController;
use App\Http\Controllers\Dashboard\MoraslatController;

use App\Http\Controllers\Dashboard\GeneralController;
use App\Http\Controllers\Dashboard\VacationController;
use App\Http\Controllers\Dashboard\ReportController;


//use App\Http\Controllers\Dashboard\PhotoController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::group([
    'middleware' => (['auth']),
    'as' => 'dashboard.',
    'prefix' => 'dashboard',
],
    function () {
        Route::get('/', [HomeController::class, 'index'])->name('home');
        Route::resource('/categories', CategoriesController::class);

        Route::get('/workers/index', [WorkersController::class, 'index'])->name('workers.index');
        Route::get('/workers/import', [WorkersController::class, 'import'])->name('workers.import');
        Route::post('/workers/impfile', [WorkersController::class, 'impfile'])->name('workers.impfile');

        Route::post('/workers/tbl', [WorkersController::class, 'tbl'])->name('workers.tbl');
        Route::post('/workers/ajax_search_workers', [WorkersController::class, 'ajax_search_workers'])->name('workers.ajax_search_workers');
        Route::post('/workers/upd_workers', [WorkersController::class, 'upd_workers'])->name('workers.upd_workers');
        Route::post('/workers/cancal_workers', [WorkersController::class, 'cancal_workers'])->name('workers.cancal_workers');
        Route::post('/workers/updcancal', [WorkersController::class, 'updcancal'])->name('workers.updcancal');
        Route::post('/workers/del_workers', [WorkersController::class, 'del_workers'])->name('workers.del_workers');
        Route::post('/workers/ajax_search_project', [WorkersController::class, 'ajax_search_project'])->name('workers.ajax_search_project');
        Route::get('/workers/views', [WorkersController::class, 'views'])->name('workers.views');
        Route::post('/workers/updstore', [WorkersController::class, 'updstore'])->name('workers.updstore');
        Route::resource('/workers', WorkersController::class);
        Route::post('/workers/delete_file', [WorkersController::class, 'delete_file'])->name('workers.delete_file');
        Route::post('/workers/upd_note', [workersController::class, 'upd_note'])->name('workers.upd_note');
        Route::post('/workers/updnote', [workersController::class, 'updnote'])->name('workers.updnote');
        Route::post('/workers/upd_remark', [workersController::class, 'upd_remark'])->name('workers.upd_remark');
        Route::post('/workers/updremark', [workersController::class, 'updremark'])->name('workers.updremark');
        Route::post('/workers/tbl_remark', [workersController::class, 'tbl_remark'])->name('workers.tbl_remark');
        Route::post('/workers/ajax_search_remark', [workersController::class, 'ajax_search_remark'])->name('workers.ajax_search_remark');
        Route::post('/workers/del_remark', [workersController::class, 'del_remark'])->name('workers.del_remark');
        Route::post('/workers/change_remark', [workersController::class, 'change_remark'])->name('workers.change_remark');
        Route::post('/workers/worker_note_history', [workersController::class, 'worker_note_history'])->name('workers.worker_note_history');
        Route::post('/workers/tbl_history', [workersController::class, 'tbl_history'])->name('workers.tbl_history');
        Route::post('/workers/ajax_search_history', [workersController::class, 'ajax_search_history'])->name('workers.ajax_search_history');



        Route::get('/constant/city', [ConstantController::class, 'city'])->name('constant.city');
        Route::post('/constant/storecity', [ConstantController::class, 'storecity'])->name('constant.storecity');
        Route::post('/constant/tblcity', [ConstantController::class, 'tblcity'])->name('constant.tblcity');
        Route::post('/constant/delcity', [ConstantController::class, 'delcity'])->name('constant.delcity');
        Route::post('/constant/updcity', [ConstantController::class, 'updcity'])->name('constant.updcity');
        Route::post('/constant/updstorecity', [ConstantController::class, 'updstorecity'])->name('constant.updstorecity');


        Route::get('/constant/workplace', [ConstantController::class, 'workplace'])->name('constant.workplace');
        Route::post('/constant/storeworkplace', [ConstantController::class, 'storeworkplace'])->name('constant.storeworkplace');
        Route::post('/constant/tblworkplace', [ConstantController::class, 'tblworkplace'])->name('constant.tblworkplace');
        Route::post('/constant/delworkplace', [ConstantController::class, 'delworkplace'])->name('constant.delworkplace');
        Route::post('/constant/updworkplace', [ConstantController::class, 'updworkplace'])->name('constant.updworkplace');
        Route::post('/constant/updstoreworkplace', [ConstantController::class, 'updstoreworkplace'])->name('constant.updstoreworkplace');
        Route::get('/constant/expensecategoty', [ConstantController::class, 'expensecategoty'])->name('constant.expensecategoty');
        Route::post('/constant/storeexpensecategoty', [ConstantController::class, 'storeexpensecategoty'])->name('constant.storeexpensecategoty');
        Route::post('/constant/tblexpensecategoty', [ConstantController::class, 'tblexpensecategoty'])->name('constant.tblexpensecategoty');
        Route::post('/constant/delexpensecategoty', [ConstantController::class, 'delexpensecategoty'])->name('constant.delexpensecategoty');
        Route::post('/constant/updexpensecategoty', [ConstantController::class, 'updexpensecategoty'])->name('constant.updexpensecategoty');
        Route::post('/constant/updstoreexpensecategoty', [ConstantController::class, 'updstoreexpensecategoty'])->name('constant.updstoreexpensecategoty');


        Route::get('/constant/job', [ConstantController::class, 'job'])->name('constant.job');
        Route::post('/constant/storejob', [ConstantController::class, 'storejob'])->name('constant.storejob');
        Route::post('/constant/tbljob', [ConstantController::class, 'tbljob'])->name('constant.tbljob');
        Route::post('/constant/deljob', [ConstantController::class, 'deljob'])->name('constant.deljob');
        Route::post('/constant/updjob', [ConstantController::class, 'updjob'])->name('constant.updjob');
        Route::post('/constant/updstorejob', [ConstantController::class, 'updstorejob'])->name('constant.updstorejob');


        Route::get('/constant/violation', [ConstantController::class, 'violation'])->name('constant.violation');
        Route::post('/constant/storeviolation', [ConstantController::class, 'storeviolation'])->name('constant.storeviolation');
        Route::post('/constant/tblviolation', [ConstantController::class, 'tblviolation'])->name('constant.tblviolation');
        Route::post('/constant/delviolation', [ConstantController::class, 'delviolation'])->name('constant.delviolation');
        Route::post('/constant/updviolation', [ConstantController::class, 'updviolation'])->name('constant.updviolation');
        Route::post('/constant/updstoreviolation', [ConstantController::class, 'updstoreviolation'])->name('constant.updstoreviolation');


        /*Route::post('/constant/upd_expense', [ConstantController::class,'upd_expense'])->name('expense.upd_expense');
        Route::post('/constant/ajax_search_project', [ConstantController::class,'ajax_search_project'])->name('expense.ajax_search_project');
        Route::get('/constant/views', [ConstantController::class,'views'])->name('expense.views');
        Route::post('/constant/updstore', [ConstantController::class,'updstore'])->name('expense.updstore');*/
        Route::resource('/constant', ConstantController::class);


        Route::post('/general/sel_worker_list', [GeneralController::class, 'sel_worker_list'])->name('general.sel_worker_list');
        Route::post('/manager/sel_worker_manager', [GeneralController::class, 'sel_worker_manager'])->name('general.sel_worker_manager');
        Route::post('/manager/sel_shop_manager', [GeneralController::class, 'sel_shop_manager'])->name('general.sel_shop_manager');

        Route::post('/general/sel_shop_list', [GeneralController::class, 'sel_shop_list'])->name('general.sel_shop_list');
        Route::post('/general/sel_shop_pay', [GeneralController::class, 'sel_shop_pay'])->name('general.sel_shop_pay');
        Route::post('/general/chk_calculate', [GeneralController::class, 'chk_calculate'])->name('general.chk_calculate');
        Route::post('/general/sel_worker_pay', [GeneralController::class, 'sel_worker_pay'])->name('general.sel_worker_pay');


        Route::post('/emps/tbl', [empsController::class, 'tbl'])->name('emps.tbl');
        Route::post('/emps/ajax_search_emps', [empsController::class, 'ajax_search_emps'])->name('emps.ajax_search_emps');
        Route::post('/emps/upd_emps', [empsController::class, 'upd_emps'])->name('emps.upd_emps');
        Route::post('/emps/del_emps', [empsController::class, 'del_emps'])->name('emps.del_emps');
        Route::post('/emps/inactive_emp', [empsController::class, 'inactive_emp'])->name('emps.inactive_emp');

        Route::get('/emps/views', [empsController::class, 'views'])->name('emps.views');
        Route::post('/emps/updstore', [empsController::class, 'updstore'])->name('emps.updstore');
        Route::post('/emps/print', [empsController::class, 'print'])->name('emps.print');
        Route::post('/emps/show_job_cat', [EmpsController::class, 'show_job_cat'])->name('emps.show_job_cat');
        Route::post('/emps/load_emp_div', [EmpsController::class, 'load_emp_div'])->name('emps.load_emp_div');
        Route::post('/emps/sel_emp_supervisor', [EmpsController::class, 'sel_emp_supervisor'])->name('emps.sel_emp_supervisor');
        Route::get('/emps/add_role', [EmpsController::class, 'add_role'])->name('emps.add_role');
        Route::post('/emps/save_role', [empsController::class, 'save_role'])->name('emps.save_role');
        Route::get('/emps/view_role', [empsController::class, 'view_role'])->name('emps.view_role');
        Route::post('/emps/tbl_role', [empsController::class, 'tbl_role'])->name('emps.tbl_role');
        Route::post('/emps/ajax_search_role', [empsController::class, 'ajax_search_role'])->name('emps.ajax_search_role');
        Route::post('/emps/del_role', [empsController::class, 'del_role'])->name('emps.del_role');
        Route::post('/emps/upd_role', [empsController::class, 'upd_role'])->name('emps.upd_role');
        Route::post('/emps/updrole', [empsController::class, 'updrole'])->name('emps.updrole');
        Route::resource('/emps', empsController::class);

        Route::post('/accountings/create', [accountingsController::class, 'create'])->name('accountings.create');
        Route::post('/accountings/storepmonth', [accountingsController::class, 'storepmonth'])->name('accountings.storepmonth');
        Route::post('/accountings/pmonth_tbl', [accountingsController::class, 'pmonth_tbl'])->name('accountings.pmonth_tbl');
        Route::post('/accountings/ajax_search_pmonth', [accountingsController::class, 'ajax_search_pmonth'])->name('accountings.ajax_search_pmonth');
        Route::post('/accountings/upd_pmonth', [accountingsController::class, 'upd_pmonth'])->name('accountings.upd_pmonth');
        Route::post('/accountings/updpmonthstore', [accountingsController::class, 'updpmonthstore'])->name('accountings.updpmonthstore');

        Route::post('/accountings/del_pmonth', [accountingsController::class, 'del_pmonth'])->name('accountings.del_pmonth');
        Route::get('/accountings/viewpmonth', [accountingsController::class, 'viewpmonth'])->name('accountings.viewpmonth');


        Route::post('/accountings/tbl', [accountingsController::class, 'tbl'])->name('accountings.tbl');
        Route::post('/accountings/ajax_search_accountings', [accountingsController::class, 'ajax_search_accountings'])->name('accountings.ajax_search_accountings');
        Route::post('/accountings/upd_accountings', [accountingsController::class, 'upd_accountings'])->name('accountings.upd_accountings');
        Route::post('/accountings/del_accountings', [accountingsController::class, 'del_accountings'])->name('accountings.del_accountings');
        Route::get('/accountings/views', [accountingsController::class, 'views'])->name('accountings.views');
        Route::post('/accountings/updstore', [accountingsController::class, 'updstore'])->name('accountings.updstore');
        Route::resource('/accountings', accountingsController::class);
        Route::post('/accountings/print', [accountingsController::class, 'print'])->name('accountings.print');
        Route::post('/accountings/show_job_cat', [accountingsController::class, 'show_job_cat'])->name('accountings.show_job_cat');
        Route::post('/accountings/load_emp_div', [accountingsController::class, 'load_emp_div'])->name('accountings.load_emp_div');
        Route::post('/accountings/payments_month', [accountingsController::class, 'payments_month'])->name('accountings.payments_month');


        Route::post('/shop/tbl', [shopController::class, 'tbl'])->name('shop.tbl');
        Route::post('/shop/ajax_search_shop', [shopController::class, 'ajax_search_shop'])->name('shop.ajax_search_shop');
        Route::post('/shop/upd_shop', [shopController::class, 'upd_shop'])->name('shop.upd_shop');
        Route::post('/shop/del_shop', [shopController::class, 'del_shop'])->name('shop.del_shop');
        Route::post('/shop/ajax_search_project', [shopController::class, 'ajax_search_project'])->name('shop.ajax_search_project');
        Route::get('/shop/views', [shopController::class, 'views'])->name('shop.views');
        Route::post('/shop/updstore', [shopController::class, 'updstore'])->name('shop.updstore');
        Route::resource('/shop', shopController::class);
        Route::post('/shop/print', [shopController::class, 'print'])->name('shop.print');
        Route::post('/shop/sel_worker_list', [shopController::class, 'sel_worker_list'])->name('shop.sel_worker_list');
        Route::post('/shop/delete_file', [shopController::class, 'delete_file'])->name('shop.delete_file');
        Route::post('/shop/upd_file', [shopController::class, 'upd_file'])->name('shop.upd_file');
        Route::post('/shop/updfile', [shopController::class, 'updfile'])->name('shop.updfile');
        Route::post('/shop/del_shop_rentpay', [shopController::class, 'del_shop_rentpay'])->name('shop.del_shop_rentpay');

        Route::post('/shop/upd_note', [shopController::class, 'upd_note'])->name('shop.upd_note');
        Route::post('/shop/updnote', [shopController::class, 'updnote'])->name('shop.updnote');
        Route::post('/shop/shop_note_history', [shopController::class, 'shop_note_history'])->name('shop.shop_note_history');
        Route::post('/shop/tbl_history', [shopController::class, 'tbl_history'])->name('shop.tbl_history');
        Route::post('/shop/ajax_search_history', [shopController::class, 'ajax_search_history'])->name('shop.ajax_search_history');

        Route::post('/shop/upd_remark', [shopController::class, 'upd_remark'])->name('shop.upd_remark');
        Route::post('/shop/updremark', [shopController::class, 'updremark'])->name('shop.updremark');
        Route::post('/shop/tbl_remark', [shopController::class, 'tbl_remark'])->name('shop.tbl_remark');
        Route::post('/shop/ajax_search_remark', [shopController::class, 'ajax_search_remark'])->name('shop.ajax_search_remark');
        Route::post('/shop/del_remark', [shopController::class, 'del_remark'])->name('shop.del_remark');
        Route::post('/shop/change_remark', [shopController::class, 'change_remark'])->name('shop.change_remark');


        Route::post('/shop/upd_rentpay', [shopController::class, 'upd_rentpay'])->name('shop.upd_rentpay');
        Route::post('/shop/updrentpay', [shopController::class, 'updrentpay'])->name('shop.updrentpay');
        Route::post('/shop/tbl_rentpay', [shopController::class, 'tbl_rentpay'])->name('shop.tbl_rentpay');
        Route::post('/shop/ajax_search_rentpay', [shopController::class, 'ajax_search_rentpay'])->name('shop.ajax_search_rentpay');
        Route::post('/shop/del_rentpay', [shopController::class, 'del_rentpay'])->name('shop.del_rentpay');
        Route::post('/shop/change_rentpay', [shopController::class, 'change_rentpay'])->name('shop.change_rentpay');



        Route::post('/violation/tbl', [violationController::class, 'tbl'])->name('violation.tbl');
        Route::post('/violation/ajax_search_violation', [violationController::class, 'ajax_search_violation'])->name('violation.ajax_search_violation');
        Route::post('/violation/upd_violation', [violationController::class, 'upd_violation'])->name('violation.upd_violation');
        Route::post('/violation/del_violation', [violationController::class, 'del_violation'])->name('violation.del_violation');
        Route::get('/violation/views', [violationController::class, 'views'])->name('violation.views');
        Route::post('/violation/updstore', [violationController::class, 'updstore'])->name('violation.updstore');
        Route::resource('/violation', violationController::class);
        Route::post('/violation/print', [violationController::class, 'print'])->name('violation.print');
        Route::post('/violation/show_job_cat', [violationController::class, 'show_job_cat'])->name('violation.show_job_cat');
        Route::post('/violation/load_emp_div', [violationController::class, 'load_emp_div'])->name('violation.load_emp_div');
        Route::post('/violation/payments_month', [violationController::class, 'payments_month'])->name('violation.payments_month');
        Route::post('/violation/upd_statement', [violationController::class, 'upd_statement'])->name('violation.upd_statement');
        Route::post('/violation/updstatement', [violationController::class, 'updstatement'])->name('violation.updstatement');
        Route::post('/violation/tbl_detail', [violationController::class, 'tbl_detail'])->name('violation.tbl_detail');
        Route::post('/violation/ajax_search_violation_detail', [violationController::class, 'ajax_search_violation_detail'])->name('violation.ajax_search_violation_detail');

        Route::post('/violation/violation_note_history', [violationController::class, 'violation_note_history'])->name('violation.violation_note_history');
        Route::post('/violation/tbl_history', [violationController::class, 'tbl_history'])->name('violation.tbl_history');
        Route::post('/violation/ajax_search_history', [violationController::class, 'ajax_search_history'])->name('violation.ajax_search_history');

        Route::post('/violation/del_violation_det', [violationController::class, 'del_violation_det'])->name('violation.del_violation_det');
        Route::post('/violation/upd_violation_det', [violationController::class, 'upd_violation_det'])->name('violation.upd_violation_det');

        Route::post('/calculate/tbl', [calculateController::class, 'tbl'])->name('calculate.tbl');
        Route::post('/calculate/ajax_search_calculate', [calculateController::class, 'ajax_search_calculate'])->name('calculate.ajax_search_calculate');
        Route::post('/calculate/upd_calculate', [calculateController::class, 'upd_calculate'])->name('calculate.upd_calculate');
        Route::post('/calculate/del_calculate', [calculateController::class, 'del_calculate'])->name('calculate.del_calculate');
        Route::get('/calculate/views', [calculateController::class, 'views'])->name('calculate.views');
        Route::post('/calculate/updstore', [calculateController::class, 'updstore'])->name('calculate.updstore');
        Route::resource('/calculate', calculateController::class);
        Route::post('/calculate/print', [calculateController::class, 'print'])->name('calculate.print');
        Route::post('/calculate/show_job_cat', [calculateController::class, 'show_job_cat'])->name('calculate.show_job_cat');
        Route::post('/calculate/load_emp_div', [calculateController::class, 'load_emp_div'])->name('calculate.load_emp_div');
        Route::post('/calculate/payments_month', [calculateController::class, 'payments_month'])->name('calculate.payments_month');
        Route::post('/calculate/upd_statement', [calculateController::class, 'upd_statement'])->name('calculate.upd_statement');
        Route::post('/calculate/updstatement', [calculateController::class, 'updstatement'])->name('calculate.updstatement');
        Route::post('/calculate/tbl_detail', [calculateController::class, 'tbl_detail'])->name('calculate.tbl_detail');
        Route::post('/calculate/ajax_search_calculate_detail', [calculateController::class, 'ajax_search_calculate_detail'])->name('calculate.ajax_search_calculate_detail');

        Route::post('/calculate/del_calculate_det', [calculateController::class, 'del_calculate_det'])->name('calculate.del_calculate_det');
        Route::post('/calculate/upd_calculate_det', [calculateController::class, 'upd_calculate_det'])->name('calculate.upd_calculate_det');

        Route::post('/financial/tbl', [financialController::class, 'tbl'])->name('financial.tbl');
        Route::post('/financial/ajax_search_financial', [financialController::class, 'ajax_search_financial'])->name('financial.ajax_search_financial');
        Route::post('/financial/upd_financial', [financialController::class, 'upd_financial'])->name('financial.upd_financial');
        Route::post('/financial/del_financial', [financialController::class, 'del_financial'])->name('financial.del_financial');
        Route::get('/financial/views', [financialController::class, 'views'])->name('financial.views');
        Route::post('/financial/updstore', [financialController::class, 'updstore'])->name('financial.updstore');
        Route::resource('/financial', financialController::class);
        Route::post('/financial/print', [financialController::class, 'print'])->name('financial.print');
        Route::post('/financial/show_job_cat', [financialController::class, 'show_job_cat'])->name('financial.show_job_cat');
        Route::post('/financial/load_emp_div', [financialController::class, 'load_emp_div'])->name('financial.load_emp_div');
        Route::post('/financial/payments_month', [financialController::class, 'payments_month'])->name('financial.payments_month');
        Route::post('/financial/upd_statement', [financialController::class, 'upd_statement'])->name('financial.upd_statement');
        Route::post('/financial/updstatement', [financialController::class, 'updstatement'])->name('financial.updstatement');
        Route::post('/financial/tbl_detail', [financialController::class, 'tbl_detail'])->name('financial.tbl_detail');
        Route::post('/financial/ajax_search_financial_detail', [financialController::class, 'ajax_search_financial_detail'])->name('financial.ajax_search_financial_detail');

        Route::post('/financial/del_financial_det', [financialController::class, 'del_financial_det'])->name('financial.del_financial_det');
        Route::post('/financial/upd_financial_det', [financialController::class, 'upd_financial_det'])->name('financial.upd_financial_det');
        Route::post('/general/chk_financial', [GeneralController::class, 'chk_financial'])->name('general.chk_financial');
        Route::post('/financial/financial_note_history', [financialController::class, 'financial_note_history'])->name('financial.financial_note_history');
        Route::post('/financial/tbl_history', [financialController::class, 'tbl_history'])->name('financial.tbl_history');
        Route::post('/financial/ajax_search_history', [financialController::class, 'ajax_search_history'])->name('financial.ajax_search_history');


        Route::post('/manager/tbl', [managerController::class, 'tbl'])->name('manager.tbl');
        Route::post('/manager/ajax_search_manager', [managerController::class, 'ajax_search_manager'])->name('manager.ajax_search_manager');
        Route::post('/manager/upd_manager', [managerController::class, 'upd_manager'])->name('manager.upd_manager');
        Route::post('/manager/del_manager', [managerController::class, 'del_manager'])->name('manager.del_manager');
        Route::post('/manager/ajax_search_project', [managerController::class, 'ajax_search_project'])->name('manager.ajax_search_project');
        Route::get('/manager/views', [managerController::class, 'views'])->name('manager.views');
        Route::post('/manager/updstore', [managerController::class, 'updstore'])->name('manager.updstore');
        Route::resource('/manager', managerController::class);
        Route::post('/manager/print', [managerController::class, 'print'])->name('manager.print');
        Route::post('/manager/sel_worker_list', [managerController::class, 'sel_worker_list'])->name('manager.sel_worker_list');

        Route::post('/manager/delete_file', [managerController::class, 'delete_file'])->name('manager.delete_file');
//Route::get('/upload/delete_file/{id}', [UploadController::class,'delete_file'])->name('upload.delete_file');


        Route::post('/purchase/tbl', [purchaseController::class, 'tbl'])->name('purchase.tbl');
        Route::post('/purchase/ajax_search_purchase', [purchaseController::class, 'ajax_search_purchase'])->name('purchase.ajax_search_purchase');
        Route::post('/purchase/upd_purchase', [purchaseController::class, 'upd_purchase'])->name('purchase.upd_purchase');
        Route::post('/purchase/del_purchase', [purchaseController::class, 'del_purchase'])->name('purchase.del_purchase');
        Route::post('/purchase/ajax_search_project', [purchaseController::class, 'ajax_search_project'])->name('purchase.ajax_search_project');
        Route::get('/purchase/views', [purchaseController::class, 'views'])->name('purchase.views');
        Route::post('/purchase/updstore', [purchaseController::class, 'updstore'])->name('purchase.updstore');
        Route::resource('/purchase', purchaseController::class);
        Route::post('/purchase/print', [purchaseController::class, 'print'])->name('purchase.print');
        Route::post('/purchase/sel_worker_list', [purchaseController::class, 'sel_worker_list'])->name('purchase.sel_worker_list');
        Route::post('/purchase/delete_file', [purchaseController::class, 'delete_file'])->name('purchase.delete_file');
        Route::post('/purchase/upd_file', [purchaseController::class, 'upd_file'])->name('purchase.upd_file');
        Route::post('/purchase/updfile', [purchaseController::class, 'updfile'])->name('purchase.updfile');
        Route::post('/purchase/upd_note', [purchaseController::class, 'upd_note'])->name('purchase.upd_note');
        Route::post('/purchase/updnote', [purchaseController::class, 'updnote'])->name('purchase.updnote');
        Route::post('/purchase/upd_remark', [purchaseController::class, 'upd_remark'])->name('purchase.upd_remark');
        Route::post('/purchase/updremark', [purchaseController::class, 'updremark'])->name('purchase.updremark');
        Route::post('/calculate/tbl_remark', [purchaseController::class, 'tbl_remark'])->name('purchase.tbl_remark');
        Route::post('/calculate/ajax_search_remark', [purchaseController::class, 'ajax_search_remark'])->name('purchase.ajax_search_remark');
        Route::post('/purchase/del_remark', [purchaseController::class, 'del_remark'])->name('purchase.del_remark');
        Route::post('/purchase/change_remark', [purchaseController::class, 'change_remark'])->name('purchase.change_remark');


        Route::post('/expense/tbl', [expenseController::class, 'tbl'])->name('expense.tbl');
        Route::post('/expense/ajax_search_expense', [expenseController::class, 'ajax_search_expense'])->name('expense.ajax_search_expense');
        Route::post('/expense/upd_expense', [expenseController::class, 'upd_expense'])->name('expense.upd_expense');
        Route::post('/expense/del_expense', [expenseController::class, 'del_expense'])->name('expense.del_expense');
        Route::post('/expense/ajax_search_project', [expenseController::class, 'ajax_search_project'])->name('expense.ajax_search_project');
        Route::get('/expense/views', [expenseController::class, 'views'])->name('expense.views');
        Route::post('/expense/updstore', [expenseController::class, 'updstore'])->name('expense.updstore');
        Route::resource('/expense', expenseController::class);
        Route::post('/expense/print', [expenseController::class, 'print'])->name('expense.print');
        Route::post('/expense/sel_worker_list', [expenseController::class, 'sel_worker_list'])->name('expense.sel_worker_list');
        Route::post('/expense/delete_file', [expenseController::class, 'delete_file'])->name('expense.delete_file');
        Route::post('/expense/upd_file', [expenseController::class, 'upd_file'])->name('expense.upd_file');
        Route::post('/expense/updfile', [expenseController::class, 'updfile'])->name('expense.updfile');
        Route::post('/expense/upd_note', [expenseController::class, 'upd_note'])->name('expense.upd_note');
        Route::post('/expense/updnote', [expenseController::class, 'updnote'])->name('expense.updnote');
        Route::post('/expense/upd_remark', [expenseController::class, 'upd_remark'])->name('expense.upd_remark');
        Route::post('/expense/updremark', [expenseController::class, 'updremark'])->name('expense.updremark');
        Route::post('/calculate/tbl_remark', [expenseController::class, 'tbl_remark'])->name('expense.tbl_remark');
        Route::post('/calculate/ajax_search_remark', [expenseController::class, 'ajax_search_remark'])->name('expense.ajax_search_remark');
        Route::post('/expense/del_remark', [expenseController::class, 'del_remark'])->name('expense.del_remark');
        Route::post('/expense/change_remark', [expenseController::class, 'change_remark'])->name('expense.change_remark');
        Route::post('/expense/load_expense_form', [expenseController::class, 'load_expense_form'])->name('expense.load_expense_form');


        Route::post('/moraslat/tbl', [moraslatController::class, 'tbl'])->name('moraslat.tbl');
        Route::post('/moraslat/ajax_search_moraslat', [moraslatController::class, 'ajax_search_moraslat'])->name('moraslat.ajax_search_moraslat');
        Route::post('/moraslat/upd_moraslat', [moraslatController::class, 'upd_moraslat'])->name('moraslat.upd_moraslat');
        Route::post('/moraslat/del_moraslat', [moraslatController::class, 'del_moraslat'])->name('moraslat.del_moraslat');
        Route::post('/moraslat/ajax_search_project', [moraslatController::class, 'ajax_search_project'])->name('moraslat.ajax_search_project');
        Route::get('/moraslat/views', [moraslatController::class, 'views'])->name('moraslat.views');
        Route::post('/moraslat/updstore', [moraslatController::class, 'updstore'])->name('moraslat.updstore');
        Route::resource('/moraslat', moraslatController::class);
        Route::post('/moraslat/print', [moraslatController::class, 'print'])->name('moraslat.print');
        Route::post('/moraslat/sel_worker_list', [moraslatController::class, 'sel_worker_list'])->name('moraslat.sel_worker_list');
        Route::post('/moraslat/delete_file', [moraslatController::class, 'delete_file'])->name('moraslat.delete_file');
        Route::post('/moraslat/upd_file', [moraslatController::class, 'upd_file'])->name('moraslat.upd_file');
        Route::post('/moraslat/updfile', [moraslatController::class, 'updfile'])->name('moraslat.updfile');
        Route::post('/moraslat/upd_note', [moraslatController::class, 'upd_note'])->name('moraslat.upd_note');
        Route::post('/moraslat/updnote', [moraslatController::class, 'updnote'])->name('moraslat.updnote');
        Route::post('/moraslat/upd_remark', [moraslatController::class, 'upd_remark'])->name('moraslat.upd_remark');
        Route::post('/moraslat/updremark', [moraslatController::class, 'updremark'])->name('moraslat.updremark');
        Route::post('/calculate/tbl_remark', [moraslatController::class, 'tbl_remark'])->name('moraslat.tbl_remark');
        Route::post('/calculate/ajax_search_remark', [moraslatController::class, 'ajax_search_remark'])->name('moraslat.ajax_search_remark');
        Route::post('/moraslat/del_remark', [moraslatController::class, 'del_remark'])->name('moraslat.del_remark');
        Route::post('/moraslat/change_remark', [moraslatController::class, 'change_remark'])->name('moraslat.change_remark');
        Route::post('/moraslat/load_moraslat_form', [moraslatController::class, 'load_moraslat_form'])->name('moraslat.load_moraslat_form');
        Route::post('/moraslat/open_moraslat', [moraslatController::class, 'open_moraslat'])->name('moraslat.open_moraslat');
        Route::post('/moraslat/updopenstore', [moraslatController::class, 'updopenstore'])->name('moraslat.updopenstore');
        Route::post('/moraslat/show_history', [moraslatController::class, 'show_history'])->name('moraslat.show_history');


        Route::get('/vacation/views', [vacationController::class, 'views'])->name('vacation.views');
        Route::get('/vacation/views_all', [vacationController::class, 'views_all'])->name('vacation.views_all');

        Route::post('/vacation/tbl', [vacationController::class, 'tbl'])->name('vacation.tbl');
        Route::post('/vacation/ajax_search_vacation', [vacationController::class, 'ajax_search_vacation'])->name('vacation.ajax_search_vacation');
        Route::post('/vacation/upd_vacation', [vacationController::class, 'upd_vacation'])->name('vacation.upd_vacation');
        Route::post('/vacation/del_vacation', [vacationController::class, 'del_vacation'])->name('vacation.del_vacation');
        Route::post('/vacation/print_xlsx', [vacationController::class, 'print_xlsx'])->name('vacation.print_xlsx');
        Route::post('/vacation/print_pdf', [vacationController::class, 'print_pdf'])->name('vacation.print_pdf');

        Route::post('/vacation/tbl_all', [vacationController::class, 'tbl_all'])->name('vacation.tbl_all');
        Route::post('/vacation/ajax_search_vacation_all', [vacationController::class, 'ajax_search_vacation_all'])->name('vacation.ajax_search_vacation_all');
        Route::post('/vacation/print', [vacationController::class, 'print'])->name('vacation.print');

        Route::post('/vacation/updstore', [vacationController::class, 'updstore'])->name('vacation.updstore');
        Route::resource('/vacation', vacationController::class);


        Route::post('/report/print_worker_pdf', [ReportController::class, 'print_worker_pdf'])->name('report.print_worker_pdf');
        Route::post('/report/print_worker_xlsx', [ReportController::class, 'print_worker_xlsx'])->name('report.print_worker_xlsx');

        Route::post('/report/print_shop_pdf', [ReportController::class, 'print_shop_pdf'])->name('report.print_shop_pdf');
        Route::post('/report/print_shop_xlsx', [ReportController::class, 'print_shop_xlsx'])->name('report.print_shop_xlsx');

        Route::post('/report/print_fnancial_pdf', [ReportController::class, 'print_fnancial_pdf'])->name('report.print_fnancial_pdf');
        Route::post('/report/print_fnancial_xlsx', [ReportController::class, 'print_fnancial_xlsx'])->name('report.print_fnancial_xlsx');

        Route::post('/report/print_purchase_pdf', [ReportController::class, 'print_purchase_pdf'])->name('report.print_purchase_pdf');
        Route::post('/report/print_purchase_xlsx', [ReportController::class, 'print_purchase_xlsx'])->name('report.print_purchase_xlsx');


        Route::post('/report/print_expense_pdf', [ReportController::class, 'print_expense_pdf'])->name('report.print_expense_pdf');
        Route::post('/report/print_expense_xlsx', [ReportController::class, 'print_expense_xlsx'])->name('report.print_expense_xlsx');

        Route::post('/report/print_calculate_pdf', [ReportController::class, 'print_calculate_pdf'])->name('report.print_calculate_pdf');
        Route::post('/report/print_calculate_xlsx', [ReportController::class, 'print_calculate_xlsx'])->name('report.print_calculate_xlsx');
        Route::post('/report/print_violation_pdf', [ReportController::class, 'print_violation_pdf'])->name('report.print_violation_pdf');
        Route::post('/report/print_violation_xlsx', [ReportController::class, 'print_violation_xlsx'])->name('report.print_violation_xlsx');

        Route::post('/report/print_vacation_xlsx', [ReportController::class, 'print_vacation_xlsx'])->name('report.print_vacation_xlsx');
        Route::post('/report/print_vacation_pdf', [ReportController::class, 'print_vacation_pdf'])->name('report.print_vacation_pdf');
        Route::resource('/report', ReportController::class);


    });



