<?php

use Illuminate\Support\Facades\Route;
use IMS\ImageSearch\Application\Controllers\ImageSearchController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/imagesearch',[ImageSearchController::class, 'index'])->name('imagesearch.search');