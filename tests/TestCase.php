<?php

namespace Tests;

use App\Device;
use App\Exceptions\Handler;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected $signInUser;
    protected $device;

    protected function setUp()
    {
        parent::setUp();

        // DB::statement('PRAGMA foreign_keys=on;');

        $this->disableExceptionHandling();
        $this->withExceptionHandling();
    }

    protected function signIn($role = 'user')
    {
        $this->signInUser = createUser($role);

        $this->loginAs($this->signInUser->user);

        $this->device = $this->signInUser->user->devices()->create(factory(Device::class)->make()->toArray());

        return $this;
    }

    protected function disableExceptionHandling()
    {
        $this->oldExceptionHandler = $this->app->make(ExceptionHandler::class);

        $this->app->instance(ExceptionHandler::class, new class extends Handler {
            public function __construct()
            {
            }

            public function report(\Exception $e)
            {
            }

            public function render($request, \Exception $e)
            {
                throw $e;
            }
        });
    }

    protected function withExceptionHandling()
    {
        $this->app->instance(ExceptionHandler::class, $this->oldExceptionHandler);

        return $this;
    }

    protected function assertHasErrorsFor($fields)
    {
    }

    public function sendAnalytics($model, $action = 'start', $time = null)
    {
        if ($model instanceof \App\Tour) {
            return $this->postJson("/mobile/tours/{$model->id}/track", [
                'activity' => [
                    [
                        'action' => $action,
                        'device_id' => $this->device->id,
                        'timestamp' => $time ?: strtotime('now'),
                    ],
                ],
            ])->assertStatus(200);
        } elseif ($model instanceof \App\TourStop) {
            return $this->postJson("/mobile/stops/{$model->id}/track", [
                'activity' => [
                    [
                        'action' => $action,
                        'device_id' => $this->device->id,
                        'timestamp' => $time ?: strtotime('now'),
                    ],
                ],
            ])->assertStatus(200);
        }
    }
}
