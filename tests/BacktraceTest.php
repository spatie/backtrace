<?php

namespace Spatie\Backtrace\Tests;

use Spatie\Backtrace\Tests\Concerns\MatchesCodeSnippetSnapshots;
use Spatie\Backtrace\Backtrace;
use Spatie\Backtrace\Tests\TestClasses\ThrowAndReturnExceptionAction;

class BacktraceTest extends TestCase
{
    use MatchesCodeSnippetSnapshots;

    /** @test */
    public function it_can_convert_an_exception_to_an_array()
    {
        $exception = (new ThrowAndReturnExceptionAction())->execute();

        $backtrace = Backtrace::createForThrowable($exception)->toArray();

        $this->assertGreaterThan(1, count($backtrace));

        $this->assertMatchesCodeSnippetSnapshot($backtrace[0]);
    }

    /** @test */
    public function it_can_create_a_backtrace()
    {
        $backtrace = Backtrace::create()->toArray();

        $this->assertGreaterThan(1, count($backtrace));

        $this->assertMatchesCodeSnippetSnapshot($backtrace[0]);
    }
}
