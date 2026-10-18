<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** @method static Builder inTicket(int $ticketNumber) @method static Builder atPositions(array $positions) @method static Builder inBlock(int $blockNumber) */
class Question extends Model
{
    protected $guarded = [];

    public function options(): HasMany
    {
        return $this->hasMany(AnswerOption::class)->orderBy('position');
    }

    public function scopeInTicket(Builder $query, int $ticketNumber): Builder
    {
        return $query->where('ticket_number', $ticketNumber);
    }

    public function scopeAtPositions(Builder $query, array $positions): Builder
    {
        return $query->whereIn('position', $positions);
    }

    public function scopeInBlock(Builder $query, int $blockNumber): Builder
    {
        return $query->where('block_number', $blockNumber);
    }
}
