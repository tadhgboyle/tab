<?php

namespace Tests;

use Mockery\MockInterface;
use App\Http\Middleware\HasPermission;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    public function setUp(): void
    {
        parent::setUp();
    }

    protected function expectPermissionChecks(array $permissions): void
    {
        $this->mock(HasPermission::class, function (MockInterface $mock) use ($permissions) {
            foreach ($permissions as $permission) {
                $mock->shouldReceive('handle')->withSomeOfArgs(
                    $permission,
                )->andReturnUsing(function ($request, $next) {
                    return $next($request);
                })->once();
            }
        });
    }

    protected function expectMiddleware(array $middlewares): void
    {
        foreach ($middlewares as $middleware) {
            $this->mock($middleware, function (MockInterface $mock) {
                $mock->shouldReceive('handle')
                    ->andReturnUsing(function ($request, $next) {
                        return $next($request);
                    })->once();
            });
        }
    }
}
