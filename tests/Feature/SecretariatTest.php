<?php

use App\Enums\CollectionComponentSource;
use App\Enums\SecretariatLevel;
use App\Filament\Resources\Secretariats\Pages\CreateSecretariat;
use App\Filament\Resources\Secretariats\Pages\EditSecretariat;
use App\Models\Secretariat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function makeSecretariat(array $overrides = []): Secretariat
{
    return Secretariat::create(array_merge([
        'is_active' => true,
        'image' => 'secretariats/jane.jpg',
        'name' => 'Jane Doe',
        'role' => 'Executive Secretary',
        'role_id' => 'Sekretaris Eksekutif',
        'level' => SecretariatLevel::TOP_LEVEL->value,
        'sorted_at' => 0,
    ], $overrides));
}

it('offers exactly three levels, stored as 1, 2 and 3', function () {
    $cases = SecretariatLevel::cases();

    expect(array_map(fn(SecretariatLevel $case): int => $case->value, $cases))
        ->toBe([1, 2, 3])
        ->and(array_map(fn(SecretariatLevel $case): string => $case->getLabel(), $cases))
        ->toBe(['Top Level', 'Manager', 'Staff']);
});

it('creates a secretariat member from the admin form', function () {
    $this->actingAs(User::factory()->create());

    Livewire::test(CreateSecretariat::class)
        ->fillForm([
            'name' => 'John Smith',
            'role' => 'Program Manager',
            'role_id' => 'Manajer Program',
            'level' => SecretariatLevel::MANAGER->value,
            'image' => UploadedFile::fake()->image('john.jpg'),
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $secretariat = Secretariat::firstOrFail();

    expect($secretariat->name)->toBe('John Smith')
        ->and($secretariat->role)->toBe('Program Manager')
        ->and($secretariat->role_id)->toBe('Manajer Program')
        ->and($secretariat->level)->toBe(SecretariatLevel::MANAGER)
        ->and($secretariat->getRawOriginal('level'))->toBe(2);
});

it('edits an existing secretariat member', function () {
    $this->actingAs(User::factory()->create());
    $secretariat = makeSecretariat();

    Livewire::test(EditSecretariat::class, ['record' => $secretariat->getRouteKey()])
        ->fillForm(['level' => SecretariatLevel::STAFF->value])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($secretariat->refresh()->level)->toBe(SecretariatLevel::STAFF)
        ->and($secretariat->getRawOriginal('level'))->toBe(3);
});

it('is offered as a collection page block source', function () {
    expect(CollectionComponentSource::SECRETARIAT->getLabel())->toBe('Secretariat')
        ->and(CollectionComponentSource::SECRETARIAT->getEndpoint())->toBe('/api/secretariats');
});

it('lists only published members through the api, ordered by sorted_at', function () {
    makeSecretariat(['name' => 'Second', 'sorted_at' => 1]);
    makeSecretariat(['name' => 'First', 'sorted_at' => 0]);
    makeSecretariat(['name' => 'Hidden', 'is_active' => false, 'sorted_at' => -1]);

    $names = collect($this->getJson('/api/secretariats')->assertOk()->json('data'))
        ->pluck('name')
        ->all();

    expect($names)->toBe(['First', 'Second']);
});

it('returns the level as an integer plus its label', function () {
    makeSecretariat(['level' => SecretariatLevel::MANAGER->value]);

    $member = $this->getJson('/api/secretariats')->assertOk()->json('data.0');

    expect($member['level'])->toBe(2)
        ->and($member['level_label'])->toBe('Manager');
});

it('publishes the image as a full url', function () {
    makeSecretariat();

    $member = $this->getJson('/api/secretariats')->assertOk()->json('data.0');

    expect($member['image'])->toContain('/storage/secretariats/jane.jpg');
});
