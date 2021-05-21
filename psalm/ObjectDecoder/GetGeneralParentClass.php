<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\ObjectDecoder;

use Psalm\Codebase;
use Fp\Functional\Option\Option;

final class GetGeneralParentClass
{
    /**
     * @return Option<string>
     */
    public static function for(string $class, Codebase $codebase): Option
    {
        return Option::do(function() use ($class, $codebase) {
            $type_metadata = yield Option::try(fn() => $codebase->classlike_storage_provider->get($class));
            $parent_class = $type_metadata->parent_class;

            return null !== $parent_class
                ? yield self::for($parent_class, $codebase)
                : $class;
        });
    }
}
