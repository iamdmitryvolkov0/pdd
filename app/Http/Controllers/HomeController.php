<?php

namespace App\Http\Controllers;

use App\Models\PracticeSession;
use App\Services\StatisticsService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HomeController
{
    public function __invoke(Request $request, StatisticsService $statistics): Response
    {
        $visitor = $request->attributes->get('visitor');
        $activeSession = PracticeSession::query()->where('visitor_id', $visitor->id)->where('status', 'active')->latest()->first();

        return Inertia::render('Home', ['statistics' => $statistics->forVisitor($visitor), 'activeSession' => $activeSession?->only(['uuid', 'mode'])]);
    }
}
