<?php

namespace Database\Seeders;

use App\Models\Level;
use App\Models\Quiz;
use App\Models\Question;
use App\Models\Answer;
use Illuminate\Database\Seeder;

class PlacementTestSeeder extends Seeder
{
    public function run()
    {
        // 1. Create Levels
        $beginner = Level::updateOrCreate(['name' => 'Beginner'], ['is_global' => true, 'required_xp' => 100]);
        $elementary = Level::updateOrCreate(['name' => 'Elementary'], ['is_global' => true, 'required_xp' => 500]);
        $advanced = Level::updateOrCreate(['name' => 'Advanced'], ['is_global' => true, 'required_xp' => 1000]);

        // 2. Create the Placement Quiz
        $quiz = Quiz::updateOrCreate(
            ['quiz_type' => 'placement'],
            [
                'title' => 'English Placement Test',
                'source_type' => 'admin',
                'is_global' => true,
            ]
        );

        // 3. Create Questions & Answers
        $questions = [
            [
                'text' => 'Which of the following is a greeting?',
                'answers' => [
                    ['text' => 'Goodbye', 'is_correct' => false],
                    ['text' => 'Hello', 'is_correct' => true],
                    ['text' => 'Apple', 'is_correct' => false],
                    ['text' => 'Blue', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Translate to English: "أنا أحب تعلم اللغات"',
                'answers' => [
                    ['text' => 'I like learning languages', 'is_correct' => true],
                    ['text' => 'I hate learning languages', 'is_correct' => false],
                    ['text' => 'I am a language', 'is_correct' => false],
                    ['text' => 'Learning is a language', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Choose the correct sentence:',
                'answers' => [
                    ['text' => 'She is an teacher', 'is_correct' => false],
                    ['text' => 'She be a teacher', 'is_correct' => false],
                    ['text' => 'She is a teacher', 'is_correct' => true],
                    ['text' => 'She am a teacher', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'What is the past tense of "Go"?',
                'answers' => [
                    ['text' => 'Goes', 'is_correct' => false],
                    ['text' => 'Went', 'is_correct' => true],
                    ['text' => 'Gone', 'is_correct' => false],
                    ['text' => 'Going', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Complete the sentence: "I ____ to the gym every morning."',
                'answers' => [
                    ['text' => 'goes', 'is_correct' => false],
                    ['text' => 'go', 'is_correct' => true],
                    ['text' => 'going', 'is_correct' => false],
                    ['text' => 'wented', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Which word is an adjective?',
                'answers' => [
                    ['text' => 'Run', 'is_correct' => false],
                    ['text' => 'Quickly', 'is_correct' => false],
                    ['text' => 'Beautiful', 'is_correct' => true],
                    ['text' => 'House', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Identify the incorrect sentence:',
                'answers' => [
                    ['text' => 'They they are happy', 'is_correct' => true],
                    ['text' => 'They are happy', 'is_correct' => false],
                    ['text' => 'He is happy', 'is_correct' => false],
                    ['text' => 'I am happy', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'What is the opposite of "Hot"?',
                'answers' => [
                    ['text' => 'Sun', 'is_correct' => false],
                    ['text' => 'Cold', 'is_correct' => true],
                    ['text' => 'Warm', 'is_correct' => false],
                    ['text' => 'Ice', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Choose the correct plural form:',
                'answers' => [
                    ['text' => 'Childs', 'is_correct' => false],
                    ['text' => 'Childrens', 'is_correct' => false],
                    ['text' => 'Children', 'is_correct' => true],
                    ['text' => 'Childes', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Complete the phrase: "An apple a day keeps the ____ away."',
                'answers' => [
                    ['text' => 'Teacher', 'is_correct' => false],
                    ['text' => 'Doctor', 'is_correct' => true],
                    ['text' => 'Patient', 'is_correct' => false],
                    ['text' => 'Nurse', 'is_correct' => false],
                ],
            ],
        ];

        foreach ($questions as $qData) {
            $question = Question::create([
                'quiz_id' => $quiz->id,
                'question' => $qData['text'],
            ]);

            foreach ($qData['answers'] as $aData) {
                Answer::create([
                    'question_id' => $question->id,
                    'answer' => $aData['text'],
                    'is_correct' => $aData['is_correct'],
                ]);
            }
        }
    }
}
