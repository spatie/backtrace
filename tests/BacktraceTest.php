<?php

namespace Spatie\Backtrace\Tests;

use PHPUnit\Framework\TestSuite;
use Spatie\Backtrace\Backtrace;
use Spatie\Backtrace\Frame;
use Spatie\Backtrace\Tests\TestClasses\ThrowAndReturnExceptionAction;

class BacktraceTest extends TestCase
{
    /** @test */
    public function it_can_create_a_backtrace()
    {
        $frames = Backtrace::create()->frames();

        $this->assertGreaterThan(10, count($frames));

        /** @var \Spatie\Backtrace\Frame $firstFrame */
        $firstFrame = $frames[0];

        $this->assertEquals(__LINE__ - 7, $firstFrame->lineNumber);
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
    public function it_can_get_the_snippet_properties()
    {
        /** @var \Spatie\Backtrace\Frame $firstFrame */
        $firstFrame = Backtrace::create()->frames()[0];

        $snippet = $firstFrame->getSnippetProperties(5);

        $this->assertStringContainsString('$firstFrame =', $snippet[2]['text']);
        $this->assertEquals(61, $snippet[2]['line_number']);
    }

    /** @test */
    public function it_can_limit_the_amount_of_frames()
    {
        $frames = Backtrace::create()->limit(5)->frames();

        $this->assertCount(5, $frames);
    }

    /** @test */
    public function it_can_start_at_a_specific_frame()
    {
        $firstFrame = Backtrace::create()
            ->startingFromFrame(function (Frame $frame) {
                return $frame->class = TestSuite::class;
            })
            ->frames()[0];

        $this->assertEquals(TestSuite::class, $firstFrame->class);
    }

    /** @test */
    public function it_can_start_at_a_specific_frame_and_limit_the_number_of_frames()
    {
        $frames = Backtrace::create()
            ->startingFromFrame(function (Frame $frame) {
                return $frame->class = TestSuite::class;
            })
            ->limit(2)
            ->frames();

        $this->assertEquals(TestSuite::class, $frames[0]->class);
        $this->assertCount(2, $frames);
    }

    /** @test */
    public function it_can_skip_frames()
    {
        /** @var Frame $firstFrame */
        $firstFrame = Backtrace::create()->offset(1)->frames()[0];

        $this->assertEquals('runTest', $firstFrame->method);
    }

    /** @test */
    public function it_can_get_a_backtrace_from_a_throwable()
    {
        $throwable = ThrowAndReturnExceptionAction::getThrowable();

        $frames = Backtrace::createForThrowable($throwable)->frames();

        $this->assertGreaterThan(10, count($frames));

        /** @var Frame $firstFrame */
        $firstFrame = $frames[0];

        $this->assertEquals(13, $firstFrame->lineNumber);
        $this->assertEquals(ThrowAndReturnExceptionAction::class, $firstFrame->class);
        $this->assertEquals('getThrowable', $firstFrame->method);
    }

    /** @test */
    public function it_can_get_the_index_of_the_first_application_frame()
    {
        $this->assertEquals(0, Backtrace::create()->firstApplicationFrameIndex());
    }
}
