<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\Answer;
use App\Models\Quiz;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    public function store(Request $request, Quiz $quiz)
    {
        $request->validate([
            'question'           => 'required|string',
            'type'               => 'required|in:multiple_choice,true_false,matching,essay',
            'points'             => 'required|integer|min:1',
            'answers'            => 'required_if:type,multiple_choice,true_false,matching|array',
            'answers.*'          => 'required_if:type,multiple_choice,true_false,matching|string|max:255',
            'correct_answer'     => 'required_if:type,multiple_choice,true_false|integer',
            'matching_pairs'     => 'required_if:type,matching|array',
            'matching_pairs.*'   => 'required_if:type,matching|string|max:255',
        ], [
            'question.required'       => 'نص السؤال مطلوب.',
            'answers.required_if'     => 'يجب إضافة الإجابات المطلوبة لهذا النوع.',
            'points.required'         => 'درجة السؤال مطلوبة.',
        ]);

        $question = Question::create([
            'quiz_id'  => $quiz->id,
            'question' => $request->question,
            'type'     => $request->type,
            'points'   => $request->points,
        ]);

        if ($request->type === 'multiple_choice' || $request->type === 'true_false') {
            foreach ($request->answers as $index => $answerText) {
                Answer::create([
                    'question_id' => $question->id,
                    'answer'      => $answerText,
                    'is_correct'  => ($index == $request->correct_answer),
                ]);
            }
        } elseif ($request->type === 'matching') {
            // matching_pairs will be Term list, answers will be Definition list
            foreach ($request->matching_pairs as $index => $term) {
                $definition = $request->answers[$index] ?? '';
                Answer::create([
                    'question_id' => $question->id,
                    'answer'      => $term . '|||' . $definition,
                    'is_correct'  => true,
                ]);
            }
        } elseif ($request->type === 'essay') {
            // Optional model answer can be stored if provided
            if ($request->filled('model_answer')) {
                Answer::create([
                    'question_id' => $question->id,
                    'answer'      => $request->model_answer,
                    'is_correct'  => true,
                ]);
            }
        }

        $prefix = auth()->user()->type === 'admin' ? 'admin' : 'teacher';
        return redirect()->route($prefix . '.quizzes.show', $quiz)->with('success', 'تم إضافة السؤال بنجاح.');
    }

    public function destroy(Question $question)
    {
        $quizId = $question->quiz_id;
        $question->delete();
        $prefix = auth()->user()->type === 'admin' ? 'admin' : 'teacher';
        return redirect()->route($prefix . '.quizzes.show', $quizId)->with('success', 'تم حذف السؤال بنجاح.');
    }
}
