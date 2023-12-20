<?php

/*
 * Copyright (c) Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Application\Day20\Enum;

enum Pulse: int
{
    case High = 1;
    case Low = 0;
}
