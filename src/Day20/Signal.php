<?php

/*
 * Copyright (c) Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Application\Day20;

use Application\Day20\Enum\Pulse;

readonly class Signal
{
    /**
     * @param Pulse $pulse
     * @param string $origin
     * @param list<string> $destinations
     */
    public function __construct(
        public Pulse $pulse,
        public string $origin,
        public array $destinations,
    ) {}

    public function count(): int
    {
        return count($this->destinations);
    }

    public function debug(): void
    {
        foreach ($this->destinations as $destination) {
            echo $this->origin . ' -' . ($this->pulse === Pulse::High ? 'high' : 'low') . '-> ' . $destination . "\n";
        }
    }
}
