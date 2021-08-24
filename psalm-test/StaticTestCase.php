<?php

declare(strict_types=1);

namespace Klimick\PsalmTest;

use Closure;
use Klimick\PsalmTest\StaticType\StaticTypeInterface;

final class StaticTestCase
{
    public function __construct(public Closure $codeBlock)
    {
    }

    public static function describe(): PsalmCodeBlockFactory
    {
        return new PsalmCodeBlockFactory();
    }

    public function seeReturnType(StaticTypeInterface $is, bool $invariant = true): self
    {
        return $this;
    }

    public function seePsalmIssue(string $type, string $message): self
    {
        return $this;
    }
}
