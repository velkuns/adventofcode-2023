<?php

/*
 * Copyright (c) Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Application\Matrix;

use Application\Enum\PathDirection;
use Velkuns\Math\_2D\Direction;
use Velkuns\Math\_2D\Point2D;
use Velkuns\Math\_2D\Vector2DDir;
use Velkuns\Math\Matrix;

class Maze extends Matrix
{
    private const MAP_NEXT_DIRECTION = [
        Direction::Left->value => [
            '═' => Direction::Left,
            '╚' => Direction::Up,
            '╔' => Direction::Down,
        ],
        Direction::Right->value => [
            '═' => Direction::Right,
            '╝' => Direction::Up,
            '╗' => Direction::Down,
        ],
        Direction::Up->value => [
            '║' => Direction::Up,
            '╔' => Direction::Right,
            '╗' => Direction::Left,
        ],
        Direction::Down->value => [
            '║' => Direction::Down,
            '╚' => Direction::Right,
            '╝' => Direction::Left,
        ],
    ];

    public function isValidPath(Point2D $position, Direction $direction): bool
    {
        $next = $this->nextPosition($position, $direction);
        $path = $this->get($next);

        return match ($direction) {
            Direction::Up => in_array($path, ['║', '╔', '╗']),
            Direction::Down => in_array($path, ['║', '╚', '╝']),
            Direction::Left => in_array($path, ['═', '╚', '╔']),
            Direction::Right => in_array($path, ['═', '╝', '╗']),
        };
    }

    public function nextPosition(Point2D $position, Direction $direction): Point2D
    {
        return $position->translate(Vector2DDir::fromDirection($direction));
    }

    public function nextDirection(Point2D $nextPosition, Direction $fromDirection): Direction
    {
        $nextPath = $this->get($nextPosition);
        if ($nextPath === 'S') {
            return $fromDirection;
        }

        return self::MAP_NEXT_DIRECTION[$fromDirection->value][$nextPath];
    }

    public function render(): string
    {
        $string = "\n";
        for ($y = $this->getMaxY(); $y >= $this->getMinY(); $y--) {
            for ($x = $this->getMinX(); $x <= $this->getMaxX(); $x++) {
                $string .= $this->get(new Point2D($x, $y));
            }
            $string .= "\n";
        }

        return $string;
    }

    /**
     * @param array<string, array{move: PathDirection, orientation: Direction}> $allPaths
     * @return Direction
     */
    public function sideOfLoopThatIn(array $allPaths): Direction
    {
        $list  = array_map(fn(array $data) => $data['move']->value, $allPaths);
        $turns = array_filter($list, fn(string $pathDir) => $pathDir !== PathDirection::MoveForward->value);

        $count = array_count_values($turns);
        arsort($count);

        return Direction::from((string) key($count));
    }
}
