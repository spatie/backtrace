<?php

namespace Spatie\Backtrace\Tests\TestClasses;

use Closure;
use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use Exception;
use SensitiveParameter;
use Spatie\Backtrace\Backtrace;
use Spatie\Backtrace\Frame;
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

    public function withoutArguments(): Frame
    {
        return $this->getTraceFrame();
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
    ): Frame {
        return $this->getTraceFrame();
    }

    public function withEnums(
        FakeUnitEnum $unitEnum,
        FakeStringBackedEnum $stringBackedEnum,
        FakeIntBackedEnum $intBackedEnum
    ): Frame {
        return $this->getTraceFrame();
    }

    public function withArray(
        array $array
    ) {
        return $this->getTraceFrame();
    }

    public function withDefaults(
        string $stringA,
        string $stringB = 'B'
    ): Frame {
        return $this->getTraceFrame();
    }

    public function withVariadicArgument(
        string $base,
        string ...$strings
    ): Frame {
        return $this->getTraceFrame();
    }

    public function withDefaultAndVardiadicArgument(
        string $base = 'base',
        string ...$strings
    ): Frame {
        return $this->getTraceFrame();
    }

    public function withClosure(
        Closure $closure
    ): Frame {
        return $this->getTraceFrame();
    }

    public function withDate(
        DateTime $dateTime,
        DateTimeImmutable $dateTimeImmutable
    ): Frame {
        return $this->getTraceFrame();
    }

    public function withTimeZone(
        DateTimeZone $dateTimeZone
    ): Frame {
        return $this->getTraceFrame();
    }

    public function withSensitiveParameter(
        #[SensitiveParameter]
        string $sensitive
    ): Frame {
        return $this->getTraceFrame();
    }

    public function withCombination(
        string $simple,
        DateTimeZone $object,
        int ...$variadic
    ): Frame {
        return $this->getTraceFrame();
    }

    public function withCalledClosure(): Frame
    {
        $closure = function (
            $simple,
            $object,
            ...$variadic
        ) {
            return $this->getTraceFrame();
        };

        return $closure('string', new DateTimeZone('Europe/Brussels'), 42, 69);
    }

    public function withStdClass(stdClass $class): Frame
    {
        return $this->getTraceFrame();
    }

    public function withNotEnoughArgumentsProvided(): Frame
    {
        try {
            $this->withCombination('provided');
        } catch (Throwable $exception) {
            return Backtrace::createForThrowable($exception)->withArguments()->frames()[0];
        }
    }

    public function withStringable(
        \Stringable $stringable
    ): Frame {
        return $this->getTraceFrame();
    }

    public function exception(
        string $string,
        DateTime $dateTime
    ): Exception {
        return new Exception('Some exception');
    }

    protected function getTraceFrame(): Frame
    {
        return Backtrace::create()->withArguments()->frames()[1];
    }
}
