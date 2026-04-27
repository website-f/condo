<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\ListingController;
use App\Http\Controllers\PublicSiteController;
use App\Http\Controllers\SeoController;
use App\Http\Controllers\SocialMediaController;
use App\Http\Controllers\SocialChannelController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RecentlyDeletedController;
use App\Http\Controllers\TutorialController;
use App\Http\Middleware\AgentAuth;
use App\Http\Middleware\ResolveAgentSubsite;
use Illuminate\Support\Facades\Route;

// Public agent subdomain routes — only match when Host is *.condo.com.my (or *.condo.test)
foreach (['condo.com.my', 'condo.test'] as $publicBase) {
    Route::domain('{publicAgentLabel}.' . $publicBase)
        ->middleware(ResolveAgentSubsite::class)
        ->group(function () {
            Route::get('/', [PublicSiteController::class, 'home'])->name('public.home');
            Route::get('/listings', [PublicSiteController::class, 'listings'])->name('public.listings');
            Route::get('/listings/{source}/{id}', [PublicSiteController::class, 'listing'])
                ->whereIn('source', ['ipp', 'icp'])
                ->name('public.listing');
            Route::get('/articles', [PublicSiteController::class, 'articles'])->name('public.articles');
            Route::get('/articles/{slug}', [PublicSiteController::class, 'article'])->name('public.article');
        });
}

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
    Route::get('/seo', [SeoController::class, 'index'])->name('seo.index');
    Route::get('/seo/{listing}/edit', [SeoController::class, 'edit'])->name('seo.edit');
    Route::put('/seo/{listing}', [SeoController::class, 'update'])->name('seo.update');

    // Social Media
    Route::get('/social', [SocialMediaController::class, 'index'])->name('social.index');
    Route::get('/social/create', [SocialMediaController::class, 'create'])->name('social.create');
    Route::post('/social', [SocialMediaController::class, 'store'])->name('social.store');
    Route::get('/social/channels', [SocialChannelController::class, 'index'])->name('social.channels.index');
    Route::get('/social/channels/create', [SocialChannelController::class, 'createChannel'])->name('social.channels.create');
    Route::get('/social/channels/oauth-start/{network}', [SocialChannelController::class, 'oauthStart'])->name('social.channels.oauth-start');
    Route::post('/social/channels', [SocialChannelController::class, 'storeChannel'])->name('social.channels.store');
    Route::post('/social/channels/oauth-import', [SocialChannelController::class, 'importOauthChannels'])->name('social.channels.oauth-import');
    Route::post('/social/channels/{channel}/refresh', [SocialChannelController::class, 'refreshChannel'])->name('social.channels.refresh');
    Route::get('/social/channels/{channel}/edit', [SocialChannelController::class, 'editChannel'])->name('social.channels.edit');
    Route::put('/social/channels/{channel}', [SocialChannelController::class, 'updateChannel'])->name('social.channels.update');
    Route::delete('/social/channels/{channel}', [SocialChannelController::class, 'destroyChannel'])->name('social.channels.destroy');
    Route::get('/social/accounts/create', [SocialChannelController::class, 'createAccount'])->name('social.accounts.create');
    Route::post('/social/accounts', [SocialChannelController::class, 'storeAccount'])->name('social.accounts.store');
    Route::get('/social/accounts/{session}/edit', [SocialChannelController::class, 'editAccount'])->name('social.accounts.edit');
    Route::put('/social/accounts/{session}', [SocialChannelController::class, 'updateAccount'])->name('social.accounts.update');
    Route::get('/social/{group}/edit', [SocialMediaController::class, 'edit'])->name('social.edit');
    Route::put('/social/{group}', [SocialMediaController::class, 'update'])->name('social.update');
    Route::delete('/social/{group}', [SocialMediaController::class, 'destroy'])->name('social.destroy');

    // Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

    // Recently Deleted
    Route::get('/recently-deleted', [RecentlyDeletedController::class, 'index'])->name('recently-deleted.index');
    Route::post('/recently-deleted/restore', [RecentlyDeletedController::class, 'restore'])->name('recently-deleted.restore');
    Route::delete('/recently-deleted', [RecentlyDeletedController::class, 'destroy'])->name('recently-deleted.destroy');

    // Profile
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::get('/visit-site', [ProfileController::class, 'visitSite'])->name('profile.visit-site');

    // Tutorials
    Route::get('/tutorials', [TutorialController::class, 'index'])->name('tutorials.index');
    Route::get('/tutorials/{topic}', [TutorialController::class, 'show'])->name('tutorials.show');
});
