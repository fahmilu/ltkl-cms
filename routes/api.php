<?php

use App\Http\Controllers\Api\CollectionController;
use App\Http\Controllers\Api\ContactUsController;
use App\Http\Controllers\Api\KabupatenController;
use App\Http\Controllers\Api\PageController;
use App\Http\Controllers\Api\ParticipationPathwayController;
use App\Http\Controllers\Api\PillarController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\SettingController;
use Illuminate\Support\Facades\Route;

/*Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');*/

/* Collections */
Route::get('collections', [CollectionController::class, 'index']);
Route::get('collection/{slug}', [CollectionController::class, 'show']);
/* Pages */
Route::get('pages', [PageController::class, 'index']);
Route::get('page/{slug?}', [PageController::class, 'show']);
/* Posts */
Route::get('posts', [PostController::class, 'index']);
Route::get('post/{slug}', [PostController::class, 'show']);
/* Kabupatens */
Route::get('kabupatens', [KabupatenController::class, 'index']);
Route::get('kabupatens/map', [KabupatenController::class, 'map']);
Route::get('kabupaten/{slug}', [KabupatenController::class, 'show']);
/* Pillars */
Route::get('pillars', [PillarController::class, 'index']);
Route::get('pillar/{slug}', [PillarController::class, 'show']);
/* Participation Pathways */
Route::get('participation-pathways', [ParticipationPathwayController::class, 'index']);
Route::get('participation-pathway/{slug}', [ParticipationPathwayController::class, 'show']);
/* Settings */
Route::get('settings', [SettingController::class, 'index']);
/* Navigation */
Route::get('navigations', [PageController::class, 'navigations']);
/* Contact Us */
Route::post('contact-us', [ContactUsController::class, 'store']);
