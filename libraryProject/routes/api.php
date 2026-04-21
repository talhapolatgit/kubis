<?php

use App\Http\Controllers\Api\CatalogApiController;
use App\Http\Controllers\Api\MemberAuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('mobile')->group(function () {
    Route::post('/auth/register', [MemberAuthController::class, 'register']);
    Route::post('/auth/token', [MemberAuthController::class, 'token']);

    Route::middleware('jwt.uye')->group(function () {
        Route::get('/uyelik-bilgileri', [MemberAuthController::class, 'profile']);
        Route::get('/kitaplar', [CatalogApiController::class, 'index']);
        Route::get('/kitapdetay', [CatalogApiController::class, 'catalogDetail']);
        Route::get('/kategoriler', [CatalogApiController::class, 'categories']);
        Route::get('/kutuphaneler', [CatalogApiController::class, 'libraries']);
        Route::get('/oduncler', [CatalogApiController::class, 'loans']);
        Route::get('/favoriler', [CatalogApiController::class, 'memberFavorites']);
        Route::post('/favoriekle', [CatalogApiController::class, 'insertFavorites']);
        Route::delete('/favorisil', [CatalogApiController::class, 'deleteFavorites']);
        Route::get('/beklemeler', [CatalogApiController::class, 'memberWaitings']);
        Route::post('/beklemeekle', [CatalogApiController::class, 'insertWaitings']);
        Route::delete('/beklemesil', [CatalogApiController::class, 'deleteWaitings']);
        Route::get('/rezervasyonlar', [CatalogApiController::class, 'memberReservations']);
        Route::post('/rezervasyonekle', [CatalogApiController::class, 'insertReservation']);
        Route::post('/rezervasyoniptal', [CatalogApiController::class, 'cancelReservation']);
        Route::get('/uyesayac', [CatalogApiController::class, 'memberCounts']);
    });
});
