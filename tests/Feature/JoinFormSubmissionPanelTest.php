<?php

use App\Filament\Resources\JoinFormSubmissions\Pages\ListJoinFormSubmissions;
use App\Filament\Resources\JoinFormSubmissions\Pages\ViewJoinFormSubmission;
use App\Models\JoinFormSubmission;
use App\Models\ParticipationPathway;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(User::factory()->create());

    $this->pathway = ParticipationPathway::create([
        'is_active' => true,
        'title' => 'Jadi Donatur',
        'title_id' => 'Jadi Donatur',
        'slug' => 'become-a-donor',
        'slug_id' => 'jadi-donatur',
        'sorted_at' => 1,
    ]);

    $this->submission = JoinFormSubmission::create([
        'name' => 'Sri Wahyuni',
        'email' => 'sri@organisasi.org',
        'organization' => 'Yayasan Lestari',
        'region' => 'Siak, Riau',
        'participation_pathway_id' => $this->pathway->id,
        'message' => 'Tertarik mendukung sebagai donatur.',
    ]);
});

it('lists submissions with their pathway', function () {
    Livewire::test(ListJoinFormSubmissions::class)
        ->assertCanSeeTableRecords([$this->submission])
        ->assertCanRenderTableColumn('participationPathway.title');
});

it('shows a submission with every field from the join form', function () {
    Livewire::test(ViewJoinFormSubmission::class, ['record' => $this->submission->getKey()])
        ->assertSee('Sri Wahyuni')
        ->assertSee('Yayasan Lestari')
        ->assertSee('Siak, Riau')
        ->assertSee('Jadi Donatur')
        ->assertSee('Tertarik mendukung sebagai donatur.');
});
