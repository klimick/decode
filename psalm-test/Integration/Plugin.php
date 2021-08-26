<?php

declare(strict_types=1);

namespace Klimick\PsalmTest\Integration;

use Klimick\PsalmTest\Integration\Hook\GenericObjectReturnTypeProvider;
use Klimick\PsalmTest\Integration\Hook\OptionalReturnTypeProvider;
use Klimick\PsalmTest\Integration\Hook\ShapeReturnTypeProvider;
use Klimick\PsalmTest\Integration\Hook\TestCaseAnalysis;
use Psalm\Plugin\PluginEntryPointInterface;
use Psalm\Plugin\RegistrationInterface;
use SimpleXMLElement;

final class Plugin implements PluginEntryPointInterface
{
    public function __invoke(RegistrationInterface $registration, ?SimpleXMLElement $config = null): void
    {
        $register = function(string $hook) use ($registration): void {
            class_exists($hook);
            $registration->registerHooksFromClass($hook);
        };

        $register(ShapeReturnTypeProvider::class);
        $register(GenericObjectReturnTypeProvider::class);
        $register(OptionalReturnTypeProvider::class);
        $register(TestCaseAnalysis::class);
    }
}
