<?php
declare(strict_types = 1);

namespace Tests\Innmind\Homeostasis;

use function Innmind\Homeostasis\bootstrap;
use Innmind\Homeostasis\{
    Factor,
    Actuator,
    Regulator,
    Regulator\ModulateStateHistory,
    Regulator\ThreadSafe,
};
use Innmind\Filesystem\Adapter;
use Innmind\TimeContinuum\TimeContinuumInterface;
use Innmind\Immutable\Set;
use PHPUnit\Framework\TestCase;

class BootstrapTest extends TestCase
{
    public function testBootstrap()
    {
        $homeostasis = bootstrap(
            Set::of(Factor::class),
            $this->createMock(Actuator::class),
            $this->createMock(Adapter::class),
            $this->createMock(TimeContinuumInterface::class)
        );

        $this->assertInstanceOf(Regulator\Regulator::class, $homeostasis['regulator']);
        $this->assertIsCallable($homeostasis['modulate_state_history']);
        $modulateStateHistory = $homeostasis['modulate_state_history'](
            $this->createMock(Adapter::class)
        );
        $this->assertIsCallable($modulateStateHistory);
        $this->assertInstanceOf(
            ModulateStateHistory::class,
            $modulateStateHistory($this->createMock(Regulator::class))
        );
        $this->assertIsCallable($homeostasis['thread_safe']);
        $this->assertInstanceOf(
            ThreadSafe::class,
            $homeostasis['thread_safe']($this->createMock(Regulator::class))
        );
    }
}
