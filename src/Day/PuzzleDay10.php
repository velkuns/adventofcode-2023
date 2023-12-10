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
use Application\Service\Maze;
use Velkuns\Math\_2D\Direction;
use Velkuns\Math\_2D\Point2D;

class PuzzleDay10 extends Puzzle
{
    private const MAP_CHARS = ['F' => '╔', '7' => '╗', 'L' => '╚', 'J' => '╝', '-' => '═', '|' => '║'];

    /** @var array<Direction> */
    private const DIRECTIONS = [
        Direction::Right,
        Direction::Left,
        Direction::Up,
        Direction::Down,
    ];

    /**
     * @param list<string> $inputs
     */
    protected function partOne(array $inputs): int
    {
        //~ Build maze from inputs
        $maze = $this->buildMaze($inputs);

        //~ Get position of starting point
        $position = $maze->locate('S');

        //~ Get position of starting point
        $direction = $this->getInitialDirection($maze, $position);

        //~ Then continue to follow the path until we have finished the loop and returned to the starting point
        $step = 0;
        do {
            $position  = $maze->nextPosition($position, $direction);
            $direction = $maze->nextDirection($position, $direction);
            $step++;
        } while ($maze->get($position) !== 'S');

        //~ Return the number of step to cycle divided by 2 to have the farthest point from starting point
        return (int) (ceil($step / 2));
    }

    /**
     * @param list<string> $inputs
     */
    private function buildMaze(array $inputs): Maze
    {
        //~ split lines into array of chars
        $inputs = array_map(str_split(...), $inputs);

        //~ Replace chars for better rendering
        $inputs = array_map(
            fn(array $line) => str_replace(array_keys(self::MAP_CHARS), array_values(self::MAP_CHARS), $line),
            $inputs
        );

        //~ Transform array to Maze object (Matrix) then invert & transpose it
        return (new Maze($inputs))
            ->invert(false)
            ->transpose()
        ;
    }

    private function getInitialDirection(Maze $maze, Point2D $start): Direction
    {
        //~ Then get first next direction to use that is a valid path
        /** @var Direction $direction */
        foreach (self::DIRECTIONS as $direction) {
            if ($maze->isValidPath($start, $direction)) {
                break;
            }
        }

        return $direction;
    }

    /**
     * @param list<string> $inputs
     */
    protected function partTwo(array $inputs): int
    {
        //~ Build maze from inputs
        $maze = $this->buildMaze($inputs);

        //~ Get position of starting point
        $fromPosition = $maze->locate('S');

        //~ Get position of starting point
        $fromDirection = $this->getInitialDirection($maze, $fromPosition);

        /** @var array<string, array{move: PathDirection, orientation: Direction}> $allPaths */
        $allPaths = [];

        //~ Then continue to follow the path until we have finished the loop and returned to the starting point
        do {
            $toPosition  = $maze->nextPosition($fromPosition, $fromDirection);
            $toDirection = $maze->nextDirection($toPosition, $fromDirection);

            $allPaths[(string) $toPosition] = [
                'orientation' => PathDirection::orientation($fromDirection, $toDirection),
                'move'        => PathDirection::move($fromDirection, $toDirection),
            ];

            $fromDirection = $toDirection;
            $fromPosition  = $toPosition;

        } while ($maze->get($toPosition) !== 'S');

        //~ Determine which side of loop is in and which side is out
        $sideOfLoopThatIn = $maze->sideOfLoopThatIn($allPaths);

        //~ Then get list of unexplored position
        $unexploredPositions = $this->getUnexploredPositions($maze, $allPaths);

        $inLoop = $this->getInLoopPositions($maze, $sideOfLoopThatIn, $unexploredPositions, $allPaths);

        return count($inLoop);
    }

    /**
     * @param Maze $maze
     * @param array<string, array{move: PathDirection, orientation: Direction}> $allPaths
     * @return list<Point2D>
     */
    private function getUnexploredPositions(Maze $maze, array $allPaths): array
    {
        $unexploredPositions = [];
        for ($y = $maze->getMinY(); $y <= $maze->getMaxY(); $y++) {
            for ($x = $maze->getMinX(); $x <= $maze->getMaxX(); $x++) {
                $position = new Point2D($x, $y);
                if (!isset($allPaths[(string) $position])) {
                    $unexploredPositions[] = $position;
                }
            }
        }

        return $unexploredPositions;
    }

    /**
     * @param Maze $maze
     * @param Direction $sideOfLoopThatIn
     * @param array<Point2D> $unexploredPositions
     * @param array<string, array{move: PathDirection, orientation: Direction}> $allPaths
     * @return array<string, bool>
     */
    private function getInLoopPositions(
        Maze $maze,
        Direction $sideOfLoopThatIn,
        array $unexploredPositions,
        array $allPaths,
    ): array {
        $inLoop    = [];
        $outOfLoop = [];

        //~ For each unexplored position, try to find the path on the right
        foreach ($unexploredPositions as $position) {
            $unknownPositions = [$position];
            //~ On current unexplored path, increase x position to get neighbour
            for ($x = $position->getX() + 1; $x <= $maze->getMaxX(); $x++) {
                $nextPosition = new Point2D($x, $position->getY());

                //~ Neighbour is in loop, so flag all unknown positions to in loop
                if (isset($inLoop[(string) $nextPosition])) {
                    $inLoop = $this->tagUnknownPositions($unknownPositions, $inLoop);
                    $unknownPositions = [];
                    break;
                }

                if (isset($outOfLoop[(string) $nextPosition])) {
                    $outOfLoop = $this->tagUnknownPositions($unknownPositions, $outOfLoop);
                    $unknownPositions = [];
                    break;
                }

                //~ Neighbour is path, so loop at the path to determine if is it in loop or out of the loop
                if (isset($allPaths[(string) $nextPosition])) {
                    $isInLoop = $this->isInLoop($allPaths[(string) $nextPosition]['orientation'], $sideOfLoopThatIn);
                    if ($isInLoop) {
                        //~ Neighbour is in loop, so flag all unknown positions to in loop
                        $inLoop = $this->tagUnknownPositions($unknownPositions, $inLoop);
                        $unknownPositions = [];
                        break;
                    }

                    //~ Neighbour is out of loop, so flag all unknown positions to out of the loop
                    $outOfLoop = $this->tagUnknownPositions($unknownPositions, $outOfLoop);
                    $unknownPositions = [];
                    break;
                }

                $unknownPositions[] = $position;
            }

            //~ Flag any unknown remaining position to out of loop (border of the maze
            foreach ($unknownPositions as $unknownPosition) {
                $outOfLoop[(string) $unknownPosition] = true;
            }
        }

        return $inLoop;
    }

    /**
     * @param array<Point2D> $unknownPositions
     * @param array<string, bool> $taggedPositions
     * @return array<string, bool>
     */
    private function tagUnknownPositions(array $unknownPositions, array $taggedPositions): array
    {
        foreach ($unknownPositions as $unknownPosition) {
            $taggedPositions[(string) $unknownPosition] = true;
        }

        return $taggedPositions;
    }

    private function isInLoop(Direction $pathOrientation, Direction $sideOfLoopThatIn): bool
    {
        return
            ($pathOrientation === Direction::Up && $sideOfLoopThatIn === Direction::Left) ||
            ($pathOrientation === Direction::Down && $sideOfLoopThatIn === Direction::Right)
        ;
    }
}
