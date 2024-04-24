<?php

namespace Spatie\Backtrace\Tests\CodeSnippets;

use Spatie\Backtrace\CodeSnippets\LaravelSerializableClosureSnippetProvider;
use Spatie\Backtrace\Tests\TestCase;

class LaravelSerializableClosureSnippetProviderTest extends TestCase
{
    /** @test */
    public function it_can_get_a_snippet_for_a_serializable_closure_and_formats_it()
    {
        $closure = <<<'EOT'
laravel-serializable-closure://function () {
            // We do quite a lot over here
            // Just to make the closure bigger
            // for example, lets calculate the pythagorean theorem

            $a = 3;
            $b = 4;

            $c = sqrt($a * $a + $b * $b);

            // Lets add an if with indentation
            if ($c > 5) {
                echo 'The hypotenuse is greater than 5';
            }

            // That should be enough
            // Let's throw an exception

            throw new \Exception('This is a test exception from a serialized closure');
        }
EOT;

        $provider = new LaravelSerializableClosureSnippetProvider($closure);

        $output = '';

        for ($i = 1; $i <= $provider->numberOfLines(); $i++) {
            $output .= $provider->getLine($i).PHP_EOL;
        }

        $expected = <<<'EOT'
laravel-serializable-closure://function () {
    // We do quite a lot over here
    // Just to make the closure bigger
    // for example, lets calculate the pythagorean theorem

    $a = 3;
    $b = 4;

    $c = sqrt($a * $a + $b * $b);

    // Lets add an if with indentation
    if ($c > 5) {
        echo 'The hypotenuse is greater than 5';
    }

    // That should be enough
    // Let's throw an exception

    throw new \Exception('This is a test exception from a serialized closure');
}

EOT;

        $this->assertEquals($expected, $output);
    }

    /** @test */
    public function it_can_get_a_snippet_for_an_empty_closure()
    {
        $closure = <<<'EOT'
laravel-serializable-closure://function () {
        }
EOT;

        $provider = new LaravelSerializableClosureSnippetProvider($closure);

        $output = '';

        for ($i = 1; $i <= $provider->numberOfLines(); $i++) {
            $output .= $provider->getLine($i).PHP_EOL;
        }

        $expected = <<<'EOT'
laravel-serializable-closure://function () {
}

EOT;

        $this->assertEquals($expected, $output);
    }
}
