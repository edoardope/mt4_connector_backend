<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\IstanceController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/istances', [IstanceController::class, 'index']);

Route::get('/searchIstance/{id}', [IstanceController::class, 'searchIstance']);

Route::get('/createIstance/{istance_name}', [IstanceController::class, 'createIstance']);

Route::post('/status', [IstanceController::class, 'status']);