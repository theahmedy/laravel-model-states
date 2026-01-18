<?php

use Spatie\ModelStates\Models\StateTransition;
use Spatie\ModelStates\Tests\Dummy\ModelStates\StateA;
use Spatie\ModelStates\Tests\Dummy\ModelStates\StateB;
use Spatie\ModelStates\Tests\Dummy\TestModel;

it('can persist transition metadata and timestamp when enabled', function () {
    config()->set('model-states.transition_history.enabled', true);

    $model = TestModel::create([
        'state' => StateA::class,
    ]);

    $model->state->transitionTo(StateB::class, metaData: [
        'approved_by' => 5,
        'comment' => 'Reviewed',
    ]);

    $model->refresh();

    expect($model->state)->toBeInstanceOf(StateB::class);

    expect($model->state->metaData())->toEqual([
        'approved_by' => 5,
        'comment' => 'Reviewed',
    ]);

    expect($model->state->createdAt())->not()->toBeNull();

    $transition = StateTransition::query()->first();

    expect($transition)->not()->toBeNull();
    expect($transition->from_state)->toEqual(StateA::getMorphClass());
    expect($transition->to_state)->toEqual(StateB::getMorphClass());
    expect($transition->field)->toEqual('state');
    expect($transition->meta_data)->toEqual([
        'approved_by' => 5,
        'comment' => 'Reviewed',
    ]);
    expect($transition->created_at)->not()->toBeNull();
});

it('does not persist transition metadata when disabled', function () {
    config()->set('model-states.transition_history.enabled', false);

    $model = TestModel::create([
        'state' => StateA::class,
    ]);

    $model->state->transitionTo(StateB::class, metaData: ['approved_by' => 5]);

    $model->refresh();

    expect(StateTransition::query()->count())->toEqual(0);
    expect($model->state->metaData())->toBeNull();
    expect($model->state->createdAt())->toBeNull();
});

it('stored transitions are immutable', function () {
    config()->set('model-states.transition_history.enabled', true);

    $model = TestModel::create([
        'state' => StateA::class,
    ]);

    $model->state->transitionTo(StateB::class, metaData: ['approved_by' => 5]);

    $transition = StateTransition::query()->firstOrFail();

    $this->expectException(LogicException::class);

    $transition->meta_data = ['approved_by' => 6];
    $transition->save();
});
