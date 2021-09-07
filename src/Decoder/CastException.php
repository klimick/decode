<?php

declare(strict_types=1);

namespace Klimick\Decode\Decoder;

use Exception;
use Klimick\Decode\Report\ErrorReport;

final class CastException extends Exception
{
    private ErrorReport $report;

    public function __construct(ErrorReport $report, string $typename)
    {
        parent::__construct(
            message: "Unable to cast given data to type {$typename}"
        );

        $this->report = $report;
    }

    public function getErrorReport(): ErrorReport
    {
        return $this->report;
    }
}
