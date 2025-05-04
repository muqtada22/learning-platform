<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'lesson_id',
        'question_text',
        'question_type',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'question_type' => 'string',
    ];

    /**
     * Get the lesson that owns the question.
     */
    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    /**
     * Get the answer options for the question.
     */
    public function answerOptions(): HasMany
    {
        return $this->hasMany(AnswerOption::class);
    }

    /**
     * Get the user progress records for the question.
     */
    public function userProgress(): HasMany
    {
        return $this->hasMany(UserProgress::class);
    }

    /**
     * Get the correct answer options for the question.
     */
    public function correctAnswers()
    {
        return $this->answerOptions()->where('is_correct', true);
    }
}
