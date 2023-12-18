<?php

/*
 * Copyright (c) Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Application\Day;

use Application\Enum\PathDirection;
use Application\Puzzle;
use Velkuns\Math\_2D\Direction;
use Velkuns\Math\_2D\Point2D;
use Velkuns\Math\_2D\Vector2DDir;

class PuzzleDay18 extends Puzzle
{
    private const DIRECTIONS = [
        'L' => Direction::Left,
        'R' => Direction::Right,
        'U' => Direction::Up,
        'D' => Direction::Down,
        '0' => Direction::Right,
        '1' => Direction::Down,
        '2' => Direction::Left,
        '3' => Direction::Up,
    ];

    /**
     * 36726 to high :(
     * 36724 to low
     * so, it is 36725 :D
     * @param list<string> $inputs
     */
    protected function partOne(array $inputs): int
    {
        //~ Parse input for part 1
        $inputs = $this->parseInput(1, $inputs);

        //~ Calculate Area
        return $this->calculateArea($inputs);
    }

    private function parseInput(int $part, array $inputs): array
    {
        return array_map(
            function (string $line) use ($part): array {
                [$direction, $length, $hex] = explode(' ', $line);
                if ($part === 1) {
                    return [self::DIRECTIONS[$direction], (int) $length];
                }

                $hex = trim($hex, '()#');
                return [
                    self::DIRECTIONS[substr($hex, 5)], // direction
                    hexdec(substr($hex, 0, 5))
                ];
            },
            $inputs
        );
    }

    private function calculateArea(array $inputs): int
    {

        $shoelace = 0;

        $moves = [
            PathDirection::MoveForward->value => 0,
            PathDirection::TurnRight->value => 0,
            PathDirection::TurnLeft->value => 0,
        ];

        //~ Start from 0.0, centered on first block
        $pointA = new Point2D(0, 0);
        $fromDirection = null;

        foreach ($inputs as [$direction, $length,]) {
            //~ Get next point, center on the block
            $pointB = $pointA->translate(Vector2DDir::fromDirection($direction, $length));
            //echo "$pointA => $pointB | Direction: $direction->value\n";

            //~ calculate value for shoelace formula
            $shoelace += ($pointA->getX() * $pointB->getY() - $pointA->getY() * $pointB->getX());

            //~ Register number of straight blocks + 1 turn at the end
            $moves[PathDirection::MoveForward->value] += ($length - 1); // Length - last block (that will be a corner)
            if ($fromDirection !== null) {
                $move = PathDirection::move($fromDirection, $direction);
                $moves[$move->value]++;
            }

            $pointA = $pointB;
            $fromDirection = $direction;
        }

        //~ Add last corner for origin (positive shoelace - counter clock wise - last will be left, otherwise, right)
        if ($shoelace > 0) {
            $moves[PathDirection::TurnLeft->value]++;
        } else {
            $moves[PathDirection::TurnRight->value]++;
        }

        //~ tmp var for readability
        $nbTurnLeft  = $moves[PathDirection::TurnLeft->value];
        $nbTurnRight = $moves[PathDirection::TurnRight->value];

        //~ Finally compute area
        $area =
            //~ Base shoelace formula
            (abs($shoelace) / 2)
            +
            //~ Each straight block have 1/2 area (outside the closed area)
            ($moves[PathDirection::MoveForward->value] / 2)
            +
            //~ For shoelace value:
            // - positive (counter clock wise): turn left is corner "out" with 3/4 area outside, turn left 1/4 area
            // - negative ( clock wise) : turn right is corner "in" with 1/4 area outside, turn right 3/4 area
            ($shoelace > 0 ? $nbTurnRight * 0.25 + $nbTurnLeft * 0.75 : $nbTurnRight * 0.75 + $nbTurnLeft * 0.25)
        ;

        return (int) $area;
    }

    /**
     * @param list<string> $inputs
     */
    protected function partTwo(array $inputs): int
    {
        //~ Parse input for part 2
        $inputs = $this->parseInput(2, $inputs);

        //~ Calculate Area
        return $this->calculateArea($inputs);
    }
}
