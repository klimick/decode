<?php

declare(strict_types=1);

namespace Klimick\Decode\Report;

final class UndefinedErrorReport
{
    public function __construct(
        public string $path,
        public string $property,
    ) { }
}
