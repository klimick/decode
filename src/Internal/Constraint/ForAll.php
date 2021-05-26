<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal\Constraint;

use Fp\Functional\Either\Either;
use Fp\Functional\Either\Left;
use Klimick\Decode\Constraint\ConstraintInterface;
use Klimick\Decode\Context;
use function Klimick\Decode\Constraint\invalids;
use function Klimick\Decode\Constraint\valid;

/**
 * @template TVal
 * @implements ConstraintInterface<list<TVal>>
 * @psalm-immutable
 */
final class ForAll implements ConstraintInterface
{
    /**
     * @param non-empty-list<ConstraintInterface<TVal>> $constraints
     */
    public function __construct(public array $constraints) { }

    public function name(): string
    {
        return 'FOR_ALL';
    }

    public function check(Context $context, mixed $value): Either
    {
        $errors = [];

        foreach ($value as $k => $v) {
            foreach ($this->constraints as $constraint) {
                $result = $constraint->check($context->append($constraint->name(), $v, (string) $k), $v);

                if ($result instanceof Left) {
                    $errors = [...$errors, ...$result->get()->errors];
                }
            }
        }

        return !empty($errors) ? invalids($errors) : valid();
    }
}
