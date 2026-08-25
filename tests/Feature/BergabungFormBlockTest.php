<?php

use App\Filament\Resources\Pages\Pages\CreatePage;
use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function makePageWithBergabungBlock(array $data = []): Page
{
    $block = [
        'type' => 'bergabung_form',
        'data' => array_merge([
            'label' => 'Bergabung',
            'title' => 'Mari bekerja bersama',
            'description' => '<h2>Kirim pesan</h2><p>Kami balas dalam tiga hari kerja.</p>',
            'contact_info' => '<h3>Sekretariat</h3><p>halo@kabupatenlestari.org</p>',
            'job_opportunity' => [
                'label' => 'Karir',
                'title' => 'Lowongan terbuka',
                'description' => '<h2>Bergabung dengan tim</h2>',
                'button_text' => 'Lihat lowongan',
                'button_url' => 'https://kabupatenlestari.org/karir',
            ],
        ], $data),
    ];

    return Page::create([
        'is_active' => true,
        'title' => 'Join',
        'title_id' => 'Bergabung',
        'slug' => 'join',
        'slug_id' => 'bergabung',
        'components' => [$block],
        'components_id' => [$block],
    ]);
}

it('publishes the bergabung form block with its job opportunity section', function () {
    makePageWithBergabungBlock();

    $block = $this->getJson('/api/page/join')->assertOk()->json('data.components.0');

    expect($block['type'])->toBe('bergabung_form')
        ->and($block['data']['label'])->toBe('Bergabung')
        ->and($block['data']['title'])->toBe('Mari bekerja bersama')
        // Headings step down one level, as everywhere else in the builder.
        ->and($block['data']['description'])->toBe('<h3>Kirim pesan</h3><p>Kami balas dalam tiga hari kerja.</p>')
        ->and($block['data']['contact_info'])->toBe('<h4>Sekretariat</h4><p>halo@kabupatenlestari.org</p>')
        ->and($block['data']['job_opportunity'])->toBe([
            'label' => 'Karir',
            'title' => 'Lowongan terbuka',
            'description' => '<h3>Bergabung dengan tim</h3>',
            'button_text' => 'Lihat lowongan',
            'button_url' => 'https://kabupatenlestari.org/karir',
        ]);
});

it('keeps the job opportunity keys on a block that carries none of them', function () {
    makePageWithBergabungBlock(['job_opportunity' => null, 'contact_info' => null]);

    $block = $this->getJson('/api/page/join')->assertOk()->json('data.components.0');

    expect($block['data']['contact_info'])->toBeNull()
        ->and($block['data']['job_opportunity'])->toBe([
            'label' => null,
            'title' => null,
            'description' => null,
            'button_text' => null,
            'button_url' => null,
        ]);
});

it('saves the block from the page builder, job section nested', function () {
    $this->actingAs(User::factory()->create());

    Livewire::test(CreatePage::class)
        ->fillForm([
            'title' => 'Join',
            'title_id' => 'Bergabung',
            'slug' => 'join',
            'slug_id' => 'bergabung',
            'components' => [
                'block1' => [
                    'type' => 'bergabung_form',
                    'data' => [
                        'label' => 'Bergabung',
                        'title' => 'Mari bekerja bersama',
                        'contact_info' => '<p>halo@kabupatenlestari.org</p>',
                        'job_opportunity' => [
                            'label' => 'Karir',
                            'button_text' => 'Lihat lowongan',
                            'button_url' => 'https://kabupatenlestari.org/karir',
                        ],
                    ],
                ],
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $block = Page::firstOrFail()->components[0];

    expect($block['type'])->toBe('bergabung_form')
        ->and($block['data']['label'])->toBe('Bergabung')
        ->and($block['data']['job_opportunity']['label'])->toBe('Karir')
        ->and($block['data']['job_opportunity']['button_url'])->toBe('https://kabupatenlestari.org/karir');
});

it('anchors the block when it is flagged as a submenu entry', function () {
    makePageWithBergabungBlock(['add_as_submenu' => true]);

    $block = $this->getJson('/api/page/join')->assertOk()->json('data.components.0');

    expect($block['data']['anchor'])->toBe('mari-bekerja-bersama');
});
