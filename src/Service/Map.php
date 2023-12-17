<?php

/*
 * Copyright (c) Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Application\Service;

use Application\Queue\LowerPriorityQueue;
use Application\Struct\Crucible;
use Velkuns\Math\_2D\Direction;
use Velkuns\Math\_2D\Point2D;
use Velkuns\Math\_2D\Vector2DDir;
use Velkuns\Math\Matrix;

class Map extends Matrix
{
    /** @var array<string, Crucible> $path  */
    private array $path = [];

    /** @var array<string, int> $visited */
    private array $visited = [];

    private const DIRECTIONS = [
        Direction::Up,
        Direction::Left,
        Direction::Down,
        Direction::Right,
    ];

    public function findColdestPath(Point2D $start, Point2D $end): int
    {
        /** @var LowerPriorityQueue<Crucible> $queue */
        $queue = new LowerPriorityQueue();

        $queue->insert(new Crucible($start, null, null, 0, 0), 0);

        while (!$queue->isEmpty()) {
            /** @var Crucible $crucible */
            $crucible = $queue->extract();

            //~ Crucible reach the end ? So return heat loss value !
            if ($this->isEnd($crucible->position, $end)) {
                return $crucible->heatLoss;
            }

            //~ Enqueue next position of crucible
            $queue = $this->enqueueNextPositions($queue, $crucible);
        }

        throw new \UnexpectedValueException('End of map not reached!');
    }

    private function isEnd(Point2D $position, Point2D $end): bool
    {
        return $position->getX() === $end->getX() && $position->getY() === $end->getY();
    }

    /**
     * @param LowerPriorityQueue<Crucible> $queue
     * @return LowerPriorityQueue<Crucible>
     */
    private function enqueueNextPositions(
        LowerPriorityQueue $queue,
        Crucible $crucible
    ): LowerPriorityQueue {
        static $nbTested = 0, $nbSkippedBackEmptyOrMoveThree = 0, $nbSkippedHigher = 0;

        //~ Enqueue 2 or 3 next direction (no backward, no more than 3 move in same direction)
        foreach (self::DIRECTIONS as $newDirection) {
            $nextPosition = $crucible->position->translate(Vector2DDir::fromDirection($newDirection, invertY: true));
            /** @var int|null $tile */
            $tile = $this->get($nextPosition);

            //~ Is backward direction or already moved three time in that direction
            $alreadyMoveThreeTime = $newDirection === $crucible->direction && $crucible->move === 3;
            if (
                empty($tile) ||
                $this->isBackwardDirection($crucible->direction, $newDirection) ||
                $alreadyMoveThreeTime
            ) {
                $nbSkippedBackEmptyOrMoveThree++;
                continue;
            }

            $nextHeatLoss = (int) $tile + $crucible->heatLoss;
            $nextMove     = $crucible->direction === $newDirection ? $crucible->move + 1 : 1;
            $nextCrucible = new Crucible($nextPosition, $crucible->position, $newDirection, $nextMove, $nextHeatLoss);

            //~ Get visited tile and compare registered heat loss (if exist)
            $visitedHeatLoss = ($this->visited[(string) $nextCrucible] ?? PHP_INT_MAX);

            //~ Visited position already have lower heat loss, so skip it and continue
            if ($visitedHeatLoss < $nextHeatLoss) {
                $nbSkippedHigher++;
                continue;
            }

            $this->visited[(string) $nextCrucible] = $nextHeatLoss;
            $this->path[(string) $nextPosition]    = $crucible;
            $nbTested++;
            $queue->insert($nextCrucible, $nextHeatLoss);
            echo "Nb tested = $nbTested, Nb skipped- = $nbSkippedBackEmptyOrMoveThree, Nb skipped+ = $nbSkippedHigher                     \r";
        }

        return $queue;
    }

    private function isBackwardDirection(Direction|null $previousDirection, Direction $newDirection): bool
    {
        if ($previousDirection === null) {
            return false;
        }

        return
            ($newDirection === Direction::Up && $previousDirection === Direction::Down) ||
            ($newDirection === Direction::Down && $previousDirection === Direction::Up) ||
            ($newDirection === Direction::Right && $previousDirection === Direction::Left) ||
            ($newDirection === Direction::Left && $previousDirection === Direction::Right)
        ;
    }
}
