<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\Hook\AfterClassLikeAnalysis;

use Fp\Functional\Option\Option;
use Fp\PsalmToolkit\Toolkit\PsalmApi;
use Klimick\Decode\Decoder\DecoderInterface;
use Klimick\Decode\Decoder\InferShape;
use Klimick\Decode\Decoder\ObjectInstance;
use Klimick\Decode\Internal\Shape\ShapeDecoder;
use Klimick\PsalmDecode\Common\DecoderType;
use Psalm\Internal\MethodIdentifier;
use Psalm\Internal\Type\TypeAlias\ClassTypeAlias;
use Psalm\Plugin\EventHandler\AfterClassLikeVisitInterface;
use Psalm\Plugin\EventHandler\Event\AfterClassLikeVisitEvent;
use Psalm\Storage\ClassLikeStorage;
use Psalm\Storage\MethodStorage;
use Psalm\Type\Atomic\TGenericObject;
use Psalm\Type\Atomic\TKeyedArray;
use Psalm\Type\Atomic\TNamedObject;
use Psalm\Type\Atomic\TTypeAlias;
use Psalm\Type\Union;
use function array_key_exists;
use function Fp\Collection\filter;
use function Fp\Evidence\proveNonEmptyArray;
use function Fp\Evidence\proveTrue;

final class InferShapeAfterClassLikeVisit implements AfterClassLikeVisitInterface
{
    public static function afterClassLikeVisit(AfterClassLikeVisitEvent $event): void
    {
        Option::do(function() use ($event) {
            $storage = $event->getStorage();

            $props = yield proveTrue(PsalmApi::$classlikes->classImplements($storage, InferShape::class))
                ->flatMap(fn() => GetMethodReturnType::from($event, 'shape'))
                ->flatMap(fn($type) => DecoderType::getShapeProperties($type))
                ->flatMap(fn($props) => proveNonEmptyArray($props));

            self::fixShapeMethod(to: $storage);
            self::addShapeTypeAlias($props, to: $storage);
            self::removeMetaMixin(from: $storage);

            if (PsalmApi::$classlikes->isTraitUsed($storage, ObjectInstance::class)) {
                self::addTypeMethod(to: $storage);
                self::addProperties($props, to: $storage);
            }
        });
    }

    /**
     * @param non-empty-array<string, Union> $properties
     */
    private static function fixShapeMethod(ClassLikeStorage $to): void
    {
        if (!array_key_exists('shape', $to->methods)) {
            return;
        }

        $shape_type_param = new Union([
            new TTypeAlias($to->name, PsalmApi::$classlikes->toShortName($to) . 'Shape'),
        ]);

        $to->methods['shape']->return_type = new Union([
            PsalmApi::$types->addIntersection(
                to: new TGenericObject(DecoderInterface::class, [$shape_type_param]),
                type: new TGenericObject(ShapeDecoder::class, [$shape_type_param]),
            ),
        ]);
    }

    /**
     * @param non-empty-array<string, Union> $properties
     */
    private static function addShapeTypeAlias(array $properties, ClassLikeStorage $to): void
    {
        $short_class_name = PsalmApi::$classlikes->toShortName($to);
        $type_alias_name = "{$short_class_name}Shape";

        if (array_key_exists($type_alias_name, $to->type_aliases)) {
            return;
        }

        $to->type_aliases[$type_alias_name] = new ClassTypeAlias([
            new TKeyedArray($properties),
        ]);
    }

    /**
     * @param non-empty-array<string, Union> $properties
     */
    private static function addProperties(array $properties, ClassLikeStorage $to): void
    {
        foreach ($properties as $property_mame => $property_type) {
            $to->pseudo_property_get_types['$' . $property_mame] = $property_type->possibly_undefined
                ? PsalmApi::$types->asNullable($property_type)
                : $property_type;
        }

        $to->sealed_properties = true;
    }

    private static function addTypeMethod(ClassLikeStorage $to): void
    {
        $name_lc = 'type';

        $method = new MethodStorage();
        $method->cased_name = $name_lc;
        $method->is_static = true;
        $method->return_type = new Union([
            new TGenericObject(DecoderInterface::class, [
                new Union([
                    new TNamedObject($to->name),
                ]),
            ]),
        ]);

        $to->declaring_method_ids[$name_lc] = new MethodIdentifier($to->name, $name_lc);
        $to->appearing_method_ids[$name_lc] = new MethodIdentifier($to->name, $name_lc);
        $to->methods[$name_lc] = $method;
    }

    private static function removeMetaMixin(ClassLikeStorage $from): void
    {
        $from->namedMixins = filter($from->namedMixins, fn($t) => $t->value !== "{$from->name}MetaMixin");
    }
}
