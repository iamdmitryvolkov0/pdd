<?php

namespace App\Services;

use App\Models\AnswerOption;
use App\Models\PracticeSession;
use App\Models\Question;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AnswerSubmissionService
{
    public function __construct(private ExamEngine $examEngine, private QuestionSelectionService $questions)
    {
    }

    public function submit(PracticeSession $session, Question $question, AnswerOption $option, ?int $responseTimeMs): array
    {
        return DB::transaction(function () use ($session, $question, $option, $responseTimeMs): array {
            $lockedSession = PracticeSession::query()->lockForUpdate()->findOrFail($session->id);
            if ($lockedSession->status !== 'active') {
                throw ValidationException::withMessages(['session' => 'Сессия уже завершена.']);
            }
            if ($lockedSession->mode === 'exam' && now()->isAfter($lockedSession->expires_at)) {
                $lockedSession->update(['status' => 'failed', 'completed_at' => now()]);
                throw ValidationException::withMessages(['session' => 'Время экзамена истекло.']);
            }
            $belongsToSession = $lockedSession->questions()->where('question_id', $question->id)->exists();
            if (! $belongsToSession || $option->question_id !== $question->id) {
                throw ValidationException::withMessages(['answer' => 'Ответ не относится к этой сессии.']);
            }

            $answer = $lockedSession->answers()->firstOrCreate(['question_id' => $question->id], ['answer_option_id' => $option->id, 'is_correct' => $option->is_correct, 'response_time_ms' => $responseTimeMs, 'answered_at' => now()]);
            $lockedSession->load(['answers', 'questions.question']);
            $state = $lockedSession->mode === 'exam' ? $this->examEngine->evaluate($lockedSession) : ['status' => 'active'];
            if ($state['status'] === 'failed' || $state['status'] === 'passed') {
                $lockedSession->update(['status' => $state['status'], 'completed_at' => now()]);
            }
            if ($state['status'] === 'additional') {
                $this->appendAdditionalQuestions($lockedSession, $state['blocks']);
            }

            return ['answer' => $answer, 'state' => $state, 'is_correct' => $lockedSession->mode === 'exam' ? null : $answer->is_correct];
        });
    }

    private function appendAdditionalQuestions(PracticeSession $session, array $blocks): void
    {
        $existingQuestions = $session->questions->map->question;
        $position = $session->questions->max('position');
        foreach ($blocks as $blockNumber) {
            foreach ($this->questions->additionalBlock((int) $blockNumber, $existingQuestions) as $question) {
                $position++;
                $session->questions()->create(['question_id' => $question->id, 'position' => $position, 'stage' => 1, 'block_number' => $blockNumber]);
            }
        }
        $session->update(['expires_at' => now()->addMinutes(count($blocks) * 5)]);
    }
}
