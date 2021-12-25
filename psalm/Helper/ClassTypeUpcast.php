<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\Helper;

use Fp\Collections\ArrayList;
use Fp\Functional\Option\Option;
use Klimick\Decode\Decoder\DecoderInterface;
use Psalm\Internal\Analyzer\ProjectAnalyzer;
use Psalm\Internal\Type\TemplateResult;
use Psalm\Internal\Type\TemplateStandinTypeReplacer;
use Psalm\Storage\ClassLikeStorage;
use Psalm\Type\Atomic\TGenericObject;
use Psalm\Type\Atomic\TNamedObject;
use Psalm\Type\Union;
use function Fp\Collection\at;
use function Fp\Evidence\proveOf;

/**
 * ObjectDecoder<Foo> | UnionDecoder<Foo> -> DecoderInterface<Foo>
 * ObjectDecoder<Foo> | UnionDecoder<Bar> -> DecoderInterface<Foo|Bar>
 */
final class ClassTypeUpcast
{
    /**
     * @return Option<Union>
     */
    public static function forUnion(Union $union, string $to): Option
    {
        return Option::do(function() use ($union, $to) {
            $upcasted = [];

            foreach ($union->getAtomicTypes() as $atomic) {
                $upcasted[] = yield proveOf($atomic, TNamedObject::class)
                    ->flatMap(fn($named_object) => self::forAtomic($named_object, $to));
            }

            return new Union($upcasted);
        });
    }

    /**
     * @return Option<TNamedObject>
     */
    public static function forAtomic(TNamedObject $type, string $to): Option
    {
        $codebase = ProjectAnalyzer::$instance->getCodebase();

        return Option::do(function() use ($codebase, $to, $type) {
            if ($type->value === $to) {
                return $type;
            }

            $storage = yield Option::fromNullable($codebase->classlikes->getStorageFor($type->value));

            $parent = yield self::getParent($storage);
            $template_result = self::getTemplateResult($storage, $type);

            $template_params = Option::fromNullable($storage->template_extended_offsets)
                ->flatMap(fn($extended_offsets) => at($extended_offsets, $parent))
                ->map(fn($parent_templates) => ArrayList::collect($parent_templates)->map(fn($t) => clone $t))
                ->getOrElse(ArrayList::empty())
                ->map(fn($template_type) => TemplateStandinTypeReplacer::replace(
                    union_type: $template_type,
                    template_result: $template_result,
                    codebase: $codebase,
                    statements_analyzer: null,
                    input_type: null,
                ))
                ->toArray();

            $upcasted = !empty($template_params)
                ? new TGenericObject($parent, $template_params)
                : new TNamedObject($parent);

            return yield self::forAtomic($upcasted, $to);
        });
    }

    /**
     * @return Option<string>
     */
    private static function getParent(ClassLikeStorage $storage): Option
    {
        return Option::fromNullable($storage->parent_class)
            ->orElse(
                fn() => Option::some(strtolower(DecoderInterface::class))
                    ->flatMap(fn($decoder_interface) => at($storage->class_implements, $decoder_interface))
                    ->map(fn() => DecoderInterface::class)
            );
    }

    private static function getTemplateResult(ClassLikeStorage $storage, TNamedObject $named_object): TemplateResult
    {
        $template_types = [];

        if ($named_object instanceof TGenericObject) {
            $type_param_names = array_keys($storage->template_types ?? []);

            foreach ($named_object->type_params as $param_offset => $param_type) {
                $template_types[$type_param_names[$param_offset]] = [$storage->name => $param_type];
            }
        }

        return new TemplateResult(
            template_types: $template_types,
            lower_bounds: [],
        );
    }
}
