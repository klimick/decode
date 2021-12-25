<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\HighOrder;

use Fp\Functional\Option\Option;
use Klimick\Decode\Decoder\AbstractDecoder;
use Klimick\Decode\Decoder\DecoderInterface;
use Klimick\Decode\HighOrder\Brand\FromBrand;
use Klimick\Decode\HighOrder\Brand\DefaultBrand;
use Klimick\Decode\HighOrder\Brand\OptionalBrand;
use Klimick\PsalmDecode\Issue\HighOrder\BrandAlreadyDefinedIssue;
use Klimick\PsalmDecode\Issue\HighOrder\OptionalCallContradictionIssue;
use Klimick\PsalmTest\Integration\Psalm;
use PhpParser\Node\Expr\MethodCall;
use Psalm\CodeLocation;
use Psalm\IssueBuffer;
use Psalm\Plugin\EventHandler\AfterMethodCallAnalysisInterface;
use Psalm\Plugin\EventHandler\Event\AfterMethodCallAnalysisEvent;
use Psalm\StatementsSource;
use Psalm\Type;
use function Fp\Cast\asList;
use function Fp\Collection\map;
use function Fp\Evidence\proveOf;
use function Fp\Evidence\proveTrue;

final class DecoderMethodsAnalysis implements AfterMethodCallAnalysisInterface
{
    private const METHODS_TO_BRANDS = [
        self::METHOD_OPTIONAL => OptionalBrand::class,
        self::METHOD_FROM => FromBrand::class,
        self::METHOD_DEFAULT => DefaultBrand::class,
    ];

    private const METHOD_OPTIONAL = AbstractDecoder::class . '::' . 'optional';
    private const METHOD_FROM = AbstractDecoder::class . '::' . 'from';
    private const METHOD_DEFAULT = AbstractDecoder::class . '::' . 'default';

    public static function getClassLikeNames(): array
    {
        return [AbstractDecoder::class];
    }

    public static function afterMethodCallAnalysis(AfterMethodCallAnalysisEvent $event): void
    {
        Option::do(function() use ($event) {
            $source = $event->getStatementsSource();
            $method_name = $event->getAppearingMethodId();

            yield proveTrue(in_array($method_name, array_keys(self::METHODS_TO_BRANDS), true));

            $method_call = yield proveOf($event->getExpr(), MethodCall::class);
            $code_location = new CodeLocation($event->getStatementsSource(), $method_call->name);

            $return_type = yield Psalm::getType($event, $method_call->var)
                ->flatMap(fn($decoder_type) => Psalm::asSingleAtomicOf(Type\Atomic\TNamedObject::class, $decoder_type))
                ->map(fn($decoder_atomic) => self::withBrand($source, $code_location, $decoder_atomic, $method_name))
                ->map(fn($branded_decoder_atomic) => new Type\Union([$branded_decoder_atomic]));

            $event->setReturnTypeCandidate($return_type);
        });
    }

    /**
     * @psalm-param DecoderMethodsAnalysis::METHOD_* $method_name
     */
    private static function withBrand(
        StatementsSource $source,
        CodeLocation $code_location,
        Type\Atomic\TNamedObject $atomic,
        string $method_name,
    ): Type\Atomic\TNamedObject
    {
        $current_brands = map(asList($atomic->getIntersectionTypes() ?? []), fn(Type\Atomic $a) => $a->getId());
        $brand = self::METHODS_TO_BRANDS[$method_name];

        if (self::hasBrandContradiction($brand, $current_brands)) {
            $issue = new OptionalCallContradictionIssue($code_location);
            IssueBuffer::accepts($issue, $source->getSuppressedIssues());
        }

        if (in_array($brand, $current_brands, true)) {
            $issue = new BrandAlreadyDefinedIssue($method_name, $code_location);
            IssueBuffer::accepts($issue, $source->getSuppressedIssues());
        }

        $brand_type = new Type\Atomic\TNamedObject($brand);
        $brand_type->from_docblock = true;

        $with_brand = clone $atomic;
        $with_brand->addIntersectionType($brand_type);

        return $with_brand;
    }

    private static function hasBrandContradiction(string $brand, array $current_brands): bool
    {
        return
            ($brand === DefaultBrand::class && in_array(OptionalBrand::class, $current_brands, true)) ||
            ($brand === OptionalBrand::class && in_array(DefaultBrand::class, $current_brands, true));
    }
}
