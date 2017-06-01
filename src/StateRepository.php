<?php
declare(strict_types = 1);

namespace Innmind\Homeostasis;

interface StateRepository
{
    public function add(State $state): self;
    public function get(Identity $identity): State;
    public function remove(Identity $identity): self;

    /**
     * @return SetInterface<State>
     */
    public function all(): SetInterface;
}
