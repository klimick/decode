<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\Hook\AfterClassLikeAnalysis;

use Fp\Functional\Option\Option;
use Klimick\Decode\Decoder\DecoderInterface;
use Klimick\Decode\Decoder\Derive;
use Klimick\Decode\Decoder\Derive\Decoder;
use Klimick\PsalmDecode\Helper\DecoderType;
use Klimick\PsalmDecode\Plugin;
use Fp\PsalmToolkit\Toolkit\PsalmApi;
use Psalm\Internal\MethodIdentifier;
use Psalm\Internal\Type\TypeAlias\ClassTypeAlias;
use Psalm\Plugin\EventHandler\AfterClassLikeVisitInterface;
use Psalm\Plugin\EventHandler\Event\AfterClassLikeVisitEvent;
use Psalm\Storage\ClassLikeStorage;
use Psalm\Storage\MethodStorage;
use Psalm\Type\Atomic\TGenericObject;
use Psalm\Type\Atomic\TKeyedArray;
use Psalm\Type\Atomic\TNamedObject;
use Psalm\Type\Union;
use function Fp\Collection\at;
use function Fp\Collection\filter;
use function Fp\Evidence\proveNonEmptyArray;
use function Fp\Evidence\proveTrue;

final class DerivePropsVisitor implements AfterClassLikeVisitInterface
{
    public static function afterClassLikeVisit(AfterClassLikeVisitEvent $event): void
    {
        Option::do(function() use ($event) {
            $storage = $event->getStorage();

            $props = yield proveTrue(PsalmApi::$classlikes->classImplements($storage->name, Derive\Props::class))
                ->flatMap(fn() => self::getPropsType($event));

            self::addTypeMethod(to: $storage);
            self::fixPropsMethod($props, to: $storage);
            self::addProperties($props, to: $storage);
            self::addShapeTypeAlias($props, to: $storage);
            self::removePropsMixin(from: $storage);
        });
    }

    /**
     * @return Option<non-empty-array<string, Union>>
     */
    private static function getPropsType(AfterClassLikeVisitEvent $event): Option
    {
        $storage = $event->getStorage();
        $storage->populated = true;

        $type = Option::do(function() use ($event, $storage) {
            $props_expr = yield GetPropsExpr::from($event->getStmt());
            $analyzer = yield CreateStatementsAnalyzer::for($event);

            return yield PsalmApi::$types->analyzeType(
                analyzer: $analyzer,
                expr: $props_expr,
                context: CreateContext::for(
                    self: $storage->name,
                    props_expr: $props_expr,
                    node_data: $analyzer->getNodeTypeProvider(),
                ),
            );
        });

        $storage->populated = false;

        return $type
            ->flatMap(fn($type) => DecoderType::getShapeProperties($type))
            ->flatMap(fn($props) => proveNonEmptyArray($props));
    }

    /**
     * @param non-empty-array<string, Union> $properties
     */
    private static function fixPropsMethod(array $properties, ClassLikeStorage $to): void
    {
        Option::do(function() use ($to, $properties) {
            $props_method = yield at($to->methods, 'props');
            $props_method->return_type = yield DecoderType::withShapeDecoderIntersection(
                DecoderType::createShapeDecoder($properties),
            );
        });
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
        if (!array_key_exists(strtolower(Decoder::class), $to->used_traits)) {
            return;
        }

        foreach ($properties as $property_mame => $property_type) {
            $to->pseudo_property_get_types['$' . $property_mame] = $property_type;
        }

        $to->sealed_properties = true;
    }

    private static function addTypeMethod(ClassLikeStorage $to): void
    {
        if (!array_key_exists(strtolower(Decoder::class), $to->used_traits)) {
            return;
        }

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

    private static function removePropsMixin(ClassLikeStorage $from): void
    {
        Option::do(function() use ($from) {
            $config = yield Plugin::getMixinConfig();
            $namespace = $config['namespace'];

            $from->namedMixins = filter($from->namedMixins, fn($t) => !str_starts_with($t->value, $namespace));
        });
    }
}
