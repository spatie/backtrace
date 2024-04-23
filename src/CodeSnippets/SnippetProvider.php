<?php

namespace Spatie\Backtrace\CodeSnippets;

use SplFileObject;

interface SnippetProvider
{
    public function numberOfLines(): int;

    public function getLine(int $lineNumber = null): string;

    public function getNextLine(): string;
}
