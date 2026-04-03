<?php

use App\Http\Controllers\Api\ArticleApiController;
use App\Http\Controllers\Api\ListingApiController;
use App\Http\Controllers\Api\NewsApiController;
use App\Http\Controllers\Api\SeoApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Public API endpoints for headless consumption
    Route::get('/articles', [ArticleApiController::class, 'index']);
    Route::get('/articles/{slug}', [ArticleApiController::class, 'show']);

    Route::get('/listings', [ListingApiController::class, 'index']);
    Route::get('/listings/{id}', [ListingApiController::class, 'show']);

    Route::get('/news', [NewsApiController::class, 'index']);
    Route::get('/news/{id}', [NewsApiController::class, 'show']);

    Route::get('/seo', [SeoApiController::class, 'show']);
});
