<?php

namespace Spatie\Backtrace\Tests;

use PHPUnit\Framework\TestCase as PHPUnit;

abstract class TestCase extends PHPUnit
{
    public static function makePathsRelative(string $text): string
    {
        return str_replace(dirname(__DIR__, 1), '', $text);
    }
}
