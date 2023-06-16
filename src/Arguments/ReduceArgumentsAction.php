<?php

namespace Spatie\Backtrace\Arguments;

use ReflectionException;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionParameter;
use Spatie\Backtrace\Arguments\ReducedArgument\VariadicReducedArgument;
use Spatie\Backtrace\Frame as SpatieFrame;
use Throwable;

class ReduceArgumentsAction
{
    /** @var \Spatie\Backtrace\Arguments\ArgumentReducers */
    protected $argumentReducers;

    /** @var ReduceArgumentPayloadAction */
    protected $reduceArgumentPayloadAction;

    public function __construct(
        $argumentReducers
    ) {
        $this->argumentReducers = $argumentReducers;
        $this->reduceArgumentPayloadAction = new ReduceArgumentPayloadAction($argumentReducers);
    }

    public function execute(SpatieFrame $frame): ?array
    {
        try {
            if ($frame->arguments === null) {
                return null;
            }

            $parameters = $this->getParameters($frame);

            if ($parameters === null) {
                $arguments = [];

                foreach ($frame->arguments as $index => $argument) {
                    $arguments[$index] = ProvidedArgument::fromNonReflectableParameter($index)
                        ->setReducedArgument($this->reduceArgumentPayloadAction->reduce($argument))
                        ->toArray();
                }

                return $arguments;
            }

            $arguments = array_map(
                function ($argument) {
                    return $this->reduceArgumentPayloadAction->reduce($argument);
                },
                $frame->arguments,
            );

            $argumentsCount = count($arguments);
            $hasVariadicParameter = false;

            foreach ($parameters as $index => $parameter) {
                if ($index + 1 > $argumentsCount) {
                    $parameter->defaultValueUsed();
                } elseif ($parameter->isVariadic) {
                    $parameter->setReducedArgument(new VariadicReducedArgument(array_slice($arguments, $index)));

                    $hasVariadicParameter = true;
                } else {
                    $parameter->setReducedArgument($arguments[$index]);
                }

                $parameters[$index] = $parameter->toArray();
            }

            if ($this->moreArgumentsProvidedThanParameters($arguments, $parameters, $hasVariadicParameter)) {
                for ($i = count($parameters); $i < count($arguments); $i++) {
                    $parameters[$i] = ProvidedArgument::fromNonReflectableParameter(count($parameters))
                        ->setReducedArgument($arguments[$i])
                        ->toArray();
                }
            }

            return $parameters;
        } catch (Throwable $e) {
            return null;
        }
    }

    /** @return null|Array<\Spatie\Backtrace\Arguments\ProvidedArgument> */
    protected function getParameters(SpatieFrame $frame): ?array
    {
        try {
            $reflection = null !== $frame->class
                ? new ReflectionMethod($frame->class, $frame->method)
                : new ReflectionFunction($frame->method);
        } catch (ReflectionException $e) {
            return null;
        }

        return array_map(
            function (ReflectionParameter $reflectionParameter) use ($frame) {
                return ProvidedArgument::fromReflectionParameter($reflectionParameter);
            },
            $reflection->getParameters(),
        );
    }

    protected function moreArgumentsProvidedThanParameters(
        array $arguments,
        array $parameters,
        bool $hasVariadicParameter
    ): bool {
        return count($arguments) > count($parameters) && ! $hasVariadicParameter;
    }
}
