<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\ObjectDecoder\RuntimeData;

use PhpParser\Node;
use Psalm\IssueBuffer;
use Psalm\CodeLocation;
use Psalm\Internal\Type\Comparator\UnionTypeComparator;
use Psalm\Plugin\EventHandler\AfterStatementAnalysisInterface;
use Psalm\Plugin\EventHandler\Event\AfterStatementAnalysisEvent;
use Klimick\Decode\RuntimeData;
use Klimick\PsalmDecode\DecodeIssue;
use Klimick\PsalmDecode\ObjectDecoder\GetGeneralParentClass;
use Klimick\PsalmDecode\ShapeDecoder\DecoderType;
use Fp\Functional\Option\Option;
use function Fp\Evidence\proveOf;
use function Fp\Evidence\proveString;
use function Fp\Evidence\proveTrue;

final class DefinitionReturnAnalysis implements AfterStatementAnalysisInterface
{
    public static function afterStatementAnalysis(AfterStatementAnalysisEvent $event): ?bool
    {
        $analysis = Option::do(function() use ($event) {
            $context = $event->getContext();
            $source = $event->getStatementsSource();
            $provider = $source->getNodeTypeProvider();
            $codebase = $source->getCodebase();

            $calling_method_id = yield proveString($context->calling_method_id);
            yield proveTrue(str_contains($calling_method_id, '::definition'));
            $return_stmt = yield proveOf($event->getStmt(), Node\Stmt\Return_::class);

            $return_decoder_type = yield Option::of($provider->getType($return_stmt));

            $self_class = yield proveString($context->self);

            $general_class = yield GetGeneralParentClass::for($self_class, $codebase);
            yield proveTrue(RuntimeData::class === $general_class);

            $expected_decoder_type = DecoderType::createObject($self_class);

            if (!UnionTypeComparator::isContainedBy($codebase, $return_decoder_type, $expected_decoder_type)) {
                $issue = DecodeIssue::invalidRuntimeDataDefinition(
                    $expected_decoder_type,
                    $return_decoder_type,
                    new CodeLocation($source, $return_stmt),
                );

                IssueBuffer::accepts($issue);
            }
        });

        return $analysis->get();
    }
}
