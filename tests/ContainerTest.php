<?php
declare(strict_types = 1);

namespace Tests\Innmind\Homeostasis;

use Innmind\Homeostasis\{
    Regulator\Regulator,
    Regulator\ThreadSafe,
    Factor,
    Actuator
};
use Innmind\Compose\{
    ContainerBuilder\ContainerBuilder,
    Loader\Yaml
};
use Innmind\Url\Path;
use Innmind\Filesystem\Adapter;
use Innmind\TimeContinuum\{
    TimeContinuumInterface,
    ElapsedPeriod
};
use Innmind\Immutable\{
    Map,
    Set
};
use PHPUnit\Framework\TestCase;

class ContainerTest extends TestCase
{
    public function testDefault()
    {
        $container = (new ContainerBuilder(new Yaml))(
            new Path('container.yml'),
            (new Map('string', 'mixed'))
                ->put('factors', Set::of(Factor::class))
                ->put('stateFilesystem', $this->createMock(Adapter::class))
                ->put('clock', $this->createMock(TimeContinuumInterface::class))
                ->put('actuator', $this->createMock(Actuator::class))
        );

        $this->assertInstanceOf(Regulator::class, $container->get('default'));
    }

    public function testRegulator()
    {
        $container = (new ContainerBuilder(new Yaml))(
            new Path('container.yml'),
            (new Map('string', 'mixed'))
                ->put('factors', Set::of(Factor::class))
                ->put('stateFilesystem', $this->createMock(Adapter::class))
                ->put('actionFilesystem', $this->createMock(Adapter::class))
                ->put('clock', $this->createMock(TimeContinuumInterface::class))
                ->put('actuator', $this->createMock(Actuator::class))
                ->put('maxHistory', new ElapsedPeriod(42))
                ->put('minHistory', new ElapsedPeriod(42))
        );

        $this->assertInstanceOf(ThreadSafe::class, $container->get('regulator'));
    }
}
