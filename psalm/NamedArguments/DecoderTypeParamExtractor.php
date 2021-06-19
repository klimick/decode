<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\NamedArguments;

use Psalm\Type;
use Fp\Functional\Option\Option;
use Klimick\Decode\Decoder\AbstractDecoder;
use function Fp\Cast\asList;
use function Fp\Collection\first;
use function Fp\Collection\firstOf;
use function Fp\Evidence\proveTrue;

final class DecoderTypeParamExtractor
{
    /**
     * @return Option<Type\Union>
     */
    public static function extract(Type\Union $named_arg_type): Option
    {
        return Option::do(function() use ($named_arg_type) {
            $atomics = asList($named_arg_type->getAtomicTypes());
            yield proveTrue(1 === count($atomics));

            $generic_object = yield firstOf($atomics, Type\Atomic\TGenericObject::class);

            yield proveTrue($generic_object->value === AbstractDecoder::class);
            yield proveTrue(1 === count($generic_object->type_params));

            return yield first($generic_object->type_params);
        });
    }
}
