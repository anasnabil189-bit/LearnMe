<?php

namespace App\Services;

use App\Models\User;
use App\Models\Quiz;
use App\Models\Level;
use App\Models\QuizAttempt;
use App\Models\UserQuizSummary;
use Illuminate\Support\Facades\DB;

class QuizService
{
    protected $languageService;

    public function __construct(LanguageService $languageService)
    {
        $this->languageService = $languageService;
    }

    /**
     * Process a quiz submission and update XP based on best attempt.
     */
    public function processSubmission(User $user, Quiz $quiz, array $data)
    {
        return DB::transaction(function () use ($user, $quiz, $data) {
            $earnedPoints = $data['earned_points'] ?? 0;
            $totalPointsPossible = $data['total_points_possible'] ?? 0;
            
            $score = $data['score'] ?? 0; 
            $status = $data['status'] ?? 'completed';
            $details = $data['details'] ?? [];

            // XP logic: 10 XP per point earned
            $xpForThisAttempt = (int) round($earnedPoints * 10);
            $percentage = $totalPointsPossible > 0 ? round(($earnedPoints / $totalPointsPossible) * 100, 2) : 0;

            // 1. Create Quiz Attempt
            $attempt = QuizAttempt::create([
                'user_id'           => $user->id,
                'quiz_id'           => $quiz->id,
                'score'             => (int) round($earnedPoints),
                'total_points'      => (int) $totalPointsPossible,
                'unused_percentage' => $percentage,
                'xp_earned'         => $xpForThisAttempt,
                'status'            => $status,
                'details'           => $details,
            ]);

            if ($status === 'pending_grading') {
                return $attempt;
            }

            // 2. Handle Best Attempt & XP
            $this->updateSummaryIfBetter($user, $quiz, $attempt);

            return $attempt;
        });
    }

    /**
     * Update an attempt (e.g. after manual grading) and recalculate summary.
     */
    public function updateAttempt(QuizAttempt $attempt, array $data)
    {
        return DB::transaction(function () use ($attempt, $data) {
            $attempt->update($data);
            
            if (($data['status'] ?? $attempt->status) === 'completed') {
                $this->updateSummaryIfBetter($attempt->user, $attempt->quiz, $attempt);
            }

            return $attempt;
        });
    }

    /**
     * Internal logic to update summary and user XP if the provided attempt is the new best.
     */
    protected function updateSummaryIfBetter(User $user, Quiz $quiz, QuizAttempt $attempt)
    {
        $summary = UserQuizSummary::firstOrCreate(
            ['user_id' => $user->id, 'quiz_id' => $quiz->id],
            ['best_score' => 0, 'best_total_points' => 0, 'best_xp' => 0]
        );

        if ($attempt->xp_earned > $summary->best_xp) {
            $xpDiff = $attempt->xp_earned - $summary->best_xp;
            
            // Update User XP
            if ($quiz->quiz_type === 'challenge') {
                $user->increment('challenge_xp', $xpDiff);
            } else {
                // Determine language
                $languageId = $quiz->language_id ?: $this->languageService->getActiveLanguageId($user);
                $ul = $this->languageService->getUserLanguage($user, $languageId);
                $ul->increment('learning_xp', $xpDiff);
            }

            // Update Summary
            $summary->update([
                'best_score'        => $attempt->score,
                'best_total_points' => $attempt->total_points,
                'best_xp'           => $attempt->xp_earned,
            ]);
        }
    }

    /**
     * Check if a user has enough XP to access a level.
     */
    public function isLevelAccessible(User $user, Level $level): bool
    {
        if ($user->type === 'admin' || $user->type === 'teacher') {
            return true;
        }

        $languageId = $level->language_id ?: $this->languageService->getActiveLanguageId($user);
        $xp = $this->languageService->getUserXP($user, $languageId);

        return $xp >= ($level->required_xp ?? 0);
    }
}
