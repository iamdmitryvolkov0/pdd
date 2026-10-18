<?php

namespace App\Services;

use App\Models\Question;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class QuestionSelectionService
{
    public function forPractice(): Collection
    {
        return collect(range(1, 4))->flatMap(function (int $blockNumber): Collection {
            $ticketNumber = random_int(1, 40);

            return Question::query()->inTicket($ticketNumber)->inBlock($blockNumber)->orderBy('position')->get();
        });
    }

    public function forExam(): Collection
    {
        return $this->forPractice();
    }

    public function forTicket(int $ticketNumber): Collection
    {
        if ($ticketNumber < 1 || $ticketNumber > 40) {
            throw new InvalidArgumentException('Номер билета должен быть от 1 до 40.');
        }

        return Question::query()->inTicket($ticketNumber)->orderBy('position')->get();
    }

    public function forPositions(array $positions): Collection
    {
        $validPositions = collect($positions)->map(fn (mixed $position): int => (int) $position)->filter(fn (int $position): bool => $position >= 1 && $position <= 20)->unique()->all();
        if ($validPositions === []) {
            throw new InvalidArgumentException('Выберите хотя бы один номер вопроса.');
        }

        return Question::query()->atPositions($validPositions)->inRandomOrder()->get();
    }

    public function forMarathon(): Collection
    {
        return Question::query()->orderBy('ticket_number')->orderBy('position')->get();
    }

    public function additionalBlock(int $blockNumber, Collection $excludedQuestions): Collection
    {
        $excludedQuestionIds = $excludedQuestions->pluck('id');
        $ticketNumbers = Question::query()->inBlock($blockNumber)->whereNotIn('id', $excludedQuestionIds)->distinct()->pluck('ticket_number')->shuffle();
        foreach ($ticketNumbers as $ticketNumber) {
            $questions = Question::query()->inBlock($blockNumber)->inTicket((int) $ticketNumber)->whereNotIn('id', $excludedQuestionIds)->orderBy('position')->get();
            if ($questions->count() === 5) {
                return $questions;
            }
        }
        throw new InvalidArgumentException("Не удалось сформировать дополнительный блок {$blockNumber}.");
    }
}
