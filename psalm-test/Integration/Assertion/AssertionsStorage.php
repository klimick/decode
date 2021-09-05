<?php

declare(strict_types=1);

namespace Klimick\PsalmTest\Integration\Assertion;

use Klimick\PsalmTest\PsalmTest;

final class AssertionsStorage
{
    /** @var array<non-empty-string, Assertions> */
    private static array $data = [];

    /**
     * @param class-string<PsalmTest> $for
     * @return array<non-empty-string, Assertions>
     */
    public static function take(string $for): array
    {
        $take = [];
        $notTake = [];

        foreach (self::$data as $key => $data) {
            str_starts_with($key, $for)
                ? ($take[$key] = $data)
                : ($notTake[$key] = $data);
        }

        self::$data = $notTake;

        return $take;
    }

    /**
     * @param class-string<PsalmTest> $test_class
     * @param lowercase-string $test_method
     */
    public static function get(string $test_class, string $test_method): Assertions
    {
        return array_key_exists(self::key($test_class, $test_method), self::$data)
            ? self::$data[self::key($test_class, $test_method)]
            : new Assertions();
    }

    /**
     * @param class-string<PsalmTest> $test_class
     * @param lowercase-string $test_method
     */
    public static function set(string $test_class, string $test_method, Assertions $new_data): void
    {
        self::$data[self::key($test_class, $test_method)] = $new_data;
    }

    /**
     * @param class-string<PsalmTest> $testClass
     * @param lowercase-string $testMethod
     * @return non-empty-string
     */
    private static function key(string $testClass, string $testMethod): string
    {
        return "{$testClass}::$testMethod";
    }
}
