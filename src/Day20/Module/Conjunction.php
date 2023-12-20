<?php

/*
 * Copyright (c) Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Application\Day20\Module;

use Application\Day20\Enum\Pulse;
use Application\Day20\Signal;

class Conjunction extends Module
{
    /** @var array<string, Pulse> $memory */
    private array $memory = [];

    public function initMemory(string $origin): void
    {
        $this->memory[$origin] = Pulse::Low;
    }

    public function receive(Signal $signal): Signal|null
    {
        $this->memory[$signal->origin] = $signal->pulse;

        //~ Has register high pulse from all origins, so send low pulse. Otherwise, send high pulse
        $pulse = !in_array(Pulse::Low, $this->memory) ? Pulse::Low : Pulse::High;

        return new Signal($pulse, $this->name, $this->destinations);
    }
}
