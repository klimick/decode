<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode;

use Fp\Functional\Option\Option;
use Psalm\Internal\Analyzer\ProjectAnalyzer;
use Psalm\Internal\Type\TypeExpander;
use Psalm\Storage\ClassLikeStorage;
use Psalm\Type\Union;

final class PsalmInternal
{
    /**
     * @return Option<ClassLikeStorage>
     * @psalm-suppress InternalMethod
     */
    public static function getStorageFor(string $classlike): Option
    {
        $codebase = ProjectAnalyzer::$instance->getCodebase();

        return Option::fromNullable($codebase->classlikes->getStorageFor($classlike));
    }

    /**
     * @psalm-suppress InternalMethod
     * @psalm-suppress InternalClass
     */
    public static function expandType(string $self_class, Union $type): Union
    {
        $codebase = ProjectAnalyzer::$instance->getCodebase();

        return TypeExpander::expandUnion(
            codebase: $codebase,
            return_type: $type,
            self_class: $self_class,
            static_class_type: null,
            parent_class: null,
        );
    }
}
