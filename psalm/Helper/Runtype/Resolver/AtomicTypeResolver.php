<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\Helper\Runtype\Resolver;

use DateTimeImmutable;
use Klimick\PsalmDecode\Helper\Runtype\TypeResolver;
use Klimick\PsalmDecode\Helper\Runtype\TypeResolverInterface;
use PhpParser\Node;
use Psalm\Type;
use Klimick\PsalmTest\Integration\Psalm;
use Fp\Functional\Option\Option;
use function Fp\Evidence\proveOf;

final class AtomicTypeResolver implements TypeResolverInterface
{
    public function __invoke(Node\Expr $expr, TypeResolver $resolver): Option
    {
        return proveOf($expr, Node\Expr\FuncCall::class)
            ->flatMap(fn($expr) => Psalm::getFunctionName($expr))
            ->flatMap(fn($func) => Option::fromNullable(
                match($func) {
                    'Klimick\Decode\Decoder\mixed' => Type::getMixed(),
                    'Klimick\Decode\Decoder\null' => Type::getNull(),
                    'Klimick\Decode\Decoder\int' => Type::getInt(),
                    'Klimick\Decode\Decoder\positiveInt' => Type::getPositiveInt(),
                    'Klimick\Decode\Decoder\float' => Type::getFloat(),
                    'Klimick\Decode\Decoder\numeric' => Type::getNumeric(),
                    'Klimick\Decode\Decoder\numericString' => Type::getNumericString(),
                    'Klimick\Decode\Decoder\bool' => Type::getBool(),
                    'Klimick\Decode\Decoder\string' => Type::getString(),
                    'Klimick\Decode\Decoder\nonEmptyString' => Type::getNonEmptyString(),
                    'Klimick\Decode\Decoder\scalar' => Type::getScalar(),
                    'Klimick\Decode\Decoder\arrKey' => Type::getArrayKey(),
                    'Klimick\Decode\Decoder\datetime' => new Type\Union([
                        new Type\Atomic\TNamedObject(DateTimeImmutable::class),
                    ]),
                    default => null,
                }
            ));
    }
}
