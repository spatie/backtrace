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

    /** @test */
    public function it_can_detect_application_frames()
    {
        $applicationPath = '/Users/johndoe/Code/backtrace';

        $backtrace = json_decode(file_get_contents(__DIR__.'/testFiles/backtrace.json'), true);

        $backtrace = new Backtrace($backtrace, $applicationPath);

        $this->assertSame(8, $backtrace->firstApplicationFrameIndex());
    }
}
