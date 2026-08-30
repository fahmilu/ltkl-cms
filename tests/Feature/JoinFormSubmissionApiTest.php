<?php

use App\Mail\JoinFormSubmissionReceived;
use App\Models\JoinFormSubmission;
use App\Models\ParticipationPathway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Inerba\DbConfig\DbConfig;

uses(RefreshDatabase::class);

function makeJoinPathway(array $attributes = []): ParticipationPathway
{
    return ParticipationPathway::create(array_merge([
        'is_active' => true,
        'title' => 'Jadi Donatur',
        'title_id' => 'Jadi Donatur',
        'slug' => 'become-a-donor',
        'slug_id' => 'jadi-donatur',
        'sorted_at' => 1,
    ], $attributes));
}

function joinFormPayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'Sri Wahyuni',
        'email' => 'sri@organisasi.org',
        'organization' => 'Yayasan Lestari',
        'region' => 'Siak, Riau',
        'message' => 'Tertarik mendukung sebagai donatur.',
    ], $overrides);
}

it('stores a submission and emails the address set in website settings', function () {
    Mail::fake();
    DbConfig::set('website.join_us_email', 'inbox@kabupatenlestari.org');

    $pathway = makeJoinPathway();

    $response = $this->postJson('/api/join-form-submissions', joinFormPayload([
        'participation_pathway_id' => $pathway->id,
    ]))->assertCreated();

    expect($response->json('data.participation_pathway.title'))->toBe('Jadi Donatur')
        ->and($response->json('data.region'))->toBe('Siak, Riau');

    $submission = JoinFormSubmission::sole();

    expect($submission->organization)->toBe('Yayasan Lestari')
        ->and($submission->participation_pathway_id)->toBe($pathway->id);

    Mail::assertSent(
        JoinFormSubmissionReceived::class,
        fn (JoinFormSubmissionReceived $mail) => $mail->hasTo('inbox@kabupatenlestari.org')
            && $mail->submission->is($submission)
    );
});

it('still stores the submission when no recipient is configured', function () {
    Mail::fake();

    $pathway = makeJoinPathway();

    $this->postJson('/api/join-form-submissions', joinFormPayload([
        'participation_pathway_id' => $pathway->id,
    ]))->assertCreated();

    expect(JoinFormSubmission::count())->toBe(1);

    Mail::assertNothingSent();
});

it('rejects a submission without the required fields', function () {
    $this->postJson('/api/join-form-submissions', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name', 'email', 'region', 'participation_pathway_id', 'message']);
});

it('rejects a pathway that is not active', function () {
    $pathway = makeJoinPathway(['is_active' => false]);

    $this->postJson('/api/join-form-submissions', joinFormPayload([
        'participation_pathway_id' => $pathway->id,
    ]))->assertUnprocessable()->assertJsonValidationErrors(['participation_pathway_id']);
});

it('renders the notification email with the submission details', function () {
    $pathway = makeJoinPathway();

    $submission = JoinFormSubmission::create(joinFormPayload([
        'participation_pathway_id' => $pathway->id,
    ]));

    $body = (new JoinFormSubmissionReceived($submission->load('participationPathway')))->render();

    expect($body)->toContain('Sri Wahyuni')
        ->toContain('Jadi Donatur')
        ->toContain('Siak, Riau')
        ->toContain('/administrator/join-form-submissions/' . $submission->id);
});
