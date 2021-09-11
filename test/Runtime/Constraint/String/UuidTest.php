<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Runtime\Constraint\String;

use Klimick\Decode\Test\Helper\Gen;
use Klimick\Decode\Test\Runtime\Constraint\Helper\Check;
use PHPUnit\Framework\TestCase;
use function Klimick\Decode\Constraint\uuid;
use function Klimick\Decode\Test\Helper\eris;

final class UuidTest extends TestCase
{
    /**
     * @dataProvider validUuidProvider
     */
    public function testValid(string $uuid): void
    {
        Check::isValid()
            ->forConstraint(uuid())
            ->withValue($uuid);
    }

    public function testInvalid(): void
    {
        eris()->forAll(Gen::nonEmptyString())
            ->then(fn(string $string) => Check::isInvalid()
                ->forConstraint(uuid())
                ->withValue($string));
    }

    /**
     * @return list<array{string}>
     */
    public function validUuidProvider(): array
    {
        return [
            ['ab5133fb-db20-405d-bab2-733a78f870ec'],
            ['3c3eed46-4a8a-4a14-ac1e-62e8920ea061'],
            ['52a8d3d3-45e5-4c7c-b460-671323859637'],
            ['79ddd4a4-58cd-4d5a-952e-74eb0d5786da'],
            ['b08603ea-8f9e-422e-9d7f-457e3a96e0d0'],
            ['ea273638-d70e-444a-b13a-13db5468e891'],
            ['e94a81b8-df08-4468-99b9-4c5afbcb9c34'],
            ['296D1119-01AC-4322-ABFA-55C657C686D6'],
            ['B249227E-7B3E-4A6F-A50F-972CFB7B3074'],
            ['2CA50F2E-F475-42B2-8959-205AC7F4F774'],
            ['2BAE1A6B-1202-42A0-A463-AE711F4421D2'],
            ['14FBFE45-C5C4-42E2-B518-73574590C20C'],
            ['12268D4C-685F-4BA6-83D1-024952339281'],
            ['7EC7C24B-4DAA-441E-AB4B-CF124D46C783'],
            ['1D18EB57-2154-4EED-B6D6-057BD72508D6'],
            ['F9C8752C-8DF2-45B1-B8F3-2848918C4192'],
            ['2A4AFE18-B1EC-4D93-B875-28D95A1C56C9'],
        ];
    }
}
