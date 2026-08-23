<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns the custom 404 at the root url', function () {
    $this->get('/')
        ->assertNotFound()
        ->assertSee('Page not found')
        ->assertSee('Error 404');
});

it('uses the same page for any unknown url', function () {
    $this->get('/does-not-exist')
        ->assertNotFound()
        ->assertSee('Page not found');
});

it('points visitors at the CMS', function () {
    $this->get('/')->assertNotFound()->assertSee(route('filament.administrator.auth.login'), false);
});

it('keeps search engines out of the error page', function () {
    $this->get('/')->assertNotFound()->assertSee('noindex', false);
});

it('keeps the home route registered for the auth layouts', function () {
    // The auth layouts link to route('home'); dropping the name would throw.
    expect(route('home'))->toBe(url('/'));
});

it('leaves the panel and the api reachable', function () {
    $this->get('/administrator/login')->assertOk();
    $this->getJson('/api/pages')->assertOk();
});
