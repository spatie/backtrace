<?php

namespace Spatie\Backtrace;

class Frame
{
    public string $file;

    public int $lineNumber;

    public ?array $arguments = null;

    public bool $isApplicationFrame;

    public ?string $method;

    public ?string $class;

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

        $this->isApplicationFrame = $isApplicationFrame;
    }

    public function getSnippet(int $lineCount): array
    {
        return (new CodeSnippet())
            ->surroundingLine($this->lineNumber)
            ->snippetLineCount($lineCount)
            ->get($this->file);
    }
}
