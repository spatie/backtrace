<?php

namespace Spatie\Backtrace\Tests\TestClasses;

use Exception;
use Laravel\SerializableClosure\SerializableClosure;
use Throwable;

class LaravelSerializableClosureThrow
{
    public static function getThrowable(): Throwable
    {
        $closure = function () {
            throw new Exception('This is a test exception from a serialized closure');
        };

        $serialized = serialize(new SerializableClosure($closure));
        $closure = unserialize($serialized)->getClosure();

        try {
            $closure();
        } catch (Throwable $exception) {
            return $exception;
        }
    }
}
