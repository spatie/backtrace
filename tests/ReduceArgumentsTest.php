<?php

namespace Spatie\Backtrace\Tests;

use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use SensitiveParameterValue;
use Spatie\Backtrace\Arguments\ArgumentReducers;
use Spatie\Backtrace\Arguments\ProvidedArgument;
use Spatie\Backtrace\Arguments\ReduceArgumentsAction;
use Spatie\Backtrace\Tests\TestClasses\FakeIntBackedEnum;
use Spatie\Backtrace\Tests\TestClasses\FakeStringBackedEnum;
use Spatie\Backtrace\Tests\TestClasses\FakeUnitEnum;
use Spatie\Backtrace\Tests\TestClasses\TraceArguments;
use stdClass;
use Stringable;

class ReduceArgumentsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        ini_set('zend.exception_ignore_args', 0); // Enabled on GH actions
    }

    /** @test */
    public function it_can_reduce_arguments_with_arguments_disabled_in_the_trace()
    {
        $frame = TraceArguments::create()->withoutArgumentsEnabledInTrace();

        $this->assertNull(
            (new ReduceArgumentsAction(ArgumentReducers::default()))->execute(
                $frame->class,
                $frame->method,
                $frame->arguments
            )
        );
    }

    /**
     * @test
     * @dataProvider reduceableFramesDataSet
     */
    public function it_can_reduce_frames_with_arguments(
        array $frames,
        array $expected
    ) {
        $reduced = (new ReduceArgumentsAction(ArgumentReducers::default()))->execute(
            $frames[1]->class,
            $frames[1]->method,
            $frames[2]->arguments // The package shifts these arguments automatically, we're calling this action manually
        );

        $this->assertEquals(
            array_map(function (ProvidedArgument $argument) {
                return $argument->toArray();
            }, $expected),
            $reduced
        );
    }

    public static function reduceableFramesDataSet()
    {
        yield 'without arguments' => [
            TraceArguments::create()->withoutArguments(),
            [],
        ];

        yield 'simple arguments' => [
            TraceArguments::create()->withSimpleArguments(
                true,
                false,
                '',
                'Hello World',
                42,
                PHP_INT_MAX,
                3.14,
                10,
                INF,
                null,
            ), [
                ProvidedArgumentFactory::create('true')->reducedValue(true)->originalType('bool')->get(),
                ProvidedArgumentFactory::create('false')->reducedValue(false)->originalType('bool')->get(),
                ProvidedArgumentFactory::create('emptyString')->reducedValue('')->originalType('string')->get(),
                ProvidedArgumentFactory::create('string')->reducedValue('Hello World')->originalType('string')->get(),
                ProvidedArgumentFactory::create('int')->reducedValue(42)->originalType('int')->get(),
                ProvidedArgumentFactory::create('intMax')->reducedValue(PHP_INT_MAX)->originalType('int')->get(),
                ProvidedArgumentFactory::create('float')->reducedValue(3.14)->originalType('float')->get(),
                ProvidedArgumentFactory::create('floatNan')->reducedValue(10)->originalType('float')->get(),
                ProvidedArgumentFactory::create('floatInfinity')->reducedValue(INF)->originalType('float')->get(),
                ProvidedArgumentFactory::create('null')->reducedValue(null)->originalType('null')->get(),
            ],
        ];

        yield 'with array of simple values' => [
            TraceArguments::create()->withArray(
                ['a', 'b', 'c']
            ), [
                ProvidedArgumentFactory::create('array')->reducedValue(['a', 'b', 'c'])->originalType('array')->get(),
            ],
        ];

        yield 'with array of complex values' => [
            TraceArguments::create()->withArray(
                [
                    new DateTimeZone('Europe/Brussels'),
                    new DateTimeZone('Europe/Amsterdam'),
                ]
            ), [
                ProvidedArgumentFactory::create('array')
                    ->reducedValue(['object (DateTimeZone)', 'object (DateTimeZone)'])
                    ->originalType('array')
                    ->get(),
            ],
        ];

        yield 'with array which gets truncated' => [
            TraceArguments::create()->withArray(
                array_fill(0, 100, 'a')
            ), [
                ProvidedArgumentFactory::create('array')
                    ->truncated()
                    ->reducedValue(array_fill(0, 25, 'a'))
                    ->originalType('array')
                    ->get(),
            ],
        ];

        yield 'with array of sub arrays which get reduced simply' => [
            TraceArguments::create()->withArray(
                [
                    'string',
                    new DateTimeZone('Europe/Brussels'),
                    [
                        'string',
                        new DateTimeZone('Europe/Brussels'),
                        ['a', 'b', 'c'],
                    ],
                ]
            ), [
                ProvidedArgumentFactory::create('array')
                    ->reducedValue([
                        'string',
                        'object (DateTimeZone)',
                        'array (size=3)',
                    ])
                    ->originalType('array')
                    ->get(),
            ],
        ];

        yield 'with defaults' => [
            TraceArguments::create()->withDefaults(
                'A',
            ), [
                ProvidedArgumentFactory::create('stringA')
                    ->reducedValue('A')
                    ->originalType('string')
                    ->get(),
                ProvidedArgumentFactory::create('stringB')
                    ->defaultValue('B')
                    ->defaultValueUsed()
                    ->originalType('string')
                    ->get(),
            ],
        ];

        yield 'with defaults and provided other value then default' => [
            TraceArguments::create()->withDefaults('A', 'notB'), [
                ProvidedArgumentFactory::create('stringA')
                    ->reducedValue('A')
                    ->originalType('string')
                    ->get(),
                ProvidedArgumentFactory::create('stringB')
                    ->defaultValue('B')
                    ->reducedValue('notB')
                    ->originalType('string')
                    ->get(),
            ],
        ];

        yield 'with variadic argument (not provided)' => [
            TraceArguments::create()->withVariadicArgument('base'),
            [
                ProvidedArgumentFactory::create('base')
                    ->reducedValue('base')
                    ->originalType('string')
                    ->get(),
                ProvidedArgumentFactory::create('strings')
                    ->isVariadic()
                    ->defaultValue([])
                    ->defaultValueUsed()
                    ->reducedValue(null)
                    ->originalType('array')
                    ->get(),
            ],
        ];

        yield 'with variadic argument (one provided)' => [
            TraceArguments::create()->withVariadicArgument('base', 'string'),
            [
                ProvidedArgumentFactory::create('base')
                    ->reducedValue('base')
                    ->originalType('string')
                    ->get(),
                ProvidedArgumentFactory::create('strings')
                    ->isVariadic()
                    ->defaultValue([])
                    ->reducedValue(['string'])
                    ->originalType('array')
                    ->get(),
            ],
        ];


        yield 'with variadic argument (multiple provided)' => [
            TraceArguments::create()->withVariadicArgument('base', 'string', 'another', 'one'),
            [
                ProvidedArgumentFactory::create('base')
                    ->reducedValue('base')
                    ->originalType('string')
                    ->get(),
                ProvidedArgumentFactory::create('strings')
                    ->isVariadic()
                    ->defaultValue([])
                    ->reducedValue(['string', 'another', 'one'])
                    ->originalType('array')
                    ->get(),
            ],
        ];

        yield 'with default + variadic argument (default + variadic not provided)' => [
            TraceArguments::create()->withDefaultAndVardiadicArgument(),
            [
                ProvidedArgumentFactory::create('base')
                    ->defaultValue('base')
                    ->reducedValue('base')
                    ->defaultValueUsed()
                    ->originalType('string')
                    ->get(),
                ProvidedArgumentFactory::create('strings')
                    ->isVariadic()
                    ->defaultValue([])
                    ->defaultValueUsed()
                    ->reducedValue(null)
                    ->originalType('array')
                    ->get(),
            ],
        ];

        yield 'with default + variadic argument (variadic not provided)' => [
            TraceArguments::create()->withDefaultAndVardiadicArgument('base'),
            [
                ProvidedArgumentFactory::create('base')
                    ->defaultValue('base')
                    ->originalType('string')
                    ->reducedValue('base')
                    ->get(),
                ProvidedArgumentFactory::create('strings')
                    ->isVariadic()
                    ->defaultValue([])
                    ->defaultValueUsed()
                    ->reducedValue(null)
                    ->originalType('array')
                    ->get(),
            ],
        ];

        yield 'with default + variadic argument (one provided)' => [
            TraceArguments::create()->withDefaultAndVardiadicArgument('base', 'string'),
            [
                ProvidedArgumentFactory::create('base')
                    ->defaultValue('base')
                    ->originalType('string')
                    ->reducedValue('base')
                    ->get(),
                ProvidedArgumentFactory::create('strings')
                    ->isVariadic()
                    ->defaultValue([])
                    ->reducedValue(['string'])
                    ->originalType('array')
                    ->get(),
            ],
        ];

        yield 'with default + variadic argument (multiple provided)' => [
            TraceArguments::create()->withDefaultAndVardiadicArgument('base', 'string', 'another', 'one'),
            [
                ProvidedArgumentFactory::create('base')
                    ->defaultValue('base')
                    ->originalType('string')
                    ->reducedValue('base')
                    ->get(),
                ProvidedArgumentFactory::create('strings')
                    ->isVariadic()
                    ->defaultValue([])
                    ->reducedValue(['string', 'another', 'one'])
                    ->originalType('array')
                    ->get(),
            ],
        ];

        yield 'with closure' => [
            TraceArguments::create()->withClosure(
                function () {
                    return 'Hello World';
                },
            ), [
                ProvidedArgumentFactory::create('closure')
                    ->reducedValue(__FILE__.':'.(__LINE__ - 5).'-'.(__LINE__ - 3))
                    ->originalType('Closure')
                    ->get(),
            ],
        ];

        yield 'with date' => [
            TraceArguments::create()->withDate(
                new DateTime('2020-05-16 14:00:00', new DateTimeZone('Europe/Brussels')),
                new DateTimeImmutable('2020-05-16 14:00:00', new DateTimeZone('Europe/Brussels')),
            ), [
                ProvidedArgumentFactory::create('dateTime')
                    ->reducedValue('16 May 2020 14:00:00 Europe/Brussels')
                    ->originalType(DateTime::class)
                    ->get(),
                ProvidedArgumentFactory::create('dateTimeImmutable')
                    ->reducedValue('16 May 2020 14:00:00 Europe/Brussels')
                    ->originalType(DateTimeImmutable::class)
                    ->get(),
            ],
        ];

        yield 'with timezone' => [
            TraceArguments::create()->withTimeZone(
                new DateTimeZone('Europe/Brussels'),
            ), [
                ProvidedArgumentFactory::create('dateTimeZone')
                    ->reducedValue('Europe/Brussels')
                    ->originalType(DateTimeZone::class)
                    ->get(),
            ],
        ];

        yield 'with sensitive parameter' => [
            TraceArguments::create()->withSensitiveParameter('secret'),
            [
                ProvidedArgumentFactory::create('sensitive')
                    ->reducedValue(version_compare(PHP_VERSION, '8.2', '>=')
                        ? 'SensitiveParameterValue(string)'
                        : 'secret')
                    ->originalType(version_compare(PHP_VERSION, '8.2', '>=')
                        ? SensitiveParameterValue::class
                        : 'string')
                    ->get(),
            ],
        ];

        yield 'with called closure (no reflection possible)' => [
            TraceArguments::create()->withCalledClosure(), [
                ProvidedArgumentFactory::create('arg0')
                    ->reducedValue('string')
                    ->originalType('string')
                    ->get(),
                ProvidedArgumentFactory::create('arg1')
                    ->reducedValue('Europe/Brussels')
                    ->originalType(DateTimeZone::class)
                    ->get(),
                ProvidedArgumentFactory::create('arg2')
                    ->reducedValue(42)
                    ->originalType('int')
                    ->get(),
                ProvidedArgumentFactory::create('arg3')
                    ->reducedValue(69)
                    ->originalType('int')
                    ->get(),
            ],
        ];

        yield 'with stdClass' => [
            TraceArguments::create()->withStdClass(
                (object) [
                    'simple' => 'string',
                    'complex' => new DateTimeZone('Europe/Brussels'),
                ]
            ), [
                ProvidedArgumentFactory::create('class')
                    ->reducedValue([
                        'simple' => 'string',
                        'complex' => 'object (DateTimeZone)',
                    ])
                    ->originalType(stdClass::class)
                    ->get(),
            ],
        ];

        yield 'with too many arguments provided' => [
            TraceArguments::create()->withArray(['a', 'b', 'c'], ['d', 'e', 'f'], ['x', 'y', 'z']),
            [
                ProvidedArgumentFactory::create('array')
                    ->reducedValue(['a', 'b', 'c'])
                    ->originalType('array')
                    ->get(),
                ProvidedArgumentFactory::create('arg1')
                    ->reducedValue(['d', 'e', 'f'])
                    ->originalType('array')
                    ->get(),
                ProvidedArgumentFactory::create('arg2')
                    ->reducedValue(['x', 'y', 'z'])
                    ->originalType('array')
                    ->get(),
            ],
        ];
    }

    /** @test */
    public function it_will_reduce_values_with_enums()
    {
        if (version_compare(PHP_VERSION, '8.1', '<')) {
            $this->markTestSkipped('Enums are only supported in PHP 8.1+');
        }

        $frames = TraceArguments::create()->withEnums(
            FakeUnitEnum::A,
            FakeStringBackedEnum::A,
            FakeIntBackedEnum::A,
        );

        $reduced = (new ReduceArgumentsAction(ArgumentReducers::default()))->execute(
            $frames[1]->class,
            $frames[1]->method,
            $frames[2]->arguments // The package shifts these arguments automatically, we're calling this action manually
        );

        $this->assertEquals([
            ProvidedArgumentFactory::create('unitEnum')
                ->reducedValue(FakeUnitEnum::class.'::A')
                ->originalType(FakeUnitEnum::class)
                ->get()
                ->toArray(),
            ProvidedArgumentFactory::create('stringBackedEnum')
                ->reducedValue(FakeStringBackedEnum::class.'::A')
                ->originalType(FakeStringBackedEnum::class)
                ->get()
                ->toArray(),
            ProvidedArgumentFactory::create('intBackedEnum')
                ->reducedValue(FakeIntBackedEnum::class.'::A')
                ->originalType(FakeIntBackedEnum::class)
                ->get()
                ->toArray(),
        ], $reduced);
    }

    /** @test   */
    public function it_will_reduce_stringable_values()
    {
        if (version_compare(PHP_VERSION, '8.0', '<')) {
            $this->markTestSkipped('Stringable is only supported in PHP 8.0+');
        }

        $stringable = new class implements Stringable {
            public function __toString(): string
            {
                return 'Hello world';
            }
        };

        $frames = TraceArguments::create()->withStringable(
            $stringable
        );

        $reduced = (new ReduceArgumentsAction(ArgumentReducers::default()))->execute(
            $frames[1]->class,
            $frames[1]->method,
            $frames[2]->arguments // The package shifts these arguments automatically, we're calling this action manually
        );

        $this->assertEquals([
            ProvidedArgumentFactory::create('stringable')
                ->reducedValue('Hello world')
                ->originalType(get_class($stringable))
                ->get()
                ->toArray(),
        ], $reduced);
    }

    /** @test */
    public function it_will_reduce_values_even_when_no_reducers_are_specified()
    {
        $frames = TraceArguments::create()->withCombination(
            'string',
            new DateTimeZone('Europe/Brussels'),
            42,
            69
        );

        $reduced = (new ReduceArgumentsAction(ArgumentReducers::create([])))->execute(
            $frames[1]->class,
            $frames[1]->method,
            $frames[2]->arguments // The package shifts these arguments automatically, we're calling this action manually
        );

        $this->assertEquals([
            ProvidedArgumentFactory::create('simple')
                ->reducedValue('string')
                ->originalType('string')
                ->get()
                ->toArray(),
            ProvidedArgumentFactory::create('object')
                ->reducedValue('object')
                ->originalType(DateTimeZone::class)
                ->get()
                ->toArray(),
            ProvidedArgumentFactory::create('variadic')
                ->reducedValue([42, 69])
                ->isVariadic()
                ->originalType('array')
                ->get()
                ->toArray(),
        ], $reduced);
    }

    /** @test */
    public function it_will_reduce_with_not_enough_arguments_provided()
    {
        if (PHP_OS === 'Linux') {
            $this->markTestSkipped('Fails on Linux, due to no arguments provided when creating the trace when too many arguments are provided');
        }

        $frames = TraceArguments::create()->withNotEnoughArgumentsProvided();

        $reduced = (new ReduceArgumentsAction(ArgumentReducers::default()))->execute(
            $frames[0]->class,
            $frames[0]->method,
            $frames[1]->arguments // The package shifts these arguments automatically, we're calling this action manually
        );

        $this->assertEquals([
            ProvidedArgumentFactory::create('simple')
                ->reducedValue('provided')
                ->originalType('string')
                ->get()
                ->toArray(),
            ProvidedArgumentFactory::create('object')
                ->reducedValue(null)
                ->originalType('null')
                ->get()
                ->toArray(),
            ProvidedArgumentFactory::create('variadic')
                ->isVariadic()
                ->reducedValue([])
                ->originalType('array')
                ->get()
                ->toArray(),
        ], $reduced);
    }

    /**
     * @test
     * @dataProvider providedArgumentsDataSet
     */
    public function it_will_transform_an_ProvidedArgument_to_array(
        ProvidedArgument $argument,
        array $expected
    ) {
        $this->assertEquals($expected, $argument->toArray());
    }

    public static function providedArgumentsDataSet()
    {
        yield 'base' => [
            ProvidedArgumentFactory::create('string')
                ->reducedValue('string')
                ->originalType('string')
                ->get(),
            [
                'name' => 'string',
                'value' => 'string',
                'passed_by_reference' => false,
                'is_variadic' => false,
                'truncated' => false,
                'original_type' => 'string',
            ],
        ];
        yield 'base passed by reference' => [
            ProvidedArgumentFactory::create('string')
                ->reducedValue('string')
                ->originalType('string')
                ->passedByReference()
                ->get(),
            [
                'name' => 'string',
                'value' => 'string',
                'passed_by_reference' => true,
                'is_variadic' => false,
                'truncated' => false,
                'original_type' => 'string',
            ],
        ];
        yield 'base variadic' => [
            ProvidedArgumentFactory::create('string')
                ->reducedValue('string')
                ->originalType('array')
                ->isVariadic()
                ->get(),
            [
                'name' => 'string',
                'value' => 'string',
                'passed_by_reference' => false,
                'is_variadic' => true,
                'truncated' => false,
                'original_type' => 'array',
            ],
        ];
        yield 'base truncated' => [
            ProvidedArgumentFactory::create('string')
                ->reducedValue('string')
                ->originalType('string')
                ->truncated()
                ->get(),
            [
                'name' => 'string',
                'value' => 'string',
                'passed_by_reference' => false,
                'is_variadic' => false,
                'truncated' => true,
                'original_type' => 'string',
            ],
        ];
        yield 'default' => [
            ProvidedArgumentFactory::create('string')
                ->defaultValue('string')
                ->defaultValueUsed()
                ->originalType('string')
                ->get(),
            [
                'name' => 'string',
                'value' => 'string',
                'passed_by_reference' => false,
                'is_variadic' => false,
                'truncated' => false,
                'original_type' => 'string',
            ],
        ];
    }
}

class ProvidedArgumentFactory
{
    /** @var ProvidedArgument */
    protected $argument;

    public static function create(string $name): self
    {
        return new self($name);
    }

    public function __construct(string $name)
    {
        $this->argument = new ProvidedArgument($name);
    }

    public function reducedValue($value): self
    {
        $this->argument->reducedValue = $value;

        return $this;
    }

    public function originalType(string $type): self
    {
        $this->argument->originalType = $type;

        return $this;
    }

    public function truncated(): self
    {
        $this->argument->truncated = true;

        return $this;
    }

    public function defaultValue($value): self
    {
        $this->argument->defaultValue = $value;
        $this->argument->hasDefaultValue = true;

        return $this;
    }

    public function defaultValueUsed(): self
    {
        $this->argument->defaultValueUsed = true;

        return $this;
    }

    public function isVariadic(): self
    {
        $this->argument->isVariadic = true;

        return $this;
    }

    public function passedByReference(): self
    {
        $this->argument->passedByReference = true;

        return $this;
    }

    public function get(): ProvidedArgument
    {
        return $this->argument;
    }
}
