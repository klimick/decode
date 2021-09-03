<?php /** @noinspection PhpUnusedAliasInspection */

declare(strict_types=1);

namespace Klimick\Decode;

use Fp\Functional\Either\Either;
use Fp\Functional\Semigroup\Semigroup;
use Klimick\Decode\Decoder\Invalid;
use Klimick\Decode\Decoder\Valid;

/**
 * @template TValid of Valid
 * @extends Semigroup<Either<Invalid, TValid>>
 * @psalm-immutable
 */
final class DecodeSemigroup extends Semigroup
{
    /**
     * @param Semigroup<TValid> $validSemigroup
     */
    public function __construct(private Semigroup $validSemigroup) { }

    public function combine(mixed $lhs, mixed $rhs): mixed
    {
        if ($lhs->isRight() && $rhs->isRight()) {
            $valid = $this->validSemigroup->combine(
                $lhs->get(),
                $rhs->get(),
            );

            return Either::right($valid);
        }

        if ($lhs->isLeft() && $rhs->isLeft()) {
            $invalid = new Invalid([
                ...$lhs->get()->errors,
                ...$rhs->get()->errors,
            ]);

            return Either::left($invalid);
        }

        return $rhs->isLeft() ? $rhs : $lhs;
    }
}
