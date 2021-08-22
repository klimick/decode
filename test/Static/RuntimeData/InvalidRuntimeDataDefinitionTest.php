<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Static\RuntimeData;

use Klimick\Decode\Decoder\AbstractDecoder;
use Klimick\Decode\Decoder\RuntimeData;
use function Klimick\Decode\Decoder\string;

/**
 * @psalm-immutable
 */
final class InvalidRuntimeDataDefinitionTest extends RuntimeData
{
    /**
     * @psalm-suppress InvalidRuntimeDataDefinitionIssue
     */
    protected static function properties(): AbstractDecoder
    {
        return string();
    }
}
