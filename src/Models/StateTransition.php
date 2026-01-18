<?php

namespace Spatie\ModelStates\Models;

use Illuminate\Database\Eloquent\Model;
use LogicException;

/**
 * @property int $id
 * @property string $model_type
 * @property int|string $model_id
 * @property string $field
 * @property string|null $from_state
 * @property string $to_state
 * @property array|null $meta_data
 * @property \Illuminate\Support\Carbon|null $created_at
 */
class StateTransition extends Model
{
    protected $guarded = [];

    protected $casts = [
        'meta_data' => 'array',
        'created_at' => 'datetime',
    ];

    public $timestamps = false;

    protected static function booted(): void
    {
        static::updating(function () {
            throw new LogicException('State transitions are immutable once stored.');
        });
    }

    public function getTable(): string
    {
        return config('model-states.transition_history.table', 'model_state_transitions');
    }

    public function getConnectionName(): ?string
    {
        return config('model-states.transition_history.connection');
    }
}
