<?php

declare(strict_types=1);

namespace Klimick\Decode\Error;

/**
 * @psalm-import-type ContextFor from Context
 *
 * @template T of ContextFor
 * @psalm-immutable
 */
interface ErrorInterface
{
    /**
     * @return Context<T>
     */
    public function context(): Context;
}
