<?php

namespace App\Services;

use App\Models\Question;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use RuntimeException;

class QuestionImportService
{
    public function import(string $datasetPath, callable $report): array
    {
        $ticketsPath = rtrim($datasetPath, '/') . '/questions/A_B/tickets';
        if (! File::isDirectory($ticketsPath)) {
            throw new RuntimeException("Каталог билетов не найден: {$ticketsPath}");
        }

        $importedQuestions = 0;
        $copiedImages = 0;
        for ($ticketNumber = 1; $ticketNumber <= 40; $ticketNumber++) {
            $ticketFile = "{$ticketsPath}/Билет {$ticketNumber}.json";
            if (! File::exists($ticketFile)) {
                throw new RuntimeException("Не найден билет {$ticketNumber}.");
            }
            $questions = json_decode(File::get($ticketFile), true, flags: JSON_THROW_ON_ERROR);
            if (! is_array($questions) || count($questions) !== 20) {
                throw new RuntimeException("Билет {$ticketNumber} должен содержать 20 вопросов.");
            }

            DB::transaction(function () use ($questions, $ticketNumber, $datasetPath, &$importedQuestions, &$copiedImages): void {
                foreach ($questions as $questionData) {
                    $position = $this->position($questionData);
                    $answers = $questionData['answers'] ?? null;
                    if (! is_array($answers) || collect($answers)->where('is_correct', true)->count() !== 1) {
                        throw new RuntimeException("Билет {$ticketNumber}, вопрос {$position}: требуется ровно один правильный ответ.");
                    }
                    $imagePath = $this->copyImage($questionData['image'] ?? null, $datasetPath);
                    if ($imagePath !== null) {
                        $copiedImages++;
                    }

                    $question = Question::query()->updateOrCreate(
                        ['ticket_number' => $ticketNumber, 'position' => $position],
                        ['source_id' => (string) ($questionData['id'] ?? ''), 'block_number' => intdiv($position - 1, 5) + 1, 'text' => (string) ($questionData['question'] ?? ''), 'explanation' => $questionData['answer_tip'] ?? null, 'image_path' => $imagePath, 'topics' => json_encode($questionData['topic'] ?? [], JSON_UNESCAPED_UNICODE)]
                    );
                    foreach ($answers as $answerIndex => $answerData) {
                        $question->options()->updateOrCreate(
                            ['position' => $answerIndex + 1],
                            ['text' => (string) ($answerData['answer_text'] ?? ''), 'is_correct' => (bool) ($answerData['is_correct'] ?? false)]
                        );
                    }
                    $importedQuestions++;
                }
            });
            $report("Билет {$ticketNumber}: импортировано 20 вопросов");
        }
        if ($importedQuestions !== 800) {
            throw new RuntimeException("Ожидалось 800 вопросов, получено {$importedQuestions}.");
        }

        return ['questions' => $importedQuestions, 'images' => $copiedImages];
    }

    private function position(array $questionData): int
    {
        if (! preg_match('/(\d+)/u', (string) ($questionData['title'] ?? ''), $matches)) {
            throw new RuntimeException('Не удалось определить номер вопроса.');
        }

        return (int) $matches[1];
    }

    private function copyImage(mixed $sourceImage, string $datasetPath): ?string
    {
        if (! is_string($sourceImage) || $sourceImage === '') {
            return null;
        }
        $relativeSource = ltrim(preg_replace('#^(\./)?(tmp/)?#', '', $sourceImage), '/');
        if ($relativeSource === 'images/no_image.jpg') {
            return null;
        }
        $absoluteSource = rtrim($datasetPath, '/') . '/' . $relativeSource;
        if (! File::exists($absoluteSource)) {
            throw new RuntimeException("Изображение не найдено: {$relativeSource}");
        }
        $extension = pathinfo($absoluteSource, PATHINFO_EXTENSION);
        $filename = hash_file('sha256', $absoluteSource) . '.' . $extension;
        $targetDirectory = public_path('images/questions');
        File::ensureDirectoryExists($targetDirectory);
        if (! File::exists("{$targetDirectory}/{$filename}")) {
            File::copy($absoluteSource, "{$targetDirectory}/{$filename}");
        }

        return "/images/questions/{$filename}";
    }
}
