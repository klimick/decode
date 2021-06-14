<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\HighOrder;

use Fp\Functional\Option\Option;
use Klimick\Decode\Decoder\AbstractDecoder;
use Klimick\Decode\HighOrder\Brand\FromBrand;
use Klimick\Decode\HighOrder\Brand\ConstrainedBrand;
use Klimick\Decode\HighOrder\Brand\DefaultBrand;
use Klimick\Decode\HighOrder\Brand\OptionalBrand;
use Klimick\PsalmDecode\DecodeIssue;
use Klimick\PsalmDecode\Psalm;
use PhpParser\Node\Expr\MethodCall;
use Psalm\CodeLocation;
use Psalm\IssueBuffer;
use Psalm\Plugin\EventHandler\AfterMethodCallAnalysisInterface;
use Psalm\Plugin\EventHandler\Event\AfterMethodCallAnalysisEvent;
use Psalm\Type;
use function Fp\Cast\asList;
use function Fp\Collection\map;
use function Fp\Evidence\proveOf;
use function Fp\Evidence\proveTrue;

final class DecoderMethodsAnalysis implements AfterMethodCallAnalysisInterface
{
    private const METHODS_TO_BRANDS = [
        self::METHOD_OPTIONAL => OptionalBrand::class,
        self::METHOD_CONSTRAINED => ConstrainedBrand::class,
        self::METHOD_FROM => FromBrand::class,
        self::METHOD_DEFAULT => DefaultBrand::class,
    ];

    private const METHOD_OPTIONAL = 'optional';
    private const METHOD_CONSTRAINED = 'constrained';
    private const METHOD_FROM = 'from';
    private const METHOD_DEFAULT = 'default';

    public static function getClassLikeNames(): array
    {
        return [AbstractDecoder::class];
    }

    public static function afterMethodCallAnalysis(AfterMethodCallAnalysisEvent $event): void
    {
        Option::do(function() use ($event) {
            $source = $event->getStatementsSource();
            $type_provider = $source->getNodeTypeProvider();

            $method_id = explode('::', $event->getAppearingMethodId());

            yield proveTrue(2 === count($method_id));
            [$class_name, $method_name] = $method_id;

            yield proveTrue($class_name === AbstractDecoder::class);
            yield proveTrue(in_array($method_name, array_keys(self::METHODS_TO_BRANDS), true));

            $method_call = yield proveOf($event->getExpr(), MethodCall::class);
            $current_type = yield Psalm::getType($type_provider, $method_call->var);

            $code_location = new CodeLocation($event->getStatementsSource(), $method_call->name);

            $return_type = yield Option::fromNullable($current_type)
                ->flatMap(fn($decoder_type) => self::simplifyToAtomic($decoder_type))
                ->map(fn($decoder_atomic) => self::withBrand($code_location, $method_name, $decoder_atomic))
                ->map(fn($branded_decoder_atomic) => self::possiblyUndefinedIfHasOptionalBrand($branded_decoder_atomic))
                ->map(fn($branded_decoder_atomic) => new Type\Union([$branded_decoder_atomic]));

            $event->setReturnTypeCandidate($return_type);
        });
    }

    /**
     * @return Option<Type\Atomic\TGenericObject>
     */
    private static function simplifyToAtomic(Type\Union $decoder_type): Option
    {
        return Option::do(function() use ($decoder_type) {
            $atomics = asList($decoder_type->getAtomicTypes());
            yield proveTrue(1 === count($atomics));

            $decoder_atomic = yield proveOf($atomics[0], Type\Atomic\TGenericObject::class);
            yield proveTrue($decoder_atomic->value === AbstractDecoder::class);
            yield proveTrue(1 === count($decoder_atomic->type_params));

            return $decoder_atomic;
        });
    }

    /**
     * @psalm-param DecoderMethodsAnalysis::METHOD_* $method_name
     */
    private static function withBrand(CodeLocation $code_location, string $method_name, Type\Atomic\TGenericObject $atomic): Type\Atomic\TGenericObject
    {
        $with_brand = clone $atomic;
        $current_brands = map(asList($atomic->getIntersectionTypes() ?? []), fn(Type\Atomic $a) => $a->getId());
        $brand = self::METHODS_TO_BRANDS[$method_name];

        if (self::hasBrandContradiction($brand, $current_brands)) {
            IssueBuffer::accepts(DecodeIssue::usingDefaultAndOptionalHasNoSense($code_location));
        }

        in_array($brand, $current_brands, true)
            ? IssueBuffer::accepts(DecodeIssue::brandAlreadyDefined($method_name, $code_location))
            : $with_brand->addIntersectionType(new Type\Atomic\TNamedObject($brand));

        return $with_brand;
    }

    private static function possiblyUndefinedIfHasOptionalBrand(Type\Atomic\TGenericObject $atomic): Type\Atomic\TGenericObject
    {
        if (array_key_exists(OptionalBrand::class, $atomic->extra_types ?? [])) {
            $cloned = clone $atomic;
            $cloned->type_params[0]->possibly_undefined = true;

            return $cloned;
        }

        return $atomic;
    }

    private static function hasBrandContradiction(string $brand, array $current_brands): bool
    {
        return
            ($brand === DefaultBrand::class && in_array(OptionalBrand::class, $current_brands, true)) ||
            ($brand === OptionalBrand::class && in_array(DefaultBrand::class, $current_brands, true));
    }
}
