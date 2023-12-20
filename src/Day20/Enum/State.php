<?php

/*
 * Copyright (c) Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Application\Day20\Enum;

enum State: int
{
    case On = 1;
    case Off = 0;

    public static function toggle(State $state): State
    {
        return $state === State::On ? State::Off : State::On;
    }
}
