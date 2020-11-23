<?php

namespace Spatie\Backtrace\Tests\TestClasses;

use Exception;
use Throwable;

class ThrowAndReturnExceptionAction
{
    public static function getThrowable(): Throwable
    {
        try {
            throw new Exception();
        } catch (Exception $exception) {
            return $exception;
        }
    }
}
