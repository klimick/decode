<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\Helper;

use Fp\Functional\Option\Option;
use Klimick\Decode\Decoder\DecoderInterface;
use Klimick\PsalmTest\Integration\Psalm;
use Psalm\Type;
use Psalm\Type\Atomic\TGenericObject;
use function Fp\Collection\first;

final class DecoderTypeParamExtractor
{
    /**
     * @return Option<Type\Union>
     */
    public static function extract(Type\Union $named_arg_type): Option
    {
        return Option::some($named_arg_type)
            ->flatMap(fn($type) => ClassTypeUpcast::forUnion(union: $type, to: DecoderInterface::class))
            ->flatMap(fn($type) => Psalm::asSingleAtomicOf(TGenericObject::class, $type))
            ->flatMap(fn($type) => first($type->type_params));
    }
}
