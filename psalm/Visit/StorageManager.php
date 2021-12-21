<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\Visit;

use Psalm\Internal\MethodIdentifier;
use Psalm\Storage\ClassLikeStorage;
use Psalm\Storage\FunctionLikeParameter;
use Psalm\Storage\MethodStorage;
use Psalm\Type\Union;

/**
 * @psalm-type TemplateName = string
 * @psalm-type MethodId = string
 */
final class StorageManager
{
    /**
     * @param list<FunctionLikeParameter> $with_params
     * @param null|array<TemplateName, non-empty-array<MethodId, Union>> $with_templates
     */
    public static function addMethod(
        string $named_as,
        ClassLikeStorage $to,
        array $with_params,
        array $with_templates = null,
        Union $with_return_type = null,
    ): void
    {
        $method = new MethodStorage();
        $method->cased_name = $named_as;
        $method->return_type = $with_return_type;
        $method->template_types = $with_templates;
        $method->setParams($with_params);
        $method->required_param_count = count($with_params);

        $name_lc = strtolower($named_as);

        $to->declaring_method_ids[$name_lc] = new MethodIdentifier($to->name, $name_lc);
        $to->appearing_method_ids[$name_lc] = new MethodIdentifier($to->name, $name_lc);
        $to->methods[$name_lc] = $method;
    }

    public static function makeImmutable(ClassLikeStorage $storage): void
    {
        $storage->mutation_free = true;
        $storage->external_mutation_free = true;
        $storage->specialize_instance = true;
    }
}
