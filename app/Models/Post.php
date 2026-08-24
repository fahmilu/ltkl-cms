<?php

namespace App\Models;

use App\Enums\CollectionType;
use App\Enums\PostType;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use HasFactory, SoftDeletes, Filterable;

    /**
     * @var string[]
     */
    protected $casts = [
        'type' => PostType::class,
        'type_data' => 'array',
        'components' => 'json',
        'components_id' => 'json',
        'published_at' => 'datetime',
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
        'type',
        'type_data',
        'image',
        'title',
        'title_id',
        'slug',
        'slug_id',
        'lead',
        'lead_id',
        'content',
        'components',
        'components_id',
        'meta_title',
        'meta_description',
        'meta_image',
        'is_featured',
        'is_external_url',
        'external_type',
        'external_url',
        'external_file',
        'published_at',
        'sorted_at',
    ];

    /**
     * @return BelongsToMany
     */
    public function post_tags(): BelongsToMany
    {
        return $this->belongsToMany(Collection::class, 'post_tags')->where('type', CollectionType::TAG->value)->withTimestamps();
    }

    /**
     * @return BelongsToMany
     */
    /* public function post_clients(): BelongsToMany
    {
        return $this->belongsToMany(Collection::class, 'post_clients')->where('type', CollectionType::CLIENT->value)->withTimestamps();
    } */

    /**
     * @return BelongsToMany
     */
    /* public function post_services(): BelongsToMany
    {
        return $this->belongsToMany(Collection::class, 'post_services')->where('type', CollectionType::SERVICE->value)->withTimestamps();
    } */

    /**
     * @return BelongsToMany
     */
    public function post_topics(): BelongsToMany
    {
        return $this->belongsToMany(Collection::class, 'post_topics')->where('type', CollectionType::TOPIC->value)->withTimestamps();
    }

    /**
     * @return BelongsToMany
     */
    public function post_kabupatens(): BelongsToMany
    {
        return $this->belongsToMany(Kabupaten::class, 'post_kabupatens')->withTimestamps();
    }
}
