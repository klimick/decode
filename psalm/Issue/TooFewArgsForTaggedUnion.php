<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\Issue;

use Closure;
use Fp\Functional\Option\Option;
use Psalm\Issue\CodeIssue;
use Psalm\IssueBuffer;
use Psalm\Plugin\EventHandler\Event\MethodReturnTypeProviderEvent;

final class TooFewArgsForTaggedUnion extends CodeIssue
{
    /**
     * @return Closure(): Option<never-return>
     */
    public static function raise(MethodReturnTypeProviderEvent $event): Closure
    {
        return function() use ($event) {
            $source = $event->getSource();

            $issue = new self(
                message: 'Too few args passed for tagged',
                code_location: $event->getCodeLocation(),
            );

            IssueBuffer::accepts($issue, $source->getSuppressedIssues());

            return Option::none();
        };
    }
}
