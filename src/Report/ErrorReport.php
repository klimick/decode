<?php

declare(strict_types=1);

namespace Klimick\Decode\Report;

use Fp\Collections\ArrayList;
use JsonSerializable;
use Stringable;
use function Fp\Collection\filter;
use const PHP_EOL;

final class ErrorReport implements JsonSerializable, Stringable
{
    public function __construct(
        /**
         * @psalm-readonly
         * @var list<TypeErrorReport>
         */
        public array $typeErrors = [],
        /**
         * @psalm-readonly
         * @var list<ConstraintErrorReport>
         */
        public array $constraintErrors = [],
        /**
         * @psalm-readonly
         * @var list<UndefinedErrorReport>
         */
        public array $undefinedErrors = [],
    ) { }

    public function __toString(): string
    {
        $typeErrors = ArrayList::collect($this->typeErrors)
            ->map(fn(TypeErrorReport $e) => $e->toString())
            ->mkString(sep: PHP_EOL);

        $constraintErrors = ArrayList::collect($this->constraintErrors)
            ->map(fn(ConstraintErrorReport $e) => $e->toString())
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
