<?php

declare(strict_types=1);

namespace Klimick\Decode\Decoder;

use Exception;
use Klimick\Decode\Report\ErrorReport;

final class CastException extends Exception
{
    private ErrorReport $report;

    public function __construct(ErrorReport $report)
    {
        $this->report = $report;
        $reportAsString = (string) $report;

        parent::__construct(
            message: "Cast failed:\n{$reportAsString}"
        );
    }

    public function getErrorReport(): ErrorReport
    {
        return $this->report;
    }
}
