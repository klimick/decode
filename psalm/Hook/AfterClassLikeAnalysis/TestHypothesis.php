<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\Hook\AfterClassLikeAnalysis;

use Fp\Functional\Option\Option;
use Klimick\Decode\Decoder\DecoderInterface;
use Klimick\Decode\Decoder\Derive;
use Psalm\Internal\MethodIdentifier;
use Psalm\Plugin\EventHandler\AfterClassLikeVisitInterface;
use Psalm\Plugin\EventHandler\Event\AfterClassLikeVisitEvent;
use Psalm\Storage\ClassLikeStorage;
use Psalm\Storage\MethodStorage;
use Psalm\Type\Atomic\TGenericObject;
use Psalm\Type\Atomic\TNamedObject;
use Psalm\Type\Union;
use function Fp\Evidence\proveTrue;

final class TestHypothesis implements AfterClassLikeVisitInterface
{
    public static function afterClassLikeVisit(AfterClassLikeVisitEvent $event): void
    {
        Option::do(function() use ($event) {
            $codebase = $event->getCodebase();
            $storage = $event->getStorage();

            yield proveTrue($codebase->classImplements($storage->name, Derive\Props::class));

            $props_expr = yield GetPropsExpr::from($event->getStmt());
            $analyzer = yield CreateStatementsAnalyzer::for($event);

            self::addTypeMethod(to: $storage);

            AnalysisQueue::instance()->push(
                deferred: new DeferredAnalysis($analyzer, $props_expr, $storage),
                deps: FindAnalysisDependencies::for($props_expr),
            );

            AnalysisQueue::instance()->classVisited($storage->name);
            AnalysisQueue::instance()->popIndependent();
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
                    new TNamedObject($to->name)
                ]),
            ]),
        ]);

        $to->declaring_method_ids[$name_lc] = new MethodIdentifier($to->name, $name_lc);
        $to->appearing_method_ids[$name_lc] = new MethodIdentifier($to->name, $name_lc);
        $to->methods[$name_lc] = $method;
    }
}
