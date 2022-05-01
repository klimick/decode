<?php

declare(strict_types=1);

namespace Klimick\Decode\Decoder\Derive;

use Klimick\Decode\Decoder\DecoderInterface;

/**
 * @template T of object
 */
interface Props
{
    /**
     * @return DecoderInterface<array<string, mixed>>
     */
    public static function props(): DecoderInterface;
}
