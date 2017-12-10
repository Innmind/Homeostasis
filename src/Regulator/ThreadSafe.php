<?php
declare(strict_types = 1);

namespace Innmind\Homeostasis\Regulator;

use Innmind\Homeostasis\{
    Regulator,
    Strategy,
    Exception\HomeostasisAlreadyInProcess
};
use Symfony\Component\Lock\{
    Factory,
    Store\FlockStore
};

final class ThreadSafe implements Regulator
{
    private $regulate;
    private $lock;

    public function __construct(Regulator $regulator)
    {
        $this->regulate = $regulator;
        $this->lock = new Factory(new FlockStore);
    }

    public function __invoke(): Strategy
    {
        $lock = $this->lock->createLock('homeostasis');

        if (!$lock->acquire()) {
            throw new HomeostasisAlreadyInProcess;
        }

        try {
            $strategy = ($this->regulate)();
        } finally {
            $lock->release();
        }

        return $strategy;
    }
}
