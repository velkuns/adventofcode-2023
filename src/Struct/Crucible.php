<?php

/*
 * Copyright (c) Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Application\Struct;

use Velkuns\Math\_2D\Direction;
use Velkuns\Math\_2D\Point2D;

class Crucible implements \Stringable
{
    public function __construct(
        public readonly Point2D $position,
        public readonly Point2D|null $from,
        public readonly Direction|null $direction,
        public readonly int $move,
        public readonly int $heatLoss
    ) {}

    public function __toString(): string
    {
        return "$this->position|$this->move";
    }
}
