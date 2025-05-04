<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserCourse extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'course_id',
        'is_favorite',
        'added_at',
        'deleted_at',
        'last_viewed_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_favorite' => 'boolean',
        'added_at' => 'datetime',
        'deleted_at' => 'datetime',
        'last_viewed_at' => 'datetime',
    ];

    /**
     * Get the user that the course is assigned to.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the course that is assigned to the user.
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }
}
