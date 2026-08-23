<?php

use App\Enums\CollectionComponentSource;
use App\Enums\EmploymentType;
use App\Enums\JobStatus;
use App\Filament\Resources\JobOpportunities\JobOpportunityResource;
use App\Filament\Resources\JobOpportunities\Pages\CreateJobOpportunity;
use App\Models\JobOpportunity;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function makeJob(array $overrides = []): JobOpportunity
{
    return JobOpportunity::create(array_merge([
        'is_active' => true,
        'status' => JobStatus::OPEN,
        'employment_type' => EmploymentType::CONSULTANT,
        'title' => 'Consultant - Sustainable Investment Development',
        'title_id' => 'Konsultan - Sustainable Investment Development',
        'slug' => 'consultant-sustainable-investment-development',
        'slug_id' => 'konsultant-sustainable-investment-development',
        'location' => 'Jakarta, Indonesia',
        'location_id' => 'Jakarta, Indonesia',
        'description' => '<p>LTKL is a district association.</p>',
        'description_id' => '<p>LTKL adalah asosiasi kabupaten.</p>',
        'how_to_apply' => '<p>Send your CV to recruitment@kabupatenlestari.org</p>',
        'how_to_apply_id' => '<p>Kirim CV ke recruitment@kabupatenlestari.org</p>',
        'contact_email' => 'recruitment@kabupatenlestari.org',
        'posted_at' => '2026-02-05',
        'deadline_at' => '2026-03-16',
        'sorted_at' => 1,
    ], $overrides));
}

it('lives under Masters as its own resource', function () {
    expect(JobOpportunityResource::getNavigationGroup())->toBe('Masters')
        ->and(JobOpportunityResource::getNavigationLabel())->toBe('Job Opportunities');

    $resources = Filament::getPanel('administrator')->getResources();

    expect($resources)->toContain(JobOpportunityResource::class);
});

it('saves a vacancy in both languages', function () {
    $this->actingAs(User::factory()->create());

    Livewire::test(CreateJobOpportunity::class)
        ->fillForm([
            'title_id' => 'Konsultan - Sustainable Investment Development',
            'title' => 'Consultant - Sustainable Investment Development',
            'slug_id' => 'konsultant-sustainable-investment-development',
            'slug' => 'consultant-sustainable-investment-development',
            'location_id' => 'Jakarta, Indonesia',
            'location' => 'Jakarta, Indonesia',
            'employment_type' => EmploymentType::CONSULTANT->value,
            'status' => JobStatus::OPEN->value,
            'contact_email' => 'recruitment@kabupatenlestari.org',
            'posted_at' => '2026-02-05',
            'deadline_at' => '2026-03-16',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $job = JobOpportunity::firstOrFail();

    expect($job->title_id)->toBe('Konsultan - Sustainable Investment Development')
        ->and($job->status)->toBe(JobStatus::OPEN)
        ->and($job->employment_type)->toBe(EmploymentType::CONSULTANT)
        ->and($job->deadline_at->toDateString())->toBe('2026-03-16');
});

it('defaults a vacancy with no status to open', function () {
    expect(JobStatus::fromState(null))->toBe(JobStatus::OPEN)
        ->and(JobStatus::fromState('nonsense'))->toBe(JobStatus::OPEN)
        ->and(JobStatus::fromState('closed'))->toBe(JobStatus::CLOSED);
});

it('lists published vacancies with their status', function () {
    makeJob();
    makeJob([
        'title' => 'Knowledge Management Consultant',
        'slug' => 'knowledge-management-consultant',
        'slug_id' => 'konsultan-manajemen-pengetahuan',
        'status' => JobStatus::CLOSED,
        'posted_at' => '2026-01-02',
        'sorted_at' => 2,
    ]);
    makeJob([
        'is_active' => false,
        'title' => 'Draft role',
        'slug' => 'draft-role',
        'slug_id' => 'peran-draf',
        'sorted_at' => 3,
    ]);

    $data = $this->getJson('/api/job-opportunities')->assertOk()->json('data');

    expect($data)->toHaveCount(2)
        ->and($data[0]['slug'])->toBe('consultant-sustainable-investment-development')
        ->and($data[0]['status'])->toBe('open')
        ->and($data[0]['is_open'])->toBeTrue()
        ->and($data[0]['employment_type'])->toBe('consultant')
        ->and($data[0]['deadline_at'])->toBe('2026-03-16')
        ->and($data[1]['status'])->toBe('closed')
        ->and($data[1]['is_open'])->toBeFalse();
});

it('filters the listing by status', function () {
    makeJob();
    makeJob([
        'title' => 'Knowledge Management Consultant',
        'slug' => 'knowledge-management-consultant',
        'slug_id' => 'konsultan-manajemen-pengetahuan',
        'status' => JobStatus::CLOSED,
        'sorted_at' => 2,
    ]);

    $open = $this->getJson('/api/job-opportunities?status=open')->assertOk()->json('data');
    $closed = $this->getJson('/api/job-opportunities?status=closed')->assertOk()->json('data');
    $all = $this->getJson('/api/job-opportunities?status=nonsense')->assertOk()->json('data');

    expect($open)->toHaveCount(1)
        ->and($open[0]['status'])->toBe('open')
        ->and($closed)->toHaveCount(1)
        ->and($closed[0]['status'])->toBe('closed')
        ->and($all)->toHaveCount(2);
});

it('serves a vacancy on either language slug', function () {
    makeJob();

    foreach ([
        'consultant-sustainable-investment-development',
        'konsultant-sustainable-investment-development',
    ] as $slug) {
        $data = $this->getJson('/api/job-opportunity/' . $slug)->assertOk()->json('data');

        expect($data['title_id'])->toBe('Konsultan - Sustainable Investment Development')
            ->and($data['how_to_apply_id'])->toContain('recruitment@kabupatenlestari.org')
            ->and($data['attachment'])->toBeNull();
    }
});

it('still serves a closed vacancy', function () {
    makeJob(['status' => JobStatus::CLOSED]);

    $data = $this->getJson('/api/job-opportunity/consultant-sustainable-investment-development')
        ->assertOk()
        ->json('data');

    expect($data['status'])->toBe('closed')
        ->and($data['is_open'])->toBeFalse();
});

it('hides an unpublished vacancy', function () {
    makeJob(['is_active' => false]);

    $this->getJson('/api/job-opportunity/consultant-sustainable-investment-development')
        ->assertNotFound();
});

it('publishes the attachment as a url', function () {
    makeJob(['attachment' => 'job_opportunities/tor.pdf']);

    $data = $this->getJson('/api/job-opportunity/consultant-sustainable-investment-development')
        ->assertOk()
        ->json('data');

    expect($data['attachment'])->toContain('/storage/job_opportunities/tor.pdf');
});

it('is offered as a collection page block source', function () {
    expect(CollectionComponentSource::JOB_OPPORTUNITIES->getLabel())->toBe('Job Opportunities')
        ->and(CollectionComponentSource::JOB_OPPORTUNITIES->getEndpoint())->toBe('/api/job-opportunities');
});
