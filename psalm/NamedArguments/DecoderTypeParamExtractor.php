<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\NamedArguments;

use Psalm\Type;
use Klimick\PsalmTest\Integration\Psalm;
use Klimick\Decode\Decoder\DecoderInterface;
use Fp\Functional\Option\Option;
use function Fp\Collection\first;

final class DecoderTypeParamExtractor
{
    /**
     * @return Option<Type\Union>
     */
    public static function extract(Type\Union $named_arg_type): Option
    {
        return Option::some($named_arg_type)
            ->flatMap(fn($type) => Psalm::asSingleAtomicOf(Type\Atomic\TGenericObject::class, $type))
            ->filter(fn($generic) => DecoderInterface::class === $generic->value)
            ->flatMap(fn($generic) => first($generic->type_params));
    }
}
