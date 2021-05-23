<?php

declare(strict_types=1);

namespace Klimick\Decode\Error;

use Klimick\Decode\Context;

/**
 * @psalm-immutable
 */
final class TypeError implements ErrorInterface
{
    public function __construct(
        public Context $context,
    ) { }
}
