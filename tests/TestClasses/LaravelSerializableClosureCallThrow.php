<?php

namespace Spatie\Backtrace\Tests\TestClasses;

use Exception;
use Laravel\SerializableClosure\SerializableClosure;
use Throwable;

class LaravelSerializableClosureCallThrow
{
    public static function getThrowable(): Throwable
    {
        $closure = function () {
            self::throw();
        };

        $serialized = serialize(new SerializableClosure($closure));
        $closure = unserialize($serialized)->getClosure();

        try {
            $closure();
        } catch (Throwable $exception) {
            return $exception;
        }
    }

    public static function throw()
    {
        throw new Exception('This is a test exception from a serialized closure');
    }
}
