<?php

namespace App\Http\Controllers;

use App\Services\StatisticsService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StatisticsController
{
    public function __invoke(Request $request, StatisticsService $statistics): Response
    {
        $visitor = $request->attributes->get('visitor');
        return Inertia::render('Statistics', ['statistics' => $statistics->forVisitor($visitor), 'difficultQuestions' => $statistics->difficultQuestions($visitor)]);
    }
}
