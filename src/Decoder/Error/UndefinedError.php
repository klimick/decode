<?php

declare(strict_types=1);

namespace Klimick\Decode\Decoder\Error;

use Klimick\Decode\Context;

/**
 * @psalm-immutable
 */
final class UndefinedError implements DecodeErrorInterface
{
    public function __construct(public Context $context) { }
}
