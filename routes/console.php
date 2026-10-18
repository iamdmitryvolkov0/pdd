<?php

use Illuminate\Support\Facades\Artisan;

Artisan::command('about-pdd', function (): void {
    $this->info('Вектор ПДД');
});
