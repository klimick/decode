<?php

declare(strict_types=1);

namespace Klimick\Decode\Report;

use Fp\Collections\ArrayList;
use JsonSerializable;
use Stringable;
use function Fp\Collection\filter;
use const JSON_UNESCAPED_UNICODE;
use const PHP_EOL;

/**
 * @psalm-immutable
 */
final class ErrorReport implements JsonSerializable, Stringable
{
    /**
     * @param list<TypeErrorReport> $typeErrors
     * @param list<ConstraintErrorReport> $constraintErrors
     * @param list<UndefinedErrorReport> $undefinedErrors
     */
    public function __construct(
        public array $typeErrors = [],
        public array $constraintErrors = [],
        public array $undefinedErrors = [],
    ) { }

    public function __toString(): string
    {
        $typeErrors = ArrayList::collect($this->typeErrors)
            ->map(function($e) {
                $actualValue = is_object($e->actual)
                    ? get_class($e->actual) . '::class'
                    : trim(json_encode($e->actual, JSON_UNESCAPED_UNICODE), '"');

                $expectedValue = class_exists($e->expected)
                    ? $e->expected . '::class'
                    : $e->expected;

                return "[{$e->path}]: Type error. Value {$actualValue} cannot be represented as {$expectedValue}";
            })
            ->mkString(sep: PHP_EOL);

        $constraintErrors = ArrayList::collect($this->constraintErrors)
            ->map(function($e) {
                $actualValue = is_object($e->value)
                    ? get_class($e->value) . '::class'
                    : trim(json_encode($e->value, JSON_UNESCAPED_UNICODE), '"');

                $payload = json_encode($e->payload, JSON_UNESCAPED_UNICODE);

                return "[{$e->path}]: Value {$actualValue} cannot be validated with {$e->constraint}($payload)";
            })
            ->mkString(sep: PHP_EOL);

        $undefinedErrors = ArrayList::collect($this->undefinedErrors)
            ->map(fn(UndefinedErrorReport $e) => $e->toString())
            ->mkString(sep: PHP_EOL);

        return implode(PHP_EOL, [$typeErrors, $constraintErrors, $undefinedErrors, PHP_EOL]);
    }

    public function jsonSerialize(): array
    {
        $values = [
            'typeErrors' => $this->typeErrors,
            'constraintErrors' => $this->constraintErrors,
            'undefinedErrors' => $this->undefinedErrors,
        ];

        return filter($values, fn($v) => !empty($v), preserveKeys: true);
    }
}
