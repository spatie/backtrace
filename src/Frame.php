<?php

namespace Spatie\Backtrace;

class Frame
{
    public string $file;

    public int $lineNumber;

    public bool $isApplicationFrame;

    public ?string $method;

    public ?string $class;

    public function __construct(
        string $file,
        int $lineNumber,
        string $method = null,
        string $class = null,
        bool $isApplicationFrame = false
    ) {
        $this->file = $file;

        $this->lineNumber = $lineNumber;

        $this->method = $method;

        $this->class = $class;

        $this->isApplicationFrame = $isApplicationFrame;
    }

    public function toArray(): array
    {
        return [
            'line_number' => $this->lineNumber,
            'method' => $this->method,
            'class' => $this->class,
            'file' => $this->file,
            'is_application_frame' => $this->isApplicationFrame,
        ];
    }

    public function getSnippet(int $lineCount): array
    {
        return (new CodeSnippet())
            ->surroundingLine($this->lineNumber)
            ->snippetLineCount($lineCount)
            ->get($this->file);
    }
}
