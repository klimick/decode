<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Runtime\Constraint\Helper;

use Klimick\Decode\Constraint\ConstraintInterface;
use Klimick\Decode\Context;
use function Fp\Cast\asList;
use function PHPUnit\Framework\assertEmpty;
use function PHPUnit\Framework\assertNotEmpty;

final class RunCheck
{
    public function __construct(
        private bool $isValid,
        private ConstraintInterface $constraint
    ) { }

    public function withValue(mixed $actual): void
    {
        $context = Context::root($this->constraint->name(), $actual);

        $result = asList($this->constraint->check($context, $actual));
        $message = json_encode(['actual' => $actual]);

        $this->isValid
            ? assertEmpty($result, $message)
            : assertNotEmpty($result, $message);
    }
}
