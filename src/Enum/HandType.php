<?php

/*
 * Copyright (c) Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Application\Enum;

enum HandType: int
{
    case HighCard = 0;
    case OnePair = 1;
    case TwoPair = 2;
    case ThreeOfAKind = 3;
    case FullHouse = 4;
    case FourOfAKind = 5;
    case FiveOfAKind = 6;

    /**
     * @param array<int, int> $handA
     * @param array<int, int> $handB
     */
    public static function best(array $handA, array $handB): int
    {
        $typeA = self::type($handA);
        $typeB = self::type($handB);

        return $typeA->value <=> $typeB->value;
    }

    /**
     * @param array<int, int> $hand
     */
    private static function type(array $hand): HandType
    {
        arsort($hand);

        /** @var int<1,5> $countDiffCards */
        $countDiffCards = count($hand);
        $countMostCards = (int) reset($hand);

        return match (true) {
            $countDiffCards === 5 => HandType::HighCard,
            $countDiffCards === 4 => HandType::OnePair,
            $countDiffCards === 3 && $countMostCards === 2 => HandType::TwoPair,
            $countDiffCards === 3 && $countMostCards === 3 => HandType::ThreeOfAKind,
            $countDiffCards === 2 && $countMostCards === 3 => HandType::FullHouse,
            $countDiffCards === 2 && $countMostCards === 4 => HandType::FourOfAKind,
            $countDiffCards === 1 => HandType::FiveOfAKind,
            true => throw new \UnexpectedValueException('Wrong input. To few or to many chars'),
        };
    }
}
