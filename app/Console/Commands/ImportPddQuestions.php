<?php

namespace App\Console\Commands;

use App\Services\QuestionImportService;
use Illuminate\Console\Command;
use Throwable;

class ImportPddQuestions extends Command
{
    protected $signature = 'pdd:import {path? : Путь к локальной копии pdd_russia}';

    protected $description = 'Идемпотентно импортирует 40 билетов категорий A/B/M';

    public function handle(QuestionImportService $importer): int
    {
        $path = $this->argument('path') ?: env('PDD_DATASET_PATH', storage_path('app/pdd_russia'));
        try {
            $result = $importer->import($path, fn (string $message) => $this->line($message));
            $this->newLine();
            $this->info("Готово: {$result['questions']} вопросов, {$result['images']} изображений.");

            return self::SUCCESS;
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }
    }
}
