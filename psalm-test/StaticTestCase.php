<?php

declare(strict_types=1);

namespace Klimick\PsalmTest;

use Closure;
use Klimick\PsalmTest\StaticType\StaticTypeInterface;

/**
 * @template TTestCaseName of non-empty-string
 */
final class StaticTestCase
{
    public function __construct(public Closure $codeBlock)
    {
    }

    /**
     * @template TName of non-empty-string
     *
     * @param TName $testCaseName
     * @return PsalmCodeBlockFactory<TName>
     */
    public static function describe(string $testCaseName): PsalmCodeBlockFactory
    {
        NoCode::here();
    }

    /**
     * @return self<TTestCaseName>
     */
    public function seeReturnType(StaticTypeInterface $is, bool $invariant = true): self
    {
        NoCode::here();
    }

    /**
     * @param array<string, string> $args
     * @return self<TTestCaseName>
     */
    public function seePsalmIssue(string $type, string $message, array $args = []): self
    {
        NoCode::here();
    }
}
