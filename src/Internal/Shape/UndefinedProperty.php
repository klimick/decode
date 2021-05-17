<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal\Shape;

/**
 * @psalm-immutable
 */
final class UndefinedProperty
{
    /**
     * @return UndefinedProperty
     * @psalm-pure
     * @psalm-suppress ImpureStaticVariable
     */
    public static function instance(): self
    {
        /** @var null|UndefinedProperty $instance */
        static $instance = null;

        if (null === $instance) {
            $instance = new self();
        }

        return $instance;
    }
}
