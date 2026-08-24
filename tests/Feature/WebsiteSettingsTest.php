<?php

use App\Filament\Pages\WebsiteSettings;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Inerba\DbConfig\DbConfig;
use Livewire\Livewire;

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

it('saves the footer cta from the settings page and serves it per language', function () {
    $this->actingAs(User::factory()->create());

    Livewire::test(WebsiteSettings::class)
        ->fillForm([
            'footer_cta_title' => 'Build the next landscape',
            'footer_cta_title_id' => 'Bangun lanskap berikutnya',
            'footer_cta_description' => 'Districts, business and civil society, working on one plan.',
            'footer_cta_description_id' => 'Kabupaten, dunia usaha dan masyarakat sipil, satu rencana.',
            'footer_cta_button_text' => 'Join now',
            'footer_cta_button_text_id' => 'Gabung sekarang',
            'footer_cta_button_url' => 'https://kabupatenlestari.org/join',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $settings = collect($this->getJson('/api/settings?group=website')->assertOk()->json('data'))
        ->pluck('settings', 'key');

    expect($settings['footer_cta_title'])->toBe('Build the next landscape')
        ->and($settings['footer_cta_title_id'])->toBe('Bangun lanskap berikutnya')
        ->and($settings['footer_cta_description'])->toBe('Districts, business and civil society, working on one plan.')
        ->and($settings['footer_cta_description_id'])->toBe('Kabupaten, dunia usaha dan masyarakat sipil, satu rencana.')
        ->and($settings['footer_cta_button_text'])->toBe('Join now')
        ->and($settings['footer_cta_button_text_id'])->toBe('Gabung sekarang')
        ->and($settings['footer_cta_button_url'])->toBe('https://kabupatenlestari.org/join');
});

it('rejects a footer cta button url that is not a url', function () {
    $this->actingAs(User::factory()->create());

    Livewire::test(WebsiteSettings::class)
        ->fillForm(['footer_cta_button_url' => 'not a url'])
        ->call('save')
        ->assertHasFormErrors(['footer_cta_button_url']);
});
