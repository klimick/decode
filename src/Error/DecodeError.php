<?php

declare(strict_types=1);

namespace Klimick\Decode\Error;

use Klimick\Decode\Decoder\DecoderInterface;

/**
 * @implements ErrorInterface<DecoderInterface>
 * @psalm-immutable
 */
final class DecodeError implements ErrorInterface
{
    public const KIND_TYPE_ERROR = 'TYPE_ERROR';
    public const KIND_UNDEFINED_ERROR = 'UNDEFINED_ERROR';
    public const KIND_CONSTRAINT_ERRORS = 'CONSTRAINT_ERRORS';

    /**
     * @param Context<DecoderInterface> $context
     * @psalm-param DecodeError::KIND_* $kind
     * @param list<non-empty-string> $aliases
     * @param list<ConstraintError> $constraintErrors
     */
    private function __construct(
        public Context $context,
        public string $kind,
        public array $aliases = [],
        public array $constraintErrors = [],
    ) {}

    /**
     * @param Context<DecoderInterface> $context
     *
     * @psalm-pure
     */
    public static function typeError(Context $context): self
    {
        return new DecodeError(
            context: $context,
            kind: self::KIND_TYPE_ERROR,
        );
    }

    /**
     * @param Context<DecoderInterface> $context
     * @param list<non-empty-string> $aliases
     *
     * @psalm-pure
     */
    public static function undefinedError(Context $context, array $aliases): self
    {
        return new DecodeError(
            context: $context,
            kind: self::KIND_UNDEFINED_ERROR,
            aliases: $aliases,
        );
    }

    /**
     * @param Context<DecoderInterface> $context
     * @param non-empty-list<ConstraintError> $constraintErrors
     *
     * @psalm-pure
     */
    public static function constraintErrors(Context $context, array $constraintErrors): self
    {
        return new DecodeError(
            context: $context,
            kind: self::KIND_CONSTRAINT_ERRORS,
            constraintErrors: $constraintErrors,
        );
    }

    public function context(): Context
    {
        return $this->context;
    }
}