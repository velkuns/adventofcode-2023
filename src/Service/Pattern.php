<?php

/*
 * Copyright (c) Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Application\Service;

use Velkuns\Math\Matrix;

class Pattern extends Matrix
{
    /** @var Cache<int> $cache */
    private Cache $cache;

    /**
     * @param Cache<int> $cache
     */
    public function setCache(Cache $cache): static
    {
        $this->cache = $cache;

        return $this;
    }

    public function getVerticalReflectionPoint(string $index, bool $storeInCache): int
    {
        $reflectionPoints = [];

        //~ Iterate on columns
        for ($x = $this->getMinX() + 1; $x <= $this->getMaxX(); $x++) {
            //~ If we found a reflection point, store it & continue to iterate on x for another reflection point if exists
            if ($this->hasVerticalReflectionAfterX($x)) {
                $reflectionPoints[] = $x;
            }
        }

        if (empty($reflectionPoints)) {
            return 0;
        }

        //~ For part 1, it always 0
        $cachedReflectionPoint = (int) $this->cache->get($index);

        foreach ($reflectionPoints as $reflectionPoint) {
            //~ Same as cached reflection point, but for part two, we want a new one (and for part 1, cached = 0)
            if ($reflectionPoint === $cachedReflectionPoint) {
                continue;
            }

            if ($storeInCache) {
                $this->cache->set($index, $reflectionPoint); // Store it in part 1 for part 2
            }

            //~ Found, so return it
            return $reflectionPoint;
        }


        return 0;
    }

    public function getHorizontalReflectionPoint(string $index, bool $storeInCache): int
    {
        //~ Transpose pattern then get vertical reflection point
        return $this
            ->transpose()
            ->setCache($this->cache)
            ->getVerticalReflectionPoint($index, $storeInCache)
        ;
    }

    private function hasVerticalReflectionAfterX(int $x): bool
    {
        [$left, $right] = $this->splitAfterX($x);

        $left = $left->invert(false); //~ Invert matrix for comparison

        $max = min($left->getMaxX(), $right->getMaxX());

        for ($column = 0; $column <= $max; $column++) {
            if ($left->matrix[$column] !== $right->matrix[$column]) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return array{0: static, 1: static}
     */
    private function splitAfterX(int $x): array
    {
        //~ Copy array to avoid modification of current matrix
        $array = $this->matrix;

        //~ Extract left part as new instance, then recreate right instance part for remaining elements
        //~ to have the correct x min/max & y min/max values
        $left  = (new static(array_splice($array, 0, $x)));
        $right = (new static($array));

        return [$left, $right];
    }
}
