<?php

declare(strict_types=1);

namespace Klimick\Decode\Report;

use Fp\Collections\ArrayList;
use Stringable;
use const PHP_EOL;

final class ErrorReport implements Stringable
{
    public function __construct(
        /**
         * @psalm-readonly
         * @var list<TypeErrorReport|ConstraintErrorReport|UndefinedErrorReport>
         */
        public array $errors = [],
    ) { }

    public function toString(): string
    {
        return (string) $this;
    }

    public function __toString(): string
    {
        return ArrayList::collect($this->errors)
            ->map(fn($e) => $e->toString())
            ->mkString(sep: PHP_EOL);
    }
}
