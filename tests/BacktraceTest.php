<?php

namespace Spatie\Backtrace\Tests;

use DateTime;
use PHPUnit\Framework\TestSuite;
use Spatie\Backtrace\Arguments\ArgumentReducers;
use Spatie\Backtrace\Backtrace;
use Spatie\Backtrace\Frame;
use Spatie\Backtrace\Tests\TestClasses\FakeArgumentReducer;
use Spatie\Backtrace\Tests\TestClasses\ThrowAndReturnExceptionAction;
use Spatie\Backtrace\Tests\TestClasses\TraceArguments;

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
    public function it_can_disable_the_use_of_arguments_with_a_backtrace()
    {
        function createBackTraceWithoutArguments(string $string): array
        {
            return Backtrace::create()
                ->withArguments(false)
                ->frames();
        }

        $frames = createBackTraceWithoutArguments('Hello World');

        $this->assertNull($frames[1]->arguments);
    }

    /** @test */
    public function it_can_disable_the_use_of_arguments_with_a_throwable()
    {
        $exception = TraceArguments::create()->exception(
            'Hello World',
            new DateTime(),
        );

        $this->assertNull(
            Backtrace::createForThrowable($exception)
            ->withArguments(false)
            ->frames()[1]
            ->arguments
        );
    }

    /** @test */
    public function it_can_get_add_the_arguments_reduced()
    {
        function createBackTrace(string $test, bool $withArguments): Frame
        {
            return Backtrace::create()
                ->withArguments($withArguments)
                ->reduceArguments()
                ->frames()[1];
        }

        $frame = createBackTrace('test', false);

        $this->assertNull($frame->arguments);

        $frame = createBackTrace('test', true);

        $this->assertEquals([
            "name" => "test",
            "value" => "test",
            "original_type" => 'string',
            "passed_by_reference" => false,
            "is_variadic" => false,
            "truncated" => false,
        ], $frame->arguments[0]);
    }

    /** @test */
    public function it_can_manually_define_a_reducer_using_an_array()
    {
        function createBackTraceWithReducerFromArray(string $test): Frame
        {
            return Backtrace::create()
                ->withArguments()
                ->reduceArguments([new FakeArgumentReducer()])
                ->frames()[1];
        }

        $frame = createBackTraceWithReducerFromArray('test', true);

        $this->assertEquals('FAKE', $frame->arguments[0]['value']);
    }

    /** @test */
    public function it_can_manually_define_a_reducer_using_an_argument_reducers_object()
    {
        function createBackTraceWithReducerFromObject(string $test): Frame
        {
            return Backtrace::create()
                ->withArguments()
                ->reduceArguments(ArgumentReducers::default([new FakeArgumentReducer()]))
                ->frames()[1];
        }

        $frame = createBackTraceWithReducerFromObject('test', true);

        $this->assertEquals('FAKE', $frame->arguments[0]['value']);
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
        $this->assertEquals(154, $snippet[2]['line_number']);
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
