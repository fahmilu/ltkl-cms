<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

// This is a headless CMS: the public site is served elsewhere, so the root URL
// deliberately 404s rather than exposing a landing page. The route stays
// registered and named because the auth layouts link to route('home').
Route::get('/', function () {
    abort(404);
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

require __DIR__ . '/auth.php';

