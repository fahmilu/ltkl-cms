<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Page extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * @var string[]
     */
    protected $casts = [
        'components' => 'json',
        'components_id' => 'json',
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
        'is_default',
        'is_active',
        'template',
        'image',
        'title',
        'title_id',
        'slug',
        'slug_id',
        'content',
        'components',
        'components_id',
        'meta_is_hidden',
        'meta_title',
        'meta_title_id',
        'meta_description',
        'meta_description_id',
        'meta_image',
        'menu_parent_id',
        'menu_group',
        'menu_is_active',
        'menu_is_external',
        'menu_title',
        'menu_url',
        'menu_url_target',
        'sorted_at',
    ];

    /**
     * @return HasOne
     */
    public function page_parent(): HasOne
    {
        return $this->hasOne(Page::class, 'id', 'menu_parent_id');
    }

    /**
     * @return HasMany
     */
    public function subpages(): HasMany
    {
        return $this->hasMany(Page::class, 'menu_parent_id', 'id');
    }

}
