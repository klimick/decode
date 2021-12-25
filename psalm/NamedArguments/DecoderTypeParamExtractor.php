<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\NamedArguments;

use Fp\Collections\ArrayList;
use Psalm\Codebase;
use Psalm\Internal\Analyzer\ProjectAnalyzer;
use Psalm\Internal\Type\TemplateResult;
use Psalm\Internal\Type\TemplateStandinTypeReplacer;
use Psalm\Storage\ClassLikeStorage;
use Psalm\Type;
use Klimick\PsalmTest\Integration\Psalm;
use Klimick\Decode\Decoder\DecoderInterface;
use Fp\Functional\Option\Option;
use Psalm\Type\Atomic\TGenericObject;
use Psalm\Type\Atomic\TNamedObject;
use function Fp\Collection\at;
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
