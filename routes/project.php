<?php
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;


Route::group([
    'middleware'=>(['auth']),
    'as'=>'dashboard.',
    'prefix'=>'dashboard',

   ],
function () {
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::resource('/categories', CategoriesController::class);
//  Route::resource('/photo', PhotoController::class);
Route::post('/project/tbl', [ProjectController::class, 'tbl'])->name('projects.tbl');
Route::post('/project/ajax_search_project', [ProjectController::class, 'ajax_search_project'])->name('projects.ajax_search_project');
Route::post('/project/upd_project', [ProjectController::class, 'upd_project'])->name('projects.upd_project');
Route::post('/project/del_project', [ProjectController::class, 'del_project'])->name('projects.del_project');
Route::post('/project/ajax_search_project', [ProjectController::class, 'ajax_search_project'])->name('projects.ajax_search_project');
Route::get('/project/views', [ProjectController::class, 'views'])->name('projects.views');
Route::post('/project/updstore', [ProjectController::class, 'updstore'])->name('projects.updstore');
Route::resource('/project', ProjectController::class);



/*
Route::post('/emps/show_job_cat', [EmpsController::class, 'show_job_cat'])->name('emps.show_job_cat');
Route::post('/emps/load_emp_div', [EmpsController::class, 'load_emp_div'])->name('emps.load_emp_div');
 Route::post('/emps/sel_emp_supervisor', [EmpsController::class, 'sel_emp_supervisor'])->name('emps.sel_emp_supervisor');

Route::resource('/emps',EmpsController::class);
*/


});

