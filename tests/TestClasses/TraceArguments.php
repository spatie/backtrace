<?php

namespace Spatie\Backtrace\Tests\TestClasses;

use Closure;
use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use Exception;
use SensitiveParameter;
use Spatie\Backtrace\Backtrace;
use stdClass;
use Throwable;

class TraceArguments
{
    public static function create(): self
    {
        return new self();
    }

    public function withoutArgumentsEnabledInTrace()
    {
        return Backtrace::create()->frames()[0];
    }

    public function withoutArguments(): array
    {
        return $this->getTraceFrames();
    }

    public function withSimpleArguments(
        bool $true,
        bool $false,
        string $emptyString,
        string $string,
        int $int,
        int $intMax,
        float $float,
        float $floatNan,
        float $floatInfinity,
        ?string $null
    ): array {
        return $this->getTraceFrames();
    }

    public function withEnums(
        FakeUnitEnum $unitEnum,
        FakeStringBackedEnum $stringBackedEnum,
        FakeIntBackedEnum $intBackedEnum
    ): array {
        return $this->getTraceFrames();
    }

    public function withArray(
        array $array
    ) {
        return $this->getTraceFrames();
    }

    public function withDefaults(
        string $stringA,
        string $stringB = 'B'
    ): array {
        return $this->getTraceFrames();
    }

    public function withVariadicArgument(
        string $base,
        string ...$strings
    ): array {
        return $this->getTraceFrames();
    }

    public function withDefaultAndVardiadicArgument(
        string $base = 'base',
        string ...$strings
    ): array {
        return $this->getTraceFrames();
    }

    public function withClosure(
        Closure $closure
    ): array {
        return $this->getTraceFrames();
    }

    public function withDate(
        DateTime $dateTime,
        DateTimeImmutable $dateTimeImmutable
    ): array {
        return $this->getTraceFrames();
    }

    public function withTimeZone(
        DateTimeZone $dateTimeZone
    ): array {
        return $this->getTraceFrames();
    }

    public function withSensitiveParameter(
        #[SensitiveParameter]
        string $sensitive
    ): array {
        return $this->getTraceFrames();
    }

    public function withCombination(
        string $simple,
        DateTimeZone $object,
        int ...$variadic
    ): array {
        return $this->getTraceFrames();
    }

    public function withCalledClosure(): array
    {
        $closure = function (
            $simple,
            $object,
            ...$variadic
        ) {
            return $this->getTraceFrames();
        };

        return $closure('string', new DateTimeZone('Europe/Brussels'), 42, 69);
    }

    public function withStdClass(stdClass $class): array
    {
        return $this->getTraceFrames();
    }

    public function withNotEnoughArgumentsProvided(): array
    {
        try {
            $this->withCombination('provided');
        } catch (Throwable $exception) {
            return Backtrace::createForThrowable($exception)->withArguments()->frames();
        }
    }

    public function withStringable(
        \Stringable $stringable
    ): array {
        return $this->getTraceFrames();
    }

    public function exception(
        string $string,
        DateTime $dateTime
    ): Exception {
        $ignoreArgsOriginalValue = ini_get('zend.exception_ignore_args');

        ini_set('zend.exception_ignore_args', false);

        $exception = new Exception('Some exception');

        ini_set('zend.exception_ignore_args', $ignoreArgsOriginalValue);

        return $exception;
    }

    protected function getTraceFrames(): array
    {
        return Backtrace::create()
            ->withArguments()
            ->frames();
    }
}
