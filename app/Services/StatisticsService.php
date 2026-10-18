<?php

namespace App\Services;

use App\Models\PracticeSession;
use App\Models\SessionAnswer;
use App\Models\Visitor;
use Illuminate\Support\Facades\DB;

class StatisticsService
{
    public function forVisitor(Visitor $visitor): array
    {
        $answers = SessionAnswer::query()->whereHas('session', fn ($query) => $query->where('visitor_id', $visitor->id));
        $total = (clone $answers)->count();
        $correct = (clone $answers)->where('is_correct', true)->count();
        $unique = (clone $answers)->distinct()->count('question_id');
        $completed = PracticeSession::query()->where('visitor_id', $visitor->id)->whereNotNull('completed_at')->count();
        $exams = PracticeSession::query()->where('visitor_id', $visitor->id)->where('mode', 'exam')->whereNotNull('completed_at');
        $examTotal = (clone $exams)->count();
        $examPassed = (clone $exams)->where('status', 'passed')->count();
        return ['total_answers' => $total, 'correct_answers' => $correct, 'errors' => $total - $correct, 'accuracy' => $total > 0 ? round($correct / $total * 100) : 0, 'unique_questions' => $unique, 'progress' => round($unique / 800 * 100, 1), 'completed_sessions' => $completed, 'exam_pass_rate' => $examTotal > 0 ? round($examPassed / $examTotal * 100) : 0, 'average_response_seconds' => round(((clone $answers)->avg('response_time_ms') ?? 0) / 1000, 1)];
    }

    public function difficultQuestions(Visitor $visitor): array
    {
        return SessionAnswer::query()->select('question_id', DB::raw('COUNT(*) as attempts'), DB::raw('SUM(CASE WHEN is_correct = 0 THEN 1 ELSE 0 END) as errors'))->whereHas('session', fn ($query) => $query->where('visitor_id', $visitor->id))->with('question:id,text,ticket_number,position')->groupBy('question_id')->having('errors', '>', 0)->orderByDesc('errors')->limit(8)->get()->all();
    }
}
