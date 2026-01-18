<?php

return [

    /*
     * The fully qualified class name of the default transition.
     */
    'default_transition' => Spatie\ModelStates\DefaultTransition::class,

    /*
     * Opt-in transition history storage.
     *
     * When enabled, every successful state transition will be persisted in the
     * configured table. This unlocks per-transition `created_at` timestamps and
     * optional `meta_data` storage.
     */
    'transition_history' => [
        'enabled' => false,

        /*
         * The table name for storing transitions.
         * Publish and run the package migration to create it.
         */
        'table' => 'model_state_transitions',

        /*
         * Optional database connection name.
         */
        'connection' => null,

        /*
         * The Eloquent model used to persist transitions.
         */
        'model' => Spatie\ModelStates\Models\StateTransition::class,
    ],

];
