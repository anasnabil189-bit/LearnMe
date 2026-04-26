<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIService
{
    protected $apiKey;
    protected $apiUrl;

    public function __construct()
    {
        $this->apiKey = env('AI_API_KEY');
        $this->apiUrl = env('AI_API_URL', 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent');
    }

    protected function canUseAI($type = 'ai_general')
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        if (!$user || $user->type !== 'user') return true;
        if ($user->isPro()) return true;

        $today = now()->toDateString();
        
        if ($type === 'ai_feedback') {
            $count = \App\Models\DailyUserUsage::where('user_id', $user->id)
                ->where('usage_type', 'ai_feedback')
                ->where('usage_date', $today)
                ->count();
            return $count < 3; // Free users get 3 feedback requests
        }

        // For general AI features
        $usage = \App\Models\AiUsage::firstOrCreate(
            ['user_id' => $user->id, 'usage_date' => $today],
            ['requests_count' => 0]
        );

        $limit = $user->isFree() ? 1 : 5; // Basic is 5
        return $usage->requests_count < $limit;
    }

    protected function consumeAIQuota($type = 'ai_general')
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        if (!$user || $user->type !== 'user' || $user->isPro()) return;

        $today = now()->toDateString();
        
        if ($type === 'ai_feedback') {
            // AIFeatureController handles tracking for ai_feedback
            return;
        }

        \App\Models\AiUsage::where('user_id', $user->id)
            ->where('usage_date', $today)
            ->increment('requests_count');
    }

    /**
     * Parse the response text to extract json.
     * We look for the first [ and the last ], or first { and last }.
     */
    protected function parseJsonFromText($text)
    {
        // Try to strip out markdown formatting if any e.g. ```json ... ```
        $text = preg_replace('/```json/i', '', $text);
        $text = preg_replace('/```/', '', $text);
        $text = trim($text);

        $firstBracket = strpos($text, '[');
        $firstBrace = strpos($text, '{');
        
        if ($firstBracket !== false && ($firstBrace === false || $firstBracket < $firstBrace)) {
            // Array seems to be the outermost
            $end = strrpos($text, ']');
            if ($end !== false) {
                $jsonStr = substr($text, $firstBracket, $end - $firstBracket + 1);
                $decoded = json_decode($jsonStr, true);
                if ($decoded !== null) return $decoded;
            }
        } elseif ($firstBrace !== false) {
            // Object seems to be the outermost
            $end = strrpos($text, '}');
            if ($end !== false) {
                $jsonStr = substr($text, $firstBrace, $end - $firstBrace + 1);
                $decoded = json_decode($jsonStr, true);
                if ($decoded !== null) return $decoded;
            }
        }

        return json_decode($text, true);
    }

    /**
     * Generate Quiz Questions via AI
     * 
     * @param string $topic
     * @param string $difficulty (easy, medium, hard)
     * @param int $count
     * @param string $type (multiple_choice, true_false, matching, essay)
     * @return array|null 
     */
    public function generateQuizQuestions($topic, $difficulty = 'medium', $count = 3, $type = 'multiple_choice', $pointsPerQuestion = 1)
    {
        $typeInstructions = "";
        $commonFields = "'question' (string), 'points' (integer, always $pointsPerQuestion)";

        if ($type === 'multiple_choice') {
            $typeInstructions = "Return an array of objects, each with $commonFields, 'options' (array of exactly 4 strings), and 'correct_answer_index' (integer 0-3).";
        } elseif ($type === 'true_false') {
            $typeInstructions = "Return an array of objects, each with $commonFields, 'options' (must be exactly ['True', 'False']), and 'correct_answer_index' (0 for True, 1 for False).";
        } elseif ($type === 'matching') {
            $typeInstructions = "Return an array of objects for matching. Each object has $commonFields (general instruction like 'Match the terms with their definitions') and 'pairs' (an object where keys are terms and values are their matching definitions). Provide exactly 4 pairs.";
        } elseif ($type === 'essay') {
            $typeInstructions = "Return an array of objects, each with $commonFields (the essay prompt) and 'model_answer' (a suggested correct response).";
        }

        $prompt = "Generate {$count} {$difficulty} English quiz questions about '{$topic}' of type '{$type}'. " . 
                  $typeInstructions . 
                  " Return the result ONLY as a raw JSON array of these objects. Do not include markdown formatting or extra text.";

        return $this->sendToGeminiAndParse($prompt);
    }

    /**
     * AI Grading for Essay Answers
     */
    public function gradeEssayAnswer($questionText, $studentAnswer, $modelAnswerText = null)
    {
        if (!$this->canUseAI()) {
            return [
                'score' => 0,
                'feedback' => "عذراً، لقد استنفذت رصيد الاستخدام اليومي للذكاء الاصطناعي الخاص بباقتك. يرجى الترقية للحصول على المزيد."
            ];
        }

        $prompt = "You are an English teacher. Grade the following student's essay answer based on the question prompt.\n" .
                  "Question: {$questionText}\n";
                  
        if ($modelAnswerText) {
            $prompt .= "Model Answer (Expected): {$modelAnswerText}\n";
        }
        
        $prompt .= "Student Answer: {$studentAnswer}\n\n" .
                  "Provide the result ONLY as JSON with:\n" .
                  "- 'score': (integer 0-100)\n" .
                  "- 'feedback': (string explanation of the grade)\n" .
                  "Do not include any other text.";

        return $this->sendToGeminiAndParse($prompt);
    }

    /**
     * Generate Feedback for a Wrong Answer
     * 
     * @param string $questionText
     * @param string $correctAnswer
     * @param string $studentAnswer
     * @return string
     */
    public function generateQuestionFeedback($questionText, $correctAnswer, $studentAnswer)
    {
        if (!$this->canUseAI('ai_feedback')) {
            return "عذراً، لقد استنفذت رصيد التغذية الراجعة بالذكاء الاصطناعي لهذا اليوم. قم بالترقية للحصول على استخدام غير محدود.";
        }

        $prompt = "A student answered a question incorrectly.\n" . 
                  "Question: \"{$questionText}\"\n" .
                  "Correct Answer: \"{$correctAnswer}\"\n" .
                  "Student's Answer: \"{$studentAnswer}\"\n" .
                  "Explain why this answer is wrong and provide a simple, encouraging explanation for the student to help them understand the concept. Keep it concise (2-3 sentences).";

        $response = $this->sendToGemini($prompt);
        return $response['text'] ?? "حدث خطأ أثناء جلب رد الذكاء الاصطناعي. الرجاء المحاولة لاحقاً.";
    }

    /**
     * Internal method to send prompt to Gemini API
     */
    protected function sendToGemini($prompt)
    {
        if (empty($this->apiKey)) {
            Log::error("AIService: AI_API_KEY is missing from environment variables.");
            return ['error' => true, 'message' => 'API Key is missing'];
        }

        $url = $this->apiUrl . "?key=" . $this->apiKey;

        try {
            $response = Http::post($url, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ]
            ]);

            if ($response->successful()) {
                $this->consumeAIQuota();
                $data = $response->json();
                $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
                return ['error' => false, 'text' => $text];
            } else {
                Log::error("AIService Error: " . $response->body());
                return ['error' => true, 'message' => 'API Error'];
            }
        } catch (\Exception $e) {
            Log::error("AIService Exception: " . $e->getMessage());
            return ['error' => true, 'message' => 'Exception occurred'];
        }
    }

    /**
     * Sends to Gemini and immediately attempts to parse JSON
     */
    protected function sendToGeminiAndParse($prompt)
    {
        $result = $this->sendToGemini($prompt);
        if ($result['error']) {
            return null;
        }

        \Illuminate\Support\Facades\Log::info("AI Raw Text:", ['text' => $result['text']]);
        
        $parsed = $this->parseJsonFromText($result['text']);
        if (!$parsed) {
            \Illuminate\Support\Facades\Log::error("AI JSON Parse Failed. Raw Text: " . $result['text']);
        }

        return $parsed;
    }

    /**
     * Generate Lesson Draft
     * 
     * @param string $topic
     * @param string $level
     * @param string $length
     * @return array|null
     */
    public function generateLessonDraft($topic, $level = 'Intermediate', $length = 'medium')
    {
        $prompt = "Generate a structured English lesson for {$level} students about {$topic}. " .
                  "The length of the lesson should be {$length}. " .
                  "Return the result ONLY as a JSON object with the properties: " .
                  "'title' (string), " .
                  "'blocks' (array of objects, where each object has 'type': 'text' and 'content': string). " .
                  "Create exactly 3 blocks: " .
                  "1. Simple explanation. " .
                  "2. 3 to 5 examples. " .
                  "3. Key points summary. " .
                  "Keep the content clear, beginner-friendly, and well-organized. Do not include any other text except valid JSON.";

        return $this->sendToGeminiAndParse($prompt);
    }
}
