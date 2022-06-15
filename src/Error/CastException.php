<?php

declare(strict_types=1);

namespace Klimick\Decode\Error;

use Exception;
use Klimick\Decode\Report\ErrorReport;

final class CastException extends Exception
{
    public function __construct(
        /** @psalm-readonly */
        public ErrorReport $report,
        /** @psalm-readonly */
        public string $typename,
    )
    {
        parent::__construct("Cast to type '{$typename}' failed:\n{$report}");
    }
}
