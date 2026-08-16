<?php

use App\Enums\CollectionComponentSource;
use App\Filament\Resources\Pages\Pages\CreatePage;
use App\Models\User;
use Filament\Forms\Components\Builder as BuilderField;
use Filament\Forms\Components\Builder\Block;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

/**
 * Pull the collection block straight out of the real page form, so the test
 * breaks if the block is renamed or its label closure is dropped.
 */
function collectionBlock(): Block
{
    $component = Livewire::test(CreatePage::class)->instance();

    foreach ($component->getSchema('form')->getFlatComponents() as $field) {
        if ($field instanceof BuilderField) {
            foreach ($field->getBlocks() as $block) {
                if ($block->getName() === 'collection') {
                    return $block;
                }
            }
        }
    }

    throw new RuntimeException('The collection block is missing from the page form.');
}

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

it('shows the plain name in the block picker', function () {
    // A null state means the picker, which must not show a stale source.
    expect((string) collectionBlock()->getLabel(null))->toBe('Collection');
});

it('shows the chosen source in the block header', function () {
    $block = collectionBlock();

    expect((string) $block->getLabel(['source' => CollectionComponentSource::PILLARS->value]))
        ->toBe('Collection: Pillars')
        ->and((string) $block->getLabel(['source' => CollectionComponentSource::KABUPATEN_MAP->value]))
        ->toBe('Collection: Kabupaten Map')
        ->and((string) $block->getLabel(['source' => CollectionComponentSource::PARTICIPATION_PATHWAYS->value]))
        ->toBe('Collection: Participation Pathways');
});

it('falls back to the plain name when no source is chosen yet', function () {
    $block = collectionBlock();

    expect((string) $block->getLabel([]))->toBe('Collection')
        ->and((string) $block->getLabel(['source' => null]))->toBe('Collection')
        ->and((string) $block->getLabel(['source' => '']))->toBe('Collection');
});

it('falls back to the plain name for a source that no longer exists', function () {
    // A block saved against a source since removed from the enum must not crash.
    expect((string) collectionBlock()->getLabel(['source' => 'retired_source']))
        ->toBe('Collection');
});
