<?php

namespace Spatie\Backtrace\Tests\TestClasses;

use Spatie\Backtrace\Arguments\ReducedArgument\ReducedArgument;
use Spatie\Backtrace\Arguments\ReducedArgument\ReducedArgumentContract;
use Spatie\Backtrace\Arguments\Reducers\ArgumentReducer;

class FakeArgumentReducer implements ArgumentReducer
{
    public function execute($argument): ReducedArgumentContract
    {
        return new ReducedArgument('FAKE', gettype($argument));
    }
}
