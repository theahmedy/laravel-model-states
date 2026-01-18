<?php

namespace Spatie\ModelStates\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Spatie\ModelStates\Exceptions\InvalidConfig;
use Spatie\ModelStates\Models\StateTransition;
use Spatie\ModelStates\State;
use Spatie\ModelStates\Transition;

class TransitionHistory
{
    private static array $tableExistsCache = [];

    public static function enabled(): bool
    {
        return (bool) config('model-states.transition_history.enabled', false);
    }

    public static function resolveModelClass(): string
    {
        return config('model-states.transition_history.model', StateTransition::class);
    }

    public static function record(State $fromState, State $toState, Transition $transition): ?StateTransition
    {
        if (! self::enabled()) {
            return null;
        }

        $model = $toState->getModel();

        if (! $model instanceof Model) {
            return null;
        }

        if (! $model->exists) {
            return null;
        }

        $table = config('model-states.transition_history.table', 'model_state_transitions');
        $connection = config('model-states.transition_history.connection');

        if (! self::tableExists($table, $connection)) {
            throw InvalidConfig::transitionHistoryTableMissing($table);
        }

        $transitionModelClass = self::resolveModelClass();

        /** @var \Spatie\ModelStates\Models\StateTransition $record */
        $record = new $transitionModelClass();

        $record->forceFill([
            'model_type' => $model->getMorphClass(),
            'model_id' => $model->getKey(),
            'field' => $toState->getField(),
            'from_state' => $fromState->getValue(),
            'to_state' => $toState->getValue(),
            'meta_data' => self::normalizeMetaData($transition->metaData()),
            'created_at' => now(),
        ]);

        if ($connection) {
            $record->setConnection($connection);
        }

        $record->save();

        return $record;
    }

    /**
     * @return array|null
     */
    public static function normalizeMetaData(?array $metaData): ?array
    {
        if ($metaData === null) {
            return null;
        }

        return $metaData;
    }

    /**
     * @return array{metaData: array|null, createdAt: \DateTimeInterface|null}
     */
    public static function loadForState(State $state): array
    {
        if (! self::enabled()) {
            return ['metaData' => null, 'createdAt' => null];
        }

        $model = $state->getModel();

        if (! $model instanceof Model || ! $model->exists) {
            return ['metaData' => null, 'createdAt' => null];
        }

        $table = config('model-states.transition_history.table', 'model_state_transitions');
        $connection = config('model-states.transition_history.connection');

        if (! self::tableExists($table, $connection)) {
            throw InvalidConfig::transitionHistoryTableMissing($table);
        }

        $transitionModelClass = self::resolveModelClass();

        /** @var \Spatie\ModelStates\Models\StateTransition|null $record */
        $record = $transitionModelClass::query()
            ->where('model_type', $model->getMorphClass())
            ->where('model_id', $model->getKey())
            ->where('field', $state->getField())
            ->where('to_state', $state->getValue())
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->first();

        if (! $record) {
            return ['metaData' => null, 'createdAt' => null];
        }

        return [
            'metaData' => $record->meta_data,
            'createdAt' => $record->created_at,
        ];
    }

    private static function tableExists(string $table, ?string $connection): bool
    {
        $cacheKey = ($connection ?: 'default') . '|' . $table;

        if (array_key_exists($cacheKey, self::$tableExistsCache)) {
            return self::$tableExistsCache[$cacheKey];
        }

        return self::$tableExistsCache[$cacheKey] = (bool) ($connection
            ? Schema::connection($connection)->hasTable($table)
            : Schema::hasTable($table));
    }
}
