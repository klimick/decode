<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\ObjectDecoder;

use Fp\Functional\Option\Option;
use Psalm\Codebase;

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
