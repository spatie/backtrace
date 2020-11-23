<?php

namespace Spatie\Backtrace\Tests;

use Spatie\Backtrace\Backtrace;
use Spatie\Backtrace\Tests\Concerns\MatchesCodeSnippetSnapshots;
use Spatie\Backtrace\Tests\TestClasses\ThrowAndReturnExceptionAction;

class BacktraceTest extends TestCase
{
    /** @test */
    public function it_can_create_a_backtrace()
    {
        $frames = Backtrace::create()->frames();

        $this->assertGreaterThan(1, count($frames));

        /** @var \Spatie\Backtrace\Frame $firstFrame */
        $firstFrame = $frames[0];

        $this->assertEquals( __LINE__ - 7, $firstFrame->lineNumber,);
        $this->assertEquals(__FILE__, $firstFrame->file);
        $this->assertEquals(static::class, $firstFrame->class);
        $this->assertEquals(explode('::', __METHOD__)[1], $firstFrame->method);
    }

    /** @test */
    public function it_can_get_add_the_arguments()
    {
        /** @var \Spatie\Backtrace\Frame $firstFrame */
        $firstFrame = Backtrace::create()->frames()[0];

        $this->assertNull($firstFrame->arguments);

        /** @var \Spatie\Backtrace\Frame $firstFrame */
        $firstFrame = Backtrace::create()
            ->withArguments()
            ->frames()[0];

        $this->assertIsArray($firstFrame->arguments);
    }

    /** @test */
    public function it_can_get_the_snippet_around_the_frame()
    {
        /** @var \Spatie\Backtrace\Frame $firstFrame */
        $firstFrame = Backtrace::create()->frames()[0];

        $snippet = $firstFrame->getSnippet(5);

        $this->assertStringContainsString('$firstFrame =', $snippet[__LINE__ - 4]);
        $this->assertCount(5, $snippet);
        $this->assertEquals(__LINE__ - 8, array_key_first($snippet));
    }

    /** @test */
    public function it_can_limit_the_amount_of_frames()
    {
        $frames = Backtrace::create()->limit(5)->frames();

        $this->assertCount(5, $frames);
    }
}
