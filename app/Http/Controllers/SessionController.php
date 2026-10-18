<?php

namespace App\Http\Controllers;

use App\Models\AnswerOption;
use App\Models\PracticeSession;
use App\Models\Question;
use App\Services\AnswerSubmissionService;
use App\Services\PracticeSessionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class SessionController
{
    public function tickets(Request $request): Response
    {
        $visitor = $request->attributes->get('visitor');
        $results = PracticeSession::query()->where('visitor_id', $visitor->id)->where('mode', 'ticket')->whereNotNull('completed_at')->get()->groupBy(fn (PracticeSession $session): int => (int) $session->settings['ticket'])->map(function ($sessions): array {
            $last = $sessions->sortByDesc('completed_at')->first();
            $scores = $sessions->map(fn (PracticeSession $session): int => $session->answers()->where('is_correct', true)->count());

            return ['last' => $scores->first(), 'best' => $scores->max(), 'errors' => 20 - $scores->first()];
        });

        return Inertia::render('Tickets', ['results' => $results]);
    }

    public function questions(): Response
    {
        return Inertia::render('QuestionPicker');
    }

    public function store(Request $request, PracticeSessionService $sessions): RedirectResponse
    {
        $data = $request->validate(['mode' => ['required', Rule::in(['practice', 'exam', 'marathon', 'ticket', 'questions'])], 'ticket' => ['nullable', 'integer', 'between:1,40'], 'positions' => ['nullable', 'array'], 'positions.*' => ['integer', 'between:1,20']]);
        $settings = [];
        if (isset($data['ticket'])) {
            $settings['ticket'] = $data['ticket'];
        }
        if (isset($data['positions'])) {
            $settings['positions'] = $data['positions'];
        }
        $session = $sessions->start($request->attributes->get('visitor'), $data['mode'], $settings);

        return redirect()->route('sessions.show', $session->uuid);
    }

    public function show(Request $request, PracticeSession $practiceSession): Response
    {
        $this->authorizeVisitor($request, $practiceSession);
        if ($practiceSession->mode === 'exam' && $practiceSession->status === 'active' && now()->isAfter($practiceSession->expires_at)) {
            $practiceSession->update(['status' => 'failed', 'completed_at' => now()]);
        }
        $practiceSession->load(['questions.question.options', 'answers']);
        $answeredByQuestion = $practiceSession->answers->keyBy('question_id');
        $isExamInProgress = $practiceSession->mode === 'exam' && $practiceSession->status === 'active';
        $questions = $practiceSession->questions->map(function ($sessionQuestion) use ($answeredByQuestion, $isExamInProgress): array {
            $question = $sessionQuestion->question;
            $answer = $answeredByQuestion->get($question->id);
            $options = $question->options->map(function (AnswerOption $option) use ($answer, $isExamInProgress): array {
                $payload = ['id' => $option->id, 'position' => $option->position, 'text' => $option->text];
                if (! $isExamInProgress && $answer !== null) {
                    $payload['is_correct'] = (bool) $option->getRawOriginal('is_correct');
                }

                return $payload;
            });

            return ['id' => $question->id, 'position' => $sessionQuestion->position, 'stage' => $sessionQuestion->stage, 'block' => $sessionQuestion->block_number, 'ticket' => $question->ticket_number, 'text' => $question->text, 'image' => $question->image_path, 'explanation' => $isExamInProgress || $answer === null ? null : $question->explanation, 'options' => $options, 'answer' => $answer?->only(['answer_option_id', 'is_correct'])];
        });

        return Inertia::render('Session', ['session' => ['uuid' => $practiceSession->uuid, 'mode' => $practiceSession->mode, 'status' => $practiceSession->status, 'startedAt' => $practiceSession->started_at->toIso8601String(), 'expiresAt' => $practiceSession->expires_at?->toIso8601String(), 'questions' => $questions]]);
    }

    public function answer(Request $request, PracticeSession $practiceSession, AnswerSubmissionService $answers): JsonResponse
    {
        $this->authorizeVisitor($request, $practiceSession);
        $data = $request->validate(['question_id' => ['required', 'integer'], 'answer_option_id' => ['required', 'integer'], 'response_time_ms' => ['nullable', 'integer', 'min:0', 'max:3600000']]);
        $question = Question::query()->findOrFail($data['question_id']);
        $option = AnswerOption::query()->findOrFail($data['answer_option_id']);
        $result = $answers->submit($practiceSession, $question, $option, $data['response_time_ms'] ?? null);
        $payload = ['state' => $result['state'], 'isCorrect' => $result['is_correct']];
        if ($practiceSession->mode !== 'exam') {
            $payload['correctOptionId'] = $question->options()->where('is_correct', true)->value('id');
            $payload['explanation'] = $question->explanation;
        }

        return response()->json($payload);
    }

    public function finish(Request $request, PracticeSession $practiceSession): RedirectResponse
    {
        $this->authorizeVisitor($request, $practiceSession);
        DB::transaction(function () use ($practiceSession): void {
            $session = PracticeSession::query()->lockForUpdate()->findOrFail($practiceSession->id);
            if ($session->status !== 'active') {
                return;
            }
            $status = $session->mode === 'exam' ? 'failed' : 'completed';
            $session->update(['status' => $status, 'completed_at' => now(), 'elapsed_seconds' => $session->started_at->diffInSeconds(now())]);
        });

        return redirect()->route('sessions.show', $practiceSession->uuid);
    }

    private function authorizeVisitor(Request $request, PracticeSession $session): void
    {
        abort_unless($session->visitor_id === $request->attributes->get('visitor')->id, 404);
    }
}
