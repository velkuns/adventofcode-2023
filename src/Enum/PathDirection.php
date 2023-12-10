<?php

/*
 * Copyright (c) Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Application\Enum;

use Velkuns\Math\_2D\Direction;

enum PathDirection: string
{
    case MoveForward = 'F';
    case TurnLeft = Direction::Left->value;
    case TurnRight = Direction::Right->value;

    private const MapDirectionMove = [
        //~ From Left direction
        Direction::Left->value => [
            //~ To
            Direction::Left->value => self::MoveForward,
            Direction::Up->value   => self::TurnRight,
            Direction::Down->value => self::TurnLeft,
        ],
        //~ From Right direction
        Direction::Right->value => [
            //~ To
            Direction::Right->value => self::MoveForward,
            Direction::Up->value    => self::TurnLeft,
            Direction::Down->value  => self::TurnRight,
        ],
        //~ From Up direction
        Direction::Up->value => [
            //~ To
            Direction::Up->value    => self::MoveForward,
            Direction::Left->value  => self::TurnLeft,
            Direction::Right->value => self::TurnRight,
        ],
        //~ From Up direction
        Direction::Down->value => [
            //~ To
            Direction::Down->value    => self::MoveForward,
            Direction::Left->value  => self::TurnRight,
            Direction::Right->value => self::TurnLeft,
        ],
    ];

    public static function move(Direction $from, Direction $to): PathDirection
    {
        return self::MapDirectionMove[$from->value][$to->value];
    }

    public static function orientation(Direction $from, Direction $to): Direction
    {
        //~ Search of UP & Down orientation first, because we need it to determine side of path after
        return match (true) {
            $from === Direction::Up || $to === Direction::Up => Direction::Up,
            $from === Direction::Down || $to === Direction::Down => Direction::Down,
            $from === Direction::Left || $to === Direction::Left => Direction::Left,
            $from === Direction::Right || $to === Direction::Right => Direction::Right,
        };
    }
}
