<?php

declare(strict_types=1);

namespace Klimick\PsalmTest\Integration\Assertion\Collector;

use Fp\Functional\Option\Option;
use Klimick\PsalmTest\Integration\Assertion\Assertions;

interface AssertionCollectorInterface
{
    /**
     * @return Option<Assertions>
     */
    public static function collect(Assertions $data, AssertionCollectingContext $context): Option;

    public static function isSupported(AssertionCollectingContext $context): bool;
}
