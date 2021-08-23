<?php

declare(strict_types=1);

namespace Klimick\PsalmTest\Integration\Assertion;

use Fp\Functional\Option\Option;

/**
 * @psalm-immutable
 */
final class Assertions
{
    /**
     * @param array<class-string<AssertionData>, AssertionData> $data
     */
    public function __construct(private array $data = [])
    {
    }

    /**
     * @template T of AssertionData
     *
     * @param class-string<T> $name
     * @return Option<T>
     */
    public function __invoke(string $name): Option
    {
        return (array_key_exists($name, $this->data) && $this->data[$name] instanceof $name)
            ? Option::some($this->data[$name])
            : Option::none();
    }

    /**
     * @template T of AssertionData
     *
     * @param class-string<T> $name
     * @param T $value
     */
    public function with(AssertionData $value): self
    {
        return new self(array_merge($this->data, [$value::class => $value]));
    }
}
