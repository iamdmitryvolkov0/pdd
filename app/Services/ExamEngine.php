<?php

namespace App\Services;

use App\Models\PracticeSession;
use Illuminate\Support\Collection;

class ExamEngine
{
    public function evaluate(PracticeSession $session): array
    {
        $answers = $session->answers;
        $wrongAnswers = $answers->where('is_correct', false);
        $wrongMainAnswers = $wrongAnswers->filter(fn ($answer): bool => (int) $session->questions->firstWhere('question_id', $answer->question_id)?->stage === 0);
        $wrongAdditionalAnswers = $wrongAnswers->count() - $wrongMainAnswers->count();
        $wrongByBlock = $this->wrongAnswersByBlock($session, $wrongMainAnswers);

        if (now()->isAfter($session->expires_at) || $wrongAdditionalAnswers > 0 || $wrongMainAnswers->count() >= 3 || $wrongByBlock->max() >= 2) return ['status' => 'failed'];
        if ($answers->count() < $session->questions->count()) return ['status' => 'active'];
        if ($wrongMainAnswers->isEmpty()) return ['status' => 'passed'];
        if ($session->questions->where('stage', 1)->isEmpty()) return ['status' => 'additional', 'blocks' => $wrongByBlock->keys()->all()];
        return ['status' => 'passed'];
    }

    private function wrongAnswersByBlock(PracticeSession $session, Collection $answers): Collection
    {
        return $answers->groupBy(fn ($answer): int => (int) $session->questions->firstWhere('question_id', $answer->question_id)?->block_number)->map->count();
    }
}
