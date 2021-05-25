<?php

declare(strict_types=1);

namespace Klimick\Decode;

class Typed
{
    public const null = 'Klimick\Decode\Decoder\null';
    public const int = 'Klimick\Decode\Decoder\int';
    public const positiveInt = 'Klimick\Decode\Decoder\positiveInt';
    public const float = 'Klimick\Decode\Decoder\float';
    public const numeric = 'Klimick\Decode\Decoder\numeric';
    public const bool = 'Klimick\Decode\Decoder\bool';
    public const string = 'Klimick\Decode\Decoder\string';
    public const nonEmptyString = 'Klimick\Decode\Decoder\nonEmptyString';
    public const numericString = 'Klimick\Decode\Decoder\numericString';
    public const scalar = 'Klimick\Decode\Decoder\scalar';
    public const arrKey = 'Klimick\Decode\Decoder\arrKey';
    public const mixed = 'Klimick\Decode\Decoder\mixed';
}
