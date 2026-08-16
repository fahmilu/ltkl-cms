<?php

namespace App\ModelFilters;

use App\Enums\PostType;
use EloquentFilter\ModelFilter;

class PostFilter extends ModelFilter
{
    /**
     * Allowed sortable columns
     */
    protected $allowedSortColumns = [
        'id',
        'title',
        'title_id',
        'published_at',
        'created_at',
        'updated_at',
        'is_featured',
        'is_active',
    ];

    /**
     * Setup default filters and sorting
     */
    public function setup()
    {
        // Always filter for active posts only
        $this->where('is_active', true);
        
        // Apply default sorting if sort is not in the input
        if (!isset($this->input['sort'])) {
            return $this->orderBy('published_at', 'desc');
        }
        
        return $this;
    }

    /**
     * Filter by featured status
     */
    public function featured($value)
    {
        if ($value === true || $value === 'true' || $value === '1' || $value === 1) {
            return $this->where('is_featured', true);
        } elseif ($value === false || $value === 'false' || $value === '0' || $value === 0) {
            return $this->where('is_featured', false);
        }
        
        return $this;
    }

    /**
     * Filter by active status
     * Note: This method is kept for backward compatibility but is effectively a no-op
     * since active posts are always filtered in setup()
     */
    public function active($value)
    {
        // Active filter is always applied in setup(), so this method does nothing
        return $this;
    }

    /**
     * Search by title
     */
    public function search($value)
    {
        if (!empty($value)) {
            return $this->where('title', 'LIKE', '%' . $value . '%');
        }
        
        return $this;
    }

    /**
     * Sort by column
     */
    public function sort($value)
    {
        if (empty($value)) {
            return $this;
        }

        // Validate column is allowed
        $column = in_array($value, $this->allowedSortColumns) ? $value : 'published_at';
        
        // Get order direction from input, default to 'desc'
        $order = $this->input['order'] ?? 'desc';
        $order = strtolower($order) === 'asc' ? 'asc' : 'desc';
        
        return $this->orderBy($column, $order);
    }

    /**
     * Order direction (asc/desc)
     * This works together with sort() - if sort is provided, order will be applied there
     */
    public function order($value)
    {
        // If sort is already provided, it will handle the order
        // This method exists to prevent errors if only order is provided without sort
        if (!isset($this->input['sort'])) {
            // If no sort column specified, use default
            $order = strtolower($value) === 'asc' ? 'asc' : 'desc';
            return $this->orderBy('published_at', $order);
        }
        
        return $this;
    }

    /**
     * Filter by post type (article, video, event, library, media_coverage)
     * Accepts single type or array of types
     */
    public function type($value)
    {
        if (empty($value)) {
            return $this;
        }

        $types = is_array($value) ? $value : [$value];

        // Keep only types the application actually knows about
        $types = array_values(array_intersect(
            array_map('strval', $types),
            array_column(PostType::cases(), 'value')
        ));

        if (empty($types)) {
            return $this;
        }

        return $this->whereIn('type', $types);
    }

    /**
     * Filter by post tags (collection IDs)
     * Accepts single ID or array of IDs
     */
    public function postTags($value)
    {
        if (empty($value)) {
            return $this;
        }

        // Convert single value to array
        $ids = is_array($value) ? $value : [$value];
        
        // Filter to integers only
        $ids = array_filter(array_map('intval', $ids));

        if (empty($ids)) {
            return $this;
        }

        return $this->whereHas('post_tags', function ($query) use ($ids) {
            $query->whereIn('collections.id', $ids);
        });
    }

    /**
     * Filter by post topics (collection IDs)
     * Accepts single ID or array of IDs
     */
    public function postTopics($value)
    {
        if (empty($value)) {
            return $this;
        }

        // Convert single value to array
        $ids = is_array($value) ? $value : [$value];
        
        // Filter to integers only
        $ids = array_filter(array_map('intval', $ids));

        if (empty($ids)) {
            return $this;
        }

        return $this->whereHas('post_topics', function ($query) use ($ids) {
            $query->whereIn('collections.id', $ids);
        });
    }

    /**
     * Filter by post kabupatens (kabupaten IDs)
     * Accepts single ID or array of IDs
     */
    public function postKabupatens($value)
    {
        if (empty($value)) {
            return $this;
        }

        // Convert single value to array
        $ids = is_array($value) ? $value : [$value];

        // Filter to integers only
        $ids = array_filter(array_map('intval', $ids));

        if (empty($ids)) {
            return $this;
        }

        return $this->whereHas('post_kabupatens', function ($query) use ($ids) {
            $query->whereIn('kabupatens.id', $ids);
        });
    }
}

