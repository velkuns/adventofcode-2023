<?php

/*
 * Copyright (c) Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Application;

interface PuzzleInterface
{
    /**
     * @return array{1: array<int, array<string>>, 2: array<int, array<string>>}
     */
    public function getExamples(int $part): array;

    /**
     * @param list<string> $inputs
     */
    public function solve(int $part, array $inputs, bool $doSolveFunctional = false): string;
}
