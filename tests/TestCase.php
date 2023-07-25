<?php

namespace Tests;

use Closure;
use App\Helpers\Helper;
use Mockery\MockInterface;
use App\Http\Middleware\HasPermission;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    public function setUp(): void
    {
        parent::setUp();

        Helper::wipe();
    }
//
//    protected function assertChanges(Closure $callback, mixed $from, mixed $to, Closure $test): void
//    {
//        $initial = $callback();
//
//        $test();
//
//        $this->assertSame($from, $initial);
//
//        $this->assertSame($to, $callback());
//    }

    protected function expectPermissionChecks(array $permissions): void
    {
        $this->instance(
            HasPermission::class,
            $this->mock(HasPermission::class, function (MockInterface $mock) use ($permissions) {
                foreach ($permissions as $permission) {
                    $mock->shouldReceive('handle')->withSomeOfArgs(
                        $permission,
                    )->andReturnUsing(function ($request, $next) {
                        return $next($request);
                    })->once();
                }
            })
        );
    }
}
