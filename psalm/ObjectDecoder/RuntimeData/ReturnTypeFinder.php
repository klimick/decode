<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\ObjectDecoder\RuntimeData;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use Fp\Functional\Option\Option;
use function Fp\Collection\first;

final class ReturnTypeFinder extends NodeVisitorAbstract
{
    /** @var list<Node\Stmt\Return_> */
    private array $return = [];

    public function leaveNode(Node $node): void
    {
        if ($node instanceof Node\Stmt\Return_) {
            $this->return[] = $node;
        }
    }

    /**
     * @return Option<Node\Stmt\Return_>
     */
    public function getReturn(): Option
    {
        return $this->isMultiple() ? Option::none() : first($this->return);
    }

    public function isMultiple(): bool
    {
        return count($this->return) > 1;
    }
}
