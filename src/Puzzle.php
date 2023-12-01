<?php

/*
 * Copyright (c) Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Application;

abstract class Puzzle implements PuzzleInterface
{
    /**
     * @param list<string> $inputs
     */
    abstract protected function partOne(array $inputs): string|int|float;

    /**
     * @param list<string> $inputs
     */
    abstract protected function partTwo(array $inputs): string|int|float;

    /**
     * @return array{1: array<int, array<string>>, 2: array<int, array<string>>}
     */
    public function getExamples(int $part): array
    {
        return [
            1 => [0 => []],
            2 => [0 => []],
        ];
    }

    /**
     * @param list<string> $inputs
     */
    public function solve(int $part, array $inputs, bool $doSolveFunctional = false): string
    {
        if ($doSolveFunctional) {
            return (string) ($part === 1 ? $this->partOneFunctional($inputs) : $this->partTwoFunctional($inputs));
        } else {
            return (string) ($part === 1 ? $this->partOne($inputs) : $this->partTwo($inputs));
        }
    }

    /**
     * @param list<string> $inputs
     */
    protected function partOneFunctional(array $inputs): string|int|float
    {
        return 0;
    }

    /**
     * @param list<string> $inputs
     */
    protected function partTwoFunctional(array $inputs): string|int|float
    {
        return 0;
    }
}
