<?php

namespace Spatie\Backtrace\Tests;

use Spatie\Backtrace\CodeSnippet;
use Spatie\Snapshots\MatchesSnapshots;

class CodeSnippetTest extends TestCase
{
    use MatchesSnapshots;

    /** @test */
    public function it_can_get_the_code_snippet_as_a_string()
    {
        $snippetString = (new CodeSnippet())
            ->snippetLineCount(15)
            ->surroundingLine(10)
            ->getAsString(__DIR__ . '/TestClasses/TestClass.php');

        $this->assertMatchesTextSnapshot($snippetString);
    }
}
