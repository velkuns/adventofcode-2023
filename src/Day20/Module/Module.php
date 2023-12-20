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

abstract class Module
{
    /**
     * @param list<string> $destinations
     */
    public function __construct(
        public readonly string $name,
        public readonly array $destinations,
    ) {}

    abstract public function receive(Signal $signal): Signal|null;
}
