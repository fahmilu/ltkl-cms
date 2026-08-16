<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PillarPractice extends Model
{
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'pillar_practices';

    /**
     * @var string[]
     */
    protected $casts = [
        'since_year' => 'integer',
        'sorted_at' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'pillar_id',
        'kabupaten_id',
        'since_year',
        'image',
        'title',
        'title_id',
        'description',
        'description_id',
        'sorted_at',
    ];

    /**
     * @return BelongsTo
     */
    public function pillar(): BelongsTo
    {
        return $this->belongsTo(Pillar::class);
    }

    /**
     * @return BelongsTo
     */
    public function kabupaten(): BelongsTo
    {
        return $this->belongsTo(Kabupaten::class);
    }
}
