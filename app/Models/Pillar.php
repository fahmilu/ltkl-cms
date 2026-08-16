<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pillar extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * @var string
     */
    protected $table = 'pillars';

    /**
     * @var string[]
     */
    protected $casts = [
        'is_active' => 'boolean',
        'statistics' => 'array',
        'statistics_id' => 'array',
        'results' => 'array',
        'results_id' => 'array',
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
        'image',
        'title',
        'title_id',
        'slug',
        'slug_id',
        'technical_term',
        'technical_term_id',
        'description',
        'description_id',
        'statistics',
        'statistics_id',
        'results',
        'results_id',
        'sorted_at',
    ];

    /**
     * @return HasMany
     */
    public function practices(): HasMany
    {
        return $this->hasMany(PillarPractice::class)->orderBy('sorted_at');
    }

    /**
     * @return BelongsToMany
     */
    public function kabupatens(): BelongsToMany
    {
        return $this->belongsToMany(Kabupaten::class, 'kabupaten_pillars')
            ->withTimestamps()
            ->orderBy('kabupatens.sorted_at');
    }
}
