<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\Hook\FunctionReturnTypeProvider;

use Fp\Functional\Option\Option;
use Klimick\PsalmDecode\Helper\DecoderType;
use Klimick\PsalmDecode\Issue\Object\IntersectionCollisionIssue;
use Fp\PsalmToolkit\Toolkit\PsalmApi;
use Psalm\IssueBuffer;
use Psalm\Plugin\EventHandler\Event\FunctionReturnTypeProviderEvent;
use Psalm\Plugin\EventHandler\FunctionReturnTypeProviderInterface;
use Psalm\Type;

final class IntersectionReturnTypeProvider implements FunctionReturnTypeProviderInterface
{
    public static function getFunctionIds(): array
    {
        return ['klimick\decode\decoder\intersection'];
    }

    public static function getFunctionReturnType(FunctionReturnTypeProviderEvent $event): ?Type\Union
    {
        $type = Option::do(function() use ($event) {
            $properties = [];
            $collisions = [];

            foreach ($event->getCallArgs() as $arg) {
                $shape_type = yield PsalmApi::$types->getType($event, $arg->value)
                    ->flatMap(fn($type) => DecoderType::getShapeProperties($type));

                foreach ($shape_type as $property => $type) {
                    if (array_key_exists($property, $properties)) {
                        $collisions[] = $property;
                    }

                    $properties[$property] = $type;
                }
            }

            if (!empty($collisions)) {
                $source = $event->getStatementsSource();

                $issue = new IntersectionCollisionIssue($collisions, $event->getCodeLocation());
                IssueBuffer::accepts($issue, $source->getSuppressedIssues());
            }

            return DecoderType::createShapeDecoder($properties);
        });

        return $type->get();
    }
}
