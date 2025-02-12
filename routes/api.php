<?php

use App\Http\Controllers\API\FileController;
use App\Http\Controllers\API\RegisterController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\LogoutController;
use Illuminate\Support\Facades\Route;

Route::controller(RegisterController::class)->group(function () {
    Route::post('register', 'register');
    Route::post('login', 'login');
});
Route::apiResource('/files', FileController::class);
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/login', LoginController::class);
    Route::post('/logout', LogoutController::class);
}
);
