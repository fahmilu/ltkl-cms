<?php

namespace App\Models;

use App\Enums\EmploymentType;
use App\Enums\JobStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JobOpportunity extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * @var string
     */
    protected $table = 'job_opportunities';

    /**
     * @var string[]
     */
    protected $casts = [
        'is_active' => 'boolean',
        'status' => JobStatus::class,
        'employment_type' => EmploymentType::class,
        'posted_at' => 'date',
        'deadline_at' => 'date',
        'deleted_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'is_active',
        'status',
        'employment_type',
        'title',
        'title_id',
        'slug',
        'slug_id',
        'location',
        'location_id',
        'description',
        'description_id',
        'how_to_apply',
        'how_to_apply_id',
        'contact_email',
        'apply_url',
        'attachment',
        'posted_at',
        'deadline_at',
        'sorted_at',
    ];

    /**
     * Whether the vacancy still takes applications.
     */
    public function isOpen(): bool
    {
        return JobStatus::fromState($this->status) === JobStatus::OPEN;
    }
}
