<?php

namespace Database\Seeders;

use App\Models\Level;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\Question;
use App\Models\Answer;
use Illuminate\Database\Seeder;

class LessonsSeeder extends Seeder
{
    public function run()
    {
        $beginner = Level::where('name', 'Beginner')->first();
        if (!$beginner) return;

        // Lesson 1: Basic Greetings
        $lesson1 = Lesson::create([
            'title' => 'Basic Greetings',
            'content' => 'In this lesson, you will learn how to greet people in English. Common greetings include Hello, Hi, and Hey. To be more formal, you can use Good Morning, Good Afternoon, or Good Evening.',
            'video_url' => 'https://www.youtube.com/watch?v=gVIFEVLzP4o',
            'source_type' => 'admin',
            'is_global' => true,
            'level_id' => $beginner->id,
            'order' => 1,
        ]);

        $quiz1 = Quiz::create([
            'title' => 'Greetings Quiz',
            'source_type' => 'admin',
            'is_global' => true,
            'level_id' => $beginner->id,
            'lesson_id' => $lesson1->id,
            'quiz_type' => 'lesson',
        ]);

        $q1 = Question::create(['quiz_id' => $quiz1->id, 'question' => 'How do you say hello in a formal way in the morning?']);
        Answer::create(['question_id' => $q1->id, 'answer' => 'Good Morning', 'is_correct' => true]);
        Answer::create(['question_id' => $q1->id, 'answer' => 'Hi', 'is_correct' => false]);

        // Lesson 2: Personal Pronouns
        $lesson2 = Lesson::create([
            'title' => 'Personal Pronouns',
            'content' => 'Learn how to refer to yourself and others: I, You, He, She, It, We, They.',
            'video_url' => 'https://www.youtube.com/watch?v=XunM7yR06S8',
            'source_type' => 'admin',
            'is_global' => true,
            'level_id' => $beginner->id,
            'order' => 2,
        ]);

        $quiz2 = Quiz::create([
            'title' => 'Pronouns Quiz',
            'source_type' => 'admin',
            'is_global' => true,
            'level_id' => $beginner->id,
            'lesson_id' => $lesson2->id,
            'quiz_type' => 'lesson',
        ]);

        $q2 = Question::create(['quiz_id' => $quiz2->id, 'question' => 'Which pronoun is used for a group of people including yourself?']);
        Answer::create(['question_id' => $q2->id, 'answer' => 'We', 'is_correct' => true]);
        Answer::create(['question_id' => $q2->id, 'answer' => 'They', 'is_correct' => false]);
    }
}
