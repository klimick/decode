<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\ShapeDecoder;

use SimpleXMLElement;
use Psalm\Type;
use Psalm\IssueBuffer;
use Psalm\Plugin\RegistrationInterface;
use Psalm\Plugin\PluginEntryPointInterface;
use Psalm\Internal\Analyzer\StatementsAnalyzer;
use Psalm\Plugin\EventHandler\Event\FunctionReturnTypeProviderEvent;
use Psalm\Plugin\EventHandler\FunctionReturnTypeProviderInterface;
use Klimick\PsalmDecode\DecodeIssue;
use Klimick\PsalmDecode\NamedArguments\DecoderTypeParamExtractor;
use Fp\Functional\Option\Option;
use function Fp\Evidence\proveOf;

final class IntersectionReturnTypeProvider implements FunctionReturnTypeProviderInterface, PluginEntryPointInterface
{
    public function __invoke(RegistrationInterface $registration, ?SimpleXMLElement $config = null): void
    {
        $registration->registerHooksFromClass(self::class);
    }

    public static function getFunctionIds(): array
    {
        return ['klimick\decode\intersection'];
    }

    public static function getFunctionReturnType(FunctionReturnTypeProviderEvent $event): ?Type\Union
    {
        $type = Option::do(function() use ($event) {
            $source = yield proveOf($event->getStatementsSource(), StatementsAnalyzer::class);
            $provider = $source->getNodeTypeProvider();
            $codebase = $source->getCodebase();

            $properties = [];
            $collisions = [];

            foreach ($event->getCallArgs() as $arg) {
                $arg_type = yield Option::of($provider->getType($arg->value));

                $decoder_type_param = yield DecoderTypeParamExtractor::extract($arg_type, $source, $codebase);
                $shape_type = yield ShapePropertiesExtractor::fromDecoderTypeParam($decoder_type_param);

                foreach ($shape_type as $property => $type) {
                    if (array_key_exists($property, $properties)) {
                        $collisions[] = $property;
                    }

                    $properties[$property] = $type;
                }
            }

            if (!empty($collisions)) {
                $issue = DecodeIssue::intersectionCollision($collisions, $event->getCodeLocation());
                IssueBuffer::accepts($issue);
            }

            return ShapeDecoderType::create($properties);
        });

        return $type->get();
    }
}
