<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JoinFormSubmission extends Model
{
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'join_form_submissions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'organization',
        'phone',
        'participation_pathway_id',
        'message',
    ];

    /**
     * The pathway the visitor picked on the join form.
     */
    public function participationPathway(): BelongsTo
    {
        return $this->belongsTo(ParticipationPathway::class);
    }
}
