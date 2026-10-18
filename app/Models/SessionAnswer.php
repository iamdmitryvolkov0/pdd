<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SessionAnswer extends Model
{
    protected $guarded = [];
    protected $casts = ['answered_at' => 'immutable_datetime'];
    public function session() { return $this->belongsTo(PracticeSession::class, 'practice_session_id'); }
    public function question() { return $this->belongsTo(Question::class); }
}
