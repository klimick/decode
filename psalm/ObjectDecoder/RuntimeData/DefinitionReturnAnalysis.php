<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\ObjectDecoder\RuntimeData;

use Klimick\Decode\Decoder\AbstractDecoder;
use Klimick\PsalmDecode\Issue\RuntimeData\InvalidRuntimeDataDefinitionIssue;
use Klimick\PsalmDecode\Psalm;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use Psalm\IssueBuffer;
use Psalm\CodeLocation;
use Psalm\Plugin\EventHandler\AfterFunctionLikeAnalysisInterface;
use Psalm\Plugin\EventHandler\Event\AfterFunctionLikeAnalysisEvent;
use Klimick\Decode\Decoder\RuntimeData;
use Klimick\PsalmDecode\ObjectDecoder\GetGeneralParentClass;
use Fp\Functional\Option\Option;
use Psalm\Type;
use function Fp\Cast\asList;
use function Fp\Collection\first;
use function Fp\Collection\firstOf;
use function Fp\Evidence\proveOf;
use function Fp\Evidence\proveString;
use function Fp\Evidence\proveTrue;

final class DefinitionReturnAnalysis implements AfterFunctionLikeAnalysisInterface
{
    public static function afterStatementAnalysis(AfterFunctionLikeAnalysisEvent $event): ?bool
    {
        $analysis = Option::do(function() use ($event) {
            $context = $event->getContext();
            $source = $event->getStatementsSource();
            $provider = $event->getNodeTypeProvider();
            $codebase = $source->getCodebase();

            $self_class = yield proveString($context->self);

            $general_class = yield GetGeneralParentClass::for($self_class, $codebase);
            yield proveTrue(RuntimeData::class === $general_class);

            $calling_method_id = yield proveString($context->calling_method_id);
            yield proveTrue(str_contains($calling_method_id, '::properties'));

            $class_method = yield proveOf($event->getStmt(), Node\Stmt\ClassMethod::class);

            $visitor = new ReturnTypeFinder();
            $traverser = new NodeTraverser();
            $traverser->addVisitor($visitor);
            $traverser->traverse($class_method->stmts ?? []);

            $return_stmt = yield $visitor->getReturn();

            $is_return_type_valid = Psalm::getType($provider, $return_stmt)
                ->filter(fn($return_type) => self::isReturnTypeValid($return_type))
                ->map(fn() => true)
                ->getOrElse(false);

            if (!$is_return_type_valid) {
                $code_location = new CodeLocation($source, $return_stmt);
                $issue = new InvalidRuntimeDataDefinitionIssue($code_location);

                IssueBuffer::accepts($issue, $source->getSuppressedIssues());
            }
        });

        return $analysis->get();
    }

    private static function isReturnTypeValid(Type\Union $return_type): bool
    {
        $isValid = Option::do(function() use ($return_type) {
            $atomics = asList($return_type->getAtomicTypes());
            yield proveTrue(1 === count($atomics));

            $decoder = yield firstOf($atomics, Type\Atomic\TGenericObject::class)
                ->filter(fn($atomic) => AbstractDecoder::class === $atomic->value)
                ->flatMap(fn($atomic) => first($atomic->type_params))
                ->flatMap(fn($union) => Psalm::asSingleAtomic($union));

            return $decoder instanceof Type\Atomic\TKeyedArray;
        });

        return $isValid->getOrElse(false);
    }
}
