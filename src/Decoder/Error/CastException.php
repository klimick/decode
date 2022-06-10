<?php

declare(strict_types=1);

namespace Klimick\Decode\Decoder\Error;

use Exception;
use Klimick\Decode\Report\ErrorReport;

final class CastException extends Exception
{
    public function __construct(private ErrorReport $report, private string $typename)
    {
        parent::__construct("Cast to type '{$typename}' failed:\n{$report}");
    }

    public function getReport(): ErrorReport
    {
        return $this->report;
    }

    public function getTypename(): string
    {
        return $this->typename;
    }
}
