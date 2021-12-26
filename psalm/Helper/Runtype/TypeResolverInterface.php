<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\Helper\Runtype;

use Fp\Functional\Option\Option;
use PhpParser\Node;
use Psalm\Type;

interface TypeResolverInterface
{
    /**
     * @return Option<Type\Union>
     */
    public function __invoke(Node\Expr $expr, TypeResolver $resolver): Option;
}
