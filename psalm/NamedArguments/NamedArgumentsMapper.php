<?php /** @noinspection PhpInternalEntityUsedInspection */

declare(strict_types=1);

namespace Klimick\PsalmDecode\NamedArguments;

use Psalm\Type;
use Psalm\Codebase;
use Psalm\StatementsSource;
use Psalm\NodeTypeProvider;
use Psalm\Internal\Analyzer\StatementsAnalyzer;
use PhpParser\Node;
use Fp\Functional\Option\Option;
use Klimick\Decode\DecoderInterface;
use function Fp\Evidence\proveString;

final class NamedArgumentsMapper
{
    /**
     * @param list<Node\Arg> $call_args
     * @return Option<Type\Union>
     */
    public static function map(
        array $call_args,
        StatementsSource $source,
        NodeTypeProvider $provider,
        Codebase $codebase,
        bool $partial = false,
    ): Option
    {
        if (!($source instanceof StatementsAnalyzer)) {
            return Option::none();
        }

        return Option::do(function() use ($call_args, $provider, $source, $codebase, $partial) {
            $properties = [];

            foreach ($call_args as $arg) {
                $info = yield self::getPropertyInfo($arg, $provider, $source, $codebase);

                $properties[$info['property']] = $partial
                    ? self::asPossiblyUndefined($info['type'])
                    : $info['type'];
            }

            $mapped_shape = new Type\Union([
                empty($properties)
                    ? new Type\Atomic\TArray([Type::getEmpty(), Type::getEmpty()])
                    : new Type\Atomic\TKeyedArray($properties)
            ]);

            $inferred_decoder = new Type\Atomic\TGenericObject(DecoderInterface::class, [$mapped_shape]);

            return new Type\Union([$inferred_decoder]);
        });
    }

    /**
     * @psalm-type PropertyInfo = array{
     *     property: string,
     *     type: Type\Union
     * }
     *
     * @return Option<PropertyInfo>
     */
    private static function getPropertyInfo(
        Node\Arg $named_arg,
        NodeTypeProvider $provider,
        StatementsAnalyzer $source,
        Codebase $codebase,
    ): Option
    {
        return Option::do(function() use ($named_arg, $provider, $source, $codebase) {
            $named_arg_type = yield Option::of($provider->getType($named_arg->value));
            $arg_identifier = yield Option::of($named_arg->name);

            return [
                'property' => yield proveString($arg_identifier->name),
                'type' => yield DecoderTypeParamGetter::get($named_arg_type, $source, $codebase),
            ];
        });
    }

    private static function asPossiblyUndefined(Type\Union $union): Type\Union
    {
        $cloned = clone $union;
        $cloned->possibly_undefined = true;

        return $cloned;
    }
}
