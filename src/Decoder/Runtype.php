<?php

declare(strict_types=1);

namespace Klimick\Decode\Decoder;

/**
 * @template-covariant T of DecoderInterface
 * @psalm-immutable
 */
abstract class Runtype
{
    /**
     * @return T
     */
    abstract public static function type(): DecoderInterface;
}
