<?php

namespace Spatie\Backtrace;

class Frame
{
    public $file;

    public $lineNumber;

    public $arguments = null;

    public $applicationFrame;

    public $method;

    public $class;

    public function __construct(
        string $file,
        int $lineNumber,
        ?array $arguments,
        string $method = null,
        string $class = null,
        bool $isApplicationFrame = false
    ) {
        $this->file = $file;

        $this->lineNumber = $lineNumber;

        $this->arguments = $arguments;

        $this->method = $method;

        $this->class = $class;

        $this->applicationFrame = $isApplicationFrame;
    }

    public function getSnippet(int $lineCount): array
    {
        return (new CodeSnippet())
            ->surroundingLine($this->lineNumber)
            ->snippetLineCount($lineCount)
            ->get($this->file);
    }
}
