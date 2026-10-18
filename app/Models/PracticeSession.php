<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PracticeSession extends Model
{
    protected $guarded = [];
    protected $casts = ['started_at' => 'immutable_datetime', 'expires_at' => 'immutable_datetime', 'completed_at' => 'immutable_datetime', 'settings' => 'array'];

    public function visitor(): BelongsTo { return $this->belongsTo(Visitor::class); }
    public function questions(): HasMany { return $this->hasMany(SessionQuestion::class)->orderBy('position'); }
    public function answers(): HasMany { return $this->hasMany(SessionAnswer::class); }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
