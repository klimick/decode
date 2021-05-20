<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\ObjectDecoder;

use Fp\Functional\Option\Option;
use Klimick\PsalmDecode\NamedArguments\DecoderTypeParamExtractor;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\ClassMethod;
use Psalm\Codebase;
use Psalm\NodeTypeProvider;
use Psalm\Plugin\EventHandler\AfterFunctionLikeAnalysisInterface;
use Psalm\Plugin\EventHandler\Event\AfterFunctionLikeAnalysisEvent;
use Psalm\Plugin\PluginEntryPointInterface;
use Psalm\Plugin\RegistrationInterface;
use Psalm\Type\Union;
use SimpleXMLElement;
use function Fp\Cast\asList;
use function Fp\Evidence\proveOf;
use function Fp\Evidence\proveString;
use function Fp\Evidence\proveTrue;

final class RuntimeDataPseudoPropertiesAnalysis implements AfterFunctionLikeAnalysisInterface, PluginEntryPointInterface
{
    public function __invoke(RegistrationInterface $registration, ?SimpleXMLElement $config = null): void
    {
        $registration->registerHooksFromClass(self::class);
    }

    public static function afterStatementAnalysis(AfterFunctionLikeAnalysisEvent $event): ?bool
    {
        $analysis = Option::do(function() use ($event) {
            $stmt = $event->getStmt();
            $context = $event->getContext();
            $codebase = $event->getCodebase();
            $type_provider = $event->getNodeTypeProvider();

            $class_name = yield proveString($context->self);
            $class_method = yield proveOf($stmt, ClassMethod::class);

            $properties = yield self::getPseudoProperties($codebase, $class_method, $type_provider);

            $class_storage = yield Option::try(fn() => $codebase->classlike_storage_provider->get($class_name));
            $class_storage->sealed_properties = true;

            foreach ($properties as $name => $type) {
                $class_storage->pseudo_property_get_types['$' . $name] = $type;
            }

            $file_path = yield Option::of($class_storage->location?->file_path);
            $codebase->cacheClassLikeStorage($class_storage, $file_path);
        });

        return $analysis->get();
    }

    /**
     * @return Option<array<string, Union>>
     */
    private static function getPseudoProperties(
        Codebase $codebase,
        ClassMethod $class_method,
        NodeTypeProvider $type_provider,
    ): Option
    {
        return Option::do(function() use ($codebase, $class_method, $type_provider) {
            yield proveTrue(null !== $class_method->stmts);

            $statements = asList($class_method->stmts);
            yield proveTrue(1 === count($statements));

            $return = yield proveOf($statements[0], Stmt\Return_::class);
            $object_factory_call = yield proveOf($return->expr, FuncCall::class);

            $object_func_call = yield proveOf($object_factory_call->name, FuncCall::class);
            $object_func_name = yield proveOf($object_func_call->name, Name::class);
            yield proveTrue('Klimick\Decode\object' === $object_func_name->getAttribute('resolvedName'));

            $pseudo_properties = [];

            foreach ($object_factory_call->args as $arg) {
                $property_name = yield Option::of($arg->name?->name);
                $property_type = yield Option::of($type_provider->getType($arg->value));

                $pseudo_properties[$property_name] = yield DecoderTypeParamExtractor::extract($property_type, $codebase);
            }

            return $pseudo_properties;
        });
    }
}
