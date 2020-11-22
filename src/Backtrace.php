<?php

namespace Spatie\Backtrace;

use Closure;
use Throwable;

class Backtrace
{
    protected bool $withArguments = false;

    protected bool $withObject = false;

    protected ?string $applicationPath;

    protected int $offset = 0;

    protected int $limit = PHP_INT_MAX;

    protected ?Closure $startingFromFrameClosure = null;

    protected ?Throwable $throwable = null;

    public function create(): self
    {
        return new static();
    }

    public function createForThrowable(Throwable $throwable): self
    {
        return (new static())->forThrowable($throwable);
    }

    protected function forThrowable(Throwable $throwable): self
    {
        $this->throwable = $throwable;

        return $this;
    }

    public function withArguments(): self
    {
        $this->withArguments = true;

        return $this;
    }

    public function withObject(): self
    {
        $this->withObject = true;

        return $this;
    }

    public function applicationPath(string $applicationPath): self
    {
        $this->applicationPath = $applicationPath;

        return $this;
    }

    public function offset(int $offset): self
    {
        $this->offset = $offset;

        return $this;
    }

    public function limit(int $limit): self
    {
        $this->limit = $limit;

        return $this;
    }

    public function startingFromFrame(Closure $startingFromFrameClosure)
    {
        $this->startingFromFrameClosure = $startingFromFrameClosure;

        return $this;
    }

    public function frames(): array
    {
        $rawFrames = $this->getRawFrames();

        return $this->toFrameObjects($rawFrames);
    }

    protected function getRawFrames(): array
    {
        if ($this->throwable) {
            return $this->throwable->getTrace();
        }

        $options = null;

        if (! $this->withArguments) {
            $options = $options | DEBUG_BACKTRACE_IGNORE_ARGS;
        }

        if ($this->withObject()) {
            $options = $options | DEBUG_BACKTRACE_PROVIDE_OBJECT;
        }

        return debug_backtrace($options, $this->limit);
    }

    protected function toFrameObjects(array $rawFrames): array
    {
        $currentFile = $this->throwable ? $this->throwable->getFile() : '';
        $currentLine = $this->throwable ? $this->throwable->getLine() : 0;

        $rawFrames = array_map(fn (array $rawFrame) => new Frame(
            $currentFile,
            $currentLine,
            $rawFrame['args'] ?? [],
            $rawFrame['function'] ?? null,
            $rawFrame['class'] ?? null,
            $this->isApplicationFrame($currentFile)
        ), $rawFrames);

        $rawFrames[] = new Frame(
            $currentFile,
            $currentLine,
            [],
            '[top]'
        );


        $rawFrames = array_slice($rawFrames, $this->offset, $this->limit, true);

        if ($this->startingFromFrameClosure) {
            // TODO: implement
        }

        return $rawFrames;
    }

    protected function isApplicationFrame(string $frameFilename): bool
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
}
