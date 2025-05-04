<?php

namespace App\Http\Controllers;

use App\Models\AnswerOption;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Question;
use App\Models\UserProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuestionController extends Controller
{
    /**
     * Display a listing of the questions for a lesson.
     */
    public function index(Course $course, Lesson $lesson)
    {
        // Ensure the lesson belongs to the course
        if ($lesson->course_id !== $course->id) {
            return redirect()->route('courses.show', $course)
                ->with('error', 'Lesson not found in this course.');
        }
        
        $questions = $lesson->questions;
        
        return view('questions.index', compact('course', 'lesson', 'questions'));
    }

    /**
     * Show the form for creating a new question.
     */
    public function create(Course $course, Lesson $lesson)
    {
        $user = Auth::user();
        
        // Ensure the lesson belongs to the course
        if ($lesson->course_id !== $course->id) {
            return redirect()->route('courses.show', $course)
                ->with('error', 'Lesson not found in this course.');
        }
        
        // Check if the authenticated user is the professor of the course
        if ($user->role !== 'professor' || $course->professor_id !== $user->id) {
            return redirect()->route('courses.lessons.show', [$course, $lesson])
                ->with('error', 'Only the course professor can add questions.');
        }
        
        return view('questions.create', compact('course', 'lesson'));
    }

    /**
     * Store a newly created question in storage.
     */
    public function store(Request $request, Course $course, Lesson $lesson)
    {
        $user = Auth::user();
        
        // Ensure the lesson belongs to the course
        if ($lesson->course_id !== $course->id) {
            return redirect()->route('courses.show', $course)
                ->with('error', 'Lesson not found in this course.');
        }
        
        // Check if the authenticated user is the professor of the course
        if ($user->role !== 'professor' || $course->professor_id !== $user->id) {
            return redirect()->route('courses.lessons.show', [$course, $lesson])
                ->with('error', 'Only the course professor can add questions.');
        }
        
        $validated = $request->validate([
            'question_text' => 'required|string',
            'question_type' => 'required|in:multiple_choice,true_false,fill_in_blank,matching',
            'options' => 'required|array|min:2',
            'options.*' => 'required|string',
            'correct_options' => 'required|array|min:1',
            'correct_options.*' => 'required|integer',
        ]);
        
        $question = $lesson->questions()->create([
            'question_text' => $validated['question_text'],
            'question_type' => $validated['question_type'],
        ]);
        
        // Create answer options
        foreach ($validated['options'] as $index => $optionText) {
            $isCorrect = in_array($index, $validated['correct_options']);
            
            $question->answerOptions()->create([
                'option_text' => $optionText,
                'is_correct' => $isCorrect,
            ]);
        }
        
        return redirect()->route('courses.lessons.questions.show', [$course, $lesson, $question])
            ->with('success', 'Question created successfully.');
    }

    /**
     * Display the specified question.
     */
    public function show(Course $course, Lesson $lesson, Question $question)
    {
        // Ensure the lesson belongs to the course
        if ($lesson->course_id !== $course->id) {
            return redirect()->route('courses.show', $course)
                ->with('error', 'Lesson not found in this course.');
        }
        
        // Ensure the question belongs to the lesson
        if ($question->lesson_id !== $lesson->id) {
            return redirect()->route('courses.lessons.show', [$course, $lesson])
                ->with('error', 'Question not found in this lesson.');
        }
        
        $answerOptions = $question->answerOptions;
        
        return view('questions.show', compact('course', 'lesson', 'question', 'answerOptions'));
    }

    /**
     * Show the form for editing the specified question.
     */
    public function edit(Course $course, Lesson $lesson, Question $question)
    {
        $user = Auth::user();
        
        // Ensure the lesson belongs to the course
        if ($lesson->course_id !== $course->id) {
            return redirect()->route('courses.show', $course)
                ->with('error', 'Lesson not found in this course.');
        }
        
        // Ensure the question belongs to the lesson
        if ($question->lesson_id !== $lesson->id) {
            return redirect()->route('courses.lessons.show', [$course, $lesson])
                ->with('error', 'Question not found in this lesson.');
        }
        
        // Check if the authenticated user is the professor of the course
        if ($user->role !== 'professor' || $course->professor_id !== $user->id) {
            return redirect()->route('courses.lessons.questions.show', [$course, $lesson, $question])
                ->with('error', 'Only the course professor can edit questions.');
        }
        
        $answerOptions = $question->answerOptions;
        
        return view('questions.edit', compact('course', 'lesson', 'question', 'answerOptions'));
    }

    /**
     * Update the specified question in storage.
     */
    public function update(Request $request, Course $course, Lesson $lesson, Question $question)
    {
        $user = Auth::user();
        
        // Ensure the lesson belongs to the course
        if ($lesson->course_id !== $course->id) {
            return redirect()->route('courses.show', $course)
                ->with('error', 'Lesson not found in this course.');
        }
        
        // Ensure the question belongs to the lesson
        if ($question->lesson_id !== $lesson->id) {
            return redirect()->route('courses.lessons.show', [$course, $lesson])
                ->with('error', 'Question not found in this lesson.');
        }
        
        // Check if the authenticated user is the professor of the course
        if ($user->role !== 'professor' || $course->professor_id !== $user->id) {
            return redirect()->route('courses.lessons.questions.show', [$course, $lesson, $question])
                ->with('error', 'Only the course professor can edit questions.');
        }
        
        $validated = $request->validate([
            'question_text' => 'required|string',
            'question_type' => 'required|in:multiple_choice,true_false,fill_in_blank,matching',
            'options' => 'required|array|min:2',
            'options.*' => 'required|string',
            'option_ids' => 'nullable|array',
            'option_ids.*' => 'nullable|integer|exists:answer_options,id',
            'correct_options' => 'required|array|min:1',
            'correct_options.*' => 'required|integer',
        ]);
        
        $question->update([
            'question_text' => $validated['question_text'],
            'question_type' => $validated['question_type'],
        ]);
        
        // Delete existing options that are not in the update
        if (isset($validated['option_ids'])) {
            $question->answerOptions()->whereNotIn('id', array_filter($validated['option_ids']))->delete();
        } else {
            $question->answerOptions()->delete();
        }
        
        // Update or create options
        foreach ($validated['options'] as $index => $optionText) {
            $isCorrect = in_array($index, $validated['correct_options']);
            $optionId = isset($validated['option_ids'][$index]) ? $validated['option_ids'][$index] : null;
            
            if ($optionId) {
                // Update existing option
                AnswerOption::where('id', $optionId)->update([
                    'option_text' => $optionText,
                    'is_correct' => $isCorrect,
                ]);
            } else {
                // Create new option
                $question->answerOptions()->create([
                    'option_text' => $optionText,
                    'is_correct' => $isCorrect,
                ]);
            }
        }
        
        return redirect()->route('courses.lessons.questions.show', [$course, $lesson, $question])
            ->with('success', 'Question updated successfully.');
    }

    /**
     * Remove the specified question from storage.
     */
    public function destroy(Course $course, Lesson $lesson, Question $question)
    {
        $user = Auth::user();
        
        // Ensure the lesson belongs to the course
        if ($lesson->course_id !== $course->id) {
            return redirect()->route('courses.show', $course)
                ->with('error', 'Lesson not found in this course.');
        }
        
        // Ensure the question belongs to the lesson
        if ($question->lesson_id !== $lesson->id) {
            return redirect()->route('courses.lessons.show', [$course, $lesson])
                ->with('error', 'Question not found in this lesson.');
        }
        
        // Check if the authenticated user is the professor of the course
        if ($user->role !== 'professor' || $course->professor_id !== $user->id) {
            return redirect()->route('courses.lessons.questions.show', [$course, $lesson, $question])
                ->with('error', 'Only the course professor can delete questions.');
        }
        
        $question->delete();
        
        return redirect()->route('courses.lessons.show', [$course, $lesson])
            ->with('success', 'Question deleted successfully.');
    }
    
    /**
     * Answer a question and record progress.
     */
    public function answer(Request $request, Course $course, Lesson $lesson, Question $question)
    {
        $user = Auth::user();
        
        // Only students can answer questions
        if ($user->role !== 'student') {
            return redirect()->route('courses.lessons.questions.show', [$course, $lesson, $question])
                ->with('error', 'Only students can answer questions.');
        }
        
        $validated = $request->validate([
            'selected_option_id' => 'required|exists:answer_options,id',
        ]);
        
        $selectedOption = AnswerOption::findOrFail($validated['selected_option_id']);
        
        // Ensure the selected option belongs to the question
        if ($selectedOption->question_id !== $question->id) {
            return redirect()->route('courses.lessons.questions.show', [$course, $lesson, $question])
                ->with('error', 'Invalid answer option selected.');
        }
        
        // Record the user's answer
        $userProgress = UserProgress::create([
            'user_id' => $user->id,
            'question_id' => $question->id,
            'selected_option_id' => $selectedOption->id,
            'is_correct' => $selectedOption->is_correct,
            'answered_at' => now(),
        ]);
        
        // Award XP points if the answer is correct
        if ($selectedOption->is_correct) {
            // Update XP points
            $user = Auth::user();
            $user->xp_points += 10;
            $user->save();
            
            return redirect()->route('courses.lessons.questions.show', [$course, $lesson, $question])
                ->with('success', 'Correct answer! You earned 10 XP points.');
        }
        
        return redirect()->route('courses.lessons.questions.show', [$course, $lesson, $question])
            ->with('error', 'Incorrect answer. Try again!');
    }
}
