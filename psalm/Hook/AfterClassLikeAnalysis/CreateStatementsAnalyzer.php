<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\Hook\AfterClassLikeAnalysis;

use PhpParser\Node;
use Fp\Collections\ArrayList;
use Fp\Functional\Option\Option;
use Psalm\Codebase;
use Psalm\FileSource;
use Psalm\Internal\Analyzer\FileAnalyzer;
use Psalm\Internal\Analyzer\NamespaceAnalyzer;
use Psalm\Internal\Analyzer\ProjectAnalyzer;
use Psalm\Internal\Analyzer\StatementsAnalyzer;
use Psalm\Internal\Provider\NodeDataProvider;
use Psalm\Plugin\EventHandler\Event\AfterClassLikeVisitEvent;
use Psalm\StatementsSource;

final class CreateStatementsAnalyzer
{
    /**
     * @return Option<StatementsSource>
     * @psalm-suppress InternalClass
     * @psalm-suppress InternalMethod
     */
    public static function for(AfterClassLikeVisitEvent $event): Option
    {
        return Option::do(function() use ($event) {
            $file_statements = yield self::getFileStatements(
                codebase: $event->getCodebase(),
                source: $event->getStatementsSource(),
            );

            $storage = $event->getStorage();
            $node_data_provider = new NodeDataProvider();
            $namespace = $file_statements
                ->firstOf(Node\Stmt\Namespace_::class)
                ->getOrCall(fn() => new Node\Stmt\Namespace_(
                    stmts: $file_statements
                        ->filterOf(Node\Stmt\Use_::class)
                        ->toArray(),
                ));

            return yield Option
                ::try(fn() => ProjectAnalyzer::$instance->getFileAnalyzerForClassLike($storage->name))
                ->map(fn(FileAnalyzer $analyzer) => new NamespaceAnalyzer($namespace, $analyzer))
                ->tap(fn(NamespaceAnalyzer $analyzer) => $analyzer->collectAnalyzableInformation())
                ->tap(fn(NamespaceAnalyzer $analyzer) => $analyzer->addSuppressedIssues(['all']))
                ->map(fn(NamespaceAnalyzer $analyzer) => new StatementsAnalyzer($analyzer, $node_data_provider));
        });
    }

    /**
     * @return Option<ArrayList<Node\Stmt>>
     */
    private static function getFileStatements(Codebase $codebase, FileSource $source): Option
    {
        $file_path = $source->getFilePath();

        return Option::try(fn() => $codebase->getStatementsForFile($file_path))
            ->map(fn($stmts) => ArrayList::collect($stmts));
    }
}
