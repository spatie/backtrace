<?php

namespace Spatie\Backtrace;

use Throwable;

class Backtrace
{
    /** @var \Spatie\Backtrace\Frame[] */
    protected array $frames;

    protected ?string $applicationPath;

    public static function createForThrowable(Throwable $throwable, ?string $applicationPath = null): self
    {
        return new static($throwable->getTrace(), $applicationPath, $throwable->getFile(), $throwable->getLine());
    }

    public static function create(?string $applicationPath = null): self
    {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS & ~DEBUG_BACKTRACE_PROVIDE_OBJECT);

        return new static($backtrace, $applicationPath);
    }

    public function __construct(
        array $backtrace,
        ?string $applicationPath = null,
        string $topmostFile = null,
        string $topmostLine = null
    ) {
        $this->applicationPath = $applicationPath;

        $currentFile = $topmostFile ?? '';
        $currentLine = $topmostLine ?? 0;

        foreach ($backtrace as $rawFrame) {
            $this->frames[] = new Frame(
                $currentFile,
                $currentLine,
                $rawFrame['function'] ?? null,
                $rawFrame['class'] ?? null,
                $this->frameFileFromApplication($currentFile)
            );


            $currentFile = $rawFrame['file'] ?? 'unknown';
            $currentLine = $rawFrame['line'] ?? 0;
        }

        $this->frames[] = new Frame(
            $currentFile,
            $currentLine,
            '[top]'
        );
    }

    protected function frameFileFromApplication(string $frameFilename): bool
    {
        $relativeFile = str_replace('\\', DIRECTORY_SEPARATOR, $frameFilename);

        if (! empty($this->applicationPath)) {
            $relativeFile = array_reverse(explode($this->applicationPath ?? '', $frameFilename, 2))[0];
        }

        if (strpos($relativeFile, DIRECTORY_SEPARATOR . 'vendor') === 0) {
            return false;
        }

        return true;
    }

    public function toArray(): array
    {
        return array_map(function (Frame $frame) {
            return $frame->toArray();
        }, $this->frames);
    }
}
