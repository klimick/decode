<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Helper;

use Eris\Facade;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class ErisFacade extends Facade
{
    /**
     * @var array{
     *     method: array{eris-repeat?: array{int}, eris-ratio?: array{int}}
     * }
     */
    private array $options = [];

    public function __construct(int $repeat = null, int $ratio = null)
    {
        if (null !== $repeat) {
            $this->options['method']['eris-repeat'] = [$repeat];
        }

        if (null !== $ratio) {
            $this->options['method']['eris-ratio'] = [$ratio];
        }

        parent::__construct();
    }

    protected function getAnnotations(): array
    {
        return $this->options;
    }
}
