<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Runtime\Constraint\Helper;

use Klimick\Decode\Constraint\ConstraintInterface;
use Klimick\Decode\Constraint\Invalid;
use Klimick\Decode\Constraint\Valid;
use Klimick\Decode\Context;
use function PHPUnit\Framework\assertInstanceOf;

final class RunCheck
{
    public function __construct(
        private bool $isValid,
        private ConstraintInterface $constraint
    ) { }

    public function withValue(mixed $actual): void
    {
        $context = Context::root($this->constraint->name(), $actual);

        $result = $this->constraint
            ->check(context: $context, value: $actual)
            ->get();

        assertInstanceOf($this->isValid ? Valid::class : Invalid::class, $result, json_encode(['actual' => $actual]));
    }
}
