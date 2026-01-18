<?php

namespace Spatie\ModelStates\Tests;

use Illuminate\Database\Schema\Blueprint;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase();
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    protected function setUpDatabase()
    {
        $this->app->get('db')->connection()->getSchemaBuilder()->create('test_models', function (Blueprint $table) {
            $table->increments('id');
            $table->string('state')->nullable();
            $table->string('message')->nullable();
            $table->timestamps();
        });

        $this->app->get('db')->connection()->getSchemaBuilder()->create('model_state_transitions', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('model_type');
            $table->unsignedBigInteger('model_id');

            $table->string('field');

            $table->string('from_state')->nullable();
            $table->string('to_state');

            $table->json('meta_data')->nullable();

            $table->timestamp('created_at')->nullable();

            $table->index(['model_type', 'model_id', 'field']);
            $table->index(['model_type', 'model_id', 'field', 'created_at']);
        });
    }
}
