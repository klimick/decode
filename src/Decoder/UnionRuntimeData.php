<?php

declare(strict_types=1);

namespace Klimick\Decode\Decoder;

use Klimick\Decode\Internal\ObjectDecoder;
use Klimick\Decode\Internal\UnionDecoder;
use RuntimeException;

/**
 * @psalm-immutable
 */
abstract class UnionRuntimeData
{
    private string $caseId;
    private RuntimeData|UnionRuntimeData $instance;

    final public function __construct(RuntimeData|UnionRuntimeData $case)
    {
        $this->caseId = static::getCaseIdByInstance($case);
        $this->instance = $case;
    }

    private static function getCaseIdByInstance(RuntimeData|UnionRuntimeData $instance): string
    {
        foreach (static::getCases() as $type => $decoder) {
            if ($decoder->is($instance)) {
                return $type;
            }
        }

        throw new RuntimeException('Unable to create UnionRuntimeData. Check psalm issues.');
    }

    final public function match(callable ...$matchers): mixed
    {
        return ($matchers[$this->caseId])($this->instance);
    }

    /**
     * @psalm-return AbstractDecoder<static> & UnionDecoder<static>
     */
    final public static function type(): AbstractDecoder
    {
        /** @var null|(AbstractDecoder<static> & UnionDecoder<static>) $decoder */
        static $decoder = null;

        if (null !== $decoder) {
            return $decoder;
        }

        return ($decoder = new UnionDecoder(
            ...array_map(
                fn($decoder) => new ObjectDecoder(static::class, [
                    'instance' => $decoder->from('$'),
                ]),
                array_values(static::getCases()),
            )
        ));
    }

    /**
     * @psalm-return non-empty-array<non-empty-string, ObjectDecoder<RuntimeData> | UnionDecoder<UnionRuntimeData>>
     */
    private static function getCases(): array
    {
        /** @var null|non-empty-array<non-empty-string, ObjectDecoder<RuntimeData> | UnionDecoder<UnionRuntimeData>> $cases */
        static $cases = null;

        if (null !== $cases) {
            return $cases;
        }

        return ($cases = static::cases());
    }

    /**
     * @psalm-return non-empty-array<non-empty-string, ObjectDecoder<RuntimeData> | UnionDecoder<UnionRuntimeData>>
     */
    abstract protected static function cases(): array;
}
