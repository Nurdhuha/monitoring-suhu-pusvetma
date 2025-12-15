<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\DataSuhuController;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\UserController;

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

Route::get('/', function () {
    return redirect('/home');
});

// Admin Routes
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/home', [AdminController::class, 'home'])->name('admin.home');
    Route::get('/', [AdminController::class, 'home'])->name('admin.dashboard');
    Route::get('/data-suhu/download', [DataSuhuController::class, 'downloadExcel'])->name('admin.data-suhu.download');
    Route::post('/data-suhu/import', [DataSuhuController::class, 'import'])->name('admin.data-suhu.import');
    Route::resource('devices', DeviceController::class)->names('admin.devices');
    Route::resource('data-suhu', DataSuhuController::class)->names('admin.data-suhu');
});

// Super Admin Routes
Route::middleware(['auth', 'superadmin'])->prefix('superadmin')->group(function () {
    Route::get('/home', [SuperAdminController::class, 'home'])->name('superadmin.home');
    Route::get('/', [SuperAdminController::class, 'home'])->name('superadmin.dashboard');
    Route::get('/data-suhu/download', [DataSuhuController::class, 'downloadExcel'])->name('superadmin.data-suhu.download');
    Route::post('/data-suhu/import', [DataSuhuController::class, 'import'])->name('superadmin.data-suhu.import');
    Route::resource('users', UserController::class)->names('superadmin.users');
    Route::resource('devices', DeviceController::class)->names('superadmin.devices');
    Route::resource('data-suhu', DataSuhuController::class)->names('superadmin.data-suhu');
});

// Auth routes (assuming Laravel UI or Breeze is installed)
Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home')->middleware('auth');
Route::get('/dashboard', [App\Http\Controllers\HomeController::class, 'dashboard'])->name('dashboard')->middleware('auth');

Route::get('/get-temperature-data', [App\Http\Controllers\HomeController::class, 'getTemperatureData'])->name('get.temperature.data');
