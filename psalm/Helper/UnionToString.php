<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\Helper;

use Psalm\Type\Atomic;
use Psalm\Type\Union;
use function Fp\Collection\map;

final class UnionToString
{
    public static function for(Union $union): string
    {
        return implode('|', map($union->getAtomicTypes(), fn(Atomic $atomic) => match (true) {
            $atomic instanceof Atomic\TNamedObject => self::forNamedObject($atomic),
            $atomic instanceof Atomic\TArray => self::forArray($atomic),
            $atomic instanceof Atomic\TList => self::forList($atomic),
            default => $atomic->getId(),
        }));
    }

    private static function forNamedObject(Atomic\TNamedObject $named_object): string
    {
        return $named_object instanceof Atomic\TGenericObject
            ? self::forGenericObject($named_object)
            : "\\{$named_object->getId()}";
    }

    private static function forGenericObject(Atomic\TGenericObject $generic_object): string
    {
        $type_params = implode(', ', map($generic_object->type_params, fn(Union $u) => self::for($u)));
        return "\\{$generic_object->getId()}<$type_params>";
    }

    private static function forList(Atomic\TList $list): string
    {
        $type_param = self::for($list->type_param);

        return $list instanceof Atomic\TNonEmptyList
            ? "non-empty-list<{$type_param}>"
            : "list<{$type_param}>";
    }

    private static function forArray(Atomic\TArray $array): string
    {
        $type_params = $array->type_params[0]->getId() . ',' . self::for($array->type_params[1]);

        return $array instanceof Atomic\TNonEmptyArray
            ? "non-empty-array<{$type_params}>"
            : "array<{$type_params}";
    }
}
