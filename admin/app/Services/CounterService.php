<?php

declare(strict_types=1);

class CounterService
{
    public function __construct(private CounterRepository $repo)
    {
    }

    public function formatted(): string
    {
        return number_format($this->repo->get(), 0, ',', '.');
    }
}
