<?php

use App\Filament\Resources\Kabupatens\Pages\CreateKabupaten;
use App\Models\Kabupaten;
use App\Models\User;
use App\Services\LocationSearch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Cache::flush();
    $this->actingAs(User::factory()->create());
});

function fakeNominatim(array $results): void
{
    Http::fake([
        'nominatim.openstreetmap.org/*' => Http::response($results),
    ]);
}

it('maps geocoder results into labelled coordinates', function () {
    fakeNominatim([
        ['display_name' => 'Siak, Riau, Indonesia', 'lat' => '0.8118231', 'lon' => '101.8001234'],
        ['display_name' => 'Siak Sri Indrapura, Riau', 'lat' => '0.7994', 'lon' => '102.0501'],
    ]);

    $places = app(LocationSearch::class)->search('Siak');

    expect($places)->toHaveCount(2)
        ->and($places[0])->toBe([
            'label' => 'Siak, Riau, Indonesia',
            'latitude' => 0.8118231,
            'longitude' => 101.8001234,
        ]);
});

it('sends the required user agent and country filter', function () {
    fakeNominatim([]);

    app(LocationSearch::class)->search('Sintang');

    Http::assertSent(function ($request) {
        return $request->hasHeader('User-Agent')
            && $request['q'] === 'Sintang'
            && $request['countrycodes'] === 'id'
            && $request['format'] === 'jsonv2';
    });
});

it('ignores searches shorter than three characters without calling the API', function () {
    fakeNominatim([['display_name' => 'x', 'lat' => '1', 'lon' => '2']]);

    expect(app(LocationSearch::class)->search('Si'))->toBe([]);

    Http::assertNothingSent();
});

it('caches repeat searches so the rate limit is respected', function () {
    fakeNominatim([['display_name' => 'Siak, Riau', 'lat' => '0.81', 'lon' => '101.8']]);

    $service = app(LocationSearch::class);
    $service->search('Siak');
    $service->search('siak');
    $service->search('  SIAK  ');

    Http::assertSentCount(1);
});

it('returns no results instead of failing when the provider errors', function () {
    Http::fake([
        'nominatim.openstreetmap.org/*' => Http::response('upstream down', 503),
    ]);

    expect(app(LocationSearch::class)->search('Siak'))->toBe([]);
});

it('returns no results instead of failing when the provider is unreachable', function () {
    Http::fake(fn() => throw new ConnectionException('Connection timed out'));

    expect(app(LocationSearch::class)->search('Siak'))->toBe([]);
});

it('skips malformed results missing coordinates', function () {
    fakeNominatim([
        ['display_name' => 'No coordinates here'],
        ['display_name' => 'Siak, Riau', 'lat' => '0.81', 'lon' => '101.8'],
    ]);

    expect(app(LocationSearch::class)->search('Siak'))->toHaveCount(1);
});

it('moves the pin when a searched place is selected', function () {
    fakeNominatim([]);

    Livewire::test(CreateKabupaten::class)
        ->fillForm([
            'title' => 'Siak Regency',
            'title_id' => 'Kabupaten Siak',
            'slug' => 'siak-regency',
            'slug_id' => 'kabupaten-siak',
        ])
        // The option key carries the coordinates chosen from the search results.
        ->set('data.location_search', '0.8118231,101.8001234')
        ->assertSchemaStateSet([
            'latitude' => 0.8118231,
            'longitude' => 101.8001234,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $kabupaten = Kabupaten::firstWhere('slug', 'siak-regency');

    expect((float) $kabupaten->latitude)->toBe(0.8118231)
        ->and((float) $kabupaten->longitude)->toBe(101.8001234);
});

it('does not store the search field itself', function () {
    fakeNominatim([]);

    Livewire::test(CreateKabupaten::class)
        ->fillForm([
            'title' => 'Sintang Regency',
            'title_id' => 'Kabupaten Sintang',
            'slug' => 'sintang-regency',
            'slug_id' => 'kabupaten-sintang',
        ])
        ->set('data.location_search', '0.0667,111.5')
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Kabupaten::firstWhere('slug', 'sintang-regency')->getAttributes())
        ->not->toHaveKey('location_search');
});
