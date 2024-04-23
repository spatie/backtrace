<?php

namespace Spatie\Backtrace\Tests;

use Spatie\Backtrace\CodeSnippets\CodeSnippet;
use Spatie\Backtrace\CodeSnippets\FileSnippetProvider;
use Spatie\Backtrace\CodeSnippets\NullSnippetProvider;
use Spatie\Snapshots\MatchesSnapshots;

class CodeSnippetTest extends TestCase
{
    use MatchesSnapshots;

    /** @test */
    public function it_can_get_a_file_code_snippet_as_a_string()
    {
        if ($this->runningOnWindows()) {
            $this->markAsSucceeded();

            return;
        }

        $snippetString = (new CodeSnippet())
            ->snippetLineCount(15)
            ->surroundingLine(10)
            ->getAsString(new FileSnippetProvider(__DIR__.'/TestClasses/TestClass.php'));

        $this->assertMatchesTextSnapshot($snippetString);
    }

    /** @test */
    public function it_can_get_a_null_code_snippet_as_a_string()
    {
        if ($this->runningOnWindows()) {
            $this->markAsSucceeded();

            return;
        }

        $snippetString = (new CodeSnippet())
            ->snippetLineCount(15)
            ->surroundingLine(10)
            ->getAsString(new NullSnippetProvider());

        $this->assertMatchesTextSnapshot($snippetString);
    }

    protected function runningOnWindows(): string
    {
        return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
    }

    protected function markAsSucceeded()
    {
        $this->assertTrue(true);
    }
}
