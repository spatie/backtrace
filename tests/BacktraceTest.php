<?php

namespace Spatie\Backtrace\Tests;

use DateTime;
use PHPUnit\Framework\TestSuite;
use ReflectionClass;
use Spatie\Backtrace\Arguments\ArgumentReducers;
use Spatie\Backtrace\Backtrace;
use Spatie\Backtrace\Frame;
use Spatie\Backtrace\Tests\TestClasses\FakeArgumentReducer;
use Spatie\Backtrace\Tests\TestClasses\LaravelSerializableClosureCallThrow;
use Spatie\Backtrace\Tests\TestClasses\LaravelSerializableClosureThrow;
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
    public function it_can_get_add_the_arguments_with_a_backtrace()
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
    public function it_can_get_add_the_arguments_with_a_throwable()
    {
        $exception = TraceArguments::create()->exception(
            'Hello World',
            new DateTime(),
        );

        $this->assertIsArray(
            Backtrace::createForThrowable($exception)
                ->withArguments()
                ->frames()[1]
                ->arguments
        );
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
    public function it_can_get_add_the_object()
    {
        $firstFrame = Backtrace::create()->frames()[0];

        $this->assertNull($firstFrame->object);

        $firstFrame = Backtrace::create()
            ->withObject()
            ->frames()[0];

        $this->assertIsObject($firstFrame->object);
    }

    /** @test */
    public function it_can_disable_the_use_of_the_object_with_a_backtrace()
    {
        /** @return \Spatie\Backtrace\Frame[] */
        function createBackTraceWithoutObject(): array
        {
            return Backtrace::create()
                ->withObject(false)
                ->frames();
        }

        $frames = createBackTraceWithoutObject();

        $this->assertNull($frames[1]->object);
    }

    /** @test */
    public function it_can_disable_the_use_of_arguments_and_enable_the_use_of_the_object_with_a_backtrace()
    {
        /** @return \Spatie\Backtrace\Frame[] */
        function createBackTraceWithoutArgumentsAndWithObject(string $string): array
        {
            return Backtrace::create()
                ->withArguments(false)
                ->withObject()
                ->frames();
        }

        $frames = createBackTraceWithoutArgumentsAndWithObject('Hello World');

        $this->assertNull($frames[1]->arguments);
        $this->assertIsObject($frames[1]->object);
    }

    /** @test */
    public function it_can_enable_the_use_of_arguments_and_disable_the_use_of_the_object_with_a_backtrace()
    {
        /** @return \Spatie\Backtrace\Frame[] */
        function createBackTraceWithArgumentsAndWithoutObject(string $string): array
        {
            return Backtrace::create()
                ->withArguments()
                ->withObject(false)
                ->frames();
        }

        $frames = createBackTraceWithArgumentsAndWithoutObject('Hello World');

        $this->assertIsArray($frames[1]->arguments);
        $this->assertNull($frames[1]->object);
    }

    /** @test */
    public function it_can_enable_the_use_of_arguments_and_the_object_with_a_backtrace()
    {
        /** @return \Spatie\Backtrace\Frame[] */
        function createBackTraceWithArgumentsAndObject(string $string): array
        {
            return Backtrace::create()
                ->withArguments()
                ->withObject()
                ->frames();
        }

        $frames = createBackTraceWithArgumentsAndObject('Hello World');

        $this->assertIsArray($frames[1]->arguments);
        $this->assertIsObject($frames[1]->object);
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
        $this->assertEquals(__LINE__ - 5, $snippet[2]['line_number']);
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
    public function it_can_handle_a_laravel_serializable_closure_via_throwable()
    {
        if (version_compare(PHP_VERSION, '8.1', '<')) {
            $this->markTestSkipped('Enums are only supported in PHP 8.1+');
        }

        $throwable = LaravelSerializableClosureThrow::getThrowable();

        $frames = Backtrace::createForThrowable($throwable)->frames();

        $this->assertGreaterThan(10, count($frames));

        /** @var Frame $firstFrame */
        $firstFrame = $frames[0];

        $this->assertEquals(2, $firstFrame->lineNumber);

        if (version_compare(PHP_VERSION, '8.4', '>=')) {
            $this->assertEquals(
                <<<'EOT'
{closure:laravel-serializable-closure://function () {
            throw new \Exception('This is a test exception from a serialized closure');
        }:2}
EOT,
                $firstFrame->method
            );
        } else {
            $this->assertEquals('{closure}', $firstFrame->method);
        }

        $this->assertEquals(LaravelSerializableClosureThrow::class, $firstFrame->class);
        $this->assertTrue($firstFrame->applicationFrame);

        $firstFrameSnippet = $firstFrame->getSnippetAsString(5);

        $this->assertEquals(
            <<<'EOT'
1 laravel-serializable-closure://function () {
2     throw new \Exception('This is a test exception from a serialized closure');
3 }
EOT,
            $firstFrameSnippet
        );
    }

    /** @test */
    public function it_can_handle_a_laravel_serializable_closure_via_call_throwable()
    {
        if (version_compare(PHP_VERSION, '8.1', '<')) {
            $this->markTestSkipped('Enums are only supported in PHP 8.1+');
        }

        $throwable = LaravelSerializableClosureCallThrow::getThrowable();

        $frames = Backtrace::createForThrowable($throwable)->frames();

        /** @var Frame $firstFrame */
        $firstFrame = $frames[0];

        $this->assertEquals(29, $firstFrame->lineNumber);
        $this->assertEquals('throw', $firstFrame->method);
        $this->assertEquals(LaravelSerializableClosureCallThrow::class, $firstFrame->class);
        $this->assertEquals(realpath(__DIR__.'/../tests/TestClasses/LaravelSerializableClosureCallThrow.php'), $firstFrame->file);
        $this->assertTrue($firstFrame->applicationFrame);

        /** @var Frame $secondFrame */
        $secondFrame = $frames[1];

        $this->assertEquals(2, $secondFrame->lineNumber);

        if (version_compare(PHP_VERSION, '8.4', '>=')) {
            $this->assertEquals(
                <<<'EOT'
{closure:laravel-serializable-closure://function () {
            self::throw();
        }:2}
EOT,
                $secondFrame->method
            );
        } else {
            $this->assertEquals('{closure}', $secondFrame->method);
        }

        $this->assertEquals(LaravelSerializableClosureCallThrow::class, $secondFrame->class);
        $this->assertTrue($secondFrame->applicationFrame);

        $secondFrameSnippet = $secondFrame->getSnippetAsString(5);

        $this->assertEquals(
            <<<'EOT'
1 laravel-serializable-closure://function () {
2     self::throw();
3 }
EOT,
            $secondFrameSnippet
        );
    }

    /** @test */
    public function it_handles_laravels_artisan_file_as_a_vendor_frame()
    {
        $throwable = null;

        try {
            require __DIR__ . '/TestClasses/artisan';
        } catch (\Exception $e) {
            $throwable = $e;
        }

        $this->assertNotNull($throwable);

        $frames = Backtrace::createForThrowable($throwable)->frames();

        // Loop over frames and find the frame with the artisan file
        // It should be marked as a non-application frame
        $found = false;
        foreach ($frames as $frame) {
            if (strpos($frame->file, 'artisan') !== false) {
                $found = true;

                $this->assertFalse($frame->applicationFrame);
            }
        }
        $this->assertTrue($found, 'Did not find the artisan frame');
    }

    /** @test */
    public function it_can_get_the_index_of_the_first_application_frame()
    {
        $this->assertEquals(0, Backtrace::create()->firstApplicationFrameIndex());
    }

    /** @test */
    public function it_trims_file_path_if_application_path_set()
    {
        $className = (new ReflectionClass(self::class))->getShortName();

        $this->assertEquals(DIRECTORY_SEPARATOR.$className.'.php', Backtrace::create()->applicationPath(__DIR__)->trimFilePaths()->frames()[0]->trimmedFilePath);
    }

    /** @test */
    public function it_does_not_trim_file_path_if_no_application_path_set()
    {
        $this->assertNull(Backtrace::create()->trimFilePaths()->frames()[0]->trimmedFilePath);
    }
}
