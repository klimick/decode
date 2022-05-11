<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\Hook\AfterClassLikeAnalysis;

use Fp\Functional\Option\Option;
use Klimick\PsalmDecode\Helper\DecoderTypeParamExtractor;
use Klimick\PsalmDecode\PsalmInternal;
use Klimick\PsalmTest\Integration\Psalm;
use PhpParser\Node\Expr\FuncCall;
use Psalm\Context;
use Psalm\Internal\Analyzer\Statements\ExpressionAnalyzer;
use Psalm\Internal\Analyzer\StatementsAnalyzer;
use Psalm\Storage\ClassLikeStorage;
use Psalm\Type\Atomic\TKeyedArray;
use function Fp\Evidence\proveString;

final class DeferredAnalysis
{
    public function __construct(
        private StatementsAnalyzer $analyzer,
        private FuncCall $props_expr,
        private ClassLikeStorage $storage,
    ) {}

    public function run(): void
    {
        $this->analyse();
        $this->fillProperties();
    }

    /**
     * @psalm-suppress InternalClass
     * @psalm-suppress InternalMethod
     */
    private function analyse(): void
    {
        $old_populated = $this->storage->populated;
        $this->storage->populated = true;

        ExpressionAnalyzer::analyze($this->analyzer, $this->props_expr, self::createContext($this->storage->name));

        $this->storage->populated = $old_populated;
    }

    /**
     * @psalm-suppress InternalMethod
     */
    private function fillProperties(): void
    {
        Option::do(function() {
            $type_provider = $this->analyzer->getNodeTypeProvider();

            $decoder_union = yield Option::fromNullable($type_provider->getType($this->props_expr));
            $props_union = yield DecoderTypeParamExtractor::extract($decoder_union);
            $props_atomic = yield Psalm::asSingleAtomicOf(TKeyedArray::class, $props_union);

            $props = [];

            foreach ($props_atomic->properties as $key => $type) {
                $props[yield proveString($key)] = $type;
            }

            foreach ($props as $property_mame => $property_type) {
                $this->storage->pseudo_property_get_types['$' . $property_mame] = $property_type;
            }

            PsalmInternal::codebase()->cacheClassLikeStorage($this->storage, $this->analyzer->getFilePath());
        });
    }

    private static function createContext(string $self): Context
    {
        $context = new Context();
        $context->inside_general_use = true;
        $context->pure = true;
        $context->self = $self;

        return $context;
    }
}
