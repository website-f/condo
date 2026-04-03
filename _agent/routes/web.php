<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\ListingController;
use App\Http\Controllers\SeoController;
use App\Http\Controllers\SocialMediaController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\BillingController;
use App\Http\Middleware\AgentAuth;
use Illuminate\Support\Facades\Route;

// Auth routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected routes
Route::middleware(AgentAuth::class)->group(function () {

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Articles
    Route::resource('articles', ArticleController::class)->except(['show']);

    // Listings
    Route::get('/listings', [ListingController::class, 'index'])->name('listings.index');
    Route::get('/listings/create', [ListingController::class, 'create'])->name('listings.create');
    Route::post('/listings', [ListingController::class, 'store'])->name('listings.store');
    Route::get('/listings/{id}/edit', [ListingController::class, 'edit'])->name('listings.edit');
    Route::put('/listings/{id}', [ListingController::class, 'update'])->name('listings.update');
    Route::delete('/listings/{id}', [ListingController::class, 'destroy'])->name('listings.destroy');
    Route::get('/listings/{id}', [ListingController::class, 'show'])->name('listings.show');

    // SEO
    Route::resource('seo', SeoController::class);

    // Social Media
    Route::get('/social', [SocialMediaController::class, 'index'])->name('social.index');
    Route::get('/social/account/create', [SocialMediaController::class, 'createAccount'])->name('social.account.create');
    Route::post('/social/account', [SocialMediaController::class, 'storeAccount'])->name('social.account.store');
    Route::delete('/social/account/{account}', [SocialMediaController::class, 'destroyAccount'])->name('social.account.destroy');
    Route::get('/social/post/create', [SocialMediaController::class, 'createPost'])->name('social.post.create');
    Route::post('/social/post', [SocialMediaController::class, 'storePost'])->name('social.post.store');
    Route::delete('/social/post/{post}', [SocialMediaController::class, 'destroyPost'])->name('social.post.destroy');

    // Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

    // News
    Route::get('/news', [NewsController::class, 'index'])->name('news.index');
    Route::get('/news/create', [NewsController::class, 'create'])->name('news.create');
    Route::post('/news', [NewsController::class, 'store'])->name('news.store');
    Route::get('/news/{id}/edit', [NewsController::class, 'edit'])->name('news.edit');
    Route::put('/news/{id}', [NewsController::class, 'update'])->name('news.update');
    Route::delete('/news/{id}', [NewsController::class, 'destroy'])->name('news.destroy');
    Route::get('/news/{id}', [NewsController::class, 'show'])->name('news.show');

    // Profile
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // Billing
    Route::get('/billing', [BillingController::class, 'index'])->name('billing.index');
});
