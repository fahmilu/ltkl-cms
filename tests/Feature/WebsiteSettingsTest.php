<?php

use App\Filament\Pages\WebsiteSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Inerba\DbConfig\DbConfig;

uses(RefreshDatabase::class);

it('defaults the language switcher to on', function () {
    expect((new WebsiteSettings)->getDefaultData()['multi_language'])->toBeTrue();
});

it('serves the logos as full urls, svg included', function () {
    DbConfig::set('website.main_logo', 'logo/main.svg');
    DbConfig::set('website.footer_logo', 'logo/footer.png');

    $settings = collect($this->getJson('/api/settings?group=website')->assertOk()->json('data'))
        ->pluck('settings', 'key');

    expect($settings['main_logo'])->toBe(Storage::disk('public')->url('logo/main.svg'))
        ->and($settings['footer_logo'])->toBe(Storage::disk('public')->url('logo/footer.png'));
});

it('serves an empty logo as null rather than the bare site url', function () {
    DbConfig::set('website.main_logo', null);

    $this->getJson('/api/settings?group=website&key=main_logo')
        ->assertOk()
        ->assertJsonPath('data.0.settings', null);
});

it('serves the footer description in both languages', function () {
    DbConfig::set('website.footer_description', 'A coalition of districts.');
    DbConfig::set('website.footer_description_id', 'Koalisi kabupaten.');

    $settings = collect($this->getJson('/api/settings?group=website')->assertOk()->json('data'))
        ->pluck('settings', 'key');

    expect($settings['footer_description'])->toBe('A coalition of districts.')
        ->and($settings['footer_description_id'])->toBe('Koalisi kabupaten.');
});

it('serves the multi language flag as a boolean', function () {
    DbConfig::set('website.multi_language', false);

    $this->getJson('/api/settings?group=website&key=multi_language')
        ->assertOk()
        ->assertJsonPath('data.0.group', 'website')
        ->assertJsonPath('data.0.key', 'multi_language')
        ->assertJsonPath('data.0.settings', false);

    DbConfig::set('website.multi_language', true);

    $this->getJson('/api/settings?group=website&key=multi_language')
        ->assertOk()
        ->assertJsonPath('data.0.settings', true);
});
