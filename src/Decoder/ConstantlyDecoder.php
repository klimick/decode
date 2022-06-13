<?php

declare(strict_types=1);

namespace Klimick\Decode\Decoder;

use Fp\Functional\Either\Either;
use Klimick\Decode\Error\Context;
use function Fp\Collection\every;
use function Fp\Collection\keys;
use function Fp\Collection\map;
use function get_class;
use function get_debug_type;
use function implode;
use function is_int;
use function is_object;

/**
 * @template T
 * @extends AbstractDecoder<T>
 * @psalm-immutable
 */
final class ConstantlyDecoder extends AbstractDecoder
{
    /**
     * @param T $constant
     */
    public function __construct(public mixed $constant) { }

    public function name(): string
    {
        return 'constant<' . self::getTypename($this->constant) . '>';
    }

    /**
     * @return non-empty-string
     * @psalm-pure
     */
    private static function getTypename(mixed $value): string
    {
        /** @var non-empty-string */
        return match (get_debug_type($value)) {
            'null' => 'null',
            'bool' => $value ? 'true' : 'false',
            'int', 'float' => (string) $value,
            'string' => "'{$value}'",
            'array' => self::getArrayTypeName($value),
            default => is_object($value) ? get_class($value) : 'unknown',
        };
    }

    /**
     * @return non-empty-string
     * @psalm-pure
     */
    private static function getArrayTypeName(array $arr): string
    {
        $isList = every(keys($arr), fn($k) => is_int($k));

        $types = $isList
            ? map($arr, fn(mixed $v) => self::getTypename($v))
            : map($arr, fn(mixed $v, string|int $k) => $k . ': ' . self::getTypename($v));

        return 'array{' . implode(', ', $types) . '}';
    }

    public function decode(mixed $value, Context $context): Either
    {
        return valid($this->constant);
    }
}
