<?php

declare(strict_types=1);

namespace Klimick\PsalmTest\Integration\Assertion\Reconciler;

use Fp\Functional\Option\Option;
use Klimick\PsalmTest\Integration\Assertion\Assertions;
use Psalm\Issue\CodeIssue;

interface AssertionReconcilerInterface
{
    /**
     * @return Option<CodeIssue>
     */
    public static function reconcile(Assertions $data): Option;
}
