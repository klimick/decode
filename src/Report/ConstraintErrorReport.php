<?php

declare(strict_types=1);

namespace Klimick\Decode\Report;

use Klimick\Decode\Constraint\Metadata\ConstraintMetaInterface;
use Klimick\Decode\Constraint\Metadata\ConstraintMetaWithPayload;
use Klimick\Decode\Constraint\Metadata\ConstraintMetaWithNested;
use function array_map;
use function implode;
use function json_encode;
use function vsprintf;

final class ConstraintErrorReport
{
    public function __construct(
        /** @psalm-readonly */
        public string $path,
        /** @psalm-readonly */
        public mixed $value,
        /** @psalm-readonly */
        public ConstraintMetaInterface $meta,
    ) { }

    public function toString(): string
    {
        $actualAsString = ActualValueToString::for($this->value);
        $metaAsString = self::metaToString($this->meta);

        return "[{$this->path}]: Value {$actualAsString} cannot be validated with {$metaAsString}";
    }

    private static function metaToString(ConstraintMetaInterface $meta): string
    {
        return match (true) {
            $meta instanceof ConstraintMetaWithPayload => vsprintf('%s(%s)', [
                $meta->name,
                json_encode($meta->payload),
            ]),
            $meta instanceof ConstraintMetaWithNested => match (true) {
                is_array($meta->nested) => vsprintf('%s[%s]', [
                    $meta->name,
                    implode(', ', array_map(fn(ConstraintMetaInterface $m) => self::metaToString($m), $meta->nested)),
                ]),
                default => vsprintf('%s.%s', [
                    $meta->name,
                    self::metaToString($meta->nested),
                ])
            },
        };
    }
}
