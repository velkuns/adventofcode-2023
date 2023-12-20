<?php

/*
 * Copyright (c) Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Application\Day20\Module;

use Application\Day20\Signal;

class Broadcast extends Module
{
    public function receive(Signal $signal): Signal|null
    {
        return new Signal($signal->pulse, $this->name, $this->destinations);
    }
}
