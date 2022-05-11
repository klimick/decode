<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\Hook\AfterClassLikeAnalysis;

final class AnalysisQueue
{
    /**
     * @var array<int, array{
     *     deps: array<int, string>,
     *     deferred: DeferredAnalysis
     * }>
     */
    private array $queue = [];

    private static AnalysisQueue|null $instance = null;

    public static function instance(): AnalysisQueue
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @param list<string> $deps
     */
    public function push(DeferredAnalysis $deferred, array $deps): void
    {
        array_unshift($this->queue, [
            'deps' => $deps,
            'deferred' => $deferred,
        ]);
    }

    public function popIndependent(): void
    {
        foreach ($this->queue as $queueKey => $queued) {
            if (!empty($queued['deps'])) {
                continue;
            }

            $queued['deferred']->run();
            unset($this->queue[$queueKey]);
        }
    }

    public function classVisited(string $visited): void
    {
        foreach ($this->queue as $queuedKey => $queued) {
            foreach ($queued['deps'] as $depKey => $dep) {
                if ($visited !== $dep) {
                    continue;
                }

                unset($this->queue[$queuedKey]['deps'][$depKey]);
            }
        }
    }
}
