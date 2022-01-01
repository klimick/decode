<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Static\Fixtures\Messenger;

use Klimick\Decode\Decoder\SumType;
use Klimick\Decode\Decoder\SumCases;
use function Klimick\Decode\Decoder\cases;

final class Messenger extends SumType
{
    protected static function definition(): SumCases
    {
        return cases(
            smpp: SmppSms::type(),
            telegram: Telegram::type(),
            whatsapp: Whatsapp::type(),
        );
    }
}
