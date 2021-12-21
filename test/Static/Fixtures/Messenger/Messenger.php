<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Static\Fixtures\Messenger;

use Klimick\Decode\Decoder\SumType;
use Klimick\Decode\Decoder\SumCases;
use function Klimick\Decode\Decoder\cases;
use function Klimick\Decode\Decoder\productType;

final class Messenger extends SumType
{
    protected static function definition(): SumCases
    {
        return cases(
            smpp: productType(SmppSms::class),
            telegram: productType(Telegram::class),
            whatsapp: productType(Whatsapp::class),
        );
    }
}
