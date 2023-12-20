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
use Application\Day20\Enum\State;
use Application\Day20\Signal;

class FlipFlop extends Module
{
    private State $state = State::Off;

    public function receive(Signal $signal): Signal|null
    {
        if ($signal->pulse === Pulse::High) {
            return null;
        }

        $this->state = State::toggle($this->state);

        $pulse = $this->state === State::On ? Pulse::High : Pulse::Low;

        return new Signal($pulse, $this->name, $this->destinations);
    }

    public function state(): State
    {
        return $this->state;
    }
}
