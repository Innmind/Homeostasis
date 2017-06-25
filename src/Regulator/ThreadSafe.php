<?php
declare(strict_types = 1);

namespace Innmind\Homeostasis\Regulator;

use Innmind\Homeostasis\{
    Regulator,
    Strategy,
    Exception\HomeostasisAlreadyInProcess
};
use Symfony\Component\Filesystem\LockHandler;

final class ThreadSafe implements Regulator
{
    private $regulate;

    public function __construct(Regulator $regulator)
    {
        $this->regulate = $regulator;
    }

    public function __invoke(): Strategy
    {
        $handler = new LockHandler('homeostasis');

        if (!$handler->lock()) {
            throw new HomeostasisAlreadyInProcess;
        }

        $strategy = ($this->regulate)();
        $handler->release();

        return $strategy;
    }
}
