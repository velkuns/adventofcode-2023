<?php

/*
 * Copyright (c) Deezer
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Application\Queue;

/**
 * @template T
 */
class LowerPriorityQueue extends \SplPriorityQueue
{
    /**
     * @param int $priority1
     * @param int $priority2
     * @return int
     */
    public function compare(mixed $priority1, mixed $priority2): int
    {
        return $priority2 <=> $priority1;
    }
}
