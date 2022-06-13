<?php

declare(strict_types=1);

namespace Klimick\Decode\Decoder;

use Fp\Functional\Either\Either;
use Fp\Functional\Option\Option;
use Klimick\Decode\Error\Context;
use Klimick\Decode\Error\DecodeError;
use function Fp\Collection\at;
use function Fp\Collection\map;
use function Fp\Evidence\proveString;

/**
 * @template-covariant T
 * @extends AbstractDecoder<T>
 * @psalm-immutable
 */
final class TaggedUnionDecoder extends AbstractDecoder
{
    /**
     * @param non-empty-string $tag
     * @param non-empty-array<non-empty-string, DecoderInterface<T>> $decoders
     */
    public function __construct(
        public string $tag,
        public array $decoders,
    ) { }

    public function name(): string
    {
        return implode(' | ', map(
            $this->decoders,
            fn(DecoderInterface $decoder) => $decoder->name(),
        ));
    }

    public function decode(mixed $value, Context $context): Either
    {
        return $this->getTaggedDecoderFor($context, $value)
            ->flatMap(fn($decoder) => $decoder->decode($value, $context));
    }

    /**
     * @param Context<DecoderInterface> $context
     * @return Either<non-empty-list<DecodeError>, DecoderInterface<T>>
     */
    private function getTaggedDecoderFor(Context $context, mixed $value): Either
    {
        return Option::fromNullable(is_array($value) ? $value : null)
            ->flatMap(fn($array) => at($array, $this->tag))
            ->flatMap(fn($tag) => proveString($tag))
            ->flatMap(fn($tag) => at($this->decoders, $tag))
            ->toRight(fn() => [
                DecodeError::undefinedError(
                    context: $context($this, actual: null, key: $this->tag),
                    aliases: [],
                )
            ]);
    }
}
