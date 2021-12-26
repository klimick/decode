<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\Helper\Runtype\Resolver;

use Fp\Functional\Option\Option;
use Klimick\PsalmDecode\Helper\Runtype\TypeResolver;
use Klimick\PsalmDecode\Helper\Runtype\TypeResolverInterface;
use Klimick\PsalmTest\Integration\Psalm;
use PhpParser\Node;
use Psalm\Type\Atomic\TKeyedArray;
use Psalm\Type\Union;
use function Fp\Evidence\proveNonEmptyList;
use function Fp\Evidence\proveOf;

final class ShapeTypeResolver implements TypeResolverInterface
{
    public function __invoke(Node\Expr $expr, TypeResolver $resolver): Option
    {
        return Option::do(function() use ($expr, $resolver) {
            $func_call = yield proveOf($expr, Node\Expr\FuncCall::class);
            $func_name = yield Psalm::getFunctionName($func_call)
                ->filter(fn($func_name) => in_array($func_name, [
                    'Klimick\Decode\Decoder\tuple',
                    'Klimick\Decode\Decoder\shape',
                    'Klimick\Decode\Decoder\partialShape',
                ]));

            $call_args = yield proveNonEmptyList($func_call->args);
            $partial = $func_name === 'Klimick\Decode\Decoder\partialShape';

            return yield self::resolveShapeArgs($partial, $call_args, $resolver);
        });
    }

    /**
     * @param non-empty-list<Node\Arg|Node\VariadicPlaceholder> $args
     * @return Option<Union>
     */
    private static function resolveShapeArgs(bool $partial, array $args, TypeResolver $resolver): Option
    {
        return Option::do(function() use ($partial, $args, $resolver) {
            $types = [];
            $is_list = true;

            foreach ($args as $offset => $arg) {
                $arg = yield proveOf($arg, Node\Arg::class);

                $name = Psalm::getArgName($arg)->getOrElse($offset);
                $type = yield $resolver($arg->value);

                if (is_string($name) || $type->possibly_undefined || $partial) {
                    $is_list = false;
                }

                if ($partial) {
                    $type->possibly_undefined = true;
                }

                $types[$name] = $type;
            }

            $keyed_array = new TKeyedArray($types);
            $keyed_array->is_list = $is_list;
            $keyed_array->sealed = $is_list;

            return new Union([$keyed_array]);
        });
    }
}
