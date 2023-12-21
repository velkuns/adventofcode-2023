<?php

/*
 * Copyright (c) Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Application\Matrix;

use Velkuns\Math\_2D\Direction;
use Velkuns\Math\_2D\Point2D;
use Velkuns\Math\_2D\Vector2DDir;
use Velkuns\Math\Matrix;

class Garden extends Matrix
{
    /** @var array<string, int> $nbStepsForPositions */
    private array $nbStepsForPositions = [];

    private const DIRECTIONS = [
        Direction::Up,
        Direction::Left,
        Direction::Down,
        Direction::Right,
    ];

    /** @var array{
     *     even: array{
     *         left: array{pos: Point2D|null, steps: int},
     *         right: array{pos: Point2D|null, steps: int},
     *         top: array{pos: Point2D|null, steps: int},
     *         bottom: array{pos: Point2D|null, steps: int},
     *     },
     *     odd: array{
     *         left: array{pos: Point2D|null, steps: int},
     *         right: array{pos: Point2D|null, steps: int},
     *         top: array{pos: Point2D|null, steps: int},
     *         bottom: array{pos: Point2D|null, steps: int},
     *     }
     * } $edges
     */
    private array $edges = [
        'even' => [
            'left'   => ['pos' => null, 'steps' => PHP_INT_MAX],
            'right'  => ['pos' => null, 'steps' => PHP_INT_MAX],
            'top'    => ['pos' => null, 'steps' => PHP_INT_MAX],
            'bottom' => ['pos' => null, 'steps' => PHP_INT_MAX],
        ],
        'odd' => [
            'left'   => ['pos' => null, 'steps' => PHP_INT_MAX],
            'right'  => ['pos' => null, 'steps' => PHP_INT_MAX],
            'top'    => ['pos' => null, 'steps' => PHP_INT_MAX],
            'bottom' => ['pos' => null, 'steps' => PHP_INT_MAX],
        ],
    ];

    public function computeAllShortestPaths(Point2D $start): void
    {
        $currentStep = 0;

        /** @var \SplQueue<array{0: Point2D, 1: int}> $queue */
        $queue = new \SplQueue();

        $queue->enqueue([$start, $currentStep]);

        while (!$queue->isEmpty()) {
            [$position, $currentStep] = $queue->dequeue();

            $minStep = $this->nbStepsForPositions[(string) $position] ?? PHP_INT_MAX;

            if ($currentStep >= $minStep) {
                continue;
            }

            $this->nbStepsForPositions[(string) $position] = $currentStep;

            $currentStep++;

            foreach (self::DIRECTIONS as $direction) {
                $nextPosition = $position->translate(Vector2DDir::fromDirection($direction, invertY: true));
                if ($this->get($nextPosition) === '.') {
                    $queue->enqueue([$nextPosition, $currentStep]);
                }
            }
        }

        $this->computeMinStepsToReachEdges();
    }

    private function computeMinStepsToReachEdges(): void
    {
        $edges = [
            'left'   => [
                ['even' => $this->getMinX(), 'odd' => $this->getMinX() + 1],
                $this->getMinY(),
                $this->getMaxY(),
            ],
            'right'  => [
                ['even' => $this->getMaxX(), 'odd' => $this->getMaxX() - 1],
                $this->getMinY(),
                $this->getMaxY(),
            ],
            'top'    => [
                ['even' => $this->getMinY(), 'odd' => $this->getMinY() + 1],
                $this->getMinX(),
                $this->getMaxX(),
            ],
            'bottom' => [
                ['even' => $this->getMaxY(), 'odd' => $this->getMaxY() - 1],
                $this->getMinX(),
                $this->getMaxX(),
            ],
        ];

        foreach ($edges as $edge => [$types, $min, $max]) {
            for ($v = $min; $v <= $max; $v++) {
                foreach ($types as $type => $w) {
                    $x = in_array($edge, ['left', 'right']) ? $w : $v;
                    $y = in_array($edge, ['top', 'bottom']) ? $w : $v;
                    $pos = new Point2D($x, $y);
                    if (!isset($this->nbStepsForPositions[(string) $pos])) {
                        continue;
                    }

                    $steps = $this->nbStepsForPositions[(string) $pos];
                    if ($steps < $this->edges[$type][$edge]['steps']) {
                        $this->edges[$type][$edge]['pos']   = $pos;
                        $this->edges[$type][$edge]['steps'] = $steps;
                    }
                }
            }
        }
    }

    public function countTilesReachableInMaxSteps(int $maxSteps, bool $infinite): int
    {
        $isEvenSteps = $maxSteps % 2 === 0;

        $evenTiles = array_filter($this->nbStepsForPositions, fn(int $nbSteps) => $nbSteps % 2 === 0);
        $oddTiles  = array_filter($this->nbStepsForPositions, fn(int $nbSteps) => $nbSteps % 2 === 1);
        $tiles     = $isEvenSteps ? $evenTiles : $oddTiles;
        $needScale = $infinite && $maxSteps > ($this->width() + 1 / 2);

        if (!$needScale) {
            return count(array_filter($tiles, fn(int $nbSteps) => $nbSteps <= $maxSteps));
        }

        //~ Assuming we can go with the min distance direct on left, top, right & bottom. Also true for corners with
        //~ Manhattan distance

        //~ Scale down factor to reduce garder to 1 unit
        $downScaleFactor = $this->width();

        //~ With previous assumption, we can calculate area for a triangle rectangle with both length on left / top
        //~ directions with length of max step divided by the down scale factor (one unit of length = 1 garden)
        $scaledDownTriangleLength = $maxSteps / $downScaleFactor;

        //~ Calculate area for the triangle
        $scaledDownTriangleArea = pow($scaledDownTriangleLength, 2) / 2;

        //~ Calculate approximation: area * number of even tiles * 4 (because the triangle area is one quarter of
        //~ the total area for walking in four directions
        $approx = ($scaledDownTriangleArea * count($evenTiles)) * 4;

        return (int) $approx;
    }
}
