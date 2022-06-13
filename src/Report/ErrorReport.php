<?php

declare(strict_types=1);

namespace Klimick\Decode\Report;

use Fp\Collections\ArrayList;
use JsonSerializable;
use Stringable;
use const PHP_EOL;

final class ErrorReport implements JsonSerializable, Stringable
{
    public function __construct(
        /**
         * @psalm-readonly
         * @var list<TypeErrorReport|ConstraintErrorReport|UndefinedErrorReport>
         */
        public array $errors = [],
    ) { }

    public function __toString(): string
    {
        return ArrayList::collect($this->errors)
            ->map(fn($e) => $e->toString())
            ->mkString(sep: PHP_EOL);
    }

    public function jsonSerialize(): array
    {
        return $this->errors;
    }
}
