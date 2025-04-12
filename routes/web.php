<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StatusController;
use App\Http\Controllers\EnquiryController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\VelzonRoutesController;

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

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function () {

    Route::controller(VelzonRoutesController::class)->group(function () {

        // dashboards
        Route::get('/', 'dashboard');

    });
});

// Route::controller(StatusController::class)->group(function () {
//     Route::get('/status', 'index')->name('status.index');
//     Route::get('/status/create','create');
//     Route::post('/status', 'store');
//     Route::get('/status/show/{status}', 'show');
// });

Route::resource('announcements', AnnouncementController::class)->only(['index', 'store', 'update', 'destroy']);
Route::get('/announcements/fetchdata', [AnnouncementController::class, 'fetchData']);

Route::resource('galleries', GalleryController::class);

Route::resource('enquiries', EnquiryController::class)->only(['index', 'store', 'update', 'destroy']);;
Route::get('/enquiries/fetchdata', [EnquiryController::class, 'fetchdata']);

