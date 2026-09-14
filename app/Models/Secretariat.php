<?php

namespace App\Models;

use App\Enums\SecretariatLevel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Secretariat extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * @var string[]
     */
    protected $casts = [
        'is_active' => 'boolean',
        'level' => SecretariatLevel::class,
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
        'name',
        'role',
        'role_id',
        'level',
        'sorted_at',
    ];
}
