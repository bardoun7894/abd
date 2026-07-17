<?php

use App\Http\Controllers\ZatcaController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

//use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\ServiceController;

Route::get('show_404', function () {
    return redirect('/404');
})->name('404');

// LOCAL-ONLY invoice-extraction test UI (no auth / no main DB). Gated on env('INVOICE_LOCAL_UI').
Route::get('/local-invoices', [\App\Http\Controllers\InvoiceLocalTestController::class, 'index']);
Route::post('/local-invoices', [\App\Http\Controllers\InvoiceLocalTestController::class, 'store']);
Route::get('/local-invoices/{id}', [\App\Http\Controllers\InvoiceLocalTestController::class, 'show'])->whereNumber('id');


Route::get('show_404', [HomeController::class, 'show_404'])->name('show_404');
Route::get('show_not_allow', [HomeController::class, 'show_not_allow'])->name('show_not_allow');
Route::get('show_enter_data', [HomeController::class, 'show_enter_data'])->name('show_enter_data');
Route::get('show_enter_data_other', [HomeController::class, 'show_enter_data'])->name('show_enter_data_other');
Route::get('edit_profile', [HomeController::class, 'edit_profile'])->name('edit_profile');
Route::post('updateProfile', [HomeController::class, 'updateProfile'])->name('updateProfile');
Route::post('load_alerts', [HomeController::class, 'load_alerts'])->name('load_alerts');

Route::post('notify_num', [HomeController::class, 'notify_num'])->name('notify_num');

Route::get('/', function () {
    return view('home');
})->middleware(['auth', 'verified'])->name('home');

Route::post('/images_upload', [UploadController::class, 'images'])->name('upload.images');

Route::get('/', [HomeController::class, 'index'])->middleware(['auth', 'verified'])->name('home');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/insert_vehicle',[VehicleController::class,"add"])->name("insert_vehicle");
    Route::post('/store_vehicle',[VehicleController::class,"store"])->name("store_vehicle");
    Route::get('/vehicles',[VehicleController::class,"index"])->name("vehicles.index");

    Route::get('/destroy_vehicle/{id}',[VehicleController::class,"destroy"])->name("vehicles.destroy");
    Route::get('/edit_vehicle/{id}',[VehicleController::class,"add"])->name("vehicles.edit");
    Route::post('/update_vehicle/{id}',[VehicleController::class,"update"])->name("update_vehicle");
    Route::post('/vehicles/ai-extract',[VehicleController::class,"aiExtract"])->name("vehicles.ai_extract"); // Spec 004 B4


});



Route::get('profile', function () {
    return view('users.profile');
})->name('profile');
/*Route::get('edit_profile', function () {
    return view('users.edit_profile');
})->name('edit_profile');*/

require __DIR__ . '/auth.php';
require __DIR__ . '/dashboard.php';
Route::get('/zatca', [ZatcaController::class, 'index'])->middleware('auth')->name('zatca.index');
Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');
Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');
Route::get('/tasks/{task}/subtasks', [TaskController::class, 'getSubtasks'])->name('tasks.subtasks');
Route::post('/subtasks', [TaskController::class, 'storeSubtask'])->name('subtasks.store');
Route::get('/tasks/{task}/edit', [TaskController::class, 'edit'])->name('tasks.edit');
Route::put('/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
Route::get('/tasks/{task}', [TaskController::class, 'destroyTask'])->name('tasks.destroy');
// جداول المهام
Route::prefix('schedules')->group(function () {
    Route::post('/', [TaskController::class, 'storeSchedule'])->name('schedules.store');
    Route::get('/{id}', [TaskController::class, 'showSchedule'])->name('schedules.show');
    Route::get('/complete/{id}', [TaskController::class, 'completeSchedule'])->name('schedules.complete');
    Route::put('/{id}', [TaskController::class, 'updateSchedule'])->name('schedules.update');
    Route::get('/{id}', [TaskController::class, 'destroySchedule'])->name('schedules.destroy');
    Route::get('/{id}/pdf', [TaskController::class, 'exportPDF'])->name('schedules.pdf');
    Route::get('/{id}/excel', [TaskController::class, 'exportExcel'])->name('schedules.excel');
    Route::get('/{id}/print', [TaskController::class, 'printSchedule'])->name('schedules.print');
    Route::get('/{id}/incomplete', [TaskController::class, 'incompleteSchedule'])->name('schedules.incomplete');
});

// الخدمات
Route::prefix('services')->group(function () {
    Route::post('/', [ServiceController::class, 'store'])->name('services.store');
    Route::get('/', [ServiceController::class, 'index'])->name('services.index');
    Route::get('/{service}', [ServiceController::class, 'show'])->name('services.show');
    Route::get('/{service}', [ServiceController::class, 'update'])->name('services.update');
    Route::get('/destroy/{service}', [ServiceController::class, 'destroy'])->name('services.destroy');
});
