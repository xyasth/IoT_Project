<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RobotController;


Route::get('/', function () {
    return view('welcome');
});


Route::get('/', [RobotController::class, 'index']);
Route::post('/start', [RobotController::class, 'start']);
Route::post('/finish', [RobotController::class, 'finish']);
Route::get('/export', [RobotController::class, 'export']);
Route::post('/reset', [RobotController::class, 'reset']);
