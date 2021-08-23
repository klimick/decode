<?php

declare(strict_types=1);

namespace Klimick\PsalmTest;

use Closure;

final class PsalmCodeBlock
{
    public function __construct(public Closure $codeBlock)
    {
    }
}
