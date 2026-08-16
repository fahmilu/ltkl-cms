<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Collection extends Model
{
    use HasFactory, SoftDeletes;
    
    /**
     * @var string[]
     */
    protected $casts = [
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
        'type',
        'is_active',
        'image',
        'title',
        'title_id',
        'slug',
        'slug_id',
        'content',
        'content_id',
        'short_description',
        'short_description_id',
        'sorted_at',
    ];

    /**
     * @return BelongsToMany
     */
    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'post_tags')->withTimestamps();
    }

    /**
     * @return BelongsToMany
     */
    public function clients(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'post_clients')->withTimestamps();
    }

    /**
     * @return BelongsToMany
     */
    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'post_services')->withTimestamps();
    }

    /**
     * @return BelongsToMany
     */
    /* public function topics(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'post_topics')->withTimestamps();
    } */
}
