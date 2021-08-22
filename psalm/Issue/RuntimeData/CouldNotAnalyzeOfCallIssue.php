<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\Issue\RuntimeData;

use PhpParser\Node;
use Psalm\CodeLocation;
use Psalm\Issue\CodeIssue;
use Psalm\StatementsSource;

final class CouldNotAnalyzeOfCallIssue extends CodeIssue
{
    public function __construct(CodeLocation $code_location)
    {
        parent::__construct(
            message: 'RuntimeData::of call could not be analyzed because array value is not literal',
            code_location: $code_location
        );
    }

    public static function from(StatementsSource $source, Node\Expr\StaticCall $method_call): self
    {
        return new self(new CodeLocation($source, $method_call));
    }
}
