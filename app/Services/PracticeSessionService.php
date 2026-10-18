<?php

namespace App\Services;

use App\Models\PracticeSession;
use App\Models\Visitor;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class PracticeSessionService
{
    public function __construct(private QuestionSelectionService $questions) {}

    public function start(Visitor $visitor, string $mode, array $settings = []): PracticeSession
    {
        $questions = match ($mode) {
            'practice' => $this->questions->forPractice(), 'exam' => $this->questions->forExam(), 'marathon' => $this->questions->forMarathon(),
            'ticket' => $this->questions->forTicket((int) ($settings['ticket'] ?? 0)), 'questions' => $this->questions->forPositions($settings['positions'] ?? []),
            default => throw new InvalidArgumentException('Неизвестный режим.'),
        };
        if ($questions->isEmpty()) throw new InvalidArgumentException('Вопросы не импортированы. Выполните php artisan pdd:import.');

        return DB::transaction(function () use ($visitor, $mode, $settings, $questions): PracticeSession {
            $session = PracticeSession::query()->create(['uuid' => (string) Str::uuid(), 'visitor_id' => $visitor->id, 'mode' => $mode, 'settings' => $settings, 'started_at' => now(), 'expires_at' => $mode === 'exam' ? now()->addMinutes(20) : null]);
            $questions->values()->each(function ($question, int $index) use ($session): void {
                $session->questions()->create(['question_id' => $question->id, 'position' => $index + 1, 'stage' => 0, 'block_number' => $question->block_number]);
            });
            return $session;
        });
    }
}
