<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\Hook\AfterClassLikeAnalysis;

use Klimick\PsalmDecode\PsalmInternal;
use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use function Fp\Collection\filter;

final class FindAnalysisDependencies
{
    /**
     * @return list<string>
     */
    public static function for(FuncCall $props_expr): array
    {
        $visitor = new class extends NodeVisitorAbstract {
            /** @var list<string> */
            private array $dependencies = [];

            public function leaveNode(Node $node): void
            {
                $depended_class = null;

                if ($node instanceof Node\Expr\StaticCall && $node->class instanceof Node\Name) {
                    $depended_class = (string) $node->class->getAttribute('resolvedName');
                }

                if ($node instanceof Node\Expr\New_ && $node->class instanceof Node\Name) {
                    $depended_class = (string) $node->class->getAttribute('resolvedName');
                }

                if (null === $depended_class) {
                    return;
                }

                $storage = PsalmInternal::getStorageFor($depended_class)->get();

                if (null === $storage || !$storage->populated) {
                    $this->dependencies[] = $depended_class;
                }
            }

            /**
             * @return list<string>
             */
            public function getDependencies(): array
            {
                return filter($this->dependencies, fn($dep) => 'self' !== $dep && 'static' !== $dep);
            }
        };

        $traverser = new NodeTraverser();
        $traverser->addVisitor($visitor);
        $traverser->traverse([$props_expr]);

        return $visitor->getDependencies();
    }
}
