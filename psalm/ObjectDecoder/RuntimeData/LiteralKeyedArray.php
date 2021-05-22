<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\ObjectDecoder\RuntimeData;

use Psalm\Type\Union;
use Psalm\Type\Atomic;
use Fp\Functional\Option\Option;
use function Fp\Cast\asList;
use function Fp\Evidence\proveTrue;

final class LiteralKeyedArray
{
    /**
     * @return Option<scalar|array>
     */
    public static function toPhpValue(Union $union): Option
    {
        return Option::do(function() use ($union) {
            $atomics = asList($union->getAtomicTypes());

            yield proveTrue(1 === count($atomics));
            yield proveTrue($atomics[0]::class === Atomic\TKeyedArray::class);

            return yield self::fromLiteralAtomic($atomics[0]);
        });
    }

    /**
     * @return Option<scalar|array>
     */
    private static function fromLiteralUnion(Union $union): Option
    {
        return Option::do(function() use ($union) {
            $atomics = asList($union->getAtomicTypes());
            yield proveTrue(1 === count($atomics));

            return yield self::fromLiteralAtomic($atomics[0]);
        });
    }

    private static function isEmpty(Union $union): bool
    {
        $atomics = asList($union->getAtomicTypes());
        return 1 === count($atomics) && $atomics[0] instanceof Atomic\TEmpty;
    }

    /**
     * @param Atomic $atomic
     * @return Option<scalar|array>
     */
    private static function fromLiteralAtomic(Atomic $atomic): Option
    {
        return Option::do(function() use ($atomic) {
            if ($atomic instanceof Atomic\TKeyedArray) {
                $keyed_array_value = [];

                foreach ($atomic->properties as $name => $type) {
                    $keyed_array_value[$name] = yield self::fromLiteralUnion($type);
                }

                return $keyed_array_value;
            }

            if ($atomic instanceof Atomic\TArray) {
                yield proveTrue(self::isEmpty($atomic->type_params[0]));
                yield proveTrue(self::isEmpty($atomic->type_params[1]));

                return [];
            }

            if ($atomic instanceof Atomic\TList) {
                yield proveTrue(self::isEmpty($atomic->type_param));

                return [];
            }

            yield proveTrue(
                $atomic instanceof Atomic\TLiteralClassString ||
                $atomic instanceof Atomic\TLiteralString ||
                $atomic instanceof Atomic\TLiteralFloat ||
                $atomic instanceof Atomic\TLiteralInt
            );

            return $atomic->value;
        });
    }
}
