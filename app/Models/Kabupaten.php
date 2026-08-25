<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Kabupaten extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * @var string
     */
    protected $table = 'kabupatens';

    /**
     * @var string[]
     */
    protected $casts = [
        'is_active' => 'boolean',
        'is_founding_member' => 'boolean',
        'joined_year' => 'integer',
        'forest_cover_ha' => 'decimal:2',
        'protected_area_ha' => 'decimal:2',
        'social_forestry_tora_ha' => 'decimal:2',
        'area_km2' => 'decimal:2',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'commodities' => 'array',
        'commodities_id' => 'array',
        'achievements' => 'array',
        'achievements_id' => 'array',
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
        'role',
        'role_id',
        'content',
        'content_id',
        'forest_cover_ha',
        'protected_area_ha',
        'social_forestry_tora_ha',
        'area_km2',
        'city',
        'province',
        'latitude',
        'longitude',
        'is_founding_member',
        'joined_year',
        'commodities',
        'commodities_id',
        'achievements',
        'achievements_id',
        'sorted_at',
    ];

    /**
     * @return BelongsToMany
     */
    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'post_kabupatens')->withTimestamps();
    }

    /**
     * "Pilar Terkait" on the kabupaten page.
     *
     * @return BelongsToMany
     */
    public function pillars(): BelongsToMany
    {
        return $this->belongsToMany(Pillar::class, 'kabupaten_pillars')
            ->withTimestamps()
            ->orderBy('pillars.sorted_at');
    }
}
