<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\Hook\AfterClassLikeAnalysis;

use Fp\Functional\Option\Option;
use Klimick\Decode\Decoder\DecoderInterface;
use Klimick\Decode\Decoder\Derive;
use Klimick\PsalmDecode\Helper\DecoderTypeParamExtractor;
use Klimick\PsalmDecode\Plugin;
use Klimick\PsalmDecode\PsalmInternal;
use Klimick\PsalmTest\Integration\Psalm;
use Psalm\Internal\MethodIdentifier;
use Psalm\Plugin\EventHandler\AfterClassLikeVisitInterface;
use Psalm\Plugin\EventHandler\Event\AfterClassLikeVisitEvent;
use Psalm\Storage\ClassLikeStorage;
use Psalm\Storage\MethodStorage;
use Psalm\Type\Atomic\TGenericObject;
use Psalm\Type\Atomic\TKeyedArray;
use Psalm\Type\Atomic\TNamedObject;
use Psalm\Type\Union;
use function Fp\Collection\filter;
use function Fp\Evidence\proveString;
use function Fp\Evidence\proveTrue;

final class TestHypothesis implements AfterClassLikeVisitInterface
{
    public static function afterClassLikeVisit(AfterClassLikeVisitEvent $event): void
    {
        Option::do(function() use ($event) {
            $codebase = $event->getCodebase();
            $storage = $event->getStorage();

            $props = yield proveTrue($codebase->classImplements($storage->name, Derive\Props::class))
                ->flatMap(fn() => self::getPropsType($event));

            self::addTypeMethod(to: $storage);
            self::addProperties($props, to: $storage);
            self::removePropsMixin(from: $storage);
        });
    }

    /**
     * @return Option<Union>
     * @psalm-suppress InternalProperty
     */
    private static function getPropsType(AfterClassLikeVisitEvent $event): Option
    {
        $storage = $event->getStorage();
        $storage->populated = true;

        $type = Option::do(function() use ($event, $storage) {
            $props_expr = yield GetPropsExpr::from($event->getStmt());
            $analyzer = yield CreateStatementsAnalyzer::for($event);

            return yield PsalmInternal::analyzeExpression(
                analyzer: $analyzer,
                stmt: $props_expr,
                context: CreateContext::for($storage->name, $props_expr, $analyzer->node_data),
            );
        });
        $storage->populated = false;

        return $type;
    }

    private static function addProperties(Union $properties, ClassLikeStorage $to): void
    {
        Option::do(function() use ($properties, $to) {
            $props_union = yield DecoderTypeParamExtractor::extract($properties);
            $props_atomic = yield Psalm::asSingleAtomicOf(TKeyedArray::class, $props_union);

            $props = [];

            foreach ($props_atomic->properties as $key => $type) {
                $props[yield proveString($key)] = $type;
            }

            foreach ($props as $property_mame => $property_type) {
                $to->pseudo_property_get_types['$' . $property_mame] = $property_type;
            }

            $to->sealed_properties = true;
        });
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

    private static function removePropsMixin(ClassLikeStorage $from): void
    {
        Option::do(function() use ($from) {
            $config = yield Plugin::getMixinConfig();
            $namespace = $config['namespace'];

            $from->namedMixins = filter($from->namedMixins, fn($t) => !str_starts_with($t->value, $namespace));
        });
    }
}
